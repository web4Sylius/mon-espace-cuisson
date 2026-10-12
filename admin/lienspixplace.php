<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";


echo "<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">PIXPLACE</font>
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
   <td align=\"justify\" valign=\"middle\">" ;

echo "<br>
<div align=\"left\">
<hr><br><br>
<strong>ETAPE 1</strong> : Fichier < PRODUCT > <br><br><br>
Cliquez sur ce lien pour <a href=\"comparateur/pixplace_produit.php\">GENERER LE FICHIER PRODUIT</a>.<br>
A faire chaque fois que voudrez mettre à jour les produits sur pixplace.<br>
<br><br><hr style=\"width:50%;text-align:center;\"><br>" ;
$rep = $rootpath."admin/comparateur/" ;
$dir = opendir($rep) ;
$i = 0 ;
$tabdate = array();
$tabli = array();
while ($f = readdir($dir)) {
	if (is_file($rep.$f) && ereg("pixplaceproduit", $f)) {
		$size = filesize($rep.$f)/1024/1024 ;
		$size = number_format($size, 2, ",", " ");
		$tabdate[$i] = filectime($rep.$f) ;
		$tabli[$i] = "<li><a href=\"comparateur/$f\">$f</a> (env. $size Mo)</li>" ;
		$i++ ;
		}
	}
closedir($dir);
asort($tabdate);
$lis = "" ;
foreach ($tabdate as $key => $value)
	$lis .= $tabli[$key] ;
if ($i > 0) {
	echo "$i fichier(s) généré(s) :
	<ul style=\"text-align:left;margin-left:25px;\">$lis</ul>
	Chaque fichier doit être uploadé sur <a href=\"https://bo-pixplace.pixmania.com/\">pixplace</a> : <br>
	Onglet PRODUCT MANAGEMENT -> Menu Import -> Etape 1 Product<br>
	Uploader le fichier souhaité<br><br>
	... patientez quelques heures (de 30 min à 24 h ** 6 jours) ...<br><br>
	Récupérez le fichier généré dans le dernier tableau, puis passez à l'étape suivante." ;
	}
else
	echo "Aucun fichier produit n'a encore été généré." ;

/****************************************************************************************************************************************************************/

$rep = $rootpath."admin/comparateur/" ;
$dir = opendir($rep) ;
$i = 0 ;
$tabdate = array();
$tabli = array();
while ($f = readdir($dir)) {
	if (is_file($rep.$f) && ereg("pixplacemedia", $f) && $f != "pixplacetempmedia.csv") {
		$size = filesize($rep.$f)/1024/1024 ;
		$size = number_format($size, 2, ",", " ");
		$tabdate[$i] = filectime($rep.$f) ;
		$tabli[$i] = "<li><a href=\"comparateur/$f\">$f</a> (env. $size Mo)</li>" ;
		$i++ ;
		}
	}
closedir($dir);
echo "<br><br><br>
<hr><br><br>
<strong>ETAPE 2</strong> : Fichier < MEDIA > <br><br><br>
<form method=\"post\" action=\"comparateur/pixplace_media.php\" enctype=\"multipart/form-data\">" ;
if ($i > 0)
	echo "<input type=\"hidden\" name=\"newfile\" value=\"$i\">" ;
echo "Uploadez le fichier récupéré précédemment sur <a href=\"https://bo-pixplace.pixmania.com/\">pixplace</a> <input type=\"file\" name=\"fileproduit\"> 
<input type=\"submit\" value=\"Ok\">
</form><br>
A faire pour chaque fichier produit récupéré.<br>
<br><br><hr style=\"width:50%;text-align:center;\"><br>" ;
asort($tabdate);
$lis = "" ;
foreach ($tabdate as $key => $value)
	$lis .= $tabli[$key] ;
if ($i > 0) {
	echo "$i fichier(s) généré(s) :
	<ul style=\"text-align:left;margin-left:25px;\">$lis</ul>
	Chaque fichier doit être uploadé sur <a href=\"https://bo-pixplace.pixmania.com/\">pixplace</a> : <br>
	Onglet PRODUCT MANAGEMENT -> Menu Import -> Etape 3 Média<br>
	Uploader le fichier souhaité<br><br>
	... patientez quelques heures (de 30 min à 24 h) ...<br><br>
	Récupérez le fichier généré dans le dernier tableau, puis passez à l'étape suivante." ;
	}
