<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "../class/facture.class.php";

ini_set("max_execution_time",180);
ini_set("memory_limit","256M");

echo "<h1>Stat market place</h1>";

print "<div style=\"margin-left:30px;\"><table><tr>";

$now=time();
for($y=2016;$y<=date("Y");$y++){
    $ts=mktime(0,0,0,1,1,$y);
    print "</td>
    <td width=\"100\" valign=\"top\">
    <b><a href=\"export/export_ventes_mp.php?ts=".$ts."&mode=year\">".$y."</a></b>
    <ul style=\"padding:0;\">";
    for($m=1;$m<=12;$m++){
        $ts=mktime(0,0,0,$m,1,$y);
        if($ts>$now) break 2;
        print "<li><a href=\"export/export_ventes_mp.php?ts=".$ts."\">".str_pad($m,2,"0",STR_PAD_LEFT)."-".$y."</a></li>";
    }
    print "</ul></td>";
}

print "</tr></table></div>";





$marketplaces=array("01"=>"Amazon",
					"04"=>"Price Minister",
					"05"=>"Pixmania",
					"06"=>"Windill",
					"07"=>"Rue du Commerce",
					"08"=>"Edengo",
					"10"=>"Ebay",
					"11"=>"2x moins cher",
					"12"=>"Fnac",
					"13"=>"CDiscount",
					"14"=>"LaRedoute",
					"15"=>"Galeries Lafayette",
					"16"=>"Mister Good Deal",
					"18"=>"Delamaison",
					"19"=>"Conforama",
					"21"=>"Showroomprive",
					"22"=>"Retif",
					"23"=>"Boulanger");
echo "
<style>
.ca { color:black; border-collapse:collapse; border:solid thin black; }
.ca td { border:solid thin black; }
.ca_nb { text-align:right; white-space:nowrap; }
.ca_title { background-color:yellow; text-align:center; }
.ca_head { font-weight:bold; text-align:center; }
.ca_total { background-color:#FFFFDA; text-align:right; }
</style>
<h1>Chiffre d'affaires sur market places</h1>";

for($year=2016;$year<=date("Y");$year++){
	$head="<tr><td>&nbsp;</td>";
	for($i=1;$i<=12;$i++)
		$head.="<td class=\"ca_head\">".$i."/".$year."</td>";
	$head.="<td class=\"ca_total ca_head\">Total</td></tr>";
	print "<br><br><table class=\"ca\">";
	print "<tr><td colspan=\"14\" class=\"ca_title\">Objectif ensemble des market places ".$year."</td></tr>".$head;
	print "<tr><td>Objectif market places</td>";
	for($i=1;$i<=12;$i++)
		print "<td class=\"ca_nb\">".number_format($objectif_mp[$year][$i],0,',',' ')." &euro;</td>";
	print "<td class=\"ca_total\">".number_format(array_sum($objectif_mp[$year]),0,',',' ')." &euro;</td>
	</tr>";
	$provmp=array();
	$f=new facture($DB_site2);
	$factures=$f->search("valide IN(1,5,7,8) AND datefacture LIKE '".$year."-%' AND provenance='mp' ORDER BY provenanceid ");
	foreach($factures as $facture){
		$mois=date("n",$facture->datecommande);
		$provmp[$facture->provenanceid][$mois]+=$facture->totalht;
	}
	$totallistmp=array();
	print "<tr><td colspan=\"14\" class=\"ca_title\">CA par market place ".$year."</td></tr>".$head;
	foreach($provmp as $mpid=>$totalmpmois){
		print "<tr><td>".$marketplaces[$mpid]."</td>";
		for($i=1;$i<=12;$i++){
		print "<td class=\"ca_nb\">".number_format($totalmpmois[$i],0,',',' ')." &euro;</td>";
		$totallistmp[$i]+=$totalmpmois[$i];
		}
		print "<td class=\"ca_total\">".number_format(array_sum($totalmpmois),0,',',' ')." &euro;</td>
		</tr>";
	}
	print "<tr><td align=\"right\"><b>Total :</b></td>";
	for($i=1;$i<=12;$i++){
		$pc=round(100*$totallistmp[$i]/$objectif_mp[$year][$i],1);
		$color="";
		if($pc!=0) $color=($pc<100?"color:red;":"color:green;");
		print "<td class=\"ca_nb\" style=\"".$color."\">".number_format($totallistmp[$i],0,',',' ')." &euro;<br>".$pc."%</td>";
	}
	$pc=round(100*array_sum($totallistmp)/array_sum($objectif_mp[$year]));
	$color=($pc<100?"color:red;":"color:green;");
	print "<td class=\"ca_total\" style=\"".$color."\">".number_format(array_sum($totallistmp),0,',',' ')." &euro;<br>".$pc."%</td>
	</tr>";
	print "</table>";
}


include "./footer.php";

?>
