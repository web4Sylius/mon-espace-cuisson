<?php

require "../admin_global.php";
require "functions_markstatexport.php";
include "../header.php";
include "menu_2.php";
include "../../class/facture.class.php";
include "../../class/fonctions.php";

ini_set("display_errors",0);


$annee=date("Y");
$Y = date("Y");
if(isset($_GET['year'])) $annee=intval($_GET['year']);
else
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


print "<style>
.input {width:100%;}
.stat_rouge {color:red; text-align: right;}
.stat_vert {color:green; text-align: right;}
 tr:hover { background-color: #ffff99;}
.ca_mois { color:black; border-collapse:collapse; border:solid thin black; margin:auto; float:left; }
.ca_mois td { border:solid thin black; width:100px; }
.ca { color:black; border-collapse:collapse; border:solid thin black; margin:auto; float:left; }
.ca td { border:solid thin black; width:180px; }
.ca_nb { text-align:right; white-space:nowrap; }
.ca_prc { text-align:center; white-space:nowrap; }
.ca_title { background-color:#4CAF50; text-align:center; color:white;}
.ca_second_title { background-color:#af504c; text-align:center; color:white;}
.ca_head { font-weight:bold; text-align:center; }
</style>
<script language=\"javascript\">
function showpopup(id){
    document.getElementById(id).style.visibility='visible';
    document.getElementById(id).style.backgroundColor='#AAFFAA';
    document.getElementById(id).parentNode.style.backgroundColor='#AAFFAA';
}
function hidepopup(id){
    document.getElementById(id).style.visibility='hidden';
    document.getElementById(id).parentNode.style.backgroundColor='';
}
</script>";?>


<h1>Statistiques Export - Enregistrés</h1>
<?php
include(BACK_PATH.'/statistiques/statistiques_adm.php');
echo "<br><br><br>";



echo "<p align=\"center\">Année : <select name=\"year\" onchange=\"document.location='mark_stat_export.php?year='+this.value\">";
for($y=2010;$y<=(2009+count($objectif_facture));$y++) echo "<option value=\"$y\"".($y==$annee?" selected":"").">$y</option>";
echo "</select></p>";



print "<table style=\"display:none;\">";
foreach($change as $currency=>$value) print "<tr><td>$currency</td><td>$value</td></tr>";
print "</table>";



print "<table class=\"ca\">
<thead>
  <tr>
    <td class=\"ca_second_title\" colspan=\"26\">Clients de l'union Européenne</td>
  </tr>
  <tr>
    <td colspan=\"2\">Pays</td>
    <td>Client part.</td>
    <td>Client pro</td>
    <td>client pro tva intra</td>
    <td>Total CA HT</td>
    <td>Seuil HT</td>
    <td>Position export</td>
    <td>% export</td>
    <td>av/ret / N-1</td>
    <td>% N-1</td>
    <td>av/ret / N-2</td>
    <td>% N-2</td>
    <td>Nbr inscrits</td>
    <td>Dif N-1</td>
    <td>Dif N-2</td>
    <td>Nbr factures</td>
    <td>Dif N-1</td>
    <td>Dif N-2</td>
    <td>Panier moyen</td>
    <td>Panier moyen N-1</td>
    <td>Panier moyen N-2</td>
  </tr>
</thead>";




/**************************************************************************************************
La premiere boucle permet de lire la table sql et de stocker les pays avec un rang (d'exportation)*
***************************************************************************************************/


$paysdata=array();
$totalpays=array();
$listefacture=array();
$sql="SELECT f.* FROM facture f INNER JOIN utilisateur u USING(userid) INNER JOIN pays p ON(p.paysid=u.paysid) WHERE valide IN (1,5,7,8) AND p.tvaintracom=1 AND f.datefacture LIKE '$annee-%'";
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
    $totalpays[$facture->utilisateur->paysid]+=$facture->totalht;
    $paysdata[$facture->utilisateur->paysid]['nb']++;
    $listefacture[$facture->utilisateur->paysid][]=$facture;
}

arsort($totalpays);
$total=array_sum($totalpays);
$paysrank=array_flip(array_keys($totalpays));

$nm1=annee_prec($annee-1,true,$encours);
$nm2=annee_prec($annee-2,true,$encours);

$sum=array('CApart'=>0,'CApro'=>0,'CAintra'=>0);

$compteur = 0;
$counter = 0;


$req=$DB_site2->query("SELECT paysid,libelle,diminutif,TVAlocale,currency FROM pays WHERE tvaintracom=1 ORDER BY libelle");
while($pays=$DB_site2->fetch_array($req))
{
 

    $partpro=$paysdata[$pays['paysid']]['CApart']+$paysdata[$pays['paysid']]['CApro'];
    $local="";
    $seuileur=$seuils[$pays['paysid']][0];

    if(isset($change[$pays['currency']]))
    {
       $seuileur=round($seuils[$pays['paysid']][0]*$change[$pays['currency']]);
       $local=$seuils[$pays['paysid']][0].$pays['currency'];
    }

    $color=($paysdata[$pays['paysid']]['CApart']+$paysdata[$pays['paysid']]['CApro']>$seuileur?"red":"");

    if($paysdata[$pays['paysid']]['nb']>0) $moy=$totalpays[$pays['paysid']]/$paysdata[$pays['paysid']]['nb'];
    
    $sum['CApart']+=$paysdata[$pays['paysid']]['CApart'];
    $sum['CApro']+=$paysdata[$pays['paysid']]['CApro'];
    $sum['CAintra']+=$paysdata[$pays['paysid']]['CAintra'];
    $sum['nb']+=$paysdata[$pays['paysid']]['nb'];

   if (isset($paysrank[$pays['paysid']])?($paysrank[$pays['paysid']]+1):"")
   {
      $tab_pays_rank[$counter] = $paysrank[$pays['paysid']];
      $counter++;
      $tab_pays_rank[$counter] = $pays['libelle'];
      $counter++;
      $compteur++;
   }
}










$total_ue=array_sum($sum);



/********************************************************************************************
La deuxieme boucle permet de chercher dans le tableau des pays avec un rang le meilleur rang*
l'afficher dans un tableau avec ses stats, puis passer au rang suivant                      *
jusqu'a ce que tout les pays exportateur soient affichés                                    *
*********************************************************************************************/


$best_rank = $compteur;
$paysdata=array();
$totalpays=array();
$listefacture=array();

$the_rank = 0;
$increment = 0;


while($the_rank < $compteur)
{
  $azer = 0;
  if($tab_pays_rank[$increment] == $the_rank)
  {
      $azer = $increment + 1;
      $increment = -2;

      $sql="SELECT f.* FROM facture f INNER JOIN utilisateur u USING(userid) INNER JOIN pays p ON(p.paysid=u.paysid) WHERE valide IN (1,5,7,8) AND p.tvaintracom=1 AND f.datefacture LIKE '$annee-%' AND p.libelle='".$tab_pays_rank[$azer]."'";
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
          $totalpays[$facture->utilisateur->paysid]+=$facture->totalht;
          $paysdata[$facture->utilisateur->paysid]['nb']++;
          $listefacture[$facture->utilisateur->paysid][]=$facture;
      }
      arsort($totalpays);
      $total=array_sum($totalpays);
      $paysrank=array_flip(array_keys($totalpays));
      $nm1=annee_prec($annee-1,true,$encours);
      $nm2=annee_prec($annee-2,true,$encours);
      $sum=array('CApart'=>0,'CApro'=>0,'CAintra'=>0);
      $total_ins = 0;
      $total_insn1 = 0;
      $total_insn2 = 0;
      $total_fac = 0;
      $total_facn1[0] = 0;
      $total_facn2[0] = 0;
      $total_facn1[1] = 0;
      $total_facn2[1] = 0;
      $req=$DB_site2->query("SELECT paysid,libelle,diminutif,TVAlocale,currency FROM pays WHERE tvaintracom=1 AND libelle='".$tab_pays_rank[$azer]."'");
      while($pays=$DB_site2->fetch_array($req))
      {
          if($year < $Y)
          {
            $inscrits = c_inscrits($year, $pays['paysid']);
            $ins_n1 = c_ins_av($year - 1, $pays['paysid'], $inscrits);
            $ins_n2 = c_ins_av($year - 2, $pays['paysid'], $inscrits);
            $factures = c_factures($year, $pays['paysid']);
            $fac_n1 = c_fac_av($year - 1, $pays['paysid'], $factures);
            $fac_n2 = c_fac_av($year - 2, $pays['paysid'], $factures);
          }
          else if($year == $Y)
          {
            $inscrits = c_inscrits_cur($year, $pays['paysid']);
            $ins_n1 = c_ins_cur_av($year - 1, $pays['paysid'], $inscrits);
            $ins_n2 = c_ins_cur_av($year - 2, $pays['paysid'], $inscrits);
            $factures = c_factures_cur($year, $pays['paysid']);
            $fac_n1 = c_fac_cur_av($year - 1, $pays['paysid'], $factures);
            $fac_n2 = c_fac_cur_av($year - 2, $pays['paysid'], $factures);
          }

          if ($inscrits == NULL && $ins_n1 != 0 || $inscrits == NULL && $ins_n2 != 0)
            $inscrits = 0;
          $total_ins += $inscrits;
          $total_insn1 += $ins_n1;
          $total_insn2 += $ins_n2;
          $total_fac += $factures;
          $total_facn1[0] += $fac_n1[0];
          $total_facn2[0] += $fac_n2[0];
          $total_facn1[1] += $fac_n1[1];
          $total_facn2[1] += $fac_n2[1];
          $partpro=$paysdata[$pays['paysid']]['CApart']+$paysdata[$pays['paysid']]['CApro'];
          $local="";
          $seuileur=$seuils[$pays['paysid']][0];
          if(isset($change[$pays['currency']]))
          {
             $seuileur=round($seuils[$pays['paysid']][0]*$change[$pays['currency']]);
             $local=$seuils[$pays['paysid']][0].$pays['currency'];
          }
         $color=($paysdata[$pays['paysid']]['CApart']+$paysdata[$pays['paysid']]['CApro']>$seuileur?"red":"");
          $moy=0;
          if($paysdata[$pays['paysid']]['nb']>0) $moy=$totalpays[$pays['paysid']]/$paysdata[$pays['paysid']]['nb'];
          $diffnm1=$totalpays[$pays['paysid']]-$nm1[$pays['paysid']];
          $diffnm2=$totalpays[$pays['paysid']]-$nm2[$pays['paysid']];
          $pcdiffnm1=$pcdiffnm2="";
          if($nm1[$pays['paysid']]>0) $pcdiffnm1=100*$diffnm1/$nm1[$pays['paysid']];
          if($nm2[$pays['paysid']]>0) $pcdiffnm2=100*$diffnm2/$nm2[$pays['paysid']];
          $sum['CApart']+=$paysdata[$pays['paysid']]['CApart'];
          $sum['CApro']+=$paysdata[$pays['paysid']]['CApro'];
          $sum['CAintra']+=$paysdata[$pays['paysid']]['CAintra'];
          $sum['nb']+=$paysdata[$pays['paysid']]['nb'];
          $pcexport=($paysdata[$pays['paysid']]['CApart']+$paysdata[$pays['paysid']]['CApro']+$paysdata[$pays['paysid']]['CAintra']) * 100;
          $pcexport=$pcexport/$total_ue;


          $sql = "SELECT given_value,taux FROM taux_de_change_eu WHERE pays_txt='".$pays['libelle']."'";
          $req=$DB_site2->query($sql);
          while($f=$DB_site2->fetch_array($req))
          {
            $seuil_HT = $f['given_value'] / $f['taux'];
          }

          print "
          <tr>
            <td style=\"height:25px;\"><img src=\"../images/drapeaux/br/".$pays['diminutif'].".gif\"></td>
            <td style=\"height:25px;\">".$pays['libelle']."</td>
            <td style=\"height:25px;\" class=\"ca_nb $color\">".number_format($paysdata[$pays['paysid']]['CApart'],2,',',' ')."&euro;</td>
            <td style=\"height:25px;\" class=\"ca_nb $color\">".number_format($paysdata[$pays['paysid']]['CApro'],2,',',' ')."&euro;</td>
            <td style=\"height:25px;\" class=\"ca_nb\">".number_format($paysdata[$pays['paysid']]['CAintra'],2,',',' ')."&euro;</td>
            <td style=\"height:25px;\" class=\"ca_nb\" onmouseover=\"showpopup('factures".$pays['paysid']."')\" onmouseout=\"hidepopup('factures".$pays['paysid']."')\">".number_format($paysdata[$pays['paysid']]['CApart']+$paysdata[$pays['paysid']]['CApro']+$paysdata[$pays['paysid']]['CAintra'],2,',',' ')."&euro;<div id=\"factures".$pays['paysid']."\" class=\"popup\">".formatlist($pays['paysid'])."</div></td>
            <td style=\"height:25px;\" class=\"ca_nb\">".number_format($seuil_HT,0,',',' ')."&euro;</td>
            <td style=\"height:25px;\" class=\"ca_prc\">".(isset($paysrank[$pays['paysid']])?($paysrank[$pays['paysid']]+1):"")."</td>
            <td style=\"height:25px;\" class=\"ca_prc\">".number_format($pcexport,2,',',' ')."%</td>
            ".tdvalue($diffnm1,"&euro;",true).
            tdvalue_prc($pcdiffnm1,"%",true).
            tdvalue($diffnm2,"&euro;",true).
            tdvalue_prc($pcdiffnm2,"%",true)."
            <td style=\"height:25px;\" class=\"ca_prc\">".$inscrits."</td>".
            tdvalue_n_prc($ins_n1,true).
            tdvalue_n_prc($ins_n2,true)."
            <td style=\"height:25px;\" class=\"ca_prc\">".$factures."</td>".
            tdvalue_n_prc($fac_n1[0],true).
            tdvalue_n_prc($fac_n2[0],true)."
            <td style=\"height:25px;\" class=\"ca_nb\">".number_format(($paysdata[$pays['paysid']]['CApart']+$paysdata[$pays['paysid']]['CApro']+$paysdata[$pays['paysid']]['CAintra']) / $factures,2,',',' ')."&euro;</td>
            <td style=\"height:25px;\" class=\"ca_nb\">".number_format($nm1[$pays['paysid']] / $fac_n1[1],2,',',' ')."&euro;</td>
            <td style=\"height:25px;\" class=\"ca_nb\">".number_format($nm2[$pays['paysid']] / $fac_n2[1],2,',',' ')."&euro;</td>
          </tr>";
        $the_rank++;
     } 
  }
  $increment = $increment + 2;
}



$paysdata=array();
$totalpays=array();
$listefacture=array();
$sql="SELECT f.* FROM facture f INNER JOIN utilisateur u USING(userid) INNER JOIN pays p ON(p.paysid=u.paysid) WHERE valide IN (1,5,7,8) AND p.tvaintracom=1 AND f.datefacture LIKE '$annee-%'";
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
    $totalpays[$facture->utilisateur->paysid]+=$facture->totalht;
    $paysdata[$facture->utilisateur->paysid]['nb']++;
    $listefacture[$facture->utilisateur->paysid][]=$facture;
}

