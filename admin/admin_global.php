<?php
require_once __DIR__.'/../secrets.php';
session_start();
ini_set("display_errors","1");
error_reporting(E_ALL);

extract($_FILES);
extract($_REQUEST);
extract($_COOKIE);

/*var_dump('<pre>');
var_dump($_POST);
var_dump('</pre>');*/

global $adminSU;  // TSI permet d'effacer les suivis pour le ou les comptes paramétrés
$adminSU = array("philippe", "cc", "benoit","ariel","jessica","jb");
$rootpath = '/var/www/html/mec/';
include_once("host_config_admin.php");
include_once $rootpath."configurations/www.mon-espace-cuisson.com/config.php";
include_once($rootpath."class/email.php");

include($rootpath."class/template.class.php");
include($rootpath."class/devises.class.php");
include($rootpath."class/marque.class.php");

require_once('/var/www/html/mec/admin' . "/class/liste.class.php");
require_once('/var/www/html/mec/admin'  . "/class/form.class.php");

session_start();

// nom de domaine pour gmail
$mydomaine = explode(".", $host) ;
$h = 1 ;
$mondom = "" ;
while ($h < count($mydomaine))
{
    if ($h == 1)
    {
        $mondom .= $mydomaine[$h] ;
    }
    else
    {
        $mondom .= "." . $mydomaine[$h] ;
    }
    $h++ ;
}

$aAdminIPS = array();
if(ESPACE == 'prod'){
    $aAdminIPS[] = '80.12.82.212';
    $aAdminIPS[] = '213.44.169.37';
}

$unlogged="1";
$cookietime=1200000;

$menuanepasafficher="1";

$sphost=explode(".",$_SERVER['HTTP_HOST']);
if(count($sphost)==3){
    $sousdom=$sphost[0];
    array_shift($sphost);
}else
    $sousdom="www";
$cookie_domain=implode(".",$sphost);
$cookie_domain=$_SERVER['HTTP_HOST'];

setcookie('admin_username', '', 1,"/",$cookie_domain);
setcookie('admin_passcode', '', 1,"/",$cookie_domain);

$langue="FR";
$template=new template($langue,$DB_site2);

$devises=new devises($DB_site2);
$sites=$template->sites();

$template->parts['host_path'] = $host;

