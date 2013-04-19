<?php
/*
 *---------------------------------------------------------------
 * 核心模块
 *---------------------------------------------------------------
 * @copyright (c) 2013
 * @author Qiufeng <fengdingbo@gmail.com>
 * @version 1.0
 */

/**
 * 获取验证码
 * 
 * @access public
 * @param int $uid
 * @param string $verify
 * @return array
 */
function check_verify($uid)
{
	$ch = curl_init("https://ssl.ptlogin2.qq.com/check?uin={$uid}&appid=1003903&r=0.14233942252344134");
	$cookie = "confirmuin=0; ptvfsession=b1235b1729e7808d5530df1dcfda2edd94aabec43bf450d8cf037510802aa1a7dbed494c66577479895c62efa3ef35ab; ptisp=cnc";
	curl_setopt($ch, CURLOPT_COOKIE, $cookie);
// 	curl_setopt($ch, CURLOPT_HEADER, TRUE);
	curl_setopt($ch, CURLOPT_COOKIEFILE, temp_dir."cookie");
	curl_setopt($ch, CURLOPT_COOKIEJAR, temp_dir."cookie");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	if (preg_match("/ptui_checkVC\('(.*)','(.*)','(.*)'\);/", $data, $verify))
	{
		return array_slice($verify, 1);
	}
}

/**
 * 登录
 * 
 * @access public
 * @param int $uid
 * @param string $passwd
 * @param string $verify
 * @return array
 */
function login($uid, $passwd, $verify)
{
	$url = "http://ptlogin2.qq.com/login?u={$uid}&p={$passwd}&verifycode={$verify}&webqq_type=10&remember_uin=1&login2qq=1&aid=1003903&u1=http%3A%2F%2Fweb.qq.com%2Floginproxy.html%3Flogin2qq%3D1%26webqq_type%3D10&h=1&ptredirect=0&ptlang=2052&from_ui=1&pttype=1&dumy=&fp=loginerroralert&action=8-38-447467&mibao_css=m_webqq&t=3&g=1";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_COOKIEFILE, temp_dir."cookie");
// 	curl_setopt($ch, CURLOPT_HEADER, TRUE);
 	curl_setopt($ch, CURLOPT_COOKIEJAR, temp_dir."cookie");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	if (preg_match("/ptuiCB\('(.*)','(.*)','(.*)','(.*)','(.*)',\s'(.*)'\);/U", $data, $verify))
	{
		return array_slice($verify, 1);
	}
}

/**
 * 真正的登录(上线)
 * 
 * @access public
 * @param string $ptwebqq
 * @return string
 */
function login2($ptwebqq,$clientid)
{
	$url = "http://d.web2.qq.com/channel/login2";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "r=%7B%22status%22%3A%22online%22%2C%22ptwebqq%22%3A%22{$ptwebqq}%22%2C%22passwd_sig%22%3A%22%22%2C%22clientid%22%3A%22{$clientid}%22%2C%22psessionid%22%3Anull%7D&clientid={$clientid}&psessionid=null");
	// 必须要来路域名
	curl_setopt($ch, CURLOPT_REFERER, "http://d.web2.qq.com/proxy.html?v=20110331002&callback=1&id=2");
//	curl_setopt($ch, CURLOPT_HEADER, TRUE);
	curl_setopt($ch, CURLOPT_COOKIEFILE, temp_dir."cookie");
	curl_setopt($ch, CURLOPT_COOKIEJAR, temp_dir."cookie");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	return curl_exec($ch);
}

/**
 * 退出
 * 
 * @access public
 * @param int $clientid
 * @param string $psessionid
 */
function logout($clientid, $psessionid)
{
	$url = "http://d.web2.qq.com/channel/logout2?ids=&clientid={$clientid}&psessionid={$psessionid}&t=136587427856";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_REFERER, "http://d.web2.qq.com/proxy.html?v=20110331002&callback=1&id=3");
	curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie");
// 	curl_setopt($ch, CURLOPT_HEADER, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	return curl_exec($ch);
}

