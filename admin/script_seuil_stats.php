<?php

require "admin_global.php";
include "header.php";
include "../class/facture.class.php";
include "../class/fonctions.php";

ini_set("display_errors",0);


$annee=date("Y");
$Y = date("Y");
$year = $Y;
$encours=$annee==date("Y");

  $seuils=array(64=>array(100000,85000), //allemagne
  10=>array(35000,30000), //autriche
  17=>array(35000,30000), //belgique
  26=>array(70000,30000), //bulgarie
  184=>array(35000,30000), //chypre
  45=>array(280000,30000), //danemark
  153=>array(35000,30000), //espagne
  53=>array(35000,30000), //estonie
  56=>array(35000,30000), //finlande
  66=>array(35000,30000), //grece
  75=>array(8800000,30000), //hongrie
  81=>array(35000,30000), //irelande
  83=>array(35000,30000), //italie
  186=>array(34052,30000), //lettonie
  98=>array(36203,30000), //lithuanie
  99=>array(100000,85000), //luxembourg
  105=>array(35000,30000), //malte
  118=>array(100000,85000), //pays bas
  132=>array(160000,40000), //pologne
  133=>array(35000,30000), //portugal
  148=>array(35000,30000), //slovaquie
  44=>array(1140000,30000), //rep tcheque
  136=>array(118000,30000), //roumanie
  172=>array(70000,85000), //uk
  149=>array(35000,30000), //slovénie
  157=>array(320000,30000) //suède
);

$change=$devises->dailychange();
ksort($change);


/*********************************************************************************************************
* Dans cette page on récupère le CA généré par chaque pays de la même façon que dans les pages           *
* "dir_stat_export.php & mark_stat_export.php" pour le comparer au seuil limite fixé par la page         *
* "dir_config_export.php"                                                                                *
*                                                                                                        *
* Si le CA est supérieur ou égal à cette limite, on envoie un mail à l'adresse mail : pr@ma-cave-a-vin.fr*
**********************************************************************************************************/

include(BACK_PATH.'/statistiques/statistiques_adm.php');


$paysdata=array();
$totalpays=array();
$listefacture=array();

$sql="SELECT f.* FROM facture f INNER JOIN utilisateur u USING(userid) INNER JOIN pays p ON(p.paysid=u.paysid) WHERE valide IN (1,5,7,8) AND p.tvaintracom=1 AND f.dateexpedition LIKE '$annee-%'";
$req=$DB_site2->query($sql);

while($f=$DB_site2->fetch_array($req))
{
   $facture=new facture($DB_site2,$f);
   if(!isset($paysdata[$facture->utilisateur->paysid])) $paysdata[$facture->utilisateur->paysid]=array("CApart"=>0,"CApro"=>0,"CAintra"=>0,"nb"=>0);
      
   if($facture->utilisateur->raisonsociale=="")
     $cat="part";
   else
     $cat=$facture->utilisateur->apply_intracom?"intra":"pro";

    $paysdata[$facture->utilisateur->paysid]['CA'.$cat]+=$facture->totalht;
}


$sum=array('CApart'=>0,'CApro'=>0,'CAintra'=>0);


$req=$DB_site2->query("SELECT paysid,libelle,diminutif,TVAlocale,currency FROM pays WHERE tvaintracom=1 ORDER BY libelle");

$i = 0;
while($pays=$DB_site2->fetch_array($req))
{

    $sum['CApart']+=$paysdata[$pays['paysid']]['CApart'];
    $sum['CApro']+=$paysdata[$pays['paysid']]['CApro'];
    $sum['CAintra']+=$paysdata[$pays['paysid']]['CAintra'];

    $tab_ca[$i] = $paysdata[$pays['paysid']]['CApart']+$paysdata[$pays['paysid']]['CApro'];
 //echo    number_format($paysdata[$pays['paysid']]['CApart']+$paysdata[$pays['paysid']]['CApro']/*+$paysdata[$pays['paysid']]['CAintra']*/,2,',',' ');
    $i++;
    $tab_ca[$i] = $pays['libelle'];
    $i++;
}

$sql = "";
$sql = "SELECT alerte, pays_txt FROM taux_de_change_eu";
$req = $DB_site2->query($sql);

$i = 0;
while ($limit=$DB_site2->fetch_array($req))
{

  $tab_lim[$i] = $limit['alerte'];
  $i++;
  $tab_lim[$i] = $limit['pays_txt'];
  $i++;

}

$i = 0;
$a = 0;
while ($i <= 55)
{
  if ($tab_ca[$i] >= $tab_lim[$i])
  { 
    $name[$a] = $tab_lim[$i+1];
    $content_mail[$a] = $tab_lim[$i+1];
    $a++;
    $content_mail[$a] = "Chiffre : ";
    $content_mail[$a] .= $tab_ca[$i];
    $a++;
    $content_mail[$a] = "Alerte : ";
    $content_mail[$a] .= $tab_lim[$i];
    $a++;
  }

  $i = $i + 2;
}









/*
.___  ___.      ___       __   __      
|   \/   |     /   \     |  | |  |     
|  \  /  |    /  ^  \    |  | |  |     
|  |\/|  |   /  /_\  \   |  | |  |     
|  |  |  |  /  _____  \  |  | |  `----.
|__|  |__| /__/     \__\ |__| |_______|
                                       
*/



$test = 0;
if ($name[0] != NULL)
  $test = 1;

$name = implode(", ", $name);
$content_mail = "<ul><li>" . implode("</li><li>", $content_mail) . "</li></ul>";

$message = "Bonjour, <br><br><br> Le chiffre d'affaires réalisé dans le(s) pays suivant(s) :<br><br>".$name." <br><br>dépasse le seuil d'alerte fixé : <br><br>".$content_mail." <br><br>Cordialement,";


if ($test == 1)
{
  $mail=new email();
  $mail->expediteur="contact@ma-cave-a-vin.fr";
  $mail->expname="Ma Cave à Vin";
  $mail->destinataire="pr@ma-cave-a-vin.fr";
  $mail->subject ="Alerte - Seuil HT";
  $mail->body=$message;
  $mail->sendmail();
}
?>