<?php

require "admin_global.php";
echo "<SCRIPT LANGUAGE=\"JavaScript\">
<!-- 
function ajouter(action_envoyee)
	{
	document.formu.action2.value=action_envoyee;
	document.formu.submit() ;
	}
//-->
</script>" ;
include "./header.php";
include "./menu.php";
include "./fonctions.php";	


echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Pages récapitulant les tops</font>
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
   <td align=\"left\" valign=\"middle\">" ;
if (isset($action) and $action == "change")
	{
	$DB_site2->query("update parametresgeneraux set page_promos_active = '$promos', page_topsventes_active = '$topsventes', page_nouveautes_active = '$nouveautes'") ;
	}

	$pages=$DB_site2->query_first("select page_promos_active, page_topsventes_active, page_nouveautes_active from parametresgeneraux") ;
	switch ($pages[0])
		{
		case '0':
			$checked00 = "checked" ;
			$checked01 = "" ;
			break ;
		case '1':
			$checked00 = "" ;
			$checked01 = "checked" ;
			break ;
		}
	switch ($pages[1])
		{
		case '0':
			$checked10 = "checked" ;
			$checked11 = "" ;
			break ;
		case '1':
			$checked10 = "" ;
			$checked11 = "checked" ;
			break ;
		}
	switch ($pages[2])
		{
		case '0':
			$checked20 = "" ;
			$checked20 = "checked" ;
			break ;
		case '1':
			$checked20 = "" ;
			$checked21 = "checked" ;
			break ;
		}

echo "<br><form method=\"post\" action=\"pages_tops.php\">
	<input type=\"hidden\" name=\"action\" value=\"change\">
	<ul>" ;

	if (in_array ("5", $modules)) // vérification module promotions
		{
		echo "<li><strong>Promotions</strong></li>
				<blockquote>
				<table>
					<tr>
						<td>
							<input type=\"radio\" name=\"promos\" value=\"1\" $checked01> Active 
							<br><input type=\"radio\" name=\"promos\" value=\"0\" $checked00> Inactive
						</td>
					</tr>
				</table>
				</blockquote>" ;
		}
	
	if (in_array ("19", $modules)) // vérification module tops ventes
		{
		echo "<li><strong>Tops ventes</strong></li>
				<blockquote>
				<table width=\"100%\">
					<tr>
						<td>
							<input type=\"radio\" name=\"topsventes\" value=\"1\" $checked11> Active 
							<br><input type=\"radio\" name=\"topsventes\" value=\"0\" $checked10> Inactive
						</td>
					</tr>
				</table>
				</blockquote>" ;
		}
		
	if (in_array ("17", $modules)) // vérification module nouveautés
		{
		echo "<li><strong>Nouveautés</strong></li>
				<blockquote>
				<table>
					<tr>
						<td>
							<input type=\"radio\" name=\"nouveautes\" value=\"1\" $checked21> Active 
							<br><input type=\"radio\" name=\"nouveautes\" value=\"0\" $checked20> Inactive
						</td>
					</tr>
				</table>
				</blockquote>" ;
		}
		
 echo "</ul>
 	<center><input type=\"submit\" value=\"Valider\"></center>
 </form>";


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