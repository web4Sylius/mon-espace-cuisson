<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

if(!isset($order) || $order=="") $order="qte";
$val=intval($val);
$tsfrom=$tsto=0;
if(isset($from) && $from!=""){
    $sp=split("/",$from);
    $tsfrom=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
}
if(isset($to) && $to!=""){
    $sp=split("/",$to);
    $tsto=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
}

$rq=$DB_site2->query("SELECT * FROM marque ORDER BY libelle");
while($m=$DB_site2->fetch_array($rq)){
    $marques[$m['marqueid']]=$m['libelle'];
    $optmarques.="<option value=\"$m[marqueid]\" ".($marque==$m['marqueid']?"selected":"").">$m[libelle]</option>";
	$all.= $m['marqueid'].",";
}

$visibletxt=array(0=>"<span style=\"color:red;\">Inactif</span>",1=>"Actif",2=>"<span style=\"color:red;\">Supprimé</span>");

if(isset($_GET['prov']) && !is_array($_GET['prov'])) $_GET['prov']=array($_GET['prov']);
if(isset($_GET['marque']) && !is_array($_GET['marque'])) $_GET['marque']=array($_GET['marque']);
if(!isset($_GET['valide'])) $_GET['valide']=array("1,7","5,8");
if(!is_array($_GET['valide'])) $_GET['valide']=array($_GET['valide']);

