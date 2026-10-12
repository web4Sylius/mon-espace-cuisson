<?php

// GARANTIES
$recup='';
$facture=$DB_site2->query_first("SELECT * FROM facture WHERE facturid=$facturid");

print "<div id=\"tabsls\" style=\"".($_GET['type']!="sls"?"visibility:hidden; position:absolute;":"")."\">
<h3><img src=\"images/carton.png\" width=\"25\" align=\"absmiddle\"> Installateur de matériel SLS</h3>
<form name=\"form3\" action=\"facture.php?action=editer&facturid=".$facturid."\" method=\"post\" enctype=\"multipart/form-data\" style=\"position: relative;\">
<div class=\"loader-adv\"><img src=\"".BACK_URL."/images/loader.gif\" class=\"loading\"></div>
<input type=\"hidden\" name=\"actionadv\" value=\"advfournisseur\">
<input type=\"hidden\" name=\"fournisseurname\" value=\"".$adv->destinataire."\">";

print "<div id=\"stepsls1\" class=\"advgroup\">
<p>1. Produits</p>
<table>
  <tr>
    <td colspan=\"2\"></td>
    <td>qte</td>
    <td>Détail</td>
    <td>Poids brut en kg</td>
  </tr>";
$artids = array();
$lignefactures=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid = '$facturid'");
while ($lignefacture=$DB_site2->fetch_array($lignefactures)){
  $article=$DB_site2->query_first("SELECT *, (emballage_poids1+emballage_poids2+emballage_poids3) as emballage_poids FROM article a, article_marque am, marque m WHERE am.artid=a.artid AND am.marqueid=m.marqueid AND a.artid=".$lignefacture['artid']);
  $artids[$lignefacture['artid']] = $article['typearticleid'] ;
  $options=$DB_site2->query_first("SELECT SUM(paht) FROM lignefacturecaracteristique WHERE lignefactureid=".$lignefacture['lignefactureid']);
  $envoi=$DB_site2->query_first("SELECT * FROM envoi_fournisseur_ligne WHERE type='sls' AND ligne_facture_id=$lignefacture[lignefactureid]");
  $style="";
  if($envoi['date']!="") $style="background-color:green;";
  $img="";
  $path="../configurations/".$host."/images/produits/br/".$lignefacture['artid'].".jpg";
  if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
  $checkbox="<input type=\"checkbox\" name=\"produits3[]\" value=\"$lignefacture[lignefactureid]\" onclick=\"assurancesls();showstep('sls2');updligne($lignefacture[lignefactureid],(this.checked?".$lignefacture[qte].":0),2)\" ".($adv->lignes[$lignefacture[lignefactureid]]>0?"checked":"").($adv->locked?" disabled":"").">";
  $oadv=$adv->listadv($facturid,"sls",$lignefacture['lignefactureid']);

  if(count($oadv)>0 && $oadv[0]->id!=$adv->id && $envoi['ligne_facture_id']==$lignefacture['lignefactureid']){
    $checkbox="";
    $style="background-color:#FFAA22;";
  }

  print "<tr>
    <td style=\"$style\">
        <input type=\"hidden\" name=\"artcode_$lignefacture[lignefactureid]\" value=\"$article[artcode]\">
      <input type=\"hidden\" name=\"qte3_$lignefacture[lignefactureid]\" value=\"$lignefacture[qte]\">
      <input type=\"hidden\" name=\"price3_$lignefacture[lignefactureid]\" value=\"".($lignefacture['paht']+$options[0])."\">
      <input type=\"hidden\" name=\"poids_$lignefacture[lignefactureid]\" value=\"".($article['artid']==0?'0':$article['emballage_poids'])."\">
      <input type=\"hidden\" name=\"ecopart3_$lignefacture[lignefactureid]\" value=\"".($article['artid']==0?'0':$article['ecopartht'])."\">$checkbox</td>
    <td>$img</td>
    <td>$lignefacture[qte]</td><td id=\"libelle_$lignefacture[lignefactureid]\">".($article['artid']==0?$lignefacture['libelle']:$article[libelleFR])."</td>
    <td><input type=\"hidden\" name=\"poids3_$lignefacture[lignefactureid]\" value=\"".($article['artid']==0?'0':$article[emballage_poids])."\"><input type=\"hidden\" name=\"typearticle_$lignefacture[lignefactureid]\" value=\"$article[typearticleid]\">".($article['artid']==0?'0':$article['emballage_poids'])."</td>
  </tr>";
  if($article['artid']==0) {
    $recup='recup_cave';
  }
}

print "<tr><td colspan=\"3\"></td><td>Poids total</td><td id=\"poidstotal3\"></td></tr>
</table>
</div>";