arsort($totalpays);
$total=array_sum($totalpays);
$paysrank=array_flip(array_keys($totalpays));

$nm1=annee_prec($annee-1,true,$encours);
$nm2=annee_prec($annee-2,true,$encours);

$sum=array('CApart'=>0,'CApro'=>0,'CAintra'=>0);

/************************
Totaux inscrits-commande*
*************************/

$total_ins = 0;
$total_insn1 = 0;
$total_insn2 = 0;

$total_fac = 0;
$total_facn1[0] = 0;
$total_facn2[0] = 0;
$total_facn1[1] = 0;
$total_facn2[1] = 0;




$best_rank = $compteur;

$req=$DB_site2->query("SELECT paysid,libelle,diminutif,TVAlocale,currency FROM pays WHERE tvaintracom=1 ORDER BY libelle");
while($pays=$DB_site2->fetch_array($req))
{
 if ($paysrank[$pays['paysid']] <= $best_rank)
  {
   if (isset($paysrank[$pays['paysid']])?($paysrank[$pays['paysid']]+1):"")
    {
    if($year < $Y)
    {
      $inscrits = c_inscrits($year, $pays['paysid']);
      $ins_n1 = c_ins_av($year - 1, $pays['paysid'], $inscrits);
      $ins_n2 = c_ins_av($year - 2, $pays['paysid'], $inscrits);

      $factures = c_factures($year, $pays['paysid']);
      $fac_n1 = c_fac_av($year - 1, $pays['paysid'], $factures);
      $fac_n2 = c_fac_av($year - 2, $pays['paysid'], $factures);
    }
    else if($year == $Y)
    {
      $inscrits = c_inscrits_cur($year, $pays['paysid']);
      $ins_n1 = c_ins_cur_av($year - 1, $pays['paysid'], $inscrits);
      $ins_n2 = c_ins_cur_av($year - 2, $pays['paysid'], $inscrits);


      $factures = c_factures_cur($year, $pays['paysid']);
      $fac_n1 = c_fac_cur_av($year - 1, $pays['paysid'], $factures);
      $fac_n2 = c_fac_cur_av($year - 2, $pays['paysid'], $factures);
    }

    if ($inscrits == NULL && $ins_n1 != 0 || $inscrits == NULL && $ins_n2 != 0)
      $inscrits = 0;

    $total_ins += $inscrits;
    $total_insn1 += $ins_n1;
    $total_insn2 += $ins_n2;
    $total_fac += $factures;
    $total_facn1[0] += $fac_n1[0];
    $total_facn2[0] += $fac_n2[0];
    $total_facn1[1] += $fac_n1[1];
    $total_facn2[1] += $fac_n2[1];

    $partpro=$paysdata[$pays['paysid']]['CApart']+$paysdata[$pays['paysid']]['CApro'];
    $local="";
    $seuileur=$seuils[$pays['paysid']][0];

    if(isset($change[$pays['currency']]))
    {
    	 $seuileur=round($seuils[$pays['paysid']][0]*$change[$pays['currency']]);
    	 $local=$seuils[$pays['paysid']][0].$pays['currency'];
    }

    $color=($paysdata[$pays['paysid']]['CApart']+$paysdata[$pays['paysid']]['CApro']>$seuileur?"red":"");
    $moy=0;

    if($paysdata[$pays['paysid']]['nb']>0) $moy=$totalpays[$pays['paysid']]/$paysdata[$pays['paysid']]['nb'];
    
    $diffnm1=$totalpays[$pays['paysid']]-$nm1[$pays['paysid']];
    $diffnm2=$totalpays[$pays['paysid']]-$nm2[$pays['paysid']];
    $pcdiffnm1=$pcdiffnm2="";

    if($nm1[$pays['paysid']]>0) $pcdiffnm1=100*$diffnm1/$nm1[$pays['paysid']];
    if($nm2[$pays['paysid']]>0) $pcdiffnm2=100*$diffnm2/$nm2[$pays['paysid']];

    $pcexport=$total>0?100*$totalpays[$pays['paysid']]/$total:0;
    
    $sum['CApart']+=$paysdata[$pays['paysid']]['CApart'];
    $sum['CApro']+=$paysdata[$pays['paysid']]['CApro'];
    $sum['CAintra']+=$paysdata[$pays['paysid']]['CAintra'];
    $sum['nb']+=$paysdata[$pays['paysid']]['nb'];

      }
    }
}
/*

$total_ue=array_sum($sum);

$moy=0;
$sumtotalpays=array_sum($totalpays);
$sumnm1=array_sum($nm1);
$sumnm2=array_sum($nm2);

if($sum['nb']>0) $moy=$sumtotalpays/$sum['nb'];

$diffnm1=$sumtotalpays-$sumnm1;
$diffnm2=$sumtotalpays-$sumnm2;
$pcdiffnm1=100*$diffnm1/$sumnm1;
$pcdiffnm2=100*$diffnm2/$sumnm2;

print "
<tr class=\"total\">
  <td style=\"height:29px;\" colspan=\"2\">Total</td>
  <td style=\"height:29px;\" class=\"ca_nb\">".number_format($sum['CApart'],2,',',' ')."&euro;</td>
  <td style=\"height:29px;\" class=\"ca_nb\">".number_format($sum['CApro'],2,',',' ')."&euro;</td>
  <td style=\"height:29px;\" class=\"ca_nb\">".number_format($sum['CAintra'],2,',',' ')."&euro;</td>
  <td style=\"height:29px;\" class=\"ca_nb\">".number_format($total_ue,2,',',' ')."&euro;</td>
  <td style=\"height:29px;\"></td>
  <td style=\"height:29px;\"></td>"
  .tdvalue($diffnm1,"&euro;",true).
  tdvalue($pcdiffnm1,"%",true).
  tdvalue($diffnm2,"&euro;",true).
  tdvalue($pcdiffnm2,"%",true)."
  <td style=\"height:29px;\" class=\"ca_nb\">".$total_ins."</td>".
  tdvalue_n($total_insn1,true).
  tdvalue_n($total_insn2,true)."
  <td style=\"height:29px;\" class=\"ca_nb\">".$total_fac."</td>".
  tdvalue_n($total_facn1[0],true).
  tdvalue_n($total_facn2[0],true)."
  <td style=\"height:25px;\" class=\"ca_nb\">".number_format($total_ue / $total_fac,2,',',' ')."&euro;</td>
  <td style=\"height:25px;\" class=\"ca_nb\">".number_format($sumnm1 / $total_facn1[1],2,',',' ')."&euro;</td>
  <td style=\"height:25px;\" class=\"ca_nb\">".number_format($sumnm2 / $total_facn2[1],2,',',' ')."&euro;</td>
</tr>";



*/















