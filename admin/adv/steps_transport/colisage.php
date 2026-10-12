<?php
$display_step = '';

//Si étape 6 déjà traitée, on l'affiche
//Sinon, elle est masquée jusqu'à ce que l'utilisateur ai traité les étapes précédentes
if(isset($adv->steps[6])) $display_step = "style=\"display:block;\"";
?>
<div id="steptrspt6" <?php echo $display_step; ?> class="advgroup">
    <!-- Contenu chargé en ajax avec la fonction loadStep() - voir fichier "admin/adv/ajax/majColisage.php -->
</div>