<?php
include("admin_global.php");

$DB_site = $DB_site2;

include("../class/fonctions.php");
include("../class/email_client.php");

$usertel = (string)($user_info['tel'] != 0);

header("Cache-Control: no-cache");
header("Content-type:text/xml");

$_GET['site_code']="base";

echo ""
. "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
     . "<xml>\n";

if(isset($_GET['emailid'])){
     
     
     $lang = "FR";
     if(isset($_GET['mail_lang'])) $lang = $_GET['mail_lang'];
     $template = new template($lang,$DB_site2);
     $template->changesite($_GET['site_code'], $lang);
     //Préparation du template de mail (header classique/pro, FR/EN...)
     $sitepro = ($_GET['pro']==1 && $_GET['site_code']=="base");
     $template->parts["pro"] = $pro = ($sitepro ? "_pro" : "");
     $urlsite = $template->translate("/site/[site_code]/url");
     $urlsite = str_replace("https://","",$urlsite);
     if($sitepro) $urlsite = str_replace("www","pro",$urlsite);
     $template->parts['host_path']=$host_conf->host;
     $template->pro = $sitepro;
     $template->parts["urlsite"] = $urlsite;
     $template->parts["autocolor"]=($sitepro?"#0393c2":"#a88d5c");
     $template->parts["tracking"] = "";
     
     //Récupération des données concernant l'administrateur courant
     $username = (isset($_GET['username']) ? $_GET['username'] : $user_info['username']);
     
     $sql = "SELECT p.nom_contact, ac.nom_contact".$lang." as nom_contact_new, ac.mail".$lang." AS mail, ac.tel".$lang." AS tel, ac.fonction".$lang." as fonction";
     $sql .= " FROM admin_contact ac";
     $sql .= " INNER JOIN parametresgeneraux p USING(username)";
     $sql .= " WHERE ac.username='".$username."'";
     $sql .= " AND ac.site_code='".$_GET['site_code']."'";
     $admin_contact = $DB_site2->query_first($sql);
     
     //Mise en forme signature
     $template->parts["telephone"]    = $admin_contact['tel'];
     $template->parts["mail_contact"] = $admin_contact['mail'];
     $template->parts["utilisateur"]  = $admin_contact['nom_contact_new'];
     $template->parts["fonction"]  = $admin_contact['fonction'];
     
     //Récupération du contenu du mail à intégrer
     /*$sql = "SELECT emailid, libelle, sujet".$lang." AS sujet, contenu".$lang." AS contenu";
     $sql .= " FROM emails_clients";
     $sql .= " WHERE emailid = '".$_GET['emailid']."'";
     $email_client = $DB_site2->query_first($sql);
     $body = $email_client['contenu'];*/
    

      //récupération des infos du client
    if(isset($_GET['dest']) && $_GET['dest'] != '' && $_GET['dest'] != 'undefined'){
        $sql = "SELECT nom, prenom, civilite FROM utilisateur WHERE mail='".mysqli_real_escape_string($DB_site2->link_id,$_GET['dest'])."'";
        $client = $DB_site2->query_first($sql);

        $template->parts["nom"]=$client['nom'];
        $template->parts["prenom"]=$client['prenom'];
        $template->parts['civilite'] = ($client['civilite'] == '1' ? 'Madame' : 'Monsieur');
    }
    
    $tracking="";
    //tracking EMAIL Commercial
    if(isset($_GET['tracking']) && isset($_GET['tracking_type'])){
        $template->parts['tracking'] = $tracking =  " ".txt_tracking($_GET['tracking_type'],$_GET['tracking'],$_GET['userid']);
    }

    if(isset($_GET['tracking_sav'])){
        $template->parts['tracking'] = $tracking =  " #".$_GET['tracking_sav']."#";
    }
    
    $email_client=new email_client($DB_site2,$template,$_GET['emailid']);
    if(isset($_GET['notemplate'])){
        $body=$template->parts["mail_body"];
    }else{
        $template->parts["texte"]=$template->parts["mail_body"];
        
        //Paramètre indiquant qu'il faut utiliser le dernier modèle de template
        if(isset($_GET['use_base'])){
            
            //Remplissage du corps du mail
    //           $template->parts['texte'] = $email_client['contenu'];
    //           $template->parts["subject"]=$email_client['sujet'] ;
            
            //**************************************\\
            // ENCART "LA SELECTION DE NOS EXPERTS" \\
            //**************************************\\
            if(isset($_GET['selection'])){
                selection_vignette($_GET['selection']);
            }
            
            //******************************\\
            // ENCART "PRODUITS CONSEILLES" \\
            //******************************\\
            if(isset($_GET['artids'])){
                
                $artids = explode(",",$_GET['artids']);

                if(count($artids) > 0){
                        
                        $template->parts["articles_content"] = "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">";
                        $i = 0;
                        $cpt = 0;
                        foreach($artids as $artid){
                            
                            $center_vignette = false;
                            if($i == 2){
                                
                                $template->parts["articles_content"] .= ""
                                . "</tr>"
                                . "<tr>"
                                    . "<td colspan=\"2\" style=\"height:1px;border-top:solid 1px #808080;\"></td>"
                                . "</tr>"
                                . "<tr>";
                                
                                $i=0;
                                
                                if(!isset($artids[$cpt+1])) $center_vignette = true;
                            }
                            
                            $i++;
                            
                            vignette_mail($artid, $center_vignette);
                            
                            $cpt++;
                        }
                        
                        $template->parts["articles_content"] .= "</table>";

                        $template->evaltemplate("MailCommercial_articles","articles");
                }
            }
            
            //**********************************\\
            // ENCART "NOS UNIVERS A DECOUVRIR" \\
            //**********************************\\
            if(isset($_GET['univers'])){
                
                $universids = explode(",",$_GET['univers']);
                $universids = array_slice($universids,0,2);
                
                foreach($universids as $universid) univers($universid);

                $template->evaltemplate("MailCommercial_univers","univers");
            }
            
            //********************************\\
            // ENCART "POURQUOI CHOISIR MCAV" \\
            //********************************\\
            /*if(isset($_GET['pourquoi'])){
                
                $template->parts["pourquoi"] = ""
                . "<tr>"
                        . "<td height=\"10\"></td>"
                . "</tr>"
                . "<tr>"
                        . "<td height=\"50\"><img src=\"../images/mail/".$template->site_code()."/".$template->langue."/titre_pourquoi".$pro.".png\" align=\"center\"></td>"
                . "</tr>"
                . "<tr>"
                        . "<td><img src=\"../images/mail/".$template->site_code()."/".$template->langue."/pourquoi_choisir".$pro.".png\"></td>"
                . "</tr>";
            }*/



            
            //**********************\\
            // ENCART "NOS MARQUES" \\
            //**********************\\
            if(isset($_GET['marques'])) $template->evaltemplate("MailCommercial_marques","marques");

            //**********************\\
            // ENCART "Mail conseil" \\
            //**********************\\
            //if(isset($_GET['conseils'])) $template->evaltemplate("MailCommercial_conseils","conseils");
            
            //*********************\\
            // BAS DE PAGE DE MAIL \\
            //*********************\\
            $template->parts["ext_footer"] = '';
            if($_GET['footer'] != ""){
                
                if($_GET['footer'] == "complet"){
                        
                        $template->parts["footer"] = ""
                        . "<tr>"
                            . "<td align=\"center\"><img src=\"https://".$template->parts["urlsite"]."/images/mail/".$template->site_code()."/".$template->langue."/footer_reassur.png\" usemap=\"#footer\"></td>"
                        . "</tr>";
                        
        // $template->parts["ext_footer"] = "<br>".$template->translate("/mail/asterisques");                    
                }

                if($_GET['footer'] == "reduit"){
                        
                        $template->parts["footer"] = ""
                        . "<tr>"
                            . "<td align=\"center\"><img src=\"https://".$template->parts["urlsite"]."/images/mail/".$template->site_code()."/".$template->langue."/copyright.png\"></td>"
                        . "</tr>";
                }
            }
            
            //***************************\\
            // ENCART "BON DE REDUCTION" \\
            //***************************\\
            if(isset($_GET['cadeau'])) codepromo($_GET['cadeau']);
        
            $template->evaltemplate("MailCommercial_signature","texte");
            switch($_GET['emailid'])
            {
                case 179:
                    $template_use = 'MailCommercial_Sirha2017';
                    break;
                default:
                    $template_use = 'MailCommercial';
                    break;
            }
            $template->evaltemplate($template_use,"mailcommercial");
            
            $body = stripslashes($template->parts['mailcommercial']);
        }else{
            $body=$email_client->body;
        }
    }
    
    print "<email libelle=\"".$email_client->libelle."\" sujet=\"".$email_client->subject.$tracking. "\"><![CDATA[".$body."]]></email>";
}

