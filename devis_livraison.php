 <?php
//numero de page
$pdf->setXY(0, 22);
$pdf->setfont('arial', '', 8);
$pdf->textColor("black");
$pdf->Cell(0, 5, $template->translate("/devis/page", "F") . " " . $pdf->PageNo(), 0, 0, 'R');

$tarifsls=0;

$confort = $confortmes = $premium = $livr_spe = false;
$tauxTVA = $template->tva_ref();
$taux20 = $tauxTVA[1];
//1 - on calcul le tarif standard/eco de livraison
$devises = new devises($DB_site);
$change  = $devises->getchange();
$taux = tauxParPays($DB_site,$facture['paysid']);

if ($taux['port'] == '0' || ($user_info['tvaintracom'] != "" && $taux['tvaintracom'] == 1)){
   $tauxport = 0 ;
} else {
   $tauxport = $taux['TVAtauxnormal'];
}

$prixport  = $frais_port->prixttc($tauxport,$change[$template->devise()]);

if($template->pro) $prixport = $frais_port->prixht;
//2 - on verifie si on propose la confort et on calcul le prix
$install = pays_install($facture['paysid']);
$prixtypeinstall="";
if($install == "sls") {
	$tarifsls = prixsls($facture['codepostal'],$frais_port->getPoidsGramme(),$facture['paysid'],$prixtypeinstall);
	if($tarifsls>0) {
		$confort = true;
		if($template->pro){ 
			$tarifsls = intval($tarifsls)/(1+$taux20/100);
			$tarifsls += $frais_port->prixht;
		}else{
			$tarifsls += $prixport;
		}
	}
	$tarifmes = tarifMES($facture['facturid'], true) ;
	$txtmes = $tarifmes[1];
	$tarifmes = $tarifmes[0]+$tarifsls;
}


if($tarifmes > 0){
	$confortmes = true;
}

//3 on verifie si on propose la premium et on calcul le prix
//a cause de la dissociation est doit reprendre lignefacture 1 par 1 en utilisant la class
mysqli_data_seek($lignefactures, 0);
$prix_montage=0;
while ($lignefacture = $DB_site->fetch_array($lignefactures)) {
	$ligne = new lignefacture($DB_site,intval($lignefacture['lignefactureid']));
	$prix_montage+=prix_montage_devis($ligne);
}

if($template->pro) $prix_montage =intval($prix_montage)/(1+$taux20/100);
if($prix_montage>0 && $tarifsls>0) {
	$premium = true;
	$prix_montage += $tarifsls;
}

if($prixport>0)
	$prixport = $template->price_format($prixport,2,false);
else
	$prixport = $template->translate("/devis/offert","F");

if (intval($facture['modelivraison']) == 5) {
	$livr_spe = true;
}

