<?php

$article=$DB_site2->query_first("select a.*, spec.* from article AS a INNER JOIN ".$table_type[0]." AS spec USING(artid)  where artid = '$artid'");
var_dump("select a.*, spec.* from article AS a INNER JOIN ".$table_type[0]." AS spec USING(artid)  where artid = '$artid'");
$contenu_onglet .= "<h3>Cave à cigares</h3>
		<font class=\"cost\">$erreur</font>
		<form name=\"formu\" method=\"post\" enctype=\"multipart/form-data\" style=\"display:inline\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"del\" value=\"\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">

		<table align=\"center\" width=\"100%\">
		<tr><td>Système d'humidification :</td><td>".selecttranslate("systeme_humidification","/article/systeme_humidification",$article['systeme_humidification'])."</td></tr>
		<tr><td>Régulation électronique de l'humidification :</td><td>".selecttranslate("regulation_humidification","/article/regulation_humidification",$article['regulation_humidification'])."</td></tr>
		<tr><td>Hygromètre :</td><td>".selecttranslate("hygrometre","/article/hygrometre",$article['hygrometre'])."</td></tr>
		<tr><td>Etiquettes de repérage :</td><td>".selecttranslate("etiquette_reperage","/article/etiquette_reperage",$article['etiquette_reperage'])."</td></tr>
		</table>
		</form>";

?>