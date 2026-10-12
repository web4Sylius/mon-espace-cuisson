<?php
require "admin_global.php";
include "./header.php";
include "./menu.php";
include "../class/categorie.class.php";
include $rootpath."configurations/".$host."/class/cloudflare.class.php";

$oCategorie = new categorie($DB_site2, 0, $template->langue);
$oCategorie->setparam("pro",($sitepro?2:1));
$oCategorie->setparam("site_code",$template->site_code());

// On supprime le cache du menu
if($_GET['cache'] == 'vider'){
	$langue = array("FR");
	foreach ($langue as $key => $value) {
		$path = "../configurations/$host/cache/".$value."/";
		$rep=opendir($path);
		while($file = readdir($rep)){
			if($file != '..' && $file !='.' && $file !='' && $file!='.htaccess'){
				unlink($path.$file);
			}
		}
	}
	$urls = "https://www.ma-cave-a-vin.fr/
https://pro.ma-cave-a-vin.fr/
https://www.my-wine-cabinet.com/
https://pro.my-wine-cabinet.com/
https://www.ma-cave-a-vin.fr/css/css.css
https://pro.ma-cave-a-vin.fr/css/css.css
https://www.my-wine-cabinet.com/css/css.css
https://pro.my-wine-cabinet.com/css/css.css";
		$urls = preg_split("/\\r\\n|\\r|\\n/", $urls);
		// echo nl2br($_POST['url']);
		$cloud = new cloudflare_api($DB_site2,$rootpath);
		//$cloud->zone_id=$zone_id;
		while (list($key, $val) = each($urls)) {
			if($val!=""){
				if(substr($val,0,28)=="https://www.ma-cave-a-vin.fr" || substr($val,0,28)=="https://pro.ma-cave-a-vin.fr"){
					$zoneid=$cloudflare_zone_id_mcav;
				} else {
					$zoneid=$cloudflare_zone_id_mwc;
				}
				$results = $cloud->purgeFiles($cloudflare_email, $cloudflare_api, $zoneid, array($val) );
			}
		}
	echo "Le cache du menu a bien été vidé";
}

//On insère les modifications du menu dans la bdd
if($_POST['action'] == 'modif'){
	$catids = $_POST['catids'];
	foreach($catids as $catid){
		$colonne = $_POST['colonne'.$catid];
		$ordre = $_POST['ordre'.$catid];
		$colonne_resp = $_POST['colonne_resp'.$catid];
		$ordre_resp = $_POST['ordre_resp'.$catid];
		$exist_menu = $DB_site2->query_first("SELECT id_bloc FROM menu_bloc WHERE id_categorie=".$catid);
		if(!is_null($exist_menu['id_bloc'])){
			$DB_site2->query("UPDATE menu_bloc SET ordre = ".intval($ordre).",colonne =".intval($colonne)." ,id_categorie =".intval($catid).",colonne_resp=".intval($colonne_resp).",ordre_resp=".intval($ordre_resp)." WHERE id_bloc=".$exist_menu['id_bloc']);
		}
		else{
			$DB_site2->query("INSERT INTO menu_bloc (id_menu,ordre,colonne,ordre_resp,colonne_resp,id_categorie) values (".$_GET['menu_princi'].",".intval($ordre).",".intval($colonne).",".intval($ordre_resp).",".intval($colonne_resp).",".intval($catid).")");
		}
	}
}

//Select avec les catégories principales
echo "<h1>Gestion du menu sur le site</h1>
<select name='' onchange='document.location=\"menu_gestion.php?menu_princi=\"+this.value'>
<option value='' selected>Choix</option>";

$cat_principales = $DB_site2->query("SELECT idmenu,libelleFR,position FROM menus");

while ($cat_principale=$DB_site2->fetch_array($cat_principales)){
	echo "<option value='".$cat_principale['idmenu']."' ".($_GET['menu_princi']==$cat_principale['idmenu'] ? 'selected' : '').">".$cat_principale['libelleFR']."</option>";
}

echo '</select>';


