<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

print "<h1>Recherches effectuées</h1>";

$to=date('Y-m-d');
$to2=time();
$from=date('Y-m-d', (time()-6*30*86400));
$from2= $to2-6*30*86400;

if(isset($_POST['du'])){
    $dt=datetime::createfromformat("d/m/Y",$_POST['du']);
    $from=$dt->format('Y-m-d');
    $du=$dt->format('d/m/Y');
    $dt=datetime::createfromformat("d/m/Y",$_POST['au']);
    $to=$dt->format('Y-m-d');
    $au=$dt->format('d/m/Y');
}
else {
    $du=date("d/m/Y",$from2);
    $au=date("d/m/Y",$to2);
}


print <<<HTML
<style>
#tbl { border-collapse:collapse; }
#tbl td { border:solid 1px black; padding:4px; }
#tbl thead { text-align:center; font-weight:bold; background-color:#ccc; }
#tbl .nb { text-align:right; }
</style>

<div class="commande_detail">
    <form method="post" style="margin:10px;">
		Du <input type="text" name="du" id="du" value="$du" onclick="calendrier_show(this.id,this.value)" size="10" readonly>(inclus)  au <input type="text" name="au" id="au" value="$au" onclick="calendrier_show(this.id,this.value)" size="10" readonly>(exclus)
		<input type="submit" class="skinned" value="OK">
		<br>Les recherches des administrateurs ne sont pas prises en compte.
    </form>
</div>
<div class="clear"></div>
HTML;

$srclist=array();

//$sql="SELECT sp.timestamp,page FROM stat_page sp INNER JOIN stat ON(sp.statid=stat.id) WHERE ip NOT IN('".implode("','",$blockip)."') AND sp.timestamp BETWEEN ".$from." AND ".$to." AND detail IN ('".implode("','",$langlist)."') AND (page LIKE '/recherche-rapide.php%' OR page LIKE '/rr.php%')";
$sql = "SELECT mot_cle, SUM(nb_recherches) as qte FROM stat_recherches WHERE date_recherche BETWEEN '".$from."' AND '".$to."' GROUP BY mot_cle ORDER BY qte DESC";
$req=$DB_site2->query($sql);
if($req > 0){
    print "<table id=\"tbl\">
    <thead>
    <tr>
      <td>Mot clé recherché</td>
      <td>Nombre de recherche effectuées</td>
    </tr>
    </thead>";

    while($stat=$DB_site2->fetch_array($req)){
        print "<tr>
                <td>".$stat['mot_cle']." <a href=\"//".$host_conf->http_host."/recherche-rapide.php?action=".urlencode($stat['mot_cle'])."\" target=\"_blank\">voir</a></td>
                <td class=\"nb\">".$stat['qte']."
            </tr>";
    }
    print "</table>";
}else
    print "<p>Aucun résultat</p>";

include "./footer.php";

?>