<?php

include("admin_global.php");

if(isset($_GET['del'])){
  $mail=$DB_site2->query_first("SELECT `to` FROM mail_inbox WHERE id=".intval($_GET['del']));
  $DB_site2->query("UPDATE mail_inbox SET del=1 WHERE id=".intval($_GET['del']));
  exit;
}

print "<style>
.divlist { height:50%; overflow:auto; }
.inbox { width:100%; font-size:12px; border-collapse:collapse; border:solid thin #AAAAAA; }
.inbox thead { background-color:#AAAAAA; border-bottom:solid thin #AAAAAA; }
.head { background-color:white; border-bottom:solid thin #AAAAAA; cursor:pointer; }
.tdbody { width:100%; height:50%; border:solid thin #AAAAAA; }
.separation { width:100%; height:5px; background-color:#AAAAAA; }
</style>";

print "<script language=\"javascript\" src=\"/javascripts/ajax.js\"></script>
<script language=\"javascript\">
function openbody(id){
    document.getElementById('tdbody').src='inbox_body.php?id='+id;
    var trs=document.getElementsByTagName('tr');
    for(i=0;i<trs.length;i++){
	if(trs[i].id.substring(0,5)=='thead') trs[i].style.backgroundColor='white';
    }
    document.getElementById('thead'+id).style.backgroundColor='#DDDDDD';
}
function delmsg(id){
    ajaxrequest('inbox.php?del='+id,function(){});
    document.getElementById('tdbody').src='';
    document.getElementById('thead'+id).style.display='none';
}
</script>";

print "<div class=\"divlist\">
<table class=\"inbox\">
<thead>
<tr><td>Date</td><td>De</td><td>Sujet</td><td>Ajouter à</td></tr>
</thead>";
if(!isset($usermail)) $usermail=$user_info[mail_contact];
$requete=$DB_site2->query("SELECT * FROM mail_inbox WHERE `to`='".$usermail."' AND suivitype='' AND del=0 ORDER BY id DESC");
while($m=$DB_site2->fetch_array($requete)){
      $action="<img src=\"images/supprimer.gif\" onclick=\"delmsg($m[id])\">";
      $from=finduser($m['from']);
      $findcmd=findcmd($m['subject']);
      if(count($findcmd)==0) $findcmd=findcmd($m['body']);
      if(count($findcmd)<=2) foreach($findcmd as $facturid) $action.="<br>cmd ".$facturid;
      $emails=findemails($m['body']);
      foreach($emails as $email){
	  $contact=finduser($email);
	  if($contact['name']!=$email && $contact['id']!="") $action.="<br>".$contact['name'];
      }
      print "<tr id=\"thead$m[id]\" class=\"head\"><td onclick=\"openbody($m[id]);\">".date("d/m/Y H:i",$m['date'])."</td><td title=\"".$m['from']."\" onclick=\"openbody($m[id]);\">".$from['name']."</td><td onclick=\"openbody($m[id]);\">".$m['subject']."</td><td>".$action."</td></tr>";
}
print "</table>
</div>
<div class=\"separation\"></div>
<iframe id=\"tdbody\" class=\"tdbody\"></iframe>";


function finduser($email){
    global $DB_site2;
    if(strpos($email,"@ma-cave-a-vin.fr")>-1){
	$user=$DB_site2->query_first("SELECT nom_contact FROM parametresgeneraux WHERE mail_contact='".$email."'");
	if($user[0]!="")
	    $ret['name']=$user[0];
	else
	    $ret['name']=$email;
	return $ret;
    }

    $utilisateur=$DB_site2->query_first("SELECT * FROM utilisateur WHERE mail='".$email."'");
    if($utilisateur['mail']==$email){
	$ret['name']=trim($utilisateur['raisonsociale']." ".$utilisateur['prenom']." ".$utilisateur['nom']);
	$ret['id']=$utilisateur['userid'];
	/*$requete=$DB_site2->query("SELECT * FROM facture WHERE userid=".$utilisateur['userid']." ORDER BY heurefacture");
	if($DB_site2->num_rows($requete)>0){
	    $assoc="<select name=\"facturid\">";
	    while($facture=$DB_site2->fetch_array($requete))
		$assoc.="<option>".$facture['facturid']."</option>";
	    $assoc.="</select>";
	}*/
	$ret['action']=trim($utilisateur['raisonsociale']." ".$utilisateur['prenom']." ".$utilisateur['nom']);
	return $ret;
    }
    $fournisseur=$DB_site2->query_first("SELECT * FROM fournisseur WHERE mail='".$email."'");
    if($fournisseur['mail']==$email){
	$ret['name']=$fournisseur['societe']." ".$fournisseur['prenom']." ".$fournisseur['nom'];
	return $ret;
    }
    $facture=$DB_site2->query_first("SELECT * FROM facture WHERE mail='".$email."' ORDER BY heurefacture DESC");
    if($facture['mail']==$email){
	$ret['name']=trim($facture['societe']." ".$facture['prenom']." ".$facture['nom']);
	return $ret;
    }
    /*$mcav=$DB_site2->query_first("SELECT mail_contact,nom_contact FROM parametresgeneraux WHERE mail_contact='".$email."'");
    if($mcav['mail_contact']==$email){
	$ret['name']=$mcav['nom_contact'];
	return $ret;
    }*/

    return array("name"=>$email);
}

function findemails($txt){
    preg_match("/[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}/",$txt,$matches);
    return $matches;
}

function findcmd($txt){
    preg_match_all("/[^0-9]([3|4|5|6]{1}[0-9]{4})[^0-9]/",$txt,$matches);
    return array_unique($matches[1]);
}

?>