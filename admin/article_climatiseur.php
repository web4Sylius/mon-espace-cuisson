<?php

$article=$DB_site2->query_first("select a.*, spec.* from article AS a INNER JOIN ".$table_type[0]." AS spec USING(artid)  where artid = '$artid'");

$contenu_onglet .= "<h3>Climatiseur</h3>
		<font class=\"cost\">$erreur</font>
		<form name=\"formu\" method=\"post\" enctype=\"multipart/form-data\" style=\"display:inline\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"del\" value=\"\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">

		<fieldset>
		<legend>Climatiseur</legend>
		<table align=\"center\" width=\"100%\">
		<tr><td>Type de climatiseur :</td><td>".selecttranslate("type_climatiseur","/article/type_climatiseur",$article['type_climatiseur'])."</td></tr>
		<tr><td>Filtre à poussière :</td><td>".selecttranslate("filtre_poussiere","/article/filtre_poussiere",$article['filtre_poussiere'])."</td></tr>
		<tr><td>Distance max entre unités (m) :</td><td><input type=\"text\" name=\"distance_unites\" value=\"".$article['distance_unites']."\" onchange=\"setmodif()\"></td></tr>
		<tr><td>Longueur totale max des gaines (m) :</td><td><input type=\"text\" name=\"longueur_gaine\" value=\"".$article['longueur_gaine']."\" onchange=\"setmodif()\"></td></tr>
		</table>
		</fieldset>
		<fieldset>
		<legend>Groupe de condensation / unité extérieure</legend>
		<table align=\"center\" width=\"100%\">
		<tr><td>Puissance frigorifique (W)</td><td><input type=\"text\" name=\"puissance_frigo\" value=\"".$article['puissance_frigo']."\" onchange=\"setmodif()\"></td></tr>
		<tr><td>Puissance absorbée</td><td><input type=\"text\" name=\"puissance\" value=\"".$article['puissance']."\"></td></tr>
		<tr><td>Niveau sonore à 1m (dB)</td><td><input type=\"text\" name=\"niveau_sonore_1m\" value=\"".$article['niveau_sonore_1m']."\" onchange=\"setmodif()\"></td></tr>
		<tr><td>Niveau sonore à 3m (dB)</td><td><input type=\"text\" name=\"niveau_sonore_3m\" value=\"".$article['niveau_sonore_3m']."\" onchange=\"setmodif()\"></td></tr>
		<tr><td>Niveau sonore à 5m (dB)</td><td><input type=\"text\" name=\"niveau_sonore_5m\" value=\"".$article['niveau_sonore_5m']."\" onchange=\"setmodif()\"></td></tr>
		<tr><td>Kit bas niveau sonore</td><td>".selecttranslate("kit_sonore","/article/kit_sonore",$article['kit_sonore'])."</td></tr>
		<tr><td>Gaz réfrigérant</td><td>".selecttranslate("gaz","/article/gaz",$article['gaz'])."</td></tr>
		<tr><td>Débit d'air extérieur (m<sup>3</sup>/h)</td><td><input type=\"text\" name=\"debit_air_ext\" value=\"".$article['debit_air_ext']."\" onchange=\"setmodif()\"></td></tr>
		<tr><td>Coloris groupe</td><td>".selecttranslate("coloris_groupe","/article/coloris",$article['coloris_groupe'])."</td></tr>
		</table>
		</fieldset>
		<fieldset>
		<legend>Evaporateur</legend>
		<table align=\"center\" width=\"100%\">
		<tr><td>Nombre ventilateurs</td><td><input type=\"text\" name=\"nb_ventilateur\" value=\"".$article['nb_ventilateur']."\" onchange=\"setmodif()\"></td></tr>
		<tr><td>Débit d'air intérieur (m<sup>3</sup>/h)</td><td><input type=\"text\" name=\"debit_air_int\" value=\"".$article['debit_air_int']."\" onchange=\"setmodif()\"></td></tr>
		<tr><td>Puissance chauffage (W)</td><td><input type=\"text\" name=\"puissance_chauffage\" value=\"".$article['puissance_chauffage']."\" onchange=\"setmodif()\"></td></tr>
		<tr><td>Kit humidificateur / hygrométrie</td><td>".selecttranslate("kit_humidificateur","/article/kit_humidificateur",$article['kit_humidificateur'])."</td></tr>
		<tr><td>Raccordement évacuation de l'eau</td><td>".selecttranslate("evacuation_eau","/article/evacuation_eau",$article['evacuation_eau'])."</td></tr>
		<tr><td>Coloris évaporateur</td><td>".selecttranslate("coloris_evaporateur","/article/coloris",$article['coloris_evaporateur'])."</td></tr>
		</table>
		</fieldset>
		<fieldset>
		<legend>Régulation</legend>
		<table align=\"center\" width=\"100%\">
		<tr><td>Régulation</td><td>".selecttranslate("regulation","/article/regulation",$article['regulation'])."</td></tr>
		<tr><td>Position régulateur</td><td>".selecttranslate("position_regulateur","/article/position_regulateur",$article['position_regulateur'])."</td></tr>
		<tr><td>Affichage</td><td>".selecttranslate("affichage","/article/affichage",$article['affichage'])."</td></tr>
		</table>
		</fieldset>
		</form>
		" ;

?>