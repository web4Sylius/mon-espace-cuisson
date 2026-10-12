<?php

/** Statistiques Boutiques
 * Fichiers necessaire au fonctionnement :
 *  - Ce fichier
 *  - stats.php (include dans le global aprés la verification utilisateur)
 *  - stat_panier.php (include dans le global au moment de l'ajout panier, ou dans addProduct.php dans la version AJAX)
 *  - statsgraphs.php (plus d'info dans ce fichier...)
 *  - statsarticle.php (Details des statistiques par articles)
 *  - statsusers.php (Details des statistiques par utilisateur)
 *  
 * 
 * Tables necessaire au fonctionnement :
 * - stat_quotidien (statistique de frequentation)
 * - stat_article (statistiques par article)
 * - stat_geoloc (statistiques de frequentation par pays)
 * - stat_pageunique (table de session temporaire pour identifier les visites sortantes)
 * - stat_user (statistiques de frequentation utilisateurs)
 * - stat_user_article (statistiques d'interet d'article par utilisateur)
 * - codetocountry
 * - iptocountry
 * 
 * Tables devant se trouver dans la boutique
 * - facture, lignefacture, article, categorie (suivi d'activité, et statistiques par articles)
 * - utilisateurs (statistiques utilisateurs)
 * 
 * 
 * Configuration :
 * Configurer la variable "$notin_array" avec les categorie (catid) à ignorer lors des divers statistiques
 * S'il n'ya pas de catégories a ignorer, renseigner la variable avec une array contenant '0'
 * 
 */


require "admin_global.php";
include "./header.php";
include "./menu.php";

$notin_array = array(0);

setlocale(LC_TIME, "fr_FR");
?>

<style type="text/css">
.calendar {
    border-collapse: collapse;
    border: 1px solid black;
    text-align: center;
    font-weight: bold;
}

.calendar td {
    background-color: #EEEEFF;
    padding: 3px;
    font-size: 10px;
}

.calendar td a {
    text-decoration: none;
    color: #000000;
}

.calendar td a:hover {
    text-decoration: underline;
}

.calendar td.now {
    background-color: #000099;
    color: #FFFFFF;
}

.calendar th {
    background-color: #9999FF;
    font-size: 10px;
    color: #FFFFFF;
}

.calendar th.day {
    background-color: #FFFFFF;    
    color: black;
}

.calendar th a {
    text-decoration: none;
    color: #FFFFFF;
}

.calendar th a:hover {
    text-decoration: underline;
}

.records {
    border-collapse: collapse;
    border: 1px solid black;
    text-align: left;
    font-weight: bold;
    width: 9em;
}

.records td {
    background-color: #EEEEFF;
    padding: 3px;
    font-size: 10px;
}

.records td a {
    text-decoration: none;
    color: #000000;
}

.records td a:hover {
    text-decoration: underline;
}

.records th {
    text-align: center;
    background-color: #9999FF;
    font-size: 10px;
    color: #FFFFFF;
}

.records th a {
    text-decoration: none;
    color: #FFFFFF;
}

.records th a:hover {
    text-decoration: underline;
}

.stats {
    border-collapse: collapse;
    border: 1px solid black;
    text-align: left;
    font-weight: bold;
    width: 90%;
}

.stats td {
    background-color: #EEEEFF;
    padding: 3px;
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
}

.stats th a {
    text-decoration: none;
    color: #FFFFFF;
}

