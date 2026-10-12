<?php

require "admin_global.php";
include "./header.php";
echo "<SCRIPT LANGUAGE=\"JavaScript\">
function litnombre(aChaineNombre)
{
	// retire les espaces les points et autres caractères différents des nombres
	var li=0;
	var lChaine=\"\"+aChaineNombre;

	while (li<eval(lChaine.length))
	{
		if (lChaine.charCodeAt(li)<48 || lChaine.charCodeAt(li)>57)
		{
			lChaine=lChaine.substring(0,li)+lChaine.substring(li+1,lChaine.length);
			li--;
		}

		li++;
	}
	return lChaine;
}

</SCRIPT>

<SCRIPT LANGUAGE=\"JavaScript\">

function litnombreCompte(aChaineNombre)
{
	var li=0;
	var lNb;
	var lChaine=\"\"+aChaineNombre;

	while (li<eval(lChaine.length))
	{
		if (lChaine.charCodeAt(li)<48 || lChaine.charCodeAt(li)>57)
		{
			if (lChaine.charCodeAt(li)>=65 || lChaine.charCodeAt(li)<=90)
			{
				lNb=lChaine.charCodeAt(li)-64;
				if (lNb>9)
				{
					lNb=lNb-9;
				}

				if (lNb>9)
				{
					lNb=lNb-8;
				}

				lChar=\"\"+lNb;
				lChaine=lChaine.substring(0,li)+lChar+lChaine.substring(li+1,lChaine.length);
			}
			else
			{
				if (lChaine.charCodeAt(li)>=97 || lChaine.charCodeAt(li)<=122)
				{
					lNb=lChaine.charCodeAt(li)-96;
					if (lNb>9)
					{
						lNb=lNb-9;
					}

					if (lNb>9)
					{
						lNb=lNb-8;
					}
	
					lChar=\"\"+lNb;
					lChaine=lChaine.substring(0,li)+lChar+lChaine.substring(li+1,lChaine.length);
				}
				else
				{	
					// on retire le caractère indésirable
					lChaine=lChaine.substring(0,li)+lChaine.substring(li+1,lChaine.length);
					li--;
				}
			}
		}

		li++;
	}
	return lChaine;
}

function litnombreIBAN(aChaineNombre)
{
	// retire les espaces les points et autres caractères différents des nombres
	// pour les lettres A à Z, remplacement par un nombre :
	// A remplacé par 10 ... Z remplacé par 35

	var li=0;
	var lNb;
	var lChaine=\"\"+aChaineNombre;
	var lChaineRes=\"\";

	while (li<eval(lChaine.length))
	{
		if (lChaine.charCodeAt(li)<48 || lChaine.charCodeAt(li)>57)
		{
			if (lChaine.charCodeAt(li)>=65 || lChaine.charCodeAt(li)<=90)
			{
				lNb=lChaine.charCodeAt(li)-55;

				lChar=\"\"+lNb;
				lChaineRes=lChaineRes+lChar;
			}
			else
			{
				if (lChaine.charCodeAt(li)>=97 || lChaine.charCodeAt(li)<=122)
				{
					lNb=lChaine.charCodeAt(li)-87;
					lChar=\"\"+lNb;
					lChaineRes=lChaineRes+lChar;
				}
				else
				{	
					// on retire le caractère indésirable
				}
			}
		}
		else
		{
			lChaineRes=lChaineRes+lChaine.substring(li,li+1);
		}
		// alert(lChaineRes);

		li++;
	}
	return lChaineRes;
}

function fCalcRib(aCodeBanque, aCodeGuichet, aNoCompte)
{
	var lCodeBanque, lCodeGuichet, lNoCompte;
	var lint ;
	var lCompteNum;
	var lCompteStr;
	var lA,	lB, lC, lD, lE, lF, lG, lH, lI, lLength, lLoop,	lAsc, lNb;

	// retire les espaces les points et autres caractères différents des nombres

	lCodeBanque=litnombre(aCodeBanque);
	lCodeGuichet=litnombre(aCodeGuichet);
	lNoCompte=litnombreCompte(aNoCompte);



	if (lCodeBanque.length!=5)
	{
		//alert(\"Le code banque n'a pas 5 caractères\");
		return 0;
	}

	if (lCodeGuichet.length!=5)
	{
		//alert(\"Le code guichet n'a pas 5 caractères\");
		return 0;
	}

	if (lNoCompte.length>11)
	{
		//alert(\"Le numéro de compte est trop long, il ne doit pas comporter plus de 11 caractères\");
		return 0;
	}

	lA=parseFloat(lCodeBanque);
	lB=parseFloat(lCodeGuichet);
	lC=parseFloat(lNoCompte);

	lD=8*lA;


	lA=lD % 97;

	lE=15*lB;
	lB=97-(lE % 97);
	lF=3*lC;
	lC=97-(lF % 97);
	lG=lA+lB+lC;
	lI=lG % 97;

	if (lI==0)
	{
		return 97;
	}
	else
	{
		return lI;
	}
}

