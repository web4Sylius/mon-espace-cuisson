<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/admin_global.php');


if(isset($_GET['fichier']) && $_GET['fichier'] != '')
{
	$filename = SITE_PATH . '/log/' . $_GET['fichier'];
	$filesize = filesize(SITE_PATH . '/log/' . $_GET['fichier']);
	var_dump($filename);
	header("Content-Type: application/force-download");
	header('Content-Disposition: attachment; filename="' . $_GET['fichier'] . '"');
	header("Content-Length: " . $filesize);
	readfile($filename);
}

include_once(BACK_PATH . "/header.php");
include_once(BACK_PATH . "/menu.php");
?>
<script type="text/javascript">
	
	function affiche_div(id)
	{
		$jQuery('.liste_erreurs').each(function(){
			$jQuery(this).hide();
		});

		$jQuery('#div_' + id).show();
	}

	function cleanBoutons()
	{
		$jQuery('.bouton_erreur').each(function(){
			$jQuery(this).removeClass('selected');
		});
	}

	$jQuery(document).ready(function(){

		$jQuery('.bouton_erreur').each(function(){
			$jQuery(this).on('click',function(){
				cleanBoutons();
				$jQuery(this).addClass('selected');
			});
		});

	});

</script>
<?php
$dossiers_files = scandir(SITE_PATH . '/log');

$aFichiers = array('dberror' => array(),'dbslow' => array());
$cpt       = 0; 
foreach($dossiers_files as $le_fichier)
{
	if(substr($le_fichier,0,1) != '.')
	{
		preg_match_all('#^(?P<type>.*)_(?P<annee>[0-9]{4})-(?P<mois>[0-9]{2})-(?P<jour>[0-9]{2})\.txt$#', $le_fichier, $matches);
		
		if($matches)
		{
			$fichier_type  = $matches['type'][0];
			$fichier_nom   = $matches[0][0];
			$fichier_annee = $matches['annee'][0];
			$fichier_mois  = $matches['mois'][0];
			$fichier_jour  = $matches['jour'][0];

			$aFichiers[$fichier_type][$cpt]['nom'] = $fichier_nom;
			$aFichiers[$fichier_type][$cpt]['date'] = $fichier_jour . '/' . $fichier_mois . '/' . $fichier_annee;

			$cpt++;
		}
	} 
}

ksort($aFichiers);
?>
<h1>Logs d'erreur</h1>

<div class="gestion_erreurs">
<?php
if(count($aFichiers) > 0)
{
?>
	<!--<a href="javascript:void(0);" class="bouton_erreur selected" onclick="affiche_div('404errors');">Erreurs 404</a>-->
	<a href="javascript:void(0);" class="bouton_erreur" onclick="affiche_div('dberror');">Requêtes en erreur</a>
	<a href="javascript:void(0);" class="bouton_erreur" onclick="affiche_div('dbslow');">Requêtes lentes</a>
	<!--<a href="javascript:void(0);" class="bouton_erreur" onclick="affiche_div('phperrors');">Erreurs fatales</a>-->
<?php
	$j = 0;
	$style_display = '';
	foreach($aFichiers as $fichier_type => $fichier_liste)
	{
		if($fichier_type != ''){
			rsort($fichier_liste);
			if($j > 0) $style_display = 'display: none;';

			echo ""
			. "<div id=\"div_" . $fichier_type . "\" class=\"liste_erreurs\" style=\"width: 100%; border: 1px solid #AC8B5A; margin-top: 10px; padding: 10px; " . $style_display . "\">"
				. "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"width: 100%;\">";

					if(count($fichier_liste) > 0)
					{
						$cpt = 1;
						foreach($fichier_liste as $l_erreur)
						{
							if($cpt == 1) echo "<tr>";

							echo "<td align=\"center\"><a href=\"http://".$_SERVER['SERVER_NAME']."/admin/erreurs.php?fichier=" . $l_erreur['nom'] . "\">" . $l_erreur['date'] . "</a></td>";
							
							$cpt++;
							
							if($cpt == 7 || $cpt == count($fichier_liste)+1)
							{
								echo "</tr>";
								$cpt = 1;
							} 
						}
					}
					else
					{
						echo ""
						. "<tr>"
							. "<td align=\"center\">Aucun log enregistré</td>"
						. "</tr>";
					}

			echo ""
				. "</table>"
			. "</div>";

			$j++;
		}
	}
}
?>
</div>