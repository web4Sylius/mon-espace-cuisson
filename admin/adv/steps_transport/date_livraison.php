<?php
$display_step   = '';

//Si étape 10 déjà traitée, on l'affiche
//Sinon, elle est masquée jusqu'à ce que l'utilisateur ai traité les étapes précédentes
if(isset($adv->steps[10])) $display_step = "style=\"display:block;\"";
?>
<div id="steptrspt10" class="advgroup" <?php echo $display_step; ?>>
	<!-- Contenu chargé en ajax avec la fonction loadStep() - voir fichier "admin/adv/ajax/majDateLivr.php" -->
</div>