<?php

include("admin_global.php");

print "<form method=\"post\">
Champ <input type=\"text\" name=\"field\" value=\"".$_POST['field']."\"> traduction <input type=\"text\" name=\"path\" value=\"".$_POST['path']."\"> <input type=\"checkbox\" name=\"update\"> MàJ <input type=\"submit\">
</form>";

$trad=array();
if(isset($_POST['path']) && $_POST['path']){
    $req=$DB_site2->query("SELECT path,FR,EN FROM traduction WHERE path LIKE '".$_POST['path']."%'");
    while($t=$DB_site2->fetch_array($req)){
	$trad[$t['FR']]=$t['path'];
	$traden[$t['FR']]=$t['EN'];
    }
}

if(isset($_POST['field'])){
    $field=$_POST['field'];
    print "<table>";
    $req=$DB_site2->query("SELECT artcode,".$field.",artid FROM article WHERE active IN ('0','1') ORDER BY artcode");
    while($art=$DB_site2->fetch_array($req)){
	$color="#FFAAAA";
	if($art[1]=="") $color="#CCC";
	if(isset($trad[$art[1]])){
	  $color="#AAFFAA";
	  if(isset($_POST['update'])) $DB_site2->query("UPDATE article SET `".$field."`='".$trad[$art[1]]."' WHERE artid=".$art['artid']);
	}
	print "<tr style=\"background-color:$color;\"><td>".$art['artcode']."</td><td>".$art[1]."</td><td>".$traden[$art[1]]."</td></tr>";
    }
    print "</table>";
}

?>