if(isset($_GET['fid'])){
    $requete=$DB_site2->query("SELECT f.* FROM fournisseur f INNER JOIN fournisseur_condition fc USING(societe) WHERE f.id=".intval($_GET['fid'])." AND f.mail<>'' AND fc.activite = '1' AND f.type != 'old' GROUP BY f.mail ORDER BY f.nom");
    while($contact=$DB_site2->fetch_array($requete)){
	    $nom=$contact['nom']." ".$contact['prenom'];
        print "<type id=\"srcfournisseur\" />";
	    print "<contact id=\"$contact[id]\" mail=\"$contact[mail]\" fonction=\"".cleantxt($contact[fonction])."\" nom=\"".cleantxt($nom)."\" societe=\"".cleantxt($contact[societe])."\" telephone=\"$contact[telephone]\" portable=\"$contact[portable]\" tel=\"$usertel\" type=\"pro\" caller=\"".$user_info['tel'.$langue]."\" />\n";
      //print "<a href=\"javascript:addcontact('$contact[mail]','$nom');\" title=\"$contact[mail]\"><img src=\"/admin/images/pro.png\" height=\"20\" border=\"0\" align=\"absmiddle\">".surligne($nom,$txt)." (".utf8_encode(surligne($contact['fonction']." ".$contact['societe'],$txt)).")</a><br>";
    }
}

