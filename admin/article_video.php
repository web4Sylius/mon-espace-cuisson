<?php

$sql="SELECT artid";
foreach($langues as $langue)
    $sql.=",video_code".$langue;
$sql.=" FROM article WHERE artid=".$artid;
 
$article=$DB_site2->query_first($sql);



$contenu_onglet.= "<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
<input type=\"hidden\" name=\"old\" value=\"video\">
<input type=\"hidden\" name=\"quitter\" value=\"\">
<input type=\"hidden\" name=\"mq2\" value=\"\">
<input type=\"hidden\" name=\"modif\" value=\"0\">
<input type=\"hidden\" name=\"action2\" value=\"video\">
<input type=\"hidden\" name=\"artid\" value=\"$artid\">
<input type=\"hidden\" name=\"catid\" value=\"$catid\">

<h3>Vidéo</h3>";

//foreach($langues as $langue){
$langue='FR';
    $contenu_onglet.= "
	    <p>Video <img src=\"images/drapeaux/".$langue.".gif\" width=\"20\"></p>";
    
    if($article['video_code'.$langue]!=""){
	    $video = explode(",",$article['video_code'.$langue]);
	    $url = "<iframe width=\"720\" height=\"480\" src=\"https://www.youtube.com/embed/".$video[0]."\" frameborder=\"0\" allowfullscreen></iframe>";
	    $contenu_onglet.= $url."<p onclick=\"document.getElementsByName('video".$langue."0')[0].value='';document.getElementsByName('video".$langue."1')[0].value='';document.getElementsByName('video".$langue."2')[0].value='';document.forms.formu.submit();\" style=\"cursor:pointer;\"><img src=\"images/supprimer.gif\"> Supprimer la vidéo</p>";
    }else{
	    $video=array("",900,510);
    }
	    
    $contenu_onglet.= "
	    <b>code vidéo youtube</b> : <input type=\"text\" value=\"$video[0]\" name=\"video".$langue."0\" size=\"50\" placeholder=\"Exemple : E512bAPSVuI\"/>";
//}

$contenu_onglet.= "<p><input type=\"submit\" value=\"Mettre à jour\"></p>
</form>

<em>* La taille de la vidéo présente dans l'admin n'est pas proportionnelle aux valeurs définis</em>";

?>