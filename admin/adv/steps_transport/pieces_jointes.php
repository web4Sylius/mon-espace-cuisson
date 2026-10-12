<?php
$display_step   = '';

//Si étape 11 déjà traitée, on l'affiche
//Sinon, elle est masquée jusqu'à ce que l'utilisateur ai traité les étapes précédentes
if(isset($adv->steps[11])) $display_step = "style=\"display:block;\"";
?>
<div id="steptrspt11" class="advgroup"<?php echo (isset($adv->steps[11]) ? " style=\"display:block;\"" : ""); ?>>
	<!-- Contenu chargé en ajax avec la fonction loadStep() - voir fichier "admin/adv/ajax/majPJ.php" -->
</div>