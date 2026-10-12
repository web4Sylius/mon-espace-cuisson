<?php

require ("../admin_global.php");
include("article.php");
include("order.php");
// include("../email.php");

define('TVA_COEFF',1.196);

print "<head>
<title>Market Place</title>
<script language=\"javascript\">var tva_coeff=".TVA_COEFF.";</script>
<script src=\"ajax.js\" language=\"javascript\"></script>
<script language=\"javascript\" src=\"order.js\"></script>
<style>
.plist{ border:solid thin black; font-size:12px; }
.plist td{border-right:solid thin #808080;}
.titlelist { border-bottom:solid thin black; font-weight:bold; text-align:center;}
</style>
<link rel=\"StyleSheet\" href=\"mp.css\" type=\"text/css\">
</head>";

$show_status=0;
if(isset($_GET['state'])) $show_status=$_GET['state'];
$selstates=array(0=>"en cours",2=>"livrées",3=>"annulées",4=>"S.A.V.");
$selopts="";
foreach($selstates as $key=>$value)
    $selopts.="<option value=\"".$key."\"".($show_status==$key?" selected":"").">".$value."</option>";


print "<body>
<table align=\"center\" width=\"800\" class=\"menu\">
<tr><td rowspan=\"2\" colspan=\"2\"></td><td>Bienvenue</td><td><span style=\"font-size:16px; font-weight:bold;\">Market Place</span><br><a href=\"../\">Accueil administration</a></td></tr>
<tr style=\"background-color:white; color:black;\"><td><a href=\"index.php\">Liste des commande</a></td><td height=\"30\" colspan=\"6\">Voir commandes <select name=\"show_state\" onchange=\"document.location='index.php?state='+this.value\">".$selopts."</select> &nbsp; Rechercher une commande <input type=\"text\" id=\"src\"><input type=\"button\" value=\"OK\" onclick=\"srccmd()\"></td></tr>
</table>";

$inc="list";
if(isset($_GET['order']) || isset($_POST['orderid']) || isset($_GET['create']) || isset($_POST['shippingorder']) || isset($_POST['commissionorder'])) $inc="create";
if(isset($_GET['export'])) $inc="export";
include($inc.".inc");

print "</body>\n";

function moneyformat($value){
         return number_format($value,2,",","")." €";
}

?>