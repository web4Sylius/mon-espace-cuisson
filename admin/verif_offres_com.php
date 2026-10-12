<?php

ini_set("display_errors","1");
error_reporting(E_ALL);

require "admin_global.php";
include "./header.php";
include "./menu.php";

echo "Lien pour procéder à la vérification des offres commerciales : <a href=\"/scripts/script_verif_offres_commerciales.php\" target=\"_blank\">ICI</a>.";

include "./footer.php";
?>