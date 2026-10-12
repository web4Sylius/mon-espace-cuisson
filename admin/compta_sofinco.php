<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/admin/admin_global.php');

include_once(BACK_PATH."/header.php");
include_once(BACK_PATH."/menu.php");

$DB_site = clone $DB_site2;

//$apiUrl="https://r7weblong.transcred.com/websrv/index.asp";
$apiUrl="https://weblong.transcred.com/websrv/index.asp";
/////////////////////////////// Calcul du montant total Facture ////////////////////////////////////

if(!function_exists('get_montant_ttc')){
        function get_montant_ttc($facturid){
                global $DB_site;

                //Remise HT = TOT HT hors remise x (Remise TTC / TOT TTC hors remise)
                $sql = "SELECT SUM(lf.prix * lf.qte) as 'prixHT', f.prixport as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM(lf.prix * lf.qte) + f.prixport) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
                $sql .= " FROM facture f";
                $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
                $sql .= " WHERE f.facturid = ".$facturid;
                $sql .= " GROUP BY f.facturid";
                $ca_cmd = $DB_site->query($sql);

                $ca_total = 0;
                while($ligne = $DB_site->fetch_array($ca_cmd)){
                        $ca_total += $ligne['prixHT'] + $ligne['portHT'] - $ligne['remiseHT'];
                }

                return number_format($ca_total,2,',',' ');
        }
}

/////////////////////////////// NB total Factures ////////////////////////////////////

//NB facture SOFINCO en cours, moyenid 17 et 18 de la table moyenpaiement
$nbFactureSofinco = 0;
$sql = "SELECT 1 FROM facture WHERE moyenid in (17,18) and num_dossier <>'' ";
$req = $DB_site->query($sql);
$nbFactureSofinco = $DB_site->num_rows($req);


/////////////////////////////// Filtres //////////////////////////////////////////////

//liste des pays a afficher
$aPaysEuro = array(57,64,10,17,26,42,45,153,53,56,66,75,81,83,98,99,105,118,132,133,44,136,172,148,149,157,181,186);

//Sélection des pays
$sql = "SELECT paysid, libelle";
$sql .= " FROM pays";
$sql .= " WHERE paysid IN (".implode(',',$aPaysEuro).")";
$sql .= " ORDER BY libelle";
$req = $DB_site->query($sql);
$optPays = '';
while($row = $DB_site->fetch_array($req))
{
    $optPays .= "<option value=\"".$row['paysid']."\" ".(isset($_POST['paysid']) && $row['paysid'] == $_POST['paysid'] ? 'selected' : '').">".substr($row['libelle'],0,20)."</option>";
}

//liste types de commandes/factures validées
$aSofValide = array(1,5,7,8);

//demande de Philippe, voir par défaut résultat des 2 derniers mois :
$date_last_month = time() - 5184000;
$date_last_month = date('Y-m-d',$date_last_month);
$date_now = date('Y-m-d',time());
$sqlDate = " AND f.datefacture BETWEEN '".$date_last_month."' AND '".$date_now."' ";
$last_month = date('d/m/Y',time() - 5184000);
$now = date('d/m/Y',time());
//////////////////////////////// Gestion des filtres ////////////////////////////////////

