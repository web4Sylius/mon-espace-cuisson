<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(SITE_PATH.'/class/DAOgarantie.class.php');
require_once(SITE_PATH.'/class/DAOarticle.class.php');
require_once(SITE_PATH.'/class/garantie.class.php');
require_once(SITE_PATH.'/class/article.class.php');
require_once(BACK_PATH.'/mailing_config.php');

$erreur_message = '';
if(!empty($_POST)){

	//Enregistrement de la bannière
	$template_banniere = '';
	if(!empty($_FILES['mailing_banniere']['name'])){

	    // Recuperation de l'extension du fichier
    	$extension  = pathinfo($_FILES['mailing_banniere']['name'], PATHINFO_EXTENSION);

	    // On verifie l'extension du fichier
	    if(strtolower($extension) == 'jpg'){

			// On recupere les dimensions du fichier
			$infosImg = getimagesize($_FILES['mailing_banniere']['tmp_name']);

			if($infosImg[0] == 570){

				if(!move_uploaded_file($_FILES['mailing_banniere']['tmp_name'], BACK_PATH.'/templates_mailing/images/banniere.jpg')){
					$message = 'Il y a eu une erreur lors de la copie de la bannière';
				} else {
					$template_banniere = file_get_contents(BACK_PATH.'/templates_mailing/banniere.html');
				}

			} else {
				$erreur_message = 'La bannière doit faire 570px de largeur';
			}

	    } else {
	    	$erreur_message = 'La bannière doit être au format JPG';
	    }
	}

	//Récupération du template principal
	$template_mailing = file_get_contents(BACK_PATH.'/templates_mailing/main.html');

	//TRAITEMENT PRODUITS
	$template_produits = '';
	$disposition       = 'g'; //gauche
	$nb = 0;
	if(isset($_POST['mailing_produits'])){
		foreach($_POST['mailing_produits'] as $article_id){ //Boucle sur les ID de produits sélectionnés par l'admin
			
			//Récupération des données de l'article
			$article = new produit($DB_site2,'FR','base',$template->tva_ref(),$article_id);

			//Gestion des tailles, pour améliorer l'affichage
			$aSize = getimagesize(SITE_URL.$article->visuel[0]['lien']);

			switch($_POST['template_pdt_type']){
				case 1:

					$template_produits .= file_get_contents(BACK_PATH.'/templates_mailing/produit-1'.$disposition.'.html');

					if($disposition == 'g'){
						$disposition = 'd';
					} else {
						$disposition = 'g';
					}

					$wMax = 140;
					$hMax = 210;

					if($aSize[0] < $wMax){
						$wMax = $aSize[0];
						$hMax = $aSize[1];
					} 

					if($aSize[0] <= $aSize[1]){ //Portrait
						$ratioW = $aSize[0] / $wMax;
						$newH = round($aSize[1] / $ratioW);
						if($newH <= $hMax){
							$finalW = $wMax;
							$finalH = $newH;
						} else {
							$ratioH = $aSize[1] / $hMax;
							$newW = round($aSize[0] / $ratioH);
							$finalW = $newW;
							$finalH = $hMax;
						}
					} else { //Paysage
						$ratioW = $aSize[0] / $wMax;
						$newH = round($aSize[1] / $ratioW);
						$finalW = $wMax;
						$finalH = $newH;
					}

					break;
				case 2:

					$hMax = 180;
					$wMax = 275;

					if($aSize[1] < $hMax){
						$wMax = $aSize[0];
						$hMax = $aSize[1];
					} 

					if($aSize[0] <= $aSize[1]){ //Portrait
						$ratioH = $aSize[1] / $hMax;
						$newW = round($aSize[0] / $ratioH);
						$finalW = $newW;
						$finalH = $hMax;
					} else { //Paysage
						$ratioW = $aSize[0] / $wMax;
						$newH = round($aSize[1] / $ratioW);
						$finalW = $wMax;
						$finalH = $newH;
					}

					//Ajout du trait de séparation si on s'apprête à afficher le modèle de droite
					if($nb%2 != 0) $template_produits .= file_get_contents(BACK_PATH.'/templates_mailing/separation-tpl2.html');

					//Template du produit
					$template_produits .= file_get_contents(BACK_PATH.'/templates_mailing/produit-2.html');

					//Gestion des débuts fin de tableau pour les retours à la ligne
					if($nb == 0) $template_produits = str_replace('%MAILING_DEBUT_TABLEAU%',"<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>",$template_produits);
					if($nb%2 == 0) $template_produits = str_replace('%MAILING_DEBUT_TABLEAU%',"<table width=\"100%\"><tr><td>&nbsp;</td></tr></table><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>",$template_produits);
					if(($nb%2 != 0 && $nb > 0) || $nb == count($_POST['produits'])-1) $template_produits = str_replace('%MAILING_FIN_TABLEAU%',"</td></tr></table>",$template_produits);
		 	
					$template_produits = str_replace('%MAILING_DEBUT_TABLEAU%','',$template_produits);
					$template_produits = str_replace('%MAILING_FIN_TABLEAU%','',$template_produits);

					break;
			}

			//Remplacement des variables produit
			$template_produits = str_replace('%MAILING_PDT_VISUW%',$finalW,$template_produits);
			$template_produits = str_replace('%MAILING_PDT_VISUH%',$finalH,$template_produits);
			$template_produits = str_replace('%MAILING_PDT_ALT%',$article->libelle,$template_produits);
			$template_produits = str_replace('%MAILING_PDT_URL%',SITE_URL.$article->visuel[0]['lien'],$template_produits);
			$template_produits = str_replace('%MAILING_PDT_LIBELLE%',$article->libelle,$template_produits);
			$template_produits = str_replace('%MAILING_PDT_CONTENANCE%',in_array($article->type_id,array(1,5,10)) && $article->capacite != '' ? '<b>'.$article->capacite.'</b> bouteille(s)' : '',$template_produits);
			$template_produits = str_replace('%MAILING_PDT_PX_BARRE%',SITE_URL.$article->remise > 0 ? "<strike>".$article->ppi_ttc_format." €</strike>" : '',$template_produits);
			$template_produits = str_replace('%MAILING_PDT_PX%','<b>'.$article->prix_ttc_format.' €</b>',$template_produits);
			$template_produits = str_replace('%MAILING_MQ_ALT%',$article->marque_libelle,$template_produits);
			$template_produits = str_replace('%MAILING_MQ_URL%',SITE_URL.$article->marque_visuel['lien'],$template_produits);

			$nb++;
		}
	}

	//TRAITEMENT CATEGORIES
	$template_categories = '';
	if(isset($_POST['mailing_categories'])){
		
		$template_categories = ""
		. "<tr>"
			. "<td valign=\"top\" width=\"15\"></td>"
			. "<td colspan=\"3\">";

		$disposition         = 'g'; //gauche
		foreach($_POST['mailing_categories'] as $la_categorie){
			
			$template_categories .= file_get_contents(BACK_PATH.'/templates_mailing/categorie-'.$disposition.'.html');

			if($disposition == 'g'){
				$disposition = 'd';
			} else {
				$disposition = 'g';
			}

			//Remplacement des variables produit
			$template_categories = str_replace('%NUM_CAT%',$la_categorie,$template_categories);
			$template_categories = str_replace('%MAILING_CAT_TITRE%',"<span style=\"font-size:24px; color:".$aCategories[$la_categorie]['codec']."\"><b>".substr($aCategories[$la_categorie]['titre'],0,1)."</b></span>".substr($aCategories[$la_categorie]['titre'],1),$template_categories);
			$template_categories = str_replace('%MAILING_CAT_DESC%',$aCategories[$la_categorie]['desc'],$template_categories);
		}

		$template_categories .= ""
			. "</td>"
			. "<td valign=\"top\" width=\"15\"></td>"
		. "</tr>";
	}

	//TRAITEMENT SELECTIONS
	$template_selections = '';
	if(isset($_POST['mailing_selections'])){

		$template_selections = ""
		. "<tr>"
			. "<td height=\"40\"></td>"
		. "</tr>"
		. "<tr>"
			. "<td valign=\"top\" colspan=\"5\"><img src=\"%SITE_URL%/admin/templates_mailing/images/selections.jpg\" alt=\"Nos sélections\" border=\"0\" width=\"600\" height=\"56\" style=\"display:block\"/></td>"
		. "</tr>"
		. "<tr>"
			. "<td valign=\"top\" width=\"15\"></td>"
			. "<td colspan=\"3\">";

		$disposition = 'g'; //gauche
		$nb = 0;
		foreach($_POST['mailing_selections'] as $la_selection){

			switch($_POST['template_sel_type']){
				case 1:

					//Ajout du trait de séparation si on s'apprête à afficher le modèle de droite
					if($nb%2 != 0) $template_selections .= "</td><td width=\"48\"></td><td>";

					//Template du produit
					$template_selections .= file_get_contents(BACK_PATH.'/templates_mailing/selection-1.html');

					//Gestion des débuts fin de tableau pour les retours à la ligne
					if($nb == 0) $template_selections = str_replace('%MAILING_DEBUT_TABLEAU%',"<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td style=\"text-align: center;\">",$template_selections);
					if($nb%2 == 0) $template_selections = str_replace('%MAILING_DEBUT_TABLEAU%',"<table width=\"100%\"><tr><td>&nbsp;</td></tr></table><table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td style=\"text-align: center;\">",$template_selections);
					if(($nb%2 != 0 && $nb > 0) || $nb == count($_POST['produits'])-1) $template_selections = str_replace('%MAILING_FIN_TABLEAU%',"</td></tr></table>",$template_selections);
		 	
					$template_selections = str_replace('%MAILING_DEBUT_TABLEAU%','',$template_selections);
					$template_selections = str_replace('%MAILING_FIN_TABLEAU%','',$template_selections);

					break;
				case 2:

					$template_selections .= file_get_contents(BACK_PATH.'/templates_mailing/selection-2'.$disposition.'.html');

					if($disposition == 'g'){
						$disposition = 'd';
					} else {
						$disposition = 'g';
					}

					break;
			}

			$template_selections = str_replace('%NUM_SEL%',$la_selection,$template_selections);
			$template_selections = str_replace('%MAILING_SEL_DESC%',$_POST['mailing_selection'][$la_selection]['desc'],$template_selections);

			$nb++;
		}

		$template_selections .= ""
			. "</td>"
			. "<td valign=\"top\" width=\"15\"></td>"
		. "</tr>"
		. "<tr>"
			. "<td height=\"20\"></td>"
		. "</tr>";
	}

	//Remplacement des variables du template général
	$template_mailing = str_replace('%MAILING_ENTETE%',$_POST['mailing_entete'],$template_mailing);
	$template_mailing = str_replace('%MAILING_BANNIERE%',$template_banniere,$template_mailing);
	$template_mailing = str_replace('%MAILING_TITRE%',$_POST['mailing_titre'],$template_mailing);
	$template_mailing = str_replace('%MAILING_DESC%',$_POST['mailing_desc'],$template_mailing);
	$template_mailing = str_replace('%MAILING_LIEN_OFFRES%',$_POST['mailing_lien'],$template_mailing);
	$template_mailing = str_replace('%MAILING_PDTS_TPL%',$template_produits,$template_mailing);
	$template_mailing = str_replace('%MAILING_CATS_TPL%',$template_categories,$template_mailing);
	$template_mailing = str_replace('%MAILING_SELS_TPL%',$template_selections,$template_mailing);

	if(isset($_POST['apercu']) && $_POST['apercu'] == 1){
		$template_mailing = str_replace('%MAILING_STYLE_DIV%','style="padding: 20px 0;"',$template_mailing);
		$template_mailing = str_replace('%MAILING_STYLE_TABLE%','style="position: relative; border: 1px solid #d9d9d9; margin: 20px 0;"',$template_mailing);
		$template_mailing = str_replace('%MAILING_CLOSE_APERCU%','<img src="%SITE_URL%/admin/images/icon/erreur.png" style="position: absolute; right: 5px; top: 5px; cursor: pointer;" onclick="close_apercu();">',$template_mailing);
	} else {
		$template_mailing = str_replace('%MAILING_STYLE_DIV%','',$template_mailing);
		$template_mailing = str_replace('%MAILING_STYLE_TABLE%','',$template_mailing);
		$template_mailing = str_replace('%MAILING_CLOSE_APERCU%','',$template_mailing);
	}

	$template_mailing = str_replace('%SITE_URL%',SITE_URL,$template_mailing);
	//%MAILING_DOSSIER%
	//%MAILING_UTM%
	
	echo $template_mailing;
}