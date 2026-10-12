<?php
require "admin_global.php";
include "./header.php";
include "./menu.php";

$option = $option_marque ="<option></option>";

//liste des departements
$dep_liste = $DB_site2->query("SELECT code, nom_departement FROM departement ORDER BY code ASC");
while ($result = $DB_site2->fetch_array($dep_liste)) {
  $selected = "";
  if(isset($_POST['dep']) && $_POST['dep'] == $result['code']) $selected = "selected";
	$option .= "<option value='".$result['code']."' $selected>".$result['code']." ".$result['nom_departement']."</option>";
}

//liste des marques
$marque_liste = $DB_site2->query("SELECT marqueid, libelle FROM marque ORDER BY libelle ASC");
while ($result = $DB_site2->fetch_array($marque_liste)){
  $selected = "";
  if(isset($_POST['marque']) && intval($_POST['marque']) == $result['marqueid']) $selected = "selected";
	$option_marque .="<option value='".$result['marqueid']."' $selected>".$result['libelle']."</option>";
}
if(isset($_POST['sel_artcode']) && $_POST['sel_artcode'] != ''){
  $search = $_POST['sel_artcode'];
  $artid = mysqli_real_escape_string($DB_site2->link_id,$_POST['sel_art']);
}
else{
  $search = "Rechercher";
  $artid = '';
}


?>
<h2>Géolocalisation client pro</h2>
<script type="text/javascript">
	function srcart(txt){	        
	      if(txt.length<4) return;
	      ajaxrequest('marketplace/src.php?active=1&txt='+txt,srcartcb);
	  }
	function srcartcb(httprequest){
	    var xml=httprequest.responseXML.documentElement;
	        var html='';
	        if(xml.getElementsByTagName('prod')[0]){
	            html+="<table width='100%' style='border:solid thin #808080;'>";
	            for(i=0;i<xml.getElementsByTagName('prod').length;i++){
	                var description=xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
	                var id=xml.getElementsByTagName('prod')[i].getAttribute('id');
	                var price=xml.getElementsByTagName('prod')[i].getAttribute('price');
	                var buyprice=xml.getElementsByTagName('prod')[i].getAttribute('buyprice');
	                var artid=xml.getElementsByTagName('prod')[i].getAttribute('artid');
			  		var visible=xml.getElementsByTagName('prod')[i].getAttribute('visible');
			 		color='black';
			 		if(visible=='0') color='red';
	                    html+="<tr><td><img src='../configurations/<?php echo $host; ?>/images/produits/"+artid+".jpg' width='30'></td><td style='border-bottom:solid thin #cccccc;color:"+color+";'>"+id+"</td><td style='border-bottom:solid thin #cccccc;'>"+description+"</span></td><td style='border-bottom:solid thin #cccccc;'><a href=javascript:selectart('"+artid+"','"+id+"')>Sélectionner</a></td></tr>";
	                }
	                html+="</table>";
	        }else
	            html="Pas de résultat";

	    document.getElementById('art_result').innerHTML=html;
	    document.getElementById('art_result').style.display='block';
	}
	function selectart(artid,artcode){
	    document.getElementsByName('sel_art')[0].value=artid;
	    if(artcode=='') document.getElementById('sel_artname').innerHTML='';
	    document.getElementById('sel_artid').value = artcode;
      document.getElementsByName('sel_artcode')[0].value = artcode;
	    
	    document.getElementById('art_result').style.display='none';
	}
	function verif(){
		var err = '';
		if(document.getElementsByName('sel_art')[0].value == '' && document.getElementsByName('marque')[0].value == '' ) err +='Aucun article n\'a été choisi !'; 
		if(err == ''){
			document.forms.form_geo.submit();
		}else{
			alert(err);
		}
	}
</script>
<!-- Formulaire de recherche d'articles -->
<h3 style="color: red;font-weight: bold;text-decoration: underline;text-transform: uppercase;">Ne pas mettre "ACI-" dans la recherche !!</h3>