else {
	echo "Aucun fichier produit n'a encore été généré." ;
	}

/****************************************************************************************************************************************************************/

$rep = $rootpath."admin/comparateur/" ;
$dir = opendir($rep) ;
$i = 0 ;
$tabdate = array();
$tabli = array();
while ($f = readdir($dir)) {
	if (is_file($rep.$f) && ereg("pixplacestock", $f) && $f != "pixplacetempstock.csv") {
		$size = filesize($rep.$f)/1024/1024 ;
		$size = number_format($size, 2, ",", " ");
		$tabdate[$i] = filectime($rep.$f) ;
		$tabli[$i] = "<li><a href=\"comparateur/$f\">$f</a> (env. $size Mo)</li>" ;
		$i++ ;
		}
	}
closedir($dir);
echo "<br><br><br>
<hr><br><br>
<strong>ETAPE 3</strong> : Fichier < PRICE STOCK DELIVERY > <br><br><br>
<form method=\"post\" action=\"comparateur/pixplace_stock.php\" enctype=\"multipart/form-data\">" ;
if ($i > 0)
	echo "<input type=\"hidden\" name=\"newfile\" value=\"$i\">" ;
echo "Uploadez le fichier récupéré précédemment sur <a href=\"https://bo-pixplace.pixmania.com/\">pixplace</a> <input type=\"file\" name=\"fileproduit\"> 
<input type=\"submit\" value=\"Ok\">
</form><br>
A faire pour chaque fichier produit récupéré.<br>
<br><br><hr style=\"width:50%;text-align:center;\"><br>" ;
asort($tabdate);
$lis = "" ;
foreach ($tabdate as $key => $value)
	$lis .= $tabli[$key] ;
if ($i > 0) {
	echo "$i fichier(s) généré(s) :
	<ul style=\"text-align:left;margin-left:25px;\">$lis</ul>
	Chaque fichier doit être uploadé sur <a href=\"https://bo-pixplace.pixmania.com/\">pixplace</a> : <br>
	Onglet PRODUCT MANAGEMENT -> Menu Import -> Etape 4 Price stock<br>
	Uploader le fichier souhaité<br><br><br><br>
	Vous pouvez immédiatement passer à l'étape suivante." ;
	}
else {
	echo "Aucun fichier produit n'a encore été généré." ;
	}

/****************************************************************************************************************************************************************/

$rep = $rootpath."admin/comparateur/" ;
$dir = opendir($rep) ;
$i = 0 ;
$tabdate = array();
$tabli = array();
while ($f = readdir($dir)) {
	if (is_file($rep.$f) && ereg("pixplacetechinfo", $f) && $f != "pixplacetemptechinfo.csv") {
		$size = filesize($rep.$f)/1024/1024 ;
		$size = number_format($size, 2, ",", " ");
		$tabdate[$i] = filectime($rep.$f) ;
		$tabli[$i] = "<li><a href=\"comparateur/$f\">$f</a> (env. $size Mo)</li>" ;
		$i++ ;
		}
	}
closedir($dir);
echo "<br><br><br>
<hr><br><br>
<strong>ETAPE 4</strong> : Fichier < TECH INFO > <br><br><br>
<form method=\"post\" action=\"comparateur/pixplace_techinfo.php\" enctype=\"multipart/form-data\">" ;
if ($i > 0)
	echo "<input type=\"hidden\" name=\"newfile\" value=\"$i\">" ;
echo "Uploadez le fichier stock <input type=\"file\" name=\"filetechinfo\"> 
<input type=\"submit\" value=\"Ok\">
</form><br>
A faire pour chaque fichier généré.<br>
<br><br><hr style=\"width:50%;text-align:center;\"><br>" ;
asort($tabdate);
$lis = "" ;
foreach ($tabdate as $key => $value)
	$lis .= $tabli[$key] ;
if ($i > 0) {
	echo "$i fichier(s) généré(s) :
	<ul style=\"text-align:left;margin-left:25px;\">$lis</ul>
	Chaque fichier doit être uploadé sur <a href=\"https://bo-pixplace.pixmania.com/\">pixplace</a> : <br>
	Onglet PRODUCT MANAGEMENT -> Menu Import -> Etape 2 Tech info<br>
	Uploader le fichier souhaité<br><br>" ;
	}
else {
	echo "Aucun fichier produit n'a encore été généré." ;
	}



	
echo "
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