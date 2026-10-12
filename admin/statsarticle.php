<?php


require "admin_global.php";
include "./header.php";
include "./menu.php";

$notin_array = array(0);

setlocale(LC_TIME, "fr_FR");
?>

<style type="text/css">
.stats {
    border-collapse: collapse;
    border: 1px solid black;
    text-align: left;
    font-weight: bold;
    width: 90%;
}

.stats td {
    background-color: #EEEEFF;
    padding: 3px 2px 3px 2px;
    font-size: 10px;
}

.stats td a {
    text-decoration: none;
    color: #000000;
}

.stats td a:hover {
    text-decoration: underline;
}

.stats th {
    text-align: center;
    background-color: #9999FF;
    font-size: 10px;
    color: #FFFFFF;
    padding: 3px 2px 3px 2px;
}

.stats th a {
    text-decoration: underline;
    color: #FFFFFF;
}

.stats th a:hover {
    text-decoration: none;
}

.stats .bar {
	border-left: 2px solid black;
}

.stats .art {
	background-color: white;
}
</style>

<?php

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
       <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
        <tr>
            <td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Statistiques</font>
            </td>
        </tr>
        <tr>
            <td background=\"images2/cadre_r3_c2.gif\" width=\"100%\" height=\"2\">
            </td>
        </tr>
    </table>
   </td>
   <td><img name=\"cadre_r2_c5\" src=\"images2/cadre_r2_c5.gif\" width=\"11\" height=\"27\" border=\"0\" alt=\"\"></td>
   <td background=\"images2/cadre_r2_c6.gif\" width=\"100%\" height=\"27\" align=\"center\" valign=\"middle\">
   </td>
   <td><img name=\"cadre_r2_c8\" src=\"images2/cadre_r2_c8.gif\" width=\"13\" height=\"27\" border=\"0\" alt=\"\"></td>
  </tr>
</table>

</tr></td>
<tr><td>


<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td background=\"images2/cadre_r4_c2.gif\" width=\"3\" height=\"100%\"></td>
   <td align=\"center\" valign=\"middle\">";

?>

<?php

//il faut definir la periode

if(!isset($_GET['date0']) || !$_GET['date0']) {
    $_GET['date0'] = mktime(0,0,0);
}
if(!isset($_GET['date1']) || !$_GET['date1']) {
    $_GET['date1'] = mktime(0,0,0);
}

if($_GET['date0'] > $_GET['date1']) {
    $_GET['date1'] = $_GET['date0'];
}


//analyse de la periode

$periode_n = 'Aujourd\'hui';
$date_n = mktime(0,0,0);

