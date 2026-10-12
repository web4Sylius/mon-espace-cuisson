<?php

require "admin_global.php";
echo "<script language=\"javascript\">
function change(num)
	{
	if (num == '0')
		{
		document.getElementById('promo0').style.display='block'
		document.getElementById('promo1').style.display='none'
		document.getElementById('promo2').style.display='none'
		}
	else if (num == '1')
		{
		document.getElementById('promo0').style.display='none'
		document.getElementById('promo1').style.display='block'
		document.getElementById('promo2').style.display='none'
		}
	else if (num == '2')
		{
		document.getElementById('promo0').style.display='none'
		document.getElementById('promo1').style.display='none'
		document.getElementById('promo2').style.display='block'
		}
	else
		{
		document.getElementById('promo0').style.display='none'
		document.getElementById('promo1').style.display='none'
		document.getElementById('promo2').style.display='none'
		}
	}

function liste_articles()
	{
	document.formu.action.value='ajouterbis'
	document.formu.submit();
	}

function arrondir(nombre, decimales) // affiliation
	{
	  if (isNaN(nombre)) return '';
	  if (decimales == 0) 
		{
		 var n = Math.round(nombre);
		 return n.toString();
	  }
  else if (decimales > 0) 
	{
	 if (document.all) 
		var undefined; // Netscape 4 ne connaît pas la constante undefined
	 var p10 = Math.pow(10, decimales);
	 var n = Math.round(nombre * p10);
	 n = n.toString();
	 var point = n.length - decimales;
	 if (point >= 0) 
		n = n.substring(0, point) + '.' + n.substring(point);
	 else if (n == 0)
		n = '0.00';
	  }
  else 
	{
	 var p10 = Math.pow(10, -decimales);
	 var n = Math.round(nombre / p10) * p10;
	 n = n.toString();
	  }
  if (nombre.signe_decimal == undefined) 
	return n;
  else 
	return n.replace('.', nombre.signe_decimal);
	}	

function updatePrix(valeur)
	{
	if (valeur != parseFloat(valeur))
		{
		var newvaleur = document.getElementById('pct').value.substr(0, document.getElementById('pct').value.length-1);
		document.getElementById('pct').value = newvaleur;
		var liste = document.getElementById('liste_artids').value ;
		var montableaudarticles=liste.split(',');
		var x ;
		for (x=1; x<montableaudarticles.length; x++) 
			{
			document.getElementById('prixpromo_'+montableaudarticles[x]).value = '' ;
			if (document.getElementById('prixpromohidden_'+montableaudarticles[x]))
				{
				document.getElementById('prixpromohidden_'+montableaudarticles[x]).value = '' ;
				}
			}
		}
	else if (valeur < 100 && valeur > 0)
		{
		var liste = document.getElementById('liste_artids').value ;
		var montableaudarticles=liste.split(',');
		var x ;
		for (x=1; x<montableaudarticles.length; x++) 
			{
			var prixnormal = document.getElementById('prix_'+montableaudarticles[x]).value ;
			if (prixnormal > 0)
				{
				var prixpromo = prixnormal - prixnormal*valeur/100 ;
				prixpromo = prixpromo.toFixed(2);
				if (prixpromo.substr(0, 1) == '.')
					{
					prixpromo = '0'+prixpromo ;
					}
				document.getElementById('prixpromo_'+montableaudarticles[x]).value = prixpromo ;
				if (document.getElementById('prixpromohidden_'+montableaudarticles[x]))
					{
					document.getElementById('prixpromohidden_'+montableaudarticles[x]).value = prixpromo ;
					}
				}
			}
		}
	else
		{
		var newvaleur = document.getElementById('pct').value.substr(0, document.getElementById('pct').value.length-1);
		document.getElementById('pct').value = newvaleur;
		alert('Le pourcentage doit être compris entre 0 et 100');
		}
	}

</script>" ;
if (!in_array("5", $modules))
	echo "<script language=\"javascript\">location.href=\"index.php\";</script>";

if ($action == "ajouter" or $action == "ajouterbis" or $action == "ajouter2" or $action == "editer" or $action == "editer2")
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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Gestion des promotions</font>
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
   <td align=\"center\" valign=\"middle\">" ;


function convertirChaineEnDate($la_date) 
	{
   	@list( $jour, $mois, $annee ) = explode( '/', $la_date );
   	return @date( 'Y-m-d',mktime(0,0,0,$mois,$jour,$annee) );
	}

function arborescence($parentid)
	{
	global $DB_site2,$select,$niveau,$libelle,$catid,$libellecat, $promo_art;
	$niveau .= "--->" ;
	$arbos=$DB_site2->query("select $libelle,catid from categorie where parentid = '$parentid' ORDER BY $libelle");
	while ($arbo=$DB_site2->fetch_array($arbos)) 
		{
		$articles=$DB_site2->query("select * from article where catid = '$arbo[catid]' ORDER BY $libelle");
		$options = "" ;
		if ($DB_site2->num_rows($articles) > 0)
			{
			$niveauart = "" ;
			$i = 0 ;
			while ($i++ <= strlen($niveau))
				{
				$niveauart .= "&nbsp;&nbsp;" ;
				}
			while ($article=$DB_site2->fetch_array($articles)) 
				{
				if (strlen($article[artcode]) > 60)
					{
					$article[artcode] = substr($article[artcode], 0, 60) . "..." ;
					}
				if ($article[artid] == $promo_art)
					{
					$options .= "<option value=\"$article[artid]\" selected>$niveauart $article[artcode]</option>\n" ;
					}
				else
					{
					$options .= "<option value=\"$article[artid]\">$niveauart $article[artcode]</option>\n" ;
					}
				}
			}
		if (strlen($arbo[$libelle]) > 60)
			{
			$arbo[$libelle] = substr($arbo[$libelle], 0, 60) . "..." ;
			}
		$select.="<option value=\"$arbo[catid]\" style=\"background-color:#EBEBE4;color:#000000;\" disabled=\"disabled\">$niveau $arbo[$libelle]</option>\n$options";
		arborescence($arbo[catid]);
		}
	$niveau=substr($niveau , 0 , strlen($niveau)-4 );
	return ($select);
	}

