<?php

// MULTISITE

//Liste déroulante des fournisseurs
$optsoc.="<option></option>";
$r=$DB_site2->query("SELECT DISTINCT(societe),devise FROM fournisseur INNER JOIN fournisseur_condition USING(societe) WHERE fournisseur.type=\"fournadv\" OR fournisseur.type=\"garadv\" ORDER BY societe");
while($soc=$DB_site2->fetch_array($r)){
	$optsoc.="<option value=\"".$soc['societe']."|".$soc['devise']."|".$devises->symbole($soc['devise'])."\">".$soc['societe']."</option>";
}

//Liste déroulante des types de garanties
$optgar.="<option></option>";
$r=$DB_site2->query("SELECT id_type,libelle_type FROM garantie_type");
while($gar=$DB_site2->fetch_array($r)){
	$optgar.="<option value=\"".$gar['id_type']."\">".$gar['libelle_type']."</option>";
}

//Liste déroulantes des marques
$optmar.="<option></option>";
$r=$DB_site2->query("SELECT marqueid,libelle FROM marque ORDER BY libelle");
$cptMarque=0;
while($marques=$DB_site2->fetch_array($r)){
	$tabMarques[] = $marques;
	$optmar.="<option value=\"".$marques['marqueid']."\">".$marques['libelle']."</option>";
}

//Checkboxes des types de produits
$r=$DB_site2->query("SELECT typearticleid,libelle FROM typearticle ORDER BY libelle");
$cptType=0;
while($types=$DB_site2->fetch_array($r)){
	$aCheckTypeArticle[] = $types;
}
$nbTypesArticles     = count($aCheckTypeArticle);
$nbTypesArticlesCols = ceil($nbTypesArticles/3);

//Checkboxes des pays
$r=$DB_site2->query("SELECT paysid,libelle FROM pays WHERE typepays=\"EE\" ORDER BY libelle");
$cptPays=0;
while($pays=$DB_site2->fetch_array($r)){
	$aCheckPays[] = $pays;
}
$nbPays     = count($aCheckPays)+2; //+2 pour "reste du monde" et "reste de l'europe"
$nbPaysCols = ceil($nbPays/3);

?>
<h3>Ajouter une garantie</h3>

