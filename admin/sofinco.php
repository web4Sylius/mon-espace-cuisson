<?php

/// sofinco
include_once($_SERVER['DOCUMENT_ROOT'].'/admin/host_config_admin.php');
include_once($_SERVER['DOCUMENT_ROOT']."/class/host.php");
include_once($rootpath."class/email.php");
$host_conf = new Host();
$host=$host_conf->host;
include_once($_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/config_variables.php");

$providerId="99102122272";
//$apiUrl="https://r7weblong.transcred.com/websrv/index.asp";
$apiUrl="https://weblong.transcred.com/websrv/index.asp";


     /// on récupère les factures sofinco qui ne sont pas traitées
$factures=$DB_site2->query("SELECT facturid,heurefacture FROM facture WHERE moyenid=17 AND valide=0");
$array_dossiers=array();
while($facture=$DB_site2->fetch_array($factures)) {
     $facturesid[]=$facture['facturid'];
     $date=date('Ymd',$facture['heurefacture']);
     $date_end=$facture['heurefacture']+86400;
     $date_end=date('Ymd',$date_end);
          /// on envoie les dates des factures dans le flux pour récupérer les bons n° de dossiers
     $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
     <Service ServiceName=\"FLUX_A1\">
     <IN StartDate=\"".$date."\" EndDate=\"".$date_end."\">
     <VendeursCollection>
     <Vendeur>99102122272</Vendeur>
     </VendeursCollection>
     </IN>
     </Service>
     ";

     $ch = curl_init();
     curl_setopt ($ch, CURLOPT_URL,$apiUrl);
     curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
     curl_setopt($ch, CURLOPT_POST, true); 
     curl_setopt($ch, CURLOPT_POSTFIELDS, $xml); 
     curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
     curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20); 
     curl_setopt($ch, CURLOPT_TIMEOUT, 20);   
     $data = curl_exec($ch);
     curl_close($ch);

          /// on transforme le xml de retour en array pour mieux récupérer les infos
     $retour_xml=simplexml_load_string($data, 'SimpleXMLElement');
     $json = json_encode($retour_xml);
     $array = json_decode($json,TRUE);
     $array_liste=array($array['OUT']['DossiersCollection']['Dossier']); 

          /// s'il y a plusieurs dossiers ou non le même jour
     foreach($array_liste as $dossier) {
          $nb_sous_arr=count($dossier);
          if($nb_sous_arr > 1){
               for($i=0;$i<$nb_sous_arr;$i++) {
                    $array_dossiers[]=$dossier[$i];
               }
          }
          else {
               $array_dossiers[]=$dossier;
          }    
     }
}
//81641658397
$array_dossiers=array_filter(array_unique($array_dossiers,SORT_REGULAR));
foreach($array_dossiers as $dossier) {
     /// on vérifie que ce soit le bon dossier lié au bon n° de commande
     if(in_array($dossier['@attributes']['CommandNumber'], $facturesid)) {

          /// on enregistre le n° de dossier en bdd pour pouvoir utiliser les flux plus facilement ailleurs
          $numerodossier = $DB_site2->query_first("SELECT num_dossier FROM facture WHERE facturid=".$dossier['@attributes']['CommandNumber']);
          if($numerodossier['num_dossier']===''){
               $sql = "UPDATE facture SET num_dossier = '".$dossier['@attributes']['DossierNumber']."' WHERE facturid = ".$dossier['@attributes']['CommandNumber'];
               $DB_site2->query($sql);
               $suivi=addslashes("Sofinco : Dossier n°".$dossier['@attributes']['DossierNumber']." de la commande #CDE".$dossier['@attributes']['CommandNumber']."# vient d'être créé.");
               $DB_site2->query("INSERT INTO suivi_commande SET facturid=".$dossier['@attributes']['CommandNumber'].", date=".time().", commercial='', suivi='$suivi'");

               if($dossier['@attributes']['DossierStatus'] !== "REF"){
                    $mail=new email();
                    $mail->destinataire="pr@ma-cave-a-vin.fr";
                    $mail->expediteur="contact@ma-cave-a-vin.fr";
                    $mail->to = array('webmaster4@ma-cave-a-vin.fr');
                    $mail->subject="Vérification commande Sofinco - confirmation commande";
                    $mail->body="Le dossier n°".$dossier['@attributes']['DossierNumber']." vient d'être créé, il faut envoyer une confirmation de commande <a href='https://www.ma-cave-a-vin.fr/admin/facture.php?action=editer&facturid=".$dossier['@attributes']['CommandNumber']."'>#CDE".$dossier['@attributes']['CommandNumber']."#</a>";
                    $mail->sendmail();
               }
          }

               $alert="";
               switch($dossier['@attributes']['DossierStatus']){
                    case "ATT":
                    case "AFI":
                    $alert= "<br>Ce dossier a été validé par Sofinco. La commande peut être traitée :";
                    $destinataires="contact@ma-cave-a-vin.fr";
                    $statut= "dossier en attente de financement";
                    break;
                    case "ETU":
                    $statut= "dossier en étude";
                    break;
                    case "CLI":
                    $statut= "Attente retour client";
                    break;
                    case "REF":
                    $destinataires="contact@ma-cave-a-vin.fr";
                    $statut= "refus";
                    break;
                    case "ANN":
                    $destinataires="contact@ma-cave-a-vin.fr";
                    $statut= "annulé ou sans suite";
                    break;
                    case "FIN":
                    $alert= "<br>Le financement a été effectué pour ce dossier/cette commande.";
                    $statut= "financé";
                    break;
                    case "NFD":
                    $statut= "non trouvé";
                    break;
                    case "ERR":
                    $destinataires="webmaster4@ma-cave-a-vin.fr";
                    $statut= "non traité car erreur technique";
                    break;
               }

               $message="";
               $message.=$alert;
               $message.="<br>Dossier n°".$dossier['@attributes']['DossierNumber'].". Commande <a href=\"https://www.ma-cave-a-vin.fr/admin/facture.php?action=editer&facturid=".$dossier['@attributes']['CommandNumber']."\">n°".$dossier['@attributes']['CommandNumber']."</a>. Statut: <b>".$dossier['@attributes']['DossierStatus']." - ".$statut."</b>. Montant du dossier: ".substr_replace($dossier['@attributes']['DossierValue'], ',', -2, 0)."<br>";
               $suivi="Sofinco : Dossier n°".$dossier['@attributes']['DossierNumber'].". Commande n°".$dossier['@attributes']['CommandNumber'].". Statut: ".$dossier['@attributes']['DossierStatus'];

               $suivitexte = $DB_site2->query_first("SELECT suivi FROM suivi_commande WHERE facturid=".$dossier['@attributes']['CommandNumber']." AND suivi ='".$suivi."'");
               if($suivitexte['suivi'] === '' || is_null($suivitexte['suivi'])){
                    $DB_site2->query("INSERT INTO suivi_commande SET facturid=".$dossier['@attributes']['CommandNumber'].", date=".time().", commercial='', suivi='$suivi'");

                    $mail=new email();
                    //$mail->destinataire="webmaster4@ma-cave-a-vin.fr";
                    $mail->destinataire=$destinataires;
                    $mail->expediteur="contact@ma-cave-a-vin.fr";
                    $mail->to = array('cecile@ma-cave-a-vin.fr','webmaster4@ma-cave-a-vin.fr');
                    $mail->subject="Dossier Sofinco #CDE".$dossier['@attributes']['CommandNumber']."#";
                    $mail->body="Le dossier suivant a changé de statut :<br>".$message;
                    $mail->sendmail();
                    $message="";
               }
     }    
}


/*
$xml2="<Service ServiceName=\"FLUX_D\">
<IN>
<DossiersCollection>
<Dossier DossierNumber=\"81641690096\"/>
</DossiersCollection>
</IN>
</Service>";

 $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL,$apiUrl);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POST, true); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml2); 
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true); 
$data2 = curl_exec($ch);

curl_close($ch);
 $retour_xml=simplexml_load_string($data2, 'SimpleXMLElement');
     $json = json_encode($retour_xml);
     $array = json_decode($json,TRUE);
     $array_dossiers[]=$array['OUT']['DossiersCollection']['Dossier']['@attributes']; 

var_dump(array_filter(array_unique($array_dossiers,SORT_REGULAR)));
*/