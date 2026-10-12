<?php

require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
header('Content-Type: text/html; charset=utf-8');
set_time_limit(20);


if($_GET['user_commercial']!=""){
	$commercial=$_GET['user_commercial'];
	echo "pour le commercial : ".$_GET['user_commercial']."<br>";
	$sqlcommercial1=" p.commercial='".$commercial."' ";
	$sqlcommercial2=" u.commercial='".$commercial."' ";
} else {
	$sqlcommercial1=" 1=1 ";
	$sqlcommercial2=" 1=1 ";
}
if($_GET['type']!=""){
	$type=$_GET['type'];
	echo "de type : ".$type."<br>";
}

$from=mktime(0,0,0);
$to=mktime(23,59,59);
$liste_etatid="1,2,3,4,7,8";

switch($type){
	case 'nouvel_evenement':
		$sql = "SELECT u.userid as userid, u.*, p.* FROM utilisateur AS u LEFT JOIN projet AS p on(u.userid=p.userid) 
				WHERE (".$sqlcommercial1." AND p.etatid='9' AND u.utilisateur_admin='0')
				OR
					(".$sqlcommercial2." AND u.projet_etatid='9' AND p.projetid IS NULL AND u.utilisateur_admin='0')  "; // nouvel événement
		break;
	case 'a_attribuer':
		$sql = "SELECT u.userid as userid, u.*, p.* FROM utilisateur AS u LEFT JOIN projet AS p on(u.userid=p.userid) 
				WHERE u.commercial='' AND (conseils='oui' OR allowemail='1') AND projet_etatid IN(".$liste_etatid.")
				AND u.utilisateur_admin='0' ";
		echo "le critère 'commercial' n'a pas été pris en compte<br>";
		break;
	case 'sans_projet':
		$sql = "SELECT u.userid as userid, u.*, p.* FROM utilisateur AS u LEFT JOIN projet AS p on(u.userid=p.userid) 
				WHERE ".$sqlcommercial2."
				AND u.projet_etatid IN(".$liste_etatid.",9)
				AND u.utilisateur_admin='0'
				AND p.projetid IS NULL ";
		break;
	case 'sans_rappel':
		$sql = "SELECT u.userid as userid, u.*, p.* FROM utilisateur AS u INNER JOIN projet AS p on(u.userid=p.userid) 
				WHERE ".$sqlcommercial1."
				AND p.rappel=0
				AND u.utilisateur_admin='0'
				AND p.etatid IN(".$liste_etatid.")";
		break;
	case 'rappels_jour':
		$sql = "SELECT u.userid as userid, u.*, p.* FROM utilisateur AS u INNER JOIN projet AS p on(u.userid=p.userid) 
				WHERE ".$sqlcommercial1."
				AND p.rappel BETWEEN ".$from." AND ".$to."
				AND u.utilisateur_admin='0'
				AND p.etatid IN(".$liste_etatid.") ORDER BY rappel ASC ";
		break;
	case 'rappels_retard':
		$sql = "SELECT u.userid as userid, u.*, p.* FROM utilisateur AS u INNER JOIN projet AS p on(u.userid=p.userid) 
				WHERE ".$sqlcommercial1."
				AND p.rappel<".$from." AND rappel>0
				AND u.utilisateur_admin='0'
				AND p.etatid IN(".$liste_etatid.") ORDER BY rappel ASC ";
		break;
	case 'rappels_futur':
		$sql = "SELECT u.userid as userid, u.*, p.*, DATE_FORMAT(u.dateinscription, '%d/%m/%Y<br>%Hh%i') as date_inscription FROM utilisateur AS u INNER JOIN projet AS p on(u.userid=p.userid) 
				WHERE ".$sqlcommercial1."
				AND p.rappel>".$to."
				AND u.utilisateur_admin='0'
				AND p.etatid IN(".$liste_etatid.") ORDER BY rappel ASC ";
	
		break;
	default :
		die("erreur de parametre");
		break;
}


echo "<div id=\"comptage\"></div>
	<table class=\"table_alternate_color\" style=\"width:1000px;\">
		<tr>
			<td align=\"center\">Tag</td>
			<td align=\"center\">Raison sociale</td>
			<td align=\"center\">Nom</td>
			<td align=\"center\">Projet</td>
			<td align=\"center\">Etat</td>
			<td align=\"center\">Commercial</td>
			<td align=\"center\">Echéance</td>
			<td align=\"center\">Rappel</td>
			<td align=\"center\">Budget</td>
		</tr>";
$i=0;
// echo $sql;
$sql.=" LIMIT 1000 ";
$requete=$DB_site2->query($sql);
while($row=$DB_site2->fetch_array($requete)){
	$row['userid']=$row[0];
	$tag  = usericon($row['vip'],20);
	// print_r($row);
	if(isset($row['projetid']) && $row['projetid']!=""){
		$projet_txt="<a href=\"/admin/utilisateur.php?action=editer&userid=".$row['userid']."\" target=\"_blank\" title=\"N° ".$row['projetid']."\">".$row['libelle']."</a>\n";
		$etat=$DB_site2->query_first("SELECT * FROM projet_etat WHERE etatid='".$row['etatid']."' ");
		$etat_projet=$etat['libelle'];
	} else {
		$projet_txt="--";
		$etat_projet="--";
	}
	if($row['budget']==-1){
		$budget="~~";
	} else {
		$budget=number_format($row['budget'],2,',',' ')." &euro;";
	}
	
	$sql="SELECT * FROM suivi_client WHERE userid=".$row['userid']." AND rappel>0 ORDER BY id DESC LIMIT 1";
	$suivi=$DB_site2->query_first($sql);
	
	?>
		<tr class="flist">
			 <td align="center"><?php echo $tag; ?></td>
			 <td align="center"><?php echo $row['raisonsociale']; ?></td>
			 <td align="center"><a href="/admin/utilisateur.php?action=editer&userid=<?php echo $row['userid']; ?>" target="_blank"><?php echo $row['nom']." ".$row['prenom']; ?></a></td>
			 <td align="center" style="overflow: hidden;"><?php echo $projet_txt; ?></td>
			 <td align="center"><?php echo $etat_projet; ?></td>
			 <td align="center"><?php echo $row['commercial']; ?></td>
			 <td align="center"><?php if($row['echeance_livraison'] && $row['echeance_livraison'] !== "0000-00-00"){ echo date('d/m/Y', $row['echeance_livraison']); }?></td>
			 <td align="center"><?php echo isset($suivi['jour']) ? rappel_heure($suivi['rappel'],$suivi['jour']).$rappeltype.'<br>'.$suivi['pour'] : ''; ?></td>
			 <td align="center" nowrap><?php echo $budget;?></td>
		</tr>
	<?php
	$i++;
}

if($i==0){
	echo "<tr><td colspan=\"10\" align=\"center\">aucun résultat</td></tr>";
}

echo "</table>";

?>
<script>
$jQuery("#comptage").html('<?php echo $i;?> résultats');
</script>