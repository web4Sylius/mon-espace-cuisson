<?php
set_time_limit(600);
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(SITE_PATH."/class/facture.class.php");
require_once(BACK_PATH."/export/xlexport.php");
require_once(BACK_PATH."/adv/adv.class.php");

$debug_page=false; // mode débuggage ...

//on recupere toutes les factures depuis la date choisie, commandes de rang1 (si sav juste premiere commande), + cmd MP
if(!isset($_POST['du']) && !isset($_POST['au'])){
	include_once(BACK_PATH."/header.php");
	include_once(BACK_PATH."/menu.php");
	?>
	<h2>Recap des factures</h2>
	<form method="post" action="#" name="daterecap" id="formu" target="_blank">
	  <input type="hidden" name="telecharger" value="1">
	  <p>
		Du <input type="date" name="du">
		au<input type="date" name="au"><br>
		&nbsp; <input type="checkbox" name="expedie" value="1">uniquement les commandes expédiées<br>
		&nbsp; <input type="checkbox" name="nonanalyse" value="1">uniquement les commandes non-analysées<br>
		&nbsp; <input type="checkbox" name="nonfacture" value="1">uniquement les commandes sans non-facturé<br>
	  </p> 
	  <p>
		<input type="submit" value="télécharger" onclick="document.getElementById('formu').elements.namedItem('telecharger').value='1';document.getElementById('formu').submit();">
		<input type="button" value="visualiser" onclick="document.getElementById('formu').elements.namedItem('telecharger').value='0';document.getElementById('formu').submit();">
	  </p>
	</form>
	<?php	
}

function couleur_marge($pct) {
	$pct_txt=number_format($pct, 1, ',', ' ');
	if($pct<=0){
		$return="<font color=\"#ff00ff\"><b>".$pct_txt." %</b></font>";
	}
	if($pct>0 && $pct<=20){
		$return="<font color=\"#ff0000\"><b>".$pct_txt." %</b></font>";
	}
	if($pct>20 && $pct<=25){
		$return="<font color=\"#ffaa00\"><b>".$pct_txt." %</b></font>";
	}
	if($pct>25 && $pct<=30){
		$return="<font color=\"#22CC22\"><b>".$pct_txt." %</b></font>";
	}
	if($pct>30 && $pct<=40){
		$return="<font color=\"#339933\"><b>".$pct_txt." %</b></font>";
	}
	if($pct>40){
		$return="<font color=\"#3333ff\"><b>".$pct_txt." %</b></font>";
	}
	return $return;
}


