<?php

ini_set("display_errors","1");
error_reporting(E_ALL);

require "admin_global.php";
include "./header.php";
include "./menu.php";


$annee = isset($_GET['annee']) ? intval($_GET['annee']) : date("Y");

if($_POST['action']=="modify"){
	foreach ($_POST as $key => $value) {
		$my_key=substr($key,0,4);
		if($my_key=="obj_"){
			$explode=explode("_", $key);
			$username=$explode[1];
			$mois=$explode[2];
			if($mois!=""){
				$DB_site2->query("REPLACE INTO objectif_commercial (username, annee, mois, objectif_ca) VALUES ('".$username."', '".$_POST[annee]."', '".$mois."', '".$value."') ");
			}
		}
	}
	
	echo "<h1>modifications enregistrées avec succès</h1><br><br><a href=\"objectifs_commerciaux.php\">retour</a>";
} else {
	?>
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
	   border-collapse: collapse;
	   text-align:center;
	}
	.fixedheight{
		height:35px;
	}
	.fixedheight2{
		height:50px;
	}
	table {
		border-collapse: collapse;
	}
	input[type="text"] {
		width: 70px;
	}
	</style>
	<?php
		$liste_commerciaux=array();
		$liste_commerciaux[]=array('username' => 'direct', 'jours' => 'francs');
		$commercials=$DB_site2->query(" SELECT username FROM parametresgeneraux WHERE commissions ='1' AND actif='1' ORDER BY username ASC ");
		while($commercial=$DB_site2->fetch_array($commercials)){
			$liste_commerciaux[]=array('username' => $commercial[username], 'jours' => 'ouvres');
        }
        
        $optannee="";
        foreach(range(date("Y")-1,date("Y")+1) as $y)
            $optannee.="<option value=\"".$y."\"".($y==$annee?" selected":"").">".$y."</option>";
        
		echo "<h1>Modifier les objectifs commerciaux <select name=\"annee\" onchange=\"document.location='objectifs_commerciaux.php?annee='+this.value\">".$optannee."</select></h1>
			<center>
				<form method=\"post\" action=\"objectifs_commerciaux.php\">
					<input type=\"hidden\" name=\"action\" value=\"modify\">
					<input type=\"hidden\" name=\"annee\" value=\"".$annee."\">
					<table align=\"center\" width=\"100%\">
						<tr>
							<td>
								<table align=\"center\" width=\"100%\" height=\"100%\">
									<tr class=\"fixedheight\">
										<td align=\"center\">-</td>
									</tr>
									<tr class=\"fixedheight2\">
										<td align=\"center\">-</td>
									</tr>
									<tr class=\"fixedheight\">
										<td align=\"center\">Janvier</td>
									</tr>
									<tr class=\"fixedheight\">
										<td align=\"center\">Février</td>
									</tr>
									<tr class=\"fixedheight\">
										<td align=\"center\">Mars</td>
									</tr>
									<tr class=\"fixedheight\">
										<td align=\"center\">Avril</td>
									</tr>
									<tr class=\"fixedheight\">
										<td align=\"center\">Mai</td>
									</tr>
									<tr class=\"fixedheight\">
										<td align=\"center\">Juin</td>
									</tr>
									<tr class=\"fixedheight\">
										<td align=\"center\">Juillet</td>
									</tr>
									<tr class=\"fixedheight\">
										<td align=\"center\">Aout</td>
									</tr>
									<tr class=\"fixedheight\">
										<td align=\"center\">Septembre</td>
									</tr>
									<tr class=\"fixedheight\">
										<td align=\"center\">Octobre</td>
									</tr>
									<tr class=\"fixedheight\">
										<td align=\"center\">Novembre</td>
									</tr>
									<tr class=\"fixedheight\">
										<td align=\"center\">Décembre</td>
									</tr>
									<tr class=\"fixedheight\">
										<td align=\"center\"><b>Total :</b></td>
									</tr>
								</table>
							</td>";
		
		foreach ($liste_commerciaux as $key => $value) {
			echo "<td align=\"center\">
					<table align=\"center\" width=\"100%\" style=\"\">
						<tr class=\"fixedheight\">
							<td align=\"center\" colspan=\"3\">
								".UCfirst($value[username])."
							</td>
						</tr>
						<tr class=\"fixedheight2\">
							<td>Jours travaillés</td>
							<td>Objectif<br>mensuel</td>
							<td>Objectif<br>journalier</td>
						</tr>";
			$total_nb_jour=0;
			$objectif_total=0;
			for ($i = 1; $i <= 12; $i++) {
				if($value['jours']=="francs"){
					$nb_jour=date("t", mktime(0,0,0,$i,1,$annee));
				} else{
					$datedebut=$annee."-".$i."-01";
					$datefin=$annee."-".$i."-".date("t", mktime(0,0,0,$i,1,$annee));
					$nb_jour=getJours($datedebut, $datefin, $value[username]);
				}
				$objectif=$DB_site2->query_first(" SELECT objectif_ca FROM objectif_commercial WHERE annee ='".$annee."' AND mois='".$i."' AND username='".$value[username]."' ");
				if($objectif[objectif_ca]==""){
					$objectif[objectif_ca]=0;
				}
				
				if($nb_jour>0){
					// $objectif_jour=$objectif[objectif_ca]/$nb_jour;
					$objectif_jour[objectif_ca]=$objectif[objectif_ca];
					$objectif_mois[objectif_ca]=$objectif[objectif_ca]*$nb_jour;
				} else {
					$objectif_jour[objectif_ca]=0;
					$objectif_mois[objectif_ca]=0;
				}
				$objectif_total+=$objectif_mois[objectif_ca];
				echo "	<tr class=\"fixedheight\">
							<td>".$nb_jour."<input type=\"hidden\" id=\"nb_".$value[username]."_".$i."\" value=\"".$nb_jour."\"></td>
							<td><input type=\"text\" id=\"obj_".$value[username]."_".$i."\" value=\"".round($objectif_mois[objectif_ca])."\" readonly></td>
							<td><input type=\"text\" class=\"input_obj\" name=\"obj_".$value[username]."_".$i."\" id=\"input_".$value[username]."_".$i."\" value=\"".$objectif_jour[objectif_ca]."\"></td>
						</tr>";
				$total_nb_jour+=$nb_jour;
			}
			if($total_nb_jour>0){
				$objectif_mois_total=$objectif_total;
			} else {
				$objectif_mois_total=0;
			}
			echo "	<tr class=\"fixedheight\">
							<td>".$total_nb_jour."<input type=\"hidden\" id=\"nb_".$value[username]."\" value=\"".$total_nb_jour."\"></td>
							<td><input type=\"text\" id=\"obj_".$value[username]."\" value=\"".round($objectif_mois_total)."\" readonly></td>
							<td><input type=\"text\" class=\"input_obj\" name=\"obj_".$value[username]."\" id=\"input_".$value[username]."\" value=\"".$objectif_jour_total."\" readonly style=\"display:none\"></td>
						</tr>";
			
			echo "	</table>
				</td>";
		}
		
		
		
		
		echo "			</tr>
					</table>
					<br><br>
					<input type=\"submit\" value=\"Enregister\">
				</form>
			</center>";
	/*
	$test=getJours('2018-01-01', '2018-01-31', 'philippe');
	echo "ca fait : ".$test;
	*/
	?>
	<script>
	$=$jQuery;
	$(document).ready(function() {
		$(".input_obj").on('input', function() {
			var name=$(this).attr('name');
			var strpos=name.lastIndexOf("_");
			var commercial=name.substr(4, strpos-4);
			var nbr="nb"+name.substr(3);
			var result=Math.round( parseInt($(this).val()) * parseInt($("#"+nbr).val()) );
			$("#"+name).val(result);
			// console.log( "le commercial est : "+commercial );
			calcul_total(commercial);
		});
	} );
	function calcul_total(commercial){
		var nbr=0;
		var ca=0;
		var nbr_total=0;
		for (var i = 1; i <= 12; i++) {
			ca+=parseInt( $("#obj_"+commercial+"_"+i).val() );
		}
		$("#obj_"+commercial).val(ca);
		nbr_total=parseInt( $("#nb_"+commercial).val() );
		var result=Math.round( ca / nbr_total );
		$("#input_"+commercial).val(result);
	}
	</script>
	<?php

	
}




