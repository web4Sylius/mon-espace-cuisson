<?php

$mporder=new mp_order();

if(isset($_POST['billing_last_name'])){
     $mporder=new MP_Order();
     if($_POST['modiforder']=="")
         $mporder->id=$_POST['setorderid'];
     else
         $mporder->load($_POST['modiforder']);

     $mporder->order_date=mktime(0,0,0,$_POST['month'],$_POST['day'],$_POST['year']);
     $mporder->payment_method=0;
     $mporder->vendor=$admember->id;
     $mporder->market_place_id=$_POST['marketplace'];
     $mporder->billing_first_name=$_POST['billing_first_name'];
     $mporder->billing_last_name=$_POST['billing_last_name'];
     $mporder->billing_company=$_POST['billing_company'];
     $mporder->billing_address_1=$_POST['billing_address_1'];
     $mporder->billing_address_2=$_POST['billing_address_2'];
     $mporder->billing_zip=$_POST['billing_zip'];
     $mporder->billing_city=$_POST['billing_city'];
     $mporder->billing_country=$_POST['billing_country']; 
     $mporder->billing_phone=$_POST['billing_phone'];
     $mporder->billing_fax=$_POST['billing_fax'];
     $mporder->billing_email=$_POST['billing_mail'];
     $mporder->shipping_first_name=$_POST['shipping_first_name'];
     $mporder->shipping_last_name=$_POST['shipping_last_name'];
     $mporder->shipping_company=$_POST['shipping_company'];
     $mporder->shipping_address_1=$_POST['shipping_address_1'];
     $mporder->shipping_address_2=$_POST['shipping_address_2'];
     $mporder->shipping_zip=$_POST['shipping_zip'];
     $mporder->shipping_city=$_POST['shipping_city'];
     $mporder->shipping_country=$_POST['shipping_country']; 
     $mporder->shipping_phone=$_POST['shipping_phone'];
     $mporder->shipping_fax=$_POST['shipping_fax'];
     $mporder->shipping_details=$_POST['infotrspt'];
     $mporder->billing_date=$mporder->order_date;
     $mporder->vat_100=floatval(TVA_COEFF);

     if($_POST['modiforder']=="")
         $result=$mporder->create();
     else
         $result=$mporder->update();

     if($result) $_GET['step']=2;
}

if(isset($_POST['orderid']) && isset($_POST['prod'])){
     $mporder=new MP_Order();
     $mporder->load($_POST['orderid']);

     if($_POST['prod']!=""){
         for($i=1;$i<=$_POST['qtt'];$i++){
             $nlorder=new MP_Order_Line();
             $nlorder->order=$mporder->id;
             $nlorder->ref_tin=$_POST['prod'];
             $nlorder->product_name=$_POST['description'];
             $nlorder->price=($_POST['price']/TVA_COEFF);
             $nlorder->buy_price=$_POST['buyprice'];
             $nlorder->strikeout_price=($_POST['strikeout_price']/TVA_COEFF);
             $nlorder->barcode=$_POST['barcode'];
             $nlorder->create();
         }
         
         $i=0;
         while(isset($_POST['caract_'.$i])){
              $nlorder->updcaract($_POST['caract_'.$i],$_POST['caractname_'.$i]);
              $i++;
         }
     }

     $mporder->calc_items_price();
     $_GET['step']=2;

}

if(isset($_POST['shipprice']) && isset($_POST['shippingorder'])){
         $mporder=new MP_Order();
         $mporder->load($_POST['shippingorder']);
         $mporder->shipping_price=($_POST['shipprice']/TVA_COEFF);
         $mporder->update();
         $mporder->calc_items_price();
         $_GET['step']=2;
}

if(isset($_POST['commission']) && isset($_POST['commissionorder'])){
         $mporder=new MP_Order();
         $mporder->load($_POST['commissionorder']);
         $mporder->commission=$_POST['commission'];
         $mporder->update();
         $_GET['step']=2;                      
}

