<?php

require "admin_global.php";
if(ESPACE =!'prod'){
	include("/var/www/vhosts/ma-cave-a-vin.fr/fusion.ma-cave-a-vin.fr/fonctions.php");
}else{
	include("/var/www/vhosts/mon-espace-cuisson.com/httpdocs/fonctions.php");
}


if(!isset($_GET['facturid']) || !isset($_GET['montant'])) exit;

$facturid=$_GET['facturid'];
$montant=$_GET['montant'];
$mttfianet=$montant;

if($_GET['moyen']==1){
    $parm="merchant_id=045294738500039";
    $parm="$parm amount=".(100*$montant);
    $parm="$parm automatic_response_url=http://ns382043.ovh.net/vad/macavavin/call_autoresponse.php";
    $parm="$parm cancel_return_url=http://$host/admin/facture.php";
    $parm="$parm currency_code=978";
    $parm="$parm language=fr";
    $parm="$parm normal_return_url=http://$host/admin/facture.php";
    $parm="$parm payment_means=CB,1,VISA,1,MASTERCARD,1,AMEX,2";
    $parm="$parm transaction_id=$facturid";
    $parm="$parm merchant_country=fr";
    $parm="$parm logo_id=commercant.gif";
    $parm="$parm pathfile=/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/vad/macavavin/param/pathfile";

    $path_bin = "/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/vad/macavavin/bin/request";
    $result=exec("$path_bin $parm");
    $tableau = explode ("!", "$result");
    $code = $tableau[1];
    $error = $tableau[2];
    $message = $tableau[3];
    if (( $code == "" ) && ( $error == "" ) ){
      $pagepaiementcb= "<CENTER>erreur appel request</CENTER><BR>";
      $pagepaiementcb.= "executable request non trouve $path_bin<br>";
      $pagepaiementcb.= "Result : $result";
    }
    else if ($code != 0){
      $pagepaiementcb= "<center><b><h2>Erreur appel API de paiement.</h2></center></b>";
      $pagepaiementcb.= "<br><br><br>";
      $pagepaiementcb.= " message erreur : $error (code : $code)";
    }
    else {
      $pagepaiementcb= $message;
    }

    print "Effectuer le paiement par CB";
    print $pagepaiementcb;
}


if($_GET['moyen']==8 || $_GET['moyen']==9){

    // receive and pay
	$DB_site2->query("UPDATE facture SET moyenid=".$_GET['moyen']." WHERE facturid=$facturid");

	$infosxml=$DB_site2->query_first("select u.mail umail, u.civilite ucivilite, u.nom unom, u.prenom uprenom, u.codepostal ucodepostal, 
	u.adresse uadresse, u.ville uville, u.telephone utelephone, u.raisonsociale uraisonsociale, p1.libelle upays, u.portable uportable, f.facturid, 
	f.prixport, f.tvaport, f.civilite, f.nom, f.prenom, f.codepostal, f.adresse, f.ville, f.telephone, f.raisonsociale, p2.libelle, f.mail, 
	f.montantremise, f.montantcadeau, f.ip, f.timestamp from facture f inner join utilisateur u on (f.userid = u.userid), pays p1, pays p2 
	WHERE f.facturid = '$facturid' and p1.paysid = u.paysid and p2.paysid = f.paysid");
	$xml = "<?xml version='1.0' encoding='ISO-8859-1'?>" ;
	$xml .= "<control>" ;
	$xml .= "<utilisateur type='facturation' qualite='2'>" ;
	switch ($infosxml[ucivilite]){
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

	if ($infosxml[upays]=="France métropolitaine (hors Corse)")
		$infosxml[upays]="France métropolitaine";
		
	if ($infosxml[libelle]=="France métropolitaine (hors Corse)")
		$infosxml[libelle]="France métropolitaine";	
		
		
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
	switch ($infosxml[civilite]){
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
	$xml .= "<refid>$facturid</refid>" ;
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
	$lignesfactures=$DB_site2->query("select artcode, qte, libelle, artid from lignefacture WHERE factureid = '$facturid'");
	$nb=$DB_site2->num_rows($lignesfactures);
	$transporteur = "MORY";
	if ($nb == 1)
		{
		$article = $DB_site2->query_first("SELECT sowine FROM article a INNER JOIN lignefacture lf ON (lf.artid = a.artid) WHERE lf.factureid = '$facturid'");
		if ($article[sowine] == '1')
			$transporteur = "FEDEX Express";
		}
	$xml .= "<nom>$transporteur</nom>" ;
	$xml .= "</transport>" ;
	$xml .= "<list nbproduit='".$nb."'>" ;
	$lstartid=array();
	while ($lignefacture=$DB_site2->fetch_array($lignesfactures))
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
	
	include("/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/vad/macavavin/fianet_key_64bits.php");

	$datecom = date("Y-m-d H:i:s");
// 	$datelivr = date("Y-m-d",datelivraison($lstartid)) ;

	$Sample = new EncodingKey();
	$key = $Sample->giveHashCode2("f07dbc36eabcbc55de7df04adda5e5fd", substr($datecom, -2, 2), "$infosxml[mail]", "$facturid", "$mttfianet", "$infosxml[nom]");
	
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

	if ($mttfianet > 6000)
		$pagepaiementreceiveandpay =  "Ce moyen de paiement n'est pas disponible pour des commandes supérieures à 6000 euros." ;
	else{
		$pagepaiementreceiveandpay =  "<form name=\"formulaire\" method=\"post\" action=\"$receiveandpayurl\">
		<input type=\"hidden\" name=\"MerchID\" value=\"$fianetsiteid\">
		<input type=\"hidden\" name=\"XMLInfo\" value=\"$xml\">
		<input type=\"hidden\" name=\"URLCall\" value=\"http://$host/index.php\">
		<input type=\"hidden\" name=\"URLSys\" value=\"$host/vad/macavavin/call_autoresponse3.php\">
		<input type=\"hidden\" name=\"CheckSum\" value=\"$BonCheckSum\">
		<input type=\"hidden\" name=\"XMLParam\" value=\"$XMLParam\">
		<input type=\"hidden\" name=\"TypeIHM\" value=\"1\">
		Vous allez être automatiuement redirigé vers Kwixo dans quelques secondes, si cela ne fonctionne pas, cliquez sur le bouton ci dessous<br>
		<input type=\"submit\" value=\"Continuer\">
		</form>
		<script language=\"javascript\">
		window.onload=function(){
 		  document.forms.formulaire.submit();
		}
		</script>" ;
	}

	print "Paiement par Kwixo";
	print $pagepaiementreceiveandpay;
}

?>