if(!empty($_POST))
{

    //Filtre "pays"
    $sqlPaysEuro = "";

    if(isset($_POST['paysid']) && $_POST['paysid'] != 0)
    {
        $sqlPaysEuro = " AND p.paysid = ".$_POST['paysid'];
    }


    //Filtre "recherche"
    $sqlRecherche = '';

    if(isset($_POST['facture_recherche']) && $_POST['facture_recherche'] != '')
    {
        $sqlRecherche .= " AND (";
        $sqlRecherche .= " f.facturid = '".$_POST['facture_recherche']."'";
        $sqlRecherche .= " OR u.raisonsociale LIKE '%".$_POST['facture_recherche']."%'";
        $sqlRecherche .= " OR u.nom LIKE '%".$_POST['facture_recherche']."%'";
        $sqlRecherche .= " OR u.prenom LIKE '%".$_POST['facture_recherche']."%'";
        $sqlRecherche .= ") ";   
    }   

    //Filtre "date"

    if($_POST['facture_debut'] != '' && $_POST['facture_fin'] != '')
    {            $erreur_date = false;

        //Vérification de la saisie
        $aTemp = explode('/', $_POST['facture_debut']);
        $aTemp2 = explode('/', $_POST['facture_fin']);
        if(count($aTemp) != 3 || strlen($aTemp[0]) != 2 || strlen($aTemp[1]) != 2 || strlen($aTemp[2]) != 4) $erreur_date = true;
        if(count($aTemp2) != 3 || strlen($aTemp2[0]) != 2 || strlen($aTemp2[1]) != 2 || strlen($aTemp2[2]) != 4) $erreur_date = true;
        $date_debut = $aTemp[2].'-'.$aTemp[1].'-'.$aTemp[0];
        $date_fin = $aTemp2[2].'-'.$aTemp2[1].'-'.$aTemp2[0];

        if(!$erreur_date) $sqlDate = " AND f.datefacture BETWEEN '".$date_debut."' AND '".$date_fin."' ";
    }

    //Filtre checkbox validée
    $sqlValide = "";

    if(isset($_POST['sofvalide']) && $_POST['sofvalide'] != "")
    {
        $sqlValide = " AND f.valide in (".implode(',',$aSofValide).")";
        //print_r ("TOTO ".$sqlValide);
    }
}

//////////////////////////// Récupération des cmd Sofinco en cours ////////////////////////////////////

$sql = "SELECT "
        . "f.facturid"
        . ", f.datefacture as commande_date"
        . ", DATE_FORMAT(f.datefacture,'%d/%m/%Y') as commande_date_formate"
        . ", u.raisonsociale"
        . ", u.prenom"
        . ", u.nom"
        . ", u.vip"
        . ", IF(p.paysid = 57,'France',IF(p.paysid = 181, 'France (Corse)', p.libelle)) as pays_libelle"
        . ", u.ville"
        . ", u.site_code"
        . ", u.langue"
        . ", f.num_dossier"
        . ", f.montant_credit"
        . ", f.TEG_annuel"
        . ", e.libelleFR"
        . ", f.valide";
$sql .= " FROM facture f";
$sql .= " INNER JOIN utilisateur u ON (u.userid = f.userid)";
$sql .= " INNER JOIN pays p ON (p.paysid = u.paysid)";
$sql .= " INNER JOIN etatfacture e ON (e.valudeid = f.valide)";
$sql .= " WHERE ";
$sql .= " f.moyenid IN (17,18)";
$sql .= " AND num_dossier <>''";
$sql .= $sqlRecherche;
$sql .= $sqlPaysEuro;
$sql .= $sqlDate;
$sql .= $sqlValide;
$sql .= " ORDER BY f.facturid DESC"; 

//echo "<BR>";
//print_r ($sql);
//print_r ($sqlDate);
//print_r ($sqlPaysEuro);

$req = $DB_site->query($sql);
$nbEnr = $DB_site->num_rows($req);

//////////////////////////////// formatage des données avant affichage ////////////////////////////////////
//////////////////////On limite a 400 le nombre de factures possible à traiter ////////////////////////////

