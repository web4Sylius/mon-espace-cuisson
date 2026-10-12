<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "../class/facture.class.php";
include "../class/fonctions.php";

$annee=date("Y");
if(isset($_GET['year'])) $annee=intval($_GET['year']);
$encours=$annee==date("Y");

$seuils=array(64=>array(100000,85000), //allemagne
10=>array(35000,30000), //autriche
17=>array(35000,30000), //belgique
26=>array(70000,30000), //bulgarie
184=>array(35000,30000), //chypre
45=>array(280000,30000), //danemark
153=>array(35000,30000), //espagne
53=>array(35000,30000), //estonie
56=>array(35000,30000), //finlande
66=>array(35000,30000), //grece
75=>array(8800000,30000), //hongrie
81=>array(35000,30000), //irelande
83=>array(35000,30000), //italie
186=>array(34052,30000), //lettonie
98=>array(36203,30000), //lithuanie
99=>array(100000,85000), //luxembourg
105=>array(35000,30000), //malte
118=>array(100000,85000), //pays bas
132=>array(160000,40000), //pologne
133=>array(35000,30000), //portugal
148=>array(35000,30000), //slovaquie
44=>array(1140000,30000), //rep tcheque
136=>array(118000,30000), //roumanie
172=>array(70000,85000), //uk
149=>array(35000,30000), //slovénie
157=>array(320000,30000) //suède
);

$change=$devises->dailychange();
ksort($change);


print "<style>
.ca { color:black; border-collapse:collapse; border:solid thin black; }
.ca td { border:solid thin black; }
.ca_nb { text-align:right; white-space:nowrap; }
.ca_title { background-color:yellow; text-align:center; }
.ca_historique { background-color:green; text-align:center; }
.ca thead { font-weight:bold; text-align:center; }
.ca .red { color:red; }
.ca .sep { border:none; height:20px; }
.ca .total { font-weight:bold; }
</style>

<script language=\"javascript\">
function showpopup(id){
    document.getElementById(id).style.visibility='visible';
    document.getElementById(id).style.backgroundColor='#AAFFAA';
    document.getElementById(id).parentNode.style.backgroundColor='#AAFFAA';
}
function hidepopup(id){
    document.getElementById(id).style.visibility='hidden';
    document.getElementById(id).parentNode.style.backgroundColor='';
}
</script>";

echo "<p align=\"center\">Année : <select name=\"year\" onchange=\"document.location='chiffre_export.php?year='+this.value\">";
for($y=2010;$y<=(2009+count($objectif_facture));$y++) echo "<option value=\"$y\"".($y==$annee?" selected":"").">$y</option>";
echo "</select></p>";

print "<table style=\"display:none;\">";
foreach($change as $currency=>$value) print "<tr><td>$currency</td><td>$value</td></tr>";
print "</table>";

print "<table class=\"ca\">
<thead>
<tr>
  <td class=\"ca_title\" colspan=\"12\">Clients de l'union Européenne</td>
  <td class=\"ca_historique\" colspan=\"8\">Historique - temps réel / N-1 et N-2</td>
</tr>
<tr>
  <td colspan=\"2\">Pays</td>
  <td>Client part.</td>
  <td>Client pro</td>
  <td>Sous total HT</td>
  <td>Seuil HT</td>
  <td>Monnaie locale</td>
  <td>Alerte</td>
  <td>TVA locale</td>
  <td>N°TVA</td>
  <td>client pro tva intra</td>
  <td>Total CA HT</td>
  <td>Position export</td>
  <td>Nbre cde</td>
  <td>cde moy.</td>
  <td>% export</td>
  <td>av/ret / N-1</td>
  <td>% N-1</td>
  <td>av/ret / N-2</td>
  <td>% N-2</td>
</tr>
</thead>";


$paysdata=array();
$totalpays=array();
$listefacture=array();
$sql="SELECT f.* FROM facture f INNER JOIN utilisateur u USING(userid) INNER JOIN pays p ON(p.paysid=u.paysid) WHERE valide IN (5,8) AND p.tvaintracom=1 AND f.dateexpedition LIKE '$annee-%'";
$req=$DB_site2->query($sql);
while($f=$DB_site2->fetch_array($req)) {
    $facture=new facture($DB_site2,$f);
    if(!isset($paysdata[$facture->utilisateur->paysid])) $paysdata[$facture->utilisateur->paysid]=array("CApart"=>0,"CApro"=>0,"CAintra"=>0,"nb"=>0);
    
    if($facture->utilisateur->raisonsociale=="")
      $cat="part";
    else
      $cat=$facture->utilisateur->apply_intracom?"intra":"pro";
    $paysdata[$facture->utilisateur->paysid]['CA'.$cat]+=$facture->totalht;
    $totalpays[$facture->utilisateur->paysid]+=$facture->totalht;
    $paysdata[$facture->utilisateur->paysid]['nb']++;
    $listefacture[$facture->utilisateur->paysid][]=$facture;
}

