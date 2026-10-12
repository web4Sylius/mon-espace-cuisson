<?php
echo "<br><div id=\"popup_cmd_projet\">
	<form method=\"post\">
    <input type=\"hidden\" name=\"action\" value=\"lier-projet-cmd\">
    <input type=\"hidden\" name=\"factid\" class=\"inputnum_cmd\">
    <p>Associer la commande n°<span class=\"num_cmd\"></span> à un projet : <br>
    <select name=\"projetid\">
    <option value=''>-- Projets --</option>";
          
    $projets=$DB_site2->query("SELECT * FROM projet WHERE facturid=0 AND userid = ".$_GET['userid']." AND etatid IN (2, 3, 4, 5, 6, 7, 8)");
    while($projet=$DB_site2->fetch_array($projets)){
        echo "<option value=".$projet['projetid'].">PJT du ".date('d/m/Y', $projet['dateline'])." n°".$projet['projetid']." - " .substr(UCfirst($projet['libelle']), 0, 60)."... - ".$projet['commercial']."</option>";
    }
          
echo "</select></p><input type=\"submit\" value=\"Lier\">
    </form><button onclick=\"back_popup_devisprojet();\">Retour</button></div>

    <div id=\"popup_projet_cmd_delie\"><form method=\"post\">
    <input type=\"hidden\" name=\"action\" value=\"delier-projet-cmd\">
    <input type=\"hidden\" name=\"projetid\" class=\"inputnum_projet\">
    <p>Délier la commande n°<span class=\"num_cmd\"></span> du projet n°<span class=\"num_projet\"></span> - <span class=\"libelle_projet\"></span> ?</p>
   <input type=\"submit\" value=\"Délier\">
    </form><button onclick=\"back_popup_devisprojet();\">Retour</button></div>

	<div id=\"popup_devis_projet\"><form method=\"post\">
    <p>Associer le devis n°<span class=\"num_devis\"></span> à un projet : <br>
    <input type=\"hidden\" name=\"action\" value=\"lier-projet-devis\">
    <input type=\"hidden\" name=\"devisid\" class=\"inputnum_devis\">
    <select name=\"projetid\">
    <option value=''>-- Projets --</option>";
          
    $projets=$DB_site2->query("SELECT * FROM projet WHERE userid = ".$_GET['userid']." AND etatid IN (2, 3, 4, 5, 6, 7, 8)");
    while($projet=$DB_site2->fetch_array($projets)){
        echo "<option value=".$projet['projetid'].">PJT du ".date('d/m/Y', $projet['dateline'])." n°".$projet['projetid']." - " .substr(UCfirst($projet['libelle']), 0, 60)."... - ".$projet['commercial']."</option>";
    }
          
echo "</select></p><input type=\"submit\" value=\"Lier\">
    </form><button onclick=\"back_popup_devisprojet();\">Retour</button></div>
    <div id=\"popup_devis_projet_delie\"><form method=\"post\">
    <input type=\"hidden\" name=\"action\" value=\"delier-projet-devis\">
        <input type=\"hidden\" name=\"devisid\" class=\"inputnum_devis\">
    <input type=\"hidden\" name=\"projetid\" class=\"inputnum_projet\">
    <p>Délier le devis n°<span class=\"num_devis\"></span> du projet n°<span class=\"num_projet\"></span> - <span class=\"libelle_projet\"></span> ?</p>
   <input type=\"submit\" value=\"Délier\">
    </form><button onclick=\"back_popup_devisprojet();\">Retour</button></div>";

echo"<br><table class=\"table_factures\" id=\"utilisateur_suivicommande\">";

echo "	<tr id=\"command_append_before\"><td colspan=\"10\" class=\"$rowalt\" style=\"text-align:center; font-weight:bold; font-size:14px;\"><a id=\"charger_toutes_commandes\">CHARGEMENT DES 10 DERNIERES COMMANDES</a></td></tr>";
echo "</table>";

?>

<script>
$jq(document).ready(function () {
	
	
	$jq.ajax('ajax/suivi_client_commande.php?userid=<?php echo $utilisateur['userid']; ?>&prems=1',   // request url
	{           
		success: function (data, status, xhr) {    // success callback function
			$jq('#command_append_before').before(data);
			$jq('#charger_toutes_commandes').text('CHARGER TOUT');
		}
	});

	
	
	
	$jq('#charger_toutes_commandes').click(function(){
		$jq.ajax('ajax/suivi_client_commande.php?userid=<?php echo $utilisateur['userid']; ?>',   // request url
		{            
			success: function (data, status, xhr) {    // success callback function
				$jq('#utilisateur_suivicommande').children().remove();
				$jq('#utilisateur_suivicommande').append(data);
			},
			error: function (jqXhr, textStatus, errorMessage) { // error callback 
				$jq('#charger_toutes_commandes').text('Erreur !' + errorMessage);
			},
			beforeSend: function() {
				$jq('#charger_toutes_commandes').text('Chargement en cours ...');
			}
		});
	});
});

function loadbyyear(timestamp){
		$jq.ajax('ajax/suivi_client_commande.php?userid=<?php echo $utilisateur['userid']; ?>&datemax='+timestamp,   // request url
		{            
			success: function (data, status, xhr) {    // success callback function
				$jq('#utilisateur_suivicommande').children().remove();
				$jq('#utilisateur_suivicommande').append(data);
			},
			error: function (jqXhr, textStatus, errorMessage) { // error callback 
				$jq('#charger_toutes_commandes').text('Erreur !' + errorMessage);
			},
			beforeSend: function() {
				$jq('#utilisateur_suivicommande').children().remove();
				$jq('#utilisateur_suivicommande').append('<p>Chargement en cours ...</p>');
			}
		});
}
</script>
<div class="clear"></div>
