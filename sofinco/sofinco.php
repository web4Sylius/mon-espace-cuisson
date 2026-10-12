<?php
require_once __DIR__.'/../secrets.php';
$providerId="99102122284";
$equipmentCode="290";
//$clientID = SOFINCO_PREPROD_CLIENT_ID;//PREPROD
//$secretID = SOFINCO_PREPROD_SECRET_ID;//PREPROD
$clientID = SOFINCO_CLIENT_ID;//PROD
$secretID = SOFINCO_SECRET_ID;//PROD

//$gateway_url="https://rct-api.sofinco.fr";
$gateway_url="https://api.sofinco.fr";
$token_url = $gateway_url."/token";
$client_id = $clientID;
$client_secret = $secretID; 
$error = false;
$exchangeUrl = 'https://www.mon-espace-cuisson.com/sofinco/retourSofinco.php';
$retourUrl = 'https://www.mon-espace-cuisson.com/sofinco/retourSofinco.php';
//Récupération des informations sur le client et la commande
$sql = "SELECT u.mail umail, u.civilite ucivilite, u.nom unom, u.prenom uprenom, u.codepostal ucodepostal, u.adresse uadresse, u.ville uville, u.telephone utelephone, u.raisonsociale uraisonsociale, p1.libelle upays, p1.paysid AS upaysid, p1.diminutif AS ucodepays, u.portable uportable, f.facturid, f.prixport, f.tvaport, f.civilite, f.nom, f.prenom, f.codepostal, f.adresse, f.ville, f.telephone, f.raisonsociale, p2.libelle, p2.paysid AS paysid, f.mail, f.montantremise, f.montantcadeau, f.ip, f.timestamp";
$sql .= " FROM pays p1, pays p2, facture f";
$sql .= " INNER JOIN utilisateur u on (f.userid = u.userid)";
$sql .= " WHERE p1.paysid = u.paysid";
$sql .= " AND p2.paysid = f.paysid";
$sql .= " AND f.facturid = '".$factureid."'";
$infosxml = $DB_site->query_first($sql);

$montant_sofinco = $facture->totalttc * 100 ; //montant total en centimes

$transaction_id = 'CV'.date('hisdmY');
$sql = "UPDATE facture";
$sql .= " SET transaction_id = '".$transaction_id."'";
$sql .= " WHERE facturid = ".$factureid;
$DB_site->query($sql);

/*$params = array(
     'q6'   => $sofinco_site_id //Identifiant unique
    ,'p0'   => 'PANIER' //Fonction du module crédit
    ,'ti'   => $facture->facturid//N° commande client
    ,'s3'   => $montant_sofinco //Montant de le commande
    ,'i4'   => $infosxml['ucivilite']+1 //Civilité
    ,'i1'   => substr($infosxml['unom'],0,20) //Nom
    ,'i2'   => substr($infosxml['uprenom'],0,20) //Prénom
    ,'a1'   => substr(str_to_noaccent($infosxml['uadresse']),0,32) //Adresse
    ,'a3'   => substr($infosxml['ucodepostal'],0,5) //Code postal
    ,'a5'   => substr($infosxml['uville'],0,32) //Ville
    ,'a9'   => substr($infosxml['umail'],0,64) //Email
    ,'a6'   => substr($infosxml['utelephone'],0,16) //Téléphone
    ,'p5'   => 'https://'.$_SERVER['HTTP_HOST'].'/sofinco/call_autoresponse.php' //URL de retour
    ,'uret' => 'https://'.$_SERVER['HTTP_HOST'].'/checkout_paiement.php?facturid='.$facture->facturid.'&transactionid='.$transaction_id //URL de retour
);*/
$send['providerContext']= array(
     "businessProviderId" => $providerId,
     "returnUrl"          =>$retourUrl,
     "exchangeUrl"        => $exchangeUrl
         
);
$send['customerContext']= array(
   "externalCustomerId"  => $facture->userid,
    "civilityCode"       => (intval($infosxml['civilite'])+1),
    "firstName"          => $infosxml['uprenom'],
    "lastName"           => $infosxml['unom'],
    "birthName"          => "",
    "street"             => $infosxml['uadresse'],
    "additionalStreet"   => "",
    "city"               => $infosxml['uville'],
    "zipCode"            => $infosxml['ucodepostal'],
    "countryCode"        => $infosxml['ucodepays'],
    "phoneNumber"        => $infosxml['utelephone'],
    "mobileNumber"       => $infosxml['uportable'],
    "emailAddress"       => $infosxml['umail']   
);
$send['coBorrowerContext'] =array(
   "externalCustomerId" => "",
    "civilityCode" => ""
);
$send['offerContext'] =array(
     "orderId"           => $facture->facturid,
     "equipmentCode"     => $equipmentCode,
     "amount"            => $montant_sofinco,
     "orderAmount"       => $montant_sofinco
);

