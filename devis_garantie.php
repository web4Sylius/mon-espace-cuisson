<?php

//Récupération des informations sur la commande
$fcl = new facture($DB_site, $facture['facturid']);
$fcl->lignes();
$destinataire_id = !empty($user['raisonsociale']) ? 2 : 1;

$tabGarantie = $garantiesPossiblesPayantes = array();
$en = 0;
if($template->langue == 'EN') $en = 2;
$lignefactures = $leslignesfactures;
mysqli_data_seek($lignefactures, 0);
//foreach($fcl->lignes as $lfid => $lignefacture){
while ($lignefacture = $DB_site->fetch_array($lignefactures)) {  
  // if(intval($lignefacture[artid]) == 0) continue;
  $lignefacture = new lignefacture($DB_site,intval($lignefacture['lignefactureid']));
  $lfid = $lignefacture->lignefactureid;
  $garantieDeBaseAffiche = 0;
	//GESTION des garanties de base
	$lesGarantiesDeBase = DAOgarantie::loadMaxGarantieArtByPays($DB_site,$template->site_code(),'FR',$user['paysid'],$lignefacture->artid,$destinataire_id,$lignefacture->prix_nogarantie_devis);
  if(count($lesGarantiesDeBase)>0){
		//On boucle sur chacune d'entre elles
       for($i=0; $i < count($lesGarantiesDeBase); $i++){
       		$duree = $lesGarantiesDeBase[$i]->duree;
       		//voir pour cumul = 1

       		//Pour orthographier "an" correctement
            $an = $template->translate("/article/garantie_type/an");
            if ($lesGarantiesDeBase[$i]->duree > 1) {
                 $an = $template->translate("/article/garantie_type/ans");
            }
            if($i == 0){
            	$prix 		= $lesGarantiesDeBase[$i]->prix;
              $dureeBase 	= $lesGarantiesDeBase[$i]->duree;
              $gar = new garantie($DB_site,'base',$template->langue,$lesGarantiesDeBase[$i]->garantieid);
              $gar->prix = 0; // offerte car de base;
              $tabGarantie[$lfid][] = $gar;
              $garantieDeBaseAffiche = 1;
            }
            if($dureeAf != 99) $anneeGarantie = $dureeAf." ".$an;
       }
	}

	//Garanties SUP
	$garantiesPossiblesPayantes = DAOgarantie::loadGarantiesPayArtByPays($DB_site,$template->site_code(),'FR',$user['paysid'],$lignefacture->artid, !empty($user['raisonsociale']) ? 2 : 1, $lignefacture->prix_nogarantie_devis,$lignefacture->tva, $lignefacture->lignefactureid);
	if(!is_null($garantiesPossiblesPayantes) && count($garantiesPossiblesPayantes)){
		for($i=0; $i < count($garantiesPossiblesPayantes); $i++){
			// voir cumul = 1
			$duree = $garantiesPossiblesPayantes[$i]->duree;
			$an = $template->translate("/article/garantie_type/an");
			if ($duree>1) $an = $template->translate("/article/garantie_type/ans");
			$gar = new garantie($DB_site,'base',$template->langue,$garantiesPossiblesPayantes[$i]->garantieid);
			$gar->prix = $garantiesPossiblesPayantes[$i]->prix;
			$tabGarantie[$lfid][] = $gar;
		}
	}

}

