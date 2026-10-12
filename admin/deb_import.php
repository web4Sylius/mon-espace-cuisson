<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/admin/admin_global.php');
require_once(SITE_PATH.'/class/facture.class.php');

$DB_site = clone $DB_site2;

if(!isset($_GET['tri_critere']) || $_GET['tri_critere'] == '') $_GET['tri_critere'] = 'date';
if(!isset($_GET['tri_sens']) || $_GET['tri_sens'] == '') $_GET['tri_sens'] = 'desc';

$aPaysEuro = array(64,10,17,26,42,45,153,53,56,66,75,81,83,98,99,105,118,132,133,44,136,148,149,157,186);

//Sélection des pays
$sql = "SELECT paysid, libelle";
$sql .= " FROM pays";
$sql .= " WHERE paysid IN (".implode(',',$aPaysEuro).")";
$sql .= " ORDER BY libelle";
$req = $DB_site->query($sql);
$optPays = '';
while($row = $DB_site->fetch_array($req)){
	$optPays .= "<option value=\"".$row['paysid']."\" ".(isset($_POST['paysid']) && $row['paysid'] == $_POST['paysid'] ? 'selected' : '').">".$row['libelle']."</option>";
}

//Filtres recherche
if(isset($_POST['facture_pays']) && $_POST['facture_pays'] != 0){
	$aPaysEuro = array($_POST['facture_pays']);
}

$sqlRecherche = '';
if(isset($_POST['facture_recherche']) && $_POST['facture_recherche'] != ''){
	$sqlRecherche .= " AND (";
	$sqlRecherche .= " ff.numero = '".$_POST['facture_recherche']."'";
	$sqlRecherche .= " OR ff.fournisseur LIKE '%".$_POST['facture_recherche']."%'";
	$sqlRecherche .= " OR fc.tvaintra LIKE '%".$_POST['facture_recherche']."%'";
	$sqlRecherche .= ")";	
}

if(isset($_POST['facture_debut']) && $_POST['facture_debut'] != '' && isset($_POST['facture_fin']) && $_POST['facture_fin'] != ''){
	$aDebut = explode('/',$_POST['facture_debut']);
	$aFin   = explode('/',$_POST['facture_fin']);

	$sqlRecherche .= " AND ff.date_facture BETWEEN '".strtotime($_POST['facture_debut'])."' AND '".strtotime($_POST['facture_fin'])."'";
	$sqlRecherche_deb = " AND date BETWEEN '".strtotime($_POST['facture_debut'])."' AND '".strtotime($_POST['facture_fin'])."'";
}
else{
	$sqlRecherche .= " AND ff.date_facture BETWEEN '1640991600' AND '".time()."'";
	$sqlRecherche_deb = " AND date BETWEEN '1640991600' AND '".time()."'";
}

if(isset($_FILES['facture_debfichier']) && $_FILES['facture_debfichier'] != ''){
	$ext_file = pathinfo($_FILES['facture_debfichier']['name'], PATHINFO_EXTENSION);
	//Insertion du fichier DEB dans la BDD
	$sql = "INSERT INTO user_files (";
	$sql .= "userid";
	$sql .= ", facturid";
	$sql .= ", `date`";
	$sql .= ", utilisateur";
	$sql .= ", name";
	$sql .= ", size";
	$sql .= ", ext";
	$sql .= ", state";
	$sql .= ") VALUES (";
	$sql .= "0";
	$sql .= ", 0";
	$sql .= ", UNIX_TIMESTAMP()";
	$sql .= ", '".$_COOKIE['admin_username']."'";
	$sql .= ", '".$_FILES['facture_debfichier']['name']."'";
	$sql .= ", ".$_FILES['facture_debfichier']['size'];
	$sql .= ", '".$ext_file."'";
	$sql .= ", 1";
	$sql .= ")";
	$DB_site->query($sql);
	$file_id = $DB_site->insert_id();

	move_uploaded_file($_FILES['facture_debfichier']['tmp_name'], BACK_PATH.'/userfiles/'.$file_id.'.'.$ext_file);
}