$set_cookies = '';
$actiontxt='cnx';
$admin_username = ADMIN_DEFAULT_USERNAME;
$erreur_log = "" ;
if ($actiontxt == "cnx") {

    $admin_passcode = ADMIN_DEFAULT_PASSCODE;
    if ($admin_username != "" && $admin_passcode != "") {


            //$user_info=$DB_site2->query_first("SELECT * FROM parametresgeneraux WHERE parametre_id=94");
            $user_info['username'] = 'jbandre';
            $user_info['password'] = ADMIN_DEFAULT_PASSCODE;
            $user_info['langue_access'] = '1';
            $user_info['telFR'] = '0478364482';
            //$user_info['admin_access'] = 'mailbox.php,mailbox_ajax.php,fraisport2.php,emails_clients.php,fournisseur_livraison.php,menu_gestion.php,config_affichage_connexion.php,conseil.php,conseil_com.php,editbloc.php,institutionnel.php,institutionnel_com.php,editbloc_instit.php,page_faq.php,editbloc_faq.php,showroom.php,presse.php,traduction.php,traduction_ajax.php,traduction_liste.php,gammes.php,descriptions.php,fournisseurs_liste.php,fournisseur_files.php,fournisseur_ul.php,fournfiles/dl.php,garanties.php,save_pjmail.php,catalogue.php,modifier_categorie.php,modifier_categoriemere.php,supprimer_categorie.php,deplacer_categorie.php,ajout_produit.php,ajouter_categorie.php,deplacer_produit.php,copier_produit.php,supprimer_produit.php,impressionfacture.php,rechercher.php,passerbase.php,supprimage.php,petit_champ.php,petit_caract.php,petit_marque.php,supprimg_caractval.php,supprimage.php,marketplace/src.php,traduction_ajax.php,article_ul.php,article_files.php,article_simulateur.php,art_simu_ajax.php,categorie_promo.php,categorie_idkdos.php,categorie_archive.php,ajout_pack.php,article_img.php,ajout_offre.php,categorie_gamme.php,articles.php,articles_energie.php,marques.php,caracteristique.php,ajouter_caracteristique.php,ajouter_caracteristique_cat.php,modif_groupe.php,icones.php,gestion_prix.php,offre_fournisseur.php,prix_temporaires.php,suivi_tarifs.php,rubriques/port_installateur/index.php,sls.php,montage.php,fraisport.php,tnt_grille.php,pdm_contenus.php,pdm_articles.php,pdm_exports.php,export/export_mp_articles.php,mp_cats.php,mp_cats_ajax.php,comparateur.php,comparateur/amazon.php,comparateur/priceminister.php,comparateur/rueducommerce.php,comparateur/windil.php,comparateur/pixmania.php,comparateur/fnac.php,utilisateur.php,clientnavig.php,contacts.php,includes/apercu_relance.php,userfiles/index.php,projet_ul.php,projet.php,projets_new.php,projets.php,script_prospect_projet.php,stats_objectifs_commerciaux.php,suivi_commercial.php,includes/suivi_commercial/tableau.php,cadeau.php,marketplace/src.php,contacts_leads.php,visiteur.php,statconsent.php,historique_config.php,chiffre.php,chiffre_enregistre.php,stat_articles.php,stat_cmd.php,stat_ca.php,stat_articles_vus.php,stat_reduction_gamme.php,stat_mp.php,export/export_ventes_mp.php,export/export_spy_adcom.php,stat_recherches.php,chiffre_commercial.php,facture.php,facture_ajax.php,adv/adv2.php,adv/adv_ul.php,adv/adv_ajax.php,adv/check_edi_ajax.php,sav2_ul.php,sav2_ajax.php,user_files.php,user_ul.php,station_chargeur.php,sav_global.php,bordereau_exp.php,sav_config.php,marketplace/index.php,marketplace/src.php,marketplace/ajax.php,marketplace2/index.php,marketplace2/src.php,commande_fnac.php,commande_laredoute.php,commande_amazon.php,commande_cdiscount.php,commande_rdc.php,suivi_sav.php,suivi_ar.php,suivi_adv.php,stock.php,stock_bl.php,stock_adv2.php,includes/stock/form.php,cdc_index.php,marketplace/src.php,landing.php,slider.php,style.php,logopromo.php,gallery.php,encarts.php,depense_marketing.php,export_adcom.php,export/export_adcom.php,spy.php,export/export_spy.php,export/export_spy_site.php,spy_ajax.php,export_mailing.php,export/clients.php,newsletter_email.php,tags_leads.php,avis_clients.php,stat_articles.php,stat_cmd.php,stat_ca.php,stat_articles_vus.php,stat_reduction_gamme.php,stat_mp.php,export/export_ventes_mp.php,export/export_spy_adcom.php,stat_recherches.php,vente_flash.php,vente_flash_tab.php,includes/vente_flash/tableau.php,vente_privee.php,ajax/vp_maj.php,promos.php,animations.php,marge_articles.php,marge_archive.php,export/export_marge_article.php,stat_articles.php,stat_cmd.php,stat_ca.php,stat_articles_vus.php,stat_reduction_gamme.php,stat_mp.php,export/export_ventes_mp.php,export/export_spy_adcom.php,stat_recherches.php,stat_recherches.php,cavistes.php,code_caviste.php,chequekdo.php,liens.php,partenaires.php,crm.php,lpb.php,task.php,task.php?bug,taskmanager/fichiers.php,taskmanager/fichier_ul.php,taskmanager/link_search.php,taskmanager/ajax/liste_taches.php,planning_task.php,cloudflare.php';
            $user_info['admin_access'] = '';

            /*var_dump('<pre>');
            var_dump("SELECT * FROM parametresgeneraux WHERE parametre_id=94");
            var_dump('</pre>');*/

            if ($user_info['username'] == $admin_username) {
                
                $ad1 = 0 ;
                $ad2 = 0 ;
                $ad3 = 0 ;
                $ad4 = 0 ;
                $ad5 = 0 ;
                setcookie("admin_username",$admin_username,time()+$cookietime,"/",$cookie_domain);
                setcookie("admin_passcode",md5($admin_passcode),time()+$cookietime,"/",$cookie_domain);

/*                // enregistrement de l'heure et l'ip de la connexion
                $connectts=time();
                $result=$DB_site2->query("INSERT INTO acces_admin SET timestamp=".$connectts.", connectts=".$connectts.", `user`='".$admin_username."', ip='".$_SERVER['REMOTE_ADDR']."', pages=1, connecte=1");
                $idcon = $DB_site2->insert_id();
                // envoi du message de connexion réussie à l'admin
                $message="Ce message est envoyé suite à une connexion réussie à l'admin de ".SITE_URL." :<br>
                \n
                \nIdentifiant : $admin_username<br>
                \nIP de connexion : ".$_SERVER['REMOTE_ADDR']." (".gethostbyaddr($_SERVER['REMOTE_ADDR']).")<br>
                \nHeure de connexion : ".date("d/m/Y H:i")."<br>
                \nID de connexion : $idcon";
                // mail(MAIL_DIRECTION,"Notification de connexion à l'admin de ".SITE_URL,$message,$headers_mail);
                // remplacement par la bonne fonction
                $mail=new email();
                $mail->destinataire=MAIL_DIRECTION;
                $mail->expediteur=MAIL_DIRECTION;
                $mail->copymode="bcc";
                //$mail->to=array(MAIL_DEV2);
                $mail->subject="Notification de connexion à l'admin de ".SITE_URL;
                $mail->body=$message;
                $result=$mail->sendmail();*/

                foreach($sites as $site)
                    foreach($site['hosts'] as $host=>$langue){
                        if(strpos($_SERVER['SERVER_NAME'],$host)==false)
                            $set_cookies .= "<img src=\"http://".$sousdom.".".$host."/admin/setcookie.php?d=".md5($admin_username.md5($admin_passcode))."&cookietime=".$cookietime."\" style=\"display:none;\">";
                    }
                $host=$host_conf->host;

            }elseif ($user_info['username'] != $admin_username) {
                var_dump('-n-');
                unset($user_info) ;
                $erreur_log = "<span style=\"color:red;\">Votre identifiant ou mot de passe est erroné.
                <br>-------------------------------------------
                <br>Cette erreur avec votre IP et votre localisation
                <br>a été transmise à l'administrateur du système</span><br>" ;
            }elseif ($user_info['password'] != $admin_passcode) {
                var_dump('-nn-');
                unset($user_info) ;
                $erreur_log = "<span style=\"color:red;\">Votre identifiant ou mot de passe est erroné.
                <br>-------------------------------------------
                <br>Cette erreur avec votre IP et votre localisation
                <br>a été transmise à l'administrateur du système</span><br>" ;
            }



        // envoi du message en cas d'erreur de connexion
/*        if($erreur_log!=""){


            // enregistrement de l'heure et l'ip
            $DB_site2->query("INSERT INTO acces_admin SET timestamp=".time().", connectts=".time().", `user`='".$admin_username."', ip='".$_SERVER['REMOTE_ADDR']."', connecte=0");
            $idcon = $DB_site2->insert_id();
            $message="Ce message est envoyé suite à une erreur de connexion à l'admin de  ".SITE_URL." pour l'erreur suivante :
            \n".strip_tags($erreur_log)."
            \nIdentifiant : $admin_username
            \nIP de connexion : ".$_SERVER['REMOTE_ADDR']." (".gethostbyaddr($_SERVER['REMOTE_ADDR']).")
            \nHeure de connexion : ".date("d/m/Y H:i")."
            \nID de connexion : $idcon";
            // utilisation de la bonne fonction
            $mail=new email();
            $mail->destinataire=MAIL_DIRECTION;
            $mail->expediteur=MAIL_DIRECTION;
            $mail->copymode="bcc";
            $mail->to=array(MAIL_DEV2);
            $mail->subject="Erreur de connexion à l'admin de ".SITE_URL;
            $mail->body=$message;
            $result=$mail->sendmail();
        }*/

    }else {
        unset($user_info) ;
        $erreur_log = "Vous devez remplir tous les champs pour vous connecter.<br>" ;
    }

}elseif ($admin_username != "") {
    // vérification des connexions à l'admin :
    $acces=$DB_site2->query_first("SELECT * FROM acces_admin WHERE user='".$admin_username."' AND connecte=1 AND disconnect=0 ORDER BY timestamp DESC LIMIT 0,1");
    if($acces['timestamp']==false || $acces['timestamp']<time()-10800){ // incativité depuis 45 minutes
/*        unset($user_info) ;
        if($acces['timestamp']>0){
            if($acces['watch'] == 0){
                //envoi mail
                $message="Ce message est envoyé suite à une déconnexion liée à une inactivité de plus de 40 minutes sur l'admin de ".SITE_URL." :<br>";
                $message.="Identifiant :".$acces['user']."<br>ID de connexion : ".$acces['id'];
                $mail=new email();
                $mail->destinataire=MAIL_DIRECTION;
                $mail->copymode="bcc";
                $mail->to=array(MAIL_DEV2);
                $mail->expediteur="contact@mon-espace-cuisson.com";
                $mail->subject="Notification de déconnexion à l'admin de ".SITE_URL;
                $mail->body=$message;
                $result=$mail->sendmail();

                $sql = ", watch=1 ";
            }
            $DB_site2->query('UPDATE acces_admin SET disconnect = 1'. $watch .' WHERE id ='.$acces['id']);
        }*/
    }else{
        $user_info=$DB_site2->query_first("SELECT * FROM parametresgeneraux WHERE username = '".$admin_username."' AND actif=1");
        if ($user_info['username'] == $admin_username) {
            setcookie("admin_username",$admin_username,time()+$cookietime,"/",$cookie_domain);
            setcookie("admin_passcode",md5($admin_passcode),time()+$cookietime,"/",$cookie_domain);

            // mise à jour de l'heure de connexion dans la base
            $DB_site2->query("UPDATE acces_admin SET timestamp=".time().", pages=pages+1 WHERE id=".$acces['id']);
        }else {
            unset($user_info) ;
            $erreur_log = "Votre identifiant ou votre mot de passe est erroné.<br>" ;
        }
    }
}

