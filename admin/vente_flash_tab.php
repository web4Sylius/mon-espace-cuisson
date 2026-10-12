<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

// Ajout
$listeArt=array();
$vfArticle = $DB_site2->query("SELECT * FROM vente_flash_article");
while($avf = $DB_site2->fetch_array($vfArticle)){
	$listeArt[$avf['artcode']]=$avf;
}

if(isset($_POST['venteflashCheck'])){
	foreach($_POST['venteflashCheck'] as $key => $value) {
		$get = "prix_".$value;
		if($listeArt[$value] != ''){
			$DB_site2->query("UPDATE vente_flash_article SET prix=".$_POST[$get]." WHERE artcode='".$value."'");
		}else
			$DB_site2->query("INSERT INTO vente_flash_article SET vfid=1, artcode='".$value."', prix=".$_POST[$get]);
	}
	print "<div class=\"alert-box success\" id=\"alert-box\"><span>&nbsp;&nbsp;Succès : </span>Article(s) ajouté(s) à la vente flash ! <a href=\"javascript:void(0);\" onclick=\"notif_close();\" class=\"notif-close\">&times;</a></div>";
}


// Filtres
$rq = $DB_site2->query("SELECT * FROM marque ORDER BY libelle");
while($m = $DB_site2->fetch_array($rq)){
    $marques[$m['marqueid']] = $m['libelle'];
}
asort($marques);

$rq = $DB_site2->query("SELECT * FROM typearticle ORDER BY libelle");
while($t = $DB_site2->fetch_array($rq)){
    $types[$t['typearticleid']] = $t['libelle'];
}
asort($types);

$optmarq = "<option value=\"0\">Toutes les marques</option>
			<option disabled>─ ─ ─ ─ ─ ─</option>";
$opttype = "<option value=\"0\">Tous types</option>
			<option disabled>─ ─ ─ ─ ─ ─</option>";

foreach($marques as $mid => $libelle) 
	$optmarq .= "<option value=\"$mid\"".($_POST['marqueid']==$mid?" selected":"").">".$libelle."</option>";

foreach($types as $tid => $libelle) 
	$opttype .= "<option value=\"$tid\"".($_POST['typeid']==$tid?" selected":"").">".$libelle."</option>";

?>

<h1>Tableau Vente Flash</h1>

<div class="vf_search">
	<div style="padding:10px;">
		<form name="srcvf" method="POST" action="vente_flash_tab.php" style="display: inline;">
			<strong>Tableau :</strong>
			<select name="src_tab" id="src_tab" onchange="selectTab(this.value);">
				<option value="0"></option>
				<option value="stock">Stock</option>
				<option value="vus">Vus</option>
				<option value="vendus">Vendus</option>
			</select>
			<br><br>
			<div id="src_stock">
				<strong id="src_marq">Marque :</strong>
				<select name="src_marq" id="src_marq"><?php echo $optmarq; ?></select>
				<div id="src_type" class="separateur_vertical"></div>
				<strong id="src_type">Type article :</strong>
				<select name="src_type" id="src_type"><?php echo $opttype; ?></select>
			</div>
			<br>
			<center>
				<a href="javascript:void(0)" class="search_check" onclick="validTab();"><i class="fa fa-check" aria-hidden="true"></i> Valider</a>
				<a href="vente_flash_tab.php" class="search_times"><i class="fa fa-times" aria-hidden="true"></i> Réinitialiser</a>
			</center>
		</form>
	</div>
</div>

<div id="tabVF"></div>

