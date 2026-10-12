<?php

include("admin_global.php");

if(isset($_GET['traite'])) $DB_site2->query("UPDATE contact SET commercial='".$user_info['username']."', date_reponse=".time()." WHERE id=".$_GET['contactid']);

if(isset($_GET['commercial'])) $DB_site2->query("UPDATE contact SET commercial='".$_GET['commercial']."' WHERE id=".intval($_GET['contactid']));

if(isset($_GET['src'])){
  $users=$DB_site2->query("SELECT nom,prenom,userid FROM utilisateur WHERE nom LIKE '%".$_GET['src']."%' OR prenom LIKE '%".$_GET['src']."%' OR raisonsociale LIKE '%".$_GET['src']."%' OR userid ='".$_GET['src']."' ORDER BY nom");
  while($u=$DB_site2->fetch_array($users)){
      print "<a href=\"javascript:selectassoc('client',".$u['userid'].")\">".utf8_encode($u['nom']." ".$u['prenom'])."<br><small>Compte ".$u['userid']."</small></a>";
  }
  $factures=$DB_site2->query("SELECT nom,prenom,facturid FROM facture WHERE facturid=".intval($_GET['src'])." OR nom LIKE '%".$_GET['src']."%' OR prenom LIKE '%".$_GET['src']."%' OR raisonsociale LIKE '%".$_GET['src']."%' ORDER BY nom");
  while($f=$DB_site2->fetch_array($factures)){
      print "<a href=\"javascript:selectassoc('commande',".$f['facturid'].")\">".utf8_encode($f['nom']." ".$f['prenom'])."<br><small>Commande ".$f['facturid']."</small></a>";
  }
  exit;
}

include "./header.php";
include "./menu.php";

echo "<h1>Demandes de contact</h1>";