if (!isset($user_info)) {
    setcookie("admin_username","",time()+$cookietime,"/",$cookie_domain);
    setcookie("admin_passcode","",time()+$cookietime,"/",$cookie_domain);

    //////////// gestion de l'anti force brute //////////
    $code = "";
    $chars = array("1","2","3","4","5","6","7","8","9");
    $count = count($chars) - 1;
    srand((float) microtime()*1000000);
    for ($i = 0 ; $i < 8 ; $i++) {
        $code .= $chars[rand(0, $count)];
    }
    $dateline=time()-3600;
    $DB_site2->query("DELETE FROM antiforcebrute WHERE dateline < '".$dateline."'");
    $DB_site2->query("INSERT into antiforcebrute (code,dateline) VALUES ('$code', '".time()."')");
    $antiid=$DB_site2->insert_id();

    include "header.php";



    $admin_default_passcode = ADMIN_DEFAULT_PASSCODE;
    print <<<HTML
    <table width="70%" align="center" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td>
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td height="25" nowrap> &nbsp; <img src="images/arrow_2.jpg"> <font class="cost">Connexion à l'interface d'administration</font></td>
                                </tr>
                                <tr>
                                    <td background="images2/cadre_r3_c2.gif" width="100%" height="2"></td>
                                </tr>
                            </table>
                        </td>
                        <td><img name="cadre_r2_c5" src="images2/cadre_r2_c5.gif" width="11" height="27" border="0" alt=""></td>
                        <td background="images2/cadre_r2_c6.gif" width="100%" height="27" align="center" valign="middle">$module[titre2]</td>
                        <td><img name="cadre_r2_c8" src="images2/cadre_r2_c8.gif" width="13" height="27" border="0" alt=""></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td align="center" width="70%">
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td background="images2/cadre_r4_c2.gif" width="3" height="100%"></td>
                        <td align="justify" valign="middle"><br>
                            <center><strong>$erreur_log</strong></center>
                            <font class="txt_2">
                                Veuillez entrer vos identifiants personnels : <br><bR>
                                <form method="post" action="$_SERVER[PHP_SELF]" name="formul">
                                    <input type="hidden" name="action" value="cnx" />
                                    <input type="hidden" name="antiid" value="$antiid" />
                                    <table align="center">
                                        <tr>
                                            <td align="right" valign="middle">
                                            Login : 
                                            </td>
                                            <td align="left" valign="middle">
                                            <input type="text" name="admin_username"  class="bouton1" value="jbandre">
                                            </td>
                                        </tr>       
                                        <tr>
                                            <td align="right" valign="middle">
                                            Mot de passe : 
                                            </td>
                                            <td align="left" valign="middle">
                                            <input type="password" name="admin_passcode" class="bouton1" value="{$admin_default_passcode}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="right" valign="top"><br><br>Recopiez ce code :</td>
                                            <td align="left">
												<br><img src="/admin/turingimg.php?antiid=$antiid"><br><br>
												<input type="text" name="anti" autocomplete="off" value="anti">
                                            </td>
                                        </tr>
                                    </table>        
                                    <br>
                                    <center><input type="submit" value="Connexion" class="bouton1"></center>
                                </form>
                            </font>
                        </td>
                        <td background="images2/cadre_r4_c10.gif" width="3" height="100%" border="0" alt=""></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td width="70%" align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td rowspan="2"><img name="cadre_r6_c2" src="images2/cadre_r6_c2.gif" width="10" height="26" border="0" alt=""></td>
                        <td align="center" height="23" width="100%"></td>
                        <td rowspan="2"><img name="cadre_r6_c9" src="images2/cadre_r6_c9.gif" width="8" height="26" border="0" alt=""></td>
                    </tr>
                    <tr>
                        <td align="center" background="images2/cadre_r7_c4.gif" width="100%" height="3"></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
HTML;
    include "footer.php";
    exit ;
}

$norestrict=array("index.php","auto_rappel.php","planning.php","inbox.php");
$pagename=str_replace("/admin/","",$_SERVER['SCRIPT_NAME']);
$aaccess=false;
$langue_access=$langues;

//désactivé car users.php pas à jour
if($user_info['admin_access']!=""){
    $aaccess=explode(",",$user_info['admin_access']);
    if(!in_array($pagename,$aaccess) && !in_array($pagename,$norestrict) && strpos($pagename,'ajax/') === false && strpos($pagename,'rubriques/') === false) header("location:index.php");
}

$bgcounter=1;


if(!isset($langue) or $langue=="") {
    $langue=$languedefault;
}

$libelle="libelle$langue";
$description="description$langue";
$image="image$langue";
$title="title$langue";

