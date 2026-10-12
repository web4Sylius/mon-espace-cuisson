<?php

require "admin_global.php";

include "./header.php";
include "./menu.php";




echo "<h1>Planning</h1>";

echo "<style>
.ca { color:black; border-collapse:collapse; border:solid thin black; margin-right:100px;}
.ca td { border:solid thin black; }
.ca_nb { text-align:right; white-space:nowrap; }
.ca_title { background-color:yellow; text-align:center; }
.ca_head { font-weight:bold; text-align:center; }
.gris{background-color:#ccc;}
.ajd{background-color:#0cc;}
</style>";

echo "<br>Utilisateur : <a href=\"planning_task.php?planning_username=benoit\">Benoit</a> &nbsp; 
						<a href=\"planning_task.php?planning_username=nia\">Nia</a> &nbsp; 
						<a href=\"planning_task.php?planning_username=morgane\">Morgane</a> &nbsp; 
						<a href=\"planning_task.php?planning_username=ariel\">Ariel</a> &nbsp; 
						<a href=\"planning_task.php?planning_username=cc\">Cyril</a> &nbsp; ";

if(isset($_GET['planning_username'])){
	$planning_username=$_GET['planning_username'];
    echo "<h2>le planning de ".$planning_username."</h2><br>";
	
	$req_bornes=$DB_site2->query_first("	SELECT MIN(excepted_date) AS mini_date, MAX(excepted_date) AS maxi_date FROM suivi_interne 
								WHERE in_charge='".$planning_username."'
								AND status IN ('working','planned','waiting','toplan') ");
	
	
	
	$start_time=strtotime($req_bornes[mini_date]);
	$end_time=strtotime($req_bornes[maxi_date]);


	$start=date("m-d-Y", $start_time);
	$end=date("m-d-Y", $end_time);

	$datediff = $end_time - $start_time;
	$duree=round($datediff / (60 * 60 * 24));

	echo "<div><table class=\"ca\"><tr>";
	for ($i = 0; $i <= $duree; $i++) {
		$jour_time=$start_time+(60*60*24*$i);
		$jour=date("d/m", $jour_time);
		$j=0;
		$class="";
		if(date("w",$jour_time)==0 || date("w",$jour_time)==6){
			$class="gris";
		}
		if(date("d/m")==$jour){
			$class="ajd";
		}
		echo "<td align=\"center\" class=\"".$class."\" nowrap>".$jour."</td>";
	}
	echo "</tr>";

	for ($i = 0; $i <= $duree; $i++) {
		$jour_time=$start_time+(60*60*24*$i);
		$jour=date("Y-m-d", $jour_time);
		
		
		$req=$DB_site2->query("	SELECT * FROM suivi_interne 
							WHERE in_charge='".$planning_username."'
							AND status IN ('working','planned','waiting','toplan')
							AND excepted_date BETWEEN '".$jour." 00-00-00' AND '".$jour." 23-59-59' ");
		while($res=$DB_site2->fetch_array($req)){
			$reste=$duree-$i-1;
			echo "<tr>";
			
			for ($l = 0; $l < $i; $l++) {
				$l_time=$start_time+(60*60*24*$l);
				$jour_l=date("d/m", $l_time);
				$class="";
				if(date("w",$l_time)==0 || date("w",$l_time)==6){
					$class="gris";
				}
				if(date("d/m")==$jour_l){
					$class="ajd";
				}
				echo "<td class=\"".$class."\">&nbsp;</td>";
			}
			/*
			if($i!=0){
				echo "<td colspan=\"".$i."\">&nbsp;</td>";
			}
			*/
			if($res[duration]>0){
				$pourcent=round($res[duration_real]*100/$res[duration]);
			} else {
				$pourcent=0;
			}
			$class="";
			if(date("w",$jour_time)==0 || date("w",$jour_time)==6){
				$class="gris";
			}
			if(date("Y-m-d")==$jour){
				$class="ajd";
			}
			echo "<td align=\"center\" nowrap class=\"survol ".$class."\" style=\"position:relative\">
					<a href=\"task.php?a=showTask&task=".$res[id]."\" target=\"_blank\" style=\"z-index:50;\">".$res[id]."</a><br>
					".$pourcent." %
					<div style=\"display:none; position:absolute; top:20px; background-color:#fff; border:1px solid black; z-index:100;\">".$res[name]."</div>
				</td>";
			
			for ($l = 0; $l <= $reste; $l++) {
				$l_time=$jour_time+(60*60*24*($l+1));
				$jour_l=date("d/m", $l_time);
				$class="";
				if(date("w",$l_time)==0 || date("w",$l_time)==6){
					$class="gris";
				}
				if(date("d/m")==$jour_l){
					$class="ajd";
				}
				echo "<td class=\"".$class."\">&nbsp;</td>";
			}
			echo "</tr>";
		}
	}

	echo "</table></div>";
	?>
	<script>

	$jQuery(function() {
		$jQuery('.survol').hover(
		  function() {
			$jQuery( this ).find( "div:last" ).show();
		  }, function() {
			$jQuery( this ).find( "div:last" ).hide();
		  }
		);
	});

	</script>
	<?
	
	
}

include "./footer.php";

?>