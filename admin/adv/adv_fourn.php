<?php
//ONGLET "Fournisseur(s)" de l'adv
$activefournkeys = array();
$lieustocknb     = array();
$pahtlist        = array();
$fournlist       = array();
$chkstock        = array();
$opt             = array(
          "" => "-- Sélectionner --"
);
$optsls          = array(
          "" => "-- Sélectionner --"
);

$sql = "SELECT paysid, site_code, datefacture,heurefacture";
$sql .= " FROM facture";
$sql .= " WHERE facturid = ".intval($facturid);
$facture = $DB_site2->query_first($sql);

if($facture['site_code'] == "") $facture['site_code'] = "base";
/// affiche les transporteurs (et installateurs) actifs
$sql = "SELECT *";
$sql .= " FROM fournisseur";
$sql .= " LEFT OUTER JOIN fournisseur_condition fc USING(societe)";
$sql .= " WHERE (type = 'trsptadv' OR type = 'sls')";
$sql .= " AND fc.activite = '1'";
$sql .= " AND (fc.types LIKE '%transporteur%' OR fc.types LIKE '%installateur%')";
$sql .= " ORDER BY societe";
$requete = $DB_site2->query($sql);
while($t = $DB_site2->fetch_array($requete)){
     $opt[$t['societe']]=$t['societe'];
}

$sql = "SELECT *";
$sql .= " FROM fournisseur";
$sql .= " WHERE type = 'sls'";
$requete = $DB_site2->query($sql);
while($sls = $DB_site2->fetch_array($requete)){
     $optsls[$sls['id']] = $sls['societe'].' '.$sls['ville'].' '.$sls['fonction'];
}

$offre_adv=$adv->fields['offre'];

?>

<div id="tabfourn" style="<?php echo (!isset($_GET['diss']) && $_GET['type'] != "fourn" ? "visibility:hidden; position:absolute;" : ""); ?>">
<h3><img src="images/cave.png" width="20" align="absmiddle"> Fournisseur de caves + accessoires</h3>
<form name="form1" action="facture.php?action=editer&facturid=<?php echo $facturid; ?>" method="post" enctype="multipart/form-data" style="position: relative;">
<div class="loader-adv"><img src="<?php echo BACK_URL."/images/loader.gif"; ?>" class="loading"></div>
    <input type="hidden" name="actionadv" value="advfournisseur">
    <input type="hidden" name="fournisseurname" value="<?php echo $adv->destinataire; ?>">
    <input type="hidden" name="lieustock" value="0">
    
<?php

$sql = "SELECT af.prix as prix, af.trspt, af.devise, af.fournisseur, af.principal, lf.artcode, lf.qte, a.artcode_eurocave, lf.artid, lf.lignefactureid, af.delai, af.reappro";
$sql .= " FROM article_fournisseur af";
$sql .= " INNER JOIN article a USING(artid)";
$sql .= " INNER JOIN lignefacture lf USING(artid)";
$sql .= " INNER JOIN facture f ON (f.facturid = lf.factureid)";
$sql .= " WHERE f.facturid = ".$facturid;

$r = $DB_site2->query($sql);
while($fourn = $DB_site2->fetch_array($r)){
     $fournlist[] = $fourn['fournisseur'];
     
     if(isset($offres[$fourn['fournisseur']])){
	foreach($offres[$fourn['fournisseur']] as $offre){
	    $pahtart=$offre->paht_articles($artids);

	    if(isset($pahtart[$fourn['artid']])){
		foreach($pahtart[$fourn['artid']] as $paht=>$qte){
		    if($offre->articles[$fourn['artid']]['offre']!=""){
			$fourn['listprix'][$paht]=$qte>$fourn['qte']?$fourn['qte']:$qte;
			if($paht!=$fourn['prix']) $fourn['oldprice']=$fourn['prix'];
		    }
		}
	    }
	}
     }
     
     $pahtlist[$fourn['lignefactureid']][$fourn['fournisseur']] = $fourn;
     
     if($fourn['prix']>0){
	if(isset($fourn['listprix'])){
	    $totalart=0;
	    foreach($fourn['listprix'] as $pahtu=>$qte) $totalart+=($pahtu+$fourn['trspt'])*$qte;
	    $fourntotal[$fourn['fournisseur']][] = $totalart;
	}else
	    $fourntotal[$fourn['fournisseur']][] = $fourn['qte']*($fourn['prix']+$fourn['trspt']);
     }
}


