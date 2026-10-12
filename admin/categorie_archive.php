<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "./fonctions.php";

if(isset($_POST['catid'])) {
    $sql="";
    foreach($langues as $l) 
	if(in_array($l,$langue_access)) $sql.=",title$l='".mysqli_real_escape_string($DB_site2->link_id,$_POST['title'.$l])."', url$l='".mysqli_real_escape_string($DB_site2->link_id,$_POST['url'.$l])."'";
    
    $archcat=$DB_site2->query_first("SELECT catid FROM categorie_archive WHERE catid=".intval($_POST['catid']));
    if($archcat['catid']!=$_POST['catid'])
	$sql="INSERT INTO categorie_archive SET catid=".intval($_POST['catid']).$sql;
    else
	$sql="UPDATE categorie_archive SET ".substr($sql,1)." WHERE catid=".intval($_POST['catid']);

    $result=$DB_site2->query($sql);
    if($result) $msg="<p style=\"color:green;\">La modification a bien été prise en compte. <a href=\"catalogue.php\">Retour</a></p>";
}


$cat=$DB_site2->query_first("SELECT libelleFR FROM categorie WHERE catid=".intval($_GET['catid']));
$archcat=$DB_site2->query_first("SELECT * FROM categorie_archive WHERE catid=".intval($_GET['catid']));

echo "<h1>Page archive ".$cat['libelleFR']."</h1>" ;

print $msg;

print "<form method=\"post\">
<input type=\"hidden\" name=\"catid\" value=\"".$_GET['catid']."\">";

foreach($langues as $l) {
    $flag=$l;
    if($l=="EN") $flag="GB"; 
    if($l=="SP") $flag="ES";
    
    print "<p>Titre <img src=\"images/drapeaux/br/$flag.gif\"> : <input type=\"text\" name=\"title$l\" value=\"".$archcat['title'.$l]."\" size=\"100\"></p>
    <p>URL <img src=\"images/drapeaux/br/$flag.gif\"> : <input type=\"text\" name=\"url$l\" value=\"".$archcat['url'.$l]."\" size=\"100\"></p>
    <hr>";    
}

print "<input type=\"submit\" value=\"Enregistrer\">
</form>";

include "./footer.php";


?>