/*******************************************************************************************
La troisieme boucle affiche les pays qui n'ont pas générés de CA mais                      *
qui ont tout de mêmes des statistiques à voir, comme le taux d'inscrits, panier moyen etc..*
********************************************************************************************/









/************************
Totaux inscrits-commande*
*************************/
/*
$total_ins = 0;
$total_insn1 = 0;
$total_insn2 = 0;

$total_fac = 0;
$total_facn1[0] = 0;
$total_facn2[0] = 0;
$total_facn1[1] = 0;
$total_facn2[1] = 0;
*/


$req=$DB_site2->query("SELECT paysid,libelle,diminutif,TVAlocale,currency FROM pays WHERE tvaintracom=1 ORDER BY libelle");
while($pays=$DB_site2->fetch_array($req))
{
 if ($paysrank[$pays['paysid']] == "" && $pays['libelle'] != "United Kingdom")
  {
    if($year < $Y)
    {
      $inscrits = c_inscrits($year, $pays['paysid']);
      $ins_n1 = c_ins_av($year - 1, $pays['paysid'], $inscrits);
      $ins_n2 = c_ins_av($year - 2, $pays['paysid'], $inscrits);

      $factures = c_factures($year, $pays['paysid']);
      $fac_n1 = c_fac_av($year - 1, $pays['paysid'], $factures);
      $fac_n2 = c_fac_av($year - 2, $pays['paysid'], $factures);
    }
    else if($year == $Y)
    {
      $inscrits = c_inscrits_cur($year, $pays['paysid']);
      $ins_n1 = c_ins_cur_av($year - 1, $pays['paysid'], $inscrits);
      $ins_n2 = c_ins_cur_av($year - 2, $pays['paysid'], $inscrits);


      $factures = c_factures_cur($year, $pays['paysid']);
      $fac_n1 = c_fac_cur_av($year - 1, $pays['paysid'], $factures);
      $fac_n2 = c_fac_cur_av($year - 2, $pays['paysid'], $factures);
    }

    if ($inscrits == NULL && $ins_n1 != 0 || $inscrits == NULL && $ins_n2 != 0)
      $inscrits = 0;

    $total_ins += $inscrits;
    $total_insn1 += $ins_n1;
    $total_insn2 += $ins_n2;
    $total_fac += $factures;
    $total_facn1[0] += $fac_n1[0];
    $total_facn2[0] += $fac_n2[0];
    $total_facn1[1] += $fac_n1[1];
    $total_facn2[1] += $fac_n2[1];

    $partpro=$paysdata[$pays['paysid']]['CApart']+$paysdata[$pays['paysid']]['CApro'];
    $local="";
    $seuileur=$seuils[$pays['paysid']][0];

    if(isset($change[$pays['currency']]))
    {
       $seuileur=round($seuils[$pays['paysid']][0]*$change[$pays['currency']]);
       $local=$seuils[$pays['paysid']][0].$pays['currency'];
    }

    $color=($paysdata[$pays['paysid']]['CApart']+$paysdata[$pays['paysid']]['CApro']>$seuileur?"red":"");
//  $local=number_format($change[$pays['currency']]*$partpro,2,',','').$pays['currency'];
    $moy=0;

    if($paysdata[$pays['paysid']]['nb']>0) $moy=$totalpays[$pays['paysid']]/$paysdata[$pays['paysid']]['nb'];
    
    $diffnm1=$totalpays[$pays['paysid']]-$nm1[$pays['paysid']];
    $diffnm2=$totalpays[$pays['paysid']]-$nm2[$pays['paysid']];
    $pcdiffnm1=$pcdiffnm2="";

    if($nm1[$pays['paysid']]>0) $pcdiffnm1=100*$diffnm1/$nm1[$pays['paysid']];
    if($nm2[$pays['paysid']]>0) $pcdiffnm2=100*$diffnm2/$nm2[$pays['paysid']];

    $pcexport=$total>0?100*$totalpays[$pays['paysid']]/$total:0;
    
    $sum['CApart']+=$paysdata[$pays['paysid']]['CApart'];
    $sum['CApro']+=$paysdata[$pays['paysid']]['CApro'];
    $sum['CAintra']+=$paysdata[$pays['paysid']]['CAintra'];
    $sum['nb']+=$paysdata[$pays['paysid']]['nb'];


          $sql2 = "SELECT given_value,taux FROM taux_de_change_eu WHERE pays_txt='".$pays['libelle']."'";
          $req2=$DB_site2->query($sql2);
          while($f=$DB_site2->fetch_array($req2))
          {
            $seuil_HT = $f['given_value'] / $f['taux'];
          }




         print "
    <tr>
      <td style=\"height:25px;\"><img src=\"../images/drapeaux/br/".$pays['diminutif'].".gif\"></td>
      <td style=\"height:25px;\">".$pays['libelle']."</td>
      <td style=\"height:25px;\" class=\"ca_nb $color\">".number_format($paysdata[$pays['paysid']]['CApart'],2,',',' ')."&euro;</td>
      <td style=\"height:25px;\" class=\"ca_nb $color\">".number_format($paysdata[$pays['paysid']]['CApro'],2,',',' ')."&euro;</td>
      <td style=\"height:25px;\" class=\"ca_nb\">".number_format($paysdata[$pays['paysid']]['CAintra'],2,',',' ')."&euro;</td>
      <td style=\"height:25px;\" class=\"ca_nb\" onmouseover=\"showpopup('factures".$pays['paysid']."')\" onmouseout=\"hidepopup('factures".$pays['paysid']."')\">".number_format($paysdata[$pays['paysid']]['CApart']+$paysdata[$pays['paysid']]['CApro']+$paysdata[$pays['paysid']]['CAintra'],2,',',' ')."&euro;<div id=\"factures".$pays['paysid']."\" class=\"popup\">".formatlist($pays['paysid'])."</div></td>
      <td style=\"height:25px;\" class=\"ca_nb\">".number_format($seuil_HT,0,',',' ')."&euro;</td>
      <td style=\"height:25px;\" class=\"ca_prc\">".(isset($paysrank[$pays['paysid']])?($paysrank[$pays['paysid']]+1):"")."</td>
      <td style=\"height:25px;\" class=\"ca_prc\">".number_format($pcexport,2,',',' ')."%</td>
      ".tdvalue($diffnm1,"&euro;",true).
      tdvalue_prc($pcdiffnm1,"%",true).
      tdvalue($diffnm2,"&euro;",true).
      tdvalue_prc($pcdiffnm2,"%",true)."
      <td style=\"height:25px;\" class=\"ca_prc\">".$inscrits."</td>".
      tdvalue_n_prc($ins_n1,true).
      tdvalue_n_prc($ins_n2,true)."
      <td style=\"height:25px;\" class=\"ca_prc\">".$factures."</td>".
      tdvalue_n_prc($fac_n1[0],true).
      tdvalue_n_prc($fac_n2[0],true)."
      <td style=\"height:25px;\" class=\"ca_nb\">".number_format(($paysdata[$pays['paysid']]['CApart']+$paysdata[$pays['paysid']]['CApro']+$paysdata[$pays['paysid']]['CAintra']) / $factures,2,',',' ')."&euro;</td>
      <td style=\"height:25px;\" class=\"ca_nb\">".number_format($nm1[$pays['paysid']] / $fac_n1[1],2,',',' ')."&euro;</td>
      <td style=\"height:25px;\" class=\"ca_nb\">".number_format($nm2[$pays['paysid']] / $fac_n2[1],2,',',' ')."&euro;</td>
    </tr>";
      
    }
}



