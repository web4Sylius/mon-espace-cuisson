<?php

require ("../../global.php");

if(!isset($_GET['artid'])) exit;

$langue="FR";
if(isset($_GET['langue'])) $langue=$_GET['langue'];
$site_code="base";
if(isset($_GET['site_code'])) $site_code=$_GET['site_code'];
$short=false;
if(isset($_GET['short'])) $short=true;

$font="../FreeSans.ttf";
$bold="../FreeSansBold.ttf";

$vignette_w=278;
$vignette_h=193;
$img_w=100;
$img_h=165;
$imgbox_h=$vignette_h;
$logo_w=130;
$desc_x=105;
$pc_diam=32;
if($short){
  $vignette_w=183;
  $img_w=70;
  $img_h=113;
  $imgbox_h=$img_h;
  $logo_w=71;
  $desc_x=0;
  $pc_diam=24;
}
    

$vignette=imagecreatetruecolor($vignette_w,$vignette_h);
$gris=imagecolorallocate($vignette,84,84,84);
$jaune=imagecolorallocate($vignette,221,219,0);
$marron=imagecolorallocate($vignette,56,46,40);
$blanc=imagecolorallocate($vignette,255,255,255);
$orange=imagecolorallocate($vignette,248,177,51);
$grisclair=imagecolorallocate($vignette,189,189,189);
$grisdesc=imagecolorallocate($vignette,221,219,217);
$noir=imagecolorallocate($vignette,0,0,0);
imagefill($vignette,0,0,$blanc);


$article=$DB_site->query_first("SELECT artid,libelle".$langue." AS libelle,artcode,a_s.active,commandable,gamme".$template->langue.",a_s.prix,a_s.prixconseilleht,a_s.tauxchoisi FROM article INNER JOIN article_site a_s USING(artid) WHERE artid=".intval($_GET['artid'])." AND site_code='".$site_code."'");
$mq=$DB_site->query_first("select libelle,m.marqueid,image from marque m, article_marque am where m.marqueid = am.marqueid and am.artid = '".$_GET['artid']."'") ;
$imgm="../../configurations/www.ma-cave-a-vin.fr/images/marques/br/".$mq[marqueid].".".$mq[image];
if(file_exists($imgm) && $mq[image]!=""){
    $logomarque=imagecreatefromjpeg($imgm);
    $size=getimagesize($imgm);
    $dh=$size[1]*$logo_w/$size[0];
    if($dh>46){
      $logo_w=$size[0]*46/$size[1];
      $dh=46;
    }
    imagecopyresampled($vignette,$logomarque,$img_w+($vignette_w-$img_w-$logo_w)/2-18,3,0,0,$logo_w,$dh,$size[0],$size[1]);
}

$autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=".$_GET['artid']." AND visible=1 AND (langue='' OR langue='".$langue."')");
$autre="";
if($autreimg!="") $autre="_".$autreimg['articlephotoid'];
$artimg="../../configurations/www.ma-cave-a-vin.fr/images/produits/".$_GET['artid'].$autre.".jpg";
if(file_exists($artimg)){
    $img=imagecreatefromjpeg($artimg);
    $size=getimagesize($artimg);
    $w=$img_w;
    $h=$size[1]*$img_w/$size[0];
    if($h>$img_h){
	$h=$img_h;
	$w=$size[0]*$h/$size[1];
    }
    $x=round(($img_w-$w)/2)+2;
    $y=round(($imgbox_h-$h)/2)+6;
    imagecopyresampled($vignette,$img,$x,$y,0,0,$w,$h,$size[0],$size[1]);
}


imagefilledrectangle($vignette,$desc_x,127,$vignette_w,$vignette_h,$grisdesc);
imagesetthickness($vignette,2);
imageline($vignette,$desc_x+20,126,$vignette_w-20,126,$marron);
imagesetthickness($vignette,1);
imagerectangle($vignette,0,0,$vignette_w-1,$vignette_h-1,$grisclair);
$t="Ref : ".$article['artcode'];
$size=imagettfbbox(9,0,$bold,$t);
$textwidth=$size[4]-$size[6];
imagettftext($vignette,9,0,(($vignette_w-$img_w-$textwidth)/2+$img_w),60,$gris,$bold,$t);

$tvafrance=$DB_site->query_first("SELECT 0,TVAtauxnormal,TVAtauxreduit FROM pays WHERE paysid=57");
$txtva=1+($tvafrance[$article['tauxchoisi']]/100);

$yprice=98;
$prixttc=$article['prix'];
$strikeout="";
$temp_price=$DB_site->query_first("SELECT * FROM temp_price WHERE artid=$artid AND ".mktime(0,0,0)." BETWEEN du AND au");
if($temp_price['id']!=""){
    $article['prix']=$temp_price['prix'];
    $article['prixconseilleht']=$temp_price['prixconseilleht'];
    $prixttc=$temp_price['prix'];
}

