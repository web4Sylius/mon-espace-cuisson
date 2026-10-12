<?php
ini_set('max_execution_time', 10000);
include('global.php');
/*$sql = "SELECT * FROM article_caractval";
$res = $DB_site->query($sql);
while($row = $DB_site->fetch_array($res)){
	$old = $DB_site->query_first("SELECT * FROM article_caractval_mec WHERE artid =".$row['artid']." AND caractvalid='".$row['caractvalid']."'");
	//$DB_site->query("UPDATE article_caractval SET differenceprixht ='".$old['differenceprixht']."', differenceprix='".$old['differenceprix']."' WHERE artid =".$row['artid']." AND caractvalid='".$row['caractvalid']."'");
}*/

//hotte performance
/*$sql = "SELECT * FROM article_hotte";
$res = $DB_site->query($sql);
while($row = $DB_site->fetch_array($res)){
	$old = $DB_site->query_first("SELECT * FROM article_mec WHERE artid =".$row['artid']);
	echo "UPDATE article_hotte SET frequence = '".$old[frequence]."', class_energetique='".$old[energy_label]."', cordon_alim='".$old[cordon_alim]."', intensite_electrique='".$old[intensite_electrique]."', tension_electrique='".$old[tension_electrique]."', puissance_totale = '".$old[puissance_totale]."' WHERE artid=".$row[artid]."<br>";
	$DB_site->query("UPDATE article_hotte SET frequence = '".$old[frequence]."', class_energetique='".$old[energy_label]."', cordon_alim='".$old[cordon_alim]."', intensite_electrique='".$old[intensite_electrique]."', tension_electrique='".$old[tension_electrique]."', puissance_totale = '".$old[puissance_totale]."' WHERE artid=".$row[artid]);
}*/
//cuisiniere performance
/*$sql = "SELECT * FROM article_cuisiniere";
$res = $DB_site->query($sql);
while($row = $DB_site->fetch_array($res)){
	$old = $DB_site->query_first("SELECT * FROM article_mec WHERE artid =".$row['artid']);
	echo "UPDATE article_cuisiniere SET frequence = '".$old[frequence]."', class_energetique='".$old[energy_label]."', cordon_alim='".$old[cordon_alim]."', intensite_electrique='".$old[intensite_electrique]."', tension_electrique='".$old[tension_electrique]."', puissance_totale = '".$old[puissance_totale]."' WHERE artid=".$row[artid]."<br>";
	$DB_site->query("UPDATE article_cuisiniere SET frequence = '".$old[frequence]."', class_energetique='".$old[energy_label]."', cordon_alim='".$old[cordon_alim]."', intensite_electrique='".$old[intensite_electrique]."', tension_electrique='".$old[tension_electrique]."', puissance_totale = '".$old[puissance_totale]."' WHERE artid=".$row[artid]);
}*/

//class energetique et cordon alim
/*$table = "article_cellule_refroidissement";
$sql = "SELECT * FROM $table";
$res = $DB_site->query($sql);
while($row = $DB_site->fetch_array($res)){
	$old = $DB_site->query_first("SELECT * FROM article_mec WHERE artid =".$row['artid']);
	echo "UPDATE $table SET class_energetique='".$old[energy_label]."', cordon_alim='".$old[cordon_alim]."' WHERE artid=".$row[artid]."<br>";
	$DB_site->query("UPDATE $table SET class_energetique='".$old[energy_label]."', cordon_alim='".$old[cordon_alim]."' WHERE artid=".$row[artid]);
}*/

//update article_mec set energy_label ='A' WHERE energy_label ='/article/energy_label/A-30%'

/* 
*/