print "<div id=\"stepsls2\" class=\"advgroup\"".(isset($adv->steps[2])?" style=\"display:block;\"":"").">
<p>2. Agence de livraison et d'installation</p>
<input type=\"hidden\" name=\"livrslsname\" value=\"".$adv->fields['livrslsname']."\">
<input type=\"hidden\" name=\"name_agence\" value=\"".$adv->fields['name_agence']."\">
<table ".($adv->modif?"style=\"display:none;\"":"").">
<tr><td>".$adv->radio_inputs("livrsls",array(""=>"0"),"document.getElementsByName('livrslsname')[0].value='0';showstep('sls13');fournisseur_save(0);adv_save('livrsls');adv_save('livrslsname')")."</td><td>Pas d'installation</td>
<td>".$adv->radio_inputs("livrsls",array(""=>"webmaster4@mon-espace-cuisson.com"),"document.getElementsByName('livrslsname')[0].value='test';showstep('sls3');fournisseur_save('test','GBP');adv_save('livrsls',3);adv_save('livrslsname')")."</td><td>Acilyon test</td></tr><tr>";

$fourn=array();
$fournisseur_devise=array();
$list=$DB_site2->query("SELECT *,fc.devise FROM fournisseur LEFT OUTER JOIN fournisseur_condition fc USING(societe) WHERE `type`='slsadv' AND fc.activite = '1' ORDER BY societe");
while($advf=$DB_site2->fetch_array($list)){
    $pa=$DB_site2->query_first("SELECT paht FROM montage WHERE fournisseur='".$advf['societe']."' AND paysid=".$facture['paysid']);
    $prixmontage[trim($advf['societe'])]=floatval($pa['paht']);
    if($advf['devise']=="") $advf['devise']=$devises->defaut;
    if($advf['groupes']!=""){
      $groups=explode(",",$advf['groupes']);
      foreach($groups as $group){
	$fourn[$group][]=$advf['mail'];
	$fournisseur_devise[$group]=$advf['devise'];
      }
    }else{
      $fourn[$advf['societe']][]=$advf['mail'];
      $fournisseur_devise[$advf['societe']]=$advf['devise'];
    }
}

foreach($fourn as $group=>$mails){
    
// 	    if($transbk_base==0 && $group=="Trans BK Logistique") continue;
	    $i++;
	    $c++;
	    $mails=array_unique($mails);
	    $mailcsv=implode(";",$mails);
// 	    if(strpos($group,$dep)>-1) 

	    print "<td>".$adv->radio("livrsls",array(""=>$mailcsv),"select_install('$group');showstep('sls3');liste_sls_agence('$group','liste_sls_$i');fournisseur_save('$group','".$fournisseur_devise[$group]."');change_devise('".$fournisseur_devise[$group]."');assurancesls();adv_save('livrsls',3);adv_save('livrslsname'); majTypeInstallateur('" . $group . "','".$recup."');")."</td><td title=\"$mailcsv\"".(strpos($group,$dep)>-1?" style=\"color:red;\"":"").">".ucfirst($group)."<div id=\"liste_sls_".$i."\" class=\"liste_sls\">";

        $sql = "SELECT *";
        $sql .= " FROM fournisseur";
        $sql .= " INNER JOIN pays ON (pays.paysid = fournisseur.paysid)";
        $sql .= " WHERE type = 'sls' AND societe = '".$group."'";
        $requete = $DB_site2->query($sql);
        if($DB_site2->num_rows($requete) > 1){
            while($sls=$DB_site2->fetch_array($requete)){
                print "<p>".$adv->radio("name_agence",array(""=>$group . " ".$sls['ville']),"adv_save('name_agence')"). " ".$group . " ".$sls['ville']."</p>";
            }
        }

        print "</div>
            </td>";
        if($c==2){
            $c=0;
            print "</tr><tr>";
        }
}
//showstep('sls3');

print "</tr></table>";
if($adv->modif) print $adv->destinataire." (".$adv->fields['livrsls'].")";
print "</div>";


//// ici liste agences si plusieurs agences

    print "";
/////
print "<div id=\"stepsls3\" class=\"advgroup\"".(isset($adv->steps[3])?" style=\"display:block;\"":"").">
<p>3. Pièces jointes</p>
<table><tr><td>
".$adv->checkbox("slspj","showstep('sls4');adv_save(%name,4)","0")." Aucune</td></tr>
<tr><td>".$adv->checkbox("bonslspj","showstep('sls4');adv_save(%name,4)",1)." Bon de livraison</td></tr>
<tr><td>".$adv->checkbox("factureslspj","showstep('sls4');adv_save(%name,4)",1)." Facture</td></tr>
<tr><td>".$adv->checkbox("otherslspj","showstep('sls4');adv_save(%name,4)",1)." Autre : <br><input type=\"file\" name=\"otherslspjfile\"><br><input type=\"file\" name=\"otherslspjfile_bis\"></td></tr>";