$fournlist = array_unique($fournlist);
asort($fournlist);
?>
<div class="advgroup">
     <p style="padding:5px; margin:0;">Fournisseur(s)</p>
     <table>
          <tr>
               <td colspan="2" align="center"><strong>Article</strong></td>
               
               <?php foreach($fournlist as $fournname){
		  $style=(count($fourntotal[$fournname]) == count($pahtlist)?"font-weight:bold;":"");
		  $iconoffre="";
		  $link="";
		  if(isset($offres[$fournname])){
		      if(count($offres[$fournname])==1){
			  $link="&offre=".$offres[$fournname][0]->id;
		      }else{
			  $link="&offres=".implode(",",array_map(function($a){return $a->id;},$offres[$fournname]));
		      }
		      $iconoffre=" <a href=\"facture.php?action=editer&facturid=".$facturid."&type=fourn&advid=".$adv->id.$link."#adv\">Offre spéciale</a>";
		  }
		  
		  print <<<HTML
		  <td style="$style" align="center">$fournname $iconoffre</td>
HTML;
               } ?>
          </tr>
          <?php

          foreach($pahtlist as $lignefactureid => $list){
               
               $fkey = key($list);
               $img  = "<img src=\"../configurations/".$host."/images/produits/".$pahtlist[$lignefactureid][$fkey]['artid'].".jpg\" width=\"50\">";
               ?>
               <tr>
                    <td style="border-top:solid 1px #888; padding: 10px;">
                         <strong><?php echo $pahtlist[$lignefactureid][$fkey]['qte']."x ".$pahtlist[$lignefactureid][$fkey]['artcode']; ?></strong>
                         <br>
                         <?php echo $pahtlist[$lignefactureid][$fkey]['artcode_eurocave']; ?>
                    </td>
                    <td style="text-align: center; padding: 10px;"><?php echo $img; ?></td>
               <?php
    
               foreach($fournlist as $fournname){

                    $info = '';

                    if($pahtlist[$lignefactureid][$fournname]['prix'] > 0){
			      $info.="<strong>Prix achat HT</strong> : ";
			      if(!isset($pahtlist[$lignefactureid][$fournname]['listprix'])){
				  $info .= number_format($pahtlist[$lignefactureid][$fournname]['prix'],2,',',' ').'&nbsp;'.$devises->symbole($pahtlist[$lignefactureid][$fournname]['devise']);
                              }else{
				  $info.="<table>";
				  foreach($pahtlist[$lignefactureid][$fournname]['listprix'] as $paht=>$qte)
				      
				      $info.="<tr>
					<td>".$qte."x</td>
					<td>".number_format($paht,2,',','').'&nbsp;'.$devises->symbole($pahtlist[$lignefactureid][$fournname]['devise'])."</td>
					<td style=\"text-decoration:line-through;\">".($pahtlist[$lignefactureid][$fournname]['oldprice']!=$paht?number_format($pahtlist[$lignefactureid][$fournname]['oldprice'],2,',','').'&nbsp;'.$devises->symbole($pahtlist[$lignefactureid][$fournname]['devise']):"")."</td>
				      </tr>";
				  $info.="</table>";
                              }
                    }
                    
                    if($pahtlist[$lignefactureid][$fournname]['trspt'] > 0){

                        $info .=  ' + '.number_format($pahtlist[$lignefactureid][$fournname]['trspt'],2,',',' ').' '.$devises->symbole($pahtlist[$lignefactureid][$fournname]['devise']).' soit '.number_format($pahtlist[$lignefactureid][$fournname]['prix']+$pahtlist[$lignefactureid][$fournname]['trspt'],2,',',' ').' '.$devises->symbole($pahtlist[$lignefactureid][$fournname]['devise']);

                    }

                    if($pahtlist[$lignefactureid][$fournname]['delai'] > 0){

                        $info .=  '<br><strong>Délai livr. fournisseur</strong> : '.$pahtlist[$lignefactureid][$fournname]['delai'].'j';

                    }

                    $sql = "SELECT *";
                    $sql .= " FROM calendrier";
                    $sql .= " WHERE objid = ".$lignefactureid;
                    $sql .= " AND destinataire = '".mysqli_real_escape_string($DB_site2->link_id,$fournname)."'";
                    $calendrier = $DB_site2->query_first($sql);

                    if(is_array($calendrier)){

                         if($calendrier['enlevement']>0) $info .= "<br>Date enlèvement ".date("d/m/Y",$calendrier['enlevement']);
                         if($calendrier['livraison']>0) $info .= "<br>Date livraison ".date("d/m/Y",$calendrier['livraison']);
                         if($calendrier['reappro']>0) $info .= "<br>Date réappro ".date("d/m/Y",$calendrier['reappro']);

                    } elseif($pahtlist[$lignefactureid][$fournname]['reappro']>time()){

                         $info .= "<br><strong>Réappro</strong> le ".date("d/m/Y",$pahtlist[$lignefactureid][$fournname]['reappro']);

                    }


                    $garpaht = 0;
                    $garanties = DAOgarantie::loadAllGarantiesByFournArtPays($DB_site2,$facture['site_code'],"FR",$pahtlist[$lignefactureid][$fournname]['artid'],$facture['paysid'],$fournname);

                    foreach($garanties as $garantie){

                         $info .= "<br><strong>Garantie</strong> ".formatGarantieName($garantie->duree,$garantie->cumul,$garantie->type_garantie,$garantie->fournisseur)." ".($garantie->paht>0?$garantie->paht.$garantie->devise:"");
                         $garpaht += $garantie->paht;

                    }

                    ?>
                    <td align="right" style="border-top:solid 1px #888; border-left:solid 1px #888; padding: 10px; <?php if($pahtlist[$lignefactureid][$fournname]['principal'] == 'base' || $pahtlist[$lignefactureid][$fournname]['principal'] == 'uk') { ?>background-color:#FFD8A5;<?php } ?>"><?php echo $info; ?></td>
                    <?php

                    if($pahtlist[$lignefactureid][$fournname]['trspt']>0){
                         $prixtrspt = $pahtlist[$lignefactureid][$fournname]['trspt'];
                    }
               }
               ?>
                    <input type="hidden" id="prixtrspt_<?php echo $lignefactureid; ?>" value="<?php echo $prixtrspt; ?>"/>
                    <input type="hidden" id="gar_<?php echo $lignefactureid; ?>[<?php echo $fournname; ?>]" value="<?php echo $garpaht; ?>">
               </tr>
               <?php
          }
          ?>
          
          <tr>
               <td colspan="2"></td>
          
          <?php foreach($fournlist as $fournname){
	      $total="";
	      $color="";
	      if(count($fourntotal[$fournname]) == count($pahtlist)){
		  $total=number_format(array_sum($fourntotal[$fournname]),2,',',' ').$devises->symbole($fournisseur_devise[$fournname]);
		  $color="#00aa00";
	      }
              print <<<HTML
               <td align="right" style="background-color: $color; color: white; font-size: 14px; font-weight: bold;padding:5px 10px; <?php if(count($fourntotal[$fournname]) == count($pahtlist)){ ?>font-weight: bold;<?php } ?>">$total</td>
HTML;
          } ?>
               
          </tr>
     </table>
</div>
 <div id="stepfourn1" class="advgroup">
        <p style="padding:5px; margin:0;">1. Sélection des produits</p>
        <table>
            <tr>
                <td colspan="2"></td>
                <td align="center" style="background-color: #CCCCCC;"><strong>Qté</strong></td>
                <td align="center"><strong>Libellé</strong></td>
                <td align="center" style="background-color: #CCCCCC;"><strong>Marque<br />Réf. ACILYON</strong></td>
                <td align="center"><strong>Réf. fournisseur</strong></td>
                <td align="center" style="background-color: #CCCCCC;"><strong>Prix d'achat</strong></td>
                <td align="center"><strong>Eco part.</strong></td>
            </tr>
<?php

$price_display=(!is_null($offre_adv)?array("style"=>"display:none"):"");


$sql = "SELECT *";
$sql .= " FROM lignefacture";
$sql .= " WHERE factureid = '".$facturid."'";
$lignefactures = $DB_site2->query($sql);
$artids = array();

