<?php
if(isset($_POST['item_name1']) AND !empty($_POST['item_name1'])){
	$paypal_item = htmlentities($_POST['item_name1'], ENT_QUOTES, 'utf-8');
	$paypal_item = explode('-', $paypal_item);
	$str = $paypal_item[1];
	$paypal_is_site = (int)(substr($str, 17, 1));
	if($paypal_is_site){
		$paypal_site_id = (int)(substr($str, 21, -3));
		$paypal_order_id = (int)(str_replace('Order SN:', '', $paypal_item[0]));
		
		include(dirname(__FILE__).'/host.php');
		
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		
		  // Fill params
		$params = 'cmd=_notify-validate';
		foreach ($_POST AS $key => $value)
			$params .= '&'.$key.'='.urlencode(stripslashes($value));
		
		// PayPal Server
		$paypalServer = 'www.paypal.com/cgi-bin/webscr';
		
		// Getting PayPal data...
		if (function_exists('curl_exec'))
		{
			// curl ready
			$ch = curl_init('https://' . $paypalServer . '/cgi-bin/webscr');
			
			// If the above fails, then try the url with a trailing slash (fixes problems on some servers)
			 if (!$ch)
				$ch = curl_init('https://' . $paypalServer . '/cgi-bin/webscr/');
			
			if (!$ch)
				$errors .= 'connect curlmethodfailed';
			else
			{
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_TIMEOUT, 60);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
				$result = curl_exec($ch);
		
				if ($result != 'VERIFIED')
					$errors .= 'curlmethod cURL error:'.curl_error($ch);
				curl_close($ch);
			}
		}
		elseif (($fp = @fsockopen('ssl://' . $paypalServer, 443, $errno, $errstr, 60)) || ($fp = @fsockopen($paypalServer, 80, $errno, $errstr, 60)))
		{
			// fsockopen ready
			$header = 'POST /cgi-bin/webscr HTTP/1.0'."\r\n" .
				  'Host: '.$paypalServer."\r\n".
				  'Content-Type: application/x-www-form-urlencoded'."\r\n".
				  'Content-Length: '.strlen($params)."\r\n".
				  'Connection: close'."\r\n\r\n";
			fputs($fp, $header.$params);
			
			$read = '';
			while (!feof($fp))
			{
				$reading = trim(fgets($fp, 1024));
				$read .= $reading;
				if (($reading == 'VERIFIED') || ($reading == 'INVALID'))
				{
					 $result = $reading;
					break;
				}
			 }
			if ($result != 'VERIFIED')
				$errors .= 'socketmethod '.$result;
			fclose($fp);
		}
		else
			$errors = 'connect nomethod';
		if ($result != 'VERIFIED')
			$errors .= 'verified'; 
		
		$post_data = array();
		$post_data = array('mc_gross' => $_POST['mc_gross'],
							 'payment_status' => $_POST['payment_status'],
							 'custom' => $_POST['custom'],
							 'txn_id' => $_POST['txn_id'],
							 'mc_currency' => $_POST['mc_currency']);
							 
		$content = host($paypal_site_id);//Find logo corresponds to the remote site information
		include(dirname(__FILE__).'/connect.php');
		$my_host = str_replace('http://','',$content[0]);
		$my_host = str_replace('/','',$my_host);
		
		$o="";
		foreach ($post_data as $k=>$v)
		{
			$o.= "$k=".urlencode($v)."&";
		}
		$post_data=substr($o,0,-1);
		$result = makeConnection($my_host, '/'.$content[3], $post_data);
		if($result=='Completed')
			echo '1';
		exit();
	}
}