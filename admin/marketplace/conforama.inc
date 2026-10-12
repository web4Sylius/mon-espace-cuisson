<?php

include_once("../admin_global.php");
include_once("../../class/facture.class.php");
include_once("../../class/article.class.php");
include_once("conforama.class.php");

?>

<p style="text-align: center; margin: 10px 0; padding: 0; font-size: 13px; font-weight: bold;"><?php echo $titre; ?></p>
 <table cellspacing="0" width="800" align="center" style="margin-top:10px;" class="list">
    <tr>
        <td height="30" class="row_title" style="border-bottom: 1px solid #990000;">N° Cde</td>
        <td class="row_title" style="border-bottom: 1px solid #990000;">Date</td>
        <td class="row_title" style="border-bottom: 1px solid #990000;">Réf. article(s)</td>
        <td class="row_title" style="border-bottom: 1px solid #990000;">Prix Commande(TTC)</br>dont X€ de port</td>
        <td class="row_title" style="border-bottom: 1px solid #990000;">Adr. facturation</td>
        <td class="row_title" style="border-bottom: 1px solid #990000;">Adr. livraison</td>
        <td class="row_title" style="border-bottom: 1px solid #990000;">Etat</td>
        <td class="row_title" style="border-bottom: 1px solid #990000;">Création Commande</td>
    </tr>
    <?php
    
    
    $start_date=new datetime("30 days ago");
    $end_date=null;
    if(isset($_GET['choixDate'])){
        switch($_GET['choixDate']){
            case "60j":
            $start_date=new DateTime("60 days ago");
            $end_date=new DateTime("30 days ago");
            break;
            
            default:
            break;
        }
    }

    if(isset($_GET['auDate'])){
        $start_date=DateTime::createFromFormat("j/n/Y",$_GET['duDate']);
        $end_date=DateTime::createFromFormat("j/n/Y",$_GET['auDate']);
    }
    
    $confo=new conforama();
    foreach($confo->orders_bydate($start_date,$end_date) as $order){
        unset($facture);
        unset($info_commande);
        $commande_site = $DB_site2->query_first("SELECT * FROM facture WHERE mp_facturid='".mysqli_real_escape_string($DB_site2->link_id,$order->order_id)."'");
        if($commande_site!=""){
            $facture=new facture($DB_site2,$commande_site['facturid']);
            $lien_commande = BACK_URL."/facture.php?action=editer&facturid=".$facture->facturid;
            $info_commande="<br><a href=\"".$lien_commande."\" target=\"_blank\">".$facture->facturid."</a>";
            if (round($facture->totalttc,2) == round($order->total_price,2)){
                $info_commande.="<br>".number_format($facture->totalttc,2,',',' ')."€";
            }else{
                $info_commande.="<span style=\"color:red;\">
                      ".$facture->totalttc."€ Attention les prix ne correspondent pas
                </span>";
            }
        }
        
        $dt=new datetime($order->created_date);
        $date_commande=$dt->format("d/m/Y H:i:s");
    
        $articles="";
        $formarticles="";
        $i=0;
        $linescount=count($order->order_lines);
        foreach($order->order_lines as $line){
            if(is_numeric($line->offer_sku)){
                $artid = $line->offer_sku;
            }else{
                $artid = $DB_site2->query_first("SELECT artid FROM article WHERE artcode='".$line->offer_sku."'");
                $artid = $artid[0];
            }
            $article=new produit($DB_site2,"FR","base",array(),intval($artid));
            $puht=$line->price/$line->quantity;
            $articles.=$line->quantity." x ".$article->ref_aci." (".number_format($puht,2)."€)<br>";
            
            $formarticles.=<<<HTML
                <input type="hidden" name="qte_art$i" value="$line->quantity">
                <input type="hidden" name="nom_art$i" value="$article->ref_aci"> 
                <input type="hidden" name="prix_art$i" value="$puht">
HTML;
            $i++;
        }
    
        $prixtotal=number_format($order->total_price,2)."€";
        if($order->shipping_price>0) $prixtotal.="<br>dont ".number_format($order->shipping_price,2)."€ de port";
    
        $facturation=$livraison="";
        $billing=$order->customer->billing_address;
        $billing_phone1=preg_replace("/\(.+\)|[\s]/","",$billing->phone);
        $billing_phone2=preg_replace("/\(.+\)|[\s]/","",$billing->phone_secondary);
        if($billing->company!="") $facturation.=$billing->company." ";
        $facturation.=$billing->firstname." ".$billing->lastname;
        if($billing->street_1!="") $facturation.="<br>".$billing->street_1;
        if($billing->city!="") $facturation.="<br>".$billing->zip_code." ".$billing->city;
        if($billing->country!="") $facturation.="<br>".$billing->country;
        if($billing->phone!="") $facturation.="<br>".$billing_phone1;
        if($billing->phone_secondary!="") $facturation.="<br>".$billing_phone2;
        
        $shipping=$order->customer->shipping_address;
        $shipping_phone1=preg_replace("/\(.+\)|[\s]/","",$shipping->phone);
        $shipping_phone2=preg_replace("/\(.+\)|[\s]/","",$shipping->phone_secondary);
        if($shipping->company!="") $livraison.=$shipping->company." ";
        $livraison.=$shipping->firstname." ".$shipping->lastname;
        if($shipping->street_1!="") $livraison.="<br>".$shipping->street_1;
        if($shipping->city!="") $livraison.="<br>".$shipping->zip_code." ".$shipping->city;
        if($shipping->country!="") $livraison.="<br>".$shipping->country;
        if($shipping->phone!="") $livraison.="<br>".$shipping_phone1;
        if($shipping->phone_secondary!="") $livraison.="<br>".$shipping_phone2;
        
        
        $form_cmd=<<<HTML
            <form action="index.php?create=1" method="post" id="formAuto">
            <input type="hidden" name="id_commande" value="$order->order_id">
            <input type="hidden" name="date_commande" value="$date_commande">
            <input type="hidden" name="market_place" value="Conforama">
            <input type="hidden" name="market_place_id" value="19">

            <input type="hidden" name="facturation_societe" value="$billing->company">
            <input type="hidden" name="facturation_prenom" value="$billing->firstname">
            <input type="hidden" name="facturation_nom" value="$billing->lastname">
            <input type="hidden" name="facturation_adresse" value="$billing->street_1">
            <input type="hidden" name="facturation_cp" value="$billing->zip_code">
            <input type="hidden" name="facturation_ville" value="$billing->city">
            <input type="hidden" name="facturation_pays" value="$billing->country">
            <input type="hidden" name="facturation_tel" value="$billing_phone1">

            <input type="hidden" name="livraison_societe" value="$shipping->company">
            <input type="hidden" name="livraison_prenom" value="$shipping->firstname">
            <input type="hidden" name="livraison_nom" value="$shipping->lastname">
            <input type="hidden" name="livraison_adresse" value="$shipping->street_1">
            <input type="hidden" name="livraison_cp" value="$shipping->zip_code">
            <input type="hidden" name="livraison_ville" value="$shipping->city">
            <input type="hidden" name="livraison_pays" value="$shipping->country">
            <input type="hidden" name="livraison_fixe" value="$shipping_phone1">
            <input type="hidden" name="livraison_mobile" value="$shipping_phone2">
            <input type="hidden" name="livraison_tel" value="$shipping_phone1">

            <input type="hidden" name="frais_de_port" value="$order->shipping_price">
            <input type="hidden" name="nbr_produit" value="$linescount">
            <input type="hidden" name="commission" value="$order->total_commission">
            $formarticles
            <input type="submit" value="Créer">
            </form>
HTML;
        
        $trColor = '';
        
        switch($order->order_state){
            case "STAGING":
            
            break;
            
            case "WAITING_ACCEPTANCE":
            $creation_cmd=!is_null($facture) ? "Commande à accepter sur Conforama" : $form_cmd;
            $trColor = '#ffcba0';
            break;
            
            case "WAITING_DEBIT":
            case "WAITING_DEBIT_PAYMENT":
            $creation_cmd=!is_null($facture) ? "Commande en attente de paiement sur Conforama" : $form_cmd;
            $trColor = '#ffcba0';
            break;
            
            case "SHIPPING":
            $creation_cmd=!is_null($facture) ? "Commande à expédier sur Conforama" : $form_cmd;
            $trColor = '#ffcba0';
            break;
            
            case "SHIPPED":
            $creation_cmd=!is_null($facture) ? $creation_cmd="Commande expédiée sur Conforama" : $form_cmd;
            $trColor = '#b4dbbc';
            break;
            
            case "RECEIVED":
            $creation_cmd=!is_null($facture) ? $creation_cmd="Commande reçue" : $form_cmd;
            $trColor = '#b4dbbc';
            break;
            
            case "CLOSED":
            $creation_cmd="Commande terminée";
            $trColor = '#b4dbbc';
            break;
            
            case "REFUSED":
            if(!is_null($facture)){
                if(!in_array($facture->valide,array(2,3))){
                    $creation_cmd="Commande à annuler";
                }else{
                    $creation_cmd="Commande refusée";
                }
            }else
                $creation_cmd="Commande refusée";
            $trColor = '#fcbebe';
            break;
            
            case "CANCELED":
            if(!is_null($facture)){
                if(!in_array($facture->valide,array(2,3))){
                    $creation_cmd="Commande à annuler";
                }else{
                    $creation_cmd="Commande annulée";
                }
            }else
                $creation_cmd="Commande annulée";
            $trColor = '#fcbebe';
            break;
        }
    
        print <<<HTML
        <tr class="row$alt" style="background-color:$trColor; border-color: black;">
            <td height="20" style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">
                $order->order_id
                $info_commande
            </td>
            <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">$date_commande</td>
            <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">$articles</td>
            <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">$prixtotal</td>
            <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">$facturation</td>
            <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">$livraison</td>
            <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">$order->order_state</td>
            <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">$creation_cmd</td>
        </tr>
HTML;
    }
    ?>
</table>
