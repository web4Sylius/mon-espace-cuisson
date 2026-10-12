<?php

ini_set("display_errors",true);

require "admin_global.php";
include "./header.php";
include "./menu.php";

include "../class/facture.class.php";

// http://localhost.ma-cave-a-vin.fr/admin/stat_articles.php?order2=&order=qte&prov%5B%5D=&prov%5B%5D=mobile&prov%5B%5D=mp&prov%5B%5D=sav&valide%5B%5D=1%2C7&valide%5B%5D=5%2C8&marque=&from=&to=

$start=microtime(true);

if(!isset($order) || $order=="") $order="qte";
$val=intval($val);

if(isset($from) && $from!=""){
    $sp=explode("/",$from);
    $tsfrom=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
}
if(isset($to) && $to!=""){
    $sp=explode("/",$to);
    $tsto=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
}

$rq=$DB_site2->query("SELECT * FROM marque ORDER BY libelle");
while($m=$DB_site2->fetch_array($rq)){
    $marques[$m['marqueid']]=$m['libelle'];
    $optmarques.="<option value=\"$m[marqueid]\" ".($marque==$m['marqueid']?"selected":"").">$m[libelle]</option>";
}

$visibletxt=array(0=>"<span style=\"color:red;\">Inactif</span>",1=>"Actif",2=>"<span style=\"color:red;\">Supprimé</span>",3=>"<span style=\"color:red;\">Archivé</span>");

if(isset($_GET['prov']) && !is_array($_GET['prov'])) $_GET['prov']=array($_GET['prov']);
if(!isset($_GET['valide'])) $_GET['valide']=array("1,5","7,8");
if(!is_array($_GET['valide'])) $_GET['valide']=array($_GET['valide']);

