<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(BACK_PATH."/adv/adv.class.php");

$fournisseur_nom = $_GET['name'];
$commande_id     = $_GET['facturid'];
$adv_id          = $_GET['advid'];

$adv = new adv($DB_site2, $adv_id);
$adv->locked = isset($_GET['annule']);

//Mise en place de la liste déroulante de nos transporteurs
$sql = "SELECT f.*";
$sql .= " FROM fournisseur f";
$sql .= " INNER JOIN fournisseur_condition fc ON (fc.societe LIKE f.societe)";
$sql .= " WHERE fc.types LIKE '%transporteur%'";
$sql .= " AND f.type = 'trspt'";
$sql .= " ORDER BY societe, ville";
$requete = $DB_site2->query($sql);
$optlieutrspt = array( "" => "-- Sélectionner --");
while($lieutrspt=$DB_site2->fetch_array($requete)){
    $optlieutrspt[$lieutrspt['id']] = $lieutrspt['societe']." ".$lieutrspt['ville']." (".substr($lieutrspt['cp'],0,2).")";
}

//Liste déroulante transporteurs
$sql = "SELECT *";
$sql .= " FROM fournisseur";
$sql .= " WHERE type = 'fourn'";
$sql .= " GROUP BY societe";
$sql .= " ORDER BY societe";
$req = $DB_site2->query($sql);
while($f=$DB_site2->fetch_array($req)){
	$livrparopt[$f['societe']] = $f['societe'];
} 

$aTransporteur = array(
	'acilyon'      		=> 'Acilyon',
	'phase_automation'  => 'Phase Automation',
	'climadiff'    		=> 'Climadiff SA',
	'interfroid'   		=> 'Interfroid',
	'atelierduvin' 		=> 'L\'Atelier du Vin',
	'friax'        		=> 'Friax',
	'eurocave'     		=> 'Eurocave',
	'sommeliere'   		=> 'La Sommelière',
	'arnage'   	   		=> 'FRIO Entreprise'
);

//Définition des lieux d'enlèvement selon le transporteur
switch($_GET['name']){
	case 'Acilyon (TEST)':
		$aLieux = array('acilyon','phase_automation','ajd_fondation'); //Ajouter ici les index utilisés dans le tableau $aParams plus bas
		break;
	case 'DB Schenker France':
	case 'DB Schenker National':
	case 'DB Schenker Lyon':
		$aLieux = array('acilyon','friax','phase_automation','ajd_fondation'); //Ajouter ici les index utilisés dans le tableau $aParams plus bas
	break;
	case 'DB Schenker International':
		$aLieux = array('acilyon','phase_automation'); //Ajouter ici les index utilisés dans le tableau $aParams plus bas
	break;
	case 'HEPPNER LYON':
		$aLieux = array('acilyon','phase_automation');
		break;
	case 'HEPPNER Le Mans':
		$aLieux = array('acilyon','phase_automation','climadiff','interfroid','eurocave','sommeliere'); //Ajouter ici les index utilisés dans le tableau $aParams plus bas
	break;
	case 'FEDEX Express':
		$aLieux = array('acilyon','climadiff','interfroid','atelierduvin','sommeliere'); //Ajouter ici les index utilisés dans le tableau $aParams plus bas
	break;
	case 'TNT International':
		$aLieux = array(); //Un tableau vide permet d'afficher tous les lieux d'enlèvement existants en BDD
	break;
	case 'GLS':
		$aLieux = array(
			35740 => 'acilyon',
			36734 => 'friax',
			52103 => 'arnage'
		); //Ajouter ici les index utilisés dans le tableau $aParams plus bas
	break;
	default:
		$aLieux = array('acilyon','phase_automation','ajd_fondation'); //Ajouter ici les index utilisés dans le tableau $aParams plus bas
	break;
}

//On récupére les marques des produits sélectionnées en étape 5
//Ceci va permettre de n'afficher que les fournisseurs vendant les produits sélectionnés
foreach($adv->lignes as $lignefactureid => $qte){
	$sql = "SELECT am.marqueid";
	$sql .= " FROM lignefacture lf";
	$sql .= " INNER JOIN article_marque am ON (am.artid = lf.artid)";
	$sql .= " WHERE lf.lignefactureid = ".$lignefactureid;
	$marque_id = $DB_site2->query_first($sql)[0];
	$aMarqProd[] = $marque_id;
}

