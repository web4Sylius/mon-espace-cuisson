<?php
//Script pour insertion des garanties SFG pour les professionnels

require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

//Récupération des données du fichiers d'import
$entete = true;
$nom_fichier = 'tarifs_sfg_professionnels.csv';
$test = false;

$tabTarifs = extractCSV($nom_fichier);

//Récupération des différentes garanties
//Critères : Fournisseur "SFG" / Pays "France
$sql = "SELECT *";
$sql .= " FROM garantie";
$sql .= " WHERE fournisseur LIKE 'SFG'";
$sql .= " AND garantie_client_id = 2";
//France métropolitaine (hors Corse)/Corse/Belgique/Luxembourg/Monaco
$sql .= " AND (pays LIKE '%57%' OR pays LIKE '%181%' OR pays LIKE '%17%' OR pays LIKE '%111%' OR pays LIKE '%99%')";
$req = $DB_site->query($sql);
$tabGarantie = array();
while($row = $DB_site->fetch_array($req)){
     $tabGarantie[] = $row;
}

//Boucle sur liste des garanties SFG
foreach($tabGarantie as $la_garantie){
     
    //Boucle sur les lignes importées pour retrouver la garantie correspondante
     $cnt = 0;
     foreach($tabTarifs as $le_tarif){
          
          //On zappe les entêtes de colonnes
          if($cnt > 0){
               
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
          
          $cnt++;
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

