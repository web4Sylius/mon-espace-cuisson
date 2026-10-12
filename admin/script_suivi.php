<?php
ini_set("display_errors","on");
set_time_limit(240);

$nostat   = 1;
include_once("host_config_admin.php");
include_once($rootpath."class/email.php");

$today     = mktime(0,0,0,date("m"),date("d"),date("Y"));
$tomorrow  = $today+86400;
$tomorrow2 = $today+(2*86400);
$yesterday = $today-(100*86400);

$test = true;

//*************************************\\
// EMAIL SUIVI COMMERCIAL (COMMERCIAUX \\
//*************************************\\

//Désactivé car jugé inutile

/*$html = "
<style>
body { font-size:12px; }
.bloc { border:solid thin black; margin:5px; padding:5px; }
.bloc table { font-size:12px; }
.bloc td { padding:4px; }
.bloc p { font-weight:bold; }
.vip {color:red;font-weight:bold;}
</style>";

$moyens=array();
$requete=$DB_site2->query("SELECT moyenid,libelleFR FROM moyenpaiement");
while($row=$DB_site2->fetch_array($requete)) $moyens[$row['moyenid']]=$row['libelleFR'];

$thead="<tr><td>Société</td><td width=\"170\">Client</td><td>Inscription</td><td width=\"60\">Statut</td><td width=\"40\">Commercial</td><td width=\"40\">Interlocuteur</td><td width=\"50\">Date rappel</td><td width=\"40\">N°commande</td><td width=\"50\">Date commande</td><td width=\"90\">Total commande</td><td width=\"40\"></td></tr>";


$html.="<div class=\"bloc\"><p>Nouveaux contacts du jour ou Contacts en cours sans rappel - Prioritaire</p>
<table>$thead\n";

$totaltbl=0;

$sql="SELECT u.nom,u.prenom,u.raisonsociale,u.userid,u.dateinscription,u.commercial,u.vip FROM `utilisateur` u WHERE
 ((select COUNT(etat) from suivi_client where userid=u.userid)=0 OR 
    (select etat from suivi_client where userid=u.userid order by date desc limit 1)<>0 and (select rappel from suivi_client where userid=u.userid order by date desc limit 1)=0)
 and (((select valide from facture where userid=u.userid AND supprimee='0' AND heurefacture>".(time()-10360000)." order by heurefacture desc limit 1) not in (1,5,7,8) AND (SELECT moyenid FROM facture WHERE userid=u.userid AND supprimee='0' AND heurefacture>".(time()-10360000)." order by heurefacture desc limit 1)=0) or (select count(*) from facture where userid=u.userid and supprimee='0')=0)
 order by u.userid desc";

$ligne=array();
$totaltbl=0;
$rq=$DB_site2->query($sql);
while($suivi=$DB_site2->fetch_array($rq)){
    $totalcmd=0;
    $facture=$DB_site2->query_first("SELECT valide,facturid,moyenid,heurefacture FROM facture WHERE userid=$suivi[userid] AND supprimee='0' ORDER BY heurefacture DESC LIMIT 1");
    if($facture['valide']!=""){
	$lignes=$DB_site2->query_first("SELECT SUM(qte*prix) FROM lignefacture WHERE factureid=".$facture['facturid']);
	$totalcmd=$lignes[0]+$com['prixport']-$com['montantcadeau'];
    }
    $totaltbl+=$totalcmd;
    switch($facture['valide']){
	case "":
	$etat="Aucune commande";
	break;
	
	case "0":
	$etat="en attente";
	break;
	case "2":
	$etat="Refusée";
	break;
	case "3":
	$etat="Refus commerçant";
	break;
    }
    $vip="";
    $tvip=0;
    if($suivi['vip']==1) {$vip="<span class=\"vip\">VIP</span>";$tvip=10000;}
    $ligne[$totalcmd+$tvip][]="<tr><td>$vip $suivi[raisonsociale]</td><td>$suivi[nom] $suivi[prenom]</td><td>$suivi[dateinscription]</td><td>".$moyens[$facture['moyenid']]." ".$etat."</td><td>$suivi[commercial]</td><td></td><td></td><td><a href=\"http://www.mon-espace-cuisson.com/admin/facture.php?action=editer&facturid=".$facture['facturid']."\">".$facture['facturid']."</a></td><td>".($facture['heurefacture']!=""?date("d/m/Y",$facture['heurefacture']):"")."</td><td align=\"right\">".number_format($totalcmd,2,',','')." &euro;</td><td align=\"right\"><a href=\"http://www.mon-espace-cuisson.com/admin/utilisateur.php?action=editer&userid=$suivi[userid]\">Fiche client</a></td></tr>";
}

krsort($ligne);
foreach($ligne as $total=>$l)
  $html.=implode("",$l);

$html.="<tr><td colspan=\"9\"></td><td style=\"text-align:right;font-weight:bold;\">".number_format($totaltbl,2,',','')." &euro;</td></tr>
</table></div>";

$html.="<div class=\"bloc\"><p>Rappels du jour</p>
<table>$thead";
$html.=rappelbetween($today,$tomorrow);
$html.="<tr><td colspan=\"9\"></td><td style=\"text-align:right;font-weight:bold;\">".number_format($totaltbl,2,',','')." &euro;</td></tr>";
$html.="</table></div>";

$html.="<div class=\"bloc\"><p>Rappels en retard</p>
<table>$thead\n";
$totaltbl=0;
$html.=rappelbetween($yesterday,$today);
$html.="<tr><td colspan=\"9\"></td><td style=\"text-align:right;font-weight:bold;\">".number_format($totaltbl,2,',','')." &euro;</td></tr>
</table></div>";


$html.="<div class=\"bloc\"><p>Rappels de demain</p>
<table>$thead\n";
$totaltbl=0;
$html.=rappelbetween($tomorrow,$tomorrow2);
$html.="<tr><td colspan=\"9\"></td><td style=\"text-align:right;font-weight:bold;\">".number_format($totaltbl,2,',','')." &euro;</td></tr>
</table></div>\n";

$html.="</div><div class=\"bloc\"><p>Rappels futurs</p>
<table>$thead\n";
$totaltbl=0;
$html.=rappelbetween($tomorrow2,$tomorrow2+(86400*60));
$html.="<tr><td colspan=\"9\"></td><td style=\"text-align:right;font-weight:bold;\">".number_format($totaltbl,2,',','')." &euro;</td></tr>
</table></div>\n";

$mail=new email();
$mail->destinataire="webmaster@mon-espace-cuisson.com";
$mail->destinataire="commercial@mon-espace-cuisson.com";
$mail->to=array("webmaster@mon-espace-cuisson.com","pr@mon-espace-cuisson.com","commercial1@mon-espace-cuisson.com");
$mail->expname="ma cave à vin";
$mail->expediteur="contact@mon-espace-cuisson.com";
$mail->subject="Suivi commercial";
$mail->body=$html;*/
// if(in_array(date("w"),$sendwd) && !isset($_GET['test'])){
//   $return=$mail->sendmail();
//   print "envoi";
// }

