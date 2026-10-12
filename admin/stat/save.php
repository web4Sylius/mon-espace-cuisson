<?php

include("../../config.php");
include("../../class/pagecontrol.php");

/*
for($i=mktime(0,0,0,4,1,2010);$i<=mktime(0,0,0,4,30,2010);$i+=86400){
$stat=new ccstat();
$stat->sst=$i;
$stat->est=$stat->sst+86400;
$stat->calcstat(true);
$stat->savexml();
}*/

$stat=new ccstat();

$now=getdate();
$now0=mktime(0,0,0,$now['mon'],$now['mday'],$now['year']);
$yest=$now0-86400;
print date("d/m/Y",$yest);
$stat->start=date("d/m/Y",$yest);

$stat->init();

$stat->calcstat(true);
$stat->calcpages();
$stat->calcorders();

$stat->savexml();

?>