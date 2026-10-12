<?php
require_once __DIR__.'/../../secrets.php';


/* PAYBOX */

// Ennonciation de variables

$pbx_site = '1771902';
$pbx_rang = '01';
$pbx_identifiant = '725932740';

if(ESPACE=="preprod"){
    
    $serveurOK = 'preprod-tpeweb.paybox.com';
    $hmackey = PAYBOX_HMAC_KEY;

}else{
    
    $hmackey=PAYBOX_TEST_HMAC_KEY;

    $serveurs = array('tpeweb.paybox.com','tpeweb1.paybox.com');
    $serveurOK = "";

    foreach($serveurs as $serveur){
        $doc = new DOMDocument();
        $doc->loadHTMLFile('https://'.$serveur.'/load.html');
        $server_status = "";
        $element = $doc->getElementById('server_status');
        if($element){
            $server_status = $element->textContent;
        }
        if($server_status == "OK"){
            // Le serveur est prêt et les services opérationnels
            $serveurOK = $serveur;
            break;
        }
        // else : La machine est disponible mais les services ne le sont pas.
    }
    //curl_close($ch); <== voir paybox
    if(!$serveurOK){
        die("Erreur : Aucun serveur n'a été trouvé");
    }
}

$pbx_cmd = $facture->facturid;
if(isset($reglement)) $pbx_cmd.="-".$reglement['reglement_id'];
$pbx_porteur = $facture->utilisateur->mail;
$pbx_total = round($totalmontant*100,0);

// Paramétrage des urls de redirection après paiement
$pbx_effectue = "https://".$_SERVER['HTTP_HOST']."/checkout_paiement.php";
$pbx_effectue = url_retour($pbx_effectue);
$pbx_annule = "https://".$_SERVER['HTTP_HOST']."/checkout_paiement.php";
$pbx_annule = url_retour($pbx_annule);
$pbx_refuse = "https://".$_SERVER['HTTP_HOST']."/checkout_paiement.php";
$pbx_refuse = url_retour($pbx_refuse);
// Paramétrage de l'url de retour back office site
$pbx_repondre_a = "https://".$_SERVER['HTTP_HOST']."/vad/macavavin/paybox_response.php";
$pbx_repondre_a = url_retour($pbx_repondre_a);
// Paramétrage du retour back office site
$pbx_retour = "auto:A;amount:M;facturid:R;trans:T;cbpays:Y;error:E;bin6:N;type:C;auth3d:F;gar3d:G";

//bidouilles url retour



$pbx_langues=array("FR"=>"FRA","EN"=>"GBR","ES"=>"ESP");
$langue=$facture->utilisateur->langue;
$pbx_langue=isset($pbx_langues[$langue]) ? $pbx_langues[$langue] : "FRA";

//l'adresse1 ne peut comporter que 50 caractères :
$adress = remove_accents(str_replace("'","&apos;",$facture->utilisateur->adresse));
if($adress !== '' && strlen($adress)>50){
    $adress1 = substr($adress,0,50);
    $adress2 = substr($adress,50,50);
}
elseif($adress1 !== ''){
    $adress1 = $adress;
    $adress2 = "";
}
/*
$pbx_billing='<?xml version="1.0" encoding="utf-8" ?><Billing><Address><FirstName>'.remove_accents($facture->utilisateur->prenom).'</FirstName><LastName>'.remove_accents($facture->utilisateur->nom).'</LastName><Address1>'.remove_accents(str_replace("'","&apos;",$facture->utilisateur->adresse)).'</Address1><ZipCode>'.$facture->utilisateur->codepostal.'</ZipCode><City>'.remove_accents(str_replace("'","&apos;",$facture->utilisateur->ville)).'</City><CountryCode>'.$facture->utilisateur->iso3166.'</CountryCode></Address></Billing>';
*/
$indicatifQuery = $DB_site->query_first("SELECT indicatif FROM pays WHERE paysid='" . $facture->utilisateur->paysid ."';");