$artimg="";
$item="";
$rq_lignefacturediss=$DB_site2->query("SELECT a.artid,a.artcode,lf.libelle,lf.prix,lf.qte,lf.lignefactureid FROM lignefacturediss lf INNER JOIN article a USING(artid) WHERE lf.factureid = '".$facturid."' order by lf.artcode");
        while($lignefacturediss=$DB_site2->fetch_array($rq_lignefacturediss)){
            $i=0;
            $artimg_base = $DB_site2->query_first("select artid, image,artcode from article where artid = '".$lignefacturediss['artid']."'") ;
            $artimg ="<div style=\"display:inline-block;\"><img src=\"http://dedie.ma-cave-a-vin.fr/configurations/$host/images/produits/article/$artimg_base[artid].$artimg_base[image]?t=".time()."\" name=\"base\" width=\"100\" style=\"margin-right:15px;\">";
            $artimg.="<p style=\"text-align:center;\">".$adv->checkbox("articlephoto_".$i,"adv_save(%name,4)",$lignefacturediss['artid'])."</p></div>"; 
            $i++;
            $articleimages=$DB_site2->query("select * from articlephoto where artid = '".$lignefacturediss['artid']."' ORDER BY position") ;
            while ($articleimage=$DB_site2->fetch_array($articleimages)){
                    $artimg .="<div style=\"display:inline-block;\"><img src=\"http://dedie.ma-cave-a-vin.fr/configurations/$host/images/produits/article/$articleimage[artid]_$articleimage[articlephotoid].$articleimage[image]?t=".time()."\" width=\"100\" style=\"margin-right:15px;\">";
                    $artimg.="<p style=\"text-align:center;\">".$adv->checkbox("articlephotosup_".$i,"adv_save(%name,4)",$articleimage[articlephotoid])."</p></div>"; 
                $i++;
            }
            $item.="<div>Lot ".$artimg_base['artcode']."</div>".$artimg;
        }

foreach($artids as $artid => $typeArticleid){
    if($artid != 0) {
        $artimg_base = $DB_site2->query_first("select artid, image,artcode from article where artid = '$artid'") ;
        $artimg ="<div style=\"display:inline-block;\"><img src=\"http://dedie.ma-cave-a-vin.fr/configurations/$host/images/produits/article/$artimg_base[artid].$artimg_base[image]?t=".time()."\" name=\"base\" width=\"100\" style=\"margin-right:15px;\">";
        $artimg.="<p style=\"text-align:center;\">".$adv->checkbox("articlephoto_".$i,"adv_save(%name,4)",$artid)."</p></div>"; 
        $i++;
        $articleimages=$DB_site2->query("select * from articlephoto where artid = '$artid' ORDER BY position") ;
        while ($articleimage=$DB_site2->fetch_array($articleimages)){
                $artimg .="<div style=\"display:inline-block;\"><img src=\"http://dedie.ma-cave-a-vin.fr/configurations/$host/images/produits/article/$articleimage[artid]_$articleimage[articlephotoid].$articleimage[image]?t=".time()."\" width=\"100\" style=\"margin-right:15px;\">";
                $artimg.="<p style=\"text-align:center;\">".$adv->checkbox("articlephotosup_".$i,"adv_save(%name,4)",$articleimage[articlephotoid])."</p></div>"; 
            $i++;
        }
        $item.="<div>".$artimg_base['artcode']."</div>".$artimg;
    }
}

print "<tr><td>Photos article pour montage<br>$item</td></tr>";



print "</table></div>";

if(!$adv->locked && $adv->fields["slsobj"]=="annule") $adv->save_field("slsobj","cmd");
if($adv->locked) $adv->save_field("slsobj","annule");
if($adv->modif) $adv->save_field("slsobj","modif");
print "<div id=\"stepsls4\" class=\"advgroup\"".(isset($adv->steps[4])?" style=\"display:block;\"":"").">
<p>4. Objet du mail</p>
<div".($facture['modelivraison']==4 || $adv->modif || $adv->locked?" style=\"display:none;\"":"").">".$adv->radio_inputs("slsobj",array(""=>"cmd"),"showstep('sls5');adv_save('slsobj',5)")." Commande de livraison et d'installation n°$facturid</div>
<div".($facture['modelivraison']!=4 || $adv->modif || $adv->locked?" style=\"display:none;\"":"").">".$adv->radio_inputs("slsobj",array(""=>"install"),"showstep('sls5');adv_save('slsobj',5)")." Commande de livraison, d'installation et de montage n°$facturid</div>
<div".($adv->modif || !$adv->locked?" style=\"display:none;\"":"").">".$adv->radio("slsobj",array(""=>"annule"),"showstep('sls11');adv_save('slsobj',11)")." Annulation de la commande n°$facturid</div>
<div ".(!$adv->modif?" style=\"display:none;\"":"").">".$adv->radio("slsobj",array("Modifier commande"=>"modif"),"showstep('sls5');adv_save('slsobj',5);")."</div>
</div>";

