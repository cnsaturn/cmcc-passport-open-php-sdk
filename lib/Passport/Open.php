<?php
/**
 * CMCC Passport Open SDK
 *
 * The MIT License (MIT)
 * Copyright (c) 2012 Situos Inc., http://www.situos.com
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the “Software”), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category   CMCC
 * @package	   Passport_Open
 * @copyright  Copyright (c) 2012 Situos Inc. (http://www.situos.com)
 * @license	   The MIT License (MIT)
 */

/**
 * @category   CMCC
 * @package	   Passport_Open
 * @copyright  Copyright (c) 2012 Situos Inc. (http://www.situos.com)
 * @license	   http://framework.zend.com/license/new-bsd	 New BSD License
 */
class Passport_Open
{
	/**
	 * 是否DEBUG 模式
	 */
	const DEBUG = FALSE;

	/**
	 * API URI 前缀
	 */
	const API_ENDPOINT_URL = 'http://120.197.230.234/passport/api';

	/**
	 * OAuth 鉴权 URI 前缀
	 */
	const OAUTH_ENDPOINT_URL = 'http://120.197.230.234/passport/oauth';

	/**
	 * OAuth 1.0a 鉴权参数
	 *
	 * @var array
	 */
	protected $_options = array();

	/**
	 * Zend OAuth 客户端对象
	 *
	 * @var Zend_Oauth_Consumer
	 */
	protected $_consumer;

	/**
	 * OAuth Token 令牌对象
	 *
	 * @var Zend_Oauth_Token
	 */
	protected $_token;

	/**
	 * 处理 API 请求的 http 客户端对象
	 *
	 * @var Zend_Http_Client
	 */
	protected $_client;

	/**
	 * 解析函数用来自定义 OAuth 鉴权参数 $params
	 *
	 * @param  array $params		 OAuth鉴权参数
	 */
	public function __construct($params)
	{
		set_include_path(dirname(dirname(__FILE__)) . PATH_SEPARATOR . get_include_path());

		// 检查必填参数
		foreach(array('consumerKey', 'consumerSecret', 'callbackUrl') as $key)
		{
			if( ! isset($params[$key]))
			{
				die("缺少 Oauth 鉴权参数：$key");
			}
		}

		require_once('Zend/Oauth/Consumer.php');

		// 设置 OAuth 鉴权参数
		$this->_options = array(
			'siteUrl' => self::OAUTH_ENDPOINT_URL,
			'callbackUrl' => $params['callbackUrl'],
			'consumerKey' => $params['consumerKey'],
			'consumerSecret' => $params['consumerSecret'],
			'version' => '1.0'
		);

		$this->_consumer = new Zend_Oauth_Consumer($this->_options);

		// 确保 Session 可用，并用来存储 Request Token 和 Access Token
		if(session_id() == '')
		{
			session_start();
		}
	}

	/**
	 * 实现 OAuth 鉴权
	 *
	 * @return bool
	 */
	public function authenticate()
	{
		// Case #1: 已获取有效的 Access Token
		// 获取的 Access Token 永久有效，除非用户手动取消授权或第三方在申请 Access token 时动态设置，
		// 详见开放平台上的 Oauth 文档说明。
		if(isset($_SESSION['ACCESS_TOKEN']))
		{
			// 从 Session 中恢复 Access Token
			$this->_token = unserialize($_SESSION ['ACCESS_TOKEN']);
			// 创建符合 OAuth 规范的 http 请求
			$this->_client = $this->_token->getHttpClient($this->_options);
			return TRUE;
		}

		// Case #2: 已成功获取 Request Token，但尚未获取 Access token
		if( ! empty($_GET))
		{
			// 通过解析 URL 中的 GET 字符串，使用 Request Token 交换 Access Token
			$this->_token = $this->_consumer->getAccessToken($_GET, unserialize($_SESSION['REQUEST_TOKEN']));

			// 记录本次获取的 Access Token，以便后续请求复用
			$_SESSION['ACCESS_TOKEN'] = serialize($this->_token);
			// 创建符合 OAuth 规范的 http 请求
			$this->_client = $this->_token->getHttpClient($this->_options);
			return TRUE;
		}

		// Case #3: 尚未获取 Request Token
		// 从开放平台申请一个 Request Token
		$this->_token = $this->_consumer->getRequestToken();

		// 将 Request Token 临时存储起来
		$_SESSION ['REQUEST_TOKEN'] = serialize($this->_token);

		// 跳转到用户授权页面，等待用户授权操作
		// 用户授权成功后，将返回本地所设置的 callback 地址
		$this->_consumer->redirect();
		return TRUE;
	}
}