$article['prixht']=$article['prix']/$txtva;

$promo=$DB_site->query_first("SELECT COUNT(*) FROM promo WHERE artid=".$artid);

if(round($article['prixht'])!=round($article['prixconseilleht']) && ($promo[0]>0 || $secondchoix[0]>0)){
    $strikeout=number_format($article['prixconseilleht']*$txtva,2,',','');
    $pc="-".round(100*($article['prixconseilleht']-$article['prixht'])/$article['prixconseilleht'])."%";
    $yprice=105;
}
$vfarticle=$DB_site->query_first("SELECT * FROM vente_flash_article vfa INNER JOIN vente_flash vf ON (vf.id=vfa.vfid) WHERE (".time()." BETWEEN vf.du AND vf.au) AND vfa.artcode='$article[artcode]'");
if($vfarticle['id']!=""){
    $pc = "-".round(100-(($vfarticle[prix] * 100) / ($txtva*$article[prixconseilleht])))."%";
    $strikeout=number_format($article[prixconseilleht]*$txtva, 2, ',', ' ');
    $prixttc=$vfarticle['prix'];
    $yprice=105;
}

rounded_rectangle($vignette,101,39,$img_w+($vignette_w-$img_w-101)/2,73,$marron,5);

if($strikeout!=""){
    $size=imagettfbbox(11,0,$bold,$strikeout." &#8364;");
    $textwidth=$size[4]-$size[6];
    imagettftext($vignette,11,0,$img_w+($vignette_w-$img_w-$textwidth)/2,90,$blanc,$bold,$strikeout." &#8364;");
    $strikeout_x=$img_w+($vignette_w-$img_w-$textwidth)/2;
    imageline($vignette,$strikeout_x,84,$strikeout_x+$textwidth,84,$blanc);
    imagefilledellipse($vignette,$vignette_w-$pc_diam/2-5,$pc_diam/2+4,$pc_diam,$pc_diam,$orange);
    imagefilledrectangle($vignette,$vignette_w-$pc_diam-5,4,$vignette_w-$pc_diam/2-5,$pc_diam/2+4,$orange);
    imagettftext($vignette,9,0,$vignette_w-$pc_diam-3,$pc_diam/2+8,$marron,$bold,$pc);
}
$prixttc=number_format($prixttc,2,",","")." &#8364;";
$size=imagettfbbox(11,0,$bold,$prixttc);
$textwidth=$size[4]-$size[6];
imagettftext($vignette,11,0,$img_w+($vignette_w-$img_w-$textwidth)/2,$yprice,$jaune,$bold,$prixttc);
imagettftext($vignette,8,0,$desc_x+5,140,$marron,$font,wrap(8,$font,$article['libelle'],$vignette_w-$desc_x-5));

$btn=imagecreatefromjpeg($langue."/btn_vignette.jpg");
$size=getimagesize($langue."/btn_vignette.jpg");
$btn_x=($vignette_w-$desc_x-$size[0])/2+$desc_x;
imagecopyresampled($vignette,$btn,$btn_x,168,0,0,$size[0],$size[1],$size[0],$size[1]);


header("Content-type: image/jpeg");
imagejpeg($vignette, null, 90);


function rounded_rectangle($im,$w,$h,$x,$y,$color,$radius=0){
    imagefilledrectangle($im,$x,$y+$radius,($w+$x),($y+$h-$radius),$color);
    imagefilledrectangle($im,$x+$radius,$y,($x+$w-$radius),($y+$h),$color);

    $dia = $radius*2;

    imagefilledellipse($im, $x+$radius, $y+$radius, $radius*2, $dia, $color);
//     imagefilledellipse($im, $x+$radius, $y+$h-$radius, $radius*2, $dia, $color);
    imagefilledrectangle($im,$x,$y+$h-$radius,$x+$radius,$y+$h,$color);
    imagefilledellipse($im, $x+$w-$radius, $y+$h-$radius, $radius*2, $dia, $color);
    imagefilledellipse($im, $x+$w-$radius, $y+$radius, $radius*2, $dia, $color);
}

function wrap($fontSize, $fontFace, $string, $width) {

    $ret = "";
    $arr = explode(" ", $string);

    foreach ( $arr as $word ){
      $testboxWord = imagettfbbox($fontSize, 0, $fontFace, $word);

      // huge word larger than $width, we need to cut it internally until it fits the width
      $len = strlen($word);
      while ( $testboxWord[2] > $width && $len > 0) {
        $word = substr($word, 0, $len);
        $len--;
        $testboxWord = imagettfbbox($fontSize, 0, $fontFace, $word);
      }

      $teststring = $ret.' '.$word;
      $testboxString = imagettfbbox($fontSize, 0, $fontFace, $teststring);
      if ( $testboxString[2] > $width ){
        $ret.=($ret==""?"":"\n").$word;
       } else {
         $ret.=($ret==""?"":' ').$word;
      }
    }

  return $ret;
}

?>