echo <<<HTML
<style>
.ca { color:black; border-collapse:collapse; border:solid thin black; width:50%; margin-left:5%; margin-right:45%;}
.ca td { border:solid thin black; padding:1px; }
.ca_nb { text-align:right; white-space:nowrap; }
.ca_title { background-color:yellow; text-align:center; }
.ca_head { font-weight:bold; text-align:center; background-color:#dddddd; height:20p; border-bottom:solid thin black; }
.export { display:block; vertical-align:middle; text-decoration:none!important; }
.export img { width:20px; vertical-align:middle; }
</style>
<LINK href="cal.css" type="text/css" rel="stylesheet">

<script language="javascript" src="calendrier.js"></script>
<script language="javascript">
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
HTML;

if($tsfrom>0 && $tsto>0){

    // recuperation des factures
    $sql="SELECT l.artcode,f.facturid,l.qte,l.prix,l.tva,am.marqueid,m.libelle,a.artcode_eurocave,a.active,a.catid,a.artid 
    FROM article a INNER JOIN `lignefacture` l ON (a.artid=l.artid) INNER JOIN facture f ON (f.facturid=l.factureid) INNER JOIN article_marque am ON (l.artid=am.artid) INNER JOIN marque m ON (am.marqueid=m.marqueid)
    WHERE l.prix>=0";
    if(!isset($_GET['diss'])) $sql.=" AND lignefacturedissid=0 AND packid=0";
    if(isset($_GET['valide'])) $sql.=" AND f.valide IN (".implode(",",$_GET['valide']).")";
    $sql.=" AND UNIX_TIMESTAMP(f.datefacture)<=".$tsto." AND UNIX_TIMESTAMP(f.datefacture)>=".$tsfrom;
    if($_GET['temp']==1) 
	     $sql.=" AND ((a.artid IN (SELECT artid FROM temp_price WHERE (du<=".$tsfrom." AND au>=".$tsto.") OR (du<".$tsfrom." AND au>=".$tsto.") OR (du>=".$tsfrom." AND au<".$tsto.")))
	           OR a.artcode IN (SELECT vfa.artcode FROM vente_flash_article vfa INNER JOIN vente_flash vf ON(vf.id=vfa.vfid) WHERE (du<=".$tsfrom." AND au>=".$tsto.") OR (du<".$tsfrom." AND au>=".$tsto.") OR (du>=".$tsfrom." AND au<".$tsto.")))";
    
    if(isset($marque) && $marque!="") $sql.=" AND am.marqueid=".$marque;
    if(isset($_GET['prov'])) $sql.=" AND provenance IN ('".implode("','",$_GET['prov'])."')";
    if($_GET['ref']!="") $sql.=" AND (a.artcode LIKE '%".mysqli_real_escape_string($DB_site2->link_id,$_GET['ref'])."%' OR a.artcode_eurocave LIKE '%".mysqli_real_escape_string($DB_site2->link_id,$_GET['ref'])."%')";

    // $sql.=" GROUP BY f.facturid";

    $plist=$plistoffert=$plistpay=$pca=$pcaht=$pcahtcp=$pmarq=$pfab=$pactive=$cat=$catqte=$marq=$marqqte=$img=array();
    $req=$DB_site2->query($sql);
    
    while($prod=$DB_site2->fetch_array($req)){
	     tableaux($prod);
    }
    
    // lots
    if(!isset($_GET['diss'])){
        $sql="SELECT l.artcode,f.facturid,l.qte,l.prix,l.tva,am.marqueid,m.libelle,a.artcode_eurocave,a.active,a.catid,a.artid 
        FROM lignefacturediss l INNER JOIN article a ON (a.artid=l.artid) INNER JOIN facture f ON (f.facturid=l.factureid) INNER JOIN article_marque am ON (l.artid=am.artid) INNER JOIN marque m ON (am.marqueid=m.marqueid)
        WHERE l.prix>=0";
        if(isset($_GET['valide'])) $sql.=" AND f.valide IN (".implode(",",$_GET['valide']).")";
        $sql.=" AND UNIX_TIMESTAMP(f.datefacture)<=".$tsto." AND UNIX_TIMESTAMP(f.datefacture)>=".$tsfrom;
        if($_GET['temp']==1) 
                    $sql.=" AND ((a.artid IN (SELECT artid FROM temp_price WHERE (du<=".$tsfrom." AND au>=".$tsto.") OR (du<".$tsfrom." AND au>=".$tsto.") OR (du>=".$tsfrom." AND au<".$tsto.")))
                        OR a.artcode IN (SELECT vfa.artcode FROM vente_flash_article vfa INNER JOIN vente_flash vf ON(vf.id=vfa.vfid) WHERE (du<=".$tsfrom." AND au>=".$tsto.") OR (du<".$tsfrom." AND au>=".$tsto.") OR (du>=".$tsfrom." AND au<".$tsto.")))";
        if(isset($marque) && $marque!="") $sql.=" AND am.marqueid=".$marque;
        if(isset($_GET['prov'])) $sql.=" AND provenance IN ('".implode("','",$_GET['prov'])."')";
        if($_GET['ref']!="") $sql.=" AND (a.artcode LIKE '%".mysqli_real_escape_string($DB_site2->link_id,$_GET['ref'])."%' OR a.artcode_eurocave LIKE '%".mysqli_real_escape_string($DB_site2->link_id,$_GET['ref'])."%')";

        $req=$DB_site2->query($sql);
        while($prod=$DB_site2->fetch_array($req)){
                tableaux($prod);
        }


        // recuperation des offres speciales, modification TSI : 1 pack, xtiple article mais 1 seul prix. Pas de calcul du nombre d'articles a faire => erreur qte et prix sinon...  SB#2000
        /*$sql="SELECT ap.artid, concat(ap.artcode,'/',a.artcode) as artcode,f.facturid,SUM(l.qte)/(select SUM(qte) FROM article_pack where packid=ap.artid) AS qte,SUM(l.prix)/qte AS prix,l.tva,ap.active
        FROM lignefacture l INNER JOIN article a ON (a.artid=l.artid) INNER JOIN facture f ON (f.facturid=l.factureid) INNER JOIN article_marque am ON (l.artid=am.artid) INNER JOIN marque m ON (am.marqueid=m.marqueid) INNER JOIN article ap ON(ap.artid=l.packid)
        WHERE l.prix>=0 AND packid>0";*/
        $sql="SELECT ap.artid, concat(ap.artcode,'/',a.artcode) as artcode,f.facturid,1 AS qte,SUM(l.prix)/qte AS prix,l.tva,ap.active
        FROM lignefacture l INNER JOIN article a ON (a.artid=l.artid) INNER JOIN facture f ON (f.facturid=l.factureid) INNER JOIN article_marque am ON (l.artid=am.artid) INNER JOIN marque m ON (am.marqueid=m.marqueid) INNER JOIN article ap ON(ap.artid=l.packid)
        WHERE l.prix>=0 AND packid>0";
        if(isset($_GET['valide'])) $sql.=" AND f.valide IN (".implode(",",$_GET['valide']).")";
        $sql.=" AND UNIX_TIMESTAMP(f.datefacture)<=".$tsto." AND UNIX_TIMESTAMP(f.datefacture)>=".$tsfrom;

        if(isset($marque) && $marque!="") $sql.=" AND am.marqueid=".$marque;
        if(isset($_GET['prov'])) $sql.=" AND provenance IN ('".implode("','",$_GET['prov'])."')";
        if($_GET['ref']!="") $sql.=" AND (a.artcode LIKE '%".mysqli_real_escape_string($DB_site2->link_id,$_GET['ref'])."%' OR a.artcode_eurocave LIKE '%".mysqli_real_escape_string($DB_site2->link_id,$_GET['ref'])."%')";
        $sql.=" GROUP BY facturid,packid";
        
        $req=$DB_site2->query($sql);
        while($prod=$DB_site2->fetch_array($req)){
            tableaux($prod);
        }

    }

    $mainlist=array();
    switch($order){
      	case "qte":
      	$mainlist=$plist;
      	arsort($mainlist);
      	break;
      	
      	case "qtepay":
      	$mainlist=$plistpay;
      	arsort($mainlist);
        break;
      
        case "qteoffert":
      	$mainlist=$plistoffert;
      	arsort($mainlist);
        break;
      
      	case "ca":
      	$mainlist=$pca;
      	arsort($mainlist);
      	break;
      	
      	case "caht":
      	$mainlist=$pcaht;
      	arsort($mainlist);
      	break;
      	
      	case "cahtcp":
      	$mainlist=$pcahtcp;
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
    $oicon[$order]=" ▾";
}


