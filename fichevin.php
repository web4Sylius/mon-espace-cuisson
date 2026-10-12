<?php

function fichevin($pdf,$article,$finitionid){
    global $rootpath,$template,$host,$DB_site,$facture,$autocolor;

    $selection=$DB_site->query("SELECT a.artid,a.artcode,a.libelle".$template->langue.",a.image,a.gamme".$template->langue." FROM article a INNER JOIN articlerecherche_finition af USING(artid) INNER JOIN article_site a_s USING(artid) WHERE typearticleid=6 AND a.artid<>$article[artid] AND af.finitionid='".$finitionid."' AND a_s.active='1' AND a_s.site_code='".$facture['site_code']."' LIMIT 4");
    if($DB_site->num_rows($selection)==0) return false;
    
    $marque=$DB_site->query_first("SELECT * FROM marque m, article_marque am WHERE m.marqueid=am.marqueid AND am.artid='$article[artid]'");

    $pdf->AddPage();
    $pdf->SetAutoPageBreak(false,10);

    $pdf->image($rootpath."images/templates/".$template->site_code()."/".$template->langue."/logo2016.jpg",7,4,99);

    $pdf->setfont('arial','B',12);
    $pdf->settextcolor(171,22,74);
    $pdf->setXY(110,7);
    $pdf->cell(100,4,$template->translate("/devis/selection_sommelier","F"),0,0,'L');
    
    $pdf->setfont('arial','',8);
    $pdf->textColor("black");
    $pdf->setXY(110,14);

    $pdf->multicell(91,4,$template->translate("/devis/selection_sommelier_description"));

   
    $ly=48;
    $lx=7;
    $nonprop=array();
    
    while($vin=$DB_site->fetch_array($selection)){
	$article_prices=article_prices($vin[artid],$template->tva_ref(),$template->site_code(),false);
	$nonprop[]=$vin['artid'];
	$pdf->drawColor($autocolor);
	$pdf->textColor("black");

	$pdf->rect($lx,$ly,44,71,'D');
	$img=$rootpath."configurations/".$host."/images/produits/article/".$vin[artid].".".$vin[image];
	if(file_exists($img)){
		$size=getimagesize($img);
		$w=intval(54*$size[0]/$size[1]);
		if($w>42) $w=42;
		$pdf->image($img,$lx+(44-$w)/2,$ly+1,$w);
	}
	$pdf->setfont('arial','',7);
	
	$pdf->link($lx,$ly,44,71,$template->translate("/site/[site_code]/url").$template->mkurl("article",$vin['artid'],$vin));

	$pdf->textColor($autocolor);
	$pdf->setfont('arial','',7);
	$pdf->setXY($lx,$ly+61);
	$pdf->cell(44,4,$vin['gamme'.$template->langue]." ".$vin['artcode'],0,0,'L');

	$pdf->setfont('arial','B',8);
	$pdf->textColor("black");
	$pdf->setXY($lx,$ly+67);
	$pdf->cell(44,4,$template->price_format($article_prices['prix'],2,false),0,0,'R');

	$pdf->setfont('arial','',8);
	$pdf->setXY($lx,$ly+54);
	$pdf->multicell(44,3,Truncate(html_entity_decode($vin['libelle'.$template->langue],ENT_COMPAT,"ISO-8859-15"),60),0,'L');
	$lx+=50;
    }

    $pdf->setfont('arial','B',10);
    $pdf->fillColor($autocolor);
    $pdf->textColor("white");
    $pdf->setXY(8,130);
    $pdf->cell(194,6,$template->translate("/devis/a_decouvrir","F"),0,0,'C',1);

    $lx=8;
    $ly=138;
    $selection=$DB_site->query("SELECT a.artid,a.artcode,a.libelle".$template->langue.",a.prix,a.prixconseilleht,a.image,a.gamme".$template->langue." FROM article a INNER JOIN article_site a_s USING(artid) WHERE typearticleid=6 AND a_s.active='1' AND a_s.site_code='".$facture['site_code']."' AND a.artid NOT IN ('".implode("','",$nonprop)."') ORDER BY RAND() LIMIT 8");
    while($vin=$DB_site->fetch_array($selection)){
	$article_prices=article_prices($vin[artid],$template->tva_ref(),$template->site_code(),false);
	
	$pdf->drawColor($autocolor);
	$pdf->textColor("black");
	$pdf->rect($lx,$ly,44,70,'D');
	$img=$rootpath."configurations/".$host."/images/produits/".$vin[artid].".".$vin[image];
	if(file_exists($img)){
		$size=getimagesize($img);
		$w=intval(47*$size[0]/$size[1]);
		if($w>43) $w=43;
		$pdf->image($img,$lx+(44-$w)/2,$ly+1,$w);
	}

	$pdf->link($lx,$ly,44,70,$template->translate("/site/[site_code]/url").$template->mkurl("article",$vin['artid'],$vin));

	$pdf->textColor($autocolor);
	$pdf->setfont('arial','',7);
	$pdf->setXY($lx,$ly+61);
	$pdf->cell(44,4,$vin['gamme'.$template->langue]." ".$vin['artcode'],0,0,'L');

	$pdf->textColor("black");
	$pdf->setfont('arial','B',8);
	$pdf->setXY($lx,$ly+66);
	$pdf->cell(44,4,$template->price_format($article_prices['prix'],2,false),0,0,'R');

	$pdf->setfont('arial','',8);
	$pdf->setXY($lx,$ly+52);
	$pdf->multicell(44,3,Truncate(html_entity_decode($vin['libelle'.$template->langue],ENT_COMPAT,"ISO-8859-15"),60),0,'L');
	$lx+=50;
	if($lx>200){
	    $lx=8;
	    $ly+=76;
	}
    }
}