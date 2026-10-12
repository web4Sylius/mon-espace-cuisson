<?php
error_reporting(7);

session_start();
extract($_REQUEST);

include_once("global_include.php");



if(ESPACE == 'prod'){

    $sitetest = false;

    //Vérification de l'url
    $sph = explode(".", $_SERVER['HTTP_HOST']);
    if($sph[0] != "www" && $sph[0] != "pro" && $sph[0]!="m" && $sph[1] != "localhost" && $sph[0] != "dedie" && $sph[0] != "dediepro" && $sph[0] != "fusion" && $sph[0] != "fusionmcav"){
      
         if(count($sph) == 2 || $sph[2] == "uk") array_unshift($sph, "www");
         
         $sph[0] = "www";
         header("Status: 301 Moved Permanently", false, 301);
         header("Location: http://".implode(".",$sph).$_SERVER['REQUEST_URI']);
         exit(); 
    }
}else{
    $sitetest = true ;
}

if(!function_exists('nice')){
     function nice($var, $color = 'white'){
          echo "<div style=\"color:".$color.";\">";
          echo "<p style=\"color:red; font-weight:bold;\">Contenu de la variable :</p>";
          if(is_array($var) || is_object($var)){
                    echo "<pre>";
                    print_r($var);
                    echo "</pre>";   
          } else {
                    var_dump($var);
          }
          echo "</div>";
     }
}

if(!function_exists('nicex')){
     function nicex($var, $color = 'white'){
          nice($var, $color);
          exit();
     }
}

$aAdminIPS = array();
if(ESPACE == 'prod'){
    $aAdminIPS[] = '80.12.82.212';
    $aAdminIPS[] = '213.44.169.37';
}

$langue="FR";
$template = new template($langue,$DB_site);


$sitepro  = $template->pro;
//IP autorisées à voir le site UK
$allowuk = array("80.12.82.212","88.184.216.175","46.105.98.28","91.212.42.11","127.0.0.1");

$template->parts["host"]     = $template->translate("/site/base/url");
$template->parts["host_path"]= $host;
$template->parts["site"]     = $template->translate("/site/nom");
$template->parts["logosite"] = "<a href=\"/\" title=\"".$template->translate("/site/nom")."\"><img class=\"img img-responsive\" src=\"/images/templates/".$template->site_code()."/".$template->langue."/logo".($sitepro?"-pro":"").".jpg\" alt=\"".$template->translate("/site/alt_logo","F")."\"></a>";
$template->parts["logoresp"] = "<a href=\"/\" title=\"".$template->translate("/site/nom")."\"><img class=\"img img-responsive\" src=\"/images/templates/".$template->site_code()."/".$template->langue."/logo_resp".($sitepro?"_pro":"").".png\" alt=\"".$template->translate("/site/alt_logo","F")."\" width=\"150\" height=\"50\"></a>";

$dedie = strpos($_SERVER['HTTP_HOST'], "dedie");

if($dedie !== false || $sitetest){
    $template->parts["follow"]='<meta NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">';
    
}else {
    $template->parts["follow"]='<meta NAME="ROBOTS" CONTENT="INDEX, FOLLOW, ALL">';
}

if($sitepro){
        $template->parts["logopro"]="<img src=\"/images/templates/".$template->site_code()."/".$template->langue."/pro.png\">";
        //$template->evaltemplate("ProBtnPart","probtn");
        $template->parts["prostyle"]="pro";
        $template->part['logoresppro'] = "_pro";
}else{
        //$template->evaltemplate("ProBtnPro","probtn");
}

//$template->parts["analytics_tracker"]=$template->translate("/site/[site_code]/analytics_tracker");
$template->parts["year"]=date("Y");
$template->parts["canonical"]="https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$template->parts["contact_telephone"]=$template->translate("/site/[site_code]/telephone");
$template->parts["telephone_link"]=preg_replace("/[\. ]+/","",$template->parts["contact_telephone"]);

 // ****** $template->host = HTTP_HOST et $host = $host_conf->path

if($template->common_page()){
    $template->parts["canonical"]="https://".$template->host.$_SERVER['REQUEST_URI'];
}

$vfarticle=$DB_site->query_first("SELECT * FROM vente_flash vf WHERE (".time()." BETWEEN vf.du AND vf.au) AND site_code='".$template->site_code()."'");
if($vfarticle!="" && !$sitepro){
    $template->evaltemplate("BarreFixeVF","barre_vf");
    header("Cache-Control: no-cache, must-revalidate");
}
$vparticle=$DB_site->query_first("SELECT * FROM vente_privee vp WHERE (".time()." BETWEEN vp.du AND vp.au) AND site_code='".$template->site_code()."'");
if($vparticle!="" && !$sitepro){
    $template->evaltemplate("BarreFixeVP","barre_vp");
    header("Cache-Control: no-cache, must-revalidate");
}
//Génération du drapeau de langue.
if($sitepro)
    $pro ='pro.';
else
    $pro ='www.';

$sites=$template->sites();

foreach((array) $sites[$template->site_code()]["hosts"] as $url=>$deflangue){
    if($deflangue != $template->langue){
        $testValue="<a href=\"https://".$pro.$url."\" class=\"flaglink\"><img src=\"/images/langues/".$deflangue.".gif\" alt=\"".$url."\"><span>".$deflangue."</span></a>"; 
        $template->parts["flaglink"].="<a href=\"https://".$pro.$url."\" target=\"_blank\" class=\"flaglink\"><img src=\"/images/langues/".$deflangue.".gif\" alt=\"".$url."\" width=\"26\" height=\"19\"></a>";
        break;
    }
}


$template->parts["mouseovercompte"]=<<<HTML
onmouseenter="popup_header('popup_compte',true)" onmouseleave="popup_header('popup_compte',false)"
HTML;


$template->parts["jsmodif"]=filemtime(__DIR__."/javascripts/cache.js");
//$template->parts["jsmodif"]=filemtime(__DIR__."\\javascripts\\cache.js");

include("ccstat.php");


if(isset($_COOKIE['cookieconsent'])){
    $cookieconsent=true;
}else{
    $cookieconsent=false;
}

$template->parts["modalclass"] = "modal c_modal";
$template->evaltemplate("Rgpd", "modal");
$template->evaltemplate("Overlay","modaltype");

$url_from=explode(".", $_SERVER['HTTP_HOST']);



if (empty($action) === false && $action=="loggout"){
    setcookie("userid","",time()+3600000, "/");
    setcookie("password","",time()+3600000, "/");
    setcookie("prenom","",time()+3600000, "/");
    setcookie("userid","",time()+3600000, "/" , $template->host);
    setcookie("password","",time()+3600000, "/", $template->host);
    setcookie("prenom","",time()+3600000, "/", $template->host);
    setcookie("s","",time()+3600000, "/", $template->host);
    setcookie("userid","",time()+3600000, "/", ".".prev($url_from).".".end($url_from));
    setcookie("prenom","",time()+3600000, "/", ".".prev($url_from).".".end($url_from));
    setcookie("s","",time()+3600000, "/", ".".prev($url_from).".".end($url_from));
    setcookie("password","",time()+3600000, "/", ".".prev($url_from).".".end($url_from));
    header("Location: /");
}

if($_POST['action']=="clientnavig"){
    //on supprime les cookies avant d'en recréer. 
    setcookie("userid", '', time() - 3600,'/');
    unset($_COOKIE['userid']);
    setcookie("password", "", time() - 3600 , '/');
    unset($_COOKIE['password']);
    setcookie("prenom", "", time() - 3600 , '/');
    unset($_COOKIE['prenom']);

    setcookie("userid",$_POST['userid'],time()+3600,"/", $template->host);
    $_COOKIE[userid]=$_POST['userid'];
    setcookie("password",$_POST[password],time()+3600,"/", $template->host);
    $_COOKIE['password']=$_POST['password'];
     setcookie("prenom",$_POST[prenom],time()+3600,"/", $template->host);
    $_COOKIE['prenom']=$_POST['prenom'];
    setcookie("admin_username",$_POST['admin'],time()+3600,"/", $template->host);
    $_COOKIE['admin_username']=$_POST['admin'];
}

$horaires=new horaire($DB_site,$template->site_code());
if(!isset($_COOKIE['aide']) && $horaires->ouvert()){
    $dureeattente=(time()-$enterdate>120?10:120);
    $template->parts["js_aide"]="autoaide(".$dureeattente.");";
}