<div class="col-xs-12 no-padding" id="form_geo">
  <div class="col-xs-3">
    Article recherché : <input type="text" value="<?=$search; ?>" id="sel_artid" onclick="this.value=''" onkeyup="srcart(this.value)" size="10" required>
  </div>
  <div id="art_result" style="display:none;z-index:999;position: absolute;top: 40px;background-color: white;"></div>
	<form name="form_geo" method="post">
		<div class="col-xs-9 no-padding">
			<input type="hidden" name="geo_search" value="1">
			<input type="hidden" name="sel_art" value="<?=$artid; ?>">
      <input type="hidden" name="sel_artcode" value="<?=$search?>">
			<div class="col-xs-3 no-padding">Département <select name="dep" style="width: 100px;"><?=$option; ?></select></div>
			<div class="col-xs-3 no-padding">Marque <select name="marque" style="width: 100px;"><?=$option_marque; ?></select></div>
      <div class="col-xs-3 no-padding">Années 
        <select name="annee">
          <option value="3">3 ans</option>
          <option value="5">5 ans</option>
          <option value="100">+ 5ans</option>
        </select>
      </div>
      <div class="col-xs-2 no-padding">
        + Adresse livr <input type="checkbox" name="adresse">
      </div>
			<div class="col-xs-1 no-padding"> <input type="checkbox" name="pro" checked> Pro</div>
		</div>
	</form>
</div>
<div class="col-xs-offset-11 col-xs-1"> <button onclick="verif();" style="width: 100%;">Find</button></div>
<br><br>


<?php

