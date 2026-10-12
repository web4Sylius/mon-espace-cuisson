<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(SITE_PATH."/class/fournisseur.class.php");
require_once(SITE_PATH."/class/stock.class.php");

$class_fournisseur = new fournisseur($DB_site2);

global $authDelSuivi;
$authDelSuivi=0;

//************************//
// GESTION DES EVENEMENTS //
//************************//
if(isset($_GET['action'])){

    //Suppression d'un contact fournisseur
    if($_GET['action'] == "del"){

        $sql = "SELECT societe";
        $sql .= " FROM fournisseur";
        $sql .= " WHERE id = ".$_GET['id'];
        $aSociete = $DB_site2->query_first($sql);
        
        $sql = "DELETE FROM fournisseur";
        $sql .= " WHERE id = ".$_GET['id'];
        $DB_site2->query($sql);

        header("Location: ".BACK_URL."/fournisseurs_liste.php?action=editer&fournisseur=".$aSociete['societe']);
        exit;
    }
}

 
//Listing des types fournisseurs
$sql = "SELECT types";
$sql .= " FROM fournisseur_condition"; 
$sql .= " ORDER BY types";
$req = $DB_site2->query($sql);
$aTypeFourn = array();
while($row = $DB_site2->fetch_array($req)){
    $aTypes = explode(',',$row['types']);
    foreach($aTypes as $le_type){
        $aTypeFourn[$le_type]++;
    }
}

//Tableau de listing des types de commerciaux
$types = array(
    "facture"      => "Comptabilité",
    "fourn"        => "Lieu produit fournisseur",
    "trspt"        => "Lieu Transporteur",
    "sls"          => "Lieu Installateur",
    "fournadv"     => "ADV fournisseur",
    "trsptadv"     => "ADV transporteur",
    "slsadv"       => "ADV installateur",
    "garadv"       => "ADV garantie",
    "commercial"   => "Commercial", 
    "sav"          => "SAV",
    "tech"         => "Technique",
    "savclient"    => "Info Client",
    "trsptcontact" => "Informatique",
    "trsptsuivi"   => "Suivi commande",
    "avoir"        => "Demande d'avoir",
    "direction"    => "Direction",
    "litige"       => "Contact litiges",
    "old"          => "Ancien contact",
    "marketing"    => "Marketing"
);
asort($types);

if(!empty($_POST)){

    //*************************************//
    // AJOUTER/EDITION CONTACT FOURNISSEUR //
    //*************************************//
    if(isset($_POST['modif']) && $_POST['modif'] != ''){
 
        $error = "";
        if($_POST['societe'] == "") $error = "Il faut fournir un nom de société";
        if(!filter_input(INPUT_POST,"mail",FILTER_VALIDATE_EMAIL)) $_POST['mail']="";
        
        //Création/mise à jour du contact fournisseur
        $sql = "fournisseur SET";
        $sql .= " type       = '".$_POST['type']."'";
        $sql .= ", societe   = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['societe'])."'";
        $sql .= ", nom       = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['nom'])."'";
        $sql .= ", prenom    = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['prenom'])."'";
        $sql .= ", fonction  = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['fonction'])."'";
        $sql .= ", mail      = '".trim($_POST['mail'])."'";
        $sql .= ", telephone = '".$_POST['telephone']."'";
        $sql .= ", portable  = '".$_POST['portable']."'";
        $sql .= ", fax       = '".$_POST['fax']."'";
        $sql .= ", adresse1  = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['adresse1'])."'";
        $sql .= ", adresse2  = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['adresse2'])."'";
        $sql .= ", cp        = '".$_POST['cp']."'";
        $sql .= ", ville     = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['ville'])."'";
        $sql .= ", paysid    = ".$_POST['paysid']."";
        $sql .= ", horaires  = '".$_POST['horaires']."'";
        $sql .= ", groupes   = '".$_POST['groupes']."'";
        $sql .= ", code      = '".$_POST['codea']."'";
        
       
        if($_POST['modif'] == 0){
            $sql = "INSERT INTO ".$sql;

            if($_POST['new_societe'] == 1){
                $sql2 = "INSERT INTO fournisseur_condition (";
                $sql2 .= "societe";
                $sql2 .= ") VALUES (";
                $sql2 .= "'".mysqli_real_escape_string($DB_site2->link_id,$_POST['societe'])."'";
                $sql2 .= ")"; 
            }
        } else {
            $sql = "UPDATE ".$sql;
            $sql .= " WHERE id = ".$_POST['modif'];
        }

        if($error == ""){
            $result = $DB_site2->query($sql);
            if(isset($sql2) && $sql2 != '') $DB_site2->query($sql2);
        } 

        //Si création/MAJ effectuée, on informe le suivi
        if($result){

            //Construction du contenu à afficher dans le suivi
            $suivitxt = ""
            . $_POST['nom']." ".$_POST['prenom']."<br>"
            . $_POST['fonction']." ".$_POST['societe']
            . (!empty($_POST['adresse1']) ? "<br>".$_POST['adresse1'] : "")
            . (!empty($_POST['adresse2']) ? "<br>".$_POST['adresse2'] : "")
            . "<br>".$_POST['cp']." ".$_POST['ville']
            . "<br>".$_POST['telephone']." ".$_POST['portable']." ".$_POST['fax']
            . (!empty($_POST['horaires']) ? "<br>".$_POST['horaires'] : "");

            if($_POST['modif'] == 0){
                $error    = "Fournisseur ajouté";
                $suivitxt = "Ajout contact :<br>".$suivitxt;
            } else {
                $error    = "Fournisseur modifié";
                $suivitxt = "Modification contact :<br>".$suivitxt;
            }

            //Insertion dans le suivi
            $sql = "INSERT INTO suivi_fournisseur SET";
            $sql .= " fournisseur  = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['societe'])."'";
            $sql .= " , commercial = '$user_info[username]'";
            $sql .= " , date       = ".time();
            $sql .= " , suivi      = '".mysqli_real_escape_string($DB_site2->link_id,$suivitxt)."'";
            $DB_site2->query($sql);
        }

        header("Location: fournisseurs_liste.php?action=editer&fournisseur=".$_POST['societe']);
        exit;
    }

    //**********************************//
    // ASSOCATION MARQUE => FOURNISSEUR //
    //**********************************//
    if(isset($_POST['action']) && $_POST['action'] == "marques2"){
        
        foreach($_POST as $fourn => $marq){
            
            if(substr($fourn,0,5) == "marq_"){

                $fourn = substr($fourn,5);

                //Récupération du libellé fournisseur pour mise à jour
                $sql = "SELECT societe";
                $sql .= " FROM fournisseur";
                $sql .= " WHERE id = '".$fourn."'";
                $adv = $DB_site2->query_first($sql);

                //Mise à jour des marques associées au fournisseur
                $sql = "UPDATE fournisseur SET";
                $sql .= " marques = '".$marq."'";
                $sql .= " WHERE societe = '".addslashes($adv[0])."'";
                $DB_site2->query($sql);
            }
        }
    }

    //********************************************//
    // AJOUT CONTACT SUIVI COMMANDES FOURNISSEUR  //
    //********************************************//
    if(isset($_POST['newsuivi'])){

        $sql = "SELECT *";
        $sql .= " FROM fournisseur";
        $sql .= " WHERE id = ".$_POST['newsuivi'];
        $contact = $DB_site2->query_first($sql);
        $suivis = explode(",",$contact['suivi']);

        if(!in_array($_POST['group'], $suivis)){

            $suivis[] = $_POST['group'];

            $sql = "UPDATE fournisseur SET";
            $sql .= " suivi   = '".implode(",", $suivis)."'";
            $sql .= ", suivih = ".intval($_POST['suivih']);
            $sql .= " WHERE id = ".$_POST['newsuivi'];
            $DB_site2->query($sql);
        }
    }

    //************************//
    // MISE A JOUR CONDITIONS //
    //************************//
    if(isset($_POST['conditions'])){

        $sqlCond = "societe      = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
        $sqlCond .= ", tarif     = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['tarif'])."'";
        $sqlCond .= ", commande  = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['commande'])."'";
        $sqlCond .= ", livraison = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['livraison'])."'";
        $sqlCond .= ", sav       = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['sav'])."'";
        $sqlCond .= ", divers    = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['divers'])."'";
        $sqlCond .= ", export    = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['export'])."'";
        $sqlCond .= ", codes     = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['codes'])."'";
        $sqlCond .= ", tvaintra  = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['tvaintra'])."'";
        
        if($_POST['conditions'] == 0){
            //Construction du suivi
            $suivitxt = ""
            . "Mise à jour conditions :"
            . ($_POST['tarif'] != '' ? "<br>Tarif : " . $_POST['tarif'] : '')
            . ($_POST['commande'] != '' ? "<br>Commande : " . $_POST['commande'] : '')
            . ($_POST['livraison'] != '' ? "<br>Livraison : " . $_POST['livraison'] : '')
            . ($_POST['sav'] != '' ? "<br>SAV : " . $_POST['sav'] : '')
            . ($_POST['export'] != '' ? "<br>Export : " . $_POST['export'] : '')
            . ($_POST['divers'] != '' ? "<br>Divers : " . $_POST['divers'] : '')
            . ($_POST['codes'] != '' ? "<br>Codes : " . $_POST['codes'] : '');

            $sql = "INSERT INTO fournisseur_condition SET ".$sqlCond;
        } else {
            //Récupération des conditions avant modification
            //Utile pour n'indiquer que les conditions qui ont changé dans le suivi
            $sql = "SELECT tarif, commande, livraison, sav, divers, export, codes";
            $sql .= " FROM fournisseur_condition";
            $sql .= " WHERE id = " . $_POST['conditions'];
            $aConditions = $DB_site2->query_first($sql);

            //Construction du suivi
            $suivitxt = ""
            . "Mise à jour conditions :"
            . ($aConditions['tarif'] != $_POST['tarif'] ? '<br>Tarif : ' . $_POST['tarif'] : '')
            . ($aConditions['commande'] != $_POST['commande'] ? '<br>Commande : ' . $_POST['commande'] : '')
            . ($aConditions['livraison'] != $_POST['livraison'] ? '<br>Livraison : ' . $_POST['livraison'] : '')
            . ($aConditions['sav'] != $_POST['sav'] ? '<br>SAV : ' . $_POST['sav'] : '')
            . ($aConditions['divers'] != $_POST['divers'] ? '<br>Divers : ' . $_POST['divers'] : '')
            . ($aConditions['export'] != $_POST['export'] ? '<br>Export : ' . $_POST['export'] : '')
            . ($aConditions['codes'] != $_POST['codes'] ? '<br>Codes : ' . $_POST['codes'] : '');

            $sql = "UPDATE fournisseur_condition SET ".$sqlCond." WHERE id = ".$_POST['conditions'];
        }
        
        //Mise à jour des données dans la table "fournisseur_condition"
        $DB_site2->query($sql);
        
        $sql = "INSERT INTO suivi_fournisseur SET";
        $sql .= " fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
        $sql .= ", commercial = '$user_info[username]'";
        $sql .= ", date       = ".time()."";
        $sql .= ", suivi      = '".mysqli_real_escape_string($DB_site2->link_id,$suivitxt)."'";
        $DB_site2->query($sql);
    }

    //*******************************************//
    // CREATION/MAJ INFOS GENERALES FOURNISSEUR  //
    //*******************************************//
    if(isset($_POST['fourn_infos'])){ 

        //Récupération des anciennes valeurs pour inscription dans suivi
        $sql = "SELECT *";
        $sql .= " FROM fournisseur_condition";
        $sql .= " WHERE societe = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'"; 
        $aConditions = $DB_site2->query_first($sql);
        

        //Mise à jour de toutes les tables contenant le libelle fournisseur pour maintenir l'association...
        if($_POST['fournisseur_libelle'] != $_GET['fournisseur']){

            $sql = "UPDATE fournisseur SET";
            $sql .= " societe = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur_libelle'])."'";
            $sql .= " WHERE societe = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
            $DB_site2->query($sql);

            $sql = "UPDATE fournisseur_code SET";
            $sql .= " fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur_libelle'])."'";
            $sql .= " WHERE fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
            $DB_site2->query($sql);

            $sql = "UPDATE fournisseur_condition SET";
            $sql .= " societe = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur_libelle'])."'";
            $sql .= " WHERE societe = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
            $DB_site2->query($sql);

            $sql = "UPDATE fournisseur_files SET";
            $sql .= " fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur_libelle'])."'";
            $sql .= " WHERE fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
            $DB_site2->query($sql);

            $sql = "UPDATE fournisseur_frais SET";
            $sql .= " fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur_libelle'])."'";
            $sql .= " WHERE fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
            $DB_site2->query($sql);

            $sql = "UPDATE fournisseur_offre SET";
            $sql .= " fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur_libelle'])."'";
            $sql .= " WHERE fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
            $DB_site2->query($sql);

            $sql = "UPDATE article_fournisseur SET";
            $sql .= " fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur_libelle'])."'";
            $sql .= " WHERE fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
            $DB_site2->query($sql);

            $sql = "UPDATE facture_fournisseur SET";
            $sql .= " fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur_libelle'])."'";
            $sql .= " WHERE fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
            $DB_site2->query($sql);

            $sql = "UPDATE suivi_fournisseur SET";
            $sql .= " fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur_libelle'])."'";
            $sql .= " WHERE fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
            $DB_site2->query($sql);
            
            $sql = "UPDATE garantie SET";
            $sql .= " fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur_libelle'])."'";
            $sql .= " WHERE fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
            $DB_site2->query($sql);
			
			$sql = "UPDATE utilisateur_relation SET";
            $sql .= " fournisseur_societe = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur_libelle'])."'";
            $sql .= " WHERE fournisseur_societe = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
            $DB_site2->query($sql);

            $maj_libelle = true;
        }

        $_POST['fourn_infos'] = intval($_POST['fourn_infos']);
        
        $sqlInfos = "types            = '".implode(',',$_POST['ftype'])."'";
        $sqlInfos .= ", devise        = '".$_POST['devise']."'";
        $sqlInfos .= ", compte_client = '".$_POST['compte_client']."'";
        $sqlInfos .= ", langue        = '".$_POST['flangue']."'";
        $sqlInfos .= ", activite      = '".$_POST['activite']."'";
        $sqlInfos .= ", pdts_vendus   = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['pdts_vendus'])."'";
        $sqlInfos .= ", ar            = ".intval($_POST['ar'])."";
        $sqlInfos .= ", tvaintra      = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['tvaintra'])."'";
        $sqlInfos .= ", fourn_note    = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur_note'])."'";
        $sqlInfos .= ", tracking  = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['tracking'])."'";
        $sqlInfos .= ", societe_facturation_id  = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['societe_facturation'])."'";
        
        // TSI modif table: ALTER TABLE `fournisseur_condition` ADD `fourn_admin` INT NULL DEFAULT NULL AFTER `tvaintra`;
        $sqlInfos .= ", fourn_admin  = '".$_POST['fourn_admin']."'";
        
        if ($_POST['fourn_admin'] == "") $testIt = 0;
        else $testIt = 1;
          
        if($_POST['fourn_infos'] == 0){
            $sql = "INSERT INTO fournisseur_condition SET";
            $sql .= " societe='".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
            $sql .= ",".$sqlInfos;
        } else {  
            $sql = "UPDATE fournisseur_condition SET ";
            $sql .= $sqlInfos;
            $sql .= " WHERE id = ".$_POST['fourn_infos'];
        }
        
        $DB_site2->query($sql);
        
        // Màj info services généraux
        $maj_401 = false;
        if(isset($_POST['code401'])){
           
            $maj_401 = true;

            $sql = "SELECT *";
            $sql .= " FROM fournisseur_frais";
            $sql .= " WHERE fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
            $ff = $DB_site2->query_first($sql);

            $sqlSup = "fournisseur_frais SET";
            $sqlSup .= " code401          = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['code401'])."'";
            if(isset($_POST['compte'])) $sqlSup .= ", compte = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['compte'])."'";
            if(isset($_POST['code_analytique'])) $sqlSup .= ", code_analytique = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['code_analytique'])."'";
        
            if($ff != ""){
                $sql = "UPDATE ".$sqlSup;
                $sql .= " WHERE fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
            } else if(in_array("service généraux",$_POST['ftype']) || in_array("communication/réseaux sociaux",$_POST['ftype']) || in_array("stockage",$_POST['ftype']) || in_array("école",$_POST['ftype']) || in_array("partenaire",$_POST['ftype'])){
                $sql = "INSERT INTO ".$sqlSup;
                $sql .= ", fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
            }
            $DB_site2->query($sql);

            $sql = "SELECT id, code";
            $sql .= " FROM fournisseur";
            $sql .= " WHERE type = 'facture'";
            $sql .= " AND societe = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
            $req = $DB_site2->query($sql);
            if($DB_site2->num_rows($req) > 0){
                while($aFourn = $DB_site2->fetch_array($req)){
                    $sql = "UPDATE fournisseur SET";
                    $sql .= " code = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['code401'])."'";
                    $sql .= " WHERE id = ".$aFourn['id'];
                    $DB_site2->query($sql);
                }
            } else {
                //Récupération du pays
                $sql = "SELECT paysid";
                $sql .= " FROM fournisseur";
                $sql .= " WHERE societe = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
                $sql .= " LIMIT 1";
                $aPays = $DB_site2->query_first($sql);

                //Récupération des marques
                $sql = "SELECT marques";
                $sql .= " FROM fournisseur";
                $sql .= " WHERE societe = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
                $sql .= " LIMIT 1";
                $aMarques = $DB_site2->query_first($sql);

                $sql = "INSERT INTO fournisseur (";
                $sql .= "type";
                $sql .= ", societe";
                $sql .= ", paysid";
                $sql .= ", marques";
                $sql .= ", code";
                $sql .= ") VALUES (";
                $sql .= "'facture'";
                $sql .= ", '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
                $sql .= ", ".($aPays['paysid'] != '' ? $aPays['paysid'] : 57);
                $sql .= ", '".$aMarques['marques']."'";
                $sql .= ", '".mysqli_real_escape_string($DB_site2->link_id,$_POST['code401'])."'";
                $sql .= ")";
                $DB_site2->query($sql);
            }

            if($ff['code401'] != ''){
                $code401 = $ff['code401'];
            } else if($aFourn['code'] != ''){
                $code401 = $aFourn['code'];
            } else {
                $code401 = '';
            }
        }
        
        $DB_site2->query("DELETE FROM code_comptable WHERE fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'");
        if(isset($_POST['codecomptable'])){
            foreach($_POST['codecomptable'] as $typecode=>$code){
                $infos=$DB_site2->query_first("SELECT info FROM code_comptable WHERE type='".mysqli_real_escape_string($DB_site2->link_id,$typecode)."'");
            
                $sql="INSERT INTO code_comptable SET 
                code='".mysqli_real_escape_string($DB_site2->link_id,substr($code,0,6))."',
                type='".mysqli_real_escape_string($DB_site2->link_id,$typecode)."',
                fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."',
                info='".mysqli_real_escape_string($DB_site2->link_id,$infos['info'])."'";
                $DB_site2->query($sql);
            }
        }
        

        //Ajout dans suivi fournisseur
        $suivitxt = "Mise à jour infos :";
        
        // TSI suivi de la modification compte administrateur;
        if ($_POST['fourn_admin'] == "") $testIt = 0;
        else $testIt = 1;
        if ($aConditions['fourn_admin'] != $testIt)
            $suivitxt .= "<br> Compte administrateur : ".$aConditions['fourn_admin']." => ".$testIt;
        
        if($aConditions['societe'] != mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur_libelle'])) $suivitxt .= "<br>Libellé : ".$aConditions['societe']." => ".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur_libelle']);
        if($aConditions['types'] != implode(',',$_POST['ftype'])) $suivitxt .= "<br>Types : ".$aConditions['types']." => ".implode(',',$_POST['ftype']);
        if($aConditions['devise'] != $_POST['devise']) $suivitxt .= "<br>Devise : ".$aConditions['devise']." => ".$_POST['devise'];
        if($aConditions['compte_client'] != $_POST['compte_client']) $suivitxt .= "<br>Compte client : ".$aConditions['compte_client']." => ".$_POST['compte_client'];
        if($aConditions['langue'] != $_POST['flangue']) $suivitxt .= "<br>Langue : ".$aConditions['langue']." => ".$_POST['flangue'];
        if($aConditions['activite'] != $_POST['activite']) $suivitxt .= "<br>Activite : ".($aConditions['activite'] == 1 ? 'Oui' : $aConditions['activite'] == 2 ? 'Non' : '')." => ".($_POST['activite'] == 1 ? 'Oui' : $_POST['activite'] == 2 ? 'Non' : '');
        if($aConditions['pdts_vendus'] != $_POST['pdts_vendus']) $suivitxt .= "<br>Pdts vendus : ".$aConditions['pdts_vendus']." => ".$_POST['pdts_vendus'];
        if($aConditions['ar'] != intval($_POST['ar'])) $suivitxt .= "<br>AR ADV : ".($aConditions['ar'] == 1 ? 'Oui' : 'Non')." => ".(intval($_POST['ar']) == 1 ? 'Oui' : 'Non');
        if($aConditions['tvaintra'] != mysqli_real_escape_string($DB_site2->link_id,$_POST['tvaintra'])) $suivitxt .= "<br>tva intra : ".$aConditions['tvaintra']." => ".mysqli_real_escape_string($DB_site2->link_id,$_POST['tvaintra']);

        if($maj_401 && $code401 != mysqli_real_escape_string($DB_site2->link_id,$_POST['code401'])) $suivitxt .= "<br>Code 401 : ".$code401." => ".mysqli_real_escape_string($DB_site2->link_id,$_POST['code401']);

        $sql = "INSERT INTO suivi_fournisseur SET";
        $sql .= " fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur_libelle'])."'";
        $sql .= ", commercial = '$user_info[username]'";
        $sql .= ", date       = ".time()."";
        $sql .= ", suivi      = '".mysqli_real_escape_string($DB_site2->link_id,$suivitxt)."'";
        $DB_site2->query($sql);

        if($maj_libelle){
            header("Location: ".BACK_URL."/fournisseurs_liste.php?action=editer&fournisseur=".$_POST['fournisseur_libelle']);
            exit;
        }
    }

    //********************//
    // AJOUT SUIVI MANUEL //
    //********************//
    if(isset($_POST['suivi'])){

        $tsrappel = 0;
        
        if($_POST['rappel'] != ""){
            
            $sp       = preg_split('/[- :\/h]/',$_POST['rappel']);
            $tsrappel = mktime($sp[3],$sp[4],0,$sp[1],$sp[0],$sp[2]);
        }
        
        $hide = intval(!isset($_POST['box']));

        $sql = "INSERT INTO suivi_fournisseur SET";
        $sql .= " fournisseur   = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
        $sql .= ", commercial   = '$user_info[username]'";
        $sql .= ", date         = ".time()."";
        $sql .= ", suivi        = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['suivi'])."'";
        $sql .= ", rappel       = ".$tsrappel."";
        $sql .= ", rappeltype   = '".$_POST['rappeltype']."'";
        $sql .= ", jour         = ".intval($_POST['jour'])."";
        $sql .= ", pour         = '".$_POST['pour']."'";
        $sql .= ", hide         = $hide";
        $sql .= ", type         = '".$_POST['type']."'";
        $sql .= ", moyen        = '".$_POST['moyen']."'";
        $sql .= ", destinataire = '".$_POST['destinataire']."'";
        $DB_site2->query($sql);
    }
}

