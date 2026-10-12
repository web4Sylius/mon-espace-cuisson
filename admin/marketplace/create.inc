<?php
//PAGE de création de commande
$mporder = new mp_order();
if(isset($_POST['billing_last_name']))
{ //n°1
  $mporder = new MP_Order(0,$DB_site2);
 

  if($_POST['modiforder']=="")
    $mporder->mp_facturid=trim($_POST['setorderid']);
  else
    $mporder->load($_POST['modiforder']);

  $sp=preg_split('/[- :\/h]/',$_POST['orderdate']);
  $mporder->order_date=mktime($sp[3],$sp[4],0,$sp[1],$sp[0],$sp[2]);
  $qp=preg_split('/[- :\/h]/',$_POST['deliverdate']);
  $mporder->billing_deliver=mktime($qp[3],$qp[4],0,$qp[1],$qp[0],$qp[2]);

  if($_POST['userid'] != "" | $_POST['userid'] != 0)
  {
    $mporder->userid2 = $_POST['userid'];
  }
  $mporder->payment_method=1;
  $mporder->market_place_id=$_POST['marketplace'];
  $mporder->billing_civi=$_POST['billing_civi'];
  $mporder->billing_first_name=$_POST['billing_first_name'];
  $mporder->billing_last_name=$_POST['billing_last_name'];
  $mporder->billing_company=$_POST['billing_company'];
  $mporder->billing_address=$_POST['billing_address'];
  $mporder->billing_zip=$_POST['billing_zip'];
  $mporder->billing_city=$_POST['billing_city'];
  $mporder->billing_country=$_POST['billing_country']; 
  $mporder->billing_phone=$_POST['billing_phone'];
  $mporder->billing_email=$_POST['billing_mail'];

  $mporder->shipping_civi=$_POST['shipping_civi'];
  $mporder->shipping_first_name=$_POST['shipping_first_name'];
  $mporder->shipping_last_name=$_POST['shipping_last_name'];
  $mporder->shipping_company=$_POST['shipping_company'];
  $mporder->shipping_address=$_POST['shipping_address'];
  $mporder->shipping_zip=$_POST['shipping_zip'];
  $mporder->shipping_city=$_POST['shipping_city'];
  $mporder->shipping_country=$_POST['shipping_country']; 
  $mporder->shipping_phone=$_POST['shipping_phone'];
  $mporder->shipping_details=$_POST['infotrspt'];
  $mporder->billing_date=$mporder->order_date;
  $mporder->vat_100=floatval(TVA_COEFF-1)*100;

  if(isset($_POST['infoimportante'])) $mporder->info_importante=$_POST['infoimportante'];


  $mporder->interetbca=$_POST['interetbca'];
  $mporder->nbr_produit=$_POST['nbr_produit'];
  $mporder->shipping_price=$_POST['fdeport'];
  $mporder->commission=$_POST['commission'] / 1.2; //la commisssion doit etre donne hors taxe d'ou le /1.2
  for ($i=0;$i<$mporder->nbr_produit;$i++)
  {
    $nom = "nom".$i;
    $mporder->nom[$i] = $_POST[$nom];
    $qte = "qte".$i;
    $mporder->qte[$i] = $_POST[$qte];
    $prix = "prix".$i;
    $mporder->prix[$i] = $_POST[$prix];
  }

  if($_POST['modiforder']=="" || $_POST['modiforder']=="0"){
    $result=$mporder->create();
    $txt="Création commande ".$mporder->id." ".$mporder->marketplaces[$mporder->market_place_id]." ".$mporder->mp_facturid." du ".$_POST['orderdate']."<br>
    Facturation :<br>".$mporder->billing_first_name." ".$mporder->billing_last_name."<br>".$mporder->billing_address."<br>".$mporder->billing_zip." ".$mporder->billing_city."<br>".$mporder->billing_phone."<br>".$mporder->billing_email."
    <br><br>Livraison :<br>".$mporder->shipping_first_name." ".$mporder->shipping_last_name."<br>".$mporder->shipping_address."<br>".$mporder->shipping_zip." ".$mporder->shipping_city."<br>".$mporder->shipping_phone."<br>".$mporder->shipping_details;
    for ($i=0;$i<$mporder->nbr_produit;$i++)
    {
    $sql = "SELECT * FROM article WHERE artcode ='".$mporder->nom[$i]."' AND active IN ('0','1')";
    $req=$DB_site2->query($sql);
    while($rep=$DB_site2->fetch_array($req)){
        $nlorder=new MP_Order_Line(0,$DB_site2);
        $nlorder->order=$mporder->id;
        $nlorder->artid=$rep['artid'];
        $nlorder->qte=$mporder->qte[$i];
        $nlorder->ref_tin=$mporder->nom[$i];
        $nlorder->product_name=$rep['libelleFR'];
        $nlorder->price=$mporder->prix[$i];
        $nlorder->paht=$rep['paht'];
        $nlorder->ecopart=$rep['ecopart'];
    }
    //nice($nlorder);
    $nlorder->create();
    $typeGarantie = $template->translate("/article/garantie_type/garantie",'F');
    DAOlignefacturegarantie::insertFourn($DB_site2, "base",$nlorder->artid,$nlorder->id,$mporder->shipping_country,$typeGarantie,1);
    $mporder->items_price  =$mporder->calc_items_price();  
    $suivitxt="Ajout ".$nlorder->qte." ".$nlorder->ref_tin." ".$nlorder->price." &euro; dans la commande<br>".recap($_POST['orderid']);
    $DB_site2->query("INSERT INTO suivi_commande SET facturid=".$nlorder->order.", date=".time().", commercial='".$user_info[username]."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivitxt)."'");
   
   if (floatval($_POST['interetbca'])>0) {
		$nlorder=new MP_Order_Line(0,$DB_site2);
		$nlorder->order=$mporder->id;
		$nlorder->artid=0;
		$nlorder->qte=1;
		$nlorder->ref_tin='produit';
		$nlorder->product_name='Facilités de paiement';
		$nlorder->price=floatval($_POST['interetbca']);
		$nlorder->paht=0;
		$nlorder->create();
   }
 
  }
  }else{
    $result=$mporder->update();
    $txt="Modification commande ".$mporder->id." ".$mporder->marketplaces[$mporder->market_place_id]." ".$mporder->mp_facturid." du ".$_POST['orderdate']."<br>
    Facturation :<br>".$mporder->billing_first_name." ".$mporder->billing_last_name."<br>".$mporder->billing_address."<br>".$mporder->billing_zip." ".$mporder->billing_city."<br>".$mporder->billing_phone."<br>".$mporder->billing_email."
    <br><br>Livraison :<br>".$mporder->shipping_first_name." ".$mporder->shipping_last_name."<br>".$mporder->shipping_address."<br>".$mporder->shipping_zip." ".$mporder->shipping_city."<br>".$mporder->shipping_phone."<br>".$mporder->shipping_details;
  }

  if($result){
    $_GET['step']=2;
    $DB_site2->query("INSERT INTO suivi_commande SET facturid=".$mporder->id.", date=".time().", commercial='".$user_info['username']."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$txt)."'");
  }
} //fin n°1

if(isset($_POST['orderid']) && isset($_POST['prod'])){ //n°2

  if($_POST['prod']!=""){
    $mporder=new MP_Order(0,$DB_site2);
	  $mporder->load($_POST['orderid']);

	  $nlorder=new MP_Order_Line(0,$DB_site2);
	  $nlorder->order=$_POST['orderid'];
	  $nlorder->artid=$_POST['artid'];
	  $nlorder->qte=$_POST['qtt'];
	  $nlorder->ref_tin=$_POST['prod'];
	  $nlorder->product_name=$_POST['description'];
	  $nlorder->price=$_POST['price'];
	  $nlorder->paht=$_POST['buyprice'];
	  $nlorder->create();
      
	  $suivitxt="Ajout ".$nlorder->qte." ".$nlorder->ref_tin." ".$nlorder->price." &euro; dans la commande<br>".recap($_POST['orderid']);

	  $DB_site2->query("INSERT INTO suivi_commande SET facturid=".$nlorder->order.", date=".time().", commercial='".$user_info[username]."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivitxt)."'");

    $i=0;
    while(isset($_POST['caract_'.$i])){
      $nlorder->updcaract($_POST['caract_'.$i],$_POST['caractname_'.$i]);
      $i++;
    }
         
    $duree_init = 0;
    if(isset($_POST['gar_fourn'])){
      $garantie=new garantie($DB_site2,"base","FR",$_POST['gar_fourn'],$_POST['artid']);
      $codefourn=DAOfournisseur::loadCodeFournisseurByName($DB_site2,$garantie->fournisseur);
      DAOlignefacturegarantie::insert($DB_site2,"base",$garantie->garantieid,$_POST['artid'],$nlorder->id,$garantie->cumul == 1 ? 'Extension de garantie +' : 'Garantie',$garantie->duree,$garantie->fournisseur,$codefourn,$mporder->billing_country,$garantie->duree,$garantie->type_garantie,$garantie->prix,$garantie->ppi,$garantie->paht);
      $duree_init += $garantie->duree;
    }
         
    if(isset($_POST['garantie'])){
      $garantie=new garantie($DB_site2,"base","FR",$_POST['garantie'],$_POST['artid']);
      $codefourn=DAOfournisseur::loadCodeFournisseurByName($DB_site2,$garantie->fournisseur);
      DAOlignefacturegarantie::insert($DB_site2,"base",$garantie->garantieid,$_POST['artid'],$nlorder->id,$garantie->cumul == 1 ? 'Extension de garantie +' : 'Garantie',$garantie->cumul == 1 ? $garantie->duree + $duree_init : $garantie->duree,$garantie->fournisseur,$codefourn,$mporder->billing_country,$garantie->duree,$garantie->type_garantie,$garantie->prix,$garantie->ppi,$garantie->paht);
    }
  }

  $mporder=new MP_Order(0,$DB_site2);
  $mporder->load($_POST['orderid']);
     
  $_GET['step']=2;
} //fin n°2

if(isset($_POST['shipprice']) && isset($_POST['shippingorder'])){//n°3
  $mporder=new MP_Order(0,$DB_site2);
  $mporder->load($_POST['shippingorder']);
  $mporder->shipping_price=($_POST['shipprice']);
  $mporder->update();
  $mporder->calc_items_price();
  $_GET['step']=2;
  $suivitxt="Modification frais de port : ".$_POST['shipprice']." &euro;<br>".recap($_POST['shippingorder']);
  $DB_site2->query("INSERT INTO suivi_commande SET facturid=".$_POST['shippingorder'].", date=".time().", commercial='".$user_info[username]."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivitxt)."'");
}//fin n°3

if(isset($_POST['commission']) && isset($_POST['commissionorder'])){//n°4
  $mporder=new MP_Order(0,$DB_site2);
  $mporder->load($_POST['commissionorder']);
  $mporder->commission=$_POST['commission'];
  $mporder->update();
  $_GET['step']=2;                      
}//fin n°4

if(isset($_GET['order'])){ //n°5
  $mporder=new MP_Order(0,$DB_site2);
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
}//fin n°5

if(isset($_GET['del'])){//n°6
  $mporderline=new MP_Order_Line(0,$DB_site2);
  $mporderline->load($_GET['del']);

  if($mporderline->order_status==0){
  	$suivitxt="Suppression ".$mporderline->qte." ".$mporderline->ref_tin." ".$mporderline->price." &euro; de la commande<br>".recap($_GET['order']);
  	$DB_site2->query("INSERT INTO suivi_commande SET facturid=".$_GET['order'].", date=".time().", commercial='".$user_info[username]."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivitxt)."'");
  	$mporderline->delete();
  }

  $mporder->load($_GET['order']);
}//fin n°6

//tout le temps?
print "<div><h3>Ajouter/modifier une commande Market Place</h3> <a href=\"javascript:history.back()\">retour</a></div>\n";

$step=1;
if(isset($_GET['step'])) 
  $step=$_GET['step'];

switch($step){
  case 1:
    $mpo = new mp_order(0,$DB_site2);
    //$radiomp = "";
    $selectmp = "";
    foreach($mpo->marketplaces as $mpnb=>$mpname){
      //$radiomp .= "<br><input type=\"radio\" name=\"marketplace\" value=\"".$mpnb."\"".($mporder->market_place_id==$mpnb?" checked":"")."> ".$mpname." ($mpnb)\n";
      $selectmp .= "<option value=\"".$mpnb."\">".$mpname." ($mpnb)</option>";
    }
      

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
    
    $orderdate="";
    if($mporder->order_date>0) $orderdate=date("d/m/Y H:i",$mporder->order_date);


    $deliverdate="";
    if($mporder->billing_deliver>0) $deliverdate=date("d/m/Y H:i",$mporder->billing_deliver);

    $optbcountries=$optscountries="";
    if($mporder->billing_country=="") $mporder->billing_country=57;
    if($mporder->shipping_country=="") $mporder->shipping_country=57;

    $requete=$DB_site2->query("SELECT paysid,libelle FROM pays ORDER BY libelle");
    while($pays=$DB_site2->fetch_array($requete)){
      $optbcountries.="<option value=\"$pays[paysid]\"".($mporder->billing_country==$pays[paysid]?" selected":"").">$pays[libelle]</option>";
      $optscountries.="<option value=\"$pays[paysid]\"".($mporder->shipping_country==$pays[paysid]?" selected":"").">$pays[libelle]</option>";
    }

    //recreation des inputs hidden pour la semi automatisation des commandes marketplace
    
    foreach($_POST as $name => $value)
    {
      //echo $name."->".$value."</br>";
      echo "<input type=\"hidden\" name=\"".$name."\" value=\"".$value."\">";
    }

    // Commande
    
    if(isset($_POST['date_commande']))
    {
      $orderdate = $_POST['date_commande'];
    }
    if(isset($_POST['market_place']) && isset($_POST['market_place_id']))
    {
      $selectmp = "<option value=\"".$_POST['market_place_id']."\">".$_POST['market_place']." (".$_POST['market_place_id'].")</option>"; 
    }
    $count = 0;
    for($count; $count < $_POST["nbr_produit"]; $count++){ 
        $nom = "nom_art".$count;
        $chaine .= "<input type=\"hidden\" name=\"nom".$count."\" value=\"".$_POST[$nom]."\">";
        $qte = "qte_art".$count;
        $chaine .= "<input type=\"hidden\" name=\"qte".$count."\" value=\"".$_POST[$qte]."\">";
        $prix = "prix_art".$count;
        $chaine .= "<input type=\"hidden\" name=\"prix".$count."\" value=\"".$_POST[$prix]."\">";
    } 
    print "<form action=\"index.php?create=1\" method=\"post\" id=\"formCreate\">
    <input type=\"hidden\" name=\"interetbca\" value=\"".(isset($_POST['interetbca']) ? $_POST['interetbca'] : 0)."\">
    <input type=\"hidden\" name=\"modiforder\" value=\"".$mporder->id."\">
    <input type=\"hidden\" name=\"nbr_produit\" value=\"".(isset($_POST['nbr_produit']) ? $_POST['nbr_produit'] : 0)."\">
    <input type=\"hidden\" name=\"fdeport\" value=\"".(isset($_POST['frais_de_port']) ? $_POST['frais_de_port'] : 0)."\">
    <input type=\"hidden\" name=\"commission\" value=\"".(isset($_POST['commission']) ? $_POST['commission'] : 0)."\">
    <input type=\"hidden\" name=\"infoimportante\" value=\"".(isset($_POST['infoimportante']) ? $_POST['infoimportante'] : "")."\">
    ".$chaine."
   	  <table class=\"frameCde\">
      <tr>
        <td colspan=\"2\" class=\"frametitle\">Commande</td>
      </tr>
      <tr>
        <td>Market Place</td>
        <td><select name=\"marketplace\">".$selectmp."</select></td>
      </tr>
      <tr>
        <td>Date de la commande *</td>
        <td>
          <input type=\"text\" size=\"14\" name=\"orderdate\" id=\"orderdate\" value=\"".$orderdate."\" onclick=\"calendrier_show(this.id,this.value,true)\" readonly>
          <input type=\"hidden\" name=\"orderdate_hidden\" id=\"orderdate_hidden\" value=\"\">
        </td>
      </tr>
      <tr>
        <td>Numéro de commande de la Market Place *</td>
        <td><input type=\"text\" size=\"33\" name=\"setorderid\" value=\"".(isset($_POST['id_commande']) && $_POST['id_commande'] != '' ? $_POST['id_commande'] : $mporder->id)."\"><a href=\"javascript:void(0)\" onclick=\"verifCde();\" class=\"verifCde\"> <img src=\"../images/icon/viewmag.png\" width=\"25\"></a></td>
      </tr>
      <tr>
        <td>Date de livraison *</td>
        <td>
        <input type=\"text\" size=\"14\" name=\"deliverdate\" id=\"deliverdate\" value=\"".$deliverdate."\" onclick=\"calendrier_show(this.id,this.value,true, null, null, 1)\"readonly>
        <input type=\"hidden\" name=\"deliverdate_hidden\" id=\"deliverdate_hidden\" value=\"\">
        </td>
      </tr>
      <tr>
        <td>(* Champs obligatoire)</td>
        <td><span id=\"cdeResult\"></td>
      </tr>
      <tr>
        <td colspan=\"2\"><div id=\"cal_ok\" class=\"cal_ok\" onclick=\"checkCde('".addslashes($_POST['facturation_nom'])."');\"><span>SUIVANT </span></div></td>
      </tr>
    </table>\n";

    // Client
    echo $_POST['facturation_nom']."</br>".$_POST['facturation_prenom']."</br>".$_POST['facturation_adresse']."</br>".$_POST['facturation_cp']."</br>".$_POST['facturation_ville']."</br>";

    print "<p id=\"pClient\" class=\"verifClient\"></p>
            <p><span id=\"clientResults\"></p>";

    // Adresse
    print "<div id=\"tabClient\"></div>
    </form>";
    break;

    case 2:
    
      $listprod=$mporder->list_lines();

      $paysfacture=$DB_site2->fetch_array($DB_site2->query("SELECT libelle FROM pays WHERE paysid=".$mporder->billing_country));
      $payslivraison=$DB_site2->fetch_array($DB_site2->query("SELECT libelle FROM pays WHERE paysid=".$mporder->shipping_country));

      $lienbp="<a href=\"../../facture_mp.php?facturid=".$mporder->id."&cde=1\" target=\"_blank\">Bon de préparation</a>";
      $lienfacture="<a href=\"../../facture_mp.php?facturid=".$mporder->id."\" target=\"_blank\">Facture</a>";
      $lienexpedie="<input type=\"button\" onclick=\"delivered_state();\" value=\"Expédier\">";
      $lienannule="<input type=\"button\" onclick=\"if(confirm('Etes vous sur de vouloir annuler la commande ".$mporder->id." ?')) document.location='index.php?order=".$mporder->id."&state=3'\" value=\"Refuser\">";
      $liensup="<a href=\"javascript:if(confirm('Etes vous sur de vouloir supprimer définitivement la commande ".$mporder->id." ?')) document.location='index.php?del=".$mporder->id."\" style=\"color:red\">Supprimer la commande</a>";

      // Récap Commande
      print "<table class=\"frame\">
        <tr>
          <td colspan=\"2\" class=\"frametitle\">Commande</td>
        </tr>
        <tr>
          <td>Numéro : ".$mporder->id."</td>
          <td>n° facture : ".$mporder->billing_number."-".$mporder->market_place_id."</td>
        </tr>
        <tr>
          <td>Commande ".$mporder->market_place." n°".$mporder->mp_facturid."</td>
          <td>Date : ".date("d/m/Y",$mporder->order_date)."</td>
        </tr>
        <tr>
          <td></td>
          <td>Livraison : ".date("d/m/Y",$mporder->billing_deliver)."</td>
        </tr>
      </table>\n";

      // Informations client
      print "<table class=\"frame\">
        <tr>
          <td colspan=\"2\" class=\"frametitle\">Informations client</td>
        </tr>
        <tr>
          <td><b>Facturation</b>
            <br><input type=\"hidden\" name=\"raisonsociale\" value=\"".$mporder->billing_company."\">".$mporder->billing_company."
            <br>".$mporder->billing_first_name." ".$mporder->billing_last_name."
            <br>".$mporder->billing_address."
            <br>".$mporder->billing_zip." ".$mporder->billing_city."
            <br>".$paysfacture['libelle']."
            <br>".$mporder->billing_phone."
          </td>
          <td><b>Livraison</b>
            <br>".$mporder->shipping_company."
            <br>".$mporder->shipping_first_name." ".$mporder->shipping_last_name."
            <br>".$mporder->shipping_address."
            <br>".$mporder->shipping_zip." ".$mporder->shipping_city."
            <br>".$payslivraison['libelle']."
            <br>".$mporder->shipping_phone."
          </td>
        </tr>
        <tr>
          <td>Info trspt : ".$mporder->shipping_details."</td>
        </tr>
        <tr>
          <td><a href=\"index.php?order=".$mporder->id."&step=1\">Modifier les infos de facturation et livraison</a></td>
        </tr>
      </table>";
      
      // Détails commandes   
      print "<input type=\"hidden\" id=\"paysid\" value=\"".$mporder->billing_country."\">
      <table class=\"frame\">
        <tr>
          <td colspan=\"2\" class=\"frametitle\">Détail des produits commandés</td>
        </tr>
        <tr>
          <td colspan=\"2\">
            <table cellspacing=\"0\" cellpadding=\"5\" class=\"plist\">
              <tr>
                <td class=\"titlelist\">Référence</td>
                <td class=\"titlelist\">Description</td>
                <td class=\"titlelist\">Quantité</td>
                <td class=\"titlelist\">Prix Unit. T.T.C</td>
                <td class=\"titlelist\">Total T.T.C</td>
                <td></td>
              </tr>\n";
      if($listprod!=false){
        foreach($listprod as $prod){
          print "<tr>
                  <td>".$prod->ref_tin."</td>
                  <td>".$prod->product_name."</td>
                  <td>".$prod->qte."</td>
                  <td>".round($prod->price,2)."</td>
                  <td>".round($prod->qte*$prod->price,2)."</td>
                  <td><a href=\"index.php?order=".$mporder->id."&del=".$prod->id."\"><img src=\"../images/icon/cancel.png\" alt=\"Supprimer\" width=\"12\"></a></td>
                </tr>\n";
        }
      }

      print "</table>
        </td>
      </tr>\n
      
      <tr>
        <td colspan=\"2\" height=\"40\">Rechercher un produit <input type=\"text\" name=\"src\" id=\"newsrc\"><input type=\"button\" value=\"OK\" onclick=\"srcprod(".($mporder->billing_company != '' ? true : false).")\"></td>
      </tr>
      <tr>
        <td colspan=\"2\" id=\"mpprodresults\"></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td colspan=\"2\" id=\"addprod\" style=\"height:40px; visibility:hidden; position:absolute;background-color:#dddddd;\">
          <form name=\"addline\" action=\"index.php\" method=\"post\">
            <input type=\"hidden\" name=\"orderid\" value=\"".$mporder->id."\">
            <input type=\"hidden\" name=\"artid\" value=\"\">
            <input type=\"text\" name=\"prod\" value=\"\" readonly><input type=\"hidden\" name=\"buyprice\" value=\"\"> &nbsp;Quantité <input type=\"text\" name=\"qtt\" size=\"4\" value=\"1\"> &nbsp;<input type=\"hidden\" name=\"strikeout_price\" size=\"6\" value=\"\"> Prix vente unit. T.T.C <input type=\"text\" name=\"price\" size=\"6\" value=\"\"><input type=\"hidden\" name=\"description\" value=\"\"><input type=\"hidden\" name=\"barcode\" value=\"\">
            <div id=\"divopts\"></div>
            <input type=\"button\" value=\"Ajouter à la commande\" onclick=\"submitline()\">
          </form>\n
        </td>
      </tr>\n";

      $totalht=($mporder->shipping_price/(1+$mporder->vat_100/100))+$mporder->items_price+$mporder->interetbca/1.2;

      $affich ="
      <tr>
        <td colspan=\"2\" style=\"padding-top:10px;\">
          <form action=\"index.php\" method=\"post\">
            <input type=\"hidden\" name=\"shippingorder\" value=\"".$mporder->id."\">
            <table cellspacing=\"0\" cellpadding=\"4\" class=\"plist\">
              <tr>
                <td>Port T.T.C</td>
                <td><input type=\"text\" name=\"shipprice\" id=\"shipprice\" size=\"5\" value=\"".number_format(($mporder->shipping_price),2,",","")."\"> <input type=\"submit\" value=\"OK\"></td>
              </tr>
              <tr>
                <td>Total H.T.</td>
                <td>".moneyformat($totalht)."</td>
              </tr>
              <tr>
                <td>T.V.A. (".(TVA_COEFF*100-100)." %)</td>
                <td>".moneyformat($totalht*(TVA_COEFF-1))."</td>
              </tr>
              <tr>
                <td>Total T.T.C.</td>
                <td>".moneyformat($mporder->totalttc())."</td>
              </tr>
            </table>
          </form>
          <form action=\"index.php\" method=\"post\">
            <input type=\"hidden\" name=\"commissionorder\" value=\"".$mporder->id."\">
            <table cellspacing=\"0\" cellpadding=\"0\" class=\"plist\">
              <tr>
                <td align=\"center\">Coût Market Place</td>
              </tr>
              <tr>
                <td align=\"center\"><input type=\"text\" name=\"commission\" value=\"".$mporder->commission."\"> <input type=\"submit\" value=\"OK\"></td>
              </tr>
            </table>
          </form>
        </td>
      </tr>\n
      <tr>";
      if ($listprod!=false)
      {
        $affich .= "<td><div id=\"cal_ok\" class=\"cal_ok\" onclick=\"javascript:location.href='../facture.php?facturid=".$mporder->id."&action=editer'\"><span>Traiter la commande </span></div></td>";
      }
      $affich .="</tr>
      </table>";
      print ($affich);
      break;

    case 3:
      include("adv2.php");

      $listprod=$mporder->list_lines();
      $buyprice=$mporder->getbuyprice();

      $lienbp="<a href=\"../../facture_mp.php?facturid=".$mporder->id."&cde=1\" target=\"_blank\">Bon de préparation</a>";
      $lienfacture="<a href=\"../../facture_mp.php?facturid=".$mporder->id."\" target=\"_blank\">Facture</a>";
      $lienexpedie="<input type=\"button\" onclick=\"delivered_state();\" value=\"Expédier\">";
      $lienannule="<input type=\"button\" onclick=\"if(confirm('Etes vous sur de vouloir annuler la commande ".$mporder->id." ?')) document.location='index.php?order=".$mporder->id."&state=3'\" value=\"Refuser\">";
      $liensup="<a href=\"javascript:if(confirm('Etes vous sur de vouloir supprimer définitivement la commande ".$mporder->id." ?')) document.location='index.php?del=".$mporder->id."\" style=\"color:red\">Supprimer la commande</a>";
      $liensav="<a href=\"index.php?order=".$mporder->id."&sav=new\" color=\"green\">Mettre la commande en S.A.V. </a>";
      $liensav2="<a href=\"index.php?order=".$mporder->id."&sav=end\" color=\"red\">Fin du S.A.V. </a>";

      print "<table class=\"frame\">
        <tr><td colspan=\"5\" class=\"frametitle\">Commande</td></tr>
        <tr><td>Numéro</td><td>".$mporder->id."</td><td></td><td>n° facture</td><td>".$mporder->billing_number."-".$mporder->market_place_id."</td></tr>
        <tr><td>Date</td><td>".date("d/m/Y",$mporder->order_date)."</td><td></td><td>Date d'expédition</td><td>".date("d/m/Y",$mporder->billing_date)."</td></tr>
        <tr><td>Etat actuel</td><td>".$mporder->order_state."</td><td></td><td>SAV</td><td>".($mporder->order_status==2?$liensav:$liensav2)."</td></tr>
      </table>\n";

      print "<table class=\"frame\">
        <tr><td colspan=\"2\" class=\"frametitle\">Informations client</td></tr>
        <tr>
          <td><b>Facturation</b><br><input type=\"hidden\" name=\"raisonsociale\" value=\"".$mporder->billing_company."\">".$mporder->billing_company."<br>".$mporder->billing_first_name." ".$mporder->billing_last_name."<br>".$mporder->billing_address."<br>".$mporder->billing_zip." ".$mporder->billing_city."<br>".$paysfacture['libelle']."<br>".$mporder->billing_phone."</td>
          <td><b>Livraison</b><br>".$mporder->shipping_company."<br>".$mporder->shipping_first_name." ".$mporder->shipping_last_name."<br>".$mporder->shipping_address."<br>".$mporder->shipping_zip." ".$mporder->shipping_city."<br>".$payslivraison['libelle']."<br>".$mporder->shipping_phone."</td>
        </tr>
        <tr><td>Info trspt : ".$mporder->shipping_details."</td></tr>
      </table>";

      print "<table class=\"frame\">
        <tr><td colspan=\"2\" class=\"frametitle\">Détail des produits commandés</td></tr>
        <tr><td colspan=\"2\"><table cellspacing=\"0\" cellpadding=\"5\" class=\"plist\">
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
      </td></tr>
  	  </table>\n";

      print "<div class=\"frame\">
        <p class=\"frametitle\">Suivi client</p>
        <form action=\"index.php?order=".$mporder->id."\" method=\"post\">
        <textarea name=\"comment\" cols=\"80\" rows=\"4\">".$mporder->comment."</textarea>
        <br><input type=\"submit\" value=\"Modifier\">
        </form>
      </div>";

      //if($mporder->order_status==2 || $mporder->order_status==4) print "<tr><td><a href=\"../../facture_mp.php?facturid=".$mporder->id."\" target=\"_blank\"><img src=\"images/pdf.gif\" border=\"0\" align=\"absmiddle\"> Facture</a> &nbsp; <a href=\"#\" onclick=\"javascript:if(confirm('Envoyer la facture par email au client ?')) document.location='index.php?order=".$mporder->id."&sendbill=1';\"><img src=\"images/pdf.gif\" border=\"0\" align=\"absmiddle\">Envoyer la facture</a></td></tr>\n";
      //if($mporder->order_status==2) print "<tr><td><a href=\"index.php?order=".$mporder->id."&sav=new\"><img src=\"../../images/sav.jpg\" border=\"0\" align=\"absmiddle\"> Mettre la commande en S.A.V. </a></td></tr>\n";
      //if($mporder->order_status==4) print "<tr><td><a href=\"index.php?order=".$mporder->id."&sav=end\"><img src=\"../../images/sav.jpg\" border=\"0\" align=\"absmiddle\"> Fin du S.A.V. </a></td></tr>\n";
      break;
}

?>
