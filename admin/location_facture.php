<?php

require "./admin_global.php";
$rootpath="/home/arobases/www";

define('FPDF_FONTPATH',"$rootpath/boutique/admin/factures_location/");
include("$rootpath/boutique/admin/factures_location/fpdf.php");

/*
if(__FILE__==$rootpath . "location_facture.php") {
	$outputrootpath=".";
} else {
	$outputrootpath="..";
}
*/

function CleanFiles($dir) {
    $t=time();
    $h=opendir($dir);
    while($file=readdir($h))
    {
        if(substr($file,0,8)=='tmp_loc_' and substr($file,-4)=='.pdf')
        {
            $path=$dir.'/'.$file;
            if($t-filemtime($path)>30)
                @unlink($path);
        }
    }
    closedir($h);
} 


////////////// modification de la classe
class PDF extends FPDF {
	function Header() {
	//	$this->Image("$rootpath/boutique/admin/factures_location/logo_arobases.jpg",10,10,80);
		$this->SetFont('Arial','B',25);
		$this->Cell(100);
		$this->Cell(80,25,'FACTURE',0,0,'C');
		$this->Ln(25);
	}
	function Footer() {
	//	$this->Image("$rootpath/boutique/admin/factures_location/bas_page.jpg",10,280,190);
	}
}
////////////// fin modification de la classe

// debut
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle("FACTURE");
$pdf->SetAuthor("Arobases");
// fin debut

$pdf->SetFillColor(230,230,230);
$pdf->Ln(5);
$pdf->Cell(95,5,"",0,0,'',1);
//$pdf->Cell(95,5,"",0,0,'',1);
$pdf->Ln(5);

// fin
$file=basename(tempnam("$rootpath/boutique/admin/factures_location" , "tmp_loc_"));
$pdffile="$rootpath/boutique/admin/factures_location/" . $file . ".pdf";
rename("$rootpath/boutique/admin/factures_location/$file" , "$rootpath/boutique/admin/factures_location/$file.pdf");
$pdf->Output($pdffile);
// fin fin

CleanFiles("$rootpath/boutique/admin/factures_location/");
$pdffile="/boutique/admin/factures_location/" . $file . ".pdf";
echo "<HTML><SCRIPT>document.location='$pdffile';</SCRIPT></HTML>";

?>