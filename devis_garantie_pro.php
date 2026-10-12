<?php

//Récupération des informations sur la commande
//$fcl = new facture($DB_site, $facture['facturid']);
//$fcl->lignes();

$destinataire = array(1,2);

$tauxTVA = $template->tva_ref();
$taux20 = $tauxTVA[1];

$lignefactures = $leslignesfactures;


$tabGarantie = array();
$garantiePieces=array();

foreach ($destinataire as $destinataire_id) {

  mysqli_data_seek($lignefactures, 0);

  while ($lignefacture = $DB_site->fetch_array($lignefactures)) { 
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
          //garantie pièces seulement, pro
          if($destinataire_id == 2 && intval($lesGarantiesDeBase[$i]->type_garantie) == 1){
            $garantiePieces[$lfid] = $lesGarantiesDeBase[$i];
          }
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
              $gar->prixht=0; //offerte
              $tabGarantie[$lfid][$destinataire_id][] = $gar;
              $garantieDeBaseAffiche = 1;
            }
            if($dureeAf != 99) $anneeGarantie = $dureeAf." ".$an;
       }
  	}

  	//Garanties SUP
  	$garantiesPossiblesPayantes = DAOgarantie::loadGarantiesPayArtByPays($DB_site,$template->site_code(),'FR',$user['paysid'],$lignefacture->artid, $destinataire_id, $lignefacture->prix_nogarantie_devis,$lignefacture->tva);

  	if(count($garantiesPossiblesPayantes)){
  		for($i=0; $i < count($garantiesPossiblesPayantes); $i++){
  			// voir cumul = 1
  			$duree = $garantiesPossiblesPayantes[$i]->duree;
  			$an = $template->translate("/article/garantie_type/an");
        if ($duree>1) $an = $template->translate("/article/garantie_type/ans");
        $gar = new garantie($DB_site,'base',$template->langue,$garantiesPossiblesPayantes[$i]->garantieid);
        $gar->prix = $garantiesPossiblesPayantes[$i]->prix;
        $gar->prixht = $gar->prix/(1+$taux20/100);
        $tabGarantie[$lfid][$destinataire_id][] = $gar;
  		}
  	}
  }
}
$nbart = 0;
if(count($tabGarantie)>0){
  //ligne bandeau "decouvrez tous les modes de livr" 
  bandeau_garantie($pdf,$template,$autocolor);
  $ly+=4;

  //garanties part et pro
  foreach ($tabGarantie as $lfid => $tablfid) {
    $nbart++;
    $saut = false;
    // calcul si faut ajouter une page
    if ($ly>=205) {
        //footer($pdf);
        //options defaut select
        $pdf->SetAutoPageBreak(false);
        $pdf->addpage();
        $saut=true;
        $nbart=1;
        Head($pdf);
        $ly = $pdf->getY()+5;
        $height=0;
        $ly+=10;
        //$ly=86;
        bandeau_garantie($pdf,$template,$autocolor);
    }
    if($nbart%2==0){
        $pdf->Line(5,$ly,5+$total_wcol,$ly);
        $ly += 5;
      }
    $lignefacture = new lignefacture($DB_site,$lfid);
    $lx=5;
    //Ligne de Séparation entre 2 articles
    
    
    //IMAGE PRODUIT GAUCHE
    $imgpath = $rootpath."configurations/".$host."/images/produits/".$lignefacture->artid.".jpg";
    if ($imgpath != "" && file_exists($imgpath)) {
      $size  = getimagesize($imgpath);
      $img_w = $wcol[0] - 16;
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
      //on enregistre la hauteurde l'image avec tout ce qu'il y a en dessous
      $ly_img = $pdf->getY()+$logo_w;
    }

    $lx=40;

    if(count($garantiePieces[$lfid])>0){
      //on trace le rectangle "pieces"
      $pdf->SetXY($lx,$ly);
      $pdf->Rect($lx,$ly,166,18,'D');
      $pdf->setfont('arial','',10);
      $pdf->textColor("white");
      $pdf->SetXY($lx,$ly);
      $pdf->fillColor("bleu");
      $pdf->Cell(8,18,"",0,0,'L',1);
      $pdf->RotatedText($lx+5,$ly+14.5,utf8_decode($template->translate("/devis/garantie_pro/pieces")),90);
      $pdf->setXY($lx+10,$ly+2);
      $pdf->setfont('arial','B',9);
      $pdf->textColor("black");
      $pdf->Cell(40,4,$template->translate("/devis/garantie_pro/lib3","F"),0,0,'L');
      $ly+=7;
      //On le rempli
      $garantie = $garantiePieces[$lfid];
      if($garantie->prixht>0){
        $prix = $template->price_format($garantie->prixht,2,false);
        if($lignefacture->qte > 1) $prix = $lignefacture->qte." x ".$prix;
      }else{
        $prix = $template->translate('/article/garantie_type/offerte','F');
      }

      //si la garantie correspond a celle choisi on affiche le check
      if(intval($lignefacture->garantieid) == intval($garantie->garantieid)){
        $img = $rootpath."configurations/".$host."/images/templates/validation.png";
        $pdf->Image($img,$lx+11,$ly+2,4,4,'PNG');
      }

      $pdf->setfont('arial','',10);
      $pdf->textColor("white");
      $pdf->setXY($lx+17,$ly);
      $pdf->fillColor("bleu");
      $pdf->Cell(13,8,anneeGarantie($garantie,$template,$DB_site,$lignefacture,$user['paysid'],2),0,0,'C',true);
      $pdf->setXY($lx+31,$ly);
      $pdf->setfont('arial','B',9);
      $pdf->textColor("black");
      $pdf->multicell(88,4,libTypeGarantie($garantie,$template,$DB_site,$lignefacture,$user['paysid'],2),0,'L',0);
      $pdf->setfont('arial','',7);
      $pdf->setXY($lx+31,$ly+4);
      $pdf->multicell(88,4,descrptionGarantie($template,$garantie,$DB_site,$lignefacture,$user['paysid'],2),0,'L',0);
      //POINTILLES GARANTIE
      $pdf->setXY($lx+115,$ly);
      $pdf->multicell(30,4,". . . . . . . . . . . . . . . . .",0,'L',0);
      //PRIX GARANTIE
      $pdf->textColor("rouge");
      $pdf->setfont('arial','B',11);
      $pdf->setXY($lx+146,$ly);
      $pdf->Cell(17,4,$prix,0,0,'R');

      $ly+=13;
      
    }

    //on trace le rectangle "domestique"
    $pdf->SetXY($lx,$ly);
    $pdf->drawColor("dore");
    $pdf->Rect($lx,$ly,166,30,'D');
    $pdf->setfont('arial','',10);
    $pdf->textColor("white");
    $pdf->SetXY($lx,$ly);
    $pdf->fillColor("dore");
    $pdf->Cell(8,30,"",0,0,'L',1);
    $pdf->RotatedText($lx+5,$ly+24.5,$template->translate("/devis/garantie_pro/domestique"),90);
    $pdf->setXY($lx+10,$ly+2);
    $pdf->setfont('arial','B',9);
    $pdf->textColor("black");
    $pdf->Cell(40,4,$template->translate("/devis/garantie_pro/lib1","F"),0,0,'L');
    $ly+=7;
    $height = 7;
    //on le rempli
    foreach ($tablfid[1] as $garantie) {
      if($garantie->prixht>0){
        $prix = $template->price_format($garantie->prixht,2,false);
        if($lignefacture->qte > 1) $prix = $lignefacture->qte." x ".$prix;
      }else{
        $prix = $template->translate('/article/garantie_type/offerte','F');
      }

      //si la garantie correspond a celle choisi on affiche le check
      if(intval($lignefacture->garantieid) == intval($garantie->garantieid)){
        $img = $rootpath."configurations/".$host."/images/templates/validation.png";
        $pdf->Image($img,$lx+11,$ly+2,4,4,'PNG');
      }

      $pdf->setfont('arial','',10);
      $pdf->textColor("white");
      $pdf->setXY($lx+17,$ly);
      $pdf->fillColor("dore");
      $pdf->Cell(13,8,anneeGarantie($garantie,$template,$DB_site,$lignefacture,$user['paysid'],1),0,0,'C',true);
      $pdf->setXY($lx+31,$ly);
      $pdf->setfont('arial','B',9);
      $pdf->textColor("black");
      $pdf->multicell(88,4,libTypeGarantie($garantie,$template,$DB_site,$lignefacture,$user['paysid'],1),0,'L',0);
      $pdf->setfont('arial','',7);
      $pdf->setXY($lx+31,$ly+4);
      $pdf->multicell(88,4,descrptionGarantie($template,$garantie,$DB_site,$lignefacture,$user['paysid'],1),0,'L',0);
      //POINTILLES GARANTIE
      $pdf->setXY($lx+115,$ly);
      $pdf->multicell(30,4,". . . . . . . . . . . . . . . . .",0,'L',0);
      //PRIX GARANTIE
      $pdf->textColor("rouge");
      $pdf->setfont('arial','B',11);
      $pdf->setXY($lx+146,$ly);
      $pdf->Cell(17,4,$prix,0,0,'R');

      $ly+=11;
      $height +=11;
    }
    if($height<30){
      $diff = 30 - $height;
      $ly += $diff;
    }
    $ly+=3;

    //on trace le rectangle "professionel"
    $pdf->SetAutoPageBreak(false);
    $pdf->SetXY($lx,$ly);
    $pdf->drawColor("bleu");
    $pdf->Rect($lx,$ly,166,40,'D');
    $pdf->setfont('arial','',10);
    $pdf->textColor("white");
    $pdf->SetXY($lx,$ly);
    $pdf->fillColor("bleu");
    $pdf->Cell(8,40,"",0,0,'L',1);
    $pdf->RotatedText($lx+5,$ly+31,$template->translate("/devis/garantie_pro/pro"),90);
    $pdf->setXY($lx+10,$ly+2);
    $pdf->setfont('arial','B',9);
    $pdf->textColor("black");
    $pdf->Cell(40,4,$template->translate("/devis/garantie_pro/lib2","F"),0,0,'L');
    $ly+=7;
    $height=7;
    //on le rempli
    foreach ($tablfid[2] as $garantie) {
      
      if($garantie->prixht>0){
        $prix = $template->price_format($garantie->prixht,2,false);
        if($lignefacture->qte > 1) $prix = $lignefacture->qte." x ".$prix;
      }else{
        $prix = $template->translate('/article/garantie_type/offerte','F');
      }

      //si la garantie correspond a celle choisi on affiche le check
      if(intval($lignefacture->garantieid) == intval($garantie->garantieid)){
        $img = $rootpath."configurations/".$host."/images/templates/validation.png";
        $pdf->Image($img,$lx+11,$ly+2,4,4,'PNG');  
      }

      $pdf->setfont('arial','',10);
      $pdf->textColor("white");
      $pdf->setXY($lx+17,$ly);
      $pdf->fillColor("bleu");
      $pdf->Cell(13,8,anneeGarantie($garantie,$template,$DB_site,$lignefacture,$user['paysid'],2),0,0,'C',true);
      $pdf->setXY($lx+31,$ly);
      $pdf->setfont('arial','B',9);
      $pdf->textColor("black");
      $pdf->multicell(88,4,libTypeGarantie($garantie,$template,$DB_site,$lignefacture,$user['paysid'],2),0,'L',0);
      $pdf->setfont('arial','',7);
      $pdf->setXY($lx+31,$ly+4);
      $pdf->multicell(88,4,descrptionGarantie($template,$garantie,$DB_site,$lignefacture,$user['paysid'],2),0,'L',0);

      //POINTILLES GARANTIE
      $pdf->setXY($lx+115,$ly);
      $pdf->multicell(30,4,". . . . . . . . . . . . . . . . .",0,'L',0);
      //PRIX GARANTIE
      $pdf->textColor("rouge");
      $pdf->setfont('arial','B',11);
      $pdf->setXY($lx+146,$ly);
      $pdf->Cell(17,4,$prix,0,0,'R');
      $ly+=11;
      $height+=11;
      $pdf->SetAutoPageBreak(true);
    }
    if($height<40){
      $diff = 40 - $height;
      $ly += $diff;
    }
    if($ly<$ly_img) $ly = round($ly_img);
    $ly+=3;
    $pdf->drawColor($autocolor);
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
  $pdf->setfont('arial','',8);;
  $pdf->textColor("black");
  $pdf->fillColor("white");
  $pdf->Cell(50,4,$template->translate("/devis/options_defaut"),0,0,'L',1);

  //ligne bandeau "decouvrez tous les modes de livr" 
  $pdf->setfont('arial','B',12);
  $pdf->fillColor($autocolor);
  $pdf->textColor("white");

  //$ly+=1;
  $pdf->setXY(5,$ly);
  $pdf->Cell($total_wcol,6,$template->translate("/devis/garantie/bandeau1","F"),0,0,"C",1);
  $pdf->setXY(185,$ly);
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