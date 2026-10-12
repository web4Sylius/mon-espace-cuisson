<?php
$rootpath   = "/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
require_once($rootpath."configurations/www.ma-cave-a-vin.fr/config_mysql.php");
require_once($rootpath."class/mysql.php");

ini_set("max_execution_time",180);
ini_set('display_errors','on');

$DB_site2=new DB_Sql;
$DB_site2->appname='boutique';
$DB_site2->appshortname='boutique';
$DB_site2->database=$DBname;
$DB_site2->server=$db_server;
$DB_site2->user=$DBusername;
$DB_site2->password=$DBpassword;
$DB_site2->connect();
$DB_site=clone $DB_site2;

function nice($var, $color = 'black'){
          echo "<div style=\"color:".$color.";\">";
          echo "<p style=\"color:red; font-weight:bold;\">Contenu de la variable :</p>";
          if(is_array($var) || is_object($var)){
                    echo "<pre>";
                    print_r($var);
                    echo "</pre>";   
          } else {
                    var_dump($var);
          }
          echo "</div>";
}

function nicex($var){
          nice($var);
          exit();
}

//Champs à paramétrer
$market_place   = 'Priceminister';
$categorie_mere = 79;
$tabInfos = array(
    'Titre'   => '[marque] - [libelle] - [capacite] bouteilles - [couleur] - [artcode]',
    'Description'    => '[libelle] - Marque : [marque] - Capacité : [capacite] bouteilles - Couleur : [couleur]',
    'Type' => 'Porte-bouteille'
);

$sql = "SELECT catid";
$sql .= " FROM categorie";
$sql .= " WHERE catid IN (".$categorie_mere.")";
$sql .= " OR catid IN (SELECT c.catid FROM categorie c WHERE c.parentid = ".$categorie_mere.")";
$sql .= " OR catid IN (SELECT c2.catid FROM categorie c, categorie c2 WHERE c2.parentid = c.catid AND c.parentid = ".$categorie_mere.")";
$req = $DB_site2->query($sql);
while($row = $DB_site2->fetch_array($req)){
     
     $catid = $row['catid'];
     
     foreach($tabInfos as $key => $value){
          
          $sql = "SELECT id";
          $sql .= " FROM market_place_catid";
          $sql .= " WHERE market_place = '".$market_place."'";
          $sql .= " AND catid = ".intval($catid);
          $sql .= " AND champ = '".$key."'";
          $contenu_id = $DB_site2->query_first($sql);
          
          if(!empty($contenu_id) && is_array($contenu_id)){
               
               $contenu_id = $contenu_id[0];
               
               echo "<br><span style=\"color:green;\">Mise à jour des informations pour la catégorie \"".$catid."\" : \"".$key."\" => \"".$value."\"</span>";
               
               $sql = "UPDATE market_place_catid SET";
               $sql .= " valeur = '".addslashes($value)."'";
               $sql .= " WHERE id = ".intval($contenu_id);
               
          } else {
               
               echo "<br><span style=\"color:red;\">Insertion des informations pour la catégorie \"".$catid."\" : \"".$key."\" => \"".$value."\"</span>";
               
               $sql = "INSERT INTO market_place_catid (";
               $sql .= " market_place";
               $sql .= ", catid";
               $sql .= ", champ";
               $sql .= ", valeur";
               $sql .= ") VALUES (";
               $sql .= "'".$market_place."'";
               $sql .= ", ".intval($catid);
               $sql .= ", '".$key."'";
               $sql .= ", '".addslashes($value)."'";
               $sql .= ")";

          }
          
          $DB_site2->query($sql);
          
     }
     
}