if(isset($_GET['contactid'])){
    $contact=$DB_site2->query_first("SELECT * FROM contact WHERE id=".$_GET['contactid']);
    if(isset($_POST['contactsuivitype'])) {
	$sql="";
	$txt="Demande de contact :<br>Sujet: ".nl2br($contact['sujet'])."<br>Message :<br>".nl2br($contact['message']);
	switch($_POST['contactsuivitype']){
	    case "commande":
	    $sql="INSERT INTO suivi_commande SET type='client', destinataire=".intval($_POST['userid']).", moyen='contact', facturid=".$_POST['facturid'].", date=".$contact['date'].", commercial='".$user_info['username']."', suivi='".addslashes($txt)."'";
	    break;
	    
	    case "client":
	    $sql="INSERT INTO suivi_client SET type='client', destinataire=".intval($_POST['userid']).", moyen='contact', userid=".intval($_POST['userid']).", date=".$contact['date'].", commercial='".$user_info['username']."', suivi='".addslashes($txt)."', etat=1";
	    break;
	}
	if($sql!="") $DB_site2->query($sql);
    }
    
    if($contact['sujet']=="") $contact['sujet']="(vide)";

    $flag="";
    if($contact['langue']!="") $flag=" &nbsp;<img src=\"images/drapeaux/".$contact['langue'].".gif\" width=\"20\">";
    
    $useropt="<option value=\"\">personne</option>";
    $users=$DB_site2->query("SELECT username FROM parametresgeneraux WHERE actif=1 ORDER BY username");
    while($user=$DB_site2->fetch_array($users))
		$useropt.="<option value=\"".$user['username']."\"".($contact['commercial']==$user['username']?" selected":"").">".$user['username']."</option>";
    
    print "<p>Attribué à <select onchange=\"document.location='contacts.php?contactid=".$_GET['contactid']."&commercial='+this.value\">$useropt</select></p>";
    
    print "<table style=\"width:100%;\">
    <tr style=\"background-color:#DDDDDD;color:black;\"><td style=\"width:450px;\">".$contact['sujet']."</td><td>".date("d/m/Y H:i",$contact['date'])."</td></tr>
    <tr><td colspan=\"2\" style=\"background-color:#DDDDDD;font-weight:bold;\">Coordonnées</td></tr>
    <tr><td>".($contact['raisonsociale']!=""?$contact['raisonsociale']."<br>":"").$contact['nom']." ".$contact['prenom'].$flag."<br>".$contact['adresse']."<br>".$contact['cp']." ".$contact['ville']."<br>".$contact['pays']."<br>".epigy($contact['telephone']).($contact['daterappel']!="" || $contact['heurerappel']!=""?"(".$contact['daterappel']." ".$contact['heurerappel'].")":"")."<br><a href=\"javascript:mail_auto(undefined,'".$contact['mail']."',undefined,'Re:".$contact['sujet']."');\">".$contact['mail']."</a></td></tr>
    <tr><td colspan=\"2\" style=\"background-color:#DDDDDD;font-weight:bold;\">Message</td></tr>
    <tr><td colspan=\"2\">".nl2br($contact['message'])."<br><br>Connu par :".($contact['connu'] != '' ? $contact['connu'] : 'Non renseignée')."</td></tr>
    </table>";
    
    if($contact['userid']!=0){
	$ajoute=$DB_site2->query_first("SELECT userid FROM suivi_client WHERE userid=".$contact['userid']." AND moyen='contact' AND date=".$contact['date']);
	$suiviclient="<br><a href=\"javascript:addsuivi('client')\">Ajouter au suivi du client ".$contact['userid']."</a>";
	if($ajoute!="") $suiviclient="<br>Ajouté au suivi client ".$contact['userid']." <img src=\"images/coche_verte.svg\" width=\"20\">";
	
	$suivi=$suiviclient;
	$factures=$DB_site2->query("SELECT facturid,heurefacture FROM facture WHERE userid=".$contact['userid']." AND valide>0 AND supprimee='0' ORDER BY facturid");
	if($DB_site2->num_rows($factures)>0){
	    $suivi.="<br>Ajouter à la commande <select onchange=\"document.getElementsByName('facturid')[0].value=this.value\"><option></option>";
	    while($facture=$DB_site2->fetch_array($factures)){
		$ajoute=$DB_site2->query_first("SELECT facturid FROM suivi_commande WHERE facturid=".$facture['facturid']." AND moyen='contact' AND date=".$contact['date']);
		if($ajoute=="") $suivi.="<option value=\"".$facture['facturid']."\">".$facture['facturid']." du ".date("d/m/Y",$facture['heurefacture'])."</option>";
	    }
	    $suivi.="</select> <input type=\"button\" value=\"OK\" onclick=\"addsuivi('commande')\">";
	}
    }else{
	$suivi="Associer à : <input type=\"text\" id=\"src\" onkeyup=\"assocsrc(this.value)\" autocomplete=\"off\"><input type=\"button\" id=\"submitsrc\" value=\"OK\" onclick=\"document.forms.suiviform.submit()\">
	    <div id=\"assocresult\"></div>";
    }
    
    if($contact['date_reponse']==0)
	$valid="<a href=\"contacts.php?contactid=".$_GET['contactid']."&traite=1\" style=\"float:right;\">Marquer cette demande traitée <img src=\"images/coche_verte.svg\" width=\"20\"></a>";
    else
	$valid="<span style=\"float:right;\">Contact effectué par ".$contact['commercial']." le ".date("d/m/Y",$contact['date_reponse'])."</span>";
	
    print "<div style=\"background-color:#DDD; margin-top:10px;vertical-align:center;\">
	   <form name=\"suiviform\" method=\"post\" style=\"float:left;\">
	    <input type=\"hidden\" name=\"contactsuivitype\">
	    <input type=\"hidden\" name=\"userid\" value=\"".$contact['userid']."\">
	    <input type=\"hidden\" name=\"facturid\">"
	    .$suivi."
	    </form>
	    $valid
	    <div class=\"clear\"></div>
	  </div>";
    
}else{
    $contacts=$DB_site2->query("SELECT * FROM contact WHERE 1 ORDER BY date DESC");
    print "<table class=\"tableau_contact\">
    <tr style=\"background-color:#DDDDDD;color:black;\">
	<td>Date demande</td>
	<td>Sujet</td>
	<td>Nom société</td>
	<td>Attribué</td>
	<td>Traité</td>
    </tr>";
    while($contact=$DB_site2->fetch_array($contacts)){
	if($contact['sujet']=="") $contact['sujet']="(vide)";
	print "<tr>
		<td>".date("d/m/Y H:i",$contact['date'])."</td>
		<td style=\"width:400px;\"><a href=\"contacts.php?contactid=".$contact['id']."\">".$contact['sujet']."</a></td>
		<td>".$contact['raisonsociale']." ".$contact['nom']."</td>
		<td>".$contact['commercial']."</td>
		<td>".($contact['date_reponse']>0?date("d/m/Y",$contact['date_reponse']):"Non traité")."</td>
	      </tr>";
    }
    print "</table>";
}

print "<script language=\"javascript\">
function addsuivi(type) {
    document.getElementsByName('contactsuivitype')[0].value=type;
    document.forms.suiviform.submit();
}

function assocsrc(src){
    document.getElementById('assocresult').innerHTML='';
    if(src.length<4) return;
    ajaxrequest('contacts.php?src='+src,src_cb);
}

function src_cb(httprequest){
    document.getElementById('assocresult').innerHTML=httprequest.responseText;
}

function selectassoc(type,id) {
    document.getElementsByName('contactsuivitype')[0].value=type;
    if(type=='client') document.getElementsByName('userid')[0].value=id;
    if(type=='commande') document.getElementsByName('facturid')[0].value=id;
    document.getElementById('src').value=type+' '+id;
    document.getElementById('submitsrc').style.display='inline';
    document.getElementById('assocresult').innerHTML='';
}
</script>";

print "<style>
#assocresult { position:absolute; background:#CCC; }
#assocresult a { display:block; padding:2px; }
#assocresult a:hover { background:white; }
#submitsrc { display:none; }
</style>";

include "./footer.php";

?>