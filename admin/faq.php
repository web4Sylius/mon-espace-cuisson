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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Aide à l'administration</font>
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

echo "<h3>Guide de l'utilisateur</h3>";


// définition des onglets
$onglets = array ("sommaire") ; // actions
$onglets_textes = array ("Sommaire") ; // textes affichés
$maxonglets = 4 ;
$onglet_en_cours = 0 ;
require "function3.php";
$contenu_onglet = "" ;

$contenu_onglet .= "<table border=0 cellpadding=\"0\" cellspacing=\"1\" align=\"center\" width=\"100%\">
						<tr>
							<td align=\"left\" width=\"40%\">Questions
							</td>
							<td bgcolor=\"#000044\" width=\"2%\" height=\"100%\"></td>
							<td align=\"left\" width=\"58%\">Réponses
							</td>
						</tr>
					</table>" ;

echo (ongletcontenu($contenu_onglet) );

if(isset($faqid) and $faqid!="") // si une question est sélectionnée
	{
	$faq = $DB_site->query_first("select *  from bou_faq2 WHERE faqid = '$faqid'") ;
	echo "<b> $faq[question] </b><br><br>
			$faq[reponse]<br><br>
			<a href=faq.php>Retour</a>";
	} 
else // sinon on affiche tout
	{
	$faqs = $DB_site->query("select *  from bou_faq2 order by rang asc") ;
	echo "<table align=\"center\">
			<tr>
				<td>" ;
	while ($faq=$DB_site->fetch_array($faqs)) 
		{
		echo "<a href=\"faq.php?faqid=$faq[faqid]\">$faq[question]</a><br>";
		}
	echo "</td>
		 </tr>
	   </table>" ;
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
        </tr>";

include "./footer.php";
?>