// Criteres de recherche
print "<h1>Stats Articles Vendus</h1>
           
<div class=\"commande_detail\">
  <form name=\"filter\" method=\"get\">
    <input type=\"hidden\" name=\"order2\" value=\"".$_GET['order2']."\">
    <input type=\"hidden\" name=\"order\" value=\"$order\">
     <p class=\"column\">Provenance :<input type=\"checkbox\" name=\"prov[]\" value=\"\"".(!isset($_GET['prov'])||in_array("",$_GET['prov'])?" checked":"")."> Site <input type=\"checkbox\" name=\"prov[]\" value=\"mobile\"".(!isset($_GET['prov'])||in_array("mobile",$_GET['prov'])?" checked":"")."> Mobile <input type=\"checkbox\" name=\"prov[]\" value=\"mp\"".(!isset($_GET['prov'])||in_array("mp",$_GET['prov'])?" checked":"")."> Market place <input type=\"checkbox\" name=\"prov[]\" value=\"sav\"".(!isset($_GET['prov'])||in_array("sav",$_GET['prov'])?" checked":"")."> SAV</p>
     <p class=\"column\">Commandes :<input type=\"checkbox\" name=\"valide[]\" value=\"1,5\"".(in_array("1,5",$_GET['valide'])?" checked":"")."> Validées, expédiée et payée <input type=\"checkbox\" name=\"valide[]\" value=\"7,8\"".(in_array("7,8",$_GET['valide'])?" checked":"")."> Validée, expediée et en attente de paiement</p>
     <p class=\"column\">Marque :&nbsp;&nbsp;&nbsp;<select name=\"marque\" onchange=\"document.forms.filter.submit()\"><option value=\"\">Toutes</option>$optmarques</select></p>
     <p class=\"column\">Référence : <input type=\"text\" name=\"ref\" value=\"".(isset($_GET['ref'])?$_GET['ref']:"")."\"></p>
     <p class=\"column\">A partir du :<input type=\"text\" name=\"from\" id=\"from\" size=\"10\" value=\"".$from."\" onclick=\"calendrier_show('from',this.value);\" readonly> jusqu'au :<input type=\"text\" name=\"to\" id=\"to\" size=\"10\" value=\"".$to."\" onclick=\"calendrier_show('to',this.value);\" readonly> <input type=\"checkbox\" name=\"temp\" value=\"1\"".($_GET['temp']==1?" checked":"")."> Prix temporaires <input type=\"checkbox\" name=\"diss\" value=\"1\"".($_GET['diss']==1?" checked":"")."> Dissocier les lots</p>
     <input type=\"submit\" class=\"skinned\" value=\"Afficher\">
  </form>
