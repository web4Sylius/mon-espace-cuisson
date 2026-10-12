<?php
$document_root = '/var/www/vhosts/ma-cave-a-vin.fr/httpdocs'; //PROD
//$document_root = '/var/www/ma-cave-a-vin.fr'; //TEST

include_once($document_root."/global.php");
include_once($document_root."/mailconfirmation.php");
include_once($document_root."/admin/dissociation.php");

$params['facture_id']     = $_GET['ti'];
$params['num_dossier']    = $_GET['c2'];
$params['response']       = $_GET['c3'];
$params['montant_credit'] = $_GET['s3'];
$params['teg_annuel']     = $_GET['c4'];
$params['mensualites']    = $_GET['c5'];
$params['moyen_paiement'] = 'sofinco';

//Analyse du status de retour de Sofinco
switch ($params['response']){
     //Envoi OPC vers le client (accord de principe)
     case 0:
          //Mise à jour de la commande au status "validé"
          updateStatutSofinco($params);
          $status = 1;
          $status_txt = 'Accord de principe, dossier à étudier';
          
          $facture = new facture($DB_site, $params['facture_id']);

          mailconfirmation($facture->moyenid, $facture, null);

          break;
     //Refusé
     case 1: 
          //Récupération des informations sur la commande
          $sql = "SELECT *";
          $sql .= " FROM facture";
          $sql .= " WHERE facturid = '".$commande_id."'";
          $facture = $DB_site->query_first($sql);

          //Annulation de la commande, si elle n'avait aucune status renseigné
          if ($facture['valide'] == 0){
               $update_status = true;
               $status = 3;
          }
          $status_txt = 'Refusé';

          break;
     //Envoi OPC vers le client (accord de principe)
     case 2:
          //Mise à jour de la commande au status "validé"
          updateStatutSofinco($params);
          $status = 1;
          $status_txt = 'Accord de principe, dossier à étudier';

          $facture = new facture($DB_site, $params['facture_id']);

          mailconfirmation($facture->moyenid, $facture, null);

          break;
}

//Enregistrement du paiement dans les logs
$logfile = "autoresponseSOF.log";
$fp = fopen($logfile, "a");
fwrite($fp, "\n///// PAIEMENT SOFINCO ///// \n");
fwrite($fp, "Date => ".date('d-m-Y H:i:s')."\n");
foreach ($_GET as $key => $value){
     fwrite($fp, "$key => $value\n");
}
fwrite($fp, "///// PAIEMENT SOFINCO ///// \n\n");
fclose ($fp);

function updateStatutSofinco($params){
     
     global $DB_site;
     
     //Mise à jour infos commande
     $sql = "UPDATE facture SET";
     //$sql .= " valide          = '".$status."',";
     $sql .= " num_dossier    = '".$params['num_dossier']."'";
     $sql .= ", montant_credit = '".$params['montant_credit']."'";
     $sql .= ", TEG_annuel     = '".$params['teg_annuel']."'";
     $sql .= ", nb_mensualites = '".$params['mensualites']."'";
     $sql .= " WHERE facturid = '".$params['facture_id']."'";
     $DB_site->query($sql);
     
     $suivi_txt = ""
     . "Réponse automatique de Sofinco :<br>"
     . "Reponse => ".$params['response']."<br>"
     . "N° dossier => ".$params['num_dossier']."<br>"
     . "Montant credit => ".$params['montant_credit']."<br>"
     . "TEG annuel => ".$params['teg_annuel']."<br>"
     . "Nb mensualités => ".$params['mensualites'];
     
     //Ajout dans le suivi
     $sql = "INSERT INTO suivi_commande SET";
     $sql .= " type          = ''";
     $sql .= ", destinataire = '0'";
     $sql .= ", moyen        = ''";
     $sql .= ", facturid     = ".$params['facture_id'];
     $sql .= ", date         = ".time();
     $sql .= ", commercial   = ''";
     $sql .= ", suivi        = '".addslashes($suivi_txt)."'";
     $sql .= ", rappel       = '0'";
     $sql .= ", rappeltype   = ''";
     $sql .= ", jour         = '0'";
     $sql .= ", pour         = ''";
     $sql .= ", hide         = '0'";
     $DB_site->query($sql);
}