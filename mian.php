<?php
/**
 * 小米路由重新拨号
 * Created by cqqh
 * Date: 2016/8/10 11:19
 */


//config 配置后才能使用
$_config = [
    'key' => 'a2ffa5c9be07488bbb04a3a47d3c5f6a',    //路由器key，路由器登录页 var Encrypt 查看
    'deviceId' => 'b8:27:eb:f2:8d:f7',              //路由器deviceId，路由器登录页 var Encrypt 下 nonceCreat 查看
    'pwd' => 'pwd',                                 //路由器密码
    'route_url' => 'http://192.168.31.1'            //路由器地址
];

CONST LOGIN_URL = '/cgi-bin/luci/api/xqsystem/login';

$nonce = [
    0,
    $_config['deviceId'],
    time(),
    rand(1000,9999)
];

$nonce = implode('_',$nonce);
$oldpwd = sha1($nonce . sha1($_config['pwd'].$_config['key']));

$param = [
    'username' => 'admin',
    'password' => $oldpwd,
    'logtype'=> 2,
    'nonce' => $nonce
];
$json = http_request($_config['route_url'].LOGIN_URL, false, $param);
$res = json_decode($json, true);

if($res['code'] == 0) {
    http_request($_config['route_url'].'/cgi-bin/luci/;stok='.$res["token"].'/api/xqnetwork/pppoe_stop');
    sleep(3);
    echo http_request($_config['route_url'].'/cgi-bin/luci/;stok='.$res["token"].'/api/xqnetwork/pppoe_start');
    sleep(3);
    echo http_request($_config['route_url'].'/cgi-bin/luci/;stok='.$res["token"].'/api/xqnetwork/pppoe_status');
} else {
    var_dump($res);
}

function http_request($URI, $isHearder = false, $post = false)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $URI);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, $isHearder);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.85 Safari/537.36');
    if(strpos($URI, 'https') === 0){
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    if($post){
        curl_setopt ($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $post);
    }
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}