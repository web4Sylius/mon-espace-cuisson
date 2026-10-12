<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");

//print_r ("Vente Flash action ".$_GET['action']."");
//echo "Vente Flash action ".$_GET['action'];

if(isset($_GET['action'])){
	
  switch ($_GET['action']) {
		case 'close':
			//Cloture vente flash, mise en historique
      //http://localhost.ma-cave-a-vin.fr/admin/ajax/vf_maj.php?action=close&name=toto
			$vf = $DB_site2->query_first("SELECT * FROM vente_flash WHERE id='".$_GET['id']."'");
			if($vf){
				
				//Historique vente flash
				$DB_site2->query("INSERT INTO vente_flash_historique (`nom`, `site`, `du`, `au`) VALUES('".$_GET['name']."', '".$vf['site_code']."', ".$vf['du'].", ".$vf['au'].")");
				$hist = $DB_site2->insert_id();

				//Historique articles selectionné
				$req = $DB_site2->query("SELECT * FROM vente_flash_article WHERE vfid = ".$_GET['id']);
				while($prod = $DB_site2->fetch_array($req)){
					$article = $DB_site2->query_first("SELECT a_s.prix, artcode_eurocave FROM article INNER JOIN article_site a_s USING(artid) WHERE artcode='".$prod['artcode']."' AND site_code='".$vf['site_code']."'");
					$DB_site2->query("INSERT INTO vente_flash_harticles (`vfhid`, `artcode`, `artcode_eurocave`, `prix`, `prixflash`) VALUES (".$hist.", '".$prod['artcode']."', '".$article['artcode_eurocave']."', '".$article['prix']."', '".$prod['prix']."') ");
				}

				//Delete
				//$DB_site2->query("DELETE FROM vente_flash_article WHERE vfid = 1");
			}

			echo "Vente Flash ".$_GET['name']." sauvegardee !";
		  break;
      
    case 'delete':
			//Vente flash remise a zéro de la liste d'articles, pour en creer une nouvelle
      //http://localhost.ma-cave-a-vin.fr/admin/ajax/vf_maj.php?action=delete&id=xx
			$vf = $DB_site2->query_first("SELECT * FROM vente_flash WHERE id='".$_GET['id']."'");
			if($vf){
  				// Delete liste article vente flash
  				$DB_site2->query("DELETE FROM vente_flash_article WHERE vfid = ".$vf[id]);
			}

			echo "Vente Flash ".$_GET['name']." liste effacée !";
		  break;
      
      case 'deleteh':
          // suppression d'une vente flash historique et ses articles
          $DB_site2->query("DELETE FROM vente_flash_historique WHERE id=".$_GET['vfhid']);
          $DB_site2->query("DELETE FROM vente_flash_harticles WHERE vfhid=".$_GET['vfhid']);
      
      echo "Vente Flash historique ".$_GET['vfhid']." effacée !";
		  break;
      
	}	
}

?>