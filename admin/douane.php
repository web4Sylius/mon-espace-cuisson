<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/admin/admin_global.php');
require_once(SITE_PATH.'/class/facture.class.php');

$DB_site = clone $DB_site2;

$superusers = array("philippe", "cc", "benoit", "ariel", "cecile");
$is_superuser = in_array($user_info['username'], $superusers);

if(!isset($_GET['tri_critere']) || $_GET['tri_critere'] == ''){
	$_GET['tri_critere'] = 'date';
}
if(!isset($_GET['tri_sens']) || $_GET['tri_sens'] == ''){
	$_GET['tri_sens'] = 'desc';
}
if(isset($_GET['facture_recherche']) && $_GET['facture_recherche'] != ''){
	$_POST['facture_recherche'] = $_GET['facture_recherche'];
}
if(isset($_GET['facture_pays']) && $_GET['facture_pays'] != ''){
	$_POST['facture_pays'] = $_GET['facture_pays'];
}
if(isset($_GET['facture_trspt']) && $_GET['facture_trspt'] != ''){
	$_POST['facture_trspt'] = $_GET['facture_trspt'];
}
if(isset($_GET['facture_debut']) && $_GET['facture_debut'] != ''){
	$_POST['facture_debut'] = $_GET['facture_debut'];
}
if(isset($_GET['facture_fin']) && $_GET['facture_fin'] != ''){
	$_POST['facture_fin'] = $_GET['facture_fin'];
}
if(isset($_GET['facture_yesdoc']) && $_GET['facture_yesdoc'] != ''){
	$_POST['facture_yesdoc'] = $_GET['facture_yesdoc'];
}

//Sélection des pays
$sql = "SELECT paysid, libelle";
$sql .= " FROM pays";
$sql .= " WHERE cee=0";
$sql .= " ORDER BY libelle";
$req = $DB_site->query($sql);
$optPays = '';
$listepays=array();
while($row = $DB_site->fetch_array($req)){
    $listepays[]=$row['paysid'];
	$optPays .= "<option value=\"".$row['paysid']."\" ".(isset($_POST['facture_pays']) && $row['paysid'] == $_POST['facture_pays'] ? 'selected' : '').">".$row['libelle']."</option>";
}

$sqlRecherche = '';

//Filtres recherche
if(isset($_POST['facture_pays']) && $_POST['facture_pays'] != 0){
	$sqlRecherche.=" AND u.paysid=".intval($_POST['facture_pays']);
}

if(isset($_POST['facture_recherche']) && $_POST['facture_recherche'] != ''){
	$sqlRecherche .= " AND (";
	$sqlRecherche .= " f.facturid = '".$_POST['facture_recherche']."'";
	$sqlRecherche .= " OR u.raisonsociale LIKE '%".$_POST['facture_recherche']."%'";
	$sqlRecherche .= " OR u.nom LIKE '%".$_POST['facture_recherche']."%'";
	$sqlRecherche .= " OR u.prenom LIKE '%".$_POST['facture_recherche']."%'";
	$sqlRecherche .= ")";	
}

if(isset($_POST['facture_debut'])){
	$ddebut=date_create_from_format("d/m/Y",$_POST['facture_debut']);
	$ddebut->settime(0,0,0);
}else{
	$ddebut=new DateTime("1 year ago");
}
if(isset($_POST['facture_fin'])){
	$dfin=date_create_from_format("d/m/Y",$_POST['facture_fin']);
	$dfin->settime(0,0,0);
}else{
	$dfin=new DateTime();
}

$sqlRecherche .= " AND f.datefacture BETWEEN '".$ddebut->format("Y-m-d")."' AND '".$dfin->format("Y-m-d")."'";


