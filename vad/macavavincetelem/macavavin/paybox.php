<?php
require_once __DIR__.'/../../../secrets.php';


/* PAYBOX */

// Ennonciation de variables

$pbx_site = '1771904';
$pbx_rang = '01';
$pbx_identifiant = '726182171';

if(ESPACE=="preprod"){
    
    $serveurOK = 'preprod-tpeweb.paybox.com';
    $hmackey = PAYBOX_CETELEM_HMAC_KEY;

}else{
    
    $hmackey=PAYBOX_CETELEM_TEST_HMAC_KEY;
    
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

$pbx_source=$template->mobile?"XHTML":"HTML";


//Création de l'url cgi paybox
$serveurOKmobile = 'https://'.$serveurOK.'/cgi/ChoixPaiementMobile.cgi';
$serveurOK = 'https://'.$serveurOK.'/cgi/MYchoix_pagepaiement.cgi';



// echo $serveurOK;



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
<input type="hidden" name="PBX_LANGUE" value="$pbx_langue">
<input type="hidden" name="PBX_SOURCE" value="$pbx_source">
<input type="hidden" name="PBX_HMAC" value="$hmac">
<img src="/configurations/www.ma-cave-a-vin.fr/images/templates/cb.jpg">
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