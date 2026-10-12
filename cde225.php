<?php
require($_SERVER['DOCUMENT_ROOT']."/global.php");
/*if(!isset($_COOKIE['admin_username'])){
    header("Location: maintenance.html");
    exit;
}*/


//ici on fais afficher les balise hreflang pour fr et en
foreach($template->sites() as $site_code=>$site){
    foreach($site['hosts'] as $url=>$deflangue){
    if($site['actif'][$url])
        $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url."/cde225.php\">\n";
    }
}
$libelle = "libelle".$template->langue;

$factureid = $_POST['factureid'];
//Si aucune commande en cours, redirection sur la home
if(intval($factureid) == 0){
     header("location:/");
	 exit;
}

if(isset($_COOKIE['trspt']) && is_numeric($_COOKIE['trspt'])){
     $sql = "UPDATE facture SET";
     $sql .= " modelivraison = '".$_COOKIE['trspt']."'";
     $sql .= " WHERE facturid = ".$factureid;
     $DB_site->query($sql);
} 

//Récupération des informations sur la commande en cours
$sql = "SELECT *";
$sql .= " FROM facture";
$sql .= " WHERE facturid = ".intval($factureid);
$facture = $DB_site->query_first($sql);

//Si la commande en cours ne correspond pas à l'utilisateur en cours, redirection sur la home
if($facture['facturid'] != $factureid || $facture['userid'] != $user_info['userid']){
     header("location:/");
}

$_SESSION['facture_id'] = $facture['facturid'];

$prixtotalpanier = $totalpaht = $poidtotal = 0;
$artids   = array();
$prixtypeinstall="";

$objFacture = new facture($DB_site, $facture['facturid']);
$objFacture->lignes(); 
//Boucle sur chacun des produits
$redirection = true;
$poidscaves=array();

foreach($objFacture->lignes as $lfid => $lignefacture){
     //en cas de retour sur le choix de la garantie on supprime la ligne dans la table platinehome
     if(isset($_POST['retourEtape'])){
          $DB_site->query("DELETE FROM platinehome WHERE lignefactureid='".$lfid."'");
     }

     //Incrémentation des valeurs : poids total produits / prix de vente total TTC du panier / prix d'achat total HT du panier
     $poidtotal       += 1000 * floatval(str_replace(",",".",$lignefacture->emballage_poids)) * $lignefacture->qte;
     install_type($lignefacture->artid);
     if($lignefacture->typearticleid==1) $poidscaves[round($lignefacture->emballage_poids)]+=$lignefacture->qte;
     $prixtotalpanier += $lignefacture->prix * $lignefacture->qte;
     $totalpaht       += $lignefacture->paht * $lignefacture->qte;

     //Stockage de la quantité de chaque produit
     $artids[$lignefacture->artid] += $lignefacture->qte;
     
     //Récupération des garanties de base sur le produit
     $garantiesPossibles = DAOgarantie::loadGarantiesPayArtByPays($DB_site,$template->site_code(),'FR',$user_info['paysid'],$lignefacture->artid, !empty($user_info['raisonsociale']) ? 2 : 1, $lignefacture->prix_nogarantie,$lignefacture->tva,$lignefacture->lignefactureid);
     $platinehome = DAOgarantie::platinehome($DB_site,DAOgarantie::platinehomeID($DB_site),$lignefacture,$user_info['raisonsociale'], $objFacture->paysid);

     if(count($garantiesPossibles) > 0 || $platinehome){ //Si au moins 1 garantie payante possible, on affiche la page

        $redirection = false;
          
     } else { //Si uniquement garanties gratuites, on récupère la meilleure et on l'ajoute à la commande
        $tabGaranties = DAOgarantie::loadMaxGarantieArtByPays($DB_site,$template->site_code(),'FR',$user_info['paysid'],$lignefacture->artid,!empty($user_info['raisonsociale']) ? 2 : 1,$lignefacture->prix_nogarantie);

        foreach ($tabGaranties as $key => $garantie) {
            $libelle_garantie=$template->translate("/article/garantie_type/".($garantie->cumul==1?"extension_":"")."garantie",'F');
            DAOlignefacturegarantie::insert($DB_site,$template->site_code(),$tabGaranties[$key]->garantieid,$lignefacture->artid,$lignefacture->lignefactureid,$libelle_garantie,$tabGaranties[$key]->duree,$tabGaranties[$key]->fournisseur,DAOfournisseur::loadCodeFournisseurByName($DB_site,$tabGaranties[$key]->fournisseur),$user_info['paysid'],$tabGaranties[$key]->duree,$tabGaranties[$key]->type_garantie);
        }

     }
}

