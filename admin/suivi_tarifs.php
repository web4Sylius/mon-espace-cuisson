<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/admin/admin_global.php');

$export_csv = false;
if(isset($_GET['export']) && $_GET['export'] == 1) $export_csv = true;

if($export_csv){
        require_once(BACK_PATH."/export/xlexport.php");

        $csv = new xlexport("csv");
        $csv->name       = "suivis_tarifs";
        $csv->csvsep     = ";";
}

$DB_site = clone $DB_site2;

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

if(!$export_csv){
        include_once(BACK_PATH."/header.php");
        include_once(BACK_PATH."/menu.php");
}

$_POST['marque_id'] = array(6,12,13); //Climadiff & Avintage

$sql = "SELECT ah.artid, a.artcode, a.artcode_eurocave, m.libelle as marque_nom, ah.date as date_unix, DATE_FORMAT(FROM_UNIXTIME(ah.date), '%d/%m/%Y') as date_tarif, ah.value";
$sql .= " FROM article_history ah";
$sql .= " INNER JOIN article a ON (a.artid = ah.artid)";
$sql .= " INNER JOIN article_site asite ON (asite.artid = a.artid)";
$sql .= " INNER JOIN article_marque am ON (am.artid = a.artid)";
$sql .= " INNER JOIN marque m ON (m.marqueid = am.marqueid)";
$sql .= " WHERE 1";
$sql .= " AND a.artid NOT IN (SELECT lotid FROM article_lot)";
//$sql .= " AND am.marqueid IN (".implode(',',$_POST['marque_id']).")";
$sql .= " AND ah.`key` = 'paht'";
$sql .= " AND ah.`value` != ''";
$sql .= " AND asite.site_code = 'base'";
$sql .= " AND asite.active IN (1,0)";
$sql .= " ORDER BY m.libelle, ah.artid";
$req = $DB_site->query($sql);
while($le_tarif = $DB_site->fetch_array($req)){
        $aPaHT['tarif_date']    = $le_tarif['date_tarif'];
        $aPaHT['tarif_montant'] = $le_tarif['value'];

        $aHistoriquePrix[$le_tarif['artid']]['article_ref']                          = $le_tarif['artcode'];
        $aHistoriquePrix[$le_tarif['artid']]['article_ref_fab']                      = $le_tarif['artcode_eurocave'];
        $aHistoriquePrix[$le_tarif['artid']]['article_marque']                       = $le_tarif['marque_nom'];
        $aHistoriquePrix[$le_tarif['artid']]['article_paht'][$le_tarif['date_unix']] = $aPaHT;
}

$sql = "SELECT tp.artid, a.artcode, a.artcode_eurocave, m.libelle as marque_nom, tp.du as date_unix, DATE_FORMAT(FROM_UNIXTIME(tp.du), '%d/%m/%Y') as date_debut_tarif, DATE_FORMAT(FROM_UNIXTIME(tp.au), '%d/%m/%Y') as date_fin_tarif, tp.paht";
$sql .= " FROM temp_price tp";
$sql .= " INNER JOIN article a ON (a.artid = tp.artid)";
$sql .= " INNER JOIN article_site asite ON (asite.artid = a.artid)";
$sql .= " INNER JOIN article_marque am ON (am.artid = a.artid)";
$sql .= " INNER JOIN marque m ON (m.marqueid = am.marqueid)";
$sql .= " WHERE 1";
$sql .= " AND a.artid NOT IN (SELECT lotid FROM article_lot)";
$sql .= " AND am.marqueid IN (".implode(',',$_POST['marque_id']).")";
$sql .= " AND tp.site_code = 'base'";
$sql .= " AND asite.site_code = 'base'";
$sql .= " AND asite.active IN (1,0)";
$sql .= " ORDER BY m.libelle, tp.artid";
$req = $DB_site->query($sql);
while($le_tarif = $DB_site->fetch_array($req)){
        $aPaHT['tarif_date']    = 'Du '.$le_tarif['date_debut_tarif'].' au '.$le_tarif['date_fin_tarif'];
        $aPaHT['tarif_montant'] = $le_tarif['paht'];

        $aHistoriquePrix[$le_tarif['artid']]['article_ref']                          = $le_tarif['artcode'];
        $aHistoriquePrix[$le_tarif['artid']]['article_ref_fab']                      = $le_tarif['artcode_eurocave'];
        $aHistoriquePrix[$le_tarif['artid']]['article_marque']                       = $le_tarif['marque_nom'];
        $aHistoriquePrix[$le_tarif['artid']]['article_paht'][$le_tarif['date_unix']] = $aPaHT;
}