</div>

<div style=\"text-align:center; margin:14px; vertical-align:center;\">
  <br><br><br><br><br><br><br><br><br><br><br><br><span>Rechercher un produit :&nbsp;</span><input type=\"text\" id=\"src\"><input type=\"button\" value=\"Rechercher\" onclick=\"srcartcode(document.getElementById('src').value);\"><br>
</div>";

if($tsfrom>0 && $tsto>0){

    // entete du tableau avec tris
    
    print "<div style=\"margin:14px; vertical-align:center;\">(Resultats sans les frais de transports)</div><br>";
    print "<table class=\"ca\">
      <tr class=\"ca_head\">
    	  <td></td>
    	  <td width=\"100\"><a href=\"javascript:order('artcode')\">Code Article</a>".$oicon['artcode']."</td>
    	  <td><a href=\"javascript:order('marque')\">Marque</a>".$oicon['marque']."</td>
    	  <td><a href=\"javascript:order('qteoffert')\">Qte offerte</a>".$oicon['qteoffert']."</td>
    	  <td><a href=\"javascript:order('qtepay')\">Qte vendue</a>".$oicon['qtepay']."</td>
    	  <td><a href=\"javascript:order('qte')\">Qte totale</a>".$oicon['qte']."</td>
    	  <td><a href=\"javascript:order('ca')\">CA</a>".$oicon['ca']."</td>
    	  <td><a href=\"javascript:order('caht')\">CA HT</a>".$oicon['caht']."</td>
    	  <td><a href=\"javascript:order('cahtcp')\">CA HT Réduc</a>".$oicon['cahtcp']."</td>
      </tr>";

    // données du tableau, attention a jouter les PACK, OFR et autres offres speciales si il y en a. Sinon la somme ne sera pas bonne / liste affichée !!
    $i=0;
    $SommeCol1=$SommeCol2=0;
    foreach($mainlist as $code=>$value){
      	if(in_array(substr($code,0,3),array("PAK","ACI","OFR"))){
      	  $i++;
          $SommeCol1 = $SommeCol1 + (float) $pca[$code];
          $SommeCol2 = $SommeCol2 + (float) $pcahtcp[$code];
      	  print "<tr id=\"".$code."\">
      	    <td align=\"right\">".$img[$code]."</td>
      	    <td><a name=\"".$code."\"></a><a href=\"javascript:linksrc('".$code."');\">".$code."</a><br><i>".$pfab[$code]."</i><br>".$visibletxt[$pactive[$code]]."</td>
      	    <td>".$pmarq[$code]."</td>
      	    <td align=\"right\">".$plistoffert[$code]."</td>
      	    <td align=\"right\">".$plistpay[$code]."</td>
      	    <td align=\"right\">".$plist[$code]."</td>
      	    <td align=\"right\">".number_format($pca[$code],2,',','')."</td>
      	    <td align=\"right\">".number_format($pcaht[$code],2,',','')."</td>
      	    <td align=\"right\">".number_format($pcahtcp[$code],2,',','')."</td>
      	  </tr>";
      	}
    }

    // affichage des recapitulatifs
    echo "<tr class=\"ca_head\">
          	<td colspan=\"3\">".count($mainlist)."</td>
          	<td>".array_sum($plistoffert)."</td>
          	<td>".array_sum($plistpay)."</td>
            <td>".array_sum($plist)."</td>
          	<td>".number_format(array_sum($pca),2,',','')."</td>
          	<td>".number_format(array_sum($pcaht),2,',','')."</td>
          	<td>".number_format(array_sum($pcahtcp),2,',','')."</td>
        </tr>";

    echo "</table>";
    
    // Affichage de traces pour debug
    // echo $SommeCol1." : ".$SommeCol2."<br>".array_sum($pca)."  :  ".array_sum($pcaht)."<br>";
    // echo number_format($SommeCol1,2,',','')." : ".number_format($SommeCol2,2,',','')."<br>";
    // print_r ($pcaht);
        
    //export xls
    echo "<a href=\"javascript:export_stats('vendu','art');\" class=\"export\"><img src=\"images/icon/xls.png\"> Exporter</a>";

    // Tableau par categories
    ksort($cat);
    echo "<br><table class=\"ca\">
          <tr class=\"ca_head\">
            	<td>Catégorie</td>
            	<td><a href=\"javascript:order2('qte')\">Qte</a></td>
            	<td><a href=\"javascript:order2('ca')\">CA HT</a></td>
          </tr>";

    // Recup affichage des lignes
    foreach($cat as $catid=>$mca) {
    	$catname=$DB_site2->query_first("SELECT * FROM categorie WHERE catid=".intval($catid));
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
    
    if(count($mainlist)>0){
      	arsort($mainlist);
      	foreach($mainlist as $catid=>$ca){
      	    $catname=$DB_site2->query_first("SELECT * FROM categorie WHERE catid=".intval($catid));
      	    print "<tr><td><img src=\"images/plus.gif\" id=\"opencat$catid\" align=\"absmiddle\" onclick=\"opencat($catid)\">".($catname['libelleFR']==""?"Article sans Catégorie (offre etc.)":$catname['libelleFR'])."</td><td>".$pcatqte[$catid]."</td><td align=\"right\">".number_format($pcat[$catid],2,',','')."</td></tr>";
      	    foreach($parentcat[$catid] as $scatid){ 
      		  $catname=$DB_site2->query_first("SELECT * FROM categorie WHERE catid=".intval($scatid));
      		  print "<tr name=\"sscat$catid\" style=\"display:none;\"><td>&nbsp;".$catname['libelleFR']."</td><td>".$catqte[$scatid]."</td><td align=\"right\">".number_format($cat[$scatid],2,',','')."</td></tr>";
      	    }
      	}
    }

    echo "</table>";
    // export
    echo "<a href=\"javascript:export_stats('vendu','categ');\" class=\"export\"><img src=\"images/icon/xls.png\"> Exporter</a>";


    // liste par marque
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
    // export
    echo "<a href=\"javascript:export_stats('vendu','marq');\" class=\"export\"><img src=\"images/icon/xls.png\"> Exporter</a>";
}

// print microtime(true)-$start;

include "./footer.php";

function tableaux($prod){
    global $DB_site2,$host;
    global $plist,$plistoffert,$plistpay,$pca,$pcaht,$pcahtcp,$pmarq,$pfab,$pactive,$cat,$catqte,$marq,$marqqte,$img;
    
    $facture=new facture($DB_site2,$prod['facturid']);
    $plist[$prod['artcode']]+=$prod['qte'];
    if(!isset($plistoffert[$prod['artcode']])) $plistoffert[$prod['artcode']]=0;
    if(!isset($plistpay[$prod['artcode']])) $plistpay[$prod['artcode']]=0;
    if($prod['prix']==0){
        $plistoffert[$prod['artcode']]+=$prod['qte'];
    }else{
        $plistpay[$prod['artcode']]+=$prod['qte'];
    }
        
    $pca[$prod['artcode']]+=$prod['qte']*$prod['prix'];
    $prixht=$prod['qte']*$prod['prix']/(1+$prod['tva']/100);
    $pcaht[$prod['artcode']]+=$prixht;
    if($facture->totallignesht>0)
        $pcahtcp[$prod['artcode']]+=$prixht-$facture->cadeauht*($prixht/$facture->totallignesht);
    $pmarq[$prod['artcode']]=$prod['libelle'];
    $pfab[$prod['artcode']]=$prod['artcode_eurocave'];
    $pactive[$prod['artcode']]=$prod['active'];
    $cat[$prod['catid']]+=$prod['qte']*$prod['prix']/(1+$prod['tva']/100);
    $catqte[$prod['catid']]+=$prod['qte'];
    $marq[$prod['marqueid']]+=$prod['qte']*$prod['prix']/(1+$prod['tva']/100);
    $marqqte[$prod['marqueid']]+=$prod['qte'];
    if(file_exists("../configurations/".$host."/images/produits/br/".$prod['artid'].".jpg"))
	     $img[$prod['artcode']]="<img src=\"../configurations/".$host."/images/produits/br/".$prod['artid'].".jpg\" width=\"30\">";
}