/*
$total_ue=array_sum($sum);

$moy=0;
$sumtotalpays=array_sum($totalpays);
$sumnm1=array_sum($nm1);
$sumnm2=array_sum($nm2);

/*if($sum['nb']>0) $moy=$sumtotalpays/$sum['nb'];

$diffnm1=$sumtotalpays-$sumnm1;
$diffnm2=$sumtotalpays-$sumnm2;
$pcdiffnm1=100*$diffnm1/$sumnm1;
$pcdiffnm2=100*$diffnm2/$sumnm2;*//*

print "
<tr class=\"total\">
	<td style=\"height:29px;\" colspan=\"2\">Total</td>
  <td style=\"height:29px;\"></td>
	<td style=\"height:29px;\"></td>
  <td style=\"height:29px;\"></td>
  <td style=\"height:29px;\"></td>
  <td style=\"height:29px;\"></td>
	<td style=\"height:29px;\"></td>"
	.tdvalue($diffnm1,"&euro;",true).
	tdvalue($pcdiffnm1,"%",true).
	tdvalue($diffnm2,"&euro;",true).
	tdvalue($pcdiffnm2,"%",true)."
  <td style=\"height:29px;\" class=\"ca_nb\">".$total_ins."</td>".
  tdvalue_n($total_insn1,true).
  tdvalue_n($total_insn2,true)."
  <td style=\"height:29px;\" class=\"ca_nb\">".$total_fac."</td>".
  tdvalue_n($total_facn1[0],true).
  tdvalue_n($total_facn2[0],true)."
  <td style=\"height:25px;\" class=\"ca_nb\">".number_format($total_ue / $total_fac,2,',',' ')."&euro;</td>
  <td style=\"height:29px;\"></td>
  <td style=\"height:29px;\"></td>
</tr>";
      */



