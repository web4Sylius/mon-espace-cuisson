<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(BACK_PATH."/adv/adv.class.php");

$fournisseur_nom = $_GET['name'];
$commande_id     = $_GET['facturid'];
$adv_id          = $_GET['advid'];

$adv = new adv($DB_site2, $adv_id);
$adv->locked = isset($_GET['annule']);

//Récupération des produits de la commande
$sql = "SELECT *";
$sql .= " FROM lignefacture";
$sql .= " WHERE factureid = '".$commande_id."'";
$lignefactures = $DB_site2->query($sql);
while ($lignefacture = $DB_site2->fetch_array($lignefactures)){
    
    $style    = "";
    $img      = "";
    $path     = "../configurations/".$host."/images/produits/br/".$lignefacture['artid'].".jpg";
    $checked  = "";
    $disabled = "";
    //if($envoi['date']!="") $style="background-color:green;";

    //Infos sur l'article en cours  
    $sql = "SELECT *,(a.emballage_poids1+a.emballage_poids2+a.emballage_poids3) as emballage_poids,(a.emballage_hauteur1+a.emballage_hauteur2+a.emballage_hauteur3) as emballage_hauteur, (a.emballage_largeur1+a.emballage_largeur2+a.emballage_largeur3) as emballage_largeur,  (a.emballage_profondeur1+a.emballage_profondeur2+a.emballage_profondeur3) as emballage_profondeur";
    $sql .= " FROM article a, article_marque am, marque m";
    $sql .= " WHERE am.artid = a.artid";
    $sql .= " AND am.marqueid = m.marqueid";
    $sql .= " AND a.artid = ".$lignefacture['artid'];
    $article = $DB_site2->query_first($sql);
    $options=$DB_site2->query_first("SELECT SUM(paht) FROM lignefacturecaracteristique WHERE lignefactureid=".$lignefacture['lignefactureid']);

    //$envoi=$DB_site2->query_first("SELECT * FROM envoi_fournisseur_ligne WHERE type='trspt' AND ligne_facture_id=$lignefacture[lignefactureid]");
    $article['volume'] = round($article['emballage_largeur'] * $article['emballage_hauteur'] * $article['emballage_profondeur'] / pow(10,9),1);
    if($article['marqueid'] != null) $marqueids[] = $article['marqueid'];
        
    if(file_exists($path)) $img = "<img src=\"".$path."\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute'; this.style.width='200px';\" onmouseout=\"this.style.position='relative'; this.style.width='30px';\">";

    $aChkActions = array();
    //Actions exécutées au clic sur la checkbox
    if($adv->destinataire != '0'){
        $aChkActions[] = "loadStep('trspt6', '/admin/adv/ajax/majColisage.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')";
    } else {
        $aChkActions[] = "loadStep('trspt13', '/admin/adv/ajax/majValidation.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')";
    }
    $aChkActions[] = "totalassurance()";
    $aChkActions[] = "updligne(".$lignefacture['lignefactureid'].", (this.checked ? ".$lignefacture['qte']." : 0), 5)";

    //Si le produit a déjà été coché => on coche par défaut
    if($adv->lignes[$lignefacture['lignefactureid']] > 0) $checked = 'checked';

    //Si commande annulée, on désactive pour empêcher de changer quoique ce soit
    if($adv->locked) $disabled = 'disabled';

    $checkbox = "<input type=\"checkbox\" name=\"produits2[]\" value=\"".$lignefacture['lignefactureid']."\" onclick=\"".implode(';',$aChkActions)."\" ".$checked." ".$disabled.">";
    
    $oadv = $adv->listadv($commande_id,"trspt",$lignefacture['lignefactureid']);
	
	//***************************************
	//*****		  bug/tâche 2921       	*****
	//***************************************
	
	//echo "commande id == " . $commande_id . "*";
	//echo "<br>";
	//echo "ligne facture == " . $lignefacture['lignefactureid'] . "***";
	//echo "<br>";
	
	//print_r($oadv);
	
	//echo "count == " . count(oadv); 
	//echo "<br> oadv 0 -> id : ";
	//echo $oadv[0]->id . " != adv -> id : " . $adv->id;
	//echo "<br>";
	//print "substr == " . substr($oadv[0]->fields['trsptlivr'],0,5);
	//echo "<br>";
	//echo "fields[trsptlivr] == " . $oadv[0]->fields['trsptlivr'];
	//echo "<br>";
	//echo "type == " . $oadv[0]->type;
	
    if(count($oadv) > 0 && $oadv[0]->id != $adv->id && substr($oadv[0]->fields['trsptlivr'],0,5) != "stock"){
        $checkbox = "";
        $style    = "background-color:#FFAA22;";
    }
if($_COOKIE['admin_username']=='jessica'){
    var_dump($article['artcode']);
    var_dump($article['libelleFR']);
    var_dump($lignefacture['artid']);
}
    $aProduits[] = array(
        'trstyle'         => $style,
        'lignefactureid'  => $lignefacture['lignefactureid'],
        'artcode'         => $article['artcode'],
        'libelle'         => $article['libelleFR'],
        'qte'             => $lignefacture['qte'],
        'paht'            => $article['paht']+$options[0],
        'prixht'          => $article['prixht'],
        'ecopartht'       => $article['ecopartht'],
        'checkbox'        => $checkbox,
        'img'             => $img,
        'emballage_poids' => $article['emballage_poids'],
        'poidsvolume'     => $article['poidsvolume'],
        'volume'          => $article['volume']
    );
}

