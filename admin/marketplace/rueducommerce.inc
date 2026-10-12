<?php
require_once(BACK_PATH . '/class/marketplaces/rueducommerce.class.php');
require_once(SITE_PATH . '/class/facture.class.php');

$minDate = null;
$maxDate=null;
$titre = 'Commande(s) des 30 derniers jours';
//si une date a été selectionnée dans le choix de date du ==> au
if(isset($_GET['duDate']) && isset($_GET['auDate'])){
  $sp = explode("/",$_GET['duDate']);
  $du1 = mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
  $du = date('Y-m-d',$du1);
  $sp = explode("/",$_GET['auDate']);
  $au1 = mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
  $au = date('Y-m-d',$au1);
  if($du1 == $au1){ // si dans le choix des date on choisis le meme jour du = au
   $du =  date('Y-m-d',$du1);
   $au =  date('Y-m-d',$au1+86400); 
  }
  $minDate = $du ;
  $maxDate = $au;
  $titre = 'Commande(s) du '.date('Y-m-d',$du1).' au '.date('Y-m-d',$au1) ;
}
//si une option 30j ou 60j a été cochée
if(isset($_GET['choixDate'])){
  if($_GET['choixDate'] == '30j'){
    $minDate = date('Y-m-d',mktime(0,0,0,date('n')-1,date('j'),date('Y')));
    $titre = 'Commande(s) des 30 derniers jours';
  }else{
    $minDate = date('Y-m-d',mktime(0,0,0,date('n')-2,date('j'),date('Y')));
    $maxDate = date('Y-m-d',mktime(0,0,0,date('n')-1,date('j'),date('Y')));
    $titre = 'Commande(s) des 30 au 60 derniers jours';
  }
}

$rdc = new rueducommerce($DB_site2,$minDate,$maxDate);
$rdc->getOrders();

//Ancienne version, conservée pour la postérité...
//require_once(BACK_PATH . '/marketplace/rueducommerce_bis.inc');

$tr = array();

