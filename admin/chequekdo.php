<?php
extract($_REQUEST);
extract($_COOKIE);

include_once($_SERVER['DOCUMENT_ROOT']."/class/host.php");
$host_conf = new Host();
$host=$host_conf->host;

include_once("../configurations/$host/config_mysql.php");
include_once("../configurations/".$host."/config_variables.php");
include("../class/mysql.php");

define('FPDF_FONTPATH',"../class/font/");
include_once("../class/fpdf.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

$pdf=new FPDF();
$pdf->AliasNbPages();
$pdf->AddPage('L');
$pdf->SetTitle("Bon d'achat");
$pdf->SetAuthor("ma-cave-a-vin");
$pdf->SetAutoPagebreak(false,10);

$code_caviste=$DB_site->query_first("SELECT * FROM code_caviste WHERE code='$code'");
$caviste=$DB_site->query_first("SELECT * FROM caviste WHERE cavisteid=".intval($code_caviste['caviste']));

$pdf->image("../images/templates/base/FR/flyerpartenaire.jpg",0.5,0,296);

demipage(1);
demipage(150);

$pdf->output();

function demipage($x=0){
    global $pdf,$rootpath,$host;
    global $caviste,$code_caviste;

    $pdf->setfont('arial','B',15);
    $pdf->settextcolor(255);
    $pdf->setxy($x+45.5,31.75);
    $pdf->multicell(54,8,$code_caviste['code'],0,'C',0);

    $pdf->setfont('arial','B',20);
    $pdf->settextcolor(0);
    $pdf->setxy($x+98,42);
    $pdf->multicell(22,5,round($code_caviste['pc'])."%");
    $pdf->setfont('arial','B',23);
    $pdf->setxy($x+108,41);
    $pdf->multicell(22,5,"*");
    
    $pdf->setfont('arial','B',13);
    $pdf->settextcolor(0);
    $pdf->setxy($x+12,49);
    $pdf->multicell(123,5,utf8_decode("à valoir chez notre partenaire :"),0,'C');

    $pdf->setfont('arial','B','12');
    $pdf->settextcolor(136,136,136);
    $pdf->setxy($x+12,58);
    $pdf->multicell(123,4,utf8_decode(stripslashes($caviste['raisonsociale'])),0,'C');
    $pdf->setxy($x+12,64);
    $pdf->setfont('arial','','10');
    $pdf->multicell(123,3,utf8_decode(stripslashes($caviste['adresse'])."\n\n".$caviste['codepostal']." ".$caviste['ville']),0,'C',0);

    $pdf->settextcolor(136,136,136);
    $pdf->setfont('arial','',7);
    $pdf->setxy($x+21,201);
    $pdf->cell(100,4,utf8_decode(round($code_caviste['pc'])."% sur les articles hors promotion et ".round($code_caviste['pcpromo'])."% sur les articles en promotion. Date de validité : ".date("d/m/Y",$code_caviste['validite'])));
    $pdf->setxy($x+14,204);
    $pdf->cell(200,5,utf8_decode("Hors transport, installation spécialisée, garantie sérénité et articles proposés en ventes flash et ventes privées"));
}
 
?>