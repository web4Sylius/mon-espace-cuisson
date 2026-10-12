<?php

ini_set("display_errors","on");

require "admin_global.php";

include("fonctions.php");

setcookie("hostname",$host,time()+3600,"/");


include "./header.php";
include "./menu.php";

$date_de_debut=1522879200; // les historiques ont été créé le 5 avril 2018. On affiche tout à partir de cette date.
$taux_tva=1.2; // 20 %


$artcodes=$DB_site2->query("SELECT DISTINCT(artcode) FROM history WHERE timestamp>".$date_de_debut." ");
while($artcode=$DB_site2->fetch_array($artcodes)){
	$article=$DB_site2->query_first("SELECT artid FROM article WHERE artcode='".$artcode[artcode]."' ");
	$exist_pdf=$DB_site2->query_first("SELECT COUNT(*) FROM history_pdf WHERE artid='".$article[artid]."' ");
	if($exist_pdf[0]==0){
		echo "il manque une ligne de PDF pour l'article ".$artcode[artcode]."<br>";
	}
}


$contenu_onglet .= "<h3>Concurrents</h3>";
$contenu_onglet .= "<a href=\"ajout_produit.php?artid=".$artid."&prec=modif\">Retour à la fiche article</a><br><br>";
$article=$DB_site2->query_first("SELECT * FROM article  WHERE artid='".$artid."' ");
$marque=$DB_site2->query_first("SELECT * FROM article_marque INNER JOIN marque USING(marqueid) WHERE article_marque.artid='".$artid."' ");

$contenu_onglet .= "Ref Acilyon : ".$article['artcode']."<br>Ref fabriquant : ".$article['artcode_eurocave']."<br>Marque : ".$marque['libelle']."<br>Libellé : ".$article['libelleFR']."<br>";
$contenu_onglet .= "Prix MCAV actuel : ".number_format($article[prix], 2, ',', ' ')." &euro;<br>";
$contenu_onglet .= "PPI actuel : ".number_format(($article[prixconseilleht]*$taux_tva), 2, ',', ' ')." &euro;<br><br>";

$sites_count=$DB_site2->query_first("	SELECT COUNT(*) FROM history_pdf WHERE artid='".$artid."' ");

$ajd=mktime(0,0,0);

