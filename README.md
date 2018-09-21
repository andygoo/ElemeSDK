# 饿了么蜂鸟配送php开发包(开发中)

### 功能介绍
#### 本项目为蜂鸟配送开放平台api封装库，使用php语言实现，封装为composer包。

### 安装方法

1. 直接克隆项目或下载发行版

2. 使用composer安装（推荐）

* 请在项目根目录执行以下命令（请使用版本较新的composer，并设置好镜像源）

```
composer require hillpy/eleme-sdk
```

### 使用方法

* 以下为示例代码

```
/**
 * 使用案例
 * 注意：实际项目请先引入自动加载脚本（require __DIR__ . '/vender/autoload.php';）。另外需安装redis扩展并开启redis服务
 */
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
$eleme = new Eleme($paramArr);

//若缓存中不存在accessToken，从新实例化对象中获取并写入redis
if (!$accessToken) {
     isset($eleme->accessTokenData['access_token']) && $accessToken = $eleme->accessTokenData['access_token'];

    if ($accessToken) {
        // 获取的expire_time为毫秒时间戳，转秒时间戳并减去10秒（过期时间适当提前避免accessToken实际已失效）
        if (isset($eleme->accessTokenData['expire_time'])) {
            $expireTime = intval(($eleme->accessTokenData['expire_time'] / 1000)) - 10;
            $cacheTime = $expireTime - time();
        } else {
            $cacheTime = 0;
        }
        $redis->setex('eleme_access_token_appid_' . $appId, $cacheTime, $accessToken);
    }
}

// 输出accessToken
if ($accessToken == '') {
    echo 'accessToken获取失败';
} else {
    echo 'accessToken:' . $accessToken;
}
```