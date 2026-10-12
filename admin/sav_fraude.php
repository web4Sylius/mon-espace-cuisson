<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/admin/admin_global.php');

require_once(BACK_PATH.'/sav.class.php');

$DB_site = clone $DB_site2;

if(!function_exists('get_factures_sav')){
        function get_factures_sav($aFactures){
                global $DB_site;

                $aReturn = array();

                $sql = "SELECT facturid as commande_id";
                $sql .= " FROM facture";
                $sql .= " WHERE provenance = 'sav'";
                $sql .= " AND provenanceid IN (".implode(',',$aFactures).")";
                $sql .= " UNION ";
                $sql .= "SELECT provenanceid as commande_id";
                $sql .= " FROM facture";
                $sql .= " WHERE provenance = 'sav'";
                $sql .= " AND facturid IN (".implode(',',$aFactures).")";
                $req = $DB_site->query($sql);
                while($row = $DB_site->fetch_array($req)){
                        $aReturn[] = $row['commande_id'];
                }

                return $aReturn;
        }
}

if(!function_exists('get_suivi')){
        function get_suivi($facturid){
                global $DB_site;

                //Récupération de tous les ids commande du dossier SAV
                $aFactureIds = $aFactureClean = array($facturid);
                
                while(1==1){
                    $loop = false;

                    $aFactureIds = get_factures_sav($aFactureIds);

                    foreach($aFactureIds as $facture_id){
                        if(!in_array($facture_id, $aFactureClean)){
                            $loop = true;
                            $aFactureClean[] = $facture_id;
                        }
                    }

                    $aFactureIds = $aFactureClean;

                    if(!$loop) break;
                }

                //Récupération du dernier suivi créé par l'utilisateur qualite (1)
                $sql = "SELECT pour, DATE_FORMAT(FROM_UNIXTIME(rappel), '%d/%m/%Y %H:%i') as dateRappel, rappel, `date`";
                $sql .= " FROM suivi_commande";
                $sql .= " WHERE 1";
                $sql .= " AND facturid IN (".implode(',',$aFactureClean).")";
                $sql .= " AND commercial = 'qualite'";
                $sql .= " AND suivi NOT LIKE '%[maj_sav]%'";
                $sql .= " ORDER BY date DESC";
                $sql .= " LIMIT 0,1";
                $tabSuivi = $DB_site->query_first($sql);

                //Récupération du dernier suivi avec un rappel adressé à qualite (2)
                $sql = "SELECT pour, DATE_FORMAT(FROM_UNIXTIME(rappel), '%d/%m/%Y %H:%i') as dateRappel, rappel, `date`";
                $sql .= " FROM suivi_commande";
                $sql .= " WHERE 1";
                $sql .= " AND facturid IN (".implode(',',$aFactureClean).")";
                $sql .= " AND pour     = 'qualite'";
                $sql .= " AND rappel   != 0";
                $sql .= " AND suivi NOT LIKE '%[maj_sav]%'";
                $sql .= " ORDER BY date DESC";
                $sql .= " LIMIT 0,1";
                $tabRappel = $DB_site->query_first($sql);

                $bg_color = 'green';

                //Si date 1 > date 2 : si pas de rappel => rouge
                if($tabSuivi['date'] > $tabRappel['date']){
                        $tabReturn = array();
                        if($tabSuivi['rappel'] == 0) $bg_color = '#C5170E';
                        if($tabSuivi['pour'] == 'qualite') $tabReturn = $tabSuivi;
                }  else {
                        $tabReturn = $tabRappel;
                }

                if($tabReturn['rappel'] < mktime(0,0,0,date('n'),date('j')+1,date('Y'))) $bg_color = '#C5170E';

                $tabReturn['bgcolor'] = $bg_color;                

                return $tabReturn;
        }
}

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

if(!function_exists('nice')){
        function nice($var, $color = 'black'){
                if(isset($_COOKIE['admin_username']) && $_COOKIE['admin_username'] == 'sylvain'){
                        echo "<div style=\"color:".$color.";\">";
                        echo "<p style=\"color:red; font-weight:bold;\">Contenu de la variable :</p>";
                        if(is_array($var) || is_object($var)){
                                echo "<pre>";
                                print_r($var);
                                echo "</pre>";   
                        } else {
                                var_dump($var);
                        }
                        echo "</div>";
                }
        }
}

