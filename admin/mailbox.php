<?php
$mail_lang    = "FR";
$default_site = "base";

$_GET['userid'] = '0'; //2025
//Tableau permettant d'ajouter "Contact" dans la liste de sélection de l'expéditeur
$admc = array("alexis","philippe","jessica","jbandre","ariel");

$explist = "";

//Récupération des coordonnées de l'utilisateur en cours
$sql = "SELECT *";
$sql .= " FROM admin_contact";
$sql .= " WHERE username = '" . $user_info['username'] . "'";
$list_contact = $DB_site2->query($sql);
while($admin_contact = $DB_site2->fetch_array($list_contact))
{
    //Vérification du site (par défaut, les infos du site base (FR) sont chargées, et non celles du UK)
    if($admin_contact['site_code'] == $default_site)
    {
        $default_contact['tel']  = $admin_contact['tel'.$mail_lang];
        $default_contact['mail'] = $admin_contact['mail'.$mail_lang];
        $defaultsignature        = signature_mail($default_site,$mail_lang,$user_info['nom_contact'],$default_contact['tel'],$default_contact['mail'],$admin_contact['fonction'.$mail_lang]);
    }

    //Choix de l'adresse email d'expédition en fonction de la langue
    foreach($langues as $langue)
    {
        if($admin_contact['mail' . $langue] != "")
        {
            $explist .= "<a href=\"javascript:change_exp('" . $user_info['username'] . "','" . $admin_contact['site_code'] . "','" . $langue . "');\" id=\"exp_" . $user_info['username'] . "_" . $admin_contact['site_code'] . "_" . $langue . "\"><img src=\"/admin/images/drapeaux/br/" . $langue . ".gif\"> " . $admin_contact['mail' . $langue] . "</a>"; 
        } 
    }
}

//Ajout de "Contact" dans la liste des expéditeurs (commerciaux)
if(in_array($user_info['username'], $admc))
{
    $sql = "SELECT *";
    $sql .= " FROM admin_contact";
    $sql .= " WHERE username = 'contact'";
    $list_ctc = $DB_site2->query($sql);
    while($admin_ctc = $DB_site2->fetch_array($list_ctc))
    {
        foreach($langues as $langue)
        {
            if($admin_ctc['mail' . $langue] != "")
            {
                $explist .= "<a href=\"javascript:change_exp('contact','" . $admin_ctc['site_code'] . "','" . $langue . "');\" id=\"exp_contact_" . $admin_ctc['site_code'] . "_".$langue . "\"><img src=\"images/drapeaux/br/" . $langue . ".gif\"> " . $admin_ctc['mail' . $langue] . "</a>";
            } 
        }
    }
}

//Si Albane, on lui laisse le choix de l'exp ADV
if($user_info['username'] == 'albane')
{
    $sql = "SELECT *";
    $sql .= " FROM admin_contact";
    $sql .= " WHERE username = 'adv_albane'";
    $list_parms = $DB_site2->query($sql);
    while($admin_parms = $DB_site2->fetch_array($list_parms))
    {
        foreach($langues as $langue)
        {
            if($admin_parms['mail' . $langue] != "")
            {
                $explist .= "<a href=\"javascript:change_exp('" . $admin_parms['username'] . "','" . $admin_parms['site_code'] . "','" . $langue . "');\" id=\"exp_contact_" . $admin_parms['site_code'] . "_".$langue . "\"><img src=\"images/drapeaux/br/" . $langue . ".gif\"> " . $admin_parms['mail' . $langue] . "</a>";
            } 
        }
    }
}

