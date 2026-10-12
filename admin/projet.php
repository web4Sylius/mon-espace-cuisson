<?php
require_once("admin_global.php");
include_once("../class/projet.class.php");
include_once("marketplace/article.php");
include_once("../class/fournisseur.class.php");
include_once("../class/DAOarticle.class.php");
include_once("../class/garantie.class.php");
include_once("../class/categorie.class.php");
include_once("../class/DAOgarantie.class.php");
include_once("../class/configurateur.class.php");
include_once("../class/configurateur/complet.php");
include("../class/fonctions.php");
include_once("includes/utilisateur_fonction.php");
include_once("../class/frais_port.php");
include_once("../class/stock.class.php");
require_once("marge.php");
require_once("../class/cocktel.class.php");


$DB_site=$DB_site2;

$projets=new projets();

if(isset($_GET['projetid'])){
    $projet=new projet($_GET['projetid']);
}elseif(isset($_GET['foruserid'])){
    $projet=new projet(null,$_GET['foruserid']);
}else{
    $projet = new projet();
}

/*if($_SERVER['REQUEST_METHOD']=="POST"){
    $f=fopen("projet.log","a");
    fwrite($f,date("d/m/Y H:i:s")." ".$user_info['username']." ".$projet->projetid." ".var_export($_POST,true)."\n");
    fclose($f);
}*/


