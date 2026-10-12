<?php


require_once 'platinehome_connect.php';
// RECUPERATION DES OFFRES ET ID CATEGORIE DE LA CATEGORIE DU PRODUIT
$url = 'https://sigma.sfg.fr/api/insurances/V1/get_offers';
$jsonDataCateg = array(
    'password'      => $password
);
$categProduit = "Cave à vin";
$jsonDataEncoded = json_encode($jsonDataCateg);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','accept: application/json','authorization: Bearer '.$access_token));
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response = curl_exec($ch);
$response = json_decode($response);
$offer_id = $offer_price = $offer_category_id = "";
$offer = array();
$oldPrice = "";
if(!$error){
	foreach ($response->data as $offre) {
		if($offre->max_devices == 1){ // pour eviter de tomber sur l'offre Platine Kitchen qui ne nous concerne pas.
			if($oldPrice != '' && $oldPrice <= $offre->device_price_max) break;
			if($prixArticle <= $offre->device_price_max ){ //pour tomber sur l'offre qui correspond au prix de l'article
				$offer_id = $offre->identifier;
				$offer_price = $offre->offer_price;
				$offer_category_id = $offre->allowed_devices_categories;
				//allowed_devices_categories est un tableau d'objets contenant les infos de toutes les categ assurées par cette offre 
				foreach($offre->allowed_devices_categories as $categorie){
					if($categorie->name == $categProduit){
						$offer_category_id = $categorie->id;
					}
				}
				$oldPrice = $offre->device_price_max;
			}
		}
	}
}
curl_close($ch);
// RECUPERATION DES OFFRES ET ID CATEGORIE DE LA CATEGORIE DU PRODUIT


?>