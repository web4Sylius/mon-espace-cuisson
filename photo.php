<?php

require ("global.php");

// include ("global_institutionnel.php");
//include "espace_partenaires.php" ;
$host=$template->parts["host"];

if(!isset($artid) or $artid=="") {
	echo "ERREUR !!" ;
	exit;
}

$requete_articles="SELECT * FROM article WHERE artid='$artid' ";
$article=$DB_site->query_first($requete_articles);


$url_libelle_art = url_rewrite($article[$libelle]);

if($article[artid]==$artid) $catid=$article[catid];

$pagewidth=800;
$mainheight=600;
$curimg=1;

$articlediaporama="<table class=\"maintable\" id=\"maintable\"><tr>";
$photo=$DB_site->query_first(" SELECT count(*) nb FROM articlephoto WHERE artid='$artid' AND langue IN ('','".$template->langue."') AND visible IN (0,1)");
$autre="";

$jslist="var imgl=new Array();\n";
if($photo[nb]>0){
	$i=1;
	$virgule="";
	$photos=$DB_site->query("SELECT * FROM articlephoto WHERE artid='$artid' AND langue IN ('','".$template->langue."') AND visible IN (0,1) ORDER BY position");
	while($photo=$DB_site->fetch_array($photos)) {
		if($photo[articlephotoid]==$imgid) {
			$curimg=$i;
			$url="images/".$artid."_".$photo[articlephotoid]."_".$pagewidth."_".$mainheight.".jpg";
			$articlediaporama.="
			  <td class=\"btnl\" onclick=\"curimg--; changeimg(curimg);\"></td>
			  <td class=\"mainpic\">
			  <img id=\"mainpic\" src=\"$url\" alt=\"$photo[legende]\" name=\"big\">
			  </td>
			  <td class=\"btnr\" onclick=\"curimg++; changeimg(curimg);\"></td>";
		}

		$url="images/".$artid."_$photo[articlephotoid]_50_50.jpg";
// 		if($i==12) $autre.="</tr><tr><td colspan=\"24\" class=\"sep\"></td></tr><tr>";
		$autre.="<td class=\"thumb\" id=\"td_$i\" onclick=\"changeimg($i)\"><IMG alt=\"$photo[legende]\" src=\"$url\"></td><td class=\"sep\"></td>";
		$virgule=",";
		$jslist.="imgl[$i]=\"images/".$artid."_".$photo[articlephotoid]."_".$pagewidth."_".$mainheight.".jpg\";\n";
		$i++;
	} //
}

$autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=".$artid." AND visible=1 AND (langue='' OR langue='".$template->langue."')");
$autreid="";
if($autreimg==""){
    $url="images/".$artid."_".$pagewidth."_".$mainheight.".jpg";
    $jslist.="imgl[$i]=\"$url\";\n";
    $autre.="<td class=\"thumb\" id=\"td_$i\" onclick=\"changeimg($i)\"><IMG src=\"images/".$artid."_50_50.jpg\"></td>";
    $virgule=",";
}

//Ajout de l'image du pack dans les zooms
$packs = new Pack($DB_site);
$kdopid = $packs->kdos_pack($artid);
if($kdopid){
  $i++;
  $kdop = $DB_site->query_first("SELECT * FROM articlephoto WHERE artid=".$kdopid." AND visible=1 AND (langue='' OR langue='".$template->langue."')");
  $url="images/".$kdopid."_".$kdop['articlephotoid']."_".$pagewidth."_".$mainheight.".jpg";
  $jslist.="imgl[$i]=\"$url\";\n";
  $autre.="<td class=\"thumb\" id=\"td_$i\" onclick=\"changeimg($i)\"><IMG src=\"images/".$kdopid."_".$kdop['articlephotoid']."_50_50.jpg\"></td>";
  $virgule=",";
}