foreach($rdc->orders as $xml)
{
    foreach($xml as $order)
    {
        $facturation_tel = $livraison_tel = "";

        foreach($order->utilisateur as $row)
        {
            if($row["type"] == "facturation")
            {
                $nom_facturation        = $row->nom->__tostring();
                $prenom_facturation     = $row->prenom->__tostring();
                $telhome_facturation    = $row->telhome->__tostring();
                $teloffice_facturation  = $row->teloffice->__tostring();
                $email_facturation      = $row->email->__tostring();
                $rue1_facturation       = $row->adresse->rue1->__tostring();
                $rue2_facturation       = $row->adresse->rue2->__tostring();
                $cpostal_facturation    = $row->adresse->cpostal->__tostring();
                $ville_facturation      = $row->adresse->ville->__tostring();
                $pays_facturation       = $row->adresse->pays->__tostring();
                $commentaire_faturation = $row->commentairefacturation->__tostring();
                $societe_facturation    = $row->societe->__tostring();

                $facturation_tel = $telhome_facturation;

                if($facturation_tel == "" && $teloffice_facturation != "") $facturation_tel = $teloffice_facturation;

            }

            if($row["type"] == "livraison")
            {
                $nom_livraison          = $row->nom->__tostring();
                $prenom_livraison       = $row->prenom->__tostring();
                $telhome_livraison      = $row->telhome->__tostring();
                $teloffice_livraison    = $row->teloffice->__tostring();
                $email_livraison        = $row->email->__tostring();
                $rue1_livraison         = $row->adresse->rue1->__tostring();
                $rue2_livraison         = $row->adresse->rue2->__tostring();
                $cpostal_livraison      = $row->adresse->cpostal->__tostring();
                $ville_livraison        = $row->adresse->ville->__tostring();
                $pays_livraison         = $row->adresse->pays->__tostring();
                $commentaire_livraison  = $row->commentairelivraison->__tostring();
                $societe_livraison      = $row->societe->__tostring();

                $livraison_tel = $telhome_livraison;

                if($livraison_tel == "" && $teloffice_livraison != "") $livraison_tel = $teloffice_livraison;
            }   
        }

        $prix          = 0;
        $td_articles   = "";
        $inputarticles = "";

        foreach($order->infocommande as $row)
        {
            $type                       = $row->status->__tostring();
            $site_commande              = $row->sitecommande;
            $refid_commande             = $row->refid->__tostring();
            $status_commande            = $row->status->__tostring();
            $montant_commande           = $row->montant->__tostring();
            $devise_commande            = $row->montant['devise']->__tostring();
            $date_commande              = $row->date->__tostring();
            $dtcommande                 = date_create_from_format("d/m/Y H:i:s",$date_commande);
            $nom_transport_commande     = $row->transport->nom->__tostring();
            $montant_transport_commande = $row->transport->montant->__tostring();
            $devise_transport_commande  = $row->transport->montant['devise']->__tostring();
            $nbproduits_commande        = $row->list['nbproduit']->__tostring();

			$i = 0;
            foreach($row->list as $line)
            {
                $produit_commande          = $line->produit->__tostring();
                $produit_commande_ref      = $line->produit['ref']->__tostring();
                $nb_produit                = $line->produit['nb']->__tostring();
                $produit_modid             = $line->produit['modid']->__tostring();
                $produit_delivery_rule     = $line->produit['delivery_rule']->__tostring();
                $produit_status            = $line->produit['status']->__tostring();
                $produit_merchantProductId = $line->produit['merchantProductId']->__tostring();
                $produit_merchantOfferId   = $line->produit['merchantOfferId']->__tostring();
                $produit_gender            = $line->produit['gender']->__tostring();
                $produit_weight            = $line->produit['weight'].'';
                $produit_ean13             = $line->produit['ean13'].'';
                $produit_prix              = $line->produit['price'].'';
                
                $prix += floatval($nb_produit) * floatval($produit_prix);

                $article = new article($DB_site2);
                $article->load($produit_merchantOfferId);
                $artcode = is_null($article->artcode) ? "Ref inconnue" : $article->artcode;
                $td_articles .= $nb_produit . " x " . $artcode . " (" . number_format($produit_prix,2) . "€)<br>";

                $inputarticles .= ""
                    . "<input type=\"hidden\" name=\"qte_art" . $i . "\" value=\"" . $nb_produit . "\">"
                    . "<input type=\"hidden\" name=\"nom_art" . $i . "\" value=\"" . $article->artcode . "\">"
                    . "<input type=\"hidden\" name=\"prix_art" . $i . "\" value=\"" . $produit_prix . "\">";
					$i++;
            }
        }

        $prix_tot = $montant_commande;
        $td_total = number_format($prix_tot,2) . "€<br>dont " . number_format($montant_transport_commande,2) . "€ de port";

        $totalmcav     = "";
        $lien_commande = "";

        $facture = new facture($DB_site2);

        if($facture->from_mp_facturid($refid_commande))
        {
            $lien_commande = "<a href=\"" . BACK_URL . "/facture.php?action=editer&facturid=" . $facture->facturid . "\" target=\"_blank\">" . $facture->facturid . "</a>";

            if(!in_array($facture->valide,array(2,3)))
            {
                if(round($prix_tot,2) == round($facture->totalttc,2))
                {
                    $totalmcav = $facture->totalttc . "€";
                }
                else
                {
                    $totalmcav = "<span style=\"color:red;\">" . $facture->totalttc . "€ Attention les prix ne correspondent pas</span>";
                }
            }
        }

        $creer = ""
        . "<form action=\"index.php?create=1\" method=\"post\" id=\"formAuto\">"
            . "<input type=\"hidden\" name=\"id_commande\" value=\"" . $refid_commande . "\">"
            . "<input type=\"hidden\" name=\"date_commande\" value=\"" . $date_commande . "\">"
            . "<input type=\"hidden\" name=\"market_place\" value=\"rueducommerce\">"
            . "<input type=\"hidden\" name=\"market_place_id\" value=\"07\">"

            . "<input type=\"hidden\" name=\"facturation_societe\" value=\"" . $societe_facturation . "\">"
            . "<input type=\"hidden\" name=\"facturation_prenom\" value=\"" . $prenom_facturation . "\">"
            . "<input type=\"hidden\" name=\"facturation_nom\" value=\"" . $nom_facturation . "\">"
            . "<input type=\"hidden\" name=\"facturation_adresse\" value=\"" . $rue1_facturation . "\">"
            . "<input type=\"hidden\" name=\"facturation_cp\" value=\"" . $cpostal_facturation . "\">"
            . "<input type=\"hidden\" name=\"facturation_ville\" value=\"" . $ville_facturation . "\">"
            . "<input type=\"hidden\" name=\"facturation_pays\" value=\"" . $pays_facturation . "\">"
            . "<input type=\"hidden\" name=\"facturation_tel\" value=\"" . $facturation_tel . "\">"

            . "<input type=\"hidden\" name=\"livraison_societe\" value=\"" . $societe_livraison . "\">"
            . "<input type=\"hidden\" name=\"livraison_prenom\" value=\"" . $prenom_livraison . "\">"
            . "<input type=\"hidden\" name=\"livraison_nom\" value=\"" . $nom_livraison . "\">"
            . "<input type=\"hidden\" name=\"livraison_adresse\" value=\"" . $rue1_livraison . "\">"
            . "<input type=\"hidden\" name=\"livraison_cp\" value=\"" . $cpostal_livraison . "\">"
            . "<input type=\"hidden\" name=\"livraison_ville\" value=\"" . $ville_livraison . "\">"
            . "<input type=\"hidden\" name=\"livraison_pays\" value=\"" . $pays_livraison . "\">"
            . "<input type=\"hidden\" name=\"livraison_tel\" value=\"" . $livraison_tel . "\">"

            . "<input type=\"hidden\" name=\"frais_de_port\" value=\"" . $montant_transport_commande . "\">"
            . "<input type=\"hidden\" name=\"nbr_produit\" value=\"" . $nbproduits_commande . "\">"
            . "<input type=\"hidden\" name=\"commission\" value=\"0\">"
            . $inputarticles
            . "<input type=\"submit\" value=\"Créer\">"
        . "</form>";

        switch($type)
        {
            case "new":

                $trColor = '#76a9fc';

                if(is_null($facture->facturid))
                {
                    $td_action = $creer;
                }
                else
                {
                    $td_action = "Commande à expédier sur RDC";
                }
                
            break;

            case "processing":

                $trColor = '#ffcba0';

                if(is_null($facture->facturid))
                {
                    $td_action = $creer;
                }
                else
                {
                    $td_action="Commande à expédier sur RDC";
                }
                    

            break;

            case "cancelled":

                $trColor = '#fcbebe';

                if(!is_null($facture->facturid))
                {
                    if(!in_array($facture->valide,array(2,3)))
                    {
                        $td_action = "Commande à annuler";
                    }
                    else
                    {
                        $td_action = "Commande annulée";
                    }
                }

            break;

            case "restocking:":
                
                $trColor = '#ffcba0';
            
            break;

            case "shipped":
                
                $trColor = '#b4dbbc';

                if(is_null($facture->facturid))
                {
                    $td_action = $creer;
                }
                else
                {
                    $td_action = "Commande expédiée sur RDC";
                }

            break;
        }

        $tr[$dtcommande->getTimestamp() . $refid_commande] = ""
        . "<tr style=\"background-color: " . $trColor . ";>; border-color: black;\">"
            . "<td height=\"20\" style=\"padding:4px; text-align: center;border-bottom: 1px solid #990000;\">"
                . $refid_commande
                . "<br>" . $lien_commande
                . "<br>" . $totalmcav
            . "</td>"
            . "<td class=\"dateRdc\" style=\"padding:4px; text-align: center;border-bottom: 1px solid #990000;\">" . $date_commande . "</td>"
            . "<td style=\"padding:4px; text-align: center;border-bottom: 1px solid #990000;\">"
                . $td_articles
            . "</td>"
            . "<td style=\"padding:4px; text-align: center;border-bottom: 1px solid #990000;\">"
                . $td_total
            . "</td>"
            . "<td style=\"padding:4px; text-align: center;border-bottom: 1px solid #990000;\">"
                . $societe_facturation
                . "<br>" . $prenom_facturation . " " . $nom_facturation
                . "<br>" . $rue1_facturation
                . "<br>" . $cpostal_facturation . " " . $ville_facturation
                . "<br>" . $pays_facturation
                . "<br>" . $facturation_tel
            . "</td>"
            . "<td style=\"padding:4px; text-align: center;border-bottom: 1px solid #990000;\">"
                . $societe_livraison
                . "<br>" . $prenom_livraison . " " . $nom_facturatio
                . "<br>" . $rue1_livraison
                . "<br>" . $cpostal_livraison . " " . $ville_facturation
                . "<br>" . $pays_livraison
                . "<br>" . $livraison_tel
            . "</td>"
                . "<td style=\"padding:4px; text-align: center;border-bottom: 1px solid #990000;\">" . $type . "</td>"
                . "<td style=\"padding:4px; text-align: center;border-bottom: 1px solid #990000;\">"
                . $td_action
            . "</td>"
        . "</tr>";

    }
}

krsort($tr);
$trstr = implode("",$tr);
?>
<p style="text-align: center; margin: 10px 0; padding: 0; font-size: 13px; font-weight: bold;"><?php echo $titre ; ?></p>
<table onload = "sortTable()" cellspacing="0" width="800" align="center" style="margin-top:10px;" class="list" id="tabRdc">
    <thead>
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
    </thead>
    <tbody class="rdc">
        <?php echo $trstr; ?>
    </tbody>
</table>
