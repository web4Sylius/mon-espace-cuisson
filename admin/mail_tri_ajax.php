<?php  
include "admin_global.php";
$mailid = $_GET['mailid'];

$mail = $DB_site2->query_first("SELECT * FROM mail_to_check WHERE id='$mailid'");

$body=nl2br(stripslashes($mail['body']));
$body=strip_tags($body,"<br><p><style><table><tbody><td><tr><img>");
$body=preg_replace("#<style>(.+)</style>#iU","",$body);
$body=preg_replace('/<style[^>]*>.*?<\/style>/s', '', $body);
$body=preg_replace('#<br>{3,30}#', '', $body);




echo 	"<div class=\"col-xs-10\">".$body."</div>";	
echo 	"<div class=\"col-xs-2\">";
echo 		"<div class=\"col-xs-12 no-padding\" style=\"text-align:center;\">Attribuer à :</div>";
echo 		"<div class=\"col-xs-12 no-padding\">
				<div class=\"col-xs-offset-3 col-xs-6 no-padding btn_attr\" onclick=\"jQuery('#cli_".$mailid."').toggle();\">CLIENT</div><div class=\"col-xs-3\"></div>
				<div class=\"col-xs-12\" style=\"display:none;\">
					<input type=\"text\" id=\"cli_".$mailid."\" class=\"input_client\" placeholder=\"numéro client\">
					<button style=\"width:22%;\">ok</button>
				</div>
				<div class=\"col-xs-offset-3 col-xs-6 no-padding btn_attr\">SAV</div><div class=\"col-xs-3\"></div>
				<div class=\"col-xs-12\" style=\"display:none;\">
					<input type=\"text\" id=\"sav_".$mailid."\" class=\"input_sav\" placeholder=\"numéro client\">
					<button style=\"width:22%;\">ok</button>
				</div>
				<div class=\"col-xs-offset-3 col-xs-6 no-padding btn_attr\">PJT</div><div class=\"col-xs-3\"></div>
				<div class=\"col-xs-12\" style=\"display:none;\">
					<input type=\"text\" id=\"pjt_".$mailid."\" class=\"input_pjt\" placeholder=\"numéro client\">
					<button style=\"width:22%;\">ok</button>
				</div>
				<div class=\"col-xs-offset-3 col-xs-6 no-padding btn_attr\">CDE</div><div class=\"col-xs-3\"></div>
				<div class=\"col-xs-12\" style=\"display:none;\">
					<input type=\"text\" id=\"cde_".$mailid."\" class=\"input_cde\" placeholder=\"numéro client\">
					<button style=\"width:22%;\">ok</button>
				</div>
			</div>";
echo 	"</div>";






?>