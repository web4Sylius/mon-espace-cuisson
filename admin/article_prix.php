<?php


include_once("article_fournisseurs.php");
include_once("../class/frais_port.php");
include_once("../class/offre_fournisseur.php");

// $req=$DB_site2->query("SELECT * FROM article_lot");
// while($al=$DB_site2->fetch_array($req)) $DB_site2->query("INSERT INTO article_lot_site SET alid=".$al['id'].", site_code='base', prix=".$al['prix']);

if($_POST['action2']=="tempprice"){
	if($_POST['du']=="" || $_POST['au']=="")
		$error="Il faut préciser la période";
	$site_code=$_POST['site_code'];
	$sql="SELECT * FROM temp_price WHERE artid=$artid AND site_code='".$site_code."' AND id<>".intval($_POST['tpid']);
	// echo $sql;
	$others=$DB_site2->query($sql);
	
	$sp = explode("/",$_POST['du']);
	$du = mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
	$sp = explode("/",$_POST['au']);
	$au = mktime(23,59,0,$sp[1],$sp[0],$sp[2]);
  
    while($other=$DB_site2->fetch_array($others)){
		// echo "post_du : ".$du." other_du : ".$other['du'] . " other_au : " . $other['au'] . " post_au : ". $au ."<br>";
		if($du>=$other['du'] && $du<=$other['au'])
			$error="La date de début se situe dans une autre période de prix temporaire";
		if($au>=$other['du'] && $au<=$other['au'])
			$error="La date de fin se situe dans une autre période de prix temporaire";
    }
  $txtsuivi="";
	if($error == ""){
		

        if($_POST['tpid'] == 0){ //Création d'un prix temporaire
          $sql = "INSERT INTO temp_price SET";
          $sql .= " site_code        = '".$site_code."'";
          $sql .= ", du              = $du";
          $sql .= ", au              = $au";
          $sql .= ", artid           = $artid";
          $sql .= ", paht            = ".floatval($_POST['tpaht'][$site_code])."";
          $sql .= ", prix            = ".floatval($_POST['tprix'][$site_code])."";
          $sql .= ", prixconseilleht = ".floatval($_POST['tprixconseilleht'][$site_code]);
          $DB_site2->query($sql);

          $txtsuivi .= "Création prix temporaire : du ".date("d/m/Y", $du)." au ".date("d/m/Y", $au)."<br>";
          $txtsuivi .= "paht => ".floatval($_POST['tpaht'][$site_code])." prix => ".floatval($_POST['tprix'][$site_code])." prixconseilleht => ".floatval($_POST['tprixconseilleht'][$site_code]);
          $tpid = $DB_site2->insert_id();

          if(isset($_POST['tplotprix'][$site_code])){
      			foreach($_POST['tplotprix'][$site_code] as $alid=>$prix){
      				$sql = "INSERT INTO temp_price_lot SET";
      				$sql .= " tpid = ".intval($tpid);
      				$sql .= ", alid = ".intval($alid);
      				$sql .= ", prix = ".floatval($prix);
      				$sql .= ", paht = ".floatval($_POST['tpachatlotprix'][$site_code][$alid]);
      				$DB_site2->query($sql);
      			}
          }

          if($_POST['tppromo'][$site_code] == 1){
      			$sql = "INSERT INTO promo SET";
      			$sql.= " artid     =".$artid;
      			$sql.= ", site_code  ='".$site_code."'";
      			$sql.= ", position   =0";
      			$sql.= ", dbt_promo  =".$du;
      			$sql.= ", fin_promo  =".$au;
      			$DB_site2->query($sql);

            $txtsuivi .= "<br>Promo => oui";
          }

          $DB_site2->query("INSERT INTO suivi_article SET artid=$artid, commercial ='".$user_info['username']."', date ='".time()."', suivi='$txtsuivi'");

        } else { //Edition d'un prix temporaire

          $old = $DB_site2->query_first("SELECT * FROM temp_price WHERE id = ".intval($_POST['tpid']));

          $sql = "UPDATE temp_price SET";
          $sql .= " site_code        = '".$site_code."'";
          $sql .= ", du              = ".$du;
          $sql .= ", au              = ".$au;
          $sql .= ", artid           = ".$artid;
          $sql .= ", paht            = ".floatval($_POST['tpaht'][$site_code]);
          $sql .= ", prix            = ".floatval($_POST['tprix'][$site_code]);
          $sql .= ", prixconseilleht = ".floatval($_POST['tprixconseilleht'][$site_code]);
          $sql .= " WHERE id = ".intval($_POST['tpid']);
          $DB_site2->query($sql);

          $txtsuivi = "Modification prix temporaire : \npaht : ".$old['paht']." => ".floatval($_POST['tpaht'][$site_code])." \nprix : ".$old['prix']." => ".floatval($_POST['tprix'][$site_code])." \n prixconseilleht : ".floatval($_POST['tprixconseilleht'][$site_code])."\n du : ".date("d/m/Y",$old['du'])." => ".date("d/m/Y",$du)."\n au : ".date("d/m/Y",$old['au'])." => ".date("d/m/Y",$au);

          if(isset($_POST['tplotprix'][$site_code])){
            foreach($_POST['tplotprix'][$site_code] as $alid=>$prix){
             $sql = "UPDATE temp_price_lot SET";
             $sql .= " tpid  = ".intval($tpid);
             $sql .= ", alid = ".intval($alid);
             $sql .= ", prix = ".floatval($prix);
             $sql .= ", paht = ".floatval($_POST['tpachatlotprix'][$site_code][$alid]);
             $sql .= " WHERE tpid = ".intval($_POST['tpid']);
             $sql .= " AND alid = ".intval($alid);
             $DB_site2->query($sql);
           }
         }

        switch($_POST['tppromo'][$site_code])
        {
            case '0':
                if(isset($_POST['promoid']) && $_POST['promoid'] != '')
                {
                    $sql = "DELETE FROM promo WHERE promoid = " . intval($_POST['promoid']);
                    $DB_site2->query($sql);
                    $txtsuivi .= "\npromo => non";
                }
            break;
            case '1':
                $sql = "INSERT INTO promo SET";
                $sql .= " artid    =".$artid;
                $sql .= ", site_code   ='".$site_code."'";
                $sql .= ", position  =0";
                $sql .= ", dbt_promo =".$du;
                $sql .= ", fin_promo   =".$au;
                $DB_site2->query($sql);
                $txtsuivi .= "\npromo => oui";
            break;
        }

        $DB_site2->query("INSERT INTO suivi_article SET artid=$artid, commercial ='".$user_info['username']."', date ='".time()."', suivi='$txtsuivi'");

      }
    }
  }
  if($_POST['action2']=="deltempprice" && $_POST['tpid']!=0){
    $old = $DB_site2->query_first("SELECT * FROM temp_price WHERE id = ".intval($_POST['tpid'])); 
    $DB_site2->query("DELETE FROM temp_price WHERE id=".$_POST['tpid']);
    $txtsuivi = "Suppression prix temporaire :\npaht => ".$old['paht'].", prix => ".$old['prix'].", prixconseilleht => ".$old['prixconseilleht'];
    $txtsuivi .= "\ndu => ".date("d/m/Y",$old['du'])." au => ".date("d/m/Y",$old['au']);
     $DB_site2->query("INSERT INTO suivi_article SET artid=$artid, commercial ='".$user_info['username']."', date ='".time()."', suivi='$txtsuivi'");
  }


  $paht=$ecopartht=$prixtrspt=array();
  $req=$DB_site2->query("SELECT * FROM article_fournisseur WHERE artid=".$artid);
  while($article_fournisseur=$DB_site2->fetch_array($req)) {
    $sites_code=explode(",",$article_fournisseur['principal']);
    foreach($sites_code as $site_code){
      $paht[$site_code] = $article_fournisseur['prix'] ;
      $prixtrspt[$site_code] = $article_fournisseur['trspt'];
      $pahtdevise[$site_code]=$article_fournisseur['devise'];
    }
  }


  $prix=$prixht=$prixconseilleht=array();
  $req_article_site=$DB_site2->query("SELECT * FROM article_site WHERE artid='$artid'");
  while($article_site=$DB_site2->fetch_array($req_article_site)){
    $site_code=$article_site['site_code'];
    $prix[$site_code] = $article_site['prix'] ;
    $prixconseilleht[$site_code] = $article_site['prixconseilleht'] ;
    $ecopartht[$site_code]=$article['ecopartht'];
    $tauxchoisi[$site_code]=$article_site['tauxchoisi'];
  }

  $contenu_onglet.="<style>
  .tblprix,#tbltemp,.tbltempprices { background-color:#DEDEDE;border-collapse:collapse; }
  .tblprix { display:none; }
  .tbltempprices { display:none; width:500px; }
  .tblprix td,#tbltemp td,.tbltempprices td { border:solid thin white;padding:2px; }
  .tblprix .lot { background-color:#DEDEFF; }
  .tblprix .roinput { background:none; border:none; width:60px;}
  .tblprix .spy {border-collapse:collapse;}
  .tempprice { display:none;}
  .tabstats{width:500px;height:80px;background-color:#DEDEDE;}
  .tabstats td{border:1px solid white;text-align:center;}
	#prixdiv {float:left;margin-left:10px;}
	#tblprixbase,#tbltemppricesbase { display:table; }"
  .fournisseurs_css()."
  </style>

  <h3>Prix</h3>";

  $contenu_onglet.="<div>";
  foreach($sites as $site_code=>$site) {
    $style=$warning="";
    if(!isset($prix[$site_code])) {
     $style="color:red;";
     $warning="&#9888; ";
   }
   $contenu_onglet.="<a href=\"javascript:switch_site('$site_code')\" class=\"sitetab".($site_code=="base"?"on":"")."\" id=\"tab$site_code\" style=\"".$style."\">".$warning."Site $site_code</a>";
 }
 $contenu_onglet.="</div>";

 $contenu_onglet.="
 <form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
 <input type=\"hidden\" name=\"old\" value=\"\">
 <input type=\"hidden\" name=\"quitter\" value=\"\">
 <input type=\"hidden\" name=\"mq2\" value=\"\">
 <input type=\"hidden\" name=\"modif\" value=\"0\">
 <input type=\"hidden\" name=\"action2\" value=\"\">
 <input type=\"hidden\" name=\"artid\" value=\"$artid\">
 <input type=\"hidden\" name=\"catid\" value=\"$catid\">";


 $requete=$DB_site2->query("SELECT a.artcode,a.artid FROM article_lot al INNER JOIN article a ON(a.artid=al.lotid) WHERE al.artid=$artid");
 if($DB_site2->num_rows($requete)>0){
  $contenu_onglet.="<p>Cet article fait partie des lots suivant :</p>";
  while($lot=$DB_site2->fetch_array($requete))
    $contenu_onglet.="<a href=\"ajout_produit.php?artid=".$lot['artid']."&prec=modif\" target=\"_blank\">".$lot['artcode']."</a><br>";
}

$contenu_onglet.="<div id=\"prixdiv\"><h4>Prix normaux</h4>";


$change_achat=$devises->getchange("site","achat");
$change_vente=$devises->getchange("site","vente");

$tdtempprix=array();
foreach($sites as $site_code=>$site) {
  $devise_vente=$devises->symbole($site['devise']);
  if($pahtdevise[$site_code]=="") $pahtdevise[$site_code]="EUR";
  $devise_achat=$devises->symbole($pahtdevise[$site_code]);

  $taux=$DB_site2->query_first("SELECT 0,TVAtauxnormal,TVAtauxreduit FROM pays WHERE paysid=".$site['paysid_ref']);
  $tvapc=$taux[$tauxchoisi[$site_code]];
  $tvapc_site[$site_code]=$tvapc;

  $stylebarre=$styleecopart=$styleecopart=$stylepaht=$styleprix=$styleprixht="";

  $totalprix=$totalachat=$totalecopartht=$totalbarre=$totaltrspt=0;
  $tdartcode=$tdecopartht=$tdecopart=$tdprixht=$tdprix=$tdpaht=$tdbarre=$tdcalc=$tdspy="";
  $lartids=array();
  $lotartcount=0;
  $mainartro="";
  $trcalc="";

  $requete=$DB_site2->query("SELECT al.id,al.artid,al.qte,al.caractvalids FROM article_lot al WHERE lotid=$artid");
  if($DB_site2->num_rows($requete)>0){
    $mainartro="readonly";
    while($lart=$DB_site2->fetch_array($requete)){
     $lartids[]=$lart['id'];
     $larticle=$DB_site2->query_first("SELECT a.artid,a.artcode,a.artcode_eurocave,a.prixtrspt,a.ecopartht FROM article a WHERE artid=".$lart['artid']);
     $article_site=$DB_site2->query_first("SELECT prixconseilleht FROM article_site WHERE site_code='$site_code' AND artid=".$lart['artid']);
     $article_lot_site=$DB_site2->query_first("SELECT prix,paht FROM article_lot_site WHERE alid=".$lart['id']." AND site_code='".$site_code."'");
     $article_fournisseur=$DB_site2->query_first("SELECT ecopartht,trspt,devise FROM article_fournisseur WHERE artid=".$lart['artid']." AND (principal LIKE '%,$site_code' OR principal LIKE '$site_code,%' OR principal LIKE '%,$site_code,%' OR principal='$site_code')");
     $devise_achat_lot=$devises->symbole($article_fournisseur['devise']);

     $tdartcode.="<td class=\"lot\"><input type=\"hidden\" name=\"lotqte[$site_code][$lart[id]]\" value=\"$lart[qte]\">".$lart['qte']." x <a href=\"ajout_produit.php?artid=".$lart['artid']."&prec=modif\" target=\"_blank\">".$larticle['artcode']."</a><br>(".$larticle['artcode_eurocave'].")</td>";
     if($article_site==false)
       $tdspy.="<td class=\"lot\" rowspan=\"11\">Produit à configurer<br>individuellement<br>avant le lot</td>";
     else{
       $article_lot_site['prixht']=round($article_lot_site['prix']/(1+$tvapc/100),2);
       $totalprix+=($lart['qte']*$article_lot_site['prix']);
       $totalachat+= isset($change_achat[$article_fournisseur['devise']]) ? ($lart['qte']*$article_lot_site['paht']/$change_achat[$article_fournisseur['devise']]) : 0;
       $totalecopartht+=($lart['qte']*$article_fournisseur['ecopartht']);
       $totalbarre+=($lart['qte']*$article_site['prixconseilleht']);
       $totaltrspt+=($lart['qte']*$larticle['prixtrspt']);

       $prixtrspt[$site_code]+=($lart['qte']*$larticle['prixtrspt']);

       $tdecopartht.="<td class=\"lot\"><input type=\"text\" name=\"lotecopartht[$site_code][$lart[id]]\" value=\"".($larticle['ecopartht'])."\" class=\"roinput\" readonly></td>";
       $tdecopart.="<td class=\"lot\" id=\"lotecopart_".$site_code."_".$lart[id]."\">".round($larticle['ecopartht']*(1+$tvapc/100),2)."</td>";
       $tdtrspt.="<td class=\"lot\" id=\"lottrspt_".$site_code."_".$lart[id]."\">".round($larticle['prixtrspt'],2)."</td>";
       $tdprixht.="<td class=\"lot\" id=\"lotprixht_".$site_code."_".$lart[id]."\">".round($article_lot_site['prixht'],2).$devise_vente."</td>";
       $tdprix.="<td class=\"lot\"><input type=\"text\" name=\"lotprix[$site_code][$lart[id]]\" value=\"".($article_lot_site['prix'])."\" size=\"7\" onkeypress=\"nbonly(event,true);\" onkeyup=\"verifprix(false,'$site_code')\">".$devise_vente."</td>";
       $tdpaht.="<td class=\"lot\"><input type=\"text\" name=\"lotpaht[$site_code][$lart[id]]\" value=\"".($article_lot_site['paht'])."\" size=\"7\" onkeypress=\"nbonly(event,true);\" onkeyup=\"verifprix(false,'$site_code')\">$devise_achat_lot<br><span id=\"lotpattc_".$site_code."_".$lart[id]."\">".round($article_lot_site['paht']*(1+$tvapc/100),2)."$devise_achat_lot TTC</span><input type=\"hidden\" name=\"lotdevise[$site_code][$lart[id]]\" value=\"".$article_fournisseur['devise']."\"></td>";
       $tdbarre.="<td class=\"lot\"><input type=\"text\" name=\"lotbarre[$site_code][$lart[id]]\" value=\"".round($article_site['prixconseilleht']*(1+$tvapc/100),2)."\" class=\"roinput\" readonly> ".$devise_vente." TTC<br><span id=\"lotbarreht_".$site_code."_".$lart[id]."\">".round($article_site['prixconseilleht'],2)." ".$devise_vente." HT</td>";
       $tdspy.="<td class=\"lot\">".spytable($larticle['artcode'])."</td>";

       $tdtempprix[$site_code].="<td><input type=\"text\" name=\"tplotprix[$site_code][$lart[id]]\" value=\"".($article_lot_site['prix'])."\" size=\"7\" onkeyup=\"calctemp('$site_code');calcmarge('t','$site_code');\" onkeypress=\"nbonly(event,true);\">".$devise_vente."</td>";
       $tdcalc.="<td><a href=\"javascript:srcprix('".$larticle['artcode']."',".$lart[id].",".$lart['artid'].",'".$site_code."')\">Remise a jour tarif article</a></td>";

       $tdtempachatprix[$site_code].="<td><input type=\"text\" name=\"tpachatlotprix[$site_code][$lart[id]]\" value=\"".($article_lot_site['paht'])."\" size=\"7\" readonly>&euro;</td>";
     }
    //       $contenu_onglet.= "<tr><td><img src=\"images/supprimer.gif\" style=\"cursor:pointer;\" onclick=\"document.forms.addform.action='ajout_produit.php?delartid=$lart[id]';document.forms.addform.submit();\"></td><td></td><td></td><td>".number_format($larticle['prixconseilleht']*(1+$txtva/100),2,',','')."</td><td>$opt</td></tr>";
   }

   $trcalc="<tr><td></td><td><input type=\"button\" value=\"Calcul lot\" onclick=\"verifprix(true,'$site_code');calcmarge('','$site_code');calctxtva('$site_code');\"></td>$tdcalc</tr>";

   round($totalprix,2)==$prix[$site_code] ? $styleprix="color:green;" : $stylepaht="color:red;";
   round($totalachat,2)==floatval($paht[$site_code]) ? $stylepaht="color:green;" : $stylepaht="color:red;";
   $totalecopartht==floatval($ecopartht[$site_code]) ? $styleecopartht="color:green;" : $styleecopartht="color:red;";
   round($totalecopartht*(1+$tvapc/100),2)==$ecopart[$site_code] ? $styleecopart="color:green;" : $styleecopart="color:red;";
   round($totalbarre,2)==round($prixconseilleht[$site_code],2) ? $stylebarre="color:green;" : $stylebarre="color:red;";
   $lotartcount=$DB_site2->num_rows($requete);
 }

 $conseille="";
 if($site_code!="base"){
  $conseille="<br><i>Prix conseillé : <span id=\"conseille_".$site_code."\"></span>".$devise_vente."</i>";
  $ppi_conseille="<br><i>conseillé : <span id=\"ppi_conseille_".$site_code."\"></span>".$devise_vente."</i>";
}

$contenu_onglet.="
<table id=\"tblprix$site_code\" class=\"tblprix\">
<tr><td></td><td>$article[artcode]</td>".$tdartcode."</tr>
<tr><td>SPY</td><td>".spytable($article['artcode'])."</td>$tdspy</tr>
<tr>
<td align=\"left\">Prix d'achat HT : </td>
<td align=\"left\"><input type=\"text\" name=\"paht[$site_code]\" value=\"".$paht[$site_code]."\" style=\"$stylepaht\" size=\"7\" onkeyup=\"nbonly(event,true);setmodif();calctxtva('$site_code');calcmarge('','$site_code');\" autocomplete=\"off\" readonly>$devise_achat<br>
<span id=\"pattc_$site_code\">".number_format($paht[$site_code]*(1+$tvapc/100),2)."</span> $devise_achat TTC<input type=\"hidden\" id=\"changepaht_$site_code\" value=\"".$change_achat[$pahtdevise[$site_code]]."\"></td>
$tdpaht
</tr>
<tr>
<td align=\"left\">Eco-part HT : </td>
<td align=\"left\"><input type=\"text\" name=\"ecopartht[$site_code]\" value=\"".$ecopartht[$site_code]."\" style=\"$styleecopartht\" size=\"7\" onkeyup=\"nbonly(event,true);calctxtva('$site_code');calcmarge('','$site_code');\" onchange=\"setmodif();\" autocomplete=\"off\"></td>
$tdecopartht
</tr>
<tr>
<td align=\"left\">Eco-part TTC : </td>
<td align=\"left\"><input type=\"text\" name=\"ecopart[$site_code]\" value=\"".number_format($ecopartht[$site_code]*(1+$tvapc/100),2)."\" style=\"$styleecopart\" size=\"7\" onkeyup=\"nbonly(event,true);calctxtva('$site_code');\" onchange=\"setmodif();\" autocomplete=\"off\" readonly></td>
$tdecopart
</tr>
<tr>
<td align=\"left\">Trspt frn/emballage : </td>
<td align=\"left\"><input type=\"text\" name=\"prixtrspt[$site_code]\" value=\"".$prixtrspt[$site_code]."\" style=\"$styleprix\" size=\"7\" onkeyup=\"nbonly(event,true);calctxtva('$site_code');setmodif();calcmarge('','$site_code');\" autocomplete=\"off\" class=\"roinput\" readonly>$devise_achat</td>
$tdtrspt
</tr>
<tr>
<td align=\"left\">Prix vente HT : </td>
<td align=\"left\"><input type=\"text\" name=\"prixht[$site_code]\" value=\"".round($prix[$site_code]/(1+$tvapc/100),2)."\" id=\"prixht\" style=\"$styleprixht\" size=\"7\" class=\"roinput\" readonly>$devise_vente</td>
$tdprixht
</tr>
<tr>
<td align=\"left\">Prix vente TTC : </td>
<td align=\"left\"><input type=\"text\" name=\"prix[$site_code]\" value=\"".$prix[$site_code]."\" id=\"prix\" style=\"$styleprix\" size=\"7\" onkeyup=\"nbonly(event,true);setmodif();calctxtva('$site_code');calcmarge('','$site_code');\" autocomplete=\"off\" $mainartro><input type=\"hidden\" id=\"change_$site_code\" value=\"".$change_vente[$site['devise']]."\">$devise_vente $conseille</td>
$tdprix
</tr>
<tr>
<td align=\"left\">Marge HT : </td>
<td align=\"left\" id=\"marge_$site_code\"></td>
</tr>";
$prixconseille=round($prixconseilleht[$site_code]*(1+$tvapc/100),2);
$remise=0;
if($prixconseilleht[$site_code]>0) $remise=100*($prixconseilleht[$site_code]-$prix[$site_code]/(1+$tvapc/100))/$prixconseilleht[$site_code];
$contenu_onglet.="<tr>
<td align=\"left\">Prix conseillé TTC : </td>
<td align=\"left\"><input type=\"text\" name=\"prixconseille[$site_code]\" value=\"$prixconseille\" style=\"$stylebarre\" size=\"7\" onkeyup=\"nbonly(event,true);setmodif();calctxtva('$site_code');\" autocomplete=\"off\">$devise_vente<br><span id=\"barreht_$site_code\">".round($prixconseilleht[$site_code],2)."</span> $devise_vente HT<br><span id=\"remise_$site_code\">".round($remise,2)."</span>% $ppi_conseille</td>
$tdbarre
</tr>" ;
$seltaux0=$seltaux1=$seltaux2="";
${"seltaux".$tauxchoisi[$site_code]}="selected";

$contenu_onglet .= "<tr>
<td align=\"left\">Taux T.V.A. : <br><a href=\"tva.php\" target=\"_blank\">Vérifier les taux</a></td>
<td align=\"left\">
<input type=\"hidden\" name=\"tvapc[$site_code]\" value=\"".$tvapc."\">
<select name=\"tauxtva[$site_code]\" onchange=\"setmodif();\">
<option value=\"0\" $seltaux0 onclick=\"document.getElementsByName('tvapc[$site_code]')[0].value=0;calctxtva('$site_code');calcmarge('','$site_code');\">Aucun</option>
<option value=\"1\" $seltaux1 onclick=\"document.getElementsByName('tvapc[$site_code]')[0].value=$taux[1];calctxtva('$site_code');calcmarge('','$site_code');\">Taux normal (".$taux[1]."%)</option>
<option value=\"2\" $seltaux2 onclick=\"document.getElementsByName('tvapc[$site_code]')[0].value=$taux[2];calctxtva('$site_code');calcmarge('','$site_code');\">Taux réduit (".$taux[2]."%)</option>
</select>
</td>
</tr>
$trcalc
</table>";
}


$contenu_onglet.="</div>
<div style=\"float:left; margin:20px 0 0 10px;\">";

$artids=array();
$requete=$DB_site2->query("SELECT al.id,al.artid,al.qte,al.caractvalids FROM article_lot al WHERE lotid=$artid");
if($DB_site2->num_rows($requete)==0)
  $artids=array($artid=>1);
else
  while($art=$DB_site2->fetch_array($requete))
   $artids[$art['artid']]=$art['qte'];


 $offres=new offre_fournisseur($DB_site2);
 $from=new DateTime("1 year ago");
 $offres_art=$offres->search_artids($artids,"du",$from->getTimestamp(),true);

 $now=mktime(0,0,0);
 foreach($sites as $site_code=>$site) {
    $offre_conf=array();
    $devise_vente=$devises->symbole($site['devise']);
    $tps=$DB_site2->query("SELECT * FROM temp_price WHERE artid=$artid AND site_code='$site_code' ORDER BY du");
    $contenu_onglet.="<span style=\"color:red;\">$error</span>
    <table id=\"tbltempprices$site_code\" class=\"tbltempprices\">
    <tr><td colspan=\"4\" style=\"background:white;\"><h4 style=\"text-align:center;\">Prix temporaires site $site_code</h4><span onclick=\"edittemp(0,$now,$now,$prix[$site_code],$paht[$site_code],$prixconseilleht[$site_code],'','$site_code')\"><img src=\"images/plus.gif\" align=\"absmiddle\"> Ajouter</span></td></tr>
    <tr><td>Période</td><td>Prix vente TTC</td><td>Prix achat HT</td><td>Action</td></tr>";
    
    while($tp=$DB_site2->fetch_array($tps)){
        $style=$tp['du']<=$now && $tp['au']>=$now ? "style=\"color:green;\"":"";
        $lparam=array();
        if($lotartcount>0){
            $r2=$DB_site2->query("SELECT * FROM temp_price_lot WHERE tpid=$tp[id]");
            while($tplot=$DB_site2->fetch_array($r2)){
                $lparam[]=$tplot['alid'].":".$tplot['prix'].":".$tplot['paht']; 
            }
        }

        $txtoffre="";
        foreach($offres_art as $offre_art){
            if($offre_art->du==$tp['du'] && $offre_art->au==$tp['au']){
                $txtoffre="Offre fournisseur<br>";
                $offre_conf[]=$offre_art->id;
            }
        }
        // if($tp['du']<=$now && $tp['au']>=$now){
        if($tp['au']>=$now){
            $btn = ""
            . "<img src=\"images/editer.gif\" title=\"Modifier\" onclick=\"edittemp(" . $tp['id'] . "," . $tp['du'] . "," . $tp['au'] . "," . $tp['prix'] . "," . $tp['paht'] . "," . $tp['prixconseilleht'] . ",'" . implode(",",$lparam) . "','" . $site_code . "')\">"
            . " <img src=\"images/supprimer.gif\" title=\"Supprimer\" onclick=\"deltemp(" . $tp['id'] . ",'" . $site_code . "')\">";
        } else {
            $btn="-";
        }
        $contenu_onglet.="<tr>
            <td $style>".$txtoffre.date("d/m/Y",$tp['du'])." - ".date("d/m/Y",$tp['au'])."</td>
            <td>".number_format($tp['prix'],2,',','').$devise_vente." <s>".number_format($tp['prixconseilleht']*(1+$tvapc_site[$site_code]/100),2,',','').$devise_vente."</s> ".round(100*($tp['prixconseilleht']-($tp['prix']/(1+$tvapc_site[$site_code]/100)))/$tp['prixconseilleht'])."%</td>
            <td>".$tp['paht']."</td>
            <td>$btn</td>
        </tr>";

    }

    if(count($offres_art)>0){
        $paht_liste=array();
                
        foreach($offres_art as $offre_art){
                $offre_art=$offre_art[0];
                $requete=$DB_site2->query("SELECT al.id,al.artid,al.qte,al.prix FROM article_lot al WHERE lotid=$artid");
        
                $style=$offre_art->du<=$now && $offre_art->au>=$now ? "style=\"color:green;\"":"";
                $lparam=array();
                
                if(!in_array($offre_art->id,$offre_conf) && $offre_art->du>0 && $offre_art->au>0){
                    $paht_offre=$offre_art->paht_articles($artids);

                    if($DB_site2->num_rows($requete)==0){
                        $paht_liste[$artid]=array_keys(reset($paht_offre))[0];
                        $paht_art=$paht_liste[$artid];
                        
                    }else{
                        foreach($paht_offre as $lartid=>$pahtlist){
                            $paht_qte=0;
                            foreach($pahtlist as $alpaht=>$qte)
                                $paht_qte+=$qte*$alpaht;

                            $paht_qte/=array_sum($pahtlist);
                            if(!isset($paht_liste[$lartid]) || $paht_qte<$paht_liste[$lartid])
                            $paht_liste[$lartid]=$paht_qte;

                        }
                    
                        $paht_art=0;
                        while($al=$DB_site2->fetch_array($requete)){
                            $lparam[]=$al['id'].":".$al['prix'].":".$paht_liste[$al['artid']];
                            $paht_art+=$al['qte']*$paht_liste[$al['artid']];
                        }
                    }
                    
                    $btn="";
                    if($offre_art->au>time())
                        $btn="<img src=\"images/ajouter.gif\" onclick=\"edittemp(0,".$offre_art->du.",".$offre_art->au.",".$prix[$site_code].",".$paht_art.",".$prixconseilleht[$site_code].",'".implode(",",$lparam)."','$site_code')\">";
                    
                    $contenu_onglet.="<tr>
                        <td $style>Offre fournisseur<br>".($offre_art->du>0?date("d/m/Y",$offre_art->du):"")." - ".($offre_art->au>0?date("d/m/Y",$offre_art->au):"")."</td>
                        <td></td>
                        <td>".$paht_art."</td>
                        <td>".$btn."</td>
                        </tr>";
                }
        }
    
    }

    $contenu_onglet.="<tr><td id=\"tdedittemp$site_code\" colspan=\"4\"></td></tr>
    </table>";
}


$trvente=array();
foreach($sites as $site_code=>$site){
  $trmarge="";
  if($site['devise']!="EUR"){
   $trmarge="<tr>
   <td colspan=\"6\">Marge en ".$site['devise']." <small>1 ".$site['devise']." = ".$change_achat[$site['devise']]."/".$change_vente[$site['devise']]." EUR</small></td>
   <td id=\"margeht_devise_".$site_code."\"></td>
   </tr>";
 }

 $contenu_onglet.=<<<HTML
 <h4>Marge site $site_code (EUR)</h4> $change_info
 <table id="calcmarge_$site_code" border="1" cellspacing="0">
 <tr>
 <td></td>
 <td>Article</td>
 <td>Eco part</td>
 <td>Emball/trspt</td>
 <td>Garantie</td>
 <td>Transport</td>
 <td>Total</td>
 </tr>
 <tr>
 <td>Achat HT</td>
 <td id="maarticle_$site_code"></td>
 <td id="maeco_$site_code"></td>
 <td id="maembal_$site_code"></td>
 <td id="magar_$site_code"></td>
 <td id="matrspt_$site_code"></td>
 <td id="matotal_$site_code">
 </tr>
 <tr>
 <td>Vente HT site $site_code</td>
 <td id="mvarticle_$site_code"></td>
 <td id="mveco_$site_code"></td>
 <td></td>
 <td id="mvgar_$site_code"></td>
 <td id="mvtrspt_$site_code"></td>
 <td id="mvtotal_$site_code"></td>
 </tr>
 <tr>
 <td colspan="6">Marge</td>
 <td id="margeht_$site_code" style="font-weight:bold;"></td>
 </tr>
 $trmarge
 </table>
HTML;
}

$contenu_onglet.="</div>
<div class=\"clear\"></div>";

$contenu_onglet .= '<div id="fournisseurs">'.show_fournisseurs($DB_site2, $mq, $artid).'</div>';

$contenu_onglet.="</form>";

//nouveau dev
$sql = "SELECT ah.artid, a.artcode, a.artcode_eurocave, m.libelle as marque_nom, ah.date as date_unix, DATE_FORMAT(FROM_UNIXTIME(ah.date), '%d/%m/%Y') as date_tarif, ah.value";
$sql .= " FROM article_history ah";
$sql .= " INNER JOIN article a ON (a.artid = ah.artid)";
$sql .= " INNER JOIN article_site asite ON (asite.artid = a.artid)";
$sql .= " INNER JOIN article_marque am ON (am.artid = a.artid)";
$sql .= " INNER JOIN marque m ON (m.marqueid = am.marqueid)";
$sql .= " WHERE 1";
$sql .= " AND a.artid=".$artid;
//$sql .= " AND a.artid NOT IN (SELECT lotid FROM article_lot)";
//$sql .= " AND am.marqueid IN (".implode(',',$_POST['marque_id']).")";
$sql .= " AND ah.`key` = 'paht'";
$sql .= " AND ah.`value` != ''";
$sql .= " AND asite.site_code = 'base'";
$sql .= " AND asite.active IN (1,0)";
$sql .= " ORDER BY m.libelle, ah.artid";
$aHistoriquePrix=array();
$req = $DB_site2->query($sql);
while($le_tarif = $DB_site2->fetch_array($req)){
        $aPaHT['tarif_date']    = $le_tarif['date_tarif'];
        $aPaHT['tarif_montant'] = $le_tarif['value'];

        $aHistoriquePrix[$le_tarif['artid']]['article_ref']                          = $le_tarif['artcode'];
        $aHistoriquePrix[$le_tarif['artid']]['article_ref_fab']                      = $le_tarif['artcode_eurocave'];
        $aHistoriquePrix[$le_tarif['artid']]['article_marque']                       = $le_tarif['marque_nom'];
        $aHistoriquePrix[$le_tarif['artid']]['article_paht'][$le_tarif['date_unix']] = $aPaHT;
}

$sql = "SELECT tp.artid, a.artcode, a.artcode_eurocave, m.libelle as marque_nom, tp.du as date_unix, DATE_FORMAT(FROM_UNIXTIME(tp.du), '%d/%m/%Y') as date_debut_tarif, DATE_FORMAT(FROM_UNIXTIME(tp.au), '%d/%m/%Y') as date_fin_tarif, tp.paht";
$sql .= " FROM temp_price tp";
$sql .= " INNER JOIN article a ON (a.artid = tp.artid)";
$sql .= " INNER JOIN article_site asite ON (asite.artid = a.artid)";
$sql .= " INNER JOIN article_marque am ON (am.artid = a.artid)";
$sql .= " INNER JOIN marque m ON (m.marqueid = am.marqueid)";
$sql .= " WHERE 1";
$sql .= " AND a.artid=".$artid;
//$sql .= " AND a.artid NOT IN (SELECT lotid FROM article_lot)";
//$sql .= " AND am.marqueid IN (".implode(',',$_POST['marque_id']).")";
$sql .= " AND tp.site_code = 'base'";
$sql .= " AND asite.site_code = 'base'";
$sql .= " AND asite.active IN (1,0)";
$sql .= " ORDER BY m.libelle, tp.artid";
$req = $DB_site2->query($sql);
while($le_tarif = $DB_site2->fetch_array($req)){
        $aPaHT['tarif_date']    = 'Du '.$le_tarif['date_debut_tarif'].' au '.$le_tarif['date_fin_tarif'];
        $aPaHT['tarif_montant'] = $le_tarif['paht'];

        $aHistoriquePrix[$le_tarif['artid']]['article_ref']                          = $le_tarif['artcode'];
        $aHistoriquePrix[$le_tarif['artid']]['article_ref_fab']                      = $le_tarif['artcode_eurocave'];
        $aHistoriquePrix[$le_tarif['artid']]['article_marque']                       = $le_tarif['marque_nom'];
        $aHistoriquePrix[$le_tarif['artid']]['article_paht'][$le_tarif['date_unix']] = $aPaHT;
}

$contenu_onglet.= "<br><br><h1>Suivis des prix d'achat</h1>";
foreach($aHistoriquePrix as $article_id => $l_article){
  ksort($l_article['article_paht']);
  
  $contenu_onglet .="<table style=\"border: none; margin: 5px 0; border-collapse: collapse;\">"
    ."<tr>"
    ."<td style=\"border: 1px solid; text-align: center; background-color: #AA1147; color: white; padding: 5px 10px; width: 100px; font-size: 10px;\">Marque</td>
      <td style=\"border: 1px solid; text-align: center; background-color: #AA1147; color: white; padding: 5px 10px; width: 100px; font-size: 10px;\">Réf. fabricant</td>
      <td style=\"border: 1px solid; text-align: center; background-color: #AA1147; color: white; padding: 5px 10px; width: 100px; font-size: 10px;\">Réf. Acilyon</td>";
  $rem_last_tarif = '';
  foreach($l_article['article_paht'] as $le_tarif){

          if(strpos($le_tarif['tarif_montant'], 'base') !== false){
                  
                  preg_match('#[0-9]+#', $le_tarif['tarif_montant'], $matches);
                  
                  $article_paht = $matches[0];
          } else {
                  $article_paht = $le_tarif['tarif_montant'];
          }

          if($rem_last_tarif == '' || $rem_last_tarif != $article_paht){
                  $rem_last_tarif = $article_paht;    
                $contenu_onglet .=" <td style=\"border: 1px solid; text-align: center; background-color: #A88D5C; color: white; padding: 5px 10px; max-width: 110px; font-size: 10px;\"> $le_tarif[tarif_date]</td>";
          }
  }
  $contenu_onglet .="</tr>";
  $contenu_onglet .="<tr>";
  $contenu_onglet .="<td style=\"border: 1px solid; text-align: center; padding: 5px 10px; width: 100px; font-size: 10px;\">$l_article[article_marque]</td>
    <td style=\"border: 1px solid; text-align: center; padding: 5px 10px; width: 100px; font-size: 10px;\">$l_article[article_ref_fab]</td>
    <td style=\"border: 1px solid; text-align: center; padding: 5px 10px; width: 100px; font-size: 10px;\">$l_article[article_ref]</td>";
    $rem_last_tarif = '';
    foreach($l_article['article_paht'] as $le_tarif){ 

      if(strpos($le_tarif['tarif_montant'], 'base') !== false){
              
              preg_match('#[0-9]+#', $le_tarif['tarif_montant'], $matches);
              
              $article_paht = $matches[0];
      } else {
              $article_paht = $le_tarif['tarif_montant'];
      }

      if($rem_last_tarif == '' || $rem_last_tarif != $article_paht){
              $rem_last_tarif = $article_paht;
        $contenu_onglet.="<td style=\"border: 1px solid; text-align: center; padding: 5px 10px; max-width: 110px; font-size: 10px;\">".@number_format($article_paht,2,',',' ')." &euro;</td>";
      }
    }

    $contenu_onglet .="</tr></table>";
}
// PRIX 
$sql = "SELECT ah.artid, a.artcode, a.artcode_eurocave, m.libelle as marque_nom, ah.date as date_unix, DATE_FORMAT(FROM_UNIXTIME(ah.date), '%d/%m/%Y') as date_tarif, ah.value";
$sql .= " FROM article_history ah";
$sql .= " INNER JOIN article a ON (a.artid = ah.artid)";
$sql .= " INNER JOIN article_site asite ON (asite.artid = a.artid)";
$sql .= " INNER JOIN article_marque am ON (am.artid = a.artid)";
$sql .= " INNER JOIN marque m ON (m.marqueid = am.marqueid)";
$sql .= " WHERE 1";
$sql .= " AND a.artid=".$artid;
//$sql .= " AND a.artid NOT IN (SELECT lotid FROM article_lot)";
//$sql .= " AND am.marqueid IN (".implode(',',$_POST['marque_id']).")";
$sql .= " AND ah.`key` = 'prix'";
$sql .= " AND ah.`value` != ''";
$sql .= " AND asite.site_code = 'base'";
$sql .= " AND asite.active IN (1,0)";
$sql .= " ORDER BY m.libelle, ah.artid";

$req = $DB_site2->query($sql);
while($le_tarif = $DB_site2->fetch_array($req)){
        $aPaHT['tarif_date']    = $le_tarif['date_tarif'];
        $aPaHT['tarif_montant'] = $le_tarif['value'];

        $aHistoriquePrix[$le_tarif['artid']]['article_ref']                          = $le_tarif['artcode'];
        $aHistoriquePrix[$le_tarif['artid']]['article_ref_fab']                      = $le_tarif['artcode_eurocave'];
        $aHistoriquePrix[$le_tarif['artid']]['article_marque']                       = $le_tarif['marque_nom'];
        $aHistoriquePrix[$le_tarif['artid']]['article_paht'][$le_tarif['date_unix']] = $aPaHT;
}

$sql = "SELECT tp.artid, a.artcode, a.artcode_eurocave, m.libelle as marque_nom, tp.du as date_unix, DATE_FORMAT(FROM_UNIXTIME(tp.du), '%d/%m/%Y') as date_debut_tarif, DATE_FORMAT(FROM_UNIXTIME(tp.au), '%d/%m/%Y') as date_fin_tarif, tp.paht";
$sql .= " FROM temp_price tp";
$sql .= " INNER JOIN article a ON (a.artid = tp.artid)";
$sql .= " INNER JOIN article_site asite ON (asite.artid = a.artid)";
$sql .= " INNER JOIN article_marque am ON (am.artid = a.artid)";
$sql .= " INNER JOIN marque m ON (m.marqueid = am.marqueid)";
$sql .= " WHERE 1";
$sql .= " AND a.artid=".$artid;
//$sql .= " AND a.artid NOT IN (SELECT lotid FROM article_lot)";
//$sql .= " AND am.marqueid IN (".implode(',',$_POST['marque_id']).")";
$sql .= " AND tp.site_code = 'base'";
$sql .= " AND asite.site_code = 'base'";
$sql .= " AND asite.active IN (1,0)";
$sql .= " ORDER BY m.libelle, tp.artid";
$req = $DB_site2->query($sql);
while($le_tarif = $DB_site2->fetch_array($req)){
        $aPaHT['tarif_date']    = 'Du '.$le_tarif['date_debut_tarif'].' au '.$le_tarif['date_fin_tarif'];
        $aPaHT['tarif_montant'] = $le_tarif['paht'];

        $aHistoriquePrix[$le_tarif['artid']]['article_ref']                          = $le_tarif['artcode'];
        $aHistoriquePrix[$le_tarif['artid']]['article_ref_fab']                      = $le_tarif['artcode_eurocave'];
        $aHistoriquePrix[$le_tarif['artid']]['article_marque']                       = $le_tarif['marque_nom'];
        $aHistoriquePrix[$le_tarif['artid']]['article_paht'][$le_tarif['date_unix']] = $aPaHT;
}

$contenu_onglet.= "<h1>Suivis des prix de vente</h1>";
foreach($aHistoriquePrix as $article_id => $l_article){
  ksort($l_article['article_paht']);
  
  $contenu_onglet .="<table style=\"border: none; margin: 5px 0; border-collapse: collapse;\">"
    ."<tr>"
    ."<td style=\"border: 1px solid; text-align: center; background-color: #AA1147; color: white; padding: 5px 10px; width: 100px; font-size: 10px;\">Marque</td>
      <td style=\"border: 1px solid; text-align: center; background-color: #AA1147; color: white; padding: 5px 10px; width: 100px; font-size: 10px;\">Réf. fabricant</td>
      <td style=\"border: 1px solid; text-align: center; background-color: #AA1147; color: white; padding: 5px 10px; width: 100px; font-size: 10px;\">Réf. Acilyon</td>";
  $rem_last_tarif = '';
  foreach($l_article['article_paht'] as $le_tarif){

          if(strpos($le_tarif['tarif_montant'], 'base') !== false){
                  
                  preg_match('#[0-9]+#', $le_tarif['tarif_montant'], $matches);
                  
                  $article_paht = $matches[0];
          } else {
                  $article_paht = $le_tarif['tarif_montant'];
          }

          if($rem_last_tarif == '' || $rem_last_tarif != $article_paht){
                  $rem_last_tarif = $article_paht;    
                $contenu_onglet .=" <td style=\"border: 1px solid; text-align: center; background-color: #A88D5C; color: white; padding: 5px 10px; max-width: 110px; font-size: 10px;\"> $le_tarif[tarif_date]</td>";
          }
  }
  $contenu_onglet .="</tr>";
  $contenu_onglet .="<tr>";
  $contenu_onglet .="<td style=\"border: 1px solid; text-align: center; padding: 5px 10px; width: 100px; font-size: 10px;\">$l_article[article_marque]</td>
    <td style=\"border: 1px solid; text-align: center; padding: 5px 10px; width: 100px; font-size: 10px;\">$l_article[article_ref_fab]</td>
    <td style=\"border: 1px solid; text-align: center; padding: 5px 10px; width: 100px; font-size: 10px;\">$l_article[article_ref]</td>";
    $rem_last_tarif = '';
    foreach($l_article['article_paht'] as $le_tarif){ 

      if(strpos($le_tarif['tarif_montant'], 'base') !== false){
              
              preg_match('#[0-9]+#', $le_tarif['tarif_montant'], $matches);
              
              $article_paht = $matches[0];
      } else {
              $article_paht = $le_tarif['tarif_montant'];
      }

      if($rem_last_tarif == '' || $rem_last_tarif != $article_paht){
              $rem_last_tarif = $article_paht;
        $contenu_onglet.="<td style=\"border: 1px solid; text-align: center; padding: 5px 10px; max-width: 110px; font-size: 10px;\">".@number_format($article_paht,2,',',' ')." &euro;</td>";
      }
    }

    $contenu_onglet .="</tr></table>";
}

$contenu_onglet.=<<<HTML
<br><h4>Historique offres</h4>

HTML;
$contenu_onglet .="<table class=\"tabstats\">";
$hist_offres=$offres->search_artids($artids,"du",0,true);
foreach($hist_offres as $offregrp){
    $du=date("d/m/Y",$offregrp[0]->du);
    $au=date("d/m/Y",$offregrp[0]->au);
    
    $conditions=$DB_site2->query_first("SELECT * FROM fournisseur_condition WHERE societe='".mysqli_real_escape_string($DB_site2->link_id,$offregrp->fournisseur)."'");
    $symbole=$devises->symbole($conditions['devise']);
    
    $infosart="";
    foreach($offregrp as $offre){
        foreach($offre->articles as $offartid=>$infos){
            $offart=$DB_site2->query_first("SELECT artcode FROM article WHERE artid=".$offartid);
            
            $infosart.=$offart['artcode'];
            switch($infos['offre']){
                case "temp":
                $infosart.=" => ".$infos['value'].$symbole;
                break;
                case "reduc":
                $infosart.=" => -".$infos['value'].$symbole;
                break;
                case "pc":
                $infosart.=" => -".$infos['value']."%";
                break;
                case "offert":
                $infosart.=" => ".$infos['value']." offert";
                break;
            }
            $infosart.="<br>";
        }
    }
    
    $tdstyle=($offre->du<=time() && $offre->au>time()) ? "color:green" : "";
    
    $contenu_onglet.=<<<HTML
    <tr>
        <td>$offre->fournisseur</td>
        <td style="$tdstyle">$du</td>
        <td style="$tdstyle">$au</td>
        <td>$infosart</td>
    </tr>
HTML;
}

$contenu_onglet.="</table>";

//on cree le tableau avec des valeurs a 0 qu'on remplira ensuite en javascript plus bas
$date=date('Y');
$contenu_onglet .="<br><br><h4>ventes par années</h4>
                  <table class=\"tabstats\">
                    <tr><td></td><td>".$date."</td><td>".($date-1)."</td><td id=\"annee\">".($date-2)."</td><td><strong>Total</strong></td></tr>
                    <tr><td style=\"text-align:right;width:25%;\">nombre de devis</td><td id=\"devis_".$date."\">0</td><td id=\"devis_".($date-1)."\">0</td><td id=\"devis_".($date-2)."\">0</td><td id=\"total_devis\"></td></tr>
                    <tr><td style=\"text-align:right;width:25%;\">nombre de ventes</td><td id=\"ventes_".$date."\">0</td><td id=\"ventes_".($date-1)."\">0</td><td id=\"ventes_".($date-2)."\">0</td><td id=\"total_ventes\"></td></tr>
                    <tr id=\"ventes_lot\"><td style=\"text-align:right;width:25%;\">nb ventes lot</td><td id=\"ventes_lot_".$date."\">0</td><td id=\"ventes_lot_".($date-1)."\">0</td><td id=\"ventes_lot_".($date-2)."\">0</td><td id=\"total_ventes_lot\"></td></tr>
                    <tr><td style=\"text-align:right;width:25%;\">CA HT</td><td id=\"ca_".$date."\">0</td><td id=\"ca_".($date-1)."\">0</td><td id=\"ca_".($date-2)."\">0</td><td id=\"total_ca\"></td></tr>
                  </table>";


/*
$m=array();
$requete=$DB_site2->query("SELECT * FROM article_history WHERE artid=$artid ORDER BY date");
while($modif=$DB_site2->fetch_array($requete)){
  $m[$modif['date']][$modif['key']]=$modif['value'];
  $mu[$modif['date']]=$modif['utilisateur'];
}
krsort($m);
foreach($m as $date=>$fields){
  if(array_key_exists("paht",$fields))
   $pahtdata.=",".$date.":".$fields['paht'];
 if(array_key_exists("prix",$fields))
   $pvdata.=",".$date.":".$fields['prix'];
}
if($pahtdata!="") $contenu_onglet.="<br><br><img src=\"ccgraph.php?data=".substr($pahtdata,1)."&width=500height=200&abstype=ts&title=Prix achat\"><br>";
if($pvdata!="") $contenu_onglet.="<img src=\"ccgraph.php?data=".substr($pvdata,1)."&width=500height=200&abstype=ts&title=Prix vente\"><br>";
*/

foreach($sites as $site_code=>$site){
  
  $check1=$check2="";
  $checked = $DB_site2->query_first("SELECT * FROM promo WHERE artid=$artid");
  if($checked != "")
    $check1 = "checked";
  else
    $check2 = "checked";
  
  $devise_vente=$devises->symbole($site['devise']);
  $contenu_onglet.="<div id=\"tempprice$site_code\" class=\"tempprice\">
  <form name=\"formtemp_price_$site_code\" method=\"post\">
  <input type=\"hidden\" name=\"old\" value=\"tempprice\">
  <input type=\"hidden\" name=\"quitter\" value=\"\">
  <input type=\"hidden\" name=\"mq2\" value=\"\">
  <input type=\"hidden\" name=\"modif\" value=\"0\">
  <input type=\"hidden\" name=\"action2\" value=\"tempprice\">
  <input type=\"hidden\" name=\"artid\" value=\"$artid\">
  <input type=\"hidden\" name=\"catid\" value=\"$catid\">
  <input type=\"hidden\" name=\"action\" value=\"tempprice\">
  <input type=\"hidden\" name=\"tpid\" value=\"0\">
  <input type=\"hidden\" name=\"site_code\" value=\"$site_code\">
  <input type=\"hidden\" name=\"promoid\" value=\"" . $checked['promoid'] . "\">
  <a name=\"tempprice\"></a>
  <table id=\"tbltemp\">
  <tr>
  <td>Période</td>
  <td colspan=\"".($lotartcount+1)."\">du <input type=\"text\" name=\"du\" id=\"du$site_code\" size=\"8\" onclick=\"calendrier_show(this.id,this.value);\" readonly> au <input type=\"text\" name=\"au\" id=\"au$site_code\" size=\"8\" onclick=\"calendrier_show(this.id,this.value);\" readonly></td>
  </tr>
  <tr>
  <td>Article</td>
  <td>$article[artcode]</td>
  $tdartcode
  </tr>
  <tr>
  <td>Prix d'achat (principal)</td>
  <td><input type=\"text\" name=\"tpaht[$site_code]\" size=\"8\" value=\"\" onkeypress=\"nbonly(event,true);\" onkeyup=\"calctemp('$site_code');calcmarge('t','$site_code');\" ></td>
  $tdtempachatprix[$site_code]
  </tr>
  <tr>
  <td>Prix de vente TTC</td>
  <td><input type=\"text\" name=\"tprix[$site_code]\" size=\"8\" value=\"\" onkeypress=\"nbonly(event,true);\" onkeyup=\"calctemp('$site_code');calcmarge('t','$site_code');\" $mainartro>$devise_vente HT : <input name=\"tprixht[$site_code]\" size=\"8\" value=\"\" readonly></td>
  $tdtempprix[$site_code]
  </tr>
  <tr>
  <td>Prix conseillé TTC</td>
  <td><input type=\"text\" name=\"tprixconseille[$site_code]\" size=\"8\" value=\"\" onkeypress=\"nbonly(event,true);\" onkeyup=\"calctemp('$site_code');calcmarge('t','$site_code');\">$devise_vente HT : <input name=\"tprixconseilleht[$site_code]\" size=\"8\" value=\"\" readonly> <span id=\"tpcremise$site_code\"></span></td>
  </tr>
  <tr>
  <td>Produit en promo</td>
  <td><input type=\"radio\" name=\"tppromo[$site_code]\" value=\"1\" $check1> Oui <input type=\"radio\" name=\"tppromo[$site_code]\" value=\"0\" $check2> Non</td>
  </tr>
  <tr>
  <td colspan=\"".(2+$lotartcount)."\" align=\"center\"><input type=\"button\" value=\"Annuler\" onclick=\"closetemp('$site_code')\"> <input type=\"submit\" id=\"validtemp\" value=\"Valider\"></td>
  </tr>
  </table>
  </form>
  </div>";
}

$jsachatport=$jsprixport=$jsgarantie="";
foreach($sites as $site_code=>$site){
  $frais_port=new frais_port($DB_site2,array($artid=>1),$site['paysid_ref'],$prix[$site_code],$paht[$site_code],$site_code);
  $jsachatport.="\nachatport['$site_code']=".round($frais_port->paht,2).";";
  $jsprixport.="\nprixport['$site_code']=".round($frais_port->prixht,2).";";

  $lesGaranties=DAOgarantie::loadMaxGarantieArtByPays($DB_site2,$site_code,'FR',$site['paysid_ref'],$artid,'',$prix[$site_code]);
  $i=0;
  $garantie=0;
  if(!empty($lesGaranties)){
    foreach ($lesGaranties as $laGarantie) {
      $i++;
      if ($i==1) $garantie=round($laGarantie->paht*$change_achat[$laGarantie->devise],2);
      if ($laGarantie->cumul==1 && $i!=1) $garantie+=round($laGarantie->paht*$change_achat[$laGarantie->devise],2);
    }
  }
  $jsgarantie.="\ngarantie['$site_code']=".$garantie.";";
}

$lotartids=implode("','",$lartids);
$fournisseurs_js=fournisseurs_js();
$jssites=implode("','",array_keys($sites));
foreach($change_achat as $devise_code=>$value) $jschange.="\nchange_achat['$devise_code']=$value;\nchange_vente['$devise_code']=".$change_vente[$devise_code].";";

//check si fait parti d'un lot
$req = "SELECT lotid FROM article_lot WHERE artid=$artid";
$isLot = $DB_site2->query_first($req);
if($isLot){

}
//ICI on rempli le tableau des stats ventes par années
//produit en lot, on recupère le nombre de vente de ce produit en lot sur 3 dernières années.
$ventes_en_lot = "SELECT SUM(lf.qte) AS qte,SUM(lf.qte*lf.prix/(1+lf.tva/100)) AS total,EXTRACT(YEAR FROM f.datefacture) as annee FROM lignefacturediss lf INNER JOIN facture f ON (f.facturid=lf.factureid) INNER JOIN article a ON (a.artid=lf.artid) WHERE f.valide IN (1,5,7,8) AND a.artid=$artid GROUP BY annee ORDER BY annee DESC LIMIT 3";
//produit en lot, on récupère le nombre de devis pour ce produit en lot sur 3 dernières années.
$devis_ventes_en_lot="SELECT sum(lf.qte) AS qte,EXTRACT(YEAR FROM f.datefacture) as annee FROM lignefacturediss lf INNER JOIN facture AS f ON(factureid=facturid) INNER JOIN envoi_devis AS ed ON(f.facturid=ed.facturid) WHERE prix>0 AND lf.artid=$artid GROUP BY annee ORDER BY annee DESC LIMIT 3";


//produit seul à l'origine d'un lot, nb de ventes en lot de ce produit sur 3 dernières années
$ventes_lot = "SELECT SUM(lf.qte) AS qte, EXTRACT(YEAR FROM f.datefacture) as annee FROM lignefacture lf INNER JOIN facture f ON (f.facturid=lf.factureid) INNER JOIN article a ON (a.artcode=lf.artcode) WHERE f.valide IN (1,5,7,8) AND a.artid=$artid AND lf.lignefacturedissid>0 GROUP BY annee ORDER BY annee DESC LIMIT 3";
//produit seul étant à l'origine ou non d'un lot, son nb de ventes sur 3 dernières années.
$ventes = "SELECT SUM(lf.qte) AS qte,SUM(lf.qte*lf.prix/(1+lf.tva/100)) AS total,EXTRACT(YEAR FROM f.datefacture) as annee FROM lignefacture lf INNER JOIN facture f ON (f.facturid=lf.factureid) INNER JOIN article a ON (a.artcode=lf.artcode) WHERE f.valide IN (1,5,7,8) AND a.artid=$artid AND lf.lignefacturedissid=0 GROUP BY annee ORDER BY annee DESC LIMIT 3";
//produit seul étant à l'origine ou non d'un lot, son nb de devis sur 3 dernières années.
$devis ="SELECT sum(lf.qte) AS qte,EXTRACT(YEAR FROM f.datefacture) as annee FROM lignefacture lf INNER JOIN facture AS f ON(factureid=facturid) INNER JOIN envoi_devis AS ed ON(f.facturid=ed.facturid) WHERE prix>0 AND lf.lignefacturedissid=0 AND lf.artid=$artid GROUP BY annee ORDER BY annee DESC LIMIT 3";


//produit seul étant à l'origine ou non d'un lot, son nb de ventes total depuis mise en vente.
$total_ventes = "SELECT SUM(lf.qte) AS qte FROM lignefacture lf INNER JOIN facture f ON (f.facturid=lf.factureid) INNER JOIN article a ON (a.artcode=lf.artcode) WHERE f.valide IN (1,5,7,8) AND a.artid=$artid AND lf.lignefacturedissid=0";
//produit seul étant à l'origine ou non d'un lot, son nb de devis total depuis mise en vente.
$total_devis = "SELECT sum(lf.qte) AS qte FROM lignefacture lf INNER JOIN facture AS f ON(factureid=facturid) INNER JOIN envoi_devis AS ed ON(f.facturid=ed.facturid) WHERE prix>0 AND lf.lignefacturedissid=0 AND lf.artid=$artid ";
//produit seul étant à l'origine ou non d'un lot, son nb de ventes en lot total depuis mise en vente.
$total_ventes_lot ="SELECT SUM(lf.qte) AS qte FROM lignefacture lf INNER JOIN facture f ON (f.facturid=lf.factureid) INNER JOIN article a ON (a.artcode=lf.artcode) WHERE f.valide IN (1,5,7,8) AND a.artid=$artid AND lf.lignefacturedissid>0";


//produit en lot, sont nb total de ventes depuis mise en vente.
$total_ventes_en_lot = "SELECT SUM(lf.qte) AS qte FROM lignefacturediss lf INNER JOIN facture f ON (f.facturid=lf.factureid) INNER JOIN article a ON (a.artid=lf.artid) WHERE f.valide IN (1,5,7,8) AND a.artid=$artid";
//produit en lot, nb total de devis depuis mise en vente
$total_devis_vente_en_lot = "SELECT sum(lf.qte) AS qte FROM lignefacturediss lf INNER JOIN facture AS f ON(factureid=facturid) INNER JOIN envoi_devis AS ed ON(f.facturid=ed.facturid) WHERE prix>0 AND lf.artid=$artid";


$ventes_en_lot = $DB_site2->query($ventes_en_lot);
$vente = array();
$vente_lot = array();
$ca = array();
$ca_lot=array();
$nbdevis = array();
$lot=false;

//on vérifie si l'article est un lot
if ($DB_site2->num_rows($ventes_en_lot) > 0) {
  $devis = $DB_site2->query($devis_ventes_en_lot);
  $total_ventes = $DB_site2->query_first($total_ventes_en_lot);
  $total_devis = $DB_site2->query_first($total_devis_vente_en_lot);
  $total_ventes_lot[0] = 0;
  $lot=true;


  while($row = $DB_site2->fetch_array($ventes_en_lot)){
    $annee_vente = $row['annee'];
    if($annee_vente > (date('Y')-3) ){ 
      $vente['ventes_'.$annee_vente] = $row['qte'];
      $ca['ca_'.$annee_vente] = number_format($row['total'],2,',','')."&euro;" ;
    }
  }
  while ($row2 = $DB_site2->fetch_array($devis)) {
    $annee_devis = $row2['annee'];
    if($annee_devis > (date('Y')-3) ){
      $nbdevis['devis_'.$annee_devis] = $row2['qte'];
    }
  }

}else{ // si ce n'est pas un lot

  $ventes=$DB_site2->query($ventes);
  $ventes_lot=$DB_site2->query($ventes_lot);
  $devis=$DB_site2->query($devis);
  $total_ventes = $DB_site2->query_first($total_ventes);
  $total_ventes_lot = $DB_site2->query_first($total_ventes_lot);
  $total_devis = $DB_site2->query_first($total_devis);

  while($row = $DB_site2->fetch_array($ventes)){
    $annee_vente = $row['annee'];
    if($annee_vente > (date('Y')-3) ){ 
      $vente['ventes_'.$annee_vente] = $row['qte'];
      $ca['ca_'.$annee_vente] = number_format($row['total'],2,',','')."&euro;" ;
    }
  }
  while($row1 = $DB_site2->fetch_array($ventes_lot)){
    $annee_vente_lot = $row1['annee'];
    if($annee_vente_lot > (date('Y')-3) ){ 
      $vente_lot['ventes_lot_'.$annee_vente_lot] = $row1['qte'];
     //$ca_lot['ca_lot_'.$annee_vente_lot] = number_format($row1['total'],2,',','')."&euro;" ;
    }
  }
  while ($row2 = $DB_site2->fetch_array($devis)) {
    $annee_devis = $row2['annee'];
    if($annee_devis > (date('Y')-3) ){
      $nbdevis['devis_'.$annee_devis] = $row2['qte'];
    }
  }
}

$contenu_onglet .="<script language=\"javascript\">";

foreach ($vente as $sold => $value) {
  $contenu_onglet .= "document.getElementById(\"".$sold."\").innerHTML =\"".$value."\" ;";
}
foreach ($vente_lot as $sold_lot => $value) {
  $contenu_onglet .= "document.getElementById(\"".$sold_lot."\").innerHTML =\"".$value."\" ;";
}
foreach ($ca as $soldca => $value) {
 // foreach($ca_lot as $soldca_lot =>$value_lot){ 
    $contenu_onglet .= "document.getElementById(\"".$soldca."\").innerHTML =\"".$value."\" ;";
 // }
}
foreach ($nbdevis as $devi => $value) {
  $contenu_onglet .= "document.getElementById(\"".$devi."\").innerHTML =\"".$value."\" ;";
}
$contenu_onglet .="document.getElementById(\"total_devis\").innerHTML =\"".$total_devis[0]."\";";
$contenu_onglet .="document.getElementById(\"total_ventes\").innerHTML =\"".$total_ventes[0]."\";";

//si c'est un lot on fais disparaitre la ligne de ventes en lot du tableau
if($lot)
  $contenu_onglet .="document.getElementById(\"ventes_lot\").style.display=\"none\";";
else
  $contenu_onglet .="document.getElementById(\"total_ventes_lot\").innerHTML =\"".$total_ventes_lot[0]."\";";
  
$contenu_onglet .="</script>";

$contenu_onglet.= <<<JS
<script language="javascript">
var sites=new Array('$jssites');
var change_achat=new Array();
var change_vente=new Array();
var marge_ref=0;
$jschange
function calcmarge(pre,site) {
  if(site==undefined){
   for(i=0;i<sites.length;i++) calcmarge(pre,sites[i]);
     highlight_marge(sites[0]);
   return;
 }
 if(pre==undefined) pre='';
 var prixport=new Array();$jsprixport
 var achatport=new Array();$jsachatport
 var garantie=new Array();$jsgarantie;
 var totalachat=achatport[site]+garantie[site];

 totalachat+=parseFloat(document.getElementsByName(pre+'paht['+site+']')[0].value*document.getElementById('changepaht_'+site).value);
 totalachat+=parseFloat(document.getElementsByName('ecopartht['+site+']')[0].value);
 totalachat+=parseFloat(document.getElementsByName('prixtrspt['+site+']')[0].value);

 var totalvente=parseFloat(document.getElementsByName(pre+'prixht['+site+']')[0].value*document.getElementById('change_'+site).value)+prixport[site];
 var marge=round(totalvente-totalachat,2);
 var pcmarge=round(100*marge/totalvente,2);
 if(site=='base') marge_ref=pcmarge;
 if(pre=='') document.getElementById('marge_'+site).innerHTML=marge+' &euro; ('+pcmarge+'%)';
 var color='green';
 if(pcmarge<25) color='red';
 if(pre=='') document.getElementById('marge_'+site).style.color=color;
 document.getElementById('margeht_'+site).style.color=color;

 if(document.getElementById('conseille_'+site)){
   var achatttc=totalachat*(1+document.getElementsByName('tvapc['+site+']')[0].value/100);
   var vente_conseille_eur=achatttc/(1-marge_ref/100);
   var vente_conseille_devise=round(vente_conseille_eur/document.getElementById('change_'+site).value,2);
   document.getElementById('conseille_'+site).innerHTML=vente_conseille_devise;
   var remise_ppi=parseFloat(document.getElementById('remise_base').innerHTML);
   document.getElementById('ppi_conseille_'+site).innerHTML=round(vente_conseille_devise*(1+remise_ppi/100),2);
 }

 document.getElementById('maarticle_'+site).innerHTML=round(document.getElementsByName(pre+'paht['+site+']')[0].value*document.getElementById('changepaht_'+site).value,2);
 document.getElementById('maeco_'+site).innerHTML=document.getElementsByName('ecopartht['+site+']')[0].value;
 document.getElementById('magar_'+site).innerHTML=garantie[site];
 document.getElementById('matrspt_'+site).innerHTML=achatport[site];
 document.getElementById('matotal_'+site).innerHTML=round(totalachat,2);
 document.getElementById('mvarticle_'+site).innerHTML=round(document.getElementsByName(pre+'prixht['+site+']')[0].value*document.getElementById('change_'+site).value,2);
 document.getElementById('mveco_'+site).innerHTML='&nbsp;';
 document.getElementById('maembal_'+site).innerHTML=parseFloat(document.getElementsByName('prixtrspt['+site+']')[0].value);
 document.getElementById('mvgar_'+site).innerHTML=0;
 document.getElementById('mvtrspt_'+site).innerHTML=prixport[site];
 document.getElementById('mvtotal_'+site).innerHTML=round(totalvente,2);
 document.getElementById('margeht_'+site).innerHTML=marge+'&euro; ('+pcmarge+'%)';
 if(document.getElementById('margeht_devise_'+site)){
  document.getElementById('margeht_devise_'+site).innerHTML=round(marge*document.getElementById('change_'+site).value,2);
  document.getElementById('margeht_devise_'+site).style.color=color;
}
highlight_marge(site);
}
window.onload=function() { calcmarge(); }

function verifprix(update,site_code){
  if(lotartids.length==0) return;

  var totalprix=0;
  var totalachat=0;
  var totaleco=0;
  var totalbarre=0;
  //var totaltrspt=0;
  var txtva=document.getElementsByName('tvapc['+site_code+']')[0].value;

  for(i=0;i<lotartids.length;i++){
   lotartid=parseInt(lotartids[i]);
   if(!document.getElementById('lotprixht_'+site_code+'_'+lotartid)){
     alert("Un article n'est pas configuré individuellement pour ce site, impossible de configurer le lot");
     return;
   }
   document.getElementById('lotprixht_'+site_code+'_'+lotartid).innerHTML=round(document.getElementsByName('lotprix['+site_code+']['+lotartid+']')[0].value/(1+txtva/100),2);
   var changedevise=document.getElementsByName('lotdevise['+site_code+']['+lotartid+']')[0].value;
   var qte=parseInt(document.getElementsByName('lotqte['+site_code+']['+lotartid+']')[0].value);
   totalprix+=parseFloat(document.getElementsByName('lotprix['+site_code+']['+lotartid+']')[0].value)*qte;
   totalachat+=parseFloat(document.getElementsByName('lotpaht['+site_code+']['+lotartid+']')[0].value*change_achat[changedevise])*qte;
   totaleco+=parseFloat(document.getElementsByName('lotecopartht['+site_code+']['+lotartid+']')[0].value)*qte;
   totalbarre+=parseFloat(document.getElementsByName('lotbarre['+site_code+']['+lotartid+']')[0].value)*qte;
   //totaltrspt+=parseFloat(document.getElementsByName('lottrspt['+site_code+']['+lotartid+']')[0].value)*qte;
 }

 if(update==true){
   document.getElementsByName('prix['+site_code+']')[0].value=round(totalprix,2);
   var totalprixht=totalprix/(1+txtva/100);
   document.getElementsByName('prixht['+site_code+']')[0].value=round(totalprixht,2);
   document.getElementsByName('paht['+site_code+']')[0].value=round(totalachat,2);
   document.getElementsByName('ecopartht['+site_code+']')[0].value=round(totaleco,2);
   document.getElementsByName('ecopart['+site_code+']')[0].value=round(totaleco*(1+txtva/100),2);
   document.getElementsByName('prixconseille['+site_code+']')[0].value=round(totalbarre,2);
   document.getElementById('barreht_'+site_code).innerHTML=round(totalbarre*(1+txtva/100),2);
   //document.getElementsByName('prixtrspt['+site+']')[0].value=round(totaltrspt,2);
   document.getElementsByName('fourn_prix_'+site_code)[0].value=document.getElementsByName('paht['+site_code+']')[0].value;
   document.getElementsByName('fourn_ecopartht_'+site_code)[0].value=document.getElementsByName('ecopartht['+site_code+']')[0].value;
   document.getElementsByName('trspt_'+site_code)[0].value=document.getElementsByName('prixtrspt['+site_code+']')[0].value;
   setmodif();
 }

 if(Math.round(totalprix)==Math.round(document.getElementsByName('prix['+site_code+']')[0].value))
  document.getElementsByName('prix['+site_code+']')[0].style.color='green';
else
  document.getElementsByName('prix['+site_code+']')[0].style.color='red';

if(Math.round(totalprixht)==Math.round(document.getElementsByName('prixht['+site_code+']')[0].value))
  document.getElementsByName('prixht['+site_code+']')[0].style.color='green';
else
  document.getElementsByName('prixht['+site_code+']')[0].style.color='red';

if(Math.round(totalachat)==Math.round(document.getElementsByName('paht['+site_code+']')[0].value))
  document.getElementsByName('paht['+site_code+']')[0].style.color='green';
else
  document.getElementsByName('paht['+site_code+']')[0].style.color='red';

if(totaleco==document.getElementsByName('ecopartht['+site_code+']')[0].value)
  document.getElementsByName('ecopartht['+site_code+']')[0].style.color='green';
else
  document.getElementsByName('ecopartht['+site_code+']')[0].style.color='red';

if(Math.round(totalbarre)==Math.round(document.getElementsByName('prixconseille['+site_code+']')[0].value))
  document.getElementsByName('prixconseille['+site_code+']')[0].style.color='green';
else
  document.getElementsByName('prixconseille['+site_code+']')[0].style.color='red';

highlight_marge(site_code);

}
var lotartids=new Array('$lotartids');

function calctxtva(site){
  if(site==undefined) site='base';
  var tvadec=1+document.getElementsByName('tvapc['+site+']')[0].value/100;
  document.getElementsByName('prixht['+site+']')[0].value=round(parseFloat(document.getElementsByName('prix['+site+']')[0].value)/tvadec,3);
  var barreht=round(parseFloat(document.getElementsByName('prixconseille['+site+']')[0].value)/tvadec,3);
  document.getElementById('barreht_'+site).innerHTML=barreht;
  document.getElementById('remise_'+site).innerHTML=round(100*(barreht-parseFloat(document.getElementsByName('prixht['+site+']')[0].value))/barreht,2);
  document.getElementById('pattc_'+site).innerHTML=round(document.getElementsByName('paht['+site+']')[0].value*tvadec,2);
  document.getElementsByName('ecopart['+site+']')[0].value=round(document.getElementsByName('ecopartht['+site+']')[0].value*tvadec,3);
}
function calctemp(site_code){
  var tvadec=1+document.getElementsByName('tvapc['+site_code+']')[0].value/100;

  if(lotartids.length>0 && lotartids[0]!=''){
   var prix=0;
   var paht=0;
   for(i=0;i<lotartids.length;i++){
     var lotartid=parseInt(lotartids[i]);
     var qte=parseInt(document.getElementsByName('lotqte['+site_code+']['+lotartid+']')[0].value);
     prix+=parseFloat(document.getElementsByName('tplotprix['+site_code+']['+lotartid+']')[0].value*qte);
   }
   document.getElementsByName('tprix['+site_code+']')[0].value=prix;
 }

 document.getElementsByName('tprixht['+site_code+']')[0].value=round(parseFloat(document.getElementsByName('tprix['+site_code+']')[0].value)/tvadec,3);
 document.getElementsByName('tprixconseilleht['+site_code+']')[0].value=round(parseFloat(document.getElementsByName('tprixconseille['+site_code+']')[0].value)/tvadec,3);
 document.getElementById('tpcremise'+site_code).innerHTML=round(100*(document.getElementsByName('tprixconseilleht['+site_code+']')[0].value-document.getElementsByName('tprixht['+site_code+']')[0].value)/document.getElementsByName('tprixconseilleht['+site_code+']')[0].value,2)+'%';
 highlight_marge(site_code);
}
function edittemp(tpid,du,au,prix,paht,prixconseilleht,templot,site_code){
  var tvadec=1+document.getElementsByName('tvapc['+site_code+']')[0].value/100;
  document.forms['formtemp_price_'+site_code].tpid.value=tpid;
  document.getElementsByName('tpaht['+site_code+']')[0].value=paht;
  document.getElementsByName('tprix['+site_code+']')[0].value=prix;
  document.getElementsByName('tprixconseille['+site_code+']')[0].value=Math.round(1000*prixconseilleht*tvadec)/1000;
  var d=new Date();
  d.setTime(1000*du);
  document.getElementById('du'+site_code).value=d.getDate()+'/'+(1+d.getMonth())+'/'+d.getFullYear();
  d.setTime(1000*au);
  document.getElementById('au'+site_code).value=d.getDate()+'/'+(1+d.getMonth())+'/'+d.getFullYear();

  if(templot!=''){
   lines=templot.split(',');
   for(i=0;i<lines.length;i++){
    d=lines[i].split(':');
    document.getElementsByName('tplotprix['+site_code+']['+d[0]+']')[0].value=d[1];
    document.getElementsByName('tpachatlotprix['+site_code+']['+d[0]+']')[0].value=d[2];
  }
}

calctemp(site_code);
calcmarge('t',site_code);

var div=document.getElementById('tempprice'+site_code);
div.style.display='block';
document.getElementById('tdedittemp'+site_code).appendChild(div);
}
function deltemp(id,site_code){
  if(confirm('Etes-vous sur de vouloir effacer ce prix temporaire ?')){
   var form=document.forms['formtemp_price_'+site_code];
   form.tpid.value=id;
   form.action2.value='deltempprice';
   form.submit();
 }
}
function closetemp(site_code) {
  var div=document.getElementById('tempprice'+site_code);
  div.style.display='none';
  document.body.appendChild(div);
}

var artlotid=0;
var artlotartcode='';
var artlotartid=0;
var artlotsite='';
function srcprix(txt,alid,artid,site_code){
  artlotid=alid;
  artlotartcode=txt;
  artlotartid=artid;
  artlotsite=site_code;
  ajaxrequest('marketplace/src.php?txt='+txt,srcprix_cb);
}
function srcprix_cb(httprequest){
  var xml=httprequest.responseXML.documentElement;
  for(i=0;i<xml.childNodes.length;i++){
    var node=xml.childNodes[i];
    if(node.nodeName=='prod' && node.getAttribute('artid')==artlotartid){
     document.getElementsByName('lotpaht['+artlotsite+']['+artlotid+']')[0].value=node.getAttribute('paht_'+artlotsite);
     document.getElementById('lotpattc_'+artlotsite+'_'+artlotid).innerHTML=round(node.getAttribute('paht_'+artlotsite)*(1+node.getAttribute('tva')/100),2)+' TTC';
     document.getElementsByName('lotprix['+artlotsite+']['+artlotid+']')[0].value=Number(node.getAttribute('price_'+artlotsite));
     document.getElementById('lotprixht_'+artlotsite+'_'+artlotid).innerHTML=round(node.getAttribute('price_'+artlotsite)/(1+node.getAttribute('tva')/100),2);
     document.getElementsByName('lotbarre['+artlotsite+']['+artlotid+']')[0].value=round((1+node.getAttribute('tva')/100)*node.getAttribute('strike_out_'+artlotsite),2);
     document.getElementById('lotbarreht_'+artlotsite+'_'+artlotid).innerHTML=Number(node.getAttribute('strike_out_'+artlotsite))+' HT';
     document.getElementsByName('lotecopartht['+artlotsite+']['+artlotid+']')[0].value=node.getAttribute('ecopartht');
     document.getElementById('lotecopart_'+artlotsite+'_'+artlotid).innerHTML=round(node.getAttribute('ecopartht')*(1+node.getAttribute('tva')/100),2);
     verifprix(false,artlotsite);
     return;
   }
 }
}
function switch_site(site_code){
  for(i=0;i<sites.length;i++){
   document.getElementById('tblprix'+sites[i]).style.display='none';
   document.getElementById('tab'+sites[i]).className='sitetab';
   document.getElementById('tbltempprices'+sites[i]).style.display='none';
   document.getElementById('tempprice'+sites[i]).style.display='none';
 }

 document.getElementById('tab'+site_code).className='sitetabon';
 document.getElementById('tblprix'+site_code).style.display='table';
 document.getElementById('tbltempprices'+site_code).style.display='table';
 highlight_marge(site_code);
}
function highlight_marge(site) {
  for(j=0;j<sites.length;j++)
   if(document.getElementById('calcmarge_'+sites[j])) document.getElementById('calcmarge_'+sites[j]).style.background='none';
 document.getElementById('calcmarge_'+site).style.background='#FFF1C6';
}
$fournisseurs_js
</script>
JS;



function spytable($artcode){
  global $DB_site2;
  $pc="";
  
  $article=$DB_site2->query_first("SELECT prix FROM article WHERE artcode='".$artcode."' AND active<>'2'");
  $requete=$DB_site2->query("SELECT h.* FROM history h WHERE h.artcode='".$artcode."' AND h.timestamp>".(time()-86400*7)." GROUP BY site ORDER BY price DESC");
  $tr="";
  while($data=$DB_site2->fetch_array($requete)){
    if(floatval($article['prix'])!=0) $pc=round(-100+100*$data['price']/$article['prix'],2)."%";
    $tr.="<tr><td>".$data['site']."</td><td".($data['price']<$article['prix']?" style=\"color:red;\"":"").">".number_format($data['price'],2,'.','')."<br>$pc</td></tr>";
  }
  $spy="<table name=\"spyprice\" class=\"spy\">";
  $spy.=$tr;
  $spy.="<tr><td>MCAV</td><td>".number_format($article['prix'],2,',','')."</td></tr>
  </table>";
  if($DB_site2->num_rows($requete)==0) $spy="-";

  return $spy;
}

?>
