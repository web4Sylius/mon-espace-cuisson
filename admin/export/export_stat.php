<?php
require_once __DIR__.'/../../secrets.php';

include("../admin_global.php");
include("xlexport.php");

$requete=$DB_site2->query("SELECT catid,parentid,libelleFR FROM categorie");
while($row=$DB_site2->fetch_array($requete)) $cat[$row['catid']]=$row['libelleFR'];

$requete=$DB_site2->query("SELECT marqueid,libelle FROM marque");
while($row=$DB_site2->fetch_array($requete)) $marque[$row['marqueid']]=$row['libelle'];

$requete=$DB_site2->query("SELECT moyenid,libelleFR FROM moyenpaiement");
while($row=$DB_site2->fetch_array($requete)) $moyen[$row['moyenid']]=$row['libelleFR'];

$sdb=mysqli_connect("mysql5-3.business",STAT_DB_USER,STAT_DB_PASSWORD);
mysqli_select_db($sdb,"macaveavstat");


$xl=new xlexport("csv");
$xl->addrow(array("Numéro de commande",
"Date de commande",
"Date de création du compte",
"Montant HT de la commande",
"Code client",
"Catégorie article",
"Produit en promo",
"Prix original HT",
"Prix vendu HT",
"Nom du produit principal",
"Marque du produit principal",
"Référence de l'article principal",
"Capacité en nombre de bouteilles",
"Nombre d'articles",
"Pays de livraison",
"Pays de facturation",
"Code postal de livraison",
"Code postal de facturation",
"Société ou particulier",
"Mode de paiement",
"Extension de garantie",
"Provenance",
"Recherche",
"Nombre de pages vues",
"Temps passé",
"Page d'entrée"));


$sql="SELECT * FROM facture f INNER JOIN pays p ON(f.paysid=p.paysid) WHERE UNIX_TIMESTAMP(datefacture)>1275343200 AND valide=5";

$r=$DB_site2->query($sql,$DB_site2->link_id);
while($facture=$DB_site2->fetch_array($r)){
    $total=0;
    $qte=0;
    $gar="non";

    $lignes=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid='$facture[facturid]' ORDER BY prix",$DB_site2->link_id);
   

    while($ligne=$DB_site2->fetch_array($lignes)){
	 $artprix=$ligne[prix]*$ligne[qte];
	  $qte+=$ligne[qte];
	  $caracts=$DB_site2->query("SELECT * FROM lignefacturecaracteristique WHERE lignefactureid='$ligne[lignefactureid]'",$DB_site2->link_id);
	  while($caract=$DB_site2->fetch_array($caracts)){
		$artprix+=$caract[differenceprix];
		if($caract[caractvalid]==10 || $caract[caractvalid]==11) $gar="oui";
	  }

	  $art=$DB_site2->query("SELECT * FROM article NATURAL JOIN article_marque WHERE artid='$ligne[artid]'",$DB_site2->link_id);
	  $article=$DB_site2->fetch_array($art);
 
	  $promo="non";
	  if($article[prixht]!=$article[prixconseilleht]) $promo="oui";
	  $marque1=$marque[$article[marqueid]];
	  $libelle1=$article[libelleFR];
	  $artcode1=$article[artcode];
	  $capacite1=$article[capacite_bouteilles];

	  $total+=$artprix;
    }


    $r2=$DB_site2->query("SELECT * FROM utilisateur NATURAL JOIN pays WHERE userid=$facture[userid]",$DB_site2->link_id);
    $user=$DB_site2->fetch_array($r2);

    $total+=$facture['prixport'];
    $total-=$facture['montantcadeau'];
    $total=$total/(1+$user['TVAtauxnormal']/100);

    $r2=$DB_site2->query("SELECT * FROM stat WHERE user=".intval($user[userid]),$sdb);
    $stat=$DB_site2->fetch_array($r2);
    $referant=$stat['referant'];
    $query="";
    if($stat!=false && $stat['referant']=="") $referant="Saisie directe";
    if($stat['ip']=="193.252.174.176") $referant="Acilyon";
    if(substr($stat['referant'],0,4)=="http"){
	$p=parse_url($stat['referant']);
	$referant=$p['host'];
	parse_str($p['query'],$q);
	$query=utf8_decode($q['q']);
	
    }
    $minp=time();
    $maxp=0;
    $pagecount="";
    $visittime="";
    $r2=$DB_site2->query(" SELECT * FROM stat_page WHERE statid='".$stat[id]."' UNION SELECT * FROM stat_page_arch2 WHERE statid='".$stat[id]."' order by timestamp ASC ",$sdb);
	
	
    $pageentree="";
    if($r2!=false && $DB_site2->num_rows($r2)>0 && intval($stat['id'])!=0){
      while($row=$DB_site2->fetch_array($r2)){
	if($pageentree=="") $pageentree=$row['page'];
	if($row['timestamp']<$minp) $minp=$row['timestamp'];
	if($row['timestamp']>$maxp) $maxp=$row['timestamp'];
	$pagecount++;
      }
      $visittime=date("i:s",$maxp-$minp);
    }
    
    $xl->addrow(array($facture['facturid'],
    $facture['datefacture'],
    $user['dateinscription'],
    number_format($total,2,',',''),
    $user['userid'],
    $cat[$article['catid']],
    $promo,
    $article['prixconseilleht'],
    $article['prixht'],
    $libelle1,
    $marque1,
    $artcode1,
    $capacite1,
    $qte,
    $facture['libelle'],
    $user['libelle'],
    $facture['codepostal'],
    $user['codepostal'],
    ($user['raisonsociale']==""?"particulier":"professionnel"),
    $moyen[$facture['moyenid']],
    $gar,
    $referant,
    $query,
    $pagecount,
    $visittime,
    $pageentree
    ));
    
}

$xl->export();

/*
Numéro de commande
Date de commande
Date de création du compte
Montant HT de la commande
Catégorie (vieillissement, conservation, service, encastrable ...)
Produit en promo : oui / non
Nom du produit principal
Marque du produit principal
Référence de l'article principal
Capacité en nombre de bouteilles
Nombre d'articles dans la commande (ou une ligne par article de la commande)
Pays de l'adresse de livraison
Pays de l'adresse de facturation
Code postal de livraison
Code postal de facturation
Société ou particulier
Mode de paiement
Extension de garantie : oui / non
*/

?>