.stats th a:hover {
    text-decoration: underline;
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


// Arbre de navigation boutique

//depliement 
$nav_deplie = "";

if ($catid || $artid) 
	{
	if($artid) 
		{
		$start_req = $DB_site2->query_first("SELECT catid FROM article WHERE artid = '$artid' LIMIT 1;");
		if($start_req) 
			{
			$startcatid = $start_req[0];
			}
		else 
			{
			$artid = 0;
			}
		}
	else 
		{
		$libelle_req = $DB_site2->query_first("SELECT $libelle FROM categorie WHERE catid = '$catid' AND catid NOT IN(".implode(',', $notin_array).") LIMIT 1;");
		if($libelle_req) 
			{
			$trilibelle = $libelle_req[0];
			}
		else 
			{
			$catid = 0;
			}
		$startcatid = $catid;
		}
	
	//liste des catid descendante pour les stats
	$catid_deeplist = array($catid);
	
	$childlist = array($catid);
	
	if($catid) 
		{
		do 
			{
			$deeplist_req = $DB_site2->query("SELECT catid FROM categorie WHERE parentid IN(".implode(',', $childlist).") AND catid NOT IN(".implode(',', $notin_array).");");
			
			$childlist = array();
			
			while($deeplist = $DB_site2->fetch_array($deeplist_req)) 
				{
				$childlist[] = $deeplist[0];
				$catid_deeplist[] = $deeplist[0];
				}			
			}
		while(sizeof($childlist));
		}
	
	
	//premier listage
	$first_req = $DB_site2->query("SELECT 'cat' as type, catid as id, $libelle FROM categorie WHERE parentid = '$startcatid'
		UNION ALL SELECT 'art' as type, artid as id, $libelle FROM article WHERE catid = '$startcatid' ORDER BY type DESC, $libelle ASC;");
	
	while($first_list = $DB_site2->fetch_array($first_req)) 
		{
		if($first_list[0] == 'cat') 
			{
			$nav_deplie .= '<li class="lcat"><a href="'.$_SERVER['PHP_SELF'].'?catid='.$first_list[1].'&date0='.$_GET['date0'].'&date1='.$_GET['date1'].'" title="Limiter au rayon : '.$first_list[2].'">'.$first_list[2].'</a></li>';
			}
		else 
			{
			if($artid == $first_list[1]) 
				{
				$trilibelle = $first_list[2];
				$nav_deplie .= '<li class="lart" style="font-weight: bold;"><a href="'.$_SERVER['PHP_SELF'].'?artid='.$first_list[1].'&date0='.$_GET['date0'].'&date1='.$_GET['date1'].'" title="Limiter au rayon : '.$first_list[2].'">'.$first_list[2].'</a></li>';
				}
			else 
				{
				$nav_deplie .= '<li class="lart"><a href="'.$_SERVER['PHP_SELF'].'?artid='.$first_list[1].'&date0='.$_GET['date0'].'&date1='.$_GET['date1'].'" title="Limiter au rayon : '.$first_list[2].'">'.$first_list[2].'</a></li>';
				}
			}
		}
	
	
	while(true) 
		{
		$deplie_nav_req = $DB_site2->query_first("SELECT categorie.catid, categorie.$libelle, categorie.parentid FROM categorie INNER JOIN categorie as c ON(categorie.parentid = c.catid) WHERE categorie.catid = '$startcatid' AND c.catid NOT IN(".implode(',',$notin_array).") AND c.parentid != 0 LIMIT 1;");
		
		if($deplie_nav_req) 
			{
			if($catid == $deplie_nav_req[0]) 
				{
				$nav_deplie = '<li style="font-weight: bold;" class="lcat"><a href="'.$_SERVER['PHP_SELF'].'?catid='.$deplie_nav_req[0].'&date0='.$_GET['date0'].'&date1='.$_GET['date1'].'" title="Limiter au rayon : '.$deplie_nav_req[1].'">'.$deplie_nav_req[1].'</a></li>'.'<ul>'.$nav_deplie.'</ul>';
				}
			else 
				{
				$nav_deplie = '<li class="lcat"><a href="'.$_SERVER['PHP_SELF'].'?catid='.$deplie_nav_req[0].'&date0='.$_GET['date0'].'&date1='.$_GET['date1'].'" title="Limiter au rayon : '.$deplie_nav_req[1].'">'.$deplie_nav_req[1].'</a></li>'.'<ul>'.$nav_deplie.'</ul>';
				}
		
			$startcatid = $deplie_nav_req[2];
			}
		else 
			{
			break;
			}
		}
	}

//toplevel
$top_nav_req = $DB_site2->query("SELECT catid, $libelle FROM categorie WHERE parentid = '0' AND catid NOT IN(".implode(',', $notin_array).");");

$nav_cat_art = '>> <a style="font-size: 12px;'.(!$catid && !$artid ? 'font-weight: bold;' : '').'" href="'.$_SERVER['PHP_SELF'].'?date0='.$_GET['date0'].'&date1='.$_GET['date1'].'" title="Toute la Boutique">Toute la Boutique</a><br /><br />';

while($top_nav = $DB_site2->fetch_array($top_nav_req)) 
	{
	$nav_cat_art .= ' - <a '.($catid == $top_nav[0] ? 'style="font-weight: bold;"' : '').' href="'.$_SERVER['PHP_SELF'].'?catid='.$top_nav[0].'&date0='.$_GET['date0'].'&date1='.$_GET['date1'].'" title="Toute le catalogue : '.$top_nav[1].'">'.$top_nav[1].'</a>';
	$second_nav_req = $DB_site2->query("SELECT catid, $libelle FROM categorie WHERE parentid = '".$top_nav[0]."' AND catid NOT IN(".implode(',', $notin_array).");");
	$nav_cat_art .= '<ul>';
	while($second_nav = $DB_site2->fetch_array($second_nav_req)) 
		{
		if($catid == $second_nav[0]) 
			{
			$nav_cat_art .= '<li style="font-weight: bold;" class="lcat"><a href="'.$_SERVER['PHP_SELF'].'?catid='.$second_nav[0].'&date0='.$_GET['date0'].'&date1='.$_GET['date1'].'" title="Limiter au rayon : '.$second_nav[1].'">'.$second_nav[1].'</a></li>';
			}
		else 
			{
			$nav_cat_art .= '<li class="lcat"><a href="'.$_SERVER['PHP_SELF'].'?catid='.$second_nav[0].'&date0='.$_GET['date0'].'&date1='.$_GET['date1'].'" title="Limiter au rayon : '.$second_nav[1].'">'.$second_nav[1].'</a></li>';
			}
		if($startcatid == $second_nav[0]) 
			{
			$nav_cat_art .= '<ul>'.$nav_deplie.'</ul>';
			}
		}
	$nav_cat_art .= '</ul>';
	}

//generate calendar
$calendar = array();

if(!isset($_GET['date0']) || !$_GET['date0']) 
	{
    $_GET['date0'] = mktime(0,0,0);
	}
if(!isset($_GET['date1']) || !$_GET['date1']) 
	{
    $_GET['date1'] = mktime(0,0,0);
	}
if($_GET['date0'] > $_GET['date1']) 
	{
    $_GET['date1'] = $_GET['date0'];
	}

for($i = 0 ; $i < 2 ; $i++) 
	{
    $datetouse = $_GET['date'.$i] ? $_GET['date'.$i] : mktime(0,0,0);
    
    $calendar[$i] = '<table class="calendar">
        <tr>
            <th colspan="2"><div align="left"> <a href="'.$_SERVER['PHP_SELF'].'?artid='.$_GET['artid'].'&catid='.$_GET['catid'].'&date'.$i.'='.mktime(0,0,0,date('n',$datetouse), date('j', $datetouse), date('Y', $datetouse)-1).'&date'.(($i+1)%2).'='.$_GET['date'.($i+1)%2].'" title="'.(date('Y', $datetouse)-1).'"><<</a> </div></td>
            <th colspan="3"> '.date('Y', $datetouse).' </td>
            <th colspan="2"><div align="right"> <a href="'.$_SERVER['PHP_SELF'].'?artid='.$_GET['artid'].'&catid='.$_GET['catid'].'&date'.$i.'='.mktime(0,0,0,date('n',$datetouse), date('j', $datetouse), date('Y', $datetouse)+1).'&date'.(($i+1)%2).'='.$_GET['date'.($i+1)%2].'" title="'.(date('Y', $datetouse)+1).'">>></a> </div></td>
        </tr>
        <tr>
            <th class="day">L</td>    
            <th class="day">M</td>    
            <th class="day">M</td>    
            <th class="day">J</td>    
            <th class="day">V</td>    
            <th class="day">S</td>    
            <th class="day">D</td>    
        </tr>
            ';
    
    $calendar[$i] .= '<tr>';
    
    $firsday = date('N', mktime(0,0,0,date('n',$datetouse), 1, date('Y', $datetouse)));
    $lastday = date('N', mktime(0,0,0,date('n', $datetouse)+1, 0, date('Y', $datetouse)));
    
    for($d = 1 ; $d < $firsday ; $d++) 
		{
        $calendar[$i] .= '<td>&nbsp;</td>';
    	}
    
    for($j = 0 ; $j < date('t', $datetouse) ; $j++) 
		{
        if (($j+$firsday-1)%7 == 0) 
			{
            $calendar[$i] .= '</tr>
            <tr>';
        	}
        if($j+1 == date('j', $datetouse)) 
			{
            $calendar[$i] .= '<td class="now">'.($j+1).'</td>';
        	}
        else 
			{
            $calendar[$i] .= '<td><a href="'.$_SERVER['PHP_SELF'].'?artid='.$_GET['artid'].'&catid='.$_GET['catid'].'&date'.$i.'='.mktime(0,0,0,date('n',$datetouse), $j+1, date('Y', $datetouse)).'&date'.(($i+1)%2).'='.$_GET['date'.($i+1)%2].'" title="'.($j+1).'">'.($j+1).'</a></td>';
        	}
    	}
    
    for($l = 7 ; $l > $lastday ; $l--) 
		{
        $calendar[$i] .= '<td>&nbsp;</td>';
    	}
    
    $calendar[$i] .= '</tr>';
    
    
    $calendar[$i] .= '<tr>
            <th colspan="2"><div align="left"> <a href="'.$_SERVER['PHP_SELF'].'?artid='.$_GET['artid'].'&catid='.$_GET['catid'].'&date'.$i.'='.mktime(0,0,0,date('n',$datetouse)-1, date('j', $datetouse), date('Y', $datetouse)).'&date'.(($i+1)%2).'='.$_GET['date'.($i+1)%2].'" title="'.strftime('%B', mktime(0,0,0,date('n',$datetouse)-1)).'"><<</a> </div></td>
            <th colspan="3"> '.strftime('%B', $datetouse).' </td>
            <th colspan="2"><div align="right"> <a href="'.$_SERVER['PHP_SELF'].'?artid='.$_GET['artid'].'&catid='.$_GET['catid'].'&date'.$i.'='.mktime(0,0,0,date('n',$datetouse)+1, date('j', $datetouse), date('Y', $datetouse)).'&date'.(($i+1)%2).'='.$_GET['date'.($i+1)%2].'" title="'.strftime('%B', mktime(0,0,0,date('n',$datetouse)+1)).'">>></a> </div></td>
        </tr>
    </table>';
	}

$records_vues_req = $DB_site2->query_first("SELECT vues, UNIX_TIMESTAMP(dateline) FROM stat_quotidien ORDER BY vues DESC LIMIT 1;");
$records_visiteurs_req = $DB_site2->query_first("SELECT visiteurs, UNIX_TIMESTAMP(dateline) FROM stat_quotidien ORDER BY visiteurs DESC LIMIT 1;");
$records_visites_req = $DB_site2->query_first("SELECT visites, UNIX_TIMESTAMP(dateline) FROM stat_quotidien ORDER BY visites DESC LIMIT 1;");
$records_visites1p_req = $DB_site2->query_first("SELECT visites1p, UNIX_TIMESTAMP(dateline) FROM stat_quotidien ORDER BY visites1p ASC LIMIT 1;");
$records_ratio_req = $DB_site2->query_first("SELECT visites1p/visites, UNIX_TIMESTAMP(dateline) FROM stat_quotidien ORDER BY visites1p/visites ASC LIMIT 1;");


//records
$record = '<table class="records">
<tr><th> - Records - </th></tr>
<tr><td>Visiteurs : '.$records_visiteurs_req[0].'<div align="right" style="font-style:italic">'.date('d/m/Y', $records_visiteurs_req[1]).'</div></td></tr>
<tr><td>Visites : '.$records_visites_req[0].'<div align="right" style="font-style:italic">'.date('d/m/Y', $records_visites_req[1]).'</div></td></tr>
<tr><td>Pages Vues : '.$records_vues_req[0].'<div align="right" style="font-style:italic">'.date('d/m/Y', $records_vues_req[1]).'</div></td></tr>
<tr><td>Visite à 1 Page : '.$records_visites1p_req[0].'<div align="right" style="font-style:italic">'.date('d/m/Y', $records_visites1p_req[1]).'</div></td></tr>
<tr><td>Ratio : '.number_format($records_ratio_req[0]*100, '1', ',', '').' %<div align="right" style="font-style:italic">'.date('d/m/Y', $records_ratio_req[1]).'</div></td></tr>
';

if(!$catid && !$artid) 
	{
    $record .= '<tr><th> - Boutique - </th></tr>';
    $ca_req = $DB_site2->query_first("SELECT UNIX_TIMESTAMP(datefacture), SUM(tot) as rec FROM (SELECT facture.facturid, facture.datefacture, sum((lignefacture.prix*lignefacture.qte)
	)
	+facture.prixport
	+facture.prixgestion
	+IF(facture.moyenid = 3, facture.prix_contre_remboursement, 0)
	-IF(facture.cadeauid > 0, facture.montantcadeau, 0)
	-IF(facture.remiseid > 0, facture.montantremise, 0)
	as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) GROUP BY lignefacture.factureid) as fs GROUP BY datefacture ORDER BY rec DESC;");
		$record .= '<tr><td>C.A. : '.number_format($ca_req['rec'], '2', ',', ' ').' &euro;<div align="right" style="font-style: italic">'.date('d/m/Y', $ca_req[0]).'</div></td></tr>';
		
		$vues_req = $DB_site2->query_first("SELECT UNIX_TIMESTAMP(dateline), SUM(vues) as rec FROM stat_article GROUP BY dateline ORDER BY rec DESC;");
		$record .= '<tr><td>Vues articles: '.$vues_req['rec'].'<div align="right" style="font-style: italic">'.date('d/m/Y', $vues_req[0]).'</div></td></tr>';
		
		$ajouts_req = $DB_site2->query_first("SELECT UNIX_TIMESTAMP(dateline), SUM(ajouts) as rec FROM stat_article GROUP BY dateline ORDER BY rec DESC;");
		$record .= '<tr><td>Ajouts panier: '.$ajouts_req['rec'].'<div align="right" style="font-style: italic">'.date('d/m/Y', $ajouts_req[0]).'</div></td></tr>';
	}
elseif ($catid) 
	{
    $record .= '<tr><th> - '.$trilibelle.' - </th></tr>';
    $ca_req = $DB_site2->query_first("SELECT UNIX_TIMESTAMP(datefacture), SUM(tot) as rec FROM (SELECT facture.facturid, facture.datefacture, sum((lignefacture.prix*lignefacture.qte)
	)
	as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) AND lignefacture.artid IN(SELECT article.artid FROM article WHERE article.catid IN(".implode(',',$catid_deeplist).")) GROUP BY lignefacture.factureid) as fs GROUP BY datefacture ORDER BY rec DESC;");
		$record .= '<tr><td> C.A. : '.number_format($ca_req['rec'], '2', ',', ' ').' &euro;<div align="right" style="font-style: italic">'.($ca_req[0] ? date('d/m/Y', $ca_req[0]) : '').'</div></td></tr>';
		
		$vues_req = $DB_site2->query_first("SELECT UNIX_TIMESTAMP(dateline), SUM(vues) as rec FROM stat_article WHERE stat_article.artid IN(SELECT article.artid FROM article WHERE article.catid IN(".implode(',',$catid_deeplist).")) GROUP BY dateline ORDER BY rec DESC;");
		$record .= '<tr><td>Vues articles: '.$vues_req['rec'].'<div align="right" style="font-style: italic">'.($vues_req[0] ? date('d/m/Y', $vues_req[0]) : '').'</div></td></tr>';
		
		$ajouts_req = $DB_site2->query_first("SELECT UNIX_TIMESTAMP(dateline), SUM(ajouts) as rec FROM stat_article WHERE stat_article.artid IN(SELECT article.artid FROM article WHERE article.catid IN(".implode(',',$catid_deeplist).")) GROUP BY dateline ORDER BY rec DESC;");
		$record .= '<tr><td>Ajouts panier: '.$ajouts_req['rec'].'<div align="right" style="font-style: italic">'.($ajouts_req[0] ? date('d/m/Y', $ajouts_req[0]) : '').'</div></td></tr>';
		
	}
