<?php
require_once __DIR__.'/../secrets.php';

function guideCommunes($soapclient, $codePostal) {

	echo '<b>Test du guide des communes</b><br/>=> Communes du code postal '.$codePostal.' :<br/>' ;

	// Appel "direct" du guide des communes
	$communes = $soapclient->citiesGuide(array('zipCode' => $codePostal));
	$nb = count($communes->City);
	if ($nb > 1) {
		// Cas o? une liste de communes est renvoy?e
		foreach ($communes->City as $City) {
			echo ' - '.($City->name).'<br/>';
		}
		echo '<br/>';
	}
	else if ($nb == 1) {
		// Cas o? une seule commune est renvoy?e
		$City = $communes->City;
		echo ' - '.($City->name).'<br/><br/>';
	}
	else {
		// Cas o? aucune commune n'est renvoy?e
		echo '<div style="color:red;">Ce code postal ne correspond ? aucune commune !</div><br/><br/>';
	}

}


function relaisColis($soapclient, $codePostal, $commune) {

	echo '<b>Test des relais colis</b><br/>=> Relais de '.$codePostal.' - '.$commune.'<br/>' ;

	// Appel "direct" aux relais colis
	$liste_relais = $soapclient->dropOffPoints(array('zipCode' => $codePostal, 'city' => $commune));
	$nb = count($liste_relais->DropOffPoint);
	if ($nb > 1) {
		// Cas o? une liste de relais est renvoy?e
		foreach ($liste_relais->DropOffPoint as $Relais) {
			echo ' - '.($Relais->name).' / '.($Relais->xETTCode).' / '.utf8_decode($Relais->openingHours->monday->am).'<br/>';
		}
		echo '<br/>';
	}
	else if ($nb == 1) {
		// Cas o? un seul relais est renvoy?
		$Relais = $liste_relais->DropOffPoint;
		echo ' - '.($Relais->name).' / '.($Relais->xETTCode).' / '.utf8_decode($Relais->openingHours->monday->am).'<br/><br/>';
	}
	else {
		// Cas o? aucune relais n'est renvoy?
		echo '<div style="color:red;">Aucun relais colis pour ce couple code postal/commune !</div><br/><br/>';
	}
}

