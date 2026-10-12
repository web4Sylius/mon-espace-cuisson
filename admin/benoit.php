<?php

require_once "admin_global.php";
require_once "taskmanager/taskReminder.class.php";

if($user_rappel==null) $user_rappel=$user_info['username'];

$ts=mktime()+60;
$from=mktime(0,0,0);
$to=mktime(23,59,59);
$alertts=mktime(date("H"),date("i"),0);

if(isset($_GET['hide2'])) $DB_site2->query("UPDATE suivi_client SET hide=1 WHERE id=".$_GET['hide2']);

if(isset($_GET['hide3'])) $DB_site2->query("UPDATE crm_calls SET hide=1 WHERE id='".$_GET['hide3']."'");

if(isset($_GET['hide6'])) $DB_site2->query("UPDATE suivi_interne_log SET hide=1 WHERE id='".$_GET['hide6']."'");

$rappels = $rappels3 = array();
$alert   = "";
$alertid = 0;

//Tableau qui va servir à trier les suivis par date ET par demi-journée (AM/PM)
$joursh = array(
    1 => 9,
    2 => 14
);


/*
$sql = "SELECT MAX(sc.id) as id, sc.type, sc.moyen, sc.destinataire, sc.userid, sc.commercial,
			sc.date, sc.suivi, sc.rappel, sc.rappeltype, sc.jour,
			sc.etat, sc.pour, sc.hide, sc.projetid, u.nom, u.prenom, u.utilisateur_type_suivi_id";
$sql .= " FROM suivi_client sc";
$sql .= " INNER JOIN utilisateur u USING(userid)";
$sql .= "GROUP BY userid ";
*/


/*
$sql = "SELECT * FROM (SELECT sc.id as id, sc.type, sc.moyen, sc.destinataire, sc.userid, sc.commercial,
			sc.date, sc.suivi, sc.rappel, sc.rappeltype, sc.jour,
			sc.etat, sc.pour, sc.hide, sc.projetid, u.nom, u.prenom, u.utilisateur_type_suivi_id";
$sql .= " FROM suivi_client sc";
$sql .= " INNER JOIN utilisateur u USING(userid) ORDER BY userid, id) ";
$sql .= "GROUP BY sc.userid ";
*/

// $sql = "SELECT * FROM suivi_client sc1 LEFT JOIN suivi_client sc2 ON sc1.userid = sc2.userid AND sc1.id < sc2.id WHERE sc1.id is null ";

$sql = "SELECT MAX(scs.id) AS id FROM suivi_client scs WHERE rappel>0 GROUP BY userid ";




// echo $sql;

$requete = $DB_site2->query($sql);




while($s = $DB_site2->fetch_array($requete)){
	$r = $DB_site2->query_first("SELECT * FROM suivi_client INNER JOIN utilisateur u USING(userid) WHERE id='".$s['id']."' ");
	$perdu = $DB_site2->query_first("SELECT * FROM suivi_client WHERE userid='".$r['userid']."' ORDER BY date DESC LIMIT 1 ");
	if($perdu['etat']==0 || $perdu['etat']==5){
	//	echo $r['userid']." => etat = 0<br>";
		continue;
	}
	if($r['hide']!=0 && $r['rappel']>$to   ){
	//	echo $r['userid']." => caché et pas du jour<br>";
		continue;
	}
	/*
	if($r['rappel']==0){
		echo $r['userid']." => rappel = 0<br>";
		continue;
	}
	*/
	if($r['rappel']>$to){
	//	echo $r['userid']." => rappel > time<br>";
		continue;
	}
	if($r['pour']!=$user_rappel && $perdu['pour']!=$user_rappel){
	//	echo $r['userid']." => user != ".$user_rappel."<br>";
		continue;
	}
	/*
	if($r['hide']!=0){
		echo $r['id']." => hide != 0<br>";
		continue;
	}
	*/
	if($r['nom'] == '') $r['nom'] = $r['userid'];
	if($r['utilisateur_type_suivi_id'] == 3 || $r['utilisateur_type_suivi_id'] == 4) $nom = "<b>".substr($r['nom'],0,25)."</b>";
	else $nom = substr($r['nom'],0,25);
	if($r['jour'] != 0) $r['rappel'] += $joursh[$r['jour']] * 3600;

	$datetxt = rappel_heure($r['rappel'],$r['jour'],false,false).":";

	$rappels[$r['rappel']] .= "<p class=\"type".$r['hide']." ".rappeltype_class($r['rappeltype'])."\">"
	 .($r['hide']==0?"<span class=\"rappelhide\" onclick=\"rappelhide($r[id],2)\">X</span>":"")
	 .$datetxt."<a href=\"utilisateur.php?action=editer&userid=$r[userid]\" onmouseover=\"document.getElementById('rappel2$r[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('rappel2$r[id]').style.visibility='hidden';\">".$nom."</a>
	 ".($r['rappeltype']!=""?sprintf("(%s)",substr(rappeltype_txt($r['rappeltype']),0,1)):"")."
	</p>
	<div id=\"rappel2$r[id]\" class=\"popup\">".stripslashes($r[suivi])."</div>";

	if($r['hide']==0 && $r['rappel']==$alertts){
	 $alert=date("H:i",$r['rappel'])." : <a href=\"utilisateur.php?userid=".$r['userid']."&action=editer\" onclick=\"rappelhide($r[id],2)\" target=\"_blank\">Client ".$r['nom']." ".$r['prenom']."</a><br>";
	 $alertid=$r['id'];
	}
}