if(isset($_GET['order'])){

    $mporder=new MP_Order();
    $result=$mporder->load($_GET['order']);
    if($result==false){
        print "<p>Impossible de trouver la commande ".$_GET['order']."</p>";
        exit;
    }
    if(!isset($_GET['step'])) $_GET['step']=2;

    if(isset($_GET['state'])){
        $mporder->order_status=$_GET['state'];
        $mporder->comment.="\nCommande reçue le ".date("d/m/Y H:i");
        $mporder->update();
    }

    if(isset($_POST['comment'])){
         $mporder->comment=$_POST['comment'];
         $mporder->update();
    }

    if(isset($_GET['sav'])){
        if($_GET['sav']=="new" && $mporder->order_status==2) $mporder->order_status=4;
        if($_GET['sav']=="end" && $mporder->order_status==4) $mporder->order_status=2;
        $mporder->update();
    }

     if(isset($_GET['sendbill'])){
	$mail=new email();
	$mail->expediteur="contact@ma-cave-a-vin.fr";
	$mail->expname="Ma Cave à Vin";
	$mail->destinataire=$mporder->billing_email;
	$mail->destname=$mporder->billing_first_name." ".$mporder->billing_last_name;
	$mail->copymode="bcc";
	$mail->to=array("pr@ma-cave-a-vin.fr","ds@ma-cave-a-vin.fr");
	$mail->subject="Votre commande sur ".$mporder->market_place;
	$mail->body="<p>Bonjour ".ucfirst($mporder->billing_last_name)." ".ucfirst($mporder->billing_first_name).",</p>
	Nous vous informons que votre commande  (Ref. N° ".$mporder->id.") passée sur ".$mporder->market_place." est terminée et que votre colis vient d'être expédié ou le sera dans les plus brefs délais. Veuillez trouver ci-joint la facture correspondante.
	<p>A très bientôt,
	<br>L'Equipe Ma cave à Vin </p>";
	
	$_GET['join']=1;
	$mail->attachstring(include("../../facture_mp.php"),"Facture.pdf","application/pdf");
	$mail->sendmail();
    }

    if($mporder->order_status==2 || $mporder->order_status==3 || $mporder->order_status==4) $_GET['step']=3;
}

if(isset($_GET['del'])){
     $mporderline=new MP_Order_Line();
     $mporderline->id=$_GET['del'];
     if($mporderline->order_status==0) $mporderline->delete();
     $mporder->calc_items_price();
}


print "<table cellspacing=\"0\" width=\"800\" align=\"center\" style=\"background-color:white;\" border=\"0\">
<tr><td colspan=\"1\" height=\"25\"><h3>Ajouter/modifier une commande Market Place</h3></td><td align=\"right\"><a href=\"javascript:history.back()\">retour</a></td></tr>\n";

