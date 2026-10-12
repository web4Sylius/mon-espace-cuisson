<?php
if(isset($_POST['item_name1']) AND !empty($_POST['item_name1'])){
	$paypal_item = htmlentities($_POST['item_name1'], ENT_QUOTES, 'utf-8');
	$paypal_item = explode('-', $paypal_item);
	$str = $paypal_item[1];
	$paypal_is_site = (int)(substr($str, 17, 1));
	if($paypal_is_site){
		$paypal_site_id = (int)(substr($str, 21, -3));
		$paypal_order_id = (int)(str_replace('Order SN:', '', $paypal_item[0]));

		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		
		$content = host($paypal_site_id);//Find logo corresponds to the remote site information
		$url = $content[0].$content[4].'?key='.$_GET['key'].'&id_cart='.(int)($_GET['id_cart']).'&id_module='.(int)($_GET['id_module']).'';
		Tools::redirectLink($url);
	}
}