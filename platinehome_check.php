<?php

require_once 'platinehome_connect.php';

$url = 'https://sigma.sfg.fr/api/insurances/V1/get_mandate_state ';

$jsonData = array(
    'contract_number'      => "C19011988"
);

$ch = curl_init($url);

//Encode the array into JSON.
$jsonDataEncoded = json_encode($jsonData);

//Tell cURL that we want to send a POST request.
curl_setopt($ch, CURLOPT_POST, 1);

//Attach our encoded JSON string to the POST fields.
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);

//Set the content type to application/json
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','accept: application/json','authorization: Bearer '.$access_token));

//Pour avoir le détail du resultat du retour et pas seulement un boolean.
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($ch, CURLOPT_TIMEOUT, 5);

//Execute the request
$response = curl_exec($ch);

$response = json_decode ( $response );

curl_close($ch);

?>