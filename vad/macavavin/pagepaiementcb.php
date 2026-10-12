<?php
$mttfianet = $totalmontant ;
$montantconversion = $totalmontant ;

if($moyen == 13 || $moyen == 6){ // paypal american express

	include("CallerService.php");

	$aLogo = array(
		'mcav'   => 'https://static.e-junkie.com/sslpic/162140.b0bb18e967d6049462c2a22a9faa6929.jpg',
		'mwccom' => 'https://static.e-junkie.com/sslpic/162141.506619231ce2409dfbf0dc3052a1b7fe.jpg',
		'mwcuk'  => 'https://static.e-junkie.com/sslpic/162142.74907bcc5609119439c48b0dd1b1e163.jpg'
	);

	$provenance = 'mec';

	if($facture->diminutif=="MC") $facture->diminutif="FR";
	$cancelURL =urlencode($template->translate("/site/[site_code]/url").'/DoExpressCheckoutPayment.php?currencyCodeType=EUR&paymentType='.$moyen.'&paymentAmount='.round($totalmontant,2)."&order=".$facture->facturid);
        $returnURL = $cancelURL."&action=delpanier_complet";
	$nvpstr="&AMT=".round($totalmontant,2)."&PAYMENTACTION=sale&RETURNURL=".$returnURL."&CANCELURL=".$cancelURL."&HDRIMG=".$aLogo[$provenance]."&CURRENCYCODE=".$template->devise()."&INVNUM=".$facture->facturid."&SHIPTONAME=".urlencode($facture->prenom." ".$facture->nom)."&SHIPTOSTREET=".urlencode($facture->adresse)."&SHIPTOCITY=".urlencode($facture->ville)."&SHIPTOCOUNTRYCODE=".$facture->diminutif."&SHIPTOZIP=".$facture->codepostal."&ADDROVERRIDE=1&SOLUTIONTYPE=Sole";

	$resArray=hash_call("SetExpressCheckout",$nvpstr);
        $_SESSION['reshash']=$resArray;
        $ack = strtoupper($resArray["ACK"]);

	 if($ack=="SUCCESS"){
	    $token = urldecode($resArray["TOKEN"]);
	    $lienfinal=PAYPAL_URL.$token;
	    $pagepaiementpaypal="<a href=".$lienfinal."><img border=\"0\" width=\"91\" alt=\"Marque d'acceptation\" src=\"https://www.paypal.com/fr_FR/FR/i/logo/PayPal_mark_180x113.gif\"></a>";
	    header("location:".PAYPAL_URL.$token);
	 }else{
	    //var_dump($totalmontant);
// 	    var_dump($resArray);
	 }
}

var_dump('expression');
die;
if ($moyen == 1) {
	/**************************************************************************/
	/****************PAIEMENT SECURISE VIA CREDIT AGRICOLE*********************/
	/**************************************************************************/
	/**************************************************************************/
	/*****$totalmontant = montant en euros de la transaction*******************/
	/*****$facturid = num de transaction unique********************************/
	/**************************************************************************/
	/**************************************************************************/
	/**************************************************************************/
	
	$totalmontant = number_format($totalmontant, 2, '.', '')*100;
	$pagepaiementcb="";
	$montant=$totalmontant;
	$lg = strtolower($langue) ;
	if ($lg == "de")
		{
		$lg = "ge" ;
		}

	$parm="merchant_id=045294738500039";
	$parm="$parm amount=$montant";
	$parm="$parm automatic_response_url=https://fusion.ma-cave-a-vin.fr/vad/macavavin/call_autoresponse.php";
	$parm="$parm cancel_return_url=https://fusion.ma-cave-a-vin.fr/index.php";
	$parm="$parm currency_code=978";
	$parm="$parm language=$lg";
	$parm="$parm normal_return_url=https://fusion.ma-cave-a-vin.fr/checkout_paiement.php";
	$parm="$parm payment_means=CB,1,VISA,1,MASTERCARD,1,AMEX,2";
	$parm="$parm transaction_id=$facturid";
	$parm="$parm merchant_country=fr";
	$parm="$parm logo_id=commercant.gif";
	$parm="$parm pathfile=/var/www/vhosts/ma-cave-a-vin.fr/vad/param/pathfile";
	//if(isset($_COOKIE['admin_username']) && $_COOKIE['admin_username'] == 'sylvain') nicex($parm);
	$path_bin = "/var/www/vhosts/ma-cave-a-vin.fr/vad/bin/request";
	$result=exec("$path_bin $parm");
	$tableau = explode ("!", "$result");
	$code = $tableau[1];
	$error = $tableau[2];
	$message = $tableau[3];
	if (( $code == "" ) && ( $error == "" ) ){
		$pagepaiementcb.= "<CENTER>erreur appel request</CENTER><BR>";
		$pagepaiementcb.= "executable request non trouve $path_bin<br>";
		$pagepaiementcb.= "Result : $result";
	}
	else if ($code != 0){
		$pagepaiementcb.= "<center><b><h2>Erreur appel API de paiement.</h2></center></b>";
		$pagepaiementcb.= "<br><br><br>";
		$pagepaiementcb.= " message erreur : $error (code : $code)";
	}
	else {
		$pagepaiementcb.= $message;
        }
	/**************************************************************************/
	/****************PAIEMENT SECURISE VIA CREDIT AGRICOLE*********************/
	/**************************************************************************/

	}