$nbart = 0;
if(count($tabGarantie)>0){

  //ligne bandeau "decouvrez tous les modes de livr" 
  bandeau_garantie($pdf,$template,$autocolor);

  foreach ($tabGarantie as $lfid => $garanties) {
    $nbart++;
    // calcul si faut ajouter une page
    if ($ly>=205) {
        footer($pdf);
        $pdf->addpage();
        Head($pdf);
        $ly = $pdf->getY()+5;
        $height=0;
        $ly+=5;
        bandeau_garantie($pdf,$template,$autocolor);
    }
    $lignefacture = new lignefacture($DB_site,$lfid);
    $lx=5;

    //Ligne de Séparation entre 2 articles
    if($nbart%2==0){
      $pdf->Line(5,$ly,5+$total_wcol,$ly);
      $ly += 5;
    }

    //IMAGE PRODUIT GAUCHE
    $imgpath = $rootpath."configurations/".$host."/images/produits/".$lignefacture->artid.".jpg";
    if ($imgpath != "" && file_exists($imgpath)) {
      $size  = getimagesize($imgpath);
      $img_w = $wcol[0] - 20;
      $img_h = $size[1] * $img_w / $size[0]; 
      $pdf->image($imgpath, (35 - $img_w) / 2, $ly, $img_w, $img_h, 'JPG');
      
      //libelle article sous l'image
      $libelle_art = libArticle($lignefacture->libelle,70);
      $pdf->setXY($lx-5,$ly+$img_h);
      $pdf->textColor("black");
      $pdf->setfont('arial','B',8);
      //$pdf->MultiCell(35,3,$libelle_art,0,'C',0);
      //Reference article
      $y = $pdf->getY();
      $pdf->setXY($lx-5,$y+2);
      $pdf->textColor($autocolor);
      $pdf->setfont('arial','',8);
      $pdf->Cell(35,2,$template->translate("/devis/ref")." ".$lignefacture->artcode,0,0,"C");
      
      //quantite article
      $y = $pdf->getY();
      $pdf->setXY($lx-5,$y+4);
      $pdf->textColor("black");
      $pdf->Cell(35,2,$template->translate("/devis/quantite")." : ".$lignefacture->qte,0,0,"C");

      //image marque article
      $logom = $rootpath."configurations/".$host."/images/marques/".$lignefacture->marqueid.".jpg";
      if(file_exists($logom)){
        $size=getimagesize($logom);
        $logo_w=15;
        $y = $pdf->getY() + 4;
        //if($size[1]*$logo_w/$size[0]>12) $logo_w=round($size[0]*12/$size[1]);
        $pdf->image($logom,(35-$logo_w)/2,$y,$logo_w);
      }
      $ly_img = $pdf->getY()+$logo_w;

    }

    foreach ($garanties as $garantie) {

      if($garantie->prix>0){
        $prix = $template->price_format($garantie->prix,2,false);
        if($lignefacture->qte > 1) $prix = $lignefacture->qte." x ".$prix;
      }else{
        $prix = $template->translate('/article/garantie_type/offerte','F');
      }
      
      $lx=5;
      
      //si la garantie correspond a celle choisi on affiche le check
      if(intval($lignefacture->garantieid) == intval($garantie->garantieid)){
        $img = $rootpath."configurations/".$host."/images/templates/validation.png";
        $pdf->Image($img,$lx+29,$ly+5,4,4,'PNG');  
      }
      
      $lx+=35;
      $pdf->SetXY($lx,$ly);
      $pdf->Rect($lx,$ly,166,15,'D');
      $pdf->fillColor($autocolor);
      $pdf->setfont('arial','B',11);
      $pdf->textColor("white");
      $pdf->Multicell(8,15,"",1,'C',1);
      $pdf->RotatedText($lx+5,$ly+12+$en,anneeGarantie($garantie,$template,$DB_site,$lignefacture,$user['paysid'],$destinataire_id),90);
      //$pdf->Cell(4,18,anneeGarantie($garantie,$template),0,0,'L');
      $pdf->SetXY($lx+10,$ly+3);
      $pdf->setfont('arial','B',9);
      $pdf->textColor("black");
      $pdf->multicell(88,4,libTypeGarantie($garantie,$template,$DB_site,$lignefacture,$user['paysid'],$destinataire_id),0,'L',0);
      $height += $pdf->nblines(88,libTypeGarantie($garantie,$template,$DB_site,$lignefacture,$user['paysid'],$destinataire_id));
      $pdf->setXY($lx+10,$ly+7);
      $pdf->setfont('arial','',8);
      $pdf->multicell(88,4,descrptionGarantie($template,$garantie,$DB_site,$lignefacture,$user['paysid'],$destinataire_id),0,'L',0);
      $height += $pdf->nblines(88,descrptionGarantie($template,$garantie,$DB_site,$lignefacture,$user['paysid'],$destinataire_id));
      //POINTILLES GARANTIE
      $pdf->setXY($lx+100,$ly+2);
      $pdf->multicell(40,4,". . . . . . . . . . . . . . . . . ",0,'L',0);
      //PRIX GARANTIE
      $pdf->textColor($autocolor);
      $pdf->setfont('arial','B',11);
      $pdf->setXY($lx+137,$ly+2);
      $pdf->Cell(30,4,$prix,0,0,'L');

      $ly+=16;
      $height+=15;
    }

    //PLATINE HOME
    //on vérifie si l'article est compatible avec la garantie platinehome
    $platinehome = DAOgarantie::platinehomeID($DB_site);
    if(DAOgarantie::platinehome($DB_site,$platinehome,$lignefacture,$user['raisonsociale'],$user['paysid'])){
        $prixArticle = $lignefacture->totalttc / $lignefacture->qte;
        $categProduit = "Cave à vin";
        //on appel le web service pour connaitre le tarif et le nom de garantie
        include($_SERVER['DOCUMENT_ROOT']."/platinehome_categ.php");        

        //si la garantie correspond a celle choisi on affiche le check
        if(intval($lignefacture->garantieid) == intval($platinehome)){
          $img = $rootpath."configurations/".$host."/images/templates/validation.png";
          $pdf->Image($img,$lx-6,$ly+14,4,4,'PNG');  
        }
        $pdf->SetXY($lx,$ly);
        $pdf->Rect($lx,$ly,166,33,'D'); //cadre

        //texte vertical
        $pdf->fillColor($autocolor);
        $pdf->setfont('arial','B',11);
        $pdf->textColor("white");
        $pdf->Cell(8,33,"",0,0,'C',1);
        $pdf->RotatedText($lx+5,$ly+29,$template->translate("/garantie/platine_home/abo","F"),90);

        $pdf->textColor("black");
        $pdf->setfont('arial','B',9);
        $pdf->SetXY($lx+10,$ly+2);
        $pdf->Cell(88,4,$template->translate("/garantie/platine_home/titre","F"),0,0,'L');
        $height += $pdf->nblines(88,$template->translate("/garantie/platine_home/titre","F"));
        $pdf->setfont('arial','B',8);
        $pdf->SetXY($lx+10,$ly+6);
        $pdf->Cell(88,4,$template->translate("/garantie/platine_home/devis_titre2_1","F"),0,0,'L');
        $height += $pdf->nblines(88,$template->translate("/garantie/platine_home/devis_titre2_1","F"));
        $pdf->setfont('arial','',8);
        $pdf->SetXY($lx+10,$ly+10);
        $pdf->MultiCell(120,4,$template->translate("/garantie/platine_home/devis_titre2_2","F"),0,'L',false);
        $height += $pdf->nblines(120,$template->translate("/garantie/platine_home/devis_titre2_2","F"));
        $pdf->SetXY($lx+10,$ly+19);
        $pdf->MultiCell(120,4,$template->translate("/garantie/platine_home/devis_titre2_3","F"),0,'L',false);
        $height+=$pdf->nblines(120,$template->translate("/garantie/platine_home/devis_titre2_3","F"));
        //POINTILLES GARANTIE
        $pdf->setXY($lx+100,$ly+2);
        $pdf->multicell(30,4,". . . . . . . . . . . . . . . . .",0,'L',0);

        //PRIX PLATINE HOME

        //si c'est un lot on affiche le nombre d'abonnements en fonction des articles eligibles du lot ex: 2 x 11€ / mois
        if(DAOarticle::isLot($DB_site, $lignefacture->artid)){
             $count = 0;
              $tabArticlesLot = DAOarticle::listeArticlesLots($DB_site, $lignefacture->artid);
              foreach ($tabArticlesLot as $art ) {
                  if(DAOgarantie::platinehomeArticles($DB_site, $art['artid'], $platinehome))
                       $count ++;
              }
        }else{
             $count = 1;
        }

        //gestion de l'affichage du tarif platine homev
        if($offer_price != ''){
          $offer_price = $template->price_format($offer_price,0,false);
          if(intval($lignefacture->qte) > 1){// si qte multiple du produit
               if($count > 1)//si le poduit est un lot de plusieurs articles
                    $x = $lignefacture->qte * $count;
               else
                    $x = $lignefacture->qte;
               
               $offer_price_affiche = $x . " x " . $offer_price;
          }else{
               if($count > 1)//si le poduit est un lot de plusieurs articles
                    $offer_price_affiche = $count . " x " . $offer_price;
               else
                    $offer_price_affiche = $offer_price;
          }

          $offer_price_affiche .= "/".$template->translate("/garantie/platine_home/mois");
        }
        
        //Affichage prix garantie  
        $pdf->textColor($autocolor);
        $pdf->setfont('arial','B',11);
        $pdf->setXY($lx+137,$ly+2);
        $pdf->Cell(30,4,$offer_price_affiche,0,0,'L');
        //cliquez ici
        $pdf->setfont('arial','B',7);
        $pdf->setXY($lx+117,$ly+28);
        $pdf->Cell(40,4,$template->translate("/garantie/platine_home/devis_lien1","F"),0,0,'L');
        $pdf->setXY($lx+145,$ly+28);
        $pdf->Cell(10,4,$template->translate("/garantie/platine_home/devis_lien2"),0,0,'L');
        $pdf->Link($lx+145,$ly+28,15,4,rewrite_url("",$template->langue,"/int,253,garantie-platine-home.htm",$template,$_SERVER['HTTP_HOST'],false));

        $ly+=35;
        $height+=35;
    }
    if($ly<$ly_img) $ly = round($ly_img);
  }
}

