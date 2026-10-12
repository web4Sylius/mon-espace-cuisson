<?php

require_once "admin_global.php";
require_once(BACK_PATH . '/marketplace/article.php');
include_once("../class/facture.class.php");

$requests=array("encours","new");

include_once(BACK_PATH . "/marketplace/rueducommerce_bis.inc");

$tocreate=$toship=0;
foreach($orders as $type=>$xml){ 
    foreach($xml as $order){
        $refid_commande=$order->infocommande->refid->__tostring();
        $facture=new facture($DB_site2);
        if(!$facture->from_mp_facturid($refid_commande)){
            ++$tocreate;
        }else{
            if($type=="encours")
                ++$toship;
        }
    }
}

$links="";
if($tocreate+$toship>0){
    if($tocreate>0) $links=" <a href=\"marketplace/index.php?marketplace=rueducommerce\" style=\"color:red;\" target=\"_blank\">A traiter (".$tocreate.")</a>";
    if($toship>0){
        if($links!="") $links.=" |";
        $links.=" <a href=\"marketplace/index.php?marketplace=rueducommerce\" style=\"color:#ff9944;\" target=\"_blank\">A expédier (".$toship.")</a>";
    }
}else
    $links="<span style=\"color:#008000\">Traité</span>";

print "<p><strong>Rue Du Commerce</strong>".$links."</p>";

?>