if(count($aLieux) > 0 &&false){ // désactivé pour tester car compliqué à configurer

	foreach($aLieux as $compte_chargeur => $le_lieu){
		$sql = "SELECT *";
		$sql .= " FROM fournisseur";
		$sql .= " WHERE societe = '".mysqli_real_escape_string($DB_site2->link_id,$aTransporteur[$le_lieu])."'";
		$sql .= " AND type = 'fourn'";
		$aTrspt = $DB_site2->query_first($sql);

		$affiche_fourn = true;
		if($aTrspt['marques'] != ''){

			$affiche_fourn = false;

			//Récupération des marques gérées par le fournisseur
			$aMarqFourn = explode(',',$aTrspt['marques']);

			foreach($aMarqProd as $marque_id){
				if(in_array($marque_id, $aMarqFourn)){
					$affiche_fourn = true;
					break;
				}
			}
		}

		if($affiche_fourn){
			$aParams[$le_lieu] = array(
				"libelle" => $aTransporteur[$le_lieu]." (".ucfirst(strtolower($aTrspt['ville'])).")".(!is_numeric($compte_chargeur) || $compte_chargeur > 20 ? " (Compte chargeur ".$compte_chargeur.")" : "" ),
				"name" 	  => "lieutrspt",
				"values"  => array("%libelle%" => "fourn".$aTrspt['id']),
				"actions" => array(
					"loadStep('trspt8', '/admin/adv/ajax/majDateEnlev.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')",
				    "adv_save('lieutrspt',7)"
			    )
			);
		}
	} 
} else {
	$sql = "SELECT f.*";
	$sql .= " FROM fournisseur f";
	$sql .= " INNER JOIN fournisseur_condition fc ON (fc.societe = f.societe)";
	$sql .= " WHERE f.type = 'fourn'";
	$sql .= " AND fc.activite = '1'";
	$sql .= " ORDER BY f.societe";
	$req = $DB_site2->query($sql);
	while($row = $DB_site2->fetch_array($req)){

		$affiche_fourn = true;
		if($row['marques'] != ''){

			$affiche_fourn = false;

			//Récupération des marques gérées par le fournisseur
			$aMarqFourn = explode(',',$row['marques']);

			foreach($aMarqProd as $marque_id){
				if(in_array($marque_id, $aMarqFourn)){
					$affiche_fourn = true;
					break;
				}
			}
		}

		if($affiche_fourn){
		    $aParams[] = array(
			    "libelle" => $row['societe']." (".ucfirst(strtolower($row['ville'])).")",
			    "name" 	  => "lieutrspt",
			    "values"  => array("%libelle%" => "fourn".$row['id']),
			    "actions" => array(
				    "loadStep('trspt8', '/admin/adv/ajax/majDateEnlev.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')",
				"adv_save('lieutrspt',7)"
			)
		    );
		}
	}
}

?>
<p style="padding:5px; margin:0;">7. Lieu d'enlèvement</p>
<table>
	<tr>
	<?php 
	$i=0;
	foreach($aParams as $lieu => $params){
// 		if(count($aLieux) == 0 || in_array($lieu, $aLieux)){
			foreach($params['values'] as $key => $value){
				unset($params['values'][$key]);
				$key = str_replace('%libelle%', $params['libelle'], $key);
				$params['values'][$key] = $value;
			}

			if($i%2 == 0) echo "</tr><tr>";
			?>
			
		        <td><?php echo $adv->radio_inputs($params['name'], $params['values'], implode(';',$params['actions'])); ?></td>
		    
			<?php

			$i++;
// 		} 
	}
	?>
	</tr>
	<?php 
	//stripos() permet de faire une recherche insensible à la casse
	if(stripos($_GET['name'], 'heppner') !== false || stripos($_GET['name'], 'schenker') !== false){ 
	?>
	<tr>
		<td colspan="2"><?php echo $adv->radio("lieutrspt",array(""=>"liebherr"),"loadStep('trspt8', '/admin/adv/ajax/majDateEnlev.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."'); adv_save('lieutrspt',7)"); ?>Livraison à votre entrepôt de <?php echo $adv->select("livraisoneb",$optlieutrspt,"adv_save(this.name)"); ?> par le transporteur de la société <?php echo $adv->select("livrpar",$livrparopt,"adv_save(this.name)"); ?></td>
	</tr>
	<?php } ?>
</table>