/*
print "<div class=\"advgroup\">
<p>4. Texte d'intro</p>
<input type=\"radio\" name=\"slsintro\" value=\"bonjour\" checked> Bonjour, Veuillez trouver ci-joint une nouvelle commande pour livraison et installation n°$facturid
<br><input type=\"radio\" name=\"slsintro\" value=\"annule\"> Merci d'annuler le transport sur la commande n°$facturid et nous le confirmer par retour de ce mail.
<!--<br><input type=\"radio\" name=\"slsintro\" value=\"otherslsintro\"> Autre : <br><textarea name=\"otherslsintro\" cols=\"80\" rows=\"3\"></textarea>-->
</div>";
*/

$opttrspt=array(""=>"Choisir...");
$requete=$DB_site2->query("SELECT * FROM fournisseur WHERE type='trspt' GROUP BY societe ORDER BY societe");
while($trspt=$DB_site2->fetch_array($requete)){
    $opttrspt[$trspt[id]]=$trspt[societe];
}
$optfourn=array(""=>"Choisir...");
$requete=$DB_site2->query("SELECT * FROM fournisseur WHERE type='fourn' GROUP BY societe ORDER BY societe");
while($trspt=$DB_site2->fetch_array($requete)){
    $optfourn[$trspt[id]]=$trspt[societe];
}
$stock=new stock($DB_site2);
$lieux=$stock->lieux();
$optlieu = array();
foreach($lieux as $lieuid=>$lieu)
    $optlieu[$lieuid]=$lieu;

//     <div id=\"lieusls\">Agence de ".$adv->select("lieusls",$optlieusls,"adv_save(this.name)")."</div>
    
print "<div id=\"stepsls5\" class=\"advgroup\"".(isset($adv->steps[5])?" style=\"display:block;\"":"").">
<p>5. Date livraison en vos entrepots</p>
<b>Date livraison commande : $datelivrtxt</b>
<table>
<!--<tr><td><input type=\"radio\" name=\"slslivr\" value=\"0\"></td><td>Aucune</td></tr>-->
<tr><td>".$adv->radio_inputs("slslivr",array(""=>"livrtrspt"),"showstep('sls6');adv_save('slslivr',6)")."</td><td>Le ou les colis vous seront livrés directement à votre entrepot par ".$adv->select("livrtrsptid",$opttrspt,"adv_save(this.name)")."</td>
<td rowspan=\"6\" style=\"border-left:solid thin black;\">
    Date livraison<br>".$adv->cal_input("slslivrdate","adv_calsave")."
  </td>
</tr>
<tr><td>".$adv->radio("slslivr",array(""=>"livrfourn"),"showstep('sls6');adv_save('slslivr',6)")."</td><td>Le ou les colis vous seront livrés directement à votre entrepot par transporteur de ".$adv->select("livrfournid",$optfourn,"adv_save(this.name)")."</td></tr>
<tr><td>".$adv->radio("slslivr",array(""=>"livrstock"),"showstep('sls6');adv_save('slslivr',6)")."</td><td>Le ou les colis sont à prendre dans le stock ".$adv->select("livrstockid",$optlieu,"adv_save('livrstockid')")."</td></tr>
<tr><td>".$adv->radio("slslivr",array(""=>"livrclient"),"showstep('sls6');adv_save('slslivr',6)")."</td><td>Le ou les colis ont déja été livrés chez le client par un transporteur</td></tr>
</table>
<!--<input type=\"checkbox\" name=\"slsautreinfocheck\" value=\"1\"> Autre :<br><textarea name=\"slsautreinfo\" cols=\"60\" rows=\"3\" onkeyup=\"document.getElementsByName('slsautreinfocheck')[0].checked=(this.value!='')\"></textarea>-->
</div>";

print "<div id=\"stepsls6\" class=\"advgroup\"".(isset($adv->steps[6])?" style=\"display:block;\"":"").">
<p>6. Identification sur le colis</p>
".$adv->checkbox("slsidentbl","showstep('sls7');adv_save(%name,7)",1)." BL fournit ci-joint
<br>".$adv->checkbox("slsidenttxt","showstep('sls7');adv_save(%name,7)",1)." Texte sur colis : commande n°$facturid de Ma Cave à Vin - Acilyon pour ".$adv->text_input("slsidentpour","adv_autosave(this.name)","","$user[nom] $user[prenom]")."
<!--<br><input type=\"checkbox\" name=\"slsidentimportant\" value=\"1\" onclick=\"showstep('sls7');\"> Important : enlever tous les documents de livraison de EBERHARDT Frères-->
<br>".$adv->checkbox("trsptenldoc","showstep('sls7');adv_save(%name,7)",1)." Important : enlever tous les documents de livraison du fournisseur et mettre le Bon de livraison ci-joint a la commande
<br>".$adv->checkbox("slsidentother","showstep('sls7');adv_save(%name,7)",1)." Autre : ".$adv->text_input("slsidentothertxt","adv_autosave(this.name)",array("size"=>60))."
</div>";

