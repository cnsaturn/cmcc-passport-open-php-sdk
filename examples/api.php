<?php
	/** 将Passport_Open SDK类包含进来 */
	require_once dirname(dirname(__FILE__)) . '/lib/Passport/Open.php';
	
	/** 初始化 Passport_Open 类 */
	$passport = new Passport_Open(array(
		'consumerKey' => '2937daedfa310c826d8727384ca8979304f03a6c9', // 填写你在开放平台申请到的应用appKey
		'consumerSecret' => '2dab22f40f108d3c619804bb80698769', // 填写你在开放平台申请到的应用appSecret
		'callbackUrl' => 'http://' . $_SERVER ['HTTP_HOST'] . $_SERVER['PHP_SELF'] // 回调 callback 地址
	));
	
	/** 执行 OAuth 1.0a 用户认证授权 */
	$passport->authenticate();
?>
<!DOCTYPE html>
<head>
<meta charset="utf-8" />
<title>API测试</title>
<style type="text/css">
body {font-size:12px; font-family: "Helvetica Neue", "Luxi Sans", "DejaVu Sans", Tahoma, "Hiragino Sans GB", STHeiti !important;text-align:left}
input[type="text"] {margin:5px;width:350px;}
dd {cursor:pointer;margin-top:5px;line-height:22px;}
a{color:#000;text-decoration:none}
dd:hover,a:hover {color:#FF0000}

.greyborder{border:1px #CCCCCC dashed;margin:10px;padding:10px;float:left}
.redborder{border:1px #FF0000 dashed;margin:10px;padding:10px}
</style>
</head>
<body>
<header>
	<h1>测试全部api</h1>
</header>
<div class="greyborder">
  <dl>
	<dt>用户帐号API</dt>
		<dd><a href="?profile">读取当前用户</a></dd>
		<dd><a href="?editProfile">更新当前用户</a></dd>
	<dt>用户职业信息API</dt>
		<dd><a href="?careers">读取当前用户职业信息</a></dd>
		<dd><a href="?target=careers&id=0">创建当前用户新的职业信息</a></dd>
		<dd><a href="?careers">更新当前用户职业信息</a></dd>
		<dd><a href="?careers">删除当前用户职业信息</a></dd>
	<dt>用户收件地址API</dt>
		<dd><a href="?recipients">读取当前用户收件地址</a></dd>
		<dd><a href="?target=recipients&id=0">创建当前用户收件地址</a></dd>
		<dd><a href="?recipients">更新当前用户收件地址</a></dd>
		<dd><a href="?recipients">删除当前用户指定收件地址</a></dd>
	<dt>用户联系人API</dt>
		<dd><a href="?contacts">读取当前用户联系人</a></dd>
		<dd><a href="?target=contacts&id=0">创建当前用户新的联系人</a></dd>
		<dd><a href="?contacts">更新当前用户指定联系人</a></dd>
		<dd><a href="?contacts">删除当前用户指定联系人</a></dd>
  </dl>
</div>
<div class="greyborder" id="box">
<?php

     /* 
	 	用户帐号API 
		返回的是一维数组
	 */
	
	//读取当前用户信息
	if($_SERVER["QUERY_STRING"] == "profile")
	{
		echo '<pre>';
		print_r($passport->getUserData("profile"));
	}
	
	//显示当前用户可以修改的项
	if($_SERVER["QUERY_STRING"] == "editProfile")
	{
		$profile = $passport->getUserData("profile");
		?>
		<form action="?" method="post" enctype="multipart/form-data">
			<input placeholder="用户昵称" name="nick_name" type="text" id="nick_name" value="<?php echo $profile["nick_name"] ?>"><br />
			<input name="gender" type="radio" id="female" value="female" <?php if($profile["gender"]=="female") echo 'checked'; ?>><label for="female">female</label> 
			<input name="gender" type="radio" value="male" id="male" <?php if($profile["gender"]=="male") echo 'checked'; ?>><label for="male">male</label> <br />
			<input placeholder="所在省份（如：广东省）" name="province" type="text" id="province" value="<?php echo $profile["province"] ?>"><br />
			<input placeholder="所在城市（如：广州市）" name="city" type="text" id="city" value="<?php echo $profile["city"] ?>"><br />
			<input placeholder="所在区域（如：天河区）" name="area" type="text" id="area" value="<?php echo $profile["area"] ?>"><br />
			<input placeholder="出生年（如：2012）" name="birth_year" type="text" id="birth_year" value="<?php echo $profile["birth_year"] ?>"><br />
			<input placeholder="出生月（如：4）" name="birth_month" type="text" id="birth_month" value="<?php echo $profile["birth_month"] ?>"><br />
			<input placeholder="出生日（如：1）" name="birth_day" type="text" id="birth_day" value="<?php echo $profile["birth_day"] ?>"><br />
			<input placeholder="QQ号码" name="im_qq" type="text" id="im_qq" value="<?php echo $profile["im_qq"] ?>"><br />
			<input placeholder="MSN账号" name="im_msn" type="text" id="im_msn" value="<?php echo $profile["im_msn"] ?>"><br />
			<input placeholder="Gtalk账号" name="im_gtalk" type="text" id="im_gtalk" value="<?php echo $profile["im_gtalk"] ?>"><br />
			<input name="" type="submit" value="提交">
			<input name="editProfile" type="hidden" value="1">
		</form>
		<?php
	}
	
	//显示当前用户可以修改的项，然后修改，提交到这里，更新当前用户信息
	if(isset($_POST["editProfile"]))
	{
		$result = $passport->updateUserProfile(array(
					"nick_name"		=>	$_POST["nick_name"],
					"gender"		=>	$_POST["gender"],
					"province"		=>	$_POST["province"],
					"city"			=>	$_POST["city"],
					"area"			=>	$_POST["area"],
					"birth_year"	=>	$_POST["birth_year"],
					"birth_month"	=>	$_POST["birth_month"],
					"birth_day"		=>	$_POST["birth_day"],
					"im_qq"			=>	$_POST["im_qq"],
					"im_msn"		=>	$_POST["im_msn"],
					"im_gtalk"		=>	$_POST["im_gtalk"],
				));
		echo '<pre>';
		print_r($result);
	}
	
	/*
		用户职业信息
		用户收货地址
		用户联系人
		返回的是二维数组
	*/
	
	//读取用户职业信息，用户收货地址，用户联系人
	if(	   $_SERVER["QUERY_STRING"] == "careers"
		|| $_SERVER["QUERY_STRING"] == "recipients"
		|| $_SERVER["QUERY_STRING"] == "contacts")
	{		
		foreach($passport->getUserData($_SERVER["QUERY_STRING"]) as $key => $val)
		{
			?>
			<pre>
			<div class="redborder"><?php print_r($val); ?>
				<input type="button" value="编辑" onClick="window.location.href='?target=<?php echo $_SERVER["QUERY_STRING"]; ?>&id=<?php echo $val['id'];?>'">	<input type="button" value="删除" onClick="window.location.href='?del<?php echo $_SERVER["QUERY_STRING"];?>=<?php echo $val['id'];?>'">
			</div>
			<?php
		}
	}
	
	//删除当前用户职业信息
	if(isset($_GET["delcareers"]))
	{
		$result = $passport->deleteUserCareer($_GET["delcareers"]);
		var_dump( $result);
	}
	//删除当前用户收货地址
	if(isset($_GET["delrecipients"]))
	{
		$result = $passport->deleteUserRecipient($_GET["delrecipients"]);
		var_dump( $result);
	}
	//删除当前用户联系人
	if(isset($_GET["delcontacts"]))
	{
		$result = $passport->deleteUserContact($_GET["delcontacts"]);
		var_dump( $result);
	}
	
	//创建，编辑当前用户的职业信息
	if(isset($_GET["target"]) && $_GET["target"] == "careers")
	{
		$province 			= 	"";
		$city				=	"";
		$area				=	"";
		$company_address	=	"";
		$company_name		=	"";
		$job_start_at		=	"";
		$job_end_at			=	"";
		$position			=	"";
		$id					=	$_GET['id'];
		
		if($id != 0)
		{
			foreach($passport->getUserData('careers') as $key => $val)
			{
				if($val['id'] == $id)
				{
					$province 			= 	$val['province'];
					$city				=	$val['city'];
					$area				=	$val['area'];
					$company_address	=	$val['company_address'];
					$company_name		=	$val['company_name'];
					$job_start_at		=	$val['job_start_at'];
					$job_end_at			=	$val['job_end_at'];
					$position			=	$val['position'];
				}
			}
		}
		
		?>
		<form action="?" method="post" enctype="multipart/form-data">
			<input name="province" type="text" id="province" placeholder="公司所在省份" required value="<?php echo $province; ?>"><br />
			<input name="city" type="text" id="city" placeholder="公司所在城市" required value="<?php echo $city; ?>"><br />
			<input name="area" type="text" id="area" placeholder="公司所在区域" required value="<?php echo $area; ?>"><br />
			<input name="company_address" type="text" id="company_address" placeholder="公司街道地址" required value="<?php echo $company_address; ?>"><br />
			<input name="company_name" type="text" id="company_name" placeholder="公司名称" required value="<?php echo $company_name; ?>"><br />
			<input name="job_start_at" type="text" id="job_start_at" placeholder="入职年份" required value="<?php echo $job_start_at; ?>"><br />
			<input name="job_end_at" type="text" id="job_end_at" placeholder="离职年份。填写“至今”表示至今尚未离职。" required value="<?php echo $job_end_at; ?>"><br />
			<input name="position" type="text" id="position" placeholder="职位" required value="<?php echo $position; ?>"><br />
			<input name="" type="submit" value="提交">
			<input name="careersid" type="hidden" value="<?php echo $id; ?>">
		</form>
		<?php
	}
	
	//创建，编辑当前用户的职业信息， 然后提交到这里
	if(isset($_POST["careersid"]))
	{
		//编辑
		if($_POST["careersid"] != 0)
		{
			$result = $passport->updateUserCareer($_POST["careersid"], array(
								"province"			=>	$_POST["province"],
								"city"				=>	$_POST["city"],
								"area"				=>	$_POST["area"],
								"company_address"	=>	$_POST["company_address"],
								"company_name"		=>	$_POST["company_name"],
								"job_start_at"		=>	$_POST["job_start_at"],
								"job_end_at"		=>	$_POST["job_end_at"],
								"position"			=>	$_POST["position"],
							));
		}
		//创建
		else
		{
			$result = $passport->createUserCareer(array(
					"province"			=>	$_POST["province"],
					"city"				=>	$_POST["city"],
					"area"				=>	$_POST["area"],
					"company_address"	=>	$_POST["company_address"],
					"company_name"		=>	$_POST["company_name"],
					"job_start_at"		=>	$_POST["job_start_at"],
					"job_end_at"		=>	$_POST["job_end_at"],
					"position"			=>	$_POST["position"],
				));
		}
		echo '<pre>';
		print_r($result);
	}
	
	//创建，编辑当前用户的收件地址
	if(isset($_GET["target"]) && $_GET["target"] == "recipients")
	{
		$recipient 			= 	"";
		$province 			= 	"";
		$city				=	"";
		$area				=	"";
		$postal_address		=	"";
		$postal_code		=	"";
		$mobile_phone		=	"";
		$contact_phone		=	"";
		$delivery_time		=	"work_days";
		$id					=	$_GET['id'];
		
		if($id != 0)
		{
			foreach($passport->getUserData('recipients') as $key => $val)
			{
				if($val['id'] == $id)
				{
					$recipient 			= 	$val['recipient'];
					$province 			= 	$val['province'];
					$city				=	$val['city'];
					$area				=	$val['area'];
					$postal_address		=	$val['postal_address'];
					$postal_code		=	$val['postal_code'];
					$mobile_phone		=	$val['mobile_phone'];
					$contact_phone		=	$val['contact_phone'];
					$delivery_time		=	$val['delivery_time'];
				}
			}
		}
		
		?>
		<form action="?" method="post" enctype="multipart/form-data">
			<input name="recipient" type="text" id="recipient" placeholder="收件人姓名" required value="<?php echo $recipient; ?>"><br />
			<input name="province" type="text" id="province" placeholder="收件人所在省份" required value="<?php echo $province; ?>"><br />
			<input name="city" type="text" id="city" placeholder="收件人所在城市" required value="<?php echo $city; ?>"><br />
			<input name="area" type="text" id="area" placeholder="收件人所在区域" required value="<?php echo $area; ?>"><br />
			<input name="postal_address" type="text" id="postal_address" placeholder="街道地址" required value="<?php echo $postal_address; ?>"><br />
			<input name="postal_code" type="text" id="postal_code" placeholder="邮政编码" required value="<?php echo $postal_code; ?>"><br />
			<input name="mobile_phone" type="text" id="mobile_phone" placeholder="手机号码" required value="<?php echo $mobile_phone; ?>"><br />
			<input name="contact_phone" type="text" id="contact_phone" placeholder="固定电话" required value="<?php echo $contact_phone; ?>"><br />
			送货时间： 
			<input name="delivery_time" type="radio" value="work_days" id="work_days" <?php if($delivery_time=="work_days") echo 'checked'; ?>> <label for="work_days">工作日</label>
			<input name="delivery_time" type="radio" value="all_days" id="all_days" <?php if($delivery_time=="all_days") echo 'checked'; ?>> <label for="all_days">任意时间</label>
			<input name="delivery_time" type="radio" value="weekends" id="weekends" <?php if($delivery_time=="weekends") echo 'checked'; ?>> <label for="weekends">周末</label><br />			
			<input name="" type="submit" value="提交">
			<input name="recipientsid" type="hidden" value="<?php echo $id; ?>">
		</form>
		<?php
	}
	
	//创建，编辑当前用户的职业信息， 然后提交到这里
	if(isset($_POST["recipientsid"]))
	{
		//编辑
		if($_POST["recipientsid"] != 0)
		{
			$result = $passport->updateUserRecipient($_POST["recipientsid"], array(
								"recipient"			=>	$_POST["recipient"],
								"province"			=>	$_POST["province"],
								"city"				=>	$_POST["city"],
								"area"				=>	$_POST["area"],
								"postal_address"	=>	$_POST["postal_address"],
								"postal_code"		=>	$_POST["postal_code"],
								"mobile_phone"		=>	$_POST["mobile_phone"],
								"contact_phone"		=>	$_POST["contact_phone"],
								"delivery_time"		=>	$_POST["delivery_time"],
							));
		}
		//创建
		else
		{
			$result = $passport->createUserRecipient(array(
					"recipient"			=>	$_POST["recipient"],
					"province"			=>	$_POST["province"],
					"city"				=>	$_POST["city"],
					"area"				=>	$_POST["area"],
					"postal_address"	=>	$_POST["postal_address"],
					"postal_code"		=>	$_POST["postal_code"],
					"mobile_phone"		=>	$_POST["mobile_phone"],
					"contact_phone"		=>	$_POST["contact_phone"],
					"delivery_time"		=>	$_POST["delivery_time"],
				));
		}
		echo '<pre>';
		print_r($result);
	}
	
	//创建，编辑当前用户的联系人
	if(isset($_GET["target"]) && $_GET["target"] == "contacts")
	{
		$group 			= 	"";
		$real_name 		= 	"";
		$email			=	"";
		$mobile_phone	=	"";
		$work_phone		=	"";
		$nick_name		=	"";
		$birth_year		=	"";
		$birth_month	=	"";
		$birth_day		=	"";
		$company_name	=	"";
		$id				=	$_GET['id'];
		
		if($id != 0)
		{
			foreach($passport->getUserData('contacts') as $key => $val)
			{
				if($val['id'] == $id)
				{
					$group 			= 	$val['group'];
					$real_name 		= 	$val['real_name'];
					$email			=	$val['email'];
					$mobile_phone	=	$val['mobile_phone'];
					$work_phone		=	$val['work_phone'];
					$nick_name		=	$val['nick_name'];
					$birth_year		=	$val['birth_year'];
					$birth_month	=	$val['birth_month'];
					$birth_day		=	$val['birth_day'];
					$company_name	=	$val['company_name'];
				}
			}
		}
		
		?>
		<form action="?" method="post" enctype="multipart/form-data">
			<input name="real_name" type="text" id="real_name" placeholder="联系人姓名" required value="<?php echo $real_name; ?>"> <br />
			所在群组：
			<input name="group" type="radio" value="" id="no" <?php if($group=="") echo 'checked'; ?>> <label for="no">不设置分组</label> 
			<input name="group" type="radio" value="co-workers" id="co-workers" <?php if($group=="co-workers") echo 'checked'; ?>> <label for="co-workers">同事</label> 
			<input name="group" type="radio" value="family" id="family" <?php if($group=="family") echo 'checked'; ?>> <label for="family">家人</label> 
			<input name="group" type="radio" value="favorites" id="favorites" <?php if($group=="favorites") echo 'checked'; ?>> <label for="favorites">收藏</label> 
			<input name="group" type="radio" value="friends" id="friends" <?php if($group=="friends") echo 'checked'; ?>> <label for="friends">朋友</label> 
			<input name="group" type="radio" value="vip" id="vip" <?php if($group=="vip") echo 'checked'; ?>> <label for="vip">特别关注</label><br />	
			<input name="email" type="text" id="email" placeholder="联系人email" value="<?php echo $email; ?>"><br />
			<input name="mobile_phone" type="text" id="mobile_phone" placeholder="联系人手机" required value="<?php echo $mobile_phone; ?>"><br />
			<input name="work_phone" type="text" id="work_phone" placeholder="联系人电话" value="<?php echo $work_phone; ?>"><br />
			<input name="nick_name" type="text" id="nick_name" placeholder="联系人昵称" value="<?php echo $nick_name; ?>"><br />
			<input name="birth_year" type="text" id="birth_year" placeholder="联系人出生年" value="<?php echo $birth_year; ?>"><br />
			<input name="birth_month" type="text" id="birth_month" placeholder="联系人出生月" value="<?php echo $birth_month; ?>"><br />
			<input name="birth_day" type="text" id="birth_day" placeholder="联系人出生日" value="<?php echo $birth_day; ?>"><br />
			<input name="company_name" type="text" id="company_name" placeholder="所在公司" value="<?php echo $company_name; ?>"><br />
			<input name="" type="submit" value="提交">
			<input name="contactsid" type="hidden" value="<?php echo $id; ?>">
		</form>
		<?php
	}
	
	//创建，编辑当前用户的联系人， 然后提交到这里
	if(isset($_POST["contactsid"]))
	{
		//编辑
		if($_POST["contactsid"] != 0)
		{
			$result = $passport->updateUserContact($_POST["contactsid"], array(
								"group"			=>	$_POST["group"],
								"real_name"		=>	$_POST["real_name"],
								"email"			=>	$_POST["email"],
								"mobile_phone"	=>	$_POST["mobile_phone"],
								"work_phone"	=>	$_POST["work_phone"],
								"nick_name"		=>	$_POST["nick_name"],
								"birth_year"	=>	$_POST["birth_year"],
								"birth_month"	=>	$_POST["birth_month"],
								"birth_day"		=>	$_POST["birth_day"],
								"company_name"	=>	$_POST["company_name"],
							));
		}
		//创建
		else
		{
			$result = $passport->createUserContact(array(
					"group"			=>	$_POST["group"],
					"real_name"		=>	$_POST["real_name"],
					"email"			=>	$_POST["email"],
					"mobile_phone"	=>	$_POST["mobile_phone"],
					"work_phone"	=>	$_POST["work_phone"],
					"nick_name"		=>	$_POST["nick_name"],
					"birth_year"	=>	$_POST["birth_year"],
					"birth_month"	=>	$_POST["birth_month"],
					"birth_day"		=>	$_POST["birth_day"],
					"company_name"	=>	$_POST["company_name"],
				));
		}
		echo '<pre>';
		print_r($result);
	}
?>
</div>
<br style="clear:both;" />
<h2>请注意：</h2>
<ul>
	<li>请在<a href="http://firefox.com.cn/">Firefox</a>或<a href="http://www.google.com/chrome">Chrome</a>中打开本页面。</li>
	<li><a href="https://github.com/cnsaturn/cmcc-passport-open-php-sdk/issues">有问题或反馈Bug，请点击此处。</a></li>
</ul>
<em>Brought to you by <a href="http://www.situos.com/">Situos</a>.</em>
</body>
</html>