elseif ($moyen == 8)
	{

	// receive and pay
	$infosxml=$DB_site->query_first("select u.mail umail, u.civilite ucivilite, u.nom unom, u.prenom uprenom, u.codepostal ucodepostal, 
	u.adresse uadresse, u.ville uville, u.telephone utelephone, u.raisonsociale uraisonsociale, p1.libelle upays, p1.paysid AS upaysid, u.portable uportable, f.facturid, 
	f.prixport, f.tvaport, f.civilite, f.nom, f.prenom, f.codepostal, f.adresse, f.ville, f.telephone, f.raisonsociale, p2.libelle, p2.paysid AS paysid, f.mail, 
	f.montantremise, f.montantcadeau, f.ip, f.timestamp from facture f inner join utilisateur u on (f.userid = u.userid), pays p1, pays p2 
	WHERE f.facturid = '$factureid' and p1.paysid = u.paysid and p2.paysid = f.paysid");
	$xml = "<?xml version='1.0' encoding='ISO-8859-1'?>" ;
	$xml .= "<control>" ;
	$xml .= "<utilisateur type='facturation' qualite='2'>" ;
	switch ($infosxml[ucivilite])
		{
		case 0:
			$infosxml[ucivilite] = "monsieur" ;
			break;
		case 1:
			$infosxml[ucivilite] = "madame" ;
			break;
		case 2:
			$infosxml[ucivilite] = "mademoiselle" ;
			break;
		}
		
		
		
		
	if ($infosxml[upaysid]==57 || $infosxml[upaysid]==181)
		$infosxml[upays]="France";
		
	if ($infosxml[paysid]==57 || $infosxml[paysid]==181)
		$infosxml[libelle]="France";	
		
		
	$xml .= "<nom titre='".$infosxml[ucivilite]."'>$infosxml[unom]</nom>" ;
	$xml .= "<prenom>$infosxml[uprenom]</prenom>" ;
	$xml .= "<societe>$infosxml[uraisonsociale]</societe>" ;
	$xml .= "<telhome>$infosxml[utelephone]</telhome>" ;
	$xml .= "<teloffice></teloffice>" ;
	$xml .= "<telmobile>$infosxml[uportable]</telmobile>" ;
	$xml .= "<telfax></telfax>" ;
	$xml .= "<email>$infosxml[umail]</email>" ;
	$xml .= "</utilisateur>" ;
	$xml .= "<adresse type='facturation' format='1'>" ;
	$xml .= "<rue1>$infosxml[uadresse]</rue1>" ;
	$xml .= "<rue2></rue2>" ;
	$xml .= "<cpostal>$infosxml[ucodepostal]</cpostal>" ;
	$xml .= "<ville>$infosxml[uville]</ville>" ;
	$xml .= "<pays>$infosxml[upays]</pays>" ;
	$xml .= "<appartement>" ;
	$xml .= "<digicode1></digicode1>" ;
	$xml .= "<digicode2></digicode2>" ;
	$xml .= "<escalier></escalier>" ;
	$xml .= "<etage></etage>" ;
	$xml .= "<nporte></nporte>" ;
	$xml .= "<batiment></batiment>" ;
	$xml .= "</appartement>" ;
	$xml .= "</adresse>" ;
	$xml .= "<utilisateur type='livraison' qualite='2'>" ;
	switch ($infosxml[civilite])
		{
		case 0:
			$infosxml[civilite] = "monsieur" ;
			break;
		case 1:
			$infosxml[civilite] = "madame" ;
			break;
		case 2:
			$infosxml[civilite] = "mademoiselle" ;
			break;
		}
		
		
	
		
	
	$xml .= "<nom titre='".$infosxml[civilite]."'>$infosxml[nom]</nom>" ;
	$xml .= "<prenom>$infosxml[prenom]</prenom>" ;
	$xml .= "<societe>$infosxml[raisonsociale]</societe>" ;
	$xml .= "<telhome>$infosxml[telephone]</telhome>" ;
	$xml .= "<teloffice></teloffice>" ;
	$xml .= "<telmobile></telmobile>" ;
	$xml .= "<telfax></telfax>" ;
	$xml .= "<email>$infosxml[mail]</email>" ;
	$xml .= "</utilisateur>" ;
	$xml .= "<adresse type='livraison' format='1'>" ;
	$xml .= "<rue1>$infosxml[adresse]</rue1>" ;
	$xml .= "<rue2></rue2>" ;
	$xml .= "<cpostal>$infosxml[codepostal]</cpostal>" ;
	$xml .= "<ville>$infosxml[ville]</ville>" ;
	$xml .= "<pays>$infosxml[libelle]</pays>" ;
	$xml .= "<appartement>" ;
	$xml .= "<digicode1></digicode1>" ;
	$xml .= "<digicode2></digicode2>" ;
	$xml .= "<escalier></escalier>" ;
	$xml .= "<etage></etage>" ;
	$xml .= "<nporte></nporte>" ;
	$xml .= "<batiment></batiment>" ;
	$xml .= "</appartement>" ;
	$xml .= "</adresse>" ;
	$xml .= "<infocommande>" ;
	$xml .= "<refid>$factureid</refid>" ;
	//$SiteID = 6903 ;
	$xml .= "<siteid>$fianetsiteid</siteid>" ;
	$mttfianet = str_replace(" ", "", $mttfianet) ;
	$mttfianet = str_replace(",", ".", $mttfianet) ;
	//echo "totalmontant = $totalmontant,  mttfianet = $mttfianet" ;
	$xml .= "<montant devise='EUR'>$mttfianet</montant>" ;
	$xml .= "<ip timestamp='".$infosxml[timestamp]."'>$infosxml[ip]</ip>" ;
	$xml .= "<transport>" ;
	$xml .= "<type>4</type>" ;
	$xml .= "<rapidite>2</rapidite>" ;
	$lignesfactures=$DB_site->query("select artcode, qte, libelle, artid from lignefacture WHERE factureid = '$factureid'");
	$nb=$DB_site->num_rows($lignesfactures);
	$transporteur = "MORY";
	if ($nb == 1)
		{
		$article = $DB_site->query_first("SELECT sowine FROM article a INNER JOIN lignefacture lf ON (lf.artid = a.artid) WHERE lf.factureid = '$factureid'");
		if ($article[sowine] == '1')
			$transporteur = "TNT";
		}
	$xml .= "<nom>$transporteur</nom>" ;
	$xml .= "</transport>" ;
	$xml .= "<list nbproduit='".$nb."'>" ;
	$lstartid=array();
	while ($lignefacture=$DB_site->fetch_array($lignesfactures))
		{
		$lignefacture[libelle] = str_replace("\"", " ", $lignefacture[libelle]) ;
		$lignefacture[libelle] = str_replace("'", " ", $lignefacture[libelle]) ;
		$xml .= "<produit type='5' ref='".$lignefacture[artcode]."' nb='".$lignefacture[qte]."'>".$lignefacture[libelle]."</produit>" ;
		$lstartid[]=$lignefacture['artid'];
		}
	$xml .= "</list>" ;
	$xml .= "</infocommande>" ;
	
	$xml = str_replace("&", "et", $xml);
	
	$datecom = date("Y-m-d H:i:s");
	
	require("fianet_key_64bits.php");

	$datecom = date("Y-m-d H:i:s");
	$datelivr = date("Y-m-d",datelivraison($factureid)) ;

	$Sample = new EncodingKey();
	$key = $Sample->giveHashCode2("f07dbc36eabcbc55de7df04adda5e5fd", substr($datecom, -2, 2), "$infosxml[mail]", "$factureid", "$mttfianet", "$infosxml[nom]");
	
	$xml .= "<wallet version='1.0'>" ;
	$xml .= "<datelivr>$datelivr</datelivr>" ;
	$xml .= "<datecom>$datecom</datecom>" ;
	$xml .= "<crypt version='2.0'>$key</crypt>";
	$xml .= "</wallet>";
	$xml .= "</control>";
	
	$xml = trim($xml);
	$xml = str_replace("\\'","'",$xml);
	$xml = str_replace("\\\"","\"",$xml);
	$xml = str_replace("\\\\","\\",$xml);
	$xml = str_replace("\t","",$xml);
	$xml = str_replace("\n","",$xml);
	$xml = str_replace("\r","",$xml);
	/*
	$TypeIHM = "" ;
	if ($mttfianet > 4000)
		$TypeIHM = "<input type=\"hidden\" name=\"TypeIHM\" value=\"1\">" ;
	*/
	// wallet/engine/frontline.cgi
	// walter
	if ($mttfianet > 6000)
		$pagepaiementreceiveandpay =  "Ce moyen de paiement n'est pas disponible pour des commandes supérieures à 6000 euros." ;
	else
		{
		$pagepaiementreceiveandpay =  "<form name=\"formulaire\" method=\"post\" action=\"$receiveandpayurl\">
		<input type=\"hidden\" name=\"MerchID\" value=\"$fianetsiteid\">
		<input type=\"hidden\" name=\"XMLInfo\" value=\"$xml\">
		<input type=\"hidden\" name=\"URLCall\" value=\"https://fusion.ma-cave-a-vin.fr/index.php\">
		<input type=\"hidden\" name=\"URLSys\" value=\"fusion.ma-cave-a-vin.fr/vad/macavavin/call_autoresponse3.php\">
		<input type=\"hidden\" name=\"CheckSum\" value=\"$BonCheckSum\">
		<input type=\"hidden\" name=\"XMLParam\" value=\"$XMLParam\">
		<input type=\"hidden\" name=\"TypeIHM\" value=\"1\">
		<p align=\"center\"><img src=\"configurations/fusion.ma-cave-a-vin.fr/images/templates/kwixo.png\"></p>
		<p>Vous allez être automatiquement redirigé vers Kwixo dans quelques secondes, si cela ne fonctionne pas, cliquez sur le bouton ci dessous<br></p>
		<p align=\"center\"><input type=\"submit\" value=\"Continuer\"></p>
		</form>
		<script language=\"javascript\">
		window.onload=function(){
 		  document.forms.formulaire.submit();
		}
		</script>" ;
		}

	}
elseif ($moyen == 9)
	{
	// receive and pay
	$infosxml=$DB_site->query_first("select u.mail umail, u.civilite ucivilite, u.nom unom, u.prenom uprenom, u.codepostal ucodepostal, 
	u.adresse uadresse, u.ville uville, u.telephone utelephone, u.raisonsociale uraisonsociale, p1.libelle upays, p1.paysid AS upaysid, u.portable uportable, f.facturid, 
	f.prixport, f.tvaport, f.civilite, f.nom, f.prenom, f.codepostal, f.adresse, f.ville, f.telephone, f.raisonsociale, p2.libelle, p2.paysid AS paysid, f.mail, 
	f.montantremise, f.montantcadeau, f.ip, f.timestamp from facture f inner join utilisateur u on (f.userid = u.userid), pays p1, pays p2 
	WHERE f.facturid = '$factureid' and p1.paysid = u.paysid and p2.paysid = f.paysid");
	$xml = "<?xml version='1.0' encoding='ISO-8859-1'?>" ;
	$xml .= "<control>" ;
	$xml .= "<utilisateur type='facturation' qualite='2'>" ;
	switch ($infosxml[ucivilite])
		{
		case 0:
			$infosxml[ucivilite] = "monsieur" ;
			break;
		case 1:
			$infosxml[ucivilite] = "madame" ;
			break;
		case 2:
			$infosxml[ucivilite] = "mademoiselle" ;
			break;
		}
		
		
	if ($infosxml[upaysid]==57 || $infosxml[upaysid]==181)
		$infosxml[upays]="France";
		
	if ($infosxml[paysid]==57 || $infosxml[paysid]==181)
		$infosxml[libelle]="France";	
		
		
	
	$xml .= "<nom titre='".$infosxml[ucivilite]."'>$infosxml[unom]</nom>" ;
	$xml .= "<prenom>$infosxml[uprenom]</prenom>" ;
	$xml .= "<societe>$infosxml[uraisonsociale]</societe>" ;
	$xml .= "<telhome>$infosxml[utelephone]</telhome>" ;
	$xml .= "<teloffice></teloffice>" ;
	$xml .= "<telmobile>$infosxml[uportable]</telmobile>" ;
	$xml .= "<telfax></telfax>" ;
	$xml .= "<email>$infosxml[umail]</email>" ;
	$xml .= "</utilisateur>" ;
	$xml .= "<adresse type='facturation' format='1'>" ;
	$xml .= "<rue1>$infosxml[uadresse]</rue1>" ;
	$xml .= "<rue2></rue2>" ;
	$xml .= "<cpostal>$infosxml[ucodepostal]</cpostal>" ;
	$xml .= "<ville>$infosxml[uville]</ville>" ;
	$xml .= "<pays>$infosxml[upays]</pays>" ;
	$xml .= "<appartement>" ;
	$xml .= "<digicode1></digicode1>" ;
	$xml .= "<digicode2></digicode2>" ;
	$xml .= "<escalier></escalier>" ;
	$xml .= "<etage></etage>" ;
	$xml .= "<nporte></nporte>" ;
	$xml .= "<batiment></batiment>" ;
	$xml .= "</appartement>" ;
	$xml .= "</adresse>" ;
	$xml .= "<utilisateur type='livraison' qualite='2'>" ;
	switch ($infosxml[civilite])
		{
		case 0:
			$infosxml[civilite] = "monsieur" ;
			break;
		case 1:
			$infosxml[civilite] = "madame" ;
			break;
		case 2:
			$infosxml[civilite] = "mademoiselle" ;
			break;
		}
	$xml .= "<nom titre='".$infosxml[civilite]."'>$infosxml[nom]</nom>" ;
	$xml .= "<prenom>$infosxml[prenom]</prenom>" ;
	$xml .= "<societe>$infosxml[raisonsociale]</societe>" ;
	$xml .= "<telhome>$infosxml[telephone]</telhome>" ;
	$xml .= "<teloffice></teloffice>" ;
	$xml .= "<telmobile></telmobile>" ;
	$xml .= "<telfax></telfax>" ;
	$xml .= "<email>$infosxml[mail]</email>" ;
	$xml .= "</utilisateur>" ;
	$xml .= "<adresse type='livraison' format='1'>" ;
	$xml .= "<rue1>$infosxml[adresse]</rue1>" ;
	$xml .= "<rue2></rue2>" ;
	$xml .= "<cpostal>$infosxml[codepostal]</cpostal>" ;
	$xml .= "<ville>$infosxml[ville]</ville>" ;
	$xml .= "<pays>$infosxml[libelle]</pays>" ;
	$xml .= "<appartement>" ;
	$xml .= "<digicode1></digicode1>" ;
	$xml .= "<digicode2></digicode2>" ;
	$xml .= "<escalier></escalier>" ;
	$xml .= "<etage></etage>" ;
	$xml .= "<nporte></nporte>" ;
	$xml .= "<batiment></batiment>" ;
	$xml .= "</appartement>" ;
	$xml .= "</adresse>" ;
	$xml .= "<infocommande>" ;
	$xml .= "<refid>$factureid</refid>" ;
	//$SiteID = 6903 ;
	$xml .= "<siteid>$fianetsiteid</siteid>" ;
	$mttfianet = str_replace(" ", "", $mttfianet) ;
	$mttfianet = str_replace(",", ".", $mttfianet) ;
	//echo "totalmontant = $totalmontant,  mttfianet = $mttfianet" ;
	$xml .= "<montant devise='EUR'>$mttfianet</montant>" ;
	$xml .= "<ip timestamp='".$infosxml[timestamp]."'>$infosxml[ip]</ip>" ;
	$xml .= "<transport>" ;
	$xml .= "<type>4</type>" ;
	$xml .= "<rapidite>2</rapidite>" ;
// 	$lstartid=array();
	$lignesfactures=$DB_site->query("select artcode, qte, libelle, artid from lignefacture WHERE factureid = '$factureid'");
	$nb=$DB_site->num_rows($lignesfactures);
	$transporteur = "MORY";
	if ($nb == 1)
		{
		$article = $DB_site->query_first("SELECT sowine FROM article a INNER JOIN lignefacture lf ON (lf.artid = a.artid) WHERE lf.factureid = '$factureid'");
		if ($article[sowine] == '1')
			$transporteur = "TNT";
		}
	$xml .= "<nom>$transporteur</nom>" ;	
	$xml .= "</transport>" ;
	$xml .= "<list nbproduit='".$nb."'>" ;
	while ($lignefacture=$DB_site->fetch_array($lignesfactures))
		{
		$lignefacture[libelle] = str_replace("\"", " ", $lignefacture[libelle]) ;
		$lignefacture[libelle] = str_replace("'", " ", $lignefacture[libelle]) ;
		$xml .= "<produit type='5' ref='".$lignefacture[artcode]."' nb='".$lignefacture[qte]."'>".$lignefacture[libelle]."</produit>" ;
		$lstartid[]=$lignefacture['artid'];
		}
	$xml .= "</list>" ;
	$xml .= "</infocommande>" ;
	
	$xml = str_replace("&", "et", $xml);
	
	$datecom = date("Y-m-d H:i:s");
	
	require("fianet_key_64bits.php");

	$datecom = date("Y-m-d H:i:s");
	$datelivr = date("Y-m-d", datelivraison($factureid)) ;

	$Sample = new EncodingKey();
	$key = $Sample->giveHashCode2("f07dbc36eabcbc55de7df04adda5e5fd", substr($datecom, -2, 2), "$infosxml[mail]", "$factureid", "$mttfianet", "$infosxml[nom]");

	$xml .= "<wallet version='1.0'>" ;
	$xml .= "<datelivr>$datelivr</datelivr>" ;
	$xml .= "<datecom>$datecom</datecom>" ;
	$xml .= "<crypt version='2.0'>$key</crypt>";
	$xml .= "</wallet>";
	$xml .= "</control>";
	
	$xml = trim($xml);
	$xml = str_replace("\\'","'",$xml);
	$xml = str_replace("\\\"","\"",$xml);
	$xml = str_replace("\\\\","\\",$xml);
	$xml = str_replace("\t","",$xml);
	$xml = str_replace("\n","",$xml);
	$xml = str_replace("\r","",$xml);
	
	$TypeIHM = "" ;
	if ($mttfianet > 4000)
		$TypeIHM = "<input type=\"hidden\" name=\"TypeIHM\" value=\"1\">" ;

	// wallet/engine/frontline.cgi
	// walter
	$pagepaiementreceiveandpayplusieursfois =  "<form name=\"formulaire\" method=\"post\" action=\"$receiveandpayurl\">
	<input type=\"hidden\" name=\"MerchID\" value=\"$fianetsiteid\">
	<input type=\"hidden\" name=\"XMLInfo\" value=\"$xml\">
	<input type=\"hidden\" name=\"URLCall\" value=\"https://fusion.ma-cave-a-vin.fr/index.php\">
	<input type=\"hidden\" name=\"URLSys\" value=\"fusion.ma-cave-a-vin.fr/vad/macavavin/call_autoresponse3.php\">
	<input type=\"hidden\" name=\"CheckSum\" value=\"$BonCheckSum\">
	<input type=\"hidden\" name=\"XMLParam\" value=\"$XMLParam\">
	<input type=\"hidden\" name=\"TypeIHM\" value=\"2\">
	$TypeIHM
	<p align=\"center\"><img src=\"configurations/$host/images/templates/kwixo.png\"></p>
	<p>Vous allez être automatiquement redirigé vers Kwixo dans quelques secondes, si cela ne fonctionne pas, cliquez sur le bouton ci dessous</p>
	<p align=\"center\"><input type=\"submit\" value=\"Continuer\"></p>
	</form>
	<script language=\"javascript\">
	window.onload=function(){
		  document.forms.formulaire.submit();
	}
	</script>" ;
	}
?>