function arborescence2($parentid)
	{
	global $DB_site2,$select2,$niveau,$libelle,$catid,$libellecat, $promo_cat;
	$niveau .= "--->" ;
	$arbos=$DB_site2->query("select $libelle,catid from categorie where parentid = '$parentid' ORDER BY $libelle");
	while ($arbo=$DB_site2->fetch_array($arbos)) 
		{
		$articles=$DB_site2->query("select * from article where catid = '$arbo[catid]' ORDER BY $libelle");
		$options = "" ;
		$count = $DB_site2->num_rows($articles) ;
		if ($count > 0)
			{
			$niveauart = "" ;
			$i = 0 ;
			while ($i++ <= strlen($niveau))
				{
				$niveauart .= "&nbsp;&nbsp;" ;
				}
			$options .= "<option value=\"\" style=\"background-color:#EBEBE4;color:#000000;\" disabled=\"disabled\">$niveauart $count article(s)</option>\n" ;
			$options .= "<option value=\"\" style=\"background-color:#EBEBE4;color:#000000;\" disabled=\"disabled\"></option>\n" ;
			if (strlen($arbo[$libelle]) > 60)
				{
				$arbo[$libelle] = substr($arbo[$libelle], 0, 60) . "..." ;
				}
			if ($arbo[catid] == $promo_cat)
				{
				$select2.="<option value=\"$arbo[catid]\" selected>$niveau $arbo[$libelle]</option>\n$options";
				}
			else
				{
				$select2.="<option value=\"$arbo[catid]\">$niveau $arbo[$libelle]</option>\n$options";
				}
			}
		else
			{
			if ($arbo[catid] == $promo_cat)
				{
				$select2.="<option value=\"$arbo[catid]\" selected>$niveau $arbo[$libelle]</option>\n$options";
				}
			else
				{
				$select2.="<option value=\"$arbo[catid]\">$niveau $arbo[$libelle]</option>\n$options";
				}
			}
		arborescence2($arbo[catid]);
		}
	$niveau=substr($niveau , 0 , strlen($niveau)-4 );
	return ($select2);
	}


if ($action == "ajouter3") 
	{
	switch ($type_promo)
		{
		case '0':
			$DB_site2->query("insert into promotion (artid, prix, datedebut, datefin) values ('$artid', '$prixpromo', '$datedebut', '$datefin')");
			break;
		case '1':
			$articles = explode(",", $liste_artids);
			if (count($articles) > 1)
				{
				$DB_site2->query("insert into promotion_groupe (groupeid, pourcentage, promotiontypeid) values ('$promo_marque', '$pct', '$type_promo')");
				$insertid=$DB_site2->insert_id();
				foreach ($articles as $key => $value)
					{
					if ($key != 0)
						{
						$DB_site2->query("insert into promotion (artid, prix, datedebut, datefin, promotiongroupeid) values ('$value', '$prixpromohidden[$value]', '$datedebut', '$datefin', '$insertid')");
						}
					}
				}
			break;
		case '2':
			$articles = explode(",", $liste_artids);
			if (count($articles) > 1)
				{
				$DB_site2->query("insert into promotion_groupe (groupeid, pourcentage, promotiontypeid) values ('$promo_cat', '$pct', '$type_promo')");
				$insertid=$DB_site2->insert_id();
				foreach ($articles as $key => $value)
					{
					if ($key != 0)
						{
						$DB_site2->query("insert into promotion (artid, prix, datedebut, datefin, promotiongroupeid) values ('$value', '$prixpromohidden[$value]', '$datedebut', '$datefin', '$insertid')");
						}
					}
				}
			break;
		}

	echo "La promotion a bien été créée.<br><br>
	<a href=\"promotions.php\">Retour</a>" ;
	}

