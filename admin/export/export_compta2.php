<?php
$document_root = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING);
$admin_username = filter_input(INPUT_COOKIE, 'admin_username', FILTER_SANITIZE_STRING);
if(empty($admin_username)){
     $admin_username = filter_input(INPUT_GET, 'admin_username', FILTER_SANITIZE_STRING);
}

ini_set("display_errors","on");

require_once($document_root.'/configurations/www.mon-espace-cuisson.com/config_mysql.php');
require_once($document_root.'/class/mysql.php');
require_once($document_root.'/class/facture.class.php');
require_once($document_root.'/class/stock.class.php');
require_once($document_root.'/admin/export/zip.lib.php');

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

require_once($document_root.'/admin/export/xlexport.php');
//require_once($document_root.'/admin/marketplace2/order.php');

//Paramétrages
$nom_fichier = 'acilyondev-clients';
$provenance = 'monespacecuisson';
$tabColonnes = array(
    "Date facture",
    "N° facture",
    "Libellé pièce",
    "N° commande",
    "code journal",
    "N° compte",
    "Code TVA",
    "Montant payé",
    "Détail des ligne",
    "Code analytique"
);
$tabCodesAnalytiques = array(
    'compensation'  => '708516',
    'port'          => '707610', //Montage mon espace cuisson
    'port_reel'     => '707510', //Port mon espace cuisson
    //'service'       => '707150', //Prestation de mise en service mon espace cuisson
    'avoir'         => '707190',
    'tva755'        => '707170',
    'eco'           => '707160', //Eco part mon espace cuisson
    'garantie'      => '707150', //Garantie mon espace cuisson
    'default'       => '707100'  //Vente mon espace cuisson
);
$tabCodesTVA = array(
     '20' => array(
        'code_tva' => '4457718',
        'code_v'   => 'V8'
     ),
     '19.6' => array(
        'code_tva' => '445710',
        'code_v'   => 'V2'
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
$sp = explode("/",$_GET['au']);
$au = mktime(23,59,59,$sp[1],$sp[0],$sp[2]);

$xl = new xlexport("csv");
$xl->name = $nom_fichier."_".date("Ymd",$du)."-".date("Ymd",$au);
$xl->addrow($tabColonnes);

$sql = "SELECT *, UNIX_TIMESTAMP(f.datefacture) AS datecde, UNIX_TIMESTAMP(f.dateexpedition) AS datef";
$sql .= " FROM facture f";
$sql .= " WHERE f.valide IN (5,8)";
$sql .= " AND UNIX_TIMESTAMP(f.dateexpedition) BETWEEN '".$du."' AND '".$au."'";
$sql .= " ORDER BY f.dateexpedition";
$requete = $DB_site->query($sql);
// $requete=$DB_site->query("SELECT *,UNIX_TIMESTAMP(f.datefacture) AS datecde,UNIX_TIMESTAMP(f.dateexpedition) AS datef FROM facture f WHERE f.valide IN (8) AND UNIX_TIMESTAMP(f.dateexpedition) BETWEEN 1304281709 AND 1309471199 ORDER BY f.dateexpedition");
while($facture=$DB_site->fetch_array($requete)){
     $f=new facture($DB_site,$facture);
     $totalttc=0;
     $mpfacturid="";
     $totaltx=array();
     $nom=$f->utilisateur->nom." ".$f->utilisateur->diminutif.$f->utilisateur->codepostal;
     $num=$f->numerofacture;
     if($f->provenance=="mp") $num=$f->numerofacture."/".$f->mp_facturid;

     $codecee = codecee($f->utilisateur);
     if($facture['provenance']=='mp'){
          $compte_ana = compte_analytique($facture['provenanceid'], $f->utilisateur->paysid);
     }else{
          $compte_ana = compte_analytique($provenance, $f->utilisateur->paysid);
     }

//     $reqlignes=$DB_site->query("SELECT * FROM lignefacture WHERE factureid=".$facture['facturid']);
//     while($ligne=$DB_site->fetch_array($reqlignes)){
     foreach($f->lignes as $ligne){
          switch($ligne->artcode){
               case "port":
               case "compensation":
               case "avoir":
                    $code700art = $tabCodesAnalytiques[$ligne->artcode];
                    break;
               default:
                    if($f->utilisateur->code411!="" && $f->provenance=="sav"){
                         $code700art = $tabCodesAnalytiques['port'];
                    }else{
                         $code700art = $tabCodesAnalytiques['default'];
                    }
                    break;
          }

          $code700eco = $tabCodesAnalytiques['eco'];
          $code700gar = $tabCodesAnalytiques['garantie'];
          
          switch($ligne->tva){
               case "20":
               case "19.6":
               case "0":
                    $codetva = $tabCodesTVA[$ligne->tva]['code_tva'];
                    $codev = $tabCodesTVA[$ligne->tva]['code_v'];
                    break;
               case "7":
               case "5.5":
                    $code700art = $tabCodesAnalytiques['tva755'];
                    $codetva = $tabCodesTVA[$ligne->tva]['code_tva'];
                    $codev = $tabCodesTVA[$ligne->tva]['code_v'];
                    break;
    }

          $sql = "SELECT *, m.libelle AS marque";
          $sql .= " FROM article a, marque m, article_marque am";
          $sql .= " WHERE am.artid = a.artid";
          $sql .= " AND am.marqueid = m.marqueid";
          $sql .= " AND a.artid=".$ligne->artid;
    $article = $DB_site->fetch_array($DB_site->query($sql));
          
          $sql="SELECT SUM(differenceprix) FROM lignefacturegarantie WHERE lignefactureid=".$ligne->lignefactureid;
          $prixgarantie = $DB_site->fetch_array($DB_site->query($sql));
          
          $txtva = $ligne->tva/100;
          $ecopart = $ligne->qte*$article['ecopartht'];
    $garantie = $ligne->qte*($prixgarantie[0]/(1+$txtva));
          $totalht = $ligne->qte*($ligne->prix/(1+$txtva))-$ecopart-$garantie;
    $totaltx[$ligne->tva] += $ligne->prix*$ligne->qte;

    if($totalht!=0){
               $xl->addrow(array(date("d/m/Y",$f->dateexpedition),$f->numerofacture,$num,$f->facturid,"VE",$code700art.$codecee,$codev,"",round($totalht,2),$compte_ana));
               if($txtva>0) $xl->addrow(array(date("d/m/Y",$f->dateexpedition),$f->numerofacture,$num,$f->facturid,"VE",$codetva,$codev,"",round($totalht*$txtva,2),$compte_ana));
    }
          if($ecopart!=0){
               $xl->addrow(array(date("d/m/Y",$f->dateexpedition),$f->numerofacture,$num,$f->facturid,"VE",$code700eco.$codecee,$codev,"",round($ecopart,2),$compte_ana));
               $xl->addrow(array(date("d/m/Y",$f->dateexpedition),$f->numerofacture,$num,$f->facturid,"VE",$codetva,$codev,"",round($ecopart*$txtva,2),$compte_ana));
    }
    if($garantie!=0){
               $xl->addrow(array(date("d/m/Y",$f->dateexpedition),$f->numerofacture,$num,$f->facturid,"VE",$code700gar.$codecee,$codev,"",round($garantie,2),$compte_ana));
               $xl->addrow(array(date("d/m/Y",$f->dateexpedition),$f->numerofacture,$num,$f->facturid,"VE",$codetva,$codev,"",round($garantie*$txtva,2),$compte_ana));
    }
     }

     if($f->cadeauht!=0){
//       foreach($totaltx as $tx=>$ttc){
          foreach($f->partcadeau as $tx=>$ttc){
               $cadeauht = round($ttc/(1+$tx/100),2);
               $tvaremise = $ttc-$cadeauht;
               $codev = $tabCodesTVA[$tx]['code_v'];
               
               $xl->addrow(array(date("d/m/Y",$f->dateexpedition),$f->numerofacture,$num,$f->facturid,"VE",$code700art.$codecee,$codev,"",-round($cadeauht,2),$compte_ana));
               $xl->addrow(array(date("d/m/Y",$f->dateexpedition),$f->numerofacture,$num,$f->facturid,"VE","4457718",$codev,"",-round($tvaremise,2),$compte_ana));
          }
     }

    $code700port = $tabCodesAnalytiques['port_reel'];
    
     if($f->portht!=0){
          $xl->addrow(array(date("d/m/Y",$f->dateexpedition),$f->numerofacture,$num,$f->facturid,"VE",$code700port.$codecee,$codev,"",round($f->portht,2),$compte_ana));
          if($f->porttva>0){
               $xl->addrow(array(date("d/m/Y",$f->dateexpedition),$f->numerofacture,$num,$f->facturid,"VE","4457718",$codev,"",round($f->portttc-$f->portht,2),$compte_ana));
          }
     }

    if($f->moyenid==99){
    $reglements=$f->getReglements();
    foreach($reglements as $reglement)
        $xl->addrow(array(date("d/m/Y",$f->dateexpedition),$f->numerofacture,$num,$f->facturid,"VE",code411($f,$reglement['reglement_moyen']),$codev,$reglement['reglement_montant'],"",""));
    }else
    $xl->addrow(array(date("d/m/Y",$f->dateexpedition),$f->numerofacture,$num,$f->facturid,"VE",code411($f),$codev,$f->totalttc,"",""));
    
    $xl->addrow(array());
}

$xl->export();

function compte_analytique($prov,$paysid){
     global $DB_site;
     $sql = "SELECT *";
     $sql .= " FROM compte_analytique";
     $sql .= " WHERE site = '".$prov."'";
     $sql .= " AND paysid = '".$paysid."'";
     $requete = $DB_site->query($sql);
     $row = $DB_site->fetch_array($requete);
     return $row['code'];
}

function compte_analytique_mp($mpid,$paysid){
     global $DB_site;
     $sql = "SELECT *";
     $sql .= " FROM compte_analytique";
     $sql .= " WHERE site = '".$mpid."'";
     $sql .= " AND paysid = '".$paysid."'";
     $requete = $DB_site->query($sql);
     $row = $DB_site->fetch_array($requete);
     return $row['code'];
}

function code411($facture,$moyenid=null){
     if($facture->utilisateur->code411!=null){
          return $facture->utilisateur->code411;
     }
     if($facture->provenance=="mp"){
          return code411_mp($facture->provenanceid);
     }
     if(is_null($moyenid)) $moyenid=$facture->moyenid;

     switch($moyenid){
    case 1:  //CB
               return "4119000200";
    case 2:     // cheque
               return "4119000300";
    case 3:     // contre remb
    case 4:     // mandat
               return "4119000900";
    case 5:     // virement
               return "4119000800";
    case 6:     // paypal
               return "4119000600";
    case 7:     // espèce
    case 8:     // receiveandpay
    case 9:     // receiveandpay en plusieurs fois
               return "411MECRAP";
    case 10:    // CB 3 fois
    case 11:    // 
    case 12:    // carte aurore
               return "411MECAUR";
    case 13:
               return "4119000100";
        case 14:
        case 15:
        return "411MECLOC";
    case 16:
    case 17:
        return "4119000700";
     }

}

function code411_mp($mpid){
     switch($mpid){
          case "01": //Amazon
               return "4119005000";
          case "04": //Price Minister
               return "4119005700";
          case "05": //Pixmania
               return "4119005600";
          case "06": //Windill
               return "411CVWIN";
          case "07": //Rue du Commerce
               return "4119005800";
          case "08": //Edengo
          case "09": //Ebuyclub
               return "";
          case "10": //Ebay
               return "411CVEB";
          case "11": // guide de la maison
               return "411CVGM";
          case "12": //fnac
               return "4119005300";
          case "13": //cdiscount
               return "4119005100";
     }
}

function codecee($utilisateur){
     global $tabCodesCEE,$DB_site;
     
     $sql = "SELECT *";
     $sql .= " FROM pays";
     $sql .= " WHERE paysid = ".$utilisateur->paysid;
     $requete = $DB_site->query($sql);
     $pays = $DB_site->fetch_array($requete);
     
     if($pays['tvaintracom']==0 && $pays['cee']==1){
          return $tabCodesCEE['france']; // france
     }
     if($pays['tvaintracom']==1 && $pays['cee']==1){
       if(trim($utilisateur->tvaintracom)!=""){
               return $tabCodesCEE['cee_notva']; // cee sans tva
       }else{
               return $tabCodesCEE['cee_tva']; // cee avec tva
       }
     }
     if($pays['cee']==0) return $tabCodesCEE['hors_cee']; // hors cee
}