elseif ($artid) 
	{
		$record .= '<tr><th> - '.$trilibelle.' - </th></tr>';
		$ca_req = $DB_site2->query_first("SELECT UNIX_TIMESTAMP(datefacture), SUM(tot) as rec FROM (SELECT facture.facturid, facture.datefacture, sum((lignefacture.prix*lignefacture.qte)
	)
	as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) AND lignefacture.artid = '$artid' GROUP BY lignefacture.factureid) as fs GROUP BY datefacture ORDER BY rec DESC;");
		$record .= '<tr><td>C.A. : '.number_format($ca_req['rec'], '2', ',', ' ').' &euro;<div align="right" style="font-style: italic">'.($ca_req[0] ? date('d/m/Y', $ca_req[0]) : '').'</div></td></tr>';
		
		$vues_req = $DB_site2->query_first("SELECT UNIX_TIMESTAMP(dateline), SUM(vues) as rec FROM stat_article WHERE artid = '$artid' GROUP BY dateline ORDER BY rec DESC;");
		$record .= '<tr><td>Vues article: '.$vues_req['rec'].'<div align="right" style="font-style: italic">'.($vues_req[0] ? date('d/m/Y', $vues_req[0]) : '').'</div></td></tr>';
		
		$ajouts_req = $DB_site2->query_first("SELECT UNIX_TIMESTAMP(dateline), SUM(ajouts) as rec FROM stat_article WHERE artid = '$artid' GROUP BY dateline ORDER BY rec DESC;");
		$record .= '<tr><td>Ajouts panier: '.$ajouts_req['rec'].'<div align="right" style="font-style: italic">'.($ajouts_req[0] ? date('d/m/Y', $ajouts_req[0]) : '').'</div></td></tr>';
	}

$record .= '</table>';

?>

<table width="100%">
<tr><td valign="top" style="width:10em;">
<?=$calendar[0]?>
<br/>
<?=$calendar[1]?>
<br />
<?=$record?>
</td>
<td valign="top">
<div style="text-align:left;">
<?=$nav_cat_art?>
</div>
</td></tr>
</table>
<?php

//on vas mettre les calculs des stats ici pour "aérer"

//analyse de la periode

$periode_n = 'Aujourd\'hui';
$date_n = mktime(0,0,0);

if($_GET['date0'] != $_GET['date1']) 
	{ //on a une periode
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
elseif(date('j', $_GET['date0']) == 1 && date('n', $_GET['date0']) == 1 && date('j', $_GET['date1']) == 31 && date('n', $_GET['date1']) == 12 && date('Y', $_GET['date0']) == date('Y', $_GET['date1'])) 
	{
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
else 
	{
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

//Je pourrais utiliser une table temporaire de date pour les jointure dans les graphs !!

$DB_site2->query("CREATE TEMPORARY TABLE datelines (
	dateline DATE NOT NULL,
	PRIMARY KEY(dateline)
	) ENGINE=MEMORY DEFAULT CHARSET=latin1;");

$ins_date = $date_0i;
$ins_query = "";
while($ins_date < $date_11 && $ins_date < $date_n) {
	$ins_query .= "(FROM_UNIXTIME('$ins_date')), ";
	$ins_date = mktime(0,0,0,date('n', $ins_date), date('j', $ins_date)+1, date('Y', $ins_date));
}

$ins_query .= "(FROM_UNIXTIME('".($date_11 > $date_n ? $date_n : $date_11)."'))";

$DB_site2->query("INSERT INTO datelines (dateline) VALUES$ins_query;");

//si on recuperais quelquechose ?

$freq_i_req = $DB_site2->query_first("SELECT SUM(visiteurs), SUM(visites), SUM(vues), SUM(visites1p) FROM stat_quotidien WHERE dateline BETWEEN FROM_UNIXTIME('".$date_0i."') AND FROM_UNIXTIME('".$date_1i."');");

$freq_7_req = $DB_site2->query_first("SELECT SUM(visiteurs), SUM(visites), SUM(vues), SUM(visites1p) FROM stat_quotidien WHERE dateline BETWEEN FROM_UNIXTIME('".$date_07."') AND FROM_UNIXTIME('".$date_17."');");

$freq_1_req = $DB_site2->query_first("SELECT SUM(visiteurs), SUM(visites), SUM(vues), SUM(visites1p) FROM stat_quotidien WHERE dateline BETWEEN FROM_UNIXTIME('".$date_01."') AND FROM_UNIXTIME('".$date_11."');");

$freq_n_req = $DB_site2->query_first("SELECT visiteurs, visites, vues, visites1p FROM stat_quotidien WHERE dateline = FROM_UNIXTIME('".$date_n."');");



//aprés le quotidien faison le GEOLOC !!
$geoloc_1_req = $DB_site2->query("SELECT SUM(visiteurs), SUM(visites), SUM(vues), SUM(visites1p), pays FROM stat_geoloc LEFT JOIN codetocountry USING(code) WHERE dateline BETWEEN FROM_UNIXTIME('".$date_01."') AND FROM_UNIXTIME('".$date_11."') GROUP BY code ORDER BY visiteurs DESC LIMIT 9;");

$geoloc_sum_req = $DB_site2->query_first("SELECT SUM(visiteurs), SUM(visites), SUM(vues), SUM(visites1p) FROM stat_geoloc WHERE dateline BETWEEN FROM_UNIXTIME('".$date_01."') AND FROM_UNIXTIME('".$date_11."');");


//un peu de suivi d'activité ?
if(!$catid && !$artid) {
	
	$ca_i_req = $DB_site2->query_first("SELECT SUM(tot) as rec FROM (SELECT facture.facturid, facture.datefacture, sum((lignefacture.prix*lignefacture.qte)
)
+facture.prixport
+facture.prixgestion
+IF(facture.moyenid = 3, facture.prix_contre_remboursement, 0)
-IF(facture.cadeauid > 0, facture.montantcadeau, 0)
-IF(facture.remiseid > 0, facture.montantremise, 0)
as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) GROUP BY lignefacture.factureid) as fs WHERE datefacture BETWEEN FROM_UNIXTIME('".$date_0i."') AND FROM_UNIXTIME('".$date_1i."') ORDER BY rec DESC;");
	
	$ca_7_req = $DB_site2->query_first("SELECT SUM(tot) as rec FROM (SELECT facture.facturid, facture.datefacture, sum((lignefacture.prix*lignefacture.qte)
)
+facture.prixport
+facture.prixgestion
+IF(facture.moyenid = 3, facture.prix_contre_remboursement, 0)
-IF(facture.cadeauid > 0, facture.montantcadeau, 0)
-IF(facture.remiseid > 0, facture.montantremise, 0)
as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) GROUP BY lignefacture.factureid) as fs WHERE datefacture BETWEEN FROM_UNIXTIME('".$date_07."') AND FROM_UNIXTIME('".$date_17."') ORDER BY rec DESC;");
	
	$ca_1_req = $DB_site2->query_first("SELECT SUM(tot) as rec FROM (SELECT facture.facturid, facture.datefacture, sum((lignefacture.prix*lignefacture.qte)
)
+facture.prixport
+facture.prixgestion
+IF(facture.moyenid = 3, facture.prix_contre_remboursement, 0)
-IF(facture.cadeauid > 0, facture.montantcadeau, 0)
-IF(facture.remiseid > 0, facture.montantremise, 0)
as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) GROUP BY lignefacture.factureid) as fs WHERE datefacture BETWEEN FROM_UNIXTIME('".$date_01."') AND FROM_UNIXTIME('".$date_11."') ORDER BY rec DESC;");
	
	$ca_n_req = $DB_site2->query_first("SELECT SUM(tot) as rec FROM (SELECT facture.facturid, facture.datefacture, sum((lignefacture.prix*lignefacture.qte)
)
+facture.prixport
+facture.prixgestion
+IF(facture.moyenid = 3, facture.prix_contre_remboursement, 0)
-IF(facture.cadeauid > 0, facture.montantcadeau, 0)
-IF(facture.remiseid > 0, facture.montantremise, 0)
as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) GROUP BY lignefacture.factureid) as fs WHERE datefacture = FROM_UNIXTIME('".$date_n."') ORDER BY rec DESC;");
	
	$suivi_i_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_article WHERE dateline BETWEEN FROM_UNIXTIME('".$date_0i."') AND FROM_UNIXTIME('".$date_1i."');");
	$suivi_7_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_article WHERE dateline BETWEEN FROM_UNIXTIME('".$date_07."') AND FROM_UNIXTIME('".$date_17."');");
	$suivi_1_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_article WHERE dateline BETWEEN FROM_UNIXTIME('".$date_01."') AND FROM_UNIXTIME('".$date_11."');");
	$suivi_n_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_article WHERE dateline = FROM_UNIXTIME('".$date_n."');");
}
elseif ($catid) {
		
	$ca_i_req = $DB_site2->query_first("SELECT SUM(tot) as rec FROM (SELECT facture.facturid, facture.datefacture, (lignefacture.prix*lignefacture.qte)

as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) AND lignefacture.artid IN (SELECT artid FROM article WHERE catid IN(".implode(',', $catid_deeplist)."))) as fs WHERE datefacture BETWEEN FROM_UNIXTIME('".$date_0i."') AND FROM_UNIXTIME('".$date_1i."') ORDER BY rec DESC;");
	
	$ca_7_req = $DB_site2->query_first("SELECT SUM(tot) as rec FROM (SELECT facture.facturid, facture.datefacture, (lignefacture.prix*lignefacture.qte)

as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) AND lignefacture.artid IN (SELECT artid FROM article WHERE catid IN(".implode(',', $catid_deeplist)."))) as fs WHERE datefacture BETWEEN FROM_UNIXTIME('".$date_07."') AND FROM_UNIXTIME('".$date_17."') ORDER BY rec DESC;");
	
	$ca_1_req = $DB_site2->query_first("SELECT SUM(tot) as rec FROM (SELECT facture.facturid, facture.datefacture, (lignefacture.prix*lignefacture.qte)

as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) AND lignefacture.artid IN (SELECT artid FROM article WHERE catid IN(".implode(',', $catid_deeplist)."))) as fs WHERE datefacture BETWEEN FROM_UNIXTIME('".$date_01."') AND FROM_UNIXTIME('".$date_11."') ORDER BY rec DESC;");
	
	$ca_n_req = $DB_site2->query_first("SELECT SUM(tot) as rec FROM (SELECT facture.facturid, facture.datefacture, (lignefacture.prix*lignefacture.qte)

as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) AND lignefacture.artid IN (SELECT artid FROM article WHERE catid IN(".implode(',', $catid_deeplist)."))) as fs WHERE datefacture = FROM_UNIXTIME('".$date_n."') ORDER BY rec DESC;");
	
	$suivi_i_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_article WHERE dateline BETWEEN FROM_UNIXTIME('".$date_0i."') AND FROM_UNIXTIME('".$date_1i."') AND artid IN(SELECT artid FROM article WHERE catid IN(".implode(',', $catid_deeplist)."));");
	$suivi_7_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_article WHERE dateline BETWEEN FROM_UNIXTIME('".$date_07."') AND FROM_UNIXTIME('".$date_17."') AND artid IN(SELECT artid FROM article WHERE catid IN(".implode(',', $catid_deeplist)."));");
	$suivi_1_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_article WHERE dateline BETWEEN FROM_UNIXTIME('".$date_01."') AND FROM_UNIXTIME('".$date_11."') AND artid IN(SELECT artid FROM article WHERE catid IN(".implode(',', $catid_deeplist)."));");
	$suivi_n_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_article WHERE dateline = FROM_UNIXTIME('".$date_n."') AND artid IN(SELECT artid FROM article WHERE catid IN(".implode(',', $catid_deeplist)."));");
	
}
elseif ($artid) {
		
	$ca_i_req = $DB_site2->query_first("SELECT SUM(tot) as rec FROM (SELECT facture.facturid, facture.datefacture, (lignefacture.prix*lignefacture.qte)

as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5)) as fs WHERE datefacture BETWEEN FROM_UNIXTIME('".$date_0i."') AND FROM_UNIXTIME('".$date_1i."') ORDER BY rec DESC;");
	
	$ca_7_req = $DB_site2->query_first("SELECT SUM(tot) as rec FROM (SELECT facture.facturid, facture.datefacture, (lignefacture.prix*lignefacture.qte)

as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) AND lignefacture.artid = '$artid') as fs WHERE datefacture BETWEEN FROM_UNIXTIME('".$date_07."') AND FROM_UNIXTIME('".$date_17."') ORDER BY rec DESC;");
	
	$ca_1_req = $DB_site2->query_first("SELECT SUM(tot) as rec FROM (SELECT facture.facturid, facture.datefacture, (lignefacture.prix*lignefacture.qte)

as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) AND lignefacture.artid = '$artid') as fs WHERE datefacture BETWEEN FROM_UNIXTIME('".$date_01."') AND FROM_UNIXTIME('".$date_11."') ORDER BY rec DESC;");
	
	$ca_n_req = $DB_site2->query_first("SELECT SUM(tot) as rec FROM (SELECT facture.facturid, facture.datefacture, (lignefacture.prix*lignefacture.qte)

as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) AND lignefacture.artid = '$artid') as fs WHERE datefacture = FROM_UNIXTIME('".$date_n."') ORDER BY rec DESC;");
	
	$suivi_i_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_article WHERE dateline BETWEEN FROM_UNIXTIME('".$date_0i."') AND FROM_UNIXTIME('".$date_1i."') AND artid = '$artid';");
	$suivi_7_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_article WHERE dateline BETWEEN FROM_UNIXTIME('".$date_07."') AND FROM_UNIXTIME('".$date_17."') AND artid = '$artid';");
	$suivi_1_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_article WHERE dateline BETWEEN FROM_UNIXTIME('".$date_01."') AND FROM_UNIXTIME('".$date_11."') AND artid = '$artid';");
	$suivi_n_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_article WHERE dateline = FROM_UNIXTIME('".$date_n."') AND artid = '$artid';");
}


