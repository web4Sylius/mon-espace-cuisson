<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";


$expdate=time()-(86400*20);

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
    <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
        <tr>
            <td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Gestion GC+</font>
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

echo "<table cellspacing=\"0\">
<tr style=\"text-align:center; font-weight:bold;\"><td width=\"120\">Date</td><td width=\"80\">N°</td><td>Client</td><td>Pris en compte</td><td>Envoyé</td></tr>";
$sql="SELECT f.facturid, UNIX_TIMESTAMP(f.dateexpedition) AS datefacture, u.nom, u.prenom  FROM facture f,lignefacturecaracteristique lc, lignefacture l, utilisateur u WHERE l.factureid=f.facturid AND f.valide=5 AND lc.caractvalid IN (132,133) AND lc.lignefactureid = l.lignefactureid AND u.userid=f.userid ORDER BY datefacture DESC";
$requete=mysql_query($sql);
while($f=mysql_fetch_array($requete)){
      $reqsfg=mysql_query("SELECT * FROM gcp WHERE facturid=".$f['facturid']);
      $pdf=mysql_fetch_array($reqsfg);
      $pdftd=$senttd="";
      if($pdf!=false){
	  $pdftd="<span style=\"color:green; font-weight:bold;\">OK</span>";
// 	  $senttd="<input type=\"checkbox\" ";
// 	  if($pdf['customer_sent']!=0) $senttd.="checked";
// 	  $senttd.=" onclick=\"document.location='liste_sfg.php?send=$f[facturid]'\">";
// 	  if($pdf['customer_sent']!=0) $senttd.="<br>".date("d/m/Y",$pdf['customer_sent']);
      }elseif($f['datefacture']<$expdate){
	  if($f['datefacture']<$expdate-(5*86400)){
	    $pdftd="<span style=\"color:red;\">Erreur</span>";
	    $senttd="<a href=\"gcp.php?retard=".$f['facturid']."\" target=\"_blank\">Envoi manuel</a>";
	  }else
	    $pdftd="<span style=\"color:#FFAA00;\">En attente</span>";
      }
      echo "<tr onmouseover=\"this.style.backgroundColor='#ececec';\" onmouseout=\"this.style.backgroundColor='';\"><td>".date("d/m/Y",$f['datefacture'])."</td><td><a href=\"facture.php?facturid=$f[facturid]&action=editer\">$f[facturid]</a></td><td>$f[nom] $f[prenom]</td><td style=\"text-align:center;\">".$pdftd."</td><td style=\"text-align:center;\">".$senttd."</td></tr>";
}
echo "</table>";

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