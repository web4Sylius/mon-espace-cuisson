<?php
require_once __DIR__.'/secrets.php';
/***** DEBUT CONNEXION ****/
//API Url connexion pour pouvoir utiliser les web services, on recupère le access_token
$url = 'https://sigma.sfg.fr/api/login';
//$url = 'https://sigma-preprod.sfg.fr/api/login';
$username = PLATINEHOME_USER;
//$username = PLATINEHOME_PREPROD_USER; //preprod
$password = PLATINEHOME_PASSWORD;
//$password = PLATINEHOME_PREPROD_PASSWORD; //preprod
$access_token = null;
//Initiate cURL.
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);

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

curl_setopt($ch, CURLOPT_TIMEOUT, 5);

//Execute the request
$response = curl_exec($ch);

$response = json_decode ( $response );

if(isset($response->success)){

	$access_token = $response->access_token;
	$error = false;
}else{
	//echo 'Failed to Connect ==> <b>message :</b> '.$response->error->message. '; <b>status_code :</b> ' . $response->error->status_code;
	$error = true;
}

curl_close($ch);
/***** FIN CONNEXION *****/
?>