while($lignefacture = $DB_site2->fetch_array($lignefactures)){
     


     $sql = "SELECT a.*,am.*,m.*,af.ecopartht as ecopartfournht";
     $sql .= " FROM article a, article_fournisseur af, article_marque am, marque m";
     $sql .= " WHERE am.artid = a.artid";
     $sql .= " AND af.artid = a.artid";
     $sql .= " AND am.marqueid = m.marqueid";
     $sql .= " AND a.artid = ".$lignefacture['artid'];
     $article = $DB_site2->query_first($sql);
     
     $artids[$lignefacture['artid']] = $article['typearticleid'] ;
     
     $sql = "SELECT *";
     $sql .= " FROM envoi_fournisseur_ligne";
     $sql .= " WHERE type = 'fourn'";
     $sql .= " AND ligne_facture_id = ".$lignefacture['lignefactureid'];
     $envoi = $DB_site2->query_first($sql);
     
     $sql = "SELECT *";
     $sql .= " FROM lignefacturecaracteristique";
     $sql .= " WHERE lignefactureid = ".$lignefacture['lignefactureid'];
     $caracts = $DB_site2->query($sql);
     
     $lignestate = 1;
     $style = "";
     
     if($envoi['date'] != "") $style = "background-color:green;";
      

     if($adv->lignes[$lignefacture['lignefactureid']] > 0){
          
          foreach($fournlink[$article['artid']] as $fkey => $mails){
               $activefournkeys[fournkey($fkey)]++;
          }
          
     }

     $rowspan = $DB_site2->num_rows($caracts) + 1;
     
     $sql = "SELECT COUNT(*)";
     $sql .= " FROM article_lot";
     $sql .= " WHERE lotid = ".$lignefacture['artid'];
     $lot = $DB_site2->query_first($sql);
      
     $img = "";
     $path = "../configurations/".$host."/images/produits/br/".$lignefacture['artid'].".jpg";
     
     if(file_exists($path)){
          $img = "<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
     }
     
     if($article['libelleFR'] == ""){
          $article['libelleFR'] = $lignefacture['libelle'];
     }
     
     if($lot[0] == 0){
          
          $options = "";
          $optref  = "";
          
          $js_fireevent_checkbox = '';
          while($caract = $DB_site2->fetch_array($caracts)){
               
               //Permet de simuler un clique sur les checkbox des options, afin de les cocher/décocher automatiquement si l'on coche/décoche le produit
               $js_fireevent_checkbox .= "$('caract_".$lignefacture['lignefactureid']."[".$caract['caractvalid']."]').simulate('click');";
               
               //Listing des caractéristiques avec checkbox associées
               $ref="";
               if($caract['ref']!="") $ref=" (".$caract['ref'].")";
               $options .= ""
               . "<tr class=\"caracts\">"
                    . "<td style=\"background-color: #FFD8A5;\" align=\"right\">%CHECKBOX".$caract['caractvalid']."%</td>"
                    . "<td style=\"background-color: #FFD8A5;\"><strong>".$caract['libelle_caract']."</strong><br>".stripslashes($caract['libelle_caractval']).$ref."</td>"
                    . "<td align=\"center\" style=\"background-color: #FFD8A5;\">".$adv->nb_input("caractval_".$lignefacture['lignefactureid']."[".$caract['caractvalid']."]","adv_save(this.name)","",$caract['paht'])."</td>"
                    . "<td style=\"background-color: #FFD8A5;\">&nbsp</td>"
               . "</tr>";
               
               //Stockage de la checkbox dans une variable, afin de choisir si on souhaite l'afficher ou non (voir plus bas)
               $checkbox_options[$caract['caractvalid']] = $adv->checkbox("caract_".$lignefacture['lignefactureid']."[".$caract['caractvalid']."]","sumprod(); adv_save(%name)", $caract['caractvalid']);
               
               if($caract['ref']!=""){
                   $optref .= "-".$caract['ref'];
               }
          }
          
          if(!empty($options)){
               $rowspan += 1;
          }
          
          //Mise en place de la checkbox produit
          $artfourn="";
          if(isset($fournlink[$article['artid']])) $artfourn=implode("','",fournkey(array_keys($fournlink[$article['artid']])));
          $checkbox = "<input type=\"checkbox\" name=\"produits[]\" value=\"".$lignefacture['lignefactureid']."\" onclick=\"sumprod(); showstep('fourn2'); showlink(this,new Array('".$artfourn."')); updligne(".$lignefacture['lignefactureid'].",(this.checked?".$lignefacture['qte'].":0),2); ".$js_fireevent_checkbox."\" ".($adv->lignes[$lignefacture['lignefactureid']]>0?"checked":"").($adv->locked || !is_null($offre_adv)?" disabled":"").">";
     
          $oadv = $adv->listadv($facturid,"fourn",$lignefacture['lignefactureid']);
          
          $affiche_checkbox_options = true;
          if(count($oadv) > 0 && $oadv[0]->id != $adv->id){
               $checkbox = "";
               $style    = "background-color:#FFAA22;";
               $affiche_checkbox_options =  false;
          }
          
          //Pour chaque options, on n'affiche ou non la checkbox associée, selon l'état du traitement de la commande adv (ex : étape déjà validée, on n'affiche pas)
          if(!empty($checkbox_options)){
               foreach($checkbox_options as $id => $value){
               
                    if($affiche_checkbox_options){
                         $options = str_replace('%CHECKBOX'.$id.'%', $value, $options);
                    } else {
                         $options = str_replace('%CHECKBOX'.$id.'%', '&nbsp;', $options);
                    }

               }
          }
          
          $price_info="";
          if($offre_adv){
	      if(isset($adv->fields["listeprix_".$lignefacture['lignefactureid']])){
		  foreach($adv->fields["listeprix_".$lignefacture['lignefactureid']] as $paht=>$qte){
		      $price_info.=$qte."x ".number_format($paht,2,',','').$devises->symbole($fournisseur_devise[$fournname])."<br>";
		  }
	      }
	  }

          $paht_default = isset($offre->articles[$lignefacture['artid']]['paht']) ? $offre->articles[$lignefacture['artid']]['paht'] : $lignefacture['paht'];
          ?>
            
          <tr>
               <td rowspan="<?php echo $rowspan; ?>" style="<?php echo $style; ?>"><?php echo $checkbox; ?><input type="hidden" name="envoi_<?php echo $lignefacture['lignefactureid']; ?>" value="<?php echo $lignestate; ?>"></td>
               <td rowspan="<?php echo $rowspan; ?>"><?php echo $img; ?></td>
               <td align="center" style="background-color: #CCCCCC;" rowspan="<?php echo $rowspan; ?>"><input type="hidden" name="qte_<?php echo $lignefacture['lignefactureid']; ?>" value="<?php echo $lignefacture['qte']; ?>"><?php echo $lignefacture['qte']; ?></td>
               <td align="center" rowspan="<?php echo $rowspan; ?>"><?php echo $adv->textarea("desc_".$lignefacture['lignefactureid'],"adv_autosave(%name)",array("cols"=>28,"rows"=>3),$article['libelleFR']); ?></td>
               <td style="background-color: #CCCCCC;" align="center"><?php echo $article['libelle']; ?><br><?php echo $lignefacture['artcode']; ?></td>
               <td align="center"><?php echo $adv->text_input("ref_".$lignefacture['lignefactureid'],"adv_autosave(this.name)",array("size"=>30),$article['artcode_eurocave']); ?></td>
               <td align="center" style="background-color: #CCCCCC;"><?php echo $price_info.$adv->nb_input("price1_".$lignefacture['lignefactureid'],"adv_autosave(this.name)",$price_display,$paht_default); ?></td>
               <td align="center"><?php echo $adv->nb_input("ecopart_".$lignefacture['lignefactureid'],"adv_autosave(this.name)","",$article['ecopartfournht']); ?></td>
          </tr>
          
          <?php 
          
          if(!empty($options)){
          ?>

          <tr>
              <td colspan="4" align="center" style="background-color: #FFD8A5; font-size: 14px; font-weight: bold;"><img src="images/icon/avertissement.png" width="32" style="vertical-align:middle;"> Sélectionnez les options à commander</td>
          </tr>

          <?php

          }
          
          echo $options;
     
     } else {
     ?>
          <tr>
               <td style="color:red;">&#9888;</td>
               <td><?php echo $lignefacture['qte']; ?></td>
               <td>Cet article est composé de <?php echo $lot[0]; ?> articles qu'il faut <a href="facture.php?action=editer&facturid=<?php echo $facturid; ?>&diss=<?php echo $lignefacture['lignefactureid']; ?>#adv">dissocier</a> avant de les commander</td>
               <td><?php echo $article['libelle']; ?></td>
               <td><?php echo $lignefacture['artcode']; ?></td>
               <td colspan="4"></td>
          </tr>     
     <?php
     } 
}

