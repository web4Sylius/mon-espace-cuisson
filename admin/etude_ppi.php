<?php

require "admin_global.php";

/*
include "../class/DAOarticle.class.php";
include "../class/garantie.class.php";
include "../class/DAOgarantie.class.php";
include "../class/stock.class.php";
include "spy.class.php";
*/


include "./header.php";
include "./menu.php";

$tauxtva=1.2;

if(isset($_GET['marqueid'])){
	$marqueid=$_GET['marqueid'];
} else {
	$marqueid=""; // toutes les marques
}
if(isset($_GET['typearticleid'])){
	$typearticleid=$_GET['typearticleid'];
} else {
	$typearticleid=""; // tous les produits
}
if(isset($_GET['typesrc'])){
	$typesrc=$_GET['typesrc']; // les plus vendus, les plus vus ou tous les produits.
} else {
	$typesrc=""; // tous les produits
}
if(isset($_GET['typeppi'])){
	$typeppi=$_GET['typeppi'];
	if($typeppi=="inf"){
		$selected_spy_inf="checked";
	}
	if($typeppi=="sup"){
		$selected_spy_sup="checked";
	}
	if($typeppi=="egal"){
		$selected_spy_egal="checked";
	}
	if($typeppi=="nospy"){
		$selected_spy_nospy="checked";
	}
	if($typeppi=="tous"){
		$selected_spy_tous="checked";
	}
} else {
	$typeppi="";
}


$req=$DB_site2->query("SELECT * FROM marque ORDER BY libelle");
while($m=$DB_site2->fetch_array($req)){
    $marques[$m['marqueid']]=$m['libelle'];
	if($m['marqueid']==$marqueid){
		$selected="selected";
	} else {
		$selected="";
	}
    $optmarques.="<option value=\"".$m['marqueid']."\" ".$selected.">".$m['libelle']."</option>";
}

$req=$DB_site2->query("SELECT * FROM typearticle ORDER BY libelle");
while($ta=$DB_site2->fetch_array($req)){
	if($ta['typearticleid']==$typearticleid){
		$selected="selected";
	} else {
		$selected="";
	}
	$opttypearticle.="<option value=\"".$ta['typearticleid']."\" ".$selected.">".$ta['libelle']."</option>";
}

if(mktime(0,0,0,date("m")-6)<mktime(0,0,0,4,5,2018)){
	$from=date("d/m/Y", mktime(0,0,0,4,5,2018));
	// $dfrom=date_create("2 month ago");
	$dfrom = new DateTime("05/04/2018"); 
} else {
	$from=date("d/m/Y", mktime(0,0,0, date("m")-6));
	$dfrom=date_create("6 month ago");
}

$to=date("d/m/Y");

$dto=date_create();





