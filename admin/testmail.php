<?php

include("email.php");

$host="www.ma-cave-a-vin.fr";

$htmlmess="<div style=\"font-family:arial;font-size:12px;\">
<p>Bonjour $client[prenom] $client[nom],</p>

<p>Nous faisons suite à votre visite sur notre site <a href=\"http://$host\">www.ma-cave-a-vin.fr</a> et vous remercions de l'intérêt que vous portez à nos produits.</p>";

/*<p>Vous trouverez ci-dessous l'offre de prix détaillée correspondant à votre demande, accompagnée de la fiche technique du produit.</p>

<p><a href=\"http://$host/devis.php?devisid=$facture[facturid]&p=$p\">Pour la visualiser, cliquez ici :  N° ".$facture[facturid]." du ".date("d/m/Y")."</a></p>
<p><a href=\"http://$host/devis.php?devisid=$facture[facturid]&p=$p&dl=1\">Pour la télécharger, cliquez ici :  N° ".$facture[facturid]." du ".date("d/m/Y")."</a></p>
*/

$htmlmess.="<p>Vous trouverez ci-joint l'offre de prix détaillée au format pdf correspondant à votre demande, accompagné de le fiche technique du produit. Si vous ne parvenez pas à lire le fichier en pièce jointe, <a href=\"http://$host/devis.php?devisid=$facture[facturid]&p=$p\">cliquez ici pour le télécharger</a></p>";

$htmlmess.="<p>Nous vous remercions de votre confiance.</p>
<img src=\"http://$host/configurations/www.ma-cave-a-vin.fr/images/templates/circle_animation.gif\" width=\"200\" border=\"0\">

<p>Notre service commercial prendra contact avec vous prochainement et reste à votre disposition pour toute autre question qui vous sera nécessaire.</p>

<p>A très bientôt,
<br>L'équipe de Ma Cave à Vin
<br>Veuillez passer votre commande en ligne, sur le site <a href=\"http://".$host."\">www.ma-cave-a-vin.fr</a> ou par téléphone au +33(0)4.26.03.05.43
<br><a href=\"http://".$host."\"><img src=\"http://".$host."/configurations/".$host."/images/templates/logo.jpg\" width=\"200\" border=\"0\"></a></p>
</div>";

$mail=new email();
$mail->destinataire="acilyon@wanadoo.fr";
$mail->destname="c c";
$mail->expediteur="info@ma-cave-a-vin.fr";
$mail->expname="Ma Cave à Vin";
$mail->to=array("mr.cc@free.fr");
$mail->copymode="bcc";
$mail->subject="test orange";
$mail->body=$htmlmess;
$mail->attachfile("http://www.ma-cave-a-vin.fr/devis.php?devisid=38219&p=8510604e12f472161bafcf7b36cc2b0c","test devis.pdf","application/pdf");
$mail->attachfile("http://www.ma-cave-a-vin.fr/facture2.php?passession=oui&facturid=38371&passe=fdjtahfk&langue=FR","test facture.pdf","application/pdf");

$ret=$mail->sendmail();

var_dump($ret);

?>