<?php
/*
 *---------------------------------------------------------------
 * 常用基础函数模块
 *---------------------------------------------------------------
 * @copyright (c) 2013
 * @author Qiufeng <fengdingbo@gmail.com>
 * @version 1.0
 */

/**
 * 设置回复
 * 
 * @access public
 * @param string $key
 * @param string $value
 * @param array $replys
 * @return array
 */
function set_reply($key, $value, &$replys)
{
	// key exists if replys
	if (array_key_exists($key, $replys))
	{
		if ( ! is_array($replys[$key]))
		{
			$replys[$key] = (array)$replys[$key];
		}
		
		foreach ($replys[$key] as $k => $v)
		{
			if ($v == $value)
			{
				return $replys;
			}
		}
		
		$replys[$key] = (array)$replys[$key];
		array_push($replys[$key], $value);
		
		return $replys;
	}
	
	// 直接保存值
	$replys[$key] = $value;
	return $replys;
}

/**
 * 删除带值回复
 * 
 * @access public
 * @param string $key
 * @param string $value
 * @param array $replys
 * @return array
 */
function delete_reply($key, $value, &$replys)
{
	if ( ! array_key_exists($key, $replys))
	{
		return $replys;
	}
	
	if (is_array($replys[$key]) && count($replys[$key]) >= 2)
	{
		foreach ($replys[$key] as $k => $v)
		{
			if ($v == $value)
			{
				unset($replys[$key][$k]);
				return $replys;
			}
		}
	}
	
	if ($replys[$key] == $value)
	{
		unset($replys[$key]);
	}

	if (is_array($replys[$key]) && implode("", $replys[$key]) == $value)
	{
		unset($replys[$key]);
	}
	
	return $replys;
}

/**
 * 删除回复
 * 
 * @access public
 * @param string $key
 * @param array $replys
 * @return array
 */
function remove_reply($key, &$replys)
{
	if ( ! array_key_exists($key, $replys))
	{
		return $replys;
	}
	
	unset($replys[$key]);

	return $replys;
}

/**
 * 读取文件
 * 
 * @access public
 * @param string $file
 * @return string
*/
function read_file($file)
{
	if ( ! file_exists($file))
	{
		return FALSE;
	}

	if (function_exists('file_get_contents'))
	{
		return file_get_contents($file);
	}

	if ( ! $fp = @fopen($file, 'rb'))
	{
		return FALSE;
	}

	flock($fp, LOCK_SH);

	$data = '';
	if (filesize($file) > 0)
	{
		$data =& fread($fp, filesize($file));
	}

	flock($fp, LOCK_UN);
	fclose($fp);

	return $data;
}

/**
 * 写入文件
 * 
 * @access public
 * @param string $path
 * @param string $data
 * @param string $mode
 * @return boolean
 */
function write_file($path, $data, $mode = "wb")
{
	if ( ! $fp = @fopen($path, $mode))
	{
		return FALSE;
	}
	
	flock($fp, LOCK_EX);
	fwrite($fp, $data);
	flock($fp, LOCK_UN);
	fclose($fp);
	
	return TRUE;
}

/**
 * 对象转数组
 * 
 * @access public
 * @param object $obj
 * @return array
 */
function obj_to_array($obj)
{
	$ret = array();
	
	foreach ($obj as $key =>$value)
	{
		if (gettype($value) == 'array' || gettype($value) == 'object')
		{
			$ret[$key] = obj_to_array($value);
		}
		else
		{
			$ret[$key] = $value;
		}
	}

	return $ret;
}

function log_message($level = 'error', $message, $php_error = FALSE)
{
	if (log_threshold == 0)
	{
		return;
	}
	
	write_log($level, $message, $php_error);
}


/**
 * 写入日志文件
 * 一般情况下，此方法由log_message函数调用
 * 
 * @access private
 * @param string $level
 * @param string $msg
 * @param boolean $php_error
 * @return boolean
 */
function write_log($level = 'error', $msg, $php_error = FALSE)
{
	$level = strtoupper($level);
	
	$filepath = temp_dir.'/log-'.date('Y-m-d').'.php';
	$message = '';

	if ( ! file_exists($filepath))
	{
		$message .= "WEBQQ DEBUG LOG\n\n";
	}

	if ( ! $fp = @fopen($filepath, "ab"))
	{
		return FALSE;
	}


	$message .= $level.' '.(($level == 'INFO') ? ' -' : '-').' '.date("Y-m-d H:i:s"). ' --->'.$msg."\n";

	flock($fp, LOCK_EX);
	fwrite($fp, $message);
	flock($fp, LOCK_UN);
	fclose($fp);

	@chmod($filepath, 0666);
	return TRUE;
}

/**
 * 解析cookie
 *
 * @access public
 * @return array
 */
function parse_cookie()
{
	// Netscape HTTP Cookie File
	$cookies = file(temp_dir."cookie");

	$data = array();
	foreach ($cookies as $v)
	{
		if (preg_match("/(.*\.qq\.com)\t(.*)\t(.*)\t(.*)\t(.*)\t(.*)\t(.*)\n/U", $v, $p))
		{
			$data[] = array_slice($p, 1);
		}
	}

	return $data;
}

/**
 * 获取cookie
 * 
 * public 
 * @param array $cookie
 * @return array
 */
function get_cookie($cookie = NULL)
{
	if ($cookie === NULL)
	{
		$cookie = parse_cookie();
	}
	
	if (is_array($cookie) && count($cookie)<=6)
	{
		return FALSE;
	}

	foreach ($cookie as $v)
	{
		$data[$v[5]] =$v[6];
	}

	return $data;
}