$fournisseuras = $_GET['fournisseur'];

$codesfourn="var codesfourn={0:\"\"";
$req=$DB_site2->query("SELECT societe,code FROM `fournisseur` WHERE `code` != ''");
while($code=$DB_site2->fetch_array($req)){
    $codesfourn.=",\"".$code['societe']."\":\"".$code['code']."\"";
}
$codesfourn.="};\n";

include_once(BACK_PATH."/header.php");
include_once(BACK_PATH."/menu.php");
?>
<link href="filebox2.css" type="text/css" rel="stylesheet">
<link href="cal.css" type="text/css" rel="stylesheet">

<script language="javascript" src="calendrier.js"></script>
<script language="javascript" src="../javascripts/prototype.js"></script>
<script language="javascript" src="filebox.js"></script>
<script language="javascript">

    function opendetail(id){
        display=document.getElementById('tbl'+id).style.display;
        if(display=='') display='none';
        if(display=='none'){
            document.getElementById('p'+id).src='images/moins.jpg';
            document.getElementById('tbl'+id).style.display='block';
        }else{
            document.getElementById('p'+id).src='images/plus.jpg';
            document.getElementById('tbl'+id).style.display='none';
        }
    }

    document.onmousemove = WhereMouse;
    document.body.onclick=stopdrag;

    oldx=0;
    oldy=0;
    moveitem='';
    movefrom='';
    marqueid='';
    enabledstop=false;

    function drag(item){
        if(moveitem==''){
            var dragitem=document.getElementById(item).cloneNode(true);
            dragitem.onclick='';
            marqueid=item.substr(4);
            document.body.appendChild(dragitem);
            dragitem.id='drag_'+Math.round(Math.random()*10000);
            moveitem=dragitem.id;
            dragitem.style.position='absolute';
            dragitem.style.backgroundColor='#AAAAFF';
            setTimeout('enablestop()',1000);
        }
    }

    function enablestop(){
        enabledstop=true;
    }

    function stopdrag(){
        if(moveitem!='' && enabledstop){
            document.getElementById(moveitem).style.position='static';
            document.getElementById(moveitem).style.backgroundColor='';
            moveitem='';
            enabledstop=false;
        }
    }

    function additem(moveon){
        if(moveitem!='' && enabledstop){
    
            var delem=document.getElementById(moveitem);
            delem.innerHTML="<a href=javascript:removefromlist("+moveon+","+marqueid+",'"+delem.id+"');><img src='images/supprimer.gif' align='absmiddle'></a> "+delem.innerHTML;

            //document.getElementById(movefrom).removeChild(delem);
            document.getElementById('div'+moveon).appendChild(delem);
            
            document.getElementsByName('marq_'+moveon)[0].value+=','+marqueid;
            if(document.getElementsByName('marq_'+moveon)[0].value.substr(0,1)==',') document.getElementsByName('marq_'+moveon)[0].value=document.getElementsByName('marq_'+moveon)[0].value.substr(1);
            stopdrag();
      
        }else{
            movefrom=moveon;
        }
    }

    function removefromlist(flist,remid,rmdiv){
        var mids=document.getElementsByName('marq_'+flist)[0].value.split(',');
        document.getElementsByName('marq_'+flist)[0].value='';
        for(i=0;i<mids.length;i++){
            if(mids[i]!=remid) document.getElementsByName('marq_'+flist)[0].value+=','+mids[i]; 
        }
        if(document.getElementsByName('marq_'+flist)[0].value.substr(0,1)==',') document.getElementsByName('marq_'+flist)[0].value=document.getElementsByName('marq_'+flist)[0].value.substr(1);

        document.getElementById('div'+flist).removeChild(document.getElementById(rmdiv));
    }

    function ordermlist(){
        var elems=new Array();
        for(i=0;i<document.getElementById('mlist').getElementsByTagName('p').length;i++){
            var elem=document.getElementById('mlist').getElementsByTagName('p')[i];
            elems.push({html:elem.innerHTML,id:elem.id});
        }

        html='';
        elems.sort(function(a,b){
            var nameA=a.html.toLowerCase();
            var nameB=b.html.toLowerCase();
            if (nameA < nameB) return -1;
            if (nameA > nameB) return 1;
            return 0;

        });
        for(i=0;i<elems.length;i++){
            html+='<p id="'+elems[i].id+'" onclick="drag(this.id)">'+elems[i].html+'</p>';
        }
        document.getElementById('contlist').innerHTML=html;
    }

