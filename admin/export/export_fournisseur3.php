<?php
$document_root = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING);
$admin_username = filter_input(INPUT_COOKIE, 'admin_username', FILTER_SANITIZE_STRING);
if(empty($admin_username)){
     $admin_username = filter_input(INPUT_GET, 'admin_username', FILTER_SANITIZE_STRING);
}

ini_set("display_errors","on");

require_once($document_root.'/configurations/config_mysql.php');
require_once($document_root.'/class/mysql.php');
require_once($document_root.'/class/facture.class.php');
require_once($document_root.'/class/stock.class.php');
require_once($document_root.'/Aci-AdM/export/zip.lib.php');

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

require_once($document_root.'/Aci-AdM/export/xlexport.php');
//require_once($document_root.'/Aci-AdM/marketplace2/order.php');

//Paramétrages
$nom_fichier = 'compta_fournisseur_mec_acilyon';
$provenance = 'monespacecuisson';
$codeTVADefault = '445660';
$tabColonnes = array(
    "Date facture",
    "N° facture",
    "N° facture",
    "N° commande",
    "Code journal",
    "N° compte",
    "Code TVA",
    "Détail des ligne",
    "Montant payé",
    "Code analytique",
    "Date échéance",
    "Date facture"
);
$tabCodesAnalytiques = array(
    'sls'   => '607610', //Montage mon espace cuisson
    'trspt' => '607510', //Port mon espace cuisson
    'eco'   => '607160', //Eco-Part mon espace cuisson
    'gar'   => '607150', //Garantie mon espace cuisson
    'avoir' => '607100',
    'fourn' => '607100', //Achat mon espace cuisson
    'part'  => '622500'
);           
$tabCodesTVA = array(
     '20' => array(
        'code_tva' => '4457120',
        'code_v'   => 'A5'
     ),
     '19.6' => array(
        'code_tva' => '445710',
        'code_v'   => 'A2'
     ),
     '7' => array(
        'code_tva' => '445712',
        'code_v'   => 'V1'
     ),
     '5.5' => array(
        'code_tva' => '445712',
        'code_v'   => 'V1'
     ),
     '0' => array(
        'code_tva' => '445710',
        'code_v'   => 'V4'
     )
);
$tabCodesCEE = array(
    'france'    => '3000',
    'cee_tva'   => '3009',
    'cee_notva' => '3099',
    'hors_cee'  => '3999'
);

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

$sp = explode("/",$_GET['du']);
$du = mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
$sp2 = explode("/",$_GET['au']);
$au = mktime(23,59,59,$sp2[1],$sp2[0],$sp2[2]);

$xl = new xlexport("csv");
$xl->name = $nom_fichier."_".date("Ymd",$du)."-".date("Ymd",$au);
$xl->addrow($tabColonnes);

