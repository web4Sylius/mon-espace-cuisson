<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

echo "<h1>Gestion des caractéristiques</h1>" ;
				
?>
<script type="text/javascript">
	function changeorder(newposition,caractid){
		document.location.href=window.location.pathname+'?action=changeposition&caractid='+caractid+'&newposition='+newposition;
	}
</script>

<?php
if (isset($_GET['action'])){
    switch($action){
	case "reorder":
	$i=1;
	$req=$DB_site2->query("SELECT caractid FROM caracteristique ORDER BY position");
	while($caract=$DB_site2->fetch_array($req)){
	    $DB_site2->query("UPDATE caracteristique SET position=".$i." WHERE caractid=".$caract['caractid']);
	    $i++;
	}

	break;
    
	case "del":

	$caracteristique=$DB_site2->query_first("SELECT * from caracteristique WHERE caractid='$caractid'");
	echo "Etes-vous sûr(e) de vouloir supprimer la caracteristique $caracteristique[$libelle] ?<br><br>
		<a href=caracteristique.php?action=dodel&caractid=$caractid>OUI</a> | <a href=caracteristique.php>NON</a><br>";
	exit;
	break;

	case "dodel":
	
	$caracteristique=$DB_site2->query_first("SELECT * from caracteristique WHERE caractid='$caractid'");
	$DB_site2->query(" DELETE from caracteristique WHERE caractid='$caractid' ");
	$DB_site2->query(" DELETE from caracteristiquevaleur WHERE caractid='$caractid' ");
	$DB_site2->query("DELETE FROM article_caractval WHERE caractvalid IN(SELECT caractvalid FROM caracteristiquevaleur WHERE caracteristiquevaleur.caractid = '$caractid');");
	echo "La caracteristique $caracteristique[$libelle] a bien été supprimée.<br>";
	break;
	
	case "edit":
	$position=$DB_site2->query_first("SELECT position FROM caracteristique WHERE caractid=".$_GET['caractid']);
	$position_actuelle=$position[0];
	if($_GET['action2']=="up" && $position_actuelle>0){
	    $DB_site2->query("UPDATE caracteristique SET position=".$position_actuelle." WHERE position<".$position_actuelle." ORDER BY position DESC LIMIT 1");
	    $DB_site2->query("UPDATE caracteristique SET position=".($position_actuelle-1)." WHERE caractid=".$_GET['caractid']);
	}
	if($_GET['action2']=="down"){
	    $DB_site2->query("UPDATE caracteristique SET position=".$position_actuelle." WHERE position>".$position_actuelle." LIMIT 1");
	    $DB_site2->query("UPDATE caracteristique SET position=".($position_actuelle+1)." WHERE caractid=".$_GET['caractid']);
	}
	break;

	case "changeposition":

		$caractid = mysqli_escape_string($DB_site2->link_id,$_GET['caractid']);
		$newposition = intval(mysqli_escape_string($DB_site2->link_id,$_GET['newposition']));

		$old_position = $DB_site2->query_first("SELECT position,marqueid FROM caracteristique WHERE caractid='$caractid'"); //qui prends la place
		$caract_to_change = $DB_site2->query_first("SELECT caractid, position FROM caracteristique WHERE position='$newposition' AND marqueid = ".$old_position['marqueid']);//qui perd sa la place

		$DB_site2->query("UPDATE caracteristique SET position = '$newposition' WHERE caractid='$caractid'");//on donne la place au nouveau
		if(!is_null($caract_to_change)){
			if($newposition > $old_position[position]){

				$les_positions = $DB_site2->query("SELECT caractid, position FROM caracteristique WHERE position < ".$newposition." AND marqueid = ".$old_position['marqueid']." ORDER BY position");
				while ($la_caract = $DB_site2->fetch_array($les_positions)) {
					if($la_caract[position] > $old_position[position]){
						$position = $la_caract[position]-1;
						$DB_site2->query("UPDATE caracteristique SET position ='$position' WHERE caractid='$la_caract[caractid]'");
					}
				}
				$DB_site2->query("UPDATE caracteristique SET position =".($caract_to_change[position]-1)." WHERE caractid = $caract_to_change[caractid]");//on decale celui qui perd sa place
			}else{
				$les_positions = $DB_site2->query("SELECT caractid, position FROM caracteristique WHERE position > ".$newposition." AND marqueid = ".$old_position['marqueid']." ORDER BY position");
				while ($la_caract = $DB_site2->fetch_array($les_positions)) {
					if($la_caract[position] < $old_position[position]){
						$position = $la_caract[position]+1;
						$DB_site2->query("UPDATE caracteristique SET position ='$position' WHERE caractid='$la_caract[caractid]'");
					}
				}
				$DB_site2->query("UPDATE caracteristique SET position =".($caract_to_change[position]+1)." WHERE caractid = $caract_to_change[caractid]");//on decale celui qui perd sa place
			}
		}
		echo "<script>document.location=\"caracteristique.php?marque_id=".$old_position['marqueid']."\"</script>";
	break;
    }
}