if ($action == "ajouter2") 
	{
	$erreur = "" ;
	if (!isset($txt_datedebut) or $txt_datedebut == "") 
		{
		$erreur .= "Vous devez choisir une date de début pour la promotion.<br>";
		} 
	if (!isset($txt_datefin) or $txt_datefin == "") 
		{
		$erreur .= "Vous devez choisir une date de fin pour la promotion.<br>";
		} 
	if (!isset($type_promo) or $type_promo == "") 
		{
		$erreur .= "Vous devez choisir un type de promotion.<br>";
		} 
	else
		{
		switch ($type_promo)
			{
			case '0':
				$debut = explode("/", $txt_datedebut);
				$fin = explode("/", $txt_datefin);
				if ("$debut[2]-$debut[1]-$debut[0]" > "$fin[2]-$fin[1]-$fin[0]")
					{
					$erreur .= "La date de fin doit être ultérieure à la date de début.<br>" ;
					}
				if (!isset($promo_art) or $promo_art == "")
					{
					$erreur .= "Vous devez choisir un article pour la promotion.<br>";
					}
				else
					{
					if (!isset($prixpromo[$promo_art]) or $prixpromo[$promo_art] == "")
						{
						$erreur .= "Vous devez définir le prix de l'article en promotion.<br>";
						}
					elseif ($prixpromo[$promo_art] == 0 or $prixpromo[$promo_art] >= $prix[$promo_art])
						{
						$erreur .= "Le prix en promotion doit être supérieur à 0 et inférieur au prix normal.<br>";
						}
					}
				$promotions_existantes=$DB_site2->query("select * from promotion where artid = '$promo_art' order by datedebut");
				if ($DB_site2->num_rows($promotions_existantes) > 0)
					{
					while ($promotion_existante=$DB_site2->fetch_array($promotions_existantes))
						{
						if (("$debut[2]-$debut[1]-$debut[0]" < $promotion_existante[datedebut] and "$fin[2]-$fin[1]-$fin[0]" <= $promotion_existante[datedebut]) or ("$debut[2]-$debut[1]-$debut[0]" >= $promotion_existante[datefin] and "$fin[2]-$fin[1]-$fin[0]" > $promotion_existante[datefin]))
							{
							// rien
							}
						else
							{
							$deb = explode("-", $promotion_existante[datedebut]);
							$fin = explode("-", $promotion_existante[datefin]);
							$erreur .= "Il y a un conflit avec la promotion du $deb[2]/$deb[1]/$deb[0] au $fin[2]/$fin[1]/$fin[0].<br>";
							}
						}
					}
				if ($erreur != "")
					{
					echo "<font class=\"cost\">$erreur</font>" ;
					$action = "ajouterbis" ;
					}
				else
					{
					$article=$DB_site2->query_first("select * from article where artid = '$promo_art'");
					echo "<form method=\"post\" action=\"promotions.php\" name=\"formu\">
					<input type=\"hidden\" name=\"action\" value=\"ajouter3\">
					<input type=\"hidden\" name=\"type_promo\" value=\"$type_promo\">
					<input type=\"hidden\" name=\"artid\" value=\"$promo_art\">
					<input type=\"hidden\" name=\"datedebut\" value=\"$debut[2]-$debut[1]-$debut[0]\">
					<input type=\"hidden\" name=\"datefin\" value=\"$fin[2]-$fin[1]-$fin[0]\">
					<input type=\"hidden\" name=\"prixpromo\" value=\"$prixpromo[$promo_art]\">
					Vous allez ajouter une promotion sur l'article <strong>$article[artcode]</strong>, vérifiez les informations :<br><br>
					Promotion du $txt_datedebut au $txt_datefin<br>
					Prix normal : ".number_format($article[prix], 2, ",", " ")." €<br>
					Prix promotionnel : ".number_format($prixpromo[$promo_art], 2, ",", " ")." €<br>
					<br>
					Etes-vous sûr(e) de vouloir ajouter cette promotion ?<br><br>
					<a href=\"Javascript: document.formu.submit();\">OUI</a> &nbsp;&nbsp; <a href=\"promotions.php?action=ajouter\">NON</a>
					</form>" ;
					}
				break;
			case '1':
				$debut = explode("/", $txt_datedebut);
				$fin = explode("/", $txt_datefin);
				if ("$debut[2]-$debut[1]-$debut[0]" > "$fin[2]-$fin[1]-$fin[0]")
					{
					$erreur .= "La date de fin doit être ultérieure à la date de début.<br>" ;
					}
				if (!is_numeric($pct) or $pct == "")
					{
					$erreur .= "Vous devez saisir un pourcentage pour la promotion.<br>";
					}
				elseif ($pct >= 100 or $pct <= 0)
					{
					$erreur .= "Vous devez saisir un pourcentage correct pour la promotion.<br>";
					}
				if (!isset($prixpromohidden))
					{
					$erreur .= "Vous devez choisir une marque contenant des articles.<br>";
					}
				else
					{
					foreach ($prixpromohidden as $key => $value)
						{
						if ($value == 0 or $value >= $prix[$key])
							{
							$erreur .= "Le prix en promotion doit être supérieur à 0 et inférieur au prix normal.<br>";
							}
						else
							{
							$promotions_existantes=$DB_site2->query("select * from promotion where artid = '$key' order by datedebut");
							if ($DB_site2->num_rows($promotions_existantes) > 0)
								{
								while ($promotion_existante=$DB_site2->fetch_array($promotions_existantes))
									{
									if (("$debut[2]-$debut[1]-$debut[0]" < $promotion_existante[datedebut] and "$fin[2]-$fin[1]-$fin[0]" <= $promotion_existante[datedebut]) or ("$debut[2]-$debut[1]-$debut[0]" >= $promotion_existante[datefin] and "$fin[2]-$fin[1]-$fin[0]" > $promotion_existante[datefin]))
										{
										// rien
										}
									else
										{
										$deb = explode("-", $promotion_existante[datedebut]);
										$fin = explode("-", $promotion_existante[datefin]);
										$erreur .= "Il y a un conflit avec la promotion du $deb[2]/$deb[1]/$deb[0] au $fin[2]/$fin[1]/$fin[0].<br>";
										}
									}
								}
							}
						}
					}
				if ($erreur != "")
					{
					echo "<font class=\"cost\">$erreur</font>" ;
					$action = "ajouterbis" ;
					}
				else
					{
					$liste_des_articles = "" ;
					$articles=$DB_site2->query("select * from article where artid in ($liste_artids)");
					while ($article=$DB_site2->fetch_array($articles))
						{
						$art = $article[artid] ;
						$liste_des_articles .= "<strong>$article[artcode]</strong> prix normal : ".number_format($article[prix], 2, ",", " ")." €, prix promotionnel : ".number_format($prixpromohidden[$art], 2, ",", " ")." €,
						<input type=\"hidden\" name=\"prixpromohidden[$art]\" value=\"$prixpromohidden[$art]\"><br>" ;
						}
					echo "<form method=\"post\" action=\"promotions.php\" name=\"formu\">
					<input type=\"hidden\" name=\"action\" value=\"ajouter3\">
					<input type=\"hidden\" name=\"pct\" value=\"$pct\">
					<input type=\"hidden\" name=\"promo_marque\" value=\"$promo_marque\">
					<input type=\"hidden\" name=\"type_promo\" value=\"$type_promo\">
					<input type=\"hidden\" name=\"liste_artids\" value=\"$liste_artids\">
					<input type=\"hidden\" name=\"datedebut\" value=\"$debut[2]-$debut[1]-$debut[0]\">
					<input type=\"hidden\" name=\"datefin\" value=\"$fin[2]-$fin[1]-$fin[0]\">
					Vous allez ajouter une promotion sur les articles :<bR> 
					<div align=\"left\">
					<ul>
					$liste_des_articles
					</ul>
					</div> vérifiez les informations :<br><br>
					Promotion du $txt_datedebut au $txt_datefin<br>
					<br>
					Etes-vous sûr(e) de vouloir ajouter cette promotion ?<br><br>
					<a href=\"Javascript: document.formu.submit();\">OUI</a> &nbsp;&nbsp; <a href=\"promotions.php?action=ajouter\">NON</a>
					</form>" ;
					}
				break;
			case '2':
				$debut = explode("/", $txt_datedebut);
				$fin = explode("/", $txt_datefin);
				if ("$debut[2]-$debut[1]-$debut[0]" > "$fin[2]-$fin[1]-$fin[0]")
					{
					$erreur .= "La date de fin doit être ultérieure à la date de début.<br>" ;
					}
				if (!is_numeric($pct) or $pct == "")
					{
					$erreur .= "Vous devez saisir un pourcentage pour la promotion.<br>";
					}
				elseif ($pct >= 100 or $pct <= 0)
					{
					$erreur .= "Vous devez saisir un pourcentage correct pour la promotion.<br>";
					}
				if (!isset($prixpromohidden))
					{
					$erreur .= "Vous devez choisir une marque contenant des articles.<br>";
					}
				else
					{
					foreach ($prixpromohidden as $key => $value)
						{
						if ($value == 0 or $value >= $prix[$key])
							{
							$erreur .= "Le prix en promotion doit être supérieur à 0 et inférieur au prix normal.<br>";
							}
						else
							{
							$promotions_existantes=$DB_site2->query("select * from promotion where artid = '$key' order by datedebut");
							if ($DB_site2->num_rows($promotions_existantes) > 0)
								{
								while ($promotion_existante=$DB_site2->fetch_array($promotions_existantes))
									{
									if (("$debut[2]-$debut[1]-$debut[0]" < $promotion_existante[datedebut] and "$fin[2]-$fin[1]-$fin[0]" <= $promotion_existante[datedebut]) or ("$debut[2]-$debut[1]-$debut[0]" >= $promotion_existante[datefin] and "$fin[2]-$fin[1]-$fin[0]" > $promotion_existante[datefin]))
										{
										// rien
										}
									else
										{
										$deb = explode("-", $promotion_existante[datedebut]);
										$fin = explode("-", $promotion_existante[datefin]);
										$erreur .= "Il y a un conflit avec la promotion du $deb[2]/$deb[1]/$deb[0] au $fin[2]/$fin[1]/$fin[0].<br>";
										}
									}
								}
							}
						}
					}
				if ($erreur != "")
					{
					echo "<font class=\"cost\">$erreur</font>" ;
					$action = "ajouterbis" ;
					}
				else
					{
					$liste_des_articles = "" ;
					$articles=$DB_site2->query("select * from article where artid in ($liste_artids)");
					while ($article=$DB_site2->fetch_array($articles))
						{
						$art = $article[artid] ;
						$liste_des_articles .= "<strong>$article[artcode]</strong> prix normal : ".number_format($article[prix], 2, ",", " ")." €, prix promotionnel : ".number_format($prixpromohidden[$art], 2, ",", " ")." €,
						<input type=\"hidden\" name=\"prixpromohidden[$art]\" value=\"$prixpromohidden[$art]\"><br>" ;
						}
					echo "<form method=\"post\" action=\"promotions.php\" name=\"formu\">
					<input type=\"hidden\" name=\"action\" value=\"ajouter3\">
					<input type=\"hidden\" name=\"pct\" value=\"$pct\">
					<input type=\"hidden\" name=\"promo_cat\" value=\"$promo_cat\">
					<input type=\"hidden\" name=\"type_promo\" value=\"$type_promo\">
					<input type=\"hidden\" name=\"liste_artids\" value=\"$liste_artids\">
					<input type=\"hidden\" name=\"datedebut\" value=\"$debut[2]-$debut[1]-$debut[0]\">
					<input type=\"hidden\" name=\"datefin\" value=\"$fin[2]-$fin[1]-$fin[0]\">
					Vous allez ajouter une promotion sur les articles :<bR> 
					<div align=\"left\">
					<ul>
					$liste_des_articles
					</ul>
					</div> vérifiez les informations :<br><br>
					Promotion du $txt_datedebut au $txt_datefin<br>
					<br>
					Etes-vous sûr(e) de vouloir ajouter cette promotion ?<br><br>
					<a href=\"Javascript: document.formu.submit();\">OUI</a> &nbsp;&nbsp; <a href=\"promotions.php?action=ajouter\">NON</a>
					</form>" ;
					}
				break;
			}
		}
	} 