if($_GET['txt']!="" && strlen($_GET['txt'])>2){
  $txt=mysqli_real_escape_string($DB_site2->link_id,$_GET['txt']);
  $id=mysqli_real_escape_string($DB_site2->link_id,$_GET['id']);
  print "<type id=\"$id\" />";
  $requete=$DB_site2->query("SELECT mail_contact,nom_contact,telephone,adresse,cp,ville FROM parametresgeneraux WHERE (nom_contact LIKE '%$txt%' OR 'acilparametreyon' LIKE '%$txt%') AND mail_contact<>'' ORDER BY nom_contact");
  while($contact=$DB_site2->fetch_array($requete)){
    $contact_infos=$DB_site2->query_first("SELECT mailFR AS mail FROM admin_contact WHERE username='contact' AND site_code='base'");
	  $nom=$contact['nom_contact'];
	  print "<contact mail=\"$contact[mail_contact]\" nom=\"$nom\" adresse=\"$contact[adresse]\" codepostal=\"$contact[cp]\" ville=\"$contact[ville]\" pays=\"\" telephone=\"$contact[telephone]\" portable=\"\" type=\"macave\" caller=\"".$user_info['tel'.$langue]."\" languemail=\"$langue\" site_code=\"base\" mailcontact=\"".$contact_infos['mail']."\"/>\n";
    //print "<a href=\"javascript:addcontact('$contact[mail_contact]','$nom');\" title=\"$contact[mail_contact]\"><img src=\"/favicon.ico\" border=\"0\" align=\"absmiddle\">".surligne($nom,$txt)."</a><br>";
  }
  if($id == "" || $id == "srcfournisseur" || $id == "listfournisseur"){
    //// pour avoir la bonne liste ci-dessous, aller voir fournisseurs_liste.php
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

    /// tâche #7062 - combiner la recherche avec société / type / fonction
    $where_and = "";
    $array_txt = explode(" ",$txt);
    if(count($array_txt) > 1 && $array_txt[1]!==''){
        foreach($array_txt as $text){
            $text=trim($text);
            $txt=trim($txt);
            $txttype="";
            $pattern = '/' . preg_quote($text, '/') . '/i';
            $txt_type=preg_grep($pattern,$types);
            if(!empty($txt_type)){
                $txttype="OR f.type LIKE '%".key($txt_type)."%'";
            }
            $where_and .= " AND (f.societe LIKE '%$text%' OR f.nom LIKE '%$text%' OR f.fonction LIKE '%$text%' ".$txttype.")"; 
        }
    }
    else {
        $txt=trim($txt);
        $txttype="";
        $pattern = '/' . preg_quote($txt, '/') . '/i';
        $txt_type=preg_grep($pattern,$types);
        if(!empty($txt_type)){
            $txttype="OR f.type LIKE '%".key($txt_type)."%'";
        }
    }
    //On rajoute un group by dans la requête pour réccuperer uniquement les noms de société
    $groupby="";
    if($id == "listfournisseur"){
        $groupby= 'GROUP BY f.societe';
    }
    $requete=$DB_site2->query("SELECT f.* FROM fournisseur f INNER JOIN fournisseur_condition fc USING(societe) WHERE (f.societe LIKE '%$txt%' OR f.nom LIKE '%$txt%' OR f.fonction LIKE '%$txt%' ".$txttype.") $where_and AND f.mail<>'' AND fc.activite = '1' AND f.type != 'old' ".$groupby." ORDER BY f.nom");

    while($contact=$DB_site2->fetch_array($requete)){
      $pays = $DB_site2->query_first("SELECT libelle FROM pays WHERE paysid=".$contact['paysid']);
	    $nom=$contact['nom']." ".$contact['prenom'];
	    print "<contact id=\"$contact[id]\" mail=\"$contact[mail]\" fonction=\"".cleantxt($contact[fonction])."\" nom=\"".cleantxt($nom)."\" societe=\"".cleantxt($contact[societe])."\" type_fourn=\"".cleantxt($types[$contact[type]])."\" adresse=\"".cleantxt($contact[adresse])."\" codepostal=\"$contact[codepostal]\" ville=\"".cleantxt($contact[ville])."\" pays=\"$pays[0]\" telephone=\"$contact[telephone]\" portable=\"$contact[portable]\" tel=\"$usertel\" type=\"pro\" caller=\"".$user_info['tel'.$langue]."\" languemail=\"$langue\" site_code=\"base\" mailcontact=\"$contact_infos[mail]\" typesrc=\"".$id."\" />\n";
      //print "<a href=\"javascript:addcontact('$contact[mail]','$nom');\" title=\"$contact[mail]\"><img src=\"/admin/images/pro.png\" height=\"20\" border=\"0\" align=\"absmiddle\">".surligne($nom,$txt)." (".utf8_encode(surligne($contact['fonction']." ".$contact['societe'],$txt)).")</a><br>";
    }
  }

  if($id=="" || $id=="srcclient"){
     /// tâche #7062 - combiner la recherche avec nom / prenom / raisonsociale / ville
    $where_and = "";
    $array_txt = explode(" ",$txt);
    if(count($array_txt) > 1 && $array_txt[1]!==''){
        foreach($array_txt as $text){
            $text=trim($text);
            $txt=trim($txt);
            if(is_numeric($txt)){
                $where = "userid LIKE '%$text%' OR telephone LIKE '%$text%'";
            }else{
                $where = "nom LIKE '%$text%' OR raisonsociale LIKE '%$text%'";
            }
            $where_and .= " AND (prenom LIKE '%$text%' OR nom LIKE '%$text%' OR raisonsociale LIKE '%$text%' OR ville LIKE '%$text%')"; 
        }
    }
    else{
        $txt=trim($txt);
        if(is_numeric($txt)){
            $where = "userid LIKE '%$txt%' OR telephone LIKE '%$txt%'";
        }else{
            $where = "prenom LIKE '%$txt%' OR nom LIKE '%$txt%' OR raisonsociale LIKE '%$txt%' OR ville LIKE '%$txt%'";
        }
    }
    $requete=$DB_site2->query("SELECT * FROM utilisateur WHERE ($where) $where_and AND mail<>'' ORDER BY nom");
    while($contact=$DB_site2->fetch_array($requete)){
      $pays = $DB_site2->query_first("SELECT libelle FROM pays WHERE paysid=".$contact['paysid']);
	    $nom=$contact['nom']." ".$contact['prenom'];
	    print "<contact userid=\"$contact[userid]\" mail=\"$contact[mail]\" nom=\"".cleantxt($nom)."\" societe=\"".cleantxt($contact[raisonsociale])."\" telephone=\"".cleantxt($contact[telephone])."\" portable=\"$contact[portable]\" tel=\"$usertel\" adresse=\"".cleantxt($contact[adresse])."\" codepostal=\"".cleantxt($contact[codepostal])."\" ville=\"".cleantxt($contact[ville])."\" paysid=\"$contact[paysid]\" pays=\"".$pays[0]."\" type=\"client\" caller=\"".$user_info['tel'.$langue]."\" typesrc=\"".$id."\" />\n";
      $autres_contact=$DB_site2->query("SELECT * FROM utilisateur_assoc WHERE userid=".$contact[userid]." AND actif != 0");
      while($autre_contact=$DB_site2->fetch_array($autres_contact)) {
        $nom = $autre_contact[utilisateur_assoc_prenom]." ".$autre_contact[utilisateur_assoc_nom];
        print "<contact assoc_id=\"$autre_contact[utilisateur_assoc_id]\" mail=\"$autre_contact[utilisateur_assoc_email]\" nom=\"".cleantxt($nom)."\" fonction=\"".cleantxt($autre_contact[utilisateur_assoc_fonction])."\" telephone=\"$autre_contact[utilisateur_assoc_tel]\" portable=\"$autre_contact[utilisateur_assoc_portable]\" tel=\"$usertel\" adresse=\"\" societe=\"".cleantxt($contact[raisonsociale])."\" codepostal=\"\" ville=\"\" paysid=\"\" pays=\"\" type=\"client\" caller=\"".$user_info['tel'.$langue]."\" />\n";
      }
    }
  }
}

