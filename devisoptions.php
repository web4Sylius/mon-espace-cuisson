<?php
//gestion des options pour devis

$rlogiciel=$rpresta=$rgarvisite=false;
$rcolor=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=1 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
$rcadre=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=2 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
// 	 $rgar=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=3 AND ac.artid=".$article['artid']);
$rportlib = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=4");
$rporte=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix, libelle".$template->langue." as libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=4 AND ac.artid=".$article['artid']);
$rinterieur=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=40 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
$advineo=0;
//if($options==0){
$reclairage=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix, libelle".$template->langue." as libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=24 AND ac.artid=".$article['artid']." ORDER BY position");
$rlogiciel=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=27 AND ac.artid=".$article['artid']." ORDER BY position");
$rprestalib = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=28");
$rpresta=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix,libelle".$template->langue." as libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=28 AND ac.artid=".$article['artid']." ORDER BY position");
$rgarvisite=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix,libelle".$template->langue." as libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=26 AND ac.artid=".$article['artid']." ORDER BY position");
$rgarvisitelib = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=26");
$raqua=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=29 AND ac.artid=".$article['artid']." ORDER BY position");
$rperso=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=30 AND ac.artid=".$article['artid']." ORDER BY position");
$rnettoyage=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=31 AND ac.artid=".$article['artid']." ORDER BY position");
//type de groupe de froid
$rgroupefroid=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=50 AND ac.artid=".$article['artid']." ORDER BY position");
//couleur de la vitre
$rcolorvitre=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix, libelle".$template->langue." as libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=13 AND ac.artid=".$article['artid']." ORDER BY position");
//serrure de porte
$rserrurelib = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=14");
$rserrure=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix,libelle".$template->langue." as libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=14 AND ac.artid=".$article['artid']." ORDER BY position");
//garantie constructeur
$rgarconst=$DB_site->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=22 AND ac.artid=".$article['artid']." ORDER BY position");
//option tropicalisation
$rtropicale=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=36 AND ac.artid=".$article['artid']." ORDER BY position");
////systeme anti-buÃ©e
$rbuee=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=25 AND ac.artid=".$article['artid']." ORDER BY position");
//type de lumiere
$rtypelum=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=44 AND ac.artid=".$article['artid']." ORDER BY position"); 
//type de pieds
$rpied=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix, libelle".$template->langue." as libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=42 AND ac.artid=".$article['artid']." ORDER BY position"); 
//type de pierre
$rpierre=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=45 AND ac.artid=".$article['artid']." ORDER BY position"); 
// type de verre
$rverrelib = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=49");
$rverre=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix, libelle".$template->langue." as libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=49 AND ac.artid=".$article['artid']." ORDER BY position"); 
//MatiÃ¨re finition extÃ©rieur 
$rmatierelib = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=51");
$rmatiere=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix, libelle".$template->langue." as libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=51 AND ac.artid=".$article['artid']." ORDER BY position"); 
//MatiÃ¨re clayette minibar
$rmclayettelib = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=54");
$rmclayette=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix, libelle".$template->langue." as libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=54 AND ac.artid=".$article['artid']." ORDER BY position");
//Disposition minicave minibar
$rdispositionlib = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=55");
$rdisposition=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix, libelle".$template->langue." as libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=55 AND ac.artid=".$article['artid']." ORDER BY position");
//Marche palier Wineroom
$rwnrpalierlib=$DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=57");
$rwnrpalier=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix, libelle".$template->langue." as libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=57 AND ac.artid=".$article['artid']." ORDER BY position");
//Position Ã©clairage barre LED
$rbarreledlib = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=58");
$rbarreled=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix, libelle".$template->langue." as libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=58 AND ac.artid=".$article['artid']." ORDER BY position");
//Agencement intÃ©rieur Wineroom
$rwnragencementlib = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=59");
$rwnragencement=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix, libelle".$template->langue." as libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=59 AND ac.artid=".$article['artid']." ORDER BY position");
//Dispositions des amÃ©nagements Wineroom
$rwnrdispolib = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=60");
$rwnrdispo=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix, libelle".$template->langue." as libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=60 AND ac.artid=".$article['artid']." ORDER BY position");

//Pack Acoustique & Domestique
$rwnracouslib = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=63");
$rwnracous=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix, libelle".$template->langue." as libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=63 AND ac.artid=".$article['artid']." ORDER BY position");

//Isolation du sol
$rwnrisolib = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=62");
$rwnriso=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix, libelle".$template->langue." as libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=62 AND ac.artid=".$article['artid']." ORDER BY position");

//Filtre a charbon actif
$rwnrfiltreclib = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=53");
$rwnrfiltrec=$DB_site->query("SELECT *,ac.".$differenceprix." as differenceprix, libelle".$template->langue." as libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=53 AND ac.artid=".$article['artid']." ORDER BY position");

//RÃ©hausse monobloc
$rrehausse=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=75 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");

//wine supervisor
$rsupervisor=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=68 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");

//gestion humiditÃ©
$rhumidite=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=76 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");

//Ã©paisseur cloison isolÃ©e
$rcloison=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=78 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");

//type de porte
$rtypeporte=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=79 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
//teinte porte
$rteinteporte=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=74 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
//fond miroir
$rfondmiroir=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=73 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
//finition des parties metaliques
$rfinmetal=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=72 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
//coloris de l'encadrement
$rcolorencadre=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=71 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
//SÃ©paration intÃ©rieure
$rsepinter=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=70 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
//Finition des cotÃ©s de la cave
$rcotecave=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=69 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
//kit limiteur de pression
$rlimpress=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=39 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
//niveau sonore
$rnivsonor=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=83 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
//AmÃ©nagement bas de cave
$rbascave=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=86 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
//amenagement cave interieur
$rintercave=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=85 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
//position cave de service
$rposcaveservice=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=84 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
//habillage groupe froid
$rhabgroupefroid=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=64 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
//verrouillage des portes
$rverrouporte=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=65 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
//finition du sol intertieur
$rsolinter=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=67 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
//coloris Carrosserie Evaporateur
$carrosserie=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=105 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
//Retro Eclairage - Climatiseur FRIAX
$retroeclairage=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=103 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");
//Facade Interchangeable - Climatiseur FRIAX
$facadefriax=$DB_site->query("SELECT ac.image,c.caractvalid,ac.".$differenceprix." as differenceprix,c.libelle".$template->langue." AS libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur c ON (c.caractvalid=ac.caractvalid) WHERE c.caractid=104 AND ac.artid=".$article['artid']." ORDER BY c.position LIMIT 28");

$options=$DB_site->num_rows($rcolor)+$DB_site->num_rows($rcadre)+$DB_site->num_rows($rporte)+$DB_site->num_rows($rinterieur)+$DB_site->num_rows($reclairage)+$DB_site->num_rows($raqua)+$DB_site->num_rows($rperso)+$DB_site->num_rows($rnettoyage)+$DB_site->num_rows($rgroupefroid)+$DB_site->num_rows($rcolorvitre)+$DB_site->num_rows($rserrure)+$DB_site->num_rows($rgarconst)+$DB_site->num_rows($rtropicale)+$DB_site->num_rows($rbuee)+$DB_site->num_rows($rtypelum)+$DB_site->num_rows($rpied)+$DB_site->num_rows($rpierre)+$DB_site->num_rows($rverre)+$DB_site->num_rows($rmatiere)+$DB_site->num_rows($rmclayette)+$DB_site->num_rows($rdisposition)+$DB_site->num_rows($rwnrpalier)+$DB_site->num_rows($rbarreled)+$DB_site->num_rows($rwnragencement)+$DB_site->num_rows($rrehausse)+$DB_site->num_rows($rsupervisor)+$DB_site->num_rows($rhumidite)+$DB_site->num_rows($rcloison)+$DB_site->num_rows($rteinteporte)+$DB_site->num_rows($rfondmiroir)+$DB_site->num_rows($rfinmetal)+$DB_site->num_rows($rcolorencadre)+$DB_site->num_rows($rlimpress)+$DB_site->num_rows($rnivsonor)+$DB_site->num_rows($rbascave)+$DB_site->num_rows($rintercave)+$DB_site->num_rows($rposcaveservice)+$DB_site->num_rows($rhabgroupefroid)+$DB_site->num_rows($rverrouporte)+$DB_site->num_rows($rsolinter);
$advineo=$DB_site->num_rows($rlogiciel)+$DB_site->num_rows($rpresta)+$DB_site->num_rows($rgarvisite)+$DB_site->num_rows($carrosserie)+$DB_site->num_rows($retroeclairage)+$DB_site->num_rows($facadefriax);
	//}
 // Ajout d'une nouvelle page spÃ©cialement pour les options.

