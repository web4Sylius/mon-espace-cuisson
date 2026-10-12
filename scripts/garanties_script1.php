<?php
//Script pour insertion des garanties SFG

require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

//Récupération des données du fichiers d'import
$entete = true;
$nom_fichier = 'tarifs_sfg_particuliers.csv';
$test = false;

$debut     = $entete ? 1 : 0;
$tabTarifs = extractCSV($nom_fichier);

//Récupération de toutes les garanties françaises
$sql = "SELECT *";
$sql .= " FROM garantie";
$sql .= " WHERE garantie_client_id = 1";
//France métropolitaine (hors Corse)/Corse/Belgique/Luxembourg/Monaco
$sql .= " AND (pays LIKE '%57%' OR pays LIKE '%181%' OR pays LIKE '%17%' OR pays LIKE '%111%' OR pays LIKE '%99%')";
$req = $DB_site->query($sql);
$tabGarantie = array();
while($row = $DB_site->fetch_array($req)){
     $tabGarantie[] = $row;
}

//On commence par vider la table dans laquelle on effectue l'import
$sql = "TRUNCATE garantie_tranche";
if(!$test) $DB_site->query($sql);

//Boucle sur liste des garanties SFG
foreach($tabGarantie as $la_garantie){
     
     /*****************\
      * GARANTIES PRO *
     \*****************/
     
     //Création de la garantie équivalente pro
     $sql = "INSERT INTO garantie (";
     $sql .= "fournisseur";
     $sql .= ", marques";
     $sql .= ", types_produits";
     $sql .= ", utilisation_garantie";
     $sql .= ", pays";
     $sql .= ", duree";
     $sql .= ", type_garantie";
     $sql .= ", devise";
     $sql .= ", cumul";
     $sql .= ", comm";
     $sql .= ", garantie_client_id";
     $sql .= ") VALUES (";
     $sql .= "'".$la_garantie['fournisseur']."'";
     $sql .= ", '".$la_garantie['marques']."'";
     $sql .= ", '".$la_garantie['types_produits']."'";
     $sql .= ", '".$la_garantie['utilisation_garantie']."'";
     $sql .= ", '".$la_garantie['pays']."'";
     $sql .= ", '".$la_garantie['duree']."'";
     $sql .= ", '".$la_garantie['type_garantie']."'";
     $sql .= ", '".$la_garantie['devise']."'";
     $sql .= ", '".$la_garantie['cumul']."'";
     $sql .= ", '".$la_garantie['comm']."'";
     $sql .= ", 2";
     $sql .= ")";
     if(!$test) $DB_site->query($sql);
     
     if(!$test) $garantie_id = $DB_site->insert_id();
     
     //On crée la tranche uniquement pour autres fournisseurs que SFG, sinon, ça se fait dans le script 2
     if($la_garantie['fournisseur'] != 'SFG'){
          
          //On commence par créer la tranche de base
          $sql = "INSERT INTO garantie_tranche (";
          $sql .= " garantie_tranche_prix_mini,";
          $sql .= " garantie_tranche_prix_maxi,";
          $sql .= " garantie_tranche_paht,";
          $sql .= " garantie_tranche_prix,";
          $sql .= " garantie_tranche_ppi,";
          $sql .= " garantieid";
          $sql .= " ) VALUES (";
          $sql .= "'0'";
          $sql .= ", '19999.99'";
          $sql .= ", '".clean_prix($la_garantie['paht'])."'";
          $sql .= ", '".$la_garantie['prix']."'";
          $sql .= ", '".$la_garantie['ppi']."'";
          $sql .= ", ".$garantie_id;
          $sql .= ")";
          if(!$test) $DB_site->query($sql);
     }
     
     //Récupération des associations article/garantie de la garantie particulier pour l'appliquer aussi à la professionnelle
     $aLiaison = array();
     $sql = "SELECT *";
     $sql .= " FROM article_garantie_liste";
     $sql .= " WHERE garantieid = ".$la_garantie['garantieid'];
     $req = $DB_site->query($sql);
     while($row = $DB_site->fetch_array($req)){
          $aLiaison[] = $row;
     }
     
     //Insertion des associations article/garantie
     foreach($aLiaison as $la_liaison){
          $sql = "INSERT INTO article_garantie_liste (";
          $sql .= "artid";
          $sql .= ", garantieid";
          $sql .= ", paht";
          $sql .= ", prix";
          $sql .= ", ppi";
          $sql .= ") VALUES (";
          $sql .= $la_liaison['artid'];
          $sql .= ", ".$garantie_id;
          $sql .= ", 0";
          $sql .= ", 0";
          $sql .= ", 0";
          $sql .= ")";
          if(!$test) $DB_site->query($sql);
     }
     
     /******************\
      * GARANTIES PART *
     \******************/
     
     //On commence par créer la tranche de base, de 0 à 4999 (garanties actuellement en place), pour tous les fournisseurs
     $sql = "INSERT INTO garantie_tranche (";
     $sql .= " garantie_tranche_prix_mini,";
     $sql .= " garantie_tranche_prix_maxi,";
     $sql .= " garantie_tranche_paht,";
     $sql .= " garantie_tranche_prix,";
     $sql .= " garantie_tranche_ppi,";
     $sql .= " garantieid";
     $sql .= " ) VALUES (";
     $sql .= "'0'";
     $sql .= ", '".($la_garantie['fournisseur'] == 'SFG' ? '4999.99' : '19999.99')."'";
     $sql .= ", '".clean_prix($la_garantie['paht'])."'";
     $sql .= ", '".$la_garantie['prix']."'";
     $sql .= ", '".$la_garantie['ppi']."'";
     $sql .= ", ".$la_garantie['garantieid'];
     $sql .= ")";
     if(!$test) $DB_site->query($sql);
     
     //On met en place les autres tranches, uniquement pour SFG
     if($la_garantie['fournisseur'] == 'SFG'){
          
          //Boucle sur les lignes importées pour retrouver la garantie correspondante
          foreach($tabTarifs as $le_tarif){

               $tranche_basse  = $le_tarif[0];
               $tranche_haute  = $le_tarif[1];
               $duree_annee    = $le_tarif[2];
               $extension      = $le_tarif[3] == 'OUI' ? 1 : 2;
               $prix_achat     = $le_tarif[5];
               $prix_vente     = $le_tarif[6];
               $prix_conseille = $le_tarif[7];

               switch($le_tarif[4]){
                    case 'PMOD':
                         $type = 5;
                         break;
                    case 'PIECES':
                         $type = 1;
                         break;
                    case 'PMO':
                         $type = 2;
                         break;
                    case 'PMORU':
                         $type = 4;
                         break;
               }

               //Si durée correspondante + extension correspondante + même type de garantie (Retour usine, PMOD, etc...)
               if($la_garantie['duree'] == $duree_annee && $la_garantie['cumul'] == $extension && $la_garantie['type_garantie'] == $type){
                    
                    $prix = clean_prix($prix_vente);
                    $ppi  = clean_prix($prix_conseille);

                    $tabPrix = array('base' => (string)$prix);
                    $tabPpi  = array('base' => (string)$ppi);
                    
                    //Création de la tranche
                    $sql = "INSERT INTO garantie_tranche (";
                    $sql .= " garantie_tranche_prix_mini,";
                    $sql .= " garantie_tranche_prix_maxi,";
                    $sql .= " garantie_tranche_paht,";
                    $sql .= " garantie_tranche_prix,";
                    $sql .= " garantie_tranche_ppi,";
                    $sql .= " garantieid";
                    $sql .= " ) VALUES (";
                    $sql .= "'".clean_prix($tranche_basse)."'";
                    $sql .= ", '".clean_prix($tranche_haute)."'";
                    $sql .= ", '".clean_prix($prix_achat)."'";
                    $sql .= ", '".serialize($tabPrix)."'";
                    $sql .= ", '".serialize($tabPpi)."'";
                    $sql .= ", ".$la_garantie['garantieid'];
                    $sql .= ")";
                    if(!$test) $DB_site->query($sql);
               }
          }
     }
}

function extractCSV($file){
     $row = 1;
     if (($handle = fopen($file, "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
               $tabRetour[] = $data;
          }
          fclose($handle);
     }
     
     return $tabRetour;
}

function clean_prix($prix){
     $prix = str_replace('¤', '', utf8_decode($prix));
     $prix = str_replace(',', '.', $prix);
     $prix = trim($prix);
     
     return round($prix,2);
}

