<?php

/**
 * 使用案例
 * 注意：实际项目请先引入自动加载脚本（require __DIR__ . '/vender/autoload.php';），此例子中直接引用类库
 */
require __DIR__ . '/src/Eleme.php';

use hillpy\ElemeSDK\Eleme;

// 设置变量
$appId = 'app_id';
$secretKey = 'secret_key';

// 设置实例化参数
$paramArr = array(
    'appId'=>$appId,
    'secretKey'=>$secretKey,
    'debug'=>true,
    'accessToken'=>''
);

// 从redis获取accessToken;
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$accessToken = $redis->get('eleme_access_token_appid_' . $appId);

// 若存在accessToken，加入paramArr数组中
$accessToken && $paramArr['accessToken'] = $accessToken;

// 实例化Eleme
$eleme = new Eleme($praramArr);

//若缓存中不存在accessToken，从新实例化对象中获取并写入redis
if (!$accessToken) {
    $accessToken = $eleme->accessTokenData['access_token'];

    if ($accessToken) {
        // 获取的expire_time为毫秒时间戳，转秒时间戳并减去10秒（过期时间适当提前避免accessToken实际已失效）
        $expireTime = intval(($eleme->accessTokenData['expire_time'] / 1000)) - 10;
        $cacheTime = $expireTime - time();
        $redis->setex('eleme_access_token_appid_' . $appId, $cacheTime, $accessToken);
    }
}

// 输出accessToken
if ($accessToken == '') {
    echo 'accessToken获取失败';
} else {
    echo 'accessToken:' . $accessToken;
}