////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////
// fonction à déplacer dans un fichier inclus en admin (genre functions.php) ///////////////
////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////
function getJours($datedeb,$datefin,$utilisateur=""){ // format "Y-m-d"
	global $DB_site2;
	$jour_ferie_array=array();
	$absence_array=array();
	$jour_feries=$DB_site2->query("SELECT * FROM jour_ferie ORDER BY dateline ASC ");
	while($jour_ferie=$DB_site2->fetch_array($jour_feries)){
		$jour_ferie_array[]=$jour_ferie[dateline]; // ici les jours fériés
	}
	if($utilisateur!=""){
		$absences=$DB_site2->query("SELECT * FROM absence WHERE username='".$utilisateur."' ");
		while($absence=$DB_site2->fetch_array($absences)){
			$abs_dated=explode('-',$absence[datedebut]);
			$abs_datef=explode('-',$absence[datefin]);
			$abs_timestampcurr=mktime(0,0,0,$abs_dated[1],$abs_dated[2],$abs_dated[0]);
			$abs_timestampf=mktime(0,0,0,$abs_datef[1],$abs_datef[2],$abs_datef[0]);
			while($abs_timestampcurr<=$abs_timestampf){
				$absence_array[]=date("Y-m-d",$abs_timestampcurr); // ici les jours d'absence
				$abs_timestampcurr=mktime(0,0,0,date('m',$abs_timestampcurr),(date('d',$abs_timestampcurr)+1) ,date('Y',$abs_timestampcurr));
			}
		}
	}
    $nb_jours=0;
    $dated=explode('-',$datedeb);
    $datef=explode('-',$datefin);
    $timestampcurr=mktime(0,0,0,$dated[1],$dated[2],$dated[0]);
    $timestampf=mktime(0,0,0,$datef[1],$datef[2],$datef[0]);
    while($timestampcurr<=$timestampf){
		$datecurr=date('Y-m-d',$timestampcurr);
		if( !in_array($datecurr,$jour_ferie_array) && !in_array($datecurr,$absence_array) ){ // si c'est pas un jour férié
			if((date('w',$timestampcurr)!=0)&&(date('w',$timestampcurr)!=6)){ // si c'est pas un samedi ou un dimanche
				$nb_jours++;
			}
		}
		$timestampcurr=mktime(0,0,0,date('m',$timestampcurr),(date('d',$timestampcurr)+1) ,date('Y',$timestampcurr));
    }
	return $nb_jours;
}

echo "<br>";
include "./footer.php";

?>