if($_GET['date0'] != $_GET['date1']) { //on a une periode
    //verifier si c'est une periode mensuelle 1er jour au dernier jour du mois
    if(date('j', $_GET['date0']) == 1 && date('t', $_GET['date1']) == date('j', $_GET['date1']) && date('n', $_GET['date0']) == date('n', $_GET['date1']) && date('Y', $_GET['date0']) == date('Y', $_GET['date1'])) {
        $date_0i = mktime(0,0,0,date('n', $_GET['date0'])-2,1,date('Y',$_GET['date0']));
        $date_1i = mktime(0,0,0,date('n', $_GET['date0'])-2, date('j', $_GET['date1']), date('Y',$_GET['date0']));
    	
    	$date_01 = $_GET['date0'];
        $date_07 = mktime(0,0,0,date('n', $_GET['date0'])-1,1,date('Y',$_GET['date0']));
        $date_11 = $_GET['date1'];
        $date_17 = mktime(0,0,0,date('n', $_GET['date0'])-1, date('j', $_GET['date1']), date('Y',$_GET['date0']));
        
        $periode_i = strftime('%B %Y', $date_0i);
        $periode_it = strftime('%B %Y', $date_0i);
        $periode_1 = strftime('%B %Y', $date_01);
        $periode_7 = strftime('%B %Y', $date_07);
        $periode_7t = strftime('%B %Y', $date_07);
    }
    //verifier si c'est une periode annuelle 1er janvier au 31 dec
    elseif(date('j', $_GET['date0']) == 1 && date('n', $_GET['date0']) == 1 && date('j', $_GET['date1']) == 31 && date('n', $_GET['date1']) == 12 && date('Y', $_GET['date0']) == date('Y', $_GET['date1'])) {
        $date_0i = mktime(0,0,0,1,1,date('Y', $_GET['date0'])-2);
    	$date_1i = mktime(0,0,0,12,31,date('Y', $_GET['date0'])-2);
        
    	$date_01 = $_GET['date0'];
        $date_07 = mktime(0,0,0,1,1,date('Y', $_GET['date0'])-1);
        $date_11 = $_GET['date1'];
        $date_17 = mktime(0,0,0,12,31,date('Y', $_GET['date0'])-1);
        
        
        $periode_i = date('Y', $_GET['date0'])-2;
        $periode_it = date('Y', $_GET['date0'])-2;
        $periode_7 = date('Y', $_GET['date0'])-1;
        $periode_7t = date('Y', $_GET['date0'])-1;
        $periode_1 = date('Y', $_GET['date0']);
    }
    //sinon c une periode quelquonque
    else {
        $diff_date = round($_GET['date1'] - $_GET['date0'])/(3600*24);
        
        $date_0i = mktime(0,0,0,date('n',$_GET['date0']),date('j',$_GET['date0'])-2-$diff_date-$diff_date,date('Y',$_GET['date0']));
        $date_1i = mktime(0,0,0,date('n',$_GET['date0']),date('j',$_GET['date0'])-2-$diff_date,date('Y',$_GET['date0']));
        
        $date_01 = $_GET['date0'];
        $date_07 = mktime(0,0,0,date('n',$_GET['date0']),date('j',$_GET['date0'])-1-$diff_date,date('Y',$_GET['date0']));
        $date_11 = $_GET['date1'];
        $date_17 = mktime(0,0,0,date('n',$_GET['date0']),date('j',$_GET['date0'])-1,date('Y',$_GET['date0']));
        
        
        $periode_i = 'du '.date('d/m/Y', $date_0i).'<br />au '.date('d/m/Y', $date_1i);
        $periode_it = date('d/m/Y', $date_0i).' au '.date('d/m/Y', $date_1i);
        $periode_7 = 'du '.date('d/m/Y', $date_07).'<br />au '.date('d/m/Y', $date_17);
        $periode_7t = date('d/m/Y', $date_07).' au '.date('d/m/Y', $date_17);
        $periode_1 = 'du '.date('d/m/Y', $_GET['date0']).'<br />au '.date('d/m/Y', $_GET['date1']);
    }
    
} //sinon c un jour
else {
    //c'est aujourd'hui
    if($_GET['date0'] == mktime(0,0,0)) {
    	$date_0i = mktime(0,0,0, date('n', $_GET['date0']), date('j', $_GET['date0'])-15, date('Y', $_GET['date0']));
    	$date_1i = $date_0i;
    	
        $date_07 = mktime(0,0,0, date('n', $_GET['date0']), date('j', $_GET['date0'])-8, date('Y', $_GET['date0']));
        $date_01 = mktime(0,0,0, date('n', $_GET['date0']), date('j', $_GET['date0'])-1, date('Y', $_GET['date0']));
        $date_17 = $date_07;
        $date_11 = $date_01;
        
        $periode_i = date('d/m/Y', $date_0i);
        $periode_it = date('d/m/Y', $date_0i);
        $periode_7 = date('d/m/Y', $date_07);
        $periode_7t = date('d/m/Y', $date_07);
        $periode_1 = 'Hier';
    }
    //date quelquonque
    else {
        $date_0i = mktime(0,0,0, date('n', $_GET['date0']), date('j', $_GET['date0'])-14, date('Y', $_GET['date0']));
        $date_1i = $date_0i;
    	
    	$date_07 = mktime(0,0,0, date('n', $_GET['date0']), date('j', $_GET['date0'])-7, date('Y', $_GET['date0']));
        $date_01 =  $_GET['date0'];
        $date_17 = $date_07;
        $date_11 = $date_01;
        
        $periode_i = date('d/m/Y', $date_0i);
        $periode_it = date('d/m/Y', $date_0i);
        $periode_7 = date('d/m/Y', $date_07);
        $periode_7t = date('d/m/Y', $date_07);
        $periode_1 = date('d/m/Y', $date_01);        
    }
}

