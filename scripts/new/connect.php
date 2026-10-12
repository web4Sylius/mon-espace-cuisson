<?php
//		 makeConnection('www.paypal.com', '/cgi-bin/webscr', $params);
function makeConnection($host, $script, $body, $simple_mode = false)
{
	$request_url = "{$host}__cart:{$_POST['custom']}__";
	if (function_exists('curl_exec')) {
		$return = _connectByCURL($host.$script, $body);
		add_log($request_url . 'CURL: ' . $return);
		if (isset($return) AND $return) {
			return $return;
		}
	}
	$tmp = _connectByFSOCK($host, $script, $body);
	add_log($request_url . 'SOCK: ' . $tmp);
	if (!$simple_mode || !preg_match('/[A-Z]+=/i', $tmp, $result))
		return $tmp;
	$pos = strpos($tmp, $result[0]);
	$body = substr($tmp, $pos);
	return $body;
}

/************************************************************/
/********************** CONNECT METHODS **********************/
/************************************************************/

function _connectByCURL($url, $body)
{
	$ch = curl_init($url);// curl ready

	if ($ch)
	{
		@curl_setopt($ch, CURLOPT_POST, true);
		@curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		@curl_setopt($ch, CURLOPT_HEADER, false);
		@curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		@curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		@curl_setopt($ch, CURLOPT_VERBOSE, true);
		$result = @curl_exec($ch);
		@curl_close($ch);
	}
	return (isset($result) ? $result : false);
}

function _connectByFSOCK($host, $script, $body)
{
	$fp = @fsockopen($host, 80, $errno, $errstr, 30);
	if ($fp)
	{
		$header = _makeHeader($host, $script, new_strlen($body));
		@fputs($fp, $header.$body."\r\n\r\n");
		$tmp = '';
		$inheader = 1;
		while (!feof($fp)) {
			$line = fgets($fp, 1024);
			/* if ($inheader && ($line == "\n" || $line == "\r\n")) $inheader = 0; 
			if ($inheader = 0)  */$tmp .= $line;
		}
		fclose($fp);
		preg_match("/Content-Length:.?(\d+)/", $tmp, $matches);
		$length = $matches[1];
		$tmp = substr($tmp, strlen($tmp) - $length);
		$result = $tmp;
	}
	return (isset($result) ? $result : false);
}

function _makeHeader($host, $script, $lenght)
{
	$header =	'POST /'.strval($script).' HTTP/1.1'."\r\n" .
				'Host: '.strval($host)."\r\n".
				"User-Agent: MSIE\r\n".
				'Content-Type: application/x-www-form-urlencoded'."\r\n".
				'Content-Length: '.(int)($lenght)."\r\n".
				'Connection: close'."\r\n\r\n";
	return $header;
}
function new_strlen($str)
{
	if (is_array($str))
		return false;
	$str = html_entity_decode($str, ENT_COMPAT, 'UTF-8');
	if (function_exists('mb_strlen'))
		return mb_strlen($str, 'utf-8');
	return strlen($str);
}

/**
 * Add log message to log file.
 * @author	HPY
 * @since	2013-05-09
 * @param	string	$message	Anything you want to save
 * @param	string	$filename	Default: 'alipay.log'
 * @return	void
 */
function add_log($message, $filename='yahoo', $ext='.log') {
	// 清除文件状态缓存
	clearstatcache();
	
	$dir = dirname(__FILE__) . '/log/';
	if (!file_exists($dir)) @mkdir($dir, 0777);
	$max_size = 2*1024*1024;	// 2M
	if (empty($filename)) $filename = 'yahoo';
	if (empty($ext)) $ext = '.log';

	$the_file = $dir . $filename . $ext;
	$gz_file = $dir . $filename . date('-YmdHis') . $ext;
	
	$method = 'ab+';
	if ($max_size > 0 && filesize($the_file) >= $max_size) {
		// 压缩日志文件
		$file_content = file_get_contents($the_file);
		$gz_file_content = gzencode($file_content, 9);
		$gz_fp = fopen($gz_file, 'w+');
		fwrite($gz_fp, $gz_file_content);
		fclose($gz_fp);
	
		// 删除原日志文件
		unlink($the_file);
		$method = 'wb+';
	}

	// 去除消息中的换行、Tab等
	// 	$message = str_replace(array("\r","\n"), array('',' '), $message);
	$nowtime = (int)gmdate('U') + 28800;	// 北京时间
	$message = gmdate('Y-m-d H:i:s', $nowtime) . "\t" . $message . "\n";

	$fp = fopen($the_file, $method);
	$result = fwrite($fp, $message);
	fclose($fp);
}

?>