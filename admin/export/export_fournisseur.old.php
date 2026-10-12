<?php
ini_set("display_errors","on");
$host="www.mon-espace-cuisson.com" ;

extract($_REQUEST);
extract($_COOKIE);

include_once("../../configurations/$host/config_mysql.php");
include("../../class/mysql.php");
include("../../class/stock.class.php");
include("../../class/devises.class.php");
include_once("zip.lib.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

if (isset($admin_username) && $admin_username != "") {
    if($admin_username!="9d3bb895f22bf0afa958d68c2a58ded7"){
      $ip=$_SERVER['REMOTE_ADDR'];
      $acces=$DB_site->query_first("SELECT * FROM acces_admin WHERE ip='$ip' AND user='$admin_username' AND connecte=1 ORDER BY timestamp DESC LIMIT 0,1");
      if($acces['timestamp']==false || $acces['timestamp']<time()-3600){
         exit;
      }else{
        $user_info=$DB_site->query_first("SELECT * FROM parametresgeneraux WHERE username = '$admin_username'");
        if ($user_info[username] != $admin_username) exit;
      }
    }
}else
    exit;

include("xlexport.php");
include("../marketplace/order.php");

if(!isset($_GET['du']) || !isset($_GET['au'])) exit;

$sp=explode("/",$_GET['du']);
$du=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
$sp2=explode("/",$_GET['au']);
$au=mktime(23,59,59,$sp2[1],$sp2[0],$sp2[2]);

$code_comptable=array();
$req=$DB_site->query("SELECT * FROM code_comptable");
while($cc=$DB_site->fetch_array($req)){
    $code_comptable[$cc['type']][strtolower($cc['fournisseur'])]=$cc['code'];
}

$devise=new devises($DB_site);

$xl=new xlexport("csv");
$xl->name="compta_fournisseur_mcav_".date("Ymd",$du)."-".date("Ymd",$au);
$xl->addrow(array("Date facture","N° facture","Libellé pièce","N° commande","Code journal","N° compte","Code TVA","Détail des ligne","Montant payé","Code analytique","Date échéance"));

$requete=$DB_site->query("SELECT * FROM facture_fournisseur ff, fournisseur_condition fc WHERE fc.societe = ff.fournisseur AND date_saisie BETWEEN $du AND $au AND fc.societe_facturation_id = 1 ORDER BY date_facture");
while($facture=$DB_site->fetch_array($requete)){

    $nom=$facture['fournisseur'];
    $txtva=(floatval($facture['tva'])/100);
    $code401=code401($facture['fournisseur']);
    $codea=codea($facture['fournisseur'], $facture['tva']);
    $codecee=codecee($nom);
    $numero_8chr=substr($facture['numero'],-8);
    $total_tva = 0;
    if($facture['devise']=="") $facture['devise']="EUR";    
    
    $r2=$DB_site->query("SELECT * FROM envoi_fournisseur_ligne WHERE facture='".$facture['numero']."'");
    while($ligne=$DB_site->fetch_array($r2)){
		$lignefacture=$DB_site->fetch_array($DB_site->query("SELECT u.paysid,f.moyenid,f.facturid,f.numerofacture,UNIX_TIMESTAMP(f.dateexpedition) AS dateexpedition,f.provenanceid,f.provenance FROM lignefacture lf, facture f, utilisateur u WHERE u.userid=f.userid AND f.facturid=lf.factureid AND lignefactureid=".$ligne['ligne_facture_id']));

		 if($lignefacture['provenance']=='mp'){
		    $compte_ana=compte_analytique($lignefacture['provenanceid'],$lignefacture['paysid']);
		 }else
		    $compte_ana=compte_analytique("macaveavin",$lignefacture['paysid']);
		
                if($lignefacture['dateexpedition']>0){
                    $factureclient=$lignefacture['facturid']." Fa N° ".$lignefacture['numerofacture']." du ".date("d/m/Y",$lignefacture['dateexpedition']);
                }else{
                    $factureclient=$lignefacture['facturid']." sans correspondance facture client";
                }
		 $dateexpedition="";
		 if($lignefacture['dateexpedition']>0) $dateexpedition=date("d/m/Y",$lignefacture['dateexpedition']);
		 
		 if($ligne['paht']!=0){
		    $ligne['paht']=$devise->in_euro($ligne['paht'],$facture['devise'],"compta","achat");
		    $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$numero_8chr,$facture['numero'],$factureclient,"AC",code607($ligne['type']).$codecee,$codea,round($ligne['qte']*$ligne['paht'],2),"",$compte_ana,date("d/m/Y",$facture['echeance']))); 
		    if($txtva>0){ 
		    	$xl->addrow(array(date("d/m/Y",$facture['date_facture']),$numero_8chr,$facture['numero'],$factureclient,"AC","445660",$codea,round($ligne['qte']*$ligne['paht']*$txtva,2),"",$compte_ana,date("d/m/Y",$facture['echeance'])));
			} else {
				$total_tva += $ligne['qte']*$ligne['paht'];
			} 
		 }
		 if($ligne['ecopart']!=0){
		    $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$numero_8chr,$facture['numero'],$factureclient,"AC",code607("eco").$codecee,$codea,round($ligne['qte']*$ligne['ecopart'],2),"",$compte_ana,date("d/m/Y",$facture['echeance'])));
		    if($txtva>0){
		    	$xl->addrow(array(date("d/m/Y",$facture['date_facture']),$numero_8chr,$facture['numero'],$factureclient,"AC","445660",$codea,round($ligne['qte']*$ligne['ecopart']*$txtva,2),"",$compte_ana,date("d/m/Y",$facture['echeance'])));
		    } else {
		    	$total_tva += $ligne['qte']*$ligne['ecopart'];
		    }
		 }
    }
    /*$r2=$DB_site->query("SELECT * FROM mp_envoi_fournisseur_ligne WHERE facture='".$facture['numero']."'");
    while($ligne=$DB_site->fetch_array($r2)){
	 $lignefacture=$DB_site->fetch_array($DB_site->query("SELECT o.marketplace,o.id,o.billing_number,UNIX_TIMESTAMP(o.billing_date) AS billing_date FROM mp_order_line ol, mp_orders o WHERE o.id=ol.order AND ol.id='".$ligne['ligne_facture_id']."'"));
	 $compte_ana=compte_analytique_mp($lignefacture['marketplace']);
	 $factureclient=$lignefacture['id']." Fa N° ".$lignefacture['billing_number']." du ".date("d/m/Y",$lignefacture['billing_date']);
	 $dateexpedition="";
	 if($lignefacture['billing_date']>0) $dateexpedition=date("d/m/Y",$lignefacture['billing_date']);
	 $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$facture['numero'],$facture['numero'],$factureclient,"AC",code607($ligne['type']),"",round($ligne['qte']*$ligne['paht'],2),"",$compte_ana,"",$dateexpedition)); 
	 $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$facture['numero'],$facture['numero'],$factureclient,"AC","445660","",round($ligne['qte']*$ligne['paht']*$txtva,2),"",$compte_ana,"",$dateexpedition));
	 if($ligne['ecopart']!=0){
	    $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$facture['numero'],$facture['numero'],$factureclient,"AC",code607("eco"),"",round($ligne['qte']*$ligne['ecopart'],2),"",$compte_ana,"",$dateexpedition));
	    $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$facture['numero'],$facture['numero'],$factureclient,"AC","445660","",round($ligne['qte']*$ligne['ecopart']*$txtva,2),"",$compte_ana,"",$dateexpedition));
	 }
    }*/
    
    $r2=$DB_site->query("SELECT * FROM facture_fournisseur_autre WHERE facture='".$facture['numero']."'");
    while($ligne=$DB_site->fetch_array($r2)){
		$compte_ana="";
		$fclient="";
		$dateexpedition="";
		$factureclient=array();
		if($ligne['facturid']!=""){
		    $fclient="";
		    if(strlen($ligne['facturid'])==4){
				$stock_cmd=new stock_cmd($DB_site,$ligne['facturid']);
				if(!is_null($stock_cmd->id)) $fclient="STOCK N°".$stock_cmd->id;
		    }else{
				$factureclient=$DB_site->query_first("SELECT u.paysid,f.facturid,f.numerofacture,UNIX_TIMESTAMP(f.dateexpedition) AS dateexpedition,f.provenance,f.provenanceid FROM facture f, utilisateur u WHERE u.userid=f.userid AND f.facturid='".intval($ligne['facturid'])."'");
				if($factureclient['facturid']==""){
				  $factureclient=$DB_site->query_first("SELECT * FROM mp_orders WHERE id='".$ligne['facturid']."'");
				  if($factureclient['facturid']!="") $compte_ana=compte_analytique_mp($factureclient['marketplace']);

				}else{
				  $prov="macaveavin";
				  if($factureclient['provenance']=="mp") $prov=$factureclient['provenanceid'];
				  $compte_ana=compte_analytique($prov,$factureclient['paysid']);
				  $fclient=$factureclient['facturid']." Fa N° ".$factureclient['numerofacture']." du ".date("d/m/Y",$factureclient['dateexpedition']);
				  if($factureclient['dateexpedition']>0) $dateexpedition=date("d/m/Y",$factureclient['dateexpedition']);
				}
		    }
		}else
		    $compte_ana=1;


		if($ligne['prix']!=0){
		    $ligne['prix']=$devise->in_euro($ligne['prix'],$facture['devise'],"compta","achat");
		    $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$numero_8chr,$facture['numero'],$fclient,"AC",code607($ligne['type'],$facture['fournisseur']).$codecee,$codea,round($ligne['prix'],2),"",$compte_ana,date("d/m/Y",$facture['echeance']))); 
		    if($txtva>0){
		    	$xl->addrow(array(date("d/m/Y",$facture['date_facture']),$numero_8chr,$facture['numero'],$fclient,"AC","445660",$codea,round($ligne['prix']*$txtva,2),"",$compte_ana,date("d/m/Y",$facture['echeance'])));
		    } else {
		    	$total_tva += $ligne['prix'];
		    }
		}
	 //if($ligne['ecopart']!=0) $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$facture['numero'],$lignefacture['facturid'],"AC",code607("eco"),"A2",$ligne['ecopart'],"",$compte_ana));
    }

    $stock=new stock($DB_site);
    $cmds=$stock->getfacture($facture['numero'],"fourn");
    foreach($cmds as $items){
	foreach($items as $artid=>$item){
	    $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$numero_8chr,$facture['numero'],"STOCK N°".$item->stock_cmd_id,"AC","607100".$codecee,$codea,round($item->paht,2),"",19,date("d/m/Y",$facture['echeance'])));
	    if($txtva>0) $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$numero_8chr,$facture['numero'],"STOCK N°".$item->stock_cmd_id,"AC","445660",$codea,round($item->paht*$txtva,2),"",19,date("d/m/Y",$facture['echeance'])));
	    if($item->ecopartht>0){
	      $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$numero_8chr,$facture['numero'],"STOCK N°".$item->stock_cmd_id,"AC",code607("eco").$codecee,$codea,round($item->ecopartht,2),"",19,date("d/m/Y",$facture['echeance'])));
	      if($txtva>0) $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$numero_8chr,$facture['numero'],"STOCK N°".$item->stock_cmd_id,"AC","445660",$codea,round($item->ecopartht*$txtva,2),"",19,date("d/m/Y",$facture['echeance'])));
	    }
	}
    }

    $cmds=$stock->getfacture($facture['numero'],"trspt");
    foreach($cmds as $items){
	foreach($items as $artid=>$item){
	    $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$numero_8chr,$facture['numero'],"","AC","607510".$codecee,$codea,round($item->paht,2),"","",date("d/m/Y",$facture['echeance'])));
	    if($txtva>0) $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$numero_8chr,$facture['numero'],"","AC","445660",$codea,round($item->paht*$txtva,2),"","",date("d/m/Y",$facture['echeance'])));
	}
    }

    $cmds=$stock->getfacture($facture['numero'],"cmdtrspt");
    foreach($cmds as $cmd){
	$xl->addrow(array(date("d/m/Y",$facture['date_facture']),$numero_8chr,$facture['numero'],"","AC","607510".$codecee,$codea,round($cmd->tariftrspt,2),"","",date("d/m/Y",$facture['echeance'])));
	if($txtva>0) $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$numero_8chr,$facture['numero'],"","AC","445660",$codea,round($cmd->tariftrspt*$txtva,2),"","",date("d/m/Y",$facture['echeance'])));
    }

    if($total_tva > 0){
    	$total_tva = $total_tva * 0.2;
    	$xl->addrow(array(date("d/m/Y",$facture['date_facture']),$numero_8chr,$facture['numero'],$fclient,"AC","4456800000",$codea,round($total_tva,2),"","",date("d/m/Y",$facture['echeance'])));
    	$xl->addrow(array(date("d/m/Y",$facture['date_facture']),$numero_8chr,$facture['numero'],$fclient,"AC","4457800000",$codea,"",round($total_tva,2),"",date("d/m/Y",$facture['echeance'])));
    }

    $ttcorigin="";
    if($facture['devise']!="EUR") $ttcorigin=number_format($facture['montant_ttc'],2,',','')." ".$facture['devise'];
    $facture['montant_ttc']=$devise->in_euro($facture['montant_ttc'],$facture['devise'],"compta","achat");
    $xl->addrow(array(date("d/m/Y",$facture['date_facture']),$numero_8chr,$facture['numero'],$ttcorigin,"AC",code401($facture['fournisseur']),"","",$facture['montant_ttc'],"",date("d/m/Y",$facture['echeance'])));
}