//Mise en forme des détails concernant l'adresse de livraison
$infotranspo = array();
if($_POST['shipdetail1']!="") $infotranspo[]="Digicode:".trim($_POST['shipdetail1']);
if($_POST['shipdetail6']!="") $infotranspo[]="Interphone:".trim($_POST['shipdetail6']);
if($_POST['shipdetail2']!="") $infotranspo[]="Etage:".trim($_POST['shipdetail2']);
if($_POST['shipdetail3']!="") $infotranspo[]="Code ascenseur:".trim($_POST['shipdetail3']);
if($_POST['shipdetail4']!="") $infotranspo[]=trim($_POST['shipdetail4']);
if($_POST['shipdetail5']!="") $infotranspo[]=trim($_POST['shipdetail5']);
$infos = strip_tags(implode(",",$infotranspo));

//Si le nom du client est a bien été renseigné dans l'adresse de livraison
if(isset($_POST['nom'])){
     
     $_POST['codepostal'] = trim($_POST['codepostal']);
    
     //Préparation de la requête pour mise à jour des informations de livraison client dans la commande
     $sqlMaj = ", civilite       = '".intval($_POST['civilite'])."'";
     $sqlMaj .= ", nom           = '".mysqli_real_escape_string($DB_site->link_id,$_POST['nom'])."'";
     $sqlMaj .= ", prenom        = '".mysqli_real_escape_string($DB_site->link_id,$_POST['prenom'])."'";
     $sqlMaj .= ", raisonsociale = '".mysqli_real_escape_string($DB_site->link_id,$_POST['raisonsociale'])."'";
     $sqlMaj .= ", adresse       = '".mysqli_real_escape_string($DB_site->link_id,$_POST['adresse'])."'";
     $sqlMaj .= ", codepostal    = '".mysqli_real_escape_string($DB_site->link_id,$_POST['codepostal'])."'";
     $sqlMaj .= ", ville         = '".mysqli_real_escape_string($DB_site->link_id,$_POST['ville'])."'";
     $sqlMaj .= ", telephone     = '".mysqli_real_escape_string($DB_site->link_id,$_POST['telephone'])."'";
     $sqlMaj .= ", mail          = '".mysqli_real_escape_string($DB_site->link_id,$_POST['email'])."'";
     $sqlMaj .= ", paysid        = ".intval($_POST['monpays']);
    
     //Mise à jour de l'adresse de livraison client dans sa fiche
     $sql = "UPDATE utilisateur SET";
     $sql .= " nom_livraison            = '".mysqli_real_escape_string($DB_site->link_id,$_POST['nom'])."'";
     $sql .= ", prenom_livraison        = '".mysqli_real_escape_string($DB_site->link_id,$_POST['prenom'])."'";
     $sql .= ", raisonsociale_livraison = '".mysqli_real_escape_string($DB_site->link_id,$_POST['raisonsociale'])."'";
     $sql .= ", adresse_livraison       = '".mysqli_real_escape_string($DB_site->link_id,$_POST['adresse'])."'";
     $sql .= ", codepostal_livraison    = '".mysqli_real_escape_string($DB_site->link_id,trim($_POST['codepostal']))."'";
     $sql .= ", ville_livraison         = '".mysqli_real_escape_string($DB_site->link_id,$_POST['ville'])."'";
     $sql .= ", telephone_livraison     = '".mysqli_real_escape_string($DB_site->link_id,$_POST['telephone'])."'";
     $sql .= ", paysid_livraison        = '".intval($_POST['monpays'])."'";
     $sql .= ", infotranspo             = '".mysqli_real_escape_string($DB_site->link_id,$infos)."'";
     $sql .= " WHERE userid = ".$user_info['userid'];
     $DB_site->query($sql);
     
     $sql = "UPDATE facture SET";
     $sql .= " infotranspo = '".mysqli_real_escape_string($DB_site->link_id,$infos)."'";
     $sql .= $sqlMaj;
     $sql .= " WHERE facturid = ".intval($factureid);
     $DB_site->query($sql);
}

