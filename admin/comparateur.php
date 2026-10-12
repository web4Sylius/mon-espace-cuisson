<?php
require "admin_global.php";
include "./header.php";
include "./menu.php";

if(isset($_FILES['fupidfile'])){
    $data=file_get_contents($_FILES['fupidfile']['tmp_name']);
    $spdata=explode("\n",$data);
}

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Comparateurs de prix</font>
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


?>


<h3>A savoir</h3>
<p align="left" style="margin:10px;">
Les comparateurs de prix et guides d’achat sont devenus des partenaires privilégiés des e-commerçants dans de nombreux secteurs d’activité. 
<br>
Ces guides facilitent les démarches d’achat de leurs très nombreux visiteurs en leur proposant un choix d’offres concurrentes pour un même article. 
<br>
Pour travailler avec les guides d'achat qui proposent diverses formules gratuites ou payantes, il est primordial de leur transmettre votre catalogue de produits dans un format adapté à leurs procédures internes de référencement. La communication régulière de vos données est une condition nécessaire à l’actualisation de vos offres sur les sites des guides d’achat.
</p>
<br />
<br />
	<table class="table_comparateurs">
		<tr>
			<td class="td_head_comparateurs" align="center"><div align="center"><b>Comparateur</b></div></td>
			<td class="td_head_comparateurs" align="center"><b>Informations</b></td>
		</tr>
		<!--<tr><td colspan="2"><hr align="center"></td></tr>
		<tr>
			<td class="td_comparateurs1" ><div align="center"><img src="http://www.twenga.fr/Images/logo-home.gif" width="100" /></div></td>
			<td class="td_comparateurs2">
				<b>Categories : TOUTES</b><br>
				Twenga est un comparateur de prix à vocation généraliste. Son principal atout : sa gratuité.
				<br>
				<br>
				1/ <a href="https://merchant.twenga.com/register.php" target="_blank">Inscrivez vous sur Twenga</a>
				<br>
				2/ <a href="./comparateur/twenga.php" target="_blank">Téléchargez votre catalogue au format compatible avec Twenga</a><br>
				<br>		 
				
				 </td>
		</tr>-->
		<!--<tr><td colspan="2"><hr align="center"></td></tr>
		<tr>
			<td class="td_comparateurs1" ><div align="center"><img src="http://r6.kelkoo.com/logos/logo_fr_FR.gif" width="100" /></div></td>
			<td class="td_comparateurs2">
				<b>Categories : TOUTES</b><br>
				Pour prendre un premier contact avec le service Kelkoo, nous vous offrons la possibilité de bénéficier d'une offre <em>test</em> limitée à 1000 clics qualifiés par mois, vous permettant d'essayer le service et de faire apparaître vos produits sur la partie moteur de recherche.
				<br>
				<br>
				1/ <a href="https://extranet.kelkoo.net/productsearch/jsp/protect.jsp" target="_blank">Inscrivez vous sur Kelkoo</a>
				<br>
				2/ <a href="./comparateur/kelkoo.php" target="_blank">Téléchargez votre catalogue au format compatible avec kelkoo</a><br>
				<br>		  </td>
		</tr>-->
		<!--<tr><td colspan="2"><hr align="center"></td></tr>
		<tr>
			<td class="td_modules_boutiques" ><div align="center"><img src="http://www.pangora.com/shoplink/img/logo_pangora.gif" width="100" /></div></td>
			<td class="td_modules_boutiques" >
				<b>Categories : TOUTES</b><br>
				Avec une seule intégration de votre catalogue produits sur PANGORA, vous pouvez présenter vos produits sur les neuf portails partenaires en France. Vous accédez ainsi à plus de 2.6 millions d'utilisateurs uniques (Source: Nielsen Netratings, Septembre 2004). 
				<br>
				<br>
				1/ <a href="http://www.pangora.com/shoplink/shops/registration.php" target="_blank">Inscrivez vous sur Pangora</a>
				<br>
				2/ <a href="./comparateur/pangora.php" target="_blank">Transmettez votre catalogue</a>
				<br>
				<br>			</td>
		</tr>-->
		<!--<tr><td colspan="2"><hr align="center"></td></tr>
		<tr>
			<td class="td_modules_boutiques" ><div align="center"><img src="http://www.clubic.com/style/images/logoclubicpagerecherche2.gif" /></div></td>
			<td class="td_modules_boutiques" >
				<b>Categories : Informatique | Multimedia | Logiciels | DVD | Téléphonie</b><br>
				Appuyé par la forte audience de Clubic.com, sur lequel est présente une déclinaison partielle du site, Achetezfacile.com génère un fort trafic qualifié chez l'ensemble des boutiques partenaires.
				<br>
				<br>
				1/ <a href="http://www.achetezfacile.com/contact.php" target="_blank">Demandez à achetezfacile.com de vous contacter en remplissant ce formulaire</a>
				<br>
				2/ <a href="./comparateur/clubic.php" target="_blank">Téléchargez votre catalogue au format compatible avecclubic et transmettez-le leur</a><br>
				<br>			</td>
		</tr>-->
		<!--<tr><td colspan="2"><hr align="center"></td></tr>
		<tr>
			<td class="td_modules_boutiques" ><div align="center"><img src="http://img.leguide.com/v3/logo_leguide_home.gif" width="100" /></div></td>
			<td class="td_modules_boutiques" >
				<b>Categories : TOUTES</b><br>
				L'inscription dans le Webmarchand est totalement gratuite et est ouverte à tous les marchands en ligne qui proposent des produits à la vente.
				<br><br>
				1/ <a href="http://www.webmarchand.com/marchand/creation.htm?action=1" target="_blank">Inscrivez vous en remplissant ce formulaire</a>
				<br>
				2/ <a href="./comparateur/webmarchand.php" target="_blank">Cliquez ici pour exporter votre catalogue au format demandé par webmarchand et transmettez le leur</a><br><br>
				3/ <a href="./comparateur/webmarchandsoldes.php" target="_blank">Cliquez ici pour exporter votre catalogue pour les soldes.</a><br /><br /></td>
		</tr>-->
		<!--<tr><td colspan="2"><hr align="center"></td></tr>
		<tr>
			<td class="td_modules_boutiques" ><div align="center"><img src="http://wvw.monsieurprix.com/images/navigation/FR/logo_tete.gif" width="100" /></div></td>
		  <td class="td_modules_boutiques" >
				<b>Catégories : Informatique | Hifi, Photo, Vidéo | Jeux vidéo | Téléphonie | Electroménager | DVD</b><br>
				Afin de mieux vous connaître et de répondre au mieux à vos besoins, monsieurprix exige que vous remplissiez au préalable un formulaire. Ils répondent généralement rapidement à votre demande.
				<br>
				<br>
			  1/ <a href="http://wvw.monsieurprix.com/divers/contact.html" target="_blank">Inscrivez vous en remplissant ce formulaire</a>
			    <br /></td>
		</tr>-->
		<!--<tr><td colspan="2"><hr align="center"></td></tr>
		<tr>
			<td class="td_modules_boutiques" ><div align="center"><img src="http://images.ciao.com/ifr/images/header/a4.gif" width="100" /></div></td>
		  <td class="td_modules_boutiques" >
				<b>Catégories : TOUTES</b><br>
				Ciao est une communauté en ligne forte de plusieurs millions de membres qui donnent des avis critiques et notent des millions de produits et services pour d’autres consommateurs.
				Preuve du succès de Ciao : plus de 10 millions de consommateurs visitent le site chaque mois.
				<br><br>
			  1/ <a href="http://www.ciao-group.com/media/media_merchant_signup.php" target="_blank">Inscrivez vous en remplissant ce formulaire</a>
			  (formulaire d'inscription en anglais)
		    <br /></td>
		</tr>-->
		<!--<tr><td colspan="2"><hr align="center"></td></tr>
		<tr>
			<td class="td_modules_boutiques" ><div align="center"><img src="http://www.acheter-moins-cher.com/asp/images/acheter-logo.gif" width="100" /></div></td>
		  <td class="td_modules_boutiques" >
				<b>Catégories : TOUTES</b><br>
				Acheter moins cher est un comparateur de prix indépendant et neutre qui relève, classe et compare les prix de vente des produits neufs disponibles à l'achat en ligne chez les marchands fiables et sécurisés du web français.
				<br><br>
			  1/ <a href="http://ticket.acheter-moins-cher.com/" target="_blank">Contactez Laurence Fernandez, service commercial, via ce formulaire</a>
			    <br /></td>
		</tr>-->
		<!--<tr><td colspan="2"><hr align="center"></td></tr>
		<tr>
			<td class="td_modules_boutiques" ><div align="center"><img src="http://www.touslesprix.com/img/tlp.gif" width="100" /></div></td>
		  <td class="td_modules_boutiques" >
				<b>Catégories : TOUTES</b><br>
				Ce comparateur de prix présente un éventail très large de catégories et est ouvert à tout nouvel adhérant. Il a aussi l'avantage d'être très populaire (400 visiteurs connectés à l'instant où j'écris ces lignes).
				<br><br>
			  1/ <a href="http://www.touslesprix.com/contact.asp?sujet=Je+souhaite+afficher+mes+produits+sur+touslesprix" target="_blank">Contactez les via ce formulaire.</a>
			    <br />
			  2/	<a href="./comparateur/touslesprix.php" target="_blank">Exporter mon catalogue au format compatible</a>
		    <br /></td>
		</tr>-->
<!-- 		<tr><td colspan="2"><hr align="center"></td></tr>
		<tr>
			<td class="td_modules_boutiques" ><div align="center"><img src="http://www.aujardin.info/cache/aujardin.gif" width="100" /></div></td>
		  <td class="td_modules_boutiques" >
				<b>Catégories : Jardins et art floral</b><br><br>
			  	<a href="./comparateur/aujardin.php" target="_blank">Exporter mon catalogue au format compatible</a>
			    <br />
		    <br /></td>
		</tr>-->
		<tr><td colspan="2"><hr align="center"></td></tr>
		<tr>
			<td class="td_modules_boutiques" ><div align="center"><img src="http://aka.priceminister.com/res/pic/0/www/www/17064/priceminister_logo.gif" width="100" /></div></td>
		  <td class="td_modules_boutiques" >
				<b>Catégories : TOUTES</b><br><br>
			  	<a href="./comparateur/priceminister.php" target="_blank">Exporter mon catalogue au format compatible</a>
			    <br />
		    <br /></td>
		</tr>		
		<tr><td colspan="2"><hr align="center"></td></tr>
		<tr>
			<td class="td_modules_boutiques" ><div align="center"><img src="images/amazon.jpg" width="100" /></div></td>
		  <td class="td_modules_boutiques" >
			  	<a href="comparateur/amazon.php" target="_blank">Exporter mon catalogue au format compatible</a>
			    <br />
		    <br /></td>
		</tr>
		<tr><td colspan="2"><hr align="center"></td></tr>
		<tr>
			<td class="td_modules_boutiques" ><div align="center"><img src="images/rueducommerce.gif" width="100" /></div></td>
		  <td class="td_modules_boutiques" >
			  	<a href="comparateur/rueducommerce.php" target="_blank">Exporter mon catalogue au format compatible</a>
			    <br />
		    <br /></td>
		</tr>
	<tr>
	  <td colspan="2">
	    <hr align="center">
	  </td>
	</tr>
        <tr>
	      <td class="td_modules_boutiques" ><div align="center"><img src="images/logo-windil.jpg" width="100" /></div></td>
	      <td class="td_modules_boutiques" >
		      <a href="comparateur/windil.php" target="_blank">Exporter mon catalogue au format compatible</a>
		      <br />
	      </td>
	</tr>
        <tr>
	      <td colspan="2">
		<hr align="center">
	      </td>
	</tr>
        <tr>
			<td class="td_modules_boutiques" ><div align="center"><img src="images/google-shopping.jpg" width="100" /></div></td>
		  <td class="td_modules_boutiques" >
			  	<a href="/configurations/<?php echo $host; ?>/exports/google.php">Exporter mon catalogue au format compatible</a>
			    <br />
		    </td>
	</tr>
	
	
	</tr>
        <tr>
	      <td colspan="2">
		<hr align="center">
	      </td>
	</tr>
        <tr>
			<td class="td_modules_boutiques" ><div align="center">Next performance</div></td>
		  <td class="td_modules_boutiques" >
			  	<a href="/configurations/<?php echo $host; ?>/exports/nextperformance.php">Exporter mon catalogue au format compatible</a>
			    <br />
		    </td>
	</tr>

	<tr>
	      <td colspan="2">
		<hr align="center">
	      </td>
	</tr>

	<tr>
			<td class="td_modules_boutiques" ><div align="center"><img src="images/pixmania.gif" width="100" /></div></td>
		  <td class="td_modules_boutiques" nowrap>
			  	<a href="comparateur/pixmania.php" target="_blank">Exporter mon catalogue produits</a>
			    <br />
				<a href="comparateur/pixmania_update.php">Prix et stock</a>
		    <br /></td>
	</tr>

	<tr>
	      <td colspan="2">
		<hr align="center">
	      </td>
	</tr>
        		
	<tr>
			<td class="td_modules_boutiques" ><div align="center"><img src="images/fnac.jpg" width="100" /></div></td>
		  <td class="td_modules_boutiques" >
			  	<a href="comparateur/fnac.php">Exporter mon catalogue au format compatible</a>
			    <br />
		    </td>
	</tr>
		
	<tr>
	      <td colspan="2">
		<hr align="center">
	      </td>
	</tr>

	<tr>
			<td class="td_modules_boutiques" ><div align="center"><img src="images/cdiscount.png" width="100" /></div></td>
		  <td class="td_modules_boutiques" nowrap>
			  	<a href="comparateur/cdiscount.php" target="_blank">Exporter mon catalogue produits</a>
			    <br />
		    <br /></td>
	</tr>
	
	</table>

<?php

echo "				</td>
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
