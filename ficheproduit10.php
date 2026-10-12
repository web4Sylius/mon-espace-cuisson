<?php

	 if($article['version_porte']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $pdf->cell($col1,4,$template->translate("/devis/version_porte","F"),0,0,'L',0);
	    $pdf->cell($col2,4,$template->translate($article['version_porte']),0,0,'L',0);
	    $ly+=4;
	 }
	 if($article['capacite_bouteilles']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $pdf->cell($col1,4,$template->translate("/devis/capacite","F"),0,0,'L',0);
	    $pdf->cell($col2,4,$article['capacite_bouteilles']." ".$template->translate($article['capacite_unite'])." (".$template->translate("/devis/mini","F")." $article[capacite_mini] - ".$template->translate("/devis/maxi","F")." $article[capacite_maxi])",0,0,'L',0);
	    $ly+=4;
	 }
	 if($article['coloris']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $pdf->cell($col1,4,$template->translate("/devis/coloris_produit","F"),0,0,'L',0);
	    $pdf->cell($col2,4,$template->translate($article['coloris']),0,0,'L',0);
	    $ly+=4;
	 }
	 if($article['coloris_porte']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $pdf->cell($col1,4,$template->translate("/devis/coloris_cadre","F"),0,0,'L',0);
	    $pdf->cell($col2,4,$template->translate($article['coloris_porte']),0,0,'L',0);
	    $ly+=4;
	 }

	 
	 $finition=$DB_site->query_first("SELECT nom".$template->langue." FROM recherche_finition INNER JOIN articlerecherche_finition USING(finitionid) WHERE artid=".$article['artid']);
	 if($finition[0]!=""){
	    $pdf->setXY($ptablex,$ly);
	    $pdf->cell($col1,4,$template->translate("/devis/niveau_gamme","F"),0,0,'L',0);
	    $pdf->cell($col2,4,$finition[0],0,0,'L',0);
	    $ly+=4;
	 }
	 $ly+=2;

         $pdf->setXY($ptablex,$ly);
	 $pdf->textColor($autocolor);
         $pdf->setfont('arial','B',10);
         $pdf->cell($col1+$col2,5,$template->translate("/devis/caracteristiques_principales","F"),0,0,'L',0);
	 $pdf->setfont('arial','',8);
         $pdf->textColor("black");
         $lheight=5;
	 $ly+=5;
   
	 if($article['nbr_temperature']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $txt=$template->translate($article['nbr_temperature']);
	    $hcol=$pdf->nblines($col2,$txt)*$lheight;
	    $pdf->cell($col1,$hcol,$template->translate("/devis/nombre_temperatures","F"),0);
	    $pdf->multicell($col2,$lheight,$txt,0,'',0);
	    $ly+=$hcol;
	 }
	 if($article['plage_temperature']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $txt=$template->translate($article['plage_temperature']);
	    $hcol=$pdf->nblines($col2,$txt)*$lheight;
	    $pdf->cell($col1,$hcol,$template->translate("/devis/plage_temperature","F"),0);
	    $pdf->multicell($col2,$lheight,$txt,0,'',0);
	    $ly+=$hcol;
	 }
	 
	 $req=$DB_site->query("SELECT qte,libelle FROM article_accessoire WHERE artid=".$article['artid']);
	 if($DB_site->num_rows($req)>0){
	    $pdf->setXY($ptablex,$ly);
	    $txt="";
	    
	    while($acc=$DB_site->fetch_array($req)){
		$txt.=$acc['qte']." ".$template->translate($acc['libelle'],"L")."\n";
	    }
	    $hcol=$DB_site->num_rows($req)*$lheight;
	    $pdf->cell($col1,$hcol,$template->translate("/devis/accessoires_inclus","F"),0);
	    $pdf->multicell($col2,$lheight,$txt,0,'',0);
	    $ly+=$hcol;
	 }
	 if($article['temperature_cave']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $txt=$template->translate($article['temperature_cave']);
	    $pdf->multicell($col1,$lheight,$template->translate("/devis/temperature_installation","F"),0);
	    $pdf->setXY($ptablex+$col1,$ly);
	    $pdf->multicell($col2,$lheight,$txt,0,'',0);
	    $ly+=$lheight;
	 }
	 if($article['energy_label']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $hcol=$lheight;
	    $pdf->cell($col1,$hcol,$template->translate("/devis/classe_energetique","F"),0);
	    $pdf->multicell($col2,$lheight,$article['energy_label'],0,'',0);
	    $ly+=$hcol;
	 }
	 $pdf->setXY($ptablex,$ly);
         $hcol=$pdf->nblines($col2,$article['dimensions'])*$lheight;
	 $pdf->cell($col1,$hcol,$template->translate("/devis/dimensions","F"),0);
         $pdf->multicell($col2,$lheight,sprintf($template->translate("/article/champs/dimensions_format"),$article['hauteur'],$article['largeur'],$article['profondeur']),0,'',0);
         $ly+=$hcol;
	 if($article['conso_kw']!=0){
	    $pdf->setXY($ptablex,$ly);
	    $txt=sprintf($template->translate("/devis/kw_24h_soit_ttc"),round($article['conso_kw'],2),round($article['conso_kw']*$prixkw*365)." €");
	    $hcol=$pdf->nblines($col2,$txt)*$lheight;
	    $pdf->cell($col1,$hcol,$template->translate("/devis/consommation_energie","F"),0);
	    $pdf->multicell($col2,$lheight,$txt,0,'',0);
	    $ly+=$hcol;
	 }
	 if($article['niveau_bruit']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $hcol=$pdf->nblines($col2,trim($article['niveau_bruit'])." dB")*$lheight;
	    $pdf->cell($col1,$hcol,$template->translate("/devis/niveau_bruit","F"),0);
	    $pdf->multicell($col2,$lheight,trim($article['niveau_bruit'])." dB",0,'',0);
	    $ly+=$hcol;
	 }
	 if($article['paysfabrication']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $pdf->cell($col1,$lheight,$template->translate("/devis/origine_fabrication","F"),0);
	    $pdf->multicell($col2,$lheight,$template->translate("/article/origines/".$article['paysfabrication']),0,'',0);
	    $ly+=$lheight;
	 }
	 $garantie=$DB_site->query_first("SELECT cv.libelle".$template->langue." AS libelle FROM caracteristiquevaleur cv INNER JOIN caracteristique c USING (caractid) INNER JOIN article_caractval ac USING(caractvalid) WHERE ac.artid=".$article['artid']." AND cv.caractid=22");
	 if($garantie['libelle']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $pdf->cell($col1,$hcol,$template->translate("/devis/garantie_constructeur_incluse","F"),0);
	    $hcol=$pdf->nblines($col2,stripslashes($garantie['libelle']))*$lheight;
	    $pdf->multicell($col2,$lheight,$garantie['libelle'],0,'',0);
	    $ly+=$hcol+3;
	 }

	 if($article['regulation'].$article['temperature'].$article['thermometre'].$article['thermometre'].$article['reglage'].$article['affichage']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $pdf->textColor($autocolor);
	    $pdf->setfont('arial','B',10);
	    $pdf->cell($col1+$col2,5,$template->translate("/devis/regulation","F"),0,0,'L',0);
	    $pdf->setfont('arial','',8);
	    $pdf->textColor("black");
	    $ly+=5;

	    if($article['regulation']!=""){
		$pdf->setXY($ptablex,$ly);
		$txt=$template->translate(stripslashes($article['regulation']));
		$hcol=$pdf->nblines($col2,$txt)*$lheight;
		$pdf->cell($col1,$hcol,$template->translate("/devis/regulation","F"),0);
		$pdf->multicell($col2,$lheight,$txt,0,'',0);
		$ly+=$hcol;
	    }
	    if($article['temperature']!=""){
		$pdf->setXY($ptablex,$ly);
		$txt=$template->translate(stripslashes($article['temperature']));
		$hcol=$pdf->nblines($col2,$txt)*$lheight;
		$pdf->cell($col1,$hcol,$template->translate("/devis/temperature","F"),0);
		$pdf->multicell($col2,$lheight,$txt,0,'',0);
		$ly+=$hcol;
	    }
	    if($article['thermometre']!=""){
		$pdf->setXY($ptablex,$ly);
		$txt=$template->translate($article['thermometre']);
		$hcol=$pdf->nblines($col2,$txt)*$lheight;
		$pdf->cell($col1,$hcol,$template->translate("/devis/thermometre","F"),0);
		$pdf->multicell($col2,$lheight,$txt,0,'',0);
		$ly+=$hcol;
	    }
	    if($article['reglage']!=""){
		$pdf->setXY($ptablex,$ly);
		$txt=$template->translate($article['reglage']);
		$hcol=$pdf->nblines($col2,$txt)*$lheight;
		$pdf->cell($col1,$hcol,$template->translate("/devis/reglage","F"),0);
		$pdf->multicell($col2,$lheight,$txt,0,'',0);
		$ly+=$hcol;
	    }
	    if($article['affichage']!=""){
		$pdf->setXY($ptablex,$ly);
		$txt=$template->translate($article['affichage']);
		$hcol=$pdf->nblines($col2,$txt)*$lheight;
		$pdf->cell($col1,$hcol,html_entity_decode($template->translate("/devis/affichage","F"),ENT_COMPAT,"ISO-8859-15"),0);
		$pdf->multicell($col2,$lheight,$txt,0,'',0);
		$ly+=$hcol;
	    }
	    $ly+=3;
	 }

	 if($article['hygrometrie'].$article['hygrometre'].$article['serrure'].$article['eclairage'].$article['modularite_interieure']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $pdf->textColor($autocolor);
	    $pdf->setfont('arial','B',10);
	    $pdf->cell($col1+$col2,5,$template->translate("/devis/equipement","F"),0,0,'L',0);
	    $pdf->setfont('arial','',8);
	    $pdf->textColor("black");
	    $ly+=5;

	    if($article['hygrometrie']!=""){
		$pdf->setXY($ptablex,$ly);
		$txt=$template->translate($article['hygrometrie']);
		$hcol=$pdf->nblines($col2,$txt)*$lheight;
		$pdf->cell($col1,$hcol,$template->translate("/devis/hygrometrie","F"),0);
		$pdf->multicell($col2,$lheight,$txt,0,'',0);
		$ly+=$hcol;
	    }
	    if($article['hygrometre']!=""){
		$pdf->setXY($ptablex,$ly);
		$txt=$template->translate($article['hygrometre']);
		$hcol=$pdf->nblines($col2,$txt)*$lheight;
		$pdf->cell($col1,$hcol,$template->translate("/devis/hygrometre","F"),0);
		$pdf->multicell($col2,$lheight,$txt,0,'',0);
		$ly+=$hcol;
	    }
	    if($article['serrure']!=""){
		$pdf->setXY($ptablex,$ly);
		$txt=$template->translate($article['serrure']);
		$hcol=$pdf->nblines($col2,$txt)*$lheight;
		$pdf->cell($col1,$hcol,$template->translate("/devis/serrure","F"),0);
		$pdf->multicell($col2,$lheight,$txt,0,'',0);
		$ly+=$hcol;
	    }
	    if($article['eclairage']!=""){
		$pdf->setXY($ptablex,$ly);
		$txt=$template->translate($article['eclairage']);
		$hcol=$pdf->nblines($col2,$txt)*$lheight;
		$pdf->cell($col1,$hcol,$template->translate("/devis/eclairage","F"),0);
		$pdf->multicell($col2,$lheight,$txt,0,'',0);
		$ly+=$hcol;
	    }
	    if($article['modularite_interieure']!=""){
		$pdf->setXY($ptablex,$ly);
		$txt=$template->translate($article['modularite_interieure']);
		$hcol=$pdf->nblines($col2,$txt)*$lheight;
		$pdf->cell($col1,$hcol,$template->translate("/devis/modularite_interieur","F"),0);
		$pdf->multicell($col2,$lheight,$txt,0,'',0);
		$ly+=$hcol;
	    }
	    $ly+=3;
	 }

	 if($article['production_froid'].$article['production_chaud'].$article['fonction_hiver_techno']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $pdf->textColor($autocolor);
	    $pdf->setfont('arial','B',10);
	    $pdf->cell($col1+$col2,5,$template->translate("/devis/technologie","F"),0,0,'L',0);
	    $pdf->setfont('arial','',8);
	    $pdf->textColor("black");
	    $ly+=5;
	 
	    if($article['production_froid']!=""){
		$pdf->setXY($ptablex,$ly);
		$txt=$template->translate($article['production_froid']);
		$hcol=$pdf->nblines($col2,$txt)*$lheight;
		$pdf->cell($col1,$hcol,$template->translate("/devis/production_froid","F"),0);
		$pdf->multicell($col2,$lheight,$txt,0,'',0);
		$ly+=$hcol;
	    }
	    if($article['production_chaud']!=""){
		$pdf->setXY($ptablex,$ly);
		$txt=$template->translate($article['production_chaud']);
		$hcol=$pdf->nblines($col2,$txt)*$lheight;
		$pdf->cell($col1,$hcol,$template->translate("/devis/production_chaud","F"),0);
		$pdf->multicell($col2,$lheight,$txt,0,'',0);
		$ly+=$hcol;
	    }
	    if($article['fonction_hiver_techno']!=""){
		$pdf->setXY($ptablex,$ly);
		$txt=$template->translate($article['fonction_hiver_techno']);
		$hcol=$pdf->nblines($col2,$txt)*$lheight;
		$pdf->cell($col1,$hcol,$template->translate("/devis/fonction_hiver","F"),0);
		$pdf->multicell($col2,$lheight,$txt,0,'',0);
		$ly+=$hcol;
	    }
	    if($article['gaz']!=""){
		$pdf->setXY($ptablex,$ly);
		$txt=$template->translate($article['gaz']);
		$hcol=$pdf->nblines($col2,$txt)*$lheight;
		$pdf->cell($col1,$hcol,$template->translate("/devis/type_gaz","F"),0);
		$pdf->multicell($col2,$lheight,$txt,0,'',0);
		$ly+=$hcol;
	    }
	    $ly+=5;
	 }
	 if($template->pro)
	 	$differenceprix = "differenceprixht";
	 else 
	 	$differenceprix = "differenceprix";

	 $rlogiciel=$rpresta=$rgarvisite=false;
	 $rcolor=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=1 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
	 $rcadre=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=2 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
