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
	 * 是否 DEBUG 模式
	 */
	const DEBUG = TRUE;

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
		$_SESSION['REQUEST_TOKEN'] = serialize($this->_token);

		// 跳转到用户授权页面，等待用户授权操作
		// 用户授权成功后，将返回本地所设置的 callback 地址
		$this->_consumer->redirect();
		return TRUE;
	}

	/**
	 * 获取用户数据; 通过 HTTP GET 方法获取用户基本信息(profile)、用户职业信息(careers)、
	 * 用户收件地址(recipients)和用户联系人信息(contacts)
	 *
	 *
     * @param  string $api 支持API：{'profile', 'careers', 'recipients', 'contacts'}
	 * @return array
	 */
	public function getUserData($api = 'profile')
	{
		// 是否支持指定 $api
		$api = strtolower($api);
		if( ! in_array($api, array('profile', 'careers', 'recipients', 'contacts')))
		{
			return FALSE;
		}

		// 发起API请求，获得响应字符串
		$content = $this->_prepareRequest("/user/$api", Zend_Http_Client::GET);

		// 正常响应内容默认以 Json 数据格式返回
		require_once('Zend/Json.php');

		if( ! self::DEBUG)
		{
			return Zend_Json::decode($content);
		}

		echo Zend_Json::prettyPrint($content, array("indent" => " "));
	}

	/**
	 * 更新当前关联用户基本信息
	 *
	 *
     * @param  array $data 需要更新的信息数组
	 * @return array 更新后的用户基本信息
	 */
	public function updateUserProfile($data)
	{
		// 正常响应内容默认以 Json 数据格式返回
		require_once('Zend/Json.php');

		// 发起API请求，获得响应字符串
		$content = $this->_prepareRequest(
			'/user/profile', 
			Zend_Http_Client::PUT, 
			Zend_Json::encode($data)
		);

		if( ! self::DEBUG)
		{
			return Zend_Json::decode($content);
		}

		echo Zend_Json::prettyPrint($content, array("indent" => " "));
	}

    /**
     * 发起指定 API 请求
     *
     * @param  string $api API路径
     * @param  string $method 请求方法方法
     * @param  string $rawPayLoad 经过处理和合理编码后的http内容
     * @return string API响应
     * @throws Zend_Http_Client_Exception
     */
	private function _prepareRequest($api, $method, $rawPayLoad = null)
	{
		// 设置 API 路径
		$this->_client->setUri(self::API_ENDPOINT_URL . $api);
		// 设置 API HTTP Verbs 方法 (GET, POST, DELETE or PUT)
		$this->_client->setMethod($method);
		// 设置 HTTP 请求数据类型
		$this->_client->setHeaders('Content-Type', 'application/json');
		// 设置 HTTP Body Payload
		switch ($method) 
		{
            case Zend_Http_Client::GET:
            case Zend_Http_Client::DELETE:
                break;
            case Zend_Http_Client::PUT:
            case Zend_Http_Client::POST:
                $this->_client->setRawData($rawPayLoad);
                break;
        }
		// 获取响应数据
		$response = $this->_client->request();
		// 解析 HTTP Body Payload
		return $response->getBody();
	}
}