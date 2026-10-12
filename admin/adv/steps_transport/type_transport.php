<?php
$display_step = '';

//Si étape 4 déjà traitée, on l'affiche
//Sinon, elle est masquée jusqu'à ce que l'utilisateur ai traité les étapes précédentes
if(isset($adv->steps[4])) $display_step = "style=\"display:block;\"";
?>
<div id="steptrspt4" class="advgroup" <?php echo $display_step; ?>>
    <!-- Contenu chargé en ajax avec la fonction majStepContenu() dans transporteur.php - voir fichier "admin/adv/ajax/majTypeTrspt.php -->
</div>