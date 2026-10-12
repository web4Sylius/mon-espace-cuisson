<?


$start=0;
$interval=24;

$per=explode(",",$_GET['data']);
if(!isset($_GET['mode'])) $_GET['mode']="G";
switch($_GET['mode']){
    case "d":
    $len=(3600*24);
    $interval=24;
    $start=0;
    break;
    case "m":
    $interval=date("t",$_GET['start']);
    $start=1;
    $mode="j";
    break;
    case "y":
    $interval=12;
    $start=1;
    break;
}

$graphwidth=800;
$graphheight=300;
$rouge=200;
$vert=100;
$bleu=0;
$image = imageCreateTrueColor($graphwidth,$graphheight);
$valcolor = ImageColorAllocate($image,10,10,10);
$realcolor=ImageColorAllocate($image,20,0,200);
$grey=ImageColorAllocate($image,200,200,200);
$dgrey=ImageColorAllocate($image,150,150,150);
$red=ImageColorAllocate($image,200,0,0);
imagefilledrectangle($image, 0, 0, $graphwidth-1, $graphheight-1, $grey);

$maxvisit=max($per);

$intwidth=($graphwidth/$interval);
for($i=0;$i<=$interval;$i++){
    $vert=255-$vert;
    $couleur = ImageColorAllocate($image,$rouge,$vert,$bleu);

    //imageFilledrectangle($image, $x,$graphheight-($per[$i]/$maxvisit)*($graphheight-40)-40, $x+20, $graphheight-40, $couleur);
    //ImageFilledRectangle($image, $x,$graphheight-($real[$i]/$maxvisit)*($graphheight-40)-40,$x+20, $graphheight-40, $realcolor);

    ImageLine($image,$intwidth*($i-0.5),$graphheight-($per[$i-1]/$maxvisit)*($graphheight-40)-40,$intwidth*($i+0.5),$graphheight-($per[$i]/$maxvisit)*($graphheight-40)-40,$couleur);
    //if($per[$i]>=0) ImageLine($image,$intwidth*($i-0.5),$graphheight-($per[$i-1]/$maxvisit)*($graphheight-40)-40,$intwidth*($i+0.5),$graphheight-($per[$i]/$maxvisit)*($graphheight-40)-40,$realcolor);

    imageLine($image,$intwidth*$i+$intwidth/2,$graphheight-($per[$i]/$maxvisit)*($graphheight-40)-40,$intwidth*$i+$intwidth/2,$graphheight-40,$dgrey);
    imagestring($image,5,$intwidth*$i,$graphheight-20,$i+$start,$couleur);
    ImageString($image,3,$intwidth*$i+$intwidth/2,$graphheight-40,$per[$i],$valcolor);
}

header("Content-type: image/png");
ImagePNG($image);
ImageDestroy();
?>