$total_ue=array_sum($sum);

$moy=0;
$sumtotalpays=array_sum($totalpays);
$sumnm1=array_sum($nm1);
$sumnm2=array_sum($nm2);

if($sum['nb']>0) $moy=$sumtotalpays/$sum['nb'];

$diffnm1=$sumtotalpays-$sumnm1;
$diffnm2=$sumtotalpays-$sumnm2;
$pcdiffnm1=100*$diffnm1/$sumnm1;
$pcdiffnm2=100*$diffnm2/$sumnm2;

print "
<tr class=\"total\">
  <td style=\"height:29px;\" colspan=\"2\">Total</td>
  <td style=\"height:29px;\" class=\"ca_nb\">".number_format($sum['CApart'],2,',',' ')."&euro;</td>
  <td style=\"height:29px;\" class=\"ca_nb\">".number_format($sum['CApro'],2,',',' ')."&euro;</td>
  <td style=\"height:29px;\" class=\"ca_nb\">".number_format($sum['CAintra'],2,',',' ')."&euro;</td>
  <td style=\"height:29px;\" class=\"ca_nb\">".number_format($total_ue,2,',',' ')."&euro;</td>
  <td style=\"height:29px;\"></td>
  <td style=\"height:29px;\"></td>
  <td style=\"height:29px;\"></td>"
  .tdvalue($diffnm1,"&euro;",true).
  tdvalue_prc($pcdiffnm1,"%",true).
  tdvalue($diffnm2,"&euro;",true).
  tdvalue_prc($pcdiffnm2,"%",true)."
  <td style=\"height:29px;\" class=\"ca_prc\">".$total_ins."</td>".
  tdvalue_n_prc($total_insn1,true).
  tdvalue_n_prc($total_insn2,true)."
  <td style=\"height:29px;\" class=\"ca_prc\">".$total_fac."</td>".
  tdvalue_n_prc($total_facn1[0],true).
  tdvalue_n_prc($total_facn2[0],true)."
  <td style=\"height:25px;\" class=\"ca_nb\">".number_format($total_ue / $total_fac,2,',',' ')."&euro;</td>
  <td style=\"height:25px;\" class=\"ca_nb\">".number_format($sumnm1 / $total_facn1[1],2,',',' ')."&euro;</td>
  <td style=\"height:25px;\" class=\"ca_nb\">".number_format($sumnm2 / $total_facn2[1],2,',',' ')."&euro;</td>
