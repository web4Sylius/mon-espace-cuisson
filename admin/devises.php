<?php

require "admin_global.php";

include "./header.php";
include "./menu.php";


if(isset($_POST['devise'])){
    $actu=$DB_site2->query_first("SELECT * FROM devises WHERE code='".$_POST['devise']."'");
    $DB_site2->query("INSERT INTO devises_history SET date=".time().", devise='".$actu['code']."', taux_achat_site=".$actu['taux_achat_site'].", taux_vente_site=".$actu['taux_vente_site'].", taux_achat_compta=".$actu['taux_achat_compta'].", taux_vente_compta=".$actu['taux_vente_compta']);
    $DB_site2->query("UPDATE devises SET taux_achat_site=".floatval($_POST['taux_achat_site']).", taux_vente_site=".floatval($_POST['taux_vente_site']).", taux_achat_compta=".floatval($_POST['taux_achat_compta']).", taux_vente_compta=".floatval($_POST['taux_vente_compta'])." WHERE code='".$_POST['devise']."'");
}


print "<style>
.devises_tbl input[type='text'] { width:50px; }
.devises_tbl .title { text-align:center; font-weight:bold; background-color:#CCC; }
.devises_tbl .submit { text-align:center; }
</style>";

print "<h1>Devises</h1>";


$req=$DB_site2->query("SELECT * FROM devises WHERE code!='EUR'");
while($d=$DB_site2->fetch_array($req)){
    print "<form method=\"post\">
    <input type=\"hidden\" name=\"devise\" value=\"".$d['code']."\">
    <table class=\"devises_tbl\">
     <tr class=\"title\">
      <td>".$d['nom']."</td>
      <td>1 ".$d['code']." = ".round(1/$devises->change(1,$d['code'],"site","achat"),3)." EUR</td>
     </tr>
     <tr>
      <td>Taux achat site :</td>
      <td>1 ".$d['code']." = <input type=\"text\" name=\"taux_achat_site\" value=\"".$d['taux_achat_site']."\"> EUR</td>
     </tr>
     <tr>
      <td>Taux achat comptabilité :</td>
      <td>1 ".$d['code']." = <input type=\"text\" name=\"taux_achat_compta\" value=\"".$d['taux_achat_compta']."\"> EUR</td>
     </tr>
    <tr>
      <td colspan=\"2\" class=\"submit\"><input type=\"submit\" value=\"Enregistrer\"></td>
    </tr>
    </table>
    </form>";
}

include "./footer.php";

?>