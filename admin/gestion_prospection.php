<?php
require_once("admin_global.php");

include_once("./header.php");
include_once("./menu.php");

$today     = mktime(0,0,0);
$tomorrow  = $today+86400;
$tomorrow2 = $today+(2*86400);
$yesterday = $today-(100*86400);

$user_commercial  = isset($_GET['user_commercial']) ? $_GET['user_commercial'] : '';

?>
<style>
.tbl { border:solid 1px black; border-collapse:collapse; width:800px; }
.tbl td { background-color:#DDD; border-bottom:solid 2px white; padding:2px; }
.tbl .done td { background-color:#BBFFBB; }
.active_link {color:red;}
</style>


<h1>Gestion de la prospection :</h1>
<?php

//Récupération de tous les commerciaux actifs dans les 30 derniers jours
$sql =  "  SELECT commercial FROM utilisateur WHERE commercial <> ''";
$sql .= "  AND TO_DAYS(NOW()) - TO_DAYS(dateinscription) <= 30"; // 900 ?
$sql .= "  GROUP BY commercial ORDER BY commercial";
$req = $DB_site2->query($sql);
$optcom = "<option value=\"\">Tous</option>";
while($commercial = $DB_site2->fetch_array($req)){
    $optcom .= "<option value=\"".$commercial[0]."\" ".($user_commercial == $commercial[0] ? "selected" : "").">$commercial[0]</option>";
}
$optco .= "<option value=\"na\" ".($_GET['user_commercial'] == "na" ? "selected" : "").">Non attribué</option>";


//Récupération de la liste des type de suivi
$sql = "SELECT * FROM utilisateur_type_suivi";
$req = $DB_site2->query($sql);
while($row = $DB_site2->fetch_array($req)){
     $aTypeSuivi[] = $row;
}

?>
<form method="get" action="gestion_prospection.php">
     Commercial à : 
     <select style="width: 70px;" name="user_commercial" id="user_commercial">
          <?php echo $optcom; ?>
     </select>
     <input type="submit" value="OK">
</form>
<?php

$affiche_resultats = false;
if(isset($_GET['user_commercial']) ){
	$affiche_resultats = true;
}
if($affiche_resultats){
    ?>
    <table class="table_alternate_color" style="margin-top:10px; width: 550px;">
		<tr>
			<td class="entete3" align="center"><strong><a id="nouvel_evenement">Nouvel événement</a></strong></td>
			<td class="entete3" align="center"><strong><a id="a_attribuer">A attribuer</a></strong></td>
			<td class="entete3" align="center"><strong><a id="sans_projet">Sans projet</a></strong></td>
			<td class="entete3" align="center"><strong><a id="sans_rappel">Sans rappel</a></strong></td>
			<td class="entete3" align="center"><strong><a id="rappels_jour">Rappels du jour</a></strong></td>
			<td class="entete3" align="center"><strong><a id="rappels_retard">Rappels en retard</a></strong></td>
			<td class="entete3" align="center"><strong><a id="rappels_futur">Rappels futurs</a></strong></td>
		</tr>
    </table>
	
	<input type="hidden" id="user_commercial" name="user_commercial" value="<?php echo $user_commercial; ?>">
	<div id="tab_loading" style="text-align: center; margin: 10px 0;"></div>
    <div id="tab_resultats" style="text-align: center; margin: 10px 0;"></div>
    <?php
} else {
	?>
	<p style="text-align: center; color: #A71145; font-weight: bold; margin: 20px 0 0;">Filtrez pour afficher des résultats</p>
	<?php
}
?>
<script type="text/javascript">
	$jQuery("#nouvel_evenement").click(function(){
		$jQuery.ajax({
			url : 'gestion_prospection_ajax.php?user_commercial='+$jQuery("#user_commercial").val()+'&type=nouvel_evenement',
			type : 'GET',
			dataType : 'html',
			beforeSend: function () {
				$jQuery("#tab_loading").html('chargement en cours .....');
			},
			complete: function () {
				$jQuery("#tab_loading").html('');
				$jQuery(".entete3 a").removeClass("active_link");
				$jQuery("#nouvel_evenement").addClass("active_link");
			},
			success : function(code_html, statut){
				$jQuery("#tab_resultats").html(code_html);
			},
			error : function(resultat, statut, erreur){
				$jQuery("#tab_resultats").html('erreur');
			}
		});
	});
	$jQuery("#a_attribuer").click(function(){
		$jQuery.ajax({
			url : 'gestion_prospection_ajax.php?user_commercial='+$jQuery("#user_commercial").val()+'&type=a_attribuer',
			type : 'GET',
			dataType : 'html',
			beforeSend: function () {
				$jQuery("#tab_loading").html('chargement en cours .....');
			},
			complete: function () {
				$jQuery("#tab_loading").html('');
				$jQuery(".entete3 a").removeClass("active_link");
				$jQuery("#a_attribuer").addClass("active_link");
			},
			success : function(code_html, statut){
				$jQuery("#tab_resultats").html(code_html);
			},
			error : function(resultat, statut, erreur){
				$jQuery("#tab_resultats").html('erreur');
			}
		});
	});
	$jQuery("#sans_projet").click(function(){
		$jQuery.ajax({
			url : 'gestion_prospection_ajax.php?user_commercial='+$jQuery("#user_commercial").val()+'&type=sans_projet',
			type : 'GET',
			dataType : 'html',
			beforeSend: function () {
				$jQuery("#tab_loading").html('chargement en cours .....');
			},
			complete: function () {
				$jQuery("#tab_loading").html('');
				$jQuery(".entete3 a").removeClass("active_link");
				$jQuery("#sans_projet").addClass("active_link");
			},
			success : function(code_html, statut){
				$jQuery("#tab_resultats").html(code_html);
			},
			error : function(resultat, statut, erreur){
				$jQuery("#tab_resultats").html('erreur');
			}
		});
	});
	$jQuery("#sans_rappel").click(function(){
		$jQuery.ajax({
			url : 'gestion_prospection_ajax.php?user_commercial='+$jQuery("#user_commercial").val()+'&type=sans_rappel',
			type : 'GET',
			dataType : 'html',
			beforeSend: function () {
				$jQuery("#tab_loading").html('chargement en cours .....');
			},
			complete: function () {
				$jQuery("#tab_loading").html('');
				$jQuery(".entete3 a").removeClass("active_link");
				$jQuery("#sans_rappel").addClass("active_link");
			},
			success : function(code_html, statut){
				$jQuery("#tab_resultats").html(code_html);
			},
			error : function(resultat, statut, erreur){
				$jQuery("#tab_resultats").html('erreur');
			}
		});
	});
	$jQuery("#rappels_jour").click(function(){
		$jQuery.ajax({
			url : 'gestion_prospection_ajax.php?user_commercial='+$jQuery("#user_commercial").val()+'&type=rappels_jour',
			type : 'GET',
			dataType : 'html',
			beforeSend: function () {
				$jQuery("#tab_loading").html('chargement en cours .....');
			},
			complete: function () {
				$jQuery("#tab_loading").html('');
				$jQuery(".entete3 a").removeClass("active_link");
				$jQuery("#rappels_jour").addClass("active_link");
			},
			success : function(code_html, statut){
				$jQuery("#tab_resultats").html(code_html);
			},
			error : function(resultat, statut, erreur){
				$jQuery("#tab_resultats").html('erreur');
			}
		});
	});
	$jQuery("#rappels_retard").click(function(){
		$jQuery.ajax({
			url : 'gestion_prospection_ajax.php?user_commercial='+$jQuery("#user_commercial").val()+'&type=rappels_retard',
			type : 'GET',
			dataType : 'html',
			beforeSend: function () {
				$jQuery("#tab_loading").html('chargement en cours .....');
			},
			complete: function () {
				$jQuery("#tab_loading").html('');
				$jQuery(".entete3 a").removeClass("active_link");
				$jQuery("#rappels_retard").addClass("active_link");
			},
			success : function(code_html, statut){
				$jQuery("#tab_resultats").html(code_html);
			},
			error : function(resultat, statut, erreur){
				$jQuery("#tab_resultats").html('erreur');
			}
		});
	});
	$jQuery("#rappels_futur").click(function(){
		$jQuery.ajax({
			url : 'gestion_prospection_ajax.php?user_commercial='+$jQuery("#user_commercial").val()+'&type=rappels_futur',
			type : 'GET',
			dataType : 'html',
			beforeSend: function () {
				$jQuery("#tab_loading").html('chargement en cours .....');
			},
			complete: function () {
				$jQuery("#tab_loading").html('');
				$jQuery(".entete3 a").removeClass("active_link");
				$jQuery("#rappels_futur").addClass("active_link");
			},
			success : function(code_html, statut){
				$jQuery("#tab_resultats").html(code_html);
			},
			error : function(resultat, statut, erreur){
				$jQuery("#tab_resultats").html('erreur');
			}
		});
	});


	
	

</script>
<?php
include "footer.php";