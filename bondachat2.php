<?php

$rootpath="/home/macavavin/www/";
$host = $HTTP_HOST;

include_once("global.php");

define('FPDF_FONTPATH',"$rootpath/configurations/$host/factures/");
include_once("$rootpath/configurations/$host/factures/fpdf.php");

$pdf=new FPDF();
$pdf->AliasNbPages();
$pdf->AddPage('L');
$pdf->SetTitle("Bon d'achat");
$pdf->SetAuthor("ma-cave-a-vin");
$pdf->SetAutoPageBreak(false,10);

$code_caviste=$DB_site->query_first("SELECT * FROM code_caviste WHERE code='$code'");
$caviste=$DB_site->query_first("SELECT * FROM caviste WHERE cavisteid=".intval($code_caviste['caviste']));

demipage(-1);
demipage(150);

$pdf->line(148,0,148,5);
$pdf->line(148,205,148,210);

$pdf->output();


function demipage($x=0){
    global $pdf,$rootpath,$host;
    global $caviste,$code_caviste;
    $pdf->image($rootpath."configurations/".$host."/images/templates/bondachat2".$code_caviste['type'].".jpg",$x,0,148);
    $pdf->setfont('arial','B',14);
    $pdf->settextcolor(120,33,33);
    $pdf->setxy($x+32,53);
    $pdf->multicell(12,5,round($code_caviste['pc'])."%",0,'C',0);

    $pdf->setfont('arial','B','12');
    $pdf->settextcolor(255,255,255);
    $pdf->setxy($x+6,72);
    $pdf->multicell(83,6,$caviste['raisonsociale']."\n".$caviste['adresse']."\n".$caviste['codepostal']." ".$caviste['ville'],0,'L',0);

    $pdf->setfont('arial','B',20);
    $pdf->settextcolor(120,33,33);
    $pdf->setxy($x+17,104);
    $pdf->multicell(80,8,$code_caviste['code'],0,'C',0);

    $pdf->setfont('arial','',8);
    $pdf->settextcolor(0,0,0);
    $pdf->setxy($x+3,112);
    $pdf->multicell(60,5,"Date de validité : ".date("d/m/Y",$code_caviste['validite']));

    $pdf->setfont('arial','',8);
    $pdf->setxy($x+1,204);
    $pdf->multicell(148,5,"(1) ".round($code_caviste['pc'])."% sur les articles hors promotion et ".round($code_caviste['pcpromo'])."% sur les articles en promotion - Hors transport et garantie sérénité",0,'C',0);

}

?>