</tr>";


echo "<tr style=\"height:20px; background-color:#af504c;\"></tr>";
      
$paysdata=array();
$totalpays=array();
$listefacture=array();

$sql="SELECT f.* FROM facture f INNER JOIN utilisateur u USING(userid) INNER JOIN pays p ON(p.paysid=u.paysid) WHERE valide IN (1,5,7,8) AND p.tvaintracom=0 AND TVAtauxnormal=0 AND f.datefacture LIKE '$annee%'";
$req=$DB_site2->query($sql);

while($f=$DB_site2->fetch_array($req))
{
    $facture=new facture($DB_site2,$f);
    if(!isset($paysdata[$facture->utilisateur->paysid])) $paysdata[$facture->utilisateur->paysid]=array("CApart"=>0,"CApro"=>0,"CAintra"=>0,"nb"=>0);
    
    $cat=($facture->utilisateur->raisonsociale==""?"part":"pro");
    if($facture->porttva>0) $cat="tva";
    
    $paysdata[$facture->utilisateur->paysid]['CA'.$cat]+=$facture->totalht;
    $totalpays[$facture->utilisateur->paysid]+=$facture->totalht;
    $paysdata[$facture->utilisateur->paysid]['nb']++;
    $listefacture[$facture->utilisateur->paysid][]=$facture;
}

