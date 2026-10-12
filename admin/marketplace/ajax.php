<?php

require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");

if(isset($_GET['client'])){

	if(is_numeric($_GET['client'])){
		$info = $DB_site2->query_first("SELECT * FROM `utilisateur` WHERE userid=".$_GET['client']);
		$requete = $DB_site2->query("SELECT paysid, libelle FROM pays ORDER BY libelle");
	    while($pays = $DB_site2->fetch_array($requete)){
			$optbcountries.="<option value=\"".$pays['paysid']."\" ".($pays['paysid'] == $info['paysid'] ? 'selected' : '').">".$pays['libelle']."</option>";
			$optscountries.="<option value=\"".$pays['paysid']."\" ".($pays['paysid'] == $info['paysid'] ? 'selected' : '').">".$pays['libelle']."</option>";
	    }

		?>
		<table class="frameAdr">
			<tr>
				<td></td>
				<td height="25" class="frametitle">Adresse de facturation</td>
				<td height="25" class="frametitle">Adresse de livraison</td>
			</tr>
			<tr>
				<td>Société</td>
				<td><input type="text" name="billing_company" size="40" value="<?php echo $info['raisonsociale']; ?>" class="inputFac" readonly></td>
				<td><input type="text" name="shipping_company" size="40" value="<?php echo $info['raisonsociale']; ?>"></td>
			</tr>
			
			<tr>
				<td>Nom</td>
				<td><input type="text" name="billing_last_name" size="40" value="<?php echo $info['nom']; ?>" class="inputFac" readonly></td>
				<td><input type="text" name="shipping_last_name" size="40" value="<?php echo $info['nom']; ?>"></td>
			</tr>
			<tr>
				<td>Prénom</td>
				<td><input type="text" name="billing_first_name" size="40" value="<?php echo $info['prenom']; ?>" class="inputFac" readonly></td>
				<td><input type="text" name="shipping_first_name" size="40" value="<?php echo $info['prenom']; ?>"></td>
			</tr>
			<tr>
				<td>Adresse</td>
				<td><input type="text" name="billing_address" size="40" value="<?php echo $info['adresse']; ?>" class="inputFac" readonly></td>
				<td><input type="text" name="shipping_address" size="40" value="<?php echo $info['adresse']; ?>"></td>
			</tr>
			<tr>
				<td>Code postal</td>
				<td><input type="text" name="billing_zip" size="40" value="<?php echo $info['codepostal']; ?>" class="inputFac" readonly></td>
				<td><input type="text" name="shipping_zip" size="10" value="<?php echo $info['codepostal']; ?>"></td>
			</tr>
			<tr>
				<td>Ville</td>
				<td><input type="text" name="billing_city" size="40" value="<?php echo $info['ville']; ?>" class="inputFac" readonly></td>
				<td><input type="text" name="shipping_city" size="40" value="<?php echo $info['ville']; ?>"></td>
			</tr>
			<tr>
				<td>Pays</td>
				<td><select name="billing_country" class="inputFac" readonly><?php echo $optbcountries ?></select></td>
				<td><select name="shipping_country"><?php echo $optscountries ?></select></td>
			</tr>
			<tr>
				<td>Téléphone</td>
				<td><input type="text" name="billing_phone" size="40" value="<?php echo $info['telephone']; ?>" class="inputFac" readonly></td>
				<td><input type="text" name="shipping_phone" size="40" value="<?php echo $info['telephone']; ?>"></td>
			</tr>
			<tr>
				<td>E-mail</td>
				<td><input type="text" name="billing_mail" size="40" value="<?php echo $info['mail']; ?>" class="inputFac" readonly></td>
				<td><input type="text" name="shipping_mail" size="40" value="<?php echo $info['mail']; ?>"></td>
			</tr>
			<tr>
				<td>Infos trspt</td>
				<td></td>
				<td><textarea name="infotrspt" rows="4" cols="30"></textarea></td>
			</tr>
			<tr>
				<td colspan="2"><br></td>
				<td><span id="err"></td>
			</tr>
			<tr>
				<td><input type="hidden" name="userid" value="<?php echo $_GET['client']; ?>"></td>
			</tr>
			<tr>
	        	<td colspan="3"><div id="cal_ok" class="cal_ok" onclick="checkAdr();"><span>SUIVANT </span></div></td>
	      	</tr>
		</table>
		<?php

	}elseif($_GET['client'] == "add"){
		$requete = $DB_site2->query("SELECT paysid, libelle FROM pays ORDER BY libelle");
	    while($pays = $DB_site2->fetch_array($requete)){
			$optbcountries.="<option id=\"".$pays['paysid']."\" value=\"".$pays['paysid']."\" ".($pays['paysid'] == 57 ? 'selected' : '').">".$pays['libelle']."</option>";
			$optscountries.="<option id=\"".$pays['paysid']."\" value=\"".$pays['paysid']."\" ".($pays['paysid'] == 57 ? 'selected' : '').">".$pays['libelle']."</option>";
	    }

		?>
		<table class="frameAdr">
			<tr>
				<td></td>
				<td height="25" class="frametitle">Adresse de facturation</td>
				<td height="25" class="frametitle">Adresse de livraison</td>
			</tr>
			<tr>
				<td>Société</td>
				<td><input type="text" name="billing_company" size="40" value=""></td>
				<td><input type="text" name="shipping_company" size="40" value=""></td>
			</tr>
			<tr>
				<td>Civilité</td>
				<td>
					<input type="radio" name="billing_civi" value="0" checked >M. 
					<input type="radio" name="billing_civi" value="1"> Mme
				</td>
				<td>
					<input type="radio" name="shipping_civi" value="0" checked >M. 
					<input type="radio" name="shipping_civi" value="1"> Mme
				</td>				
			</tr>
			<tr>
				<td>Nom</td>
				<td><input type="text" name="billing_last_name" size="40" value=""></td>
				<td><input type="text" name="shipping_last_name" size="40" value=""></td>
			</tr>
			<tr>
				<td>Prénom</td>
				<td><input type="text" name="billing_first_name" size="40" value=""></td>
				<td><input type="text" name="shipping_first_name" size="40" value=""></td>
			</tr>
			<tr>
				<td>Adresse</td>
				<td><input type="text" name="billing_address" size="40" value=""></td>
				<td><input type="text" name="shipping_address" size="40" value=""></td>
			</tr>
			<tr>
				<td>Code postal</td>
				<td><input type="text" name="billing_zip" size="10" value=""></td>
				<td><input type="text" name="shipping_zip" size="10" value=""></td>
			</tr>
			<tr>
				<td>Ville</td>
				<td><input type="text" name="billing_city" size="40" value=""></td>
				<td><input type="text" name="shipping_city" size="40" value=""></td>
			</tr>
			<tr>
				<td>Pays</td>
				<td><select name="billing_country"><?php echo $optbcountries ?></select></td>
				<td><select name="shipping_country"><?php echo $optscountries ?></select></td>
			</tr>
			<tr>
				<td>Téléphone</td>
				<td><input type="text" name="billing_phone" size="40" value=""></td>
				<td><input type="text" name="shipping_phone" size="40" value=""></td>
			</tr>
			<tr>
				<td>E-mail</td>
				<td><input type="text" name="billing_mail" size="40" value=""></td>
				<td><input type="text" name="shipping_mail" size="40" value=""></td>
			</tr>
			<tr>
				<td>Infos trspt</td>
				<td></td>
				<td><textarea name="infotrspt" rows="4" cols="30"></textarea></td>
			</tr>
			<tr>
				<td colspan="2"></td>
				<td><input type="checkbox" name="copy" onclick="copybilling();"> Identique à l'adresse de facturation</td>
			</tr>
			<tr>
				<td colspan="2"><br></td>
				<td><span id="err"></td>
			</tr>
			<tr>
	        	<td colspan="3"><div id="cal_ok" class="cal_ok" onclick="checkAdr();"><span>SUIVANT </span></div></td>
	      	</tr>
	      	<tr>
	      		<td><input type="hidden" name="nb_produit" value=""></td>
	      	</tr>
		</table>
		<?php
	}
	
}
