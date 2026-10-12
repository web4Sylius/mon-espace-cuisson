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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Taux de TVA</font>
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
	$erreur = "" ;
	$payss=$DB_site2->query("SELECT diminutif, libelle, p.paysid from fraisport fp, pays p where fp.paysid = p.paysid");
	while ($pays=$DB_site2->fetch_array($payss)) 
		{
		$id = $pays[paysid] ;
		//echo "$fraisport[$id], $fraisgestion[$id]<br>";
		if ($txnormal[$id] == "" or $txreduit[$id] == "")
			{
			$erreur .= "Les taux ne sont pas saisis pour $pays[libelle].<br>" ;
			}
		elseif (!is_numeric($txnormal[$id]) or !is_numeric($txreduit[$id]))
			{
			$erreur .= "Les taux ne sont pas numériques pour $pays[libelle].<br>" ;
			}
		else
			{
			$fp = 0 ;
			if ($fraisport[$id] == "on")
				$fp = 1 ;
			$fg = 0 ;
			if ($fraisgestion[$id] == "on")
				$fg = 1 ;
			$DB_site2->query("update pays set TVAtauxnormal = '$txnormal[$id]', TVAtauxreduit = '$txreduit[$id]', port = '$fp', gestion = '$fg' where paysid = '$id'");
			}
		}
	$action = "" ;
	}

   
echo "<h3>Configuration des taux de TVA</h3>
Les pays qui apparaîssent ici sont ceux pour qui des frais de port ont été configurés.<br><br>" ;
if (!isset($action) or $action=="") 
	{
	echo "<font class=\"cost\">$erreur</font>
	<form method=\"post\" action=\"tva.php\">
	<input type=\"hidden\" name=\"action\" value=\"enregistrer\">
	<table class=\"table_coupscoeur\">
	<tr>
		<td colspan=\"2\" class=\"td_head_promos\" bgcolor=\"#EEEEEE\"><br>Pays<br><br></td>
		<td class=\"td_head_promos\" bgcolor=\"#EEEEEE\"><br>Tx normal<br><br></td>
		<td class=\"td_head_promos\" bgcolor=\"#EEEEEE\"><br>Tx réduit<br><br></td>
		<td class=\"td_head_promos\" bgcolor=\"#EEEEEE\" colspan=\"2\"><br>Appliquer la TVA (Tx normal) sur<br><br></td>
	</tr>" ;
	$payss=$DB_site2->query("SELECT DISTINCT (p.paysid), libelle, diminutif, TVAtauxnormal, TVAtauxreduit, port, gestion FROM pays p, fraisport fp WHERE p.paysid = fp.paysid");
	while ($pays=$DB_site2->fetch_array($payss)) 
		{
		switch ($pays[port])
			{
			case '0':
				$checkedport = "" ;
				break ;
			case '1':
				$checkedport = "checked" ;
				break ;
			}
		switch ($pays[gestion])
			{
			case '0':
				$checkedgestion = "" ;
				break ;
			case '1':
				$checkedgestion = "checked" ;
				break ;
			}
		$num = $pays[paysid] ;
		echo "<tr>
				<td><img border=\"0\" src=\"./images/drapeaux/br/$pays[diminutif].gif\"></td>
				<td>$pays[libelle]</td>
				<td align=\"center\"><input type=\"text\" name=\"txnormal[$num]\" value=\"$pays[TVAtauxnormal]\" size=\"2\"></td>
				<td align=\"center\"><input type=\"text\" name=\"txreduit[$num]\" value=\"$pays[TVAtauxreduit]\" size=\"2\"></td>
				<td><input type=\"checkbox\" name=\"fraisport[$num]\" $checkedport> Frais de port </td>
				<td><input type=\"checkbox\" name=\"fraisgestion[$num]\" $checkedgestion> Frais de gestion </td>
			</tr>" ;
		}
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