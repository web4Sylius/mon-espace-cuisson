<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include ("../class/facture.class.php");
include("sav.class.php");

$today=mktime(0,0,0);
$tomorrow=$today+86400;
$tomorrow2=$today+(2*86400);
$yesterday=$today-(100*86400);
$savtypes=array(0=>"Transport/installation",1=>"Panne",2=>"Erreur produit",3=>"Rétractation/annulation",4=>"Manque produit");

// if(!isset($user_rappel)) $user_rappel=$user_info['username'];


print "<style>
.tbl { border:solid 1px black; border-collapse:collapse; }
.tbl td { background-color:#DDD; border-bottom:solid 2px white; padding:2px; }
.tbl .done td { background-color:#BBFFBB; }
</style>";

print "<script language=\"javascript\">
function showrappel(name){
  if(document.getElementById(name).style.display=='none'){
    document.getElementById('ic'+name).src='images/moins.jpg';
    document.getElementById(name).style.display='table';
  }else{
    document.getElementById('ic'+name).src='images/plus.jpg';
    document.getElementById(name).style.display='none';
  }
}
document.title=\"Suivi SAV\";

</script>";

print "<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
    <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
        <tr>
            <td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Suivi SAV</font>
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
<tr><td>";

$optcom="<option onclick=\"document.location='suivi_sav.php?user_rappel=';\">Tous</option>";
$req=$DB_site2->query("SELECT pour FROM suivi_commande WHERE pour<>'' AND rappel>UNIX_TIMESTAMP(NOW()) group by pour ORDER BY pour");
while($commercial=$DB_site2->fetch_array($req)) $optcom.="<option onclick=\"document.location='suivi_sav.php?user_rappel=$commercial[0]';\"".($_GET['user_rappel']==$commercial[0]?" selected":"").">$commercial[0]</option>";
print "Interlocuteur : <select>$optcom</select>";

$tr=array();
$sql="SELECT facturid,sav.id FROM facture f INNER JOIN sav USING(facturid) WHERE fin=0 AND (SELECT COUNT(*) FROM suivi_commande WHERE facturid=f.facturid AND rappel>".time().")=0";
$requete=$DB_site2->query($sql);
while($row=$DB_site2->fetch_array($requete)){
    $marques=array();
    $facture=new facture($DB_site2,$row['facturid']);
    $sav=new sav($DB_site2,$row['id']);
    $suivi=$DB_site2->query_first("SELECT * FROM suivi_commande WHERE facturid=".$facture->facturid." ORDER BY date DESC LIMIT 1");
    $vip=usericon($facture->utilisateur->vip,20);
    foreach($sav->lignes as $lignefactureid=>$qte) $marques[]=$facture->lignes[$lignefactureid]->marque;
    $marques=array_unique($marques);
    $responsable=responsable($sav);
    switch($order){
      case "societe":
      $key=$facture->utilisateur->raisonsociale;
      break;
      case "nom":
      $key=$facture->utilisateur->nom;
      break;
      case "facturid":
      $key=$facture->facturid;
      break;
      case "datecommande":
      $key=$facture->datecommande;
      break;
      case "commercial":
      $key=$facture->commercial;
      break;
      case "type":
      $key=$sav->typelitige;
      break;
      case "responsable":
      $key=$responsable;
      break;
      case "marques":
      $key=implode(",",$marques);
      break;
      case "suivi":
      $key=$suivi['date'];
      break;
      default:
      $key=$facture->datefacture;
    }
    $tr[$key].="<tr><td>".$vip.$facture->utilisateur->raisonsociale."</td><td>".$facture->utilisateur->prenom." ".$facture->utilisateur->nom."</td><td><a href=\"facture.php?action=editer&facturid=".$facture->facturid."&savid=".$sav->id."\" target=\"_blank\">".$facture->facturid."</a></td><td>".date("d/m/Y",$facture->datecommande)."</a></td><td>".$savtypes[$sav->typelitige]."</td><td>".$responsable."</td><td>".implode("<br>",$marques)."</td><td><b>".$suivi['commercial'].date(" d/m/Y H:i",$suivi['date'])."</b><br>".stripslashes($suivi['suivi'])."</td></tr>";
}

ksort($tr);
print "<h3><img src=\"images/moins.jpg\" id=\"icnouveau\" onclick=\"showrappel('nouveau')\" align=\"absmiddle\"> SAV sans rappel</h3>
<table id=\"nouveau\" class=\"tbl\">
<tr>
  <td><a href=\"suivi_sav.php?user_rappel=$user_rappel&order=societe\">Société</a> ".($order=="societe"?"<font color=\"red\">&#9650;</font>":"")."</td>
  <td><a href=\"suivi_sav.php?user_rappel=$user_rappel&order=nom\">Client</a> ".($order=="nom"?"<font color=\"red\">&#9650;</font>":"")."</td>
  <td><a href=\"suivi_sav.php?user_rappel=$user_rappel&order=facturid\">N° commande</a> ".($order=="facturid"?"<font color=\"red\">&#9650;</font>":"")."</td>
  <td><a href=\"suivi_sav.php?user_rappel=$user_rappel&order=datecommande\">Date commande</a> ".($order=="datecommande"?"<font color=\"red\">&#9650;</font>":"")."</td>
  <td><a href=\"suivi_sav.php?user_rappel=$user_rappel&order=type\">Type SAV</a> ".($order=="type"?"<font color=\"red\">&#9650;</font>":"")."</td>
  <td><a href=\"suivi_sav.php?user_rappel=$user_rappel&order=responsable\">Responsable</a> ".($order=="responsable"?"<font color=\"red\">&#9650;</font>":"")."</td>
  <td><a href=\"suivi_sav.php?user_rappel=$user_rappel&order=marques\">Marque</a> ".($order=="marques"?"<font color=\"red\">&#9650;</font>":"")."</td>
  <td><a href=\"suivi_sav.php?user_rappel=$user_rappel&order=suivi\">Dernier suivi</a> ".($order=="suivi"?"<font color=\"red\">&#9650;</font>":"")."</td>
</tr>
".implode("",$tr)."
</table>";

$tr=rappelbetween($today,$tomorrow);
print "<h3><img src=\"images/moins.jpg\" id=\"icjour\" onclick=\"showrappel('jour')\" align=\"absmiddle\"> Rappels du jour</h3>
<table id=\"jour\" class=\"tbl\">
<tr><td>Société</td><td>Client</td><td>N° commande</td><td>Date commande</td><td>Type SAV</td><td>Responsable</td><td>Marque</td><td style=\"width:300px;\">Dernier suivi</td><td>Rappel</td></tr>
".implode("",$tr)."
</table>";

$tr=rappelbetween($tomorrow,$tomorrow+(60*86400));
print "<h3><img src=\"images/plus.jpg\" id=\"icfutur\" onclick=\"showrappel('futur')\" align=\"absmiddle\"> Rappels futurs</h3>
<table id=\"futur\" class=\"tbl\" style=\"display:none;\">
<tr><td>Société</td><td>Client</td><td>N° commande</td><td>Date commande</td><td>Type SAV</td><td>Responsable</td><td>Marque</td><td style=\"width:300px;\">Dernier suivi</td><td>Rappel</td></tr>
".implode("",$tr)."
</table>";

print "</td> 
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

function rappelbetween($from,$to){
    global $DB_site2;
    global $savtypes;
    global $user_rappel;

    $ligne=array();
    if($user_rappel!="") $sqlpour="pour='$user_rappel' AND";
    $sql="SELECT facturid,sav.id,rappel,pour,suivi,suivi_commande.date,commercial FROM suivi_commande INNER JOIN sav USING(facturid) WHERE $sqlpour rappel BETWEEN $from AND $to";
    $rq=$DB_site2->query($sql);
    while($suivi=$DB_site2->fetch_array($rq)){
	$facture=new facture($DB_site2,$suivi['facturid']);
	$sav=new sav($DB_site2,$suivi['id']);
	$vip=usericon($facture->utilisateur->vip,20);
	$marques=array();
	foreach($sav->lignes as $lignefactureid=>$qte) $marques[]=$facture->lignes[$lignefactureid]->marque;
	$marques=array_unique($marques);
	$class="";
	$verif=$DB_site2->query_first("SELECT date,commercial FROM suivi_commande WHERE facturid=".$facture->facturid." ORDER BY date DESC LIMIT 1");
	if($verif[0]>$suivi['date'])
	  $class="done";
	else 
	  $verif=$suivi;
	$ligne[$suivi['rappel']].="<tr class=\"$class\"><td>".$vip.$facture->utilisateur->raisonsociale."</td><td>".$facture->utilisateur->prenom." ".$facture->utilisateur->nom."</td><td><a href=\"facture.php?action=editer&facturid=".$facture->facturid."&savid=".$sav->id."\" target=\"_blank\">".$facture->facturid."</a></td><td>".date("d/m/Y",$facture->datecommande)."</td><td>".$savtypes[$sav->typelitige]."</td><td>".responsable($sav)."</td><td>".implode("<br>",$marques)."</td><td><b>".$verif['commercial'].date(" d/m/Y H:i :",$verif['date'])."</b><br>".stripslashes($suivi['suivi'])."</td><td>".$suivi['pour']."<br>".date("d/m/Y H:i",$suivi['rappel'])."</td></tr>";
   }
   ksort($ligne);
   return $ligne;
}

function responsable($sav){
   switch($sav->typelitige){
      case 0:
      return $sav->fields["sav_responsable"];
      break;
      case 1:
      if($sav->fields["optsav_prischarge"]==0)
	return $sav->fields["sav_prischargesav"];
      else
	return "SFG";
      break;
      case 2:
      case 4:
      switch($sav->fields["sav_responsable"]){
	case 0:return "Aucun";
	case 1:return "Fournisseur";
	case 2:return "Transporteur";
	case 3:return "Installateur";
	case 4:return "Acilyon";
	case 5:return "Client";
      }
      break;
   }
}

include "footer.php";