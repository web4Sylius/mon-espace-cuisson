<?php

$article=$DB_site2->query_first("SELECT * FROM article WHERE artid=".$artid);

$contenu_onglet .= "<h3>Montage du produit</h3>
		<font class=\"cost\">$erreur</font>
		<form name=\"formu\" method=\"post\" enctype=\"multipart/form-data\" style=\"display:inline\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"del\" value=\"\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">

		<p><input type=\"checkbox\" name=\"montage\" value=\"1\"".($article['montage']==1?" checked":"")."> Montage mécanique du produit</p>
		<p>Temps de montage : <input type=\"text\" id=\"m\" value=\"".minutes($article['montage_heures'])."\" size=\"2\" onkeyup=\"convert_hours()\">min = <input type=\"text\" name=\"montage_heures\" value=\"".$article['montage_heures']."\" style=\"border:none;width:40px;\" readonly>h</p>";

$montage=$montage_vente=$installateurs=array();
$req=$DB_site2->query("SELECT paysid,libelle,prix,paht,fournisseur FROM montage INNER JOIN pays USING(paysid) WHERE actif=1");
while($install=$DB_site2->fetch_array($req)) {
    $montage[$install['libelle']][$install['fournisseur']]=$install['paht'];
    $montage_vente[$install['libelle']]=unserialize($install['prix']);
    $installateurs[]=$install['fournisseur'];
}

array_unique($installateurs);
ksort($montage);

$contenu_onglet .= "<table id=\"prixmontage\">
  <thead>
  <tr>
  <td>Pays</td>";
  
foreach($sites as $site_code=>$site)
    $contenu_onglet.="<td>Prix vente TTC<br>".$site_code."</td>";
  
$installateurs=array_unique($installateurs);
foreach($installateurs as $installateur)
    $contenu_onglet .= "<td>Prix achat HT<br>".$installateur."</td>";
$contenu_onglet .= "</tr>
  </thead>";
    
foreach($montage as $pays=>$fournisseurs) {
    $contenu_onglet .= "<tr>
      <td>".$pays."</td>";
    foreach($sites as $site_code=>$site)
	$contenu_onglet.="<td class=\"right\">".round($montage_vente[$pays][$site_code]*$article['montage_heures'],2).$devises->symbole($site['devise'])."</td>";
    
    foreach($fournisseurs as $fournisseur=>$paht){
	$fournisseur_condition=$DB_site2->query_first("SELECT devise FROM fournisseur_condition WHERE societe='".mysqli_real_escape_string($DB_site2->link_id,$fournisseur)."'");
	$contenu_onglet .= "<td class=\"right\">".round($paht*$article['montage_heures'],2).$devises->symbole($fournisseur_condition['devise'])."</td>";
    }
  
    $contenu_onglet .= "</tr>";
}

$contenu_onglet .= "</table>";


$contenu_onglet .= "<h3>Notices</h3>";


foreach($langues as $langue) {
    $notice=$DB_site2->query_first("SELECT * FROM article_files WHERE artid=".$article['artid']." AND infos LIKE 'notice$langue'");
    $name="Choisir...";
    $fileid="";
    if($notice['name']!=""){
	$name="<a href=\"#\" onclick=\"window.open('articlefiles/$notice[id].$notice[ext]');\">".$notice['name']."</a>";
	$fileid=$notice['id'];
    }
    $contenu_onglet.="<p>
      Notice $langue : 
      <input type=\"hidden\" name=\"notice$langue\" value=\"$fileid\">
      <span id=\"choose$langue\">$name</span>
      <img src=\"images/editer.gif\" class=\"choosebtn\" onclick=\"fb.showselect(undefined,notice);langue_notice='$langue'\">
      <img src=\"images/supprimer.gif\" class=\"delbtn\" onclick=\"delnotice('$langue')\">
    </p>";
}

$contenu_onglet.="</form>";

$contenu_onglet.="<script language=\"javascript\">
function convert_hours(){
    if(isNaN(document.getElementById('m').value)) document.getElementById('m').value=0;
    
    var m=parseInt(document.getElementById('m').value);
    var h=Math.round(m/60);
    m-=(60*h);
    
    document.getElementsByName('montage_heures')[0].value=Math.round(1000*(h+(m/60)))/1000;
}


var langue_notice='FR';
function notice(files) {
    for(i=0;i<files.length;i++){
      document.getElementsByName('notice'+langue_notice)[0].value=files[i].id;
      document.getElementById('choose'+langue_notice).innerHTML=files[i].name;
    }
}

function delnotice(langue) {
    document.getElementsByName('notice'+langue)[0].value='';
    document.getElementById('choose'+langue).innerHTML='Choisir...';
}
</script>

<style>
#prixmontage { border-collapse:collapse; }
#prixmontage thead { text-align:center;font-weight:bold; }
#prixmontage td { border:solid 1px #CCC;padding:2px; }
#prixmontage .right { text-align:right; }
[id^='choose'] {  }
.choosebtn { margin-left:5px; cursor:pointer; }
.delbtn { margin-left:5px; cursor:pointer; }
</style>";
		
function minutes($hours)  {
    return 60*intval($hours)+round(60*($hours-intval($hours)));
}
		
?>