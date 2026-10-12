<?php

// TSI ATTENTION !!!!!!!!! Le fonctionnement du bordel fait que les parametres retournés par Register.html 
// sont en partis traités dans global.php !!! ( voir ligne 320 )
// C'est heuuuuuuuuuuuuuuuu !!!!!!!!!!!   ##$$$!!!@@@###$$$ !!!!!
// 
require ("global.php");
$title="title".$template->langue;

if($sitepro)
	$title_desc = '/index/meta_description_pro';
else
	$title_desc = '/index/meta_description';

$template->pageparams($template->translate("/site/url"),$template->translate("/site/compte","F"),$template->translate($title_desc));
//ici on fais afficher les balise hreflang pour fr et en
foreach($template->sites() as $site_code=>$site){
    foreach($site['hosts'] as $url=>$deflangue){
    if($site['actif'][$url])
        $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url."/client.htm\">\n";
    }
}
//Overlay et Modal 
    // Modal d'erreurs
$template->parts['modaltype']=$template->parts['modal']='';
$template->parts["modalclass"] = "modal";
$template->evaltemplate("ModalErreur","modal");
$template->evaltemplate("Overlay","modaltype");

if(isset($_GET['mail_nl'])) { //quand k'utilisateur renseigne son email dans le bloc newletter sur l'accueil, on check si il a deja un compte
	
	$mail_nl = mysqli_escape_string($DB_site->link_id,$_GET['mail_nl']);
	if(isset($user_info)){
		$action = "modifyabo";
	}else{

		$sql = "SELECT userid";
		$sql .= " FROM utilisateur";
		$sql .= " WHERE mail = '".$mail_nl."'";
		$user = $DB_site->query_first($sql);

		$action = "";

		// verif si l'email n'est pas deja enregistré
		if($user[0] == ""){
			$template->parts["jsinscription"]="document.getElementById('register1').style.display='none';\ndocument.getElementById('register2').style.display='block';";
		}else{
			
			$template->parts["jsinscription"] ="document.getElementById('register2').style.display='none';\ndocument.getElementById('register1').style.display='block';\n";
			$template->parts["jsinscription"].="document.getElementById('email_log').value ='".$mail_nl."'";
		}
	}
}



$civint=array(0=>"choisissez_mr",1=>"choisissez_mme",2=>"choisissez_mlle");

$allowemail = $newsletter= 1;
$allowsms = 1;
$allowconseils = 'non';
if (isset($_POST[news_mail])) $allowemail=$_POST[news_mail];
if (isset($_POST[news_sms])) $allowsms= $_POST[news_mail];
if (isset($_POST[conseils])) $allowconseils = $_POST[conseils];

$site_code=$template->site_code();
if($site_code=="") $site_code="base";

$template->parts["hideinfo1"]=$template->parts["hideinfo2"]=$template->parts["hideinfo3"]=$template->parts["hideinfo4"]="hidden";

$haut =  "" ;
if(isset($_POST['action0']) && $_POST['action0'] != ""){
	$affiche = "RegisterPart";
	if($_POST['propart'] == '2'){
		$affiche = "RegisterPro";
	}
	$template->parts["zonedeliste_pays"]="";
	$payss=$DB_site->query("SELECT * FROM pays INNER JOIN fraisport USING(paysid) WHERE sites='".$site_code."' GROUP BY paysid ORDER BY libelle ASC");
	while($pays=$DB_site->fetch_array($payss)){
		$template->parts["zonedeliste_pays"] .= "<option value=$pays[paysid] ".($pays[paysid]==57?"selected":"").">$pays[libelle]</option>";
	}
	$template->parts["mail"]=(isset($_POST['mail_log'])?$_POST['mail_log']:"");
	$template->evaltemplate($affiche,"pagecommande");
	$template->evaltemplate("PanierPage","main");
}