/*
print "<div class=\"advgroup\">
<p>8. Adresse de livraison</p>
<input type=\"radio\" name=\"slsadrlivr\" value=\"particulier\"> Adresse client particulier
<br><input type=\"radio\" name=\"slsadrlivr\" value=\"pro\"> Adresse client professionnel
</div>";
*/

print "<div id=\"stepsls7\" class=\"advgroup\"".(isset($adv->steps[7])?" style=\"display:block;\"":"").">
<p>7. Date livraison et installation maximum</p>
<b>Date livraison commande : $datelivrtxt</b>
<br>Date livraison ".$adv->radio_inputs("maxslslivr",array("maximum"=>"max","date précise"=>"precis"),"adv_save('maxslslivr',8);showstep('sls8')")." ".$adv->cal_input("slsmaxdate","adv_calsave")."
<br>".$adv->checkbox("livraisonplustot","adv_save(%name)",1)." Voir la possibilité de livrer plus tôt
<br>".$adv->checkbox("slsinfoother","adv_save(%name)",1)." Info pour transporteur : ".$adv->text_input("slsinfoothertxt","adv_autosave(this.name)",array("size"=>80))."
</div>";


$totalmontage=0;

print "<div id=\"stepsls8\" class=\"advgroup\"".(isset($adv->steps[8])?" style=\"display:block;\"":"").">
<p>8. Information transporteur et d'installation</p>
".$adv->checkbox("slsinfo","adv_save(%name)","0")." Aucune
<br>".$adv->checkbox("slsrdv","showstep('sls9');adv_save(%name,9)",1)." Prendre RDV avec le client pour livraison
<br>".$adv->checkbox("slsbranchement","showstep('sls9');adv_save(%name,9)",1)." Important : ne pas brancher électriquement le produit (branchement a effectuer par le client 48h après)
<br>".$adv->checkbox("slsmiseniveau","showstep('sls9');adv_save(%name,9)",1)." Important : mettre à niveau le produit à l'aide des pieds réglables
<br>".$adv->checkbox("slsmontage","showstep('sls9');adv_save(%name,9)",1)." Important : Si besoin, assurer le montage des accessoires (poignée de porte, clayettes, etc...)
<br>".$adv->checkbox("slsemballage","showstep('sls9');adv_save(%name,9)",1)." Important : bien penser a récupérer les emballages pour destruction
<br>".$adv->checkbox("slsemballage2","showstep('sls9');adv_save(%name,9)",1)." Important : merci de laisser les emballages chez le client";
if($facture['modelivraison']==4){
   $req=$DB_site2->query("SELECT artid,lf.artcode,montage_heures,libelle,qte FROM lignefacture lf INNER JOIN article USING(artid) WHERE factureid=".$facturid." AND montage=1 AND lignefacturedissid=0");
   while($art=$DB_site2->fetch_array($req)) {
      $listnotices="";
      $notices=$DB_site2->query("SELECT * FROM article_files WHERE artid=".$art['artid']." AND infos LIKE 'notice%'");
      while($notice=$DB_site2->fetch_array($notices)) $listnotices.=$adv->checkbox("notice[".$art['artid']."]","adv_save(%name)",$notice['id'])." ".$notice['infos']."<br>";
      
      $articles_montage.="<tr>
			    <td><img src=\"../configurations/".$host."/images/produits/br/".$art['artid'].".jpg\" width=\"30\"></td>
			    <td>".$art['libelle']."</td>
			    <td>".$art['qte']."</td>
			    <td>".(60*$art['montage_heures'])." min</td>
			    <td>".(60*$art['qte']*$art['montage_heures'])." min</td>
			    <td>$listnotices</td>
			  </tr>";
      $totalmontage+=($art['qte']*$art['montage_heures']);
   }
   
   $req=$DB_site2->query("SELECT artid,lf.artcode,montage_heures,libelle,qte FROM lignefacturediss lf INNER JOIN article USING(artid) WHERE factureid=".$facturid." AND montage=1");
   while($art=$DB_site2->fetch_array($req)) {
      $listnotices="";
      $notices=$DB_site2->query("SELECT * FROM article_files WHERE artid=".$art['artid']." AND infos LIKE 'notice%'");
      while($notice=$DB_site2->fetch_array($notices)) $listnotices.=$adv->checkbox("notice[".$art['artid']."]","adv_save(%name)",$notice['id'])." ".$notice['infos']."<br>";
      
      $articles_montage.="<tr>
			    <td><img src=\"../configurations/".$host."/images/produits/br/".$art['artid'].".jpg\" width=\"30\"></td>
			    <td>".$art['artcode']."<br>".$art['libelle']."</td>
			    <td>".$art['qte']."</td>
			    <td>".(60*$art['montage_heures'])." min</td>
			    <td>".(60*$art['qte']*$art['montage_heures'])." min</td>
			    <td>$listnotices</td>
			  </tr>";
      $totalmontage+=($art['qte']*$art['montage_heures']);
   }
   
   print "<br>".$adv->checkbox("slsinstallation","showstep('sls9');adv_save(%name,9)",1)." Important : Montage mécanique par 2 personnes des éléments à faire par vos soins  (attention si des perçages sont nécessaires, dans le sol ou les murs, cela doit rester à charge du client)
    <table id=\"articles_montage\">
    <tr>
      <td>Produit</td>
      <td>Descriptif</td>
      <td>Quantité</td>
      <td>Temps unit.</td>
      <td>Temps total</td>
      <td>Notices à fournir</td>
    </tr>
    $articles_montage
    <tr>
      <td></td>
      <td>Total montage :</td>
      <td></td>
      <td></td>
      <td>".(60*$totalmontage)." min = ".$totalmontage." h</td>
    </tr>
    </table>"; 
}
print "</div>";

