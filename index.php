#!/usr/bin/php
<?php
if (php_sapi_name() != "cli") exit ("please use terminal run.\n");

include("constants.php");
include("commons.php");
include("core.php");
include("app.php");

// $a = unserialize(read_file("parse.ini"));
// $a['call'] = "呼叫格式如下：\\\\n@call:这里输入你的内容";
// $a['help'][1] = "webqq version 0.1\\\\nversion <=> 版本信息\\\\ndate <=> 当前时间\\\\nexpress <=> 查看快递详细信息\\\\ncall <=> 呼叫人工";
// $a['给大家打个招呼'] = "大家好，我是机器人麻花,欢迎大家跟我聊天，我会越来越聪明的！\\\\n你也可以加我QQ跟我聊天噢\\\\n可输入help查询相关指令";
// write_file("parse.ini",serialize($a));
// print_r($a);
// exit;
// 验证码
$verify = check_verify(USERNAME);
if (is_array($verify) && $verify[0] == 0)
{
	log_message("debug","装载验证码成功");
}
else
{
	log_message("error","装载验证码错误，可能存在图片验证码");
	exit("装载验证码错误，可能存在图片验证码\n");
}
// 密码
$passwd = jspassword(PASSWORD, $verify[2], $verify[1]);

// 登陆
$login = login(USERNAME , $passwd, $verify[1]);

if (is_array($login) && $login['4'] == "登录成功！")
{
	log_message("debug","初次登陆成功:".$login['5']);
}
elseif (isset($login[4]))
{
	log_message("error","初次登陆失败:".$login[4]);
	exit("初次登陆失败\n");
}
else
{
	log_message("error","初次登陆失败");
	exit("初次登陆失败\n");
}

// 获取cookie信息
$cookie = get_cookie();
// 生成客户端id
$cookie['clientid'] = mt_rand(50888888,80888888);

// 真正的上线
$login = obj_to_array(json_decode(login2($cookie['ptwebqq'], $cookie['clientid'])));

if ($login['retcode'] == 0)
{
	$cookie["login"] = $login['result'];
	log_message("debug","已成功上线");
}
else
{
	log_message("error","登陆失败,可能原因：vfwebqq参值不正确");
	exit("登陆失败,可能原因：vfwebqq参值不正确");
}

// 获取好友列表
// $friend_list = obj_to_array(json_decode(get_user_friend($cookie['login']['vfwebqq'])));
// 获取群列表
$group_name_list = obj_to_array(json_decode(get_group_name_list_mask($cookie['login']['vfwebqq'])));

// 获取管理员id
// $my_uin = get_friend_uin($friend_list);


//http://s.web2.qq.com/api/get_friend_uin2?tuin={$tuin}&verifysession=&type=1&code=&vfwebqq={$vfwebqq}c&t=136610165502
print_r($cookie);
//var_dump($my_uin);
// print_r($friend_list);
print_r($group_name_list);

// 回复数据
$reply = unserialize(read_file("parse.ini"));

$face = array(
		"9"=>array(
				"哭啥？有啥不开心的？说出来让我们开心一下"
		),
		"5"=>array(
				"不哭，乖",
				"哭啥？有啥不开心的？说出来让我们开心一下"
		),
		"13"=>array(
				"笑得这么开心？",
				"在笑哪个妹子呢？"
		),
		"58"=>array(
				"老板，来根大麻",
				"红塔山多少钱一包？"
		),
		"105"=>array(
				"鄙视俺的人那么多，你排队去",
				"不许插队，吼吼！～～～"
		),
);
// =======================================================
// |新开进程进行监控
// =======================================================

