<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

?>
<br>
<center>
<h1>Remises sur catégories</h1>
</center>
<br>
<br>
<?
$is_remise=$DB_site2->query_first("SELECT count(catid) as nb
								  from remisecategorie") ;
if ($is_remise[nb] != 0)
	{
	echo "<table class=\"table_modules_boutiques\"><tr><td align=\"center\">Catégorie</td><td align=\"center\">Type de remise</td><td align=\"center\">Valeur</td><td align=\"center\">Pourcentage</td></tr>" ;
	$remises=$DB_site2->query("SELECT * 
							  from categorie c, remisecategorie rc, remise r 
							  where c.catid = rc.catid and rc.remiseid = r.remiseid") ;
	while ($remise=$DB_site2->fetch_array($remises)) 
		{
		$catid = $remise[catid] ;
		$remiseid = $remise[remiseid] ;
		$libelleFR = $remise[libelleFR] ;
		$typeremise = $remise[typeremise] ;  	 
		$valeur = $remise[valeur] ;  	 
		$pctremise = $remise[pctremise] ;
		echo "<tr><td align=\"center\">$libelleFR</td><td align=\"center\">typeremise</td><td align=\"center\">$valeur</td><td align=\"center\">$pctremise</td></tr>" ;
		}
	echo "</table>" ;
	}
else
	{
	echo "Aucune remise n'a encore été créée pour une catégorie.<br>Pour en créer une, cliquez sur le lien ci-dessous." ;
	}

?>
<br>
<br>
<br>
<?
echo "<a href=\"ajouter_remise_cat.php\">Ajouter une remise pour une catégorie</a>";


include "./footer.php";

?>