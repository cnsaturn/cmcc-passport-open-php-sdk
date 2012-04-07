中国移动通行证开放平台PHP SDK
========

基于[Zend Framework][ZF]开发的移动通行证平台PHP SDK开发包；支持所有官方提供的[API][API]，可用于网站接入型应用和普通应用开发。

## 运行环境

* PHP 5.2.x + (with cURL enabled)
* 请在Firefox或Chrome中运行和查看SDK提供的示例代码。

## 入手SDK

了解[OAuth授权认证流程][OAuth]可以从本SDK项目的 examples 入手，

* Simple.php - 实现了OAuth用户授权认证，并调用某API方法获取和打印当前用户信息。
* Api.php - 以可视化Web界面查看和调试当前官方提供的[所有API方法][API]，其中包含所有API的对应调用代码。

以上两个演示用例，均须你注册成为移动用户通行证用户。

## 使用示例

```php
/** 将Passport_Open SDK类包含进来 */
require_once dirname(dirname(__FILE__)) . '/lib/Passport/Open.php';

/** 初始化 Passport_Open 类 */
$passport = new Passport_Open(array(
	'consumerKey' => '2937daedfa310c826d8727384ca8979304f03a6c9', // 填写应用appKey
	'consumerSecret' => '2dab22f40f108d3c619804bb80698769', // 填写appSecret
	'callbackUrl' => 'http://example.com/callback' // http 回调 callback 地址
));

/** 执行 OAuth 1.0a 用户认证授权 */
$passport->authenticate();

/** 获取授权用户的基本档案信息，并打印出来 */
var_dump($passport->getUserData('profile'));
```

## 联系作者

Copyright (c) 2012 Situos Inc. ([http://www.situos.com][situos])

* [反馈Bug][issues]
* [问题咨询][issues]

## License

The MIT license

[ZF]: http://zendframework.com
[API]: http://gd.10086.cn/passport/open/wiki
[OAuth]: http://oauth.net/
[issues]: https://github.com/cnsaturn/cmcc-passport-open-php-sdk/issues
[situos]: http://www.situos.com/