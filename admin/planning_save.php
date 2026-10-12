<?php

require "admin_global.php";
ini_set("display_errors","on");

header("Cache-Control: no-cache");
header("Content-type:text/xml");

print "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>
<xml>\n";

if(isset($_GET['users'])){
    $return="";
    $requete=$DB_site2->query("SELECT username FROM parametresgeneraux WHERE actif=1");
    while($adminuser=$DB_site2->fetch_array($requete))
	$return.="\t<user selected=\"".($adminuser[0]==$user_info['username']?"1":"0")."\">$adminuser[0]</user>\n";
    
    print $return;
}

if(isset($_GET['user']) && isset($_GET['date'])){
    $return="";
    $d=getdate(intval($_GET['date']));
    $d0=mktime(0,0,0,$d['mon'],$d['mday'],$d['year']);
    $d1=$d0+86399;
    $requete=$DB_site2->query("SELECT sc.userid,sc.rappel,u.nom,u.prenom,sc.hide,sc.jour FROM suivi_client sc LEFT JOIN utilisateur u USING(userid) WHERE pour='".$_GET['user']."' AND rappel BETWEEN $d0 AND $d1");
    while($rappel=$DB_site2->fetch_array($requete)){
	print "<rappel userid=\"".$rappel['userid']."\" date=\"".$rappel['rappel']."\" jour=\"".$rappel['jour']."\" hide=\"".$rappel['hide']."\">".xml_convert($rappel['nom']." ".$rappel['prenom'])."</rappel>\n";
    }
    
    $requete=$DB_site2->query("SELECT sc.facturid,sc.rappel,u.nom,u.prenom,u.userid,sc.hide,sc.jour FROM suivi_commande sc LEFT JOIN facture f USING(facturid) INNER JOIN utilisateur u USING(userid) WHERE pour='".$_GET['user']."' AND rappel BETWEEN $d0 AND $d1");
    while($rappel=$DB_site2->fetch_array($requete)){
	print "<rappel userid=\"".$rappel['userid']."\" facturid=\"".$rappel['facturid']."\" date=\"".$rappel['rappel']."\" jour=\"".$rappel['jour']."\" hide=\"".$rappel['hide']."\">".$rappel['nom']." ".$rappel['prenom']."</rappel>\n";
    }
    
    $requete=$DB_site2->query("SELECT fournisseur,rappel,jour,rappeltype,pour FROM suivi_fournisseur WHERE pour='".$_GET['user']."' AND rappel BETWEEN $d0 AND $d1");
    while($rappel=$DB_site2->fetch_array($requete)){
	print "<rappel date=\"".$rappel['rappel']."\" jour=\"".$rappel['jour']."\" hide=\"".$rappel['hide']."\">".xml_convert($rappel['fournisseur'])."</rappel>\n";
    }
    
    $requete=$DB_site2->query("SELECT sc.destinataire,rappel,ss.hide FROM suivi_stock ss LEFT JOIN stock_cmd sc ON(sc.id=ss.stock_cmd_id) WHERE pour='".$_GET['user']."' AND rappel BETWEEN $d0 AND $d1");
    while($rappel=$DB_site2->fetch_array($requete)){
	print "<rappel date=\"".$rappel['rappel']."\" hide=\"".$rappel['hide']."\">".$rappel['destinataire']."</rappel>\n";
    }
    
    $requete=$DB_site2->query("SELECT * FROM crm_calls WHERE assigned_user_id='".$_GET['user']."' AND reminder_time>0 AND reminder_time BETWEEN $d0 AND $d1");
    while($r=$DB_site2->fetch_array($requete)){
      $account=$DB_site2->query_first("SELECT * FROM crm_accounts WHERE id='$r[parent_id]'");
      $name=$account['name'];
      if($parent['id']=""){
	  $parent=$DB_site2->query_first("SELECT * FROM crm_contacts WHERE id='$r[parent_id]'");
	  $account=$DB_site2->query_first("SELECT * FROM accounts a INNER JOIN crm_accounts_contacts ac ON (a.id=ac.account_id) WHERE ac.contact_id='$parent[id]'");
	  $name=$account['name']." ".$parent['first_name']." ".$parent['last_name'];
      }
      print "<rappel crmid=\"".$account[id]."\" date=\"".$r['reminder_time']."\" hide=\"".$rappel['hide']."\">".xml_convert($name)."</rappel>";
    }
}

print "</xml>";

?>