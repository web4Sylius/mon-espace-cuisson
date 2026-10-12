<?php
//Mise à jour de l'objet facture après d'éventuelles mise à jour de l'adresse de facturation
$oFacture = new facture($DB_site2, $facturid);

$edition = ($oFacture->valide != 5 && $oFacture->valide != 6);

$aCivilite = array('' => '-- Choisir --', 0 => 'Monsieur', 1 => 'Madame', 2 => 'Mademoiselle');

//Récupération des articles garantis par SFG dans la commande
$tabArticleGar = array();
$tabGarantie = array();
$aProduits = array();

//Civilité des coordonnées de facturation
switch ($user['civilite']) {
    case 0:
        $user['civilite'] = "Monsieur";
        break;
    case 1:
        $user['civilite'] = "Madame";
        break;
    case 2:
        $user['civilite'] = "Mademoiselle";
        break;
}

//*********************************************//
// PARAMETRAGE FORM "Coordonnées de livraison" //
//*********************************************//
$formLivraison = new Formulaire($_POST);
$formLivraison->action = 'facture.php?action=editer&facturid=' . $facturid;
$formLivraison->name = 'formu';
$formLivraison->target = '_self';
$formLivraison->styles = array('display' => 'inline');

$aChamps = array(
    array(
        'type' => 'hidden',
        'name' => 'action',
        'value' => 'doeditercoord'
    ),
    array(
        'type' => 'hidden',
        'name' => 'userid',
        'value' => $oFacture->userid
    ),
    array(
        'type' => 'hidden',
        'name' => 'facturid',
        'value' => $oFacture->facturid
    ),
    array(
        'type' => 'hidden',
        'name' => 'paysid_nouse',
        'id' => 'paysid',
        'value' => $oFacture->paysid
    ),
    array(
        'type' => 'text',
        'name' => 'paysid',
        'value' => $oFacture->pays,
        'options' => array('disabled' => true)
    )
);

foreach ($aChamps as $le_champs) {
    $formLivraison->registerInput($le_champs);
}

if ($edition) {
    $aChamps = array(
        array(
            'name' => 'mail_facture',
            'type' => 'text',
            'value' => $oFacture->mail
        ),
        array(
            'name' => 'raisonsociale',
            'type' => 'text',
            'value' => stripslashes($oFacture->raisonsociale)
        ),
        array(
            'name' => 'civilite',
            'type' => 'select',
            'value' => $oFacture->civilite,
            'values' => $aCivilite,
            'obligatoire' => true
        ),
        array(
            'name' => 'nom',
            'type' => 'text',
            'value' => stripslashes($oFacture->nom),
            'obligatoire' => true
        ),
        array(
            'name' => 'prenom',
            'type' => 'text',
            'value' => stripslashes($oFacture->prenom),
            'obligatoire' => true
        ),
        array(
            'name' => 'adresse',
            'type' => 'text',
            'value' => stripslashes($oFacture->adresse),
            'obligatoire' => true
        ),
        array(
            'name' => 'adresse2',
            'type' => 'text',
            'value' => stripslashes($oFacture->adresse2)
        ),
        array(
            'name' => 'codepostal',
            'type' => 'text',
            'value' => $oFacture->codepostal,
            'options' => array('maxlength' => 10)
        ),
        array(
            'name' => 'ville',
            'type' => 'text',
            'value' => stripslashes($oFacture->ville)
        ),
        array(
            'name' => 'telephone',
            'type' => 'text',
            'value' => $oFacture->telephone,
            'obligatoire' => true
        ),
        array(
            'name' => 'infotranspo',
            'type' => 'textarea',
            'value' => stripslashes($oFacture->infotranspo),
            'styles' => array('width' => '100%'),
            'options' => array('onkeypress' => 'limit_len(event,this,250,\'infotranspolen\')')
        )
    );

    foreach ($aChamps as $l_input) {
        $formLivraison->registerInput($l_input);
    }

    $aChamps = array(
        'type' => 'submit',
        'name' => 'submit_coordonnees',
        'value' => 'Enregistrer',
        'class' => 'skinned'
    );
    $formLivraison->registerInput($aChamps);
}

//Traitement du formulaire
if (isset($_POST['action']) && $_POST['action'] == "doeditercoord") {
    if ($formLivraison->getErreur() == '') {
        //Mise à jour des données dans la classe
        $formLivraison->update();

        //Mise à jour des données dans la BDD
        $sql = "UPDATE facture SET ";
        $sql .= "mail            = '" . $_POST['mail_facture'] . "'";
        $sql .= ", raisonsociale = '" . mysqli_real_escape_string($DB_site2->link_id, $_POST['raisonsociale']) . "'";
        $sql .= ", civilite      = " . $_POST['civilite'];
        $sql .= ", nom           = '" . mysqli_real_escape_string($DB_site2->link_id, $_POST['nom']) . "'";
        $sql .= ", prenom        = '" . mysqli_real_escape_string($DB_site2->link_id, $_POST['prenom']) . "'";
        $sql .= ", adresse       = '" . mysqli_real_escape_string($DB_site2->link_id, $_POST['adresse']) . "'";
        $sql .= ", adresse2       = '" . mysqli_real_escape_string($DB_site2->link_id, $_POST['adresse2']) . "'";
        $sql .= ", codepostal    = '" . $_POST['codepostal'] . "'";
        $sql .= ", ville         = '" . mysqli_real_escape_string($DB_site2->link_id, $_POST['ville']) . "'";
        $sql .= ", telephone     = '" . $_POST['telephone'] . "'";
        $sql .= ", infotranspo   ='" . mysqli_real_escape_string($DB_site2->link_id, $_POST['infotranspo']) . "'";
        $sql .= " WHERE facturid = '" . $_POST['facturid'] . "'";
        $DB_site2->query($sql);

        $message_livraison = "<p style=\"text-align: center; font-weight: bold; color: green;\">Informations mises à jour</p>";
    } else {
        $message_livraison = "<p style=\"text-align: center; color: red; font-weight: bold;\">" . $formLivraison->getErreur() . "</p>";
    }
}