//suivi d'activité détaillé
if((!$catid && !$artid) || $catid) {
	//verifier si on a plusieurs boutique et les comparer, sinon prendre la premiere boutique
	if(!$catid) {
		$detailid_req = $DB_site2->query("SELECT catid FROM categorie WHERE parentid = '0' AND catid NOT IN(".implode(',', $notin_array).");");
		
		$detailid_num = $DB_site2->num_rows($detailid_req);
		
		if($detailid_num > 1 || $detailid_num == 0) {
			$detailcatid = 0;
		}
		else {
			$detailcatid = $DB_site2->fetch_array($detailid_req);
			$detailcatid = $detailcatid[0];
		}
	}
	else {
		$detailcatid = $catid;
	}
	
	//verifier si la categorie ne contient pas QUE des articles !
	
	$child_cat_req = $DB_site2->query("SELECT catid, $libelle FROM categorie WHERE parentid = '".$detailcatid."' AND catid NOT IN(".implode(',', $notin_array).");");
	
	if($DB_site2->num_rows($child_cat_req)) {
		
		$union_query = "SELECT '$detailcatid' as rootid, '".addslashes($trilibelle)."' as rootlib, facture.facturid, facture.datefacture, (lignefacture.prix*lignefacture.qte)
	
	as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) INNER JOIN article ON(lignefacture.artid = article.artid) WHERE facture.valide IN(1,5) AND article.catid = '$detailcatid'";
		
		while($child_cat = $DB_site2->fetch_array($child_cat_req)) {
			//il nous faut toutes les cat en profondeur...
			$child_cat_deep = array($child_cat[0]);
			$childlist = array($child_cat[0]);
			
			do {
				
				$child_deep_req = $DB_site2->query("SELECT catid FROM categorie WHERE parentid IN(".implode(',', $childlist).");");
				
				$childlist = array();
				
				while($child_deep = $DB_site2->fetch_array($child_deep_req)) {
					$childlist[] = $child_deep[0];
					$child_cat_deep[] = $child_deep[0];
				}
				
			}
			while(sizeof($childlist));
			
			$union_query .= "\nUNION ALL SELECT '".$child_cat[0]."' as rootid, '".addslashes($child_cat[1])."' as rootlib, facture.facturid, facture.datefacture, (lignefacture.prix*lignefacture.qte)
	
	as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) INNER JOIN article ON(lignefacture.artid = article.artid) WHERE facture.valide IN(1,5) AND article.catid IN (".implode(',', $child_cat_deep).")";
			
		}
	
	}
	else {
		//on a que des article, on compare sur les articles
		$union_query = "SELECT article.artid as rootid, article.$libelle as rootlib, facture.facturid, facture.datefacture, (lignefacture.prix*lignefacture.qte)
	
	as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) INNER JOIN article ON(lignefacture.artid = article.artid) WHERE facture.valide IN(1,5) AND article.catid = '$detailcatid'";
		
	}
	//faire la requete
	
	//date i
	$detail_i_req = $DB_site2->query("SELECT rootid, SUM(tot), rootlib FROM ($union_query) as fs WHERE datefacture BETWEEN FROM_UNIXTIME('$date_0i') AND FROM_UNIXTIME('$date_1i') GROUP BY rootid ORDER BY 2 DESC;");
	//date 7
	$detail_7_req = $DB_site2->query("SELECT rootid, SUM(tot), rootlib FROM ($union_query) as fs WHERE datefacture BETWEEN FROM_UNIXTIME('$date_07') AND FROM_UNIXTIME('$date_17') GROUP BY rootid ORDER BY 2 DESC;");
	//date 1
	$detail_1_req = $DB_site2->query("SELECT rootid, SUM(tot), rootlib FROM ($union_query) as fs WHERE datefacture BETWEEN FROM_UNIXTIME('$date_01') AND FROM_UNIXTIME('$date_11') GROUP BY rootid ORDER BY 2 DESC;");
	//date n
	$detail_n_req = $DB_site2->query("SELECT rootid, SUM(tot), rootlib FROM ($union_query) as fs WHERE datefacture = FROM_UNIXTIME('$date_n') GROUP BY rootid ORDER BY 2 DESC;");
	
	
	$detail_result_array = array();
	
	while($detail_1 = $DB_site2->fetch_array($detail_1_req)) {
		$detail_result_array[$detail_1[0]]['date1'] = $detail_1[1];
		$detail_result_array[$detail_1[0]]['lib'] = $detail_1[2];
	}
	while($detail_n = $DB_site2->fetch_array($detail_n_req)) {
		$detail_result_array[$detail_n[0]]['daten'] = $detail_n[1];
		$detail_result_array[$detail_n[0]]['lib'] = $detail_n[2];
	}
	while($detail_7 = $DB_site2->fetch_array($detail_7_req)) {
		$detail_result_array[$detail_7[0]]['date7'] = $detail_7[1];
		$detail_result_array[$detail_7[0]]['lib'] = $detail_7[2];
	}
	while($detail_i = $DB_site2->fetch_array($detail_i_req)) {
		$detail_result_array[$detail_i[0]]['datei'] = $detail_i[1];
		$detail_result_array[$detail_i[0]]['lib'] = $detail_i[2];
	}
	
}
//pas de suivi detaillé par article, aucun interet...








//Statistiques Utilisateurs
$user_i_req = $DB_site2->query_first("SELECT SUM(visites), SUM(vues), COUNT(DISTINCT userid) FROM stat_user WHERE dateline BETWEEN FROM_UNIXTIME('".$date_0i."') AND FROM_UNIXTIME('".$date_1i."');");
if(!$artid && !$catid) {
	$user_ar_i_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_user_article WHERE dateline BETWEEN FROM_UNIXTIME('".$date_0i."') AND FROM_UNIXTIME('".$date_1i."');");
}
elseif ($catid) {
	$user_ar_i_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_user_article WHERE dateline BETWEEN FROM_UNIXTIME('".$date_0i."') AND FROM_UNIXTIME('".$date_1i."') AND artid IN(SELECT artid FROM article WHERE catid IN(".implode(',', $catid_deeplist)."));");
}
elseif ($artid) {
	$user_ar_i_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_user_article WHERE dateline BETWEEN FROM_UNIXTIME('".$date_0i."') AND FROM_UNIXTIME('".$date_1i."') AND artid = '$artid';");
	
}

$user_7_req = $DB_site2->query_first("SELECT SUM(visites), SUM(vues), COUNT(DISTINCT userid) FROM stat_user WHERE dateline BETWEEN FROM_UNIXTIME('".$date_07."') AND FROM_UNIXTIME('".$date_17."');");
if(!$artid && !$catid) {
	$user_ar_7_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_user_article WHERE dateline BETWEEN FROM_UNIXTIME('".$date_07."') AND FROM_UNIXTIME('".$date_17."');");
}
elseif ($catid) {
	$user_ar_7_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_user_article WHERE dateline BETWEEN FROM_UNIXTIME('".$date_07."') AND FROM_UNIXTIME('".$date_17."') AND artid IN(SELECT artid FROM article WHERE catid IN(".implode(',', $catid_deeplist)."));");
}
elseif ($artid) {
	$user_ar_7_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_user_article WHERE dateline BETWEEN FROM_UNIXTIME('".$date_07."') AND FROM_UNIXTIME('".$date_17."') AND artid = '$artid';");
}

$user_1_req = $DB_site2->query_first("SELECT SUM(visites), SUM(vues), COUNT(DISTINCT userid) FROM stat_user WHERE dateline BETWEEN FROM_UNIXTIME('".$date_01."') AND FROM_UNIXTIME('".$date_11."');");
if(!$artid && !$catid) {
	$user_ar_1_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_user_article WHERE dateline BETWEEN FROM_UNIXTIME('".$date_01."') AND FROM_UNIXTIME('".$date_11."');");
}
elseif ($catid) {
	$user_ar_1_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_user_article WHERE dateline BETWEEN FROM_UNIXTIME('".$date_01."') AND FROM_UNIXTIME('".$date_11."') AND artid IN(SELECT artid FROM article WHERE catid IN(".implode(',', $catid_deeplist)."));");
}
elseif ($artid) {
	$user_ar_1_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_user_article WHERE dateline BETWEEN FROM_UNIXTIME('".$date_01."') AND FROM_UNIXTIME('".$date_11."') AND artid = '$artid';");
}

