<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

include "../class/DAOarticle.class.php";
include "../class/garantie.class.php";
include "../class/DAOgarantie.class.php";
include "../class/stock.class.php";

$visibletxt=array(1=>"<img src=\"images/icon/green.png\">",0=>"<img src=\"images/icon/orange.png\">");

$spytime=time()-4*86400;
if(isset($_POST['from']))
    $dfrom=datetime::createfromformat("d/m/Y",$_POST['from']);
else
    $dfrom=date_create("1 month ago");
    
if(isset($_POST['to']))
    $dto=datetime::createfromformat("d/m/Y",$_POST['to']);
else
    $dto=date_create();

$order="count";
if(isset($_POST['order'])) $order=$_POST['order'];

$pmarq=array();
$req=$DB_site2->query("SELECT * FROM marque ORDER BY libelle");
while($m=$DB_site2->fetch_array($req)){
    $optmarque.="<option value=\"".$m['marqueid']."\"".($_POST['marque']==$m['marqueid']?" selected":"").">".$m['libelle']."</option>";
    $pmarq[$m['marqueid']]=$m['libelle'];
}

$req=$DB_site2->query("SELECT site FROM history WHERE timestamp>".$spytime." GROUP BY site");
while($spy=$DB_site2->fetch_array($req)){
    $spysitelist[]=$spy['site'];
}
natcasesort($spysitelist);

foreach($spysitelist as $spysite)
    $trsites.="<td>".ucwords($spysite)."</td>";
    

$from=$dfrom->format("d/m/Y");
$to=$dto->format("d/m/Y");


$exclu=array("googlebot","as29017","msn","voilabot","ahref","bingbot","acoon","google","yandex","exabot","torservers","as13285");
$exclip=array("80.12.82.212","88.184.216.175");

/*
$sql="SELECT page,COUNT(page) 
FROM stat s INNER JOIN stat_page sp ON (s.id=sp.statid) 
WHERE s.timestamp BETWEEN ".$dfrom->gettimestamp()." AND ".$dto->gettimestamp()." AND page LIKE '/art,%' AND ip<>host AND NOT (host LIKE '%".implode("%' OR host LIKE '%",$exclu)."%') AND NOT ip IN ('".implode("','",$exclip)."') GROUP BY substr(page,1,10) ORDER BY COUNT(*) DESC LIMIT 500";
*/
$sql="SELECT DISTINCT(a.artid) as artid , SUM(av.nb) as somme FROM article AS a INNER JOIN article_vu AS av USING (artid) WHERE av.date BETWEEN ".$dfrom->gettimestamp()." AND ".$dto->gettimestamp()." GROUP BY artid ORDER BY somme DESC LIMIT 500";

$stock=new stock($DB_site2);

$spylist=$spymini=$spyminisite=array();
$req=$DB_site2->query("SELECT artcode,price,site FROM history WHERE timestamp>".$spytime." ORDER BY timestamp");
while($spy=$DB_site2->fetch_array($req)){
    if($spy['price']!=""){
	$artcode=$spy['artcode'];
	if($spy['price']<$spymini[$artcode] || !isset($spymini[$artcode])){
	    $spymini[$artcode]=$spy['price'];
	    $spyminisite[$artcode]=$spy['site'];
	}
	$spylist[$spy['site']]=$spy['price'];
	$spylist[$artcode][$spy['site']]=$spy['price'];
    }
}


