<?php
/*
-----------------------------------------------------------------------------
*	.FILE NAME:
*		XMLCalls.inc
*	
*	.FUNCTION:
*		Engine class for Remote Call Control Interface
*
*	.Notice: Set implicit_flush = On in php.ini file
*
-------------------------------------------------------------------------------
*/

class XMLCalls {

    var $addr;
    var $port;
    var $user_name;
    var $password;
    var $sock;

    function XMLCalls( $addr, $port , $user_name, $password )
	{
		$this->addr = $addr;
		$this->port = $port;
		$this->user_name = $user_name;
		$this->password = $password;
    }
	
    function Authenticate()
	{
		if ( empty($this->user_name ))
		{
			echo "<br>ERROR: Blank username is not allowed";
			exit(0);
		}

		if ( empty($this->password ))
		{
				echo "<br>ERROR: Blank password is not allowed";
				exit(0);
		}
		
		$arr = array($this->user_name, $this->password);
		$method = "Services.CallProcessingService.authenticate";	
		$request = xmlrpc_encode_request($method,$arr);	
		
	
		$request = $request."\r\n\r\n";/* extcc.log */		
		
		// create a streaming socket, of type TCP/IP
		$this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	
		if( !socket_connect($this->sock, $this->addr, $this->port) ) {
			echo "Socket error.Cannot connet.(function: socket_connect)";
			exit(0);
		}
		if( !socket_write($this->sock, $request, strlen($request)) ) {
			echo "Socket error.Cannot send.(function: socket_write)";
			exit(0);
		}

		// get a list of all the clients that have data to be read from
		// if there are no clients with data, go to next iteration
		$var = array($this->sock);
		$write = NULL;
		$except = NULL;
		
		if( !socket_select($var, $write, $except, array("sec" => 10, "usec" => 0)) ) {
		    echo "socket_select() failed, reason: " .
      		socket_strerror(socket_last_error()) . "\n";
	   	}
		
		$response = socket_read($this->sock, 4096);
					
		// check if the client is disconnected
		if ($response === false) {
			$go = false;
			unset($this->sock);
			echo "<br>Connection was closed by remote host!";	
			exit(0);		
		}	
	
		$method = "Services.CallProcessingService.authenticate";
		
		$arr = array();
		$arr = xmlrpc_decode_request(trim($response),$method);		
		
  	if( (strstr($response,"<methodResponse>") && $arr == "Wrong username.") || (strstr($response,"<methodResponse>") && $arr == "Wrong username or password.")) {
			return false; 
		} else {
			return true; 
		}
	}
	
	function UpdateConfig($filePath)
	{
		if(empty($filePath)) {
			echo "Parameter 'filename' is empty !";
			exit(0);
		}
									
		$this->UpdateConfiguration($filePath);
	}
	
	function Call($from, $to)
	{
		if(empty($from)) {
			echo "Parameter 'from' is empty !";
			exit(0);
		}
		if(empty($to)) {
			echo "Parameter 'to' is empty !";
			exit(0);
		}
									
		$this->CreateCall($from,$to,null,null,null,null);		
	}	

	function Join($from,$to,$from2,$to2,$play_filename)
	{
		if(empty($from)) {
			echo "Parameter 'from' is empty !";
			exit(0);
		}
		if(empty($to)) {
			echo "Parameter 'to' is empty !";
			exit(0);
		}
		if(empty($from2)) {
			echo "Parameter 'from2' is empty !";
			exit(0);
		}
		if(empty($to2)) {
			echo "Parameter 'to2' is empty !";
			exit(0);
		}
									
		$this->CreateCall($from,$to,$from2,$to2,null,$play_filename);	
	}
	
	function Transfer($from,$to,$transfer_to)
	{
		if(empty($from)) {
			echo "Parameter 'from' is empty !";
			exit(0);
		}
		if(empty($to)) {
			echo "Parameter 'to' is empty !";
			exit(0);
		}
		if(empty($transfer_to)) {
			echo "Parameter 'transfer_to' is empty !";
			exit(0);
		}
	
		$this->CreateCall($from,$to,null,null,$transfer_to,null);
	}	
	