arsort($totalpays);
$total=array_sum($totalpays);
$paysrank=array_flip(array_keys($totalpays));

$nm1=annee_prec($annee-1,false,$encours);
$nm2=annee_prec($annee-2,false,$encours);
$sumnm1=$sumnm2=0;

$sum=array('CApart'=>0,'CApro'=>0,'CAtva'=>0,'nb'=>0);

print "
<thead>
  <tr>
    <td class=\"ca_title\" colspan=\"25\">Clients hors union Européenne</td>
  </tr>
  <tr>
    <td colspan=\"2\">Pays</td>
    <td>Client part.</td>
    <td>Client pro</td>
    <td colspan=\"1\"></td>
    <td>Total CA HT</td>
    <td>Position export</td>
    <td>% export</td>
    <td>av/ret / N-1</td>
    <td>% N-1</td>
    <td>av/ret / N-2</td>
    <td>% N-2</td>
    <td>Nbr inscrits</td>
    <td>Dif N-1</td>
    <td>Dif N-2</td>
    <td>Nbr cde</td>
    <td>Dif N-1</td>
    <td>Dif N-2</td>
    <td>Panier moyen.</td>
    <td>Panier moyen N-1.</td>
    <td>Panier moyen N-2.</td>
  </tr>
</thead>";




/************************
Totaux inscrits-commande*
*************************/

$total_ins = 0;
$total_insn1 = 0;
$total_insn2 = 0;

$total_fac = 0;
$total_facn1[0] = 0;
$total_facn2[0] = 0;
$total_facn1[1] = 0;
$total_facn2[1] = 0;

//////////////////////////////////////////


if($paysdata!=array())
{
    $paysids=array_keys($paysdata);
    $req=$DB_site2->query("SELECT paysid,libelle,diminutif,TVAlocale,currency FROM pays WHERE paysid IN(".implode(",",$paysids).") ORDER BY libelle");

    while($pays=$DB_site2->fetch_array($req)) {

      if($year < $Y)
          {
            $inscrits = c_inscrits($year, $pays['paysid']);
            $ins_n1 = c_ins_av($year - 1, $pays['paysid'], $inscrits);
            $ins_n2 = c_ins_av($year - 2, $pays['paysid'], $inscrits);

            $factures = c_factures($year, $pays['paysid']);
            $fac_n1 = c_fac_av($year - 1, $pays['paysid'], $factures);
            $fac_n2 = c_fac_av($year - 2, $pays['paysid'], $factures);
          }
          else if($year == $Y)
          {
            $inscrits = c_inscrits_cur($year, $pays['paysid']);
            $ins_n1 = c_ins_cur_av($year - 1, $pays['paysid'], $inscrits);
            $ins_n2 = c_ins_cur_av($year - 2, $pays['paysid'], $inscrits);


            $factures = c_factures_cur($year, $pays['paysid']);
            $fac_n1 = c_fac_cur_av($year - 1, $pays['paysid'], $factures);
            $fac_n2 = c_fac_cur_av($year - 2, $pays['paysid'], $factures);
          }
          if ($inscrits == NULL && $ins_n1 != 0 || $inscrits == NULL && $ins_n2 != 0)
            $inscrits = 0;
          $total_ins += $inscrits;
          $total_insn1 += $ins_n1;
          $total_insn2 += $ins_n2;
          $total_fac += $factures;
          $total_facn1[0] += $fac_n1[0];
          $total_facn2[0] += $fac_n2[0];
          $total_facn1[1] += $fac_n1[1];
          $total_facn2[1] += $fac_n2[1];

        	$moy=0;

        	if($paysdata[$pays['paysid']]['nb']>0) $moy=$totalpays[$pays['paysid']]/$paysdata[$pays['paysid']]['nb'];
        	
        	$diffnm1=$totalpays[$pays['paysid']]-$nm1[$pays['paysid']];
        	$diffnm2=$totalpays[$pays['paysid']]-$nm2[$pays['paysid']];
        	$pcdiffnm1=$pcdiffnm2="";

        	if($nm1[$pays['paysid']]>0) $pcdiffnm1=100*$diffnm1/$nm1[$pays['paysid']];
        	if($nm2[$pays['paysid']]>0) $pcdiffnm2=100*$diffnm2/$nm2[$pays['paysid']];
        	
        	$sum['CApart']+=$paysdata[$pays['paysid']]['CApart'];
        	$sum['CApro']+=$paysdata[$pays['paysid']]['CApro'];
        	$sum['CAtva']+=$paysdata[$pays['paysid']]['CAtva'];
        	$sum['nb']+=$paysdata[$pays['paysid']]['nb'];
        	$sumnm1+=$nm1[$pays['paysid']];
        	$sumnm2+=$nm2[$pays['paysid']];
        	
        	print "
          <tr>
          	  <td style=\"height:25px;\"><img src=\"../images/drapeaux/br/".$pays['diminutif'].".gif\"></td>
          	  <td style=\"height:25px;\">".$pays['libelle']."</td>
          	  <td style=\"height:25px;\" class=\"ca_nb\">".number_format($paysdata[$pays['paysid']]['CApart'],2,',',' ')."&euro;</td>
          	  <td style=\"height:25px;\" class=\"ca_nb\">".number_format($paysdata[$pays['paysid']]['CApro'],2,',',' ')."&euro;</td>
          	  <td style=\"height:25px;\" class=\"ca_nb\" colspan=\"1\"></td>
          	  <td style=\"height:25px;\" class=\"ca_nb\" onmouseover=\"showpopup('factures".$pays['paysid']."')\" onmouseout=\"hidepopup('factures".$pays['paysid']."')\">".number_format($totalpays[$pays['paysid']],2,',',' ')."&euro;<div id=\"factures".$pays['paysid']."\" class=\"popup\">".formatlist($pays['paysid'])."</div></td>
          	  <td style=\"height:25px;\" class=\"ca_nb\">".(isset($paysrank[$pays['paysid']])?($paysrank[$pays['paysid']]+1):"")."</td>
          	  <td style=\"height:25px;\" class=\"ca_nb\">".number_format(100*$totalpays[$pays['paysid']]/$total,2,',','')."%</td>
          	  ".tdvalue($diffnm1,"&euro;",true).
          	  tdvalue($pcdiffnm1,"%",true).
          	  tdvalue($diffnm2,"&euro;",true).
          	  tdvalue($pcdiffnm2,"%",true)."
              <td style=\"height:25px;\" class=\"ca_nb\">".$inscrits."</td>".
              tdvalue_n($ins_n1,true).
              tdvalue_n($ins_n2,true)."
              <td style=\"height:25px;\" class=\"ca_nb\">".$paysdata[$pays['paysid']]['nb']."</td>".
              tdvalue_n($fac_n1[0],true).
              tdvalue_n($fac_n2[0],true)."
              <td style=\"height:25px;\" class=\"ca_nb\">".number_format($moy,2,',',' ')."&euro;</td>
              <td style=\"height:25px;\" class=\"ca_nb\">".number_format($nm1[$pays['paysid']] / $fac_n1[1],2,',',' ')."&euro;</td>
              <td style=\"height:25px;\" class=\"ca_nb\">".number_format($nm2[$pays['paysid']] / $fac_n2[1],2,',',' ')."&euro;</td>
         	</tr>";
    }
}