//Récupération des DEB
$sql = "SELECT date_facture, numero, ff.fournisseur, ff.montant_ttc, ff.montant_ht, p.libelle, fc.tvaintra";
$sql .= " FROM facture_fournisseur ff";
$sql .= " INNER JOIN fournisseur f ON (f.societe = ff.fournisseur)";
$sql .= " INNER JOIN pays p ON (p.paysid = f.paysid)";
$sql .= " INNER JOIN fournisseur_condition fc ON (fc.societe = ff.fournisseur)";
$sql .= " WHERE f.paysid IN (".implode(',',$aPaysEuro).")";
$sql .= $sqlRecherche;
$sql .= " AND ff.tva = '0'";
$req_list = $DB_site->query($sql);
$cpt = 0;

if($DB_site->num_rows($req_list) > 0){
	while($row = $DB_site->fetch_array($req_list)){

	//Récupération de la DEB
		$aFichierDEB = array();
		$sql = "SELECT *, DATE_FORMAT(FROM_UNIXTIME(date), '%d/%m/%Y<br>à %H:%i') as date_depot";
		$sql .= " FROM user_files";
		$sql .= " WHERE name = 'DEB ".$row['numero'].".pdf'";
		$sql .= $sqlRecherche_deb;
		$aFichierDEB = $DB_site->query_first($sql);
		$lien_deb = '';
		if(is_array($aFichierDEB) && count($aFichierDEB) > 0) $lien_deb = "<a href=\"userfiles/".$aFichierDEB['id'].".".$aFichierDEB['ext']."\" download><img src=\"images/icon/pdf2.png\" style=\"width: 30px;\"></a><br>".$aFichierDEB['date_depot'];

		switch($_GET['tri_critere']){
			case 'date':
			$tri = $row['date_facture'].$row['numero'];
			break;
			case 'cmdid':
			$tri = $row['numero'];
			break;
			case 'pays':
			$tri = clean_key($row['libelle']).$row['numero'];
			break;
			case 'tvaintra':
			$tri = $row['tvaintra'].$row['numero'];
			break;
			case 'societe':
			$tri = clean_key($row['fournisseur']).$row['numero'];
			break;
			case 'totalht':
			$tri = round($row['montant_ht']).$row['numero'];
			break;
			case 'totalttc':
			$tri = round($row['montant_ttc']).$row['numero'];
			break;
			case 'tva':
			$tri = round($row['montant_ttc'] - $row['montant_ht']).$row['numero'];
			break;
		}

		$aDEB[$tri]['commande_date']     = date("d/m/Y",$row['date_facture']);
		$aDEB[$tri]['commande_id']       = $row['numero'];
		$aDEB[$tri]['commande_num']      = $row['numero'];
		$aDEB[$tri]['commande_pays']     = $row['libelle'];
		$aDEB[$tri]['commande_tvaintra'] = $row['tvaintra'];
		$aDEB[$tri]['commande_societe']  = $row['fournisseur'];
		$aDEB[$tri]['commande_totalht']  = $row['montant_ht'].' €';
		$aDEB[$tri]['commande_totalttc'] = $row['montant_ttc'].' €';
		$aDEB[$tri]['commande_tva']      = number_format($row['montant_ttc'] - $row['montant_ht'],2,',',' ').' €';
		$aDEB[$tri]['commande_deb']      = $row['montant_ttc'] == 0 ? '<strong>Pas de DEB</strong>' : $lien_deb;

		$cpt++;
	}



	switch($_GET['tri_sens']){

		case 'asc':
		ksort($aDEB);
		break;
		case 'desc':
		krsort($aDEB);
		break;
	}
}

include_once(BACK_PATH."/header.php");
include_once(BACK_PATH."/menu.php");

