<?php

$req=$DB_site2->query_first("SELECT poid,emballage_poids1,emballage_largeur1,emballage_hauteur1,emballage_profondeur1,emballage_poids2,emballage_largeur2,emballage_hauteur2,emballage_profondeur2,emballage_poids3,emballage_largeur3,emballage_hauteur3,emballage_profondeur3 FROM article WHERE artid='$artid'");

foreach(range(1,3) as $num_emb) {
  ${'emballage_hauteur'.$num_emb} = $req['emballage_hauteur'.$num_emb] ;
  ${'emballage_largeur'.$num_emb} = $req['emballage_largeur'.$num_emb] ;
  ${'emballage_profondeur'.$num_emb} = $req['emballage_profondeur'.$num_emb] ;
  ${'emballage_poids'.$num_emb} = $req['emballage_poids'.$num_emb] ;
  ${'emballage_volume'.$num_emb}=round(($req['emballage_hauteur'.$num_emb]*$req['emballage_largeur'.$num_emb]*$req['emballage_profondeur'.$num_emb])/pow(10,9),2);
}


$contenu_onglet .= "
		<h3>Dimensions</h3>
		<script language=\"javascript\">"
     . "function calculemballageTotal(){"
          . "var poidsTotal = 0;"
          . "var volumeTotal = 0;"
          . "for (var num_emb=1; num_emb<=3; num_emb++){"
               . "poidsTotal += parseFloat(document.getElementsByName('emballage_poids' + num_emb)[0].value);"
          . "}"
          . "for (var num_emb=1; num_emb<=3; num_emb++){"
               . "volumeTotal += parseFloat(document.getElementById('volume' + num_emb).innerHTML);"
          . "}"
          . "document.getElementById('poidsTotal').innerHTML=poidsTotal;"
          . "document.getElementById('volumeTotal').innerHTML=volumeTotal;"
     . "}"
     . "function calculemballage(num_emb){"
          . "var volume = parseFloat(document.getElementsByName('emballage_hauteur' + num_emb)[0].value)*parseFloat(document.getElementsByName('emballage_largeur' + num_emb)[0].value)*parseFloat(document.getElementsByName('emballage_profondeur' + num_emb)[0].value);"
          . "volume = volume/Math.pow(10,9);"
          . "document.getElementById('volume' + num_emb).innerHTML = volume;"
          . "calculemballageTotal();"
     . "}"
     . "function changePoids(value){
          var value_kg = value * 1000;
          document.formu.poid.value = value_kg;
          document.getElementById('poid_affiche').innerHTML = value_kg+' g';
     }"
. "</script>
		<font class=\"cost\">$erreur</font>
		<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"mq2\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">";

	$poidsTotal = 0;
	$volumeTotal = 0;
	foreach(range(1,3) as $num_emb) {
	     $contenu_onglet .= ""
	     . "<fieldset>"
	          . "<legend>Dimensions brutes colis $num_emb</legend>"
	          . "<table width=\"90%\" align=\"center\">"
	               . "<tr>"
	                    . "<td align=\"left\">Hauteur (mm) : </td>"
	                    . "<td align=\"left\">&nbsp;<input type=\"text\" name=\"emballage_hauteur$num_emb\" value=\"".${'emballage_hauteur'.$num_emb}."\" onchange=\"setmodif();\" onkeyup=\"calculemballage($num_emb);\"></td>"
	               . "</tr>"
	               . "<tr>"
	                    . "<td align=\"left\">Largeur (mm) : </td>"
	                    . "<td align=\"left\">&nbsp;<input type=\"text\" name=\"emballage_largeur$num_emb\" value=\"".${'emballage_largeur'.$num_emb}."\" onchange=\"setmodif();\" onkeyup=\"calculemballage($num_emb);\"></td>"
	               . "</tr>"
	               . "<tr>"
	                    . "<td align=\"left\">Profondeur (mm) : </td>"
	                    . "<td align=\"left\">&nbsp;<input type=\"text\" name=\"emballage_profondeur$num_emb\" value=\"".${'emballage_profondeur'.$num_emb}."\" onchange=\"setmodif();\" onkeyup=\"calculemballage($num_emb);\"></td>"
	               . "</tr>"
	               . "<tr>"
	                    . "<td align=\"left\">Poids (kg) : </td>"
	                    . "<td align=\"left\">&nbsp;<input type=\"text\" name=\"emballage_poids$num_emb\" value=\"".${'emballage_poids'.$num_emb}."\" onchange=\"setmodif();\" onkeyup=\"calculemballage($num_emb);\"></td>"
	               . "</tr>"
	               . "<tr>"
	                    . "<td>Volume (m<sup>3</sup>)</td>"
	                    . "<td id=\"volume$num_emb\">".${'emballage_volume'.$num_emb}."</td>"
	               . "</tr>"
	               . "<tr>"
	                    . "<td><a href=\"javascript:calculemballage($num_emb)\">Calculer</a></td>"
	               . "</tr>"
	          . "</table>"
	     . "</fieldset>" ;
	                    
	     $poidsTotal  += ${'emballage_poids'.$num_emb};
	     $volumeTotal += ${'emballage_volume'.$num_emb};
	}	
	$contenu_onglet .= ""
