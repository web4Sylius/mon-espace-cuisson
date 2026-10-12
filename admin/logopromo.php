<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

$logos=array("idkdos"=>"Idées cadeaux","promo"=>"Promos");
$descriptions=array("idkdos"=>"/categorie/idees_cadeau_description","promo"=>"/categorie/promo_description");

if(isset($_GET['actif'])){
    if(in_array($_GET['site'],array('part','pro'))){
	$DB_site2->query("UPDATE logopromo SET `".$_GET['site']."`=0 WHERE langue='".$_GET['langue']."' AND type='".$_GET['type']."'");
	$DB_site2->query("UPDATE logopromo SET `".$_GET['site']."`=1 WHERE id=".$_GET['actif']);
    }
}

if(isset($_GET['del']))
    $DB_site2->query("DELETE FROM logopromo WHERE id=".intval($_GET['del']));

if(isset($_FILES['new'])){
    $extension = pathinfo($_FILES['new']['name'], PATHINFO_EXTENSION);
    if($extension=="jpg"){
	      $DB_site2->query("INSERT INTO logopromo SET langue='".$_POST['langue']."', type='".$_POST['type']."', nom='".$_POST['nom']."'");
	      $newid=$DB_site2->insert_id();
	      $bgfile="../configurations/$host/images/logopromo/".$newid.".jpg";
              if(!move_uploaded_file($_FILES['new']['tmp_name'], $bgfile)){
                   $upret="Votre fichier n'a pas pu être envoyé";
              }else
                   $upret="Fond d'écran mis à jour";
    }else{
	      $upret="Seul le format jpg est autorisé";
    }
}

if(isset($_POST['descriptions']) && $_POST['type']=="promo") {
    $sql="";
    foreach($langues as $langue) $sql.=",`description$langue`='".$_POST['description'.$langue]."'";
    $sql=substr($sql,1);
    $DB_site2->query("UPDATE categorie_promo SET $sql WHERE catid='0'");
}



echo "<style>
.table { width:320px; border:solid 1px black; }
.table td { border-top:solid 1px #CCC; }
.formblock { display:block; float:left; width:330px; }
</style>";

echo "<h1>Pages idées cadeaux et promos</h1>";

print $upret;


foreach($logos as $logotype=>$title){
    print "<h3>".$title."</h3>";

    if($logotype=="promo") {
	$desc=$DB_site2->query_first("SELECT * FROM categorie_promo WHERE catid=0");
	print "<form method=\"post\">
	<input type=\"hidden\" name=\"type\" value=\"$logotype\">
	<input type=\"hidden\" name=\"descriptions\" value=\"1\">";

	foreach($langues as $langue) {
	    print "<p>Description $langue <img src=\"images/drapeaux/".$langue.".gif\" width=\"20\"> :<br><textarea name=\"description$langue\" class=\"ckeditor\">".$desc['description'.$langue]."</textarea></p>";
	}
	print "<input type=\"submit\" value=\"Enregistrer les descriptions\">
	</form>";
    }

    foreach($langues as $langue){
	$req=$DB_site2->query("SELECT * FROM logopromo WHERE type='".$logotype."' AND langue='".$langue."' ORDER BY id DESC");
	print "<form method=\"post\" enctype=\"multipart/form-data\" class=\"formblock\">
	<input type=\"hidden\" name=\"langue\" value=\"$langue\">
	<input type=\"hidden\" name=\"type\" value=\"$logotype\">
	<p>".$langue." <img src=\"images/drapeaux/".$langue.".gif\" width=\"20\"></p>
	<table class=\"table\">";
	while($fond=$DB_site2->fetch_array($req)){
	
	    print "<tr>
	      <td>".$fond['nom']."<br><img src=\"../configurations/$host/images/logopromo/".$fond['id'].".jpg\"></td>
	      <td>
		 <img src=\"images/supprimer.gif\" title=\"Supprimer\" onclick=\"document.location='logopromo.php?del=".$fond['id']."';\">
		 <br><br><input type=\"radio\" name=\"fondpart[$langue]\"".($fond['part']==1?" checked":"")." value=\"".$fond['id']."\" onclick=\"document.location='logopromo.php?actif=".$fond['id']."&langue=$langue&type=".$logotype."&site=part'\">part
		 <br><input type=\"radio\" name=\"fondpro[$langue]\"".($fond['pro']==1?" checked":"")." value=\"".$fond['id']."\" onclick=\"document.location='logopromo.php?actif=".$fond['id']."&langue=$langue&type=".$logotype."&site=pro'\">pro
	      </td>
	    </tr>";
	}
	print "</table>
	<p>Ajouter une image :
	<br>Nom : <input type=\"text\" name=\"nom\">
	<br><input type=\"file\" name=\"new\">
	<br><input type=\"submit\" value=\"Envoyer\"></p>
	</form>";
    }
    print "<div class=\"clear\"></div><hr>";
}


include "./footer.php";

?>