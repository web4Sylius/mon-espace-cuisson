<?php
// include("../admin_global.php");

ini_set("display_errors",1);
set_time_limit(300);

ini_set("display_errors",1);
set_time_limit(300);

$rootpath="/var/www/vhosts/mon-espace-cuisson.com/httpdocs/";
$rootpath="../../";

$host="www.mon-espace-cuisson.com" ;
$nostat=1;
include_once($rootpath."admin/host_config_admin.php");
include_once($rootpath."configurations/$host/config_mysql.php");
include_once($rootpath."class/mysql.php");
include_once($rootpath."configurations/$host/config.php");
include_once($rootpath."class/fonctions.php");
include_once($rootpath."class/facture.class.php");

include_once("xlexport.php");

include_once($rootpath."class/template.class.php");
include_once($rootpath."class/devises.class.php");

$langue="FR";
$template=new template($langue,$DB_site2);



$categories=array();
$parents=array();
$requete=$DB_site2->query("SELECT * FROM categorie");
while($cat=$DB_site2->fetch_array($requete)){
    $categories[$cat['catid']]=$cat['libelleFR'];
    $parents[$cat['catid']]=$cat['parentid'];
}
$pays=array();
$requete=$DB_site2->query("SELECT * FROM pays");
while($row=$DB_site2->fetch_array($requete)){
    $pays[$row['paysid']]=$row['libelle'];
}

$tvafrance=$DB_site2->query_first("SELECT 0, TVAtauxnormal, TVAtauxreduit FROM pays WHERE paysid=57");

$active=array(	0=>"Inactif temporairement",
				1=>"Actif",
				2=>"Supprimé");
$commandable=array(	0=>"Vendu en magasin",
					1=>"Commandable",
					2=>"Non commandable");

$fields=array(
				"article.artid",
				"article.catid",
				"article.active",
				"article.commandable",
				"FROM_UNIXTIME(article.date_appro) AS dateappro",
				"article.artcode",
				"marque.libelle AS marque",
				"article.artcode_eurocave",
				"article.prixconseilleht",
				"article.prix",
				"article.prixht",
				"article.paht",
				"ROUND(article.ecopart, 2)",
				"ROUND(article.ecopartht, 2)",
				"prixtrspt",
				"article.libelleFR",
				"article.delailivraison",
				"article.paysid",
				"article.descriptionFR",
				"article.Titre2FR",
				"article.energy_label",
				"article.dimensions",
				"article.hauteur",
				"article.largeur",
				"article.profondeur",
				"article.poid",
				"article.volume",
				"article.poidsvolume",
				"article.douanier",
				"code_barre.codebarre");
$names=array(
				"artid",
				"catégorie",
				"active",
				"commandable",
				"date réapprovisionnement",
				"artcode",
				"marque",
				"artcode eurocave",
				"prix barré ht",
				"prix",
				"prix ht",
				"prix achat ht",
				"ecopart ttc",
				"ecopart ht",
				"trspt fourn/emball",
				"libelleFR",
				"delailivraison",
				"pays fabrication",
				"Description",
				"titre 2",
				"energy_label",
				"dimensions",
				"hauteur",
				"largeur",
				"profondeur",
				"poid",
				"volume",
				"poidsvolume",
				"douanier",
				"codebarre",
				"image",
				"url");

$name="export_produits_".date("Ymd");
$xl=new xlexport("csv",$name,false);
$xl->addrow($names);

$where=" WHERE 1";
if(isset($_GET['actifs'])) $where.=" AND article.active='".intval($_GET['actifs'])."'";
if(isset($_GET['visible'])) $where.=" AND article.active='".intval($_GET['visible'])."'";
if(isset($_GET['commandable'])) $where.=" AND article.commandable='".intval($_GET['commandable'])."'";
if(isset($_GET['marque'])) $where.=" AND article_marque.marqueid=".intval($_GET['marque']);
if(isset($_GET['categorie'])) $where.=" AND article.catid=".intval($_GET['categorie']);

$requete=$DB_site2->query("SELECT count(article.artid) FROM article inner JOIN article_marque on (article_marque.artid=article.artid) inner JOIN marque on (marque.marqueid=article_marque.marqueid) INNER JOIN code_barre ON(code_barre.artid=article.artid)".$where);
$count=$DB_site2->fetch_array($requete);


