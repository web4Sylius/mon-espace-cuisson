<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/admin/admin_global.php');

if(!isset($_GET['param']) || $_GET['param'] == '') exit;

$fournisseur_nom = $_GET['param'];

echo "<option value=\"\">-- Toutes --</option>";

$sql = "SELECT m.libelle, m.marqueid";
$sql .= " FROM marque m";
$sql .= " INNER JOIN article_marque am ON (am.marqueid = m.marqueid)";
$sql .= " INNER JOIN article_fournisseur af ON (af.artid = am.artid)";
$sql .= " WHERE af.fournisseur = '".$fournisseur_nom."'"; echo $sql;
$sql .= " GROUP BY m.marqueid";
$req = $DB_site2->query($sql);
while($row = $DB_site2->fetch_array($req)) echo "<option value=\"".$row['marqueid']."\">".$row['libelle']."</option>";