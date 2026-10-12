<?php

$article=$DB_site2->query_first("select * from article where artid = '$artid'") ;

$fraisport=$DB_site2->query_first("SELECT gratuit FROM fraisport WHERE paysid=57 AND ".(1000*$article['emballage_poids'])." BETWEEN poiddebut AND poidfin");
if($fraisport['gratuit']<=$article['prix']) $list.="<br><img src=\"../images/templates/FR/livraison.png\" align=\"absmiddle\"> Livraison offerte";

$caract_valeurs=$DB_site2->query("SELECT ac.caractvalid, libelleFR, ac.differenceprix, cv.image, cv.caractid
                                 FROM caracteristiquevaleur cv, article_caractval ac 
                                 WHERE cv.caractvalid = ac.caractvalid
                                 AND caractid IN (3,20)
                                 AND artid = '$artid'
                                 ORDER BY cv.position ASC ");
while ($caract_valeur=$DB_site2->fetch_array($caract_valeurs)){
    if ($caract_valeur[differenceprix] != "0"){
	$icon="../images/templates/FR/icon_".$caract_valeur[caractvalid].".png";
	if(file_exists($icon)) $list.="<br><img src=\"$icon\" align=\"absmiddle\"> ".$caract_valeur['libelleFR'];
    }
}

$chkicon=array();
$req=$DB_site2->query("SELECT * FROM article_icon WHERE active=1 AND artid=".$artid);
while($articon=$DB_site2->fetch_array($req)) $chkicon[]=$articon['iconid'];

$req=$DB_site2->query("SELECT * FROM icons WHERE 1");
while($icon=$DB_site2->fetch_array($req)){
    $path="../images/icons/FR/".$icon['id'].".png";
    if(file_exists($path)){
      $ai=$DB_site2->query_first("SELECT * FROM article_icon WHERE artid=".$artid." AND iconid=".$icon['id']);
      $list.="<tr><td><input type=\"checkbox\" name=\"icons[".$icon['id']."]\" value=\"1\"".($ai['active']==1?" checked":"")."></td><td><img src=\"".$path."\" align=\"absmiddle\"></td><td>".$icon['libelle']."</td><td><input type=\"text\" id=\"du_".$icon['id']."\" name=\"du[".$icon['id']."]\" value=\"".($ai['du']>0?date("d/m/Y",$ai['du']):"")."\" onclick=\"calendrier_show(this.id,this.value)\" size=\"8\" readonly></td><td><input type=\"text\" id=\"au_".$icon['id']."\" name=\"au[".$icon['id']."]\" value=\"".($ai['au']>0?date("d/m/Y",$ai['au']):"")."\" onclick=\"calendrier_show(this.id,this.value)\" size=\"8\" readonly></td></tr>";
    }
}

$contenu_onglet .= "<h3>Icones</h3>
<font class=\"cost\">$erreur</font>
<form name=\"formu\" method=\"post\" enctype=\"multipart/form-data\" style=\"display:inline\">
<input type=\"hidden\" name=\"old\" value=\"\">
<input type=\"hidden\" name=\"action2\" value=\"\">
<input type=\"hidden\" name=\"modif\" value=\"0\">
<input type=\"hidden\" name=\"del\" value=\"\">
<input type=\"hidden\" name=\"catid\" value=\"$catid\">
<input type=\"hidden\" name=\"quitter\" value=\"\">
<input type=\"hidden\" name=\"artid\" value=\"$artid\">
<div align=\"left\">
<table>
<tr><td></td><td></td><td></td><td>Affichage du</td><td>au</td></tr>
$list
</table>
</div>
</form>" ;

?>