//*********************************//
// PARAMETRAGE FORM "Contremarque" //
//*********************************//
//Recherche de la présence d'au moins 1 article garantie SFG
$sql = "SELECT lf.artid, lf.artcode, lf.prix_revendeur, lf.qte as qte";
$sql .= " FROM lignefacture lf";
$sql .= " INNER JOIN lignefacturegarantie lfg ON (lfg.lignefactureid = lf.lignefactureid)";
$sql .= " WHERE lf.factureid = " . $oFacture->facturid . "";
$sql .= " GROUP BY artid";
$req = $DB_site2->query($sql);

while ($row = $DB_site2->fetch_array($req)) {
    for ($i = 0; $i < $row['qte']; $i++) {
        $tabArticleGar[] = $row;
    }
}

$sql = "SELECT *";
$sql .= " FROM facture_infos_garantie";
$sql .= " WHERE factureid = '" . $oFacture->facturid . "'";
$tabGarantie = $DB_site2->query_first($sql);

$formContremarque = new Formulaire($_POST);
$formContremarque->action = 'facture.php?action=editer&facturid=' . $facturid;

$aChamps = array(
    array(
        'type' => 'hidden',
        'name' => 'addcontremarque',
        'value' => !$tabGarantie ? 0 : 1
    ),
    array(
        'type' => 'hidden',
        'name' => 'facturid',
        'value' => $oFacture->facturid
    )
);
foreach ($aChamps as $l_input) {
    $formContremarque->registerInput($l_input);
}

$edit_pv_revendeur = '';
if (count($tabArticleGar) > 0) {
    foreach ($tabArticleGar as $le_produit) {
        $edit_pv_revendeur .= ""
            . "<tr>"
            . "<td>" . $le_produit['artcode'] . "</td>"
            . "<td>%PRODUITS_INFOS_" . $le_produit['artid'] . "%</td>"
            . "</tr>";

        $aProduits[] = $le_produit;
    }
} else {
    $edit_pv_revendeur = "<input type=\"hidden\" name=\"prix_revendeur[]\" value=\"\">";
}

if ($edition) {
    $aChamps = array(
        array(
            'name' => 'garantie_civilite',
            'type' => 'select',
            'value' => !$tabGarantie ? '' : $tabGarantie['facture_infos_garantie_civilite'],
            'values' => $aCivilite
        ),
        array(
            'name' => 'garantie_societe',
            'type' => 'text',
            'value' => !$tabGarantie ? '' : $tabGarantie['facture_infos_garantie_societe']
        ),
        array(
            'name' => 'garantie_nom',
            'type' => 'text',
            'value' => !$tabGarantie ? '' : $tabGarantie['facture_infos_garantie_nom'],

        ),
        array(
            'name' => 'garantie_prenom',
            'type' => 'text',
            'value' => !$tabGarantie ? '' : $tabGarantie['facture_infos_garantie_prenom'],

        ),
        array(
            'name' => 'garantie_adresse',
            'type' => 'text',
            'value' => !$tabGarantie ? '' : $tabGarantie['facture_infos_garantie_adresse'],
            'obligatoire' => true
        ),
        array(
            'name' => 'garantie_codepostal',
            'type' => 'text',
            'value' => !$tabGarantie ? '' : $tabGarantie['facture_infos_garantie_cp'],
            'obligatoire' => true
        ),
        array(
            'name' => 'garantie_ville',
            'type' => 'text',
            'value' => !$tabGarantie ? '' : $tabGarantie['facture_infos_garantie_ville']
        ),
        array(
            'name' => 'garantie_telephone',
            'type' => 'text',
            'value' => !$tabGarantie ? '' : $tabGarantie['facture_infos_garantie_tel']
        ),
        array(
            'name' => 'garantie_portable',
            'type' => 'text',
            'value' => !$tabGarantie ? '' : $tabGarantie['facture_infos_garantie_port']
        ),
        array(
            'name' => 'garantie_mail',
            'type' => 'text',
            'value' => !$tabGarantie ? '' : $tabGarantie['facture_infos_garantie_mail']
        )
    );

    foreach ($aChamps as $l_input) {
        $formContremarque->registerInput($l_input);
    }

    if (count($aProduits) > 0) {
        foreach ($aProduits as $le_produit) {
            $aChamps = array(
                'type' => 'text',
                'name' => 'prix_revendeur[' . $le_produit['artcode'] . ']',
                'value' => $le_produit['prix_revendeur'] == 0 ? '' : $le_produit['prix_revendeur'],
                'obligatoire' => true
            );
            $formContremarque->registerInput($aChamps);
        }
    }

    $aChamps = array(
        'type' => 'submit',
        'name' => 'submit_contremarque',
        'value' => 'Enregistrer',
        'class' => 'skinned'
    );
    $formContremarque->registerInput($aChamps);
}