if (isset($action2) and $action2 != ""){
    switch ($action2){
        case 'inscription2': // cas de modification des données utilisateur
        	
        	// mise en place civilité renseigné
            if (isset($civilite) and $civilite!=""){
                if($civilite=='0') { $selectmr="selected"; }
                if($civilite=='1') { $selectmme="selected"; }
                if($civilite=='2') { $selectmelle="selected"; }
			}
			
            $zonedeliste_pays = "";
            $payss=$DB_site->query("SELECT * FROM pays WHERE paysid IN (select DISTINCT(paysid) FROM fraisport WHERE sites='$site_code' ORDER BY libelle ASC");
            while($pays=$DB_site->fetch_array($payss)){
                if($pays[paysid]==57) 
                    $selected="selected"; 
                else 
                    $selected="";

                $zonedeliste_pays .= "<option value=$pays[paysid] $selected>$pays[libelle]</option>";
            }
            $action = "bidule" ;
            // champ optionnel
        
			$connusite="<select name=\"connu\"><option></option>";
			$creq=$DB_site->query("SELECT * FROM formulaire_champ_valeur WHERE formulairechampid=74 ORDER BY valeur");
			while($cdata=$DB_site->fetch_array($creq)){
			  $connusite.="<option value=\"".$cdata['valeur']."\">".$cdata['valeur'.$template->langue]."</option>";
			}
			$connusite.="</select>";
			//eval( "\$TemplateClient = \"$templatecache[register]\";" );
            break ;
			
        case 'domodify': // modification de la fiche client
            $errormessage="";
            if (!isset($civilite) or $civilite=="") { $action=""; $errormessage.=$template->translate("/client/erreur_civilite")."<br> "; }
            if (isset($civilite) and $civilite!='0' and $civilite!='1' and $civilite!='2') { $action=""; $errormessage.=$template->translate("/client/erreur_civilite")."<br> "; }
            if (!isset($nom) or $nom=="") { $action=""; $errormessage.=$template->translate("/client/erreur_nom")."<br> "; }
            if (!isset($prenom) or $prenom=="") { $action=""; $errormessage.=$template->translate("/client/erreur_prenom")."<br> "; }
            if (!isset($adresse) or $adresse=="") { $action=""; $errormessage.=$template->translate("/client/erreur_adresse")."<br> "; }
            if (!isset($codepostal) or $codepostal=="") { $action=""; $errormessage.=$template->translate("/client/erreur_codepostal")."<br> "; }
            if (!isset($ville) or $ville=="") { $action=""; $errormessage.=$template->translate("/client/erreur_ville")."<br> "; }
            if (!isset($telephone) or $telephone=="") { $action=""; $errormessage.=$template->translate("/client/erreur_telephone")."<br> "; }
            if (!isset($paysid) or $paysid=="" or !preg_match('#^[0-9]+$#', $paysid)) { $action=""; $errormessage.=$template->translate("/client/erreur_pays")."<br> "; }

            $mailtva=false;
			if(trim($raisonsociale)!=""){
				$pays=$DB_site->query_first("SELECT * FROM pays WHERE paysid=".$paysid);
				if($pays['cee']==1){
					if(trim($tvaintracom)!=""){
						$tvaintracom= str_replace(array(' ', '.', '-', ',', ', '), '', $tvaintracom);
						$result=checkvatnumber($tvaintracom);
						if($result->valid==false) {
							$action="";
							$template->parts["errormessage"].=$template->translate("/client/erreur_tva_intracom")."<br>";
						}
					}elseif($pays['paysid']==57 || $pays['paysid']==181)
						$mailtva=true;
				}else
					$tvaintracom="";
			}else
				$tvaintracom="";
			
			// champ optionnel
            if ($errormessage == ""){
				
				$commercial="";
				if(isset($_COOKIE['admin_username']) && $_COOKIE['admin_username']!="") $commercial=$_COOKIE['admin_username'];
				$txt="";
				if($user_info['raisonsociale']!=$raisonsociale) $txt.="<br>".$user_info['raisonsociale']." => ".$raisonsociale;
				if($user_info['nom']!=$nom) $txt.="<br>Nom : ".$user_info['nom']." => ".$nom;
				if($user_info['prenom']!=$prenom) $txt.="<br>Prenom : ".$user_info['prenom']." => ".$prenom;
				if($user_info['adresse']!=$adresse) $txt.="<br>Adresse : ".$user_info['adresse']." => ".$adresse;
				if($user_info['codepostal']!=$codepostal) $txt.="<br>Code postal : ".$user_info['codepostal']." => ".$codepostal;
				if($user_info['ville']!=$ville) $txt.="<br>Ville : ".$user_info['ville']." => ".$ville;
				if($user_info['telephone']!=$telephone) $txt.="<br>T�l : ".$user_info['telephone']." => ".$telephone;
				if($user_info['portable']!=$portable) $txt.="<br>Portable : ".$user_info['portable']." => ".$portable;
				if($user_info['tvaintracom']!=$tvaintracom) $txt.="<br>N� TVA INTRACOM : ".$user_info['tvaintracom']." => ".$tvaintracom;
				if($txt != "") 
					$txt = "[infos_client] Modification infos client :".$txt;
				else
					$txt = "[infos_client] Modification infos client : Aucune modification";

				$DB_site->query("INSERT INTO suivi_client SET userid='".$user_info[userid]."', date=".time().", commercial='".$commercial."', suivi='".mysqli_real_escape_string($DB_site->link_id,$txt)."', etat=1");
		
                $DB_site->query("UPDATE utilisateur SET civilite = '$civilite', nom = '".mysqli_real_escape_string($DB_site->link_id,mb_strtoupper($nom))."', prenom = '".mysqli_real_escape_string($DB_site->link_id,$prenom)."', codepostal = '$codepostal',
                adresse = '".mysqli_real_escape_string($DB_site->link_id,$adresse)."', ville = '".mysqli_real_escape_string($DB_site->link_id,mb_strtoupper($ville))."', telephone = '".str_replace(array(" ",".","-"),"", $telephone)."', raisonsociale = '".mysqli_real_escape_string($DB_site->link_id,$raisonsociale)."', paysid = '".$_POST['paysid']."', tvaintracom='$tvaintracom',
                allowemail = '$newsletter', optionnel = '$option', portable = '".str_replace(array(" ",".","-"), "", $portable)."', nom_livraison = '".mysqli_real_escape_string($DB_site->link_id,mb_strtoupper($nom))."', prenom_livraison = '".mysqli_real_escape_string($DB_site->link_id,$prenom)."', codepostal_livraison = '$codepostal',
                adresse_livraison = '".mysqli_real_escape_string($DB_site->link_id,$adresse)."', ville_livraison = '".mysqli_real_escape_string($DB_site->link_id,mb_strtoupper($ville))."', raisonsociale_livraison = '".mysqli_real_escape_string($DB_site->link_id,$raisonsociale)."', paysid_livraison = '".mysqli_real_escape_string($DB_site->link_id,htmlspecialchars($_POST["paysid"]))."' WHERE userid = '$user_info[userid]'");
                $msg_affich = $template->translate("/client/modif_coord_succes");
				
                $template->parts["hideinfo1"]="";
                $action = isset($_POST['return'])?$_POST['return']:"";
            }else{
				$template->parts["errormessage"]=$errormessage;
                $action = "modify" ;
			}
            break ;
			
        case 'domodifypass': // modification du mot de passe client
            $errormessage="";
            if (!isset($ancienpass) or $ancienpass=="") { $action="modifypass"; $errormessage.=$template->translate("/client/erreur_indique_ancien_mdp","F")." <br> "; }
            if (!isset($nouveaupass) or $nouveaupass=="") { $action="modifypass"; $errormessage.=$template->translate("/client/erreur_indique_mdp","F")."<br> "; }
            if (!isset($nouveaupassconfirm) or $nouveaupassconfirm=="") { $action="modifypass"; $errormessage.=$template->translate("/client/erreur_confirm_mdp")." <br> "; }
            if ($passwordinscr!=$passwordinscrconfirm) 
                $errormessage.=$template->translate("/client/erreur_confirm_mdp")." <br> ";
                
            if (MD5($ancienpass)!=$user_info[password] and $ancienpass != "" ) 
                $errormessage.=$template->translate("/client/erreur_ancien_mdp")." <br> ";
			
			$template->parts["error"]=$errormessage;
                
            if ($errormessage == ""){
            	$nouveaupass = mysqli_real_escape_string($DB_site->link_id,$nouveaupass);
                $DB_site->query("UPDATE utilisateur SET password = MD5('$nouveaupass') WHERE userid = '$user_info[userid]'");
                $msg_affich = $template->translate("/client/modif_pass_succes");
                setcookie("password",MD5("$nouveaupass"),time()+3600000, "/");

				if(isset($_COOKIE['admin_username']) && $_COOKIE['admin_username']!="") $commercial=$_COOKIE['admin_username'];
				$txt="Modification mot de passe : ".$nouveaupass;
				$DB_site->query("INSERT INTO suivi_client SET userid='".$user_info[userid]."', date=".time().", commercial='".$commercial."', suivi='".mysqli_real_escape_string($DB_site->link_id,$txt)."', etat=1");
                $action="";
                $template->parts["hideinfo3"]="";
            }else{
                $action = "modifypass";
			}
            break ;
			
        case 'domodifymail': // modification du mail du client
            $errormessage="";
            if (!isset($mail) or $mail == "") { 
            	$action="modifymail"; 
            	$errormessage .= $template->translate("/client/nouvelle_email")."<br>" ;
            	$template->parts["errormessage"]=$errormessage;
            }

            $validemail=$DB_site->query_first("SELECT * FROM utilisateur WHERE mail = '$mail'");
            if ($validemail[userid] != "") {
                $action="modifymail"; $errormessage.=$template->translate("/client/email_deja_prise")."<br>";
                $template->parts["errormessage"]=$errormessage;
            }

            if (!preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $mail) and $mail!="") {
                $action="modifymail"; $errormessage.=$template->translate("/client/indiquer_mail_valide")."<br> ";
                $template->parts["errormessage"]=$errormessage;
            }


            if ($errormessage=="") {
				if(isset($_COOKIE['admin_username']) && $_COOKIE['admin_username']!="") $commercial=$_COOKIE['admin_username'];
				$txt="Modification email : ".$user_info['mail']." => ".$mail;
				$DB_site->query("INSERT INTO suivi_client SET userid='".$user_info['userid']."', date=".time().", commercial='".$commercial."', suivi='".mysqli_real_escape_string($DB_site->link_id,$txt)."', etat=1");

                $DB_site->query("UPDATE utilisateur SET mail = '$mail' WHERE userid = '$user_info[userid]'");
                $msg_affich =$template->translate("/client/modif_email_succes");
                $action = "" ;
                $template->parts["hideinfo2"]="";
            }else{
                $action = "modifymail" ;
            }
            break ;
			
		case 'domodifyabo': // modification des abonnements
			$conseils = $_POST["conseils"];
			$errormessage="";
			if (!isset($news_mail) or $news_mail == "") { $action = ""; $errormessage.= $template->translate("/client/erreur_mnews")."<br> "; }
			if (!isset($news_sms) or $news_sms == "") { $action = ""; $errormessage.= $template->translate("/client/erreur_snews")."<br> "; }
            if (isset($news_mail) and $news_mail != '0' and $news_mail != '1') { $action = ""; $errormessage.= $template->translate("/client/erreur_mnews")."<br> "; }
            if (isset($news_sms) and $news_sms != '0' and $news_sms != '1') { $action = ""; $errormessage.= $template->translate("/client/erreur_snews")."<br> "; }
            if (isset($conseils) and $conseils != '0' and $conseils != '1') { $action = ""; $errormessage.= $template->translate("/client/erreur_conseils")."<br> "; }
			if (isset($conseils)){if($conseils == '1'){$conseils = 'oui';}elseif ($conseils == '0'){$conseils = 'non';} }
            if ($errormessage=="") {
				if(isset($_COOKIE['admin_username']) && $_COOKIE['admin_username']!="") $commercial=$_COOKIE['admin_username'];
				$txt = "Modification abonnement :";
				if($user_info['allowemail'] != $news_mail) $txt.= "<br> Mail : ".($user_info['allowemail'] == 1 ? "Oui" : "Non")." => ".($news_mail == 1 ? "Oui" : "Non");
				if($user_info['allowsms'] != $news_sms) $txt.= "<br> Sms : ".($user_info['allowsms'] == 1 ? "Oui" : "Non")." => ".($news_sms == 1 ? "Oui" : "Non");
				if($user_info['conseils'] != $conseils) $txt."<br> Conseils Personnalises : ".$user_info['conseils']." => ".$conseils ;
				
				$DB_site->query("INSERT INTO suivi_client SET userid='".$user_info['userid']."', date=".time().", commercial='".$commercial."', suivi='".mysqli_real_escape_string($DB_site->link_id,$txt)."', etat=1");
				
				$DB_site->query("UPDATE utilisateur SET allowemail = '$news_mail', allowsms = '$news_sms', conseils = '$conseils' WHERE userid = '$user_info[userid]'");
				$msg_affich =$template->translate("/client/modif_abo_succes");
				$action = "" ;
                $template->parts["hideinfo4"]="";
            }else{
                $action = "modifyabo" ;
            }
			break;
    }
}
if(is_array($user_info)){
    switch($user_info[civilite]){
		case '0': 
		  $template->parts["selectmr"]="checked"; 
		  break;
		case '1': 
		  $template->parts["selectmme"]="checked"; 
		  break;
		case '2': 
		  $template->parts["selectmelle"]="checked";
		  break;
    }
	
	switch($user_info[allowemail]){
		case '0': 
			$template->parts["selectnewsmail0"]="checked"; 
			break;
		case '1': 
			$template->parts["selectnewsmail1"]="checked"; 
			break;
		default:
			$template->parts["selectnewsmail0"]="checked";
			break; 
    }
	
	switch($user_info[allowsms]){
		case '0': 
			$template->parts["selectnewssms0"]="checked"; 
			break;
		case '1': 
			$template->parts["selectnewssms1"]="checked"; 
			break;
		default:
			$template->parts["selectnewssms0"]="checked";
			break; 
    }

    switch ($user_info[conseils]) {
    	case 'oui':
    		$template->parts["selectconseils1"] = "checked";
    		break;
    	case 'non':
    		$template->parts["selectconseils0"] = "checked";
    		break;
    	default:
    		$template->parts["selectconseils0"]	= "checked";
    		break;
    }
    if($user_info[raisonsociale] != '') $template->parts['displayconseils'] = "style=\"display:none;\"";

    $template->parts["nom"]=$user_info['nom'];
    $template->parts["prenom"]=$user_info['prenom'];
    $template->parts["raisonsociale"]=$user_info['raisonsociale'];
    $template->parts["adresse"]=$user_info['adresse'];
    $template->parts["codepostal"]=$user_info['codepostal'];
    $template->parts["ville"]=$user_info['ville'];
    $template->parts["telephone"]=$user_info['telephone'];
    $template->parts["portable"]=$user_info['portable'];
    $template->parts["tvaintracom"]=$user_info['tvaintracom'];
}

