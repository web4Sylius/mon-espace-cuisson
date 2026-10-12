<?php

require "admin_global.php";
ini_set("display_errors","on");

header("Cache-Control: no-cache");
header("Content-type:text/xml");

print "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>
<xml>\n";

if(isset($_GET['users'])){
    $return=$type="";
    $requete=$DB_site2->query("SELECT username FROM parametresgeneraux WHERE actif=1");
    while($adminuser=$DB_site2->fetch_array($requete)){
	   $return.="\t<user selected=\"".($adminuser[0]==$user_info['username']?"1":"0")."\">$adminuser[0]</user>\n";
    }

    if(isset($_GET['type'])){
        $type = $_GET['type'];
    }
    $return.="<type>".$type."</type>";

    print $return;
}

if(isset($_GET['user']) && isset($_GET['date'])){
     
     $return = $type = $rappeltype = "";
     
     $d  = getdate(intval($_GET['date']));
     $d0 = mktime(0,0,0,$d['mon'],$d['mday'],$d['year']);
     $d1 = $d0+86399;

     if(isset($_GET['type'])){
        $type = $_GET['type'];
        if($type=="planning_rdv") {
            $rappeltype = " AND rappeltype ='rdv'";
        }
        elseif($type=="planning_suivi"){
            $rappeltype = " AND rappeltype ='int'";
        }
    }
    print "<type>".$type."</type>";


     //Sélection des rappels clients
     $sql = "SELECT sc.userid, sc.rappel, u.nom, u.prenom, sc.hide, sc.jour";
     $sql .= " FROM suivi_client sc";
     $sql .= " LEFT JOIN utilisateur u USING(userid)";
     $sql .= " WHERE pour = '".$_GET['user']."'";
     $sql .= $rappeltype;
     $sql .= " AND rappel BETWEEN ".$d0." AND ".$d1;
     $requete = $DB_site2->query($sql);
     while($rappel = $DB_site2->fetch_array($requete)){


          //On vérifie qu'il n'y a pas de suivi plus récent dans la fiche client, auquel cas il ne faut pas réserver la plage
          $sql = "SELECT rappel";
          $sql .= " FROM suivi_client";
          $sql .= " WHERE userid = ".$rappel['userid'];
          $sql .= " AND suivi NOT LIKE '[infos_client]%'";
          $sql .= " ORDER BY date";
          $sql .= " DESC LIMIT 0,1";
          $verification = $DB_site2->query_first($sql);
          
          if($verification['rappel'] != '' && $verification['rappel'] == $rappel['rappel']){

               print "<rappel userid=\"".$rappel['userid']."\" from=\"client\" date=\"".$rappel['rappel']."\" jour=\"".$rappel['jour']."\" hide=\"".$rappel['hide']."\">".xml_convert($rappel['nom']." ".$rappel['prenom'])."</rappel>\n";
          }
     }

     //Sélection des rappels commandes
     $sql = "SELECT sc.id, sc.facturid, sc.rappel, u.nom, u.prenom, u.userid, sc.hide, sc.jour";
     $sql .= " FROM suivi_commande sc";
     $sql .= " LEFT JOIN facture f USING(facturid)";
     $sql .= " INNER JOIN utilisateur u USING(userid)";
     $sql .= " WHERE pour = '".$_GET['user']."'";
     $sql .= " AND sc.suivi NOT LIKE '[maj_sav]%'";
     $sql .= " AND rappel BETWEEN ".$d0." AND ".$d1;
     $requete = $DB_site2->query($sql);
     while($rappel = $DB_site2->fetch_array($requete)){

        $affiche_creneau = true;

        //Si l'utilisateur est "qualite", on regarde s'il y a un suivi plus récent sur la commande, qui serait plus récent que le rappel.
        //Si c'est le cas, on laisse le créneau libre pour un nouveau rappel
        if($_GET['user'] == 'qualite'){
            $sql = "SELECT id";
            $sql .= " FROM suivi_commande";
            $sql .= " WHERE facturid = ".$rappel['facturid'];
            $sql .= " AND suivi NOT LIKE '[maj_sav]%'";
            $sql .= " ORDER BY date DESC";
            $sql .= " LIMIT 0,1";
            $tabSuivi = $DB_site2->query_first($sql);
            if($tabSuivi['id'] != $rappel['id']) $affiche_creneau = false;
        }

        if($affiche_creneau) print "<rappel userid=\"".$rappel['userid']."\" from=\"cmd\" facturid=\"".$rappel['facturid']."\" date=\"".$rappel['rappel']."\" jour=\"".$rappel['jour']."\" hide=\"".$rappel['hide']."\">".$rappel['nom']." ".$rappel['prenom']."</rappel>\n";
     } 
    
     //Sélection des rappels fournisseurs
     $sql = "SELECT fournisseur, rappel, jour, rappeltype, pour";
     $sql .= " FROM suivi_fournisseur";
     $sql .= " WHERE pour = '".$_GET['user']."'";
     $sql .= " AND rappel BETWEEN ".$d0." AND ".$d1;
     $requete = $DB_site2->query($sql);
     while($rappel = $DB_site2->fetch_array($requete)) print "<rappel from=\"fourn\" date=\"".$rappel['rappel']."\" jour=\"".$rappel['jour']."\" hide=\"".$rappel['hide']."\">".xml_convert($rappel['fournisseur'])."</rappel>\n";
     
     //Sélections des rappels stock
     $requete = $DB_site2->query("SELECT sc.destinataire,rappel,ss.hide 
		FROM suivi_stock ss LEFT JOIN stock_cmd sc ON(sc.id=ss.stock_cmd_id) 
		WHERE pour='".$_GET['user']."' 
		AND rappel BETWEEN $d0 AND $d1");
     while($rappel=$DB_site2->fetch_array($requete)){
         print "<rappel from=\"stock\" date=\"".$rappel['rappel']."\" hide=\"".$rappel['hide']."\">".$rappel['destinataire']."</rappel>\n";
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
       print "<rappel from=\"crm\" crmid=\"".$account[id]."\" date=\"".$r['reminder_time']."\" hide=\"".$rappel['hide']."\">".xml_convert($name)."</rappel>";
     }
}

print "</xml>";

?>