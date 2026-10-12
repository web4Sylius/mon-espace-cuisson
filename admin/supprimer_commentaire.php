<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "./fonctions.php";	

// Vérification module votes et commentaires
if (in_array ("18", $modules))
	{
	if ($comm == 'publ')
		{
		echo "<br><h1>PUBLIER UN COMMENTAIRE</h1><br>" ;
		$modif_commentaire = $DB_site2->query("update articlevote set actif = 1 where articlevoteid = '$id'") ;
		echo "<a href=\"commentaires.php\">Retour</a>" ;
		}
	if ($comm == 'refu')
		{
		echo "<br><h1>SUPPRIMER UN COMMENTAIRE</h1><br>" ;
		if ($valid == 'yes')
			{
			$suppr_commentaire = $DB_site2->query("delete from articlevote where articlevoteid = '$id'") ;
			$refu = "" ;
			$valid = "" ;
			$id = "" ;
			echo "<a href=\"commentaires.php\">Retour</a>" ;
			}
		else
			{
		echo "Etes-vous sûr de vouloir refuser ce commentaire ?<br>
		Ceci le supprimera de la base de données.<br>
		<a href=\"supprimer_commentaire.php?comm=refu&id=$id&valid=yes\">OUI</a>&nbsp;&nbsp;&nbsp;&nbsp;
		<a href=\"commentaires.php\">NON</a>" ;
			}
		}
	if ($comm == "")
		{
			echo "<a href=\"commentaires.php\">Retour</a>" ;
		}
	}
else
	{
	echo "NOK" ;
	}

include "./footer.php";
?>>