/**
 * 获取消息
 * 
 * @access public
 * @param string $psessionid
 * @param int $clientid
 * @return string
 */
function poll($psessionid,$clientid)
{
	$post = "r=%7B%22clientid%22%3A%22{$clientid}%22%2C%22psessionid%22%3A%22{$psessionid}%22%2C%22key%22%3A0%2C%22ids%22%3A%5B%5D%7D&clientid={$clientid}&psessionid={$psessionid}";
	$ch = curl_init("http://d.web2.qq.com/channel/poll2");
	// 必须要来路域名
	curl_setopt($ch, CURLOPT_REFERER, "http://d.web2.qq.com/proxy.html?v=20110331002&callback=1&id=3");
	curl_setopt($ch, CURLOPT_COOKIEFILE, temp_dir."cookie");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
// 	curl_setopt($ch, CURLOPT_HEADER, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	return curl_exec($ch);
}

/**
 * 获取好友列表
 * 
 * @access public
 * @param string $vfwebqq
 * @return string
 */
function get_user_friend($vfwebqq)
{
	$post = "r=%7B%22h%22%3A%22hello%22%2C%22vfwebqq%22%3A%22{$vfwebqq}%22%7D";
	$ch = curl_init("http://s.web2.qq.com/api/get_user_friends2");
	curl_setopt($ch, CURLOPT_REFERER, "http://d.web2.qq.com/proxy.html?v=20110331002&callback=1&id=3");
	curl_setopt($ch, CURLOPT_COOKIEFILE, temp_dir."cookie");
	curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
//  curl_setopt($ch, CURLOPT_HEADER, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	return curl_exec($ch);
	
}

/**
 * 获取群列表
 * 
 * @access public
 * @param string $vfwebqq
 * @return string
 */
function get_group_name_list_mask($vfwebqq)
{
	$post = "r=%7B%22vfwebqq%22%3A%22{$vfwebqq}%22%7D";
	$ch = curl_init("http://s.web2.qq.com/api/get_group_name_list_mask2");
	curl_setopt($ch, CURLOPT_REFERER, "http://d.web2.qq.com/proxy.html?v=20110331002&callback=1&id=3");
	curl_setopt($ch, CURLOPT_COOKIEFILE, temp_dir."cookie");
	curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	return curl_exec($ch);
}

/**
 * 获取群人员信息
 * 
 * @access public
 * @param int $gcode
 * @param string $vfwebqq
 * @return string
 */
function get_group_info_ext($gcode, $vfwebqq)
{
	$url = "http://s.web2.qq.com/api/get_group_info_ext2?gcode={$gcode}&vfwebqq={$vfwebqq}&t=1365616959866";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_REFERER, "http://s.web2.qq.com/proxy.html?v=20110412001&callback=1&id=3");
	curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie");
	ob_start();
	curl_exec($ch);
	return ob_get_clean();
}

/**
 * 发送消息
 * 
 * @access public
 * @param int $from_uin
 * @param string $msg
 * @param string $psessionid
 * @param int $clientid
 * @return string
 */
function send_buddy_msg($from_uin, $msg, $psessionid, $clientid)
{
	static $msg_id=71830055;
	$msg_id++;
	$post = "r=%7B%22to%22%3A{$from_uin}%2C%22face%22%3A606%2C%22content%22%3A%22%5B%5C%22{$msg}%5C%5Cn%5C%22%2C%5B%5C%22font%5C%22%2C%7B%5C%22name%5C%22%3A%5C%22%E5%AE%8B%E4%BD%93%5C%22%2C%5C%22size%5C%22%3A%5C%2210%5C%22%2C%5C%22style%5C%22%3A%5B0%2C0%2C0%5D%2C%5C%22color%5C%22%3A%5C%22000000%5C%22%7D%5D%5D%22%2C%22msg_id%22%3A{$msg_id}%2C%22clientid%22%3A%22{$clientid}%22%2C%22psessionid%22%3A%22{$psessionid}%22%7D&clientid={$clientid}&psessionid={$psessionid}";
	$ch = curl_init("http://d.web2.qq.com/channel/send_buddy_msg2");
	// 必须要来路域名
	curl_setopt($ch, CURLOPT_REFERER, "http://d.web2.qq.com/proxy.html?v=20110331002&callback=1&id=3");
	curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
//	curl_setopt($ch, CURLOPT_HEADER, TRUE);
	curl_exec($ch);
}

