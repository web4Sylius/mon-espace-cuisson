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
	$sqlRecherche .= " f.facturid = '".$_POST['facture_recherche']."'";
	$sqlRecherche .= " OR u.raisonsociale LIKE '%".$_POST['facture_recherche']."%'";
	$sqlRecherche .= " OR u.nom LIKE '%".$_POST['facture_recherche']."%'";
	$sqlRecherche .= " OR u.prenom LIKE '%".$_POST['facture_recherche']."%'";
	$sqlRecherche .= " OR u.tvaintracom LIKE '%".$_POST['facture_recherche']."%'";
	$sqlRecherche .= ")";	
}

if(isset($_POST['facture_debut']) && $_POST['facture_debut'] != '' && isset($_POST['facture_fin']) && $_POST['facture_fin'] != ''){
	$aDebut = explode('/',$_POST['facture_debut']);
	$aFin   = explode('/',$_POST['facture_fin']);

	$sqlRecherche .= " AND f.datefacture BETWEEN '".$aDebut[2].'-'.$aDebut[1].'-'.$aDebut[0]."' AND '".$aFin[2].'-'.$aFin[1].'-'.$aFin[0]."'";
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
	$sql .= $_POST['user_id'];
	$sql .= ", ".$_POST['facture_id'];
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
$sql = "SELECT UNIX_TIMESTAMP(datefacture) as date_tri, DATE_FORMAT(datefacture, '%d/%m/%Y') as commande_date, facturid, p.libelle, u.raisonsociale, u.userid, u.nom, u.prenom, u.tvaintracom";
$sql .= " FROM facture f";
$sql .= " INNER JOIN utilisateur u ON (u.userid = f.userid)";
$sql .= " INNER JOIN pays p ON (p.paysid = u.paysid)";
$sql .= " WHERE u.paysid IN (".implode(',',$aPaysEuro).")";
$sql .= $sqlRecherche;
$sql .= " AND u.tvaintracom != ''"; 
$sql .= " AND f.valide IN (5,8)";
$req = $DB_site->query($sql);
$cpt = 0;

while($row = $DB_site->fetch_array($req)){

	$tva_facture = true;

	//ne récupère que les commandes avec une tva à 0
	$oFacture = new facture($DB_site, $row['facturid']);
	foreach($oFacture->lignes as $ligneid => $lignefacture){
		$oFactureligne = new lignefacture($DB_site, $ligneid);
		if($oFactureligne->tva !== '0'){
			$tva_facture = false;
		}
	}
	if($tva_facture==false){ continue; }
	
	//Récupération de la DEB
	$aFichierDEB = array();
	$sql = "SELECT *, DATE_FORMAT(FROM_UNIXTIME(date), '%d/%m/%Y<br>à %H:%i') as date_depot";
	$sql .= " FROM user_files";
	$sql .= " WHERE facturid = ".$oFacture->facturid;
	$sql .= " AND name LIKE 'DEB%'";
	$aFichierDEB = $DB_site->query_first($sql);
	$lien_deb = '';
	if(is_array($aFichierDEB) && count($aFichierDEB) > 0) $lien_deb = "<a href=\"userfiles/".$aFichierDEB['id'].".".$aFichierDEB['ext']."\" download><img src=\"images/icon/pdf2.png\" style=\"width: 30px;\"></a><br>".$aFichierDEB['date_depot'];

	switch($_GET['tri_critere']){
		case 'date':
			$tri = $row['date_tri'].$oFacture->facturid;
			break;
		case 'cmdid':
			$tri = $oFacture->facturid;
			break;
		case 'pays':
			$tri = clean_key($row['libelle']).$oFacture->facturid;
			break;
		case 'societe':
			$tri = clean_key($row['raisonsociale']).$oFacture->facturid;
			break;
		case 'client':
			$tri = clean_key($row['nom']).$oFacture->facturid;
			break;
		case 'tvaintra':
			$tri = $row['tvaintracom'].$oFacture->facturid;
			break;
		case 'totalht':
			$tri = round($oFacture->totalht).$oFacture->facturid;
			break;
		case 'totalttc':
			$tri = round($oFacture->totalttc).$oFacture->facturid;
			break;
		case 'tva':
			$tri = round($oFacture->totalttc - $oFacture->totalht).$oFacture->facturid;
			break;
	}

	$aDEB[$tri]['commande_date']     = $row['commande_date'];
	$aDEB[$tri]['commande_id']       = $oFacture->facturid;
	$aDEB[$tri]['commande_num']      = "<a href=\"".BACK_URL."/facture.php?action=editer&facturid=".$oFacture->facturid."&lst_trietat=\" target=\"_blank\">".$oFacture->facturid;
	$aDEB[$tri]['commande_pays']     = $row['libelle'];
	$aDEB[$tri]['commande_societe']  = $row['raisonsociale'];
	$aDEB[$tri]['commande_userid']   = $row['userid'];
	$aDEB[$tri]['commande_client']   = $row['nom'].' '.$row['prenom'];
	$aDEB[$tri]['commande_tvaintra'] = $row['tvaintracom'];
	$aDEB[$tri]['commande_totalht']  = number_format($oFacture->totalht,2,',',' ').' €';
	$aDEB[$tri]['commande_totalttc'] = number_format($oFacture->totalttc,2,',',' ').' €';
	$aDEB[$tri]['commande_tva']      = number_format($oFacture->totalttc - $oFacture->totalht,2,',',' ').' €';
	$aDEB[$tri]['commande_deb']      = $oFacture->totalttc == 0 ? '<strong>Pas de DEB</strong>' : $lien_deb;

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

include_once(BACK_PATH."/header.php");
include_once(BACK_PATH."/menu.php");

?>
<div class="commande_detail" style="width: 500px !important; margin-top: 0;">
    <div style="padding: 10px;">
        <form method="post" action="deb_export.php" style="margin: 0;">
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
                	<td><a href="deb_export.php">Réinitialiser</a></td>
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
        <td class="entete_td_refonte"><a href="?tri_critere=cmdid&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'cmdid' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">N° CMD</a></td>
        <td class="entete_td_refonte"><a href="?tri_critere=pays&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'pays' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">Pays</a></td>
        <td class="entete_td_refonte" style="width:100px;"><a href="?tri_critere=societe&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'societe' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">Société</a></td>
        <td class="entete_td_refonte"><a href="?tri_critere=client&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'client' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">Client</a></td>
        <td class="entete_td_refonte"><a href="?tri_critere=tvaintra&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'tvaintra' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">N° TVA intra</a></td>
        <td class="entete_td_refonte"><a href="?tri_critere=totalht&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'totalht' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">Total HT</a></td>
        <td class="entete_td_refonte"><a href="?tri_critere=totalttc&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'totalttc' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">Total TTC</a></td>
        <td class="entete_td_refonte"><a href="?tri_critere=tva&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'tva' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">TVA</a></td>
        <td class="entete_td_refonte">DEB</td>
    </tr>
    <?php
    $cpt = 0;
    foreach($aDEB as $la_commande){
    	
    	$td_gris = $cpt%2 == 0 ? '1' : '2';
    ?>
    	<tr>
			<td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_date']; ?></td>
	        <td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_num']; ?></td>
	        <td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_pays']; ?></td>
	        <td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_societe']; ?></td>
	        <td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_client']; ?></td>
	        <td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_tvaintra']; ?></td>
	        <td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_totalht']; ?></td>
	        <td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_totalttc']; ?></td>
	        <td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_tva']; ?></td>
	        <td class="td_gris<?php echo $td_gris; ?>">
	        	<form method="post" action="" name="form_deb<?php echo $la_commande['commande_id']; ?>" enctype="multipart/form-data" style="margin:0; padding: 0;">
	        		<input type="hidden" name="user_id" value="<?php echo $la_commande['commande_userid']; ?>">
	        		<input type="hidden" name="facture_id" value="<?php echo $la_commande['commande_id']; ?>">
	        		<?php echo !empty($la_commande['commande_deb']) ? $la_commande['commande_deb'] : "<label for=\"input".$la_commande['commande_id']."\" class=\"label_deb\" >Ajouter</label><input type=\"file\" name=\"facture_debfichier\" id=\"input".$la_commande['commande_id']."\" style=\"display: none;\" onchange=\"document.form_deb".$la_commande['commande_id'].".submit();\"/>"; ?>
	        	</form>
	        </td>
	   	</tr>
    <?php
    	$cpt++;
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
