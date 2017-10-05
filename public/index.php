<?php

require __DIR__ . '/../vendor/autoload.php';

// Set up dependencies
require __DIR__ . '/../src/const.php';

$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

$app->add(function ($request, $response, $next) {
    session_start();
    if (!empty($_SESSION['visitor_id'])){
        $user = Models\User::getById($_SESSION['visitor_id']);
    }
    if (empty($user)) {
        $visitor = [
            '_id'     => 0,
            'name'    => '游客',
            'avatar_url'=>'',
        ];
    }
    else{
        $visitor = $user->getArrayCopy();
    }
    $request = $request->withAttribute('visitor', $visitor);
    return $next($request, $response);
});

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Set up dependencies
require __DIR__ . '/../src/models.php';

// Register routes
require __DIR__ . '/../src/routes.php';

$app->run();
