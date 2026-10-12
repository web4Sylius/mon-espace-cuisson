<?php
function guideCommunes($soapclient, $codePostal) {

    echo '<b>Test du guide des communes</b><br/>=> Communes du code postal '.$codePostal.' :<br/>' ;

    // Appel "direct" du guide des communes
    $communes = $soapclient->citiesGuide(array('zipCode' => $codePostal));
    if (property_exists($communes, 'City')) {
        $nb = count($communes->City);
        if ($nb > 1) {
            // Cas où une liste de communes est renvoyée
            foreach ($communes->City as $City) {
                echo ' - '.($City->name).'<br/>';
            }
            echo '<br/>';
        } else if ($nb == 1) {
            // Cas où une seule commune est renvoyée
            $City = $communes->City;
            echo ' - '.($City->name).'<br/><br/>';
        }
    } else {
        // Cas où aucune commune n'est renvoyée
        echo '<div style="color:red;">Ce code postal ne correspond à aucune commune !</div><br/><br/>';
    }

}


function relaisColis($soapclient, $codePostal, $commune) {

    echo '<b>Test des relais colis</b><br/>=> Relais de '.$codePostal.' - '.$commune.'<br/>' ;

    // Appel "direct" aux relais colis
    $liste_relais = $soapclient->dropOffPoints(array('zipCode' => $codePostal, 'city' => $commune));
    if (property_exists($liste_relais, 'DropOffPoint')) {
        $nb = count($liste_relais->DropOffPoint);
        if ($nb > 1) {
            // Cas où une liste de relais est renvoyée
            foreach ($liste_relais->DropOffPoint as $Relais) {
                echo ' - '.($Relais->name).' / '.($Relais->xETTCode).' / '.utf8_decode($Relais->openingHours->monday->am).'<br/>';
            }
            echo '<br/>';
        } else if ($nb == 1) {
            // Cas où un seul relais est renvoyé
            $Relais = $liste_relais->DropOffPoint;
            echo ' - '.($Relais->name).' / '.($Relais->xETTCode).' / '.utf8_decode($Relais->openingHours->monday->am).'<br/><br/>';
        }
    } else {
        // Cas où aucune relais n'est renvoyé
        echo '<div style="color:red;">Aucun relais colis pour ce couple code postal/commune !</div><br/><br/>';
    }
}

