<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

/*$r=$DB_site2->query("SELECT * FROM montage");
while($m=$DB_site2->fetch_array($r))
    $DB_site2->query("UPDATE montage SET prix='".serialize(array("base"=>$m['prix'],'uk'=>0))."' WHERE id=".$m['id']);
*/  
  
$tvafrance=$DB_site2->query_first("SELECT TVATauxnormal FROM pays WHERE paysid=57");
$txtva=(1+$tvafrance[0]/100);

print "<style>
.pv { width:40px; }
.pa { width:40px; }
.center { text-align:center; }
[id^='ht'] { color:red; margin-left:5px; }
table { border-collapse:collapse; }
table td { border:solid 1px #CCC; }
thead { font-weight:bold; text-align:center; }
</style>";

print "<script language=\"javascript\">
var txtva=$txtva;
function calcht(site,paysid){
    var prix=parseFloat(document.getElementsByName('pv['+paysid+']['+site+']')[0].value);
    if(isNaN(prix)) prix=0;
    document.getElementById('ht'+site+paysid).innerHTML=Math.round(100*prix/txtva)/100+'HT';
}
</script>";

if(isset($_POST['pa'])) {
    foreach($_POST['pa'] as $installateur=>$listpays){
	foreach($listpays as $paysid=>$paht) {
	    $exist=$DB_site2->query_first("SELECT paht FROM montage WHERE fournisseur='$installateur' AND paysid=".intval($paysid));
	    if($exist=="")
	      $DB_site2->query("INSERT INTO montage SET paht=".floatval($paht).", fournisseur='$installateur', paysid=".intval($paysid));
	    else
	      $DB_site2->query("UPDATE montage SET paht=".floatval($paht)." WHERE fournisseur='$installateur' AND paysid=".intval($paysid));
	}
    }
}

if(isset($_POST['pv'])) {
    foreach($_POST['pv'] as $paysid=>$prix)
	$DB_site2->query("UPDATE montage SET actif=".intval($_POST['actif'][$paysid]).", prix='".serialize($prix)."' WHERE paysid=".intval($paysid));
}

echo "<h1>Tarifs montage</h1>";


$listepaht=$listeprix=$actif=array();
$req=$DB_site2->query("SELECT * FROM montage WHERE 1");
while($m=$DB_site2->fetch_array($req)) {
    $listepaht[$m['paysid']][$m['fournisseur']]=$m['paht'];
    $listeprix[$m['paysid']]=unserialize($m['prix']);
    $actif[$m['paysid']]=$m['actif'];
}

$req=$DB_site2->query("SELECT f.societe,fc.devise FROM fournisseur f LEFT JOIN fournisseur_condition fc USING(societe) WHERE type='sls' GROUP BY societe");
while($inst=$DB_site2->fetch_array($req)){
    $installateurs[]=$inst['societe'];
    $devise_fournisseur[$inst['societe']]=$inst['devise'];
}

asort($installateurs);
    
print "<form method=\"post\">
  <table>
  <thead>
  <tr>
    <td>Pays</td>
    <td>Montage possible</td>";
    
foreach($sites as $site_code=>$site)
    print "<td>Prix vente/h TTC $site_code</td>";
  
foreach($installateurs as $installateur)
   print "<td>Prix achat/h HT<br>".$installateur."</td>";

print "</tr>
      </thead>";
    
$req=$DB_site2->query("SELECT paysid,libelle,diminutif FROM pays ORDER BY libelle");
while($pays=$DB_site2->fetch_array($req)) {
    
    print "<tr>
	<td><img src=\"images/drapeaux/br/".$pays['diminutif'].".gif\"> ".$pays['libelle']."</td>
	<td class=\"center\"><input type=\"checkbox\" name=\"actif[$pays[paysid]]\" value=\"1\" ".($actif[$pays[paysid]]==1?"checked":"")."></td>";
    
    foreach($sites as $site_code=>$site)    
	print "<td><input type=\"text\" name=\"pv[$pays[paysid]][$site_code]\" value=\"".floatval($listeprix[$pays[paysid]][$site_code])."\" class=\"pv\" onkeyup=\"calcht('$site_code','$pays[paysid]')\"><span id=\"ht".$site_code.$pays['paysid']."\">".round($listeprix[$pays[paysid]][$site_code]/$txtva,2).$devises->symbole($site['devise'])." HT</span></td>";
	
    foreach($installateurs as $installateur)
	print "<td><input type=\"text\" name=\"pa[$installateur][$pays[paysid]]\" value=\"".floatval($listepaht[$pays[paysid]][$installateur])."\" class=\"pa\">".$devise_fournisseur[$installateur]."</td>";

    print "</tr>";
}

print "</table>
<input type=\"submit\" value=\"Enregistrer\">
</form>";

include "./footer.php";

?>