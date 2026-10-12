<?php
require "admin_global.php";
include "./header.php";
include "./menu.php";

$msg="";
if(isset($_FILES['pdf'])){
    $extension = strtolower(pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION));
    if($extension=="pdf"){
        if(move_uploaded_file($_FILES['pdf']['tmp_name'], "pdf/aide_fiscale.pdf")){
            $msg="<p>Fichier modifié</p>";
        }else
            $msg="<p style=\"color:red;\">Problème de chargement du fichier</p>";
    }else
        $msg="<p style=\"color:red;\">Le fichier doit être au format pdf</p>";
}

if(isset($_POST['gaz'])){
    $DB_site2->query("TRUNCATE TABLE `gaz_aide`");
    foreach($_POST['gaz'] as $path)
        $DB_site2->query("INSERT INTO gaz_aide SET path='".mysqli_real_escape_string($DB_site2->link_id,$path)."'");
}

print "<h1>Aide fiscale</h1>";

print <<<HTML
<p>Ce fichier sera envoyé avec la facture aux clients pros en France qui ont commandé un produit avec les gaz sélectionnés</p>
<p><a href="pdf/aide_fiscale.pdf" target="_blank"><img src="images/icon/pdf.png" width="40"> Aide fiscale</a></p>
<form method="post" enctype="multipart/form-data">
    $msg
    Changer le fichier : <input type="file" name="pdf"> <input type="submit" value="OK">
</form>
HTML;

print <<<HTML
<h3>Gaz concernés</h3>
<form method="post">
HTML;

$listegaz=array();
$req=$DB_site2->query("SELECT * FROM gaz_aide");
while($g=$DB_site2->fetch_array($req)){
    $listegaz[]=$g['path'];
}

$req=$DB_site2->query("SELECT * FROM traduction WHERE path LIKE '/article/gaz/%' ORDER BY `FR`");
while($trad=$DB_site2->fetch_array($req)){
    $checked=in_array($trad['path'],$listegaz) ? " checked" : "";
    print <<<HTML
        <label><input type="checkbox" name="gaz[]" value="$trad[path]"$checked> $trad[FR]</label><br>
HTML;
}

print <<<HTML
<input type="submit" value="Enregistrer">
</form>
HTML;

include "./footer.php";

?>
