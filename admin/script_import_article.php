<?php
require_once __DIR__.'/../secrets.php';
ini_set("memory_limit", "6000M");
include_once($_SERVER["DOCUMENT_ROOT"]."/class/mysql.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/class/email.php");


$db_server = "localhost";
$DBname="fusion";
$DBusername=IMPORT_DB_USER; 
$DBpassword=IMPORT_DB_PASSWORD;

$DB_site=new DB_Sql;
$DB_site->appname='admin boutique';
$DB_site->appshortname='aro';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

$DB_site2=new DB_Sql;
$DB_site2->appname='admin boutique';
$DB_site2->appshortname='aro';
$DB_site2->database="fusiongarantim";
$DB_site2->server="localhost";
$DB_site2->user=IMPORT_DB2_USER;
$DB_site2->password=IMPORT_DB2_PASSWORD;
$DB_site2->connect();

//Table article
$sql="SELECT * FROM article";
$articles = $DB_site2->query($sql);
$req="";
while($article= $DB_site2->fetch_array($articles)){

	$req.="INSERT INTO article (artid, artcode, catid,active , typearticleid, prix, prixht, tva, tauxchoisi, poid, image, libelleFR, descriptionFR, description2FR, Titre2FR,commandable, ref_titleFR, ref_descriptionFR, ref_keywordsFR, page, pageposition, delailivraison, date_appro, montage, gamme, gammeFR, artcode_fabricant, artcode_eurocave, codebarre, coloris, argument, dimensions, hauteur, largeur, profondeur, volume, poidsvolume, paht, prixconseilleht, ecopart, ecopartht, prixtrspt, innovant, design, emballage_hauteur, emballage_largeur, emballage_profondeur, emballage_poids, hauteur_encastrement, largeur_encastrement, profondeur_encastrement, infos_encastrement, paysfabrication, paysid, douanier, refs_fabricant, energy_label, facture_diss, ventes, stock, redirect) VALUES ('$article[artid]','$article[artcode]',$article[catid],$article[active],$article[typearticleid],'$article[prix]','$article[prixht]','$article[tva]','$article[tauxchoisi]','$article[poid]','$article[image]','".mysqli_real_escape_string($DB_site->link_id,addslashes($article[libelleFR]))."','".mysqli_real_escape_string($DB_site->link_id,addslashes($article[descriptionFR]))."','".mysqli_real_escape_string($DB_site->link_id,addslashes($article[description2FR]))."','".mysqli_real_escape_string($DB_site->link_id,addslashes($article[Titre2FR]))."','$article[commandable]','".mysqli_real_escape_string($DB_site->link_id,addslashes($article[ref_titleFR]))."','".mysqli_real_escape_string($DB_site->link_id,addslashes($article[ref_descriptionFR]))."','$article[ref_keywordsFR]','$article[page]','$article[pageposition]','$article[delailivraison]','$article[date_appro]','$article[montage]','$article[gamme]','$article[gammeFR]','$article[artcode_fabricant]','$article[artcode_eurocave]','$article[codebarre]','$article[coloris]','$article[argument]','$article[dimensions]','$article[hauteur]','$article[largeur]','$article[profondeur]','$article[volume]','$article[poidsvolume]','$article[paht]','$article[prixconseilleht]','$article[ecopart]','$article[ecopartht]','$article[prixtrspt]','$article[innovant]','$article[design]','$article[emballage_hauteur]','$article[emballage_largeur]','$article[emballage_profondeur]','$article[emballage_poids]','$article[hauteur_encastrement]','$article[largeur_encastrement]','$article[profondeur_encastrement]','$article[infos_encastrement]','$article[paysfabrication]','$article[paysid]','$article[douanier]','$article[refs_fabricant]','$article[energy_label]','$article[facture_diss]','$article[ventes]','$article[stock]','$article[redirect]');<br>";

}

$truncate_article = "TRUNCATE TABLE article";
//$insert = "INSERT INTO article (artid, artcode, catid,active , typearticleid, prix, prixht, tva, tauxchoisi, poid, image, libelleFR, descriptionFR, description2FR, Titre2FR,commandable, ref_titleFR, ref_descriptionFR, ref_keywordsFR, page, pageposition, delailivraison, date_appro, montage, gamme, gammeFR, artcode_fabricant, artcode_eurocave, codebarre, coloris, argument, dimensions, hauteur, largeur, profondeur, volume, poidsvolume, paht, prixconseilleht, ecopart, ecopartht, prixtrspt, innovant, design, emballage_hauteur, emballage_largeur, emballage_profondeur, emballage_poids, hauteur_encastrement, largeur_encastrement, profondeur_encastrement, infos_encastrement, paysfabrication, paysid, douanier, refs_fabricant, energy_label, facture_diss, ventes, stock, redirect) VALUES ";

//$req = substr($req, 0,-1);
$insert_article = $req;

//echo $req;
file_put_contents('article.sql', $req);

$handle=file_get_contents('article.sql');

readfile($handle);
//echo $insert_article;
//$DB_site->query($truncate_article);
//$DB_site->query($insert_article);

















?>