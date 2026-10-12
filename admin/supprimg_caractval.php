<?php
require "admin_global.php";
echo "<script language=\"javascript\"> 
<!-- 
function fermer()
	{
	window.opener.document.formu.del.value = 1 ;
	window.opener.document.formu.action2.value='caract';
	window.opener.document.formu.old.value='caract';
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
	echo "Etes-vous sûr(e) de vouloir supprimer cette image ?<br><br>
		<a href=\"supprimg_caractval.php?passe=ok&artid=$artid&caractvalid=$caractvalid\">OUI</a> | <a href=\"Javascript: fermer() ;\">NON</a>" ;
	}
elseif (isset($passe) and $passe != "")
	{
	$DB_site2->query("UPDATE article_caractval SET image = '' WHERE artid = '$artid' AND caractvalid = '$caractvalid'");
	$fichier = $artid."_".$caractvalid.".jpg" ;
	$folder = $rootpath."configurations/$host/images/produits/article_caractval";
	if (file_exists($folder."/".$fichier))
		unlink($folder."/".$fichier);
	echo "<script language=\"javascript\">fermer () ;</script>" ;
	}
	
?>
</center>
</BODY>
</HTML>