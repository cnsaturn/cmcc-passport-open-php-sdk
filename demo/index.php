<?php
/**
 * just a demo
 *
 * @link https://github.com/cnsaturn/cmcc-passport-open-php-sdk
 */

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
/** 获取授权用户的基本档案信息 */
var_dump($open_sdk->getUserData('profile'));

/** 更多方法请参考 README.md 文件 */
