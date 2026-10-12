<?php
include("global.php");

if(!isset($_POST['rappelinst_tel']) && !isset($_POST['rappelinst_tel_config']) && !isset($_POST['rappelinst_tel_config_desk'])){
    $template->evaltemplate("RappelInstMsg","rappelinstmsg");
    print stripslashes($template->parts["rappelinstmsg"]);
    exit;
}


//Gestion des options de l'article
$txt_opt = "";
if(isset($_POST['checked_opt'])){
    $checked_opt = explode(",", substr($_POST['checked_opt'], 1));
    foreach($checked_opt as $option ){
        $r = $DB_site->query_first("SELECT caractid, libelleFR as libelle FROM caracteristiquevaleur WHERE caractvalid ='".$option."'");
        $r2 = $DB_site->query_first("SELECT libelleFR FROM caracteristique WHERE caractid ='".$r['caractid']."'");
        $txt_opt .= $r2[0]." : ".$r['libelle'].'<br>';
    }
}

$contactuserid=0;

if(isset($_POST['rappelinst_tel'])) {
    $nom=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_nom']));
    $prenom=$adresse=$ville=$message=$umail=$utel="";
    if(isset($_POST['rappelinst_prenom'])) $prenom=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_prenom']));
    $raisonsociale=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_societe']));
    $adresse=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_adresse']));
    $cp=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_cp']));
    $ville=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_ville']));
    $pays['libelle']=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_pays']));
    $umail=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_mail']));
    $utel=strip_tags(preg_replace("/[\.|\ ]/","",$_POST['rappelinst_tel']));
}
else if(isset($_POST['rappelinst_tel_config'])) {
    $nom=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_nom_config']));
    $prenom=$adresse=$ville=$message=$umail=$utel="";
    if(isset($_POST['rappelinst_prenom_config'])) $prenom=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_prenom_config']));
    $raisonsociale=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_societe_config']));
    $adresse=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_adresse_config']));
    $cp=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_cp_config']));
    $ville=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_ville_config']));
    $pays['libelle']=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_pays_config']));
    $umail=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_mail_config']));
    $utel=strip_tags(preg_replace("/[\.|\ ]/","",$_POST['rappelinst_tel_config']));
}
else if(isset($_POST['rappelinst_tel_config_desk'])) {
    $nom=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_nom_config_desk']));
    $prenom=$adresse=$ville=$message=$umail=$utel="";
    if(isset($_POST['rappelinst_prenom_config_desk'])) $prenom=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_prenom_config_desk']));
    $raisonsociale=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_societe_config_desk']));
    $adresse=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_adresse_config_desk']));
    $cp=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_cp_config_desk']));
    $ville=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_ville_config_desk']));
    $pays['libelle']=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_pays_config_desk']));
    $umail=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['rappelinst_mail_config_desk']));
    $utel=strip_tags(preg_replace("/[\.|\ ]/","",$_POST['rappelinst_tel_config_desk']));
}
$gclid = mysqli_escape_string($DB_site->link_id,$_POST['gclid']);


$contactinfo="<p><b>Infos formulaire</b></p>
Nom : ".$nom;
if($prenom!="") $contactinfo.="<br>Prénom : ".$prenom;
if($raisonsociale!="") $contactinfo.="<br>Société : ".$raisonsociale;
if($adresse!="") $contactinfo .="<br>Adresse : ".$adresse;
if($ville!="") $contactinfo .="<br>Ville : ".$ville;
if($cp!="") $contactinfo .="<br>Code postal : ".$cp;
if($pays['libelle']!="") $contactinfo.="<br>Pays : ".$pays['libelle'];
if($umail!="") $contactinfo.="<br>E mail : ".$umail;
if($utel!="") $contactinfo.="<br>Téléphone : ".$utel;