$requete=$DB_site2->query("SELECT * FROM crm_calls WHERE assigned_user_id='$user_rappel' AND reminder_time>0 AND reminder_time<$ts AND hide=0");
while($r=$DB_site2->fetch_array($requete)){
    $account=$DB_site2->query_first("SELECT * FROM crm_accounts WHERE id='$r[parent_id]'");
    $name=$account['name'];
    if($parent['id']=""){
      $parent=$DB_site2->query_first("SELECT * FROM crm_contacts WHERE id='$r[parent_id]'");
      $account=$DB_site2->query_first("SELECT * FROM accounts a INNER JOIN crm_accounts_contacts ac ON (a.id=ac.account_id) WHERE ac.contact_id='$parent[id]'");
      $name=$account['name']." ".$parent['first_name']." ".$parent['last_name'];
    }
    $rappels[$r['reminder_time']].="<p class=\"type0\"><span class=\"rappelhide\" onclick=\"rappelhide('$r[id]',3)\">X</span>".date("H:i",$r['reminder_time']).":<a href=\"crm.php?action=detail&id=$account[id]\" onmouseover=\"document.getElementById('rappel3$r[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('rappel3$r[id]').style.visibility='hidden';\">".substr("CRM ".$name,0,20)."</a></p><div id=\"rappel3$r[id]\" class=\"popup\">".stripslashes($r[description])."</div>";
}

$requete=$DB_site2->query("SELECT * FROM suivi_interne_log WHERE pour='$user_rappel' AND rappel>0 AND rappel<$to AND (hide=0 OR rappel BETWEEN $from AND $to)");
while($r=$DB_site2->fetch_array($requete))
  $rappels3[$r['rappel']].=
    "<p class=\"type".$r['hide']."\">"
      .($r['hide']==0?"<span class=\"rappelhide\" onclick=\"rappelhide($r[id],6)\">X</span>":"")
      .date("H:i",$r['rappel'])
      .":<a href=\"task.php?a=showTask&amp;task=$r[task_id]\" onmouseover=\"document.getElementById('rappel$r[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('rappel$r[id]').style.visibility='hidden';\">".substr("SI# ".$r[task_id],0,20)."</a>
    </p><div id=\"rappel$r[id]\" class=\"popup\">".stripslashes($r[message])."</div>";
  
    
$contacts=$contacts2=array();
$sqlcom=($aaccess==false || in_array("suivi_commercial.php",$aaccess)?"commercial='' OR":"");
$sql="SELECT * FROM contact WHERE ($sqlcom commercial='$user_rappel') AND date_reponse=0 ORDER BY date";
$requete=$DB_site2->query($sql);
if($DB_site2->num_rows($requete)>0){
    
    while($contact=$DB_site2->fetch_array($requete)){
  if($contact['nom']=="") $contact['nom']=$contact['telephone'];
  $link=date("d/m H:i",$contact['date']).":<a href=\"contacts.php?contactid=".$contact['id']."\">".$contact['nom']."</a>";

  if($contact['sujet']=="Rappel immédiat")
    $contacts2[]=$link;
  else
    $contacts[]=$link;
    }
  // RAPPELS IMMEDIATS //
    if(count($contacts2)>0)
      print "<div class=\"rappel\" style=\"border-width:2px; border-color:#AA0000;\">
      <div class=\"rappeltitle\" style=\"text-decoration:blink;font-size:15px;\">Rappels imm&eacute;diats</div>
      <div style=\"background-color:#FFCCCC;\">".implode("<br>",$contacts2)."</div>
      </div>";
  // DEMANDE CONTACT //
    if(count($contacts)>0)
      print "<div class=\"rappel\">
      <div class=\"contacttitle\">".ucfirst($template->translate("/admin/menu/demande_contact"))."</div>
      <div class=\"\">".implode("<br>",$contacts)."</div>
      </div>";
}