if($nbEnr <= 400){

        //initialisation
        $aListeSofinco = array();
        $cpt           = 0;
        $aMarques      = array();

        // recuperation des lignes de la requete
        while($ligneSof = $DB_site->fetch_array($req)){

                //site de provenance en fonction du pays
                switch($ligneSof['site_code']){
                        case '':
                        case 'base':
                                switch($ligneSof['langue']){
                                        case 'FR':
                                                $provenance = 'MCAVFR';
                                                break;
                                        case 'EN':
                                                $provenance = 'MWCCOM';
                                                break;
                                        case 'ES':
                                                $provenance = 'MVES';
                                                break;
                                }
                                break;
                        case 'uk':
                                $provenance = 'MWCUK';
                                break;
                        default:
                                $provenance = $ligneSof['site_code'];
                                break;
                }

                // mise en couleur en fonction du statut validé
                if ( in_array($ligneSof['valide'], $aSofValide))
                {
                    $isValidated ="<STRONG style=\"color:green\">".mb_strtolower($ligneSof['libelleFR'], 'UTF-8')."</STRONG>";
                }
                else
                    $isValidated = mb_strtolower($ligneSof['libelleFR'], 'UTF-8');

                $xml="<Service ServiceName=\"FLUX_D\">
                <IN>
                <DossiersCollection>
                <Dossier DossierNumber=\"".$ligneSof['num_dossier']."\"/>
                </DossiersCollection>
                </IN>
                </Service>";

                $ch = curl_init();
                curl_setopt ($ch, CURLOPT_URL,$apiUrl);
                curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_POST, true); 
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xml); 
                curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20); 
                curl_setopt($ch, CURLOPT_TIMEOUT, 20);  
                $data = curl_exec($ch);
                curl_close($ch);
                $retour_xml=simplexml_load_string($data, 'SimpleXMLElement');
                $json = json_encode($retour_xml);
                $array = json_decode($json,TRUE);
                $array_dossiers=$array['OUT']['DossiersCollection']['Dossier']['@attributes'];
               
                //Construction de la ligne du tableau
                //$aListeSofinco[$ligneSof['factureid']][$ligneSof['num_dossier']] = ""
                $aListeSofinco[$cpt] = ""
                ."<tr>"
                        ."<td class=\"%td_alt%\">
                            <a href=\"".BACK_URL."/facture.php?action=editer&facturid=".$ligneSof['facturid']."&lst_trietat=\" target=\"_blank\">".$ligneSof['facturid']
                            ."</a><br>".$ligneSof['commande_date_formate']
                        ."</td>"
                        ."<td class=\"%td_alt%\">".($ligneSof['raisonsociale'] != '' ? $ligneSof['raisonsociale']
                            .'<br>' : '').ucfirst(mb_strtolower($ligneSof['prenom'], 'UTF-8'))
                            ." ".mb_strtoupper($ligneSof['nom'], 'UTF-8')."<br>".usericon($ligneSof['vip'], 20)
                        ."</td>"
                        ."<td class=\"%td_alt%\">"
                            .ucfirst(mb_strtolower($ligneSof['ville'], 'UTF-8'))
                            .'<br>'.mb_strtoupper($ligneSof['pays_libelle'], 'UTF-8')
                        ."</td>"
                        ."<td class=\"%td_alt%\">"
                            .$provenance
                        ."</td>"
                        ."<td class=\"%td_alt%\">"
                            .mb_strtoupper($ligneSof['num_dossier'], 'UTF-8')
                        ."</td>"
                        ."<td class=\"%td_alt%\">"
                            .mb_strtoupper($ligneSof['montant_credit'], 'UTF-8')
                        ."</td>"
                        ."<td class=\"%td_alt%\">"
                            .mb_strtoupper($ligneSof['TEG_annuel'], 'UTF-8')
                        ."</td>"
                        ."<td class=\"%td_alt%\">"
                            .get_montant_ttc($ligneSof['facturid'])." &euro;"
                        ."</td>"
                        ."<td class=\"%td_alt%\">"
                            .$isValidated
                        ."</td>"
                        ."<td class=\"%td_alt%\">"
                            .$array_dossiers['DossierStatus']
                        ."</td>"
                ."</tr>";

                $cpt++;
        }
} else {
        $message = 'En raison d\'un trop grand nombre de résultats (400+), la recherche ne peut être affichée, à moins d\'être affinée';
}

?>

<!-  ------------------------------------------------------- Corps de la page --------------------------------------------------- -->
<h1>Vue d'ensemble des commandes SOFINCO</h1>

<div style="display:flex;flex-wrap:nowrap;justify-content: space-around;">
<!-  ------------------------------------------------------- Recap du nombre de commandes --------------------------------------- -->
<div>
        <table border="0" cellpadding="0" cellspacing="0" class="sav_recap" style="margin: 0 auto;">
                <tr>
                        <td class="entete_td_refonte"></td>
                        <td class="entete_td_refonte">Total</td>
                        <!--<td class="entete_td_refonte">En cours</td>
                        <td class="entete_td_refonte">A clôturer</td>-->
                </tr>
                <tr>
                        <td class="td_gris1"><strong>SOFINCO</strong></td>
                        <td class="td_gris1"><?php echo $nbFactureSofinco; ?></td>
                </tr>
        </table>
</div>


