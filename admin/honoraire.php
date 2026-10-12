<?php

function CleanFiles($dir) 
	{
    $t = time () ;
    $h = opendir ($dir) ;
    while ($file = readdir($h))
    	{
        if (substr ($file, 0, 3) == 'tmp' and substr ($file, -4) == '.pdf')
        	{
            $path = $dir.'/'.$file ;
            if ($t - filemtime ($path) > 30)
				{
                @unlink($path) ;
				}
			}
		}
    closedir ($h) ;
	} 

function french_date($joindate)
	{ 
	$monthes = array('', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'); 
	$days = array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'); 
	return $days[date("w",$joindate)].' '.date("d",$joindate).' '.$monthes[date("n",$joindate) ].' '.date("Y",$joindate); 
	}

require "./admin_global.php";

$honoraire=$DB_site2->query_first("SELECT * from affilie_honoraires where honoraireid = '$honoraireid'") ;
$affilie=$DB_site2->query_first("SELECT * from affilie_lot vl, affilie va, pays p 
							where p.paysid = va.paysid and va.affilieid = vl.affilieid and honoraireid = '$honoraireid'") ;

$boutique=$DB_site->query_first("SELECT * FROM client WHERE username = '$aro_client_username'");


define ('FPDF_FONTPATH','./pdf/') ;
include ('../../boutique/admin/./pdf/fpdf.php') ;

if ($affilie[raisonsociale] != "")
	{
	class PDF extends FPDF 
		{
		function Header() 
			{
			//$this->Image('./pdf/logo_arobases.jpg',10,10,40);
			$this->SetFont('Arial','B',25);
			$this->Cell(100);
			$this->Cell(80,25,'FACTURE',0,0,'C');
			$this->Ln(25);
			}
		function Footer() 
			{
			//$this->Image('./pdf/bas_page.jpg',10,280,190);
			}
		}
	}
else
	{
	class PDF extends FPDF 
		{
		function Header() 
			{
			//$this->Image('./pdf/logo_arobases.jpg',10,10,40);
			$this->SetFont('Arial','B',25);
			$this->Cell(100);
			$this->Cell(80,25,'FEUILLE HONORAIRES',0,0,'C');
			$this->Ln(25);
			}
		function Footer() 
			{
			//$this->Image('./pdf/bas_page.jpg',10,280,190);
			}
		}
	}

$ajd = date('d/m/Y') ;

if ($affilie[raisonsociale] != "")
	{
	$affilie[siret] = "Siret : $affilie[siret]" ;
	$affilie[raisonsociale] = "($affilie[raisonsociale])" ;
	}

$pdf=new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle("Feuille d'honoraires");
$pdf->SetAuthor("Arobases vendre-en-ligne.fr");
$pdf->SetFillColor(230,230,230);
$pdf->Ln(5);
$pdf->Cell(95,5,"",0,0,'',1);
$pdf->Cell(95,5,"",0,0,'',1);
$pdf->Ln(5);
$pdf->SetFont('Arial','U',10);
$pdf->Cell(95,5,"Expediteur",0,0,'',1);
$pdf->Cell(95,5,"Destinataire",0,0,'R',1);
$pdf->Ln(5);
$pdf->SetFont('Arial','',10);
$pdf->Cell(95,5,"$honoraire[nom] $honoraire[prenom]",0,0,'',1);
$pdf->Cell(95,5,"$boutique[raison_sociale]",0,0,'R',1);
$pdf->Ln(5);
$pdf->Cell(95,5,"$affilie[adresse]",0,0,'',1);
$pdf->Cell(95,5,"$boutique[adresse]",0,0,'R',1);
$pdf->Ln(5);
$pdf->Cell(95,5,"$affilie[codepostal] $affilie[ville]",0,0,'',1);
$pdf->Cell(95,5,"$boutique[codepostal] $boutique[ville]",0,0,'R',1);
$pdf->Ln(5);
$pdf->Cell(95,5,"Tel : $affilie[telephone]",0,0,'',1);
$pdf->Cell(95,5,"$boutique[telephone]",0,0,'R',1);
$pdf->Ln(5);
$pdf->Cell(95,5,"$affilie[libelle]",0,0,'',1);
$pdf->Cell(95,5,"FRANCE",0,0,'R',1);
$pdf->Ln(5);
$pdf->Cell(95,5,"",0,0,'',1);
$pdf->Cell(95,5,"Siret : $boutique[siret]",0,0,'R',1);
$pdf->Ln(5);
$pdf->Image('./pdf/hr.jpg',10,40,190);
$pdf->Image('./pdf/hr2.jpg',10,80,190);
$pdf->Ln(5);


$num = $honoraireid ;
while (strlen ($num) < 6)
	{
	$num = "0".$num ;
	}

$pdf->SetFillColor(250,200,150);
$pdf->Cell(30,5,"Feuille hono. N°",1,0,'R',1);
$pdf->SetFillColor(255,255,255);
$pdf->Cell(50,5,"HONO $num",1,0,'C',1);
$pdf->Ln(5);
$pdf->SetFillColor(250,200,150);
$pdf->Cell(30,5,"Date d'édition",1,0,'R',1);
$pdf->SetFillColor(255,255,255);
$pdf->Cell(50,5,"$ajd",1,0,'C',1);
$pdf->Ln(10);

$pdf->SetFillColor(255,255,255);
$pdf->Cell(5,5,"",0,0,'',1);
$pdf->SetFillColor(250,150,100);
$pdf->Cell(150,5,"Désignation",1,0,'C',1);
$pdf->Cell(30,5,"Montant",1,0,'C',1);
$pdf->Ln(5);


$lots=$DB_site2->query("SELECT * FROM affilie_lot WHERE honoraireid = '$honoraireid'") ;

while ($lot=$DB_site2->fetch_array($lots)) 
	{
	$lot[date] = substr ($lot[date], 8, 2) . "/" . substr ($lot[date], 5, 2) . "/" . substr ($lot[date], 0, 4) ;
	$mtt = number_format ($lot[pourcentage] * $lot[montant] / 100, 2, ",", " ") ;
	$lot[montant] = number_format ($lot[montant], 2, ",", " ") ;
	$pdf->SetFillColor(255,255,255);
	$pdf->Cell(5,5,"",0,0,'',1);
	$pdf->Cell(150,5," - Facture n° $lot[factureid] du $lot[date] : $lot[pourcentage] % sur $lot[montant] €" ,1,0,'',1);
	$pdf->Cell(30,5,"$mtt €",1,0,'R',1);
	$pdf->Ln(5);
	}

if ($DB_site2->num_rows($lots) < 10)
	{
	$i = $DB_site2->num_rows($lots) ;
	while ($i <= 10)
		{
		$i++ ;
		$pdf->SetFillColor(255,255,255);
		$pdf->Cell(5,5,"",0,0,'',1);
		$pdf->Cell(150,5,"" ,1,0,'',1);
		$pdf->Cell(30,5,"",1,0,'R',1);
		$pdf->Ln(5);
		$pdf->SetFillColor(255,255,255);
		$pdf->Cell(5,5,"",0,0,'',1);
		$pdf->Cell(150,5,"" ,1,0,'',1);
		$pdf->Cell(30,5,"",1,0,'R',1);
		$pdf->Ln(5);
		}
	}


$pdf->SetFillColor(255,255,255);
$pdf->Cell(5,5,"",0,0,'',1);
$pdf->SetFillColor(250,200,150);
$pdf->Cell(150,5,"Total T.T.C. :",1,0,'R',1);
$pdf->SetFillColor(250,150,100);
$pdf->Cell(30,5,"$honoraire[montant] €",1,0,'R',1);

if ($affilie[raisonsociale] != "")
	{
	$pdf->Ln(5);
	$tva = $honoraire[montant] / 1.196 ;
	$tva = number_format ($tva, 2, ",", " ") ;
	$pdf->SetFillColor(255,255,255);
	$pdf->Cell(5,5,"",0,0,'',1);
	$pdf->SetFillColor(250,200,150);
	$pdf->Cell(150,5,"Dont TVA :",1,0,'R',1);
	$pdf->SetFillColor(250,150,100);
	$pdf->Cell(30,5,"$tva €",1,0,'R',1);
	}
$pdf->Ln(15);


if ($affilie[raisonsociale] == "")
	{
	$txt_bas = "TVA non applicable selon article 293b du CGI." ;
	}

$pdf->SetFillColor(255,255,255);
$pdf->Cell(190,5,"Conditions de versement des honoraires:",0,0,'',1);
$pdf->Ln(5);
$pdf->Cell(190,5,"Par $honoraire[moyen] quinze jours après demande.",0,0,'',1);
$pdf->Ln(5);
$pdf->Cell(190,5,"$txt_bas",0,0,'',1);
$pdf->Ln(10);
$pdf->SetFillColor(255,255,255);
$pdf->Cell(190,5,"Retrouvez cette feuille d'honoraires à tout moment :",0,0,'',1);
$pdf->Ln(5);
$pdf->Cell(190,5,"http://vendre-en-ligne.fr/admin/honoraire.php?honoraireid=$honoraireid",0,0,'',1);
$pdf->Ln(10);

$file = basename (tempnam ("/home/arobases/www/boutique/admin/pdf", "tmp")) ;
$pdffile = "./pdf/".$file.".pdf" ;
rename ("/home/arobases/www/boutique/admin/pdf/$file", "/home/arobases/www/boutique/admin/pdf/$file.pdf") ;
$pdf->Output ($pdffile) ;

CleanFiles ("./pdf/") ;
echo "<HTML><SCRIPT>document.location = '$pdffile';</SCRIPT></HTML>" ;

?>