<?php
ini_set("display_errors",true);
require "admin_global.php";
include "./header.php";
include "./menu.php";
// utilisation de la librairie Taucharts (https://api.taucharts.com/)
?>
<script src="//cdn.jsdelivr.net/d3js/3.5.17/d3.min.js" charset="utf-8"></script>
<script src="//cdn.jsdelivr.net/npm/taucharts@1/build/production/tauCharts.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/taucharts@1/build/production/tauCharts.min.css">
<script>
$jQuery(document).ready(function() {
	var div = document.getElementById('step-after');
	var chart = new tauCharts.Chart({
		data: getData(),
		x: 'date',
		y: 'price',
		type: 'line',
		color: 'site',
		guide: {
			x: {label: 'jour (YYYY-mm-dd)', padding: 20},
			y: {label: 'prix (€ TTC)', padding: 20, nice: false},
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
	function getData() {
<?php

$artid=5093;

echo "return [	";
$virgule="";
$sites=$DB_site2->query("	SELECT DISTINCT(site) FROM history_pdf WHERE artid='".$artid."' ");
while($site=$DB_site2->fetch_array($sites)){
	$pdfs=$DB_site2->query("	SELECT * FROM history_pdf WHERE artid='".$artid."' AND site='".$site[site]."' ORDER BY dateline ASC ");
	while($pdf=$DB_site2->fetch_array($pdfs)){
		$month=date("n", $pdf[dateline])-1;
		echo $virgule."{site: '".$site[site]."', date: new Date(".date("Y", $pdf[dateline]).", ".$month.", ".date("j", $pdf[dateline]).", 0, 0, 0, 0),   price: ".$pdf[price]." }";
		$virgule=",";
	}
}
echo "	];	";
?>
	}
});
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
<div id="container"><div id="step-after" class="chart"><label>Prix des concurrents</label></div></div>
Télécharger les screenshots : <select id="select_site"><option value="">Sélectionnez</option>
<?php
$liste_site="";
$div_site="";
$sites=$DB_site2->query("	SELECT DISTINCT(site) FROM history_pdf WHERE artid='".$artid."' ");
while($site=$DB_site2->fetch_array($sites)){
	echo "<option value=\"".str_replace(" ","_",strtolower($site[site]))."\">".$site[site]."</option>".
	$liste_site.="\$jQuery(\"#".str_replace(" ","_",strtolower($site[site]))."\").hide();";
	$div_site.="<div id=\"".str_replace(" ","_",strtolower($site[site]))."\" style=\"display:none;\">Screenshots de ".$site[site]." :<br><table border=\"1\" width=\"50%\">";
	$div_site.="<tr><td align=\"center\">Date</td><td align=\"center\">Prix</td><td align=\"center\">PDF</td></tr>";
	
	$pdfs=$DB_site2->query("	SELECT * FROM history_pdf WHERE artid='".$artid."' AND site='".$site[site]."' ORDER BY dateline ASC ");
	while($pdf=$DB_site2->fetch_array($pdfs)){
		if($pdf[generated]=='1'){
			$out_file="/configurations/".$host."/pdf/".$pdf[artid]."/".date("Ymd-His", $pdf['dateline'])."-".str_replace(" ","-", strtolower($pdf['site'])).".pdf";
			$lien="<a href=\"".$out_file."\" target=\"_blank\">PDF</a>";
		} else {
			$lien="-- en cours --";
		}
		$div_site.="<tr><td align=\"center\">".date("d/m/Y", $pdf[dateline])."</td><td align=\"right\">".$pdf[price]." &euro;</td><td align=\"center\">".$lien."</td></tr>";
	}
	$div_site.="</table></div>";
}
echo "</select><br>";
echo $div_site;
?>
<script>
$jQuery(document).ready(function() {
	$jQuery("#select_site").change(function() {
		var todisplay=$jQuery("#select_site").val();
		<?php echo $liste_site; ?>
		$jQuery("#"+todisplay).show();
		console.log( "Handler for .change() called." );
	});
});
</script>
<?php
$pdf_max=$DB_site2->query_first("	SELECT * FROM history_pdf WHERE artid='".$artid."' ORDER BY price DESC LIMIT 1 ");
$pdf_max_date=date("d/m/Y", $pdf_max[dateline]);
if($pdf_max[generated]=='1'){
	$out_file="/configurations/".$host."/pdf/".$pdf_max[artid]."/".date("Ymd-His", $pdf_max['dateline'])."-".str_replace(" ","-", strtolower($pdf_max['site'])).".pdf";
	$lien="<a href=\"".$out_file."\" target=\"_blank\">Télécharger le PDF</a>";
} else {
	$lien=" Téléchargement du PDF en cours !";
}
echo "Prix le plus haut : ".$pdf_max[price]." € sur le site ".$pdf_max[site]." le ".$pdf_max_date.". ".$lien."<br>";



include "./footer.php";
?>