$sql = "SELECT *";
$sql .= " FROM facture_fournisseur ff, fournisseur_condition fc";
$sql .= " WHERE fc.societe = ff.fournisseur";
$sql .= " AND date_saisie BETWEEN '".$du."' AND '".$au."'";
$sql .= " AND fc.societe_facturation_id = 1";
$sql .= " ORDER BY date_facture";
$requete = $DB_site->query($sql);
while($facture=$DB_site->fetch_array($requete)){
     $nom = $facture['fournisseur'];
     $txtva = (floatval($facture['tva'])/100);
     $code401 = code401($facture['fournisseur']);
     $codea = $tabCodesTVA[$facture['tva']]['code_v'];
     $codecee = codecee($nom);
     $numero_8chr = substr($facture['numero'],-8);

     $sql = "SELECT *";
     $sql .= " FROM envoi_fournisseur_ligne";
     $sql .= " WHERE facture='".$facture['numero']."'";
     $r2 = $DB_site->query($sql);
    
     while($ligne = $DB_site->fetch_array($r2)){
          $sql = "SELECT u.paysid, f.moyenid, f.facturid, f.numerofacture, UNIX_TIMESTAMP(f.dateexpedition) AS dateexpedition, f.provenanceid, f.provenance";
          $sql .= " FROM lignefacture lf, facture f, utilisateur u";
          $sql .= " WHERE u.userid = f.userid";
          $sql .= " AND f.facturid = lf.factureid";
          $sql .= " AND lignefactureid = ".$ligne['ligne_facture_id'];
          $lignefacture = $DB_site->fetch_array($DB_site->query($sql));

          if($lignefacture['provenance']=='mp'){
               $compte_ana=compte_analytique($lignefacture['provenanceid'],$lignefacture['paysid']);
          }else{
               $compte_ana=compte_analytique($provenance,$lignefacture['paysid']);
          }
	
          $factureclient = $lignefacture['facturid']." Fa N° ".$lignefacture['numerofacture']." du ".date("d/m/Y",$lignefacture['dateexpedition']);
          $dateexpedition = "";
          if($lignefacture['dateexpedition']>0){
               $dateexpedition=date("d/m/Y",$lignefacture['dateexpedition']);
          }
          if($ligne['paht']!=0){
               $tabRow = array(
                   date("d/m/Y",$facture['date_facture']),
                   $numero_8chr,
                   $facture['numero'],
                   $factureclient,
                   "AC",
                   $tabCodesAnalytiques[$ligne['type']].$codecee,
                   $codea,
                   round($ligne['qte']*$ligne['paht'],2),
                   "",
                   $compte_ana
               );
               $xl->addrow($tabRow); 
               if($txtva>0){
                    $tabRow = array(
                         date("d/m/Y",$facture['date_facture']),
                         $numero_8chr,
                         $facture['numero'],
                         $factureclient,
                         "AC",
                         $codeTVADefault,
                         $codea,
                         round($ligne['qte']*$ligne['paht']*$txtva,2),
                         "",
                         $compte_ana
                     );
                    $xl->addrow($tabRow);
               }
          }
          if($ligne['ecopart']!=0){
               $tabRow = array(
                    date("d/m/Y",$facture['date_facture']),
                    $numero_8chr,
                    $facture['numero'],
                    $factureclient,"AC",
                    $tabCodesAnalytiques['eco'].
                    $codecee,
                    $codea,
                    round($ligne['qte']*$ligne['ecopart'],2),
                    "",
                    $compte_ana
               );
               $xl->addrow($tabRow);
               
               if($txtva>0){
                    $tabRow = array(
                         date("d/m/Y",
                         $facture['date_facture']),
                         $numero_8chr,
                         $facture['numero'],
                         $factureclient,
                         "AC",
                         $codeTVADefault,
                         $codea,
                         round($ligne['qte']*$ligne['ecopart']*$txtva,2),
                         "",
                         $compte_ana
                    );
                    $xl->addrow($tabRow);
               }
          }
     }
    
     $sql = "SELECT *";
     $sql .= " FROM facture_fournisseur_autre";
     $sql .= " WHERE facture = '".$facture['numero']."'";
     $r2 = $DB_site->query($sql);
     while($ligne=$DB_site->fetch_array($r2)){
          $compte_ana = "";
          $fclient = "";
          $dateexpedition = "";
          $factureclient = array();
          if($ligne['facturid']!=""){
               $sql = "SELECT u.paysid, f.facturid, f.numerofacture, UNIX_TIMESTAMP(f.dateexpedition) AS dateexpedition, provenance, provenanceid";
               $sql .= " FROM facture f, utilisateur u";
               $sql .= " WHERE u.userid=f.userid";
               $sql .= " AND f.facturid='".intval($ligne['facturid'])."'";
               $factureclient = $DB_site->query_first($sql);
               
               if($factureclient['facturid']==""){
                    $factureclient=$DB_site->query_first("SELECT * FROM mp_orders WHERE id='".$ligne['facturid']."'");
                    if($factureclient['facturid']!=""){
                         $compte_ana=compte_analytique_mp($factureclient['marketplace']);
                    }
               }else{
                    if($factureclient['provenance']=="mp"){
                         $provenance = $factureclient['provenanceid'];
                    }
                    $compte_ana = compte_analytique($provenance,$factureclient['paysid']);
                    $fclient = $factureclient['facturid']." Fa N° ".$factureclient['numerofacture']." du ".date("d/m/Y",$factureclient['dateexpedition']);
                    if($factureclient['dateexpedition']>0){
                         $dateexpedition=date("d/m/Y",$factureclient['dateexpedition']);
                    }
               }
          }else{
               $compte_ana = 1;
          }

          if($ligne['prix']!=0){
               $tabRow = array(
                    date("d/m/Y",$facture['date_facture']),
                    $numero_8chr,
                    $facture['numero'],
                    $fclient,
                    "AC",
                    $tabCodesAnalytiques[$ligne['type']].$codecee,
                    $codea,round($ligne['prix'],2),
                    "",
                    $compte_ana
               );
               $xl->addrow($tabRow); 
               
               if($txtva>0){
                    $tabRow = array(
                         date("d/m/Y",$facture['date_facture']),
                         $numero_8chr,
                         $facture['numero'],
                         $fclient,
                         "AC",
                         $codeTVADefault,
                         $codea,
                         round($ligne['prix']*$txtva,2),
                         "",
                         $compte_ana
                    );
                    $xl->addrow($tabRow);
               }
          }
     }

     $stock = new stock($DB_site);
     $cmds = $stock->getfacture($facture['numero'],"fourn");
     foreach($cmds as $items){
          foreach($items as $artid=>$item){
               $tabRow = array(
                    date("d/m/Y",$facture['date_facture']),
                    $numero_8chr,
                    $facture['numero'],
                    "STOCK N°".$item->stock_cmd_id,
                    "AC",
                    $tabCodesAnalytiques['fourn'].$codecee,
                    $codea,
                    round($item->paht,2),
                    "",
                    19
               );
               $xl->addrow($tabRow);
               
               if($txtva>0){
                    $tabRow = array(
                         date("d/m/Y",$facture['date_facture']),
                         $numero_8chr,
                         $facture['numero'],
                         "STOCK N°".$item->stock_cmd_id,
                         "AC",
                         $codeTVADefault,
                         $codea,
                         round($item->paht*$txtva,2),
                         "",
                         19
                    );
                    $xl->addrow($tabRow);
               }
               
               if($item->ecopartht>0){
                    $tabRow = array(
                         date("d/m/Y",$facture['date_facture']),
                         $numero_8chr,
                         $facture['numero'],
                         "STOCK N°".$item->stock_cmd_id,
                         "AC",
                         $tabCodesAnalytiques['eco'].$codecee,
                         $codea,
                         round($item->ecopartht,2),
                         "",
                         19
                    );
                    $xl->addrow($tabRow);
                    
                    if($txtva>0){
                         $tabRow = array(
                              date("d/m/Y",$facture['date_facture']),
                              $numero_8chr,
                              $facture['numero'],
                              "STOCK N°".$item->stock_cmd_id,
                              "AC",
                              $codeTVADefault,
                              $codea,
                              round($item->ecopartht*$txtva,2),
                              "",
                              19
                         );
                         $xl->addrow($tabRow);
                    }
               }
	}
     }

     $cmds = $stock->getfacture($facture['numero'],"trspt");
     foreach($cmds as $items){
          foreach($items as $artid=>$item){
               $tabRow = array(
                    date("d/m/Y",$facture['date_facture']),
                    $numero_8chr,
                    $facture['numero'],
                    "",
                    "AC",
                    $tabCodesAnalytiques['trspt'].$codecee,
                    $codea,
                    round($item->paht,2)
               );
               $xl->addrow($tabRow);
               
               if($txtva>0){
                    $tabRow = array(
                         date("d/m/Y",
                         $facture['date_facture']),
                         $numero_8chr,
                         $facture['numero'],
                         "",
                         "AC",
                         $codeTVADefault,
                         $codea,
                         round($item->paht*$txtva,2)
                    );
                    $xl->addrow($tabRow);
               }
          }
     }

     $cmds=$stock->getfacture($facture['numero'],"cmdtrspt");
     foreach($cmds as $cmd){
          $tabRow = array(
               date("d/m/Y",$facture['date_facture']),
               $numero_8chr,
               $facture['numero'],
               "",
               "AC",
               $tabCodesAnalytiques['trspt'].$codecee,
               $codea,
               round($cmd->tariftrspt,2)
          );
          $xl->addrow($tabRow);
          
          if($txtva>0){
               $tabRow = array(
                    date("d/m/Y",$facture['date_facture']),
                    $numero_8chr,
                    $facture['numero'],
                    "",
                    "AC",
                    $codeTVADefault,
                    $codea,
                    round($cmd->tariftrspt*$txtva,2)
               );
               $xl->addrow($tabRow);
          }
     }
     
     $tabRow = array(
          date("d/m/Y",$facture['date_facture']),
          $numero_8chr,
          $facture['numero'],
          "",
          "AC",
          code401($facture['fournisseur']),
          "",
          "",
          $facture['montant_ttc'],
          "",
          date("d/m/Y",$facture['echeance'])
     );
     $xl->addrow($tabRow);
}