$message_erreur = false;
$message_erreur_prix = false;
//Traitement du formulaire
if (isset($_POST['addcontremarque']) && $_POST['addcontremarque'] == 1) {
    if ($formContremarque->getErreur() == '') {
        //Mise à jour des données dans la classe
        $formContremarque->update();

        //Vérification de l'existance de la ligne pour cette commande (insertion ou mise à jour selon le cas)
        $sql = "SELECT facture_infos_garantie_id";
        $sql .= " FROM facture_infos_garantie";
        $sql .= " WHERE factureid = '" . $_POST['facturid'] . "'";
        $facture_gar_id = $DB_site2->query_first($sql);


        foreach ($_POST['prix_revendeur'] as $article_ref => $article_montant) {
            if ($article_montant != '' && $article_montant > 0) {
                $sql = "UPDATE lignefacture SET";
                $sql .= " prix_revendeur = '" . trim($article_montant) . "'";
                $sql .= " WHERE factureid = " . $_POST['facturid'];
                $sql .= " AND artcode = '" . $article_ref . "'";
                $DB_site2->query($sql);
            } else {
                $message_erreur_prix = true;
            }
        }

        if ($message_erreur_prix == false && empty($facture_gar_id[0]) && ($_POST['garantie_societe'] || ($_POST['garantie_prenom'] && $_POST['garantie_nom']))) {
            $sql = "INSERT INTO facture_infos_garantie (";
            $sql .= "facture_infos_garantie_civilite";
            $sql .= ", facture_infos_garantie_societe";
            $sql .= ", facture_infos_garantie_nom";
            $sql .= ", facture_infos_garantie_prenom";
            $sql .= ", facture_infos_garantie_adresse";
            $sql .= ", facture_infos_garantie_cp";
            $sql .= ", facture_infos_garantie_ville";
            $sql .= ", facture_infos_garantie_tel";
            $sql .= ", facture_infos_garantie_port";
            $sql .= ", facture_infos_garantie_mail";
            $sql .= ", factureid";
            $sql .= ") VALUES (";
            $sql .= "'" . $_POST['garantie_civilite'] . "'";
            $sql .= ", '" . trim(mysqli_real_escape_string($DB_site2->link_id, $_POST['garantie_societe'])) . "'";
            $sql .= ", '" . trim(mysqli_real_escape_string($DB_site2->link_id, $_POST['garantie_nom'])) . "'";
            $sql .= ", '" . trim(mysqli_real_escape_string($DB_site2->link_id, $_POST['garantie_prenom'])) . "'";
            $sql .= ", '" . trim(mysqli_real_escape_string($DB_site2->link_id, $_POST['garantie_adresse'])) . "'";
            $sql .= ", '" . trim(mysqli_real_escape_string($DB_site2->link_id, $_POST['garantie_codepostal'])) . "'";
            $sql .= ", '" . trim(mysqli_real_escape_string($DB_site2->link_id, $_POST['garantie_ville'])) . "'";
            $sql .= ", '" . trim(mysqli_real_escape_string($DB_site2->link_id, $_POST['garantie_telephone'])) . "'";
            $sql .= ", '" . trim(mysqli_real_escape_string($DB_site2->link_id, $_POST['garantie_portable'])) . "'";
            $sql .= ", '" . trim(mysqli_real_escape_string($DB_site2->link_id, $_POST['garantie_mail'])) . "'";
            $sql .= ", '" . $_POST['facturid'] . "'";
            $sql .= ")";
            $DB_site2->query($sql);
        } elseif ($message_erreur_prix == false && isset($facture_gar_id[0]) && ($_POST['garantie_societe'] || ($_POST['garantie_prenom'] && $_POST['garantie_nom']))) {
            $sql = "UPDATE facture_infos_garantie SET";
            $sql .= " facture_infos_garantie_civilite = '" . $_POST['garantie_civilite'] . "'";
            $sql .= ", facture_infos_garantie_societe = '" . trim(mysqli_real_escape_string($DB_site2->link_id, $_POST['garantie_societe'])) . "'";
            $sql .= ", facture_infos_garantie_nom     = '" . trim(mysqli_real_escape_string($DB_site2->link_id, $_POST['garantie_nom'])) . "'";
            $sql .= ", facture_infos_garantie_prenom  = '" . trim(mysqli_real_escape_string($DB_site2->link_id, $_POST['garantie_prenom'])) . "'";
            $sql .= ", facture_infos_garantie_adresse = '" . trim(mysqli_real_escape_string($DB_site2->link_id, $_POST['garantie_adresse'])) . "'";
            $sql .= ", facture_infos_garantie_cp      = '" . trim(mysqli_real_escape_string($DB_site2->link_id, $_POST['garantie_codepostal'])) . "'";
            $sql .= ", facture_infos_garantie_ville   = '" . trim(mysqli_real_escape_string($DB_site2->link_id, $_POST['garantie_ville'])) . "'";
            $sql .= ", facture_infos_garantie_tel     = '" . trim(mysqli_real_escape_string($DB_site2->link_id, $_POST['garantie_telephone'])) . "'";
            $sql .= ", facture_infos_garantie_port    = '" . trim(mysqli_real_escape_string($DB_site2->link_id, $_POST['garantie_portable'])) . "'";
            $sql .= ", facture_infos_garantie_mail    = '" . trim(mysqli_real_escape_string($DB_site2->link_id, $_POST['garantie_mail'])) . "'";
            $sql .= " WHERE factureid = '" . $_POST['facturid'] . "'";
            $DB_site2->query($sql);
        } else {
            $message_erreur = true;
        }

        if ($message_erreur_prix) {
            $message_contremarque = "<p style=\"text-align: center; color: red; font-weight: bold;\">Un prix n'est pas renseigné (ne doit pas être à 0)</p>";
        } elseif ($message_erreur) {
            $message_contremarque = "<p style=\"text-align: center; font-weight: bold; color: red;\">Société ou nom/prénom non renseigné</p>";
        } else {
            $message_contremarque = "<p style=\"text-align: center; font-weight: bold; color: green;\">Informations mises à jour</p>";
        }
    } else {
        $message_contremarque = "<p style=\"text-align: center; color: red; font-weight: bold;\">" . $formContremarque->getErreur() . "</p>";
    }
}