if(isset($_GET['test']) && isset($html) && !empty($html)) print $html;

// var_dump($return);

//Récupération du numéro de jour de la semaine
$weekday = date("N");
//$weekday = 4; //hack pour test
$sendwd  = array(1,2,4);

//*********************************\\
// EMAIL RELANCE PAIEMENT (COMPTA) \\
//*********************************\\

//Email envoyé uniquement le lundi et le jeudi
if($weekday == 1 || $weekday == 4 || isset($_GET['test'])){
     
     //Entêtes tableau "COMMANDES EN ATTENTE DE PAIEMENT"
     $thead = ""
     . "<tr style=\"background-color: #c8c8c8; font-size:15px; font-weight: bold; text-align: center;\">"
          . "<td>Société</td>"
          . "<td width=\"170\">Client</td>"
          . "<td>Inscription</td>"
          . "<td width=\"60\">Paiement</td>"
          . "<td width=\"40\">Commercial</td>"
          //. "<td width=\"40\">Interlocuteur</td>"
          //. "<td width=\"50\">Date rappel</td>"
          . "<td width=\"40\">N°commande</td>"
          . "<td width=\"50\">Date<br>commande</td>"
          . "<td>Délais commande</td>"
          . "<td width=\"90\">Total<br>commande</td>"
          . "<td width=\"40\"></td>"
     . "</tr>";

     //Styles css à incorporer dans l'email
     $html = ""
     . "<style>"
          . "body { font-size:12px; }"
          . ".bloc { border:solid thin black; margin:5px; padding:5px; width: 900px; }"
          . ".bloc table { font-size:12px; }"
          . ".bloc td { padding:4px; }"
          . ".bloc p { font-weight:bold; }"
          . ".vip {color:red;font-weight:bold;}"
     . "</style>";

     //Tableaux des commandes en attente de paiement
     $html .= ""
     . "<div class=\"bloc\">"
          . "<p style=\"font-size: 16px; text-align: center;\">COMMANDES EN ATTENTE DE PAIEMENT</p>"
          . "<table style=\" text-align: center;\">"
          . $thead;
     
     //Requête de sélection des commandes en attente de paiement
     //- Commandes valides (non supprimées)
     //- Moyens de paiement : chèque, virement, mandat postal, sofinco, multipaiements, location financière
     //- Statut "non traitée"
     $sql = "SELECT u.nom, u.prenom, u.raisonsociale, u.userid, DATE_FORMAT(u.dateinscription, '%d/%m/%Y') as dateinscription, u.commercial, u.vip, f.facturid, mp.libelleFR as paiement_libelle";
     $sql .= " FROM utilisateur u";
     $sql .= " INNER JOIN facture f USING (userid)";
     $sql .= " INNER JOIN moyenpaiement mp ON (mp.moyenid = f.moyenid)";
     $sql .= " WHERE supprimee = '0'";
     $sql .= " AND f.moyenid IN (2,4,5,14,17,18,99)";
     $sql .= " AND f.valide IN (0,7)";
     $sql .= " AND u.utilisateur_admin = 0";
     $sql .= " ORDER BY u.userid DESC";
     $requete = $DB_site2->query($sql);

     $ligne    = array();
     $totaltbl = 0;
     while($suivi = $DB_site2->fetch_array($requete)){
          
          $sql2 = "SELECT SUM(qte * prix)";
          $sql2 .= " FROM lignefacture";
          $sql2 .= " WHERE factureid = f2.facturid";
          
          //Informations de la dernière facture
          $sql = "SELECT f2.valide, f2.montantcadeau, f2.prixport, f2.facturid, f2.heurefacture, DATE_FORMAT(FROM_UNIXTIME(f2.datelivraison), '%d/%m/%Y') as datelivraison, (".$sql2.") as totCmd";
          $sql .= " FROM facture f2";
          $sql .= " WHERE f2.facturid = ".intval($suivi['facturid']);
          $sql .= " AND f2.moyenid != 99";
          $sql .= " UNION ";
          $sql .= " SELECT f.valide, 0, 0, f.facturid, f.heurefacture, DATE_FORMAT(r.reglement_date, '%d/%m/%Y') as datelivraison, r.reglement_montant as totCmd";
          $sql .= " FROM facture f";
          $sql .= " INNER JOIN reglement r ON (r.facturid = f.facturid)";
          $sql .= " WHERE f.facturid = ".intval($suivi['facturid']);
          $sql .= " AND r.reglement_statut_id = 0";
          $sql .= " GROUP BY r.reglement_id";
	  $req = $DB_site2->query($sql);
          
          //Boucle car il peut y avoir plusieurs résultat par commande dans le cas d'un multipaiement
          if($DB_site2->num_rows($req) > 0){
               
               $incr = 0;
               while($la_facture = $DB_site2->fetch_array($req)){
                    
                    //Calcul totaux intermédiaires et totaux généraux
                    $totalcmd  = $la_facture['totCmd'] + $la_facture['prixport'] - $la_facture['montantcadeau'];
                    $totaltbl += $totalcmd;

                    //Clients VIP
                    $vip  = "";
                    $tvip = 0;
                    if($suivi['vip'] == 1){ 
                         $vip  = "<span class=\"vip\">VIP</span>";
                         $tvip = 10000; //Utile pour trier le tableau avec les VIP en 1er
                    }
                    
                    //Formatage statut
                    /*switch($la_facture['valide']){
                         case 0:
                              $statut_cmd = 'Non traitée';
                              break;
                         case 7:
                              $statut_cmd = 'Validée en attente de paiement';
                              break;
                    }*/
                    
                    //Construction du tableau d'affichage
                    $ligne[$la_facture['facturid'].$incr][] = ""
                    . "<tr style=\"background-color: %td_alt%;\">"
                         . "<td>".$vip."<br>".$suivi['raisonsociale']."</td>"
                         . "<td>".$suivi['nom']." ".$suivi['prenom']."</td>"
                         . "<td>".$suivi['dateinscription']."</td>"
                         . "<td>".$suivi['paiement_libelle']."</td>"
                         . "<td>".$suivi['commercial']."</td>"
                         //. "<td></td>"
                         //. "<td></td>"
                         . "<td><a href=\"http://www.mon-espace-cuisson.com/admin/facture.php?action=editer&facturid=".$la_facture['facturid']."\">".$la_facture['facturid']."</a></td>"
                         . "<td>".date('d/m/Y', $la_facture['heurefacture'])."</td>"
                         . "<td>".$la_facture['datelivraison']."</td>"
                         . "<td align=\"right\">".number_format($totalcmd,2,',',' ')." &euro;</td>"
                         . "<td><a href=\"http://www.mon-espace-cuisson.com/admin/utilisateur.php?action=editer&userid=".$suivi['userid']."\">Fiche client</a></td>"
                    . "</tr>\n";

                    $incr++;
               }
          }
     }
     
     //Tri du tableau intermédiaire et ajout dans le tableau général
     ksort($ligne);
     
     $facture_rem = '';
     foreach($ligne as $facturid => $l){
          
          $facturid = substr($facturid,0,-1);
          
          if(empty($facture_rem) || $facture_rem != $facturid){
               $facture_rem = $facturid;
               switch($bg_color){
                    case '#b8ffc4':
                         $bg_color = '#dff4ff';
                         break;
                    default:
                    case '#dff4ff':
                         $bg_color = '#b8ffc4';
                         break;
               }
          }
          
          $html .= implode("", str_replace('%td_alt%',$bg_color,$l));
     }

     //Indication du total en fin de tableau
     $html .= ""
               . "<tr>"
                    . "<td colspan=\"10\" style=\"text-align:right;font-weight:bold;\">".number_format($totaltbl,2,',','')." &euro;</td>"
               . "</tr>"
          . "</table>"
     . "</div>\n";
}