$xl->export();

function code607($type,$fourn=null){
    global $code_comptable;

    if(is_null($fourn)) $fourn="";
    $fourn=strtolower($fourn);
    return isset($code_comptable[$type][$fourn]) ? $code_comptable[$type][$fourn] : $code_comptable[$type][""];
}


function code401($fournisseur){
    global $DB_site;
  $requete=$DB_site->query("SELECT `code` FROM fournisseur WHERE societe='".addslashes($fournisseur)."' AND type='facture' AND `code`<>''");
  $f=$DB_site->fetch_array($requete);
  if($f==false) return "";

  return $f['code'];

}

function compte_analytique($prov,$paysid){
    global $DB_site;
	if($paysid=="") return "";
        $requete=$DB_site->query("SELECT * FROM compte_analytique WHERE site='$prov' AND paysid=".$paysid);
	$row=$DB_site->fetch_array($requete);
	return $row['code'];
}

function compte_analytique_mp($mpid){
    global $DB_site;
	$requete=$DB_site->query("SELECT * FROM compte_analytique WHERE site='".$mpid."'");
	if($requete==false) return false;
	$row=$DB_site->fetch_array($requete);
	return $row['code'];
}

function codecee($fournisseur){
    global $DB_site;
    $requete=$DB_site->query("SELECT p.* FROM pays p INNER JOIN fournisseur USING(paysid) WHERE societe='".mysqli_real_escape_string($DB_site->link_id,$fournisseur)."' AND type='facture'");
    if($requete==false) return "";
    $pays=$DB_site->fetch_array($requete);
    if($pays==false) return "0000";

    if($pays['tvaintracom']==0) {
      if($pays['cee']==1)
	return "0000"; // france
      else
	return "0999"; // hors cee
    }else
      return "0099"; // cee sans tva
	
}

function codea($fournisseur, $tva){
    global $DB_site;
	$codesa = array("0"=>"A4","19.6"=>"A2","20"=>"A5");

	if($tva == 0){

		$return = '';

		$sql = "SELECT p.*";
		$sql .= " FROM pays p";
		$sql .= " INNER JOIN fournisseur USING(paysid)";
		$sql .= " WHERE societe = '".mysqli_real_escape_string($DB_site->link_id,$fournisseur)."'";
		$sql .= " AND type = 'facture'";
		$requete = $DB_site->query($sql);
		if($requete){
			$pays = $DB_site->fetch_array($requete);
			if($pays && $pays['cee']==1){
				return $codesa[$tva];
			}
		}
	} else {

		$return = $codesa[$tva];

	}

	return $return;
}

?>