if(!$export_csv){
?>
        <h1>Suivis des prix d'achat des marques</h1>

        <input type="button" value="Exporter" onclick="location.href='suivi_tarifs.php?export=1'">
<?php
        foreach($aHistoriquePrix as $article_id => $l_article){

                ksort($l_article['article_paht']);
?>
                <table style="border: none; margin: 5px 0; border-collapse: collapse;">
                        <tr>
                                <td style="border: 1px solid; text-align: center; background-color: #AA1147; color: white; padding: 5px 10px; width: 100px; font-size: 10px;">Marque</td>
                                <td style="border: 1px solid; text-align: center; background-color: #AA1147; color: white; padding: 5px 10px; width: 100px; font-size: 10px;">Réf. fabricant</td>
                                <td style="border: 1px solid; text-align: center; background-color: #AA1147; color: white; padding: 5px 10px; width: 100px; font-size: 10px;">Réf. Acilyon</td>
                                <?php
                                $rem_last_tarif = '';
                                foreach($l_article['article_paht'] as $le_tarif){

                                        if(strpos($le_tarif['tarif_montant'], 'base') !== false){
                                                
                                                preg_match('#[0-9]+#', $le_tarif['tarif_montant'], $matches);
                                                
                                                $article_paht = $matches[0];
                                        } else {
                                                $article_paht = $le_tarif['tarif_montant'];
                                        }

                                        if($rem_last_tarif == '' || $rem_last_tarif != $article_paht){
                                                $rem_last_tarif = $article_paht;
                                ?>
                                                <td style="border: 1px solid; text-align: center; background-color: #A88D5C; color: white; padding: 5px 10px; max-width: 110px; font-size: 10px;"><?php echo $le_tarif['tarif_date']; ?></td>
                                <?php
                                        }
                                }
                                ?>
                        </tr>
                        <tr>
                                <td style="border: 1px solid; text-align: center; padding: 5px 10px; width: 100px; font-size: 10px;"><?php echo $l_article['article_marque']; ?></td>
                                <td style="border: 1px solid; text-align: center; padding: 5px 10px; width: 100px; font-size: 10px;"><?php echo $l_article['article_ref_fab']; ?></td>
                                <td style="border: 1px solid; text-align: center; padding: 5px 10px; width: 100px; font-size: 10px;"><?php echo $l_article['article_ref']; ?></td>
                                <?php
                                $rem_last_tarif = '';
                                foreach($l_article['article_paht'] as $le_tarif){ 

                                        if(strpos($le_tarif['tarif_montant'], 'base') !== false){
                                                
                                                preg_match('#[0-9]+#', $le_tarif['tarif_montant'], $matches);
                                                
                                                $article_paht = $matches[0];
                                        } else {
                                                $article_paht = $le_tarif['tarif_montant'];
                                        }

                                        if($rem_last_tarif == '' || $rem_last_tarif != $article_paht){
                                                $rem_last_tarif = $article_paht;
                                        ?>
                                                <td style="border: 1px solid; text-align: center; padding: 5px 10px; max-width: 110px; font-size: 10px;"><?php echo @number_format($article_paht,2,',',' '); ?> &euro;</td>
                                <?php
                                        }
                                }
                                ?>
                        </tr>
                </table>
<?php
        }
} else {

        foreach($aHistoriquePrix as $article_id => $l_article){

                ksort($l_article['article_paht']);

                $csvRow  = array();
                $csvRow2 = array();

                $csvRow  = array('Marque', 'Réf. fabricant', 'Réf. Acilyon');
                $csvRow2 = array($l_article['article_marque'], $l_article['article_ref_fab'], $l_article['article_ref']);

                $rem_last_tarif = '';
                foreach($l_article['article_paht'] as $le_tarif){

                        if(strpos($le_tarif['tarif_montant'], 'base') !== false){
                                
                                preg_match('#[0-9]+#', $le_tarif['tarif_montant'], $matches);
                                
                                $article_paht = $matches[0];
                        } else {
                                $article_paht = $le_tarif['tarif_montant'];
                        }

                        if($rem_last_tarif == '' || $rem_last_tarif != $article_paht){
                                $rem_last_tarif = $article_paht;

                                array_push($csvRow, $le_tarif['tarif_date']);
                                array_push($csvRow2, number_format($article_paht,2,',',' '));
                        }
                }

                $csv->addrow($csvRow);
                $csv->addrow($csvRow2);
                $csv->addrow(array());
        }

        $csv->export();
}
?>