//Envoi du mardi
if($weekday == 2 || isset($_GET['test'])){
    
     $totaltbl = 0;
     $provenances = array(
          ""    => "site",
          "sav" => "SAV",
          "mp"  => "Market place"
     );
     $marketplaces = array(
          "01" => "Amazon",
          "04" => "PriceMinister",
          "05" => "Pixmania",
          "07" => "Rue du Commerce",
          "10" => "Ebay",
          "12" => "Fnac",
          "13" => "CDiscount",
          "14" => "LaRedoute",
          "15" => "Galeries Lafayette",
          "16" => "MisterGoodDeal",
          "18" => "Delamaison",
          "19" => "Conforama",
          "21" => "Showroomprive",
          "22" => "Retif"
     );
     
     $sql2 = "SELECT SUM(qte * prix)";
     $sql2 .= " FROM lignefacture";
     $sql2 .= " WHERE factureid = f.facturid";
     
     //Commandes "expédiée en attente de paiement"
     $sql = "SELECT f.facturid, f.numerofacture, f.passefacture, f.prixport, f.montantcadeau, f.provenance, f.provenanceid, f.heurefacture, f.dateexpedition, u.raisonsociale, u.nom, u.prenom, u.vip, DATE_FORMAT(u.dateinscription, '%d/%m/%Y') as dateinscription, (".$sql2.") as totCmd, IF(u.code411 != '', '3', IF(f.provenance = 'mp', '2', '1')) as TriType, IF(f.provenance = 'mp', f.provenanceid, '') as TriProvenance";
     $sql .= " FROM facture f";
     $sql .= " INNER JOIN utilisateur u USING(userid)";
     $sql .= " WHERE f.valide = 8";
     $sql .= " AND f.moyenid != 99";
     //$sql .= " AND u.code411 = ''";
     $sql .= " UNION ";
     $sql .= " SELECT f2.facturid, f2.numerofacture, f2.passefacture, 0, 0, f2.provenance, f2.provenanceid, f2.heurefacture, f2.dateexpedition, u2.raisonsociale, u2.nom, u2.prenom, u2.vip, DATE_FORMAT(u2.dateinscription, '%d/%m/%Y') as dateinscription, r.reglement_montant as totCmd, IF(u2.code411 != '', '3', IF(f2.provenance = 'mp', '2', '1')) as TriType, IF(f2.provenance = 'mp', f2.provenanceid, '') as TriProvenance"; 
     $sql .= " FROM facture f2";
     $sql .= " INNER JOIN utilisateur u2 USING(userid)";
     $sql .= " INNER JOIN reglement r USING(facturid)";
     $sql .= " WHERE f2.valide = 8";
     $sql .= " AND f2.moyenid = 99";
     //$sql .= " AND u2.code411 = ''";
     $sql .= " AND r.reglement_statut_id = 0";
     $sql .= " GROUP BY r.reglement_id";
     $sql .= " ORDER BY TriType, TriProvenance, raisonsociale, nom";
     $comexpnp = $DB_site2->query($sql);
     
     if($DB_site2->num_rows($comexpnp) > 0){
          
          //Entêtes du tableau "COMMANDES EXPEDIEES NON PAYEES"
          $html .= ""
          . "<div class=\"bloc\">"
               . "<p style=\"font-size: 16px; text-align: center;\">COMMANDES EXPEDIEES NON PAYEES</p>"
               . "<table style=\"text-align: center;\">"
                    . "<tr style=\"background-color: #c8c8c8; font-size:15px; font-weight: bold; text-align: center;\">"
                         . "<td>Société</td>"
                         . "<td width=\"170\">Client</td>"
                         . "<td>Inscription</td>"
                         . "<td>Provenance</td>"
                         //. "<td width=\"60\">Statut</td>"
                         //. "<td width=\"40\">Commercial</td>"
                         //. "<td width=\"40\">Interlocuteur</td>"
                         //. "<td width=\"50\">Date rappel</td>"
                         . "<td width=\"40\">N°commande</td>"
                         . "<td width=\"40\">N°facture</td>"
                         . "<td width=\"50\">Date commande</td>"
                         . "<td width=\"50\">Date facture</td>"
                         . "<td width=\"90\">Total commande</td>"
                         . "<td width=\"40\"></td>"
                    . "</tr>";
          
          $ligne = array();
          $type_courant = '';
          while($com = $DB_site2->fetch_array($comexpnp)){
               
               $incr = 0;
               
               //Calcul totaux
               $totalcmd  = $com['totCmd'] + $com['prixport'] - $com['montantcadeau'];
               $totaltbl += $totalcmd;
               
               //Gestion VIP
               $vip       = "";
               $tvip      = 0;               
               if($com['vip'] == 1){
                    $vip  = "<span class=\"vip\">VIP</span>";
                    $tvip = 10000;
               }
               
               //Mise en forme provenance
               $provtxt = $provenances[$com['provenance']];
               $key     = $com['provenance'];
               if($com['provenance']=="mp"){
                    $provtxt  = $marketplaces[$com['provenanceid']];
                    $key     .= $com['provenanceid'];
               }
               
               if(empty($type_courant) || $type_courant != $com['TriType']){
                    $type_courant = $com['TriType'];
                    switch($type_courant){
                         case '1':
                              $type_resultat = 'Clients';
                              break;
                         case '2':
                              $type_resultat = 'Marketplaces';
                              break;
                         case '3':
                              $type_resultat = 'Fournisseurs';
                              break;
                    }
                    
                    $ligne[$com['facturid'].$incr][] = ""
                    . "<tr style=\"text-align: center; font-size:15px; font-weight: bold; background-color:#ffdfdf;\">"
                         . "<td colspan=\"11\">".$type_resultat."</td>"
                    . "</tr>";
                    
                    $incr++;
               }
               
               //Mise en forme tableau
               $ligne[$com['facturid'].$incr][] = ""
               . "<tr style=\"background-color: %td_alt%;\">"
                    . "<td>".$vip." ".$com['raisonsociale']."</td>"
                    . "<td>".$com['nom']." ".$com['prenom']."</td>"
                    . "<td>".$com['dateinscription']."</td>"
                    . "<td>".$provtxt."</td>"
                    //. "<td>Expédiée en attente de paiement</td>"
                    //. "<td></td>"
                    //. "<td></td>"
                    //. "<td></td>"
                    . "<td><a href=\"https://www.mon-espace-cuisson.com/admin/facture.php?action=editer&facturid=".$com['facturid']."\">".$com['facturid']."</a></td>"
                    . "<td><a href=\"https://www.mon-espace-cuisson.com/facture2.php?facturid=".$com['facturid']."&passe=".$com['passefacture']."\">".$com['numerofacture']."</a></td>"
                    . "<td>".date("d/m/Y",$com['heurefacture'])."</td>"
                    . "<td>".date_create($com['dateexpedition'])->format("d/m/Y")."</td>"
                    . "<td align=\"right\">".number_format($totalcmd,2,',','')." &euro;</td>"
                    . "<td align=\"right\"><a href=\"https://www.mon-espace-cuisson.com/admin/facture.php?action=editer&facturid=".$com['facturid']."\">Commande</a></td>"
               . "</tr>";
               
               $incr++;
          }

          //ksort($ligne);
          $facture_rem = '';
          foreach($ligne as $facturid => $l){
               
               $facturid = substr($facturid,0,-1);

               if(empty($facture_rem) || $facture_rem != $facturid){
                    
                    $facture_rem = $facturid;
                    switch($bg_color){
                         case '#b8ffc4':
                              $bg_color = '#dff4ff';
                              break;
                         default:
                         case '#dff4ff':
                              $bg_color = '#b8ffc4';
                              break;
                    }
               }

               $html .= implode("", str_replace('%td_alt%',$bg_color,$l));
          }
          
          $html .= ""
                    . "<tr>"
                         . "<td colspan=\"11\" style=\"text-align:right;font-weight:bold;\">".number_format($totaltbl,2,',','')." &euro;</td>"
                    . "</tr>"
               . "</table>"
          . "</div>";
     }

     //Tableu "AVOIRS EN ATTENTE" 
     $html .= ""
      . "<div class=\"bloc\">"
          . "<p style=\"font-size: 16px; text-align: center;\">AVOIRS EN ATTENTE</p>"
          . "<table>";
    
     //Récupération des avoirs non reçu
     $sql = "SELECT *";
     $sql .= " FROM avoir_ligne";
     $sql .= " WHERE date_recu = 0";
     $sql .= " ORDER BY destinataire, date";
     $requete = $DB_site2->query($sql);

     $dest       = "";
     $totalavoir = 0;
     $total      = 0;
     $tr         = "";
     $i          = 0;
     while($avoir=$DB_site2->fetch_array($requete)){
          
          if($dest != $avoir['destinataire'] && $totalavoir > 0){
               
               if($i%2 == 0){
                    $bg_color = '#b8ffc4';
               } else {
                    $bg_color = '#dff4ff';
               }
               $i++;
               
               $html .= ""
               . "<tr style=\"background-color:".$bg_color.";\">"
                    . "<td>".$dest."</td>"
                    . "<td align=\"right\">".number_format($totalavoir,2,",","")." &euro;</td>"
               . "</tr>";
               
               $totalavoir = 0;
          }

          $dest        = $avoir['destinataire'];
          $totalavoir += $avoir['paht'];
          $total      += $avoir['paht'];
     }
     
     if($i%2 == 0){
          $bg_color = '#b8ffc4';
     } else {
          $bg_color = '#dff4ff';
     }
     $i++;
     
     $html .= ""
     . "<tr style=\"background-color:".$bg_color.";\">"
          . "<td>".$dest."</td>"
          . "<td align=\"right\">".number_format($totalavoir,2,",","")." &euro;</td>"
     . "</tr>";
    
     $html .= ""
               . "<tr>"
                    . "<td><b>Total</b></td>"
                    . "<td><b>".number_format($total,2,",","")." &euro;</b></td>"
               . "</tr>"
          . "</table>"
     . "</div>";

}

