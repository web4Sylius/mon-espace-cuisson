<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

$ext=array("jpg","png","gif","swf");

$error="";
if(isset($action)){
    
    if($action=="ajouter2"){
      unset($action);
      $DB_site2->query("INSERT INTO bloc_ip SET ip='".$_POST['ip']."', motif='".$_POST['motif']."', date=".time()); 
    }

    if($action=="modifier2"){
      unset($action);
      $DB_site2->query("UPDATE bloc_ip SET ip='".$_POST['ip']."', motif='".$_POST['motif']."' WHERE id=".$_POST['id']);
    }

    if(isset($supprimer2) && isset($id)){
	$DB_site2->query("DELETE FROM bloc_ip WHERE id=$id");
	unset($action);
    }
}

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
    <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
        <tr>
            <td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Blocage des IP</font>
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
   <td align=\"justify\" valign=\"middle\">
    <font class=\"txt_2\">
        <table align=\"center\" width=\"100%\">
            <tr>
                <td align=\"center\" valign=\"middle\" width=\"100%\">" ;

if(!isset($action)){
    print "<p><a href=\"blocip.php?action=ajouter\">Ajouter une IP</a></p>";
    print "<table>
    <tr><td>IP</td><td>Motif</td><td>Date</td><td>Connexions</td></tr>";
    $liste=$DB_site2->query("SELECT * FROM bloc_ip ORDER BY date");
    while($ip=$DB_site2->fetch_array($liste)){
	print "<tr><td>".$ip['ip']."</td><td>".$ip['motif']."</td><td>".date("d/m/Y",$ip['date'])."</td><td>$ip[hit]</td><td><a href=\"blocip.php?action=modifier&id=".$ip['id']."\">Modifier</a> <a href=\"\">Supprimer</a></td></tr>";
    }
    print "</table>";

}else{
    if($action=="modifier"){
	$ip=$DB_site2->query_first("SELECT * FROM bloc_ip WHERE id=".$id);
	print "<form action=\"blocip.php?action=modifier2\" method=\"post\">
	<input type=\"hidden\" name=\"id\" value=\"$ip[id]\">
	<table>
	<tr><td align=\"right\">IP :</td><td><input type=\"text\" name=\"ip\" value=\"$ip[ip]\"></td></tr>
	<tr><td align=\"right\">Motif :</td><td><input type=\"text\" name=\"motif\" value=\"$ip[motif]\"></td></tr>
	<tr><td colspan=\"2\"><input type=\"submit\" value=\"Modifier\"> <input type=\"button\" value=\"Annuler\" onclick=\"history.back();\"></td></tr>
	</table>
	</form>";
    }
    if($action=="ajouter"){
	
	print "<form action=\"blocip.php?action=ajouter2\" method=\"post\">
	<table>
	<tr><td colspan=\"2\">$error</td></tr>
	<tr><td align=\"right\">IP :</td><td><input type=\"text\" name=\"ip\" value=\"\"></td></tr>
	<tr><td align=\"right\">Motif :</td><td><input type=\"text\" name=\"motif\" value=\"\"></td></tr>
	<tr><td colspan=\"2\"><input type=\"submit\" value=\"Ajouter\"> <input type=\"button\" value=\"Annuler\" onclick=\"history.back();\"></td></tr>
	</table>
	</form>";
    }

    if($action=="supprimer"){
	print "Confirmez vous la suppression ?<br><a href=\"blocip.php\">NON</a> | <a href=\"blocip.php?action=supprimer2&id=$id\">OUI</a>";
    }

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