//Récupération des informations client
$sql = "SELECT *";
$sql .= " FROM utilisateur";
$sql .= " WHERE userid = ".$user_info['userid'];
$user_info = $DB_site->query_first($sql);


if(!isset($_POST['action2'])){
    
     $sqlfdp   = "";
     $tarifsls = $tarifmes = 0;
     $incoterms = "";


     $frais_port = new frais_port($DB_site,$facture['facturid']);
     $prixport   = $frais_port->prixttc($facture['tvaport']);
     $install    = pays_install($user_info['paysid_livraison']);

     if(($facture['modelivraison'] == 1 || $facture['modelivraison'] == 4) && $install != false){

          if($install == "sls") $tarifsls = prixsls($user_info['codepostal_livraison'],$poidtotal,$user_info['paysid_livraison'],$prixtypeinstall);
          if($install == "enigma") $tarifsls = prix_enigma($poidscaves,$template->site_code());

          if($facture['modelivraison'] == 4) $tarifsls += prix_montage($facture['facturid']);
          if($user_info['paysintracom'] == 1 && $user_info['tvaintracom'] != "") $tarifsls=$tarifsls/(1+$user_info['TVAtauxnormal']/100);
          
     }elseif($facture['modelivraison'] == 7){

          //On commence par supprimer les installations potentiellement déjà enregistrée pour la commande
          $sql = "DELETE FROM lignefactureinstallation WHERE factureid = ".intval($facture['facturid']);
          $DB_site->query($sql);

          $prix_ttc = 0;
          //tarif mise en service
          $aTypeArticle = array();
          $req = $DB_site->query("SELECT DISTINCT(a.typearticleid) FROM lignefacture lf INNER JOIN article a ON (a.artid = lf.artid) WHERE lf.factureid = ".$facture['facturid']);
          
          while($row = $DB_site->fetch_array($req)){
             $aTypeArticle[] = $row['typearticleid'];
          }

          $aInstallation = array();
          //Récupérations des installations correspondantes
          foreach($aTypeArticle as $typearticleid){
               $req = $DB_site->query("SELECT * FROM installation i WHERE i.installation_type_id = 2 AND installation_typearticle = '|".$typearticleid."|'");
               while($row = $DB_site->fetch_array($req)){
                 $aInstallation[] = $row;
               }
          }
          if(count($aInstallation) > 0){
               foreach($aInstallation as $l_installation){
                    //Insertion des informations sur l'installation, dans la commande
                    $sql = "INSERT INTO lignefactureinstallation (";
                    $sql .= "factureid";
                    $sql .= ", installation_id";
                    $sql .= ", lignefactureinstallation_differenceprix";
                    $sql .= ", lignefactureinstallation_paht";
                    $sql .= ", lignefactureinstallation_tauxtva";
                    $sql .= ", lignefactureinstallation_typearticles";
                    $sql .= ", lignefactureinstallation_type";
                    $sql .= ", lignefactureinstallation_description";
                    $sql .= ") VALUES (";
                    $sql .= intval($factureid);
                    $sql .= ", ".$l_installation['installation_id'];
                    $sql .= ", '".($l_installation['installation_pv_ht'] * (1 + ($l_installation['installation_tva_taux'] / 100)))."'";
                    $sql .= ", '".$l_installation['installation_pa_ht']."'";
                    $sql .= ", '".$l_installation['installation_tva_taux']."'";
                    $sql .= ", '".$l_installation['installation_typearticle']."'";
                    $sql .= ", ".$l_installation['installation_type_id'];
                    $sql .= ", '".addslashes($l_installation['installation_description'])."'";
                    $sql .= ")";
                    $DB_site->query($sql);

                     //Concaténation des types d'articles pour affichage dans le titre
                     $aTemp = explode('|', $l_installation['installation_typearticle']);
                     $aTypeTemp = array();
                     foreach($aTemp as $ligne){
                        if(is_numeric($ligne)) $typearticleid = $ligne;
                     }                      
                     //Calcul du prix de vente TTC
                     $prix_ttc += $l_installation['installation_pv_ht'] * (1 + ($l_installation['installation_tva_taux'] / 100));
               }
          }

          $tarifmes = $prix_ttc + 30;
          $tarifmes += prixsls($user_info['codepostal_livraison'],$poidtotal,$user_info['paysid_livraison'],$prixtypeinstall);


     } elseif($facture['modelivraison'] == 5){
          
          $prixport = floatval($_POST['prixlivr5']) * ((intval($facture['tvaport'])/100)+1);
          $txtlivr = mysqli_real_escape_string($DB_site->link_id,$_POST['txtlivr']);
          $incoterms = mysqli_real_escape_string($DB_site->link_id,$_POST['incoterms-sp']);
          
          if($incoterms != "")
               $sqlfdp=" modelivraison = '5' , incoterms ='".$incoterms." - ".$txtlivr."'";
          else          
               $sqlfdp=" modelivraison = '5' , txtlivr='".$txtlivr."', incoterms ='".$incoterms."'";

     }elseif($facture['modelivraison'] > 2) {

          $sqlfdp=" modelivraison = '0'";
     }
     
     $totalport = $prixport + $tarifsls + $tarifmes;

     $sqlfdp .= ($sqlfdp != '' ? ',' : '') . " prixport=".floatval($totalport);
    
     if(isset($_POST['datelivr'.$facture['modelivraison']])){
          
          $sp = explode("/",$_POST['datelivr'.$facture['modelivraison']]);
          $datelivr = mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
          $sqlfdp .= ", datelivraison=".intval($datelivr);
          if($_POST['datelivr'.$facture['modelivraison']] != $_POST['datelivrorigin'.$facture['modelivraison']]) {
               if($facture['commercial'] != "") {
                    $sqlfdp.=", changelivraison='".$facture['commercial']."'";
               }
               else {
                    $sqlfdp.=", changelivraison='client'";
               }
          } 
     }
     //Suppression du produit "Récupération pour destruction" avant réinsertion
     $sql = "DELETE FROM lignefacture";
     $sql .= " WHERE factureid = ".$factureid;
     $sql .= " AND libelle LIKE '%".mysqli_real_escape_string($DB_site->link_id,$template->translate("/commande/recuperation_cave"))."%'";
     $DB_site->query($sql);
     if(isset($_POST['recup'])){
          $tauxTVA = $template->taux_tva($user_info['paysid']);
          $sql = "INSERT INTO lignefacture SET";
          $sql .= " factureid = ".$factureid."";
          $sql .= ", qte      = 1";
          $sql .= ", prix     = 0"; //modifié le 08/10/2018 par Ariel. Le prix n'est plus de 60€ mais gratuit.
          $sql .= ", tva      = ".$tauxTVA['taux1'];
          $sql .= ", libelle  = '".mysqli_real_escape_string($DB_site->link_id,$template->translate("/commande/recuperation_cave"))." ".stripslashes(strip_tags($_POST['dim']))." ".stripslashes($_POST['poids'])."kg'";
          $DB_site->query($sql);
     } 
  
      //Mise à jour des informations de livraison de la commande
      $sql = "UPDATE facture SET";
      $sql .= $sqlfdp;
      $sql .= " WHERE facturid = ".intval($factureid);
      $DB_site->query($sql);
}


