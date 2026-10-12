<h3>Modifier les codes fournisseurs</h3>
<form method="post">
	<input type="hidden" name="modifCode" value="modifCode"/>
	<table class="table_moyen_paiement" style="border-collapse: collapse;">
		<th align="left">Founisseur de garantie complémentaire</th>
		<th align="left">Code</th>
		<?php
		$founisseursGar=$DB_site2->query("SELECT DISTINCT(societe) FROM fournisseur WHERE type=\"garadv\" ORDER BY societe;");
		while($founisseurGar=$DB_site2->fetch_array($founisseursGar)){
			$codeFourn=$DB_site2->query_first("SELECT code FROM fournisseur_code WHERE fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$founisseurGar['societe'])."';");
			?>
			<tr class="td_location" align="left">
				<td><?php echo $founisseurGar['societe'];?></td>
				<td><input type="text" name="code[<?php echo $founisseurGar['societe'];?>]" value="<?php echo $codeFourn['code'];?>"/></td>
			</tr>
			<?php
		}
		?>
	</table>
	<br/><br/>
	<table class="table_moyen_paiement" style="border-collapse: collapse;">
		<th align="left">Founisseur de garantie/produit</th>
		<th align="left">Code</th>
		<?php
		$founisseursGar=$DB_site2->query("SELECT DISTINCT(societe) FROM fournisseur WHERE type=\"fournadv\" ORDER BY societe;");
		while($founisseurGar=$DB_site2->fetch_array($founisseursGar)){
			$codeFourn=$DB_site2->query_first("SELECT code FROM fournisseur_code WHERE fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$founisseurGar['societe'])."';");
			?>
			<tr class="td_location" align="left">
				<td><?php echo $founisseurGar['societe'];?></td>
				<td><input type="text" name="code[<?php echo $founisseurGar['societe'];?>]" value="<?php echo $codeFourn['code'];?>"/></td>
			</tr>
			<?php
		}
		?>
	</table>
	<input type="submit" value="Terminer"/>
</form>
<br/><a href="garanties.php">Retour</a>