print ""
. "<div id=\"stepsls9\" class=\"advgroup\"".(isset($adv->steps[9])?" style=\"display:block;\"":"") . ">"
    . "<p>9. Type de transport</p>"
    . "<div id=\"type_trspt_install\"></div>"
. "</div>";

print "<div id=\"stepsls10\" class=\"advgroup\"".(isset($adv->steps[10])?" style=\"display:block;\"":"").">
    <p>10. Colisage</p>"
    .$adv->radio_inputs("pcolis", array('Colisage simple (1 seul colis)' => 0), "adv_save(%name,10);show_colis(this.value);colis_prepare(this.value);colis_add('colis','',this.value);")
    .$adv->radio("pcolis", array('Colisage multiple' => 1), "adv_save('pcolis',10); show_colis(this.value); colis_prepare(this.value);")
    ."<div id=\"colis_simple\">
        <div id=\"colis_simple_dim\"></div>
       <center><input type=\"button\" value=\"Valider\" onclick=\"showstep('sls11');\" style=\"width: 100px;\"></center>
    </div>
    <div id=\"colis_conf\">
        <input type=\"hidden\" name=\"nb_palette\" id=\"nb_palette\" value=\"0\">
        <div id=\"colis_list\">
            <a href=\"javascript:void(0)\" onclick=\"colis_add('colis')\"><img src=\"images/icon/edit_add.png\"> Ajouter un colis</a>
            <!-- Commenté pour éviter de commander des palettes consignées, ce que nous ne faisons jamais -->
            <!-- <a href=\"javascript:void(0)\" onclick=\"colis_add('palette')\"><img src=\"images/icon/edit_add.png\"> Ajouter une palette</a> -->
        </div>
        <div id=\"art_list\"></div>
        <div class=\"clear\"></div>
        <center><input type=\"button\" value=\"Valider\" onclick=\"showstep('sls11');\" style=\"width: 100px;\"></center>
    </div>
</div>";

print "<div id=\"stepsls11\" class=\"advgroup\"".(isset($adv->steps[11])?" style=\"display:block;\"":"").">
<p>11. Récupération ancien produit</p>
".$adv->radio_inputs("recup",array(""=>"0"),"showstep('sls12');showstep('sls13');adv_save('recup','12,13')")." Aucune
<br>".$adv->radio("recup",array(""=>1),"showstep('sls12');showstep('sls13');adv_save('recup','12,13')")." récupération de l'ancien produit, pour destruction. Poids : ".$adv->nb_input("recuppoids","adv_autosave(this.name)")."
<br>".$adv->radio("recup",array(""=>2),"showstep('sls12');showstep('sls13');adv_save('recup','12,13')")." réemballage/récupération ancien produit pour retour chez : ".$adv->select("recupretour",$optfourn,"adv_save(this.name)")." Poids : ".$adv->nb_input("recuppoids2","adv_autosave(this.name)")."
<br>".$adv->radio("recup",array(""=>"other"),"showstep('sls12');showstep('sls13');adv_save('recup','12,13')")." ".$adv->text_input("recupother","adv_autosave(this.name)",array("size"=>80))."
</div>";
?>

