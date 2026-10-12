<?php

require "admin_global.php";
setcookie("hostname",$host,time()+3600,"/");
include "./header.php";
include "./menu.php";

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Texte du panier conforme à la loi Chatel</font>
			</td>
		</tr>
		<tr>
			<td background=\"images2/cadre_r3_c2.gif\" width=\"100%\" height=\"2\">
			</td>
		</tr>
	</table>
   </td>
   <td><img name=\"cadre_r2_c5\" src=\"images2/cadre_r2_c5.gif\" width=\"11\" height=\"27\" border=\"0\" alt=\"\"></td>
   <td background=\"images2/cadre_r2_c6.gif\" width=\"100%\" height=\"27\" align=\"center\" valign=\"middle\">
   </td>
   <td><img name=\"cadre_r2_c8\" src=\"images2/cadre_r2_c8.gif\" width=\"13\" height=\"27\" border=\"0\" alt=\"\"></td>
  </tr>
</table>

</tr></td>
<tr><td>

<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td background=\"images2/cadre_r4_c2.gif\" width=\"3\" height=\"100%\"></td>
   <td align=\"justify\" valign=\"middle\"><br>
   	<font class=\"txt_2\">
		<table align=\"center\" width=\"100%\">
			<tr>
				<td align=\"center\" valign=\"middle\" width=\"100%\">" ;

$i = 0 ;
echo "Choisir la langue à modifier<br>" ;
while ($i < count($langues)) {
	echo "<a href=\"texteloichatel.php?lang=$langues[$i]\">Contenu$langues[$i]</a>&nbsp;|&nbsp;" ;
	$i++ ;
	}
if (!isset($lang) or $lang == "")
	$lang = "FR" ;

echo "<br><br>Langue en cours : <strong>Contenu$lang</strong>" ;
$wysiwyg_temp=$DB_site2->query_first("SELECT * FROM wysiwyg");
$DB_site2->query( "DELETE FROM wysiwyg" );
//$rootpath = "/home/arobases/www/vendre-en-ligne.fr/";
include($rootpath."fckeditor/fckeditor.php") ;	
echo "<center>
		<form method=\"post\" action=\"texteloichatel.php\">
			<table align=\"center\" width=\"100%\">";

$loichatel = "loichatel$lang" ;
$contenu_rq = "contenu$lang" ;
//$content = "content$lang" ;
if (isset($action3) and $action3 == "modifier")
	{
	$DB_site2->query("INSERT INTO wysiwyg (wysiwygid) VALUES ('1')");
	$DB_site2->query("UPDATE wysiwyg SET $contenu_rq = '$content'");
	$wysiwyg_temp = $DB_site2->query_first("SELECT * FROM wysiwyg");
	$DB_site2->query("update parametresgeneraux set $loichatel = '" .addslashes($wysiwyg_temp[$contenu_rq]). "'");
	$wysiwyg_suppr = $DB_site2->query( "DELETE FROM wysiwyg" );
	echo "<script language=\"javascript\">location.href=\"texteloichatel.php\";</script>";
	}
$actu=$DB_site2->query_first("select $loichatel from parametresgeneraux");
$content = $actu[$loichatel] ;

			echo "<tr>
					<td align=\"center\" width=\"100%\"><br>" ;
			$contenu = "<table align=\"center\" width=\"100%\"><tr><td><br>" ;
			$oFCKeditor = new FCKeditor('content') ;
			$oFCKeditor->BasePath = '/fckeditor/' ;
			$oFCKeditor->Value		= $content ;
			$oFCKeditor->Height	= 400 ;
			$oFCKeditor->Width	= 585 ;
			$contenu .= $oFCKeditor->Create() ;
			$contenu .= "</td></tr></table>";
			echo "$contenu</td>
				</tr>
				<tr>
					<td align=\"center\" width=\"100%\"><input type=\"hidden\" name=\"action3\" value=\"modifier\">
					<input type=\"hidden\" name=\"lang\" value=\"$lang\">
						<input type=\"submit\" name=\"btn_valid\" value=\"Modifier\"></td>
				</tr>
			</table>
		</form>
	<br><br><a href=\"index.php\" target=\"_self\">Retour</a></center>";

echo "</td>
			</tr>	
		</table>	
     </font>
	</td>
   <td background=\"images2/cadre_r4_c10.gif\" width=\"3\" height=\"100%\" border=\"0\" alt=\"\"></td>
  </tr>
</table>
</td>
</tr>
<tr><td>

<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td rowspan=\"2\"><img name=\"cadre_r6_c2\" src=\"images2/cadre_r6_c2.gif\" width=\"10\" height=\"26\" border=\"0\" alt=\"\"></td>
   <td align=\"center\" height=\"23\" width=\"100%\"></td>
   <td rowspan=\"2\"><img name=\"cadre_r6_c9\" src=\"images2/cadre_r6_c9.gif\" width=\"8\" height=\"26\" border=\"0\" alt=\"\"></td>
  </tr>
  <tr>
   <td background=\"images2/cadre_r7_c4.gif\" width=\"100%\" height=\"3\"></td>
  </tr>
</table>
</td></tr></table>
</td>
</tr><tr>
</tr></table>
				  </td>
              </tr>
            </table>
		  </td>
        </tr>";

include "./footer.php";

?>