if(isset($_GET['relance'])){
    if(isset($_GET['stockrelance']) && $_GET['stockrelance']==true){
        $DB_site2->query("UPDATE stock_cmd SET date_relance = ".time()." WHERE id=".$_GET['relance']);
    }
    else{
        $DB_site2->query("UPDATE envoi_fournisseur SET date_relance = ".time()." WHERE id=".$_GET['relance']);
    }
}

if(isset($_GET['mail'])){
    $requete=$DB_site2->query("SELECT * FROM utilisateur WHERE mail='".$_GET['mail']."' ORDER BY nom");
    while($contact=$DB_site2->fetch_array($requete)){
// 	$nom=utf8_encode($contact['nom'])." ".utf8_encode($contact['prenom']);
	$nom=$contact['nom']." ".$contact['prenom'];
	print "<contact userid=\"$contact[userid]\" mail=\"$contact[mail]\" nom=\"".cleantxt($nom)."\" societe=\"".cleantxt($contact[raisonsociale])."\" telephone=\"$contact[telephone]\" portable=\"$contact[portable]\" tel=\"$usertel\" adresse=\"".cleantxt($contact[adresse])."\" codepostal=\"$contact[codepostal]\" ville=\"".cleantxt($contact[ville])."\" paysid=\"$contact[paysid]\" type=\"client\" caller=\"".$user_info['tel'.$langue]."\" />\n";
    }
}