$tr=array();
$req=$DB_site2->query($sql);
while($page=$DB_site2->fetch_array($req)){
    $art=article($page[artid],$page[somme],$dfrom->gettimestamp(),$dto->gettimestamp());
    if($_POST['marque']=="" || $_POST['marque']==$art['marqueid'] && !is_null($art)){
	$code=$art['artcode'];
    
	$instock=$stock->in_stock(intval($art['artid']));
	$art['stock']=intval($instock->qte);
	
	$reqlot=$DB_site2->query("SELECT a.artcode,al.qte FROM article_lot al INNER JOIN article a USING(artid) WHERE al.lotid=".intval($art['artid']));
	$lotartnb=$DB_site2->num_rows($reqlot);
	if($lotartnb==0)
	    $spycodes=array($art['artcode']=>1);
	else{
	    $spycodes=array();
	    while($al=$DB_site2->fetch_array($reqlot)) $spycodes[$al['artcode']]=$al['qte'];
	}
	
	$trspylist="";
	foreach($spysitelist as $site){
	    $content=$red="";
	    $totalspy=0;
	    $qtespy=0;
	    foreach($spycodes as $spycode=>$qte){
		if(isset($spylist[$spycode][$site])){
		    $qtespy++;
		    $pc="";
		    if($art['prix']>0 && $lotartnb==0) $pc=round(-100*($art['prix']-$spylist[$spycode][$site])/$art['prix']);
		    $red=$pc<0?" red":"";
		    if($pc!=="") $pc="<br>".($pc>0?"+":"").$pc."%";
		    $totalspy+=$spylist[$spycode][$site]*$qte;
		    $content.="<div class=\"nb$red\">".number_format($spylist[$spycode][$site],2,',','')."&euro;".$pc."</div>";
		}else
		    $content.="<div></div>";
	    }
	    if($qtespy==$lotartnb && $lotartnb>0){
		$pc="<br>".round(-100*($art['prix']-$totalspy)/$art['prix'],1)."%";
		$red=$pc<0?" red":"";
		$content.="<div class=\"total nb$red\">".number_format($totalspy,2,',','')."&euro;".$pc."</div>";
		
		if($totalspy>0 && (!isset($spymini[$code]) || $totalspy<$spymini[$code])){
		    $spymini[$code]=$totalspy;
		    $spyminisite[$code]=$site;
		}
	    }
	    $trspylist.="<td".($lotartnb>0?" class=\"div\"":"").">".$content."</td>";
	    
	}
	
	if(isset($spymini[$code])){
	    $spyht=$spymini[$code]/(1+$tva/100);
	    $margespy=$spyht+$art['port']-$art['pahttotal'];
	    $pcmargespy=100*$margespy/$spyht;
	}
	
	if($art['prix']>0)
	    $pcspymini=round(-100*($art['prix']-$spymini[$code])/$art['prix']);
	else
	    $pcspymini="";
	
	$tr[$art[$order]].="<tr>
	  <td><img src=\"../configurations/".$host."/images/produits/br/".$art['artid'].".jpg\" width=\"30\"></td>
	  <td>".$art['artcode']."<br><i>".$art['artcode_eurocave']."</i><br>".$visibletxt[$art['active']]."</td>
	  <td>".$pmarq[$art['marqueid']]."</td>
	  <td class=\"nb\">".number_format($art['prixconseille'],2,',','')."&euro;</td>
	  <td class=\"nb\">".number_format($art['prix'],2,',','')."&euro;</td>
	  <td class=\"nb\">".round($art['pcmarge'])."%<br>".number_format($art['marge'],2,',','')."&euro;</td>
	  <td class=\"center\">".$art['count']."</td>
	  <td class=\"center\">".$art['ventes']."</td>
	  <td class=\"center\">".$art['devis']."</td>
	  <td class=\"center\">".$art['stock']."</td>
	  <td class=\"sep\"></td>
	  <td class=\"nb".($pcspymini<0?" red":"")."\">".($spymini[$code]!=""?number_format($spymini[$code],2,',','')."&euro;<br>".$pcspymini."%":"")."</td>
	  <td class=\"center\">".count($spylist[$code])."</td>
	  <td>".$spyminisite[$code]."</td>
	  <td class=\"nb\">".(isset($spymini[$code])?round($pcmargespy)."%<br>".number_format($margespy,2,',','')."&euro;":"")."</td>
	  <td class=\"sep\"></td>
	  $trspylist
	</tr>";

    }
}

krsort($tr);
$tbody=implode("",$tr);
$sort=array($order=>"&nbsp;&#9660;");

echo <<<HTML
  <h1>SPY par articles vus</h1>

  <div class="commande_detail">
      <form name="filter" method="POST" style="margin:10px;">
      <input type="hidden" id="order" name="order" value="$order">
      <p><b>Marque :</b> <select name="marque"><option value="">Toutes</option>$optmarque</select></p>
      <p>Du <input type="text" id="from" name="from" value="$from" onclick="calendrier_show(this.id,this.value)" size="8" readonly> au <input type="text" id="to" name="to" value="$to" onclick="calendrier_show(this.id,this.value)" size="8" readonly> &nbsp; <input type="submit" class="loupe" value=""></p>
      </form>
  </div>

  <table class="ca">
  <thead>
  <tr>
      <td></td>
      <td width="100">Code</td>
      <td>Marque</td>
      <td>PPI</td>
      <td>PV MCAV</td>
      <td>Marge MCAV</td>
      <td><a href="javascript:order('count')">Nb vues</a>$sort[count]</td>
      <td><a href="javascript:order('ventes')">Qté vendue</a>$sort[ventes]</td>
      <td><a href="javascript:order('devis')">Qté devis</a>$sort[devis]</td>
      <td>Stock</td>
      <td class="sep"></td>
      <td>Prix le plus bas</td>
      <td>Nb concur.</td>
      <td>Site mini</td>
      <td>Marge si prix + bas</td>
      <td class="sep"></td>
      $trsites
  </tr>
  </thead>
  
  $tbody

  </table>
