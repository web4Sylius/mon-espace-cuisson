 
<?php
ini_set("max_execution_time",180);
ini_set("display_errors",1);

$rootpath=__DIR__."/../../../";

require_once($rootpath."configurations/www.ma-cave-a-vin.fr/config_mysql.php");
require_once($rootpath."class/mysql.php");

$DB_site2 = new DB_Sql;
$DB_site2->appname='boutique';
$DB_site2->appshortname='boutique';
$DB_site2->database=$DBname;
$DB_site2->server=$db_server;
$DB_site2->user=$DBusername;
$DB_site2->password=$DBpassword;
$DB_site2->connect();

$errstatus=array("CheckMandatoryAttribute","Classification","CheckMandatoryBrand");

$connection = ssh2_connect('echange.redoute.fr',22);
 
// Authenticate
if (!ssh2_auth_password($connection, 'macaveavin', 'T!FGVJP:')) {
    var_dump('Unable to connect.');
    exit;
}
 
// Create our SFTP resource
if (!$sftp = ssh2_sftp($connection)) {
    var_dump('Unable to create SFTP connection.');
    exit;
}

$files = array();
$dirHandle = opendir("ssh2.sftp://".intval($sftp)."/Reports/Product");

// Properly scan through the directory for files, ignoring directory indexes (. & ..)
while (false !== ($file = readdir($dirHandle))) {
    if ($file != '.' && $file != '..') {
        $files[] = $file;
    }
}

arsort($files);
$lastfile=reset($files);

$h=fopen("ssh2.sftp://".intval($sftp)."/Reports/Product/".$lastfile,"r");
$f=fgets($h);
while($line = fgetcsv($h)){
    if(in_array($line[9],$errstatus)){
        $article=$DB_site2->query_first("SELECT artcode FROM article WHERE artid=".intval($line[4]));
        $DB_site2->query("INSERT INTO market_place_error SET date=".time().", market_place='redoute', artcode='".$article['artcode']."', error='".mysqli_real_escape_string($DB_site2->link_id,$line[10])."'");
    }
}

fclose($h);
