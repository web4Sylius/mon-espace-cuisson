<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!--
-----------------------------------------------------------------------------
*	.FILE NAME:
*		rcci.php
*	
*	.FUNCTION:
*		Remote Call Control Interface
*
*	.Notice: Set implicit_flush = On in php.ini file
-----------------------------------------------------------------------------
-->
<?php
require_once __DIR__.'/../../secrets.php';
ini_set("display_errors","On");
include "XMLCalls_inc.php";

$request = xmlrpc_encode_request("method", array(1, 2, 3));
$context = stream_context_create(array('http' => array(
    'method' => "POST",
    'header' => "Content-Type: text/xml",
    'content' => $request
)));

/*
$file = file_get_contents("http://www.example.com/xmlrpc", false, $context);
$response = xmlrpc_decode($file);
if (xmlrpc_is_fault($response)) {
    trigger_error("xmlrpc: $response[faultString] ($response[faultCode])");
} else {
    print_r($response);
}*/
?>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Remote Call Control Interface</title>
</head>

<LINK rel="stylesheet" type="text/css" href="styles/styles.css">


<script language='javascript' >
	
	function runJoin() 
	{
		this.document.forms['call_control'].elements['join'].value="boot";
		this.document.forms['call_control'].submit();
	}

	function runTransfer() 
	{
		this.document.forms['call_control'].elements['transfer'].value="boot";
		this.document.forms['call_control'].submit();
	}
	
	function getFileName() 
	{
		this.document.forms['call_control'].elements['play_filename'].value=this.document.forms['call_control'].file_name.value;
	}		
	
	function closeCall() 
	{
		this.document.forms['call_control'].elements['hdn_closecall'].value="boot";
	}

</script>

<center><font size='6' class="title">Remote Call Control Interface</font></center>
<br>

<form name='call_control' method='post' target="_self">

<table class="table_border" align="center">
<tr>
<td class="inp_header" colspan="2" align="center">Connection parameters</td>
</tr>

<tr>
<td class="td_pad">Quadro IP</td>
<td class="td_pad"><input type='text' class="input" name='ip' maxlength='15' size='15' value='<?php echo !empty($_POST['ip']) ? $_POST['ip'] : '83.136.166.238';  ?>'></td>

<tr>
<td class="td_pad">Port</td>
<td class="td_pad"><input type='text' class="input" name='port' maxlength='4' size='15'  value='4849'></td>

<tr>
<td class="td_pad">User Name</td>
<td class="td_pad"><input type='text' class="input" name='user_name' maxlength='10' size='15'  value='admin'></td>

<tr>
<td class="td_pad">Password</td>
<td class="td_pad"><input type='password' class="input" name='password' maxlength='10' size='15'  value='<?php echo IPBX_PASSWORD; ?>'></td>

<?php
if( (!empty($_POST['join']) && ($_POST['join'] == "boot")) || ( !empty($_POST['transfer']) && ($_POST['transfer'] == "boot"))) {	

	$addr = $_POST['ip'];
	$port = $_POST['port'];
	$user_name = $_POST['user_name'];
	$password = $_POST['password'];	
	//echo $addr."---".$port."---".$user_name."---".$password;
	if ( checkConnectFields() )
	{
		$net = new XMLCalls($addr, $port, $user_name, $password );
	
		if( !$net->Authenticate() ) {
			?>
			<tr>
			<td class="td_pad">Connection Status</td>
			<td class="td_pad"><input type='text' class="input" size='35' value='Wrong username or password'></td>
			<?php	
			exit(0);
		} else {
			?>
			<tr>
			<td class="td_pad">Connection Status</td>
			<td class="td_pad"><input type='text' class="input" size='35' value='Connected'></td>
			<?php			
		}
	}		
}
?>

</table>

<br>

<table class="table_border" align="center">
<tr>
<td class="inp_header" colspan="2" align="center">Call 1</td>
</tr>

<tr>
<td class="td_pad">From</td>
<td class="td_pad"><input type='text' class="input" name='from' maxlength='50' size='50'  value='<?php if ( !empty($_POST['from'] )) echo $_POST['from']; else echo "100" ?>'></td>

<tr>
<td class="td_pad">To</td>
<td class="td_pad"><input type='text' class="input" name='to' maxlength='50' size='50'  value='<?php if ( !empty($_POST['to'] )) echo $_POST['to']; else echo "0618388111" ?>'></td>

<tr>
<td class="td_pad">Transfer to</td>
<td class="td_pad"><input type='text' class="input" name='transfer_to' maxlength='50' size='50'  value='<?php if ( !empty($_POST['transfer_to'] )) echo $_POST['transfer_to']; else echo "100" ?>'></td>

</table>

<br>

<center>
<input type='button'  value='Run Transfer' class="button" onclick="runTransfer()">
</center>

<br>