arsort($totalpays);
$total=array_sum($totalpays);
$paysrank=array_flip(array_keys($totalpays));

$nm1=annee_prec($annee-1,true,$encours);
$nm2=annee_prec($annee-2,true,$encours);

$sum=array('CApart'=>0,'CApro'=>0,'CAintra'=>0);

$req=$DB_site2->query("SELECT paysid,libelle,diminutif,TVAlocale,currency FROM pays WHERE tvaintracom=1 ORDER BY libelle");
while($pays=$DB_site2->fetch_array($req)) {
    $partpro=$paysdata[$pays['paysid']]['CApart']+$paysdata[$pays['paysid']]['CApro'];
    $local="";
    $seuileur=$seuils[$pays['paysid']][0];
    if(isset($change[$pays['currency']])){
	$seuileur=round($seuils[$pays['paysid']][0]*$change[$pays['currency']]);
	$local=$seuils[$pays['paysid']][0].$pays['currency'];
    }
    $color=($paysdata[$pays['paysid']]['CApart']+$paysdata[$pays['paysid']]['CApro']>$seuileur?"red":"");
//  $local=number_format($change[$pays['currency']]*$partpro,2,',','').$pays['currency'];
    $moy=0;
    if($paysdata[$pays['paysid']]['nb']>0) $moy=$totalpays[$pays['paysid']]/$paysdata[$pays['paysid']]['nb'];
    
    $diffnm1=$totalpays[$pays['paysid']]-$nm1[$pays['paysid']];
    $diffnm2=$totalpays[$pays['paysid']]-$nm2[$pays['paysid']];
    $pcdiffnm1=$pcdiffnm2="";
    if($nm1[$pays['paysid']]>0) $pcdiffnm1=100*$diffnm1/$nm1[$pays['paysid']];
    if($nm2[$pays['paysid']]>0) $pcdiffnm2=100*$diffnm2/$nm2[$pays['paysid']];
    $pcexport=$total>0?100*$totalpays[$pays['paysid']]/$total:0;
    
    $sum['CApart']+=$paysdata[$pays['paysid']]['CApart'];
    $sum['CApro']+=$paysdata[$pays['paysid']]['CApro'];
    $sum['CAintra']+=$paysdata[$pays['paysid']]['CAintra'];
    $sum['nb']+=$paysdata[$pays['paysid']]['nb'];
    
    print "<tr>
      <td><img src=\"images/drapeaux/br/".$pays['diminutif'].".gif\"></td>
      <td>".$pays['libelle']."</td>
      <td class=\"ca_nb $color\">".number_format($paysdata[$pays['paysid']]['CApart'],2,',','')."&euro;</td>
      <td class=\"ca_nb $color\">".number_format($paysdata[$pays['paysid']]['CApro'],2,',','')."&euro;</td>
      <td class=\"ca_nb $color\">".number_format($partpro,2,',','')."&euro;</td>
      <td class=\"ca_nb $color\">".$seuileur."&euro;</td>
      <td class=\"ca_nb $color\">".$local."</td>
      <td class=\"ca_nb $color\">".round($seuileur-5000)."&euro;</td>
      <td class=\"ca_nb\">".$pays['TVAlocale']."%</td>
      <td class=\"ca_nb\"></td>
      <td class=\"ca_nb\">".number_format($paysdata[$pays['paysid']]['CAintra'],2,',','')."&euro;</td>
      <td class=\"ca_nb\" onmouseover=\"showpopup('factures".$pays['paysid']."')\" onmouseout=\"hidepopup('factures".$pays['paysid']."')\">".number_format($paysdata[$pays['paysid']]['CApart']+$paysdata[$pays['paysid']]['CApro']+$paysdata[$pays['paysid']]['CAintra'],2,',','')."&euro;<div id=\"factures".$pays['paysid']."\" class=\"popup\">".formatlist($pays['paysid'])."</div></td>
      <td class=\"ca_nb\">".(isset($paysrank[$pays['paysid']])?($paysrank[$pays['paysid']]+1):"")."</td>
      <td class=\"ca_nb\">".$paysdata[$pays['paysid']]['nb']."</td>
      <td class=\"ca_nb\">".number_format($moy,2,',','')."</td>
      <td class=\"ca_nb\">".number_format($pcexport,2,',','')."%</td>
      ".tdvalue($diffnm1,"&euro;",true).
      tdvalue($pcdiffnm1,"%",true).
      tdvalue($diffnm2,"&euro;",true).
      tdvalue($pcdiffnm2,"%",true)."
    </tr>";
}