$template->parts['display_register'] = 'none';
$template->parts['hide_register']    = 'block';

// login et inscription
if(isset($_POST['action']) && $_POST['action'] == "logging"){

    $template->parts["errormessage"] ="";
    if(isset($_POST['maillog'])) $_POST['mail'] = $_POST['maillog'];

    $mailtva = false;
    $pass    = $_POST['pass_logging'];
    $email   = $_POST['mail_logging'];
    $news_mail = $_POST['news_mail'];
    $news_sms = $_POST['news_sms'];
    $conseils = ($_POST['conseils']==1?"oui":"non");
    $ispro = "";


    if(isset($_POST['gclidlog'])) {
        $gclid1 = mysqli_real_escape_string($DB_site->link_id,$_POST['gclidlog']); //gclid du formulaire de connexion
    }elseif(isset($_POST['gclidlogpop'])){
        $gclid1 = mysqli_real_escape_string($DB_site->link_id,$_POST['gclidlogpop']); //gclid du formulaire de connexion popup
    }elseif(isset($_POST['gclid1'])) {
        $gclid1 = mysqli_real_escape_string($DB_site->link_id,$_POST['gclid1']); //gclid du formulaire de connexion devis et configurateur
    }else{
        $gclid1 ="";
    }
    
    if(!preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $email) && $email != ""){
            $email = "";
            $template->parts["errormessage"] .= $template->translate("/client/indiquer_mail_valide")."<br> ";
        }
    
    $register_action="logging";
    if(isset($_POST['action2']) && $_POST['action2'] == "inscription2"){ // inscription

        if(isset($_POST['connectdevis'])){
            $template->parts['jsconnectdevis'] = "openpopudevis();";
        }
        if(intval($_POST['propart']) == 2){
            $ispro = "pro";
        }
        $conseils = "oui";
        $register_action="inscription2";
        
        //Nettoyage des champs avant insertion
        foreach($_POST as $key => $value){
            $_POST[$key] = trim($value); // on enleve les espaces
        }
        //on adapte le nom des variables post en fonction du formulaire rempli
        $_POST['civilite'] = $_POST['civilite'.$ispro];
        $_POST['nom'] = $_POST['nom'.$ispro];
        $_POST['prenom'] = $_POST['prenom'.$ispro];
        $_POST['adresse'] = $_POST['adresse'.$ispro];
        $_POST['codepostal'] = $_POST['codepostal'.$ispro];
        $_POST['ville'] = $_POST['ville'.$ispro];
        $_POST['telephone'] = $_POST['telephone'.$ispro];
        $_POST['paysid'] = $_POST['paysid'.$ispro];
        $_POST['portable'] = $_POST['pordiv'.$ispro];
        $_POST['connu'] = $_POST['connu'.$ispro];
        $paysid = $_POST['paysid'];
        $civilite = $_POST['civilite'];
        if(isset($_POST['gclid'])) //Gclid du formulaire d'inscription
            $gclid = $_POST['gclid'];
        else
            $gclid = "";


        // Traitement d'erreur supplémentaire ? une grosse partie deja validé par jscript dans le formulaire... ?
        if($_POST['civilite'] == '') $template->parts["errormessage"] .= $template->translate("/client/erreur_civilite")."<br>";
        if($_POST['civilite'] != '' && $_POST['civilite'] != 0 && $_POST['civilite'] != 1 && $_POST['civilite'] != 2) $template->parts["errormessage"] .= $template->translate("/client/erreur_civilite")."<br>";
        if($_POST['nom'] == '') $template->parts["errormessage"] .= $template->translate("/client/erreur_nom")."<br>";
        if($_POST['prenom'] == '') $template->parts["errormessage"] .= $template->translate("/client/erreur_prenom")."<br>";
        if($_POST['mail'] == '') $template->parts["errormessage"] .= $template->translate("/client/indiquer_mail_valide")."<br>";
        if($_POST['adresse'] == '') $template->parts["errormessage"] .= $template->translate("/client/erreur_adresse")."<br>";
        if($_POST['codepostal'] == '') $template->parts["errormessage"] .= $template->translate("/client/erreur_codepostal")."<br>";
        if($_POST['ville'] == '') $template->parts["errormessage"] .= $template->translate("/client/erreur_ville")."<br>";
        if($_POST['passwordinscr'] == '') $template->parts["errormessage"] .= $template->translate("/client/erreur_pass")."<br>";
        if($_POST['passwordinscr'] != '' && strlen($_POST['passwordinscr']) < 6 and $_POST['passwordinscr'] != "") $template->parts["errormessage"] .= $template->translate("/client/erreur_pass_6caract")."<br>";
        if($_POST['telephone'] == '' && $_POST['portable'] == '') $template->parts["errormessage"] .= $template->translate("/client/erreur_telephone")."<br>";
        if($_POST['paysid'] == '' || !preg_match('#^[0-9]+$#', $_POST['paysid'])) $template->parts["errormessage"] .= $template->translate("/client/erreur_pays")."<br>";
        //err_mnews
        //err_snews

        if($template->parts["errormessage"] != '') $_POST['action'] = '';

        //if (!isset($connu) or $connu=="") { $action=""; $errormessage.="Vous devez sélectionner le moyen par lequel vous avez connu le site<br>"; }
        $contacthour = (isset($_POST['contacthour']) ? $_POST['contacthour'] : array());
        //Vérification de la tva intra si nom de société renseigné
        if($_POST['raisonsociale'] != ""){
            $templateMail = "inscription";

            $sql = "SELECT *";
            $sql .= " FROM pays";
            $sql .= " WHERE paysid = ".$paysid;
            $pays = $DB_site->query_first($sql);

            $tvaResult = true;
            if($pays['cee'] == 1){
                if($_POST['tvaintracom'] != ""){
                    $_POST['tvaintracom'] = str_replace(array(' ', '.', '-', ',', ', '), '', $_POST['tvaintracom']);
                    $result      = checkvatnumber($_POST['tvaintracom']);
                    if($result->valid == false) {
                        //Ne bloque plus si TVA invalide -> envoi mail à Cécile
                        //$tvaResult = false;
                    }
                } elseif($pays['paysid']==57 || $pays['paysid']==181){
                    $mailtva = true;
                }
            } else{
                $_POST['tvaintracom'] = "";
            }
        } else {
            $_POST['tvaintracom'] = "";
            $templateMail = "inscription";
        }
        
        $email = $_POST['mail'];
        $pass  = $_POST['passwordinscr'];

        if(!preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $_POST['mail']) && $_POST['mail'] != ""){
            $_POST['mail'] = "";
            $template->parts["errormessage"] .= $template->translate("/client/indiquer_mail_valide")."<br> ";
        }
        if($_POST['passwordinscr'] != $_POST['passwordinscrconfirm']) $template->parts["errormessage"] .= $template->translate("/client/erreur_confirm_mdp")."<br> ";

        // verif si l'email n'est pas deja enregistré
        $user = $DB_site->query_first("SELECT * FROM utilisateur WHERE mail = '".mysqli_real_escape_string($DB_site->link_id,htmlspecialchars($_POST['mail']))."'");
        if($user[0] != "") $template->parts["errormessage"] .= $template->translate("/client/email_deja_prise")."<br> ";

        unset($action);
        unset($action2);
        unset($_POST['action']);
        unset($_POST['action2']);

        $template->parts["nom"]           = $_POST['nom'];
        $template->parts["prenom"]        = $_POST['prenom'];
        $template->parts["raisonsociale"] = $_POST['raisonsociale'];
        $template->parts["mail"]          = $_POST['mail'];
        $template->parts["adresse"]       = $_POST['adresse'];
        $template->parts["codepostal"]    = $_POST['codepostal'];
        $template->parts["ville"]         = $_POST['ville'];
        $template->parts["telephone"]     = $_POST['telephone'];
        $template->parts["portable"]      = $_POST['portable'];
        $template->parts["tvaintracom"]   = $_POST['tvaintracom'];
        
        //Aucune erreur => création du compte
        if($template->parts["errormessage"] == ""){

            $langue_user =  $template->langue ;
            $site_code   = $template->site_code();
            if(is_null($provenance)) $provenance = $template->mobile?"mobile":"site";
            //Attribution du commercial en train de créer le compte
            $commercial = "";
            if(isset($_COOKIE['admin_username']) && $_COOKIE['admin_username'] != ""){
                $commercial = $_COOKIE['admin_username'];
                $provenance = "administrateur";
            }

            //Création du compte => insertion dans BDD
            $sql = "INSERT INTO utilisateur (";
            $sql .= "mail";
            $sql .= ", password";
            $sql .= ", civilite";
            $sql .= ", nom";
            $sql .= ", prenom";
            $sql .= ", codepostal";
            $sql .= ", adresse";
            $sql .= ", ville";
            $sql .= ", telephone";
            $sql .= ", telephone_livraison";
            $sql .= ", raisonsociale";
            $sql .= ", dateinscription";
            $sql .= ", paysid";
            $sql .= ", tvaintracom";
            $sql .= ", site_code";
            $sql .= ", langue";
            $sql .= ", allowemail";
            $sql .= ", allowsms";
            $sql .= ", optionnel";
            $sql .= ", portable";
            $sql .= ", nom_livraison";
            $sql .= ", prenom_livraison";
            $sql .= ", adresse_livraison";
            $sql .= ", codepostal_livraison";
            $sql .= ", ville_livraison";
            $sql .= ", raisonsociale_livraison";
            $sql .= ", paysid_livraison";
            $sql .= ", connu";
            $sql .= ", contacthour";
            $sql .= ", provenance";
            $sql .= ", fuseau_horaire";
            $sql .= ", conseils";
            $sql .= ", gclid";
            $sql .= ") VALUES (";
            $sql .= "'".$_POST['mail']."'";
            $sql .= ", MD5('".mysqli_real_escape_string($DB_site->link_id,$_POST['passwordinscr'])."')";
            $sql .= ", '".$civilite."'";
            $sql .= ", '".mb_strtoupper(mysqli_real_escape_string($DB_site->link_id,$_POST['nom']))."'";
            $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,$_POST['prenom'])."'";
            $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,$_POST['codepostal'])."'";
            $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,$_POST['adresse'])."'";
            $sql .= ", '".mb_strtoupper(mysqli_real_escape_string($DB_site->link_id,$_POST['ville']))."'";
            $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,str_replace(array(" ",".","-"), "", $_POST['telephone']))."'";
            $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,str_replace(array(" ",".","-"), "", $_POST['telephone']))."'";
            $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,$_POST['raisonsociale'])."'";
            $sql .= ", NOW()";
            $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,htmlspecialchars($_POST['paysid']))."'";
            $sql .= ", '".str_replace(" ", "", $tvaintracom)."'";
            $sql .= ", '".$site_code."'";
            $sql .= ", '".$langue_user."'";
            $sql .= ", '".$news_mail."'";
            $sql .= ", '".$news_sms."'";
            $sql .= ", '".$option."'";
            $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,str_replace(array(" ",".","-"), "", $_POST['portable']))."'";
            $sql .= ", '".mb_strtoupper(mysqli_real_escape_string($DB_site->link_id,$_POST['nom']))."'";
            $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,$_POST['prenom'])."'";
            $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,$_POST['adresse'])."'";
            $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,$_POST['codepostal'])."'";
            $sql .= ", '".mb_strtoupper(mysqli_real_escape_string($DB_site->link_id,$_POST['ville']))."'";
            $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,$_POST['raisonsociale'])."'";
            $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,htmlspecialchars($_POST['paysid']))."'";
            $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,$_POST['connu'])."'";
            $sql .= ", '".implode(",",$contacthour)."'";
            $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,$provenance)."'";
            $sql .= ", '".$_POST['fuseau_horaire']."'";
            $sql .= ", '".$conseils."'";
            $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,$gclid)."'";
            $sql .= ")";
            $DB_site->query($sql);

            $iid = $DB_site->insert_id();

            ////tache 7093 : mise en place du Datalayer
            if(isset($_POST['dest'])){
            if($_POST['dest'] == 'panier'){
                $template->parts['js'] .= "<script type=\"text/javascript\">
                var news_mail = 'non';
                var news_sms = 'non';
                if('".$news_mail."' === '1'){
                    news_mail = 'oui';
                }
                if('".$news_sms."' === '1'){
                    news_sms = 'oui';
                }
                if('".$_POST['raisonsociale']."' === ''){
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push({
                        'event': 'compte_creation_validation',
                        'compte_type': 'particulier',
                        'compte_creation_origine': 'panier',
                        'compte_newsletter_mail': news_mail,
                        'compte_newsletter_sms': news_sms
                    });
                }
                else if('".$_POST['mail']."' !== '' && '".$_POST['raisonsociale']."' !== ''){
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push({
                        'event': 'compte_creation_validation',
                        'compte_type': 'professionnel',
                        'compte_creation_origine': 'panier',
                        'compte_newsletter_mail': news_mail,
                        'compte_newsletter_sms': news_sms
                    });
                }";    
            }
            else{
                $template->parts['js'] .= "<script type=\"text/javascript\">
                var news_mail = 'non';
                var news_sms = 'non';
                if('".$news_mail."' === '1'){
                    news_mail = 'oui';
                }
                if('".$news_sms."' === '1'){
                    news_sms = 'oui';
                }
                if('".$_POST['raisonsociale']."' === ''){
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push({
                        'event': 'compte_creation_validation',
                        'compte_type': 'particulier',
                        'compte_creation_origine': 'header',
                        'compte_newsletter_mail': news_mail,
                        'compte_newsletter_sms': news_sms
                        });
                    }
                    else if('".$_POST['mail']."' !== '' && '".$_POST['raisonsociale']."' !== ''){
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push({
                            'event': 'compte_creation_validation',
                            'compte_type': 'professionnel',
                            'compte_creation_origine': 'header',
                            'compte_newsletter_mail': news_mail,
                            'compte_newsletter_sms': news_sms
                            });
                        }
                        </script>";
                    }
                }
            ////fin
            $vars=array(
              "nom"=>$_POST['nom'],
              "prenom"=>$_POST['prenom'],
              "email"=>$_POST['mail'],
              "pass"=>$_COOKIE['admin_username']==""?"******":$_POST['passwordinscr']
            );
            $emc=new email_client($DB_site,$template,null,$templateMail,$vars);
        
            $cmail=new email();
            $cmail->destinataire=$mail;
            $cmail->destname=$prenom." ".$nom;
            $cmail->expediteur=$template->translate("/site/[site_code]/mail_expediteur");
            $cmail->expname=$template->translate("/site/nom");
            $cmail->copymode="bcc";
            $cmail->to="commercial2@mon-espace-cuisson.com";
            $cmail->subject=$emc->subject;
            $cmail->body=$emc->body;
            $cmail->sendmail();

            $DB_site->query("INSERT INTO suivi_client SET userid='".$iid."', date=".time().", commercial='".$commercial."', suivi='".mysqli_real_escape_string($DB_site->link_id,$cmail->subject."<br>".$cmail->body)."', etat=1");
          
                if($cid!=0) $DB_site->query("UPDATE stat SET user=".intval($iid)." WHERE cid=".$cid);
                $DB_site->query("UPDATE article_vu SET userid=".intval($iid)." WHERE s='".mysqli_real_escape_string($DB_site->link_id,$_COOKIE[s])."'");

            if(isset($_COOKIE['s'])){
                $s = $_COOKIE['s'];
                $DB_site->query("DELETE FROM visiteur WHERE s='$s'");
            }    
                
            
            
            /******** mise en commentaire suite a la demande de philippe SI#3757 *************************************/
            /*if($mailtva) {
                $mail=new email();
                $mail->destinataire=$template->translate("cecile@mon-espace-cuisson.com");
                $mail->expediteur=$template->translate("/site/[site_code]/mail_expediteur");
                $mail->expname=$template->translate("/site/nom");
                $mail->subject="Manque info tva intra";
                $mail->body="Attention le client n°".$iid.", société ".$_POST['raisonsociale'].", ".$prenom." ".$nom." n'a pas indiqué son numéro de TVA";
                $mail->sendmail();

                $DB_site->query("INSERT INTO suivi_client SET userid='".$iid."', date=".time().", commercial='".$commercial."', suivi='".mysqli_real_escape_string($DB_site->link_id,$mail->subject."<br>".$mail->body)."', etat=1");
            }*/

            //Récap inscription
            $c_txt = array("M.", "Mme", "Melle");
            $body = "Client : ".$c_txt[$civilite]." ".$_POST['nom']." ".$_POST['prenom']."
            <br>Société : ".$_POST['raisonsociale']."
            <br>TVA Intra : ".$_POST['tvaintracom']."
            <br>Adresse : ".$_POST['adresse']." - ".$_POST['codepostal']." ".$_POST['ville']."
            <br>Téléphone : ".$_POST['telephone']."
            <br>Email : ".$_POST['mail']."";
            $DB_site->query("INSERT INTO suivi_client (`userid`, `commercial`, `date`, `suivi`, `etat`) VALUES ('".$iid."', '".$commercial."', ".time().", '".mysqli_real_escape_string($DB_site->link_id,$body)."', 1)");
            
            // fin creation compte
        } else {
            // traitment erreur creation compte
            $template->parts['display_register'] = 'block';
            $template->parts['hide_register'] = 'none';
            $mail_logging = "" ;
        }
    }

    if($template->parts["errormessage"]==""){
        $user_info=$DB_site->query_first("SELECT * FROM  utilisateur WHERE mail = '". mysqli_real_escape_string($DB_site->link_id,htmlspecialchars($email))."'");
        if ($user_info[password] == ""){
            $raisons=$template->translate("/client/erreur_email_inconnu");
            $user_info=array();
            $action = "" ;
           $template->parts['js'] .= "<script type=\"text/javascript\"> $( document ).ready(function() {
        var align = false;
        var largeur = $(document).width();
        if(largeur < 1183) align = true;
        $('.open-popup-link').magnificPopup({
        type:'inline',
        alignTop:align,
        midClick: true // allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source.
        });
        openpopudevis('$raisons');
    });
    </script>";
            //$user_info[userid] = "" ;
        }
        elseif($user_info[password] != md5($pass)) {
    $template->parts["raison"]=$template->translate("/client/erreur_mdp");
    $template->parts['js'] .= "<script type=\"text/javascript\"> $( document ).ready(function() {
        var align = false;
        var largeur = $(document).width();
        if(largeur < 1183) align = true;
        $('.open-popup-link').magnificPopup({
        type:'inline',
        alignTop:align,
        midClick: true // allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source.
        });
        openpopudevis('$raisons');
    });
    </script>";
        $user_info=array();
        $action = "" ;

        }

        elseif ($user_info[password]==md5($pass)){
            setcookie("userid","$user_info[userid]",time()+3600000,"/", $template->host);
            setcookie("password","$user_info[password]",time()+3600000,"/", $template->host);
            setcookie("prenom","$user_info[prenom]",time()+3600000,"/", $template->host);
            $template->parts["mouseovercompte"]="";

            //gestion du Gclid
            if(($user_info['gclid'] == "" || $user_info['gclid'] != $gclid1) && $gclid1 != ""){
                //enregistrement du gclid pour cet utilisateur ou si le premier gclid date de + de 90 jours on le remplace par le nouveau
                $DB_site->query("UPDATE utilisateur set gclid='".$gclid1."' WHERE userid='".$user_info['userid']."'");
            }
            
            // 1 connexion en + pour ce user !
            $amj = date ('Y-m-d') ;
            $stat_user=$DB_site->query_first("SELECT * FROM statistique_utilisateur WHERE userid = '$user_info[userid]'
                            and datestatistique = '$amj'");
            if ($stat_user[0] == ""){
            $new_stat=$DB_site->query("insert into statistique_utilisateur (userid, datestatistique, visites, pagesvues)
                        values ('$user_info[userid]', '$amj', '1', '1')");
            }else{
            $new_stat=$DB_site->query("update statistique_utilisateur set visites = visites + 1, pagesvues = pagesvues + 1
                            where userid = '$user_info[userid]' and datestatistique = '$amj'");
            }
            
            if($template->langue!=$user_info['langue'] || $template->site_code()!=$user_info['site_code']) $DB_site->query("UPDATE utilisateur SET langue='".$template->langue."', site_code='".$template->site_code()."' WHERE userid=".$user_info['userid']);
            
           
                if($cid!=0) $DB_site->query("UPDATE stat SET user=".$user_info[userid]." WHERE cid=".$cid);
                $DB_site->query("UPDATE article_vu SET userid=".intval($user_info[userid])." WHERE s='".mysqli_real_escape_string($DB_site->link_id,$_COOKIE[s])."'");
            
            $action = "" ;
            $action2 = "" ;
            /////// datalayer, connexion panier pro/part
            $datalayer = false;
            if($user_info['raisonsociale'] != "" && $_SERVER['SCRIPT_NAME'] == '/cde2.php'){
                $compte_type = 'professionnel';
                $co_origine = 'panier';
                $datalayer = true;
                //actualise la page pour que le picto connecté change
                header("Location: /cde2.php");
            }
            elseif($user_info['raisonsociale'] == "" && $_SERVER['SCRIPT_NAME'] == '/cde2.php'){
                $compte_type = 'particulier';
                $co_origine = 'panier';
                $datalayer = true;
                header("Location: /cde2.php");
            }
            /// connexion header pro/part
            elseif($user_info['raisonsociale'] != "" && $_SERVER['SCRIPT_NAME'] == '/client.php'){
                $compte_type = 'professionnel';
                $co_origine = 'header';
                $datalayer = true;
                //actualise la page pour que le picto connecté change
                header("Location: /client.php");
            }
            elseif($user_info['raisonsociale'] == "" && $_SERVER['SCRIPT_NAME'] == '/client.php'){
                $compte_type = 'particulier';
                $co_origine = 'header';
                $datalayer = true;
                header("Location: /client.php");
            }
            if($datalayer == true){
                echo "<script>window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({
                    'event': 'compte_connexion',
                    'compte_type': '".$compte_type."',
                    'compte_connexion_origine': '".$co_origine."'
                });</script>";
            }
            /// fin datalayer

            //redirection site pro si l'utilisateur qui se connect est un client pro sur le site part.
            /*if(($user_info['raisonsociale'] != "" && !$sitepro) || ($user_info['raisonsociale'] == "" && $sitepro)){ 
            // Si client pro qui se cherche a se connecter sur site part. exclusion du cas ou il se connecte dans le configurateur (0 = configurateur).
                if($_POST['redirection'] != '0') {
                    $userid=$user_info['userid'];
                    $prenom=$user_info['prenom'];
                    $password=$user_info['password'];
                    $s=$_COOKIE['s'];
                    redirectionPro($_SERVER['SCRIPT_NAME'], $_SERVER['HTTP_HOST'], $template,$userid,$s,$prenom,$password);
                }                     
        }*/
    }
    else{
            $template->parts["raison"]='$template->translate("/client/erreur_mdp")';
            if(isset($_POST['provenance']) && $_POST['provenance'] == 'devisrapide'){
                $template->parts['js'] = "<script type=\"text/javascript\">\$( document ).ready(function() {devisrap();});</script>";
            }
            $user_info=array();
            $action = "" ;
        }
    }
    else{
        //Mail non valide lors de la connexion (logging)
        $template->parts["raison"]=$template->translate("/client/erreur_mail");
        $user_info=array();
        $action = "" ;
    }
}
else{
    if($_COOKIE['userid']!=""){
        $user_info=$DB_site->query_first("SELECT * FROM  utilisateur WHERE userid='".$_COOKIE['userid']."' ");
        if ($user_info['password']==""){
            $raison="$multilangue[mailinconnu]";
            $user_info=array();
        }
        elseif ($user_info['password']==$_COOKIE['password']){
            $template->parts['mouseovercompte']="";
       
            if($user_info['vip']==-1){
                $stat=$DB_site->query_first("SELECT id FROM stat WHERE user='".$user_info['userid']."' ORDER BY timestamp DESC LIMIT 1");
                $statid=intval($stat['id']);
                $lastvisit=$DB_site->query_first("SELECT timestamp FROM stat_page WHERE statid = ".$statid." ORDER BY timestamp DESC LIMIT 2,1");
                if(time()-intval($lastvisit[0])>3600){
                    $listpagesvues="<br><br>Pages vues :";
                    $req=$DB_site->query("SELECT page,timestamp FROM stat_page WHERE statid=".$statid." AND timestamp>".(time()-3600)." ORDER BY timestamp");
                    while($pagevue=$DB_site->fetch_array($req))
                    $listpagesvues.="<br>".date("H:i:s",$pagevue['timestamp'])." ".$pagevue['page'];
                
                    $mail=new email();
                    $mail->destinataire=$template->translate("/site/[site_code]/mail_commercial");
                    $mail->expediteur=$template->translate("/site/[site_code]/mail_expediteur");
                    $mail->expname=$template->translate("/site/nom");
                    $mail->subject="Connexion client blacklisté";
                    $mail->body="Attention le client n°".$user_info['userid']." ".$user_info['nom']." ".$user_info['prenom']." est connecté sur le site.<br>IP : ".$_SERVER['REMOTE_ADDR'].$listpagesvues;
                    $mail->sendmail();
                }
            }
        }
        else{
            setcookie("userid","",time()+3600000,"/", $template->host);
            setcookie("password","",time()+3600000,"/", $template->host);
            setcookie("prenom","",time()+3600000,"/", $template->host);
            $raison="$multilangue[motpasseincorrect]";
            $user_info=array();
        }
    }
}

