<?php

ini_set("display_errors",1);
error_reporting(E_ALL);

$host="www.mon-espace-cuisson.com" ;

include_once("../../configurations/$host/config_mysql.php");
include("../../class/mysql.php");
include("../../class/stock.class.php");
include("../../class/devises.class.php");
include("xlexport.php");
include("ooo.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

$admin_username=isset($_GET['admin_username'])?$_GET['admin_username']:$_COOKIE['admin_username'];

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


$stock=new stock($DB_site);

$fromstr=isset($_GET['date']) ? $_GET['date'] : "first day of last month";

$from=new DateTime($fromstr);
$to=clone $from;
$to->modify("last day of this month 23:59:59");

$ooo=new ooo();
$ooo->name="reajustement_".$from->format("Ym");

$style_total=new ooo_style("total");
$style_total->family="table-cell";
$style_total->font_weight="bold";
$style_total->setborders("thin solid");
$ooo->addstyle($style_total);

$table_cell=new ooo_style("cell");
$table_cell->family="table-cell";
$table_cell->setborders("thin solid");
$ooo->addstyle($table_cell);

$ooo_1=new ooo_table("1");
$ooo_1->set_row(1,"8mm");

$c=new ooo_cell("1 - Factures FRN de commandes de stock avec produits entrés en stock le mois suivant");
$ooo_1->addcell(1,1,$c);

$cells=array();
$cells[]=new ooo_cell("Fourn","cell");
$cells[]=new ooo_cell("Date facture","cell");
$cells[]=new ooo_cell("N° facture","cell");
$cells[]=new ooo_cell("Montant HT facture","cell");
$cells[]=new ooo_cell("Cmd stock","cell");
$cells[]=new ooo_cell("Montant","cell");

$ooo_1->addrow(2,$cells);

$listcmd=$stock->facture_date($from->gettimestamp(),$to->gettimestamp());

$totalcmd=array();
foreach($listcmd as $cmdid=>$items){
    $cmd=new stock_cmd($DB_site,$cmdid);
    $items=$cmd->livraison_date($to->gettimestamp());

    if(count($items)>0){
        foreach($items as $item){
            $totalcmd[$item->facture][$item->stock_cmd_id]+=$item->qte*($item->ecopartht+$item->paht);
        }
    }
}

$i=3;
foreach($totalcmd as $numfacture=>$stock_cmds){
    $ff=$DB_site->query_first("SELECT * FROM facture_fournisseur WHERE numero='".mysqli_real_escape_string($DB_site->link_id,$numfacture)."'");
    foreach($stock_cmds as $stock_cmd_id=>$paht){
        $total1+=$paht;
        $cells=array();
        $cells[]=new ooo_cell($ff['fournisseur'],"cell");
        $cells[]=new ooo_cell($ff['date_facture']!=""?date("d/m/Y",$ff['date_facture']):"","cell");
        $cells[]=new ooo_cell($numfacture,"cell");
        $cells[]=new ooo_cell($ff['montant_ht'],"cell","currency");
        $cells[]=new ooo_cell($stock_cmd_id,"cell");
        $cells[]=new ooo_cell(round($paht,2),"cell","currency");
        $ooo_1->addrow($i,$cells);
        $i++;
    }
}


$c=new ooo_cell(round($total1,2),"total","currency");
$ooo_1->addcell(6,$i,$c);

$totalbycmd=array();
$factures=array();
$dateentree=array();
$list=$stock->livraison_items($from->gettimestamp(),$to->gettimestamp());

foreach($list as $item){
    if($item->facture==""){
        $totalbycmd[""][$item->stock_cmd_id]+=$item->paht;
    }else{
        if(!isset($factures[$item->facture])){
            $facture=$DB_site->query_first("SELECT date_facture,numero,fournisseur FROM facture_fournisseur WHERE numero='".mysqli_real_escape_string($DB_site->link_id,$item->facture)."'");
            $factures[$item->facture]=$facture;
        }

        $date_facture=$factures[$item->facture]['date_facture'];
        if($date_facture>$to->gettimestamp())
            $totalbycmd[$item->facture][$item->stock_cmd_id]+=$item->paht+$item->ecopartht;
    }
    $dateentree[$item->facture][$item->stock_cmd_id]=$item->date;
}

$ooo_2=new ooo_table("2");
$ooo_2->set_row(1,"8mm");
$c=new ooo_cell("2 - Commandes de stock avec produits entrés en stock sur le mois mais factures FRN non recues");
$ooo_2->addcell(1,1,$c);

$cells=array();
$cells[]=new ooo_cell("Cmd stock","cell");
$cells[]=new ooo_cell("Date entrée stock","cell");
$cells[]=new ooo_cell("Montant entré","cell");
$cells[]=new ooo_cell("Fourn","cell");
$cells[]=new ooo_cell("Date facture","cell");
$cells[]=new ooo_cell("N° facture","cell");
$ooo_2->addrow(2,$cells);

$i=3;
foreach($totalbycmd as $numfacture=>$stock_cmds){
    foreach($stock_cmds as $stock_cmd_id=>$paht){
        $cells=array();
        $cells[]=new ooo_cell($stock_cmd_id,"cell");
        $cells[]=new ooo_cell(date("d/m/Y",$dateentree[$numfacture][$stock_cmd_id]),"cell");
        $cells[]=new ooo_cell(round($paht,2),"cell","currency");
        $cells[]=new ooo_cell($factures[$numfacture]['fournisseur'],"cell");
        $cells[]=new ooo_cell($factures[$numfacture]['date_facture']>0?date("d/m/Y",$factures[$numfacture]['date_facture']):"","cell");
        $cells[]=new ooo_cell($numfacture,"cell");
        $ooo_2->addrow($i,$cells);
        $i++;
        $total2+=$paht;
    }
}

$c=new ooo_cell(round($total2,2),"total","currency");
$ooo_2->addcell(3,$i,$c);


$ooo_3=new ooo_table("3");
$ooo_3->set_row(1,"8mm");
$c=new ooo_cell("3 - Toutes les factures client des 6 derniers mois pour lequel on n'a pas recu de factures fournisseurs");
$ooo_3->addcell(1,1,$c);
$cells=array();
$cells[]=new ooo_cell("Facture client","cell");
$cells[]=new ooo_cell("Date facture","cell");
$cells[]=new ooo_cell("Cmd client","cell");
$cells[]=new ooo_cell("Fournisseur","cell");
$cells[]=new ooo_cell("Montant","cell");
$ooo_3->addrow(2,$cells);


$from3str=isset($_GET['date']) ? $_GET['date'] : "first day of ".date("F Y",mktime(0,0,0,date("m")-6));
$from3=new DateTime($from3str);


$total3=0;
$i=3;
$sql="SELECT facturid, numerofacture, UNIX_TIMESTAMP(dateexpedition) AS date, ef.destinataire, efl.qte, efl.paht, efl.ecopart, efl.facture FROM facture
	INNER JOIN envoi_fournisseur ef USING(facturid)
	INNER JOIN envoi_fournisseur_ligne efl ON(efl.efid=ef.id)
	WHERE dateexpedition BETWEEN '".$from3->format("Y-m-d")."' AND '".$to->format("Y-m-d")."'
	AND efl.facture!='STOCK'
	AND ef.destinataire NOT IN ('0','')";

$req=$DB_site->query($sql);
while($ef=$DB_site->fetch_array($req)){
    $totalligne=$ef['qte']*($ef['paht']+$ef['ecopart']);
    if($ef['facture']==""){
        $cells=array();
        $cells[]=new ooo_cell($ef['numerofacture'],"cell");
        $cells[]=new ooo_cell(date("d/m/Y",$ef['date']),"cell");
        $cells[]=new ooo_cell($ef['facturid'],"cell");
        $cells[]=new ooo_cell($ef['destinataire'],"cell");
        $cells[]=new ooo_cell(round($totalligne,2),"cell","currency");
        $ooo_3->addrow($i,$cells);
        $i++;
        $total3+=$totalligne;
    }else{

        $facture=$DB_site->query_first("SELECT date_facture FROM facture_fournisseur WHERE numero='".mysqli_real_escape_string($DB_site->link_id,$ef['facture'])."'");
        if($facture['date_facture']>$to->gettimestamp()){
            $cells=array();
            $cells[]=new ooo_cell($ef['numerofacture'],"cell");
            $cells[]=new ooo_cell(date("d/m/Y",$ef['date']),"cell");
            $cells[]=new ooo_cell($ef['facturid'],"cell");
            $cells[]=new ooo_cell($ef['destinataire'],"cell");
            $cells[]=new ooo_cell(round($totalligne,2),"cell","currency");
            $ooo_3->addrow($i,$cells);
            $i++;
            $total3+=$totalligne;
        }
    }
}

$c=new ooo_cell(round($total3,2),"total","currency");
$ooo_3->addcell(5,$i,$c);



$ooo_4=new ooo_table("4");
$ooo_4->set_row(1,"8mm");
$c=new ooo_cell("4 - Commandes non facturées au client mais commandes fournisseurs facturées");
$ooo_4->addcell(1,1,$c);
$cells=array();
$cells[]=new ooo_cell("Fournisseur","cell");
$cells[]=new ooo_cell("Date facture fourn.","cell");
$cells[]=new ooo_cell("N° facture fourn.","cell");
$cells[]=new ooo_cell("Montant","cell");
$cells[]=new ooo_cell("Cmd client","cell");
$ooo_4->addrow(2,$cells);

$total4=0;
$i=3;
$sql="SELECT ef.facturid,ef.date,ef.destinataire,efl.facture,SUM(efl.qte*efl.paht) AS total,ff.date_facture
FROM envoi_fournisseur ef INNER JOIN envoi_fournisseur_ligne efl ON(efl.efid=ef.id) INNER JOIN facture_fournisseur ff ON (ff.numero=efl.facture)
WHERE facture!='' AND date_facture BETWEEN ".$from->gettimestamp()." AND ".$to->gettimestamp()."
GROUP BY facture,facturid";

$req=$DB_site->query($sql);
while($ef=$DB_site->fetch_array($req)){
    $facture=$DB_site->query_first("SELECT valide FROM facture WHERE facturid=".$ef['facturid']);
    if($facture['valide']!=5 && $facture['valide']!=8){
        $cells=array();
        $cells[]=new ooo_cell($ef['destinataire'],"cell");
        $cells[]=new ooo_cell(date("d/m/Y",$ef['date_facture']),"cell");
        $cells[]=new ooo_cell($ef['facture'],"cell");
        $cells[]=new ooo_cell(round($ef['total'],2),"cell","currency");
        $cells[]=new ooo_cell($ef['facturid'],"cell");
        $ooo_4->addrow($i,$cells);
        $total4+=$ef['total'];
        $i++;
    }
}
$c=new ooo_cell(round($total4,2),"total","currency");
$ooo_4->addcell(4,$i,$c);

$ooo_recap=new ooo_table("Récap");
$ooo_recap->set_col(1,"12cm");

$c=new ooo_cell($from->format("m/Y"));
$ooo_recap->addcell(1,1,$c);

$cells=array();
$cells[]=new ooo_cell("1 - Factures FRN de commandes de stock avec produits entrés en stock le mois suivant","cell");
$cells[]=new ooo_cell(round($total1,2),"cell","currency");
$ooo_recap->addrow(2,$cells);

$cells=array();
$cells[]=new ooo_cell("2 - Commandes de stock avec produits entrés en stock sur le mois mais factures FRN non recues","cell");
$cells[]=new ooo_cell(round(-$total2,2),"cell","currency");
$ooo_recap->addrow(3,$cells);

$cells=array();
$cells[]=new ooo_cell("3 - Toutes les factures client des 6 derniers mois pour lequel on a pas recu de factures fournisseurs","cell");
$cells[]=new ooo_cell(round(-$total3,2),"cell","currency");
$ooo_recap->addrow(4,$cells);

$cells=array();
$cells[]=new ooo_cell("4 - Commandes non facturées au client mais commandes fournisseurs facturées","cell");
$cells[]=new ooo_cell(round($total4,2),"cell","currency");
$ooo_recap->addrow(5,$cells);

$c=new ooo_cell(round($total1-$total2-$total3+$total4,2),"total","currency");
$ooo_recap->addcell(2,6,$c);

$ooo->addtable($ooo_recap);
$ooo->addtable($ooo_1);
$ooo->addtable($ooo_2);
$ooo->addtable($ooo_3);
$ooo->addtable($ooo_4);

$ooo->generate();

?>