function suiviColis($soapclient, $bonTransport) {

    echo '<b>Test du suivi colis</b><br/>=> Tracking de '.$bonTransport.'<br/>' ;

    // Appel "direct" au suivi colis
    $reponse = $soapclient->trackingByConsignment(array('parcelNumber' => $bonTransport));
    
    if (!property_exists($reponse, 'Parcel')) {
        echo '<div style="color:red;">Colis inexistant</div><br/>';
        return;
    }
    
    $colis = $reponse->Parcel;
    $expediteur = $colis->sender;
    $destinataire = $colis->receiver;
    $evenements = $colis->events;

    $expAddress2 = '';
    if (property_exists($expediteur, 'address2')) $expAddress2 = $expediteur->address2;
    
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
                    <td>'.$expAddress2.'</td>
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
    
        
    // Attention : les différents événements peuvent ne pas être renseignés selon l'état courant de la livraison

                
    if (property_exists($evenements, 'requestDate') && $evenements->requestDate != '') {
        $requestDate = new DateTime($evenements->requestDate);
        echo'
                <tr>
                    <td></td>
                    <td>Colis en pr&#233;paration</td>
                    <td style="text-align:left;">'.$requestDate->format('d/m/Y').'</td>
                    <td style="text-align:right;"></td>
                    <td style="text-align:center;"></td>
                </tr>';
    }
    if (property_exists($evenements, 'processDate') && $evenements->processDate != '') {
        $processDate = new DateTime($evenements->processDate);
        echo'
                <tr>
                    <td></td>
                    <td>Colis en cours d\'acheminement</td>
                    <td style="text-align:left;">'.$processDate->format('d/m/Y').'</td>
                    <td style="text-align:right;">'.$processDate->format('H:i').'</td>
                    <td style="text-align:center;">'.$evenements->processCenter.'</td>
                </tr>';
    }
    if (property_exists($evenements, 'arrivalDepartureDate') && $evenements->arrivalDepartureDate != '') {
        $arrivalDate = new DateTime($evenements->arrivalDate);
        echo'
                <tr>
                    <td></td>
                    <td>Arriv&#233;e au centre de distribution</td>
                    <td style="text-align:left;">'.$arrivalDate->format('d/m/Y').'</td>
                    <td style="text-align:right;">'.$arrivalDate->format('H:i').'</td>
                    <td style="text-align:center;">'.$evenements->arrivalCenter.'</td>
                </tr>';
    }
    if (property_exists($evenements, 'deliveryDepartureDate') && $evenements->deliveryDepartureDate != '') {
        $deliveryDepartureDate = new DateTime($evenements->deliveryDepartureDate);
        echo'
                <tr>
                    <td></td>
                    <td>D&#233;part en livraison</td>
                    <td style="text-align:left;">'.$deliveryDepartureDate->format('d/m/Y').'</td>
                    <td style="text-align:right;">'.$deliveryDepartureDate->format('H:i').'</td>
                    <td style="text-align:center;">'.$evenements->deliveryDepartureCenter.'</td>
                </tr>';
    }
    if (property_exists($evenements, 'deliveryDate') && $evenements->deliveryDate != '') {
        $deliveryDate = new DateTime($evenements->deliveryDate);
        echo'
                <tr>
                    <td></td>
                    <td>Pr&#233;sentation du chauffeur</td>
                    <td style="text-align:left;">'.$deliveryDate->format('d/m/Y').'</td>
                    <td style="text-align:right;">'.$deliveryDate->format('H:i').'</td>
                    <td style="text-align:center;"></td>
                </tr>';
    }
    echo'
                <tr><td colspan="5" height="15px"></td></tr>
                <tr>
                    <td style="height:65px;"></td>
                    <td style="vertical-align:top;"><b>Etat de la livraison</b></td>
                    <td style="vertical-align:top; text-align:justify;" colspan="3"><b>';
                    
    $nb = count($colis->longStatus);
    if ($nb > 1) {
        // Cas où plusieurs lignes d'info sont retournées
        foreach ($colis->longStatus as $Ligne) {
            echo utf8_decode($Ligne).'<br/>';
        }
    }
    else if ($nb == 1) {
        // Cas où une seule ligne d'info est retournée
        $Ligne = $colis->longStatus;
        echo utf8_decode($Ligne).'<br/>';
    }
    if (property_exists($colis, 'primaryPODUrl')) echo '<a href="'.$colis->primaryPODUrl.'" target="_blank">Voir la preuve de livraison</a>' ;
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
        // Cas où une liste de services est renvoyée
        foreach ($services->Service as $Service) {
            $dateTheoriqueLivraison = new DateTime($Service->dueDate);
            echo ' - Livraison pr&#233;visionnelle le '.($dateTheoriqueLivraison->format('d/m/Y')).' en '.utf8_decode($Service->serviceLabel).' - Livraison samedi : '.($Service->saturdayDelivery == 0 ? 'Non' : 'Oui').'<br/>';
        }
        echo '<br/>';
    }
    else if ($nb == 1) {
        // Cas où un seul service est renvoyé
        $Service = $services->Service;
        $dateTheoriqueLivraison = new DateTime($Service->dueDate);
        echo ' - Livraison pr&#233;visionnelle le '.($dateTheoriqueLivraison->format('d/m/Y')).' en '.utf8_decode($Service->serviceLabel).' -  Livraison samedi : '.($Service->saturdayDelivery == 0 ? 'Non' : 'Oui').'<br/><br/>';
    }
    else {
        // Cas où aucun service n'est renvoyé
        echo '<div style="color:red;">Aucun service disponible pour ce trajet !</div><br/><br/>';
    }

}

