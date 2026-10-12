<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

$result="";
if(isset($_POST['display'])){
    $DB_site2->query("DELETE FROM display_marque WHERE display='".$_POST['display']."'");

    if(isset($_POST['dcat'])){
    foreach($_POST['dcat'] as $dcatid){
	$DB_site2->query("INSERT INTO display_marque SET marqueid=".$dcatid.", display='".$_POST['display']."'");
    }
    }

    $result="Les modifications ont été réalisées";
}

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
    <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
        <tr>
            <td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Affichage des marques</font>
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

$list=array();
$dcatlist=$DB_site2->query("SELECT marqueid,display FROM display_marque");
while($dcat=$DB_site2->fetch_array($dcatlist)) $list[$dcat['display']][]=$dcat[marqueid];

print "<div align=\"left\">
<form method=\"post\">
<input type=\"hidden\" name=\"display\" value=\"rechercheexpert\">".$result;
$cats=$DB_site2->query("SELECT marqueid,libelle FROM marque ORDER BY position");
while($cat=$DB_site2->fetch_array($cats)){
    $checked="";
    if(isset($list['rechercheexpert']) && in_array($cat[marqueid],$list['rechercheexpert'])) $checked="checked";
    print "<br><input type=\"checkbox\" name=\"dcat[]\" value=\"$cat[marqueid]\" $checked> ".$cat['libelle']."<div style=\"padding-left:20px;\">";
   
    print "</div>";
}
print "<p><input type=\"submit\" value=\"Modifier\"></p>
</form>
</div>";


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