?>
          <tr>
               <td colspan="6" align="right" style="padding:5px 10px; font-size: 13px;"><strong>Total :</strong></td>
               <td style="background-color:#00AA00; padding:5px 10px; color:white; font-weight: bold; font-size: 14px;" align="center" colspan="2" id="prod1total" nowrap></td>
          </tr>
          <tr>
               <td><?php echo $adv->checkbox("uncolis","adv_save(%name)",1); ?></td>
               <td colspan="4">Attention : mettre tous les articles dans 1 seul colis</td>
          </tr>
          <tr>
               <td><?php echo $adv->checkbox("palette","adv_save(%name)",1); ?></td>
               <td colspan="5">Attention : Article(s) à mettre sur palette + cerclage pour le transport</td>
          </tr>
          <tr>
               <td><?php echo $adv->checkbox("suremballage","adv_save(%name)",1); ?></td>
               <td colspan="4">Attention :  Prévoir un suremballage du produit + palette + cerclage pour le transport</td>
          </tr>
     </table>
</div>

<!--**********************-->
<!-- CHOIX DU FOURNISSEUR -->
<!--**********************-->
<div id="stepfourn2" class="advgroup" <?php echo isset($adv->steps[2]) ? "style=\"display:block;\"" : ""; ?>>
    <p style="padding:5px; margin:0;">2. Choix du fournisseur</p>
    <table <?php echo (!is_null($offre_adv) ? "style=\"display:none;\"" : "");?>>
        <tr>
            <td>
                <?php 
                $name = "fournisseur";
                $values = array(
                    "Aucun fournisseur" => 0
                );
                $tabJSAction = array(
                    "fournisseur_save(0)",
                    "showstep('fourn7')",
                    "setlieustock(0)",
                    "adv_save('fournisseur',7)"
                );
                echo $adv->radio_inputs($name, $values, implode(';',$tabJSAction)); 
                ?>
            </td>
            <td>
                <?php 
                $name = "fournisseur";
                $values = array(
                    "<a href=\"#\" title=\"Développeur\">Test</a>" => "webmaster4@mon-espace-cuisson.com"
                );
                $tabJSAction = array(
                    "showstep('fourn3')",
                    "setlieustock(0)",
                    "fournisseur_save('Test','EUR')",
                    "adv_save('fournisseur',3)",
                    "setpaht('test','EUR')"
                );
                $options = array(
                    "id" => "fourn0"
                );
                echo $adv->radio($name, $values, implode(';',$tabJSAction), $options); 
                ?>
            </td>
        </tr>
        <tr>
        <?php

        $i = 0;
        $c = 0;


        $fourn = array();
        
        foreach($fournlink as $artid => $list){
             foreach($list as $societe => $mails) $fourn[$societe] = $mails;
        }
              
        foreach($fourn as $group => $groupmails){
            
            $oFourn = new fournisseur($DB_site2, $group);

            $i++;
            $c++;

            $mailcsv = implode(";",array_unique($groupmails));
            $fkey    = fournkey($group);

            ?>

            <td id="<?php echo $fkey; ?>" style="<?php echo ($activefournkeys[$fkey] == 0 ? "display:none;" : ""); ?>">
                <?php 
                $name = "fournisseur";
                $values = array(
                    "<a href=\"#\" onclick=\"javascript:document.getElementById('fourn".$i."').checked=true;\" title=\"".$mailcsv."\">".ucfirst($group)."</a>" => $mailcsv
                );
                $tabJSAction = array(
                    "showstep('fourn3')",
                    "setlieustock(0)",
                    "fournisseur_save('".addslashes($group)."','".$fournisseur_devise[$group]."')",
                    "adv_save('fournisseur',3)",
                    "setpaht('".$fkey."','".$fournisseur_devise[$group]."')"
                );
                $options = array(
                    "id" => "fourn".$i
                );
                echo $adv->radio($name, $values, implode(';',$tabJSAction), $options);
                if($oFourn->fournisseur_note != '') echo "<div class=\"fourn_alerte\"><img src=\"" . BACK_URL . "/images/icon/agt_update_critical.png\" onmouseover=\"toggleDiv('#fourn_alerte_" . $oFourn->fournisseur_id . "').show();\" onmouseout=\"toggleDiv('#fourn_alerte_" . $oFourn->fournisseur_id . "').hide();\"><p id=\"fourn_alerte_" . $oFourn->fournisseur_id . "\">" . $oFourn->fournisseur_note . "</p></div>"; 
                ?>
            </td>

            <?php
                 
                 if($c==2){
                      $c=0;
                      print "</tr><tr>";
                 }
            }
            ?>
               
        </tr>
    </table>

    <?php
    if($adv->modif) print $adv->destinataire." (".$adv->fields['fournisseur'].")";
    if(!is_null($offre_adv)) print "<div><b>Offre ".$adv->destinataire."</b>&nbsp;<a href=\"facture.php?action=editer&facturid=".$facturid."&type=fourn&advid=".$adv->id."&offre=0#adv\">Annuler l'offre</a></div>"; 
    ?>