if(isset($_GET['menu_princi'])&& $_GET['menu_princi']!== ''){
	//On prévisualise le futur menu au dessus du tableau
	echo "<p class='p_menuprevi'>Prévisualisation du menu :</p>
	<div id='menu_previ'>";

	$i = 1;
	while($i<5){
		echo "<div class='colonne_previ'>";

		$previs= $DB_site2->query("SELECT * FROM menu_bloc mb INNER JOIN categorie c ON mb.id_categorie = c.catid WHERE id_menu = ".$_GET['menu_princi']." AND colonne = ".$i." ORDER BY ordre");

		while($previ=$DB_site2->fetch_array($previs)){
			//On affiche que les catégories/souscat/etc qui ont des articles actifs ET qui doivent être affichées
			if(count($oCategorie->getArticles($previ['catid'])) > 0 && $previ['affichage_menu'] == '1'){
				$style = "";
				if($previ['menu_appartenance'] == '0'){
					//Les sous catégories principales
					$style = "padding-left:10px;";
				}
				else{
					//Les sous-sous catégories et catégories niveau inférieur
					$style = "font-weight:bold;";
				}
				echo "<div style='".$style."'>".$previ['libelleFR']."</div>";
			}
		}
		echo "</div>";
		
		$i++;
	}

echo "</div><p class='p_menuprevi'>Prévisualisation du menu responsive (tablette) :</p>
	<div id='menu_previ_resp'>";
//On prévisualise le futur menu responsive (tablette) au dessus du tableau
	$i = 1;
	while($i<4){
		echo "<div class='colonne_previ'>";

		$previs= $DB_site2->query("SELECT * FROM menu_bloc mb INNER JOIN categorie c ON mb.id_categorie = c.catid WHERE id_menu = ".$_GET['menu_princi']." AND colonne_resp = ".$i." ORDER BY ordre_resp");

		while($previ=$DB_site2->fetch_array($previs)){
			//On affiche que les catégories/souscat/etc qui ont des articles actifs ET qui doivent être affichées
			if(count($oCategorie->getArticles($previ['catid'])) > 0 && $previ['affichage_menu'] == '1'){
				$style = "";
				if($previ['menu_appartenance'] == '0'){
					//Les sous catégories principales
					$style = "padding-left:10px;";
				}
				else{
					//Les sous-sous catégories et catégories niveau inférieur
					$style = "font-weight:bold;";
				}
				echo "<div style='".$style."'>".$previ['libelleFR']."</div>";
			}
		}
		echo "</div>";
		
		$i++;

	}

	///Le tableau de gestion
	echo "</div><form method='POST'><input type='hidden' name='action' value='modif'>";

	///Calcule le nombre total de catégories + sous catégories du bloc :
	$compt_cat = 0;
	$sous_cats = $DB_site2->query("SELECT catid, menu_appartenance, libelleFR, urlFR FROM categorie WHERE menu_appartenance = ".$_GET['menu_princi']);
	while($sous_cat=$DB_site2->fetch_array($sous_cats)){
		$sous_sous_cats = $DB_site2->query("SELECT catid, parentid, libelleFR, urlFR FROM categorie WHERE parentid = ".$sous_cat['catid']);
		while($sous_sous_cat=$DB_site2->fetch_array($sous_sous_cats)){
			$compt_cat++;
		}
		$compt_cat++;
	}
	
	echo"<table class='table_alternate_color'>
	<tr>
	<th class='entete tri_tableau'>Colonne</th>
	<th class='entete tri_tableau'>Ordre</th>
	<th class='entete tri_tableau'>Colonne resp</th>
	<th class='entete tri_tableau'>Ordre resp</th>
	<th class='entete tri_tableau'>Libellé catégorie</th>
	</tr>";

	//On recherche toutes les sous-catégories du menu pour les mettre dans le tableau
	$sous_cats = $DB_site2->query("SELECT catid, menu_appartenance, libelleFR, urlFR, colonne, ordre, colonne_resp, ordre_resp, affichage_menu FROM categorie c LEFT JOIN menu_bloc mb ON mb.id_categorie = c.catid WHERE menu_appartenance = ".$_GET['menu_princi']);
	while($sous_cat=$DB_site2->fetch_array($sous_cats)){

			echo "<tr>
			<td align='center'>
			<input type='hidden' name='catids[]' value='".$sous_cat['catid']."'>
			<select name='colonne".$sous_cat['catid']."'>
			<option value='0' selected></option>";

		//Colonne
			$i = 0;
			while($i < 4){
				$selected = "";
				$i++;
				if(!is_null($sous_cat['colonne']) && $sous_cat['colonne'] ==$i) {
					$selected = "selected";
				}

				echo "<option value='".$i."'".$selected.">".$i."</option>";
			}

			echo "</select>
			</td>
			<td align='center'>
			<select name='ordre".$sous_cat['catid']."'>
			<option value='0' selected></option>";

		//Ordre
			$i = 0;
			while($i < $compt_cat){
				$selected = "";
				$i++;
				if(!is_null($sous_cat['ordre']) && $sous_cat['ordre'] ==$i) {
					$selected = "selected";
				}
				echo "<option value='".$i."'".$selected.">".$i."</option>";
			}

			echo "
			<td align='center'>
			<input type='hidden' name='catids[]' value='".$sous_cat['catid']."'>
			<select name='colonne_resp".$sous_cat['catid']."'>
			<option value='0' selected></option>";

		//Colonne resp
			$i = 0;
			while($i < 3){
				$selected = "";
				$i++;
				if(!is_null($sous_cat['colonne_resp']) && $sous_cat['colonne_resp'] ==$i) {
					$selected = "selected";
				}

				echo "<option value='".$i."'".$selected.">".$i."</option>";
			}

			echo "</select>
			</td>
			<td align='center'>
			<select name='ordre_resp".$sous_cat['catid']."'>
			<option value='0' selected></option>";

		//Ordre resp
			$i = 0;
			while($i < $compt_cat){
				$selected = "";
				$i++;
				if(!is_null($sous_cat['ordre_resp']) && $sous_cat['ordre_resp'] ==$i) {
					$selected = "selected";
				}
				echo "<option value='".$i."'".$selected.">".$i."</option>";
				var_dump($selected);
			}

			//On filtre les catégories avec 0 article actif
			if(count($oCategorie->getArticles($sous_cat['catid'])) > 0 && $sous_cat['affichage_menu'] == '1'){
				$libelle = $sous_cat['libelleFR'];
			}
			else{
				$libelle = $sous_cat['libelleFR'] . " <i style='font-weight: normal;'>Catégorie non visible car 0 article actif</i>";
			}

			echo "</select>
			</td>
			<td><b> ".$libelle."</b></td>
			</tr>";

		//On recherche toutes les sous-sous-catégories du menu pour les mettre dans le tableau
		$sous_sous_cats = $DB_site2->query("SELECT catid, parentid, libelleFR, urlFR, colonne, ordre, colonne_resp, ordre_resp FROM categorie c LEFT JOIN menu_bloc mb ON mb.id_categorie = c.catid WHERE parentid = ".$sous_cat['catid']);
		while($sous_sous_cat=$DB_site2->fetch_array($sous_sous_cats)){

			echo "<tr>
			<td align='center'>
			<input type='hidden' name='catids[]' value='".$sous_sous_cat['catid']."'>
			<select name='colonne".$sous_sous_cat['catid']."'>
			<option value='0' selected></option>";

			//Colonne
			$i = 0;
			while($i < 4){
				$selected = "";
				$i++;
				if(!is_null($sous_sous_cat['colonne']) && $sous_sous_cat['colonne'] ==$i) {
					$selected = "selected";
				}
				echo "<option value='".$i."'".$selected.">".$i."</option>";
			}
			echo "</select>
			</td>
			<td align='center'>
			<select name='ordre".$sous_sous_cat['catid']."'>
			<option value='0' selected></option>";

			//Ordre
			$i = 0;
			while($i < $compt_cat){
				$selected = "";
				$i++;
				if(!is_null($sous_sous_cat['ordre']) && $sous_sous_cat['ordre'] ==$i) {
					$selected = "selected";
				}
				echo "<option value='".$i."'".$selected.">".$i."</option>";
			}

			echo "
			<td align='center'>
			<input type='hidden' name='catids[]' value='".$sous_sous_cat['catid']."'>
			<select name='colonne_resp".$sous_sous_cat['catid']."'>
			<option value='0' selected></option>";

			//Colonne resp
			$i = 0;
			while($i < 3){
				$selected = "";
				$i++;
				if(!is_null($sous_sous_cat['colonne_resp']) && $sous_sous_cat['colonne_resp'] ==$i) {
					$selected = "selected";
				}
				echo "<option value='".$i."'".$selected.">".$i."</option>";
			}
			echo "</select>
			</td>
			<td align='center'>
			<select name='ordre_resp".$sous_sous_cat['catid']."'>
			<option value='0' selected></option>";

			//Ordre resp
			$i = 0;
			while($i < $compt_cat){
				$selected = "";
				$i++;
				if(!is_null($sous_sous_cat['ordre_resp']) && $sous_sous_cat['ordre_resp'] ==$i) {
					$selected = "selected";
				}
				echo "<option value='".$i."'".$selected.">".$i."</option>";
			}

			
			if(count($oCategorie->getArticles($sous_sous_cat['catid'])) > 0 && $sous_cat['affichage_menu'] == '1'){
				$libelle = $sous_sous_cat['libelleFR'];
			}
			else{
				//On différencie les catégories/souscat/etc qui n'ont pas d'articles actifs
				$libelle = $sous_sous_cat['libelleFR'] . " <i style='font-weight: normal;'>Catégorie non visible car 0 article actif</i>";
			}

			echo "</select>
			</td>
			<td> ".$libelle."</td>
			</tr>";
		}
	}
	echo"</table>
	<input type='submit' value='Modifier'>
	</form>
	<a href='menu_gestion.php?cache=vider' class='style_btn'>Vider le cache</a>";

}


include "./footer.php";
?>