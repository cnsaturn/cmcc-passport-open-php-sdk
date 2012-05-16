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
 * @license	   The MIT License (MIT)
 */
class Passport_Open
{
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
	 * @param	array $params OAuth鉴权参数
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
			$this->_token = unserialize($_SESSION['ACCESS_TOKEN']);
			// 创建符合 OAuth 规范的 http 请求
			$this->_client = $this->_token->getHttpClient($this->_options);
			return TRUE;
		}

		// Case #2: 已成功获取 Request Token，但尚未获取 Access token
		if( ! empty($_GET))
		{
			// 通过解析 URL 中的 GET 字符串，使用 Request Token 交换 Access Token
			try
			{
			    $this->_token = $this->_consumer->getAccessToken($_GET, unserialize($_SESSION['REQUEST_TOKEN']));
			}
			catch (Zend_Oauth_Exception $e)
			{
			    echo 'Caught exception: ',  $e->getMessage(), "\n";
			    exit;
			}

			// 记录本次获取的 Access Token，以便后续请求复用
			$_SESSION['ACCESS_TOKEN'] = serialize($this->_token);
			// 创建符合 OAuth 规范的 http 请求
			$this->_client = $this->_token->getHttpClient($this->_options);
			return TRUE;
		}

		// Case #3: 尚未获取 Request Token
		// 从开放平台申请一个 Request Token
		try
		{
			$this->_token = $this->_consumer->getRequestToken();
		}
		catch (Zend_Oauth_Exception $e)
		{
			echo 'Caught exception: ',  $e->getMessage(), "\n";
			exit;
		}


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

		return $this->_makeRequest("/user/$api", Zend_Http_Client::GET);
	}

	/**
	 * 更新当前用户基本信息
	 *
	 *
	 * @param  array $data 需要更新的信息数组
	 * @return array 更新后的用户基本信息
	 */
	public function updateUserProfile($data)
	{
		return $this->_makeRequest('/user/profile', Zend_Http_Client::PUT, $data);
	}

	/**
	 * 创建当前用户新的职业信息
	 *
	 *
	 * @param  array $data 新的信息数组
	 * @return array 创建成功后的职业信息
	 */
	public function createUserCareer($data)
	{
		return $this->_makeRequest('/user/careers', Zend_Http_Client::POST, $data);
	}

	/**
	 * 更新当前用户职业信息
	 *
	 *
	 * @param  int $id 职业信息条目ID
	 * @param  array $data 需要更新的信息数组
	 * @return array 更新后的信息条目
	 */
	public function updateUserCareer($id, $data)
	{
		return $this->_makeRequest("/careers/id/$id", Zend_Http_Client::PUT, $data);
	}

	/**
	 * 删除当前用户职业信息
	 *
	 *
	 * @param  int $id 职业信息条目ID
	 * @return bool TRUE表示删除成功，FALSE则删除失败
	 */
	public function deleteUserCareer($id)
	{
		return $this->_makeRequest("/careers/id/$id", Zend_Http_Client::DELETE);
	}

	/**
	 * 创建当前用户收件地址
	 *
	 *
	 * @param  array $data 新的信息数组
	 * @return array 创建成功后的收件地址
	 */
	public function createUserRecipient($data)
	{
		return $this->_makeRequest('/user/recipients', Zend_Http_Client::POST, $data);
	}

	/**
	 * 更新当前用户收件地址
	 *
	 *
	 * @param  int $id 收件地址条目ID
	 * @param  array $data 需要更新的信息数组
	 * @return array 更新后的收件地址
	 */
	public function updateUserRecipient($id, $data)
	{
		return $this->_makeRequest("/recipients/id/$id", Zend_Http_Client::PUT, $data);
	}

	/**
	 * 删除当前用户指定收件地址
	 *
	 *
	 * @param  int $id 收件地址条目ID
	 * @return bool TRUE表示删除成功，FALSE则删除失败
	 */
	public function deleteUserRecipient($id)
	{
		return $this->_makeRequest("/recipients/id/$id", Zend_Http_Client::DELETE);
	}

	/**
	 * 创建当前用户新的联系人
	 *
	 *
	 * @param  array $data 新的信息数组
	 * @return array 创建成功后的联系人条目
	 */
	public function createUserContact($data)
	{
		return $this->_makeRequest('/user/contacts', Zend_Http_Client::POST, $data);
	}

	/**
	 * 更新当前用户指定联系人
	 *
	 *
	 * @param  int $id 联系人条目ID
	 * @param  array $data 需要更新的信息数组
	 * @return array 更新后的联系人
	 */
	public function updateUserContact($id, $data)
	{
		return $this->_makeRequest("/contacts/id/$id", Zend_Http_Client::PUT, $data);
	}

	/**
	 * 删除当前用户指定联系人
	 *
	 *
	 * @param  int $id 收件地址条目ID
	 * @return bool TRUE表示删除成功，FALSE则删除失败
	 */
	public function deleteUserContact($id)
	{
		return $this->_makeRequest("/contacts/id/$id", Zend_Http_Client::DELETE);
	}

	/**
	 * 发起指定 API 请求
	 *
	 * @param  string $api API路径
	 * @param  string $method http请求Verbs方法
	 * @param  string|array $PayLoadData 经过处理和合理编码后的http内容或数组
	 * @return array|bool  解析后的API响应数据数组；或TRUE表示删除成功
	 */
	private function _makeRequest($api, $method, $PayLoadData = null)
	{
		// 正常响应内容默认以 Json 数据格式返回
		require_once('Zend/Json.php');

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
				$this->_client->setRawData(
					is_array($PayLoadData)
					? Zend_Json::encode($PayLoadData)
					: $PayLoadData
				);
				break;
		}

		// 获取响应数据
		try
		{
		   $response = $this->_client->request();
		}
		catch (Zend_Oauth_Exception $e)
		{
		    echo 'Caught exception: ',  $e->getMessage(), "\n";
		    exit;
		}
		
		// 解析 HTTP Body Payload
		$content = $response->getBody();
		// HTTP 响应码
		$status = $response->getStatus();

		// 根据 HTTP Body Payload 返回数据
		switch ($status)
		{
			// 删除成功返回TRUE
			case '204':
				return TRUE;
				break;
			// 其他操作直接返回解析后的http payload内容
			default:
				return Zend_Json::decode($content);
		}
	}
}