if(isset($_FILES['facture_debfichier']) && $_FILES['facture_debfichier'] != ''){
        $sql="SELECT * FROM user_files WHERE facturid=".$_POST['facture_id']." AND name='Douane' AND ext=''";
        $dir=$DB_site->query_first($sql);
        if($dir==""){
                $DB_site->query("INSERT INTO user_files SET facturid=".$_POST['facture_id'].", userid=".$_POST['user_id'].", date=".time().", utilisateur='".$user_info['username']."', name='Douane', state=1");
                $parent=$DB_site->insert_id();
        }else
                $parent=$dir['id'];
        
	$ext_file = strtolower(pathinfo($_FILES['facture_debfichier']['name'], PATHINFO_EXTENSION));
	if($ext_file=="pdf"){
		//Insertion du fichier DEB dans la BDD
		$sql = "INSERT INTO user_files (";
		$sql .= "userid";
		$sql .= ", facturid";
		$sql .= ", `parent`";
		$sql .= ", `date`";
		$sql .= ", utilisateur";
		$sql .= ", name";
		$sql .= ", size";
		$sql .= ", ext";
		$sql .= ", state";
		$sql .= ") VALUES (";
		$sql .= $_POST['user_id'];
		$sql .= ", ".$_POST['facture_id'];
		$sql .= ", ".$parent;
		$sql .= ", UNIX_TIMESTAMP()";
		$sql .= ", '".$_COOKIE['admin_username']."'";
		$sql .= ", 'Douane ".$_POST['facture_id']."'";
		$sql .= ", ".$_FILES['facture_debfichier']['size'];
		$sql .= ", '".$ext_file."'";
		$sql .= ", 1";
		$sql .= ")";
		$DB_site->query($sql);
		$file_id = $DB_site->insert_id();
		move_uploaded_file($_FILES['facture_debfichier']['tmp_name'], BACK_PATH.'/userfiles/'.$file_id.'.'.$ext_file);
	}
}

if(isset($_POST['nodoc'])){
     $DB_site->query("UPDATE facture SET douane=".(intval(isset($_POST['nodocactive'])))." WHERE facturid=".intval($_POST['nodoc']));
}
$aDEB= array();
//Récupération des DEB
$sql = "SELECT UNIX_TIMESTAMP(datefacture) as date_tri, DATE_FORMAT(datefacture, '%d/%m/%Y') as commande_date, facturid, p.libelle, u.raisonsociale, u.userid, u.nom, u.prenom, u.tvaintracom";
$sql .= " FROM facture f";
$sql .= " INNER JOIN utilisateur u ON (u.userid = f.userid)";
$sql .= " INNER JOIN pays p ON (p.paysid = u.paysid)";
$sql .= " WHERE u.paysid IN (".implode(',',$listepays).")";
$sql .= $sqlRecherche;
$sql .= " AND f.valide IN (5,8)";
$req = $DB_site->query($sql);
$cpt = 0;
while($row = $DB_site->fetch_array($req)){
	$oFacture = new facture($DB_site, $row['facturid']);
	//Récupération du fichier
	$aFichierDEB = array();
	$sql = "SELECT *, DATE_FORMAT(FROM_UNIXTIME(date), '%d/%m/%Y<br>à %H:%i') as date_depot";
	$sql .= " FROM user_files";
	$sql .= " WHERE ext='pdf' AND parent = ";
	$sql .= " (SELECT id FROM user_files WHERE facturid = ".$oFacture->facturid." AND name LIKE 'Douane')";
	$lien_deb = array();
	$aFichierDEBs = $DB_site->query($sql);
	while($aFichierDEB=$DB_site->fetch_array($aFichierDEBs)){
		if(is_array($aFichierDEB) && count($aFichierDEB) > 0){
			$lien_deb[]="<a href=\"userfiles/".$aFichierDEB['id'].".".$aFichierDEB['ext']."\" download><img src=\"images/icon/pdf2.png\" style=\"width: 30px;\"></a><br>".$aFichierDEB['date_depot'];
		}
	}
	$trspt=array();
	$sqltrspt="";
	if(isset($_POST['facture_trspt']) && $_POST['facture_trspt']!=""){
		$sqltrspt=" AND destinataire LIKE '%".mysqli_real_escape_string($DB_site->link_id,$_POST['facture_trspt'])."%'";
	}
	$sql="SELECT destinataire FROM envoi_fournisseur WHERE facturid=".$oFacture->facturid." AND type='trspt' AND destinataire!='0'".$sqltrspt;
	$reqtrspt=$DB_site->query($sql);
	if($sqltrspt!="" && $DB_site->num_rows($reqtrspt)==0){
		continue;
	}
	while($ef=$DB_site->fetch_array($reqtrspt)){
        $trspt[]="<span>".$ef['destinataire']."</span><br>";
	}
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
		case 'totalht':
			$tri = round($oFacture->totalht).$oFacture->facturid;
			break;
		case 'totalttc':
			$tri = round($oFacture->totalttc).$oFacture->facturid;
			break;
		case 'tva':
			$tri = round($oFacture->totalttc - $oFacture->totalht).$oFacture->facturid;
			break;
		/*
		case 'trspt':
			$tri=trim(strip_tags($trspt).$oFacture->facturid);
			break;
		*/
	}
	$aDEB[$tri]['commande_date']     = $row['commande_date'];
	$aDEB[$tri]['commande_id']       = $oFacture->facturid;
	$aDEB[$tri]['commande_num']      = "<a href=\"facture.php?action=editer&facturid=".$oFacture->facturid."&lst_trietat=\" target=\"_blank\">".$oFacture->facturid;
	$aDEB[$tri]['commande_pays']     = $row['libelle'];
	$aDEB[$tri]['commande_societe']  = $row['raisonsociale'];
	$aDEB[$tri]['commande_userid']   = $row['userid'];
	$aDEB[$tri]['commande_client']   = $row['nom'].' '.$row['prenom'];
	$aDEB[$tri]['commande_trspt']    = $trspt;
	$aDEB[$tri]['commande_totalht']  = number_format($oFacture->totalht,2,',',' ').' €';
	$aDEB[$tri]['commande_totalttc'] = number_format($oFacture->totalttc,2,',',' ').' €';
	$aDEB[$tri]['commande_tva']      = number_format($oFacture->totalttc - $oFacture->totalht,2,',',' ').' €';
	$aDEB[$tri]['commande_deb']      = $lien_deb;
	$aDEB[$tri]['commande_douane']   = $oFacture->douane;
	$cpt++;
}