//Si l'utilisateur en cours est "qualite", on lui ajoute la possibilité de choisir l'utilisateur "adv" comme expéditeur
//Si l'utilisateur en cours est "adv", on lui ajoute la possibilité de choisir l'utilisateur "qualite" comme expéditeur
if(in_array($user_info['username'], array('qualite','adv')))
{
    $aParms['adv']     = 'qualite2';
    $aParms['qualite'] = 'adv2';

    $sql = "SELECT *";
    $sql .= " FROM admin_contact";
    $sql .= " WHERE username = '" . $aParms[$user_info['username']] . "'";
    $list_parms = $DB_site2->query($sql);
    while($admin_parms = $DB_site2->fetch_array($list_parms))
    {
        foreach($langues as $langue)
        {
            if($admin_parms['mail' . $langue] != "")
            {
                $explist .= "<a href=\"javascript:change_exp('" . $admin_parms['username'] . "','" . $admin_parms['site_code'] . "','" . $langue . "');\" id=\"exp_contact_" . $admin_parms['site_code'] . "_".$langue . "\"><img src=\"images/drapeaux/br/" . $langue . ".gif\"> " . $admin_parms['mail' . $langue] . "</a>";
            } 
        }
    }
}

if(isset($_POST['action']) && $_POST['action'] == 'rappel_relance'){
    $tsrappel=0;
    if($_POST['rappel']!=""){
        $sp=preg_split('/[- :\/h]/',$_POST['rappel']);
        $tsrappel=mktime($sp[3],$sp[4],0,$sp[1],$sp[0],$sp[2]);
    }
    $sql = "INSERT INTO suivi_commande SET";
    $sql .= " facturid    = ".$facturid."";
    $sql .= ", date       = ".time()."";
    $sql .= ", commercial   = '$user_info[username]'";
    $sql .= ", suivi        = '".mysqli_real_escape_string($DB_site2->link_id,'Rappel pour la relance fournisseur/transporteur/installateur')."'";
    $sql .= ", rappel       = ".$tsrappel."";
    $sql .= ", rappeltype   = '".$_POST['rappeltype']."'";
    $sql .= ", jour         = ".intval($_POST['jour'])."";
    $sql .= ", pour         = '".$_POST['pour']."'";
    $DB_site2->query($sql);
}
if(isset($_POST['action']) && $_POST['action'] == 'rappel_relance_stock'){
    $tsrappel=0;
    if($_POST['rappel']!=""){
        $sp=preg_split('/[- :\/h]/',$_POST['rappel']);
        $tsrappel=mktime($sp[3],$sp[4],0,$sp[1],$sp[0],$sp[2]);
    }
    $sql = "INSERT INTO suivi_stock SET";
    $sql .= " stock_cmd_id    = ".$_POST['envoiid']."";
    $sql .= ", date       = ".time()."";
    $sql .= ", commercial   = '$user_info[username]'";
    $sql .= ", suivi        = '".mysqli_real_escape_string($DB_site2->link_id,'Rappel pour la relance fournisseur/transporteur/installateur')."'";
    $sql .= ", rappel       = ".$tsrappel."";
    $sql .= ", pour         = '".$_POST['pour']."'";
    $DB_site2->query($sql);
}

