<?php
/**
 * 本演示实现了 OAuth 用户授权认证，并调用一个 API 获取当前用户信息
 *
 * @link https://github.com/cnsaturn/cmcc-passport-open-php-sdk
 */

/** 将Passport_Open SDK类包含进来 */
require_once dirname(dirname(__FILE__)) . '/lib/Passport/Open.php';

/** 初始化 Passport_Open 类 */
$open_sdk = new Passport_Open(array(
	'consumerKey' => '2937daedfa310c826d8727384ca8979304f03a6c9', // 填写你在开放平台申请到的应用appKey
	'consumerSecret' => '2dab22f40f108d3c619804bb80698769', // 填写你在开放平台申请到的应用appSecret
	'callbackUrl' => 'http://example.com/callback' // 回调 callback 地址(如当前页地址)
));

/** 执行 OAuth 1.0a 用户认证授权 */
$open_sdk->authenticate();

/** 获取授权用户的基本档案信息，并打印出来 */
var_dump($open_sdk->getUserData('profile'));

/** 更多 API 方法请参考官方wiki 或 api.php 文件 */