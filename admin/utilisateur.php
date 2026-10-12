<?php
require "admin_global.php";
include "./header.php";
include "./menu.php";

$DB_site=$DB_site2;
include_once("../class/ccstat.class.php");
include_once("../class/fonctions.php");
include("marketplace/article.php");
include("../class/stock.class.php");
include_once("../class/frais_port.php");
include "../class/DAOgarantie.class.php";
include "../class/categorie.class.php";
include "../class/configurateur.class.php";
include_once("../class/configurateur/complet.php");
include_once("../class/facture.class.php");
include_once("../class/email_client.php");
require_once("../class/fournisseur.class.php");
include_once("../class/projet.class.php");
require_once("../class/cocktel.class.php");
// include_once("../class/configurateur/artevino.php");
// include_once("../class/configurateur/climadiff.php");
// include_once("../class/configurateur/budget.php");

//unset($user_info);


//Mise en place liste de choix multiple de "Pays"
$sql = "SELECT paysid, libelle";
$sql .= " FROM pays";
$sql .= " ORDER BY libelle";
$result = $DB_site2->query($sql);
while($row = $DB_site2->fetch_array($result)){
     $tabPays[] = $row;
}

//Mise en place de la liste de choix multiple de "Connu par"
$sql = "SELECT valeur, description";
$sql .= " FROM formulaire_champ_valeur";
$sql .= " WHERE formulairechampid = 74";
$sql .= " ORDER BY valeur";
$result = $DB_site2->query($sql);
while($row = $DB_site2->fetch_array($result)) {
     $tabConnu[] = $row;
}

$fil_ariane = "<div id=\"fil_ariane\"><a title=\"Liste des utilisateurs\" href=\"utilisateur.php\">Utilisateurs</a>";
ini_set('display_errors', 1);
if(isset($_GET['userid'])){
     $sql = "SELECT prenom, nom";
     $sql .= " FROM utilisateur";
     $sql .= " WHERE userid = ".$_GET['userid'];
     $result = $DB_site2->query_first($sql);
/*     echo '<pre>';
    var_dump($_GET['userid']);
    var_dump($userid);
    var_dump($sql);
    var_dump($result);
    echo '</pre>';*/
     $fil_ariane .= " &gt; <a class=\"active\" title=\"Utilisateur ".$result['prenom']." ".$result['nom']."\" href=\"utilisateur.php?action=editer&userid=".$_GET['userid']."\">Fiche client : \"".$result['prenom']." ".$result['nom']."\"</a>";
}

//Récupération des types de suivi possible pour les afficher dans les différents select
$sql = "SELECT *";
$sql .= " FROM utilisateur_type_suivi";
$req = $DB_site2->query($sql);
while($row = $DB_site2->fetch_array($req)){
     $tabTypeSuivi[] = $row;
}

$fil_ariane .= "</div>";

echo "<h1>FICHES CLIENTS</h1>

<script language=\"JavaScript\" type=\"text/javascript\" src=\"../javascripts/host.js\"></script>
<script language=\"JavaScript\" type=\"text/javascript\" src=\"facture.js\"></script>
<script language=\"JavaScript\" type=\"text/javascript\" src=\"utilisateur.js\"></script>" ;

$causes=array(
	0=>"prix",
	1=>"délai",
	2=>"projet abandonné",
	3=>"trop relancé",
	4=>"mauvais rapports commerciaux",
	5=>"ne veux pas dire",
	6=>"autre",
	7=>"Client injoignable",
	8=>"Double compte",
	9=>"Petit dossier",
	10=>"achat ailleurs",
	11=>"Faux numéro/email",
	12=>"Petit dossier"
	);

// $causes=array(0=>"prix",1=>"délai",2=>"projet abandonné",3=>"trop relancé",4=>"mauvais rapports commerciaux",5=>"ne veux pas dire",6=>"autre",7=>"Injoignable",8=>"Double compte",9=>"Petit dossier, pas de suivi");

$marketplaces=array("01"=>"Amazon","04"=>"Price Minister","05"=>"Pixmania","06"=>"Windill","07"=>"Rue du Commerce","08"=>"Edengo","10"=>"Ebay","11"=>"2x moins cher","12"=>"Fnac","13"=>"CDiscount","14"=>"LaRedoute","15"=>"Galeries Lafayette","16"=>"Mister Good Deal");

if(isset($_POST['suivi'])){
    //$sp=split("/",$_POST['rappel']);
    $tsrappel=0;
    if($_POST['rappel']!=""){
      $sp=preg_split('/[- :\/h]/',$_POST['rappel']);
      $tsrappel=mktime($sp[3],$sp[4],0,$sp[1],$sp[0],$sp[2]);
    }
    $hide=intval(!isset($_POST['box']));
    if(!isset($_POST['etat'])) $_POST['etat']=1;
    if($_POST['projetid']==0){ // pas de projet
		$DB_site2->query("UPDATE utilisateur SET projet_etatid='".$_POST['etat']."', projet_rappel='".$tsrappel."' WHERE userid='".$_GET['userid']."' ");
	} else {
		if($_POST['projet_facturid']!="" && $_POST['etat']==5){ // en commande, on met donc le numéro de commande.
			$sql_update=", facturid='".$_POST['projet_facturid']."' ";
		} else {
			$sql_update="";
		}
		$DB_site2->query("UPDATE projet SET etatid='".$_POST['etat']."', rappel='".$tsrappel."', rappel_commercial='".$_POST['pour']."' ".$sql_update." WHERE projetid='".$_POST['projetid']."' ");
	}
	if($_POST['etat']==6){
		$_POST['etat']=0;
	}
    if($_POST['etat']==0){
		$DB_site2->query("INSERT INTO causeperte SET userid=".$_GET['userid'].",date=".time().", cause=".intval($_POST['cause']).", commentaire='".mysqli_real_escape_string($DB_site2->link_id,$_POST['commentaire'])."', lieu='".$_POST['lieuachat']."', article='".$_POST['article']."', marque='".$_POST['marque']."', prix=".floatval($_POST['prix']));
		$user=$DB_site2->query_first("SELECT * FROM utilisateur WHERE userid=".$_GET['userid']);
		$suivi=$DB_site2->query_first("SELECT * FROM suivi_client WHERE userid=".$_GET['userid']." ORDER BY date DESC LIMIT 1");
		$mail=new email();
		$mail->destinataire="benoit@mon-espace-cuisson.com";
	// 	$mail->destinataire="webmaster@mon-espace-cuisson.com";
		$mail->expediteur="contact@mon-espace-cuisson.com";
		$mail->expname="Mon Espace Cuisson";
		$mail->subject="client perdu";
		$mail->body="Le client <a href=\"https://$host/admin/utilisateur.php?action=editer&userid=".$user['userid']."\">".$user['prenom']." ".$user['nom']."</a> a été marque perdu par ".$user_info['username']." le ".date("d/m/Y à H:i")."
		<p>".$_POST['suivi']."</p>
		Cause de la perte : ".$causes[intval($_POST['cause'])]."
		<br>Lieu d'achat : ".$_POST['lieuachat']."
		<br>Article : ".$_POST['article']."
		<br>Marque : ".$_POST['marque']."
		<br>Prix : ".floatval($_POST['prix']);
		$mail->sendmail();
		$_POST['suivi']=$mail->body;
    }
    $time = time() + 1;
    $DB_site2->query("INSERT INTO suivi_client SET userid='$_GET[userid]', commercial='$user_info[username]', date=".$time.", suivi='".addslashes($_POST['suivi'])."', rappel=".$tsrappel.", jour=".intval($_POST['jour']).", rappeltype='".$_POST['rappeltype']."', etat=".$_POST['etat'].", pour='".$_POST['pour']."', hide=$hide, type='".$_POST['type']."', moyen='".$_POST['moyen']."', destinataire='".$_POST['destinataire']."', projetid='".$_POST['projetid']."', callid='".$_POST['callid']."'");
	
    $action="editer";
}

if(isset($_POST['action']) && $_POST['action']=='lier-projet-config') {
  $projet = new projet($_POST['liste_projets']);
  $projet->addconfig($_POST['configid']);
 echo "<script language=\"javascript\">location.href=\"utilisateur.php?action=editer&userid=$userid\";</script>";
}
if(isset($_POST['action']) && $_POST['action']=='delier-projet-config'){
    $projet = new projet($_POST['projetid_config']);
    $projet->rmconfig($_POST['configid']);
     echo "<script language=\"javascript\">location.href=\"utilisateur.php?action=editer&userid=$userid\";</script>";
}
if(isset($_POST['action']) && $_POST['action']=='lier-projet-artvu') {
  $projet = new projet($_POST['liste_projets']);
  $projet->addartvu($_POST['artvuid']);
 echo "<script language=\"javascript\">location.href=\"utilisateur.php?action=editer&userid=$userid\";</script>";
}
if(isset($_POST['action']) && $_POST['action']=='delier-projet-artvu'){
    $projet = new projet($_POST['projetid_artvu']);
    $projet->rmartvu($_POST['artvuid']);
     echo "<script language=\"javascript\">location.href=\"utilisateur.php?action=editer&userid=$userid\";</script>";
}
if(isset($_POST['action']) && $_POST['action']=='lier-projet-devis') {
  $projet = new projet($_POST['projetid']);
  $projet->adddevis($_POST['devisid']);
  echo "<script language=\"javascript\">location.href=\"utilisateur.php?action=editer&userid=$userid\";</script>";
}
if(isset($_POST['action']) && $_POST['action']=='delier-projet-devis'){
    $projet = new projet($_POST['projetid']);
    $projet->rmdevis($_POST['devisid']);
    echo "<script language=\"javascript\">location.href=\"utilisateur.php?action=editer&userid=$userid\";</script>";
}
if(isset($_POST['action']) && $_POST['action']=='lier-projet-cmd') {
  $projet = new projet($_POST['projetid']);
  $projet->etatid = 5;
  $projet->facturid = $_POST['factid'];
  $projet->save();
  echo "<script language=\"javascript\">location.href=\"utilisateur.php?action=editer&userid=$userid\";</script>";
}
if(isset($_POST['action']) && $_POST['action']=='delier-projet-cmd'){
    $projet = new projet($_POST['projetid']);
    $projet->etatid = 8;
    $projet->facturid = 0;
    $projet->save();
    echo "<script language=\"javascript\">location.href=\"utilisateur.php?action=editer&userid=$userid\";</script>";
}


if(isset($_POST['pourcentage_remise'])){
	$old = $DB_site->query_first("SELECT pourcentage_remise, conditions_reglement, encours_financier, garantie, utilisateur_livraison, francodeport FROM utilisateur WHERE userid =".intval($_GET['userid']));
    $sql="UPDATE utilisateur SET
    pourcentage_remise='".mysqli_real_escape_string($DB_site2->link_id,$_POST['pourcentage_remise'])."',
    conditions_reglement='".mysqli_real_escape_string($DB_site2->link_id,$_POST['conditions_reglement'])."',
    encours_financier=".floatval($_POST['encours_financier']).",
    garantie='".mysqli_real_escape_string($DB_site2->link_id,$_POST['garantie'])."',
    utilisateur_livraison='".mysqli_real_escape_string($DB_site2->link_id,$_POST['utilisateur_livraison'])."',
    francodeport = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['francodeport'])."'
    WHERE userid=".intval($_GET['userid']);
    $DB_site2->query($sql);
    $suivi ="";
    foreach ($old as $key => $value) {
    	if(isset($_POST[$key]) &&  $_POST[$key] != $value) $suivi .= ", ".$key .' : '.$value. " ==> " . $_POST[$key] ;
    }
    $suivi = substr($suivi, 1);

    $last = $DB_site2->query_first("SELECT etat FROM suivi_client WHERE userid='".$_GET['userid']."' ORDER BY id DESC LIMIT 1");

    $date = time() + 1;
    $DB_site->query("INSERT INTO suivi_client SET userid ='$_GET[userid]', commercial='$user_info[username]', date=".$date.", suivi ='".addslashes($suivi)."', etat='".$last[0]."'");
}

if( $action == 'cacher'){
    $DB_site2->query("update facture set supprimee = '1' WHERE facturid = '$facturid'");
    $action = "editer" ;
}
if($action=="commercial"){

    $DB_site2->query("UPDATE utilisateur SET commercial='$_GET[commercial]' WHERE userid='$_GET[userid]'");
    $last=$DB_site2->query_first("SELECT etat FROM suivi_client WHERE userid=".$_GET['userid']." ORDER BY date DESC LIMIT 1");
    if($last=="") $last['etat']=1;
    $DB_site2->query("INSERT INTO suivi_client SET commercial='$user_info[username]', date=".time().", userid=".$_GET['userid'].", suivi='[infos_client] Nouveau commercial attribué : ".$_GET[commercial]."', rappel=0, etat=".intval($last['etat']).",compte=0");
    $action="editer";
}
if($action=="vip"){
    $DB_site2->query("UPDATE utilisateur SET vip=".intval($vip)." WHERE userid=".$_GET['userid']);
    $txticon=array(-2=>"Client difficile",-1=>"Client blacklisté",0=>"Client normal",1=>"Client VIP");
    $last=$DB_site2->query_first("SELECT etat FROM suivi_client WHERE userid=".$_GET['userid']." ORDER BY date DESC LIMIT 1");
    if($last=="") $last['etat']=1;
    $DB_site2->query("INSERT INTO suivi_client SET commercial='$user_info[username]', date=".time().", userid=".$_GET['userid'].", suivi='[infos_client] ".$txticon[$vip]."', rappel=0, etat=".intval($last['etat']).", compte=0");
    $action="editer";
}

if($action=="typepro"){
    $DB_site2->query("UPDATE utilisateur SET typeclient_pro=".intval($typepro)." WHERE userid=".$_GET['userid']);
    $libellepro=$DB_site2->query_first("SELECT libelle FROM typeclient_pro WHERE id=".intval($typepro)."");
    $DB_site2->query("INSERT INTO suivi_client SET commercial='$user_info[username]', date=".time().", userid=".$_GET['userid'].", suivi='[infos_client] ".$libellepro['libelle']."', rappel=0, etat=".intval($last['etat']).", compte=0");
    $action="editer";
}

if($action == "typesuivi"){
     
     //Mise à jour du type de suivi du client
     $sql = "UPDATE utilisateur SET";
     $sql .= " utilisateur_type_suivi_id = ".intval($type_suivi);
     $sql .= " WHERE userid = ".$_GET['userid'];
     $DB_site2->query($sql);
     
     //Sélection du nom de type de suivi correspondant, pour insertion dans suivi client
     $sql = "SELECT utilisateur_type_suivi_nom";
     $sql .= " FROM utilisateur_type_suivi";
     $sql .= " WHERE utilisateur_type_suivi_id = ".intval($type_suivi);
     $aTabTypeSuivi = $DB_site2->query_first($sql);
     $type_suivi = isset($aTabTypeSuivi['utilisateur_type_suivi_nom']) ? $aTabTypeSuivi['utilisateur_type_suivi_nom'] : '';
     
     //Sélection de l'état précédent du client dans son suivi
     $sql = "SELECT etat";
     $sql .= " FROM suivi_client";
     $sql .= " WHERE userid = ".$_GET['userid'];
     $sql .= " ORDER BY date DESC";
     $sql .= " LIMIT 1";
     $last = $DB_site2->query_first($sql);
     if($last == "") $last['etat'] = 1; //Si aucun, on lui en met un par défaut
     
     //Ajout de l'info du changement de type de suivi, dans le suivi client
     $sql = "INSERT INTO suivi_client SET";
     $sql .= " commercial = '".$user_info['username']."'";
     $sql .= " , date     = ".time();
     $sql .= ", userid    = ".$_GET['userid'];
     $sql .= ", suivi     = '[infos_client] Type de suivi : ".$type_suivi."'";
     $sql .= ", rappel    = 0";
     $sql .= ", etat      = ".intval($last['etat']);
     $sql .= ",compte     = 0";
     $DB_site2->query($sql);    
    
     $action = "editer";
}