<div id="stepsls12" class="advgroup" <?php echo (isset($adv->steps[12])?" style=\"display:block;\"" : ""); ?>>
     <p>12. Tarifs</p>
     <table>
          <tr>
               <td><?php echo $adv->checkbox("slscouttrspt","adv_save(%name);adv_save('slscouttrsptval');totalsls()",1); ?></td>
               <td>Cout transport et installation <div style="float:right;"><span id="coutpoidssls"></span> CP <?php echo $rs_facture['codepostal']; ?> - <?php echo $pays_fact['libelle']; ?></div></td>
               <td></td>
               <td><?php echo $adv->nb_input("slscouttrsptval","adv_autosave(this.name)"); ?><span id="slscouttrsptval_devise"><?php echo $devises->symbole($adv->devise); ?></span></td>
          </tr>
          <tr>
               <td>
                    <?php 
                    $name    = 'slscoutctrremb';
                    $actions = array(
                         "totalsls()",
                         "adv_save(%name)"
                    );
                    $value   = 1;
                    echo $adv->checkbox($name, implode(';', $actions), $value); ?>
               </td>
               <td colspan="2">Contre remboursement | Moyen de paiement
                    <?php 
                    //Mise en place de la liste déroulante des moyens de paiement
                    $sql = "SELECT moyenid, libelleFR";
                    $sql .= " FROM moyenpaiement";
                    $sql .= " WHERE moyenid NOT IN (0,3)";
                    $sql .= " ORDER BY libelleFR";
                    $requete = $DB_site2->query($sql);
                    $opt_moyen[0] = "-- Sélectionner --";
                    while($le_moyen = $DB_site2->fetch_array($requete)){
                        $opt_moyen[$le_moyen['moyenid']] = $le_moyen['libelleFR'];
                    }
                    
                    $name    = 'sls_ctrremb_moyen';
                    $values  = $opt_moyen;
                    $actions = array(
                         'adv_save(this.name)'
                    );
                    echo $adv->select($name,$values,implode(';', $actions)); 
                    ?> | Montant du règlement
                    <?php
                    $name    = 'sls_ctrremb_montant';
                    $actions = array(
                         'adv_save(this.name)'
                    );
                    echo $adv->nb_input($name, implode(';', $actions))
                    ?><span id="sls_ctrremb_montant_devise"><?php echo $devises->symbole($adv->devise); ?></span> | 
                    Tarif du service 
                    <?php
                    $name    = 'slscoutctrrembval';
                    $actions = array(
                         'adv_save(this.name)'
                    );
                    echo $adv->nb_input($name, implode(';', $actions))
                    ?><span id="sls_ctrremb_coutval_devise"><?php echo $devises->symbole($adv->devise); ?></span>
               </td>
          </tr>
          <tr id="trmontage" style="<?php echo ($facture['modelivraison']!=4?"display:none;":""); ?>">
               <td><?php echo $adv->checkbox("slscoutmontage","adv_save(%name);totalsls()",1); ?></td>
               <td>Montage mécanique et assemblage <span id="tauxmontage"></span></td>
               <td></td>
               <td><?php echo $adv->nb_input("slscoutmontageval","adv_autosave(this.name)"); ?></td>
          </tr>
          <tr id="trcorse">
               <td><?php echo $adv->checkbox("slscoutcorse","adv_save(%name);totalsls()",1); ?></td>
               <td>Passage maritime Corse</td>
               <td><input type="text" id="prixkgcorse" value="1.28" size="5"> &euro;/kg</td>
               <td><?php echo $adv->nb_input("slscorsetotal","adv_autosave(this.name)"); ?></td>
          </tr>
          <tr>
               <td><?php echo $adv->checkbox("slscoutreprise","adv_save(%name);totalsls()",1); ?></td>
               <td>Reprise ancien produit pour destruction ou relivraison</td>
               <td></td>
               <td><?php echo $adv->nb_input("slscoutrepriseval","adv_autosave(this.name)"); ?><span id="slscoutrepriseval_devise"><?php echo $devises->symbole($adv->devise); ?></span></td>
          </tr>
          <tr id="trassurancesls">
               <td><?php echo $adv->checkbox("slscoutassur","adv_save(%name);totalsls()",1); ?></td>
               <td>Assurance complémentaire 0,8% valeur</td>
               <td colspan="2">Valeur : <?php echo $adv->nb_input("valeurtotalsls","adv_autosave(this.name)",array("id"=>"valeurtotalsls","readonly"=>"true")); ?><br>Remb. : <input type="text" id="valeurremboursesls" value="0" size="5" readonly><br><?php echo $adv->nb_input("slscoutassurval","adv_autosave(this.name)"); ?><span id="slscoutassurval_devise"><?php echo $devises->symbole($adv->devise); ?></span></td>
          </tr>
          <tr id="trassuranceenigma">
               <td><?php echo $adv->checkbox("enigmacoutassur","adv_save(%name);totalsls()",1); ?></td>
               <td>Assurance obligatoire £5 unit.</td>
               <td></td>
               <td><?php echo $adv->nb_input("enigmacoutassurval","adv_autosave(this.name)"); ?>£</td>
          </tr>
          <tr id="trprise">
               <td><?php echo $adv->checkbox("enigmaprise","adv_save(%name);totalsls()",1); ?></td>
               <td>Fourniture et changement prise 6£ unit.</td>
               <td></td>
               <td><?php echo $adv->nb_input("enigmapriseval","adv_autosave(this.name)"); ?>£</td>
          </tr>
          <tr>
               <td><?php echo $adv->checkbox("slscoutother","adv_save(%name);totalsls()",1); ?></td>
               <td>Autre : <?php echo $adv->text_input("slscoutothername","adv_autosave(this.name)",array("size"=>80)); ?></td>
               <td></td>
               <td><?php echo $adv->nb_input("slscoutotherval","adv_autosave(this.name)"); ?></td>
          </tr>
          <tr>
               <td></td>
               <td>Total HT (Hors taxe gas oil)</td>
               <td></td>
               <td id="totalsls"></td>
          </tr>
     </table>
     <br>Informations supplémentaires :<br><textarea cols="80" name="infocomplement"></textarea>
     <input type="button" onclick="adv_save('infocomplement')" value="ok" />
