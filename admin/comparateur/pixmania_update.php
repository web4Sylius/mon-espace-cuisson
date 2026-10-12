<?php

ini_set("display_errors","1");

$rootpath="/var/www/macave/site/";
$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";

$host="www.ma-cave-a-vin.fr" ;
$nostat=1;
include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."class/mysql.php");
include($rootpath."class/template.class.php");
include($rootpath."class/stock.class.php");
include($rootpath."class/fonctions.php");
include($rootpath."admin/mpfields.php");
include($rootpath."admin/export/xlexport.php");

$libelle="libelleFR";
$description="descriptionFR";

$today=mktime(0,0,0);

$DB_site2=new DB_Sql;
$DB_site2->appname='boutique';
$DB_site2->appshortname='boutique';
$DB_site2->database=$DBname;
$DB_site2->server=$db_server;
$DB_site2->user=$DBusername;
$DB_site2->password=$DBpassword;
$DB_site2->connect();
$DB_site=$DB_site2;

$template=new template("FR",$DB_site2);

$active2qte=array('0'=>0,'1'=>10,'2'=>0);

$csv=new xlexport("csv");
$csv->name="pixmania";
$csv->csvsep=";";

$csv->addrow(array("SKU","CTRY","LANGUAGE","RETAIL_PRICE","STOCK","AVAILABILITY","SHIPPING_COST","SHIPPING_DESC","CONDITION_CODE","SHIPPING_COST_MULT","SALES_PRICE"));

$tvafrance=$DB_site2->query_first("SELECT TVAtauxnormal AS taux1,TVAtauxreduit AS taux2 FROM pays WHERE paysid=57");
$tvafrance['taux1']=(1+$tvafrance['taux1']/100);
$tvafrance['taux2']=(1+$tvafrance['taux2']/100);