$moy=0;
$sumtotalpays=array_sum($totalpays);

if($sum['nb']>0) $moy=$sumtotalpays/$sum['nb'];

$diffnm1=$sumtotalpays-$sumnm1;
$diffnm2=$sumtotalpays-$sumnm2;
$pcdiffnm1=100*$diffnm1/$sumnm1;
$pcdiffnm2=100*$diffnm2/$sumnm2;
$total_ex=$sum['CAtva']+$sum['CApro']+$sum['CApart'];

print "
<tr class=\"total\">
  	<td style=\"height:25px;\" colspan=\"2\">Total</td>
  	<td style=\"height:25px;\" class=\"ca_nb\">".number_format($sum['CApart'],2,',',' ')."&euro;</td>
  	<td style=\"height:25px;\" class=\"ca_nb\">".number_format($sum['CApro'],2,',',' ')."&euro;</td>
  	<td style=\"height:25px;\" colspan=\"1\"></td>
  	<td style=\"height:25px;\" class=\"ca_nb\">".number_format($total_ex,2,',',' ')."&euro;</td>
  	<td style=\"height:25px;\"></td>
  	<td style=\"height:25px;\"></td>"
  	.tdvalue($diffnm1,"&euro;",true).
  	tdvalue($pcdiffnm1,"%",true).
  	tdvalue($diffnm2,"&euro;",true).
  	tdvalue($pcdiffnm2,"%",true)."
    <td style=\"height:29px;\" class=\"ca_nb\">".$total_ins."</td>".
    tdvalue_n($total_insn1,true).
    tdvalue_n($total_insn2,true)."
    <td style=\"height:25px;\" class=\"ca_nb\">".$sum['nb']."</td>".
    tdvalue_n($total_facn1[0],true).
    tdvalue_n($total_facn2[0],true)."
    <td style=\"height:25px;\" class=\"ca_nb\">".number_format($moy,2,',',' ')."&euro;</td>
    <td style=\"height:25px;\" class=\"ca_nb\">".number_format($sumnm1 / $total_facn1[1],2,',',' ')."&euro;</td>
    <td style=\"height:25px;\" class=\"ca_nb\">".number_format($sumnm2 / $total_facn2[1],2,',',' ')."&euro;</td>
</tr>";
    
echo "<tr style=\"height:20px; background-color:#af504c;\"></tr>";

print "
<tr class=\"total\">
	<td colspan=\"2\">Total export HT</td>
	<td colspan=\"9\"></td>
	<td class=\"ca_nb\">".number_format($total_ex+$total_ue,2,',',' ')."&euro;</td>
</tr>";

$catotal=ca($annee);
print "
<tr class=\"total\">
	 <td colspan=\"8\"></td>
	 <td colspan=\"3\">CA HT Global</td>
	 <td class=\"ca_nb\">".number_format($catotal,2,',',' ')."&euro;</td>
	 <td class=\"ca_nb\">".round(100*($total_ex+$total_ue)/$catotal,2)."%</td>
</tr>";
	
print "</table>";

?>