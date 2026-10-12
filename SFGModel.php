<?php
require_once __DIR__.'/secrets.php';

/***** DEBUT CONNEXION ****/
//API Url connexion pour pouvoir utiliser les web services, on recupère le access_token
$url = 'https://sigma-preprod.sfg.fr/api/login';
$username = PLATINEHOME_PREPROD_USER;
$password = PLATINEHOME_PREPROD_PASSWORD;
$access_token = null;
//Initiate cURL.
$ch = curl_init($url);

//The JSON data.
$jsonData = array(
    'username' => $username,
    'password' => $password
);

//Encode the array into JSON.
$jsonDataEncoded = json_encode($jsonData);

//Tell cURL that we want to send a POST request.
curl_setopt($ch, CURLOPT_POST, 1);

//Attach our encoded JSON string to the POST fields.
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);

//Set the content type to application/json
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','accept: application/json')); 

//Pour avoir le détail du resultat du retour et pas seulement un boolean.
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

//Execute the request
$response = curl_exec($ch);

$response = json_decode ( $response );

if(isset($response->success)){

	$access_token = $response->access_token;

	echo "le token est : ".$access_token."<br>youhou!!!";
}else{
	echo 'Failed to Connect ==> <b>message :</b> '.$response->error->message. '; <b>status_code :</b> ' . $response->error->status_code;
	exit;
}

curl_close($ch);
/***** FIN CONNEXION *****/

//sur la page cde225.php on recupère la bonne offre pour l'afficher

/******* RECUPERATION DES OFFRES ET ID CATEGORIE DE LA CATEGORIE DU PRODUIT ********/
$url = 'https://sigma-preprod.sfg.fr/api/insurances/V1/get_offers';
$jsonDataCateg = array(
    'password'      => $password
);
$jsonDataEncoded = json_encode($jsonDataCateg);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','accept: application/json','authorization: Bearer '.$access_token));
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec($ch);
$response = json_decode($response);
$prixArticle = 1399;
$ok = true;
$categProduit = "Cave à vin";
$offer = array();
foreach ($response->data as $offre) {
	if($offre->max_devices == 1){ // pour eviter de tomber sur l'offre Platine Kitchen qui ne nous concerne pas.
		if($prixArticle <= $offre->device_price_max && $ok ){ //pour tomber sur l'offre qui correspond au prix de l'article
			$offer_id = $offre->identifier;
			$offer_price = $offre->offer_price;
			$offer_category_id = $offre->allowed_devices_categories;
			//allowed_devices_categories est un tableau d'objets contenant les infos de toutes les categ assurées par cette offre 
			foreach($offre->allowed_devices_categories as $categorie){
				if($categorie->name == $categProduit){
					$offer_category_id = $categorie->id;
				}
			}
			$ok = false;
		}
	}
}
curl_close($ch);
/****** RECUPERATION DES OFFRES ET ID CATEGORIE DE LA CATEGORIE DU PRODUIT *****/


//on déclare le contrat


/*****  RECUPERATION DE LA MARQUE  *****/
$url = 'https://sigma-preprod.sfg.fr/api/brands/V1/search_brands';
$marque = $marqueLibelle;
//The JSON data.
$jsonDataMarque = array(
    'search_string' => $marque,
    'password'      => $password
);
$jsonDataEncoded = json_encode($jsonDataMarque);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','accept: application/json','authorization: Bearer '.$access_token));
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

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

/**** DECLARATION DU CONTRAT ******/
//API Url envoi contrat
$url = 'https://sigma-preprod.sfg.fr/api/insurances/V1/declare_contract';

//Initiate cURL. en 2eme temps avec le access_token
$ch = curl_init($url);

$customer = array(
	'last_name'        => $last_name,
	'first_name'       => $first_name,
	'gender'           => $civ,
	'email'      	   => $email,
	'phone'            => $telephone,
	'city_name'        => $ville,
	'city_postal_code' => $cp,
	'country_code'     => $country_code,
	'date_of_birth'    => $naissance,
	'address'          => $address,
	'iban'  		   => $iban,
	'bic'			   => $bic		
);
$subscription = array(
	'offer_identifier' => $offer_identifier,
	'identifier' 	   => $factureid,
	'payment_schedule' => '1',
	'date'			   => $date_subscription, // DATE EFFET ?? QUELLE DATE DOIT ON METTRE ??
	'generate_mandate' => $generate_mandate		
);
$device = array(
	'purchase_date'    => $dateAchat,
	'purchase_price'   => $prixArticle,
	'brand_id'		   => $brand_id,
	'category_id'      => $category_id,		
	'model_name'	   => $artcode

);

//The JSON data.
$jsonData = array(
    'customer' => $customer,
    'subscription' => $subscription,
    'device' => $device
);
$jsonDataEncoded = json_encode($jsonData);

curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','accept: application/json','authorization: Bearer '.$access_token)); 
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);

//Pour avoir le resultat du retour et pas seulement un boolean.
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec($ch);
$response = json_decode($response);

if(isset($response->success)){

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