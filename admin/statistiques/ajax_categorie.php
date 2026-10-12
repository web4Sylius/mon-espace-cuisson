<?php

require "../admin_global.php";
if($_POST['id'])
{
$id=$_POST['id'];
if ($_POST['id'] == "none")
{
  $sql = "SELECT libelle FROM typearticle";

  $cams = $DB_site2->query($sql);
  while ($row = $DB_site2->fetch_array($cams))
    {
     $Type_art = $row['libelle'];
	echo '<option value="'.$id.'">'.$Type_art.'</option>';
    }
}
else
{
	$sql="SELECT distinct(typearticle.typearticleid), typearticle.libelle FROM article_marque INNER JOIN article ON (article_marque.artid=article.artid) INNER JOIN typearticle on (article.typearticleid=typearticle.typearticleid) WHERE article_marque.marqueid='".$id."'";
	$cams = $DB_site2->query($sql);
	echo "<option value=\"all\">Tous</option>";
	echo " <optgroup label=\"-----------\"></optgroup>";
	while ($row = $DB_site2->fetch_array($cams))
	    {
			$id=$row['typearticleid'];
			$nom=$row['libelle'];
			echo '<option value="'.$id.'">'.$nom.'</option>';
	    }
	}
}


if($_POST['id_type'])
{
$id=$_POST['id_type'];

if ($_POST['id_type'] == "all")
{
	echo "<option value=\"all_sstype\">Toutes</option>";
	echo " <optgroup label=\"-----------\"></optgroup>";
}
else
{
	$sql="SELECT DISTINCT (
IFNULL( CONCAT( c4.catid, '-', c4.libelleFR ) , IFNULL( CONCAT( c3.catid, '-', c3.libelleFR ) , IFNULL( CONCAT( c2.catid, '-', c2.libelleFR ) , CONCAT( c1.catid, '-', c1.libelleFR ) ) ) )
) AS libelle_categorie
FROM article a
LEFT JOIN categorie c1 ON ( c1.catid = a.catid )
LEFT JOIN categorie c2 ON ( c2.catid = c1.parentid )
LEFT JOIN categorie c3 ON ( c3.catid = c2.parentid )
LEFT JOIN categorie c4 ON ( c4.catid = c3.parentid )
INNER JOIN typearticle t ON ( t.typearticleid = a.typearticleid )
INNER JOIN article_site asite ON ( asite.artid = a.artid )
WHERE a.typearticleid = '1'
AND asite.active
IN ( 1, 2 ) ORDER BY `libelle_categorie`  ASC";
	$cams = $DB_site2->query($sql);
	echo "<option value=\"all_sstype\">Tous</option>";
	echo " <optgroup label=\"-----------\"></optgroup>";
	while ($row = $DB_site2->fetch_array($cams))
	    {
//			$id=$row['catid'];
			$nom=$row['libelle_categorie'];
			echo '<option value="'.$nom.'">'.$nom.'</option>';
	    }
	}
}




?>