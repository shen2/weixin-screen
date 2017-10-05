<?php
class WeixinApi{
    
    protected static $endpoint = 'https://api.weixin.qq.com/';

    protected $accessToken;

    public static function getToken($appid, $secret){
        $curl = curl_init();
        $params = [
            'grant_type'=> 'client_credential',
            'appid' => $appid,
            'secret' => $secret,
        ];
        curl_setopt($curl, CURLOPT_URL, self::$endpoint . 'cgi-bin/token?' . http_build_query($params));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        return json_decode($response, true);
    }

    public static function getTokenByCode($appid, $secret, $code){
        $curl = curl_init();
        $params = [
            'appid'     => $appid,
            'secret'    => $secret,
            'code'   => $code,
            'grant_type'=> 'authorization_code',
        ];
        curl_setopt($curl, CURLOPT_URL, 'https://api.weixin.qq.com/sns/oauth2/access_token?' . http_build_query($params));

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        return json_decode($response, true);
    }

    public static function getSessionKeyByCode($appid, $secret, $code){
        $curl = curl_init();
        $params = [
            'appid'     => $appid,
            'secret'    => $secret,
            'js_code'   => $code,
            'grant_type'=> 'authorization_code',
        ];
        curl_setopt($curl, CURLOPT_URL, 'https://api.weixin.qq.com/sns/jscode2session?' . http_build_query($params));

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        return json_decode($response, true);
    }

    public function __construct($accessToken){
        $this->accessToken = $accessToken;
    }

    public function post($path, $params){
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, self::$endpoint . $path . '?access_token=' . $this->accessToken);
        curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($c);
        return $response;
    }

    public function get($path, $params){
        $c = curl_init();
        $params['access_token'] = $this->accessToken;
        curl_setopt($c, CURLOPT_URL, self::$endpoint . $path . '?' . http_build_query($params));
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($c);
        return json_decode($response, true);
    }
}