//Envoi de l'email suite à validation
if(isset($_POST['mail_dest'])){
    $mail = new email();
    $mail->stylesheet($rootpath."/configurations".$host."/mails.css");
    $mail->expediteur   = $_POST['mail_exp'];
    $mail->expname      = $_POST['mail_expname']." - Mon Espace Cuisson";
    $mail->destinataire = $_POST['mail_dest'];
    $mail->cc           = $_POST['mail_cc'];
    $mail->bcc          = $_POST['mail_cci'];

    //Paramétrage sujet
    $mail->subject = stripslashes($_POST['mail_subject']);
    //if($_POST['suiviid'] != "0") $mail->subject .= " - Commande n°".$_POST['suiviid'];  
    
    //Paramétrage corps de mail
    $mail->body = stripslashes($_POST['mail_body']);
    if($_POST['mail_signatureshow'] == "1") $mail->body .= stripslashes($_POST['mail_signature']);

    //on enregistre que le mail Avis client a été envoyé; ID du mail : 220
    if(intval($_POST['emailid']) == 220){
        $sql = "UPDATE utilisateur SET satisfaction = '1' WHERE mail = '".$_POST['mail_dest']."'";
        $DB_site2->query($sql);
    }

    //filtre si le code tracking se trouve dans l'objet ou le corps du mail
    if(strpos($mail->expediteur, "@mon-espace-cuisson.com")){
        $check = $mail->destinataire;
    }else{
        $check = $mail->expediteur;
    }

    $sqlset = filtre_mail($mail->subject,$_POST['mail_body'],$check);
    
    //si pas de code tracking dans l'objet ou corps du mail alors on regarde la provenance
    if($sqlset == '' && isset($_POST['suivitype']) && isset($_POST['suiviid'])){
        switch ($_POST['suivitype']) {
            case 'suivi_commande':
                $sqlset = ", facturid = '".$_POST['suiviid']."'";
                break;
            
            case 'suivi_client':
                $sqlset = ", userid = '".$_POST['suiviid']."'";
                break;

            case 'suivi_fournisseur':
                $sqlset = ", fournisseur = '".$_POST['suiviid']."'";
                break;

            case 'suivi_interne':
                $sqlset = ", suivi_interne_id = '".$_POST['suiviid']."'";
                break;

            case 'suivi_stock':
                $sqlset = ", stock_cmd_id = '".$_POST['suiviid']."'";
                break;            
        }
    }
    $dest = $mail->destinataire;
    /*if($_POST['mail_cci'] != ''){
        $dest .= ';'.$_POST['mail_cci'];
    }*/
    if($_POST['mail_cc'] != ''){
        $dest .= ';'.$_POST['mail_cc'];
    }
    //Insertion dans suivi
    $sql = "INSERT INTO mail_outbox SET";
    $sql .= " date         = ".time()."";
    $sql .= ", utilisateur = '".$user_info['username']."'";
    $sql .= ", `from`      = '".$mail->expediteur."'";
    $sql .= ", `to`        = '".$dest."'";
    $sql .= ", subject     = '".addslashes($mail->subject)."'";
    $sql .= ", body        = '".addslashes($_POST['mail_body'])."'";
    $sql .= ", pj          = ''";
    $sql .= ", uniqueid    = ''";
    $sql .= $sqlset;
    $DB_site2->query($sql);

    $mailid = $DB_site2->insert_id();
    
    if($_POST['suivitype']=="suivi_client"){
        // mise à jour de la date d'envoi du dernier mail de suivi client dans la table utilisateur
        $sql = "UPDATE utilisateur SET date_mail_outbox_suivi_client='".time()."' WHERE userid = '".$_POST['suiviid']."' ";
        $DB_site2->query($sql);
    }

    //Insertion des pièces jointes
    $year = date("Y", time());
    $tabPJid = array();
    $listpj = array();
    $pjsize=0;

    if($_POST['serverpj'] != ""){
        $pjs = explode(",",$_POST['serverpj']);
        foreach($pjs as $pj){
            if($pj != ""){
                $data = explode("=",$pj);
                $url=urldecode($data[1]);
                if(substr($url, 0,1) == "/") $url = $rootpath.substr($url, 1);
                
                $mail->attachfile($url,$data[0]);
                $listpj[] = $data[0];
                $pjsize+=@filesize($url);
                $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
                $sql = "INSERT INTO mail_attachment SET moyen='mail_out', mailid='$mailid', name='".mysqli_real_escape_string($DB_site2->link_id,$data[0])."', ext='$ext', size='".@filesize($url)."'";
                $DB_site2->query($sql);
                $pjid = $DB_site2->insert_id();
                if($pjid>0){
                    @copy($url, $rootpath."configurations/".$host."/mails/attachments/$year/".$pjid.".".$ext);
                }

            }
        }
    }

    //$uploaddir = $rootpath."/images/mail/pj2/";ancien repertoire
    $uploaddir = $rootpath."configurations/".$host."/mails/attachments/$year/";
    foreach($_FILES as $f){

        if($f['name'] != ""){
            
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            
            $sql = "INSERT INTO mail_attachment SET moyen='mail_out', mailid='$mailid', name='".mysqli_real_escape_string($DB_site2->link_id,$f['name'])."', ext='$ext', size='".$f['size']."'";
            $DB_site2->query($sql);
            $pjid = $DB_site2->insert_id();

            //$tabPJid[] = $pjid.'.'.$ext;
            
            $listpj[] = $f['name'];
            $uploadfile = $uploaddir.$pjid.'.'.$ext;
            move_uploaded_file($f['tmp_name'], $uploadfile);
            $pjsize+=@filesize($uploadfile);
            $mail->attachfile($uploadfile,$f['name']);
        }
    }
    $pjsize=round($pjsize/1024000);

    $mail_result = $mail->sendmail();

    //on rajoute le uniqueid sur le mail pour qu'il ne soit pas enregistré deux fois
    $DB_site2->query("UPDATE mail_outbox SET uniqueid='".$mail->uniqueid."', pj='".addslashes(implode(',',$listpj))."' WHERE id='".$mailid."'");   
    
}