//Si aucun produit de la commande n'a de garantie potentielle, on passe directement à l'étape suivante
if($redirection){
     
     $_SESSION['facture_id'] = $facture['facturid'];
     header("Location: /cde25.php");
     exit;
}

//Récupération des informations sur la commande
$fcl = new facture($DB_site, $facture['facturid']);
$fcl->lignes();

if(!isset($_POST['destinataire_id'])){
     $destinataire_id = !empty($user_info['raisonsociale']) ? 2 : 1;
} else {
     $destinataire_id = $_POST['destinataire_id'];
}

//Récupération du template des garanties, par article
foreach($fcl->lignes as $lfid => $lignefacture){
     
     //Récupération des garanties disponible pour le produit
     $lesGaranties = DAOgarantie::loadMaxGarantieArtByPays($DB_site,$template->site_code(),'FR',$user_info['paysid'],$lignefacture->artid,$destinataire_id,$lignefacture->prix_nogarantie);

     $dureeBase             = 0;
     $garantieDeBaseAffiche = "";

     //Si le produit a des garanties disponibles
     if(count($lesGaranties) > 0){

          //On boucle sur chacune d'entre elles
          for($i=0; $i < count($lesGaranties); $i++){

               $duree        = $lesGaranties[$i]->duree;
               $dureeAf      = $duree;
               $typeGarantie = $template->translate("/article/garantie_type/garantie",'F');

               //Si la garantie en cours est une extension
               if ($lesGaranties[$i]->cumul == 1){

                    //On cherche dans le tableau qui contient toutes les garanties offertes celle du fournisseur de produit
                    $pointer = DAOgarantie::searchGarantieToAddExtension($DB_site,$lesGaranties[$i]->type_garantie,$lesGaranties,$lesGaranties[$i]->garantieid);

                    //On prend la durée de cette garantie pour l'ajouter à l'extension
                    $dureeAf += $lesGaranties[$pointer]->duree;
                    $typeGarantie = $template->translate("/article/garantie_type/extension_garantie",'F')." +";

               }

               //Pour orthographier "an" correctement
               $an = $template->translate("/article/garantie_type/an");

               if ($lesGaranties[$i]->duree > 1) {

                    $an = $template->translate("/article/garantie_type/ans");

               }

               //Calculs pour déterminer la meilleure garantie incluse/gratuite
               if($i == 0){

                    $ppi           = $lesGaranties[$i]->ppi;
                    $prix          = $lesGaranties[$i]->prix;
                    $dureeBase     = $lesGaranties[$i]->duree;
                    $dureeDeBaseAf = $dureeAf;

                    //Durée de la garantie (limitée ou "à vie")
                    if($dureeAf != 99){

                         $anneeGarantie = $dureeAf." ".$an;

                    } else {

                         $anneeGarantie = $template->translate("/article/garantie_type/a_vie",'F');

                    }

                    $garantieDeBaseAffiche = $anneeGarantie." ".$template->translate("/article/garantie_type/".$lesGaranties[$i]->type_garantie);
                    $garantieID            = $lesGaranties[$i]->garantieid;
                    $pointer = 0;

                    if($lesGaranties[$i]->cumul == 1){

                            //On cherche dans le tableau qui contient toutes les garanties offertes celle du fournisseur de produit
                            $pointer = DAOgarantie::searchGarantieToAddExtension($DB_site,$lesGaranties[$i]->type_garantie,$lesGaranties,$lesGaranties[$i]->garantieid);
                            //On prend la durée de cette garantie pour l'ajouter à l'extension
                            $ppi       += $lesGaranties[$pointer]->ppi;
                            $prix      += $lesGaranties[$pointer]->prix;
                            $dureeBase += $lesGaranties[$pointer]->duree;

                    }

                    $auLieuDe = "";

                    if($ppi > $prix){

                         $auLieuDe = " ".$template->translate("/article/garantie_type/au_lieu_de")." ".$template->price_format($ppi);

                    }
               }

               //Intitulé de la ligne de garantie
               $libGarantie = $typeGarantie." ".$duree." ".$an." ".$template->translate("/article/garantie_type/".$lesGaranties[$i]->type_garantie)." ".DAOpays::getPaysById($DB_site,$user_info['paysid']);
               
               $add = true;

               //On vérifie que ce fournisseur de garantie n'est pas déjà présent dans la table avec une garantie non cumulable dont la durée serait supérieure à celle-ci
               $sql = "SELECT *";
               $sql .= " FROM lignefacturegarantie lfg";
               $sql .= " INNER JOIN garantie g ON (g.garantieid = lfg.garantieid)";
               $sql .= " WHERE lignefactureid = ".$lignefacture->lignefactureid;
               $sql .= " AND lfg.fournisseur = '".$lesGaranties[$i]->fournisseur."'";
               $sql .= " AND g.cumul = 2";
               $sql .= " AND dureeReelle > ".$lesGaranties[$i]->duree;
               $sql .= " AND lfg.garantieid <> ".$lesGaranties[$i]->garantieid;
               if($DB_site->num_rows($DB_site->query($sql))>0) {

                    $add = false;

               }

               $sql = "SELECT *";
               $sql .= " FROM lignefacturegarantie";
               $sql .= " WHERE lignefactureid = ".$lignefacture->lignefactureid;
               $sql .= " AND garantieid = ".$lesGaranties[$i]->garantieid;
               if ($DB_site->num_rows($DB_site->query($sql))==0 && $add) {

                    //Si elles n'existent pas déjà on enregistre la garantie constructeur/de base, on ajoute
                    DAOlignefacturegarantie::insert($DB_site,$template->site_code(),$lesGaranties[$i]->garantieid,$lignefacture->artid,$lignefacture->lignefactureid,$typeGarantie,$dureeAf,$lesGaranties[$i]->fournisseur,DAOfournisseur::loadCodeFournisseurByName($DB_site,$lesGaranties[$i]->fournisseur),$user_info['paysid'],$duree,$lesGaranties[$i]->type_garantie);

               }

          }

     }
}