function suiviColis($soapclient, $bonTransport) {

	echo '<b>Test du suivi colis</b><br/>=> Tracking de '.$bonTransport.'<br/>' ;

	// Appel "direct" au suivi colis
	$reponse = $soapclient->trackingByConsignment(array('parcelNumber' => $bonTransport));
	
	if (!$reponse->Parcel) {
		echo '<div style="color:red;">Colis inexistant</div><br/>';
		return;
	}
	
	$colis = $reponse->Parcel;
	$expediteur = $colis->sender;
	$destinataire = $colis->receiver;
	$evenements = $colis->events;
	
	// Attention : les diff?rents ?v?nements peuvent ne pas ?tre renseign?s selon l'?tat courant de la livraison
	$requestDate = new DateTime($evenements->requestDate);
	$processDate = new DateTime($evenements->processDate);
	$arrivalDate = new DateTime($evenements->arrivalDate);
	$deliveryDepartureDate = new DateTime($evenements->deliveryDepartureDate);
	$deliveryDate = new DateTime($evenements->deliveryDate);

	echo'<style type="text/css">
			.apptable {font-size: 10pt;  border: 1px solid #000000; margin-bottom: 10px;}
			.section  {border-bottom: 1px solid #000000;}
		</style>
		<table width="600px" cellspacing="0" cellpadding="0" class="apptable">
			<tbody>
				<tr>
					<td height="25" width="10px" class="section">&nbsp;</td>
					<td colspan="5" class="section"><b>D&#233;tails du colis</b></td>
				</tr>
				<tr><td colspan="6" height="2px"></td></tr>
				<tr>
					<td width="10px">&nbsp;</td>
					<td width="125px"><b>Bon de transport</b></td>
					<td width="152px">'.$colis->consignmentNumber.'</td>
					<td width="130px"><b>Statut colis</b></td>
					<td width="173px">'.utf8_decode($colis->shortStatus).'</td>
					<td width="10px">&nbsp;</td>
				</tr>
				<tr><td colspan="6" height="1px"></td></tr>
				<tr>
					<td width="10px">&nbsp;</td>
					<td width="125px"><b>Compte client</b></td>
					<td width="152px">'.$colis->accountNumber.'</td>
					<td width="130px"><b>Service de livraison</b></td>
					<td width="173px">'.$colis->service.'</td>
					<td width="10px">&nbsp;</td>
				</tr>
				<tr><td colspan="6" height="1px"></td></tr>
				<tr>
					<td width="10px">&nbsp;</td>
					<td width="125px"><b>R&#233;f&#233;rence</b></td>
					<td width="152px">'.$colis->reference.'</td>
					<td width="130px"><b>Poids</b></td>
					<td width="173px">'.number_format($colis->weight, 1, ',', ' ').'&nbsp;kg</td>
					<td width="10px">&nbsp;</td>
				</tr>
				<tr><td colspan="6" height="2px"></td></tr>
			</tbody>
		</table>
		<table width="600px" cellspacing="0" cellpadding="0" class="apptable">
			<tbody>
				<tr>
					<td width="10px" class="section" height="25px">&nbsp;</td>
					<td width="273px" class="section"><b>Informations exp&#233;diteur</b></td>
					<td width="4px" class="section">&nbsp;</td>
					<td width="313px" class="section"><b>Informations destinataire</b></td>
				</tr>
				<tr><td colspan="4" height="6px"></td></tr>
				<tr>
					<td></td>
					<td><b>'.$expediteur->name.'</b></td>
					<td></td>
					<td><b>'.$destinataire->name.'</b></td>
				</tr>
				<tr><td colspan="4" height="4px"></td></tr>
				<tr>
					<td></td>
					<td>'.$expediteur->address1.'</td>
					<td></td>
					<td>'.$destinataire->address1.'</td>
				</tr>
				<tr>
					<td></td>
					<td>'.$expediteur->address2.'</td>
					<td></td>
					<td>'.$destinataire->address2.'</td>
				</tr>
				<tr><td colspan="4" height="2px"></td></tr>
				<tr>
					<td></td>
					<td>'.$expediteur->zipCode.' '.$expediteur->city.'</td>
					<td></td>
					<td>'.$destinataire->zipCode.' '.$destinataire->city.'</td>
				</tr>
				<tr><td colspan="4" height="10px"></td></tr>
			</tbody>
		</table>
		<table width="600px" cellspacing="0" cellpadding="0" class="apptable">
			<tbody>
				<tr>
					<td class="section" width="10px" height="25px">&nbsp;</td>
					<td class="section" width="277px"><b>Ev&#233;nements</b></td>
					<td class="section" width="60px" style="text-align:left;"><b>Date</b></td>
					<td class="section" width="40px" style="text-align:right;"><b>Heure</b></td>
					<td class="section" width="213px" style="text-align:center;"><b>Nom du centre</b></td>
				</tr>
				<tr><td colspan="5" height="6px"></td></tr>';
	if ($evenements->requestDate) echo'
				<tr>
					<td></td>
					<td>Colis en pr&#233;paration</td>
					<td style="text-align:left;">'.$requestDate->format('d/m/Y').'</td>
					<td style="text-align:right;"></td>
					<td style="text-align:center;"></td>
				</tr>';
	if ($evenements->processDate) echo'
				<tr>
					<td></td>
					<td>Colis en cours d\'acheminement</td>
					<td style="text-align:left;">'.$processDate->format('d/m/Y').'</td>
					<td style="text-align:right;">'.$processDate->format('H:i').'</td>
					<td style="text-align:center;">'.$evenements->processCenter.'</td>
				</tr>';
	if ($evenements->arrivalDepartureDate) echo'
				<tr>
					<td></td>
					<td>Arriv&#233;e au centre de distribution</td>
					<td style="text-align:left;">'.$arrivalDate->format('d/m/Y').'</td>
					<td style="text-align:right;">'.$arrivalDate->format('H:i').'</td>
					<td style="text-align:center;">'.$evenements->arrivalCenter.'</td>
				</tr>';
	if ($evenements->deliveryDepartureDate) echo'
				<tr>
					<td></td>
					<td>D&#233;part en livraison</td>
					<td style="text-align:left;">'.$deliveryDepartureDate->format('d/m/Y').'</td>
					<td style="text-align:right;">'.$deliveryDepartureDate->format('H:i').'</td>
					<td style="text-align:center;">'.$evenements->deliveryDepartureCenter.'</td>
				</tr>';
	if ($evenements->deliveryDate) echo'
				<tr>
					<td></td>
					<td>Pr&#233;sentation du chauffeur</td>
					<td style="text-align:left;">'.$deliveryDate->format('d/m/Y').'</td>
					<td style="text-align:right;">'.$deliveryDate->format('H:i').'</td>
					<td style="text-align:center;"></td>
				</tr>';
	echo'
				<tr><td colspan="5" height="15px"></td></tr>
				<tr>
					<td style="height:65px;"></td>
					<td style="vertical-align:top;"><b>Etat de la livraison</b></td>
					<td style="vertical-align:top; text-align:justify;" colspan="3"><b>';

	$nb = count($colis->longStatus);
	if ($nb > 1) {
		// Cas o? plusieurs lignes d'info sont retourn?es
		foreach ($colis->longStatus as $Ligne) {
			echo utf8_decode($Ligne).'<br/>';
		}
	}
	else if ($nb == 1) {
		// Cas o? une seule ligne d'info est retourn?e
		$Ligne = $colis->longStatus;
		echo utf8_decode($Ligne).'<br/>';
	}
	if ($colis->primaryPODUrl) echo '<a href="'.$colis->primaryPODUrl.'" target="_blank">Voir la preuve de livraison</a>' ;
	echo'
						</b>
					</td>
				</tr>
			</tbody>
		</table>';
}

function faisabilite($soapclient, $compte, $dateExpedition, 
					 $codePostalDepart, $communeDepart, 
					 $codePostalArrivee, $communeArrivee, $typeDestinataire) {
	
	echo '<b>Test de faisabilit&#233;</b><br/>=> Trajet de '.$codePostalDepart.' - '.$communeDepart.' vers '.$codePostalArrivee.' - '.$communeArrivee.' le '.$dateExpedition.'<br/>' ;
	
	$sender = array("zipCode" => $codePostalDepart, "city" => $communeDepart);
	$receiver = array("zipCode" => $codePostalArrivee, "city" => $communeArrivee, "type" => $typeDestinataire);
	$parameters = array("accountNumber" => $compte, "shippingDate" => $dateExpedition, "sender" => $sender, "receiver" => $receiver);
	
	$services = $soapclient->feasibility(array('parameters' => $parameters));
	$nb = count($services->Service);
	if ($nb > 1) {
		// Cas o? une liste de services est renvoy?e
		foreach ($services->Service as $Service) {
			$dateTheoriqueLivraison = new DateTime($Service->dueDate);
			echo ' - Livraison pr&#233;visionnelle le '.($dateTheoriqueLivraison->format('d/m/Y')).' en '.utf8_decode($Service->serviceLabel).' - Livraison samedi : '.($Service->saturdayDelivery == 0 ? 'Non' : 'Oui').'<br/>';
		}
		echo '<br/>';
	}
	else if ($nb == 1) {
		// Cas o? un seul service est renvoy?
		$Service = $services->Service;
		$dateTheoriqueLivraison = new DateTime($Service->dueDate);
		echo ' - Livraison pr&#233;visionnelle le '.($dateTheoriqueLivraison->format('d/m/Y')).' en '.utf8_decode($Service->serviceLabel).' -  Livraison samedi : '.($Service->saturdayDelivery == 0 ? 'Non' : 'Oui').'<br/><br/>';
	}
	else {
		// Cas o? aucun service n'est renvoy?
		echo '<div style="color:red;">Aucun service disponible pour ce trajet !</div><br/><br/>';
	}

}

$username = TNT_USER;
$password = TNT_PASSWORD;

// Generation "en dur" de l'en t?te d'authentification pour WS-Security
$authheader = sprintf('
	<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
	  <wsse:UsernameToken>
		<wsse:Username>%s</wsse:Username>
		<wsse:Password>%s</wsse:Password>
	 </wsse:UsernameToken>
	</wsse:Security>', htmlspecialchars($username), htmlspecialchars( $password ));

$authvars = new SoapVar($authheader, XSD_ANYXML);
$header = new SoapHeader("http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd", "Security", $authvars);
$file = "http://www.tnt.fr/service/?wsdl";

try {
	// Construction du client et ajout de l'en t?te WS-Security
	$soapclient = new SoapClient($file, array('trace'=>1));
	$soapclient->__setSOAPHeaders(array($header));

	// Trace des fonctions d?clar?es par le serveur
	echo '<b>Fonctions d?clar?es par le serveur :</b><br/>';
	$arFunctions = $soapclient->__getFunctions();
	foreach ($arFunctions as $function) {
	echo $function.'<br/>';
	}
	echo '<br/>';

	guideCommunes($soapclient, "75015"); 
	guideCommunes($soapclient, "74140"); 
	guideCommunes($soapclient, "99999"); 

	relaisColis($soapclient, "75015", "PARIS 15"); 
	relaisColis($soapclient, "01260", "LUTHEZIEU");
	relaisColis($soapclient, "99999", "TEST"); 

	suiviColis($soapclient, "9412345000000014"); 
	suiviColis($soapclient, "9412345000000025");
	
	// Num?ro de BT valide (modulo ok)  mais colis inexistant
	suiviColis($soapclient, "1111111111111118");
} 
catch( SoapFault $e ) {
	echo($e);      
}
catch( Exception $e ) {
	echo($e);
}

try {
	// Num?ro de BT invalide (modulo incorrect)
	suiviColis($soapclient, "1234567896548521");
}
catch( SoapFault $e ) {
	echo '<div style="color:red;">'.$e->faultstring.'</div><br/>';
}

try {
	// Attention : les communes d?part et arriv?e doivent bien ?tre valides vis-?-vis du service de guide des communes
	// Test avec compte de test standard et date courante (!!! retournera une exception si samedi, dimanche ou jour f?ri?)
	faisabilite($soapclient, "06324676", date("Y-m-d"), "75015", "PARIS 15", "69001", "LYON 01", "ENTERPRISE"); 
	faisabilite($soapclient, "06324676", date("Y-m-d"), "75015", "PARIS 15", "69001", "LYON 01", "DROPOFFPOINT"); 
}
catch( SoapFault $e ) {
	echo($e);      
}

?>

