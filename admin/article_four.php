<?php
// var_dump("select a.*, spec.* from article AS a INNER JOIN ".$table_type[0]." AS spec USING(artid)  where artid = '$artid'");

$count=$DB_site2->query_first("SELECT COUNT(*) FROM  ".$table_type[0]." where artid = '$artid'");
if($count[0]==0){
	$DB_site2->query("INSERT INTO ".$table_type[0]." SET artid = '$artid'");
}

$article=$DB_site2->query_first("select a.*, spec.* from article AS a INNER JOIN ".$table_type[0]." AS spec USING(artid)  where artid = '$artid'");



$contenu_onglet .= "<font class=\"cost\">$erreur</font>
		<h3>Coloris / Matière / Finition</h3>
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
<td align=\"left\">coloris personnalisable / inox / couleur définie : </td>
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



?>
