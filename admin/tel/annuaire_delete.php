<?php
$rootpath=dirname(dirname(__DIR__));

include_once $rootpath."/configurations/www.mon-espace-cuisson.com/config_mysql.php";
include_once $rootpath."/class/mysql.php";
include_once $rootpath."/class/cocktel.class.php";

ini_set("display_errors",1);

$DB_site2=new DB_Sql;

$mysqli=new mysqli($db_server,$DBusername,$DBpassword,$DBname);

$cocktel=new cocktel("http://acilyon.cocktel.fr","mcav","synchromcav*","json","/tmp");

$delete = "";
//suppression ancien contact fournisseur
$sql="SELECT f.id , s.userid FROM fournisseur f INNER JOIN cocktel_synchro s ON (s.userid=f.id AND s.annuaire = 'fournisseurs mec') WHERE f.type ='old'";
$req=$mysqli->query($sql);
if($req->num_rows>0){
    while($user=$req->fetch_array()){
        $contacts = json_decode($cocktel->contact_site($user['userid']));
        foreach($contacts->contacts as $lecontact){
            if($lecontact->sync[0]->sync == 'fournisseurs mec'){
                $delete.= $lecontact->id."," ; 
                $deletetab[$lecontact->id] = $user['userid'];
            }
        }
    }
}

$delete = substr($delete, 0, -1);
$ret = $cocktel->SuppressionContact($delete);
$liste="";
$ret = json_decode($ret);

foreach($ret->contacts as $id => $state){

    if(intval($state) == 1){
      $liste .="'".$deletetab[$id]."',";
    } 
}
$liste = substr($liste, 0, -1);
$sql = "DELETE FROM cocktel_synchro WHERE annuaire='fournisseurs mec' AND userid IN(".$test.")";
$mysqli->query($sql);   