?>
  <h1>Etude de PPI :</h1>
  <div class="commande_detail">
	  <form name="filter" method="get" action="etude_ppi.php" style="margin:10px;">
	  <input type="hidden" name="action" value="etude">
		<p class="column">
			<b>Type de recherche :</b> 
			<input type="radio" name="typesrc" value="vus"> Les 100 articles les plus vus 
			<input type="radio" name="typesrc" value="vendus"> Les 100 articles les plus vendus 
			<input type="radio" name="typesrc" value="" checked> Tous les articles 
		</p>
		<p class="column">
			<b>PPI :</b> 
			<input type="radio" name="typeppi" value="inf" <?= $selected_spy_inf ?>> PPI inférieur au spy
			<input type="radio" name="typeppi" value="sup" <?= $selected_spy_sup ?>> PPI supérieur au spy
			<input type="radio" name="typeppi" value="egal" <?= $selected_spy_egal ?>> PPI égal au spy
			<input type="radio" name="typeppi" value="nospy" <?= $selected_spy_nospy ?>> Pas de spy
			<input type="radio" name="typeppi" value="tous" <?= $selected_spy_tous ?>> Tous les articles
		</p> 
		<p class="column">
			<b>Marque :</b>
			<select name="marqueid">
				<option value="">Toutes</option>
				<?= $optmarques ?>
			</select>
		</p>
		<p class="column">
			<b>Type d'article :</b>
			<select name="typearticleid">
				<option value="">Tous</option>
				<?= $opttypearticle ?>
			</select>
		</p>
		<p class="column">
			<b>Articles actifs uniquement : oui</b>
		</p>
		<p class="column">
			<b>Spy compris entre le (inclus) :</b> <?= $from ?>
			<b>et le (inclus) :</b> <?= $to ?>
		</p>
		<input type="submit" class="skinned" value="Envoyer">
	  </form>
	  <br><br>nb : les vues des articles ne sont disponibles que pour les 6 derniers mois.
	  
	  <br><br>nb :<br>
		PPI inférieur au spy = au moins 1 prix du spy est supérieur au PPI sur les 6 dernieres mois => on peut donc augmenter notre PPI<br>
		PPI supérieur au spy = tous les prix du spy sont inférieurs au PPI sur les 6 derniers mois => <font color="red">on est donc hors la loi !!!</font><br>
		PPI égal au spy = prix maxi du spy sur les 6 derniers mois égal au PPI => tout est bien configuré :)<br>
		Pas de spy = aucun spy configuré sur ce produit. On ne devrait donc pas avoir de PPI de configuré<br>
	  
  </div>
  <div class="clear"></div>
<?php