/**
 * 发送群消息
 * 
 * @access public
 * @param int $group_id
 * @param string $msg
 * @param string $psessionid
 * @param int $clientid
 * @return string
 */
function send_qun_msg($group_id, $msg, $psessionid, $clientid)
{
	static $msg_id = 77860003;
	$msg_id++;
	$post = "r=%7B%22group_uin%22%3A{$group_id}%2C%22content%22%3A%22%5B%5C%22{$msg}%5C%5Cn%5C%22%2C%5B%5C%22font%5C%22%2C%7B%5C%22name%5C%22%3A%5C%22%E5%AE%8B%E4%BD%93%5C%22%2C%5C%22size%5C%22%3A%5C%2210%5C%22%2C%5C%22style%5C%22%3A%5B0%2C0%2C0%5D%2C%5C%22color%5C%22%3A%5C%22000000%5C%22%7D%5D%5D%22%2C%22msg_id%22%3A{$msg_id}%2C%22clientid%22%3A%22{$clientid}%22%2C%22psessionid%22%3A%22{$psessionid}%22%7D&clientid={$clientid}&psessionid={$psessionid}";
	$ch = curl_init("http://d.web2.qq.com/channel/send_qun_msg2");
	curl_setopt($ch, CURLOPT_REFERER, "http://d.web2.qq.com/proxy.html?v=20110331002&callback=1&id=3");
	curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
//	curl_setopt($ch, CURLOPT_HEADER, TRUE);
	curl_exec($ch);
}

/**
 * 获取指定用户uin
 * 
 * @access public
 * @param array $friends
 * @return int
 */
function get_friend_uin($friends)
{
	foreach ($friends['result']['marknames'] as $k=>$v)
	{
		if ($v['markname'] == "!@#$%^aaD4E")
		{
					return $v['uin'];
		}
	}
}

/**
 * 获取指定群id
 * 
 * @access public
 * @param string $data
 * @return array
 */
function get_group_gid($data)
{
	$r = json_decode($data);
	$gnamelist = $r->result->gnamelist;
	foreach ($gnamelist as $k=>$v)
	{
		if ($v->name == "09.经贸。电商")
		{
			return array(
					"gid"=>$v->gid,
					"code"=>$v->code
				);
		}
	}
}

/**
 * 获取群里的指定用户
 * 
 * @access public
 * @param string $data
 * @return array
 */
function get_group_user_find_uid($data)
{
	$r = obj_to_array(json_decode($data));
	
	foreach ($r["result"]["minfo"] as $v)
	{
		if ($v['nick'] == "秋风" && $v['uin'] != USERNAME)
		{
			return $v;
		}
	}
}

/**
 * WEBQQ3.0 新版登陆加密函数
 * 
 * @access public
 * @param string $p
 * @param string $pt
 * @param string $vc
 * @param boolean $md5
 * @return string
 */
function jspassword($p,$pt,$vc,$md5 = true)
{
	if($md5)
	{
		$p = strtoupper(md5($p));
	}
	$len = strlen($p);
	$temp = null;
	for ($i=0; $i < $len ; $i = $i + 2)
	{
		$temp .= '\x'.substr($p, $i,2);
	}
	return strtoupper(md5(strtoupper(md5(hex2asc($temp).hex2asc($pt))).$vc));
}

/**
 * 十六进制转字符
 * 
 * @access private
 * @param string $str
 * @return string
 */
function hex2asc($str)
{
	$str = join('', explode('\x', $str));
	$len = strlen($str);
	$data = null;
	for ($i=0;$i<$len;$i+=2)
	{
		$data.=chr(hexdec(substr($str,$i,2)));
	}
	return $data;
}