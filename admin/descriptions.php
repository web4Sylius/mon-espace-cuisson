<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "./fonctions.php";  

include("../class/stock.class.php");

if(isset($_POST['descriptionEN'])) $DB_site2->query("UPDATE article SET descriptionEN='".$_POST['descriptionEN']."' WHERE artid=".$_POST['artid']);


echo "<h1>Descriptions</h1>" ;

if(!isset($_GET['artid'])){
    $parents=array();
    $req=$DB_site2->query("SELECT * FROM categorie ORDER BY position");
    while($cat=$DB_site2->fetch_array($req)){
	$parents[$cat['catid']]=$cat;
    }
  

    $sql="SELECT artid,artcode,libelleEN,descriptionEN,catid FROM article INNER JOIN article_site USING(artid) WHERE site_code='uk' ORDER BY ventes";
    $title="Site anglais";
    if($_GET['order']=="ventes"){
	$sql="SELECT artid,artcode,libelleEN,descriptionEN,catid FROM article ORDER BY ventes DESC LIMIT 100";
	$title="Meilleures ventes";
    }

    $req=$DB_site2->query($sql);
    
    $tdlist=array();
    while($art=$DB_site2->fetch_array($req)){
	while($parents[$art['catid']]['parentid']!=0) $art['catid']=$parents[$art['catid']]['parentid'];

	$tdlist[$art['catid']][]="<a href=\"descriptions.php?artid=".$art['artid']."\" style=\"".($art['descriptionEN']!=""?"color:green":"")."\" title=\"".$art['libelleEN']."\">".$art['artcode']."</a>";
    }
    
    print "<h2>$title</h2>";
    
    ksort($tdlist);
    foreach($tdlist as $catid=>$tdcatlist){
	asort($tdcatlist);
	$c=0;
	print "<h3>".$parents[$catid]['libelleEN']."</h3>";
	print "<table width=\"100%\">";
	foreach($tdcatlist as $artlink){
	    print "<td>".$artlink."</td>";
	    $c++;
	    if($c==4){
		print "</tr><tr>";
		$c=0;
	    }
	}
	print "</table>";
    }
}else{

    print "<h3>".$art['artcode']."</h3>";
    
    $art=$DB_site2->query_first("SELECT artid,artcode,descriptionFR,descriptionEN FROM article WHERE artid=".$_GET['artid']);
    
    print "<p>Voir l'article : ";
    foreach($sites as $site_code=>$site){
	foreach($site['hosts'] as $url=>$langue){
	    $url_art="http://".$url."/art,".$art['artid'].",-.htm";
	    print "<a href=\"$url_art\" target=\"_blank\">$url</a> ";
	}
    }
    
    print "</p>
    <form action=\"descriptions.php\" method=\"post\">
    <input type=\"hidden\" name=\"artid\" value=\"".$art['artid']."\">
    
    <div id=\"fr\" style=\"float:left;width:49%;overflow:auto;\">
      <img src=\"images/drapeaux/FR.gif\" width=\"20\">"
      .$art['descriptionFR'].$prop.
    "</div>
    
    <div class=\"\" style=\"float:right;width:49%;overflow:auto;\">
      <img src=\"images/drapeaux/GB.gif\" width=\"20\">
      <textarea name=\"descriptionEN\" class=\"ckeditor\">".$art['descriptionEN']."</textarea>
    </div>";

    echo "<div class=\"clear\"></div>
      <p><input type=\"button\" value=\"Annuler\" onclick=\"document.location='descriptions.php';\"> <input type=\"submit\" value=\"Enregistrer\"></p>
    </form>";
}

print "<script language=\"javascript\" src=\"./../ckeditor/ckeditor.js\"></script>
<script>

CKEDITOR.config.height = '600px';

</script>";

include "./footer.php";
?>