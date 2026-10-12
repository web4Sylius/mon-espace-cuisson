<?php
$display_step = '';
$aProduits    = array();

//Si étape 5 déjà traitée, on l'affiche
//Sinon, elle est masquée jusqu'à ce que l'utilisateur ai traité les étapes précédentes
if(isset($adv->steps[5])) $display_step = "style=\"display:block;\"";
?>
<div id="steptrspt5" <?php echo $display_step; ?> class="advgroup">
    <!-- Contenu chargé en ajax avec la fonction majStepContenu() dans transporteur.php - voir fichier "admin/adv/ajax/majProduits.php -->
</div>