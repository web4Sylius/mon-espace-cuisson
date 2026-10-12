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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Location de boutique</font>
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

?>
<h3>Factures existantes</h3>
<table class="table_location">
<tr>
<td class="td_head_location" height="25">Periode</td>
<td  class="td_head_location">Etat</td>
<td  class="td_head_location">Action</td>
</tr>
<?php
$factures = $DB_site->query( "select factureid, etatfacture, datefacture, passefacture 
							  from facture_location 
							  where clientid = '$user_info[clientid]' 
							  order by datefacture desc" );
while ($facture=$DB_site->fetch_array($factures)) 
{
	$factureid = $facture[factureid] ; 
	$datefacture = $facture[datefacture] ;
	$passefacture = $facture[passefacture] ; 
	$etatfacture = $facture[etatfacture] ; 

$a = substr ($datefacture, 0, 4) ;
$m = substr ($datefacture, 5, 2) ;
switch ($m) 
{
case '01':
   $m_lettre = "Janvier" ;
   break ;
case '02':
   $m_lettre = "Fevrier" ;
   break ;
case '03':
   $m_lettre = "Mars" ;
   break ;
case '04':
   $m_lettre = "Avril" ;
   break ;
case '05':
   $m_lettre = "Mai" ;
   break ;
case '06':
   $m_lettre = "Juin" ;
   break ;
case '07':
   $m_lettre = "Juillet" ;
   break ;
case '08':
   $m_lettre = "Aout" ;
   break ;
case '09':
   $m_lettre = "Septembre" ;
   break ;
case '10':
   $m_lettre = "Octobre" ;
   break ;
case '11':
   $m_lettre = "Novembre" ;
   break ;
case '12':
   $m_lettre = "Decembre" ;
   break ;
}
?>
<tr>
<td class="td_location" align="left">
<?
echo "$m_lettre $a" ; 
?>
</td>
<td class="td_location">
<?php
switch ($etatfacture) 
	{
	case '0':
	   $selected0 = "selected" ;
	   $selected1 = "" ;
	   $selected2 = "" ;
	   $selected3 = "" ;
		$selectetat = "<font color=\"red\">Non reglée</font>" ;
	   break ;
	case '1':
		$selectetat = "<font color=\"#009900\">Réglée</font>" ;
	   break ;
	case '2':
		$selectetat = "<font color=\"red\">Impayée</font>" ;
	   break ;
	case '3':
		$selectetat = "<font color=\"#009900\">Gratuit</font>" ;
	   break ;
	}
echo $selectetat ;


?>
</td>
<td class="td_location">
<a href="facture_pdf.php?facture=<?=$factureid ; ?>&passefacture=<?=$passefacture; ?>" target="_blank">EDITER</a>
</td>
</tr>
<?php
}
?>
</table>	
<bR>
<br>	


<?php
echo "
				</td>
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