$template->parts["nobg"]="clientbg";


if ((!isset($action) or $action == "") && !isset($_POST['action0'])){
    if (is_array($user_info) && $user_info[userid] != ""){ // connexion réussie dans global.php
        $civ=$civint[$user_info[civilite]];

		$commandes=$DB_site->query_first("SELECT COUNT(facturid) FROM facture WHERE userid=".$user_info['userid']." AND valide IN (1,5,7,8)");
		if($commandes[0]>0){
			$template->evaltemplate("ClientSAVlink","savlink");
			//$template->evaltemplate("ClientSatisfactionLink","satisfactionlink");
		}
		$template->evaltemplate("ClientCompte","content");
		$template->evaltemplate("ClientPage","main");
    }else{ // Pas encore connecté ou cas d'erreur dans la connexion ou dans la creation d'un compte (ex: mail deja inscrit)

		$template->parts["zonedeliste_pays"]="";
		$payss=$DB_site->query("SELECT * FROM pays INNER JOIN fraisport USING(paysid) WHERE sites='".$site_code."' GROUP BY paysid ORDER BY libelle ASC");
		while($pays=$DB_site->fetch_array($payss)){
			$template->parts["zonedeliste_pays"] .= "<option value=$pays[paysid] ".($pays[paysid]==57?"selected":"").">$pays[libelle]</option>";
		}
		
		if($_GET['email'] == "Votre e-mail" || $_GET['email'] == "Your e-mail"){
			$valeurmail="";
		}
		else{
			$valeurmail=$_GET['email'];
		}
		if(isset($_GET['mail_nl'])) $valeurmail = $_GET['mail_nl'];
		$template->parts["defaultmail"]=$valeurmail;
		// mise en place civilité renseigné
		if (isset($civilite) and $civilite!=""){
			if($civilite=='0') { $selectmr="selected"; }
			if($civilite=='1') { $selectmme="selected"; }
			if($civilite=='2') { $selectmelle="selected"; }
		}
		
		switch($civilite){
			case '0':
				$template->parts["selectmr"]="checked";
				break;
			case '1':
				$template->parts["selectmme"]="checked";
				break;
			case '2':
				 $template->parts["selectmelle"]="checked";
				 break;
		}
		
		switch($allowemail){
			case '0':
				$template->parts["selectnewsmail0"]="checked";
				break;
			case '1':
				$template->parts["selectnewsmail1"]="checked";
				break;
			default:
				$template->parts["selectnewsmail0"]="checked";
		}
		
		switch($allowsms){
			case '0':
				$template->parts["selectnewssms0"]="checked";
				break;
			case '1':
				$template->parts["selectnewssms1"]="checked";
				break;
			default:
				$template->parts["selectnewssms0"]="checked";
		}
		$template->parts["mail"]=(isset($_POST['mail'])?$_POST['mail']:"");
		$template->evaltemplate("Register","pagecommande");
		$template->evaltemplate("PanierPage","main");
    }
}else{
    switch ($action){
        case 'inscription':
            if (isset($civilite) and $civilite!=""){
                if($civilite=='0') { $selectmr="selected"; }
                if($civilite=='1') { $selectmme="selected"; }
                if($civilite=='2') { $selectmelle="selected"; }
            }
            $zonedeliste_pays = "";
            $payss=$DB_site->query("SELECT * FROM pays INNER JOIN fraisport USING(paysid) WHERE sites='".$site_code."' GROUP BY paysid ORDER BY libelle ASC");
            while($pays=$DB_site->fetch_array($payss)){
                if($pays[paysid]==57) 
                    $selected="selected";
                else 
                    $selected="";
                    
                $zonedeliste_pays .= "<option value=$pays[paysid] $selected>$pays[libelle]</option>";
            }
           
			if(isset($_GET['email'])){
				$template->parts["defaultmail"]=$_GET['email'];
				$template->parts["jsinscription"]="document.getElementById('register1').style.display='none';\ndocument.getElementById('register2').style.display='block';";
			}
            break ;
			
        case 'logging': // cas d'erreur � la connexion
            break ;
			
        case 'modify':
            $liste_pays = "";
            $payss=$DB_site->query("SELECT * FROM pays INNER JOIN fraisport USING(paysid) WHERE sites='".$site_code."' GROUP BY paysid ORDER BY libelle ASC");
            while($pays=$DB_site->fetch_array($payss)){
                if($pays[paysid]==$user_info[paysid]) 
                    $selected="selected";
                else 
                    $selected="";
                $template->parts["zonedeliste_pays"].= "<option value=$pays[paysid] $selected>$pays[libelle]</option>";
            }
	
	    if(isset($_GET['return'])) $template->parts["return_info"]="<input type=\"hidden\" name=\"return\" value=\"".$_GET['return']."\">";

	    $template->evaltemplate("ClientModify","content");
	    $template->parts["fil"]="<span typeof=\"v:Breadcrumb\"><a href=\"/\" rel=\"v:url\" property=\"v:title\">".$template->translate("/site/nom")."</a></span> &gt; <span typeof=\"v:Breadcrumb\"><a href=\"client.php\" rel=\"v:url\" property=\"v:title\">".$template->translate("/client/mon_compte")."</a></span> &gt; ".$template->translate("/client/modif_coordonnees");
	    $template->evaltemplate("ClientPage","main");
            break ;
			
        case 'facture':
            if (!isset($pagenumber) or $pagenumber=="" or $pagenumber==0) $pagenumber=1;
                
            $perpage=20;
            $debut=($pagenumber-1)*$perpage;
			$template->parts["lignes"]="";
			$f=new facture($DB_site);
			$factures=$f->search("userid = '$user_info[userid]' AND (valide IN (1,3,5,7,8) OR (moyenid != '0' AND valide=0)) order by facturid desc");
			foreach($factures as $facture){
				$expedition="";
				if($facture->dateexpedition!=0) $expedition=date("d/m/Y",$facture->dateexpedition);
				$moyenpaiement=$DB_site->query_first("SELECT libelle".$template->langue." AS libelle FROM moyenpaiement WHERE moyenid = ".$facture->moyenid);
				$etat=$DB_site->query_first("SELECT libelle".$template->langue." AS libelle FROM etatfacture WHERE valudeid = ".$facture->valide);
				$liendevis=$lienfacturepdf=$liensuivi="";
				
				if($facture->valide==5){
				  $lienfacturepdf = "<a href=\"http://".$_SERVER['HTTP_HOST']."/facture2.php?facturid=".$facture->facturid."&passe=".$facture->passefacture."\" target=\"_blank\"><i class=\"fa fa-file-pdf-o\"></i>".ucfirst($template->translate("/client/facture"))."</a>" ;
				  if(file_exists("admin/sfg_pdf/".$facture->facturid.".pdf")) $liensfgpdf="<a href=\"garantie".($facture->facturid*123+10).".pdf\"><i class=\"fa fa-file-pdf-o\"></i>".ucfirst($template->translate("/client/garantie"))."</a> - ";
                }
                
                if(in_array($facture->valide,array(1,5,7,8)))
                    $liensuivi="<a href=\"/suivi_commande.php?facturid=".$facture->facturid."&suiviid=".$facture->suiviId()."\"><i class=\"fa fa-truck\" aria-hidden=\"true\"></i> Suivi livraison</a>";
		
				$template->parts["lignes"].="<tr><td>".$facture->facturid."</td>
                        <td>".date("d/m/Y",$facture->datecommande)."</td>
                        <td>$expedition</td>
                        <td>$moyenpaiement[libelle]</td>
                        <td>$etat[libelle]</td>
                        <td class=\"price\">". number_format($facture->totalttc, 2, ',', ' ') ."&nbsp;&euro;</td>
                        <td>$lienfacturepdf $liensfgpdf $liensuivi</td></tr>";
                
                $template->parts["lignes_mobile"].="<tr>
					<td><b>".$facture->facturid."</b><br>".date("d/m/Y",$facture->datecommande)."</td>
					<td>$etat[libelle]</td>
					<td>$lienfacturepdf<br>$liensfgpdf</td></tr>";
			}

			$template->evaltemplate("ClientFactures","content");
			$template->parts["fil"]="<span typeof=\"v:Breadcrumb\"><a href=\"/\" rel=\"v:url\" property=\"v:title\">".$template->translate("/site/nom")."</a></span> &gt; <span typeof=\"v:Breadcrumb\"><a href=\"client.php\" rel=\"v:url\" property=\"v:title\">".$template->translate("/client/mon_compte")."</a></span> &gt; ".ucfirst($template->translate("/client/mes_commandes"));
			$template->evaltemplate("ClientPage","main");
            break ;
			
        case 'devis':
			if (!isset($pagenumber) or $pagenumber=="" or $pagenumber==0) $pagenumber=1;
                
			$perpage=20;
			$debut=($pagenumber-1)*$perpage;
			$template->parts["lignes"]="";
			$f=new facture($DB_site);
			$factures=$f->search("userid = '$user_info[userid]' AND valide=0 AND moyenid=0 AND (SELECT COUNT(*) FROM envoi_devis ed WHERE ed.facturid=facture.facturid)>0 order by facturid desc");
			
			foreach($factures as $facture){
				$liendevis="<a href=\"javascript:popdevis(".$facture->facturid.");\"><i class=\"fa fa-file-pdf-o\"></i>".ucfirst($template->translate("/client/devis"))."</a>";
				$template->parts["lignes"].="<tr><td>".$facture->facturid."</td>
					<td>".date("d/m/Y",$facture->datecommande)."</td>
					<td class=\"price\">". number_format($facture->totalttc, 2, ',', ' ') ."&nbsp;&euro;</td>
					<td>$liendevis</td></tr>";
							
				$template->parts["lignes_mobile"].="<tr>
					<td><b>".$facture->facturid."</b><br>".date("d/m/Y",$facture->datecommande)."</td>
					<td class=\"price\">". number_format($facture->totalttc, 2, ',', ' ') ."&nbsp;&euro;</td>
					<td>$liendevis</td></tr>";
			}

			$template->evaltemplate("ClientDevis","content");
			$template->parts["fil"]="<span typeof=\"v:Breadcrumb\"><a href=\"/\" rel=\"v:url\" property=\"v:title\">".$template->translate("/site/nom")."</a></span> &gt; <span typeof=\"v:Breadcrumb\"><a href=\"client.php\" rel=\"v:url\" property=\"v:title\">".$template->translate("/client/mon_compte")."</a></span> &gt; ".ucfirst($template->translate("/client/mes_commandes"));
			$template->evaltemplate("ClientPage","main");
			break;
			
		case 'modifypass':
            $template->evaltemplate("ClientModifPass","content");
			$template->parts["fil"]="<span typeof=\"v:Breadcrumb\"><a href=\"/\" rel=\"v:url\" property=\"v:title\">".$template->translate("/site/nom")."</a></span> &gt; <span typeof=\"v:Breadcrumb\"><a href=\"client.php\" rel=\"v:url\" property=\"v:title\">".$template->translate("/client/mon_compte")."</a></span> &gt; ".ucfirst($template->translate("/client/modif_pass"));
			$template->evaltemplate("ClientPage","main");
            break ;
			
        case 'modifymail':
			$template->parts["oldmail"]=$user_info['mail'];
			$template->evaltemplate("ClientModifMail","content");
			$template->parts["fil"]="<span typeof=\"v:Breadcrumb\"><a href=\"/\" rel=\"v:url\" property=\"v:title\">".$template->translate("/site/nom")."</a></span> &gt; <span typeof=\"v:Breadcrumb\"><a href=\"client.php\" rel=\"v:url\" property=\"v:title\">".$template->translate("/client/mon_compte")."</a></span> &gt; ".ucfirst($template->translate("/client/modif_email"));
			$template->evaltemplate("ClientPage","main");
            break ;
			
        case 'modifyabo':
			$template->evaltemplate("ClientModifAbo","content");
			$template->parts["fil"]="<span typeof=\"v:Breadcrumb\"><a href=\"/\" rel=\"v:url\" property=\"v:title\">".$template->translate("/site/nom")."</a></span> &gt; <span typeof=\"v:Breadcrumb\"><a href=\"client.php\" rel=\"v:url\" property=\"v:title\">".$template->translate("/client/mon_compte")."</a></span> &gt; ".ucfirst($template->translate("/client/modif_abo"));
			$template->evaltemplate("ClientPage","main");
			break;
			
		case "sav":
			include("sav.php");
			$template->parts["fil"]="<span typeof=\"v:Breadcrumb\"><a href=\"/\" rel=\"v:url\" property=\"v:title\">".$template->translate("/site/nom")."</a></span> &gt; <span typeof=\"v:Breadcrumb\"><a href=\"client.php\" rel=\"v:url\" property=\"v:title\">".$template->translate("/client/mon_compte")."</a></span> &gt; ".ucfirst($template->translate("/client/sav"));
			$template->parts["nobg"]="";
			$template->evaltemplate("ClientPage","main");
			break;

            
        case 'basket':
            $template->evaltemplate("ClientPanier","content");
            $template->evaltemplate("ClientPage","main");
        break;
    }
}
//barrecookie
$template->parts["stylePanier"]="style=\"bottom:0;\"";
$template->parts["remarketing"]=google_remarketing('',"siteview",'');
$template->out();
?>
