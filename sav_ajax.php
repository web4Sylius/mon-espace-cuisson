<?php
require($_SERVER['DOCUMENT_ROOT'] . "/global.php");

//Si l'utilisateur n'est pas connecté, stoppe le traitement
if(!isset($user_info)) exit;

//Si numéro de commande et type de litige précisés, on traite
if(isset($_GET['facturid']) && isset($_GET['typelitige']))
{
	//Récupération du numéro client associé au numéro de commande
	$sql = "SELECT userid, facturid";
	$sql .= " FROM facture";
	$sql .= " WHERE valide IN (1,5,7,8)";
	$sql .= " AND facturid = " . intval($_GET['facturid']);
	$facture = $DB_site->query_first($sql);

	//Si le client de la commande est bien le même que celui qui fait la demande de SAV, on traite
    if($facture['userid'] == $user_info['userid'])
    {
    	?>
    	<table id="facturelignes" class="listecmd">
			<thead>
				<tr>
					<td><?php echo $template->translate("/sav/ref_produit","F"); ?></td>
					<td><?php echo $template->translate("/sav/libelle","F"); ?></td>
					<td><?php echo $template->translate("/sav/garantie_constructeur","F"); ?></td>
					<td><?php echo $template->translate("/sav/garantie_complementaire","F"); ?></td>
					<td><?php echo $template->translate("/sav/qte_commande","F"); ?></td>
					<td><?php echo $template->translate("/sav/qte_incriminee","F"); ?></td>
				</tr>
			</thead>
    		<?php
    	//Récupration des différents produits de la commande
    	$sql = "SELECT lf.lignefactureid, lf.artcode, lf.libelle, lf.qte, a.garantie_incluse";
    	$sql .= " FROM lignefacture lf";
    	$sql .= " INNER JOIN article a USING (artid)";
    	$sql .= " WHERE lf.factureid = ".intval($facture['facturid']);
		$lignes = $DB_site->query($sql);

		//Boucles sur chacun des produits
		while($ligne=$DB_site->fetch_array($lignes))
		{
			//Récupération de la caractéristique "Garantie" (OBSOLETE !)
			//TODO : Mettre à jour avec le nouveau système de garantie
			$sql = "SELECT lfc.libelle_caract, lfc.libelle_caractval, cv.libelleFR";
			$sql .= " FROM lignefacturecaracteristique lfc";
			$sql .= " INNER JOIN caracteristiquevaleur cv ON (cv.caractvalid=lfc.caractvalid)";
			$sql .= " WHERE cv.caractid = 3";
			$sql .= " AND lfc.lignefactureid = " . $ligne['lignefactureid'];
		    $caract = $DB_site->query_first($sql);

		    if($caract['libelle_caractval'] == "") $caract['libelle_caractval'] = $template->translate("/sav/aucune_garantie","F");
		    if($ligne['garantie_incluse'] == "") $ligne['garantie_incluse'] = $template->translate("/sav/aucune_garantie","F");

		    //Construction de la liste de choix multiple de quantité
		    //Permet de choisir le nombre de produits concernés, ayant la même référence
		    $selectqte = "<select name=\"qte" . $ligne['lignefactureid'] . "\" onchange=\"submit1()\">";
		    for($i=0;$i<=$ligne['qte'];$i++) $selectqte .= "<option value=\"" . $i . "\">" . $i . "</option>";
		    $selectqte .= "</select>";

		    ?>
		    <tr>
		    	<td><?php echo $ligne['artcode']; ?></td>
		    	<td><?php echo $ligne['libelle']; ?></td>
		    	<td><?php echo $ligne['garantie_incluse']; ?></td>
		    	<td><?php echo $caract['libelle_caractval']; ?></td>
		    	<td class="qte"><?php echo $ligne['qte']; ?></td>
		    	<td class="qte"><?php echo $selectqte; ?></td>
		    </tr>
		    <?php
		}
		?>
		</table>
		<?php
		//Récupération de la liste des pays
		$sql = "SELECT pays.libelle";
		$sql .= " FROM fraisport";
		$sql .= " INNER JOIN pays USING(paysid)";
		$sql .= " GROUP BY paysid";
		$sql .= " ORDER BY libelle";
		$requete = $DB_site->query($sql);

		//Construction de la liste déroulante de pays
		$template->parts["optpays"] = "<option value=\"\"></option>";
		while($payslivraison = $DB_site->fetch_array($requete)) $template->parts["optpays"] .= "<option value=\"" . $payslivraison['libelle'] . "\">" . $payslivraison['libelle'] . "</option>";
		$template->parts["optpays"] .= "<option value=\"Autre\">".$template->translate("/sav/autre","F")."</option>";

		//Génère un entier random qui sera ajouté dans un input hidden
		//Permet de sauvegarder les images dans le SAV, sans qu'il existe déjà
		//Une fois créé, on la clé étrangère (contenant ce nombre unique) par l'id du SAV, et c'est réglé
		$template->parts["uniq"] = rand();

		//Nom du template à charger
		$templatehtml = "ClientSAVtype" . $_GET['typelitige'];
		//Stockage du template dans "fields"
		$template->evaltemplate($templatehtml,"fields");

		//Affichage du template
		print stripslashes($template->parts["fields"]);
    }

}

?>