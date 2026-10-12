<?php
$display_step = '';

//Si étape 13 déjà traitée, on l'affiche
//Sinon, elle est masquée jusqu'à ce que l'utilisateur ai traité les étapes précédentes
if(isset($adv->steps[13])) $display_step = "display:block;";
?>
<div id="steptrspt13" style="text-align: center; padding: 10px; background-color: #fff; <?php echo $display_step; ?>" class="advgroup">
	<!-- Contenu chargé en ajax avec la fonction loadStep() - voir fichier "admin/adv/ajax/majValidation.php" -->
</div>