if ($action == "ajouterbis") 
	{
	echo "<div align=\"center\"><h3>Nouvelle promotion</h3></div>";
	
	$articlespromo0 = "" ;
	$articlespromo1 = "" ;
	$articlespromo2 = "" ;

	$liste_type = "<option value=\"\"></option>" ;
	$types=$DB_site2->query("select * from promotion_type order by id");
	while ($type=$DB_site2->fetch_array($types))
		{
		if ($type[id] == $type_promo)
			{
			$liste_type .= "<option value=\"$type[id]\" selected>$type[libelle]</option>" ;
			$appel = "<script language=\"javascript\">document.getElementById('promo'+$type[id]).style.display='block'</script>" ;
			switch ($type[id])
				{
				case '0':
					$article_concerne=$DB_site2->query_first("select * from article where artid = '$promo_art'");
					$articlespromo0 = "<br><br>
										<table class=\"table_modif_categ\">
											<tr>
												<td align=\"left\"><strong>Article</strong></td>
												<td align=\"right\"><strong>Prix normal</strong></td>
												<td align=\"right\"><strong>Prix promo</strong></td>
											</tr>
											<tr>
												<td align=\"left\">$article_concerne[$libelle]</td>
												<td align=\"right\"><input type=\"hidden\" name=\"prix[$promo_art]\" value=\"$article_concerne[prix]\">".number_format($article_concerne[prix], 2, ",", " ")." €</td>
												<td align=\"right\"><input type=\"text\" name=\"prixpromo[$promo_art]\" value=\"$prixpromo[$promo_art]\" size=\"4\"> €</td>
											</tr>
										</table>" ;
					break;
				case '1':
					$articlespromo1 = "<br><br>
										Réduction de <input type=\"text\" name=\"pct\" value=\"$pct\" id=\"pct\" size=\"3\" onkeyup=\"Javascript: updatePrix(this.value);\"> %
										<br><br>
										<table class=\"table_modif_categ\">
											<tr>
												<td align=\"left\"><strong>Article</strong></td>
												<td align=\"right\"><strong>Prix normal</strong></td>
												<td align=\"right\"><strong>Prix promo</strong></td>
											</tr>" ;
					$liste_artid = "0" ;
					$articles_concernes=$DB_site2->query("select * from article where artid IN (select artid from article_marque where marqueid = '$promo_marque')");
					while ($article_concerne=$DB_site2->fetch_array($articles_concernes))
						{
						$artid = $article_concerne[artid] ;
						$liste_artid .= ",$artid" ;
						$articlespromo1 .= "<tr>
												<td align=\"left\">$article_concerne[$libelle]</td>
												<td align=\"right\"><input type=\"hidden\" name=\"prix[$artid]\" id=\"prix_$artid\" value=\"$article_concerne[prix]\">".number_format($article_concerne[prix], 2, ",", " ")." €</td>
												<td align=\"right\"><input type=\"hidden\" name=\"prixpromohidden[$artid]\" id=\"prixpromohidden_$artid\" value=\"$prixpromo[$artid]\"><input type=\"text\" name=\"prixpromo[$artid]\" id=\"prixpromo_$artid\" value=\"$prixpromo[$artid]\" size=\"4\"  disabled=\"disabled\"> €</td>
											</tr>" ;
						}
					$articlespromo1 .= "</table>
					<input type=\"hidden\" name=\"liste_artids\" id=\"liste_artids\" value=\"$liste_artid\">" ;
					if ($pct != "")
						{
						$articlespromo1 .= "<script language=\"javascript\">updatePrix($pct);</script>";
						}
					break;
				case '2':
					$articlespromo2 = "<br><br>
										Réduction de <input type=\"text\" name=\"pct\" value=\"$pct\" id=\"pct\" size=\"3\" onkeyup=\"Javascript: updatePrix(this.value);\"> %
										<br><br>
										<table class=\"table_modif_categ\">
											<tr>
												<td align=\"left\"><strong>Article</strong></td>
												<td align=\"right\"><strong>Prix normal</strong></td>
												<td align=\"right\"><strong>Prix promo</strong></td>
											</tr>" ;
					$liste_artid = "0" ;
					$liste_catid = "$promo_cat";
					$liste_catid .= categories_filles($promo_cat); 
					$articles_concernes=$DB_site2->query("select * from article where catid IN ($liste_catid)");
					while ($article_concerne=$DB_site2->fetch_array($articles_concernes))
						{
						$artid = $article_concerne[artid] ;
						$liste_artid .= ",$artid" ;
						$articlespromo2 .= "<tr>
												<td align=\"left\">$article_concerne[$libelle]</td>
												<td align=\"right\"><input type=\"hidden\" name=\"prix[$artid]\" id=\"prix_$artid\" value=\"$article_concerne[prix]\">".number_format($article_concerne[prix], 2, ",", " ")." €</td>
												<td align=\"right\"><input type=\"hidden\" name=\"prixpromohidden[$artid]\" id=\"prixpromohidden_$artid\" value=\"$prixpromo[$artid]\"><input type=\"text\" name=\"prixpromo[$artid]\" id=\"prixpromo_$artid\" value=\"$prixpromo[$artid]\" size=\"4\"  disabled=\"disabled\"> €</td>
											</tr>" ;
						}
					$articlespromo2 .= "</table>
					<input type=\"hidden\" name=\"liste_artids\" id=\"liste_artids\" value=\"$liste_artid\">" ;
					if ($pct != "")
						{
						$articlespromo2 .= "<script language=\"javascript\">updatePrix($pct);</script>";
						}
					break;
				}
			}
		else
			{
			$liste_type .= "<option value=\"$type[id]\">$type[libelle]</option>" ;
			}
		}
	
	// liste des articles
	arborescence(0) ;
	$liste_articles = "$select" ;
	
	// liste des marques
	$liste_marques = "" ;
	$marques=$DB_site2->query("select marqueid, libelle from marque where marqueid IN (select distinct(marqueid) from article_marque)");
	while ($marque=$DB_site2->fetch_array($marques)) 
		{ 
		$options = "" ;
		$arts=$DB_site2->query("select artid, $libelle from article where artid IN (select artid from article_marque where marqueid = '$marque[marqueid]')");
		$count=$DB_site2->num_rows($arts) ;
		$options .= "<option value=\"\" style=\"background-color:#EBEBE4;color:#000000;\" disabled=\"disabled\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$count article(s)</option>" ;
		$options .= "<option value=\"\" style=\"background-color:#EBEBE4;color:#000000;\" disabled=\"disabled\"></option>" ;
		if ($marque[marqueid] == $promo_marque)
			{
			$liste_marques .= "<option value=\"$marque[marqueid]\" selected>$marque[libelle]</option>\n$options" ;
			}
		else
			{
			$liste_marques .= "<option value=\"$marque[marqueid]\">$marque[libelle]</option>\n$options" ;
			}
		}
	
	// liste des rayons
	arborescence2(0) ;
	$liste_rayons = "$select2" ;
	
	echo "<form method=\"post\" action=\"promotions.php\" name=\"formu\">
			<table border=\"0\">
				  <tr>
					<td align=\"right\">Type de promo :</td>
					<td><select name=\"type_promo\" onchange=\"Javascript:change(this.value);\">$liste_type</select></td>
				  </tr>
				<tr>
					<td align=\"right\">Date de début :</td>
					<td><input type = \"text\" name=\"txt_datedebut\" value=\"$txt_datedebut\" size=\"10\" maxlength=\"10\">
					<a href=\"javascript:DCAL.Show('DC1');\" border=\"0\"><img src=\"scripts/calendar/Img/c_b.gif\" border=\"0\" id=\"iDC1\"></a>
					</td>
				</tr>
				<tr>
					<td align=\"right\">Date de fin :</td>
					<td><input type = \"text\" name=\"txt_datefin\" value=\"$txt_datefin\" size=\"10\" maxlength=\"10\">
					<a href=\"javascript:DCAL.Show('DC2');\" border=\"0\"><img src=\"scripts/calendar/Img/c_b.gif\" border=\"0\" id=\"iDC2\"></a>
					</td>
				</tr>
			</table><br><br>
			<div id=\"promo0\" style=\"display:none;\">
				Article concerné par la promotion : <br>
				<select name=\"promo_art\" onchange=\"Javascript: liste_articles();\">$liste_articles</select>
				$articlespromo0
			</div>
			<div id=\"promo1\" style=\"display:none;\">
				Marque concernée par la promotion : <br>
				<select name=\"promo_marque\" onchange=\"Javascript: liste_articles();\">$liste_marques</select>
				$articlespromo1
			</div>
			<div id=\"promo2\" style=\"display:none;\">
				Rayon concerné par la promotion : <br>
				<select name=\"promo_cat\" onchange=\"Javascript: liste_articles();\">$liste_rayons</select>
				$articlespromo2
			</div>
			<br><br>
			<input type = \"hidden\" name = \"action\" value = \"ajouter2\">
			<input type = \"submit\" value = \"Ajouter la promotion\">
			</form>
			$appel";
	} 

