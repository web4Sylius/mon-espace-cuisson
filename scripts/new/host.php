<?php
/* Debug only */
@ini_set('display_errors', 'off');
/* Improve PHP configuration to prevent issues */
@set_time_limit(0);
@ini_set('max_execution_time', 0);
@ini_set('memory_limit', '2000M');
@ini_set('upload_max_filesize', '100M');
//Find logo corresponds to the remote site information
function host($valve, $db = '')
{
	$db = dirname(__FILE__).'/gb.dat';
	$valve = htmlentities($valve);//safe
	$list1 = file($db);
	$num = count($list1);
	$content = array();//beging
	for ($i=0;$i<$num;$i++)
	{
		$list2 = explode('|',$list1[$i]);
		if (is_numeric(trim($valve)))
		{//from ID
			if(eregi($valve,intval($list2[5])))
			{
				$content = $list2;
				break;
			}
		}
		else
		{//from web
			if(eregi($valve,$list2[0]))
			{
				$content = $list2;
				break;
			}
		}
	}
	return $content;
}

function getHost() {
	$host = (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']);
	$host = 'http://'.$host.dirname($_SERVER['PHP_SELF']).'/';
	return $host;
}
?>