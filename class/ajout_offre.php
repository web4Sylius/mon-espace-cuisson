<?php
require "admin_global.php";

include_once("fonctions.php");

setcookie("hostname",$host,time()+3600,"/");
include_once("marketplace/article.php");

$notranslate=false;

include "../class/garantie.class.php";
include "../class/DAOgarantie.class.php";
include "../class/DAOarticle.class.php";
include "../class/pack.class.php";
include_once "../class/frais_port.php";

$active=array(0=>"Inactif temporairement",1=>"Actif",2=>"Supprimé");


if(isset($artid) && $artid!=""){
	$artcodetitre=$DB_site2->query_first("SELECT artcode FROM article  WHERE artid=".intval($artid).";");

	$navigationdebut="<a href=\"catalogue.php\">Catalogue</a> > ".$artcodetitre['artcode'];
}


// onglets généraux
$gen_tabs = array (
  // actions		// textes affichés
	"suivi"			=> "Suivi",
	"infos"			=> "Infos",
	"image"			=> "Photos",
	"image2"		=> "Photos 2"
	);


if (!isset ($action) or $action == "") $action = "infos" ;

if (isset ($action) and $action != ""){ // traitements des formulaires après (soummission)
	if (!isset ($artid) or $artid == ""){ // Première saisie
		if($old=="infos"){
			$opt_visible=0;
			$catid=0;
			$DB_site2->query("INSERT INTO article (catid, artcode) VALUES ($catid, '$artcode')");
			$artid=$DB_site2->insert_id();
			$_POST['artid']=$artid;

			$DB_site2->query("INSERT INTO suivi_article SET artid=$artid, date=".time().", commercial='".$user_info['username']."', suivi='Création article'");

			if(isset($_POST['addartid'])){
				$as=$DB_site2->query_first("SELECT prix FROM article_site WHERE artid=".$_POST['addartid']." AND site_code='base'");
				$DB_site2->query("INSERT INTO article_pack SET packid=".$artid.",artid=".$_POST['addartid'].", qte=1, prix=".$as['prix']);
			}
			
			$erreur = "Article créé";
		}
	}

	if (isset ($artid) and $artid != ""){ // Mise à jour
		switch ($_POST['old']){ // action d'où on part
		case "suivi":
		if($_POST['suivi']!=""){
			$DB_site2->query("INSERT INTO suivi_article SET artid=$artid, commercial='".$user_info['username']."', date=".time().", suivi='".nl2br($_POST['suivi'])."'");
		}
		break;

			case "infos": // update
			break ;
			
			case "pack":
			if(isset($_POST['addartid']) && $_POST['addartid']!=""){
				$addprix=floatval($_POST['addprix']);
				$addpaht=floatval($_POST['addpaht']);
				$prixoption=0;
				$caracts=explode(',', $_POST['caracts']);
				foreach ($caracts as $caract) {
					$prixCaract=$DB_site2->query_first("SELECT differenceprix,achat FROM article_caractval WHERE artid='".$_POST['addartid']."' AND caractvalid='".$caract."';");
					$addprix+=$prixCaract['differenceprix'];
					$addpaht+=$prixCaract['achat'];
					$prixoption+=$prixCaract['differenceprix'];
				}
				$offre=$DB_site2->query_first("SELECT offre FROM article_pack WHERE packid=".$_POST['artid'].";")['offre'];
				$DB_site2->query("INSERT INTO article_pack SET packid=".$_POST['artid'].",artid=".$_POST['addartid'].", qte=".intval($_POST['qte']).", prix=".$addprix.", paht=".$addpaht.", caractvalids='".$_POST['caracts']."', prixoption='$prixoption', offre='$offre';");
			}
			if(isset($_GET['delartid'])) $DB_site2->query("DELETE FROM article_pack WHERE id=".intval($_GET['delartid']));
			break;
			
			case "visible":
			if($_POST['delautovisible']!="0") $DB_site2->query("DELETE FROM article_visible WHERE id=".intval($_POST['delautovisible']));

			if($_POST['newdatevisible']!=""){
				$d=date_create_from_format("d/m/Y",$_POST['newdatevisible']);
				$DB_site2->query("INSERT INTO article_visible SET artid='".$_POST['artid']."', date=".intval($d->gettimestamp()).", active=".intval($_POST['autovisible']));
			}

			$packs=new pack($DB_site2);
			$apartid=$DB_site2->query_first("SELECT artid FROM article_pack WHERE packid=$artid AND prix !=''");
			$liste=$packs->offres_pack($apartid[0]);
			$nb=1;

			if($opt_visible == 1){
				foreach($liste as $packid){
					if($packid != $artid){
						$arch=$DB_site2->query_first("SELECT active FROM article_site WHERE artid=$packid");
						if($arch[0] != 2){
							$DB_site2->query("UPDATE article SET active = '0', commandable = '$opt_commandable', redirect='$redirect' WHERE artid = '$packid'");
							$DB_site2->query("UPDATE article_site SET active=0 WHERE artid=".$packid." AND site_code='base'");
						}
					}
				}
			}
			$DB_site2->query("UPDATE article SET active = '$opt_visible', commandable = '$opt_commandable', redirect='$redirect' WHERE artid = '$artid'");
			$DB_site2->query("UPDATE article_site SET active=".intval($opt_visible)." WHERE artid=".$artid." AND site_code='base'");

			// MaJ Article_pack
			$DB_site2->query("UPDATE article_pack SET publie ='$opt_publie' WHERE packid=".$artid." ");

			modifhistory($_POST);
			break;

			case "prix":
			if(isset($_POST['offrePack']) && $_POST['offrePack']!=""){
				$offre=$_POST['offrePack'].'-'.$_POST['offrePack_'.$_POST['offrePack']];
				$DB_site2->query("DELETE FROM article_pack WHERE packid=$artid AND prix=0;");
				if ($_POST['offrePack']=='pdtoffert') {
					$DB_site2->query("INSERT INTO article_pack SET packid=".$artid.", artid=".$_POST['kdoartid'].", qte=1, prix=0");
				}
				$DB_site2->query("UPDATE article_pack SET offre='".$offre."' WHERE packid=$artid;");
			}
			if (isset($_POST['prix'])) {
				$DB_site2->query("UPDATE article SET prix=".floatval($_POST['prix']).", tauxchoisi='".$_POST['tauxtva']."', date_appro='".strtots($_POST['date_appro'])."', delailivraison='".intval($_POST['delailivraison'])."', prixht='".round($_POST['prixht'],3)."', paht=".floatval($_POST['paht']).", ecopartht=".floatval($_POST['ecopartht']).", ecopart=".floatval($_POST['ecopart']).", prixtrspt=".floatval($_POST['prixtrspt']).", prixconseilleht=".$_POST['prixconseilleht']." WHERE artid=".$artid);
			}
			$article_site=$DB_site2->query_first("SELECT id FROM article_site WHERE artid=".$artid." AND site_code='base'");
			if($article_site['id']!="")
				$DB_site2->query("UPDATE article_site SET prix=".floatval($_POST['prix']).", prixconseilleht=".floatval($_POST['prixconseilleht']).", tauxchoisi='".$_POST['tauxtva']."' WHERE site_code='base' AND artid=".$artid);
			else
				$DB_site2->query("INSERT INTO article_site SET artid=".$artid.", site_code='base', prix=".floatval($_POST['prix']).", prixconseilleht=".floatval($_POST['prixconseilleht']).", tauxchoisi='".$_POST['tauxtva']."'");

			modifhistory($_POST);
			break;

			case "image":
			if(isset($_FILES)){
				foreach($langues as $langue){
					if($_FILES['img'.$langue]['name']!=""){
						$ext=pathinfo($_FILES['img'.$langue]['name'],PATHINFO_EXTENSION);
						if(strtolower($ext)=="jpg"){
							$DB_site2->query("DELETE FROM articlephoto WHERE artid=".$artid." AND langue='".$langue."'");
							$DB_site2->query("INSERT INTO articlephoto SET artid=".$artid.", image='jpg', langue='".$langue."', visible=1");
							$articlephotoid=$DB_site2->insert_id();
							$return=move_uploaded_file($_FILES['img'.$langue]['tmp_name'],"../configurations/www.mon-espace-cuisson.com/images/produits/".$artid."_".$articlephotoid.".jpg");
							$err[$langue]=($return?"L'image a bien été envoyée":"Une erreur s'est produite à l'envoi de l'image");
						}else
						$err[$langue]="Ce type de fichier n'est pas autorisé";
					}
				}
			}
			break;

			case "image2":
			$req = $DB_site2->query("SELECT artid FROM article_pack WHERE packid=".$artid);
			while($row = $DB_site2->fetch_array($req)){
				$img[] = $row['artid'];
			}

			if(isset($_POST['imgOffre']) && $_POST['imgOffre'] != ''){
				foreach($langues as $langue){
					$DB_site2->query("DELETE FROM articlephoto WHERE artid=".$artid." AND langue='".$langue."'");
					$DB_site2->query("INSERT INTO articlephoto SET artid=".$artid.", image='jpg', langue='".$langue."', visible=1");
					$articlephotoid=$DB_site2->insert_id();

					header("Location: article_img.php?artid=".$img[0]."&offre=".$img[1]."&format=".$_POST['imgOffre']."&langue=".$langue."&packid=".$artid."&photoid=".$articlephotoid);
					exit;
					//TODO : Enregistrement de l'image
				}
			}elseif(isset($_FILES)){
				foreach($langues as $langue){
					if($_FILES['img'.$langue]['name']!=""){
						$ext=pathinfo($_FILES['img'.$langue]['name'],PATHINFO_EXTENSION);
						if(strtolower($ext)=="jpg"){
							$DB_site2->query("DELETE FROM articlephoto WHERE artid=".$artid." AND langue='".$langue."'");
							$DB_site2->query("INSERT INTO articlephoto SET artid=".$artid.", image='jpg', langue='".$langue."', visible=1");
							$articlephotoid=$DB_site2->insert_id();
							$return=move_uploaded_file($_FILES['img'.$langue]['tmp_name'],"../configurations/www.mon-espace-cuisson.com/images/produits/".$artid."_".$articlephotoid.".jpg");
							$err[$langue]=($return?"L'image a bien été envoyée":"Une erreur s'est produite à l'envoi de l'image");
						}else
							$err[$langue]="Ce type de fichier n'est pas autorisé";
					}
				}
			}
			break;
		}

	}
}