<table class="table_border" align="center">
<tr>
<td class="inp_header" colspan="2" align="center">Call 2</td>
</tr>

<tr>
<td class="td_pad">From</td>
<td class="td_pad"><input type='text' class="input" name='from2' maxlength='50' size='50'  value='<?php if ( !empty($_POST['from2'] )) echo $_POST['from2']; ?>'></td>

<tr>
<td class="td_pad">To</td>
<td class="td_pad"><input type='text' class="input" name='to2' maxlength='50' size='50'  value='<?php if ( !empty($_POST['to2'] )) echo $_POST['to2']; ?>'></td>

<tr>
<td class="td_pad">File to play</td>
<td class="td_pad">
<select class="input"  onChange="getFileName()" name="file_name" size="1">
	<option>
			<option value='accept.wav' <?php echo !empty($_POST['play_filename']) && ($_POST['play_filename'])=="accept.wav"?"selected":""?>> accept.wav
			<option value='activated.wav' <?php echo !empty($_POST['play_filename']) && ($_POST['play_filename'])=="activated.wav"?"selected":""?>> activated.wav
			<option value='accblocked.wav' <?php echo !empty($_POST['play_filename']) && ($_POST['play_filename'])=="accblocked.wav"?"selected":""?>> accblocked.wav
			<option value='adminsysmsgmenu.wav' <?php echo !empty($_POST['play_filename']) && ($_POST['play_filename'])=="adminsysmsgmenu.wav"?"selected":""?>> adminsysmsgmenu.wav
			<option value='attcntmenu.wav' <?php echo !empty($_POST['play_filename']) && ($_POST['play_filename'])=="attcntmenu.wav"?"selected":""?>> attcntmenu.wav
			<option value='attdial.wav' <?php echo !empty($_POST['play_filename']) && ($_POST['play_filename'])=="attdial.wav"?"selected":""?>> attdial.wav
			<option value='attextension.wav' <?php echo !empty($_POST['play_filename']) && ($_POST['play_filename'])=="attextension.wav"?"selected":""?>> attextension.wav
			<option value='attinvext.wav' <?php echo !empty($_POST['play_filename']) && ($_POST['play_filename'])=="attinvext.wav"?"selected":""?>> attinvext.wav
			<option value='attinvlogin.wav' <?php echo !empty($_POST['play_filename']) && ($_POST['play_filename'])=="attinvlogin.wav"?"selected":""?>> attinvlogin.wav
</select>
</td>

</table>

<br>
<center>
<input type='button'  value='Run Join' class="button" onclick="runJoin()">
</center>

<input type='hidden' name="join" value="">
<input type='hidden' name="transfer" value="">
<input type='hidden' name="play_filename" value="<?php echo !empty($_POST['play_filename']) && ($_POST['play_filename'])?>">

</form>

<?php

function checkConnectFields()
{
	if( empty($_POST['ip']) ) {
		?>
		<script language="javascript">
			alert("Please, enter Quadro IP address.");
		</script>
		<?php
		return false;
	}
	
	if( empty($_POST['port']) ) {
		?>
		<script language="javascript">
			alert("Please, enter port number.");
		</script>
		<?php
		return false;
	}

	return true;
}

function checkCall1Fields()
{
	if( empty($_POST['from']) ) {
		?>
		<script language="javascript">
			alert("Please, fill 'From' field.");
		</script>
		<?php
		return false;
	}
	
	if( empty($_POST['to']) ) {
		?>
		<script language="javascript">
			alert("Please, fill 'To' field.");
		</script>
		<?php
		return false;
	}
	
	if( $_POST['transfer'] == "boot" ) {
		if( empty($_POST['transfer_to']) ) {
			?>
			<script language="javascript">
				alert("Please, fill 'Transfer to' field.");
			</script>
			<?php
			return false;
		}	
	}	
	
	return true;	
}

function checkCall2Fields()
{

	if( empty($_POST['from2']) ) {
		?>
		<script language="javascript">
			alert("Please, fill 'From' field.");
		</script>
		<?php
		return false;
	}
	
	if( empty($_POST['to2']) ) {
		?>
		<script language="javascript">
			alert("Please, fill 'To' field.");
		</script>
		<?php
		return false;
	}
	
	return true;	
}

if( !empty($_POST['join']) && $_POST['join'] == "boot" ) {

	if(checkCall1Fields() && checkCall2Fields()) {
		
		$net->Join($_POST['from'],$_POST['to'],$_POST['from2'],$_POST['to2'],$_POST['play_filename']);
	
	}
}

if( !empty($_POST['transfer']) && $_POST['transfer'] == "boot" ) {

	if(checkCall1Fields()) {
		$net->Transfer($_POST['from'],$_POST['to'],$_POST['transfer_to']);	
// 		$net->Call($_POST['from'],$_POST['to']);	
	}
}
?>

</body>
</html>
