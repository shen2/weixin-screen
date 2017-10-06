<?php
require 'WeixinApi.php';

class ExceptionResponse{
    public static function unauthorized(){
        $response = new Slim\Http\Response(401);
        $body = new Slim\Http\Body(fopen('php://temp', 'r+'));
        $body->write(json_encode(['message' => '没有身份授权']));
        return $response->withHeader('Content-Type', 'application/json;charset=utf-8')
                        ->withBody($body);
    }
}

// Routes
$app->get('/visitor', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");
    return $response->withJson([
        'status'=>'ok',
        'visitor'=>$request->getAttribute('visitor'),
        'version'=>'0.0.1',
    ]);
});

$app->get('/screens/{screen_id}', function ($request, $response, $args) {
    $visitor = $request->getAttribute('visitor');
    $screenId = (int) $args['screen_id'];
    $screen = Models\Screen::getById($screenId);
    $screen = $screen->getArrayCopy();
    if (!in_array($visitor['_id'], $screen['members'])){
        Models\Screen::addMember($screenId, $visitor['_id']);
    }
    return $this->view->render($response, 'index.html', [
        'screen'  => $screen,
        'visitor' => $visitor,
    ]);
})->setName('index');

$app->get('/screens/{screen_id}/board', function ($request, $response, $args) {
    $visitor = $request->getAttribute('visitor');
    $screenId = (int) $args['screen_id'];
    $screen = Models\Screen::getById($screenId);
    $screen = $screen->getArrayCopy();
    return $this->view->render($response, 'board.html', [
        'screen'  => $screen,
        'visitor' => $visitor,
    ]);
})->setName('board');

$app->get('/weixin-login', function ($request, $response, $args) {

    $redirectUrl = 'https://open.weixin.qq.com/connect/oauth2/authorize?'
        . http_build_query([
            'appid' => WEIXIN_APPID,
            'redirect_uri'=> 'http://wedding.tuchong.com/weixin-callback',
            'response_type'=> 'code',
            'scope'=> 'snsapi_userinfo',
            'state'=> '',
        ])
        . '#wechat_redirect';
    return $response->withStatus(302)->withHeader('Location', $redirectUrl);
});
$app->get('/weixin-callback', function ($request, $response, $args) {
    $params = $request->getQueryParams();
    $token = WeixinApi::getTokenByCode(WEIXIN_APPID, WEIXIN_SECRET, $params['code']);

    $weixinApi = new WeixinApi($token['access_token']);
    $userInfo = $weixinApi->get('sns/userinfo', ['openid' => $token['openid']]);
    $user = [
        '_id'       => $userInfo['openid'],
        'union_id'  => $userInfo['unionid'],
        'name'      => $userInfo['nickname'],
        'avatar_url'=> $userInfo['headimgurl'],
        'gender'    => $userInfo['sex'],
        'city'      => $userInfo['city'],
        'province'      => $userInfo['province'],
        'country'      => $userInfo['country'],
    ];
    Models\User::save($user);

    $_SESSION['visitor_id'] = $user['_id'];

    return $response->withStatus(302)->withHeader('Location', '/screens/123');
});

$app->get('/api/screens/{screen_id}', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Screen route");
    $screenId = (int) $args['screen_id'];
    $screen = Models\Screen::getById($screenId);
    $screen = $screen->getArrayCopy();
    $screen['members'] = Models\User::getList($screen['members']);
    return $response->withJson(['screen'=>$screen,]);
});

$app->get('/api/board/{screen_id}', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Screen route");
    $visitor = $request->getAttribute('visitor');
    $screenId = (int) $args['screen_id'];
    $postList = [];
    $postListCursor = Models\Post::getByScreenId($screenId);
    foreach($postListCursor as $post){
        $postList[] = Models\Post::toJson($post, $visitor['_id']);
    }
    return $response->withJson(['messages'=>$postList,]);
});

$app->post('/api/board/new', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("New message route");
    $requestBody = $request->getParsedBody();
    $visitor = $request->getAttribute('visitor');
    $newMessage = [
      'screen_id' => $requestBody['screen_id'],
      'content' => $requestBody['content'],
      'author'  => $visitor,
    ];
    $insertedId = Models\Post::insertOne($newMessage);
    $newMessage['_id'] = $insertedId->__toString();
    $newMessage['created_at'] = date('H:i', $insertedId->getTimestamp());
    return $response->withJson(['newMessage'=>$newMessage,]);
});

$app->post('/api/posts/{post_id}/likes', function ($request, $response, $args) {
    $visitor = $request->getAttribute('visitor');
    $post = Models\Post::getById(new MongoDB\BSON\ObjectID($args['post_id']));
    if (empty($post['likes']) || !in_array($post['likes'],$visitor['_id'])){
        Models\Post::addLike($post['_id'], $visitor['_id']);
    }
    return $response->withJson(['newMessage'=>$newMessage,]);
});
