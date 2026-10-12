<?php
ini_set("display_errors","on");

if(!isset($_GET['facturid']) || intval($_GET['facturid']==0)) exit;

if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false){
	$rootpath="d:/wamp/www/fusion";
} else{
        $rootpath=$_SERVER['DOCUMENT_ROOT'];
}
include_once($_SERVER['DOCUMENT_ROOT']."/class/host.php");
$host_conf = new Host();
$host=$host_conf->host;

define('FPDF_FONTPATH', $rootpath.'/class/font/');
include_once("$rootpath/class/fpdf.php");

include_once($rootpath."/configurations/$host/config_mysql.php");
include_once($_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/config_variables.php");
include($rootpath."/class/mysql.php");
include($rootpath."/class/facture.class.php");
include($rootpath."/class/template.class.php");
include($rootpath."/class/devises.class.php");
include($rootpath."/class/fonctions.php");
include($rootpath."/configurations/$host/config.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();



$facture=new facture($DB_site,$_GET['facturid']);
$lignefacture=new lignefacture($DB_site,$_GET['lignefactureid']);
if($lignefacture->facturid!=$facture->facturid) exit;
if(substr($lignefacture->artcode,0,6)!="EC-KDO") exit;

$template=new template($facture->utilisateur->langue,$DB_site);
$template->changesite("base",$facture->utilisateur->langue);

$pdf=new FPDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle($template->translate("/cheque/cheque_cadeau","F"));
$pdf->SetAuthor($template->translate("/site/nom"));
$pdf->SetAutoPageBreak(false,10);

$pdf->image($rootpath."/images/templates/".$template->langue."/cheque-cadeau.jpg",0,0,210,297,"jpg");

// 18/05/2016 : "-" supprimé pour rester à 12 caractères
// 24/07/2017 : le lignefactureid étant passé sur 10 chiffres (et non plus 6), il faut le raccourcir pour toujours arriver à 12 caractères au total
$code=$facture->facturid.substr($lignefacture->lignefactureid,4,6);
$dt=new datetime("+6 month");
$validite = $dt->format("d/m/Y");
$valeur=intval(substr($lignefacture->artcode,7));

$pdf->setXY(111,34);
$pdf->setfillcolor(224,208,175);
$pdf->settextcolor(255,255,255);
$pdf->setfont('arial','',20);
$pdf->cell(52,8,$code,0,0,'C',0);

$pdf->setfont('arial','',9);
$pdf->settextcolor(0);
$pdf->setXY(5,30);
$pdf->multicell(190,4,utf8_decode(sprintf($template->translate("/cheque/suite_commande"),$facture->facturid,date("d/m/Y",$facture->datecommande),preg_replace("/https?:\/\//","",$template->translate("/site/url")),$valeur)));

$pdf->settextcolor(0);
$pdf->setfont('arial','',12);
$pdf->setXY(25,105);
if($template->langue=="FR")
  $valtxt=ucfirst(val2str($valeur));
else
  $valtxt=ucfirst(convert_number_to_words($valeur))." euros";
$pdf->cell(91,8,$valtxt,0,0,'C');

$pdf->settextcolor(0);
$pdf->setfont('arial','B',20);
$pdf->setXY(136,99);
$pdf->cell(33,17,$valeur." ".chr(128),0,0,'C');

$pdf->settextcolor(0);
$pdf->setfont('arial','',10);
$pdf->setXY(77,142);
$pdf->multicell(67,4,utf8_decode($facture->nom." ".$facture->prenom."\n".$facture->adresse."\n".$facture->codepostal." ".$facture->ville."\n".$facture->pays."\n".$facture->telephone),0,'C');

// $pdf->setXY(89,159);
// $pdf->cell(45,5,$template->translate("/site/nom"),0,0,"C");

$pdf->setxy(10,175);
$pdf->cell(120,7,utf8_decode(sprintf($template->translate("/cheque/valable"),$validite)));

$pdf->settextcolor(0,0,0);
$pdf->setfont('arial','B',14);
$pdf->setXY(157,175);
$pdf->cell(40,7,utf8_decode("N° ".$code),0,0,'R');


$pdf->output();


?>