/**************************/
/* INFORMATION IMPORTANTE */
/**************************/
// Traitement du formulaire "information importante" 
if (isset($_POST['info_importante'])) {
    $sql = "UPDATE facture SET ";
    $sql .= " info_importante = '" . mysqli_real_escape_string($DB_site2->link_id, $_POST['info_importante']) . "'";
    $sql .= " WHERE facturid = " . intval($facturid);
    $DB_site2->query($sql);
}

//Texte "information importante"
$sql = "SELECT info_importante";
$sql .= " FROM facture";
$sql .= " WHERE facturid = " . intval($facturid);
$tabInfoImp = $DB_site2->query_first($sql);

$formInfo = new Formulaire($_POST);
$formInfo->id = 'info_importante';

$aChamps = array(
    array(
        'type' => 'textarea',
        'name' => 'info_importante',
        'id' => 'textarea',
        'value' => $tabInfoImp['info_importante'],
        'options' => array(
            'cols' => 120,
            'rows' => 4
        )
    ),
    array(
        'type' => 'submit',
        'value' => 'Enregistrer',
        'name' => 'submit_info'
    )
);
foreach ($aChamps as $le_champs) $formInfo->registerInput($le_champs);

if ($edition) {
    //Champs de "Coordonnées de livraison"
    $edit_mail = $formLivraison->input('mail_facture');
    $edit_societe = $formLivraison->input('raisonsociale');
    $edit_civilite = $formLivraison->input('civilite');
    $edit_nom = $formLivraison->input('nom');
    $edit_prenom = $formLivraison->input('prenom');
    $edit_adresse = $formLivraison->input('adresse');
    $edit_adresse2 = $formLivraison->input('adresse2');
    $edit_cp = $formLivraison->input('codepostal');
    $edit_ville = $formLivraison->input('ville');
    $edit_telephone = $formLivraison->input('telephone');
    $edit_infotranspo = $formLivraison->input('infotranspo');

    $submit_livraison = $formLivraison->input('submit_coordonnees');

    //Champs de "Garantie (contremarque)"
    $edit_civilite_gar = $formContremarque->input('garantie_civilite');
    $edit_societe_gar = $formContremarque->input('garantie_societe');
    $edit_prenom_gar = $formContremarque->input('garantie_prenom');
    $edit_nom_gar = $formContremarque->input('garantie_nom');
    $edit_adresse_gar = $formContremarque->input('garantie_adresse');
    $edit_cp_gar = $formContremarque->input('garantie_codepostal');
    $edit_ville_gar = $formContremarque->input('garantie_ville');
    $edit_tel_gar = $formContremarque->input('garantie_telephone');
    $edit_port_gar = $formContremarque->input('garantie_portable');
    $edit_mail_gar = $formContremarque->input('garantie_mail');

    if (count($aProduits) > 0) {
        foreach ($aProduits as $le_produit) {
            $edit_pv_revendeur = str_replace('%PRODUITS_INFOS_' . $le_produit['artid'] . '%', $formContremarque->input('prix_revendeur[' . $le_produit['artcode'] . ']'), $edit_pv_revendeur);
        }
    }

    $submit_contremarque = $formContremarque->input('submit_contremarque');
} else {
    //Champs de "Coordonnées de livraison"
    $edit_mail = $oFacture->mail;
    $edit_societe = $oFacture->raisonsociale;
    $edit_civilite = $aCivilite[$oFacture->civilite];
    $edit_nom = $oFacture->nom;
    $edit_prenom = $oFacture->prenom;
    $edit_adresse = $oFacture->adresse;
    $edit_adresse2 = $oFacture->adresse2;
    $edit_cp = $oFacture->codepostal;
    $edit_ville = $oFacture->ville;
    $edit_telephone = '';
    $edit_infotranspo = $oFacture->infotranspo;

    //Champs de "Garantie (contremarque)"
    $edit_civilite_gar = $tabGarantie['facture_infos_garantie_civilite'];
    $edit_societe_gar = $tabGarantie['facture_infos_garantie_societe'];
    $edit_prenom_gar = $tabGarantie['facture_infos_garantie_prenom'];
    $edit_nom_gar = $tabGarantie['facture_infos_garantie_nom'];
    $edit_adresse_gar = $tabGarantie['facture_infos_garantie_adresse'];
    $edit_cp_gar = $tabGarantie['facture_infos_garantie_cp'];
    $edit_ville_gar = $tabGarantie['facture_infos_garantie_ville'];
    $edit_tel_gar = $tabGarantie['facture_infos_garantie_tel'] != '' && $tabGarantie['facture_infos_garantie_tel'] != 'inconnu' ? epigy($tabGarantie['facture_infos_garantie_tel']) : $tabGarantie['facture_infos_garantie_tel'];
    $edit_port_gar = $tabGarantie['facture_infos_garantie_port'] != '' && $tabGarantie['facture_infos_garantie_port'] != 'inconnu' ? epigy($tabGarantie['facture_infos_garantie_port']) : $tabGarantie['facture_infos_garantie_port'];
    $edit_mail_gar = $tabGarantie['facture_infos_garantie_mail'];

    if (count($aProduits) > 0) {
        foreach ($aProduits as $le_produit) $edit_pv_revendeur = str_replace('%PRODUITS_INFOS_' . $le_produit['artid'] . '%', number_format($le_produit['prix_revendeur'], 2, ',', ' ') . ' €', $edit_pv_revendeur);
    }

    $submit_livraison = '';
    $submit_contremarque = '';
}