$xl->export();

function code607($type,$fourn=null){
    switch($type){
	case "avoir": return "607100";
	case "fourn": return "607100";
	case "trspt": return "607510";
	case "sls": return "607610";
	case "eco": return "607160";
	case "gar": return "607150";
	case "part":return "622500";
	case "com":
	$fourn=strtolower($fourn);
	switch($fourn){
	  case "rue du commerce": return "622210";
	  case "priceminister": return "622220";
	  case "amazon services europe": return "622270";
	  case "ebay europe sarl": return "622230";
	  case "pixmania": return "622240";
	  case "cdiscount": return "622245";
	  case "fnac marketplace": return "622295";
	  default: return "622210";
	}
	break;
    }
}


function code401($fournisseur){
     $requete=$DB_site->query("SELECT `code` FROM fournisseur WHERE societe='".addslashes($fournisseur)."' AND type='facture' AND `code`<>''");
     $f=$DB_site->fetch_array($requete);
     if($f==false) return "";

     return $f['code'];

}

function compte_analytique($prov,$paysid){
     $requete=$DB_site->query("SELECT * FROM compte_analytique WHERE site='$prov' AND paysid=".$paysid);
     $row=$DB_site->fetch_array($requete);
     return $row['code'];
}

function compte_analytique_mp($mpid){
     $requete=$DB_site->query("SELECT * FROM compte_analytique WHERE site='".$mpid."'");
     if($requete==false) return false;
     $row=$DB_site->fetch_array($requete);
     return $row['code'];
}

function codecee($fournisseur){
     global $tabCodesCEE;
     
     $sql = "SELECT p.*";
     $sql .= " FROM pays p";
     $sql .= " INNER JOIN fournisseur USING(paysid)";
     $sql .= " WHERE societe='".$DB_site->real_escape_string($fournisseur)."'";
     $sql .= " AND type='facture'";
     $requete = $DB_site->query($sql);
     if($requete == false){
          return "";
     }
     $pays = $DB_site->fetch_array($requete);
     if($pays==false){
          return $tabCodesCEE['france']; //France
     }

     if($pays['tvaintracom']==0) {
          if($pays['cee']==1){
               return $tabCodesCEE['cee_notva']; // france
          } else {
               return $tabCodesCEE['cee_tva']; // hors cee
          }
     }else{
          return $tabCodesCEE['hors_cee']; // cee sans tva
     }
}