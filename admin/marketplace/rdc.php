<?php

require ("../admin_global.php");
include("article.php");
include("order.php");

class mpconnect{
var $DB;
var $doc;
var $url="https://marchands.rueducommerce.fr/merchant/syndication/orders/ma%20cave/new_orders_248a0a1bd20c59d74ef20eef3e365d578558d8fa.xml";
var $xml;
var $tvafr;

function __construct($DB){
    $this->DB=$DB;
    $tvafr=$this->DB->query_first("SELECT TVAtauxnormal,TVAtauxreduit FROM pays WHERE paysid=57");
    $this->txtva[0]=0;
    $this->txtva[1]=$tvafr['TVAtauxnormal'];
    $this->txtva[2]=$tvafr['TVAtauxreduit'];
}

function getdata(){
    $this->doc=new DOMDocument();
    $this->xml=file_get_contents($this->url);
    $this->doc->loadXML($this->xml);
}

function getlist(){
    if($this->doc==null) $this->getdata();
    $return=array();
    $orders=$this->doc->getElementsByTagName("order");
    foreach($orders as $order){
      $infocommande=$order->getElementsByTagName("infocommande")->item(0);
      $refid=$infocommande->getElementsByTagName("refid")->item(0)->nodeValue;
      $return[$refid]['status']=$infocommande->getElementsByTagName("status")->item(0)->nodeValue;
      $return[$refid]['date']=$this->ts($infocommande->getElementsByTagName("date")->item(0)->nodeValue);
      $utilisateur=$order->getElementsByTagName("utilisateur")->item(0);
      $return[$refid]['nom']=$utilisateur->getElementsByTagName("nom")->item(0)->nodeValue;
    }
    return $return;
}

function createcmd($id){
	global $DB_site2;
  if($this->doc==null) $this->getdata();
  $orders=$this->doc->getElementsByTagName("order");
  foreach($orders as $order){
    $infocommande=$order->getElementsByTagName("infocommande")->item(0);
    $refid=$infocommande->getElementsByTagName("refid")->item(0)->nodeValue;
    $status=$infocommande->getElementsByTagName("status")->item(0)->nodeValue;
    $date=$infocommande->getElementsByTagName("date")->item(0)->nodeValue;
    if($refid==$_GET['refid']){
	$neworder=new MP_order(0,$DB_site2);
	$neworder->billing_date=$this->ts($date);
	$neworder->market_place_id="07";
	$neworder->payment_method=5;
	$neworder->mp_facturid=$refid;
	$utilisateurs=$order->getElementsByTagName("utilisateur");
	foreach($utilisateurs as $utilisateur){
	    $type=$utilisateur->getAttribute("type");
	    $pref="shipping";
	    if($type=="facturation") $pref="billing";
	    $neworder->{$pref."_first_name"}=$utilisateur->getElementsByTagName("prenom")->item(0)->nodeValue;
	    $neworder->{$pref."_last_name"}=$utilisateur->getElementsByTagName("nom")->item(0)->nodeValue;
	    $neworder->{$pref."_company"}=$utilisateur->getElementsByTagName("societe")->item(0)->nodeValue;
	    $neworder->{$pref."_phone"}=$utilisateur->getElementsByTagName("telhome")->item(0)->nodeValue;
	    $neworder->{$pref."_email"}=$utilisateur->getElementsByTagName("email")->item(0)->nodeValue;
	    $adresse=$utilisateur->getElementsByTagName("adresse")->item(0);
	    $neworder->{$pref."_address1"}=$adresse->getElementsByTagName("rue1")->item(0)->nodeValue;
	    $neworder->{$pref."_address2"}=$adresse->getElementsByTagName("rue2")->item(0)->nodeValue;
	    $neworder->{$pref."_zip"}=$adresse->getElementsByTagName("cpostal")->item(0)->nodeValue;
	    $neworder->{$pref."_city"}=$adresse->getElementsByTagName("ville")->item(0)->nodeValue;
	}
	$neworder->billing_country=57;
	$neworder->shipping_country=57;
	$transport=$infocommande->getElementsByTagName("transport")->item(0);
	$neworder->shipping_price=$transport->getElementsByTagName("montant")->item(0)->nodeValue;
	$neworder->vat_100=$txtva[1];
	$neworder->create();

	$produits=$infocommande->getElementsByTagName("produit");
	foreach($produits as $produit){
	    $ol=new MP_Order_line();
	    $ol->order=$neworder->id;
	    $art=$DB_site2->query_first("SELECT artid,caractvalid FROM code_barre WHERE codebarre='".$produit->getAttribute("ean13")."'");
	    $article=$DB_site2->query_first("SELECT tauxchoisi,paht,libelleFR FROM article WHERE artid=".intval($art['artid']));
	    $ol->artid=$art['artid'];
	    $ol->ref_tin=$produit->getAttribute("partnumber");
	    $ol->qte=$produit->getAttribute("nb");
	    $ol->price=$produit->getAttribute("price");
	    $ol->paht=$article['paht'];
	    $ol->tva=$txtva[$article['tauxchoisi']];
	    $ol->product_name=$article['libelleFR'];
	    $ol->create();

	    if($art['caractvalid']>0){
		$caract=$DB_site2->query_first("SELECT cv.libelleFR AS caractvalname,c.libelleFR AS caractname,av.paht FROM caracteristiquevaleur cv INNER JOIN caracteristique c USING(caractid) INNER JOIN article_caractval av USING(caractvalid) WHERE cv.caractvalid=".$art['caractvalid']." AND artid=".$art['artid']);
		$DB_site2->query("INSERT INTO lignefacturecaracteristique SET lignefactureid=".$ol->id.", caractvalid=".$art['caractvalid'].", libelle_caract='".$caract['caractname']."', libelle_caractval='".$caract['caractvalname']."', differenceprix=0, paht=".floatval($caract['paht']));
	    }
	}
    }
  }
}

function ts($date){
    preg_match("/([0-9]+)\/([0-9]+)\/([0-9]+)\ ([0-9]+):([0-9]+):([0-9]+)/",$date,$d);
    return mktime($d[4],$d[5],$d[6],$d[2],$d[1],$d[3]);
}

}


$rdc=new mpconnect($DB_site2);
$listorders=$rdc->getlist();


print "<table>";
foreach($listorders as $id=>$data){
    $facture=$DB_site2->query_first("SELECT valide FROM facture WHERE provenanceid='07' AND mp_facturid='".$id."'");
    switch($facture['valide']){
    case "":
    $link="Créer";
    break;
    case 7:
    $link="Validée";
    break;
    case 8:
    case 5:
    $link="Expédiée";
    break;
    }
    print "<tr><td>".$id."</td><td>".$data['nom']."</td><td>".$data['date']."</td><td>".$link."</td></tr>";
}
print "</table>";