	function GenerateCallID()
	{
		 mt_srand(time());

		 $Part1 = ltrim( mt_rand(1, 99999), '-');
		 $Part2 = ltrim( mt_rand(1, 99999), '-');
		 $Part3 = ltrim( mt_rand(1, 99999), '-');
		 $Part4 = ltrim( mt_rand(1, 99999), '-');

		 return $Part1.$Part2.$Part3.$Part4;	
	}

	function CreateMethodResponseID($ID)
	{
		$request = "<?xml version='1.0'?>".
			"<methodResponse>\n".
			"<params>\n".
			"<param>\n".
			"<value><string>".$ID."</string></value>\n".
			"</param>\n".
			"</params>\n".
			"</methodResponse>";
		
		return $request."\r\n\r\n";/* extcc.log */
	}
	
	function CreateCall($from, $to, $from2, $to2, $transfer_to, $play_filename)
	{
		if($this->sock == null) {
			// create a streaming socket, of type TCP/IP
			$this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			$sock_data = socket_connect($this->sock, $addr, $port);
		}
		$arr = array($this->GenerateCallID(), $from, $to);
		$method = "Services.CallProcessingService.createcall";	
		$request = xmlrpc_encode_request($method,$arr);	
		$request = $request."\r\n\r\n";/* extcc.log */	

		$sock_data = socket_write($this->sock, $request, strlen($request));	
		
		// create a list of all the clients that will be connected to us..
		// add the listening socket to this list
		$clients = array($this->sock);
		$go = true;	
	
		echo "<form name='call_proceed' method='post'>".
		"<input type='hidden' name='hdn_closecall' value=''>".
		"<input type='hidden' name='ip' value='{$_POST['ip']}'>".		
		"<input type='hidden' name='port' value='{$_POST['port']}'>".		
		"<input type='hidden' name='user_name' value='{$_POST['user_name']}'>".		
		"<input type='hidden' name='password' value='{$_POST['password']}'>".		
		"<input type='hidden' name='from' value='{$_POST['from']}'>".		
		"<input type='hidden' name='to' value='{$_POST['to']}'>".
		"<input type='hidden' name='from2' value='{$_POST['from2']}'>".		
		"<input type='hidden' name='to2' value='{$_POST['to2']}'>".
		"<input type='hidden' name='join' value='{$_POST['join']}'>".
		"<input type='hidden' name='transfer' value='{$_POST['transfer']}'>".
		"<input type='hidden' name='play_filename' value='{$_POST['play_filename']}'>".				
		"<input type='hidden' name='socket' value='$this->sock'>".
		"<hr width='450'><br>".
		"<center>".
		"<input type='text' readonly class='input_readonly1' value='CallID' size='35'><input type='text' readonly class='input_readonly1' value='Status' size='15'><br>";
			
		while ($go) {
			// create a copy, so $clients doesn't get modified by socket_select()
			$read = $clients;
			
			// get a list of all the clients that have data to be read from
			// if there are no clients with data, go to next iteration
			$write = NULL;
			$except = NULL;
		
			if (socket_select($read, $write, $except, 
												array("sec" => 10, "usec" => 0)) < 1){
				continue;
			}
		   
			// socket_read while show errors when the client is disconnected, 
			// so silence the error messages
		
			$response = socket_read($this->sock, 4096);print $response;
						
			// check if the client is disconnected
			if ($response === false) {
				$go = false;
				unset($this->sock);
				echo "<br>Connection was closed by remote host!";			
			}	

			if( strstr($response,"<methodResponse>")) {
				$arr = array();
				$arr = xmlrpc_decode_request($response,$method);				
				//echo "create call response callid=".$arr;
				// save callid for transfer
				echo "<input type='hidden' name='call_id' value='$arr'>";
				
				continue;
			}			
			
			if( strstr($response,"<methodResponse>") && $_POST['hdn_closecall'] == "boot" ) {
				$call_id = $arr[0];
				echo "<input type='text' readonly class='input_readonly' size='35' value='{$_POST['call_id']}'>".
					 "<input type='text' readonly class='input_readonly' size='15' value='Closed'><br>";
				// also close unset socket here
				exit;
			}
		
			if( !strstr($response,"<methodResponse>") ) {
				
				$response_test = strstr(trim($response),"\r\n\r\n");

				if( strstr($response_test,"\r\n\r\n") != FALSE ) {

					$pos = strpos($response,"\r\n\r\n");
					$response1 = trim(substr($response,0,$pos));
					$response2 = trim(substr($response,$pos,strlen($response)));

					$method = "Services.CallProcessingService.callstatechanged";
					
					$arr1 = array();
					$arr1 = xmlrpc_decode_request($response1,$method);
					//echo "<br>tttarr1=".$arr1[0];
					//echo "<br>arr2=".$arr1[1]."<br>";
					
					$arr2 = array();
					$arr2 = xmlrpc_decode_request($response2,$method);
					//echo "<br>arr1=".$arr2[0];
					//echo "<br>arr2=".$arr2[1]."<br>";	
											
					if($arr1[1] == 0 && $arr2[1] == 1) {
					
						for( $i = 0; $i < 2; $i++ ) {
							
							//$arr = array($arr1[0]);
							//$request = xmlrpc_encode_request(null,$arr);	

							$request = $this->CreateMethodResponseID($arr1[0]);

							$sock_data = socket_write($this->sock, $request, strlen($request));	
						}
						echo "<input type='text' readonly class='input_readonly' size='35' value='$arr1[0]'>".
							 "<input type='text' readonly class='input_readonly' size='15' value='Trying'><br>".
							 "<input type='text' readonly class='input_readonly' size='35' value='$arr2[0]'>".
							 "<input type='text' readonly class='input_readonly' size='15' value='Ringing'><br>";
			
					}
					
					continue;
				}	
				
				$method = "Services.CallProcessingService.callstatechanged";
				
				$arr = array();
				$arr = xmlrpc_decode_request(trim($response),$method);
				//echo "<br>arr1=".$arr[0];
				//echo "<br>arr2=".$arr[1]."<br>";		
				
				$status = "";
									
				switch($arr[1])
				{
					case 0:
						$status = "Trying";
						
						//$arr = array($arr[0]);
						//$request = xmlrpc_encode_request(null,$arr);	
						//$request = $request."\r\n\r\n";/* extcc.log */	
						
						$request = $this->CreateMethodResponseID($arr[0]);
						$sock_data = socket_write($this->sock, $request, strlen($request));	
						
						echo "<input type='hidden' name='trying' value='Trying'>".	
							 "<input type='text' readonly class='input_readonly' size='35' value='$arr[0]'>".
							 "<input type='text' readonly class='input_readonly' size='15' value='$status'><br>";

					break;					
					case 1:
						$status = "Ringing";
						
						//$arr = array($arr[0]);
						//$request = xmlrpc_encode_request(null,$arr);	
						//$request = $request."\r\n\r\n";/* extcc.log */	
						$request = $this->CreateMethodResponseID($arr[0]);
						$sock_data = socket_write($this->sock, $request, strlen($request));	
						
						echo "<input type='hidden' name='ringing' value='Ringing'>".
							 "<input type='text' readonly class='input_readonly' size='35' value='$arr[0]'>".
							 "<input type='text' readonly class='input_readonly' size='15' value='$status'><br>";
						
					break;						
					case 2:
						$status = "Confirmed";
						
						//$arr = array($arr[0]);
						//$request = xmlrpc_encode_request(null,$arr);	
						//$request = $request."\r\n\r\n";/* extcc.log */	
						$request = $this->CreateMethodResponseID($arr[0]);
						$sock_data = socket_write($this->sock, $request, strlen($request));	
						
						echo "<input type='hidden' name='confirmed' value='Confirmed'>".
							 "<input type='text' readonly class='input_readonly' size='35' value='$arr[0]'>".
							 "<input type='text' readonly class='input_readonly' size='15' value='$status'><br>";

						if( !empty($transfer_to) ) {
							
							$this->TransferCall($arr[0],$transfer_to);
							//exit(0);
						
						} elseif( !empty($from2) && !empty($to2) ) {
							
							// join case
							//$this->PlayMessage($arr[0], $play_filename, 2);

							$this->CreateCall2($from2, $to2, $arr[0], $play_filename);
						}		
					break;
					case 3:
						$status = "Closed";
						
						//$arr = array($arr[0]);
						//$request = xmlrpc_encode_request(null,$arr);	
						//$request = $request."\r\n\r\n";/* extcc.log */
						$request = $this->CreateMethodResponseID($arr[0]);	
						$sock_data = socket_write($this->sock, $request, strlen($request));
													
						echo "<input type='hidden' name='closed' value='Closed'>".
							 "<input type='text' readonly class='input_readonly' size='35' value='$arr[0]'>".
							 "<input type='text' readonly class='input_readonly' size='15' value='$status'><br>".
							 "</center></form>";
						
						$go = false;
						
						unset($this->sock);
						//echo "<br>Connection was closed by remote host!";	
						exit(0);							
					break;
					case 4:
						$status = "Joined";
						echo "<input type='hidden' name='joined' value='Joined'>".
							 "<input type='text' readonly class='input_readonly' size='35' value='$arr[0]'>".
							 "<input type='text' readonly class='input_readonly' size='15' value='$status'><br>";

					break;
					case 5:
						$status = "Unjoined";
						echo "<input type='hidden' name='ringing' value='Unjoined'>".
							 "<input type='text' readonly class='input_readonly' size='35' value='$arr[0]'>".
							 "<input type='text' readonly class='input_readonly' size='15' value='$status'><br>";
						
					break;
					case 6:
						{
							switch ( $arr[2] )
							{
								case 0:
									$status = "Line busy";
									break;
								case 1:
									$status = "Temporary unavailable";
									break;
								case 2:
									$status = "User not found";
									break;
								case 3:
									$status = "Media not supported";
									break;
								case 4:
									$status = "Inappropriate command";
									break;
								default:
									$status = "Generic error";
							}
						echo "<input type='hidden' name='unjoined' value='Error'>".
							 "<input type='text' readonly class='input_readonly' size='35' value='$arr[0]'>".
							 "<input type='text' readonly class='input_readonly' size='15' value='$status'><br>";
						}
					break;
					default:
						$status = "Unknown code received!";
						echo "<input type='hidden' name='unknown' value='Unknown'>".
							 "<input type='text' readonly class='input_readonly' size='35' value='$arr[0]'>".
							 "<input type='text' readonly class='input_readonly' size='15' value='$status'><br>";
						
					break;
				}
			}           
		} // end while
		
		echo "</center></form>";
	
		// close the listening socket
		if($this->sock){
			echo "<br>Socket closed !";
			socket_close($this->sock);
		}	
	}
	
