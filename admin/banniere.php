<?php

require "./admin_global.php";
include "./fonctions.php";	

// Vérification module espace partenaires
if( in_array( "7", $modules ) )
	{
	if ($emp != "")
		{
		$emplacement=$DB_site2->query_first("select * from partenaire where emplacementid = $emp") ;
		echo "<img src=\"http://$host/configurations/$host/images/bannieres/$emp.$emplacement[image]\" border=\"1\">" ;
		}
	}
else
	{
	echo "NOK" ;
	}
?>