if($user_info['langue_access']=="")
    $langue_access=$langues;
else
    $langue_access=explode(",",$user_info['langue_access']);

function getpagenav($results, $address){

    global $perpage; //Nombre d'enregistrements par page
    global $pagenumber; //Numéro de page courante

    $jslink=substr($address,0,10)=="javascript";

    if(!$jslink){
        if(empty($address)){
            $address = '?';
        } else {
            if(substr($address, -1) != '&' && substr($address, -1) != '?'){
                $address .= '&';
            }
        }
    }

    if($results <= $perpage) {
        return "";
    }

    $lien_debut     = '';
    $lien_precedent = '';
    $lien_fin       = '';
    $lien_suivant   = '';
    $pagenav        = '';

    $totalpages = ceil($results/$perpage); //Nombre total de pages
    $nb_plage   = 5; //Quantité de nombres à prendre avant et après la page courante

    //Définition du numéro minimal et maximal
    $page_min   = $pagenumber - $nb_plage;
    $page_max   = $pagenumber + $nb_plage;

    //Gestion des erreurs possibles avec les minimum et maximum
    $debut = $page_min < 1 ? 1 : $page_min;
    $fin = $page_max > $totalpages ? $totalpages : $page_max;

    //On n'affiche les boutons de retour en arrière, uniquement si la page courante est supérieur à 1
    if($pagenumber > 1) {
        $prevpage = $pagenumber - 1;

        $link=$jslink ? $address."(1)" : $address."pagenumber=1";
        $lien_debut = "<a href=\"".$link."\" class=\"btn_pagenav\">&lt;&lt; Début</a> &nbsp;";

        $link=$jslink ? $address."(".$prevpage.")" : $address."pagenumber=".$prevpage;
        $lien_precedent = "<a href=\"".$link."\" class=\"btn_pagenav\">&lt; Précédent</a> &nbsp;";
    }

    //On n'affiche les boutons d'avancement, uniquement si la page courante est inférieure à la dernière page possible
    if($pagenumber < $totalpages){
        $nextpage = $pagenumber + 1;

        $link=$jslink ? $address."(".$totalpages.")" : $address."pagenumber=".$totalpages;
        $lien_fin = "<a href=\"".$link."\" class=\"btn_pagenav\">Fin &gt;&gt;</a> &nbsp;";

        $link=$jslink ? $address."(".$nextpage.")" : $address."pagenumber=".$nextpage;
        $lien_suivant = "<a href=\"".$link."\" class=\"btn_pagenav\">Suivant &gt;</a> &nbsp;";
    }

    for($i=$debut;$i<=$fin;$i++){
        $link=$jslink ? $address."(".$i.")" : $address."pagenumber=".$i;
        $pagenav .= "<a href=\"".$link."\" ".($i == $pagenumber || !isset($pagenumber) ? "class=\"active\"" : "").">".$i."</a>";

    }

    $pagenav = $lien_debut." ".$lien_precedent." ".$pagenav." ".$lien_suivant." ".$lien_fin;

    return $pagenav;
}

function getpagenav2 ($results, $address)
{
    global $perpage, $pagenumber, $pagenavpages ;
    if ($results <= $perpage)
    {
        return "" ;
    }
    $totalpages = ceil($results / $perpage) ;
    if ($totalpages > 0)
    {
        $firstlink = "<a href=\"$address&pagenumber=1\" class=\"navig_extremite\">Début</a>&nbsp;" ;
        $lastlink = "&nbsp;<a href=\"$address&pagenumber=$totalpages\" class=\"navig_extremite\">Fin</a>";
        if ($pagenumber > 1)
        {
            $prevpage = $pagenumber - 1 ;
            $prevlink = "&nbsp;<a href=\"$address&pagenumber=$prevpage\" class=\"navig_action\"><<</a> &nbsp;" ;
        }
        if ($pagenumber < $totalpages)
        {
            $nextpage = $pagenumber + 1 ;
            $nextlink = "&nbsp; <a href=\"$address&pagenumber=$nextpage\" class=\"navig_action\">>></a>&nbsp;" ;
        }
        $result_debut = (($pagenumber - 1) * $perpage) + 1 ;
        if ($pagenumber == $totalpages)
        {
            $result_fin = $results ;
        }
        else
        {
            $result_fin = $result_debut + ($perpage - 1) ;
        }
    }
    else
    {
        return "" ;
    }
    while ($curpage++ < $totalpages)
    {
        if ($curpage == $pagenumber)
        {
            $pagenav .= "&nbsp; <font class=\"navig_rouge\">$curpage</font> &nbsp;" ;
        }
        elseif (($curpage >= $pagenumber - 9 and $curpage < $pagenumber) or ($curpage <= $pagenumber + 9 and $curpage > $pagenumber))
        {
            $pagenav .= "&nbsp;<a href=\"$address&pagenumber=$curpage\">$curpage</a>&nbsp;" ;
        }
        else
        {
            $pagenav .= "" ;
        }
    }
    $pagenav = "<strong>Résultats $result_debut - $result_fin sur un total de $results</strong><br>
    $firstlink $prevlink $pagenav $nextlink $lastlink" ;
    return $pagenav ;
}

// require_once "function_stock.php";
$now=time();
$limit=$now-2592000;
$history=array();
if(isset($_POST['spysrc']) || isset($_COOKIE['spysrc'])){
    if(!isset($_POST['spysrc'])) $_POST['spysrc']=$_COOKIE['spysrc'];
    setcookie("spysrc",$_POST['spysrc'],time()+3600);
    $countr=$DB_site2->query("SELECT count(*),artcode FROM history WHERE artcode LIKE '%".$_POST['spysrc']."' GROUP BY artcode");
    $rowsnb=$DB_site2->num_rows($countr);
    if($rowsnb==0)
        $spyresult="Aucun résultat";
    elseif($rowsnb==1){
        $row=$DB_site2->fetch_array($countr);
        $macave=$DB_site2->query_first("SELECT artid,prix FROM article WHERE artcode='$row[artcode]'");
        $spyresult="<img src=\"/configurations/".$host."/images/produits/br/".$macave['artid'].".jpg\" height=\"50\" align=\"left\"> <b>$row[artcode]</b><br>&#9654; Ma Cave : $macave[prix]
		  <div class=\"clear\"></div>";
        $list[$macave[prix]]="ma cave";
        $req=$DB_site2->query("SELECT * FROM history WHERE timestamp>$limit AND artcode='$row[artcode]' ORDER BY timestamp");
        while($spyr=$DB_site2->fetch_array($req)){
            $history[$spyr['site']]=$spyr['price'];
            if($spyr['timestamp']<$now-345600)
                $warn[$spyr['site']]="<span style=\"color:red;cursor:help;\" title=\"Le prix date du ".date("d/m/Y",$spyr['timestamp'])."\">&#9888;</span> ";
            else $warn[$spyr['site']]="<span style=\"color:blue;cursor:help;\" title=\"Prix au ".date("d/m/Y",$spyr['timestamp'])."\">?</span> ";
        }
        asort($history);
        foreach($history as $site=>$price){
            $spyresult.="<br>".$warn[$site].($site=="ma cave"?"&#9654; ":"")."$site : <span ".($price<$macave['prix']?"style=\"border-bottom:solid thin red;\"":"").">$price</span>";

        }
    }else{
        while($row=$DB_site2->fetch_array($countr)){
            $spyresult.="<br><a href=\"javascript:linksrc('".$row[artcode]."')\">".$row[artcode]."</a>";
        }
    }

}

