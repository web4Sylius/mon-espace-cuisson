<?php

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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Frais de port gratuits</font>
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
   <td align=\"center\" valign=\"middle\">" ;

if (isset($action) and $action == "enregistrer") 
	{
	foreach($_POST['gratuitpart'] as $site_code=>$listepays){
	    foreach($listepays as $paysid=>$valeur){
		$DB_site2->query("UPDATE fraisport SET gratuit=".floatval($valeur)." WHERE paysid=".$paysid." AND sites='".$site_code."'");
	    }
	}
	foreach($_POST['gratuitpro'] as $site_code=>$listepays){
	    foreach($listepays as $paysid=>$valeur){
		$DB_site2->query("UPDATE fraisport SET gratuitpro=".floatval($valeur)." WHERE paysid=".$paysid." AND sites='".$site_code."'");
	    }
	}
	$action = "" ;
	}

   
echo "<h3>Configuration des frais de port (2)</h3>
Les pays qui apparaîssent ici sont ceux pour qui des frais de port ont été configurés.<br><br>" ;
if (!isset($action) or $action=="") 
	{
	echo "<form method=\"post\" action=\"fraisport2.php\">
	<input type=\"hidden\" name=\"action\" value=\"enregistrer\">
	<table class=\"table_coupscoeur\">" ;
	
	$fpsites=$DB_site2->query("SELECT sites FROM fraisport WHERE zone_tnt=0 GROUP BY sites");
	//while($fpsite=$DB_site2->fetch_array($fpsites)){
	
	    echo "<tr>
	      <td colspan=\"2\" style=\"text-align:center;font-size:16px;\">Site ".$fpsite['sites']."</td><td><b>PARTICULIER</b></td><td><b>PRO</b></td>
	    </tr>";
	    $payss=$DB_site2->query("SELECT DISTINCT (p.paysid), gratuit, gratuitpro, diminutif, libelle FROM pays p, fraisport fp WHERE p.paysid = fp.paysid AND zone_tnt=0 AND sites='base'");
	    while ($pays=$DB_site2->fetch_array($payss)) 
		    {
		    $num = $pays[paysid] ;
		    echo "<tr>
				    <td><img border=\"0\" src=\"./images/drapeaux/br/$pays[diminutif].gif\"></td>
				    <td>$pays[libelle]</td>
				    <td align=\"left\"> à partir de <input type=\"text\" name=\"gratuitpart[base][$num]\" value=\"$pays[gratuit]\" size=\"2\"> EUR d'achat.</td>
				    <td align=\"left\"> à partir de <input type=\"text\" name=\"gratuitpro[base][$num]\" value=\"$pays[gratuitpro]\" size=\"2\"> EUR d'achat.</td>
			    </tr>" ;
		    }
	//}
	echo "</table><br><br>
	<input type=\"submit\" value=\"Enregistrer\">
	</form>";
	}



echo "</td>
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