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

$req=$DB_site2->query("SELECT * FROM marque ORDER BY libelle");
while($m=$DB_site2->fetch_array($req)){
    $marques[$m['marqueid']]=$m['libelle'];
    $optmarques.="<option value=\"".$m['marqueid']."\">".$m['libelle']."</option>";
}

$req=$DB_site2->query("SELECT * FROM typearticle ORDER BY libelle");
while($ta=$DB_site2->fetch_array($req)){
	$opttypearticle.="<option value=\"".$ta['typearticleid']."\">".$ta['libelle']."</option>";
}

$from=date("d/m/Y", mktime(0,0,0, date("m")-6));
$to=date("d/m/Y");


$sql = "SELECT * FROM market_place WHERE market_place_site_id != '00'";
$requete = $DB_site2->query($sql);
$optmp="";
while($row = $DB_site2->fetch_array($requete)){
	$optmp.="<option value=\"".$row['market_place_nom2']."\">".$row['market_place_nom_complet']."</option>";
}


$datemoins6mois=mktime(0,0,0,date("m")-6);
$optvf="";
$sql = "SELECT * FROM vente_flash_historique WHERE du>".$datemoins6mois." ORDER BY du DESC";
$requete = $DB_site2->query($sql);
while($row = $DB_site2->fetch_array($requete)){
	$datedu=date("d/m/Y", $row['du']);
	$dateau=date("d/m/Y", $row['au']);
	$sql_artids = "	SELECT article.artid FROM vente_flash_harticles 
					INNER JOIN article ON (article.artcode=vente_flash_harticles.artcode)
					WHERE vfhid=".$row['id'];
	$artids="";
	$virgule="";
	$requeteartids = $DB_site2->query($sql_artids);
	while($rowartids = $DB_site2->fetch_array($requeteartids)){
		$artids.=$virgule.$rowartids['artid'];
		$virgule=",";
	}
	$optvf.="<option value=\"".$artids."\" data-datedu=\"".$datedu."\" data-dateau=\"".$dateau."\">".$row['nom']."</option>";
}



?>
  <h1>Créer une étude de marge sur les articles :</h1>
  <div class="commande_detail">
	  <form name="filter" method="get" action="./export/export_marge_article.php" style="margin:10px;" target="_blank">
		<p class="column">
			<b>Type de recherche :</b> 
			<input type="radio" name="typesrc" value="vus"> Les 100 articles les plus vus 
			<input type="radio" name="typesrc" value="vendus"> Les 100 articles les plus vendus 
			<input type="radio" name="typesrc" value="" checked> Tous les articles 
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
			<b>Lotage d'article :</b>
			<input type="checkbox" name="lotage[]" value="seul" checked> Articles seuls
			<input type="checkbox" name="lotage[]" value="lot" checked> Lots
		</p>
		<p class="column">
			<b>Status d'article :</b>
			<input type="checkbox" name="active[]" value="1" id="active1" checked> Actifs
			<input type="checkbox" name="active[]" value="0" id="active0"> Inactifs
			<input type="checkbox" name="active[]" value="2" id="active2"> Supprimé
			<input type="checkbox" name="active[]" value="3" id="active3"> Archivé
		</p>
		<p class="column">
			<b>Uniquement exporté sur marketplace :</b>
			<select name="marketplace">
				<option value="">Ignorer ce critère</option>
				<?= $optmp ?>
			</select>
		</p>
		<p class="column">
			<b>Uniquement vendu à un client (marche <b>uniquement</b> pour les 100 plus vendus) :</b>
			<select name="typeutilisateur">
				<option value="">Ignorer ce critère</option>
				<option value="pro">Pro uniquement</option>
				<option value="part">Particulier uniquement</option>
			</select>
		</p>
		<p class="column">
			<b>Uniquement présent dans la vente flash :</b>
			<select name="artids" id="artids">
				<option value=""  data-datedu="<?= $from ?>" data-dateau="<?= $to ?>">Ignorer ce critère</option>
				<?= $optvf ?>
			</select> <i>En sélectionnant ce critère, l'étude de marge ne sera pas sauvegardée</i>
		</p>
<script>
// ici le jquery du onchange du select vente flash.

$jQuery("#artids")
  .change(function () {
    $jQuery("#from").val($jQuery('#artids').find(':selected').data('datedu'));
	$jQuery("#to").val($jQuery('#artids').find(':selected').data('dateau'));
	$jQuery("#active1").prop('checked', true);
	$jQuery("#active0").prop('checked', true);
	$jQuery("#active2").prop('checked', true);
	$jQuery("#active3").prop('checked', true);
  });
