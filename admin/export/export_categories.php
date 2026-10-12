<?php

$rootpath="../../";
$host="www.ma-cave-a-vin.fr" ;
ini_set("memory_limit","1024M");
ini_set("max_execution_time",60);

ini_set("display_errors",1);

include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."class/mysql.php");
include($rootpath."configurations/$host/config.php");
include_once($rootpath."class/fonctions.php");
include($rootpath."admin/export/ooo.php");
include($rootpath."class/categorie.class.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

$doc=new ooo();
$doc->name="Export Categories ".date("Y-m-d");

$bold=new ooo_style("bold");
$bold->font_weight="bold";
$bold->family="table-cell";
$bold->setborders("0.5pt solid #000000");
$doc->addstyle($bold);
$sscat=new ooo_style("sscat");
$sscat->family="table-cell";
$sscat->setborders("0.5pt solid #000000");
$doc->addstyle($sscat);

$main=new categorie($DB_site);
foreach($main->getMenus() as $idmenu=>$menu){
    $table=new ooo_table($menu['libelle']);
    for($c=2;$c<=2*count($langues)+1;$c++)
		$table->set_col($c,"5cm");
    
    $row=array(new ooo_cell("catid"));
    foreach($langues as $langue){
		$row[]=new ooo_cell("Libelle ".$langue);
		$row[]=new ooo_cell("Description ".$langue);
    }
    $table->addrow(1, $row);
    
    $i=2;
    $categories=$main->getMenu($idmenu);

    foreach($categories as $tbl){
		$rows=categorie_row($tbl['catid'],$table,$i,"bold");
    }

    $doc->addtable($table);
}

$doc->generate();


function categorie_row($catid,$table,&$i,$style="",$level=0){
    global $DB_site;
    global $doc;
    global $langues;

    $row=array(new ooo_cell($catid,$style));
	
    foreach($langues as $langue){
		$categorie=new categorie($DB_site,$catid,$langue);
		$row[]=new ooo_cell(str_repeat("--",$level).html_entity_decode($categorie->libelle),$style);
		$row[]=new ooo_cell(html_entity_decode(strip_tags($categorie->description)),$style);
    }
    $link="https://www.ma-cave-a-vin.fr/admin/modifier_categorie.php?catid=".$catid;
    $cell=new ooo_cell($link,$style);
    $cell->content=$doc->link("Modifier",$link);
    $row[]=$cell;
    
    $table->addrow($i,$row);
    $i++;
    
    $level++;
    foreach($categorie->subcategories() as $p=>$subcategorie){
		categorie_row($subcategorie[0]->catid,$table,$i,"sscat",$level);
    }
    if($style=="bold") $i++;
}

?>