//liste des catid descendante pour les stats

$libelle_req = $DB_site2->query_first("SELECT $libelle FROM categorie WHERE catid = '$catid' AND catid NOT IN(".implode(',', $notin_array).") LIMIT 1;");
if($libelle_req) {
	$trilibelle = $libelle_req[0];
}
else {
	$catid = 0;
}

$catid_deeplist = array($catid);

$childlist = array($catid);

if($catid) {
	do {
		
		$deeplist_req = $DB_site2->query("SELECT catid FROM categorie WHERE parentid IN(".implode(',', $childlist).") AND catid NOT IN(".implode(',', $notin_array).");");
		
		$childlist = array();
		
		while($deeplist = $DB_site2->fetch_array($deeplist_req)) {
			$childlist[] = $deeplist[0];
			$catid_deeplist[] = $deeplist[0];
		}
		
	}
	while(sizeof($childlist));
}

switch ($orderby) {
	case 'vues1' :
	$orderbyclause = ', vues1';	
	break;
	case 'ajouts1' :
	$orderbyclause = ', ajouts1';	
	break;
	case 'cde1' :
	$orderbyclause = ', cde1';	
	break;
	
	case 'vues7' :
	$orderbyclause = ', vues7';	
	break;
	case 'ajouts7' :
	$orderbyclause = ', ajouts7';	
	break;
	case 'cde7' :
	$orderbyclause = ', cde7';	
	break;
	
	case 'vuesn' :
	$orderbyclause = ', vuesn';	
	break;
	case 'ajoutsn' :
	$orderbyclause = ', ajoutsn';	
	break;
	case 'cden' :
	$orderbyclause = ', cden';	
	break;
	
	case 'artlibelle' : 
	default : 
	$orderbyclause = ', artlibelle';
	break;
}

switch ($sens) {
	default :
	case 'asc' :
	$orderbyclause .= ' ASC';
	break;
	case 'desc' :
	$orderbyclause .= ' DESC';
	break;	
}

//il vas falloir une super requete sur ce coup la !!