</script>
		<p class="column">
			<b>Ventes et vues comprises entre le (inclus) :</b> <input type="text" name="from" id="from" size="10" value="<?= $from ?>" onclick="calendrier_show('from',this.value);" readonly>
			<b>et le (inclus) :</b> <input type="text" name="to" id="to" size="10" value="<?= $to ?>" onclick="calendrier_show('to',this.value);" readonly> <i>La date de vente considérée est la date de création de la commande</i>
		</p>
		<p class="column">
			<b>Visualisation pour fournisseurs :</b>
			<input type="radio" name="visu_fournisseur" value="0" checked> non
			<input type="radio" name="visu_fournisseur" value="1"> oui
		</p>
		<input type="submit" class="skinned" value="Envoyer">
	  </form>
	  <br><br>nb : les vues des articles ne sont disponibles que pour les 6 derniers mois.
  </div>
  <div class="clear"></div>
  <h1>Liste des étude de marge sur les articles :</h1>
  <div class="commande_detail">
	<table align="center" width="100%">
		<tr class="flist">
			<td align="center">#</td>
			<td align="center">Date</td>
			<td align="center">Type de recherche</td>
			<td align="center">Marque</td>
			<td align="center">Type</td>
			<td align="center">Lotage</td>
			<td align="center">Status</td>
			<td align="center">Période</td>
			<td align="center">marketplace</td>
			<td align="center">Visu</td>
			<td align="center">Détail</td>
		</tr>
		<?php
		$i=0;
		$marges=$DB_site2->query("SELECT * FROM marge_archive ORDER BY marge_archiveid DESC");
		while($marge=$DB_site2->fetch_array($marges)){
			$critere=unserialize($marge[criteres]);
			
			if($critere[marqueid]==""){
				$lib_marque_txt="Toutes marques";
			} else {
				$lib_marque=$DB_site2->query_first("SELECT * FROM marque WHERE marqueid='".$critere[marqueid]."' ");
				$lib_marque_txt=$lib_marque[libelle];
			}
			if($critere[visu_fournisseur]==0){
				$visu_fournisseur_txt="interne";
			} else {
				$visu_fournisseur_txt="fournisseur";
			}
			if($critere[typesrc]==""){
				$typesrc_txt="tous";
			}elseif($critere[typesrc]=="vus"){
				$typesrc_txt="les + vus";
			}else{
				$typesrc_txt="les + vendus";
			}
			if($critere[typearticleid]==""){
				$typearticleid_txt="Tous";
			} else {
				$res=$DB_site2->query_first("SELECT * FROM typearticle WHERE typearticleid='".$critere[typearticleid]."' ");
				$typearticleid_txt=$res[libelle];
			}
			$lotage_txt="";
			$virgule="";
			if(in_array("seul",$critere[lotage])){
				$lotage_txt.="articles seuls";
				$virgule=", ";
			}
			if(in_array("lot",$critere[lotage])){
				$lotage_txt.=$virgule."lots";
			}
			$active_txt="";
			$virgule="";
			if(in_array(1,$critere[active])){
				$active_txt.="Actifs";
				$virgule=", ";
			}
			if(in_array(0,$critere[active])){
				$active_txt.=$virgule."Inactifs";
				$virgule=", ";
			}
			if(in_array(2,$critere[active])){
				$active_txt.=$virgule."supprimés";
				$virgule=", ";
			}
			if(in_array(3,$critere[active])){
				$active_txt.=$virgule."archivés";
			}
			if($critere[marketplace]==""){
				$marketplace_txt="ignoré";
			} else {
				$marketplace_txt=$critere[marketplace];
			}
			echo "<tr class=\"flist\">
					<td>".$marge[marge_archiveid]."</td>
					<td>".date("d/m/Y h:i:s",$marge[dateline])."</td>
					<td>".$typesrc_txt."</td>
					<td>".$lib_marque_txt."</td>
					<td>".$typearticleid_txt."</td>
					<td>".$lotage_txt."</td>
					<td>".$active_txt."</td>
					<td>du ".$critere[from]."<br>au ".$critere[to]."</td>
					<td>".$marketplace_txt."</td>
					<td>".$visu_fournisseur_txt."</td>
					<td>
						<a href=\"marge_archive.php?action=visu&marge_archiveid=".$marge[marge_archiveid]."\" target=\"_blank\">charger</a><br>
						<a href=\"marge_archive.php?action=delete&marge_archiveid=".$marge[marge_archiveid]."\">supprimer</a>
					</td>
				</tr>";
			$i++;
		}
		if($i==0){
			echo "<tr><td colspan=\"10\" align=\"center\">aucune étude de marge disponible</td></tr>";
		}
		?>
	</table>
  </div>
<?php

include "./footer.php";

?>