$pbx_billing='<?xml version="1.0" encoding="utf-8" ?><Billing><Address><FirstName>'.remove_accents($facture->utilisateur->prenom).'</FirstName><LastName>'.remove_accents($facture->utilisateur->nom).'</LastName><Address1>'.$adress1.'</Address1><Address2>'.$adress2.'</Address2><ZipCode>'.$facture->utilisateur->codepostal.'</ZipCode><City>'.remove_accents(str_replace("'","&apos;",$facture->utilisateur->ville)).'</City><CountryCode>'.$facture->utilisateur->iso3166.'</CountryCode><MobilePhone>' . $facture->utilisateur->telephone . '</MobilePhone><CountryCodeMobilePhone>+' . $indicatifQuery[0] . '</CountryCodeMobilePhone></Address></Billing>';




$tt_qte=0;
$qte_art = $DB_site->query("SELECT qte FROM lignefacture WHERE factureid = '".$facture->facturid."' AND lignefacturedissid = 0");
while($qte=$DB_site->fetch_array($qte_art)){
    $tt_qte += $qte['qte'];
}
$qte_art = $DB_site->query("SELECT qte FROM lignefacturediss WHERE factureid = '".$facture->facturid."'");
while($qte=$DB_site->fetch_array($qte_art)){
    $tt_qte += $qte['qte'];
}
//// Cette donnée est restreinte à 2 caractères dans le protocole 3DSv2 et ne peut donc excéder la valeur 99. Dans le cas où le nombre d’articles est supérieure à 99, la donnée à indiquer sera égale à la valeur 99.
if($tt_qte > 99){
    $tt_qte = 99;
}
$pbx_shoppingcart='<?xml version="1.0" encoding="utf-8" ?><shoppingcart><total><totalQuantity>'.$tt_qte.'</totalQuantity></total></shoppingcart>';

$pbx_source=$template->mobile?"XHTML":"HTML";


//Création de l'url cgi paybox
$serveurOKmobile = 'https://'.$serveurOK.'/cgi/ChoixPaiementMobile.cgi';
$serveurOK = 'https://'.$serveurOK.'/cgi/MYchoix_pagepaiement.cgi';



// echo $serveurOK;

function remove_accents($string) {
    if ( !preg_match('/[\x80-\xff]/', $string) )
        return $string;

    $chars = array(
    // Decompositions for Latin-1 Supplement
    chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
    chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
    chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
    chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
    chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
    chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
    chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
    chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
    chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
    chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
    chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
    chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
    chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
    chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
    chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
    chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
    chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
    chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
    chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
    chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
    chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
    chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
    chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
    chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
    chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
    chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
    chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
    chr(195).chr(191) => 'y',
    // Decompositions for Latin Extended-A
    chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
    chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
    chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
    chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
    chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
    chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
    chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
    chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
    chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
    chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
    chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
    chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
    chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
    chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
    chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
    chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
    chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
    chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
    chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
    chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
    chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
    chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
    chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
    chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
    chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
    chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
    chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
    chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
    chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
    chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
    chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
    chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
    chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
    chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
    chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
    chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
    chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
    chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
    chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
    chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
    chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
    chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
    chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
    chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
    chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
    chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
    chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
    chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
    chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
    chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
    chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
    chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
    chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
    chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
    chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
    chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
    chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
    chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
    chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
    chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
    chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
    chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
    chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
    chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
    );

    $string = strtr($string, $chars);

    return $string;
}

// --------------- TRAITEMENT DES VARIABLES ---------------

// On récupère la date au format ISO-8601
$dateTime = date("c");

