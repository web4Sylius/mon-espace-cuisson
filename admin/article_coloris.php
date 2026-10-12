<?php

$article=$DB_site2->query_first("select a.*, spec.* from article AS a INNER JOIN ".$table_type[0]." AS spec USING(artid)  where artid = '$artid'");

$contenu_onglet .= "<font class=\"cost\">$erreur</font>
		<h3>Coloris / Matière </h3>
		<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"mq2\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">";

$contenu_onglet .="<table width=\"90%\" align=\"center\">
<tr>
<td align=\"left\">version porte pleine/vitrée : </td>
<td align=\"left\">&nbsp;".selecttranslate("version_porte","/article/version_porte",$article['version_porte'])."</td>
</tr>
<tr>
<td align=\"left\">coloris / matière : </td>
<td align=\"left\">&nbsp;".selecttranslate("coloris","/article/coloris",$article['coloris'])."</td>
</tr>
<tr>
<td align=\"left\">coloris cadre porte : </td>
<td align=\"left\">&nbsp;".selecttranslate("coloris_porte","/article/coloris_porte",$article['coloris_porte'])."</td>
</tr>
<tr>
<td align=\"left\">matière : </td>
<td align=\"left\">&nbsp;".selecttranslate("matiere","/article/matiere",$article['matiere'])."</td>
</tr>
</table>";

/*$contenu_onglet.="
<table width=\"20%\" style=\"float:left;\">
<tr>
    <td colspan=\"2\"><h3>Type de pose</h3></td>
</tr>";

$articletypeposeid=array();
$req=$DB_site2->query("SELECT * FROM articlerecherche_typepose WHERE artid=".$artid);
while($article_pose=$DB_site2->fetch_array($req)){
    $articletypeposeid[]=$article_pose['typeposeid'];
}
$listetypepose=$DB_site2->query("SELECT * FROM recherche_typepose");
while($typepose=$DB_site2->fetch_array($listetypepose))
    $contenu_onglet.="<tr>
        <td><input type=\"checkbox\" name=\"typepose[]\" value=\"".$typepose[typeposeid]."\" ".(in_array($typepose[typeposeid],$articletypeposeid)?" checked":"")."></td>
        <td>".ucfirst($typepose['nomFR'])."</td>
    </tr>";

$contenu_onglet.="</table>";

$contenu_onglet.="<div style=\"width:40%; float:right; text-align:left;\">
    <h3>Hauteur de porte (pour cave sous plan)</h3>
    ".selecttranslate("hauteur_porte","/article/hauteur_porte",$article['hauteur_porte'])."
</div>";*/

?>