function bandeau_garantie($pdf,$template,$autocolor){
  global $lx,$ly,$total_wcol,$rootpath,$host;

  $pdf->setXY(0, 22);
  $pdf->setfont('arial', '', 8);
  $pdf->textColor("black");
  $pdf->Cell(0, 5, $template->translate("/devis/page", "F") . " " . $pdf->PageNo(), 0, 0, 'R');

  $img = $rootpath."configurations/".$host."/images/templates/validation.png";
  $pdf->Image($img,6,27,4,4,'PNG');
  $pdf->SetXY(10,27);
  $pdf->setfont('arial','',8);
  $pdf->textColor("black");
  $pdf->fillColor("white");
  $pdf->Cell(50,4,$template->translate("/devis/options_defaut"),0,0,'L',1);
  
  //ligne bandeau "decouvrez tous les modes de livr" 
  $pdf->setfont('arial','B',12);
  $pdf->fillColor($autocolor);
  $pdf->textColor("white");

  $ly+=5;
  $pdf->setXY(5,$ly);
  $pdf->Cell($total_wcol,6,$template->translate("/devis/garantie/bandeau1","F"),0,0,"C",1);
  $pdf->setXY(182,$ly);
  $libPrix = $template->pro ? $template->translate("/devis/prix_ht"):$template->translate("/devis/prix_ttc");
  $pdf->Cell(20,6,$libPrix,0,0,"L",0);
  $ly+=7;


}