//SELECT count(*),commercial FROM utilisateur u where (select etat from suivi_client where userid=u.userid order by date desc limit 1)=1 and (select valide from facture where userid=u.userid and supprimee='0' order by heurefacture desc limit 1)=0 group by commercial

//**************************//
// RAPPELS CLIENTS DU JOUR //
//************************//
ksort($rappels);
if(count($rappels) > 0){ 
     echo ""
     . "<div id=\"autorappel\" class=\"rappel\">"
          . "<div class=\"rappeltitle\">".ucfirst($template->translate("/admin/menu/rappels"))." clients</div>"
          . "<div id=\"rappeltxt\" class=\"rappeltxt\">";
}
$lastd="";
foreach($rappels as $date => $txt){
     if(date("d-m",$date) != $lastd) print "<p class=\"day\">".date("d/m/Y",$date)."</p>";
     echo $txt;
     $lastd = date("d-m",$date);
}
echo ""
     . "</div>"
. "</div>";

//********************************//
// RAPPELS SUIVI INTERNE DU JOUR //
//******************************//
ksort($rappels3);

if(count($rappels3)>0) print "<div id=\"autorappel\" class=\"rappel\">
<div class=\"rappeltitle\" style=\"background-color:#00b\">".ucfirst($template->translate("/admin/menu/rappels"))." suivi interne</div>
<div id=\"rappeltxt\" class=\"rappeltxt\">";
foreach($rappels3 as $date=>$txt){
  if(date("d",$date)!=$lastd) print "<p class=\"day\">".date("d/m/Y",$date)."</p>";
  print $txt;
  $lastd=date("d",$date);
}
print "</div>
</div>";


if($alertid>0)
  $alert="<p class=\"title\"><img src=\"../images/templates/warning.jpg\"> Rappel</p>".
  $alert.
  "<p><a href=\"javascript:rappelhide($alertid,2);alerthide();\">Supprimer le rappel</a> <!--<a href=\"\">D&eacute;caler le rappel</a>--></p>";

print "</div>
</div>
<div id=\"alertbg\"></div>
<div id=\"alert\">$alert</div>";

//**********************//
// INSCRIPTION DU JOUR //
//********************//
if($aaccess==false || in_array("suivi_commercial.php",$aaccess)){
    $u=array();
    $today=new datetime();
    $today->settime(0,0,0);
    $requete=$DB_site2->query("	SELECT u.nom,u.prenom,u.dateinscription,u.userid FROM utilisateur u
								WHERE 
									(select count(facturid) from facture where valide in (1,5,7,8) and userid=u.userid)=0
									and (select count(id) from suivi_client where userid=u.userid and commercial<>'')=0
									AND UNIX_TIMESTAMP(dateinscription)>".(time()-2592000)."
									AND conseils='oui'
									order by u.userid desc LIMIT 20");
    if($DB_site2->num_rows($requete)>0){
  while($utilisateur=$DB_site2->fetch_array($requete)){
      $d=new datetime($utilisateur['dateinscription']);
      $ddate=$d<$today ? $d->format("d/m") : $d->format("H:i");
      $u[]=$ddate.":<a href=\"utilisateur.php?action=editer&userid=".$utilisateur['userid']."\">".$utilisateur['prenom']." ".$utilisateur['nom']."</a>";
  }
  print "<div class=\"rappel\">
  <div class=\"registertitle\">".ucfirst($template->translate("/admin/menu/inscriptions"))."</div>
  <div class=\"\">".implode("<br>",$u)."</div>
  </div>";
    }
}

// $tr = new TaskReminder($DB_site2, $user_rappel);
// $tr->show('Suivi interne: ce jour', $tr->taskList(false));
// $tr->show('Suivi interne: autres', $tr->taskList(false));

?>