if($options + $advineo > 0){

	optionhead($pdf,$article,$marque);
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
	$pdf->multicell(56,3,html_entity_decode($article["libelle".$template->langue],ENT_COMPAT,"ISO-8859-15"),0,'L',0);
    $pdf->ln(8);  
    
    
    $autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=".$article['artid']." AND visible=1 AND (langue='' OR langue='".$template->langue."')");
	$autreid="";
	if($autreimg!="") $autreid="_".$autreimg['articlephotoid'];
	$image=$rootpath."configurations/".$host."/images/produits/br/".$article['artid'].$autreid.".".$article['image'];
    
    $w=46;
	$py=60;
	
	if(file_exists($image)){
	    $size=getimagesize($image);
	    if($size[1]>392) $w=intval($size[0]*(392/$size[1])*0.25);
	    if($w>46) $w=46;
	    $pdf->image($image,10,$py,$w);
    }  
    
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

}

  
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
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+10,$ly+16);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+16,4,4,'PNG');
		    }
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
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+10,$ly+16);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+16,4,4,'PNG');
		    }
		    $x+=18;
		    $count++;

		}
		$ly+=22;
		$pdf->setfont('arial','',8);
	 }

	 $lx=10;
	 $pdf->setXY($ptablex,$ly);
	 $hcol=$DB_site->num_rows($rgarvisite)*7;
	 if($rgarvisite && $DB_site->num_rows($rgarvisite)>0){
	 	$nbrow = $DB_site->num_rows($rgarvisite);
	 	$nbrow = $nbrow / 3;
	 	$push = 32;
	 	$nbrow = ceil($nbrow);
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$rgarvisitelib['libcaract'],0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,16*$nbrow + $push,"",1);
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rgarvisite)){
			if($count > 3){
				$count = 1;
				$ly+=32;
				$x=$ptablex+5;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+9,$ly+16,10); //image option
		    $pdf->setXY($x,$ly+2); //position x et y du texte de l'option.
		    $pdf->multicell(27,2,$color['libelle'],0,'C',0); //libelle option
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+9,$ly+20);
		    $pdf->cell(10,20,$txt,0,0,'C');
		    $x+=35;
		    $count++;
		}
		$ly+= $push;
		$pdf->setfont('arial','',8);
	 }

	 if($rpresta && $DB_site->num_rows($rpresta)>0){
	 	$nbrow = $DB_site->num_rows($rpresta);
	 	$nbrow = $nbrow / 3;
	 	$push = 12;
	 	$nbrow = ceil($nbrow);
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$rprestalib['libcaract'],0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,16*$nbrow + $push,"",1);
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rpresta)){
			if($count > 3){
				$count = 1;
				$ly+=18;
				$x=$ptablex+5;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+9,$ly+10,10); //image option
		    $pdf->setXY($x,$ly+2); //position x et y du texte de l'option.
		    $pdf->multicell(27,2,$color['libelle'],0,'C',0); //libelle option
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+9,$ly+20);
		    $pdf->cell(10,10,$txt,0,0,'C');
		    $x+=35;
		    $count++;
		}
		$ly+=16 + $push;
		$pdf->setfont('arial','',8);
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
		  $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		  if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
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
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+10,$ly+16);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+16,4,4,'PNG');
		    }
		    $x+=18;
		    $count++;
		}
		$ly+=22;
		$pdf->setfont('arial','',8);
	 }
	 // sens de la porte
	 if($DB_site->num_rows($rporte)>0){
	 	$nbrow = $DB_site->num_rows($rporte);
	 	$nbrow = $nbrow / 7;
	 	$nbrow = ceil($nbrow);
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$rportlib['libcaract'],0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,22*$nbrow,"",1);
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rporte)){
			if($count > 7){
				$count = 1;
				$ly+=22;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+10,$ly+8,10);
		    $pdf->setXY($x+2,$ly+2);
		    $pdf->multicell(25,2,$color['libelle'],0,'C',0);
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+10,$ly+17);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+17,4,4,'PNG');
		    }
		    $x+=18;
		    $count++;
		}
		$ly+=22;
		$pdf->setfont('arial','',8);
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
		  $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		  if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		  $pdf->multicell(17,7,$txt,1,0,'C',0);
		  $ly+=7;
	      }
	      $ly+=2;
	 }

	/*if($rperso && $DB_site->num_rows($rperso)>0){
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
		  $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		  $prixopt==0?$txt=$template->translate("/devis/inclus","F"):$txt=$prix;
		  $pdf->multicell(17,7,$txt,1,0,'C',0);
		  $ly+=7;
	      }
	      $ly+=2;
	 }*/
	 
	 if($reclairage && $DB_site->num_rows($reclairage)>0){
	 	$nbrow = $DB_site->num_rows($reclairage);
	 	$nbrow = $nbrow / 5;
	 	$nbrow = ceil($nbrow);
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$template->translate("/devis/eclairage","F"),0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,24*$nbrow,"",1);
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($reclairage)){
			if($count > 7){
				$count = 1;
				$ly+=18;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+4,$ly+8,10);
		    $pdf->setXY($x-4,$ly+1);
		    $pdf->multicell(25,2,$color['libelle'],0,'C',0);
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+4,$ly+20);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+20,4,4,'PNG');
		    }
		    $x+=23;
		    $count++;
		}
		$ly+=24;
		$pdf->setfont('arial','',8);
	 }

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
		  	$template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		  	$pdf->multicell(17,7,$txt,1,0,'C',0);
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
		  	$template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		  	$pdf->multicell(17,7,$txt,1,0,'C',0);
		  	$ly+=7;

	    }
	 }
	 //couleur de la vitre caractid 13
	 if($rcolorvitre && $DB_site->num_rows($rcolorvitre)>0){
	 	$nbrow = $DB_site->num_rows($rcolorvitre);
	 	$nbrow = $nbrow / 7;
	 	$nbrow = ceil($nbrow);
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$template->translate("/devis/coloris_vitre","F"),0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,22*$nbrow,"",1);
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rcolorvitre)){
			if($count > 6){
				$count = 1;
				$ly+=22;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+10,$ly+4,10);
		    $pdf->setXY($x+6,$ly);
		    $pdf->multicell(18,3,$color['libelle'],0,'C',0);
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+10,$ly+16);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+16,4,4,'PNG');
		    }

		    $x+=18;
		    $count++;
		}
		$ly+=22;
		$pdf->setfont('arial','',8);
	 }
	 //serrure porte caractid 14
	  if($rserrure && $DB_site->num_rows($rserrure)>0){
	 	$nbrow = $DB_site->num_rows($rserrure);
	 	$nbrow = $nbrow / 3;
	 	$push = 12;
	 	$nbrow = ceil($nbrow);
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$rserrurelib['libcaract'],0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,16*$nbrow + $push,"",1);
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rserrure)){
			if($count > 3){
				$count = 1;
				$ly+=18;
				$x=$ptablex+5;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+9,$ly+8,10); //image option
		    $pdf->setXY($x,$ly+2); //position x et y du texte de l'option.
		    $pdf->multicell(27,2,$color['libelle'],0,'C',0); //libelle option
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+9,$ly+20);
		    $pdf->cell(10,10,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+20,4,4,'PNG');
		    }
		    $x+=35;
		    $count++;
		}
		$ly+=16 + $push;
		$pdf->setfont('arial','',8);
	 }
	 //prestation complÃ©mentaire
	 if($rgarconst && $DB_site->num_rows($rgarconst)>0){
	 	$nbrow = $DB_site->num_rows($rgarconst);
	 	$nbrow = $nbrow / 3;
	 	$push = 8;
	 	$nbrow = ceil($nbrow);
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$rgarconst['libcaract'],0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,16*$nbrow + $push,"",1);
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rgarconst)){
			if($count > 3){
				$count = 1;
				$ly+=18;
				$x=$ptablex+5;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+9,$ly+8,10); //image option
		    $pdf->setXY($x,$ly+2); //position x et y du texte de l'option.
		    $pdf->multicell(27,2,$color['libelle'],0,'C',0); //libelle option
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+9,$ly+20);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+20,4,4,'PNG');
		    }
		    $x+=35;
		    $count++;
		}
		$ly+=16 + $push;
		$pdf->setfont('arial','',8);
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
		  	$template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		  	$pdf->multicell(17,7,$txt,1,0,'C',0);
		  	$ly+=7;

	    }
	 }
	 //systeme anti-buÃ©e
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
		  	$template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		  	$pdf->multicell(17,7,$txt,1,0,'C',0);
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
		  	$template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		  	$pdf->multicell(17,7,$txt,1,0,'C',0);
		  	$ly+=7;

	    }
	 }
	 // type de pieds
	 if($rpied && $DB_site->num_rows($rpied)>0){
	 	$nbrow = $DB_site->num_rows($rpied);
	 	$nbrow = $nbrow / 6;
	 	$nbrow = ceil($nbrow);
		$pdf->setXY($ptablex,$ly); //position titre option
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$template->translate("/devis/type_pied","F"),0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,18*$nbrow,"",1);//cadre option
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rpied)){
			if($count > 6){
				$count = 1;
				$ly+=18;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+5,$ly+7,10); //image
		    $pdf->setXY($x-2,$ly+2); // position x et y texte libelle option
		    $pdf->multicell(25,2,$color['libelle'],0,'C',0); //libelle option
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+5,$ly+14); //position x et y prix 
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+14,4,4,'PNG');
		    }
		    $x+=23; // espace entre photo
		    $count++;
		}
		$ly+=18;
		$pdf->setfont('arial','',8);
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
		  	$template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		  	$pdf->multicell(17,7,$txt,1,0,'C',0);
		  	$ly+=7;

	    }
	 }

	 //MatiÃ¨re finition extÃ©rieur caractid 51
	 if($rmatiere && $DB_site->num_rows($rmatiere)>0){
	 	$nbrow = $DB_site->num_rows($rmatiere);
	 	$nbrow = $nbrow / 6;
	 	$nbrow = ceil($nbrow);
		$pdf->setXY($ptablex,$ly); //position titre option
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$rmatierelib['libcaract'],0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,18*$nbrow,"",1); //cadre option
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rmatiere)){
			if($count > 6){
				$count = 1;
				$ly+=18;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+4,$ly+7,10); //image
		    $pdf->setXY($x-4,$ly+2); //text
		    $pdf->multicell(27,2,$color['libelle'],0,'C',0);
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+4,$ly+12);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+12,4,4,'PNG');
		    }
		    $x+=23; // espace entre photo
		    $count++;
		}
		$ly+=18;
		$pdf->setfont('arial','',8);
	 }
	  //MatiÃ¨re matiÃ¨re clayette minibar caractid 54
	 if($rmclayette && $DB_site->num_rows($rmclayette)>0){
	 	$nbrow = $DB_site->num_rows($rmclayette);
	 	$nbrow = $nbrow / 7;
	 	$nbrow = ceil($nbrow);
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$rmclayettelib['libcaract'],0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,18*$nbrow,"",1);
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rmclayette)){
			if($count > 6){
				$count = 1;
				$ly+=18;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+2,$ly+7,10);
		    $pdf->setXY($x-4,$ly+2);
		    $pdf->multicell(20,2,$color['libelle'],0,'C',0);
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+2,$ly+13);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+13,4,4,'PNG');
		    }
		    $x+=23;
		    $count++;
		}
		$ly+=18;
		$pdf->setfont('arial','',8);
	 }

	  //Disposition minicave minibar caractid 55
	 if($rdisposition && $DB_site->num_rows($rdisposition)>0){
	 	$nbrow = $DB_site->num_rows($rdisposition);
	 	$nbrow = $nbrow / 6;
	 	$nbrow = ceil($nbrow);
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$rdispositionlib['libcaract'],0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,18*$nbrow,"",1);
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rdisposition)){
			if($count > 6){
				$count = 1;
				$ly+=18;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+2,$ly+5,10);
		    $pdf->setXY($x-4,$ly+2);
		    $pdf->multicell(25,2,$color['libelle'],0,'C',0);
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+2,$ly+13);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+13,4,4,'PNG');
		    }
		    $x+=23;
		    $count++;
		}
		$ly+=18;
		$pdf->setfont('arial','',8);
	 }

	 //Marche Palier Wineroom 57
	 if($rwnrpalier && $DB_site->num_rows($rwnrpalier)>0){
	 	$nbrow = $DB_site->num_rows($rwnrpalier);
	 	$nbrow = $nbrow / 3;
	 	$push = 8;
	 	$nbrow = ceil($nbrow);
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$rwnrpalierlib['libcaract'],0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,16*$nbrow + $push,"",1);
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rwnrpalier)){
			if($count > 3){
				$count = 1;
				$ly+=18;
				$x=$ptablex+5;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+9,$ly+8,10); //image option
		    $pdf->setXY($x,$ly+2); //position x et y du texte de l'option.
		    $pdf->multicell(27,2,$color['libelle'],0,'C',0); //libelle option
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+9,$ly+20);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+20,4,4,'PNG');
		    }
		    $x+=35;
		    $count++;
		}
		$ly+=16 + $push;
		$pdf->setfont('arial','',8);
	 }

	 //Position Ã©clairage barre led Wineroom 58
	 if($rbarreled && $DB_site->num_rows($rbarreled)>0){
	 	$nbrow = $DB_site->num_rows($rbarreled);
	 	$nbrow = $nbrow / 2;
	 	$nbrow = ceil($nbrow);
	 	if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$rbarreledlib['libcaract'],0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,22*$nbrow,"",1);
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rbarreled)){
			if($count > 2){
				$count = 1;
				$ly+=18;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+8,$ly+6,10);
		    $pdf->setXY($x-1,$ly+1);
		    $pdf->multicell(30,2,$color['libelle'],0,'C',0);
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+8,$ly+18);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+18,4,4,'PNG');
		    }
		    $x+=30;
		    $count++;
		}
		$ly+=22;
		$pdf->setfont('arial','',8);
	 }
	 //disposition des amÃ©nagements Wineroom 60
	 if($rwnrdispo && $DB_site->num_rows($rwnrdispo)>0){
	 	$nbrow = $DB_site->num_rows($rwnrdispo);
	 	$nbrow = $nbrow / 2;
	 	$nbrow = ceil($nbrow);
	 	if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$rwnrdispolib['libcaract'],0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,22*$nbrow,"",1);
		
		$x=$ptablex;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rwnrdispo)){
			if($count > 2){
				$count = 1;
				$ly+=18;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+12,$ly+8,10);
		    $pdf->setXY($x+2,$ly+1);
		    $pdf->multicell(30,2,$color['libelle'],0,'C',0);
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+12,$ly+18);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+18,4,4,'PNG');
		    }
		    $x+=30;
		    $count++;
		}
		$ly+=22;
		$pdf->setfont('arial','',8);
	 }

	 //Agencement intÃ©rieur Wineroom 59
	 if($rwnragencement && $DB_site->num_rows($rwnragencement)>0){
	 	$nbrow = $DB_site->num_rows($rwnragencement);
	 	$nbrow = $nbrow / 5;
	 	$nbrow = ceil($nbrow);
	 	if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$rwnragencementlib['libcaract'],0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,22*$nbrow,"",1);
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rwnragencement)){
			if($count > 5){
				$count = 1;
				$ly+=21;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+8,$ly+9,10);//image option
		    $pdf->setXY($x-1,$ly+1);//position x et y du texte de l'option.
		    $pdf->multicell(30,2,$color['libelle'],0,'C',0);//libelle option
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+8,$ly+18);//position x et y prix 
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+18,4,4,'PNG');
		    }
		    $x+=28; // espace entre photo
		    $count++;
		}
		$ly+=23;
		$pdf->setfont('arial','',8);
	 }

	 //type de verre 49
	 if($rverre && $DB_site->num_rows($rverre)>0){
	 	$nbrow = $DB_site->num_rows($rverre);
	 	$nbrow = $nbrow / 5;
	 	$nbrow = ceil($nbrow);
	 	if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$rverrelib['libcaract'],0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,22*$nbrow,"",1);//cadre option
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rverre)){
			if($count > 5){
				$count = 1;
				$ly+=21.5;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+4,$ly+7.5,10);
		    $pdf->setXY($x-4,$ly+1);
		    $pdf->multicell(30,2,$color['libelle'],0,'C',0);
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+4,$ly+19);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+19,4,4,'PNG');
		    }
		    $x+=28;
		    $count++;
		}
		$ly+=24;
		$pdf->setfont('arial','',8);
	 }
	
	 //Isolation du sol 62
	 if($rwnriso && $DB_site->num_rows($rwnriso)>0){
	 	$nbrow = $DB_site->num_rows($rwnriso);
	 	$nbrow = $nbrow / 5;
	 	$nbrow = ceil($nbrow);
	 	if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$rwnrisolib['libcaract'],0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,23*$nbrow,"",1);//cadre option
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rwnriso)){
			if($count > 5){
				$count = 1;
				$ly+=21.5;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+4,$ly+7.5,10);
		    $pdf->setXY($x-4,$ly+1);
		    $pdf->multicell(30,2,$color['libelle'],0,'C',0);
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+4,$ly+19);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+19,4,4,'PNG');
		    }
		    $x+=28;
		    $count++;
		}
		$ly+=24;
		$pdf->setfont('arial','',8);
	 }

	 //Pack Acoustique & Domestique 63
	 if($rwnracous && $DB_site->num_rows($rwnracous)>0){
	 	$nbrow = $DB_site->num_rows($rwnracous);
	 	$nbrow = $nbrow / 5;
	 	$nbrow = ceil($nbrow);
	 	if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$rwnracouslib['libcaract'],0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,18*$nbrow,"",1);//cadre option
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rwnracous)){
			if($count > 5){
				$count = 1;
				$ly+=21.5;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+6,$ly+5,10);
		    $pdf->setXY($x-2,$ly+1);
		    $pdf->multicell(30,2,$color['libelle'],0,'C',0);
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+6,$ly+12);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+12,4,4,'PNG');
		    }
		    $x+=28;
		    $count++;
		}
		$ly+=18;
		$pdf->setfont('arial','',8);
	 }

	 //Filtre a charbon actif 53
	 if($rwnrfiltrec && $DB_site->num_rows($rwnrfiltrec)>0){
	 	$nbrow = $DB_site->num_rows($rwnrfiltrec);
	 	$nbrow = $nbrow / 5;
	 	$nbrow = ceil($nbrow);
	 	if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$rwnrfiltreclib['libcaract'],0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,18*$nbrow,"",1);//cadre option
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rwnrfiltrec)){
			if($count > 5){
				$count = 1;
				$ly+=21.5;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+8,$ly+5,10);
		    $pdf->setXY($x-2,$ly+1);
		    $pdf->multicell(30,2,$color['libelle'],0,'C',0);
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+8,$ly+13);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+13,4,4,'PNG');
		    }
		    $x+=28;
		    $count++;
		}
		$ly+=18;
		$pdf->setfont('arial','',8);
	 }

    //rÃ©hausse monobloc
	 if($rrehausse && $DB_site->num_rows($rrehausse)>0){
	 	$nbrow = $DB_site->num_rows($rrehausse);
	 	$nbrow = $nbrow / 5;
	 	$nbrow = ceil($nbrow);
	 	if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
        $libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=75");
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,18*$nbrow,"",1);//cadre option
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rrehausse)){
			if($count > 5){
				$count = 1;
				$ly+=21.5;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+8,$ly+5,10);
		    $pdf->setXY($x-2,$ly+1);
		    $pdf->multicell(30,2,$color['libelle'],0,'C',0);
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+8,$ly+13);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+13,4,4,'PNG');
		    }
		    $x+=28;
		    $count++;
		}
		$ly+=18;
		$pdf->setfont('arial','',8);
	 }


    //Wine supervisor
	 if($rsupervisor && $DB_site->num_rows($rsupervisor)>0){
	 	$nbrow = $DB_site->num_rows($rsupervisor);
	 	$nbrow = $nbrow / 5;
	 	$nbrow = ceil($nbrow);
	 	if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
        }
        
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=68");
		$pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,23*$nbrow,"",1);//cadre option
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rsupervisor)){
			if($count > 5){
				$count = 1;
				$ly+=21.5;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
            $size=getimagesize($caractimg);
		    $imgw=10;
		    $imgh=intval($imgw*$size[1]/$size[0]);
		    if($imgh>12){
                $imgh=12;
                $imgw=intval(12*$size[0]/$size[1]);
            }
		    $pdf->image($caractimg,$x+8+(10-$imgw)/2,$ly+6,$imgw,$imgh);
		    $pdf->setXY($x-2,$ly+1);
		    $pdf->multicell(30,2,$color['libelle'],0,'C',0);
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+8,$ly+19);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+19,4,4,'PNG');
		    }
		    $x+=28;
		    $count++;
		}
		$ly+=24;
		$pdf->setfont('arial','',8);
	 }

    //Gestion humiditÃ©
	 if($rhumidite && $DB_site->num_rows($rhumidite)>0){
	 	$nbrow = $DB_site->num_rows($rhumidite);
	 	$nbrow = $nbrow / 5;
	 	$nbrow = ceil($nbrow);
	 	if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=76");
		$pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,23*$nbrow,"",1);//cadre option
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rhumidite)){
			if($count > 5){
				$count = 1;
				$ly+=21.5;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $size=getimagesize($caractimg);
		    $imgw=14;
		    $imgh=intval($imgw*$size[1]/$size[0]);
		    if($imgh>10){
                $imgh=10;
                $imgw=intval(10*$size[0]/$size[1]);
            }
		    $pdf->image($caractimg,$x+8+(10-$imgw)/2,$ly+8,$imgw,$imgh);
		    $pdf->setXY($x-2,$ly+1);
		    $pdf->multicell(30,2,$color['libelle'],0,'C',0);
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+8,$ly+19);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+19,4,4,'PNG');
		    }
		    $x+=28;
		    $count++;
		}
		$ly+=24;
		$pdf->setfont('arial','',8);
    }

    //Ã©paisseur cloison isolÃ©e
	 if($rcloison && $DB_site->num_rows($rcloison)>0){
	 	$nbrow = $DB_site->num_rows($rcloison);
	 	$nbrow = $nbrow / 5;
	 	$nbrow = ceil($nbrow);
	 	if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
		$pdf->setXY($ptablex,$ly);
		$pdf->setfont('arial','B',8);
		$libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=78");
		$pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
		$ly+=5;
		$pdf->setfont('arial','',8);
		$pdf->setXY($ptablex,$ly);
		$pdf->cell($cellw,18*$nbrow,"",1);//cadre option
		
		$x=$ptablex+1;
		$count=1;
		$pdf->setfont('arial','',7);
		while($color=$DB_site->fetch_array($rcloison)){
			if($count > 5){
				$count = 1;
				$ly+=21.5;
				$x=$ptablex+1;
			}
		    $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
		    if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
		    $pdf->image($caractimg,$x+8,$ly+6,10);
		    $pdf->setXY($x-2,$ly+1);
		    $pdf->multicell(30,2,$color['libelle'],0,'C',0);
		    $color['differenceprix']=unserialize($color['differenceprix']);
		    $prixopt=$color['differenceprix'][$site_code];
		    $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
		    if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
		    $pdf->setXY($x+8,$ly+13);
		    $pdf->cell(10,4,$txt,0,0,'C');
		    if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+13,4,4,'PNG');
		    }
		    $x+=28;
		    $count++;
		}
		$ly+=18;
		$pdf->setfont('arial','',8);
    }
    
    //type porte
    if($rtypeporte && $DB_site->num_rows($rtypeporte)>0){
        $nbrow = $DB_site->num_rows($rtypeporte);
        $nbrow = $nbrow / 5;
        $nbrow = ceil($nbrow);
        if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
        $pdf->setXY($ptablex,$ly);
        $pdf->setfont('arial','B',8);
        $libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=79");
        $pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
        $ly+=5;
        $pdf->setfont('arial','',8);
        $pdf->setXY($ptablex,$ly);
        $pdf->cell($cellw,18*$nbrow,"",1);//cadre option
        
        $x=$ptablex+1;
        $count=1;
        $pdf->setfont('arial','',7);
        while($color=$DB_site->fetch_array($rtypeporte)){
            if($count > 5){
                $count = 1;
                $ly+=21.5;
                $x=$ptablex+1;
            }
            $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
            if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
            $pdf->image($caractimg,$x+8,$ly+6,10);
            $pdf->setXY($x-2,$ly+1);
            $pdf->multicell(30,2,$color['libelle'],0,'C',0);
            $color['differenceprix']=unserialize($color['differenceprix']);
            $prixopt=$color['differenceprix'][$site_code];
            $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
            if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
            $pdf->setXY($x+8,$ly+13);
            $pdf->cell(10,4,$txt,0,0,'C');
            if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+13,4,4,'PNG');
		    }
            $x+=28;
            $count++;
        }
        $ly+=18;
        $pdf->setfont('arial','',8);
    }

    //teinte porte
    if($rteinteporte && $DB_site->num_rows($rteinteporte)>0){
        $nbrow = $DB_site->num_rows($rteinteporte);
        $nbrow = $nbrow / 5;
        $nbrow = ceil($nbrow);
        if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
        $pdf->setXY($ptablex,$ly);
        $pdf->setfont('arial','B',8);
        $libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=74");
        $pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
        $ly+=5;
        $pdf->setfont('arial','',8);
        $pdf->setXY($ptablex,$ly);
        $pdf->cell($cellw,30*$nbrow,"",1);//cadre option
        
        $x=$ptablex+1;
        $count=1;
        $pdf->setfont('arial','',7);
        while($color=$DB_site->fetch_array($rteinteporte)){
            if($count > 5){
                $count = 1;
                $ly+=21.5;
                $x=$ptablex+1;
            }
            $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
            if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
            $pdf->image($caractimg,$x+8,$ly+6,10);
            $pdf->setXY($x-2,$ly+1);
            $pdf->multicell(30,2,$color['libelle'],0,'C',0);
            $color['differenceprix']=unserialize($color['differenceprix']);
            $prixopt=$color['differenceprix'][$site_code];
            $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
            if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
            $pdf->setXY($x+8,$ly+13);
            $pdf->cell(10,30,$txt,0,0,'C');
            if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+25,4,4,'PNG');
		    }
            $x+=28;
            $count++;
        }
        $ly+=30;
        $pdf->setfont('arial','',8);
    }

    //fond miroir
    if($rfondmiroir && $DB_site->num_rows($rfondmiroir)>0){
        $nbrow = $DB_site->num_rows($rfondmiroir);
        $nbrow = $nbrow / 5;
        $nbrow = ceil($nbrow);
        if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
        $pdf->setXY($ptablex,$ly);
        $pdf->setfont('arial','B',8);
        $libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=73");
        $pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
        $ly+=5;
        $pdf->setfont('arial','',8);
        $pdf->setXY($ptablex,$ly);
        $pdf->cell($cellw,30*$nbrow,"",1);//cadre option
        
        $x=$ptablex+1;
        $count=1;
        $pdf->setfont('arial','',7);
        while($color=$DB_site->fetch_array($rfondmiroir)){
            if($count > 5){
                $count = 1;
                $ly+=21.5;
                $x=$ptablex+1;
            }
            $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
            if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
            $pdf->image($caractimg,$x+8,$ly+6,10);
            $pdf->setXY($x-2,$ly+1);
            $pdf->multicell(30,2,$color['libelle'],0,'C',0);
            $color['differenceprix']=unserialize($color['differenceprix']);
            $prixopt=$color['differenceprix'][$site_code];
            $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
            if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
            $pdf->setXY($x+8,$ly+13);
            $pdf->cell(10,30,$txt,0,0,'C');
            if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+25,4,4,'PNG');
		    }
            $x+=28;
            $count++;
        }
        $ly+=30;
        $pdf->setfont('arial','',8);
    }

    //finitions metaliques
    if($rfinmetal && $DB_site->num_rows($rfinmetal)>0){
        $nbrow = $DB_site->num_rows($rfinmetal);
        $nbrow = $nbrow / 5;
        $nbrow = ceil($nbrow);
        if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
        $pdf->setXY($ptablex,$ly);
        $pdf->setfont('arial','B',8);
        $libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=72");
        $pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
        $ly+=5;
        $pdf->setfont('arial','',8);
        $pdf->setXY($ptablex,$ly);
        $pdf->cell($cellw,34*$nbrow,"",1);//cadre option
        
        $x=$ptablex+1;
        $count=1;
        $pdf->setfont('arial','',7);
        while($color=$DB_site->fetch_array($rfinmetal)){
            if($count > 5){
                $count = 1;
                $ly+=21.5;
                $x=$ptablex+1;
            }
            $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
            if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
            $pdf->image($caractimg,$x+8,$ly+9,10);
            $pdf->setXY($x-2,$ly+1);
            $pdf->multicell(30,2,$color['libelle'],0,'C',0);
            $color['differenceprix']=unserialize($color['differenceprix']);
            $prixopt=$color['differenceprix'][$site_code];
            $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
            if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
            $pdf->setXY($x+8,$ly+13);
            $pdf->cell(10,36,$txt,0,0,'C');
            if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+28,4,4,'PNG');
		    }
            $x+=28;
            $count++;
        }
        $ly+=34;
        $pdf->setfont('arial','',8);
    }

    //coloris encadrement
    if($rcolorencadre && $DB_site->num_rows($rcolorencadre)>0){
        $nbrow = $DB_site->num_rows($rcolorencadre);
        $nbrow = $nbrow / 5;
        $nbrow = ceil($nbrow);
        if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
        $pdf->setXY($ptablex,$ly);
        $pdf->setfont('arial','B',8);
        $libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=71");
        $pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
        $ly+=5;
        $pdf->setfont('arial','',8);
        $pdf->setXY($ptablex,$ly);
        $pdf->cell($cellw,21*$nbrow,"",1);//cadre option
        
        $x=$ptablex+1;
        $count=1;
        $pdf->setfont('arial','',7);
        while($color=$DB_site->fetch_array($rcolorencadre)){
            if($count > 5){
                $count = 1;
                $ly+=21.5;
                $x=$ptablex+1;
            }
            $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
            if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
            $pdf->image($caractimg,$x+8,$ly+6,10);
            $pdf->setXY($x-2,$ly+1);
            $pdf->multicell(30,2,$color['libelle'],0,'C',0);
            $color['differenceprix']=unserialize($color['differenceprix']);
            $prixopt=$color['differenceprix'][$site_code];
            $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
            if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
            $pdf->setXY($x+8,$ly+13);
            $pdf->cell(10,4,$txt,0,0,'C');
            if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+13,4,4,'PNG');
		    }
            $x+=28;
            $count++;
        }
        $ly+=18;
        $pdf->setfont('arial','',8);
    }

    //separation interieur
    if($rsepinter && $DB_site->num_rows($rsepinter)>0){
        $nbrow = $DB_site->num_rows($rsepinter);
        $nbrow = $nbrow / 5;
        $nbrow = ceil($nbrow);
        if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
        $pdf->setXY($ptablex,$ly);
        $pdf->setfont('arial','B',8);
        $libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=70");
        $pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
        $ly+=5;
        $pdf->setfont('arial','',8);
        $pdf->setXY($ptablex,$ly);
        $pdf->cell($cellw,18*$nbrow,"",1);//cadre option
        
        $x=$ptablex+1;
        $count=1;
        $pdf->setfont('arial','',7);
        while($color=$DB_site->fetch_array($rsepinter)){
            if($count > 5){
                $count = 1;
                $ly+=21.5;
                $x=$ptablex+1;
            }
            $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
            if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
            $pdf->image($caractimg,$x+8,$ly+6,10);
            $pdf->setXY($x-2,$ly+1);
            $pdf->multicell(30,2,$color['libelle'],0,'C',0);
            $color['differenceprix']=unserialize($color['differenceprix']);
            $prixopt=$color['differenceprix'][$site_code];
            $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
            if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
            $pdf->setXY($x+8,$ly+13);
            $pdf->cell(10,4,$txt,0,0,'C');
            if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+13,4,4,'PNG');
		    }
            $x+=28;
            $count++;
        }
        $ly+=18;
        $pdf->setfont('arial','',8);
    }

    //Finition des cotÃ©s de la cave
    if($rcotecave && $DB_site->num_rows($rcotecave)>0){
        $nbrow = $DB_site->num_rows($rcotecave);
        $nbrow = $nbrow / 5;
        $nbrow = ceil($nbrow);
        if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
        $pdf->setXY($ptablex,$ly);
        $pdf->setfont('arial','B',8);
        $libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=69");
        $pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
        $ly+=5;
        $pdf->setfont('arial','',8);
        $pdf->setXY($ptablex,$ly);
        $pdf->cell($cellw,18*$nbrow,"",1);//cadre option
        
        $x=$ptablex+1;
        $count=1;
        $pdf->setfont('arial','',7);
        while($color=$DB_site->fetch_array($rcotecave)){
            if($count > 5){
                $count = 1;
                $ly+=21.5;
                $x=$ptablex+1;
            }
            $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
            if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
            $pdf->image($caractimg,$x+8,$ly+6,10);
            $pdf->setXY($x-2,$ly+1);
            $pdf->multicell(30,2,$color['libelle'],0,'C',0);
            $color['differenceprix']=unserialize($color['differenceprix']);
            $prixopt=$color['differenceprix'][$site_code];
            $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
            if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
            $pdf->setXY($x+8,$ly+13);
            $pdf->cell(10,4,$txt,0,0,'C');
            if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+13,4,4,'PNG');
		    }
            $x+=28;
            $count++;
        }
        $ly+=18;
        $pdf->setfont('arial','',8);
    }
    //kit limiteur pression
    if($rlimpress && $DB_site->num_rows($rlimpress)>0){
        $nbrow = $DB_site->num_rows($rlimpress);
        $nbrow = $nbrow / 5;
        $nbrow = ceil($nbrow);
        if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
        $pdf->setXY($ptablex,$ly);
        $pdf->setfont('arial','B',8);
        $libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=39");
        $pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
        $ly+=5;
        $pdf->setfont('arial','',8);
        $pdf->setXY($ptablex,$ly);
        $pdf->cell($cellw,18*$nbrow,"",1);//cadre option
        
        $x=$ptablex+1;
        $count=1;
        $pdf->setfont('arial','',7);
        while($color=$DB_site->fetch_array($rlimpress)){
            if($count > 5){
                $count = 1;
                $ly+=21.5;
                $x=$ptablex+1;
            }
            $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
            if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
            $pdf->image($caractimg,$x+8,$ly+6,10);
            $pdf->setXY($x-2,$ly+1);
            $pdf->multicell(30,2,$color['libelle'],0,'C',0);
            $color['differenceprix']=unserialize($color['differenceprix']);
            $prixopt=$color['differenceprix'][$site_code];
            $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
            if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
            $pdf->setXY($x+8,$ly+13);
            $pdf->cell(10,4,$txt,0,0,'C');
            if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+13,4,4,'PNG');
		    }
            $x+=28;
            $count++;
        }
        $ly+=18;
        $pdf->setfont('arial','',8);
    }
    //niveau sonor
    if($rnivsonor && $DB_site->num_rows($rnivsonor)>0){
        $nbrow = $DB_site->num_rows($rnivsonor);
        $nbrow = $nbrow / 5;
        $nbrow = ceil($nbrow);
        if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
        $pdf->setXY($ptablex,$ly);
        $pdf->setfont('arial','B',8);
        $libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=83");
        $pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
        $ly+=5;
        $pdf->setfont('arial','',8);
        $pdf->setXY($ptablex,$ly);
        $pdf->cell($cellw,18*$nbrow,"",1);//cadre option
        
        $x=$ptablex+1;
        $count=1;
        $pdf->setfont('arial','',7);
        while($color=$DB_site->fetch_array($rnivsonor)){
            if($count > 5){
                $count = 1;
                $ly+=21.5;
                $x=$ptablex+1;
            }
            $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
            if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
            $pdf->image($caractimg,$x+8,$ly+6,10);
            $pdf->setXY($x-2,$ly+1);
            $pdf->multicell(30,2,$color['libelle'],0,'C',0);
            $color['differenceprix']=unserialize($color['differenceprix']);
            $prixopt=$color['differenceprix'][$site_code];
            $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
            if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
            $pdf->setXY($x+8,$ly+13);
            $pdf->cell(10,4,$txt,0,0,'C');
            if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+13,4,4,'PNG');
		    }
            $x+=28;
            $count++;
        }
        $ly+=18;
        $pdf->setfont('arial','',8);
    }

    //Amenagement bas de cave
    if($rbascave && $DB_site->num_rows($rbascave)>0){
        $nbrow = $DB_site->num_rows($rbascave);
        $nbrow = $nbrow / 5;
        $nbrow = ceil($nbrow);
        if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
        $pdf->setXY($ptablex,$ly);
        $pdf->setfont('arial','B',8);
        $libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=86");
        $pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
        $ly+=5;
        $pdf->setfont('arial','',8);
        $pdf->setXY($ptablex,$ly);
        $pdf->cell($cellw,18*$nbrow,"",1);//cadre option
        
        $x=$ptablex+1;
        $count=1;
        $pdf->setfont('arial','',7);
        while($color=$DB_site->fetch_array($rbascave)){
            if($count > 5){
                $count = 1;
                $ly+=21.5;
                $x=$ptablex+1;
            }
            $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
            if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
            $pdf->image($caractimg,$x+8,$ly+6,10);
            $pdf->setXY($x-2,$ly+1);
            $pdf->multicell(30,2,$color['libelle'],0,'C',0);
            $color['differenceprix']=unserialize($color['differenceprix']);
            $prixopt=$color['differenceprix'][$site_code];
            $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
            if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
            $pdf->setXY($x+8,$ly+13);
            $pdf->cell(10,4,$txt,0,0,'C');
            if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+13,4,4,'PNG');
		    }
            $x+=28;
            $count++;
        }
        $ly+=18;
        $pdf->setfont('arial','',8);
    }

    //Amenagement interieur de cave
    if($rintercave && $DB_site->num_rows($rintercave)>0){
        $nbrow = $DB_site->num_rows($rintercave);
        $nbrow = $nbrow / 5;
        $nbrow = ceil($nbrow);
        if($ly+40*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
        $pdf->setXY($ptablex,$ly);
        $pdf->setfont('arial','B',8);
        $libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=85");
        $pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
        $ly+=5;
        $pdf->setfont('arial','',8);
        $pdf->setXY($ptablex,$ly);
        $pdf->cell($cellw,44*$nbrow,"",1);//cadre option
        
        $x=$ptablex+1;
        $count=1;
        $pdf->setfont('arial','',7);
        while($color=$DB_site->fetch_array($rintercave)){
            if($count > 5){
                $count = 1;
                $ly+=44;
                $x=$ptablex+1;
            }
            $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
            if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
            $pdf->image($caractimg,$x+8,$ly+8,10);
            $pdf->setXY($x-2,$ly+1);
            $pdf->multicell(30,2,$color['libelle'],0,'C',0);
            $color['differenceprix']=unserialize($color['differenceprix']);
            $prixopt=$color['differenceprix'][$site_code];
            $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
            if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
            $pdf->setXY($x+8,$ly+40);
            $pdf->cell(10,4,$txt,0,0,'C');
            if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+40,4,4,'PNG');
		    }
            $x+=28;
            $count++;
        }
        $ly+=44;
        $pdf->setfont('arial','',8);
    }

    //position cave service
    if($rposcaveservice && $DB_site->num_rows($rposcaveservice)>0){
        $nbrow = $DB_site->num_rows($rposcaveservice);
        $nbrow = $nbrow / 5;
        $nbrow = ceil($nbrow);
        if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
        $pdf->setXY($ptablex,$ly);
        $pdf->setfont('arial','B',8);
        $libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=84");
        $pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
        $ly+=5;
        $pdf->setfont('arial','',8);
        $pdf->setXY($ptablex,$ly);
        $pdf->cell($cellw,18*$nbrow,"",1);//cadre option
        
        $x=$ptablex+1;
        $count=1;
        $pdf->setfont('arial','',7);
        while($color=$DB_site->fetch_array($rposcaveservice)){
            if($count > 5){
                $count = 1;
                $ly+=21.5;
                $x=$ptablex+1;
            }
            $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
            if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
            $pdf->image($caractimg,$x+8,$ly+6,10);
            $pdf->setXY($x-2,$ly+1);
            $pdf->multicell(30,2,$color['libelle'],0,'C',0);
            $color['differenceprix']=unserialize($color['differenceprix']);
            $prixopt=$color['differenceprix'][$site_code];
            $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
            if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
            $pdf->setXY($x+8,$ly+13);
            $pdf->cell(10,4,$txt,0,0,'C');
            if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+13,4,4,'PNG');
		    }
            $x+=28;
            $count++;
        }
        $ly+=18;
        $pdf->setfont('arial','',8);
    }

    //verrou porte
    if($rverrouporte && $DB_site->num_rows($rverrouporte)>0){
        $nbrow = $DB_site->num_rows($rverrouporte);
        $nbrow = $nbrow / 5;
        $nbrow = ceil($nbrow);
        if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
        $pdf->setXY($ptablex,$ly);
        $pdf->setfont('arial','B',8);
        $libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=65");
        $pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
        $ly+=5;
        $pdf->setfont('arial','',8);
        $pdf->setXY($ptablex,$ly);
        $pdf->cell($cellw,35*$nbrow,"",1);//cadre option
        
        $x=$ptablex+1;
        $count=1;
        $pdf->setfont('arial','',7);
        while($color=$DB_site->fetch_array($rverrouporte)){
            if($count > 5){
                $count = 1;
                $ly+=21.5;
                $x=$ptablex+1;
            }
            $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
            if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
            $pdf->image($caractimg,$x+8,$ly+6,10);
            $pdf->setXY($x-2,$ly+1);
            $pdf->multicell(30,2,$color['libelle'],0,'C',0);
            $color['differenceprix']=unserialize($color['differenceprix']);
            $prixopt=$color['differenceprix'][$site_code];
            $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
            if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
            $pdf->setXY($x+8,$ly+13);
            $pdf->cell(10,40,$txt,0,0,'C');
            if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+13,4,4,'PNG');
		    }
            $x+=28;
            $count++;
        }
        $ly+=18+17;
        $pdf->setfont('arial','',8);
    }

    //finition du sol interieur
    if($rsolinter && $DB_site->num_rows($rsolinter)>0){
        $nbrow = $DB_site->num_rows($rsolinter);
        $nbrow = $nbrow / 5;
        $nbrow = ceil($nbrow);
        if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
        $pdf->setXY($ptablex,$ly);
        $pdf->setfont('arial','B',8);
        $libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=67");
        $pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
        $ly+=5;
        $pdf->setfont('arial','',8);
        $pdf->setXY($ptablex,$ly);
        $pdf->cell($cellw,18*$nbrow,"",1);//cadre option
        
        $x=$ptablex+1;
        $count=1;
        $pdf->setfont('arial','',7);
        while($color=$DB_site->fetch_array($rsolinter)){
            if($count > 5){
                $count = 1;
                $ly+=21.5;
                $x=$ptablex+1;
            }
            $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
            if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
            $pdf->image($caractimg,$x+8,$ly+6,10);
            $pdf->setXY($x-2,$ly+1);
            $pdf->multicell(30,2,$color['libelle'],0,'C',0);
            $color['differenceprix']=unserialize($color['differenceprix']);
            $prixopt=$color['differenceprix'][$site_code];
            $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
            if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
            $pdf->setXY($x+8,$ly+13);
            $pdf->cell(10,4,$txt,0,0,'C');
            if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+13,4,4,'PNG');
		    }
            $x+=28;
            $count++;
        }
        $ly+=18;
        $pdf->setfont('arial','',8);
    }

    //habillage groupe de froid
    if($rhabgroupefroid && $DB_site->num_rows($rhabgroupefroid)>0){
        $nbrow = $DB_site->num_rows($rhabgroupefroid);
        $nbrow = $nbrow / 5;
        $nbrow = ceil($nbrow);
        if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
        $pdf->setXY($ptablex,$ly);
        $pdf->setfont('arial','B',8);
        $libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=64");
        $pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
        $ly+=5;
        $pdf->setfont('arial','',8);
        $pdf->setXY($ptablex,$ly);
        $pdf->cell($cellw,20*$nbrow,"",1);//cadre option
        
        $x=$ptablex+1;
        $count=1;
        $pdf->setfont('arial','',7);
        while($color=$DB_site->fetch_array($rhabgroupefroid)){
            if($count > 5){
                $count = 1;
                $ly+=21.5;
                $x=$ptablex+1;
            }
            $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
            if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
            $pdf->image($caractimg,$x+8,$ly+6,10);
            $pdf->setXY($x-2,$ly+1);
            $pdf->multicell(30,2,$color['libelle'],0,'C',0);
            $color['differenceprix']=unserialize($color['differenceprix']);
            $prixopt=$color['differenceprix'][$site_code];
            $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
            if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
            $pdf->setXY($x+8,$ly+13);
            $pdf->cell(10,11,$txt,0,0,'C');
            if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+13,4,4,'PNG');
		    }
            $x+=28;
            $count++;
        }
        $ly+=20;
        $pdf->setfont('arial','',8);
    }

    //personalisation du distributeur
    if($rperso && $DB_site->num_rows($rperso)>0){
        $nbrow = $DB_site->num_rows($rperso);
        $nbrow = $nbrow / 5;
        $nbrow = ceil($nbrow);
        if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
        $pdf->setXY($ptablex,$ly);
        $pdf->setfont('arial','B',8);
        $libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=30");
        $pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
        $ly+=5;
        $pdf->setfont('arial','',8);
        $pdf->setXY($ptablex,$ly);
        $pdf->cell($cellw,36*$nbrow,"",1);//cadre option
        
        $x=$ptablex+1;
        $count=1;
        $pdf->setfont('arial','',7);
        while($color=$DB_site->fetch_array($rperso)){
            if($count > 5){
                $count = 1;
                $ly+=21.5;
                $x=$ptablex+1;
            }
            $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
            if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
            $pdf->image($caractimg,$x+8,$ly+6,10);
            $pdf->setXY($x-2,$ly+1);
            $pdf->multicell(30,2,$color['libelle'.$template->langue],0,'C',0);
            $color['differenceprix']=unserialize($color['differenceprix']);
            $prixopt=$color['differenceprix'][$site_code];
            $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
            if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
            $pdf->setXY($x+8,$ly+13);
            $pdf->cell(10,42,$txt,0,0,'C');
            if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+13,4,4,'PNG');
		    }
            $x+=28;
            $count++;
        }
        $ly+=36;
        $pdf->setfont('arial','',8);
    }

    //carrosserie 105
    if($carrosserie && $DB_site->num_rows($carrosserie)>0){
        $nbrow = $DB_site->num_rows($carrosserie);
        $nbrow = $nbrow / 5;
        $nbrow = ceil($nbrow);
        if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
        $pdf->setXY($ptablex,$ly);
        $pdf->setfont('arial','B',8);
        $libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=105");
        $pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
        $ly+=5;
        $pdf->setfont('arial','',8);
        $pdf->setXY($ptablex,$ly);
        $pdf->cell($cellw,18*$nbrow,"",1);//cadre option
        
        $x=$ptablex+1;
        $count=1;
        $pdf->setfont('arial','',7);
        while($color=$DB_site->fetch_array($carrosserie)){
            if($count > 5){
                $count = 1;
                $ly+=21.5;
                $x=$ptablex+1;
            }
            $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
            if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
            $pdf->image($caractimg,$x+8,$ly+4,10);
            $pdf->setXY($x-2,$ly+1);
            $pdf->multicell(30,2,$color['libelle'.$template->langue],0,'C',0);
            $color['differenceprix']=unserialize($color['differenceprix']);
            $prixopt=$color['differenceprix'][$site_code];
            $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
            if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
            $pdf->setXY($x+8,$ly+11);
            $pdf->cell(10,10,$txt,0,0,'C');
            if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+10,4,4,'PNG');
		    }
            $x+=28;
            $count++;
        }
        $ly+=18;
        $pdf->setfont('arial','',8);
    }

    //faÃ§ade friax 104
    if($facadefriax && $DB_site->num_rows($facadefriax)>0){
        $nbrow = $DB_site->num_rows($facadefriax);
        $nbrow = $nbrow / 5;
        $nbrow = ceil($nbrow);
        if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
        $pdf->setXY($ptablex,$ly);
        $pdf->setfont('arial','B',8);
        $libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=104");
        $pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
        $ly+=5;
        $pdf->setfont('arial','',8);
        $pdf->setXY($ptablex,$ly);
        $pdf->cell($cellw,22*$nbrow,"",1);//cadre option
        
        $x=$ptablex+1;
        $count=1;
        $pdf->setfont('arial','',7);
        while($color=$DB_site->fetch_array($facadefriax)){
            if($count > 5){
                $count = 1;
                $ly+=21.5;
                $x=$ptablex+1;
            }
            $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
            if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
            $pdf->image($caractimg,$x+8,$ly+6,10);
            $pdf->setXY($x-2,$ly+1);
            $pdf->multicell(30,2,$color['libelle'.$template->langue],0,'C',0);
            $color['differenceprix']=unserialize($color['differenceprix']);
            $prixopt=$color['differenceprix'][$site_code];
            $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
            if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
            $pdf->setXY($x+8,$ly+13);
            $pdf->cell(10,15,$txt,0,0,'C');
            if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+13,4,4,'PNG');
		    }
            $x+=28;
            $count++;
        }
        $ly+=26;
        $pdf->setfont('arial','',8);
    }

    //eclairage led friax 103
    if($retroeclairage && $DB_site->num_rows($retroeclairage)>0){
        $nbrow = $DB_site->num_rows($retroeclairage);
        $nbrow = $nbrow / 5;
        $nbrow = ceil($nbrow);
        if($ly+22*$nbrow>280){
            optionhead($pdf,$article,$marque);
            $ly=35;
	 	}
	 	
        $pdf->setXY($ptablex,$ly);
        $pdf->setfont('arial','B',8);
        $libcaract = $DB_site->query_first("SELECT libelle".$template->langue." as libcaract FROM caracteristique WHERE caractid=103");
        $pdf->cell($cellw,5,$libcaract[0],0,0,'C',0);
        $ly+=5;
        $pdf->setfont('arial','',8);
        $pdf->setXY($ptablex,$ly);
        $pdf->cell($cellw,24*$nbrow,"",1);//cadre option
        
        $x=$ptablex+1;
        $count=1;
        $pdf->setfont('arial','',7);
        while($color=$DB_site->fetch_array($retroeclairage)){
            if($count > 5){
                $count = 1;
                $ly+=21.5;
                $x=$ptablex+1;
            }
            $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/".$color['caractvalid'].".jpg";
            if(!file_exists($caractimg)) $caractimg=$rootpath."configurations/".$host."/images/caracteristiquevaleur/nc.jpg";
            $pdf->image($caractimg,$x+8,$ly+6,10);
            $pdf->setXY($x-2,$ly+1);
            $pdf->multicell(30,2,$color['libelle'.$template->langue],0,'C',0);
            $color['differenceprix']=unserialize($color['differenceprix']);
            $prixopt=$color['differenceprix'][$site_code];
            $template->pro?$prix = "+".$template->price_format($prixopt,2,false,false,'',true):$prix="+".$template->price_format($prixopt,2,false);
            if(!$afficheprixopt) {
		    	$txt="";
		    }elseif($prixopt == 0){
		    	$txt=$template->translate("/devis/offert","F");
		    }else{
		    	$txt=$prix;
		    }
            $pdf->setXY($x+8,$ly+13);
            $pdf->cell(10,15,$txt,0,0,'C');
            if(in_array($color['caractvalid'], $lescaractsval)){
		    	$img = $rootpath."configurations/".$host."/images/templates/validation.png";
	   			$pdf->Image($img,$pdf->getX()+1,$ly+13,4,4,'PNG');
		    }
            $x+=28;
            $count++;
        }
        $ly+=20;
        $pdf->setfont('arial','',8);
    }

?>
