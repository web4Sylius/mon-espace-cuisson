<?php

error_reporting(E_ALL ^ E_NOTICE);

$ips=array("80.12.82.212","213.44.169.37");
if(!in_array($_SERVER['REMOTE_ADDR'],$ips)) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

include_once "../../configurations/www.mon-espace-cuisson.com/config_mysql.php";
include_once "../../class/mysql.php";

$DB_site2=new DB_Sql;
$DB_site2->appname='admin boutique';
$DB_site2->appshortname='aro';
$DB_site2->database=$DBname;
$DB_site2->server=$db_server;
$DB_site2->user=$DBusername;
$DB_site2->password=$DBpassword;
$DB_site2->connect();
$DBpassword="";
$DB_site2->password="" ;

$langues=array("FR","EN","SP");

$tel=$_GET['numero'];
if($tel=="anonymous"){
    print "Inconnu";
    exit;
}

if($tel==""){
    exit;
}

$tel=preg_replace("/[^0-9]/","",$tel);
if(strlen($tel)<6){
    print $_GET['numero'];
    exit;
}

if(substr($tel,0,2)=="00"){
 	$tel=substr($tel,2);
}

$poste=$_GET['poste'];

$regexreplace="CREATE FUNCTION  `regex_replace`(pattern VARCHAR(1000),replacement VARCHAR(1000),original VARCHAR(1000))
 
RETURNS VARCHAR(1000)
DETERMINISTIC
BEGIN
 DECLARE temp VARCHAR(1000);
 DECLARE ch VARCHAR(1);
 DECLARE i INT;
 DECLARE j INT;
 DECLARE qbTemp VARCHAR(1000);
 
 SET i = 1;
 SET j = 1;
 SET temp = '';
 SET qbTemp = '';
  
 IF original REGEXP pattern THEN
  loop_label: LOOP
   IF i>CHAR_LENGTH(original) THEN
    LEAVE loop_label; 
   END IF;
   SET ch = SUBSTRING(original,i,1);
   IF NOT ch REGEXP pattern THEN
    SET temp = CONCAT(temp,ch);
   ELSE
    SET temp = CONCAT(temp,replacement);
   END IF;
   SET i=i+1;
  END LOOP;
 ELSE
  SET temp = original;
 END IF;
 SET temp = TRIM(BOTH replacement FROM temp);
 SET temp = REPLACE(REPLACE(REPLACE(temp , CONCAT(replacement,replacement),CONCAT(replacement,'#')),CONCAT('#',replacement),''),'#','');
 RETURN temp;
END";

// $DB_site2->query($regexreplace);

$sqlpostes="";
foreach($langues as $langue) $sqlpostes.=",poste".$langue;
$admin_contact=$DB_site2->query_first("SELECT username FROM admin_contact WHERE '".$poste."' IN (".substr($sqlpostes,1).")");

$sqlpattern="[^0-9+]";
// $user=$DB_site2->query_first("SELECT userid,raisonsociale,nom,prenom FROM utilisateur WHERE regex_replace('$sqlpattern','',telephone) LIKE '%$tel' OR regex_replace('$sqlpattern','',portable) LIKE '%$tel'");
$user=$DB_site2->query_first("SELECT userid,raisonsociale,nom,prenom FROM utilisateur WHERE telephone LIKE '".$tel."' OR telephone LIKE '+".$tel."' OR telephone LIKE '00".$tel."' OR portable LIKE '".$tel."' OR portable LIKE '+".$tel."' OR portable LIKE '00".$tel."' ");
if($user=="") {
    // $fourn=$DB_site2->query_first("SELECT id,societe,nom,prenom FROM fournisseur WHERE regex_replace('$sqlpattern','',telephone) LIKE '%$tel' OR regex_replace('$sqlpattern','',portable) LIKE '%$tel'");
	$fourn=$DB_site2->query_first("SELECT id,societe,nom,prenom FROM fournisseur WHERE telephone LIKE '".$tel."' OR telephone LIKE '+".$tel."' OR telephone LIKE '00".$tel."' OR portable LIKE '".$tel."' OR portable LIKE '+".$tel."' OR portable LIKE '00".$tel."' ");
    if($fourn!=""){
      $DB_site2->query("INSERT INTO suivi_fournisseur SET type='pro', destinataire='".$fourn['id']."', moyen='tel_in', fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$fourn['societe'])."', date=".time().", commercial='".$admin_contact['username']."', suivi='Appel entrant'");
      print trim($fourn['societe']." ".$fourn['nom']." ".$fourn['prenom']);
		  
    }else{
      $assoc = $DB_site2->query_first("SELECT ua.userid,utilisateur.raisonsociale,ua.utilisateur_assoc_nom AS nom,ua.utilisateur_assoc_prenom AS prenom FROM utilisateur_assoc ua INNER JOIN utilisateur USING(userid) WHERE utilisateur_assoc_tel LIKE '".$tel."' OR utilisateur_assoc_tel LIKE '+".$tel."' OR utilisateur_assoc_portable LIKE '".$tel."' OR utilisateur_assoc_portable LIKE '+".$tel."' ");
      if($assoc !=""){
        $DB_site2->query("INSERT INTO suivi_client SET type='client', userid='".$assoc['userid']."', moyen='tel_in', destinataire=0, date=".time().", commercial='".$admin_contact['username']."', suivi='Appel entrant', etat=1");
        print trim($assoc['raisonsociale']." ".$assoc['nom']." ".$assoc['prenom']);
      }else{
        print $_GET['numero'];
      }
    }
}else{
    $DB_site2->query("INSERT INTO suivi_client SET type='client', userid='".$user['userid']."', moyen='tel_in', destinataire=".$user['userid'].", date=".time().", commercial='".$admin_contact['username']."', suivi='Appel entrant', etat=1");
    print trim($user['raisonsociale']." ".$user['nom']." ".$user['prenom']);
}

?>