<!-  ------------------------------------------------------- Panneau de recherche --------------------------------------------------- -->
<div class="commande_detail" style="width: 550px !important; margin-top: 0;">
    <div style="padding: 10px;">
        <form method="post" action="compta_sofinco.php" style="margin: 0;">
            <table cellpadding="0" cellspacing="0" class="sav_filtres">
                <tr>
                    <td>
                        <strong>Recherche :</strong> 
                    </td>
                    <td>
                        <input type="text" name="facture_recherche" placeholder="N° Facture / Société / Client" value="<?php echo !empty($_POST['facture_recherche']) ? $_POST['facture_recherche'] : ''; ?>" style="width: 200px;">
                    </td>
                    <td>
                        <strong>Pays :</strong>
                    </td>
                    <td>
                        <select name="paysid">
                            <option value="0">-- Tous --</option>
                            <?php echo $optPays; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="left">
                        <strong>Factures du :<input type="text" name="facture_debut" style="width: 75px;" placeholder="xx/mm/YYYY" value="<?php echo !empty($_POST['facture_debut']) ? $_POST['facture_debut'] : $last_month; ?>"> au <input type="text" style="width: 75px;" name="facture_fin" placeholder="xx/mm/YYYY" value="<?php echo !empty($_POST['facture_fin']) ? $_POST['facture_fin'] : $now; ?>"></strong>
                        <?php if($erreur_date){ ?>
                        <br>
                        <span style="color: red; padding-top: 5px;">Erreur de saisie de date. Le filtre n'a pas été pris en compte</span>
                        <?php } ?>
                    </td>
                    <td colspan="2" align="left">
                        <strong>Validées :<input type="checkbox" name="sofvalide" style="width: 50px;" value="ok" <?php echo !empty($_POST['sofvalide']) ? 'checked' : ''; ?> > </strong>
                    </td>
                </tr>
                <tr>
                    <td><a href="compta_sofinco.php">Réinitialiser</a></td>
                    <td colspan="3" align="right"><input type="submit" value="Rechercher"></td>
                </tr>   
            </table>
        </form>
    </div>
</div>

<!-  ------------------------------------------------------- explication statut --------------------------------------------------- -->

<div style="font-size: 9px;">ETU: dossier en étude<br>CLI: Attente retour client<br>REF: refus<br>ANN: Annulé ou sans suite<br>FIN: Financé<br>NFD: Non trouvé<br>ERR: Non traité car erreur technique<br>ATT/AFI: Dossier en attente de financement</div>

</div>
<!-  ------------------------------------------------------- messages infos --------------------------------------------------- -->

<div style="clear: both;"></div>

<?php if(isset($message)){ ?>
        <p style="text-align: center; font-weight: bold;"><?php echo $message; ?></p>
<?php } else { ?>
        <p style="text-align: center; font-weight: bold;"><?php echo $cpt; ?> résultat(s)</p>
<?php } ?>

<!-  -------------------------------------------------------Liste de resultats ------------------------------------------------ -->

<?php 

//int_r($aListeSofinco);

/////////////////////////////////////////////// Affichage de la liste de resultat

if(!empty($aListeSofinco)){

                 echo ""
                . "<table border=\"0\" class=\"sav_listing\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin: 10px 0 0;\">"
                . "<tr>"
                        . "<td class=\"entete_td_refonte\">CMD</td>"
                        . "<td class=\"entete_td_refonte\">Société<br>Client</td>"
                        . "<td class=\"entete_td_refonte\">Dom.</td>"
                        . "<td class=\"entete_td_refonte\">Prov.</td>"
                        . "<td class=\"entete_td_refonte\">num dossier</td>"
                        . "<td class=\"entete_td_refonte\" style=\"width: 90px;\">montant credit</td>"
                        . "<td class=\"entete_td_refonte\" style=\"width: 60px;\">TEG</td>"
                        . "<td class=\"entete_td_refonte\" style=\"width: 90px;\">Total TTC</td>"
                        . "<td class=\"entete_td_refonte\" style=\"width: 90px;\">Statut</td>"
                        . "<td class=\"entete_td_refonte\" style=\"width: 90px;\">Statut Sofinco</td>"
                . "</tr>";

                //krsort($le_sav);

                $cpt = 0;
                foreach($aListeSofinco as $ligne_sav){
                        echo str_replace('%td_alt%', 'td_gris'.($cpt%2 == 0 ? '1' : '2'), $ligne_sav);
                        $cpt++;
                }

                echo "</table>";
}

///////////////////////////////////////////////Footer
include_once(BACK_PATH."/footer.php");
