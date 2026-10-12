<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

if(isset($_POST['causeid']) && isset($_POST['commentaire'])){
    $DB_site2->query("UPDATE causeperte SET commentaire='".$_POST['commentaire']."' WHERE id=".$_POST['causeid']);
}

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Clients perdus</font>
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

$causes=array(
	0=>"prix",
	1=>"délai",
	2=>"projet abandonné",
	3=>"trop relancé",
	4=>"mauvais rapports commerciaux",
	5=>"ne veux pas dire",
	6=>"autre",
	7=>"Client injoignable",
	8=>"Double compte",
	9=>"Petit dossier",
	10=>"achat ailleurs",
	11=>"Faux numéro/email",
	12=>"Petit dossier",
  13=>"Sans conseils"
	);
$from=time()-date("t")*86400;
$to=time();

if(isset($_GET['from']) && isset($_GET['to'])){
    $s=explode("/",$_GET['from']);
    $from=mktime(0,0,0,$s[1],$s[0],$s[2]);
    $s=explode("/",$_GET['to']);
    $to=mktime(0,0,0,$s[1],$s[0],$s[2]);
}

print "<script src=\"calendrier.js\" language=\"javascript\"></script>
<script language=\"javascript\">

function com(id){

    for(i=0;i<document.getElementsByTagName(\"INPUT\").length;i++){
	if(document.getElementsByTagName(\"INPUT\")[i].name.substr(0,6)=='combtn')
	     document.getElementsByTagName(\"INPUT\")[i].style.display='none';
    }
    document.getElementsByName('combtn_'+id)[0].style.display='inline';
    document.getElementsByName('causeid')[0].value=id;
}
function submitcom(){
    var causeid=document.getElementsByName('causeid')[0].value;
    document.getElementsByName('commentaire')[0].value=document.getElementById('com_'+causeid).value;
    document.forms.comform.submit();
}
</script>
<style>
.td td {border-bottom:solid thin black;padding:2px;}
</style>
<form method=\"get\">
Du <input type=\"text\" id=\"from\" name=\"from\" size=\"8\" onclick=\"calendrier_show(this.id,this.value);\" value=\"".$_GET['from']."\" readonly> au <input type=\"text\" id=\"to\" name=\"to\" size=\"8\" onclick=\"calendrier_show(this.id,this.value);\" value=\"".$_GET['to']."\" readonly> <input type=\"submit\" value=\"OK\">
</form>";

print "<table class=\"table_factures td\" style=\"width:800px;\">
<tr><td>Client</td><td>Montant</td><td>Date perdu</td><td>Cause</td><td>Article commandé</td><td width=\"250\">Dernier suivi</td><td>Commentaire</td></tr>";

$totalcause=array();
$requete=$DB_site2->query("SELECT * FROM causeperte c INNER JOIN utilisateur u USING (userid) WHERE c.date BETWEEN $from AND $to ORDER BY date DESC");
while($cause=$DB_site2->fetch_array($requete)){
    $totalttc=0;
    $facture=$DB_site2->query_first("SELECT * FROM facture WHERE userid=".$cause['userid']." ORDER BY heurefacture DESC LIMIT 1");
    if($facture['facturid']!=""){
      $lignes=$DB_site2->query_first("SELECT SUM(prix*qte) FROM lignefacture WHERE factureid=".$facture['facturid']);
      $totalttc=$lignes[0]+$facture['prixport']-$facture['montantcadeau'];
    }
    $suivi=$DB_site2->query_first("SELECT * FROM suivi_client WHERE userid=".$cause['userid']." ORDER BY date DESC LIMIT 1");
    print "<tr><td><a href=\"utilisateur.php?action=editer&userid=".$cause['userid']."\" target=\"_blank\">".$cause['nom']." ".$cause['prenom']."<br>$cause[userid]</a><br>".$cause['dateinscription']."</td><td>".number_format($totalttc,2,',','')."</td><td>".date("d/m/Y H:i",$cause['date'])."</td><td>".$causes[$cause['cause']]."</td><td>".$cause['article']." ".$cause['marque']."<br>".$cause['lieu']."<br>".$cause['prix']."</td><td>".$suivi['suivi']."</td><td><textarea cols=\"30\" id=\"com_$cause[id]\" onclick=\"com($cause[id])\">$cause[commentaire]</textarea><input type=\"button\" name=\"combtn_$cause[id]\" value=\"OK\" onclick=\"submitcom();\" style=\"display:none;\"></td></tr>";
    $totalcause[$cause['cause']]++;
}

print "</table>";

print "<form name=\"comform\" method=\"post\">
<input type=\"hidden\" name=\"causeid\">
<input type=\"hidden\" name=\"commentaire\">
</form>";

arsort($totalcause);
$total=array_sum($totalcause);
print "<br><table class=\"table_factures\">";
foreach($totalcause as $cause=>$nb){
    $pc=round(100*$nb/$total);
    print "<tr><td>".$causes[$cause]."</td><td align=\"right\">".$nb."</td><td><div style=\"width:".$pc."px;background-color:red;\">".$pc."%</div></td></tr>";
}
print "</table>";

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