<form method="post" action="garanties.php" name="frmGarantie" onsubmit="return valideFrmGarantie();">
    <input type="hidden" name="addBDD" value="addBDD"/>
        
    <div id="onglet_garantie" class="admin_onglet onglet_actif" onclick="affiche_div(this, 'garantie');">Garantie</div>
	<div id="onglet_tarifs" class="admin_onglet" onclick="affiche_div(this, 'tarifs');">Tarifs</div>
    <div id="onglet_marques" class="admin_onglet" onclick="affiche_div(this, 'marques');">Marques</div>
    <div id="onglet_typesarticles" class="admin_onglet" onclick="affiche_div(this, 'types_articles');">Types d'articles</div>
    <div id="onglet_pays" class="admin_onglet" onclick="affiche_div(this, 'pays');">Pays</div>
    <div class="clear"></div>
	 
	<!-- DEBUT ONGLET GARANTIE -->
    <div id="garantie" class="contenus" style="display: block;">
        <table style="width: 100%;">
			<tr>
				<td style="width: 120px;"><strong>Prestataire</strong> :</td>
				<td>
					<input type="radio" name="utilisation_garantie" value="0" onclick="jQuery('#m1').show();jQuery('#m2').hide();"/> Fournisseur *
					<input type="radio" name="utilisation_garantie" value="1" onclick="jQuery('#m1').show();jQuery('#m2').hide();"/> Complémentaire **
					<input type="radio" name="utilisation_garantie" value="2" onclick="jQuery('#m1').hide();jQuery('#m2').show();" /> Abonnement ***
				</td>
			</tr>
			<tr>
				<td><strong>Fournisseur</strong> : </td>
				<td>
					<input type="hidden" name="fournisseur">
					<input type="hidden" name="devise">
					<select onchange="selectfournisseur(this.value)"><?php echo $optsoc; ?>
				</td>
			</tr>
			<tr>
				<td><strong>Destinataires</strong> :</td>
				<td>
					<input type="radio" name="destinataire_garantie" value="1"/> Particuliers 
					<input type="radio" name="destinataire_garantie" value="2"/> Professionnels 
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><strong>Extension</strong> :</td>
				<td>
					<input type="radio" name="cumul" value="1"/> Oui
					<input type="radio" name="cumul" value="2"/> Non
				</td>
			</tr>
			<tr>
				<td><strong>Durée</strong> :</td>
				<td>
					<input type="text" name="duree" style="width:25px" onkeypress="nbonly(event,true,this);"> ans(s) (à vie = 99ans)
				</td>
			</tr>
			<tr>
				<td><strong>Couverture</strong> :</td>
				<td>
					<select name="type_garantie"><?php echo $optgar; ?></select>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td style="vertical-align: top;"><strong>Commentaire(s)</strong> :</td>
				<td>
					<textarea style="resize: none;" name="comm" cols="30" rows="7"></textarea>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2">
					* Fournisseur de l'article garantie (Dometic, Climadiff, Eurocave, etc...)
					<br>
					** Autre organisme (SFG, Enigma, etc...)
					<br> 
					*** Platine Home (Abonnement SFG)
				</td>
			</tr>
		</table>
	</div>
	<!-- FIN ONGLET GARANTIE -->
	
	<!-- DEBUT ONGLET TARIFS -->
	<div id="tarifs" class="contenus">
		<p><center>A remplir après création de la garantie</center></p>
	</div>
	<!-- FIN ONGLET TARIFS -->
	
	<!-- DEBUT ONGLET MARQUES -->
	<div id="marques" class="contenus">
		<table id="m1" class="tbl_checkbox" align="center">
			<tr>
				<td><strong>Marques</strong> :</td>
				<td>
                    <select name="marques1"><?php echo $optmar; ?></select>
				</td>
			</tr>
		</table>
		<table id="m2" class="tbl_checkbox" align="center" style="display: none;">
			<tr>
				<td><input type="checkbox" onClick="checkAll(this,'marques[]')"><strong>Tout cocher</strong><br/></td>
			</tr>
			<tr>
				<td>
					<?php
					$compteur = 0;
					$cptMarque = round(count($tabMarques)/3);
					$nbMax = $cptMarque;
					for($i=$compteur;$i<$nbMax;$i++){
						$compteur++;
						echo "<input type=\"checkbox\" name=\"marques[]\" id=\"marque_".$compteur."\" value=\"".$tabMarques[$i]['marqueid']."\" ".$hidden." ".$checked." /><span ".$gras.">".$tabMarques[$i]['libelle']."</span><br/>";
					}
					?>
				</td>
				<td>
					<?php
					$nbMax = $cptMarque + $compteur;
					for($i=$compteur;$i<$nbMax;$i++){
						$compteur++;
						echo "<input type=\"checkbox\" name=\"marques[]\" id=\"marque_".$compteur."\" value=\"".$tabMarques[$i]['marqueid']."\" ".$hidden." ".$checked." /><span ".$gras.">".$tabMarques[$i]['libelle']."</span><br/>";
					}
					?>
				</td>
				<td>
					<?php
					$nbMax = $cptMarque + $compteur > count($tabMarques) ? count($tabMarques) : $cptMarque + $compteur;
					for($i=$compteur;$i<$nbMax;$i++){
						$compteur++;
						echo "<input type=\"checkbox\" name=\"marques[]\" id=\"marque_".$compteur."\" value=\"".$tabMarques[$i]['marqueid']."\" ".$hidden." ".$checked." /><span ".$gras.">".$tabMarques[$i]['libelle']."</span><br/>";
					}

					?>
				</td>
			</tr>
		</table>
	</div>
	<!-- FIN ONGLET MARQUES -->
	 
	<!-- DEBUT ONGLET TYPES ARTICLES -->
    <div id="types_articles" class="contenus">
		<table class="tbl_checkbox" align="center">
			<tr>
				<td><input type="checkbox" onClick="checkAll(this,'types_produits[]')"><strong>Tout cocher</strong><br/></td>
			</tr>
			<tr>
				<td>
					<?php
					$compteur = 0;
					$nbMax = $nbTypesArticlesCols;
					for($i=$compteur;$i<$nbMax;$i++){
						$compteur++;
						echo "<input type=\"checkbox\" name=\"types_produits[]\" id=\"types_produits_".$compteur."\" value=\"".$aCheckTypeArticle[$i]['typearticleid']."\" ".$hidden." ".$checked." /><span ".$gras.">".$aCheckTypeArticle[$i]['libelle']."</span><br/>";
					}
					?>
				</td>
				<td>
					<?php
					$nbMax = $nbTypesArticlesCols + $compteur;
					for($i=$compteur;$i<$nbMax;$i++){
						$compteur++;
						echo "<input type=\"checkbox\" name=\"types_produits[]\" id=\"types_produits_".$compteur."\" value=\"".$aCheckTypeArticle[$i]['typearticleid']."\" ".$hidden." ".$checked." /><span ".$gras.">".$aCheckTypeArticle[$i]['libelle']."</span><br/>";
					}
					?>
				</td>
				<td>
					<?php
					$nbMax = $nbTypesArticlesCols + $compteur > count($aCheckTypeArticle) ? count($aCheckTypeArticle) : $nbTypesArticlesCols + $compteur;
					for($i=$compteur;$i<$nbMax;$i++){
						$compteur++;
						echo "<input type=\"checkbox\" name=\"types_produits[]\" id=\"types_produits_".$compteur."\" value=\"".$aCheckTypeArticle[$i]['typearticleid']."\" ".$hidden." ".$checked." /><span ".$gras.">".$aCheckTypeArticle[$i]['libelle']."</span><br/>";
					}
					?>
				</td>
			</tr>
		</table>
    </div>
    <!-- FIN ONGLET TYPES ARTICLES --> 
	
	<!-- DEBUT ONGLET PAYS -->
    <div id="pays" class="contenus">
        <table class="tbl_checkbox" align="center">
			<tr>
				<td><input type="checkbox" onClick="checkAll(this,'pays[]')"><strong>Tout cocher</strong><br/></td>
			</tr>
			<tr>
				<td>
					<?php
					$compteur = 0;
					$nbMax = $nbPaysCols;
					for($i=$compteur;$i<$nbMax;$i++){
						$compteur++;
						echo "<input type=\"checkbox\" name=\"pays[]\" id=\"pays_".$compteur."\" value=\"".$aCheckPays[$i]['paysid']."\"/>".$aCheckPays[$i]['libelle']."<br/>";
					}
					?>
				</td>
				<td>
					<?php
					$nbMax = $nbPaysCols + $compteur;
					for($i=$compteur;$i<$nbMax;$i++){
						$compteur++;
						echo "<input type=\"checkbox\" name=\"pays[]\" id=\"pays_".$compteur."\" value=\"".$aCheckPays[$i]['paysid']."\"/>".$aCheckPays[$i]['libelle']."<br/>";
					}
					?>
				</td>
				<td>
					<?php
					$nbMax = $nbPaysCols + $compteur > count($aCheckPays) ? count($aCheckPays) : $nbPaysCols + $compteur;
					for($i=$compteur;$i<$nbMax;$i++){
						$compteur++;
						echo "<input type=\"checkbox\" name=\"pays[]\" id=\"pays_".$compteur."\" value=\"".$aCheckPays[$i]['paysid']."\"/>".$aCheckPays[$i]['libelle']."<br/>";
					}
					?>
					<input type="checkbox" name="pays[]" value="RE" id="pays_<?php echo $cptPays+1; ?>"/>Reste de l'Europe<br/>
					<input type="checkbox" name="pays[]" value="RM" id="pays_<?php echo $cptPays+2; ?>"/>Reste du Monde
				</td>
			</tr>
		</table>
    </div>
    <!-- FIN ONGLET PAYS -->
	
	<p style="width: 600px; text-align: center;"><input type="submit" class="skined" style="width:230px;" value="Ajouter"></p>
	<br>
	<?php if(!empty($error)) echo "<span style=\"color:red\">".$error."</span>"; ?>
</form>

<br/>
<a style="color: #363E42; font-weight: bold; font-size: 13px; margin: 10px 0 0 10px; float: left;" href="garanties.php"><img src="images/icon/previous.png" style="vertical-align: -8px; margin: 0 5px 0 0;">Retour</a>