if ($action == "ajouter") 
	{
	echo "<div align=\"center\"><h3>Nouvelle promotion</h3></div>";
	
	$liste_type = "<option value=\"\"></option>" ;
	$types=$DB_site2->query("select * from promotion_type order by id");
	while ($type=$DB_site2->fetch_array($types))
		{
		if ($type[id] == $type_promo)
			{
			$liste_type .= "<option value=\"$type[id]\" selected>$type[libelle]</option>" ;
			}
		else
			{
			$liste_type .= "<option value=\"$type[id]\">$type[libelle]</option>" ;
			}
		}
	
	// liste des articles
	arborescence(0) ;
	$liste_articles = "$select" ;
	
	// liste des marques
	$liste_marques = "" ;
	$marques=$DB_site2->query("select marqueid, libelle from marque where marqueid IN (select distinct(marqueid) from article_marque)");
	while ($marque=$DB_site2->fetch_array($marques)) 
		{ 
		$options = "" ;
		$arts=$DB_site2->query("select artid, $libelle from article where artid IN (select artid from article_marque where marqueid = '$marque[marqueid]')");
		$count=$DB_site2->num_rows($arts) ;
		$options .= "<option value=\"\" style=\"background-color:#EBEBE4;color:#000000;\" disabled=\"disabled\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$count article(s)</option>" ;
		$options .= "<option value=\"\" style=\"background-color:#EBEBE4;color:#000000;\" disabled=\"disabled\"></option>" ;
		$liste_marques .= "<option value=\"$marque[marqueid]\">$marque[libelle]</option>\n$options" ;
		}
	
	// liste des rayons
	arborescence2(0) ;
	$liste_rayons = "$select2" ;
	
	echo "<form method=\"post\" action=\"promotions.php\" name=\"formu\">
			<table border=\"0\">
				  <tr>
					<td align=\"right\">Type de promo :</td>
					<td><select name=\"type_promo\" onchange=\"Javascript:change(this.value);\">$liste_type</select></td>
				  </tr>
				<tr>
					<td align=\"right\">Date de début :</td>
					<td><input type = \"text\" name=\"txt_datedebut\" size=\"10\" maxlength=\"10\">
					<a href=\"javascript:DCAL.Show('DC1');\" border=\"0\"><img src=\"scripts/calendar/Img/c_b.gif\" border=\"0\" id=\"iDC1\"></a>
					</td>
				</tr>
				<tr>
					<td align=\"right\">Date de fin :</td>
					<td><input type = \"text\" name=\"txt_datefin\" size=\"10\" maxlength=\"10\">
					<a href=\"javascript:DCAL.Show('DC2');\" border=\"0\"><img src=\"scripts/calendar/Img/c_b.gif\" border=\"0\" id=\"iDC2\"></a>
					</td>
				</tr>
			</table><br><br>
			<div id=\"promo0\" style=\"display:none;\">
				Article concerné par la promotion : <br>
				<select name=\"promo_art\" onchange=\"Javascript: liste_articles();\">$liste_articles</select>
				$articlespromo0
			</div>
			<div id=\"promo1\" style=\"display:none;\">
				Marque concernée par la promotion : <br>
				<select name=\"promo_marque\" onchange=\"Javascript: liste_articles();\">$liste_marques</select>
				$articlespromo1
			</div>
			<div id=\"promo2\" style=\"display:none;\">
				Rayon concerné par la promotion : <br>
				<select name=\"promo_cat\" onchange=\"Javascript: liste_articles();\">$liste_rayons</select>
				$articlespromo2
			</div>
			<br><br>
			<input type = \"hidden\" name = \"action\" value = \"\">
			<input type = \"submit\" value = \"Ajouter la promotion\">
			</form>";
	} 