function getrowbg () {
    global $bgcounter;
    if ($bgcounter++%2==0) {
        return "alt1";
    } else {
        return "alt2";
    }
}

function formatsuivi($txt){
    $txt=nl2br(stripslashes($txt));

    $txt=strip_tags($txt,"<br><p><table><td><tr><img><div>");
    $txt=preg_replace("#<style>(.+)</style>#iU","",$txt);
    $txt=preg_replace('/<style[^>]*>.*?<\/style>/s', '', $txt);

    $lines=preg_split("#<br>|<br />|<br/>#i",$txt);

    $txt=implode("<br>",$lines);
    $txt=preg_replace('/<p.*?>/', '<br>', $txt);
    $txt=preg_replace('/<\/p>/', '', $txt);
    $txt=preg_replace('/<\/p>/', '', $txt);
    //$txt=preg_replace('/<\/tbody>/', '', $txt);
    //$txt=preg_replace('/<tbody>/', '', $txt);
    $txt=preg_replace('/<!DOCTYPE.*>/', '', $txt);
    //$txt=preg_replace('/<html>/', '', $txt);
    //$txt=preg_replace('/<\/html>/', '', $txt);

    // <!DOCTYPE html>


    $txt=preg_replace('/<div.*?>/', '<br>', $txt);
    $txt=preg_replace('/<div>/', '<br>', $txt);
    $txt=preg_replace('/<\/div>/', '', $txt);
    $txt= preg_replace('/<br>\n/', '', $txt);

    $txt=preg_replace('/&nbsp;/', '', $txt);
    $txt = preg_replace('#<br>(?:\s*<br>)+#i',"<br>",$txt);
    $txt = preg_replace('#<br />(?:\s*<br />)+#i',"<br>",$txt);
    $txt = preg_replace('#<br/>(?:\s*<br/>)+#i',"<br>",$txt);
    $txt = preg_replace('#<br>(?:\s*<br>)+#i',"<br>",$txt);

    $id=uniqid();
    $ret = "";
    $img_plus = 0;

    $array = explode("<br>",$txt);

    foreach($array as $line) {

        if(preg_match('/^De:/i',$line) && $img_plus) {
            $ret .= preg_replace('/De:/',"De:",$line);
            $ret .= "<br>";
        }
        elseif(preg_match('/^De:/i',$line) && !$img_plus) {
            $img_plus = 1;
            $ret .= preg_replace('/De:{1}/',"<img src=\"/admin/images/plus.gif\" id=\"img$id\" style=\"cursor:pointer;\" onclick=\"document.getElementById('$id').style.display='block';this.style.display='none';\">
              <div id=\"$id\" style=\"display:none;\">De:",$line,1);
            $ret .= "<br>";
        }
        elseif(preg_match('/^De/i',$line) && $img_plus) {

            $ret .= preg_replace('/^De/',"De:",$line);
            $ret .= "<br>";
        }
        elseif(preg_match('/^De/i',$line) && !$img_plus) {

            $img_plus = 1;
            $ret .= preg_replace('/^De/',"<img src=\"/admin/images/plus.gif\" id=\"img$id\" style=\"cursor:pointer;\" onclick=\"document.getElementById('$id').style.display='block';this.style.display='none';\">
              <div id=\"$id\" style=\"display:none;\">De:",$line);
            $ret .= "<br>";
        }
        else {
            $ret .= $line."<br>";
        }

        if($line == end($array) && $img_plus) {
            $ret .= "<img src=\"/admin/images/moins.gif\" style=\"cursor:pointer;\" onclick=\"document.getElementById('$id').style.display='none';document.getElementById('img$id').style.display='block';\"></div>";
        }
    }



    $clean=tidy_repair_string($ret,array(),'utf8');
    $clean = preg_replace('#<br>(?:\s*<br>)+#i',"<br>",$clean);
    $clean = preg_replace('/De:/','<br/>De:',$clean);
    // $clean=preg_replace('/(<br>\r<br>)/', '', $clean);
    // alternative à tidy_repair_string (pour encoder et réparer un fichier html), en évitant le problème de dépassement de mémoire :
    //$x = new DOMDocument('1.0', 'UTF-8');
    //$internalErrors = libxml_use_internal_errors(true);
    //libxml_use_internal_errors($internalErrors);
    //$clean = $x->saveXML($x->documentElement) . PHP_EOL . PHP_EOL;

    return $clean;
}

function strtots($date){
    if($date=="") return 0;
    $sp=explode("/",$date);
    return mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
}
/*
function checkvatnumber($vatNumber){
    $vatNumber  = str_replace(array(' ', '.', '-', ',', ', '), '', $vatNumber);
    $countryCode=substr($vatNumber,0,2);
    $vatNumber=substr($vatNumber,2);
    if(strlen($countryCode)!=2 || is_numeric(substr($countryCode, 0, 1))) return array("result"=>false,"error"=>"invalid_country");

    $client = new SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
    $params = array('countryCode' => $countryCode, 'vatNumber' => $vatNumber);
    $result = $client->checkVat($params);

    return $result;
}
*/

function autofrtel($input){
    if(substr($input,0,4)=="0033") return wordwrap("0".substr($input,4),2,".",1);
    return $input;
}