if(!isset($imgid)) {
	$curimg=$i;
	$articlediaporama.="
	  <td class=\"btnl\" onclick=\"curimg--; changeimg(curimg);\"></td>
	  <td class=\"mainpic\">
	  <img id=\"mainpic\" src=\"\" alt=\"$photo[legende]\" name=\"big\">
	  </td>
	  <td class=\"btnr\" onclick=\"curimg++; changeimg(curimg);\"></td>";

}


$articlediaporama.="</tr></table>";


$js="<script language=\"javascript\">

var maxw=$pagewidth;
var maxh=parseInt(document.getElementById('TB_ajaxContent').style.height.substr(0,3)-70);
document.getElementById('maintable').style.height=maxh+'px';
var curimg=$curimg;
$jslist
changeimg($curimg);

if(imgl.length<=12){
    document.getElementById('movethumbleft').style.display='none';
    document.getElementById('movethumbright').style.display='none';
}

function changeimg(id){

  if(id>imgl.length-1) id=1;
  if(id<1) id=imgl.length
  var newImg = new Image();
  var url=imgl[id];
  curimg=id;
//document.getElementById('mainpic').src='javascripts/thickbox/loadingAnimation.gif';

  newImg.onload=function(){
  var height = newImg.height;
  var width = newImg.width;
  if(height>width){
    if(height>maxh){
	width=maxh*(width/height);
	height=maxh;
    }
    if(width>maxw){
	height=maxh*(width/height);
	width=maxw;
    }

  }else{
    if(width>maxw){
	height=maxw*(height/width);
	width=maxw;
    }
    if(height>maxh){
	width=maxh*(width/height);
	height=maxh;
    }
  }
  var mainpic=document.getElementById('mainpic')
  mainpic.style.width=width+'px';
  mainpic.style.height=height+'px';
  mainpic.src=url;

  for(i=1;i<imgl.length;i++) document.getElementById('td_'+i).className='thumb';
  document.getElementById('td_'+curimg).className='thumb selected';
  }
  newImg.src = url;
}

function keydown(keycode){
      switch(keycode){
      case 40:
      case 37:
      curimg--;
      changeimg(curimg);
      break;

      case 38:
      case 39:
      curimg++;
      changeimg(curimg);
      break;
      }
}

var scrollint;
function scrollthumb(sens){
    document.getElementById('autresimg').scrollLeft+=(sens*10);
    scrollint=setTimeout('scrollthumb('+sens+')',50);
}

function stopthumb(){
    clearTimeout(scrollint);
}
</script>";

echo $js;
// echo "<body onkeydown=\"pkey(event);\">";
echo $articlediaporama;

echo "<div style=\"clear:both;\"></div>
<div id=\"thumbdiv\">
  <img src=\"/images/templates/arrow_left.jpg\" id=\"movethumbleft\" onmouseover=\"scrollthumb(-1)\" onmouseout=\"stopthumb()\">
  <div id=\"autresimg\">
    <table id=\"thumbtb\">
      <tr>$autre</tr>
    </table>
  </div>
  <img src=\"/images/templates/arrow_right.jpg\" id=\"movethumbright\" onmouseover=\"scrollthumb(1)\" onmouseout=\"stopthumb()\">
</div>";

// echo "</body>";


function resizedimg($url,$maxw,$maxh){
  $s=getimagesize($url);
  $w=$s[0];
  $h=$s[1];
  if($s[0]>$s[1]){
    if($s[0]>$maxw){
      $h=intval($maxw*($s[1]/$s[0]));
      $w=$maxw;
    }
    if($h>$maxh){
      $w=intval($maxh*($s[0]/$s[1]));
      $h=$maxh;
    }
  }else{
    if($s[1]>$maxh){
      $w=intval($maxh*($s[0]/$s[1]));
      $h=$maxh;
    }
    if($w>$maxw){
      $h=intval($maxh*($s[1]/$s[0]));
      $w=$maxw;
    }
  }
  return array($w,$h);
}

?>