function WhereMouse( e ){
    var DocRef;    // Variable pour IE uniquement

    // L'événement est passée à la fonction
    // donc tous sauf IE…
    if( e){                     // Dans ce cas on obtient directement la position dans la page
        Mouse_X = e.pageX;
        Mouse_Y = e.pageY;
    }
    else{                      // Dans ce cas on obtient la position relative à la fenêtre d'affichage
        Mouse_X = event.clientX;
        Mouse_Y = event.clientY;

        //-- Il faut traiter le CAS des DOCTYPE sous IE
        if( document.documentElement && document.documentElement.clientWidth) // Donc DOCTYPE
        DocRef = document.documentElement;   // Dans ce cas c'est documentElement qui est réfèrence
        else
        DocRef = document.body;                    // Dans ce cas c'est body qui est réfèrence

        //-- On rajoute la position liée aux ScrollBars
        Mouse_X += DocRef.scrollLeft;
        Mouse_Y += DocRef.scrollTop;
    }
    if(moveitem!=''){
        document.getElementById(moveitem).style.left=(Mouse_X+5)+'px';
        document.getElementById(moveitem).style.top=(Mouse_Y+1)+'px';
    }
}

function addgroup(name){
    if(name.indexOf(',')>-1){
    alert('Il n\'est pas possible d\'utiliser un groupe qui contient une virgule');
    return;
    }
    var ugroupes=document.getElementsByName('groupes')[0].value.split(',');
    for(i=0;i<ugroupes.length;i++)
    if(ugroupes[i]==name) return;
    ugroupes.push(name);

    document.getElementsByName('groupes')[0].value+=','+name;
    if(document.getElementsByName('groupes')[0].value.substr(0,1)==',') document.getElementsByName('groupes')[0].value=document.getElementsByName('groupes')[0].value.substr(1);

    html='';
    for(i=0;i<ugroupes.length;i++){
      if(ugroupes[i]!='') html+='<p>'+ugroupes[i]+'<a href="#" onclick="rmgroup(\''+ugroupes[i]+'\');\"><img src="images/moins.gif" align="absmiddle" title="Supprimer"></a></p>';
    }
   
    document.getElementById('ugroupes').innerHTML=html;
}
function rmgroup(name){
    var ugroupes=document.getElementsByName('groupes')[0].value.split(',');
    var html='';
    var csvlist='';
    for(i=0;i<ugroupes.length;i++){
    if(ugroupes[i]!='' && ugroupes[i]!=name){
      html+='<p>'+ugroupes[i]+'<a href="#" onclick="rmgroup(\''+ugroupes[i]+'\');"><img src="images/moins.gif" align="absmiddle" title="Supprimer"></p>';
      csvlist+=','+ugroupes[i];
    }
    }
    if(csvlist.substr(0,1)==',') csvlist=csvlist.substr(1);
    document.getElementsByName('groupes')[0].value=csvlist;
    document.getElementById('ugroupes').innerHTML=html;
}
function changetype(type){

    if(type=='facture')
      document.getElementById('trcodea').style.display='table-row';
    else
      document.getElementById('trcodea').style.display='none';
}

function showcontact(type){
    var displayall=(type==''?'table':'none');
    var div=document.getElementById('contacts');
    var tables=div.getElementsByTagName('table');
    for(i=0;i<tables.length;i++)
    tables[i].style.display=displayall;

    if(type!='') document.getElementById(type).style.display='table';

    var tabs=document.getElementById('tabs').getElementsByTagName('div');
    for(i=0;i<tabs.length;i++)
    tabs[i].className='tab';
    
    document.getElementById('tab_'+type).className='tabon';
}