// 	 $rgar=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=3 AND ac.artid=".$article['artid']);
	 $rporte=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=4 AND ac.artid=".$article['artid']);
	 $rinterieur=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=40 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
	 $advineo=0;
// 	 if($options==0){
	  $reclairage=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=24 AND ac.artid=".$article['artid']." ORDER BY position");
	  $rlogiciel=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=27 AND ac.artid=".$article['artid']." ORDER BY position");
	  $rpresta=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=28 AND ac.artid=".$article['artid']." ORDER BY position");
	  $rgarvisite=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=26 AND ac.artid=".$article['artid']." ORDER BY position");
	  $raqua=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=29 AND ac.artid=".$article['artid']." ORDER BY position");
	  $rperso=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=30 AND ac.artid=".$article['artid']." ORDER BY position");
	  $rnettoyage=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=31 AND ac.artid=".$article['artid']." ORDER BY position");
	  //type de groupe de froid
	  $rgroupefroid=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=50 AND ac.artid=".$article['artid']." ORDER BY position");
	  //couleur de la vitre
	  $rcolorvitre=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=13 AND ac.artid=".$article['artid']." ORDER BY position");
	  //serrure de porte
	  $rserrure=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=14 AND ac.artid=".$article['artid']." ORDER BY position");
	  //garantie constructeur
	  $rgarconst=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=22 AND ac.artid=".$article['artid']." ORDER BY position");
	  //option tropicalisation
	  $rtropicale=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=36 AND ac.artid=".$article['artid']." ORDER BY position");
	  ////systeme anti-buée
	  $rbuee=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=25 AND ac.artid=".$article['artid']." ORDER BY position");
	  //type de lumiere
	  $rtypelum=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=44 AND ac.artid=".$article['artid']." ORDER BY position"); 
	  //type de pieds
	  $rpied=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=42 AND ac.artid=".$article['artid']." ORDER BY position"); 
	  //type de pierre
	  $rpierre=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=45 AND ac.artid=".$article['artid']." ORDER BY position"); 
	  // type de verre
	  $rverre=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=49 AND ac.artid=".$article['artid']." ORDER BY position"); 

	  $options=$DB_site->num_rows($rcolor)+$DB_site->num_rows($rcadre)+$DB_site->num_rows($rporte)+$DB_site->num_rows($rinterieur)+$DB_site->num_rows($reclairage)+$DB_site->num_rows($raqua)+$DB_site->num_rows($rperso)+$DB_site->num_rows($rnettoyage)+$DB_site->num_rows($rgroupefroid)+$DB_site->num_rows($rcolorvitre)+$DB_site->num_rows($rserrure)+$DB_site->num_rows($rgarconst)+$DB_site->num_rows($rtropicale)+$DB_site->num_rows($rbuee)+$DB_site->num_rows($rtypelum)+$DB_site->num_rows($rpied)+$DB_site->num_rows($rpierre)+$DB_site->num_rows($rverre);
	  $advineo=$DB_site->num_rows($rlogiciel)+$DB_site->num_rows($rpresta)+$DB_site->num_rows($rgarvisite);