</div>

     <!--****************-->
     <!-- PIECES JOINTES -->
     <!--****************-->
     <div id="stepfourn3" class="advgroup" style="<?php echo isset($adv->steps[3]) ? 'display:block;' : ''; ?>">
          <p style="padding:5px; margin:0;">3. Pièces jointes</p>
          <table>
               <tr>
                    <td><?php echo $adv->checkbox("nopj","showstep('fourn4');adv_save(%name,4)","0"); ?></td>
                    <td>Aucune</td>
               </tr>
               <tr>
                    <td><?php echo $adv->checkbox("blpj","showstep('fourn4');adv_save(%name,4)","1"); ?></td>
                    <td>Bon de livraison</td>
               </tr>
               <tr>
                    <td><?php echo $adv->checkbox("blslspj","showstep('fourn4');adv_save(%name,4)","1"); ?></td>
                    <td>Bon de livraison autre adresse : <span id="blslsname"><?php echo isset($adv->fields['enlev2sls']) && !empty($adv->fields['enlev2sls']) ? $optsls[$adv->fields['enlev2sls']] : 'N/A'; ?></span></td>
               </tr>
               <?php
                    $r=$DB_site2->query("SELECT * FROM user_files WHERE facturid=".$adv->facturid." AND ext='pdf' ORDER BY name");
                    while($file=$DB_site2->fetch_array($r)){
                        print "<tr>
                            <td>".$adv->checkbox("pj[".$file['id']."]","showstep('fourn4');adv_save(%name,4)","1")."</td>
                            <td style=\"cursor:pointer;\" title=\"Voir le fichier\" onclick=\"window.open('userfiles/".$file['id'].".".$file['ext']."')\">".$file['name']."</td>
                        </tr>";
                    }
                    
               ?>
               <tr>
                    <td><?php echo $adv->checkbox("facturepj","showstep('fourn4');adv_save(%name,4)","1"); ?></td>
                    <td>Facture (pour commandes export uniquement)</td>
               </tr>
               <tr>
                    <td><?php echo $adv->checkbox("glspj","showstep('fourn4');adv_save(%name,4)","1"); ?></td>
                    <td>Bon GLS ou FEDEX Express</td>
                    <td><?php echo $adv->sendfile("bongls"); ?></td>
               </tr>
               <tr>
                    <td><?php echo $adv->checkbox("trsptpja4","showstep('fourn4');adv_save(%name,4)","1"); ?></td>
                    <td>Fragile + Note importante A4</td>
               </tr>
               <tr>
                    <td><?php echo $adv->checkbox("trsptpja5","showstep('fourn4');adv_save(%name,4)","1"); ?></td>
                    <td>Fragile + Note importante A5</td>
                    <td></td>
               </tr>
               <tr>
                    <td><?php echo $adv->checkbox("trsptoptpj","showstep('fourn4');adv_save(%name,4)","1"); ?></td>
                    <td>Etiquette transporteur<br>
                    <?php echo $adv->radio_inputs("trsptetiquette",array("Heppner Star Date"=>"stardate","Star Priority 13"=>"stardate13","Star Priority"=>"starpriority","Star Priority 13"=>"starpriority13","Schenker Top13"=>"top13","Top18"=>"top18"),"adv_save(%name,4)") ?>
                    </td>
                    <td></td>
               </tr>
               <!--<tr><td><input type="checkbox" name="identpj" value="1" onclick="showstep('fourn4');"></td><td colspan="2">Texte à inscrire sur colis : Commande N° $facturid De Ma cave à vin - ACILYON pour $rs_facture[nom] $rs_facture[prenom] $rs_facture[codepostal] $rs_facture[ville] $pays_fact[libelle]</td></tr>-->
               <!--<tr><td>".$adv->checkbox("otherpj","showstep('fourn4');adv_save(%name,4)","1")."</td><td>Autre : <input type="text" name="otherpjname" size="60"></td><td><input type="file" name="otherpjfile"></td></tr>-->
               <?php
               $item="";
               $rq_lignefacturediss=$DB_site2->query("SELECT a.artid,a.artcode,lf.libelle,lf.prix,lf.qte,lf.lignefactureid FROM lignefacturediss lf INNER JOIN article a USING(artid) WHERE lf.factureid = '".$facturid."' order by lf.artcode");
               while($lignefacturediss=$DB_site2->fetch_array($rq_lignefacturediss)){
                  $i=0;
                  $artimg_base = $DB_site2->query_first("select artid, image,artcode from article where artid = '".$lignefacturediss['artid']."'") ;
                  $artimg ="<div style=\"display:inline-block;\"><img src=\"https://dedie.mon-espace-cuisson.com/configurations/$host/images/produits/article/$artimg_base[artid].$artimg_base[image]?t=".time()."\" name=\"base\" width=\"100\" style=\"margin-right:15px;\">";
                  $artimg.="<p style=\"text-align:center;\">".$adv->checkbox("articlephoto_".$i,"adv_save(%name,4)",$lignefacturediss['artid'])."</p></div>"; 
                  $i++;
                  $articleimages=$DB_site2->query("select * from articlephoto where artid = '".$lignefacturediss['artid']."' ORDER BY position") ;
                  while ($articleimage=$DB_site2->fetch_array($articleimages)){
                    $artimg .="<div style=\"display:inline-block;\"><img src=\"https://dedie.mon-espace-cuisson.com/configurations/$host/images/produits/article/$articleimage[artid]_$articleimage[articlephotoid].$articleimage[image]?t=".time()."\" width=\"100\" style=\"margin-right:15px;\">";
                    $artimg.="<p style=\"text-align:center;\">".$adv->checkbox("articlephotosup_".$i,"adv_save(%name,4)",$articleimage[articlephotoid])."</p></div>"; 
                    $i++;
                    }
                    $item.="<div>Lot ".$artimg_base['artcode']."</div>".$artimg;
               }
               foreach($artids as $artid => $typeArticleid){
                   if($artid != 0) {
                       $artimg_base = $DB_site2->query_first("select artid, image,artcode from article where artid = '$artid'") ;
                       $artimg ="<div style=\"display:inline-block;\"><img src=\"https://dedie.mon-espace-cuisson.com/configurations/$host/images/produits/article/$artimg_base[artid].$artimg_base[image]?t=".time()."\" name=\"base\" width=\"100\" style=\"margin-right:15px;\">";
                       $artimg.="<p style=\"text-align:center;\">".$adv->checkbox("articlephoto_".$i,"adv_save(%name,4)",$artid)."</p></div>"; 
                       $i++;
                       $articleimages=$DB_site2->query("select * from articlephoto where artid = '$artid' ORDER BY position") ;
                       while ($articleimage=$DB_site2->fetch_array($articleimages)){
                          $artimg .="<div style=\"display:inline-block;\"><img src=\"https://dedie.mon-espace-cuisson.com/configurations/$host/images/produits/article/$articleimage[artid]_$articleimage[articlephotoid].$articleimage[image]?t=".time()."\" width=\"100\" style=\"margin-right:15px;\">";
                          $artimg.="<p style=\"text-align:center;\">".$adv->checkbox("articlephotosup_".$i,"adv_save(%name,4)",$articleimage[articlephotoid])."</p></div>"; 
                          $i++;
                     }
                     $item.="<div>".$artimg_base['artcode']."</div>".$artimg;
                }
           }

               
               ?>
               <tr>
                    <td><?php //echo $adv->checkbox("artimg","showstep('fourn4');adv_save(%name,4);","1"); ?></td>
                    <td>Photos article pour montage<br>
                         <?php echo $item; ?>
                    </td>
               </tr>

               <tr>
                    <td><?php echo $adv->checkbox("otherpj","showstep('fourn4');adv_save(%name,4);","1"); ?></td>
                    <td colspan="2">
                         Autre(s) :<br>
                         <input type="text" name="otherpjname" placeholder="Description de la pièce jointe" onblur="adv_save('otherpjname',4)" size="50" > <input type="file" name="otherpjfile"><br>
                         <input type="text" name="otherpjname_bis" placeholder="Description de la pièce jointe" onblur="adv_save('otherpjname_bis',4)" size="50" > <input type="file" name="otherpjfile_bis">
                    </td>
               </tr>
               
          </table>
     </div>
<?php

if(!$adv->locked && $adv->fields["typecmd"]=="delete") $adv->save_field("typecmd","new");
if($adv->locked) $adv->save_field("typecmd","delete");
if($adv->modif) $adv->save_field("typecmd","modif");

?>
<div id="stepfourn4" class="advgroup" <?php echo (isset($adv->steps[4]) ? "style=\"display:block;\"" : ""); ?>>
     <p style="padding:5px; margin:0;">4. Objet du mail</p>
     <span id="newcmd" <?php echo ($adv->modif || $adv->locked ? "style=\"display:none;\"" : ""); ?>><?php echo $adv->radio_inputs("typecmd", array("Nouvelle commande" => "new"),"showstep('fourn5'); adv_save('typecmd',5);"); ?></span>
     <span id="delcmd" <?php echo ($adv->modif || !$adv->locked ? "style=\"display:none;\"" : ""); ?>><?php echo $adv->radio("typecmd", array("Annuler commande" => "delete"),"showstep('fourn7'); adv_save('typecmd',7);"); ?></span>
     <span id="modifcmd" <?php echo (!$adv->modif ? " style=\"display:none;\"" : ""); ?>><?php echo $adv->radio("typecmd", array("Modifier commande"=>"modif"), "showstep('fourn7');adv_save('typecmd',7);"); ?></span>
     <!--<input type="radio" name="mailsubject" value="cmd" onclick="document.getElementsByName('intro')[0].checked=true;document.getElementsByName('endtxt2')[0].checked=true;" checked> Commande n°$facturid
     <br><input type="radio" name="mailsubject" value="annule" onclick="document.getElementsByName('intro')[1].checked=true;document.getElementsByName('endtxt2')[1].checked=true;"> Annulation de la commande n°$facturid-->
