<?php
$fourn                = array();
$fournisseur_devise   = array();
$display_step         = '';
$display_trspt        = '';
$display_destinataire = '';

//Si étape 2 déjà traitée, on l'affiche
//Sinon, elle est masquée jusqu'à ce que l'utilisateur ai traité l'étape 1
if(isset($adv->steps[2])) $display_step = "style=\"display:block;\"";

//Si la commande a déjà été transmise au transporteur et qu'on souhaite la modifier, on masque le transporteur
//Quoiqu'il arrive, le transporteur n'est plus modifiable une fois la commande envoyée
if($adv->modif){
	$display_trspt = "style=\"display:none;\"";
	$display_destinataire = $adv->destinataire." (".$adv->fields['typetrspt'].")";
} 

//PARAMS "pas de transport"
$aLibNoTrspt = array("Pas de transport" => 0);
$aFuncNoTrspt = array(
	"fournisseur_save(0)",
	"document.getElementsByName('trsptname')[0].value = '0'",
	"if(document.getElementById('steptrspt3')){ document.getElementById('steptrspt3').style.display='none'; }",
	"if(document.getElementById('steptrspt4')){ document.getElementById('steptrspt4').style.display='none'; }",
	"loadStep('trspt5', '/admin/adv/ajax/majProduits.php?name=0&facturid=".$facturid."&advid=".$adv->id."')", //On saute toutes les autres étapes car "pas de transporteur"
	"adv_save('typetrspt',5)",
	"adv_save('trsptname');"
);

//PARAMS "transporteur de test"
$aLibTrsptTest = array("Acilyon (TEST)" => "webmaster@mon-espace-cuisson.com, webmaster1@mon-espace-cuisson.com, webmaster4@mon-espace-cuisson.com");
$aFuncTrsptTest = array(
	"document.getElementsByName('trsptname')[0].value = 'Acilyon (TEST)'",
	"loadStep('trspt3', '/admin/adv/ajax/majEDI.php?name=Acilyon (TEST)&facturid=".$facturid."&advid=".$adv->id."')",
	"fournisseur_save('Acilyon (TEST)')",
	"adv_save('typetrspt',2)",
	"majStepContenu('#stepTypeTrspt', '/admin/adv/ajax/majTypeTrspt.php?name=Acilyon (TEST)')",
	"majStepContenu('#stepLieuEnlev', '/admin/adv/ajax/majLieuEnlev.php?name=Acilyon (TEST)&facturid=".$facturid."')"
);

//Liste des transporteurs :
//Avec un contact de type "trsptadv" (ATTENTION : si le fournisseur n'a pas de contact de ce type, il ne sera pas proposé dans cette liste)
//Actif
$sql = "SELECT *, fc.devise";
$sql .= " FROM fournisseur";
$sql .= " LEFT OUTER JOIN fournisseur_condition fc USING(societe)";
$sql .= " WHERE type = 'trsptadv'";
$sql .= " AND fc.activite = '1'";
$sql .= " AND fc.types LIKE '%transporteur%'";
$sql .= " ORDER BY societe";
$list = $DB_site2->query($sql);
while($advf = $DB_site2->fetch_array($list)){
    
    if($advf['devise'] == "") $advf['devise'] = $devises->defaut; //Devise € si non renseignée
    if($advf['groupes'] != ""){
		$groups = explode(",", $advf['groupes']);
		foreach($groups as $group){
			$fourn[$group][]            = $advf['mail']; //Clé : nom transporteur | Valeur : mail destinataire
			$fournisseur_devise[$group] = $advf['devise'];
		}
    } else {
		$fourn[$advf['societe']][]            = $advf['mail']; //Clé : nom transporteur | Valeur : mail destinataire
		$fournisseur_devise[$advf['societe']] = $advf['devise'];
    }
}
?>
<div id="steptrspt2" class="advgroup" <?php echo $display_step; ?>>
	<p style="padding:5px; margin:0;">2. Transporteur</p>
	<table <?php echo $display_trspt; ?>>
		<tr>
			<td><?php echo $adv->radio_inputs("typetrspt", $aLibNoTrspt, implode(';',$aFuncNoTrspt)); ?></td>
			<td><?php echo $adv->radio("typetrspt", $aLibTrsptTest, implode(';',$aFuncTrsptTest)); ?></td>
		</tr>
		<?php
		foreach($fourn as $group => $mails){
		    $i++;
		    $c++;
			$mails   = array_unique($mails); //Pour éviter les doublons
			$mailcsv = implode(";",$mails);

			//PARAMS transporteur en cours
			$aLibtrspt = array(ucfirst($group) => $mailcsv);
			$aFuncTrspt = array(
				"adv_save('typetrspt',2)",
				"document.getElementsByName('trsptname')[0].value = '".$group."'",
				"adv_save('trsptname')",
				"loadStep('trspt3', '/admin/adv/ajax/majEDI.php?name=".$group."&facturid=".$facturid."&advid=".$adv->id."')",
				"fournisseur_save('".$group."','".$fournisseur_devise[$group]."')",
				"change_devise('".$fournisseur_devise[$group]."')",
			);
			?>
			<td title="<?php echo $mailcsv; ?>"><?php echo $adv->radio("typetrspt", $aLibtrspt, implode(';',$aFuncTrspt)); ?></td>
			<?php
		        if($c==2){
					$c = 0;
					print "</tr><tr>";
		        }
		}
		?>
	</table>
	<?php echo $display_destinataire; ?>
</div>