// 	 }
	  // Ajout d'une nouvelle page spécialement pour les options.
	  if($options + $advineo > 0){
		  $pdf->AddPage();
		  $pdf->SetAutoPageBreak(false,5);

		  $pdf->SetFont('Arial','B',25);
	      $pdf->Cell(100);
	      $pdf->image($rootpath."images/templates/".$template->site_code()."/".$template->langue."/logo2016".($template->pro?"_pro":"").".jpg",5,5,84,0,"JPG",$template->translate("/site/url"));
		  $pdf->textColor($autocolor);
		 
		  $pdf->setXY(80,7);
		  $pdf->setfont('arial','B',18);
		  $title=$template->translate("/devis/devis_n","F");
		  if(isset($_GET['proforma'])) $title=$template->translate("/devis/facture_proforma_n","F");
		  $pdf->cell(80,8,$title.$facture['facturid'],0,0,'C');

		 $pdf->setfont('arial','',8);
		 $pdf->textColor("black");
		 $pdf->setXY(185,26);
		 $pdf->cell(20,8,"Page ".$pdf->page,0,0,'R');
		 $ly=$pdf->getY();

		 $logoimg=$rootpath."configurations/".$host."/images/marques/".$marque['marqueid'].".".$marque['image'];
		 if(file_exists($logoimg)){
			 $size=getimagesize($logoimg);
			 $logo_w=40;
			 if($size[1]*$logo_w/$size[0]>12) $logo_w=round($size[0]*12/$size[1]);
			 $pdf->image($logoimg,5+(56-$logo_w)/2,$ly,$logo_w);
		 }
	     $pdf->settextcolor(48,40,37);
		 $pdf->setfont('arial','',8);
		 $pdf->setXY(5,$ly+30);
		 $pdf->multicell(56,3,html_entity_decode($article["libelle".$template->langue],ENT_COMPAT,"ISO-8859-15"),0,'L',0);
	     $pdf->ln(8);  
	     $autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=".$article['artid']." AND visible=1 AND (langue='' OR langue='".$template->langue."')");
		 $autreid="";
		 if($autreimg!="") $autreid="_".$autreimg['articlephotoid'];
		 $image=$rootpath."configurations/".$host."/images/produits/br/".$article['artid'].$autreid.".".$article['image'];
	     $w=46;
		 $py=48;
		 	if(file_exists($image)){
		         $size=getimagesize($image);
		         if($size[1]>392) $w=intval($size[0]*(392/$size[1])*0.25);
		         if($w>46) $w=46;
		         $pdf->image($image,10,$py,$w);
	        }  
	     $photos=$DB_site->query("SELECT * FROM articlephoto WHERE artid=".$article['artid']." AND langue IN ('','".$template->langue."') AND visible=0 ORDER BY position LIMIT 3");
		 while($photo=$DB_site->fetch_array($photos)){
		      $py+=($w*$size[1]/$size[0])+5;
		      $image=$rootpath."configurations/".$host."/images/produits/br/".$article['artid']."_".$photo['articlephotoid'].".".$photo['image'];
		      if(file_exists($image)){
			      $size=getimagesize($image);
			      $imgbottom=$py+($w*$size[1]/$size[0]);
			      if($imgbottom<280) $pdf->image($image,10,$py,$w);
			  }
		 }
		 
		$ptablex=64;

		$pdf->setXY($ptablex,$ly+12);
	    $pdf->fillColor($autocolor);
		$pdf->textColor("white");
	    $pdf->setfont('arial','B',10);      
		$pdf->cell($col1+$col2,5,$template->translate("/devis/options","U")." ".$marque['libelle']." ".$article['gamme'.$template->langue]." ".$article['artcode'],0,0,'C',1);
		$ly+=17;
	}


	 /*if($options+$advineo>0){
	    $pdf->fillColor($autocolor);
	    $pdf->textColor("white");
	    $pdf->setXY($ptablex,$ly+12);
	    $pdf->cell($col1+$col2,4,$template->translate("/devis/options_disponibles","F"),0,0,'C',1);
	    $ly+=17;
	  }*******************MIS EN COMMENTAIRE PAR ARIEL 26/03/2018  c'est le badeau options disponibles*/


	  /*$sql = "SELECT caractid, libelle".$template->langue." as libelle FROM caracteristique";
	  $caract = $DB_site->query($sql);
	  while($caracts = $DB_site->fetch_array($caract)){
	  	$sql = "SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid='".$caracts[0]."' AND ac.artid=".$article['artid']." ORDER BY position";
	  	$c = $DB_site->query($sql);
	  	if($DB_site->num_rows($c)>0){
	  		while($row = $DB_site->fetch_array($c)){
	  		
	  		}
	  	}
	  }*/

  
	 $cellw=$col1+$col2;
	 $topoptions=$ly;
	 $pdf->textColor("black");
	 $pdf->drawColor("black");
	 
	 if($DB_site->num_rows($rcolor)>0){
	 	$nbrow = $DB_site->num_rows($rcolor);
	 	$nbrow = $nbrow / 7;
	 	$nbrow = ceil($nbrow);
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$template->translate("/devis/coloris_produit","F"),0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,22*$nbrow,"",1);
		
		$x=$ptablex+1;
		$count =1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rcolor)){
			if($count > 7){ //on veut mettre 7 couleurs par lignes
				$count = 1;
				$ly+=22;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+10,$ly+6,10);
		    $pdf->setXY($x+6,$ly);
		    $pdf->multicell(18,3,$color['libelle'],0,'C',0);
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $prixopt==0?$txt=$template->translate("/devis/offert","F"):$txt="+".$template->price_format($prixopt,2,false);
		    $pdf->setXY($x+10,$ly+16);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    $x+=18;
		    $count++;
		}
		$ly+=22;
		$pdf->setfont('arial','',8);
	 }

	 if($DB_site->num_rows($rinterieur)>0){
	 	$nbrow = $DB_site->num_rows($rinterieur);
	 	$nbrow = $nbrow / 7;
	 	$nbrow = ceil($nbrow);
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$template->translate("/devis/coloris_interieur","F"),0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,22*$nbrow,"",1);
		
		$x=$ptablex+1;
		$count =1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rinterieur)){
			if($count > 7){
				$count = 1;
				$ly+=22;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+10,$ly+6,10);
		    $pdf->setXY($x+6,$ly);
		    $pdf->multicell(18,3,$color['libelle'],0,'C',0);
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $prixopt==0?$txt=$template->translate("/devis/offert","F"):$txt="+".$template->price_format($prixopt,2,false);
		    $pdf->setXY($x+10,$ly+16);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    $x+=18;
		    $count++;

		}
		$ly+=22;
		$pdf->setfont('arial','',8);
	 }
	 
	 /*$pdf->setXY($ptablex,$ly);
	 $hcol=$DB_site->num_rows($rgar)*7;
	 if($DB_site->num_rows($rgar)>0){
	      $pdf->setfillcolor(131,126,124);
	      $pdf->settextcolor(255);
	      $pdf->multicell($cellw,5,$template->translate("/devis/garantie_serenite_complementaire","F"),1,'C',1);
	      $ly+=5;
	      $pdf->setfillcolor(100);
	      $pdf->settextcolor(0);
	      while($caract=$DB_site->fetch_array($rgar)){
		  $txtgar.=stripslashes($caract['libelle'.$template->langue])."\n";
		  $pdf->setXY($ptablex,$ly);
		  $hcell=7;
		  $nbline=$pdf->nblines($cellw-15,$caract['libelle'.$template->langue]);
		  $pdf->multicell($cellw-15,($hcell/$nbline),stripslashes($caract['libelle'.$template->langue]),1,'L');
		  $pdf->setXY($ptablex+$cellw-15,$ly);
		  $caract['differenceprix']==0?$txt=$template->translate("/devis/offert","F"):$txt="+".number_format($caract['differenceprix'],2,',','')." €";
		  $pdf->multicell(15,$hcell,$txt,1,'C',0);
		  $ly+=$hcell;
	      }
	      $ly+=1;
	 }*/

	 $lx=10;
	 $pdf->setXY($ptablex,$ly);
	 $hcol=$DB_site->num_rows($rgarvisite)*7;
	 if($rgarvisite && $DB_site->num_rows($rgarvisite)>0){
	      $pdf->setfont('arial','B',8);
	      $pdf->multicell($cellw,5,$template->translate("/devis/garantie_complementaire_visite","F"),0,'C',0);
	      $ly+=5;
	      $pdf->setfont('arial','',8);

	      while($caract=$DB_site->fetch_array($rgarvisite)){
		  $txtgar.=stripslashes($caract['libelle'.$template->langue])."\n";
		  $pdf->setXY($ptablex,$ly);
		  $hcell=7;
		  $nbline=$pdf->nblines($cellw-17,$caract['libelle'.$template->langue]);
		  $pdf->multicell($cellw-17,($hcell/$nbline),stripslashes($caract['libelle'.$template->langue]),1,'L');
		  $pdf->setXY($ptablex+$cellw-17,$ly);
		  $caract['differenceprix']=unserialize($caract[$differenceprix]);
		  $prixopt=$caract['differenceprix'][$site_code];
		  $prixopt==0?$txt=$template->translate("/devis/offert","F"):$txt="+".$template->price_format($prixopt,2,false);
		  $pdf->multicell(17,$hcell,$txt,1,'C',0);
		  $ly+=$hcell;
	      }
	      $ly+=2;
	 }

	 if($raqua && $DB_site->num_rows($raqua)>0){
	      $pdf->setXY($ptablex,$ly);
	      $pdf->setfont('arial','B',8);
	      $pdf->multicell($cellw,5,$template->translate("/devis/finition_aquarium","F"),0,'C',0);
	      $ly+=5;
	      $pdf->setfont('arial','',8);
	      while($caract=$DB_site->fetch_array($raqua)){
		  $txtgar.=stripslashes($caract['libelle'.$template->langue])."\n";
		  $pdf->setXY($ptablex,$ly);
		  $hcell=7;
		  $nbline=$pdf->nblines($cellw-17,$caract['libelle'.$template->langue]);
		  $pdf->multicell($cellw-17,($hcell/$nbline),stripslashes($caract['libelle'.$template->langue]),1,'L');
		  $pdf->setXY($ptablex+$cellw-17,$ly);
		  $caract['differenceprix']=unserialize($caract[$differenceprix]);
		  $prixopt=$caract['differenceprix'][$site_code];
		  $prixopt==0?$txt=$template->translate("/devis/inclus","F"):$txt="+".$template->price_format($prixopt,2,false);
		  $pdf->multicell(17,$hcell,$txt,1,'C',0);
		  $ly+=$hcell;
	      }
	      $ly+=1;
	 }
	 

	 $lx=108;


	 if($DB_site->num_rows($rcadre)>0){
	 	$nbrow = $DB_site->num_rows($rcadre);
	 	$nbrow = $nbrow / 7;
	 	$nbrow = ceil($nbrow);
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$template->translate("/devis/coloris_cadre","F"),0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,22*$nbrow,"",1);
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rcadre)){
			if($count > 7){
				$count = 1;
				$ly+=22;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+10,$ly+6,10);
		    $pdf->setXY($x+6,$ly);
		    $pdf->multicell(18,3,$color['libelle'],0,'C',0);
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $prixopt==0?$txt=$template->translate("/devis/offert","F"):$txt="+".$template->price_format($prixopt,2,false);
		    $pdf->setXY($x+10,$ly+16);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    $x+=18;
		    $count++;
		}
		$ly+=22;
		$pdf->setfont('arial','',8);
	 }

	 if($DB_site->num_rows($rporte)>0){
	      $pdf->setXY($ptablex,$ly);
	      $pdf->setfont('arial','B',8);
	      $pdf->cell($cellw,5,$template->translate("/devis/sens_porte","F"),0,0,'C',0);
	      $ly+=5;
	      $pdf->setfont('arial','',8);

	      while($sens=$DB_site->fetch_array($rporte)){
		  $pdf->setXY($ptablex,$ly);
		  $pdf->cell($cellw,5,$sens['libelle'.$template->langue],1);
		  $pdf->setX($ptablex+$cellw-15);
		  $sens['differenceprix']=unserialize($sens['$differenceprix']);
		  $prixopt=$sens['differenceprix'][$site_code];
		  $pdf->cell(15,5,"+".$template->price_format($prixopt,2,false),1,0,'C',0);
		  $ly+=5;
	      }
	 }

	 if($rlogiciel && $DB_site->num_rows($rlogiciel)>0){
	      $pdf->setXY($ptablex,$ly);
	      $pdf->setfont('arial','B',8);
	      $pdf->cell($cellw,5,$template->translate("/devis/version_distributeur","F"),0,0,'C',0);
	      $ly+=5;
	      $pdf->setfont('arial','',8);
	      
	      while($logiciel=$DB_site->fetch_array($rlogiciel)){
		  $pdf->setXY($ptablex,$ly);
		  $nbline=$pdf->nblines($cellw-17,$logiciel['libelle'.$template->langue]);
		  $pdf->multicell($cellw-17,7/$nbline,$logiciel['libelle'.$template->langue],1);
		  $pdf->setXY($ptablex+$cellw-17,$ly);
		  $logiciel['differenceprix']=unserialize($logiciel[$differenceprix]);
		  $prixopt=$logiciel['differenceprix'][$site_code];
		  $pdf->multicell(17,7,($prixopt==0?$template->translate("/devis/inclus","F"):"+".$template->price_format($prixopt,2,false)),1,0,'C',0);
		  $ly+=7;
	      }
	      $ly+=2;
	 }

	if($rperso && $DB_site->num_rows($rperso)>0){
	      $pdf->setXY($ptablex,$ly);
	      $pdf->setfont('arial','B',8);
	      $pdf->cell($cellw,5,$template->translate("/devis/personnalisation_distributeur","F"),0,0,'C',0);
	      $ly+=5;
	      $pdf->setfont('arial','',8);
	      
	      while($perso=$DB_site->fetch_array($rperso)){
		  $pdf->setXY($ptablex,$ly);
		  $nbline=$pdf->nblines($cellw-17,$perso['libelle'.$template->langue]);
		  $pdf->multicell($cellw-17,7/$nbline,$perso['libelle'.$template->langue],1);
		  $pdf->setXY($ptablex+$cellw-17,$ly);
		  $perso['differenceprix']=unserialize($perso[$differenceprix]);
		  $prixopt=$perso['differenceprix'][$site_code];
		  $pdf->multicell(17,7,($prixopt==0?$template->translate("/devis/inclus","F"):"+".$template->price_format($prixopt,2,false)),1,0,'C',0);
		  $ly+=7;
	      }
	      $ly+=2;
	 }
	 
	 if($reclairage && $DB_site->num_rows($reclairage)>0){
	      $pdf->setXY($ptablex,$ly);
	      $pdf->setfont('arial','B',8);
	      $pdf->cell($cellw,5,$template->translate("/devis/eclairage","F"),0,0,'C',0);
	      $ly+=5;
	      $pdf->setfont('arial','',8);
	      
	      while($perso=$DB_site->fetch_array($reclairage)){
		  $pdf->setXY($ptablex,$ly);
		  $nbline=$pdf->nblines($cellw-17,$perso['libelle'.$template->langue]);
		  $pdf->multicell($cellw-17,7/$nbline,$perso['libelle'.$template->langue],1);
		  $pdf->setXY($ptablex+$cellw-17,$ly);
		  $perso['differenceprix']=unserialize($perso[$differenceprix]);
		  $prixopt=$perso['differenceprix'][$site_code];
		  $pdf->multicell(17,7,($prixopt==0?$template->translate("/devis/inclus","F"):"+".$template->price_format($prixopt,2,false)),1,0,'C',0);
		  $ly+=7;
	      }
	      $ly+=2;
	 }

	/*if($rpresta && $DB_site->num_rows($rpresta)>0){
	      $pdf->setXY($ptablex,$ly);
	      $pdf->settextcolor(255);
	      $pdf->setfillcolor(131,126,124);
	      $pdf->cell($cellw,5,$template->translate("/devis/prestation_complementaire","F"),1,0,'C',1);
	      $ly+=5;
	      $pdf->settextcolor(0);
	      while($presta=$DB_site->fetch_array($rpresta)){
		  $pdf->setXY($ptablex,$ly);
		  $nbline=$pdf->nblines($cellw-17,$presta['libelle'.$template->langue]);
		  $pdf->multicell($cellw-17,7/$nbline,$presta['libelle'.$template->langue],1);
		  $pdf->setXY($ptablex+$cellw-17,$ly);
		  $pdf->multicell(17,7,($presta['differenceprix']==0?$template->translate("/devis/offert"):"+".number_format($presta['differenceprix'],2,',','')."€"),1,0,'C',0);
		  $ly+=7;
	      }
	      $ly+=2;
	 }*/

	 if($rnettoyage && $DB_site->num_rows($rnettoyage)>0){
	      $pdf->setXY($ptablex,$ly);
	      $pdf->setfont('arial','B',8);
	      
	      $pdf->cell($cellw,5,$template->translate("/devis/nettoyage_auto","F"),0,0,'C',0);
	      $ly+=5;
	      $pdf->setfont('arial','',8);
	      
	      while($nett=$DB_site->fetch_array($rnettoyage)){
		  	$pdf->setXY($ptablex,$ly);
		  	$nbline=$pdf->nblines($cellw-17,$nett['libelle'.$template->langue]);
		  	$pdf->multicell($cellw-17,7/$nbline,$nett['libelle'.$template->langue],1);
		  	$pdf->setXY($ptablex+$cellw-17,$ly);
		  	$nett['differenceprix']=unserialize($nett[$differenceprix]);
		  	$prixopt=$nett['differenceprix'][$site_code];
		  	$pdf->multicell(17,7,($prixopt==0?$template->translate("/devis/inclus","F"):"+".$template->price_format($prixopt,2,false)),1,0,'C',0);
		  	$ly+=7;
	      }
	 }

	 if($rgroupefroid && $DB_site->num_rows($rgroupefroid)>0){
	 	$pdf->setXY($ptablex,$ly);
	    $pdf->setfont('arial','B',8);

	    $pdf->cell($cellw,5,$template->translate("/devis/groupe_froid","F"),0,0,'C',0);
	    $ly+=5;
	    $pdf->setfont('arial','',8);
	    while($groupe=$DB_site->fetch_array($rgroupefroid)){
	    	$pdf->setXY($ptablex,$ly);
		  	$nbline=$pdf->nblines($cellw-17,$groupe['libelle'.$template->langue]);
		  	$pdf->multicell($cellw-17,7/$nbline,$groupe['libelle'.$template->langue],1);
		  	$pdf->setXY($ptablex+$cellw-17,$ly);
		  	$groupe['differenceprix']=unserialize($groupe[$differenceprix]);
		  	$prixopt=$groupe['differenceprix'][$site_code];
		  	$pdf->multicell(17,7,($prixopt==0?$template->translate("/devis/inclus","F"):"+".$template->price_format($prixopt,2,false)),1,0,'C',0);
		  	$ly+=7;

	    }
	 }
	 //couleur de la vitre caractid 13
	 if($rcolorvitre && $DB_site->num_rows($rcolorvitre)>0){
	 	$pdf->setXY($ptablex,$ly);
	    $pdf->setfont('arial','B',8);

	    $pdf->cell($cellw,5,$template->translate("/devis/coloris_vitre","F"),0,0,'C',0);
	    $ly+=5;
	    $pdf->setfont('arial','',8);
	    while($vitre=$DB_site->fetch_array($rcolorvitre)){
	    	$pdf->setXY($ptablex,$ly);
		  	$nbline=$pdf->nblines($cellw-17,$vitre['libelle'.$template->langue]);
		  	$pdf->multicell($cellw-17,7/$nbline,$vitre['libelle'.$template->langue],1);
		  	$pdf->setXY($ptablex+$cellw-17,$ly);
		  	$vitre['differenceprix']=unserialize($vitre[$differenceprix]);
		  	$prixopt=$vitre['differenceprix'][$site_code];
		  	$pdf->multicell(17,7,($prixopt==0?$template->translate("/devis/inclus","F"):"+".$template->price_format($prixopt,2,false)),1,0,'C',0);
		  	$ly+=7;

	    }
	 }
	 //serrure porte caractid 14
	 if($rserrure && $DB_site->num_rows($rserrure)>0){
	 	$pdf->setXY($ptablex,$ly);
	    $pdf->setfont('arial','B',8);

	    $pdf->cell($cellw,5,$template->translate("/devis/serrure_porte","F"),0,0,'C',0);
	    $ly+=5;
	    $pdf->setfont('arial','',8);
	    while($serr=$DB_site->fetch_array($rserrure)){
	    	$pdf->setXY($ptablex,$ly);
		  	$nbline=$pdf->nblines($cellw-17,$serr['libelle'.$template->langue]);
		  	$pdf->multicell($cellw-17,7/$nbline,$serr['libelle'.$template->langue],1);
		  	$pdf->setXY($ptablex+$cellw-17,$ly);
		  	$serr['differenceprix']=unserialize($serr[$differenceprix]);
		  	$prixopt=$serr['differenceprix'][$site_code];
		  	$pdf->multicell(17,7,($prixopt==0?$template->translate("/devis/inclus","F"):"+".$template->price_format($prixopt,2,false)),1,0,'C',0);
		  	$ly+=7;

	    }
	 }
	 //prestation complémentaire
	 if($rgarconst && $DB_site->num_rows($rgarconst)>0){
	 	$pdf->setXY($ptablex,$ly);
	    $pdf->setfont('arial','B',8);

	    $pdf->cell($cellw,5,$template->translate("/devis/garantie_constructeur","F"),0,0,'C',0);
	    $ly+=5;
	    $pdf->setfont('arial','',8);
	    while($gar=$DB_site->fetch_array($rgarconst)){
	    	$pdf->setXY($ptablex,$ly);
		  	$nbline=$pdf->nblines($cellw-17,$gar['libelle'.$template->langue]);
		  	$pdf->multicell($cellw-17,7/$nbline,$gar['libelle'.$template->langue],1);
		  	$pdf->setXY($ptablex+$cellw-17,$ly);
		  	$gar['differenceprix']=unserialize($gar[$differenceprix]);
		  	$prixopt=$gar['differenceprix'][$site_code];
		  	$pdf->multicell(17,7,($prixopt==0?$template->translate("/devis/inclus","F"):"+".$template->price_format($prixopt,2,false)),1,0,'C',0);
		  	$ly+=7;

	    }
	 }
	 //option tropicalisation
	 if($rtropicale && $DB_site->num_rows($rtropicale)>0){
	 	$pdf->setXY($ptablex,$ly);
	    $pdf->setfont('arial','B',8);

	    $pdf->cell($cellw,5,$template->translate("/devis/option_tropicale","F"),0,0,'C',0);
	    $ly+=5;
	    $pdf->setfont('arial','',8);
	    while($trop=$DB_site->fetch_array($rtropicale)){
	    	$pdf->setXY($ptablex,$ly);
		  	$nbline=$pdf->nblines($cellw-17,$trop['libelle'.$template->langue]);
		  	$pdf->multicell($cellw-17,7/$nbline,$trop['libelle'.$template->langue],1);
		  	$pdf->setXY($ptablex+$cellw-17,$ly);
		  	$trop['differenceprix']=unserialize($trop[$differenceprix]);
		  	$prixopt=$trop['differenceprix'][$site_code];
		  	$pdf->multicell(17,7,($prixopt==0?$template->translate("/devis/inclus","F"):"+".$template->price_format($prixopt,2,false)),1,0,'C',0);
		  	$ly+=7;

	    }
	 }
	 //systeme anti-buée
	 if($rbuee && $DB_site->num_rows($rbuee)>0){
	 	$pdf->setXY($ptablex,$ly);
	    $pdf->setfont('arial','B',8);

	    $pdf->cell($cellw,5,$template->translate("/devis/option_buee","F"),0,0,'C',0);
	    $ly+=5;
	    $pdf->setfont('arial','',8);
	    while($buee=$DB_site->fetch_array($rbuee)){
	    	$pdf->setXY($ptablex,$ly);
		  	$nbline=$pdf->nblines($cellw-17,$buee['libelle'.$template->langue]);
		  	$pdf->multicell($cellw-17,7/$nbline,$buee['libelle'.$template->langue],1);
		  	$pdf->setXY($ptablex+$cellw-17,$ly);
		  	$buee['differenceprix']=unserialize($buee[$differenceprix]);
		  	$prixopt=$buee['differenceprix'][$site_code];
		  	$pdf->multicell(17,7,($prixopt==0?$template->translate("/devis/inclus","F"):"+".$template->price_format($prixopt,2,false)),1,0,'C',0);
		  	$ly+=7;

	    }
	 }
	 //type de lumiere
	 if($rtypelum && $DB_site->num_rows($rtypelum)>0){
	 	$pdf->setXY($ptablex,$ly);
	    $pdf->setfont('arial','B',8);

	    $pdf->cell($cellw,5,$template->translate("/devis/type_lumiere","F"),0,0,'C',0);
	    $ly+=5;
	    $pdf->setfont('arial','',8);
	    while($lum=$DB_site->fetch_array($rtypelum)){
	    	$pdf->setXY($ptablex,$ly);
		  	$nbline=$pdf->nblines($cellw-17,$lum['libelle'.$template->langue]);
		  	$pdf->multicell($cellw-17,7/$nbline,$lum['libelle'.$template->langue],1);
		  	$pdf->setXY($ptablex+$cellw-17,$ly);
		  	$lum['differenceprix']=unserialize($lum[$differenceprix]);
		  	$prixopt=$lum['differenceprix'][$site_code];
		  	$pdf->multicell(17,7,($prixopt==0?$template->translate("/devis/inclus","F"):"+".$template->price_format($prixopt,2,false)),1,0,'C',0);
		  	$ly+=7;

	    }
	 }
	 // type de pieds
	 if($rpied && $DB_site->num_rows($rpied)>0){
	 	$pdf->setXY($ptablex,$ly);
	    $pdf->setfont('arial','B',8);

	    $pdf->cell($cellw,5,$template->translate("/devis/type_pied","F"),0,0,'C',0);
	    $ly+=5;
	    $pdf->setfont('arial','',8);
	    while($pied=$DB_site->fetch_array($rpied)){
	    	$pdf->setXY($ptablex,$ly);
		  	$nbline=$pdf->nblines($cellw-17,$pied['libelle'.$template->langue]);
		  	$pdf->multicell($cellw-17,7/$nbline,$pied['libelle'.$template->langue],1);
		  	$pdf->setXY($ptablex+$cellw-17,$ly);
		  	$pied['differenceprix']=unserialize($pied[$differenceprix]);
		  	$prixopt=$pied['differenceprix'][$site_code];
		  	$pdf->multicell(17,7,($prixopt==0?$template->translate("/devis/inclus","F"):"+".$template->price_format($prixopt,2,false)),1,0,'C',0);
		  	$ly+=7;

	    }
	 }
	  // type de pierre
	 if($rpierre && $DB_site->num_rows($rpierre)>0){
	 	$pdf->setXY($ptablex,$ly);
	    $pdf->setfont('arial','B',8);

	    $pdf->cell($cellw,5,$template->translate("/devis/type_pierre","F"),0,0,'C',0);
	    $ly+=5;
	    $pdf->setfont('arial','',8);
	    while($pierre=$DB_site->fetch_array($rpierre)){
	    	$pdf->setXY($ptablex,$ly);
		  	$nbline=$pdf->nblines($cellw-17,$pierre['libelle'.$template->langue]);
		  	$pdf->multicell($cellw-17,7/$nbline,$pierre['libelle'.$template->langue],1);
		  	$pdf->setXY($ptablex+$cellw-17,$ly);
		  	$pierre['differenceprix']=unserialize($pierre[$differenceprix]);
		  	$prixopt=$pierre['differenceprix'][$site_code];
		  	$pdf->multicell(17,7,($prixopt==0?$template->translate("/devis/inclus","F"):"+".$template->price_format($prixopt,2,false)),1,0,'C',0);
		  	$ly+=7;

	    }
	 }
	 //type de verre
	 if($rverre && $DB_site->num_rows($rverre)>0){
	 	$pdf->setXY($ptablex,$ly);
	    $pdf->setfont('arial','B',8);

	    $pdf->cell($cellw,5,$template->translate("/devis/type_verre","F"),0,0,'C',0);
	    $ly+=5;
	    $pdf->setfont('arial','',8);
	    while($verre=$DB_site->fetch_array($rverre)){
	    	$pdf->setXY($ptablex,$ly);
		  	$nbline=$pdf->nblines($cellw-17,$verre['libelle'.$template->langue]);
		  	$pdf->multicell($cellw-17,7/$nbline,$verre['libelle'.$template->langue],1);
		  	$pdf->setXY($ptablex+$cellw-17,$ly);
		  	$verre['differenceprix']=unserialize($verre[$differenceprix]);
		  	$prixopt=$verre['differenceprix'][$site_code];
		  	$pdf->multicell(17,7,($prixopt==0?$template->translate("/devis/inclus","F"):"+".$template->price_format($prixopt,2,false)),1,0,'C',0);
		  	$ly+=7;

	    }
	 }


?>