$req = "SELECT stats1.*, statsi.*, stats7.*, statsn.*, categorie.$libelle as catlibelle, article.$libelle as artlibelle, categorie.catid, article.artcode FROM 
	(SELECT DISTINCT artid FROM stat_article) as statart
	LEFT JOIN
	article USING(artid)
	LEFT JOIN
	categorie USING(catid)
	LEFT JOIN
	(SELECT artid as artid1, SUM(ajouts) as ajouts1, SUM(vues) as vues1, SUM(cde1) as cde1 FROM stat_article LEFT JOIN article USING(artid) LEFT JOIN (SELECT SUM(qte) as cde1, artid as cda1, datefacture as date1 FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) AND facture.datefacture BETWEEN FROM_UNIXTIME('$date_01') AND FROM_UNIXTIME('$date_11') GROUP BY artid, datefacture) as fct1 ON(fct1.cda1 = stat_article.artid AND fct1.date1 = stat_article.dateline) WHERE stat_article.dateline BETWEEN FROM_UNIXTIME('$date_01') AND FROM_UNIXTIME('$date_11') GROUP BY stat_article.artid) as stats1 ON(statart.artid = stats1.artid1)
	LEFT JOIN
	(SELECT artid as artidi, SUM(ajouts) as ajoutsi, SUM(vues) as vuesi, SUM(cdei) as cdei FROM stat_article LEFT JOIN article USING(artid) LEFT JOIN (SELECT SUM(qte) as cdei, artid as cdai, datefacture as datei FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) AND facture.datefacture BETWEEN FROM_UNIXTIME('$date_0i') AND FROM_UNIXTIME('$date_1i') GROUP BY artid, datefacture) as fcti ON(fcti.cdai = stat_article.artid AND fcti.datei = stat_article.dateline) WHERE stat_article.dateline BETWEEN FROM_UNIXTIME('$date_0i') AND FROM_UNIXTIME('$date_1i') GROUP BY stat_article.artid) as statsi ON(statart.artid = statsi.artidi)
	LEFT JOIN
	(SELECT artid as artid7, SUM(ajouts) as ajouts7, SUM(vues) as vues7, SUM(cde7) as cde7 FROM stat_article LEFT JOIN article USING(artid) LEFT JOIN (SELECT SUM(qte) as cde7, artid as cda7, datefacture as date7 FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) AND facture.datefacture BETWEEN FROM_UNIXTIME('$date_07') AND FROM_UNIXTIME('$date_17') GROUP BY artid, datefacture) as fct7 ON(fct7.cda7 = stat_article.artid AND fct7.date7 = stat_article.dateline) WHERE stat_article.dateline BETWEEN FROM_UNIXTIME('$date_07') AND FROM_UNIXTIME('$date_17') GROUP BY stat_article.artid) as stats7 ON(statart.artid = stats7.artid7)
	LEFT JOIN
	(SELECT artid as artidn, ajouts as ajoutsn, vues as vuesn, cden FROM stat_article LEFT JOIN article USING(artid) LEFT JOIN (SELECT SUM(qte) as cden, artid as cdan, datefacture as daten FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) AND facture.datefacture BETWEEN FROM_UNIXTIME('$date_0n') AND FROM_UNIXTIME('$date_1n') GROUP BY artid, datefacture) as fctn ON(fctn.cdan = stat_article.artid AND fctn.daten = stat_article.dateline) WHERE stat_article.dateline = FROM_UNIXTIME('$date_n')) as statsn ON(statart.artid = statsn.artidn)
	WHERE (artid1 IS NOT NULL OR artid7 IS NOT NULL OR artidn IS NOT NULL)".($catid ? " AND article.catid IN(".implode(',', $catid_deeplist).")" : '')." ORDER BY catlibelle ASC, catid ASC$orderbyclause;";

$super_requete = $DB_site2->query($req);

function print_progress($ratio, $unit = '%') {
	
	$pr_pr = '<font style="font-style:italic;font-size:9px;';
	
	if($ratio < 0) {
		$pr_pr .= 'color: red;">('.number_format($ratio, '1', ',', '').' '.$unit.')</font>';
	}
	elseif($ratio > 0) {
		$pr_pr .= 'color: green;">('.number_format($ratio, '1', ',', '').' '.$unit.')</font>';
	}
	else {
		$pr_pr .= 'color: blue;">( = )</font>';
	}
	
	return $pr_pr;
}

?>
<h2 align="center"><?=($catid ? $trilibelle : 'Boutique')?></h2>

<table class="stats">

<tr>
<th rowspan="2"><a href="statsarticle.php?date0=<?=$_GET['date0']?>&date1=<?=$_GET['date1']?>&catid=<?=$_GET['catid']?>&orderby=artlibelle&sens=<?=$_GET['sens'] == 'asc' ? 'desc' : 'asc'?>">Articles</a></th>
<th colspan="3"><?=$periode_7?></th>
<th colspan="3"><?=$periode_1?></th>
<th colspan="3"><?=$periode_n?></th>
</tr>
<tr>
<th><a href="statsarticle.php?date0=<?=$_GET['date0']?>&date1=<?=$_GET['date1']?>&catid=<?=$_GET['catid']?>&orderby=vues7&sens=<?=$_GET['sens'] == 'asc' ? 'desc' : 'asc'?>">Vues</a></th>
<th><a href="statsarticle.php?date0=<?=$_GET['date0']?>&date1=<?=$_GET['date1']?>&catid=<?=$_GET['catid']?>&orderby=ajouts7&sens=<?=$_GET['sens'] == 'asc' ? 'desc' : 'asc'?>">Panier</a></th>
<th><a href="statsarticle.php?date0=<?=$_GET['date0']?>&date1=<?=$_GET['date1']?>&catid=<?=$_GET['catid']?>&orderby=cde7&sens=<?=$_GET['sens'] == 'asc' ? 'desc' : 'asc'?>">Cdes</a></th>

