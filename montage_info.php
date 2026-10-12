<?php

require ("global.php");

$artid=intval($_GET['artid']);
if($artid>0) {
   $article=$DB_site->query_first("SELECT artid,artcode,libelle".$template->langue." AS libelle,gamme".$template->langue." AS gamme,m.libelle AS marque FROM article INNER JOIN article_marque USING(artid) INNER JOIN marque m USING(marqueid) WHERE artid=".intval($_GET['artid']));
   $autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=$artid AND visible=1 AND (langue='' OR langue='".$template->langue."')");
   $autre="";
   if($autreimg!="")
	$autre="_".$autreimg['articlephotoid'];

   $template->parts["article"]="<h2>".$article['libelle']." ".$article['marque']." ".$article['gamme']." ".$article['artcode']."</h2>
   <img src=\"/images/".$article['artid']."_280_370".$autre.".jpg\" id=\"mainimg\">";

}

$template->evaltemplate("MontageInfo","montage_info");

print stripslashes($template->parts["montage_info"]);

?>