function srcfournisseur_hide(){
    document.getElementById('srcfournisseur').style.display='none';
}
function srcfournisseur(txt){
    if(txt.length<3) return;
    document.getElementById('srcfournisseur').style.display='block';
    document.getElementById('srcfournisseur').innerHTML='<img src="/configurations/<?php echo $host ?>/images/templates/ajax_wait.gif">';
    ajaxrequest('mailbox_ajax.php?txt='+txt,srcfournisseurcb);
}
function srcfournisseurcb(httpRequest){
    var xml=httpRequest.responseXML.documentElement;
    var mailautolist=new Array();
    
    if(xml.getElementsByTagName('contact')[0]){
    var html='<table>';
    var table=document.createElement('table');
    for(i=0;i<xml.getElementsByTagName('contact').length;i++){
        var id=xml.getElementsByTagName('contact')[i].getAttribute('id');
        var mail=xml.getElementsByTagName('contact')[i].getAttribute('mail');
        var societe=xml.getElementsByTagName('contact')[i].getAttribute('societe');
        var nom=xml.getElementsByTagName('contact')[i].getAttribute('nom');
        var fonction=xml.getElementsByTagName('contact')[i].getAttribute('fonction') || '';
        var type=xml.getElementsByTagName('contact')[i].getAttribute('type');
        var telephone=xml.getElementsByTagName('contact')[i].getAttribute('telephone');
        var portable=xml.getElementsByTagName('contact')[i].getAttribute('portable');
        var tel=xml.getElementsByTagName('contact')[i].getAttribute('tel');
        
        var suivitype='suivi_fournisseur';
        var suiviid="<?=$fournisseuras; ?>";

        var tr=document.createElement('tr');
        var td1=document.createElement('td');
        var icimg=document.createElement('img');
        icimg.src=icon(type);
        icimg.style.width="26px";
        td1.appendChild(icimg);
        tr.appendChild(td1);
        var td2=document.createElement('td');
        td2.innerHTML=societe+'<br>'+fonction+'<br><span class="nom">'+nom+'</span>';
        tr.appendChild(td2);
        var td3=document.createElement('td');
        var telspan=document.createElement('span');
        telspan.className='suivilink';
        telspan.setAttribute('onclick',"suiviauto(\"tel_out\",\""+type+"\","+id+",\"Appel téléphone "+addslashes(societe)+" ("+addslashes(nom)+")\","+(tel==1?"\""+telephone+"\"":undefined)+");");
        telspan.innerHTML=telephone;
        if(portable!='') telspan.innerHTML+='<br>'+portable;
        td3.appendChild(telspan);
        var br=document.createElement('br');
        td3.appendChild(br);
        var telspan=document.createElement('span');
        telspan.className="suivilink";
        telspan.setAttribute('onclick',"suiviauto(\"tel_in\",\""+type+"\","+id+",\"Appel reçu de "+addslashes(societe)+" ("+addslashes(nom)+")\")");
        telspan.innerHTML="Appel reçu";
        td3.appendChild(telspan);
        var nbsp=document.createTextNode("\u00A0");
        td3.appendChild(nbsp);
        var mailspan=document.createElement('span');
        mailspan.className="suivilink";
        mailspan.setAttribute('onclick',"suiviauto(\"mail_in\",\""+type+"\","+id+",\"Email reçu de "+addslashes(societe)+" ("+addslashes(nom)+")\");");
        mailspan.innerHTML="Email reçu";
        td3.appendChild(mailspan);
        var br=document.createElement('br');
        td3.appendChild(br);
        var mailspan=document.createElement('span');
        mailspan.className="suivilink";
        mailspan.title=mail;
        mailspan.setAttribute('onclick',"mail_auto(undefined,\""+mail+"\");mail_suivi(\""+suivitype+"\",\""+suiviid+"\");srcfournisseur_hide();suivi_hide();");
        mailspan.innerHTML=mail;
        td3.appendChild(mailspan);
        tr.appendChild(td3);
        table.appendChild(tr);
        
        /*html+='<tr><td><img src="'+icon(type)+'" width="26"></td>';
        html+='<td>'+societe+'<br>'+fonction+'<br><span class="nom">'+nom+'</span></td>';
        html+='<td><span class="suivilink" onclick="suiviauto(\'tel_out\',\''+type+'\','+id+',\'Appel téléphone '+addslashes(societe)+' ('+addslashes(nom)+')\','+(tel==1?'\''+telephone+'\'':undefined)+');">'+telephone;
        if(portable!='') html+='<br>'+portable;
        html+='</span><br><span class="suivilink" onclick="suiviauto(\'tel_in\',\''+type+'\','+id+',\'Appel reçu de '+addslashes(societe)+' ('+addslashes(nom)+')\');">Appel reçu</span> <span class="suivilink" onclick="suiviauto(\'mail_in\',\''+type+'\','+id+',\'Email reçu de '+addslashes(societe)+' ('+addslashes(nom)+')\');">Email reçu</span>';
        html+='<br><span class="suivilink" title="'+mail+'" onclick="mail_auto(undefined,\''+mail+'\');mail_suivi(\''+suivitype+'\',\''+suiviid+'\');srcfournisseur_hide();suivi_hide();">'+mail+'</span></td></tr>';*/

    }
  //    html+='</table>';
    document.getElementById('srcfournisseur').innerHTML="";
    document.getElementById('srcfournisseur').appendChild(table);
      }
      
  }

  function suiviauto(moyen,type,destinataire,txt,tel){
      document.getElementsByName('moyen')[0].value=moyen;
      document.getElementsByName('type')[0].value=type;
      document.getElementsByName('destinataire')[0].value=destinataire;
      document.getElementById('suivititle').innerHTML=txt;
      if(moyen=="mail_out"){
        document.getElementById('suividiv').style.display='none';
        maildivdisplay='block';
      }else{
        document.getElementById('suividiv').style.display='block';
        maildivdisplay='none';
      }
      if(document.getElementById('suivimaildiv')) document.getElementById('suivimaildiv').style.display=maildivdisplay;
      document.getElementById('srcfournisseur').style.display='none';
      if(tel!=undefined) document.getElementById('suivititle').innerHTML+="&nbsp; <span class=\"epigy\" onclick=\"epigy_call('"+tel+"');\"><img src=\"images/ring_phone_2.gif\">"+tel+"</span>";
      switch(type){
        case "":
        document.getElementsByName('rappeltype')[0].checked=true;
        break;
        case "pro":
        document.getElementsByName('rappeltype')[1].checked=true;
        break;
      }
  }

  function planningcb(){
      document.getElementsByName('jour')[0].value=cal_jour;
      document.getElementsByName('pour')[0].value=document.getElementById('user').value;
  }

  function addslashes(txt) {
      return (txt + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
  }

  function edit_conditions(name) {
      document.getElementById('view_'+name).style.display="none";
      document.getElementById('edit_'+name).style.display="block";
  }

    function setNewSociety(){
        if(document.getElementsByName('societe')[0].style.visibility != 'visible'){
            document.getElementsByName('societe')[0].style.visibility = 'visible';
            document.getElementById('new_societe').value = 1;
            document.getElementById('libelle_lien').innerHTML = 'annuler';
        } else {
            document.getElementsByName('societe')[0].style.visibility = 'hidden';
            document.getElementById('new_societe').value = 0;
            document.getElementById('libelle_lien').innerHTML = 'nouveau';
        }
    }
    
     // efface les articles de la liste
    function cleanSuivi(id, suiviid, mailid){
        res = confirm("Effacer ce suivi ? ");
        if(res == true){
            ajaxrequest('ajax/delSuivi.php?fournid='+id+'&suiviid='+suiviid+'&mailid='+mailid,enditCallback);
        }
    }
    
    // retour a la page utilisateur
    function enditCallback(httpRequest){
        var html = httpRequest.responseText;
        var form = document.createElement('form');
        form.setAttribute('method',"POST");
        form.setAttribute('action',"fournisseur_liste.php");
      
        var inpt = document.createElement('input');
        inpt.setAttribute('type',"text");
        inpt.setAttribute('name',"txtcb");
        inpt.setAttribute("value", html);
      
        form.appendChild(inpt);
      
        form.submit();
      
        window.location.href = "fournisseur_liste.php";
    }
    
    function dlpj(name){
        var form=document.createElement("form");
        form.method="post";
        form.action="/admin/includes/download.php";
        var inp=document.createElement("input");
        inp.type="hidden";
        inp.name="page";
        inp.value="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/admin/mail/attachments/"+name;
        form.appendChild(inp);
        document.body.appendChild(form);
        form.submit();
    }
    
    function shownewcode(){
        var options=document.querySelectorAll("#newtypecode option");
        for(x in options){
            if(options.hasOwnProperty(x)){
                options[x].style.display="";
                options[x].selected=false;
            }
        }
        
        var codes=document.querySelectorAll("input[name*=codecomptable]");
        for(x in codes){
            if(codes.hasOwnProperty(x)){
                var typecode=codes[x].name.match(/codecomptable\[(.+)\]/);
                document.querySelector("#newtypecode option[value="+typecode[1]+"]").style.display='none';
            }
        }
        document.getElementById("addcode").style.display="block";
    }
    
    function addnewcode(){
        var table=document.getElementById("tblistecodes");
        var tr=document.createElement("tr");
        
        var opt=document.querySelector("#newtypecode option:checked");
        var td1=document.createElement("td");
        td1.innerHTML=opt.innerHTML;
        tr.appendChild(td1);
        
        var td2=document.createElement("td");
        td2.innerHTML=document.getElementById("newcode").value;
        tr.appendChild(td2);
        
        var td3=document.createElement("td");
        var input=document.createElement("input");
        input.type="hidden";
        input.name="codecomptable["+opt.value+"]";
        input.value=document.getElementById("newcode").value;
        td3.appendChild(input);
        var del=document.createElement("img");
        del.src="images/supprimer.gif";
        del.setAttribute("onclick","delcode('"+opt.value+"')");
        td3.appendChild(del);
        tr.appendChild(td3);
        
        table.insertBefore(tr,table.lastElementChild);
        document.getElementById("addcode").style.display="none";
    }
    
    function delcode(type){
        var input=document.querySelector("input[name='codecomptable["+type+"]']");
        var tr=input.closest("tr");
        console.debug(tr);
        document.querySelector("#tblistecodes tbody").removeChild(tr);
    }
    
    function verifemail(input){
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        input.style.color=re.test(String(input.value).toLowerCase()) ? '' : 'red';
    }
    
    function verifcodefourn(){
        <?=$codesfourn; ?>
        var err="";
        var curcode=document.getElementsByName("code401")[0].value.strip();
        var fournisseur=document.getElementsByName("fournisseur_libelle")[0].value.strip();
        if(curcode!=""){
            if(!curcode.match(/^\d{10}$/))
                err+="Le code fournisseur doit se composer de 10 chiffres\n";
            for(x in codesfourn){
                if(codesfourn[x]==curcode && x!=fournisseur){
                    err+="Ce code est utilisé pour "+x+"\n";
                }
            }
        }
        if(err!=""){
            if(!confirm(err+"Voulez-vous l'utiliser quand même ?")){
                document.getElementsByName("code401")[0].value="";
            }
        }
    }
    
</script>

<style>
    .flist { background-color:#cccccc;border: 1px solid white;}
    .flist td { padding:4px; }
    .detail { display:none; border-collapse:collapse; width:100%; margin-top:5px; }
    .detail td { border-style:solid; border-width:thin; border-color:#808080 #cccccc #808080 #cccccc; padding:4px; }
    .fournbox { border:solid thin #cccccc; width:250px; margin-top:10px; margin-left:10px; padding:5px; color:black; }
    .fournbox p { color:#505050; cursor:move; }
    .fournbox p:hover { background-color:#DDDDFF; }
    .tab { float:left; border:solid 1px #aaa; margin:4px; padding:4px; background-color:#DDD; cursor:pointer; }
    .tab:hover { margin-top:-3px; }
    .tabon { float:left; border:solid 1px #aaa; margin:4px; padding:4px; font-weight:bold; cursor:pointer; }
    #contacts { clear:both; width:100%; }
    .condition { display:none; }
    .conditions { width:100%; }
    .conditions td { border:solid 1px #AAA; width:50%; }
    #addcode { display:none; }
</style>

<h1>Fournisseurs</h1>

<?php echo $error; ?>

<?php include(BACK_PATH.'/includes/fournisseur_recherche.php'); ?>

<div style="margin: 20px 0; width: 100%; text-align: center;">
    <a href="fournisseurs_liste.php?action=marques" class="recherche_init" style="margin: 0 20px; float: none;">Attribuer les marques</a>
    <a href="fournisseurs_liste.php?action=suivi" class="recherche_init" style="margin: 0 20px; float: none;">Contacts pour le suivi</a>
    <a href="fournisseurs_liste.php?action=adv_h" class="recherche_init" style="margin: 0 20px; float: none;">Heures d'envoi des commandes</a>
</div>

<?php

$fourn_types = array(
    "produits principaux"           => "fourn",
    "produits étrangers"            => "fourn",
    "produits secondaires"          => "fourn",
    "transporteur"                  => "trspt",
    "installateur"                  => "sls",
    "garantie /sav"                 => "garantie",
    "partenaire"                    => "general",
    "communication/réseaux sociaux" => "general",
    "stockage"                      => "general",
    "service généraux"              => "general",
    "école"                         => "general",
    "market place"                  => "com"
);
ksort($fourn_types);

//********************************************//
// AFFICHAGE PAR DEFAUT => LISTE FOURNISSEURS // 
//********************************************//
if(!isset($_GET['action'])){
    
    $marque = new marque($DB_site2);
    
    //Détail fournisseur
    $sql = "SELECT f.societe";
    $sql .= " FROM fournisseur f";
    $sql .= " LEFT JOIN fournisseur_condition fc ON (fc.societe = f.societe)";
    $sql .= " WHERE 1";
    $sql .= isset($_GET['fournisseur_nom']) && $_GET['fournisseur_nom'] != '' ? " AND f.societe LIKE '%".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur_nom'])."%'" : "";
    $sql .= isset($_GET['fournisseur_type']) && $_GET['fournisseur_type'] != '' ? " AND fc.types LIKE '%".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur_type'])."%'" : "";
    if(isset($_GET['fournisseur_code401']) && $_GET['fournisseur_code401'] != 0){
        switch($_GET['fournisseur_code401']){
            case 1:
                $sql .= " AND (";
                $sql .= "       (SELECT COUNT(id) FROM fournisseur f2 WHERE f2.societe = f.societe AND f2.code != '') > 0";
                $sql .= "       OR";
                $sql .= "       (SELECT COUNT(*) FROM fournisseur_frais ff WHERE ff.fournisseur = f.societe AND ff.code401 != '') > 0";
                $sql .= " )";
                break;
            case 2:
                $sql .= " AND (SELECT COUNT(id) FROM fournisseur f2 WHERE f2.societe = f.societe AND f2.code != '') = 0";
                $sql .= " AND (SELECT COUNT(*) FROM fournisseur_frais ff WHERE ff.fournisseur = f.societe AND ff.code401 != '') = 0";
                break;
        }
    }
    if(isset($_GET['fournisseur_activite']) && $_GET['fournisseur_activite'] != 0){
        switch($_GET['fournisseur_activite']){
            case 1:
                $sql .= " AND fc.activite = '1'";
                break;
            case 2:
                $sql .= " AND (fc.activite = '0' OR fc.activite = '2')";
                break;
        } 
    }
    $sql .= " GROUP BY f.societe";
    $sql .= " ORDER BY fc.societe";
    $requete = $DB_site2->query($sql);
    
    $tr = array();
    while($f = $DB_site2->fetch_array($requete)){

        $comptescharge=array();
    
        $sql = "SELECT societe, types, devise, langue, compte_client, activite, pdts_vendus, tvaintra, fourn_admin, sf.societe_facturation_nom";
        $sql .= " FROM fournisseur_condition fc";
        $sql .= " LEFT JOIN societe_facturation sf ON (sf.societe_facturation_id = fc.societe_facturation_id)";
        $sql .= " WHERE societe = '".mysqli_real_escape_string($DB_site2->link_id,$f['societe'])."'";
        $conditions = $DB_site2->query_first($sql);
        
        $arr_ftypes = explode(",",$conditions['types']);

        $sql = "SELECT *";
        $sql .= " FROM fournisseur_frais";
        $sql .= " WHERE fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$f['societe'])."'";
        $fournisseur_frais = $DB_site2->query_first($sql);
        
        //Si le fournisseur est présent dans la table fournisseur frais, c'est un type "services généraux", et on récupère donc le code401 dans la table fournisseur_frais
        if($fournisseur_frais != ''){

            $code[0]       = $fournisseur_frais['code401'];
            $comptescharge[] = $fournisseur_frais['compte'];
            $codeAna = $fournisseur_frais['code_analytique'];
            $codeCEE = '';

        } else {

            $sql = "SELECT code";
            $sql .= " FROM fournisseur";
            $sql .= " WHERE societe = '".mysqli_real_escape_string($DB_site2->link_id,$f['societe'])."'";
            $sql .= " AND type = 'facture'";
            $sql .= " AND code != ''";
            $code = $DB_site2->query_first($sql);
            
            
            $sql = "SELECT p.*";
            $sql .= " FROM pays p";
            $sql .= " INNER JOIN fournisseur USING(paysid)";
            $sql .= " WHERE societe = '".mysqli_real_escape_string($DB_site2->link_id,$f['societe'])."'";
            $sql .= " AND type = 'facture'";
            $aPays = $DB_site2->query_first($sql);

            $codeCEE = '';
            if($aPays['tvaintracom'] == 0) {
                if($aPays['cee'] == 1){
                    $codeCEE = "0000"; // france
                } else {
                    $codeCEE = "0999"; // hors cee
                }
            } else {
                $codeCEE = "0099"; // cee sans tva
            }
            
            $sql="SELECT code FROM code_comptable WHERE fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$f['societe'])."'";
            $req=$DB_site2->query($sql);
            while($cc=$DB_site2->fetch_array($req)){
                $comptescharge[]=$cc['code'].$codeCEE;
            }

            //Récupération du code analytique
            //Ici, on considère que la commande a été passé sur ma-cave-a-vin.fr, depuis la France
            $sql = "SELECT *";
            $sql .= " FROM compte_analytique";
            $sql .= " WHERE site = 'base'";
            $sql .= " AND paysid = 57";
            $aCode = $DB_site2->query_first($sql);

            $codeAna = '';
        }
        
        $sql = "SELECT marques";
        $sql .= " FROM fournisseur";
        $sql .= " WHERE societe = '".mysqli_real_escape_string($DB_site2->link_id,$f['societe'])."'";
        $sql .= " AND type = 'fournadv'";
        $marques = $DB_site2->query_first($sql);
    
        //Récupération de la liste des marques
        $liste_nom_marque = '';
        if(!empty($marques['marques'])){
              
            $params = array(
                  'liste_ids' => $marques['marques']
              );
              
            $tabMarque = $marque->getListe($params);
              
              //Sécurité au cas ou la marque n'existe pas/plus (copie depuis MCAV par exemple)
              if(!empty($tabMarque)){

                  foreach($tabMarque as $la_marque){

                        if(empty($liste_nom_marque)) $liste_nom_marque .= '<ul style="margin:0 0 0 15px; padding:0;">';

                    $liste_nom_marque .= '<li>'.ucwords(strtolower($la_marque['libelle'])).'</li>';
                  }

                  $liste_nom_marque .= '</ul>';
              }
        }
    
        // TSI colorisation actif/inactif pour compte admin $conditions['fourn_admin']
        if ($conditions['fourn_admin']==1) $cptAdmin = " style=\"background-color: yellow;\"";
        else $cptAdmin="";
    
        $picto_activite = "";
        if(isset($conditions) && !empty($conditions)){
              //Association du picto "Activité"
              switch($conditions['activite']){
                  case 1:
                        $picto_activite = "<img src=\"images/icon/green.png\"".$cptAdmin.">";
                        break;
                  case 2:
                        $picto_activite = "<img src=\"images/icon/red.png\">";
                        break;
              }
        }
    
        if($conditions['types'] == ""){
            $types = array("inconnu"); 
        } else {
            $types = isset($_GET['fournisseur_type']) && $_GET['fournisseur_type'] != '' ? array($_GET['fournisseur_type']) : explode(",",$conditions['types']);
        }
            
        //Construction du tableau de liste des fournisseurs      
        foreach($types as $ftype){
            $tr[$ftype][] = ""
                ."<tr class=\"flist\">"
                ."<td>".$f['societe']."</td>"
                ."<td align=\"center\">".$picto_activite."</td>"
                ."<td>".(isset($conditions['pdts_vendus']) && !empty($conditions['pdts_vendus']) ? $conditions['pdts_vendus'] : '')."</td>"
                . "<td>".$liste_nom_marque."</td>"
                . "<td>".($conditions['langue']!=""?"<img src=\"images/drapeaux/br/".$conditions['langue'].".gif\">":"")."</td>"
                . "<td>".(isset($conditions['devise']) && !empty($conditions['devise']) ? $conditions['devise'] : '')."</td>"
                . "<td>".(isset($conditions['tvaintra']) && !empty($conditions['tvaintra']) ? $conditions['tvaintra'] : '')."</td>"
                . "<td>".(isset($conditions['compte_client']) && !empty($conditions['compte_client']) ? $conditions['compte_client'] : '')."</td>"
                . "<td align=\"center\">".$conditions['societe_facturation_nom']."</td>"
                . "<td>".$code[0]."</td>"
                . "<td>".implode("<br>",$comptescharge)."</td>"
                . "<td>".$codeAna."</td>"
                . "<td><a href=\"fournisseurs_liste.php?action=editer&fournisseur=".rawurlencode($f['societe'])."\"><img src=\"images/icon/edit.png\">";
                "</a></td>"
            . "</tr>";
            
        }
    }
    
    ksort($tr);
    
    /*print "<p>";

    foreach($tr as $ftype => $trlist) print "<a href=\"#".str_replace(" ","",$ftype)."\">".ucfirst($ftype)." (".count($trlist).")</a> ";
        
    print "</p>";*/
    
    print "<table align=\"center\">";
    
    $legende_actif = ""
          . "<br /><span style=\"font-size:9px;\"><img src=\"images/icon/green.png\" width=\"9\"> actif</span>"
          . "<br /><span style=\"font-size:9px;\"><img src=\"images/icon/red.png\" width=\"9\"> inactif</span>";
     
    foreach($tr as $ftype => $trlist){
        print ""
        . "<tr>"
             . "<td colspan=\"12\" align=\"center\" class=\"type_fournisseur\"><a name=\"".str_replace(" ","",$ftype)."\"></a><b>".ucfirst($ftype)."</b></td>"
        . "</tr>"
        . "<tr class=\"flist\">"
            . "<td align=\"center\"><strong>Société</strong></td>"
            . "<td align=\"center\"><strong>".$legende_actif."</strong></td>"
            . "<td align=\"center\"><strong>Activité</strong></td>"
            . "<td align=\"center\"><strong>Marques</strong></td>"
            . "<td align=\"center\"><strong>Langue</strong></td>"
            . "<td align=\"center\"><strong>Devise</strong></td>"
            . "<td align=\"center\"><strong>N° TVA intra</strong></td>"
            . "<td align=\"center\"><strong>N° client</strong></td>"
            . "<td align=\"center\"><strong>Société de facturation</strong></td>"
            . "<td align=\"center\"><strong>Code fournisseur</strong></td>"
            . "<td align=\"center\"><strong>Comptes de charge</strong></td>"
            . "<td align=\"center\"><strong>Code analytique</strong></td>"
            . "<td align=\"center\"><strong>Editer</strong></td>"
        . "</tr>"
        .implode("",$trlist)
        ."<tr>"
             . "<td>&nbsp;</td>"
        . "</tr>";
    }
    
    echo "</table>";

} else {
    
    switch($_GET['action']){

        case "editer":
            
            $sql = "SELECT *";
            $sql .= " FROM fournisseur_condition";
            $sql .= " WHERE societe = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
            $conditions = $DB_site2->query_first($sql);
            
            // TSI gestion de l'affichage suppression d'un suivi / mail pour SU et utilisateur_admin
           if(in_array($user_info['username'],$adminSU) && ($conditions['fourn_admin'] == 1 ))
               $authDelSuivi = 1;
           else
               $authDelSuivi = 0;

            $fournisseur_nom = $conditions['societe'];

            $tabSocFacturation = $class_fournisseur->getListeSocFacturation();
            $select_societe_facturation = "<select name=\"societe_facturation\">";
            $select_societe_facturation .= "<option value=\"0\">---</option>";
            foreach($tabSocFacturation as $la_societe){
              $selected_option = '';
              if($conditions['societe_facturation_id'] == $la_societe['societe_facturation_id']){
                $selected_option = 'selected';
              }
              $select_societe_facturation .= "<option value=\"".$la_societe['societe_facturation_id']."\" ".$selected_option.">".$la_societe['societe_facturation_nom']."</option>";
            }
            $select_societe_facturation .= "</select>";
            
            //Récupération du code 411
            $sql = "SELECT code as code411, ff.compte as compte, ff.code_analytique as analytique";
            $sql .= " FROM fournisseur f";
            $sql .= " LEFT JOIN fournisseur_frais ff ON (ff.fournisseur = f.societe)";
            $sql .= " WHERE f.societe = '".mysqli_real_escape_string($DB_site2->link_id,$fournisseur_nom)."'";
            $sql .= " AND f.code != ''";
            $sql .= " UNION ";
            $sql .= " SELECT code401 as code411, compte as compte, code_analytique as analytique";
            $sql .= " FROM fournisseur_frais";
            $sql .= " WHERE fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$fournisseur_nom)."'";
            
            //print $sql;
            $req = $DB_site2->query($sql);
            
            $code411 ="";
            $compte ="";
            $analytiq ="";
            
            if($DB_site2->num_rows($req)){
                 while($row = $DB_site2->fetch_array($req)){
                      $code411 = $row['code411'];
                      $compte = $row['compte'];
                      $analytiq = $row['analytique'];
                 }
            }

            foreach($devises->listcodes() as $devise){
                $optdevise .= "<option value=\"".$devise."\"".($devise==$conditions['devise']?" selected":"").">".$devise." ".$devises->symbole($devise)."</option>";
            }
                
            foreach($langues as $flangue){
                $optlangue .= "<option value=\"$flangue\"".($flangue==$conditions['langue']?" selected":"").">$flangue</option>";
            } 

            $arr_ftypes = explode(",",$conditions['types']);

            $nb_types = count($fourn_types);
            $nb = 0;
            foreach($fourn_types as $ftype => $fclass){
                if($opttype == '') $opttype = "<div style=\"float: left;\">";
                if($nb == $nb_types / 2) $opttype .= "</div><div style=\"float: left;\">";

                $onclick=$ftype=="transporteur" ? "onclick=\"document.getElementById('p_tracking').style.display=(this.checked?'block':'none');\"" : "";
                $opttype.="<input type=\"checkbox\" name=\"ftype[]\" $onclick value=\"$ftype\" ".(in_array($ftype,$arr_ftypes)?"checked":"")."> ".$ftype."<br>";

                $nb++;

                if($nb == $nb_types) $opttype .= "</div>";
            } 

            ?>
            <div id="fil_ariane"><a title="Liste des utilisateurs" href="fournisseurs_liste.php">Fournisseurs</a> &gt; <a class="active" title="<?php echo $fournisseur_nom; ?>" href="fournisseurs_liste.php?action=editer&amp;fournisseur=<?php echo $fournisseur_nom ;?>"><?php echo $fournisseur_nom; ?></a></div>
            <?php

            //include(BACK_PATH.'/includes/fournisseur_fiche.php');


            //Infos supplémentaires pour les fournisseurs de type "service généraux"
            //if(in_array("service généraux",$arr_ftypes) || in_array("communication/réseaux sociaux",$arr_ftypes) || in_array("stockage",$arr_ftypes) || in_array("école",$arr_ftypes)){
                $comptescharge=array();
                
                $sql = "SELECT *";
                $sql .= " FROM fournisseur_frais";
                $sql .= " WHERE fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
                $ff = $DB_site2->query_first($sql);

                $codes_a = array("",9,11);

                foreach($codes_a as $c) $optcode_a.="<option value=\"".$c."\"".($ff && $ff['code_analytique']==$c ? " selected" : "").">".$c."</option>";

                $formsg = ""
                . "<tr>"
                    . "<td colspan=\"2\">"
                        . "<fieldset style=\"font-weight: bold;\">"
                            . "<legend>Infos comptabilité</legend>"
                            . "<p style=\"padding: 0; margin: 5px 0;\">Code fournisseur : <input type=\"text\" name=\"code401\" value=\"".$code411."\" onblur=\"verifcodefourn()\"></p>";

                            if($ff!=''){
                                
                                $formsg .= ""
                                . "<p style=\"padding: 0; margin: 5px 0;\">Compte de charge : &nbsp;&nbsp;<input type=\"text\" name=\"compte\" value=\"".($ff != '' ? $ff['compte'] : '')."\"></p>"
                                . "<p style=\"padding: 0; margin: 5px 0;\">Code analytique : &nbsp;&nbsp;<select name=\"code_analytique\">".$optcode_a."</select></p>";

                                $comptescharge[]=$ff['compte'];
                            } else {

                                /*$codeComptable = '';
                                $codeCEE = '';
                                if(isset($arr_ftypes[0]) && isset($fourn_types[$arr_ftypes[0]])){

                                    //Récupération du code comptable
                                    $sql = "SELECT code";
                                    $sql .= " FROM code_comptable";
                                    $sql .= " WHERE type = '".$fourn_types[$arr_ftypes[0]]."'";
                                    if($fourn_types[$arr_ftypes[0]] == 'com') $sql .= " AND fournisseur = '".strtolower($conditions['societe'])."'";
                                    $codeComptable = $DB_site2->query_first($sql)['code'];

                                    

                                    //Récupération du code analytique
                                    //Ici, on considère que la commande a été passé sur ma-cave-a-vin.fr, depuis la France
                                    $sql = "SELECT *";
                                    $sql .= " FROM compte_analytique";
                                    $sql .= " WHERE site = 'base'";
                                    $sql .= " AND paysid = 57";
                                    $aCode = $DB_site2->query_first($sql);
                                }*/
                                
                                $sql = "SELECT p.*";
                                $sql .= " FROM pays p";
                                $sql .= " INNER JOIN fournisseur USING(paysid)";
                                $sql .= " WHERE societe = '".mysqli_real_escape_string($DB_site2->link_id,$fournisseur)."'";
                                $sql .= " AND type = 'facture'";
                                $aPays = $DB_site2->query_first($sql);

                                $codeCEE = '';
                                if($aPays['tvaintracom'] == 0) {
                                    if($aPays['cee'] == 1){
                                        $codeCEE = "0000"; // france
                                    } else {
                                        $codeCEE = "0999"; // hors cee
                                    }
                                } else {
                                    $codeCEE = "0099"; // cee sans tva
                                }
                            
                                $tblcodeComptable = '<table id="tblistecodes">';
                                $sql="SELECT code,type,info FROM code_comptable WHERE fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$fournisseur)."'";
                                $req=$DB_site2->query($sql);
                                while($codecomptable=$DB_site2->fetch_array($req)){
                                    $tblcodeComptable.="<tr>
                                        <td>".$codecomptable['info']."</td>
                                        <td>".$codecomptable['code'].$codeCEE."</td>
                                        <td><input type=\"hidden\" name=\"codecomptable[".$codecomptable['type']."]\" value=\"".$codecomptable['code']."\"><img src=\"images/supprimer.gif\" onclick=\"delcode('".$codecomptable['type']."')\"></td>
                                    </tr>";
                                    $comptescharge[]=$codecomptable['code'].$codeCEE;
                                }
                                $tblcodeComptable.="<tr>
                                <td colspan=\"3\"><a href=\"javascript:void(0)\" onclick=\"shownewcode()\">Ajouter un code</a></td>
                                </tr>
                                </table>";
                                
                                $formsg .= ""
                                . "<p style=\"padding: 0; margin: 5px 0;\">Comptes de charge : &nbsp;&nbsp;".$tblcodeComptable."</p>";
                                
                                $sql="SELECT type,info FROM code_comptable GROUP BY type ORDER BY info";
                                $req=$DB_site2->query($sql);
                                $optcode="";
                                while($code=$DB_site2->fetch_array($req)){
                                    $optcode.="<option value=\"".$code['type']."\">".$code['info']."</option>";
                                }
                                
                                $formsg.="<div id=\"addcode\">
                                    Ajouter :<br>
                                    <select id=\"newtypecode\">".$optcode."</select> : <input type=\"text\" id=\"newcode\">
                                    <input type=\"button\" value=\"Ajouter\" onclick=\"addnewcode()\">
                                </div>";
                            }

                        $formsg .= ""
                        . "</fieldset>"
                    . "</td>"
                . "</tr>";
            //}
            include(BACK_PATH.'/includes/fournisseur_fiche.php');
            ?>
            <div style="clear: both;"></div>
            <form name="form_infos" method="post" class="form_conditions_fourn" style="width: 100%;">
                <input type="hidden" name="fourn_infos" value="<?php echo $conditions['id']; ?>">
                <table>
                    <tr>
                        <td><strong>Actif :</strong></td>
                        <td style="width:400px;">
                            Oui <input type="radio" name="activite" value="1" <?php echo $conditions['activite'] == 1 ? 'checked' : ''; ?>>
                            Non <input type="radio" name="activite" value="2" <?php echo $conditions['activite'] == 2 ? 'checked' : ''; ?>>
                        </td>
                        <td>&nbsp;</td>
                        <td colspan="2">
                          <!-- TSI Ajout gestion des suivi type administrateur -->
                          <span style="font-weight: bold; color: #A71145;"><input type="checkbox" style="vertical-align: -2px;" name="fourn_admin" value="1" <?php echo $conditions['fourn_admin'] == 1 ? "checked" : ""; ?> >Compte administrateur</span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Libellé :</strong></td>
                        <td colspan="2"><input type="text" name="fournisseur_libelle" value="<?php echo $conditions['societe']; ?>" size="40"></td>
                        <td align="right"><strong>Activité :</strong></td>
                        <td colspan="2"><input type="text" name="pdts_vendus" value="<?php echo $conditions['pdts_vendus']; ?>" size="40"></td>
                    </tr>
                    <tr>
                        <td rowspan="10"><strong>Type :</strong></td>
                        <td rowspan="10">
                            <?php echo $opttype; ?>
                            <p id="p_tracking" style="clear:both;display:<?=$conditions['tracking']==""?"none":""; ?>;margin-top:20px;"><b>URL de tracking</b> (code = [?trackid]) : <input type="text" name="tracking" value="<?=$conditions['tracking']; ?>" style="width:100%"></p>
                            <p style="display: block; margin:20px 0 0; padding:0;">
                                <strong>Information</strong>
                                <br>
                                <textarea name="fournisseur_note" style="width: 100%;"><?php echo $conditions['fourn_note']; ?></textarea>
                                <br>
                                <span style="font-style: italic; font-size: 10px;">Cette information est affichée lors du passage de commande au fournisseur.</span>
                            </p>
                        </td>
                        <td rowspan="10" width="20"></td>
                        <td align="right"><strong>Devise :</strong></td>
                        <td><select name="devise"><?php echo $optdevise; ?></select></td>
                    </tr>
                    <tr>
                        <td align="right"><strong>Langue :</strong></td>
                        <td><select name="flangue"><?php echo $optlangue; ?></select></td>
                    </tr>
                    <tr>
                        <td align="right"><strong>Compte client :</strong></td>
                        <td><input type="text" name="compte_client" value="<?php echo $conditions['compte_client']; ?>">
                    </tr>
                    <tr>
                        <td align="right"><strong>Société de facturation :</strong></td>
                        <td><?php echo $select_societe_facturation; ?></td>
                    </tr>
                    <tr>
                        <td align="right"><strong>N° TVA intra :</strong></td>
                        <td><input type="text" name="tvaintra" value="<?php echo $conditions['tvaintra']; ?>">
                    </tr>
                    <!--<tr>
                        <td align="right"><strong>Code fournisseur :</strong></td>
                        <td><input type="text" name="code401" value="<?php echo $code411; ?>">
                    </tr>-->
                    <tr>
                        <td><strong>Accusé de réception adv</strong></td>
                        <td><input type="checkbox" name="ar" value="1" <?php echo $conditions['ar'] == 1 ? "checked" : ""; ?>></td>
                    </tr>
                    <?php echo $formsg; ?>
                    <tr>
                        <td colspan="6" align="center" style="padding-top: 15px;"><input type="submit" value="Enregistrer"></td>
                    </tr>
                </table>
            </form>
            <div style="clear: both;"></div>

            <h3>Contacts</h3>
            <?php
    
    $marques=array();
    $tr=array();
    $code411="";
    $req2=$DB_site2->query("SELECT * FROM fournisseur f INNER JOIN pays USING (paysid) WHERE societe='".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."' ORDER BY f.type");
    while($p=$DB_site2->fetch_array($req2)){
        $marques=array_merge($marques,explode(",",$p['marques']));
        $tr[$p['type']].="<tr><td>".$p['fonction']."</td><td>".$p['nom']." ".$p['prenom']."</td><td>".$p['adresse1'].($p['addresse2']!=""?"<br>".$p['addresse2']:"")."<br>".$p['cp']." ".$p['ville']."<br>".$p['libelle']."</td><td><a href=\"javascript:mail_auto(undefined,'$p[mail]');\">".$p['mail']."</a><br>".str_replace(",","<br>",$p['groupes'])."</td><td>".$p['telephone']."<br>".$p['portable']."</td><td><a href=\"fournisseurs_liste.php?action=modif&id=".$p['id']."\"><img src=\"images/editer.gif\" title=\"Modifier\"></a> <a href=\"#\" onclick=\"if(confirm('Etes vous sur de vouloir effacer ce fournisseur ?')) document.location='fournisseurs_liste.php?action=del&id=".$p['id']."';\"><img src=\"images/supprimer.gif\" title=\"Supprimer\"></a></td></tr>";
        if($p['code']!="") $code411=$p['code'];
    }

    print "<div id=\"tabs\">
      <div id=\"tab_\" class=\"tab\" onclick=\"showcontact('')\">Tout</div>";
    foreach($tr as $type=>$lines) print "<div id=\"tab_$type\" class=\"tab\" onclick=\"showcontact('$type')\">".$types[$type]."</div>";
    print "</div>";

    print "<div id=\"contacts\">";
    foreach($tr as $type=>$lines)
      print "<table id=\"$type\" class=\"detail\">
      <tr><td colspan=\"5\">".$types[$type].($type=="facture"?" ".$code411:"")."</td></tr>
      <tr><td>Fonction</td><td>Nom</td><td>Adresse</td><td>Email</td><td>téléphone</td></tr>".
      $lines.
      "</table>";
    print "</div>";
    
    print "<h3>Conditions</h3>";
    print "<form method=\"post\">
    <input type=\"hidden\" name=\"conditions\" value=\"".intval($conditions['id'])."\">
    <table class=\"conditions\">
    <tr>
      <td>Tarifs : <img src=\"images/editer.gif\" onclick=\"edit_conditions('tarif')\">
        <div id=\"view_tarif\">
          ".$conditions['tarif']."
        </div>
        <div id=\"edit_tarif\" class=\"condition\">
          <textarea name=\"tarif\" class=\"ckeditor\">".$conditions['tarif']."</textarea>
          <input type=\"submit\" value=\"Enregistrer\">
        </div>
      </td>
      <td>Passage de commande / Demande dispo : <img src=\"images/editer.gif\" onclick=\"edit_conditions('commande')\">
        <div id=\"view_commande\">
          ".$conditions['commande']."
        </div>
        <div id=\"edit_commande\" class=\"condition\">
          <textarea name=\"commande\" class=\"ckeditor\">".$conditions['commande']."</textarea>
          <input type=\"submit\" value=\"Enregistrer\">
        </div>
      </td>
    </tr>
    <tr>
      <td>Livraison : <img src=\"images/editer.gif\" onclick=\"edit_conditions('livraison')\">
        <div id=\"view_livraison\">
          ".$conditions['livraison']."
        </div>
        <div id=\"edit_livraison\" class=\"condition\">
          <textarea name=\"livraison\" class=\"ckeditor\">".$conditions['livraison']."</textarea>
          <input type=\"submit\" value=\"Enregistrer\">
        </div>
      </td>
      <td>SAV : <img src=\"images/editer.gif\" onclick=\"edit_conditions('sav')\">
        <div id=\"view_sav\">
          ".$conditions['sav']."
        </div>
        <div id=\"edit_sav\" class=\"condition\">
          <textarea name=\"sav\" class=\"ckeditor\">".$conditions['sav']."</textarea>
          <input type=\"submit\" value=\"Enregistrer\">
        </div>
      </td>
    </tr>
    <tr>
      <td>Divers : <img src=\"images/editer.gif\" onclick=\"edit_conditions('divers')\">
        <div id=\"view_divers\">
          ".$conditions['divers']."
        </div>
        <div id=\"edit_divers\" class=\"condition\">
          <textarea name=\"divers\" class=\"ckeditor\">".$conditions['divers']."</textarea>
          <input type=\"submit\" value=\"Enregistrer\">
        </div>
      </td>
      <td>Export :<img src=\"images/editer.gif\" onclick=\"edit_conditions('export')\">
        <div id=\"view_export\">
          ".$conditions['export']."
        </div>
        <div id=\"edit_export\" class=\"condition\">
          <textarea name=\"export\" class=\"ckeditor\">".$conditions['export']."</textarea>
          <input type=\"submit\" value=\"Enregistrer\">
        </div>
      </td>
    </tr>
    <tr>
      <td>Codes :<img src=\"images/editer.gif\" onclick=\"edit_conditions('codes')\">
        <div id=\"view_codes\">
          ".$conditions['codes']."
        </div>
        <div id=\"edit_codes\" class=\"condition\">
          <textarea name=\"codes\" class=\"ckeditor\">".$conditions['codes']."</textarea>
          <input type=\"submit\" value=\"Enregistrer\">
        </div>
      </td>
    </tr>
    </table>
    </form>";
    
    $marques=array_unique($marques);
    print "<h3>Marques</h3>";
    foreach($marques as $marqueid) if($marqueid!="") print "<img src=\"../configurations/".$host."/images/marques/br/$marqueid.jpg\" width=\"100\"> ";

    $count=$DB_site2->query_first("SELECT COUNT(*) FROM fournisseur_files WHERE fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."' AND ext<>''"); 
    print "<h3>Fichiers</h3>
    <div id=\"fbcontainer\"></div>";

    print "<script language=\"javascript\">
    var fb=new filebox('fb',document.getElementById('fbcontainer'));
    fb.show('fournisseur_files.php?fournisseur=".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."&parent=0');
    </script>";

	
function tableau_relation($genre=1, $sens=1){
	global $DB_site2;
	$return="<div style=\"padding:5px;\">";
	if($sens==1){
		$autresens=2;
	} else {
		$autresens=1;
	}
	$sql="SELECT * FROM utilisateur_relation
			WHERE relation_genre='".$genre."'
			AND fournisseur_societe='".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'
			AND user".$autresens."id<>0 ";
	$contacts = $DB_site2->query($sql);
	while($contact=$DB_site2->fetch_array($contacts)){
		$userid=$contact['user'.$autresens.'id'];
		$sql_contact="SELECT userid, raisonsociale, prenom, nom FROM utilisateur WHERE userid='".$userid."' ";
		$coordonnees=$DB_site2->query_first($sql_contact);
		if($coordonnees[userid]==$userid){
			$libelle="";
			if($coordonnees[raisonsociale]!=""){
				$libelle=$coordonnees[raisonsociale]." - ";
			}
			$libelle.=$coordonnees[prenom]." ".$coordonnees[nom];
			$lien="utilisateur.php?action=editer&userid=".$userid;
			$type="client";
		} else {
			$libelle="<strong>erreur : client introuvable</strong>";
		}
		$return.="<a href=\"".$lien."\" target=\"_blank\">".$libelle."</a><br>".$contact[relation_description];
	}
	return $return;
}
	
	print "<h3>Relations</h3>	
			<table class=\"table_factures\">
				<tr>
					<td class=\"td_alt1\">
						<b>Ce fournisseur a été envoyé par :</b><br>
						".tableau_relation(1, 1)."
					</td>
					<td class=\"td_alt2\">
						<b>Ce fournisseur nous a envoyé :</b><br>
						".tableau_relation(1, 2)."
					</td>
					<td class=\"td_alt1\">
						<b>Ce fournisseur est l'architecte de :</b><br>
						".tableau_relation(2, 1)."
					</td>
					<td class=\"td_alt2\">
						<b>Ce fournisseur a pour architecte :</b><br>
						".tableau_relation(2, 2)."
					</td>
				</tr>
			</table>";

			

	
	
	
    $agesuivi=6*30*86400;
    
    $suiviicon=array("tel_out"=>"<img src=\"images/tel_out.png\"><br>","tel_in"=>"<img src=\"images/tel_in.png\"><br>","mail_in"=>"<img src=\"images/get_mail.png\"><br>");
    $requete=$DB_site2->query("SELECT * FROM suivi_fournisseur WHERE date>".(time()-$agesuivi)." AND fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."' ORDER BY date DESC");

    while($suivi=$DB_site2->fetch_array($requete)){
            $classtype="int";
            $tddest="";
            if($suivi['type']!=""){
                    $classtype=$suivi['type'];
                    switch($suivi['type']){
                        case "pro":
                        $contact=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id=".$suivi['destinataire']);
                        break;
                        case "client":
                        $contact=$DB_site2->query_first("SELECT *,userid AS id FROM utilisateur WHERE userid=".$suivi['destinataire']);
                        break;
                    }
                    
                    $tddest="<div class=\"addr\">
                    <div>".$contact['societe']." ".$contact['prenom']." ".$contact['nom']."
                    <span class=\"suivilink\" onclick=\"suiviauto('mail_out','".$suivi['type']."',".$suivi['destinataire'].");mail_suivi('suivi_client',".$suivi['destinataire'].");\">".$contact['mail']."</span>
                    <span class=\"suivilink\" onclick=\"suiviauto('tel_out','".$suivi['type']."',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].")',".($usertel?"'$contact[telephone]'":"undefined").");\">".epigy($contact['telephone'])."</span>";
                    if($contact['portable']!="") $tddest.="<span class=\"suivilink\" onclick=\"suiviauto('tel_out','".$suivi['type']."',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].",".($usertel?"'$contact[portable]'":"undefined").")');\">".epigy($contact['portable'])."</span>";
                    $tddest.="</div>
                    </div>";
            }
            
            $suivi_assoc=
            "<input type=\"button\" name=\"transfert_suivi\" style=\"background-color: #AA1147; border: medium none; border-radius: 10px; color: white; font-size: 10px; font-weight: bold; padding: 1px 10px 3px; cursor: pointer; margin: 5px 0;\" value=\"Lier à une commande\" onclick=\"document.getElementById('suivi_recherche_cmd_".$suivi['id']."').style.display='block';\">
            <div style=\"position: relative;\">
                <input type=\"text\" id=\"suivi_recherche_cmd_".$suivi['id']."\" style=\"margin: 5px auto; width: 100px; display:none;\" onkeyup=\"checkListFacture(this.value, ".$suivi['id'].", 'fournisseur');\">
                <div id=\"type_suivi_resultats_".$suivi['id']."\" class=\"type_suivi_resultats\"></div>
            </div>
            <input type=\"button\" name=\"transfert_suivi\" style=\"background-color: #AA1147; border: medium none; border-radius: 10px; color: white; font-size: 10px; font-weight: bold; padding: 1px 5px 3px; cursor: pointer; margin: 5px 0;\" value=\"Lier à un client\" onclick=\"document.getElementById('suivi_recherche_".$suivi['id']."').style.display = 'block';\">
            <input type=\"texte\" id=\"suivi_recherche_".$suivi['id']."\" name=\"transfert_suivi_recherche\" style=\"margin: 5px auto; width: 100px; display: none;\" onkeyup=\"checkListClients(this.value, '1', ".$suivi['id'].", 'fournisseur');\">
            <div style=\"position: relative;\">
                <div id=\"type_suivi_clients_".$suivi['id']."\" class=\"type_suivi_resultats\"></div>
            </div>";

            $suivitd[$suivi['date']]="<tr class=\"suivi$classtype\">
            <td>".$suiviicon[$suivi['moyen']]."<br>".$suivi['commercial']."<br>".date("d/m/Y H:i",$suivi['date'])."</td>
            <td>".$tddest.formatsuivi($suivi['suivi']).$causeperte."</td>
            <td>".($suivi['rappel']!=0?rappel_heure($suivi['rappel'],$suivi['jour'])." par ".$suivi['pour']:"").($suivi['rappeltype']!=""?sprintf(" (%s)",rappeltype_txt($suivi['rappeltype'])):"")."</td>
            <td></td>
            <td>".$suivi_assoc."</td>";
            // TSI pour modification suivi compte_admin
            if ($authDelSuivi==1)  
            $suivitd[$suivi['date']] .= "<td align=\"center\"><a href=\"\"><img src=\"images/supprimer.gif\" onclick=\"cleanSuivi('".$_GET['fournisseur']."',".$suivi['id'].",'')\" width=\"20\"></a></td>";
            $suivitd[$suivi['date']] .= "</tr>";
    }
    
    $requete=$DB_site2->query("SELECT * FROM mail_outbox WHERE date>".(time()-$agesuivi)." AND suivitype='suivi_fournisseur' AND fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'");
    while($mail=$DB_site2->fetch_array($requete)){
        $td=array();
        foreach(array('from','to') as $mailtype){
            $mails=preg_split("[,|;]",$mail[$mailtype]);
            $td[$mailtype]="";
            $virg="";
            foreach($mails as $mailto){
                $contact=$DB_site2->query_first("SELECT * FROM fournisseur WHERE mail<>'' AND mail='".addslashes($mailto)."'");
                if($mailto==$contact['mail']){
                    $td[$mailtype].=$virg."<div>".$contact['societe']." ".$contact['prenom']." ".$contact['nom']."
                    <span onclick=\"mail_auto(undefined,'".$contact['mail']."');mail_suivi('suivi_commande','".$mail['suiviid']."')\">".$contact['mail']."</span>
                    <span onclick=\"suiviauto('tel_out','pro',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].",".($usertel?"'$contact[telephone]'":"undefined").")');self.location.hash='#suivitop';\">".$contact['telephone']."</span>";
                    if($contact['portable']!="") $td[$mailtype].="<br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','pro',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].")',".($usertel?"'$contact[portable]'":"undefined").");self.location.hash='#suivitop';\">".$contact['portable']."</span>";
                        $td[$mailtype].="</div>";
                }else{
                    $td[$mailtype].=$virg."<div class=\"suividest\">".$mailto."</div>";
                }
                $virg=", ";
            }
        }
            
        $pjlist="";
        $sql = "SELECT * FROM mail_attachment WHERE mailid = ".$mail['id'];
        $reqpj = $DB_site2->query($sql);
        $i = 0;
        if($DB_site2->num_rows($reqpj) > 0){
            while($pj = $DB_site2->fetch_array($reqpj)){
                $valeur = ROOT_PATH."configurations/".$host."/mails/attachments/".$pj['id'].".".$pj['ext'];
                if (file_exists($valeur)){
                        $pjlist .= "<form method=\"post\" style=\"margin: 0px;\" align=\"center\" type=\"button\" action=\"/admin/includes/download.php\">
                        <input type=\"submit\" value=\" \" onmouseover=\"document.getElementById('special".$pj['id']."').style.display='block';\" onmouseout=\"document.getElementById('special".$pj['id']."').style.display='none';\" class=\"dlbutton\"/>
                        <input type=\"hidden\" name=\"page\" value=\"".$valeur."\"/>
                    </form> <div id=\"special".$pj['id']."\" class=\"spehover\">".$pj['name']."</div>";
                }
                
            }
        }
        
        $suivi_assoc=
        "<input type=\"button\" name=\"transfert_suivi\" style=\"background-color: #AA1147; border: medium none; border-radius: 10px; color: white; font-size: 10px; font-weight: bold; padding: 1px 10px 3px; cursor: pointer; margin: 5px 0;\" value=\"Lier à une commande\" onclick=\"document.getElementById('suivi_recherche_cmd_".$mail['id']."').style.display='block';\">
        <div style=\"position: relative;\">
            <input type=\"text\" id=\"suivi_recherche_cmd_".$mail['id']."\" style=\"margin: 5px auto; width: 100px; display:none;\" onkeyup=\"checkListFacture(this.value, ".$mail['id'].", 'mail_outbox');\">
            <div id=\"type_suivi_resultats_".$mail['id']."\" class=\"type_suivi_resultats\"></div>
        </div>
        <input type=\"button\" name=\"transfert_suivi\" style=\"background-color: #AA1147; border: medium none; border-radius: 10px; color: white; font-size: 10px; font-weight: bold; padding: 1px 5px 3px; cursor: pointer; margin: 5px 0;\" value=\"Lier à un client\" onclick=\"document.getElementById('suivi_recherche_".$mail['id']."').style.display = 'block';\">
        <input type=\"texte\" id=\"suivi_recherche_".$mail['id']."\" name=\"transfert_suivi_recherche\" style=\"margin: 5px auto; width: 100px; display: none;\" onkeyup=\"checkListClients(this.value, '1', ".$mail['id'].", 'mail_outbox');\">
        <div style=\"position: relative;\">
            <div id=\"type_suivi_clients_".$mail['id']."\" class=\"type_suivi_resultats\"></div>
        </div>";
            
        $suivitd[$mail['date']]="<tr class=\"suivipro\">
            <td><img src=\"images/send_mail.png\"><br>".$mail['utilisateur']."<br>".date("d/m/Y H:i",$mail['date'])."</td>
            <td class=\"suivitxt\">
                <div class=\"addr\">Pour : ".$td['to']."</div>
                <div class=\"subject\">Sujet : <b>".$mail['subject']."</b></div>"
                .formatsuivi($mail['body']).($mail['pj']!=""?"<br>PJ : ".$mail['pj']:"")
            ."</td>
            <td></td>
            <td>".$pjlist."</td>
            <td>".$suivi_assoc."</td>";
            
            if ($authDelSuivi==1)  
                $suivitd[$mail['date']] .= "<td></td>";
            $suivitd[$mail['date']] .="</tr>";
    }

    $td=array();
    $requete=$DB_site2->query("SELECT * FROM mail_inbox WHERE date>".(time()-$agesuivi)." AND fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'");
    while($mail=$DB_site2->fetch_array($requete)){
        foreach(array('from','to') as $mailtype){
            $mails=preg_split("[,|;]",$mail[$mailtype]);
            $td[$mailtype]="";
            $virg="";
            foreach($mails as $mailto){
                $contact=$DB_site2->query_first("SELECT * FROM fournisseur WHERE mail<>'' AND mail='".addslashes($mailto)."'");
                if($mailto==$contact['mail']){
                    $td[$mailtype].=$virg."<div>"
                    .$contact['societe']." ".$contact['prenom']." ".$contact['nom']."
                    <span onclick=\"mail_auto(undefined,'".$contact['mail']."');mail_suivi('suivi_commande','".$mail['suiviid']."')\">".$contact['mail']."</span>
                    <span onclick=\"suiviauto('tel_out','pro',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].",".($usertel?"'$contact[telephone]'":"undefined").")');self.location.hash='#suivitop';\">".$contact['telephone']."</span>";
                    if($contact['portable']!="") $tddest.="<span onclick=\"suiviauto('tel_out','pro',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].")',".($usertel?"'$contact[portable]'":"undefined").");self.location.hash='#suivitop';\">".$contact['portable']."</span>";
                    $td[$mailtype].="</div>";
                }else{
                    $td[$mailtype].=$virg."<div>".$mailto."</div>";
                }
                $virg=", ";
            }
        }
            
        $pjlist="";
        $sql = "SELECT * FROM mail_attachment WHERE mailid = ".$mail['id'];
        $reqpj = $DB_site2->query($sql);
        $i = 0;
        if($DB_site2->num_rows($reqpj) > 0){
            while($pj = $DB_site2->fetch_array($reqpj)){
                $pjlist .= "<input type=\"button\" value=\" \" onclick=\"dlpj('".$pj['id'].".".$pj['ext']."')\" onmouseover=\"document.getElementById('special".$pj['id']."').style.display='block';\" onmouseout=\"document.getElementById('special".$pj['id']."').style.display='none'\" class=\"dlbutton\"/>
                <div id=\"special".$pj['id']."\" class=\"spehover\">".$pj['name']."</div>";
            }
        }
        
        $suivi_assoc=
        "<input type=\"button\" name=\"transfert_suivi\" style=\"background-color: #AA1147; border: medium none; border-radius: 10px; color: white; font-size: 10px; font-weight: bold; padding: 1px 10px 3px; cursor: pointer; margin: 5px 0;\" value=\"Lier à une commande\" onclick=\"document.getElementById('suivi_recherche_cmd_".$mail['id']."').style.display='block';\">
        <div style=\"position: relative;\">
            <input type=\"text\" id=\"suivi_recherche_cmd_".$mail['id']."\" style=\"margin: 5px auto; width: 100px; display:none;\" onkeyup=\"checkListFacture(this.value, ".$mail['id'].", 'mail_inbox');\">
            <div id=\"type_suivi_resultats_".$mail['id']."\" class=\"type_suivi_resultats\"></div>
        </div>
        <input type=\"button\" name=\"transfert_suivi\" style=\"background-color: #AA1147; border: medium none; border-radius: 10px; color: white; font-size: 10px; font-weight: bold; padding: 1px 5px 3px; cursor: pointer; margin: 5px 0;\" value=\"Lier à un client\" onclick=\"document.getElementById('suivi_recherche_".$mail['id']."').style.display = 'block';\">
        <input type=\"texte\" id=\"suivi_recherche_".$mail['id']."\" name=\"transfert_suivi_recherche\" style=\"margin: 5px auto; width: 100px; display: none;\" onkeyup=\"checkListClients(this.value, '1', ".$mail['id'].", 'mail_inbox');\">
        <div style=\"position: relative;\">
            <div id=\"type_suivi_clients_".$mail['id']."\" class=\"type_suivi_resultats\"></div>
        </div>";
        
        $suivitd[$mail['date']]="<tr class=\"suivipro\">
            <td><img src=\"images/get_mail.png\"><br>".date("d/m/Y H:i",$mail['date'])."<br>".$mail['utilisateur']."</td>
            <td class=\"suivitxt\">
                <div class=\"addr\">De : ".$td['from']."</div>
                <div class=\"addr\">Pour : ".$td['to']."</div>
                <div class=\"subject\">Sujet : <b>".$mail['subject']."</b></div>"
                .formatsuivi($mail['body']).($mail['pj']!=""?"<br>PJ : ".$mail['pj']:"")
            ."</td>
            <td></td>
            <td>".$pjlist."</td>
            <td>".$suivi_assoc."</td>";
        // TSI pour modification suivi compte_admin
        if ($authDelSuivi==1)  
            $suivitd[$mail['date']] .= "<td align=\"center\"><a href=\"\"><img src=\"images/supprimer.gif\" onclick=\"cleanSuivi('".$_GET['fournisseur']."','',".$mail['id'].")\" width=\"20\"></a></td>";
        $suivitd[$mail['date']] .="</tr>";
    }
    
    if(isset($suivitd)){
          krsort($suivitd);
          $tblsuivi="<table class=\"table_factures\">
            <thead style=\"color:black; text-align:center; font-weight:bold;\">
            <tr><td>Date</td><td>Suivi</td><td>Rappel</td><td>PJ</td>";
            if ($authDelSuivi==1)  
                $tblsuivi .= "<td></td>";
            $tblsuivi .="</tr></thead>"
            .implode("",$suivitd).
            "</table>";
    }
    
//TODO: create a TaskManager/TaskTemplate method
    print "<h3>Suivi interne</h3>";
    $si_query = $DB_site2->query('
      SELECT `type`, `id`, `name`, `short_desc`, `excepted_date`
      FROM `suivi_interne`
      WHERE `link_type` LIKE "fournisseur"
      AND `link` LIKE "'.mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur']).'"
      ');
    print '<table class="table_factures">';
    print '
      <thead>
        <tr>
          <th>ID</th><th>Nom</th><th>Résumé</th><th>Date de fin</th>
        </tr>
      </thead>';
    print '<tbody>';
    while ($si = $DB_site2->fetch_array($si_query))
      print '
        <tr class="suiviint">
          <td>'.($si['type']=='suivi'?'SI':'SB').'#'.$si['id'].'</td>
          <td>'.$si['name'].'</td>
          <td>
          <a href="task.php?a=showTask&amp;task=
          '.$si['id'].'&amp;'.$si['type'].'">
        '.$si['short_desc'].'</a>
          </td>
          <td>'.$si['excepted_date'].'</td>
        </tr>';
    print '</tbody>';
    print '</table>';
    
    print "<h3>Suivi</h3>

          <form name=\"suivi_client\" action=\"fournisseurs_liste.php?action=editer&fournisseur=".stripslashes($_GET['fournisseur'])."\" method=\"post\">
          <input type=\"hidden\" name=\"moyen\">
          <input type=\"hidden\" name=\"type\">
          <input type=\"hidden\" name=\"destinataire\">
          <table id=\"suiviaction\">
          <thead>
          <tr><td class=\"suivipro\">Fournisseur</td><td class=\"suiviint\">Interne</td></tr>
          </thead>
          <tr>
              <td class=\"suivipro\"><input type=\"text\" onkeyup=\"srcfournisseur(this.value);\"><div id=\"srcfournisseur\"></div></td>
              <td class=\"suiviint\"><span class=\"suivilink\" onclick=\"suiviauto('','',0,'Suivi interne')\">Message interne</span></td>
          </tr>
          </table>
          <div id=\"suividiv\" style=\"display:none;\">
            <div style=\"float:left;\">
            <div id=\"suivititle\"></div>
            <textarea name=\"suivi\" id=\"suivi\" cols=\"60\" rows=\"8\"></textarea>
            <br>Date de rappel : <input type=\"text\" name=\"rappel\" id=\"rappel\" onclick=\"calendrier_show(this.id,this.value,'planning',planningcb);\" readonly> pour <input type=\"text\" name=\"pour\" size=\"6\" readonly> <input type=\"hidden\" name=\"box\" value=\"1\">
            <br>Rappel <span class=\"suiviint\"><input type=\"radio\" name=\"rappeltype\" value=\"int\" checked> interne</span>
              &nbsp; <span class=\"suivipro\"><input type=\"radio\" name=\"rappeltype\" value=\"pro\"> fournisseur</span>
            <input type=\"hidden\" name=\"jour\" value=\"0\">
            <p><input type=\"submit\" value=\"Ajouter le suivi\"></p>
            </div>
          </div>";
    echo $tblsuivi;
            
    break;

    case "add":
    case "modif":
    $modif=0;
    if(isset($_GET['id'])){
        $mf=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id=".$_GET['id']);
        $modif=$mf['id'];
    }

    foreach($types as $t=>$name) $typesopt.="<option value=\"$t\"".($mf['type']==$t?" selected":"").">$name</option>";

    $optsoc.="<option></option>";
    $r=$DB_site2->query("SELECT societe FROM fournisseur GROUP BY societe ORDER BY societe");
    while($soc=$DB_site2->fetch_array($r)){
      $optsoc.="<option value=\"".$soc['societe']."\"".($soc['societe']==$mf['societe']?" selected":"").">".$soc['societe']."</option>";
    }
    $r=$DB_site2->query("SELECT paysid,libelle FROM pays ORDER BY libelle");
    while($pays=$DB_site2->fetch_array($r)){
      $optpays.="<option value=\"$pays[paysid]\"".($mf['paysid']==$pays['paysid']?" selected":"").">$pays[libelle]</option>";
    }

    $ugroupes="<div id=\"ugroupes\">";
    if($mf['groupes']!=""){
        $list=explode(",",$mf['groupes']);
        foreach($list as $groupe)
          $ugroupes.="<p>$groupe <a href=\"javascript:void(0)\" onclick=\"rmgroup('$groupe');\" align=\"absmiddle\"><img src=\"images/moins.gif\" align=\"absmiddle\" title=\"Supprimer\"></a></p>";
    }
    $ugroupes.="</div>";
    $fgr=array();
    $sql="SELECT groupes FROM fournisseur";
    if($mf['societe']!="") $sql.=" WHERE societe='".addslashes($mf['societe'])."'";
    $requete=$DB_site2->query($sql);
    while($g=$DB_site2->fetch_array($requete)){
        $glist=explode(",",$g['groupes']);
        if($glist!=false) $fgr=array_merge($fgr,$glist);
    }
    $fgr=array_unique($fgr);
    foreach($fgr as $g)
        if($g!="") $fgroupes.="<p><a href=\"javascript:void(0)\" onclick=\"addgroup('$g');\" align=\"absmiddle\"><img src=\"images/plus.gif\" title=\"Ajouter\"></a>$g</p>";
    $fgroupes.="<p><a href=\"javascript:void(0)\" onclick=\"addgroup(document.getElementById('newgroup').value);\" align=\"absmiddle\"><img src=\"images/plus.gif\" title=\"Ajouter\"></a><input type=\"text\" id=\"newgroup\" value=\"Nouveau\" onclick=\"this.value='';\"></p>";

    print "<form action=\"fournisseurs_liste.php?action=editer&fournisseur=".$mf['societe']."\" method=\"post\">
    <input type=\"hidden\" id=\"new_societe\" name=\"new_societe\" value=\"0\">
    <input type=\"hidden\" name=\"modif\" value=\"$modif\">
    <table>
    <tr><td>Type</td><td><select name=\"type\" onchange=\"changetype(this.value)\">$typesopt</select></td></tr>
    <tr><td>Société</td><td colspan=\"2\"><select name=\"societe_liste\" onchange=\"document.getElementsByName('societe')[0].value=this.value\">$optsoc</select> ou <a href=\"#\" id=\"libelle_lien\" onclick=\"setNewSociety();\">nouveau</a> <input type=\"text\" name=\"societe\" style=\"visibility:hidden;\" value=\"$mf[societe]\"></td></tr>
    <tr><td>Fonction</td><td><input type=\"text\" name=\"fonction\" value=\"$mf[fonction]\"></td></tr>
    <tr><td>Nom</td><td><input type=\"text\" name=\"nom\" value=\"$mf[nom]\"></td></tr>
    <tr><td>Prenom</td><td><input type=\"text\" name=\"prenom\" value=\"$mf[prenom]\"></td></tr>
    <tr><td>Adresse</td><td><input type=\"text\" name=\"adresse1\" value=\"$mf[adresse1]\"></td></tr>
    <tr><td>Adresse suite</td><td><input type=\"text\" name=\"adresse2\" value=\"$mf[adresse2]\"></td></tr>
    <tr><td>Code postal</td><td><input type=\"text\" name=\"cp\" value=\"$mf[cp]\"></td></tr>
    <tr><td>Ville</td><td><input type=\"text\" name=\"ville\" value=\"$mf[ville]\"></td></tr>
    <tr><td>Pays</td><td colspan=\"2\"><select name=\"paysid\">$optpays</select></td></tr>
    <tr><td>Téléphone</td><td><input type=\"text\" name=\"telephone\" value=\"$mf[telephone]\"></td></tr>
    <tr><td>Portable</td><td><input type=\"text\" name=\"portable\" value=\"$mf[portable]\"></td></tr>
    <tr><td>Fax</td><td><input type=\"text\" name=\"fax\" value=\"$mf[fax]\"></td></tr>
    <tr><td>E mail</td><td><input type=\"text\" name=\"mail\" value=\"$mf[mail]\" onkeyup=\"verifemail(this)\"></td></tr>
    <tr><td>Groupes</td><td><input type=\"hidden\" name=\"groupes\" value=\"$mf[groupes]\">$ugroupes</td><td style=\"border:solid thin #808080\">Groupes$fgroupes</td></tr>
    <tr><td>Horaires</td><td><input type=\"text\" name=\"horaires\" value=\"$mf[horaires]\"></td></tr>
    <tr id=\"trcodea\" style=\"".($mf['type']=="facture"?"":"display:none;")."\"><td>Code analytique</td><td><input type=\"text\" name=\"codea\" value=\"$mf[code]\"></td></tr>
    <tr><td><input type=\"submit\" value=\"Envoyer\"></td><td><input type=\"button\" value=\"Retour\" onclick=\"document.location='fournisseurs_liste.php\"></td></tr>
    </table>
    </form>";
    break;

    case "marques":
    $req=$DB_site2->query("SELECT * FROM marque ORDER BY libelle");
    while($marque=$DB_site2->fetch_array($req)){
        $marques[$marque['marqueid']]=$marque['libelle'];
    }
    $attrmid=array();

    print "<p>Cliquez sur les marques pour les déplacer entre les listes</p>
    <form action=\"fournisseurs_liste.php?action=marques\" method=\"post\">
  <input type=\"hidden\" name=\"action\" value=\"marques2\">
    <div style=\"float:left;\">";
    $req=$DB_site2->query("SELECT * FROM fournisseur WHERE type='fournadv' GROUP BY societe ORDER BY societe");
    while($fourn=$DB_site2->fetch_array($req)){
        print "<div id=\"div$fourn[id]\" class=\"fournbox\" onclick=\"additem($fourn[id]);\" onmouseover=\"this.style.borderColor='black';overitem='$mid';\" onmouseout=\"this.style.borderColor='#cccccc';\">
        <input type=\"hidden\" name=\"marq_$fourn[id]\" value=\"$fourn[marques]\">
        <div style=\"background-color:#cccccc; text-align:center;\">".$fourn['societe']."</div>";
        $mlist=explode(",",$fourn['marques']);
        foreach($mlist as $mid){
        $attrmid[]=$mid;
        $randid=rand(1000,10000);
        if($mid!="") print "<p id=\"drag_$randid\"><a href=javascript:removefromlist(".$fourn[id].",".$mid.",'drag_$randid');><img src='images/supprimer.gif' align='absmiddle'></a>".$marques[$mid]."</p>";
        }
        print "</div>";
    }
    print "</div>";

    print "<div id=\"mlist\" class=\"fournbox\" style=\"float:left;\" onclick=\"additem('contlist');\" onmouseover=\"this.style.border='solid thin black';overitem='list';\" onmouseout=\"this.style.border='';\">
    <div style=\"background-color:#cccccc; text-align:center;\">Marques</div>
    <div id=\"contlist\">";
    foreach($marques as $mid=>$name){
       print "<p id=\"list$mid\" style=\"".(!in_array($mid,$attrmid)?"color:red;":"") ."\" onclick=\"drag(this.id)\">$name</p>";
    }
    print "</div></div>";
    print "<p style=\"clear:both;\"><input type=\"submit\" value=\"Enregistrer\"> <input type=\"button\" value=\"Annuler\" onclick=\"document.location='fournisseurs_liste.php';\"></p>
    </form>";
    break;

    case "suivi":
    
    $fourn=array();
    $list=$DB_site2->query("SELECT * FROM fournisseur WHERE `type`='fournadv' ORDER BY societe");
    while($advf=$DB_site2->fetch_array($list)){
    if($advf['groupes']!=""){
        $groups=explode(",",$advf['groupes']);
        foreach($groups as $group)
        $fourn[$group][]=$advf['mail'];
    }else
        $fourn[$advf['societe']][]=$advf['mail'];
    }

    print suivilist($fourn,"Fournisseurs produits");

    $fourn=array();
    $list=$DB_site2->query("SELECT * FROM fournisseur WHERE `type`='trsptadv' ORDER BY societe");
    while($advf=$DB_site2->fetch_array($list)){
    if($advf['groupes']!=""){
        $groups=explode(",",$advf['groupes']);
        foreach($groups as $group)
        $fourn[$group][]=$advf['mail'];
    }else
        $fourn[$advf['societe']][]=$advf['mail'];
    }

    print suivilist($fourn,"Transporteurs");

    $fourn=array();
    $list=$DB_site2->query("SELECT * FROM fournisseur WHERE `type`='slsadv' ORDER BY societe");
    while($advf=$DB_site2->fetch_array($list)){
    if($advf['groupes']!=""){
        $groups=explode(",",$advf['groupes']);
        foreach($groups as $group)
        $fourn[$group][]=$advf['mail'];
    }else
        $fourn[$advf['societe']][]=$advf['mail'];
    }

    print suivilist($fourn,"Installateurs");

    $stock=new stock($DB_site2);
    $lieux=$stock->lieux();
    $tr="";
    foreach($lieux as $lid=>$lieu){
        $suivi="";
        $req=$DB_site2->query("SELECT nom,prenom FROM fournisseur WHERE suivi LIKE '%STOCK".$lid."%'");
        while($fourn=$DB_site2->fetch_array($req)) $suivi.=$fourn['nom']." ".$fourn['prenom']."<br>";
        if($suivi=="") $suivi="Aucun";
        $tr.="<tr><td>".$lieu."</td><td>".$suivi."</td><td><a href=\"fournisseurs_liste.php?action=modifsuivi&group=STOCK".$lid."\">Modifier</a></td></tr>";
    }
    print "<table class=\"table_factures\">
    <tr><td colspan=\"3\" style=\"text-align:center; font-weight:bold;\">Suivi stock</td></tr>
    <tr style=\"font-weight:bold;\"><td>Lieu</td><td>Suivi par</td><td></td></tr>
    $tr
    </table>";

    break;

    case "modifsuivi":
    if(isset($_GET['del'])){
        $contact=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id=".$_GET['del']);
        $suivis=explode(",",$contact['suivi']);
        $suivis2=array();
        foreach($suivis as $suivi) if($suivi!=$_GET['group']) $suivis2[]=$suivi;
        $newsuivi="";
        $suivis2=array_filter($suivis2);
        if($suivis2!=array()) $newsuivi=implode(",",$suivis2);

        $DB_site2->query("UPDATE fournisseur SET suivi='$newsuivi' WHERE id=".$_GET['del']);
    }

    $group=$_GET['group'];
    if(substr($group,0,5)=="STOCK"){
        $fourn=$DB_site2->query_first("SELECT societe,ville FROM fournisseur WHERE id=".intval(substr($group,5)));
        $group="Stock ".$fourn['societe']." ".$fourn['ville'];
    }
    print "<p>Suivi des commandes ".$group."</p>
    <form method=\"post\">
    <input type=\"hidden\" name=\"group\" value=\"".$_GET['group']."\">
    <table>";
    $requete=$DB_site2->query("SELECT * FROM fournisseur WHERE suivi LIKE '".addslashes($_GET['group']).",%' OR suivi LIKE '%,".addslashes($_GET['group'])."%' OR suivi LIKE '".addslashes($_GET['group'])."'");
    while($contact=$DB_site2->fetch_array($requete)) print "<tr><td>".$contact['societe']." ".$contact['nom']." ".$contact['prenom']."</td><td>".$contact['suivih']."H</td><td><a href=\"fournisseurs_liste.php?action=modifsuivi&group=".$_GET['group']."&del=".$contact['id']."\" title=\"Supprimer\"><img src=\"images/supprimer.gif\"></a></td></tr>";
    if($DB_site2->num_rows($requete)==0) print "<tr><td colspan=\"2\">Aucun suivi</td></tr>";

    print "<tr><td>Ajouter un contact pour le suivi :</td><td>Heure d'envoi</td></tr>
    <tr><td><select name=\"newsuivi\">";
    $requete=$DB_site2->query("SELECT * FROM fournisseur ORDER BY societe");
    while($contact=$DB_site2->fetch_array($requete)) print "<option value=\"$contact[id]\">$contact[societe] $contact[nom] $contact[prenom]</option>";
    for($i=0;$i<=24;$i++) $opth.="<option value=\"$i\">$i h</option>";
    print "</select></td><td><select name=\"suivih\">$opth</select></td><td><input type=\"submit\" value=\"Ajouter\"></td></tr>
    </table>
    </form>
    <p><input type=\"button\" value=\"Retour\" onclick=\"document.location='fournisseurs_liste.php?action=suivi';\"></p>";

    break;

    case "adv_h":
    if(isset($_POST['fourn'])){
        foreach($_POST['fourn'] as $name=>$h){
        $DB_site2->query("REPLACE INTO adv_heure (fournisseur,heure) VALUES ('".$name."',".$h.")");
        }
    }

    $liste=array();
    $req=$DB_site2->query("SELECT groupes,type FROM fournisseur WHERE groupes<>'' AND type IN ('fournadv','trsptadv','slsadv')");
    while($group=$DB_site2->fetch_array($req)){
        $sp=explode(",",$group['groupes']);
        foreach($sp as $g) $liste[$group['type']][$g]=1;
    }

    $req=$DB_site2->query("SELECT societe,type FROM fournisseur WHERE groupes='' AND type IN ('fournadv','trsptadv','slsadv')");
    while($group=$DB_site2->fetch_array($req)){
        $liste[$group['type']][$group['societe']]=1;
    }

    $gfourn=array_keys($liste['fournadv']);
    $gtrspt=array_keys($liste['trsptadv']);
    $gsls=array_keys($liste['slsadv']);

    $req=$DB_site2->query("SELECT * FROM adv_heure WHERE 1");
    while($data=$DB_site2->fetch_array($req)) $adv_h[$data['fournisseur']]=$data['heure'];

    asort($gfourn);
    print "<form method=\"post\">
    <p><b>Stock</b></p>
    <table>";
    $stock=new stock($DB_site2);
    foreach($stock->lieux() as $lieuid=>$lieu){
        $opt="<select name=\"fourn[Stock $lieu]\">";
        for($i=0;$i<=23;$i++) $opt.="<option value=\"$i\"".($adv_h['Stock '.$lieu]==$i?" selected":"").">$i h</option>";
        $opt.="</select>";
        print "<tr><td>".$lieu."</td><td>$opt</td></tr>";
    }
    print "</table>";

    print "<p><b>Fournisseurs</b></p>
    <table>";
    foreach($gfourn as $fourn){
        $opt="<select name=\"fourn[$fourn]\">";
        for($i=0;$i<=23;$i++) $opt.="<option value=\"$i\"".($adv_h[$fourn]==$i?" selected":"").">$i h</option>";
        $opt.="</select>";
        print "<tr><td>".$fourn."</td><td>$opt</td></tr>";
    }
    print "</table>";

    asort($gtrspt);
    print "<p><b>Transporteurs</b></p>
    <table>";
    foreach($gtrspt as $fourn){
        $opt="<select name=\"fourn[$fourn]\">";
        for($i=0;$i<=23;$i++) $opt.="<option value=\"$i\"".($adv_h[$fourn]==$i?" selected":"").">$i h</option>";
        $opt.="</select>";
        print "<tr><td>".$fourn."</td><td>$opt</td></tr>";
    }
    print "</table>";

    asort($gsls);
    print "<p><b>SLS</b></p>
    <table>";
    foreach($gsls as $fourn){
        $opt="<select name=\"fourn[$fourn]\">";
        for($i=0;$i<=23;$i++) $opt.="<option value=\"$i\"".($adv_h[$fourn]==$i?" selected":"").">$i h</option>";
        $opt.="</select>";
        print "<tr><td>".$fourn."</td><td>$opt</td></tr>";
    }
    print "</table>";

    print "<input type=\"submit\" value=\"Enregistrer\">
    </form>";
    break;
    }

}



function suivilist($fourn,$title){
      global $DB_site2;

      $ret="<table class=\"table_factures\">
    <tr><td colspan=\"3\" style=\"text-align:center; font-weight:bold;\">$title</td></tr>
    <tr style=\"font-weight:bold;\"><td>ADV</td><td>Suivi par</td></tr>";
    foreach($fourn as $group=>$groupmails){
       $mailcsv=implode(";",$groupmails);
       $ret.= "<tr onmouseover=\"this.style.background='#cccccc';\" onmouseout=\"this.style.background='none';\"><td>".$group."</td><td>" ;
       
       $requete=$DB_site2->query("SELECT * FROM fournisseur WHERE suivi LIKE '%".addslashes(trim($group))."%'");
       while($contact=$DB_site2->fetch_array($requete)) $ret.= $contact['nom']." ".$contact['prenom']."<br>";
       if($DB_site2->num_rows($requete)==0) $ret.= "Aucun";
       $ret.="</td><td><a href=\"fournisseurs_liste.php?action=modifsuivi&group=$group\">Modifier</a></td></tr>";
    }
    $ret.= "</table><br>";

    return $ret;
}

include "./footer.php";

?>
