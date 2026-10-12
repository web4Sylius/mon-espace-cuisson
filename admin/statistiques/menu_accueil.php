<?php

require "../admin_global.php";
include "../header.php";
include "../menu.php";

ini_set("display_errors",0);

?>
<h1>Accueil Statistiques</h1>
<style>
.dropbtn {
    background-color: #4CAF50;
    color: white;
    padding: 16px;
    font-size: 14px;
    width: 330px;
    border: none;
    cursor: pointer;
}

.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: #f9f9f9;
    min-width: 330px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
}

.dropdown-content a {
    color: black;
    padding: 10px 16px;
    text-decoration: none;
    display: block;
}

.dropdown-content a:hover {background-color: #f1f1f1}

.dropdown:hover .dropdown-content {
    display: block;
}

.dropdown:hover .dropbtn {
    background-color: #3e8e41;
}
</style>
<br>
<div class="dropdown" style="float:left;">
  <button class="dropbtn">Configuration</button>
  <div class="dropdown-content">
    <a href="objfacture.php">Objectifs Facturés</a>
    <a href="objenregistres.php">Objectifs Enregistrés</a>
    <a href="dir_config_export.php">Configuration TVA (Export)</a>
    <a href="dir_config_users.php">Administration Utilisateurs</a>
  </div>
</div>

<div class="dropdown" style="float:left;">
  <button class="dropbtn">Direction</button>
  <div class="dropdown-content">
    <a href="dir_ca_general.php">CA Général - Facturé</a>
    <a href="dir_ca_detailsite.php">CA Détail Sites - Facturé</a>
    <a href="dir_ca_marketplace.php">CA Détail Marketplaces - Facturé</a>
    <a href="dir_stat_export.php">Statistiques Export - Facturé</a>
    <a href="dir_fac_fournisseurs.php">Facturation Fournisseurs</a>
    <a href="dir_analyse_fourn.php">Analyse Par Fournisseurs</a>
    <a href="dir_analyse_produits.php">Analyse Par Produits</a>
  </div>
</div>

<div class="dropdown" style="float:left;">
  <button class="dropbtn">Marketing</button>
  <div class="dropdown-content">
    <a href="mark_ca_general.php">CA Général - Enregistré</a>
    <a href="mark_ca_detailsite.php">CA Détail Sites - Enregistré</a>
    <a href="mark_ca_marketplace.php">CA Détail Marketplaces - Enregistré</a>
    <a href="mark_stat_export.php">Statistiques Export - Enregistré</a>
    <a href="mark_analyse_prod.php">Analyse Produits</a>
  </div>
</div>