$total_ue=array_sum($sum);

$moy=0;
$sumtotalpays=array_sum($totalpays);
$sumnm1=array_sum($nm1);
$sumnm2=array_sum($nm2);
if($sum['nb']>0) $moy=$sumtotalpays/$sum['nb'];
$diffnm1=$sumtotalpays-$sumnm1;
$diffnm2=$sumtotalpays-$sumnm2;
$pcdiffnm1=($sumnm1==0?"":100*$diffnm1/$sumnm1);
$pcdiffnm2=($sumnm2==0?"":100*$diffnm2/$sumnm2);

print "<tr class=\"total\">
	<td colspan=\"2\">Total</td>
	<td class=\"ca_nb\">".number_format($sum['CApart'],2,',','')."&euro;</td>
	<td class=\"ca_nb\">".number_format($sum['CApro'],2,',','')."&euro;</td>
	<td class=\"ca_nb\">".number_format($sum['CApart']+$sum['CApro'],2,',','')."&euro;</td>
	<td colspan=\"5\"></td>
	<td class=\"ca_nb\">".number_format($sum['CAintra'],2,',','')."&euro;</td>
	<td class=\"ca_nb\">".number_format($total_ue,2,',','')."&euro;</td>
	<td></td>
	<td class=\"ca_nb\">".$sum['nb']."</td>
	<td class=\"ca_nb\">".number_format($moy,2,',','')."</td>
	<td></td>"
	.tdvalue($diffnm1,"&euro;",true).
	tdvalue($pcdiffnm1,"%",true).
	tdvalue($diffnm2,"&euro;",true).
	tdvalue($pcdiffnm2,"%",true)."
      </tr>";
      
print "<tr><td class=\"sep\"></td></tr>";
      
print "<tr>
	 <td colspan=\"8\"></td>
	 <td colspan=\"3\" class=\"ca_nb\">Seuil DEB complexe</td>
	 <td class=\"ca_nb\">460000,00&euro;</td>
	</tr>
	<tr>
	 <td colspan=\"8\"></td>
	 <td colspan=\"3\" class=\"ca_nb\">Alerte 2</td>
	 <td class=\"ca_nb\">400000,00&euro;</td>
	</tr>";
      
print "<tr><td class=\"sep\"></td></tr>";

$paysdata=array();
$totalpays=array();
$listefacture=array();
$sql="SELECT f.* FROM facture f INNER JOIN utilisateur u USING(userid) INNER JOIN pays p ON(p.paysid=u.paysid) WHERE valide IN (5,8) AND p.tvaintracom=0 AND TVAtauxnormal=0 AND f.dateexpedition LIKE '$annee%'";
$req=$DB_site2->query($sql);
while($f=$DB_site2->fetch_array($req)) {
    $facture=new facture($DB_site2,$f);
    if(!isset($paysdata[$facture->utilisateur->paysid])) $paysdata[$facture->utilisateur->paysid]=array("CApart"=>0,"CApro"=>0,"CAintra"=>0,"nb"=>0);
    
    $cat=($facture->utilisateur->raisonsociale==""?"part":"pro");
    if($facture->porttva>0) $cat="tva";
    
    $paysdata[$facture->utilisateur->paysid]['CA'.$cat]+=$facture->totalht;
    $totalpays[$facture->utilisateur->paysid]+=$facture->totalht;
    $paysdata[$facture->utilisateur->paysid]['nb']++;
    $listefacture[$facture->utilisateur->paysid][]=$facture;
}

arsort($totalpays);
$total=array_sum($totalpays);
$paysrank=array_flip(array_keys($totalpays));

$nm1=annee_prec($annee-1,false,$encours);
$nm2=annee_prec($annee-2,false,$encours);
$sumnm1=$sumnm2=0;