$step=1;
if(isset($_GET['step'])) $step=$_GET['step'];
switch($step){
    case 1:
    $mpo=new mp_order();
    $radiomp="";
    foreach($mpo->marketplaces as $mpnb=>$mpname)
          $radiomp.="<br><input type=\"radio\" name=\"marketplace\" value=\"".$mpnb."\"".($mporder->market_place_id==$mpnb?" checked":"")."> ".$mpname." ($mpnb)\n";

    $d=getDate();
    if($mporder->order_date!=0) $d=getdate($mporder->order_date);

    $selectdate="<select name=\"day\">\n";
    for($i=1;$i<=31;$i++)
        $selectdate.="<option value=\"".$i."\"".($d['mday']==$i?" selected":"").">".$i."</option>\n";
    $selectdate.="</select>\n/<select name=\"month\">\n";
    for($i=1;$i<=12;$i++)
        $selectdate.="<option value=\"".$i."\"".($d['mon']==$i?" selected":"").">".$i."</option>\n";
    $selectdate.="</select>\n/<select name=\"year\">\n";
    $year=intval($d['year']);
    for($i=$year;$i<=$year;$i++)
        $selectdate.="<option value=\"".$i."\">".$i."<option>\n";
    $selectdate.="</select>";

    $optbcountries=$optscountries="";
    $requete=mysql_query("SELECT paysid,libelle FROM pays");
    while($pays=mysql_fetch_array($requete)){
	$optbcountries.="<option value=\"$pays[paysid]\"".($mporder->billing_country==$pays[paysid]?" selected":"").">$pays[libelle]</option>";
	$optscountries.="<option value=\"$pays[paysid]\"".($mporder->shipping_country==$pays[paysid]?" selected":"").">$pays[libelle]</option>";
    }

          print "<form action=\"index.php?create=1\" method=\"post\">
          <input type=\"hidden\" name=\"modiforder\" value=\"".$mporder->id."\">
          <tr><td>Market Place</td><td>".$radiomp."</td></tr>
          <tr><td>Numéro de commande de la Market Place</td><td><input type=\"text\" name=\"setorderid\" value=\"".$mporder->id."\"></td></tr>
          <tr><td>Date de la commande</td><td>".$selectdate."</td></tr>
          <tr><td>&nbsp;</td></tr>\n";

          print "<tr style=\"background-color:#cccccc;\"><td colspan=\"2\" height=\"25\">Adresse de facturation</td></tr>
          <tr style=\"background-color:#cccccc;\"><td>Société</td><td><input type=\"text\" name=\"billing_company\" size=\"40\" value=\"".$mporder->billing_company."\"></td></tr>
          <tr style=\"background-color:#cccccc;\"><td>Nom</td><td><input type=\"text\" name=\"billing_last_name\" size=\"40\" value=\"".$mporder->billing_last_name."\"></td></tr>
          <tr style=\"background-color:#cccccc;\"><td>Prénom</td><td><input type=\"text\" name=\"billing_first_name\" size=\"40\" value=\"".$mporder->billing_first_name."\"></td></tr>
          <tr style=\"background-color:#cccccc;\"><td>Adresse</td><td><input type=\"text\" name=\"billing_address_1\" size=\"40\" value=\"".$mporder->billing_address_1."\"><br><input type=\"text\" name=\"billing_address_2\" size=\"40\" value=\"".$mporder->billing_address_2."\"></td></tr>
          <tr style=\"background-color:#cccccc;\"><td>Code postal</td><td><input type=\"text\" name=\"billing_zip\" size=\"10\" value=\"".$mporder->billing_zip."\"></td></tr>
          <tr style=\"background-color:#cccccc;\"><td>Ville</td><td><input type=\"text\" name=\"billing_city\" size=\"40\" value=\"".$mporder->billing_city."\"></td></tr>
          <tr style=\"background-color:#cccccc;\"><td>Pays</td><td><select name=\"billing_country\">$optbcountries</select></td></tr>
	  <tr style=\"background-color:#cccccc;\"><td>Téléphone</td><td><input type=\"text\" name=\"billing_phone\" size=\"40\" value=\"".$mporder->billing_phone."\"></td></tr>
          <tr style=\"background-color:#cccccc;\"><td>Fax</td><td><input type=\"text\" name=\"billing_fax\" size=\"40\" value=\"".$mporder->billing_fax."\"></td></tr>
          <tr style=\"background-color:#cccccc;\"><td>E mail</td><td><input type=\"text\" name=\"billing_mail\" size=\"40\" value=\"".$mporder->billing_email."\"></td></tr>
          <tr><td>&nbsp;</td></tr>
          <tr style=\"background-color:#cccccc;\"><td height=\"25\">Adresse de Livraison</td><td><input type=\"checkbox\" name=\"copy\" onclick=\"copybilling();\"> Identique à l'adresse de facturation</td></tr>
          <tr style=\"background-color:#cccccc;\"><td>Société</td><td><input type=\"text\" name=\"shipping_company\" size=\"40\" value=\"".$mporder->shipping_company."\"></td></tr>
          <tr style=\"background-color:#cccccc;\"><td>Nom</td><td><input type=\"text\" name=\"shipping_last_name\" size=\"40\" value=\"".$mporder->shipping_last_name."\"></td></tr>
          <tr style=\"background-color:#cccccc;\"><td>Prénom</td><td><input type=\"text\" name=\"shipping_first_name\" size=\"40\" value=\"".$mporder->shipping_first_name."\"></td></tr>
          <tr style=\"background-color:#cccccc;\"><td>Adresse</td><td><input type=\"text\" name=\"shipping_address_1\" size=\"40\" value=\"".$mporder->shipping_address_1."\"><br><input type=\"text\" name=\"shipping_address_2\" size=\"40\" value=\"".$mporder->shipping_address_2."\"></td></tr>
          <tr style=\"background-color:#cccccc;\"><td>Code postal</td><td><input type=\"text\" name=\"shipping_zip\" size=\"10\" value=\"".$mporder->shipping_zip."\"></td></tr>
          <tr style=\"background-color:#cccccc;\"><td>Ville</td><td><input type=\"text\" name=\"shipping_city\" size=\"40\" value=\"".$mporder->shipping_city."\"></td></tr>
	  <tr style=\"background-color:#cccccc;\"><td>Pays</td><td><select name=\"shipping_country\">$optscountries</select></td></tr>
	  <tr style=\"background-color:#cccccc;\"><td>Téléphone</td><td><input type=\"text\" name=\"shipping_phone\" size=\"40\" value=\"".$mporder->shipping_phone."\"></td></tr>
          <tr style=\"background-color:#cccccc;\"><td>Fax</td><td><input type=\"text\" name=\"shipping_fax\" size=\"40\" value=\"".$mporder->shipping_fax."\"></td></tr>
          <tr style=\"background-color:#cccccc;\"><td>Infos transporteurs</td><td><input type=\"text\" name=\"infotrspt\" size=\"40\" value=\"".$mporder->shipping_details."\"></td></tr>
          <tr><td>&nbsp;</td></tr>
          <tr><td><input type=\"submit\" value=\"Continuer\"></td></tr>
          </form>";
          
    break;

    case 2:
	  include("adv2.php");
          $listprod=$mporder->list_lines();

	  $paysfacture=mysql_fetch_array(mysql_query("SELECT libelle FROM pays WHERE paysid=".$mporder->billing_country));
	  $payslivraison=mysql_fetch_array(mysql_query("SELECT libelle FROM pays WHERE paysid=".$mporder->shipping_country));
          print "<tr><td colspan=\"2\" style=\"font-weight:bold; color:#808080; font-size:14px;\">n°".$mporder->billing_number."-".$mporder->market_place_id."</td></tr>\n";
          print "<tr>
                <td><b>Facturation</b><br>".$mporder->billing_company."<br>".$mporder->billing_first_name." ".$mporder->billing_last_name."<br>".$mporder->billing_address_1."<br>".$mporder->billing_zip." ".$mporder->billing_city."<br>".$paysfacture['libelle']."<br>".$mporder->billing_phone."</td>
                <td><b>Livraison</b><br>".$mporder->shipping_company."<br>".$mporder->shipping_first_name." ".$mporder->shipping_last_name."<br>".$mporder->shipping_address_1."<br>".$mporder->shipping_zip." ".$mporder->shipping_city."<br>".$payslivraison['libelle']."<br>".$mporder->shipping_phone."</td>
          </tr>
          <tr><td>Info trspt : ".$mporder->shipping_details."</td></tr>
          <tr><td><a href=\"index.php?order=".$mporder->id."&step=1\">Modifier les infos de facturation et livraison</a></td></tr>
          <tr><td colspan=\"2\"><form action=\"index.php?order=".$mporder->id."\" method=\"post\">
          <br><span style=\"font-size:10px\">Suivi client</span>
          <br><textarea name=\"comment\" cols=\"80\" rows=\"4\">".$mporder->comment."</textarea>
          <br><input type=\"submit\" value=\"Modifier\">
          </form>
          </td></tr>
          <tr><td colspan=\"2\" height=\"40\">Rechercher un produit <input type=\"text\" name=\"src\" id=\"newsrc\"><input type=\"button\" value=\"OK\" onclick=\"srcprod()\"></td></tr>
          <tr><td colspan=\"2\" id=\"srcresults\"></td></tr>
          <tr><td>&nbsp;</td></tr>
          <tr><td colspan=\"2\" id=\"addprod\" style=\"height:40px; visibility:hidden; position:absolute;background-color:#dddddd;\">
          <form name=\"addline\" action=\"index.php\" method=\"post\">
          <input type=\"hidden\" name=\"orderid\" value=\"".$mporder->id."\">
          <input type=\"text\" name=\"prod\" value=\"\" readonly><input type=\"hidden\" name=\"buyprice\" value=\"\"> &nbsp;Quantité <input type=\"text\" name=\"qtt\" size=\"4\" value=\"1\"> &nbsp;Prix tarif unit. T.T.C <input type=\"text\" name=\"strikeout_price\" size=\"6\" value=\"\"> &nbsp;Prix vente unit. T.T.C <input type=\"text\" name=\"price\" size=\"6\" value=\"\"><input type=\"hidden\" name=\"description\" value=\"\"><input type=\"hidden\" name=\"barcode\" value=\"\">
          <div id=\"divopts\"></div>
          <input type=\"button\" value=\"Ajouter à la commande\" onclick=\"submitline()\">
          </form>\n
          </td></tr>\n";
          
          $buyprice=$mporder->getbuyprice();

          print "<tr><td colspan=\"2\" style=\"font-weight:bold;\">Articles de la commande ".$mporder->market_place." n°".$mporder->id."</td></tr>
          <tr><td>Facture n°".$mporder->billing_number."</td></tr>
          <tr><td>Etat : ".$mporder->order_state.($mporder->order_status==1?" &nbsp;&nbsp;&nbsp;<a href=\"javascript:delivered_state();\">Commande envoyée</a>":"")."</td></tr>
          <tr><td>Total achat : ".moneyformat($buyprice)." H.T. => ".moneyformat($buyprice*TVA_COEFF)." T.T.C.</td></tr>\n";
          print "<tr><td colspan=\"2\"><table cellspacing=\"0\" cellpadding=\"5\" class=\"plist\">
          <tr><td class=\"titlelist\">Référence</td><td class=\"titlelist\">Description</td><td class=\"titlelist\">Quantité</td><td class=\"titlelist\">Prix Unit.</td><td class=\"titlelist\">Total</td></tr>\n";
          if($listprod!=false){
          foreach($listprod as $prod){
                  print "<tr><td>".$prod->ref_tin."</td><td>".$prod->product_name."</td><td>".$prod->number."</td><td>".round($prod->price*TVA_COEFF,2)."</td><td>".round($prod->number*$prod->price*TVA_COEFF,2)."</td><td><a href=\"index.php?order=".$mporder->id."&del=".$prod->id."\"><img src=\"../../images/remove.gif\" alt=\"Supprimer\" border=\"0\"></a></td></tr>\n";
          }
          }
          print "</table></td></tr>\n";

          $totalht=$mporder->shipping_price+$mporder->items_price;
          print "<tr><td colspan=\"2\" style=\"padding-top:10px;\">
          <form action=\"index.php\" method=\"post\">
          <input type=\"hidden\" name=\"shippingorder\" value=\"".$mporder->id."\">
          <table cellspacing=\"0\" cellpadding=\"4\" class=\"plist\">
          <tr><td>Port</td><td><input type=\"text\" name=\"shipprice\" id=\"shipprice\" size=\"5\" value=\"".number_format(($mporder->shipping_price*TVA_COEFF),2,",","")."\"> <input type=\"submit\" value=\"OK\"></td></tr>
          <tr><td>Total H.T.</td><td>".moneyformat($totalht)."</td></tr>
          <tr><td>T.V.A. (".(TVA_COEFF*100-100)." %)</td><td>".moneyformat($totalht*(TVA_COEFF-1))."</td></tr>
          <tr><td>Total T.T.C.</td><td>".moneyformat($mporder->totalttc())."</td></tr>
          </table>
          </form>
          <form action=\"index.php\" method=\"post\">
          <input type=\"hidden\" name=\"commissionorder\" value=\"".$mporder->id."\">
          <table cellspacing=\"0\" cellpadding=\"0\" class=\"plist\">
          <tr><td align=\"center\">Coût Market Place</td></tr>
          <tr><td align=\"center\"><input type=\"text\" name=\"commission\" value=\"".$mporder->commission."\"> <input type=\"submit\" value=\"OK\"></td></tr>
          </table>
          </form>
          </td></tr>\n";

	  print "<tr><td colspan=\"2\" align=\"center\">";
	  include("adv.php");
	  print "</td></tr>";

          print "<tr><td><a href=\"../../facture_mp.php?facturid=".$mporder->id."&cde=1\" target=\"_blank\"><img src=\"images/pdf.gif\" border=\"0\" align=\"absmiddle\"> Bon de préparation</a> &nbsp; &nbsp; &nbsp; <a href=\"../../facture_mp.php?facturid=".$mporder->id."\" target=\"_blank\"><img src=\"images/pdf.gif\" border=\"0\" align=\"absmiddle\"> Facture</a> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a href=\"index.php?order=".$mporder->id."&state=2\">Commande livrée</a> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a href=\"javascript:if(confirm('Etes vous sur de vouloir annuler la commande ".$mporder->id." ?')) document.location='index.php?order=".$mporder->id."&state=3'\" style=\"color:red\">Annuler la commande</a> &nbsp; &nbsp; &nbsp; <a href=\"javascript:if(confirm('Etes vous sur de vouloir supprimer définitivement la commande ".$mporder->id." ?')) document.location='index.php?del=".$mporder->id."\" style=\"color:red\">Supprimer la commande</a></td></tr>\n";
    break;

    case 3:
          include("adv2.php");
	  $listprod=$mporder->list_lines();
          $buyprice=$mporder->getbuyprice();

          print "<tr><td colspan=\"2\" style=\"font-weight:bold; color:#808080; font-size:14px;\">n°".$mporder->billing_number."-".$mporder->market_place_id."</td></tr>\n";
          print "<tr>
                 <td><b>Facturation</b><br>".$mporder->billing_company."<br>".$mporder->billing_first_name." ".$mporder->billing_last_name."<br>".$mporder->billing_address_1."<br>".$mporder->billing_zip." ".$mporder->billing_city."<br>".$mporder->billing_phone."</td>
                <td><b>Livraison</b><br>".$mporder->shipping_company."<br>".$mporder->shipping_first_name." ".$mporder->shipping_last_name."<br>".$mporder->shipping_address_1."<br>".$mporder->shipping_zip." ".$mporder->shipping_city."<br>".$mporder->shipping_phone."</td>
          </tr>
          <tr><td colspan=\"2\"><form action=\"index.php?order=".$mporder->id."\" method=\"post\">
          <span style=\"font-size:10px\">Suivi client</span>
          <br><textarea name=\"comment\" cols=\"80\" rows=\"4\">".$mporder->comment."</textarea>
          <br><input type=\"submit\" value=\"Modifier\">
          </form>
          </td></tr>
          <tr><td>Info trspt : ".$mporder->shipping_details."</td></tr>
          <tr><td colspan=\"2\">Articles de la commande ".$mporder->market_place." n°".$mporder->id."</td></tr>
          <tr><td>Etat : ".$mporder->order_state."</td></tr>
          <tr><td>Total achat : ".moneyformat($buyprice)." H.T. => ".moneyformat($buyprice*TVA_COEFF)." T.T.C.</td></tr>\n";
          print "<tr><td colspan=\"2\"><table cellspacing=\"0\" cellpadding=\"5\" class=\"plist\">
          <tr><td class=\"titlelist\">Référence</td><td class=\"titlelist\">Description</td><td class=\"titlelist\">Quantité</td><td class=\"titlelist\">Prix Unit.</td><td class=\"titlelist\">Total</td></tr>\n";
          if($listprod!=false){
          foreach($listprod as $prod){
                  print "<tr><td>".$prod->ref_tin."</td><td>".$prod->product_name."</td><td>".$prod->number."</td><td>".$prod->price."</td><td>".round($prod->number*$prod->price,2)."</td></td></tr>\n";
          }
          }
          print "</table></td></tr>\n";

          $totalht=$mporder->shipping_price+$mporder->items_price;
          print "<tr><td colspan=\"2\" style=\"padding-top:10px;\">
          <table cellspacing=\"0\" cellpadding=\"4\" class=\"plist\">
          <tr><td>Port</td><td>".moneyformat($mporder->shipping_price)."</td></tr>
          <tr><td>Total H.T.</td><td>".moneyformat($totalht)."</td></tr>
          <tr><td>T.V.A. (".(TVA_COEFF*100-100)." %)</td><td>".moneyformat($totalht*(TVA_COEFF-1))."</td></tr>
          <tr><td>Total T.T.C.</td><td>".moneyformat($mporder->totalttc())."</td></tr>
          </table>
          <table cellspacing=\"0\" cellpadding=\"0\" class=\"plist\">
          <tr><td align=\"center\">Coût Market Place</td></tr>
          <tr><td align=\"center\">".moneyformat($mporder->commission)."</td></tr>
          </table>
          </td></tr>\n";

          if($mporder->order_status==2 || $mporder->order_status==4) print "<tr><td><a href=\"../../facture_mp.php?facturid=".$mporder->id."\" target=\"_blank\"><img src=\"images/pdf.gif\" border=\"0\" align=\"absmiddle\"> Facture</a> &nbsp; <a href=\"#\" onclick=\"javascript:if(confirm('Envoyer la facture par email au client ?')) document.location='index.php?order=".$mporder->id."&sendbill=1';\"><img src=\"images/pdf.gif\" border=\"0\" align=\"absmiddle\">Envoyer la facture</a></td></tr>\n";
          if($mporder->order_status==2) print "<tr><td><a href=\"index.php?order=".$mporder->id."&sav=new\"><img src=\"../../images/sav.jpg\" border=\"0\" align=\"absmiddle\"> Mettre la commande en S.A.V. </a></td></tr>\n";
          if($mporder->order_status==4) print "<tr><td><a href=\"index.php?order=".$mporder->id."&sav=end\"><img src=\"../../images/sav.jpg\" border=\"0\" align=\"absmiddle\"> Fin du S.A.V. </a></td></tr>\n";

	  print "<tr><td colspan=\"2\" align=\"center\">";
	  include("adv.php");
	  print "</td></tr>";
    break;
}


?>