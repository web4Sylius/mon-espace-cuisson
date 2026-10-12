<?php

$actiontmp = $action2 ;
function ongletcontenu ($templatecontenu = "Rien à afficher") 
	{
	global $actiontmp, $onglets, $onglets_textes, $catid, $maxonglets, $onglet_en_cours, $nbong ;
	while (list($key, $val) = each($onglets)) 
		{
		if ($actiontmp == $val) 
			{
			$onglet_actif = $key ;
			}
		}
	$return = " <table align=\"center\" border=0 cellpadding=\"0\" cellspacing=\"0\" width=\"95%\">
				<tr>
					<td align=\"right\">
						<table align=\"right\" border=0 cellpadding=\"0\" cellspacing=\"0\">" ;
	$nb_lignes = count($onglets) / $maxonglets ;
	if (is_float($nb_lignes)) // si c'est un décimal on arrondit à l'entier supérieur
		{
		$nb_lignes = ceil($nb_lignes) ;
		}
	$ligne_en_cours = 0 ;
	for ($i = 0 ; $i < count($onglets) ; $i++)
		{
		$nbong++;
		if ($onglet_en_cours == $maxonglets or $onglet_en_cours == 0)
			{
			$return .= "<tr>
							<td>";
			$onglet_en_cours = 0 ;
			$ligne_en_cours++ ;
			}
		if ($i == 0 or $onglet_en_cours == 0) 
			{
			$moitiegauche = "" ;
			} 
		else 
			{			
			$moitiegauche = "_moitie" ;
			}
		if ($ligne_en_cours == $nb_lignes)
			{
			$return .= "<table border=0 cellpadding=\"0\" cellspacing=\"0\">
							<tr height=\"18\">
								<td><img src=\"./images/onglets/gauche".$moitiegauche.".gif\"></td>
								<td width=\"100%\" background=\"./images/onglets/milieu.gif\" valign=\"middle\" align=\"center\" nowrap>";
			}
		else
			{
			$return .= "<table border=0 cellpadding=\"0\" cellspacing=\"0\">
							<tr height=\"18\">
								<td><img src=\"./images/onglets/gauche".$moitiegauche."_bas.gif\"></td>
								<td width=\"100%\" background=\"./images/onglets/milieu_bas.gif\" valign=\"middle\" align=\"center\" nowrap>";
			}
		if ($i != $onglet_actif) 
			{
			$return .= "<a href=\"Javascript: clique('$onglets[$i]','$actiontmp') ;\">$onglets_textes[$i]</a>"; // ici , changer private.php par le php que tu veux => renvoie un $action
			} 
		else 
			{
			$return .= "<span class=\"onglet_select\">$onglets_textes[$i]</span>" ;
			}
		if ($i == count($onglets) - 1 or $i >= $onglet_actif) 
			{
			$moitiedroite = "" ;
			} 
		else 
			{
			$moitiedroite = "_moitie" ;
			}
		if ($ligne_en_cours == $nb_lignes)
			{
			$return .= "</td>
						<td><img src=\"./images/onglets/droite".$moitiedroite.".gif\"></td>
					</tr>" ;
			}
		else
			{
			$return .= "</td>
						<td><img src=\"./images/onglets/droite".$moitiedroite."_bas.gif\"></td>
					</tr>" ;
			}
		$return .= "</table>
			</td>
			<td>" ;
		if ($onglet_en_cours == $maxonglets - 1 or $nbong == count($onglets))
			{
			$return .= "</td>
			</tr>";
			}
		$onglet_en_cours++ ;
		}
	// affichage du contenu de l'onglet choisit
	$return .= "</table>
			</td>
		</tr>
		<tr>
			<td>
				<table align=\"center\" width=\"100%\" border=\"1\" bordercolor=\"#666666\" cellpadding=\"3\" cellspacing=\"0\" style=\"border-collapse: collapse\">
					<tr>
						<td class=\"content\" align=center>$templatecontenu</td>
					</tr>
				</table>
			</td>
		</tr>
	</table><br><br>" ;
	return $return ;
	}

?>