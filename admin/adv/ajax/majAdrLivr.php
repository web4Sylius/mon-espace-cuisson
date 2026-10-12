<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(BACK_PATH."/adv/adv.class.php");
require_once(SITE_PATH."/class/stock.class.php");
require_once(BACK_PATH."/facture.class.php");

$fournisseur_nom = $_GET['name'];
$commande_id     = $_GET['facturid'];
$adv_id          = $_GET['advid'];

$facture = new facture($DB_site2, $commande_id);
$adv     = new adv($DB_site2, $adv_id);
$adv->locked = isset($_GET['annule']);

$displaystocktr = $displayslstr = "none";
$iconstock      = $iconsls      = "plus";
$aSLS           = array();

if(isset($adv->fields['trsptlivr'])){
    switch(substr($adv->fields['trsptlivr'],0,3)){
        case "sto":
            $displaystocktr = "";
            $iconstock      = "moins";
            break;
        
        case "sls":
            $displayslstr = "";
            $iconsls      = "";
            break;
    }
}

//Récupération des différents lieux de stockage pour affichage dans la liste
if($adv->fields['trspttype'] != 'tnt_relais'){ //Uniquement si relais colis n'a pas été précédemment choisi
    $stock = new stock($DB_site2);
    $i=0;
    foreach($stock->lieux() as $fournid => $stockname){

        $sql = "SELECT f.*, pays.libelle AS pays";
        $sql .= " FROM fournisseur f";
        $sql .= " INNER JOIN pays USING(paysid)";
        $sql .= " WHERE id = ".$fournid;
        $stock = $DB_site2->query_first($sql);
    	$aStock[$i]['fourn'] = $stock;
    	$aStock[$i]['id']    = $fournid;
    	$aStock[$i]['name']  = $stockname;
    	$aStock[$i]['cp']    = $stock['cp'];
    	$aStock[$i]['pays']  = $stock['pays'];

        $i++;
    }
}

//Récupération des lieux de livraison possibles de Girard Agediss (ex SLS)
if($adv->fields['trspttype'] != 'tnt_relais'){ //Uniquement si relais colis n'a pas été précédemment choisi
    $sql = "SELECT *";
    $sql .= " FROM fournisseur";
    $sql .= " INNER JOIN pays ON (pays.paysid = fournisseur.paysid)";
    $sql .= " WHERE type = 'sls'";
    $requete = $DB_site2->query($sql);
    while($sls=$DB_site2->fetch_array($requete)){
        $aSLS[] = $sls;
    }

    //Récupération des différents fournisseurs disponibles pour livraison
    $sql = "SELECT *";
    $sql .= " FROM fournisseur_condition fc";
    $sql .= " INNER JOIN fournisseur f ON (f.societe = fc.societe)";
    $sql .= " WHERE fc.types IN ('produits principaux','produits secondaires')";
    $sql .= " AND fc.activite = '1'";
    $sql .= " AND f.type = 'fournadv'";
    $sql .= " AND f.adresse1 != ''";
    $sql .= " GROUP BY f.id";
    $sql .= " ORDER BY f.societe";
    $requete = $DB_site2->query($sql);
    while($fourn=$DB_site2->fetch_array($requete)){
        $aFourn[] = $fourn;
    }
}

//PARAMS "Adresse client"
if($adv->fields['trspttype'] != 'tnt_relais'){ //Uniquement si relais colis n'a pas été précédemment choisi
    $aLibDateLivrLocaux = array("Domicile client" => "client", "Société client" => "societe");
    $aFuncDateLivrLocaux = array(
      "document.getElementById('cplivr_temp').value = '".$facture->codepostal." - ".$facture->pays."'",
      "loadStep('trspt10', '/admin/adv/ajax/majDateLivr.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')",
      "adv_save('trsptlivr',10)"
    );
}

//PARAMS "Relais colis"
if($adv->fields['trspttype'] == 'tnt_relais'){
    $aLibRelaisColis = array("Relais Colis" => "relaiscolis");
    $aFuncRelaisColis = array(
      "adv_save('trsptlivr',10)"
    );
}
?>
<p style="padding:5px; margin:0;">9. Adresse de livraison</p>
<div>
    <?php 
    //Si relais colis sélectionné, on ne propose que le champs de sélection des relais colis
    if(isset($aLibRelaisColis) && !empty($aLibRelaisColis)){
        echo $adv->radio_inputs("trsptlivr", $aLibRelaisColis, implode(';',$aFuncRelaisColis));
        ?>
        <input type="text" id="cp_relais" name="cp_relais" value="" placeholder="Code postal">
        <input type="text" id="ville_relais" name="ville_relais" value="" placeholder="Commune">
        <input type="button" value="Rechercher" onclick="getRelaisColisTNT(document.getElementById('cp_relais').value, document.getElementById('ville_relais').value, <?php echo $adv->id; ?>, <?php echo $commande_id; ?>, '<?php echo $fournisseur_nom; ?>');">
        <div id="relais_results"><?php echo isset($adv->fields['relaiscolisadr']) ? "<p style=\"padding-top:5px; padding-left:20px;\">".$adv->fields['relaiscolisadr']."</p>" : ''; ?></div>
        <?php
    }

    if(isset($aLibDateLivrLocaux) && !empty($aLibDateLivrLocaux)){
        echo $adv->radio_inputs("trsptlivr", $aLibDateLivrLocaux, implode(';', $aFuncDateLivrLocaux));
    }
    ?>