if(isset($_POST['action'])){
    switch($_POST['action']){
        case "editer":
        if(isset($_POST['besoins'])){
            $projet->besoin=$_POST['user_projet_besoin'];
            $projet->projetid=$_GET['projetid'];
        }
        else{
            //Si budget auto du dernier devis coché :
            if($_POST['user_projet_devis'] == '1'){
                $_POST['user_projet_budget'] = $_POST['valeur_budget_auto'];
                $projet->budget_devis = 1;
            }
            else{
                $projet->budget_devis = 0;
            }
            $projet->commercial=$_POST['user_projet_commercial'];
            $projet->libelle=$_POST['user_projet_libelle'];
            $projet->budget=$_POST['user_projet_budget']!=""?$_POST['user_projet_budget']:0;
            $projet->budget_type=isset($_POST['user_projet_sans_budget']) ? $_POST['user_projet_sans_budget'] : 0;
            $projet->etatid=$_POST['user_projet_etatid'];
            $projet->facturid=$_POST['user_projet_facturid'];
            $projet->dateecheance=empty($_POST['user_projet_echeance_livraison'])?null:new datetime($_POST['user_projet_echeance_livraison']);
            $projet->echeance_type=$_POST['echeance_type'];
            $projet->delai_cmd=$_POST['delai_cmd'];

            if($_POST['user_projet_etatid']==6 || $_POST['user_projet_etatid']==5){
                $projet->rappel(0,'',null);
            }

            if($_POST['user_projet_etatid']==6){
                $DB_site2->query("INSERT INTO causeperte SET userid=".$projet->userid.", date=".time().", commentaire='PJT".$projet->projetid."', cause=".intval($_POST['causeperte']));
            }
        }

        if(is_null($projet->userid)) $projet->userid=$_POST['userid'];
        $projet->save();

        if(!isset($_GET['projetid'])){
            header("location:projet.php?projetid=".$projet->projetid);
            exit;
        }
        break;
        
        case "etat":
        $tsrappel=0;
        $rappelrdv="";
        if($_POST['rappeltype'] == "rdv"){
            $rappelrdv="_rdv";
        }

        if($_POST['rappel'.$rappelrdv]!=""){
            $sp=preg_split('/[- :\/h]/',$_POST['rappel'.$rappelrdv]);
            $tsrappel=mktime($sp[3],$sp[4],0,$sp[1],$sp[0],$sp[2]);
            if($_POST['rappeltype'] !== "rdv"){
                if($_POST['jour']=='1'){
                    $tsrappel=mktime(9,0,0,$sp[1],$sp[0],$sp[2]);
                }
                elseif($_POST['jour']=='2'){
                    $tsrappel=mktime(14,0,0,$sp[1],$sp[0],$sp[2]);
                }
            }
        }

        $projet->rappel($tsrappel,$_POST['pour'],$_POST['rappeltype']);

        //Si budget auto du dernier devis coché :
        if($_POST['user_projet_devis'] == '1'){
            $_POST['budget'] = $_POST['valeur_budget_auto'];
            $projet->budget_devis = 1;
        }
        else{
            $projet->budget_devis = 0;
        }

        $projet->etatid=$_POST['etat'];
        $projet->dateecheance=empty($_POST['suivi_echeance'])?null:new datetime($_POST['suivi_echeance']);
        $projet->echeance_type=$_POST['echeance_type'];
        $projet->delai_cmd=$_POST['delai_cmd'];
        $projet->budget=$_POST['budget'];
        $projet->budget_type=isset($_POST['sans_budget']) ? $_POST['sans_budget'] : 0;
        $projet->save();

        $time = time() + 1;
        if($_POST['etat']==6){
            $DB_site2->query("INSERT INTO causeperte SET userid=".$projet->userid.", date=".time().", commentaire='PJT".$projet->projetid."', cause=".intval($_POST['causeperte']));
        }
        else{
            $DB_site2->query("INSERT INTO suivi_client SET userid='".$_POST['userid']."', commercial='$user_info[username]', date=".$time.", suivi='Envoi du mail', rappel=".$tsrappel.", jour=".intval($_POST['jour']).", rappeltype='".$_POST['rappeltype']."', etat=".intval($_POST['etat']).", pour='".$_POST['pour']."', type='".$_POST['type']."', moyen='".$_POST['moyen']."', destinataire='".$_POST['destinataire']."', projetid='".$projet->projetid."', callid='".$_POST['callid']."'");
        }
        break;
        
        case "swap":
        $projet->swaprelation($_POST['swapwith']);
        break;
        
        case "addrelation":
        foreach($_POST['newrelation'] as $newrelation)
            $projet->addrelation($newrelation);
        break;
        
        case "rmrelation":
        $projet->rmrelation($_POST['userid']);
        break;

        //Réccupére le fournisseur coché pour l'inserer dans la table projet_relation_fournisseur
        case "relation_fourn":
        $ajout_fournisseur=$DB_site2->query("INSERT INTO projet_relation_fournisseur SET projetid='".$projet->projetid."', societe='".addslashes($_POST['societe_fourn'])."', dateline='".time()."'");
        break;
        
        case "contacts":
        $projet->contactids=$_POST['projet_contact'];
        $projet->save();
        break;

        case "add_fourn":
        $projet->fournisseurs=$_POST['contacts_fourn'];
        $projet->save();
        break;

        case "rmrelationfourn":
        $projet->rmrelationfourn($_POST['fournid']);
        break;
        
        case "suivi":
        $tsrappel=0;
        $rappelrdv=$rappelext="";

        if($_POST['rappeltype'] == "rdv"){
            $rappelrdv="_rdv";
        }

        if($_POST['pour'] == ""){
            $_POST['pour'] = 'externe';
            $rappelext = "_ext";
        }

        if($_POST['rappel'.$rappelrdv.$rappelext]!=""){
            $sp=preg_split('/[- :\/h]/',$_POST['rappel'.$rappelrdv.$rappelext]);
            $tsrappel=mktime($sp[3],$sp[4],0,$sp[1],$sp[0],$sp[2]);
            if($_POST['rappeltype'] !== "rdv"){
                if($_POST['jour']=='1'){
                    $tsrappel=mktime(9,0,0,$sp[1],$sp[0],$sp[2]);
                }
                elseif($_POST['jour']=='2'){
                    $tsrappel=mktime(14,0,0,$sp[1],$sp[0],$sp[2]);
                }
            }
        }

        
        $projet->rappel($tsrappel,$_POST['pour'],$_POST['rappeltype']);

        //Si budget auto du dernier devis coché :
        if($_POST['user_projet_devis'] == '1'){
            $_POST['budget'] = $_POST['valeur_budget_auto'];
            $projet->budget_devis = 1;
        }
        else{
            $projet->budget_devis = 0;
        }

        $projet->etatid=$_POST['etat'];
        $projet->facturid=$_POST['projet_facturid'];
        $projet->dateecheance=empty($_POST['suivi_echeance'])?null:new datetime($_POST['suivi_echeance']);
        $projet->echeance_type=$_POST['echeance_type'];
        $projet->delai_cmd=$_POST['delai_cmd'];
        $projet->budget=$_POST['budget'];
        $projet->budget_type=isset($_POST['sans_budget']) ? $_POST['sans_budget'] : 0;
        $projet->save();

        $time = time() + 1;
        $DB_site2->query("INSERT INTO suivi_client SET userid='".$_POST['userid']."', commercial='$user_info[username]', date=".$time.", suivi='".mysqli_real_escape_string($DB_site2->link_id,$_POST['suivi'])."', rappel=".$tsrappel.", jour=".intval($_POST['jour']).", rappeltype='".$_POST['rappeltype']."', etat=".intval($_POST['etat']).", pour='".$_POST['pour']."', type='".$_POST['type']."', moyen='".$_POST['moyen']."', destinataire='".$_POST['destinataire']."', projetid='".$projet->projetid."', callid='".$_POST['callid']."'");
        if($_POST['etat']==6)
            $DB_site2->query("INSERT INTO causeperte SET userid=".$projet->userid.", date=".time().", commentaire='PJT".$projet->projetid."', cause=".intval($_POST['cause']));

        //Insère le suivi externe dans le projet :
        $DB_site2->query("UPDATE projet SET suivi_externe = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['suivi_externe'])."' WHERE projetid = ".$projet->projetid);
        if($_POST['suivi_externe']!=='' && $_POST['pour'] == 'externe'){
            $DB_site2->query("INSERT INTO suivi_client SET userid='".$_POST['userid']."', commercial='$user_info[username]', date=".$time.", suivi='Consignes pour suivi externe : ".mysqli_real_escape_string($DB_site2->link_id,$_POST['suivi_externe'])."', pour='externe', projetid='".$projet->projetid."', callid='".$_POST['callid']."'");
        }
        
       header("location:projet.php?projetid=".$projet->projetid);
        break;
        
        case "prop":
        $caractvalids=array();
        foreach(preg_grep("/^caract_\d+$/",array_keys($_POST)) as $c)
            $caractvalids[]=$_POST[$c];
        $projet->addprop($_POST['prop'],$_POST['propqte'],$caractvalids);
        break;
        
        case "rmprop":
        $projet->rmprop($_POST['prop']);
        break;
        
        case "devis":
        $projet->adddevis($_POST['facturid']);
        break;
        
        case "rmdevis":
        $projet->rmdevis($_POST['facturid']);
        break;

        case "config":
        $projet->addconfig($_POST['configid']);
        break;
        
        case "rmconfig":
        $projet->rmconfig($_POST['configid']);
        break;

        case "compare":
        $projet->addcompare($_POST['artid']);
        break;
        
        case "rmcompare":
        $projet->rmcompare($_POST['artid']);
        break;

        case "artvu":
        $projet->addartvu($_POST['artid']);

        break;
        
        case "rmartvu":
        $projet->rmartvu($_POST['artid']);
        break;
    }
}

$clientprojet=null;
$utilisateurs="";
$fournisseurs="";
$users=$projet->users();
$maxtdnb=3;
$tdwidth=floor(100/min(count($users),$maxtdnb));
$i=0;
$filtrecontact="<div class=\"suivifilter\">
        <label><input type=\"checkbox\" onclick=\"checkall(this)\" checked> Contacts</label>";

$divfactures=$divconfigs=$divcompares=$divartvu=$tableartvu=$tablefactures=$tableconfigs=$tablecompares="";

foreach($users as $user){

    $i++;
    $fonction=$fonction_infos=$swap="";
    if($user->userid==$projet->userid){
        $fonction="Client";
        $clientprojet=$user;
    }else{
        $relation=$DB_site2->query_first("SELECT * FROM utilisateur_relation WHERE user2id=".intval($projet->userid)." AND user1id=".intval($user->userid));
        if($relation!=""){
            if($relation['relation_genre']==2)
                $fonction="Architecte";
            $fonction_infos=$relation['relation_description']."<br>";
        }
        $swap="<div class=\"editcontact\" onclick=\"this.classList.toggle('open');\">
            <a href=\"javascript:swaprelation(".$user->userid.")\"><i class=\"fa fa-arrows-h\" aria-hidden=\"true\"></i> Passer en client</a>
            <a href=\"javascript:rmrelation(".$user->userid.")\"><i class=\"fa fa-user-times\" aria-hidden=\"true\"></i> Supprimer du projet</a>
        </div>";
    }

    $telephones="";
    if($user->telephone!="") $telephones.="<span class=\"suivilink\" onclick=\"show_choix_suivi(this,'ficheinfo')\" style=\"color:blue;\"><img src=\"images/ring_phone_2.gif\"/>".$user->telephone."</span>
    <span class=\"addsuivi\">
    <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>
    <span class=\"suivilink\" onclick=\"suiviauto('tel_out','client',".$user->userid.",'Appel téléphone ".addslashes($user->nom." ".$user->prenom)."',undefined);epigy_call('".$user->telephone."','".($user->langue=='FR'?$user_info['telFR']:$user_info['telEN'])."');\">Click to call</span>
        <span class=\"suivilink tel_in\" onclick=\"suiviauto('tel_in','client',".$user->userid.",'Appel téléphone ".addslashes($user->nom." ".$user->prenom)."',undefined)\">Appel reçu</span>
        <span class=\"suivilink tel_out\" onclick=\"suiviauto('tel_out','client',".$user->userid.",'Appel téléphone ".addslashes($user->nom." ".$user->prenom)."',undefined)\">Appel effectué</span>
        <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span>
    </span>";
    if($user->portable!="") $telephones.="<span class=\"suivilink\" onclick=\"show_choix_suivi(this,'ficheinfo')\" style=\"color:blue;\"><img src=\"images/ring_phone_2.gif\"/>".$user->portable."</span>
    <span class=\"addsuivi\">
    <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>
    <span class=\"suivilink\" onclick=\"suiviauto('tel_out','client',".$user->userid.",'Appel téléphone ".addslashes($user->nom." ".$user->prenom)."',undefined);epigy_call('".$user->portable."','".($user->langue=='FR'?$user_info['telFR']:$user_info['telEN'])."');\">Click to call</span>
    <span class=\"suivilink tel_in\" onclick=\"suiviauto('tel_in','client',".$user->userid.",'Appel téléphone ".addslashes($user->nom." ".$user->prenom)."',undefined)\">Appel reçu</span>
    <span class=\"suivilink tel_out\" onclick=\"suiviauto('tel_out','client',".$user->userid.",'Appel téléphone ".addslashes($user->nom." ".$user->prenom)."',undefined)\">Appel effectué</span>
    <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span>
    </span>";
    
    $email="";
    if($user->mail!="") $email="<span class=\"suivilink mail_out\" onclick=\"show_choix_suivi(this,'ficheinfo')\">".$user->mail."</span>
    <span class=\"addsuivi\">
    <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>
    <span class=\"suivilink\" onclick=\"suiviauto('mail_out','client',".$user->userid.");mail_suivi('suivi_client',".$user->userid.");document.getElementById('dest').value='".$user->mail."';\">Ecrire email</span>
    <a href=\"mailto:".$user->mail."?subject=".txt_tracking("PJT",$projet->projetid,null)."\" class=\"suivilink mail_out\">Ecrire avec Outlook</a>
    <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span>
    </span>";

    $conditionsclientpart="";
    $conditionsclientpro="";
    $editfournisseurprojet="";
    $editcontactsutilisateur="";
    $contactsutilisateur="";
    $contacts=$user->contacts();

    if(count($contacts)>0){
        $contactcount=0;
        foreach($contacts as $contact){
            $checked="";
            if(in_array($contact->userid,$projet->contactids)){
                $contactcount++;
                $checked=" checked";
                
                $contactsutilisateur.="<div>
                <b>".$contact->nom." ".$contact->prenom."</b>";
                if($contact->fonction!="") $contactsutilisateur.=" (".$contact->fonction.")".($contact->ancien=='0'?' - ancien contact':'');
                if($contact->telephone!=""){
                    $contactsutilisateur.="<br>
                    <span class=\"suivilink\" onclick=\"show_choix_suivi(this,'ficheinfo')\" style=\"color:blue;\"><img src=\"images/ring_phone_2.gif\"/>".$contact->telephone."</span>
                    <span class=\"addsuivi\">
                    <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>
                    <span class=\"suivilink\" onclick=\"suiviauto('tel_out','assoc',".$contact->userid.",'Appel téléphone client',undefined);epigy_call('".$contact->telephone."','".($contact->langue=='FR'?$user_info['telFR']:$user_info['telEN'])."');\">Click to call</span>
                    <span class=\"suivilink tel_in\" onclick=\"suiviauto('tel_in','assoc',".$contact->userid.",'Appel téléphone ".addslashes($contact->nom." ".$contact->prenom)."',undefined)\">Appel reçu</span>
                    <span class=\"suivilink tel_out\" onclick=\"suiviauto('tel_out','assoc',".$contact->userid.",'Appel téléphone ".addslashes($contact->nom." ".$contact->prenom)."',undefined)\">Appel effectué</span>
                    <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span>
                    </span>";
                }
                if($contact->portable!=""){
                    $contactsutilisateur.="<br><span class=\"suivilink\" onclick=\"show_choix_suivi(this,'ficheinfo')\" style=\"color:blue;\"><img src=\"images/ring_phone_2.gif\"/>".$contact->portable."</span>
                    <span class=\"addsuivi\">
                    <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>
                    <span class=\"suivilink\" onclick=\"suiviauto('tel_out','assoc',".$contact->userid.",'Appel téléphone client',undefined);epigy_call('".$contact->portable."','".($contact->langue=='FR'?$user_info['telFR']:$user_info['telEN'])."');\">Click to call</span>
                    <span class=\"suivilink tel_in\" onclick=\"suiviauto('tel_in','assoc',".$contact->userid.",'Appel téléphone ".addslashes($contact->nom." ".$contact->prenom)."',undefined)\">Appel reçu</span>
                    <span class=\"suivilink tel_out\" onclick=\"suiviauto('tel_out','assoc',".$contact->userid.",'Appel téléphone ".addslashes($contact->nom." ".$contact->prenom)."',undefined)\">Appel effectué</span>
                    <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span>
                    </span>";
                }
                if($contact->mail!="")
                    $contactsutilisateur.="<span class=\"suivilink mail_out\" onclick=\"show_choix_suivi(this,'ficheinfo')\">".$contact->mail."</span>
                <span class=\"addsuivi\">
                <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>
                <span class=\"suivilink\" onclick=\"suiviauto('mail_out','assoc',".$contact->userid.");mail_suivi('suivi_client',".$contact->userid.");document.getElementById('dest').value='".$contact->mail."';\">Ecrire email</span>
                <a href=\"mailto:".$contact->mail."?subject=".txt_tracking("PJT",$projet->projetid,null)."\" class=\"suivilink mail_out\">Ecrire avec Outlook</a>
                <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span>
                </span>";
                
                $contactsutilisateur.="</div>";
            }
            $editcontactsutilisateur.="<label><input type=\"checkbox\" name=\"projet_contact[]\" value=\"".$contact->userid."\" ".$checked."> ".$contact->nom." ".$contact->prenom;
            if($contact->fonction!="") $editcontactsutilisateur.=" (".$contact->fonction.")".($contact->ancien=='0'?' - ancien contact':'');
            $editcontactsutilisateur.="</label>";
        }
        $editcontactsutilisateur="<div class=\"contacts\">
            <span onclick=\"this.parentNode.classList.toggle('open')\">Contacts liés au projet (".$contactcount.")</span>
            <div>
            <form method=\"post\">
            <input type=\"hidden\" name=\"action\" value=\"contacts\">
                ".$editcontactsutilisateur."
                <input type=\"submit\" value=\"OK\">
                </form>
            </div>
        </div>";
        
    }
//Si l'utilisateur est pro, on affiche Conditions client pro sous Contacts
    if($user->raisonsociale!=""){
            $conditionsclientpro="<div class=\"contacts\">
            <span onclick=\"this.parentNode.classList.toggle('open')\">Conditions client pro</span>
            <div>
                <strong>Remise</strong> : ".$user->pourcentage_remise."
                <br><strong>Conditions de règlements</strong> : ".$user->conditions_reglement."
                <br><strong>Encours client HT</strong> : ".$user->encours_financier."
                <br><strong>Garantie</strong> : ".$user->garantie."
                <br><strong>Livraison commande</strong> : ".$user->utilisateur_livraison."
                <br><strong>Franco de port</strong> : ".$user->francodeport."
            </div>
        </div>";  
        }else{ //L'utilisateur est part, on affiche Conditions client part sous Contacts
            $conditionsclientpart="<div class=\"contacts\">
            <span onclick=\"this.parentNode.classList.toggle('open')\">Conditions client part</span>
            <div>
                <strong>Remise</strong> : ".$user->pourcentage_remise."
                <br><strong>Conditions de règlements</strong> : ".$user->conditions_reglement."
            </div>
        </div>";  
        }
          
    $factures=$user->devis();
    $nb_devis=0;
    foreach($factures as $facture){
        //Permet d'enlever les devis liés pour le compteur, sinon ça va afficher l'entête du tableau :
        if(in_array($facture->facturid,$projet->devis)){
            $nb_devis++;
        }
    }
    if(count($factures) - $nb_devis > 0){
        $divfactures.="<p>".trim($user->raisonsociale." ".$user->nom." ".$user->prenom)."</p>";
        $divfactures.="
        <table class=\"table_alternate_color\">
            <tr>
                <th class=\"entete tri_tableau\"><b>N° Fac</b></td>
                <th class=\"entete tri_tableau\"><b>Date</b></td>
                <th class=\"entete tri_tableau\"><b>Montant</b></td>
                <th class=\"entete tri_tableau\"><b>Moyen paiement</b></td>
                <th class=\"entete tri_tableau\"><b>Etat</b></td>
                <th class=\"entete tri_tableau\"><b>Cial attribué</b></td>
                <th class=\"entete tri_tableau\"><b>ACTIONS</b></td>              
            </tr>";

        foreach($factures as $facture){
            if(!in_array($facture->facturid,$projet->devis)){
                if($user->raisonsociale==""){
                    $total=number_format($facture->totalttc,2,","," ")." €TTC";
                }else{
                    $total=number_format($facture->totalht,2,","," ")." €HT";
                }

                $divfactures.="
                <tr>
                    <td style=\"font-size:10px;\">".$facture->facturid."</td>
                    <td style=\"font-size:10px;\">".date("d/m/Y",$facture->datecommande)."</td>
                    <td style=\"font-size:10px;\">".$total."</td>
                    <td style=\"font-size:10px;\">".$facture->getMoyen($facture->moyenid)."</td>
                    <td style=\"font-size:10px;\">".$facture->validetxt."</td>
                    <td style=\"font-size:10px;\">".$facture->commercial."</td>
                    <td style=\"font-size:10px;\"><a href=\"javascript:adddevis(".$facture->facturid.")\">Ajouter dans le projet</a></td>
                </tr>
                <tr><td colspan=\"7\" style=\"font-size:10px;font-style:italic;color:#5F718A\">";

                foreach($facture->lignes as $ligne){
                    $divfactures.=" - ".$ligne->artcode." (".$ligne->ref.")";
                }
                $divfactures.="</td></tr>";
            }
        }
        $divfactures.="</table>";
    }


    $sql = "SELECT artid,date";
    $sql .= " FROM comparateur";
    $sql .= "  WHERE userid = ".$user->userid;
    $sql .= "  AND remove = 0";
    $sql .= "  GROUP BY artid";
    $sql .= "  ORDER BY date";
    $comparateur = $DB_site2->query($sql);

    if($DB_site2->num_rows($comparateur) > 0){
        foreach($comparateur as $artid){
            $vignettes="";
                $art=$DB_site->query_first("SELECT * FROM article a INNER JOIN article_marque am ON am.artid = a.artid WHERE a.artid =".$artid['artid']);
                $prixcaract = array(
                    'achat' => 0,
                    'prixht' => 0,
                    'prix' => 0
                );
                $sqlcaract = "SELECT differenceprix FROM caracteristiquevaleur AS cv INNER JOIN `article_caractval` USING(caractvalid) WHERE caractvalid=(SELECT caractvalid FROM caracteristiquevaleur INNER JOIN `article_caractval` USING(caractvalid) WHERE artid=".$artid['artid']." AND caractid=cv.caractid ORDER BY position LIMIT 1) AND artid=".$artid['artid'];
                    $caracts = $DB_site->query($sqlcaract);
                    while($caract = $DB_site->fetch_array($caracts)){
                        $differenceprix=unserialize($caract['differenceprix']);
                        $prixcaract['prix'] += $differenceprix['base'];
                    }
                    $prixtotal = $art['prix'] + $prixcaract['prix'];
                    $vignettes.="<div class=\"vignettes_histo_config\" >";
                    if(file_exists($_SERVER['DOCUMENT_ROOT']."/configurations/www.mon-espace-cuisson.com/images/marques/br/".$art['marqueid'].".jpg")) {
                        $vignettes.="<img src=\"/configurations/www.mon-espace-cuisson.com/images/marques/br/".$art['marqueid'].".jpg\" height=\"18\" style=\"margin-bottom:3px;\"><br>";
                    }
                    $vignettes.="<a href=\"ajout_produit.php?artid=".$artid['artid']."&prec=modif\" target=\"_blank\"><img src=\"/configurations/www.mon-espace-cuisson.com/images/produits/br/".$artid['artid'].".jpg\" style=\"max-height: 50px; max-width: 50px;\" height=\"50\"></a>
                    <p>".$art['artcode'].
                    "</p><p class=\"pt\">(".(strlen($art['artcode_eurocave']) <= 10 ? $art['artcode_eurocave'] : substr($art['artcode_eurocave'],0,10)."...").")</p>
                    <table><tr><td class=\"td_intitule1\">PPI TTC</td><td class=\"td_alt1\">".number_format($art['prixconseilleht'] * $tvanormal,2,',',' ')."&euro;"."</td></tr>
                    <tr><td class=\"td_intitule2\">PV TTC</td><td class=\"td_alt2\">".number_format($prixtotal,2,',',' ')."&euro;"."</td></tr></table>
                    </div>";
                
                $divcompares.="
                <p><a href=\"javascript:addcompare('".$artid['artid']."')\" style=\"display:inline;\">Le ".date('d/m/Y H:i',$artid['date'])."</a> <img src=\"images/plus.jpg\" id=\"compareplus".$artid['artid']."\" align=\"absmiddle\" onclick=\"show_compare_info(".$artid['artid'].")\" style=\"width:25px;\"></p><div id=\"compareinfo".$artid['artid']."\" style=\"display:none;\">".$vignettes."</div>";
    }
    }

    $sql = "SELECT max(date) as maxdate, av.* FROM `article_vu` av WHERE `userid` = ".$user->userid." GROUP BY artid ORDER BY date DESC";
    $articlesvus = $DB_site2->query($sql);

    if($DB_site2->num_rows($articlesvus) > 0){
        foreach($articlesvus as $articlevu){
            $vignettes="";
                $art=$DB_site->query_first("SELECT * FROM article a INNER JOIN article_marque am ON am.artid = a.artid WHERE a.artid =".$articlevu['artid']);
                $prixcaract = array(
                    'achat' => 0,
                    'prixht' => 0,
                    'prix' => 0
                );
                $sqlcaract = "SELECT differenceprix FROM caracteristiquevaleur AS cv INNER JOIN `article_caractval` USING(caractvalid) WHERE caractvalid=(SELECT caractvalid FROM caracteristiquevaleur INNER JOIN `article_caractval` USING(caractvalid) WHERE artid=".$articlevu['artid']." AND caractid=cv.caractid ORDER BY position LIMIT 1) AND artid=".$articlevu['artid'];
                    $caracts = $DB_site->query($sqlcaract);
                    while($caract = $DB_site->fetch_array($caracts)){
                        $differenceprix=unserialize($caract['differenceprix']);
                        $prixcaract['prix'] += $differenceprix['base'];
                    }
                    $prixtotal = $art['prix'] + $prixcaract['prix'];
                    $vignettes.="<div class=\"vignettes_histo_config\" style=\"width:140px;\">";
                    if(file_exists($_SERVER['DOCUMENT_ROOT']."/configurations/www.mon-espace-cuisson.com/images/marques/br/".$art['marqueid'].".jpg")) {
                        $vignettes.="<img src=\"/configurations/www.mon-espace-cuisson.com/images/marques/br/".$art['marqueid'].".jpg\" height=\"18\" style=\"margin-bottom:3px;\"><br>";
                    }
                    $vignettes.="<a href=\"ajout_produit.php?artid=".$articlevu['artid']."&prec=modif\" target=\"_blank\"><img src=\"/configurations/www.mon-espace-cuisson.com/images/produits/br/".$articlevu['artid'].".jpg\" style=\"max-height: 50px; max-width: 50px;\" height=\"50\"></a>
                    <p>".$art['artcode'].
                    "</p><p class=\"pt\">(".(strlen($art['artcode_eurocave']) <= 10 ? $art['artcode_eurocave'] : substr($art['artcode_eurocave'],0,10)."...").")</p>
                    <table><tr><td class=\"td_intitule1\">PPI TTC</td><td class=\"td_alt1\">".number_format($art['prixconseilleht'] * $tvanormal,2,',',' ')."&euro;"."</td></tr>
                    <tr><td class=\"td_intitule2\">PV TTC</td><td class=\"td_alt2\">".number_format($prixtotal,2,',',' ')."&euro;"."</td></tr></table>
                    </div>";
        $divartvu.="
                <div id=\"artvuinfo".$articlevu['artid']."\"><p><a href=\"javascript:addartvu('".$articlevu['artid']."')\" style=\"display:inline;\">Le ".date('d/m/Y H:i',$articlevu['date'])."</a></p>".$vignettes."</div>";
    }
}

    $sql = "SELECT configurateurid";
    $sql .= " FROM configurateur";
    $sql .= " WHERE userid = ".$user->userid;
    $sql .= " ORDER BY date DESC";
    $configurateurs = $DB_site2->query($sql);

    if($DB_site2->num_rows($configurateurs) > 0){
        while($configurateurid = $DB_site2->fetch_array($configurateurs)){
          $configurateur = new configurateur($DB_site2,$configurateurid[0]);
          $divconfigs.="<div style=\"white-space:nowrap\"><a href=\"javascript:addconfig('".$configurateur->configurateurid."')\" style=\"display:inline;\">".$configurateur->configurateurid ." le ".date("d/m/Y H:i", $configurateur->date)."</a><img src=\"images/plus.jpg\" id=\"configplus".$configurateur->configurateurid."\" align=\"absmiddle\" onclick=\"show_config_info(".$configurateur->configurateurid.")\" style=\"width:25px;\"></div>";
          $div2configs.="<table id=\"configinfo".$configurateur->configurateurid."\" style=\"display:none;\"><tr><td>";
          $rep="";
          $vignettes="";
          foreach($configurateur->reponses() as $nb => $reponse){
                $infos = $reponse->infos();
                if($infos!=""){
                    $rep.="<b>".$reponse->type . " :</b> " . $infos."<br>";
                }
            }
            if(is_array($configurateur->artids) && count($configurateur->artids) > 0){
                foreach($configurateur->artids as $configartid){
                    if(file_exists($_SERVER['DOCUMENT_ROOT']."/configurations/www.mon-espace-cuisson.com/images/produits/br/".$configartid.".jpg")){
                        $art=$DB_site->query_first("SELECT * FROM article a INNER JOIN article_marque am ON am.artid = a.artid WHERE a.artid =".$configartid);
                        $prixcaract = array(
                            'achat' => 0,
                            'prixht' => 0,
                            'prix' => 0
                        );
                        $sqlcaract = "SELECT differenceprix FROM caracteristiquevaleur AS cv INNER JOIN `article_caractval` USING(caractvalid) WHERE caractvalid=(SELECT caractvalid FROM caracteristiquevaleur INNER JOIN `article_caractval` USING(caractvalid) WHERE artid=".$configartid." AND caractid=cv.caractid ORDER BY position LIMIT 1) AND artid=".$configartid;
                            $caracts = $DB_site->query($sqlcaract);
                            while($caract = $DB_site->fetch_array($caracts)){
                                $differenceprix=unserialize($caract['differenceprix']);
                                $prixcaract['prix'] += $differenceprix['base'];
                            }
                            $prixtotal = $art['prix'] + $prixcaract['prix'];
                            $vignettes.="<div class=\"vignettes_histo_config\" >";
                            if(file_exists($_SERVER['DOCUMENT_ROOT']."/configurations/www.mon-espace-cuisson.com/images/marques/br/".$art['marqueid'].".jpg")) {
                                $vignettes.="<img src=\"/configurations/www.mon-espace-cuisson.com/images/marques/br/".$art['marqueid'].".jpg\" height=\"18\" style=\"margin-bottom:3px;\"><br>";
                            }
                            $vignettes.="<a href=\"ajout_produit.php?artid=".$configartid."&prec=modif\" target=\"_blank\"><img src=\"/configurations/www.mon-espace-cuisson.com/images/produits/br/".$configartid.".jpg\" style=\"max-height: 50px; max-width: 50px;\" height=\"50\"></a>
                            <p>".$art['artcode'].
                            "</p><p class=\"pt\">(".(strlen($art['artcode_eurocave']) <= 10 ? $art['artcode_eurocave'] : substr($art['artcode_eurocave'],0,10)."...").")</p>
                            <table><tr><td class=\"td_intitule1\">PPI TTC</td><td class=\"td_alt1\">".number_format($art['prixconseilleht'] * $tvanormal,2,',',' ')."&euro;"."</td></tr>
                            <tr><td class=\"td_intitule2\">PV TTC</td><td class=\"td_alt2\">".number_format($prixtotal,2,',',' ')."&euro;"."</td></tr></table>
                            </div>";
                        }
                        else {
                            $vignettes.="";
                        }
                    }
                }
        $div2configs.="
                <tr><td style='padding:5px;'>Config n° ".$configurateur->configurateurid." par ".$user->nom." ".$user->prenom."<br>".$rep."</td><td colspan='3'><div style='overflow-x:auto;white-space: nowrap;background-color:#EBEBEB;width:400px;'>".$vignettes."</div></td></tr></table>";
      }
  }

    
    $raisonsociale="";
    if($user->raisonsociale!="")
        $raisonsociale=<<<HTML
            <br><b><a href="utilisateur.php?action=editer&userid=$user->userid">$user->raisonsociale</a></b>
            <a href="https://www.google.com/search?q=$user->raisonsociale" target="_blank"><img src="images/icone_google.jpg" title="Google" align="absmiddle"></a>
            <a href="http://www.societe.com/cgi-bin/mainsrch?champ=$user->raisonsociale" target="_blank"><img src="images/societe.com.gif" title="societe.com" align="absmiddle" width="16"></a>
            <a href="http://www.118000.fr/search?sb_Professionnel1=sb_professionnel&sb_quiquoi=$user->raisonsociale&sb_Ou=$user->codepostal" target="_blank"><img src="images/pj.ico" title="Annaire" align="absmiddle" width="16"></a>
HTML;
    if($user->tvaintracom!="")
        $raisonsociale.="<br>TVA Intra : ".$user->tvaintracom;
        
    $profession=!empty($user->profession) ? "<br>Profession : ".$user->profession : "";
    $usericon=usericon($user->vip, 50);
    $qmaps=urlencode($user->adresse)."+".$user->codepostal."+".urlencode($user->ville);
    $date_inscrip = $user->dateinscription;
    $date_inscrip = date( "d/m/Y", $date_inscrip);

    $utilisateurs.=<<<HTML
    <td class="projet_contact" width="$tdwidth%">
    <div>
        $swap
        <span class="title_infos">N°$user->userid $fonction</span> <img src="images/drapeaux/$user->langue.gif" width="20">
        <a href="clientnavig.php?userid=$user->userid" class="connexion_compte" target="_blank"></a>
        <br>
        $fonction_infos
        $usericon <b><a href="utilisateur.php?action=editer&userid=$user->userid" >$user->nom $user->prenom</a></b>
        <a href="https://www.google.com/search?q=$user->nom $user->prenom" target="_blank"><img src="images/icone_google.jpg" title="Google" align="absmiddle" style="margin: 0 2px;"></a> 
        <a href="http://www.societe.com/cgi-bin/liste?dirig=$user->nom&pre=$user->prenom" target="_blank"><img src="images/societe.com.gif" align="absmiddle"  title="Societe.com" style="margin: 0 2px;" width="16"></a>
        <a href="http://www.118000.fr/search?sb_Professionnel1=sb_Particulier1&sb_quiquoi=$user->nom $user->prenom&sb_Ou=$user->codepostal" target="_blank"><img src="images/pj.ico" title="Annuaire" align="absmiddle" style="margin: 0 2px;" width="16"></a>
        $raisonsociale
        <br>$user->adresse, $user->codepostal, $user->ville <a href="http://maps.google.com/maps?f=q&source=s_q&hl=fr&geocode=&q=$qmaps" target="_blank"><img src="images/localiser.gif" width="20" align="absmiddle" title="Localiser" style="margin: 0 2px;"></a>
        <br>$user->pays - heure locale: <a href="https://www.google.com/search?q=heure+local+$user->pays" target="_blank"> google</a>
        $profession
        <br>$telephones
        <br>$email
        <br>Connu le site par: $user->connu
        <br>Site web: $user->site
        <br>Inscrit par: $user->provenance, le $date_inscrip
    </div>
    $editcontactsutilisateur
    $contactsutilisateur 
    $conditionsclientpro
    $conditionsclientpart     
    </td>
HTML;

    $filtrecontact.="<label><input type=\"checkbox\" name=\"filtredest\" value=\"".$user->userid."\" onclick=\"filtersuivi()\" checked> ".$user->nom." ".$user->prenom."</label>";

    if($i%$maxtdnb==0)
        $utilisateurs.="</tr><tr>";

}//fin boucle client

$fournisseurs_relations_projets=$DB_site2->query("SELECT * FROM projet_relation_fournisseur  WHERE projetid='".$projet->projetid."'");
//Permet de redimensionner les div contenant les fournisseurs
$nombrefourn=$DB_site2->num_rows($fournisseurs_relations_projets);
$maxtdnb=3;
$tdwidth=100;
if($nombrefourn > 0){
    $tdwidth=floor(100/min($nombrefourn,$maxtdnb));
}

//Boucle qui reccupère les sociétés cochées dans "ajouter des fournisseurs"
while($fournisseur_relation_projet=$DB_site2->fetch_array($fournisseurs_relations_projets)){
    //Compteur des contacts fournisseurs
    $cptcontact=0;
    foreach($projet->fournisseurs as $idfourn){
        $idcontact=$DB_site2->query("SELECT id from fournisseur WHERE id=".$idfourn." AND societe='".addslashes($fournisseur_relation_projet['societe'])."' ");
        if($DB_site2->num_rows($idcontact)>0){
            $cptcontact++;
        }
    }

    //Récupère l'adresse de la société
    $adresse_fourn=$DB_site2->query_first("SELECT adresse1, adresse2, cp, ville, libelle from fournisseur INNER JOIN pays ON pays.paysid = fournisseur.paysid WHERE societe='".addslashes($fournisseur_relation_projet['societe'])."' AND type ='facture' AND adresse1 != ''");
    $adresse2 = ($adresse_fourn['adresse2'] != '' ? ' '.$adresse_fourn['adresse2'].',' : '');
    $qmaps=urlencode($adresse_fourn['adresse1'])."+".$adresse_fourn['cp']."+".urlencode($adresse_fourn['ville']);

    $fournisseurs.=<<<HTML
    <td class="projet_contact" width="$tdwidth%">
        <div>
            <span class="title_infos">$fournisseur_relation_projet[societe] Fournisseur</span>
            <img src="images/icon/cancel.png" alt="" style="width:15px; height:15px; float:right; cursor:pointer;" onclick="rmrelationfourn($fournisseur_relation_projet[relationid])">
            <br>
            $adresse_fourn[adresse1],$adresse2 $adresse_fourn[cp], $adresse_fourn[ville] <a href="http://maps.google.com/maps?f=q&source=s_q&hl=fr&geocode=&q=$qmaps" target="_blank"><img src="images/localiser.gif" width="20" align="absmiddle" title="Localiser" style="margin: 0 2px;"></a>
            <br>
            $adresse_fourn[libelle]
        </div>
    <div class="contacts">
        <span onclick="this.parentNode.classList.toggle('open')">Contacts ($cptcontact)</span>
        <div style="max-height:200px; overflow-y: auto;">       
HTML;
            $fournisseurs_relations=$DB_site2->query("SELECT * FROM projet_relation_fournisseur prf INNER JOIN fournisseur f ON prf.societe = f.societe WHERE projetid='".$projet->projetid."' AND f.type!='old' AND prf.societe='".addslashes($fournisseur_relation_projet[societe])."'");
            $affichcontactfourn="";
            //Boucle qui reccupère tous les contacts de la société cochée
            while($fournisseur_relation=$DB_site2->fetch_array($fournisseurs_relations)){
                $fournisseurs.="
                <p><input type=\"checkbox\" name=\"contacts_fourn[]\" value=\"".$fournisseur_relation['id']."\" ".(in_array($fournisseur_relation['id'], $projet->fournisseurs) ? 'checked' : '')." style=\"vertical-align:sub;\"> ".$fournisseur_relation['prenom']." ".$fournisseur_relation['nom']." (".$fournisseur_relation['fonction'].") - ".$fournisseur_relation['mail']."
                 </p>"; 
                if(in_array($fournisseur_relation['id'],$projet->fournisseurs)){
                       if($fournisseur_relation['telephone']!=""){
                           $telephone="<span class=\"suivilink\" onclick=\"show_choix_suivi(this,'ficheinfo')\" style=\"color:blue;\"><img src=\"images/ring_phone_2.gif\"/>".$fournisseur_relation['telephone']."</span>
                           <span class=\"addsuivi\">
                           <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>
                           <span class=\"suivilink\" onclick=\"suiviauto('tel_out','pro',".$fournisseur_relation['id'].",'Appel téléphone ".$fournisseur_relation['societe']." (".addslashes($fournisseur_relation['nom']." ".$fournisseur_relation['prenom']).")',undefined);epigy_call('".$fournisseur_relation['telephone']."','".$user_info['telFR']."');\">Click to call</span>
                           <span class=\"suivilink tel_in\" onclick=\"suiviauto('tel_in','pro',".$fournisseur_relation['id'].",'Appel téléphone ".$fournisseur_relation['societe']." (".addslashes($fournisseur_relation['nom']." ".$fournisseur_relation['prenom']).")',undefined)\">Appel reçu</span>
                           <span class=\"suivilink tel_out\" onclick=\"suiviauto('tel_out','pro',".$fournisseur_relation['id'].", 'Appel téléphone ".$fournisseur_relation['societe']." (".addslashes($fournisseur_relation['nom']." ".$fournisseur_relation['prenom']).")',undefined)\">Appel effectué</span>
                           <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span>
                           </span>";
                       }

                       if($fournisseur_relation['portable']!=""){
                           $portable="<span class=\"suivilink\" onclick=\"show_choix_suivi(this,'ficheinfo')\" style=\"color:blue;\"><img src=\"images/ring_phone_2.gif\"/>".$fournisseur_relation['portable']."</span>
                           <span class=\"addsuivi\">
                           <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>
                           <span class=\"suivilink\" onclick=\"suiviauto('tel_out','pro',".$fournisseur_relation['id'].",'Appel téléphone ".$fournisseur_relation['societe']." (".addslashes($fournisseur_relation['nom']." ".$fournisseur_relation['prenom']).")',undefined);epigy_call('".$fournisseur_relation['telephone']."','".$user_info['telFR']."');\">Click to call</span>
                           <span class=\"suivilink tel_in\" onclick=\"suiviauto('tel_in','pro',".$fournisseur_relation['id'].",'Appel téléphone ".$fournisseur_relation['societe']." (".addslashes($fournisseur_relation['nom']." ".$fournisseur_relation['prenom']).")',undefined)\">Appel reçu</span>
                           <span class=\"suivilink tel_out\" onclick=\"suiviauto('tel_out','pro',".$fournisseur_relation['id'].",'Appel téléphone ".$fournisseur_relation['societe']." (".addslashes($fournisseur_relation['nom']." ".$fournisseur_relation['prenom']).")',undefined)\">Appel effectué</span>
                           <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span>
                           </span>";
                       }

                       if($fournisseur_relation['mail']!=""){ 
                            $email="<span class=\"suivilink mail_out\" onclick=\"show_choix_suivi(this,'ficheinfo')\">".$fournisseur_relation['mail']."</span>
                            <span class=\"addsuivi\">
                            <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>
                            <span class=\"suivilink\" onclick=\"suiviauto('mail_out','pro',".$fournisseur_relation['id'].");mail_suivi('suivi_client',".$user->userid.");document.getElementById('dest').value='".$fournisseur_relation['mail']."';\">Ecrire email</span>
                            <a href=\"mailto:".$fournisseur_relation['mail']."?subject=".txt_tracking("PJT",$projet->projetid,null)."\" class=\"suivilink mail_out\">Ecrire avec Outlook</a>
                            <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span>
                            </span>";
                        }

                    $affichcontactfourn.="<div>
                       <b>".$fournisseur_relation['nom']." ".$fournisseur_relation['prenom']."</b> (".$fournisseur_relation['fonction'].")<br> ".$email." ".($fournisseur_relation['telephone'] != '' ?  '- '.$telephone : '')." ".($fournisseur_relation['portable'] != '' ?  '- '.$portable : '')."   </div>";    
                }
            }
            $fournisseurs.="  
        </div>
        <div><input type=\"submit\" value=\"OK\"></div>    
        </div>
    $affichcontactfourn
    </td>
";
}

$filtrecontact.="</div>";

if($divfactures=="") $divfactures="<p>Aucun devis à ajouter</p>";

$echeancecolor="";
$dateecheance="Inconnue";
$chkecheance="";
if(!is_null($projet->projetid)){
    $chkecheance="checked";
}
if(!is_null($projet->dateecheance)){
    $dateecheance=$projet->dateecheance->format("d/m/Y");
    $chkecheance="";
    if(!in_array($projet->etatid,$projets->clos))
        $echeancecolor=$projet->dateecheance<new datetime('today 0:0:0')?"red":"green";
}

$datecmd="-";
$datecmdcolor="";
$datecmd=$projet->datecmd();
if(!is_null($datecmd)){
    if(!in_array($projet->etatid,$projets->clos))
        $datecmdcolor=$datecmd<new datetime('today 0:0:0')?"red":"green";
    $datecmd=$datecmd->format("d/m/Y");
}

$optcommercial="";
$liste_commerciaux=$DB_site2->query("SELECT username FROM parametresgeneraux WHERE commissions='1' ORDER BY username ASC ");
while($liste_commercial=$DB_site2->fetch_array($liste_commerciaux)){
    if($liste_commercial['username']==$projet->commercial || (is_null($projet->commercial) && $liste_commercial['username']==$user_info['username'])){
        $selected="selected";
    } else {
        $selected="";
    }
    $optcommercial.="<option value=\"".$liste_commercial['username']."\" ".$selected.">".$liste_commercial['username']."</option>";
}

$optetat="";
$etatsuivimail="";
$etatsuivi="";
$etatjs="";
foreach($projet->etats as $etatid=>$libelle){
    if($etatid!=7){ ///pour l'instant on n'affiche pas les suivi externe
        //if($etatid!=6)  => commenté suite à la tâche SI#6303 : manque etat dans projet
        $optetat.="<input type=\"radio\" name=\"user_projet_etatid\" value=\"".$etatid."\"".($etatid==$projet->etatid?" checked":"")." id=\"etatid_".$etatid."\" required>
        <label for=\"etatid_".$etatid."\" style=\"color:".$projet->etatcolor[$etatid].";\" > ".$libelle."</label>";
        // $etatsuivi.="<label style=\"color:".$projet->etatcolor[$etatid].";\"><input type=\"radio\" name=\"etat\" value=\"".$etatid."\" ".($etatid==$projet->etatid?" checked":"")."> ".$libelle."</label>";
		$etatsuivi.="<label style=\"color:".$projet->etatcolor[$etatid].";\"><input type=\"radio\" name=\"etat\" value=\"".$etatid."\"> ".$libelle."</label>";
        $etatsuivimail.="<label style=\"color:".$projet->etatcolor[$etatid].";\" ><input type=\"radio\" name=\"mailetats\" ".($etatid==$projet->etatid?" checked":"")." onclick=\"document.querySelector('#suivimaildiv input[name=etat]').value=this.value;\" value=\"".$etatid."\"> ".$libelle."</label>";
    }
}

$prochainrappel="";
$optfacture="<option value=\"\">Aucune</option>";

if(!is_null($projet->userid)){
    $h1="Projet ".$projet->libelle;
    $projetuser=reset($projet->users("client"));
    $projet_infos_style="";
    $projet_edit_style="display:none;";
    $annule_style="";

    $usersids=$projet->relationids;
    $usersids[]=$projet->userid;
    $req=$DB_site2->query("SELECT facturid,heurefacture FROM facture WHERE userid IN (".implode(",",$usersids).") AND moyenid>0");
    while($facture=$DB_site2->fetch_array($req)){
        $optfacture.="<option value=\"".$facture['facturid']."\"".($facture['facturid']==$projet->facturid?" selected":"").">".$facture['facturid']." ".date("d/m/Y",$facture['heurefacture'])."</option>";
    }

    $relation_edit="";
    $projet_relations=$DB_site2->query("SELECT * FROM utilisateur_relation WHERE (user1id='".intval($projet->userid)."' OR user2id='".$projet->userid."') AND relation_type='utilisateur'");
    while($projet_relation=$DB_site2->fetch_array($projet_relations)){
        if($projet_relation['user1id']==$projet->userid){
            $userid_related=$projet_relation['user2id'];
        } else {
            $userid_related=$projet_relation['user1id'];
        }
        
        if(!in_array($userid_related,$projet->relationids)){
            $utilisateur=new utilisateur($DB_site2,$userid_related);
            $relation_edit.="<label><input type=\"checkbox\" name=\"newrelation[]\" value=\"".$userid_related."\"> ".trim($utilisateur->raisonsociale." ".$utilisateur->nom." ".$utilisateur->prenom)."</label>";
        }
    }
    
    $prop_av="";
    $c=0;
    $sql="SELECT artid,artcode,prix,active,libelleFR FROM article a INNER JOIN article_vu USING(artid) WHERE userid=".intval($projet->userid)." AND active='1'";
    //      if($archdate>$dateinscr) $sql.=" UNION SELECT * FROM article_vu_arch WHERE userid='".$_GET['userid']."'";
    $sql.=" ORDER BY date DESC LIMIT 20";
    $req=$DB_site2->query($sql);
    while($art=$DB_site2->fetch_array($req)){
        if($c==4){
            $c=0;
            $prop_av.="<div class=\"clear\"></div>";
        }
        $c++;
        $prop_av.="<div class=\"prop_vignette\">".vignette($art)."<a href=\"javascript:add_prop(".$art['artid'].",'".$art['artcode']."','".addslashes(str_replace("\"","'",$art['libelleFR']))."')\">Ajouter</a></div>";
    }
    if($DB_site2->num_rows($req)==0) $prop_av="Aucun article trouvé";

    $prop_prop="";
    $c=0;
    $propositions="";
    $width_prop=0;
    $utilisateur=array("userid"=>$clientprojet->userid,"paysid_livraison"=>$clientprojet->paysid);
    foreach($projet->propositions() as $p){
        $width_prop++;
        $prop=article_vignette($p['artid'],array("propid" => $p['id'], "caractvalid" => $p['caractvalid']), "propositions");
        
        if(isset($prop['options']) && !empty($prop['options'])){
            $options=<<<HTML
                <a href="javascript:void(0);" class="lien" onclick="affiche_div('options_$prop[propid]');">Afficher les options</a>
                <div id="options_$prop[propid]" class="caracteristiques">
                    <table cellpadding="0" cellspacing="0" border="0">
HTML;

            foreach($prop['options'] as $option){
                $options.=<<<HTML
                <tr>
                    <td class="td_intitule1 center"><strong>$option[caracteristique]</strong></td>
                </tr>
                <tr>
                    <td class="td_alt1 center">$option[libelle] $option[differenceprix] €</td>
                </tr>
HTML;
            }
            $options.="</table>
                </div>";
        } else {
            $options="<p class=\"pt rouge\">Sans option</p>";
        }
        
        $rows="";
        $i = 2;
        foreach($prop['rows'] as $key => $caract){
            $i=$i==2?1:2;
            
            $rows.=<<<HTML
                <tr>
                    <td class="td_intitule$i">$key</td>
                    <td class="td_alt$i center" nowrap>$caract</td>
                </tr>
HTML;
        }
        
        $propositions.=<<<HTML
        <div class="proposition" style="width:180px;">
            <form method="post">
                <input type="hidden" name="action" value="rmprop">
                <input type="hidden" name="prop" value="$prop[artid]">
                $prop[marque_visuel]
                $prop[img]
                <p style="">$prop[artcode_mcav]</p>
                <p class="pt">($prop[artcode_eurocave])</p>
                $options
                <div id="carac_$prop[propid]" class="caracteristiques" style="display:block;">
                    <table cellpadding="0" cellspacing="0" border="0">
                        $rows
                    </table>
                    <p class="pt">$prop[infos]</p>
                </div>
                <a class="lien lien_fiche" href="$prop[lien_fiche]" target="_blank">Sur site</a>
                $prop[dispo]
                $prop[files]
                $prop[lockit]
                <a onclick="if(confirm('Etes-vous sur de vouloir supprimer cet article des propositions ?')) this.closest('form').submit()" href="javascript:void(0);"><img src="images/icon/delete.png" class="del picto" title="Supprimer"></a>
                $prop[spy]
            </form>
        </div>
HTML;
        if($c==4){
            $c=0;
            $prop_prop.="<div class=\"clear\"></div>";
        }
        $c++;
        $prop_prop.="<div class=\"prop_vignette\">".vignette($prop)."<a href=\"javascript:add_prop(".$prop['artid'].",'".$prop['artcode']."','".addslashes(str_replace("\"","'",$art['libelleFR']))."')\">Ajouter</a></div>";
    }
    $prop_prop.="<div class=\"clear\"></div>";
    if($c==0) $prop_prop="Aucun article trouvé";

    $expadv = "";
     //Si albane, on lui laisse le choix de l'exp ADV
    if($user_info['username'] == 'albane'){
        $expadv = 'adv_albane';
    }
    $list_contact=$DB_site2->query("SELECT * FROM admin_contact WHERE username IN ('".$user_info['username']."','contact','".$expadv."') AND site_code='base'");
    while($contact=$DB_site2->fetch_array($list_contact)){
        $opt_exp.="<option value=\"".$contact['username']."\" data-mail=\"".$contact['mail'.$clientprojet->langue]."\"".($contact['username']==$user_info['username']?" checked":"").">".$contact['username']."</option>";
    }

    if(!is_null($projet->rappel)){
        $format="d/m/Y";
        $rappel=$projet->rappel;
        if($projet->rappel->format("G")>0 && $projet->rappel_type == 'rdv'){
            $format.=" H:i";
        }else
            $rappel->settime(19,0,0);
        $datetimestamp = new datetime('today 0:0:0');

        if(($rappel<new datetime() && $projet->rappel_type == 'rdv') || ($projet->rappel_type != 'rdv' && ($rappel->getTimestamp()+(24*60*60))<$datetimestamp->getTimestamp())){
            $prochainrappel="<div class=\"prochainrappel\" style=\"color:red;\"><b>Rappel en retard</b> :<br>".$projet->rappel->format($format)." par ".$projet->rappel_commercial."</div>";
        }
        else
            $prochainrappel="<div class=\"prochainrappel\">Prochain rappel :<br>".$projet->rappel->format($format)." par ".$projet->rappel_commercial."</div>";
    }else
        $prochainrappel="<div class=\"prochainrappel\" style=\"color:red;\"><b>Aucun rappel</b></div>";
        

    $listeconfigs=$projet->configs();
    $first_conf=0;
    foreach($listeconfigs as $listconfig){
        $configs=$DB_site->query_first("SELECT * FROM configurateur c INNER JOIN configurateur_reponse cr ON cr.configurateurid = c.configurateurid WHERE c.configurateurid = ".$listconfig);
        $rep="";
        $image="";
        $vignettes="";
        $configurateur = new configurateur($DB_site,$configs['configurateurid']);
        foreach($configurateur->reponses() as $nb => $reponse){
            $infos = $reponse->infos();
            if($infos!=""){
                $rep.="<b>".$reponse->type . " :</b> " . $infos."<br>";
            }
        }
        if(is_array($configurateur->artids) && count($configurateur->artids) > 0){
            foreach($configurateur->artids as $configartid){
                $configs=article_vignette($configartid,array( ),"configurateur" );
                          $vignettes.="<div class=\"vignettes_client_config\" style=\"width:180px;vertical-align:top;\">
                              ".$configs['marque_visuel']
                              .$configs['img']
                              ."<p style=''>".$configs['artcode_mcav']."</p>
                              <p class=\"pt\">(".$configs['artcode_eurocave'].")</p>";
                              if(isset($configs['options']) && !empty($configs['options'])){
                                   $vignettes.="<a href=\"javascript:void(0);\" class=\"lien\" onclick=\"affiche_div('options_".$configs['propid']."');\">Afficher les options</a>
                                   <div id=\"options_".$configs['propid']."\" class=\"caracteristiques\">
                                        <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
                                foreach($configs['options'] as $option){
                                    $vignettes.="<tr>
                                                       <td class=\"td_intitule1 center\"><strong>".$option['caracteristique']."</strong></td>
                                                  </tr>
                                                  <tr>
                                                       <td class=\"td_alt1 center\">".$option['libelle']." ".$option['differenceprix']." €</td>
                                                  </tr>";
                                            }
                                    $vignettes.="</table>
                                   </div>";
                              } else {
                                   $vignettes.="<p class=\"pt rouge\">Sans options</p>";
                              
                               }
                              $vignettes.="<div id=\"carac_".$configs['propid']."\" class=\"caracteristiques\" style=\"display:block;\">
                                   <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
                                        
                                        $i = 0;
                                        foreach($configs['rows'] as $key => $caract){
                                            $classtd1 = $i%2 == 0 ? 'td_intitule1' : 'td_intitule2';
                                            $classtd2 = $i%2 == 0 ? 'td_alt1' : 'td_alt2';
                                            
                                               $vignettes.="<tr>
                                                  <td class='".$classtd1."'>".$key."</td>
                                                  <td class='".$classtd2." center' nowrap>".$caract."</td>
                                               </tr>";
                                           
                                            $i++;
                                        }
                                    $vignettes.="
                                   </table>
                                   <p class=\"pt\">".$configs['infos']."</p>
                              </div>
                              <a class=\"lien lien_fiche\" href=\"".$configs['lien_fiche']."\" target=\"_blank\">Sur site</a>
                              ".$configs['dispo']
                              .$configs['files']
                              .$configs['lockit']
                              .$configs['suppr']
                              .$configs['spy']."
                    </div>";
                }
            }
        $tableconfigs.="
        <tr><td colspan=\"2\">Fait le ".date("d/m/Y à H:i",$configurateur->date)." <br>Config n° ".$listconfig." - <a href='javascript:rmconfig(".$listconfig.")'>enlever du projet</a> <img src=\"".($first_conf==0?'images/moins.jpg':'images/plus.jpg')."\" id=\"config2plus".$configurateur->configurateurid."\" align=\"absmiddle\" onclick=\"show_config2_info(".$configurateur->configurateurid.")\" style=\"width:25px;\"></td></tr>
                <tr style=\"display:".($first_conf==0?'block':'none')."\" id=\"config2info".$configurateur->configurateurid."\"><td style='padding:5px;'>".$rep."</td><td><div style='overflow-x:auto;white-space: nowrap;background-color:#EBEBEB;width:600px;'>".$vignettes."</div></td></tr>";
        $first_conf++;
    }  


    $listecompare=$projet->comparateur();
    if(count($listecompare)>0){
        $first_comp=0;
         foreach($listecompare as $artid){
            $vignettes="";
            $datecomp=$DB_site->query_first("SELECT date FROM comparateur c WHERE c.userid = ".$user->userid." AND artid=".$artid);
                $compare=article_vignette($artid,array( ),"configurateur" );
                          $vignettes.="<div class=\"vignettes_client_config\" style=\"width:180px;vertical-align:top;\">
                              ".$compare['marque_visuel']
                              .$compare['img']
                              ."<p style=''>".$compare['artcode_mcav']."</p>
                              <p class=\"pt\">(".$compare['artcode_eurocave'].")</p>";
                              if(isset($compare['options']) && !empty($compare['options'])){
                                   $vignettes.="<a href=\"javascript:void(0);\" class=\"lien\" onclick=\"affiche_div('options_".$compare['propid']."');\">Afficher les options</a>
                                   <div id=\"options_".$compare['propid']."\" class=\"caracteristiques\">
                                        <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
                                foreach($compare['options'] as $option){
                                    $vignettes.="<tr>
                                                       <td class=\"td_intitule1 center\"><strong>".$option['caracteristique']."</strong></td>
                                                  </tr>
                                                  <tr>
                                                       <td class=\"td_alt1 center\">".$option['libelle']." ".$option['differenceprix']." €</td>
                                                  </tr>";
                                            }
                                    $vignettes.="</table>
                                   </div>";
                              } else {
                                   $vignettes.="<p class=\"pt rouge\">Sans options</p>";
                              
                               }
                              $vignettes.="<div id=\"carac_".$compare['propid']."\" class=\"caracteristiques\" style=\"display:block;\">
                                   <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
                                        
                                        $i = 0;
                                        foreach($compare['rows'] as $key => $caract){
                                            $classtd1 = $i%2 == 0 ? 'td_intitule1' : 'td_intitule2';
                                            $classtd2 = $i%2 == 0 ? 'td_alt1' : 'td_alt2';
                                            
                                               $vignettes.="<tr>
                                                  <td class='".$classtd1."'>".$key."</td>
                                                  <td class='".$classtd2." center' nowrap>".$caract."</td>
                                               </tr>";
                                           
                                            $i++;
                                        }
                                    $vignettes.="
                                   </table>
                                   <p class=\"pt\">".$compare['infos']."</p>
                              </div>
                              <a class=\"lien lien_fiche\" href=\"".$compare['lien_fiche']."\" target=\"_blank\">Sur site</a>
                              ".$compare['dispo']
                              .$compare['files']
                              .$compare['lockit']
                              .$compare['suppr']
                              .$compare['spy']."
                    </div>";
                
                $tablecompares.="
                <tr><td style=\"width:300px\">Fait le ".date('d/m/Y',$datecomp['date'])." - <a href=\"javascript:rmcompare('".$artid."')\" >enlever du projet</a> <img src=\"".($first_comp==0?'images/moins.jpg':'images/plus.jpg')."\" id=\"compare2plus".$artid."\" align=\"absmiddle\" onclick=\"show_compare2_info(".$artid.")\" style=\"width:25px;\"></td><td><div id=\"compare2info".$artid."\" style=\"display:".($first_comp==0?'block':'none')."\">".$vignettes."</div></td></tr>";
                $first_comp++;
    }
    }


    $listeartvu=$projet->articles_vus();
    if(count($listeartvu)>0){
        $first_vu=0;
         foreach($listeartvu as $artid){
            $vignettes="";
            $datevu=$DB_site->query_first("SELECT max(date) as date FROM article_vu av WHERE av.userid = ".$user->userid." AND artid=".$artid);
                $vus=article_vignette($artid,array( ),"configurateur" );
                          $vignettes.=$vus['marque_visuel']
                              .$vus['img']
                              ."<p style=''>".$vus['artcode_mcav']."</p>
                              <p class=\"pt\">(".$vus['artcode_eurocave'].")</p>";
                              if(isset($vus['options']) && !empty($vus['options'])){
                                   $vignettes.="<a href=\"javascript:void(0);\" class=\"lien\" onclick=\"affiche_div('options_".$vus['propid']."');\">Afficher les options</a>
                                   <div id=\"options_".$compare['propid']."\" class=\"caracteristiques\">
                                        <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
                                foreach($vus['options'] as $option){
                                    $vignettes.="<tr>
                                                       <td class=\"td_intitule1 center\"><strong>".$option['caracteristique']."</strong></td>
                                                  </tr>
                                                  <tr>
                                                       <td class=\"td_alt1 center\">".$option['libelle']." ".$option['differenceprix']." €</td>
                                                  </tr>";
                                            }
                                    $vignettes.="</table>
                                   </div>";
                              } else {
                                   $vignettes.="<p class=\"pt rouge\">Sans options</p>";
                              
                               }
                              $vignettes.="<div id=\"carac_".$vus['propid']."\" class=\"caracteristiques\" style=\"display:block;\">
                                   <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
                                        
                                        $i = 0;
                                        foreach($vus['rows'] as $key => $caract){
                                            $classtd1 = $i%2 == 0 ? 'td_intitule1' : 'td_intitule2';
                                            $classtd2 = $i%2 == 0 ? 'td_alt1' : 'td_alt2';
                                            
                                               $vignettes.="<tr>
                                                  <td class='".$classtd1."'>".$key."</td>
                                                  <td class='".$classtd2." center' nowrap>".$caract."</td>
                                               </tr>";
                                           
                                            $i++;
                                        }
                                    $vignettes.="
                                   </table>
                                   <p class=\"pt\">".$vus['infos']."</p>
                              </div>
                              <a class=\"lien lien_fiche\" href=\"".$vus['lien_fiche']."\" target=\"_blank\">Sur site</a>
                              ".$vus['dispo']
                              .$vus['files']
                              .$vus['lockit']
                              .$vus['suppr']
                              .$vus['spy'];
                
                $tableartvu.="
               <div class=\"proposition\" style=\"width:200px;\"><span class=\"date_artvu\">Fait le ".date('d/m/Y',$datevu['date'])."<br> <a href=\"javascript:rmartvu('".$artid."')\" >enlever du projet</a></span>".$vignettes."</div>";
                $first_vu++;
    }
    }


    $listedevis=$projet->devis();
    $facture=$projet->facture();
    if(!is_null($facture)) array_unshift($listedevis,$facture);
    
    if(count($listedevis)>0){
        $tablefactures=<<<HTML
            <tr>
                <td class="td_head_factures"><b>N° Fac</b></td>
                <td class="td_head_factures"><b>N° Cde</b></td>
                <td class="td_head_factures"><b>Date</b></td>
                <td class="td_head_factures"><b>Montant</b></td>
                <td class="td_head_factures"><b>Marge</b></td>
                <td class="td_head_factures"><b>Moyen paiement</b></td>
                <td class="td_head_factures"><b>Etat</b></td>
                <td class="td_head_factures"><b>Cial attribué</b></td>
                <td class="td_head_factures"><b>ACTIONS</b></td>              
            </tr>
HTML;

        foreach($listedevis as $devis){
            $alt=$alt==1?2:1;
            $date=date("d/m/Y H:i",$devis->datecommande);
            if($devis->utilisateur->raisonsociale==""){
                $total=number_format($devis->totalttc,2,","," ")." €TTC";
            }else{
                $total=number_format($devis->totalht,2,","," ")." €HT";
            }
                
            $moyen=$devis->getMoyen($devis->moyenid);
            $etat="<span style=\"".$devis->validecolor."\">".$devis->validetxt."</span>";
            
            $marge=new marge($DB_site2,$devis->facturid);
            $tdmarge=number_format($marge->marge,2,',','')." &euro;<br>".number_format($marge->pcmarge,2,',','')."%";
            
            $p = strrev(md5($devis->facturid."devis"));
            
            $envoidevis="";
            $envoi=$devis->envoi_devis();
            if($envoi!=false){
                $d=date("d/m/Y H:i",$envoi['date']);
                $envoidevis=<<<HTML
                <a href="../devis.php?devisid=$devis->facturid&p=$p" target="_blank">
                    <img src="images/devis.png" height="24" align="absmiddle">
                    <br>$d par $envoi[utilisateur]
                </a>
HTML;
            }
            
            $tablefactures.=<<<HTML
                <tr>
                    <td class="td_factures_alt$alt">$devis->numerofacture</td>
                    <td class="td_factures_alt$alt">$devis->facturid</td>
                    <td class="td_factures_alt$alt">$date</td>
                    <td class="td_factures_alt$alt">$total</td>
                    <td class="td_factures_alt$alt">$tdmarge</td>
                    <td class="td_factures_alt$alt">$moyen</td>
                    <td class="td_factures_alt$alt" style="color:$devis->validecolor;">$etat $envoidevis</td>
                    <td class="td_factures_alt$alt">$devis->commercial</td>
                    <td class="td_factures_alt$alt">
                        <a href="facture.php?action=editer&amp;facturid=$devis->facturid" target="_blank">Editer</a>
                        <br><a href="javascript:rmdevis('$devis->facturid')">Enlever du projet</a>
                        <br><a href="javascript:popdevis('$devis->facturid')">Envoyer le devis</a>
                    </td>
                </tr>
HTML;
            foreach($devis->lignes as $ligne){
                $tablefactures.=<<<HTML
                    <tr>
                        <td colspan="9" class="td_factures_alt$alt">$ligne->qte <a href="clientnavig.php?userid=$devis->userid&goto=art,$ligne->artid,-.htm" target="_blank">$ligne->artcode</a> <a href="ajout_produit.php?artid=$ligne->artid&prec=modif" target="_blank">($ligne->ref)</a></td>
                    </tr>
HTML;
            }
        }
    }
    
}else{
    $projetuser=new utilisateur($DB_site2,$_GET['foruserid']);
    $h1="Nouveau projet ".$projetuser->raisonsociale;
    $projet_infos_style="display:none;";
    $projet_edit_style="";
    $annule_style="display:none;";
}



if($relation_edit!=""){
    $relation_edit=<<<HTML
    <div id="relations">
        <span onclick="this.parentNode.classList.toggle('open')"><img src="images/icon/edit_add.png" align="absmiddle"> Ajouter des relations</span>
        <div id="listnewrelation">
            <form method="post">
            <input type="hidden" name="action" value="addrelation">
            $relation_edit
            <input type="submit" value="OK">
            </form>
        </div>
    </div>
HTML;
}


    $fournisseur_edit=<<<HTML
    <div id="fournisseurs">
        <span onclick="this.parentNode.classList.toggle('open')"><img src="images/icon/edit_add.png" align="absmiddle"> Ajouter des fournisseurs</span>
            <div id="listnewfournisseur">
                <input type="text" onkeyup="srcfournisseur(this.value,'listfournisseur',true);" placeholder="Rechercher">
                <div id="listfournisseur"></div>
            </div>
    </div>
HTML;




$callid="";
$suividivdisplay="none";
$suivititle="";
$suivimoyen="";
$suivitype="";
$suividest="";

/*
$cocktel=new cocktel("http://acilyon.cocktel.fr",$user_info['username'],$user_info['cocktelpass'],"json","/tmp");
if(!is_null(json_decode($cocktel->AppelsEnCours(true)))){
    $json=json_decode($cocktel->AppelsEnCours(true));
    foreach($json->phones as $phone){
        foreach($phone->calls as $call){
            foreach($call->url as $sync=>$syncobj){
                if(in_array($sync,array("clients mec","assoc mec")) && ($syncobj->syncid==$projet->userid || in_array($syncobj->syncid,$projet->relationids))){
                    if($call->local->num==$user_info['telFR'] || $call->local->num==$user_info['telEN']){
                        $callid=$call->callid;
                        $suividivdisplay="block";
                        $suivititle=$call->dir==0?"Appel téléphone client":"Appel client reçu";
                        $suivimoyen=$call->dir==0?"tel_out":"tel_in";
                        $suivitype="client";
                        $suividest=$syncobj->syncid;
                        break 3;
                    }
                }
            }
        }
    }
}
*/
$siteurl=$DB_site2->query_first("SELECT * FROM traduction WHERE path='/site/url'");


$listmail=array();
$req=$DB_site2->query("SELECT service,emailid,libelle FROM emails_clients WHERE service IN ('commercial','projet') AND site_code='base' AND archive = 0 ORDER BY libelle");
while($mail=$DB_site2->fetch_array($req)){
    if(!isset($listmail[$mail['service']])) $listmail[$mail['service']]=array();
    $listmail[$mail['service']][]="<option data-service=\"".$mail['service']."\" value=\"".$mail['emailid']."\">".$mail['libelle']."</option>";
}

$opt_mail="<option>E-mail vide</option>";
if(isset($listmail['commercial']))
    $opt_mail.="<option disabled>-- Relance commerciale --</option>
    <option data-service=\"commercial\">E-mail commercial vide</option>
    ".implode("",$listmail['commercial']);
if(isset($listmail['projet']))
    $opt_mail.="<option disabled>-- Projet --</option>".implode("",$listmail['projet']);


$fournlist="";
$fid=$projet->fournisseurs();
if(count($fid)>0){
    
    $societefourn=array();
    $sql="SELECT * FROM fournisseur WHERE id IN (".implode(",",$fid).")";
    $req=$DB_site2->query($sql);
    while($fourn=$DB_site2->fetch_array($req)){
        $fournlist[$fourn['societe'].$fourn['nom'].$fourn['prenom'].$fourn['mail']]=sprintf("<li onclick=\"srcfournisseurid(%u)\">%s %s %s</li>",$fourn['id'],$fourn['societe'],$fourn['nom'],$fourn['prenom']);
        $societefourn[]=$fourn['societe'];
    }
    
    ksort($fournlist);
    $fournlist="<ul>".implode("",$fournlist)."</ul>";
    
    $filtrefourn="<div class=\"suivifilter\">
        <label><input type=\"checkbox\" onclick=\"checkall(this)\" checked> Fournisseurs</label>";
    
    foreach(array_unique($societefourn) as $fourn)
        $filtrefourn.="<label><input type=\"checkbox\" name=\"filtrefourn\" value=\"".$fourn."\" onclick=\"filtersuivi()\" checked> ".$fourn."</label>";
    
    $filtrefourn.="</div>";
}

include_once("./header.php");
include_once("./menu.php");

//Si le budget "automatique dernier devis" est coché
$montant_last_devis = "";
if(!is_null($projet->projetid)){
    $devis_lies = $DB_site2->query_first("SELECT devis FROM projet WHERE projetid = ".$projet->projetid);
    $devis_lie = unserialize($devis_lies['devis']);
    if(isset($devis_lie)){
        $devis_lie = end($devis_lie);
        $devis_lie = new facture($DB_site2,$devis_lie);
        $montant_last_devis = $devis_lie->totalht;
    }
    else{
        $montant_last_devis = 0;
    }
}

$delais = array("5","15","35","60","90");

$cause_perte = $DB_site2->query_first("SELECT cause FROM causeperte WHERE commentaire ='PJT".$projet->projetid."' ORDER BY id DESC LIMIT 1");


?>
<script type="text/javascript" src="../javascripts/host.js"></script>
<script type="text/javascript" src="utilisateur.js"></script>
<script type="text/javascript" src="facture.js"></script>

<ul class="liens_rapides">
    <li><a href="#infos" class="js-scrollTo">Infos</a></li>
    <li><a href="#fichiers" class="js-scrollTo">Fichiers</a></li>
    <li><a href="#propositions" class="js-scrollTo">Propositions</a></li>
    <li><a href="#articles_vus" class="js-scrollTo">Articles vus</a></li>
    <li><a href="#comparateur" class="js-scrollTo">Comparateur</a></li>
    <li><a href="#configurateur" class="js-scrollTo">Configurateur</a></li>
    <li><a href="#devis" class="js-scrollTo">Devis</a></li>
    <li><a href="#contacts" class="js-scrollTo">Contacts</a></li>
    <li><a href="#suivis" class="js-scrollTo">Suivis</a></li>
</ul>

<?php include("projets_src.php"); ?>

<div id="infos" class="commande_detail">
    <img src="images/icon/edit.png" onclick="document.getElementById('projet_edit').style.display='block';document.getElementById('projet_infos').style.display='none';" style="float:right;">
    <h2>Infos projet</h2>
    <h1>Projet <?=$projet->libelle; ?></h1>
    <table id="projet_infos" width="100%" style="<?=$projet_infos_style; ?>">
        <tr>
            <td>N° <b><?=$projet->pjt; ?></b> du <b><?=!is_null($projet->date)?$projet->date->format("d/m/Y"):""; ?></b></td>
            <td><b>Commercial :</b> <?=$projet->commercial; ?></td>
            <td><b>Commande :</b> <?=$projet->facturid>0?"<a href=\"facture.php?action=editer&facturid=".$projet->facturid."\" target=\"_blank\">".$projet->facturid."</a>":"--"; ?></td>
        </tr>
        <tr>
            <td><b>Budget :</b> <?=number_format($projet->budget,2,',','&nbsp;'); ?> &euro;</td>
            <td><b>Etat :</b> <span style="color:<?=$projet->etatcolor[$projet->etatid]; ?>;"><?=$projet->etat; ?></span></td>
            <td><b>Date pour commander :</b> <span style="color:<?=$datecmdcolor; ?>"><?=$datecmd; ?></span></td>
        </tr>
        <tr>
            <td><b>Type projet :</b> <?=$projet->budget_type_lib(); ?></td>
            <td><b>Echéance livraison :</b> <span style="color:<?=$echeancecolor; ?>"><?=$dateecheance; ?></span></td>
            <td><b>Délai pour commander :</b> <?=$projet->delai_cmd; ?> jours</td>
        </tr>
    </table>
    <div id="projet_edit" style="<?=$projet_edit_style; ?>">
        <form method="post" onsubmit="return checkinfos()">
            <input type="hidden" name="action" value="editer">
            <table width="100%" id="infos_projet">
                <tr>
                    <td style="width: 150px;">Commercial :</td>
                    <td><select name="user_projet_commercial"><?=$optcommercial; ?></select></td>
                </tr>
                <tr>
                    <td>Client :</td>
                    <td>
                        <input type="hidden" name="userid" value="<?=$projetuser->userid; ?>">
                        <?=printf("%s %s %s (%u)",$projetuser->raisonsociale,$projetuser->nom,$projetuser->prenom,$projetuser->userid); ?>
                    </td>
                </tr>
                <tr>
                    <td>Libellé :</td>
                    <td><input type="text" name="user_projet_libelle" value="<?=$projet->libelle; ?>" style="width: 600px;"></td>
                </tr>
                <tr>
                    <td>Budget :</td>
                    <td><input type="radio" name="user_projet_budget" value="<?=$projet->budget; ?>" onclick="document.getElementsByName('user_projet_devis')[0].checked=false;budget_auto('no_auto');" <?=($projet->budget_devis == 1 ? '' : 'checked') ?>> Budget € HT
                        <input type="text" style="width: 100px;" value="<?=$projet->budget; ?>" id="valeur_budget" onchange="budget_val(this)">€
                        <input type="radio" name="user_projet_devis" value="1" <?= ($projet->budget_devis == 1 ? 'checked' : '') ?> onclick="document.getElementsByName('user_projet_budget')[0].checked=false;"> Automatique dernier devis
                        <input type="text" style="width: 100px;" value="<?= $montant_last_devis ?>" id="valeur_budget_auto" name="valeur_budget_auto" readonly>€ HT
                        <input type="checkbox" name="user_projet_sans_budget" value="4" <?=$projet->budget_type==4?"checked":""; ?> style="margin-left:30px;">Sur mesure ou personnalisé</td>
                </tr>
                <tr>
                    <td>Etat :</td>
                    <td><?=$optetat; ?></td>
                </tr>
                <tr style="display:none;">
                    <td></td>
                </tr>
                <!-- Depuis la tâche SI#7427 Nouveau mode projet : on regroupe les états 7(client injoignable) et 11 / 12(petit dossier) et 13 -->
                <tr id="cause_perte">
                    <td>Cause de la perte :</td>
                    <td style="padding-left:20px;">
						<input type="radio" name="causeperte" value="10" id="cause10" <?php echo ($cause_perte['cause'] == '10' ? 'checked' : '') ?>><label for="cause10">Achat ailleurs</label>
						<br><input type="radio" name="causeperte" value="2" id="cause2" <?php echo ($cause_perte['cause'] == '2' ? 'checked' : '') ?>><label for="cause2">Projet abandonné</label>
						<br><input type="radio" name="causeperte" value="11" id="cause11" <?php echo ($cause_perte['cause'] == '11' ? 'checked' : '') ?>><label for="cause11">Client injoignable / Faux numéro / Faux email</label>
						<br><input type="radio" name="causeperte" value="12" id="cause12" <?php echo ($cause_perte['cause'] == '12' ? 'checked' : '') ?>><label for="cause12">Petit dossier / Sans conseil / Sans suivi / Projet décliné par MCAV / Répondu trop tard</label>
						<br><input type="radio" name="causeperte" value="8" id="cause8" <?php echo ($cause_perte['cause'] == '8' ? 'checked' : '') ?>><label for="cause8">Double projet / Double compte / Compte interne</label>
						<br><input type="radio" name="causeperte" value="13" id="cause13" <?php echo ($cause_perte['cause'] == '13' ? 'checked' : '') ?>><label for="cause13">Hors budget</label>
					</td>
                </tr>
                <tr id="div_num_cde" style="display:none;">
                    <td>N° Cde :</td>
                    <td><select name="user_projet_facturid"><?=$optfacture; ?></select></td>
                </tr>
                <tr>
                    <td>Echéance livr. :</td>
                    <td>
                        <!--<input type="checkbox" onclick="chkecheance(this,document.getElementsByName('user_projet_echeance_livraison')[0])" <?=$chkecheance; ?>> Sans échéance connue<br>-->
                        <input type="date" size="10" maxlength="10" name="user_projet_echeance_livraison" value="<?=!is_null($projet->dateecheance)?$projet->dateecheance->format("Y-m-d"):""; ?>" >
                        <input type="radio" name="echeance_type" value="0" id="type0" <?php echo ($projet->echeance_type == '0' ? 'checked' : '') ?> ><label for="type0">Estimé par MCAV</label>
                        <input type="radio" name="echeance_type" value="1" id="type1" <?php echo ($projet->echeance_type == '1' ? 'checked' : '') ?>><label for="type1">Info du client</label>
                    </td>
                </tr>
                <tr>
                    <td>Délai livraison <img src="images/information.jpg" onmouseenter="document.getElementById('infodelai').style.display='block';" onmouseleave="document.getElementById('infodelai').style.display='none';">
                        <ul id="infodelai">
                            <li>Standard en stock : 10j à 15j</li>
                            <li>Calice métal : 30j</li>
                            <li>Calice bois : 40j</li>
                            <li>Calice Design : 40j</li>
                            <li>Calice Design option noir : 50j</li>
                            <li>Sur mesure : 90j</li>
                            <li>Autre produit : voir délai sur site</li>
                        </ul>
                        :
                    </td>
                    <td><label><input type="radio" name="delai_cmd" value="5" <?php echo ($projet->delai_cmd == '5' ? 'checked' : ''); ?>> 5 j</label>
                    <label><input type="radio" name="delai_cmd" value="15" <?php echo ($projet->delai_cmd == '15' ? 'checked' : ''); ?>> 15 j</label>
                    <label><input type="radio" name="delai_cmd" value="35" <?php echo ($projet->delai_cmd == '35' ? 'checked' : ''); ?>> 35 j</label>
                    <label><input type="radio" name="delai_cmd" value="60" <?php echo ($projet->delai_cmd == '60' ? 'checked' : ''); ?>> 60 j</label>
                    <label><input type="radio" name="delai_cmd" value="90" <?php echo ($projet->delai_cmd == '90' ? 'checked' : ''); ?>> 90 j</label>
                    <label><input type="radio" name="delai_cmd" value="" <?php echo (!in_array($projet->delai_cmd,$delais) && $projet->delai_cmd !== '0' ? 'checked' : ''); ?>> Autre</labe>
                    <input type="text" name="delai_autre_cmd" value="<?php echo (!in_array($projet->delai_cmd,$delais) && $projet->delai_cmd !== '0' ? $projet->delai_cmd : ''); ?>" style="width: 50px;" onchange="delai_val(this)" id="valeur_delai"></td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <input type="button" value="Annuler" style="<?=$annule_style; ?>" onclick="document.getElementById('projet_edit').style.display='none';document.getElementById('projet_infos').style.display='block';"> <input type="submit" value="Enregistrer">
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<?php

if(!is_null($projet->projetid)){
    $width_prop = $width_prop*200;

?>

<div id="besoin" class="commande_detail">
    <img src="images/icon/edit.png" onclick="document.getElementById('besoin_edit').style.display='block';document.getElementById('liste_besoin').style.display='none';" style="float:right;">
    <h2>Besoins</h2>
    <tr id="liste_besoin">
        <td colspan="3">
                <div style="max-height: 200px; overflow: auto;">
                    <b>Liste des besoins :</b> <?=nl2br($projet->besoin); ?>
                </div>          
        </td>
    </tr>
    
        <div id="besoin_edit">
            <table width="100%">
                <form method="post">
                <input type="hidden" name="action" value="editer">
                <input type="hidden" name="besoins" value="true">
                <input type="hidden" name="userid" value="<?=$projetuser->userid; ?>">
            <tr>
                <table style="margin: 10px 0;">
                    <tr>
                        <td style="padding:0 20px;">
                            <strong>Cuisinières et Pianos</strong><a href="javascript:besoin(1,'edit_projet_besoin');" title="Cuisinières et Pianos"><img src="images/icon/edit_add.png" alt="" align="absmiddle"></a>
                        </td>
                        <td style="padding:0 20px;">
                            <strong>Tables de cuisson</strong><a href="javascript:besoin(2,'edit_projet_besoin');" title="Tables de cuisson"><img src="images/icon/edit_add.png" alt="" align="absmiddle"></a>
                        </td>
                        <td style="padding:0 20px;">
                            <strong>Fours</strong><a href="javascript:besoin(3,'edit_projet_besoin');" title="Fours"><img src="images/icon/edit_add.png" alt="" align="absmiddle"></a>
                        </td>
                        <td style="padding:0 20px;">
                            <strong>Hotte</strong><a href="javascript:besoin(4,'edit_projet_besoin');" title="Hotte"><img src="images/icon/edit_add.png" alt="" align="absmiddle"></a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 20px;">
                            <strong>Barbecue</strong><a href="javascript:besoin(5,'edit_projet_besoin');" title="Barbecue"><img src="images/icon/edit_add.png" alt="" align="absmiddle"></a>
                        </td>
                        <td style="padding:0 20px;">
                            <strong>Micro-ondes</strong><a href="javascript:besoin(6,'edit_projet_besoin');" title="Micro-ondes"><img src="images/icon/edit_add.png" alt="" align="absmiddle"></a>
                        </td>
                        <td style="padding:0 20px;">
                            &nbsp;
                        </td>
                        <td style="padding:0 20px;">
                            &nbsp;
                        </td>
                    </tr>
                </table>
            </tr> 
            <tr>
                <td>
                    <textarea name="user_projet_besoin" id="edit_projet_besoin" cols="85" rows="15"><?=$projet->besoin; ?></textarea>
                </td>
            </tr> 
            <tr>
                <td colspan="2" align="center">
                    <input type="button" value="Annuler" style="<?=$annule_style; ?>" onclick="document.getElementById('besoin_edit').style.display='none';document.getElementById('liste_besoin').style.display='block';"><input type="submit" value="Enregistrer">
                </td>
            </tr>
            </table>              
        </div>
    </form> 

</div>


<div id="fichiers" class="commande_detail">
    <h2>Fichiers</h2>
    <div id="fbcontainer"></div>
</div>

<div id="propositions" class="commande_detail">
    <h2>Propositions d'articles</h2>
    <div class="table_modules_boutiques no_border prop_article" style="overflow: auto;">
        <div style="width: <?php echo $width_prop; ?>px;">
            <div class="overflow_auto">
                <?=$propositions; ?>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <div class="clear"></div>
    Ajouter une référence : <input type="text" onkeyup="srcsimilaire(this.value,'base');">
    <div style="float:right;" class="bouton_dispo">
        <a class="lien lien_fiche" href="javascript:artmaildispo();">Demande de disponibilité</a>
    </div><div class="clear"></div>
    <div id="results"></div>
</div>

<?php if($DB_site2->num_rows($articlesvus) > 0){ ?>
<div id="articles_vus" class="commande_detail">
    <h2>Articles vus</h2>
    <div id="projet_vus">
        <span onclick="document.getElementById('projet_vus').classList.toggle('open')"><img src="images/icon/edit_add.png" align="absmiddle"> Ajouter un article vu</span>
        <div id="listeartvu">
            <div style="display:flex;flex-wrap:nowrap;">
               <?=$divartvu; ?>
            </div>
        </div>
    </div>
    <div class="results_artvu">
        <div style="display:flex;flex-wrap:nowrap;">
            <?=$tableartvu; ?>
        </div>
    </div>
</div>

<?php } if($DB_site2->num_rows($comparateur) > 0){ ?>
<div id="comparateur" class="commande_detail">
    <h2>Comparateur</h2> 
    <div id="projet_compare">
        <span onclick="document.getElementById('projet_compare').classList.toggle('open')"><img src="images/icon/edit_add.png" align="absmiddle"> Ajouter un comparateur</span>
        <div id="listecompare">
            <div style="display:flex;flex-wrap:nowrap;">
                <div><?=$divcompares; ?></div>
            </div>
        </div>
    </div>
    <table class="table_factures">
        <?=$tablecompares; ?>
    </table>
</div>

<?php }  if($DB_site2->num_rows($configurateurs) > 0){ ?>
<div id="configurateur" class="commande_detail">
    <h2>Configurateur</h2> 
    <div id="projet_config">
        <span onclick="document.getElementById('projet_config').classList.toggle('open')"><img src="images/icon/edit_add.png" align="absmiddle"> Ajouter une configuration</span>
        <div id="listeconfigs">
            <div style="display:flex;flex-wrap:nowrap;">
                <div><?=$divconfigs; ?></div>
                <div><?=$div2configs; ?></div>
            </div>
        </div>
    </div>
    <table class="table_factures">
        <?=$tableconfigs; ?>
    </table>
</div>
<?php }  if(count($listedevis)>0 || count($factures)>0){ ?>

<div id="devis" class="commande_detail">
    <h2>Devis - Commandes</h2> 
    <div id="projet_devis">
        <span onclick="document.getElementById('projet_devis').classList.toggle('open')"><img src="images/icon/edit_add.png" align="absmiddle"> Ajouter un devis</span>
        <div id="listedevis">
            <?=$divfactures; ?>
        </div>
    </div>
    <table class="table_factures">
        <?=$tablefactures; ?>
    </table>
</div>
<?php }

$delais = array("5","15","35","65","90");?>

<div id="contacts" class="commande_detail">
    <h2>Contacts</h2>
    <?=$relation_edit; ?>
    <?=$fournisseur_edit; ?>
    <table class="table_modules_boutiques no_border">
        <tr>
            <?=$utilisateurs; ?>
        </tr>
    </table>
    <!-- On englobe la table dans un form pour faciliter les traitements -->
    <form method="post">
        <input type="hidden" name="action" value="add_fourn">
        <table class="table_modules_boutiques no_border">
            <tr>
                <?=$fournisseurs; ?>
            </tr>
        </table>
    </form>
</div>

<div id="suivis" class="commande_detail">
    <?=$prochainrappel; ?>
    <h2>Suivis</h2>
    <table id="suiviaction">
        <thead>
            <tr>
                <td class="suiviclient">Client</td>
                <td class="suivipro">Fournisseur</td>
                <td class="suiviint">Interne</td>
            </tr>
        </thead>
        <tr>
            <td class="suiviclient" align="center">
                <!--$fournlist;  -->
                <input type="text" onkeyup="srcfournisseur(this.value,'srcclient');" placeholder="Rechercher">
            </td>
            <td class="suivipro" align="center">
                <?=$fournlist; ?>
                <input type="text" onkeyup="srcfournisseur(this.value,'srcfournisseur');" placeholder="Rechercher">
            </td>
            <td class="suiviint" align="center">
                <span class="suivilink int" onclick="suiviauto('','',0,'Suivi interne')">Message interne</span>
            </td>
        </tr>
        <tr><td><div id="srcfournisseur"></div></td></tr>
        <tr><td><div id="srcclient"></div></td></tr>
    </table>
    <form name="suivi_client" method="post" id="projet_suivi_client">
        <div id="suividiv" style="display:<?=$suividivdisplay; ?>;">
            <input type="hidden" name="action" value="suivi">
            <input type="hidden" name="userid" value="<?=$projet->userid; ?>">
            <input type="hidden" name="moyen" value="<?=$suivimoyen; ?>">
            <input type="hidden" name="type" value="<?=$suivitype; ?>">
            <input type="hidden" name="callid" value="<?=$callid; ?>">
            <input type="hidden" name="destinataire" value="<?=$suividest; ?>">
            
            <div id="suivititle"><?=$suivititle; ?></div>
            
            <div class="mail_com">
                <p>Message de suivi</p>
                <textarea name="suivi" id="suivi"></textarea>
            </div>
            
            
            <div class="mail_com">
                <p>Infos projet :</p>
                Etat :
                <?=$etatsuivi; ?>
                <div class="mail_com" id="cause0"> 
                    <p>Cause de la perte :</p>
                    <label><input type="radio" name="cause" value="10">Achat ailleurs</label>
                    <label><input type="radio" name="cause" value="2">Projet abandonné</label>
                    <label><input type="radio" name="cause" value="11">Client injoignable / Faux numéro / Faux email</label>
                    <br><label><input type="radio" name="cause" value="12">Petit dossier / Sans conseil / Sans suivi / Projet décliné par MCAV / Répondu trop tard</label>
                    <label><input type="radio" name="cause" value="8">Double projet / Double compte / Compte interne</label> 
					<br><label><input type="radio" name="cause" value="13">Hors budget</label> 
                </div>
                <div id="cmdecheance">
                    Echéance livr. : <input type="date" name="suivi_echeance" value="<?=!is_null($projet->dateecheance)?$projet->dateecheance->format("Y-m-d"):""; ?>">
                    <input type="radio" name="echeance_type" value="0" id="type0" <?php echo ($projet->echeance_type == '0' ? 'checked' : '') ?> ><label for="type0">Estimé par MCAV</label>
                        <input type="radio" name="echeance_type" value="1" id="type1" <?php echo ($projet->echeance_type == '1' ? 'checked' : '') ?>><label for="type1">Info du client</label>
                    <br>Délai livraison :
                    <label><input type="radio" name="delai_cmd" value="5" <?php echo ($projet->delai_cmd == '5' ? 'checked' : ''); ?>> 5 j</label>
                    <label><input type="radio" name="delai_cmd" value="15" <?php echo ($projet->delai_cmd == '15' ? 'checked' : ''); ?>> 15 j</label>
                    <label><input type="radio" name="delai_cmd" value="35" <?php echo ($projet->delai_cmd == '35' ? 'checked' : ''); ?>> 35 j</label>
                    <label><input type="radio" name="delai_cmd" value="60" <?php echo ($projet->delai_cmd == '60' ? 'checked' : ''); ?>> 60 j</label>
                    <label><input type="radio" name="delai_cmd" value="90" <?php echo ($projet->delai_cmd == '90' ? 'checked' : ''); ?>> 90 j</label>
                    <label style="margin:0;"><input type="radio" name="delai_cmd" value="" <?php echo (!in_array($projet->delai_cmd,$delais) && $projet->delai_cmd !== '0' ? 'checked' : ''); ?>> Autre</label>
                    <input type="text" name="delai_autre_cmd" value="<?php echo (!in_array($projet->delai_cmd,$delais) && $projet->delai_cmd !== '0' ? $projet->delai_cmd : ''); ?>" style="width: 50px;" onchange="delai_val(this)" id="valeur_delai">
                    <br>Budget : 
                        <input type="radio" name="budget" value="<?=$projet->budget; ?>" onclick="document.getElementsByName('user_projet_devis')[1].checked=false;budget_auto('no_auto')" <?=($projet->budget_devis == 1 ? '' : 'checked') ?>> Budget HT
                        <input type="text" style="width: 100px;" value="<?=$projet->budget; ?>" id="valeur_budget" onchange="budget_val(this)">€
                        <input type="radio" name="user_projet_devis" value="1" <?= ($projet->budget_devis == 1 ? 'checked' : '') ?> onclick="document.getElementsByName('budget')[0].checked=false;"> Automatique dernier devis
                        <input type="text" style="width: 100px;" value="<?= $montant_last_devis ?>" id="valeur_budget_auto" name="valeur_budget_auto" readonly>€ HT
                        <input type="checkbox" name="sans_budget" value="4" <?=$projet->budget_type==4?"checked":""; ?>>Sur mesure ou personnalisé
                </div>
            </div>
            
            <div class="mail_com" id="cmdsuivi">
                <p>N° de commande :</p>
                <select name="projet_facturid">
                    <?=$optfacture; ?>
                </select>
            </div>
            
            <div class="mail_com" id="rappelsuivi">
                <p>Prochain rappel :</p>
                Suivi commercial : <label onclick="choix_rappel('','rdv')"><input type="radio" name="type_rdv" value="rdv"/> RDV client / A traiter</label>
                <label onclick="choix_rappel()"><input type="radio" name="type_rdv" value="suivi"/> suivi sur 3 jours</label>
                <input type="text" name="rappel_rdv" id="rappel_rdv" onclick="calendrier_show(this.id,this.value,'planning_rdv',planningcb,1);" readonly style="width:130px;">
    
                <input type="text" name="rappel" id="rappel" onclick="calendrier_show(this.id,this.value,'planning_suivi',planningcb,1);" readonly style="width:130px;">

                pour <input type="text" name="pour" size="6" readonly>
                <input type="hidden" name="box" value="1"> 
                <input type="hidden" name="rappeltype" value="">
                <input type="hidden" name="jour" value="0"><br>

				<div id="suiviexterne">
                    Suivi externe : <label onclick="choix_rappel('ext','rdv',true)"><input type="radio" name="type_rdv" value="rdv"/> RDV client externe / A traiter externe</label>
                    <label onclick="choix_rappel('ext',null,true)"><input type="radio" name="type_rdv" value="suivi"/> suivi externe sur 3 jours</label>

                    <input type="text" name="rappel_rdv_ext" id="rappelext_rdv" onclick="calendrier_show(this.id,this.value,'planning_rdv',planningcb,1,null,true);" readonly style="width:130px;">
                    <input type="text" name="rappel_ext" id="rappelext" onclick="calendrier_show(this.id,this.value,'planning_suivi',planningcb,1,null,true);" readonly style="width:130px;">
                    <br>
                    <div style="display: none;">
                        Consignes pour suivi externe :
                        <textarea name="suivi_externe" id="suivi_externe"><?=$projet->suivi_externe; ?></textarea>
					</div>
                </div>
            </div>
                    
            <div class="mail_com" id="span_4">
                <input type="submit" value="Ajouter le suivi">
            </div>
            
        </div>
        <div class="clear"></div>
    </form>
        
    <div id="suivimaildiv" style="display:none;">
        <input type="hidden" id="langue" value="<?=$clientprojet->langue; ?>"> 
        <input type="hidden" id="siteurl" value="<?=$siteurl[$clientprojet->langue]; ?>">
        <input type="hidden" id="site_code" value="base">
        <input type="hidden" id="pro" value="<?=($clientprojet->raisonsociale==""?0:1); ?>"> 
        <input type="hidden" id="dest" value="<?=$clientprojet->mail; ?>">
        <input type="hidden" id="tracking_type" value="PJT">
        <input type="hidden" id="tracking_id" value="<?=$projet->projetid; ?>">
        <input type="hidden" id="userid_suivi" value="<?=$projet->userid; ?>">
        
        <div id="propositions_tab">
            <div id="td_prop_av" onclick="prop_tab('prop_av');"><img src="images/plus.jpg">Articles vus</div>
            <div id="prop_av"><?=$prop_av; ?></div>
        
            <div id="td_prop_prop" onclick="prop_tab('prop_prop');"><img src="images/plus.jpg">Propositions d'articles</div>
            <div id="prop_prop"><?=$prop_prop; ?></div>
        </div>
        <div id="article_mail_result" style="z-index: 100;"></div>

        <div class="mail_com">
            <p>Choix de l'expéditeur :</p>
            <select id="com_exp"><?=$opt_exp; ?></select>
        </div>
    
        <div class="mail_com">
            <p>Sujet de  l'e-mail :</p>
            <select id="com_mail_id" onchange="sujetmail()"><?=$opt_mail; ?></select>
        </div>

        <div class="mail_com relance">  
            <p>Notre offre - Bon de réduction :</p>
            <input type="text" id="codepromo" onkeyup="codepromo_src(this.value)" onclick="this.value='';" value="Rechercher"> <span id="selected_codepromo" style="border:solid 1px black;padding:4px;display:none;"><input type="text" id="codepromo_code" style="border:none;width:140px;" readonly> <img src="images/supprimer.gif" onclick="select_codepromo();"></span> <a href="cadeau.php?action=ajouter" target="_blank">Créer un code</a>
            <div id="bon_result"></div>
        </div>
    
        <div class="mail_com relance">
            <p>La sélection</p>
            <input type="text" id="srcarticle0" onkeyup="article_mail(this.value,0)" size="10" onclick="move_prop(this)">
            <div id="article0"></div>
        </div>
    
        <div class="mail_com relance">
            <p>Produits conseillés</p>
    
            <div class="article">
                Article 1 :
                <br><input type="text" id="srcarticle1" onkeyup="article_mail(this.value,1)" size="10" onclick="move_prop(this)">
                <div id="article1"></div>
            </div>

            <div class="article">
                Article 2 :
                <br><input type="text" id="srcarticle2" onkeyup="article_mail(this.value,2)" size="10" onclick="move_prop(this)">
                <div id="article2"></div>
            </div>

            <div class="clear"></div>
            <hr>

            <div class="article">
                Article 3 :
                <br><input type="text" id="srcarticle3" onkeyup="article_mail(this.value,3)" size="10" onclick="move_prop(this)">
                <div id="article3"></div>
            </div>
                    
            <div class="article">
                Article 4 :
                <br><input type="text" id="srcarticle4" onkeyup="article_mail(this.value,4)" size="10" onclick="move_prop(this)">
                <div id="article4"></div>
            </div>

            <div class="clear"></div>
            <hr>

            <div class="article">
                Article 5 :
                <br><input type="text" id="srcarticle5" onkeyup="article_mail(this.value,5)" size="10" onclick="move_prop(this)">
                <div id="article5"></div>
            </div>
            
            <div class="article">
                Article 6 :
                <br><input type="text" id="srcarticle6" onkeyup="article_mail(this.value,6)" size="10" onclick="move_prop(this)">
                <div id="article6"></div>
            </div>
            
            <div class="clear"></div>
        </div>

        <div class="mail_com relance">
            <p>Pourquoi nous choisir</p>
            <input type="checkbox" id="pourquoi" checked> Afficher
        </div>

        <div class="mail_com relance">
            <p>Nos marques</p>
            <input type="checkbox" id="marques" checked> Afficher
        </div>
        
        <div class="mail_com relance">
            <p>Bas de page</p>
            <select id="footer">
                <option value="complet" selected>Complet</option>
                <option value="reduit">Réduit</option>
                <option value="">Aucun</option>
            </select>
        </div>
        
        <form name="mail_etat" method="post" id="projet_mailcom">
        <div class="mail_com">
            <p>Etat du projet</p>
             <input type="hidden" name="userid" value="<?=$projet->userid; ?>">
            <input type="hidden" name="action" value="etat">
            <input type="hidden" name="etat" value="<?= $projet->etatid ?>">
            <input type="hidden" name="delai_cmd" value="<?= $projet->delai_cmd ?>">
            <input type="hidden" name="echeance_type" value="<?= $projet->echeance_type ?>">
            <input type="hidden" name="user_projet_devis" value="<?= $projet->budget_devis ?>">
            <input type="hidden" name="causeperte" value="<?= $projet->causeperte ?>">

            <?=$etatsuivimail; ?>
            <!-- Depuis la tâche SI#7427 Nouveau mode projet : on regroupe les états 7(client injoignable) et 11 / 12(petit dossier) et 13 -->
            <div id="cause_perte2">
                <p>Cause de la perte :</p>
                <div style="padding-left:20px;">
                    <input type="radio" name="2causeperte" value="10" id="2cause10" <?php echo ($cause_perte['cause'] == '10' ? 'checked' : '') ?> onclick="document.querySelector('#suivimaildiv input[name=causeperte]').value=this.value;"><label for="2cause10">Achat ailleurs</label><br>
                    <input type="radio" name="2causeperte" value="2" id="2cause2" <?php echo ($cause_perte['cause'] == '2' ? 'checked' : '') ?> onclick="document.querySelector('#suivimaildiv input[name=causeperte]').value=this.value;"><label for="2cause2">Projet abandonné</label><br>
                    <input type="radio" name="2causeperte" value="11" id="2cause11" <?php echo ($cause_perte['cause'] == '11' ? 'checked' : '') ?> onclick="document.querySelector('#suivimaildiv input[name=causeperte]').value=this.value;"><label for="2cause11">Client injoignable / Faux numéro / Faux email</label>
                    <br><input type="radio" name="2causeperte" value="12" id="2cause12" <?php echo ($cause_perte['cause'] == '12' ? 'checked' : '') ?> onclick="document.querySelector('#suivimaildiv input[name=causeperte]').value=this.value;"><label for="2cause12">Petit dossier / Sans conseil / Sans suivi / Projet décliné par MCAV / Répondu trop tard</label>
                    <br><input type="radio" name="2causeperte" value="8" id="2cause8" <?php echo ($cause_perte['cause'] == '8' ? 'checked' : '') ?> onclick="document.querySelector('#suivimaildiv input[name=causeperte]').value=this.value;"><label for="2cause8">Double projet / Double compte / Compte interne</label></div>
                </div>
        </div>

        <div class="mail_com" id="cmdmail" style="display:none;">
                <p>N° de commande :</p>
                <select name="projet_facturid">
                    <?=$optfacture; ?>
                </select>
            </div>
        
        <div class="mail_com">
            <p>Date de rappel</p>
            <label onclick="choix_rappel('mail','rdv')"><input type="radio" name="type_rdv" value="rdv"/> RDV</label>
                <label onclick="choix_rappel('mail')"><input type="radio" name="type_rdv" value="suivi"/> suivi sur 3 jours</label>
                <input type="text" name="rappel_rdv" id="rappelmail_rdv" onclick="calendrier_show(this.id,this.value,'planning_rdv',planningcb,1);" readonly style="width:130px;">

                <input type="text" name="rappel" id="rappelmail" onclick="calendrier_show(this.id,this.value,'planning_suivi',planningcb,1);" readonly style="width:130px;">

                pour <input type="text" name="pour" size="6" readonly>
                <input type="hidden" name="box" value="1"> 
                <input type="hidden" name="rappeltype" value="">
                <input type="hidden" name="jour" value="0">

        </div>

        <div class="mail_com" id="mail_com_infos">
            <p>Infos projet :</p>
            Echéance livr. : <input type="date" name="suivi_echeance" id="mail_echeance" value="<?=!is_null($projet->dateecheance)?$projet->dateecheance->format("Y-m-d"):""; ?>" style="<?=$styleecheance; ?>" > 
            <input type="radio" name="echeance_type_mail" value="0" id="type0" <?php echo ($projet->echeance_type == '0' ? 'checked' : '') ?> onclick="document.querySelector('#suivimaildiv input[name=echeance_type]').value=this.value;"><label for="type0">Estimé par MCAV</label>
            <input type="radio" name="echeance_type_mail" value="1" id="type1" <?php echo ($projet->echeance_type == '1' ? 'checked' : '') ?> onclick="document.querySelector('#suivimaildiv input[name=echeance_type]').value=this.value;"><label for="type1">Info du client</label>
            <br>Délai livraison :
            <label onclick="document.querySelector('#suivimaildiv input[name=delai_cmd]').value=this.children[0].value;"><input type="radio" name="delai_cmd_mail" value="5" <?php echo ($projet->delai_cmd == '5' ? 'checked' : ''); ?>> 5 j</label>
            <label onclick="document.querySelector('#suivimaildiv input[name=delai_cmd]').value=this.children[0].value;"><input type="radio" name="delai_cmd_mail" value="15" <?php echo ($projet->delai_cmd == '15' ? 'checked' : ''); ?>> 15 j</label>
            <label onclick="document.querySelector('#suivimaildiv input[name=delai_cmd]').value=this.children[0].value;"><input type="radio" name="delai_cmd_mail" value="35" <?php echo ($projet->delai_cmd == '35' ? 'checked' : ''); ?>> 35 j</label>
            <label onclick="document.querySelector('#suivimaildiv input[name=delai_cmd]').value=this.children[0].value;"><input type="radio" name="delai_cmd_mail" value="60" <?php echo ($projet->delai_cmd == '60' ? 'checked' : ''); ?>> 60 j</label>
            <label onclick="document.querySelector('#suivimaildiv input[name=delai_cmd]').value=this.children[0].value;"><input type="radio" name="delai_cmd_mail" value="90" <?php echo ($projet->delai_cmd == '90' ? 'checked' : ''); ?>> 90 j</label>
            <label  onclick="document.querySelector('#suivimaildiv input[name=delai_cmd]').value=this.children[0].value;" style="margin:0;"><input type="radio" name="delai_cmd_mail" value="" <?php echo (!in_array($projet->delai_cmd,$delais) && $projet->delai_cmd !== '0' ? 'checked' : ''); ?>> Autre</label>
            <input type="text" name="delai_autre_cmd" value="<?php echo (!in_array($projet->delai_cmd,$delais) && $projet->delai_cmd !== '0' ? $projet->delai_cmd : ''); ?>" style="width: 50px;" onchange="delai_val(this)" id="valeur_delai">
            <br>Budget :
            <input type="radio" name="budget" value="<?=$projet->budget; ?>" onclick="document.getElementsByName('mail_projet_devis')[0].checked=false;document.querySelector('#suivimaildiv input[name=user_projet_devis]').value=0;" <?=($projet->budget_devis == 1 ? '' : 'checked') ?>> Budget HT
            <input type="text" style="width: 100px;" value="<?=$projet->budget; ?>" id="valeur_budget" onchange="budget_val(this)">€
            <input type="radio" name="mail_projet_devis" value="1" <?= ($projet->budget_devis == 1 ? 'checked' : '') ?> onclick="document.getElementsByName('budget')[1].checked=false;document.querySelector('#suivimaildiv input[name=user_projet_devis]').value=1;"> Automatique dernier devis
            <input type="text" style="width: 100px;" value="<?= $montant_last_devis ?>" id="valeur_budget_auto" name="valeur_budget_auto" readonly>€ HT
            <input type="checkbox" name="sans_budget" value="4" <?=$projet->budget_type==4?"checked":""; ?>>Sur mesure ou personnalisé
        </div>
        
        </form>
        
        <div class="mail_com">
            <input type="button" id="mailgenerate" value="Générer l'e-mail">
        </div>
    </div>

    <div>
        <?=$filtrefourn.$filtrecontact; ?>
        <div class="suivifilter">
            <label><input type="checkbox" onclick="filtersuivi()" name="filtreinterne" checked> Interne</label>
            <label><input type="checkbox" onclick="if(this.checked)document.getElementsByName('filtreinterne')[0].checked=true;filtersuivi();" name="filtremodif"> Modifications projet seulement</label>
        </div>
        <div class="clear"></div>
    </div>

        <?php if($projet->suivi_externe !== ''){ ?>
    <div class="suivi_externe">
        <div class="titre_suiviext">Suivi externe</div>
        <?= $projet->suivi_externe ?>
    </div>
    <?php } ?>

    <div id="tab_suivi_client"></div>
</div>

<?php 

}

?>

<link href="filebox2.css" type="text/css" rel="stylesheet">
<script language="javascript" src="../javascripts/prototype.js"></script>
<script language="javascript" src="filebox.js"></script>
<script type="text/javascript">
    var $jq = jQuery.noConflict();
    var fb;

    document.body.onload=function(){
     document.title=document.getElementsByTagName("h1")[0].innerHTML;
        $jq("#projet_edit").find("input[type='radio'][name='user_projet_etatid']").click(function() {
            $jq("#cause_perte").hide();
            $jq("#div_num_cde").hide();
            $jq("#budgettr").hide();
            switch(this.value){
                case "6":
                $jq("#cause_perte").show();
                $jq('#projet_edit').find('input').removeAttr('required');
                break;

                case "5":
                $jq("#div_num_cde").css('display','table-row');
                break;

                case "9":
                $jq("#echlivr").hide();
                $jq("#delailivr").hide();
                $jq("#budgettr").hide();
                break;

                default:
                $jq("#echlivr").show();
                $jq("#delailivr").show();
                $jq("#budgettr").show();
                break;
            }
        });
        $jq("#suivimaildiv").find("input[type='radio'][name='mailetats']").click(function() {
            $jq("#cause_perte2").hide();
            $jq("#cmdmail").hide();
            switch(this.value){
                case "6":
                $jq("#cause_perte2").show();
                $jq('#suivimaildiv').find('input').removeAttr('required');
                $jq("#cmdmail").next().hide();
                break;

                case "5":
                $jq("#cmdmail").show();
                break;

                case "9":
                $jq("#mail_com_infos").hide();
                break;

                default:
                $jq("#cmdmail").next().show();
                $jq("#mail_com_infos").hide();
                break;
            }
        });

        if(document.getElementById('tab_suivi_client'))
            loadsuivi(<?=$projet->userid; ?>,"pjt",1,<?=$projet->projetid; ?>);
        
        $jq("#suividiv input[type='radio'][name='etat']").click(function() {
            $jq("#cause0").hide();
            $jq('#span_4').show();
            $jq("#cmdsuivi").hide();
            $jq('#rappelsuivi').hide();
            $jq('#cmdecheance').hide();
            
            switch(this.value){
                case "6":
                $jq("#cause0").show();
                $jq('#cmdecheance').find('input').removeAttr('required');
                break;
                
                case "5":
                $jq("#cmdsuivi").show();
                $jq('#span_4').show();
                break;

                case "9":
                $jq('#cmdecheance').hide();
                $jq('#rappelsuivi').show();
                $jq('#suiviexterne').hide();
                break;
                
                default:
                $jq('#rappelsuivi').show();
                $jq('#cmdecheance').show();
            }
        });
        
        if(document.getElementById('fbcontainer')){
            fb=new filebox('fb',document.getElementById('fbcontainer'));
            fb.show('user_files.php?projetid=<?=$projet->projetid; ?>&amp;parent=0');
            fbmail=new filebox('fbmail');
            fbmail.dataurl=fb.dataurl;
            mail_pagepj('fbmail.showselect',['undefined',mail_addpjs]);
        }
        
        document.querySelector("#mailgenerate").onclick=function(){
            if(document.querySelector("#suivimaildiv input[name='etat']").value=="" || document.querySelector("#suivimaildiv input[name='etat']").value=="0"){
                alert("Merci de choisir l'état du projet");
            }else
                com_mail_generate();
        };
    };
    var userid=0;
    
    function swaprelation(userid){
        var form=document.createElement("form");
        form.method="post";
        document.body.appendChild(form);
        var action=document.createElement("input");
        action.type="hidden";
        action.name="action";
        action.value="swap";
        form.appendChild(action);
        var swapwith=document.createElement("input");
        swapwith.type="hidden";
        swapwith.name="swapwith";
        swapwith.value=userid;
        form.appendChild(swapwith);
        form.submit();
    }
    
    function rmrelation(userid){
        if(!confirm("Confirmez-vous la suppression de ce contact du projet ?")) return;
        var form=document.createElement("form");
        form.method="post";
        document.body.appendChild(form);
        var action=document.createElement("input");
        action.type="hidden";
        action.name="action";
        action.value="rmrelation";
        form.appendChild(action);
        var inp=document.createElement("input");
        inp.type="hidden";
        inp.name="userid";
        inp.value=userid;
        form.appendChild(inp);
        form.submit();
    }
    //Demande confirmation avant de lancer la suppression du fournisseur du projet
     function rmrelationfourn(fournid){
        if(!confirm("Confirmez-vous la suppression de ce fournisseur du projet ?")) return;
        var form=document.createElement("form");
        form.method="post";
        document.body.appendChild(form);
        var action=document.createElement("input");
        action.type="hidden";
        action.name="action";
        action.value="rmrelationfourn";
        form.appendChild(action);
        var inp=document.createElement("input");
        inp.type="hidden";
        inp.name="fournid";
        inp.value=fournid;
        form.appendChild(inp);
        form.submit();
    }

    /*function underline(div){
        $jq(div).parent().find("input[type='radio']:checked").each(function(e){
            $jq(this).next().css('text-decoration','underline');
        });
    }*/

    function choix_rappel(div="",type=null){
        if(div == "ext"){
            $jq("#suivi_externe").parent().css('display','block');
            $jq("#span_4").hide();
        }

        if(type==null){
            $jq("#rappel"+div).show();
            $jq("#rappel"+div+"_rdv").hide();
            if(div == 'mail'){
                $jq("#projet_mailcom").find("input[name='rappeltype']").val('int');
            }
            else{
                $jq("input[name='rappeltype']").val('int');
            }
        }
        else{                
            $jq("#rappel"+div+"_rdv").show();
            $jq("#rappel"+div).hide();
            if(div == 'mail'){
                $jq("#projet_mailcom").find("input[name='rappeltype']").val('rdv');
            }
            else{
                $jq("input[name='rappeltype']").val('rdv');
            }
        }
    }

    function budget_val(input){
        var budget = $jq(input).val();
        $jq(input).prev().val(budget);
    }

    function delai_val(input){
        var delai = $jq(input).val();
        $jq(input).prev().find('input').val(delai);
    }

 
    
    function sujetmail(){
        var e = document.getElementById("com_mail_id");
        var service = e.options[e.selectedIndex].dataset.service;
        var display=service=="commercial" ? "block" : "none";
        document.querySelectorAll(".mail_com.relance").forEach(function(e){
            e.style.display=display;
        });
    }
    
    function checkinfos(){
        if(document.getElementsByName('userid')[0].value=="" || document.getElementsByName('userid')[0].value==0){
            alert("Aucun contact n'est associé à ce projet");
            return false;
        }

        var libelle=document.getElementsByName('user_projet_libelle')[0];
        if(libelle.value==""){
            alert("Veuillez remplir le libellé");
            libelle.style.backgroundColor="FFAAAA";
            libelle.focus();
            libelle.onkeyup=function(){
                this.style.backgroundColor="";
            };
            return false;
        }
        var cause = "";
        var etat = "";
        var causes=document.getElementsByName('causeperte');
        causes.forEach(function(input){
            if(input.checked){
                cause = input.value;
            }
        });
        var etats=document.getElementsByName('user_projet_etatid');
        etats.forEach(function(input){
            if(input.checked){
                etat = input.value;
            }
        });
        if(cause == "" && etat == "6"){
            alert("Veuillez choisir une cause");
            return false;
        }

        var echeance=document.getElementsByName('user_projet_echeance_livraison')[0];
        if(echeance.value=="" && !echeance.parentNode.querySelector("input[type='checkbox']").checked){
            alert("Veuillez choisir une date d'échéance");
            echeance.style.backgroundColor="FFAAAA";
            echeance.focus();
            echeance.onclick=function(){
                this.style.backgroundColor="";
            };
            return false;
        }

        return true;
    }
    
    function checkall(sender){
        var parent=sender.parentNode;
        while(parent!=null && !parent.classList.contains("suivifilter"))
            parent=parent.parentNode;

        parent.querySelectorAll("input[type=checkbox]").forEach(function(input){
            input.checked=sender.checked;
        });
        filtersuivi();
    }
    
    function filtersuivi(){
        var fourn=undefined;
        var allfourn=document.querySelectorAll("[name=filtrefourn]");
        var selectedfourn=document.querySelectorAll("[name=filtrefourn]:checked");
        if(allfourn.length>0){
            if(allfourn.length!=selectedfourn.length){
                fourn=[];
                selectedfourn.forEach(function(e){
                    fourn.push(e.value);
                });
            }
            if(selectedfourn.length==0) fourn=[];
        }
        
        var destinataire=undefined;
        var alldest=document.querySelectorAll("[name=filtredest]");
        var selecteddest=document.querySelectorAll("[name=filtredest]:checked");
        if(alldest.length>0){
            if(alldest.length!=selecteddest.length){
                destinataire=[];
                selecteddest.forEach(function(e){
                    destinataire.push(e.value);
                });
            }
            if(selecteddest.length==0) destinataire=[];
        }
        
        var interne=document.getElementsByName("filtreinterne")[0].checked?1:0;
        var search=document.getElementsByName("filtremodif")[0].checked?"infos_projet":undefined;
        
        loadsuivi(undefined,"pjt",1,<?=intval($projet->projetid); ?>,fourn,destinataire,interne,search);
    }
    
    function chkecheance(sender,input){
        var display='inline';
        var date=sender.dataset.oldvalue;
        if(sender.checked){
            display="none";
            date="";
            sender.dataset.oldvalue=input.value;
        }
        
        input.style.display=display;
        input.value=date;
    }
    
    function addconfig(configid){
        var form=document.createElement("form");
        form.method="post";
        document.body.appendChild(form);
        var action=document.createElement("input");
        action.type="hidden";
        action.name="action";
        action.value="config";
        form.appendChild(action);
        var input=document.createElement("input");
        input.type="hidden";
        input.name="configid";
        input.value=configid;
        form.appendChild(input);
        form.submit();
    }

    function rmconfig(configid){
        var form=document.createElement("form");
        form.method="post";
        document.body.appendChild(form);
        var action=document.createElement("input");
        action.type="hidden";
        action.name="action";
        action.value="rmconfig";
        form.appendChild(action);
        var input=document.createElement("input");
        input.type="hidden";
        input.name="configid";
        input.value=configid;
        form.appendChild(input);
        form.submit();
    }

    function addartvu(artid){
        var form=document.createElement("form");
        form.method="post";
        document.body.appendChild(form);
        var action=document.createElement("input");
        action.type="hidden";
        action.name="action";
        action.value="artvu";
        form.appendChild(action);
        var input=document.createElement("input");
        input.type="hidden";
        input.name="artid";
        input.value=artid;
        form.appendChild(input);
        form.submit();
    }

    function rmartvu(artid){
        var form=document.createElement("form");
        form.method="post";
        document.body.appendChild(form);
        var action=document.createElement("input");
        action.type="hidden";
        action.name="action";
        action.value="rmartvu";
        form.appendChild(action);
        var input=document.createElement("input");
        input.type="hidden";
        input.name="artid";
        input.value=artid;
        form.appendChild(input);
        form.submit();
    }

    function addcompare(artid){
        var form=document.createElement("form");
        form.method="post";
        document.body.appendChild(form);
        var action=document.createElement("input");
        action.type="hidden";
        action.name="action";
        action.value="compare";
        form.appendChild(action);
        var input=document.createElement("input");
        input.type="hidden";
        input.name="artid";
        input.value=artid;
        form.appendChild(input);
        form.submit();
    }

    function rmcompare(artid){
        var form=document.createElement("form");
        form.method="post";
        document.body.appendChild(form);
        var action=document.createElement("input");
        action.type="hidden";
        action.name="action";
        action.value="rmcompare";
        form.appendChild(action);
        var input=document.createElement("input");
        input.type="hidden";
        input.name="artid";
        input.value=artid;
        form.appendChild(input);
        form.submit();
    }

    function adddevis(facturid){
        var form=document.createElement("form");
        form.method="post";
        document.body.appendChild(form);
        var action=document.createElement("input");
        action.type="hidden";
        action.name="action";
        action.value="devis";
        form.appendChild(action);
        var input=document.createElement("input");
        input.type="hidden";
        input.name="facturid";
        input.value=facturid;
        form.appendChild(input);
        form.submit();
    }
    
    function rmdevis(facturid){
        var form=document.createElement("form");
        form.method="post";
        document.body.appendChild(form);
        var action=document.createElement("input");
        action.type="hidden";
        action.name="action";
        action.value="rmdevis";
        form.appendChild(action);
        var input=document.createElement("input");
        input.type="hidden";
        input.name="facturid";
        input.value=facturid;
        form.appendChild(input);
        form.submit();
    }

    function show_config2_info(id){
    var display=document.getElementById('config2info'+id).style.display;
    if(display=="block"){
        document.getElementById('config2info'+id).style.display="none";
        document.getElementById('config2plus'+id).src="images/plus.jpg";
    }else{
        document.getElementById('config2info'+id).style.display="block";
        document.getElementById('config2plus'+id).src="images/moins.jpg";
    }
}

function show_compare2_info(id){
    var display=document.getElementById('compare2info'+id).style.display;
    if(display=="block"){
        document.getElementById('compare2info'+id).style.display="none";
        document.getElementById('compare2plus'+id).src="images/plus.jpg";
    }else{
        document.getElementById('compare2info'+id).style.display="block";
        document.getElementById('compare2plus'+id).src="images/moins.jpg";
    }
}
</script>

<?php
include "footer.php";
?>
