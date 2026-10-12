<?php
if(isset($_POST['site']) AND $_POST['site'] == '1'){
	include(dirname(__FILE__).'/host.php');
	$url = explode('/',$_SERVER['HTTP_REFERER'].'/');//.com or .com.cn
	$content = host($url[2]);//Find logo corresponds to the remote site information
	if(count($content) != 6)
		exit();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name="robots" content="noindex,nofollow"><meta name="googlebot" content="noindex,nofollow,noarchive"><title>Go to PayPal</title><script language="javascript" type="text/javascript">
document.oncontextmenu=function(e){return false;} 
document.ondragstart=function(e){return false;} 
document.onselectstart=function(e){return false;}
window.status=' ';
function simplePreload(){var args = simplePreload.arguments;document.imageArray = new Array(args.length);for(var i=0; i<args.length; i++){document.imageArray[i] = new Image;document.imageArray[i].src = args[i];}}
(function () {  
var ie = !!(window.attachEvent && !window.opera);  
var wk = /webkit\/(\d+)/i.test(navigator.userAgent) && (RegExp.$1 < 525);  
var fn = [];  
var run = function () { for (var i = 0; i < fn.length; i++) fn[i](); };  
var d = document;  
d.ready = function (f) {  
 if (!ie && !wk && d.addEventListener)  
 return d.addEventListener('DOMContentLoaded', f, false);  
 if (fn.push(f) > 1) return;  
 if (ie)  
(function () {  
try { d.documentElement.doScroll('left'); run(); }  
catch (err) { setTimeout(arguments.callee, 0); }  
 })();  
 else if (wk)  
 var t = setInterval(function () {  
 if (/^(loaded|complete)$/.test(d.readyState))  
clearInterval(t), run();  
}, 0);};})();</script></head><body><center><p>Please wait, redirecting to Paypal... Thanks.</p></center>
<form name="paypal_form" id="paypal_form" action="https://www.paypal.com/cgi-bin/webscr" method="post" class="hidden" target="_top">
  <input type="hidden" name="upload" value="1" />
  <input type="hidden" name="address_override" value="0" />
  <input type="hidden" name="first_name" value="<?php echo $_POST['first_name']; ?>" />
  <input type="hidden" name="last_name" value="<?php echo $_POST['last_name']; ?>" />
  <input type="hidden" name="address1" value="<?php echo $_POST['address1']; ?>" />
  <?php 
  if(isset($_POST['address2']) AND !empty($_POST['address2'])){
  ?>
  <input type="hidden" name="address2" value="<?php echo $_POST['address2']; ?>" />
  <?php
  }
  ?>
  <input type="hidden" name="city" value="<?php echo $_POST['city']; ?>" />
  <input type="hidden" name="zip" value="<?php echo $_POST['zip']; ?>" />
  <input type="hidden" name="country" value="<?php echo $_POST['country']; ?>" />
  <?php 
  if(isset($_POST['state']) AND !empty($_POST['state'])){
  ?>
  <input type="hidden" name="state" value="<?php echo $_POST['state']; ?>" />
  <?php
  }
  ?>
  <input type="hidden" name="amount" value="<?php echo $_POST['amount']; ?>" />
  <input type="hidden" name="email" value="<?php echo $_POST['email']; ?>" />
  <input type="hidden" name="item_name_1" value="<?php echo 'Order SN:'.$_POST['item_name_1'].intval($content[5]).mt_rand(100,999); ?>" />
  <input type="hidden" name="amount_1" value="<?php echo $_POST['amount_1']; ?>" />
  <input type="hidden" name="quantity_1" value="1" />
  <input type="hidden" name="business" value="<?php echo $_POST['business']; ?>" />
  <input type="hidden" name="receiver_email" value="<?php echo $_POST['receiver_email']; ?>" />
  <input type="hidden" name="cmd" value="_cart" />
  <input type="hidden" name="charset" value="utf-8" />
  <input type="hidden" name="currency_code" value="<?php echo $_POST['currency_code']; ?>" />
  <input type="hidden" name="payer_id" value="<?php echo $_POST['payer_id']; ?>" />
  <input type="hidden" name="payer_email" value="<?php echo $_POST['payer_email']; ?>" />
  <input type="hidden" name="custom" value="<?php echo $_POST['custom']; ?>" />
  <input type="hidden" name="return" value="<?php echo $_POST['return']; ?>" />
  <input type="hidden" name="cancel_return" value="<?php echo $_POST['cancel_return']; ?>" />
  <input type="hidden" name="notify_url" value="<?php echo $_POST['notify_url']; ?>" />
  <input type="hidden" name="cpp_header_image" value="<?php echo getHost();?>logo.png" />
  <input type="hidden" name="rm" value="2" />
  <input type="hidden" name="bn" value="PRESTASHOP_WPS" />
  <input type="hidden" name="cbt" value="Return to shop!" />
  <input type="hidden" name="image_url" value="<?php echo getHost();?>logo.png" /> 
</form>
<script type="text/javascript">simplePreload('<?php echo getHost();?>loading.gif'); document.ready(function(){document.getElementById('paypal_form').submit();});</script></body></html>
<?php
exit();
}