if(!function_exists('nicex')){
        function nicex($var, $color = 'black'){
                nice($var, $color);
                  
                if(isset($_COOKIE['admin_username']) && $_COOKIE['admin_username'] == 'sylvain')  exit();
        }
}

include_once(BACK_PATH."/header.php");
include_once(BACK_PATH."/menu.php");

//Par défaut, tous les chiffres du recap à 0
for($i=0;$i<=4;$i++){
        $aRecapSAV[$i]['actif']   = 0;
        $aRecapSAV[$i]['cloture'] = 0;
}

//NB SAV en cours
$sql = "SELECT s.facturid, sf.value as sav_type, s.demande_cloture";
$sql .= " FROM sav s";
$sql .= " INNER JOIN sav_fields sf ON (sf.savid = s.id)";
$sql .= " INNER JOIN facture f ON (f.facturid = s.facturid)";
$sql .= " WHERE 1";
$sql .= " AND sf.name  = 'sav_typelitige'";
$sql .= " AND sf.value = '5'";
$sql .= " AND s.fin    = 0";
$req = $DB_site->query($sql);
while($le_sav = $DB_site->fetch_array($req)){

        if($le_sav['demande_cloture'] == 0){
                //if($le_sav['sav_type'] == 0) echo $le_sav['facturid'].'<br>';
                $aRecapSAV[$le_sav['sav_type']]['actif']++;
        } else {
                $aRecapSAV[$le_sav['sav_type']]['cloture']++;
        }
}

//Filtres par défaut (quand aucun filtre sélectionné)
$sqlStatut = " AND s.fin = 0 AND s.demande_cloture = 0";
$sqlType   = " AND sf.value = '5'"; //On n'affiche que les type "fraude"
$sqlDate   = "";

//Gestion des filtres
if(!empty($_POST)){

        //Filtre "Statut SAV"
        if($_POST['sav_type'] != ''){
                $sqlType = " AND sf.value = ".$_POST['sav_type'];
        }

        //Filtre "Statut SAV"
        switch($_POST['sav_statut']){
                case '0':
                        $sqlStatut = " AND s.fin = 0 AND s.demande_cloture = 0";
                        break;
                case '1':
                        $sqlStatut = " AND s.demande_cloture != 0 AND s.fin = 0";
                        break;
                case '2':
                        $sqlStatut = " AND s.fin != 0";
                        break;
                default:
                        $sqlStatut = "";
                        break;
        }

        //Filtre "date"
        if($_POST['sav_debut'] != '' && $_POST['sav_fin'] != ''){

                $erreur_date = false;

                //Vérification de la saisie
                $aTemp = explode('/', $_POST['sav_debut']);
                $aTemp2 = explode('/', $_POST['sav_fin']);
                if(count($aTemp) != 3 || strlen($aTemp[0]) != 2 || strlen($aTemp[1]) != 2 || strlen($aTemp[2]) != 4) $erreur_date = true;
                if(count($aTemp2) != 3 || strlen($aTemp2[0]) != 2 || strlen($aTemp2[1]) != 2 || strlen($aTemp2[2]) != 4) $erreur_date = true;
                $date_debut = $aTemp[2].'-'.$aTemp[1].'-'.$aTemp[0];
                $date_fin = $aTemp2[2].'-'.$aTemp2[1].'-'.$aTemp2[0];

                if(!$erreur_date) $sqlDate = " AND FROM_UNIXTIME(s.date) BETWEEN '".$date_debut."' AND '".$date_fin."'";
        }
}

//Récupération des SAV en cours
$sql = "SELECT "
        . "f.facturid"
        . ", s.id as sav_id"
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
        . ", DATE_FORMAT(FROM_UNIXTIME(s.date), '%d/%m/%Y %Hh%i') as sav_date_formate"
        . ", s.lignes as liste_produits"
        . ", DATE_FORMAT(FROM_UNIXTIME(s.date),'%Y-%m-%d') as sav_date"
        . ", sf.value as type_litige";