//couleur fab
/*$tabtable = array("article_cuisiniere","article_piano","article_hotte","article_froid","article_frigo","article_congel","article_cellule_refroidissement","article_four","article_table_cuisson","article_plancha","article_tiroir","article_lavevaisselle","article_laveseche");
foreach($tabtable as $table){
	$sql = "SELECT * FROM article_mec";
	$res = $DB_site->query($sql);
	while($row = $DB_site->fetch_array($res)){

		$sql2 = "SELECT * FROM $table WHERE artid=".$row[artid];
		$res2 = $DB_site->query($sql2);
		if($DB_site->num_rows($res2)>0 && $row[coloris]!=''){
			echo "UPDATE $tadimensions cuisinière
$table = "article_cuisiniere";
$sql = "SELECT * FROM article_mec WHERE typearticleid = '2'";
$res = $DB_site->query($sql);
while($row = $DB_site->fetch_array($res)){
	echo "UPDATE $table SET hauteur='".$row[hauteur]."', largeur='".$row[largeur]."', profondeur='".$row[profondeur]."',poids='".$row[poid]."' WHERE artid=".$row[artid]."<br>";
	$DB_site->query("UPDATE $table SET hauteur='".$row[hauteur]."', largeur='".$row[largeur]."', profondeur='".$row[profondeur]."',poids='".$row[poid]."' WHERE artid=".$row[artid]);
}ble SET couleur_fab='".$row[coloris]."' WHERE artid=".$row[artid]."<br>";
			$DB_site->query("UPDATE $table SET couleur_fab='".$row[coloris]."' WHERE artid=".$row[artid]);
		}
	}
}*/

//matiere type meuble
/*$tabtable = array("article_meuble");
foreach($tabtable as $table){
	$sql = "SELECT * FROM article_meuble_mec";
	$res = $DB_site->query($sql);
	while($row = $DB_site->fetch_array($res)){

		$sql2 = "SELECT * FROM $table WHERE artid=".$row[artid];
		$res2 = $DB_site->query($sql2);
		if($DB_site->num_rows($res2)>0){
			echo "UPDATE $table SET matiere='".$row[matiere_meuble]."', type_accessoire='".$row[utilisation_meuble]."' WHERE artid=".$row[artid]."<br>";
			$DB_site->query("UPDATE $table SET matiere='".$row[matiere_meuble]."', type_accessoire='".$row[utilisation_meuble]."' WHERE artid=".$row[artid]);
		}
	}
}*/

//nb couverts lavevaisselle
/*$tabtable = array("article_lavevaisselle");
foreach($tabtable as $table){
	$sql = "SELECT * FROM article_lavevaisselle_mec";
	$res = $DB_site->query($sql);
	while($row = $DB_site->fetch_array($res)){

		$sql2 = "SELECT * FROM $table WHERE artid=".$row[artid];
		$res2 = $DB_site->query($sql2);
		if($DB_site->num_rows($res2)>0){
			echo "UPDATE $table SET couverts='".$row[capacite_lv]."' WHERE artid=".$row[artid]."<br>";
			$DB_site->query("UPDATE $table SET couverts='".$row[capacite_lv]."' WHERE artid=".$row[artid]);
		}
	}
}*/
/*$tabtable = array("article_table_cuisson");
foreach($tabtable as $table){
	$sql = "SELECT * FROM article_table_cuisson_mec";
	$res = $DB_site->query($sql);
	while($row = $DB_site->fetch_array($res)){

		$sql2 = "SELECT * FROM $table WHERE artid=".$row[artid];
		$res2 = $DB_site->query($sql2);
		if($DB_site->num_rows($res2)>0){
			echo "UPDATE $table SET matiere='".$row[nature_surface]."' WHERE artid=".$row[artid]."<br>";
			$DB_site->query("UPDATE $table SET matiere='".$row[nature_surface]."' WHERE artid=".$row[artid]);
		}
	}
}*/

