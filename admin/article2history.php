<?php
require_once __DIR__.'/../secrets.php';

$sqlsite=mysql_connect("localhost",LEGACY_DB_USER,LEGACY_DB_PASSWORD);
mysql_select_db("macaveavsite",$sqlsite);

$sqlm1=mysql_connect("localhost",LEGACY_DB_USER,LEGACY_DB_PASSWORD,true);
mysql_select_db("macaveavsite-1",$sqlm1);

$fields=array("absence_vibration"=>"absence_vibration",
"affichage"=>"affichage",
"artcode"=>"artcode",
"artcode_eurocave"=>"artcode_eurocave",
"artcode_fabricant"=>"artcode_fabricant",
"boutons_reglage"=>"boutons_reglage",
"bouton_test"=>"bouton_test",
"capacite_bouteilles"=>"capacite_bouteilles",
"capacite_l"=>"capacite_l",
"capacite_maxi"=>"capacite_maxi",
"capacite_mini"=>"capacite_mini",
"coloris"=>"coloris",
"coloris_porte"=>"coloris_porte",
"conso_euro"=>"conso_euro",
"conso_kw"=>"conso_kw",
"date_appro"=>"date_reappro",
"delailivraison"=>"delailivraison",
"delailivraison_txt"=>"delailivraison_txt",
"dimensions"=>"dimensions",
"douanier"=>"douanier",
"eclairage"=>"eclairage",
"ecopart"=>"ecopart",
"ecopartht"=>"ecopartht",
"emballage_hauteur"=>"emballage_hauteur",
"emballage_largeur"=>"emballage_largeur",
"emballage_profondeur"=>"emballage_profondeur",
"emballage_poids"=>"emballage_poids",
"energy_label"=>"energy_label",
"filtre_charbon"=>"filtre_charbon",
"gamme"=>"gamme",
"gammeFR"=>"gammeFR",
"gammeEN"=>"gammeEN",
"gammeSP"=>"gammeSP",
"hauteur"=>"hauteur_txt",
"hygrometre"=>"hygrometre",
"hygrometrie"=>"hygrometrie",
"interrupteur"=>"interrupteur",
"largeur"=>"largeur_txt",
"libelleEN"=>"libelleEN",
"libelleFR"=>"libelleFR",
"modele"=>"modele",
"modularite_interieure"=>"modularite_interieure",
"marqueid"=>"mq",
"nbr_temperature"=>"nbr_temperature",
"commandable"=>"opt_commandable",
"active"=>"opt_visible",
"paht"=>"paht",
"paysfabrication"=>"paysfabrication",
"paysid"=>"paysidfab",
"pieds_reglables"=>"pieds_reglables",
"poid"=>"poid",
"poignee"=>"poignee",
"prix"=>"prix",
"prixconseilleht"=>"prixconseilleht",
"prixht"=>"prixht",
"profondeur"=>"profondeur_txt",
"rangements_txt"=>"rangements_txt",
"refs_fabricant"=>"refscons",
"reglage"=>"reglage",
"regulation"=>"regulation",
"roulettes"=>"roulettes",
"serrure"=>"serrure",
"tauxchoisi"=>"tauxtva",
"temperature"=>"temperature",
"temperature_cave"=>"temperature_cave",
"thermometre"=>"thermometre",
"titre2EN"=>"titre2EN",
"titre2FR"=>"titre2FR",
"usage"=>"usage",
"version_porte"=>"version_porte",
"volume"=>"volume",
"volumepoids"=>"volumepoids");

$requete=mysql_query("SELECT * FROM article a inner join article_marque using (artid)",$sqlm1);
while($row=mysql_fetch_array($requete)){
      print $row['artcode']."<br>";
      foreach($fields as $field=>$key){
	    mysql_query("INSERT INTO article_history SET artid=".$row['artid'].",utilisateur='cc',date=1311342614,`key`='".$key."',value='".addslashes($row[$field])."'",$sqlsite);
	    print mysql_error();
      }
}

?>