$fontsizes=" <select onchange=\"doRichEditCommand('fontSize',this.value);\" title=\"Taille du texte\" style=\"float:left; margin-left:5px;\">";
for($i=1;$i<=7;$i++) $fontsizes.="<option value=\"$i\">".($i*2+4)."</option>";
$fontsizes.="</select>";

$format_btn="<div onclick=\"doRichEditCommand('bold')\" style=\"font-weight:bold;\" class=\"format_btn\" title=\"Gras\">B</div>
<div onclick=\"doRichEditCommand('underline')\" style=\"text-decoration:underline;\" class=\"format_btn\" title=\"Souligné\">U</div>
<div onclick=\"doRichEditCommand('italic')\" style=\"font-style:italic;\" class=\"format_btn\" title=\"Italique\">I</div> 
<div onclick=\"showpicker('foreColor');\" style=\"font-weight:bold;color:red; margin-left:5px;\" class=\"format_btn\" title=\"Couleur du texte\">A</div>
<div onclick=\"showpicker('hiliteColor');\" class=\"format_btn\" title=\"Couleur du fond\"><span style=\"font-weight:bold;background-color:red;\">A</span></div>".colorpicker()
.$fontsizes." 
<div onclick=\"createlink();\" style=\"text-decoration:underline;color:blue; margin-left:5px;\" class=\"format_btn\" title=\"Lien\">A</div>
<div onclick=\"doRichEditCommand('justifyLeft')\" style=\"margin-left:5px;background-image:url('/admin/images/align_left.png'); background-repeat:no-repeat;background-position:center center;\" class=\"format_btn\" title=\"Aligner à gauche\"></div>
<div onclick=\"doRichEditCommand('justifyCenter')\" style=\"background-image:url('/admin/images/align_center.png'); background-repeat:no-repeat;background-position:center center;\" class=\"format_btn\" title=\"Centrer\"></div>
<div onclick=\"doRichEditCommand('justifyRight')\" style=\"background-image:url('/admin/images/align_right.png'); background-repeat:no-repeat;background-position:center center;\" class=\"format_btn\" title=\"Aligner à droite\"></div>
<div onclick=\"doRichEditCommand('removeFormat')\" style=\"color:red; margin-left:5px;\" class=\"format_btn\" title=\"Supprimer mise en page\">X</div>
<div onclick=\"doRichEditCommand('undo')\" class=\"format_btn\" style=\"background-image:url('/admin/images/undo.png'); background-repeat:no-repeat;background-position:center center;\" title=\"Annuler\"></div>";

