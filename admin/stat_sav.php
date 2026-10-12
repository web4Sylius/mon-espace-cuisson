<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

include("sav.class.php");

if(isset($_GET['filter'])) print "<a href=\"stat_sav.php\">Tout afficher</a>";

print "<table border=\"1\">
<tr>
  <td>Commande</td>
  <td>Type</td>
  <td>Date SAV</td>
  <td>Nb jours après exp.</td>
  <td>Produits</td>
  <td>Infos</td>
</tr>";

$qtepanne=$infopanne=array();

$listsav=$DB_site2->query("SELECT * FROM sav WHERE date>1356991200 ORDER BY date");
while($savdata=$DB_site2->fetch_array($listsav)){
    $sav=new sav($DB_site2,$savdata['id']);
    switch($sav->typelitige){
	case 0:
	case 2:
	case 3:
	case 4:
	$infos=nl2br($sav->fields['sav_reserves_verif']);
	break;
	
	case 1:
	$infos=nl2br($sav->fields['sav_detail1-verif']);
	break;
	
	case 5:
	$infos="Fraude";
	break;
    }
    $facture=$DB_site2->query_first("SELECT UNIX_TIMESTAMP(dateexpedition) FROM facture WHERE facturid=".$sav->facturid);
    $jours=round(($sav->date_creation-$facture[0])/86400);
    
    $print=!isset($_GET['filter']);
    $produits=array();
    foreach($sav->lignes as $lignefactureid=>$qte){
	if($qte>0){
	    $lf=$DB_site2->query_first("SELECT a.artcode,artcode_eurocave FROM article a INNER JOIN lignefacture USING(artid) WHERE lignefactureid=".intval($lignefactureid));
	    $produits[]=$qte." x ".$lf['artcode']." (".$lf['artcode_eurocave'].")";
	    if($sav->typelitige==1){
		$qtepanne[$lf['artcode']]+=$qte;
		$infopanne[$lf['artcode']][]=$jours;
		if($lf['artcode']==$_GET['filter']) $print=true;
	    }
	}
    }
    
    
    if($print)
      print "<tr>
	<td>".$sav->facturid."</td>
	<td>".$sav->types[$sav->typelitige]."</td>
	<td>".date("d/m/Y",$sav->date_creation)."</td>
	<td>".$jours."</td>
	<td>".implode("<br>",$produits)."</td>
	<td>".$infos."</td>
      </tr>";
}

print "</table>";

print "<h2>Pannes</h2>
<table border=\"1\">
<tr>
  <td>Produit</td>
  <td>Nb pannes</td>
  <td>Nb jours moy</td>
</tr>";

arsort($qtepanne);
foreach($qtepanne as $artcode=>$qte){
    $jours=round(array_sum($infopanne[$artcode])/count($infopanne[$artcode]));
    print "<tr>
	<td><a href=\"stat_sav.php?filter=".$artcode."\">".$artcode."</a></td>
	<td>".$qte."</td>
	<td>".$jours."</td>
    </tr>";
}

print "</table>";

include "./footer.php";

?>