	function CreateCall2($from, $to, $call_id1, $play_filename)
	{
		if($this->sock == null) {
			// create a streaming socket, of type TCP/IP
			$this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			$sock_data = socket_connect($this->sock, $addr, $port);
		}
		$arr = array($this->GenerateCallID(), $from, $to);
		$method = "Services.CallProcessingService.createcall";	
		$request = xmlrpc_encode_request($method,$arr);	
		$request = $request."\r\n\r\n";/* extcc.log */	
		$sock_data = socket_write($this->sock, $request, strlen($request));	
		
		// create a list of all the clients that will be connected to us..
		// add the listening socket to this list
		$clients = array($this->sock);
		$go = true;	
			
		while ($go) {
			// create a copy, so $clients doesn't get modified by socket_select()
			$read = $clients;
			
			// get a list of all the clients that have data to be read from
			// if there are no clients with data, go to next iteration
			$write = NULL;
			$except = NULL;
		
			if (socket_select($read, $write, $except, 
												array("sec" => 10, "usec" => 0)) < 1){
				continue;
			}
		   
			// socket_read while show errors when the client is disconnected, 
			// so silence the error messages
		
			$response = socket_read($this->sock, 4096);
						
			// check if the client is disconnected
			if ($response === false) {
				$go = false;
				unset($this->sock);
				echo "<br>Connection was closed by remote host!";			
			}	

		
			if( strstr($response,"<methodResponse>")) {
				$arr = array();
				$arr = xmlrpc_decode_request($response,$method);				
				//echo "create call response callid=".$arr;
				// save callid for transfer
				echo "<input type='hidden' name='call_id' value='$arr'>";
				
				continue;
			}			
			
			if( strstr($response,"<methodResponse>") && $_POST['hdn_closecall'] == "boot" ) {
				$call_id = $arr[0];
				echo "<input type='text' readonly class='input_readonly' size='35' value='{$_POST['call_id']}'>".
					 "<input type='text' readonly class='input_readonly' size='15' value='Closed'><br>";
				// also close unset socket here
				exit;
			}
		
			if( !strstr($response,"<methodResponse>") ) {
			
				$methodPlMsg = "Services.CallProcessingService.messageplayed";
				if( strstr($response,"messageplayed") ) {
				
					$arr = xmlrpc_decode_request($response,$methodPlMsg);					
					$request = $this->CreateMethodResponseID($arr[0]);
					$sock_data = socket_write($this->sock, $request, strlen($request));	
											
					//for($i=0;$i<1000;$i++){} // just for timeout
					
					$this->JoinCalls($call_id1, $arr[0]);						
																	
					continue;
				}			
				
				$response_test = strstr(trim($response),"\r\n\r\n");

				if( strstr($response_test,"\r\n\r\n") != FALSE ) {				

					$pos = strpos($response,"\r\n\r\n");
					$response1 = trim(substr($response,0,$pos));
					$response2 = trim(substr($response,$pos,strlen($response)));

					$method = "Services.CallProcessingService.callstatechanged";
					
					$arr1 = array();
					$arr1 = xmlrpc_decode_request($response1,$method);
					//echo "<br>tttarr1=".$arr1[0];
					//echo "<br>arr2=".$arr1[1]."<br>";
					
					$arr2 = array();
					$arr2 = xmlrpc_decode_request($response2,$method);
					//echo "<br>arr1=".$arr2[0];
					//echo "<br>arr2=".$arr2[1]."<br>";	
					
					//$response_count++;			
					
					if($arr1[1] == 0 && $arr2[1] == 1) {
					
						for( $i = 0; $i < 2; $i++ ) {
							
							$request = $this->CreateMethodResponseID($arr1[0]);	
							$sock_data = socket_write($this->sock, $request, strlen($request));	
						}
						echo "<input type='text' readonly class='input_readonly' size='35' value='$arr1[0]'>".
							 "<input type='text' readonly class='input_readonly' size='15' value='Trying'><br>".
							 "<input type='text' readonly class='input_readonly' size='35' value='$arr2[0]'>".
							 "<input type='text' readonly class='input_readonly' size='15' value='Ringing'><br>";
			
					}
					
					continue;
				}	
				
				$method = "Services.CallProcessingService.callstatechanged";
				
				$arr = array();
				$arr = xmlrpc_decode_request(trim($response),$method);
				//echo "<br>arr1=".$arr[0];
				//echo "<br>arr2=".$arr[1]."<br>";		
				
				$status = "";
									
				switch($arr[1])
				{
					case 0:
						$status = "Trying";

						$request = $this->CreateMethodResponseID($arr[0]);
						$sock_data = socket_write($this->sock, $request, strlen($request));	
						
						echo "<input type='hidden' name='trying' value='Trying'>".	
							 "<input type='text' readonly class='input_readonly' size='35' value='$arr[0]'>".
							 "<input type='text' readonly class='input_readonly' size='15' value='$status'><br>";

					break;					
					case 1:
						$status = "Ringing";
	
						$request = $this->CreateMethodResponseID($arr[0]);
						$sock_data = socket_write($this->sock, $request, strlen($request));	
						
						echo "<input type='hidden' name='ringing' value='Ringing'>".
							 "<input type='text' readonly class='input_readonly' size='35' value='$arr[0]'>".
							 "<input type='text' readonly class='input_readonly' size='15' value='$status'><br>";
						
					break;						
					case 2:
						$status = "Confirmed";

						$request = $this->CreateMethodResponseID($arr[0]);	
						$sock_data = socket_write($this->sock, $request, strlen($request));	
						
						echo "<input type='hidden' name='confirmed' value='Confirmed'>".
							 "<input type='text' readonly class='input_readonly' size='35' value='$arr[0]'>".
							 "<input type='text' readonly class='input_readonly' size='15' value='$status'><br>";
						
						if(!empty($play_filename)) {
							$this->PlayMessage($arr[0], $play_filename, 2);
						} else {
							$this->JoinCalls($call_id1, $arr[0]);
						}
						
					break;
					case 3:
						$status = "Closed";
						
						//$arr = array($arr[0]);
						//$request = xmlrpc_encode_request(null,$arr);	
						//$request = $request."\r\n\r\n";/* extcc.log */	
						$request = $this->CreateMethodResponseID($arr[0]);
						$sock_data = socket_write($this->sock, $request, strlen($request));
													
						echo "<input type='hidden' name='closed' value='Closed'>".
							 "<input type='text' readonly class='input_readonly' size='35' value='$arr[0]'>".
							 "<input type='text' readonly class='input_readonly' size='15' value='$status'><br>".
							 "</center></form>";
						
						$go = false;
						
						unset($this->sock);
						//echo "<br>Connection was closed by remote host!";	
						exit(0);							
					break;
					case 4:
						$status = "Joined";
						echo "<input type='hidden' name='joined' value='Joined'>".
							 "<input type='text' readonly class='input_readonly' size='35' value='$arr[0]'>".
							 "<input type='text' readonly class='input_readonly' size='15' value='$status'><br>";

					break;
					case 5:
						$status = "Unjoined";
						echo "<input type='hidden' name='ringing' value='Unjoined'>".
							 "<input type='text' readonly class='input_readonly' size='35' value='$arr[0]'>".
							 "<input type='text' readonly class='input_readonly' size='15' value='$status'><br>";
						
					break;
					case 6:
						$status = "Error";
						echo "<input type='hidden' name='unjoined' value='Error'>".
							 "<input type='text' readonly class='input_readonly' size='35' value='$arr[0]'>".
							 "<input type='text' readonly class='input_readonly' size='15' value='$status'><br>";
						
					break;												
					default:
						$status = "Unknown code received!";
						echo "<input type='hidden' name='unknown' value='Unknown'>".
							 "<input type='text' readonly class='input_readonly' size='35' value='$arr[0]'>".
							 "<input type='text' readonly class='input_readonly' size='15' value='$status'><br>";
						
					break;
				}					
			}           
		} // end while
		
		echo "</center></form>";
	
		// close the listening socket
		if($this->sock){
			echo "<br>Socket closed !";
			socket_close($this->sock);
		}	
	}	
	
