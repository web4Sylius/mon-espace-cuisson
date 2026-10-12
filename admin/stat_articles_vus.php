<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "../class/stock.class.php";

echo "cette page a été fermée, car les données qu'elle affiche sont fausses.";
exit;

$exclu=array("googlebot","as29017","msn","voilabot","ahref","bingbot","acoon","google","yandex","exabot","torservers","as13285");
$exclip=array("80.12.82.212","88.184.216.175");

$from=mktime(0,0,0,date("n"),1,date("Y"));
$to=mktime(23,59,59,date("n"),date("t"),date("Y"));
if(isset($_GET['from']) && isset($_GET['to'])){
    $sp=explode("/",$_GET['from']);
    $from=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
    $sp=explode("/",$_GET['to']);
    $to=mktime(23,59,59,$sp[1],$sp[0],$sp[2]);
}
$order="count";
if(isset($_GET['order'])) $order=$_GET['order'];

$txfrance=$DB_site2->query_first("SELECT 0,TVAtauxnormal,TVAtauxreduit FROM pays WHERE paysid=57");
$visibletxt=array(0=>"<span style=\"color:red;\">Inactif</span>",1=>"Actif",2=>"<span style=\"color:red;\">Supprimé</span>",3=>"<span style=\"color:red;\">Archivé</span>");

$req=$DB_site2->query("SELECT * FROM marque");
while($m=$DB_site2->fetch_array($req)) $marques[$m['marqueid']]=$m['libelle'];
asort($marques);

$optmarque="<option value=\"0\">Tout</option>";
foreach($marques as $mid=>$libelle) $optmarque.="<option value=\"$mid\"".($_GET['marqueid']==$mid?" selected":"").">$libelle</option>";

print "<h1>Articles vus</h1>";
echo "Ces statistiques ne prennent en compte que les vues des 6 derniers mois.<br>";
print "<style>
#tbl { border-collapse:collapse; }
#tbl td { border:solid 1px black; }
#tbl thead { text-align:center; font-weight:bold; background-color:#ccc; }
#tbl .nb { text-align:right; }
.export { display:block; float:right; vertical-align:middle; text-decoration:none!important; }
.export img { width:20px; vertical-align:middle; }
</style>
<script language=\"javascript\">
function order(key){
    if(document.getElementById('order').value==key)
	document.getElementById('sens').value=(document.getElementById('sens').value=='ASC'?'DESC':'ASC');
    else
	document.getElementById('sens').value='ASC';
    document.getElementById('order').value=key;
    document.forms.form.submit();
}
</script>
<LINK href=\"cal.css\" type=\"text/css\" rel=\"stylesheet\">
<script language=\"javascript\" src=\"calendrier.js\"></script>
<a href=\"javascript:export_stats('vu');\" class=\"export\"><img src=\"images/icon/xls.png\"> Exporter</a>
<form name=\"form\" method=\"GET\">
<input type=\"hidden\" id=\"order\" name=\"order\" value=\"".$order."\">
<input type=\"hidden\" id=\"sens\" name=\"sens\" value=\"".$_GET['sens']."\">
Du <input type=\"text\" id=\"from\" name=\"from\" value=\"".date("d/m/Y",$from)."\" onclick=\"calendrier_show(this.id,this.value)\" size=\"8\" readonly> au <input type=\"text\" id=\"to\" name=\"to\" value=\"".date("d/m/Y",$to)."\" onclick=\"calendrier_show(this.id,this.value)\" size=\"8\" readonly> Marque : <select name=\"marqueid\">$optmarque</select> <input type=\"submit\" value=\"OK\">
</form>";

$stock=new stock($DB_site2);

