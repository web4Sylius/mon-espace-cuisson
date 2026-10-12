<?php


$ecopart = $article[ecopart] ;
$ecopartht = $article[ecopartht] ;
$tauxtva = $article[tauxchoisi] ;
$delailivraison=$article[delailivraison];
$date_appro=($article[date_appro]>0?date("d/m/Y",$article[date_appro]):"");
$pattc=0;
$prixconseille=0;
$prixconseilleht = 0 ;
$typeoffre='';
$chiffreoffre='';
$remisePack=0;
$remisePackHT=0;
$prixtrspt=0;


$stylebarre=$styleecopart=$styleecopart=$stylepaht="";

$totalprix=$totalachat=$totalecopartht=$totalecopart=$totalbarre=0;
$tdartcode=$tdecopartht=$tdecopart=$tdprixht=$tdprix=$tdpaht=$tdbarre=$tdcalc="";
//$tdtempprix=$tdtemppaht=$$tdtempbarre="";
$lartids=array();
$lotartcount=0;
$mainartro="";

$requete=$DB_site2->query("SELECT * FROM article_pack WHERE prix>0 AND packid=$artid ORDER BY prix DESC");
if($DB_site2->num_rows($requete)>0){
  $mainartro="readonly";
  while($lart=$DB_site2->fetch_array($requete)){
      $typeoffre=explode('-', $lart['offre'])[0];
      $chiffreoffre=explode('-', $lart['offre'])[1];
      
      $larticle=$DB_site2->query_first("SELECT a.artid,a.artcode,a.artcode_eurocave,a_s.prixconseilleht,a_s.prix,a_s.tauxchoisi,af.prix AS paht,af.ecopartht,af.trspt,af.reappro,a.delailivraison
      FROM article a 
      LEFT JOIN article_fournisseur af USING(artid) 
      LEFT JOIN article_site a_s USING(artid) 
      WHERE af.principal LIKE '%base%' AND a_s.site_code='base' AND a.artid=".$lart['artid']);
      
      $tvapc=$txfrance[$larticle['tauxchoisi']];
      $date_approArt=($larticle[date_appro]>0?date("d/m/Y",$larticle[date_appro]):"");
      $totalprix+=($lart['qte']*$larticle['prix']);
      $totalachat+=($lart['qte']*$larticle['paht']);
      $totalecopartht+=($lart['qte']*$larticle['ecopartht']);
      $totalecopart+=($lart['qte']*$larticle['ecopartht']*(1+$tvapc/100));
      $totalbarre+=($lart['qte']*(($larticle['prixconseilleht']*(1+$tvapc/100))+$lart['prixoption']));
      $prixtrspt+=floatval($larticle['trspt']);
      $lartids[]=$lart['id'];
      $artids[$lart['artid']]=$lart['qte'];
      $pattc += round($lart['qte']*$larticle['paht']*(1+$tvapc/100),3);
      $prixconseille += round($larticle['prixconseilleht']*(1+$tvapc/100),3);
      $prixconseilleht += $larticle['prixconseilleht'];
      $tdartcode.="<td class=\"lot\" style=\"max-width:150px;vertical-align:top;\"><img src=\"../configurations/$host/images/produits/$lart[artid].jpg\" style=\"max-height:50px;\"><br><input type=\"hidden\" name=\"packqte[$lart[id]]\" value=\"$lart[qte]\">".$lart['qte']." x <a href=\"ajout_produit.php?artid=".$larticle['artid']."&prec=modif\" target=\"_blank\">".$larticle['artcode']."</a><br>(".$larticle['artcode_eurocave'].")</td>";
      $tdecopartht.="<td class=\"lot\"><input type=\"text\" name=\"packecopartht[$lart[id]]\" value=\"".($larticle['ecopartht'])."\" class=\"roinput\" readonly></td>";
      $tdecopart.="<td class=\"lot\"><input type=\"text\" name=\"packecopart[$lart[id]]\" value=\"".round($larticle['ecopartht']*(1+$tvapc/100),3)."\" class=\"roinput\" readonly></td>";
      $tdtrspt.="<td class=\"lot\"><input type=\"text\" name=\"packtrspt[$lart[id]]\" value=\"".round($larticle['trspt'],3)."\" class=\"roinput\" readonly></td>";
      $tddelailiv.="<td class=\"lot\"><input type=\"text\" name=\"packdelailiv[$lart[id]]\" value=\"".$larticle['delailivraison']."\" class=\"roinput\" readonly></td>";
      $tddatereap.="<td class=\"lot\"><input type=\"text\" name=\"packdatereap[$lart[id]]\" value=\"".$date_approArt."\" class=\"roinput\" readonly></td>";
      $tdprixht.="<td class=\"lot\"><input type=\"text\" name=\"packprixht[$lart[id]]\" value=\"".round($lart['prix']/(1+$tvapc/100),3)."\" class=\"roinput small\" readonly></td>";
      $tdprix.="<td class=\"lot\"><input type=\"text\" name=\"packprix[$lart[id]]\" value=\"".($lart['prix'])."\" size=\"7\" class=\"roinput\" autocomplete=\"off\" readonly></td>";
      $tdpaht.="<td class=\"lot\"><input type=\"text\" name=\"packpaht[$lart[id]]\" value=\"".$larticle['paht']."\" size=\"7\" class=\"roinput\" autocomplete=\"off\" readonly><br><input type=\"text\" name=\"packpattc[$lart[id]]\" value=\"".round($larticle['paht']*(1+$tvapc/100),3)."\" size=\"7\" class=\"roinput small\" autocomplete=\"off\" readonly>&euro; TTC</td>";
      $tdbarre.="<td class=\"lot\"><input type=\"text\" name=\"packbarre[$lart[id]]\" value=\"".round(($larticle['prixconseilleht']*(1+$tvapc/100))+$lart['prixoption'],3)."\" class=\"roinput\" readonly> &euro; TTC<br><input type=\"text\" name=\"packbarreht[$lart[id]]\" value=\"".round($larticle['prixconseilleht']+($lart['prixoption']/(1+$tvapc/100)),3)."\" class=\"roinput small\" readonly> &euro; HT</td>";

    }


    switch ($typeoffre) {
	  case 'euro':
		  $remisePack=$chiffreoffre;
		  $remisePackHT=round($chiffreoffre/(1+$txfrance[1]/100),3);
		  break;
	  case 'pourcent':
		  $remiseEuro=$totalprix-($totalprix*((100-$chiffreoffre)/100));
		  $remisePack=$remiseEuro;
		  $remisePackHT=round($remiseEuro/(1+$txfrance[1]/100),3);
		  break;
	  case 'chkdo':
		  $remisePack=0;
		  break;
	  case 'pdtoffert':
		  $remisePack=0;
		  $remisePackHT=0;
		  $larticle=$DB_site2->query_first("SELECT ap.id,ap.qte,a.* FROM article_pack ap INNER JOIN article a USING(artid) WHERE ap.prix=0 AND packid=$artid;");
		  
		  $tdartcode.="<td class=\"kdos\" style=\"max-width:150px;vertical-align:top;\"><img src=\"../configurations/$host/images/produits/$larticle[artid].jpg\" style=\"max-height:50px;\"><br>".$larticle['qte']." x <a href=\"ajout_produit.php?artid=".$larticle['artid']."&prec=modif\" target=\"_blank\">".$larticle['artcode']."</a><br>(".$larticle['artcode_eurocave'].")</td>";
		  $tdecopartht.="<td class=\"kdos\"><input type=\"text\" value=\"".($larticle['ecopartht'])."\" class=\"roinput\" readonly></td>";
		  $tdecopart.="<td class=\"kdos\"><input type=\"text\" value=\"".round($larticle['ecopartht']*(1+$tvapc/100),3)."\" class=\"roinput\" readonly></td>";
		  $tdtrspt.="<td class=\"kdos\"><input type=\"text\" value=\"".round($larticle['trspt']*(1+$tvapc/100),3)."\" class=\"roinput\" readonly></td>";
		  $tddelailiv.="<td class=\"kdos\"><input type=\"text\" value=\"".$larticle['delailivraison']."\" class=\"roinput\" readonly></td>";
		  $tddatereap.="<td class=\"kdos\"><input type=\"text\" value=\"".$date_approArt."\" class=\"roinput\" readonly></td>";
		  $tdprixht.="<td class=\"kdos\"><input type=\"text\" value=\"0\" class=\"roinput small\" readonly></td>";
		  $tdprix.="<td class=\"kdos\"><input type=\"text\" value=\"0\" size=\"7\" class=\"roinput\" autocomplete=\"off\" readonly></td>";
		  $tdpaht.="<td class=\"kdos\"><input type=\"text\" value=\"".$larticle['paht']."\" size=\"7\" class=\"roinput\" autocomplete=\"off\" readonly><br><input type=\"text\" name=\"packpattc[$lart[id]]\" value=\"".round($larticle['paht']*(1+$tvapc/100),3)."\" size=\"7\" class=\"roinput small\" autocomplete=\"off\" readonly>&euro; TTC</td>";
		  $tdbarre.="<td class=\"kdos\"><input type=\"text\" value=\"".round(($larticle['prixconseilleht']*(1+$tvapc/100)),3)."\" class=\"roinput\" readonly> &euro; TTC<br><input type=\"text\" value=\"".round($larticle['prixconseilleht'],3)."\" class=\"roinput small\" readonly> &euro; HT</td>";
		  
		  $totalachat+=($larticle['qte']*$larticle['paht']);
		  $pattc+=round($larticle['qte']*$larticle['paht']*(1+$tvapc/100),3);
		  $prixconseille+=$larticle['prixconseilleht']*(1+$tvapc/100);
		  $prixconseilleht+=$larticle['prixconseilleht'];
		  break;
    }

    $totalprixht=$totalprix/(1+$txfrance[1]/100);
    $lotartcount=$DB_site2->num_rows($requete);

}

$remise=0;
$remisePPIEuro=0;
if($prixconseille>0) {
	$remisePPIEuro=number_format($prixconseille-$totalprix,2);
	$remise=number_format((100*($remisePPIEuro))/$prixconseille,2);
}

$remisePack=number_format($remisePack,3);
$remisePackHT=number_format($remisePackHT,3);
$totalecopart=number_format($totalecopart,2);

$contenu_onglet.=<<<HTML
<h3>Prix</h3>

<div id="prixdiv">
	
	<input type="hidden" name="tauxtva" value="1">
	<table id="tblprix">
	<tr>
	  <td></td>
	  <td>$article[artcode]</td>
	  $tdartcode
	</tr>
	<tr>
	  <td>Prix d'achat HT : </td>
	  <td>
	    <input type="text" name="paht" value="$totalachat" style="$stylepaht" size="7" autocomplete="off" readonly class="roinput">
	    <br><input type="text" name="pattc" value="$pattc" size="7" autocomplete="off" readonly class="roinput small"> &euro; TTC
	  </td>
	  $tdpaht
	</tr>
	<tr>
	  <td>Eco-part HT : </td>
	  <td><input type="text" name="ecopartht" value="$totalecopartht" style="$styleecopartht" size="7" onchange="setmodif();" autocomplete="off" class="roinput" readonly></td>
	  $tdecopartht
	</tr>
	<tr>
	  <td>Eco-part TTC : </td>
	  <td><input type="text" name="ecopart" value="$totalecopart" style="$styleecopart" size="7" onchange="setmodif();" autocomplete="off" $mainartro class="roinput" readonly></td>
	  $tdecopart
	</tr>
	<tr>
	  <td>Garantie :</td>
	  <td id="magar"></td>
	</tr>
	<tr>
	  <td>Trspt frn/emballage : </td>
	  <td><input type="text" name="prixtrspt" value="$prixtrspt" size="7" style="background-color : rgb(222, 222, 222);border: 0px solid;" autocomplete="off" readonly></td>
	  $tdtrspt
	</tr>
	<tr>
	  <td>Transport :</td>
	  <td id="matrspt"></td>
	</tr>
	<tr>
	  <td>Délai livraison : </td>
	  <td><input type="text" name="delailivraison" size="7" style="background-color : rgb(222, 222, 222);border: 0px solid;" value="$delailivraison" autocomplete="off" readonly></td>
	  $tddelailiv
	</tr>
	<tr>
	  <td>Date réappro : </td>
	  <td><input type="text" name="date_appro" size="8" style="background-color : rgb(222, 222, 222);border: 0px solid;" value="$date_appro" autocomplete="off" readonly></td>
	  $tddatereap
	</tr>
	<tr>
	  <td>Prix vente HT : </td>
	  <td><input type="text" name="prixht" value="$totalprixht" id="prixht" size="7" class="roinput" readonly></td>
	  $tdprixht
	</tr>
	<tr>
	  <td>Prix vente TTC : </td>
	  <td><input type="text" name="prix" value="$totalprix" id="prix" style="font-weight:bold;" size="7" autocomplete="off" class="roinput" $mainartro readonly></td>
	  $tdprix
	</tr>
	<tr>
	  <td>Marge HT : </td>
	  <td id="marge"></td>
	</tr>
	<tr>
	  <td>Remise pack : </td>
	  <td>
	    <input type="text" name="remisePack" value="$remisePack" id="remisePack" size="7" autocomplete="off" class="roinput" $mainartro readonly>
	    <br><input type="text" name="remisePackHT" value="$remisePackHT" id="remisePackHT" size="7" autocomplete="off" class="roinput small" $mainartro readonly> &euro; HT
	  </td>
	</tr>
	<tr>
	  <td>Prix conseillé TTC : </td>
	  <td>
	    <input type="text" name="prixconseille" value="$prixconseille" style="$stylebarre" size="7" autocomplete="off" class="roinput" readonly>
	    <br><input type="text" name="prixconseilleht" value="$prixconseilleht" size="7" autocomplete="off" class="roinput small" readonly> &euro; HT
	  </td>
	  $tdbarre
	</tr>
	<tr>
	  <td>Remise PPI : </td>
	  <td>
	    <span id="remise">$remise</span> %
	    <br><span id="remisePPIEuro">$remisePPIEuro</span> ¤
	  </td>
	</tr>
	<!--<tr>
	  <td></td>
	  <td><input type="button" value="Calcul pack" onclick="calcmarge();"></td>
	  $tdcalc
	</tr>-->
	</table>
</div>
HTML;

$js="var artcaract=new Array();\n";
$caracts=$DB_site2->query("SELECT * FROM article_caractval ac  WHERE ac.artid=".$artid);
while($caract=$DB_site2->fetch_array($caracts))
    $js.="artcaract[".$caract['caractvalid']."]=true;\n";


$pack=new pack($DB_site2);
$stats=$pack->stats($artid);
$stats['total']=number_format($stats['total'],2);
    
$contenu_onglet .= <<<HTML
<div class="clear" />

<div>
    <h4>Stats</h4>
    Nombre de devis : $stats[devis]
    <br>Nombre de ventes : $stats[cmd]
    <br>Total HT : $stats[total]
</div>
<div class="clear"></div>
</form>
HTML;


$frais_port=new frais_port($DB_site2,$artids,57,$totalprix,$totalachat,"base");

$garantie=0;
$lesGaranties=DAOgarantie::loadMaxGarantieArtByPays($DB_site2,'base','FR',57,$artid);
foreach ($lesGaranties as $laGarantie) {
	$garantie+=$laGarantie->paht;
}

$js.="var prixport=".round($frais_port->prixht,2)."
var achatport=".floatval($frais_port->paht).";
var garantie=".floatval($garantie).";
calcmarge();";

?>