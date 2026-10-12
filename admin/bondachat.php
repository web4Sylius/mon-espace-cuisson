<?php

ini_set("display_errors","on");

$rootpath="../";
// $rootpath="/media/disk/www/macave/site2012/";
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

//if(!isset($_GET['facturid'])) exit;
$facturid=$_GET['facturid'];

//Informations sur la facture
$sql = "SELECT *, UNIX_TIMESTAMP(datefacture) AS datefacture, UNIX_TIMESTAMP(dateexpedition) AS dateexp";
$sql .= " FROM facture f, pays p";
$sql .= " WHERE facturid = '".$facturid."'";
$sql .= " AND f.paysid = p.paysid";
$facture = $DB_site->query_first($sql);

//Informations sur le pays
$sql = "SELECT *";
$sql .= " FROM pays";
$sql .= " WHERE paysid = '".$facture['paysid']."'";
$pays = $DB_site->query_first($sql);

if($facture['moyenid'] == 14){ //Si c'est une commande LOCAM, il faut prendre les coordonnées de livraison pour le bon d'achat
     
     $client['langue']        = empty($facture['raisonsociale']) ? 'FR' : $facture['raisonsociale'];
     $client['raisonsociale'] = $facture['raisonsociale'];
     $client['nom']           = $facture['nom'];
     $client['prenom']        = $facture['prenom'];
     $client['adresse']       = $facture['adresse'];
     $client['codepostal']    = $facture['codepostal'];
     $client['ville']         = $facture['ville'];
     $client['libelle']       = $facture['libelle'];
     
} else { //Sinon, on prend les coordonnées de facturation
     
     $sql = "SELECT *";
     $sql .= " FROM utilisateur u";
     $sql .= " INNER JOIN pays p ON (p.paysid = u.paysid)";
     $sql .= " WHERE u.userid = '".$facture['userid']."'";
     $client = $DB_site->query_first($sql);
}

$template=new template($client['langue'],$DB_site);

if($facture['cadeauid']=="0") exit;

$code_caviste=$DB_site->query_first("SELECT * FROM code_caviste WHERE code='".$facture['cadeauid']."'");
if($code_caviste['caviste']=="" || $code_caviste['type']!=1 || $code_caviste['mini']>$facture['montantcadeau']) exit;

$caviste=$DB_site->query_first("SELECT * FROM caviste WHERE cavisteid='".$code_caviste['caviste']."'");

$coord_commercant=$DB_site->query_first("SELECT mail_contact mail, raison_sociale, nom_contact, adresse, cp codepostal, ville, siret, tva, telephone, fax FROM parametresgeneraux");

$pdf=new FPDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle($template->translate("/bondachat/bondachat"));         
$pdf->SetAuthor($template->translate("/site/nom"));
$pdf->SetAutoPageBreak(false,10);

$pdf->image($rootpath."images/templates/".$template->site_code()."/".$template->langue."/contact_devis2016".($template->pro?"_pro":"").".jpg",161,4,46,0,"jpg",$template->translate("/site/[site_code]/url"));
$pdf->image($rootpath."images/templates/".$template->site_code()."/".$template->langue."/logo2016".($template->pro?"_pro":"").".jpg",5,5,84,0,"JPG",$template->translate("/site/[site_code]/url"));

if($client['raisonsociale']=="")
    $pdf->setTextColor(168,141,92);
else
    $pdf->setTextColor(0,150,202);
$pdf->setfont('arial','B',18);
$pdf->setxy(0,30);
$pdf->cell(210,5,"BON D'ACHAT",0,0,'C');

$pdf->settextcolor(0,0,0);
$pdf->SetFont('Arial','B',8);
$pdf->setxy(15,35);
$pdf->Cell(70,5,utf8_decode($template->translate("/devis/expediteur")));
$pdf->SetFont('Arial','',8);
$pdf->setxy(15,40);
$pdf->multicell(100,4,utf8_decode("$coord_commercant[raison_sociale]\n$coord_commercant[adresse]\n$coord_commercant[codepostal] $coord_commercant[ville] - FRANCE"));

