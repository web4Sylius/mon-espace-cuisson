<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

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
	
function copier()
	{
	document.rib.iban.value=document.rib.codebanque.value+document.rib.codeguichet.value+document.rib.nocompte.value+document.rib.cle.value ;
	}
	
</SCRIPT>" ;

echo "<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Moyens de paiement</font>
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

if (isset ($action) and $action == "modif")
	{
	$moyen_paiement_a_modifier=$DB_site2->query_first("select accepte from moyenpaiement where moyenid = '$moyen'");
	switch ($moyen_paiement_a_modifier[accepte])
		{
		case 0:
			$modif_accepte = '1' ;
			break ;
		case 1:
			$modif_accepte = '0' ;
			break ;
		}
	$modif_moyens_paiement=$DB_site2->query("UPDATE moyenpaiement set accepte = '$modif_accepte' where moyenid = '$moyen'");
	echo "<script language=\"javascript\">location.href=\"moyens_paiement.php\";</script>";
	}
	
if (isset ($action) and $action == "modif_paypal")
	{
	$modif_paypal_sup=$DB_site2->query("UPDATE parametresgeneraux set paypal_sup = '$paypal'");
	echo "<script language=\"javascript\">location.href=\"moyens_paiement.php\";</script>";
	}

if (isset ($action) and $action == "modif_mtt_cb")
	{
	$modif_paypal_sup=$DB_site2->query("UPDATE parametresgeneraux set montant_max_cb = '$max_cb_mtt'");
	echo "<script language=\"javascript\">location.href=\"moyens_paiement.php\";</script>";
	}


if (isset ($action) and $action == "modif_rib")
	{
	if ($clerib == $cle)
		{
		$maj=$DB_site2->query("update parametresgeneraux set RIBetab = '$codebanque', RIBguichet = '$codeguichet', RIBcompte = '$nocompte', RIBrice = '$clerib', IBAN = '$iban', BIC = '$bic'");
		}
	else
		{
		$msg_rib = "Le RIB n'est pas correct." ;
		}
	}

if (isset ($action) and $action == "modif_contre_remb")
	{
	$modif_paypal_sup=$DB_site2->query("UPDATE parametresgeneraux set prix_contre_remboursement = '$prix_sup'");
	echo "<script language=\"javascript\">location.href=\"moyens_paiement.php\";</script>";
	}




echo "<h3>Moyens de paiement acceptés sur ma boutique</h3>
<table class=\"table_moyen_paiement\">
	<tr>
		<td class=\"td_head_comparateurs\" align=\"left\">&nbsp;</td>
		<td class=\"td_head_comparateurs\">Moyen de paiement</td>
		<td class=\"td_head_comparateurs\">Accepté</td>
		<td class=\"td_head_comparateurs\" align=\"left\">&nbsp;</td>
	</tr>" ;
$moyens_paiement=$DB_site2->query("SELECT * from moyenpaiement where moyenid NOT IN (0, 7) order by libelleFR asc");
while ($moyen_paiement=$DB_site2->fetch_array($moyens_paiement)) 
	{
	switch ($moyen_paiement[accepte])
		{
		case 0:
			$accepte = "Non" ;
			break ;
		case 1:
			$accepte = "Oui" ;
			break ;
		}
	$moy = $moyen_paiement[moyenid] ;
	echo "<tr>
			<td class=\"td_location\" align=\"left\">&nbsp;</td>
			<td class=\"td_location\" align=\"left\">$moyen_paiement[libelleFR]</td>
			<td class=\"td_location\" align=\"center\">$accepte</td>
			<td class=\"td_location\" align=\"center\" valign=\"middle\"><a href=\"moyens_paiement.php?action=modif&moyen=$moyen_paiement[moyenid]\">
				<img src=\"./images/I_Changer.gif\" height=10 width=10></a></td>
		</tr>" ;
	}
echo "</table>" ;

$params=$DB_site2->query_first("SELECT * from parametresgeneraux");
$prix_sup = $params[prix_contre_remboursement] ;
$mtt_cb_max = $params[montant_max_cb] ;
if ($mtt_cb_max == '0')
	{
	$mtt_cb_max = "" ;
	}
switch ($params[paypal_sup])
	{
	case 0:
		$oui = "" ;
		$non = "checked" ;
		break ;
	case 1:
		$oui = "checked" ;
		$non = "" ;
		break ;
	}

$rib=$DB_site2->query_first("SELECT RIBetab, RIBguichet, RIBcompte, RIBrice, IBAN, BIC from parametresgeneraux");
if ($rib[RIBetab] != "0")
	{
	$codebanque = $rib[RIBetab] ;
	}
if ($rib[RIBguichet] != "0")
	{
	$codeguichet = $rib[RIBguichet] ;
	}
if ($rib[RIBcompte] != "0")
	{
	$nocompte = $rib[RIBcompte] ;
	}
if ($rib[RIBrice] != "0")
	{
	$cle = $rib[RIBrice] ;
	}