function fCalcIBAN(acodepays, aRIB)
{

	var lcodepays, lRIB, lConcat, lNb, lIBAN, lCodeNum, lCodeStr, li, lRetenue, lCle, lNbInterm, lStrInterm;

	lRIB=litnombreIBAN(aRIB);
	lcodepays=litnombreIBAN(acodepays);

	// alert(acodepays);
	// alert(lcodepays);

	// alert(lRIB);

	if ((acodepays.length!=2) || (lcodepays.length!=4))
	{
		//alert(\"Le code pays n'a pas 2 lettres\");
		return \"\";
	}

	lConcat=lRIB+lcodepays+\"00\";
	// alert(lConcat);
	
	// découpage par bloc de 9 chiffres pour calculer le modulo (pb de précision)
	li=0;
	lRetenue=\"\";
	while (li<eval(lConcat.length))
	{
		lStrInterm=lRetenue+lConcat.substring(li, li+9);
		lNbInterm=parseFloat(lStrInterm);
		lCle=lStrInterm % 97;
		lRetenue=\"\"+lCle;
		
		li=li+9;
	}


	lCodeNum=98-(lCle % 97);

	if (lCodeNum<10)
	{
		lCodeStr=\"0\"+lCodeNum;
	}
	else
	{
		lCodeStr=\"\"+lCodeNum;
	}
			
	
	lIBAN=acodepays+lCodeStr+aRIB;

	return lIBAN;

}

function fCopierRib(aCodeBanque, aCodeGuichet, aNoCompte, aCleRib)
{
	var lCodeBanque, lCodeGuichet, lNoCompte, lCleRib, lCodeStr, lRib, lNbCleRib;

	lCodeBanque=litnombre(aCodeBanque);
	lCodeGuichet=litnombre(aCodeGuichet);
	lNoCompte=aNoCompte;
	lCleRib=litnombre(aCleRib);


	if (lCodeBanque.length!=5)
	{
		//alert(\"Le code banque n'a pas 5 caractères\");
		return 0;
	}

	if (lCodeGuichet.length!=5)
	{
		//alert(\"Le code guichet n'a pas 5 caractères\");
		return 0;
	}

	if (lNoCompte.length!=11)
	{
		//alert(\"Le numéro de compte doit comporter 11 caractères\");
		return 0;
	}
	
//	alert(lCleRib);

        if ((lCleRib.length>2)||(lCleRib.length==0))
	{
		//alert(\"La clé Rib doit être renseignée et ne pas avoir plus de 2 caractères\");
		return 0;
	}

	lNbCleRib=parseFloat(lCleRib) % 100;

        if ((lNbCleRib<1) || (lNbCleRib>97))
	{
		//alert(\"La clé Rib doit être comprise entre 1 et 97\");
		return 0;
	}

	if (lCleRib<10)
	{
		lCodeStr=\"0\"+lNbCleRib;
	}
	else
	{
		lCodeStr=\"\"+lNbCleRib;
	}

	lRib=lCodeBanque+lCodeGuichet+lNoCompte+lCodeStr;

	return lRib;
}

function clique()
	{
	document.rib.clerib.value=fCalcRib(document.rib.codebanque.value, document.rib.codeguichet.value, document.rib.nocompte.value) ;
	document.rib.submit() ;
	}
</SCRIPT>" ;

include "./menu.php";

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Gestion des affiliations</font>
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