$user_n_req = $DB_site2->query_first("SELECT SUM(visites), SUM(vues), COUNT(DISTINCT userid) FROM stat_user WHERE dateline = FROM_UNIXTIME('".$date_n."');");
if(!$artid && !$catid) {
	$user_ar_n_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_user_article WHERE dateline = FROM_UNIXTIME('".$date_n."');");
}
elseif ($catid) {
	$user_ar_n_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_user_article WHERE dateline = FROM_UNIXTIME('".$date_n."') AND artid IN(SELECT artid FROM article WHERE catid IN(".implode(',', $catid_deeplist)."));");
}
elseif ($artid) {
	$user_ar_n_req = $DB_site2->query_first("SELECT SUM(ajouts), SUM(vues) FROM stat_user_article WHERE dateline = FROM_UNIXTIME('".$date_n."') AND artid = '$artid';");
}

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
<br /><br />
<table class="stats">
<tr>
<th><div align="left">Fréquentation</div></th>
<th><?=$periode_7?></th>
<th><?=$periode_1?></th>
<th><?=$periode_n?></th>
</tr>

<tr>
<td>Visiteurs</td>
<?php $ratio_7_0 = $freq_i_req[0] ? $freq_7_req[0]/$freq_i_req[0]*100-100 : '0'; ?>
<?php $ratio_1_0 = $freq_7_req[0] ? $freq_1_req[0]/$freq_7_req[0]*100-100 : '0'; ?>
<td align="right"><?=$freq_7_req[0] ? $freq_7_req[0] : 0?> <span title="Progression par rapport à la periode du <?=$periode_it?>"><?=print_progress($ratio_7_0)?></span></td>
<td align="right"><?=$freq_1_req[0] ? $freq_1_req[0] : 0?> <span title="Progression par rapport à la periode du <?=$periode_7t?>"><?=print_progress($ratio_1_0)?></span></td>
<td align="right"><?=$freq_n_req[0] ? $freq_n_req[0] : 0?></td>
</tr>

<tr>
<?php $ratio_7_1 = $freq_i_req[1] ? $freq_7_req[1]/$freq_i_req[1]*100-100 : '0'; ?>
<?php $ratio_1_1 = $freq_7_req[1] ? $freq_1_req[1]/$freq_7_req[1]*100-100 : '0'; ?>
<td>Visites</td>
<td align="right"><?=$freq_7_req[1] ? $freq_7_req[1] : 0?> <span title="Progression par rapport à la periode du <?=$periode_it?>"><?=print_progress($ratio_7_1)?></span></td>
<td align="right"><?=$freq_1_req[1] ? $freq_1_req[1] : 0?> <span title="Progression par rapport à la periode du <?=$periode_7t?>"><?=print_progress($ratio_1_1)?></span></td>
<td align="right"><?=$freq_n_req[1] ? $freq_n_req[1] : 0?></td>
</tr>

<tr>
<?php $ratio_7_2 = $freq_i_req[2] ? $freq_7_req[2]/$freq_i_req[2]*100-100 : '0'; ?>
<?php $ratio_1_2 = $freq_7_req[2] ? $freq_1_req[2]/$freq_7_req[2]*100-100 : '0'; ?>
<td>Pages Vues</td>
<td align="right"><?=$freq_7_req[2] ? $freq_7_req[2] : 0?> <span title="Progression par rapport à la periode du <?=$periode_it?>"><?=print_progress($ratio_7_2)?></span></td>
<td align="right"><?=$freq_1_req[2] ? $freq_1_req[2] : 0?> <span title="Progression par rapport à la periode du <?=$periode_7t?>"><?=print_progress($ratio_1_2)?></span></td>
<td align="right"><?=$freq_n_req[2] ? $freq_n_req[2] : 0?></td>
</tr>

<tr>
<?php $ratio_7_3 = $freq_i_req[3] ? $freq_7_req[3]/$freq_i_req[3]*100-100 : '0'; ?>
<?php $ratio_1_3 = $freq_7_req[3] ? $freq_1_req[3]/$freq_7_req[3]*100-100 : '0'; ?>
<td>Visites à une page</td>
<td align="right"><?=$freq_7_req[3] ? $freq_7_req[3] : 0?> <span title="Progression par rapport à la periode du <?=$periode_it?>"><?=print_progress($ratio_7_3)?></span></td>
<td align="right"><?=$freq_1_req[3] ? $freq_1_req[3] : 0?> <span title="Progression par rapport à la periode du <?=$periode_7t?>"><?=print_progress($ratio_1_3)?></span></td>
<td align="right"><?=$freq_n_req[3] ? $freq_n_req[3] : 0?></td>
</tr>

<tr>
<?php $ratio_ip = $freq_i_req[1] ? $freq_i_req[3]/$freq_i_req[1]*100 : '0'; ?>
<?php $ratio_7p = $freq_7_req[1] ? $freq_7_req[3]/$freq_7_req[1]*100 : '0'; ?>
<?php $ratio_1p = $freq_1_req[1] ? $freq_1_req[3]/$freq_1_req[1]*100 : '0'; ?>
<?php $ratio_7_4 = $ratio_ip ? $ratio_ip - $ratio_7p : '0'; ?>
<?php $ratio_1_4 = $ratio_7p ? $ratio_7p - $ratio_1p : '0'; ?>
<td>Ratio visites 1 page</td>
<td align="right"><?=($freq_7_req[1] ? number_format($freq_7_req[3]/$freq_7_req[1]*100, '1', ',', '') : '0,0')?> % <span title="Progression par rapport à la periode du <?=$periode_it?>"><?=print_progress($ratio_7_4, 'pt.')?></span></td>
<td align="right"><?=($freq_1_req[1] ? number_format($freq_1_req[3]/$freq_1_req[1]*100, '1', ',', '') : '0,0')?> % <span title="Progression par rapport à la periode du <?=$periode_7t?>"><?=print_progress($ratio_1_4, 'pt.')?></span></td>
<td align="right"><?=($freq_n_req[1] ? number_format($freq_n_req[3]/$freq_n_req[1]*100, '1', ',', '') : '0,0')?> %</td>
</tr>
</table>

<br />
<?php
//preparer de quoi generer le graph !!

$graph_freq_req = $DB_site2->query("SELECT visiteurs, visites, vues, visites1p, UNIX_TIMESTAMP(dateline) FROM datelines LEFT JOIN stat_quotidien USING(dateline) WHERE dateline BETWEEN FROM_UNIXTIME('".$date_0i."') AND FROM_UNIXTIME('".$date_11."') ORDER BY dateline DESC;");

$graph_freq_legend = base64_encode('Visiteurs;Visites;Pages Vues (x10);Visites 1 Pages;Pages Vues/Visites');
$graph_freq_values = array();

$graph_freq_vsteur = array();
$graph_freq_vstes = array();
$graph_freq_vues = array();
$graph_freq_1p = array();
$graph_freq_ratio = array();

if($DB_site2->num_rows($graph_freq_req) > 7) {
	//il faut reduire
	$graph_nb_7 = floor(($date_11 - $date_07)/(3600*24));
	$graph_nb_1 = floor(($date_11 - $date_01)/(3600*24));
	
	if($graph_nb_7 < 8 || $graph_nb_1 < 8) {
		//on a 7- jours entre les date
		//on prend tant qu'on peu :)
		$i = 0;
		while($graph_freq = $DB_site2->fetch_array($graph_freq_req)) {
			$i++;
			$graph_freq_vsteur[] = $graph_freq[0] == null ? 0 : $graph_freq[0];
			$graph_freq_vstes[] = $graph_freq[1] == null ? 0 : $graph_freq[1];
			$graph_freq_vues[] = round($graph_freq[2]/10);
			$graph_freq_1p[] = $graph_freq[3] == null ? 0 : $graph_freq[3];
			$graph_freq_ratio[] = $graph_freq[1] ? $graph_freq[2]/$graph_freq[1] : 0;
			$graph_freq_values[] = date('d/m/y', $graph_freq[4]);
			
			if($i > 6) {
				break;
			}
		}
		
	}
	else {
		//on a pas le choix il faut emputer
		$nb_enr = $graph_nb_1;
		$grp_num = ceil($nb_enr/7); //obligatoirement >= 2
		$nb_loop = ceil($nb_enr/$grp_num); // obligatoirement >= 4
		
		for($i = 0; $i < $nb_loop ; $i++) {
			$graph_freq = $DB_site2->query_first("SELECT SUM(visiteurs), SUM(visites), SUM(vues), SUM(visites1p) FROM datelines LEFT JOIN stat_quotidien USING(dateline) WHERE dateline BETWEEN FROM_UNIXTIME('".($date_11-(($i*$grp_num)+($grp_num-1))*3600*24)."') AND FROM_UNIXTIME('".($date_11-(($i*$grp_num)*3600*24))."');");
			
				$graph_freq_vsteur[] = $graph_freq[0] == null ? 0 : $graph_freq[0];
				$graph_freq_vstes[] = $graph_freq[1] == null ? 0 : $graph_freq[1];
				$graph_freq_vues[] = round($graph_freq[2]/10);
				$graph_freq_1p[] = $graph_freq[3] == null ? 0 : $graph_freq[3];
				$graph_freq_ratio[] = $graph_freq[1] ? $graph_freq[2]/$graph_freq[1] : 0;
				$graph_freq_values[] = date('d/m/y', ($date_11-(($i*$grp_num)+($grp_num-1))*3600*24));
					
		}
	}
	
}
else {
	
	while($graph_freq = $DB_site2->fetch_array($graph_freq_req)) {
		$graph_freq_vsteur[] = $graph_freq[0] == null ? 0 : $graph_freq[0];
		$graph_freq_vstes[] = $graph_freq[1] == null ? 0 : $graph_freq[1];
		$graph_freq_vues[] = round($graph_freq[2]/10);
		$graph_freq_1p[] = $graph_freq[3] == null ? 0 : $graph_freq[3];
		$graph_freq_ratio[] = $graph_freq[1] ? $graph_freq[2]/$graph_freq[1] : 0;
		$graph_freq_values[] = date('d/m/y', $graph_freq[4]);
	}
}
?>
<img src="statsgraphs.php?type=line&legends=<?=$graph_freq_legend?>&bar1=<?=base64_encode(implode(";", $graph_freq_vsteur))?>&bar2=<?=base64_encode(implode(";", $graph_freq_vstes))?>&bar3=<?=base64_encode(implode(";", $graph_freq_vues))?>&bar4=<?=base64_encode(implode(";", $graph_freq_1p))?>&line1=<?=base64_encode(implode(";", $graph_freq_ratio))?>&values=<?=base64_encode(implode(";", $graph_freq_values))?>&title=<?=base64_encode('Fréquentation')?>" width="550" height="270" border="0" align="middle">