if(isset($_POST['du']) && isset($_POST['au'])){
	$du = mysqli_real_escape_string($DB_site2->link_id,$_POST['du']);
	$au = mysqli_real_escape_string($DB_site2->link_id,$_POST['au']);
	if($_POST['expedie']==1){
		$valide="5,8";
	} else {
		$valide="1,5,7,8";
	}
	
	if($_POST['nonanalyse']==1){
		$sqlnonanalyse=" AND (SELECT COUNT(*) FROM facture_rentabilite WHERE facturid=facture.facturid)=0 ";
	} else {
		$sqlnonanalyse="";
	}
	$tableau_total=array();
	$sql = "SELECT facturid, datefacture, numerofacture,provenance, commercial FROM facture
			WHERE valide IN(".$valide.") AND provenance NOT IN ('sav','avoir') AND datefacture BETWEEN '".$du."' and '".$au."'
			".$sqlnonanalyse." ";
	$factures = $DB_site2->query($sql);
	$csv = new xlexport("csv","exportfacture");
	$head = array("Date commande", "Num commande","SAV","origine","type cde","Num facture","Total EUR HT","Marge previsonnelle EUR HT","Marge previs.%","Marge Reelle EUR HT", "Marge Reelle %","Total Achat HT", "Non facture"); // , "marge prévis. devis EUR HT","marge devis  %");
	if($debug_page){
		$head[] = ("details");
	}
	$csv->addrow($head,true);
	while($facture = $DB_site2->fetch_array($factures)){
		$facturid = $facture['facturid'];
		$ligne=get_totaux_facture($facturid, $DB_site2);
		//on veut afficher si il y a un sav pour cette commande, le cas échéant on met son statut
		$sav = $DB_site2->query_first("SELECT id,fin, demande_cloture FROM sav WHERE facturid ='".$facturid."'");
		if($sav != false){
			if($sav['fin'] != '0'){
          		$savstate = "Clot";
     		} else if($sav['demande_cloture'] > 0){
          		$savstate = "Demande de cloture";
     		} else {
        		$savstate = 'En cours';
     		}
		}else{
			$savstate = "Aucun";
		}

		$datefacture = $facture['datefacture'];
		if($facture['numerofacture']=="556500"){
			$numFacture="-";
		} else {
			$numFacture = $facture['numerofacture'];
		}
		$typeCde = $facture['commercial'];
		if($_POST['telecharger']!="1"){
			$factureid="<a href=\"facture.php?action=editer&facturid=".$facturid."\" target=\"_blank\">".$facturid."</a>";
			$sql = "SELECT note FROM facture_rentabilite WHERE facturid ='".$facturid."' ";
			$rentabilite = $DB_site2->query_first($sql);
			if($rentabilite[note]!=""){
				$factureid.=" <a href=\"facture.php?action=editer&facturid=".$facturid."\" target=\"_blank\" title=\"".utf8_decode($rentabilite[note])."\">XXX</a>";
			}
			$ligne['total_marge_previ_pct']=couleur_marge(floatval($ligne['total_marge_previ_pct']));
			$ligne['total_marge_reel_pct']=couleur_marge(floatval($ligne['total_marge_reel_pct']));
		} else {
			$factureid=$facturid;
			$ligne['total_marge_previ_pct'].=" %";
			$ligne['total_marge_reel_pct'].=" %";
		}
		$cols = array(	$datefacture,
						$factureid,
						$savstate,
						$ligne['provenance'],
						$typeCde,
						$numFacture,
						$ligne['total_vente_ht'],
						$ligne['total_marge_previ'],
						$ligne['total_marge_previ_pct'],
						$ligne['total_marge_reel'],
						$ligne['total_marge_reel_pct'],
						$ligne['total_achat_ht'],
						$ligne['total_nonfacture_ht']);
		if($debug_page){
			$cols[] = ($ligne['details']);
		}
		if($_POST['nonfacture']==1 && $ligne['total_nonfacture_ht']>0){
			// on ne prend pas cette facture
		} else {
			$tableau_total[]=$cols;
			$csv->addrow($cols,true);
		}
	}
	if($_POST['telecharger']=="1"){
		// on force doawnload le fichier CSV
		$csv->export();
	} else {
		// on affiche directement le tableau ...
		$echototal="";
		$echototal.="<html><head>
		<script src=\"//code.jquery.com/jquery-1.12.4.js\"></script>
		<script src=\"//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js\"></script>
		<script src=\"//cdn.datatables.net/fixedcolumns/3.2.4/js/dataTables.fixedColumns.min.js\"></script>
		</head>
		<body>
		<style>
		.odd, .odd td, .odd th{
			background-color:#DDD;
		}
		.even, .even td, .even th{
			background-color:#FFF;
		}
		td, th {
		   border: solid 1px black;
		   padding: 0px;
		   font-weight: normal;
		   font-size: 11px;
		   box-sizing: initial;
		}
		.sorting, .sorting_asc, .sorting_desc{
			background-color:#FFF;
		}
		.container {
		   position: relative;
		   overflow: hidden;
		}
		table {
			border-collapse: collapse;
		}
		body{
			font-family: verdana;
			font-size: 10px;
		}
		div.dataTables_wrapper {
			margin: 0 auto;
		}
		.input_marge_pct{
			width: 40px;
		}
		</style>
		<style src=\"//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css\"></style>
		<style src=\"//cdn.datatables.net/fixedcolumns/3.2.4/css/fixedColumns.dataTables.min.css\"></style>
		<script>
		function precisionRound(number, precision) {
			var factor = Math.pow(10, precision);
			return Math.round(number * factor) / factor;
		}
		//$=$jQuery;
		$(document).ready(function() {
			var table = $('#tableau').DataTable( {
				scrollY:        \"800px\",
				scrollX:        true,
				scrollCollapse: false,
				paging:         false,
				aaSorting: [],
				initComplete: function(settings, json) {
					// $('.sorting').width($('.sorting').width()+10);
					// console.log('complete');
				},
				drawCallback: function( settings ) {
					// console.log('DataTables has redrawn the table');
					// $(\".dataTables_scrollHeadInner\").css({\"width\":\"100%\"});
					// $(\".table\").css({\"width\":\"100%\"});
				},
				fixedColumns:   {
					leftColumns: 2
				}
			} );
		} );
		</script>";
		
		$headtable="
			<table id=\"tableau\" class=\"stripe row-border order-column\" cellspacing=\"0\" width=\"100%\" border=\"0\">
				<thead>
				<tr>";
		foreach ($head as $key => $val){
			$headtable.="<th>".$val."</th>";
		}
		$headtable.="</tr></thead><tbody>";
		$echototal.=$headtable;
		foreach ($tableau_total as $key => $val){
			$echoligne= "<tr>";
			foreach ($val as $key2 => $val2){
				$echoligne.= "<td>".$val2."</td>";
			}
			$echoligne.= "</tr>";
			$echototal.=$echoligne;
		}
		$echototal.="</tbody></table>";
		$echototal.= "</body></html>";
		echo $echototal;
	}
}

function savlinkids($parent,$links){
      $return=array($parent);
      IF($links==false) return $return;
      foreach($links as $link=>$a){
		$return=array_merge($return,savlinkids($link,$a));
      }
      return $return;
}
function savlink($linkid,$DB_site2){
      $return=array();
      if($sens=="to" || is_null($sens)){
        $r=$DB_site2->query("SELECT facturid FROM facture WHERE provenance IN ('sav','avoir') AND provenanceid=".intval($linkid));

        while($savto=$DB_site2->fetch_array($r)){
            $return[$savto[0]]=savlink($savto[0],$DB_site2);
        //$links=savlink($savto[0],"to");print "<br>[$sens $linkid]";var_dump($links);
        //foreach($links as $linkid2=>$a){ $return[$linkid2]['to']=$a['to'];$return[$linkid2]['from']=$linkid;}
        }
        if($DB_site2->num_rows($r)==0) $return=false;

      }
      return $return;
}
function getparentsav($linkid,$DB_site2){
  $savfrom=$DB_site2->query_first("SELECT provenanceid FROM facture WHERE provenance IN ('sav','avoir') AND facturid=".intval($linkid));
  if($savfrom[0]!="")
      return getparentsav($savfrom[0],$DB_site2);
  else
      return $linkid;
}



?>