<?php

// KWIXO

include_once($_SERVER['DOCUMENT_ROOT']."/global.php");
include_once($_SERVER['DOCUMENT_ROOT']."/mailconfirmation.php");
include_once($_SERVER['DOCUMENT_ROOT']."/admin/dissociation.php");

$commande_id = $_POST['RefID'];
$update_status = false;

//Analyse du status de retour de Kwixo
switch ($_POST['Tag']){
     //Commande Avortée
     case 0:
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
          
          break;
     //Commande acceptée validée
     case 1: 
          //Mise à jour de la commande au status "validé"
          $update_status = true;
          $status = 1;
          
          $facture = new facture($DB_site, $commande_id);
          
          mailconfirmation(8,$facture,null);
          $diss=new dissociation($DB_site,$commande_id);
          
          break;
     //Ko Commande refusée
     case 2:
          //Mise à jour de la commande au status "annulé"
          $update_status = true;
          $status = 3;
          
          break;
     //U Commande sous surveillance FIA-NET
     case 3: 
          //Mise à jour au status "non traitée"
          $update_status = true;
          $status = 0;
          
          break;
     //Surveillance OK, la commande est validée par Kwixo après coup
     case 10: 
          //Récupération de l'état actuel de la commande
          $sql = "SELECT valide";
          $sql .= " FROM facture";
          $sql .= " WHERE facturid = '".$commande_id."'";
          $etatactuelbase = $DB_site->query_first($sql);
          
          //Si la commande a un autre status que "expédiée"
          if ($etatactuelbase['valide'] != 5) {
               //Mise à jour en status "validée"
               $update_status = true;
               $status = 1;

               $facture = new facture($DB_site,$commande_id);
               
               mailconfirmation(8,$facture,null);
               $diss = new dissociation($DB_site,$commande_id);
          }
          break;
     //Surveillance KO, la commande est annulée par Kwixo après coup   
     case '11': 
          //Mise à jour en status "annulée"
          $update_status = true;
          $status = 3;
          
          break;
     //OK Cloture de la transaction
     case '100': 
          //Récupération de l'état actuel de la commande
          $sql = "SELECT valide";
          $sql .= " FROM facture";
          $sql .= " WHERE facturid = '".$commande_id."'";
          $etatactuelbase = $DB_site->query_first($sql);
          
          //Si la commande a un autre status que "expédiée"
          if ($etatactuelbase['valide'] != 5) {
               //Mise à jour en status "validée"
               $update_status = true;
               $status = 1;

               $facture = new facture($DB_site,$commande_id);
               
               mailconfirmation(8,$facture,null);
               $diss = new dissociation($DB_site,$commande_id);
          }
          break;
     //KO Annulation de la transaction
     case '101': 
          $update_status = true;
          $status = 3;
          break;
     }

if($update_status){
     $sql = "UPDATE facture SET";
     $sql .= " valide = '".$status."'";
     $sql .= " WHERE facturid = '".$commande_id."'";
     $DB_site->query($sql);
}
     
//Enregistrement du paiement dans les logs
$logfile = "autoresponseRP.log";
$fp = fopen($logfile, "a");
fwrite($fp, "\n///// PAIEMENT RECEIVE AND PAY ///// \n");
fwrite($fp, "Date => ".date('d-m-Y H:i:s')."\n");
foreach ($_POST as $key => $value){
     fwrite($fp, "$key => $value\n");
}
fwrite($fp, "///// PAIEMENT RECEIVE AND PAY ///// \n\n");
fclose ($fp);
?>