if(isset($_GET['services'])){
    $services=explode(",",$_GET['services']);
    $requete=$DB_site2->query("SELECT libelle,service,emailid FROM emails_clients WHERE site_code='".$_GET['site_code']."' AND service IN ('".implode("','",$services)."') ORDER BY service,libelle");
    while($email=$DB_site2->fetch_array($requete)){
	print "<email id=\"".$email['emailid']."\" service=\"".$email['service']."\">".$email['libelle']."</email>";
    }
}

if(isset($_GET['signature']))
{
    $lang      = "FR";
    $site_code = "base";
    
    if(isset($_GET['mail_lang']) && $_GET['mail_lang'] != "") $lang = $_GET['mail_lang'];
    if(isset($_GET['site_code']) && $_GET['site_code'] != "") $site_code = $_GET['site_code'];
    if(!isset($_GET['utilisateur']))
    {
        $user = $user_info;
    }
    else
    {
        $sql = "SELECT nom_contact, username";
        $sql .= " FROM parametresgeneraux";
        $sql .= " WHERE username = '".$_GET['utilisateur']."'";
        $user = $DB_site2->query_first($sql);
    }
	 
    if($user)
    {
        $sql = "SELECT *";
        $sql .= " FROM admin_contact";
        $sql .= " WHERE site_code = '".$site_code."'";
        $sql .= " AND username = '".$user['username']."'";
    }
    else
    {
        $sql = "SELECT *";
        $sql .= " FROM admin_contact";
        $sql .= " WHERE site_code = '".$site_code."'";
        $sql .= " AND username = '".$_GET['utilisateur']."'";
    }
    $admin_contact = $DB_site2->query_first($sql);

    if(isset($_GET['exp'])){
        $admin_contact=$DB_site2->query_first("SELECT * FROM admin_contact WHERE mail". $lang."='".$_GET['exp']."'");
    }
    
    $signaturemail = signature_mail($site_code, $lang, $admin_contact['nom_contact' . $lang], $admin_contact['tel' . $lang], $admin_contact['mail' . $lang], $admin_contact['fonction' . $lang]);

    print "<signature><![CDATA[" . $signaturemail . "]]></signature>";
    print "<exp mail=\"" . $admin_contact['mail' . $lang] . "\" >" . $admin_contact['nom_contact' . $lang] . "</exp>";
}