function colorpicker(){
  for ($red_channel=0;$red_channel<=15;$red_channel=$red_channel+3){ 
    for ($green_channel=0;$green_channel<=15;$green_channel=$green_channel+3){ 
        for ($blue_channel=0;$blue_channel<=15;$blue_channel=$blue_channel+3){ 
            $red_channel_value=dechex($red_channel).dechex($red_channel); 
            $green_channel_value=dechex($green_channel).dechex($green_channel);     
            $blue_channel_value=dechex($blue_channel).dechex($blue_channel); 
            @$index_pointer++;     
            $hex_values_table[$index_pointer]=$red_channel_value.$green_channel_value.$blue_channel_value; 
        } 
    } 
  }
  
  $ret="<table id=\"colorpicker\" class=\"colorpicker\"><tr>";
  $c = 0;
  foreach($hex_values_table as $hexval){
    $ret.="<td class=\"colorsquare\" style=\"background-color:#$hexval\" onmouseover=\"document.getElementById('colorsel').style.backgroundColor='#$hexval';\" onclick=\"doRichEditCommand(editcolor,'#$hexval');closepicker();\">&nbsp</td>";
    $c++;
    if($c==24){
      $c=0;
      $ret.="</tr><tr>";
    }
  }
  $ret.="</tr>
  <tr><td colspan=\"24\" id=\"colorsel\"><div class=\"mail_hide\" onclick=\"closepicker();\" style=\"float:right;\">X</div></td></tr>
  </table>";

  return $ret;
}

$contacts="<div id=\"mail_contact_fourn\" class=\"mail_contact\">
<input type=\"text\" id=\"contactsrc\" value=\"\" placeholder=\"Rechercher\" onkeyup=\"request_contact(this.value,'srcfournisseur');\" style=\"float:left;\"> <div class=\"mail_hide\" title=\"Fermer\" onclick=\"document.getElementById('mail_contact_fourn').style.display='none';\">X</div>
<div id=\"srcresults_srcfournisseur\" class=\"contact_list\" style=\"clear:both;\"></div>
</div>
<div id=\"mail_contact_clients\" class=\"mail_contact\">
<input type=\"text\" id=\"contactsrc\" value=\"\" placeholder=\"Rechercher\" onkeyup=\"request_contact(this.value,'srcclient');\" style=\"float:left;\"> <div class=\"mail_hide\" title=\"Fermer\" onclick=\"document.getElementById('mail_contact_clients').style.display='none';\">X</div>
<div id=\"srcresults_srcclient\" class=\"contact_list\" style=\"clear:both;\"></div>
</div>";

$suiviid=mysqli_real_escape_string($DB_site2->link_id,$_GET['userid']);
$cadre_sav = "";
$cadre_pjt = "";
if(isset($_GET['facturid'])){
    $facturid = mysqli_real_escape_string($DB_site2->link_id,$_GET['facturid']);
    $cadre_cde ="<span id=\"chx_cde\" class=\"cadreenvoi2\" onclick=\"css_btn_tracking(this); reset_btn_tracking(this.id);cadreenvoicmd('CDE',$facturid);subject_select('cde');\">CDE</span>";
    $savs = $DB_site2->query("SELECT id FROM  sav WHERE facturid = '$facturid'");
    while ($sav = $DB_site2->fetch_array($savs)) {
         $tabSAV[] = $sav['id'];
    }
    if(is_array($tabSAV) && count($tabSAV)>0){
        $cadre_sav .= "<span id=\"chx_sav\" class=\"cadreenvoi2\" onclick=\"css_btn_tracking(this); reset_btn_tracking(this.id);\">SAV <select name=\"choixsav\" onchange=\"cadreenvoicmd('SAV',this.value); subject_select('sav');\"> <option value=\"\"></option>";
        foreach ($tabSAV as $savid ) {
            $cadre_sav .= "<option value=\"".$savid."\">".$savid."</option>";
        }
        $cadre_sav .= "</select></span>";
    }
}elseif (isset($_GET['userid'])) {
    $userid = mysqli_real_escape_string($DB_site2->link_id,$_GET['userid']);
    $projets = $DB_site2->query("SELECT projetid FROM projet WHERE userid=$userid ");
    while ($projet = $DB_site2->fetch_array($projets)) {
        $tabPJT[]=$projet['projetid'];
    }
    if(empty($tabPJT) === false && is_array($tabPJT) && count($tabPJT)>0){
        $cadre_pjt .= "<span id=\"chx_pjt\" class=\"cadreenvoi2\" onclick=\"css_btn_tracking(this); reset_btn_tracking(this.id);\">PROJET <select name=\"choixsav\" onchange=\"cadreenvoicmd('PJT',this.value); subject_select('pjt');\"> <option value=\"\"></option>";
        foreach ($tabPJT as $pjt ) {
            $cadre_pjt .= "<option value=\"".$pjt."\">".$pjt."</option>";
        }
        $cadre_pjt .= "</select></span>";
    }

}