<br /><br />
<table class="stats">
<tr>
<th><div align="left">Suivi Activité</div><div style="max-width: 20em;font-style:italic;"><?=($artid || $catid ? $trilibelle : 'Boutique')?></div></th>
<th><?=$periode_7?></th>
<th><?=$periode_1?></th>
<th><?=$periode_n?></th>
</tr>

<tr>
<?php $ca_ratio_7 = $ca_i_req[0] ? $ca_7_req[0]/$ca_i_req[0]*100-100 : 0; ?>
<?php $ca_ratio_1 = $ca_7_req[0] ? $ca_1_req[0]/$ca_7_req[0]*100-100 : 0; ?>
<td>C.A.</td>
<td><div align="right"><?=number_format($ca_7_req[0], '2', ',', ' ')?> &euro; <span title="Progression par rapport à la periode du <?=$periode_it?>"><?=print_progress($ca_ratio_7, '%')?></span></div></td>
<td><div align="right"><?=number_format($ca_1_req[0], '2', ',', ' ')?> &euro; <span title="Progression par rapport à la periode du <?=$periode_7t?>"><?=print_progress($ca_ratio_1, '%')?></span></div></td>
<td><div align="right"><?=number_format($ca_n_req[0], '2', ',', ' ')?> &euro;</div></td>
</tr>

<tr>
<?php $suivi_ratio_7_1 = $suivi_i_req[1] ? $suivi_7_req[1]/$suivi_i_req[1]*100-100 : 0; ?>
<?php $suivi_ratio_1_1 = $suivi_7_req[1] ? $suivi_1_req[1]/$suivi_7_req[1]*100-100 : 0; ?>
<td>Vues articles</td>
<td><div align="right"><?=$suivi_7_req[1] ? $suivi_7_req[1] : 0?> <span title="Progression par rapport à la periode du <?=$periode_it?>"><?=print_progress($suivi_ratio_7_1, '%')?></span></div></td>
<td><div align="right"><?=$suivi_1_req[1] ? $suivi_1_req[1] : 0?> <span title="Progression par rapport à la periode du <?=$periode_7t?>"><?=print_progress($suivi_ratio_1_1, '%')?></span></div></td>
<td><div align="right"><?=$suivi_n_req[1] ? $suivi_n_req[1] : 0?></div></td>
</tr>

<tr>
<?php $suivi_ratio_7_0 = $suivi_i_req[0] ? $suivi_7_req[0]/$suivi_i_req[0]*100-100 : 0; ?>
<?php $suivi_ratio_1_0 = $suivi_7_req[0] ? $suivi_1_req[0]/$suivi_7_req[0]*100-100 : 0; ?>
<td>Ajouts panier</td>
<td><div align="right"><?=$suivi_7_req[0] ? $suivi_7_req[0] : 0?> <span title="Progression par rapport à la periode du <?=$periode_it?>"><?=print_progress($suivi_ratio_7_0, '%')?></span></div></td>
<td><div align="right"><?=$suivi_1_req[0] ? $suivi_1_req[0] : 0?> <span title="Progression par rapport à la periode du <?=$periode_7t?>"><?=print_progress($suivi_ratio_1_0, '%')?></span></div></td>
<td><div align="right"><?=$suivi_n_req[0] ? $suivi_n_req[0] : 0?></div></td>
</tr>
</table>

<br />

<?php
//preparer de quoi generer le graph !!
if(!$catid && !$artid) {
	$graph_act_req = $DB_site2->query("SELECT UNIX_TIMESTAMP(dateline) as dateline, tot, tv FROM datelines LEFT JOIN (SELECT dateline, SUM(tot) as tot FROM (SELECT facture.facturid, facture.datefacture as dateline, sum((lignefacture.prix*lignefacture.qte)
)
+facture.prixport
+facture.prixgestion
+IF(facture.moyenid = 3, facture.prix_contre_remboursement, 0)
-IF(facture.cadeauid > 0, facture.montantcadeau, 0)
-IF(facture.remiseid > 0, facture.montantremise, 0)
as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) GROUP BY facture.facturid) as ps GROUP BY dateline) as fs USING(dateline) LEFT JOIN (SELECT SUM(vues) as tv, dateline FROM stat_article LEFT JOIN article USING(artid) GROUP BY dateline) as vs USING(dateline) WHERE dateline BETWEEN FROM_UNIXTIME('".$date_0i."') AND FROM_UNIXTIME('".$date_11."') ORDER BY dateline DESC;");

}
elseif ($catid) {
	$graph_act_req = $DB_site2->query("SELECT UNIX_TIMESTAMP(dateline) as dateline, tot, tv FROM datelines LEFT JOIN (SELECT facture.facturid, facture.datefacture as dateline, SUM(lignefacture.prix*lignefacture.qte)

as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) LEFT JOIN article USING(artid) WHERE facture.valide IN(1,5) AND article.catid IN(".implode(',',$catid_deeplist).") GROUP BY dateline) as fs USING(dateline) LEFT JOIN (SELECT SUM(vues) as tv, dateline FROM stat_article LEFT JOIN article USING(artid) WHERE article.catid IN(".implode(',',$catid_deeplist).") GROUP BY dateline) as vs USING(dateline) WHERE dateline BETWEEN FROM_UNIXTIME('".$date_0i."') AND FROM_UNIXTIME('".$date_11."') ORDER BY dateline DESC;");
}
elseif ($artid) {
	$graph_act_req = $DB_site2->query("SELECT UNIX_TIMESTAMP(dateline) as dateline, tot, tv FROM datelines LEFT JOIN (SELECT facture.facturid, facture.datefacture as dateline, SUM(lignefacture.prix*lignefacture.qte)

as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) AND lignefacture.artid = '$artid' GROUP BY dateline) as fs USING(dateline) LEFT JOIN (SELECT SUM(vues) as tv, dateline FROM stat_article LEFT JOIN article USING(artid) WHERE article.artid = '$artid' GROUP BY dateline) as vs USING(dateline) WHERE dateline BETWEEN FROM_UNIXTIME('".$date_0i."') AND FROM_UNIXTIME('".$date_11."') ORDER BY dateline DESC;");
}

$graph_act_legend = base64_encode('Vues Articles;Chiffre Affaire');
$graph_act_values = array();

$graph_act_vues = array();
$graph_act_tot = array();

if($DB_site2->num_rows($graph_act_req) > 10) {
	//il faut reduire
	$graph_nb_7 = floor(($date_11 - $date_07)/(3600*24));
	$graph_nb_1 = floor(($date_11 - $date_01)/(3600*24));

	
	if($graph_nb_7 < 11 || $graph_nb_1 < 11) {
		//on a 7- jours entre les date
		//on prend tant qu'on peu :)
		$i = 0;
		while($graph_act = $DB_site2->fetch_array($graph_act_req)) {
			$i++;
			$graph_act_vues[] = $graph_act['tv'] == null ? 0 : $graph_act['tv'];
			$graph_act_tot[] = $graph_act['tot'] == null ? 0 : $graph_act['tot'];
			$graph_act_values[] =  date('d/m/y', $graph_act['dateline']);
			
			if($i > 9) {
				break;
			}
		}
		
	}
	else {
		//on a pas le choix il faut emputer
		$nb_enr = $graph_nb_1;
		$grp_num = ceil($nb_enr/10); //obligatoirement >= 2
		$nb_loop = ceil($nb_enr/$grp_num); // obligatoirement >= 4
		
		for($i = 0; $i < $nb_loop ; $i++) {
			if(!$catid && !$artid) {
				$graph_act_req = $DB_site2->query_first("SELECT SUM(tot) as tot, SUM(tv) as tv FROM datelines LEFT JOIN (SELECT dateline, SUM(tot) as tot FROM (SELECT facture.facturid, facture.datefacture as dateline, sum((lignefacture.prix*lignefacture.qte)
			)
			+facture.prixport
			+facture.prixgestion
			+IF(facture.moyenid = 3, facture.prix_contre_remboursement, 0)
			-IF(facture.cadeauid > 0, facture.montantcadeau, 0)
			-IF(facture.remiseid > 0, facture.montantremise, 0)
			as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) GROUP BY facture.facturid) as ps GROUP BY dateline) as fs USING(dateline) LEFT JOIN (SELECT SUM(vues) as tv, dateline FROM stat_article LEFT JOIN article USING(artid) GROUP BY dateline) as vs USING(dateline) WHERE dateline BETWEEN FROM_UNIXTIME('".($date_11-(($i*$grp_num)+($grp_num-1))*3600*24)."') AND FROM_UNIXTIME('".($date_11-(($i*$grp_num)*3600*24))."');");
			}
			elseif ($catid) {
				$graph_act_req = $DB_site2->query_first("SELECT SUM(tot) as tot, SUM(tv) as tv FROM datelines LEFT JOIN (SELECT facture.facturid, facture.datefacture as dateline, SUM(lignefacture.prix*lignefacture.qte)
			
			as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) LEFT JOIN article USING(artid) WHERE facture.valide IN(1,5) AND article.catid IN(".implode(',',$catid_deeplist).") GROUP BY dateline) as fs USING(dateline) LEFT JOIN (SELECT SUM(vues) as tv, dateline FROM stat_article LEFT JOIN article USING(artid) WHERE article.catid IN(".implode(',',$catid_deeplist).") GROUP BY dateline) as vs USING(dateline) WHERE dateline BETWEEN FROM_UNIXTIME('".($date_11-(($i*$grp_num)+($grp_num-1))*3600*24)."') AND FROM_UNIXTIME('".($date_11-(($i*$grp_num)*3600*24))."');");
			}
			elseif ($artid) {
				$graph_act_req = $DB_site2->query_first("SELECT SUM(tot) as tot, SUM(tv) as tv FROM datelines LEFT JOIN (SELECT facture.facturid, facture.datefacture as dateline, SUM(lignefacture.prix*lignefacture.qte)
			
			as tot FROM facture INNER JOIN lignefacture ON(facture.facturid = lignefacture.factureid) WHERE facture.valide IN(1,5) AND lignefacture.artid = '$artid' GROUP BY dateline) as fs USING(dateline) LEFT JOIN (SELECT SUM(vues) as tv, dateline FROM stat_article WHERE artid = '$artid' GROUP BY dateline) as vs USING(dateline) WHERE dateline BETWEEN FROM_UNIXTIME('".($date_11-(($i*$grp_num)+($grp_num-1))*3600*24)."') AND FROM_UNIXTIME('".($date_11-(($i*$grp_num)*3600*24))."');");
			}
			
			$graph_act_vues[] = $graph_act_req['tv'] == null ? 0 : $graph_act_req['tv'];
			$graph_act_tot[] = $graph_act_req['tot'] == null ? 0 : $graph_act_req['tot'];
			$graph_act_values[] =  date('d/m/y', ($date_11-(($i*$grp_num)+($grp_num-1))*3600*24));
		}
	}
	
}
else {
	
	while($graph_act = $DB_site2->fetch_array($graph_act_req)) {
		$graph_act_vues[] = $graph_act['tv'] == null ? 0 : $graph_act['tv'];
		$graph_act_tot[] = $graph_act['tot'] == null ? 0 : $graph_act['tot'];
		$graph_act_values[] =  date('d/m/y', $graph_act['dateline']);
	}
}
?>

