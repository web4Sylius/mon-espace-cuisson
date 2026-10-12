<?php

// MULTISITE

// $req=$DB_site2->query("SELECT * FROM article_caractval");
// while($ac=$DB_site2->fetch_array($req)) $DB_site2->query("UPDATE article_caractval SET differenceprixht='".serialize(array('base'=>$ac['differenceprixht']))."', differenceprix='".serialize(array('base'=>$ac['differenceprix']))."' WHERE artid=".$ac['artid']." AND caractvalid=".$ac['caractvalid']);

$contenu_onglet .= "<h3>Caractéristiques</h3>
	<font class=\"cost\">$erreur</font>
	<form name=\"formu\" method=\"post\" action=\"ajout_produit.php\" enctype=\"multipart/form-data\">
	<input type=\"hidden\" name=\"old\" value=\"\">
	<input type=\"hidden\" name=\"del\" value=\"\">
	<input type=\"hidden\" name=\"action2\" value=\"\">
	<input type=\"hidden\" name=\"modif\" value=\"0\">
	<input type=\"hidden\" name=\"quitter\" value=\"\">
	<input type=\"hidden\" name=\"catid\" value=\"$catid\">
	<input type=\"hidden\" name=\"artid\" value=\"$artid\">" ;

	///ne pas afficher les caractéristiques "Couleur RAL"
	$ral_colors = $DB_site2->query("SELECT c.caractid FROM caracteristique c INNER JOIN caracteristiquevaleur cv ON cv.subcaractid = c.caractid");
	$ral=array();
	while($ral_color = mysqli_fetch_array($ral_colors)) {
    	$ral[] = $ral_color['caractid'];
	}
	$ral = implode("','",$ral);

	//verif si lot
	$lot=array();
	$sql_caracteristiques="";
	$req=$DB_site2->query("SELECT artid,qte FROM article_lot WHERE lotid=".$artid);
	$compt=0;
	while($art_lot=$DB_site2->fetch_array($req)){
		$lot[]=array($art_lot['artid'],$art_lot['qte']);
		//Récupération de la marque du produit pour filtrer les caractéristiques
		$sql = "SELECT marqueid";
		$sql .= " FROM article_marque";
		$sql .= " WHERE artid = '".$art_lot['artid']."'";
		$article_marque = $DB_site2->query_first($sql)[0];
		if($compt > 0){
			$union=" UNION ";
		}
		
		$sql_caracteristiques.=$union."SELECT c.caractid, c.$libelle as libelle,c.descriptioninterne as description, cv.caractvalid, cv.$libelle as libval, ac.differenceprixht, ac.image,ac.achat,cv.ref, cv.prix_fixe, cv.pvht, cv.paht, cv.position as pos, c.marqueid, cv.pvttc, ac.differenceprix, c.position
		FROM caracteristique c
		INNER JOIN caracteristiquevaleur as cv USING(caractid)
		LEFT JOIN article_caractval as ac ON(cv.caractvalid = ac.caractvalid)
			WHERE (SELECT COUNT(subcaractid) FROM caracteristiquevaleur WHERE subcaractid=c.caractid )=0 AND ac.artid = '".$art_lot['artid']."' AND c.marqueid = ".$article_marque." AND c.caractid NOT IN ('".$ral."') GROUP BY ac.caractvalid
			";
		$compt++;
	}

	if(!empty($lot)){
		$caracteristiques=$DB_site2->query("SELECT caractid, libelle,description, caractvalid, libval, differenceprixht, image,achat,ref, prix_fixe, pvht, paht, pos, marqueid,pvttc, differenceprix
		FROM (".$sql_caracteristiques. ") t GROUP BY caractvalid ORDER BY marqueid desc,position,pos");
	}
	else{
		//Récupération de la marque du produit pour filtrer les caractéristiques
		$sql = "SELECT marqueid";
		$sql .= " FROM article_marque";
		$sql .= " WHERE artid = '".$artid."'";
		$article_marque = $DB_site2->query_first($sql)[0];

		$caracteristiques=$DB_site2->query("SELECT c.caractid, c.$libelle as libelle,c.descriptioninterne as description, cv.caractvalid, cv.$libelle as libval, ac.differenceprixht, ac.image,ac.achat,cv.ref, cv.prix_fixe, cv.pvht, cv.paht, cv.pvttc, ac.differenceprix
		FROM caracteristique c
		INNER JOIN caracteristiquevaleur as cv USING(caractid)
		LEFT JOIN article_caractval as ac ON(cv.caractvalid = ac.caractvalid AND ac.artid = '$artid')
		WHERE (SELECT COUNT(subcaractid) FROM caracteristiquevaleur WHERE subcaractid=c.caractid)=0 AND c.marqueid = ".$article_marque." AND c.caractid NOT IN ('".$ral."')
		ORDER BY c.position,cv.position");
	}

	$last_caract = "";
	$tr=array();
	$caractv=array();
	$infocolor=array();

if(!is_null($caracteristiques) && $DB_site2->num_rows($caracteristiques) > 0){
	$comptcarac=0;
	while($caracteristique = $DB_site2->fetch_array($caracteristiques)){

		$caractvalid_artid=$DB_site2->query_first("SELECT ac.artid FROM article_caractval ac WHERE ac.artid=".$artid." AND ac.caractvalid=".$caracteristique['caractvalid']);

		$caractlist[$caracteristique['caractid']]=$caracteristique['libelle'];
		$tabDescription[$caracteristique['caractid']] = $caracteristique['description'];
		///pour afficher le titre par changement de marque :
		if($comptcarac == 0 && $lot!=array()){
			$sql = "SELECT libelle FROM marque WHERE marqueid =".$caracteristique['marqueid'];
			$lib_marque = $DB_site2->query_first($sql)[0];
			$caracMarque[$caracteristique['caractid']] = "<div style=\"font-weight:bold;\">Caractéristiques de la marque ".$lib_marque."</div>";
			$titre_marque = $caracteristique['marqueid'];
		}
		if($comptcarac > 0 && $titre_marque !== $caracteristique['marqueid'] && $lot!=array()){
			$sql = "SELECT libelle FROM marque WHERE marqueid =".$caracteristique['marqueid'];
			$lib_marque = $DB_site2->query_first($sql)[0];
			$caracMarque[$caracteristique['caractid']] = "<div style=\"font-weight:bold;margin-top:33px;\">Caractéristiques de la marque ".$lib_marque."</div>";
			$titre_marque = $caracteristique['marqueid'];
		}
		if($caracteristique['differenceprixht'] != NULL) $caractv[$caracteristique['caractid']]=true;

		$caractvaltotal="";
		$infolot="";
		$caractcolor="";
		if($lot!=array()){
			foreach($lot as $article => $art_val){

				$art=$DB_site2->query_first("SELECT differenceprixht,achat, cv.prix_fixe, cv.pvht, cv.paht, cv.pvttc, differenceprix FROM article_caractval INNER JOIN caracteristiquevaleur as cv USING(caractvalid)WHERE caractvalid=".$caracteristique['caractvalid']." AND artid=".intval($art_val[0]));
				if($art!=""){
					if($art['prix_fixe'] == '1'){
						$caractvaltotal["prixht"]+=$art['pvht']*$art_val[1];
						$caractvaltotal["paht"]+=$art["paht"]*$art_val[1];
						$caractvaltotal["pvttc"]+=$art["pvttc"]*$art_val[1];
					}
					else{
						$diffprix = $art["differenceprixht"];
						$caractvaltotal["prixht"]+=$diffprix['base']*$art_val[1];
						$caractvaltotal["pvttc"]+=$art["differenceprix"]*$art_val[1];
						$caractvaltotal["paht"]+=$art["achat"]*$art_val[1];
					}
				}
			}
			if(isset($caractvaltotal["prixht"])){
				$infolot="vente:".round($caractvaltotal["prixht"],4)."&euro;HT, achat:".round($caractvaltotal["paht"],4)."&euro;";
				$caractcolor=$infocolor[$caracteristique['caractid']]="red";
			}
		}

		$disabled = "";
		$styledisabled = "";
		/// si l'option a un prix fixe (fichier caractéristique.php), on ne peut pas modifier les prix ici (tache 7131)
		if($caracteristique['prix_fixe'] == '1'){
			$disabled = "readonly";
			$styledisabled = "background-color:#e9e5e5;";
		}

		$tdvente="";
		$differenceprixht=$caracteristique['differenceprixht'];
		$differenceprix=$caracteristique['differenceprix'];
		//foreach($sites as $site_code=>$site){
		    $tdvente.="<td><input type=\"text\" size=\"5\" name=\"mtmodif[".$caracteristique['caractid']."][".$caracteristique['caractvalid']."]\" value=\"".(isset($caractvaltotal["prixht"]) ? round($caractvaltotal["prixht"],4) : ($caracteristique['prix_fixe'] == '1'?$caracteristique['pvht']:floatval($differenceprixht)))."\" onkeypress=\"nbonly(event,true,this)\" $disabled style=\"$styledisabled\" onkeyup=\"setprixttc_edit(".$caracteristique['caractvalid'].");\" id=\"ht_".$caracteristique['caractvalid']."\"></td>
		    <td><input type=\"text\" size=\"5\" value=\"".(isset($caractvaltotal["pvttc"]) ? round($caractvaltotal["pvttc"],4) : ($caracteristique['prix_fixe'] == '1'?$caracteristique['pvttc']:floatval($differenceprix)))."\" readonly style=\"background-color:#e9e5e5;\" id=\"ttc_".$caracteristique['caractvalid']."\"></td>
		    ";
		//}
		     
		$tr[$caracteristique['caractid']].="<tr>
			    <td style=\"color:".$caractcolor.";\"><input type=\"checkbox\" name=\"chk[".$caracteristique['caractid']."][".$caracteristique['caractvalid']."]\" value=\"1\"".($caracteristique['differenceprixht'] != NULL && !is_null($caractvalid_artid) ? ' checked' : '')."> ".stripslashes($caracteristique['libval'])."</td>
                            <td>".$caracteristique['ref']."</td>
			    $tdvente
			    <td><input type=\"text\" size=\"10\" name=\"achatmodif[".$caracteristique['caractid']."][".$caracteristique['caractvalid']."]\" value=\"".(isset($caractvaltotal["paht"]) ? round($caractvaltotal["paht"],4) : ($caracteristique['prix_fixe'] == '1'?$caracteristique['paht']:$caracteristique['achat']))."\" onkeypress=\"nbonly(event,true,this)\" $disabled style=\"$styledisabled\"></td>
			    <td>$infolot</td>
			</tr>";
		$comptcarac++;
	}

	$thead="<tr><td>Caractéristique</td><td>Référence</td>";
	//foreach($sites as $site_code=>$site)
	    $thead.="<td>Prix site EUR HT</td>";
	$thead.="<td>Prix vente TTC</td><td>Prix achat HT</td><td>Info lot</td></tr>";
	
	foreach($caractlist as $caractid=>$libelle){
		if(isset($caractv[$caractid])){
			$icon="images/moins.jpg";
			$style="";
		}else{
			$icon="images/plus.jpg";
			$style="display:none";
		}

		$tauxfrance = $DB_site2->query_first("SELECT TVAtauxnormal FROM pays WHERE paysid=57");

		$contenu_onglet.=$caracMarque[$caractid]."<h3 style=\"text-align:left;color:".$infocolor[$caractid].";\"><img src=\"".$icon."\" id=\"icon".$caractid."\" align=\"absmiddle\" onclick=\"showcaract(".$caractid.")\"> ".stripslashes($libelle)." <span style=\"color:black;font-size:11px;\">($tabDescription[$caractid])</span></h3> 
		<div id=\"caract".$caractid."\" style=\"".$style."\">
		<a href=\"Javascript: resize2(".$caractid.") ;\" alt=\"Ajouter une valeur pour ".$libelle."\" title=\"Ajouter une valeur pour ".$libelle."\">Ajouter une valeur</a>
		<table width=\"100%\">"
		.$thead;
		$contenu_onglet.=$tr[$caractid];
		$contenu_onglet .= "</table></div>

		<script>
		function setprixttc_edit(ht) {
			var tva = ".$tauxfrance[0].";
			var val_ht = \$jQuery('#ht_'+ht).val();
			\$jQuery('#ttc_'+ht).val(Math.round(val_ht * (1 + tva / 100) * 100) / 100);
		}
		</script>" ;
	}
}

?>
