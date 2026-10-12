<?php

$sort_type='heurefacture desc';
if(isset($_GET['sort'])) $sort_type=$_GET['sort'];
$listorder=new mp_order(0,$DB_site2);
$list=$listorder->list_orders(array($show_status),$sort_type);

if(isset($_GET['src'])) $list=$listorder->search($_GET['src']);

print "<table cellspacing=\"0\" width=\"800\" align=\"center\" style=\"margin-top:10px;\" class=\"list\">
<tr>
  <td class=\"row_title\" width=\"80\"><a href=\"index.php?state=".$show_status."&sort=billing_number desc\" class=\"row_title\">N° Fac</a></td>
  <td height=\"30\" class=\"row_title\"><a href=\"index.php?state=".$show_status."&sort=id desc\" class=\"row_title\">N° Cde</a></td>
  <td class=\"row_title\"><a href=\"index.php?state=".$show_status."\" class=\"row_title\">Date</a></td>
  <td class=\"row_title\">Montant TTC</td>
  <td class=\"row_title\">Etat</td></tr>\n";

$alt=0;

if($list!=false){
    foreach($list as $order){
	      $requete=$DB_site2->query("SELECT mpefl.id,COUNT(mpefl.id) as nb FROM mp_envoi_fournisseur_ligne mpefl, mp_order_line ol WHERE mpefl.ligne_facture_id=ol.id AND mpefl.type='fourn' AND ol.order='".$order->id."'");
	      $fourn=$DB_site2->fetch_array($requete);
	      $requete=$DB_site2->query("SELECT mpefl.id,COUNT(mpefl.id) as nb FROM mp_envoi_fournisseur_ligne mpefl, mp_order_line ol WHERE mpefl.ligne_facture_id=ol.id AND mpefl.type='trspt' AND ol.order='".$order->id."'");
	      $trspt=$DB_site2->fetch_array($requete);
	      $requete=$DB_site2->query("SELECT mpefl.id,COUNT(mpefl.id) as nb FROM mp_envoi_fournisseur_ligne mpefl, mp_order_line ol WHERE mpefl.ligne_facture_id=ol.id AND mpefl.type='sls' AND ol.order='".$order->id."'");
	      $sls=$DB_site2->fetch_array($requete);
              $requete=$DB_site2->query("SELECT id,COUNT(id) as nb FROM mp_order_line WHERE `order`='".$order->id."'");
	      $fourntotal=$DB_site2->fetch_array($requete);
	      
	      $suivi_fournisseur=colortext($fourn[nb],$fourntotal[nb],"Fournisseur")."<br>".colortext($trspt[nb],$fourntotal[nb],"Transporteur")."<br>".colortext($sls[nb],$fourntotal[nb],"Installateur");
	      $suivi_fournisseur.="<br><img src=\"../../configurations/www.ma-cave-a-vin.fr/images/templates/icone_info.gif\" title=\"".$order->suivi2."\" width=\"32\">";
	      print "<tr onclick=\"index.php?order=".$order->id."\" class=\"row$alt\">
		<td style=\"padding:4px;\">".$order->billing_number."-".$order->market_place_id."</td>
		<td height=\"20\" style=\"padding:4px;\">".$order->id."<br>".$order->billing_last_name."</td>
		<td>".date("d/m/Y",$order->order_date)."</td>
		<td>".number_format($order->totalttc(),2,',','')." &euro;</td>
		<td>".$order->order_state."</td>
		<td><a href=\"index.php?order=".$order->id."\">Modifier</a><br><a href=\"../facture.php?facturid=".$order->id."&action=editer\">Traiter</a>".($order->order_status==2?"<br><a href=\"../../facture.php?facturid=".$order->id."\" target=\"_blank\">Facture</a>":"")."</td>
		</tr>\n";
	      $alt=abs($alt-1);
     }
}
if(count($list)==0) print "<tr><td colspan=\"5\" align=\"center\">Aucune commande</td></tr>\n";

print "</table>\n";


function colortext($nb,$total,$text){
    if($nb==0) return "<font color=\"red\">$text</font>";

    if($nb<$total){
	return "<font color=\"#FFAA00\">$text</font>";
    }else{
	return "<font color=\"green\">$text</font>";
    }
}

?>