if(isset($_GET['mailid'])){
  $mailbox="mail_".$_GET['type'];
  $re=intval($_GET['re']);
  $responseto=array("mail_inbox"=>"from","mail_outbox"=>"to");

  $mail=$DB_site2->query_first("SELECT * FROM ".$mailbox." WHERE id=".intval($_GET['mailid']));
  $response=($re==1?$mail[$responseto[$mailbox]]:"");
  $psubject=($re==1?"RE:":"TR:");
  if($_GET['type']=="inbox"){
  	$destinfo=findemail($mail['from']);
  	if($destinfo) $info.="<br><b>De :</b> ".$destinfo['display'];
  	$info.="<br><b>Envoyé :</b> ".date("d/m/Y H:i",$mail['date']);
  	$maillangues=array();
  	foreach($langues as $l) $maillangues[]="mail".$l;
  	$dest=$DB_site2->query_first("SELECT nom_contact FROM parametresgeneraux INNER JOIN admin_contact USING(username) WHERE '".$mail['to']."' IN (".implode(",",$maillangues).")");
  	$info.="<br><b>A :</b> ".$dest['nom_contact'];

  }else{
  	
    $contact=$DB_site2->query_first("SELECT nom_contact FROM parametresgeneraux WHERE username='".$mail['utilisateur']."'");
  	$info="<b>De :</b> ".$contact['nom_contact'];
  	$info.="<br><b>Envoyé :</b> ".date("d/m/Y à H:i",$mail['date']);
  	$destlist=explode(";",$mail['to']);
  	foreach($destlist as $destmail){
  	    $destinfo=findemail($destmail);
  	    if($destinfo) $info.="<br><b>A :</b> ".$destinfo['display'];
  	}
  }
    $info.="<br><b>Objet :</b> ".$mail['subject']."AAAAAAAA";

    if($re == 0) $info .="<br>".$mail['body'];

    print "<info><![CDATA[".$info."]]></info>\n";
    print "<typesuivi>".$mail['suivitype']."</typesuivi>\n";
    print "<mail date=\"".$mail['date']."\" from=\"".$mail['from']."\" to=\"".$mail['to']."\" response=\"".$response."\" psubject=\"".$psubject."\" subject=\"".str_replace("&","&amp;",$mail['subject'])."\">
    <![CDATA[".$mail['body']."]]>
    </mail>";
  
    if($re==0){

	   $pjlist=$DB_site2->query("SELECT * FROM mail_attachment ma WHERE mailid=".intval($_GET['mailid']));
	   while($pj=$DB_site2->fetch_array($pjlist)){
	     $year = date("Y",$mail['date']);
        print "<pj name=\"".$pj['name']."\" path=\"attachments/$year/".$pj['id'].".".$pj['ext']."\" size=\"".$pj['size']."\" />";
	   }
    }
}

print "</xml>";

function surligne($txt,$src){
    return preg_replace( '/('.$src.')/i', '<b>$1</b>',$txt);
}

function cleantxt($txt){
    $find=array("&","\"");
    $replace=array("&amp;","&quot;");
    return str_replace($find,$replace,$txt);
}

function findemail($email){
    global $DB_site2;
    
    if($email=="") return false;
    $contact=$DB_site2->query_first("SELECT * FROM utilisateur WHERE mail='".$email."'");
    if($contact=="") $contact=$DB_site2->query_first("SELECT f.*,f.societe AS raisonsociale,fc.langue FROM fournisseur f LEFT JOIN fournisseur_condition fc USING(societe) WHERE mail='".$email."'");
    if($contact=="") $contact['mail']=$email;
    $contact['display']=trim($contact['prenom']." ".$contact['nom'].($contact['raisonsociale']!=""?" - ".$contact['raisonsociale']:"")." [".$contact['mail']."]");
    return $contact;
}

function getInfosArticle($artid){
     global $DB_site2;
     global $template;
     $sqlSub = "SELECT active";
     $sql .= " FROM article_site";
     $sql .= " WHERE artid = ".intval($artid);
     $sql .= " AND site_code = '".$template->site_code()."'";
     $sql .= " LIMIT 1";
     $sql = "SELECT artid, typearticleid, libelle".$template->langue.", libelle".$template->langue." AS libelle, artcode, (".$sqlSub.") AS active, commandable, gamme".$template->langue." AS gamme, modele, coloris, hauteur, largeur, profondeur";
     $sql .= " FROM article";
     $sql .= " WHERE artid = ".intval($artid);
     $article = $DB_site2->query_first($sql);
     return $article;
}

function getInfosMarque($artid){
     
     global $DB_site2;
     global $template;
     
     $sql = "SELECT libelle, m.marqueid, image, m.type".$template->langue." AS mtype";
     $sql .= " FROM marque m, article_marque am";
     $sql .= " WHERE m.marqueid = am.marqueid";
     $sql .= " AND am.artid = '".$artid."'";
     $mq = $DB_site2->query_first($sql) ;  
     
     return $mq;
}