$liensuivi = "";
if (in_array($oFacture->valide, array(1, 5, 7, 8))) {
    $liensuivi = "<a href=\"/suivi_commande.php?facturid=" . $oFacture->facturid . "&amp;suiviid=" . $oFacture->suiviId() . "\" target=\"_blank\" style=\"display:block;float:left;margin:20px 0 0 10px;\">Suivi livraison</a>";
}

?>
<style>
    input.champs_missing {
        border: 2px solid red;
        color: red;
    }

    select.champs_missing {
        border: 2px solid red;
        color: red;
    }

    textarea.champs_missing {
        border: 2px solid red;
        color: red;
    }
</style>
<div class="table_modules_boutiques tab_special" id="facture_coordonnees">
    <a href="clientnavig.php?userid=<?php echo $user['userid']; ?>" target="_blank" id="compte_client"><img
                src="images/connexion_compte.png"></a>
    <a href="utilisateur.php?action=editer&amp;userid=<?php echo $user['userid']; ?>" id="fiche_client"><img
                src="images/fiche_client.png" title="Voir la fiche client"></a>
    <?= $liensuivi; ?>
    <h2>Coordonnées</h2>
    <span class="fileboxbutton"
          onclick="fb.show('user_files.php?userid=<?php echo $user['userid']; ?>&amp;facturid=<?php echo $oFacture->facturid; ?>');"><img
                src="images/icon/jpg.png"> Voir les fichiers joints (<?php echo $pj[0]; ?>)</span>
    <div class="clear"></div>
    <?php echo $formLivraison->start(); ?>
    <?php echo $formLivraison->input('action'); ?>
    <?php echo $formLivraison->input('userid'); ?>
    <?php echo $formLivraison->input('facturid'); ?>
    <div class="infos_client small">
        <?php echo empty($message_livraison) ? '' : $message_livraison; ?>
        <h3>Coordonnées de livraison <a
                    href="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=fr&amp;geocode=&amp;q=<?php echo urlencode($oFacture->adresse); ?>+<?php echo $oFacture->codepostal; ?>+<?php echo urlencode($oFacture->ville); ?>"
                    target="_blank"><img src="images/localiser.gif" width="25" align="absmiddle" title="Localiser"></a>
        </h3>
        <table>
            <tr>
                <td>Mail :</td>
                <td><?php echo $edit_mail; ?></td>
            </tr>
            <tr>
                <td>Raison sociale :</td>
                <td><?php echo $edit_societe; ?></td>
            </tr>
            <tr>
                <td>Civilité *:</td>
                <td><?php echo $edit_civilite; ?></td>
            </tr>
            <tr>
                <td>Nom *:</td>
                <td><?php echo $edit_nom; ?></td>
            </tr>
            <tr>
                <td>Prénom *:</td>
                <td><?php echo $edit_prenom; ?></td>
            </tr>
            <tr>
                <td>Adresse *:</td>
                <td><?php echo $edit_adresse; ?></td>
            </tr>
            <tr>
                <td>Adresse comp. :</td>
                <td><?php echo $edit_adresse2; ?></td>
            </tr>
            <tr>
                <td>CP :</td>
                <td><?php echo $edit_cp; ?></td>
            </tr>
            <tr>
                <td>Ville :</td>
                <td><?php echo $edit_ville; ?></td>
            </tr>
            <tr>
                <td>Pays :</td>
                <td>
                    <img src="images/drapeaux/br/<?php echo $oFacture->diminutif; ?>.gif" align="absmiddle">
                    <?php echo $formLivraison->input('paysid_nouse'); ?>
                    <?php echo $formLivraison->input('paysid'); ?>
                </td>
            </tr>
            <tr>
                <td>Téléphone *:</td>
                <td><?php echo $edit_telephone . " <span class=\"suivilink\" onclick=\"show_choix_suivi(this,'ficheinfo')\" style=\"color:blue;\"><img src=\"images/ring_phone_2.gif\"/>" . $oFacture->telephone . "</span>
                <span class=\"addsuivi\">
    <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>
    <span class=\"suivilink\" onclick=\"suiviauto('tel_out','client'," . $user['userid'] . ",'Appel téléphone " . addslashes($user['nom'] . " " . $user['prenom']) . "',undefined);epigy_call('" . $oFacture->telephone . "','" . ($user['langue'] == 'FR' ? $user_info['telFR'] : $user_info['telEN']) . "');\">Click to call</span>
    <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span></span>"; ?></td>
            </tr>
            <tr>
                <td>Infos transporteur :</td>
                <td><?php echo $edit_infotranspo; ?><span id="infotranspolen"></span></td>
            </tr>
        </table>
        <?php echo $submit_livraison; ?>
        <p style="text-align: left; padding: 0; margin: 10px 0 0; font-size: 10px;">* Champs obligatoire</p>
    </div>
    <?php echo $formLivraison->end(); ?>
    <div class="infos_client small">
        <h3>Coordonnées de facturation <a
                    href="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=fr&amp;geocode=&amp;q=<?php echo urlencode($user['adresse']); ?>+<?php echo $user['codepostal']; ?>+<?php echo urlencode($user['ville']); ?>"
                    target="_blank"><img src="images/localiser.gif" width="25" align="absmiddle" title="Localiser"></a>
        </h3>
        <span class="langue"><?php echo empty($lang_flag) ? '' : $lang_flag; ?>
            </span>
        <p style="text-align: center; margin: 0; padding: 0;"><?php echo usericon($user['vip'], 30); ?> <strong>Client
                n° <?php echo $user['userid']; ?></strong></p>
        <table>
            <tr>
                <td>Mail :</td>
                <td><?php echo $user['mail']; ?></td>
            </tr>
            <tr>
                <td>Raison sociale :</td>
                <td><?php echo $user['raisonsociale']; ?></td>
            </tr>
            <tr>
                <td>Civilité :</td>
                <td><?php echo $user['civilite']; ?></td>
            </tr>
            <tr>
                <td>Nom :</td>
                <td><?php echo $user['nom']; ?></td>
            </tr>
            <tr>
                <td>Prénom :</td>
                <td><?php echo $user['prenom']; ?></td>
            </tr>
            <tr>
                <td>Adresse :</td>
                <td><?php echo $user['adresse']; ?></td>
            </tr>
            <tr>
                <td>CP :</td>
                <td><?php echo $user['codepostal'] . ' ' . $user['ville']; ?></td>
            </tr>
            <tr>
                <td>Pays :</td>
                <td><img src="images/drapeaux/br/<?php echo $user['diminutif']; ?>.gif"
                         align="absmiddle"> <?php echo $user['libelle']; ?></td>
            </tr>
            <tr>
                <td>Téléphone :</td>
                <td><?php echo "<span class=\"suivilink\" onclick=\"show_choix_suivi(this,'ficheinfo')\" style=\"color:blue;\"><img src=\"images/ring_phone_2.gif\"/>" . $user['telephone'] . "</span>
                <span class=\"addsuivi\">
    <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>
    <span class=\"suivilink\" onclick=\"suiviauto('tel_out','client'," . $user['userid'] . ",'Appel téléphone " . addslashes($user['nom'] . " " . $user['prenom']) . "',undefined);epigy_call('" . $user['telephone'] . "','" . ($user['langue'] == 'FR' ? $user_info['telFR'] : $user_info['telEN']) . "');\">Click to call</span>
    <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span></span>";
                    if ($user['portable'] != '') {
                        echo
                            "<br><span class=\"suivilink\" onclick=\"show_choix_suivi(this,'ficheinfo')\" style=\"color:blue;\"><img src=\"images/ring_phone_2.gif\"/>" . $user['portable'] . "</span>
                <span class=\"addsuivi\">
    <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>
    <span class=\"suivilink\" onclick=\"suiviauto('tel_out','client'," . $user['userid'] . ",'Appel téléphone " . addslashes($user['nom'] . " " . $user['prenom']) . "',undefined);epigy_call('" . $user['portable'] . "','" . ($user['langue'] == 'FR' ? $user_info['telFR'] : $user_info['telEN']) . "');\">Click to call</span>
    <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span></span>";
                    } ?></td>
            </tr>
            <tr>
                <td>TVA intracommunautaire :</td>
                <td><?php echo $user['tvaintracom']; ?></td>
            </tr>
            <tr>
                <td>Heures d'appel :</td>
                <td><?php echo $contacthour; ?></td>
            </tr>
            <tr>
                <td>Profession :</td>
                <td><?php echo $user['profession']; ?></td>
            </tr>
        </table>
        <div>
            <form name="form_mf" method="post" action="#">
                <input type="hidden" name="modiffacturation" value="1">
                <input type="hidden" id="useridmf_new" name="useridmf_new" value="">
            </form>
            <a class="btnmodiffac" onclick="jQuery('#inputmf').show()">Modifier compte de facturation</a>
            <div id="inputmf"><input type="text" value="Rechercher" onkeyup="srcuser(this.value);"
                                     onclick="this.value=''">
                <div id="user_results"
                     style="position:absolute;border:solid 1px black; background:white; display:none; padding:4px;z-index:999;"></div>
                <br><span id="cadeauuser"></span></div>
        </div>
    </div>

    <!--  BLOC CONTRE MARQUE -->
    <?php echo $formContremarque->start(); ?>
    <div class="infos_client small">
        <?php echo empty($message_contremarque) ? '' : $message_contremarque; ?>
        <h3>Garantie (contremarque)</h3>
        <?php echo $formContremarque->input('addcontremarque'); ?>
        <?php echo $formContremarque->input('facturid'); ?>
        <table id="contremarquetbl"
               style="<?php echo !$tabGarantie && !isset($_POST['addcontremarque']) ? 'display : none;' : ''; ?>">
            <tr>
                <td>Civilité :</td>
                <td><?php echo $edit_civilite_gar; ?></td>
            </tr>
            <tr>
                <td>Raison sociale:</td>
                <td><?php echo $edit_societe_gar; ?></td>
            </tr>
            <tr>
                <td>Prénom :</td>
                <td><?php echo $edit_prenom_gar; ?></td>
            </tr>
            <tr>
                <td>Nom :</td>
                <td><?php echo $edit_nom_gar; ?></td>
            </tr>
            <tr>
                <td>Adresse *:</td>
                <td><?php echo $edit_adresse_gar; ?></td>
            </tr>
            <tr>
                <td>CP *:</td>
                <td><?php echo $edit_cp_gar; ?></td>
            </tr>
            <tr>
                <td>Ville :</td>
                <td><?php echo $edit_ville_gar; ?></td>
            </tr>
            <tr>
                <td>Tél. :</td>
                <td><?php echo $edit_tel_gar; ?></td>
            </tr>
            <tr>
                <td>Port. :</td>
                <td><?php echo $edit_port_gar; ?></td>
            </tr>
            <tr>
                <td>Email :</td>
                <td><?php echo $edit_mail_gar; ?></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center;"><span style="font-weight: bold;">Prix de vente unitaire revendeur en TTC</span>
                </td>
            </tr>
            <?php echo $edit_pv_revendeur; ?>
            <tr>
                <td colspan="2"><?php echo $submit_contremarque; ?></td>
            </tr>
            <tr>
                <td colspan="2"><p style="text-align: left; padding: 0; margin: 10px 0 0; font-size: 10px;">* Champs
                        obligatoire</p></td>
            </tr>
        </table>
        <p id="addcontremarquelink" style="<?php echo !$tabGarantie ? '' : 'display:none;'; ?>"><a
                    href="javascript:newcontremarque(true)"><img src="images/icon/edit_add.png" align="absmiddle">
                Ajouter</a></p>
        <p id="hidecontremarquelink" style="<?php echo !$tabGarantie ? 'display:none;' : ''; ?>"><a
                    href="javascript:newcontremarque(false)"><img src="images/icon/cancel.png" width="16"
                                                                  align="absmiddle"> Annuler</a></p>


    </div>
    <?php echo $formContremarque->end(); ?>
    <div class="clear"></div>

    <br>