<th><a href="statsarticle.php?date0=<?=$_GET['date0']?>&date1=<?=$_GET['date1']?>&catid=<?=$_GET['catid']?>&orderby=vues1&sens=<?=$_GET['sens'] == 'asc' ? 'desc' : 'asc'?>">Vues</a></th>
<th><a href="statsarticle.php?date0=<?=$_GET['date0']?>&date1=<?=$_GET['date1']?>&catid=<?=$_GET['catid']?>&orderby=ajouts1&sens=<?=$_GET['sens'] == 'asc' ? 'desc' : 'asc'?>">Panier</a></th>
<th><a href="statsarticle.php?date0=<?=$_GET['date0']?>&date1=<?=$_GET['date1']?>&catid=<?=$_GET['catid']?>&orderby=cde1&sens=<?=$_GET['sens'] == 'asc' ? 'desc' : 'asc'?>">Cdes</a></th>

<th><a href="statsarticle.php?date0=<?=$_GET['date0']?>&date1=<?=$_GET['date1']?>&catid=<?=$_GET['catid']?>&orderby=vuesn&sens=<?=$_GET['sens'] == 'asc' ? 'desc' : 'asc'?>">Vues</a></th>
<th><a href="statsarticle.php?date0=<?=$_GET['date0']?>&date1=<?=$_GET['date1']?>&catid=<?=$_GET['catid']?>&orderby=ajoutsn&sens=<?=$_GET['sens'] == 'asc' ? 'desc' : 'asc'?>">Panier</a></th>
<th><a href="statsarticle.php?date0=<?=$_GET['date0']?>&date1=<?=$_GET['date1']?>&catid=<?=$_GET['catid']?>&orderby=cden&sens=<?=$_GET['sens'] == 'asc' ? 'desc' : 'asc'?>">Cdes</a></th>
</tr>

<?php 

$dispcatid = "";

$sum_vues_7 = 0;
$sum_ajouts_7 = 0;
$sum_cde_7 = 0;

$sum_vues_1 = 0;
$sum_ajouts_1 = 0;
$sum_cde_1 = 0;

$sum_vues_n = 0;
$sum_ajouts_n = 0;
$sum_cde_n = 0;

