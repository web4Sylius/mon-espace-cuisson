<?php

// MULTISITE

// $r=$DB_site2->query("SELECT * FROM garantie");
// while($g=$DB_site2->fetch_array($r)) $DB_site2->query("UPDATE garantie SET prix='".serialize(array('base'=>$g['prix']))."', ppi='".serialize(array('base'=>$g['ppi']))."'");

$garanties = DAOgarantie::loadAllGaranties($DB_site2,null,'FR',1);
if (isset($_POST['triMarque']) && !empty($_POST['triMarque'])) {
     $garanties = array_intersect_key($garanties, DAOgarantie::loadGarantiesByMarque($DB_site2,null,'FR',$_POST['triMarque'],1));
}
if (isset($_POST['triFournisseur']) && !empty($_POST['triFournisseur'])) {
     $garanties = array_intersect_key($garanties, DAOgarantie::loadGarantiesByFournisseur($DB_site2,null,'FR',$_POST['triFournisseur'],1));
}
if (isset($_POST['triType']) && !empty($_POST['triType'])) {
     $garanties = array_intersect_key($garanties, DAOgarantie::loadGarantiesByType($DB_site2,null,'FR',$_POST['triType'],1));
}
if (isset($_POST['triDestinataire']) && !empty($_POST['triDestinataire'])) {
     $garanties = array_intersect_key($garanties, DAOgarantie::loadGarantiesByDestinataire($DB_site2,null,'FR',$_POST['triDestinataire'],1));
}
//Filtrage sur les pays sélectionnés
if(isset($_POST['pays']) && !empty($_POST['pays'])){
     foreach($_POST['pays'] as $pays_id){
          foreach($garanties as $garantie_id => $garantie_valeurs){
               if(!in_array($pays_id, $garantie_valeurs->pays)){
                    unset($garanties[$garantie_id]);
               }
          }
     }
}

//Mise en forme du select "destinataire"
$optdestinataire.="<option value=\"\">Tous les destinataires</option>";
$sql = "SELECT garantie_client_id, garantie_client_nom";
$sql .= " FROM garantie_client";
$sql .= " ORDER BY garantie_client_nom";
$r = $DB_site2->query($sql);
while($destinataire=$DB_site2->fetch_array($r)){
     $selected = "";
     if (isset($_POST['triDestinataire']) && $destinataire['garantie_client_id'] == $_POST['triDestinataire']) {
          $selected = "selected";
     }
     $optdestinataire.="<option value=\"".$destinataire['garantie_client_id']."\" $selected>".$destinataire['garantie_client_nom']."</option>";
}
$optmarques.="<option value=\"\">Toutes les marques</option>";
$r=$DB_site2->query("SELECT marqueid,libelle FROM marque ORDER BY libelle");
while($marques=$DB_site2->fetch_array($r)){
	$selected="";
	if ($marques['marqueid'] == $_POST['triMarque']) {
		$selected = "selected";
	}
	$optmarques.="<option value=\"".$marques['marqueid']."\" $selected>".$marques['libelle']."</option>";
}
$optfournisseur.="<option value=\"\">Tous les fournisseurs</option>";
$r=$DB_site2->query("SELECT DISTINCT(societe) FROM fournisseur WHERE type=\"fournadv\" OR type=\"garadv\" ORDER BY societe;");
while($fournisseur=$DB_site2->fetch_array($r)){
	$selected="";
	if ($fournisseur['societe'] == $_POST['triFournisseur']) {
		$selected = "selected";
	}
	$optfournisseur.="<option value=\"".$fournisseur['societe']."\" $selected>".$fournisseur['societe']."</option>";
}
$opttype.="<option value=\"\">Tous les types</option>";
$r=$DB_site2->query("SELECT typearticleid,libelle FROM typearticle ORDER BY libelle;");
while($type=$DB_site2->fetch_array($r)){
	$selected="";
	if ($type['typearticleid'] == $_POST['triType']) {
		$selected = "selected";
	}
	$opttype.="<option value=\"".$type['typearticleid']."\" $selected>".$type['libelle']."</option>";
}

//Récupération de la liste des pays
$sql = "SELECT paysid, libelle";
$sql .= " FROM pays";
$sql .= " WHERE typepays=\"EE\"";
$sql .= " ORDER BY libelle";
$req = $DB_site2->query($sql);

$checked = "";
if(isset($_POST['checkAllPays']) && $_POST['checkAllPays'] == 1){
     $checked = "checked";
}

