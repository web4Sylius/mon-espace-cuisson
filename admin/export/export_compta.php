<?php

include("../admin_global.php");
include("xlexport.php");
include("../marketplace/order.php");

if(!isset($_GET['du']) || !isset($_GET['au'])) exit;

$sp=explode("/",$_GET['du']);
$du=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
$sp=explode("/",$_GET['au']);
$au=mktime(23,59,59,$sp[1],$sp[0],$sp[2]);

$xl=new xlexport("csv");
$xl->name="compta";

$cols=array("Provenance","Compte analytique","Date Cde","N° Cde","Date facture","N° facture","Mode de règlement","info total remise HT","Code promo","Total des produits HT remisés hors écopart. et garanties","Total Ecopart HT","Total du trspt HT","Total garantie HT","Total Cde HT","Total TVA","Total Cde TTC","Société","Nom prenom","Code postal","Ville","Pays","ARTEVINO","TRANSTHERM","MODULORACK","MODULOCUBE","SOWINE","Vin sur vin","Climadiff","Préservino","Vinosafe","Avintage","La Sommelière","BIVINOX","Frigelux","Vinosphere","Liebherr","Dometic","Waeco","Mobicool","","","","","");

$req=$DB_site2->query("SELECT libelle FROM marque");
while($marques=$DB_site2->fetch_array($req))
    $ppmarquemem[$marques[0]]="";

$req=$DB_site2->query("SELECT catid,libelleFR FROM categorie");
while($cat=$DB_site2->fetch_array($req)){
    $catmem[$cat[0]]="";
    $cols[]=$cat['libelleFR'];
}

$xl->addrow($cols);

$requete=$DB_site2->query("SELECT *,UNIX_TIMESTAMP(f.datefacture) AS datecde,UNIX_TIMESTAMP(f.dateexpedition) AS datef FROM facture f WHERE f.valide=5 AND UNIX_TIMESTAMP(f.dateexpedition) BETWEEN $du AND $au ORDER BY f.dateexpedition");
while($facture=$DB_site2->fetch_array($requete)){
      $ppmarque=$ppmarquemem;
      $catnb=$catmem;
      $cadeau=false;
      $cadeaucat=false;
      $cadeaucode="";
      $utilisateur=$DB_site2->fetch_array($DB_site2->query("SELECT *,p.libelle AS pays FROM utilisateur u, pays p WHERE p.paysid=u.paysid AND u.userid=".$facture['userid']));
      if($facture['cadeauid']!=0){
          $cadeau=$DB_site2->fetch_array($DB_site2->query("SELECT * FROM cadeau WHERE cadeauid=".$facture['cadeauid']));
          $reqcadeau=$DB_site2->query("SELECT * FROM categorie_cadeau WHERE cadeauid=".$facture['cadeauid']);
          if($DB_site2->num_rows($reqcadeau)>0)
              $cadeaucat=$DB_site2->fetch_array($reqcadeau);
          else
              $cadeaucat=false;
      }
      $reqlignes=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid=".$facture['facturid']);
      $total=$totalttc=$tva=0;
      $montantcadeau=0;
      $ecopart=0;
      $garantie=0;
      $txtva=0;
      while($ligne=$DB_site2->fetch_array($reqlignes)){
          $article=$DB_site2->fetch_array($DB_site2->query("SELECT *, m.libelle AS marque FROM article a, marque m, article_marque am WHERE am.artid=a.artid AND am.marqueid=m.marqueid AND a.artid=".$ligne['artid']));
          $options=$DB_site2->fetch_array($DB_site2->query("SELECT SUM(differenceprix) FROM lignefacturecaracteristique WHERE lignefactureid=".$ligne['lignefactureid']." AND libelle_caract LIKE '%Garantie%'"));
          $gar=$DB_site2->fetch_array($DB_site2->query("SELECT SUM(differenceprix) FROM lignefacturegarantie WHERE lignefactureid=".$ligne['lignefactureid']));
          $txtva=$ligne['tva']/100;
          $ecopart+=$ligne['qte']*$article['ecopartht'];
          $total+=$ligne['qte']*($ligne['prix']/(1+$txtva));
          $garantie+=$ligne['qte']*(($options[0]+$gar[0])/(1+$txtva));
          $ppmarque[$article['marque']]+=$ligne['qte']*(($ligne['prix']-$options[0]-$article['ecopart'])/(1+$txtva));
          $catnb[$article['catid']]+=$ligne['qte'];

          if($facture['cadeauid']!=0 && $cadeaucat!=false && $ligne['catid']==$cadeaucat['catid']){
               $cadeaucode=$cadeau['code'];
               switch($cadeau['typecadeauid']){
                   case 0:
                   $cadeauligne=$ligne['qte']*($cadeau['valeurcadeau']/(1+$txtva));
                   $montantcadeau+=$cadeauligne;
                   $ppmarque[$article['marque']]-=$cadeauligne;
                   break;

                   case 1:
                   $cadeauligne=$ligne['qte']*($ligne['prix']/(1+$txtva))*($cadeau['valeurcadeau']/100);
                   $montantcadeau+=$cadeauligne;
                   $ppmarque[$article['marque']]-=$cadeauligne;
                   break;
               }
          }
           
      }

      if($facture['cadeauid']!=0 && $cadeaucat==false){
          $cadeaucode=$cadeau['code'];
          $montantcadeau=$facture['montantcadeau']/(1+$txtva);

      }
      $total-=$montantcadeau;

      $portht=$facture[prixport]/(1+$facture[tvaport]/100);
      $tva=$total*$txtva+$facture['prixport']*($facture['tvaport']/100)/(1+$facture['tvaport']/100);
      $totalcmd=$total+$portht;
      $totalttc+=$totalcmd*(1+$txtva);
      $line=array("Site",compte_analytique($facture[paysid]),date("d/m/Y",$facture['datecde']),$facture[facturid],date("d/m/Y",$facture['datef']),$facture[numerofacture],paymethod($facture['moyenid']),-$montantcadeau,$cadeaucode,($total-$ecopart-$garantie),$ecopart,$portht,$garantie,$totalcmd,$tva,$totalttc,$utilisateur[raisonsociale],$utilisateur[nom]." ".$utilisateur[prenom],$utilisateur[codepostal],$utilisateur[ville],$utilisateur[pays],$ppmarque['ARTEVINO'],$ppmarque['TRANSTHERM'],$ppmarque['MODULORACK'],$ppmarque['MODULOCUBE'],$ppmarque['SOWINE'],$ppmarque['VIN sur VIN'],$ppmarque['CLIMADIFF'],$ppmarque['PRESERVINO'],$ppmarque['VINOSAFE'],$ppmarque['AVINTAGE'],$ppmarque['La SOMMELIERE'],$ppmarque['BIVINOX'],$ppmarque['FRIGELUX'],$ppmarque['VINOSPHERE'],$ppmarque['LIEBHERR'],$ppmarque['DOMETIC'],$ppmarque['WAECO'],$ppmarque['MOBICOOL'],"","","","","");
      $line=array_merge($line,$catnb);
      $xl->addrow($line);
}