. "<fieldset>"
. "<legend>Addition des 3 colis</legend>"
     . "<table width=\"90%\" align=\"center\">"
          . "<tr>"
          . "<td>Poids total brut (kg) : </td>"
               . "<td id=\"poidsTotal\">$poidsTotal</td>"
          . "</tr>"
          . "<tr>"
          . "<td>Volume total (m<sup>3</sup>)</td>"
               . "<td id=\"volumeTotal\">$volumeTotal</td>"
          . "</tr>"
     . "</table>"
. "</fieldset>";
	$article=$DB_site2->query_first("select a.*, spec.* from article AS a INNER JOIN ".$table_type[0]." AS spec USING(artid)  where artid = '$artid'");
    $checkedfab0=$checkedfab1=$checkedfab2=$checkedfab3="";
    ${"checkedfab".$article[paysfabrication]}="selected";

    $paysidfabopt="<option value=\"0\"></option>";
    $reqpays=$DB_site2->query("SELECT libelle, paysid FROM pays ORDER BY libelle");
    while($p=$DB_site2->fetch_array($reqpays)){
          $paysidfabopt.="<option value=\"$p[paysid]\"".($p['paysid']==$article['paysid']?" selected":"").">$p[libelle]</option>";
    }

    $reqcb=$DB_site2->query("SELECT * FROM code_barre left join `caracteristiquevaleur` using(caractvalid) WHERE artid=".$artid);
    while($cb=$DB_site2->fetch_array($reqcb)){
          $codebarre.=$cb['codebarre']." ".($cb['libelleFR']!=null?$cb['libelleFR']:"")."<br>";
    }
    
	$contenu_onglet.="
	<fieldset>
	<h3>Origine de fabrication - codes</h3>
	<table width=\"90%\" align=\"center\">
    <tr>
    <td align=\"left\">Origine fabrication : </td>
    <td align=\"left\"><select name=\"paysfabrication\" onchange=\"setmodif();\"><option value=\"0\" $checkedfab0>France</option><option value=\"1\" $checkedfab1>Europe</option><option value=\"2\" $checkedfab2>Asie</option><option value=\"3\" $checkedfab3>US</option></select></td>
    </tr>
    <tr>
    <td>Pays fabrication (pour export) :</td>
    <td><select name=\"paysidfab\" onchange=\"setmodif();\">$paysidfabopt</select></td>
    </tr>
    <tr>
    <td>Code douanier :</td>
    <td><input type=\"text\" name=\"douanier\" value=\"$article[douanier]\" onchange=\"setmodif();\"></td>
    </tr>
    <tr>
    <td>Code barre fabricant :</td>
    <td><input type=\"text\" name=\"codebarre\" value=\"$article[codebarre]\" onchange=\"setmodif();\"></td>
    </tr>
    <tr>
    <td><br>Code(s) barres Acilyon :</td>
    <td><br>$codebarre</td>
    </tr>
    </table></fieldset></form>" ;

?>