$mail = new email();
$mail->destinataire = "cecile@mon-espace-cuisson.com";
$mail->to           = "pr@mon-espace-cuisson.com";
$mail->expname      = "Ma Cave à Vin";
$mail->expediteur   = "contact@mon-espace-cuisson.com";
$mail->subject      = "Suivi financier";
$mail->body         = $html; 
if(in_array(date("w"),$sendwd) && date("H") < 10 && !isset($_GET['test'])){
     $return = $mail->sendmail();
     print "envoi";
}

if(isset($_GET['test'])) print $html;

//Fonction inutile avec envoi du suivi désactivé
/*function rappelbetween($from,$to){
  global $DB_site2;
  global $totaltbl;
  $to-=1;
  $ligne=array();

  $sql="SELECT u.nom,u.prenom,u.raisonsociale,u.userid,u.commercial,u.vip,u.dateinscription,sc.etat,sc.rappel,sc.pour FROM suivi_client sc INNER JOIN utilisateur u USING (userid) WHERE sc.rappel BETWEEN $from AND $to ORDER BY sc.rappel";
  //"SELECT u.nom,u.prenom,u.userid,u.commercial,(select rappel from suivi_client where userid=u.userid order by date desc limit 1) AS rappel FROM suivi_client sc INNER JOIN utilisateur u USING (userid) WHERE (select rappel from suivi_client where userid=u.userid order by date desc limit 1) BETWEEN $from AND $to group by sc.userid ORDER BY rappel"
  $rq=$DB_site2->query($sql);
  while($suivi=$DB_site2->fetch_array($rq)){
     $verif=$DB_site2->query_first("SELECT * FROM suivi_client WHERE userid=".$suivi['userid']." ORDER BY date DESC LIMIT 1");
     if($verif['rappel']==$suivi['rappel']){
	$facture=$DB_site2->query_first("SELECT valide,montantcadeau,prixport,facturid,heurefacture FROM facture WHERE userid=$suivi[userid] AND supprimee='0' AND provenance<>'sav' ORDER BY heurefacture DESC LIMIT 1");
	if($facture['facturid']!=""){
	  $lignes=$DB_site2->query_first("SELECT SUM(qte*prix) FROM lignefacture WHERE factureid=".$facture['facturid']);
	  $totalcmd=$lignes[0]+$facture['prixport']-$facture['montantcadeau'];
	}else
	  $totalcmd=0;

	switch($suivi['etat']){
	case 0:
	$suivitxt="<font color=\"red\">perdu</font>";
	break;
	case 1:
	$suivitxt="<font color=\"#0000AA\">en cours</font>";
	break;
	case 2:
	$suivitxt="<font color=\"#0000FF\">Presque sûr</span>";
	break;
	case 3:
	$suivitxt="<font color=\"#000088\">Peu probable</span>";
	break;
	}
	if(!in_array($facture[0],array(1,5,7,8)) && $suivi['etat']!=0){
	  $totaltbl+=$totalcmd;
	  $vip="";
	  $tvip=0;
	  if($suivi['vip']==1) {$vip="<span class=\"vip\">VIP</span>";$tvip=10000;}
	  $ligne[$suivi['rappel']][]="<tr><td>$vip $suivi[raisonsociale]</td><td>$suivi[nom] $suivi[prenom]</td><td>$suivi[dateinscription]</td><td>$suivitxt</td><td>$suivi[commercial]</td><td>$suivi[pour]</td><td>".date("d/m/Y H:i",$suivi['rappel'])."</td><td><a href=\"http://www.mon-espace-cuisson.com/admin/facture.php?action=editer&facturid=".$facture['facturid']."\">".$facture['facturid']."</a></td><td>".date("d/m/Y",$facture['heurefacture'])."</td><td align=\"right\">".number_format($totalcmd,2,',','')." &euro;</td><td align=\"right\"><a href=\"http://mon-espace-cuisson.com/admin/utilisateur.php?action=editer&userid=$suivi[userid]\">Fiche client</a></td></tr>\n";
	}
     }
  }

  ksort($ligne);
  $html="";
  foreach($ligne as $total=>$l){
      $html.=implode("",$l);
  }
  
  return $html;
}*/