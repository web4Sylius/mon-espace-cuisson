<?php

require "../admin_global.php";
include "../header.php";
include "menu_2.php";
include(BACK_PATH.'/statistiques/statistiques_adm.php');


$Catalogue = array(
	'catalogue' => array(
		'catalogue.php',
		'ajout_produit.php',
		'ajouter_categorie.php',
		'deplacer_produit.php',
		'copier_produit.php',
		'rechercher.php',
		'modifier_categorie.php',
		'deplacer_categorie.php',
		'categorie_promo.php',
		'categorie_idkdos.php',
		'categorie_archive.php'),
	'param_articles' => array(
		'articles.php',
		),
	);




if(!isset($user)) $user==NULL;

$cams = "";
$compteur = 0;
$i = 0;
$sql = "SELECT username FROM parametresgeneraux";
  $cams = $DB_site2->query($sql);
  while ($row = $DB_site2->fetch_array($cams))
    {
     $tab_user[] = $row['username'];
     $i++;
    }



  echo "<p style=\"margin-top:40px; margin-bottom:15px;\" align=\"center\">Utilisateurs : <select name=\"type\" onchange=\"document.location='dir_config_users.php?user='+this.value\">";
  echo "<option value=\"-\"</option>";
  while($compteur <= $i)
  {
    echo "<option value=\"".$tab_user[$compteur]."\">".$tab_user[$compteur]."</option>";
    $compteur++;
  }
  echo "</select></p>";


$cams = "";
$sql = "SELECT parametre_id, mail_contact, telephone, admin_access FROM parametresgeneraux WHERE username='".$user."'";
$cams = $DB_site2->query($sql);
$row = $DB_site2->fetch_array($cams);
$id = $row['parametre_id'];
$mail = $row['mail_contact'];
$tel = $row['telephone'];
$access = $row['admin_access'];

echo $user;
echo "<br>";
echo $id;
echo "<br>";
echo $mail;
echo "<br>";
echo $tel;
echo "<br>";
echo $access;
echo "<br>";
echo "<br>";

nice($Catalogue);

?>
