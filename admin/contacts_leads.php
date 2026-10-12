<?php

include("admin_global.php");

if(isset($_GET['traite'])) $DB_site2->query("UPDATE contact_leads SET commercial='".$user_info['username']."', date_reponse=".time()." WHERE id=".$_GET['contactid']);

if(isset($_GET['commercial'])) $DB_site2->query("UPDATE contact_leads SET commercial='".$_GET['commercial']."' WHERE id=".intval($_GET['contactid']));

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

echo "<h1>Demandes de contact campagne</h1>";

if(isset($_GET['contactid'])){
    $contact=$DB_site2->query_first("SELECT cl.*, lu.utm_campagne, lu.utm_source AS 'source' FROM contact_leads cl INNER JOIN leads_utm lu ON cl.utm_source = lu.id WHERE cl.id=".$_GET['contactid']);
    if(isset($_POST['contactsuivitype'])) {
	$sql="";
	$txt="Message formulaire campagne :<br>Message : ".nl2br($contact['message'])."<br>Source : ".$contact['source']."<br>Campagne : ".$contact['utm_campagne'];
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

    $flag="";
    if($contact['langue']!="") $flag=" &nbsp;<img src=\"images/drapeaux/".$contact['langue'].".gif\" width=\"20\">";
    
    $useropt="<option value=\"\">personne</option>";
    $users=$DB_site2->query("SELECT username FROM parametresgeneraux WHERE actif=1 ORDER BY username");
    while($user=$DB_site2->fetch_array($users))
		$useropt.="<option value=\"".$user['username']."\"".($contact['commercial']==$user['username']?" selected":"").">".$user['username']."</option>";

	print "<p>Attribué à <select onchange=\"document.location='contacts_leads.php?contactid=".$_GET['contactid']."&commercial='+this.value\">$useropt</select></p>";
    
    print "<table style=\"width:100%;\">
    <tr style=\"background-color:#DDDDDD;color:black;\"><td style=\"width:450px;\"></td><td>".date("d/m/Y H:i",$contact['date'])."</td></tr>
    <tr><td colspan=\"2\" style=\"background-color:#DDDDDD;font-weight:bold;\">Coordonnées</td></tr>
    <tr><td>".($contact['societe']!=""?$contact['societe']."<br>":"").$contact['nom']." ".$contact['prenom'].$flag."<br>".$contact['mail']."<br>".epigy($contact['telephone']).($contact['daterappel']!="" || $contact['heurerappel']!=""?"(".$contact['daterappel']." ".$contact['heurerappel'].")":"")."<br><a href=\"javascript:mail_auto(undefined,'".$contact['mail']."',undefined,'Re:Formulaire de Contact');\">".$contact['mail']."</a></td></tr>
    <tr><td colspan=\"2\" style=\"background-color:#DDDDDD;font-weight:bold;\">Message</td></tr>
    <tr><td colspan=\"2\">".nl2br($contact['message'])."<br><br>Connu par : ".$contact['source']." avec la campagne ".$contact['utm_campagne']."</td></tr>
    </table>";


	$suivi="Associer à : <input type=\"text\" id=\"src\" onkeyup=\"assocsrc(this.value)\" autocomplete=\"off\"><input type=\"button\" id=\"submitsrc\" value=\"OK\" onclick=\"document.forms.suiviform.submit()\">
	    <div id=\"assocresult\"></div>";
 
    
    if($contact['date_reponse']==0)
	$valid="<a href=\"contacts_leads.php?contactid=".$_GET['contactid']."&traite=1\" style=\"float:right;\">Marquer cette demande traitée <img src=\"images/coche_verte.svg\" width=\"20\"></a>";
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
    $contacts=$DB_site2->query("SELECT * FROM contact_leads WHERE 1 ORDER BY date DESC");
    print "<table class=\"tableau_contacts\">
    <tr style=\"background-color:#DDDDDD;color:black;\">
	<td>Date demande</td>
	<td>Nom société</td>
	<td>Nom / prénom</td>
	<td>Message</td>
	<td>Attribué</td>
	<td>Traité</td>
    </tr>";
    while($contact=$DB_site2->fetch_array($contacts)){
	print "<tr>
		<td>".date("d/m/Y H:i",$contact['date'])."</td>
		<td>".$contact['societe']."</td>
		<td>".$contact['nom']." ".$contact['prenom']."</td>
		<td style=\"width:400px;\"><a href=\"contacts_leads.php?contactid=".$contact['id']."\">".substr($contact['message'],0,40)."...</a></td>
		<td>".$contact['commercial']."</td>
		<td>".($contact['date_reponse']>0?date("d/m/Y",$contact['date_reponse']):"Non traité")."</td>
	      </tr>";
    }
    print "</table>";
}



print "<script language=\"javascript\">
function assocsrc(src){
    document.getElementById('assocresult').innerHTML='';
    if(src.length<4) return;
    ajaxrequest('contacts_leads.php?src='+src,src_cb);
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