<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");

if(isset($_GET['action'])){

	$vpid = $_GET['id'];
	
  	switch ($_GET['action']) {
		case 'close':
			//Cloture vente privee, mise en historique
			$vp = $DB_site2->query_first("SELECT * FROM vente_privee WHERE id='".$vpid."'");
			if($vp){
				
				//Historique vente privee
				$DB_site2->query("INSERT INTO vente_privee_historique (`nom`, `site`, `du`, `au`) VALUES('".$_GET['name']."', '".$vp['site_code']."', ".$vp['du'].", ".$vp['au'].")");
				$hist = $DB_site2->insert_id();

				//Historique articles selectionné
				$req = $DB_site2->query("SELECT * FROM vente_privee_article WHERE vfid = ".$_GET['id']);
				while($prod = $DB_site2->fetch_array($req)){
					$article = $DB_site2->query_first("SELECT a_s.prix, artcode_eurocave FROM article INNER JOIN article_site a_s USING(artid) WHERE artcode='".$prod['artcode']."' AND site_code='".$vp['site_code']."'");
					$DB_site2->query("INSERT INTO vente_privee_harticles (`vfhid`, `artcode`, `artcode_eurocave`, `prix`, `prixflash`) VALUES (".$hist.", '".$prod['artcode']."', '".$article['artcode_eurocave']."', '".$article['prix']."', '".$prod['prix']."') ");
				}

				//Delete
				$DB_site2->query("DELETE FROM vente_privee_article WHERE vfid = $vpid");
				$DB_site2->query("DELETE FROM vente_privee WHERE id = $vpid");
			}

			echo "Vente Privée ".$_GET['name']." sauvegardee !";
		  break;
      
    case 'delete':
			//Vente privee remise a zéro de la liste d'articles, pour en creer une nouvelle
      		//http://localhost.ma-cave-a-vin.fr/admin/ajax/vp_maj.php?action=delete&id=xx
			$vp = $DB_site2->query_first("SELECT * FROM vente_privee WHERE id='".$_GET['id']."'");
			if($vp){
  				// Delete liste article vente privee
  				$DB_site2->query("DELETE FROM vente_privee_article WHERE vfid = ".$vp[id]);
			}

			echo "Vente Privée ".$_GET['name']." liste effacée !";
		  break;
      
      case 'deleteh':
          // suppression d'une vente flash historique et ses articles
          $DB_site2->query("DELETE FROM vente_privee_historique WHERE id=".$_GET['vfhid']);
          $DB_site2->query("DELETE FROM vente_privee_harticles WHERE vfhid=".$_GET['vfhid']);
      
      echo "Vente Privée historique ".$_GET['vfhid']." effacée !";
		  break;
      
	}	
}

?>