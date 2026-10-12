<?php

include $_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php";

if (!isset($langue))
	{
	$langue = "FR" ;
	}

function CleanFiles($dir) 
	{
    $t=time();
    $h=opendir($dir);
    while($file=readdir($h))
		{
        if(substr($file,0,3)=='tmp' and substr($file,-4)=='.pdf')
			{
            $path=$dir.'/'.$file;
            if($t-filemtime($path)>30)
				{
                @unlink($path);
				}
			}
		}
    closedir($h);
	} 

function french_date($joindate )
	{ 
	$monthes = array('', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'); 
	$days = array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'); 
	return $days[date("w",$joindate)].' '.date("d",$joindate).' '.$monthes[date("n",$joindate) ].' '.date("Y",$joindate); 
	}


$libelle = "libelle$langue";

/*$facture=$DB_site2->query_first("SELECT * FROM facture f, pays p WHERE facturid = '$facturid' and f.paysid = p.paysid");
$user=$DB_site2->query_first("SELECT * FROM utilisateur u, pays p WHERE userid = '$facture[userid]' and u.paysid = p.paysid");
$numerofacture = $facture[codenumerofacture].$facture[numerofacture];
$moyenpaiement=$DB_site2->query_first("SELECT * FROM moyenpaiement WHERE moyenid = '$facture[moyenid]'");
$coord_commercant=$DB_site2->query_first("SELECT * from parametresgeneraux");
$coord_commercant[siret] = "RCS Grenoble 420 977 217" ;
$coord_commercant[tva] = "N° intracommunautaire : FR 0542097721700025" ;*/

if (isset($datedebut) and isset($datefin) and $datefin >= $datedebut) 
	{
	$coord_commercant=$DB_site2->query_first("SELECT mail_contact mail, raison_sociale, nom_contact, adresse, cp codepostal, ville, siret, tva, telephone, fax FROM parametresgeneraux");

	$cde = "1" ;
	$deb = explode("/", $datedebut);
	$fin = explode("/", $datefin);
	if ($etatcommande == "-1")
		{
		$liste_etats = "" ;
		}
	else
		{
		switch ($etatcommande)
			{
			case '0':
				$liste_etats = "and valide = '0' and moyenid NOT IN (0, 1)" ;
				$cde = "1" ;
				break;
			case 'XX':
				$liste_etats = "and valide = '0' and moyenid = '1'" ;
				$cde = "1" ;
				break;
			default:
				$liste_etats = "and valide = '$etatcommande'" ;
				$cde = "0" ;
				break;
			}
		if ($etatcommande != 5)
			{
			$cde = "1" ;
			}
		}

	define('FPDF_FONTPATH',$rootpath."configurations/$host/factures/");
	include($rootpath."configurations/$host/factures/fpdf.php");
	include $rootpath."configurations/$host/config.php";
	include $rootpath."configurations/$host/langue$langue.php";

	class PDF extends FPDF 
		{
		function Header() 
			{
			
			global $host,$rootpath, $langue,$cde ;
			include $rootpath."configurations/$host/config.php";
			include $rootpath."configurations/$host/langue$langue.php";
			$this->SetFont('Arial','B',25);
			$this->Cell(100);
			if (@file_exists($rootpath."configurations/$host/factures/logo.jpg"))
				{
				$this->Image($rootpath."configurations/$host/factures/logo.jpg",15,10,50);
				}
			/*if($cde=="1") {
				$this->Cell(80,25,"$multilangue[bondecommande]",0,0,'C');
			} else {*/
				$this->Cell(80,25,"$multilangue[facture]",0,0,'C');
			//}
			$this->Ln(35);
			}
		function Footer() 
			{
		//	$this->Image("$rootpath/configurations/$host/factures/bas_page.jpg",10,280,190);
			}
		}

	$pdf=new PDF();
	$pdf->AliasNbPages();

	$factures=$DB_site2->query("SELECT * 
								FROM facture f inner join pays p on (f.paysid = p.paysid) 
								WHERE datefacture >= '$deb[2]-$deb[1]-$deb[0]' and datefacture <= '$fin[2]-$fin[1]-$fin[0]' $liste_etats
								ORDER BY facturid");
	if ($DB_site2->num_rows($factures) == 0)
		{
		$pdf->AddPage();
		$pdf->SetTitle("$multilangue[facture]");
		$pdf->SetAuthor("Arobases");
		$pdf->SetFillColor(255,255,255);
		$pdf->Cell(20,5,"",0,0,'',1);
		$pdf->SetFillColor(230,230,230);
		$pdf->SetFont('Arial','U',8);
		$pdf->Cell(150,5,"Aucune facture pour ces dates",0,0,'C',1);
		$pdf->Ln(5);
		}
	while ($facture=$DB_site2->fetch_array($factures))
		{
		$pdf->AddPage();
		$pdf->SetTitle("$multilangue[facture]");
		$pdf->SetAuthor("Arobases");
		$pdf->SetFillColor(230,230,230);

		$user=$DB_site2->query_first("SELECT * FROM utilisateur u, pays p WHERE userid = '$facture[userid]' and u.paysid = p.paysid");
		$numerofacture = $facture[codenumerofacture].$facture[numerofacture];
		$moyenpaiement=$DB_site2->query_first("SELECT * FROM moyenpaiement WHERE moyenid = '$facture[moyenid]'");
		$facturid = $facture[facturid];
	
		$pdf->SetFont('Arial','U',8);
		$pdf->Cell(70,5,"$multilangue[expediteur] :",0,0,'',1);
		$pdf->Cell(60,5,"Coordonnées de livraison :",0,0,'',1);
		$pdf->Cell(60,5,"Coordonnées de facturation :",0,0,'',1);
		$pdf->Ln(5);
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(70,5,"$coord_commercant[raison_sociale]",0,0,'',1);
		$pdf->Cell(60,5,"$facture[raisonsociale]",0,0,'',1);
		$pdf->Cell(60,5,"$user[raisonsociale]",0,0,'',1);
		$pdf->Ln(5);
		$pdf->Cell(70,5,"$coord_commercant[adresse]",0,0,'',1);
		$pdf->Cell(60,5,"$facture[nom] $facture[prenom]",0,0,'',1);
		$pdf->Cell(60,5,"$user[nom] $user[prenom]",0,0,'',1);
		$pdf->Ln(5);
		$pdf->Cell(70,5,"$coord_commercant[codepostal] $coord_commercant[ville]",0,0,'',1);
		$pdf->Cell(60,5,"$facture[adresse]",0,0,'',1);
		$pdf->Cell(60,5,"$user[adresse]",0,0,'',1);
		$pdf->Ln(5);
		$pdf->Cell(70,5,"FRANCE",0,0,'',1);
		$pdf->Cell(60,5,"$facture[codepostal] $facture[ville]",0,0,'',1);
		$pdf->Cell(60,5,"$user[codepostal] $user[ville]",0,0,'',1);
		$pdf->Ln(5);
		$pdf->Cell(70,5,"Tel : $coord_commercant[telephone]",0,0,'',1);
		$pdf->Cell(60,5,"$facture[libelle]",0,0,'',1);
		$pdf->Cell(60,5,"$user[libelle]",0,0,'',1);
		$pdf->Ln(5);
		$pdf->Cell(70,5,"Fax : $coord_commercant[fax]",0,0,'',1);
		$pdf->Cell(60,5,"$facture[telephone]",0,0,'',1);
		$pdf->Cell(60,5,"$user[telephone]",0,0,'',1);
		$pdf->Ln(5);
		$pdf->Cell(70,5,"contact@ma-cave-a-vin.fr ",0,0,'',1);
		$pdf->Cell(60,5,"$facture[mail]",0,0,'',1);
		$pdf->Cell(60,5,"$user[mail]",0,0,'',1);
		$pdf->Ln(5);
		$pdf->Cell(70,5,"N° SIRET $coord_commercant[siret]",0,0,'',1);
		$pdf->Cell(60,5,"",0,0,'',1);
		$pdf->Cell(60,5,"",0,0,'',1);
		$pdf->Ln(10);
	
		while (strlen($facturid) < 8)
			{
			$facturid = "0".$facturid ;
			}
	
		$pdf->SetFillColor(250,200,150);
		if ($cde == "1") 
			{
			$pdf->Cell(30,5,"Bon de commande N°",1,0,'R',1);
			} 
		else 
			{
			$pdf->Cell(30,5,"Facture N°",1,0,'R',1);
			}
		$pdf->SetFillColor(255,255,255);
		if ($cde == "1") 
			{
			$pdf->Cell(50,5,"$facturid",1,0,'C',1);
			} 
		else 
			{
			$pdf->Cell(50,5,"$numerofacture",1,0,'C',1);
			$pdf->Ln(5);
			$pdf->SetFillColor(250,200,150);
			$pdf->Cell(30,5,"Bon de commande N°",1,0,'R',1);
			$pdf->SetFillColor(255,255,255);
			$pdf->Cell(50,5,"$facturid",1,0,'C',1);
			}
		$pdf->Ln(5);
		$pdf->SetFillColor(250,200,150);
		$pdf->Cell(30,5,"Code client",1,0,'R',1);
		$pdf->SetFillColor(255,255,255);
		$pdf->Cell(50,5,"$facture[userid]",1,0,'C',1);
		$pdf->Ln(5);
		$pdf->SetFillColor(250,200,150);
		$pdf->Cell(30,5,"$multilangue[dateeedition]",1,0,'R',1);
		$pdf->SetFillColor(255,255,255);
		$dateafftmp=explode("-",$facture[datefacture]);
		$dateaff="$dateafftmp[2]/$dateafftmp[1]/$dateafftmp[0]";
		$pdf->Cell(50,5,"$dateaff",1,0,'C',1);
		$pdf->Ln(5);
		$pdf->SetFillColor(250,200,150);
		$pdf->Cell(30,5,"$multilangue[page]",1,0,'R',1);
		$pdf->SetFillColor(255,255,255);
		$pdf->Cell(50,5,"1",1,0,'C',1);
		$pdf->Ln(10);
	
		$pdf->SetFillColor(250,150,100);
		$pdf->Cell(30,5,"$multilangue[ref]",1,0,'C',1);
		$pdf->Cell(80,5,"$multilangue[designation]",1,0,'C',1);
		$pdf->Cell(20,5,"$multilangue[puht]",1,0,'C',1);
		$pdf->Cell(20,5,"$multilangue[quantite]",1,0,'C',1);
		$pdf->Cell(15,5,"$multilangue[tva]",1,0,'C',1);
		$pdf->Cell(25,5,"$multilangue[montantttc]",1,0,'C',1);
		$pdf->Ln(5);
	
	
		$lignefactures=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid = '$facturid' order by artcode");
		$total = 0 ;
		$tva = 0 ;
		$i=0;
		$j=1;
	
		while ($lignefacture=$DB_site2->fetch_array($lignefactures))
			{
			$des_caracts = "" ;
			$caracts=$DB_site2->query("SELECT * FROM lignefacturecaracteristique WHERE lignefactureid = '$lignefacture[lignefactureid]'");
			while ($caract=$DB_site2->fetch_array($caracts))
				{
				$des_caracts .= "$caract[libelle_caract] : $caract[libelle_caractval]," ;
				}
			if ($des_caracts != "")
				{
				$des_caracts = " (" . substr($des_caracts, 0, -1) . ")" ;
				}
			$libmarque=$DB_site2->query_first("SELECT * from article_marque am, marque m where am.marqueid = m.marqueid and am.artid = '$lignefacture[artid]'");	
			$libmodele = $DB_site2->query_first("SELECT modele FROM article WHERE artid = '$lignefacture[artid]'");	
			$designation = html_entity_decode($lignefacture[libelle]." ".$libmarque[libelle]." ".$libmodele[0]) . $des_caracts ;
	
			$puht = ($lignefacture[prix] / (1 + $lignefacture[tva] / 100)) ;
			$puht = number_format($puht, 2, ',', ' ') ;
			$qte = $lignefacture[qte] ;
			$txtva = $lignefacture[tva] ;
			$tva += ($lignefacture[prix] * $qte) - (($lignefacture[prix] * $qte) / (1 + $lignefacture[tva] / 100)) ;
			$montant_ttc = $lignefacture[prix] * $qte ;
			if ($lignefacture[remboursement] != '1')
				{
				$total += $montant_ttc ;
				}
			$montant_ttc = number_format($montant_ttc, 2, ',', ' ') ;
			$pdf->SetFillColor(255,255,255);
			$i++ ;
			if (isset($affichetitre2facture) and $affichetitre2facture == 1)
				{
				$titre = $DB_site2->query_first("SELECT $titre2 FROM article WHERE artid = '$lignefacture[artid]'"); 
				if ($titre[$titre2] != "")
					{
					$titre[$titre2] = "(".$titre[$titre2].")";
					}
				$height = 5 ;
				$nbre = ceil(strlen($designation." ".$titre[$titre2])/60) ;
				if ($nbre > 1)
					{
					$height = $nbre * 5 ;
					}
				$pdf->SetFont('Arial','',8);
				$pdf->Cell(30,$height,stripslashes("$lignefacture[artcode]"),1,0,'',1);
		
		
				if ($nbre > 1)
					{
					$pdf->MultiCell(80,5,"$designation $titre[$titre2]" ,1,'',1);
					$pdf->Enleve($height-5);
					}
				else
					{
					$pdf->Cell(80,5,"$designation $titre[$titre2]" ,1, 0, '',1);
					}
				$pdf->SetFont('Arial','',10);
				$pdf->Cell(20,$height,"$puht € " ,1,0,'R',1);
				$pdf->Cell(20,$height,"$qte" ,1,0,'C',1);
				$pdf->Cell(15,$height,"$txtva %" ,1,0,'C',1);
				$pdf->Cell(25,$height,"$montant_ttc €",1,0,'R',1);
				if ($nbre > 1)
					{
					$pdf->Ajoute($height-5);
					}
				$pdf->Ln(5);
				}
			else
				{
				$height = 5 ;
				$nbre = ceil(strlen($designation)/60) ;
				if ($nbre > 1)
					$height = $nbre * 5 ;
				$pdf->SetFont('Arial','',8);
				$pdf->Cell(30,$height,stripslashes("$lignefacture[artcode]"),1,0,'',1);
		
		
				if ($nbre > 1)
					{
					$pdf->MultiCell(80,5,"$designation" ,1,'',1);
					$pdf->Enleve($height-5);
					}
				else
					{
					$pdf->Cell(80,5,"$designation" ,1,0,'',1);
					}
				$pdf->SetFont('Arial','',10);
				$pdf->Cell(20,$height,"$puht € " ,1,0,'R',1);
				$pdf->Cell(20,$height,"$qte" ,1,0,'C',1);
				$pdf->Cell(15,$height,"$txtva %" ,1,0,'C',1);
				$pdf->Cell(25,$height,"$montant_ttc €",1,0,'R',1);
				if ($nbre > 1)
					{
					$pdf->Ajoute($height-5);
					}
				$pdf->Ln(5);
				}
	
			
			if ($lignefacture[remboursement] == '1')
				{
				$dateremb = explode("-", $lignefacture[date_remboursement]) ;
				$pdf->SetFont('Arial','',8);
				if ($nbre > 1)
					{
					$pdf->MultiCell(165,5,"Remboursement $designation le $dateremb[2]/$dateremb[1]/$dateremb[0]" ,1,'R',1);
					$pdf->Enleve($height-5);
					}
				else
					{
					$pdf->Cell(165,5,"Remboursement $designation le $dateremb[2]/$dateremb[1]/$dateremb[0]" ,1,0,'R',1);
					}
				$pdf->SetFont('Arial','',10);
				$pdf->Cell(25,$height,"- $montant_ttc €",1,0,'R',1);
				if ($nbre > 1)
					{
					$pdf->Ajoute($height-5);
					}
				$pdf->Ln(5);
				$i++;
				}
			
			
			if (($i==20 and $j==1) OR ($i==30 and $j>1)) 
				{
				$pdf->AddPage();
				$j++;
				$pdf->Ln(5);
				while (strlen($factureid) < 8)
					{
					$factureid = "0".$factureid ;
					}
				$pdf->SetFillColor(250,200,150);
				$pdf->Cell(30,5,"$multilangue[facture] N°",1,0,'R',1);
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(50,5,"$facturid",1,0,'C',1);
				$pdf->Ln(5);
				$pdf->SetFillColor(250,200,150);
				$pdf->Cell(30,5,"$multilangue[dateedition]",1,0,'R',1);
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(50,5,"$dateaff",1,0,'C',1);
				$pdf->Ln(5);
				$pdf->SetFillColor(250,200,150);
				$pdf->Cell(30,5,"$multilangue[page]",1,0,'R',1);
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(50,5,"$j",1,0,'C',1);
				$pdf->Ln(20);
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(15,5,"",0,0,'',1);
				$pdf->SetFillColor(250,150,100);
				$pdf->Cell(80,5,"$multilangue[designation]",1,0,'C',1);
				$pdf->Cell(20,5,"$multilangue[puht]",1,0,'C',1);
				$pdf->Cell(20,5,"$multilangue[quantite]",1,0,'C',1);
				$pdf->Cell(15,5,"$multilangue[tva]",1,0,'C',1);
				$pdf->Cell(25,5,"$multilangue[montantttc]",1,0,'C',1);
				$pdf->Ln(5);
				$i=0;
				}
			}
			
		$i++ ;
		$pdf->SetFillColor(255,255,255);
		$pdf->Cell(30,5,"",1,0,'',1);
		$pdf->Cell(80,5,"" ,1,0,'',1);
		$pdf->Cell(20,5,"" ,1,0,'R',1);
		$pdf->Cell(20,5,"" ,1,0,'C',1);
		$pdf->Cell(15,5,"" ,1,0,'C',1);
		$pdf->Cell(25,5,"",1,0,'R',1);
		$pdf->Ln(5);
	
	
		// remise sut tout catalogue 
		if (in_array ("106", $modules))
			{
			if ($facture[pctremise] != 0)
				{
				$i++ ;
				$fmttremise = $total * ($facture[pctremise] / 100) ;
				$total -= $fmttremise ;
				$pdf->SetFillColor(255,255,255);
				$pdf->SetFont('Arial','',8);
				$pdf->Cell(110,5, "$multilangue[remisede] $facture[pctremise] % $multilangue[surtoutlecat]",1,0,'',1);
				$pdf->SetFont('Arial','',10);
				$pdf->Cell(20,5,"" ,1,0,'R',1);
				$pdf->Cell(20,5,"" ,1,0,'C',1);
				$pdf->Cell(15,5,"" ,1,0,'C',1);
				$pdf->Cell(25,5,"-".number_format($fmttremise, 2, ',', ' ')." €",1,0,'R',1);
				$pdf->Ln(5);
				}
			}
			
		// remise
		if (in_array ("3", $modules))
			{
			if ($facture[remiseid] != 0)
				{
				$infosremise=$DB_site2->query_first("SELECT * from remise where remiseid = '$facture[remiseid]'");
				$article=$DB_site2->query_first("SELECT $libelle from article a, remisearticle ra where a.artid = ra.artid and remiseid = '$facture[remiseid]'");
				if ($article[0] != "")
					{
					$fin = " $multilangue[surlarticle] $article[0]." ;
					}
				else
					{
					$rayon=$DB_site2->query_first("SELECT $libelle from categorie c, remisecategorie rc where c.catid = rc.catid and remiseid = '$facture[remiseid]'");
					if ($rayon[0] != "")
						{
						$fin = " $multilangue[surlesarticlesdurayon] $rayon[0]." ;
						}
					else
						{
						$fin = "." ;
						}
					}
				switch ($infosremise[typeremise])
					{
					case '0': // remise sur le poids
						$libremise = "$multilangue[remise] $infosremise[pctremise] % : $multilangue[poids] > $infosremise[valeur] $multilangue[grammes] $fin" ;
						break;
					case '1': // remise sur le prix
						$libremise = "$multilangue[remise] $infosremise[pctremise] % : $multilangue[prix] > $infosremise[valeur] € $fin" ;
						break;
					case '2': // remise sur la quantité
						$libremise = "$multilangue[remise] $infosremise[pctremise] % : $multilangue[quantite] > $infosremise[valeur] $multilangue[articlesoupas] $fin" ;
						break;
					} // fin du switch
				$i++ ;
				$total -= $facture[montantremise] ;
				$pdf->SetFillColor(255,255,255);
				$pdf->SetFont('Arial','',8);
				$pdf->Cell(110,5, "$libremise",1,0,'',1);
				$pdf->SetFont('Arial','',10);
				$pdf->Cell(20,5,"" ,1,0,'R',1);
				$pdf->Cell(20,5,"" ,1,0,'C',1);
				$pdf->Cell(15,5,"" ,1,0,'C',1);
				$pdf->Cell(25,5,"-".number_format($facture[montantremise], 2, ',', ' ')." €",1,0,'R',1);
				$pdf->Ln(5);
				}
			} 
	
		$tvaremise = 0;
		// Bons de réduction 
		if (in_array("13", $modules))
			{
			if ($facture[cadeauid] != 0)
				{
				$infoscadeau=$DB_site2->query_first("SELECT * FROM cadeau WHERE cadeauid = '$facture[cadeauid]'");
				$article=$DB_site2->query_first("SELECT $libelle from article a, article_cadeau ac where a.artid = ac.artid and cadeauid = '$facture[cadeauid]'");
				if ($article[0] != "")
					{
					$fin = " $multilangue[surlarticle] $article[0]." ;
					}
				else
					{
					$rayon=$DB_site2->query_first("SELECT $libelle from categorie c, categorie_cadeau cc where c.catid = cc.catid and cadeauid = '$facture[cadeauid]'");
					if ($rayon[0] != "")
						{
						$fin = " $multilangue[surlesarticlesdurayon] $rayon[0]." ;
						}
					else
						{
						$fin = "." ;
						}
					}
				switch ($infoscadeau[typecadeauid])
					{
					case '0': // remise sur le poids
						$libcadeau = "$multilangue[bonreduction] $multilangue[de] $infoscadeau[valeurcadeau] € $fin" ;
						break;
					case '1': // remise sur le prix
						$libcadeau = "$multilangue[bonreduction] $multilangue[de] $infoscadeau[valeurcadeau] % $fin" ;
						break;
					}
				$i++ ;
				$total -= $facture[montantcadeau] ;
				
				$tvaremise = $facture[montantcadeau]-($facture[montantcadeau]/1.196);
				
				$pdf->SetFillColor(255,255,255);
				$pdf->SetFont('Arial','',8);
				$pdf->Cell(110,5, "$libcadeau",1,0,'',1);
				$pdf->SetFont('Arial','',10);
				$pdf->Cell(20,5,"" ,1,0,'R',1);
				$pdf->Cell(20,5,"" ,1,0,'C',1);
				$pdf->Cell(15,5,"" ,1,0,'C',1);
				$pdf->Cell(25,5,"-".number_format($facture[montantcadeau], 2, ',', ' ')." €",1,0,'R',1);
				$pdf->Ln(5);
				}
			}
	
	
	
		$i++ ;
		$pdf->SetFillColor(255,255,255);
		$pdf->Cell(30,5,"",1,0,'',1);
		$pdf->Cell(80,5,"" ,1,0,'',1);
		$pdf->Cell(20,5,"" ,1,0,'R',1);
		$pdf->Cell(20,5,"" ,1,0,'C',1);
		$pdf->Cell(15,5,"" ,1,0,'C',1);
		$pdf->Cell(25,5,"",1,0,'R',1);
		$pdf->Ln(5);
		
		// frais de port
		$total += $facture[prixport] ;
		$tva += $facture[prixport] - ($facture[prixport] / (1 + $facture[tvaport]/100)) ;
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(30,5,"" ,1,0,'',1);
		$pdf->Cell(80,5,"$multilangue[fraisport]" ,1,0,'',1);
		$pdf->Cell(20,5,"" ,1,0,'R',1);
		$pdf->Cell(20,5,"" ,1,0,'C',1);
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(15,5,"$facture[tvaport] %" ,1,0,'C',1);
		$pdf->Cell(25,5,number_format($facture[prixport], 2, ',', ' ')." €",1,0,'R',1);
		$pdf->Ln(5);
		
	
		// contre remboursement
		if ($facture[moyenid] == '3' and $facture[prix_contre_remboursement] != 0)
			{
			$i++ ;
			$pdf->SetFillColor(255,255,255);
			$pdf->Cell(30,5,"",1,0,'',1);
			$pdf->Cell(80,5,"" ,1,0,'',1);
			$pdf->Cell(20,5,"" ,1,0,'R',1);
			$pdf->Cell(20,5,"" ,1,0,'C',1);
			$pdf->Cell(15,5,"" ,1,0,'C',1);
			$pdf->Cell(25,5,"",1,0,'R',1);
			$pdf->Ln(5);
		
			$total += $facture[prix_contre_remboursement] ;
			$i++;
			$pdf->SetFillColor(255,255,255);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(110,5,"$multilangue[supplementcontreremb]" ,1,0,'',1);
			$pdf->Cell(20,5,"" ,1,0,'R',1);
			$pdf->Cell(20,5,"" ,1,0,'C',1);
			$pdf->SetFont('Arial','',10);
			$pdf->Cell(15,5,"" ,1,0,'C',1);
			$pdf->Cell(25,5,number_format($facture[prix_contre_remboursement], 2, ',', ' ')." €",1,0,'R',1);
			$pdf->Ln(5);
			}
	
			// ajout des lignes
			while ($i<10) 
				{
				$i++;
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(30,5,"",1,0,'',1);
				$pdf->Cell(80,5,"",1,0,'',1);
				$pdf->Cell(20,5,"",1,0,'',1);
				$pdf->Cell(20,5,"",1,0,'',1);
				$pdf->Cell(15,5,"",1,0,'',1);
				$pdf->Cell(25,5,"",1,0,'',1);
				$pdf->Ln(5);
				}
			while( $j>1 && $i<30 )
				{
				$i++;
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(30,5,"",1,0,'',1);
				$pdf->Cell(80,5,"",1,0,'',1);
				$pdf->Cell(20,5,"",1,0,'',1);
				$pdf->Cell(20,5,"",1,0,'',1);
				$pdf->Cell(15,5,"",1,0,'',1);
				$pdf->Cell(25,5,"",1,0,'',1);
				$pdf->Ln(5);	
				}
	
	
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFillColor(250,200,150);
		$pdf->Cell(165,5,"$multilangue[total] $multilangue[ttc] :",1,0,'R',1);
		$pdf->SetFillColor(250,150,100);
		$pdf->Cell(25,5,number_format($total, 2, ',', ' ')." €",1,0,'R',1);
		$pdf->Ln(5);
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFillColor(250,200,150);
		$pdf->Cell(165,5,"$multilangue[donttva] :",1,0,'R',1);
		$pdf->SetFillColor(250,150,100);
		$pdf->Cell(25,5,number_format($tva-$tvaremise, 2, ',', ' ')." €",1,0,'R',1);
		$pdf->Ln(10);
	
	
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(190,5,"$multilangue[moyenpaiement] : $moyenpaiement[$libelle]",0,0,'',1);
		$pdf->Ln(5);
		if ($cde != "1") 
			{
			$dateexp=explode("-",$facture[dateexpedition]);
			$pdf->Cell(190,5,"Date expédition : $dateexp[2]/$dateexp[1]/$dateexp[0]",0,0,'',1);
			}
		$pdf->Ln(5);
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(190,5,"www.ma-cave-a-vin.fr est la propriété de la société ACILYON - SARL au capital de 8 000 € - APE 741G - Siret 452 947 385 RCS Lyon - ",0,0,'L',1);
		$pdf->Ln(5);
		$pdf->Cell(190,5,"TVA FR 13 452 947 385",0,0,'L',1);
		$pdf->Ln(5);
		$pdf->Cell(190,5,"Réserve de propriété : nous nous réservons la propriété des marchandises jusqu'au paiement intégral de nos factures (loi N°80335 du 12 mai 1980)",0,0,'L',1);
		$pdf->Ln(5);
		$pdf->Cell(190,5,"Loi n° 92.1992 du 31/12/1992, un intérêt de retard égal à 1,5 fois le taux d'intérêt légal sera appliqué au montant de la facture lors d'un paiement tardif",0,0,'L',1);
		}

	// fin
	$file=basename(tempnam("./pdf" , "tmp"));
	$pdffile="./pdf/" . $file . ".pdf";
	rename("./pdf/$file", "./pdf/$file.pdf");
	$pdf->Output($pdffile);

	CleanFiles($rootpath."configurations/$host/factures/");
	echo "<HTML><SCRIPT type=\"text/javascript\">document.location='$pdffile';</SCRIPT></HTML>";
	} 
else 
	{
	echo "$multilangue[erreur] !!!";
	}
?>