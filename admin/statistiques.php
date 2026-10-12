<?php
require "admin_global.php";
include "./header.php";
include "./menu.php";


echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Statistiques de ma boutique</font>
			</td>
		</tr>
		<tr>
			<td background=\"images2/cadre_r3_c2.gif\" width=\"100%\" height=\"2\">
			</td>
		</tr>
	</table>
   </td>
   <td><img name=\"cadre_r2_c5\" src=\"images2/cadre_r2_c5.gif\" width=\"11\" height=\"27\" border=\"0\" alt=\"\"></td>
   <td background=\"images2/cadre_r2_c6.gif\" width=\"100%\" height=\"27\" align=\"center\" valign=\"middle\">
   </td>
   <td><img name=\"cadre_r2_c8\" src=\"images2/cadre_r2_c8.gif\" width=\"13\" height=\"27\" border=\"0\" alt=\"\"></td>
  </tr>
</table>

</tr></td>
<tr><td>

<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td background=\"images2/cadre_r4_c2.gif\" width=\"3\" height=\"100%\"></td>
   <td align=\"justify\" valign=\"middle\"><br>
   	<font class=\"txt_2\">
		<table align=\"center\" width=\"100%\">
			<tr>
				<td align=\"center\" valign=\"middle\" width=\"100%\">" ;
				
if (in_array("108",$modules)) // analyse des référents
	{
	echo "<div align=\"left\">Lien vers <a href=\"referents.php\"><font size=\"2\"><strong>Analyse des référents</strong></font></a></div>" ;
	}




if (isset($action) and $action == "vider")
	{
	echo "Etes-vous sûr(e) de vouloir supprimer toutes les statistiques ?<br><br>
	<a href=\"statistiques.php?action=viderok\">OUI</a> | <a href=\"statistiques.php\">NON</a><br><br>
	<font class=\"cost\">Attention : cette action est irréversible !</font>" ;
	}

if (isset($action) and $action == "viderok")
	{
	$DB_site2->query("delete from statistique");
	$DB_site2->query("delete from statistique_page");
	$DB_site2->query("delete from statistique_page_utilisateur");
	$DB_site2->query("delete from statistique_produit");
	$DB_site2->query("delete from statistique_produit_utilisateur");
	$DB_site2->query("delete from statistique_utilisateur");
	$action = "" ;
	}

