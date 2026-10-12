<?php
/**
 * Retour des moyens de paiement : CB / Kwixo (obsolète) / Adyen (paiement CB EN) / Sofinco
 */
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

//Retour depuis Paybox (CB)
if(isset($_GET['facturid']) && !empty($_GET['facturid']) && isset($_GET['trans']) && !empty($_GET['trans'])){
    
    $facture_id     = $_GET['facturid'];
    if(preg_match("/(\d+)\-(\d+)/",$facture_id,$m)){
        $facture_id=$m[1];
        $reglement_id=$m[2];
    }
    $transaction_id = $_GET['trans'];
    $moyen_paiement = 'cb';

    $facture = new facture($DB_site, $facture_id);

    if($_GET['error']=="00000"){
        $status = 'succes';
        $facture->clear_panier($s);
    }else{
        $status = 'refus';
        if($_GET['error'] == "10000"){
            sleep(2);
            $sql = "SELECT valide FROM facture WHERE facturid='".mysqli_real_escape_string($DB_site->link_id,$facture_id)."' AND transaction_id ='".mysqli_real_escape_string($DB_site->link_id,$transaction_id)."' ";
            $req = $DB_site->query_first($sql);
            $valide = $req[0];
            if(intval($valide) == 1){
                $status = 'succes';
                $facture->clear_panier($s);
            }
        }
    }
    

//Retour depuis CB (Crédit Agricole)
} else if(isset($_POST['DATA']) && !empty($_POST['DATA'])){

    $message="message=".$_POST['DATA'];
    $result=exec("$path_bin $message $pathfile");

    $tableau = explode ("!", $result); 
    
    if(strlen($tableau[6]) <= 5){
        $reglement_id = substr($tableau[6], 1);
        $sql = "SELECT *";
        $sql .= " FROM reglement";
        $sql .= " WHERE reglement_id = ".$reglement_id;
        $tabReglement = $DB_site->query_first($sql);
        $facture_id = $tabReglement['facturid'];
    } else {
        $facture_id = $tableau[6];
    }

    $moyen_paiement = 'cb';

    if(is_numeric($facture_id) && $facture_id > 0){

        $moyen_paiement = 'cb';

        $facture = new facture($DB_site, $facture_id);

        switch($facture->valide){
            case 1:
                $status = 'succes';
                $facture->clear_panier($s);
            break;
            
            case 0:
                $status = 'attente';
            break;

            default:
                $status = 'refus';
            break;
        }
    }

//Retour depuis Kwixo
} else if(isset($_POST['RefID']) && !empty($_POST['RefID'])){

    $facture_id     = $_POST['RefID'];
    $moyen_paiement = 'kwixo';

    switch($_POST['Tag']){
        case 0:
            $status = 'attente';
        break;

        case 1:
            $status = 'succes';
            $facture->clear_panier($s);
        break;

        case 2:
            $status = 'refus';
        break;
    }

//Retour depuis Adyen
} else if(isset($_GET['merchantReference'])){

    $facture_id     = $_GET['merchantReference'];
    
    $facture = new facture($DB_site, $facture_id);
    
    $moyen_paiement = "adyen";
    
    switch($_GET['authResult']){
        case "AUTHORISED":
            $status = "succes";
            $facture->clear_panier($s);
        break;

        case "REFUSED":
        case "ERROR":
            $status = "refus";
        break;

        case "CANCELLED":
            $status = "refus";
        break;

        case "PENDING":
            $status = "attente";
        break;
    }

//Retour depuis Sofinco
} else if(isset($_GET['facturid']) && !empty($_GET['facturid']) && isset($_GET['transactionid']) && !empty($_GET['transactionid'])){ 
     
     $facture_id     = $_GET['facturid'];
     $transaction_id = $_GET['transactionid'];
     $moyen_paiement = 'sofinco';
     
     $facture = new facture($DB_site, $facture_id);
     
     switch($facture->valide){
          case 1:
              $status = 'succes';
              $facture->clear_panier($s);
          break;

          case 0:
              $status = 'attente';
          break;

          default:
              $status = 'refus'; 
          break;
          
     }

}

if($moyen_paiement){
    
    if($status == 'succes'){
        setcookie('popup_exit',1,time()+86400);
         /* TRACKING NEXTPERFORMANCE */

        $cpt   = 0;
        $nxtck = '';
        foreach($facture->lignes as $lignefacture){
            if($cpt > 0) $nxtck .= ',';
            $nxtck .= $lignefacture->artid;
            $cpt++;
        }
        $template->parts["nextperformance"] = "<script type=\"text/javascript\" src=\"https://nxtck.com/act.php?tag=43958&pid=".$nxtck."&id=".$facture->facturid."&mt=".$facture->totalttc."&tvalid=".(isset($_COOKIE['nxtck']) && $_COOKIE['nxtck'] == 1 ? 1 : 0)."\" async></script>";

         /* FIN TRACKING NEXTPERFORMANCE */
    }
    
    $template->parts["moyen_paiement"]=$facture->getMoyen($facture->moyenid);
    $template->parts["status_paiement"]=$template->translate("/commande/paiement_".$status);
    $template->parts["message_paiement"]=sprintf($template->translate("/commande/".$moyen_paiement."_".$status), $facture_id).$template->translate("/commande/message_fin");
    

    $template->parts["etape"]    = "<img src=\"/images/templates/".$template->langue."/commande5.jpg\" alt=\"Paiement\" class=\"img img-responsive\">";
    $template->parts["paiement"] = $templateCde;
    $template->evaltemplate("Cde3PaiementCB","paiement");

    $template->evaltemplate("PanierPaiement2","pagecommande");
    $template->evaltemplate("PanierPage","main");

    $template->out();
     
} else {
     $template->redirect("/",302);
     exit;
}