////** DEBUT TRAITEMENT GARANTIE **\\\\
if(isset($_POST['action2']) && $_POST['action2'] == "garantie"){
  
     foreach($_POST['garantie'] as $key => $values){
          $abo = false;
          $tabTemp = explode('-',$key);
          $lignefactureid = $tabTemp[0];
          $artid          = $tabTemp[1];
          $garantieid     = $tabTemp[2];

          //on vérifie si c'est la garantie abonnement platine home
          //if($_POST['garantie'][$lignefactureid]['choixGarantie'] != ''){
          $gar = $DB_site->query_first("SELECT utilisation_garantie FROM garantie WHERE garantieid='".$_POST['garantie'][$lignefactureid]['choixGarantie']."'");
          if(intval($gar[0]) == 2 && $_POST['garantie'][$lignefactureid]['choixGarantie'] == $garantieid) $abo = true;
          //}
          
          if(count($tabTemp) == 3 && $_POST['garantie'][$lignefactureid]['choixGarantie'] == $garantieid && !$abo){

               //Récupération du montant de la garantie associée au produit choisi
               $sql = "SELECT SUM(differenceprix)";
               $sql .= " FROM lignefacturegarantie";
               $sql .= " WHERE lignefactureid = ".$lignefactureid;
               $sommePrixGarantieBefore = $DB_site->fetch_array($DB_site->query($sql));
               $sommePrixGarantieBefore = $sommePrixGarantieBefore[0];

               //Si la garantie est payante, on soustrait son prix au montant du produit
               if($sommePrixGarantieBefore > 0){

                    $sql = "UPDATE lignefacture SET";
                    $sql .= " prix = prix - ".$sommePrixGarantieBefore;
                    $sql .= " WHERE lignefactureid = ".$lignefactureid;
                    $DB_site->query($sql);

               }

               //Suppresion de la garantie préalablement associée
               $sql = "DELETE FROM lignefacturegarantie";
               $sql .= " WHERE lignefactureid = ".$lignefactureid;
               $DB_site->query($sql);

               //Récupération des informations sur la nouvelle garantie choisie
               $laGarantie = new garantie($DB_site, $template->site_code(), 'FR', $garantieid, $artid, $values['prix'], $lignefactureid);

               $typeGarantie = $template->translate("/article/garantie_type/garantie",'F');
               $duree        = $laGarantie->duree;

               //Si c'est une extension
               if ($laGarantie->cumul == 1){
					
				$duree = $values['duree'];
                    $typeGarantie = $template->translate("/article/garantie_type/extension_garantie",'F')." +";
               }
			   
			$tabArtGarantie = DAOgarantie::checkMultiProduit($DB_site, $artid);
               if(DAOarticle::isLot($DB_site, $artid) || !empty($tabArtGarantie)){ 

                    //Ajout de la nouvelle garantie sur le produit
                    DAOlignefacturegarantie::insert($DB_site,$template->site_code(), $garantieid, $artid, $lignefactureid, $typeGarantie, $duree == 0 ? $laGarantie->duree : $duree, $laGarantie->fournisseur, DAOfournisseur::loadCodeFournisseurByName($DB_site,$laGarantie->fournisseur), $user_info['paysid'], $laGarantie->duree, $laGarantie->type_garantie, $values['gar_prix'], $values['gar_ppi'], $values['gar_paht']);

               } else { //Ce n'est pas un lot => Insertion classique

                    //Ajout de la nouvelle garantie sur le produit
                    DAOlignefacturegarantie::insert($DB_site,$template->site_code(), $garantieid, $artid, $lignefactureid, $typeGarantie, $duree == 0 ? $laGarantie->duree : $duree, $laGarantie->fournisseur, DAOfournisseur::loadCodeFournisseurByName($DB_site,$laGarantie->fournisseur), $user_info['paysid'], $laGarantie->duree, $laGarantie->type_garantie);

               }
               if($laGarantie->utilisation_garantie != 0)
                    DAOlignefacturegarantie::insertFourn($DB_site, "base",$artid,$lignefactureid,$user_info['paysid'],$template->translate("/article/garantie_type/garantie",'F'),1);

               //Calcul du montant de la garantie après insertion
               $sql = "SELECT SUM(differenceprix)";
               $sql .= " FROM lignefacturegarantie";
               $sql .= " WHERE lignefactureid = ".$lignefactureid;
               $sommePrixGarantieAfter = $DB_site->fetch_array($DB_site->query($sql));
               $sommePrixGarantieAfter = $sommePrixGarantieAfter[0];

               //Si elle a un cout, on l'ajoute au prix du produit
               if ($sommePrixGarantieAfter > 0) {

                    $sql = "UPDATE lignefacture SET";
                    $sql .= " prix = prix + ".$sommePrixGarantieAfter;
                    $sql .= " WHERE lignefactureid = ".$lignefactureid;
                    $DB_site->query($sql);

               }
          }elseif ($abo) {
               
               //Suppresion de la garantie préalablement associée
               $sql = "DELETE FROM lignefacturegarantie";
               $sql .= " WHERE lignefactureid = ".$lignefactureid;
               $DB_site->query($sql);
               
               //Récupération des informations sur la nouvelle garantie choisie
               $laGarantie = new garantie($DB_site, $template->site_code(), 'FR', $garantieid, $artid, $values['prix']);
               $typeGarantie = $template->translate("/article/garantie_type/garantie",'F');

               //$tabArtGarantie = DAOgarantie::checkMultiProduit($DB_site, $artid);

               //Ajout de la nouvelle garantie sur le produit. Duree a 100 pour reconnaitre la garantie abo
               DAOlignefacturegarantie::insert($DB_site,$template->site_code(), $garantieid, $artid, $lignefactureid, $typeGarantie, 100, $laGarantie->fournisseur, DAOfournisseur::loadCodeFournisseurByName($DB_site,$laGarantie->fournisseur), $user_info['paysid'], 100, $laGarantie->type_garantie,0,0,0);
               //Ajout de la garantie constructeur
               DAOlignefacturegarantie::insertFourn($DB_site, "base",$artid,$lignefactureid,$user_info['paysid'],$typeGarantie,1);

               $lfid = $lignefactureid;

               $qte = $DB_site->query_first("SELECT qte FROM lignefacture WHERE lignefactureid ='".$lignefactureid."'");
               $qte = $qte['qte'];
               $sql = "SELECT lignefactureid FROM platinehome WHERE lignefactureid='".$lignefactureid."'";
               $result = $DB_site->query($sql);
               $telephoneph = parseNumFr($_POST['telephoneph_'.$lfid],$DB_site);
               $iban = str_replace(" ", "", mysqli_real_escape_string($DB_site->link_id,$_POST['ibanph_'.$lfid]));
               $naissance = mysqli_real_escape_string($DB_site->link_id,$_POST['datenaissancephaa_'.$lfid])."-".mysqli_real_escape_string($DB_site->link_id,$_POST['datenaissancephmm_'.$lfid])."-".mysqli_real_escape_string($DB_site->link_id,$_POST['datenaissancephjj_'.$lfid]);
               if($DB_site->num_rows($result) == 0){
                    $sql = "INSERT INTO platinehome SET lignefactureid ='".$lignefactureid."', factureid='".$factureid."', userid='".$user_info['userid']."',naissance='".$naissance."', telephone='".$telephoneph."', iban='".$iban."', offre ='".mysqli_real_escape_string($DB_site->link_id,$_POST['offreplatine_'.$lfid])."', offre_prix='".mysqli_real_escape_string($DB_site->link_id,$_POST['offer_price_'.$lfid])."', offre_categorie_id='".mysqli_real_escape_string($DB_site->link_id,$_POST['offer_category_id_'.$lfid])."', count=".mysqli_real_escape_string($DB_site->link_id,$_POST['count_'.$lfid])." ";
                    for($i=0;$i<$qte;$i++){
                         $DB_site->query($sql);
                    }     
               }else{
                    $sql = "UPDATE platinehome SET naissance='".$naissance."', telephone='".$telephoneph."', iban='".$iban."', offre ='".mysqli_real_escape_string($DB_site->link_id,$_POST['offreplatine_'.$lfid])."',offre_prix='".mysqli_real_escape_string($DB_site->link_id,$_POST['offer_price_'.$lfid])."', offre_categorie_id='".mysqli_real_escape_string($DB_site->link_id,$_POST['offer_category_id_'.$lfid])."', count=".mysqli_real_escape_string($DB_site->link_id,$_POST['count_'.$lfid])." WHERE lignefactureid ='".$lignefactureid."'";
                    $DB_site->query($sql);
               }
               
          }
     }
     header("Location: /cde25.php");
     exit;
}
////** FIN TRAITEMENT GARANTIE **\\\\

