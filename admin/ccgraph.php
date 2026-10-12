<?php

$title="";
$graphwidth=600;
$graphheight=300;
$coord=array();
$abs=array();
$debug=false;
$ordtype="";
$abstype="";

// ajout d'une verification pour faire fonctionner l'affichage sous windows
if (substr(php_uname("a"), 0, 3) != "Win")
  $fontfile="freesans";
else
  $fontfile="arial.ttf";
  
putenv('GDFONTPATH=' . realpath('.'));

// Attention, aussi en parametrage dans la function drawscale()
$lmargin=50;
$bmargin=20;
$rmargin=50;

if(isset($_GET['title'])) $title=$_GET['title'];
if(isset($_GET['width'])) $graphwidth=intval($_GET['width']);
if(isset($_GET['height'])) $graphheight=intval($_GET['height']);
if(isset($_GET['ordtype'])) $ordtype=$_GET['ordtype'];
if(isset($_GET['abstype'])) $abstype=$_GET['abstype'];
if(isset($_GET['debug'])) $debug=true;

$absmin=$absmax=$ordmin=$ordmax=0;

if(!file_exists($fontfile.".ttf")) $fontfile="";

$image = imageCreateTrueColor($graphwidth,$graphheight);
$grey=ImageColorAllocate($image,220,250,255);
$dgrey=imagecolorallocate($image,128,128,128);
$white=imagecolorallocate($image,255,255,255);
$black=imagecolorallocate($image,0,0,0);
imagefilledrectangle($image, 0, 0, $graphwidth-$rmargin, $graphheight-1-$bmargin, $grey);
if($lmargin>0) imagefilledrectangle($image,0,0,$lmargin,$graphheight-1,$white);
if($rmargin>0) imagefilledrectangle($image,$graphwidth-$rmargin,0,$graphwidth,$graphheight-1,$white);
if($bmargin>0) imagefilledrectangle($image,0,$graphheight-$bmargin,$graphwidth,$graphheight,$white);
$gcolor=ImageColorAllocate($image,20,100,200);

imagestring($image,3,$lmargin,0,$title,$black);

if(!isset($_GET['data'])) draw($image);

$result=convertdata($_GET['data']);
if($result!=true) draw($image);

$minstep=intval(strlen(formatdata($absmax,$abstype))*8);
drawscale($minstep);

$xpx0=$ypx0=0;
$ltxt="";
foreach($coord as $x=>$y){
    $xpx=($x-$absmin)*(($graphwidth-$lmargin-$rmargin)/($absmax-$absmin))+$lmargin+1;
    $ypx=$graphheight-($y-$ordmin)*(($graphheight-$bmargin)/($ordmax-$ordmin))-1-$bmargin;

    if($xpx0!=0 && $ypx0!=0) ImageLine($image,$xpx0,$ypx0,$xpx,$ypx,$gcolor);

    $xstr=$xpx;
    $ystr=$ypx;
    $txt=formatdata($y,$ordtype);
    $lentxt=strlen($txt);
//     if($xstr+8*$lentxt>=$graphwidth) $xstr-=(8*$lentxt);
    if($ystr>=($graphheight-$bmargin-16)) $ystr=$graphheight-$bmargin-16;
    if($xstr<=0) $xstr+=6;
    if($ystr<=0) $ystr+=6;
    if($fontfile=="")
      imagestring($image,3,$xstr,$ystr,$txt,$black);
    else{
      $angle=45;
      $box=imagettfbbox(8,45,$fontfile,$txt);
      $boxh=$box[1]-$box[7];
      if($ystr-$boxh<0 || $ystr<12){
	$angle=-45;
	$ystr+=5;
      }
      if($xstr-$xstr0>10 && $txt!=$ltxt){
	  imagefilledellipse($image,$xpx,$ypx,6,6,$gcolor);
	  imagettftext($image,8,$angle,$xstr,$ystr,$black,$fontfile,$txt);
	  $xstr0=$xstr;
      }else
	  imagefilledellipse($image,$xpx,$ypx,2,2,$gcolor);

      $ltxt=$txt;
    }
    
    if($debug) print "\n$xpx:$ypx";
    $xpx0=$xpx;
    $ypx0=$ypx;
}

draw($image);

function drawscale($minstep=80){
    global $absmin,$absmax,$ordmin,$ordmax,$graphwidth,$graphheight;
    global $image;
    global $black,$dgrey;
    global $ordtype,$abstype,$lmargin,$bmargin;
    
    // ajout TSI, car problemes de variable globales 
    $lmargin=50;
    $bmargin=20;
    $rmargin=50;
    
    imageline($image,$lmargin,0,$lmargin,$graphheight-$bmargin,$dgrey);

    $i=$lmargin;
    $ypx=$graphheight-$bmargin;
    while($i<$graphwidth){
      $val=($absmax-$absmin)*($i-$lmargin)/($graphwidth-$lmargin)+$absmin;
      imagestring($image,2,$i,$ypx,formatdata($val,$abstype),$black);
      imageline($image,$i,$ypx-4,$i,$ypx+2,$dgrey);
      $i+=$minstep;
    }

    $step=round(($ordmax-$ordmin)/($graphheight/20));

    for($i=$ordmin;$i<=$ordmax;$i+=$step){
	$ypx=$graphheight-($i-$ordmin)*(($graphheight-$bmargin-$rmargin)/($ordmax-$ordmin))-1-$bmargin;
	if($ypx==-1) $ypx=0;
	//imageline($image,$lmargin/2,$ypx,$graphwidth,$ypx,$dgrey);
	if($ypx>=$graphheight-16) $ypx-=16;
	//if($ypx<=10) $ypx=10;
	imagestring($image,2,0,$ypx-4,formatdata($i,$ordtype),$black);
    }
  /*  
    $ypx=$graphheight-$bmargin;
    for($i=$absmin;$i<=$absmax;$i+=1000000){
	$xpx=($i-$absmin)*(($graphwidth-$lmargin)/($absmax-$absmin))+$lmargin+1;
	imagestring($image,3,$xpx,$ypx,formatdata($i,$abstype),$black);
	imageline($image,$xpx,$ypx+2,$xpx,$ypx-4,$black);
    }*/

    return true;
}

function formatdata($data,$type){
    switch($type){
	case "":return $data;
	case "ts":return date("d/m/Y",$data);
	case "euro":return number_format($data,2,',','');
    }
}

function convertdata($data){
   global $coord;
   global $abs;
   global $absmin,$absmax,$ordmin,$ordmax;

   $absord=explode(",",$data);
   foreach($absord as $c){
      $sp=explode(":",$c);
      $coord[$sp[0]]=$sp[1];
   }
   $abs=array_keys($coord);
   if(isset($_GET['absmax']))
      $absmax=$_GET['absmax'];
   else
      $absmax=max($abs);
   
   if(isset($_GET['absmin']))
      $absmin=$_GET['absmin'];
   else
      $absmin=min($abs);

   $ordmin=min($coord);
   $ordmax=max($coord);
   $ordmax+=10;
   $ordmin-=10;
   ksort($coord);

   return true;
}


function draw($image){
  global $debug;
  global $coord;
  global $absmin,$absmax,$ordmin,$ordmax;

  if($debug==false){
    header("Content-type: image/png");
    ImagePNG($image);
    ImageDestroy();
    exit;
  }else{
    print_r($coord);
    print "\nabsmax:$absmax";
    print "\nabsmin:$absmin";
    print "\nordmin:$ordmin";
    print "\nordmax:$ordmax";
  }
}

?>