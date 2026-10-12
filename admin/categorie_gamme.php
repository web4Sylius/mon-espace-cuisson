<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "./fonctions.php";

include_once("../class/article_stat.php");

$gamme=$_GET['gamme'];

if(isset($_POST['catid'])) {
    $sql="";
    foreach($langues as $l) 
	if(in_array($l,$langue_access)) $sql.=",title$l='".$_POST['title'.$l]."', description$l='".$_POST['description'.$l]."', ref_title$l='".$_POST['ref_title'.$l]."', url$l='".$_POST['url'.$l]."'";
    
    $promocat=$DB_site2->query_first("SELECT catid FROM categorie_gamme WHERE catid=".intval($_POST['catid'])." AND gamme='".$gamme."'");
    if($promocat['catid']!=$_POST['catid'])
	$sql="INSERT INTO categorie_gamme SET catid=".intval($_POST['catid']).", gamme='".$gamme."'".$sql;
    else
	$sql="UPDATE categorie_gamme SET ".substr($sql,1)." WHERE catid=".intval($_POST['catid'])." AND gamme='".$gamme."'";

    $result=$DB_site2->query($sql);
    if($result) $msg="<p style=\"color:green;\">La modification a bien été prise en compte. <a href=\"catalogue.php\">Retour</a></p>";
}

$cat=$DB_site2->query_first("SELECT libelleFR FROM categorie WHERE catid=".intval($_GET['catid']));
$gammecat=$DB_site2->query_first("SELECT * FROM categorie_gamme WHERE catid=".intval($_GET['catid'])." AND gamme='".mysqli_real_escape_string($DB_site2->link_id,$gamme)."'");

echo "<h1>Page gamme ".$gamme." ".$cat['libelleFR']."</h1>" ;

print $msg;

if($_GET['catid']>0) print "<p><a href=\"categorie_gamme.php?catid=0&gamme=".$gamme."\">Page index gamme ".$gamme."</a></p>";

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
    
    print "<p>Titre <img src=\"images/drapeaux/br/$flag.gif\"> : ".($readonly?$gammecat['title'.$l]:"<input type=\"text\" name=\"title$l\" value=\"".$gammecat['title'.$l]."\" size=\"100\">")."</p>
    <p>Description <img src=\"images/drapeaux/br/$flag.gif\"> : ".($readonly?$gammecat['description'.$l]:"<textarea name=\"description$l\" class=\"ckeditor\">".$gammecat['description'.$l]."</textarea>")."</p>
    <p>Méta title <img src=\"images/drapeaux/br/$flag.gif\"> : ".($readonly?$gammecat['ref_title'.$l]:"<input type=\"text\" name=\"ref_title$l\" value=\"".$gammecat['ref_title'.$l]."\" size=\"100\">")."</p>
    <p>URL <img src=\"images/drapeaux/br/$flag.gif\"> : <input type=\"text\" name=\"url$l\" value=\"".$gammecat['url'.$l]."\" size=\"100\" ".($readonly||$_GET['catid']==0?"readonly":"")."></p>
    <hr>";
    
}

print "<input type=\"submit\" value=\"Enregistrer\">
</form>";

include "./footer.php";

?>