<img src="statsgraphs.php?type=line&legends=<?=$graph_act_legend?>&bar1=<?=base64_encode(implode(";", $graph_act_vues))?>&line1=<?=base64_encode(implode(";", $graph_act_tot))?>&values=<?=base64_encode(implode(";", $graph_act_values))?>&title=<?=base64_encode('Suivi Activité - '.($artid || $catid ? $trilibelle : 'Boutique'))?>" width="550" height="270" border="0" align="middle">

<?php if(!$artid) { ?>

<br /><br />
<table class="stats">
<tr>
<th><div align="left">Suivi Activité Détaillé</div><div style="max-width: 20em;font-style:italic;"><?=($catid ? $trilibelle : 'Boutique')?></div></th>
<th><?=$periode_7?></th>
<th><?=$periode_1?></th>
<th><?=$periode_n?></th>
</tr>
<?php $total_7 = 0; $total_1 = 0; $total_n = 0; ?>
<?php $pie_val_array = array(); ?>
<?php $pie_lib_array = array(); ?>
<?php foreach ($detail_result_array as $dk => $dv) { ?>
<?php $total_7 += $dv['date7']; $total_1 += $dv['date1']; $total_n += $dv['daten']; ?>
<?php if($dv['date1'] && sizeof($pie_val_array) < 10) { $pie_val_array[] = $dv['date1']; $pie_lib_array[] = $dv['lib']; } ?>
<?php $dt_ratio_7 = $dv['datei'] ? $dv['date7']/$dv['datei']*100-100 : 0; ?>
<?php $dt_ratio_1 = $dv['date7'] ? $dv['date1']/$dv['date7']*100-100 : 0; ?>

<tr>
<td><?=$dv['lib']?></td>
<td><div align="right"><?=number_format($dv['date7'], 2, ',', ' ')?> &euro; <span title="Progression par rapport à la periode du <?=$periode_it?>"><?=print_progress($dt_ratio_7, '%')?></span></div></td>
<td><div align="right"><?=number_format($dv['date1'], 2, ',', ' ')?> &euro; <span title="Progression par rapport à la periode du <?=$periode_7t?>"><?=print_progress($dt_ratio_1, '%')?></span></div></td>
<td><div align="right"><?=number_format($dv['daten'], 2, ',', ' ')?> &euro;</div></td>
</tr>

<?php } ?>

<tr>
<th><div align="left">Total</div></th>
<th><div align="right"><?=number_format($total_7, '2', ',', ' ')?> &euro;</div></th>
<th><div align="right"><?=number_format($total_1, '2', ',', ' ')?> &euro;</div></th>
<th><div align="right"><?=number_format($total_n, '2', ',', ' ')?> &euro;</div></th>
</tr>
</table>
<?php if(!$artid) { ?>
<div align="right" style="margin-right:2em;"><a href="statsarticle.php?date0=<?=$_GET['date0']?>&date1=<?=$_GET['date1']?>&catid=<?=$_GET['catid']?>" target="_blank" title="Affichage Détaillé sur la période et le rayon séléctionnés">+ de details sur les Articles</a></div>
<?php } ?>

<?php if(sizeof($pie_val_array)) { ?>

<br />
<img src="statsgraphs.php?type=pie&val=<?=base64_encode(implode(";", $pie_val_array))?>&lib=<?=base64_encode(implode(";", $pie_lib_array))?>&tot=<?=base64_encode($total_1)?>&title=<?=base64_encode('Suivi Activité Détaillé - '.($catid ? $trilibelle : 'Boutique'))?>" width="540" height="220" border="0" align="middle">

<?php } } ?>

<br /><br />

<table class="stats">
<tr>
<th><div align="left">Fréquentation Client</div></th>
<th><?=$periode_7?></th>
<th><?=$periode_1?></th>
<th><?=$periode_n?></th>
</tr>

<tr>
<?php $user_ratio_7_2 = $user_i_req[2] ? $user_7_req[2]/$user_i_req[2]*100-100 : 0; ?>
<?php $user_ratio_1_2 = $user_7_req[2] ? $user_1_req[2]/$user_7_req[2]*100-100 : 0; ?>
<td>Clients</td>
<td><div align="right"><?=$user_7_req[2] ? $user_7_req[2] : 0?> <span title="Progression par rapport à la periode du <?=$periode_it?>"><?=print_progress($user_ratio_7_2, '%')?></span></div></td>
<td><div align="right"><?=$user_1_req[2] ? $user_1_req[2] : 0?> <span title="Progression par rapport à la periode du <?=$periode_7t?>"><?=print_progress($user_ratio_1_2, '%')?></span></div></td>
<td><div align="right"><?=$user_n_req[2] ? $user_n_req[2] : 0?></div></td>
</tr>

<tr>
<?php $user_ratio_7_0 = $user_i_req[0] ? $user_7_req[0]/$user_i_req[0]*100-100 : 0; ?>
<?php $user_ratio_1_0 = $user_7_req[0] ? $user_1_req[0]/$user_7_req[0]*100-100 : 0; ?>
<td>Visites</td>
<td><div align="right"><?=$user_7_req[0] ? $user_7_req[0] : 0?> <span title="Progression par rapport à la periode du <?=$periode_it?>"><?=print_progress($user_ratio_7_0, '%')?></span></div></td>
<td><div align="right"><?=$user_1_req[0] ? $user_1_req[0] : 0?> <span title="Progression par rapport à la periode du <?=$periode_7t?>"><?=print_progress($user_ratio_1_0, '%')?></span></div></td>
<td><div align="right"><?=$user_n_req[0] ? $user_n_req[0] : 0?></div></td>
</tr>

<tr>
<?php $user_ratio_7_1 = $user_i_req[1] ? $user_7_req[1]/$user_i_req[1]*100-100 : 0; ?>
<?php $user_ratio_1_1 = $user_7_req[1] ? $user_1_req[1]/$user_7_req[1]*100-100 : 0; ?>
<td>Pages Vues</td>
<td><div align="right"><?=$user_7_req[1] ? $user_7_req[1] : 0?> <span title="Progression par rapport à la periode du <?=$periode_it?>"><?=print_progress($user_ratio_7_1, '%')?></span></div></td>
<td><div align="right"><?=$user_1_req[1] ? $user_1_req[1] : 0?> <span title="Progression par rapport à la periode du <?=$periode_7t?>"><?=print_progress($user_ratio_1_1, '%')?></span></div></td>
<td><div align="right"><?=$user_n_req[1] ? $user_n_req[1] : 0?></div></td>
</tr>

<tr>
<th colspan="4"><?=($catid || $artid ? $trilibelle : 'Boutique')?></th>
</tr>


<tr>
<?php $user_ratio_7_ar = $user_ar_i_req[1] ? $user_ar_7_req[1]/$user_ar_i_req[1]*100-100 : 0; ?>
<?php $user_ratio_1_ar = $user_ar_7_req[1] ? $user_ar_1_req[1]/$user_ar_7_req[1]*100-100 : 0; ?>
<td>Vues Articles</td>
<td><div align="right"><?=$user_ar_7_req[1] ? $user_ar_7_req[1] : 0?> <span title="Progression par rapport à la periode du <?=$periode_it?>"><?=print_progress($user_ratio_7_ar, '%')?></span></div></td>
<td><div align="right"><?=$user_ar_1_req[1] ? $user_ar_1_req[1] : 0?> <span title="Progression par rapport à la periode du <?=$periode_7t?>"><?=print_progress($user_ratio_1_ar, '%')?></span></div></td>
<td><div align="right"><?=$user_ar_n_req[1] ? $user_ar_n_req[1] : 0?></div></td>
</tr>

<tr>
<?php $user_ratio_7_aj = $user_ar_i_req[0] ? $user_ar_7_req[0]/$user_ar_i_req[0]*100-100 : 0; ?>
<?php $user_ratio_1_aj = $user_ar_7_req[0] ? $user_ar_1_req[0]/$user_ar_7_req[0]*100-100 : 0; ?>
<td>Ajouts Panier</td>
<td><div align="right"><?=$user_ar_7_req[0] ? $user_ar_7_req[0] : 0?> <span title="Progression par rapport à la periode du <?=$periode_it?>"><?=print_progress($user_ratio_7_aj, '%')?></span></div></td>
<td><div align="right"><?=$user_ar_1_req[0] ? $user_ar_1_req[0] : 0?> <span title="Progression par rapport à la periode du <?=$periode_7t?>"><?=print_progress($user_ratio_1_aj, '%')?></span></div></td>
<td><div align="right"><?=$user_ar_n_req[0] ? $user_ar_n_req[0] : 0?></div></td>
</tr>

</table>
<?php /*<div align="right" style="margin-right:2em;"><a href="statsuser.php?date0=<?=$_GET['date0']?>&date1=<?=$_GET['date1']?>&catid=<?=$_GET['catid']?>&artid=<?=$_GET['artid']?>" target="_blank" title="Affichage Détaillé sur la période et le rayon séléctionnés">+ de details sur les Clients</a></div>*/ ?>
<br />

<?php
//preparer de quoi generer le graph !!