$sum=array('CApart'=>0,'CApro'=>0,'CAtva'=>0,'nb'=>0);

print "
<thead>
<tr>
  <td class=\"ca_title\" colspan=\"12\">Clients hors union Européenne</td>
  <td class=\"ca_historique\" colspan=\"8\">Historique - temps réel / N-1 et N-2</td>
</tr>
<tr>
  <td colspan=\"2\">Pays</td>
  <td>Client part.</td>
  <td>Client pro</td>
  <td colspan=\"4\"></td>
  <td>TVA locale</td>
  <td>N°TVA</td>
  <td>Clients TVA 20%</td>
  <td>Total CA HT</td>
  <td>Position export</td>
  <td>Nbre cde</td>
  <td>cde moy.</td>
  <td>% export</td>
  <td>av/ret / N-1</td>
  <td>% N-1</td>
  <td>av/ret / N-2</td>
  <td>% N-2</td>
</tr>
</thead>";


if($paysdata!=array()) {
    $paysids=array_keys($paysdata);
    $req=$DB_site2->query("SELECT paysid,libelle,diminutif,TVAlocale,currency FROM pays WHERE paysid IN(".implode(",",$paysids).") ORDER BY libelle");
    while($pays=$DB_site2->fetch_array($req)) {

	$moy=0;
	if($paysdata[$pays['paysid']]['nb']>0) $moy=$totalpays[$pays['paysid']]/$paysdata[$pays['paysid']]['nb'];
	
	$diffnm1=$totalpays[$pays['paysid']]-$nm1[$pays['paysid']];
	$diffnm2=$totalpays[$pays['paysid']]-$nm2[$pays['paysid']];
	$pcdiffnm1=$pcdiffnm2="";
	if($nm1[$pays['paysid']]>0) $pcdiffnm1=100*$diffnm1/$nm1[$pays['paysid']];
	if($nm2[$pays['paysid']]>0) $pcdiffnm2=100*$diffnm2/$nm2[$pays['paysid']];
	
	$sum['CApart']+=$paysdata[$pays['paysid']]['CApart'];
	$sum['CApro']+=$paysdata[$pays['paysid']]['CApro'];
	$sum['CAtva']+=$paysdata[$pays['paysid']]['CAtva'];
	$sum['nb']+=$paysdata[$pays['paysid']]['nb'];
	$sumnm1+=$nm1[$pays['paysid']];
	$sumnm2+=$nm2[$pays['paysid']];
	
	print "<tr>
	  <td><img src=\"images/drapeaux/br/".$pays['diminutif'].".gif\"></td>
	  <td>".$pays['libelle']."</td>
	  <td class=\"ca_nb\">".number_format($paysdata[$pays['paysid']]['CApart'],2,',','')."&euro;</td>
	  <td class=\"ca_nb\">".number_format($paysdata[$pays['paysid']]['CApro'],2,',','')."&euro;</td>
	  <td class=\"ca_nb\" colspan=\"4\"></td>
	  <td class=\"ca_nb\">".$pays['TVAlocale']."%</td>
	  <td class=\"ca_nb\"></td>
	  <td class=\"ca_nb\">".number_format($paysdata[$pays['paysid']]['CAtva'],2,',','')."&euro;</td>
	  <td class=\"ca_nb\" onmouseover=\"showpopup('factures".$pays['paysid']."')\" onmouseout=\"hidepopup('factures".$pays['paysid']."')\">".number_format($totalpays[$pays['paysid']],2,',','')."&euro;<div id=\"factures".$pays['paysid']."\" class=\"popup\">".formatlist($pays['paysid'])."</div></td>
	  <td class=\"ca_nb\">".(isset($paysrank[$pays['paysid']])?($paysrank[$pays['paysid']]+1):"")."</td>
	  <td class=\"ca_nb\">".$paysdata[$pays['paysid']]['nb']."</td>
	  <td class=\"ca_nb\">".number_format($moy,2,',','')."</td>
	  <td class=\"ca_nb\">".number_format(100*$totalpays[$pays['paysid']]/$total,2,',','')."%</td>
	  ".tdvalue($diffnm1,"&euro;",true).
	  tdvalue($pcdiffnm1,"%",true).
	  tdvalue($diffnm2,"&euro;",true).
	  tdvalue($pcdiffnm2,"%",true)."
	</tr>";
    }
}

