<?php

require "admin_global.php";

if( !in_array( "5", $modules ) )
	echo "<script language=\"javascript\">location.href=\"index.php\";</script>";

if( $action == "ajouter" || $action == "ajouter2"
 || $action == "editer" || $action == "editer2" )
{
	$bodyonload = "onLoad=\"myInit();\"";
	
	echo "
	<script type=\"text/javascript\" src=\"scripts/calendar/Js/CLanguage.js\"></script>
	<script type=\"text/javascript\" src=\"scripts/calendar/Js/DateControl.js\"></script>
	<script type=\"text/javascript\" src=\"scripts/calendar/Js/DOMCalendar.js\"></script>
	
	<script language=\"javascript\">";
	/*-------------------------------------------------------------------------------------------*/
	/*------------------------------------DEBUT DU CALENDRIER------------------------------------*/
	/*-------------------------------------------------------------------------------------------*/
	echo "
	var DCAL = null;
	
	GetDomLeft = function ( oNode )
	{
	  var oCurrentNode = oNode;
	  var iLeft = 0;
	  while (oCurrentNode.tagName != \"BODY\") {
		iLeft += oCurrentNode.offsetLeft;
		oCurrentNode = oCurrentNode.offsetParent;
	  };
	  return iLeft;
	};
	GetDomTop = function ( oNode )
	{
	  var oCurrentNode = oNode;
	  var iTop = 0;
	  while (oCurrentNode.tagName != \"BODY\") {
		iTop += oCurrentNode.offsetTop;
		oCurrentNode = oCurrentNode.offsetParent;
	  };
	  return iTop;
	};
	
	function myInit() {
	
	 // On recupere les objets images
	 oImg1 = document.getElementById(\"iDC1\");
	 oImg2 = document.getElementById(\"iDC2\");
	
	 // On instancie l'objet Calendar
	 DCAL = new DOMCalendar();
	
	 // On affecte le repertoire image
	 DCAL.SetImgPath(\"scripts/calendar/Img\");
	
	 // On ajoute des zones Calendar
	 // Name, Form : Nom et formulaire a controler
	 DCAL.AddCalendar(\"DC1\", document.formu.txt_datedebut);
	 DCAL.AddCalendar(\"DC2\", document.formu.txt_datefin);
	
	 // On affecte le premier jour de la semaine
	 // 0 : Dimanche -> 6 : Samedi
	 DCAL.SetFirstDayOfWeek(\"DC1\", 1);
	 DCAL.SetFirstDayOfWeek(\"DC2\", 1);
	 // On affecte la position par rapport a l'image
	 DCAL.SetPosition(\"DC1\", GetDomLeft(oImg1) + oImg1.offsetWidth + 10, GetDomTop(oImg1) - 32);
	 DCAL.SetPosition(\"DC2\", GetDomLeft(oImg2) + oImg2.offsetWidth + 10, GetDomTop(oImg2) - 32);
	 // On affecte les SelectList
	 //DCAL.HideSelectList(\"DC1\", document.formu.tSelect_1);
	 DCAL.Init();
	};";
	/*-----------------------------------------------------------------------------------------*/
	/*------------------------------------FIN DU CALENDRIER------------------------------------*/
	/*-----------------------------------------------------------------------------------------*/
	echo "</script>";
}

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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Promotions</font>
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
		<table align=\"center\">
			<tr>
				<td align=\"center\" valign=\"middle\">" ;


function convertirChaineEnDate( $la_date ) {
   @list( $jour, $mois, $annee ) = explode( '/', $la_date );
   return @date( 'Y-m-d',mktime(0,0,0,$mois,$jour,$annee) );
}

$article=$DB_site2->query_first( "select catid, $libelle, prix from article where artid = '$artid'" ); 

$lienretour = "<a href=\"catalogue.php?catid=$article[catid]\">Retour</a>" ;

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AFFICHAGE DES PROMOTIONS (action=voir)
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( $action == "voir" || !isset($action) ) {
	$i = 0;
	$rq_promotions = "select * from promotion where artid = '$artid'";
	$promotions = $DB_site2->query( $rq_promotions );
	
	echo "<h3>Liste des promotions pour l'article '$article[$libelle]' <br> (Prix : $article[prix] €)</h3><br>
		<table class=\"table_modules_boutiques\">";
	echo "<tr><td align=\"center\"><strong>Début de la promotion (jj/mm/aaaa)</strong></td>
			  <td align=\"center\"><strong>Fin de la promotion (jj/mm/aaaa)</strong></td>
			  <td align=\"center\"><strong>Prix promo</strong></td>
			  <td align=\"center\" width=\"35%\"><strong>ACTION</strong></td>
			</tr>";
	
	while( $promotion = $DB_site2->fetch_array( $promotions ) ) {
		list($annee_debut, $mois_debut, $jour_debut) = explode( "-", $promotion[datedebut] );
		list($annee_fin, $mois_fin, $jour_fin) = explode( "-", $promotion[datefin] );
		
	
		$rowalt = "td_".getrowbg();
		
		echo "<tr>";
		echo "  <td class=\"$rowalt\" align=\"center\">$jour_debut/$mois_debut/$annee_debut</td>";
		echo "  <td class=\"$rowalt\" align=\"center\">$jour_fin/$mois_fin/$annee_fin</td>";
		echo "  <td class=\"$rowalt\" align=\"center\">$promotion[prix] €";
		echo "  <td class=\"$rowalt\" align=\"center\">
				  <a href=\"promotion.php?action=editer&artid=$artid&promoid=$promotion[promotionid]\" target=\"_self\">Editer</a>
				  | <a href=\"promotion.php?action=supprimer&artid=$artid&promoid=$promotion[promotionid]\" target=\"_self\">Supprimer</a>
				</td>";
		echo "</tr>";
		$i++;
	}
	if( $i == 0 )
		echo "<tr><td><br>Aucune promotion pour cet article</td></tr>";
	
	echo "</table>";
	echo "<div align=\"center\"><br><br><a href=\"promotion.php?action=ajouter&artid=$artid\" target=\"_self\">Ajouter une promotion</a></div>";
	echo "<div align=\"center\"><br><br><a href=\"catalogue.php?catid=$article[catid]\" target=\"_self\">Retour</a></div>";

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AJOUTER UNE PROMOTION (action=ajouter)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
} elseif( $action == "ajouter" ) {

	echo "<div align=\"center\"><h3>Article '$article[$libelle]'<br>(Prix : $article[prix] €)</h3></div>";

	echo "<form method=\"post\" action=\"promotion.php\" name=\"formu\">
		    <table border=\"0\">
		  	  <tr><td align=\"right\">Date de début (jj/mm/aaaa) :</td>
			  	  <td><input type = \"text\" name=\"txt_datedebut\" size=\"10\" maxlength=\"10\">
				  <a href=\"javascript:DCAL.Show('DC1');\" border=\"0\"><img src=\"scripts/calendar/Img/c_b.gif\" border=\"0\" id=\"iDC1\"></a>
				  </td>
			  </tr><td align=\"right\">Date de fin (jj/mm/aaaa) :</td>
			  	  <td><input type = \"text\" name=\"txt_datefin\" size=\"10\" maxlength=\"10\">
				  <a href=\"javascript:DCAL.Show('DC2');\" border=\"0\"><img src=\"scripts/calendar/Img/c_b.gif\" border=\"0\" id=\"iDC2\"></a>
				  </td>
			  <tr><td align=\"right\">Prix promo : </td>
			  	<td><input type = \"text\" name=\"txt_prix\" size=\"10\">
				</td>
			  </tr>
			</table><br><br>
		  <input type = \"hidden\" name = \"action\" value = \"ajouter2\">
		  <input type = \"hidden\" name = \"artid\" value = \"$artid\">
		 <input type = \"submit\" value = \"Ajouter la promotion\">
		  </form>";
	
////////////////////////////////////////////////////////////////////////////////////////////////////////
// AJOUTER UNE PROMOTION (action=ajouter2)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
} elseif( $action == "ajouter2" ) {
	$erreur_saisie = 0;
	
	echo "<div align=\"center\"><h3>Article '$article[$libelle]'<br>(Prix : $article[prix] €)</h3></div>";

	// Vérification d'erreurs
	echo "<table align=\"center\">";
	
	if( !isset($txt_datedebut) || $txt_datedebut == "" 
	  	|| !isset($txt_datefin) || $txt_datefin == ""
		|| !isset($txt_prix) || $txt_prix == "" ) {
		echo "<tr><td><b>Veuillez spécifier :</b></td></tr>";
		$erreur_saisie = 1;
	} else {
		$tmp_prix = str_replace( ",", ".", $txt_prix );

		if( ( (isset($txt_prix) && $txt_prix!="") && !is_numeric($tmp_prix) )
			|| ( (isset($txt_prix) && $txt_prix!="") && is_numeric($tmp_prix) && ($tmp_prix > $article[prix]) ) ) {
			//echo "<table align=\"center\"><tr><td><b>";
			$erreur_saisie = 1;
		}
	}

	if( !isset($txt_datedebut) || $txt_datedebut == "" ) {
		echo "<tr><td><b>  - La date de début (jj/mm/aaaa)</b></td></tr>";
		$erreur_saisie = 1;
	}
	if( !isset($txt_datefin) || $txt_datefin == "" ) {
		echo "<tr><td><b>  - La date de fin (jj/mm/aaaa)</b></td></tr>";
		$erreur_saisie = 1;
	}
	
	if( !isset($txt_prix) || $txt_prix == "" ) {
		echo "<tr><td><b>  - Le prix<b></td></tr>";
		$erreur_saisie = 1;
	} else {
		$tmp_prix = str_replace( ",", ".", $txt_prix );
		if( !is_numeric( $tmp_prix ) ) {
			echo "<tr><td><b>  Le prix doit être de valeur numérique<b></td></tr>";
			$erreur_saisie = 1;
		}
		elseif( $tmp_prix >= $article[prix] ) {
			echo "<tr><td><b>  Le prix de la promotion doit etre inférieur au prix normal de l'article<b></td></tr>";
			$erreur_saisie = 1;
		}
		elseif( $tmp_prix < 0 ) {
			echo "<tr><td><b>  Le prix doit etre supérieur à 0<b></td></tr>";
			$erreur_saisie = 1;
		}
	}


	if( ( isset($txt_datedebut) && $txt_datedebut != "" )
		 && ( isset($txt_datefin) && $txt_datefin != "" ) )
	{
		// Test des dates
		$tmp_datedebut = explode( '-', convertirChaineEnDate($txt_datedebut) );
		$str_datedebut = $tmp_datedebut[0] . $tmp_datedebut[1] . $tmp_datedebut[2];
		$tmp_datefin = explode( '-', convertirChaineEnDate($txt_datefin) );
		$str_datefin = $tmp_datefin[0] . $tmp_datefin[1] . $tmp_datefin[2];
		
		if( $str_datedebut > $str_datefin ) {
			echo "<tr><td><b>La date de début doit être avant la date de fin</b></td></tr>";
			$erreur_saisie = 1;
		} else {
			// On vérifie qu'il n'y ait pas de promotion en même temps
			$i = 0;
			$rq_promotions = "select * from promotion where artid = '$artid'
							  order by datedebut";
			$promotions = $DB_site2->query( $rq_promotions );
			
			while( $promotion = $DB_site2->fetch_array( $promotions ) ) {
				$tmp_datedebut_bdd = explode( '-', $promotion[datedebut] );
				$str_datedebut_bdd = $tmp_datedebut_bdd[0] . $tmp_datedebut_bdd[1] . $tmp_datedebut_bdd[2];
				$tmp_datefin_bdd = explode( '-', $promotion[datefin] );
				$str_datefin_bdd = $tmp_datefin_bdd[0] . $tmp_datefin_bdd[1] . $tmp_datefin_bdd[2];

				if( $tmp_datedebut < $tmp_datefin_bdd )
					if( $tmp_datefin > $tmp_datedebut_bdd ) {
						list($annee_debut, $mois_debut, $jour_debut) = explode( "-", $promotion[datedebut] );
						list($annee_fin, $mois_fin, $jour_fin) = explode( "-", $promotion[datefin] );
						echo "<tr><td><b>Il existe déja une promotion pour ces dates :<b></td></tr>";
						echo "<tr><td><b>$nbsp$nbsp$nbsp Du $jour_debut/$mois_debut/$annee_debut au $jour_fin/$mois_fin/$annee_fin de $promotion[prix]€<b></td></tr>";
						$erreur_saisie = 1;
					}
				$i++;
			}
		}
	}
	echo "</table>";

	if( $erreur_saisie == 1 )
	{
		// Réaffichage du formulaire en cas d'erreur
		echo "<form method=\"post\" action=\"promotion.php\" name=\"formu\">
			<table border=\"0\">
			  <tr><td align=\"right\">Date de début :</td>
				  <td><input type = \"text\"  name=\"txt_datedebut\" value=\"$txt_datedebut\" size=\"10\" maxlength=\"10\">
				  <a href=\"javascript:DCAL.Show('DC1');\" border=\"0\"><img src=\"scripts/calendar/Img/c_b.gif\" border=\"0\" id=\"iDC1\"></a>
				  </td>
			  </tr><td align=\"right\">Date de fin :</td>
				  <td><input type = \"text\"  name=\"txt_datefin\" value=\"$txt_datefin\" size=\"10\" maxlength=\"10\">
				  <a href=\"javascript:DCAL.Show('DC2');\" border=\"0\"><img src=\"scripts/calendar/Img/c_b.gif\" border=\"0\" id=\"iDC2\"></a>
				  </td>
			  <tr><td align=\"right\">Prix promo :</td>";
			  
			  if( ( (isset($txt_prix) || $txt_prix!="") && !is_numeric($tmp_prix) )
					|| ( (isset($txt_prix) || $txt_prix!="") && is_numeric($tmp_prix) && ($tmp_prix >= $article[prix]) ) )
					echo "<td><input type = \"text\" name=\"txt_prix\"  size=\"10\">";
					else
					echo "<td><input type = \"text\" name=\"txt_prix\"  value=\"$txt_prix\" size=\"10\">";
		echo "	</td>
			  </tr>
			</table><br><br>
		  <input type = \"hidden\" name = \"action\" value = \"ajouter2\">
		  <input type = \"hidden\" name = \"artid\" value = \"$artid\">
		  <input type = \"hidden\" name = \"promoid\" value = \"$promoid\">		  
		  <input type = \"submit\" value = \"Ajouter la promotion\">
		  </form>";


	} else {
		// Ajout de la promotion
		$tmp_prix = str_replace (",", ".", $txt_prix) ;
		$last=$DB_site2->query_first("select max(position) from promotion");
		if ($last[0] != "")
			{
			$position = $last[0] + 1 ;
			}
		else
			{
			$position = 1 ;
			}
		$rq_ajouter_promo = "INSERT INTO promotion (artid, position, datedebut, datefin, prix)
							 VALUES ('".htmlentities($artid)."', '$position', '".htmlentities(convertirChaineEnDate($txt_datedebut))."',
							 		 '".htmlentities(convertirChaineEnDate($txt_datefin))."', '".htmlentities($tmp_prix). "')" ;
		$DB_site2->query($rq_ajouter_promo);
		if (in_array("105", $modules))
			{
			$arts_lies=$DB_site2->query("select * from liens_articles where article_base = '$artid' or article_lie = '$artid'") ;
			while ($art_lie=$DB_site2->fetch_array($arts_lies))
				{
				if ($art_lie[article_lie] != $artid)
					{
					$art = $art_lie[article_lie] ;
					}
				else
					{
					$art = $art_lie[article_base] ;
					}
				$last=$DB_site2->query_first("select max(position) from promotion");
				if ($last[0] != "")
					{
					$position = $last[0] + 1 ;
					}
				else
					{
					$position = 1 ;
					}
				$DB_site2->query("INSERT INTO promotion (artid, position, datedebut, datefin, prix)
								 VALUES ('$art', '$position', '".htmlentities(convertirChaineEnDate($txt_datedebut))."',
										 '".htmlentities(convertirChaineEnDate($txt_datefin))."', '".htmlentities($tmp_prix). "')") ;
				}
			}
		echo "<script language=\"javascript\">location.href=\"promotion.php?action=voir&artid=$artid\";</script>";
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// EDITER UNE PROMOTION (action=editer)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
} elseif( $action == "editer" ) {

	$promotion = $DB_site2->query_first( "select * from promotion where promotionid = $promoid" );

	list($annee_debut, $mois_debut, $jour_debut) = explode( "-", $promotion[datedebut] );
	list($annee_fin, $mois_fin, $jour_fin) = explode( "-", $promotion[datefin] );

	echo "<div align=\"center\"><h3>Promotion sur l'article '$article[$libelle]'<br>(Prix : $article[prix] €)</h3></div>";

	echo "<form method=\"post\" action=\"promotion.php\" name=\"formu\">
		    <table border=\"0\">
		  	  <tr><td align=\"right\">Date de début (jj/mm/aaaa) :</td>
			  	  <td><input type = \"text\" name=\"txt_datedebut\" value=\"$jour_debut/$mois_debut/$annee_debut\" size=\"10\" maxlength=\"10\">
				  <a href=\"javascript:DCAL.Show('DC1');\" border=\"0\"><img src=\"scripts/calendar/Img/c_b.gif\" border=\"0\" id=\"iDC1\"></a>
				  </td>
			  </tr><td align=\"right\">Date de fin (jj/mm/aaaa) :</td>
			  	  <td><input type = \"text\" name=\"txt_datefin\" value=\"$jour_fin/$mois_fin/$annee_fin\" size=\"10\" maxlength=\"10\">
				  <a href=\"javascript:DCAL.Show('DC2');\" border=\"0\"><img src=\"scripts/calendar/Img/c_b.gif\" border=\"0\" id=\"iDC2\"></a>
				  </td>
			  <tr><td align=\"right\">Prix promo :</td>
			  	<td><input type = \"text\" name=\"txt_prix\" value=\"$promotion[prix]\" size=\"10\">
				</td>
			  </tr>
			</table><br><br>
		  <input type = \"hidden\" name = \"action\" value = \"editer2\">
		  <input type = \"hidden\" name = \"artid\" value = \"$artid\">
		  <input type = \"hidden\" name = \"promoid\" value = \"$promoid\">		  
		  <input type = \"submit\" value = \"Modifier la promotion\">
		  </form>";

////////////////////////////////////////////////////////////////////////////////////////////////////////
// EDITER UNE PROMOTION (action=editer2)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
} elseif( $action == "editer2" ) {

	echo "<div align=\"center\"><h3>Promotion sur l'article '$article[$libelle]'<br>(Prix : $article[prix] €)</h3></div>";

	// Vérification d'erreurs
	echo "<table align=\"center\">";
	
	if( !isset($txt_datedebut) || $txt_datedebut == "" 
	  	|| !isset($txt_datefin) || $txt_datefin == ""
		|| !isset($txt_prix) || $txt_prix == "" ) {
		echo "<tr><td><b>Veuillez spécifier :</b></td></tr>";
		$erreur_saisie = 1;
	} else {
		$tmp_prix = str_replace( ",", ".", $txt_prix );

		if( ( (isset($txt_prix) && $txt_prix!="") && !is_numeric($tmp_prix) )
			|| ( (isset($txt_prix) && $txt_prix!="") && is_numeric($tmp_prix) && ($tmp_prix > $article[prix]) ) ) {
			//echo "<table align=\"center\"><tr><td><b>";
			$erreur_saisie = 1;
		}
	}

	if( !isset($txt_datedebut) || $txt_datedebut == "" ) {
		echo "<tr><td><b>  - La date de début (jj/mm/aaaa)</b></td></tr>";
		$erreur_saisie = 1;
	}
	if( !isset($txt_datefin) || $txt_datefin == "" ) {
		echo "<tr><td><b>  - La date de fin (jj/mm/aaaa)</b></td></tr>";
		$erreur_saisie = 1;
	}
	
	if( !isset($txt_prix) || $txt_prix == "" ) {
		echo "<tr><td><b>  - Le prix<b></td></tr>";
		$erreur_saisie = 1;
	} else {
		$tmp_prix = str_replace( ",", ".", $txt_prix );
		if( !is_numeric( $tmp_prix ) ) {
			echo "<tr><td><b>  Le prix doit être de valeur numérique<b></td></tr>";
			$erreur_saisie = 1;
		}
		elseif( $tmp_prix >= $article[prix] ) {
			echo "<tr><td><b>  Le prix de la promotion doit etre inférieur au prix normal de l'article<b></td></tr>";
			$erreur_saisie = 1;
		}
		elseif( $tmp_prix < 0 ) {
			echo "<tr><td><b>  Le prix doit etre supérieur à 0<b></td></tr>";
			$erreur_saisie = 1;
		}
	}

	if( ( isset($txt_datedebut) && $txt_datedebut != "" )
		 && ( isset($txt_datefin) && $txt_datefin != "" ) )
	{
		// Test des dates
		$tmp_datedebut = explode( '-', convertirChaineEnDate($txt_datedebut) );
		$str_datedebut = $tmp_datedebut[0] . $tmp_datedebut[1] . $tmp_datedebut[2];
		$tmp_datefin = explode( '-', convertirChaineEnDate($txt_datefin) );
		$str_datefin = $tmp_datefin[0] . $tmp_datefin[1] . $tmp_datefin[2];
		
		if( $str_datedebut > $str_datefin ) {
			echo "<tr><td><b>La date de début doit être avant la date de fin</b></td></tr>";
			$erreur_saisie = 1;
		} else {
			$i = 0;
			$rq_promotions = "select * from promotion where artid = '$artid'
							  and promotionid <> $promoid order by datedebut";
			$promotions = $DB_site2->query( $rq_promotions );
			
			while( $promotion = $DB_site2->fetch_array( $promotions ) ) {
				$tmp_datedebut_bdd = explode( '-', $promotion[datedebut] );
				$str_datedebut_bdd = $tmp_datedebut_bdd[0] . $tmp_datedebut_bdd[1] . $tmp_datedebut_bdd[2];
				$tmp_datefin_bdd = explode( '-', $promotion[datefin] );
				$str_datefin_bdd = $tmp_datefin_bdd[0] . $tmp_datefin_bdd[1] . $tmp_datefin_bdd[2];

				if( $tmp_datedebut <= $tmp_datefin_bdd )
					if( $tmp_datefin >= $tmp_datedebut_bdd ) {
						list($annee_debut, $mois_debut, $jour_debut) = explode( "-", $promotion[datedebut] );
						list($annee_fin, $mois_fin, $jour_fin) = explode( "-", $promotion[datefin] );
						echo "<tr><td><b>Il existe déja une promotion pour ces dates :<b></td></tr>";
						echo "<tr><td><b>$nbsp$nbsp$nbsp Du $jour_debut/$mois_debut/$annee_debut au $jour_fin/$mois_fin/$annee_fin de $promotion[prix]€<b></td></tr>";
						$erreur_saisie = 1;
					}
				$i++;
			}
			if( $i == 0 ) {	
			}
		}
	}
	echo "</table>";

	if( $erreur_saisie == 1 )
	{
		// Réaffichage du formulaire en cas d'erreur
		echo "<form method=\"post\" action=\"promotion.php\" name=\"formu\">
			<table border=\"0\">
			  <tr><td align=\"right\">Date de début :</td>
				  <td><input type = \"text\" name=\"txt_datedebut\" value=\"$txt_datedebut\" size=\"10\" maxlength=\"10\">
				  <a href=\"javascript:DCAL.Show('DC1');\" border=\"0\"><img src=\"scripts/calendar/Img/c_b.gif\" border=\"0\" id=\"iDC1\"></a>
				  </td>
			  </tr><td align=\"right\">Date de fin :</td>
				  <td><input type = \"text\" name=\"txt_datefin\" value=\"$txt_datefin\" size=\"10\" maxlength=\"10\">
				  <a href=\"javascript:DCAL.Show('DC2');\" border=\"0\"><img src=\"scripts/calendar/Img/c_b.gif\" border=\"0\" id=\"iDC2\"></a>
				  </td>
			  <tr><td align=\"right\">Prix promo :</td>";
			  
			  if( ( (isset($txt_prix) || $txt_prix!="") && !is_numeric($tmp_prix) )
					|| ( (isset($txt_prix) || $txt_prix!="") && is_numeric($tmp_prix) && ($tmp_prix >= $article[prix]) ) )
					echo "<td><input type = \"text\" name=\"txt_prix\"  size=\"10\">";
					else
					echo "<td><input type = \"text\" name=\"txt_prix\"  value=\"$txt_prix\" size=\"10\">";
		echo "	</td>
			  </tr>
			</table><br><br>
		  <input type = \"hidden\" name = \"action\" value = \"editer2\">
		  <input type = \"hidden\" name = \"artid\" value = \"$artid\">
		  <input type = \"hidden\" name = \"promoid\" value = \"$promoid\">		  
		  <input type = \"submit\" value = \"Modifier la promotion\">
		  </form>";

	} else {
		// Modification de la promotion
		$tmp_prix = str_replace (",", ".", $txt_prix) ;
		$rq_modifier_promo = "UPDATE promotion SET datedebut = '".htmlentities(convertirChaineEnDate($txt_datedebut))."', 
							  datefin = '".htmlentities(convertirChaineEnDate($txt_datefin))."', prix='".htmlentities($tmp_prix)."' 
							 WHERE promotionid = '$promoid'" ; 
		$DB_site2->query($rq_modifier_promo) ;
		if (in_array("105", $modules))
			{
			$promo_a_modifier=$DB_site2->query_first("select * from promotion where promotionid = '$promoid'") ;
			$arts_lies=$DB_site2->query("select article_lie from liens_articles where article_base = '$artid' or article_lie = '$artid'") ;
			while ($art_lie=$DB_site2->fetch_array($arts_lies))
				{
				if ($art_lie[article_lie] != $artid)
					{
					$art = $art_lie[article_lie] ;
					}
				else
					{
					$art = $art_lie[article_base] ;
					}
				$promo_identique=$DB_site2->query_first("select promotionid from promotion where artid = '$art'
														and prix = '$promo_a_supprimer[prix]' and datedebut = '$promo_a_supprimer[datedebut]'
														and datefin = '$promo_a_supprimer[datefin]'") ;
				$DB_site2->query("UPDATE promotion SET datedebut = '".htmlentities(convertirChaineEnDate($txt_datedebut))."', 
								  datefin = '".htmlentities(convertirChaineEnDate($txt_datefin))."', prix='".htmlentities($tmp_prix)."' 
								 WHERE promotionid = '$promo_identique[promotionid]'") ;
				}
			}
		echo "<script language=\"javascript\">location.href=\"promotion.php?action=voir&artid=$artid\";</script>";
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER UNE PROMOTION (action=supprimer)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
} elseif( $action == "supprimer" ) {
	// Confirmation de suppression
	echo "Etes-vous sûr'e) de vouloir supprimer cette promotion ?<br><br>
		  <a href=\"promotion.php?action=supprimer2&artid=$artid&promoid=$promoid\">Oui</a>
		  | <a href=\"promotion.php?action=voir&artid=$artid\">Non</a>";

////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER UNE PROMOTION (action=supprimer2)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
	} 
elseif ($action == "supprimer2") 
	{
	if (in_array("105", $modules))
		{
		$promo_a_supprimer=$DB_site2->query_first("select * from promotion where promotionid = '$promoid'") ;
		$arts_lies=$DB_site2->query("select article_lie from liens_articles where article_base = '$artid' or article_lie = '$artid'") ;
		while ($art_lie=$DB_site2->fetch_array($arts_lies))
			{
			if ($art_lie[article_lie] != $artid)
				{
				$art = $art_lie[article_lie] ;
				}
			else
				{
				$art = $art_lie[article_base] ;
				}
			$promo_identique=$DB_site2->query_first("select promotionid from promotion where artid = '$art'
													and prix = '$promo_a_supprimer[prix]' and datedebut = '$promo_a_supprimer[datedebut]'
													and datefin = '$promo_a_supprimer[datefin]'") ;
			if ($promo_identique[promotionid] != "")
				{
				//$DB_site2->query("delete from promotion where promotionid = '$promo_identique[promotionid]'") ;


				$position_page=$DB_site2->query_first("SELECT position FROM promotion WHERE promotionid = '$promo_identique[promotionid]'");
				$position_page = $position_page[0];
				$rq_positions_suivantes=$DB_site2->query("SELECT promotionid, position FROM promotion WHERE position > $position_page" );
				while ($rs_positions_suivantes=$DB_site2->fetch_array($rq_positions_suivantes)) 
					{
					$position_temp = $rs_positions_suivantes[position] - 1;
					$DB_site2->query("UPDATE promotion SET position = '$position_temp' WHERE promotionid = '$rs_positions_suivantes[promotionid]'");
					}
				$DB_site2->query("DELETE from promotion WHERE promotionid = '$promo_identique[promotionid]'");
				}
			}
		}
	//$DB_site2->query("delete from promotion where promotionid = '$promoid'") ;


	$position_page=$DB_site2->query_first("SELECT position FROM promotion WHERE promotionid = '$promoid'");
	$position_page = $position_page[0];
	$rq_positions_suivantes=$DB_site2->query("SELECT promotionid, position FROM promotion WHERE position > $position_page" );
	while ($rs_positions_suivantes=$DB_site2->fetch_array($rq_positions_suivantes)) 
		{
		$position_temp = $rs_positions_suivantes[position] - 1;
		$DB_site2->query("UPDATE promotion SET position = '$position_temp' WHERE promotionid = '$rs_positions_suivantes[promotionid]'");
		}
	$DB_site2->query("DELETE from promotion WHERE promotionid = '$promoid'");
	echo "<script language=\"javascript\">location.href=\"promotion.php?action=voir&artid=$artid\";</script>";
	}

echo "				<br><br>$lienretour<br><br>

</td>
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
        </tr>
";


include "./footer.php";
?>