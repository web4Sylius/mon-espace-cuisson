<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
?>

<br>
<center>
<h1>Ajout d'une remise pour une catégorie</h1>
</center>
<br>
<table>
<tr>
<td align="right">
Catégorie : 
</td>
<td align="left">
<select name="categ">
	<option value="0"></option>
<?php
$categories=$DB_site2->query("SELECT catid, libelleFR from categorie order by libelleFR asc") ;
while ($categorie=$DB_site2->fetch_array($categories)) 
	{
	echo "<option value=\"$categorie[catid]\">$categorie[libelleFR]</option>" ;
	}
?>
</select>
</td>
</tr>
<tr>
<td align="right">
Type de remise : 
</td>
<td align="left" valign="middle">
<select name="type_remise">
	<option value="0"></option>
<?php
$types_remises=$DB_site2->query("SELECT * FROM type_remise order by typeremiselibelle asc") ;
while ($type_remise=$DB_site2->fetch_array($types_remises)) 
	{
	echo "<option value=\"$type_remise[typeremiseid]\">$type_remise[typeremiselibelle]</option>" ;
	echo "$type_remise[description]" ;
	}
?> 
</select>
</td>
</tr>
<tr>
<td align="right">
Valeur : 
</td>
<td align="left">
...
</td>
</tr>
<tr>
<td align="right">
Pourcentage : 
</td>
<td align="left">
...
</td>
</tr>
</table>
<br>
<br>

<?php
echo "<a href=\"remisescat.php\">Retour</a>";


include "./footer.php";

?>