$moy=0;
$sumtotalpays=array_sum($totalpays);
if($sum['nb']>0) $moy=$sumtotalpays/$sum['nb'];
$diffnm1=$sumtotalpays-$sumnm1;
$diffnm2=$sumtotalpays-$sumnm2;
$pcdiffnm1=($sumnm1==0?"":100*$diffnm1/$sumnm1);
$pcdiffnm2=($sumnm2==0?"":100*$diffnm2/$sumnm2);
$total_ex=$sum['CAtva']+$sum['CApro']+$sum['CApart'];

print "<tr class=\"total\">
	<td colspan=\"2\">Total</td>
	<td class=\"ca_nb\">".number_format($sum['CApart'],2,',','')."&euro;</td>
	<td class=\"ca_nb\">".number_format($sum['CApro'],2,',','')."&euro;</td>
	<td colspan=\"6\"></td>
	<td class=\"ca_nb\">".number_format($sum['CAtva'],2,',','')."&euro;</td>
	<td class=\"ca_nb\">".number_format($total_ex,2,',','')."&euro;</td>
	<td></td>
	<td class=\"ca_nb\">".$sum['nb']."</td>
	<td class=\"ca_nb\">".number_format($moy,2,',','')."</td>
	<td></td>"
	.tdvalue($diffnm1,"&euro;",true).
	tdvalue($pcdiffnm1,"%",true).
	tdvalue($diffnm2,"&euro;",true).
	tdvalue($pcdiffnm2,"%",true)."
      </tr>";
    
print "<tr><td class=\"sep\"></td></tr>";

print "<tr class=\"total\">
	<td colspan=\"2\">Total export HT</td>
	<td colspan=\"9\"></td>
	<td class=\"ca_nb\">".number_format($total_ex+$total_ue,2,',','')."&euro;</td>
      </tr>";
      
print "<tr><td class=\"sep\"></td></tr>";     

$catotal=ca($annee);
print "<tr class=\"total\">
	 <td colspan=\"8\"></td>
	 <td colspan=\"3\">CA HT Global</td>
	 <td class=\"ca_nb\">".number_format($catotal,2,',','')."&euro;</td>
	 <td class=\"ca_nb\">".round(100*($total_ex+$total_ue)/$catotal,2)."%</td>
	</tr>";
	
print "</table>";


function annee_prec($annee,$intra=true,$encours=false) {
    global $DB_site2;
    
    $return=array();
    
    $sqldate="LIKE '$annee%'";
    if($encours) $sqldate="BETWEEN '$annee-01-01' AND '$annee-".date("m-d")."'";
    $sql="SELECT f.* FROM facture f INNER JOIN utilisateur u USING(userid) INNER JOIN pays p ON(p.paysid=u.paysid) WHERE valide IN (5,8) AND p.tvaintracom=".($intra?"1":"0 AND TVAtauxnormal=0")." AND f.datefacture ".$sqldate;

    $req=$DB_site2->query($sql);
    while($f=$DB_site2->fetch_array($req)) {
	$facture=new facture($DB_site2,$f);
	$return[$facture->utilisateur->paysid]+=$facture->totalht;
    }
    
    return $return;
}

function tdvalue($value,$sym="",$sign=null,$climit=0){
	if($value=="") return "<td></td>";
	return "<td class=\"ca_nb\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,2,',','').$sym."</td>";
}

function ca($annee){
	global $DB_site2;

	$chiffremois=0;
	$etat="5,8";
	$sql="select * from facture WHERE valide in (".$etat.") and dateexpedition LIKE '".$annee."-%'";
	$cams=$DB_site2->query($sql);

	while ($cam=$DB_site2->fetch_array($cams)) {
	      $f=new facture($DB_site2,$cam);
	      $chiffremois += $f->totalht;
	}
	return $chiffremois;
}

function formatlist($paysid){
	global $listefacture;

	$html="<table>";

	$list=$listefacture[$paysid];

	if(is_null($list)) return "";
	
	foreach($list as $f)
	    $html.="<tr>
	      <td><a href=\"facture.php?action=editer&facturid=".$f->facturid."\" target=\"_blank\">".$f->facturid."</a></td>
	      <td>".$f->utilisateur->nom."</td>
	      <td>".date("d/m/Y",$f->dateexpedition)."</td>
	      <td align=\"right\">".number_format($f->totalht,2,',','')." &euro;</td>
	    </tr>";

	$html.="</table>";
	return $html;
	
}

?>