function libArticle($libelle,$limit){
  if(strlen($libelle) > $limit ){
    return substr($libelle, 0, $limit);
  }else{
    return $libelle;
  }
}

/**
 * Renvoi le libelle correspondant
 * au type de garantie
 * @param object $garantie 
 * @param object $template 
 * @param object $DB 
 * @param object $lignefacture 
 * @param string $paysid 
 * @param int $destinataire_id 
 * @return string
 */
function libTypeGarantie($garantie, $template,$DB,$lignefacture,$paysid,$destinataire_id){

  $duree = dureeGarantie($garantie,$DB,$lignefacture,$paysid,$destinataire_id);

  if (intval($duree) > 1) {
    $an = $template->translate("/article/garantie_type/ans");
  }else{
    $an = $template->translate("/article/garantie_type/an");
  }

  if(intval($duree) != 99 ){
    $lib = $duree." ".$an;  
  }else{
    $lib = $template->translate("/article/garantie_type/a_vie",'F');
  }

  $lib .= " ".$template->translate("/article/garantie_type/".$garantie->type_garantie);

  $lib = $template->translate('/garanties/garantie','F')." ".$lib;

  return $lib;
}

/**
 * retourne description Garantie (date de validité)
 * @param object $template 
 * @param object $garantie 
 * @return string
 */
function descrptionGarantie($template,$garantie,$DB,$lignefacture,$paysid,$destinataire_id){
  $date = new DateTime(date('Y-m-d'));
  $duree = dureeGarantie($garantie,$DB,$lignefacture,$paysid,$destinataire_id);
  if($duree != ""){

      $date->modify('+'.$duree.' year');
      $date->modify('-1 day');

  }
  $date = $template->translate("/site/mois/".$date->format('n'))." ".$date->format('Y');
  $lib = $template->translate('/garanties/garantie','F')." ".$template->translate("/article/garantie_type/valable_jusqu_en")." ".$date;

  return $lib;
}

/**
 * retourne juste l'année pour affichage a 90degres.
 * @param object $garantie 
 * @param object $template 
 * @param object $DB 
 * @param object $lignefacture 
 * @param string $paysid 
 * @param int $destinataire_id 
 * @return string
 */
function anneeGarantie($garantie,$template,$DB,$lignefacture,$paysid,$destinataire_id){

  $duree = dureeGarantie($garantie,$DB,$lignefacture,$paysid,$destinataire_id);
  
  if (intval($duree) > 1) {
    $an = $template->translate("/article/garantie_type/ans");
  }else{
    $an = $template->translate("/article/garantie_type/an");
  }
  if(intval($duree) != 99 ){
    $lib = $duree." ".$an;  
  }else{
    $lib = $template->translate("/article/garantie_type/a_vie",'F');
  }

  return $lib;
}

function dureeGarantie($garantie,$DB,$lignefacture,$paysid,$destinataire_id){
  $duree = $garantie->duree;
  if($garantie->cumul == 1){
    $allGaranties=DAOgarantie::loadGarantiesArtByPaysSaufExtensions($DB,'base','FR',$paysid,$lignefacture->artid, $destinataire_id);
    $pointer=DAOgarantie::searchGarantieToAddExtension($DB,$garantie->type_garantie,$allGaranties,$garantie->garantieid);
    /*if ($allGaranties[$pointer]->prix){
       $ppi += $allGaranties[$pointer]->ppi;
    }*/
    $duree += $allGaranties[$pointer]->duree;
  }
  return $duree;

}

?>