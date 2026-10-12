<?php

error_reporting(E_ALL);
ini_set("display_errors","on");

$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";

require "admin_global.php";
echo "<script language=\"javascript\"> 
<!-- 
function fermer()
	{
	window.opener.document.formu.del.value='2';
	window.opener.document.formu.action2.value='img';
	window.opener.document.formu.old.value='img';
	window.opener.document.formu.submit () ;
	window.close() ;
	} 
//-->
</script>" ;

if ($dh = opendir($rootpath."configurations/$host/images/produits/")) 
	{
	$imagebase=$DB_site2->query_first("select image from article where artid = '$art'");
	$imagesupl=$DB_site2->query_first("select image from articlephoto where artid = '$art' and articlephotoid = '$sup'");
	if (file_exists($rootpath."configurations/$host/images/produits/$art.$imagebase[0]")) {
		// racine
		@rename ($rootpath."configurations/$host/images/produits/".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/old".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/$art"."_".$sup.".".$imagesupl[0], $rootpath."configurations/$host/images/produits/".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/old".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/$art"."_".$sup.".".$imagesupl[0]) ;
		// article
		@rename ($rootpath."configurations/$host/images/produits/article/".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/article/old".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/article/$art"."_".$sup.".".$imagesupl[0], $rootpath."configurations/$host/images/produits/article/".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/article/old".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/article/$art"."_".$sup.".".$imagesupl[0]) ;
		// br
		@rename ($rootpath."configurations/$host/images/produits/br/".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/br/old".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/br/$art"."_".$sup.".".$imagesupl[0], $rootpath."configurations/$host/images/produits/br/".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/br/old".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/br/$art"."_".$sup.".".$imagesupl[0]) ;
		// categorie
		@rename ($rootpath."configurations/$host/images/produits/categorie/".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/categorie/old".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/categorie/$art"."_".$sup.".".$imagesupl[0], $rootpath."configurations/$host/images/produits/categorie/".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/categorie/old".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/categorie/$art"."_".$sup.".".$imagesupl[0]) ;
		// cdc_categ
		@rename ($rootpath."configurations/$host/images/produits/cdc_categ/".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/cdc_categ/old".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/cdc_categ/$art"."_".$sup.".".$imagesupl[0], $rootpath."configurations/$host/images/produits/cdc_categ/".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/cdc_categ/old".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/cdc_categ/$art"."_".$sup.".".$imagesupl[0]) ;
		// cdc_index
		@rename ($rootpath."configurations/$host/images/produits/cdc_index/".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/cdc_index/old".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/cdc_index/$art"."_".$sup.".".$imagesupl[0], $rootpath."configurations/$host/images/produits/cdc_index/".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/cdc_index/old".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/cdc_index/$art"."_".$sup.".".$imagesupl[0]) ;
		// nouveautes_categ
		@rename ($rootpath."configurations/$host/images/produits/nouveautes_categ/".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/nouveautes_categ/old".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/nouveautes_categ/$art"."_".$sup.".".$imagesupl[0], $rootpath."configurations/$host/images/produits/nouveautes_categ/".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/nouveautes_categ/old".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/nouveautes_categ/$art"."_".$sup.".".$imagesupl[0]) ;
		// nouveautes_index
		@rename ($rootpath."configurations/$host/images/produits/nouveautes_index/".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/nouveautes_index/old".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/nouveautes_index/$art"."_".$sup.".".$imagesupl[0], $rootpath."configurations/$host/images/produits/nouveautes_index/".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/nouveautes_index/old".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/nouveautes_index/$art"."_".$sup.".".$imagesupl[0]) ;
		// promos_categ
		@rename ($rootpath."configurations/$host/images/produits/promos_categ/".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/promos_categ/old".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/promos_categ/$art"."_".$sup.".".$imagesupl[0], $rootpath."configurations/$host/images/produits/promos_categ/".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/promos_categ/old".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/promos_categ/$art"."_".$sup.".".$imagesupl[0]) ;
		// promos_index
		@rename ($rootpath."configurations/$host/images/produits/promos_index/".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/promos_index/old".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/promos_index/$art"."_".$sup.".".$imagesupl[0], $rootpath."configurations/$host/images/produits/promos_index/".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/promos_index/old".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/promos_index/$art"."_".$sup.".".$imagesupl[0]) ;
		// topventes_categ
		@rename ($rootpath."configurations/$host/images/produits/topventes_categ/".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/topventes_categ/old".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/topventes_categ/$art"."_".$sup.".".$imagesupl[0], $rootpath."configurations/$host/images/produits/topventes_categ/".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/topventes_categ/old".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/topventes_categ/$art"."_".$sup.".".$imagesupl[0]) ;
		// topventes_index
		@rename ($rootpath."configurations/$host/images/produits/topventes_index/".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/topventes_index/old".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/topventes_index/$art"."_".$sup.".".$imagesupl[0], $rootpath."configurations/$host/images/produits/topventes_index/".$art.".".$imagebase[0]) ;
		@rename ($rootpath."configurations/$host/images/produits/topventes_index/old".$art.".".$imagebase[0], $rootpath."configurations/$host/images/produits/topventes_index/$art"."_".$sup.".".$imagesupl[0]) ;
		}
	closedir($dh);
	
	clear_img_cache($art);
	}


echo "<p>La modification a été effectuée<br><a href=\"javascript:fermer();\">Fermer</a></p>" ;
	

?>