$token = getAccessToken();
if($token != '' && !is_null($token)){
     $context = contextTransfert($token, $send);
     if(!isset($context->code)){
          $sofinco_url = $sofinco_url."&token=".$context->token;

     }else{
          $error = true;
     }
}else{
     $error = true; 
}


if($error){
     $sofinco_url = $host;
     $pagepaiementsofinco .= ""
     . "<p align=\"center\" style=\"margin:0; padding: 0;\"><img src=\"https://".$_SERVER['HTTP_HOST']."/configurations/www.mon-espace-cuisson.com/images/templates/sofinco_partner.jpg\" style=\"width:260px;\"></p>"
     ."<p align=\"center\" style=\"margin:0; padding: 0;\">Une erreur a été rencontrée, vous allez être redirigé vers l'accueil. "
     . "<p align=\"center\" style=\"margin:0; padding: 0;\"><input type=\"submit\" value=\"Retour\"></p>
</div>
<script language=\"javascript\">
    setTimeout(function(){
        document.location.href='https://".$_SERVER['HTTP_HOST']."';
    },2000);
</script>" ; 
}else{

     $pagepaiementsofinco =  "<div class=\"frame\">";

     $pagepaiementsofinco .= ""
          . "<p align=\"center\" style=\"margin:0; padding: 0;\"><img src=\"http://".$_SERVER['HTTP_HOST']."/configurations/www.mon-espace-cuisson.com/images/templates/sofinco_partner.jpg\" style=\"width:260px;\"></p>"
          . "<p align=\"center\" style=\"margin:0; padding: 0;\">Vous allez être automatiquement redirigé vers le site de Sofinco dans quelques secondes.<br><br> Si cela ne fonctionne pas, cliquez sur le bouton ci dessous<br><br></p>"
          . "<p align=\"center\" style=\"margin:0; padding: 0;\"><input type=\"submit\" value=\"Continuer\"></p>
     </div>
     <script language=\"javascript\">
         setTimeout(function(){
             document.location.href='".$sofinco_url."';
         },2000);
     </script>" ;

}



function getAccessToken() {
     global $token_url, $client_id, $client_secret;

     $content = "grant_type=client_credentials";
     $authorization = base64_encode("$client_id:$client_secret");
     $header = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");

     $curl = curl_init();
     curl_setopt_array($curl, array(
          CURLOPT_URL => $token_url,
          CURLOPT_HTTPHEADER => $header,
          CURLOPT_SSL_VERIFYPEER => false,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_POST => true,
          CURLOPT_POSTFIELDS => $content
     ));
     $response = curl_exec($curl);
     //var_dump(curl_getinfo($curl));
     curl_close($curl);

     return json_decode($response)->access_token;
}

function contextTransfert($token, $send){
     global $providerId,$client_id, $client_secret;
     $url ="https://api.sofinco.fr/BusinessDataTransfer/V1/businessDataTransferTokens/";
     $header = array("Authorization: Bearer {$token}", "Content-Type: application/json");

    $businessContext = addslashes(json_encode($send, JSON_UNESCAPED_SLASHES));
    $tokenFormat = "OPAQUE";
    $associationKEY = $token;
    $tokenDuration='30';

    $data = "{\"tokenFormat\":\"$tokenFormat\",\"businessContext\":\"$businessContext\",\"tokenDuration\":\"$tokenDuration\"}";
     $curl = curl_init();
     curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_HTTPHEADER => $header,
          CURLOPT_SSL_VERIFYPEER => false,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_POST => true,
          CURLOPT_POSTFIELDS => $data
     ));

     $response = curl_exec($curl);
     curl_close($curl);

     return json_decode($response);
}