uksort($aDEB,function($a,$b){
	return strcmp($a,$b);
});

if($_GET['tri_sens']=='desc'){
	$aDEB=array_reverse($aDEB,true);
}
include_once(BACK_PATH."/header.php");
include_once(BACK_PATH."/menu.php");

// print_r($_POST);

$criterepost="facture_recherche=".$_POST['facture_recherche']."&amp;facture_pays=".$_POST['facture_pays']."&amp;facture_trspt=".$_POST['facture_trspt'];
if(isset($_POST['facture_debut'])){
	$criterepost.="&amp;facture_debut=" . $_POST['facture_debut'];
}
if(isset($_POST['facture_fin'])){
	$criterepost.="&amp;facture_fin=" . $_POST['facture_fin'];
}
if(isset($_POST['facture_yesdoc'])){
	$criterepost.="&amp;facture_yesdoc=" . $_POST['facture_yesdoc'];
}

if($_POST['facture_yesdoc']=="1"){
	$checked="checked";
} else {
	$checked="";
}

$checkbox_yesdoc="<input type=\"checkbox\" name=\"facture_yesdoc\" value=\"1\" ".$checked."> uniquement avec document à uploader";

?>
<div class="commande_detail">
    <div style="padding: 10px;">
        <form method="post" style="margin: 0;">
                <table cellpadding="0" cellspacing="0" class="sav_filtres">
                <tr>
                    <td>
                        <strong>Recherche :</strong> 
                    </td>
                    <td>
                        <input type="text" name="facture_recherche" placeholder="N° CMD / Société / Client" value="<?=$_POST['facture_recherche']; ?>" style="width: 200px;">
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
                    <td>
                        <strong>Transporteur :</strong> 
                    </td>
                    <td>
                        <input type="text" name="facture_trspt" value="<?=$_POST['facture_trspt']; ?>" style="width: 200px;">
                    </td>
                    <td colspan="4" align="center">
                        <strong>Factures
						du <input type="text" id="facture_debut" name="facture_debut" placeholder="xx/mm/YYYY" value="<?php echo $ddebut->format("d/m/Y"); ?>" onclick="calendrier_show(this.id,this.value)" readonly> 
						au <input type="text" id="facture_fin" name="facture_fin" placeholder="xx/mm/YYYY" value="<?php echo $dfin->format("d/m/Y"); ?>" onclick="calendrier_show(this.id,this.value)" readonly>
						</strong>
                        <?php
						if($erreur_date){
							?>
							<br><span style="color: red; padding-top: 5px;">Erreur de saisie de date. Le filtre n'a pas été pris en compte</span>
							<?php
						}
						?>
                    </td>
                </tr>
                <tr>
                	<td><a href="?">Réinitialiser</a></td>
					<td>
						<?php echo $checkbox_yesdoc; ?>
					</td>
                	<td colspan="2" align="right"><input type="submit" value="Rechercher"></td>
                </tr>	
	        </table>
        </form>
	</div>
