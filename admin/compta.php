<?php

include("admin_global.php");

include "./header.php";
include "./menu.php";

print "<LINK href=\"cal.css\" type=\"text/css\" rel=\"stylesheet\">
<script language=\"javascript\" src=\"calendrier.js\"></script>
<script language=\"javascript\">
initcalendrier();

var filetype='compta2';
function compta(){
         err='';
         if(document.getElementById('du').value=='') err='Il faut donner une date de début';
         if(document.getElementById('au').value=='') err='Il faut donner une date de fin';
         if(err==\"\")
             document.location='export/export_'+filetype+'.php?du='+document.getElementById('du').value+'&au='+document.getElementById('au').value;
         else
             alert(err);
}
</script>";

echo "<h1>Comptablilté</h1>";

echo "
Exporter du <input type=\"text\" id=\"du\" size=\"8\" onclick=\"calendrier_show('du',this.value)\" readonly><a href=\"javascript:calendrier_show('du',document.getElementById('du').value)\"><img src=\"images/icone_calendrier.jpg\" width=\"32\" border=\"0\" align=\"absmiddle\"></a> &nbsp; au <input type=\"text\" id=\"au\" size=\"8\" onclick=\"calendrier_show('au',this.value)\" readonly><a href=\"javascript:calendrier_show('au',document.getElementById('au').value)\"><img src=\"images/icone_calendrier.jpg\" width=\"32\" border=\"0\" align=\"absmiddle\"></a> 
<p><input type=\"radio\" name=\"type\" onclick=\"filetype='compta';\">Fichier commandes client détaillé
<br><input type=\"radio\" name=\"type\" onclick=\"filetype='compta2';\" checked>Fichier factures client pour comptable
<br><input type=\"radio\" name=\"type\" onclick=\"filetype='fournisseur';\">Fichier factures fournisseur pour comptable
<br><input type=\"radio\" name=\"type\" onclick=\"filetype='sorties_stock';\">Sorties de stock
<br><input type=\"radio\" name=\"type\" onclick=\"filetype='compta_fournisseur';\">Fichier factures clients fournisseurs associées
<br><input type=\"radio\" name=\"type\" onclick=\"filetype='cutoff';\">Fichier factures fournisseur en attente pour situation
<br><input type=\"radio\" name=\"type\" onclick=\"filetype='frais';\">Frais généraux
<br><input type=\"radio\" name=\"type\" onclick=\"filetype='cheque';\">Remise chèque
<br><input type=\"radio\" name=\"type\" onclick=\"filetype='facilite_paiement';\">Facilité de paiement
<br><input type=\"radio\" name=\"type\" onclick=\"filetype='reajustement';\">Réajustement
<br><input type=\"radio\" name=\"type\" onclick=\"filetype='cutoff';\">FNP</p>
<p><input type=\"button\" value=\"OK\" onclick=\"compta()\"></p>";


include "./footer.php";

?>