echo "
<style>
.ca { color:black; border-collapse:collapse; border:solid thin black; }
.ca td { border:solid thin black; padding:1px; }
.ca_nb { text-align:right; white-space:nowrap; }
.ca_title { background-color:yellow; text-align:center; }
.ca_head { font-weight:bold; text-align:center; background-color:#dddddd; height:20p; border-bottom:solid thin black; }
</style>
<LINK href=\"cal.css\" type=\"text/css\" rel=\"stylesheet\">

<script language=\"javascript\" src=\"calendrier.js\"></script>
<script language=\"javascript\">
initcalendrier();

function srcartcode(code){
    code=code.toUpperCase();
    if(code.substr(0,3)!='ACI') code='ACI-'+code;
    
    if(document.getElementById(code)){
      document.location='#'+code;
      document.getElementById(code).style.backgroundColor='#ffaaaa';
    }else{
      alert('Produit non trouvé');
    }
}

function opencat(catid){
    if(document.getElementsByName('sscat'+catid)[0].style.display=='none'){
	var display='table-row';
	document.getElementById('opencat'+catid).src='images/moins.gif';
    }else{
	var display='none';
	document.getElementById('opencat'+catid).src='images/plus.gif';
    }
    for(i=0;i<document.getElementsByName('sscat'+catid).length;i++)
	document.getElementsByName('sscat'+catid)[i].style.display=display;
}

function order(key){
    document.getElementsByName('order')[0].value=key;
    document.forms.filter.submit();
}

function order2(key){
    document.getElementsByName('order2')[0].value=key;
    document.forms.filter.submit();
}
</script>

<h1>Chiffre d'affaire</h1>
           
<div class=\"commande_detail\">
  <form name=\"filter\" method=\"get\">
    <input type=\"hidden\" name=\"order2\" value=\"".$_GET['order2']."\">
    <input type=\"hidden\" name=\"order\" value=\"$order\">
     <p class=\"column\">Provenance : <input type=\"checkbox\" name=\"prov[]\" value=\"\"".(!isset($_GET['prov'])||in_array("",$_GET['prov'])?" checked":"")."> Site <input type=\"checkbox\" name=\"prov[]\" value=\"mp\"".(!isset($_GET['prov'])||in_array("mp",$_GET['prov'])?" checked":"")."> Market place <input type=\"checkbox\" name=\"prov[]\" value=\"sav\"".(!isset($_GET['prov'])||in_array("sav",$_GET['prov'])?" checked":"")."> SAV</p>
     <p class=\"column\">Commandes : <input type=\"checkbox\" name=\"valide[]\" value=\"1,7\"".(in_array("1,7",$_GET['valide'])?" checked":"")."> validées <input type=\"checkbox\" name=\"valide[]\" value=\"5,8\"".(in_array("5,8",$_GET['valide'])?" checked":"")."> facturées</p>
	 <p class=\"column\">Marque : <select name=\"marque[]\" multiple=\"\" style=\"width: 150px; height: 200px;\">$optmarques</select><em> (Multisélection disponible avec la touche \"CTRL\")</em></p>
     <p class=\"column\">A partir de <input type=\"text\" name=\"from\" id=\"from\" size=\"10\" value=\"$from\" onclick=\"calendrier_show('from',this.value);\" readonly> <input type=\"button\" value=\"X\" onclick=\"document.getElementById('from').value='';\"> jusqu'au  <input type=\"text\" name=\"to\" id=\"to\" size=\"10\" value=\"$to\" onclick=\"calendrier_show('to',this.value);\" readonly> <input type=\"button\" value=\"X\" onclick=\"document.getElementById('to').value='';\"> <input type=\"checkbox\" name=\"temp\" value=\"1\"".($_GET['temp']==1?" checked":"")."> Prix temporaires</p>
     <input type=\"submit\" class=\"skinned\" value=\"Afficher\">
  </form>
</div>
<div style=\"text-align:center; margin:14px;\">
<input type=\"text\" id=\"src\"> <input type=\"button\" value=\"Rechercher\" onclick=\"srcartcode(document.getElementById('src').value);\">
</div>
<table class=\"ca\">
  <tr class=\"ca_head\"><td></td><td width=\"100\"><a href=\"javascript:order('artcode')\">Code</a><td><a href=\"javascript:order('marque')\">Marque</a></td><td><a href=\"javascript:order('qte')\">Qte</a></td><td><a href=\"javascript:order('ca')\">CA</a></td><td>CA HT</td></tr>";

$sql="SELECT l.artcode,f.facturid,l.qte,l.prix,l.tva,am.marqueid,m.libelle,a.artcode_eurocave,a.active,a.catid,a.artid FROM article a inner join `lignefacture` l on (a.artid=l.artid) inner join facture f on (f.facturid=l.factureid) inner join article_marque am on (l.artid=am.artid) inner join marque m on (am.marqueid=m.marqueid) where l.prix>=0";
if(isset($_GET['valide'])) $sql.=" AND f.valide IN (".implode(",",$_GET['valide']).")";
if($tsfrom>0 && $tsto>0){
    $sql.=" AND UNIX_TIMESTAMP(f.datefacture)<=".$tsto." AND UNIX_TIMESTAMP(f.datefacture)>=".$tsfrom;
    if($_GET['temp']==1) $sql.=" AND a.artid IN (SELECT artid FROM temp_price WHERE (du<=".$tsfrom." AND au>=".$tsto.") OR (du<".$tsfrom." AND au>=".$tsto.") OR (du>=".$tsfrom." AND au<".$tsto."))";
 }
if(isset($_GET['marque'])) $sql.=" AND am.marqueid IN ('".implode("','",$_GET['marque'])."')";
if(isset($_GET['prov'])) $sql.=" AND provenance IN ('".implode("','",$_GET['prov'])."')";
// $sql.=" GROUP BY f.facturid";

$cat=array();
$plist=$pca=$pcaht=$pmarq=$pfab=$pactive=$pcat=$pcatqte=$marq=$marqqte=array();
$req=$DB_site2->query($sql);
while($prod=$DB_site2->fetch_array($req)){
    $plist[$prod['artcode']]+=$prod['qte'];
    $pca[$prod['artcode']]+=$prod['qte']*$prod['prix'];
    $pcaht[$prod['artcode']]+=$prod['qte']*$prod['prix']/(1+$prod['tva']/100);
    $pmarq[$prod['artcode']]=$prod['libelle'];
    $pfab[$prod['artcode']]=$prod['artcode_eurocave'];
    $pactive[$prod['artcode']]=$prod['active'];
    $cat[$prod['catid']]+=$prod['qte']*$prod['prix']/(1+$prod['tva']/100);
    $catqte[$prod['catid']]+=$prod['qte'];
    $marq[$prod['marqueid']]+=$prod['qte']*$prod['prix']/(1+$prod['tva']/100);
    $marqqte[$prod['marqueid']]+=$prod['qte'];
    $img[$prod['artcode']]="<img src=\"../configurations/".$host."/images/produits/br/".$prod['artid'].".jpg\" width=\"30\">";
}

$mainlist=array();
switch($order){
    case "qte":
    $mainlist=$plist;
    arsort($mainlist);
    break;

    case "ca":
    $mainlist=$pca;
    arsort($mainlist);
    break;
    
    case "marque":
    $mainlist=$pmarq;
    asort($mainlist);
    break;

    case "artcode":
    $mainlist=$plist;
    ksort($mainlist);
    break;

    case "ref":
    $mainlist=$pfab;
    asort($mainlist);
    break;

    default:
    $mainlist=$plist;
    arsort($mainlist);
    break;
}



$i=0;

foreach($mainlist as $code=>$value){
    if(substr($code,0,3)=="ACI"){
      $i++;
      print "<tr id=\"".$code."\">
	<td align=\"right\">".$img[$code]."</td>
	<td><a name=\"".$code."\"></a><a href=\"javascript:linksrc('".$code."');\">".$code."</a><br><i>".$pfab[$code]."</i><br>".$visibletxt[$pactive[$code]]."</td>
	<td>".$pmarq[$code]."</td>
	<td align=\"right\">".$plist[$code]."</td>
	<td align=\"right\">".number_format($pca[$code],2,',','')."</td>
	<td align=\"right\">".number_format($pcaht[$code],2,',','')."</td>
      </tr>";
    }
}

echo "<tr class=\"ca_head\"><td colspan=\"3\">".count($mainlist)."</td><td>".array_sum($plist)."</td><td>".number_format(array_sum($pca),2,',','')."</td><td>".number_format(array_sum($pcaht),2,',','')."</td></tr>";

echo "</table>";

ksort($cat);
echo "<br><table class=\"ca\">
<tr class=\"ca_head\"><td>Catégorie</td><td><a href=\"javascript:order2('qte')\">Qte</a></td><td><a href=\"javascript:order2('ca')\">CA HT</a></td></tr>";
foreach($cat as $catid=>$mca){
    $catname=$DB_site2->query_first("SELECT * FROM categorie WHERE catid=$catid");
    if($catname['parentid']==0) $catname['parentid']=$catid;
    $pcat[$catname['parentid']]+=$mca;
    $pcatqte[$catname['parentid']]+=$catqte[$catid];
    $parentcat[$catname['parentid']][]=$catid;
//     print "<tr><td>".$catname['libelleFR']."</td><td>".number_format($mca,2,',','')."</td></tr>";
}

$mainlist=array();
switch($_GET['order2']){
    case "qte":
    $mainlist=$pcatqte;
    $marqlist=$marqqte;
    break;

    case "ca":
    $mainlist=$pcat;
    $marqlist=$marq;
    break;

    default:
    $mainlist=$pcat;
    $marqlist=$marq;
}
arsort($mainlist);
foreach($mainlist as $catid=>$ca){
    $catname=$DB_site2->query_first("SELECT * FROM categorie WHERE catid=$catid");
    print "<tr><td><img src=\"images/plus.gif\" id=\"opencat$catid\" align=\"absmiddle\" onclick=\"opencat($catid)\">".$catname['libelleFR']."</td><td>".$pcatqte[$catid]."</td><td align=\"right\">".number_format($pcat[$catid],2,',','')."</td></tr>";
    foreach($parentcat[$catid] as $scatid){ 
	$catname=$DB_site2->query_first("SELECT * FROM categorie WHERE catid=$scatid");
	print "<tr name=\"sscat$catid\" style=\"display:none;\"><td>&nbsp;".$catname['libelleFR']."</td><td>".$catqte[$scatid]."</td><td align=\"right\">".number_format($cat[$scatid],2,',','')."</td></tr>";
    }
}

echo "</table>";

echo "<br><table class=\"ca\">
<tr class=\"ca_head\">
    <td>Marque</td>
    <td><a href=\"javascript:order2('qte')\">Qte</a></td>
    <td><a href=\"javascript:order2('ca')\">CA HT</a></td>
</tr>";

arsort($marqlist);
foreach($marqlist as $marqueid=>$ca){
    print "<tr>
      <td>".$marques[$marqueid]."</td>
      <td>".$marqqte[$marqueid]."</td>
      <td align=\"right\">".number_format($marq[$marqueid],2,',','')."</td>
   </tr>";
}
echo "</table>";

include "./footer.php";