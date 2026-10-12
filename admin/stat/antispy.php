<?php

include("../../global.php");
include_once("../../fonctions.php");

// $ts=$_GET['ts'];
if(!isset($_GET['ts'])) $ts=mktime(0,0,0);
$groupby="sp.statid";
$groupby="s.ip";

$sql="SELECT *,count(sp.statid) AS nbp FROM stat_page sp inner join stat s on (s.id=sp.statid) WHERE sp.timestamp > $ts GROUP BY $groupby ORDER BY count(sp.statid) DESC LIMIT 50";

print "<head>
<style>
body { font-size:11px; }
td { border:solid thin #808080; font-size:12px; }
div { font-size:10px; display:none;}
</style>
</head>";

print "<body>";
print "<table>";

if(!isset($_GET['ip'])){
    $i=0;
    $requete=$DB_site->query($sql);
    while($stat=$DB_site->fetch_object($requete)){
	$i++;
	print "<tr onclick=\"document.location='antispy.php?ip=".$stat->ip."';\"><td>".$stat->host."</td><td>".$stat->agent."</td><td>".$stat->nbp."</td></tr>";
	
    }
}else{
    $lastagent="";
    $lastts=0;

    $req2=$DB_site->query("SELECT sp.timestamp,sp.page,s.host,s.agent,sp.status,s.ip FROM stat_page sp INNER JOIN stat s ON (s.id=sp.statid) WHERE s.ip='".$_GET['ip']."' ORDER BY sp.timestamp");
    $nbp=$DB_site->num_rows($req2);
    while($p=$DB_site->fetch_object($req2)){
	  if($lastagent!=$p->agent){
	      
	      print "<tr><td>".$p->host."<br>".$p->ip."</td><td>".$p->agent."</td><td>$nbp</td></tr>";
	      $lastagent=$p->agent;
	  }
	  $diffts=$p->timestamp-$lastts;
	  print "<tr><td style=\"color:".($diffts<=1?"red":"black")."\">".date("d/m/Y H:i:s",$p->timestamp)."</td><td>".$p->page."</td><td>".$p->status."</td></tr>";
	  $lastts=$p->timestamp;
    }


}
print "</table>";
print "</body>";

/*

*/

?>