if (!isset($action) or $action == "")
	{
	// fréquentation
	$statistique=$DB_site2->query_first("select sum(visites) as visites , sum(visiteurs) as visiteurs , sum(pagesvues) as pagesvues 
										from statistique where datestatistique='".date("Y")."-".date("m")."-".date("d")."' ");
	$nb_pages_j += $statistique[pagesvues] ;
	$nb_visites_j += $statistique[visites] ;
	$nb_visiteurs_j += $statistique[visiteurs] ;
	
	$statistiques=$DB_site2->query_first("select sum(visites) as visites , sum(visiteurs) as visiteurs , sum(pagesvues) as pagesvues 
										from statistique where datestatistique>'".date("Y")."-".date("m")."-01' ");
	$nb_pages_m += $statistiques[pagesvues] ;
	$nb_visites_m += $statistiques[visites] ;
	$nb_visiteurs_m += $statistiques[visiteurs] ;
	
	$statistiquess=$DB_site2->query_first("select sum(visites) as visites , sum(visiteurs) as visiteurs , sum(pagesvues) as pagesvues 
										from statistique");
	$nb_pages_tot += $statistiquess[pagesvues] ;
	$nb_visites_tot += $statistiquess[visites] ;
	$nb_visiteurs_tot += $statistiquess[visiteurs] ;
	
	// chiffre d'affaires
	$factures_j=$DB_site2->query("select f.facturid as facturid, f.userid as userid, f.prixport as prixport, f.prixgestion as prixgestion, 
								f.dateexpedition as dateexpedition, lf.artid as artid, lf.qte as qte, lf.prix as prix, 
								c.typecadeauid as typecadeauid,	c.valeurcadeau as valeurcadeau from lignefacture lf,
								facture f left outer join cadeau c on f.cadeauid = c.cadeauid where f.facturid = lf.factureid 
								and (f.valide = 1 or f.valide=5) and f.datefacture='".date("Y")."-".date("m")."-".date("d")."' ORDER BY f.facturid ASC ");
	
	while ($facture_j=$DB_site2->fetch_array($factures_j)) 
		{
		$facturid = $facture_j[facturid] ;
		$prixport = $facture_j[prixport] ;
		$prixgestion = $facture_j[prixgestion] ;
		$qte = $facture_j[qte] ;
		$prix = $facture_j[prix] ;
		$typecadeauid = $facture_j[typecadeauid] ;
		$valeurcadeau = $facture_j[valeurcadeau] ;
		if ($facturid != $facturid_j_prec)
			{
			if ($typecadeauid  == 0) //euros
				{
				$ca_j += ($qte * $prix) - $valeurcadeau + $prixport + $prixgestion ;
				}
			elseif ($typecadeauid == 1) //pourcent
				{
				$ca_j += ($qte * $prix) - (($qte * $prix) * $valeurcadeau / 100) + $prixport + $prixgestion ;
				}
			else 
				{
				$ca_j += ($qte * $prix) + $prixport + $prixgestion ;
				}
			}
		else
			{
			if ($typecadeauid  == 0) //euros
				{
				$ca_j += ($qte * $prix) - $valeurcadeau ;
				}
			elseif ($typecadeauid == 1) //pourcent
				{
				$ca_j += ($qte * $prix) - (($qte * $prix) * $valeurcadeau / 100) ;
				}
			else 
				{
				$ca_j += ($qte * $prix) ;
				}
			}
		$facturid_j_prec = $facturid ;
		}
	$factures_m=$DB_site2->query("select f.facturid as facturid, f.userid as userid, f.prixport as prixport, f.prixgestion as prixgestion,
								f.dateexpedition as dateexpedition, lf.artid as artid, lf.qte as qte, lf.prix as prix, 
								c.typecadeauid as typecadeauid,	c.valeurcadeau as valeurcadeau from lignefacture lf, facture f 
								left outer join cadeau c on f.cadeauid = c.cadeauid	where f.facturid = lf.factureid 
								and (f.valide = 1 or f.valide=5) and f.datefacture>'".date("Y")."-".date("m")."-01' ORDER BY f.facturid ASC ");
	while ($facture_m=$DB_site2->fetch_array($factures_m)) 
		{
		$facturid = $facture_m[facturid] ;
		$prixport = $facture_m[prixport] ;
		$prixgestion = $facture_m[prixgestion] ;
		$qte = $facture_m[qte] ;
		$prix = $facture_m[prix] ;
		$typecadeauid = $facture_m[typecadeauid] ;
		$valeurcadeau = $facture_m[valeurcadeau] ;
		if ($facturid != $facturid_m_prec)
			{
			if ($typecadeauid  == 0) //euros
				{
				$ca_m += ($qte * $prix) - $valeurcadeau + $prixport + $prixgestion ;
				}
			elseif ($typecadeauid == 1) //pourcent
				{
				$ca_m += ($qte * $prix) - (($qte * $prix) * $valeurcadeau / 100) + $prixport + $prixgestion ;
				}
			else 
				{
				$ca_m += ($qte * $prix) + $prixport + $prixgestion ;
				}
			}
		else
			{
			if ($typecadeauid  == 0) //euros
				{
				$ca_m += ($qte * $prix) - $valeurcadeau ;
				}
			elseif ($typecadeauid == 1) //pourcent
				{
				$ca_m += ($qte * $prix) - (($qte * $prix) * $valeurcadeau / 100) ;
				}
			else 
				{
				$ca_m += ($qte * $prix) ;
				}
			}
		$facturid_m_prec = $facturid ;
	}
	
	$factures_tot=$DB_site2->query("select f.facturid as facturid, f.userid as userid, f.prixport as prixport, f.prixgestion as prixgestion,
								f.dateexpedition as dateexpedition, lf.artid as artid, lf.qte as qte, lf.prix as prix, 
								c.typecadeauid as typecadeauid,	c.valeurcadeau as valeurcadeau from lignefacture lf, facture f 
								left outer join cadeau c on f.cadeauid = c.cadeauid	where f.facturid = lf.factureid 
								and (f.valide = 1 or f.valide=5) ORDER BY f.facturid ASC ");
	while ($facture_tot=$DB_site2->fetch_array($factures_tot)) 
		{
		$facturid = $facture_tot[facturid] ;
		$prixport = $facture_tot[prixport] ;
		$prixgestion = $facture_tot[prixgestion] ;
		$qte = $facture_tot[qte] ;
		$prix = $facture_tot[prix] ;
		$typecadeauid = $facture_tot[typecadeauid] ;
		$valeurcadeau = $facture_tot[valeurcadeau] ;
		if ($typecadeauid  == 0) //euros
			{
			$ca_tot += ($qte * $prix) - $valeurcadeau + $prixport + $prixgestion ;
			}
		elseif ($typecadeauid == 1) //pourcent
			{
			$ca_tot += ($qte * $prix) - (($qte * $prix) * $valeurcadeau / 100) + $prixport + $prixgestion ;
			}
		else 
			{
			$ca_tot += ($qte * $prix) + $prixport + $prixgestion ;
			}
	}
	
	$anneemois = date ('Y-m') ;
	$anneemoisjour = date ('Y-m-d') ;
	
	// utilisateurs
	$nb_util_tot=$DB_site2->query_first("SELECT COUNT(*) FROM utilisateur");
	$nb_util_mois=$DB_site2->query_first("SELECT COUNT(*) FROM utilisateur where dateinscription like '$anneemois-%'");
	$nb_util_jour=$DB_site2->query_first("SELECT COUNT(*) FROM utilisateur where dateinscription = '$anneemoisjour'");
	$nb_cnx_tot=$DB_site2->query_first("SELECT SUM(visites) FROM statistique_utilisateur");
	$nb_cnx_mois=$DB_site2->query_first("SELECT SUM(visites) FROM statistique_utilisateur where datestatistique like '$anneemois-%'");
	$nb_cnx_jour=$DB_site2->query_first("SELECT SUM(visites) FROM statistique_utilisateur where datestatistique = '$anneemoisjour'");
	
	// articles
	$stat_art_tot=$DB_site2->query_first("SELECT SUM(visites) as visites, SUM(panier) as panier, SUM(commandes) as commandes 
										FROM statistique_produit");
	$stat_art_mois=$DB_site2->query_first("SELECT SUM(visites) as visites, SUM(panier) as panier, SUM(commandes) as commandes 
										FROM statistique_produit where datestatistique like '$anneemois-%'");
	$stat_art_jour=$DB_site2->query_first("SELECT SUM(visites) as visites, SUM(panier) as panier, SUM(commandes) as commandes 
										FROM statistique_produit where datestatistique = '$anneemoisjour'");
	
	// commandes
	$nb_cdespassees_tot=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture");
	$nb_cdespassees_mois=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where datefacture LIKE '$anneemois-%'");
	$nb_cdespassees_jour=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where datefacture = '$anneemoisjour'");

	$nb_cdessanssuite_tot=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '4'");
	$nb_cdessanssuite_mois=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '4' and datefacture LIKE '$anneemois-%'");
	$nb_cdessanssuite_jour=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '4' and datefacture = '$anneemoisjour'");
	$nb_cdesexpediees_tot=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5'");
	$nb_cdesexpediees_mois=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5' and datefacture LIKE '$anneemois-%'");
	$nb_cdesexpediees_jour=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5' and datefacture = '$anneemoisjour'");
	$nb_cdesexpediees1_tot=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5' and moyenid = '1'");
	$nb_cdesexpediees2_tot=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5' and moyenid = '2'");
	$nb_cdesexpediees3_tot=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5' and moyenid = '3'");
	$nb_cdesexpediees4_tot=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5' and moyenid = '4'");
	$nb_cdesexpediees5_tot=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5' and moyenid = '5'");
	$nb_cdesexpediees6_tot=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5' and moyenid = '6'");
	$nb_cdesexpediees1_mois=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5' and moyenid = '1' and datefacture LIKE '$anneemois-%'");
	$nb_cdesexpediees2_mois=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5' and moyenid = '2' and datefacture LIKE '$anneemois-%'");
	$nb_cdesexpediees3_mois=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5' and moyenid = '3' and datefacture LIKE '$anneemois-%'");
	$nb_cdesexpediees4_mois=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5' and moyenid = '4' and datefacture LIKE '$anneemois-%'");
	$nb_cdesexpediees5_mois=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5' and moyenid = '5' and datefacture LIKE '$anneemois-%'");
	$nb_cdesexpediees6_mois=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5' and moyenid = '6' and datefacture LIKE '$anneemois-%'");
	$nb_cdesexpediees1_jour=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5' and moyenid = '1' and datefacture = '$anneemoisjour'");
	$nb_cdesexpediees2_jour=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5' and moyenid = '2' and datefacture = '$anneemoisjour'");
	$nb_cdesexpediees3_jour=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5' and moyenid = '3' and datefacture = '$anneemoisjour'");
	$nb_cdesexpediees4_jour=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5' and moyenid = '4' and datefacture = '$anneemoisjour'");
	$nb_cdesexpediees5_jour=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5' and moyenid = '5' and datefacture = '$anneemoisjour'");
	$nb_cdesexpediees6_jour=$DB_site2->query_first("SELECT COUNT(facturid) FROM facture where valide = '5' and moyenid = '6' and datefacture = '$anneemoisjour'");
	
	echo "<h3>Statistiques générales</h3><a href=\"statistiques.php?action=vider\">Vider les statistiques</a><bR><br>
	<table class=\"table_caracts\">
		<tr><td align=\"center\" class=\"td_promos_alt2\"><strong>Statistique</strong></td><tD class=\"td_promos_alt2\" align=\"center\"><strong>Au total</strong></td><td class=\"td_promos_alt2\" align=\"center\"><strong>Ce mois</strong></td><td class=\"td_promos_alt2\" align=\"center\"><strong>Ce jour</strong></td></tr>
		<tr><td><u>Fréquentation</u></td><tD align=\"center\"></td><tD align=\"center\"></td><tD align=\"center\"></td></tr>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;Visites</td><tD align=\"center\">$nb_visites_tot</td><td align=\"center\">$nb_visites_m</td><td align=\"center\">$nb_visites_j</td></tr>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;Visiteurs</td><tD align=\"center\">$nb_visiteurs_tot</td><td align=\"center\">$nb_visiteurs_m</td><td align=\"center\">$nb_visiteurs_j</td></tr>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;Pages visitées</td><tD align=\"center\">$nb_pages_tot</td><td align=\"center\">$nb_pages_m</td><td align=\"center\">$nb_pages_j</td></tr>
		<tr><td colspan=\"4\" align=\"right\"><a href=\"statistiques.php?action=frequentation\" alt=\"+ de détails sur la fréquentation\" title=\"+ de détails sur la fréquentation\">+ de détails sur la fréquentation</a> </td></tr>
		<tr><td class=\"td_promos_alt1\" height=\"1\"></td><tD align=\"center\" class=\"td_promos_alt1\"></td><tD align=\"center\" class=\"td_promos_alt1\"></td><tD align=\"center\" class=\"td_promos_alt1\"></td></tr>
		<tr><td><u>Chiffre d'affaires</u></td><tD align=\"center\"></td><td align=\"center\"></td><td align=\"center\"></td></tr>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;Montant</td><tD align=\"center\">$ca_tot</td><td align=\"center\">" . round($ca_m , 2) . "</td><td align=\"center\">" . round($ca_j ,2) . "</td></tr>
		<tr><td class=\"td_promos_alt1\" height=\"1\"></td><tD align=\"center\" class=\"td_promos_alt1\"></td><tD align=\"center\" class=\"td_promos_alt1\"></td><tD align=\"center\" class=\"td_promos_alt1\"></td></tr>
		<tr><td><u>Utilisateurs</u></td><tD align=\"center\"></td><td align=\"center\"></td><td align=\"center\"></td></tr>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;Nombre d'inscrits</td><tD align=\"center\">$nb_util_tot[0]</td><td align=\"center\">$nb_util_mois[0]</td><td align=\"center\">$nb_util_jour[0]</td></tr>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;Nombre de connexions</td><tD align=\"center\">$nb_cnx_tot[0]</td><td align=\"center\">$nb_cnx_mois[0]</td><td align=\"center\">$nb_cnx_jour[0]</td></tr>
		<tr><td colspan=\"4\" align=\"right\"><a href=\"utilisateur.php\" target=\"_blank\" alt=\"+ de détails sur les utilisateurs\" title=\"+ de détails sur les utilisateurs\">+ de détails sur les utilisateurs</a> </td></tr>
		<tr><td class=\"td_promos_alt1\" height=\"1\"></td><tD align=\"center\" class=\"td_promos_alt1\"></td><tD align=\"center\" class=\"td_promos_alt1\"></td><tD align=\"center\" class=\"td_promos_alt1\"></td></tr>
		<tr><td><u>Articles</u></td><tD align=\"center\"></td><td align=\"center\"></td><td align=\"center\"></td></tr>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;Articles visualisés</td><tD align=\"center\">$stat_art_tot[visites]</td><td align=\"center\">$stat_art_mois[visites]</td><td align=\"center\">$stat_art_jour[visites]</td></tr>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;Articles ajoutés au panier</td><tD align=\"center\">$stat_art_tot[panier]</td><td align=\"center\">$stat_art_mois[panier]</td><td align=\"center\">$stat_art_jour[panier]</td></tr>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;Articles commandés</td><tD align=\"center\">$stat_art_tot[commandes]</td><td align=\"center\">$stat_art_mois[commandes]</td><td align=\"center\">$stat_art_jour[commandes]</td></tr>
		<tr><td colspan=\"4\" align=\"right\"><a href=\"statistiques.php?action=articles\" alt=\"+ de détails sur les articles\" title=\"+ de détails sur les articles\">+ de détails sur les articles</a> </td></tr>
		<tr><td class=\"td_promos_alt1\" height=\"1\"></td><tD align=\"center\" class=\"td_promos_alt1\"></td><tD align=\"center\" class=\"td_promos_alt1\"></td><tD align=\"center\" class=\"td_promos_alt1\"></td></tr>
		<tr><td><u>Commandes</u></td><tD align=\"center\"></td><td align=\"center\"></td><td align=\"center\"></td></tr>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;Commandes passées</td><tD align=\"center\">$nb_cdespassees_tot[0]</td><td align=\"center\">$nb_cdespassees_mois[0]</td><td align=\"center\">$nb_cdespassees_jour[0]</td></tr>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;Commandes abandonnées</td><tD align=\"center\">$nb_cdessanssuite_tot[0]</td><td align=\"center\">$nb_cdessanssuite_mois[0]</td><td align=\"center\">$nb_cdessanssuite_jour[0]</td></tr>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;Commandes expediées</td><tD align=\"center\">$nb_cdesexpediees_tot[0]</td><td align=\"center\">$nb_cdesexpediees_mois[0]</td><td align=\"center\">$nb_cdesexpediees_jour[0]</td></tr>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dont par Carte Bleue</td><tD align=\"center\">$nb_cdesexpediees1_tot[0]</td><td align=\"center\">$nb_cdesexpediees1_mois[0]</td><td align=\"center\">$nb_cdesexpediees1_jour[0]</td></tr>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dont par Chèque</td><tD align=\"center\">$nb_cdesexpediees2_tot[0]</td><td align=\"center\">$nb_cdesexpediees2_mois[0]</td><td align=\"center\">$nb_cdesexpediees2_jour[0]</td></tr>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dont par Contre remboursement</td><tD align=\"center\">$nb_cdesexpediees3_tot[0]</td><td align=\"center\">$nb_cdesexpediees3_mois[0]</td><td align=\"center\">$nb_cdesexpediees3_jour[0]</td></tr>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dont par Mandat postal</td><tD align=\"center\">$nb_cdesexpediees4_tot[0]</td><td align=\"center\">$nb_cdesexpediees4_mois[0]</td><td align=\"center\">$nb_cdesexpediees4_jour[0]</td></tr>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dont par Virement</td><tD align=\"center\">$nb_cdesexpediees5_tot[0]</td><td align=\"center\">$nb_cdesexpediees5_mois[0]</td><td align=\"center\">$nb_cdesexpediees5_jour[0]</td></tr>
		<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dont par PayPal</td><tD align=\"center\">$nb_cdesexpediees6_tot[0]</td><td align=\"center\">$nb_cdesexpediees6_mois[0]</td><td align=\"center\">$nb_cdesexpediees6_jour[0]</td></tr>
		<tr><td colspan=\"4\" align=\"right\"><a href=\"facture.php\" target=\"_blank\" alt=\"+ de détails sur les commandes\" title=\"+ de détails sur les commandes\">+ de détails sur les commandes</a> </td></tr>
	</table>" ;
	$liste_mois = "" ;
	$liste_annee = "" ;
	$inscr=$DB_site2->query_first("SELECT date_inscription FROM parametresgeneraux");
	$ai = substr($inscr[0], 0, 4) ;
	$a = date('Y') ;
	while ($ai <= $a)
		{
		$liste_annee .= "<option value=\"$ai\">$ai</option>" ;
		$ai++ ;
		}
	$m = 1 ;
	while ($m <= 12)
		{
		if (strlen($m) == 1)
			{
			$ma = "0" . $m ;
			}
		else
			{
			$ma = $m ;
			}
		$liste_mois .= "<option value=\"$ma\">$ma</option>" ;
		$m++ ;
		}
	echo "<br><bR>
	<form method=\"post\" action=\"statistiques.php\"><input type=\"hidden\" name=\"action2\" value=\"date\">
	Chiffre d'affaires du mois de : <select name=\"mois\"><option value=\"0\"> </option>$liste_mois</select> <select name=\"annee\"><option value=\"0\"> </option>$liste_annee</select> <input type=\"submit\" value=\"ok\">
	</form>	<br>" ;	
	if (isset($action2) and $action2 == "date")
		{
		switch ($mois)
			{
			case "01":
				$mois_l = "Janvier" ;
				break ;
			case "02":
				$mois_l = "Février" ;
				break ;
			case "03":
				$mois_l = "Mars" ;
				break ;
			case "04":
				$mois_l = "Avril" ;
				break ;
			case "05":
				$mois_l = "Mai" ;
				break ;
			case "06":
				$mois_l = "Juin" ;
				break ;
			case "07":
				$mois_l = "Juillet" ;
				break ;
			case "08":
				$mois_l = "Août" ;
				break ;
			case "09":
				$mois_l = "Septembre" ;
				break ;
			case "10":
				$mois_l = "Octobre" ;
				break ;
			case "11":
				$mois_l = "Novembre" ;
				break ;
			case "12":
				$mois_l = "Décembre" ;
				break ;
			}
		$facts=$DB_site2->query("SELECT * FROM facture where datefacture LIKE '$annee-$mois-%' and valide = '5'");
		echo $DB_site2->num_rows($facts) . " commandes expediées pour le mois de $mois_l $annee<br>" ;
		$totalht = 0 ;
		$totalport = 0 ;
		$totaltva = 0 ;
		$total_general = 0 ;
		$liste = "" ;
		while ($fact=$DB_site2->fetch_array($facts))
			{
			$mtt=$DB_site2->query_first("SELECT sum(prix*qte) FROM lignefacture where factureid = '$fact[facturid]'");
			$tvas=$DB_site2->query("SELECT prix, qte, tva FROM lignefacture where factureid = '$fact[facturid]'");
			$matva = 0 ;
			while ($tva=$DB_site2->fetch_array($tvas)) {
				$matva += ($tva[prix] * $tva[qte]) - ($tva[prix] / (1 + ($tva[tva] / 100))) * $tva[qte] ;
				}
			$totalttc = $mtt[0] + $fact[prixport] + $fact[prixgestion] - $fact[montantremise] - $fact[montantcadeau] ;
			$matvaport = 0 ;
			if ($fact[tvaport] > 0) {
				$matvaport = $fact[prixport] - ($fact[prixport] / (1 + ($fact[tvaport] / 100))) ;
				}
			$mttht = $totalttc - ($matva + $matvaport) ;
			$totalht += $mttht ;
			$totalport += $fact[prixport] ;
			$tva = $matva + $matvaport ;
			$totaltva += $tva ;
			$totalgeneral += $totalttc ;
			$tabdate = explode("-", $fact[datefacture]) ;
			$liste .= "<tr>
						<td align=\"center\">$fact[codenumerofacture]$fact[numerofacture]</td>
						<td align=\"center\">$fact[facturid]</td>
						<td align=\"center\">$tabdate[2]/$tabdate[1]/$tabdate[0]</td>
						<td align=\"right\">".number_format($mttht, 2, ",", " ")." €</td>
						<td align=\"right\">".number_format($fact[prixport], 2, ",", " ")." €</td>
						<td align=\"right\">".number_format($tva, 2, ",", " ")." €</td>
						<td align=\"right\">".number_format($totalttc, 2, ",", " ")." €</td>
					</tr>" ;
			}
		echo "<table align=\"center\" class=\"table_remises_ajout\">
			<tr>
				<td class=\"td_head_promos\">n° fact</td>
				<td class=\"td_head_promos\">n° cde</td>
				<td class=\"td_head_promos\">date</td>
				<td class=\"td_head_promos\">Total HT</td>
				<td class=\"td_head_promos\">Frais port</td>
				<td class=\"td_head_promos\">TVA</td>
				<td class=\"td_head_promos\">Total TTC</td>
			</tr>
			<tr><td><br></td></tr>
			$liste
			<tr><td><br></td></tr>
			<tr>
				<td colspan=\"3\" align=\"right\"><strong>TOTAUX : </strong></td>
				<td align=\"right\"><strong>".number_format($totalht, 2, ",", " ")." €</strong></td>
				<td align=\"right\"><strong>".number_format($totalport, 2, ",", " ")." €</strong></td>
				<td align=\"right\"><strong>".number_format($totaltva, 2, ",", " ")." €</strong></td>
				<td align=\"right\"><strong>".number_format($totalgeneral, 2, ",", " ")." €</strong></td>
			</tr>
		</table>" ;
		
		}
	}
elseif ($action == "articles")
	{
	echo "<h3>+ de détails sur les articles</h3>
	<table class=\"table_coupscoeur\">
		<tr>
			<td align=\"center\"><strong>Article</strong></td>
			<tD align=\"center\" colspan=\"3\"><strong>Au total</strong></td>
			<tD align=\"center\">&nbsp;</td>
			<tD align=\"center\" colspan=\"3\"><strong>Ce mois</strong></td>
			<tD align=\"center\">&nbsp;</td>
			<tD align=\"center\" colspan=\"3\"><strong>Ce jour</strong></td>
		</tr>
		<tr>
			<td align=\"center\"><strong></strong></td>
			<tD align=\"center\"><strong>Vues</strong></td>
			<tD align=\"center\"><strong>Panier</strong></td>
			<tD align=\"center\"><strong>Cdes</strong></td>
			<tD align=\"center\">&nbsp;</td>
			<tD align=\"center\"><strong>Vues</strong></td>
			<tD align=\"center\"><strong>Panier</strong></td>
			<tD align=\"center\"><strong>Cdes</strong></td>
			<tD align=\"center\">&nbsp;</td>
			<tD align=\"center\"><strong>Vues</strong></td>
			<tD align=\"center\"><strong>Panier</strong></td>
			<tD align=\"center\"><strong>Cdes</strong></td>
		</tr>
		" ;
	$arts=$DB_site2->query("SELECT libelleFR, artid FROM article order by libelleFR asc");
	$visites = 0 ;
	$panier = 0 ;
	$commandes = 0 ;
	while ($art=$DB_site2->fetch_array($arts))
		{
		$art_tot=$DB_site2->query_first("SELECT sum(visites) as visites, sum(panier) as panier, sum(commandes) as commandes 
		FROM statistique_produit where artid = $art[artid]");
		$visites += $art_tot[visites] ;
		$panier += $art_tot[panier] ;
		$commandes += $art_tot[commandes] ;
		$anneemois = date ('Y-m') ;
		$arts_mois=$DB_site2->query_first("SELECT sum(visites) as visites, sum(panier) as panier, sum(commandes) as commandes 
		FROM statistique_produit where artid = '$art[artid]' and datestatistique LIKE '%$anneemois-%'");
		$anneemoisjour = date ('Y-m-d') ;
		$arts_jour=$DB_site2->query_first("SELECT sum(visites) as visites, sum(panier) as panier, sum(commandes) as commandes 
		FROM statistique_produit where artid = '$art[artid]' and datestatistique = '$anneemoisjour'");
		echo "
			<tr>
				<td align=\"left\" width=\"25%\">$art[libelleFR]</td>
				<tD align=\"center\" class=\"td_promos_alt1\">$art_tot[visites]</td>
				<tD align=\"center\" class=\"td_promos_alt1\">$art_tot[panier]</td>
				<tD align=\"center\" class=\"td_promos_alt1\">$art_tot[commandes]</td>
				<tD align=\"center\" bgcolor=\"#5F718A\" width=\"1\"></td>
				<tD align=\"center\" class=\"td_promos_alt2\">$arts_mois[visites]</td>
				<tD align=\"center\" class=\"td_promos_alt2\">$arts_mois[panier]</td>
				<tD align=\"center\" class=\"td_promos_alt2\">$arts_mois[commandes]</td>
				<tD align=\"center\" bgcolor=\"#5F718A\" width=\"1\"></td>
				<tD align=\"center\" class=\"td_promos_alt2\">$arts_jour[visites]</td>
				<tD align=\"center\" class=\"td_promos_alt2\">$arts_jour[panier]</td>
				<tD align=\"center\" class=\"td_promos_alt2\">$arts_jour[commandes]</td>
			</tr>" ;
		}
	echo "</table>
	<br><a href=\"statistiques.php\">Retour</a>" ;
	}
elseif ($action == "frequentation")
	{
	echo "<h3>+ de détails sur les pages</h3>
	<table class=\"table_moyen_paiement\">
		<tr>
			<td align=\"center\"><strong>Nom de la page</strong></td>
			<tD align=\"center\" colspan=\"3\"><strong>Nombre de vues</strong></td>
		</tr>
		<tr>
			<td align=\"center\"><strong></strong></td>
			<tD align=\"center\"><strong>Au total</strong></td>
			<tD align=\"center\"><strong>Ce mois</strong></td>
			<tD align=\"center\"><strong>Ce jour</strong></td>
		</tr>" ;
	$pages=$DB_site2->query("SELECT distinct(script) FROM statistique_page order by script asc");
	while ($page=$DB_site2->fetch_array($pages))
		{
		$pages_tot=$DB_site2->query_first("SELECT sum(visites) as visites FROM statistique_page where script = '$page[script]'");
		$anneemois = date ('Y-m') ;
		$page_mois=$DB_site2->query_first("SELECT sum(visites) as visites FROM statistique_page where script = '$page[script]' and datestatistique LIKE '%$anneemois-%'");
		$anneemoisjour = date ('Y-m-d') ;
		$page_jour=$DB_site2->query_first("SELECT sum(visites) as visites FROM statistique_page where script = '$page[script]' and datestatistique = '$anneemoisjour'");
		echo "
			<tr>
				<td align=\"left\">&nbsp;&nbsp;&nbsp;$page[script]</td>
				<tD align=\"center\">$pages_tot[visites]</td>
				<td align=\"center\">$page_mois[visites]</td>
				<td align=\"center\">$page_jour[visites]</td>
			</tr>" ;
		}
	echo "</table>
	<br><Br><a href=\"statistiques.php\">Retour</a>" ;
	}
	

	echo "			</td>
			</tr>	
		</table>	
     </font>
	</td>
   <td background=\"images2/cadre_r4_c10.gif\" width=\"3\" height=\"100%\" border=\"0\" alt=\"\"></td>
  </tr>
</table>
</td>
</tr>
<tr><td>

<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td rowspan=\"2\"><img name=\"cadre_r6_c2\" src=\"images2/cadre_r6_c2.gif\" width=\"10\" height=\"26\" border=\"0\" alt=\"\"></td>
   <td align=\"center\" height=\"23\" width=\"100%\"></td>
   <td rowspan=\"2\"><img name=\"cadre_r6_c9\" src=\"images2/cadre_r6_c9.gif\" width=\"8\" height=\"26\" border=\"0\" alt=\"\"></td>
  </tr>
  <tr>
   <td background=\"images2/cadre_r7_c4.gif\" width=\"100%\" height=\"3\"></td>
  </tr>
</table>
</td></tr></table>
</td>
</tr><tr>
</tr></table>
				  </td>
              </tr>
            </table>
		  </td>
        </tr>";

include "./footer.php";
?>