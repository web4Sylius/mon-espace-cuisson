<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(BACK_PATH."/adv/adv.class.php");

$code_postal     = $_GET['cp'];
$ville           = $_GET['ville'];
$adv_id          = $_GET['advid'];
$fournisseur_nom = $_GET['name'];
$commande_id     = $_GET['facturid'];

$adv = new adv($DB_site2, $adv_id);

$authheader = sprintf('
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
      <wsse:UsernameToken>
        <wsse:Username>%s</wsse:Username>
        <wsse:Password>%s</wsse:Password>
     </wsse:UsernameToken>
    </wsse:Security>', htmlspecialchars('pr@mon-espace-cuisson.com'), htmlspecialchars('acilyon12'));

$authvars = new SoapVar($authheader, XSD_ANYXML);
$header = new SoapHeader("http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd", "Security", $authvars);

$soapclient = new SoapClient('http://www.tnt.fr/service/?wsdl', array(
    'trace'=> 1,
    'stream_context'=>stream_context_create(array(
        'http'=> array(
            'user_agent' => 'PHP/SOAP',
            'accept' => 'application/xml'
        )
    ))
));
$soapclient->__setSOAPHeaders(array($header));

$liste_relais = $soapclient->dropOffPoints(array('zipCode' => $code_postal, 'city' => $ville));

if(is_object($liste_relais) && isset($liste_relais->DropOffPoint)){
    ?>
    <input type="hidden" name="relaiscolisxett" value="">
    <input type="hidden" name="relaiscoliscp" value="">
    <input type="hidden" name="relaiscolisville" value="">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <?php
        $i=0;
        foreach($liste_relais->DropOffPoint as $le_relais){
            $class = 'td_alt1';
            if($i%2 == 0) $class = 'td_alt2';

            $relais_txt = $le_relais->name.'<br>'.$le_relais->address1.'<br>'.$le_relais->zipCode.' '.$le_relais->city;
            
            $aFuncRelais = array(
                "document.getElementsByName('relaiscolisxett')[0].value = '" . $le_relais->xETTCode . "'",
                "document.getElementsByName('relaiscoliscp')[0].value = '" . $le_relais->zipCode . "'",
                "document.getElementsByName('relaiscolisville')[0].value = '" . $le_relais->city . "'",
                "adv_save('relaiscolisadr',10)",
                "adv_save('relaiscolisxett')",
                "adv_save('relaiscoliscp')",
                "adv_save('relaiscolisville')",
                "loadStep('trspt9', '/admin/adv/ajax/majAdrLivr.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')",
                "loadStep('trspt10', '/admin/adv/ajax/majDateLivr.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')"
            );
            ?>
            <tr>
                <td class="<?php echo $class; ?>">
                    <?php echo $adv->radio_inputs("relaiscolisadr", array("" => $relais_txt), implode(';',$aFuncRelais)); ?>
                </td>
                <td class="<?php echo $class; ?>" style="width: 200px;">
                    <?php echo $le_relais->name; ?>
                    <br><?php echo $le_relais->address1; ?>
                    <br><?php echo $le_relais->zipCode.' '.$le_relais->city; ?>
                    <br><a href="https://www.google.fr/maps/place/<?php echo $le_relais->latitude; ?>,<?php echo $le_relais->longitude; ?>" target="_blank">Localisation Google Map</a>
                    <br><a href="<?php echo $le_relais->geolocalisationUrl; ?>" target="_blank">Infos pratique TNT</a>
                </td>
                <td class="<?php echo $class; ?>" style="font-size: 10px;">
                    <u>Lundi</u>
                    <br><?php echo $le_relais->openingHours->monday->am; ?> | <?php echo $le_relais->openingHours->monday->pm; ?>
                    <br><u>Mardi</u>
                    <br><?php echo $le_relais->openingHours->tuesday->am; ?> | <?php echo $le_relais->openingHours->tuesday->pm; ?>
                    <br><u>Mercredi</u>
                    <br><?php echo $le_relais->openingHours->wednesday->am; ?> | <?php echo $le_relais->openingHours->wednesday->pm; ?>
                </td>
                <td class="<?php echo $class; ?>" style="font-size: 10px;">
                    <u>Jeudi</u>
                    <br><?php echo $le_relais->openingHours->thursday->am; ?> | <?php echo $le_relais->openingHours->thursday->pm; ?>
                    <br><u>Vendredi</u>
                    <br><?php echo $le_relais->openingHours->friday->am; ?> | <?php echo $le_relais->openingHours->friday->pm; ?>
                </td>
                <td class="<?php echo $class; ?>" style="font-size: 10px;">
                    <u>Samedi</u>
                    <br><?php echo $le_relais->openingHours->saturday->am; ?> | <?php echo $le_relais->openingHours->saturday->pm; ?>
                    <br><u>Dimanche</u>
                    <br><?php echo $le_relais->openingHours->sunday->am; ?> | <?php echo $le_relais->openingHours->sunday->pm; ?>
                </td>
            </tr>
            <?php

            $i++;
        }
        ?>
    </table>
    <?php
} else {
    echo "Aucun résultat";
}