print <<<HTML
<div onclick="show_mail()" class="mail_open" id="mail_icon">e mail</div>
<div id="mail_div" class="mail_div">
    <form name="mail_form" method="post" enctype="multipart/form-data">
        <input type="hidden" id="mail_lang" value="$mail_lang">
        <input type="hidden" id="mail_site_code" value="$default_site">
        <input type="hidden" id="mail_default_user" value="$user_info[username]">
        <input type="hidden" id="mail_default_nom" value="jbandre">
        <input type="hidden" id="mail_default_mail" value="webmaster@ma-cave-a-vin.fr">
        <input type="hidden" id="mail_cb" name="mail_cb">
        <input type="hidden" id="suivitype" name="suivitype" value="suivi_client">
        <input type="hidden" id="suiviid" name="suiviid" value="$suiviid">
        <input type="hidden" name="serverpj">
        <input type="hidden" name="emailid" id="emailid" value="">
        <input type="hidden" id="envoiid" name="envoiid" value="">
        <input type="hidden" id="relance_stock" name="relance_stock" value="">
        <div class="mailtitle" onmousedown="move_mail(true)" onmouseup="move_mail(false)">
            <div class="mail_hide" title="Réduire" onclick="show_mail(false);"> &#9664</div>
            <div class="mail_hide" onclick="remove_mail()">Supprimer <img src="/admin/images/supprimer.gif" align="absmiddle" height="15"></div>
            <div class="mail_send" onclick="verifmailform()">Envoyer <img src="/admin/images/send_mail.png" align="absmiddle" height="20"></div>
        </div>
        <table class="mailaddr">
            <tr>
                <td>Expéditeur :</td>
                <td>
                    <input type="text" name="mail_expname" class="mail_exp" value="jbandre" readonly>
                    <input type="text" name="mail_exp" value="webmaster@ma-cave-a-vin.fr" class="mail_exp" readonly> 
                    <img src="/admin/images/showtranslate.jpg" id="mail_exp_arrow" onclick="show_exp_list()">
                    <div id="exp_list">
                        $explist
                    <div id="exp_list_supp"></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>Destinataire :</td>
                <td>
                    <input type="text" name="mail_dest" class="mail_dest">
                    <img src="/admin/images/address_book.png" height="20" align="absmiddle" id="addressbook1" title="Contacts Fournisseurs" onclick="show_contacts('mail_dest','fourn')">
                    <img src="/admin/images/icon/user_male_pt.png" height="20" align="absmiddle" id="addressbook2" title="Contacts Clients" onclick="show_contacts('mail_dest','clients')">
                    $contacts
                </td>
            </tr>
            <tr>
                <td>CC :</td>
                <td>
                    <input type="text" name="mail_cc" value="" class="mail_dest">
                    <img src="/admin/images/address_book.png" height="20" align="absmiddle" title="Contacts Fournisseurs" onclick="show_contacts('mail_cc','fourn')">
                    <img src="/admin/images/icon/user_male_pt.png" height="20" align="absmiddle" id="addressbook2" title="Contacts Clients" onclick="show_contacts('mail_cc','clients')">
                </td>
            </tr>
            <tr>
                <td>CCI :</td>
                <td>
                    <input type="text" name="mail_cci" value="webmaster@ma-cave-a-vin.fr" class="mail_dest">
                    <img src="/admin/images/address_book.png" height="20" align="absmiddle" title="Contacts Fournisseurs" onclick="show_contacts('mail_cci','fourn')">
                    <img src="/admin/images/icon/user_male_pt.png" height="20" align="absmiddle" id="addressbook2" title="Contacts Clients" onclick="show_contacts('mail_cci','clients')">
                </td>
            </tr>
            <tr>
                <td>Joindre :</td>
                <td id="td_pj">
                    <input type="file" name="mail_pj1" onchange="addpj(this);">
                    <input type="button" id="mail_pagepj" value="Pièces jointes" style="display:none;">
                </td>
            </tr>
        </table>
        <div id="mailservice">
            Mail automatique :
            <div id="mailservicelist" class="mail_services"></div>
            <input type="text" id="mailserviceselect" onclick="service_select(0)" value="Choisir" readonly>
            <img src="/admin/images/showtranslate.jpg" id="services_arrow" onclick="service_select(0)">
        </div>
        <div id="cadreenvoi" style="display: none;">Cadre d'envoi : $cadre_cde$cadre_sav$cadre_pjt</div>
        <input type="hidden" id="tracking_type" name="tracking_type" value="">
        <input type="hidden" id="tracking_id" name="tracking_id" value="">
        <input type="hidden" id="check_cadre" value="0">
        <div class="mailsubject">Sujet : <input type="text" name="mail_subject"></div>
        <textarea name="mail_body" id="mail_body"></textarea>
        <div>$format_btn</div>
        <div style="clear:both;"></div>
        <iframe id="wys" class="wys"></iframe>
        <textarea name="mail_signature" style="display:none;">$defaultsignature</textarea>
        <div id="divsignature" class="mail_signature">$defaultsignature</div>
        <div style="float:right;">
            <input type="checkbox" name="mail_signatureshow" value="1" onclick="show_signature(this.checked)" checked>
            Afficher
        </div>
    </form>