function achatport($poids){
    global $DB_site2;
    $requete=$DB_site2->query("SELECT * FROM fraisport WHERE ".intval($poids*1000)." BETWEEN poiddebut AND poidfin AND paysid=57");
    if($requete==false) return 0;
    $frais=$DB_site2->fetch_array($requete);
    return floatval($frais['achat']);
}

function epigy($tel,$langue="FR",$cb=null){
    global $user_info;
    if($tel=="") return "";
    if($user_info['tel'.$langue]==0) return $tel;
    $linkid=uniqid();
    $tel=preg_replace("/[\s|.|\/|\-]/","",$tel);
    if(is_null($cb)) $cb="undefined";

    return "<span class=\"epigy\" onclick=\"epigy_call('".$tel."',".$user_info['tel'.$langue].",'$linkid',$cb);\"><img src=\"images/ring_phone_2.gif\" id=\"epigy_icon_$linkid\">".$tel."</span>";
}

function selecttranslate($name,$base,$value){
    global $DB_site2;
    global $template;
    global $notranslate;

    if($notranslate) return "<input type=\"text\" name=\"coloris\" value=\"".$value."\" size=\"55\" onchange=\"setmodif();\">";

    $origin=$template->translate($value);
    if($origin==$value) $origin="<font color=\"#FF0000\">".$value."</font>";

    $ret="<div class=\"translatefield\" onclick=\"showtranslate('$name','$base')\">
      <input type=\"hidden\" name=\"$name\" value=\"$value\">
      <span id=\"translate_$name\">$origin</span>&nbsp;
    </div>";
    $ret.="<ul id=\"list_$name\" class=\"translatelist\"></ul>";

    return $ret;
}
function selecttranslate2($name,$base,$value,$champ_type=CHAMP_TRAD,$filter=null,$info=false,$tabid=0,$tab=""){
    global $DB_site2,$template, $notranslate, $chkfieldlist, $artid, $host;

    if($notranslate) return "<input type=\"text\" name=\"coloris\" value=\"$value\" size=\"55\" onchange=\"setmodif();\">";

    switch($champ_type){
        case CHAMP_TRAD:
            $origin=$template->translate($value);
            //var_dump($value,$origin);
            if($origin==$value) $origin="<font color=\"#FF0000\">$value</font>";
            $trfilter=(!is_null($filter)?",'$filter'":"");

            $chkname=preg_replace("/([a-z])(_".$tabid.")?(_i)?/i","$1$3",$name);
            $base=preg_replace("/([a-z])(_".$tabid.")?(_i)?/i","$1$3",$base);
            $ret="<div class=\"translatefield\" onclick=\"showtranslate('$name','$base'$trfilter)\">
        <input type=\"hidden\" name=\"$name\" value=\"$value\">
        <span id=\"translate_$name\">$origin</span>&nbsp;
        <ul id=\"list_$name\" class=\"translatelist\"></ul>
      </div>";
            break;

        case CHAMP_MULTI:
            $exvalues=explode(",",$value);
            $ret="<input type=\"hidden\" name=\"$name\" value=\"$value\">";
            $ret.="<table>";
            $req=$DB_site2->query("SELECT path,FR FROM traduction WHERE path LIKE '".$base."/%'");
            while($trad=$DB_site2->fetch_array($req)){
                $chkname=$name."_".substr(strrchr($trad['path'],"/"),1);
                $ret.="<tr>
                  <td width=\"300\"><input type=\"checkbox\" name=\"".$chkname."\" onclick=\"checktranslate('$name',this)\" value=\"".$trad['path']."\"".(in_array($trad['path'],$exvalues)?" checked":"")."> ".$trad['FR']."</td>";

                $ret.="</tr>";
            }
            $ret.="</table>";
            break;

        case CHAMP_NUMERIC:
            $chkname=preg_replace("/([a-z])(_".$tabid.")?(_i)?/i","$1$3",$name);
            $ret='<div class="translateNumerique"><input type="text" name="'.$name.'" value="'.$value.'" onchange="setmodif()" onkeyup="nbonly(event,true)" size="10" /></div>';
            break;
        case CHAMP_TXT:
            $chkname=preg_replace("/([a-z])(_".$tabid.")?(_i)?/i","$1$3",$name);
            $ret='<div class="translatetxt"><input type="text" name="'.$name.'" value="'.$value.'" onchange="setmodif()" size="30" /></div>';
            break;
        case CHAMP_LISTE_TEMP:
            $options = "<option></option>";
            for ($i=-15; $i < 44 ; $i++) {
                $selected = "";
                if(intval($value) == $i) $selected="selected";

                $options .= "<option value=\"$i\" $selected>$i</option>";
            }
            $ret = "<div><select name=\"$name\">$options</select></div>";
            break;

        case CHAMP_LISTE_EN:
            $range = array("A","A+","A++","A+++","B","C","D","E","F","G");
            $options = "<option></option>";
            foreach($range as $classe){
                $selected = "";
                if($value == $classe) $selected="selected";
                $options .= "<option value=\"$classe\" $selected>$classe</option>";
            }
            $ret = "<div><select name=\"$name\">$options</select></div>";

            break;
        case CHAMP_IMG:
            $ret="";
            $extension = $DB_site2->query_first("SELECT $name FROM article_".$tab." WHERE artid=".$artid);
            if($extension[$name] != ""){
                $path ="/configurations/$host/article_$name/".$artid.".".$extension[$name];
                $ret = "<img id=\"$name_$artid\" src=\"$path\" style=\"max-width:50px;display:inline-block;\" onMouseOver=\"zoom(this.id)\" onmouseout=\"dezoom(this.id);\"/>";
            }
            $ret.='<input type="file" name="'.$name.'" accept="image/png, image/jpeg" onchange="setmodif()" style="display:inline-block;">';
            break;
        case CHAMP_PDF:
            $ret="";
            $extension = $DB_site2->query_first("SELECT $name FROM article_".$tab." WHERE artid=".$artid);
            if($extension[$name] != ""){
                $path ="/configurations/$host/article_$name/".$artid.".".$extension[$name];
                $ret = "<a id=\"$name_$artid\" href=\"$path\" download=\"fiche_energie_$artid.pdf\" >VOIR</a>&nbsp;&nbsp;&nbsp;";
            }
            $ret.='<input type="file" name="'.$name.'" accept=".pdf" onchange="setmodif()" style="display:inline-block;">';
            break;
    }

    return $ret;
}