if(isset($_COOKIE['userid'])){
    $template->parts["compt_co"]="connecte.svg?t=".time();
    $user_info=$DB_site->query_first("SELECT * FROM utilisateur WHERE userid='".$_COOKIE['userid']."'");
    $template->parts["nom_compte"]=$user_info['prenom'];
}
else{
    $template->parts["compt_co"]=".svg?t=".time();
    $template->parts["nom_compte"]=$template->translate("/client/mon_compte","U");
    
}

// Fin logging et inscription
$template->parts["ajoutSelect"]="";
for($i=1;$i<=99;$i++) $template->parts['ajoutSelect'].="<option value=\"".$i."\">".$i."</option>";

if ($_COOKIE['s']==""){
    $s = md5(uniqid(rand(), true));
    $url_from=explode(".", $_SERVER['HTTP_HOST']);
    end($url_from);
    setcookie("s",$s,time()+3600000, "/", ".".prev($url_from).".".end($url_from));
}else{

    $s = $_COOKIE['s'];
    $url_from=explode(".", $_SERVER['HTTP_HOST']);
    end($url_from);

    if ($action=="loggout"){
        setcookie("userid","",time()+3600000, "/", $template->host);
        setcookie("password","",time()+3600000, "/", $template->host);
        setcookie("prenom","",time()+3600000, "/", $template->host);
        setcookie("userid","",time()+3600000, "/", ".".prev($url_from).".".end($url_from));
        setcookie("prenom","",time()+3600000, "/", ".".prev($url_from).".".end($url_from));
        setcookie("s","",time()+3600000, "/", ".".prev($url_from).".".end($url_from));
        setcookie("password","",time()+3600000, "/", ".".prev($url_from).".".end($url_from));
        setcookie("s","",time()+3600000, "/", $template->host);
        header("Location: /");
    }
    
    if (isset($action) and $action == "delpanier_complet"){
        $DB_site->query("delete from panier where sessionid = '".$s."'");
        setcookie('popup_exit',1,time()+86400);
    }

    if(!strpos($_SERVER['SCRIPT_NAME'], "cde1.php") && !strpos($_SERVER['SCRIPT_NAME'], "cde2.php")){
        $panier=$DB_site->query_first("SELECT * FROM panier where sessionid = '".$s."'");
    }
    else
    {
        // delpanier
        $sql = "SELECT *";
        $sql .= " FROM panier";
        $sql .= " WHERE sessionid = '".$s."'";
        $panier = $DB_site->query_first($sql);

        if($panier['panierid'] != "")
        {
            if($action == "delpanier")
            {
                if(isset($artpanierid) && $artpanierid != "")
                {
                    $sql = "DELETE FROM lignepanier";
                    $sql .= " WHERE panierid = '" . $panier['panierid'] . "'";
                    $sql .= " AND lignepanierid = '" . $artpanierid . "'";
                    $DB_site->query($sql);

                    $sql = "DELETE FROM lignepaniercaracteristique";
                    $sql .= " WHERE lignepanierid = '" . $artpanierid . "'";
                    $DB_site->query($sql);
                }
                else
                {
                    $sql = "SELECT artid, qte";
                    $sql .= " FROM lignepanier";
                    $sql .= " WHERE lignepanierid = '" . $lignepanierid . "'";
                    $delart = $DB_site->query_first($sql);

                    if(isset($delart['artid']) && is_numeric($delart['artid']))
                    {
                        $produit = new produit($DB_site,"FR",$template->site_code(),$template->tva_ref(),$delart['artid']);
                    
                        $template->parts["ecommerce"] = "ga('ec:addProduct', {
                        'id': '" . $produit->ref_aci . "',
                        'name': \"" . html_entity_decode($produit->libelle) . "\",
                        'category': \"" . $produit->categorie[0]['categorie_libelle'] . "\",
                        'brand': '" . $produit->marque_libelle . "',
                        'variant': '',
                        'price': " . $produit->prix_ttc . ",
                        'quantity': " . $delart['qte'] . "
                        });
                        ga('ec:setAction', 'remove');";
                    }

                    $sql = "DELETE FROM lignepanier";
                    $sql .= " WHERE lignepanierid = '" . $lignepanierid . "'";
                    $DB_site->query($sql);

                    $sql = "DELETE FROM lignepaniercaracteristique";
                    $sql .= " WHERE lignepanierid = '" . $lignepanierid . "'";
                    $DB_site->query($sql);
                }

                $sql = "SELECT COUNT(*)";
                $sql .= " FROM lignepanier";
                $sql .= " WHERE panierid = '" . $panier['panierid'] . "'";
                $lignepaniercount = $DB_site->query_first($sql);

                if($lignepaniercount[0] == 0)
                {
                    $sql = "DELETE FROM panier";
                    $sql .= " WHERE panierid = '" . $panier['panierid'] . "'";
                    $DB_site->query($sql);
                }

                $template->redirect($_SERVER['HTTP_REFERER'],302);
            }
        }

    if ($action == "addpanier"){
        if ($qte > "0"){
        $datepanier = date('Y-m-d') ;
        $panier=$DB_site->query_first("SELECT * FROM panier where sessionid=".$s);
        $temppanierid = $panier[panierid] ;
        $DB_site->query("INSERT IGNORE INTO panier (sessionid, datepanier, prixtva, prixgestion, affilieid,userid) VALUES ('$s', '$datepanier', 0, 0, 0,".$user_info['userid'].")");
        if ($temppanierid == "") $temppanierid = $DB_site->insert_id() ;
        
        $produit=new produit($DB_site,"FR",$template->site_code(),$template->tva_ref(),$artid);
        $template->parts["ecommerce"]="ga('ec:addProduct', {
          'id': '".$produit->ref_aci."',
          'name': \"".html_entity_decode($produit->libelle)."\",
          'category': \"".$produit->categorie[0]['categorie_libelle']."\",
          'brand': '".$produit->marque_libelle."',
          'variant': '',
          'price': ".$produit->prix_ttc.",
          'quantity': ".$delart['qte']."
        });
        ga('ec:setAction', 'add');";
            
        $existe=$DB_site->query_first("select count(*) from lignepanier where panierid = '".$temppanierid."' and artid = '".$artid."'") ;
        if ($existe[0] == 0){
            $mycaractvalids = array() ;
            foreach ($_POST as $cle=>$valeur){
                if (substr($cle, 0, 4) == "lst_"){
                    $autres=$DB_site->query_first("select count(*) from article_caractval ac inner join caracteristiquevaleur cv on (ac.caractvalid = cv.caractvalid) 
                    where ac.artid = '".$artid."' and cv.caractid IN (select caractid from caracteristiquevaleur where caractvalid = '".$valeur."')") ;
                    array_push($mycaractvalids, $valeur) ;
                }
            }
        
            $DB_site->query("INSERT INTO lignepanier (panierid, artid, qte) VALUES ('".$temppanierid."', '".$artid."', '".$qte."')") ;
            $templignepanierid = $DB_site->insert_id() ;
            // Caractéristiques
            foreach ($_POST as $cle=>$valeur){ 
                if (substr($cle, 0, 4) == "lst_"){   // Pour chaque zone de liste on met la valeur séléctionnée dans lignepaniercaracteristique
                    $DB_site->query("INSERT INTO lignepaniercaracteristique (lignepanierid, caractvalid)
                              VALUES ('".$templignepanierid."', '".$valeur."')");
                }
            }
        }else{
            $from ="" ;
            $where = "" ;
            $nbre = 0 ;
            // Caractéristiques
            foreach ($_POST as $cle=>$valeur){
                if (substr($cle, 0, 4) == "lst_"){
                    $nbre++ ;
                    $from .= ", lignepaniercaracteristique lpc".$nbre ;
                    $where .= " and lp.lignepanierid = lpc".$nbre.".lignepanierid " ;
                    $where .= " and lpc".$nbre.".caractvalid = '".$valeur."'" ;
                }
            }
            $article_correspond=$DB_site->query_first("select * from lignepanier lp ".$from." where artid = '".$artid."' and panierid = '".$temppanierid."' ".$where." ") ;
            if ($article_correspond[0] != ""){
                $passe = 0 ;
                $mycaractvalids = array() ;
                $DB_site->query("UPDATE lignepanier set qte = (qte + $qte) where lignepanierid = '".$article_correspond['lignepanierid']."'") ;
                $templignepanierid=$article_correspond['lignepanierid'] ;
            }
        }
        }
    }
    }
    
    $panier=$DB_site->query_first("SELECT * FROM panier where sessionid ='".$s."'");
    
    if ($panier['panierid'] != ""){
    $lignepaniercount=$DB_site->query_first("SELECT count(*) FROM lignepanier WHERE panierid = '".$panier['panierid']."'");
    $TemplateCde1Bit = "" ;
    if ($action != "delpanier"){
        if ($action2 == "changeqte"){
        while (list($key, $val) = each($qte)){
            if ($val > "0"){
            $artencours=$DB_site->query_first("select artid from lignepanier WHERE lignepanierid = '".$key."'");
            $DB_site->query("UPDATE lignepanier SET qte='".$val."' WHERE lignepanierid='".$key."'");
            
            }else{
            $DB_site->query("DELETE FROM lignepanier WHERE lignepanierid = '".$key."'");
            $DB_site->query("DELETE FROM lignepaniercaracteristique WHERE lignepanierid = '".$key."'");
            if ($lignepaniercount[0] == 1)
                $DB_site->query("DELETE FROM panier WHERE panierid='".$panier['panierid']."'");

            }
            if(is_array($lst_caract[$key])){
            $DB_site->query("DELETE FROM lignepaniercaracteristique WHERE lignepanierid = '".$key."'");
            foreach ($lst_caract[$key] as $cle=>$valeur){
                $DB_site->query("INSERT INTO lignepaniercaracteristique SET lignepanierid='".$key."', caractvalid='".$valeur."'");
                /*$rs_lignecaracteristique = $DB_site->query_first("SELECT lpc.lignepaniercaracteristiqueid, lignepanierid,
                lpc.caractvalid, caractid FROM lignepaniercaracteristique lpc INNER JOIN caracteristiquevaleur cv
                ON (cv.caractvalid=lpc.caractvalid) WHERE lignepanierid = '$key' AND caractid='$cle'");
                if ($rs_lignecaracteristique[lpc.caractvalid] != $valeur){
                  $DB_site->query("UPDATE lignepaniercaracteristique SET caractvalid='$valeur'
                  WHERE lignepaniercaracteristiqueid = '$rs_lignecaracteristique[lignepaniercaracteristiqueid]'");
                }*/
            }
            }
        }
         }
    }
    } // fin si panier
}