</div>
HTML;

if(isset($mail_result)){
  if($mail_result){
      $mail_result="<img src=\"images/icon/valider_action.png\">Votre e mail a été envoyé avec succès";
      if($_POST['envoiid'] !== '' && $_POST['relance_stock'] !== ''){
          $mail_result.='<p>Date de rappel pour la relance</p>
          <form method="post">
          <input type="text" name="rappel" id="rappelmail" onclick="calendrier_show(this.id,this.value,\'planning\',planningmailcb,1)" readonly style="width:130px;"> pour <input type="text" id="poursuivi" name="pour" size="6" readonly> <input type="hidden" name="box" value="1">
          <input type="hidden" name="envoiid" value="'.$_POST['envoiid'].'">
          <input type="hidden" name="action" value="rappel_relance_stock">
          <input type="submit" value="OK"></form>';
      }
      elseif($_POST['envoiid'] !== '' && $_POST['relance_stock'] == ''){
        $mail_result.='<p>Date de rappel pour la relance</p>
        <form method="post">
        <input type="text" name="rappel" id="rappelmail" onclick="calendrier_show(this.id,this.value,\'planning\',planningmailcb,1)" readonly style="width:130px;"> pour <input type="text" id="poursuivi" name="pour" size="6" readonly> <input type="hidden" name="box" value="1"> 
        <input type="hidden" name="rappeltype" value="int">
        <input type="hidden" name="jour" value="0">
        <input type="hidden" name="action" value="rappel_relance">
        <input type="submit" value="OK"></form>';
      }
      if($pjsize>=10) $mail_result.="<b>L'email fait ".$pjsize."Mo, le destinataire risque de ne pas le recevoir, merci de le contacter pour vérification</b>";
  }else{
      $mail_result="<img src=\"images/icon/avertissement.png\">Une erreur est survenue lors de l'envoi de l'email";
  }
  
  print "<div id=\"mail_notif\" class=\"mail_notif\">
        <img src=\"/admin/images/icon/erreur.png\" class=\"close\" onclick=\"hide_notif()\">
      $mail_result
  </div>";
}

print "<script language=\"javascript\" src=\"../javascripts/host.js?\"></script>
<script language=\"javascript\" src=\"http://localhost/mec/admin/mailbox.js?2\"></script>
<script language=\"javascript\">

myf=getIFrameDocument('wys');
myf.open();
myf.designMode='On';
myf.close(); 
$contact_js
</script>";