/*$tabtable = array("article_frigo_dependant");
foreach($tabtable as $table){
	$sql = "SELECT * FROM article_frigo_mec";
	$res = $DB_site->query($sql);
	while($row = $DB_site->fetch_array($res)){

		$sql2 = "SELECT * FROM $table WHERE artid=".$row[artid];
		$res2 = $DB_site->query($sql2);
		if($DB_site->num_rows($res2)>0){
			echo "UPDATE $table SET balconnet='".$row[balconnet]."', compartiment='".$row[compartiment_fraicheur]."' WHERE artid=".$row[artid]."<br>";
			$DB_site->query("UPDATE $table SET balconnet='".$row[balconnet]."', compartiment='".$row[compartiment_fraicheur]."' WHERE artid=".$row[artid]);
		}
	}
}*/
//dimensions credence
/*$table = "article_credence";
$sql = "SELECT * FROM article_mec WHERE typearticleid='28'";
$res = $DB_site->query($sql);
while($row = $DB_site->fetch_array($res)){
	$res2 = $DB_site->query("SELECT artid FROM article_credence where artid =".$row[artid]);
	if($DB_site->num_rows($res2)>0){
		echo "UPDATE $table SET hauteur='".$row[hauteur]."', largeur='".$row[largeur]."', profondeur='".$row[profondeur]."',poids='".$row[poid]."' WHERE artid=".$row[artid]."<br>";
	$DB_site->query("UPDATE $table SET hauteur='".$row[hauteur]."', largeur='".$row[largeur]."', profondeur='".$row[profondeur]."',poids='".$row[poid]."' WHERE artid=".$row[artid]);
	}else{
		echo "INSERT INTO $table SET hauteur='".$row[hauteur]."', largeur='".$row[largeur]."', profondeur='".$row[profondeur]."',poids='".$row[poid]."' ,artid=".$row[artid];
		$DB_site->query("INSERT INTO $table SET hauteur='".$row[hauteur]."', largeur='".$row[largeur]."', profondeur='".$row[profondeur]."',poids='".$row[poid]."' ,artid=".$row[artid]);
	}
	
	
}
*/
// MISE A JOUR TARIFS 
/*$sql = "SELECT * FROM article a INNER JOIN article_marque USING(artid) WHERE marqueid IN(17,26,27)";
$res = $DB_site->query($sql);
while($row = $DB_site->fetch_array($res)){
	$sql2 = "SELECT * FROM article_mec WHERE artid=".$row[artid];
	$res2 = $DB_site->query_first($sql2);
	if(!is_null($res2)){

		// REVOIR LES CHAMPS DE LA REQUETE
		echo "UPDATE article SET prix='$res2[prix]',ecopart='$res2[ecopart]',ecopartht='$res2[ecopartht]' .... WHERE artid = $row[artid]; <br>";
		$DB_site->query("UPDATE article_fournisseur SET prix='$res2[prix]',ecopart='$res2[ecopart]',ecopartht='$res2[ecopartht]' ...... WHERE artid = $row[artid]");
	}	
}

$sql = "SELECT * FROM article_fournisseur af INNER JOIN article_marque USING(artid) WHERE marqueid IN(17,26,27)";
$res = $DB_site->query($sql);
while($row = $DB_site->fetch_array($res)){
	$sql2 = "SELECT * FROM article_fournisseur_mec WHERE artid=".$row[artid];
	$res2 = $DB_site->query_first($sql2);
	if(!is_null($res2)){
		echo "UPDATE article_fournisseur SET prix='$res2[prix]',ecopart='$res2[ecopart]',ecopartht='$res2[ecopartht]' WHERE artid = $row[artid]; <br>";
		$DB_site->query("UPDATE article_fournisseur SET prix='$res2[prix]',ecopart='$res2[ecopart]',ecopartht='$res2[ecopartht]' WHERE artid = $row[artid]");
	}
	
}

$sql = "SELECT * FROM article_site INNER JOIN article_marque USING(artid) WHERE marqueid IN(17,26,27)";
$res = $DB_site->query($sql);
while($row = $DB_site->fetch_array($res)){
	$sql2 = "SELECT * FROM article_mec WHERE artid=".$row[artid];
	$res2 = $DB_site->query_first($sql2);
	if(!is_null($res2)){
				   echo "UPDATE article_site SET prix='$res2[prix]',prixconseilleht='$res2[prixconseilleht]' WHERE artid = $row[artid]; <br>";
		$DB_site->query("UPDATE article_site SET prix='$res2[prix]',prixconseilleht='$res2[prixconseilleht]' WHERE artid = $row[artid]");
	}
	
}
*/
// FIN MISE A JOUR TARIF
/*
$sql = "SELECT * FROM article_fournisseur";
$res = $DB_site->query($sql);
while($row = $DB_site->fetch_array($res)){
	$sql2 = "SELECT * FROM article WHERE artid=".$row[artid];
	$res2 = $DB_site->query_first($sql2);
	if(!is_null($res2)){
		echo "UPDATE article SET delailivraison='$row[delai]' WHERE artid = $row[artid]; <br>";
		$DB_site->query("UPDATE article SET delailivraison='$row[delai]' WHERE artid = $row[artid]");
	}
}*/


