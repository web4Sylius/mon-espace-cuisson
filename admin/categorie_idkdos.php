<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "./fonctions.php";

include_once("../class/article_stat.php");

if(isset($_POST['catid'])) {
    $sql="";
    foreach($langues as $l) 
	if(in_array($l,$langue_access)) $sql.=",title$l='".$_POST['title'.$l]."', description$l='".$_POST['description'.$l]."', ref_title$l='".$_POST['ref_title'.$l]."', url$l='".$_POST['url'.$l]."'";
    
    $idkdoscat=$DB_site2->query_first("SELECT catid FROM categorie_idkdos WHERE catid=".intval($_POST['catid']));
    if($idkdoscat['catid']!=$_POST['catid'])
	$sql="INSERT INTO categorie_idkdos SET catid=".intval($_POST['catid']).$sql;
    else
	$sql="UPDATE categorie_idkdos SET ".substr($sql,1)." WHERE catid=".intval($_POST['catid']);

    $result=$DB_site2->query($sql);
    if($result) $msg="<p style=\"color:green;\">La modification a bien été prise en compte. <a href=\"catalogue.php\">Retour</a></p>";
}

$cat=$DB_site2->query_first("SELECT libelleFR FROM categorie WHERE catid=".intval($_GET['catid']));
$idkdoscat=$DB_site2->query_first("SELECT * FROM categorie_idkdos WHERE catid=".intval($_GET['catid']));

echo "<h1>Idées cadeaux ".$cat['libelleFR']."</h1>" ;

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
    
    print "<p>Titre <img src=\"images/drapeaux/br/$flag.gif\"> : ".($readonly?$idkdoscat['title'.$l]:"<input type=\"text\" name=\"title$l\" value=\"".$idkdoscat['title'.$l]."\" size=\"100\">")."</p>
    <p>Description <img src=\"images/drapeaux/br/$flag.gif\"> : ".($readonly?$idkdoscat['description'.$l]:"<textarea name=\"description$l\" class=\"ckeditor\">".$idkdoscat['description'.$l]."</textarea>")."</p>
    <p>Méta title <img src=\"images/drapeaux/br/$flag.gif\"> : ".($readonly?$idkdoscat['ref_title'.$l]:"<input type=\"text\" name=\"ref_title$l\" value=\"".$idkdoscat['ref_title'.$l]."\" size=\"100\">")."</p>
    <p>URL <img src=\"images/drapeaux/br/$flag.gif\"> : ".($readonly?$idkdoscat['url'.$l]:"<input type=\"text\" name=\"url$l\" value=\"".$idkdoscat['url'.$l]."\" size=\"100\">")."</p>
    <hr>";
    
}

print "<input type=\"submit\" value=\"Enregistrer\">
</form>";

include "./footer.php";

?>