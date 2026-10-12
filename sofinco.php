<?php
require_once __DIR__.'/secrets.php';

$status = array(
	"ATT"=>"Attente de financement",
	"AFI"=>"Attente de financement",
	"ETU"=>"Dossier en étude",
	"CLI"=>"Attente retour client",
	"REF"=>"Refus",
	"ANN"=>"Annulé ou sans suite",
	"FIN"=>"Financé",
	"NFD"=>"non trouvé",
	"ERR"=>"Non traité - erreur technique"
	);

$code_erreurs = array(
	0=>"OK",
	"100"=>"Warning : Le traitement demandé n'a pas été effectué complètement",
	"5001"=>"Erreur service métier",
	"5002"=>"Erreur service",
	"5004"=>"Erreur de chargement du flux XML en entrée",
	"5005"=>"Nœud Service absent",
	"5006"=>"Service inconnu",
	"5010"=>"StartDate absent",
	"5011"=>"StartDate pas au format YYYYMMDD",
	"5012"=>"EndDate absent",
	"5013"=>"EndDate pas au format YYYYMMDD",
	"5020"=>"Flux Xml mal formé",
	"5021"=>"100 dossiers en entree"
	);

$clientID = SOFINCO_PREPROD_CLIENT_ID;
$clientID64 = SOFINCO_PREPROD_CLIENT_ID64;
$secretID = SOFINCO_PREPROD_SECRET_ID;
$secretID64 = SOFINCO_PREPROD_SECRET_ID64;
$gateway_url="https://rct-api.sofinco.fr";
$providerId="99102122272";
$equipmentCode="290";

$token_url = $gateway_url."/token";
$client_id = $clientID;
$client_secret = $secretID;

$token = getAccessToken();

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
	var_dump(curl_getinfo($response));
	die;
	curl_close($curl);

	return json_decode($response)->access_token;
}

function contextTransfert($token){
	global $providerId,$client_id, $client_secret;
	$url ="https://rct-api.sofinco.fr/BusinessDataTransfer/V1/businessDataTransferTokens/";
	$header = array("Authorization: Bearer {$token}", "Content-Type: application/json");

	$send['providerContext'] =array(
        "businessProviderId" => $providerId,
         "returnUrl" => "www.mon-espace-cuisson.com"
    );
    $send['customerContext']= array(
        "externalCustomerId" => "37269",
         "civilityCode" => "1",
         "firstName"	=> "ariel",
         "lastName"		=> "benhamou",
         "birthName"	=> "111111",
         "street"		=> "fdvfgf",
         "additionalStreet"=>"rgrg",
         "city" 		=>"grvrg",
         "zipCode"		=>"23000",
         "distributerOffice"=>"gtgtrgrtg",
         "countryCode"	=>"F"	
    );
    $send['coBorrowerContext'] =array(
        "externalCustomerId" => "",
         "civilityCode" => ""
    ) ;
    $send['offerContext'] =array(
    	"orderId"=>""
    ) ;
    $businessContext = addslashes(json_encode($send));
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

?>