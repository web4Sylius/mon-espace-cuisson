<?php

require "admin_global.php";
echo "<script language=\"javascript\"> 
<!-- 
function fermer()
	{
	window.opener.document.formu.del.value = 1 ;
	window.opener.document.formu.action2.value='pieces';
	window.opener.document.formu.old.value='pieces';
	window.opener.document.formu.submit () ;
	window.close() ;
	} 
//-->
</script>" ;
echo "<HTML>
<HEAD>
<TITLE>Supprimer une pièce jointe</TITLE>
<LINK href=\"vendre-en-ligne.css\" type=\"text/css\" rel=\"stylesheet\">
</HEAD>
<BODY>
<center>

" ;
if (isset($pieceid) and $pieceid != "")
	{
	if (!isset($del) or $del = "")
		{
		echo "Etes-vous sûr(e) de vouloir supprimer cette pièce jointe ?<br><br>
			<a href=\"suppr_piece.php?pieceid=$pieceid&del=oui\">OUI</a> | <a href=\"Javascript: fermer() ;\">NON</a>" ;
		}
	else
		{
		$DB_site2->query("delete from article_piece where articlepieceid = '$pieceid'");
		echo "<script language=\"javascript\">fermer () ;</script>" ;
		}
	}
else
	{
	echo "<script language=\"javascript\">fermer () ;</script>" ;
	}
	
?>
</center>
</BODY>
</HTML>