//Construction de l'encart de la liste de cases à cocher  de pays
$chkpays = "<div style=\"border: 1px solid grey; margin:10px 0 10px 0; padding: 2px; width: 60%;\">"
               . "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">"
                    . "<tr>"
                         . "<td colspan=\"3\" align=\"left\" style=\"font-weight:bold;\">"
                              . "<input type=\"checkbox\" name=\"checkAllPays\" value=\"1\" onClick=\"checkAll(this,'pays[]')\" ".$checked.">Tout cocher"    
                         . "</td>"
                    . "</tr>";

$cptPays = 0;
$nbCols = 4;
while($pays = $DB_site2->fetch_array($req)){
     $checked = "";
     //Précochage si pays selectionné
     if(isset($_POST['pays']) && !empty($_POST['pays']) && in_array($pays['paysid'], $_POST['pays'])){
          $checked = "checked";
     }
     if (isset($_POST['pays']) && !empty($_POST['pays']) && in_array("RE", $_POST['pays'])) {
          $checkedRE = "checked";
     }
     if (isset($_POST['pays']) && !empty($_POST['pays']) && in_array("RM", $_POST['pays'])) {
          $checkedRM = "checked";
     }
     
     if($cptPays == 0){
          $chkpays .= "<tr>";
     } else if($cptPays%$nbCols == 0) {
          $chkpays .= "</tr><tr>";
     }
     
     $chkpays .= "<td align=\"left\"><input type=\"checkbox\" name=\"pays[]\" id=\"pays_".$cptPays."\" value=\"".$pays['paysid']."\" $checked />".$pays['libelle']."</td>";
     $cptPays++;
}

$chkpays .= "</tr>"
          . "<tr>"
               . "<td align=\"left\" style=\"font-weight:bold;\">"
                    . "<input type=\"checkbox\" name=\"pays[]\" value=\"RE\" id=\"pays_".($cptPays+1)."\" ".$checkedRE."/>Reste de l'Europe"    
               . "</td>"
               . "<td align=\"left\" style=\"font-weight:bold;\">"
                    . "<input type=\"checkbox\" name=\"pays[]\" value=\"RM\" id=\"pays_".($cptPays+2)."\" ".$checkedRM."/>Reste du Monde"    
               . "</td>";

for($i=1;$i<=$nbCols-2;$i++){
     $chkpays .= "<td>&nbsp;</td>";
}

$chkpays .= "</tr>"
     . "</table>"
. "</div>";

?>
<form name="frmTri" method="post" action="garanties.php?action=liste" enctype="multipart/form-data">
     <select name="triDestinataire"><?php echo $optdestinataire; ?></select>
     <select name="triFournisseur"><?php echo $optfournisseur; ?></select>
     <select name="triType"><?php echo $opttype; ?></select>
     <select name="triMarque"><?php echo $optmarques; ?></select>
          <?php echo $chkpays; ?>
          <input type="submit" style="width: 100px;" value="Filtrer" />
</form>

<a href="garanties.php?action=ajouter">Ajouter une garantie</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="garanties.php?action=tabRecap">Tableau Récapitulatif</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="garanties.php?action=modifCode">Code fournisseur</a><br/><br/>

	<?php
	foreach ($garanties as $key => $garantie) {
	?>
		<table class="table_garantie">
		<tr>
			<td class="garantieid">Garantie n°<b><?php echo $garantie->garantieid; ?></b><br><?php echo $garantie->getTypes(); ?> <?php echo $garantie->getMarques(); ?></td>
			<td class="title"><?php echo $garantie->duree." an".($garantie->duree>1?"s":""); ?> <?php echo $garantie->getTypeGarantie(); ?> <?php echo $garantie->fournisseur; ?></td>
			<td rowspan="2" style="vertical-align: top;">
                              <img src="<?php echo 'images/icon/'.($garantie->destinataire_garantie == 1 ? 'particulier.png' : 'pro.png'); ?>" style="margin-bottom: 10px;">
                              <a href="garanties.php?action=modifier&garantieid=<?php echo $garantie->garantieid ?>"><img src="./images/editer.gif"></a><br><br>
                              <a href="garanties.php?action=copier&garantieid=<?php echo $garantie->garantieid ?>"><img src="./images/copier.gif"></a>
                              <!--<a href="garanties.php?action=supprimer&garantieid=<?php echo $garanties->garantieid ?>"><img src="./images/supprimer.gif"></a></td>-->
		</tr>
		<tr>
			<td><?php echo $garantie->comm; ?></td>
			<td class="pays"><?php echo $garantie->getPays(); ?></td>
		</tr>
		</table>
	<?php
	}
?>
<br/><br/>