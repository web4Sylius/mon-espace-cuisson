<?php

ini_set("display_errors","on");

if(!isset($_GET['artid']) || intval($_GET['artid']==0)) exit;
$langue=isset($_GET['langue']) ? $_GET['langue'] : "FR";

include_once($_SERVER['DOCUMENT_ROOT']."/class/host.php");
$host_conf = new Host();
$host=$host_conf->host;
include_once($_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/config_variables.php");

define('FPDF_FONTPATH', $rootpath.'/class/font/');
include_once("$rootpath/class/fpdf.php");

include_once($rootpath."/configurations/$host/config_mysql.php");
include($rootpath."/class/mysql.php");
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

class PDF extends FPDF
{
    
    public $colors = array("dore" => array(168, 141, 92), "bleu" => array(0, 150, 202), "black" => array(0, 0, 0), "white" => array(255, 255, 255), "light gray" => array(230, 226, 224), "orange" => array(255, 87, 13));
    
    function Header()
    {
        
    }
    function Footer()
    {
        //  $this->Image("$rootpath/configurations/www.ma-cave-a-vin.fr/factures/bas_page.jpg",10,280,190);
    }
    
    function cell($w = 0, $h = 0, $txt = "", $border = 0, $ln = 0, $align = "", $fill = false, $link = null)
    {
        if (preg_match('!!u', $txt))
            $txt = utf8_decode($txt);
        return parent::cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
    }
    
    function textColor($name)
    {
        $color = $this->colors[$name];
        return parent::setTextColor($color[0], $color[1], $color[2]);
    }
    
    function fillColor($name)
    {
        $color = $this->colors[$name];
        return parent::setfillcolor($color[0], $color[1], $color[2]);
    }
    
    function drawColor($name)
    {
        $color = $this->colors[$name];
        return parent::setdrawcolor($color[0], $color[1], $color[2]);
    }
    
    function NbLines($w, $txt)
    {
        //Computes the number of lines a MultiCell of width w will take
        $cw =& $this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s    = str_replace("\r", '', $txt);
        $nb   = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i   = 0;
        $j   = 0;
        $l   = 0;
        $nl  = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j   = $i;
                $l   = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j   = $i;
                $l   = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }
}

$autocolor="dore";

$template=new template($langue,$DB_site);
$template->changesite("base",$langue);

$article=$DB_site->query_first("SELECT * FROM article WHERE artid=".intval($_GET['artid']));
$marque=$DB_site->query_first("SELECT m.* FROM marque m INNER JOIN article_marque am USING(marqueid) WHERE artid=".intval($_GET['artid']));

$pdf=new PDF();
$pdf->AliasNbPages();
$pdf->SetTitle($template->translate("/article/champs/conseils","F")."_".$article['artcode']);
$pdf->SetAuthor($template->translate("/site/nom"));
$pdf->SetAutoPageBreak(false,10);

newpage();

$ly=30;
$logoimg=$rootpath."configurations/www.ma-cave-a-vin.fr/images/marques/".$marque[marqueid].".".$marque[image];
if(file_exists($logoimg)){
    $size=getimagesize($logoimg);
    $logo_w=27;
    $pdf->image($logoimg,5+(56-$logo_w)/2,$ly,$logo_w);
}
$ly+=10;
$pdf->setXY(9,$ly);
$pdf->setfont('arial','',8);
$lblheight=$pdf->nblines(45,$article['libelle'.$template->langue])*3;
$pdf->multicell(45,3,html_entity_decode($article['libelle'.$template->langue],ENT_COMPAT,"ISO-8859-15"),0,'L');
$ly+=$lblheight;
$pdf->setXY(9,$ly);
$pdf->textcolor($autocolor);
$lbl=$marque[libelle]." ".$article['gamme'.$template->langue]." ".$article[artcode];
$lblheight=$pdf->nblines(45,3,$lbl)*3;
$pdf->multicell(45,3,$lbl,0,'L');
$ly+=$lblheight;

$autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=".$article[artid]." AND visible=1 AND (langue='' OR langue='".$template->langue."')");
$autreid="";
if($autreimg!="") $autreid="_".$autreimg['articlephotoid'];
$image=$rootpath."/configurations/www.ma-cave-a-vin.fr/images/produits/br/".$article[artid].$autreid.".".$article[image];
$w=46;
if(file_exists($image)){
    $size=getimagesize($image);
    if($size[1]>392) $w=intval($size[0]*(392/$size[1])*0.25);
    if($w>46) $w=46;
    $pdf->image($image,10,$ly+6,$w);
}

$conseils=array();
$req=$DB_site->query("SELECT * FROM article_nosconseils WHERE artid=".$article['artid']);
while($conseil=$DB_site->fetch_array($req)){
    $conseils[$conseil['type']][]=$template->translate($conseil['path']);
}

$ptablex=64;

$plan=$DB_site->query_first("SELECT * FROM article_files WHERE artid=".$article['artid']." AND ext='jpg' AND infos IN ('instruction','instruction".$template->langue."')");
if($plan['id']!=""){
    $file="articlefiles/".$plan['id'].".".$plan['ext'];
    if(file_exists($file)){
        $size=getimagesize($file);
        $w=140;
        $h=$size[1]/$size[0]*140;
        if($h>240){
            $h=240;
            $w=$size[0]/$size[1]*$h;
        }
        $pdf->image($file,$ptablex,$ly,$w);
        $ly+=$h+5;
    }
}
 
if($ly>=250){
    newpage();
}

foreach(array("conseil_installation","conseil_utilisation","conseil_entretien") as $typeconseil){
    if(isset($conseils[$typeconseil])){
        $pdf->setXY($ptablex,$ly);
        $pdf->fillColor($autocolor);
        $pdf->textColor("white");
        $pdf->setfont('arial','',10);
        $pdf->cell(140,5,$template->translate("/article/champs/".$typeconseil),0,0,'L',1);
        $ly+=5;
        
        $req=$DB_site->query("SELECT * FROM articlephoto WHERE artid=".$article['artid']." AND langue IN ('','".$template->langue."') AND conseil='".preg_replace("/conseil_/","",$typeconseil)."'");
        while($articleimage=$DB_site->fetch_array($req)){
            $file="../configurations/$host/images/produits/".$articleimage[artid]."_".$articleimage[articlephotoid].".".$articleimage[image];
            $size=getimagesize($file);
            $w=140;
            $h=$size[1]/$size[0]*140;
            if($h>240){
                $h=100;
                $w=$size[0]/$size[1]*$h;
            }
            
            if($ly>=250) newpage();
            
            $pdf->image($file,$ptablex+(140-$w)/2,$ly,$w);
            $ly+=$h+5;
        }

        foreach($conseils[$typeconseil] as $conseil){
            if($ly>=250) newpage();
            $pdf->setXY($ptablex,$ly);
            $pdf->textColor("black");
            $pdf->setfont('arial','',8);
            $pdf->cell(140,5,$conseil,0,0,'L');
            $ly+=5;
        }
        $ly+=5;
    }
}

if (isset($_GET['dl'])) {
    //header("Content-Type: application/force-download");
    header('Content-type: application/pdf');
    header("Content-Disposition: attachment; filename=\"" . $artid['artcode'] . ".pdf\"");
    header("Content-Transfer-Encoding: text/plain\n");
    header("Pragma: no-cache");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
    print $pdf->output('', 'S');
} else
    $pdf->output();

function newpage(){
    global $pdf;
    global $ly;
    global $template;
    global $rootpath;
    global $autocolor;

    $pdf->AddPage();
    $ly=50;
    $pdf->image($rootpath."/images/templates/".$template->site_code()."/".$template->langue."/logo2016".($template->pro?"_pro":"").".jpg",5,5,84,0,"JPG",$template->translate("/site/url"));
    $pdf->textcolor($autocolor);
    $pdf->setXY(80,30);
    $pdf->setfont('arial','B',18);
    $pdf->cell(80,8,$template->translate("/article/champs/conseils","F"),0,0,'C');
    $pdf->textcolor("black");
}