?>
<div class="commande_detail" style="width: 500px !important; margin-top: 0;">
    <div style="padding: 10px;">
        <form method="post" action="deb_import.php" style="margin: 0;">
			<table cellpadding="0" cellspacing="0" class="sav_filtres">
                <tr>
                    <td>
                        <strong>Recherche :</strong> 
                    </td>
                    <td>
                        <input type="text" name="facture_recherche" placeholder="N° CMD / Société / Client / n° TVA intra." style="width: 200px;">
                    </td>
                    <td>
                        <strong>Pays :</strong>
                    </td>
                    <td>
                        <select name="facture_pays">
                            <option value="0">-- Tous --</option>
                            <?php echo $optPays; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" align="center">
                        <strong>Factures du <input type="text" name="facture_debut" style="width: 80px;" placeholder="xx/mm/YYYY" value="<?php echo !empty($_POST['facture_debut']) ? $_POST['facture_debut'] : ''; ?>"> au <input type="text" style="width: 80px;" name="facture_fin" placeholder="xx/mm/YYYY" value="<?php echo !empty($_POST['facture_fin']) ? $_POST['facture_fin'] : ''; ?>"></strong>
                        <?php if($erreur_date){ ?>
                        <br>
                        <span style="color: red; padding-top: 5px;">Erreur de saisie de date. Le filtre n'a pas été pris en compte</span>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                	<td><a href="deb_import.php">Réinitialiser</a></td>
                	<td colspan="3" align="right"><input type="submit" value="Rechercher"></td>
                </tr>	
	        </table>
        </form>
	</div>
</div>
<div style="clear: both;"></div>
<p style="text-align: center; font-weight: bold;"><?php echo $cpt; ?> résultat(s)</p>
<table border="0" class="sav_listing" cellpadding="0" cellspacing="0" style="margin: 10px 0 0;">
    <tr>
        <td class="entete_td_refonte"><a href="?tri_critere=date&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'date' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">Date facture</a></td>
        <td class="entete_td_refonte"><a href="?tri_critere=cmdid&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'cmdid' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">N° facture</a></td>
        <td class="entete_td_refonte"><a href="?tri_critere=pays&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'pays' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">Pays</a></td>
        <td class="entete_td_refonte" style="width:100px;"><a href="?tri_critere=societe&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'societe' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">Fournisseur</a></td>
        <td class="entete_td_refonte"><a href="?tri_critere=tvaintra&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'tvaintra' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">N° TVA intra</a></td>
        <td class="entete_td_refonte"><a href="?tri_critere=totalht&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'totalht' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">Total HT</a></td>
        <td class="entete_td_refonte"><a href="?tri_critere=totalttc&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'totalttc' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">Total TTC</a></td>
        <td class="entete_td_refonte"><a href="?tri_critere=tva&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'tva' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">TVA</a></td>
        <td class="entete_td_refonte">DEB</td>
    </tr>
    <?php
    $cpt = 0;
    if($DB_site->num_rows($req_list) > 0){
    	foreach($aDEB as $la_commande){

    		$td_gris = $cpt%2 == 0 ? '1' : '2';
    		?>
    		<tr>
    			<td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_date']; ?></td>
    			<td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_num']; ?></td>
    			<td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_pays']; ?></td>
    			<td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_societe']; ?></td>
    			<td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_tvaintra']; ?></td>
    			<td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_totalht']; ?></td>
    			<td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_totalttc']; ?></td>
    			<td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_tva']; ?></td>
    			<td class="td_gris<?php echo $td_gris; ?>">
    				<form method="post" action="" name="form_deb" enctype="multipart/form-data" style="margin:0; padding: 0;">
	        		<input type="hidden" name="facture_id" value="<?php echo $la_commande['commande_id']; ?>">
	        		<?php echo !empty($la_commande['commande_deb']) ? $la_commande['commande_deb'] : "<label for=\"inputfile\" class=\"label_deb\" >Ajouter</label><input type=\"file\" name=\"facture_debfichier\" id=\"inputfile\" style=\"display: none;\" onchange=\"this.form.submit();\"/>"; ?>
	        	</form>
    			</td>
    		</tr>
    		<?php
    		$cpt++;
    	}
    }
    ?>
</table>
<?php

include_once(BACK_PATH."/footer.php");

function clean_key($chaine){
	$caracteres = array('a', 'Á' => 'a', 'Â' => 'a', 'Ä' => 'a', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', '@' => 'a',
		'È' => 'e', 'É' => 'e', 'Ê' => 'e', 'Ë' => 'e', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', '€' => 'e',
		'Ì' => 'i', 'Í' => 'i', 'Î' => 'i', 'Ï' => 'i', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
		'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Ö' => 'o', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o',
		'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'µ' => 'u',
		'Œ' => 'oe', 'œ' => 'oe',
		'$' => 's');

	$chaine = strtr($chaine, $caracteres);
	$chaine = preg_replace('#[^A-Za-z0-9]+#', '-', $chaine);
	$chaine = trim($chaine, '-');
	$chaine = strtolower($chaine);

	return $chaine;
}