$marqueids = isset($marqueids) ? array_unique($marqueids) : array();
?>
<p style="padding:5px; margin:0;">5. Produits</p>
<table>
    <tr>
        <td colspan="2"></td>
        <td>Qté</td>
        <td>Détail</td>
        <td>Poids brut en kg</td>
    </tr>
    <?php foreach($aProduits as $le_produit){ ?>
    <tr>
        <td style="<?php echo $le_produit['trstyle']; ?>">
            <input type="hidden" name="artcode_<?php echo $le_produit['lignefactureid']; ?>" value="<?php echo $le_produit['artcode']; ?>">
            <input type="hidden" name="price_<?php echo $le_produit['lignefactureid']; ?>" value="<?php echo $le_produit['paht']; ?>">
            <input type="hidden" name="pv_<?php echo $le_produit['lignefactureid']; ?>" value="<?php echo $le_produit['prixht']; ?>">
            <input type="hidden" name="ecopart2_<?php echo $le_produit['lignefactureid']; ?>" value="<?php echo $le_produit['ecopartht']; ?>">
            <?php echo $le_produit['checkbox']; ?>
        </td>
        <td><?php echo $img; ?></td>
        <td><input type="hidden" name="qte2_<?php echo $le_produit['lignefactureid']; ?>" value="<?php echo $le_produit['qte']; ?>"><?php echo $le_produit['qte']; ?></td>
        <td id="libelle_<?php echo $le_produit['lignefactureid']; ?>"><?php echo $le_produit['artcode'].' - '.$le_produit['libelle']; ?></td>
        <td>
            <?php echo $le_produit['emballage_poids']; ?>
            <input type="hidden" name="poids_<?php echo $le_produit['lignefactureid']; ?>" value="<?php echo str_replace(',','.',$le_produit['emballage_poids']); ?>">
            <input type="hidden" name="poidsvolume_<?php echo $le_produit['lignefactureid']; ?>" value="<?php echo $le_produit['poidsvolume']; ?>">
            <input type="hidden" name="volume_<?php echo $le_produit['lignefactureid']; ?>" value="<?php echo $le_produit['volume']; ?>">
        </td>
    </tr>
    <?php } ?>
    <tr>
        <td colspan="3"></td>
        <td>Poids total</td>
        <td id="poidstotal"></td>
    </tr>
</table>