</div>
<div style="clear: both;"></div>
<p style="text-align: center; font-weight: bold;"><span id="nb_resultat"></span> résultat(s)</p>
<table border="0" class="sav_listing" cellpadding="0" cellspacing="0" style="margin: 10px 0 0;">
    <tr>
        <td class="entete_td_refonte"><a href="?<?php echo $criterepost; ?>&amp;tri_critere=date&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'date' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">Date facture</a></td>
        <td class="entete_td_refonte"><a href="?<?php echo $criterepost; ?>&amp;tri_critere=cmdid&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'cmdid' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">N° CMD</a></td>
        <td class="entete_td_refonte"><a href="?<?php echo $criterepost; ?>&amp;tri_critere=pays&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'pays' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">Pays</a></td>
        <td class="entete_td_refonte" style="width:100px;"><a href="?<?php echo $criterepost; ?>&amp;tri_critere=societe&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'societe' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">Société</a></td>
        <td class="entete_td_refonte"><a href="?<?php echo $criterepost; ?>&amp;tri_critere=client&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'client' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">Client</a></td>
        <td class="entete_td_refonte">Transporteur</td>
        <td class="entete_td_refonte"><a href="?<?php echo $criterepost; ?>&amp;tri_critere=totalht&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'totalht' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">Total HT</a></td>
        <td class="entete_td_refonte"><a href="?<?php echo $criterepost; ?>&amp;tri_critere=totalttc&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'totalttc' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">Total TTC</a></td>
        <td class="entete_td_refonte"><a href="?<?php echo $criterepost; ?>&amp;tri_critere=tva&amp;tri_sens=<?php echo $_GET['tri_critere'] == 'tva' && $_GET['tri_sens'] == 'asc' ? 'desc' : 'asc'; ?>" style="color: white;">TVA</a></td>
        <td class="entete_td_refonte">Doc douanes</td>
    </tr>
    <?php
    
    $nb_resultat=0;
    $cpt = 0;
    
    foreach($aDEB as $la_commande){
    	$td_gris = $cpt%2 == 0 ? '1' : '2';
    	$td_doc="";
        
        if($la_commande['commande_douane']==0){
            $td_doc .= " "
            . "<form method= \"post\" action=\"\" name=\"form_deb" . $la_commande['commande_id'] . "\" enctype=\"multipart/form-data\" style=\"margin:0; padding: 0;\">"
                . "<input type=\"hidden\" name=\"user_id\" value=\"" . $la_commande['commande_userid']. "\">"
                . "<input type=\"hidden\" name=\"facture_id\" value=\"" . $la_commande['commande_id'] . "\">"
                . "<input type=\"hidden\" name=\"facture_recherche\" value=\"" . $_POST['facture_recherche'] . "\">"
                . "<input type=\"hidden\" name=\"facture_pays\" value=\"" . $_POST['facture_pays'] . "\">"
                . "<input type=\"hidden\" name=\"facture_trspt\" value=\"" . $_POST['facture_trspt'] . "\">"
                . (isset($_POST['facture_debut']) ? "<input type=\"hidden\" name=\"facture_debut\" value=\"" . $_POST['facture_debut'] . "\">" : "")
                . (isset($_POST['facture_fin']) ? "<input type=\"hidden\" name=\"facture_fin\" value=\"" . $_POST['facture_fin'] . "\">" : "")
                . "<label for=\"input" . $la_commande['commande_id'] . "\" class=\"label_deb\" >Ajouter</label>"
                . "<input type=\"file\" name=\"facture_debfichier\" id=\"input" . $la_commande['commande_id'] . "\" style=\"display: none;\" onchange=\"document.form_deb" . $la_commande['commande_id']. ".submit();\"/>"
            . "</form>";
        }
        
        if(count($la_commande['commande_deb'])==0){
            $td_doc.="<form method=\"post\" name=\"f" . $la_commande['commande_id'] . "\">"
                . "<label for=\"d" . $la_commande['commande_id'] . "\">"
                    . "<input type=\"hidden\" name=\"facture_recherche\" value=\"" . $_POST['facture_recherche'] . "\">"
                    . "<input type=\"hidden\" name=\"facture_pays\" value=\"" . $_POST['facture_pays'] . "\">"
                    . "<input type=\"hidden\" name=\"facture_trspt\" value=\"" . $_POST['facture_trspt'] . "\">"
                    . "<input type=\"hidden\" name=\"nodoc\" value=\"".$la_commande['commande_id']."\">"
                    . (isset($_POST['facture_debut']) ? "<input type=\"hidden\" name=\"facture_debut\" value=\"" . $_POST['facture_debut'] . "\">" : "")
                    . (isset($_POST['facture_fin']) ? "<input type=\"hidden\" name=\"facture_fin\" value=\"" . $_POST['facture_fin'] . "\">" : "")
                        // . "<input type=\"checkbox\" id=\"d" . $la_commande['commande_id'] . "\" name=\"nodoc\" onclick=\"document.forms['f" . $la_commande['commande_id'] . "'].submit()\" value=\"" . $la_commande['commande_id'] . "\"> Pas de document</label>"
                    . "<input type=\"checkbox\" id=\"d" . $la_commande['commande_id'] . "\" name=\"nodocactive\" onclick=\"if(confirm('Etes vous sûr?')){document.forms['f" . $la_commande['commande_id'] . "'].submit();}else{document.getElementById('d" . $la_commande['commande_id'] . "').checked=false;}\" value=\"1\"".($la_commande['commande_douane']==1?" checked":"")."> Pas de document
                </label>"
            . "</form>";
        }
            
        $td_doc.=implode("<br>", $la_commande['commande_deb']);
            
        if( ( $_POST['facture_yesdoc']=="1" ) && ($la_commande['commande_douane']==1 || count($la_commande['commande_deb'])>count($la_commande['commande_trspt']) || ( count($la_commande['commande_deb'])==count($la_commande['commande_trspt']) && count($la_commande['commande_trspt'])>0)  )){
                continue;
        }
        ?>
        
    	<tr>
                <td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_date']; ?></td>
	        <td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_num']; ?></td>
	        <td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_pays']; ?></td>
	        <td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_societe']; ?></td>
	        <td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_client']; ?></td>
	        <td class="td_gris<?php echo $td_gris; ?>"><?= implode("<br>", $la_commande['commande_trspt']); ?></td>
	        <td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_totalht']; ?></td>
	        <td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_totalttc']; ?></td>
	        <td class="td_gris<?php echo $td_gris; ?>"><?php echo $la_commande['commande_tva']; ?></td>
	        <td class="td_gris<?php echo $td_gris; ?>">
                    <?=$td_doc; ?>
	        </td>
	   	</tr>
    <?php
                $cpt++;
		$nb_resultat++;
    }
    ?>
    
</table>
<script type="text/javascript">
    $jQuery(document).ready(function(){
		$jQuery("form textarea").css({"background":"none","border":"solid 1px #888"}).click(function(){
			$jQuery("form textarea").css({"background":"none","border":"solid 1px #888"});
			$jQuery(".sav_listing input[type='submit']").hide();
			var form=$jQuery(this).parent();
			form.find("input[type='submit']").show();
			$jQuery(this).css({"background":"white"});
		});
		$jQuery(".sav_listing input[type='submit']").hide();
		$jQuery("#nb_resultat").text('<?php echo $nb_resultat; ?>');
    });
</script>
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
?>