for($i=0;$i<$count[0];$i+=100){
    $sql="SELECT ".implode(",",$fields)." FROM article inner JOIN article_marque on (article_marque.artid=article.artid) inner JOIN marque on (marque.marqueid=article_marque.marqueid) INNER JOIN code_barre ON(code_barre.artid=article.artid) $where group by article.artid ORDER BY article.artid limit $i,100";
    $req=$DB_site2->query($sql);

    if($req==false) print mysqli_error($DB_site2->link_id);
    while($art=$DB_site2->fetch_assoc($req))   {
        if($art['dateappro']=="1970-01-01 01:00:00") $art['dateappro']="";
        $catname=$categories[$parents[$art['catid']]];
        if($catname=="") $catname=" ";
        $art['catid']=$categories[$art['catid']];
        $art['active']=$active[$art['active']];
        $art['commandable']=$commandable[$art['commandable']];
        $art['paysid']=$pays[$art['paysid']];
		// $remise=round(100*(1-$art['prixht']/$art['prixconseilleht']),2);
        // array_splice($art,1,0,$catname);
        // array_splice($art,14,0,$remise);
        $prixport=prixport($art['emballage_poids'],$art['prix']);
        // array_splice($art,17,0,$prixport['prix']);
        // array_splice($art,18,0,round($art['prixht']+$prixport['prix'],2));
        $achatgar=achatgarantie($art['artid']);
        // array_splice($art,20,0,$achatgar);
        // array_splice($art,24,0,$prixport['achat']);
        $totalachat=$art['paht']+$art['ecopartht']+$prixport['achat']+$achatgar+$art['prixtrspt'];
        // array_splice($art,25,0,$totalachat);
        $margeht=round(($art['prixht']+$prixport['prix'])-$totalachat,2);
        // array_splice($art,26,0,$margeht); 
        $margepc=round(100*$margeht/($art['prixht']+$prixport['prix']),2);
        // array_splice($art,27,0,$margepc);
        $spy=spyprice($art['artcode']);
		// $diff=round(-100*(1-floatval($spy['price'])/$art['prix']),2);
        if($spy['price']=="") $diff="";
        $spy[]=$diff;
        // array_splice($art,28,0,$spy);
        $files=array();
        $files[]="https://".$host."/configurations/".$host."/images/produits/article/".$art['artid'].".jpg";
        $files[]="https://".$host.$template->mkurl("article",$art['artid'],$art);
        $art=array_merge($art,$files);
        $xl->addrow($art);
    }
}
$xl->export();


function url_rewrite($string) {
        $string=html_entity_decode($string , ENT_NOQUOTES , "ISO-8859-1" );
        $caracteres = array("¥" => "Y", "µ" => "u", "À" => "A", "Á" => "A", "Â" => "A", "Ã" => "A", "Ä" => "A", "Å" => "A",
        "Æ" => "A", "Ç" => "C", "È" => "E", "É" => "E", "Ê"=> "E", "Ë" => "E", "Ì" => "I", "Í" => "I",
        "Î" => "I", "Ï" => "I", "Ð" => "D", "Ñ" => "N", "Ò" => "O", "Ó" => "O", "Ô" => "O", "Õ" => "O",
        "Ö" => "O", "Ø" => "O", "Ù" => "U", "Ú" => "U", "Û" => "U", "Ü" => "U", "Ý" => "Y", "ß" => "s",
        "à" => "a", "á" => "a", "â" => "a", "ã" => "a", "ä" => "a", "å"=> "a", "æ" => "a", "ç"=> "c",
        "è" => "e", "é" => "e", "ê" => "e", "ë" => "e", "ì" => "i", "í" => "i", "î" => "i", "ï"=> "i",
        "ð"=> "o", "ñ"=> "n", "ò" => "o", "ó" => "o", "ô" => "o", "õ" => "o", "ö" => "o", "ø" => "o",
        "ù" => "u", "ú" => "u", "û" => "u", "ü" => "u", "ý" => "y", "ÿ" => "y");
        $string = strtr($string , $caracteres);
        $string=strtolower($string);
        $len = strlen($string);
        for($a=0; $a<$len; $a++){
            $p = ord($string[$a]);
            if ( ($p > 96 && $p < 123) or ($p > 47 && $p < 58) ) {
                $return .= $string[$a];
            } else {
                $return .= "-";
            }
        }
        while(strpos($return,"--")!==FALSE) {
            $return=str_replace("--","-",$return);
        }
        $return=trim($return,"-");
        return $return;
}

function prixport($poids,$prix){
	global $tvafrance;
    global $DB_site2;
	
	$requete=$DB_site2->query("SELECT * FROM fraisport WHERE paysid=57 AND ".(1000*$poids)." BETWEEN poiddebut AND poidfin");
	$port=$DB_site2->fetch_array($requete);
	if($port==false) return array(0,0);
	if($prix>$port['gratuit']) $port['prix']=0;
	$port['prix']=round($port['prix']/(1+$tvafrance[1]/100),2);
	
	return $port;
}

function achatgarantie($artid){
    global $DB_site2;

	$requete=$DB_site2->query("SELECT achat FROM article_caractval ac INNER JOIN caracteristiquevaleur cv USING (caractvalid) WHERE caractvalid IN (9,10,11,134,154,155) AND artid=".$artid." ORDER BY cv.position LIMIT 1");
	$return=$DB_site2->fetch_array($requete);
	return round($return['achat'],2);
}

function spyprice($artcode){
    global $DB_site2;
    
	$limit=time()-864000;
	$requete=$DB_site2->query("SELECT price,site FROM `history` WHERE `artcode`='$artcode' and timestamp>$limit order by price LIMIT 1");
	$spy=$DB_site2->fetch_assoc($requete);
	if($spy==false) return array("price"=>"","site"=>"");
	return $spy;
}

?>
