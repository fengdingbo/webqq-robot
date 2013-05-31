<?php
/*
 *---------------------------------------------------------------
 * 应用模块
 *---------------------------------------------------------------
 * @copyright (c) 2013
 * @author Qiufeng <fengdingbo@gmail.com>
 * @version 1.0
 */

/**
 * 快递模块
 * 
 * @access public
 * @param string $str
 * @return string
 */
function is_express($str)
{
	if (is_numeric($str))
	{
		$postid = strlen($str);
		switch($postid)
		{
			case 10:
				$type = "yuantong";
				break;
			case 12:
				$type = "shunfeng";
				break;
			case 13:
				$type ="yunda";
				break;
		}
		
		if (isset($type))
		{
			$data = obj_to_array(json_decode(express($type, $str)));
	
			return get_express_data($data);
		}
	}
}

/**
 * 解析快递返回值
 * 
 * @access private
 * @param string $data
 * @return string
 */
function get_express_data($data)
{
	if ($data['message'] == "ok")
	{
		$result = null;
		foreach ($data['data'] as $v)
		{
			$result .= $v['time']." ".$v['context']."\\\\n";
		}

		return $result;
	}

	if ($data['status'] == 201)
	{
		return $data['message'];
	}
}

/**
 * 获取快递信息
 * 
 * @access private
 * @param string $type
 * @param int $postid
 * @return string
 */
function express($type,$postid)
{
	$url = "http://baidu.kuaidi100.com/query?type=$type&postid={$postid}&id=4&valicode=%E9%AA%8C%E8%AF%81%E7%A0%81&temp=0.23501448333263397&sessionid=B7167E9FBEE767A28CE7501BAF786C4A";

	return file_get_contents($url);
}

/**
 * 学习模块
 * 
 * @access public
 * @param string $str
 * @return int
 */
function is_study($str)
{

	if (preg_match("/^@study:(off|on);/", $str, $result))
	{
		if ($result[1] == "off")
		{
			global $user_study;
			$user_study = "off";
			return 110;
		}
		{
			global $user_study;
			$user_study = "on";
			return 111;
		}
	}

	if (preg_match("/^>(.*):(.*);/",$str,$result))
	{
		$replys = unserialize(read_file(REPLY));
		set_reply($result[1], $result[2], $replys);
		write_file(REPLY, serialize($replys));
		unset($replys);

		return 101;
	}

	if (preg_match("/^<(.*);/",$str,$result))
	{
		$replys = unserialize(read_file(REPLY));
		remove_reply($result[1], $replys);
		write_file(REPLY, serialize($replys));
		unset($replys);

		return 102;
	}
}

/**
 * 数学模块
 * 
 * @access public
 * @param string $str
 * @return string
 */
function math($str)
{
	//if (preg_match("/^\d+(\+|-|\*|\/)\d+$/", $str, $result))
	if (preg_match("/^(\d+([-+\/\*]\d+)+)$/", $str, $result))
	{
		eval("\$s=".$result[0].";");
		return $s;
	}

}

/**
 * ip模块
 * 
 * @access public
 * @param string $str
 */
function is_ip($str)
{
	$m = "/^(\d{1,2}|1\d\d|2[0-4]\d|25[0-5]).(\d{1,2}|1\d\d|2[0-4]\d|25[0-5]).(\d{1,2}|1\d\d|2[0-4]\d|25[0-5]).(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$/";
	if (preg_match($m,$str, $result))
	{
		return get_ip($result[0]);
	}
}

/**
 * 获取ip信息
 * 
 * @access private
 * @param string $ip
 * @return string
 */
function get_ip($ip)
{
	$url = "http://www.youdao.com/smartresult-xml/search.s?type=ip&q={$ip}";
	$xml = file_get_contents($url);
	$data=simplexml_load_string($xml);
	return (string)$data->product->location;
}

function is_call($str)
{
	if (preg_match("/^@call:(.*)/", $str, $result))
	{
		return $result[1];
	}
}

function is_from_admin_reply($str)
{
	if (preg_match("/^@(friend|qun):(.*):(.*)/", $str, $result))
	{
		if ($result[1] == "qun" || $result[1] == "friend")
		{
			return array_slice($result, 1);
		}
	}
}

function is_match($str)
{
	$array = array(
			"你妹",
			"麻花",
			"二货",
			);
	$match = implode("|",$array);
	if (preg_match("/($match)/",$str,$p))
	{
		return $p[1];
	}
}

/**
 * 每日一句
 * 
 * @access public
 * @param string $str
 * @return string
 */
function daily_sentence($str)
{
	$array = array("英语","每日一句","再来一个","robot","小麻");
	$match = implode("|",$array);
	if (preg_match("/($match)/",$str,$p))
	{
		$rand = mt_rand(1, 70);
		$data =file_get_contents("http://news.iciba.com/dailysentence-1-2-{$rand}.html");
		preg_match_all("/content_(.*)<a href=(.*)>(.*)<\/a>/",$data,$key);
		preg_match_all("/note_(.*)>(.*)</",$data,$val);
		$data = array(array_pop($key) , array_pop($val));
		$key = mt_rand(0,count($val[0]) - 1);
		return $data[0][$key] . ' -- ' . $data[1][$key];
	}
}

/**
 * 笑话模块
 * @access public
 * @param string $str
 * @return string
 */
function is_qiushi($str)
{
	$array = array("糗事","笑话","糗百");
	$match = implode("|", $array);
	if (preg_match("/($match)/",$str,$p))
	{
		return get_qiubai();
	}
}

/**
 * 糗事百科
 * @access public
 * @return string
 */
function get_qiubai()
{
	$url = "http://wap3.qiushibaike.com/hot/page/" . mt_rand(1,100);
	$data = file_get_contents($url);
	$data = implode("",(explode("\n",$data)));
	preg_match_all("/<div class=\"qiushi\">(<p class=\"user\">)*(.*)<p class=\"vote\">/U",$data,$matches);
	if ( ! $matches && ! array_key_exists('2',$matches))
	{
		get_qiubai();
	}

	$matches = preg_replace(array('/(<p.*p>)(.*)<br\/>+/'), "$2", $matches[2]);
	foreach ($matches as $v)
	{
		if ( ! strpos($v,"<img"))
		{
			$d[] = strip_tags($v);
		}
	}

	if (empty($d))
	{
		get_qiubai();
	}

	$result = $d[mt_rand(0,count($d)-1)];
	return $result;
}
/* End of file app.php */