$sql .= " FROM sav s";
$sql .= " INNER JOIN sav_fields sf ON (sf.savid = s.id)";
$sql .= " INNER JOIN facture f ON (f.facturid = s.facturid)";
$sql .= " INNER JOIN utilisateur u ON (u.userid = f.userid)";
$sql .= " INNER JOIN pays p ON (p.paysid = u.paysid)";
$sql .= " WHERE 1";
$sql .= " AND sf.name = 'sav_typelitige'";
$sql .= $sqlType;
$sql .= $sqlStatut;
$sql .= $sqlDate;
$sql .= " GROUP BY s.id";
$sql .= " ORDER BY sf.value"; 
$req = $DB_site->query($sql);
$nbEnr = $DB_site->num_rows($req);
if($nbEnr <= 400){
        $aListeSAV     = array();
        $cpt           = 0;
        $aMarques      = array();
        while($le_sav = $DB_site->fetch_array($req)){

                //Recherche sur site de provenance
                switch($le_sav['site_code']){
                        case '':
                        case 'base':
                                switch($le_sav['langue']){
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
                }

                $nj_jours_creation = round((strtotime(date('Y-m-d')) - strtotime($le_sav['sav_date'])) / 86400);
                $nj_jours_tri      = round((strtotime(date('Y-m-d')) - strtotime($le_sav['sav_date'])) / 86400);

                $tabSuivi = get_suivi($le_sav['facturid']);
                
                $dernier_rappel = '';
                if(!empty($tabSuivi)) $dernier_rappel = $tabSuivi['pour']."<br>".$tabSuivi['dateRappel'];

                //Utilisation de la date de commande pour le tri du tableau
                if(!isset($remNBJours)) $remNBJours = array(0 => array(), 1 => array(), 2 => array(), 3 => array(), 4 => array(), 5 => array());

                //Si aucun rappel, on insère la valeur 0 en début d'index pour qu'ils apparaissent en 1er dans le tableau
                if($tabSuivi['bgcolor'] == 'green'){
                        $nj_jours_tri = intval('1'.$nj_jours_tri.'00');
                } else {
                        $nj_jours_tri = intval('1000'.$nj_jours_tri.'00');
                }

                //Si une date a déjà été utilisée comme index, on ajoute la valeur du compteur pour les différencier
                if(!in_array($nj_jours_tri, $remNBJours[$le_sav['type_litige']])){
                        $remNBJours[$le_sav['type_litige']][] = $nj_jours_tri;
                } else {
                        while(in_array($nj_jours_tri, $remNBJours[$le_sav['type_litige']])){
                                $nj_jours_tri++;
                        }
                }

                //Construction de la ligne du tableau
                $aListeSAV[$le_sav['type_litige']][$nj_jours_tri] = ""
                . "<tr>"
                        . "<td class=\"%td_alt%\"><a href=\"".BACK_URL."/facture.php?action=editer&facturid=".$le_sav['facturid']."&lst_trietat=\" target=\"_blank\">".$le_sav['facturid']."</a><br>".$le_sav['commande_date_formate']."</td>"
                        . "<td class=\"%td_alt%\">".($le_sav['raisonsociale'] != '' ? $le_sav['raisonsociale'].'<br>' : '').ucfirst(mb_strtolower($le_sav['prenom'], 'UTF-8'))." ".mb_strtoupper($le_sav['nom'], 'UTF-8')."<br>".usericon($le_sav['vip'], 20)."</td>"
                        . "<td class=\"%td_alt%\">".ucfirst(mb_strtolower($le_sav['ville'], 'UTF-8')).'<br>'.mb_strtoupper($le_sav['pays_libelle'], 'UTF-8')."</td>"
                        . "<td class=\"%td_alt%\">".$provenance."</td>"
                        . "<td class=\"%td_alt%\">".$le_sav['sav_date_formate']."</td>"
                        . "<td class=\"%td_alt%\"><span style=\"color:".($nj_jours_creation > 30 ? 'red' : 'green')."\">".$nj_jours_creation."</span></td>"
                        . "<td class=\"%td_alt%\">".get_montant_ttc($le_sav['facturid'])." &euro;</td>"
                . "</tr>";

                $cpt++;
        }
} else {
        $message = 'En raison d\'un trop grand nombre de résultats (400+), la recherche ne peut être affichée, à moins d\'être affinée';
}
?>
<h1>Vue d'ensemble des SAV</h1>

<div style="float: left; width: 400px;">
        <table border="0" cellpadding="0" cellspacing="0" class="sav_recap" style="margin: 0 auto;">
                <tr>
                        <td class="entete_td_refonte"></td>
                        <td class="entete_td_refonte">En cours</td>
                        <td class="entete_td_refonte">A clôturer</td>
                </tr>
                <tr>
                        <td class="td_gris1"><strong>Fraude</strong></td>
                        <td class="td_gris1"><?php echo $aRecapSAV[5]['actif']; ?></td>
                        <td class="td_gris1"><?php echo $aRecapSAV[5]['cloture']; ?></td>
                </tr>
        </table>
</div>

<div class="commande_detail" style="width: 500px !important; margin-top: 0;">
        <div style="padding: 10px;">
                <form method="post" action="" style="margin: 0;">
        		<table cellpadding="0" cellspacing="0" class="sav_filtres">
                                <tr>
                                        <td align="center">
                                                <strong>Statut :</strong> 
                                                <select name="sav_statut" style="max-width: 125px;">
                                                        <option value="">Tous</option>
                                                        <option value="0" <?php echo empty($_POST) || $_POST['sav_statut'] == '0' ? 'selected' : ''; ?>>En cours</option>
                                                        <option value="1" <?php echo !empty($_POST) && $_POST['sav_statut'] == '1' ? 'selected' : ''; ?>>A clôturer</option>
                                                        <option value="2" <?php echo !empty($_POST) && $_POST['sav_statut'] == '2' ? 'selected' : ''; ?>>Clôturé</option>
                                                </select>
                                        </td>
                                </tr>
                                <tr>
                                        <td align="center">
                                                <strong>SAV créé entre le <input type="text" name="sav_debut" style="width: 80px;" placeholder="xx/mm/YYYY" value="<?php echo !empty($_POST['sav_debut']) ? $_POST['sav_debut'] : ''; ?>"> et le <input type="text" style="width: 80px;" name="sav_fin" placeholder="xx/mm/YYYY" value="<?php echo !empty($_POST['sav_fin']) ? $_POST['sav_fin'] : ''; ?>"></strong>
                                                <?php if($erreur_date){ ?>
                                                <br>
                                                <span style="color: red; padding-top: 5px;">Erreur de saisie de date. Le filtre n'a pas été pris en compte</span>
                                                <?php } ?>
                                        </td>
                                </tr>
                                <tr>
                                        <td align="center" valign="bottom"><input type="submit" value="Rechercher"></td>
                                </tr>
                        </table>
                </form>
	</div>
</div>
<div style="clear: both;"></div>

<?php if(isset($message)){ ?>
        <p style="text-align: center; font-weight: bold;"><?php echo $message; ?></p>
<?php } else { ?>
        <p style="text-align: center; font-weight: bold;"><?php echo $cpt; ?> résultat(s)</p>
<?php } ?>

<?php 

if(!empty($aListeSAV)){

        foreach($aListeSAV as $sav_type => $le_sav){

                //Type de litige
                switch($sav_type){
                        case 0:
                                $type_litige = 'Avarie de transport';
                                break;
                        case 1:
                                $type_litige = 'Panne';
                                break;
                        case 2:
                                $type_litige = 'Erreur de livraison';
                                break;
                        case 3:
                                $type_litige = 'Rétractation';
                                break;
                        case 4:
                                $type_litige = 'Produit manquant';
                                break;
                        case 5:
                                $type_litige = 'Fraude';
                                break;
                }

                echo ""
                . "<table border=\"0\" class=\"sav_listing\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin: 10px 0 0;\">"
                . "<tr>"
                        . "<td colspan=\"2\" style=\"text-align: left; background-color: #A71145; color: white; font-weight: bold; font-size: 15px;\">".$type_litige."</td>"
                . "</tr>"
                . "<tr>"
                        . "<td class=\"entete_td_refonte\">CMD</td>"
                        . "<td class=\"entete_td_refonte\">Société<br>Client</td>"
                        . "<td class=\"entete_td_refonte\">Dom.</td>"
                        . "<td class=\"entete_td_refonte\">Prov.</td>"
                        . "<td class=\"entete_td_refonte\">Date SAV</td>"
                        . "<td class=\"entete_td_refonte\">NB jours</td>"
                        . "<td class=\"entete_td_refonte\" style=\"width: 90px;\">Total TTC</td>"
                . "</tr>";

                krsort($le_sav);

                $cpt = 0;
                foreach($le_sav as $key => $ligne_sav){
                        echo str_replace('%td_alt%', 'td_gris'.($cpt%2 == 0 ? '1' : '2'), $ligne_sav);
                        $cpt++;
                }

                echo "</table>";
        }
}

include_once(BACK_PATH."/footer.php");