$tr=array();
/*
$sql="SELECT page,COUNT(page) 
FROM stat s INNER JOIN stat_page sp ON (s.id=sp.statid) 
WHERE s.timestamp BETWEEN ".$from." AND ".$to." AND sp.page LIKE '/art,%' AND ip<>host AND NOT (host LIKE '%".implode("%' OR host LIKE '%",$exclu)."%') AND NOT ip IN ('".implode("','",$exclip)."') 
GROUP BY substr(page,1,10) 
ORDER BY COUNT(*) DESC LIMIT 500";
*/

$sql="SELECT DISTINCT(a.artid) as artid , SUM(av.nb) as somme
FROM article AS a INNER JOIN article_vu AS av USING (artid) 
WHERE av.date BETWEEN ".$from." AND ".$to."
GROUP BY artid
ORDER BY somme DESC LIMIT 500";

$req=$DB_site2->query($sql);
while($page=$DB_site2->fetch_array($req)){
    $art=article($page[artid],$page[somme],$from,$to);
    if($_GET['marqueid']==0 || $_GET['marqueid']==$art['marqueid']){

      $instock=$stock->in_stock(intval($art['artid']));
      $art['stock']=intval($instock->qte);
      $tr[$art[$order]].="<tr>
      <td><img src=\"../configurations/".$host."/images/produits/br/".$art['artid'].".jpg\" width=\"30\"></td>
      <td>".$art['artcode']."<br><i>".$art['artcode_eurocave']."</i><br>".$visibletxt[$art['active']]."</td>
      <td>".$marques[$art['marqueid']]."</td>
      <td>".$art['libelleFR']."</td>
      <td class=\"nb\">".$page[1]."</td>
      <td class=\"nb\">".$art['prix']."&euro;</td>
      <td class=\"nb\">".round($art['pcmarge'],2)."%</td>
      <td class=\"nb\">".$art['spynb']."</td>
      <td class=\"nb\">".$art['spymini'].($art['spymini']!=""?"&euro;":"")."</td>
      <td>".$art['spysite']."</td>
      <td class=\"nb\">".$art['spymarge'].($art['spymarge']!=""?"%":"")."</td>
      <td class=\"nb\">".$art['ventes']."</td>
      <td class=\"nb\">".round($art['ventesvu'],2)."%</td>
      <td class=\"nb\">".$art['devis']."</td>
      <td class=\"nb\">".round($art['devisvu'],2)."%</td>
      <td class=\"nb\">".$art['stock']."</td>
    </tr>";
    }
}
if($_GET['sens']=="ASC"){
  ksort($tr);
  $sens=" &#9650;";
}else{
  krsort($tr);
  $sens=" &#9660;";
}

print "<table id=\"tbl\">
<thead>
<tr>
  <td colspan=\"2\" width=\"200\"><a href=\"javascript:order('artcode');\">Article</a>".($order=="artcode"?$sens:"")."</td>
  <td><a href=\"javascript:order('marqueid');\">Marque</a>".($order=="marqueid"?$sens:"")."</td>
  <td width=\"200\"><a href=\"javascript:order('libelleFR');\">Libellé</a>".($order=="libelleFR"?$sens:"")."</td>
  <td><a href=\"javascript:order('count');\">Nb vues</a>".($order=="count"?$sens:"")."</td>
  <td><a href=\"javascript:order('prix');\">Prix vente</a>".($order=="prix"?$sens:"")."</td>
  <td><a href=\"javascript:order('pcmarge');\">% marge</a>".($order=="pcmarge"?$sens:"")."</td>
  <td><a href=\"javascript:order('spynb');\">Nb spy</a>".($order=="spynb"?$sens:"")."</td>
  <td><a href=\"javascript:order('spymini');\">Prix mini spy</a>".($order=="spymini"?$sens:"")."</td>
  <td><a href=\"javascript:order('spysite');\">Site</a>".($order=="spysite"?$sens:"")."</td>
  <td><a href=\"javascript:order('spymarge');\">% marge spy</a>".($order=="spymarge"?$sens:"")."</td>
  <td><a href=\"javascript:order('ventes');\">Ventes</a>".($order=="ventes"?$sens:"")."</td>
  <td><a href=\"javascript:order('ventesvu');\">Ventes/vus</a>".($order=="ventesvu"?$sens:"")."</td>
  <td><a href=\"javascript:order('devis');\">Devis</a>".($order=="devis"?$sens:"")."</td>
  <td><a href=\"javascript:order('devisvu');\">Devis/vus</a>".($order=="devisvu"?$sens:"")."</td>
  <td><a href=\"javascript:order('stock');\">Qté stock</a>".($order=="stock"?$sens:"")."</td>