if(isset($_POST['addgartoligne']) && $_POST['garsup']<>""){  //modification garantie
    $DB_site->query("UPDATE lignepaniercaracteristique SET caractvalid='".$_POST['garsup']."' WHERE lignepanierid=".$_POST['addgartoligne']);
}

// pays pour définir le taux de TVA
if ($_COOKIE['userid'] != ""){
  $pays_user=$DB_site->query_first("select paysid from utilisateur where userid = '".$_COOKIE['userid']."'");
  $paysid = $pays_user['paysid'] ;
}elseif (isset($user_info['userid']) && $user_info['userid'] != ""){
  $pays_user=$DB_site->query_first("select paysid from utilisateur where userid = '".$user_info['userid']."'");
  $paysid = $pays_user['paysid'] ;
}else{
  $paysid = "57" ;
}

$tvafrance=$DB_site->query_first("select 0 AS taux0,TVAtauxnormal AS taux1, TVAtauxreduit AS taux2 from pays where paysid = '57'");
$now=mktime(0,0,0);
$lignepaniertmps=$DB_site->query("SELECT l.qte , a.artid, a.artcode, a.prix, a.tauxchoisi, a.fraisgestionid, l.lignepanierid, a.libelle".$template->langue." AS libelle, m.libelle AS marque  
        FROM lignepanier l 
        INNER JOIN article a on (a.artid = l.artid) 
        LEFT JOIN article_marque am ON(am.artid=l.artid)
        LEFT JOIN marque m USING(marqueid)
        WHERE panierid = '$panier[panierid]' AND panierid != 0 ORDER BY l.lignepanierid ASC");
$artcount=$DB_site->num_rows($lignepaniertmps);

if($artcount > 0){
     
     $artcount = 0;
     $packs=new Pack($DB_site);
     
     while($lignepaniertmp = $DB_site->fetch_array($lignepaniertmps)){

      if($packs->isPack($lignepaniertmp['artid']))
          $prices=$packs->prices($lignepaniertmp['artid'],$template->tva_ref,$sitepro);
      else
          $prices = article_prices($lignepaniertmp['artid'],$template->tva_ref,$template->site_code(),$sitepro);
          $lignepaniertmp['prix'] = $prices['prix'];

          //Inclusion du montant des caractéristiques dans le calcul du montant total
          $sql = "SELECT ac.differenceprix";
          $sql .= " FROM lignepanier lp, article_caractval ac, lignepaniercaracteristique lpc";
          $sql .= " WHERE lp.lignepanierid = lpc.lignepanierid";
          $sql .= " AND lpc.caractvalid = ac.caractvalid";
          $sql .= " AND lp.lignepanierid = '".$lignepaniertmp['lignepanierid']."'";
          $sql .= " AND ac.artid = ".$lignepaniertmp['artid'];
          $caracts_diff_px = $DB_site->query($sql);
          $modif_px = 0 ;
          while($caract_diff_px = $DB_site->fetch_array($caracts_diff_px)){
               
               $prixsites = @unserialize($caract_diff_px['differenceprix']);

               if($prixsites === false){

                    $modif_px += floatval($caract_diff_px['differenceprix']);

               } else {

                    $modif_px += $prixsites[$template->site_code()];
               }
          }
          $template->parts["popuppanierimg"]=article_image($lignepaniertmp['artid'],71,93);
          
          $template->parts["popuppanierlibelle"]=addslashes($lignepaniertmp['libelle']);
          $template->parts["popuppaniermarque"]=$lignepaniertmp['marque'];
          $template->parts["popuppanierref"]=$lignepaniertmp['artcode'];
          $template->parts["popuppanierprix"]=$template->price_format($prices['prix']);
          $template->parts["popuppanierqte"]=$lignepaniertmp['qte'];
          $template->parts["lignepanierid"]=$lignepaniertmp['lignepanierid'];
          
          $template->evaltemplate("PopupPanierItem","popuppanierliste");
          
          $lignepaniertmp['prix'] += $modif_px;
          if($sitepro) $lignepaniertmp['prix'] = $lignepaniertmp['prix']/(1+$tvafrance['taux'.$lignepaniertmp['tauxchoisi']]/100);      

          $prixtotalpanier += $lignepaniertmp['qte'] * $lignepaniertmp['prix'];
          $artcount        += $lignepaniertmp['qte'];
     }

     $template->parts["mouseoverpanier"]=<<<HTML
      onmouseenter="popup_header('popup_panier',true)" onmouseleave="popup_header('popup_panier',false)"
HTML;
    $nb_articles_panier = $artcount;
}

$fraisport=$DB_site->query_first("SELECT gratuit FROM fraisport WHERE paysid=57 LIMIT 1");
$template->parts["portgratuit"]=$template->price_format($fraisport[0]);
$template->parts["portgratuitht"]=$template->price_format($fraisport[0]/(1+$template->tva_ref(1)/100));

$promocat=new categorie($DB_site,0,$template->langue);
$promocat->promo_load();
$template->parts["promourl"]=$promocat->promo_url;

//gestion du formulaire de rappel instantané
if(isset($user_info['userid'])){
    $pays_libelle = $DB_site->query_first("SELECT libelle FROM pays WHERE paysid ='".$user_info['paysid']."'");
    $template->parts["telephone"] = $user_info['telephone'];
    $template->parts["email"] = $user_info['mail'];
    $template->parts['nomprenom'] = $user_info['nom']. ' ' . $user_info['prenom'];
    $template->parts['pays'] = $pays_libelle[0];
}

//Gestion du menu

if(!$template->mobile){
    if(!$template->getcache("menu",intval($sitepro)) || !$template->getcache("sousmenus",intval($sitepro)) || !$template->getcache("soussousmenu",intval($sitepro)) || !$template->getcache("menuresp",intval($sitepro)) || !$template->getcache("sousmenusResp",intval($sitepro))){

    include("onglets.php");

    //Mise en cache du menu
    $template->savecache("menu",intval($sitepro));
    $template->savecache("sousmenus",intval($sitepro));
    $template->savecache("soussousmenu",intval($sitepro));
    $template->savecache("menuresp",intval($sitepro));
    $template->savecache("sousmenusResp",intval($sitepro));
    }
}else{var_dump('yghes');
    include("menu_mobile.php");
    $template->evaltemplate("rr","recherche_rapide");
}

$mvial=$DB_site->query_first("SELECT libelle".$template->langue.", institutionnelid FROM institutionnel WHERE libelleFR LIKE '%mathieu vial%'");
$template->parts["mvialurl"]=$template->mkurl("institutionnel",$mvial['institutionnelid'],$mvial);



$main=new categorie($DB_site,0,$template->langue);
//Gestion du menu FOOTER
$template->parts["menufooter"] = "";
$pro = 0;
$version = '';
if($sitepro){
 $pro = 1;
 $version = 'pro';
}
/*
$menus = $DB_site->query("SELECT * FROM menu_footer WHERE actif = 1 ORDER BY position");
while($menu = $DB_site->fetch_array($menus)){
    //construction du MENU FOOTER
    $template->parts["menufooter"] .= "<ul class=\"menufooter col-sm-3 hidden-xs hidden-sm\"><li class=\"lititle\">".$menu['titre'.$template->langue.$version]."</li>";
    $pages = $DB_site->query("SELECT * FROM menu_footer_pages WHERE menuid = '".$menu['menuid']."' AND pro=".$pro." AND libelle".$template->langue." != '' ORDER BY position ASC");
    while($page = $DB_site->fetch_array($pages)){
        $template->parts["menufooter"] .= "<li><a href=\"".$page['url'.$template->langue]."\">".$page['libelle'.$template->langue]."</a></li>";
    }
    $template->parts["menufooter"] .= "</ul>";
}
*/



//gestion de l'affichage des pages faq. initialisation des variable qui seront le nom de la table appelée
if($template->pro){
    $table_faq          = "faq_pro";
    $table_faq_bloc     = "faq_bloc_pro";
    $table_faq_article  = "faq_article_pro";
    $blocfaq                   = "faqblocpro";
}else{
    $table_faq          = "faq_part";
    $table_faq_bloc     = "faq_bloc_part";
    $table_faq_article  = "faq_article_part";
    $blocfaq                   = "faqbloc";
}

//Construction du menu faq
$questions=$DB_site->query("SELECT faqid, libelle".$template->langue." AS libelle FROM ".$table_faq." WHERE faqparent=0 ORDER BY position");
while($question=$DB_site->fetch_array($questions)){ 
    if($question['libelle'] != '')
        $template->parts["faq"].="<li><a href=\"/faq,$question[faqid],".url_rewrite($question['libelle']).".htm\">$question[libelle]</a></li>";
}

//gestion de l'affichage des pages conseils. initialisation des variable qui seront le nom de la table appelée
if($template->pro){
    $table_conseil          = "conseil_pro";
    $table_conseil_bloc     = "conseil_bloc_pro";
    $table_conseil_article  = "conseil_article_pro";
    $bloc                   = "blocpro";
}else{
    $table_conseil          = "conseil_part";
    $table_conseil_bloc     = "conseil_bloc_part";
    $table_conseil_article  = "conseil_article_part";
    $bloc                   = "bloc";
}

//Construction du menu des conseils d'expert
$conseils=$DB_site->query("SELECT conseilid, libelle".$template->langue." AS libelle FROM ".$table_conseil." WHERE conseilparent=0 AND site_code='".$template->site_code()."' ORDER BY position");
while($conseil=$DB_site->fetch_array($conseils)){ 
    if($conseil['libelle'] != '')
        $template->parts["conseils"].="<li><a href=\"/conseil,$conseil[conseilid],".url_rewrite($conseil['libelle']).".htm\">$conseil[libelle]</a></li>";
}

//Lien vers la presse
$aPresse=$DB_site->query_first("SELECT url".$template->langue." AS url FROM presse WHERE id=0");
$template->parts["article_presse_url"]="/presse/".$aPresse['url'].".htm";
$i=0;
$req=$DB_site->query("SELECT id,libelle".$template->langue." AS libelle, url".$template->langue." AS url FROM presse WHERE parent_id=0 LIMIT 3");
while($parent=$DB_site->fetch_array($req)) {
    
    //$presscount=$DB_site->query_first("SELECT COUNT(id) FROM presse WHERE active=1 AND parent_id=".$parent['id']);
    //$plimit=(date("W") % $presscount[0]);
    $presse=$DB_site->query_first("SELECT id,libelle".$template->langue." AS libelle, url".$template->langue." AS url, resume".$template->langue." AS resume, contenu".$template->langue." AS contenu FROM presse WHERE parent_id=".$parent['id']." AND active=1 ORDER BY date_creation DESC LIMIT 1");
    $presse_thumb="configurations/".$host."/images/presse/thumb/".$presse['id'].".jpg";
    if(!file_exists($presse_thumb)){
    $presse_img=img_in_html($presse["contenu"]);
    $presse_img[0]=ltrim($presse_img[0],"./");
    $resizeok=resize_img($presse_img[0],$presse_thumb,50);
    if(!$resizeok) $presse_thumb="";
    }    
    if($presse_thumb!=false)
      $htmlimg="<a href=\"/presse/".$presse['url'].".htm\"><img src=\"/".$presse_thumb."\" alt=\"".$presse['libelle']."\"></a>";
    
    $template->parts["article_presse".$i].="<div class=\"post post_cine\">
      <!--<div class=\"postimg\">$htmlimg</div>-->
      <div class=\"posttxt\">
    <a href=\"/presse/".$parent['url'].".htm\" class=\"title\">".$parent['libelle']." &nbsp;:</a>
    <a href=\"/presse/".$presse['url'].".htm\">".nl2br($presse['resume'])."</a>
      </div>
      <div class=\"clear\"></div>
    </div>";
    $i++;
}

$template->evaltemplate("MoyensPaiement","moyens_paiement");

$sql="SELECT am.marqueid,m.libelle,m.url".$template->langue." AS url FROM article_rangement ar 
INNER JOIN article_marque am USING(artid) INNER JOIN marque m USING(marqueid)
INNER JOIN article_site ac_s ON(ac_s.artid=ar.artid)
WHERE ac_s.active =1 AND ac_s.site_code='".$template->site_code()."'
GROUP BY am.marqueid ORDER BY m.libelle";
$req=$DB_site->query($sql);
while($accmarque=$DB_site->fetch_array($req)){
    $template->parts["footeraccessoires"].="| <a href=\"/".$template->translate("/article/accessoires/accessoires","L")."/".$accmarque['url']."-".$accmarque['marqueid'].".htm\">
      ".$accmarque['libelle']."
    </a>";
    $template->parts["listemarquesaccessoires"].="<option value=\"".$accmarque['marqueid']."\">".$accmarque['libelle']."</option>";
}
$template->parts["footermarques"]="";
$liste_marque_homes=$DB_site->query("SELECT marqueid FROM marque WHERE menu_home='1' ORDER BY libelle ASC ");
while($liste_marque_home=$DB_site->fetch_array($liste_marque_homes)){
    $marqueid=$liste_marque_home['marqueid'];
    $marque=new marque($DB_site,$marqueid,$template->langue);
    $template->parts["footermarques"].="| <a href=$marque->link>$marque->libelle</a>";
}


if(!$sitepro){
    $landings=$DB_site->query("SELECT footer".$template->langue." AS footer, url".$template->langue." AS url,type FROM landing WHERE footer".$template->langue."<>''");
    while($landing=$DB_site->fetch_array($landings)) $template->parts["landingfooter".$landing['type']].="| <a href=\"".$template->translate("/landing/index","L").$landing['url'].".html\">".$landing['footer']."</a> ";
    $template->parts["landingfootermarque"]=substr($template->parts["landingfootermarque"],2);
    $template->parts["landingfootercategorie"]=substr($template->parts["landingfootercategorie"],2);
    
    $template->parts["footeraccessoires"]=substr($template->parts["footeraccessoires"],2);
    $template->parts["footermarques"]=substr($template->parts["footermarques"],2);
    
    $template->evaltemplate("FooterLanding","footerlanding");


    $photopath=$template->translate("/site/gallery_path");
    $gallery=new gallery($DB_site,$template->langue);
    $gallery->path=$template->translate("/site/gallery_path");
    $list=$gallery->getListByType("categorie");
    $count=0;
    $photos=$liens=$sep="";
    foreach($list as $gal){
      if($count<10){
      $p=$gal->first_photo();
      if($p!=false){
          $photos.="<a href=\"".$gal->getFullUrl()."\"><img src=\"/".$p->getpath($photopath,80,56)."\" alt=\"".$p->libelle."\" class=\"gallery lazyf\"></a>";
          $count++;
      }
      }
      $liens.=$sep."<a href=\"".$gal->getFullUrl()."\">".$gal->libelle."</a> ";
      $sep="| ";
    }
    $template->parts["gallerycatlinks"]=$photos."<div class=\"clear\"></div>".$liens;
    
    $count=0;
    $photos=$liens=$sep="";
    $list=$gallery->getListByType("marque");
    foreach($list as $gal){
      if($count<10){
      $p=$gal->first_photo();
      if($p!=false){
          $photos.="<a href=\"".$gal->getFullUrl()."\"><img data-src=\"/".$p->getpath($photopath,80,56)."\" alt=\"".$p->libelle."\" class=\"gallery lazyf\"></a>";
          $count++;
      }
      }
      $liens.=$sep."<a href=\"".$gal->getFullUrl()."\">".$gal->libelle."</a> ";
      $sep="| ";
    }
    $template->parts["gallerymarquelinks"]=$photos."<div class=\"clear\"></div>".$liens;
      
    $gallery->loadIndex();
    $template->parts['galleryindexurl']=$gallery->getFullUrl();
    $template->evaltemplate("FooterGallery","galleryfooter");
}


$maincat=new categorie($DB_site,$template->langue);

$template->parts["listecapacites"] = "" ;
$i=0;
$capacitesnb=count($maincat->capacites);
foreach($maincat->capacites as $capaciteid=>$value){
  $txt=sprintf($template->translate("/menu/de_a_bouteilles"),$value,$maincat->capacites[$capaciteid+1]);
  if($capaciteid==0) $txt=sprintf($template->translate("/menu/jusqua_bouteilles"),$maincat->capacites[$capaciteid+1]);
  if($i==$capacitesnb-2) $txt=sprintf($template->translate("/menu/plusde_bouteilles"),$value);
  $template->parts["listecapacites"].="<option value=\"$capaciteid\">$txt</option>";
  $i++;
  if($i==$capacitesnb-1) break;
}

$template->parts["listefinitions"] = "" ;
$r=$DB_site->query("SELECT * FROM recherche_finition");
while ($recherchefinition=$DB_site->fetch_array($r))
    $template->parts["listefinitions"] .= "<option value=\"$recherchefinition[finitionid]\" class=\"listesrecherche\">".$recherchefinition['nom'.$template->langue]."</option>" ;
   
$template->parts["listerangements"] = "" ;
$r=$DB_site->query("SELECT * FROM recherche_moderangement");
while ($rechercherangement=$DB_site->fetch_array($r))
    $template->parts["listerangements"] .= "<option value=\"$rechercherangement[recherchemoderangementid]\" class=\"listesrecherche\">".$rechercherangement['nom'.$template->langue]."</option>" ;

//Paramétrage pour informations comparateur (barre footer)
$comparateur=new comparateur($DB_site);

$mvial=$DB_site->query_first("SELECT libelle".$template->langue." AS libelle, institutionnelid FROM institutionnel WHERE libelleFR LIKE '%mathieu vial%'");
$template->parts["mvialurl"]="/int,".$mvial['institutionnelid'].",".url_rewrite($mvial['libelle']).".htm";
$catmvial=new categorie($DB_site,182,$template->langue);
$template->parts["urlselectionvins"]=$catmvial->url()['url'];

//Gestion du wallpaper
$sql = "SELECT *";
$sql .= " FROM fond";
$sql .= " WHERE site_code='".$template->site_code()."'";
$sql .= " AND langue='".$template->langue."'";
$sql .= " AND ".($sitepro?"pro":"part")." = 1";
$sql .= " AND date_dbt<'".time()."' AND date_fin>'".time()."' ";
$fond = $DB_site->query_first($sql);
$template->parts["fond_color"] = $body_color = '#E6E2E0';
$src = "/configurations/".$host."/images/fond/";
if($fond != ''){
    if(time() >= $fond['date_dbt'] && time() <= $fond['date_fin']){
        $template->parts['wallpaperclass'] ="wallpaperDis";
        $template->parts["fond_left"] ="<a href=\"".$fond['lien']."\"><div class=\"fond-left\" style=\"background:url(/images/fond/".$fond['id']."_left.jpg?t=".time().") right top no-repeat;\"></div></a>";
        $template->parts["fond_right"] ="<a href=\"".$fond['lien']."\"><div class=\"fond-right\" style=\"background: url(/images/fond/".$fond['id']."_right.jpg?t=".time().") no-repeat;\"></div></a>";
        if($fond['fond_color'] != ''){
            $template->parts["fond_color"] = $body_color = $fond['fond_color'];
        }  
    }
}

if(ESPACE == 'prod'){

    /*******************************/
    /* REMARGETING NEXTPERFORMANCE */
    /*******************************/
    if(isset($_GET['nxtck']) && $_GET['nxtck'] == 1) setcookie("nxtck",1,time()+1296000, "/"); //Le cookie reste 15 jours

    /**********************/
    /* RETARGETING EMATCH */
    /**********************/
    $template->parts["ematch"] = "
    <script>
        var __adUrl=document.location.protocol+'//atout.email-match.com/emafunc.js';
        try
        {
            scriptTag=document.createElement(\"script\");
            scriptTag.type=\"text/javascript\";
            scriptTag.async=true;
            scriptTag.src=__adUrl;
            var headTag= document.getElementsByTagName(\"head\")[0];
            headTag.insertBefore(scriptTag,headTag.firstChild);
        } catch(e)
        {
            document.write('<sc'+'ript language=\"javascript\" async=\"\" type=\"text/javascript\" src=\"'+__adUrl+'\"></sc'+'ript>');
        }
        function w_emasend()
        { if (typeof(emasend)!='undefined'){emasend();} else 
        {setTimeout('w_emasend()',200);} }
            window.ema_id_site=1521;
            %TAG_SUPPLEMENTAIRES%
            w_emasend();
    </script>";


    //PANIER ETAPE 1
    if($_SERVER['SCRIPT_NAME'] == '/cde1.php'){

        $tagArticles = "window.ema_critere = 'crt_listproduct=%ARTIDS%';";

        //Récupération du panier
        $sql = "SELECT lp.artid";
        $sql .= " FROM panier p";
        $sql .= " INNER JOIN lignepanier lp ON (lp.panierid = p.panierid)";
        $sql .= " WHERE p.sessionid = '".$s."'";
        $req = $DB_site->query($sql);
        if($DB_site->num_rows($req) > 0){
            $liste_artids = '';
            while($row = $DB_site->fetch_array($req)){
                if($liste_artids != '') $liste_artids .= ';';
                $liste_artids .= $row['artid'];
            }
        }

        //On ajoute les lignes uniquement s'il y a des produits au panier
        if($liste_artids != ''){
            $tagArticles               = str_replace('%ARTIDS%',$liste_artids,$tagArticles);
            $template->parts["ematch"] = str_replace('%TAG_SUPPLEMENTAIRES%',$tagArticles,$template->parts["ematch"]);    
        } 
    }

    //PANIER ETAPE 5 (confirmation de paiement)
    if($_SERVER['SCRIPT_NAME'] == '/cde3.php'){
        //Pour cette étape, les tags sont créés dans la page cde3.php, pour éviter d'alourdir le chargement de la page en faisant de nouveau appel à la classe $facture    
    }

    //Remplace tous les tags possibles par du vide, pour éviter de laisses trainer des variables de remplacement
    $template->parts["ematch"] = str_replace('%TAG_SUPPLEMENTAIRES%','',$template->parts["ematch"]);
}
$template->parts["lazy_load"] = ""
."<script >
  \$( document ).ready(function() {
    \$('.lazy').lazy();
  });
</script>";

if(isset($_GET['debug_global'])) nice($_SERVER);

//$template->evaltemplate("BarreCookies","barrefixe");
//var_dump($template->parts['modaltype']);