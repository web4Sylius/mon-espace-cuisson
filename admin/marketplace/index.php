<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/admin_global.php');
require_once(BACK_PATH . '/marketplace/article.php');
require_once(BACK_PATH . '/marketplace/order.php');
require_once(SITE_PATH . '/class/tva.class.php');
require_once(SITE_PATH . '/class/garantie.class.php');
require_once(SITE_PATH . '/class/DAOgarantie.class.php');
require_once(SITE_PATH . '/class/DAOarticle.class.php');
require_once(SITE_PATH . '/class/DAOtype.class.php');
require_once(SITE_PATH . '/class/DAOlignefacturegarantie.class.php');
require_once(SITE_PATH . '/class/DAOfournisseur.class.php');

$tva = new tva($DB_site2); 
$tva->setPays(57);

$coeff_tva = 1 + $tva->taux_normal / 100;
define('TVA_COEFF', $coeff_tva);

include_once(BACK_PATH . '/header.php');
include_once(BACK_PATH . '/menu.php');

$show_status = 1;
if(isset($_GET['state'])) $show_status = $_GET['state'];

//Liste déroulante masquée dans l'affichage
/*$selstates = array(
    0 => "en cours",
    2 => "expédiées",
    3 => "annulées",
    4 => "S.A.V."
);
$selopts = "";
foreach($selstates as $key => $value)
{
    $selopts .= "<option value=\"" . $key . "\"" . ($show_status == $key ? " selected" : "") . ">" . $value . "</option>";
}*/

$inc = "list"; //Template OBSOLETE : affiche une page blanche
if(isset($_GET['export'])) $inc = "export"; //Template OBSOLETE qui permet d'exporter les produits des marketplace

//Template de création de commande
if(isset($_GET['order']) || isset($_POST['orderid']) || isset($_GET['create']) || isset($_POST['shippingorder']) || isset($_POST['commissionorder'])) $inc = "create";

//Template de listing des commandes marketplace
if(isset($_GET['marketplace']) && !empty($_GET['marketplace']))
{
    switch($_GET['marketplace'])
    {
        case 'laredoute' :
            $inc = "laredoute";
        break;

        case 'fnac' :
            $inc = 'fnac';
        break;

        case 'amazon' :
            $inc = 'amazon';
        break;

        case 'cdiscount' :
            $inc = 'cdiscount';
        break;

        case 'rueducommerce';
            $inc = 'rueducommerce';
        break;
        
        case 'conforama':
            $inc="conforama";
        break;

        case 'showroomprive':
            $inc="showroomprive";
        break;

        case 'retif':
            $inc="retif";
        break;
    } 
} 
?>
<h1>Market Place</h1>

<script language="javascript">var tva_coeff=<?php echo TVA_COEFF; ?></script>
<script src="ajax.js" language="javascript"></script>
<script language="javascript" src="order.js"></script>
<script language="javascript" src="../calendrier.js"></script>
<style>
    .plist{ border:solid thin black; font-size:12px; }
    .plist td{border-right:solid thin #808080;}
    .titlelist { border-bottom:solid thin black; font-weight:bold; text-align:center;}
</style>
<link rel="StyleSheet" href="mp.css" type="text/css">
<link rel="StyleSheet" href="../cal.css" type="text/css">

<body>
    <table align="center" width="800" class="menu" style="border-spacing: 15px;">
        <tr>
            <td><a href="index.php">Liste des commande</a></td>
            <td><a href="../marketplace2/index.php">Anciennes commandes</a></td>
            <td><a href="index.php?create=1">Créer une commande</a></td>
        </tr>
        <tr style="background-color:white; color:black;">
            <td height="30" colspan="1">
                <select name="marketplace" onchange="location.href='index.php?marketplace='+this.value">
                    <option value="">-- MarketPlace --</option>
                    <option value="amazon" <?php echo isset($_GET['marketplace']) && $_GET['marketplace'] == 'amazon' ? 'selected' : ''; ?>>Amazon</option>
                    <option value="cdiscount" <?php echo isset($_GET['marketplace']) && $_GET['marketplace'] == 'cdiscount' ? 'selected' : '';?>>CDiscount</option>
                    <option value="conforama" <?php echo isset($_GET['marketplace']) && $_GET['marketplace'] == 'conforama' ? 'selected' : '';?>>Conforama</option>
                    <option value="fnac" <?php echo isset($_GET['marketplace']) && $_GET['marketplace'] == 'fnac' ? 'selected' : ''; ?>>Fnac</option>
                    <option value="laredoute" <?php echo isset($_GET['marketplace']) && $_GET['marketplace'] == 'laredoute' ? 'selected' : ''; ?>>LaRedoute</option>
                    <option value="rueducommerce" <?php echo isset($_GET['marketplace']) && $_GET['marketplace'] == 'rueducommerce' ? 'selected' : '';?>>Rue du Commerce</option>
                    <option value="showroomprive" <?php echo isset($_GET['marketplace']) && $_GET['marketplace'] == 'showroomprive' ? 'selected' : '';?>>Showroomprive</option>
                    <option value="retif" <?php echo isset($_GET['marketplace']) && $_GET['marketplace'] == 'retif' ? 'selected' : '';?>>Retif</option>
                </select>

            <td height="30" colspan="1">
                <form name="jDate" id="jDate" methode="get">
                    <input type="radio" name="choixDate" value="30j" <?php  if ((isset($_GET['choixDate']) && $_GET['choixDate'] == '30j') || (!isset($_GET['choixDate']))){ echo 'checked';}  ?> checked onclick="validform()"><span>30 Derniers jours </span><br>
                    <input type="radio" name="choixDate" value="60j"  <?php echo isset($_GET['choixDate']) && $_GET['choixDate'] == '60j' ? 'checked' : ''; ?> onclick="validform()"><span>30 - 60 Derniers jours </span><br>
                    <input type="hidden" name="marketplace" value="<?php echo $_GET['marketplace'] ?>">
                </form>
            </td>  
            <td>
                <!--Trier par état <select name="show_state" onchange="document.location='index.php?state='+this.value"><?php //echo $selopts; ?></select> &nbsp; Rechercher : n° commande / n° facture / nom <input type="text" id="src"><input type="button" value="OK" onclick="srccmd()">-->
            </td>
        </tr>
    </table>
    <script type="text/javascript">
        function validform(){
            document.getElementById("jDate").submit();
        }
    </script>
    <?php
    include($inc . ".inc");


    ?>
</body>
<?php

function moneyformat($value){
         return number_format($value,2,",","")." €";
}

function recap($orderid){
    global $DB_site2;
    $order=new MP_Order(0,$DB_site2);
    $order->load($orderid);
    $lines=$order->list_lines();
    $return="<table>";
    foreach($lines as $line)
      $return.="<tr><td>".$line->qte."</td><td>".$line->ref_tin." ".$line->product_name."</td><td align=\"right\" nowrap>".number_format($line->price,2,',','')." &euro;</td></tr>";

    if($order->shipping_price>0) $return.="<tr><td colspan=\"2\">Frais de port</td><td align=\"right\" nowrap>".number_format($order->shipping_price,2,',','')." &euro;</td></tr>";
    $return.="<tr><td colspan=\"2\">Total TTC</td><td align=\"right\" nowrap>".number_format($order->totalttc(),2,',','')." &euro;</td></tr>
    </table>";

    return $return;
}

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

include "../footer.php";
?>
