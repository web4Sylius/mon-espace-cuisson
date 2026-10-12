<?php
$display_step = '';

//Si étape 3 déjà traitée, on l'affiche
//Sinon, elle est masquée jusqu'à ce que l'utilisateur ai traité les étapes précédentes
if(isset($adv->steps[3])) $display_step = "style=\"display:block;\"";
?>
<div id="steptrspt3" <?php echo $display_step; ?> class="advgroup">
	<!-- Contenu chargé en ajax avec la fonction majStepContenu() dans transporteur.php - voir fichier "admin/adv/ajax/majEDI.php" -->
</div>