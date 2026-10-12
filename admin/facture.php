<?php
ini_set("memory_limit","600M");
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
$numero_min_facture = defined(NUM_FACTURE) ? NUM_FACTURE : 556500;
ini_set("display_errors",1);
require_once(SITE_PATH."/class/facture.class.php");
require_once(SITE_PATH."/class/DAOgarantie.class.php");
require_once(SITE_PATH."/class/garantie.class.php");
require_once(SITE_PATH."/class/DAOfournisseur.class.php");
require_once(SITE_PATH."/class/DAOlignefacturegarantie.class.php");
require_once(SITE_PATH."/class/stock.class.php");
require_once(SITE_PATH."/class/frais_port.php");
require_once(SITE_PATH."/class/installateur.class.php");
require_once(SITE_PATH."/class/formulaire.class.php");
require_once(BACK_PATH."/dissociation.php");
require_once(SITE_PATH."/class/email_client.php");
require_once(BACK_PATH."/sav.class.php");
require_once(BACK_PATH."/adv/adv.class.php");
require_once(BACK_PATH."/export/xlexport.php");
require_once(SITE_PATH."/class/fournisseur.class.php");
require_once(SITE_PATH."/class/cocktel.class.php");
require_once(SITE_PATH."/class/fonctions.php");
require_once(SITE_PATH."/class/projet.class.php");
require_once(SITE_PATH."/admin/marge.php");



$superusers = array("philippe", "benoit","jb");
$supercom = array("alexis"); // droits pour les commerciaux \\

//***********************************\\
// DEBUT TRAITEMENTS STATUS COMMANDE \\
//***********************************\\
$affiche_validation_finale = false;
$affiche_refus_final = false;
$affiche_expedition_finale = false;
$affiche_check_facture = false;
if(isset($_POST['action']) && !empty($_POST['action'])){
    //inséré dans le mail pour pour tracking email
    $tracking = "<div style=\"display:none;font-size:1px;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;\">#CDE$facturid#</div>";

    switch($_POST['action']){

        //VALIDATION DE COMMANDE (ETAPE 1)
        case 'valider':
        case 'validersp':
        case 'paiement':

            $affiche_validation_finale = true;

            if($_POST['action'] == 'valider'){
                $finale_action = 'valider2';
            } else if($_POST['action'] == 'validersp'){
                $finale_action = 'validersp2';
            } else {
                $finale_action = 'paiement2';
                $affiche_check_facture = true;
            }

            break;

        case 'refuser':

            $affiche_refus_final = true;

            break;

        case 'expedier':
        case 'expediersp':
            $facture = new facture($DB_site2,$facturid);
            $arr_pays=array(57,181,111);

            ///vérifie s'il manque des contrats de garantie qui n'auraient pas été commandés :
            $facture->lignes();
            $cmpt_commande=0;
            $cmpt_gar=0;
            foreach($facture->lignes as $lfid=>$lignefacture){
                //les garanties qui doivent être commandées :
                $allGarantieCompByLigneFac=$DB_site2->query("SELECT lfg.*, g.cumul, g.garantie_client_id FROM lignefacturegarantie lfg INNER JOIN garantie g ON g.garantieid=lfg.garantieid WHERE lfg.lignefactureid=".$lfid." AND utilisation_garantie=1 ORDER BY cumul DESC, lfg.duree DESC");
                foreach($allGarantieCompByLigneFac as $garantie){
                    $eflgarFourn=$DB_site2->query_first("SELECT efl.* FROM envoi_fournisseur_ligne efl INNER JOIN envoi_fournisseur ef ON(ef.id=efl.efid) WHERE ligne_facture_id=".$lfid." AND destinataire='".$GarantieCompByLigneFac['fournisseur']."' AND destinataire<>''");
                    if($eflgarFourn['id']==""){
                        $advgar=new adv($DB_site2);
                        $result=$advgar->load_from_lignefactureid($lfid,"garantie");
                        //les garanties qui ont été commandées :
                        if($result && $advgar->destinataire==$garantie['fournisseur']){
                            $cmpt_commande++;
                        }
                    }
                    $cmpt_gar++;
                }
            }

            if($facture->incoterms == '' && !in_array($facture->paysid,$arr_pays)) {
                echo "<script language='javascript'>if(confirm('Vous devez renseigner un incoterm avant de pouvoir expédier la commande')){
            document.location='facture.php?action=editer&facturid=".$facturid."&lst_trietat=';
        }else{document.location='facture.php?action=editer&facturid=".$facturid."&lst_trietat=';}</script>";
            }
            elseif($cmpt_gar > $cmpt_commande){

                echo "<script language='javascript'>
        if(confirm('Attention, contrat de garantie non commandé, continuer ?')==false){
            document.location='facture.php?action=editer&facturid=".$facturid."';
        }

        </script>";
                $affiche_expedition_finale = true;
                $finale_action = $_POST['action'] == 'expedier' ? 'doexpedier' : 'doexpediersp';
            }
            ///vérifie si l'accord du client doit être donné ou non pour expédier :
            elseif($facture->exp_accord_client == 1 && $facture->date_exp_accord_client == 0){
                echo "<script language='javascript'>alert('Attention. Cette commande ne peut pas être expédiée sans l’accord du client ');
        document.location='facture.php?action=editer&facturid=".$facturid."';</script>";
            }
            else {
                $affiche_expedition_finale = true;

                $finale_action = $_POST['action'] == 'expedier' ? 'doexpedier' : 'doexpediersp';
            }
            break;

        //VALIDATION DE COMMANDE (ETAPE 2)
        case 'valider2':
        case 'validersp2':

            $statut_cmd = $_POST['action'] == 'valider2' ? 1 : 7;

            $sql = "UPDATE facture SET";
            $sql .= " valide = '".$statut_cmd."'";
            $sql .= " WHERE facturid = '".$facturid."'";
            $DB_site2->query($sql);

            $facture = new facture($DB_site2,$facturid);

            $template = new template($facture->utilisateur->langue,$DB_site2);

            $template->changesite($facture->site_code,$facture->utilisateur->langue);
            $template->pro = ($facture->utilisateur->raisonsociale != "" && $template->site_code() == "base");

            //Envoi du mail si l'on souhaite
            if(isset($_POST['send_mail']) && $_POST['send_mail'] == 1){

                if($_POST['action'] == 'validersp2'){
                    $mail_type = 'paiementapresexpok';
                } else {
                    //Pour les paiement par "Multipaiement", on envoie un email légèrement différent
                    if($facture->moyenid == 99){
                        $mail_type = 'paiementmultirecu';
                    } else {
                        $mail_type = 'paiementrecu';
                    }
                }

                $vars = array(
                    "nom"       => $facture->utilisateur->nom,
                    "prenom"    => $facture->utilisateur->prenom,
                    "facturid"  => $facturid,
                    "prixtotal" => $template->price_format($facture->totalttc),
                    "tracking"  => $tracking
                );
                $ec = new email_client($DB_site2,$template,null,$mail_type,$vars);

                $mail = new email();
                $mail->stylesheet($rootpath."/configurations/mails.css");
                $mail->destinataire = $facture->utilisateur->mail;
                $mail->destname     = $facture->utilisateur->prenom." ".$facture->utilisateur->nom;
                $mail->to           = ["pr@mon-espace-cuisson.com", 'webmaster@ma-cave-a-vin.fr'];
                $mail->expediteur   = $template->translate("/site/[site_code]/mail_expediteur");
                $mail->expname      = $template->translate("/site/nom");
                $mail->copymode     = "bcc";
                $mail->subject      = $ec->subject;
                $mail->body         = $ec->body;

                if(!in_array($facture->provenance,array("sav","avoir","mp"))){

                    $mail->sendmail();

                    $sql = "INSERT INTO mail_outbox SET";
                    $sql .= " date         = ".time()."";
                    $sql .= ", utilisateur = '".$user_info['username']."'";
                    $sql .= ", `to`        = '".$mail->destinataire."'";
                    $sql .= ", `from`        = '".$mail->expediteur."'";
                    $sql .= ", subject     = '".addslashes($mail->subject)."'";
                    $sql .= ", body        = '".addslashes($mail->body)."'";
                    $sql .= ", facturid    = '".$facture->facturid."'";
                    $sql .= ", userid      = '".$facture->utilisateur->userid."'";
                    $sql .= ", uniqueid    ='".$mail->uniqueid."'";
                    $sql .= ", compta      ='oui'";
                    $sql .= ", auto        = 0";
                    $DB_site2->query($sql);

                }

            }

            $sql = "INSERT INTO suivi_commande SET";
            $sql .= " facturid    = ".$facturid;
            $sql .= ", date       = ".time();
            $sql .= ", commercial = '".$user_info['username']."'";
            $sql .= ", suivi      = 'Paiement validé ".($_POST['action'] == 'validersp2' ? 'sans paiement' : '')."'";
            $DB_site2->query($sql);

            $diss = new dissociation($DB_site2,$facturid);

            //Redirection vers la page de traitement de commande initiale
            header('Location: facture.php?action=editer&facturid='.$_POST['facturid'].'&lst_trietat=');
            exit;

            break;

        //REFUS DE COMMMANDE
        case 'dorefuser':

            $facture = new facture($DB_site2,$facturid);

            $nom    = $facture->utilisateur->nom;
            $prenom = $facture->utilisateur->prenom;

            $sql = "UPDATE facture SET";
            $sql .= " valide = '2'";
            $sql .= " WHERE facturid = '".$facturid."'";
            $DB_site2->query($sql);

            // Si moyen != " Abandon " et que l'on souhaite avertir le client
            if ($facture->moyenid != "0" && isset($_POST['send_mail']) && $_POST['send_mail'] == 1){

                $template = new template($facture->utilisateur->langue,$DB_site2);

                $template->changesite($facture->site_code,$facture->utilisateur->langue);
                $template->pro = ($facture->utilisateur->raisonsociale != "" && $template->site_code() == "base");

                $vars = array(
                    "nom"      => $facture->utilisateur->nom,
                    "prenom"   => $facture->utilisateur->prenom,
                    "facturid" => $facturid,
                    "tracking" => $tracking
                );
                $ec = new email_client($DB_site2,$template,null,"commandeannulee",$vars);

                $mail=new email();
                $mail->stylesheet($rootpath."/configurations/".$host."/mails.css");
                $mail->destinataire = $facture->utilisateur->mail;
                $mail->destname     = $facture->utilisateur->nom." ".$facture->utilisateur->prenom;
                $mail->expname      = $template->translate("/site/nom");
                $mail->expediteur   = $template->translate("/site/[site_code]/mail_expediteur");
                $mail->copymode     = "bcc";
                $mail->to           = ["pr@mon-espace-cuisson.com", 'webmaster@ma-cave-a-vin.fr'];
                $mail->subject      = $ec->subject;
                $mail->body         = $ec->body;
                $mail->sendmail();

                $sql = "INSERT INTO mail_outbox SET";
                $sql .= " date         = ".time()."";
                $sql .= ", utilisateur = '".$user_info['username']."'";
                $sql .= ", `to`        = '".$mail->destinataire."'";
                $sql .= ", `from`        = '".$mail->expediteur."'";
                $sql .= ", subject     = '".addslashes($mail->subject)."'";
                $sql .= ", body        = '".addslashes($mail->body)."'";
                $sql .= ", facturid    = '".$facture->facturid."'";
                $sql .= ", userid    = '".$facture->utilisateur->userid."'";
                $sql .= ", uniqueid    = '".$mail->uniqueid."'";
                $sql .= ", compta      = 'oui'";
                $sql .= ", auto        = 0";
                $DB_site2->query($sql);
            }

            //Insertion dans le suivi
            $sql = "INSERT INTO suivi_commande SET";
            $sql .= " facturid    = ".$facturid."";
            $sql .= ", date       = ".time()."";
            $sql .= ", commercial = '".$user_info['username']."'";
            $sql .= ", suivi      = 'Commande annulée'";
            $DB_site2->query($sql);

            //Redirection vers la page de traitement de commande initiale
            header('Location: facture.php?action=editer&facturid='.$_POST['facturid'].'&lst_trietat=');
            exit;

            break;

        //EXPEDITION DE COMMMANDE
        case 'doexpedier':
        case 'doexpediersp':


            $facture = new facture($DB_site2,$facturid);


////SOFINCO
            // A l'expédition si paiement sofinco, on envoie une demande de financement à sofinco pour la commande
            if($facture->moyenid == 17){
                //$apiUrl="https://r7weblong.transcred.com/websrv/index.asp";
                $apiUrl="https://weblong.transcred.com/websrv/index.asp";
                $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
    <Service ServiceName=\"FLUX_D\">
    <IN>
    <DossiersCollection>
    <Dossier DossierNumber=\"".$facture->no_dossier."\"/>
    </DossiersCollection>
    </IN>
    </Service>";
                $ch = curl_init();
                curl_setopt ($ch, CURLOPT_URL,$apiUrl);
                curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
                curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
                curl_setopt($ch, CURLOPT_TIMEOUT, 20);
                $data = curl_exec($ch);
                curl_close($ch);

                $data_xml=simplexml_load_string($data, 'SimpleXMLElement');
                $json = json_encode($data_xml);
                $array = json_decode($json,TRUE);
                $array_dossiers[]=$array['OUT']['DossiersCollection']['Dossier']['@attributes'];
                $array_dossiers=array_filter(array_unique($array_dossiers,SORT_REGULAR));
                foreach($array_dossiers as $dossier) {
                    /// on récupère le dossier seulement s'il a le statut 'dossier à financer'
                    if($dossier['DossierStatus']=='ATT' || $dossier['DossierStatus']=='AFI'){
                        $xml2="<Service ServiceName=\"FLUX_B\">
            <IN>
            <DossiersCollection>
            <Dossier DossierNumber=\"".$dossier['DossierNumber']."\" CommandNumber=\"".$dossier['CommandNumber']."\"/>
            </DossiersCollection>
            </IN>
            </Service>";

                        /// on envoie à sofinco la demande de financement correspondant au bon dossier
                        $ch = curl_init();
                        curl_setopt ($ch, CURLOPT_URL,$apiUrl);
                        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml2);
                        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
                        $data = curl_exec($ch);
                        curl_close($ch);

                        $data_xml=simplexml_load_string($data, 'SimpleXMLElement');
                        $json = json_encode($data_xml);
                        $array = json_decode($json,TRUE);
                        $array_retour[]=$array['OUT']['DossiersCollection']['Dossier']['@attributes'];
                        $array_retour=array_filter(array_unique($array_retour,SORT_REGULAR));
                        /// envoie des erreurs
                        foreach($array_retour as $retour) {
                            if($retour['DossierStatus'] == "REF" || $retour['DossierStatus'] == "ANN" ||$retour['DossierStatus'] == "NFN" ||$retour['DossierStatus'] == "ERR"){
                                $message="<br>Dossier n°".$retour['DossierNumber'].". Commande n°".$retour['CommandNumber'].". Statut: ".$retour['DossierStatus'];
                                $mail=new email();
                                $mail->destinataire	= 'cecile@mon-espace-cuisson.com';
                                $mail->expediteur	= "contact@mon-espace-cuisson.com";
                                $mail->to 			= ["pr@mon-espace-cuisson.com", 'webmaster@ma-cave-a-vin.fr'];
                                $mail->subject		= "Erreur demande financement sofinco #CDE".$dossier['CommandNumber']."#";
                                $mail->body			= "Le dossier suivant présente des erreurs :".$message;
                                $mail->sendmail();
                                $suivi="Sofinco : Dossier n°".$dossier['DossierNumber'].". Commande n°".$dossier['CommandNumber'].". Statut: ".$dossier['DossierStatus'];
                            }
                            else {
                                $suivi="Sofinco : Dossier n°".$dossier['DossierNumber'].". Commande n°".$dossier['CommandNumber'].". Demande de financement envoyé.";
                            }
                        }
                        $DB_site2->query("INSERT INTO suivi_commande SET facturid=".$dossier['CommandNumber'].", date=".time().", commercial='', suivi='$suivi'");
                    }
                }
            }
////FIN SOFINCO


//On met à jour la date d'expédition
            $sqlUpdateFacture = "UPDATE facture SET";
            $sqlUpdateFacture .= " dateexpedition = NOW()";
            $sqlUpdateFacture .= " WHERE facturid = ".$facturid;
            $DB_site2->query($sqlUpdateFacture);

            //Insertion dans le suivi
            $sql = "INSERT INTO suivi_commande SET";
            $sql .= " facturid    = ".$facturid."";
            $sql .= ", date       = ".time()."";
            $sql .= ", commercial = '".$user_info['username']."'";
            $sql .= ", suivi      = 'Commande expédiée ".($_POST['action'] == 'doexpediersp' ? 'sans paiement' : '')."'";
            $DB_site2->query($sql);

            $statut_cmd = $_POST['action'] == 'doexpedier' ? 5 : 8;

            //Si facture inexistante, on la crée au format PDF
            if($facture->numerofacture == $numero_min_facture || intval($facture->numerofacture) == 0){
                $code = "" ;
                $chars = array ("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z") ;
                $count = count($chars) - 1;
                srand((float) microtime() * 1000000);

                for ($i = 0; $i < 8; $i++){
                    $code .= $chars[rand(0, $count)];
                }

                //A l'expédition, on défini le numéro de facture réel de la facture
                //$rs_lock=$DB_site2->query('LOCK TABLE facture WRITE');
                $sql = "SELECT MAX(numerofacture) as numerofacture";
                $sql .= " FROM facture";
                $rs_numerofacture = $DB_site2->query_first($sql);

                $num_facture = $rs_numerofacture['numerofacture'] + 1;

                $sql = "UPDATE facture SET";
                $sql .= " valide = '".$statut_cmd."'";
                $sql .= ", passefacture = '".$code."'";
                $sql .= ", numerofacture = '".$num_facture."'";
                $sql .= " WHERE facturid = '".$facturid."'";
                $result = $DB_site2->query($sql);

                $_GET['join']  = 1;
                $_GET['nopub'] = 1;
                $passe = $code;

                $arrContextOptions=array(
                    "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    ),
                );
                $facturepdf = file_get_contents("http://".$_SERVER['HTTP_HOST']."/facture2.php?facturid=".$facturid."&passe=".$code,FALSE,stream_context_create($arrContextOptions));
                try{
                    $f = fopen("../configurations/$host/factures/".$facturid.".pdf","w");
                    if($f != false){
                        fputs($f, $facturepdf);
                        fclose($f);
                    }
                } catch(Exception $e){
                    print "impossible d'ouvrir la facture";
                }

            } else { //Si la facture existe déjà, on met uniquement à jour le statut

                $sql = "UPDATE facture SET";
                $sql .= " valide = '".$statut_cmd."'";
                $sql .= " WHERE facturid = '".$facturid."'";
                $result = $DB_site2->query($sql);

            }

            //Décrémentation du stock
            $stock = new stock($DB_site2);
            $stockitems = $stock->search(null,null,"facture ".$facturid,2);
            foreach($stockitems as $item){
                $item->state=3;
                $item->update();
            }

            //Envoi du contrat platine home le cas échéant
            foreach ($facture->lignes as $lignefacture) {
                $abonnement = $DB_site2->query("SELECT * FROM platinehome WHERE lignefactureid = '".$lignefacture->lignefactureid."'");
                while($abo = $DB_site2->fetch_array($abonnement)){
                    $phid = $abo['phid'];
                    require '../platinehome_declare.php';
                }
            }


            $template = new template($facture->utilisateur->langue,$DB_site2);
            $template->changesite($facture->site_code,$facture->utilisateur->langue);
            $template->pro = ($facture->utilisateur->raisonsociale != "" && $template->site_code() == "base");

            //Si expédition sans paiement, passé par le site (non sav, non market place...)
            if($_POST['action'] == 'doexpediersp' && $facture->provenance == "" && $facture->moyenid != 17 && $facture->moyenid != 18){

                if(isset($_POST['add_invoice']) && $_POST['add_invoice'] == 1){

                    $vars = array(
                        "nom"          => $facture->utilisateur->nom,
                        "prenom"       => $facture->utilisateur->prenom,
                        "facturid"     => $facturid,
                        "prixtotal"    => $template->price_format($facture->totalttc),
                        "tracking"  => $tracking
                    );
                    //$ec = new email_client($DB_site2,$template,null,"expeditionsp",$vars);
                    $ec = new email_client($DB_site2,$template,null,"expedition",$vars);

                    $mail = new email();
                    $mail->stylesheet($rootpath."/configurations/".$host."/mails.css");
                    $mail->destinataire = $facture->utilisateur->mail;
                    $mail->destname     = $facture->utilisateur->prenom." ".$facture->utilisateur->nom;
                    $mail->to           = ["pr@mon-espace-cuisson.com", 'webmaster@ma-cave-a-vin.fr'];
                    $mail->expediteur   = $template->translate("/site/[site_code]/mail_expediteur");
                    $mail->expname      = $template->translate("/site/nom");
                    $mail->copymode     = "bcc";
                    $mail->subject      = $ec->subject;
                    $mail->body         = $ec->body;

                    //aide_fiscale($mail,$facture);
                    $mail->attachfile("https://".$_SERVER['HTTP_HOST']."/facture2.php?facturid=".$facture->facturid."&passe=$code",$template->translate("/client/facture","F")." ".$facture->facturid.".pdf","application/pdf");

                    if($facture->utilisateur->mail != ''){
                        $mail->sendmail();
                    } else {
                        $sql = "INSERT INTO suivi_commande SET";
                        $sql .= " facturid    = ".$facturid."";
                        $sql .= ", date       = ".time()."";
                        $sql .= ", commercial = '".$user_info['username']."'";
                        $sql .= ", suivi      = 'Pas d\'email d\'expédition envoyé car aucune adresse email'";
                        $DB_site2->query($sql);
                    }
                } else {

                    $sql = "INSERT INTO suivi_commande SET";
                    $sql .= " facturid    = ".$facturid."";
                    $sql .= ", date       = ".time()."";
                    $sql .= ", commercial = '".$user_info['username']."'";
                    $sql .= ", suivi      = 'Pas d\'envoi de facture'";
                    $DB_site2->query($sql);
                }

                $sql = "INSERT INTO mail_outbox SET";
                $sql .= " date         = ".time()."";
                $sql .= ", utilisateur = '".$user_info['username']."'";
                $sql .= ", `to`        = '".$mail->destinataire."'";
                $sql .= ", `from`        = '".$mail->expediteur."'";
                $sql .= ", subject     = '".addslashes($mail->subject)."'";
                $sql .= ", body        = '".addslashes($mail->body)."'";
                $sql .= ", facturid    = '".$facture->facturid."'";
                $sql .= ", userid    = '".$facture->utilisateur->userid."'";
                $sql .= ", uniqueid    = '".$mail->uniqueid."'";
                $sql .= ", compta      = 'non'";
                $sql .= ", auto        = 0";
                $DB_site2->query($sql);
            }

            //Si expédition sans paiement, passé par market place
            if($_POST['action'] == 'doexpediersp' && $facture->provenance == "mp"){

                if(isset($_POST['add_invoice']) && $_POST['add_invoice'] == 1){
                    $vars = array(
                        "nom"      => $facture->utilisateur->nom,
                        "prenom"   => $facture->utilisateur->prenom,
                        "facturid" => $facturid,
                        "tracking" => $tracking
                    );
                    //Dans le cas d'une commande market place, on envoie le mail d'expédition classique car le paiement a déjà été effectué sur la marketplace
                    $ec = new email_client($DB_site2,$template,null,"expedition",$vars);

                    $mail = new email();
                    $mail->stylesheet($rootpath."/configurations/".$host."/mails.css");
                    $mail->destinataire = $facture->utilisateur->mail;
                    $mail->destname     = $facture->utilisateur->prenom." ".$facture->utilisateur->nom;
                    $mail->to           = ["pr@mon-espace-cuisson.com", 'webmaster@ma-cave-a-vin.fr'];
                    $mail->expediteur   = $template->translate("/site/[site_code]/mail_expediteur");
                    $mail->expname      = $template->translate("/site/nom");
                    $mail->copymode     = "bcc";
                    $mail->subject      = $ec->subject;
                    $mail->body         = $ec->body;

                    //aide_fiscale($mail,$facture);
                    $mail->attachfile("https://".$_SERVER['HTTP_HOST']."/facture2.php?facturid=".$facture->facturid."&passe=$code",$template->translate("/client/facture","F")." ".$facture->facturid.".pdf","application/pdf");

                    if($facture->utilisateur->mail != ''){
                        $mail->sendmail();
                    } else {
                        $sql = "INSERT INTO suivi_commande SET";
                        $sql .= " facturid    = ".$facturid."";
                        $sql .= ", date       = ".time()."";
                        $sql .= ", commercial = '".$user_info['username']."'";
                        $sql .= ", suivi      = 'Pas d\'email d\'expédition envoyé car aucune adresse email'";
                        $DB_site2->query($sql);
                    }
                } else {

                    $sql = "INSERT INTO suivi_commande SET";
                    $sql .= " facturid    = ".$facturid."";
                    $sql .= ", date       = ".time()."";
                    $sql .= ", commercial = '".$user_info['username']."'";
                    $sql .= ", suivi      = 'Pas d\'envoi de facture'";
                    $DB_site2->query($sql);
                }

                $sql = "SELECT destinataire,trackid";
                $sql .= " FROM envoi_fournisseur";
                $sql .= " WHERE facturid = ".$facture->facturid;
                $sql .= " AND type = 'trspt'";
                $sql .= " AND destinataire <> '0'";
                $req_envoi = $DB_site2->query($sql);


                while($envoi_trspt = $DB_site2->fetch_array($req_envoi)){
                    $transporteur .= $envoi_trspt['destinataire']." N° ".$envoi_trspt['trackid']." ";
                }

                $mail = new email();
                $mail->destinataire = defined('MAIL_TRANSPORT') ? MAIL_TRANSPORT : 'trspt@acilyon.com';
                $mail->expediteur   = defined('MAIL_CONTACT') ? MAIL_CONTACT : 'contact@mon-espace-cuisson.com';
                $mail->expname      = defined('NOM_CONTACT') ? NOM_CONTACT : 'Mon Espace Cuisson';
                $mail->subject      = "Expédition commande market place #CDE$facturid#";
                $mail->body         = ""
                    . "Commande ".$facture->facturid
                    . "<br>".$facture->utilisateur->raisonsociale." ".$facture->utilisateur->nom." ".$facture->utilisateur->prenom
                    . "<br>Commande ".$facture->mp_name." ".$facture->mp_facturid
                    . "<br>Expédition le ".date("d/m/Y",$facture->dateexpedition)
                    . "<br>Transporteur : ".trim($transporteur);

                $mail->sendmail();

                $sql = "INSERT INTO mail_outbox SET";
                $sql .= " date         = ".time()."";
                $sql .= ", utilisateur = '".$user_info['username']."'";
                $sql .= ", `to`        = '".$mail->destinataire."'";
                $sql .= ", `from`      = '".$mail->expediteur."'";
                $sql .= ", subject     = '".addslashes($mail->subject)."'";
                $sql .= ", body        = '".addslashes($mail->body)."'";
                $sql .= ", facturid    = '".$facture->facturid."'";
                $sql .= ", userid      = '".$facture->utilisateur->userid."'";
                $sql .= ", uniqueid    = '".$mail->uniqueid."'";
                $sql .= ", compta      = 'non'";
                $sql .= ", auto        = 0";
                $DB_site2->query($sql);

            }

            //Email envoyé uniquement dans le cas d'une expédition classique (avec paiement)
            if($_POST['action'] == 'doexpedier' || ($_POST['action'] == 'doexpediersp' && ($facture->moyenid == 17 || $facture->moyenid == 18))){

                //Vérification de la demande de transmission de facture
                if(isset($_POST['add_invoice']) && $_POST['add_invoice'] == 1){
                    $vars = array(
                        "nom"      => $facture->utilisateur->nom,
                        "prenom"   => $facture->utilisateur->prenom,
                        "facturid" => $facturid,
                        "tracking" => $tracking
                    );
                    $ec = new email_client($DB_site2,$template,null,"expedition",$vars);

                    $mail = new email();
                    $mail->destinataire = $facture->utilisateur->mail;
                    $mail->destname     = $facture->utilisateur->prenom." ".$facture->utilisateur->nom;
                    $mail->to           = ["pr@mon-espace-cuisson.com", 'webmaster@ma-cave-a-vin.fr'];
                    $mail->expediteur   = $template->translate("/site/[site_code]/mail_expediteur");
                    $mail->expname      = $template->translate("/site/nom");
                    $mail->copymode     = "bcc";
                    $mail->subject      = $ec->subject;
                    $mail->body         = $ec->body;

                    //aide_fiscale($mail,$facture);
                    $mail->attachfile("http://".$_SERVER['HTTP_HOST']."/facture2.php?facturid=".$facture->facturid."&passe=".$code, $template->translate("/client/facture","F")." ".$facture->facturid.".pdf","application/pdf");

                    if($facture->utilisateur->mail != ''){
                        $mail->sendmail();
                    } else {
                        $sql = "INSERT INTO suivi_commande SET";
                        $sql .= " facturid    = ".$facturid."";
                        $sql .= ", date       = ".time()."";
                        $sql .= ", commercial = '".$user_info['username']."'";
                        $sql .= ", suivi      = 'Pas d\'email d\'expédition envoyé car aucune adresse email'";
                        $DB_site2->query($sql);
                    }

                    $sql = "INSERT INTO mail_outbox SET";
                    $sql .= " date         = ".time()."";
                    $sql .= ", utilisateur = '".$user_info['username']."'";
                    $sql .= ", `to`        = '".$mail->destinataire."'";
                    $sql .= ", `from`        = '".$mail->expediteur."'";
                    $sql .= ", subject     = '".addslashes($mail->subject)."'";
                    $sql .= ", body        = '".addslashes($mail->body)."'";
                    $sql .= ", pj          = 'Facture ".$facture->facturid.".pdf'";
                    $sql .= ", facturid    = '".$facture->facturid."'";
                    $sql .= ", userid      = '".$facture->utilisateur->userid."'";
                    $sql .= ", uniqueid    = '".$mail->uniqueid."'";
                    $sql .= ", compta      = 'non'";
                    $sql .= ", auto        = 0";
                    $DB_site2->query($sql);
                } else {

                    $sql = "INSERT INTO suivi_commande SET";
                    $sql .= " facturid    = ".$facturid."";
                    $sql .= ", date       = ".time()."";
                    $sql .= ", commercial = '".$user_info['username']."'";
                    $sql .= ", suivi      = 'Pas d\'envoi de facture'";
                    $DB_site2->query($sql);
                }
            }

            // Option "Transmettre les consignes de réception" cochée

            ////////////////////////////
            // CONSIGNES DE RECEPTION //
            ////////////////////////////

            if(isset($_POST['add_consignes']) && $_POST['add_consignes'] == 1){

                $vars=array(
                    "nom"      => $facture->nom,
                    "prenom"   => $facture->prenom,
                    "facturid" => $facturid,
                    "tracking" => $tracking
                );
                $ec = new email_client($DB_site2,$template,null,"consignes",$vars);

                $mail = new email();
                $mail->stylesheet($rootpath."/configurations/".$host."/mails.css");
                $mail->destinataire = $facture->mail;
                $mail->destname     = $facture->prenom." ".$facture->nom;
                $mail->expediteur   = $template->translate("/site/base/mail_expediteur");
                $mail->expname      = $template->translate("/site/nom");
                $mail->to           = ["pr@mon-espace-cuisson.com", 'webmaster@ma-cave-a-vin.fr'];
                $mail->copymode     = "bcc";
                $mail->subject      = $ec->subject;
                $mail->body         = $ec->body;

                if($facture->mail != ''){
                    $mail->sendmail();
                } else {
                    $sql = "INSERT INTO suivi_commande SET";
                    $sql .= " facturid    = ".$facturid."";
                    $sql .= ", date       = ".time()."";
                    $sql .= ", commercial = '".$user_info['username']."'";
                    $sql .= ", suivi      = 'Pas de consignes envoyées car aucune adresse email'";
                    $DB_site2->query($sql);
                }

                $sql = "INSERT INTO mail_outbox SET";
                $sql .= " date         = ".time()."";
                $sql .= ", utilisateur = '".$user_info['username']."'";
                $sql .= ", `to`        = '".$mail->destinataire."'";
                $sql .= ", `from`        = '".$mail->expediteur."'";
                $sql .= ", subject     = '".addslashes($mail->subject)."'";
                $sql .= ", body        = '".addslashes($mail->body)."'";
                $sql .= ", facturid    = '".$facture->facturid."'";
                $sql .= ", userid      = '".$facture->utilisateur->userid."'";
                $sql .= ", uniqueid    = '".$mail->uniqueid."'";
                $sql .= ", compta      = 'non'";
                $sql .= ", auto        = 0";
                $DB_site2->query($sql);

            } else {

                $sql = "INSERT INTO suivi_commande SET";
                $sql .= " facturid    = ".$facturid."";
                $sql .= ", date       = ".time();
                $sql .= ", commercial = '".$user_info['username']."'";
                $sql .= ", suivi      = 'Pas d\'envoi de mail consignes de reception'";
                $DB_site2->query($sql);
            }

            if(isset($_POST['consignes'])){
                foreach($_POST['consignes'] as $efid=>$mailname){
                    if($mailname=='fourn'){
                        mail_livraison("fourn",$efid);
                    }
                    elseif($mailname=='multi'){
                        mail_livraison("multi",$efid);
                    }
                    elseif($mailname=='Retrait par le client'){
                        mail_livraison("Retrait par le client",$efid);
                    }
                    else{
                        mail_livraison("transporteur_livraison",$efid);
                    }
                }
            }


            $nodeb = array(57,181,172);
            $tva_cmd = intval($facture->totalttc - $facture->totalht);

            if(($_POST['action'] == 'doexpedier' || $_POST['action'] == 'doexpediersp') && $facture->utilisateur->tvaintracom != "" && !in_array($facture->utilisateur->paysid, $nodeb) && $facture->totalttc != 0 && $tva_cmd == 0){

                if($facture->utilisateur->apply_intracom) debmail($facture);

            }

            //Redirection vers la page de traitement de commande initiale
            header('Location: facture.php?action=editer&facturid='.$_POST['facturid'].'&lst_trietat=');
            exit;
            break;

        //VALIDATION DE REGLEMENT D'UNE COMMANDE
        case 'paiement2':

            $sql = "UPDATE facture SET";
            $sql .= " valide = '5'";
            $sql .= " WHERE facturid = '".$facturid."'";
            $DB_site2->query($sql);

            $facture = new facture($DB_site2,$facturid);

            $emailcode = 'paiementapresexprecu';
            if($facture->provenance == "mp") $emailcode='paiementapresexp';

            if(isset($_POST['send_mail']) && $_POST['send_mail'] == 1){

                $template = new template($facture->utilisateur->langue,$DB_site2);

                $template->changesite($facture->site_code,$facture->utilisateur->langue);
                $template->pro = ($facture->utilisateur->raisonsociale != "" && $template->site_code() == "base");

                $vars = array(
                    "nom"      => $facture->utilisateur->nom,
                    "prenom"   => $facture->utilisateur->prenom,
                    "facturid" => $facturid,
                    "tracking" => $tracking
                );
                $ec = new email_client($DB_site2,$template,null,$emailcode,$vars);

                $mail = new email();
                $mail->stylesheet($rootpath."/configurations/".$host."/mails.css");
                $mail->destinataire = $facture->utilisateur->mail;
                $mail->destname     = $facture->utilisateur->prenom." ".$facture->utilisateur->nom;
                $mail->to           = ["pr@mon-espace-cuisson.com", 'webmaster@ma-cave-a-vin.fr'];
                $mail->expediteur   = $template->translate("/site/[site_code]/mail_expediteur");
                $mail->expname      = $template->translate("/site/nom");
                $mail->copymode     = "bcc";
                $mail->subject      = $ec->subject;
                $mail->body         = $ec->body;

                if(isset($_POST['add_invoice']) && $_POST['add_invoice'] == 1){

                    $mail->attachfile("https://".$_SERVER['HTTP_HOST']."/facture2.php?facturid=".$facture->facturid."&passe=".$facture->passefacture,"Facture ".$facture->facturid.".pdf","application/pdf");
                }

                $mail->sendmail();

                $sql = "INSERT INTO mail_outbox SET";
                $sql .= " date         = ".time()."";
                $sql .= ", utilisateur = '".$user_info['username']."'";
                $sql .= ", `to`        = '".$mail->destinataire."'";
                $sql .= ", `from`        = '".$mail->expediteur."'";
                $sql .= ", subject     = '".addslashes($mail->subject)."'";
                $sql .= ", body        = '".addslashes($mail->body)."'";
                $sql .= ", pj          = 'facture ".$facturid.".pdf'";
                $sql .= ", facturid    = '".$facture->facturid."'";
                $sql .= ", userid      = '".$facture->utilisateur->userid."'";
                $sql .= ", uniqueid    = '".$mail->uniqueid."'";
                $sql .= ", compta      = 'oui'";
                $sql .= ", auto        = 0";
                $DB_site2->query($sql);

            }

            $sql = "INSERT INTO suivi_commande SET";
            $sql .= " facturid    = ".$facturid."";
            $sql .= ", date       = ".time()."";
            $sql .= ", commercial = '".$user_info['username']."'";
            $sql .= ", suivi      = 'Paiement validé'";
            $DB_site2->query($sql);

            //Redirection vers la page de traitement de commande initiale
            header('Location: facture.php?action=editer&facturid='.$_POST['facturid'].'&lst_trietat=');
            exit;

            break;

        //RENVOI DE CONFIRMATION DE COMMANDE
        case 'mailconfirmation':

            include(SITE_PATH."/mailconfirmation.php");

            $DB_site = $DB_site2;
            $template = new template($facture->utilisateur->langue,$DB_site2);
            $f = new facture($DB_site2,$facturid);
            mailconfirmation($f->moyenid,$f,null,$template);
            //Si commande à l'étranger et qu'un article n'a pas de code douanier ni d'origine enregistrée
            verif_export($f,$template);

            //Redirection vers la page de traitement de commande initiale
            header('Location: facture.php?action=editer&facturid='.$_POST['facturid'].'&lst_trietat=');
            exit;

            break;

        //RENVOI DE LA FACTURE
        case 'mailfacture':
            $facture = new facture($DB_site2,$facturid);
            $vars = array(
                "nom"      => $facture->utilisateur->nom,
                "prenom"   => $facture->utilisateur->prenom,
                "facturid" => $facturid,
                "tracking" => $tracking
            );

            $template = new template($facture->utilisateur->langue,$DB_site2);

            $template->changesite($facture->site_code,$facture->utilisateur->langue);
            $template->pro = ($facture->utilisateur->raisonsociale != "" && $template->site_code() == "base");

            $ec = new email_client($DB_site2,$template,null,"expedition",$vars);

            $mail = new email();
            $mail->destinataire = $facture->utilisateur->mail;
            $mail->destname     = $facture->utilisateur->prenom." ".$facture->utilisateur->nom;
            $mail->to           = ["pr@mon-espace-cuisson.com", 'webmaster@ma-cave-a-vin.fr'];
            $mail->expediteur   = $template->translate("/site/[site_code]/mail_expediteur");
            $mail->expname      = $template->translate("/site/nom");
            $mail->copymode     = "bcc";
            $mail->subject      = $ec->subject;
            $mail->body         = $ec->body;
            $mail->attachfile("https://".$_SERVER['HTTP_HOST']."/facture2.php?facturid=".$facture->facturid."&passe=".$facture->passefacture, $template->translate("/client/facture","F")." ".$facture->facturid.".pdf","application/pdf");

            if($facture->utilisateur->mail != ''){
                $mail->sendmail();

            } else {
                $sql = "INSERT INTO suivi_commande SET";
                $sql .= " facturid    = ".$facturid."";
                $sql .= ", date       = ".time()."";
                $sql .= ", commercial = '".$user_info['username']."'";
                $sql .= ", suivi      = 'Pas d\'email d\'expédition envoyé car aucune adresse email'";
                $DB_site2->query($sql);
            }

            $sql = "INSERT INTO mail_outbox SET";
            $sql .= " date         = ".time()."";
            $sql .= ", utilisateur = '".$user_info['username']."'";
            $sql .= ", `to`        = '".$mail->destinataire."'";
            $sql .= ", `from`        = '".$mail->expediteur."'";
            $sql .= ", subject     = '".addslashes($mail->subject)."'";
            $sql .= ", body        = '".addslashes($mail->body)."'";
            $sql .= ", pj          = 'Facture ".$facture->facturid.".pdf'";
            $sql .= ", facturid    = '".$facture->facturid."'";
            $sql .= ", userid      = '".$facture->utilisateur->userid."'";
            $sql .= ", uniqueid    = '".$mail->uniqueid."'";
            $sql .= ", compta      = 'non'";
            $sql .= ", auto        = 0";
            $DB_site2->query($sql);

            //Redirection vers la page de traitement de commande initiale
            header('Location: facture.php?action=editer&facturid='.$_POST['facturid'].'&lst_trietat=');
            exit;

            break;
        //Modification de l'attribution de commercial
        case 'attribution':

            // echo "ici on change l'attribution de la cde ".$_POST['facturid']." par le commercial ".$_POST['commercial_attribution']." <br>";

            $sql = "UPDATE facture SET ";
            $sql .= " commercial_attribution = '".$_POST['commercial_attribution']."' ";
            $sql .= " WHERE facturid = '".$_POST['facturid']."' ";
            $DB_site2->query($sql);

            //Redirection vers la page de traitement de commande initiale
            header('Location: facture.php?action=editer&facturid='.$_POST['facturid'].'&lst_trietat=');
            exit;

            break;

        case 'delier-projet':
            $id_projet = $_POST['projet-id'];
            $id_facture = $_POST['facturid'];
            $etat = new Projet($id_projet);
            $etat->etatid = 8;
            $etat->save();
            $DB_site2->query("UPDATE projet SET facturid = 0 WHERE projetid=".$id_projet);
            header('Location: /admin/facture.php?action=editer&facturid='.$id_facture.'&lst_trietat=');
            exit;
            break;


        case 'lier-projet':
            $id_facture = $_POST['facturid'];
            $id_projet = $_POST['liste_projets'];
            if($id_projet != '') {
                $etat = new Projet($id_projet);
                $etat->etatid = 5;
                $etat->save();
                $DB_site2->query("UPDATE projet SET facturid = ".$id_facture." WHERE projetid=".$id_projet);
                header('Location: /admin/facture.php?action=editer&facturid='.$id_facture.'&lst_trietat=');
                exit;
            }
            else {
                header('Location: /admin/facture.php?action=editer&facturid='.$id_facture.'&lst_trietat=');
                exit;
            }
            break;


        case 'liersav':
            $factureAlier = $DB_site2->query("SELECT * FROM facture WHERE facturid='".$_POST['commandeAlier']."' ");
            $laFacture = $DB_site2->query_first("SELECT provenance, provenanceid FROM facture WHERE facturid='".$_POST['facturid']."'");
            $msg = 0;
            if($DB_site2->num_rows($factureAlier) > 0){
                switch ($laFacture['provenance']) {
                    case '':
                        $DB_site2->query("UPDATE facture SET provenance = 'sav', provenanceid='".trim($_POST['commandeAlier'])."' WHERE facturid ='".$_POST['facturid']."' ");
                        break;
                    case 'sav':
                        $msg = 1;
                        break;
                    case 'avoir':
                        $msg = 2;
                        break;
                    case 'mp':
                        $msg = 3;
                        break;
                }
            }else{
                $msg = 5;
            }

            header('Location: facture.php?action=editer&facturid='.$_POST['facturid'].'&lst_trietat=&liersav='.$msg);
            exit;

            break;


        //AJOUT D'UNE ETAPE DANS LE RESUME DE SAV
        case 'sav_resume_add':
            $explode=explode("-",$_POST['dateline']);
            $dateline=mktime(0,0,0,$explode[1],$explode[2],$explode[0]);
            $sql =  "INSERT INTO sav_resume (savid,  etapeid, dateline, datecreation) VALUES ('".$_POST['savid']."' , '".$_POST['etapeid']."' , '".$dateline."', '".time()."') ";
            $DB_site2->query($sql);
            //Redirection vers la page de traitement de commande initiale
            header('Location: facture.php?action=editer&facturid='.$_POST['facturid'].'&lst_trietat=&sav_open='.$_POST['savid']);
            exit;
            break;

        //SUPPRESSION D'UNE ETAPE DANS LE RESUME DE SAV
        case 'sav_resume_del':
            $sql =  "DELETE FROM sav_resume WHERE resumeid='".$_POST['resumeid']."' ";
            $DB_site2->query($sql);
            //Redirection vers la page de traitement de commande initiale
            header('Location: facture.php?action=editer&facturid='.$_POST['facturid'].'&lst_trietat=');
            exit;
            break;

        case "mail_trspt":
            mail_livraison($_POST['mailname'],$_POST['efid']);
            break;

        case "avis":
            if($_POST['avis']==1){
                $facture = new facture($DB_site2,$facturid);
                $vars = array(
                    "nom"      => $facture->utilisateur->nom,
                    "prenom"   => $facture->utilisateur->prenom,
                    "tracking" => $tracking
                );
                $template = new template($facture->utilisateur->langue,$DB_site2);

                $template->changesite($facture->site_code,$facture->utilisateur->langue);
                $template->pro = ($facture->utilisateur->raisonsociale != "" && $template->site_code() == "base");

                $ec = new email_client($DB_site2, $template, null, "avisclient", $vars);
                $mail = new email();
                $mail->destinataire = $facture->utilisateur->mail;
                // $mail->destinataire = "benoit@mon-espace-cuisson.com";
                $mail->destname     = $facture->utilisateur->prenom." ".$facture->utilisateur->nom;
                $mail->bcc          = array("benoit@mon-espace-cuisson.com","stephane@mon-espace-cuisson.com"); // array("benoit@mon-espace-cuisson.com","marketing1@mon-espace-cuisson.com");
                $mail->expediteur   = $template->translate("/site/[site_code]/mail_expediteur");
                $mail->expname      = $template->translate("/site/nom");
                $mail->copymode     = "bcc";
                $mail->subject      = $ec->subject;
                $mail->body         = $ec->body;
                $mail->sendmail();

                $sql = "INSERT INTO mail_outbox SET";
                $sql .= " date         = ".time()."";
                $sql .= ", utilisateur = '".$user_info['username']."'";
                $sql .= ", `to`        = '".$mail->destinataire."'";
                $sql .= ", `from`        = '".$mail->expediteur."'";
                $sql .= ", subject     = '".addslashes($mail->subject)."'";
                $sql .= ", body        = '".addslashes($mail->body)."'";
                $sql .= ", facturid    = '".$facture->facturid."'";
                $sql .= ", userid      = '".$facture->utilisateur->userid."'";
                $sql .= ", uniqueid    = '".$mail->uniqueid."'";
                $sql .= ", compta      = 'non'";
                $sql .= ", auto        = 0";
                $DB_site2->query($sql);
            }
            $DB_site2->query("UPDATE facture SET avis=".intval($_POST['avis'])." WHERE facturid=".intval($_GET['facturid']));
            header('Location: facture.php?action=editer&facturid='.$_GET['facturid'].'&lst_trietat=');
            exit;
            break;
    }

    // on a fini les parametres POST ... on redirige en GET vers la page de visualisation de la commande
    // header('Location: facture.php?action=editer&facturid='.$_GET['facturid']);
    // exit;
}

if(isset($_GET['action']) && !empty($_GET['action'])  ){

    switch($_GET['action']){

        case 'cacher':

            $sql = "UPDATE facture SET";
            $sql .= " supprimee = '1'";
            $sql .= " WHERE facturid = '".$facturid."'";
            $DB_site2->query($sql);

            //Redirection vers la page de traitement de commande initiale
            header('Location: facture.php');
            exit;

            break;

        case 'del_sav':

            if(isset($_GET['sav_id']) && is_numeric($_GET['sav_id'])){

                $sav_id = $_GET['sav_id'];

                $oSAV = new sav($DB_site2, $sav_id);

                $sql = "DELETE FROM sav_fields";
                $sql .= " WHERE savid = ".$oSAV->id;
                $DB_site2->query($sql);

                $sql = "DELETE FROM sav_files";
                $sql .= " WHERE savid = ".$oSAV->id;
                $DB_site2->query($sql);

                $sql = "DELETE FROM sav_steps";
                $sql .= " WHERE savid = ".$oSAV->id;
                $DB_site2->query($sql);

                $sql = "DELETE FROM sav";
                $sql .= " WHERE id = ".$oSAV->id;
                $DB_site2->query($sql);

                $sql = "DELETE FROM sav_resume";
                $sql .= " WHERE savid = ".$oSAV->id;
                $DB_site2->query($sql);

                //Mise en forme du texte du suivi
                $suivi_commande = "Dossier SAV ".$oSAV->types[$oSAV->typelitige]." du ".date("d/m/Y",$oSAV->date_creation)." supprimé";

                //Mise à jour dans le suivi
                $sql = "INSERT INTO suivi_commande SET";
                $sql .= " facturid    = ".$oSAV->facturid;
                $sql .= ", date       = ".time()."";
                $sql .= ", commercial = '".$_COOKIE['admin_username']."'";
                $sql .= ", suivi      = '".mysqli_real_escape_string($DB_site2->link_id,$suivi_commande)."'";
                $DB_site2->query($sql);

                header('Location: facture.php?action=editer&facturid='.$facturid.'&lst_trietat=');
                exit;
            }

            break;

    }

}
//***********************************\\
// FIN TRAITEMENTS STATUS COMMANDE \\
//***********************************\\

$fournlink = array();
$jscontact = "var advfourn=new Array();\n";

$sql = "SELECT *";
$sql .= " FROM fournisseur";
$sql .= " WHERE type = 'fournadv'";
$req = $DB_site2->query($sql);
while($advf = $DB_site2->fetch_array($req)){
    $mails = array();
    if($advf['groupes'] != ""){
        $groups = explode(",",$advf['groupes']);

        foreach($groups as $group){
            $fournlink[$group][] = $advf['mail'];
        }
    } else {
        $fournlink[$advf['societe']][] = $advf['mail'];
    }
}

foreach($fournlink as $societe=>$mails) $jscontact .= "advfourn['".addslashes($societe)."']=\"".implode(";",$mails)."\";\n";

include_once(BACK_PATH."/header.php");
include_once(BACK_PATH."/menu.php");
?>
    <LINK href="cal.css" type="text/css" rel="stylesheet">
    <LINK href="filebox.css" type="text/css" rel="stylesheet">
    <script language="javascript" src="calendrier.js"></script>
    <script language="javascript" src="../javascripts/prototype.js"></script>
    <script language="javascript" src="../javascripts/prototype.simulate.js"></script>
    <script language="javascript" type="text/javascript" src="../javascripts/host.js"></script>
    <script language="javascript" type="text/javascript" src="facture.js"></script>
    <script language="javascript" src="filebox.js"></script>
    <script language="javascript">
        initcalendrier();

        //fonction pour envoyer un email avec le devis (devis en lien)
        function popdevis(id){
            if(!confirm('Confirmez-vous l\'envoi de l\'email au client ?')){
                return;
            }
            var page='../maildevis.php?id='+id;
            var width=900;
            var height=500;

            var top=(screen.height-height)/2;
            var left=(screen.width-width)/2;

            window.open(page,'','toolbar=no, location=no, directories=no, status=no, scrollbars=yes, resizable=no, copyhistory=yes, width='+width+', height='+height+', left='+left+', top='+top);
        }

        function contacter(factureid){
            window.open ('contact_facture.php?factureid='+factureid+'', 'Contact', 'Width=500, Height=280, toolbar=no, directories=no, location=no, menubar=no, resizable=no, scrollbars=no, status=no') ;
        }

        function check_facture(obj){
            /*if(!obj.checked){
               document.getElementById('input_invoice').checked    = '';
               document.getElementById('input_invoice').disabled   = true;
               document.getElementById('input_consignes').checked  = '';
               document.getElementById('input_consignes').disabled = true;
          } else {
               document.getElementById('input_invoice').checked    = true;
               document.getElementById('input_invoice').disabled   = '';
               document.getElementById('input_consignes').checked  = true;
               document.getElementById('input_consignes').disabled = '';
           }*/
            var nextelem=obj.nextElementSibling;
            while(nextelem!=null){
                if(nextelem.type=="checkbox"){
                    nextelem.checked=obj.checked;
                    nextelem.disabled=!obj.checked;
                }
                nextelem=nextelem.nextElementSibling;
            }
        }

        function check_select_value(obj){
            if(obj.value !== ''){
                if(confirm('Créer un SAV pour \"'+obj.options[obj.selectedIndex].text+'\" ?')){
                    document.forms.newsav.submit();
                }
            }
        }

        function show_element(id){
            if(document.getElementById(id).style.display !== 'block'){
                document.getElementById(id).style.display = 'block';
            } else {
                document.getElementById(id).style.display = 'none';
            }
        }


        // efface les articles de la liste
        function cleanSuiviMail(userid, mailid, sens){
            res = confirm("Effacer ce suivi ? ");
            if(res == true){
                ajaxrequest('ajax/delSuivi.php?userid='+userid+'&mailid='+mailid+'&sens='+sens,enditCallback);
            }
        }

        <?php echo $jscontact; ?>
    </script>

    <h1>Factures de mes clients</h1>
<?php

$today=mktime(0,0,0,date("m"),date("d"),date("Y"));
$tomorrow=$today+86400;

$marketplaces=array("01"=>"Amazon",
    "04"=>"Price Minister",
    "05"=>"Pixmania",
    "06"=>"Windill",
    "07"=>"Rue du Commerce",
    "08"=>"Edengo",
    "10"=>"Ebay",
    "11"=>"2x moins cher",
    "12"=>"Fnac",
    "13"=>"CDiscount",
    "14"=>"LaRedoute",
    "15"=>"Galeries Lafayette",
    "16"=>"Mister Good Deal",
    "18"=>"Delamaison",
    "19"=>"Conforama",
    "21"=>"Showroomprive",
    "22"=>"Retif");

/*$script = explode("/", $PHP_SELF);
if ($script[2] == "V2")
    $script[2] = $script[3] ;

$liens="<div style=\"float:right;\"><a href=\"$script[2]\">Accueil liste commandes</a>";
if (isset($oldaction) && $oldaction != "" )
       $liens.="<br><a href=\"$script[2]?action=rechercher&ref=$ref&nom=$nom&date=$date&mail=$mail&lst_trietat=$lst_trietat\">Page précédente</a>" ;
elseif (isset($lst_trietat) && $lst_trietat != "-1")
       $liens.="<br><a href=\"$script[2]?lst_trietat=$lst_trietat\">Page précédente</a>";
       $liens.="</div>";*/

/********************************** TSI ***************************/
// Modification du formaulaire de recherche pour ajout recherche client

// Formulaire de recherche commande
$srcform="<div id=\"formurecherche\" class=\"table_factures\">
       <form method=\"post\" action=\"facture.php\" style=\"display:inline;\">
       <input type=\"hidden\" name=\"action\" value=\"rechercher\">
       <b><span size=\"500px\">&nbsp;Rechercher une Commande :</span></b> 
       <input type=\"text\" placeholder=\"n° cde / n° fact / société / nom  / email /n° tel.\" name=\"ref\" size=\"60\" value=\""
    .($action=="rechercher"?$ref:"")."\"> <input type=\"checkbox\" name=\"suppr\" "
    .(isset($suppr)?"checked":"")
    .">Supprimées <input type=\"submit\" value=\"Rechercher\"></form>";

// Formulaire recherche article
$srcform.="<form method=\"post\" style=\"display:inline;\" >
       <input type=\"hidden\" name=\"action\" value=\"article\">
       <br><b>&nbsp;Rechercher un article  :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
       </b> <input type=\"text\" placeholder=\"code article\" name=\"ref\" value=\""
    .($action=="article"?$ref:"")."\"> <input type=\"checkbox\" name=\"valid\""
    .(isset($valid)?" checked":"")."> Commandes validées <input type=\"submit\" value=\"OK\">$liens</form>";

// Formulaire recherche client
$srcform.="<form method=\"get\" style=\"display:inline;\" target=\"_blank\" action=\"utilisateur.php\" ><br>
       <b>&nbsp;Rechercher un client :</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
       <input type=\"text\" placeholder=\"société / nom  / email / n° tél. / n° client \" name=\"src\" size=\"60\" value=\"\">&nbsp;
       <input type=\"submit\" value=\"OK\">$liens</form>";


// Formulaire recherche SAV
$srcform.="<form method=\"get\" style=\"display:inline;\" action=\"facture.php\" >
       <input type=\"hidden\" name=\"action\" value=\"sav\"><br>
       <b>&nbsp;Rechercher un SAV :</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
       <input type=\"text\" placeholder=\"N° de série / N° de dossier fabriquant\" name=\"ref\" size=\"60\" value=\""
    .($action=="sav"?$ref:"")."\">&nbsp;
       <input type=\"submit\" value=\"OK\">$liens</form>";

//rechercher par reference TRSPT
$srcform.="<form method=\"get\" style=\"display:inline;\" action=\"facture.php\" >
       <input type=\"hidden\" name=\"action\" value=\"trspt\"><br>
       <b>&nbsp;N° transporteur :</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
       <input type=\"text\" placeholder=\"Réf transporteur / installateur\" name=\"ref\" size=\"60\" value=\""
    .($action=="trspt"?$ref:"")."\">&nbsp;
       <input type=\"submit\" value=\"OK\">$liens</form>";

//rechercher par montant payé
$srcform.="<form method=\"get\" style=\"display:inline;\" action=\"facture.php\" >
       <input type=\"hidden\" name=\"action\" value=\"smontant\"><br>
       <b>&nbsp;Montant payé :</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
       <input type=\"text\" placeholder=\"Montant\" name=\"montant\" size=\"10\" value=\""
    .($action=="montant"?$ref:"")."\">&nbsp;
       <label for=\"start\">Date début:</label>
       <input type=\"date\" id=\"start\" name=\"montant_start\" required>
       <label for=\"end\">Date fin:</label>
       <input type=\"date\" id=\"end\" name=\"montant_end\" required>
       <input type=\"submit\" value=\"OK\">$liens</form>";



// Fin formulaires
$srcform.="<div style=\"clear:both\"></div></div>";

//https://localhost.ma-cave-a-vin.fr/admin/utilisateur.php?orderby=&ordersens=&src=mazet&dpt=&commercial=0&encours=&typeclient=&vip=&code401=&prov=&suivi=

// Requete de recherche générique
if($ref=="" && ($action == "rechercher" || $action=="article" || $action=="sav")) unset($action);
if (isset($action) and ($action == "rechercher" || $action=="article" || $action=="sav" || $action=="trspt" || $action=="smontant"))
{
    $where="";

    $ref=trim($ref);
    if($action=="rechercher"){
        $srcphone=array();
        $phonenum=trim(preg_replace("/[^\d\+]/","",$ref));
        $phonenum=preg_replace("/^\+33|0033/","0",$phonenum);
        if(strlen($phonenum)>8){
            $srcphone[]=$phonenum;
            if(preg_match("/^0\d{9}$/",$phonenum)){
                $srcphone[]="0033".substr($phonenum,1);
                $srcphone[]="+33".substr($phonenum,1);
            }
            $sqlphone=implode("','",$srcphone);
        }
        if(!isset($suppr)) $where.="supprimee = '0' and ";
        if (isset($ref) and $ref != ""){
            if(preg_match("/(CDE|cde)([0-9]{5,6})/", $ref,$m) == 1){
                $ref = $m[2];
            }
            $where .= " f.facturid = '".mysqli_real_escape_string($DB_site2->link_id,$ref)."' or f.numerofacture like '".mysqli_real_escape_string($DB_site2->link_id,$ref)."' or f.clientcde like '".mysqli_real_escape_string($DB_site2->link_id,$ref)."' or f.nom like '%".mysqli_real_escape_string($DB_site2->link_id,$ref)."%' or u.nom like '%".mysqli_real_escape_string($DB_site2->link_id,$ref)."%' or f.mp_facturid like '".mysqli_real_escape_string($DB_site2->link_id,$ref)."' or f.raisonsociale LIKE '%".mysqli_real_escape_string($DB_site2->link_id,$ref)."%' or u.raisonsociale LIKE '%".mysqli_real_escape_string($DB_site2->link_id,$ref)."%' or f.mail LIKE '".mysqli_real_escape_string($DB_site2->link_id,$ref)."'";
        }
        if(isset($sqlphone))
            $where.=" or REPLACE(f.telephone,'.','') IN ('".$sqlphone."') or REPLACE(u.telephone,'.','') IN ('".$sqlphone."') or REPLACE(u.portable,'.','') IN ('".$sqlphone."')" ;
    }

    if($action=="article"){
        if(isset($valid)) $where.="valide<>0 AND ";
        $where.="f.facturid IN (SELECT factureid FROM lignefacture lf INNER JOIN article a USING (artid) WHERE lf.artcode LIKE '%$ref' OR a.artcode_eurocave LIKE '%$ref%')";
    }


    if($action=="sav"){ // à ecrire ici !!!
        $where.="f.facturid IN (SELECT DISTINCT(facturid) FROM sav AS s INNER JOIN sav_fields AS sf ON(s.id=sf.savid) WHERE sf.name IN('sav_nbserie_verif','sav_ndossier') AND sf.value='$ref' )";
    }

    if($action=="trspt"){ // recherche commande par reference trspt
        $where.="f.facturid = (SELECT facturid FROM adv WHERE id='$ref' )";
    }
    if($action=="smontant"){
        $tabFactures = array();
        $montant = str_replace(",", ".", mysqli_real_escape_string($DB_site2->link_id,$montant));
        $montant = floatval($montant);
        if($montant != 0){
            $where="f.facturid IN(SELECT facturid FROM facture WHERE datefacture BETWEEN '$montant_start' AND '$montant_end' AND totalttc LIKE '$montant%' )";
            $where.=" OR f.facturid IN(SELECT facturid FROM facture JOIN reglement USING(facturid) WHERE datefacture BETWEEN '$montant_start' AND '$montant_end' AND reglement_montant ='$montant%')"; // totalttc a 0 si facture pas validé
        }else{
            $where="f.facturid IN(SELECT facturid FROM facture WHERE datefacture BETWEEN '$montant_start' AND '$montant_end')";
            $where.=" OR f.facturid IN(SELECT facturid FROM facture JOIN reglement USING(facturid) WHERE datefacture BETWEEN '$montant_start' AND '$montant_end')"; // totalttc a 0 si facture pas validé
        }
    }

    $factures=$DB_site2->query("SELECT f.facturid, passefacture, datefacture, heurefacture, datelivraison,
        u.mail, f.prixgestion, f.prixport, ef.$libelle, f.valide, mp.$libelle as moyenpaiement,
        f.nom, u.nom AS unom,f.commercial, f.devise,
        cadeauid, f.moyenid, f.prix_contre_remboursement, f.numerofacture, f.codenumerofacture,
        f.advdate1, f.advdate2, f.advdate3, f.provenance, f.provenanceid, f.mp_facturid,
        f.userid, u.vip, u.codepostal, u.ville, u.paysid FROM facture f
        INNER JOIN utilisateur u ON (f.userid = u.userid)
        INNER JOIN etatfacture ef ON (f.valide = ef.valudeid)
        INNER JOIN moyenpaiement mp ON (f.moyenid = mp.moyenid)
        WHERE ".$where."
        ORDER BY datefacture DESC, f.facturid DESC ");

    $nb = $DB_site2->num_rows($factures) ;

    if ($nb > 0)  // factures trouvées
    {
        print $srcform;

        if (!isset($nb) || ($nb<=0))
            echo "<font class=\"cost\">Aucune facture ne correspond à votre recherche</font><br><br>" ;
        else
            echo "$nb facture(s) correspond(ent) à votre recherche : <br><br>";

        echo "<a href=\"facture.php\">Retour à la liste des factures</a>
      <br><br>
      <table class=\"table_factures\">
      <tr>
      <td class=\"td_head_factures\"><b>N° Fac</b></td>
      <td class=\"td_head_factures\"><b>N° Cde</b></td>
      <td class=\"td_head_factures\"><b>Date</b></td>
      <td class=\"td_head_factures\"><b>Montant TTC</b></td>
      <td class=\"td_head_factures\" width=\"30\"><b>Moyen paiement</b></td>
      <td class=\"td_head_factures\"><b>ACTIONS</b></td>
      <td class=\"td_head_factures\"><b>CDE fournisseur</b></td>
      <td class=\"td_head_factures\"><b>Suivi</b></td>
      <td class=\"td_head_factures\"><b>Date livraison</b></td>
      <td class=\"td_head_factures\"><b>1. Départ fournisseur</b></td>
      <td class=\"td_head_factures\"><b>2. Livraison intermédiaire</b></td>
      <td class=\"td_head_factures\"><b>3. Livraison client</b></td>           
      </tr>";

        while ($facture=$DB_site2->fetch_array($factures))
        {
            $remboursement = 0 ;
            $rowalt = "td_factures_".getrowbg();
            list ($annee, $mois, $jour) = explode("-", $rs_facture[datefacture]);
            $lignefactures=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid = '$facture[facturid]'");
            $total_facture = 0 ;


            while ($lignefacture=$DB_site2->fetch_array($lignefactures))
            {
                $total_facture += ($lignefacture[prix] * $lignefacture[qte]) ;
                if ($lignefacture[remboursement])
                {
                    $remboursement += $lignefacture[prix] * $lignefacture[qte] ;
                }
            }

            //Montant total de l'installation
            //Liste des installations commandées
            $prix_installation_ttc = 0;
            $prix_installation_ht = 0;
            $sql = "SELECT *";
            $sql .= " FROM lignefactureinstallation";
            $sql .= " WHERE factureid = ".$facture['facturid'];
            $req = $DB_site2->query($sql);
            if($DB_site2->num_rows($req) > 0){
                while($row = $DB_site2->fetch_array($req)){
                    $prix_installation_ttc += $row['lignefactureinstallation_differenceprix'];
                    $prix_installation_ht += $row['lignefactureinstallation_differenceprix'] / (1 + $row['lignefactureinstallation_tauxtva'] / 100);
                }
            }

            $total_facture=$DB_site2->query_first("SELECT SUM(prix*qte) FROM lignefacture WHERE factureid='$facture[facturid]'");
            // remise sur tout le catalogue
            $fact=$DB_site2->query_first("SELECT pctremise FROM facture WHERE facturid = '$facture[facturid]'");
            if ($fact[0] != 0)
            {
                $total_facture[0] = $total_facture[0] * (1 - $fact[0] / 100) ;
            }

            // Bon de réduction
            $prixcadeau = 0;
            if (in_array( "13", $modules ) && $facture[cadeauid] > 0 )
            {
                $cadeau=$DB_site2->query_first("SELECT cadeauid, montantcadeau FROM facture WHERE facturid = '$facture[facturid]'") ;
                if ($cadeau[cadeauid] != "")
                {
                    $prixcadeau = $cadeau[montantcadeau] ;
                }
            }
            if(is_string($rs_facture[cadeauid]) && $rs_facture[cadeauid]!="")
            {}

            // remise
            if (in_array ("3", $modules))
            {
                $prixremise = 0 ;
                $remise_facture = $DB_site2 -> query_first ("SELECT r.remiseid, r.typeremise, r.valeur, r.pctremise, f.montantremise 
       FROM facture f inner join remise r on (f.remiseid = r.remiseid)
       WHERE f.facturid = '$facture[facturid]'" );
                if ($remise_facture[remiseid] != "")
                {
                    $prixremise = $remise_facture[montantremise] ;
                } // fin du if remise
            } // fin des remises

            // contre remboursement
            if ($rs_facture[moyenid] == '3')
            {
                $prix_contre_remb = $facture[prix_contre_remboursement] ;
            }
            else
            {
                $prix_contre_remb = 0 ;
            }
            //Montant total de l'installation
            //Liste des installations commandées
            $prix_installation_ttc = 0;
            $sql = "SELECT *";
            $sql .= " FROM lignefactureinstallation";
            $sql .= " WHERE factureid = ".$facture['facturid'];
            $req = $DB_site2->query($sql);
            if($DB_site2->num_rows($req) > 0){
                while($row = $DB_site2->fetch_array($req)){
                    $prix_installation_ttc += $row['lignefactureinstallation_differenceprix'];
                }
            }
            $total_facture = number_format($total_facture[0] + $facture[prixgestion] + $prix_contre_remb + $facture[prixport] - $prixcadeau -$prixremise + $prix_installation_ttc, 2, ',', ' ')." ".$devises->symbole($facture['devise']);
            list($annee, $mois, $jour) = explode( "-", $facture[datefacture] );

            if ($remboursement > 0)
            {
                $remboursement = "<br><font color=\"#E5840F\">-".number_format($remboursement, 2, ',', ' ')." €</font>" ;
            }
            else
            {
                $remboursement = "" ;
            }

            echo "<tr Height=\"25\">";
            if ($facture[numerofacture] > 556500)
            {
                echo "<td class=\"$rowalt\" align=\"center\">$facture[codenumerofacture]$facture[numerofacture]</td>";
                $lientelecharger = "<br><br><a href=\"https://$host/facture2.php?passession=oui&facturid=$facture[facturid]&passe=$facture[passefacture]&langue=FR\" target=\"_blank\">Facture</a>" ;
            }
            else
            {
                if ($facture[valide] == 1)
                {
                    echo "<td class=\"$rowalt\" align=\"center\"> -- </td>" ;
                    $lientelecharger = "<br><br><a href=\"https://$host/facture2.php?passession=oui&facturid=$facture[facturid]&passe=$facture[passefacture]&cde=1&langue=FR\" target=\"_blank\">Commande</a>" ;
                    /*
                      if (in_array("124", $modules))
                        {
                          $lientelecharger .= " <input type=\"checkbox\" name=\"chk_etiquette[$rs_facture[facturid]]\" />" ;
                        }
                      */
                }
                else
                {
                    echo "<td class=\"$rowalt\" align=\"center\"> -- </td>" ;
                    $lientelecharger = "<br><br>" ;
                }
            }

            $vip=usericon($facture['vip'],20);

            $sav=$DB_site2->query_first("SELECT sav.id,value AS typelitige FROM sav INNER JOIN sav_fields ON ( sav.id = sav_fields.savid ) WHERE name = 'sav_typelitige' AND facturid=".$facture['facturid']." AND fin=0 ORDER BY sav.date DESC");

            if($sav['id']!="") $vip.="<a href=\"facture.php?action=editer&facturid=".$facture['facturid']."&savid=".$sav['id']."#sav\"><img src=\"images/".($sav['typelitige']!=5?"sav":"fraude").".png\" height=\"20\"></a>";

            if($facture['paysid']==57)
                $ville=substr($facture['codepostal'],0,2)." ".$facture['ville'];
            else
                $ville=$facture['ville'];

            echo "<td class=\"$rowalt\">$facture[facturid]<br>$facture[nom]<br><a href=\"utilisateur.php?action=editer&userid=$facture[userid]\" style=\"color:#404040;\">$facture[unom]</a>$vip $ville</td>";
            echo "<td class=\"$rowalt\" align=\"center\">".date("d/m/Y H:i",$facture['heurefacture'])."</td>";
            echo "<td class=\"$rowalt\" align=\"right\" nowrap>$total_facture $remboursement</td>";

            if ($facture[valide] == 0)
            { // non traité
                $couleurlien="#000000";
            }
            if ($facture[valide] == 1 || $facture[valide]==7)
            { // Validée
                $couleurlien="#0000FF";
            }
            if ($facture[valide] == 2)
            { // refus commercant
                $couleurlien="#FF0000";
            }
            if ($facture[valide] == 3)
            { // refus carte bleue
                $couleurlien="#FF0000";
            }
            if ($facture[valide] == 4)
            { // abandon internaute
                $couleurlien="#FF0000";
            }
            if ($facture[valide] == 5 || $facture[valide]==8)
            { // expédié
                $couleurlien="#00FF00";
            }

            $suivienvoi="";
            $advdate1=$advdate2=$advdate3="";
            $efl=array();
            $rappeltxt="";

            if(in_array($facture['valide'],array(1,5,7,8)))
            {
                $r=$DB_site2->query("SELECT * FROM envoi_fournisseur_ligne ef, lignefacture l WHERE ef.type='fourn' AND l.factureid=".$facture['facturid']." AND ef.ligne_facture_id=l.lignefactureid");
                $fourncount=$DB_site2->num_rows($r);
                while($envoifourn=$DB_site2->fetch_array($r)){ $efl[$envoifourn['lignefactureid']]['in']['fourn']=$envoifourn['indate']; $efl[$envoifourn['lignefactureid']]['out']['fourn']=$envoifourn['outdate'];
                }

                $r=$DB_site2->query("SELECT * FROM envoi_fournisseur_ligne ef, lignefacture l WHERE ef.type='trspt' AND l.factureid=".$facture['facturid']." AND ef.ligne_facture_id=l.lignefactureid");
                $trsptcount=$DB_site2->num_rows($r);

                while($envoitrspt=$DB_site2->fetch_array($r)){
                    $efl[$envoitrspt['lignefactureid']]['in']['trspt']=$envoitrspt['indate']; $efl[$envoitrspt['lignefactureid']]['out']['trspt']=$envoitrspt['outdate'];
                }

                $r=$DB_site2->query("SELECT * FROM envoi_fournisseur_ligne ef, lignefacture l WHERE ef.type='sls' AND l.factureid=".$facture['facturid']." AND ef.ligne_facture_id=l.lignefactureid");
                $slscount=$DB_site2->num_rows($r);

                while($envoisls=$DB_site2->fetch_array($r)){
                    $efl[$envoisls['lignefactureid']]['in']['sls']=$envoisls['indate']; $efl[$envoisls['lignefactureid']]['out']['sls']=$envoisls['outdate'];
                }

                $lignenb=$DB_site2->query_first("SELECT count(*) FROM lignefacture WHERE factureid=".$facture['facturid']);
                $suivienvoi="<div style=\"color:".envoicolor($fourncount,$lignenb[0]).";\">Fournisseur</div><div style=\"color:".envoicolor($trsptcount,$lignenb[0]).";\">Transport</div><div style=\"color:".envoicolor($slscount,$lignenb[0]).";\">Installation</div>";

                calcdates($efl);

                /*if($facture['advdate1']!=0) $advdate1="Enlevement<br>".date("d/m/Y",$facture['advdate1']);
                   if($facture['advdate2']!=0) $advdate2="Chez livreur<br>".date("d/m/Y",$facture['advdate2']);
                   if($facture['advdate3']!=0) $advdate3="Chez client<br>".date("d/m/Y",$facture['advdate3']);
                   if($facture['advdate1']==$today) $advdate1="<font color=\"red\">".$advdate1."</font>";
                   if($facture['advdate2']==$today) $advdate2="<font color=\"red\">".$advdate2."</font>";
                   if($facture['advdate3']==$today) $advdate3="<font color=\"red\">".$advdate3."</font>";*/

                $rq_rappel=$DB_site2->query_first("SELECT * FROM suivi_commande FORCE INDEX (facturid) WHERE facturid=$facture[facturid] ORDER BY date DESC LIMIT 1");//recupération de la table suivi_commande ou lon va recupéré le commercial qui sera utilé pour les "passé par"

                if($rq_rappel['rappel']!=0) $rappeltxt="<br><br><font color=\"".($rq_rappel['rappel']>$today && $rq_rappel['rappel']<$tomorrow?"red":"")."\">$rq_rappel[pour] rappel le ".date("d/m/Y à H:i",$rq_rappel['rappel'])."</font>";

                if($rq_rappel['suivi']!="") $rappeltxt.="<img src=\"../configurations/".$host."/images/templates/icone_info.gif\" onmouseover=\"document.getElementById('div$facture[facturid]').style.visibility='visible';\" onmouseout=\"document.getElementById('div$facture[facturid]').style.visibility='hidden';\" width=\"20\" align=\"absmiddle\"><div id=\"div$facture[facturid]\" class=\"popup\">".stripslashes($rq_rappel['suivi'])."</div>";

            }

            $devis=$DB_site2->query_first("SELECT * FROM envoi_devis WHERE facturid=$facture[facturid] ORDER BY date DESC");

            if($devis['date']!="")
                $rappeltxt.="<img src=\"images/devis.png\" width=\"24\"><br>".date("d/m/Y à H:i",$devis['date'])." par ".($devis['utilisateur']!=""?$devis['utilisateur']:$rq_rappel['commercial']);

            $passepar="Passé par ".($facture['commercial']==""?$rq_rappel['commercial']:$facture['commercial']);

            if($facture['provenance']=="mp") $facture[moyenpaiement]=$marketplaces[$facture['provenanceid']];

            echo "<td class=\"$rowalt\" align=\"center\" nowrap width=\"10%\">$facture[moyenpaiement]<br><font color=\"$couleurlien\">$facture[$libelle]</font><br>$passepar</td>
            <td rowspan=\"2\" class=\"$rowalt\" align=\"center\" width=\"30%\"><div  align=\"center\">
            <a href=\"facture.php?action=editer&facturid=$facture[facturid]&oldaction=rechercher&ref=$ref&nom=$nom&mail=$mail&date=$date&lst_trietat=$lst_trietat\" target=\"_self\">Editer</a>";
            if ($facture[numerofacture] > 556500)
            {
                echo "<br><br><a href=\"https://$host/facture2.php?passession=oui&facturid=$facture[facturid]&passe=$facture[passefacture]&langue=FR\" target=\"_blank\">Facture</a>" ;
            }
            else
            {
                if ($facture[valide] == 1)
                    echo "<br><br><a href=\"https://$host/facture2.php?passession=oui&facturid=$facture[facturid]&passe=$facture[passefacture]&cde=1&langue=FR\" target=\"_blank\">Commande</a>" ;
                elseif ($facture[valide] ==0)
                    echo "<br><br><a href=\"facture.php?action=cacher&facturid=$facture[facturid]&oldaction=rechercher&ref=$ref&nom=$nom&mail=$mail&date=$date&lst_trietat=$lst_trietat\" target=\"_self\">Supprimer</a>" ;
                else
                    echo "<br><br>" ;
            }
            if($facture['valide']==0) echo "<br><a href=\"javascript:popdevis($facture[facturid])\">Envoyer le devis</a>";
            $datelivraison=($facture['datelivraison']>0?date("d/m/Y",$facture['datelivraison']):"?");
            echo "</div>
          </td><td rowspan=\"2\" class=\"$rowalt\" style=\"line-height:18px; text-align:center;\">$suivienvoi <img src=\"../configurations/".$host."/images/templates/icone_info.gif\" title=\"".($rs_facture['suivi2'])."\" width=\"32\"></td>
          <td rowspan=\"2\" class=\"$rowalt\">$rappeltxt</td>
          <td rowspan=\"2\" class=\"$rowalt\"".($rs_facture['datelivraison']<=time()?" style=\"color:red;\"":"").">".$datelivraison."</td>
          <td rowspan=\"2\" class=\"$rowalt\">$advdate1</td>
          <td rowspan=\"2\" class=\"$rowalt\">$advdate2</td>
          <td rowspan=\"2\" class=\"$rowalt\">$advdate3</td></tr>" ;

            $lignefactures=$DB_site2->query("SELECT lf.*,a.artcode_eurocave FROM lignefacture lf LEFT JOIN article a ON(a.artid=lf.artid) WHERE lf.factureid = '$facture[facturid]'");
            $articles="";
            while ($lignefacture=$DB_site2->fetch_array($lignefactures))
            {
                if($lignefacture['prix']>0) $articles.=$lignefacture['qte']." <a href=\"clientnavig.php?userid=$facture[userid]&goto=art,".$lignefacture['artid'].",-.html\" target=\"_blank\">".$lignefacture['artcode']."</a><a href=\"ajout_produit.php?artid=".$lignefacture['artid']."&prec=modif\" target=\"_blank\"> (".$lignefacture['artcode_eurocave'].")</a><br>";
            }
            echo "<tr><td colspan=\"5\" class=\"$rowalt\">$articles</td></tr>";
        }
        echo "</table>
      <br><br>
      <a href=\"facture.php\">Retour à la liste des factures</a>" ;
    }
    else // pas de factures trouvées
    {
        echo "<font class=\"cost\" style=\"font-size: 20px;\" > ! Aucune facture ne correspond à votre recherche !</font>" ;
        $action = "" ;
    }
}
if (!isset ($action) or $action == "")
{
    if (!isset ($pagenumber) or $pagenumber == "")
    {
        $pagenumber = "1";
    }
    $perpage = "30";
    $limitlower = ($pagenumber -1) * $perpage;
    $limitupper = ($pagenumber) * $perpage;
    $liste_etats_rq="";
    $lst_trietat=array();
    $etatcaption=array("Tous");
    if(isset($_GET['lst_trietat']))
        $_POST['lst_trietat']=$_GET['lst_trietat'];

    if(isset($_POST['lst_trietat'])){
        $etatcaption=array();
        $lst_trietat=is_array($_POST['lst_trietat']) ? $_POST['lst_trietat'] : array($_POST['lst_trietat']);
        $liste_etats_rq=" AND f.valide IN (".implode(",",$lst_trietat).")";
    }

    $divetats="";
    $rq_etatfacture = $DB_site2->query("SELECT valudeid, $libelle FROM etatfacture");
    while ($rs_etatfacture = $DB_site2->fetch_array($rq_etatfacture)){
        $divetats.="<label><input type=\"checkbox\" name=\"lst_trietat[]\" value=\"".$rs_etatfacture['valudeid']."\" onclick=\"document.formu.submit()\"".(in_array($rs_etatfacture['valudeid'],$lst_trietat)?" checked":"").">".$rs_etatfacture[$libelle]."</label>";
        if(in_array($rs_etatfacture['valudeid'],$lst_trietat))
            $etatcaption[]=$rs_etatfacture[$libelle];
    }

    $attributionsql="";
    $select_attribution="";
    $select_attribution.="<select name=\"commercial_attribution\" onChange=\"document.formu.submit()\">";
    $select_attribution.="<option value=\"\">Tous</option>";
    if($commercial_attribution=="direct"){
        $selected="selected";
        $attributionsql="AND f.commercial_attribution IN ('".$commercial_attribution."')";
    } else {
        $selected="";
    }
    $select_attribution.="<option value=\"direct\" ".$selected.">Direct</option>";
    $commercials=$DB_site2->query(" SELECT username FROM parametresgeneraux WHERE commissions ='1' ORDER BY username ASC ");
    while($commercial=$DB_site2->fetch_array($commercials)){
        if($commercial_attribution==$commercial['username']){
            $selected="selected";
            $attributionsql="AND f.commercial_attribution IN ('".$commercial_attribution."')";
        } else {
            $selected="";
        }
        $select_attribution.="<option value=\"".$commercial['username']."\" ".$selected.">".UCfirst($commercial['username'])."</option>";
    }
    $select_attribution.="</select>";

    $datesql="";
    if(isset($_GET['fromdate']) && isset($_GET['todate'])){
        $datesql=" AND heurefacture BETWEEN ".intval($_GET['fromdate'])." AND ".intval($_GET['todate']);
    }

    $sqlsav=$sqladv=$provsql="";
    if(isset($sav))
        $sqlsav=" AND (sav=1 OR (SELECT COUNT(*) FROM sav WHERE fin=0 AND facturid=f.facturid)>0)";

    if(isset($advdate))
        $sqladv=" AND (advdate1=$today OR advdate2=$today OR advdate3=$today)";
    if(isset($annule))
        $sqladv=" AND (SELECT COUNT(*) FROM lignefacture WHERE factureid=f.facturid AND remboursement='1')>0";
    $trisql="";
    if(isset($lst_trimoyen) && $lst_trimoyen!=0)
        $trisql="AND f.moyenid IN ($lst_trimoyen)";

    if(isset($prov))
        $provsql="AND f.provenance IN ('".implode("','",$prov)."')";

    $trimoyens=array("CB, American Express"=>"1,10","Paypal"=>"6","Chèque, virement, mandat"=>"2,4,5","Receive And Pay, Aurore"=>"8,9,12","Sofinco"=>"17,18");
    foreach($trimoyens as $name=>$value)
        $moyensoptions.="<option value=\"$value\"".($lst_trimoyen==$value?" selected":"").">$name</option>";

    $mp_sql="";
    $select_mp="";
    $select_mp.="<select name=\"lst_mp\" onChange=\"document.formu.submit()\">";
    $select_mp.="<option value=\"\">Toutes</option>";
    foreach ($marketplaces as $key => $value) {
        if($lst_mp==$key){
            $selected="selected";
            $mp_sql="AND f.provenanceid = '".$key."' ";
        } else {
            $selected="";
        }
        $select_mp.="<option value=\"".$key."\" ".$selected.">".$value."</option>";
    }
    $select_mp.="</select>";

    $facturecount = $DB_site2->query_first("SELECT count(*) FROM facture f INNER JOIN moyenpaiement mp ON (f.moyenid = mp.moyenid)
     WHERE f.supprimee = '0' $liste_etats_rq $sqlsav $sqladv $trisql $provsql $attributionsql $mp_sql $rq_factures_abandonnees $datesql");
    $parametats="";
    foreach($lst_trietat as $valideid)
        $parametats.="&lst_trietat[]=".$valideid;
    $paramdate="";
    if(isset($_GET['fromdate']) && isset($_GET['todate'])){
        $paramdate="&fromdate=".$_GET['fromdate']."&todate=".$_GET['todate'];
    }
    $pagenav = getpagenav2($facturecount[0], "facture.php?".$parametats.$paramdate."&lst_trimoyen=".$lst_trimoyen."&commercial_attribution=".$commercial_attribution."&lst_mp=".$lst_mp);
    // Liste des factures en fonction de la recherche
    echo "<h3>Factures existantes</h3>".$srcform;

    if(!isset($prov))
        $prov=array();

    echo "$pagenav<br><br>";
    echo "<table class=\"table_factures\">
<tr>
<td colspan=\"11\" align=\"center\">
<form action=\"facture.php\" method=\"post\" name=\"formu\">
<br> Trier par état :
<div id=\"divetats\">
<div class=\"caption\" onclick=\"document.getElementById('divetats').classList.toggle('open');\">
".implode(",",$etatcaption)."
<svg width=\"10\" height=\"6\">
<path style=\"fill:none;stroke:#555555;stroke-width:2;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1\" d=\"M 0,0 5,5 10,0\" />
</svg>
</div>
".$divetats."
</div>
<span class=\"btn\"><a href=\"facture.php?annule=1\">Annulées</a></span>
<br>Par moyen de paiement : 
<select name=\"lst_trimoyen\" onChange=\"document.formu.submit()\">
<option value=\"0\">Tout</option>
".$moyensoptions."
</select>
<br>Provenance : 
<input type=\"checkbox\" name=\"prov[]\" value=\"\" onclick=\"document.formu.submit()\"".(in_array("",$prov)?" checked":"").">Site 
&nbsp;<input type=\"checkbox\" name=\"prov[]\" value=\"sav\" onclick=\"document.formu.submit()\"".(in_array("sav",$prov)?" checked":"").">SAV 
&nbsp;<input type=\"checkbox\" name=\"prov[]\" value=\"mp\" onclick=\"document.formu.submit()\"".(in_array("mp",$prov)?" checked":"").">Market Place 
&nbsp;<input type=\"checkbox\" name=\"prov[]\" value=\"mobile\" onclick=\"document.formu.submit()\"".(in_array("mobile",$prov)?" checked":"").">Mobile
<br>
Par attribution : ".$select_attribution."<br>
Par marketplace : ".$select_mp."
</form>
</td>
</tr>";
    $rq_facture = $DB_site2->query("SELECT f.facturid, f.nom, passefacture,
  datefacture, heurefacture, datelivraison, exp_accord_client, date_exp_accord_client,
  u.mail, f.prixgestion, f.prixport, ef.$libelle,
  f.valide,  mp.$libelle as moyenpaiement,f.commercial,f.devise,
  cadeauid, f.moyenid, f.prix_contre_remboursement,
  f.codenumerofacture, f.numerofacture, f.suivi2,
  u.nom AS nom2, f.userid, f.advdate1, f.advdate2,
  f.advdate3, f.provenance, f.infotranspo, f.provenanceid,
  f.mp_facturid, f.commission, u.vip, u.codepostal,
  u.ville, u.paysid, commercial_attribution, f.avis
  FROM facture f
  INNER JOIN utilisateur u ON (f.userid = u.userid)
  INNER JOIN etatfacture ef ON (f.valide = ef.valudeid)
  INNER JOIN moyenpaiement mp ON (f.moyenid = mp.moyenid)
  WHERE f.supprimee = '0'
  $liste_etats_rq
  $sqlsav
  $sqladv
  $provsql
  $attributionsql
  $trisql
  $mp_sql
  $rq_factures_abandonnees
  $datesql
  ORDER BY datefacture DESC, f.facturid DESC
  LIMIT $limitlower, $perpage");
    if ($DB_site2->num_rows($rq_facture) > 0)
    {
        echo "<tr>
    <td class=\"td_head_factures\"><b>N° Fac</b></td>
    <td class=\"td_head_factures\"><b>N° Cde</b></td>
    <td class=\"td_head_factures\"><b>Date</b></td>
    <td class=\"td_head_factures\"><b>Montant TTC</b></td>
    <td class=\"td_head_factures\" width=\"30\"><b>Moyen paiement</b></td>
    <!--<td class=\"td_head_factures\"><b>Etat</b></td>-->             
    <td class=\"td_head_factures\"><b>ACTIONS</b></td>
    <td class=\"td_head_factures\"><b>CDE fournisseur</b></td>
    <td class=\"td_head_factures\"><b>Suivi</b></td>
    <td class=\"td_head_factures\"><b>Date livraison</b></td>
    <td class=\"td_head_factures\"><b>1. Départ fournisseur</b></td>
    <td class=\"td_head_factures\"><b>2. Livraison intermédiaire</b></td>
    <td class=\"td_head_factures\"><b>3. Livraison client</b></td>
    </tr>";

        /*
        if (in_array("124", $modules))
            {
            //début form etiquette
            echo "<form name=\"formetiquette\" action=\"impressionetiquette.php\" method=\"post\">";
            }
        */
        $total_col=0;
        while ($rs_facture=$DB_site2->fetch_array($rq_facture))
        {
            if(isset($_GET['sav']) && isset($_GET['savtype'])){
                $savinfo=$DB_site2->query_first("SELECT value FROM sav_fields WHERE name='sav_typelitige' AND savid IN (SELECT id FROM sav WHERE facturid=".$rs_facture['facturid']." AND fin=0)");
                if($savinfo['value']!=$_GET['savtype']) continue;
            }
            $remboursement = 0 ;
            $rowalt = "td_factures_" . getrowbg();
            list ($annee, $mois, $jour) = explode("-", $rs_facture['datefacture']);
            if($rs_facture['heurefacture']!=0){
                $d=getdate($rs_facture['heurefacture']);
                $jour=$d['mday'];
                $mois=$d['mon'];
                $annee=$d['year']."<br>".date("H:i",$rs_facture['heurefacture']);
            }
            $lignefactures=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid = '".$rs_facture['facturid']."'");
            $total_facture = 0 ;
            while ($lignefacture=$DB_site2->fetch_array($lignefactures))
            {
                $total_facture += ($lignefacture['prix'] * $lignefacture['qte']) ;
                if ($lignefacture['remboursement'])
                {
                    $remboursement += $lignefacture['prix'] * $lignefacture['qte'] ;
                }
            }

            //Montant total de l'installation
            //Liste des installations commandées
            $prix_installation_ttc = 0;
            $prix_installation_ht = 0;
            $sql = "SELECT *";
            $sql .= " FROM lignefactureinstallation";
            $sql .= " WHERE factureid = ".$rs_facture['facturid'];
            $req = $DB_site2->query($sql);
            if($DB_site2->num_rows($req) > 0){
                while($row = $DB_site2->fetch_array($req)){
                    $prix_installation_ttc += $row['lignefactureinstallation_differenceprix'];
                    $prix_installation_ht += $row['lignefactureinstallation_differenceprix'] / (1 + $row['lignefactureinstallation_tauxtva'] / 100);
                }
            }

            // Bon de réduction
            $prixcadeau = 0;
            if (in_array("13", $modules) && $rs_facture['cadeauid'] > 0)
            {
                $cadeau = $DB_site2->query_first("select cadeauid, montantcadeau from facture where facturid = '".$rs_facture['facturid']."'");
                if ($cadeau['cadeauid'] != "")
                {
                    $prixcadeau = $cadeau['montantcadeau'];
                }
            }
            // remise
            if (in_array("3", $modules))
            {
                $prixremise = 0;
                $remise_facture = $DB_site2->query_first("SELECT r.remiseid, r.typeremise, r.valeur, r.pctremise, f.montantremise FROM facture f inner join 
                        remise r on (f.remiseid = r.remiseid) WHERE f.facturid = '".$rs_facture['facturid']."'");
                if ($remise_facture['remiseid'] != "")
                {
                    $prixremise = $remise_facture['montantremise'];
                } // fin du if remise
            } // fin des remises
            // contre remboursement
            if ($rs_facture['moyenid'] == '3')
            {
                $prix_contre_remb = $rs_facture['prix_contre_remboursement'];
            }
            else
            {
                $prix_contre_remb = 0;
            }
            //Montant total de l'installation
            //Liste des installations commandées
            $prix_installation_ttc = 0;
            $sql = "SELECT *";
            $sql .= " FROM lignefactureinstallation";
            $sql .= " WHERE factureid = ".$rs_facture['facturid'];
            $req = $DB_site2->query($sql);
            if($DB_site2->num_rows($req) > 0){
                while($row = $DB_site2->fetch_array($req)){
                    $prix_installation_ttc += $row['lignefactureinstallation_differenceprix'];
                }
            }

            $float_total_facture=$total_facture + $rs_facture['prixgestion'] + $prix_contre_remb + $rs_facture['prixport'] - $prixcadeau - $prixremise+$prix_installation_ttc;
            $total_col+=$float_total_facture;
            $total_facture = number_format($float_total_facture, 2, ',', ' ')." ".$devises->symbole($rs_facture['devise']);
            if ($remboursement > 0)
            {
                $remboursement = "<br><font color=\"#E5840F\">-".number_format($remboursement, 2, ',', ' ')." €</font>" ;
            }
            else
            {
                $remboursement = "" ;
            }

            echo "<tr height=\"25\">";
            if ($rs_facture['numerofacture'] > 556500)
            {
                echo "<td class=\"".$rowalt."\" align=\"center\">".$rs_facture['codenumerofacture'].$rs_facture['numerofacture']."</td>";
                $lientelecharger = "<br><br><a href=\"https://".$host."/facture2.php?passession=oui&facturid=".$rs_facture['facturid']."&passe=".$rs_facture['passefacture']."&langue=FR\" target=\"_blank\">Facture</a>" ;
            }
            else
            {
                if ($rs_facture['valide'] == 1)
                {
                    echo "<td class=\"".$rowalt."\" align=\"center\"> -- </td>" ;
                    $lientelecharger = "<br><br><a href=\"https://".$host."/facture2.php?passession=oui&facturid=".$rs_facture['facturid']."&passe=".$rs_facture['passefacture']."&cde=1&langue=FR\" target=\"_blank\">Commande</a>" ;
                    /*
                    if (in_array("124", $modules))
                        {
                        $lientelecharger .= " <input type=\"checkbox\" name=\"chk_etiquette[$rs_facture[facturid]]\" />";
                        }
                    */
                }
                else
                {
                    echo "<td class=\"".$rowalt."\" align=\"center\"> -- </td>" ;
                    $lientelecharger = "<br><br>" ;
                }
            }
            $vip=usericon($rs_facture['vip'],20);
            $sav=$DB_site2->query_first("SELECT sav.id,value AS typelitige FROM sav INNER JOIN sav_fields ON ( sav.id = sav_fields.savid ) WHERE name = 'sav_typelitige' AND facturid=".$rs_facture['facturid']." AND fin=0 ORDER BY sav.date DESC");
            if($sav['id']!="") $vip.="<a href=\"facture.php?action=editer&facturid=".$rs_facture['facturid']."&savid=".$sav['id']."#sav\"><img src=\"images/".($sav['typelitige']!=5?"sav":"fraude").".png\" align=\"right\" height=\"20\"></a>";
            if($rs_facture['paysid']==57)
                $ville=substr($rs_facture['codepostal'],0,2)." ".$rs_facture['ville'];
            else
                $ville=$rs_facture['ville'];
            echo "<td class=\"".$rowalt."\">".$rs_facture['facturid']."<br>".$rs_facture['nom']."<br><a href=\"utilisateur.php?action=editer&userid=".$rs_facture['userid']."\" style=\"color:#404040;\">".$rs_facture['nom2']."</a>".$vip."<br>".$ville."</td>";
            echo "<td class=\"".$rowalt."\" align=\"center\">".$jour."/".$mois."/".$annee."</td>";
            echo "<td class=\"".$rowalt."\" align=\"right\" nowrap>".$total_facture." ".$remboursement."</td>";
//             echo "<td class=\"".$rowalt."\" align=\"center\">".$rs_facture['moyenpaiement']."</td>";
            if ($rs_facture['valide'] == 0)
            { // non traité
                $couleurlien = "#000000";
            }
            if ($rs_facture['valide'] == 1 || $rs_facture['valide']==7)
            { // Validée
                $couleurlien = "#0000FF";
            }
            if ($rs_facture['valide'] == 2)
            { // refus commercant
                $couleurlien = "#FF0000";
            }
            if ($rs_facture['valide'] == 3)
            { // refus carte bleue
                $couleurlien = "#FF0000";
            }
            if ($rs_facture['valide'] == 4)
            { // abandon internaute
                $couleurlien = "#FF0000";
            }
            if ($rs_facture['valide'] == 5 || $rs_facture['valide']==8)
            { // expédié
                $couleurlien = "#00FF00";
            }
            if ($rs_facture['valide'] == 6)
            { // expédié
                $couleurlien = "#FF0000";
            }
            if (($rs_facture['moyenid'] == 1 and $rs_facture['valide'] == 0))
            { // non aboutie
                $couleurlien = "#FF9933";
                $rs_facture[$libelle] = "Non aboutie";
            }
            $liensuppression = "";

            // vérification des envois aux fournisseurs
            $suivienvoi="";
            $advdate1=$advdate2=$advdate3="";
            $rappeltxt="";
            $efl=array();
            if(in_array($rs_facture['valide'],array(1,5,7,8))){
                $r=$DB_site2->query("SELECT * FROM envoi_fournisseur_ligne ef, lignefacture l WHERE ef.type='fourn' AND l.factureid=".$rs_facture['facturid']." AND ef.ligne_facture_id=l.lignefactureid");
                $fourncount=$DB_site2->num_rows($r);
                while($envoifourn=$DB_site2->fetch_array($r)){ $efl[$envoifourn['lignefactureid']]['in']['fourn']=$envoifourn['indate']; $efl[$envoifourn['lignefactureid']]['out']['fourn']=$envoifourn['outdate']; }
                $r=$DB_site2->query("SELECT * FROM envoi_fournisseur_ligne ef, lignefacture l WHERE ef.type='trspt' AND l.factureid=".$rs_facture['facturid']." AND ef.ligne_facture_id=l.lignefactureid");
                $trsptcount=$DB_site2->num_rows($r);
                while($envoitrspt=$DB_site2->fetch_array($r)){ $efl[$envoitrspt['lignefactureid']]['in']['trspt']=$envoitrspt['indate']; $efl[$envoitrspt['lignefactureid']]['out']['trspt']=$envoitrspt['outdate']; }
                $r=$DB_site2->query("SELECT * FROM envoi_fournisseur_ligne ef, lignefacture l WHERE ef.type='sls' AND l.factureid=".$rs_facture['facturid']." AND ef.ligne_facture_id=l.lignefactureid");
                $slscount=$DB_site2->num_rows($r);
                while($envoisls=$DB_site2->fetch_array($r)){ $efl[$envoisls['lignefactureid']]['in']['sls']=$envoisls['indate']; $efl[$envoisls['lignefactureid']]['out']['sls']=$envoisls['outdate']; }
                $lignenb=$DB_site2->query_first("SELECT count(*) FROM lignefacture WHERE factureid=".$rs_facture['facturid']);

                $suivienvoi="<div style=\"color:".envoicolor($fourncount,$lignenb[0]).";\">Fournisseur</div><div style=\"color:".envoicolor($trsptcount,$lignenb[0]).";\">Transport</div><div style=\"color:".envoicolor($slscount,$lignenb[0]).";\">Installation</div>";

                calcdates($efl);

                /*if($rs_facture['advdate1']!=0) $advdate1="Enlevement<br>".date("d/m/Y",$rs_facture['advdate1']);
       if($rs_facture['advdate2']!=0) $advdate2="Chez livreur<br>".date("d/m/Y",$rs_facture['advdate2']);
       if($rs_facture['advdate3']!=0) $advdate3="Chez client<br>".date("d/m/Y",$rs_facture['advdate3']);
       if($rs_facture['advdate1']==$today) $advdate1="<font color=\"red\">".$advdate1."</font>";
       if($rs_facture['advdate2']==$today) $advdate2="<font color=\"red\">".$advdate2."</font>";
       if($rs_facture['advdate3']==$today) $advdate3="<font color=\"red\">".$advdate3."</font>";*/

                $rq_rappel=$DB_site2->query_first("SELECT * FROM suivi_commande FORCE INDEX (facturid) WHERE facturid=$rs_facture[facturid] ORDER BY date DESC LIMIT 1");
                if($rq_rappel['rappel']!=0) $rappeltxt="<br><br><font color=\"".($rq_rappel['rappel']>$today && $rq_rappel['rappel']<$tomorrow?"red":"")."\">Rappel le ".date("d/m/Y à H:i",$rq_rappel['rappel']).($rq_rappel['pour']!=""?" ".$rq_rappel['pour']:"")."</font><br>";
                if($rq_rappel['suivi']!="") $rappeltxt.="<img src=\"../configurations/".$host."/images/templates/icone_info.gif\" onmouseover=\"document.getElementById('div$rs_facture[facturid]').style.visibility='visible';\" onmouseout=\"document.getElementById('div$rs_facture[facturid]').style.visibility='hidden';\" width=\"20\" align=\"absmiddle\"><div id=\"div$rs_facture[facturid]\" class=\"popup\">".tidy_repair_string($rq_rappel['suivi'])."</div>";

            }

            $sql = "SELECT *";
            $sql .= " FROM envoi_devis";
            $sql .= " WHERE facturid = ".$rs_facture['facturid'];
            $sql .= " ORDER BY date DESC";
            $devis = $DB_site2->query_first($sql);

            //Si la commande comprend un devis, affichage de l'icone et de l'utilisateur qui l'a réalisé
            if($devis['date'] != ""){
                $rappeltxt .= ""
                    . "<img src=\"images/devis.png\" width=\"24\">"
                    . "<br>"
                    . date("d/m/Y à H:i",$devis['date'])." par ".($devis['utilisateur']!=""?$devis['utilisateur']:$rq_rappel['commercial']);
            }
            //Si commande marketplace, le moyen de paiement est le nom de la marketplace
            if($rs_facture['provenance'] == "mp") $rs_facture['moyenpaiement'] = $marketplaces[$rs_facture['provenanceid']];

            //Si commande "Non traitée", aucun devis effectué, et mode de paiement autre que Sofinco et Facilités de paiement, on donne la possibilité de supprimer
            if($rs_facture['valide'] == 0 && $devis['date'] == "" && !in_array($rs_facture['moyenid'], array(17,18,99))) $liensuppression = "<a href=\"facture.php?action=cacher&facturid=".$rs_facture['facturid']."&lst_trietat=".$lst_trietat."\" target=\"_self\">Supprimer</a>";

            $datelivraison=($rs_facture['datelivraison']>0?date("d/m/Y",$rs_facture['datelivraison']):"?");
            $liendevis="";
            $passepar="Passée par ".($rs_facture['commercial']==""?$rq_rappel['commercial']:$rs_facture['commercial']);
            if($rs_facture['valide']==0) $liendevis="<br><a href=\"javascript:popdevis($rs_facture[facturid])\">Envoyer le devis</a>";
            echo "  <td class=\"$rowalt\" align=\"center\">$rs_facture[moyenpaiement]<br><font color=\"$couleurlien\">$rs_facture[$libelle]</font><br>$passepar</td>
<td rowspan=\"2\" class=\"$rowalt\" align=\"center\" >
<div  align=\"center\">
<a href=\"facture.php?action=editer&facturid=$rs_facture[facturid]&lst_trietat=$lst_trietat\" target=\"_self\">Editer</a>
$lientelecharger
$liensuppression
$liendevis
</div>
</td><td rowspan=\"2\" class=\"$rowalt\" style=\"line-height:18px; text-align:center;\">$suivienvoi <img src=\"../configurations/$host/images/templates/icone_info.gif\" title=\"".($rs_facture['suivi2'])."\" width=\"32\"></td>
<td rowspan=\"2\" class=\"$rowalt\">$rappeltxt</td>
<td rowspan=\"2\" class=\"$rowalt\"".($rs_facture['datelivraison']<=time()?" style=\"color:red;\"":"").">".$datelivraison."</td>
<td rowspan=\"2\" class=\"$rowalt\">$advdate1</td>
<td rowspan=\"2\" class=\"$rowalt\">$advdate2</td>
<td rowspan=\"2\" class=\"$rowalt\">$advdate3</td>
</tr>";

            $lignefactures=$DB_site2->query("SELECT lf.*,a.artcode_eurocave FROM lignefacture lf LEFT JOIN article a ON(a.artid=lf.artid) WHERE lf.factureid = '$rs_facture[facturid]'");
            $articles="";
            while ($lignefacture=$DB_site2->fetch_array($lignefactures)){
                if($lignefacture['prix']>0) $articles.=$lignefacture['qte']." <a href=\"clientnavig.php?userid=$rs_facture[userid]&goto=art,".$lignefacture['artid'].",-.html\" target=\"_blank\">".$lignefacture['artcode']."</a> <a href=\"ajout_produit.php?artid=".$lignefacture['artid']."&prec=modif\" target=\"_blank\">(".$lignefacture['artcode_eurocave'].")</a><br>";
            }
            echo "<tr><td colspan=\"5\" class=\"$rowalt\">$articles</td></tr>";
        }
        echo "<tr><td></td><td>Total (".$DB_site2->num_rows($rq_facture).")</td><td></td><td>".round($total_col,2)."</td></tr>";
    }
    else
    {
        echo "  <tr>
    <td align=\"center\">Il n'y a aucune facture</td>
    </tr>";
    }
    echo "</table>" ;
    /*
    if (in_array("124", $modules))
        {
        if ($DB_site2->num_rows($rq_facture) > 0)
            {
            echo "<br><br>
            <div align=\"left\">Impression des étiquettes : commencer à partir de <input type=\"text\" size=\"1\" name=\"nb_etiquette\" align=\"absmiddle\"/>&nbsp;&nbsp;&nbsp;<input type=\"submit\" value=\"Lancer\" /></div>";
            //fin form etiquettes
            echo "</form>";
            }
        }
    */
    echo"<br>$pagenav";
}

if($affiche_validation_finale){

    ?>
    <div class="commande_detail" style="font-size: 12px; text-align: center;">
        <h2>Validation de la commande n°<span style="color:#FF5A08;"><?php echo $_POST['facturid']; ?></span></h2>
        <u>Vous souhaitez</u> :<br>
        <form method="post" action="" name="validation_cmd_final" style="margin: 10px 0; padding: 0;">
            <input type="hidden" name="action" value="<?php echo $finale_action; ?>">
            <input type="hidden" name="facturid" value="<?php echo $_POST['facturid']; ?>">
            <input type="checkbox" name="send_mail" value="1" checked="checked" onchange="check_facture(this);"> Avertir le client par email automatique
            <?php if($affiche_check_facture){ ?>
                <br>
                <input type="checkbox" name="add_invoice" value="1" checked="checked" id="input_invoice"> Joindre la facture
            <?php } ?>
        </form>
        <a href="javascript:void(0)" onclick="if(confirm('Valider la commande n°<?php echo $facturid; ?> ?')){ document.forms.validation_cmd_final.submit(); }" class="actions expedier" style="margin-right: 10px; display: initial;">Valider</a>
        <a href="facture.php?action=editer&facturid=<?php echo $_POST['facturid']; ?>&lst_trietat=" class="actions annuler" style="display: initial;">Retour</a>
        <br><br>
    </div>
    <?php

}

if($affiche_refus_final){

    ?>
    <div class="commande_detail" style="font-size: 12px; text-align: center;">
        <h2>Annulation de la commande n°<span style="color:#FF5A08;"><?php echo $_POST['facturid']; ?></span></h2>
        <u>Vous souhaitez</u> :<br>
        <form method="post" action="" name="refus_cmd_final" style="margin: 10px 0; padding: 0;">
            <input type="hidden" name="action" value="dorefuser">
            <input type="hidden" name="facturid" value="<?php echo $_POST['facturid']; ?>">
            <input type="checkbox" name="send_mail" value="1" checked="checked"> Avertir le client par email automatique
        </form>
        <p style="color: #CC0000;">Attention : cette action n'est pas réversible !</p>
        <a href="javascript:void(0)" onclick="if(confirm('Annuler la commande n°<?php echo $facturid; ?> ?')){ document.forms.refus_cmd_final.submit(); }" class="actions expedier" style="margin-right: 10px; display: initial;">Valider l'annulation</a>
        <a href="facture.php?action=editer&facturid=<?php echo $_POST['facturid']; ?>&lst_trietat=" class="actions annuler" style="display: initial;">Retour</a>
        <br><br>
    </div>
    <?php

}

if($affiche_expedition_finale){
    $chkconsignes="";
    include_once("adv/adv.class.php");
    $facture = new facture($DB_site2,$facturid);

    if($facture->moyenid == 17){ /// si paiement sofinco, on vérifie que le délai de rétractation de 14jours est passé ou non
        //$apiUrl="https://r7weblong.transcred.com/websrv/index.asp";
        $apiUrl="https://weblong.transcred.com/websrv/index.asp";
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
        <Service ServiceName=\"FLUX_D\">
        <IN>
        <DossiersCollection>
        <Dossier DossierNumber=\"".$facture->no_dossier."\"/>
        </DossiersCollection>
        </IN>
        </Service>
        ";

        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL,$apiUrl);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $data = curl_exec($ch);
        curl_close($ch);

        $data_xml=simplexml_load_string($data, 'SimpleXMLElement');
        $json = json_encode($data_xml);
        $array = json_decode($json,TRUE);
        $array_dossiers[]=$array['OUT']['DossiersCollection']['Dossier']['@attributes'];
        $array_dossiers=array_filter(array_unique($array_dossiers,SORT_REGULAR));
        foreach($array_dossiers as $dossier) {
            $date_signature=strtotime($dossier['DateSignature']);
        }
        if((time() - $date_signature) < 1209600){
            $alert_sofinco="<p><b>/!\ Paiement sofinco. Attention, le délai de rétractation de 14 jours pour le client n'est pas passé /!\</b></p>";
        }
    }

    foreach($facture->transportsClient() as $efid=>$mailname){
        $ef=$DB_site2->query_first("SELECT destinataire FROM envoi_fournisseur WHERE id=".$efid);
        $adv=$DB_site2->query_first("SELECT af.value FROM adv a INNER JOIN adv_fields af ON af.advid = a.id INNER JOIN envoi_fournisseur ef ON ef.facturid = a.facturid WHERE ef.id=".$efid." AND af.name='trsptmode' AND af.value='parenlevementdestinataire'");
        if($adv['value']=='parenlevementdestinataire'){
            $mailname='Retrait par le client';
        }
        $chkconsignes.="<br><input type=\"checkbox\" name=\"consignes[".$efid."]\" value=\"".$mailname."\" checked> Transmettre les consignes de réception ".$ef['destinataire'];
    }

    ?>
    <div class="commande_detail" style="font-size: 12px; text-align: center;">
        <h2>Expédition de la commande n°<span style="color:#FF5A08;"><?php echo $_POST['facturid']; ?></span></h2>
        <u>Vous souhaitez</u> :<br>
        <form method="post" action="" name="expedition_cmd_final" style="margin: 10px 0; padding: 0;">
            <input type="hidden" name="action" value="<?php echo $finale_action; ?>">
            <input type="hidden" name="facturid" value="<?php echo $_POST['facturid']; ?>">
            <!--<input type="checkbox" name="add_consignes" value="1" checked="checked" id="input_consignes"> Transmettre les consignes de réception-->
            <?=$chkconsignes; ?>
            <br>
            <input type="checkbox" name="add_invoice" value="1" checked="checked" id="input_invoice"> Joindre la facture
        </form>
        <p style="color: #CC0000;">Attention : cette action n'est pas réversible !<br>Le stock des articles concernés sera automatiquement décrémenté</p>
        <?php echo $alert_sofinco; ?>
        <a href="javascript:void(0)" onclick="if(confirm('Expédier la commande n°<?php echo $facturid; ?> ?')){ document.forms.expedition_cmd_final.submit(); }" class="actions expedier" style="margin-right: 10px; display: initial;">Valider l'expédition</a>
        <a href="facture.php?action=editer&facturid=<?php echo $_POST['facturid']; ?>&lst_trietat=" class="actions annuler" style="display: initial;">Retour</a>
        <br><br>
    </div>
    <?php

}

if ($action == "rembourser"){

    if (is_array($_POST['rembourser']) and count($_POST['rembourser']) > 0){
        $facture_remboursee=$DB_site2->query_first("SELECT * FROM facture WHERE facturid = '$factureid'");
        echo "<strong>Remboursement</strong><br><br>
        Vous avez demandé le remboursement des articles suivants de la facture $facture_remboursee[codenumerofacture]$facture_remboursee[numerofacture] : <br>
        <ul>" ;
        $lignesfactures = array() ;
        $remboursement = 0 ;
        $achecked=$fdp=$display="";

        foreach ($_POST['rembourser'] as $key => $value) {
            if ($value == 'on'){
                $lignesfactures[]= $key ;
                $ligne_remboursee=$DB_site2->query_first("SELECT * FROM lignefacture WHERE lignefactureid = '$key'");
                echo "<li>$ligne_remboursee[libelle] : ".number_format($ligne_remboursee[prix], 2, ",", " ")." € (x$ligne_remboursee[qte])</li>" ;
                $remboursement += $ligne_remboursee[prix] * $ligne_remboursee[qte] ;
            }
        }

        if($_POST['integral']=="1"){

            $display="block";
            $achecked="checked";
            $remboursement+=$facture_remboursee['prixport'];
            if($facture_remboursee['prixport']>0){
                echo "<li>Frais de port : ".number_format($facture_remboursee['prixport'],2,',','')." €</li>";
                $fdp="checked";
            }
            if ($facture_remboursee[cadeauid] != ""){
                $infoscadeau=$DB_site2->query_first("SELECT * FROM cadeau WHERE cadeauid = '$facture_remboursee[cadeauid]'");
                if($infoscadeau['code']!=""){
                    $cadeau="checked";
                    $remboursement-=$facture_remboursee['montantcadeau'];
                    echo "<li>Code promo ".$infoscadeau['code']." : -".number_format($facture_remboursee['montantcadeau'],2,',','')." €</li>";
                }
            }

            $infocaviste=$DB_site2->query_first("SELECT * FROM code_caviste WHERE code='$facture_remboursee[cadeauid]'");
            if($infocaviste['type']==1 && $infocaviste['mini']>$facture_remboursee['montantcadeau']){
                $remboursement-=$facture_remboursee['montantcadeau'];
                $cadeau="checked";
                echo "<li>Code caviste ".$infocaviste['code']." : -".number_format($facture_remboursee['montantcadeau'],2,',','');
            }
            if($infocaviste['type']==2){
                $remboursement-=$facture_remboursee['montantcadeau'];
                $cadeau="checked";
                echo "<li>Code caviste ".$infocaviste['code']." : -".number_format($facture_remboursee['montantcadeau'],2,',','');
            }
        }

        echo "</ul>
        <br>Soit un total de <strong>".number_format($remboursement, 2, ",", " ")." €</strong> à rembourser<br>
        <br>Veuillez valider ce choix pour prendre en compte le remboursement dans la facture.<br>

        <form method=\"post\" action=\"facture.php\" name=\"form_remb\">
        <input type=\"hidden\" name=\"action\" value=\"dorembourser\">
        <input type=\"hidden\" name=\"factureid\" value=\"$factureid\">
        <input type=\"hidden\" name=\"lignesfactures\" value=\"".implode(',',$lignesfactures)."\">

        <br><input type=\"checkbox\" name=\"avoir\" value=\"1\" onclick=\"document.getElementById('detailremb').style.display=this.checked?'block':'none';\" $achecked> Créer un avoir
        <div id=\"detailremb\" style=\"display:$display;\">";

        if($facture_remboursee['prixport']>0) echo "<input type=\"checkbox\" name=\"fdp\" value=\"1\" $fdp> Rembourser les frais de port";
        if($facture_remboursee['cadeauid']!="0") echo "<input type=\"checkbox\" name=\"cadeau\" value=\"1\" $cadeau> Déduire la réduction";

        echo "</div><br><a href=\"Javascript: document.form_remb.submit();\">Confirmer</a> &nbsp;&nbsp; <a href=\"facture.php?action=editer&facturid=$factureid\">Annuler</a>
        </form>" ;
    }
}

if ($action == "dorembourser")
{
    $date_remboursement = date('Y-m-d');
    $DB_site2->query("UPDATE lignefacture SET remboursement = '1', date_remboursement = '$date_remboursement', modifby='$user_info[username]' WHERE lignefactureid IN (".$lignesfactures.")");
    $sp=explode(',',$lignesfactures);
    $txtsuivi="";
    foreach($sp as $lfid){
        $lf=$DB_site2->query_first("SELECT * FROM lignefacture WHERE lignefactureid=".$lfid);
        $txtsuivi.="<br>".$lf['artcode'];
    }
    $DB_site2->query("INSERT INTO suivi_commande SET facturid=$factureid, date=".time().", commercial='".$user_info[username]."', suivi='Remboursement$txtsuivi'");
    $facturid = $factureid ;
    $action = "editer" ;
    if(isset($_POST['avoir'])){
        print "<script language=\"javascript\">
            document.location='facture.php?action=creer&createavoir=$lignesfactures&fdp=".$_POST['fdp']."&cadeau=".$_POST['cadeau']."';
            </script>
            Création avoir ...";
        exit;
    }
}

if ($action == "regenererfacture") {
    // regeneration de PDF de facture
    echo "on regenere le PDF de la facture numero ".$facturid;
    $facture = new facture($DB_site2,$facturid);
    @unlink("../configurations/$host/factures/".$facturid.".pdf");

    $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );
    $facturepdf = file_get_contents("https://".$host."/facture2.php?facturid=".$facturid."&passe=".$facture->passefacture,FALSE,stream_context_create($arrContextOptions));
    try{
        $f = fopen("../configurations/$host/factures/".$facturid.".pdf","w");
        if($f != false){
            fputs($f, $facturepdf);
            fclose($f);
        }
    } catch(Exception $e){
        print "impossible d'ouvrir la facture";
    }
    //Redirection vers la page de traitement de commande initiale
    //header('Location: facture.php?action=editer&facturid='.$facturid.'&lst_trietat=');
    //exit;
}


if (isset($action) and $action == "ajouter_champ")
{
    $erreur_champ_libre = "" ;
    if (isset($champ_libre_artcode) and $champ_libre_artcode != "" and isset($champ_libre_libelle) and $champ_libre_libelle != "" and isset($champ_libre_montant) and $champ_libre_montant != "")
    {
        if (ereg(",", $champ_libre_montant))
        {
            $champ_libre_montant = str_replace(",", ".", $champ_libre_montant) ;
        }
        if (is_numeric($champ_libre_montant)/* and $champ_libre_montant > 0*/)
        {
            $DB_site2->query("insert into lignefacture (factureid, artcode, qte, libelle, prix, tva) 
            values ('$factureid', '$champ_libre_artcode', '1', '$champ_libre_libelle', '$champ_libre_montant', '0')");
            $champ_libre_artcode = "" ;
            $champ_libre_libelle = "" ;
            $champ_libre_montant = "" ;
        }
        else
        {
            $erreur_champ_libre .= "Le montant doit être numerique.<br>" ;
        }
    }
    else
    {
        $erreur_champ_libre .= "Vous devez entrer une référence, une designation et un montant.<br>" ;
    }
    if ($erreur_champ_libre != "")
    {
        $erreur_champ_libre = "<font class=\"cost2\">$erreur_champ_libre</font>" ;
        $champ_libre_artcode = stripslashes($champ_libre_artcode) ;
        $champ_libre_libelle = stripslashes($champ_libre_libelle) ;
        $champ_libre_montant = stripslashes($champ_libre_montant) ;
    }
    $facturid = $factureid ;
    $action = "editer" ;
}

// Edition / detail d'une facture
if ($action == "editer" || $_GET['action'] == 'editer')
{

    include_once("adv/envoi_fournisseur.php");
    //include_once("adv/adv.class.php");
    include_once("../class/offre_fournisseur.php");
    include("adv/adv2.php");

    ?>
    <style>
        .liens_rapides{
            position:fixed;
            z-index:1001;
            margin-left:1010px;
            background-color:#fff;
            padding:10px;
            list-style-type:none;
            list-style-image:none;
            border: 1px solid #000;
        }
    </style>
    <ul class="liens_rapides">
        <li><a class="js-scrollTo" href="#facture_details">Détails</a></li>
        <li><a class="js-scrollTo" href="#facture_coordonnees">Coordonnées</a></li>
        <li><a class="js-scrollTo" href="#table_article">Produits</a></li>
        <li><a class="js-scrollTo" href="#tablinkrecap">ADV</a></li>
        <li><a class="js-scrollTo" href="#detail_stock">Stock</a></li>
        <li><a class="js-scrollTo" href="#detail_marge">Marge</a></li>
        <li><a class="js-scrollTo" href="#calcul_marge">Calcul Marge</a></li>
        <li><a class="js-scrollTo" href="#suivi_commercial">Suivi commercial</a></li>
        <li><a class="js-scrollTo" href="#suivi_client">Suivi client</a></li>
    </ul>
    <script>
        var $jq = jQuery.noConflict();
        $jq(document).ready(function() {
            $jq('.js-scrollTo').each(function() {
                var page = $jq(this).attr('href');
                if ($jq(page).length==0) {
                    $jq(this).hide();
                }
            });
            $jq('.js-scrollTo').on('click', function() { // Au clic sur un élément
                var page = $jq(this).attr('href'); // Page cible
                var speed = 200; // Durée de l'animation (en ms)
                $jq('html, body').animate( { scrollTop: $jq(page).offset().top }, speed ); // Go
                return false;
            });
        });
    </script>

    <?php
//verif marge
    if(isset($_GET['margeverif']) && $_GET['margeverif'] == 'ok') {
        $margetotal=new marge($DB_site2,$facturid);
        $margett = $margetotal->margetotal($DB_site2,$facturid);
        if($margett['totalfactureht'] != 0.00 && $margett['totalachat'] != 0.00) {
            if($margett['parentid'] != '') {
                $DB_site2->query("UPDATE facture SET marge ='".$margett["margett_pc"]."' WHERE facturid ='".$margett['parentid']."'");
            }
            $DB_site2->query("UPDATE facture SET marge ='".$margett["margett_pc"]."' WHERE facturid ='".$facturid."'");
        }
        $facture_user = new facture($DB_site2,$facturid);
        if($margett["margett_pc"] < 20 && $margett["margett_pc"] != "") {
            $mail=new email();
            $mail->destinataire	= "pr@mon-espace-cuisson.com";
            $mail->to 			= ["pr@mon-espace-cuisson.com", 'webmaster@ma-cave-a-vin.fr'];
            $mail->expediteur	= "contact@mon-espace-cuisson.com";
            $mail->expname		= NOM_CONTACT;
            $mail->subject		= "Analyse rentabilité";
            $mail->body			= "Une analyse de rentabilité de commande est tombée sous les 20% :
							<br>Commande N° : <a href='https://www.mon-espace-cuisson.com/admin/facture.php?action=editer&facturid=".$_GET['facturid']."#detail_marge'>".$facturid."</a>
							<br>Nom : " .$facture_user->prenom . " " . $facture_user->nom;
            $mail->sendmail();
            $sql  = "INSERT INTO suivi_commande SET";
            $sql .= " facturid    = ".$facturid;
            $sql .= ", date       = ".time();
            $sql .= ", commercial = '".$user_info['username']."'";
            $sql .= ", suivi      = 'l\'analyse de marge a trouvé une marge de ".$margett["margett_pc"]."%'";
            $DB_site2->query($sql);
        }
    }


    //modification compte facturation
    if(isset($_POST['modiffacturation'])){
        $new_userid = $_POST['useridmf_new'];
        $DB_site2->query("UPDATE facture SET userid ='".$new_userid."' WHERE facturid ='".$facturid."'");
    }

    if(isset($_POST['etatRenta'])){ // Analyse de rentabilite
        $dateRenta = time();
        $notes = mysqli_real_escape_string($DB_site2->link_id,$_POST['noteEtudeRenta']);
        $suivi_renta = "Etude de Rentabilité : ".$notes;

        $DB_site2->query("INSERT INTO facture_rentabilite (facturid,date, personne,etat,note) VALUES('$facturid','$dateRenta','$admin_username',$_POST[etatRenta],'$notes')");
        $DB_site2->query("INSERT INTO suivi_commande (facturid,date,commercial,suivi) VALUES ('$facturid','".time()."','$admin_username', '$suivi_renta')");
    }
    if(isset($_POST['modifRenta'])){// modification du message de l'analyse de rentabilité
        $notes = mysqli_real_escape_string($DB_site2->link_id,$_POST['noteEtudeRenta']);
        $suivi_renta = "Etude de Rentabilité : ".$notes;

        $DB_site2->query("UPDATE facture_rentabilite SET note = '".$notes."' WHERE facturid='".$facturid."'");
        $DB_site2->query("INSERT INTO suivi_commande (facturid,date,commercial,suivi) VALUES ('$facturid','".time()."','$admin_username', '$suivi_renta')");
    }
    //On dissocie
    if(isset($_POST['dissocier'])){
        $diss = new dissociation($DB_site2, $_POST['facturid']);
    }

    $f=new facture($DB_site2,$facturid);
// var_dump($f->verif_avant_exp());

    // mise à jour du suivi client
    if(isset($_POST['suivi']) && $_POST['suivi'] != ''){
        $savid=0;
        if(isset($_POST['choixsav_suivi'])) $savid = $_POST['choixsav_suivi'];
        $tsrappel = 0;
        if($_POST['rappel'] != ""){

            $sp       = preg_split('/[- :\/h]/',$_POST['rappel']);
            $tsrappel = mktime($sp[3],$sp[4],0,$sp[1],$sp[0],$sp[2]);

            //On efface tous les précédents rappels si l'utilisateur se remet un rappel sur la commande
            if($user_info['username'] == $_POST['pour'] && $user_info['username'] == 'qualite'){

                $sql = "UPDATE suivi_commande SET";
                $sql .= " rappel = 0";
                $sql .= ", pour = ''";
                $sql .= " WHERE 1";
                $sql .= " AND commercial = '".$user_info['username']."'";
                $sql .= " AND pour       = '".$user_info['username']."'";
                $sql .= " AND facturid   = '".$facturid."'";
                $sql .= " AND savid      = '".$savid."'";
                $DB_site2->query($sql);
            }

            // test pour bug 4062
            if($_POST['pour']==""){
                $_POST['pour']=$user_info['username'];
            }
        }

        $hide = intval(!isset($_POST['box']));

        $sql = "INSERT INTO suivi_commande SET";
        $sql .= " date          = ".time()."";
        $sql .= ", moyen        = '".$_POST['moyen']."'";
        $sql .= ", type         = \"".$_POST['type']."\"";
        $sql .= ", destinataire = \"".intval($_POST['destinataire'])."\"";
        $sql .= ", commercial   = '$user_info[username]'";
        $sql .= ", suivi        = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['suivi'])."'";
        $sql .= ", facturid     = $facturid";
        $sql .= ", rappel       = $tsrappel";
        $sql .= ", rappeltype   = '".$_POST['rappeltype']."'";
        $sql .= ", jour         = ".intval($_POST['jour'])."";
        $sql .= ", pour         = '".$_POST['pour']."'";
        $sql .= ", hide         = $hide";
        $sql .= ", callid       = '".$_POST['callid']."'";
        $sql .= ", savid        = '".$savid."'";
        $DB_site2->query($sql);
    }

    if(isset($_GET['datelivr_client'])){
        $f=$DB_site2->query_first("SELECT datelivraison FROM facture WHERE facturid=".$facturid);
        $suivi="Modification date livraison : ".date("d/m/Y",$f[0])." => ".date("d/m/Y",$_GET['datelivr_client']);
        $DB_site2->query("INSERT INTO suivi_commande SET date=".time().", commercial='$user_info[username]', suivi='".addslashes($suivi)."', facturid=$facturid");
        $DB_site2->query("UPDATE facture SET datelivraison=".intval($_GET['datelivr_client']).", changelivraison='".$user_info['username']."' WHERE facturid=".$facturid);
    }

    // Enregistrement envoi demande dispo fournisseur
    if(isset($_POST['dispo'])){
        foreach($_POST['dispo'] as $lignefactureid=>$txtbis){
            $cal=$DB_site2->query_first("SELECT objid FROM calendrier WHERE objid='".$lignefactureid."'");
            if($cal['objid']==$lignefactureid)
                $DB_site2->query("UPDATE calendrier SET dispo=1 WHERE objid='".$lignefactureid."'");
            else
                $DB_site2->query("INSERT INTO calendrier SET objid='".$lignefactureid."', type='fourncmd', expediteur='".$user_info['username']."', dispo=1");
        }
    }

    if(isset($_GET['diss'])) $diss=new dissociation($DB_site2,$facturid);

    if(isset($_POST['cdeclient'])) $DB_site2->query("UPDATE facture SET clientcde='".mysqli_real_escape_string($DB_site2->link_id,$_POST['cdeclient'])."' WHERE facturid=".intval($facturid));

    if(isset($_POST['numcommande'])) $DB_site2->query("UPDATE facture SET mp_facturid='".mysqli_real_escape_string($DB_site2->link_id,$_POST['numcommande'])."' WHERE facturid=".intval($facturid));

    //Modification d'une ligne
    if (isset ($etat) && $etat == "update_ligne")
    {
        //MAJ de lignefacturecaracteristique
        if (($nb_caract = count($caracteristiques)) > 0)
        {
            //On vérifie qu'un article avec les nouvelles caractéristiques n'existe pas déjà
            $rq_caracteristiques_select = "SELECT l.lignefactureid, l.qte ";
            $rq_caracteristiques_from = "FROM lignefacture l ";
            $rq_caracteristiques_inner = "";
            $rq_caracteristiques_where = "WHERE factureid = '$facturid' AND catid = '$catid' AND artid = '$artid' ";
            $rq_caracteristiques_and = "AND l.lignefactureid != '$lignefactureid' ";

            $rq_caracteristiques_on = 'l';

            for ($i = 0; $i < $nb_caract; $i++)
            {
                //Récupération des libelles des caracteristiques
                $rs_caract_lib = $DB_site2->query_first("SELECT cv.$libelle as libellecaractval, c.$libelle as libellecaract FROM caracteristiquevaleur cv 
                INNER JOIN caracteristique c ON (cv.caractid = c.caractid) WHERE cv.caractvalid = '$caracteristiques[$i]'");
                //Création de la ligne INNER JOIN
                $rq_caracteristiques_inner .= 'INNER JOIN lignefacturecaracteristique lc' . $i . ' ON (' . $rq_caracteristiques_on . '.lignefactureid = lc' . $i . '.lignefactureid) ';
                $rq_caracteristiques_on = 'lc' . $i;
                //Creation de la ligne AND
                $rq_caracteristiques_and .= 'AND lc' . $i . '.libelle_caract = \'' . $rs_caract_lib[libellecaract] . '\' ';
                $rq_caracteristiques_and .= 'AND lc' . $i . '.libelle_caractval = \'' . $rs_caract_lib[libellecaractval] . '\' ';
            }
            //Construction de notre requête
            $rq_present = $rq_caracteristiques_select . $rq_caracteristiques_from . $rq_caracteristiques_inner . $rq_caracteristiques_where . $rq_caracteristiques_and;
            $rs_present = $DB_site2->query_first($rq_present);

            if ($rs_present[lignefactureid] != '')
            {
                //Un article similaire est déjà présent, on le met à jour avec les nouvelles valeurs
                //On supprime dans un premier temps l'article mis à jour
                $rs_dellignefacturechamp = $DB_site2->query("DELETE FROM lignefacture WHERE lignefactureid=$lignefactureid");
                $rs_dellignefacturecaract = $DB_site2->query("DELETE FROM lignefacturecaracteristique WHERE lignefactureid=$lignefactureid");
                //on met à jour lignefactureid qui prends la valeur de l'autre article identique
                $lignefactureid = $rs_present[lignefactureid];
                //Seul la quantité à besoin d'être modifiée
                $rq_maj_qte_prix = 'qte = (qte+\'' . $qte . '\') ';
            }
            else
            {
                //Aucun article ne correspond, on peut modifier notre article tranquillement
                $lignefacturecaracteristiqueid = explode("|", $lignefacturecaractid);
                //Pour chaque caracteristiques, on cherche le libellé puis la difference de prix
                for ($i = 0; $i < $nb_caract; $i++)
                {
                    $rs_caract = $DB_site2->query_first("SELECT cv.$libelle as libellecaractval, c.$libelle as libellecaract FROM caracteristiquevaleur cv 
                    INNER JOIN caracteristique c ON (cv.caractid = c.caractid) WHERE cv.caractvalid = '$caracteristiques[$i]'");

                    $rs_diffprix = $DB_site2->query_first("SELECT ac.differenceprix FROM caracteristiquevaleur cv INNER JOIN article_caractval ac ON 
                    (cv.caractvalid = ac.caractvalid) WHERE cv.caractvalid = '$caracteristiques[$i]'");

                    if ($rs_diffprix[differenceprix] != '')
                        $diffprix += $rs_diffprix[differenceprix];

                    $rs_updatelignefacturecaract = $DB_site2->query("UPDATE lignefacturecaracteristique SET libelle_caract = '$rs_caract[libellecaract]',
                    libelle_caractval = '$rs_caract[libellecaractval]' WHERE lignefacturecaracteristiqueid = '$lignefacturecaracteristiqueid[$i]'");
                }
                //On ajoute la différence au prix de l'article
                $rs_prixarticle = $DB_site2->query_first("SELECT prix FROM article WHERE artid = $artid");
                $prixfinal = $rs_prixarticle[prix] + $diffprix;
                $rq_maj_qte_prix = 'qte = \'' . $qte . '\', prix = \'' . $prixfinal . '\' ';
            } //end if($rs_present[lignefactureid] != '')
        }
        else
        {
            //Article sans caracteristique, il ne peut donc y avoir de doublons
            //On modifie donc uniquement la quantité, le ayant déjà été calculé
            $rq_maj_qte_prix = 'qte = \'' . $qte . '\' ';
        } //end if (($nb_caract = count($caracteristiques)) > 0)

        //On modifie la lignefacture
        $rs_updatelignefacture = $DB_site2->query("UPDATE lignefacture SET $rq_maj_qte_prix WHERE lignefactureid = '$lignefactureid'");
        //Prix total de la commande
        $prixtotal = $DB_site2->query_first("SELECT SUM(lf.qte*lf.prix) as prixtotal FROM facture f INNER JOIN lignefacture lf ON (f.facturid = lf.factureid) 
                WHERE f.facturid = '$facturid' AND remboursement='0'");
        //Poids total de la facture
        $poidstotal = $DB_site2->query_first("SELECT SUM(a.poid*lf.qte) as poidstotal FROM article a INNER JOIN lignefacture lf ON (a.artid = lf.artid) 
                WHERE lf.factureid = '$facturid'");

        // frais de port
        $gratuitfrais = $DB_site2->query_first("SELECT distinct gratuit FROM fraisport fp INNER JOIN facture f ON (fp.paysid = f.paysid) 
                WHERE f.facturid = '$facturid'");

        if (($gratuitfrais[gratuit] != 0) and ($prixtotal[prixtotal] >= $gratuitfrais[gratuit]))
            // si on paie pas de frais de port !!
            $DB_site2->query("UPDATE facture SET prixport = '0' WHERE facturid = '$facturid'");
        else
        {
            // on fait tout le traitement des frais de port !!
            $paysid = $DB_site2->query_first("SELECT paysid FROM facture WHERE facturid = '$facturid'");

            $fraisport = $DB_site2->query_first("SELECT * FROM fraisport WHERE poiddebut <= '$poidstotal[poidstotal]' AND poidfin >= '$poidstotal[poidstotal]' 
                    and paysid = '$paysid[paysid]'");

            if ($fraisport[fraisportid] == "")
            {
                // On prend le frais de port par défaut
                $rs_fraisportpays = $DB_site2->query_first("SELECT prixfraisport FROM pays WHERE paysid = '$paysid[paysid]'");
                $prixfraisport = $rs_fraisportpays[prixfraisport];
            }
            else
                //On prend le frais de port correspondant
                $prixfraisport = $fraisport[prix];

            //MAJ des frais de port
            $DB_site2->query("UPDATE facture SET prixport = '$prixfraisport' WHERE facturid = '$facturid'");
        }
    }

    //Une erreur à été commise lors de la mise à jour des coordonnées, on reaffiche les valeurs saisies
    if (isset ($errmsg) && $errmsg == "1")
    {
        $rs_facture = $DB_site2->query_first("SELECT f.datefacture, f.heurefacture, datelivraison,
                f.infotranspo,f.incoterms, f.valide, mp.$libelle, f.prixgestion,
                f.prixport, f.dateexpedition, u.mail, f.moyenid, f.clientcde 
                cadeauid, f.prix_contre_remboursement, f.transaction_id,
                f.montant_credit, f.TEG_annuel, f.num_dossier,
                f.nb_mensualites, f.commercial_attribution, f.avis f.txtlivr
                FROM facture f
                INNER JOIN utilisateur u ON (f.userid = u.userid)
                INNER JOIN moyenpaiement mp ON (f.moyenid = mp.moyenid)
                WHERE f.facturid = '$facturid'");
        $rs_facture[mail_facture] = $mail_facture;
        $rs_facture[raisonsociale] = $raisonsociale;
        $rs_facture[civilite] = $civilite;
        $rs_facture[nom] = $nom;
        $rs_facture[prenom] = $prenom;
        $rs_facture[adresse] = $adresse;
        $rs_facture[codepostal] = $codepostal;
        $rs_facture[ville] = $ville;
        $rs_facture[telephone] = $telephone;
        $rs_facture[facturid] = $facturid;
        $rs_facture[userid] = $userid;
        $rs_facture[paysid] = $paysid;
    }
    else
    {
        $rs_facture = $DB_site2->query_first("SELECT f.facturid, f.infotranspo,f.incoterms, f.datefacture, f.heurefacture,
                f.datelivraison, f.changelivraison, f.civilite, f.nom, f.exp_accord_client, f.date_exp_accord_client,
                f.prenom, f.mail, f.mail as mail_facture, f.adresse, 
                f.codepostal, f.ville, f.telephone, f.raisonsociale,f.langue,
                f.valide, mp.$libelle, f.prixgestion, f.prixport,
                f.tvaport, f.dateexpedition, f.moyenid, f.paysid,
                cadeauid, montantcadeau, clientcde, f.prix_contre_remboursement,
                u.userid, f.modelivraison, f.infotranspo,
                f.sav, f.provenance, f.provenanceid,f.commission,
                f.mp_facturid, f.passefacture, f.numerofacture, f.commercial,
                f.site_code, f.transaction_id, f.montant_credit, f.TEG_annuel,
                f.num_dossier, f.nb_mensualites, f.commercial_attribution, f.avis, f.txtlivr,f.gclid
                FROM facture f
                INNER JOIN utilisateur u ON (f.userid = u.userid)
                INNER JOIN moyenpaiement mp ON (f.moyenid = mp.moyenid)
                WHERE f.facturid = '$facturid'");
    }
    if($rs_facture['site_code']=="") $rs_facture['site_code']="base";

    // Coordonnées Client
    $user=$DB_site2->query_first("SELECT u.*,p.libelle,p.diminutif FROM utilisateur u, pays p WHERE u.paysid = p.paysid and u.userid = '$rs_facture[userid]'");
    $pays_fact=$DB_site2->query_first("SELECT libelle,diminutif FROM pays WHERE paysid = '$rs_facture[paysid]'");


    $etatfacture=$DB_site2->query_first("SELECT * FROM etatfacture WHERE valudeid = '$rs_facture[valide]'");
    switch ($rs_facture['valide'])
    {
        case 0:
            $couleurlien = "#000000";
            break;
        case 1:
        case 7:
            $couleurlien = "#0000FF";
            break;
        case 2:
            $couleurlien = "#FF0000";
            break;
        case 3:
            $couleurlien = "#FF0000";
            break;
        case 4:
            $couleurlien = "#FF0000";
            break;
        case 5:
        case 8:
            $couleurlien = "#00FF00";
            break;
        case 6:
            $couleurlien = "#FF0000";
            break;
    }

    $envoidevis=$DB_site2->query_first("SELECT COUNT(*) FROM envoi_devis WHERE facturid=".$facturid);

    $envois=$DB_site2->query("SELECT * FROM envoi_fournisseur WHERE facturid=".$facturid." AND destinataire!='0'");
    $fourn_cmd=$DB_site2->num_rows($envois);

    $stock=new stock($DB_site2);
    $si=$stock->search(null,null,"facture ".$facturid,2);
    $reserves=count($si);
    $annulation_action=($fourn_cmd>0 || $reserves>0) ? "return annulation_impossible(".intval($fourn_cmd).",".intval($reserves).")" : "";

    if (($rs_facture['moyenid'] == 1 and $rs_facture['valide'] == 0))
    { // non aboutie
        $couleurlien = "#FF9933";
        $etatfacture[$libelle] = "Non aboutie";
    }
    $etat_facture = "<b><font color=\"$couleurlien\">$etatfacture[$libelle]</font></b>";
    if ($rs_facture['valide'] == 5 || $rs_facture['valide'] == 8) // expédié
    {
        list ($annee, $mois, $jour) = explode("-", $rs_facture['dateexpedition']);
        //$etat_facture .= "<br>Date d'expédition : <b>$jour/$mois/$annee</b>";
        $dateexpedition = "Date expédition : ".$jour."/".$mois."/".$annee;
        $actions="<input type=\"button\" value=\"Créer un avoir\" onclick=\"window.open('facture.php?action=creer&createfromuser=".$facturid."');\">";
    }
    else
    {
        if (in_array("110", $modules) and $rs_facture['moyenid'] == 1)
        {
            /************************************TRAITEMENT FIA NET**************************************/
            $etat_facture .= "<br><a href=\"https://secure.fia-net.com/$fianetetat/BO/visucheck_detail.php?sid=$fianetsiteid&log=$fianetlogin&pwd=".urlencode("$fianetpasse")."&rid=$rs_facture[facturid]\" target=\"_blank\">Cliquez ici pour consulter l'analyse FIANET</a>";
            /************************************TRAITEMENT FIA NET**************************************/
        }
        elseif ($rs_facture['moyenid'] == 8 || $rs_facture['moyenid'] == 9)
        {
            $etat_facture .= "<br>
            <form name=\"rnpbo\" target=\"_blank\" style=\"display:none;\" method=\"post\" action=\"https://secure.fia-net.com/wallet/marchand/merchantcookie.php\">
            <input type=\"hidden\" name=\"login\" value=\"$receiveandpaylogin\">
            <input type=\"hidden\" name=\"PWD\" value=\"$receiveandpaypasse\">
            </form>
            <a onclick=\"document.rnpbo.submit();\">Cliquez ici pour consulter l'analyse R&P</a>";
        }
        $dateexpedition = "" ;
    }
    if ($rs_facture['valide'] == 1 /* or ($rs_facture['moyenid'] != 1 and $rs_facture['moyenid'] != 7 and $rs_facture['moyenid'] != 0 and $rs_facture['valide'] == 0)*/ )
    { // Validée ou non traité pas carte bleue et pas abandonnée
        $actions = "<br>
            &nbsp; <form action=\"facture.php\" method=\"post\" style=\"display:inline;\">
            <input type=\"hidden\" name=\"action\" value=\"expedier\">
            <input type=\"hidden\" name=\"facturid\" value=\"$facturid\">   
            <input type=\"submit\" value=\"Expédier\" style=\"color:green;\">
            </form>
            <form action=\"facture.php\" method=\"post\" style=\"display:inline;\" onsubmit=\"$annulation_action\">
            <input type=\"hidden\" name=\"action\" value=\"refuser\">
            <input type=\"hidden\" name=\"facturid\" value=\"$facturid\">   
            <input type=\"submit\" value=\"Annuler\" style=\"color:red;\">
            </form><br>
            <form method=\"post\" action=\"facture.php\" onsubmit=\"return mailconfirm();\">
            <input type=\"hidden\" name=\"action\" value=\"mailconfirmation\">
            <input type=\"hidden\" name=\"facturid\" value=\"$facturid\">
            <input type=\"submit\" value=\"Renvoyer la confirmation de commande\" style=\"\">
            </form>" ;
    }
    elseif ($rs_facture['valide'] == 0 && $rs_facture['moyenid'] == 1)
    {
        if($envoidevis[0]==0)
            $actions = "<br>
        &nbsp; <form action=\"facture.php\" method=\"post\" name=\"formu\" style=\"display:inline;\">
        <input type=\"hidden\" name=\"action\" value=\"cacher\">
        <input type=\"hidden\" name=\"facturid\" value=\"$facturid\">   
        <input type=\"submit\" value=\"Supprimer cette commande\" style=\"color:red;\">
        </form>" ;
    }
    elseif ($rs_facture['valide'] == 0 && $rs_facture['moyenid'] > 1)
    {
        $actions = "<br>
        &nbsp; <form action=\"facture.php\" method=\"post\" style=\"display:inline;\">
        <input type=\"hidden\" name=\"action\" value=\"valider\">
        <input type=\"hidden\" name=\"facturid\" value=\"$facturid\">   
        <input type=\"submit\" value=\"Valider le règlement\" style=\"color:blue;\">
        </form>";
        $actions.="&nbsp; <form action=\"facture.php\" method=\"post\" style=\"display:inline;\" onsubmit=\"$annulation_action\">
        <input type=\"hidden\" name=\"action\" value=\"refuser\">
        <input type=\"hidden\" name=\"facturid\" value=\"$facturid\">   
        <input type=\"submit\" value=\"Annuler cette commande\" style=\"color:red;\">
        </form>";
        $actions.="&nbsp; <form action=\"facture.php\" method=\"post\" style=\"display:inline;\">
        <input type=\"hidden\" name=\"action\" value=\"validersp\">
        <input type=\"hidden\" name=\"facturid\" value=\"$facturid\">   
        <br><input type=\"submit\" value=\"Valider sans paiement\" style=\"color:blue;\">
        </form><br>
        <form method=\"post\" action=\"facture.php\" onsubmit=\"return mailconfirm();\">
        <input type=\"hidden\" name=\"action\" value=\"mailconfirmation\">
        <input type=\"hidden\" name=\"facturid\" value=\"$facturid\">
        <input type=\"submit\" value=\"Renvoyer la confirmation de commande\" style=\"\">
        </form>" ;
    }
    elseif ($rs_facture['valide'] ==7)
    { //
        $actions = "<br>&nbsp; 
            <form action=\"facture.php\" method=\"post\" style=\"display:inline;\">
            <input type=\"hidden\" name=\"action\" value=\"valider\">
            <input type=\"hidden\" name=\"facturid\" value=\"$facturid\">   
            <input type=\"submit\" value=\"Valider le règlement\" style=\"color:blue;\">
            </form>
            <form action=\"facture.php\" method=\"post\" style=\"display:inline;\" onsubmit=\"$annulation_action\">
            <input type=\"hidden\" name=\"action\" value=\"refuser\">
            <input type=\"hidden\" name=\"facturid\" value=\"$facturid\">   
            <input type=\"submit\" value=\"Annuler\" style=\"color:red;\">
            </form><br>
            <form name=\"expedier\" action=\"facture.php\" method=\"get\" style=\"display:inline;\">
            <input type=\"hidden\" name=\"action\" value=\"expediersp\">
            <input type=\"hidden\" name=\"facturid\" value=\"$facturid\">
            <input type=\"hidden\" name=\"infolivr\" value=\"0\">
            <input type=\"hidden\" name=\"infofacture\" value=\"0\">
            <input type=\"button\" value=\"Expédier sans règlement\" style=\"color:green;\" onclick=\"document.getElementById('infolivr').style.display='block'\">
            <div id=\"infolivr\" style=\"z-index:100;display:none;position:absolute;width:320px;height:200px;background:white;border:solid 1px black;\">
            <div id=\"infolivr\" style=\"z-index:100;margin-top:10px;\">Voulez vous envoyer le mail d'information livraison ? <br><br><a href=\"#\" id=\"infolivr1\" onclick=\"selectinfolivr(1)\">OUI</a> &nbsp;&nbsp;&nbsp;&nbsp; <a href=\"#\" id=\"infolivr0\" onclick=\"selectinfolivr(0)\">NON</a></div>
            <div id=\"infofacture\" style=\"display:none;margin-top:10px;\">Voulez-vous envoyer l'email avec la facture ?<br><br>".($rs_facture['provenance']!="mp"?"<a href=\"#\" onclick=\"expedier_cmd(1)\">OUI</a>":"")." &nbsp;&nbsp;&nbsp;&nbsp; <a href=\"#\" onclick=\"expedier_cmd(0)\">NON</a></div>          
            </div>
            </form><br>
            <form method=\"post\" action=\"facture.php\"  onsubmit=\"return mailconfirm();\">
            <input type=\"hidden\" name=\"action\" value=\"mailconfirmation\">
            <input type=\"hidden\" name=\"facturid\" value=\"$facturid\">
            <input type=\"submit\" value=\"Renvoyer la confirmation de commande\" style=\"\">
            </form>" ;
    }
    elseif($rs_facture['valide']==8){
        $actions="<br><form name=\"paiement_form\" action=\"facture.php\" method=\"post\" style=\"display:inline;\">
          <input type=\"hidden\" name=\"action\" value=\"paiement\">
          <input type=\"hidden\" name=\"facturid\" value=\"$facturid\">
          <input type=\"hidden\" name=\"envoi_mail\" value=\"0\">
          <input type=\"button\" value=\"Valider le règlement\" style=\"color:green;\" onclick=\"paiement_mail()\">
          </form><br>
          <form method=\"post\" action=\"facture.php\" onsubmit=\"return mailconfirm();\">
          <input type=\"hidden\" name=\"action\" value=\"mailconfirmation\">
          <input type=\"hidden\" name=\"facturid\" value=\"$facturid\">
          <input type=\"submit\" value=\"Renvoyer la confirmation de commande\" style=\"\">
          </form>
          <br><input type=\"button\" value=\"Créer un avoir\" onclick=\"window.open('facture.php?action=creer&createfromuser=".$facturid."');\">";

    }elseif($rs_facture['valide']==0 && $envoidevis[0]==0){
        $actions.="&nbsp; <form action=\"facture.php\" method=\"post\" style=\"display:inline;\">
       <input type=\"hidden\" name=\"action\" value=\"cacher\">
       <input type=\"hidden\" name=\"facturid\" value=\"$facturid\">   
       <input type=\"submit\" value=\"Supprimer cette commande\" style=\"color:red;\">
       </form>";
    }
    $moyenpaiement = $DB_site2->query_first("SELECT * FROM moyenpaiement  WHERE moyenid='".$rs_facture['moyenid']."'");
    $num_suivi=$DB_site2->query_first("SELECT numero_suivi FROM facture WHERE facturid = '".$rs_facture['facturid']."'");
    if ($num_suivi['numero_suivi'] != "")
    {
        $suivi .= "<br>&nbsp; Numéro de suivi ".$num_suivi['numero_suivi'];
    }

    echo $srcform;

    ?>
    <script language="javascript" src="filebox.js"></script>
    <script language="javascript">
        var fb = new filebox('fb');
        fb.dataurl = "user_files.php?userid=<?php echo $rs_facture['userid']; ?>&facturid=<?php echo $rs_facture['facturid']; ?>";
        mail_pagepj('fb.showselect',['undefined',mail_addpjs]);
        document.title = "<?php echo $rs_facture['facturid']." ".$rs_facture['nom']." ".$rs_facture['prenom']; ?>";
    </script>
    <?php

    /************************************************* TSI ********************************************************/
    // Panneau d'affichage situé sous le formulaire de recherche et avant les informations coordonnées du client
    // Affichage des informations de la commande/facture (Statut, Numero, Devis...) et du tree view SAV
    // Formulaires: confirmation_cmd et newsav

    include(BACK_PATH.'/includes/facture_details.php');

    $txt="";
    $suiviclient="";
    $rq=$DB_site2->query("SELECT * FROM suivi_client WHERE userid=$rs_facture[userid] ORDER BY date DESC");
    while($s=$DB_site2->fetch_array($rq)){
        $txt.="<p>".$s['commercial']." ".date("d/m/Y H:i",$s['date'])." : ".nl2br($s['suivi'])."</p>";
    }
    if($txt!="") $suiviclient="<img src=\"../configurations/$host/images/templates/icone_info.gif\" width=\"20\" align=\"absmiddle\" onmouseover=\"document.getElementById('divsuivi').style.visibility='visible';\" onmouseout=\"document.getElementById('divsuivi').style.visibility='hidden';\"><div id=\"divsuivi\" style=\"visibility:hidden;position:absolute; background-color:white; border:solid thin black;\">$txt</div>";


    $chcheck=array(0=>"Peu importe l'heure et le jour",1=>"9h-12h",2=>"12h-14h",3=>"14h-18h",4=>"18h-20h",5=>"lundi",6=>"mardi",7=>"mercredi",8=>"jeudi",9=>"vendredi",10=>"samedi");
    $listch=explode(",",$user['contacthour']);
    foreach($listch as $ch)
        $contacthour.=$chcheck[$ch]." ";
    if($user['contacthour']=="") $contacthour="?";

    $pj=$DB_site2->query_first("SELECT COUNT(*) FROM user_files WHERE userid=".$rs_facture['userid']." AND facturid=".$rs_facture['facturid']." AND ext<>''");

    if($user['site_code']=="") $user['site_code']="base";

    include(BACK_PATH.'/includes/facture_coordonnees.php');

    include('includes/facture_reglements.php');

    $lignefactures=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid = '".$facturid."'");
    $total_facture = 0 ;
    while ($lignefacture=$DB_site2->fetch_array($lignefactures))
    {
        $total_facture += ($lignefacture['prix'] * $lignefacture['qte']) ;
    }

    // Bon de réduction
    $prixcadeau = 0;

    // remise
    if (in_array("3", $modules))
    {
        $prixremise = 0;
        $remise_facture = $DB_site2->query_first("SELECT r.remiseid, r.typeremise, r.valeur, r.pctremise, f.montantremise FROM facture f inner join remise r 
        on (f.remiseid = r.remiseid) WHERE f.facturid = '".$facturid."'");
        switch ($remise_facture[typeremise]) {
            case 0 : // remise sur le poids
                $poids = $DB_site2->query_first("SELECT SUM(poid) as poids_tot FROM lignefacture lf, article a WHERE lf.artid = a.artid and lf.factureid = '".$facturid."'");
                if ($poids['poids_tot'] > $remise_facture['valeur'])
                {
                    $prixremise = $remise_facture['montantremise'];
                    $libremise = "Remise de gros (Poids total > ".$remise_facture['valeur']." grammes) : ";
                }
                break;
            case 1 : // remise sur le prix
                if ($total_facture[0] > $remise_facture['valeur'])
                {
                    $prixremise = $remise_facture['montantremise'];
                    $libremise = "Remise de gros (Montant total > ".$remise_facture['valeur']." €) : ";
                }
                break;
            case 2 : // remise sur la quantité
                $qte = $DB_site2->query_first("SELECT SUM(qte) nb FROM lignefacture WHERE factureid = '".$rs_facture['facturid']."'");
                if ($qte['nb'] > $remise_facture['valeur'])
                {
                    $prixremise = $remise_facture['montantremise'];
                    $libremise = "Remise de gros (Nombre articles différents > ".$remise_facture['valeur'].") : ";
                }
                break;
        } // fin du switch
    }

    // remise sur tout le catalogue
    $fact = $DB_site2->query_first("SELECT pctremise FROM facture WHERE facturid = '".$rs_facture['facturid']."'");
    if ($fact[0] != 0)
    {
        $remise_tt_cat = "<tr><td colspan=\"5\" align=\"right\">Remise de ".$fact[0]." % déduite.</td></tr>";
        $total_facture = $total_facture * (1 - $fact[0] / 100);
    }

    // contre remboursement
    if ($rs_facture['moyenid'] == '3' and $rs_facture['prix_contre_remboursement'] != 0)
    {
        $total_facture = $total_facture + $rs_facture['prix_contre_remboursement'];
    }


    // Détail de la facture
    if ($rs_facture['valide'] != 5 && $rs_facture['valide'] != 6 && $rs_facture['valide'] != 8)
    {
        ////pour le bouton dissocier
        $lot = false;
        $lignefacturediss = $DB_site2->query_first("SELECT SUM(lignefacturedissid) FROM lignefacture WHERE factureid = '".$_GET['facturid']."'");
        if(intval($lignefacturediss[0]) == 0){
            $lignefactures = $DB_site2->query("SELECT * FROM lignefacture WHERE factureid = '".$_GET['facturid']."'");
            while($lignefacture = $DB_site2->fetch_array($lignefactures)){
                $sql = "SELECT al.id, al.caractvalids, al.qte, al.artid, als.prix, als.paht";
                $sql .= " FROM article_lot al";
                $sql .= " INNER JOIN article_lot_site als ON (als.alid = al.id)";
                $sql .= " WHERE lotid = ".$lignefacture['artid']."";
                $sql .= " AND als.site_code = 'base'";
                $artlot = $DB_site2->query($sql);
                if($DB_site2->num_rows($artlot) > 0) $lot = true;
            }
        }

        echo "<a name=\"detail_facture\"></a>
            <table id=\"table_article\" class=\"table_modules_boutiques\">
            <tr id=\"facture_detail\">";
        if(intval($lignefacturediss[0]) == 0 && $lot) {
            echo "<td><button onclick=\"document.forms.formDiss.submit();\">Dissocier</button>
                <form name=\"formDiss\" method=\"post\" action=\"facture.php?action=editer&facturid=".$_GET['facturid']."\">
                <input type=\"hidden\" name=\"dissocier\" value=\"1\">
                <input type=\"hidden\" name=\"facturid\" value=\"".$_GET['facturid']."\">
                </form></td>";
        }
        echo "<td colspan=\"5\" align=\"center\"><b>Détail des produits commandés</b> ".$modifcmd."</td>
            </tr>
            <tr>
            <td></td>
            <td align=\"center\" width=\"100\"><u>Référence</u></td>
            <td align=\"center\" width=\"300\"><u>Libelle</u></td>
            <td align=\"center\"><u>Garanties</u></td>
            <td align=\"center\"><u>Caractéristiques</u></td>
            <td align=\"center\"><u>Quantité</u></td>
            <td align=\"center\"><u>Prix</u></td>
            </tr>";
    }
    else
    {
        echo "<a name=\"detail_facture\"></a>
            <script language=\"javascript\">
            function rembintegral(){    
              var form=document.forms.formu_remboursement;
              for(i=0;i<form.getElementsByTagName('INPUT').length;i++){
                  var oi=form.getElementsByTagName('INPUT')[i];
                  if(oi.type=='checkbox' && oi.name.substr(0,10)=='rembourser'){
                    oi.checked=true;
                }
            }
            document.getElementsByName('integral')[0].value=1;
            document.forms.formu_remboursement.submit();
        }
        ";

        //JS 'Rentrée de stock' ajouté 08/04/2016
        echo "
        function showStock(){
          document.getElementById(\"formStock\").style.display = \"block\";
      }

      function hideStock(){
          document.getElementById(\"formStock\").style.display = \"none\";
          document.getElementById(\"trAlert\").style.display  = \"none\";
          document.getElementById(\"trQte\").style.display  = \"none\";
          document.getElementById(\"trDelai\").style.display  = \"none\";

      //Vide les champs lorsqu'on ferme le formulaire
          document.getElementsByName(\"artStock\")[0].value   = '';
          var x = document.getElementById(\"mySelect\");
          document.getElementById(\"myQte\").removeChild(x);
          document.getElementsByName(\"lieuStock\")[0].value  = '';
          document.getElementsByName(\"commStock\")[0].value  = '';
          document.getElementById('conforme1').checked    = false;
          document.getElementById('conforme2').checked    = false;
          document.getElementsByName(\"qteMini\")[0].value  = '';
          document.getElementsByName(\"delaiMini\")[0].value  = '';
      }

      function miniStock(){   
          var art = document.getElementsByName(\"artStock\")[0].value;
          var artsplit = art.split(\":\");
          var mini = artsplit[1];
          if(mini == 0){
            document.getElementById(\"trAlert\").style.display  = \"\";
            document.getElementById(\"trQte\").style.display  = \"\";
            document.getElementById(\"trDelai\").style.display  = \"\";
            }else{
                document.getElementById(\"trAlert\").style.display  = \"none\";
                document.getElementById(\"trQte\").style.display  = \"none\";
                document.getElementById(\"trDelai\").style.display  = \"none\";
            }

      //Création du select QTE
            var myQte = document.getElementById(\"myQte\");
            var nb = artsplit[2];

            var selectList = document.createElement(\"select\");
            selectList.setAttribute(\"id\", \"mySelect\");
            selectList.setAttribute(\"name\", \"qteStock\");
            myQte.innerHTML = '';
            myQte.appendChild(selectList);

            for (var i = 1; i <= nb; i++) {
                var option = document.createElement(\"option\");
                option.setAttribute(\"value\", i);
                option.text = i;
                selectList.appendChild(option);
            }

      //Vide les champs quand nouvel sélection d'un produit
            document.getElementsByName(\"lieuStock\")[0].value  = '';
            document.getElementsByName(\"commStock\")[0].value  = '';
            document.getElementById('conforme1').checked    = false;
            document.getElementById('conforme2').checked    = false;
            document.getElementsByName(\"qteMini\")[0].value  = '';
            document.getElementsByName(\"delaiMini\")[0].value  = '';
        }

        function submitStock(){ 
          var id = document.getElementsByName(\"facStock\")[0].value;
          var art = document.getElementsByName(\"artStock\")[0].value;
          var artsplit = art.split(\":\");
          var artid = artsplit[0];
          var qte = document.getElementsByName(\"qteStock\")[0].value;
          var lieu = document.getElementsByName(\"lieuStock\")[0].value;
          var comm = document.getElementsByName(\"commStock\")[0].value;
          if (document.getElementById('conforme1').checked) {
            conf = document.getElementById('conforme1').value;
            }else if(document.getElementById('conforme2').checked){
                conf = document.getElementById('conforme2').value;
                }else{
                    conf = '';
                }

                var mini = document.getElementsByName(\"qteMini\")[0].value;
                var delai = document.getElementsByName(\"delaiMini\")[0].value;

                var data = 'fact='+id+'&artid='+artid+'&qte='+qte+'&lieu='+lieu+'&comm='+comm+'&conf='+conf;

                if(artid == '' || qte == '' || lieu == '' || conf == '' || comm == ''){
                    alert(\"Veuillez remplir tous les champs\");
                    }else if(document.getElementById(\"trAlert\").style.display === '' && (mini == '' || delai == '')){
                        alert(\"Veuillez remplir tous les champs\");
                        }else{
                            if(document.getElementById(\"trAlert\").style.display === '')
                            data += '&mini='+mini+'&delai='+delai;
                            if(confirm(\"Valider cette rentrée de stock ?\")){
                              ajaxrequest('ajax/rentre_stock.php?'+data, resultStock);
                          }
                      }
                  }

                  function resultStock(httpRequest){
                      var split = httpRequest.responseText.split(\":\");
                      var reponse = split[0];
                      var id = split[1];
                      if(reponse === 'OK'){
                        alert('Rentrée de stock ajoutée');
                        window.location.href = \"facture.php?facturid=\"+id+\"&action=editer\";
                        }else{
                            alert(\"Erreur lors de l'ajout\");
                        }
                    }
                    </script>";

        echo "<form method=\"post\" name=\"formu_remboursement\" action=\"facture.php\" id=\"formu_remboursement\">
                    <input type=\"hidden\" name=\"action\" value=\"rembourser\" id=\"action_rembourser\">
                    <input type=\"hidden\" name=\"factureid\" value=\"".$facturid."\">
                    <input type=\"hidden\" name=\"integral\" value=\"\">
                    <table id=\"table_article\" class=\"table_modules_boutiques\" cellspacing=\"0\">
                    <tr id=\"facture_detail\">
                    <td colspan=\"6\" align=\"center\"><b>Détail des produits commandés</b><br><br></td>
                    </tr>
                    <tr>
                    <td></td>
                    <td align=\"center\"><u>Référence</u></td>
                    <td align=\"center\"><u>Libelle</u></td>
                    <td align=\"center\"><u>Garanties</u></td>
                    <td align=\"center\"><u>Caractéristiques</u></td>
                    <td align=\"center\"><u>Quantité</u></td>
                    <td align=\"center\"><u>Prix</u></td>
                    </tr>";
    }

    $totalttc=array();

    $ftemplate=new template("FR",$DB_site2);
    $ftemplate->changesite($rs_facture['site_code'],$user['langue']);

    $rq_lignefacturediss=$DB_site2->query("SELECT a.artid,a.artcode,lf.libelle,lf.prix,lf.qte,lf.lignefactureid FROM lignefacturediss lf INNER JOIN article a USING(artid) WHERE lf.factureid = '".$facturid."' order by lf.artcode");
    while($lignefacturediss=$DB_site2->fetch_array($rq_lignefacturediss)){
        $caractlist="";
        $infoappro="";
        $rq_lignefacturecaracteristique = $DB_site2->query("SELECT lignefacturecaracteristiqueid, libelle_caractval, libelle_caract, ref FROM lignefacturecaracteristique WHERE lignefactureid = '".$lignefacturediss['lignefactureid']."'");
        while($caract=$DB_site2->fetch_array($rq_lignefacturecaracteristique)){
            $ref="";
            if($caract['ref']!="") $ref=" (".$caract['ref'].")";
            $caractlist.=$caract['libelle_caract']." : ".$caract['libelle_caractval'].$ref."<br>";
        }
        $datefin_garantie="";
        $liste_garantie="";
        $garanties=DAOgarantie::getGarantiesByProduitCmd($DB_site2,$lignefacturediss['lignefactureid']);

        foreach($garanties as $garantie){
            $intitule_destinataire = '';
            if($rs_facture['raisonsociale'] != ''){
                switch($garantie['garantie_client_id']){
                    case 1:
                        $intitule_destinataire = '"PARTICULIER"';
                        break;
                    case 2:
                        $intitule_destinataire = '"PRO"';
                        break;
                }
            }
            if(isset($rs_facture['dateexpedition']) && $rs_facture['dateexpedition'] != "0000-00-00") {
                $dateEffetGarantie=date_create($rs_facture['dateexpedition']);
                date_add($dateEffetGarantie,date_interval_create_from_date_string($garantie["dureeAffiche"]." years"));
                date_add($dateEffetGarantie,date_interval_create_from_date_string("-1 day"));
                $dateEffetGarantie = date_format($dateEffetGarantie,"d/m/Y");
                $datefin_garantie = " - ".$template->translate("/article/garantie_type/valable_jusqu_au",'F')." ".$dateEffetGarantie;
            }
            $liste_garantie .= $intitule_destinataire." ".($garantie['cumul']==1?"+":"").$garantie['duree']." an(s) ".$garantie['libelle_type']." ".$garantie['fournisseur'].$datefin_garantie."<br>";
        }


        $fbart="<img src=\"images/icon/pdf.png\" width=\"30\" onclick=\"fb.show('article_files.php?artid=".$lignefacturediss['artid']."&parent=0')\">";
        echo "<tr style=\"background-color:#dddddd;\">
            <td>&nbsp;$fbart</td>
            <td>Article dissocié :<br>".$lignefacturediss['artcode']."</td>
            <td>".$lignefacturediss['libelle']."</td>
            <td>$liste_garantie</td>
            <td>$caractlist</td>
            <td align=\"center\">".$lignefacturediss['qte']."</td>
            <td align=\"right\">".$ftemplate->price_format($lignefacturediss['prix']*$lignefacturediss['qte'])."</td>
            <td></td>
            </tr>";

        $req=$DB_site2->query("SELECT a.artcode_eurocave, a.emballage_poids1,a.emballage_poids2,a.emballage_poids3,lf.*,date_appro FROM lignefacture lf LEFT JOIN article a USING(artid) WHERE lignefacturedissid=".$lignefacturediss['lignefactureid']);
        while($artdiss=$DB_site2->fetch_array($req)){
            $infoappro="";
            $artdiss['emballage_poids']=$artdiss['emballage_poids1']+$artdiss['emballage_poids2']+$artdiss['emballage_poids3'];
            $totalttc[$artdiss['tva']]+=$artdiss['prix']*$artdiss['qte'];
            $rq_caracteristiques = $DB_site2->query("SELECT lignefacturecaracteristiqueid, libelle_caractval, libelle_caract, ref FROM lignefacturecaracteristique WHERE lignefactureid = '".$artdiss['lignefactureid']."'");
            $select_caracteristique="";
            $carac_dispo = '';
            while ($rs_caracteristique = $DB_site2->fetch_array($rq_caracteristiques)){
                $ref="";
                if($rs_caracteristique['ref']!="") $ref="(".$rs_caracteristique['ref'].")";
                $select_caracteristique .= '<p>' . $rs_caracteristique[libelle_caract]." $rs_caracteristique[libelle_caractval] $ref</p>";
                $carac_dispo .= '<br>' . $rs_lignefacturecaracteristique['libelle_caractval'];
            }
            $datefin_garantie="";
            $liste_garantie="";
            $garanties=DAOgarantie::getGarantiesByProduitCmd($DB_site2,$artdiss['lignefactureid']);
            foreach($garanties as $garantie){

                $intitule_destinataire = '';
                if($rs_facture['raisonsociale'] != ''){
                    switch($garantie['garantie_client_id']){
                        case 1:
                            $intitule_destinataire = '"PARTICULIER"';
                            break;
                        case 2:
                            $intitule_destinataire = '"PRO"';
                            break;
                    }
                }
                if(isset($rs_facture['dateexpedition']) && $rs_facture['dateexpedition'] != "0000-00-00") {
                    $dateEffetGarantie=date_create($rs_facture['dateexpedition']);
                    date_add($dateEffetGarantie,date_interval_create_from_date_string($garantie["dureeAffiche"]." years"));
                    date_add($dateEffetGarantie,date_interval_create_from_date_string("-1 day"));
                    $dateEffetGarantie = date_format($dateEffetGarantie,"d/m/Y");
                    $datefin_garantie = " - ".$template->translate("/article/garantie_type/valable_jusqu_au",'F')." ".$dateEffetGarantie;
                }
                $liste_garantie.=$intitule_destinataire." ".($garantie['cumul']==1?"+":"").$garantie['duree']." an(s) ".$garantie['libelle_type']." ".$garantie['fournisseur'].$datefin_garantie."<br>";
            }



            $checkbox="";
            if($rs_facture['valide']==5 || $rs_facture['valide']==8){
                if($artdiss['remboursement']==0)
                    $checkbox="<input type=\"checkbox\" name=\"rembourser[$artdiss[lignefactureid]]\">";
                else
                    $checkbox="<font color=\"#E5840F\">Remb. le ".$artdiss['date_remboursement']."</font><br>";
            }

            $chequekdo="";
            if(substr($artdiss['artcode'],0,6)=="EC-KDO" && in_array($rs_facture['valide'],array(1,5,7,8)) && $artdiss['prix']>=0){
                $chequekdo="<br><a href=\"cheque_cadeau.php?facturid=$facturid&lignefactureid=$artdiss[lignefactureid]\" style=\"color:#7B0000;\">Chèque cadeau pdf</a>";
            }

            $fbart="<img src=\"images/icon/pdf.png\" width=\"30\" onclick=\"fb.show('article_files.php?artid=$artdiss[artid]&parent=0')\">";

            $checkbox_dispo = '';
            if($artdiss['artid'] > 0){

                $dateappro = '';
                if($artdiss['date_appro']!=0){
                    $dateappro=date("d/m/Y",$artdiss['date_appro']);
                    $infoappro="<p style=\"color:blue;\">Date de réappro : ".$dateappro."</p>";
                }

                $r=$DB_site2->query("SELECT * FROM calendrier WHERE type='fourncmd' AND objid=".$artdiss['lignefactureid']);
                if($DB_site2->num_rows($r)==0) $r=$DB_site2->query("SELECT * FROM calendrier WHERE type='stockcmd' AND objid=".$artdiss['lignefactureid']);
                $calendrier=$DB_site2->fetch_array($r);

                $artfourn=array();
                $reqfourn=$DB_site2->query("SELECT fournisseur FROM article_fournisseur WHERE artid='".$artdiss['artid']."'");
                while($fourn=$DB_site2->fetch_array($reqfourn)){
                    $artfourn[]=addslashes($fourn['fournisseur']);
                }

                if ($rs_facture[valide] != 5 && $rs_facture[valide] != 6 && $rs_facture[valide] != 8){
                    $js_checkbox = "todispo(this.checked,".$rs_facture['facturid'].",".$artdiss['lignefactureid'].",'".$artdiss['artcode_eurocave']."','".$artdiss['qte']."','".$artdiss['paht'].($artdiss['ecopartht'] > 0 ? "+".$artdiss['ecopartht'] : "")."','".$calendrier['enlevement']."','".$calendrier['livraison']."','".$dateappro."','".htmlentities($artdiss['libelle']) . $carac_dispo."',new Array('".implode("','",$artfourn)."'));";
                    $checkbox_dispo = "<input type=\"checkbox\" class=\"checkbox_dispo\" name=\"demande_dispo\" value=\"".$rs_lignefacture['artid']."\" onclick=\"".$js_checkbox."\">";
                    $js_checkall .= $js_checkbox;
                }
            }

            print "<tr class=\"ligne\">
<td style=\"background:url('images/tdtreeh.gif') repeat-x center;border-left:solid 2px black;text-align: right; width: 58px;\">
".$checkbox_dispo."
<input type=\"hidden\" id=\"facturid_$artdiss[lignefactureid]\" name=\"facturid\" value=\"$rs_facture[facturid]\">
<input type=\"hidden\" id=\"lignefactureid_$artdiss[lignefactureid]\"  name=\"lignefactureid\" value=\"$artdiss[lignefactureid]\">
<input type=\"hidden\" id=\"artid_$artdiss[lignefactureid]\" name=\"artid\" value=\"$artdiss[artid]\">
<input type=\"hidden\" id=\"catid_$artdiss[lignefactureid]\" name=\"catid\" value=\"$artdiss[catid]\">  
<input type=\"hidden\" id=\"lignefacturecaractid_$artdiss[lignefactureid]\" name=\"lignefacturecaractid\" value=\"$lignefacturecaractid\">
<input type=\"hidden\" id=\"prix_base_article_$artdiss[lignefactureid]\" value=\"$artdiss[prix]\">
</td>
<td nowrap>$fbart <a href=\"clientnavig.php?userid=$rs_facture[userid]&goto=art,$artdiss[artid],-.htm\" target=\"_blank\">$artdiss[artcode]</a><br><a href=\"ajout_produit.php?artid=$artdiss[artid]&prec=modif\" target=\"_blank\">".wordwrap($artdiss[artcode_eurocave],50,"<br>")."</a>".$chequekdo."</td>
<td>".$artdiss['libelle']."<br>&nbsp;".$artdiss['qte']*$artdiss['emballage_poids']." kg".$infoappro."</td>
<td>$liste_garantie</td>
<td>$select_caracteristique</td>
<td align=\"center\">".$artdiss['qte']."</td>
<td align=\"right\">".$ftemplate->price_format($artdiss['qte']*$artdiss['prix'])."</td>
<td>$checkbox</td>
</tr>";
        }
    }

    $rq_lignefacture=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid = '$facturid' AND lignefacturedissid=0 order by artcode");
    $num_ligne = 0;
    $calcul_prix_total = '';
    $tauxtva=0;

    while ($rs_lignefacture = $DB_site2->fetch_array($rq_lignefacture))
    {

        $sql = "SELECT marqueid";
        $sql .= " FROM article_marque";
        $sql .= " WHERE artid = '".$artid."'";
        $article_marque = $DB_site2->query_first($sql)[0];

        $tauxtva=$rs_lignefacture['tva']/100;
        $totalttc[$rs_lignefacture['tva']]+=$rs_lignefacture['prix']*$rs_lignefacture['qte'];
        $liste_caracteristiques = "";
        $infoappro="";
        $articletable="";
        $type_table = $DB_site2->query_first("SELECT bdd_table FROM typearticle INNER JOIN article USING(typearticleid) WHERE article.artid =".$rs_lignefacture['artid']);
        $articletable=$type_table[0];
        //Mode edition
        $select_caracteristique = "";
        if ($rs_facture['valide'] != 5 && $rs_facture['valide'] != 6 && false) //mode édition supprimé
        {
            //On récupère la liste des caractéristiques pour le produit
            $rq_caracteristiques = $DB_site2->query("SELECT DISTINCT(c.caractid), c.".$libelle." as libelle,cv.caractvalid, cv.ref FROM article_caractval ac INNER JOIN 
                caracteristiquevaleur cv ON (cv.caractvalid = ac.caractvalid) INNER JOIN caracteristique c ON (cv.caractid = c.caractid) WHERE ac.artid = 
                '".$rs_lignefacture['artid']."' AND c.marqueid = '".$article_marque."' ORDER BY c.caractid ASC");

            $lignefacturecaractid = "";
            while ($rs_caracteristique = $DB_site2->fetch_array($rq_caracteristiques))
            {
                //Pour chaque caracteristique, on liste les valeurs possibles
                //On récupère la valeur sélectionnée pour l'article
                $rq_lignefacturecaracteristique = $DB_site2->query("SELECT lignefacturecaracteristiqueid, libelle_caractval FROM lignefacturecaracteristique 
                    WHERE lignefactureid = '$rs_lignefacture[lignefactureid]' AND caractvalid = '".addslashes($rs_caracteristique[caractvalid])."'");

                if ($rs_lignefacturecaracteristique = $DB_site2->fetch_array($rq_lignefacturecaracteristique))
                {
                    $lignefacturecaractid .= $rs_lignefacturecaracteristique[lignefacturecaracteristiqueid] . "|";
                    $libelle_caractval_select = trim(stripslashes($rs_lignefacturecaracteristique[libelle_caractval]));
                }
                else
                {
                    $lignefacturecaractid .= $rs_lignefacturecaracteristique[lignefacturecaracteristiqueid] . "|";
                    $libelle_caractval_select = "";
                }

                $ref="";
                if($rs_caracteristique['ref']!="") $ref=" (".$rs_caracteristique['ref'].")";

                $select_caracteristique .= "<p>" . $rs_caracteristique[libelle]." : ".$libelle_caractval_select.$ref."</p>";

            }
            $liste_caracteristiques .= $select_caracteristique;
        }

        //mode visualisation
        $rq_lignefacturecaracteristique = $DB_site2->query("SELECT * FROM lignefacturecaracteristique WHERE lignefactureid = '$rs_lignefacture[lignefactureid]'");
        $carac_dispo = '';

        while ($rs_lignefacturecaracteristique = $DB_site2->fetch_array($rq_lignefacturecaracteristique))
        {
            $ref="";
            if($rs_lignefacturecaracteristique['ref']!="") $ref=" (".$rs_lignefacturecaracteristique['ref'].")";

            $liste_caracteristiques .= "<p>$rs_lignefacturecaracteristique[libelle_caract] : $rs_lignefacturecaracteristique[libelle_caractval] $ref</p>";
            $carac_dispo .= '<br>' . $rs_lignefacturecaracteristique['libelle_caractval'];
        }
        $datefin_garantie="";
        $liste_garantie="";
        $garanties=DAOgarantie::getGarantiesByProduitCmd($DB_site2,$rs_lignefacture['lignefactureid']);
        foreach($garanties as $garantie){
            if(isset($rs_facture['dateexpedition']) && $rs_facture['dateexpedition'] != "0000-00-00") {
                $dateEffetGarantie=date_create($rs_facture['dateexpedition']);
                date_add($dateEffetGarantie,date_interval_create_from_date_string($garantie["dureeAffiche"]." years"));
                date_add($dateEffetGarantie,date_interval_create_from_date_string("-1 day"));
                $dateEffetGarantie = date_format($dateEffetGarantie,"d/m/Y");
                $datefin_garantie = " - ".$template->translate("/article/garantie_type/valable_jusqu_au",'F')." ".$dateEffetGarantie;
            }
            $intitule_destinataire = '';
            if($rs_facture['raisonsociale'] != ''){
                switch($garantie['garantie_client_id']){
                    case 1:
                        $intitule_destinataire = '"PARTICULIER"';
                        break;
                    case 2:
                        $intitule_destinataire = '"PRO"';
                        break;
                }
            }
            if($garantie['type_garantie'] == 6){
                $liste_garantie.=$garantie['libelle_type']." ".$garantie['fournisseur'];
            }else{
                $liste_garantie.=$intitule_destinataire." ".($garantie['cumul']==1?"+":"").$garantie['duree']." an(s) ".$garantie['libelle_type']." ".$garantie['fournisseur'].$datefin_garantie."<br>";
            }
        }

        $rowalt = "td_2_" . getrowbg();
        if ($fact[0] != 0)
        {
            $rs_lignefacture[prix] = $rs_lignefacture[prix] * (1 - $fact[0] / 100);
        }
        echo '<tr id="article_' . $rs_lignefacture['lignefactureid'] . '" class="ligne">';
        $chequekdo="";
        if(substr($rs_lignefacture['artcode'],0,6)=="EC-KDO" && in_array($rs_facture['valide'],array(1,5,7,8)) && $rs_lignefacture['prix']>=0) $chequekdo="<br><a href=\"cheque_cadeau.php?facturid=$facturid&lignefactureid=$rs_lignefacture[lignefactureid]\" style=\"color:#7B0000;\">Chèque cadeau pdf</a>";

        $argument="";
        $argreq=$DB_site2->query("SELECT artcode,argument FROM article WHERE argument<>'' AND (artid IN (SELECT artid FROM article_lot WHERE lotid='".$rs_lignefacture['artid']."') OR artid='".$rs_lignefacture['artid']."')");
        if($DB_site2->num_rows($argreq)>0){
            while($arg=$DB_site2->fetch_array($argreq)) $argument.=" - ".$arg['artcode']." : ".$arg['argument'];
        }
        if($argument!="") $argument="<img src=\"images/lockit.jpg\" width=\"30\" title=\"".$argument."\">";

        $fbart="";
        if($rs_lignefacture['artid']>0){
            $nbfile=$DB_site2->query_first("SELECT COUNT(*) FROM article_files WHERE artid=".$rs_lignefacture['artid']);
            if($nbfile[0]>0) $fbart="<img src=\"images/icon/pdf.png\" width=\"30\" onclick=\"fb.show('article_files.php?artid=".$rs_lignefacture['artid']."&parent=0')\">";
        }

        //Affichage en mode édition ?
        if ($rs_facture['valide'] != 5 && $rs_facture['valide'] != 6 && $rs_facture['valide'] != 8)
        {
            //Mode édition
            if ($rs_lignefacture['artid'] != 0) {
                //On a besoin du prix de base de chaque article pour le calcul de la facture
                $prixbasearticle=$DB_site2->query_first("SELECT date_appro, ecopartht,prix,artcode_eurocave,emballage_poids1,emballage_poids2,emballage_poids3,emballage_hauteur1,emballage_hauteur2,emballage_hauteur3, emballage_largeur1,emballage_largeur2,emballage_largeur3,emballage_profondeur1,emballage_profondeur2,emballage_profondeur3 FROM article INNER JOIN ".$articletable." WHERE article.artid='".$rs_lignefacture['artid']."'");
                $prixbasearticle['emballage_poids'] = $prixbasearticle['emballage_poids1']+$prixbasearticle['emballage_poids2']+$prixbasearticle['emballage_poids3'];
                $prixbasearticle['emballage_largeur'] = $prixbasearticle['emballage_largeur1']+$prixbasearticle['emballage_largeur2']+$prixbasearticle['emballage_largeur3'];
                $prixbasearticle['emballage_profondeur'] =$prixbasearticle['emballage_profondeur1']+$prixbasearticle['emballage_profondeur2']+$prixbasearticle['emballage_profondeur3'];
                $prixbasearticle['emballage_hauteur'] = $prixbasearticle['emballage_hauteur1']+$prixbasearticle['emballage_hauteur2']+$prixbasearticle['emballage_hauteur3'];

                if($prixbasearticle["gaz"] != ''){
                    $strgaz = str_replace('/article/gaz/', '', $prixbasearticle["gaz"]);
                    $gaz = " - Gaz : " . $strgaz;
                }
                else {
                    $gaz = "";
                }
            }

            else
                $prixbasearticle=$DB_site2->query_first("SELECT prix FROM lignefacture WHERE lignefactureid = '".$rs_lignefacture['lignefactureid']."'");

            $checkbox_dispo = '';
            if($rs_lignefacture['artid'] > 0){

                $dateappro = '';
                if($prixbasearticle['date_appro']!=0){
                    $dateappro=date("d/m/Y",$prixbasearticle['date_appro']);
                    $infoappro="<p style=\"color:blue;\">Date de réappro : ".$dateappro."</p>";
                }

                $r=$DB_site2->query("SELECT * FROM calendrier WHERE type='fourncmd' AND objid=".$rs_lignefacture['lignefactureid']);
                if($DB_site2->num_rows($r)==0) $r=$DB_site2->query("SELECT * FROM calendrier WHERE type='stockcmd' AND objid=".$rs_lignefacture['lignefactureid']);
                $calendrier=$DB_site2->fetch_array($r);

                $artfourn=array();
                $reqfourn=$DB_site2->query("SELECT fournisseur FROM article_fournisseur WHERE artid='".$rs_lignefacture['artid']."'");
                while($fourn=$DB_site2->fetch_array($reqfourn)){
                    $artfourn[]=addslashes($fourn['fournisseur']);
                }

                $js_checkbox = "todispo(this.checked,".$rs_facture['facturid'].",".$rs_lignefacture['lignefactureid'].",'".$prixbasearticle['artcode_eurocave']."','".$rs_lignefacture['qte']."','".$rs_lignefacture['paht'].($prixbasearticle['ecopartht'] > 0 ? "+".$prixbasearticle['ecopartht'] : "")."','".$calendrier['enlevement']."','".$calendrier['livraison']."','".$dateappro."','".htmlentities($rs_lignefacture['libelle']) . $carac_dispo."',new Array('".implode("','",$artfourn)."'));";
                $checkbox_dispo = "<input type=\"checkbox\" class=\"checkbox_dispo\" name=\"demande_dispo\" value=\"".$rs_lignefacture['artid']."\" onclick=\"".$js_checkbox."\">";
                $js_checkall .= $js_checkbox;
            }

            // <input type=\"hidden\" name=\"action\" value=\"editer\">
            echo "<td class=\"$rowalt\" style=\"text-align: right; width: 60px;\">
        <input type=\"hidden\" name=\"etat\" value=\"update_ligne\">
        <input type=\"hidden\" id=\"facturid_$rs_lignefacture[lignefactureid]\" name=\"facturid\" value=\"$rs_facture[facturid]\">
        <input type=\"hidden\" id=\"lignefactureid_$rs_lignefacture[lignefactureid]\"  name=\"lignefactureid\" value=\"$rs_lignefacture[lignefactureid]\">
        <input type=\"hidden\" id=\"artid_$rs_lignefacture[lignefactureid]\" name=\"artid\" value=\"$rs_lignefacture[artid]\">
        <input type=\"hidden\" id=\"catid_$rs_lignefacture[lignefactureid]\" name=\"catid\" value=\"$rs_lignefacture[catid]\">  
        <input type=\"hidden\" id=\"lignefacturecaractid_$rs_lignefacture[lignefactureid]\" name=\"lignefacturecaractid\" value=\"$lignefacturecaractid\">
        <input type=\"hidden\" id=\"prix_base_article_$rs_lignefacture[lignefactureid]\" value=\"$prixbasearticle[prix]\">
        <!--<img src=\"images/supprimer.gif\" height=\"16\" width=\"16\" style=\"cursor:pointer\" onclick=\"supprimer_article($rs_lignefacture[lignefactureid],$rs_facture[facturid])\">
        <img src=\"images/ajouter.gif\" height=\"16\" width=\"16\" style=\"cursor:pointer\" onclick=\"submit_modifier_article($rs_lignefacture[lignefactureid])\">-->
        $fbart $argument 
        ".$checkbox_dispo."
        </td>
        <td class=\"".$rowalt."\"><a href=\"clientnavig.php?userid=".$rs_facture['userid']."&goto=art,".$rs_lignefacture['artid'].",-.htm\" target=\"_blank\">".$rs_lignefacture['artcode']."</a><br><a href=\"ajout_produit.php?artid=".$rs_lignefacture['artid']."&prec=modif\" target=\"_blank\">".wordwrap($prixbasearticle['artcode_eurocave'],50,"<br>")."</a> ".$chequekdo."</td>
        <td class=\"".$rowalt."\">".$rs_lignefacture['libelle']."<br>Dim. Brut : H".$prixbasearticle['emballage_hauteur']."*L".$prixbasearticle['emballage_largeur']."*P".$prixbasearticle['emballage_profondeur']. " / " .($rs_lignefacture['qte']*$prixbasearticle['emballage_poids'])." kg".($rs_lignefacture['qte']>1?" (".$rs_lignefacture['qte']." x ".$prixbasearticle['emballage_poids']." kg)":"").$infoappro .$gaz."</td>
        <td class=\"".$rowalt."\">".$liste_garantie."</td>
        <td class=\"".$rowalt."\"><div id=\"div_caracteristique_liste_article_".$rs_lignefacture['lignefactureid']."\">".$liste_caracteristiques."</div><div id=\"modif_en_cours_".$rs_lignefacture['lignefactureid']."\" style=\"display:none\">Modification en cours...</div></td>
        <td class=\"".$rowalt."\" align=\"center\"><input type=\"text\" id=\"qte_".$rs_lignefacture['lignefactureid']."\" name=\"qte\" size=\"2\" value=\"".$rs_lignefacture['qte']."\" onkeyup=\"modifier_article_quantite(event,".$rs_lignefacture['lignefactureid'].")\"></td>
        <td class=\"".$rowalt."\" align=\"right\" nowrap><div id=\"prix_article_".$rs_lignefacture['lignefactureid']."\" style=\"display:inline\">" . $ftemplate->price_format($rs_lignefacture['prix'] * $rs_lignefacture['qte']) . "</div></td>
        ";
        }
        else
        {
            //Mode visualisation
            $remb_id = $rs_lignefacture[lignefactureid] ;
            $prixbasearticle=$DB_site2->query_first("SELECT prix,artcode_eurocave,emballage_poids1+emballage_poids2+emballage_poids3 AS emballage_poids ,date_appro, emballage_hauteur1+emballage_hauteur2+emballage_hauteur3 AS emballage_hauteur, emballage_largeur1+emballage_largeur2+emballage_largeur3 AS emballage_largeur,emballage_profondeur1+emballage_profondeur2+emballage_profondeur3 AS emballage_profondeur FROM article WHERE artid='$rs_lignefacture[artid]'");
            if($prixbasearticle["gaz"] != ''){
                $strgaz = str_replace('/article/gaz/', '', $prixbasearticle["gaz"]);
                $gaz = " - Gaz : " . $strgaz;
            }
            else {
                $gaz = "";
            }

            $infoappro="";
            if($prixbasearticle['date_appro']>0) $infoappro="<p style=\"blue\">Date de réappro : ".date("d/m/Y",$prixbasearticle['date_appro'])."</p>";

            if ($rs_lignefacture[remboursement] == '1')
            {
                $tabdate = explode("-", $rs_lignefacture[date_remboursement]) ;
                echo "
            <td class=\"$rowalt\"><font color=\"#E5840F\">Remb. le<br>$tabdate[2]/$tabdate[1]/$tabdate[0] par $rs_lignefacture[modifby]</font></td>
            <td class=\"$rowalt\">$rs_lignefacture[artcode]<br>$prixbasearticle[artcode_eurocave]</td>
            <td class=\"$rowalt\">$rs_lignefacture[libelle]</td>
            <td class=\"$rowalt\">$liste_caracteristiques</td>
            <td class=\"$rowalt\" align=\"center\">$rs_lignefacture[qte]</td>
            <td class=\"$rowalt\" align=\"right\"><font color=\"#E5840F\">" . $ftemplate->price_format($rs_lignefacture[prix] * $rs_lignefacture[qte]) . "</font></td>";
            }
            else
            {
                echo "
            <td class=\"$rowalt\">$fbart $argument</td>
            <td class=\"$rowalt\">$rs_lignefacture[artcode]<br>$prixbasearticle[artcode_eurocave] $chequekdo</td>
            <td class=\"$rowalt\">$rs_lignefacture[libelle]<br>".($rs_lignefacture['qte']*$prixbasearticle['emballage_poids'])." kg".($rs_lignefacture['qte']>1?" ($rs_lignefacture[qte] x $prixbasearticle[emballage_poids] kg)":"").$infoappro." - Dimensions Brutes: H".$prixbasearticle['emballage_hauteur']."*L".$prixbasearticle['emballage_largeur']."*P".$prixbasearticle['emballage_profondeur'] .$gaz."</td>
            <td class=\"$rowalt\">$liste_caracteristiques $liste_garantie</td>
            <td class=\"$rowalt\" align=\"center\">$rs_lignefacture[qte]</td>
            <td class=\"$rowalt\" align=\"right\">" . $ftemplate->price_format($rs_lignefacture[prix] * $rs_lignefacture[qte]) . "</td>
            <td class=\"$rowalt\"><input type=\"checkbox\" name=\"rembourser[$remb_id]\"></td>";
            }
        }
        echo '</tr>';
        if (in_array("113", $modules)) // infos saisies
        {
            $champs = $DB_site2->query("select * from article_champ where artid = '$rs_lignefacture[artid]'");
            while ($champ = $DB_site2->fetch_array($champs))
            {
                switch ($champ[type])
                {
                    case "1" : // boîte texte
                        $saisie = $DB_site2->query_first("select valeur from lignefacturechamp where articlechampid = '$champ[articlechampid]'
                        and lignefactureid = '$rs_lignefacture[lignefactureid]'");
                        echo "<tr>
                    <td class=\"$rowalt\" colspan=\"5\">$champ[libelleFR] : $saisie[valeur]</td>
                    </tr>";
                        break;
                    case "2" : // Boutons radio
                        $saisie = $DB_site2->query_first("select valeur from lignefacturechamp where articlechampid = '$champ[articlechampid]'
                        and lignefactureid = '$rs_lignefacture[lignefactureid]'");
                        echo "<tr>
                    <td class=\"$rowalt\" colspan=\"5\">$champ[libelleFR] : $saisie[valeur]</td>
                    </tr>";
                        break;
                    case "3" : // Cases à cocher
                        $saisies = $DB_site2->query("select valeur from lignefacturechamp where articlechampid = '$champ[articlechampid]'
                        and lignefactureid = '$rs_lignefacture[lignefactureid]'");
                        $valeurs = "";
                        while ($saisie = $DB_site2->fetch_array($saisies))
                        {
                            $valeurs .= "$saisie[valeur] ";
                        }
                        echo "<tr>
                    <td class=\"$rowalt\" colspan=\"5\">$champ[libelleFR] : $valeurs</td>
                    </tr>";
                        break;
                    case "4" : // Champ texte
                        $saisie = $DB_site2->query_first("select valeur from lignefacturechamp where articlechampid = '$champ[articlechampid]'
                        and lignefactureid = '$rs_lignefacture[lignefactureid]'");
                        echo "<tr>
                    <td class=\"$rowalt\" colspan=\"5\">$champ[libelleFR] : $saisie[valeur]</td>
                    </tr>";
                        break;
                    case "5" : // Liste à sélection unique
                        $saisie = $DB_site2->query_first("select valeur from lignefacturechamp where articlechampid = '$champ[articlechampid]'
                        and lignefactureid = '$rs_lignefacture[lignefactureid]'");
                        echo "<tr>
                    <td class=\"$rowalt\" colspan=\"5\">$champ[libelleFR] : $saisie[valeur]</td>
                    </tr>";
                        break;
                    case "6" : // Liste à sélections multiples
                        $saisies = $DB_site2->query("select valeur from lignefacturechamp where articlechampid = '$champ[articlechampid]'
                        and lignefactureid = '$rs_lignefacture[lignefactureid]'");
                        $valeurs = "";
                        while ($saisie = $DB_site2->fetch_array($saisies))
                        {
                            $valeurs .= "$saisie[valeur] ";
                        }
                        echo "<tr>
                    <td class=\"$rowalt\" colspan=\"5\">$champ[libelleFR] : $valeurs</td>
                    </tr>";
                        break;
                    case "7" : // Insertion de fichier
                        $saisie = $DB_site2->query_first("select valeur, lignefacturechampid from lignefacturechamp where articlechampid = '$champ[articlechampid]'
                        and lignefactureid = '$rs_lignefacture[lignefactureid]'");
                        if ($saisie[lignefacturechampid] != "")
                        {
                            $url_image = "<a href=\"https://$host/configurations/$host/images/uploads/facture/" . $saisie[lignefacturechampid] . "." . $saisie[valeur] . "\" target=\"_blank\">Voir</a>";
                        }
                        else
                        {
                            $url_image = "--";
                        }
                        echo "<tr>
                    <td class=\"$rowalt\" colspan=\"5\">$champ[libelleFR] : $url_image</td>
                    </tr>";
                        break;
                }
            }
        }
        $num_ligne++;
    } //while ($rs_lignefacture=$DB_site2->fetch_array($rq_lignefacture))

    //En édition, on ajoute une ligne pour un nouvel article
    if ($rs_facture['valide'] != 5 && $rs_facture['valide'] != 6 && $rs_facture['valide'] != 8)
    {
        $rowalt = "td_2_" . getrowbg();
        //Liste des catégories avec au moins un article
        $rq_categorie = $DB_site2->query("SELECT DISTINCT(c.catid) as catid, c.$libelle as libelle FROM article a INNER JOIN categorie c ON (a.catid =c.catid) ORDER BY c.$libelle ASC");
        $zone_liste_categorie = "<select id=\"select_categories_nouveau_article\" name=\"select_categories_nouveau_article\" onchange=\"recherche_article()\">";
        $zone_liste_categorie .= '<option value="-1" selected>Sélectionnez la catégorie</option>';
        while ($rs_categorie = $DB_site2->fetch_array($rq_categorie))
        {
            if (strlen($rs_categorie['libelle']) > 50)
                $rs_categorie['libelle'] = substr($rs_categorie['libelle'],0,50)." ...";
            $zone_liste_categorie .= '<option value="' . $rs_categorie['catid'] . '">' . $rs_categorie['libelle'] . '</option>';
        }
        $zone_liste_categorie .= '</select>';

        $zone_liste_article = '<select id="select_articles_nouveau_article" name="select_articles_nouveau_article" disabled>';
        $zone_liste_article .= '<option>Articles</option></select>';
        $colspan = 6;

    }
    else
    {
        $rowalt = "td_2_" . getrowbg();
        echo "<tr>
            <td class=\"$rowalt\" colspan=\"6\" align=\"left\"> 
            <input type=\"submit\" value=\"Rembourser les articles selectionnés\"> 
            <input type=\"button\" value=\"Remboursement intégral\" onclick=\"rembintegral();\">
            <input type=\"button\" value=\"Remboursement/cadeau\" onclick=\"window.open('facture.php?action=creer&createfromuser=".$facturid."');\">
            <input type=\"button\" value=\"Rentrée de stock\" onclick=\"showStock();\">
            </td>
            </tr>";
        $colspan="6";
    }


    echo "$remise_tt_cat";
    // remise
    if (($prixremise > 0) and ($libremise != ""))
    {
        echo "<tr><td colspan=\"$colspan\" align=\"right\">$libremise <div id=\"montant_remise\" style=\"display:inline\">" . number_format($prixremise, 2, ',', ' ') . "</div> €</td></tr>";
    } // fin des remises

    if (in_array("13", $modules) && intval($rs_facture['cadeauid'])!=0)
    {
        $prixcadeau=$rs_facture['montantcadeau'];
        $libcadeau=$DB_site2->query_first("SELECT * FROM cadeau WHERE cadeauid='".$rs_facture['cadeauid']."' ");
        echo "<tr><td colspan=\"$colspan\" align=\"right\">Bon de réduction N° $libcadeau[code] : <div id=\"prixcadeau\" style=\"display:inline\">-$prixcadeau</div> €</td></tr>";
        $deduction_cadeau=true;
    }

    if($rs_facture['cadeauid']!="" && is_string($rs_facture['cadeauid'])){
        $code_caviste=$DB_site2->query_first("SELECT * FROM code_caviste WHERE code='".$rs_facture['cadeauid']."'");
        if($code_caviste['caviste']!=""){
            if($code_caviste[type]==2){
                $prixcadeau=$rs_facture['montantcadeau'];
                $deduction_cadeau=true;
                echo "<tr><td colspan=\"$colspan\" align=\"right\">Code caviste $code_caviste[code] (remise immédiate) : -$prixcadeau</td></tr>";
            }

            if($code_caviste[type]==1 && $rs_facture['montantcadeau']<$code_caviste['mini']){
                $prixcadeau=$rs_facture['montantcadeau'];
                $deduction_cadeau=true;
                echo "<tr><td colspan=\"$colspan\" align=\"right\">Code caviste $code_caviste[code] (remise immédiate valeur&lt;mini) : -$prixcadeau</td></tr>";
            }
        }
    }

    if($rs_facture['montantcadeau']>0 && $deduction_cadeau){
        foreach($totalttc as $tva=>$ttc){
            $totalttc[$tva]-=$rs_facture['montantcadeau']*($ttc/array_sum($totalttc));
        }
    }

    //Montant total de l'installation
    //Liste des installations commandées
    $prix_installation_ttc = 0;
    $sql = "SELECT *";
    $sql .= " FROM lignefactureinstallation";
    $sql .= " WHERE factureid = ".$rs_facture['facturid'];
    $req = $DB_site2->query($sql);
    if($DB_site2->num_rows($req) > 0){
        while($row = $DB_site2->fetch_array($req)){
            $prix_installation_ttc += $row['lignefactureinstallation_differenceprix'];
        }
    }
    $float_total_facture=$total_facture + $rs_facture['prixgestion'] + $prix_contre_remb + $rs_facture['prixport'] - $prixcadeau - $prixremise;
    $total_facture = $ftemplate->price_format($float_total_facture);

    $totalttc[$rs_facture['tvaport']]+=$rs_facture['prixport'];

    $poidstotal = $DB_site2->query_first("SELECT a.emballage_poids1,a.emballage_poids2,a.emballage_poids3,lf.qte FROM article a INNER JOIN lignefacture lf ON (a.artid = lf.artid) WHERE lf.factureid = '$facturid'");
    $poidstotal['poidstotal'] = ($poidstotal['emballage_poids1']+$poidstotal['emballage_poids2']+$poidstotal['emballage_poids3'])*$poidstotal['qte'];
    $poidstotal['poidstotal']=round($poidstotal['poidstotal']);

    //Insertion des demandes de dispo dans le suivi
    if(isset($_POST['demande_content']) && $_POST['demande_content'] != ''){
        $sql = "INSERT INTO suivi_commande SET";
        $sql .= " date          = ".time();
        $sql .= ", commercial = '".$_COOKIE['admin_username']."'";
        $sql .= ", body        = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['demande_content'])."'";
        $sql .= ", facturid     = ".$facturid;
        $DB_site2->query($sql);
    }

    $demande_dispo = ""
        . "<td colspan=\"4\" style=\"text-align: left; padding-left: 40px;\">"
        . "<form name=\"form_suivi_demande\" action=\"facture.php?action=editer&facturid=".$facturid."&lst_trietat=\" method=\"post\">"
        . "<input type=\"hidden\" name=\"demande_content\" value=\"\">"
        . "<input type=\"hidden\" name=\"demande_dest\" value=\"\">"
        . "</form>"
        . "<span style=\"margin-right: 20px;\"><img src=\"images/fleche_haut_gauche.png\"><input type=\"checkbox\" onclick=\"checkAllDispo(); ".$js_checkall."\"> Tout cocher</span>"
        . "<span>Pour la sélection :"
        . "<select id=\"produits_actions\" onchange=\"setAction(this.value, ".$facturid.");\">"
        . "<option value=\"0\">-- Choisir une action --</option>"
        . "<option value=\"demande_dispo\">Demander la disponibilité fournisseur</option>"
        . "</select>"
        . "</span>"
        . "<p id=\"dispodest\" style=\"margin: 0; padding: 0; text-align: center; display: none; color:red;\"></p>"
        . "</td>";

//Récupération des frais d'installation et de mise en service
    /*$sql = "SELECT SUM(lignefactureinstallation_differenceprix) as prixtotal, GROUP_CONCAT(lignefactureinstallation_typearticles SEPARATOR \"|\") as types_articles";
$sql .= " FROM lignefactureinstallation";
$sql .= " WHERE factureid = '".$facturid."'";
$aPrixInstallation = $DB_site2->query_first($sql);

$total_installation = 0;
if(!is_null($aPrixInstallation['prixtotal'])){

    $aTemp = explode('|', $aPrixInstallation['types_articles']);
    $total_installation = $aPrixInstallation['prixtotal'];

    $tabTypeArticle = array();
    foreach($aTemp as $la_ligne){

         if(is_numeric($la_ligne) && !in_array($la_ligne, $tabTypeArticle)) $tabTypeArticle[] = $la_ligne;
    }

    $nb = 0;
    foreach($tabTypeArticle as $type_article_id){

         $sql = "SELECT libelle";
         $sql .= " FROM typearticle";
         $sql .= " WHERE typearticleid = ".$type_article_id;
         $libelleType = $DB_site2->query_first($sql);

         if($nb > 0) $installation_desc .= ' + ';
         $installation_desc .= '<span style="color: #FF5A08;">'.$libelleType['libelle'].'</span>';
         $nb++;
    }

    echo "<tr id=\"ligne_frais_port\">
         <td colspan=\"".($colspan+1)."\" align=\"right\">
              <strong>Installation et mise en service pour ".$installation_desc."</strong> : <div id=\"fraisdeport\" style=\"display:inline\">".number_format($total_installation, 2, ',', ' ')."</div> €
         </td>
    </tr>";
}
*/
    if ($rs_facture['prixport'] != 0)
        echo "".
            "<tr id=\"ligne_frais_port\">"
            . $demande_dispo
            . "<td colspan=\"".($colspan-3)."\" align=\"right\">Frais de port ($poidstotal[poidstotal] kg) : $modelivraison $incoterms <div id=\"fraisdeport\" style=\"display:inline\">" . $ftemplate->price_format($rs_facture[prixport]) . "</div></td>".
            "</tr>";
    else
        echo ""
            . "<tr id=\"ligne_frais_port\">"
            . $demande_dispo
            . "<td colspan=\"".($colspan-3)."\" align=\"right\">Frais de port : $modelivraison $incoterms <div id=\"fraisdeport\" style=\"display:inline\"></div>franco</td>"
            . "</tr>";

    if ($rs_facture['prix_contre_remboursement'] != 0)
        echo "<tr><td colspan=\"$colspan\" align=\"right\">Supplément contre remboursement : <div id=\"contreremboursement\" style=\"display:inline\">" . $ftemplate->price_format($rs_facture[prix_contre_remboursement]) . "</div></td></tr>";

    echo "<tr><td colspan=\"$colspan\" style=\"text-align:right;color:red;font-weight:bold;\">Total TTC : <div id=\"prix_total_facture\" style=\"display:inline\">$total_facture</div></td></tr>";

    foreach($totalttc as $tva=>$ttc)
        echo "<tr><td colspan=\"$colspan\" align=\"right\">TVA $tva% : ".$ftemplate->price_format($ttc-$ttc/(1+$tva/100),2)."</td></tr>";

    if ($rs_facture['valide'] != 5 && $rs_facture['valide'] != 6)
    {
        echo "</table>";
    }
    else
    {
        echo "</table></form>";
    }

    //'Rentrée de stock' ajouté 08/04/2016
    //Lieu de stockage
    $optlieu.="<option></option>";
    $r = $DB_site2->query("SELECT f.societe,f.ville, f.id FROM fournisseur f INNER JOIN stock_mini ON(stock_mini.lieu=f.id) GROUP By lieu ORDER BY f.societe");
    while($lieu = $DB_site2->fetch_array($r)){
        $optlieu.="<option value=\"".$lieu['id']."\">".$lieu['societe']." ".$lieu['ville']."</option>";
    }

    //Liste des articles de la commande
    $optart.="<option value=''>- Sélectionner -</option>";
    $rs_lignefacture['lignefactureid'];
    $r = $DB_site2->query("SELECT a.artid, m.libelle, a.artcode, a.artcode_eurocave, SUM(lf.qte) as qte
  FROM article a
  INNER JOIN article_marque am USING ( artid )
  INNER JOIN lignefacture lf USING ( artid )
  INNER JOIN marque m USING ( marqueid )
  WHERE factureid=$facturid
  GROUP BY a.artid
  ORDER BY artcode");

    while($art = $DB_site2->fetch_array($r)){

        //Stock_min
        $num = $DB_site2->query_first("SELECT COUNT(artid) FROM stock_mini WHERE artid=".$art['artid']." OR artids LIKE '%".$art['artid']."%'");
        if($num[0] == 0){
            $optmini = 0;
        }else $optmini = 1;

        //artid ou artids
        $sm = $DB_site2->query_first("SELECT artid FROM stock_mini WHERE artids=".$art['artid']);
        if($sm[0] != 0){
            $artQte = $sm['artid'];
        }else{
            $artQte = $art['artid'];
        }

        $sql = "SELECT COUNT(*) AS qte";
        $sql.= " FROM stock_item s";
        $sql.= " INNER JOIN article a USING(artid)";
        $sql.= " WHERE origin ='retour:".$facturid."'";
        $sql.= " AND artid=".$artQte;
        $sql.= " GROUP BY artid";
        $req = $DB_site2->query_first($sql);

        $qte = $art['qte'];
        $qte -= $req['qte'];

        $optart.="<option value=\"".$art['artid'].":".$optmini.":".$qte."\">".$art['libelle']." - ".$art['artcode']." ".$art['artcode_eurocave']."</option>";
    }

    echo"<div id=\"formStock\" style=\"display:none;\">
<form method=\"post\" id=\"form_stock\" action=\"\">
<input type=\"hidden\" name=\"facStock\" value=\"$facturid\">
<table id=\"tableStock\">
<tr>
<td colspan=\"2\" align=\"center\"><h3>Rentrée de stock</h3><hr id=\"hrStock\"></td>
</tr>
<tr>
<td><label for=\"article\">Produit : </label></td>
<td><select name=\"artStock\" id=\"article\" onchange=\"miniStock();\">$optart</select></td>
</tr>
<tr>
<td><label for=\"quantite\">Quantité : </label></td>
<td id=\"myQte\"></td>
</tr>
<tr>
<td><label for=\"lieu\">Lieu de stockage : </label></td>
<td><select name=\"lieuStock\" id=\"lieu\">$optlieu</select></td>
</tr>
<tr>
<td><label for=\"comment\">Commentaire : </label></td>
<td><textarea name=\"commStock\" id=\"comment\"></textarea></td>
</tr>
<tr>
<td><label for=\"conforme\">Produit conforme : </label></td>
<td id=\"conf\"><input type=\"radio\" id=\"conforme1\" name=\"confStock\" value=\"1\"/> Oui
<input type=\"radio\" id=\"conforme2\" name=\"confStock\" value=\"2\"/> Non</td>
</tr>
<tr id=\"trAlert\" style=\"display:none\">
<td colspan=2 style=\"color:red; text-align:center; font-style: italic; border-top: 1px solid black; padding: 5px 0px;\">
L'article n'existe pas en stock,<br>Veuillez remplir ses champs en plus :</td>
</tr>
<tr id=\"trQte\" style=\"display:none\">
<td><label for=\"quantite\">Quantité mini : </label></td>
<td><input type=\"texte\" name=\"qteMini\" size=\"5\" id=\"quantite\" class=\"number\"></td>
</tr>
<tr id=\"trDelai\" style=\"display:none\">
<td><label for=\"quantite\">Délai réappro : </label></td>
<td><input type=\"text\" name=\"delaiMini\" size=\"5\" id=\"quantite\" class=\"number\"></td>
</tr>

<tr>
<td colspan=2 style=\"text-align:center;\"><input type=\"button\" value=\"Envoyer\" onclick=\"submitStock();\">
<input type=\"button\" value=\"Fermer\" onclick=\"hideStock();\"></td>
</tr>
<tr>
<td colspan=2 style=\"text-align:center;font-style:italic;padding:5px 0px;font-size:10px;\"><strong>PS:</strong> Si aucune quantité n'est proposé, cela signifie que <br>le produit est revenu ENTIEREMENT en stock (toutes les quantités)</td>
</tr>
</table>
</form>
</div>";

    //JS
    print"<script type='text/javascript'>
var textbox = document.getElementsByClassName(\"number\");

    // Call when key is pressed
    // in keydown, we get the keyCode
    // in keyup, we get the input.value (including the charactor we've just typed)

for(let i of textbox){
    i.addEventListener(\"keydown\", function _OnNumericInputKeyDown(e) {
      var key = e.which || e.keyCode;
      if (!e.shiftKey && !e.altKey && !e.ctrlKey && key >= 65 && key <= 90 || key == 32) {
        e.preventDefault();
        return false;
    }
    if (!e.shiftKey && !e.altKeyhf && !e.ctrlKey && key >= 48 && key <= 57 || key >= 96 && key <= 105 || (e.keyCode == 65 && e.ctrlKey === true) || (key == 67 && e.ctrlKey === true) || (key == 88 && e.ctrlKey === true) || (key >= 35 && key <= 39) || key == 8 || key == 9 || key == 13 || key == 46 || key == 45) {
        return true;
    }
    var v = this.value; // v can be null, in case textbox is number and does not valid
    if (key == 109 || key == 189) {
        if (v[0] === '-') { // if already has -, ignore the new one
          //console.log('return, already has - in the beginning');
          return false;
      }
  }
  if (!e.shiftKey && !e.altKey && !e.ctrlKey && key == 190 || key == 188 || key == 110) {
    if (/[\.,]/.test(v)) { // already having comma, period, dot
          //console.log('return, already has , . somewhere');
      return false;
  }
}
});

i.addEventListener(\"keyup\", function _OnNumericInputKeyUp(e) {
  var v = this.value;
  if (+v) { // convert to number success, let it be
        //            \"1000\"  \"10.9\"  \"1,000.9\"   \"011\"   \"10c\"   \"$10\"
        //+str, str*1, str-0      1000    10.9      NaN          11       NaN       NaN
      } else if (v) {
        // refine the value
        v = (v[0] === '-' ? '-' : '') + v.replace(/[^0-9\.]/g, ''); // this replace also remove the -, we add it again if needed
        v = v.replace(/\.(?=(.*)\.)+/g, '');              // remove all dot that have other dot following. After this processing, only the last dot is kept.
        this.value = v;                       // update value only if we changed it
    }
    });
}
  //CODE SOURCE : https://github.com/lockevn/html-numeric-input/blob/master/index.html
</script>";

// affichage tableaux cmde fournisseur / garantie / transporteur et installateur (avec les "onglets"), situé juste sous detail des produits commandés
    $showsav=array(1,5,7,8);
    if(in_array($rs_facture['valide'],$showsav)) include("adv/adv.php");

// affichage du stock, situé sous les tableaux cmde fourniseeur / garantie / transporteur et installateur et juste avant le suivi commercial
    $stockArtCode = [];
    if(in_array($rs_facture['valide'],array(0,1,7,5,8))){
        print "<br><div class=\"tab_special\" id=\"detail_stock\">
    <h2>Stock</h2>
    <div class=\"clear\"></div>
    <table border=\"1\" cellspacing=\"0\" style=\"background:white;\">
    <tr><td colspan=\"2\" align=\"center\">Ref.</td><td>Qté en stock</td><td>Qté non réservé</td><td>Qté réservé</td><td>Qté non<br>validé</td><td>Qté dispo</td><td>Lieux de stockage</td><td>Livraisons prévues - cmd</td><td>Qte dispo<br>prévi.</td></tr>";

        $stock=new stock($DB_site2);
        $f=new facture($DB_site2,$facturid);
        $lieux=$stock->lieux();
        $stock1=false;
        foreach($f->lignes as $ligne){
            $artid=$ligne->artid;
            $sumnoncmd=0;
            $stockitem_tt=$stock->search($artid,null,null,0);
            $articlelot=$DB_site2->query("SELECT * FROM article_lot WHERE lotid=$artid");
            ///// dissocie les articles en lot pour les afficher dans le stock
            if($DB_site2->num_rows($articlelot)>0){
                while($lart=$DB_site2->fetch_array($articlelot)){
                    $simi=array();
                    $sql_simi=$DB_site2->query("SELECT artid,artids FROM stock_mini WHERE artids LIKE '%".$lart['artid']."%' OR artid=".$lart['artid']);
                    foreach($sql_simi as $artids){
                        $simi[]=$artids['artids'];
                        $simi[]=$artids['artid'];
                    }
                    $simi=array_filter(array_unique($simi));
                    $simi=implode("','",$simi);
                    (empty($simi)||!is_array($simi)?$art_in="('".$lart['artid']."')":$art_in="('".implode("','",$simi)."')");
                    $qte_tt_prev=0;
                    $stocklieu=array();
                    $livrlieu=array();
                    $stockcmdids=array();
                    $txtdetail=$txtlivr=$txtnoncmd="";
                    $larticle=$DB_site2->query_first("SELECT artcode,artcode_eurocave FROM article WHERE artid=".$lart['artid']);
                    $stockitems=$stock->search($lart['artid']);
                    foreach($stockitems as $stockitem)
                        $stocklieu[$stockitem->lieu]++;

                    $livritems=$stock->search($lart['artid'],null,null,0);
                    foreach($livritems as $livritem){
                        $livrlieu[$livritem->lieu][$livritem->date_livraison]++;
                        $stockcmdids[$livritem->date_livraison][$livritem->stock_cmd_id]++;
                    }
                    $si=new stock_item($DB_site2);
                    $si->loadartid($lart['artid']);
                    $noncmd=$si->cmd_non_reserve();

                    if($noncmd!=false){
                        foreach($noncmd as $noncmdid){
                            $noncmdqte=$DB_site2->query_first("SELECT SUM(qte) FROM lignefacture WHERE factureid=".$noncmdid." AND (artid=".$si->artid.($si->artids!=""?" OR artid IN (".$si->artids.")":"").")");
                            $txtnoncmd.="<a href=\"facture.php?action=editer&facturid=".$noncmdid."\" target=\"_blank\" style=\"".($noncmdid==$facturid?"color:red;":"")."\">".$noncmdid."</a> : ".$noncmdqte[0]."<br>";
                            $sumnoncmd+=$noncmdqte[0];
                        }
                    }
                    foreach($stocklieu as $lieu=>$qte){
                        $txtdetail.="<p style=\"".($qte>=$lart['qte']?"":"color:red;")."\">".$lieux[$lieu]." : ".$qte."</p>";
                    }

                    $nb=0;
                    foreach($livrlieu as $lieu=>$livraisons){
                        $txtlivr.="<p>".$lieux[$lieu];
                        foreach($livraisons as $date=>$qte){
                            foreach($stockcmdids[$date] as $stockcmdid=>$qtes){
                                ///pour les commandes sur affaire, mettre le n° de facture
                                if($stockcmdid==0){
                                    $txtlivr="";
                                    $faclist=array();
                                    $dest_facs=$DB_site2->query("SELECT dest, origin, count(*) as qte FROM stock_item si WHERE artid IN ".$art_in." AND stock_cmd_id=0 AND state != 3 AND state != 2 AND dest != '' GROUP BY dest");
                                    while($dest_fac=$DB_site2->fetch_array($dest_facs)){
                                        if(substr($dest_fac['dest'],0,7)=="facture") $facid=substr($dest_fac['dest'],8);
                                        $envoi_lignes=$DB_site2->query("SELECT outdate, efl.qte, enlevement,livraison,ef.type FROM lignefacture lf INNER JOIN envoi_fournisseur_ligne efl ON efl.ligne_facture_id = lf.lignefactureid INNER JOIN envoi_fournisseur ef ON ef.id = efl.efid WHERE lf.factureid = ".$facid." AND lf.artid IN ".$art_in." AND (ef.type='fourn' OR ef.type='stock') GROUP BY efl.qte ORDER BY enlevement DESC");
                                        while($envoi_ligne=$DB_site2->fetch_array($envoi_lignes)){
                                            //si la commande a été enlevée, elle ne doit plus apparaitre en réappro. Sinon, on l'affiche bien :
                                            if($envoi_ligne['type']=='fourn'){
                                                if($envoi_ligne['livraison']==0){
                                                    $txtlivr.=($envoi_ligne['outdate']>0?date("d/m/Y",$envoi_ligne['outdate']).' : ':"").$envoi_ligne['qte']." - <a href=\"facture.php?action=editer&facturid=".$facid."\" target=\"_blank\">n°".$facid."</a><br>";
                                                }
                                            }
                                            else{
                                                if($envoi_ligne['enlevement']==0){
                                                    $txtlivr.=($envoi_ligne['indate']>0?date("d/m/Y",$envoi_ligne['indate']).' : ':"").$envoi_ligne['qte']." - <a href=\"facture.php?action=editer&facturid=".$facid."\" target=\"_blank\">n°".$facid."</a><br>";
                                                }
                                            }
                                        }
                                    }
                                }
                                else{
                                    $txtlivr.="<br>".date("d/m/Y",$date)." : ".$qtes." - <a href=\"stock.php?action=cmd&stock_cmd_id=".$stockcmdid."\" target=\"_blank\">n°".$stockcmdid."</a>";
                                    $qte_tt_prev+=$qtes;
                                }
                            }
                        }
                        $txtlivr.="</p>";
                    }
/// pour les commandes non payées
                    $cmdnnpay="SELECT factureid,qte FROM lignefacture INNER JOIN facture ON(facturid=factureid) WHERE (select count(*) from envoi_fournisseur_ligne where ligne_facture_id=lignefactureid AND type IN ('fourn','stock'))=0 and supprimee='0' and artid IN ".$art_in." AND valide = 0 AND moyenid in (2,4,5,14,17,18,99)";
                    $reqcmd=$DB_site2->query($cmdnnpay);
                    $qte_nn_paye=0;
                    $list_nn_paye="";
                    while($facture=$DB_site2->fetch_array($reqcmd)){
                        $non_paye=$DB_site2->query_first("SELECT COUNT(*) FROM stock_item WHERE artid=".$lart['artid']." AND dest='facture ".$facture['factureid']."' AND state=2");
                        if($non_paye[0]<$facture['qte']){
                            $qte_nn_paye+=$facture['qte'];
                            $list_nn_paye.="<br><a href=\"facture.php?action=editer&facturid=".$facture[0]."\" target=\"_blank\" style=\"".($facture[0]==$facturid?"color:red;":"")."\">".$facture[0]."</a>";
                        }
                    }
/// pour les commandes non réservées:
                    $cmdqte="SELECT factureid,qte FROM lignefacture INNER JOIN facture ON(facturid=factureid) WHERE (select count(*) from envoi_fournisseur_ligne where ligne_facture_id=lignefactureid AND type IN ('fourn','stock'))=0 and valide in ('1','7') and supprimee='0' and artid IN ".$art_in;
                    $reqcmd=$DB_site2->query($cmdqte);
                    $qte_nn_reserve=0;
                    $list_nn_reserve="";
                    while($facture=$DB_site2->fetch_array($reqcmd)){
                        $reserve=$DB_site2->query_first("SELECT COUNT(*) FROM stock_item WHERE artid=".$lart['artid']." AND dest='facture ".$facture['factureid']."' AND state=2");
                        if($reserve[0]<$facture['qte']){
                            $qte_nn_reserve+=$facture['qte'];
                            $list_nn_reserve.="<br><a href=\"facture.php?action=editer&facturid=".$facture[0]."\" target=\"_blank\" style=\"".($facture[0]==$facturid?"color:red;":"")."\">".$facture[0]."</a>";
                        }
                    }
/// pour les commandes réservées:
                    $list_reserve="";
                    $qte_reserve=0;
                    $cmdres=$DB_site2->query("SELECT si.*,mini,alert,artids,delai FROM stock_item si LEFT JOIN stock_mini USING(artid) WHERE state IN ('2') AND artid IN ".$art_in." GROUP BY si.id");
                    if($DB_site2->num_rows($cmdres)>0){
                        $qte_reserve=$DB_site2->num_rows($cmdres);
                        $req_dest=$DB_site2->query("SELECT dest FROM stock_item si WHERE artid IN ".$art_in." AND state=2 GROUP BY dest");
                        $cmdlist=array();
                        while($cmd=$DB_site2->fetch_array($req_dest)){
                            if(substr($cmd[0],0,7)=="facture") $cmdlist[]=substr($cmd[0],8);
                        }
                        foreach($cmdlist as $cmdid){
                            $list_reserve.="<br><a href=\"facture.php?action=editer&facturid=".$cmdid."\" target=\"_blank\" style=\"".($cmdid==$facturid?"color:red;":"")."\">".$cmdid."</a>";
                        }
                    }
                    $stock1=true;
/// qte physique (avec les articles similaires)
                    $r1=$stock->stockbyqte(array(1,2),null);
                    $r=array_merge($r1,$stock->stock0(array(1,2),null,true));
                    ksort($r);
                    $nb_stock=0;
                    foreach($r as $item){
                        foreach($item->similaire as $artid=>$similaire){
                            if($lart['artid']==$artid){
                                $nb_stock+=$item->qte;
                            }
                        }
                        if($item->artid==$lart['artid']){
                            $nb_stock+=$item->qte;
                        }
                    }

                    $etat_art = $DB_site2->query_first("SELECT active FROM article WHERE artid ='".$ligne->artid."'");
                    if($etat_art['active'] == '1'){
                        $etat_article = 'Actif';
                    }
                    elseif($etat_art['active'] == '0'){
                        $etat_article = 'Inactif';
                    }
                    elseif($etat_art['active'] == '2'){
                        $etat_article = 'Supprimé';
                    }
                    elseif($etat_art['active'] == '3'){
                        $etat_article = 'Archivé temporairement';
                    }
                    else{
                        $etat_article = 'Archivé définitivement';
                    }

                    if(preg_match('/ginko|acilyon|frio|ajd/i', $txtdetail)) $stockArtCode[] = $larticle['artcode'];
                    print "<tr><td><img src=\"../configurations/$host/images/produits/".$lart['artid'].".jpg\" width=\"30\"></td><td style=\"width:150px;\">".$art['qte']." ".$larticle['artcode']."<br>".$larticle['artcode_eurocave']."<br><i>".$etat_article."</i></td><td style=\"text-align:center;\"><b>".$nb_stock."</b></td><td style=\"text-align:center;\">".$qte_nn_reserve.$list_nn_reserve."</td><td style=\"text-align:center;\">".$qte_reserve.$list_reserve."</td><td style=\"text-align:center;\">".$qte_nn_paye.$list_nn_paye."</td><td style=\"text-align:center;background-color:#c4c3c3;\"><b>".($nb_stock-$qte_nn_reserve-$qte_reserve-$qte_nn_paye)."</b></td><td>".$txtdetail."</td><td>".$txtlivr."</td><td style=\"text-align:center;background-color:#c4c3c3;\"><b>".($nb_stock-$qte_nn_reserve-$qte_reserve-$qte_nn_paye+$qte_tt_prev)."</b></td></tr>";
                }
            }
            else{
                $simi=array();
                $sql_simi=$DB_site2->query("SELECT artid,artids FROM stock_mini WHERE artids LIKE '%".$ligne->artid."%' OR artid=".$ligne->artid);
                foreach($sql_simi as $artids){
                    $simi[]=$artids['artids'];
                    $simi[]=$artids['artid'];
                }
                $simi=array_filter(array_unique($simi));
                (empty($simi)||!is_array($simi)?$art_in="('".$ligne->artid."')":$art_in="('".implode("','",$simi)."')");
                $stocklieu=array();
                $livrlieu=array();
                $stockcmdids=array();
                $txtdetail=$txtlivr=$txtnoncmd="";
                $qte_tt_prev=0;
                $stockitems=$stock->search($artid);
                foreach($stockitems as $stockitem)
                    $stocklieu[$stockitem->lieu]++;

                $livritems=$stock->search($artid,null,null,0);
                foreach($livritems as $livritem){
                    $livrlieu[$livritem->lieu][$livritem->date_livraison]++;
                    $stockcmdids[$livritem->date_livraison][$livritem->stock_cmd_id]++;
                }
                $si=new stock_item($DB_site2);
                $si->loadartid($ligne->artid);
                $noncmd=$si->cmd_non_reserve();

                if($noncmd!=false){
                    foreach($noncmd as $noncmdid){
                        $noncmdqte=$DB_site2->query_first("SELECT SUM(qte) FROM lignefacture WHERE factureid=".$noncmdid." AND (artid=".$si->artid.($si->artids!=""?" OR artid IN (".$si->artids.")":"").")");
                        $txtnoncmd.="<a href=\"facture.php?action=editer&facturid=".$noncmdid."\" target=\"_blank\" style=\"".($noncmdid==$facturid?"color:red;":"")."\">".$noncmdid."</a> : ".$noncmdqte[0]."<br>";
                        $sumnoncmd+=$noncmdqte[0];
                    }
                }
                foreach($stocklieu as $lieu=>$qte){
                    $txtdetail.="<p style=\"".($qte>=$ligne->qte?"":"color:red;")."\">".$lieux[$lieu]." : ".$qte."</p>";
                }

                $nb=0;
                foreach($livrlieu as $lieu=>$livraisons){
                    $txtlivr.="<p>".$lieux[$lieu];
                    foreach($livraisons as $date=>$qte){
                        foreach($stockcmdids[$date] as $stockcmdid=>$qtes){
                            ///pour les commandes sur affaire, mettre le n° de facture
                            if($stockcmdid==0){
                                $txtlivr="";
                                $faclist=array();
                                $dest_facs=$DB_site2->query("SELECT dest, origin, count(*) as qte FROM stock_item si WHERE artid IN ".$art_in." AND stock_cmd_id=0 AND state != 3 AND state != 2 AND dest != '' GROUP BY dest");
                                while($dest_fac=$DB_site2->fetch_array($dest_facs)){
                                    if(substr($dest_fac['dest'],0,7)=="facture") $facid=substr($dest_fac['dest'],8);
                                    $envoi_lignes=$DB_site2->query("SELECT outdate, efl.qte, enlevement,ef.type FROM lignefacture lf INNER JOIN envoi_fournisseur_ligne efl ON efl.ligne_facture_id = lf.lignefactureid INNER JOIN envoi_fournisseur ef ON ef.id = efl.efid WHERE lf.factureid = ".$facid." AND lf.artid IN ".$art_in." AND (ef.type='fourn' OR ef.type='stock') GROUP BY efl.qte ORDER BY enlevement DESC");
                                    while($envoi_ligne=$DB_site2->fetch_array($envoi_lignes)){
                                        //si la commande a été enlevée, elle ne doit plus apparaitre en réappro. Sinon, on l'affiche bien :
                                        if($envoi_ligne['enlevement']==0){
                                            $txtlivr.=($envoi_ligne['outdate']>0?date("d/m/Y",$envoi_ligne['outdate']).' : ':"").$envoi_ligne['qte']." - <a href=\"facture.php?action=editer&facturid=".$facid."\" target=\"_blank\">n°".$facid."</a><br>";
                                        }
                                    }
                                }
                            }
                            else{
                                $txtlivr.="<br>".date("d/m/Y",$date)." : ".$qtes." - <a href=\"stock.php?action=cmd&stock_cmd_id=".$stockcmdid."\" target=\"_blank\">n°".$stockcmdid."</a>";
                                $qte_tt_prev+=$qtes;
                            }
                        }
                    }
                    $txtlivr.="</p>";
                }

/// pour les commandes non payées
                $cmdnnpay="SELECT factureid,qte FROM lignefacture INNER JOIN facture ON(facturid=factureid) WHERE (select count(*) from envoi_fournisseur_ligne where ligne_facture_id=lignefactureid AND type IN ('fourn','stock'))=0 AND supprimee='0' AND artid IN ".$art_in." AND valide = 0 AND moyenid in (2,4,5,14,17,18,99)";
                $reqcmd=$DB_site2->query($cmdnnpay);
                $qte_nn_paye=0;
                $list_nn_paye="";
                while($facture=$DB_site2->fetch_array($reqcmd)){
                    $non_paye=$DB_site2->query_first("SELECT COUNT(*) FROM stock_item WHERE artid=".$ligne->artid." AND dest='facture ".$facture['factureid']."' AND state=2");
                    if($non_paye[0]<$facture['qte']){
                        $qte_nn_paye+=$facture['qte'];
                        $list_nn_paye.="<br><a href=\"facture.php?action=editer&facturid=".$facture[0]."\" target=\"_blank\" style=\"".($facture[0]==$facturid?"color:red;":"")."\">".$facture[0]."</a>";
                    }
                }

/// pour les commandes non réservées:
                $cmdqte="SELECT factureid,qte FROM lignefacture INNER JOIN facture ON(facturid=factureid) WHERE (select count(*) from envoi_fournisseur_ligne where ligne_facture_id=lignefactureid AND type IN ('fourn','stock'))=0 and valide in ('1','7') and supprimee='0' and artid IN ".$art_in;
                $reqcmd=$DB_site2->query($cmdqte);
                $qte_nn_reserve=0;
                $list_nn_reserve="";
                while($facture=$DB_site2->fetch_array($reqcmd)){
                    $reserve=$DB_site2->query_first("SELECT COUNT(*) FROM stock_item WHERE artid IN ".$art_in." AND dest='facture ".$facture['factureid']."' AND state=2");
                    if($reserve[0]<$facture['qte']){
                        $qte_nn_reserve+=$facture['qte'];
                        $list_nn_reserve.="<br><a href=\"facture.php?action=editer&facturid=".$facture[0]."\" target=\"_blank\" style=\"".($facture[0]==$facturid?"color:red;":"")."\">".$facture[0]."</a>";
                    }
                }

/// pour les commandes réservées:
                $list_reserve="";
                $qte_reserve=0;
                $cmdres=$DB_site2->query("SELECT si.*,mini,alert,artids,delai FROM stock_item si LEFT JOIN stock_mini USING(artid) WHERE state IN ('2') AND artid IN ".$art_in." GROUP BY si.id");
                if($DB_site2->num_rows($cmdres)>0){
                    $qte_reserve=$DB_site2->num_rows($cmdres);
                    $req_dest=$DB_site2->query("SELECT dest FROM stock_item si WHERE artid IN ".$art_in." AND state=2 GROUP BY dest");
                    $cmdlist=array();
                    while($cmd=$DB_site2->fetch_array($req_dest)){
                        if(substr($cmd[0],0,7)=="facture") $cmdlist[]=substr($cmd[0],8);
                    }
                    foreach($cmdlist as $cmdid){
                        $list_reserve.="<br><a href=\"facture.php?action=editer&facturid=".$cmdid."\" target=\"_blank\" style=\"".($cmdid==$facturid?"color:red;":"")."\">".$cmdid."</a>";
                    }
                }
                $stock1=true;
/// qte physique (avec les articles similaires)
                $r1=$stock->stockbyqte(array(1,2),null);
                $r=array_merge($r1,$stock->stock0(array(1,2),null,true));
                ksort($r);
                $nb_stock=0;
                foreach($r as $item){
                    foreach($item->similaire as $artid=>$similaire){
                        if($ligne->artid==$artid){
                            $nb_stock+=$item->qte;
                        }
                    }
                    if($item->artid==$ligne->artid){
                        $nb_stock+=$item->qte;
                    }
                }

                $etat_art = $DB_site2->query_first("SELECT active FROM article WHERE artid ='".$ligne->artid."'");
                if($etat_art['active'] == '1'){
                    $etat_article = 'Actif';
                }
                elseif($etat_art['active'] == '0'){
                    $etat_article = 'Inactif';
                }
                elseif($etat_art['active'] == '2'){
                    $etat_article = 'Supprimé';
                }
                elseif($etat_art['active'] == '3'){
                    $etat_article = 'Archivé temporairement';
                }
                else{
                    $etat_article = 'Archivé définitivement';
                }

                if(preg_match('/ginko|acilyon|frio|ajd/i', $txtdetail)) $stockArtCode[] = $ligne->artcode;
                print "<tr><td><img src=\"../configurations/$host/images/produits/".$ligne->artid.".jpg\" width=\"30\"></td><td>".$ligne->qte." ".$ligne->artcode."<br>".$ligne->ref."<br><i>".$etat_article."</i></td><td style=\"text-align:center;\"><b>".$nb_stock."</b></td><td style=\"text-align:center;\">".$qte_nn_reserve.$list_nn_reserve."</td><td style=\"text-align:center;\">".$qte_reserve.$list_reserve."</td><td style=\"text-align:center;\">".$qte_nn_paye.$list_nn_paye."</td><td style=\"text-align:center;background-color:#c4c3c3;\"><b>".($nb_stock-$qte_nn_reserve-$qte_reserve-$qte_nn_paye)."</b></td><td>".$txtdetail."</td><td>".$txtlivr."</td><td style=\"text-align:center;background-color:#c4c3c3;\"><b>".($nb_stock-$qte_nn_reserve-$qte_reserve-$qte_nn_paye+$qte_tt_prev)."</b></td></tr>";
            }
        }

        if(!$stock1) print "<tr><td colspan=\"6\" style=\"text-align:center;\">Aucun article en stock</td></tr>";
        print "</table>
</div>";
    }

// afficher en jaune les articles de la partie "stock produit" dispo dans le stock ginko/acilyon
    $style = '<style>.stockArtCode';
    foreach ($stockArtCode as $key => $value) {
        $style .= ', .' . $value;
    }
    $style .= ' { background: yellow !important; }</style>';
    echo $style;

// marge
    if(in_array($rs_facture['valide'],array(0))){

        print "<div class=\"tab_special\" id=\"calcul_marge\">
    <h2>Calcul de la marge</h2>
    <div class=\"clear\"></div>
    <table border=\"1\" cellspacing=\"0\" style=\"background:white;\">
    <thead style=\"font-weight:bold;\">
    <tr><td rowspan=\"2\">Ref produit</td><td rowspan=\"2\">Qté</td><td colspan=\"7\" align=\"center\">Achat HT</td><td colspan=\"2\" align=\"center\">Vente</td><td colspan=\"2\" align=\"center\">Marge</td></tr>
    <tr><td>Produit</td><td>Eco part</td><td>Emball.</td><td>Gties</td><td>Options</td><td>Trspt</td><td align=\"center\">Total</td><td align=\"center\">HT</td><td align=\"center\">TTC</td><td align=\"center\">HT</td><td align=\"center\">%</td></tr>
    </thead>";
        $marge=new marge($DB_site2,$rs_facture['facturid']);
        foreach($marge->articles as $ligne){
            print "<tr>
        <td>".$ligne->artcode."</td>
        <td align=\"center\">".$ligne->qte."</td>
        <td align=\"right\">".number_format($ligne->paht,2,',','')."</td>
        <td align=\"right\">".number_format($ligne->ecopart,2,',','')."</td>
        <td align=\"right\">".number_format($ligne->prixtrspt,2,',','')."</td>
        <td align=\"right\">".number_format($ligne->garantie,2,',','')."</td>
        <td align=\"right\">".number_format($ligne->options,2,',','')."</td>
        <td align=\"right\">".number_format($ligne->transport,2,',','')."</td>
        <td align=\"right\">".number_format($ligne->total,2,',','')."</td>
        <td align=\"right\">".number_format($ligne->prixht,2,',','')."</td>
        <td align=\"right\">".number_format($ligne->prix,2,',','')."</td>
        <td align=\"right\">".number_format($ligne->marge,2,',','')."</td>
        <td align=\"right\">".number_format($ligne->pcmarge,2,',','')." %</td>
        </tr>";
        }
        print "<tr><td>Code promo</td>
    <td align=\"center\">".($marge->codepromo==0?0:1) ."</td>
    <td align=\"center\" colspan=\"7\">&nbsp;</td>
    <td align=\"right\">".number_format($marge->codepromo,2,',','')."</td>
    <td align=\"right\">".number_format($marge->codepromottc,2,',','')."</td></tr>";
        print "<tr><td>Installation/transport</td>
    <td align=\"center\">1</td>
    <td colspan=\"6\">&nbsp;</td>
    <td align=\"right\">".number_format($marge->installation,2,',','')."</td>
    <td align=\"right\">".number_format($marge->venteport,2,',','')."</td>
    <td align=\"right\">".number_format($marge->venteportttc,2,',','')."</td></tr>";
        print "<tr style=\"background-color:#cccccc;color:black;\"><td style=\"font-weight:bold;\">TOTAL</td>
    <td colspan=\"7\">&nbsp;</td>
    <td align=\"right\">".number_format($marge->total,2,',','')."</td>
    <td align=\"right\">".number_format($marge->totalvente,2,',','')."</td>
    <td align=\"right\">".number_format($marge->totalventettc,2,',','')."</td>
    <td style=\"text-align:right;color:black;font-weight:bold;\">".number_format($marge->marge,2,',','')."</td>
    <td style=\"text-align:right;color:black;font-weight:bold;\">".number_format($marge->pcmarge,2,',','')." %</td></tr>
    </table>";


        $seuils=array(35,30,25,22,20);
        $nbseuils=count($seuils);
        $i=0;
        $lpc=100;

        print "<table style=\"color:black;border:solid thin black;margin-top:10px;background:white;\">
    <tr><td></td><td colspan=\"7\" align=\"center\">Seuil de marge</td></tr>
    <tr><td>&nbsp;<br>Nx PV HT<br>Nx PV TTC<br>Remise HT<br>Remise TTC<br>Remise %<br><b>Marge restante</b></td>";
        foreach($seuils as $pc){
            if($pc<$marge->pcmarge && $lpc>$marge->pcmarge){
                print "<td style=\"background:".($pc>=25?"#99FF99":"#FF9999").";text-align:right;border:solid thin black;width:70px;\">
            <div style=\"color:black;text-align:center;\">".number_format($marge->pcmarge,1,',','')."%</div>
            ".number_format($marge->totalvente,2,',','')."
            <br>".number_format($marge->totalventettc,2,',','')."
            <br>0,00
            <br>0,00
            <br>0
            <br><b>".number_format($marge->marge,2,',','')."</b></td>";
            }
            $i++;
            $lpc=$pc;
            $nmarge=$marge->set_marge($pc);
            print "<td style=\";text-align:right;width:70px;\">
        <div style=\"color:black;text-align:center;\">".$pc."%</div>
        ".number_format($nmarge['pv'],2,',','')."
        <br>".number_format($nmarge['pvttc'],2,',','')."
        <br>".number_format($nmarge['remise'],2,',','')."
        <br>".number_format($nmarge['remisettc'],2,',','')."
        <br>".number_format($nmarge['pcremise'],2,',','')."
        <br><b>".number_format($nmarge['marge'],2,',','')."</b></td>";

        }
        if($lpc>$marge->pcmarge){
            print "<td style=\"background:#FF9999;text-align:right;border:solid thin black;width:70px;\">
        <div style=\"color:black;text-align:center;\">".number_format($marge->pcmarge,1,',','')."%</div>
        ".number_format($marge->totalvente,2,',','')."
        <br>".number_format($marge->totalventettc,2,',','')."
        <br>0,00
        <br>0,00
        <br>0
        <br><b>".number_format($marge->marge,2,',','')."</b></td>";
        }
        print "<td id=\"mantd\" style=\"text-align:right;background-color:#3399FF;color:black;width:70px;\">
    <div id=\"manpc\" style=\"text-align:center;\">&nbsp;</div>
    <input type=\"text\" size=\"6\" id=\"manpv\" style=\"border:solid thin black;background:white;text-align:right;\" onclick=\"this.select()\" onkeyup=\"marge(".$marge->total.",".$marge->totalvente.",".$nmarge['tx'].",'ht')\">
    <br><input type=\"text\" size=\"6\" id=\"manpvttc\" style=\"border:solid thin black;background:white;text-align:right;\" onclick=\"this.select()\" onkeyup=\"marge(".$marge->total.",".$marge->totalvente.",".$nmarge['tx'].",'ttc')\">
    <div id=\"manremise\">&nbsp;</div>
    <div id=\"manremisettc\">&nbsp;</div>
    <div id=\"manremisepc\">&nbsp;</div>
    <div id=\"manmarge\">&nbsp;</div>
    </td>
    </tr></table>";

        $history=array();
        $requete=$DB_site2->query("SELECT h.*,lf.prix FROM lignefacture lf INNER JOIN history h ON (h.artcode=lf.artcode) WHERE lf.factureid=$facturid AND h.timestamp>".(time()-86400*7)." ORDER BY price");
        if($DB_site2->num_rows($requete)>0){

            while($article=$DB_site2->fetch_array($requete)){
                $history[$article['artcode']][$article['site']]=$article['price'];
                $sites[$article['site']]=1;
                $mcav[$article['artcode']]=$article['prix'];
            }
            print "<h2>Prix concurrents</h2>
        <div class=\"clear\"></div>";
            print "<table border=\"1\" cellspacing=\"0\" style=\"background:white;\">
        <thead style=\"text-align:center;font-weight:bold;margin:2px;\">
        <tr><td></td><td>MCAV</td>";
            foreach($sites as $site=>$n)
                print "<td>".$site."</td>";
            print "</tr></thead>";

            foreach($history as $artcode=>$prices){
                asort($prices);
                print "<tr><td>".$artcode."</td><td>".number_format($mcav[$artcode],2,',','')."</td>";
                foreach($sites as $site=>$n){
                    print "<td style=\"text-align:right;".($mcav[$artcode]>$prices[$site]?"color:red;":"")."\">".number_format($prices[$site],2,',','')."</td>";
                }
                print "</tr>";
            }
            print "</table>";
        }

        print "<br></div>";
    }

    //fournisseurs
    if(in_array($rs_facture['valide'],array(1,2,5,7,8))){

        $totalachat         = 0;
        $totalachatreel     = 0;
        $totalfactureht     = 0;
        $totalfacturehtreel = 0;
        $tr    = array();
        $types = array(
            ""         => "Commande site",
            "avoir"    => "Avoir",
            "sav"      => "Commande SAV",
            "fourn"    => "Achats produits",
            "stock"    => "Sortie de stock",
            "trspt"    => "Achat transport",
            "sls"      => "Achat installateur",
            "gar"      => "Achat garantie SFG",
            "com"      => "Commission",
            "avoir"    => "Avoir fournisseur",
            "part"     => "Commission partenaire",
            "garantie" => "Achat garantie",
            "stockam"  => "Frais stockage Amazon",
            "trsptam"  => "Frais expedition Amazon",
            "divam"    => "divers Amazon"
        );

        //  $requete=$DB_site2->query("SELECT efl.ligne_facture_id,efl.paht,efl.ecopart,efl.qte,efl.facture FROM lignefacture lf inner join envoi_fournisseur_ligne efl on(efl.ligne_facture_id=lf.lignefactureid) WHERE lf.factureid=".$facturid);

        //Boucle sur toutes les commandes liées, pour déterminer la marge
        foreach($savlinks as $linkid){

            $fact = new facture($DB_site2,$linkid);

            if(in_array($fact->valide,array(1,5,7,8))){

                foreach($fact->lignes as $ligneid => $lignefacture){

                    $color = "";
                    if($fact->valide != 1 && $fact->valide != 5) $color = "red";

                    $tr[$fact->datecommande] .= ""
                        . "<tr style=\"\">"
                        . "<td>".$types[$fact->provenance]."</td>"
                        . "<td>".$lignefacture->qte."x ".$lignefacture->artcode."</td>"
                        . "<td>".$fact->utilisateur->raisonsociale." ".$fact->utilisateur->nom."</td>"
                        . "<td style=\"background-color:".$linkcolor[$fact->facturid].";\"><a href=\"facture.php?action=editer&facturid=".$fact->facturid."\">".$fact->facturid."</a></td>"
                        . "<td>".date("d/m/Y",$fact->datecommande)."</td>"
                        . "<td>".date("d/m/Y",$fact->dateexpedition)."</td>"
                        . "<td>".$fact->commercial."</td>"
                        . "<td style=\"text-align:right;color:$color;\">".$lignefacture->totalht."</td>"
                        . "<td></td>"
                        . "</tr>";

                    $totalfactureht += $lignefacture->totalht;

                    if($fact->valide == 1 || $fact->valide == 5) $totalfacturehtreel += $lignefacture->totalht;
                }

                $totalfactureht -= $fact->cadeauht;
                $totalfactureht += $fact->portht;

                if($fact->valide == 1 || $fact->valide == 5){

                    $totalfacturehtreel -= $fact->cadeauht;
                    $totalfacturehtreel += $fact->portht;
                }

                //Si le SAV comporte des frais de port, on inpute à la marge
                if($fact->portht > 0){

                    $tr[$fact->datecommande] .= ""
                        . "<tr>"
                        . "<td>".$types[$fact->provenance]."</td>"
                        . "<td>Frais de port</td>"
                        . "<td>".$fact->utilisateur->raisonsociale." ".$fact->utilisateur->nom."</td>"
                        . "<td style=\"background-color:".$linkcolor[$fact->facturid].";\"><a href=\"facture.php?action=editer&facturid=".$fact->facturid."\">".$fact->facturid."</a></td>"
                        . "<td>".date("d/m/Y",$fact->datecommande)."</td>"
                        . "<td>".date("d/m/Y",$fact->dateexpedition)."</td>"
                        . "<td>".$fact->commercial."</td>"
                        . "<td style=\"text-align:right;color:$color;\">".$fact->portht."</td>"
                        . "<td></td>"
                        . "</tr>";
                }

                //Si le SAV comporte une remise, on inpute à la marge
                if($fact->cadeauht > 0){

                    $tr[$fact->datecommande] .= ""
                        . "<tr>"
                        . "<td>".$types[$fact->provenance]."</td>"
                        . "<td>Code promo</td>"
                        . "<td>".$fact->utilisateur->raisonsociale." ".$fact->utilisateur->nom."</td>"
                        . "<td style=\"background-color:".$linkcolor[$fact->facturid].";\"><a href=\"facture.php?action=editer&facturid=".$fact->facturid."\">".$fact->facturid."</a></td>"
                        . "<td>".date("d/m/Y",$fact->datecommande)."</td>"
                        . "<td>".date("d/m/Y",$fact->dateexpedition)."</td>"
                        . "<td>".$fact->commercial."</td>"
                        . "<td style=\"text-align:right;color:$color;\">-".$fact->cadeauht."</td>"
                        . "<td></td>"
                        . "</tr>";
                }

            }

            $trsptfourn=array();

            $sql = "SELECT ef.id AS efid, ef.facturid, ef.tarif, ef.type, efl.ligne_facture_id, efl.paht, efl.ecopart, efl.qte, efl.facture, efl.date,lf.artid";
            $sql .= " FROM envoi_fournisseur ef";
            $sql .= " INNER JOIN envoi_fournisseur_ligne efl ON (efl.efid = ef.id)";
            $sql .= " INNER JOIN lignefacture lf ON (efl.ligne_facture_id=lf.lignefactureid)";
            $sql .= " WHERE ef.destinataire <> '0'";
            $sql .= " AND ef.facturid = ".$linkid;
            $requete = $DB_site2->query($sql);

            while($ligne = $DB_site2->fetch_array($requete)){

                $facturefourn = array();
                $datefacture  = "";
                $color        = "red";

                //Si le produit est acheté chez le fournisseur, la sortie de stock doit être à 0 (stock sur affaire)
                $sqlTest = "SELECT efl.id, efl.paht";
                $sqlTest .= " FROM envoi_fournisseur_ligne efl";
                $sqlTest .= " INNER JOIN envoi_fournisseur ef ON (ef.id = efl.efid)";
                $sqlTest .= " WHERE efl.ligne_facture_id = ".$ligne['ligne_facture_id'];
                $sqlTest .= " AND (efl.type = 'fourn' OR efl.type = 'stock')";
                $sqlTest .= " AND ef.destinataire <> '0'";
                $sqlTest .= " AND ef.facturid = ".$linkid;
                $req = $DB_site2->query($sqlTest);
                $change_paht = false;
                if($DB_site2->num_rows($req) == 2){
                    $change_paht = true;
                    while($row = $DB_site2->fetch_array($req)){
                        if($row['paht'] == 0) $change_paht = false;
                    }
                }

                if($change_paht && $ligne['type'] == 'stock'){
                    $ligne['ecopart'] = 0;
                    $ligne['paht']    = 0;
                }

                if($ligne['facture'] != "" || $ligne['facture'] == "STOCK"){

                    $datefacture = "";
                    $color       = "";

                    $sql = "SELECT *";
                    $sql .= " FROM facture_fournisseur";
                    $sql .= " WHERE numero = '".$ligne['facture']."'";
                    $facturefourn = $DB_site2->query_first($sql);

                    if($facturefourn['id'] != "") $datefacture = date("d/m/Y",$facturefourn['date_facture']);
                    $totalachatreel += $ligne['qte'] * ($ligne['paht'] + $ligne['ecopart']);
                }


                $listorigin="";
                if($ligne['facture']=="STOCK"){
                    $origins=array();
                    $stock=new stock($DB_site2);
                    foreach($stock->search($ligne['artid'],null,"facture ".$linkid,array(1,2,3)) as $item){
                        $origin=$item->origin;
                        $originid="";
                        $info="";
                        if($origin=="cmd"){
                            $originid=$item->stock_cmd_id;
                            $info="cmd stock <a href=\"stock.php?action=cmd&stock_cmd_id=".$originid."\" target=\"_blank\">".$originid."</a> ".$item->paht."&euro;";
                        }
                        if(preg_match("/retour:(\d+)/",$origin,$m)){
                            $origin="retour";
                            $originid=$m[1];
                            $info="retour cmd <a href=\"facture.php?action=editer&facturid=".$m[1]."\" target=\"_blank\">".$m[1]."</a> ".$item->paht."&euro;";
                        }
                        if($origin=="fourn"){
                            $info="stock sur affaire";
                        }
                        if($origin=="manu")
                            $info=$item->infos." ".$item->paht."&euro;";

                        if(isset($origins[$origin.$originid]))
                            $origins[$origin.$originid]['qte']++;
                        else
                            $origins[$origin.$originid]=array("origin"=>$origin,"originid"=>$originid,"qte"=>1,"paht"=>$item->paht,"info"=>$info);
                    }

                    foreach($origins as $origin=>$data){
                        $listorigin.="&nbsp;-".$data['qte']."x ".$data['info']."<br>";
                    }
                }

                $sql = "SELECT lf.libelle, lf.artcode";
                $sql .= " FROM lignefacture lf";
                $sql .= " WHERE lf.lignefactureid = ".$ligne['ligne_facture_id'];
                $lignefacture = $DB_site2->query_first($sql);

                $tr[$ligne['date']] .= ""
                    . "<tr style=\"background-color:#dddddd;\">"
                    . "<td>".$types[$ligne['type']]."</td>"
                    . "<td>".$ligne['qte']."x ".$lignefacture['artcode']."</td>"
                    . "<td>".$facturefourn['fournisseur']."</td>"
                    . "<td style=\"background-color:".$linkcolor[$linkid].";\"><a href=\"fournisseur.php?action=editer&facturid=".$ligne['facture']."\">".$ligne['facture']."</a></td>"
                    . "<td>".date("d/m/Y",$ligne['date'])."</td>"
                    . "<td>".$datefacture."</td>"
                    . "<td>".$facturefourn['utilisateur']."</td>"
                    . "<td></td>"
                    . "<td style=\"text-align:right;color:$color;\">".number_format($ligne['qte']*($ligne['paht']+$ligne['ecopart']),2,".","")."</td>"
                    . "<td>".$listorigin."</td>"
                    . "</tr>";

                $totalachat += $ligne['qte'] * ($ligne['paht'] + $ligne['ecopart']);

                /* /// SI#6562 : erreur calcul prévisionnel /// mazetcout obsolète ? vu avec philippe
   ///Commenté car sinon ça double l'achat de transport et fausse la marge

            $adv=new adv($DB_site2);
            $adv->load_from_efid($ligne['efid']);
            if(isset($adv->fields['mazetcout']) && !isset($trsptfourn['mazetcout'.$adv->id])){
                $trsptfourn['mazetcout'.$adv->id][$adv->date_creation]=""
                . "<tr style=\"background-color:#dddddd;\">"
                . "<td>".$types['fourn']."</td>"
                . "<td>Transport fournisseur</td>"
                . "<td>".$adv->destinataire."</td>"
                . "<td style=\"background-color:".$linkcolor[$linkid].";\"></td>"
                . "<td>".date("d/m/Y",$adv->date_creation)."</td>"
                . "<td></td>"
                . "<td>".$adv->utilisateur."</td>"
                . "<td></td>"
                . "<td style=\"text-align:right;color:red;\">".number_format($adv->fields['mazetcout'],2,".","")."</td>"
                . "</tr>";

                $totalachat+=floatval($adv->fields['mazetcout']);
            } */
            }

            $sql = "SELECT *";
            $sql .= " FROM facture_fournisseur_autre";
            $sql .= " WHERE facturid = ".$linkid;
            $requete = $DB_site2->query($sql);

            $commission_recue = FALSE;
            while($ligne = $DB_site2->fetch_array($requete)){

                $sql = "SELECT *";
                $sql .= " FROM facture_fournisseur";
                $sql .= " WHERE numero = '".$ligne['facture']."'";
                $facturefourn = $DB_site2->query_first($sql);

                $datefacture    = date("d/m/Y",$facturefourn['date_facture']);
                $totalachat     += $ligne['prix']; // décommenter suite à la tâche de philippe SI#6483
                $totalachatreel += $ligne['prix'];

                $tr[$facturefourn['date_facture']] .= ""
                    . "<tr style=\"background-color:#dddddd;\">"
                    . "<td>".$types[$ligne['type']]."</td>"
                    . "<td>".$ligne['description']."</td>"
                    . "<td>".$facturefourn['fournisseur']."</td>"
                    . "<td style=\"background-color:".$linkcolor[$linkid].";\"><a href=\"fournisseur.php?action=editer&facturid=".$facturefourn['numero']."\">".$facturefourn['numero']."</a></td>"
                    . "<td></td>"
                    . "<td>".$datefacture."</td>"
                    . "<td>".$facturefourn['utilisateur']."</td>"
                    . "<td></td>"
                    . "<td align=\"right\">".number_format($ligne['prix'],2,".","")."</td>"
                    . "</tr>";

                if($ligne['type'] == "com") {
                    $commission_recue = TRUE;
                }

                if(isset($trsptfourn[$ligne['assoc']])) unset($trsptfourn[$ligne['assoc']]);
            }

            $sql = "SELECT ffid, facturid, date, utilisateur, destinataire, article, paht, ecopart, qte, date_recu";
            $sql .= " FROM avoir_ligne";
            $sql .= " WHERE facturid = ".$linkid;
            $requete = $DB_site2->query($sql);

            while($ligne=$DB_site2->fetch_array($requete)){

                //if($ligne['date_recu']>0) $totalachatreel-=($ligne['paht']+$ligne['ecopart']);
                if($ligne['date_recu'] == 0){
                    $totalachat -= ($ligne['paht']+$ligne['ecopart']);
                    $tr[$ligne['date']] .= ""
                        . "<tr style=\"background-color:#dddddd;\">"
                        . "<td>Demande d'avoir</td>"
                        . "<td>".$ligne['qte']."x ".$ligne['article']."</td>"
                        . "<td>".$ligne['destinataire']."</td>"
                        . "<td style=\"background-color:".$linkcolor[$linkid].";\">".$ligne['ffid']."</td>"
                        . "<td></td>"
                        . "<td>".date("d/m/Y",$ligne['date'])."</td>"
                        . "<td>".$ligne['utilisateur']."</td>"
                        . "<td></td>"
                        . "<td align=\"right\" style=\"".($ligne['date_recu']==0?"color:red;":"")."\">".number_format(-(($ligne['paht']+$ligne['ecopart'])*$ligne['qte']),2)."</td>"
                        . "</tr>";
                }
            }


            //SI rentrée de stock (08/04/2016)
            $sql = "SELECT s.artid, a.artcode, s.date, s.utilisateur, a.paht, a.ecopart, COUNT(*) AS qte, s.paht";
            $sql.= " FROM stock_item s";
            $sql.= " INNER JOIN article a USING(artid)";
            $sql.= " WHERE origin ='retour:".$linkid."'";
            $sql.= " GROUP BY date";
            $req = $DB_site2->query($sql);

            //Boucle sur le nombre de rentrée de stock
            while($retour = $DB_site2->fetch_array($req)){
                $artids=array($retour['artid']);
                $si = $DB_site2->query("SELECT artid, artids FROM stock_mini WHERE artid=".$retour['artid']);
                if($DB_site2->num_rows($si) != 0){
                    $a = $DB_site2->fetch_array($si);
                    $artids = array_merge($artids,explode(",",$a['artids']));
                }
                $artids=array_filter($artids);
                //Mise à jour du total achat H.T
                $totalachat = $totalachat - ($retour['qte']*$retour['paht']);
                $totalachatreel = $totalachatreel - ($retour['qte']*$retour['paht']);
                //Affichage du tableau
                $tr[$retour['date']] .= ""
                    . "<tr style=\"background-color:#cfe9dd;\">"
                    . "<td>Rentrée stock</td>"
                    . "<td>".$retour['qte']."x ".$retour['artcode']."</td>"
                    . "<td></td>"
                    . "<td style=\"background-color:".$linkcolor[$linkid].";\"></td>"
                    . "<td>".date("d/m/Y",$retour['date'])."</td>"
                    . "<td></td>"
                    . "<td>".$retour['utilisateur']."</td>"
                    . "<td style=\"text-align:right;\"></td>"
                    . "<td style=\"text-align:right;\">-".number_format($retour['qte']*$retour['paht'],2)."</td>"
                    . "</tr>";
            }

            // Si facture Market Place (Commission)
            if($fact->provenance == "mp" && $fact->commission != 0 && $commission_recue == FALSE){
                $tr[$rs_facture['provenance']] .= ""
                    . "<tr style=\"background-color:#cfe9dd;\">"
                    . "<td>Commission MP</td>"
                    . "<td></td>"
                    . "<td></td>"
                    . "<td></td>"
                    . "<td></td>"
                    . "<td></td>"
                    . "<td></td>"
                    . "<td style=\"text-align:right;\"></td>"
                    . "<td style=\"text-align:right;\">".number_format($rs_facture['commission'],2)."</td>"
                    . "</tr>";
                $totalachat = $totalachat + $rs_facture['commission'];
            }

        }

        foreach($trsptfourn as $assoc=>$lignes){
            foreach($lignes as $ts=>$ligne)
                $tr[$ts].=$ligne;
        }

        //Remplace -0 par 0 (08/04/2016)
        $totalachat = (round($totalachat,2) == '-0') ? '0' : round($totalachat,2);

        ksort($tr);

        $recapcommandes = ""
            . "<table>"
            . "<tr style=\"background-color:#b18456; font-weight:bold;\">"
            . "<td>Type</td>"
            . "<td>Réf. produit</td>"
            . "<td>Destinataire</td>"
            . "<td>N°</td>"
            . "<td>Date commande</td>"
            . "<td>Date facture</td>"
            . "<td>Entrée par</td>"
            . "<td>Montant H.T</td>"
            . "<td>Achat H.T</td>"
            . "<td>Historique</td>"
            . "</tr>"
            . implode("",$tr)
            . "<tr>"
            . "<td colspan=\"7\"><b>Total prévisionnel</b></td>"
            . "<td align=\"right\">".number_format($totalfactureht,2,',','')."</td>"
            . "<td align=\"right\">".number_format($totalachat,2,',','')."</td>"
            . "</tr>"
            . "<tr>"
            . "<td colspan=\"7\"><b>Total réel</b></td>"
            . "<td align=\"right\">".number_format($totalfacturehtreel,2,',','')."</td>"
            . "<td align=\"right\">".number_format($totalachatreel,2,',','')."</td>"
            . "</tr>"
            . "</table>";

        print "<br><div class=\"table_factures col-xs-12\" id=\"detail_marge\">".$recapcommandes;

        $marge     = $totalfactureht-$totalachat;
        $margereel = $totalfacturehtreel-$totalachatreel;

        print ""
            . "<br><br>"
            ."<div class=\"col-xs-6\"></div><div class=\"col-xs-6\"><h3 style=\"color: black;font-weight: 700;text-align: center;\">Etude de Rentabilité</h3></div>"
            . "<div class=\"col-xs-6 no-padding\"><table border='1'>"
            . "<tr>"
            . "<td style=\"text-align:center; font-weight:bold;\">Marge</td>"
            . "<td style=\"width:150px;text-align:center;\">Prévisionnel</td>"
            . "<td style=\"width:150px;text-align:center;\">Réel</td>"
            . "</tr>"
            . "<tr>"
            . "<td>Montant commande H.T</td>"
            . "<td align=\"right\">".number_format($totalfactureht,2,',','')." &euro;</td>"
            . "<td align=\"right\">".number_format($totalfacturehtreel,2,',','')." &euro;</td>"
            . "</tr>"
            . "<tr>"
            . "<td>Total achats H.T</td>"
            . "<td align=\"right\">".number_format($totalachat,2,',','')." &euro;</td>"
            . "<td align=\"right\">".number_format($totalachatreel,2,',','')." &euro;</td>"
            . "</tr>"
            . "<tr>"
            . "<td>Marge brute H.T</td>"
            . "<td align=\"right\" ".($marge<0?"style=\"color:red;\"":"").">".number_format($marge,2,',','')." &euro; ".($totalfactureht>0?"(".round(100*(1-$totalachat/$totalfactureht),2)." %)":"")."</td>"
            . "<td align=\"right\" ".($margereel<0?"style=\"color:red;\"":"").">".number_format($margereel,2,',','')." &euro; ".(round($totalfacturehtreel,0)>0?"(".round(100*(1-$totalachatreel/$totalfacturehtreel),2)." %)":"")."</td>"
            . "</tr>"
            . "</table></div>";


        $analyse = $DB_site2->query_first("SELECT * FROM facture_rentabilite WHERE facturid='".$facturid."'");
        print "<div id=\"etuderenta2\" class=\"col-xs-6 no-padding\">";
        if(!$analyse){
            //print "<div id=\"etuderenta\">";
            print "<form name=\"etuderenta\" method=\"post\">"
                ."<div id=\"tabetuderenta\">"
                ."<input type=\"hidden\" name=\"facturid\" value=\"".$facturid."\">"
                ."<input type=\"hidden\" id=\"etatRenta\" name=\"etatRenta\" value=\"\">"
                ."<div class=\"col-xs-3\">"
                ."<a class=\"good col-xs-12\" onclick=\"validEtudeRenta(1);\">Bon</a>"
                ."<a class=\"bad col-xs-12\" onclick=\"validEtudeRenta(2);\">Mauvais</a>"
                ."</div>"
                ."<div class=\"col-xs-9\">"
                ."<textarea name=\"noteEtudeRenta\" rows=\"4\" style=\"width:100%;\" placeholder=\"Notes...\"></textarea>"
                ."</div>"
                ."</div></form>";
        }else{
            $dateRenta = date("d/m/Y à H:i", $analyse['date']);
            //$dateRenta = $dateRenta->format('d/m/Y à H:m:i');
            if($analyse['etat'] == 1){
                $txtetat = "<span class=\"good\">BON</span>";
            }elseif ($analyse['etat'] == 2) {
                $txtetat = "<span class=\"bad\">MAUVAIS</span>";
            }
            print "<form name=\"etuderenta\" method=\"post\">"
                ."<div id=\"tabetuderenta\" class=\"col-xs-12\">"
                ."<input type=\"hidden\" name=\"facturid\" value=\"".$facturid."\">"
                //."<input type=\"hidden\" id=\"etatRenta\" name=\"etatRenta\" value=\"".$analyse['etat']."\">"
                ."<input type=\"hidden\" id=\"modifRenta\" name=\"modifRenta\" value=\"1\" >"
                ."<div class=\"col-xs-12\">"
                ."<p>Fait le ".$dateRenta." par ".$analyse['personne'].". &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$txtetat."</p>"
                ."<textarea name=\"noteEtudeRenta\" rows=\"4\" style=\"width:100%;\" >".$analyse['note']."</textarea>"
                ."</div>"
                ."<button type=\"button\" class=\"col-xs-offset-4 col-xs-4\" onclick=\"document.forms.etuderenta.submit();\">Modifier</button>"
                ."</div></form>";
        }

        print "</div></div>";
    }
    echo "<div class=\"clear\"></div>";

    //suivi client
    $suivi=$DB_site2->query_first("SELECT suivi,suivi2,advdate1,advdate2,advdate3 FROM facture WHERE facturid=$facturid");

    $usertel=($user_info['tel']!=0);

    $suiviicon=array( "tel_out"=>"<img src=\"images/tel_out.png\"><br>",
        "tel_in"=>"<img src=\"images/tel_in.png\"><br>",
        "mail_in"=>"<img src=\"images/get_mail.png\"><br>");
    // $suivitd=suivi_commande($facturid,$savlinks);

    $contact_infos=$DB_site2->query_first("SELECT mail".$user['langue']." AS mail FROM admin_contact WHERE username='contact' AND site_code='".$user['site_code']."'");

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
                if(in_array($sync,array("clients mec","assoc mec")) && $syncobj->syncid==$rs_facture['userid']){
                    if($call->local->num==$user_info['telFR'] || $call->local->num==$user_info['telEN']){
                        $callid=$call->callid;
                        $suividivdisplay="block";
                        $suivititle=$call->dir==0?"Appel téléphone client":"Appel client reçu";
                        $suivimoyen=$call->dir==0?"tel_out":"tel_in";
                        $suivitype="client";
                        $suividest=$rs_facture['userid'];
                        break 3;
                    }
                }
            }
        }
    }
}
*/
    $choixsav_suivi="";
    $req = $DB_site2->query("SELECT id FROM sav WHERE facturid=$facturid");
    if($DB_site2->num_rows($req) > 0){
        $choixsav_suivi .="<br>Lier au SAV :<select name=\"choixsav_suivi\"><option value=\"0\"></option><option value=\"-1\">SANS</option>";
        while($sav = $DB_site2->fetch_array($req)){
            $choixsav_suivi .="<option value=\"".$sav['id']."\">".$sav['id']."</option>";
            $numsav = $sav['id'];
        }
        $choixsav_suivi.="</select>";
    }
    //Pour que les mails de type SAV puissent aussi être envoyés depuis le suivi commercial (et non plus uniquement depuis les SAV), il faut ajouter ces input pour que leur contenu soit correct
    ?>
    <input type="hidden" id="sav_civ" value="<?php echo $user['civilite']; ?>">
    <input type="hidden" id="sav_prenom" value="<?php echo $user['prenom']; ?>">
    <input type="hidden" id="sav_nom" value="<?php echo $user['nom']; ?>">
    <input type="hidden" id="num_sav" value="<?php echo $numsav; ?>">
    <input type="hidden" id="num_cmd" value="<?php echo $facturid; ?>">
    <?php

    print "<form action=\"facture.php?action=editer&facturid=$facturid\" method=\"post\">
<input type=\"hidden\" name=\"moyen\" value=\"".$suivimoyen."\">
<input type=\"hidden\" name=\"type\" value=\"".$suivitype."\">
<input type=\"hidden\" name=\"callid\" value=\"".$callid."\">
<input type=\"hidden\" name=\"destinataire\" value=\"".$suividest."\">

<a name=\"suivitop\" id=\"suivi_commercial\"></a>

<div class=\"tab_special_commer\">
<div style=\"text-align: center;font-weight:bold;\"> 
<h2>SUIVI COMMERCIAL <small>$user[langue]</small> <img src=\"images/drapeaux/$user[langue].gif\" width=\"20\"></h2>
</div>
<div class=\"clear\"></div>

<table id=\"suiviaction\">
<thead>
<tr><td class=\"suiviclient\">Facturation client</td><td class=\"suiviclient\">Livraison client</td><td class=\"suivipro\">Client</td><td class=\"suivipro\">Fournisseur</td><td class=\"suiviint\">Interne</td></tr>
</thead>
<tr>
<td class=\"suiviclient\"><span class=\"suivilink\" onclick=\"show_choix_suivi(this,'ficheinfo')\" style=\"color:blue;\"><img src=\"images/ring_phone_2.gif\"/>".$user['telephone']."</span>
<span class=\"addsuivi\">
<span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>
<span class=\"suivilink\" onclick=\"suiviauto('tel_out','client',$user[userid],'Appel téléphone client',".($usertel?"'$user[telephone]'":"undefined").");epigy_call('".$user['telephone']."','".$user_info['telFR']."');\">Click to call</span>
<span class=\"suivilink\" onclick=\"copy(this)\">Copier</span></span>";
    if($user['portable']!=""){
        print "<span class=\"suivilink\" onclick=\"show_choix_suivi(this,'ficheinfo')\" style=\"color:blue;\"><img src=\"images/ring_phone_2.gif\"/>".$user['portable']."</span>
    <span class=\"addsuivi\">
    <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>
    <span class=\"suivilink\" onclick=\"suiviauto('tel_out','client',$user[userid],'Appel téléphone client',".($usertel?"'$user[portable]'":"undefined").");epigy_call('".$user['portable']."','".$user_info['telFR']."');\">Click to call</span>
    <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span></span>";}
    print "</td>
    <td class=\"suiviclient\">
    <span class=\"suivilink\" onclick=\"show_choix_suivi(this,'ficheinfo')\" style=\"color:blue;\"><img src=\"images/ring_phone_2.gif\"/>".$rs_facture['telephone']."</span>
    <span class=\"addsuivi\">
    <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>
    <span class=\"suivilink\" onclick=\"suiviauto('tel_out','facture',$rs_facture[facturid],'Appel téléphone livraison',".($usertel?"'$rs_facture[telephone]'":"undefined").");epigy_call('".$rs_facture['telephone']."','".$user_info['telFR']."');\">Click to call</span>
    <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span></span>";
    if($rs_facture[portable]!=""){
        print "<span class=\"suivilink\" onclick=\"show_choix_suivi(this,'ficheinfo')\" style=\"color:blue;\"><img src=\"images/ring_phone_2.gif\"/>".$rs_facture[portable]."</span>
        <span class=\"addsuivi\">
        <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>
        <span class=\"suivilink\" onclick=\"suiviauto('tel_out','facture',$rs_facture[facturid],'Appel téléphone livraison',".($usertel?"'$rs_facture[portable]'":"undefined").");epigy_call('".$rs_facture[portable]."','".$user_info['telFR']."');\">Click to call</span>
        <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span></span>";}
    print "</td>
        <td class=\"suiviclient\" rowspan=\"3\"><input type=\"text\" onkeyup=\"srcfournisseur(this.value,'srcclient');\"></td>
        <td class=\"suivipro\" rowspan=\"3\"><input type=\"text\" onkeyup=\"srcfournisseur(this.value,'srcfournisseur');\"></td>
        <td class=\"suiviint\" rowspan=\"3\"><span class=\"suivilink\" onclick=\"suiviauto('','',0,'Suivi interne')\">Message interne</span></td>
        </tr>
        <tr>
        <td class=\"suiviclient\"><span class=\"suivilink\" onclick=\"mail_auto(undefined,'".$user['mail']."',undefined, undefined,undefined,undefined,undefined,true);mail_services('$user[langue]','$user[site_code]','adv,commercial,sav');mail_suivi('suivi_commande',$rs_facture[facturid]);suivi_hide();add_exp_list('contact','".$contact_infos['mail']."','".$user['site_code']."','".$user['langue']."');\">".$user['mail']."</span></td>
        <td class=\"suiviclient\"><span class=\"suivilink\" onclick=\"mail_auto(undefined,'".$rs_facture['mail']."',undefined,undefined,undefined,undefined,undefined,true);mail_services('$user[langue]','$user[site_code]','adv,commercial,sav');mail_suivi('suivi_commande',$rs_facture[facturid]);suivi_hide();add_exp_list('contact','".$contact_infos['mail']."','".$user['site_code']."','".$user['langue']."');\">".$rs_facture['mail']."</span></td>
        </tr>
        <tr>
        <tr><td><div id=\"srcfournisseur\"></div></td></tr>
        <tr><td><div id=\"srcclient\"></div></td></tr>
        <td class=\"suiviclient\"><span class=\"suivilink\" onclick=\"suiviauto('tel_in','client',$user[userid],'Appel client reçu');\">Appel reçu</span> <span class=\"suivilink\" onclick=\"suiviauto('mail_in','client',$user[userid],'Email client reçu');\">Email reçu</span></td>
        <td class=\"suiviclient\"><span class=\"suivilink\" onclick=\"suiviauto('tel_in','facture',$rs_facture[facturid],'Appel livraison reçu');\">Appel reçu</span> <span class=\"suivilink\" onclick=\"suiviauto('mail_in','facture',$user[userid],'Email client reçu');\">Email reçu</span></td>
        </tr>
        </table>

        <div id=\"suividiv\" style=\"display:$suividivdisplay;\">
        <br><div id=\"suivititle\">$suivititle</div>
        <textarea name=\"suivi\" id=\"suivi\" cols=\"60\" rows=\"8\"></textarea>
        $choixsav_suivi
        <br>Date de rappel : <input type=\"text\" name=\"rappel\" id=\"rappel\" onclick=\"calendrier_show(this.id,this.value,'planning',planningcb);\" readonly> pour <input type=\"text\" name=\"pour\" size=\"6\" readonly> <input type=\"hidden\" name=\"box\" value=\"1\">
        <br>Rappel <span class=\"suiviint\"><input type=\"radio\" name=\"rappeltype\" value=\"int\" checked> interne</span>
        &nbsp; <span class=\"suiviclient\"><input type=\"radio\" name=\"rappeltype\" value=\"client\"> client</span>
        &nbsp; <span class=\"suivipro\"><input type=\"radio\" name=\"rappeltype\" value=\"pro\"> fournisseur</span>
        <input type=\"hidden\" name=\"jour\" value=\"0\">
        <p><input type=\"submit\" value=\"Ajouter le suivi\"></p>
        </div>
        <!--Filtrer : 
        <span class=\"suiviint\"><input type=\"checkbox\" name=\"filterint\" onclick=\"filtersuivi();\" checked> Interne</span>
        <span class=\"suiviclient\"><input type=\"checkbox\" name=\"filterclient\" onclick=\"filtersuivi();\" checked> Client</span>
        <span class=\"suiviclient\"><input type=\"checkbox\" name=\"filtersav\" onclick=\"filtersuivi();\" checked> Sav</span>
        <span class=\"suivipro\"><input type=\"checkbox\" name=\"filterpro\" onclick=\"filtersuivi();\" checked> Fournisseur</span><br>

        --> ";

    // onglets filtre
    $tabfournmail = array();
    $onglets=$jsonglets="";
    $rmailfourn = $DB_site2->query("SELECT id,fournisseur,date FROM mail_outbox WHERE facturid = $facturid AND fournisseur != '' GROUP BY fournisseur ORDER BY date DESC");
    while ($mailfourn = $DB_site2->fetch_array($rmailfourn)) {
        $tabfournmail[] = $mailfourn['fournisseur'];
    }
    $rmailfourn = $DB_site2->query("SELECT id,fournisseur,date FROM mail_inbox WHERE facturid = $facturid AND fournisseur != '' GROUP BY fournisseur ORDER BY date DESC");
    while ($mailfourn = $DB_site2->fetch_array($rmailfourn)) {
        $tabfournmail[] = $mailfourn['fournisseur'];
    }

    foreach($tabfournmail as $fourn){
        $name_fourn = str_replace(array(" ","'"), "-", $fourn);
        $namefourn = str_replace("'", "-", $fourn);
        $onglets .="<div id=\"suivi_".$name_fourn."\" class=\"suivi_fourn\">$fourn</div>";
        $jsonglets .="\$jq('#suivi_".$name_fourn."').click(function(){
                \$jq('div').find('.suivi_onglet_hover').removeClass('suivi_onglet_hover');
                \$jq('".$name_fourn."').addClass('suivi_onglet_hover');

                \$jq.ajax('ajax/suivi_commercial.php?facturid=$facturid&\$savlinks=".implode(",",$savlinks)."&request=fourn&namefourn=$namefourn',   // request url
                {
                    success: function (data, status, xhr) {    // success callback function
                        \$jq('#tablesuivi').children().remove();
                        \$jq('#tablesuivi').append(data);
                        },
                        error: function (jqXhr, textStatus, errorMessage) { // error callback 
                            \$jq('#btn_refresh_suivi_commercial').text('Erreur !' + errorMessage);
                            },
                            beforeSend: function() {
                                \$jq('#btn_refresh_suivi_commercial').text('Chargement en cours ...');
                            }
                            });
                        });";
    }

    ?>
    <div id="suivi_tous">Tous</div>
    <div id="suivi_int">Interne</div>
    <div id="suivi_cde">Client</div>
    <div id="suivi_sav">SAV</div>
    <?=$onglets;?>
    <div id="suivi_info"><img src="../configurations/<?php echo $host; ?>/images/templates/icone_info.gif"></div>
    <div id="info_suivi_email">
        <p>A quoi correspondent les onglets ?</p>
        <p><strong>Tous :</strong> tous les mails et suivis internes correspondant à cette commande.</p>
        <p><strong>Suivi :</strong> les suivis internes concernant cette commande.</p>
        <p><strong>Commande :</strong> les mails concernant la commande (entrant et sortant) qui ont étés triés en tant que mails commande avec le code #CDE<?=$facturid; ?># + les mails automatiques de la commande.</p>
        <p><strong>SAV :</strong> les mails concernant le SAV de cette commande (entrant et sortant) qui on étés triés avec le code #SAV<?=$facturid; ?>-xxxx# xxxx est le numéro du sav.</p>
        <p><strong>Fournisseur : </strong> les mails de commande fournisseur, transporteur et installateur. Les correspondances avec le fournisseur concernant cette commande se trouveront dans l'onglet commande car il contient le code #CDE<?=$facturid; ?>#</p>

    </div>
    <!-- <div id="suivi_compta">Compta</div> -->

    <?php
    echo '<div style="margin-bottom:15px;">';
    foreach($savlinks as $savlink){
        print " <span style=\"background-color:".$linkcolor[$savlink].";".($savlink==$facturid?"color:red;":"")."\"><input type=\"checkbox\" name=\"savlinks\" value=\"$savlink\" onclick=\"filtersuivi();\" checked> ".$savlink."</span>";
    }
    echo '</div>';
    print "<table id=\"tablesuivi\">
                  <tr><td colspan=\"3\" align=\"center\">$suivi[0]</td></tr>
                  <tr><td width=\"50\"></td><td></td><td></td></tr>";
    /*
          if(isset($suivitd) && is_array($suivitd)){
            krsort($suivitd);
            foreach($suivitd as $tr) print $tr;
          }
          */

    // ici on affiche un lien de chargement
    //echo "<tr id=\"suivi_commercial_append_after\"><td colspan=\"5\" align=\"center\"><a id=\"btn_refresh_suivi_commercial\" style=\"background-color: #AA1147; border: medium none; border-radius: 10px; color: white; font-size: 10px; font-weight: bold; padding: 1px 10px 3px; cursor: pointer; margin: 15px; display: block; width: 50%;\">CHARGEMENT EN COURS DES 10 PREMIERS RESULATS</a></td></tr>";
    print "</table></div>
          </form>";
    ?>


    <script>
        $jq(document).ready(function () {
            $jq('#suivi_tous').addClass('suivi_onglet_hover');
            $jq.ajax('ajax/suivi_commercial.php?facturid=<?php echo $facturid; ?>&$savlinks=<?php echo implode(",",$savlinks); ?>&request=all&<?php echo http_build_query( array( "linkcolor" => $linkcolor ) ); ?>',   // request url
                {
                    success: function (data, status, xhr) {    // success callback function
                        $jq('#tablesuivi').children().remove();
                        $jq('#tablesuivi').append(data);
                    }
                });

        });

        $jq('#suivi_tous').click(function(){
            $jq("div").find('.suivi_onglet_hover').removeClass('suivi_onglet_hover');
            $jq('#suivi_tous').addClass('suivi_onglet_hover');

            $jq.ajax('ajax/suivi_commercial.php?facturid=<?php echo $facturid; ?>&$savlinks=<?php echo implode(",",$savlinks); ?>&request=all&<?php echo http_build_query( array( "linkcolor" => $linkcolor ) ); ?>',   // request url
                {
                    success: function (data, status, xhr) {    // success callback function

                        // $jq('#suivi_commercial_append_after').prevAll('table').remove();
                        $jq('#tablesuivi').children().remove();
                        $jq('#tablesuivi').append(data);

                        // $jq('#suivi_commercial_append_after').hide();
                    },
                    error: function (jqXhr, textStatus, errorMessage) { // error callback
                        $jq('#btn_refresh_suivi_commercial').text('Erreur !' + errorMessage);
                    },
                    beforeSend: function() {
                        $jq('#btn_refresh_suivi_commercial').text('Chargement en cours ...');
                    }
                });
        });

        $jq('#suivi_sav').click(function(){
            $jq("div").find('.suivi_onglet_hover').removeClass('suivi_onglet_hover');
            $jq('#suivi_sav').addClass('suivi_onglet_hover');

            $jq.ajax('ajax/suivi_commercial.php?facturid=<?php echo $facturid; ?>&$savlinks=<?php echo implode(",",$savlinks); ?>&request=sav&<?php echo http_build_query( array( "linkcolor" => $linkcolor ) ); ?>',   // request url
                {
                    success: function (data, status, xhr) {    // success callback function

                        // $jq('#suivi_commercial_append_after').prevAll('table').remove();
                        $jq('#tablesuivi').children().remove();
                        $jq('#tablesuivi').append(data);

                        // $jq('#suivi_commercial_append_after').hide();
                    },
                    error: function (jqXhr, textStatus, errorMessage) { // error callback
                        $jq('#btn_refresh_suivi_commercial').text('Erreur !' + errorMessage);
                    },
                    beforeSend: function() {
                        $jq('#btn_refresh_suivi_commercial').text('Chargement en cours ...');
                    }
                });
        });

        $jq('#suivi_fourn').click(function(){
            $jq("div").find('.suivi_onglet_hover').removeClass('suivi_onglet_hover');
            $jq('#suivi_fourn').addClass('suivi_onglet_hover');

            $jq.ajax('ajax/suivi_commercial.php?facturid=<?php echo $facturid; ?>&$savlinks=<?php echo implode(",",$savlinks); ?>&request=fourn&<?php echo http_build_query( array( "linkcolor" => $linkcolor ) ); ?>',   // request url
                {
                    success: function (data, status, xhr) {    // success callback function

                        // $jq('#suivi_commercial_append_after').prevAll('table').remove();
                        $jq('#tablesuivi').children().remove();
                        $jq('#tablesuivi').append(data);

                        // $jq('#suivi_commercial_append_after').hide();
                    },
                    error: function (jqXhr, textStatus, errorMessage) { // error callback
                        $jq('#btn_refresh_suivi_commercial').text('Erreur !' + errorMessage);
                    },
                    beforeSend: function() {
                        $jq('#btn_refresh_suivi_commercial').text('Chargement en cours ...');
                    }
                });
        });

        $jq('#suivi_int').click(function(){
            $jq("div").find('.suivi_onglet_hover').removeClass('suivi_onglet_hover');
            $jq('#suivi_int').addClass('suivi_onglet_hover');

            $jq.ajax('ajax/suivi_commercial.php?facturid=<?php echo $facturid; ?>&$savlinks=<?php echo implode(",",$savlinks); ?>&request=int&<?php echo http_build_query( array( "linkcolor" => $linkcolor ) ); ?>',   // request url
                {
                    success: function (data, status, xhr) {    // success callback function

                        // $jq('#suivi_commercial_append_after').prevAll('table').remove();
                        $jq('#tablesuivi').children().remove();
                        $jq('#tablesuivi').append(data);

                        // $jq('#suivi_commercial_append_after').hide();
                    },
                    error: function (jqXhr, textStatus, errorMessage) { // error callback
                        $jq('#btn_refresh_suivi_commercial').text('Erreur !' + errorMessage);
                    },
                    beforeSend: function() {
                        $jq('#btn_refresh_suivi_commercial').text('Chargement en cours ...');
                    }
                });
        });

        $jq('#suivi_cde').click(function(){
            $jq("div").find('.suivi_onglet_hover').removeClass('suivi_onglet_hover');
            $jq('#suivi_cde').addClass('suivi_onglet_hover');

            $jq.ajax('ajax/suivi_commercial.php?facturid=<?php echo $facturid; ?>&$savlinks=<?php echo implode(",",$savlinks); ?>&request=cde&<?php echo http_build_query( array( "linkcolor" => $linkcolor ) ); ?>',   // request url
                {
                    success: function (data, status, xhr) {    // success callback function

                        // $jq('#suivi_commercial_append_after').prevAll('table').remove();
                        $jq('#tablesuivi').children().remove();
                        $jq('#tablesuivi').append(data);

                        // $jq('#suivi_commercial_append_after').hide();
                    },
                    error: function (jqXhr, textStatus, errorMessage) { // error callback
                        $jq('#btn_refresh_suivi_commercial').text('Erreur !' + errorMessage);
                    },
                    beforeSend: function() {
                        $jq('#btn_refresh_suivi_commercial').text('Chargement en cours ...');
                    }
                });
        });

        <?php echo $jsonglets;?>

    </script>



    <?php
    $options="";
    $date_select =0;
    $sql = "SELECT MIN(date) FROM suivi_client WHERE userid=".$user['userid'];
    $req = $DB_site2->query_first($sql);
    $date_select = intval(date("Y",$req[0]));
    $sql = "SELECT MIN(date) FROM mail_inbox WHERE userid=".$user['userid'];
    $req = $DB_site2->query_first($sql);
    if($date_select < intval(date("Y",$req[0]))) $date_select = intval(date("Y",$req[0]));
    $req = $DB_site2->query_first($sql);
    $sql = "SELECT MIN(date) FROM mail_outbox WHERE userid=".$user['userid'];
    $req = $DB_site2->query_first($sql);
    $req = $DB_site2->query_first($sql);
    if($date_select < intval(date("Y",$req[0]))) $date_select = intval(date("Y",$req[0]));
    $req = $DB_site2->query_first($sql);

    $today = intval(date("Y"));
    for ($i=$today; $i > ($date_select-1) ; $i--) {
        $options .="<option value=\"$i\">$i</option>";
    }
    print "<div class=\"clear\"></div>
        <div class=\"tab_special_client\" id=\"suivi_client\">
        <div style=\"text-align: center;font-weight:bold;\">  
        <h2 style=\"width:100% !important\">SUIVI CLIENT</h2><select id=\"annee_suivi\" onchange=\"load_by_year(".$user['userid'].",'all',this.value)\">".$options."</select>
        <div id=\"sc_tous\">Tous</div><div id=\"sc_pjt\">Projet</div><div id=\"sc_int\">Compte</div><div id=\"sc_cde\">CDE</div><div id=\"sc_sav\">SAV</div><div id=\"sc_tri\">A trier</div>
        <div id=\"sc_info\"><img src=\"../configurations/$host/images/templates/icone_info.gif\"></div>
        <div id=\"info_sc_email\">
        <p>A quoi correspondent les onglets ?</p>
        <p><strong>Tous :</strong> tous les mails et suivis internes correspondant à cette commande.</p>
        <p><strong>Projet :</strong> les mails concernant les projets du client (entrant et sortant) qui on étés triés avec le code #PJTxxxxx-yyyyy# xxxxx est le numéro de projet et yyyyy est le numero de client. Les mails concernant un projet dont le client fait parti mais n'en est pas le client pricipal.</p>
        <p><strong>Compte :</strong> les informations concernant le compte du client.</p>
        <p><strong>CDE :</strong> les mails concernant les commandes (<strong>entrant seulement</strong>) qui ont étés triés en tant que mails de commande avec le code #CDExxxxxx# (xxxxx est le numero de commande) pour ce client.</p>
        <p><strong>SAV :</strong> les mails concernant les SAV des commandes du client (entrant et sortant) qui on étés triés avec le code #SAVyyyyyy-xxxx# (yyyyyy est le numero de commande et xxxx est le numéro du sav).</p>
        <p><strong>A trier : </strong> les mails (entrant et sortant) concernant ce client qui ne sont pas triés, c'est à dire qu'ils ne sont attribués à rien, ni à une commande, ni un projet, ni un SAV</p>

        </div>
        </div>
        <div class=\"clear\"></div>";

    $nb_projet_client1=$DB_site2->query_first("SELECT COUNT(*) FROM projet WHERE userid='".$user['userid']."' ");
    $nb_projet_client2=$DB_site2->query_first("SELECT COUNT(*) FROM projet AS p INNER JOIN projet_relation AS pr USING(projetid) WHERE pr.userid='".$user['userid']."' ");

    if($nb_projet_client1[0]>0 || $nb_projet_client2[0]>0){
        $script_call="";
        $script_funct="";
        print "<div class=\"filtreprojet\"> Filtrer :";

        $sql = "SELECT * FROM (
                (SELECT p.* FROM projet AS p WHERE userid='".$userid."' )
                    UNION
                    (SELECT p.* FROM projet AS p INNER JOIN projet_relation AS pr USING(projetid) WHERE pr.userid='".$user['userid']."' )
                    )
                    AS T ORDER BY T.libelle ASC ";

        $projet_clients=$DB_site2->query($sql);
        while($projet_client=$DB_site2->fetch_array($projet_clients)){
            print "<span class=\"suiviprojet\"><input type=\"checkbox\" id=\"filterprojet_".$projet_client['projetid']."\" onclick=\"filtrer_suivi_pjt(".$projet_client['projetid'].")\" checked> Projet '".$projet_client['libelle']."'</span> &nbsp; ";
        }
        print "</div>";
    }
    print "<div id=\"tab_suivi_client\"></div></div>";

    ?>
    <script>
        $jq(document).ready(function () {
            $jq("div").find('.filtreprojet').show();
            $jq('#sc_tous').addClass('suivi_onglet_hover_clt');
            loadsuivi('<?php echo  $user['userid']; ?>','all',0,'<?php echo $today; ?>');

            $jq('#sc_tous').click(function(){
                $jq("div").find('.filtreprojet').show();
                $jq("div").find('.suivi_onglet_hover_clt').removeClass('suivi_onglet_hover_clt');
                $jq('#sc_tous').addClass('suivi_onglet_hover_clt');
                loadsuivi('<?php echo $user['userid']; ?>','all',0);
            });

            $jq('#sc_int').click(function(){
                $jq("div").find('.filtreprojet').hide();
                $jq("div").find('.suivi_onglet_hover_clt').removeClass('suivi_onglet_hover_clt');
                $jq('#sc_int').addClass('suivi_onglet_hover_clt');

                loadsuivi('<?php echo $user['userid']; ?>','int',0);
            });

            $jq('#sc_tri').click(function(){
                $jq("div").find('.filtreprojet').hide();
                $jq("div").find('.suivi_onglet_hover_clt').removeClass('suivi_onglet_hover_clt');
                $jq('#sc_tri').addClass('suivi_onglet_hover_clt');

                loadsuivi('<?php echo $user['userid']; ?>','client',0);
            });

            $jq('#sc_pjt').click(function(){
                $jq("div").find('.filtreprojet').show();
                $jq("div").find('.suivi_onglet_hover_clt').removeClass('suivi_onglet_hover_clt');
                $jq('#sc_pjt').addClass('suivi_onglet_hover_clt');

                loadsuivi('<?php echo $user['userid']; ?>','pjt',0);
            });

            $jq('#sc_cde').click(function(){
                $jq("div").find('.filtreprojet').show();
                $jq("div").find('.suivi_onglet_hover_clt').removeClass('suivi_onglet_hover_clt');
                $jq('#sc_cde').addClass('suivi_onglet_hover_clt');

                loadsuivi('<?php echo $user['userid']; ?>','cde',0);
            });

            $jq('#sc_sav').click(function(){
                $jq("div").find('.filtreprojet').show();
                $jq("div").find('.suivi_onglet_hover_clt').removeClass('suivi_onglet_hover_clt');
                $jq('#sc_sav').addClass('suivi_onglet_hover_clt');

                loadsuivi('<?php echo $user['userid']; ?>','sav',0);
            });
        });

        lastreq="";
        function loadsuivi(userid,req,all,date){
            if(req==undefined) req=lastreq;
            if(all==undefined) all=0;
            if(date==undefined) date = $jq('#annee_suivi').val();
            $jq('#tab_suivi_client').html("<img src='../images/templates/wait-line.gif' style='display:block;margin:20px auto;'>");
            $jq.ajax('ajax/suivi_client.php?userid='+userid+'&request='+req+'&all='+all+"&date="+date,   // request url
                {
                    success: function (data, status, xhr) {    // success callback function
                        $jq('#tab_suivi_client').html(data);
                    }
                });
            lastreq=req;
        }
        function load_by_year(userid,request,date){
            $jq("div").find('.suivi_onglet_hover_clt').removeClass('suivi_onglet_hover_clt');
            $jq('#sc_tous').addClass('suivi_onglet_hover_clt');
            loadsuivi(userid,'all',0,date);
        }
    </script>


    <?php
    //TODO: create a TaskManager/TaskTemplate method

    $si_query = $DB_site2->query('
        SELECT `type`, `id`, `name`, `short_desc`, `excepted_date`
        FROM `suivi_interne`
        WHERE `link_type` LIKE "facture"
        AND `link` LIKE "'.mysqli_real_escape_string($DB_site2->link_id,$facturid).'"
        ');
    if($DB_site2->num_rows($si_query)>0){
        print "<div class=\"tab_special\">
      <h2>Suivi interne</h2>
      <div class=\"clear\"></div>";
        print '<table style="background:white;">';
        print '
      <thead>
      <tr>
      <td>ID</td><td>Nom</td><td>Résumé</td><td>Date de fin</td>
      </tr>
      </thead>';
        print '<tbody>';
        while ($si = $DB_site2->fetch_array($si_query))
            print '
    <tr class="suiviint">
    <td>'.($si['type']=='suivi'?'SI':'SB').'#'.$si['id'].'</td>
    <td>'.$si['name'].'</td>
    <td>
    <a href="task.php?a=showTask&amp;task='
                .$si['id'].'&amp;'.$si['type'].'">'
                .$si['short_desc'].'</a>
    </td>
    <td>'.$si['excepted_date'].'</td>
    </tr>';
        print '</tbody>';
        print '</table>
    </div>';
    }

// sav.php ancien traitement, conservé pour compatibilité, sav2.php traitement normal
    $vsav="sav.php";

    if($_GET['savid']==0 || $_GET['savid']>404) $vsav="sav2.php";
    if(isset($_GET['savid']) && in_array($rs_facture['valide'],array(1,2,5,7,8))) {
        $savid = $_GET['savid'];
        include($vsav);
    }
    echo "<br><br><a href=\"facture.php\" target=\"_self\">Retour</a>";
}

if(isset($_GET['action']) && ($_GET['action'] == "creer" || $_GET['action'] == "modifier")) include(BACK_PATH."/facture_create.php");

include(BACK_PATH."/footer.php");

function envoicolor($nbprod,$totalprod){
    if($nbprod==0 || $nbprod=="") return "red";
    if($nbprod<$totalprod) return "#FFAA00";
    if($nbprod>=$totalprod) return "green";
    return "red";
}

function calcdates($efl){
    global $today;
    global $advdate1,$advdate2,$advdate3;

    if($efl==false) return "";
    foreach($efl as $lfid=>$dates){

        if($dates['in']['fourn']>0)
            $advdate1.="<span".($dates['in']['fourn']<=$today?" style=\"color:red;\"":"").">".date("d/m/Y",$dates['in']['fourn'])."</span><br>";
        else
            $advdate1.="-<br>";

        if(intval($dates['in']['sls'])>0){
            $advdate2.="<span".($dates['in']['sls']<=$today?" style=\"color:red;\"":"").">".date("d/m/Y",$dates['in']['sls'])."</span><br>";
        }elseif(intval($dates['in']['trspt'])>intval($dates['in']['fourn'])){
            $advdate2.="<span".($dates['in']['trspt']<=$today?" style=\"color:red;\"":"").">".date("d/m/Y",$dates['in']['trspt'])."</span><br>";
        }

        if(intval($dates['out']['sls'])>0){
            $advdate3.="<span".($dates['out']['sls']<=$today?" style=\"color:red;\"":"").">".date("d/m/Y",$dates['out']['sls'])."</span><br>";
        }elseif(intval($dates['out']['trspt'])>0){
            $advdate3.="<span".($dates['out']['trspt']<=$today?" style=\"color:red;\"":"").">".date("d/m/Y",$dates['out']['trspt'])."</span><br>";
        }elseif(intval($dates['out']['fourn'])>0){
            $advdate3.="<span".($dates['out']['fourn']<=$today?" style=\"color:red;\"":"").">".date("d/m/Y",$dates['out']['fourn'])."</span><br>";
        }else{
            $advdate3.="-<br>";
        }
    }
}


function formatmail_facture($mail,$sens){
    global $DB_site2;
    global $facturid;
    global $linkcolor;

    $tddest="";
    $type="int";
    $contact=array();
    $source=$mail['to'];
    if($sens=="in") $source=$mail['from'];
    $mails=preg_split("[,|;]",$source);
    foreach($mails as $mailto){
        $user=$DB_site2->query_first("SELECT userid,mail,telephone,portable,nom,prenom,raisonsociale AS societe FROM utilisateur WHERE mail<>'' AND mail='".addslashes($mailto)."' AND code411=''");
        if($user=="") $user=$DB_site2->query_first("SELECT userid,mail,telephone,'' AS portable,nom,prenom,raisonsociale AS societe FROM facture WHERE mail='".addslashes($mailto)."'");
        $contact=$DB_site2->query_first("SELECT * FROM fournisseur WHERE mail<>'' AND mail='".addslashes($mailto)."'");
        if($mailto==$contact['mail']){
            $tddest.="<div class=\"suividest\">".$contact['societe']." ".$contact['prenom']." ".$contact['nom']."
          <br><span class=\"suivilink\" onclick=\"mail_auto(undefined,'".$contact['mail']."');mail_suivi('suivi_commande','".$mail['suiviid']."')\">".$contact['mail']."</span>
          <br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','pro',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].",".($usertel?"'$contact[telephone]'":"undefined").")');self.location.hash='#suivitop';\">".epigy($contact['telephone'],$contact['langue'])."</span>";
            if($contact['portable']!="") $tddest.="<br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','pro',".$contact['id'].",'Appel téléphone ".$contact['societe']." (".$contact['nom']." ".$contact['prenom'].")',".($usertel?"'$contact[portable]'":"undefined").");self.location.hash='#suivitop';\">".epigy($contact['portable'],$contact['langue'])."</span>";
            $tddest.="</div>";
            if($type=="int") $type="pro";
        }elseif($mailto==$user['mail']){
            $tddest.="<div class=\"suividest\">".$user['societe']." ".$user['prenom']." ".$user['nom']."
          <br><span class=\"suivilink\" onclick=\"mail_auto(undefined,'".$user['mail']."');mail_services('$user[langue]','$user[site_code]','adv,commercial,sav');mail_suivi('suivi_commande','".$mail['suiviid']."');\">".$user['mail']."</span>
          <br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','client',".$user['userid'].",'Appel téléphone ".$user['societe']." (".$user['nom']." ".$user['prenom'].")',".($usertel?"'$user[telephone]'":"undefined").");self.location.hash='#suivitop';\">".epigy($user['telephone'],$contact['langue'])."</span>";
            if($user['portable']!="") $tddest.="<br><span class=\"suivilink\" onclick=\"suiviauto('tel_out','client',".$user['userid'].",'Appel téléphone ".$user['societe']." (".$user['nom']." ".$user['prenom'].")',".($usertel?"'$user[portable]'":"undefined").");self.location.hash='#suivitop';\">".epigy($user['portable'],$contact['langue'])."</span>";
            $tddest.="</div>";
            $type="client";
        }else{
            $tddest.="<div class=\"suividest\">".$mailto."</div>";
            $type="int";
        }
    }

    $sens=="in"?$icon="get_mail.png":$icon="send_mail.png";
    return "<tr id=\"".$type."-".$mail['id']."-".$mail['suiviid']."\" class=\"suivi$type\"><td><span style=\"background-color:".$linkcolor[$mail['suiviid']].";".($mail['suiviid']==$facturid?"color:red;":"")."\">".$mail['suiviid']."</span><br><img src=\"images/$icon\"><br>".date("d/m/Y H:i",$mail['date'])."</td><td>".$mail['utilisateur']."</td><td>".$tddest."</td><td class=\"suivitxt\">".$mail['subject']."<br>".formatsuivi($mail['body']).($mail['pj']!=""?"<br>PJ : ".$mail['pj']:"")."</td><td></td></tr>";
}

function savlinkids($parent,$links){
    $return=array($parent);
    IF($links==false) return $return;
    foreach($links as $link=>$a){
        $return=array_merge($return,savlinkids($link,$a));
    }

    return $return;
}


/********************************************** TSI ****************************************/
// Permet de tracer l'arbre / tree view du SAV
// Formulaires: newsav

function drawchild($linkid,$links){

    global $facturid;
    global $DB_site2;
    global $linkcolor;

    $facturIdEdit = $_GET['facturid'];

    $sql = "SELECT u.raisonsociale AS uraisonsociale, u.nom AS unom, u.prenom AS uprenom, f.facturid, f.numerofacture, f.raisonsociale, f.nom, f.prenom, f.heurefacture, f.prixport, f.valide, f.montantcadeau";
    $sql .= " FROM facture f";
    $sql .= " INNER JOIN utilisateur u USING(userid)";
    $sql .= " WHERE f.facturid = ".$linkid;
    $facture = $DB_site2->query_first($sql);
    if(!$facture){ //Si ce n'est pas une commande "classique", mais une commande de stock

        $sql = "SELECT sc.date";
        $sql .= " FROM stock_cmd sc";
        $sql .= " WHERE id = ".$linkid;
        $stock = $DB_site2->query_first($sql);

        $data = ""
            . "<div id=\"popup".$linkid."\" class=\"popup\">"
            . "<strong>Commande de stock n°".$linkid."</strong>"
            . "<br>du ".date("d/m/Y",$stock['date'])
            . "<table>";

    } else {

        $status = array(
            0 => "Non traitée",
            1 => "<span style=\"color:blue;font-weight:bold;\">Validée</span>",
            2 => "<span style=\"color:red;font-weight:bold;\">Refus commerçant</span>",
            3 => "<span style=\"color:red;font-weight:bold;\">Refus CB</span>",
            4 => "<span style=\"font-weight:bold;\">Abandon</span>",
            5 => "<span style=\"color:green;font-weight:bold;\">Expédiée</span>",
            7 => "<span style=\"color:blue;font-weight:bold;\">Validée en attente de paiement</span>",
            8 => "<span style=\"color:blue;font-weight:bold;\">Expédiée en attente de paiement</span>"
        );

        $data = ""
            . "<div id=\"popup".$linkid."\" class=\"popup\">"
            . date("d/m/Y",$facture['heurefacture'])." ".($facture['numerofacture']>556500?$facture['numerofacture']:"")." ".$status[$facture['valide']]
            . "<br>Facturation : ".$facture['uraisonsociale']." ".$facture['uprenom']." ".$facture['unom']." &nbsp; Livraison : ".$facture['raisonsociale']." ".$facture['prenom']." ".$facture['nom']
            . "<table>";

        $total = 0;
        $sql = "SELECT *";
        $sql .= " FROM lignefacture";
        $sql .= " WHERE factureid = ".$linkid;
        $lignes = $DB_site2->query($sql);
        while($ligne = $DB_site2->fetch_array($lignes)){

            $data .= ""
                . "<tr>"
                . "<td>".$ligne['qte']."</td>"
                . "<td>".$ligne['artcode']."</td>"
                . "<td>".$ligne['libelle']."</td>"
                . "<td>".number_format($ligne['prix'],2)."</td>"
                . "</tr>";

            $total += ($ligne['qte'] * $ligne['prix']);

        }

        $total += $facture['prixport'];
        $total -= $facture['montantcadeau'];

        if($facture['prixport'] > 0){
            $data .= ""
                . "<tr>"
                . "<td colspan=\"3\">Port</td>"
                . "<td>".number_format($facture['prixport'],2)."</td>"
                . "</tr>";
        }

        if($facture['montantcadeau'] > 0){
            $data .= ""
                . "<tr>"
                . "<td colspan=\"3\">Code promo</td>"
                . "<td>".number_format($facture['montantcadeau'],2)."</td>"
                . "</tr>";
        }

        $data .= ""
            . "<tr>"
            . "<td colspan=\"3\">Total TTC</td>"
            . "<td><b>".number_format($total,2)."</b></td>"
            . "</tr>";
    }

    $data .= ""
        . "</table>"
        . "</div>";

    //************************************** TSI *************************************************************
    // Modification de l'affichage du numero de facture en cours dans le treeview sav
    // - augmenter la taille de la police
    // - mettre une bordure

    $sizeTxt="";
    if ($facturIdEdit==$linkid)
    {
        //print_r($facturIdEdit." Got link2 : ".$linkid);
        $sizeTxt = "font-size:15px;font-weight:bold;border:1px solid red;padding:2px;";
    }

    if($links != false){

        if(!$facture){

            //if ($facturIdEdit==$linkid) print_r($facturIdEdit." Got link1 : ".$linkid);

            $return = ""
                . "<table class=\"savtree\">"
                . "<tr>"
                . "<td colspan=\"".count($links)."\" class=\"savtreetd\" onmouseover=\"document.getElementById('popup".$linkid."').style.visibility='visible';\" onmouseout=\"document.getElementById('popup".$linkid."').style.visibility='hidden';\">"
                . "<a href=\"stock.php?action=cmd&stock_cmd_id=".$linkid."\" style=\"background-color:".$linkcolor[$linkid].";".($linkid==$facturid?"color:red;".$sizeTxt:"")."\">".$linkid."</a>"
                . $data
                . "</td>"
                . "</tr>";

        } else {

            $nbr_commence = 0;
            $nbr_encours = 0;
            $nbr_finit = 0;
            $req = $DB_site2->query("SELECT * FROM sav WHERE facturid=".$facture["facturid"]);
            while($sav_etat=$DB_site2->fetch_array($req))
            {
                $sav = $sav_etat;
                if ($sav['fin'] != '0')
                {
                    ++$nbr_finit;
                }
                else if ($sav['demande_cloture'] != "0000-00-00 00:00:00")
                {
                    ++$nbr_encours;
                }
                else
                {
                    ++$nbr_commence;
                }
            }

            $return = ""
                . "<table class=\"savtree\">"
                . "<tr>"
                . "<td colspan=\"".count($links)."\" class=\"savtreetd\" onmouseover=\"document.getElementById('popup$linkid').style.visibility='visible';\" onmouseout=\"document.getElementById('popup$linkid').style.visibility='hidden';\">"
                . "<a href=\"facture.php?action=editer&facturid=".$linkid."\" style=\"background-color:".$linkcolor[$linkid].";".($linkid==$facturid?"color:red;".$sizeTxt:"")."\">".$linkid."</a>"
                . $data." ( <span style='color:green';>".$nbr_finit."</span>/<span style='color:orange';>".$nbr_encours."</span>/<span style='color:red';>".$nbr_commence."</span>)"
                . "</td>"
                . "</tr>";

        }

        $linksnb = count($links);

        if($linksnb > 1){
            $return .= ""
                . "<tr>"
                . "<td colspan=\"$linksnb\" class=\"savtreetd\"></td>"
                . "</tr>"
                . "<tr>";

            for($i=0;$i<$linksnb;$i++){

                $class = "treemid";
                if($i==0) $class = "treeleft";
                if($i==$linksnb-1) $class = "treeright";
                $return .= "<td class=\"$class\"><div></div></td>";
            }
            $return.="</tr>";
        }

        $return.="<tr>";

        foreach($links as $linkid2=>$links2){
            $return .= "<td class=\"savtreetd\">".drawchild($linkid2,$links2)."</td>";
        }

        $return .= "</tr>";
        $return .= "</table>";

    } else
    {
        $nbr_commence = 0;
        $nbr_encours = 0;
        $nbr_finit = 0;
        $req = $DB_site2->query("SELECT * FROM sav WHERE facturid=".$facture["facturid"]);
        while($sav_etat=$DB_site2->fetch_array($req))
        {
            $sav = $sav_etat;
            if ($sav['fin'] != '0')
            {
                ++$nbr_finit;
            }
            else if ($sav['demande_cloture'] != "0000-00-00 00:00:00")
            {
                ++$nbr_encours;
            }
            else
            {
                ++$nbr_commence;
            }
        }

        //if ($facturIdEdit==$linkid) print_r($facturIdEdit." Got link : ".$linkid);
        $return = ""
            . "<div id=\"savtreeend\" onmouseover=\"document.getElementById('popup$linkid').style.visibility='visible';\" onmouseout=\"document.getElementById('popup$linkid').style.visibility='hidden';\"><a href=\"facture.php?action=editer&facturid=$linkid\" style=\"background-color:".$linkcolor[$linkid].";".($linkid==$facturid?"color:red;".$sizeTxt:"")."\">".$linkid."</a>".$data." ( <span style='color:green';>".$nbr_finit."</span>/<span style='color:orange';>".$nbr_encours."</span>/<span style='color:red';>".$nbr_commence."</span>)</div>";

    }

    return $return;
}


function getparentsav($linkid){
    global $DB_site2;

    $savfrom=$DB_site2->query_first("SELECT provenanceid FROM facture WHERE provenance IN ('sav','avoir') AND facturid=".intval($linkid));
    if($savfrom[0]!="")
        return getparentsav($savfrom[0]);
    else
        return $linkid;
}

function savlink($linkid){
    global $DB_site2;

    $return=array();

    if($sens=="to" || is_null($sens)){
        $r=$DB_site2->query("SELECT facturid FROM facture WHERE provenance IN ('sav','avoir') AND provenanceid=".intval($linkid));

        while($savto=$DB_site2->fetch_array($r)){
            $return[$savto[0]]=savlink($savto[0]);
            //      $links=savlink($savto[0],"to");print "<br>[$sens $linkid]";var_dump($links);
            //      foreach($links as $linkid2=>$a){ $return[$linkid2]['to']=$a['to'];$return[$linkid2]['from']=$linkid;}
        }
        if($DB_site2->num_rows($r)==0) $return=false;

    }

    return $return;
}

function debmail($facture) {

    global $DB_site2;
    global $user_info;

    $mail = new email();

    $mail->destinataire = defined('MAIL_DIRECTION') ? MAIL_DIRECTION : 'pr@mon-espace-cuisson.com';
    //$mail->destinataire = defined('MAIL_COMPTABLE') ? MAIL_COMPTABLE : 'j3mexp@gmail.com';
    $mail->to           = ["pr@mon-espace-cuisson.com", 'webmaster@ma-cave-a-vin.fr', 'cecile@mon-espace-cuisson.com'];
    $mail->expediteur   = defined('MAIL_COMMERCE') ? MAIL_COMMERCE : 'contact@mon-espace-cuisson.com';
    $mail->expname      = NOM_CONTACT;
    $mail->subject      = "DEB à faire #CDE".$facture->facturid."#";
    $mail->body         = ""
        . "<table>"
        . "<tr>"
        . "<td>Commande n°</td>"
        . "<td>".$facture->facturid."</td>"
        . "</tr>"
        . "<tr>"
        . "<td>Facture n°</td>"
        . "<td>".$num_facture."</td>"
        . "</tr>"
        . "<tr>"
        . "<td>TVA intracommunautaire</td>"
        . "<td>".$facture->utilisateur->tvaintracom."</td>"
        . "</tr>"
        . "<tr>"
        . "<td>Montant HT</td>"
        . "<td>".number_format($facture->totalht,2,',','')."</td>"
        . "</tr>"
        . "<tr>"
        . "<td>Montant TTC</td>"
        . "<td>".number_format($facture->totalttc,2,',','')."</td>"
        . "</tr>"
        . "<tr>"
        . "<td>Pays facturation</td>"
        . "<td>".$facture->utilisateur->pays."</td>"
        . "</tr>"
        . "</table>";

    $mail->sendmail();

    $sql = "INSERT INTO suivi_commande SET";
    $sql .= " facturid    = ".$facture->facturid."";
    $sql .= ", date       = ".time()."";
    $sql .= ", commercial = '".$user_info['username']."'";
    $sql .= ", suivi      = '".$mail->body."'";
    $DB_site2->query($sql);
}

function mail_livraison($mailname,$efid){
    include_once("adv/adv.class.php");

    global $DB_site2;
    global $user_info;
    global $rootpath;

    if(isset($_POST['facturid'])) $_GET['facturid']=$_POST['facturid'];
    $facture = new facture($DB_site2,$_GET['facturid']);

    $template = new template($facture->utilisateur->langue,$DB_site2);

    $template->changesite($facture->site_code,$facture->utilisateur->langue);
    $template->pro = ($facture->utilisateur->raisonsociale != "" && $template->site_code() == "base");

    $vars=array(
        'facturid'=>$facture->facturid,
        'nom'=>$facture->nom,
        'prenom'=>$facture->prenom,
        'raisonsociale'=>$facture->raisonsociale
    );

    if(!is_null($efid) && !empty($efid)){
        $ef=$DB_site2->query_first("SELECT destinataire,trackid FROM envoi_fournisseur WHERE id=".intval($efid));
        $template->parts["trackid"]=$vars["trackid"]=$ef['trackid'];

        $vars["trackurl"]=$template->translate("/site/[site_code]/url")."/suivi_commande.php?facturid=".$facture->facturid."&suiviid=".$facture->suiviId();
        if($template->pro) $vars["trackurl"]=str_replace("www","pro",$vars['trackurl']);
        $vars["transporteur"]=$ef['destinataire'];
        $adv=new adv($DB_site2);
        $adv->load_from_efid($efid);
        $trspt_infos=$DB_site2->query_first("SELECT * FROM fournisseur_livraison WHERE societe='".mysqli_real_escape_string($DB_site2->link_id,$ef['destinataire'])."' AND trspttype IN ('','".$adv->fields['trspttype']."')");
        $vars['infos']=$trspt_infos['infos'.$facture->utilisateur->langue];
        $vars['contact']=$template->evalvartemplate($trspt_infos['contact'.$facture->utilisateur->langue],"contact");

        if($mailname=='transporteur_livraison'){
            $mailname = mysqli_real_escape_string($DB_site2->link_id,$ef['destinataire']);
        }
        elseif($mailname=='fourn'){
            $mailname = mysqli_real_escape_string($DB_site2->link_id,'Transporteur fournisseur');
        }
        elseif($mailname=='multi'){
            $mailname = mysqli_real_escape_string($DB_site2->link_id,'Multi transport');
        }
        elseif($mailname=='Retrait par le client'){
            $mailname = mysqli_real_escape_string($DB_site2->link_id,'Retrait par le client');
        }
    }

    //si pas de commandes passées :
    if($mailname=='multi'){
        $mailname = mysqli_real_escape_string($DB_site2->link_id,'Multi transport');
    }
    elseif($mailname=='Retrait par le client'){
        $mailname = mysqli_real_escape_string($DB_site2->link_id,'Retrait par le client');
    }

    $ec = new email_client($DB_site2,$template,null,$mailname,$vars);
    if($ec->body=="") return false;

    $mail = new email();
    $mail->destinataire = $facture->mail;
    $mail->destname     = $facture->prenom." ".$facture->nom;
    $mail->expediteur   = $template->translate("/site/base/mail_expediteur");
    $mail->expname      = $template->translate("/site/nom");
    $mail->to			= ["pr@mon-espace-cuisson.com", 'webmaster@ma-cave-a-vin.fr'];
    $mail->copymode     = "bcc";
    $mail->subject      = $ec->subject;
    $mail->body         = $ec->body;
    $file=($template->langue=="FR" ? "consignes_reception" : "delivery_note").".pdf";
    $mail->attachfile($rootpath."fichiers/".$file,$file,"application/pdf");

    $mail->sendmail();

    $sql = "INSERT INTO mail_outbox SET";
    $sql .= " date         = ".time()."";
    $sql .= ", utilisateur = '".$user_info['username']."'";
    $sql .= ", `to`        = '".$mail->destinataire."'";
    $sql .= ", `from`        = '".$mail->expediteur."'";
    $sql .= ", subject     = '".addslashes($mail->subject)."'";
    $sql .= ", body        = '".addslashes($mail->body)."'";
    $sql .= ", facturid    = '".$facture->facturid."'";
    $sql .= ", userid      = '".$facture->utilisateur->userid."'";
    $sql .= ", uniqueid    = '".$mail->uniqueid."'";
    $sql .= ", compta      = 'non'";
    $sql .= ", auto        = 0";
    $DB_site2->query($sql);
}

function aide_fiscale(&$mail,$facture){
    global $DB_site2;
    global $rootpath;

    $return=false;
    if(in_array($facture->utilisateur->paysid,array(57,181,111)) && $facture->utilisateur->raisonsociale!="" && $facture->provenance!="sav"){
        foreach($facture->lignes as $ligne){
            $article=$DB_site2->query_first("SELECT gaz FROM article INNER JOIN gaz_aide ON(gaz=path) WHERE artid=".$ligne->artid);
            if($article['gaz']!=""){
                $return=true;
                break;
            }
        }
    }

    if($return){
        $mail->attachfile($rootpath."admin/pdf/aide_fiscale.pdf","deduction_fiscale.pdf","application/pdf");
    }

    return $return;
}


?>