<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "./fonctions.php";

include_once("../class/article_stat.php");

if(isset($_POST['catid'])) {
    $sql="";
    foreach($langues as $l) 
	if(in_array($l,$langue_access)) $sql.=",title$l='".mysqli_real_escape_string($DB_site2->link_id,$_POST['title'.$l])."', description$l='".mysqli_real_escape_string($DB_site2->link_id,$_POST['description'.$l])."', ref_title$l='".mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_title'.$l])."', url$l='".$_POST['url'.$l]."'";
    
    $promocat=$DB_site2->query_first("SELECT catid FROM categorie_promo WHERE catid=".intval($_POST['catid']));
    if($promocat['catid']!=$_POST['catid'])
	$sql="INSERT INTO categorie_promo SET catid=".intval($_POST['catid']).$sql;
    else
	$sql="UPDATE categorie_promo SET ".substr($sql,1)." WHERE catid=".intval($_POST['catid']);

    $result=$DB_site2->query($sql);
    if($result) $msg="<p style=\"color:green;\">La modification a bien été prise en compte. <a href=\"catalogue.php\">Retour</a></p>";
}

$cat=$DB_site2->query_first("SELECT libelleFR FROM categorie WHERE catid=".intval($_GET['catid']));
$promocat=$DB_site2->query_first("SELECT * FROM categorie_promo WHERE catid=".intval($_GET['catid']));

echo "<h1>Page promo ".$cat['libelleFR']."</h1>" ;

print $msg;

$art_stat=new article_stat($DB_site2);
print $art_stat->fieldlist();

$filter_langue=count(array_diff($langues,$langue_access))>0;

print "<form method=\"post\">
<input type=\"hidden\" name=\"catid\" value=\"".$_GET['catid']."\">";

foreach($langues as $l) {
    $flag=$l;
    if($l=="EN") $flag="GB"; 
    if($l=="SP") $flag="ES";
    $readonly=!in_array($l,$langue_access);
    
    print "<p>Titre <img src=\"images/drapeaux/br/$flag.gif\"> : ".($readonly?$promocat['title'.$l]:"<input type=\"text\" name=\"title$l\" value=\"".$promocat['title'.$l]."\" size=\"100\">")."</p>
    <p>Description <img src=\"images/drapeaux/br/$flag.gif\"> : ".($readonly?$promocat['description'.$l]:"<textarea name=\"description$l\" class=\"ckeditor\">".$promocat['description'.$l]."</textarea>")."</p>
    <p>Méta title <img src=\"images/drapeaux/br/$flag.gif\"> : ".($readonly?$promocat['ref_title'.$l]:"<input type=\"text\" name=\"ref_title$l\" value=\"".$promocat['ref_title'.$l]."\" size=\"100\">")."</p>
    <p>URL <img src=\"images/drapeaux/br/$flag.gif\"> : ".($readonly?$promocat['url'.$l]:"<input type=\"text\" name=\"url$l\" value=\"".$promocat['url'.$l]."\" size=\"100\">")."</p>
    <hr>";
    
}

print "<input type=\"submit\" value=\"Enregistrer\">
</form>";

include "./footer.php";

?>