function formatVignette($article, $mq, $type){
     
     global $DB_site2;
     global $template;
     global $sitepro;
     
     switch($type){
          case 'conseil':
               
               $width_logomarque      = '90';
               $max_height_logomarque = '54';
               $img_height            = 202;
               $width_limit           = 132;
               
               break;
          case 'selection':
               
               $width_logomarque      = '128';
               $max_height_logomarque = '60';
               $img_height            = 220;
               $width_limit           = 160;
               
               break;
     }
     
     $host = $template->translate("/site/[site_code]/url");
     if($template->parts["pro"]!="") $host = str_replace("www","pro",$host);
     
     $template->parts["arturl"]     = $host.$template->mkurl("article",$article['artid'],$article);
     $template->parts['libelle']    = $article['libelle'];
     $template->parts['gamme']	    = strtoupper($mq['libelle']." ".$article['gamme']);
     $template->parts['ref']        = strtoupper($article['artcode']);
     $template->parts["logomarque"] = "<img src=\"https://".$template->parts["urlsite"]."/configurations/".$template->host_conf->host."/images/marques/br/".$mq['marqueid'].".jpg\" alt=\"".$mq['mtype']."\" width=\"".$width_logomarque."\" style=\"width:".$width_logomarque."px; max-height:".$max_height_logomarque."px;\">";
     
     //Vignette article
     $sql = "SELECT *";
     $sql .= " FROM articlephoto";
     $sql .= " WHERE artid = ".$article['artid'];
     $sql .= " AND visible = 1";
     $sql .= " AND (langue='' OR langue='".$template->langue."')";
     $autreimg = $DB_site2->query_first($sql);
     
     $autre = "";
     if($autreimg!="") $autre = "_".$autreimg['articlephotoid'];
     $imgpath = "http://".$template->parts["urlsite"]."/configurations/".$template->host_conf->host."/images/produits/br/".$article['artid'].$autre.".jpg";
     $size   = getimagesize($imgpath);
     $height = $img_height;
     $width  = round($height*$size[0]/$size[1]);
     if($width > $width_limit){
         $width  = $width_limit;
         $height = round($width*$size[1]/$size[0]);
     }
     $template->parts["artimg"]="<a href=\"".$template->parts["arturl"]."\"><img src=\"".$imgpath."\" alt=\"".$article['libelle']." ".$mq[0]."\" width=\"".$width."\" height=\"".$height."\"></a>";
     
}

function vignette_mail($artid, $center_vignette = false){
     
     global $DB_site2;
     global $template;
     global $sitepro;
     
     //Récupération des données concernant l'article
     $article = getInfosArticle($artid);
     if($article[0]=="") return "";
     
     //Récupération des données concernant la marque
     $mq = getInfosMarque($artid);
     $article['marque']=$mq['libelle'];
     
     //Mise en forme de la vignette article
     formatVignette($article, $mq, 'conseil');

     //Récupération tarifs
     $taxtxt = ($sitepro?"/site/ht":"/site/ttc");

     $article_prices = article_prices($article['artid'],$template->tva_ref(),$template->site_code(),$sitepro);

     $sql = "SELECT COUNT(*)";
     $sql .= " FROM promo";
     $sql .= " WHERE artid = ".$article['artid'];
     $promo = $DB_site2->query_first($sql);
     
     $sql = "SELECT COUNT(*)";
     $sql .= " FROM secondchoix";
     $sql .= " WHERE artid = ".$article['artid'];
     $secondchoix = $DB_site2->query_first($sql);

     $pc = "";
     if(round($article_prices['prix'])!=round($article_prices['prixconseille']) && ($promo[0]>0 || $secondchoix[0]>0)){
          
         $strikeout = "<s>".$template->price_format($article_prices['prixconseille'])." ".$template->translate($taxtxt,"U")."</s>";
         $pc        = "<div style=\"width:25px;height:25px;background:#df4126;color:white;padding:5px;font-size:12px;font-weight:bold;text-align:center;line-height:25px;\">".-round($article_prices['remise'])."%</div>";
     }
     if(intval($article['commandable']) == 0){ //si le produit a le prix sur demande on n'affiche pas de prix dans le mail
      $price = "";
      $taxe = "";
     }else{
      $price = $template->price_format($article_prices['prix']);
      $taxe = $template->translate($taxtxt,"U");
     }
     $template->parts['prix'] = ""
     . "<table width=\"100%\" height=\"40\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"font-size:11px;\">"
          . "<tr>"
               . "<td height=\"20\">".$strikeout."</td>"
               . "<td rowspan=\"2\">".$pc."</td>"
          . "</tr>"
          . "<tr>"
               . "<td height=\"20\" style=\"color:black;font-size:14px;font-weight:bold;white-space:nowrap;\">".$price." ".$taxe."</td>"
          . "</tr>"
     . "</table>";

     $template->parts["articles_content"] .= "<td ".($center_vignette ? 'align=\"center\"' : '')." text-align=\"center\" ".($center_vignette ? 'colspan=\"2\"' : '').">";
     $template->evaltemplate("vignetteMail","articles_content");
     $template->parts["articles_content"] .= "</td>";
}

