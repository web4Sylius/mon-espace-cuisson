<?php

$rootpath="/homez.368/macaveav/www/";
// $rootpath="/var/www/macave/site/";

$host="www.ma-cave-a-vin.fr" ;
$nostat=1;
include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."mysql.php");
include($rootpath."configurations/$host/config.php");

include("xlexport.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

$xl=new xlexport("csv");
$xl->name="Macaveavin2010.csv";

$requete=$DB_site->query("SELECT * FROM facture WHERE valide=5 AND dateexpedition BETWEEN '2010-01-01' AND '2010-12-31'");
while($facture=$DB_site->fetch_array($requete)){
    $lignes=$DB_site->query("SELECT qte,tva,prix FROM lignefacture WHERE factureid=".$facture['facturid']);
    $totalht=$totaltva=0;
    while($ligne=$DB_site->fetch_array($lignes)){
	$ht=$ligne['qte']*($ligne['prix']/(1+$ligne['tva']/100));
	$totalht+=$ht;
	$totaltva+=$ligne['qte']*($ligne['prix']-$ht);
    }
    $portht=($facture['prixport']/(1+$facture['tvaport']/100));
    $totalht+=$portht;
    $totaltva+=($facture['prixport']-$portht);
    $cadeauht=$facture['montantcadeau']/1.196;
    $totalht-=$cadeauht;
    $totaltva-=($facture['montantcadeau']-$cadeauht);

    $xl->addrow(array($facture['dateexpedition'],$facture['numerofacture'],number_format($totalht,2,',',''),number_format($totaltva,2,',',''),number_format($totalht+$totaltva,2,',','')));
}

$xl->export();

?>