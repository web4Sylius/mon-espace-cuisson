<?php

$suivih=date("H");
// $suivih=18;

$test=false;
if(isset($_GET['test'])){
  $suivih=$_GET['test'];
  $test=true;
}

$nostat=1;
include_once("host_config_admin.php");
include_once($rootpath."class/email.php");


$DB_site=clone $DB_site2;


$today=mktime(0,0,0,date("m"),date("d"),date("Y"));
if(isset($_GET['today'])) $today=intval($_GET['today']);
$day2=$today+86400;
$day3=$today+172800;
if(date("w")==5){$day3+=86400;}

$style="<style>
.tbl { font-size:12px; width:100%; margin-top:10px; border-collapse:collapse; }
.tbl thead { font-weight:bold; text-align:center; }
.tbl td { border:solid thin #808080; padding:none; }
.tbl table { border-collapse:collapse; width:100%; }
.tbl table td { border:none; }
.tbl table .tdsep { border-top:solid thin #AAA; }
.title { text-align:center; background-color:#cccccc; }
</style>";


$requete=$DB_site->query("SELECT id,mail,societe,ville,suivi FROM fournisseur WHERE suivi LIKE '%STOCK%' AND suivih=".$suivih);
while($fourn=$DB_site->fetch_array($requete)){
    $suivis=explode(",",$fourn['suivi']);
    $lid=array();
    foreach($suivis as $suivi) if(substr($suivi,0,5)=="STOCK") $lid[]=intval(substr($suivi,5));

    $r2=$DB_site->query("SELECT ef.facturid,efl.indate,efl.outdate,efl.imperatif,efl.qte,ef.trspt,lf.artid,ef.enlev FROM envoi_fournisseur ef INNER JOIN envoi_fournisseur_ligne efl ON(efl.efid=ef.id) INNER JOIN lignefacture lf on(lf.lignefactureid=efl.ligne_facture_id) WHERE ef.enlev IN (".implode(",",$lid).") AND indate<=".($today+86399)." AND indate>0 AND ef.enlevement=0 AND ef.type IN ('fourn','stock')");
    if($DB_site->num_rows($r2)>0){
	$ef=array();
	$body="";

	while($enlev=$DB_site->fetch_array($r2)){
	    $retard=($enlev['indate']<$today?0:1);
	    $article=$DB_site->query_first("SELECT artcode,artcode_eurocave,libelleFR,m.libelle AS marque FROM article a INNER JOIN article_marque am USING(artid) INNER JOIN marque m USING(marqueid) WHERE artid=".$enlev['artid']);
	    $sef=$ef[$retard][$enlev['enlev']][$enlev['facturid']];
	    if(!isset($sef)) $sef=$enlev;
	    $sef['detail'].="<tr class=\"$class\"><td>".$enlev['qte']."</td><td>".$article['artcode']."<br><i>".$article['artcode_eurocave']."</i></td><td>".$article['marque']."</td><td>".$article['libelleFR']."</td></tr>";
	    $ef[$retard][$enlev['enlev']][$enlev['facturid']]=$sef;
	}

	ksort($ef);
	foreach($ef as $retard=>$efday){
	    $txtdate=($retard==0?"en retard":"du ".date("d/m/Y",$today));
	    foreach($efday as $lid=>$eflist){
		$lieu=$DB_site->query_first("SELECT societe,ville FROM fournisseur WHERE id=".$lid);	
		$body.="<table class=\"tbl\">
		<thead>
		<tr><td class=\"title\" colspan=\"5\">Enlèvement stock ".$lieu['societe']." ".$lieu['ville']." ".$txtdate."</td></tr>
		<tr><td>Date d'enlèvement</td><td>Commande</td><td>Détail</td><td>Destination</td><td>Transporteur</td></tr>
		</thead>";
			
		foreach($eflist as $facturid=>$data){
		    $facture=$DB_site->query_first("SELECT nom,ville,codepostal,p.libelle AS pays FROM facture f INNER JOIN pays p USING(paysid) WHERE facturid=".$facturid);
		    $body.="<tr>
		    <td>".date("d/m/Y",$data['indate'])."</td>
		    <td>".$data['facturid']."</td>
		    <td><table>".$data['detail']."</table></td>
		    <td>".$facture['nom']."<br>".$facture['codepostal']." ".$facture['ville']."<br>".$facture['pays']."</td>
		    <td>".$data['trspt']."</td>
		    </tr>";
		}
		$body.="</table>";
	    }
	}

	if($test)
	  print $style."<hr>".$fourn['mail'].$body;
	else{
	  $body=$style."Bonjour,<br><br>Veullez trouver un récapitulatif de commandes à préparer pour départ ce jour "
	  .$body.
	  "<br>
	  Merci de retourner ce mail rempli vers 17h dès que les produits sont partis
	  <br>Cordialement,
	  <br>ADV - Ma Cave à Vin - www.ma-cave-a-vin.fr
	  <br>Tel +334.26.03.05.44 / fax : +33.4.26.03.05.49";
	  $mail=new email();
	  $mail->destinataire=$fourn['mail'];
	  $mail->expediteur="adv@mon-espace-cuisson.com";
	  $mail->expname="Ma Cave à Vin";
	  $mail->copymode="bcc";
	  $mail->to=array("pr@mon-espace-cuisson.com","adv@mon-espace-cuisson.com","webmaster@mon-espace-cuisson.com");
	  $mail->subject="Suivi des commandes de stock Ma Cave à Vin au ".date("d/m/Y",$today);
	  $mail->body=$body;
	  $mail->sendmail();
	}
    }
}

?>