function filtre_mail($objet, $message,$check){
    global $DB_site2;
    $sqlset="";
    $fourn = $client = false;
    //Filtre sur l'objet et corps du mail
    //on commence par l'objet
    if( preg_match("/#CLI([0-9]{1,5})#/", $objet,$m) == 1) {
        
        $userid = userid($m[1]);
        $sqlset = " ,userid='".$userid."', compta = 'non'";

    }elseif(preg_match("/#PJT([0-9]+)(-(\d+))?#/", $objet, $m) == 1) {
        
        $projetid=$m[1];
        $sqlset = " , projetid = '".$projetid."', compta = 'non'";
        if(isset($m[3])) $sqlset.=", userid ='".$m[3]."'";

    }elseif(preg_match("/#SAV([0-9]{5,6})-([0-9]{1,4})#/", $objet,$m) == 1) {
        
        $facturid = $m[1];
        $savid = $m[2];
        $userid= userid2($DB_site2, 'facture',$facturid);
        $sqlset = " , facturid='".$facturid."', savid ='".$savid."', userid='".$userid."'";

    }elseif(preg_match("/#CDE([0-9]{5,6})#/", $objet,$m) == 1) {

        $facturid = $m[1];
        $userid= userid2($DB_site2, 'facture',$facturid);
        $sqlset = " , facturid='".$facturid."', userid='".$userid."'";

    }elseif(preg_match("/#CLI([0-9]{1,5})#/", $message,$m) == 1) { //check sur le corps du mail
        
        $userid = userid($m[1]);
        $sqlset = " ,userid='".$userid."', compta = 'non'";

    }elseif(preg_match("/#PJT([0-9]+)(-(\d+))?#/", $message, $m) == 1) {
        
        $projetid=$m[1];
        $sqlset = " , projetid = '".$projetid."', compta = 'non'";
        if(isset($m[3])) $sqlset.=", userid ='".$m[3]."'";

    }elseif(preg_match("/#SAV([0-9]{5,6})-([0-9]{1,4})#/", $message,$m) == 1) {
        
        $facturid = $m[1];
        $savid = $m[2];
        $userid= userid2($DB_site2, 'facture',$facturid);
        $sqlset = " , facturid='".$facturid."', savid ='".$savid."', userid='".$userid."'";

    }elseif(preg_match("/#CDE([0-9]{5,6})#/", $message,$m) == 1) {
        
        $facturid = $m[1];
        $userid= userid2($DB_site2, 'facture',$facturid);
        $sqlset = " , facturid='".$facturid."', userid='".$userid."'";

    }elseif(!is_null(is_client($DB_site2, $check))){
        $userid = intval(is_client($DB_site2, $check));
        $sqlset = " ,userid='".$userid."', compta = 'non'";
        $client = true;

    }elseif(!is_null(is_fourn($DB_site2, $check)) && !strpos($check, "@mon-espace-cuisson.com")){

        $sqlset =" , fournisseur='".mysqli_real_escape_string($DB_site2->link_id,is_fourn($DB_site2, $check))."'";
        $type = "suivi_fournisseur"; 
        $fourn = true;
    }

    if(!$fourn && !$client){
        if(!is_null(is_fourn($DB_site2, $check)) && !strpos($check, "@mon-espace-cuisson.com")){
            $sqlset .=" , fournisseur='".mysqli_real_escape_string($DB_site2->link_id,is_fourn($DB_site2, $check))."'";
        }
    }
    return $sqlset;
}
function userid($userid){

    while(substr($userid, 0,1) == '0'){
        $userid = substr($userid, 1);
    }

    return $userid;
}
function userid2($DB,$type,$id){

    switch ($type) {
        case 'facture':
            $sql = "SELECT userid FROM facture WHERE facturid='".$id."'";
            break;
    }

    $result = $DB->query_first($sql);

    return $result[0];

}
function is_fourn($DB,$adresse_mail){

    $fournisseur = $DB->query_first("SELECT * FROM fournisseur WHERE mail ='$adresse_mail' GROUP BY societe");

    return $fournisseur['societe'];

}
function is_client($DB,$adresse_mail){

    $client = $DB->query_first("SELECT userid FROM utilisateur WHERE mail ='$adresse_mail'");

    return $client[0];

}
?>
