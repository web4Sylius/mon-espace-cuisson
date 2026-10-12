<?php

include("global.php");

$template=new template("FR",$DB_site);

$logo="/images/templates/".$template->site_code()."/".$template->langue."/logo2014.jpg";
$url=$template->translate("/site/[site_code]/url");
$gg_code="0YWgIxtYwut85DpQfB9B6jKCwlYOZh4aC9c8aRyvDQw";
if($template->langue=="EN") $gg_code="WsxSZ_NEM0IIIkdLrdkBEqpONgfRKnV4WS66wNkEJ6A";

?>


<html>
  <head>
    <title>Ma Cave à Vin</title>
    <meta name="google-site-verification" content="<?=$gg_code;?>">
    <meta name="robots" content="noindex, nofollow">
    <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf8">
    <style>
    body { text-align:center; background:black; }
    div { background:white; margin:auto; width:500px; padding:20px; border-radius:20px; }
    img { width:300px; height:auto; }
    p { font-weight:bold; color:black; font-size:14px; }
    </style>
  </head>
  <body>
    <div>
      <a href="<?=$url;?>"><img src="<?=$logo;?>"></a>
      <p>Page introuvable</p>
    </div>
  </body>
</html>