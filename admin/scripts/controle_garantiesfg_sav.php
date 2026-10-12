<?php

require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");

$mail =new email();
$mail->expediteur   = $template->translate("/site/[site_code]/mail_expediteur");
$mail->expname      = $template->translate("/site/nom");
if(ESPACE == 'prod') {
    $mail->destinataire = "contact@mon-espace-cuisson.com";
}
else {
    $mail->destinataire = "webmaster4@mon-espace-cuisson.com";
}
$mail->copymode     = "bcc";
$mail->to           = array("adv@mon-espace-cuisson.com","webmaster4@mon-espace-cuisson.com");
$mail->subject      = "Attention Contrat SFG sur commande à controler";
$body = "attention ces factures comportent des contrats SFG, à controler : ";


$garanties_sfg = $DB_site2->query("SELECT f.facturid FROM lignefacturegarantie lfg INNER JOIN lignefacture lf ON lf.lignefactureid = lfg.lignefactureid INNER JOIN facture f ON lf.factureid = f.facturid WHERE lfg.fournisseur = 'SFG' AND provenanceid != '' ORDER BY f.datefacture DESC");

foreach($garanties_sfg as $garantie_sfg) {
 $body .= "<br/> commande SAV N° ".$garantie_sfg['facturid'];
}

$mail->body = $body;
$mail->sendmail();
?>