</div>
<?php if(!empty($aStock)){ ?>
    <table>
        <thead>
        <tr>
            <td onclick="showitemlist('tr_stock',this)" style="cursor:pointer;"><img src="images/<?php echo $iconstock; ?>.gif"> Stock</td>
        </tr>
        </thead>
        <tbody id="tr_stock" style="display:<?php echo $displaystocktr; ?>;">

        <?php 
        foreach($aStock as $le_stock){ 
            //PARAMS "Nom du stock"
            $aLibStockName = array($le_stock['name'] => "stock".$le_stock['id']);
            $aFuncStockName = array(
                "document.getElementById('bonslsname_temp').innerHTML = '".addslashes($le_stock['name'])."'",
                "document.getElementsByName('bonstrsptslspj_temp')[0].value = 1",
                "document.getElementsByName('bonstrsptpj_temp')[0].value = 0",
                "document.getElementById('cplivr_temp').value='".addslashes($le_stock['cp']." ".$le_stock['pays'])."'",
                "loadStep('trspt10', '/admin/adv/ajax/majDateLivr.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')",
                "adv_save('trsptlivr',10)"
            );

        ?>
        <tr>
            <td><?php echo $adv->radio("trsptlivr", $aLibStockName, implode(';',$aFuncStockName)); ?></td>
            <td></td>
        </tr>
        <?php } ?>
        </tbody>
    </table>
<?php } ?>
<?php if(!empty($aSLS)){ ?>
    <table>
        <thead>
        <tr>
            <td onclick="showitemlist('tr_install',this)" style="cursor:pointer;"><img src="images/<?php echo $iconsls; ?>.gif"> Installateurs</td>
        </tr>
        </thead>
        <tbody id="tr_install" style="display:<?php echo $displayslstr; ?>;">
        <tr>
            <?php 
            $c = 0;
            foreach($aSLS as $le_sls){
                $c++;

                //PARAMS "SLS"
                $aLibSLS = array($le_sls['societe'].' '.$le_sls['ville'].' '.$le_sls['fonction'] => "sls".$le_sls['id']);
                $aFuncSLS = array(
                	"document.getElementById('bonslsname_temp').innerHTML = '".addslashes($le_sls['name'])."'",
    	            "document.getElementsByName('bonstrsptslspj_temp')[0].value = 1",
    	            "document.getElementsByName('bonstrsptpj_temp')[0].value = 0",
                    "document.getElementById('cplivr_temp').value = '".addslashes($le_sls['cp']." - ".$le_sls['libelle'])."'",
                    "loadStep('trspt10', '/admin/adv/ajax/majDateLivr.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')",
                    "adv_save('trsptlivr',10)"
                );

                if($rs_facture['modelivraison'] == 1 && strpos($le_sls['fonction'],$dep) >- 1) $style_color = 'style="color:red;"';
                ?>
                <td title="<?php echo $le_sls['nom']; ?> <?php echo $le_sls['prenom']; ?> - <?php echo $le_sls['adresse1']; ?> - <?php echo $le_sls['cp']; ?> <?php echo $le_sls['ville']; ?>" <?php echo $style_color; ?>><?php echo $adv->radio("trsptlivr", $aLibSLS, implode(';',$aFuncSLS)); ?></td>
                <?php
                if($c == 2){$c++;
                    $c = 0;
                    echo "</tr><tr>";
                }
            }
            ?>
        </tr>
        </tbody>
    </table>
<?php } ?>
<?php if(!empty($aFourn)){ ?>
    <table>
        <thead>
        <tr>
            <td onclick="showitemlist('tr_fournisseur',this)" style="cursor:pointer;"><img src="images/<?php echo $iconsls; ?>.gif"> Fournisseurs</td>
        </tr>
        </thead>
        <tbody id="tr_fournisseur" style="display:<?php echo $displayslstr; ?>;">
        <tr>
            <?php 
            $c = 0;
            foreach($aFourn as $le_fournisseur){
                $c++;

                //PARAMS "Fournisseur"
                $aLibFournisseur = array($le_fournisseur['societe'].' '.$le_fournisseur['ville'].' '.$le_fournisseur['fonction'] => "fourn".$le_fournisseur['id']);
                $aFuncFournisseur = array(
                    "document.getElementById('bonslsname_temp').innerHTML = '".addslashes($le_fournisseur['name'])."'",
                    "document.getElementsByName('bonstrsptslspj_temp')[0].value = 1",
                    "document.getElementsByName('bonstrsptpj_temp')[0].value = 0",
                    "document.getElementById('cplivr_temp').value = '".addslashes($le_fournisseur['cp']." - ".$le_fournisseur['libelle'])."'",
                    "loadStep('trspt10', '/admin/adv/ajax/majDateLivr.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')",
                    "adv_save('trsptlivr',10)"
                );
                ?>
                <td title="<?php echo $le_fournisseur['nom']; ?> <?php echo $le_fournisseur['prenom']; ?> - <?php echo $le_fournisseur['adresse1']; ?> - <?php echo $le_fournisseur['cp']; ?> <?php echo $le_fournisseur['ville']; ?>" <?php echo $style_color; ?>><?php echo $adv->radio("trsptlivr", $aLibFournisseur, implode(';',$aFuncFournisseur)); ?></td>
                <?php
                if($c == 2){$c++;
                    $c = 0;
                    echo "</tr><tr>";
                }
            }
            ?>
        </tr>
        </tbody>
    </table>
<?php } ?>