//$table ="article_tiroir";
/*
$sql = "SELECT couleur,id FROM $table ";
$res = $DB_site->query($sql);
while($row = $DB_site->fetch_array($res)){
	if($row[0] != ''){
		$couleurs = explode(',', $row[0]);
		$new_couleur = array();

		if(count($couleurs) < 2 ){
			$transform = strtolower(str_replace("coloris", "couleur", $couleurs[0]));
			$test = $DB_site->query_first("SELECT `path` FROM traduction WHERE `path` LIKE '$transform'");
			if(!is_null($test)){

				$new_couleur[$transform] = $transform;
		
			}else{
				$test = $DB_site->query_first("SELECT `path` FROM traduction WHERE `path` LIKE '$couleurs[0]'");
				if(!is_null($test)){
					$new_couleur[$transform] = $transform;
					$DB_site->query("UPDATE traduction SET `path` ='$transform' WHERE  `path` ='$couleurs[0]'");
					echo "UPDATE traduction SET `path` ='$transform' WHERE  `path` ='$couleurs[0]'";
				}
			}

		}else{
			$couleurs = explode(',', $row[0]);
			foreach($couleurs as $couleur){
				$transform = strtolower(str_replace("coloris", "couleur", $couleur));
				$test = $DB_site->query_first("SELECT `path` FROM traduction WHERE `path` LIKE '$transform'");
			
				if(!is_null($test)){
					
					$new_couleur[$transform] = $transform;
		
				}else{
					$test = $DB_site->query_first("SELECT `path` FROM traduction WHERE `path` LIKE '$couleur'");
					if(!is_null($test)){
						$new_couleur[$transform] = $transform;
						$DB_site->query("UPDATE traduction SET `path` ='$transform' WHERE  `path` ='$couleur'");
						echo "UPDATE traduction SET `path` ='$transform' WHERE  `path` ='$couleur'";
					}

				}
				
			}

		}
		
			echo "UPDATE $table set couleur = '".implode(',', $new_couleur)."' WHERE id=".$row[1]." <br>";
			$DB_site->query("UPDATE $table set couleur = '".implode(',', $new_couleur)."' WHERE id=".$row[1]);
		
	}

	
}
*/

/*$tables = array("article_bbq","article_cuisiniere","article_piano","article_table_cuisson","article_congel","article_credence","article_divers","article_four","article_frigo","article_hotte","article_meuble","article_tiroir","article_lavevaisselle");

foreach($tables as $table){
	$arts = $DB_site->query("SELECT artid, hauteur, largeur, profondeur FROM $table");
	while ($art=$DB_site->fetch_array($arts)) {
		if(intval($art[hauteur])!=0 && intval($art[largeur])!=0 && intval($art[profondeur])!=0){
			$sql = "UPDATE article SET hauteur='$art[hauteur]',largeur='$art[largeur]',profondeur='$art[profondeur]' where artid=$art[artid]";
			echo $sql."<br>";
			$DB_site->query($sql);

		}
		
	}
	
}*/

//////*** SCRIPT POUR SELECTIONNER LES GARANTIES PAR ARTICLE ****//////
include_once("admin/articles/onglets.php");
include_once("admin/articles/fonctions.php");
include_once("admin/articles/javascript.php");

$articles = $DB_site->query("SELECT a.artid FROM article a");