// On crée la chaîne à hacher sans URLencodage
$msg = "PBX_SITE=".$pbx_site.
"&PBX_RANG=".$pbx_rang.
"&PBX_IDENTIFIANT=".$pbx_identifiant.
"&PBX_TOTAL=".$pbx_total.
"&PBX_DEVISE=978".
"&PBX_CMD=".$pbx_cmd.
"&PBX_PORTEUR=".$pbx_porteur.
"&PBX_REPONDRE_A=".$pbx_repondre_a.
"&PBX_RETOUR=".$pbx_retour.
"&PBX_EFFECTUE=".$pbx_effectue.
"&PBX_ANNULE=".$pbx_annule.
"&PBX_REFUSE=".$pbx_refuse.
"&PBX_HASH=SHA512".
"&PBX_TIME=".$dateTime.
"&PBX_BILLING=".$pbx_billing.
"&PBX_SHOPPINGCART=".$pbx_shoppingcart.
"&PBX_LANGUE=".$pbx_langue.
"&PBX_SOURCE=".$pbx_source;
//         echo $msg;

// Si la clé est en ASCII, On la transforme en binaire
$binKey = pack("H*", $hmackey);

// On calcule lempreinte (à renseigner dans le paramètre PBX_HMAC) grâce à la fonction hash_hmac et //
// la clé binaire
// On envoi via la variable PBX_HASH l'algorithme de hachage qui a été utilisé (SHA512 dans ce cas)
// Pour afficher la liste des algorithmes disponibles sur votre environnement, décommentez la ligne //
// suivante
// print_r(hash_algos());
$hmac = strtoupper(hash_hmac('sha512', $msg, $binKey));

// La chaîne sera envoyée en majuscule, d'où l'utilisation de strtoupper()
// On crée le formulaire à envoyer
// ATTENTION : l'ordre des champs est extrêmement important, il doit
// correspondre exactement à l'ordre des champs dans la chaîne hachée

$pbx_shoppingcart = htmlspecialchars($pbx_shoppingcart);
$pbx_billing = htmlspecialchars($pbx_billing);

if($pbx_total <= 30){
    $pbx_souhaitauthent = "<input type=\"hidden\" name=\"PBX_SOUHAITAUTHENT\" value=\"02\">";
}
else{
    $pbx_souhaitauthent = "<input type=\"hidden\" name=\"PBX_SOUHAITAUTHENT\" value=\"01\">";
}
 
$pagepaiementcb=<<<HTML
<form method="POST" name="paybox" action="$serveurOK">
<input type="hidden" name="PBX_SITE" value="$pbx_site">
<input type="hidden" name="PBX_RANG" value="$pbx_rang">
<input type="hidden" name="PBX_IDENTIFIANT" value="$pbx_identifiant">
<input type="hidden" name="PBX_TOTAL" value="$pbx_total">
<input type="hidden" name="PBX_DEVISE" value="978">
<input type="hidden" name="PBX_CMD" value="$pbx_cmd">
<input type="hidden" name="PBX_PORTEUR" value="$pbx_porteur">
<input type="hidden" name="PBX_REPONDRE_A" value="$pbx_repondre_a">
<input type="hidden" name="PBX_RETOUR" value="$pbx_retour">
<input type="hidden" name="PBX_EFFECTUE" value="$pbx_effectue">
<input type="hidden" name="PBX_ANNULE" value="$pbx_annule">
<input type="hidden" name="PBX_REFUSE" value="$pbx_refuse">
<input type="hidden" name="PBX_HASH" value="SHA512">
<input type="hidden" name="PBX_TIME" value="$dateTime">
<input type="hidden" name="PBX_BILLING" value='$pbx_billing'>
<input type="hidden" name="PBX_SHOPPINGCART" value='$pbx_shoppingcart'>
<input type="hidden" name="PBX_LANGUE" value="$pbx_langue">
<input type="hidden" name="PBX_SOURCE" value="$pbx_source">
<input type="hidden" name="PBX_HMAC" value="$hmac">
<img src="/configurations/www.mon-espace-cuisson.com/images/templates/cb.jpg">
<br><input type="submit">
</form>
<script type="text/javascript">
	// console.log('width : '+window.innerWidth);
	if(window.innerWidth<768){
		document.forms.paybox.action="$serveurOKmobile";
	}
	// console.log('action :'+document.forms.paybox.action);
        document.forms.paybox.submit();
    
</script>
HTML;

function url_retour($url){
    if(strpos($url, 'dedie')){
        $url = str_replace(array("dediepro","dedie"), array("pro","www"), $url);
    }
    return $url;
}