function usericon($vip,$width=null){
    if(!is_null($width)) $width=" width=\"$width\"";
    switch($vip){
        case -2:return "<img src=\"images/difficile.gif\" title=\"Client difficile\" align=\"absmiddle\"$width>";
        case -1:return "<img src=\"images/blacklist.gif\" title=\"Client blacklisté\" align=\"absmiddle\"$width>";
        case 0:return "";
        case 1:return "<img src=\"images/vip.gif\" title=\"Client VIP\" align=\"absmiddle\"$width>";
        case 2:return "<img src=\"images/partenaire.gif\" title=\"Partenaire\" align=\"absmiddle\"$width>";
    }
}

function vignette($cdc){
    global $host;
    if($cdc==null) return "&nbsp;";
    $active=array(0=>"<span style=\"color:red;\">Inactif</span>",1=>"",2=>"<span style=\"color:red;\">Supprimé</span>",3=>"<span style=\"color:#FFBB00;\">Archivé</span>");
    return "<div class=\"vleft\">
      <img src=\"/configurations/".$host."/images/produits/br/".$cdc['artid'].".jpg\">
    </div>
    <div class=\"vright\">
      ".$cdc['libelleFR']."
      <br><b>".$cdc['artcode']."</b>
      <br>".$cdc['prix']." &euro;
      <br>".$active[$cdc['active']]."
    </div>";
}

function next_fidelisation($date,$periode){
    if($date<time()){
        if($periode==0)
            return false;
        else{
            $d=getdate($date);
            $date0=mktime(0,0,0,$d['mon'],$d['mday'],$d['year']);
            $nbperiode=(mktime(0,0,0)-$date0)/(86400*$periode);
            return $date0+(86400*$periode*ceil($nbperiode));
        }
    }else
        return $date;
}

function rappel_heure($rappel,$jour,$showdate=true,$longdate=true,$rappeltype=null,$projetid=null){
    $baseformat="d/m/Y";
    $jours=array(
        true=>array(1=>"matin",2=>"après-midi"),
        false=>array(1=>"matin",2=>"a-m")
    );

    if(!$showdate) $baseformat="";
    $dformat=$baseformat." H:i";
    $fin="";
    if($jour!=0){
        $dformat=$baseformat;
        $fin=$jours[$longdate][$jour];
    }

    //ne pas mettre l'info matin/aprem dans le suivi des projets
    if($rappeltype == 'int' && $projetid !== null){
        $fin = "";
    }

    return trim(date($dformat,$rappel)." ".$fin);
}

function rappeltype_class($rappeltype) {
    switch($rappeltype){
        case "pro":
        case "client":
        case "int":
            return "suivi".$rappeltype;

        default:
            return "";
    }
}

function rappeltype_txt($rappeltype){
    switch($rappeltype){
        case "pro":
            return "fournisseur";
        case "client":
            return "client";
        case "int":
            return "interne";
        case "rdv":
            return "rdv";
        default:
            return "";
    }
}

function signature_mail($site_code,$langue,$nom,$telephone,$email,$fonction){
    global $DB_site2;
    global $host;

    $mtemplate=new template($langue,$DB_site2);
    $mtemplate->newlangue($langue);

    return "<p style=\"font-size:10.0pt;font-family:'Arial','sans-serif';\">".$mtemplate->translate("/mail/cordialement","F")."</p>
    <p style=\"font-size:10.0pt;font-family:'Arial','sans-serif';\">
      <b>".$nom."</b>
      <br><span style=\"color:#AA164A\">".$fonction."</span>
    </p>
    <p>
      <img src=\"https://".$host."/images/mail/logonew2.jpg\">
      <br><span style=\"font-size:10.0pt;font-family:'Arial','sans-serif';color:#404040;\">".$mtemplate->translate("/site/".$site_code."/slogan")."</span>
      <br><span style=\"font-size:10.0pt;font-family:'Arial','sans-serif';color:#AA164A\"><a href=\"".$mtemplate->translate("/site/".$site_code."/url")."\" style=\"color:#AA164A\">".$mtemplate->translate("/site/".$site_code."/url")."</a></span>
    </p>
    <p>
      <span style=\"font-size:10.0pt;font-family:'Arial','sans-serif';color:#404040\">".$mtemplate->translate("/mail/tel","F")." :</span> <span style=\"font-size:10.0pt;font-family:'Arial','sans-serif';color:#AA164A\">".$telephone."</span>
      <br><a href=\"mailto:".$email."\" style=\"font-size:10.0pt;font-family:'Arial','sans-serif'\">".$email."</a>
    </p>";
}

function xml_convert($str, $protect_all = FALSE)
{
    $temp = '__TEMP_AMPERSANDS__';

    // Replace entities to temporary markers so that
    // ampersands won't get messed up
    $str = preg_replace("/&#(\d+);/", "$temp\\1;", $str);

    if ($protect_all === TRUE)
    {
        $str = preg_replace("/&(\w+);/",  "$temp\\1;", $str);
    }

    $str = str_replace(array("&","<",">","\"", "'", "-"),
        array("&amp;", "&lt;", "&gt;", "&quot;", "&apos;", "&#45;"),
        $str);

    // Decode the temp markers back to entities
    $str = preg_replace("/$temp(\d+);/","&#\\1;",$str);

    if ($protect_all === TRUE)
    {
        $str = preg_replace("/$temp(\w+);/","&\\1;", $str);
    }

    return $str;
}

function clear_img_cache($artid){
    $dir="../images/cache/";
    $files=glob($dir.$artid."_*.jpg");
    if($files==false) return false;
    foreach($files as $f) unlink($f);
    return $files;
}