while(TRUE)
{
	$d = poll($cookie['login']['psessionid'], $cookie['clientid']);
	$msg = obj_to_array(json_decode($d));
	echo $d;
	if ($msg['retcode'] === 0)
	{
		foreach ($msg['result'] as $v)
		{
// 			print_r($v);
			// 针对个人
			if ($v['poll_type'] == "message")
			{
				
				$data = trim($v['value']['content'][1]);
				// 处理换行
				$data = implode("\\\\n",explode("\n",$data));
				
				// 普通回复
				if (array_key_exists($data, $reply))
				{
					if (is_array($reply[$data]) && $count=count($reply[$data]))
					{
						send_buddy_msg($v['value']['from_uin'], $reply[$data][mt_rand(0, $count-1)], $cookie['login']['psessionid'], $cookie['clientid']);
					}
					else
					{
						send_buddy_msg($v['value']['from_uin'], $reply[$data], $cookie['login']['psessionid'], $cookie['clientid']);
					}
					continue;
				}
				
				// 呼叫人工回复
				if ($info = is_call($data))
				{
					send_buddy_msg(4092490351, "friend:{$v['value']['from_uin']}--->{$info}", $cookie['login']['psessionid'], $cookie['clientid']);
					continue;
				}
				
				// 干扰回复
				if ($info = is_from_admin_reply($data))
				{
					if ($info[0] == "qun")
					{
						send_qun_msg($info[1], "来自call的回复--->{$info[2]}", $cookie['login']['psessionid'], $cookie['clientid']);
					}
					
					if ($info[0] == "friend")
					{
						send_buddy_msg($info[1], "来自call的回复--->{$info[2]}", $cookie['login']['psessionid'], $cookie['clientid']);
					}
					
					continue;
				}
				
				// 快递
				if ($rep = is_express($data))
				{
					send_buddy_msg($v['value']['from_uin'], $rep, $cookie['login']['psessionid'], $cookie['clientid']);
					continue;
				}
				
				// 数学
				if (($rep = math($data)) !== NULL)
				{
					send_buddy_msg($v['value']['from_uin'], $rep, $cookie['login']['psessionid'], $cookie['clientid']);
					continue;
				}
				
				// 学习模式
				if (is_study($data))
				{
					send_buddy_msg($v['value']['from_uin'], "已进入学习模式.", $cookie['login']['psessionid'], $cookie['clientid']);
					$reply = unserialize(read_file(REPLY));
					continue;
				}
				
				// ip查询
				if($ip_data = is_ip($data))
				{
					send_buddy_msg($v['value']['from_uin'], $ip_data, $cookie['login']['psessionid'], $cookie['clientid']);
					continue;
				}
				// 职能取词
				if (($data = is_match($data)) && array_key_exists($data, $reply))
				{
					if (is_array($reply[$data]) && $count=count($reply[$data]))
					{
						send_buddy_msg($v['value']['from_uin'], $reply[$data][mt_rand(0, $count-1)], $cookie['login']['psessionid'], $cookie['clientid']);
					}
					else
					{
						send_buddy_msg($v['value']['from_uin'], $reply[$data], $cookie['login']['psessionid'], $cookie['clientid']);
					}
					continue;
				}
				send_buddy_msg($v['value']['from_uin'], "我是机器人麻花,谢谢您和我聊天!\\\\n输入help查询指令", $cookie['login']['psessionid'], $cookie['clientid']);
				continue;
			}
			
			// 针对群
			if ($v['poll_type'] == "group_message")
			{
				// 处理简单表情
				foreach ($v['value']['content'] as $font_face_string)
				{
					if (is_array($font_face_string) && $font_face_string[0] == "face")
					{
						if (array_key_exists($font_face_string[1], $face))
						{
							if ($count = count($face[$font_face_string[1]]))
							{
								send_qun_msg($v['value']['from_uin'], $face[$font_face_string[1]][mt_rand(0, $count-1)], $cookie['login']['psessionid'], $cookie['clientid']);
							}
							else
							{
								send_qun_msg($v['value']['from_uin'], $face[$font_face_string[1]], $cookie['login']['psessionid'], $cookie['clientid']);
							}
						}
						continue 2;
					}
				}
				
				$data = trim($v['value']['content'][1]);
				$data = implode("\\\\n",explode("\n",$data));
				
				// 普通回复
				if (array_key_exists($data, $reply))
				{
					if (is_array($reply[$data]) && $count=count($reply[$data]))
					{
						send_qun_msg($v['value']['from_uin'], $reply[$data][mt_rand(0, $count-1)], $cookie['login']['psessionid'], $cookie['clientid']);
					}
					else
					{
						send_qun_msg($v['value']['from_uin'], $reply[$data], $cookie['login']['psessionid'], $cookie['clientid']);
					}
					continue;
				}
				
				// 物流
				if ($rep = is_express($data))
				{
					send_qun_msg($v['value']['from_uin'], $rep, $cookie['login']['psessionid'], $cookie['clientid']);
					continue;
				}
				
				// 学习模式
				if (is_study($data))
				{
					send_qun_msg($v['value']['from_uin'], "已进入学习模式.", $cookie['login']['psessionid'], $cookie['clientid']);
					$reply = unserialize(read_file(REPLY));
					continue;
				}
				
				// 数学
				if (($rep = math($data)) !== NULL)
				{
					send_qun_msg($v['value']['from_uin'], $rep, $cookie['login']['psessionid'], $cookie['clientid']);
					continue;
				}
				
				// ip查询
				if($ip_data = is_ip($data))
				{
					send_qun_msg($v['value']['from_uin'], $ip_data, $cookie['login']['psessionid'], $cookie['clientid']);
					continue;
				}
				
				// 呼叫人工回复
				if ($info = is_call($data))
				{
					send_buddy_msg(4092490351, "qun:{$v['value']['from_uin']}--->{$info}", $cookie['login']['psessionid'], $cookie['clientid']);
					continue;
				}
				
				// 职能取词
				if (($data = is_match($data)) && array_key_exists($data, $reply))
				{
					if (is_array($reply[$data]) && $count=count($reply[$data]))
					{
						send_qun_msg($v['value']['from_uin'], $reply[$data][mt_rand(0, $count-1)], $cookie['login']['psessionid'], $cookie['clientid']);
					}
					else
					{
						send_qun_msg($v['value']['from_uin'], $reply[$data], $cookie['login']['psessionid'], $cookie['clientid']);
					}
					continue;
				}
			}
		}
	}

	if ($msg['retcode'] == 121)
	{
		exit("退出");
	}
}
/*
if(! pcntl_fork())
{

	while(TRUE)
	{
		// 10秒左右检测一次
		sleep(10);

		$options = read_file("options.ini");
		if (isset($options))
		{
			$options = unserialize($options);
			// 退出QQ
			if ($options['status'] == "offline")
			{
				logout($cookie['clientid'], $cookie['login']['psessionid']);
				log_message("debug","已正常退出");
				exit(0);
			}
		}
	}
}
if( ! pcntl_fork())
{
	while(TRUE)
	{
		$msg = obj_to_array(json_decode(poll($cookie['login']['psessionid'], $cookie['clientid'])));

		// 		print_r($msg);
		if ($msg['retcode'] === 0)
		{
			foreach ($msg['result'] as $v)
			{

			}
		}

		if ($msg['retcode'] == 121)
		{
			exit("退出");
		}
	}
}

while(TRUE)
{
	echo "\n\n1\t退出\n";
	if ($name = fgets(STDIN))
	{
		switch ($name)
		{
			case 1:
				options("status","offline");
				break;
		}
	}
}

function options($key,$values)
{
	if ($data = read_file("options.ini"))
	{
		$options = unserialize($data);

		print_r($options);

		$options[$key] = $values;
		write_file("options.ini",serialize($options));
	}
}
*/
