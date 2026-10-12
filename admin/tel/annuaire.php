<?php

$rootpath=dirname(dirname(__DIR__));

include_once $rootpath."/configurations/www.mon-espace-cuisson.com/config_mysql.php";
include_once $rootpath."/class/mysql.php";

ini_set("display_errors",1);

$DB_site2=new DB_Sql;

$mysqli=new mysqli($db_server,$DBusername,$DBpassword,$DBname);

$sendhash=array();
$req=$mysqli->query("SELECT md5 FROM cocktel_synchro WHERE annuaire='fournisseurs mec'");
while($h=$req->fetch_array())
    $sendhash[]=$h['hash'];

include($rootpath."/class/cocktel.class.php");

$cocktel=new cocktel("http://acilyon.cocktel.fr","mcav","synchromcav*","json","/tmp");

$csv=csvline(array("sync","syncid","champ1","champ2","champ4","champ13","champ7","champ8","champ10","champ17"));


$updatesql="";
$sql="SELECT u.*,MD5(CONCAT(nom,prenom,telephone,raisonsociale,portable)) AS hash FROM utilisateur u LEFT JOIN cocktel_synchro s ON (s.userid=u.userid AND s.annuaire = 'clients mec') WHERE s.annuaire IS NULL OR (s.annuaire='clients mec' AND MD5(CONCAT(nom,prenom,telephone,raisonsociale,portable))!=s.md5)";
$req=$mysqli->query($sql);
if($req->num_rows>0){
    while($user=$req->fetch_array()){
        if($user['raisonsociale']==""){
            $bureau="";
            $domicile=trim($user['telephone']);
        }else{
            $bureau=trim($user['telephone']);
            $domicile="";
        }
        $portable=$user['portable'];
        if($portable=="" && preg_match("/^0[6|7]\d{8}$/",$domicile)){
            $portable=$domicile;
            $domicile="";
        }
        if($portable=="" && preg_match("/^0[6|7]\d{8}$/",$bureau)){
            $portable=$bureau;
            $bureau="";
        }
        
        $csv.=csvline(array("clients mec",$user['userid'],$user['nom'],$user['prenom'],$user['raisonsociale'],$user['codepostal'],$domicile,$bureau,$portable,""));
        
        $updatesql.="DELETE FROM cocktel_synchro WHERE annuaire='clients mec' AND userid=".$user['userid']."; ";
        $updatesql.="INSERT INTO cocktel_synchro SET userid=".$user['userid'].", annuaire='clients mec', md5='".$user['hash']."', date=".time()."; ";

    }
}


$sql="SELECT a.*,u.raisonsociale,MD5(CONCAT(utilisateur_assoc_nom,utilisateur_assoc_prenom,u.raisonsociale,utilisateur_assoc_fonction,utilisateur_assoc_tel,utilisateur_assoc_portable)) AS hash FROM utilisateur_assoc a INNER JOIN utilisateur u USING(userid) LEFT JOIN cocktel_synchro s ON (s.userid=a.utilisateur_assoc_id AND s.annuaire = 'assoc mec') WHERE a.actif = '1' AND (utilisateur_assoc_tel != '' OR utilisateur_assoc_portable != '') AND s.annuaire IS NULL OR (s.annuaire='assoc mec' AND MD5(CONCAT(utilisateur_assoc_nom,utilisateur_assoc_prenom,raisonsociale,utilisateur_assoc_fonction,utilisateur_assoc_tel,utilisateur_assoc_portable))!=s.md5 AND a.actif = '1' AND (utilisateur_assoc_tel != '' OR utilisateur_assoc_portable != ''))";
$req=$mysqli->query($sql);
if($req->num_rows>0){
    while($user=$req->fetch_array()){
        $bureau=$user['utilisateur_assoc_tel'];
        $domicile="";
        $portable=$user['utilisateur_assoc_portable'];
        
        $csv.=csvline(array("assoc mec",$user['userid'],$user['utilisateur_assoc_nom'],$user['utilisateur_assoc_prenom'],$user['raisonsociale'],"",$domicile,$bureau,$portable,$user['utilisateur_assoc_fonction']));
        
        $updatesql.="DELETE FROM cocktel_synchro WHERE annuaire='assoc mec' AND userid=".$user['utilisateur_assoc_id']."; ";
        $updatesql.="INSERT INTO cocktel_synchro SET userid=".$user['utilisateur_assoc_id'].", annuaire='assoc mec', md5='".$user['hash']."', date=".time()."; ";

    }
}

$sql="SELECT f.*,MD5(CONCAT(nom,prenom,societe,fonction,telephone,portable)) AS hash FROM fournisseur f LEFT JOIN cocktel_synchro s ON (s.userid=f.id AND s.annuaire = 'fournisseurs mec') WHERE f.type NOT LIKE('old') AND s.annuaire IS NULL OR (s.annuaire='fournisseurs mec' AND MD5(CONCAT(nom,prenom,societe,fonction,telephone,portable))!=s.md5)";
$req=$mysqli->query($sql);
if($req->num_rows>0){
    while($user=$req->fetch_array()){
        $bureau=$user['telephone'];
        $domicile="";
        $portable=$user['portable'];
        
        if(!in_array($user['hash'],$sendhash)){
            $csv.=csvline(array("fournisseurs mec",$user['id'],$user['nom'],$user['prenom'],$user['societe'],$user['cp'],$domicile,$bureau,$portable,$user['fonction']));
            
            $updatesql.="DELETE FROM cocktel_synchro WHERE annuaire='fournisseurs mec' AND userid=".$user['id']."; ";
            $updatesql.="INSERT INTO cocktel_synchro SET userid=".$user['id'].", annuaire='fournisseurs mec', md5='".$user['hash']."', date=".time()."; ";
            $sendhash[]=$user['hash'];
        }

    }
}

$mysqli->multi_query($updatesql);
    
// header("content-type:text/csv");
print $csv;

var_dump($cocktel->AjoutContactsCSV($csv));


function csvline($a){
    $a=array_map(function($e){
        return "\"".utf8_encode(trim($e))."\"";
    },$a);
    return implode(",",$a)."\n";
}