while($art = $DB_site->fetch_array($articles)){
	$artid = $art[artid];
	$sql = "SELECT COUNT(lotid)";
	$sql .= " FROM article_lot";
	$sql .= " WHERE lotid = ".$artid;
	$lot = $DB_site->query_first($sql);


	if($lot[0] == 0){

		$typearticle       = DAOarticle::typeArticleByIDart($DB_site,'FR',$artid);
		$marque            = DAOarticle::marqueArticleByIDart($DB_site,'FR',$artid);
		$garantiesAvantTri = DAOgarantie::loadGarantiesByTypeMarque($DB_site,null,'FR',$typearticle["typearticleid"],$marque["marqueid"]);
		$garanties = triTableau($garantiesAvantTri,$DB_site,$artid,$nbFourPrin,$nbFourGar,$nbFourSec);
		$DB_site->query("DELETE FROM article_garantie_liste WHERE artid=".$artid.";");
		for($i=0; $i < count($garanties); $i++) {
			$paht = $garanties[$i]->paht;
	        $prix = $garanties[$i]->prixsites;
	        $ppi  = $garanties[$i]->ppisites;
	        $garantieid = $garanties[$i]->garantieid;
	        $devise = $garanties[$i]->devise;

	    	$DB_site->query("INSERT INTO article_garantie_liste SET artid=".$artid.", garantieid=".$garantieid.", paht=".number_format($paht,2).", devise='$devise', prix='".$prix."', ppi='".$ppi."';");

	        echo "INSERT INTO article_garantie_liste SET artid=".$artid.", garantieid=".$garantieid.", paht=".number_format($paht,2).", devise='$devise', prix='".$prix."', ppi='".$ppi."';<br>";
		}
	}else{
		/*$qte = DAOgarantie::lotGaranties($DB_site,$artid);
   		$garantiesAvantTri = DAOgarantie::checkGarantiesArt($DB_site,"FR",$artid);

   		if($garantiesAvantTri > 0){
   			//$DB_site->query("DELETE FROM article_garantie_liste WHERE artid=".$artid.";");

	   		for ($i=0; $i < count($garantiesAvantTri); $i++) {
	            
	            $garanties[$i] = new garantie($DB_site,null,"FR",$garantiesAvantTri[$i]['garantieid'],$artid);

	            $garanties = triTableau($garanties,$DB_site,$artid,$nbFourPrin,$nbFourGar,$nbFourSec);

	            for($i=0; $i<count($garanties); $i++){

	            	$paht = $garanties[$i]->paht;
		            $prix = $garanties[$i]->prixsites;
		            $ppi  = $garanties[$i]->ppisites;
		            $garantieid = $garanties[$i]->garantieid;
	       			$devise = $garanties[$i]->devise;

		            //$DB_site->query("INSERT INTO article_garantie_liste SET artid=".$artid.", garantieid=".$garantieid.", paht=".number_format($paht,2).", devise='$devise', prix='".$prix."', ppi='".$ppi."';");

	        		//echo "INSERT INTO article_garantie_liste SET artid=".$artid.", garantieid=".$garantieid.", paht=".number_format($paht,2).", devise='$devise', prix='".$prix."', ppi='".$ppi."';<br>";

	            }
	        }
	    }*/

	}
}

/*$sql = "SELECT a.artid FROM article a INNER JOIN article_marque USING(artid) WHERE marqueid ='17' ";
while ($art = $DB_site->fetch_array($sql)) {

}*/


function triTableau($tabGarantie,$DB,$artid,&$nbFourPrin,&$nbFourGar,&$nbFourSec) {
     $tabTri     = array();
     $etp        = 1;
     $nbFourPrin = 0;
     $nbFourGar  = 0;
     $nbFourSec  = 0;
     
     while ($etp<=3) {
          
          switch ($etp) {
               
               case 1:
                    
                    $sql = "SELECT DISTINCT(fournisseur)";
                    $sql .= " FROM article_fournisseur";
                    $sql .= " WHERE (principal = 'base' || principal = 'uk' || principal = 'base,uk')";
                    $sql .= " AND artid = ".$artid;
                    $rNbFournisseur = $DB->query($sql);
                    
                    $nbFour = 'nbFourPrin';
                    
                    break;
               
               case 2:
                    
                    $sql = "SELECT DISTINCT(fournisseur)";
                    $sql .= " FROM garantie";
                    $sql .= " WHERE fournisseur IN (";
                    $sql .= "                SELECT DISTINCT(societe)";
                    $sql .= "                FROM fournisseur";
                    $sql .= "                WHERE type = \"garadv\"";
                    $sql .= ")";
                    $rNbFournisseur = $DB->query($sql);
                    
                    $nbFour = 'nbFourGar';
                    
                    break;
               
               case 3:
                    
                    $sql = "SELECT DISTINCT(fournisseur)";
                    $sql .= " FROM article_fournisseur";
                    $sql .= " WHERE principal = ''";
                    $sql .= " AND artid = ".$artid.";";
                    $rNbFournisseur = $DB->query($sql);
                    
                    $nbFour = 'nbFourSec';
                    
                    break;
          }
          
          $nbFournisseur = $DB->num_rows($rNbFournisseur);
          
          if($nbFournisseur>0){
               
               while($fournisseur = $DB->fetch_array($rNbFournisseur)){
                    
                    $pointer = in_object_array($fournisseur[0],$tabGarantie,"fournisseur");

                    if (is_array($pointer)){
                         
                         //variable dynamique qui reçoit le nom de la bonne variable à changer
                         ${$nbFour} += count($pointer);
                         
                         for($i=0; $i < count($pointer); $i++){
                              
                              $tabTri[] = $tabGarantie[$pointer[$i]];
                         }
                    }
               }
          }
          
          $etp++;
     }
     
     return $tabTri;
}

//////*** FIN SCRIPT POUR SELECTIONNER LES GARANTIES PAR ARTICLE ****//////