//Récupération liste des marques (pour filtre)
$sql = "SELECT m.*";
$sql .= " FROM caracteristique c";
$sql .= " INNER JOIN marque m ON (m.marqueid = c.marqueid)";
$sql .= " GROUP BY m.marqueid";
$sql .= " ORDER BY m.libelle ASC";
$req = $DB_site2->query($sql);
$tabFiltreMarque = array();
while($row = $DB_site2->fetch_array($req)){
    $tabFiltreMarque[] = $row;
}
	
echo "<h3 style=\"float:left;\">Caractéristiques existantes</h3>
<a href=\"ajouter_caracteristique.php\" style=\"float:right;text-decoration: none; color: #363E42;\"><img src=\"images/icon/edit_add_22.png\" style=\"vertical-align:middle;\"> Ajouter une caracteristique</a>

<div style=\"width:100%;float:left;\">
<b>Marque :</b> <form method=\"get\" name=\"form_filtre\">
<select name=\"marque_id\" onchange=\"document.form_filtre.submit();\">
<option value=\"0\">Sélectionner</option>";
foreach($tabFiltreMarque as $la_marque){
	$selected = '';
	if(isset($_GET['marque_id']) && $_GET['marque_id'] == $la_marque['marqueid']){
		$selected = 'selected';
	}
	echo "<option value=\"".$la_marque['marqueid']."\" ".$selected.">".$la_marque['libelle']."</option>";
}

echo "</select>
</form>
</div>";

if(isset($_GET['marque_id']) && $_GET['marque_id'] != 0){
echo "<table class=\"table_alternate_color\">";
	
$caracteristiques=$DB_site2->query("SELECT * from caracteristique WHERE marqueid = ".$_GET['marque_id']." ORDER BY position");
$total=$DB_site2->query_first("SELECT COUNT(caractid) from caracteristique ");
$total = $total[0];
if ($DB_site2->num_rows($caracteristiques) > 0)
	{
	echo <<<HTML
	<tr>
		<td class="entete tri_tableau">Position</td>
		<td class="entete tri_tableau">Libellé interne</td>
		<td class="entete tri_tableau">Libellé</td>
		<td class="entete tri_tableau"></td>
	</tr>
HTML;
	}
	
$caractnb=0;
while ($caracteristique=$DB_site2->fetch_array($caracteristiques)) 
	{
	$delbtn="";
	if($aaccess==false) $delbtn="<form action=\"caracteristique.php\">
		<input type=\"hidden\" name=\"caractid\" value=\"$caracteristique[caractid]\">
		<input type=\"hidden\" name=\"action\" value=\"del\">
		<input type=\"submit\" value=\"Supprimer\" class=\"bouton1\">
		</form>";
		
	$up=$down="";
	if($caractnb>0) $up='<a href="caracteristique.php?action=edit&action2=up&caractid='.$caracteristique[caractid].'"><img src="images/monter.gif" alt="Monter" height="7" border="0"></a>';
	if($caractnb<$DB_site2->num_rows($caracteristiques)) $down='<a href="caracteristique.php?action=edit&action2=down&caractid='.$caracteristique[caractid].'"><img src="images/descendre.gif" alt="Descendre" height="7" border="0"></a>';
	$select="<select name=\"position_".$caracteristique['caractid']."\" onchange=\"changeorder(this.value,".intval($caracteristique['caractid']).")\">";
	for ($i=1; $i < $total+1 ; $i++) { 
		if($caracteristique[position] == $i){
			$select.="<option value=\"$i\" selected>$i</option>";
		}else{
			$select.="<option value=\"$i\">$i</option>";
		}
		$select.="<option value=\"$i\"".($caracteristique['position']==$i)?"selected":""." >$i</option>";
	}
	$select.="</select>";
	echo <<<HTML
	<tr>
		<td align="center">
                    $select
		</td>
		<td>$caracteristique[descriptioninterne]</td>
		<td>$caracteristique[libelleFR]</td>
		<td>
		    <a href="ajouter_caracteristique.php?action=edit&&caractid=$caracteristique[caractid]" title="Editer"><img src="images/icon/edit.png" alt="Editer"></a>
		    
		</td>
	</tr>
HTML;
$caractnb++;
	}

echo "</table>";
}


include "./footer.php";
?>