	function UpdateConfiguration($filePath)
	{
		if($this->sock == null) {
			// create a streaming socket, of type TCP/IP
			$this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			$sock_data = socket_connect($this->sock, $addr, $port);
		}
		
		//parsing filename
		$filePath = realpath($filePath);
		$filename = basename($filePath);
		
		$fileSize = filesize($filePath);
		
		$arr = array($this->GenerateCallID(), $filename, $fileSize);
		$method = "Services.SystemService.configurationupdate";	
		$request = xmlrpc_encode_request($method,$arr);	
		$request = $request."\r\n\r\n";/* extcc.log */	
		$sock_data = socket_write($this->sock, $request, strlen($request));	
		
			// create a copy, so $clients doesn't get modified by socket_select()
		$read = array($this->sock);;
			
			// get a list of all the clients that have data to be read from
			// if there are no clients with data, go to next iteration
		$write = NULL;
		$except = NULL;
		
		socket_select($read, $write, $except,array("sec" => 10, "usec" => 0));
		 
		$response = socket_read($this->sock, 4096);

			// check if the client is disconnected
		if ($response === false) {
			unset($this->sock);
			echo "<br>Connection was closed by remote host!";			
		}

			if ( strstr($response,"OK") )
			{
				//opening file to upload
				$fd = fopen( $filePath, "rb" );
				$buffer = '';
		
				while ( $fileSize > 0)
				{
					if ( $fileSize > 1024 )
					{
						$buffer = fread($fd, 1024);
						socket_write( $this->sock, $buffer, 1024 );
						$response = socket_read($this->sock, 32);
					}
					else
					{
						$buffer = fread($fd, $fileSize);
						socket_write( $this->sock, $buffer, $fileSize );
						$response = socket_read($this->sock, 32);
					}
					$fileSize -= 1024;
				}
				fclose( $fd );
			}
			else
			if (strstr($response,"FAIL"))
			{
				echo "<br>Upload failed ";
				if($this->sock){
					socket_close($this->sock);
				}	
				return;
			}
			
			$response = socket_read($this->sock, 4096);
			
			if ($response === false){
				unset($this->sock);
				echo "<br>Connection was closed by remote host!";			
			}
			
			$arr = array( );
			$arr = xmlrpc_decode_request($response,$method);
			
			$data = array($arr);

  		if( strstr($response,"<methodResponse>") && $_POST['updconfig'] == "boot" )
			{
				if ( $data[0] == 1 )
				{
					echo "<br> File uploaded successfully";
				}
				else
				{
					echo "<br> File upload failed";
				}
			}
		// close the listening socket
		
		if($this->sock){
			socket_close($this->sock);
		}	
	}	
	