// Creation d'un nouveau client 
if($action=="register"){
    $action="new";
    $error="";
    $test=$DB_site2->query_first("SELECT userid FROM utilisateur WHERE mail='".$_POST['mail']."'");
    if($test[0]!="" && $_POST['mail']!="")
      $error="Un compte existe déjà avec cette adresse email <a href=\"utilisateur.php?action=editer&userid=$test[0]\">accéder à cette fiche client</a>";
      
    // Modification suite a action anormale, bloquage de cette adresse pour eviter un suivi des mails/info de ce compte  
    if($_POST['mail']=="pr@mon-espace-cuisson.com")
      $error="EMAIL NON AUTHORISE !";      
      

    if(trim($_POST['raisonsociale'])!=""){
		if(trim($_POST['tvaintracom'])!=""){
		    $tvaintracom= str_replace(array(' ', '.', '-', ',', ', '), '', $_POST['tvaintracom']);
		    //$result=checkvatnumber($tvaintracom); mis en commentaire le 28/08/2019 car le service est indisponible pour le moment
		    if($result->valid==false) $error="Le n° de TVA intracommunautaire n'est pas valide";
		}else{
		    $mailtva=true;
		}
    }

    if($error==""){
        $gclid ="";
        $utel=mysqli_real_escape_string($DB_site2->link_id,str_replace(array(" ",".","-"), "", $_POST['telephone']));
        // gestion gclid
        $g = $DB_site2->query_first("SELECT gclid FROM contact WHERE REPLACE(telephone,'.','') LIKE '".$utel."' OR mail LIKE '".$_POST['mail']."'");
        if($g != false) $gclid = $g[0];
    	$result=$DB_site2->query("INSERT INTO utilisateur SET dateinscription=NOW(), mail='".$_POST['mail']."', password='".md5($_POST['password'])."',
    	civilite='".intval($_POST['civilite'])."', nom='".mysqli_real_escape_string($DB_site2->link_id,$_POST['nom'])."', prenom='".mysqli_real_escape_string($DB_site2->link_id,$_POST['prenom'])."', raisonsociale='".mysqli_real_escape_string($DB_site2->link_id,$_POST['raisonsociale'])."',
    	adresse='".mysqli_real_escape_string($DB_site2->link_id,$_POST['adresse'])."', codepostal='".mysqli_real_escape_string($DB_site2->link_id,$_POST['codepostal'])."', ville='".mysqli_real_escape_string($DB_site2->link_id,$_POST['ville'])."', paysid=".intval($_POST['paysid']).",
    	langue='".$_POST['langue']."', telephone='".$utel."', portable='".mysqli_real_escape_string($DB_site2->link_id,str_replace(array(" ",".","-"),"",$_POST['portable']))."',
    	raisonsociale_livraison='".mysqli_real_escape_string($DB_site2->link_id,$_POST['raisonsociale'])."', nom_livraison='".mysqli_real_escape_string($DB_site2->link_id,$_POST['nom'])."', prenom_livraison='".mysqli_real_escape_string($DB_site2->link_id,$_POST['prenom'])."',
    	codepostal_livraison='".mysqli_real_escape_string($DB_site2->link_id,$_POST['codepostal'])."', adresse_livraison='".mysqli_real_escape_string($DB_site2->link_id,$_POST['adresse'])."', ville_livraison='".mysqli_real_escape_string($DB_site2->link_id,$_POST['ville'])."', paysid_livraison=".intval($_POST['paysid']).",
    	telephone_livraison='".mysqli_real_escape_string($DB_site2->link_id,str_replace(" ", "", $_POST['telephone']))."', allowemail='1', connu='".mysqli_real_escape_string($DB_site2->link_id,$_POST['connu'])."', besoin='".mysqli_real_escape_string($DB_site2->link_id,$_POST['besoin'])."', 
    	commercial='".$user_info['username']."', tvaintracom='".$_POST['tvaintracom']."', provenance='administrateur', conseils='".$_POST['conseils']."', gclid='".$gclid."'");
	
	if($result){
      // 	$action="editer";
	    $_GET['userid']=$userid=$DB_site2->insert_id();
	    $error="<script language=\"javascript\">document.location='utilisateur.php?action=editer&userid=$userid';</script>";

	    $suivi="Inscription par ".$user_info['username']." comme nouveau client
	    <br>Rappel des codes d'accès
	    <br>Login : ".$_POST['mail']."
	    <br>Pass : ".$_POST['password'];
	    $DB_site2->query("INSERT INTO suivi_client SET userid='".$userid."', date=".time().", commercial='".$user_info['username']."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivi)."', etat=1, compte=0");

        //Mail inscription 
        if($_POST['mailregister'] == 'oui'){ 
            $vars=array(
              "nom"=>$_POST['nom'],
              "prenom"=>$_POST['prenom'],
              "email"=>$_POST['mail'],
              "pass"=>$_POST['password']
            );
            $template->langue = $_POST['langue'] ;
            $emc=new email_client($DB_site2,$template,null,"inscription",$vars);

            $cmail=new email();
            $cmail->destinataire=$_POST['mail'];
            $cmail->destname=$_POST['prenom']." ".$_POST['nom'];
            $cmail->expediteur=$template->translate("/site/[site_code]/mail_expediteur");
            $cmail->expname=$template->translate("/site/nom");
            $cmail->copymode="bcc";
            $cmail->to="contact@mon-espace-cuisson.com";
            $cmail->subject=$emc->subject;
            $cmail->body=$emc->body;
            $cmail->sendmail();

            //$DB_site2->query("INSERT INTO suivi_client SET userid='".$userid."', date=".time().", commercial='".$user_info['username']."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$cmail->subject."<br>".$cmail->body)."', etat=1");
        }
	    
	    /******** mise en commentaire suite a la demande de philippe SI#3757 *************************************/
	    /*if($mailtva){
			$mail=new email();
			$mail->destinataire="contact@mon-espace-cuisson.com";
			$mail->to=array("cecile@mon-espace-cuisson.com");
			$mail->expediteur="contact@mon-espace-cuisson.com";
			$mail->expname="Ma Cave à Vin";
			$mail->subject="Manque info tva intra";
			$mail->body="Attention le client n°".$userid.", société ".$_POST['raisonsociale'].", ".$_POST['prenom']." ".$_POST['nom']." créé depuis l'admin n'a pas de numéro de TVA";
			$mail->sendmail();
			$DB_site->query("INSERT INTO suivi_client SET userid='".$userid."', date=".time().", commercial='".$user_info['username']."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$mail->subject."<br>".$mail->body)."', etat=1");
	    }*/
	}else
	    $error="Une erreur est survenue";
    }
}

if($action=="nf"){
    $_POST['encours']="nf";
    $_POST['src']="";
    unset($action);
}

$optcom="<option value=\"0\">Tous</option>";
$req=$DB_site2->query("SELECT commercial FROM utilisateur u where commercial <> '' group by commercial ORDER BY commercial");
while($commercial=$DB_site2->fetch_array($req)) {
    $optcom.="<option value=\"$commercial[0]\">$commercial[0]</option>";
}


$optpro= "<option value=\"\">Tous</option>";
$optpro.= "<option value=\"tri\">A trier</option>";
$reqpro= $DB_site2->query("SELECT * FROM typeclient_pro WHERE 1 ORDER BY position");
while($clientpro = $DB_site2->fetch_array($reqpro)){
    $optpro.="<option value=\"".$clientpro['id']."\" ".(empty($_GET['typeclientpro']) === false &&$_GET['typeclientpro']===$clientpro[0] ?" selected":"").">".$clientpro['libelle']."</option>";
}


if (in_array("106", $modules)) // groupes d'utilisateurs
	{
	if (!isset($action) || $action == "")
		{
		echo "<h3>Gestion des utilisateurs et des groupes</h3>
		<br>
		<table width=\"100%\" align=\"center\">
			<tr>
				<td width=\"100\" align=\"left\" valign=\"top\"><br><strong>Groupes</strong><br><br></td>
				<td><div id=\"groupes\">" ;
					echo "<br>Groupes d'utilisateurs existants sur ma boutique<bR><br>
					<table class=\"table_modules_boutiques\">" ;
					$gps=$DB_site2->query("SELECT * FROM groupe_utilisateur order by nom" );
					echo "<tr>
							<td align=\"center\"><strong>Nom</strong></td>
							<td align=\"center\"><strong>Utilisateurs</strong></td>
							<td align=\"center\">&nbsp;</td>
						</tr>" ;
					while ($gp=$DB_site2->fetch_array($gps))
						{
						$rowalt = "td_promos_".getrowbg();
						$nb=$DB_site2->query_first("SELECT count(*) FROM utilisateur where groupeid = '$gp[groupeid]'" );
						if ($gp[pctremise] == 0)
							{
							$gp[pctremise] = "Aucune" ;
							}
						else
							{
							$gp[pctremise] = "$gp[pctremise] %" ;
							}
						echo "<tr>
								<td align=\"left\" class=\"$rowalt\">$gp[nom]</td>
								<td align=\"center\" class=\"$rowalt\">$nb[0]</td>
								<td align=\"center\" class=\"$rowalt\"><a href=\"utilisateur.php?action=detailgroupe&groupeid=$gp[groupeid]\">Détail</a></td>
							</tr>" ;
						}
					echo "</table>" ;
				echo "</div></td>
			</tr>
			<tr>
				<td width=\"100\" align=\"left\" valign=\"top\"><br><strong>Utilisateurs</strong><br><br></td>
				<td><div id=\"utilisateurs\">" ;
					if (!isset($pagenumber) or $pagenumber=="") 
						{
						$pagenumber="1";
						}
					$perpage="100";
					$limitlower=($pagenumber-1)*$perpage;
					$limitupper=($pagenumber)*$perpage;
					$utilisateurcount = $DB_site2->query_first( "SELECT count(*) FROM utilisateur" );
					$pagenav=getpagenav($utilisateurcount[0],"utilisateur.php?");
					echo $pagenav ;
					echo "<br>Liste des utilisateurs inscrits sur ma boutique (tous groupes confondus)<br><br>
					<table class=\"table_modules_boutiques\">";
					$rq_client = $DB_site2->query("SELECT * FROM utilisateur as u INNER JOIN pays as p ON (u.paysid=p.paysid) ORDER BY dateinscription DESC LIMIT $limitlower , $perpage " );
					if ($DB_site2->num_rows($rq_client) > 0)
						{
						echo "<tr>
							   <td align=\"center\"><b>Inscription</b></td>
							   <td align=\"center\"><b>N°</b></td>
							   <td align=\"center\"><b>Mail</b></td>
							   <td align=\"center\"><b>Nom</b></td>
							   <td align=\"center\"><b></b></td>			   
							  </tr>";
						while ($rs_client=$DB_site2->fetch_array($rq_client))
							{
							$rowalt = "td_promos_".getrowbg();
							list($annee, $mois, $jour) = explode( "-", $rs_client['dateinscription'] );
							echo "<tr class=\"td_promos\">";
							echo "  <td class=\"$rowalt\">$jour/$mois/$annee</td>";
							echo "  <td class=\"$rowalt\" align=\"left\">$rs_client[userid]</td>";
							echo "  <td class=\"$rowalt\" align=\"left\">$rs_client[mail]</td>";
							echo "  <td class=\"$rowalt\" align=\"left\">$rs_client[nom] $rs_client[prenom]</td>";
							echo "  <td class=\"$rowalt\" align=\"center\"><a href=\"utilisateur.php?action=editer&userid=$rs_client[userid]\">Détail</a></td>";
							echo "</tr>";
							}
						}
					echo "</table>";
					echo $pagenav ;
				echo "</div></td>
			</tr>
			<tr>
				<td width=\"100\" align=\"left\" valign=\"top\"><br><strong>Option</strong><br><br></td>
				<td><div id=\"option\">" ;


        echo "</div></td>
			</tr>
		</table>" ;
		}
	elseif( $action == "champopt" )
		{
		
		echo "<script language=\"javascript\">location.href=\"utilisateur.php\";</script>";
		}
	elseif( $action=="editer" )
		{
		$utilisateur=$DB_site2->query_first("SELECT * FROM utilisateur where userid = '$_GET[userid]'");
		$a = substr ($utilisateur['dateinscription'], 0, 4) ;
		$m = substr ($utilisateur['dateinscription'], 5, 2) ;
		$j = substr ($utilisateur['dateinscription'], 8, 2) ;
		$utilisateur['dateinscription'] = "$j/$m/$a" ;
		$anneemois = date ('Y-m') ;
		
		echo "<h3>Client $utilisateur[nom] $utilisateur[prenom]</h3>
		Date d'inscription : $utilisateur[dateinscription].<br><br>" ;
		switch ($utilisateur['allowemail'])
			{
			case '0':
				$utilisateur['allowemail'] = "Ne reçoit pas" ;
				break ;
			case '1':
				$utilisateur['allowemail'] = "Reçoit" ;
				break ;
			}
		switch ($utilisateur['allowsms'])
			{
				case '0':
					$utilisateur['allowsms'] = "Ne reçoit pas" ;
					break ;
				case '1':
					$utilisateur['allowsms'] = "Reçoit" ;
					break ;
			}
		switch ($utilisateur[civilite])
			{
			case '0':
				$utilisateur['civilite'] = "Monsieur" ;
				break ;
			case '1':
				$utilisateur['civilite'] = "Madame" ;
				break ;
			case '2':
				$utilisateur['civilite'] = "Mademoiselle" ;
				break ;
			}

		if ($utilisateur['portable'] == "")
			{
			$utilisateur['portable'] = "inconnu" ;
			}

            var_dump('<pre>');
            var_dump($utilisateur);
            var_dump('</pre>');
            $paysid = empty($utilisateur['paysid']) === false ? $utilisateur['paysid'] : 57;
            $pays_lib=$DB_site2->query_first("select libelle from pays where paysid = $paysid");
	
		$gp=$DB_site2->query_first("select nom from groupe_utilisateur where groupeid = '$utilisateur[groupeid]'");
		echo "<br>
		<table width=\"100%\">
			<tr><td align=\"right\">N° client : </td><td>$utilisateur[userid]</td></tr>
			<tr><td align=\"right\">Civilité : </td><td>$utilisateur[civilite]</td></tr>
			<tr><td align=\"right\">Nom Prénom : </td><td>$utilisateur[nom] $utilisateur[prenom]</td></tr>
			<tr><td align=\"right\">Email : </td><td>$utilisateur[mail] : $utilisateur[allowemail] la newsletter </td></tr>
			<tr><td align=\"right\">Raison sociale : </td><td>$utilisateur[raisonsociale]</td></tr>
			<tr><td align=\"right\">Adresse : </td><td>$utilisateur[adresse]</td></tr>
			<tr><td align=\"right\">Code postal / Ville : </td><td>$utilisateur[codepostal] $utilisateur[ville]</td></tr>
			<tr><td align=\"right\">Téléphone / Portable : </td><td>$utilisateur[telephone] / $utilisateur[portable]</td></tr>
			<tr><td align=\"right\">Pays / Langue : </td><td>$pays_lib[0] : $utilisateur[langue]</td></tr>
			<tr><td align=\"right\">Connu le site par : </td><td>$utilisateur[connu]</td></tr>
			<tr><td align=\"right\">Groupe : </td><td>$gp[nom] <a href=\"\">Changer</a></td></tr>
		</table>
		<br><Br>" ;
		echo "<br>
		<table class=\"table_moyen_paiement\">
			<tr><td align=\"center\"><strong>Statistique</strong></td><tD align=\"center\"><strong>Au total</strong></td><td align=\"center\"><strong>Ce mois</strong></td></tr>
			<tr><td>Nombre de connexions</td><tD align=\"center\">$statuser_tot[visites]</td><td align=\"center\">$statuser_mois[visites]</td></tr>
			<tr><td>Nombre de pages vues</td><tD align=\"center\">$statuser_tot[pagesvues]</td><td align=\"center\">$statuser_mois[pagesvues]</td></tr>
			<tr><td colspan=\"3\" align=\"right\"><a href=\"utilisateur.php?action=pages&userid=$_GET[userid]\" alt=\"+ de détails sur les pages\" title=\"+ de détails sur les pages\">+ de détails sur les pages</a> </td></tr>
			<tr><td>Nombre d'articles visualisés</td><tD align=\"center\">$nb_arts_tot</td><td align=\"center\">$nb_arts_tot</td></tr>
			<tr><td>Nombre d'articles ajoutés au panier</td><tD align=\"center\">$nb_pan_tot</td><td align=\"center\">$nb_pan_mois</td></tr>
			<tr><td>Nombre d'articles commandés</td><tD align=\"center\">$nb_cde_tot</td><td align=\"center\">$nb_cde_mois</td></tr>
			<tr><td colspan=\"3\" align=\"right\"><a href=\"utilisateur.php?action=articles&userid=$_GET[userid]\" alt=\"+ de détails sur les articles\" title=\"+ de détails sur les articles\">+ de détails sur les articles</a> </td></tr>
			<tr><td>Nombre de commandes passées</td><tD align=\"center\">$nb_cdespassees_tot[0]</td><td align=\"center\">$nb_cdespassees_mois[0]</td></tr>
			<tr><td>Nombre de commandes abandonnées</td><tD align=\"center\">$nb_cdessanssuite_tot[0]</td><td align=\"center\">$nb_cdessanssuite_mois[0]</td></tr>
			<tr><td>Nombre de commandes expediées</td><tD align=\"center\">$nb_cdesexpediees_tot[0]</td><td align=\"center\">$nb_cdesexpediees_mois[0]</td></tr>
			<tr><td align=\"right\">Dont par Carte Bleue</td><tD align=\"center\">$nb_cdesexpediees1_tot[0]</td><td align=\"center\">$nb_cdesexpediees1_mois[0]</td></tr>
			<tr><td align=\"right\">Dont par Chèque</td><tD align=\"center\">$nb_cdesexpediees2_tot[0]</td><td align=\"center\">$nb_cdesexpediees2_mois[0]</td></tr>
			<tr><td align=\"right\">Dont par Contre remboursement</td><tD align=\"center\">$nb_cdesexpediees3_tot[0]</td><td align=\"center\">$nb_cdesexpediees3_mois[0]</td></tr>
			<tr><td align=\"right\">Dont par Mandat postal</td><tD align=\"center\">$nb_cdesexpediees4_tot[0]</td><td align=\"center\">$nb_cdesexpediees4_mois[0]</td></tr>
			<tr><td align=\"right\">Dont par Virement</td><tD align=\"center\">$nb_cdesexpediees5_tot[0]</td><td align=\"center\">$nb_cdesexpediees5_mois[0]</td></tr>
			<tr><td align=\"right\">Dont par PayPal</td><tD align=\"center\">$nb_cdesexpediees6_tot[0]</td><td align=\"center\">$nb_cdesexpediees6_mois[0]</td></tr>
		</table>
		<br><Br><a href=\"utilisateur.php\">Retour</a>" ;
		}
	elseif( $action == "pages" )
		{
		$utilisateur=$DB_site2->query_first("SELECT * FROM utilisateur where userid = '$_GET[userid]'");
		echo "<h3>Client $utilisateur[nom] $utilisateur[prenom]</h3>
		<strong>+ de détails sur les pages</strong><br><br>
		<table class=\"table_moyen_paiement\">
			<tr>
				<td align=\"center\"><strong>Nom de la page</strong></td>
				<tD align=\"center\" colspan=\"2\"><strong>Nombre de vues</strong></td>
			</tr>
			<tr>
				<td align=\"center\"><strong></strong></td>
				<tD align=\"center\"><strong>Au total</strong></td>
				<tD align=\"center\"><strong>Ce mois</strong></td>
			</tr>" ;
		$user_pages_tot=$DB_site2->query("SELECT script, sum(visites) as visites FROM statistique_page_utilisateur 
											  where userid = '$_GET[userid]' group by script order by script asc");
		while ($user_page_tot=$DB_site2->fetch_array($user_pages_tot))
			{
			$anneemois = date ('Y-m') ;
			$user_page_mois=$DB_site2->query_first("SELECT sum(visites) as visites FROM statistique_page_utilisateur 
							where userid = '$_GET[userid]' and script = '$user_page_tot[script]' and datestatistique LIKE '%$anneemois-%'");
			echo "
				<tr>
					<td align=\"left\">&nbsp;&nbsp;&nbsp;$user_page_tot[script]</td>
					<tD align=\"center\">$user_page_tot[visites]</td>
					<td align=\"center\">$user_page_mois[visites]</td>
				</tr>" ;
			}
		echo "<tr>
				<td></td>
				<tD align=\"center\"></td>
				<td align=\"center\"></td>
			</tr>
		</table>
		<br><Br><a href=\"utilisateur.php?action=editer&userid=$_GET[userid]\">Retour</a>" ;
		}
	elseif( $action == "articles" )
		{
		$utilisateur=$DB_site2->query_first("SELECT * FROM utilisateur where userid = '$_GET[userid]'");
		echo "<h3>Client $utilisateur[nom] $utilisateur[prenom]</h3>
		<strong>+ de détails sur les articles</strong><br><br>
		<table class=\"table_coupscoeur\">
			<tr>
				<td align=\"center\"><strong>Article</strong></td>
				<tD align=\"center\" colspan=\"3\"><strong>Au total</strong></td>
				<tD align=\"center\">&nbsp;</td>
				<tD align=\"center\" colspan=\"3\"><strong>Ce mois</strong></td>
			</tr>
			<tr>
				<td align=\"center\"><strong></strong></td>
				<tD align=\"center\"><strong>Vues</strong></td>
				<tD align=\"center\"><strong>Panier</strong></td>
				<tD align=\"center\"><strong>Cdes</strong></td>
				<tD align=\"center\">&nbsp;</td>
				<tD align=\"center\"><strong>Vues</strong></td>
				<tD align=\"center\"><strong>Panier</strong></td>
				<tD align=\"center\"><strong>Cdes</strong></td>
			</tr>
			" ;
		$user_arts_tot=$DB_site2->query("SELECT libelleFR, spu.artid, sum(visites) as visites, sum(panier) as panier, sum(commandes) as commandes 
		FROM statistique_produit_utilisateur spu, article a where a.artid = spu.artid and userid = '$_GET[userid]' group by spu.artid order by spu.artid asc");
		while ($user_art_tot=$DB_site2->fetch_array($user_arts_tot))
			{
			$anneemois = date ('Y-m') ;
			$user_arts_mois=$DB_site2->query_first("SELECT sum(visites) as visites, sum(panier) as panier, sum(commandes) as commandes 
			FROM statistique_produit_utilisateur where userid = '$_GET[userid]' and artid = '$user_art_tot[artid]' and datestatistique LIKE '%$anneemois-%'");
			echo "
				<tr>
					<td align=\"left\" width=\"25%\">$user_art_tot[libelleFR]</td>
					<tD align=\"center\" class=\"td_promos_alt1\">$user_art_tot[visites]</td>
					<tD align=\"center\" class=\"td_promos_alt1\">$user_art_tot[panier]</td>
					<tD align=\"center\" class=\"td_promos_alt1\">$user_art_tot[commandes]</td>
					<tD align=\"center\" bgcolor=\"#5F718A\" width=\"1\"></td>
					<tD align=\"center\" class=\"td_promos_alt2\">$user_arts_mois[visites]</td>
					<tD align=\"center\" class=\"td_promos_alt2\">$user_arts_mois[panier]</td>
					<tD align=\"center\" class=\"td_promos_alt2\">$user_arts_mois[commandes]</td>
				</tr>" ;
			}
		echo "<tr>
				<td></td>
				<tD align=\"center\"></td>
				<td align=\"center\"></td>
			</tr>
		</table>
		<br><Br><a href=\"utilisateur.php?action=editer&userid=$_GET[userid]\">Retour</a>" ;
		}
	elseif( $action == "detailgroupe" )
		{
		$groupe=$DB_site2->query_first("SELECT * FROM groupe_utilisateur where groupeid = '$groupeid'");
		echo "$groupe[nom] (-$groupe[pctremise] %)<br><br><ul>" ;
		$users=$DB_site2->query("SELECT * FROM utilisateur where groupeid = '$groupe[groupeid]' order by nom");
		while ($u=$DB_site2->fetch_array($users))
			{
			echo "<li>$u[nom] $u[prenom]</li>" ;
			}
		echo "</ul>
		<br><Br><a href=\"utilisateur.php\">Retour</a>" ;
		}
//Liste & fiches utilisateur
} else {
     //Liste utilisateurs
    var_dump($action);
     if (empty($action) ){
         echo 'fffffffffffffffffffffffffff';
          
          //Nb résultat par page
          $perpage = 50;
          //$pagedays=10;
          
          //Gestion pagination
          if (!isset($pagenumber) or $pagenumber==""){
               $pagenumber = "1";
          }
          
          $limitlower = ($pagenumber - 1) * $perpage; //Limite basse pour la requête de sélection
          $limitupper = $perpage; //Limite haute pour la requête de sélection
          
          //Date actuel format timestamp
          $today = time();
          $tomorrow = mktime(0,0,0,date("m"),date("d"),date("Y"))+86400;
          
          //Filtre recherche sur "nom/société/email"
          $srctxt=trim($_GET['src']);
          $srcnom=trim($_GET['nom']);
          $srcprenom=trim($_GET['prenom']);
          $srcphone=array();
          $phonenum=trim(preg_replace("/[^\d\+]/","",$src));
          $phonenum=preg_replace("/^\+33|0033/","0",$phonenum);
          if(strlen($phonenum)>8){
                $srcphone[]=$phonenum;
                if(preg_match("/^0\d{9}$/",$phonenum)){
                        $srcphone[]="0033".substr($phonenum,1);
                        $srcphone[]="+33".substr($phonenum,1);
                }
            }
          if(isset($_GET['src']) && $srctxt!=""){
               $src = " AND (";
               $src .= " u.raisonsociale LIKE '%".mysqli_real_escape_string($DB_site2->link_id,$srctxt)."%'";
               $src .= " OR u.userid='".mysqli_real_escape_string($DB_site2->link_id,$srctxt)."'";
               $src .= " OR u.mail LIKE '".mysqli_real_escape_string($DB_site2->link_id,$srctxt)."'";
               if(count($srcphone)>0){
                $src .= " OR u.telephone IN ('".implode("','",$srcphone)."')";
                $src .= " OR u.portable IN ('".implode("','",$srcphone)."')";
                }
               //Utilisateur Associé
                $src .= " OR ua.userid='".mysqli_real_escape_string($DB_site2->link_id,$srctxt)."'";
                $src .= " OR ua.utilisateur_assoc_email LIKE '".mysqli_real_escape_string($DB_site2->link_id,$srctxt)."'";
                if(count($srcphone)>0){
                    $src .= " OR ua.utilisateur_assoc_tel IN ('".implode("','",$srcphone)."')";
                    $src .= " OR ua.utilisateur_assoc_portable IN ('".implode("','",$srcphone)."')";
                }
               $src .= ")";
          }

          if(isset($_GET['nom'])&& $srcnom!=""){
                $src .= " AND (";
                $src .= " u.nom LIKE '%".mysqli_real_escape_string($DB_site2->link_id,$srcnom)."%'"; 
                //Utilisateur associé
                $src .= " OR ua.utilisateur_assoc_nom LIKE '%".mysqli_real_escape_string($DB_site2->link_id,$srcnom)."%'";
                $src .= ")";
          }
          if(isset($_GET['prenom'])&& $srcprenom!=""){
                $src .= " AND (";
                $src .= " u.prenom LIKE '%".mysqli_real_escape_string($DB_site2->link_id,$srcprenom)."%'"; 
                //Utilisateur associé
                $src .= " OR ua.utilisateur_assoc_prenom LIKE '%".mysqli_real_escape_string($DB_site2->link_id,$srcprenom)."%'";
                $src .= ")";
          }
          
          //Filtre recherche sur "département / code postal"
          if(isset($_GET['dpt']) && $_GET['dpt']!=""){
               if(strlen($_GET['dpt']) > 3){ //Code postal
                    $src .= " AND codepostal = '".trim($_GET['dpt'])."'";
               } else { //Département
                    $src .= " AND codepostal LIKE '".trim($_GET['dpt'])."%'";
               }
            }
            
            if(isset($_GET['paysid']) && $_GET['paysid']!=""){
                $src.=" AND paysid=".intval($_GET['paysid']);
            }
          
          //Filtre recherche "Commercial"
          if(isset($_GET['commercial']) && $_GET['commercial']!="0"){
               $src .= " AND u.commercial='".$_GET['commercial']."'";
          }
          
          //Filtre recherche "Statut client"
          if(isset($_GET['encours']) && !empty($_GET['encours'])){
               switch($_GET['encours']){
                    case 11: //Statut "A suivre"
                         $src .= " AND (";
                         $src .= "      SELECT etat";
                         $src .= "      FROM suivi_client FORCE INDEX (userid)";
                         $src .= "      WHERE userid = u.userid";
                         $src .= "      ORDER BY date DESC LIMIT 1";
                         $src .= ") = 1 AND (";
                         $src .= "      SELECT valide";
                         $src .= "      FROM facture FORCE INDEX (userid)";
                         $src .= "      WHERE userid = u.userid";
                         $src .= "      AND supprimee = '0'";
                         $src .= "      ORDER BY heurefacture DESC";
                         $src .= "      LIMIT 1";
                         $src .= ") = 0";
                         break;
					case 12: // Statut Attente info client
                         $src .= " AND (";
                         $src .= "      SELECT etat";
                         $src .= "      FROM suivi_client FORCE INDEX (userid)";
                         $src .= "      WHERE userid = u.userid";
                         $src .= "      ORDER BY date DESC LIMIT 1";
                         $src .= ") = 2 AND (";
                         $src .= "      SELECT valide";
                         $src .= "      FROM facture FORCE INDEX (userid)";
                         $src .= "      WHERE userid = u.userid";
                         $src .= "      AND supprimee = '0'";
                         $src .= "      ORDER BY heurefacture DESC";
                         $src .= "      LIMIT 1";
                         $src .= ") = 0";
                         break;
					case 13: // Statut A chiffrer
                         $src .= " AND (";
                         $src .= "      SELECT etat";
                         $src .= "      FROM suivi_client FORCE INDEX (userid)";
                         $src .= "      WHERE userid = u.userid";
                         $src .= "      ORDER BY date DESC LIMIT 1";
                         $src .= ") = 3 AND (";
                         $src .= "      SELECT valide";
                         $src .= "      FROM facture FORCE INDEX (userid)";
                         $src .= "      WHERE userid = u.userid";
                         $src .= "      AND supprimee = '0'";
                         $src .= "      ORDER BY heurefacture DESC";
                         $src .= "      LIMIT 1";
                         $src .= ") = 0";
                         break;
                    //Statut "en commande"
                    case 2:
                         $src .= " AND (";
                         $src .= "      SELECT valide";
                         $src .= "      FROM facture FORCE INDEX (userid)";
                         $src .= "      WHERE userid = u.userid";
                         $src .= "      AND supprimee = '0'";
                         $src .= "      ORDER BY heurefacture DESC";
                         $src .= "      LIMIT 1";
                         $src .= ") IN (1,5,7,8)";
                         break;
                    //Statut "suivi automatique
                    case 3:
                    break;
                    //Statut "dossier sous-traité"
                    case 4:
                    $src .= " AND (";
                    $src .= "      SELECT etat";
                    $src .= "      FROM suivi_client FORCE INDEX (userid)";
                    $src .= "      WHERE userid = u.userid";
                    $src .= "      ORDER BY date DESC";
                    $src .= "      LIMIT 1";
                    $src .= ") = 4";
                    break;
                    //Statut "perdu"
                    case "0":
                    $src.=" AND (";
                    $src .= "      SELECT etat";
                    $src .= "      FROM suivi_client FORCE INDEX (userid)";
                    $src .= "      WHERE userid = u.userid";
                    $src .= "      ORDER BY date DESC";
                    $src .= "      LIMIT 1";
                    $src .= ") = 0";
                    break;
                    //statut "non fidélisé"
                    case "nf":
                    $src.=" AND (";
                    $src .= "      SELECT COUNT(*)";
                    $src .= "      FROM fidelisation FORCE INDEX (userid)";
                    $src .= "      WHERE userid = u.userid";
                    $src .= ") = 0";
                    $src .= " AND (";
                    $src .= "      SELECT valide";
                    $src .= "      FROM facture FORCE INDEX (userid)";
                    $src .= "      WHERE userid = u.userid";
                    $src .= "      AND supprimee = '0'";
                    $src .= "      ORDER BY heurefacture DESC";
                    $src .= "      LIMIT 1";
                    $src .= ") IN (5,8)";
                    break;
                    //statut "en fidélisation"
                    case "fid":
                    $src .=" AND (";
                    $src .= "      SELECT COUNT(*)";
                    $src .= "      FROM fidelisation FORCE INDEX (userid)";
                    $src .= "      WHERE userid = u.userid";
                    $src .= "      AND (";
                    $src .= "           date > UNIX_TIMESTAMP(NOW())";
                    $src .= "           OR periode > 0";
                    $src .= "      )";
                    $src .= ") > 0";
                    break;
               }
          }
          
          //Filtre recherche "Type de client"
          if(isset($_GET['type']) && !empty($_GET['type'])){
               switch($_GET['type']){
                    case 'part':
                         $src .= " AND raisonsociale = ''";
                         break;
                    case 'pro':
                         $src .=" AND raisonsociale <> ''";
                         break;
               }
          }
          
          //Filtre recherche "Profil client"
          if(isset($_GET['vip']) && $_GET['vip']!=""){
               $src .= " AND vip = ".$_GET['vip'];
          }

          //Filtre recherche "Code 401"
          if(isset($_GET['code401']) && $_GET['code401']!=""){
               $src .= " AND u.code411 ".($_GET['code401'] == 1 ? '!=' : '=')." ''";
          }

          //Filtre recherche "Provenance"
          if(isset($_GET['prov']) && $_GET['prov'] !=""){
          	   $src .= " AND u.provenance ".($_GET['prov'] == 1 ? '!=' : '=')." 'mobile'";
          }

          //Filtre recherche "Type de suivi"
          if(isset($_GET['suivi']) && $_GET['suivi'] !=""){
          	   $src .= " AND u.utilisateur_type_suivi_id = ".$_GET['suivi'];
          }
          
          //Filtre recherche "Inscrit à la newsletter"
          if(isset($_GET['newsletter']) && $_GET['newsletter'] !="")
          {
          		if ($_GET['newsletter']== "1")
          		{
          			$src .= " AND u.allowemail = '1'";
          		}
          		elseif ($_GET['newsletter']=="2")
          		{
          			$src .= " AND u.allowemail != '1'";
          		}
          		
          }

          //Filtre recherche "Inscrit au SMS"
          //if(isset($_GET['newsletterSms']) && $_GET['newsletterSms'] !="")
          if(isset($_GET['SMS']) && $_GET['SMS'] !="")
          {
          		if ($_GET['SMS']== "1")
          		{
          			$src .= " AND u.allowsms = '1'";
          		}
          		elseif ($_GET['SMS']=="2")
          		{
          			$src .= " AND u.allowsms != '1'";
          		}
          		
          }

          //Filtre recherche "Client pro"
          if(isset($_GET['typeclientpro']) && $_GET['typeclientpro'] !=""){
            if ($_GET['typeclientpro'] === "tri") {
                $src .= " AND u.typeclient_pro NOT IN (1,2,3,4,5,6,7,8,9,10,11,12,13)";
            }else{
               $src .= " AND u.typeclient_pro = ".$_GET['typeclientpro'];
            }
          }

          //filtre recherche date de début //filtre recherche date de fin
          if(isset($_GET['orderdate']) && $_GET['orderdate'] !="" && isset($_GET['deliverdate']) && $_GET['deliverdate'] !="")
          {
          	$date_debut_tab = explode('/', $_GET['orderdate']);
          	$date_debut_tab_bis = explode(' ', $date_debut_tab[2]);
          	$date_debut = $date_debut_tab_bis[0].'-'.$date_debut_tab[1].'-'.$date_debut_tab[0];

          	$date_fin_tab = explode('/', $_GET['deliverdate']);
          	$date_fin_tab_bis = explode(' ', $date_fin_tab[2]);
          	$date_fin = $date_fin_tab_bis[0].'-'.$date_fin_tab[1].'-'.$date_fin_tab[0];
          	$src .= " AND u.dateinscription BETWEEN  \"".$date_debut."\" AND \"".$date_fin."\"";
          }

         
         

          $src .= isset($sql_hack) && !empty($sql_hack) ? $sql_hack : '';
          
          //Construction de la requête à partir des filtres précédents
          $txtsql = "";
          $sql = "FROM utilisateur u";
          if((isset($_GET['src']) && $_GET['src']!="")|| isset($_GET['nom']) || isset($_GET['prenom'])) $sql .= " LEFT JOIN utilisateur_assoc ua USING(userid)";
          $sql .= " WHERE 1";
          if(isset($_GET['noprojet'])){ 
           if((isset($_GET['src']) && $_GET['src']!="")|| isset($_GET['nom']) || isset($_GET['prenom'])) $txtsql = " LEFT JOIN utilisateur_assoc ua USING(userid)";
           $sql="FROM utilisateur u $txtsql LEFT JOIN projet USING(userid) WHERE `conseils` = 'oui' AND projetid IS NULL AND (SELECT COUNT(*) FROM projet_relation WHERE userid=u.userid)=0 AND (SELECT COUNT(*) FROM facture WHERE (valide IN (1,5,7,8) OR moyenid IN (2,5)) AND userid=u.userid)=0 AND (SELECT COUNT(*) FROM suivi_client WHERE userid=u.userid AND etat=0)=0";
            }
          
          $sql .= $src;

          echo $sql;
          //Filtre sur clients à rappeler aujourd'hui
          if(isset($_GET['calltoday']) && $_GET['calltoday'] == 1){
               $sql = " FROM suivi_client sc";
               $sql .= " INNER JOIN utilisateur u ON (u.userid = sc.userid)";
               $sql .= " WHERE (";
               $sql .= "      SELECT rappel";
               $sql .= "      FROM suivi_client";
               $sql .= "      WHERE userid = u.userid";
               $sql .= "      ORDER BY date DESC";
               $sql .= "      LIMIT 1";
               $sql .= ") BETWEEN '".mktime(0,0,0,date("m"),date("d"),date("Y"))."' AND '".$tomorrow."'";
               $sql .= " GROUP BY sc.userid";
          }


          //Récupération du nombre de résultats
          $sql2 = " SELECT COUNT(*) ".$sql;
          $utilisateurcount = $DB_site2->query_first($sql2);			
          
          //Filtre sur une période
          //if(!isset($_POST['src']) && !isset($_GET['commercial'])) $sql.=" AND (UNIX_TIMESTAMP(dateinscription)>='".$start."' AND UNIX_TIMESTAMP(dateinscription)<=".($start+$pagedays*86400).")";
          
          $list = array();
          //Mise en place des flèches de tri
          $sensicon = array(
              "" => "<br><font color=\"red\">&#9650;</font>",
              "DESC" => "<br><font color=\"red\">&#9650;</font>",
              "ASC" => "<br><font color=\"red\">&#9660;</font>"
          );
          
          $sensordre_inscription = 'ASC';
          $sensordre_nclient     = 'ASC';
          $sensordre_societe     = 'ASC';
          $sensordre_nom         = 'ASC';
          $sensordre_commercial  = 'ASC';
          $sensordre_cde_exp  = 'ASC';
          $sensordre_cde_val  = 'ASC';
          $sensordre_ca          = 'ASC';
          $sensordre_etat        = 'ASC';
          $sensordre_rappel      = 'ASC';
          
          switch($_GET['orderby']){
               case "inscription":
               default:
                    $sql_sens = "ORDER BY u.dateinscription";
                    $sensordre_inscription = isset($_GET['sensorder']) && $_GET['sensorder'] == 'ASC' ? 'DESC' : 'ASC';
                    break;
               case "nclient":
                    $sql_sens = "ORDER BY u.userid";
                    $sensordre_nclient = isset($_GET['sensorder']) && $_GET['sensorder'] == 'ASC' ? 'DESC' : 'ASC';
                    break;
               case "societe":
                    $sql_sens = "ORDER BY u.raisonsociale";
                    $sensordre_societe = isset($_GET['sensorder']) && $_GET['sensorder'] == 'ASC' ? 'DESC' : 'ASC';
                    break;
               case "nom":
                    $sql_sens = "ORDER BY u.nom";
                    $sensordre_nom = isset($_GET['sensorder']) && $_GET['sensorder'] == 'ASC' ? 'DESC' : 'ASC';
                    break;
               case "commercial":
                    $sql_sens = "ORDER BY u.commercial";
                    $sensordre_commercial = isset($_GET['sensorder']) && $_GET['sensorder'] == 'ASC' ? 'DESC' : 'ASC';
                    break;
               case "cde_exp":
                    $sql_sens = "ORDER BY nb_cmd_expedie";
                    $sensordre_cde_exp = isset($_GET['sensorder']) && $_GET['sensorder'] == 'ASC' ? 'DESC' : 'ASC';
                    break;
               case "cde_val":
                    $sql_sens = "ORDER BY nb_cmd_valide";
                    $sensordre_cde_val = isset($_GET['sensorder']) && $_GET['sensorder'] == 'ASC' ? 'DESC' : 'ASC';
                    break;
               case "ca":
                    $sql_sens = "ORDER BY chiffre_affaire";
                    $sensordre_ca = isset($_GET['sensorder']) && $_GET['sensorder'] == 'ASC' ? 'DESC' : 'ASC';
                    break;
               case "etat":
                    $sql_sens = "ORDER BY etat_client";
                    $sensordre_etat = isset($_GET['sensorder']) && $_GET['sensorder'] == 'ASC' ? 'DESC' : 'ASC';
                    break;
               case "rappel":
                    $sql_sens = "ORDER BY rappel_client_tri";
                    $sensordre_rappel = isset($_GET['sensorder']) && $_GET['sensorder'] == 'ASC' ? 'DESC' : 'ASC';
                    break;
          }
          
          //$sql_order = (isset($_GET['sensorder']) && $_GET['sensorder'] == 'DESC') || !isset($_GET['orderby']) ? 'DESC' : 'ASC';
          $sql_order = (isset($_GET['sensorder']) && $_GET['sensorder'] == 'DESC') || !isset($_GET['orderby']) ? 'DESC' : 'DESC';
          
          //Reconstruction du début de l'url
          if(!isset($_GET['pagenumber'])) $_GET['pagenumber'] = 1; //Ne pas supprimer ! pour le bon fonctionnement du tri
          $debut_url = '';
          $url_pagenav = '';
          $params_exclusion = array('sensorder', 'orderby');
          foreach($_GET as $param => $value){
               if(!in_array($param, $params_exclusion)){
                    $debut_url .= empty($debut_url) ? '?' : '&';
                    $debut_url .= $param.'='.$value;
                    if($param != 'pagenumber'){
                         $url_pagenav .= empty($url_pagenav) ? '?' : '&';
                         $url_pagenav .= $param.'='.$value;
                    }
               }
          }
          
          //Récupération pagination
          $pagenav = getpagenav($utilisateurcount[0], $url_pagenav);
          
          //Construction requête finale
          $sqlFinale = "SELECT u.raisonsociale, u.nom, u.prenom, u.userid, u.mail, u.dateinscription, u.commercial, u.vip, u.site_code, u.langue, u.conseils, u.typeclient_pro";
          //Chiffre d'affaire généré par le client
          $sqlFinale .= ", (SELECT SUM((lf1.qte * lf1.prix) / (1 + (lf1.tva / 100))) FROM facture f1 INNER JOIN lignefacture lf1 ON (lf1.factureid = f1.facturid) WHERE f1.userid = u.userid AND f1.supprimee = '0' AND f1.valide IN (1,5,7,8)) + (SELECT SUM((f2.prixport / (1 + (f2.tvaport / 100))) - (f2.montantcadeau / (1 + (f2.tvaport / 100)))) FROM facture f2 WHERE f2.userid = u.userid AND f2.supprimee = '0' AND f2.valide IN (1,5,7,8)) as chiffre_affaire";
          //Nombre de commandes valides
          $sqlFinale .= ", (SELECT COUNT(*) FROM facture f3 WHERE f3.userid = u.userid AND f3.supprimee = '0' AND f3.valide = 1) as nb_cmd_valide";
          //Nombre de commandes expédiées
          $sqlFinale .= ", (SELECT COUNT(*) FROM facture f4 WHERE f4.userid = u.userid AND f4.supprimee = '0' AND f4.valide IN (5,8)) as nb_cmd_expedie";
          //Nombre de devis en cours
          $sqlFinale .= ", (SELECT COUNT(*) FROM facture f5 WHERE f5.userid = u.userid AND f5.supprimee = '0' AND f5.valide = 0) as nb_cmd_devis";
          //Etat du client (en cours, perdu, etc...)
		  /*
          $sqlFinale .= ", (SELECT CASE f6.valide
							WHEN 0 THEN (SELECT CASE sc.etat
								WHEN 0 THEN '<font color=\"red\">Perdu</font>'
								WHEN 1 THEN '<font color=\"#0000AA\">&Agrave; suivre</font>'
								WHEN 2 THEN '<font color=\"#0000FF\">Attente info client</span>'
								WHEN 3 THEN '<font color=\"#000088\">&Agrave; chiffrer</span>'
								WHEN 4 THEN '<font color=\"#000\">Dossier sous-traité</span>' END
								FROM suivi_client sc  USE INDEX(userid)
								WHERE sc.userid = u.userid ORDER BY sc.date DESC LIMIT 0, 1) 
							WHEN 1 THEN '<font color=\"green\">En cmd</font>'
							WHEN 2 THEN 'Refus'
							WHEN 3 THEN 'Refus commerçant'
							WHEN 5 THEN '<font color=\"green\">Expédié</font>'
							WHEN 7 THEN '<font color=\"green\">En cmd</font>'
							WHEN 8 THEN '<font color=\"green\">Expédié</font>' END
							FROM facture f6  USE INDEX(userid)
							WHERE f6.userid = u.userid
							AND f6.supprimee = '0'
							ORDER BY facturid DESC LIMIT 0, 1)
							as etat_client ";
		  */
          //Dernier rappel client
          $sqlFinale .= ", (SELECT IF(sc2.rappel != 0, CONCAT(DATE_FORMAT(FROM_UNIXTIME(sc2.rappel), '%d/%m/%Y à %H:%i'),' par ', sc2.pour), '') FROM suivi_client sc2  USE INDEX(userid) WHERE sc2.userid = u.userid ORDER BY sc2.date DESC LIMIT 0, 1) as rappel_client ";
          $sqlFinale .= ", (SELECT sc3.rappel FROM suivi_client sc3  USE INDEX(userid) WHERE sc3.userid = u.userid ORDER BY sc3.date DESC LIMIT 0, 1) as rappel_client_tri ";
          $sqlFinale .= $sql;
          $sqlFinale .= " GROUP BY u.userid";
          $sqlFinale .= " ".$sql_sens." ".$sql_order;
          $sqlFinale .= " LIMIT ".$limitlower.",".$limitupper; 
          $rq_client = $DB_site2->query($sqlFinale);
         	//echo $sqlFinale;
          // **************************************** TSI **************************************************
          // ****** insertion du panneau de recherche lors de l'ouverture de la page
          include('includes/contact_recherche.php');
          
          echo $fil_ariane;
?>
<div class="commande_detail">
     <table align="center" style="padding: 10px; width:100%">
               <tr>
                    <td align="left" valign="top"><strong><?php echo $utilisateurcount[0]; ?> résultat(s)</strong></td>
                    <td align="right"><a title="Exporter la sélection" href="recherche_export.php<?php echo $url_export; ?>" class="nouvelle_rubrique" style=""><img alt="Exporter la sélection" src="<?php echo BACK_HOST; ?>/images/icon/export.png" class="icon" style="margin: 0 5px 0 0; padding: 0;"></a><strong>Exporter la sélection</strong></td>
               </tr>
               <tr>
                    <td colspan="2">
                         <div id="utilisateurs">
                         <table class="table_modules_boutiques no_border liste_clients">
                              <?php if(!empty($pagenav)){ ?>
                              <tr>
                                   <td class="nav" colspan="14" align="center"><?php echo $pagenav; ?></td>  
                              </tr>
                              <?php } ?>
                              <tr>
                                   <td class="titre" colspan="14" align="center">Utilisateurs inscrits sur le site</td>  
                              </tr>
                              <?php

                              //Affichage du tableau s'il y a des résultats
                              if($DB_site2->num_rows($rq_client) > 0){

                                   $param = (isset($_GET['commercial']) ? "&commercial=".$_GET['commercial'] : "").(isset($_GET['encours']) ? "&encours=1" : "");

                                   ?>
                                   <tr>
                                        <td class="entete" align="center"><b><a href="<?php echo $debut_url; ?>&orderby=inscription&sensorder=<?php echo $sensordre_inscription; ?>">Inscription<?php echo ($orderby == "inscription" ? $sensicon[$sensordre_inscription] : ""); ?></a></b></td>
                                        <td class="entete" align="center"><b><a href="<?php echo $debut_url; ?>&orderby=nclient&sensorder=<?php echo $sensordre_nclient; ?>">N°<?php echo ($orderby == "nclient" ? $sensicon[$sensordre_nclient] : ""); ?></a></b></td>
                                        <!--<td align="center"><b>Mail</b></td>-->
                                        <td class="entete" align="center"><b>Site</b></td>
                                        <td class="entete" align="center"><b><a href="<?php echo $debut_url; ?>&orderby=societe&sensorder=<?php echo $sensordre_societe; ?>">Société<?php echo ($orderby == "societe" ? $sensicon[$sensordre_societe] : ""); ?></a></b></td>
                                        <td class="entete" align="center"><b><a href="<?php echo $debut_url; ?>&orderby=nom&sensorder=<?php echo $sensordre_nom; ?>">Nom<?php echo ($orderby == "nom" ? $sensicon[$sensordre_nom] : ""); ?></a></b></td>
                                        <td class="entete" align="center"><b><a href="<?php echo $debut_url; ?>&orderby=commercial&sensorder=<?php echo $sensordre_commercial; ?>">Com.<?php echo ($orderby == "commercial" ? $sensicon[$sensordre_commercial] : ""); ?></a></b></td>
                                        <td class="entete" align="center"><b><a href="<?php echo $debut_url; ?>&orderby=cde_exp&sensorder=<?php echo $sensordre_cde_exp; ?>">Cde exp.<?php echo ($orderby == "cde_exp" ? $sensicon[$sensordre_cde_exp] : ""); ?></a></b></td>
                                        <!--<td class="entete" align="center"><b><a href="<?php //echo $debut_url; ?>&orderby=cde_val&sensorder=<?php //echo $sensordre_cde_val; ?>">Cde val.<?php //echo ($orderby == "cde_val" ? $sensicon[$sensordre_cde_val] : ""); ?></a></b></td>-->
                                        <td class="entete" align="center"><b><a href="<?php echo $debut_url; ?>&orderby=ca&sensorder=<?php echo $sensordre_ca; ?>">CA HT<?php echo ($orderby == "ca" ? $sensicon[$sensordre_ca] : ""); ?></a></b></td>
                                        <!--<td class="entete" align="center"><b>Devis en cours</b></td>-->
                                        <td class="entete" align="center"><b><a href="<?php echo $debut_url; ?>&orderby=etat&sensorder=<?php echo $sensordre_etat; ?>">Etat<?php echo ($orderby == "etat" ? $sensicon[$sensordre_etat] : ""); ?></a></b></td>
                                        <!--<td class="entete" align="center"><b>Fid.</b></td>-->
                                        <td class="entete" align="center"><b>Conseils</b></td>
                                        <td class="entete" align=center><b>Fiche</b></td>
                                   </tr>
                                   <?php
                                   
                                   $cnt = 1;
                                   while($rs_client = $DB_site2->fetch_array($rq_client)){
										if($rs_client['site_code']=="") $rs_client['site_code']="base";

                                        $lastvalide = 0;

                                        list($date_inscription, $horaire_inscription) = explode(' ', $rs_client['dateinscription']);
                                        list($annee, $mois, $jour) = explode( "-", $date_inscription );
                                        list($heure, $minute, $seconde) = explode( ":", $horaire_inscription );
                                        
										/*
                                        if($rs_client['rappel_client'] > 0){
                                             $tabExplode = explode(' ', $rs_client['rappel_client']);
                                             if(!is_numeric($tabExplode[1])) $tabExplode[1] = $tabExplode[2];
                                             list($jour_rappel, $mois_rappel, $annee_rappel) = explode('/', $tabExplode[0]);
                                             list($heure_rappel, $minute_rappel) = explode(':', $tabExplode[1]);
                                             $date_rappel = mktime($heure_rappel,$minute_rappel,0,$mois_rappel,$jour_rappel,$annee_rappel);
                                             $date_auj    = mktime(date('H'),date('i'),0,date('n'),date('j'),date('Y'));
                                             $date_hier   = mktime(date('H'),date('i'),0,date('n'),date('j')-1,date('Y'));
                                             $date_demain = mktime(date('H'),date('i'),0,date('n'),date('j')+1,date('Y'));
                                             if($date_rappel < $date_auj){
                                                  $color = "style=\"color:red;\"";
                                             } elseif($date_rappel > $date_auj && $date_rappel < $date_demain){
                                                  $color = "style=\"color:green;\"";
                                             }
                                        }
										*/
										
										$rs_client['rappel_client']="";
										$br="";
										$projets = $DB_site2->query("SELECT projet.projetid, projet.rappel, projet_etat.libelle AS etat_libelle, projet_etat.color AS etat_color FROM projet INNER JOIN projet_etat ON (projet.etatid=projet_etat.etatid) WHERE userid='".$rs_client['userid']."' ");	
										while($projet=$DB_site2->fetch_array($projets)){
											$rs_client['rappel_client'].=$br."<font color=\"".$projet['etat_color']."\">".$projet['etat_libelle'];
											if($projet['rappel']>0){
												$rs_client['rappel_client'].="(".date("d/m/Y H:i:s", $projet['rappel']).")";
											}
											$rs_client['rappel_client'].="</font>";
											$br="<br>";
										}
										
                                         
                                        $class_td = $cnt%2 == 0 ? 'td_alt2' : 'td_alt1';
                                        ?>
                                        <tr>
                                             <td class="<?php echo $class_td; ?>" align="center"><?php echo $jour; ?>/<?php echo $mois; ?>/<?php echo $annee; ?> - <?php echo $heure; ?>h<?php echo $minute; ?></td>
                                             <td class="<?php echo $class_td; ?>" align="center"><?php echo $rs_client['userid']; ?></td>
                                             <td class="<?php echo $class_td; ?>" align="center"><?php echo $template->urlcode($rs_client['site_code'],$rs_client['langue']); ?></td>
                                             <td class="<?php echo $class_td; ?>" align="center"><?php echo $rs_client['raisonsociale']; ?></td>
                                             <td class="<?php echo $class_td; ?>" align="center"><?php echo usericon($rs_client['vip'],20).' '.$rs_client['nom'].' '.$rs_client['prenom']; ?></td>
                                             <td class="<?php echo $class_td; ?>" align="center"><?php echo $rs_client['commercial']; ?></td>
                                             <td class="<?php echo $class_td; ?>" align="center"><?php echo $rs_client['nb_cmd_expedie']; ?></td>
                                             <!--<td class="<?php echo $class_td; ?>" align="center"><?php echo $rs_client['nb_cmd_valide']; ?></td>-->
                                             <td class="<?php echo $class_td; ?>" align="center" nowrap><?php echo number_format($rs_client['chiffre_affaire'], 2, ',', ' '); ?> &euro;</td>
                                             <!--<td class="<?php echo $class_td; ?>" align="center"><?php echo $rs_client['nb_cmd_devis']; ?></td>-->
                                             <td class="<?php echo $class_td; ?>" align="center" <?php echo $color; ?>><?php echo $rs_client['rappel_client']; ?></td>
                                             <!--<td class="<?php//echo $class_td; ?>" align="center" title="<?php //echo $fidelisation['libelle']; ?>"><?php //echo $fidel_date; ?></td>-->
                                             <td class="<?php echo $class_td; ?>" align="center"><?php echo ($rs_client['conseils'] != '' ? $rs_client['conseils']:'non défini'); ?></td>
                                             <td class="<?php echo $class_td; ?>" align="center"><a href="utilisateur.php?action=editer&userid=<?php echo $rs_client['userid']; ?>"><img src="images/icon/edit.png"></a></td>
                                        </tr>
                                        <?php
                                        
                                        $cnt++;
                                   }
                              }
                              ?>
                              <tr>
                                   <td class="nav" align="center" colspan="13"><?php echo $pagenav; ?></td>
                              </tr>
                         </table>
                    </div>
               </td>
          </tr>
     </table>
</div>
                                             <?php
     }
	elseif( $action == "champopt" )
		{
// 		$DB_site2->query("update parametresgeneraux set user_option_actif = '$actif', user_option_lib = '$libchamp', user_option_obligatoire = '$oblig'");
		echo "<script language=\"javascript\">location.href=\"utilisateur.php\";</script>";
		}
	elseif( $action == "new" ) // Creation d'un nouveau client 
		{
		$connusite="<option value=\"\"></option>";
		$creq=$DB_site2->query("SELECT * FROM formulaire_champ_valeur WHERE formulairechampid=74 ORDER BY valeur");
		while($cdata=$DB_site2->fetch_array($creq)){
		      $connusite.="<option value=\"".$cdata['valeur']."\"".($_POST['connu']==$cdata['valeur']?" selected":"").">".$cdata['description']."</option>";
		}
		if(!isset($_POST['paysid'])) $_POST['paysid']=57;
		$listepays="";
		$r=$DB_site2->query("SELECT DISTINCT p.paysid,p.libelle from fraisport as f inner join pays as p on (f.paysid=p.paysid) order by p.libelle");
		while($pays=$DB_site2->fetch_array($r))
		      $listepays.="<option value=\"$pays[0]\"".($pays[0]==$_POST['paysid']?" selected":"").">$pays[1]</option>";

		echo "<script language=\"javascript\">
		  function checkch(chk){
		    if(chk.value==0){
			for(i=1;i<document.getElementsByName('contacthour[]').length;i++){
			    document.getElementsByName('contacthour[]')[i].checked=false;
			}
		    }else{
			var onecheck=false;
			for(i=1;i<document.getElementsByName('contacthour[]').length;i++)
			    if(document.getElementsByName('contacthour[]')[i].checked==true) onecheck=true;
			
			document.getElementsByName('contacthour[]')[0].checked=!onecheck;
		    }
		  }
		  </script>";

		$optlangue="";
		foreach($langues as $langue) $optlangue.="<option value=\"$langue\">$langue</option>";
		
		//********************************************* TSI ***********************************************
		//********** Insertion panneau de recherche
		include('includes/contact_recherche.php');
         
		// Formulaire de saisie des infos client
        echo "<form method=\"post\" onsubmit=\"if(document.getElementsByName('password')[0].value=='')document.getElementsByName('password')[0].value=random_password_generate();return true;\">
		<input type=\"hidden\" name=\"action\" value=\"register\">
		<div style=\"color:red;\">$error</div>
		<table>
		<tr>
            <td>&nbsp;</td>
        </tr>
		<tr>
            <td>Civilité</td>
            <td>
                <select name=\"civilite\">
                    <option value=\"0\"".($_POST['civilite']==0?" selected":"").">M.</option>
                    <option value=\"1\"".($_POST['civilite']==1?" selected":"").">Mme</option>
                </select>
            </td>
        </tr>
		<tr>
            <td>Nom</td>
            <td><input type=\"text\" name=\"nom\" value=\"".$_POST['nom']."\"></td>
        </tr>
		<tr>
            <td>Prenom</td>
            <td><input type=\"text\" name=\"prenom\" value=\"".$_POST['prenom']."\"></td>
        </tr>
		<tr>
            <td>Société</td>
            <td><input type=\"text\" name=\"raisonsociale\" value=\"".$_POST['raisonsociale']."\"></td>
        </tr>
		<tr>
            <td>N°TVA intra</td>
            <td><input type=\"\" name=\"tvaintracom\" value=\"".$_POST['tvaintracom']."\"></td>
        </tr>
		<tr>
            <td>Adresse</td>
            <td><input type=\"text\" name=\"adresse\" size=\"40\" value=\"".$_POST['adresse']."\"></td>
        </tr>
		<tr>
            <td>Code postal</td>
            <td><input type=\"text\" name=\"codepostal\" size=\"5\" value=\"".$_POST['codepostal']."\"></td>
        </tr>
		<tr>
            <td>Ville</td>
            <td><input type=\"text\" name=\"ville\" value=\"".$_POST['ville']."\"></td>
        </tr>
		<tr>
            <td>Pays</td>
            <td><select name=\"paysid\">$listepays</select></td>
        </tr>
		<tr>
            <td>Langue</td>
            <td><select name=\"langue\">$optlangue</select></td>
        <tr>
		<tr>
            <td>Téléphone</td>
            <td><input type=\"text\" name=\"telephone\" value=\"".$_POST['telephone']."\"></td>
        </tr>
		<tr>
            <td>Portable</td>
            <td><input type=\"text\" name=\"portable\" value=\"".$_POST['portable']."\"></td>
        </tr>
		<tr>
            <td>Email</td>
            <td><input type=\"text\" name=\"mail\" value=\"".$_POST['mail']."\"></td>
        </tr>
		<tr>
            <td>Connu le site par</td>
            <td><select name=\"connu\">$connusite</select></td>
        </tr>
		<tr>
            <td>Mot de passe</td>
            <td><input type=\"text\" name=\"password\" value=\"".$_POST['password']."\"> <input type=\"button\" value=\"Générer\" onclick=\"document.getElementsByName('password')[0].value=random_password_generate();\"></td>
        </tr>
		<tr>
            <td>Conseils personnalisés </td>
            <td>&nbsp;&nbsp;<input type=\"radio\" name=\"conseils\" value=\"oui\" checked>Oui &nbsp; / &nbsp; <input type=\"radio\" name=\"conseils\" value=\"non\"> Non</td>
        </tr>
        <tr>
            <td style=\"color:red;font-weight:700;font-size:13px;\">Envoyer mail inscription </td><td>&nbsp;&nbsp;<input type=\"radio\" name=\"mailregister\" value=\"oui\">Oui &nbsp; / &nbsp; <input type=\"radio\" name=\"mailregister\" value=\"non\" checked> Non</td>
        </tr>
		<tr>
            <td colspan=\"2\" height=\"30\" align=\"center\"><input type=\"submit\" value=\"Créer le client/utilisateur\"></td>
        </tr>
		</table>
		</form>";
		
     } elseif( $action == "editer" ){
          $chcheck = array(
               0 => "Peu importe l'heure et la date",
               1 => "9h-12h",
               2 => "12h-14h",
               3 => "14h-18h",
               4 => "18h-20h",
               5 => "lundi",
               6 => "mardi",
               7 => "mercredi",
               8 => "jeudi",
               9 => "vendredi",
               10 => "samedi"
          );
          
          //Modifications dans l'encart "Communication client" 
          if(isset($_POST['comform'])){
               $com = "";
               $suivi .= "Moyen de communication : ";
               if(isset($_POST['communication'])){
                    $com=implode(",",$_POST['communication']);
                    if(in_array("1",$_POST['communication'])){
                         $suivi.="téléphone ";
                    }
                    if(in_array("2",$_POST['communication'])){
                         $suivi.="mail ";
                    }
               }else {
                    $suivi.=" Aucun";
               }

               $sqlch="";
               $suivi.="<br>Heure de contact : ";
               if(isset($_POST['contacthour'])){
                    foreach($_POST['contacthour'] as $ch){
                         $suivi.=$chcheck[$ch]." ";
                    }
                    $sqlch=implode(",",$_POST['contacthour']);
               }else{
                    $suivi.=$chcheck[0]; 
               }
                     
               $suivi .= "<br>Newsletter : ".(isset($_POST['newsletter'])?"Oui":"Non");
               
               //Mise à jour des informations
               $sql = "UPDATE utilisateur SET";
               $sql .= " contacthour = '".$sqlch."'";
               $sql .= ", allowemail = '".(isset($_POST['newsletter']) ? 1 : 0)."'";
               $sql .= ", allowsms = '".(isset($_POST['SMS']) ? 1 : 0)."'";
               $sql .= ", communication = '".$com."'";
               $sql .= " WHERE userid = ".intval($_GET['userid']);
               $DB_site2->query($sql);
               
               $sql = "SELECT etat";
               $sql .= " FROM suivi_client";
               $sql .= " WHERE userid = ".$_GET['userid']."";
               $sql .= " ORDER BY date";
               $sql .= " DESC LIMIT 1";
               $last = $DB_site2->query_first($sql);
               if($last==""){
                    $last['etat']=1;
               }
               
               //Mise à jour du suivi
               $sql = "INSERT INTO suivi_client SET";
               $sql .= " userid = '$_GET[userid]'";
               $sql .= ", commercial = '$user_info[username]'";
               $sql .= ", date = ".time()."";
               $sql .= ", suivi = '".addslashes($suivi)."'";
               $sql .= ",etat = ".intval($last['etat']);
               $DB_site2->query($sql);
          }

          //Mise à jour des besoins
          if(isset($_POST['besoin'])){
               $sql = "UPDATE utilisateur SET";
               $sql .= " besoin = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['besoin'])."'";
               $sql .= " WHERE userid=".intval($_GET['userid']);
               $r=$DB_site2->query($sql);
               
               //Mise à jour du suivi
               $sql = "INSERT INTO suivi_client SET";
               $sql .= " userid = '".$_GET['userid']."'";
               $sql .= ", commercial = '".$user_info['username']."'";
               $sql .= ", date = ".time();
               $sql .= ", suivi = 'Besoin client : ".addslashes($_POST['besoin'])."'";
               $sql .= ", etat = 1";
               $DB_site2->query($sql);
          }

          //Mise à jour des propositions de produits au client
          if(isset($_GET['prop'])){
               $sql = "SELECT COUNT(artid)";
               $sql .= " FROM user_prop";
               $sql .= " WHERE userid = ".intval($_GET['userid']);
               $sql .= " AND artid = ".intval($_GET['prop']);
               $result = $DB_site2->query_first($sql);
               
               $propqte = intval($_GET['qte']);
               if($propqte == 0){
                    $propqte = 1;
               }
               
               //Si le produit n'est pas déjà dans les propositions, on l'ajoute
               if($result[0] == 0){
                    $sql = "INSERT INTO user_prop SET";
                    $sql .= " userid = '".intval($_GET['userid'])."'";
                    $sql .= ", artid = ".intval($_GET['prop']);
                    $sql .= ", qte = ".$propqte;
                    $sql .= ", caractvalid = '".$_GET['caract']."'";
                    $sql .= ", date = ".time();
                    $sql .= ", utilisateur = '".$user_info['username']."'";
                    $DB_site2->query($sql);
               }
          }
          
		  //Suppression d'une proposition d'article
          if(isset($_GET['rmprop'])){
               $sql = "DELETE FROM user_prop";
               $sql .= " WHERE id = ".intval($_GET['rmprop']);
               $DB_site2->query($sql);
          }
		  
          //Modifications dans la fiche client (Coordonees, Informations complementaires)
          if(isset($_POST['edituser'])){
               //Récupération des informations sur le client
               $sql = "SELECT *";
               $sql .= " FROM utilisateur";
               $sql .= " WHERE userid = '".$_GET['userid']."'";
               $utilisateur = $DB_site2->query_first($sql);
               
               //Mise en forme du texte de suivi
               $txt="[infos_client] Modification infos client";
               
               $sql = "";

               $redirect_to = 'null';
               if(isset($_POST['redirect_to']) && isset($_POST['redirect_from'])) {
           			if($_POST['redirect_from'] != $_POST['redirect_to']) $redirect_to = "'".$_POST['redirect_to']."'";
           		}
               //Mise en forme de la requête de modification des infos client
               foreach($utilisateur as $key => $value){
               		if (!isset($_POST['nocomplement'])) {
                    	if($key == 'allowemail' && !isset($_POST['allowemail'])) $_POST['allowemail'] = '';
	                    //if($key == 'allowsms' && !isset($_POST['SMS'])) $_POST['SMS'] = '';
                    	if($key == 'allowsms' && !isset($_POST['allowsms'])) $_POST['allowsms'] = '';
               		}	
                    if($key == 'utilisateur_admin' && !isset($_POST['utilisateur_admin'])) $_POST['utilisateur_admin'] = 0;

                    if(isset($_POST[$key])){
                         switch($key){
                              case 'tvaintracom':
                                   //$result = checkvatnumber($_POST[$key]); mis en commentaire le 28/02/2019 car service indisponible pour le moment
                                   /*if($result == false){
                                        $txt .= "<br />Vérification TVA Intra : Erreur serveur, vérification impossible";
                                        $_POST[$key] = "";
                                   }else{
                                        $txt .= "<br />Vérification TVA Intra ".$_POST[$key]." : ".($result->valid ? "OK" : "Erreur");
                                        $_POST[$key] = ($result->valid ? $result->countryCode.$result->vatNumber : "");
                                   }*/
                                   
                                   break;
                              case 'pourcentage_remise':
                                   $_POST[$key] = str_replace(array(',','¤'), array('.',''), $_POST[$key]);
                                   break;

                               case 'portable':
							        if(substr($_POST[$key],0,2)=="00"){
									  $_POST[$key]="+".substr($_POST[$key],2);
								    }
                              		$_POST[$key] = str_replace(array(" ",".","-"), "", $_POST[$key]);
                              		break;
                               case 'nom':
                               case 'prenom':
                               case 'codepostal':
                               case 'adresse':
                               case 'ville':
                               case 'paysid':
                               case 'raisonsociale':
                               		$sql .= " ".$key."_livraison = '".mysqli_real_escape_string($DB_site2->link_id,trim($_POST[$key]))."',";
                               		break;	
	                           case 'telephone':
	                          		$_POST[$key] = str_replace(array(" ",".","-"), "", $_POST[$key]);
									if(substr($_POST[$key],0,2)=="00"){
									  $_POST[$key]="+".substr($_POST[$key],2);
								    }
	                          		$sql .= " ".$key."_livraison = '".mysqli_real_escape_string($DB_site2->link_id,trim($_POST[$key]))."',";
	                          		break;	

                         }
                         $sql .= " ".$key." = '".mysqli_real_escape_string($DB_site2->link_id,trim($_POST[$key]))."',";
                         if($value != $_POST[$key]){
                              $txt.="<br />".$key." : ".$value." =&gt; ".$_POST[$key];
                         }
                    }
               }
               
               $sql = "UPDATE utilisateur SET ".substr($sql,0,-1).", redirection_site = $redirect_to WHERE userid = ".$_GET['userid'];
               $DB_site2->query($sql);
               
               $sql = "SELECT etat";
               $sql .= " FROM suivi_client";
               $sql .= " WHERE userid=".$_GET['userid'];
               $sql .= " ORDER BY date DESC";
               $sql .= " LIMIT 1";
               $last = $DB_site2->query_first($sql);
               if($last==""){
                    $last['etat'] = 1;
               }
               
               //Insertion dans le suivi
               $sql = "INSERT INTO suivi_client SET";
               $sql .= " userid = '".$_GET['userid']."'";
               $sql .= ", commercial = '".$user_info['username']."'";
               $sql .= ", date = ".time();
               $sql .= ", suivi = '".mysqli_real_escape_string($DB_site2->link_id,$txt)."'";
               $sql .= ", etat = ".intval($last['etat']);
               $sql .= ", compte =0";
               $DB_site2->query($sql);
          }

          	//Gestion des images envoyées
            if(empty($_FILES['photo_client']['name']) === false)
            {
				$dossier_dest   = "../configurations/$host/photos_clients";
				$nom_image      = "client_" . $_GET['userid'] . "." . substr($_FILES["photo_client"]["name"],strrpos($_FILES["photo_client"]["name"],'.')+1,strlen($_FILES["photo_client"]["name"]));
				$ancienne_image = imagecreatefromjpeg($_FILES["photo_client"]["tmp_name"]);
				
				$aDimImages       = getimagesize($_FILES["photo_client"]["tmp_name"]);
				$ancienne_largeur = $aDimImages[0];
				$ancienne_hauteur = $aDimImages[1];

				$ext_image = pathinfo($_FILES["photo_client"]["name"], PATHINFO_EXTENSION);

				if(in_array($ext_image, array('jpeg','jpg','gif','png')))
				{
					if($ancienne_largeur >= $ancienne_hauteur) //Format paysage
					{
						//Si la largeur est supérieure à 80px, on redimensionne
						if($ancienne_largeur > 80)
						{
							$coeff            = $ancienne_largeur / $ancienne_hauteur;
							$nouvelle_largeur = 80;
							$nouvelle_hauteur = round(80 / $coeff);
							$nouvelle_image   = imagecreatetruecolor($nouvelle_largeur, $nouvelle_hauteur);

							imagecopyresampled($nouvelle_image , $ancienne_image , 0 , 0 , 0 , 0 , $nouvelle_largeur , $nouvelle_hauteur , $ancienne_largeur , $ancienne_hauteur);
						}
					}
					else //Format portrait
					{
						//Si la hauteur est supérieure à 72px, on redimensionne
						if($ancienne_hauteur > 80)
						{
							$coeff            = $ancienne_hauteur / $ancienne_largeur;
							$nouvelle_largeur = round(80 / $coeff);
							$nouvelle_hauteur = 80;
							$nouvelle_image   = imagecreatetruecolor($nouvelle_largeur, $nouvelle_hauteur);

							imagecopyresampled($nouvelle_image , $ancienne_image , 0 , 0 , 0 , 0 , $nouvelle_largeur , $nouvelle_hauteur , $ancienne_largeur , $ancienne_hauteur);
						}
					}

					//Suppression de l'image précédente
					@unlink($dossier_dest . '/' . $_FILES["photo_client"]["name"]);

					//Enregistrement de l'image
					switch($ext_image)
					{
						case 'jpg':
						case 'jpeg':
							imagejpeg($nouvelle_image, $dossier_dest . '/' . $nom_image, 100);
							break;
						case 'gif':
							imagegif($nouvelle_image, $dossier_dest . '/' . $nom_image, 100);
							break;
						case 'png':
							imagepng($nouvelle_image, $dossier_dest . '/' . $nom_image, 100);
							break;
					}
				}
				else
				{
					$erreur_message = "Mauvaise format d'image (autre que JPG, GIF, PNG)";
				}
            }


            //Suppression d'image
            if(isset($_POST['removeImg'])){
            	$sql = "SELECT *";
                $sql .= " FROM utilisateur";
                $sql .= " WHERE userid = '".$_GET['userid']."'";
                $utilisateur = $DB_site2->query_first($sql);
                $photo = "../configurations/$host/photos_clients/client_".$utilisateur[userid];
                $photoSuppr = glob($photo.".{jpg,gif,png,jpeg}", GLOB_BRACE)[0];

               if(file_exists($photoSuppr)){
               		unlink($photoSuppr);
               		echo "le fichier".$photoSuppr." a été supprimé";
               }
               else{
               		echo "le fichier".$photoSuppr." n'a pas pu être supprimé";
               }
            }

            //Ajout de budjet et d'écheance
            if(isset($_POST['check_echeance_projet'])){
            	$sql = "SELECT *";
                $sql .= " FROM utilisateur";
                $sql .= " WHERE userid = '".$_GET['userid']."'";
                $utilisateur = $DB_site2->query_first($sql)[userid];
                $budget = str_replace(',', '.', $_POST['budget_projet']);
                
                $sql = "UPDATE utilisateur SET";
                $sql .= " echeance_projet = '".$_POST['echeance_projet']."'";
				$sql .= ", livraison_projet = '".$_POST['livraison_projet']."'";
                $sql .= ", budget_projet = ".$budget;
                $sql .= " WHERE userid = ".$utilisateur;
                $DB_site2->query($sql);
            }

			
          //Ajout d'un produit au programme de fidélisation
          if(isset($_POST['fidel_libelle'])){
               $sp = preg_split('/[- :\/h]/',$_POST['fidel_date']);
               $tsrappel = mktime($sp[3],$sp[4],0,$sp[1],$sp[0],$sp[2]);
               if(isset($_POST['fidel_check'])){
                    $_POST['fidel_libelle'].=" ".implode(" ",$_POST['fidel_check']);
               }
               
               $sql = "INSERT INTO fidelisation SET";
               $sql .= " userid = '".$_GET['userid']."'";
               $sql .= ", libelle = '".trim($_POST['fidel_libelle'])."'";
               $sql .= ", date = ".intval($tsrappel);
               $sql .= ", periode = ".intval($_POST['fidel_periodicite']);
               $sql .= ", utilisateur = '".$_POST['fidel_utilisateur']."'";
               $DB_site2->query($sql);
          }

          //Suppression d'un produit du programme de fidélisation
          if(isset($_GET['del_fidel'])){
               $DB_site2->query("DELETE FROM fidelisation WHERE id=".$_GET['del_fidel']);
          }

		  //Ajout d'un contact rattaché à la fiche
          if(isset($_POST['addcontact'])){
			   if(substr($_POST['user_assoc_tel'],0,2)=="00"){
			      $_POST['user_assoc_tel']="+".substr($_POST['user_assoc_tel'],2);
			   }
			   if(substr($_POST['user_assoc_portable'],0,2)=="00"){
			      $_POST['user_assoc_portable']="+".substr($_POST['user_assoc_portable'],2);
			   }
               $sql = "INSERT INTO utilisateur_assoc (";
               $sql .= "utilisateur_assoc_fonction";
               $sql .= ", utilisateur_assoc_civilite";
               $sql .= ", utilisateur_assoc_prenom";
               $sql .= ", utilisateur_assoc_nom";
               $sql .= ", utilisateur_assoc_tel";
               $sql .= ", utilisateur_assoc_portable";
               $sql .= ", utilisateur_assoc_email";
               $sql .= ", userid";
               $sql .= ") VALUES (";
               $sql .= "'" . mysqli_real_escape_string($DB_site2->link_id,$_POST['user_assoc_fonction']) . "'";
               $sql .= ", '" . $_POST['user_assoc_civilite'] . "'";
               $sql .= ", '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['user_assoc_prenom']) . "'";
               $sql .= ", '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['user_assoc_nom']) . "'";
               $sql .= ", '" . preg_replace("/[^\d\+]/","",$_POST['user_assoc_tel']) . "'";
               $sql .= ", '" . preg_replace("/[^\d\+]/","",$_POST['user_assoc_portable']) . "'";
               $sql .= ", '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['user_assoc_mail']) . "'";
               $sql .= ", " . $_POST['userid'];
               $sql .= ")";
               
               $DB_site2->query($sql);
          }
          
          //Edition d'un contact rattaché à une fiche
          if(isset($_POST['edit_contact_assoc'])){
			   if(substr($_POST['user_assoc_tel'],0,2)=="00"){
			      $_POST['user_assoc_tel']="+".substr($_POST['user_assoc_tel'],2);
			   }
			   if(substr($_POST['user_assoc_portable'],0,2)=="00"){
			      $_POST['user_assoc_portable']="+".substr($_POST['user_assoc_portable'],2);
			   }
               $sql = "UPDATE utilisateur_assoc SET";
               $sql .= " utilisateur_assoc_fonction  = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['user_assoc_fonction'])."'";
               $sql .= ", utilisateur_assoc_civilite = '".$_POST['user_assoc_civilite']."'";
               $sql .= ", utilisateur_assoc_prenom   = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['user_assoc_prenom'])."'";
               $sql .= ", utilisateur_assoc_nom      = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['user_assoc_nom'])."'";
               $sql .= ", utilisateur_assoc_tel      = '".preg_replace("/[^\d\+]/","",$_POST['user_assoc_tel'])."'";
               $sql .= ", utilisateur_assoc_portable = '".preg_replace("/[^\d\+]/","",$_POST['user_assoc_portable'])."'";
               $sql .= ", utilisateur_assoc_email    = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['user_assoc_mail'])."'";
               $sql .= " WHERE utilisateur_assoc_id = ".$_POST['user_assoc_id'];
               
               $DB_site2->query($sql);
          }
		  
		  //Ajout d'une relation rattachée à une fiche
          if(isset($_POST['add_relation'])){
			   $erreur=0;
			   if($_POST['relation_type']=="utilisateur"){
				   if($_POST['user1id']!=""){
					   $_POST['user2id']=$_POST['id'];
				   } else {
					   $_POST['user1id']=$_POST['id'];
				   }
			   } elseif($_POST['relation_type']=="fournisseur"){
				   $_POST['fournisseur_societe']=$_POST['id'];
			   } else { // rien de sélectiopnné => erreur => on enregistre rien en BdD
				   $erreur=1;
			   }
               $sql = "INSERT INTO utilisateur_relation (";
               $sql .= "relation_type";
               $sql .= ", user1id";
               $sql .= ", user2id";
               $sql .= ", fournisseur_societe";
               $sql .= ",  relation_genre";
               $sql .= ", relation_description";
               $sql .= ") VALUES (";
               $sql .= "'" . mysqli_real_escape_string($DB_site2->link_id,$_POST['relation_type']) . "'";
			   $sql .= ", '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['user1id']) . "'";
               $sql .= ", '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['user2id']) . "'";
               $sql .= ", '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur_societe']) . "'";
               $sql .= ", '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['relation_genre']) . "'";
               $sql .= ", '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['relation_description']) . "'";
               $sql .= ")";
			   if($erreur==0){
					$DB_site2->query($sql);
			   }

               $txt ="[info_client] Modification relations client";
               $txt .="<br>ajout d'une relation :".$_POST['relation_type']." = ".$_POST['id'];
 
               //Insertion dans le suivi
               $sql = "INSERT INTO suivi_client SET";
               $sql .= " userid = '".$_GET['userid']."'";
               $sql .= ", commercial = '".$user_info['username']."'";
               $sql .= ", date = ".time();
               $sql .= ", suivi = '".mysqli_real_escape_string($DB_site2->link_id,$txt)."'";
               $sql .= ", etat = '-1'";
               $sql .= ", compte =0";
               $DB_site2->query($sql);
          }
		  
		  //Suppression d'une relation rattachée à une fiche
          if(isset($_POST['del_relation'])){
			if($_POST['relationid']!=""){
               $sql = "DELETE FROM utilisateur_relation WHERE relationid='".$_POST['relationid']."' ";
               $DB_site2->query($sql);

               $txt ="[info_client] Suppression relations client";
               $txt .="<br>suppression d'une relation : ".$_POST['relationid'];
 
               //Insertion dans le suivi
               $sql = "INSERT INTO suivi_client SET";
               $sql .= " userid = '".$_GET['userid']."'";
               $sql .= ", commercial = '".$user_info['username']."'";
               $sql .= ", date = ".time();
               $sql .= ", suivi = '".mysqli_real_escape_string($DB_site2->link_id,$txt)."'";
               $sql .= ", etat = '-1'";
               $sql .= ", compte =0";
               $DB_site2->query($sql);
			}

          }
		  
		  
		  //modification d'une relation rattachée à une fiche
          if(isset($_POST['edit_relation'])){
			if($_POST['relationid']!=""){
               $sql = "UPDATE utilisateur_relation SET relation_description='" . mysqli_real_escape_string($DB_site2->link_id,$_POST['relation_description']) . "' WHERE relationid='".$_POST['relationid']."' ";
               $DB_site2->query($sql);

               $txt ="[info_client] Modification relations client";
               $txt .="<br>modification d'une relation : ".$_POST['relationid']." description = ".$_POST['relation_description'];
 
               //Insertion dans le suivi
               $sql = "INSERT INTO suivi_client SET";
               $sql .= " userid = '".$_GET['userid']."'";
               $sql .= ", commercial = '".$user_info['username']."'";
               $sql .= ", date = ".time();
               $sql .= ", suivi = '".mysqli_real_escape_string($DB_site2->link_id,$txt)."'";
               $sql .= ", etat = '-1'";
               $sql .= ", compte =0";
               $DB_site2->query($sql);
			}
          }
		  
		  //Ajout d'une date de passage au showroom à une fiche
          if(isset($_POST['add_showroom'])){
			  $erreur=0;
			  if($_POST['date_showroom']==""){
				    $erreur=1;
			  } else {
				   $explode=explode("/", $_POST['date_showroom']);
				   $dateline=mktime(0,0,0,$explode[1],$explode[0],$explode[2]);
				   $sql = "INSERT INTO utilisateur_showroom (userid, showroomid, dateline) VALUES ('" . mysqli_real_escape_string($DB_site2->link_id,$_POST['userid']) . "', 1,  '".$dateline."') ";
				   $DB_site2->query($sql);
			  }
          }
		  
		  //Suppression d'une date de passage au showroom à une fiche
          if(isset($_POST['del_showroom'])){
			  if($_POST['utilisateur_showroomid']!=""){
				   $sql = "DELETE FROM utilisateur_showroom WHERE utilisateur_showroomid='".$_POST['utilisateur_showroomid']."' ";
				   $DB_site2->query($sql);
			  }
          }
		  
		  //Ajout d'un projet
          if(isset($_POST['addprojet'])){
				if($_POST['user_projet_echeance_livraison']!=""){
					$explode=explode("-", $_POST['user_projet_echeance_livraison']);
					$echeance_livraison=mktime(0,0,0,$explode[1],$explode[2],$explode[0]);
				} else {
					$echeance_livraison=0;
				}
				if($_POST['user_projet_dateline']!=""){
					$explode=explode("-", $_POST['user_projet_dateline']);
					$echeance_dateline=mktime(0,0,0,$explode[1],$explode[2],$explode[0]);
				} else {
					$echeance_dateline=time();
				}
				if($_POST['user_projet_sans_budget']=="1"){
					$budget=-1;
				} else {
					$budget=$_POST['user_projet_budget'];
				}
				$sql="INSERT INTO projet SET
						userid='".$_POST['userid']."',
						dateline='".$echeance_dateline."',
						commercial='".$_POST['user_projet_commercial']."',
						libelle='".addslashes($_POST['user_projet_libelle'])."',
						besoin='".addslashes($_POST['user_projet_besoin'])."',
						budget='".$budget."',
						etatid='".$_POST['user_projet_etatid']."',
						projet_typeid='".$_POST['user_projet_projet_type']."',
						utilisateur_type='".$_POST['user_projet_utilisateur_type']."',
						echeance_livraison='".$echeance_livraison."',
						facturid='".$_POST['user_projet_facturid']."' ";
				$DB_site2->query($sql);
		  }
		  //suppression d'un projet
          if(isset($_POST['delprojet'])){
			$sql="DELETE FROM projet WHERE projetid='".$_POST['projetid']."' ";
			$DB_site2->query($sql);
			$DB_site2->query("DELETE FROM projet_relation WHERE projetid='".$_POST['projetid']."' ");
			$DB_site2->query("UPDATE suivi_client SET projetid='0' WHERE projetid='".$_POST['projetid']."' ");
			$DB_site2->query("UPDATE mail_outbox SET projetid='0' WHERE projetid='".$_POST['projetid']."' ");
			$DB_site2->query("UPDATE mail_inbox SET projetid='0' WHERE projetid='".$_POST['projetid']."' ");
		  }
		  //Modification d'un projet
		  if(isset($_POST['editprojet'])){
			if($_POST['projetid']!=""){
				if($_POST['user_projet_echeance_livraison']!=""){
					$explode=explode("-", $_POST['user_projet_echeance_livraison']);
					$echeance_livraison=mktime(0,0,0,$explode[1],$explode[2],$explode[0]);
				} else {
					$echeance_livraison=0;
				}
				if($_POST['user_projet_dateline']!=""){
					$explode=explode("-", $_POST['user_projet_dateline']);
					$echeance_dateline=mktime(0,0,0,$explode[1],$explode[2],$explode[0]);
				} else {
					$echeance_dateline=time();
				}
				if($_POST['user_projet_sans_budget']=="1"){
					$budget=-1;
				} else {
					$budget=$_POST['user_projet_budget'];
				}
				$sql="UPDATE projet SET
						libelle='".addslashes($_POST['user_projet_libelle'])."',
						besoin='".addslashes($_POST['user_projet_besoin'])."',
						dateline='".$echeance_dateline."',
						commercial='".$_POST['user_projet_commercial']."',
						budget='".$budget."',
						etatid='".$_POST['user_projet_etatid']."',
						projet_typeid='".$_POST['user_projet_projet_type']."',
						utilisateur_type='".$_POST['user_projet_utilisateur_type']."',
						echeance_livraison='".$echeance_livraison."',
						facturid='".$_POST['user_projet_facturid']."'
						WHERE projetid='".$_POST['projetid']."' ";
				$DB_site2->query($sql);
				$projet_relation=$_POST['projet_relation'];
				if(is_array($projet_relation)){
					$projet_rels=$DB_site2->query("SELECT * FROM utilisateur_relation WHERE user1id='".$_POST['userid']."' OR user2id='".$_POST['userid']."' ");
					while($projet_rel=$DB_site2->fetch_array($projet_rels)){
						if($projet_rel[user1id]==$_POST['userid']){
							$userid_related=$projet_rel[user2id];
						} else {
							$userid_related=$projet_rel[user1id];
						}
						if(in_array($userid_related,$projet_relation)){
							$DB_site2->query("INSERT IGNORE INTO projet_relation (projetid, userid, dateline) VALUES ('".$_POST['projetid']."' , '".$userid_related."', '".time()."') ");
						} else {
							$DB_site2->query("DELETE FROM projet_relation WHERE projetid='".$_POST['projetid']."' AND userid='".$userid_related."' ");
						}
					}
				} else {
					echo "pas de tableau reçu";
					$DB_site2->query("DELETE FROM projet_relation WHERE projetid='".$_POST['projetid']."' ");
				}
			}
		  }
		  
		  
		  
          
          //Récupération des informations sur l'utilisateur
          $sql = "SELECT *";
          $sql .= " FROM utilisateur";
          $sql .= " WHERE userid = ".$_GET['userid'];
          $utilisateur = $DB_site2->query_first($sql);
          
          if($utilisateur['site_code'] == ""){
               $utilisateur['site_code']="base";
          }
          
          ?>
          <LINK href="cal.css" type="text/css" rel="stylesheet">
          <LINK href="filebox.css" type="text/css" rel="stylesheet">
          <script language="javascript" src="calendrier.js"></script>
          <script language="javascript" src="../javascripts/prototype.js"></script>
          <script language="javascript" src="filebox.js"></script>
          
          <script language="javascript">
               var userid = '<?php echo $_GET['userid']; ?>';
               var fb = new filebox('fb');
               fb.dataurl = 'user_files.php?userid='+userid+'&action=files';
               mail_pagepj('fb.showselect',['undefined',mail_addpjs]);

               initcalendrier(true);
               timemode=10;
               hoursrange=new Array(8,9,10,11,12,13,14,15,16,17,18,19,20);
               document.title = "<?php echo $utilisateur['raisonsociale']." ".$utilisateur['nom']." ".$utilisateur['prenom']; ?>";
               
               function toggle_div(div){
                    if(document.getElementById(div).style.display === 'block'){
                         document.getElementById(div).style.display = 'none';
                    } else {
                         document.getElementById(div).style.display = 'block';
                    }
               }
               
               // efface les articles de la liste
              function cleanSuivi(id, suiviid, mailid){
                  res = confirm("Effacer ce suivi ? ");
                  if(res == true){
                      ajaxrequest('ajax/delSuivi.php?id='+id+'&suiviid='+suiviid+'&mailid='+mailid,enditCallback);
                  }
              }

              // efface les articles de la liste
              function cleanSuiviMail(userid, mailid, sens){
                  res = confirm("Effacer ce suivi ? ");
                  if(res == true){
                      ajaxrequest('ajax/delSuivi.php?userid='+userid+'&mailid='+mailid+'&sens='+sens,enditCallback);
                  }
              }
              
              // retour a la page utilisateur
              function enditCallback(httpRequest){
                  /*var html = httpRequest.responseText;
                  var form = document.createElement('form');
                  form.setAttribute('method',"POST");
                  form.setAttribute('action',"utilisateur.php");
                
                  var inpt = document.createElement('input');
                  inpt.setAttribute('type',"text");
                  inpt.setAttribute('name',"txtcb");
                  inpt.setAttribute("value", html);
                
                  form.appendChild(inpt);
                
                  form.submit();
                
                  window.location.href = "utilisateur.php";
                  */
                  location.reload();
              }
               
          </script>
          <?php
          //**************************************** TSI ************************************************
          //************* Insertion panneau de recherche ********************************************
		  if($utilisateur !== false){
			  ?>
<ul class="liens_rapides">
	<li><a class="js-scrollTo" href="#utilisateur_fiche">Fiche</a></li>
	<li><a class="js-scrollTo" href="#utilisateur_propositions">Propositions</a></li>
	<li><a class="js-scrollTo" href="#utilisateur_projets">Projets</a></li>
	<li><a class="js-scrollTo" href="#utilisateur_configurateur">Configurateur</a></li>
	<li><a class="js-scrollTo" href="#utilisateur_comparateur">Comparateur</a></li>
	<li><a class="js-scrollTo" href="#utilisateur_articlesvus">Articles vus</a></li>
	<li><a class="js-scrollTo" href="#utilisateur_provenance">Provenance</a></li>
	<li><a class="js-scrollTo" href="#utilisateur_suivicommande">Suivi commande</a></li>
	<li><a class="js-scrollTo" href="#utilisateur_suiviclient">Suivi client</a></li>
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
		  }
          include('includes/contact_recherche.php');

          echo $fil_ariane;

          //Si l'utilisateur est trouvé, on affiche les informations
          if($utilisateur !== false){
          
                // TSI gestion de l'affichage suppression d'un suivi / mail pour SU et utilisateur_admin
                global $authDelSuivi;
                if(in_array($user_info['username'],$adminSU) && ($utilisateur['utilisateur_admin'] == 1 ))
                  $authDelSuivi = 1;
                else
                  $authDelSuivi = 0;


			   //Inclusion des fonctions spécifiques
               include('includes/utilisateur_fonction.php');

               //Inclusion encart "FICHE CLIENT"
               include('includes/utilisateur_fiche.php');

               //Inclusion encart "PROPOSITIONS"
//                include('includes/utilisateur_propositions.php');

			   //Inclusion encart "PROJETS"
               include('includes/utilisateur_projets.php');

               //Inclusion encart "CONFIGURATIONS"
               if(intval($utilisateur['utilisateur_admin']) == 0) include('includes/utilisateur_configurateur.php');

               //Inclusion encart "COMPARATEURS"
               if(intval($utilisateur['utilisateur_admin']) == 0) include('includes/utilisateur_comparateur.php');

               //Inclusion encart "ARTICLES VUS"
               if(intval($utilisateur['utilisateur_admin']) == 0) include('includes/utilisateur_articlesvus.php');//2025

               //Inclusion encart "PROVENANCE"
               if(intval($utilisateur['utilisateur_admin']) == 0) include('includes/utilisateur_provenance.php');

               //Inclusion encart "SUIVI COMMANDE"
               include('includes/utilisateur_suivicommande.php');

               //Inclusion encart "SUIVI CLIENT"
               // include('includes/utilisateur_suivi.php');

              var_dump('<pre>');
              var_dump($user_info);
              var_dump('</pre>');
               include('includes/utilisateur_suiviclient.php');

		 //TODO: create a TaskManager/TaskTemplate method
		 
		$si_query = $DB_site2->query('
		  SELECT `type`, `id`, `name`, `short_desc`, `excepted_date`
		  FROM `suivi_interne`
		  WHERE `link_type` LIKE "utilisateur"
		  AND `link` LIKE "'.mysqli_real_escape_string($DB_site2->link_id,$utilisateur['userid']).'"
		  ');
		
		if($DB_site2->num_rows($si_query)>0){
		    print "<div class=\"tab_special\">
		    <h2>Suivi interne</h2>
		    <div class=\"clear\"></div>";
		    
		    print '<table>';
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

               
		}else{
		  echo "<div>Cet utilisateur est introuvable, il a probablement été effacé<br><a href=\"javascript:history.back();\">Retour</a></div>";
		}
	}elseif( $action == "pages" )
		{
		$utilisateur=$DB_site2->query_first("SELECT * FROM utilisateur where userid = '$_GET[userid]'");
		echo "<h3>Client $utilisateur[nom] $utilisateur[prenom]</h3>
		<strong>+ de détails sur les pages</strong><br><br>
		<table class=\"table_moyen_paiement\">
			<tr>
				<td align=\"center\"><strong>Nom de la page</strong></td>
				<tD align=\"center\" colspan=\"2\"><strong>Nombre de vues</strong></td>
			</tr>
			<tr>
				<td align=\"center\"><strong></strong></td>
				<tD align=\"center\"><strong>Au total</strong></td>
				<tD align=\"center\"><strong>Ce mois</strong></td>
			</tr>" ;
		$user_pages_tot=$DB_site2->query("SELECT script, sum(visites) as visites FROM statistique_page_utilisateur 
											  where userid = '$_GET[userid]' group by script order by script asc");
		while ($user_page_tot=$DB_site2->fetch_array($user_pages_tot))
			{
			$anneemois = date ('Y-m') ;
			$user_page_mois=$DB_site2->query_first("SELECT sum(visites) as visites FROM statistique_page_utilisateur 
							where userid = '$_GET[userid]' and script = '$user_page_tot[script]' and datestatistique LIKE '%$anneemois-%'");
			echo "
				<tr>
					<td align=\"left\">&nbsp;&nbsp;&nbsp;$user_page_tot[script]</td>
					<tD align=\"center\">$user_page_tot[visites]</td>
					<td align=\"center\">$user_page_mois[visites]</td>
				</tr>" ;
			}
		echo "<tr>
				<td></td>
				<tD align=\"center\"></td>
				<td align=\"center\"></td>
			</tr>
		</table>
		<br><Br><a href=\"utilisateur.php?action=editer&userid=$_GET[userid]\">Retour</a>" ;
		}
	elseif( $action == "articles" )
		{
		$utilisateur=$DB_site2->query_first("SELECT * FROM utilisateur where userid = '$_GET[userid]'");
		echo "<h3>Client $utilisateur[nom] $utilisateur[prenom]</h3>
		<strong>+ de détails sur les articles</strong><br><br>
		<table class=\"table_coupscoeur\">
			<tr>
				<td align=\"center\"><strong>Article</strong></td>
				<tD align=\"center\" colspan=\"3\"><strong>Au total</strong></td>
				<tD align=\"center\">&nbsp;</td>
				<tD align=\"center\" colspan=\"3\"><strong>Ce mois</strong></td>
			</tr>
			<tr>
				<td align=\"center\"><strong></strong></td>
				<tD align=\"center\"><strong>Vues</strong></td>
				<tD align=\"center\"><strong>Panier</strong></td>
				<tD align=\"center\"><strong>Cdes</strong></td>
				<tD align=\"center\">&nbsp;</td>
				<tD align=\"center\"><strong>Vues</strong></td>
				<tD align=\"center\"><strong>Panier</strong></td>
				<tD align=\"center\"><strong>Cdes</strong></td>
			</tr>
			" ;
		$user_arts_tot=$DB_site2->query("SELECT libelleFR, spu.artid, sum(visites) as visites, sum(panier) as panier, sum(commandes) as commandes 
		FROM statistique_produit_utilisateur spu, article a where a.artid = spu.artid and userid = '$_GET[userid]' group by spu.artid order by spu.artid asc");
		while ($user_art_tot=$DB_site2->fetch_array($user_arts_tot))
			{
			$anneemois = date ('Y-m') ;
			$user_arts_mois=$DB_site2->query_first("SELECT sum(visites) as visites, sum(panier) as panier, sum(commandes) as commandes 
			FROM statistique_produit_utilisateur where userid = '$_GET[userid]' and artid = '$user_art_tot[artid]' and datestatistique LIKE '%$anneemois-%'");
			echo "
				<tr>
					<td align=\"left\" width=\"25%\">$user_art_tot[libelleFR]</td>
					<tD align=\"center\" class=\"td_promos_alt1\">$user_art_tot[visites]</td>
					<tD align=\"center\" class=\"td_promos_alt1\">$user_art_tot[panier]</td>
					<tD align=\"center\" class=\"td_promos_alt1\">$user_art_tot[commandes]</td>
					<tD align=\"center\" bgcolor=\"#5F718A\" width=\"1\"></td>
					<tD align=\"center\" class=\"td_promos_alt2\">$user_arts_mois[visites]</td>
					<tD align=\"center\" class=\"td_promos_alt2\">$user_arts_mois[panier]</td>
					<tD align=\"center\" class=\"td_promos_alt2\">$user_arts_mois[commandes]</td>
				</tr>" ;
			}
		echo "<tr>
				<td></td>
				<tD align=\"center\"></td>
				<td align=\"center\"></td>
			</tr>
		</table>
		<br><Br><a href=\"utilisateur.php?action=editer&userid=$_GET[userid]\">Retour</a>" ;
		}
	}


print "<script language=\"javascript\">
var maillist=new Array(\"\",\"Relance après création de compte\",\"Relance après tentative de passer commande\",\"Relance mail après 3 relances téléphoniques\",\"Relance mail après 3 relances téléphoniques (offre garantie 5 ans)\",\"Suite à votre intérêt pour nos produits\");
var mailsubject=new Array();
var mailbody=new Array();
mailsubject[0]=undefined;
mailbody[0]=undefined;
mailsubject[1]=\"Votre création de compte client sur $host\";
mailbody[1]=\"\";
mailsubject[2]=\"Votre tentative de passer commande sur $host\";
mailbody[2]=\"".str_replace("\n","","<p>Bonjour,</p>\n<p>Nous avons remarqué votre passage sur notre site <a href='https://$host'>$host</a> et votre tentative de passer commande d'une cave à vin. Nous souhaiterions savoir si vous avez rencontré des problèmes pour finaliser votre commande ou si vous avez besoin d'informations complémentaires sur ce produit.</p>\n<p>Merci d'avance pour votre réponse.</p>\n<p>Nous restons à votre disposition au <b>04 26 03 05 43</b> du lundi au vendredi de 8h à 20h et le samedi de 10h à 18h pour toute information complémentaire ou pour passer commande en direct.</p>\n<p style='color:navy;'>Nous vous remercions de l'intérêt que vous portez à nos produits. Nous restons à votre disposition pour toute information complémentaire.</p>")."\";
mailsubject[3]=\"Suite à donner à vos devis sur $host\";
mailbody[3]=\"".str_replace("\n","","<p>Bonjour,</p>\n<p>Suite aux devis que nous vous avons envoyés pour des caves à vin, nous souhaiterions savoir quelle suite donner à ces offres.</p>\n<p>Nous vous remercions par avance pour votre réponse.</p>\n<p>Nous restons à votre disposition au <b>04 26 03 05 43</b> du lundi au vendredi de 8h à 20h et le samedi de 10h à 18h pour toute information complémentaire ou pour passer commande en direct</p>\n<p style='color:navy;'>Nous vous remercions de l'intérêt que vous portez à nos produits. Nous restons à votre disposition pour toute information complémentaire.</p>")."\";
mailsubject[4]=\"Suite à donner à vos devis sur $host\";
mailbody[4]=\"".str_replace("\n","","<p>Bonjour,</p>\n<p>Suite aux devis que nous vous avons envoyés pour des caves à vin, nous souhaiterions savoir quelle suite donner à ces offres. Nous vous proposons également de <b>vous offrir la garantie 5 ans pièces, main d'oeuvre et déplacement pour tous vos devis</b>. Pour bénéficier de cette offre vous pouvez nous contacter par téléphone au <b>04 26 03 05 43</b> ou répondre à cet E-mail si vous souhaitez que je vous rappelle.</p>\n<p>Nous vous remercions par avance pour votre réponse.</p>\n<p>Nous restons à votre disposition au <b>04 26 03 05 43</b> du lundi au vendredi de 8h à 20h et le samedi de 10h à 18h pour toute information complémentaire ou pour passer commande en direct</p>\n<p style='color:navy;'>Nous vous remercions de l'intérêt que vous portez à nos produits. Nous restons à votre disposition pour toute information complémentaire.</p>")."\";
mailsubject[5]=\"Suite à votre intérêt pour nos produits\";
mailbody[5]=\"".str_replace("\n","","<p>Bonjour,</p>\n<p>Concernant votre demande pour l'achat de <i>[article]</i>, notre équipe est à votre écoute au <b>04 26 03 05 43</b> du lundi au vendredi de 8h à 20h et le samedi de 10h à 18h pour toute information complémentaire ou pour passer commande en direct.</p>")."\";
</script>";

include "./footer.php";





?>