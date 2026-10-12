<?php

include("global.php");
if($_SERVER['REQUEST_URI']!="/partenaires"){
    header("Status: 301 Moved Permanently");
    header("Location: /partenaires");
    exit(); 
}

$template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">Nos partenaires</span><meta property=\"position\" content=\"2\"></span>";

include("barrefixe.php");

$metatitle="Partenaires de Ma Cave à Vin, spécialiste en caves à vin, cigares, fromage et charcuterie";
$metadescription="Partenaires de Ma Cave à Vin, spécialiste en caves à vin, cigares, fromage et charcuterie. 1er site de vente de caves à vin, plus de 350 modèles pour particuliers et professionnels. Vieillissement, service, climatisation et rangement du vin. Des distributeurs de vin au verre, des vitrines à vin climatisées, des caves à charcuterie et des caves à fromage. Des produits garantis de 2 à 5 ans. Livraison spécialisée à votre domicile. 10 moyens de paiement et de financement 100% sécurisés.";
$template->pageparams($host,$metatitle,$metadescription);
$template->evaltemplate("CategPartenaire","main");

$template->out();

?>