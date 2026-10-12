<?php
// include("../admin_global.php");

ini_set("display_errors",1);
set_time_limit(300);

$rootpath="/var/www/vhosts/mon-espace-cuisson.com/httpdocs/";
$rootpath="../../";

$host="www.mon-espace-cuisson.com" ;
$nostat=1;
include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."class/mysql.php");
include($rootpath."configurations/$host/config.php");
include_once($rootpath."class/fonctions.php");
include_once($rootpath."class/facture.class.php");

include_once("xlexport.php");

$DB_site2=new DB_Sql;
$DB_site2->appname='boutique';
$DB_site2->appshortname='boutique';
$DB_site2->database=$DBname;
$DB_site2->server=$db_server;
$DB_site2->user=$DBusername;
$DB_site2->password=$DBpassword;
$DB_site2->connect();

$status=array(-1=>"blacklisté",-2=>"difficile",0=>"",1=>"VIP",2=>"partenaire");
$civ=array(0=>"M.",1=>"Mme",2=>"Melle");

$admin_username=$_COOKIE['admin_username'];
if(isset($_GET['admin_username'])) $admin_username=$_GET['admin_username'];

if (isset($admin_username) && $admin_username != "") {
    if($admin_username!="9d3bb895f22bf0afa958d68c2a58ded7"){
        $user_info=$DB_site2->query_first("SELECT * FROM parametresgeneraux WHERE username = '$admin_username'");
        if ($user_info[username] != $admin_username){
			echo "erreur de username";
			exit;
		}
    }
}else{
  echo "erreur de droits";
  exit;
}
$name="export_";
if($sms) $name.="sms_";

if(isset($_GET['clients'])){
    if($_GET['clients']==1)
      $name.="clients_";
    else
      $name.="prospects_";
}
if(isset($_GET['pro'])){
    if($_GET['pro']==1)
      $name.="pros_";
    else
      $name.="particuliers_";
}
if(isset($_GET['origine'])) $name.=$_GET['origine']."_";
$name.=date("Ymd");

$xl=new xlexport("csv",$name,true);
$xl->buffer = chr(239) . chr(187) . chr(191);
$complet=isset($_GET['complet']) && $user_info['admin_access']=="";
$sms=isset($_GET['sms']);
if($complet)
    $xl->addrow(array("Num client","Inscription","Statut","Commercial","Société","TVA intra","Civilité","Nom","Prénom","Adresse","CP","Ville","Pays","E-mail","Téléphone","Portable","Langue","NL email","NL sms","Profession","Connu","Site","Fiche client","Nb commande","Montant HT"));
  else
    $xl->addrow(array("Nom","Prenom","Mail","Telephone"));

$sql="SELECT DISTINCT u.* FROM utilisateur u";

//si des types d'articles on été sélectionnés (pas la case "tous"), on modifie la requete sql
if(isset($_GET['x']) && intval($_GET['x'])!=0 ){
  $sql .= " INNER JOIN facture f ON(u.userid=f.userid) INNER JOIN lignefacture l ON (f.facturid=l.factureid)";
}
$sql .=" WHERE 1";

if(!$complet) $sql.=" AND u.allowemail='1' AND u.vip<>-1 AND u.mail<>''";
if($sms) $sql.=" AND u.allowsms='1' AND CONCAT(telephone,portable)!=''";
if(isset($_GET['pro'])) $sql.=" AND u.raisonsociale".($_GET['pro']==1?"<>":"=")."''";
if(isset($_GET['clients'])) $sql.=" AND (SELECT COUNT(facturid) FROM facture WHERE userid=u.userid AND valide IN(1,5,7,8))".($_GET['clients']==1?">":"=")."0";

$sql.=" AND u.langue='FR' AND u.site_code IN ('base','')";

/*if(isset($_GET['origine'])){
    switch($_GET['origine']){
      case "mcavfr":
      $sql.=" AND u.langue='FR' AND u.site_code IN ('base','')";
      break;
      
      case "mwccom":
      $sql.=" AND u.langue='EN' AND u.site_code IN ('base','')";
      break;
      
      case "mwcuk":
      $sql.=" AND u.langue='EN' AND u.site_code='uk'";
      break;
    }
}*/
//on rajoute la civilité dans la requete (Homme/Femme)
if(isset($_GET['civilite'])){
  if($_GET['civilite'] == 0)
    $sql .=" AND u.civilite ='0'";
  elseif($_GET['civilite'] == 1)
    $sql .=" AND u.civilite ='1'";
}
//On rajoute les types d'articles (catid) dans la requete, on a rajouté plus haut deux jointure sur la requete.
if(isset($_GET['x']) && intval($_GET['x'])!=0){
  $type = unserialize($_GET['type']);
  $sql .= " AND l.catid IN(";
  foreach($type as $catArt){
    $sql.=$catArt.',';
  }
  $sql=substr($sql,0,-1);
  $sql .=")";
}