function selection_vignette($artid){
     
     global $DB_site2;
     global $template;
     global $sitepro;
     
     //Récupération des données concernant l'article
     $article = getInfosArticle($artid);
     if($article[0]=="") return "";
    
     //Récupération des données concernant la marque
     $mq = getInfosMarque($artid);
     
     //Mise en forme de la vignette article
     formatVignette($article, $mq, 'conseil');
     
     //Récupération tarifs
     $taxtxt = ($sitepro?"/site/ht":"/site/ttc");
     $article_prices = article_prices($article['artid'],$template->tva_ref(),$template->site_code(),$sitepro);
     
     $sql = "SELECT COUNT(*)";
     $sql .= " FROM promo";
     $sql .= " WHERE artid = ".$article['artid'];
     $promo = $DB_site2->query_first($sql);
     
     $sql = "SELECT COUNT(*)";
     $sql .= " FROM secondchoix";
     $sql .= " WHERE artid = ".$article['artid'];
     $secondchoix = $DB_site2->query_first($sql);
     
     $template->parts['pc'] = "";
     
     if(round($article_prices['prix']) != round($article_prices['prixconseille']) && ($promo[0]>0 || $secondchoix[0]>0)){
          
          $strikeout = "<s style=\"font-size:12px;\">".$template->price_format($article_prices['prixconseille'])." ".$template->translate($taxtxt,"U")."</s>";
          $template->parts['pc'] = "<div style=\"width:60px;height:60px;background:#df4126;color:white;padding:5px;font-size:24px;font-weight:bold;text-align:center;line-height:60px;\">".-round($article_prices['remise'])."%</div>";
     }
     if(intval($article['commandable']) == 0){ //si le produit a le prix sur demande on n'affiche pas de prix dans le mail
      $price = "";
      $taxe = "";
     }else{
      $price = $template->price_format($article_prices['prix']);
      $taxe = $template->translate($taxtxt,"U");
     }
     $template->parts['prix'] = ""
     . "<div style=\"margin:10px 0 0 0;\">"
          . $strikeout
          . "<br>"
          . "<span style=\"color:black;font-weight:bold;font-size:16px;\">".$price." ".$taxe."</span>"
     . "</div>";
    
     $desc = "";
     if($article['nbr_temperature'] != "") $desc .= "<li>".$template->translate($article['nbr_temperature'])." ".$template->translate("/article/vignette/temperatures")."</li>";
     if($article['capacite_bouteilles']!="") $desc .= "<li>".$template->translate("/article/vignette/capacite","F")." : ".$article['capacite_bouteilles']." ".$template->translate($article['capacite_unite'])."</li>";
     if($article['coloris'] != "") $desc .= "<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($article['coloris'])."</li>";
     if($article['hauteur']>0) $desc .= "<li>".$template->translate("/article/vignette/dim","F")." : ".sprintf($template->translate("/article/champs/dimensions_format"),$article['hauteur'],$article['largeur'],$article['profondeur'])."</li>";
     
     $template->parts["liste"] = "<ul style=\"padding:0;font-size:11px;list-style-type:none;\">".$desc."</ul>";

     $template->evaltemplate("SelectionMail","selection");
}

function univers($id){
     
     global $DB_site2;
     global $template;
     global $sitepro;

     $sql = "SELECT id, libelle".$template->langue." AS libelle, url".$template->langue." AS url";
     $sql .= " FROM univers";
     $sql .= " WHERE id = ".intval($id);
     $univers = $DB_site2->query_first($sql);
     if($univers == false) return false;
     
     $host = $template->translate("/site/[site_code]/url");
     if($sitepro) $host = str_replace("www","pro",$host);
     $url = $host."/".$univers['url'];

     $uid = 1;
     if($template->parts['univers_content'.$uid] != "") $uid++;

     $template->parts['univers_content'.$uid] .= ""
     . "<td align=\"center\">"
          . "<table width=\"273\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">"
               . "<tr>"
                    . "<td height=\"36\" bgcolor=\"#372c28\" style=\"margin:0;padding:0 10px;background:#372c28;height:36px;text-align:center;\"><a href=\"".$url."\" style=\"color:white;font-size:16px;font-weight:bold;text-decoration:none;\">".mb_strtoupper($univers['libelle'],'utf-8')."</a></td>"
               . "</tr>"
               . "<tr>"
                    . "<td><a href=\"".$url."\"><img src=\"https://".$template->parts["urlsite"]."/images/mail/univers/".$univers['id'].".jpg\" width=\"273\" height=\"182\"></a></td>"
               . "</tr>"
          . "</table>"
     . "</td>";

}

function codepromo($id){
     
     global $DB_site2;
     global $template;
     
     $code=$DB_site2->query_first("SELECT * FROM cadeau WHERE code='".mysqli_real_escape_string($DB_site2->link_id,$id)."'");
     
     $template->parts["code"]      = $id;
     $template->parts["serverurl"] = $_SERVER['HTTP_HOST'];
     if($code['datefin']==0)
	$template->parts["hidejusquau"]="display:none;";
     else
	$template->parts["jusquau"]   = date("d/m/Y",$code['datefin']);
     
     $template->evaltemplate("MailCommercial_codepromo","codepromo");
}
