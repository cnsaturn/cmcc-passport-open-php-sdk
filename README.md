中国移动通行证开放平台PHP SDK
========

本项目是基于[Zend Framework][ZF]开发的移动通行证平台PHP SDK开发包；支持所有官方提供的API，可用于网站接入型应用和普通应用开发。

## 使用示例

```php
/** 将Passport_Open SDK类包含进来 */
require_once dirname(dirname(__FILE__)) . '/lib/Passport/Open.php';

/** 初始化 Passport_Open 类 */
$open_sdk = new Passport_Open(array(
	'consumerKey' => '-->填写你申请到的appkey<--', // 填写你在开放平台申请到的应用appKey
	'consumerSecret' => '-->填写你申请到的appSecret<--', // 填写你在开放平台申请到的应用appSecret
	'callbackUrl' => '-->填写授权成功后需要跳转到的URL（比如本php文件的URL）<--' // 回调 callback 地址
));

/** 执行 OAuth 1.0a 用户认证授权 */
$open_sdk->authenticate();

/** 更新授权用户基本档案信息 */
$open_sdk->updateUserProfile(array(
	'nick_name' => 'Saturn'
));
```

[ZF]: http://zendframework.com