</div>
<?php
$envoidate=null;
if($adv->envoi>0) $envoidate=date("d/m/Y",$adv->envoi);
$henvoi="";
if($adv->destinataire!=""){
    $h=$DB_site2->query_first("SELECT * FROM adv_heure WHERE fournisseur='".$adv->destinataire."'");
    $henvoi="à ".intval($h['heure'])."h";
}
print "<div id=\"stepsls13\"".(isset($adv->steps[13])?" style=\"display:block;\"":"").">
  <input type=\"button\" value=\"Aperçu\" class=\"preview\" onclick=\"valid3(true);\">
  <div style=\"text-align:left;".($adv->locked?"display:none;":"")."\">
      <br><input type=\"radio\" name=\"envoiauto\" onclick=\"envoidate_save();\" ".($adv->envoi>0?"checked":"")."> Envoyer la commande automatiquement <span id=\"envoi1\" style=\"".($adv->envoi==0?"display:none;":"")."\">le <input type=\"text\" id=\"envoidate\" value=\"".$envoidate."\" size=\"10\" onclick=\"calendrier_show(this.id,this.value,undefined,envoidate_save);\" readonly> <span id=\"henvoi\">".$henvoi."</span><input type=\"button\" value=\"Valider\" class=\"clock\" onclick=\"document.location='facture.php?action=editer&facturid=$facturid#adv'\"></span>
      <br><br><input type=\"radio\" name=\"envoiauto\" onclick=\"envoidate_save();\" ".($adv->envoi==0?"checked":"")."> Envoyer la commande immédiatement <span id=\"envoi0\" style=\"".($adv->envoi>0?"display:none;":"")."\"><input type=\"button\" value=\"Envoi immédiat\" onclick=\"valid3();\"></span>
  </div>
  <div style=\"".($adv->locked?"":"display:none;").";margin-top:10px;\"><input type=\"button\" onclick=\"valid3()\" value=\"Annuler la commande\" style=\"color:red;font-weight:bold;\" /></div>
</div>";

print "</div>
<input type=\"hidden\" name=\"advid\" value=\"".$adv->id."\">
</form>";

/*
print "<div class=\"advgroup\">
<p>13. Texte de fin</p>
<input type=\"checkbox\" name=\"slsfinautre\" value=\"1\"> Autre :<br><textarea name=\"otherendtxtsls\" cols=\"60\" rows=\"3\" onkeyup=\"document.getElementsByName('slsfinautre')[0].checked=(this.value!='')\"></textarea>
<br><input type=\"radio\" name=\"slsfin\" value=\"1\" checked> Nous vous remercions de votre envoi, par mail ou par fax, de votre confirmation de commande.<br>Cordialement<br>$user_info[nom_contact] - Acilyon - www.ma-cave-a-vin.fr<br>Tél : +33.4.26.03.05.43 - fax : +33.4.26.03.05.49
<br><input type=\"radio\" name=\"slsfin\" value=\"annule\"> Cordialement<br>$user_info[nom_contact] - Acilyon - www.ma-cave-a-vin.fr<br>Tél : + 33.4.26.03.05.43 - Fax : + 33.4.26.03.05.49
</div>";
*/

?>
