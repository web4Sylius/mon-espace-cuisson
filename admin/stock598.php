<?php

ini_set("display_errors","on");
include("admin_global.php");
include("../class/stock.class.php");

$from=mktime(0,0,0,1,1,2012);
$to=mktime(0,0,0,1,1,2013);
$listcat=array(1,45,13,47,65,46,212);

$stock=new stock($DB_site2);

$r=$DB_site2->query("SELECT * FROM stock_item INNER JOIN article USING(artid) WHERE catid IN(".implode(",",$listcat).") AND state>0 AND lieu=598 AND date BETWEEN ".$from." AND ".$to);
while($data=$DB_site2->fetch_array($r)){
  $d=date("W",$data['date']);
  $in[$d]++;
}

$r=$DB_site2->query("SELECT * FROM stock_item INNER JOIN article USING(artid) WHERE catid IN(".implode(",",$listcat).") AND dest LIKE 'facture%'".(!is_null($artid)?" AND stock_item.artid=".$artid:"").(!is_null($lieu)?" AND stock_item.lieu=".intval($lieu):"")." AND state=3 GROUP BY stock_item.id");
while($data=$DB_site2->fetch_array($r)){
  $item=new stock_item($DB_site2,$data);
  $item->destination();
  
  if($item->date_expedition>=$from && $item->date_expedition<$to){
     $d=date("W",$item->date_expedition);
     $out[$d]++;
  }

}
/*
foreach($suivi['in'] as $d=>$item){
  if($d>=$from && $d<$to) $in[date("W",$d)]+=$item->qte;
}

foreach($suivi['facture'] as $d=>$item){
  if($d>=$from && $d<$to) $out[date("W",$d)]+=$item->qte;
}
*/

$nb=0;
$total=0;
print "<table>
<tr><td>Semaine</td><td>Entrées</td><td>Commandes</td><td>nb articles</td></tr>";
for($i=1;$i<=52;$i++){
  $nb+=intval($in[$i]);
  $nb-=intval($out[$i]);
  $ineur=($in[$i]*5);
  $outeur=($out[$i]*4);
  $nbeur=($nb*1.7);
  print "<tr><td>$i</td><td>".intval($in[$i])." <b>".$ineur."&euro;</b></td><td>".intval($out[$i])." <b>".$outeur."&euro;</b></td><td>".intval($nb)." <b>".$nbeur."&euro;</b></td><td>".($ineur+$outeur+$nbeur)."&euro;</td><td>".($ineur+$outeur+$nbeur)*0.75."&euro;</td></tr>";
  $total+=($ineur+$outeur+$nbeur)*0.75;
}
print "<tr><td></td><td></td><td></td><td></td><td></td><td><b>".$total." =&gt; ".($total/9)."&euro;/mois</b></td></tr>
</table>";

?>