<?php

ini_set("display_errors",1);

$rootpath=dirname(dirname(__DIR__));

include_once $rootpath."/configurations/www.mon-espace-cuisson.com/config_mysql.php";
include_once $rootpath."/class/mysql.php";

$DB_site2=new DB_Sql;

$mysqli=new mysqli($db_server,$DBusername,$DBpassword,$DBname);

$users=array();
$req=$mysqli->query("SELECT telFR,telEN,username FROM parametresgeneraux WHERE actif=1");
while($u=$req->fetch_array()){
    if($u['telFR']!="") $users[$u['telFR']]=$u['username'];
    if($u['telEN']!="") $users[$u['telEN']]=$u['username'];
}

include($rootpath."/class/cocktel.class.php");

$cocktel=new cocktel("http://acilyon.cocktel.fr","mcav","synchromcav*","json","/tmp");

// header("content-type:application/json");
// print $cocktel->ListeAppels(array("search_start"=>time()-3600,"search_end"=>time()));
// exit;
$appels=json_decode($cocktel->ListeAppels(array("search_start"=>time()-3600,"search_end"=>time())));

foreach($appels->logs as $appel){
    $contact=null;
    switch($appel->sens){
        case "outgoing":
        if(!is_null($appel->to->contact))
            $contact=json_decode($cocktel->contact($appel->to->contact));
        else
            continue;
        $user=$appel->from->num;
        $moyen="tel_out";
        $suivi="Appel sortant de ".$appel->from->display." vers ".$appel->to->display.", durée ".$appel->duration;
        break;
        
        case "incoming":
        case "missed":
        if(!is_null($appel->from->contact)){
            $contact=json_decode($cocktel->contact($appel->from->contact));
        }else
            continue;
        $user=$appel->to->num;
        $moyen="tel_in";
        if($appel->sens=="missed")
            $suivi="Appel manqué de ".$appel->from->display;
        else
            $suivi="Appel entrant de ".$appel->from->display." vers ".$appel->to->display.", durée ".$appel->duration;
        break;
        
        default:
        continue;
    }

    if(!is_null($contact) && !is_null($contact->contacts[0])){
        switch($contact->contacts[0]->sync[0]->sync){
            case "assoc mec":
            case "clients mec":
            
            $req=$mysqli->query("SELECT * FROM suivi_client WHERE callid='".$appel->callid."'");
            if($req->num_rows>0){
                $sql="UPDATE suivi_client SET suivi=CONCAT(suivi,'<br><br>".$mysqli->real_escape_string($suivi)."') WHERE callid='".$appel->callid."'";
            }else{
                $req=$mysqli->query("SELECT * FROM suivi_commande WHERE callid='".$appel->callid."'");
                if($req->num_rows>0){
                    $sql="UPDATE suivi_commande SET suivi=CONCAT(suivi,'<br><br>".$mysqli->real_escape_string($suivi)."') WHERE callid='".$appel->callid."'";
                }else{
                    $req=$mysqli->query("SELECT etat FROM suivi_client WHERE userid=".intval($contact->contacts[0]->sync[0]->syncid)." ORDER BY date DESC LIMIT 1");
                    if($req->num_rows>0){
                        $last=$req->fetch_array();
                        $etat=$last['etat'];
                    }else
                        $etat=1;
                    
                        $sql="INSERT INTO suivi_client SET type='client', moyen='".$moyen."', userid=".intval($contact->contacts[0]->sync[0]->syncid).", commercial='".$users[$user]."', date=".intval($appel->datets).", suivi='".$mysqli->real_escape_string($suivi)."', etat=".$etat;
                    }
            }

            $mysqli->query($sql);
            break;
            
            case "fournisseurs mec":
            
            break;
        }
    }
}