if(isset($user_info)){ // utilisateur deja connecte
    $raisonsociale=mysqli_real_escape_string($DB_site->link_id,$user_info['raisonsociale']);
    $nom=mysqli_real_escape_string($DB_site->link_id,$user_info['nom']);
    $prenom=mysqli_real_escape_string($DB_site->link_id,$user_info['prenom']);
    $adresse=mysqli_real_escape_string($DB_site->link_id,$user_info['adresse']);
    $ville=mysqli_real_escape_string($DB_site->link_id,$user_info['ville']);
    $pays=$DB_site->query_first("SELECT libelle FROM pays WHERE paysid=".intval($user_info['paysid']));
    $umail=$user_info['mail'];
    $message="<p>Client <a href=\"http://".$host."/admin/utilisateur.php?action=editer&userid=".$user_info['userid']."\">".$prenom." ".$nom." n°".$user_info['userid']."</a></p>";
    $contactuserid=$user_info['userid'];

    // Gestion du GCLID, on vérifie si l'user n'a pas déjà un gclid.
    $g = $DB_site->query_first("SELECT gclid FROM utilisateur WHERE userid ='".$user_info['userid']."'");
    if(($g[0] == "" || $g[0] != $gclid) && $gclid != ""){ 
        $DB_site->query("UPDATE utilisateur SET gclid = '".$gclid."' WHERE userid = '".$user_info['userid']."'");
    }elseif($g[0] != ""){
        $gclid = $g[0];
    }else{
        $gclid = "";
    }

}elseif(strlen($utel)>5){

    $utilisateur=$DB_site->query_first("SELECT * FROM utilisateur WHERE REPLACE(telephone,'.','') LIKE '".$utel."' OR REPLACE(portable,'.','') LIKE '".$utel."'");
    
    if($utilisateur['userid']!=""){

        $message="<p>Client identifié par n° téléphone <a href=\"http://".$host."/admin/utilisateur.php?action=editer&userid=".$utilisateur['userid']."\">".$utilisateur['prenom']." ".$utilisateur['nom']." n°".$utilisateur['userid']."</a></p>";

    }elseif(filter_var($umail, FILTER_VALIDATE_EMAIL)){

     $utilisateur=$DB_site->query_first("SELECT * FROM utilisateur WHERE mail='".$umail."'");

     if($utilisateur['userid']!=""){

        $message="<p>Client identifié par email <a href=\"http://".$host."/admin/utilisateur.php?action=editer&userid=".$utilisateur['userid']."\">".$utilisateur['prenom']." ".$utilisateur['nom']." n°".$utilisateur['userid']."</a></p>";
    }
}
if($utilisateur['userid']!=""){
   $contactuserid=$utilisateur['userid'];
   $raisonsociale=mysqli_real_escape_string($DB_site->link_id,$utilisateur['raisonsociale']);
   $nom=mysqli_real_escape_string($DB_site->link_id,$utilisateur['nom']);
   $prenom=mysqli_real_escape_string($DB_site->link_id,$utilisateur['prenom']);
   $adresse=mysqli_real_escape_string($DB_site->link_id,$utilisateur['adresse']);
   $ville=mysqli_real_escape_string($DB_site->link_id,$utilisateur['ville']);
   $pays=$DB_site->query_first("SELECT libelle FROM pays WHERE paysid=".intval($user_info['paysid']));
   $umail=$utilisateur['mail'];

        // Gestion du GCLID, on vérifie si l'user n'a pas déjà un gclid.
   $g = $DB_site->query_first("SELECT gclid FROM utilisateur WHERE userid ='".$user_info['userid']."'");
   if(($g[0] == "" || $g[0] != $gclid) && $gclid != ""){ 
    $DB_site->query("UPDATE utilisateur SET gclid = '".$gclid."' WHERE userid = '".$user_info['userid']."'");
}elseif($g[0] != ""){
    $gclid = $g[0];
}else{
    $gclid = "";
}

}
}

if($gclid != "") $contactinfo .= "<br>gclid : ".$gclid;

$page=url2page($_POST['url'], $pro);
if($page==false) $page=$_POST['url'];

if(!isset($_POST['msg'])){
    $message="<p>Bonjour,</p>
    <p>Une personne vient de remplir le formulaire de rappel immédiat de votre boutique</p>
    ".$contactinfo."<br>".$message."
    Page : ".$page."<br>".$txt_opt;

    $sujet="Rappel immédiat";
}else{
    $message=strip_tags($_POST['msg']).$contactinfo."<br>".$message."Page : ".$page."<br>".$txt_opt;
    $sujet="Rappel immédiat";
}

$mail=new email();
$mail->expediteur=$template->translate("/site/base/mail_expediteur");
$mail->expname=$template->translate("/site/nom","F");
$mail->destinataire=$template->translate("contact@mon-espace-cuisson.com");
// $mail->to=array("commercial2@mon-espace-cuisson.com");
$mail->subject="Mon Espace Cuisson : Réponse au formulaire de votre boutique.";
$mail->body=$message;
$mail->sendmail();
$sql="INSERT INTO contact SET userid=".intval($contactuserid).", date=".time().", nom='".$nom."', raisonsociale='".$raisonsociale."', adresse='".$adresse."', ville='".$ville."', pays='".$pays['libelle']."', mail='".$umail."', telephone='".$utel."', sujet='".$sujet."', message='".mysqli_real_escape_string($DB_site->link_id,$message)."', langue='".$template->langue."', gclid='".$gclid."'";
$DB_site->query($sql);

if($contactuserid>0){
    $sql="INSERT INTO mail_inbox SET date=".time().", `from`='".mysqli_real_escape_string($DB_site->link_id,$umail)."', `to`='".$mail->destinataire."', subject='".mysqli_real_escape_string($DB_site->link_id,"Mon Espace Cuisson : Réponse au formulaire de votre boutique.")."', body='".mysqli_real_escape_string($DB_site->link_id,$message)."', userid=".$contactuserid;
    $DB_site->query($sql);
}

print "<p class=\"success\" id=\"minicontact_form_sucess\">".$template->translate("/contact/prise_compte")."</p>";

?>