$iban = $rib[IBAN] ;
$bic = $rib[BIC] ;
echo "<br><br><br>
<table width=\"80%\">
	<tr>
		<td align=\"justify\"><strong>Paiement par Carte Bleue</strong><br>Pour le règlement par Carte Bleue, 
		vous devez au préalable avoir fait les démarches nécessaires auprès de votre établissement bancaire et nous avoir 
		avertis car quelques modifications techniques sont nécéssaires.<br><br>
		<form method=\"post\" action=\"moyens_paiement.php\">
		<input type=\"hidden\" name=\"action\" value=\"modif_mtt_cb\">
		<table class=\"table_factures\" align=\"center\">
			<tr>
				<td align=\"center\">
					Fixer un montant maximum pour le paiement par Carte Bleue : 
					<input type=\"text\" name=\"max_cb_mtt\" size=\"3\" maxlength=\"5\" value=\"$mtt_cb_max\"> €
					<input type=\"submit\" value=\"OK\">
				</td>
			</tr>
		</table></form>
		</td>
	</tr><tr>
		<td align=\"justify\"><strong>Paiement par contre-remboursement</strong><br>Pour le règlement par contre-remboursement, 
		vous devez au préalable définir le montant à ajouter à la facture.<br>
		ATTENTION : Le prix contre-remboursement n'est pas soumis à TVA.<br><br>
		<form method=\"post\" action=\"moyens_paiement.php\">
		<input type=\"hidden\" name=\"action\" value=\"modif_contre_remb\">
		<table class=\"table_factures\" align=\"center\">
			<tr valign=\"middle\">
				<td align=\"center\" valign=\"middle\">
					Prix contre-remboursement :
					<input type=\"text\" name=\"prix_sup\" value=\"$prix_sup\" size=\"2\"> <input type=\"submit\" value=\"OK\">
				</td>
			</tr>
		</table></form></td>
	</tr>
	<tr>
		<td align=\"justify\"><strong>Paiement par virement</strong><br>Pour le règlement par virement, 
		vous devez au préalable nous avoir communiqué vos coordonnées bancaires : <bR><bR>" ;
		
		echo "<form name=rib method=\"POST\">
		<input type=\"hidden\" name=\"action\" value=\"modif_rib\">
		<table class=\"table_factures\" align=\"center\">
			<tr>
				<td align=\"center\">
					<table>
						<tr>
							<td align=\"center\" valign=\"middle\" rowspan=\"4\"><strong>RIB</strong>&nbsp;&nbsp;&nbsp;</td>
						</tr>
						<tr>
							<td align=\"center\">Banque</td>
							<td align=\"center\">Guichet</td>
							<td align=\"center\">Compte</td>
							<td align=\"center\">Clé</td>
							<td align=\"center\"></td>
						</tr>
						<tr>
							<td align=\"center\"><INPUT TYPE=TEXT NAME=codebanque SIZE=3 VALUE=\"$codebanque\" maxlength=\"5\" onChange=\"Javascript: copier() ;\"></td>
							<td align=\"center\"><INPUT TYPE=TEXT NAME=codeguichet SIZE=3 VALUE=\"$codeguichet\" maxlength=\"5\" onChange=\"Javascript: copier() ;\"></td>
							<td align=\"center\"><INPUT TYPE=TEXT NAME=nocompte SIZE=10 VALUE=\"$nocompte\" maxlength=\"11\" onChange=\"Javascript: copier() ;\"></td>
							<td align=\"center\"><INPUT TYPE=TEXT NAME=cle SIZE=1 VALUE=\"$cle\" maxlength=\"2\" onChange=\"Javascript: copier() ;\">
												<INPUT TYPE=hidden NAME=clerib SIZE=1 VALUE=\"\" maxlength=\"2\" readonly></td>
						</tr>
					</table>
					<table>
						<tr>
							<td align=\"center\" valign=\"middle\" rowspan=\"2\"><strong>IBAN<br>BIC</strong></td>
						</tr>
						<tr>
							<td align=\"center\"><INPUT TYPE=TEXT NAME=\"iban\" SIZE=30 VALUE=\"$iban\" maxlength=\"27\"></td>
							<td align=\"center\">&nbsp;&nbsp;<INPUT TYPE=TEXT NAME=\"bic\" SIZE=12 VALUE=\"$bic\" maxlength=\"15\"></td>
						</tr>
					</table>
				</td>
				<td align=\"center\"><INPUT TYPE=\"button\" VALUE=\"OK\" onclick=\"Javascript: clique() ;\"></td>

			</tr>
		</table>
	</form>" ;
		
		echo "</td>
	</tr>
	<tr>
		<td align=\"justify\"><strong>Paiement par PayPal</strong><br>Pour le règlement avec PayPal, 
		vous devez au préalable avoir fait les démarches nécessaires auprès de PayPal et nous avoir 
		avertis car quelques modifications techniques sont nécéssaires.<br><br>
		<form method=\"post\" action=\"moyens_paiement.php\">
		<input type=\"hidden\" name=\"action\" value=\"modif_paypal\">
		<table class=\"table_factures\" align=\"center\">
			<tr valign=\"middle\">
				<td align=\"center\" valign=\"middle\">
					Etes-vous inscrit au Code à 4 chiffres PayPal ?
					<input type=\"radio\" name=\"paypal\" value=\"1\" $oui> Oui 
					<input type=\"radio\" name=\"paypal\" value=\"0\" $non> Non 
					<input type=\"submit\" value=\"OK\">
				</td>
			</tr>
		</table></form></td>
	</tr>
</table>" ;

				echo "</td>
			</tr>	
		</table>	
     </font>
	</td>
   <td background=\"images2/cadre_r4_c10.gif\" width=\"3\" height=\"100%\" border=\"0\" alt=\"\"></td>
  </tr>
</table>
</td>
</tr>
<tr>
<td>
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