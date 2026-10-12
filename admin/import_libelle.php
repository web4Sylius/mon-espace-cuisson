<?php


$host="www.ma-cave-a-vin.fr" ;
$nostat=1;
include_once("../configurations/$host/config_mysql.php");
include("../class/mysql.php");


$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

$f=fopen("libelle.csv","r");
while(!feof($f)){
  $sp=explode(";",fgets($f,1024));
  $sp[0]=str_replace("\"","",$sp[0]);
  $sp[1]=str_replace("\"","",$sp[1]);
  $sp[1]=str_replace("\n","",$sp[1]);
  $DB_site->query("UPDATE article SET libelleSP='".mysqli_real_escape_string($DB_site->link_id,$sp[1])."' WHERE libelleFR='".mysqli_real_escape_string($DB_site->link_id,$sp[0])."' OR libelleFR='".mysqli_real_escape_string($DB_site->link_id,htmlentities($sp[0]))."'");
}
fclose($f);

print "<table>";
$r=$DB_site->query("SELECT * FROM article WHERE libelleSP='' AND active IN ('0','1')");
while($art=$DB_site->fetch_array($r))
  print "<tr><td>".$art['artcode']."</td><td>".$art['libelleFR']."</td></tr>";
print "</table>";

?>