$txtva=1.196;

/*$mporder=new MP_Order(0,$DB_site2);
$orders=$mporder->list_orders(array(2),"billing_date ASC","UNIX_TIMESTAMP(billing_date) BETWEEN $du AND $au");
if($orders){
  foreach($orders as $order){
         $lines=$order->list_lines();
         $ecopart=0;
         $ppmarque=$ppmarquemem;
         $catnb=$catmem;
         $compteanalytique="";
         switch($order->market_place_id){
               case "01": //Amazon
               $compteanalytique=1201;
               break;
               case "04": //Price Minister
               $compteanalytique=1203;
               break;
               case "05": //Pixmania
               $compteanalytique=1204;
               break;
               case "06": //Windill
               $compteanalytique=1205;
               break;
               case "07": //Rue du Commerce
               case "11": // top achat
               $compteanalytique=1202;
               break;
               case "08": //Edengo
               break;
               case "09": //Ebuyclub
               break;
               case "10": //Ebay
               break;
         }
         $total=0;
         foreach($lines as $line){
                $article=$DB_site2->fetch_array($DB_site2->query("SELECT *, m.libelle AS marque FROM article a, marque m, article_marque am WHERE am.artid=a.artid AND am.marqueid=m.marqueid AND a.artcode='".$line->ref_tin."'"));
                $ecopart+=$article['ecopartht'];
                $ppmarque[$article['marque']]+=$line->price;
                $catnb[$article['catid']]++;
                $total+=($line->price-$article['ecopartht']);
         }
         $ht=($order->items_price+$order->shipping_price);
         $tva=$ht*($txtva-1);
         $ttc=$ht*$txtva;
         $line=array($order->market_place,$compteanalytique,date("d/m/Y",$order->order_date),$order->id,date("d/m/Y",$order->billing_date),$order->billing_number,$order->market_place,0,"",$total,$ecopart,$order->shipping_price,0,$ht,$tva,$ttc,$order->billing_company,$order->billing_last_name." ".$order->billing_first_name,$order->billing_zip,$order->billing_city,$order->billing_country,$ppmarque['ARTEVINO'],$ppmarque['TRANSTHERM'],$ppmarque['MODULORACK'],$ppmarque['MODULOCUBE'],$ppmarque['SOWINE'],$ppmarque['VIN sur VIN'],$ppmarque['CLIMADIFF'],$ppmarque['PRESERVINO'],$ppmarque['VINOSAFE'],$ppmarque['AVINTAGE'],$ppmarque['La SOMMELIERE'],$ppmarque['BIVINOX'],$ppmarque['FRIGELUX'],$ppmarque['VINOSPHERE'],$ppmarque['LIEBHERR'],$ppmarque['DOMETIC'],$ppmarque['WAECO'],$ppmarque['MOBICOOL'],"","","","","");
         $line=array_merge($line,$catnb);
         $xl->addrow($line);
  }
}*/

$xl->export();


function compte_analytique($paysid){
  global $DB_site2;
  $requete=$DB_site2->query("SELECT * FROM compte_analytique WHERE site='macaveavin' AND paysid=".$paysid);
	$row=$DB_site2->fetch_array($requete);
	return $row['code'];
}

function paymethod($code){
         switch($code){
              case 0:return "Abandon";
              case 1:return "Carte bleue";
              case 2:return "Chèque";
              case 3:return "Contre remboursement";
              case 4:return "Mandat postal";
              case 5:return "Virement";
              case 6:return "Paypal";
              case 7:return "Espèces";
              case 8:return "ReceiveAndPay";
              case 9:return "ReceiveAndPay en plusieurs fois";
              case 10:return "Carte bleue 3 fois sans frais";
              case 12:return "Carte Aurore";
         }
}

?>