$template->parts["facturid"] = $factureid;
$template->parts["texte_garanties_pro"] = '';

/*if(isset($_POST['destinataire_id'])) $template->parts["js_garanties"] = 'ajaxrequest("ajax/affiche_garanties.php?facture_id='.$factureid.'&destinataire_id='.$_POST['destinataire_id'].'",affiche_encart_garanties_cb);';

if(!isset($_POST['destinataire_id']) || $_POST['destinataire_id'] == 0) $_POST['destinataire_id'] = 0; */
if(!isset($_POST['destinataire_id'])) $_POST['destinataire_id'] = 0;

if($user_info['raisonsociale'] != ''){
      $template->parts["liste_garanties0"]="
      <form method=\"post\" name=\"frmchoixGarantie\" action=\"cde25.php\">
      <input type=\"hidden\" value=\"[?facturid]\" name=\"factureid\">
      </form>
      <a href=\"javascript:void(0);\" class=\"next_step_garantie\" onclick=\"document.forms.frmchoixGarantie.submit();\">".$template->translate('/panier/garantie_valide')."<i class=\"fa fa-chevron-right\"></i></a>";
      $template->evaltemplate("GarantiesPro","texte_garanties_pro");
} else {
      $_GET['facture_id']=$factureid;
      $_GET['destinataire_id']=1;
      $template->evaltemplate("GarantiesPart","texte_garanties_pro");
      ob_start();
      include("ajax/affiche_garanties.php");
      $template->parts["liste_garanties"]=ob_get_clean();
      $template->parts["display_step2"]=" style=\"display:block;\"";
      
//      $template->parts["js_garanties"] = 'ajaxrequest("ajax/affiche_garanties.php?facture_id='.$factureid.'&destinataire_id=1",affiche_encart_garanties_cb);';
}

$template->parts["etape"]    = "<img src=\"images/templates/commande4.jpg\" alt=\"Garantie\" class=\"img img-responsive\">";
//Paramétrage des META référencement de la page
if($sitepro)
{   
    $title_trad = '/index/meta_title_pro';
    $title_desc = '/index/meta_description_pro';
}
else
{
    $title_trad = '/index/meta_title';
    $title_desc = '/index/meta_description';
}

//BarreCookie
$template->parts["stylePanier"]="style=\"bottom:0;\"";


$template->pageparams("www.mon-espace-cuisson.com", $template->translate($title_trad), $template->translate($title_desc));
$template->parts['ipiderr'] = $template->translate("/garantie/platine_home/ipid_erreur");
$template->evaltemplate("PanierPlatineHomeIpid", "ipid2");
$template->evaltemplate("PanierGarantieIpid", "ipid");
$template->evaltemplate("PanierGarantie", "pagecommande");
$template->evaltemplate("PanierPage","main");

$template->out();