if (!isset($action) or $action == "") 
	{
	echo "<div align=\"center\"><a href=\"promotions.php?action=ajouter\">Ajouter une promotion</a><br><br></div>";
	echo "<h3>Liste des promotions créées</h3><br>
		<table width=\"100%\">";
	$promotions=$DB_site2->query("select count(*) as nb, datedebut, datefin, promotiongroupeid from promotion where datefin > '".date('Y-m-d')."' group by promotiongroupeid order by datedebut, datefin");
	if ($DB_site2->num_rows($promotions) > 0)
		{
		while ($promotion=$DB_site2->fetch_array($promotions)) 
			{
			list($annee_debut, $mois_debut, $jour_debut) = explode("-", $promotion[datedebut]);
			list($annee_fin, $mois_fin, $jour_fin) = explode("-", $promotion[datefin]);
			echo "<tr><td align=\"left\"><strong>Du $jour_debut/$mois_debut/$annee_debut au $jour_fin/$mois_fin/$annee_fin</strong> : $promotion[nb] article(s)</td></tr>
			<tr><td>
					<table class=\"table_promos\">";
			$i = 0 ;
			$promos=$DB_site2->query("select p.promotionid, p.prix as prixpromo, pg.promotiontypeid, $libelle, a.prix, pg.groupeid, pg.pourcentage 
			from promotion p INNER JOIN article a on (p.artid = a.artid) LEFT OUTER JOIN promotion_groupe pg on (p.promotiongroupeid = pg.promotiongroupeid) 
			where p.promotiongroupeid = '$promotion[promotiongroupeid]' order by promotiontypeid, pg.promotiongroupeid, a.$libelle");
			while ($promo=$DB_site2->fetch_array($promos)) 
				{
				$i++ ;
				$ligne = "" ;
				$lien = "" ;
				switch ($promo[promotiontypeid])
					{
					case '1':
						$rowalt = "td_promomarque" ;
						if ($i == 1)
							{
							$marque=$DB_site2->query_first("select libelle from marque where marqueid = '$promo[groupeid]'");
							$ligne = "<tr><td align=\"left\" colspan=\"3\">Réduction de $promo[pourcentage] % sur la marque $marque[libelle]</td><td align=\"center\" width=\"45\"><a href=\"promotions.php?action=detail&promotionid=$promo[promotionid]&type=1\">Détail</a></td></tr>" ;
							}
						break;
					case '2':
						$rowalt = "td_promocat" ;
						if ($i == 1)
							{
							$categ=$DB_site2->query_first("select $libelle from categorie where catid = '$promo[groupeid]'");
							$ligne = "<tr><td align=\"left\" colspan=\"3\">Réduction de $promo[pourcentage] % sur le rayon $categ[$libelle]</td><td align=\"center\" width=\"45\"><a href=\"promotions.php?action=detail&promotionid=$promo[promotionid]&type=2\">Détail</a></td></tr>" ;
							}
						break;
					default:
						$rowalt = "td_promoart" ;
						$lien = "<a href=\"promotions.php?action=detail&promotionid=$promo[promotionid]\">Détail</a>" ;
						break;
					}
				echo "$ligne
					<tr>
						<td class=\"$rowalt\" align=\"left\">".substr($promo[$libelle], 0, 60)."</td>
						<td class=\"$rowalt\" align=\"right\" width=\"60\"><s>".number_format($promo[prix], 2, ",", " ")." €</s></td>
						<td class=\"$rowalt\" align=\"right\" width=\"60\">".number_format($promo[prixpromo], 2, ",", " ")." €</td>
						<td class=\"$rowalt\" align=\"center\" width=\"45\">$lien</td>
					</tr>";
				}
			echo "</table>
			<br><br>
			</td></tr>";
			}
		}
	else
		{
		echo "<tr><td align=\"center\"><br>Aucune promotion créée.</td></tr>";
		}
	echo "</table>
	<br><br>
	<table class=\"table_remises\">
		<tr><td align=\"center\">Légende</td></tr>
	</table>
	<table class=\"table_remises\">
		<tr>
			<td class=\"td_promoart\" width=\"5%\"></td>
			<td>Promotion normale sur un article</td>
		</tr>
		<tr>
			<td class=\"td_promocat\" width=\"5%\"></td>
			<td>Promotion groupée sur un rayon / sous-rayon</td>
		</tr>
		<tr>
			<td class=\"td_promomarque\" width=\"5%\"></td>
			<td>Promotion groupée sur une marque</td>
		</tr>
	</table>";
	} 

if ($action == "supprimer2") 
	{
	switch ($type)
		{
		case '0':
			$DB_site2->query("delete from promotion where promotionid = '$promotionid'") ;
			break;
		case '1':
			$DB_site2->query("delete from promotion where promotiongroupeid = '$promotiongroupeid'") ;
			$DB_site2->query("delete from promotion_groupe where promotiongroupeid = '$promotiongroupeid'") ;
			break;
		case '2':
			$DB_site2->query("delete from promotion where promotiongroupeid = '$promotiongroupeid'") ;
			$DB_site2->query("delete from promotion_groupe where promotiongroupeid = '$promotiongroupeid'") ;
			break;
		}
	echo "<script language=\"javascript\">location.href=\"promotions.php\";</script>";
	}

if ($action == "supprimer") 
	{
	switch ($type)
		{
		case '0':
			echo "<br><br>
					Etes-vous sûr(e) de vouloir supprimer la promotion sur cet article ?<br><br>
				  <a href=\"promotions.php?action=supprimer2&promotionid=$promotionid&type=$type\">Oui</a>
				   &nbsp;&nbsp;&nbsp; 
				  <a href=\"promotions.php?action=detail&promotionid=$promotionid\">Non</a>";
			break;
		case '1':
			echo "<br><br>
					Etes-vous sûr(e) de vouloir supprimer la promotion sur cette marque ?<br><br>
				  <a href=\"promotions.php?action=supprimer2&promotiongroupeid=$promotiongroupeid&type=$type\">Oui</a>
				   &nbsp;&nbsp;&nbsp; 
				  <a href=\"promotions.php\">Non</a>";
			break;
		case '2':
			echo "<br><br>
					Etes-vous sûr(e) de vouloir supprimer la promotion sur ce rayon ?<br><br>
				  <a href=\"promotions.php?action=supprimer2&promotiongroupeid=$promotiongroupeid&type=$type\">Oui</a>
				   &nbsp;&nbsp;&nbsp; 
				  <a href=\"promotions.php\">Non</a>";
			break;
		}
	} 

if ($action == "detail") 
	{
	switch ($type)
		{
		case '1':
			$promotion=$DB_site2->query_first("select p.promotiongroupeid, p.datedebut, p.datefin, pg.groupeid, pg.pourcentage, pg.promotiontypeid 
			from promotion p INNER JOIN promotion_groupe pg on (p.promotiongroupeid = pg.promotiongroupeid) where p.promotionid = '$promotionid'");
			list($annee_debut, $mois_debut, $jour_debut) = explode( "-", $promotion[datedebut] );
			list($annee_fin, $mois_fin, $jour_fin) = explode( "-", $promotion[datefin] );
		
			$type=$DB_site2->query_first("select * from promotion_type where id = '$promotion[promotiontypeid]'"); 
			$marque=$DB_site2->query_first("select libelle from marque where marqueid = '$promotion[groupeid]'"); 
			echo "<div align=\"center\"><h3>Promotion du $jour_debut/$mois_debut/$annee_debut au $jour_fin/$mois_fin/$annee_fin</h3></div>
				<form method=\"post\" action=\"promotions.php\" name=\"formu\">
					<table border=\"0\">
					  <tr>
						<td align=\"right\">Type de promo :</td>
						<td>$type[libelle]</td>
					  </tr>
					  <tr><td align=\"right\">Date de début :</td>
						  <td>$jour_debut/$mois_debut/$annee_debut</td>
					  </tr>
					  <tr>
						  <td align=\"right\">Date de fin :</td>
						  <td>$jour_fin/$mois_fin/$annee_fin</td>
					  </tr>
					  <tr>
						  <td align=\"right\">Marque :</td>
						  <td>$marque[libelle]</td>
					  </tr>
					  <tr>
						  <td align=\"right\">Pourcentage :</td>
						  <td>$promotion[pourcentage] %</td>
					  </tr>
					</table><br><br>" ;
			$articles=$DB_site2->query("select * from article a inner join article_marque am on (a.artid = am.artid) where marqueid = '$promotion[groupeid]'"); 
			echo "Article(s) concerné(s) : <br><br>
			<table class=\"table_caracts\">" ;
			while ($article=$DB_site2->fetch_array($articles))
				{
				$promo=$DB_site2->query_first("select prix from promotion where artid = '$article[artid]' and promotiongroupeid = '$promotion[promotiongroupeid]'"); 
				echo "<tr>
					<td align=\"left\">$article[artcode]</td>
					<td align=\"right\" width=\"60\"><s>".number_format($article[prix], 2, ",", " ")." €</s></td>
					<td align=\"right\" width=\"60\">".number_format($promo[prix], 2, ",", " ")." €</td>
				</tr>" ;
				}
			echo "</table><br><br>" ;
			echo "<input type=\"hidden\" name=\"action\" value=\"supprimer\">
			<input type=\"hidden\" name=\"type\" value=\"1\">
			<input type=\"hidden\" name=\"promotiongroupeid\" value=\"$promotion[promotiongroupeid]\">		  
			<input type=\"submit\" value=\"Supprimer la promotion\">
			</form><br><br>
			<a href=\"promotions.php\">Retour</a>";
			break;
		case '2':
			$promotion=$DB_site2->query_first("select p.promotiongroupeid, p.datedebut, p.datefin, pg.groupeid, pg.pourcentage, pg.promotiontypeid 
			from promotion p INNER JOIN promotion_groupe pg on (p.promotiongroupeid = pg.promotiongroupeid) where p.promotionid = '$promotionid'");
			list($annee_debut, $mois_debut, $jour_debut) = explode( "-", $promotion[datedebut] );
			list($annee_fin, $mois_fin, $jour_fin) = explode( "-", $promotion[datefin] );
		
			$type=$DB_site2->query_first("select * from promotion_type where id = '$promotion[promotiontypeid]'"); 
			$categ=$DB_site2->query_first("select $libelle from categorie where catid = '$promotion[groupeid]'"); 
			echo "<div align=\"center\"><h3>Promotion du $jour_debut/$mois_debut/$annee_debut au $jour_fin/$mois_fin/$annee_fin</h3></div>
				<form method=\"post\" action=\"promotions.php\" name=\"formu\">
					<table border=\"0\">
					  <tr>
						<td align=\"right\">Type de promo :</td>
						<td>$type[libelle]</td>
					  </tr>
					  <tr><td align=\"right\">Date de début :</td>
						  <td>$jour_debut/$mois_debut/$annee_debut</td>
					  </tr>
					  <tr>
						  <td align=\"right\">Date de fin :</td>
						  <td>$jour_fin/$mois_fin/$annee_fin</td>
					  </tr>
					  <tr>
						  <td align=\"right\">Rayon :</td>
						  <td>$categ[$libelle]</td>
					  </tr>
					  <tr>
						  <td align=\"right\">Pourcentage :</td>
						  <td>$promotion[pourcentage] %</td>
					  </tr>
					</table><br><br>" ;
			$liste_catid = "$promotion[groupeid]";
			$liste_catid .= categories_filles($promotion[groupeid]); 
			$articles=$DB_site2->query("select * from article where catid IN ($liste_catid)"); 
			echo "Article(s) concerné(s) : <br><br>
			<table class=\"table_caracts\">" ;
			while ($article=$DB_site2->fetch_array($articles))
				{
				$promo=$DB_site2->query_first("select prix from promotion where artid = '$article[artid]' and promotiongroupeid = '$promotion[promotiongroupeid]'"); 
				echo "<tr>
					<td align=\"left\">$article[artcode]</td>
					<td align=\"right\" width=\"60\"><s>".number_format($article[prix], 2, ",", " ")." €</s></td>
					<td align=\"right\" width=\"60\">".number_format($promo[prix], 2, ",", " ")." €</td>
				</tr>" ;
				}
			echo "</table><br><br>" ;
			echo "<input type=\"hidden\" name=\"action\" value=\"supprimer\">
			<input type=\"hidden\" name=\"type\" value=\"2\">
			<input type=\"hidden\" name=\"promotiongroupeid\" value=\"$promotion[promotiongroupeid]\">		  
			<input type=\"submit\" value=\"Supprimer la promotion\">
			</form><br><br>
			<a href=\"promotions.php\">Retour</a>";
			break;
		default:
			$promotion=$DB_site2->query_first("select p.promotionid, p.artid, p.prix as prixpromo, p.datedebut, p.datefin, a.$libelle, a.prix from 
			promotion p INNER JOIN article a on (p.artid = a.artid) where p.promotionid = '$promotionid'");
			list($annee_debut, $mois_debut, $jour_debut) = explode( "-", $promotion[datedebut] );
			list($annee_fin, $mois_fin, $jour_fin) = explode( "-", $promotion[datefin] );
		
			$type=$DB_site2->query_first("select * from promotion_type where id = '$promotion[promotiontypeid]'"); 
			echo "<div align=\"center\"><h3>Promotion du $jour_debut/$mois_debut/$annee_debut au $jour_fin/$mois_fin/$annee_fin</h3></div>
				<form method=\"post\" action=\"promotions.php\" name=\"formu\">
					<table border=\"0\">
					  <tr>
						<td align=\"right\">Type de promo :</td>
						<td>$type[libelle]</td>
					  </tr>
					  <tr><td align=\"right\">Date de début :</td>
						  <td>$jour_debut/$mois_debut/$annee_debut</td>
					  </tr>
					  <tr>
					  <td align=\"right\">Date de fin :</td>
						  <td>$jour_fin/$mois_fin/$annee_fin</td>
					  </tr>
					</table><br><br>" ;
			echo "Article concerné : <br><br>
			<table class=\"table_caracts\">
				<tr>
					<td align=\"left\">$promotion[$libelle]</td>
					<td align=\"right\" width=\"60\"><s>".number_format($promotion[prix], 2, ",", " ")." €</s></td>
					<td align=\"right\" width=\"60\">".number_format($promotion[prixpromo], 2, ",", " ")." €</td>
				</tr>
			</table><br><br>" ;
			echo "<input type=\"hidden\" name=\"action\" value=\"supprimer\">
			<input type=\"hidden\" name=\"type\" value=\"0\">
			<input type=\"hidden\" name=\"promotionid\" value=\"$promotionid\">		  
			<input type=\"submit\" value=\"Supprimer la promotion\">
			</form><br><br>
			<a href=\"promotions.php\">Retour</a>";
			break;
		}
	} 

echo "</td>
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

function categories_filles($parentid)
	{
	global $DB_site2, $cats ;
	$filles=$DB_site2->query("select catid from categorie where parentid = '$parentid'");
	while($fille=$DB_site2->fetch_array($filles))
		{
		$cats .= "," . $fille[catid] ;
		categories_filles($fille[catid]) ;
		}
	return ($cats) ;
	}

?>