$articles=$DB_site2->query("SELECT a.* FROM article a inner join categorie c 
                           using(catid) where active = '1' and commandable = '1' group by artcode order by a.artid");

while ($article=$DB_site2->fetch_array($articles)){

        $mp_field=array();
        $allenabled=$DB_site2->query_first("SELECT enabled FROM market_place_champs WHERE market_place='pixmania' AND artcode='".$article['artcode']."'");
	
	if($allenabled[0]=="1" && $article['date_appro']<$today){
	    $coloriss = $DB_site2->query("SELECT ac.image as imagecaractvalid, cv.image, ac.caractvalid, ac.artid, cv.$libelle FROM article_caractval ac INNER JOIN caracteristiquevaleur cv USING(caractvalid) WHERE ac.artid = '$article[artid]' AND cv.caractid = '1'");
	    if($DB_site2->num_rows($coloriss)==0)
	      $xml.=product($article);
	    else{
	      while($coloris=$DB_site2->fetch_array($coloriss))
		$xml.=product($article,$coloris);
	    }
	}
}

if($argv[1]=="write")
  $csv->write($rootpath."admin/comparateur");
else
  $csv->export();

function product($article,$coloris=false){
	global $DB_site2;
	global $template;
	global $csv;
	global $host;
	global $tvafrance;
	global $active2qte;

	$line=array();
	$port = $DB_site2->query_first("SELECT p.prix as prixport, a.poid,p.gratuit FROM fraisport p, article a WHERE p.paysid = '57' AND a.poid between p.poiddebut and p.poidfin AND a.artid = '$article[artid]'");
	$temp_price=$DB_site2->query_first("SELECT * FROM temp_price WHERE artid=".$article['artid']." AND ".time()." BETWEEN du AND au");
	if($temp_price['id']!=""){
	    $article[prixconseilleht]=$temp_price[prixconseilleht];
	    $article[prixht]=round($temp_price[prix]/$tvafrance['taux'.$article['tauxchoisi']],5);
	}
	if($article[prix]>=$port['gratuit']) $port[prixport]=0;
	$delai=delailivraison($article);

	$line[0]=$article['artid'];
	$line[1]="FR";
	$line[2]="FR";
	$line[3]=$article['prixconseilleht'];
	$line[4]=$active2qte[$article['active']];
	$line[5]=$delai['delailivraison'];
	$line[6]=round($port[prixport]/$tvafrance['taux1'],5);
	$line[7]="Livraison par transporteur au pied de l'habitation";
	$line[9]="00000000";
	$line[10]=0;
	$line[11]=$article['prixht'];
	
	$csv->addrow($line);
}

/*
function product($article,$coloris=false){
	global $DB_site2;
	global $host;
	global $tvafrance;

	$marque=$DB_site2->query_first("SELECT m.* FROM article_marque am INNER JOIN marque m USING(marqueid) WHERE am.artid=".$article['artid']);
	$ean=$DB_site2->query_first("SELECT codebarre FROM code_barre WHERE artid = '$article[artid]'".($coloris!=false?" AND caractvalid=".$coloris['caractvalid']:""));

	if($coloris!=false) $article['coloris']=$coloris['libelleFR'];
	$requete=mysql_query("SELECT champ, valeur FROM market_place_catid WHERE market_place='pixmania' AND catid='".$article['catid']."'");
	while($champ=mysql_fetch_array($requete)) $mp_field[$champ['champ']]=mpfields($article,$champ['valeur']);

	$temp_price=$DB_site2->query_first("SELECT * FROM temp_price WHERE artid=".$article['artid']." AND ".time()." BETWEEN du AND au");
	if($temp_price['id']!=""){
	    $article[prixconseilleht]=$temp_price[prixconseilleht];
	    $article[prix]=$temp_price[prix];
	    $article[prixht]=$temp_price[prix]/$tvafrance['taux'.$article['tauxchoisi']];
	}
	

	$image="http://$host/configurations/$host/images/produits/article/$article[artid].$article[image]";
	if ($coloris[imagecaractvalid] == "jpg")
	  $image = "http://$host/configurations/$host/images/produits/article_caractval/$article[artid]_".$coloris[caractvalid].".$coloris[imagecaractvalid]";
	$image2="";
	$im2=$DB_site2->query_first("SELECT * FROM articlephoto WHERE artid=$article[artid] ORDER BY position");
	if($im2['image']!="") $image2="<secondary>http://$host/configurations/$host/images/produits/$article[artid]_$im2[articlephotoid].$im2[image]</secondary>";

	$ret="<item id=\"".$ean[0]."\">
	<main_info>
	    <brand id=\"".$marque['pixid']."\">".strtoupper($marque['libelle'])."</brand>
	    <segment id=\"".$mp_field['segment']."\"></segment>
	    <ean>".$ean[0]."</ean>
	</main_info>
	<description>
	    <language code=\"fr\">
		<main_description>".utf8_encode($mp_field['title'])."</main_description>
		<short_description>".utf8_encode($mp_field['title'])."</short_description>
		<marketing_description><![CDATA[".format_desc($article['descriptionFR'])."]]></marketing_description>
	    </language>
	</description>
	<media>
	    <main>$image</main>
	    $image2
	</media>";

	$article['dimensions']=str_replace("*"," x ",$article['dimensions']);
	switch($mp_field['segment']){
	  case 4042:
	  case 4046:
	  case 4047:
	  case 4049:
	  case 4053:
	  $ret.="<specification>
	    <language code=\"fr\">
		<value code=\"HD00061760\">".utf8_encode($article['dimensions'])."</value>
		<value code=\"HD00061761\">".utf8_encode($article['coloris'])."</value>
	    </language>
	  </specification>";
	  break;

	  case 9689:
	  case 9690:
	  $ret.="<specification>
	    <language code=\"fr\">
		<value code=\"HD00051795\">".utf8_encode($article['dimensions'])."</value>
		<value code=\"HD00051796\">".utf8_encode(round($article['poid']/1000,1))." kg</value>";
		if($article['filtre_charbon']!="") $ret.="<value code=\"HD00062341\">".$article['filtre_charbon']."</value>\n";
		if($article['eclairage']!="") $ret.="<value code=\"HD00062342\">".utf8_encode($article['eclairage'])."</value>\n";
		if($article['garantie_incluse']!="") $ret.="<value code=\"HD00062345\">".utf8_encode($article['garantie_incluse'])."</value>\n";
		if($article['capacite_bouteilles']!=0) $ret.="<value code=\"HD00051787\">".utf8_encode($article['capacite_bouteilles'])."</value>\n";
		if($article['puissance_electrique']!=0) $ret.="<value code=\"HD00051788\">".utf8_encode($article['puissance_electrique'])."</value>\n";
		if($article['regulation']!="") $ret.="<value code=\"HD00051790\">".utf8_encode($article['regulation'])."</value>\n";
		if($article['hygrometrie']!="") $ret.="<value code=\"HD00051792\">".utf8_encode(stripslashes($article['hygrometrie']))."</value>\n";
		if($article['version_porte']!="") $ret.="<value code=\"HD00051793\">".utf8_encode($article['version_porte'])."</value>\n";
	  $ret.="</language>
	  </specification>";
	  break;
	}
	
	$ret.="<price>
	    <country code=\"fr\">
		<delivery>".$article[delailivraison]."</delivery>
		<unit_price>".number_format($article['prixht'],5,'.','')."</unit_price>\n";
	if(round($article['prixht'])!=round($article['prixconseilleht'])) $ret.="<sales_unit_price>".number_format($article['prixconseilleht'],5,'.','')."</sales_unit_price>\n";
	$fraisport=$DB_site2->query_first("SELECT * FROM fraisport WHERE poiddebut <= '".$article['emballage_poids']."' AND poidfin >= '".$article['emballage_poids']."' and paysid = '57'");
	if($fraisport['gratuit']<$article['prix']) $fraisport['prix']=0;

    $ret.="	<weee_amount>".number_format(floatval($article['ecopartht']),5,'.','')."</weee_amount>
		<state>00000000</state>
		<shipping_price_alone>".number_format($fraisport['prix']/1.196,5,'.','')."</shipping_price_alone>
		<shipping_desc>Livraison sur RDV par 1 personne</shipping_desc>
		<stock>100</stock>
	    </country>
	</price>
	</item>";
  
	 return $ret;
}*/

function format_desc($txt){
     $ret=html_entity_decode($txt);
     $ret=utf8_encode($ret);
     $ret=stripslashes($ret);
     $ret=preg_replace("/<br>|<p([^<]*)>/i","<br/>",$ret);
     $ret=strip_tags($ret,"<br/>");
     $ret=str_replace("\"\"","\"",$ret);

     return $ret;
}

?>