$graph_user_req = $DB_site2->query("SELECT *, UNIX_TIMESTAMP(dateline) as date FROM datelines LEFT JOIN (SELECT SUM(visites) as vstes, SUM(vues) as vues, COUNT(userid) as cli, dateline FROM stat_user GROUP BY dateline) as usr USING(dateline) LEFT JOIN (SELECT SUM(vues) as arvu, SUM(ajouts) as aj, dateline FROM stat_user_article GROUP BY dateline) as art USING(dateline) WHERE dateline BETWEEN FROM_UNIXTIME('".$date_0i."') AND FROM_UNIXTIME('".$date_11."') ORDER BY dateline DESC;");

$graph_user_legend = base64_encode('Clients;Visites;Pages Vues (x10);Vues Articles (x10);Articles Vues/Visites');
$graph_user_values = array();

$graph_user_cli = array();
$graph_user_vstes = array();
$graph_user_vues = array();
$graph_user_arvu = array();
$graph_user_arvuvs = array();

if($DB_site2->num_rows($graph_user_req) > 7) {
	//il faut reduire
	$graph_nb_7 = floor(($date_11 - $date_07)/(3600*24));
	$graph_nb_1 = floor(($date_11 - $date_01)/(3600*24));

	
	if($graph_nb_7 < 8 || $graph_nb_1 < 8) {
		//on a 7- jours entre les date
		//on prend tant qu'on peu :)
		$i = 0;
		while($graph_user = $DB_site2->fetch_array($graph_user_req)) {
			$i++;
			$graph_user_cli[] = $graph_user['cli'] == null ? 0 : $graph_user['cli'];
			$graph_user_vstes[] = $graph_user['vstes'] == null ? 0 : $graph_user['vstes'];
			$graph_user_vues[] = round($graph_user['vues']/10);
			$graph_user_arvu[] = round($graph_user['arvu']/10);
			$graph_user_arvuvs[] = $graph_user['vstes'] ? $graph_user['arvu']/$graph_user['vstes'] : 0;
			$graph_user_values[] =  date('d/m/y', $graph_user['date']);
			
			if($i > 6) {
				break;
			}
		}
		
	}
	else {
		//on a pas le choix il faut emputer
		$nb_enr = $graph_nb_1;
		$grp_num = ceil($nb_enr/7); //obligatoirement >= 2
		$nb_loop = ceil($nb_enr/$grp_num); // obligatoirement >= 4
		
		for($i = 0; $i < $nb_loop ; $i++) {
			$graph_user = $DB_site2->query_first("SELECT SUM(cli) as cli, SUM(vstes) as vstes, SUM(vues) as vues, SUM(arvu) as arvu FROM datelines LEFT JOIN (SELECT SUM(visites) as vstes, SUM(vues) as vues, COUNT(userid) as cli, dateline FROM stat_user GROUP BY dateline) as usr USING(dateline) LEFT JOIN (SELECT SUM(vues) as arvu, SUM(ajouts) as aj, dateline FROM stat_user_article GROUP BY dateline) as art USING(dateline) WHERE dateline BETWEEN FROM_UNIXTIME('".($date_11-(($i*$grp_num)+($grp_num-1))*3600*24)."') AND FROM_UNIXTIME('".($date_11-(($i*$grp_num)*3600*24))."');");
			
			$graph_user_cli[] = $graph_user['cli'] == null ? 0 : $graph_user['cli'];
			$graph_user_vstes[] = $graph_user['vstes'] == null ? 0 : $graph_user['vstes'];
			$graph_user_vues[] = round($graph_user['vues']/10);
			$graph_user_arvu[] = round($graph_user['arvu']/10);
			$graph_user_arvuvs[] = $graph_user['vstes'] ? $graph_user['arvu']/$graph_user['vstes'] : 0;
			$graph_user_values[] =  date('d/m/y',($date_11-(($i*$grp_num)+($grp_num-1))*3600*24));
		}
	}
	
}
else {
	
	while($graph_user = $DB_site2->fetch_array($graph_user_req)) {
		$graph_user_cli[] = $graph_user['cli'] == null ? 0 : $graph_user['cli'];
		$graph_user_vstes[] = $graph_user['vstes'] == null ? 0 : $graph_user['vstes'];
		$graph_user_vues[] = round($graph_user['vues']/10);
		$graph_user_arvu[] = round($graph_user['arvu']/10);
		$graph_user_arvuvs[] = $graph_user['vstes'] ? $graph_user['arvu']/$graph_user['vstes'] : 0;
		$graph_user_values[] =  date('d/m/y', $graph_user['date']);
	}
}
?>

<img src="statsgraphs.php?type=line&legends=<?=$graph_user_legend?>&bar1=<?=base64_encode(implode(";", $graph_user_cli))?>&bar2=<?=base64_encode(implode(";", $graph_user_vstes))?>&bar3=<?=base64_encode(implode(";", $graph_user_vues))?>&bar4=<?=base64_encode(implode(";", $graph_user_arvu))?>&line1=<?=base64_encode(implode(";", $graph_user_arvuvs))?>&values=<?=base64_encode(implode(";", $graph_user_values))?>&title=<?=base64_encode('Fréquentation Client - Boutique')?>" width="550" height="270" border="0" align="middle">

<br /><br />

<table class="stats">
<tr>
<th><div align="left">Géo-Localisation</div><font style="font-style:italic;"><?=$periode_1?></font></th>
<th>Visiteurs</th>
<th>Visites</th>
<th>Pages Vues</th>
<th>Visites 1 Page</th>
<th>Ratio / Pays</th>
</tr>
<?php 
	$geo_visiteurs = 0;
	$geo_visites = 0;
	$geo_vues = 0;
	$geo_v1p = 0;
?>
<?php $pie_val_array = array(); ?>
<?php $pie_lib_array = array(); ?>
<?php while($geoloc_stat = $DB_site2->fetch_array($geoloc_1_req)) { ?>
<?php //sum up to diff
	$geo_visiteurs += $geoloc_stat[0];
	$geo_visites += $geoloc_stat[1];
	$geo_vues += $geoloc_stat[2];
	$geo_v1p += $geoloc_stat[3];
	$pie_val_array[] = $geoloc_stat[0];
	$pie_lib_array[] = $geoloc_stat['pays']  ? ucwords(strtolower($geoloc_stat['pays'])) : 'Inconnu';
?>
<tr>
<?php $ratio_1p = $geoloc_stat[1] ? $geoloc_stat[3]/$geoloc_stat[1]*100 : '0'; ?>
<?php $ratio_geo = $geoloc_sum_req[0] ? $geoloc_stat[0]/$geoloc_sum_req[0]*100 : '0'; ?>
<td><?=$geoloc_stat['pays']  ? ucwords(strtolower($geoloc_stat['pays'])) : 'Inconnu'?></td>
<td align="right"><?=$geoloc_stat[0]?></td>
<td align="right"><?=$geoloc_stat[1]?> <font title="Ratio / Total Visites" style="font-style:italic;font-size:9px;color:blue;">(<?=($geoloc_sum_req[1] ? number_format($geoloc_stat[1]/$geoloc_sum_req[1]*100, '1', ',', '') : '0,0')?> %)</font></td>
<td align="right"><?=$geoloc_stat[2]?> <font title="Ratio / Total Pages Vues" style="font-style:italic;font-size:9px;color:blue;">(<?=($geoloc_sum_req[2] ? number_format($geoloc_stat[2]/$geoloc_sum_req[2]*100, '1', ',', '') : '0,0')?> %)</font></td>
<td align="right"><?=$geoloc_stat[3]?> <font title="Ratio Visites 1 Page / Visites" style="font-style:italic;font-size:9px;color:blue;">(<?=($geoloc_stat[1] ? number_format($geoloc_stat[3]/$geoloc_stat[1]*100, '1', ',', '') : '0,0')?> %)</font></td>
<td align="right"><?=number_format($ratio_geo, '1', ',', '')?> %</td>
</tr>


<?php } 
if($geoloc_sum_req[0] - $geo_visiteurs > 0 || $geoloc_sum_req[1] - $geo_visites > 0 || $geoloc_sum_req[2] - $geo_vues > 0 || $geoloc_sum_req[3] - $geo_v1p > 0) {
	?>
	<tr>
	<td>Autres...</td>
	<td align="right"><?=$geoloc_sum_req[0] - $geo_visiteurs?></td>
	<td align="right"><?=$geoloc_sum_req[1] - $geo_visites?> <font title="Ratio / Total Visites" style="font-style:italic;font-size:9px;color:blue;">(<?=($geoloc_sum_req[1] ? number_format(($geoloc_sum_req[1] - $geo_visites)/$geoloc_sum_req[1]*100, '1', ',', '') : '0,0')?> %)</font></td>
	<td align="right"><?=$geoloc_sum_req[2] - $geo_vues?> <font title="Ratio / Total Pages Vues" style="font-style:italic;font-size:9px;color:blue;">(<?=($geoloc_sum_req[2] ? number_format(($geoloc_sum_req[2] - $geo_vues)/$geoloc_sum_req[2]*100, '1', ',', '') : '0,0')?> %)</font></td>
	<td align="right"><?=$geoloc_sum_req[3] - $geo_v1p?> <font title="Ratio Visites 1 Page / Visites" style="font-style:italic;font-size:9px;color:blue;">(<?=($geoloc_sum_req[1] - $geo_visites ? number_format(($geoloc_sum_req[3] - $geo_v1p)/($geoloc_sum_req[1] - $geo_visites)*100, '1', ',', '') : '0,0')?> %)</font></td>
	<td align="right"><?=($geoloc_sum_req[0] ? number_format(($geoloc_sum_req[0] - $geo_visiteurs)/$geoloc_sum_req[0]*100, '1', ',', '') : '0,0')?> %</td>
	</tr>
	<?php
}

?>
<tr>
<th><div align="left">Total</div></td>
<th><div align="right"><?=$geoloc_sum_req[0]?></div></td>
<th><div align="right"><?=$geoloc_sum_req[1]?></div></td>
<th><div align="right"><?=$geoloc_sum_req[2]?></div></td>
<th><div align="right"><?=$geoloc_sum_req[3]?></div></td>
<th><div align="right"><?=($geoloc_sum_req[0] ? number_format($geoloc_sum_req[0]/$geoloc_sum_req[0]*100, '1', ',', '') : '0,0')?> %</div></td>
</tr>

</table>

<?php if(sizeof($pie_val_array)) { ?>

<br />
<img src="statsgraphs.php?type=pie&val=<?=base64_encode(implode(";", $pie_val_array))?>&lib=<?=base64_encode(implode(";", $pie_lib_array))?>&tot=<?=base64_encode($geoloc_sum_req[0])?>&title=<?=base64_encode('Geo-Localisation')?>" width="540" height="220" border="0" align="middle">

<?php } ?>

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