<?php
require_once("admin_global.php");
require_once("../class/DAOarticle.class.php");
require_once("../class/garantie.class.php");
require_once("../class/DAOgarantie.class.php");
require_once("../class/frais_port.php");
require_once("../class/fonctions.php");
require_once("../class/stock.class.php");

include_once("./header.php");
include_once("./menu.php");

ini_set('display_errors','off');

$DB_site = clone $DB_site2;

$site_code="base";

function nice($var, $color = 'black'){
          echo "<div style=\"color:".$color.";\">";
          echo "<p style=\"color:red; font-weight:bold;\">Contenu de la variable :</p>";
          if(is_array($var) || is_object($var)){
                    echo "<pre>";
                    print_r($var);
                    echo "</pre>";   
          } else {
                    var_dump($var);
          }
          echo "</div>";
}

function nicex($var){
          nice($var);
          exit();
}

//RÃ©cupÃ©ration de la liste des market place
$sql = "SELECT *";
$sql .= " FROM market_place";
$sql .= " WHERE market_place_site_id != '00'";
$requete = $DB_site2->query($sql);
$PDM = array();
while($row = $DB_site2->fetch_array($requete)){
     $PDM[$row['market_place_nom']] = array(
          'logo'        => $row['market_place_logo'],
          'com'         => $row['market_place_com'],
          'marge'       => $row['market_place_marge_toleree'],
          'active'      => (isset($_GET['pdm']) && $_GET['pdm'] == $row['market_place_nom2']) || (isset($_GET['pdm']) && empty($_GET['pdm'])) || !isset($_GET['pdm']) ? true : false,
          'nom2'        => $row['market_place_nom2'],
          'nom_complet' => $row['market_place_nom_complet'],
          'id'          => $row['market_place_site_id'],
          'id_reel'     => $row['market_place_id'],
          'infos'       => $row['market_place_infos']
     );
}
?>
<td valign="top">
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td>
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td height="25" nowrap> &nbsp; <img src="images/arrow_2.jpg"> <font class="cost">Exceptions tarifaires des places de marché</font></td>
                                </tr>
                                <tr>
                                    <td background="images2/cadre_r3_c2.gif" width="100%" height="2"></td>
                                </tr>
                            </table>
                        </td>
                        <td><img name="cadre_r2_c5" src="images2/cadre_r2_c5.gif" width="11" height="27" border="0" alt=""></td>
                        <td background="images2/cadre_r2_c6.gif" width="100%" height="27" align="center" valign="middle"></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <select name="pdm" onchange="location.href='?pdm='+this.value">
                                <option value="">- Choisir une place de marché -</option>
                                <?php foreach($PDM as $la_pdm){ ?>
                                <option value="<?php echo $la_pdm['nom2']; ?>" <?php echo isset($_GET['pdm']) && $_GET['pdm'] == $la_pdm['nom2'] ? 'selected' : '' ?>><?php echo $la_pdm['nom_complet']; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </td>