if(isset($_GET['subfrom']) && isset($_GET['subto']))
    $sql.=" AND u.dateinscription BETWEEN FROM_UNIXTIME(".intval($_GET['subfrom']).") AND FROM_UNIXTIME(".intval($_GET['subto']).")";

$clients=$DB_site2->query($sql);
while($client=$DB_site2->fetch_array($clients)){
    if($complet){
        $montantht=0;
        $req=$DB_site2->query("SELECT facturid FROM facture WHERE valide IN (1,5,7,8) AND userid=".$client['userid']);
        while($f=$DB_site2->fetch_array($req)){
            $facture=new facture($DB_site2,$f['facturid']);
            $montantht+=$facture->totalht;
        }
        $nb=$DB_site2->num_rows($req);
        // $nb = $DB_site2->query_first("SELECT COUNT(DISTINCT f.facturid) AS cmd, SUM(lf.qte*lf.prix/(1+lf.tva/100)) AS montant FROM facture f INNER JOIN lignefacture lf ON f.facturid=lf.factureid WHERE f.valide IN (1,5,7,8) AND userid=".$client['userid']." GROUP BY f.userid");
        $xl->addrow(array($client['userid'],$client['dateinscription'],$status[$client['vip']],$client['commercial'],$client['raisonsociale'],$client['tvaintracom'],$civ[$client['civilite']],$client['nom'],$client['prenom'],$client['adresse'],$client['codepostal'],$client['ville'],$client['libelle'],$client['mail'],$client['telephone'],$client['portable'],$client['langue'],$client['allowemail'],$client['allowsms'],$client['profession'],$client['connu'],$client['site'],"http://www.mon-espace-cuisson.com/admin/utilisateur.php?action=editer&userid=".$client['userid'],$nb,round($montantht,2)));
    }elseif($sms){
        $num="";
        if(preg_match("/^0[67]/",$client['telephone'])){
            $num=$client['telephone'];
        }else{
            if(preg_match("/^0[67]/",$client['portable'])){
                $num=$client['portable'];
            }
        }
        if($num!=""){
            $num=preg_replace("/[^0-9]/","",$num);
            $num="+33".substr($num,1);
            if(strlen($num)==12) $xl->addrow(array($client['nom'],$client['prenom'],$client['mail'],$num));
        }
    }else{
        $xl->addrow(array($client['nom'],$client['prenom'],$client['mail'],$client['telephone']));
    }
}

if($complet){
    $xl->addrow(array("Contacts liés"));
    $sql="SELECT u.*,ua.utilisateur_assoc_fonction,ua.utilisateur_assoc_prenom,ua.utilisateur_assoc_nom,ua.utilisateur_assoc_civilite,ua.utilisateur_assoc_tel,ua.utilisateur_assoc_portable,ua.utilisateur_assoc_email FROM utilisateur_assoc ua INNER JOIN utilisateur u USING(userid)";
    $clients=$DB_site2->query($sql);
    while($client=$DB_site2->fetch_array($clients)){
        $montantht=0;
        $req=$DB_site2->query("SELECT facturid FROM facture WHERE valide IN (1,5,7,8) AND userid=".$client['userid']);
        while($f=$DB_site2->fetch_array($req)){
            $facture=new facture($DB_site2,$f['facturid']);
            $montantht+=$facture->totalht;
        }
        $nb=$DB_site2->num_rows($req);
        $xl->addrow(array($client['userid'],$client['dateinscription'],$status[$client['vip']],$client['commercial'],$client['raisonsociale'],$client['tvaintracom'],$civ[$client['utilisateur_assoc_civilite']],$client['utilisateur_assoc_nom'],$client['utilisateur_assoc_prenom'],"","","","",$client['utilisateur_assoc_email'],$client['utilisateur_assoc_tel'],$client['utilisateur_assoc_portable'],$client['langue'],$client['allowemail'],$client['allowsms'],$client['utilisateur_assoc_fonction'],$client['connu'],$client['site'],"https://www.mon-espace-cuisson.com/admin/utilisateur.php?action=editer&userid=".$client['userid'],$nb,round($montantht,2)));
    }
}

 $xl->export();

// $xl->write(".");

?>

