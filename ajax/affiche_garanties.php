<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$facture_id      = $_GET['facture_id'];
$destinataire_id = $_GET['destinataire_id'];

$tauxTVA = $template->tva_ref();
$taux20 = $tauxTVA[1];

if($destinataire_id > 0){
     
     //Récupération des informations sur la commande
     $fcl = new facture($DB_site, $facture_id);
     $fcl->lignes();

     //Récupération du template des garanties, par article
     foreach($fcl->lignes as $lfid => $lignefacture){

          $TemplateCde1BitGarantieBit = '';

          //Récupération des garanties disponible pour le produit
          $lesGaranties = DAOgarantie::loadMaxGarantieArtByPays($DB_site,$template->site_code(),'FR',$user_info['paysid'],$lignefacture->artid,$destinataire_id,$lignefacture->prix_nogarantie,$lignefacture->tva);
          
          $dureeBase             = 0;
          $garantieDeBaseAffiche = "";

          //Si le produit a des garanties disponibles
          if(count($lesGaranties) > 0){

               //On boucle sur chacune d'entre elles
               for($i=0; $i < count($lesGaranties); $i++){

                    $duree        = $lesGaranties[$i]->duree;
                    $dureeAf      = $duree;
                    $typeGarantie = $template->translate("/article/garantie_type/garantie",'F');

                    //Si la garantie en cours est une extension
                    if ($lesGaranties[$i]->cumul == 1){

                         //On cherche dans le tableau qui contient toutes les garanties offertes celle du fournisseur de produit
                         $pointer = DAOgarantie::searchGarantieToAddExtension($DB_site,$lesGaranties[$i]->type_garantie,$lesGaranties,$lesGaranties[$i]->garantieid);

                         //On prend la durée de cette garantie pour l'ajouter à l'extension
                         $dureeAf += $lesGaranties[$pointer]->duree;
                         $typeGarantie = $template->translate("/article/garantie_type/extension_garantie",'F')." +";

                    }
                    //Pour orthographier "an" correctement
                    $an = $template->translate("/article/garantie_type/an");
                    if($duree>1) $an = $template->translate("/article/garantie_type/ans");

                    //Calculs pour déterminer la meilleure garantie incluse/gratuite
                    if($i == 0){

                         $ppi           = $lesGaranties[$i]->ppi;
                         $prix          = $lesGaranties[$i]->prix;
                         $dureeBase     = $lesGaranties[$i]->duree;
                         $dureeDeBaseAf = $dureeAf;
                         
                         //Durée de la garantie (limitée ou "à vie")
                         if($dureeAf != 99){

                              $anneeGarantie = $dureeAf." ".$an;

                         } else {

                              $anneeGarantie = $template->translate("/article/garantie_type/a_vie",'F');

                         }

                         $garantieDeBaseAffiche = $anneeGarantie." ".$template->translate("/article/garantie_type/".$lesGaranties[$i]->type_garantie);
                         $garantieID            = $lesGaranties[$i]->garantieid;
                         $pointer = 0;
                         $dest_id = $lesGaranties[$i]->destinataire_garantie;
                         if($lesGaranties[$i]->cumul == 1){

                                 //On cherche dans le tableau qui contient toutes les garanties offertes celle du fournisseur de produit
                                 $pointer = DAOgarantie::searchGarantieToAddExtension($DB_site,$lesGaranties[$i]->type_garantie,$lesGaranties,$lesGaranties[$i]->garantieid);
                                 //On prend la durée de cette garantie pour l'ajouter à l'extension
                                 $ppi       += $lesGaranties[$pointer]->ppi;
                                 $prix      += $lesGaranties[$pointer]->prix;
                                 $dureeBase += $lesGaranties[$pointer]->duree;

                         }

                         $auLieuDe = "";

                         if($ppi > $prix){

                              $auLieuDe = " ".$template->translate("/article/garantie_type/au_lieu_de")." ".$template->price_format($ppi);

                         }
                    }

                    //Intitulé de la ligne de garantie
                    $libGarantie = $typeGarantie." ".$duree." ".$an." ".$template->translate("/article/garantie_type/".$lesGaranties[$i]->type_garantie)." ".DAOpays::getPaysById($DB_site,$user_info['paysid']);

               }

          }

          if($garantieDeBaseAffiche != ""){

               //Cocher par defaut pas de garantie sup
               $checked = 'checked';

               //Construction du formulaire de choix de la garantie
               /*$TemplateCde1BitGarantieBit .= "<form method=\"post\" name=\"frmchoixGarantie".$lignefacture->lignefactureid."\" action=\"cde225.php\">";
               $TemplateCde1BitGarantieBit .= "<input type='hidden' value='".$destinataire_id."' name='destinataire_id'>";
               $TemplateCde1BitGarantieBit .= "<input type='hidden' value='".$facture_id."' name='factureid'>";
               $TemplateCde1BitGarantieBit .= "<input type='hidden' value='garantie' name='action2'>";
               $TemplateCde1BitGarantieBit .= "<input type='hidden' value='".$lignefacture->artid."' name='numArt'>";
               $TemplateCde1BitGarantieBit .= "<input type='hidden' value='".$lignefacture->lignefactureid."' name='IDLigne'>";
               $TemplateCde1BitGarantieBit .= "<div class=\"bloc_garantie_base\">";*/

               $date = new DateTime(date('Y-m-d'));
               if($dureeDeBaseAf != ""){

                    $date->modify('+'.$dureeDeBaseAf.' year');
                    $date->modify('-1 day');

               }
               $date = $template->translate("/site/mois/".$date->format('n'))." ".$date->format('Y');
               $validForm = "document.forms.frmchoixGarantie.submit();";
               /************************************      PLATINE HOME   ***********************************************/
               //on vérifie si l'article est compatible avec la garantie platinehome
               $platinehome = DAOgarantie::platinehomeID($DB_site);

               if(DAOgarantie::platinehome($DB_site,$platinehome,$lignefacture,$user_info['raisonsociale'],$fcl->paysid)){

                    //si c'est un lot on affiche le nombre d'abonnements en fonction des articles eligibles du lot ex: 2 x 11€ / mois
                    if(DAOarticle::isLot($DB_site, $lignefacture->artid)){
                         $count = 0;
                          $tabArticlesLot = DAOarticle::listeArticlesLots($DB_site, $lignefacture->artid);
                          foreach ($tabArticlesLot as $art ) {
                              if(DAOgarantie::platinehomeArticles($DB_site, $art['artid'], $platinehome))
                                   $count ++;
                          }
                    }else{
                         $count = 1;
                    }

                    $prixArticle = $lignefacture->totalttc / $lignefacture->qte;
                    $categProduit = "Cave à vin";
                    //on appel le web service pour connaitre le tarif et le nom de garantie
                    include($_SERVER['DOCUMENT_ROOT']."/platinehome_categ.php");

                    //initialisation des select pour la date de naissance
                    $tabJJ = "";
                    for ($j=1; $j < 32 ; $j++) {
                         $value = str_pad(strval($j), 2, "0", STR_PAD_LEFT);
                         $tabJJ .= "<option value=\"".$value."\">". $value ."</option>";
                    }
                    $tabMM = "";
                    for ($j=1; $j < 13 ; $j++) { 
                         $value = str_pad(strval($j), 2, "0", STR_PAD_LEFT);
                         $tabMM .= "<option value=\"".$value."\">". $value ."</option>";
                    }
                    $tabAA = "";
                    for ($j=(date('Y') - 18); $j > 1900  ; $j--) { 
                         $value = strval($j);
                         $tabAA .= "<option value=\"".$value."\">". $value ."</option>";
                    }
                    $validForm = "checkGarantieMulti()";
                    $TemplateFormPlatineHome = ""
                    ."<div id=\"form_platine_home_$lfid\" class=\"row form_platine_home\" style=\"display:none;\">"
                         ."<input type=\"hidden\" name=\"offreplatine_$lfid\" value=\"".$offer_id."\" >"
                         ."<input type=\"hidden\" name=\"count_$lfid\" value=\"".$count."\" >"
                         ."<input type=\"hidden\" name=\"lfid\" value=\"".$lfid."\" >"
                         ."<div class=\"col-xs-12 col-sm-6 col-md-6 col-lg-6\">"
                              ."<p class=\"titre\"> ".$template->translate("/garantie/platine_home/infos_perso")."<p>"
                              ."<div class=\"col-xs-12 bloc_form no-padding\">"
                                   ."<p>".$template->translate("/garantie/platine_home/datenaissance")." </p>"     
                                   ."<select id=\"datenaissancephjj_$lfid\" name=\"datenaissancephjj_$lfid\" class=\"datenaissanceph\">".$tabJJ."</select>"
                                   ."<select id=\"datenaissancephmm_$lfid\" name=\"datenaissancephmm_$lfid\" class=\"datenaissanceph\">".$tabMM."</select>"
                                   ."<select id=\"datenaissancephaa_$lfid\" name=\"datenaissancephaa_$lfid\" class=\"datenaissancephaa\">".$tabAA."</select><br>"
                                   ."<input type=\"hidden\" id=\"datenaissancepherr_$lfid\" name=\"datenaissancepherr\" value=\"".$template->translate("/garantie/platine_home/naissance_erreur")."\">"
                                   ."<p>".$template->translate("/garantie/platine_home/telephone")."</p>"
                                   ."<input type=\"text\" id=\"telephoneph_$lfid\" name=\"telephoneph_$lfid\" placeholder=\"".$template->translate("/garantie/platine_home/telephone_placeholder")."\">"
                                   ."<input type=\"hidden\" id=\"telephonepherr_$lfid\" name=\"telephonepherr\" value=\"".$template->translate("/garantie/platine_home/telephone_erreur")."\">"
                              ."</div>"          
                         ."</div>"
                         ."<div class=\"col-xs-12 col-sm-6 col-md-6 col-lg-6\">"
                              ."<p class=\"titre\"> ".$template->translate("/garantie/platine_home/infos_bq")."</p>"
                              ."<div class=\"col-xs-12 bloc_form no-padding\">"
                                   ."<p>".$template->translate("/garantie/platine_home/iban")."</p>"
                                   ."<input type=\"text\" id=\"ibanph_$lfid\" name=\"ibanph_$lfid\">"
                                   ."<input type=\"hidden\" id=\"ibanpherr_$lfid\" name=\"ibanpherr\" value=\"".$template->translate("/garantie/platine_home/iban_erreur")."\">"
                                   ."<!--<p>BIC</p>"
                                   ."<input type=\"text\" name=\"bicph\">-->"
                              ."</div>"
                         ."</div>"
                         ."<div class=\"col-xs-12 conditions\">"
                              ."<div class=\"col-xs-1 accepteph\"><input type=\"checkbox\" id=\"accepte1_$lfid\" name=\"accepte1_$lfid\"></div><div class=\"col-xs-11 no-padding\"><p>".$template->translate("/garantie/platine_home/conditions1")."</p></div>"
                              ."<br>"
                              ."<div class=\"col-xs-1 accepteph\"><input type=\"checkbox\" id=\"accepte2_$lfid\" name=\"accepte2_$lfid\"></div><div class=\"col-xs-11 no-padding\"><p>".$template->translate("/garantie/platine_home/conditions2")."</p></div>"
                              ."<input type=\"hidden\" id=\"accepteerr_$lfid\" name=\"accepteerr\" value=\"".$template->translate("/garantie/platine_home/accepte_erreur")."\">"
                         ."</div>"
                         ."<div class=\"col-xs-12 col-sm-offset-1 col-sm-10 col-md-offset-3 col-md-6 col-lg-offset-4 col-lg-4\">"
                              ."<a href=\"javascript:void(0);\" class=\"validinfos\" onclick=\"validinfoIpid(".$lfid.");\" >Valider</a>"
                         ."</div>"
                         ."<div class=\"col-xs-12 cgv\">
                              <p>".$template->translate("/garantie/platine_home/cgv")."</p></div>";

                         
                         //$TemplateFormPlatineHome .= "<div class=\"col-xs-12\"><a href=\"javascript:void(0);\" onclick=\"".$validForm."\" class=\"next_step_garantie\">".$template->translate('/panier/garantie_valide')."<i class=\"fa fa-chevron-right\"></i></a></div></div>";
                         $TemplateFormPlatineHome .= "</div>";

                    //gestion de l'affichage du tarif platine home
                    if(intval($lignefacture->qte) > 1){// si qte multiple du produit
                         if($count > 1)//si le poduit est un lot de plusieurs articles
                              $x = $lignefacture->qte * $count;
                         else
                              $x = $lignefacture->qte;
                         
                         $offer_price_affiche = $x . " x " . $offer_price;
                    }else{
                         if($count > 1)//si le poduit est un lot de plusieurs articles
                              $offer_price_affiche = $count . " x " . $offer_price;
                         else
                              $offer_price_affiche = $offer_price;
                    }
                    $TemplateCde1BitGarantieBit .=""
                    ."<div class=\"bloc_garantie_sup bloc_garantie_sup_$lfid\" id=\"bloc_garantie_".$lfid."_".$platinehome."\" onclick=\"choisigarantie(".$lfid.",".$platinehome.");checkph($lfid);\">"
                    ."<div class=\"duree duree5\">"
                     ."<div><span>".$template->translate("/garantie/platine_home/abo")."<span></div>"
                    . "</div>"
                    . "<div class=\"radiobtn\">"
                    .  "<input type=\"hidden\" value=\"".$lignefacture->prix_nogarantie."\" name=\"garantie[".$lfid."-".$lignefacture->artid."-".$platinehome."][prix]\">"
                    .  "<input type=\"hidden\" value=\"".$offer_price."\" name=\"garantie[".$lfid."-".$lignefacture->artid."-".$platinehome."][offer_price]\">"
                    .  "<input type=\"hidden\" value=\"".$offer_price."\" name=\"offer_price_$lfid\">"
                    .  "<input type=\"hidden\" value=\"".$offer_category_id."\" name=\"offer_category_id_$lfid\">"
                    .  "<input type=\"hidden\" id=\"selectph_".$lfid."\" name=\"selectph_".$lfid."\" value=\"0\">"
                    .  "<input type=\"radio\" id=\"garantie_".$lfid."_".$platinehome."\" name=\"garantie[".$lfid."][choixGarantie]\" value=\"".$platinehome."\" ".$checked.">"
                    . "</div>"
                    . "<div class=\"txt\">"
                    .  "<p class=\"intitule_garantie_abo\">".$template->translate("/garantie/platine_home/titre")." &nbsp;&nbsp;&nbsp;</p>"
                    .   "<p class=\"description_garantie_abo\">".$template->translate("/garantie/platine_home/titre2")."</p>"
                    . "</div>"
                    . "<div class=\"prix\">"
                     . "<span>".$offer_price_affiche." €<small style=\"font-size:80%;\">/".$template->translate("/garantie/platine_home/mois")."</small></span>"
                    . "</div>"
                    . "<div class=\"clear\"></div>"
                    ."</div>";

                    $TemplateCde1BitGarantieBit .= $TemplateFormPlatineHome;
               }

               
               //Récupération des garanties disponibles pour le produit
               $garantiesPossibles = DAOgarantie::loadGarantiesPayArtByPays($DB_site, $template->site_code(),'FR',$user_info['paysid'],$lignefacture->artid, $destinataire_id, $lignefacture->prix_nogarantie,$lignefacture->tva);
               $nbgarantiesPossibles = count($garantiesPossibles);
               $platine = DAOgarantie::platinehome($DB_site,$platinehome,$lignefacture,$user_info['raisonsociale'],$fcl->paysid);
               
               $intitule_destinataire = '';
               if($fcl->raisonsociale != ''){
                    switch($dest_id){
                         case 1:
                              $intitule_destinataire = '"PARTICULIER"';
                              break;
                         case 2:
                              $intitule_destinataire = '"PRO"';
                              break;
                    }
               }
               
               //Si aucune garantie disponible, on ne propose que la garantie de base (garantie constructeur)
               $TemplateCde1BitGarantieBit .= ""
                    . "<div class=\"bloc_garantie_base bloc_garantie_base_$lfid selected\" id=\"bloc_garantie_".$lfid."_".$garantieID."\" onclick=\"choisigarantie(".$lfid.",".$garantieID.")\">"
			 . "<div class=\"radiobtn\">"
			    . "<input type=\"hidden\" value=\"".$lignefacture->prix_nogarantie."\" name=\"garantie[".$lfid."-".$lignefacture->artid."-".$garantieID."][prix]\">"
			    . "<input type=\"".($nbgarantiesPossibles > 0 || $platine ? "radio" : "hidden")."\" id=\"garantie_".$lfid."_".$garantieID."\" name=\"garantie[".$lfid."][choixGarantie]\" value=\"".$garantieID."\" ".$checked."/>"
                         . "</div>"
                         . "<div class=\"txt\">"
			    . "<p class=\"intitule_garantie_base\">"
			    . $template->translate('/garanties/garantie','F')." ".$garantieDeBaseAffiche."</p>"
			    . "<p class=\"description_garantie_base\">".$template->translate('/garanties/garantie','F')." ".$template->translate("/article/garantie_type/valable_jusqu_en")." <span>".$date."</span></p>"
			 . "</div>"
			 . "<div class=\"prix\">"
			      . "<span>".$template->translate('/article/garantie_type/offerte','F')."</span>".$auLieuDe
			 . "</div>"
			 . "<div class=\"clear\"></div>"
                    . "</div>";
               //S'il y a des garanties disponibles : On les liste en donnant la possibilité de les sélectionner (garanties payantes)
               if($nbgarantiesPossibles > 0){

                    $TemplateCde1BitGarantieBit .= ""
                         . "<p class=\"mini_desc\">".$template->translate('/garanties/recommandations_experts','F')." :</p>";

                    for($i=0; $i < count($garantiesPossibles); $i++){
                         $duree = $garantiesPossibles[$i]->duree;
                         $ppi   = $garantiesPossibles[$i]->ppi;
                         $prix  = $garantiesPossibles[$i]->prix;
                         if($sitepro){
                              $ppi =floatval($ppi);
                              $ppi =$ppi/(1+$taux20/100);
                              $prix=floatval($prix);
                              $prix = $prix/(1+$taux20/100);
                         }
                         if($garantiesPossibles[$i]->cumul == 1){

                              $allGaranties=DAOgarantie::loadGarantiesArtByPaysSaufExtensions($DB_site,$template->site_code(),'FR',$user_info['paysid'],$lignefacture->artid, $destinataire_id);
                              $pointer=DAOgarantie::searchGarantieToAddExtension($DB_site,$garantiesPossibles[$i]->type_garantie,$allGaranties,$garantiesPossibles[$i]->garantieid);
                              if ($allGaranties[$pointer]->prix){

                                   $ppi += $allGaranties[$pointer]->ppi;

                              }

                              $duree += $allGaranties[$pointer]->duree;
                              $prix  += $allGaranties[$pointer]->prix;

                         }

                         $an = $template->translate("/article/garantie_type/an");
                         if ($duree>1){
                              $an = $template->translate("/article/garantie_type/ans");
                         }

                         $checked  = '';
                         $auLieuDe = "";

                         if($ppi > $prix){
                              $auLieuDe = "<span class=\"ppi\">".$template->price_format($ppi)."</span>";
                         }

                         if(DAOlignefacturegarantie::loadLigneFactureGarantieByLigneFacEtGarantie($DB_site,$lfid,$garantiesPossibles[$i]->garantieid)!==false){

                              $checked='checked';

                         }

                         $date = new DateTime(date('Y-m-d'));

                         $date->modify('+'.$duree.' year');
                         $date->modify('-1 day');

                         $date = $template->translate("/site/mois/".$date->format('n'))." ".$date->format('Y');

                         $prix_par_mois = $prix / ($duree * 12);
                         
                         $intitule_destinataire = '';
                         if($fcl->raisonsociale != ''){
                              switch($garantiesPossibles[$i]->destinataire_garantie){
                                   case 1:
                                        $intitule_destinataire = '"PARTICULIER"';
                                        break;
                                   case 2:
                                        $intitule_destinataire = '"PRO"';
                                        break;
                              }
                         }
                         if($sitepro){
                              $tax = $template->translate("/prix/ht");
                         }else{
                              $tax = $template->translate("/prix/ttc2");
                         }
                         $libqte="";
                         if($lignefacture->qte>1){
                              $libqte = $lignefacture->qte. " x ";
                         }

                         $TemplateCde1BitGarantieBit .= ""
                              . "<div class=\"bloc_garantie_sup\" id=\"bloc_garantie_".$lfid."_".$garantiesPossibles[$i]->garantieid."\" onclick=\"choisigarantie(".$lfid.",".$garantiesPossibles[$i]->garantieid.")\">"
                                   //. "<input type='hidden' value='".$duree."' name='duree'>"
				    . "<input type=\"hidden\" value=\"".$garantiesPossibles[$i]->prix."\" name=\"garantie[".$lfid."-".$lignefacture->artid."-".$garantiesPossibles[$i]->garantieid."][gar_prix]\">"
				    . "<input type=\"hidden\" value=\"".$garantiesPossibles[$i]->paht."\" name=\"garantie[".$lfid."-".$lignefacture->artid."-".$garantiesPossibles[$i]->garantieid."][gar_paht]\">"
				    . "<input type=\"hidden\" value=\"".$garantiesPossibles[$i]->ppi."\" name=\"garantie[".$lfid."-".$lignefacture->artid."-".$garantiesPossibles[$i]->garantieid."][gar_ppi]\">"
				    . "<input type=\"hidden\" value=\"".$lignefacture->prix_nogarantie."\" name=\"garantie[".$lfid."-".$lignefacture->artid."-".$garantiesPossibles[$i]->garantieid."][prix]\">"
				    . "<input type=\"hidden\" value=\"".$duree."\" name=\"garantie[".$lfid."-".$lignefacture->artid."-".$garantiesPossibles[$i]->garantieid."][duree]\">"
				    . "<div class=\"duree duree".$duree."\">"
				      ."<div><span>".$duree." ".$an."<span></div>"
				    . "</div>"
				    . "<div class=\"radiobtn\">"
				      . "<input type=\"radio\" id=\"garantie_".$lfid."_".$garantiesPossibles[$i]->garantieid."\" name=\"garantie[".$lfid."][choixGarantie]\" value=\"".$garantiesPossibles[$i]->garantieid."\" ".$checked."/>"
				    . "</div>"
				    . "<div class=\"txt\">"
					. "<p class=\"intitule_garantie_sup\">"
					  . $template->translate("/garanties/garantie")." ".$duree." ".$an." ".$template->translate("/article/garantie_type/".$garantiesPossibles[$i]->type_garantie)
					. "</p>"
					. "<p class=\"description_garantie_sup\">".$template->translate("/garanties/garantie")." ".$template->translate("/article/garantie_type/valable_jusqu_en")." <span>".$date."</span></p>"
				    . "</div>"
				    . "<div class=\"prix\">"
					.$auLieuDe
					.$libqte.$template->price_format($prix)." ".$tax."
					<span class=\"parmois\">".$template->translate("/garanties/soit").$template->price_format($prix_par_mois)."/".$template->translate("/garanties/mois")."</span>"
				    . "</div>"
				    . "<div class=\"clear\"></div>"
			      . "</div>";

                    }

                    //$TemplateCde1BitGarantieBit .= "</div>";

               }

               //$TemplateCde1BitGarantieBit .= "</form>";

               $templateGarantie[$lfid] = $TemplateCde1BitGarantieBit;
          }
     
     }
     
     $template->parts["listearticles"]="";
     foreach($fcl->lignes as $lfid => $lignefacture){

	  if(isset($templateGarantie[$lfid])){ 
	    $template->parts["artimg"]=article_image($lignefacture->artid,194,220);
	    $template->parts["libelle"]=$lignefacture->libelle;
	    $template->parts["artcode"]=$lignefacture->artcode;
	    $template->parts["qte"]=$lignefacture->qte;
	    $template->parts["listegaranties"]=$templateGarantie[$lfid];
	    $template->parts["logomarque"]="<img src=\"/marques/".$lignefacture->marqueid."_90_22.jpg\" class=\"logo\">";
	    $template->evaltemplate("GarantieBloc","listearticles");
	  }
    }
    $listegaranties=stripslashes($template->parts["listearticles"]);
?>
     <form method="post" name="frmchoixGarantie" action="cde225.php">
          <input type="hidden" value="<?php echo $destinataire_id; ?>" name="destinataire_id">
          <input type="hidden" value="<?php echo $facture_id; ?>" name="factureid">
          <input type="hidden" value="garantie" name="action2">
          <?=$listegaranties;?>
     </form>
     <a href="javascript:void(0);" id="validForm1" onclick="<?=$validForm;?>" class="next_step_garantie"><?=$template->translate('/panier/garantie_valide');?><i class="fa fa-chevron-right"></i></a>
<?php
}else
    print "
    <form method=\"post\" name=\"frmchoixGarantie\" action=\"cde25.php\">
    <input type=\"hidden\" value=\"[?facturid]\" name=\"factureid\">
    </form>
    <a href=\"javascript:void(0);\" onclick=\"document.forms.frmchoixGarantie.submit();\" class=\"next_step_garantie\">".$template->translate('/panier/garantie_valide')."<i class=\"fa fa-chevron-right\"></i></a>";

?>
