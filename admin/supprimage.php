<?php

require "admin_global.php";


echo "<script language=\"javascript\"> 
<!-- 
function fermer()
	{
	window.opener.document.formu.del.value = 1 ;
	window.opener.document.formu.action2.value='img';
	window.opener.document.formu.old.value='img';
	window.opener.document.formu.submit () ;
	window.close() ;
	} 
//-->
</script>" ;
echo "<HTML>
<HEAD>
<TITLE>Supprimer une image</TITLE>
<LINK href=\"vendre-en-ligne.css\" type=\"text/css\" rel=\"stylesheet\">
</HEAD>
<BODY>
<center>

" ;

if (!isset($passe) or $passe == "")
	{
	if ($sup == 0)
		{
		echo "Etes-vous sûr(e) de vouloir supprimer cette image ?<br><br>
			<a href=\"supprimage.php?passe=ok&art=$art\">OUI</a> | <a href=\"Javascript: fermer() ;\">NON</a>" ;
		}
	else
		{
		echo "Etes-vous sûr(e) de vouloir supprimer cette image ?<br><br>
			<a href=\"supprimage.php?passe=ok&sup=$sup&art=$art\">OUI</a> | <a href=\"Javascript: fermer() ;\">NON</a>" ;
		}
	}
elseif (isset($passe) and $passe != "")
	{
	if ($sup == 0)
		{
		$DB_site2->query("update article set image='', legende = '' where artid = '$art'");
		
		// Suppression des images produits/admin
// 		unlink($rootpath."configurations/$host/images/produits/br/$art.jpg");
// 		unlink($rootpath."configurations/$host/images/produits/br/$art.jpg");
                unlink($rootpath."configurations/$host/images/produits/$art.jpg");
                $dir=$rootpath."configurations/$host/images/produits/*/$art.jpg";
                array_map('unlink',glob($dir));
		
		// Suppression des images en cache
		$dir = $rootpath."images/cache/";
		array_map('unlink',glob($dir.$art."_*.jpg"));

		}
	else
		{
		$DB_site2->query("delete from articlephoto where artid = '$art' and articlephotoid = '$sup'");
		$DB_site2->query("delete from article_devisdesc where artid = '$art' and img_ext = '".$art."_".$sup.".jpg'");
		}
	echo "<script language=\"javascript\">fermer () ;</script>" ;
	}
	
?>
</center>
</BODY>
</HTML>