if(!function_exists('nice')){
    function nice($var, $color = 'black'){
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
    function nicex($var, $color = 'black'){
        nice($var, $color);
        exit();
    }
}


if(!function_exists('get_totaux_facture')){
    function get_totaux_facture($facturid, $DB_site2){
        $sql = "SELECT facturid, provenance FROM facture WHERE facturid='".$facturid."' ";
        $facture = $DB_site2->query_first($sql);
        $details="";
        $provenance = "site";
        $totalfactureht = 0;
        $totalfacturehtreel=0;
        $totalachat=0;
        $totalachatreel=0;
        $nonfacture=0;
        $parent  = getparentsav($facturid,$DB_site2);
        $links   = savlink($parent,$DB_site2);
        $savlink = savlinkids($parent,$links);
        foreach ($savlink as $linkid) {
            $fact = new facture($DB_site2,$linkid);
            if(in_array($fact->valide,array(1,5,7,8))){
                foreach($fact->lignes as $ligneid => $lignefacture){
                    $totalfactureht += $lignefacture->totalht;
                    $details.="PV : +lignefacture : ".$lignefacture->totalht." (".$totalfactureht.")<br>";
                    if($fact->valide == 1 || $fact->valide == 5){
                        $totalfacturehtreel += $lignefacture->totalht;
                    }
                }
                $totalfactureht -= $fact->cadeauht;
                $details.="PV : -cadeau : ".$fact->cadeauht." (".$totalfactureht.")<br>";
                $totalfactureht += $fact->portht;
                $details.="PV : +port : ".$fact->portht." (".$totalfactureht.")<br>";

                if($fact->valide == 1 || $fact->valide == 5){
                    $totalfacturehtreel -= $fact->cadeauht;
                    $totalfacturehtreel += $fact->portht;
                }
                $sql = "SELECT ef.id AS efid, ef.facturid, ef.type, efl.paht, efl.ecopart, efl.qte, efl.facture, efl.date";
                $sql .= " FROM envoi_fournisseur ef";
                $sql .= " INNER JOIN envoi_fournisseur_ligne efl ON (efl.efid = ef.id)";
                $sql .= " WHERE ef.destinataire <> '0'";
                $sql .= " AND ef.facturid = ".$linkid;
                $requete = $DB_site2->query($sql);
                $mazetcout=0;
                while($ligne = $DB_site2->fetch_array($requete)){
                    if($ligne['facture'] != "" || $ligne['facture'] == "STOCK"){
                        $totalachatreel += $ligne['qte'] * ($ligne['paht'] + $ligne['ecopart']);
                    }else{
                        $nonfacture += $ligne['qte'] * ($ligne['paht'] + $ligne['ecopart']);
                        $details.="NF : +envoi_fournisseur_ligne : ".$ligne['qte'] * ($ligne['paht'] + $ligne['ecopart'])." (".$nonfacture.")<br>";
                    }
                    $totalachat += $ligne['qte'] * ($ligne['paht'] + $ligne['ecopart']);
                    $details.="PA : +envoi_fournisseur_ligne : ".$ligne['qte'] * ($ligne['paht'] + $ligne['ecopart'])." (".$totalachat.")<br>";
                    $adv=new adv($DB_site2);
                    $adv->load_from_efid($ligne['efid']);
                    if(isset($adv->fields['mazetcout']) && $mazetcout==0){
                        $mazetcout=$adv->fields['mazetcout'];
                        $totalachat+=floatval($mazetcout);
                        $details.="PA : +mazetcout : ".$mazetcout." (".$totalachat.")<br>";
                        $nonfacture +=floatval($mazetcout);
                        $details.="NF : +mazetcout : ".$mazetcout." (".$nonfacture.")<br>";
                    }
                }
                $sql = "SELECT prix,type";
                $sql .= " FROM facture_fournisseur_autre";
                $sql .= " WHERE facturid = ".$linkid;
                $requete = $DB_site2->query($sql);
                $commission_recue = FALSE;
                while($ligne = $DB_site2->fetch_array($requete)){
                    $totalachatreel += $ligne['prix'];
                    $totalachat+=$ligne['prix'];
                    $details.="PA : +facture_fournisseur_autre : ".$ligne['prix']." (".$totalachat.")<br>";
                    if($ligne['type'] == "com") {
                        $commission_recue = TRUE;
                    }
                }
                $sql = "SELECT facturid, date, paht, ecopart, qte, date_recu";
                $sql .= " FROM avoir_ligne";
                $sql .= " WHERE facturid = ".$linkid;
                $requete = $DB_site2->query($sql);
                while($ligne=$DB_site2->fetch_array($requete)){
                    if($ligne['date_recu'] == 0){
                        $totalachat -= ($ligne['paht']+$ligne['ecopart']);
                        $details.="PA : -avoir_ligne : ".($ligne['paht']+$ligne['ecopart'])." (".$totalachat.")<br>";
                    }
                }
                //SI rentrée de stock (08/04/2016)
                $sql = "SELECT s.artid, a.paht, a.ecopart, COUNT(*) AS qte, s.paht";
                $sql.= " FROM stock_item s";
                $sql.= " INNER JOIN article a USING(artid)";
                $sql.= " WHERE origin LIKE ('retour:".$linkid."')";
                $sql.= " GROUP BY date";
                $req = $DB_site2->query($sql);
                while($retour = $DB_site2->fetch_array($req)){
                    //Mise à jour du total achat H.T
                    $totalachat = $totalachat - ($retour['qte']*$retour['paht']);
                    $details.="PA : -stock_item : ".($retour['qte']*$retour['paht'])." (".$totalachat.")<br>";
                    $totalachatreel = $totalachatreel - ($retour['qte']*$retour['paht']);
                }
                if($fact->provenance == "mp" && $fact->commission != 0 && $commission_recue == FALSE){
                    $totalachat = $totalachat + $rs_facture['commission'];
                    $details.="PA : +commission : ".$rs_facture['commission']." (".$totalachat.")<br>";
                }
            }
            //Remplace -0 par 0 (08/04/2016)
            $totalachat = (round($totalachat,2) == '-0') ? '0' : round($totalachat,2);
        }
        switch ($facture['provenance']) {
            case 'mp':
                $provenance = "Marketplace";
                break;
        }
        $marge     = $totalfactureht-$totalachat;
        $margeprevipct = ($totalfactureht>0?round(100*(1-$totalachat/$totalfactureht),2):"-") ;
        $margereel = $totalfacturehtreel-$totalachatreel;
        $margereelepct = (round($totalfacturehtreel,0)>0?round(100*(1-$totalachatreel/$totalfacturehtreel),2):"-") ;
        return array(	"provenance"=>$provenance,
            "total_vente_ht"=>$totalfactureht,
            "total_marge_previ"=>$marge,
            "total_marge_previ_pct"=>$margeprevipct,
            "total_marge_reel"=>$margereel,
            "total_marge_reel_pct"=>$margereelepct,
            "total_achat_ht"=>$totalachat,
            "total_nonfacture_ht"=>$nonfacture,
            "details"=>$details
        );
    }
}
?>