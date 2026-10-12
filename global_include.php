<?php
include_once("host_config.php"); // Gere le host
include("class/template.class.php");
include("class/marque.class.php");
include("class/facture.class.php");
include("class/article.class.php");
//include("class/categorie.class.php");
include("class/fonctions.php");
include_once("class/display.php");
include("class/email.php");
include("class/stock.class.php");
include("class/gallery.class.php");
include("configurations/www.mon-espace-cuisson.com/config.php");
include("class/fpdf.php");
include("class/pdf.class.php");
include("class/ccstat.class.php");
require("class/ral.class.php");
include("class/showroom.class.php");
include("class/article_stat.php");
include("class/frais_port.php");
include("class/devises.class.php");
require("class/garantie.class.php");
require("class/DAOgarantie.class.php");
require("class/DAOarticle.class.php");
require("class/lignefacturegarantie.class.php");
require("class/DAOlignefacturegarantie.class.php");
require("class/DAOpays.class.php");
require("class/DAOfournisseur.class.php");
require("class/comparateur.class.php");
require("class/horaire.class.php");
require("class/configurateur.class.php");
include_once("class/configurateur/complet.php");
require("class/pack.class.php");
require("class/email_client.php");
require("class/marque_client.php");

foreach($includes as $include){
	include_once($include);
}

?>