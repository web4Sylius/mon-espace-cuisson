<?php
header('Content-type: text/html; charset=iso-8859-1'); 
require ("global.php");

$artid=intval($_GET['artid']);

$article_price=article_prices($artid,$template->tva_ref(),$template->site_code(),$sitepro);
$prix_encours = $article_price['prix'];
var_dump($liste_caractvalid);
$valeur = explode(",",$liste_caractvalid);

for ($i=0;$i<count($valeur);$i++)
{
	if ($valeur[$i] != "0")
	{

		$modifprix = $DB_site->query_first("SELECT ac.differenceprix, cv.pvttc, cv.prix_fixe, cv.caractvalid FROM article_caractval ac INNER JOIN caracteristiquevaleur cv ON cv.caractvalid = ac.caractvalid WHERE ac.artid = '$artid' AND ac.caractvalid = '$valeur[$i]'");
		if(is_null($modifprix)){
			$caracteid = $DB_site->query_first("SELECT caractid FROM `caracteristiquevaleur` WHERE `caractvalid` = '$valeur[$i]'");
			$caracvals = $DB_site->query("SELECT caractvalid FROM `caracteristiquevaleur` WHERE `subcaractid` = ".$caracteid['caractid']);
			$caracvals_liste="";
			foreach($caracvals as $caracv){
				$caracvals_liste[]=$caracv['caractvalid'];
			}
			$caracvals=implode("','",$caracvals_liste);
			$caracval = $DB_site->query_first("SELECT caractvalid FROM `article_caractval` WHERE `artid` = '".$artid."' AND `caractvalid` IN ('".$caracvals."')");
			$modifprix = $DB_site->query_first("SELECT ac.differenceprix, cv.pvttc, cv.prix_fixe, cv.caractvalid FROM article_caractval ac INNER JOIN caracteristiquevaleur cv ON cv.caractvalid = ac.caractvalid WHERE ac.artid = '$artid' AND ac.caractvalid = '".$caracval['caractvalid']."'");
		}


	/////si prix fixe ou prix variable
		//pour la couleur RAL
		$caract_ral=$DB_site->query_first("select cr.caractvalid from caractval_ral cr INNER JOIN caracteristiquevaleur cv ON cv.caractvalid = cr.caractvalid WHERE cr.caractvalid =".$modifprix['caractvalid']);
		if(!is_null($caract_ral)){
			///récupère le type de couleur ral (mat ou brillant)
			$caract_id = $DB_site->query_first("SELECT caractid FROM `caracteristiquevaleur` WHERE `caractvalid` =".$caract_ral['caractvalid']);
			$prix_caract_ral=$DB_site->query_first("select cv.pvttc, cv.prix_fixe, ac.differenceprix from caracteristiquevaleur cv INNER JOIN article_caractval ac ON ac.caractvalid = cv.caractvalid WHERE ac.artid = '".$artid."' AND cv.subcaractid = ".$caract_id['caractid']);

			//Si lot, on additionne les prix
			$lot_prix=array();
			$caractvaltotal="";
			$req_lot=$DB_site->query("SELECT artid,qte FROM article_lot WHERE lotid=".$artid);
			while($art_lot=$DB_site->fetch_array($req_lot)){
				$lot_prix[]=array($art_lot['artid'],$art_lot['qte']);
			}
			if($lot_prix!=array()){
				foreach($lot_prix as $articleid => $art_val){
					$art=$DB_site->query_first("SELECT differenceprix, differenceprixht, cv.prix_fixe, cv.pvttc, cv.pvht FROM article_caractval INNER JOIN caracteristiquevaleur as cv USING(caractvalid)WHERE caractvalid=".$caract_ral['caractvalid']." AND artid=".intval($art_val[0]));

					if($art!=""){
						if($art['prix_fixe'] == '1'){
							$caractvaltotal["prixlot"]+=$art['pv'.($sitepro?'ht':'ttc')]*$art_val[1];
						}
						else{
							$diffprix = $art['differenceprix'.($sitepro?'ht':'')];
							$caractvaltotal["prixlot"]+=$diffprix['base']*$art_val[1];
						}
					}
				}
			}
			if(isset($caractvaltotal["prixlot"])){
				$caract_price = round($caractvaltotal["prixlot"],4);
			}
			else{
				if($prix_caract_ral['prix_fixe'] == '1'){
					$caract_price=caract_prices($prix_caract_ral['pvttc'],$template->tva_ref(),$sitepro);
				}
				else{
					$caract_price=caract_prices($prix_caract_ral['differenceprix'],$template->tva_ref(),$sitepro);
				}
			}
		}
		//fin couleur RAL
		else{
			//Si lot, on additionne les prix
			$lot_prix=array();
			$caractvaltotal="";
			$req_lot=$DB_site->query("SELECT artid,qte FROM article_lot WHERE lotid=".$artid);
			while($art_lot=$DB_site->fetch_array($req_lot)){
				$lot_prix[]=array($art_lot['artid'],$art_lot['qte']);
			}
			if($lot_prix!=array()){
				foreach($lot_prix as $articleid => $art_val){
					$art=$DB_site->query_first("SELECT differenceprix, differenceprixht, cv.prix_fixe, cv.pvttc, cv.pvht FROM article_caractval INNER JOIN caracteristiquevaleur as cv USING(caractvalid)WHERE caractvalid=".$modifprix['caractvalid']." AND artid=".intval($art_val[0]));
					if($art!=""){
						if($art['prix_fixe'] == '1'){
							$caractvaltotal["prixlot"]+=$art['pv'.($sitepro?'ht':'ttc')]*$art_val[1];
						}
						else{
							$diffprix = $art['differenceprix'.($sitepro?'ht':'')];
							$caractvaltotal["prixlot"]+=$diffprix['base']*$art_val[1];
						}
					}
				}
			}
			if(isset($caractvaltotal["prixlot"])){
				$caract_price = round($caractvaltotal["prixlot"],4);
			}
			else{
				if($modifprix['prix_fixe'] == '1'){
					$caract_price=caract_prices($modifprix['pvttc'],$template->tva_ref(),$sitepro);
				}
				else{
					$caract_price=caract_prices($modifprix['differenceprix'],$template->tva_ref(),$sitepro);
				}
			}
		}
		$prix_encours+=$caract_price;
	}
}
			

echo "$artid|||".$template->price_format($prix_encours)."|||$prix_encoursht|||$test" ;
?>
