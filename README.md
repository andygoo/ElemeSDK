# 饿了么蜂鸟配送php开发包(开发中)

[![issue](https://img.shields.io/github/issues/hillpy/ElemeSDK.svg)](https://github.com/hillpy/ElemeSDK/issues)
[![star](https://img.shields.io/github/stars/hillpy/ElemeSDK.svg)](https://github.com/hillpy/ElemeSDK)
[![fork](https://img.shields.io/github/forks/hillpy/ElemeSDK.svg)](https://github.com/hillpy/ElemeSDK)
[![license](https://img.shields.io/github/license/hillpy/ElemeSDK.svg)](https://github.com/hillpy/ElemeSDK/blob/master/LICENSE)

### 功能介绍
#### 本项目为蜂鸟配送开放平台api封装库，使用php语言实现，封装为composer包。

### 安装方法

1. 下载发行版

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
 * 注意：实际项目若使用composer安装的库，请先引入自动加载脚本（require __DIR__ . '/vender/autoload.php';）。另外需安装redis扩展并开启redis服务
 */
use hillpy\ElemeSDK\Eleme;

// 设置中国时区（个别接口涉及时间数据）
date_default_timezone_set('PRC');

// 设置变量
$appId = '';
$secretKey = '';

// 设置实例化参数
$paramArr = array(
    'appId'=>$appId,
    'secretKey'=>$secretKey,
    'debug'=>true,
    'accessToken'=>''
);

$debug = $paramArr['debug'] ? 'true' : 'false';

// 从redis获取accessToken;
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$accessToken = $redis->get('eleme_access_token_appid_' . $appId . '_debug_' . $debug);

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
        $redis->setex('eleme_access_token_appid_' . $appId . '_debug_' . $debug, $cacheTime, $accessToken);
    }
}

// 输出accessToken
if ($accessToken == '') {
    echo 'accessToken获取失败<br>';
} else {
    echo 'accessToken:' . $accessToken . '<br>';
}

// 以添加添加门店接口为例，以下为门店接口请求数据及结果 
$extendParamArr['data'] = array(
    'chain_store_code' => '',
    'chain_store_name' => '',
    'contact_phone' => '',
    'address' => '',
    'position_source' => 3,
    'longitude' => '',
    'latitude' => '',
    'service_code' => 1
);

$res = $eleme->addChainStore($extendParamArr);

var_dump('<pre>');
var_dump($res);
```

### 仓库地址

[Coding](https://dev.tencent.com/u/shinn_lancelot/p/ElemeSDK "ElemeSDK")<br>
[Gitee](https://gitee.com/hillpy/ElemeSDK "ElemeSDK")<br>
[Github](https://github.com/hillpy/ElemeSDK "ElemeSDK")<br>

### 协议

[MIT](https://github.com/hillpy/ElemeSDK/blob/master/LICENSE "MIT")<br>