if (isset($action) and $action == "modif")
	{
	$pb = "" ;
	if ($nom == "" or $prenom == "" or $mail == "" or $adresse == "" or $codepostal == "" or $ville == "")
		{
		$pb .= "Les champs nom, prénom, e-mail, adresse, code postal et ville sont obligatoires<br>" ;
		}
	if ($civilite == 0)
		{
		$pb .= "Vous devez choisir une civilité<br>" ;
		}
	if (!ereg("@", $mail))
		{
		$pb .= "L'e-mail saisi n'est pas valide<br>" ;
		}
	if ($codebanque != "" and $codeguichet != "" and $nocompte != "" and $cle != "")
		{
		if ($cle != $clerib)
			{
			$pb .= "Le RIB saisi n'est pas valide<br>" ;
			}
		}
	if ($pb == "")
		{
		$modif_aff=$DB_site2->query("update affilie set civilite = '$civilite', nom = '$nom', prenom = '$prenom', codepostal = '$codepostal',
									 adresse = '$adresse', ville = '$ville', telephone = '$telephone', raisonsociale = '$raisonsociale',
									 pourcentage = '$pourcentage', paysid = '$paysid', actif = '$actif', mail = '$mail', RIBetab = '$codebanque',
									 RIBguichet = '$codeguichet', RIBcompte = '$nocompte', RIBcle = '$cle' where affilieid = '$aff'");
		}
	else
		{
		echo $pb ;
		}
	}

$affilie=$DB_site2->query_first("SELECT * FROM affilie where affilieid = '$aff'");
$codebanque = $affilie[RIBetab] ;
$codeguichet = $affilie[RIBguichet] ;
$nocompte = $affilie[RIBcompte] ;
$cle = $affilie[RIBcle] ;
switch ($affilie[civilite])
	{
	case "1":
		$selectmr = "selected" ; 
		break; 
	case "2":
		$selectmme = "selected" ; 
		break; 
	case "3":
		$selectmelle = "selected" ; 
		break; 
	}
	if ($affilie[actif] == '1')
		{
		$chk1 = "checked" ;
		$chk0 = "" ;
		}
	else
		{
		$chk0 = "checked" ;
		$chk1 = "" ;
		}
$zonedeliste_pays = "";
$payss=$DB_site2->query("SELECT * FROM pays ORDER BY libelle ASC");
while($pays=$DB_site->fetch_array($payss))
	{
	if($pays[paysid]==$affilie[paysid]) 
		{
		$selected="selected";
		} 
	else 
		{
		$selected="";
		}
	$zonedeliste_pays .= "<option value=$pays[paysid] $selected>$pays[libelle]</option>";
	}
echo "<h3>Fiche affilié</h3>
<br><form method=\"post\" name=\"rib\">
<input type=\"hidden\" name=\"action\" value=\"modif\">
<table>
	<tr>
		<td align=\"right\">Civilité : </td><td align=\"left\"><select name=\"civilite\"><option value=0>--Choisissez--</option>
							<option value=1 $selectmr>Monsieur</option><option value=2 $selectmme>Madame</option></select></td>
	</tr>
	<tr>
		<td align=\"right\">Nom : </td><td align=\"left\"><input type=\"text\" name=\"nom\" value=\"$affilie[nom]\"></td>
	</tr>
	<tr>
		<td align=\"right\">Prénom : </td><td align=\"left\"><input type=\"text\" name=\"prenom\" value=\"$affilie[prenom]\"></td>
	</tr>
	<tr>
		<td align=\"right\">E-mail : </td><td align=\"left\"><input type=\"text\" name=\"mail\" value=\"$affilie[mail]\"></td>
	</tr>
	<tr>
		<td align=\"right\" valign=\"top\">Adresse : </td><td align=\"left\"><input type=\"text\" name=\"adresse\" value=\"$affilie[adresse]\"><br>
		<input type=\"text\" name=\"codepostal\" value=\"$affilie[codepostal]\" size=\"3\"> <input type=\"text\" name=\"ville\" value=\"$affilie[ville]\">
		<br><select name=\"paysid\">$zonedeliste_pays</select></td>
	</tr>
	<tr>
		<td align=\"right\">Téléphone : </td><td align=\"left\"><input type=\"text\" name=\"telephone\" value=\"$affilie[telephone]\"></td>
	</tr>
	<tr>
		<td align=\"right\">Raison sociale : </td><td align=\"left\"><input type=\"text\" name=\"raisonsociale\" value=\"$affilie[raisonsociale]\"></td>
	</tr>
	<tr>
		<td align=\"right\">Date d'inscription : </td><td align=\"left\"><input readonly type=\"text\" size=\"8\" name=\"dateinscription\" value=\"$affilie[dateinscription]\"></td>
	</tr>
	<tr>
		<td align=\"right\">Pourcentage : </td><td align=\"left\"><input type=\"text\" size=\"1\" name=\"pourcentage\" value=\"$affilie[pourcentage]\"> %</td>
	</tr>
	<tr>
		<td align=\"right\">RIB : </td>
		<td align=\"left\" nowrap>
		<INPUT TYPE=TEXT NAME=codebanque SIZE=3 VALUE=\"$codebanque\" maxlength=\"5\">
		<INPUT TYPE=TEXT NAME=codeguichet SIZE=3 VALUE=\"$codeguichet\" maxlength=\"5\">
		<INPUT TYPE=TEXT NAME=nocompte SIZE=10 VALUE=\"$nocompte\" maxlength=\"11\">
		<INPUT TYPE=TEXT NAME=cle SIZE=1 VALUE=\"$cle\" maxlength=\"2\">
		<INPUT TYPE=hidden NAME=clerib SIZE=1 VALUE=\"\" maxlength=\"2\" readonly></td>
		</td>
	</tr>
	<tr>
		<td align=\"right\">Actif : </td>
		<td align=\"left\"><input type=\"radio\" name=\"actif\" value=\"1\" $chk1>Oui&nbsp;
				<input type=\"radio\" name=\"actif\" value=\"0\" $chk0>Non</td>
	</tr>
</table>
<input type=\"button\" value=\"Modifier\" onclick=\"Javascript: clique() ;\">
</form>
<br><br><a href=\"affiliation.php?action=affilies\">Retour</a>
" ;


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
?>