<?php
$document_root = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING);
$admin_username = filter_input(INPUT_COOKIE, 'admin_username', FILTER_SANITIZE_STRING);
if(empty($admin_username)){
     $admin_username = filter_input(INPUT_GET, 'admin_username', FILTER_SANITIZE_STRING);
}

ini_set("display_errors","on");

require_once($document_root.'/configurations/www.mon-espace-cuisson.com/config_mysql.php');
require_once($document_root.'/class/mysql.php');

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

require_once($document_root.'/admin/export/xlexport.php');

if (isset($admin_username) && $admin_username != "") {
     if($admin_username != "9d3bb895f22bf0afa958d68c2a58ded7"){
          $ip = $_SERVER['REMOTE_ADDR'];
          
          //Validation d'identité avant autorisation de téléchargement
          $sql = "SELECT *";
          $sql .= " FROM acces_admin";
          $sql .= " WHERE ip = '".$ip."'";
          $sql .= " AND user='".$admin_username."'";
          $sql .= " AND connecte = 1";
          $sql .= " ORDER BY timestamp DESC";
          $sql .= " LIMIT 0,1";
          $acces = $DB_site->query_first($sql);
          
          if($acces['timestamp']==false || $acces['timestamp']<time()-3600){
               exit;
          }else{
               $sql = "SELECT *";
               $sql .= " FROM parametresgeneraux";
               $sql .= " WHERE username = '".$admin_username."'";
               $user_info = $DB_site->query_first($sql);
               if($user_info[username] != $admin_username){
                    exit;
               }
          }
     }
}else{
     exit;
}

if(!isset($_GET['du']) || !isset($_GET['au'])) exit;

$sp=explode("/",$_GET['du']);
$du=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
$sp2=explode("/",$_GET['au']);
$au=mktime(23,59,59,$sp2[1],$sp2[0],$sp2[2]);

$codestva=array("0"=>"A4","5.5"=>"A1","7"=>"A7","19.6"=>"A2","20"=>"A5");

$xl=new xlexport("csv");
$xl->name = "acilyondev-frais-".date("Ymd",$du)."-".date("Ymd",$au);
$xl->addrow(array("Date facture","N° facture","N° facture","N° commande","Code journal","N° compte","Code TVA","Détail des ligne","Montant payé","Code analytique","Date échéance"));

$requete=$DB_site->query("SELECT * FROM facture_fournisseur_frais WHERE date_saisie BETWEEN $du AND $au ORDER BY date_facture");
while($facture=$DB_site->fetch_array($requete)){
    $facture['montant_tva']=round(($facture['tva']/100)*$facture['montant_ht'],2);
    $dateecheance=($facture['date_echeance']>0?date("d/m/Y",$facture['date_echeance']):"");
    $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$facture['numero'],$facture['numero'],$facture['nom'],"AC",$facture['compte'],$codestva[$facture['tva']],$facture['montant_ht'],"",$facture['code_analytique'],$dateecheance));
    if($facture['tva']>0){
    $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$facture['numero'],$facture['numero'],$facture['nom'],"AC","4456600000",$codestva[$facture['tva']],$facture['montant_tva'],"",$facture['code_analytique'],$dateecheance));
  } else {
    $total_tva = $facture['montant_ttc'] * 0.2;
    $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$facture['numero'],$facture['numero'],$facture['nom'],"AC","445680000",$codestva[$facture['tva']],$total_tva,"",$facture['code_analytique'],$dateecheance));
    $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$facture['numero'],$facture['numero'],$facture['nom'],"AC","445780000",$codestva[$facture['tva']],"",$total_tva,$facture['code_analytique'],$dateecheance));
  }
    $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$facture['numero'],$facture['numero'],$facture['nom'],"AC",$facture['code401'],"","",$facture['montant_ttc'],$facture['code_analytique'],$dateecheance));
}

$xl->export();

?>