$txfrance=$DB_site2->query_first("SELECT 0,TVAtauxnormal,TVAtauxreduit FROM pays WHERE paysid=57;");

$contenu_onglet = "" ;
switch ($action){ // formulaires
	case "suivi":
	$contenu_onglet.="<h3>Suivi - Historique</h3>
	<form name=\"formu\" action=\"ajout_offre.php\" method=\"post\">
	<input type=\"hidden\" name=\"old\" value=\"\">
	<input type=\"hidden\" name=\"quitter\" value=\"\">
	<input type=\"hidden\" name=\"mq2\" value=\"\">
	<input type=\"hidden\" name=\"modif\" value=\"0\">
	<input type=\"hidden\" name=\"action2\" value=\"\">
	<input type=\"hidden\" name=\"artid\" value=\"$artid\">
	<input type=\"hidden\" name=\"catid\" value=\"$catid\">";

	$tr=array();
	$req=$DB_site2->query("SELECT * FROM suivi_article WHERE artid=$artid ORDER BY date DESC");
	while($suivi=$DB_site2->fetch_array($req)){
		$tr[$suivi['date']].="<tr class=\"suivi\"><td class=\"tddate\">".date("d/m/Y H:i",$suivi['date'])."</td><td>".$suivi['commercial']."</td><td>".formatsuivi($suivi['suivi'])."</td></tr>";
	}

	$contenu_onglet.="<b>Ajouter un suivi :</b><br>
	<textarea name=\"suivi\" style=\"width:600px;height:100px;\"></textarea>
	<br><input type=\"button\" value=\"Ajouter le suivi\" onclick=\"clique('suivi','suivi')\">";

	$requete=$DB_site2->query("SELECT * FROM article_history WHERE artid=$artid ORDER BY date");

	$m=array();
	$last=array();
	$paht=array();
	$pv=array();

	while($modif=$DB_site2->fetch_array($requete)){
		$m[$modif['date']][$modif['key']]=$modif['value'];
		$mu[$modif['date']]=$modif['utilisateur'];
	}
	ksort($m);


	foreach($m as $date=>$fields){

		$showmodif=array();
		foreach($fields as $field=>$value){
			if($last[$field]!=$value) $showmodif[]="<span class=\"highlight\" onclick=\"highlight('".$field."')\">".$field." : ".$template->translate($last[$field])." =&gt; ".$template->translate($value)."</span>";
			$last[$field]=$value;
		}

		$tr[$date].="<tr class=\"modif\"><td class=\"tddate\" valign=\"top\">".date("d/m/Y H:i",$date)."</td><td valign=\"top\">".$mu[$date]."</td><td>".implode("<br>",$showmodif)."</td></tr>";
		//       $last=$fields;
	}

	krsort($tr);
	$contenu_onglet.="<p><input type=\"checkbox\" id=\"chksuivi\" onclick=\"filter()\" checked> <span style=\"background-color:#ddd;\">Suivis</span> &nbsp; <input type=\"checkbox\" id=\"chkmodif\" onclick=\"filter()\" checked> <span style=\"background-color:#ddddff;\">Modifications</span></p>";
	$contenu_onglet.="<table id=\"suiviarticle\">".implode("",$tr)."</table>";

	$contenu_onglet.="</form>";

		//TODO: create a TaskManager/TaskTemplate method
	$contenu_onglet.= "<h3>Suivi interne</h3>";
	$si_query = $DB_site2->query('
		SELECT `type`, `id`, `name`, `short_desc`, `excepted_date`
		FROM `suivi_interne`
		WHERE `link_type` LIKE "article"
		AND `link` LIKE "'.mysqli_real_escape_string($DB_site2->link_id,$artid).'"
		');
	$contenu_onglet.= '
	<table style="width:100%;">
	<thead style="font-size:12px;">
	<tr>
	<th>ID</th><th>Nom</th><th>'.utf8_decode('Résumé').'</th><th>Date de fin</th>
	</tr>
	</thead>
	<tbody>';
	while ($si = $DB_site2->fetch_array($si_query))
		$contenu_onglet.= '
	<tr class="suivi">
	<td>'.($si['type']=='suivi'?'SI':'SB').'#'.$si['id'].'</td>
	<td>'.$si['name'].'</td>
	<td>
	<a href="task.php?a=showTask&amp;task='
	.$si['id'].'&amp;'.$si['type'].'">'
	.$si['short_desc'].'</a>
	</td>
	<td>'.$si['excepted_date'].'</td>
	</tr>';
	$contenu_onglet.= '
	</tbody>
	</table>';
	break;
	
	case "infos":
	$article=$DB_site2->query_first("select * from article where artid = '$artid'") ;

	if($artid!=""){
		$artcodes = $DB_site2->query("SELECT artcode,active,artid,c.libelleFR FROM article INNER JOIN categorie c USING(catid) WHERE artcode='".mysqli_real_escape_string($DB_site2->link_id,$article['artcode'])."' AND artid<>".$artid);
		$txtartcodes="";
		if($DB_site2->num_rows($artcodes)>0){
			$txtartcodes="<div style=\"color:red;\">Référence déjà existante :";
			while($aartcode=$DB_site2->fetch_array($artcodes)){
				$txtartcodes.="<br><a href=\"ajout_offre.php?artid=".$aartcode['artid']."&prec=modif\" target=\"_blank\">".$aartcode['artcode']." ".$aartcode['libelleFR']." (".$active[$aartcode['active']].")</a> ";
			}
			$txtartcodes.="</div>";
		}
	}



	$contenu_onglet .= <<<HTML
	<font class="cost">$erreur</font>

	<h3>Référence</h3>
	<input type="text" value="$article[artcode]" readonly>

HTML;

	include("article_offre.php");

	include("article_pack_prix.php");

	$packs=new Pack($DB_site2);
	$article=$packs->getDetails($artid);

		//$article=$DB_site2->query_first("select * from article where artid = '$artid'") ;
	$delailivraison = $article[delailivraison];
	$opt_visible = $article[active];
	$msg_visible="";
	$dis_visible="";
	if($article['prix_pack_ttc']==null){
		$opt_visible=0;
		$msg_visible="Il faut configurer les prix pour activer l'article<br>";
		$dis_visible="disabled";
	}
	$checkedv0=$checkedv1=$checkedv2=$checkedv3="";
	${"checkedv".$opt_visible}="checked";

	$opt_publie = $article[publie];
	$checkedp1=$checkedp2=$checkedp3="";
	${"checkedp".$opt_publie}="checked";


	
	$contenu_onglet .= <<<HTML
	<h3>Visibilité produit</h3>
	<form name="formu2" action="ajout_offre.php" method="post">
	<input type="hidden" name="old" value="visible">
	<input type="hidden" name="artid" value="$artid">

	<table width="90%">
HTML;

	$autovtxt="";
	$r=$DB_site2->query("SELECT * FROM article_visible WHERE artid='$artid'");
	while($autov=$DB_site2->fetch_array($r)) $autovtxt.=date("d/m/Y",$autov['date'])." ".($autov['active']==1?"Visible":"Invisible")." <img src=\"images/icon-x.svg\" align=\"absmiddle\" width=\"20\" onclick=\"document.getElementsByName('delautovisible')[0].value=$autov[id];document.forms.formu2.submit();\"><br>";

	$date_reappro="";
	if($article['date_appro']!=0) $date_reappro=date("d/m/Y",$article['date_appro']);

	$contenu_onglet.= <<<HTML
	<tr>
	<td>Visible :</td>
	<td>
	<span style="color:red;">$msg_visible</span>
	<input type="radio" name="opt_visible" onchange="setmodif();" value="1" $checkedv1 $dis_visible> Oui 
	<input type="radio" name="opt_visible" value="0" onchange="setmodif();" $checkedv0 $dis_visible> Invisible (temporaire) 
	<input type="radio" name="opt_visible" value="2" onchange="setmodif();" $checkedv2 $dis_visible> Archivé
	</td>
	</tr>
	<tr>
	<td>Publier sur :</td>
	<td>
	<input type="radio" name="opt_publie" value="1" $checkedp1> Particulier
	<input type="radio" name="opt_publie" value="2" $checkedp2> Pro
	<input type="radio" name="opt_publie" value="3" $checkedp3> Les 2
	</td>
	</tr>
	<tr>
	<td rowspan="3">Automatique :</td>
	<td><input type="hidden" name="delautovisible" value="0">$autovtxt</td>
	</tr>
	<tr>
	<td id="newautoactive" style="display:none;">
	à partir du <input type="text" id="newdatevisible" name="newdatevisible" size="10" readonly onclick="calendrier_show(this.id,this.value);"> le produit sera 
	<input type="radio" name="autovisible" value="1"> Visible &nbsp; 
	<input type="radio" name="autovisible" value="0"> Invivible 
	<img src="images/icon-x.svg" onclick="document.getElementById('newdatevisible').value='';document.getElementById('newautoactive').style.display='none';document.getElementById('addautovisible').style.display='table-row';" title="Annuler" align="absmiddle" width="20">
	<img src="images/coche_verte.svg" onclick="document.forms.formu2.submit()" width="20">
	</td>
	</tr>
	<tr>
	<td id="addautovisible" onclick="document.getElementById('newautoactive').style.display='table-row';this.style.display='none';" style="cursor:pointer;"><img src="images/plus.gif" align="absmiddle"> Ajouter une date</td>
	</tr>
	<tr>
	<td><input type="submit" value="Enregistrer"></td>
	</tr>
	</table>
	</form>
HTML;

	
	break;

	case "image":
	$contenu_onglet.=<<<HTML
	<div align="center">
		<h3>Photos</h3>
		<h2>Envoyer une image</h2>
		<form method="post" enctype="multipart/form-data">
		<input type="hidden" name="old" value="image">
		<input type="hidden" name="artid" value="$artid">
HTML;

	foreach($langues as $langue){
		$preview="";
		$photo=$DB_site2->query_first("SELECT articlephotoid FROM articlephoto WHERE artid=".$artid." AND langue='".$langue."'");
		if($photo!=""){
			$preview="<img src=\"../configurations/www.mon-espace-cuisson.com/images/produits/".$artid."_".$photo['articlephotoid'].".jpg\" width=\"100\">";
		}
		
		$contenu_onglet.=<<<HTML
		<h4>$langue</h4>
		<p>$err[$langue]</p>
		$preview
		<input type="file" name="img$langue" value="Choisir une image">
		<hr>
HTML;
	}

		$contenu_onglet.=<<<HTML
		<br>
		<p align="center"><input type="submit" value="Sauvegarder"></p>
		</form>
	<div>
HTML;
	break;

	case "image2":
	$req = $DB_site2->query("SELECT artid FROM article_pack WHERE packid=".$artid);
	while($row = $DB_site2->fetch_array($req)){
		$img[] = $row['artid'];
	}

	$contenu_onglet.=<<<HTML
	<main class="main_tab">
		<h3>Photos</h3>
		<h2>Générer/Envoyer une image</h2>
HTML;

	//Définition des onglets
	foreach($langues as $langue){
		if($langue == 'FR') $check = 'checked';
		else $check = '';

		$contenu_onglet.=<<<HTML
		<input id="$langue" type="radio" name="photo_tabs" class="inp_photo" $check>
		<label for="$langue">$langue <img src="images/drapeaux/$langue.gif"></label>
HTML;
	}

	//Contenu des onglets
	foreach($langues as $langue){
		$preview="";
		$photo=$DB_site2->query_first("SELECT articlephotoid FROM articlephoto WHERE artid=".$artid." AND langue='".$langue."'");
		if($photo!="") $preview="<p><img src=\"../configurations/www.mon-espace-cuisson.com/images/produits/".$artid."_".$photo['articlephotoid'].".jpg\" width=\"100\"></p>";
		else $preview="<p>Aucune image</p>";

		$contenu_onglet.=<<<HTML
		<p style="display:none">$test</p>
		<section id="content_$langue" class="section_photo">
		<form method="post" enctype="multipart/form-data">
			<input type="hidden" name="old" value="image2">
			<input type="hidden" name="artid" value="$artid">
			<table align="center" class="photo_table">
				<thead>
					<tr>
						<th>"PORTRAIT"</th>
						<th>"PAYSAGE"</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><img src="article_img.php?artid=$img[0]&offre=$img[1]&format=portrait&langue=$langue"></td>
						<td><img src="article_img.php?artid=$img[0]&offre=$img[1]&format=paysage&langue=$langue"></td>
					</tr>
					<tr>
						<td><input type="radio" name="imgOffre" value="portrait"></td>
						<td><input type="radio" name="imgOffre" value="paysage"></td>
					</tr>
					<tr>
						<td colspan="2"><br><b>OU</b></td>
					</tr>
					<tr>
						<td colspan="2"><br>Envoyer une image :
						<input type="file" name="img$langue" value="Choisir une image"></td>
					</tr>
				</tbody>
			</table>
			<p><input type="submit" value="Sauvegarder" onclick="test();"></p>
		</form>
		<br>
		<h2>Image actuellement en place :</h2>
		$preview
		</section>
HTML;
	}
	 
	$contenu_onglet.=<<<HTML
	</main>
HTML;
	break;
}

$photo=$DB_site2->query_first("SELECT articlephotoid FROM articlephoto WHERE artid=".$artid." AND langue='FR'");
if($photo['articlephotoid']!="") $strIncludeImages="<img src=\"../configurations/www.mon-espace-cuisson.com/images/produits/".$artid."_".$photo['articlephotoid'].".jpg\" height=\"80\">";

include "./header.php";
include "./menu.php";

echo '<LINK href="cal.css" type="text/css" rel="stylesheet">
<script type="text/javascript" src="calendrier.js"></script>';


echo "<h1>Ajout / Modification de produit</h1>";


echo <<<HTML
<form method="post" action="rechercher.php">
<div style="padding:4px;border:solid thin black;">
Recherche : <input class="bouton1" type="text" name="recherche" size="10">
<input type="checkbox" name="active[]" value="1" checked>Actif &nbsp;
<input type="checkbox" name="active[]" value="0">Inactif &nbsp;
<input type="checkbox" name="active[]" value="2">Supprimé &nbsp;
<img src="./images/rechercher.gif" onclick="rechercher();" style="cursor:hand" align="absmiddle">
</div>
</form>
HTML;

echo <<<HTML
<div style="float:left;width:60%;">
$navigationdebut $navigationbit
<br>Ajout / Modification de produit / $artcodetitre[0] $erreurs
</div>
<div style="float:left;margin-left:12px;">$strIncludeImages</div>
<div class="clear"></div>


<div id="onglets">
HTML;

print_onglets($gen_tabs, 'c1', $action2);

echo <<<HTML
</div>
<div id="onglet_contenu">
$contenu_onglet

</div>

<script language="javascript">
$js

function test(){
	var langue = document.getElementById
}


var srcaction="selectprod";
function changeRemise(type,value) {
	switch (type) {
		case 'euro':
		document.getElementById('remisePack').value=Math.round(value*1000)/1000;
		document.getElementById('remisePackHT').value=Math.round(value/(1+$txfrance[1]/100)*1000)/1000;
		break;

		case 'pourcent':
		var remiseEuro=document.getElementById('prix').value-(document.getElementById('prix').value*((100-value)/100));
		document.getElementById('remisePack').value=Math.round(remiseEuro*1000)/1000;
		document.getElementById('remisePackHT').value=Math.round(remiseEuro/(1+$txfrance[1]/100)*1000)/1000;
		break;
	}
}

function showOffers(offer) {
	var lesOffrePackText = document.getElementsByName('offrePackText');
	for(i=0;i<lesOffrePackText.length;i++){
		lesOffrePackText[i].style.display = 'none';
	}
	if(offer!=undefined) {
		document.getElementById(offer).style.display = '';
		if(offer=='pdtoffert') {
			var lesPdtOffert = document.getElementsByName('listPdtOffert');
			for(i=0;i<lesPdtOffert.length;i++){
				lesPdtOffert[i].style.display = '';
			}
		} else {
			var lesPdtOffert = document.getElementsByName('listPdtOffert');
			for(i=0;i<lesPdtOffert.length;i++){
				lesPdtOffert[i].style.display = 'none';
			}
		}
	} else {
		var lesOffrePack = document.getElementsByName('offrePack');
		for(i=0;i<lesOffrePack.length;i++){
			if (lesOffrePack[i].checked) {
				lesOffrePackText[i].style.display = '';
			}
		}
	}
	document.getElementById('saveoffre').style.display='';
	document.getElementById('txtvaleur').style.display="none";
}

function srcarticle(txt,destfunc){
	if(destfunc!=undefined) srcaction=destfunc;
	document.getElementById('resultlist').style.display='none';
	if(document.getElementById('addprod')){
		document.getElementById('addprod').style.display='none';
		document.getElementById('submitbtn').style.display='none';
	}
	if(txt.length<3) return;
	okartcode=false;
	ajaxrequest('marketplace/src.php?txt='+txt+'&active=1&site_code=base',srccb);
}

function srccb(httprequest){
	var xml=httprequest.responseXML.documentElement;
	var html="";
	if(xml.getElementsByTagName('prod')[0]){
		html+="<table width='100%' style='border:solid thin #808080;'>";
		for(i=0;i<xml.getElementsByTagName('prod').length;i++){
			var description=xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
  // 	      description=decodeURIComponent(escape(description));
			var id=xml.getElementsByTagName('prod')[i].getAttribute('id');
			var ref=xml.getElementsByTagName('prod')[i].getAttribute('ref');
			var price=xml.getElementsByTagName('prod')[i].getAttribute('price');
			var buyprice=xml.getElementsByTagName('prod')[i].getAttribute('paht');
			var artid=xml.getElementsByTagName('prod')[i].getAttribute('artid');
			var active=xml.getElementsByTagName('prod')[i].getAttribute('active');
			color='black';
			if(active=='0' || active=='2') color='red';
			html+="<tr><td style='border-bottom:solid thin #cccccc;color:"+color+";'>"+id+"<br>"+ref+"</td><td style='border-bottom:solid thin #cccccc;'>"+description+"</span></td><td style='border-bottom:solid thin #cccccc;'><a href=javascript:"+srcaction+"('"+artid+"','"+id+"',"+price+","+buyprice+")>Sélectionner</a></td></tr>";
		}
		html+="</table>";
	}else
	html="Pas de résultat";

	document.getElementById('resultlist').innerHTML=html;
	document.getElementById('resultlist').style.display='block';
}

function selectprod(artid,artcode,prix,paht){
	document.getElementById('addopt').innerHTML='';

	document.getElementById('addartcode').innerHTML=artcode;
	document.getElementsByName('addartid')[0].value=artid;
	document.getElementsByName('addprix')[0].value=prix;
	document.getElementsByName('addpaht')[0].value=paht;

	document.getElementById('resultlist').style.display='none';
	document.getElementById('addprod').style.display='block';
	document.getElementById('submitbtn').style.display='block';
}

function selectkdo(artid,artcode,prix,paht){
	document.getElementsByName('kdoartid')[0].value=artid;
	document.getElementsByName('offrePack_pdtoffert')[0].value=artcode;
	document.getElementById('resultlist').style.display='none';
}

function optcb(httprequest){
	var xml=httprequest.responseXML.documentElement;
	var caractid=new Array();

	if(xml.getElementsByTagName('caract')[0]){

		for(i=0;i<xml.getElementsByTagName('caract').length;i++){
			var id=xml.getElementsByTagName('caract')[i].getAttribute('id');
			var name=xml.getElementsByTagName('caract')[i].getAttribute('name');
			var value=xml.getElementsByTagName('caract')[i].getAttribute('value');
			if(caractid[name]==undefined) caractid[name]=new Array();
			caractid[name][id]=value;
		}

		var html="";
		var cid=0;
		for(name in caractid){
			if(caractid.hasOwnProperty(name)){
				cid++;
				noauto=false;
				html+="<p><b>"+name+"</b></p>";

				for(id in caractid[name]){
					color="";
					if(artcaract[id]!=true){
						color='red';
						noauto=true;
					}
					if(caractid[name].hasOwnProperty(id)) html+="<input type='radio' name='caract_"+cid+"' value='"+id+"' onclick='listcaract()'> <span style='color:"+color+"'>"+caractid[name][id]+"</span><br>";
				}

				if(!noauto)
					html+="<input type='radio' name='caract_"+cid+"' value='' onclick='listcaract()' checked> Défini par l'article principal<br>";
				else
					html+="Cette option doit être définie manuellement<br>";
			}
		}

	}else
	html="";

	document.getElementById('addopt').innerHTML=html;
}

function listcaract(){
	list=new Array();
	for(i=0;i<document.forms.addform.getElementsByTagName('INPUT').length;i++){
		var elem=document.forms.addform.getElementsByTagName('INPUT')[i];
		if(elem.name.substr(0,7)=='caract_' && elem.checked && elem.value!='') list.push(elem.value);
	}
	document.getElementsByName('caracts')[0].value=list.join(',');
}

function calcmarge(pre, paht, margeht){
	if(pre==undefined) pre='';
	if(paht==undefined) paht='paht';

	var remisepack=document.getElementById('remisePackHT').value;
	if(document.getElementsByName(pre+'prixtrspt')) prixtrspt=parseFloat(document.getElementsByName('prixtrspt')[0].value);
	var totalachat=achatport+garantie+parseFloat(document.getElementsByName(pre+paht)[0].value)+parseFloat(document.getElementsByName('ecopartht')[0].value)+parseFloat(prixtrspt);
	console.debug(achatport);
	var totalvente=parseFloat(document.getElementsByName(pre+'prixht')[0].value)+prixport-remisepack;
	var marge=Math.round(100*(totalvente-totalachat))/100;
	var pcmarge=Math.round(10000*marge/totalvente)/100;
	document.getElementById('marge').innerHTML=marge+' &euro; ('+pcmarge+'%)';
	color='green';
	if(pcmarge<25) color='red';
	document.getElementById('marge').style.color=color;
	document.getElementById('magar').innerHTML=garantie;
	document.getElementById('matrspt').innerHTML=achatport;
}


function setmodif(){
	document.getElementsByName('modif')[0].value='1';
}

function nolf(event){
	if(event.keyCode==13){
		event.preventDefault();
		event.stopPropagation();
	} 
}

function filter(){
	var displaysuivi=document.getElementById('chksuivi').checked;
	var displaymodif=document.getElementById('chkmodif').checked;
	var tbl=document.getElementById('suiviarticle');
	var trs=tbl.getElementsByTagName('tr');
	for(i=0;i<trs.length;i++){
		switch(trs[i].className){
			case 'modif':
			trs[i].style.display=(displaymodif?'table-row':'none');
			break;
			case 'suivi':
			trs[i].style.display=(displaysuivi?'table-row':'none');
			break;
		}
	}
}

function highlight(field){
	field=field+' ';
	var tbl=document.getElementById('suiviarticle');
	var spans=tbl.getElementsByTagName('span');
	for(i=0;i<spans.length;i++){console.debug(spans[i].className);
		if(spans[i].className=='highlight' && spans[i].innerHTML.indexOf(field)==0)
			spans[i].style.backgroundColor='yellow';
		else
			spans[i].style.backgroundColor='';
	}
}

function clique(action){
	document.location="ajout_offre.php?artid=$artid&action="+action;
}

function affiche_div(obj, id){
	document.getElementById('FR').style.display       = 'none';
	document.getElementById('EN').style.display       = 'none';
	document.getElementById('SP').style.display       = 'none';

	document.getElementById('onglet_FR').className    = 'adm_photo';
	document.getElementById('onglet_EN').className    = 'adm_photo';
	document.getElementById('onglet_SP').className    = 'adm_photo';

	if(document.getElementById(id)){
		document.getElementById(id).style.display = 'block';
		obj.className = 'adm_photo adm_actif';
	}
}
</script>
HTML;

print <<<CSS
<style>
#tblprix,#tbltemp,#tbltempprices { background-color:#DEDEDE;border-collapse:collapse; }
#tblprix td,#tbltemp td,#tbltempprices td { border:solid thin white;padding:2px; }
#tblprix .lot { background-color:#DEDEFF; }
#tblprix .kdos { background-color:#C9FDFF; }
#tblprix .roinput { background:none; border:none; width:60px;}
#tblprix .spy {border-collapse:collapse;}
#tempprice { margin-bottom:10px;display:none;}
#prixdiv {float:left;margin-left:10px;}
#tblprix .small {font-size:11px;font-family: Verdana; color: rgb(54, 62, 66);}
#prixdiv .small {font-size:11px;font-family: Verdana; color: rgb(54, 62, 66);}
#suiviarticle { width:100%; margin-top:10px; }
#suiviarticle .suivi { background-color:#ddd;}
#suiviarticle .modif { background-color:#ccccff; }
.tddate { width:50px; }
.highlight { cursor:help; }
.highlight:hover { background-color:white; }
</style>
CSS;

include "./footer.php";




function modifhistory($data){
	global $DB_site2;
	global $user_info;
	$nohistory=array("old","infos","mq2","quitter","modif","action2","artid","catid");
	if(!array_key_exists("artid",$data)) return false;
	if($data['modif']!=1) return false;
	$artid=$data['artid'];
	if($artid=="") return false;

	foreach($data as $key=>$value){
		if(!in_array($key,$nohistory)) $DB_site2->query("INSERT INTO article_history SET artid=$artid, utilisateur='".$user_info['username']."', date=".time().", `key`='$key', value='$value'");
	}
}



// Génération du menu d'onglets pour toutes les catégories
function print_onglets($tabs, $color, $current)
{
	foreach($tabs as $key => $text) {
		if ($key == $current)
			echo '<div class="onglet_courant">' . $text . '</div>';
		else
			echo '<div class="onglet_'. $color . '"><a href="' . "javascript:clique('$key','$current');" . '">' . $text . '</a></div>';
	}
}

?>
