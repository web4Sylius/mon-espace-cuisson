<?php
require_once __DIR__.'/../secrets.php';

$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
$host="www.ma-cave-a-vin.fr" ;

// ini_set("display_errors","1");
// error_reporting(E_ALL);

include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."class/mysql.php");
include_once("ftp.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

$expdate=time()-(86400*20);

$gcpuser=GCP_USER;
$gcppass=GCP_PASSWORD;

$marqueid="09";

if(!isset($_GET['retard'])){
      print "Garanties GC+ du ".date("d/m/Y",$expdate)."\n";
      $reqfacture=$DB_site->query("SELECT f.facturid FROM facture f INNER JOIN lignefacture lf ON(lf.factureid=f.facturid) INNER JOIN lignefacturecaracteristique lfc ON (lfc.lignefactureid=lf.lignefactureid) WHERE lfc.caractvalid IN (132,133) AND f.dateexpedition='".date("Y-m-d",$expdate)."' AND f.valide=5");
      if(mysql_num_rows($reqfacture)>0){
	  $gcp=str_pad("LOGIN",10).str_pad($gcpuser,30).str_pad($gcppass,30)."\n";
	  $gcp.=str_pad("HEADER",10).str_pad("1.0.0",7).str_repeat(" ",20)."\n";

	  while($f=$DB_site->fetch_array($reqfacture)){
	      $gcp.=contrat($f[0]);
	  }
	  $nbcont=$DB_site->num_rows($reqfacture);
	  $gcp.=str_pad("ENDHEADER",10).str_pad($nbcont,2,"0",STR_PAD_LEFT);

	  $filename=date("Y-m-d").".txt";
	  $f=fopen($rootpath."admin/gcp/".$filename,"w");
	  fwrite($f,$gcp);
	  fclose($f);

	  $result=ulfile($filename);
	  if($result)
	      print "Envoi vers le ftp réussi\n";
	  else
	      print "Erreur d'envoi";

      }else
	  print "Pas de garantie GC+\n";


      $ftp=new ftp();
      $ftp->server="ftp.rte.fr";
      $ftp->user=GCP_FTP_USER;
      $ftp->pass=GCP_FTP_PASSWORD;

      $files=$ftp->listdir("OUT");
      $local=$rootpath."/admin/gcp";

      $i=0;
      $msg2="Aucun fichier pdf récupéré";
      foreach($files as $f){
	      $result=preg_match("/([0-9]*)-([0-9]*)-([0-9]*)-([0-9]*).txt_([0-9]*).ack/",$f,$matches);
	      if($result==1){
		  $requete=mysql_query("SELECT * FROM gcp WHERE facturid='".$matches[4]."'");
		  if(mysql_num_rows($requete)==0){
		      $ftp->get("OUT/".$f,$local."/".$f);
		      $data=file_get_contents($local."/".$f,"r");
		      if(substr($data,52,1)=="1"){
			  mysql_query("INSERT INTO gcp SET facturid='".$matches[4]."', receive=".mktime());
			  $i++;
		      }
		  }
	      }
      }
      if($i>0) $msg2=$i." pdf récupérés\n";
      print $msg2;
}


if(isset($_GET['retard'])){
      
      $reqfacture=$DB_site->query_first("SELECT f.facturid FROM facture f INNER JOIN lignefacture lf ON(lf.factureid=f.facturid) INNER JOIN lignefacturecaracteristique lfc ON (lfc.lignefactureid=lf.lignefactureid) WHERE lfc.caractvalid IN (132,133) AND f.facturid=".$_GET['retard']." AND f.valide=5");
      if($reqfacture[0]!=""){
	  $gcp=str_pad("LOGIN",10).str_pad($gcpuser,30).str_pad($gcppass,30)."\n";
	  $gcp.=str_pad("HEADER",10).str_pad("1.0.0",7).str_repeat(" ",20)."\n";
	  $gcp.=contrat($reqfacture[0]);
	  $gcp.=str_pad("ENDHEADER",10).str_pad(1,2,"0",STR_PAD_LEFT);

	  $filename=date("Y-m-d")."-".$reqfacture[0].".txt";
	  $f=fopen("./gcp/".$filename,"w");
	  fwrite($f,$gcp);
	  fclose($f);

	  $result=ulfile($filename);
	  if($result)
	      print "Envoi vers le ftp réussi <a href=\"javascript:window.close();\">Fermer</a>";
	  else
	      print "Erreur d'envoi";
      }else
	  print "Cette commande n'a pas de garantie GC+";
}

function contrat($facturid){
  global $DB_site;
  global $marqueid;
  $civ=array(0=>"001",1=>"002",2=>"003");
  $extlen=array(132=>24,133=>60);
  $nbprod=0;
  $facture=$DB_site->query_first("SELECT *,UNIX_TIMESTAMP(dateexpedition) AS expeditionts FROM facture WHERE facturid=".$facturid);
  $user=$DB_site->query_first("SELECT * FROM utilisateur WHERE userid=".$facture['userid']);
  if($facture['expeditionts']<time()-(30*86400)) $facture['expeditionts']=time()-(20*86400);

  $ret=str_pad("CONTR",10).str_repeat(" ",30).$marqueid.str_pad($facturid,20).str_repeat(" ",10).date("d/m/Y",$facture['expeditionts'])."0   \n";
  $ret.=str_pad("CONSUMER",10).$civ[$user['civilite']].str_pad($user['nom'],50).str_pad($user['prenom'],50)."0".str_pad(preg_replace("/\.| |-/","",$user['telephone']),20).str_pad(preg_replace("/\.| |-/","",$user['portable']),20).str_pad($user['mail'],100)."\n";
  $ret.=str_pad("ADDRESS",10).str_pad($user['adresse'],50).str_repeat(" ",50).str_repeat(" ",50).str_pad($user['codepostal'],25).str_pad($user['ville'],50)."250ADRESSE\n";
  
  $req=$DB_site->query("SELECT lf.*,lfc.caractvalid FROM lignefacture lf INNER JOIN lignefacturecaracteristique lfc USING(lignefactureid) WHERE lfc.caractvalid IN (132,133) AND lf.factureid=".$facturid);
  while($ligne=$DB_site->fetch_array($req)){
      $article=$DB_site->query_first("SELECT artcode_eurocave FROM article WHERE artid=".$ligne['artid']);
      for($i=0;$i<$ligne['qte'];$i++){
	$ret.=str_pad("PRODUCT",10).str_repeat(" ",10).str_pad($ligne['lignefactureid'],20).str_pad($article['artcode_eurocave'],50).str_repeat(" ",50).str_repeat(" ",30).$extlen[$ligne['caractvalid']]."\n";
	$nbprod++;
      }
  }
  $ret.=str_pad("ENDCONTR",10).str_pad($nbprod,2,"0",STR_PAD_LEFT)."\n";

  return $ret;

}

function ulfile($filename){
    global $rootpath;

    $ftp=new ftp();
    $ftp->server="ftp.rte.fr";
    $ftp->user=GCP_FTP_USER;
    $ftp->pass=GCP_FTP_PASSWORD;
    $result=$ftp->put($rootpath."admin/gcp/".$filename,"IN/".$filename);
    $ftp->quit();
    return $result;
    
}


?>