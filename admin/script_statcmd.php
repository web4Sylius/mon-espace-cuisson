<?php

ini_set("display_errors","on");

$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
$host="www.ma-cave-a-vin.fr" ;

include_once($rootpath."configurations/$host/config_mysql.php");
include_once($rootpath."configurations/$host/config.php");
include($rootpath."class/mysql.php");
include_once($rootpath."admin/email.php");


$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

$today=mktime(0,0,0,date("m"),date("d"),date("Y"));
$yesterday=$today-86400;
$month=mktime(0,0,0,date("m"),1,date("Y"));

calc($yesterday,$today);
$obj=$objectif_facture[date("Y")][date("n")]/date("t");
$totalca=array_sum($ca);
$pcobj=100*$totalca/$obj-100;
$html="<p><b>Commandes du ".date("d/m/Y",$yesterday)."</b></p>";
$html.="<div>Nombre : ".array_sum($nb)."<br>CA HT : ".number_format($totalca,2,',','')."&euro;<br>Objectif : ".number_format($obj,2,',','')."&euro; <font color=\"".($pcobj>0?"green":"red")."\">".($pcobj>0?"+":"").number_format($pcobj,2,',','')."%</font></div>";

if(date("d")==1) $month=mktime(0,0,0,date("m",$yesterday),1,date("y",$yesterday));
calc($month,$today);
$totalca=array_sum($ca);
$obj=$objectif_facture[date("Y")][date("n")];
$pcobj=100*$totalca/$obj-100;
$obj2=date("d",$yesterday)*$objectif_facture[date("Y")][date("n")]/date("t");
$pcobj2=100*$totalca/$obj2-100;
$html.="<p><b>Commandes du mois en cours</b></p>";
$html.="<div>Nombre : ".array_sum($nb)."
<br>CA HT : ".number_format($totalca,2,',','')."&euro;
<br>Objectif en cours : ".number_format($obj2,2,',','')."&euro; <font color=\"".($pcobj2>0?"green":"red")."\">".($pcobj2>0?"+":"").number_format($pcobj2,2,',','')."%</font>
<br>Objectif du mois : ".number_format($obj,2,',','')."&euro; <font color=\"".($pcobj>0?"green":"red")."\">".($pcobj>0?"+":"").number_format($pcobj,2,',','')."%</font></div>";

$data="";
foreach($ca as $ts=>$value) $data.=",".$ts.":".intval($value);
$data=substr($data,1);
$html.="<br><img src=\"http://ma-cave-a-vin.fr/admin/ccgraph.php?data=$data&width=600height=400&abstype=ts&title=Commandes%20".date("m/Y",$yesterday)."&limit=\">";

calc(mktime(0,0,0,1,1),$today,true);
$totalca=array_sum($ca);
$obj=array_sum($objectif_facture[date("Y")]);
$pcobj=100*$totalca/$obj-100;
$obj2=floatval(array_sum(array_slice($objectif_facture[date("Y")],0,date("n")-1)));
$obj2+=date("d",$yesterday)*$objectif_facture[date("Y")][date("n")]/date("t");
$pcobj2=100*$totalca/$obj2-100;
$html.="<p><b>Commandes de l'année en cours</b></p>";
$html.="<div>Nombre : ".array_sum($nb)."
<br>CA HT : ".number_format($totalca,2,',','')."&euro;
<br>Objectif en cours : ".number_format($obj2,2,',','')."&euro; <font color=\"".($pcobj2>0?"green":"red")."\">".($pcobj2>0?"+":"").number_format($pcobj2,2,',','')."%</font>
<br>Objectif de l'année : ".number_format($obj,2,',','')."&euro; <font color=\"".($pcobj>0?"green":"red")."\">".($pcobj>0?"+":"").number_format($pcobj,2,',','')."%</font></div>";

$data="";
foreach($ca as $ts=>$value) $data.=",".$ts.":".intval($value);
$data=substr($data,1);
$html.="<br><img src=\"http://ma-cave-a-vin.fr/admin/ccgraph.php?data=$data&width=600height=400&abstype=ts&title=Commandes%20".date("Y")."&limit=\">";

print $html;


$mail=new email();
$mail->expname="Ma Cave à Vin";
$mail->expediteur="contact@ma-cave-a-vin.fr";
$mail->destinataire="commercial@ma-cave-a-vin.fr";
$mail->to=array("webmaster@ma-cave-a-vin.fr","pr@ma-cave-a-vin.fr");
$mail->subject="Info CA";
$mail->body=$html;
$mail->sendmail();

function calc($from,$to,$month=null){
  global $ca,$nb;
  global $DB_site;
  
  $tvafrance=$DB_site->query_first("SELECT 0,TVAtauxnormal,TVAtauxreduit FROM pays WHERE paysid=57");
  $txtva=(1+$tvafrance[1]/100);
  
  $sql="SELECT *,UNIX_TIMESTAMP(datefacture) AS tsdate FROM facture WHERE valide IN (1,5) AND heurefacture BETWEEN $from AND ".($to-1);

  $ca=array();
  $nb=array();
  $rq_facture=$DB_site->query($sql);
  while ($rs_facture=$DB_site->fetch_array($rq_facture)){
            $remboursement = 0 ;
	    if($month!=null) $rs_facture['tsdate']=mktime(0,0,0,date("n",$rs_facture['tsdate']),1,date("Y",$rs_facture['tsdate']));

            $lignefactures=$DB_site->query("SELECT * FROM lignefacture WHERE factureid = '$rs_facture[facturid]'");
            $total_facture = 0 ;
            while ($lignefacture=$DB_site->fetch_array($lignefactures))
                {
                $total_facture += ($lignefacture[prix] * $lignefacture[qte])/(1+$lignefacture[tva]/100) ;
                if ($lignefacture[remboursement])
                    {
                    $remboursement += ($lignefacture[prix] * $lignefacture[qte])/(1+$lignefacture[tva]/100) ;
                    }
                }
        
            // Bon de réduction 
            $prixcadeau = 0;
            $cadeau = $DB_site->query_first("select cadeauid, montantcadeau from facture where facturid = '$rs_facture[facturid]'");
                if ($cadeau[cadeauid] != "") $prixcadeau = $cadeau[montantcadeau];
                 
           

        $float_total_facture=$total_facture + $rs_facture[prixgestion] + $prix_contre_remb + ($rs_facture[prixport]/(1+$rs_facture[tvaport]/100)) - ($prixcadeau/$txtva) - $prixremise;
	$total_factures+=$float_total_facture;

	$ca[$rs_facture['tsdate']]+=$float_total_facture;
	$nb[$rs_facture['tsdate']]+=1;
  }
}


?>