</tr>
</thead>";
print implode("",$tr);
print "</table>";

include "./footer.php";

function article($artid,$count,$from,$to){
    global $DB_site2;
    global $txfrance;
    $spytime=time()-7*86400;
	$article=$DB_site2->query_first("SELECT artid,artcode,artcode_eurocave,libelleFR,marqueid,prix,prixht,paht,ecopartht,emballage_poids,tauxchoisi,active FROM article INNER JOIN article_marque USING(artid) WHERE artid=".$artid);
	$txtva=(1+$txfrance[$article['tauxchoisi']]/100);
	$fraisport=$DB_site2->query_first("SELECT * FROM fraisport WHERE paysid=57 AND ".(1000*$article['emballage_poids'])." BETWEEN poiddebut AND poidfin");
	if($article['prix']>$fraisport['gratuit']) $fraisport['prix']=0;
	$fraisport['prix']=$fraisport['prix']/$txtva;
	$garantie=0;
	$garanties=$DB_site2->query("SELECT (select achat from article_caractval inner join caracteristiquevaleur using(caractvalid) where artid=$article[artid] and caractid=cv.caractid order by position limit 1) FROM article_caractval ac INNER JOIN caracteristiquevaleur cv USING (caractvalid) WHERE artid=$article[artid] group by caractid");
	while($g=$DB_site2->fetch_array($garanties)) $garantie+=$g[0];
	$article['pcmarge']=100*($article['prixht']+$fraisport['prix']-$article['paht']-$article['ecopartht']-$fraisport['achat']-$garantie)/$article['prixht'];
	$article['count']=$count;
	$req=$DB_site2->query("SELECT price,site FROM history WHERE artcode='".$article['artcode']."' AND timestamp>".$spytime." GROUP BY site ORDER BY price");
	$spy=$DB_site2->fetch_array($req);
	if($spy['price']!=""){
		$article['spynb']=$DB_site2->num_rows($req);
		$article['spymini']=$spy['price'];
		$article['spysite']=$spy['site'];
		$article['spymarge']=round(100*(($spy['price']/$txtva)+$fraisport['prix']-$article['paht']-$article['ecopartht']-$fraisport['achat']-$garantie)/$article['prixht'],2);
	}
	$ventes=$DB_site2->query_first("SELECT SUM(qte) FROM lignefacture lf INNER JOIN facture f ON(f.facturid=lf.factureid) WHERE artid=".$article['artid']." AND valide>0 AND provenance='' AND heurefacture BETWEEN $from AND $to");
	$venteslot=$DB_site2->query_first("SELECT SUM(qte) FROM lignefacturediss lf INNER JOIN facture f ON(f.facturid=lf.factureid) WHERE artid=".$article['artid']." AND valide>0 AND provenance='' AND heurefacture BETWEEN $from AND $to");
	$devis=$DB_site2->query_first("SELECT SUM(qte) FROM lignefacture lf INNER JOIN facture f ON(f.facturid=lf.factureid) WHERE artid=".$article['artid']." AND valide=0 AND provenance='' AND heurefacture BETWEEN $from AND $to");
	$article['ventes']=intval($ventes[0])+intval($venteslot[0]);
	$article['devis']=intval($devis[0]);
	$article['ventesvu']=100*$ventes[0]/$count;
	$article['devisvu']=100*$devis[0]/$count;
	if($article['artcode']!="")
		return $article;
}

?>