</div>

<?php
$info_fourn = $DB_site2->query_first("SELECT suivi FROM suivi_commande FORCE INDEX (facturid) WHERE type='advfourn' AND facturid='" . mysqli_real_escape_string($DB_site2->link_id, $_GET['facturid']) . "' ORDER BY id DESC");
$info_stock = $DB_site2->query_first("SELECT suivi FROM suivi_commande FORCE INDEX (facturid) WHERE type='advstock' AND facturid='" . mysqli_real_escape_string($DB_site2->link_id, $_GET['facturid']) . "' ORDER BY id DESC");
$info_trspt = $DB_site2->query_first("SELECT suivi FROM suivi_commande FORCE INDEX (facturid) WHERE type='advtrspt' AND facturid='" . mysqli_real_escape_string($DB_site2->link_id, $_GET['facturid']) . "' ORDER BY id DESC");
$info_sls = $DB_site2->query_first("SELECT suivi FROM suivi_commande FORCE INDEX (facturid) WHERE type='advsls' AND facturid='" . mysqli_real_escape_string($DB_site2->link_id, $_GET['facturid']) . "' ORDER BY id DESC");
$info_mes = $DB_site2->query_first("SELECT suivi FROM suivi_commande FORCE INDEX (facturid) WHERE type='advmes' AND facturid='" . mysqli_real_escape_string($DB_site2->link_id, $_GET['facturid']) . "' ORDER BY id DESC");

