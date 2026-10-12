<?php

include_once($_SERVER['DOCUMENT_ROOT']."/class/host.php");
$host_conf = new Host();
$host=$host_conf->host;
include_once($_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/config_variables.php");

if (!isset($langue)) $langue = "FR" ;

include_once("../configurations/$host/config_mysql.php");
include("../class/mysql.php");
include_once("../class/email.php");
include("../class/stock.class.php");
include_once("../class/ccstat.class.php");
include_once("../class/fonctions.php");
include_once("../class/template.class.php");
include_once("../class/devises.class.php");
define('FPDF_FONTPATH', "../class/font/");
include_once("../class/fpdf.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

$cmdid=intval($_GET['cmdid']);


function french_date($joindate )
    { 
    $monthes = array('', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'); 
    $days = array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'); 
    return $days[date("w",$joindate)].' '.date("d",$joindate).' '.$monthes[date("n",$joindate) ].' '.date("Y",$joindate); 
    }

$marketplaces=array(""=>"Autre","01"=>"Amazon","04"=>"Price Minister","05"=>"Pixmania","06"=>"Windill","07"=>"Rue du Commerce","08"=>"Edengo","09"=>"Ebuyclub","10"=>"Ebay","11"=>"Le guide de la maison");
$codepaysfabrication=array(0=>"France",1=>"Europe",2=>"Asie",3=>"USA");

$optionsoffert=array(4,6,7,8);

$libelle = "libelle$langue";

$cmd=new stock_cmd($DB_site,$cmdid);
$adv=$DB_site->query_first("SELECT af.value FROM adv_fields af INNER JOIN adv a ON af.advid=a.id WHERE af.name='infocomplement' AND facturid=".$cmd->id);
$adresse=$DB_site->query_first("SELECT *,libelle AS pays FROM fournisseur INNER JOIN pays USING(paysid) WHERE id=".$cmd->livraison);
$coord_commercant=$DB_site->fetch_array($DB_site->query("SELECT mail_contact mail, raison_sociale, nom_contact, adresse, cp codepostal, ville, siret, tva, telephone, fax FROM parametresgeneraux"));
$template = new template($langue,$DB_site);
$autocolor=$template->pro?"bleu":"dore";

$ht=false;
$ht=($user['raisonsociale']!="");

$nomboutique = ${$title} ;

class PDF extends FPDF {
	
	public $colors=array(
		"dore"=>array(168,141,92),
		"bleu"=>array(0,150,202),
		"black"=>array(0,0,0),
		"white"=>array(255,255,255),
		"light gray"=>array(230,226,224),
		"orange"=>array(255,87,13)
	);
	
    function Header(){
		global $host, $template, $rootpath, $langue, $nomboutique, $cmd, $autocolor;
		include_once $rootpath."configurations/www.ma-cave-a-vin.fr/config.php";
		
		$this->SetFont('Arial','B',25);
		$this->image($rootpath."images/templates/".$template->site_code()."/".$template->langue."/logo2016".($template->pro?"_pro":"").".jpg",5,5,84,0,"JPG",$template->translate("/site/[site_code]/url"));
	    $this->SetFont('Arial','B',8);
	    $this->image($rootpath."images/templates/".$template->site_code()."/".$template->langue."/contact_devis2016".($template->pro?"_pro":"").".jpg",161,4,46,0,"jpg",$template->translate("/site/[site_code]/url"));

		$this->setXY(0,27);
		$this->SetFont('arial','B',18);
		$this->textColor($autocolor);
		$this->Cell(210,8,$template->translate("/facture/bon_livraison","F"),0,0,'C');
	}
	
    function Footer() {
		//$this->Image("$rootpath/configurations/$host/factures/bas_page.jpg",10,280,190);
	}

	function cell($w=0,$h=0,$txt="",$border=0,$ln=0,$align="",$fill=false,$link=null){
	    if(preg_match('!!u',$txt)) $txt=utf8_decode($txt);
	    return parent::cell($w,$h,$txt,$border,$ln,$align,$fill,$link);
        }
        
	function textColor($name){
	    $color=$this->colors[$name];
	    return parent::setTextColor($color[0],$color[1],$color[2]);
	}
	
	function fillColor($name){
	    $color=$this->colors[$name];
	    return parent::setfillcolor($color[0],$color[1],$color[2]);
	}
	
	function drawColor($name){
	    $color=$this->colors[$name];
	    return parent::setdrawcolor($color[0],$color[1],$color[2]);
	}
	
    function NbLines($w, $txt){
		//Computes the number of lines a MultiCell of width w will take
		$cw=&$this->CurrentFont['cw'];
		if($w==0)
		    $w=$this->w-$this->rMargin-$this->x;
		$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
		$s=str_replace("\r", '', $txt);
		$nb=strlen($s);
		if($nb>0 and $s[$nb-1]=="\n")
		$nb--;
		$sep=-1;
		$i=0;
		$j=0;
		$l=0;
		$nl=1;
		while($i<$nb){
		    $c=$s[$i];
		    if($c=="\n"){
				$i++;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
				continue;
		    }
		    if($c==' ')
				$sep=$i;
		    $l+=$cw[$c];
		    if($l>$wmax){
				if($sep==-1){
					if($i==$j)
					$i++;
				}else
					$i=$sep+1;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
		    }else
				$i++;
		}
		return $nl;
    }
}

$pdf=new pdf();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle("Commande");
$pdf->SetAuthor("cc");
$pdf->SetAutoPageBreak(true,10);
$pdf->SetFillColor(230,230,230);

$pdf->SetFont('Arial','BU',14);
//$pdf->Cell(100,5,"$multilangue[expediteur] :");

$pdf->setXY(10,41);
$pdf->SetFont('Arial','B',10);
$pdf->textColor("black");
$pdf->Cell(70,5,$template->translate("/devis/expediteur"));

$vendeur=$template->translate("/devis/commercant/[site_code]/raison_sociale")."\n".$template->translate("/devis/commercant/[site_code]/adresse")."\n".$template->translate("/devis/commercant/[site_code]/ville");

$pdf->Ln(5);
$pdf->SetFont('Arial','',8);
$pdf->multicell(100,5,$vendeur);

$pdf->fillColor($autocolor);
$pdf->rect(112,41,94,7.5,"F");
$pdf->fillColor("light gray");
$pdf->rect(112,48.5,94,33,"F");

$pdf->setXY(115,43);
$pdf->SetFont('Arial','B',8);
$pdf->textColor("white");
$pdf->Cell(70,5,$template->translate("/devis/adresse_livraison","U"));

$pdf->setXY(115,50);
$pdf->textColor("black");

$pdf->multiCell(100,5,($adresse['societe']!=""?$adresse['societe']."\n":"").$adresse['nom']." ".$adresse['prenom']);
$pdf->setXY(115,59);

$client=$adresse['adresse1']."\n".$adresse['cp']." ".$adresse['ville']."\n".$adresse['libelle']."\n".$template->translate("/facture/tel")." : ".$adresse['telephone']." / Gsm : ".$adresse['portable'];

$pdf->SetFont('Arial','',8);
$pdf->multiCell(100,5,$client);

$ly=65;
$pdf->Cell(100,5,"");
$pdf->setXY(10,$ly);
$pdf->setfont('arial','B',8);
$height=$pdf->nblines(90,$template->translate("/facture/informations","F")." : $adv[0]")*4;
$pdf->multiCell(90,4,$template->translate("/facture/informations","F")." : ".stripslashes($adv[0]));
$pdf->setfont('arial','',8);
$wpic=32;
$pdf->setY($ly+$height);
$pdf->Ln(5);

$wcol=array(8,161,0,0,0,0,0);

toptable($pdf);

$tva = array() ;
$i=0;
$j=1;
$poidstotal=0;
$fichesj=array();
$codecaviste=0;
$lignekdo=0;
$totalcadeau=0;
$totalttc=array();

$cmd->items();
$cmd->groupitems();
foreach($cmd->items as $item){
    $diffprix=0;
    $des_caracts = array() ;
    $calcdesign="";
	$pdf->drawColor("white");
    $libmodele=$DB_site->query_first("SELECT * FROM article WHERE artid=".$item->artid);

    // début de la désignation
    $designation = html_entity_decode($item->libelle." ".$item->marque,ENT_COMPAT,"ISO-8859-15");
    $designation.=" Ref. ".$item->artcode." (".$item->ref.")";
    $designation.="\n";
    $heightlibele=$pdf->nblines($wcol[1],$designation)*4;
    $calcdesign=$designation."\n".$calcdesign;

    $heighteco=0;
    $ecopart=0;
    $ecopartht=0;
    $ecoparthttotal=0;

    $designation2="";
	
    if($libmodele[rangements_txt]!="") $designation2.="\n - Accessoires inclus : ".str_replace("<br>","\n",$libmodele[rangements_txt]);
	
    if($libmodele[emballage_hauteur]!="" || $libmodele[emballage_largeur]!="" || $libmodele[emballage_profondeur]!="" || $libmodele[emballage_poids]!="" || $libmodele[hauteur]!="" || $libmodele[largeur]!="" || $libmodele[profondeur]!="" || $libmodele[poid]!=0)
	    $designation2.="\n - ".$template->translate("/facture/dim_poids_brut")." : ".$libmodele[emballage_hauteur]."*".$libmodele[emballage_largeur]."*".$libmodele[emballage_profondeur]." / ".$libmodele[emballage_poids]."\n - ".$template->translate("/facture/dim_poids_net")." : ".$libmodele[hauteur]."*".$libmodele[largeur]."*".$libmodele[profondeur]." / ".round($libmodele[poid]/1000,1) ;
    $calcdesign.="\n".$designation2;

    $poidstotal+=(floatval(str_replace(",",".",$libmodele[emballage_poids]))*$item->qte);

    $pdf->SetFillColor(255,255,255);
    $i++ ;
  
	$height = 5 ;
	$nbre = ceil(strlen($designation)/80) ;
	if ($nbre > 1)
	    $height = $nbre * 5 ;
	$pdf->SetFont('Arial','',8);
	//$pdf->Cell(30,$height,stripslashes("$lignefacture[artcode]"),1,0,'',1);

	$ly=$pdf->getY();
	$lx=$wpic+5+$wcol[0];
	$height=$pdf->nblines($wcol[1],$calcdesign)*4;
	// calcul si faut ajouter une page
	if($height+$ly>=270){
	    footer($pdf);
	    $pdf->addpage();
	    $pdf->Header($pdf);
	    toptable($pdf);
	    $ly=$pdf->getY();
	    $lx=45;
	}
	$pdf->setXY($lx,$ly);
	$pdf->multiCell($wcol[1],4,"$designation" ,0,'',0);
	$height=$pdf->nblines($wcol[1],$designation)*4;
	$posY=$height+$ly;

	// affichage de la fin de la désignation
	$lx=$wpic+5+$wcol[0];
	$pdf->setXY($lx,$posY);
	$pdf->multicell($wcol[1],4,$designation2,0,'',0);
	//$height=$pdf->nblines($wcol[1],$designation."\n".$designation2)*4;
	$height+=$pdf->nblines($wcol[1],"\n".$designation2)*4;
	$pdf->link($lx,$ly,$wcol[1],$height,$template->translate("/site/[site_code]/url").$template->mkurl("article",$libmodele['artid'],$libmodele));
	
	// affichage des prix de la cave
	$lx=5;
	$pdf->setXY($lx,$ly);
	
	$imgpath="";
	if($libmodele['artid']>0){
		$autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=".intval($libmodele['artid'])." AND visible=1 AND (langue='' OR langue='".$template->langue."')");
		
		if($autreimg!="")
			$imgfile=$libmodele['artid']."_".$autreimg['articlephotoid'];
		else
			$imgfile=$libmodele['artid'];
		
		$imgpath="../configurations/www.ma-cave-a-vin.fr/images/produits/br/".$imgfile.".jpg";
	}
	  
	if($imgpath!="" && file_exists($imgpath)){
		$size=getimagesize($imgpath);
		$img_w=$wpic-2;
		$img_h=$size[1]*$img_w/$size[0];
		if($img_h>$height){
			$img_h=$height-2;
			$img_w=$size[0]*$img_h/$size[1];
		}
		$pdf->image($imgpath,5+($wpic-$img_w)/2,$ly+($height-$img_h)/2,$img_w,$img_h,"JPG");
	}
	$pdf->cell($wpic,$height,"",1,0,'L',0);
	$lx+=$wpic;
	
	$pdf->setXY($lx,$ly);
	$pdf->multiCell($wcol[0],$heightlibele,$item->qte ,0,'C',0);
	$lx=$wpic+5;
	$pdf->setXY($lx,$ly);
	$pdf->multicell($wcol[0],$height,"",1,'',0);
	$lx=$wpic+$wcol[0]+5;
	$pdf->setXY($lx,$ly);
	$pdf->multiCell($wcol[1],$height,"" ,1,'R',0);
	$lx+=$wcol[1];	
	$pdf->drawColor("black");
	$pdf->line(5,$ly+$height-0.5,205,$ly+$height-0.5);
}

$pdf->Ln(1);

$pdf->setX(5);
$pdf->cell($wcol[0]+$wpic,5,"",0);
$pdf->cell($wcol[1],5,sprintf($template->translate("/facture/poids_total_brut"),$poidstotal),0);

$pdf->SetFillColor(255,255,255);
$pdf->SetFont('Arial','',8);

$pdf->Ln(5);

footer($pdf);

if(isset($_GET['dl'])){
	//header("Content-Type: application/force-download");
	header('Content-type: application/pdf');
	header("Content-Disposition: attachment; filename=\"Commande $cmd->id.pdf\"");
	header("Content-Transfer-Encoding: text/plain\n");
	header("Pragma: no-cache");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
	print $pdf->output('','S');
}else
    $pdf->output();

function toptable($pdf){
	global $cde;
	global $wcol;
	global $cmd;
	global $ht;
	global $multilangue;
	global $avoir;
	global $template;
	global $autocolor;

	$pdf->setfont('arial','B',8);
	$pdf->fillColor("light gray");
	$pdf->textColor("black");
	
	$hauttableau=$pdf->PageNo()==1?87:40;
	$pdf->setXY(5,$hauttableau);
	$hline=8;

	$num=$template->translate("/facture/commande_n","F").$cmd->id;
	$pdf->cell(55,8,$template->translate("/facture/date_bon_livraison","F")." : ".date("d/m/Y"),0,0,'C',1);
	$pdf->Cell(57,8,$template->translate("/facture/date_commande")." : ".date("d/m/Y",$cmd->date),0,0,'C',1);
	$pdf->multiCell(65,$hline,$num,0,'C',1);
	$pdf->setXY(182,$hauttableau);
	$pdf->cell(24,8,$template->translate("/facture/page","F")." ".$pdf->page,0,0,'C',1);

	$pdf->setdrawcolor(0);
	$pdf->line(60,$hauttableau+0.5,60,$hauttableau+7.5);
	$pdf->line(114,$hauttableau+0.5,114,$hauttableau+7.5);
	$pdf->line(182,$hauttableau+0.5,182,$hauttableau+7.5);

	$pdf->Ln(10);

	$pdf->fillColor($autocolor);
	$pdf->drawColor("white");
	$pdf->textColor("white");
	$pdf->setfont('arial','B',8);

	$lx=5;
	$ly=$pdf->getY();
	$pdf->setX($lx);
	$pdf->cell(32,8,"",1,0,'C',1);
	$lx+=32;
	$pdf->cell($wcol[0],8,$template->translate("/facture/qte","F"),1,0,"C",1);
	$lx+=$wcol[0];
	$pdf->cell($wcol[1],8,$template->translate("/facture/designation","F"),1,0,'C',1);
	$lx+=$wcol[1];

	$pdf->textColor("black");
	$pdf->setfont('arial','',8);
	$pdf->setY($hauttableau+18);           
}

function footer($pdf){
	global $template;
	if($pdf->getY()>270) return;
	$pdf->setY(275);
	$pdf->SetFont('Arial','',6);
	$pdf->Cell(200,4,sprintf($template->translate("/facture/copyright/[site_code]"),date("Y")),0,0,'C');
	$pdf->Ln(3);
	$pdf->Cell(200,4,$template->translate("/facture/reserves_propriete"),0,0,'C');
	$pdf->Ln(3);
	$pdf->multicell(200,3,$template->translate("/facture/loi"),0,'C',0);
	$pdf->SetFont('Arial','',8);
}
?>
