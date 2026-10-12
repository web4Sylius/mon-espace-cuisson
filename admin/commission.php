<?php

ini_set("display_errors","on");

include_once($_SERVER['DOCUMENT_ROOT']."/class/host.php");
$host_conf = new Host();
$host=$host_conf->host;
include_once($_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/config_variables.php");

define('FPDF_FONTPATH', $rootpath.'class/font/');
include_once("$rootpath/class/fpdf.php");

include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."class/mysql.php");
include($rootpath."class/fonctions.php");
include($rootpath."class/devises.class.php");
include($rootpath."class/template.class.php");
include($rootpath."configurations/$host/config.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

if(!isset($_GET['facturid'])) exit;

$facture=$DB_site->query_first("SELECT *, UNIX_TIMESTAMP(datefacture) AS datefacture FROM facture f, pays p WHERE facturid = '".$_GET['facturid']."' and f.paysid = p.paysid");
$pays=$DB_site->query_first("SELECT * FROM pays WHERE paysid=".intval($facture['paysid']));

$template=new template("FR",$DB_site);

if($facture['cadeauid']=="") exit;

$pdf=new FPDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle($template->translate("/bondachat/bondachat"));
$pdf->SetAuthor($template->translate("/site/nom"));
$pdf->SetAutoPageBreak(false,10);

$code_caviste=$DB_site->query_first("SELECT * FROM code_caviste WHERE code='$facture[cadeauid]'");
if($code_caviste['caviste']=="" || $code_caviste['type']!=2) exit;

$caviste=$DB_site->query_first("SELECT * FROM caviste WHERE cavisteid=".$code_caviste['caviste']);

$coord_commercant=$DB_site->query_first("SELECT mail_contact mail, raison_sociale, nom_contact, adresse, cp codepostal, ville, siret, tva, telephone, fax FROM parametresgeneraux");

$pdf->image($rootpath."images/templates/".$template->site_code()."/".$template->langue."/contact_devis2016".($template->pro?"_pro":"").".jpg",161,4,46,0,"jpg",$template->translate("/site/[site_code]/url"));
$pdf->image($rootpath."images/templates/".$template->site_code()."/".$template->langue."/logo2016".($template->pro?"_pro":"").".jpg",5,5,84,0,"JPG",$template->translate("/site/[site_code]/url"));

$pdf->SetFont('Arial','',8);
$pdf->setxy(10,30);
$pdf->multicell(100,4,utf8_decode("$coord_commercant[raison_sociale]\n$coord_commercant[adresse]\n$coord_commercant[codepostal] $coord_commercant[ville]\nFRANCE\nTel : $coord_commercant[telephone]\nFax : $coord_commercant[fax]\ncontact@ma-cave-a-vin.fr\nN° SIRET $coord_commercant[siret]"));

$pdf->setfont('arial','',10);
$pdf->setxy(120,48);
$pdf->multicell(100,5,utf8_decode("$caviste[raisonsociale]\n$caviste[adresse]\n$caviste[codepostal] $caviste[ville]\n$caviste[pays]"));

$pdf->setfillColor(230,226,224);
$pdf->settextColor(0);
$pdf->SetFont('Arial','B',8);

$xtableau=16;
$hauttableau=70;
$pdf->setXY($xtableau,$hauttableau);
$dateafftmp=explode("-",$facture[dateexpedition]);
$dateaff="$dateafftmp[2]/$dateafftmp[1]/$dateafftmp[0]";
$pdf->cell(60,8,$template->translate("/facture/date_facture","F")." : ".$dateaff,0,0,'C',1);
$pdf->cell(60,8,$template->translate("/facture/date_commande","F")." : ".date("d/m/Y",$facture['heurefacture']),0,0,'C',1);
$pdf->Cell(60,8,utf8_decode($template->translate("/facture/commande_n","F").$facture[facturid]),0,0,'C',1);
$pdf->setdrawcolor(0);
$pdf->line($xtableau+60,$hauttableau+0.5,$xtableau+60,$hauttableau+7.5);
$pdf->line($xtableau+120,$hauttableau+0.5,$xtableau+120,$hauttableau+7.5);

$pdf->setfont('arial','',10);
$pdf->setxy(5,83);
$pdf->multicell(200,5,utf8_decode($template->translate("/bondachat/bonjour","F").","));
$pdf->setxy(5,90);
$pdf->multicell(180,4,utf8_decode("Votre client $facture[nom] $facture[prenom] nous a passé commande dernièrement sur notre site www.ma-cave-a-vin.fr avec votre code partenaire $code_caviste[code]."));

$pdf->setxy(5,100);
$pdf->multicell(200,5,utf8_decode("Suivant nos accords, cette commande vous donne droit, à une commission dont la valeur est indiqué ci-dessous."));

$pdf->setxy(5,106);
$pdf->multicell(200,5,utf8_decode("Notre service comptabilité effectuera, sous 48 heures, le virement de cette somme ".$facture['montantcadeau']." euro,  sur votre compte bancaire. Nous vous remercions de nous transmettre, en retour et dès réception du paiement, une facture avec TVA, du montant correspondant."));

$pdf->setxy(5,135);
$pdf->multicell(200,5,utf8_decode("Nous vous remercions de votre confiance et de votre collaboration."));

$pdf->setxy(5,143);
$pdf->multicell(200,5,utf8_decode($template->translate("/bondachat/a_bientot")));

$pdf->setfont('arial','',10);
$pdf->setTextColor(168,141,92);
$pdf->setxy(5,149);
$pdf->multicell(100,4,utf8_decode($template->translate("/bondachat/signature")));


$pdf->setTextColor(0);
$pdf->setxy(5,288);
$pdf->SetFont('Arial','',6);
$pdf->Cell(200,4,utf8_decode(sprintf($template->translate("/bondachat/copyright"),date("Y"))),0,0,'C',0);
$pdf->Ln(3);
$pdf->Cell(200,4,utf8_decode("Réserve de propriété : nous nous réservons la propriété des marchandises jusqu'au paiement intégral de nos factures (loi N°80335 du 12 mai 1980)"),0,0,'C',0);

$pdf->output();


?>