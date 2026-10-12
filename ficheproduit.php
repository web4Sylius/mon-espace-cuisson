<?php

/// les options du devis
function ficheproduit($pdf,$article,$ligne=null){
	global $rootpath,$template,$host,$DB_site,$facture,$prixkw,$autocolor,$afficheprixopt;

	$marque=$DB_site->query_first("SELECT * FROM marque m, article_marque am WHERE m.marqueid=am.marqueid AND am.artid='$article[artid]'");
	$article['marque']=$marque['libelle'];

	//si l'article est un lot:
	$req_lot=$DB_site->query("SELECT artid,qte FROM article_lot WHERE lotid=".$article['artid']);

	$liste_caractvalid=$DB_site->query("SELECT lfc.caractvalid, cv.libelle".$template->langue." as libellecaracval, c.libelle".$template->langue." as libellecarac, c.caractid, lf.qte FROM lignefacture lf INNER JOIN `lignefacturecaracteristique` lfc ON lfc.lignefactureid = lf.lignefactureid INNER JOIN caracteristiquevaleur cv ON cv.caractvalid = lfc.caractvalid INNER JOIN caracteristique c ON c.caractid = cv.caractid WHERE lf.lignefactureid = ".$ligne." ORDER BY c.position ASC");

	$dissoc=false;

	//si dissocié, on va chercher dans lignefacturediss
	if(is_null($liste_caractvalid) || $DB_site->num_rows($liste_caractvalid) == 0){
		$liste_caractvalid=$DB_site->query("SELECT lfc.caractvalid, cv.libelle".$template->langue." as libellecaracval, c.libelle".$template->langue." as libellecarac, c.caractid,  sum(lf.qte) as qte FROM lignefacture lf INNER JOIN `lignefacturecaracteristique` lfc ON lfc.lignefactureid = lf.lignefactureid INNER JOIN caracteristiquevaleur cv ON cv.caractvalid = lfc.caractvalid INNER JOIN caracteristique c ON c.caractid = cv.caractid WHERE lf.lignefacturedissid = ".$ligne." GROUP BY cv.caractid ORDER BY c.position ASC");
		$dissoc=true;
	}


	///Si les types d'article ne correspondent pas, on n'affiche pas la fiche technique
	/*--------------------*/
	if(in_array($article['typearticleid'],array('1','2','3','4','5','6','7','10','11','12','17','21','22','23','25','28','29','30'))) {

	//Page des caractéristiques principales du produit
		$pdf->AddPage();
		$pdf->SetAutoPageBreak(false,5);

		$col2=100;
		$col1=40;
		$py=30;

		$pdf->SetFont('Arial','B',25);
		$pdf->Cell(100);
		$pdf->image($rootpath."configurations/".$host."/images/templates/".$template->site_code()."/".$template->langue."/logo".($template->pro?"_pro":"").".jpg",5,5,84,0,"JPG",$template->translate("/site/[site_code]/url"));
		$pdf->textColor($autocolor);

		$pdf->setXY(80,7);
		$pdf->setfont('arial','B',18);
		$title=$template->translate("/devis/devis_n","F");
		if(isset($_GET['proforma'])) $title=$template->translate("/devis/facture_proforma_n","F");
		$pdf->cell(80,8,$title.$facture['facturid'],0,0,'C');

		$pdf->setfont('arial','',8);
		$pdf->textColor("black");
		$pdf->setXY(185,22);
		$pdf->cell(20,8,"Page ".$pdf->PageNo(),0,0,'R');

		$w=45;
		$ly=$pdf->getY();
		$logoimg=$rootpath."configurations/".$host."/images/marques/".$marque['marqueid'].".".$marque['image'];
		if(file_exists($logoimg)){
			$size=getimagesize($logoimg);
			if($size[1]>300) $w=intval($size[0]*(300/$size[1])*0.25);
			if($w>45) $w=45;
			$logo_w=40;
			if($size[1]*$logo_w/$size[0]>12) $logo_w=round($size[0]*12/$size[1]);
			$pdf->image($logoimg,10,$py,27);
		}
		$py+=28;
		$pdf->settextcolor(48,40,37);
		$pdf->setfont('arial','',8);
		$lblheight=$pdf->nblines(45,$article['libelle'.$template->langue])*3;
		$pdf->setXY(9,$py);
		$pdf->multicell(55,3,html_entity_decode($article["libelle".$template->langue],ENT_COMPAT,"ISO-8859-15"),0,'L',0);   
		$height = $pdf->nblines(56,html_entity_decode($article["libelle".$template->langue],ENT_COMPAT,"ISO-8859-15"));
	    $pdf->textColor($autocolor);
	    $pdf->setY($py+1+($height*3));
	    $pdf->setX(9);
	    $pdf->Cell(45,3,$marque['libelle']." ".$article['gamme'.$template->langue]." ".$article['artcode'],0,0,'L');
	    $py+=$height*3 + 1;  

		$autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=".$article['artid']." AND visible=1 AND (langue='' OR langue='".$template->langue."')");
		$autreid="";
		if($autreimg!="") $autreid="_".$autreimg['articlephotoid'];
		$image=$rootpath."configurations/".$host."/images/produits/br/".$article['artid'].$autreid.".".$article['image'];
		$w=46;
		$py=$pdf->getY();
		$setimg = false;
		$photos=$DB_site->query("SELECT * FROM articlephoto WHERE artid=".$article['artid']." AND langue IN ('','".$template->langue."') AND visible=0 AND pro IN ('".($template->pro?2:1)."','0') ORDER BY position LIMIT 6");
		while($photo=$DB_site->fetch_array($photos)){
			$py+=($w*($size[0]==0?0:$size[1]/$size[0]))+5;
			$image=$rootpath."configurations/".$host."/images/produits/br/".$article['artid']."_".$photo['articlephotoid'].".".$photo['image'];
			if(file_exists($image)){
				$size=getimagesize($image);
				$imgbottom=$py+($w*$size[1]/$size[0]);
				if($imgbottom<280) {
					$pdf->image($image,10,$py,$w);
					$setimg = true;
				}
				else
					break;
			}
		}
		if(!$setimg){
			if(file_exists($image)){
				$size=getimagesize($image);
				if($size[1]>392) $w=intval($size[0]*(392/$size[1])*0.25);
				if($w>46) $w=46;
				$pdf->image($image,10,$py,$w);
			}
		}

		$ptablex=64;

		$pdf->setXY($ptablex,$ly+12);
		$pdf->fillColor($autocolor);
		$pdf->textColor("white");
		$pdf->setfont('arial','B',10);
		$pdf->cell($col1+$col2,5,$marque['libelle']." ".$article['gamme'.$template->langue]." ".$article['artcode']." - PRESENTATION",0,0,'C',1);
		$ly+=17;

		$pdf->textColor("black");
		$pdf->setfont('arial','',8);

		/*$description = $article['description'.$template->langue];
		$description = str_replace("<br />", "\n",$description);
		$pdf->textColor("black");
		$pdf->setfont('arial','B',10);
		$pdf->setXY($ptablex,$ly+7);
		$pdf->cell(140,5,"Description générale",0,0,'L',0);
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly+13);
		$pdf->multicell(140,3,strip_tags(html_entity_decode($description,ENT_QUOTES,"ISO-8859-15")),0,'L',0);
		$lblheight=$pdf->nblines(120,strip_tags($description))*3;
		$ly+=$lblheight;*/
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',10);
		$pdf->cell(140,5,"Données Techniques",0,0,'L',0);
		$ly+=4;
		$pdf->setfont('arial','',8);
		include("ficheproduit".$article['typearticleid'].".php");

		//accessoires inclus
		$i=0;
		$accessinclus='';
		$requete=$DB_site->query("SELECT aa.id,aa.qte,aa.libelle FROM article_accessoire aa WHERE aa.artid=".$article['artid']);
		if($DB_site->num_rows($requete)>0){
			while($acc=$DB_site->fetch_array($requete)){
				if($i>0) $accessinclus.=", ";
				//var_dump($acc["libelle"]);
		        $accessinclus.= $template->translate($acc["libelle"])." x ".$acc[qte];
		        $i++;
			}
		}
		$ly+=4;
		if($accessinclus != ""){
			//$pdf->setXY($ptablex,$ly);
			//$pdf->setfont('arial','B',10);
			//$pdf->cell(140,5,"Accessoires inclus",0,0,'L',0);
			//$ly+=4;

			$pdf->setXY($ptablex,$ly);
			$pdf->setfont('arial','B',8);
			$pdf->cell($col1,4,"Accessoires inclus",0,0,'L',0);
			$pdf->setfont('arial','',8);
	    	$pdf->cell($col2,4,$accessinclus,0,0,'L',0);
			$ly+=4;
		}

		//origine de fabrication
		if($article['paysfabrication'] !=''){
			/*$pdf->setXY($ptablex,$ly);
			$pdf->setfont('arial','B',10);
			$pdf->cell(140,5,"Origine de fabrication",0,0,'L',0);
			$ly+=4;*/

			
			$pdf->setXY($ptablex,$ly);
			$pdf->setfont('arial','B',8);
			$pdf->cell($col1,4,"Origine de fabrication",0,0,'L',0);
			$pdf->setfont('arial','',8);
	    	$pdf->cell($col2,4,$template->translate("/article/origines/".$article['paysfabrication'],"F"),0,0,'L',0);
	    	$ly+=4;
		}
		
	}

//--------------------
if(!is_null($liste_caractvalid) && $DB_site->num_rows($liste_caractvalid) > 0) {
	//Page des options
	     $pdf->SetAutoPageBreak(false,5);
	     optionhead($pdf,$article,$marque);

	     $w=46;
	     $py=60;
	     $col2=100;
	     $col1=40;
	     $cellw=$col1+$col2;
	     $ptablex=64;
	     $ly=35;

	     $logoimg=$rootpath."configurations/".$host."/images/marques/".$marque['marqueid'].".".$marque['image'];
	     if(file_exists($logoimg)){
	     	$size=getimagesize($logoimg);
	     	$logo_w=40;
	     	if($size[1]*$logo_w/$size[0]>12) $logo_w=round($size[0]*12/$size[1]);
	     	$pdf->image($logoimg,5+(56-$logo_w)/2,$ly,$logo_w);
	     }
	     $pdf->settextcolor(48,40,37);
	     $pdf->setfont('arial','',8);
	     $pdf->setXY(5,$ly+14);
	     $ly+=14;
	     $pdf->multicell(56,3,html_entity_decode($article["libelle".$template->langue],ENT_COMPAT,"ISO-8859-15"),0,'L',0);
	     $height = $pdf->nblines(56,html_entity_decode($article["libelle".$template->langue],ENT_COMPAT,"ISO-8859-15"));
	     $pdf->textColor($autocolor);
	     $pdf->setY($ly+1+($height*3));
	     $pdf->setX(5);
	     $pdf->Cell(56,3,$marque['libelle']." ".$article['gamme'.$template->langue]." ".$article['artcode'],0,0,'L');
	     $ly+=$height*3 + 1;

	     //$pdf->setY($ly);
	     //$pdf->ln(10);
	     $autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=".$article['artid']." AND visible=1 AND (langue='' OR langue='".$template->langue."')");
	     $autreid="";
	     if($autreimg!="") $autreid="_".$autreimg['articlephotoid'];
	     $image=$rootpath."configurations/".$host."/images/produits/br/".$article['artid'].$autreid.".".$article['image'];
	     if(file_exists($image)){
	     	$size=getimagesize($image);
	     	if($size[1]>392) $w=intval($size[0]*(392/$size[1])*0.25);
	     	if($w>46) $w=46;
	     	$pdf->image($image,7,$ly+5,$w);
	     } 

	     $ly=35;
	     foreach($liste_caractvalid as $caractvalid){

	     	//n'affiche que les caracteristiques choisies
	     	$verif_carac = $DB_site->query_first("SELECT * FROM `lignefacturecaracteristique` WHERE `lignefactureid` = ".$ligne." AND caractvalid = ".$caractvalid['caractvalid']);
	     	if(!is_null($verif_carac)){

    			//affiche toutes les options de la caracteristique
    				//si lot dissocié
    			if($dissoc==true && !is_null($req_lot) && $DB_site->num_rows($req_lot)>0){
    				$prix_opt_lot=array();
    				$liste_artid=[];
    				while($art_lot=$DB_site->fetch_array($req_lot)){
    					$liste_artid[]=$art_lot['artid'];
	     			}
	     			$liste_artid=implode("','",$liste_artid);
	     			//on va récupérer chaque prix des options du même article et les additionner selon la quantité.
	     			$liste_options = $DB_site->query("SELECT ac.artid, lf.qte, cv.caractvalid, cv.libelle".$template->langue.", ac.differenceprix, ac.differenceprixht, cv.prix_fixe, cv.pvttc, cv.pvht FROM caracteristiquevaleur cv INNER JOIN article_caractval ac ON ac.caractvalid = cv.caractvalid INNER JOIN lignefacture lf ON lf.artid = ac.artid WHERE cv.caractid = ".$caractvalid['caractid']." AND ac.artid IN ('".$liste_artid."') AND lf.lignefacturedissid = ".$ligne." ORDER BY cv.position ASC");
	     			while($liste_option=$DB_site->fetch_array($liste_options)){
    					$prix_opt_lot[$liste_option['caractvalid']]['differenceprix']+=unserialize($liste_option['differenceprix'.($template->pro?'ht':'')])['base']*$liste_option['qte'];
	     				$prix_opt_lot[$liste_option['caractvalid']]['pv']+=$liste_option['pv'.($template->pro?'ht':'ttc')]*$liste_option['qte'];
	     			}	
	     		}
	     		$liste_options = $DB_site->query("SELECT ac.artid, cv.caractvalid, cv.libelle".$template->langue.", ac.differenceprix, ac.differenceprixht, cv.prix_fixe, cv.pvttc, cv.pvht FROM caracteristiquevaleur cv INNER JOIN article_caractval ac ON ac.caractvalid = cv.caractvalid WHERE cv.caractid = ".$caractvalid['caractid']." AND ac.artid = ".$article['artid']. " ORDER BY cv.position ASC");
	     		
	     		$nb_rows = $DB_site->num_rows($liste_options);
	     		$ly+=5;
	     		$ly_old=$ly;
	     		$pdf->setfont('arial','',8);
	     		$x_cell=0;
	     		$nb_option=1;

		//nouvelle page si trop d'options
	     	$nbrow = $nbrows / 5;
	     	$nbrow = ceil($nbrow);
	     	if($ly+$ly5*$nb_row>260){
	     		optionhead($pdf,$article,$marque);
	     		$ly=35;
	     		$ly5=5;
	     		$ly_old=$ly;
	     		$pdf->setfont('arial','',8);
	     		$x_cell=0;
	     		$nb_option=1;

	     		$logoimg=$rootpath."configurations/".$host."/images/marques/".$marque['marqueid'].".".$marque['image'];
	     		if(file_exists($logoimg)){
	     			$size=getimagesize($logoimg);
	     			$logo_w=40;
	     			if($size[1]*$logo_w/$size[0]>12) $logo_w=round($size[0]*12/$size[1]);
	     			$pdf->image($logoimg,5+(56-$logo_w)/2,$ly,$logo_w);
	     		}
	     		$pdf->settextcolor(48,40,37);
	     		$pdf->setfont('arial','',8);
	     		$pdf->setXY(5,$ly+14);
	     		$pdf->multicell(56,3,html_entity_decode($article["libelle".$template->langue],ENT_COMPAT,"ISO-8859-15"),0,'L',0);
	     		$pdf->ln(8);  

	     		$autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=".$article['artid']." AND visible=1 AND (langue='' OR langue='".$template->langue."')");
	     		$autreid="";
	     		if($autreimg!="") $autreid="_".$autreimg['articlephotoid'];
	     		$image=$rootpath."configurations/".$host."/images/produits/br/".$article['artid'].$autreid.".".$article['image'];
	     		if(file_exists($image)){
	     			$size=getimagesize($image);
	     			if($size[1]>392) $w=intval($size[0]*(392/$size[1])*0.25);
	     			if($w>46) $w=46;
	     			$pdf->image($image,10,$py,$w);
	     		}
	     		$ly+=5; 
	     	}

		///libelle de la caracteristique
	     	
	     	$pdf->setfont('arial','B',8);
	     	$pdf->setXY($ptablex,$ly-5);
	     	$pdf->cell($cellw,5,$caractvalid['libellecarac'],0,0,'C',0);
	     	$pos_lib_carac = $pdf->getY();

	     	$compare_taille=array();
	     	while($option=$DB_site->fetch_array($liste_options)){

			//image option sélectionnée
	     		if($caractvalid['caractvalid'] == $option['caractvalid']){
	     			$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	     			$pdf->Image($img,$ptablex-1+$x_cell-1,$ly,4,'PNG');
	     		}

		    //libelle option
	     		$pdf->setXY($ptablex-2+$x_cell,$ly);
	     		$pdf->setfont('arial','',7);
	     		$pdf->multicell(24,2,$option['libelle'.$template->langue],0,'C',0);
	     		$gety=$pdf->getY();

		    //prix de l'option
	     		//si lot pas dissocié
	     		if($dissoc==false && !is_null($req_lot) && $DB_site->num_rows($req_lot) > 0){
	     			$nndissoc_caracts = $DB_site->query_first("SELECT differenceprix FROM lignefacturecaracteristique lc, caracteristiquevaleur cv WHERE lc.caractvalid=cv.caractvalid AND lc.lignefactureid = '$ligne' AND lc.caractvalid = ".$option['caractvalid']." ORDER BY libelle_caract");
	     			$prixopt=$nndissoc_caracts['differenceprix'];
	     			$template->pro?$prix = "+".$template->price_format($prixopt*$caractvalid['qte'],2,false,false,'',true):$prix="+".$template->price_format($prixopt*$caractvalid['qte'],2,false);
	     		}
	     		//si lot dissocié
	     		elseif($dissoc==true && !empty($prix_opt_lot)){
	     			if($option['prix_fixe'] == '1'){
	     				$prixopt=$prix_opt_lot[$option['caractvalid']]['pv'];
	     			}
	     			else{
	     				$prixopt=$prix_opt_lot[$option['caractvalid']]['differenceprix'];
	     			}
	     			$template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
	     		}
	     		else{
	     			if($option['prix_fixe'] == '1'){
	     				$prixopt=$option['pv'.($template->pro?'ht':'ttc')];
	     			}
	     			else{
	     				$prixopt=unserialize($option['differenceprix'.($template->pro?'ht':'')])['base'];
	     			}
	     			$template->pro?$prix = "+".$template->price_format($prixopt*$caractvalid['qte'],2,false,false,'',true):$prix="+".$template->price_format($prixopt*$caractvalid['qte'],2,false);
	     		}
	     		
	     		
	     		if(!$afficheprixopt) {
	     			$txt="";
	     		}elseif($prixopt == 0){
	     			$txt="";
	     		}else{
	     			$txt=$prix;
	     		}
	     		$pdf->setfont('arial','I',7);
	     		$pdf->setXY($ptablex+$x_cell+5,$gety);
	     		$pdf->cell(10,4,$txt,0,0,'C');
	     		$pos_txt_prix=$pdf->getY()+($txt==""?1:4);

		    //image de l'option
	     		$caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$option['caractvalid'].".jpg";
	     		if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/templates/photo_non_dispo.jpg";
		    //if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/3331.jpg";
	     		$pdf->image($caractimg,$ptablex+$x_cell+5,$pos_txt_prix,10);

		    //savoir la hauteur de l'image
	     		$caractimg=imagecreatefromjpeg($caractimg);
	     		$image_scale=imagescale($caractimg,10);
	     		$image_scale=imagesy($image_scale);
	     		$h_image=$image_scale;

		    //ajoute toutes les hauteurs du libelle option + son prix, par ligne
	     		$compare_taille[]=$pos_txt_prix+$h_image;

		    //si fin de la liste d'options
	     		if($nb_option == $nb_rows){
	     			$ly=$ly+22+$h_image;
	     		}
		    //pour faire un retour à la ligne
	     		elseif($ptablex+$x_cell-2 >= $cellw){
	     			$x_cell=0;
		    	//prend la plus grande hauteur de compare_taille, pour faire un espace correct entre les lignes
	     			$max_compare=max($compare_taille);
	     			$ly=$max_compare+3;
	     			$compare_taille=array();
	     		}
		    //pour espacer les options entre elles, sur la même ligne
	     		else{
	     			$x_cell+=25;
	     		}
	     		$nb_option++;
	     	}

		//la ligne sous le libelle de la carac
	     	$pdf->setXY($ptablex,$pos_lib_carac+4);
	     	$pdf->cell($cellw,0.3,"",1,0);
	     	$ly-=5;
	     	$ly5+=10;
	     }
	}
}

footer($pdf);
/*------------*/
/// Page accessoires conseillés
$art_rangements=$DB_site->query("SELECT a.artid,a.artcode,a.libelle".$template->langue.",a.gamme".$template->langue." FROM article a INNER JOIN article_rangement ac ON(a.artid = ac.artid_rangement) INNER JOIN article_site a_s ON(a.artid=a_s.artid) WHERE ac.artid = '$article[artid]' and a_s.active = '1' AND a_s.site_code='".$facture['site_code']."' GROUP BY artid LIMIT 6");
	 $art_conseil=$DB_site->query("SELECT a.artid,a.artcode,a.libelle".$template->langue.",a.gamme".$template->langue.",a.commandable FROM article a INNER JOIN article_similaire ars ON(a.artid = ars.artid) INNER JOIN article_site a_s ON(a_s.artid=a.artid) WHERE ars.gid=(SELECT gid FROM article_similaire WHERE artid = '$article[artid]') AND ars.artid<>'$article[artid]' and a_s.active = '1' AND a_s.site_code='".$facture['site_code']."' GROUP BY artid LIMIT 6");
	 if($DB_site->num_rows($art_rangements)>0){

	 $pdf->addpage();
	 $pdf->SetFont('Arial','B',25);
         $pdf->Cell(100);
         $pdf->image($rootpath."configurations/".$host."/images/templates/".$template->site_code()."/".$template->langue."/logo".($template->pro?"_pro":"").".jpg",5,5,84,0,"JPG",$template->translate("/site/[site_code]/url"));
                
	 $pdf->textColor($autocolor);
	 
	 $pdf->setXY(80,7);
	 $pdf->setfont('arial','B',18);
	 $title=$template->translate("/devis/devis_n","F");
	 if(isset($_GET['proforma'])) $title=$template->translate("/devis/facture_proforma_n","F");
	 $pdf->cell(80,8,$title.$facture['facturid'],0,0,'C');
	 $pdf->ln(8);

	 $pdf->setXY(180,22);
	 $pdf->setfont('arial','',8);
	 $pdf->textColor("black");
	 $pdf->cell(20,4,$template->translate("/devis/page","F")." ".$pdf->PageNo(),0,0,'R');

	 $top=$py=30;

	 $pdf->image($rootpath."configurations/".$host."/images/marques/".$marque['marqueid'].".".$marque['image'],10,$py,27);
	 $py+=28;
	 $pdf->setXY(9,$py);
	 $pdf->setfont('arial','',8);
	 $pdf->textColor("black");
	 $lblheight=$pdf->nblines(45,$article['libelle'.$template->langue])*3;
	 $pdf->multicell(45,3,html_entity_decode($article['libelle'.$template->langue],ENT_COMPAT,"ISO-8859-15"),0,'L');
	 $py+=$lblheight;
	 $pdf->setXY(9,$py+1);
	 $pdf->textColor($autocolor);
	 $pdf->multicell(45,3,$marque['libelle']." ".$article['gamme'.$template->langue]." ".$article['artcode'],0,'L');

	 $autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=".$article['artid']." AND visible=1 AND (langue='' OR langue='".$template->langue."')");
	 $autreid="";
	 if($autreimg!="") $autreid="_".$autreimg['articlephotoid'];
	 $image=$rootpath."configurations/".$host."/images/produits/br/".$article['artid'].$autreid.".jpg";
	 $w=45;
	 if(file_exists($image)){
	      $size=getimagesize($image);
	      if($size[1]>300) $w=intval($size[0]*(300/$size[1])*0.25);
	      if($w>45) $w=45;
	      $imgheight=intval(($w/$size[0])*$size[1]);
	      $pdf->image($image,10,$py+7,$w);
	 }
	 $py=30;
	 
	 if($DB_site->num_rows($art_rangements)>0){

	      $pdf->setfont('arial','',12);
	      $pdf->fillColor($autocolor);
	      $pdf->textColor("white");
	      $pdf->setXY(58,$py);
	      $pdf->cell(142,5,$template->translate("/devis/accessoires_conseilles","F"),0,0,"L",1);
	      $py=$pdf->getY();
	 
	      $lx=58;
	      $ly=36;
	      $c=1;
	 
	      while($art=$DB_site->fetch_array($art_rangements)){
		  $m=$DB_site->query_first("SELECT * FROM marque m, article_marque am WHERE m.marqueid=am.marqueid AND am.artid='$art[artid]'");
		  $article_prices=article_prices($art['artid'],$template->tva_ref(),$template->site_code(),false);
		  
		  $pdf->setXY($lx,$ly);

		  $pdf->drawColor($autocolor);
		  $pdf->rect($lx,$ly,46,55,'D');
		  $autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=".$art['artid']." AND visible=1 AND (langue='' OR langue='".$template->langue."')");
		  $autreid="";
		  if($autreimg!="") $autreid="_".$autreimg['articlephotoid'];
		  $img=$rootpath."configurations/".$host."/images/produits/article/".$art['artid'].$autreid.".jpg";
		  if(file_exists($img)){
			  $size=getimagesize($img);
			  $w=intval(28*$size[0]/$size[1]);
			  if($w>44) $w=44;
			  $pdf->image($img,$lx+(46-$w)/2,$ly+1,$w);
			}
		  
		  $pdf->link($lx,$ly,46,55,$template->translate("/site/[site_code]/url").$template->mkurl("article",$article['artid'],$article)."#accessoires");
	      
		  $pdf->setfont('arial','',7);
	      
		  $pdf->textColor($autocolor);
		  $pdf->setXY($lx,$ly+42);
		  $pdf->multicell(46,3,$m['libelle']." ".$art['gamme'.$template->langue]." ".$art['artcode'],0,'C');
	      
		  $pdf->textColor("black");
		  $pdf->setXY($lx,$ly+33);
		  $lbl=html_entity_decode($art['libelle'.$template->langue],ENT_COMPAT,"ISO-8859-15");
		  $pdf->multicell(46,3,$lbl,0,'C');
	      
	      
		  $logoimg=$rootpath."configurations/".$host."/images/marques/".$m['marqueid'].".".$m['image'];
		  if(file_exists($logoimg)){
			  $size=getimagesize($logoimg);
			  $logo_w=16;
			  if($size[1]*$logo_w/$size[0]>6) $logo_w=round($size[0]*6/$size[1]);
			  $pdf->image($logoimg,$lx+2,$ly+48,$logo_w);
		  }
	      
		  $pdf->setXY($lx,$ly+51);
		  $pdf->setfont('arial','B',8);
		  $pdf->cell(46,4,$template->price_format($article_prices['prix'],2,false),0,0,'R');
	      
		  if(round($article_prices['prix'])!=round($article_prices['prixconseille'])){
		      $pdf->setXY($lx,$ly+47);
		      $pdf->setfont('arial','',8);
		      $prix=$template->price_format($article_prices['prixconseille'],2,false);
		      $w=$pdf->getstringwidth($prix);
		      $pdf->cell(46,4,$prix,0,0,'R');
		      $pdf->drawColor("black");
		      $pdf->line($lx+45-$w,$ly+49,$lx+45.5,$ly+49);
		  }
	      
		  $lx+=48;
		  $c++;
		  if($c>3){
		      $lx=58;
		      $ly+=56;
		      $c=1;
		  }
	      }
	      if($c==1) $ly-=58;

	      $py=150;
	 }
	}

footer($pdf);
/*------------*/
//Page des articles de même gamme

$art_conseil=$DB_site->query("SELECT a.artid,a.artcode,a.libelle".$template->langue.",a.gamme".$template->langue.",a.commandable FROM article a INNER JOIN article_similaire ars ON(a.artid = ars.artid) INNER JOIN article_site a_s ON(a_s.artid=a.artid) WHERE ars.gid=(SELECT gid FROM article_similaire WHERE artid = '$article[artid]') AND ars.artid<>'$article[artid]' and a_s.active = '1' AND a_s.site_code='".$facture['site_code']."' GROUP BY artid LIMIT 6");
if($DB_site->num_rows($art_conseil)>0){
	$pdf->addpage();
	$pdf->SetFont('Arial','B',25);
	$pdf->Cell(100);
	$pdf->image($rootpath."configurations/".$host."/images/templates/".$template->site_code()."/".$template->langue."/logo".($template->pro?"_pro":"").".jpg",5,5,84,0,"JPG",$template->translate("/site/[site_code]/url"));

	$pdf->textColor($autocolor);

	$pdf->setXY(80,7);
	$pdf->setfont('arial','B',18);
	$title=$template->translate("/devis/devis_n","F");
	if(isset($_GET['proforma'])) $title=$template->translate("/devis/facture_proforma_n","F");
	$pdf->cell(80,8,$title.$facture['facturid'],0,0,'C');
	$pdf->ln(8);

	$pdf->setXY(180,22);
	$pdf->setfont('arial','',8);
	$pdf->textColor("black");
	$pdf->cell(20,4,$template->translate("/devis/page","F")." ".$pdf->PageNo(),0,0,'R');

	$top=$py=30;

	$pdf->image($rootpath."configurations/".$host."/images/marques/".$marque['marqueid'].".".$marque['image'],10,$py,27);
	$py+=28;
	$pdf->setXY(9,$py);
	$pdf->setfont('arial','',8);
	$pdf->textColor("black");
	$lblheight=$pdf->nblines(45,$article['libelle'.$template->langue])*3;
	$pdf->multicell(45,3,html_entity_decode($article['libelle'.$template->langue],ENT_COMPAT,"ISO-8859-15"),0,'L');
	$py+=$lblheight;
	$pdf->setXY(9,$py+1);
	$pdf->textColor($autocolor);
	$pdf->multicell(45,3,$marque['libelle']." ".$article['gamme'.$template->langue]." ".$article['artcode'],0,'L');

	$autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=".$article['artid']." AND visible=1 AND (langue='' OR langue='".$template->langue."')");
	$autreid="";
	if($autreimg!="") $autreid="_".$autreimg['articlephotoid'];
	$image=$rootpath."configurations/".$host."/images/produits/br/".$article['artid'].$autreid.".jpg";
	$w=45;
	if(file_exists($image)){
		$size=getimagesize($image);
		if($size[1]>300) $w=intval($size[0]*(300/$size[1])*0.25);
		if($w>45) $w=45;
		$imgheight=intval(($w/$size[0])*$size[1]);
		$pdf->image($image,10,$py+7,$w);
	}

	$top=$py;
	$py=30;
	$pdf->setfont('arial','',12);
	$pdf->fillColor($autocolor);
	$pdf->textColor("white");
	$pdf->setXY(58,$py);
	$pdf->cell(142,5,$template->translate("/devis/autres_modeles","F"),0,0,"L",1);
	$ly=$py+6;
	$pdf->setfont('arial','',8);

	$lx=58;
	$c=1;

	while($art=$DB_site->fetch_array($art_conseil)){
		$m=$DB_site->query_first("SELECT * FROM marque m, article_marque am WHERE m.marqueid=am.marqueid AND am.artid='$art[artid]'");
		$art['marque']=$m['libelle'];
		$article_prices=false;
		if($art['commandable']=='1') $article_prices=article_prices($art['artid'],$template->tva_ref(),$template->site_code(),false);

		$pdf->setXY($lx,$ly);

		$pdf->drawColor($autocolor);
		$pdf->rect($lx,$ly,46,65,'D');
		$autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=".$art['artid']." AND visible=1 AND (langue='' OR langue='".$template->langue."')");
		$autreid="";
		if($autreimg!="") $autreid="_".$autreimg['articlephotoid'];
		$img=$rootpath."configurations/".$host."/images/produits/article/".$art['artid'].$autreid.".jpg";
		if(file_exists($image)){
			$size=getimagesize($img);
			$w=intval(39*$size[0]/$size[1]);
			if($w>46) $w=46;
			$pdf->image($img,$lx+(46-$w)/2,$ly+1,$w);
		}

		$pdf->link($lx,$ly,46,65,$template->translate("/site/[site_code]/url").$template->mkurl("article",$art['artid'],$art));

		$pdf->setfont('arial','',7);
		$pdf->textColor($autocolor);
		$pdf->setXY($lx,$ly+51);
		$pdf->multicell(46,3,$m['libelle']." ".$art['gamme'.$template->langue]." ".$art['artcode'],0,'C');

		$pdf->textColor("black");
		$pdf->setXY($lx,$ly+42);
		$lbl=html_entity_decode($art['libelle'.$template->langue],ENT_COMPAT,"ISO-8859-15");
		$pdf->multicell(46,3,Truncate($lbl , 90 , true),0,'C');

		$logoimg=$rootpath."configurations/".$host."/images/marques/".$m['marqueid'].".".$m['image'];
		if(file_exists($logoimg)){
			$size=getimagesize($logoimg);
			$logo_w=16;
			if($size[1]*$logo_w/$size[0]>6) $logo_w=round($size[0]*6/$size[1]);
			$pdf->image($logoimg,$lx+2,$ly+58,$logo_w);
		}

		$pdf->setXY($lx,$ly+61);
		$pdf->setfont('arial','B',8);
		if($article_prices != false){
			$pdf->cell(46,4,$template->price_format($article_prices['prix'],2,false),0,0,'R');

			if(round($article_prices['prix'])!=round($article_prices['prixconseille'])){
				$pdf->textColor("black");
				$pdf->setfont('arial','',8);
				$pdf->setXY($lx,$ly+57);
				$prix=$template->price_format($article_prices['prixconseille'],2,false);
				$w=$pdf->getstringwidth($prix);
				$pdf->cell(46,4,$prix,0,0,'R');
				$pdf->drawColor("black");
				$pdf->line($lx+45-$w,$ly+59,$lx+45.5,$ly+59);
			}
		}else{
			$pdf->cell(46,4,$template->translate("/article/vignette/prix_sur_demande","F"),0,0,'R');
		}

		$lx+=48;
		$c++;
		if($c>3){
			$lx=58;
			$ly+=67;
			$c=1;
		}
	}
	if($c==1) $ly-=50;
	     }

	footer($pdf);
	}

	function kerning($pdf,$text,$size){
		$x=$pdf->getx();
		$width=0;
		for($i=0;$i<strlen($text);$i++){
			$chr=substr($text,$i,1);
			$pdf->setx($x+$width);
			$pdf->cell(5,5,$chr);
			$width+=$pdf->getstringwidth($chr)+$size;
		}
	}

	function puce($pdf,$x,$y){
		$pdf->rect($x,$y+2.5,0.9,0.9,'FD');
	}

	function optionhead($pdf,$article,$marque){
		global $rootpath,$template,$facture,$ly,$autocolor,$host;

		$pdf->AddPage();
		$pdf->SetAutoPageBreak(false,5);

		$pdf->SetFont('Arial','B',25);
		$pdf->Cell(100);
		$pdf->image($rootpath."configurations/".$host."/images/templates/".$template->site_code()."/".$template->langue."/logo".($template->pro?"_pro":"").".jpg",5,5,84,0,"JPG",$template->translate("/site/[site_code]/url"));
		$pdf->textColor($autocolor);

		$pdf->setXY(80,7);
		$pdf->setfont('arial','B',18);
		$title=$template->translate("/devis/devis_n","F");
		if(isset($_GET['proforma'])) $title=$template->translate("/devis/facture_proforma_n","F");
		$pdf->cell(80,8,$title.$facture['facturid'],0,0,'C');

		$pdf->setfont('arial','',8);
		$pdf->textColor("black");
		$pdf->setXY(185,26);
		$pdf->cell(20,-8,"Page ".$pdf->PageNo(),0,0,'R');
		$pdf->SetXY(10,290);
		$img = $rootpath."configurations/".$host."/images/templates/validation.png";
		$pdf->Image($img,6,290,4,4,'PNG');
		$pdf->setfont('arial','',8);
		$pdf->textColor("black");
		$pdf->fillColor("white");
		$pdf->Cell(50,4,$template->translate("/devis/options_defaut"),0,0,'L',1);
		$pdf->SetXY(68,25);

		$pdf->setXY(64,30);
		$pdf->fillColor($autocolor);
		$pdf->textColor("white");
		$pdf->setfont('arial','B',10);      
		$pdf->cell(140,5,$template->translate("/devis/options","U")." ".$marque['libelle']." ".$article['gamme'.$template->langue]." ".$article['artcode']." - VOTRE CONFIGURATION",0,0,'C',1);
		$pdf->textColor("black");
		$pdf->setfont('arial','',8);
	}

	?>