if($frais_port->livre){

	//ligne bandeau "decouvrez tous les modes de livr" 
	$ly = $pdf->getY();
	$lx = $pdf->getX();
	$pdf->setfont('arial','B',12);
	$pdf->fillColor($autocolor);
	$pdf->textColor("white");
	$pdf->setXY(5,$ly+10);
	$pdf->Cell($total_wcol,6,$template->translate("/devis/livraison/bandeau1","F"),0,0,"C",1);
	$pdf->setXY(185,$ly+10);
	$libPrix = $template->pro ? $template->translate("/devis/prix_ht"):$template->translate("/devis/prix_ttc");
	$pdf->Cell(20,6,$libPrix,0,0,"L",0);
	$ly = $pdf->getY() + 7;
	$lx=5;
	
	//LIVRAISON ECO STANDARD
	if(!$livr_spe){
		
		$pdf->SetXY($lx,$ly);
		$pdf->Rect($lx,$ly,$total_wcol,27,'D');
		$pathImg = "configurations/".$host."/images/templates/eco.jpg";
		$pdf->image($pathImg,$lx+2,$ly+3,17,17,'JPG');
		$pdf->setfont('arial', '', 8);
		$pdf->textColor("black");
		$pdf->setXY($lx+27,$ly+2);
		$livr = $template->translate("/devis/mode_livraison_0_titre");
		$livr .= "\n".sprintf($template->translate("/devis/mode_livraison_0"), $frais_port->getPoidsKg());
		$pdf->multicell(155,4,$livr,0,'L',0);
		$pdf->setfont('arial', 'B', 11);
		$pdf->textColor($autocolor);
		$pdf->setXY($lx+182,$ly+8);
		$pdf->Cell(20,4,$prixport,0,0,'L');
		if(intval($facture['modelivraison']) == 0){
			$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   		$pdf->Image($img,$lx+21,$ly+9,4,4,'PNG');  
		}
		$ly+=28;
	}

	$lx=5;
	$pdf->setXY($lx,$ly);
	//LIVRAISON CONFORT
	if($confort && !$livr_spe){
		$tarifsls = $template->price_format($tarifsls,2,false);
		$pdf->SetXY($lx,$ly);
		$pdf->Rect($lx,$ly,$total_wcol,28,'D');
		$pathImg = "configurations/".$host."/images/templates/confort.jpg";
		$pdf->image($pathImg,$lx+2,$ly+3,17,17,'JPG');

		
		$pdf->textColor("black");
		$pdf->setXY($lx+27,$ly+3);
		$pdf->setfont('arial', '', 11);
		$livr = $template->translate("/devis/mode_livraison_1_titre");
		$livr .= "\n".sprintf($template->translate("/devis/mode_livraison_1"), $frais_port->getPoidsKg());
		$livr .= $template->translate("/devis/mode_livraison_1_2");

		$pdf->setfont('arial', '', 8);
		$pdf->multicell(155,4,$livr,0,'L',0);
		
		$pdf->textColor($autocolor);
		$pdf->setXY($lx+182,$ly+8);
		$pdf->setfont('arial', 'B', 11);
		$pdf->Cell(20,4,$tarifsls,0,0,'L');
		if(intval($facture['modelivraison']) == 1){
			$img = $rootpath."configurations/".$host."/images/templates/validation.png";
       	$pdf->Image($img,$lx+21,$ly+9,4,4,'PNG');  
		}
		$ly+=29;

	}
	$lx=5;
	$pdf->setXY($lx,$ly);
	//LIVRAISON CONFORT MES
	if($confortmes && !$livr_spe){
		$tarifsls = $template->price_format($tarifmes,2,false);
		$livr = $template->translate("/devis/mode_livraison_7_titre");
		$livr .= "\n".sprintf($template->translate("/devis/mode_livraison_7"), $frais_port->getPoidsKg());
		$livr .= "\n".strip_tags($txtmes);
		$pdf->setfont('arial', 'I', 11);
		$livr .= $template->translate("/devis/mode_livraison_7_2");

		$height = $pdf->nblines(155,$livr)*1.8;
		$pdf->SetXY($lx,$ly);
		$pdf->Rect($lx,$ly,$total_wcol,32+$height,'D');
		$pathImg = "configurations/".$host."/images/templates/confortmes.jpg";
		$pdf->image($pathImg,$lx+2,$ly+3,17,17,'JPG');
		$pdf->setfont('arial', '', 8);
		$pdf->textColor("black");
		$pdf->setXY($lx+27,$ly+3);
		
		$pdf->multicell(155,4,$livr,0,'L',0);
		$pdf->setfont('arial', 'B', 11);
		$pdf->textColor($autocolor);
		$pdf->setXY($lx+182,$ly+8);
		$pdf->Cell(20,4,$tarifsls,0,0,'L');
		if(intval($facture['modelivraison']) == 7){
			$img = $rootpath."configurations/".$host."/images/templates/validation.png";
       		$pdf->Image($img,$lx+21,$ly+9,4,4,'PNG');  
		}
		$ly+=29+$height;

	}

	$lx=5;
	$pdf->setXY($lx,$ly);
	//LIVRAISON PREMIUM
	if($premium && !$livr_spe){
		$prix_montage = $template->price_format($prix_montage,2,false);
		$pdf->SetXY($lx,$ly);
		$pdf->Rect($lx,$ly,$total_wcol,22,'D');
		$pathImg = "configurations/".$host."/images/templates/base/".$template->langue."/devis_montage.jpg";
		$pdf->image($pathImg,$lx+2,$ly+3,17,17,'JPG');
		$pdf->setfont('arial', '', 8);
		$pdf->textColor("black");
		$pdf->setXY($lx+27,$ly+3);
		$livr = sprintf($template->translate("/devis/mode_livraison_4"), $frais_port->getPoidsKg());
		$pdf->multicell(155,4,$livr,0,'L',0);
		$pdf->setfont('arial', 'B', 11);
		$pdf->textColor($autocolor);
		$pdf->setXY($lx+182,$ly+8);
		$pdf->Cell(20,4,$prix_montage,0,0,'L');
		if(intval($facture['modelivraison']) == 4){
			$img = $rootpath."configurations/".$host."/images/templates/validation.png";
       		$pdf->Image($img,$lx+21,$ly+9,4,4,'PNG');  
		}
		$ly+=23;
	}

	$lx=5;
	if($livr_spe){
		//on calcul en HT si pro
		$prix_special = $facture['prixport'];
		if($template->pro) $prix_special = $prix_special /(1+$taux20/100);
		$prix_special = $template->price_format($prix_special,2,false);
		$pdf->SetXY($lx,$ly);
		$pdf->Rect($lx,$ly,$total_wcol,22,'D');
		$pathImg = "configurations/".$host."/images/templates/base/".$template->langue."/devis_specialise.jpg";
		$pdf->image($pathImg,$lx+2,$ly+3,17,17,'JPG');
		$pdf->setfont('arial', '', 8);
		$pdf->textColor("black");
		$pdf->setXY($lx+27,$ly+3);
		if($facture['incoterms'] != ""){
			$txtlivr = $facture['incoterms'];
		}elseif($facture['txtlivr']){
			$txtlivr = $facture['txtlivr'];
		}
		$pdf->multicell(155,4,$txtlivr,0,'L',0);
		$pdf->setfont('arial', 'B', 11);
		$pdf->textColor($autocolor);
		$pdf->setXY($lx+182,$ly+8);
		$pdf->Cell(20,4,$prix_special,0,0,'L');
		if(intval($facture['modelivraison']) == 5){
			$img = $rootpath."configurations/".$host."/images/templates/validation.png";
       		$pdf->Image($img,$lx+21,$ly+9,4,4,'PNG');  
		}
		$ly+=23;
	}
}

?>