$pdf->setfont('arial','',10);
$pdf->setxy(120,44);
$pdf->multicell(100,5,utf8_decode(($client['raisonsociale']!=""?$client['raisonsociale']."\n":"")."$client[nom] $client[prenom]\n$client[adresse]\n$client[codepostal] $client[ville]\n$client[libelle]"));

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
$pdf->multicell(200,4,utf8_decode(sprintf($template->translate("/bondachat/txt_1"),$facture['facturid'],date("d/m/Y",$facture['heurefacture']),$code_caviste['code'])));

$pdf->setxy(5,100);
$pdf->multicell(200,5,utf8_decode($template->translate("/bondachat/txt_2")));

$pdf->setfont('arial','b',10);
$pdf->setxy(0,110);
$pdf->multicell(210,4,utf8_decode("$caviste[raisonsociale]\n$caviste[adresse]\n$caviste[codepostal] $caviste[ville] $caviste[pays]"),0,'C',0);

$pdf->setfont('arial','',10);
$pdf->setxy(5,125);
$pdf->multicell(200,4,sprintf(utf8_decode($template->translate("/bondachat/txt_3")),$facture['montantcadeau']." ".chr(128)));

$pdf->setxy(5,135);
$pdf->multicell(200,5,utf8_decode($template->translate("/bondachat/txt_4")));

$pdf->setxy(5,140);
$pdf->multicell(200,5,utf8_decode($template->translate("/bondachat/a_bientot")));

$pdf->setfont('arial','',10);
$pdf->setTextColor(168,141,92);
$pdf->setxy(5,150);
$pdf->multicell(100,4,utf8_decode($template->translate("/bondachat/signature")));

$yoff=160;
$pdf->image($rootpath."images/templates/".$template->site_code()."/".$template->langue."/bondachat.jpg",5,$yoff,200);


$pdf->setTextColor(0);
$pdf->setfont('arial','B',18);
$pdf->setxy(133,43+$yoff);
$pdf->multicell(30,5,$facture['montantcadeau'].chr(128),0,'C',0);

$pdf->settextcolor(0);
$pdf->setfont('arial','',11);
$pdf->setxy(16,43+$yoff);
if($template->langue=="FR")
  $val2str=val2str($facture['montantcadeau']);
else
  $val2str=convert_number_to_words($facture['montantcadeau']);
$pdf->multicell(103,8,$val2str,0,'C',0);


$pdf->setTextColor(0);
$pdf->setfont('arial','',10);
$pdf->setxy(57,64+$yoff);
$pdf->multicell(210,4,utf8_decode(strtoupper($client['nom'])." ".ucfirst($client['prenom'])."\n".$client['adresse']."\n".$client['codepostal']." ".$client['ville']."\n".$client['libelle']),0,'L');


$pdf->setxy(57,88+$yoff);
$pdf->multicell(210,4,utf8_decode("$caviste[raisonsociale]\n$caviste[adresse]\n$caviste[codepostal] $caviste[ville]\n$caviste[pays]"),0,'L',0);

// $pdf->settextcolor(0);
// $pdf->setfont('arial','',12);
// $pdf->setxy(150,116+$yoff);
// $pdf->multicell(100,8,"Fait à Lyon, le ".date("d/m/Y",$facture['dateexp']+864000));

$pdf->settextcolor(0);
$pdf->setfont('arial','B',10);
$pdf->setxy(20,115+$yoff);
$pdf->multicell(180,10,utf8_decode("N° $facture[facturid] / $code_caviste[code]"),0,'R');

$pdf->setfont('arial','',10);
$pdf->setxy(8,115+$yoff);
$fin=mktime()+182*86400;
$pdf->multicell(200,10,utf8_decode(sprintf($template->translate("/bondachat/valable","F"),date("d/m/Y",$fin))));

$pdf->setxy(5,288);
$pdf->SetFont('Arial','',6);
$pdf->Cell(200,4,utf8_decode(sprintf($template->translate("/bondachat/copyright"),date("Y"))),0,0,'C');
$pdf->Ln(3);
// $pdf->Cell(200,4,"Réserve de propriété : nous nous réservons la propriété des marchandises jusqu'au paiement intégral de nos factures (loi N°80335 du 12 mai 1980)",0,0,'C',0);

$pdf->output();



?>