HTML;

echo <<<JS
<LINK href="cal.css" type="text/css" rel="stylesheet">
<script language="javascript" src="calendrier.js"></script>
<script language="javascript">
function order(field){
    document.getElementsByName('order')[0].value=field;
    document.forms.filter.submit();
}
</script>
JS;

echo <<<CSS
<style>
.ca { border-collapse:collapse; }
.ca tbody tr:nth-child(2n+1) { background:#DDD; }
.ca td { border:solid 1px black; color:black; }
.ca td.div { vertical-align:top; }
.ca td.div div { min-height:20px; border-bottom:solid 1px #666; color:#666; }
.ca td.div div.total { border:none; color:black; }
.ca thead { text-align:center; font-weight:bold; background-color:#ccc; }
.ca .nb { text-align:right; }
.ca .nb.red { color:red; }
.ca .center { text-align:center; }
.ca .sep { width:4px; background:yellow; }
</style>
CSS;

include "./footer.php";


function article($artid,$count,$from,$to){
    global $DB_site2;
    global $template;

    $spytime=time()-7*86400;

	$sql="SELECT a.artid,a.artcode,a.artcode_eurocave,a.libelleFR,marqueid,a_s.prix,a_s.prixconseilleht,af.prix AS paht,af.ecopartht,a.emballage_poids,a_s.tauxchoisi,a_s.active 
	FROM article a 
	INNER JOIN article_marque USING(artid) 
	INNER JOIN article_site a_s USING(artid) 
	INNER JOIN article_fournisseur af USING(artid) 
	WHERE site_code='base' AND af.principal LIKE '%base%' AND artid=".intval($artid);
	$article=$DB_site2->query_first($sql);
	if($article['artid']!=""){
	    $txtva=$template->tva_ref("taux".$article['tauxchoisi']);
	    
	    $fraisport=$DB_site2->query_first("SELECT * FROM fraisport WHERE paysid=57 AND ".(1000*$article['emballage_poids'])." BETWEEN poiddebut AND poidfin");
	    if($article['prix']>$fraisport['gratuit']) $fraisport['prix']=0;
	    $fraisport['prix']=$fraisport['prix']/$txtva;
	    
	    $pahtgarantie=0;
	    $garanties=DAOgarantie::loadMaxGarantieArtByPays($DB_site2,"base","FR",57,$article['artid']);
	    foreach($garanties as $g) $pahtgarantie+=$g->paht;

	    $article['prixconseille']=$article['prixconseilleht']*(1+$txtva/100);
	    $article['pahttotal']=$article['paht']+$article['ecopartht']+$article['trspt']+$fraisport['achat']+$pahtgarantie;
	    $article['port']=$fraisport['prix'];
	    $totalvente=$article['prix']/(1+$txtva/100)+$fraisport['prix'];
	    $article['marge']=$totalvente-$article['pahttotal'];
	    $article['pcmarge']=100*$article['marge']/$totalvente;
	    
	    $article['count']=$count;

	    $ventes=$DB_site2->query_first("SELECT SUM(qte) FROM lignefacture lf INNER JOIN facture f ON(f.facturid=lf.factureid) WHERE artid=".$article['artid']." AND valide>0 AND provenance='' AND heurefacture BETWEEN $from AND $to");
	    $venteslot=$DB_site2->query_first("SELECT SUM(qte) FROM lignefacturediss lf INNER JOIN facture f ON(f.facturid=lf.factureid) WHERE artid=".$article['artid']." AND valide>0 AND provenance='' AND heurefacture BETWEEN $from AND $to");
	    $devis=$DB_site2->query_first("SELECT SUM(qte) FROM lignefacture lf INNER JOIN facture f ON(f.facturid=lf.factureid) WHERE artid=".$article['artid']." AND valide=0 AND provenance='' AND heurefacture BETWEEN $from AND $to");
	    $article['ventes']=intval($ventes[0])+intval($venteslot[0]);
	    $article['devis']=intval($devis[0]);
	    $article['ventesvu']=100*$ventes[0]/$count;
	    $article['devisvu']=100*$devis[0]/$count;
	    if($article['artcode']!="") return $article;
	}
    
}

?>