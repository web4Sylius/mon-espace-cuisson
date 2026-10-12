<?php
$display_step = '';

//Si étape 8 déjà traitée, on l'affiche
//Sinon, elle est masquée jusqu'à ce que l'utilisateur ai traité les étapes précédentes
if(isset($adv->steps[8])) $display_step = "style=\"display:block;\"";
?>
<div id="steptrspt8" class="advgroup" <?php echo $display_step; ?>>
	<!-- Contenu chargé en ajax avec la fonction loadStep() - voir fichier "admin/adv/ajax/majDateEnlev.php" -->
</div>