?>

<!-- cadre information -->
<div class="commande_detail size_normal" id="facture_details_4" style="">
    <div class="col-xs-12"><h2 class="customized">Informations</h3></div>

    <div class="col-xs-12"><a class="infolien lien_info_importante" href="javascript:void(0);"
                              onclick="change('info_gen');change_image('edit_info1', '<?php echo BACK_URL; ?>/images/icon/edit_add_22.png', '<?php echo BACK_URL; ?>/images/icon/edit_remove.png');">Information
            Générale <img src="images/icon/edit_add_22.png" id="edit_info1"></a></div>
    <div class="col-xs-12">
        <form id="info_gen" method="post" style="display: none;">
            <textarea name="info_importante" cols="120"
                      rows="4"><?php echo $tabInfoImp['info_importante']; ?></textarea>
            <input type="submit" name="submit_info" class="button_info" value="Enregistrer">
        </form>
    </div>
    <?php if (isset($tabInfoImp['info_importante']) && $tabInfoImp['info_importante'] != '') { ?>
        <div class="col-xs-12 info_importante">
            <p class="titre">Information Générale</p>
            <p class="description"><?php echo $tabInfoImp['info_importante']; ?></p>
        </div><br><br>
    <?php } ?>

    <div class="col-xs-12"><a class="infolien lien_info_importante" href="javascript:void(0);"
                              onclick="change('info_fourn');change_image('edit_info2', '<?php echo BACK_URL; ?>/images/icon/edit_add_22.png', '<?php echo BACK_URL; ?>/images/icon/edit_remove.png');">Information
            Fournisseur <img src="images/icon/edit_add_22.png" id="edit_info2"></a></div>
    <div class="col-xs-12">
        <div id="info_fourn" class="col-xs-12" style="display: none;">
            <textarea id="fournadvcomment2" cols="120" rows="4"><?php echo $info_fourn[0]; ?></textarea>
            <button name="submit_info_fourn" class="button_info"
                    onclick="updadvcomment2('fourn','<?php echo $_GET['facturid'] ?>')">Enregistrer
            </button>
        </div>
    </div>
    <?php if (isset($info_fourn[0]) && $info_fourn[0] != '') { ?>
        <div class="col-xs-12 info_importante">
            <p class="titre">Information Fournisseur</p>
            <p class="description"><?php echo $info_fourn[0]; ?></p>
        </div><br>
    <?php } ?>

    <div class="col-xs-12"><a class="infolien lien_info_importante" href="javascript:void(0);"
                              onclick="change('info_stock');change_image('edit_info3', '<?php echo BACK_URL; ?>/images/icon/edit_add_22.png', '<?php echo BACK_URL; ?>/images/icon/edit_remove.png');">Information
            Stock <img src="images/icon/edit_add_22.png" id="edit_info3"></a></div>
    <div class="col-xs-12">
        <div id="info_stock" class="col-xs-12" style="display: none;">
            <textarea id="stockadvcomment2" cols="120" rows="4"><?php echo $info_stock[0]; ?></textarea>
            <button name="submit_info_stock" class="button_info"
                    onclick="updadvcomment2('stock','<?php echo $_GET['facturid'] ?>')">Enregistrer
            </button>
        </div>
    </div>
    <?php if (isset($info_stock[0]) && $info_stock[0] != '') { ?>
        <div class="col-xs-12 info_importante">
            <p class="titre">Information Stock</p>
            <p class="description"><?php echo $info_stock[0]; ?></p>
        </div><br>
    <?php } ?>

    <div class="col-xs-12"><a class="infolien lien_info_importante" href="javascript:void(0);"
                              onclick="change('info_trspt');change_image('edit_info4', '<?php echo BACK_URL; ?>/images/icon/edit_add_22.png', '<?php echo BACK_URL; ?>/images/icon/edit_remove.png');">Information
            Transporteur <img src="images/icon/edit_add_22.png" id="edit_info4"></a></div>
    <div class="col-xs-12">
        <div id="info_trspt" class="col-xs-12" style="display: none;">
            <textarea id="trsptadvcomment2" cols="120" rows="4"><?php echo $info_trspt[0]; ?></textarea>
            <button name="submit_info_trspt" class="button_info"
                    onclick="updadvcomment2('trspt','<?php echo $_GET['facturid'] ?>')">Enregistrer
            </button>
        </div>
    </div>
    <?php if (isset($info_trspt[0]) && $info_trspt[0] != '') { ?>
        <div class="col-xs-12 info_importante">
            <p class="titre">Information Transporteur</p>
            <p class="description"><?php echo $info_trspt[0]; ?></p>
        </div><br>
    <?php } ?>

    <div class="col-xs-12"><a class="infolien lien_info_importante" href="javascript:void(0);"
                              onclick="change('info_sls');change_image('edit_info5', '<?php echo BACK_URL; ?>/images/icon/edit_add_22.png', '<?php echo BACK_URL; ?>/images/icon/edit_remove.png');">Information
            Installateur <img src="images/icon/edit_add_22.png" id="edit_info5"></a></div>
    <div class="col-xs-12">
        <div id="info_sls" class="col-xs-12" style="display: none;">
            <textarea id="slsadvcomment2" cols="120" rows="4"><?php echo @$info_sls[0]; ?></textarea>
            <button name="submit_info_sls" class="button_info"
                    onclick="updadvcomment2('sls','<?php echo $_GET['facturid'] ?>')">Enregistrer
            </button>
        </div>
    </div>
    <?php if (isset($info_sls[0]) && $info_sls[0] != '') { ?>
        <div class="col-xs-12 info_importante">
            <p class="titre">Information Installateur</p>
            <p class="description"><?php echo $info_sls[0]; ?></p>
        </div><br>
    <?php } ?>


    <div class="col-xs-12"><a class="infolien lien_info_importante" href="javascript:void(0);"
                              onclick="change('info_mes');change_image('edit_info6', '<?php echo BACK_URL; ?>/images/icon/edit_add_22.png', '<?php echo BACK_URL; ?>/images/icon/edit_remove.png');">Information
            Mise en service <img src="images/icon/edit_add_22.png" id="edit_info6"></a></div>
    <div class="col-xs-12">
        <div id="info_mes" class="col-xs-12" style="display: none;">
            <textarea id="mesadvcomment2" cols="120" rows="4"><?php echo $info_mes[0]; ?></textarea>
            <button name="submit_info_mes" class="button_info"
                    onclick="updadvcomment2('mes','<?php echo $_GET['facturid'] ?>')">Enregistrer
            </button>
        </div>
    </div>
    <?php if (isset($info_mes[0]) && $info_mes[0] != '') { ?>
        <div class="col-xs-12 info_importante">
            <p class="titre">Information Mise en service</p>
            <p class="description"><?php echo $info_mes[0]; ?></p>
        </div><br>
    <?php } ?>


    <div class="clear"></div>

</div>
<!-- FIN cadre information -->