function expedition($soapclient, $compte, $dateExpedition, $labelFormat, $serviceLivraison, $livraisonSamedi,
                    $senderName, $senderAdr1, $senderZipCode, $senderCity, $senderCtcFirstName, $senderCtcLastName, 
                    $senderMail, $senderPhone, $receiverName, $receiverAdr1, $receiverZipCode, $receiverCity) {

    echo '<b>Test de cr&#233;ation d\'une exp&#233;dition</b><br/>=> Trajet de '.$senderZipCode.' - '.$senderCity.' vers '.$receiverZipCode.' - '.$receiverCity.' le '.$dateExpedition.'<br/>' ;

    $sender = array("name" => $senderName, "address1" => $senderAdr1, "zipCode" => $senderZipCode, "city" => $senderCity, 
                    "contactFirstName" => $senderCtcFirstName, "contactLastName" => $senderCtcLastName, 
                    "emailAddress" => $senderMail, "phoneNumber" => $senderPhone);
    $receiver = array("name" => $receiverName, "address1" => $receiverAdr1, "zipCode" => $receiverZipCode, "city" => $receiverCity);

    $colis1 = array("sequenceNumber" => "1", "customerReference" => "TESTPHP", "weight" => "1.1");
    $colis2 = array("sequenceNumber" => "2", "customerReference" => "TESTPHP", "weight" => "1.2");
    
    $parcelsRequest = array($colis1, $colis2);

    $parameters = array("accountNumber" => $compte, "shippingDate" => $dateExpedition, "labelFormat" => $labelFormat,
                    "sender" => $sender, "receiver" => $receiver, "serviceCode" => $serviceLivraison, 
                    "saturdayDelivery" => $livraisonSamedi, "quantity" => count($parcelsRequest), "parcelsRequest" => $parcelsRequest);

    $response = $soapclient->expeditionCreation(array('parameters' => $parameters));

    // Pas besoin d'appeler base64_decode pour l'étiquette, le client SOAP s'en ai chargé en interne !
    $fp = fopen('bonTransport.pdf', 'wb');
    fwrite($fp, $response->Expedition->PDFLabels);
    fclose($fp);

    echo '<a href="bonTransport.pdf" target="_blank">Etiquette</a><br/>';

    $nb = count($response->Expedition->parcelResponses);
    if ($nb > 1) {
        // Cas où une liste de colis est renvoyée
        foreach ($response->Expedition->parcelResponses as $Colis) {
            echo $Colis->parcelNumber.'<br/>';
        }
        echo '<br/>';
    }
    else if ($nb == 1) {
        // Cas où un seul colis est renvoyé
        $Colis = $response->Expedition->parcelResponses;
        echo $Colis->parcelNumber.'<br/>';
    }
    else {
        // Cas où aucun colis n'est renvoyé
        echo '<div style="color:red;">Erreur, aucun colis retourné !</div><br/><br/>';
    }

}

function ramassage($soapclient, $compte, $dateExpedition, $serviceLivraison, $livraisonSamedi,
                    $senderName, $senderAdr1, $senderZipCode, $senderCity, $senderCtcFirstName, $senderCtcLastName, 
                    $senderMail, $senderPhone, $receiverName, $receiverAdr1, $receiverZipCode, $receiverCity) {

    echo '<b>Test de cr&#233;ation d\'un ramassage</b><br/>=> Trajet de '.$senderZipCode.' - '.$senderCity.' vers '.$receiverZipCode.' - '.$receiverCity.' le '.$dateExpedition.'<br/>' ;

    $sender = array("name" => $senderName, "address1" => $senderAdr1, "zipCode" => $senderZipCode, "city" => $senderCity, 
                    "contactFirstName" => $senderCtcFirstName, "contactLastName" => $senderCtcLastName, 
                    "emailAddress" => $senderMail, "phoneNumber" => $senderPhone, "closingTime" => "18:00");
    $receiver = array("name" => $receiverName, "address1" => $receiverAdr1, "zipCode" => $receiverZipCode, "city" => $receiverCity);

    // notification sur anomalie uniquement (notifySuccess -> 0)
    $notification = array("emailAddress" => $senderMail, "media" => "EMAIL", "notifySuccess" => 0);
    
    // ramassage pour 1 colis équipé d'une étiquette TNT (quantity -> 1 & labelsProvided -> 1)
    $parameters = array("accountNumber" => $compte, "shippingDate" => $dateExpedition, "notification" => $notification,
                    "sender" => $sender, "receiver" => $receiver, "serviceCode" => $serviceLivraison, 
                    "saturdayDelivery" => $livraisonSamedi, "labelsProvided" => "1", "quantity" => "1");

    $response = $soapclient->pickUpRequestCreation(array('parameters' => $parameters));
    if (property_exists($response, 'pickUpNumber')) {
        echo 'Num&#233;ro de la demande de ramassage : '.$response->pickUpNumber.'<br/>';
    } else {
        echo '<div style="color:red;">Echec création du ramassage</div><br/><br/>';
    }

}