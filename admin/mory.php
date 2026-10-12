<?php
$js = "<script language=\"JavaScript\" type=\"text/javascript\">
<!--
  function squirrelmail_loginpage_onload() {
    document.forms[0].js_autodetect_results.value = '1';
    var textElements = 0;
    for (i = 0; i < document.forms[0].elements.length; i++) {
      if (document.forms[0].elements[i].type == \"text\" || document.forms[0].elements[i].type == \"password\") {
        textElements++;
        if (textElements == 1) {
          document.forms[0].elements[i].focus();
          break;
        }
      }
    }
  }

function init()
{
document.logon.username.focus();
}

// -->
</script>

<style type=\"text/css\">
<!--
  /* avoid stupid IE6 bug with frames and scrollbars */
  body { 
      voice-family: \"\"}\"\"; 
      voice-family: inherit; 
      width: expression(document.documentElement.clientWidth - 30);
  }
-->
</style>
" ;

$bodyonload = "onload=\"squirrelmail_loginpage_onload(); init();\"" ;

require "admin_global.php";
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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Mes e-mails</font>
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


$mondom = "" ;
$dom = explode(".", $host) ;
$i = 1 ;
while ($i < count($dom))
	{
	if ($i > 1)
		$mondom .= ".".$dom[$i] ;
	else
		$mondom .= $dom[$i] ;
	$i++ ;
	}

echo "<h3>Frais de port par livraison spécialisée MORY</h3>" ;


if (isset($action) && $action=="domodif"){
	$DB_site2->query("UPDATE mory SET prixtranche1='$prixtranche1', prixtranche2='$prixtranche2', prixtranche3='$prixtranche3', prixtranche4='$prixtranche4', prixtranche5='$prixtranche5', prixtranche6='$prixtranche6' WHERE departementid='$departementid' ");
	$action="";
}

if (isset($action) && $action=="modif"){
	$departement=$DB_site2->query_first("SELECT * FROM mory WHERE departementid='$departementid'");
	echo "Modification du département $departement[departementid]<br><br>";
	echo "<form action=\"mory.php?action=domodif\" method=\"post\">
			<input type=\"hidden\" value=\"$departement[departementid]\" name=\"departementid\">
			<table>
				<tr>
					<td>Prix tranche 1</td>
					<td><input type=\"text\" name=\"prixtranche1\" value=\"$departement[prixtranche1]\"></td>
				</tr>
				<tr>
					<td>Prix tranche 2</td>
					<td><input type=\"text\" name=\"prixtranche2\" value=\"$departement[prixtranche2]\"></td>
				</tr>
				<tr>
					<td>Prix tranche 3</td>
					<td><input type=\"text\" name=\"prixtranche3\" value=\"$departement[prixtranche3]\"></td>
				</tr>
				<tr>
					<td>Prix tranche 4</td>
					<td><input type=\"text\" name=\"prixtranche4\" value=\"$departement[prixtranche4]\"></td>
				</tr>
				<tr>
					<td>Prix tranche 5</td>
					<td><input type=\"text\" name=\"prixtranche5\" value=\"$departement[prixtranche5]\"></td>
				</tr>
				<tr>
					<td>Prix tranche 6</td>
					<td><input type=\"text\" name=\"prixtranche6\" value=\"$departement[prixtranche6]\"></td>
				</tr>
				<tr>
					<td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"modifier\"></td>
				</tr>
			</table>
		</form>";
}
if (!isset($action)||$action==""){		
	echo "Quel département voulez vous modifier ?<br><br>";
	$departements=$DB_site2->query("SELECT departementid FROM mory ORDER BY departementid");
	while($departement=$DB_site2->fetch_array($departements))
		echo "<a href=\"mory.php?action=modif&departementid=$departement[departementid]\">Département $departement[departementid]</a><br><br>";
}
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
        </tr>
";
include "./footer.php";
?>