</div>
<?php

// <option value=\"Mazet Colmar<br>ZI Nord - BP 257<br>68005 Colmar<br>Tél : 03.89.21.09.40 Fax : 03.89.21.09.49\">Mazet Colmar</option><option value=\"Mazet Gonesse<br>1 RD 370 - BP 45<br>95500 Gonesse<br>Tél : 01.34.07.72.00 Fax : 01.39.87.07.37\">Mazet Gonesse</option><option value=\"Mazet Montelimar<br>ZI Du Gournier<br>26205 Montélimar<br>Tél : 04.75.53.15.90 Fax : 04.75.00.72.29\">Mazet Montelimar</option><option value=\"Mory Valence<br>45 Avenue Marseille<br>26000 Valence<br>Tél : 04.75.78.60.12\">Mory Valence</option
// $lieuxlivr=$DB_site2->query("SELECT * FROM fournisseur WHERE `type`='

if($rs_facture['modelivraison'] == "1"){
     $slschecked = 1;
     $slsstyle="color:red;";
} else {
     $slschecked=null;
     $slsstyle="";
}

?>
<div id="stepfourn5" class="advgroup" <?php echo (isset($adv->steps[5]) ? " style=\"display:block;\"" : ""); ?>>
     <p style="padding:5px; margin:0 0 5px 0;">5. Mode de transport</p>
     <span style="font-weight: bold;">Date livraison commande : <?php echo $datelivrtxt; ?></span>
     <table>
          <tr>
               <td valign="top">
               <?php 
                    $name = "trsptmode";
                    $values = array(
                        "Enlevement à notre charge en vos locaux" => "parenlevement"
                    );
                    $tabJSAction = array(
                        "sumprod()",
                        "selectfournlivr(this.value)",
                        "adv_save('trsptmode')"
                    );
                         
                    echo $adv->radio_inputs($name, $values, implode(';', $tabJSAction)); 
               ?>
                    <div id="parenlevement" style="margin: 10px 0 0 30px; <?php echo ($adv->fields['trsptmode'] != "parenlevement" ? "display:none;" : ""); ?>"> 
                         <p class="normal">
                         Transporteur :  
                    <?php 
                         $name = "selecttrspt";
                         $values = $opt;
                         $tabJSAction = array(
                             "showstep('fourn6')",
                             "showstep('fourn7')",
                             "adv_save(this.name,'6,7')"
                         );
                         
                         echo $adv->select($name, $values, implode(';', $tabJSAction)); 
                    ?>
                         </p>
                         <p class="normal" style="<?php echo $slsstyle; ?>">
                    <?php 
                         $name = "chkenlev2sls";
                         $tabJSAction = array(
                             "sumprod()",
                             "adv_save(%name)"
                         );
                         $option = 1;
                         $values = $slschecked;
                         
                         echo $adv->checkbox($name, implode(';', $tabJSAction), $option, $values); 
                    ?> 
                         Installation par SLS :  
                    <?php 
                         $name = "enlev2sls";
                         $values = $optsls;
                         $tabJSAction = array(
                             "document.getElementById('blslsname').innerHTML=this.options[this.selectedIndex].text",
                             "adv_save(this.name)"
                         );
                         
                         echo $adv->select($name, $values, implode(';', $tabJSAction)); 
                    ?>
                         </p>
                        <p class="normal">
                    <?php
                         $name = "trspt";
                         $values = array(
                             "Livraison chez le client" => "fournisseur"
                         );
                         $tabJSAction = array(
                             "sumprod()",
                             "showstep('fourn6')",
                             "showstep('fourn7')",
                             "adv_save('trspt','6,7')"
                         );
                         
                         echo $adv->radio_inputs($name, $values, implode(';', $tabJSAction)); 
                    ?>
                         </p>
                         <p class="normal" style="<?php echo $slsstyle; ?>">
                    <?php 
                         $name = "trspt";
                         $values = array(
                             "" => "asls"
                         );
                         $tabJSAction = array(
                             "sumprod()",
                             "showstep('fourn6')",
                             "showstep('fourn7')",
                             "adv_save('trspt','6,7')"
                         );
                         
                         echo $adv->radio($name, $values, implode(';', $tabJSAction)).'Livraison chez SLS : ';
 
                         $name = "fourn2sls";
                         $values = $optsls;
                         $tabJSAction = array(
                             "document.getElementById('blslsname').innerHTML = this.options[this.selectedIndex].text",
                             "adv_save(this.name)"
                         );
                         
                         echo $adv->select($name, $values, implode(';', $tabJSAction)); 
                    ?>
                         </p>
                         <p class="normal">
                    <?php 
                         $name = "trspt";
                         $values = array(
                             "Livraison chez le transporteur : " => "livraisonmazet"
                         );
                         $tabJSAction = array(
                             "sumprod()",
                             "showstep('fourn6')",
                             "showstep('fourn7')",
                             "adv_save('trspt','6,7')"
                         );
                         
                         echo $adv->radio($name, $values, implode(';', $tabJSAction));
                         
                         $name = "selectmazet2";
                         $values = $optlieutrspt;
                         $tabJSAction = array(
                             "document.getElementById('blslsname').innerHTML = this.options[this.selectedIndex].text",
                             "adv_save(this.name)"
                         );
                         
                         echo ' '.$adv->select($name, $values, implode(';', $tabJSAction)); 
                    ?>
                         </p>
                         <p class="normal">
                    <?php 
                         $name = "trspt";
                         $values = array(
                             "Livraison à notre stock : " => "stockmcav"
                         );
                         $tabJSAction = array(
                             "sumprod()",
                             "showstep('fourn6')",
                             "showstep('fourn7')",
                             "adv_save('trspt','6,7')"
                         );
                         
                         echo $adv->radio($name, $values, implode(';', $tabJSAction));
                         
                         $name = "selectstockmcav2";
                         $values = $optstock;
                         $tabJSAction = array(
                             "document.getElementById('blslsname').innerHTML = this.options[this.selectedIndex].text",
                             "adv_save(this.name)"
                         );
                         
                         echo ' '.$adv->select($name, $values, implode(';', $tabJSAction)); 
                    ?>
                         </p>
                         <p class="normal">
                    <?php 
                         $name = "trspt";
                         $values = array(
                             "Livraison en relais colis : " => "relaiscolis"
                         );
                         $tabJSAction = array(
                             "sumprod()",
                             "showstep('fourn6')",
                             "showstep('fourn7')",
                             "adv_save('trspt','6,7')"
                         );
                         
                         echo $adv->radio($name, $values, implode(';', $tabJSAction));
                         
                         $name   = "relaiscolis_adresse2";
                         $action = "adv_autosave(%name)";
                         $params = array(
                              "cols" => 25,
                              "rows" => 5
                         );

                         echo '<br>'.$adv->textarea($name, $action, $params); 
                    ?>
                         </p>
                    </div>
               </td>
               <td id="tdparenlevement" style="border-bottom:solid thin #808080; border-left:solid thin #808080; <?php echo ($adv->fields['trsptmode'] != "parenlevement" ? "display:none;" : ""); ?>">
                    Date départ maximum
                    <br>
                    <?php 
                    $name   = "parenlevindate";
                    $action = "adv_calsave";
                    
                    echo $adv->cal_input($name, $action); 
                    ?>
               </td>
          </tr>
          <tr>
               <td valign="top">
               <?php 
                    $name = "trsptmode";
                    $values = array(
                         "Enlèvement par le destinataire final" => "parenlevementdestinataire"
                    );
                    $tabJSAction = array(
                         "sumprod()",
                         "selectfournlivr(this.value)",
                         "adv_save('trsptmode')",
                         "showstep('fourn6')",
                         "showstep('fourn7')",
                    );
                         
                    echo $adv->radio_inputs($name, $values, implode(';', $tabJSAction)); 
               ?>
                    <div id="parenlevementdestinataire"></div>
               </td>
               <td id="tdparenlevementdestinataire" style="border-left:solid thin black; <?php echo ($adv->fields['trsptmode'] != "parenlevementdestinataire" ? "display:none;" : ""); ?>">
                    Date enlèvement
                    <?php echo $adv->cal_input("parenlevementdestinatairedate", "adv_calsave"); ?>
               </td>
          </tr>
          <tr>
               <td valign="top">
                    <?php 
                    $name = "trsptmode";
                    $values = array(
                        "Livraison par vos soins par votre transporteur" => "parvossoins"
                    );
                    $tabJSAction = array(
                        "sumprod()",
                        "selectfournlivr(this.value)",
                        "adv_save('trsptmode')"
                    );
                    
                    echo $adv->radio($name, $values, implode(';', $tabJSAction)); 
                    ?>
                    <div id="parvossoins" style="margin: 10px 0 0 30px; <?php echo ($adv->fields['trsptmode']!="parvossoins" ? "display:none;" : ""); ?>">
                         <p class="normal">
                    <?php
                         $name = "trspt";
                         $values = array(
                             "Chez notre client" => "fournisseur"
                         );
                         $tabJSAction = array(
                             "sumprod()",
                             "showstep('fourn6')",
                             "showstep('fourn7')",
                             "adv_save('trspt','6,7')"
                         );
                         
                         echo $adv->radio_inputs($name, $values, implode(';', $tabJSAction)); 
                    ?>
                         </p>
                         <!--<p class="normal">
                    <?php 
                         /*$name = "trspt";
                         $values = array(
                             "Par colissimo chez notre client" => "colissimo"
                         );
                         $tabJSAction = array(
                             "sumprod()",
                             "showstep('fourn6')",
                             "showstep('fourn7')",
                             "adv_save('trspt','6,7')"
                         );
                         
                         echo $adv->radio($name, $values, implode(';', $tabJSAction));*/
                    ?>
                         </p>-->
                         <p class="normal" style="<?php echo $slsstyle; ?>">
                    <?php 
                         $name = "trspt";
                         $values = array(
                             "" => "asls"
                         );
                         $tabJSAction = array(
                             "sumprod()",
                             "showstep('fourn6')",
                             "showstep('fourn7')",
                             "adv_save('trspt','6,7')"
                         );
                         
                         echo $adv->radio($name, $values, implode(';', $tabJSAction)).'Chez SLS : ';
 
                         $name = "fournlivrsls";
                         $values = $optsls;
                         $tabJSAction = array(
                             "document.getElementById('blslsname').innerHTML = this.options[this.selectedIndex].text",
                             "adv_save(this.name)"
                         );
                         
                         echo $adv->select($name, $values, implode(';', $tabJSAction)); 
                    ?>
                         </p>
                         <p class="normal">
                    <?php 
                         $name = "trspt";
                         $values = array(
                             "Chez le transporteur : " => "livraisonmazet"
                         );
                         $tabJSAction = array(
                             "sumprod()",
                             "showstep('fourn6')",
                             "showstep('fourn7')",
                             "adv_save('trspt','6,7')"
                         );
                         
                         echo $adv->radio($name, $values, implode(';', $tabJSAction));
                         
                         $name = "selectmazet";
                         $values = $optlieutrspt;
                         $tabJSAction = array(
                             "document.getElementById('blslsname').innerHTML = this.options[this.selectedIndex].text",
                             "adv_save(this.name)"
                         );
                         
                         echo ' '.$adv->select($name, $values, implode(';', $tabJSAction)); 
                    ?>
                         </p>
                         <p class="normal">
                    <?php 
                         $name = "trspt";
                         $values = array(
                             "A notre stock : " => "stockmcav"
                         );
                         $tabJSAction = array(
                             "sumprod()",
                             "showstep('fourn6')",
                             "showstep('fourn7')",
                             "adv_save('trspt','6,7')"
                         );
                         
                         echo $adv->radio($name, $values, implode(';', $tabJSAction));
                         
                         $name = "selectstockmcav";
                         $values = $optstock;
                         $tabJSAction = array(
                             "document.getElementById('blslsname').innerHTML = this.options[this.selectedIndex].text",
                             "adv_save(this.name)"
                         );
                         
                         echo ' '.$adv->select($name, $values, implode(';', $tabJSAction)); 
                    ?>
                         </p>
                         <p class="normal">
                    <?php 
                         $name = "trspt";
                         $values = array(
                             "En relais colis, à l'adresse : " => "relaiscolis"
                         );
                         $tabJSAction = array(
                             "sumprod()",
                             "showstep('fourn6')",
                             "showstep('fourn7')",
                             "adv_save('trspt','6,7')"
                         );
                         
                         echo $adv->radio($name, $values, implode(';', $tabJSAction));
                         
                         $name   = "relaiscolis_adresse";
                         $action = "adv_autosave(%name)";
                         $params = array(
                              "cols" => 25,
                              "rows" => 5
                         );

                         echo '<br>'.$adv->textarea($name, $action, $params); 
                    ?>
                         </p>
                         <p class="normal">
                    <?php 
                         $name = "trspt";
                         $values = array(
                             "Chez un autre fournisseur : " => "fourn"
                         );
                         $tabJSAction = array(
                             "sumprod()",
                             "showstep('fourn6')",
                             "showstep('fourn7')",
                             "adv_save('trspt','6,7')"
                         );
                         
                         echo $adv->radio($name, $values, implode(';', $tabJSAction));
                         
                         $name   = "autrefourn";
                         $action = "adv_autosave(%name)";
                         $params = array(
                              "cols" => 25,
                              "rows" => 5
                         );
                         $tabJSAction = array(
                             "document.getElementById('blslsname').innerHTML = this.options[this.selectedIndex].text",
                             "adv_save(this.name)"
                         );
                         $values=array(""=>"-- Sélectionner --");
                         $req=$DB_site2->query("SELECT id,societe,cp,ville FROM fournisseur WHERE type='fourn' ORDER BY societe");
                         while($fourn=$DB_site2->fetch_array($req)){
                            $values[$fourn['id']]=sprintf("%s %s (%s)",$fourn['societe'],$fourn['ville'],substr($fourn['cp'],0,2));
                         }
                         
                         echo ' '.$adv->select($name, $values, implode(';', $tabJSAction));
                    ?>
                         </p>
                         <p class="normal">
                    <?php 
                         $name = "trspt";
                         $values = array(
                             "Avec installation par votre installateur" => "installsls"
                         );
                         $tabJSAction = array(
                             "sumprod()",
                             "showstep('fourn6')",
                             "showstep('fourn7')",
                             "adv_save('trspt','6,7')"
                         );
                         
                         echo $adv->radio($name, $values, implode(';', $tabJSAction)); 
                    ?>
                         </p>
                         <br>
                         <p class="normal">
                    <?php
                         $name = "chkinfotrspt";
                         $action = "adv_save(%name)";
                         $option = 1;
                         
                         echo $adv->checkbox($name, $action, $option); 
                    ?> 
                         Info transporteur : 
                    <?php 
                         $name = "infofournothertxt";
                         $action = "adv_autosave(this.name)";
                         $option = array(
                             "size" => 60
                         );
                         
                         echo $adv->text_input($name, $action, $option); 
                    ?>
                         </p>
                    </div>
               </td>
               <td id="tdparvossoins" style="border-left:solid thin black; <?php echo ($adv->fields['trsptmode'] != "parvossoins" ? "display:none;" : ""); ?>">
                    Date départ maximum
                    <br>
               <?php
                    $name   = "parvossoinsindate";
                    $action = "adv_calsave";
                         
                    echo $adv->cal_input($name, $action); 
               ?>
                    <br><br>
               <?php 
                    $name = "typedatelivrfourn";
                    $values = array(
                        "Date livraison au plus tard" => "max"
                    );
                    $tabJSAction = array(
                        "adv_save('typedatelivrfourn')"
                    );
                         
                    echo $adv->radio_inputs($name, $values, implode(';', $tabJSAction)); 
               ?>
                    <br>
               <?php 
                    $name = "typedatelivrfourn";
                    $values = array(
                        "Livraison impérative" => "imperative"
                    );
                    $tabJSAction = array(
                        "adv_save('typedatelivrfourn')"
                    );
                    
                    echo $adv->radio($name, $values, implode(';', $tabJSAction)); 
               ?>
                    <br>
                    le 
               <?php
                    $name   = "parvossoinsoutdate";
                    $action = "adv_calsave";
                    
                    echo $adv->cal_input($name, $action); 
               ?>
                    <br><br>
                    Cout du transport HT 
               <?php 
                    $name   = "mazetcout";
                    $action = "adv_autosave(this.name)";
                    $values = array(
                        "onblur" => "sumprod()"
                    );
                    
                    echo $adv->nb_input($name, $action, $values); 
               ?>
               </td>
          </tr>
          <tr>
               <td valign="top">
                    <?php 
                    $name = "trsptmode";
                    $values = array(
                        "Intervention par vos soins" => "intervparvossoins"
                    );
                    $tabJSAction = array(
                        "sumprod()",
                        "selectfournlivr(this.value)",
                        "adv_save('trsptmode')"
                    );
                    
                    echo $adv->radio($name, $values, implode(';', $tabJSAction)); 
                    ?>
                    <div id="intervparvossoins" style="margin: 10px 0 0 30px; <?php echo ($adv->fields['trsptmode'] != "intervparvossoins" ? "display:none;" : ""); ?>">
                         <?php
                         $name = "trspt";
                         $values = array(
                             "Chez notre client" => "fournisseur"
                         );
                         $tabJSAction = array(
                             "sumprod()",
                             "showstep('fourn6')",
                             "showstep('fourn7')",
                             "adv_save('trspt','6,7')"
                         );
                         
                         echo $adv->radio_inputs("trspt",array("Chez notre client"=>"fournisseur"),"sumprod();showstep('fourn6');showstep('fourn7');adv_save('trspt','6,7')"); 
                         ?>
                         <br>
                         Date d'intervention souhaitée (au plus tard) : 
                         <?php 
                         $name   = "trsptdateinterv";
                         $action = "adv_calsave";
                         
                         echo $adv->cal_input($name, $action); 
                         ?>
                    </div>
               </td>
               <td id="tdintervparvossoins"></td>
          </tr>
     </table>
