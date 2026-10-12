<?php
include("admin_global.php");
include_once("../class/facture.class.php");
unset($user_info);

$sql = "SELECT *";
$sql .= " FROM utilisateur";
$sql .= " WHERE userid = ".$_GET['userid'];
$user_info = $DB_site2->query_first($sql);

$host=$_SERVER['HTTP_HOST'];

//On détermine l'origine de provenance
if($user_info != ""){
	$myUser = new utilisateur($DB_site2, $_GET['userid']);
// 	$host = $myUser->redirection();
        foreach($template->sites() as $site_code=>$site){
            if($myUser->site_code=="" || $site_code==$myUser->site_code){
                $hosts=array_flip($site['hosts']);
                if(isset($hosts[$myUser->langue])){
                    $host="dedie.".$hosts[$myUser->langue];
                    break;
                }
            }
        }
}


$goto="";
if(isset($_GET['goto'])) $goto = "/".$_GET['goto'];

echo ""
. "<head>"
	. "<script language=\"javascript\">"
		. "function gotosite(){"
			. "document.forms.f.submit();"
		. "}"
		. "setTimeout('gotosite()',2000);"
	. "</script>"
	. "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf8\">"
. "</head>";


echo ""
. "<body style=\"background-color:#808080;\">"
	. "<form name=\"f\" action=\"https://".$host.$goto."\" method=\"post\">"
		. "<input type=\"hidden\" name=\"action\" value=\"clientnavig\">"
		. "<input type=\"hidden\" name=\"userid\" value=\"".$user_info['userid']."\">"
		. "<input type=\"hidden\" name=\"password\" value=\"".$user_info['password']."\">"
		. "<input type=\"hidden\" name=\"prenom\" value=\"".$user_info['prenom']."\">"
		. "<input type=\"hidden\" name=\"admin\" value=\"".$_COOKIE['admin_username']."\">"
	. "</form>"
	. "<div style=\"width:300px; background-color:white; margin:auto; text-align:center;padding:10px;\">"
		. "<p>Vous êtes maintenant connecté sur le compte de <b>".$user_info['prenom']." ".$user_info['nom']."</b></p>"
		. "<p><a href=\"javascript:gotosite();\">Aller sur le site</a></p>"
	. "</div>"
. "</body>";


?>