	function JoinCalls($id1, $id2)
	{
		$arr = array(trim($id1), trim($id2));
		$method = "Services.CallProcessingService.joincalls";	
		return $this->ProcessXML($method,$arr);
	}
	
	function TransferCall($id, $transfer_to)
	{
		$arr = array(trim($id), trim($transfer_to));
		$method = "Services.CallProcessingService.transfercall";	
		return $this->ProcessXML($method,$arr);
	}	
	
	function PlayMessage($id, $play_filename, $play_count)
	{	
		$arr = array(trim($id), trim($play_filename),(int)$play_count);
		$method = "Services.CallProcessingService.playmessage";	
		return $this->ProcessXML($method,$arr);
	}		
	
	function ProcessXML($method,$arr)
	{	
		$request = xmlrpc_encode_request($method,$arr);	
		$request = $request."\r\n\r\n";/* extcc.log */		

		$sock_data = socket_write($this->sock, $request, strlen($request));
		
		// get a list of all the clients that have data to be read from
		// if there are no clients with data, go to next iteration
		$var = array($this->sock);
		
		$write = NULL;
		$except = NULL;
		
		socket_select($var, $write, $except, array("sec" => 10, "usec" => 0));
		
		$response = socket_read($this->sock, 4096);
					
		// check if the client is disconnected
		if ($response === false) {
			$go = false;
			unset($this->sock);
			echo "<br>Connection was closed by remote host!";			
		}	
		
		$arr = array();
		$arr = xmlrpc_decode_request(trim($response),$method);		
		
		if(strstr($response,"<methodResponse>")) {
			return true; 
		} else {
			return false; 
		}
	}			
}
?>