</div>
<div id="stepfourn6" class="advgroup" <?php echo (isset($adv->steps[6]) ? " style=\"display:block;\"" : ""); ?>>
     <p style="padding:5px; margin:0;">6. Prix</p>
     <table>
          <tr>
               <td>Prix des produits</td>
               <td id="prixprodtotal"></td>
          </tr>
          <tr>
               <td>Eco participation</td>
               <td id="prixecoparttotal"></td>
          </tr>
          <tr>
               <td>Transport</td>
               <td id="prixtrspttotal"></td>
          </tr>
          <tr>
               <td>Garantie fournisseur</td>
               <td id="prixgartotal"></td>
          </tr>
          <tr>
               <td>Montant total commande HT :</td>
               <td id="prixfourntotal"></td>
          </tr>
     </table>
     <br>
     <!--Informations supplémentaires :<br><textarea cols="80" onkeyup="adv_save('infocomplement')" name="infocomplement">".$adv->fields["infocomplement"]."</textarea>-->
     <?php 
     $name   = "infocomplement";
     $action = "";
     $params = array(
          "cols" => 80,
          "rows" => 3,
          "allow" => 1
     );
     
     echo $adv->textarea($name, $action, $params);
     echo "<input type=\"button\" onclick=\"adv_save('infocomplement')\" value=\"ok\" />"; 
     ?>