while($stat_req = $DB_site2->fetch_array($super_requete)) {
	if($dispcatid != $stat_req['catid']) {
		echo '<tr><th style="border-bottom: 1px dashed black; border-top: 1px dashed black;" colspan="10">'.$stat_req['catlibelle'].'</th></tr>';
	}
	$dispcatid = $stat_req['catid'];
	
	//calcul des ratio
	$ratio_vue_7 = $stat_req['vuesi'] ? $stat_req['vues7']/$stat_req['vuesi']*100-100 : '0';
	$ratio_ajout_7 = $stat_req['ajoutsi'] ? $stat_req['ajouts7']/$stat_req['ajoutsi']*100-100 : '0';
	$ratio_cde_7 = $stat_req['cdei'] ? $stat_req['cde7']/$stat_req['cdei']*100-100 : '0';

	$ratio_vue_1 = $stat_req['vues7'] ? $stat_req['vues1']/$stat_req['vues7']*100-100 : '0';
	$ratio_ajout_1 = $stat_req['ajouts7'] ? $stat_req['ajouts1']/$stat_req['ajouts7']*100-100 : '0';
	$ratio_cde_1 = $stat_req['cde7'] ? $stat_req['cde1']/$stat_req['cde7']*100-100 : '0';
	
	$sum_vues_7 += $stat_req['vues7'];
	$sum_ajouts_7 += $stat_req['ajouts7'];
	$sum_cde_7 += $stat_req['cde7'];
	
	$sum_vues_1 += $stat_req['vues1'];
	$sum_ajouts_1 += $stat_req['ajouts1'];
	$sum_cde_1 += $stat_req['cde1'];
	
	$sum_vues_n += $stat_req['vuesn'];
	$sum_ajouts_n += $stat_req['ajoutsn'];
	$sum_cde_n += $stat_req['cden'];
	?>
	<tr>
	<td class="art"><span title="<?=$stat_req['artcode']?>"><?=$stat_req['artlibelle']?></span></td>
	
	<td align="right" class="bar" nowrap><?=$stat_req['vues7'] ? $stat_req['vues7'] : 0?> <?=print_progress($ratio_vue_7)?></td>
	<td align="right" nowrap><?=$stat_req['ajouts7'] ? $stat_req['ajouts7'] : 0?> <?=print_progress($ratio_ajout_7)?></td>
	<td align="right" nowrap><?=$stat_req['cde7'] ? $stat_req['cde7'] : 0?> <?=print_progress($ratio_cde_7)?></td>
	
	<td align="right" class="bar" nowrap><?=$stat_req['vues1'] ? $stat_req['vues1'] : 0?> <?=print_progress($ratio_vue_1)?></td>
	<td align="right" nowrap><?=$stat_req['ajouts1'] ? $stat_req['ajouts1'] : 0?> <?=print_progress($ratio_ajout_1)?></td>
	<td align="right" nowrap><?=$stat_req['cde1'] ? $stat_req['cde1'] : 0?> <?=print_progress($ratio_cde_1)?></td>
	
	<td align="right" class="bar" nowrap><?=$stat_req['vuesn'] ? $stat_req['vuesn'] : 0?></td>
	<td align="right" nowrap><?=$stat_req['ajoutsn'] ? $stat_req['ajoutsn'] : 0?></td>
	<td align="right" nowrap><?=$stat_req['cden'] ? $stat_req['cden'] : 0?></td>
	</tr>
	<?php
}
?>

	<tr>
	<th><div align="left">Total</div></th>
	
	<th class="bar" nowrap><div align="right"><?=$sum_vues_7 ? $sum_vues_7 : 0?></div></th>
	<th nowrap><div align="right"><?=$sum_ajouts_7 ? $sum_ajouts_7 : 0?></div></th>
	<th nowrap><div align="right"><?=$sum_cde_7 ? $sum_cde_7 : 0?></div></th>
	
	<th class="bar" nowrap><div align="right"><?=$sum_vues_1 ? $sum_vues_1 : 0?></div></th>
	<th nowrap><div align="right"><?=$sum_ajouts_1 ? $sum_ajouts_1 : 0?></div></th>
	<th nowrap><div align="right"><?=$sum_cde_1 ? $sum_cde_1 : 0?></div></th>
	
	<th class="bar" nowrap><div align="right"><?=$sum_vues_n ? $sum_vues_n : 0?></div></th>
	<th nowrap><div align="right"><?=$sum_ajouts_n ? $sum_ajouts_n : 0?></div></th>
	<th nowrap><div align="right"><?=$sum_cde_n ? $sum_cde_n : 0?></div></th>
	</tr>



</table>

<?php

echo "</td>
   <td background=\"images2/cadre_r4_c10.gif\" width=\"3\" height=\"100%\" border=\"0\" alt=\"\"></td>
  </tr>
</table>
</td>
</tr>
<tr><td>

<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td rowspan=\"2\"><img name=\"cadre_r6_c2\" src=\"images2/cadre_r6_c2.gif\" width=\"10\" height=\"26\" border=\"0\" alt=\"\"></td>
   <td align=\"center\" height=\"23\" width=\"100%\"></td>
   <td rowspan=\"2\"><img name=\"cadre_r6_c9\" src=\"images2/cadre_r6_c9.gif\" width=\"8\" height=\"26\" border=\"0\" alt=\"\"></td>
  </tr>
  <tr>
   <td background=\"images2/cadre_r7_c4.gif\" width=\"100%\" height=\"3\"></td>
  </tr>
</table>
</td></tr></table>
</td>
</tr><tr>
</tr></table>
                  </td>
              </tr>
            </table>
          </td>
        </tr>";

?>

<?php
include "./footer.php";
?> 