if(isset($_POST['geo_search'])){

  if(intval($_POST['annee']) == 100){
    $date = '';
  }else{
    $date = strtotime('-'.$_POST['annee'].' year');
    $date = " AND f.heurefacture > '".$date."'";
  }
  
	$departement = mysqli_real_escape_string($DB_site2->link_id,$_POST['dep']);
	$marqueid = mysqli_real_escape_string($DB_site2->link_id,$_POST['marque']);


	$sql  = "SELECT f.facturid, f.nom, passefacture,datefacture, heurefacture, datelivraison, u.mail, f.prixgestion, f.prixport, ef.$libelle, f.valide,  mp.$libelle as moyenpaiement,f.commercial,f.devise, cadeauid, f.moyenid, f.prix_contre_remboursement, f.codenumerofacture, f.numerofacture, f.suivi2, u.nom AS nom2, f.userid, f.advdate1, f.advdate2, f.advdate3, f.provenance, f.infotranspo, f.provenanceid, f.mp_facturid, f.commission, u.vip, u.codepostal, u.ville, u.paysid, commercial_attribution, f.avis";
  $sql .= " FROM facture f";
	$sql .= " INNER JOIN utilisateur u ON f.userid = u.userid";
	$sql .= " INNER JOIN etatfacture ef ON f.valide = ef.valudeid";
	$sql .= " INNER JOIN moyenpaiement mp ON f.moyenid = mp.moyenid";
	$sql .= " INNER JOIN lignefacture lf ON f.facturid=lf.factureid";
	$sql .= " INNER JOIN article_marque am ON lf.artid=am.artid";
	$sql .= " WHERE f.valide IN(5,8)";
  $sql .= " AND f.provenance NOT LIKE 'sav'";
  $sql .= $date;

  //filtres de recherche
	if($artid != '') $sql .= " AND lf.artid=".$artid;
	if($departement !=''){
    if(isset($_POST['adresse']))
      $sql .= " AND ( u.codepostal LIKE '".$departement."%' OR f.codepostal LIKE '".$departement."%')";
    else
      $sql .= " AND u.codepostal LIKE '".$departement."%'";
  } 
	if($marqueid != '') $sql .= " AND am.marqueid = '".$marqueid."'";
	if(isset($_POST['pro'])) $sql .= " AND f.raisonsociale != ''";

  $sql .= " GROUP BY f.facturid";
	$sql .= " ORDER BY datefacture DESC, f.facturid DESC";

  //affichage des résultats de la recherche
	echo "<table class=\"table_factures\">";
    echo "<tr>
           <td class=\"td_head_factures\"><b>N° Fac</b></td>
           <td class=\"td_head_factures\"><b>N° Cde</b></td>
           <td class=\"td_head_factures\"><b>Date</b></td>
           <td class=\"td_head_factures\"><b>Montant TTC</b></td>
           <td class=\"td_head_factures\" width=\"30\"><b>Moyen paiement</b></td>
           <!--<td class=\"td_head_factures\"><b>Etat</b></td>-->             
           <td class=\"td_head_factures\"><b>ACTIONS</b></td>
           <td class=\"td_head_factures\"><b>CDE fournisseur</b></td>
	       <td class=\"td_head_factures\"><b>Suivi</b></td>
	       <td class=\"td_head_factures\"><b>Date livraison</b></td>
	       <td class=\"td_head_factures\"><b>1. Départ fournisseur</b></td>
	       <td class=\"td_head_factures\"><b>2. Livraison intermédiaire</b></td>
	       <td class=\"td_head_factures\"><b>3. Livraison client</b></td>
         </tr>";

	$result = $DB_site2->query($sql);
	while($facture = $DB_site2->fetch_array($result)){
		$remboursement = 0 ;
        $rowalt = "td_factures_".getrowbg();
        list ($annee, $mois, $jour) = explode("-", $facture[datefacture]);
        $lignefactures=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid = '$facture[facturid]'");
        $total_facture = 0 ;
        while ($lignefacture=$DB_site2->fetch_array($lignefactures)) {
            $total_facture += ($lignefacture[prix] * $lignefacture[qte]) ;
            if ($lignefacture[remboursement]) $remboursement += $lignefacture[prix] * $lignefacture[qte] ;
        }
        $total_facture=$DB_site2->query_first("SELECT SUM(prix*qte) FROM lignefacture WHERE factureid='$facture[facturid]'");
        
        // remise sur tout le catalogue
        $fact=$DB_site2->query_first("SELECT pctremise FROM facture WHERE facturid = '$facture[facturid]'");
        if ($fact[0] != 0) $total_facture[0] = $total_facture[0] * (1 - $fact[0] / 100) ;
		
        // Bon de réduction 
        $prixcadeau = 0;
        if (in_array( "13", $modules ) && $facture[cadeauid] > 0 ) {
        	$cadeau=$DB_site2->query_first("SELECT cadeauid, montantcadeau FROM facture WHERE facturid = '$facture[facturid]'") ;
          	if($cadeau[cadeauid] != "") $prixcadeau = $cadeau[montantcadeau] ;
        }

        // remise
        if (in_array ("3", $modules)) {
        	$prixremise = 0 ;
          	$remise_facture = $DB_site2 -> query_first ("SELECT r.remiseid, r.typeremise, r.valeur, r.pctremise, f.montantremise 
                                                       FROM facture f inner join remise r on (f.remiseid = r.remiseid)
                                                       WHERE f.facturid = '$facture[facturid]'" );
          
          	if ($remise_facture[remiseid] != "") $prixremise = $remise_facture[montantremise] ;
        } // fin des remises

        // contre remboursement
        if ($facture[moyenid] == '3')
            $prix_contre_remb = $facture[prix_contre_remboursement] ;
        else
            $prix_contre_remb = 0 ;
                
        $total_facture = number_format($total_facture[0] + $facture[prixgestion] + $prix_contre_remb + $facture[prixport] - $prixcadeau -$prixremise, 2, ',', ' ')." ".$devises->symbole($facture['devise']);
        list($annee, $mois, $jour) = explode( "-", $facture[datefacture] );

	    if ($remboursement > 0)
	    	$remboursement = "<br><font color=\"#E5840F\">-".number_format($remboursement, 2, ',', ' ')." €</font>" ;
	    else
	        $remboursement = "" ;
	           
        $vip=usericon($facture['vip'],20);
        if($facture['paysid']==57)
            $ville=substr($facture['codepostal'],0,2)." ".$facture['ville'];
        else
            $ville=$facture['ville'];

        echo "<tr Height=\"25\">";

		if ($facture[numerofacture] > 556500) {
            echo "<td class=\"$rowalt\" align=\"center\">$facture[codenumerofacture]$facture[numerofacture]</td>";
        }else{
            echo "<td class=\"$rowalt\" align=\"center\"> -- </td>";               
        }


		echo "<td class=\"$rowalt\">$facture[facturid]<br>$facture[nom]<br><a href=\"utilisateur.php?action=editer&userid=$facture[userid]\" target=\"_blank\" style=\"color:#404040;\">$facture[unom]</a>$vip $ville</td>";
        echo "<td class=\"$rowalt\" align=\"center\">".date("d/m/Y H:i",$facture['heurefacture'])."</td>";
        echo "<td class=\"$rowalt\" align=\"right\" nowrap>$total_facture $remboursement</td>";

    if ($facture[valide] == 0) 
	    { // non traité
	      $couleurlien="#000000";
	    }
	  if ($facture[valide] == 1 || $facture[valide]==7) 
	    { // Validée
	      $couleurlien="#0000FF";
	    }
	  if ($facture[valide] == 2) 
	    { // refus commercant
	      $couleurlien="#FF0000";
	    }
	  if ($facture[valide] == 3) 
	    { // refus carte bleue
	      $couleurlien="#FF0000";
	    }
	  if ($facture[valide] == 4) 
	   { // abandon internaute
	      $couleurlien="#FF0000";
	    }
	  if ($facture[valide] == 5 || $facture[valide]==8) 
	    { // expédié
	      $couleurlien="#00FF00";
	   }

	  $suivienvoi="";
    $advdate1=$advdate2=$advdate3="";
    $efl=array();
    $rappeltxt="";    

    if(in_array($facture['valide'],array(1,5,7,8))) {

      $r=$DB_site2->query("SELECT * FROM envoi_fournisseur_ligne ef, lignefacture l WHERE ef.type='fourn' AND l.factureid=".$facture['facturid']." AND ef.ligne_facture_id=l.lignefactureid");
      $fourncount=$DB_site2->num_rows($r);
      while($envoifourn=$DB_site2->fetch_array($r)){ 
      	$efl[$envoifourn['lignefactureid']]['in']['fourn']=$envoifourn['indate']; $efl[$envoifourn['lignefactureid']]['out']['fourn']=$envoifourn['outdate']; 
      }
       
      $r=$DB_site2->query("SELECT * FROM envoi_fournisseur_ligne ef, lignefacture l WHERE ef.type='trspt' AND l.factureid=".$facture['facturid']." AND ef.ligne_facture_id=l.lignefactureid");
      $trsptcount=$DB_site2->num_rows($r);

      while($envoitrspt=$DB_site2->fetch_array($r)){ 
        $efl[$envoitrspt['lignefactureid']]['in']['trspt']=$envoitrspt['indate']; $efl[$envoitrspt['lignefactureid']]['out']['trspt']=$envoitrspt['outdate']; 
      }
     
      $r=$DB_site2->query("SELECT * FROM envoi_fournisseur_ligne ef, lignefacture l WHERE ef.type='sls' AND l.factureid=".$facture['facturid']." AND ef.ligne_facture_id=l.lignefactureid");
      $slscount=$DB_site2->num_rows($r);

      while($envoisls=$DB_site2->fetch_array($r)){ 
        $efl[$envoisls['lignefactureid']]['in']['sls']=$envoisls['indate']; $efl[$envoisls['lignefactureid']]['out']['sls']=$envoisls['outdate']; 
      }
     
      $lignenb=$DB_site2->query_first("SELECT count(*) FROM lignefacture WHERE factureid=".$facture['facturid']);
      $suivienvoi="<div style=\"color:".envoicolor($fourncount,$lignenb[0]).";\">Fournisseur</div><div style=\"color:".envoicolor($trsptcount,$lignenb[0]).";\">Transport</div><div style=\"color:".envoicolor($slscount,$lignenb[0]).";\">Installation</div>";

      calcdates($efl);
             
      $rq_rappel=$DB_site2->query_first("SELECT * FROM suivi_commande WHERE facturid=$facture[facturid] ORDER BY date DESC LIMIT 1");//recupération de la table suivi_commande ou lon va recupéré le commercial qui sera utilé pour les "passé par"

      if($rq_rappel['rappel']!=0) $rappeltxt="<br><br><font color=\"".($rq_rappel['rappel']>$today && $rq_rappel['rappel']<$tomorrow?"red":"")."\">$rq_rappel[pour] rappel le ".date("d/m/Y à H:i",$rq_rappel['rappel'])."</font>";

      if($rq_rappel['suivi']!="") $rappeltxt.="<img src=\"../configurations/".$host."/images/templates/icone_info.gif\" onmouseover=\"document.getElementById('div$facture[facturid]').style.visibility='visible';\" onmouseout=\"document.getElementById('div$facture[facturid]').style.visibility='hidden';\" width=\"20\" align=\"absmiddle\"><div id=\"div$facture[facturid]\" class=\"popup\">".stripslashes($rq_rappel['suivi'])."</div>";
    }
    $devis=$DB_site2->query_first("SELECT * FROM envoi_devis WHERE facturid=$facture[facturid] ORDER BY date DESC");

    if($devis['date']!="") 
     	$rappeltxt.="<img src=\"images/devis.png\" width=\"24\"><br>".date("d/m/Y à H:i",$devis['date'])." par ".($devis['utilisateur']!=""?$devis['utilisateur']:$rq_rappel['commercial']);

    $passepar="Passé par ".($facture['commercial']==""?$rq_rappel['commercial']:$facture['commercial']);

    if($facture['provenance']=="mp") $facture[moyenpaiement]=$marketplaces[$facture['provenanceid']];

    echo "<td class=\"$rowalt\" align=\"center\" nowrap width=\"10%\">$facture[moyenpaiement]<br><font color=\"$couleurlien\">$facture[$libelle]</font><br>$passepar</td>
        <td rowspan=\"2\" class=\"$rowalt\" align=\"center\" width=\"30%\"><div  align=\"center\">
        <a href=\"facture.php?action=editer&facturid=$facture[facturid]&oldaction=rechercher\" target=\"_blank\">Editer</a>";
    if ($facture[numerofacture] > 556500)
      echo "<br><br><a href=\"https://$host/facture2.php?passession=oui&facturid=$facture[facturid]&passe=$facture[passefacture]&langue=FR\" target=\"_blank\">Facture</a>" ;
    else{
      if ($facture[valide] == 1) 
          echo "<br><br><a href=\"https://$host/facture2.php?passession=oui&facturid=$facture[facturid]&passe=$facture[passefacture]&cde=1&langue=FR\" target=\"_blank\">Commande</a>" ;
      elseif ($facture[valide] ==0) 
          echo "<br><br><a href=\"facture.php?action=cacher&facturid=$facture[facturid]&oldaction=rechercher\" target=\"_self\">Supprimer</a>" ;
      else 
          echo "<br><br>" ;
    }

    if($facture['valide']==0) echo "<br><a href=\"javascript:popdevis($facture[facturid])\">Envoyer le devis</a>";
    $datelivraison=($facture['datelivraison']>0?date("d/m/Y",$facture['datelivraison']):"?");
    echo "</div>
    </td><td rowspan=\"2\" class=\"$rowalt\" style=\"line-height:18px; text-align:center;\">$suivienvoi </td>
    <td rowspan=\"2\" class=\"$rowalt\">$rappeltxt</td>
    <td rowspan=\"2\" class=\"$rowalt\"".($facture['datelivraison']<=time()?" style=\"color:red;\"":"").">".$datelivraison."</td>
    <td rowspan=\"2\" class=\"$rowalt\">$advdate1</td>
    <td rowspan=\"2\" class=\"$rowalt\">$advdate2</td>
    <td rowspan=\"2\" class=\"$rowalt\">$advdate3</td></tr>" ;

    $lignefactures=$DB_site2->query("SELECT lf.*,a.artcode_eurocave FROM lignefacture lf LEFT JOIN article a ON(a.artid=lf.artid) WHERE lf.factureid = '$facture[facturid]'");
    $articles="";
    while ($lignefacture=$DB_site2->fetch_array($lignefactures)) {
      if($lignefacture['prix']>0) $articles.=$lignefacture['qte']." ".$lignefacture['artcode']." (".$lignefacture['artcode_eurocave'].")<br>";
    }
    echo "<tr><td colspan=\"5\" class=\"$rowalt\">$articles</td></tr>";
	}
	

	echo "</table><br><br><br>";	

}


function envoicolor($nbprod,$totalprod){
      if($nbprod==0 || $nbprod=="") return "red";
      if($nbprod<$totalprod) return "#FFAA00";
      if($nbprod>=$totalprod) return "green";
      return "red";
}

function calcdates($efl){
  global $today;
  global $advdate1,$advdate2,$advdate3;

  if($efl==false) return "";
  foreach($efl as $lfid=>$dates){

  if($dates['in']['fourn']>0)
      $advdate1.="<span".($dates['in']['fourn']<=$today?" style=\"color:red;\"":"").">".date("d/m/Y",$dates['in']['fourn'])."</span><br>";
  else
      $advdate1.="-<br>";

  if(intval($dates['in']['sls'])>0){
      $advdate2.="<span".($dates['in']['sls']<=$today?" style=\"color:red;\"":"").">".date("d/m/Y",$dates['in']['sls'])."</span><br>";
  }elseif(intval($dates['in']['trspt'])>intval($dates['in']['fourn'])){
      $advdate2.="<span".($dates['in']['trspt']<=$today?" style=\"color:red;\"":"").">".date("d/m/Y",$dates['in']['trspt'])."</span><br>";
  }

  if(intval($dates['out']['sls'])>0){
      $advdate3.="<span".($dates['out']['sls']<=$today?" style=\"color:red;\"":"").">".date("d/m/Y",$dates['out']['sls'])."</span><br>";
  }elseif(intval($dates['out']['trspt'])>0){
      $advdate3.="<span".($dates['out']['trspt']<=$today?" style=\"color:red;\"":"").">".date("d/m/Y",$dates['out']['trspt'])."</span><br>";
  }elseif(intval($dates['out']['fourn'])>0){
      $advdate3.="<span".($dates['out']['fourn']<=$today?" style=\"color:red;\"":"").">".date("d/m/Y",$dates['out']['fourn'])."</span><br>";
  }else{
      $advdate3.="-<br>";
  }
  }
}