if(isset($action) && $action!=""){

	$typetxt="(type : ";
	if($typearticleid!=""){
		$type_article=$DB_site2->query_first("SELECT * FROM typearticle WHERE typearticleid='".$typearticleid."' ");
		$typetxt.=$type_article[libelle];
		$typesql=" AND a.typearticleid='".$typearticleid."' ";
	} else {
		$typetxt.="tous";
		$typesql=" ";
	}
	$typetxt.=")";


	$marquetxt="";
	if($marqueid==""){
		$lib_marque_txt="Toutes marques";
		$marquesql="";
	} else {
		$lib_marque=$DB_site2->query_first("SELECT * FROM marque WHERE marqueid='".$marqueid."' ");
		$lib_marque_txt=$lib_marque[libelle];
		$marquesql=" AND am.marqueid = ".$marqueid;
	}
	
	if($typesrc==""){
		$lib_typesrc_txt="";
		$typesrcsql="";
		$order_sql=" ORDER BY a.artcode ASC";
	} else {
		if($typesrc=="vus"){
			$lib_typesrc_txt="(limité aux 100 articles les plus vus)";
			$select_sup=", sum(av.nb) as somme ";
			$typesrc_jointure=" INNER JOIN article_vu AS av ON(av.artid=a.artid) ";
			$typesrc_where=" AND av.date BETWEEN ".$dfrom->gettimestamp()." AND ".$dto->gettimestamp()." ";
			$order_sql=" ORDER BY somme DESC LIMIT 100 ";
		}
		if($typesrc=="vendus"){
			$lib_typesrc_txt="(limité aux 100 articles les plus vendus)";
			$select_sup=", sum(lf.qte) as qte ";
			$typesrc_jointure=" INNER JOIN lignefacture AS lf ON(lf.artid=a.artid) INNER JOIN facture AS f ON(f.facturid=lf.factureid)";
			$typesrc_where=" AND lf.prix>0 AND lf.lignefacturedissid=0 AND f.valide IN (1,5,7,8) AND f.provenance IN('', 'mobile') AND f.datefacture BETWEEN '".$dfrom->format("Y-m-d")."' AND '".$dto->format("Y-m-d")."' ";
			$order_sql=" ORDER BY qte DESC LIMIT 100 ";
		}
	}
$typesrc="";
	
  ?>
  <h1>Etude de PPI :</h1>
  <div class="commande_detail">
	<table align="center" width="100%">
		<tr class="flist">
			<td align="center">Ref.</td>
			<td align="center">Marque</td>
			<td align="center">Libellé</td>
			<td align="center">PPI actuel</td>
			<td align="center">Prix le + haut du spy</td>
			<td align="center">PDF</td>
			<td align="center">histo</td>
		</tr>
		<?php
		$i=0;
		
		$sql="	SELECT a.artid ".$select_sup.", am.marqueid AS marqueid, a.artcode, libelleFR, prixconseilleht FROM article AS a
				INNER JOIN article_marque AS am ON(a.artid=am.artid)
				".$typesrc_jointure."
				".$marketplace_sql."
				WHERE 1
				AND a.prix>0
				".$typesrc_where."
				".$typesql."
				".$marquesql."
				AND a.active IN ('1')
				GROUP BY artid
				".$order_sql." ";
		
		$articles=$DB_site2->query($sql);
		while($article=$DB_site2->fetch_array($articles)){
			$ppi=round($article['prixconseilleht'] * $tauxtva, 2);
			$marquelibelle=$marques[$article['marqueid']];
			$affiche=0;
			if($typeppi=="tous"){ // tous les articles
				$affiche=1;
			}
			$spy_plus_haut=$DB_site2->query_first("SELECT * FROM history WHERE artcode='".$article['artcode']."' AND timestamp BETWEEN ".$dfrom->gettimestamp()." AND ".$dto->gettimestamp()." ORDER BY price DESC, timestamp DESC LIMIT 1 ");
			if($spy_plus_haut['price']=="" && $typeppi=="nospy"){ // pas de spy
				$affiche=1;
			}
			if($spy_plus_haut['price']>$ppi && $typeppi=="inf"){ //  PPI inférieur au spy
				$affiche=1;
			}
			if($spy_plus_haut['price']!="" && $spy_plus_haut['price']<$ppi && $typeppi=="sup"){ //  PPI supérieur au spy
				$affiche=1;
			}
			if($spy_plus_haut['price']==$ppi && $typeppi=="egal"){ //  PPI égal au spy
				$affiche=1;
			}
			if($spy_plus_haut['price']==""){
				$spy_txt="--";
			} else {
				$spy_txt=number_format( $spy_plus_haut['price'], 2, ',', ' ')."  &euro;";
			}
			if($affiche==1){
				$pdf=$DB_site2->query_first("SELECT * FROM history_pdf WHERE price='".$spy_plus_haut['price']."' AND artid=".$article[artid]." AND site='".$spy_plus_haut['site']."' ORDER BY dateline DESC LIMIT 1 ");
				if($pdf['history_pdfid']!=""){
					$out_file="/configurations/".$host."/pdf/".$pdf['artid']."/".date("Ymd-His", $pdf['dateline'])."-".str_replace(" ","-", strtolower($pdf['site'])).".pdf";
					$lien_pdf="<a href=\"".$out_file."\" target=\"_blank\">PDF</a>";
				} else {
					$lien_pdf="--";
				}
				echo "<tr class=\"flist\">
						<td align=\"center\"><a href=\"ajout_produit.php?artid=".$article['artid']."&prec=modif\" target=\"_blank\">".$article['artcode']."</a></td>
						<td align=\"center\">".$marquelibelle."</td>
						<td align=\"left\">".$article['libelleFR']."</td>
						<td align=\"right\">".number_format( $ppi, 2, ',', ' ')." &euro;</td>
						<td align=\"right\">".$spy_txt."</td>
						<td align=\"center\">".$lien_pdf."</td>
						<td align=\"center\"><a href=\"ajout_produit_history.php?artid=".$article['artid']."&prec=modif\" target=\"_blank\">histo</a></td>
					</tr>";
			}
			$i++;
		}
		if($i==0){
			echo "<tr><td colspan=\"6\" align=\"center\">aucune article ne correspond aux critères rentrés</td></tr>";
		}
		?>
	</table>
  </div>
<?php
}

include "./footer.php";

?>