<script type='text/javascript'>
toHtmlNumericInput('num');
function toHtmlNumericInput(inputElementId, useCommaAsDecimalSeparator) {
	var textbox = document.getElementById(inputElementId);
	textbox.addEventListener("keydown", function _OnNumericInputKeyDown(e) {
		var key = e.which || e.keyCode; // http://keycode.info/
		if (!e.shiftKey && !e.altKey && !e.ctrlKey && key >= 65 && key <= 90 || key == 32) {
			e.preventDefault();
			return false;
		}
		if (!e.shiftKey && !e.altKey && !e.ctrlKey && key >= 48 && key <= 57 || key >= 96 && key <= 105 || (e.keyCode == 65 && e.ctrlKey === true) || (key == 67 && e.ctrlKey === true) || (key == 88 && e.ctrlKey === true) || (key >= 35 && key <= 39) || key == 8 || key == 9 || key == 13 || key == 46 || key == 45) {
			return true;
		}
		var v = this.value;
		if (key == 109 || key == 189) {
			if (v[0] === '-') {
				return false;
			}
		}
		if (!e.shiftKey && !e.altKey && !e.ctrlKey && key == 190 || key == 188 || key == 110) {
			if (/[\.,]/.test(v)) {
				return false;
			}
		}
	});
	textbox.addEventListener("keyup", function _OnNumericInputKeyUp(e) {
		var v = this.value;
		if(false) {
		} else if (v) {
			v = (v[0] === '-' ? '-' : '') + (useCommaAsDecimalSeparator ? v.replace(/[^0-9\,]/g, '') : v.replace(/[^0-9\.]/g, ''));
			if(useCommaAsDecimalSeparator){
				v = v.replace(/,(?=(.*),)+/g, '');
			} else {
				v = v.replace(/\.(?=(.*)\.)+/g, '');
			}
			this.value = v; // update value only if we changed it
		}
	});
}
    	
function calcmarge(id, prix, totalachat, frais, tva){
	var totalvente = parseFloat(prix)/(1+tva/100)+frais;
	var marge = totalvente - totalachat;
	var margeap = 100*marge/totalvente;
	document.getElementById('marge_'+id).innerHTML=(!isNaN(marge) ? margeap.toFixed(1) + "%" : "");
}

function calcremise(id, prix, ppi){
	var total = (parseFloat(prix)*100)/ppi;
	var remise = 100-total;
	document.getElementById('remise_'+id).innerHTML=(!isNaN(total) ? remise.toFixed(1) +"%" : "");
}

var count = 0;

//Change la couleur si checked
function changeCheck(id){
	var split = id.split("_");
	var sp = document.getElementById("tr_"+split[1]);

	if(document.getElementById(id).checked){
    	sp.style.backgroundColor  = "#00d796";
    	count++;
    	document.getElementById('selec').innerHTML = count;
    }else{
    	sp.style.backgroundColor  = "";
    	count--;
    	document.getElementById('selec').innerHTML = count;
    }
}

function chckPrix(value, i){
	var val = document.getElementById(value).innerHTML;
	var sp = document.getElementById("tr_"+i);
	if(val == ''){
		alert('Veuillez indiquer un prix vente flash !');
		document.getElementById('checkvf_'+i).checked = false;
		sp.style.backgroundColor  = "";
		count--;
		document.getElementById('selec').innerHTML = count;
	}
		
}

function selectTab(tab){
	switch(tab){
		case 'stock':
			document.getElementById('src_stock').style.display = "none";
		break;

		case 'vus':
		case 'vendus':
		case '0':
			document.getElementById('src_stock').style.display = "";
		break;
	}
}

function validTab(){
	if(document.getElementsByName('src_tab')[0].value == 0)
		alert("Veuillez sélectionner un tableau");
	else{
		var marq = '';
		var type = '';

		if(document.getElementsByName('src_marq')[0].value != 0)
			marq = '&marq='+document.getElementsByName('src_marq')[0].value;
		if(document.getElementsByName('src_type')[0].value != 0)
			type = '&type='+document.getElementsByName('src_type')[0].value

		document.getElementById('tabVF').innerHTML = "<img src='./images/loader2.gif'>";
		var action = document.getElementsByName('src_tab')[0].value;
		var url = "./includes/vente_flash/tableau.php?action="+action+marq+type;
		ajaxrequest(url, validTab_cb);
	}
}

function validTab_cb(httprequest){
	document.getElementById('tabVF').innerHTML = httprequest.responseText;
}

function notif_close(){
	document.getElementById('alert-box').style.display = "none";
}

function exportTab(tab){
	var url = "./includes/vente_flash/tableau.php?action="+tab+"&export=1";
	ajaxrequest(url, validTab_cb);
}
</script>

<?php

function phpmarge($prix, $totalachat, $frais, $tva){
	$totalvente = $prix/(1+$tva/100)+$frais;
	$marge 		= $totalvente-$totalachat;
	$margeap 	= round(100*$marge/$totalvente,1);
	return $margeap."%";
}

function phpremise($prix, $ppi){
	$total 	= ($prix*100)/$ppi;
	$remise = round(100-$total,1);
	return $remise."%";
}