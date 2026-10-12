<?php
require_once 'platinehome_connect.php';

//on déclare le contrat platine home

/*****  RECUPERATION DE LA MARQUE  *****/
$url = 'https://sigma.sfg.fr/api/brands/V1/search_brands';
//$url = 'https://sigma-preprod.sfg.fr/api/brands/V1/search_brands';

//The JSON data.
$jsonDataMarque = array(
    'search_string' => $lignefacture->marque,
    'password'      => $password
);
$jsonDataEncoded = json_encode($jsonDataMarque);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','accept: application/json','authorization: Bearer '.$access_token));
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response = curl_exec($ch);
$response = json_decode($response);

if(isset($response->success)){
	if(isset($response->data[0]->id)){
		$brand_id= $response->data[0]->id;
	}else{
		$brand_id = null;
		echo 'aucun resultat pour cette marque !';
	}
}else{
	echo 'ERREUR MARQUE';
}

curl_close($ch);
/****** FIN RECUPERATION MARQUE *******/

/****** RECUPERATION DES INFOS DU CLIENT ******/
$infos_ph = $DB_site2->query_first("SELECT * FROM platinehome WHERE phid='".$phid."'");
$country_code = $facture->diminutif;
//$date_subscription = date("Y-m-d",$facture->datelivraison);
$date_subscription = date("Y-m-d",time());
$dateAchat = date("Y-m-d",$facture->datecommande);
/***** FIN RECUPERATION DES INFOS DU CLIENT ***/

/**** DECLARATION DU CONTRAT ******/
//API Url envoi contrat
$url = 'https://sigma.sfg.fr/api/insurances/V1/declare_contract';
//$url = 'https://sigma-preprod.sfg.fr/api/insurances/V1/declare_contract';

$generate_mandate = 0;

//Initiate cURL. en 2eme temps avec le access_token
$ch = curl_init($url);

$customer = array(
	'last_name'        => $facture->nom,
	'first_name'       => $facture->prenom,
	'gender'           => $facture->civilite + 1,
	'email'      	   => $facture->mail,
	'phone'            => $infos_ph['telephone'],
	'city_name'        => $facture->ville,
	'city_postal_code' => $facture->codepostal,
	'country_code'     => $country_code,
	'date_of_birth'    => $infos_ph['naissance'],
	'address'          => $facture->adresse,
	'iban'  		   => $infos_ph['iban'],
	'bic'			   => $infos_ph['bic']		
);
$subscription = array(
	'offer_identifier' => $infos_ph['offre'],
	'identifier' 	   => $facture->facturid,
	//'identifier' 	   => $phid."-".$lignefacture->lignefactureid,
	'payment_schedule' => '1',
	'date'			   => $date_subscription, // DATE EFFET ?? QUELLE DATE DOIT ON METTRE ??
	'generate_mandate' => $generate_mandate		
);
$device = array(
	'purchase_date'    => $dateAchat,
	'purchase_price'   => $lignefacture->totalttc,
	'brand_id'		   => $brand_id,
	'category_id'      => $infos_ph['offre_categorie_id'],		
	'model_name'	   => $lignefacture->artcode

);
$device2 = array(
	'devices'    => $device

);

//The JSON data.
$jsonData = array(
    'customer' => $customer,
    'subscription' => $subscription,
    'devices' => $device2
);
$jsonDataEncoded = json_encode($jsonData);

curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','accept: application/json','authorization: Bearer '.$access_token)); 
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);

//Pour avoir le resultat du retour et pas seulement un boolean.
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec($ch);
$response = json_decode($response);

if(isset($response->success)){
	$lienmandat = $response->mandate_signature_link;
	$statusmandat = $response->mandate_state;
	$$num_contrat = $response->contract_number;
	$DB_site2->query("UPDATE platinehome SET numero_contrat='".$response->contract_number."',date_declaration='".date("Y-m-d", time())."', date_effet='".$date_subscription."' WHERE phid='".$phid."'");
}else{
	foreach($response->errors as $error){
		foreach ($error as $err) {
			var_dump($err);
		}
	}
	//echo 'Failed to Connect ==> <b>message :</b> '.$response->error->message. '; <b>status_code :</b> ' . $response->error->status_code;
}
curl_close($ch);

?>