</div>
<?php
$envoidate = null;
if($adv->envoi>0) $envoidate=date("d/m/Y",$adv->envoi);

$henvoi="";

if($adv->destinataire!=""){
     
     $sql = "SELECT *";
     $sql .= " FROM adv_heure";
     $sql .= " WHERE fournisseur = '".addslashes($adv->destinataire)."'";
     $h = $DB_site2->query_first($sql);
     
     $henvoi = "à ".intval($h['heure'])."h";
}

?>
          <div id="stepfourn7" <?php echo (isset($adv->steps[7]) ? "style=\"display:block;\"" : ""); ?>>
            <div class="loader-adv"><img src="<?php echo BACK_URL."/images/loader.gif"; ?>"></div>
               <input type="button" value="Aperçu" class="preview" onclick="valid1(true);">
               <div style="text-align:left; <?php echo ($adv->locked ? "display:none;" : ""); ?>">
                    <br>
                    <input type="radio" name="envoiauto" onclick="envoidate_save();" <?php echo ($adv->envoi > 0 ? "checked" : ""); ?>> Envoyer la commande automatiquement <span id="envoi1" style="<?php echo ($adv->envoi == 0 ? "display:none;" : ""); ?>">le <input type="text" id="envoidate" value="<?php echo $envoidate; ?>" size="10" onclick="calendrier_show(this.id,this.value,undefined,envoidate_save);" readonly> <span id="henvoi"><?php echo $henvoi; ?></span> <input type="button" value="Valider" class="clock" onclick="document.location='facture.php?action=editer&facturid=<?php echo $facturid; ?>#adv'"></span>
                    <br><br>
                    <input type="radio" name="envoiauto" onclick="envoidate_save();" <?php echo ($adv->envoi == 0 ? "checked" : ""); ?>> Envoyer la commande immédiatement <span id="envoi0" style="<?php echo ($adv->envoi > 0 ? "display:none;" : ""); ?>"><input type="button" onclick="valid1()" value="Envoi immédiat" /></span>
               </div>
               <div style="<?php echo ($adv->locked ? "" : "display:none;"); ?>;margin-top:10px;"><input type="button" onclick="valid1()" value="Annuler la commande" style="color:red;font-weight:bold;" /></div>
          </div>
     </div>
     <input type="hidden" name="advid" value="<?php echo $adv->id; ?>">
</form>
