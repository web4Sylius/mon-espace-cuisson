<?php
include("../admin_global.php");
include("adv.class.php");
include("schenker.class.php");
include("../../class/facture.class.php");

header("Cache-Control: no-cache");
header("Content-type:text/xml");

print "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
print "<xml>";

$advid = $_POST['advid'];
$adv = new adv($DB_site2,$advid);


if($adv->destinataire == "DB Schenker Lyon" || $adv->destinataire == "DB Schenker France"){

	$schenker = new schenker($DB_site2,$adv);

	if(!$schenker->plan()){
		print "<error name=\"plan_trspt_DBS\" value=\"Adresse de livraison non trouvée pour ".$adv->destinataire." dans le plan de transport. Veuillez vérifier le code postal.\" />";
	}
}



print "</xml>";