if($sites_count[0]>0){

	$contenu_onglet .= "<script src=\"//cdn.jsdelivr.net/d3js/3.5.17/d3.min.js\" charset=\"utf-8\"></script>
	<script src=\"//cdn.jsdelivr.net/npm/taucharts@1/build/production/tauCharts.min.js\" type=\"text/javascript\"></script>
	<link rel=\"stylesheet\" type=\"text/css\" href=\"//cdn.jsdelivr.net/npm/taucharts@1/build/production/tauCharts.min.css\">
	<script>
	

	jQuery.noConflict()(function(){ 
	
	jQuery(document).ready(function() {
		var div = document.getElementById('step-after');
		var chart = new tauCharts.Chart({
			data: getData(),
			x: 'date',
			y: 'price',
			type: 'line',
			color: 'site',
			guide: {
				x: {label: 'jour (YYYY-mm-dd)', padding: 20},
				y: {label: 'prix (€ TTC)', padding: 20, nice: false, scale: 'linear'},
				padding: {b: 70, l: 70, t: 10, r: 10},
				showGridLines: 'xy',
				interpolate: 'step-after',
				showAnchors: 'always'
			},
			settings: {
				fitModel: 'entire-view'
			},
			plugins: [
				tauCharts.api.plugins.get('tooltip')(),
				tauCharts.api.plugins.get('legend')()
			]
		});
		chart.renderTo(div);

		// (year, month (+1), day, hours, minutes, seconds, milliseconds) => le mois commence à 0 !!!
		function getData() {";

	//$artid=5093;

	$contenu_onglet .= "return [	";
	$virgule="";

	$prix_mcavs=$DB_site2->query("	SELECT * FROM article_history WHERE artid='".$artid."' AND `key`='prix' AND `date`>'".$date_de_debut."' ");
	if($DB_site2->num_rows()>0){
		$last_price=0;
		while($prix_mcav=$DB_site2->fetch_array($prix_mcavs)){
			$prix=unserialize($prix_mcav['value']);
			$prix_1=substr($prix_mcav['value'], 19);
			$prix_pos=strpos($prix_1, "\\");
			$prix=substr($prix_1,0, $prix_pos);
			$month=date("n", $prix_mcav['date'])-1;
			$contenu_onglet .= $virgule."{site: 'MCAV', date: new Date(".date("Y", $prix_mcav['date']).", ".$month.", ".date("j", $prix_mcav['date']).", 0, 0, 0, 0),   price: ".$prix." }";
			$last_price=$pdf['dateline'];
			$virgule=",";
		}
		if($last_price<$ajd){
			$month=date("n")-1;
			$contenu_onglet .= $virgule."{site: 'MCAV', date: new Date(".date("Y").", ".$month.", ".date("j").", 0, 0, 0, 0),   price: ".round($article['prix'])." }";
		}
	} else {
		$month=date("n", $date_de_debut)-1;
		$contenu_onglet .= $virgule."{site: 'MCAV', date: new Date(".date("Y", $date_de_debut).", ".$month.", ".date("j", $date_de_debut).", 0, 0, 0, 0),   price: ".round($article['prix'])." }";
		$virgule=",";
		$month=date("n")-1;
		$contenu_onglet .= $virgule."{site: 'MCAV', date: new Date(".date("Y").", ".$month.", ".date("j").", 0, 0, 0, 0),   price: ".round($article['prix'])." }";
	}
	
	$sites=$DB_site2->query("	SELECT DISTINCT(site) FROM history_pdf WHERE artid='".$artid."' ");
	while($site=$DB_site2->fetch_array($sites)){
		$pdfs=$DB_site2->query("	SELECT * FROM history_pdf WHERE artid='".$artid."' AND site='".$site[site]."' ORDER BY dateline ASC ");
		$last_date=0;
		$last_price=0;
		while($pdf=$DB_site2->fetch_array($pdfs)){
			$month=date("n", $pdf[dateline])-1;
			$contenu_onglet .= $virgule."{site: '".$site['site']."', date: new Date(".date("Y", $pdf['dateline']).", ".$month.", ".date("j", $pdf['dateline']).", 0, 0, 0, 0),   price: ".round($pdf['price'])." }";
			$last_date=$pdf['dateline'];
			$last_price=$pdf['price'];
			$virgule=",";
		}
		if($last_price<$ajd){
			$month=date("n")-1;
			$contenu_onglet .= $virgule."{site: '".$site['site']."', date: new Date(".date("Y").", ".$month.", ".date("j").", 0, 0, 0, 0),   price: ".round($last_price)." }";
		}
	}
	$contenu_onglet .= "	];

		}
	});
	
	
	
	}); // fin noconflict
	</script>
	<style>
	html,
	body,
	#container {
	  width: 100%;
	  height: 300px;
	  margin: 0;
	  padding: 0;
	  background: #f3f5f6;
	}
	.chart {
	  height: 100%;
	  position: relative;
	}
	.chart >label {
	  position: absolute;
	  top: 0;
	  right: 3em;
	}
	</style>
	<div id=\"container\"><div id=\"step-after\" class=\"chart\"><label>Prix des concurrents</label></div></div>
	Télécharger les screenshots : <select id=\"select_site\"><option value=\"\">Sélectionnez</option>";

	$liste_site="";
	$div_site="";
	$sites=$DB_site2->query("	SELECT DISTINCT(site) FROM history_pdf WHERE artid='".$artid."' ");
	while($site=$DB_site2->fetch_array($sites)){
		$contenu_onglet .= "<option value=\"".str_replace(" ","_",strtolower($site[site]))."\">".$site[site]."</option>".
		$liste_site.="jQuery(\"#".str_replace(" ","_",strtolower($site[site]))."\").hide();";
		$div_site.="<div id=\"".str_replace(" ","_",strtolower($site[site]))."\" style=\"display:none;\">Screenshots de ".$site[site]." :<br><table border=\"1\" width=\"50%\">";
		$div_site.="<tr><td align=\"center\">Date</td><td align=\"center\">Prix</td><td align=\"center\">URL</td><td align=\"center\">PDF</td></tr>";
		$pdfs=$DB_site2->query("	SELECT * FROM history_pdf WHERE artid='".$artid."' AND site='".$site[site]."' ORDER BY dateline ASC ");
		while($pdf=$DB_site2->fetch_array($pdfs)){
			if($pdf[generated]=='1'){
				$out_file="/configurations/".$host."/pdf/".$pdf[artid]."/".date("Ymd-His", $pdf['dateline'])."-".str_replace(" ","-", strtolower($pdf['site'])).".pdf";
				$lien="<a href=\"".$out_file."\" target=\"_blank\">PDF</a>";
			} elseif($pdf[generated]=='2'){
				$lien="-- echec --";
			} else {
				$lien="-- en cours --";
			}
			$url="<a href=\"".$pdf['url']."\" target=\"_blank\" rel=\"noreferrer\">URL</a>";
			$div_site.="<tr><td align=\"center\">".date("d/m/Y", $pdf[dateline])."</td><td align=\"right\">".$pdf[price]." &euro;</td><td align=\"center\">".$url."</td><td align=\"center\">".$lien."</td></tr>";
		}
		$div_site.="</table></div>";
	}
	
	$mcav=$DB_site2->query_first("	SELECT COUNT(*) FROM history_mcav_pdf WHERE artid='".$artid."' ");
	if($mcav[0]>0){
		$contenu_onglet .= "<option value=\"mcav\">MCAV</option>".
		$liste_site.="jQuery(\"#mcav\").hide();";
		$div_site.="<div id=\"mcav\" style=\"display:none;\">Screenshots de MCAV :<br><table border=\"1\" width=\"50%\">";
		$div_site.="<tr><td align=\"center\">Date</td><td align=\"center\">Prix</td><td align=\"center\">PDF</td></tr>";
		$pdfs=$DB_site2->query("	SELECT * FROM history_mcav_pdf WHERE artid='".$artid."' ORDER BY dateline ASC ");
		while($pdf=$DB_site2->fetch_array($pdfs)){
			if($pdf[generated]=='1'){
				$out_file="/configurations/".$host."/pdf/".$pdf[artid]."/".date("Ymd-His", $pdf['dateline'])."-mcav.pdf";
				$lien="<a href=\"".$out_file."\" target=\"_blank\">PDF</a>";
			} elseif($pdf[generated]=='2'){
				$lien="-- echec --";
			} else {
				$lien="-- en cours --";
			}
			$div_site.="<tr><td align=\"center\">".date("d/m/Y", $pdf[dateline])."</td><td align=\"right\">".$pdf[price]." &euro;</td><td align=\"center\">".$lien."</td></tr>";
		}
		$div_site.="</table></div>";
	}
	
	
	$contenu_onglet .= "</select><br>";
	$contenu_onglet .= $div_site;
	$contenu_onglet .= "
	<script>
	jQuery(document).ready(function() {
		jQuery(\"#select_site\").change(function() {
			var todisplay=jQuery(\"#select_site\").val();
			". $liste_site ."
			jQuery(\"#\"+todisplay).show();
		});
	});
	</script>";

	$pdf_max=$DB_site2->query_first("	SELECT * FROM history_pdf WHERE artid='".$artid."' ORDER BY price DESC LIMIT 1 ");
	$pdf_max_date=date("d/m/Y", $pdf_max[dateline]);
	if($pdf_max[generated]=='1'){
		$out_file="/configurations/".$host."/pdf/".$pdf_max[artid]."/".date("Ymd-His", $pdf_max['dateline'])."-".str_replace(" ","-", strtolower($pdf_max['site'])).".pdf";
		$lien="<a href=\"".$out_file."\" target=\"_blank\">Télécharger le PDF</a>";
	} elseif($pdf_max[generated]=='2'){
		$lien=" Echev du téléchargement du PDF !";
	} else {
		$lien=" Téléchargement du PDF en cours !";
	}
	$contenu_onglet .= "Prix le plus haut : ".$pdf_max[price]." € sur le site ".$pdf_max[site]." le ".$pdf_max_date.". ".$lien."<br>";
} else {
	$contenu_onglet .= "Aucun prix de concurrent dans le spy.";
}

echo $contenu_onglet;

include "./footer.php";
?>