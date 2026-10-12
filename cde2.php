<?php
require ("global.php");

/*if(!isset($_COOKIE['admin_username'])){
    header("Location: maintenance.html");
    exit;
}*/
//ici on fais afficher les balise hreflang pour fr et en
foreach($template->sites() as $site_code=>$site){
    foreach($site['hosts'] as $url=>$deflangue){
    if($site['actif'][$url])
        $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url."/recapitulatif.html\">\n";
    }
}

$title   = "title".$template->langue;
$libelle = "libelle".$template->langue;

$pays_sfg = array("57","181","111","99","17","158");
$pays_gcp = array("57","17");

$nogarantie = false;

$template->parts['modaltype']=$template->parts['modal']='';
$template->parts["modalclass"] = "modal";
$template->evaltemplate("ModalErreur","modal");
$template->evaltemplate("Overlay","modaltype");

if(isset($_POST['action0']) && $_POST['action0'] != ""){
    $civint=array(0=>"choisissez_mr",1=>"choisissez_mme",2=>"choisissez_mlle");

    $site_code=$template->site_code();
    if($site_code=="") $site_code="base";

    $template->parts["etape"] = "<img src=\"images/templates/commande2.jpg\" class=\"img img-responsive\">";
    $template->parts["hideinfo1"]=$template->parts["hideinfo2"]=$template->parts["hideinfo3"]=$template->parts["hideinfo4"]="hidden";

    $affiche = "RegisterPart";
    if($_POST['propart'] == '2'){
        $affiche = "RegisterPro";
    }
    $template->parts["zonedeliste_pays"]="";
    $payss=$DB_site->query("SELECT * FROM pays INNER JOIN fraisport USING(paysid) WHERE sites='".$site_code."' GROUP BY paysid ORDER BY libelle ASC");
    while($pays=$DB_site->fetch_array($payss)){
        $template->parts["zonedeliste_pays"] .= "<option value=$pays[paysid] ".($pays[paysid]==57?"selected":"").">$pays[libelle]</option>";
    }
    $action="";
    $template->parts["jsinscription"]="document.getElementById('register1').style.display='none';";
    $template->parts["mail"]=(isset($_POST['mail_log'])?$_POST['mail_log']:"");
    $template->evaltemplate($affiche,"pagecommande");
}


if(isset($action2) and $action2 != ""){
     switch ($action2){
          
          case 'inscription2': // cas d'erreur pendant l'inscription
               
               if(isset($civilite) and $civilite != ""){
                   
                    if($civilite=='0') { $selectmr    = "selected"; }
                    if($civilite=='1') { $selectmme   = "selected"; }
                    if($civilite=='2') { $selectmelle = "selected"; }
               }
               
               $zonedeliste_pays = "";
               
               $sql = "SELECT *";
               $sql .= " FROM pays";
               $sql .= " WHERE paysid IN (";
               $sql .= "           SELECT DISTINCT(paysid)";
               $sql .= "           FROM fraisport";
               $sql .= ")";
               $sql .= " ORDER BY libelle ASC";
               $payss = $DB_site->query($sql);
               
               while($pays = $DB_site->fetch_array($payss)){
                    
                    if($pays['paysid'] == 57){
                        $selected="selected";
                    } else {
                        $selected="";
                    }
                    
                    $zonedeliste_pays .= "<option value=\"".$pays['paysid']."\" ".$selected.">".$pays['libelle']."</option>";
               }
               
               $action = "bidule" ;
               
               eval("\$TemplateLogging = \"".$templatecache['register']."\";");
               
               break ;
        
          case 'domodifad': // modification de l'adresse de livraison
               
               $errormessage = "";
               
               if (!isset($civilite) or $civilite == "") $errormessage .= $multilangue['civilite']."<br> ";
               if (isset($civilite) and $civilite != '0' and $civilite != '1' and $civilite != '2') $errormessage .= $multilangue['civilite']."<br> "; 
               if (!isset($nom) or $nom == "") $errormessage .= $multilangue['nom']."<br> "; 
               if (!isset($prenom) or $prenom == "") $errormessage .= $multilangue['prenom']."<br> "; 
               if (!isset($adresse) or $adresse == "") $errormessage .= $multilangue['adresse']."<br> "; 
               if (!isset($codepostal) or $codepostal == "") $errormessage .= $multilangue['codepostal']."<br> "; 
               if (!isset($ville) or $ville == "") $errormessage .= $multilangue['ville']."<br> "; 
               if (!isset($telephone) or $telephone == "") $errormessage .= $multilangue['telephone']."<br> "; 
               if (!isset($paysid) or $paysid == "") $errormessage .= $multilangue['pays']."<br> "; 
               if (!isset($factureid) || $factureid == "") $errormessage .= "NOK<br> "; 
               
               if ($errormessage == ""){

                    $sql = "UPDATE facture SET";
                    $sql .= " civilite       = '".$civilite."'";
                    $sql .= ", nom           = '".$nom."'";
                    $sql .= ", prenom        = '".$prenom."'";
                    $sql .= ", codepostal    = '".$codepostal."'";
                    $sql .= ", adresse       = '".$adresse."'";
                    $sql .= ", ville         = '".$ville."'";
                    $sql .= ", telephone     = '".$telephone."'";
                    $sql .= ", raisonsociale = '".$raisonsociale."'";
                    $sql .= ", paysid        = '".$monpays."'";
                    $sql .= ", infotranspo   = '".addslashes($infotranspo)."'";
                    $sql .= " WHERE userid = '".$user_info['userid']."'";
                    $sql .= " AND facturid = '".$factureid."'";
                    $DB_site->query($sql);
                    
                    $sql = "UPDATE utilisateur SET";
                    $sql .= " nom_livraison            = '".$nom."'";
                    $sql .= ", prenom_livraison        = '".$prenom."'";
                    $sql .= ", codepostal_livraison    = '".$codepostal."'";
                    $sql .= ", adresse_livraison       = '".$adresse."'";
                    $sql .= ", ville_livraison         = '".$ville."'";
                    $sql .= ", raisonsociale_livraison = '".$raisonsociale."'";
                    $sql .= ", paysid_livraison        = '".$monpays."'";
                    $sql .= ", infotranspo             = '".addslashes($infotranspo)."'";
                    $sql .= ", telephone_livraison     = '".$telephone."'";
                    $sql .= " WHERE userid = '".$user_info['userid']."'";
                    $DB_site->query($sql);
                    
                    $action2 = "modifok";
                    
                    $nogarantie =! in_array($monpays, array(57,181,111,99,17));
               } else {
                    
                   $action = "modifad" ;
               }
               
               break ;
          
          case 'cadeau': // cadeau
	   
               break ; 
     }
}

$sql = "SELECT sum(qte)";
$sql .= " FROM lignepanier";
$sql .= " WHERE panierid = '".$panier['panierid']."'";
$qtes = $DB_site->query_first($sql);
if($qtes[0] == 0){
    header("Location: cde1.php"); /* Redirect browser */
    exit;
}


//Affichage par défaut
if (!isset($action) or $action == ""){
     if ($user_info['userid'] != ""){ // connexion réussie dans global.php
            
             //redirection pro sur site part ou part sur site pro
           /*if(($user_info['raisonsociale'] != "" && !$sitepro) || ($user_info['raisonsociale'] == "" && $sitepro)){ 
                $userid=$user_info['userid'];
                $s=$_COOKIE['s'];
                $prenom=$user_info['prenom'];
                $password=$user_info['password'];
                redirectionPro($_SERVER['SCRIPT_NAME'], $_SERVER['HTTP_HOST'], $template,$userid,$s,$prenom,$password);         
          }*/

          if($action2 != "modifok" and $action2 != "controlecgv"){

               $user_info['nom_livraison']           = addslashes($user_info['nom_livraison']) ;
               $user_info['prenom_livraison']        = addslashes($user_info['prenom_livraison']) ;
               $user_info['codepostal_livraison']    = addslashes($user_info['codepostal_livraison']) ;
               $user_info['ville_livraison']         = addslashes($user_info['ville_livraison']) ;
               $user_info['adresse_livraison']       = addslashes($user_info['adresse_livraison']) ;
               $user_info['raisonsociale_livraison'] = addslashes($user_info['raisonsociale_livraison']) ;
               $user_info['telephone_livraison']     = addslashes($user_info['telephone_livraison']) ;

               $sql = "SELECT port, gestion, TVAtauxnormal, cee, tvaintracom";
               $sql .= " FROM pays";
               $sql .= " WHERE paysid = '".$user_info['paysid']."'";
               $taux = $DB_site->query_first($sql);
               if ($taux['port'] == '0' || ($user_info['tvaintracom'] != "" && $taux['tvaintracom'] == 1)){
                   $tauxport = 0 ;
               } else {
                   $tauxport = $taux['TVAtauxnormal'];
               }

               $modelivraison = 0;
               $commercial    = "";
               
               if(isset($_COOKIE['trspt'])) $modelivraison = $_COOKIE['trspt'];

               //On précise que la commande est passé par un administrateur (on regarde si l'utilisateur est connecté à l'admin pour ça)
               $commercial_attribution="";
               if(isset($_COOKIE['admin_username']) && $_COOKIE['admin_username'] != ""){
                    $commercial = $_COOKIE['admin_username'];
                }else{
                    $sqlSub2 = "SELECT COUNT(*) FROM suivi_client WHERE userid = ".$user_info['userid']." AND commercial NOT IN ('', 'client') ";
                    $suivi_client=$DB_site->query_first($sqlSub2);
                    if($suivi_client[0]==0)
                        $commercial_attribution="direct";
                }

               //Si l'adresse IP correspond à celle du bureau, et qu'aucun utilisateur n'est connecté à l'admin, on indique "bureau"
               if($commercial == '' && $_SERVER['REMOTE_ADDR'] == '80.12.82.212') $commercial = 'Bureau';
               
               $sql = "INSERT INTO facture (";
               $sql .= " userid";
               $sql .= ", datefacture";
               $sql .= ", heurefacture";
               $sql .= ", prixport";
               $sql .= ", civilite";
               $sql .= ", nom";
               $sql .= ", prenom";
               $sql .= ", codepostal";
               $sql .= ", adresse";
               $sql .= ", ville";
               $sql .= ", telephone";
               $sql .= ", paysid";
               $sql .= ", raisonsociale";
               $sql .= ", prixgestion";
               $sql .= ", tvaport";
               $sql .= ", tvagestion";
               $sql .= ", mail";
               $sql .= ", langue";
               $sql .= ", referentid";
               $sql .= ", infotranspo";
               $sql .= ", modelivraison";
               $sql .= ", commercial";
               $sql .= ", commercial_attribution";
               $sql .= ", site_code";
               $sql .= ", devise";
               $sql .= ", provenance";
               $sql .= ", panierid";
               $sql .= ") VALUES (";
               $sql .= "'".$user_info['userid']."'";
               $sql .= ", NOW()";
               $sql .= ", '".time()."'";
               $sql .= ", '0'";
               $sql .= ", '".$user_info['civilite']."'";
               $sql .= ", '".$user_info['nom_livraison']."'";
               $sql .= ", '".$user_info['prenom_livraison']."'";
               $sql .= ", '".$user_info['codepostal_livraison']."'";
               $sql .= ", '".$user_info['adresse_livraison']."'";
               $sql .= ", '".$user_info['ville_livraison']."'";
               $sql .= ", '".$user_info['telephone_livraison']."'";
               $sql .= ", '".$user_info['paysid_livraison']."'";
               $sql .= ", '".$user_info['raisonsociale_livraison']."'";
               $sql .= ", '".$fraisgestion."'";
               $sql .= ", '".$tauxport."'";
               $sql .= ", '".$tauxgestion."'";
               $sql .= ", '".$user_info['mail']."'";
               $sql .= ", '".$template->langue."'";
               $sql .= ", '".(isset($_COOKIE['referentid']) && $_COOKIE['referentid'] ? $_COOKIE['referentid'] : '0')."'";
               $sql .= ", '".addslashes($user_info['infotranspo'])."'";
               $sql .= ", '".$modelivraison."'";
               $sql .= ", '".$commercial."'";
               $sql .= ", '".$commercial_attribution."'";
               $sql .= ", '".$template->site_code()."'";
               $sql .= ", 'EUR'";
               $sql .= ", '".($template->mobile?"mobile":"")."'";
               $sql .= ", '".$panier['panierid']."'";
               $sql .= ")";
               $DB_site->query($sql);
               
               $factureid = $DB_site->insert_id();
               
               $template->parts["facturid"] = $factureid;
          }

          $lstartid = array();

          $poidtotal       = 0 ;
          $poidscaves      = array();
          $prixtotalpanier = 0;
          $totalpaht       = 0;
          $prixtypeinstall = "";
          $artids          = array();
	
          $now = mktime(0,0,0);
          
          // transfert de panier vers facture
          
          $sql = "SELECT *";
          $sql .= " FROM panier";
          $sql .= " WHERE sessionid = '".$s."'";
          $panier = $DB_site->query_first($sql);

          $packs=new Pack($DB_site);
          
          if($panier['panierid'] != ""){
               
               $sql = "SELECT l.qte, a.artid, a.libelleFR, a.libelleDE, a.libelleEN, a.libelleIT, a.libelleSP, a.prix , a.artcode , a.poid , a.tva, a.tauxchoisi, a.ecopartht, a.catid, a.typearticleid, l.lignepanierid, a.emballage_poids1, a.emballage_poids2, a.emballage_poids3, a.$libelle AS libelle";
               $sql .= " FROM lignepanier l";
               $sql .= " INNER JOIN article a USING (artid)";
               //$sql .= " LEFT JOIN article_fournisseur af USING (artid)";
               $sql .= " WHERE 1";
               $sql .= " AND l.panierid = '".$panier['panierid']."'";
               //$sql .= " AND af.principal LIKE '%".$template->site_code()."%'";
               $sql .= " ORDER BY l.lignepanierid ASC";
               $lignepaniertmps = $DB_site->query($sql);

               $sql = "SELECT 0, TVAtauxnormal, TVAtauxreduit, cee, tvaintracom";
               $sql .= " FROM pays";
               $sql .= " WHERE paysid = '".$user_info['paysid']."'";
               $taux = $DB_site->query_first($sql);
               $poids = 0;
            
               while ($lignepaniertmp = $DB_site->fetch_array($lignepaniertmps)){
               if(!$packs->isPack($lignepaniertmp['artid'])){
			$sql="SELECT af.prix as paht FROM article_fournisseur af WHERE artid=".$lignepaniertmp['artid']." AND af.principal LIKE '%".$template->site_code()."%'";
			$fournisseur=$DB_site->query_first($sql);
		
			$sql = "SELECT *";
			$sql .= " FROM temp_price";
			$sql .= " WHERE artid = ".$lignepaniertmp['artid'];
			$sql .= " AND site_code = '".$template->site_code()."'";
			$sql .= " AND ".$now." BETWEEN du AND au";
			$temp_prix = $DB_site->query_first($sql);
			if($temp_prix['id'] != ""){
			    $lignepaniertmp['prix'] = $temp_prix['prix'];
			    $fournisseur['paht'] = $temp_prix['paht'];
			}

			$sql = "SELECT *";
			$sql .= " FROM vente_flash_article vfa";
			$sql .= " INNER JOIN vente_flash vf ON (vf.id = vfa.vfid)";
			$sql .= " WHERE (".time()." BETWEEN vf.du AND vf.au)";
			$sql .= " AND vfa.artcode = '".$lignepaniertmp['artcode']."'";
			$vfarticle = $DB_site->query_first($sql);
			if($vfarticle['id'] != ""){
			    $lignepaniertmp['prix'] = $vfarticle['prix'];
			}
			
			$prixht = ($user_info['tvaintracom'] != "" && $taux['tvaintracom'] == 1) || $taux[$lignepaniertmp['tauxchoisi']] == 0;
			$article_prices = article_prices($lignepaniertmp['artid'],$template->tva_ref(),$template->site_code(),$prixht);
			$lignepaniertmp['prix'] = $article_prices['prix'];
			$tauxarticle = $article_prices['tauxtva'];

			$poids += $lignepaniertmp['qte'] * ($lignepaniertmp['poid'] / 1000);
			
			if($lignepaniertmp['typearticleid'] == 1 || $lignepaniertmp['typearticleid'] == 10) $poidscaves[round($lignepaniertmp['poid'])]+=$lignepaniertmp['qte'];

			// on ajoute le poids que si ce n'est pas un sowine (FDP inclus)    

			$poidtotal += 1000*floatval(str_replace(",",".",($lignepaniertmp['emballage_poids1']+$lignepaniertmp['emballage_poids2']+$lignepaniertmp['emballage_poids3'])))* $lignepaniertmp['qte'];
			install_type($lignepaniertmp['artid']);
			
			if ($action2 != "modifok" and $action2 != "controlecgv"){
			    
			    $lignepaniertmp['libelle'] = addslashes($lignepaniertmp['libelle']) ;
			    $lignepaniertmp[8] = addslashes($lignepaniertmp[8]) ;
			    
			    if(($user_info['tvaintracom']!="" && $taux['tvaintracom']==1) || $taux[$lignepaniertmp['tauxchoisi']]==0) $tauxarticle = 0 ;

			    $totalttc[$tauxarticle] += $lignepaniertmp['prix'];

			    //Insertion de la commande en BDD
			    $sql = "INSERT IGNORE INTO lignefacture (";
			    $sql .= " factureid";
			    $sql .= ", artid";
			    $sql .= ", catid";
			    $sql .= ", artcode";
			    $sql .= ", qte";
			    $sql .= ", libelle";
			    $sql .= ", prix";
			    $sql .= ", tva";
			    $sql .= ", paht";
			    $sql .= ", ecopart";
			    $sql .= ") VALUES (";
			    $sql .= "'$factureid'";
			    $sql .= ", '".$lignepaniertmp['artid']."'";
			    $sql .= ", '".$lignepaniertmp['catid']."'";
			    $sql .= ", '".$lignepaniertmp[8]."'";
			    $sql .= ", '".$lignepaniertmp[0]."'";
			    $sql .= ", '".$lignepaniertmp['libelle']."'";
			    $sql .= ", '".$lignepaniertmp['prix']."'";
			    $sql .= ", '".$tauxarticle."'";
			    $sql .= ", '".$fournisseur['paht']."'";
			    $sql .= ", '".round($lignepaniertmp['ecopartht']*(1+$tauxarticle/100),2)."'";
			    $sql .= ")";
			    $DB_site->query($sql);
			    
			    $lignefactureid = $DB_site->insert_id();

			    // Caractéristiques //
			    $sql = "SELECT c.".$libelle." as libelle_caract, cv.".$libelle." as libelle_caractval, cv.ref, lpc.caractvalid";
			    $sql .= " FROM lignepaniercaracteristique lpc";
			    $sql .= " INNER JOIN caracteristiquevaleur cv ON (lpc.caractvalid = cv.caractvalid)";
			    $sql .= " INNER JOIN caracteristique as c ON (cv.caractid = c.caractid)";
			    $sql .= " WHERE lignepanierid = '".$lignepaniertmp['lignepanierid']."'";
			    $rq_lignepaniercaracteristique = $DB_site->query($sql);
   
			    while ($rs_lignepaniercaracteristique = $DB_site->fetch_array($rq_lignepaniercaracteristique)){   
				  
				  $rs_lignepaniercaracteristique['libelle_caract']    = addslashes($rs_lignepaniercaracteristique['libelle_caract']);
				  $rs_lignepaniercaracteristique['libelle_caractval'] = addslashes($rs_lignepaniercaracteristique['libelle_caractval']);
				  
                $liste_ral=$DB_site->query_first("SELECT caractvalid FROM caractval_ral WHERE caractvalid = '".$rs_lignepaniercaracteristique['caractvalid']."'");
                ///si l'option est une couleur RAL :
                if(!is_null($liste_ral)){
                    $caracteid = $DB_site->query_first("SELECT caractid FROM `caracteristiquevaleur` WHERE `caractvalid` = '".$liste_ral['caractvalid']."'");
                    $caracvals = $DB_site->query("SELECT caractvalid FROM `caracteristiquevaleur` WHERE `subcaractid` = ".$caracteid['caractid']);
                    $caracvals_liste="";
                    foreach($caracvals as $caracv){
                        $caracvals_liste[]=$caracv['caractvalid'];
                    }
                    $caracvals=implode("','",$caracvals_liste);
                    $caracval = $DB_site->query_first("SELECT caractvalid FROM `article_caractval` WHERE `artid` = '".$lignepaniertmp['artid']."' AND `caractvalid` IN ('".$caracvals."')");
                    $sql = "SELECT ac.differenceprix, ac.achat, cv.prix_fixe, cv.pvttc, cv.pvht, cv.paht, c.libelleFR as libelleral";
                    $sql .= " FROM article_caractval ac INNER JOIN caracteristiquevaleur cv ON cv.caractvalid = ac.caractvalid INNER JOIN caracteristique c ON cv.caractid = c.caractid";
                    $sql .= " WHERE ac.caractvalid = '".$caracval['caractvalid']."'";
                    $sql .= " AND ac.artid = '".$lignepaniertmp['artid']."'";
                    $diff = $DB_site->query_first($sql);
                    $rs_lignepaniercaracteristique['caractvalid']=$caracval['caractvalid'];
                    $rs_lignepaniercaracteristique['libelle_caract']    = addslashes($diff['libelleral']);
                }
                else{
				  $sql = "SELECT ac.differenceprix, ac.achat, cv.prix_fixe, cv.pvttc, cv.pvht, cv.paht, c.libelleFR as libelleral";
				  $sql .= " FROM article_caractval ac INNER JOIN caracteristiquevaleur cv ON cv.caractvalid = ac.caractvalid INNER JOIN caracteristique c ON cv.caractid = c.caractid";
				  $sql .= " WHERE ac.caractvalid = '".$rs_lignepaniercaracteristique['caractvalid']."'";
				  $sql .= " AND ac.artid = '".$lignepaniertmp['artid']."'";
                  
				  $diff = $DB_site->query_first($sql);
                }

                
                ///prix fixe ou prix variable (tache 7131)
                    //Si lot, on additionne les prix
                    $lot_prix=array();
                    $caractvaltotal=array();
                    $req_lot=$DB_site->query("SELECT artid,qte FROM article_lot WHERE lotid=".$lignepaniertmp['artid']);
                    while($art_lot=$DB_site->fetch_array($req_lot)){
                      $lot_prix[]=array($art_lot['artid'],$art_lot['qte']);
                    }
                    if($lot_prix!=array()){
                        foreach($lot_prix as $articleid => $art_val){
                            $art=$DB_site->query_first("SELECT differenceprixht, differenceprix, cv.prix_fixe, cv.pvht, cv.pvttc, cv.paht FROM article_caractval INNER JOIN caracteristiquevaleur as cv USING(caractvalid)WHERE caractvalid=".$rs_lignepaniercaracteristique['caractvalid']." AND artid=".intval($art_val[0]));
                            if($art!=""){
                                if($art['prix_fixe'] == '1'){
                                    $caractvaltotal["prixlot"]=$caractvaltotal["prixlot"] + $art['pvttc']*$art_val[1];
                                }
                                else{
                                    $diffprix = $art["differenceprix"];
                                    $caractvaltotal["prixlot"]=$caractvaltotal["prixlot"] + $diffprix*$art_val[1];
                                }
                            }
                        }
                    }
                    if(isset($caractvaltotal["prixlot"])){
                        $differenceprix=round($caractvaltotal["prixlot"],4);
                    }
                    else{
                        if($diff['prix_fixe'] == '1'){
                            $differenceprix = $diff['pvttc'];
                            $diff['achat'] = $diff['paht'];
                        }
                        else{
                            $prixoption = $diff['differenceprix'];
                            $differenceprix = $prixoption;
                    }
                }

				  if(($rs_lignepaniercaracteristique['caractvalid'] == 10 || $rs_lignepaniercaracteristique['caractvalid'] == 11) && !in_array($user_info['paysid_livraison'], $pays_sfg)){
				      
				      $rs_lignepaniercaracteristique['caractvalid']       = 9;
				      $rs_lignepaniercaracteristique['libelle_caract']    = $template->translate("/commande/garantie_serenite","F");
				      $rs_lignepaniercaracteristique['libelle_caractval'] = $template->translate("/commande/aucune_garantie_complementaire","F");
				      $differenceprix = 0;
				  }
				
				  if(($rs_lignepaniercaracteristique['caractvalid'] == 132 || $rs_lignepaniercaracteristique['caractvalid'] == 133) && !in_array($user_info['paysid_livraison'], $pays_gcp)){
				      
				      $rs_lignepaniercaracteristique['caractvalid']       = 131;
				      $rs_lignepaniercaracteristique['libelle_caract']    = $template->translate("/commande/garantie_serenite","F");
				      $rs_lignepaniercaracteristique['libelle_caractval'] = $template->translate("/commande/aucune_garantie_complementaire","F");
				      $differenceprix = 0;
				  }

				  if($differenceprix != 0){
				      
				      if($lignepaniertmp['tauxchoisi'] == '1'){
					    
					    $tauxarticle = $taux['TVAtauxnormal'];
					    
					    if($taux['TVAtauxnormal'] == 0 || ($user_info['tvaintracom'] != "" && $taux['tvaintracom'] == 1)){
						
						$sql = "SELECT TVAtauxnormal";
						$sql .= " FROM pays";
						$sql .= " WHERE paysid = '57'";
						$tvafrance = $DB_site->query_first($sql);
						
						$differenceprix = $differenceprix / (1 + ($tvafrance['TVAtauxnormal']/100)) ;
					    }
					    
				      } elseif($lignepaniertmp['tauxchoisi'] == '2'){
					  
					    if($taux['TVAtauxreduit'] == 0 || ($user_info['tvaintracom'] != "" && $taux['tvaintracom'] == 1)){
					      
						$sql = "SELECT TVAtauxreduit";
						$sql .= " FROM pays";
						$sql .= " WHERE paysid = '57'";
						$tvafrance = $DB_site->query_first($sql);
						
						$differenceprix = $differenceprix / (1 + ($tvafrance['TVAtauxreduit']/100)) ;
					    }
				      }
				      
				      $sql = "UPDATE lignefacture SET";
				      $sql .= " prix = prix + '".$differenceprix."'";
				      $sql .= " WHERE lignefactureid = '".$lignefactureid."'";
				      $DB_site->query($sql);
				  }

				  $sql = "INSERT IGNORE INTO lignefacturecaracteristique (";
				  $sql .= "lignefactureid";
				  $sql .= ", caractvalid";
				  $sql .= ", libelle_caract";
				  $sql .= ", libelle_caractval";
				  $sql .= ", ref";
				  $sql .= ", differenceprix";
				  $sql .= ", paht";
				  $sql .= ") VALUES (";
				  $sql .= "'$lignefactureid'";
				  $sql .= ", '".$rs_lignepaniercaracteristique['caractvalid']."'";
				  $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,$rs_lignepaniercaracteristique['libelle_caract'])."'";
				  $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,$rs_lignepaniercaracteristique['libelle_caractval'])."'";
				  $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,$rs_lignepaniercaracteristique['ref'])."'";
				  $sql .= ", ".floatval($differenceprix)."";
				  $sql .= ", ".floatval($diff['achat'])."";
				  $sql .= ")";
				  $DB_site->query($sql);
			    }

			    $totalligne = $lignepaniertmp['qte'] * ($differenceprix + $lignepaniertmp['prix']);
			    $prixtotalpanier += $totalligne;
			    $totalpaht += $lignepaniertmp['qte'] * $lignepaniertmp['paht'];
			    $artids[$lignepaniertmp['artid']] += $lignepaniertmp['qte'];
			    
			    $htmlmess .= ""
			    . "<tr>"
				  . "<td>".$lignepaniertmp['libelleFR']."<br>".$lignepaniertmp['artcode']."</td>"
				  . "<td align=\"center\">".$lignepaniertmp['qte']."</td>"
				  . "<td align=\"right\">".number_format($totalligne,2,',','')."</td>"
				  . "<td align=\"right\">".number_format($totalligne*$lignepaniertmp['qte'],2,',','')."</td>"
			    . "</tr>";
			}
                    }else{
			$artinpack=$packs->getProduits($lignepaniertmp['artid']);
			$infopack=$packs->getDetails($lignepaniertmp['artid']);
			$totalpack=0;
			
			foreach($artinpack as $art){
			    $prixht = ($user_info['tvaintracom'] != "" && $taux['tvaintracom'] == 1) || $taux[$art['tauxchoisi']] == 0;
			    $article_prices = article_prices($art['artid'],$template->tva_ref(),$template->site_code(),$prixht);
			    $tauxarticle = $article_prices['tauxtva'];
			    if($art['prixpack']==0) $article_prices['prix']=0;
                    
                            $sql = "SELECT *";
                            $sql .= " FROM temp_price";
                            $sql .= " WHERE artid = ".$art['artid'];
                            $sql .= " AND site_code = '".$template->site_code()."'";
                            $sql .= " AND ".$now." BETWEEN du AND au";
                            $temp_prix = $DB_site->query_first($sql);
                            if($temp_prix['id'] != ""){
                                $art['paht'] = $temp_prix['paht'];
                            }

			    $poids += $art['qte'] * $lignepaniertmp['qte'] * ($art['poid'] / 1000);
			    
			    if($art['typearticleid'] == 1 || $art['typearticleid'] == 10) $poidscaves[round($art['poid'])]+=($art['qte']*$lignepaniertmp['qte']);

			    // on ajoute le poids que si ce n'est pas un sowine (FDP inclus)    

			    $poidtotal += 1000*floatval(str_replace(",",".",$art['emballage_poids1']+$art['emballage_poids2']+$art['emballage_poids3']))*$art['qte']*$lignepaniertmp['qte'];
			    install_type($art['artid']);
			    
			    if(($user_info['tvaintracom']!="" && $taux['tvaintracom']==1) || $taux[$lignepaniertmp['tauxchoisi']]==0) $tauxarticle = 0 ;

			    $totalttc[$tauxarticle] += $article_prices['prix'];

			    //Insertion de la commande en BDD
			    $sql = "INSERT IGNORE INTO lignefacture (";
			    $sql .= " factureid";
			    $sql .= ", artid";
			    $sql .= ", catid";
			    $sql .= ", artcode";
			    $sql .= ", qte";
			    $sql .= ", libelle";
			    $sql .= ", prix";
			    $sql .= ", tva";
			    $sql .= ", paht";
			    $sql .= ", ecopart";
			    $sql .= ", packid";
			    $sql .= ") VALUES (";
			    $sql .= "'$factureid'";
			    $sql .= ", '".$art['artid']."'";
			    $sql .= ", '".$art['catid']."'";
			    $sql .= ", '".$art['artcode']."'";
			    $sql .= ", '".($lignepaniertmp['qte']*$art['qte'])."'";
			    $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,$art['libelle'.$template->langue])."'";
			    $sql .= ", '".$article_prices['prix']."'";
			    $sql .= ", '".$tauxarticle."'";
			    $sql .= ", '".$art['paht']."'";
			    $sql .= ", '".$article_prices['ecopart']."'";
			    $sql .= ", '".$infopack['artid']."'";
			    $sql .= ")";
			    $DB_site->query($sql);
			    
			    $lignefactureid = $DB_site->insert_id();
			    
			    $totalligne = $lignepaniertmp['qte'] * $art['qte'] * $article_prices['prix'];
			    $prixtotalpanier += $totalligne;
			    $totalpack+=$totalligne;
			    $totalpaht += $lignepaniertmp['qte'] * $art['qte'] * $lignepaniertmp['paht'];
			    $artids[$art['artid']] += $lignepaniertmp['qte']*$art['qte'];
			    $offrepack=$art['offre'];
			}
			
			if($offrepack!=""){
			    $ligneremise=false;
			    list($type,$valeur)=explode("-",$offrepack);
			    switch($type){
				case "euro":
				if($prixht) $valeur=$valeur/(1+$taux[$lignepaniertmp['tauxchoisi']]/100);
				$ligneremise=true;
				$valeuraffiche=$template->price_format($valeur,0);
				break;
				
				case "pourcent":
				$valeuraffiche=$valeur."%";
				$valeur=$totalpack*($valeur/100);
				$ligneremise=true;
				break;
			    }
			    if($ligneremise){
				$sql="INSERT INTO lignefacture SET
				factureid=".$factureid.",
				qte=".$lignepaniertmp['qte'].",
				libelle='".$infopack['artcode']." - ".$template->translate("/article/autres_infos/remise_pack")." : -".$valeuraffiche."',
				prix=".floatval(-$valeur).",
				tva=".$tauxarticle.",
				packid=".$infopack['artid'];
				$DB_site->query($sql);
			    }
			}
                    }
               }
          }

          // Adresse de livraison 
          $TemplateCde2AdresseLivrContenu = "" ;
          $sql = "SELECT raisonsociale, nom, prenom, adresse, codepostal, ville, pays.paysid, pays.libelle, telephone, modelivraison, cadeauid";
          $sql .= " FROM facture";
          $sql .= " INNER JOIN pays ON (facture.paysid = pays.paysid)";
          $sql .= " WHERE userid = '".$user_info['userid']."'";
          $sql .= " AND facturid = '".$factureid."'";
          $rs_facture_coordonnees = $DB_site->query_first($sql);
          
          eval("\$TemplateCde2AdresseLivrContenu = \"".$templatecache['Cde2AdresseLivrContenu']."\";");
            
          //Frais de port
          $sql = "SELECT 0 AS taux0, TVAtauxnormal AS taux1, TVAtauxreduit AS taux2";
          $sql .= " FROM pays";
          $sql .= " WHERE paysid = '57'";
          $tvafrance = $DB_site->query_first($sql);

          // on fait tout le traitement des frais de port !!
          /*$fraisport=$DB_site->query_first("SELECT * FROM fraisport WHERE poiddebut <= '$poidtotal' AND poidfin >= '$poidtotal' 
                                              and paysid = '$rs_facture_coordonnees[paysid]'");*/
          $pro = false;                              
          if($user_info['raisonsociale'] != "") $pro = true;                 
          $frais_port = new frais_port($DB_site, $artids, $rs_facture_coordonnees['paysid'], $prixtotalpanier, $totalpaht, $template->site_code(),$pro,$user_info['userid']);
          
          $install = pays_install($rs_facture_coordonnees[paysid]);

          if(!$frais_port->livre){   // On ne livre pas dans ce pays 
               
               $template->parts["etape"]         = "<img src=\"images/templates/".$template->langue."/commande3.jpg\" class=\"img img-responsive\">";
               $template->parts["payslivraison"] = $rs_facture_coordonnees['libelle'];
               
               $template->evaltemplate("LivraisonImpossible","pagecommande");
               
          } else {
	    
               $devises = new devises($DB_site);
               $change  = $devises->getchange();
               
               $portbarre = $frais_port->prixbarrettc($tauxport,$change[$template->devise()]);
               $prixport  = $frais_port->prixttc($tauxport,$change[$template->devise()]);
               if($install == false) $rs_facture_coordonnees['modelivraison'] = '0';
               if($rs_facture_coordonnees['modelivraison'] == '0'){
                   
                    $sql = "UPDATE facture SET";
                    $sql .= " prixport      = '".$prixport."'";
                    $sql .= ",modelivraison = '0'";
                    $sql .= " WHERE facturid = ".$factureid;
                    $DB_site->query($sql);

               } elseif($rs_facture_coordonnees['modelivraison'] == '1'){
                    
                    $typelivraison = '1';
                    
                    if($install == "sls") $tarifsls = prixsls($rs_facture_coordonnees[codepostal],$poidtotal,$rs_facture_coordonnees[paysid],$prixtypeinstall);
                    if($install == "enigma") $tarifsls = prix_enigma($poidscaves,$template->site_code());

                    $prixtotalpanier += $tarif;
                    //if($tarif==0) $typelivraison='0';
                    
                    $sql = "UPDATE facture SET";
                    $sql .= " prixport = '".($prixport + $tarif)."'";
                    $sql .= ", modelivraison = '".$typelivraison."'";
                    $sql .= " WHERE facturid = ".$factureid;
                    $DB_site->query($sql);
               }
	           
               $facture = new facture($DB_site, $factureid);
               $template->parts["load_options"]    = $facture->has_options() ? '1' : '0';
	
               $prixtotalpanier = number_format($prixtotalpanier, 2, ',', ' ');
               $prixtva         = number_format($prixtva, 2, ',', ' ');    

               $datelivraison  = datelivraison($factureid);
               $datelivraison2 = datelivraison($factureid,$datelivraison,4*86400);
               $datelivraison3 = datelivraison($factureid);

               $template->parts["datelivraison1"] = date("d/m/Y",$datelivraison);
               $template->parts["datelivraison2"] = date("d/m/Y",$datelivraison2);
               $template->parts["datelivraison7"] = date("d/m/Y",$datelivraison2);
               $template->parts["datelivraison5"] = date("d/m/Y", $datelivraison3);
                
               $sql = "UPDATE facture SET";
               $sql .= " datelivraison = ".$datelivraison;
               $sql .= " WHERE facturid = ".$factureid;
               $DB_site->query($sql);
               
               $template->parts["mints1"] = 1000*$datelivraison;
               $template->parts["mints2"] = 1000*$datelivraison2;

               $seltrspt = "";

               if($poidtotal < 10000) $poidtotalstr = round($poidtotal/1000,1);
               if($cadeau['port'] == 1 || $cadeau['port'] == 3) $prixport = 0;

               if($install != false){
	      
                    if(isset($_COOKIE['trspt'])) $seltrspt = $_COOKIE['trspt'];

                    $seltrspt = $rs_facture_coordonnees['modelivraison'];
                    
                    $template->parts["seltrspt".(1+$seltrspt)] = "checked";

                    if ($poidtotal == '0'){

                    } else {

                         if($install == "sls") $tarifsls = prixsls($rs_facture_coordonnees['codepostal'], $poidtotal, $rs_facture_coordonnees['paysid'],$prixtypeinstall);
                         if($install == "enigma") $tarifsls = prix_enigma($poidscaves,$template->site_code());

                         if($tarifsls > 0){

                              $tarifsls += $prixport;

                              if($portbarre != 0){

                                   $portslsbarre = $tarifsls + $portbarre;
                                   if($sitepro) $portslsbarre = $portslsbarre / (1+$tvafrance['taux1']/100);

                                   $template->parts["choix2prixbarre"] = $template->price_format($portslsbarre);
                              }

                              if($cadeau['port'] >= 2) $tarifsls = 0;
                              if($sitepro){ 
                                   $tarifsls = $tarifsls/(1+$tvafrance['taux1']/100);
                                   $tax = $template->translate("/prix/ht2");
                              }else{
                                   $tax = $template->translate("/prix/ttc2");
                              }

                              $tarifslsstr = $template->price_format($tarifsls);

                              $template->parts["choix2prix"] = $tarifslsstr." ".$tax;
                              $template->evaltemplate("ChoixLivr2","choix2livr");

                              //tarif mise en service
                              //Récupération des articles de la commande
                                $aTypeArticle = array();
                                $sql = "SELECT DISTINCT(a.typearticleid)";
                                $sql .= " FROM lignefacture lf";
                                $sql .= " INNER JOIN article a ON (a.artid = lf.artid)";
                                $sql .= " WHERE lf.factureid = ".$factureid;
                                $req = $DB_site->query($sql);
                                while($row = $DB_site->fetch_array($req)){
                                     $aTypeArticle[] = $row['typearticleid'];
                                }
                                $aInstallation = array();
                                //Récupérations des installations correspondantes
                                foreach($aTypeArticle as $typearticleid){
                                     $sql = "SELECT *";
                                     $sql .= " FROM installation i";
                                     $sql .= " WHERE i.installation_type_id = 2";
                                     $sql .= " AND installation_typearticle = '|".$typearticleid."|'";
                                     $req = $DB_site->query($sql);
                                     while($row = $DB_site->fetch_array($req)){
                                          $aInstallation[] = $row;
                                     }
                                }
                                $prix_ttc=0;
                                if(count($aInstallation) > 0){
                                    foreach($aInstallation as $l_installation){
                                        //Concaténation des types d'articles pour affichage dans le titre
                                        $aTemp = explode('|', $l_installation['installation_typearticle']);
                                        $aTypeTemp = array();
                                        foreach($aTemp as $ligne){
                                           if(is_numeric($ligne)) $typearticleid = $ligne;
                                        }         
                                          
                                        $sql = "SELECT libelle, text_livraison";
                                        $sql .= " FROM typearticle";
                                        $sql .= " WHERE typearticleid = ".$typearticleid;
                                        $libelle_type = $DB_site->query_first($sql);

                                        $template->parts['typearticle_texte'] .= "<strong>La mise en service de votre $libelle_type[libelle] comprend : </strong>" .$libelle_type['text_livraison']."<br><br>";
                                        

                                        //Calcul du prix de vente TTC
                                        $prix_ttc += $l_installation['installation_pv_ht'] * (1 + ($l_installation['installation_tva_taux'] / 100));
                                    }

                                }

                              if($prix_ttc>0){
                                $prix_ttc += 30 + $tarifsls;
                                $template->parts["choix7prix"] = $template->price_format($prix_ttc);
                                $template->evaltemplate("ChoixLivr7","choix7livr");
                              }  
                              
                         }
                    }
               }

               if($sitepro) {

                    $portbarre = $portbarre / (1+$tvafrance['taux1']/100);

                    if($user_info['tvaintracom'] == "") $prixport=$prixport/(1+$tvafrance['taux1']/100);
               }

               if($portbarre!=0) $template->parts["choix1prixbarre"]=$template->price_format($portbarre)." ".$template->translate("/prix/ttc2");
               $template->parts["choix1prix"]=$template->price_format($prixport)." ".$tax;
               if($prixport==0) $template->parts["choix1prix"]=$template->translate("/commande/offert","F");

               /*f($poidtotal<=30000 && $rs_facture_coordonnees[paysid]==57){
                     $template->parts["datelivraison3"]=date("d/m/Y",$datelivraison2);
                     $template->parts["mints3"]=1000*$datelivraison2;
                     $template->parts["choix3prix"]=$template->parts["choix1prix"];
                     $template->evaltemplate("ChoixLivr3","choix3livr");
               }*/    

               $sql = "SELECT prix, actif";
               $sql .= " FROM montage";
               $sql .= " WHERE actif = 1";
               $sql .= " AND paysid = ".$rs_facture_coordonnees['paysid'];
               $reqmontage = $DB_site->query($sql);
               while($montagepays = $DB_site->fetch_array($reqmontage)){

                    $pumontage = unserialize($montagepays['prix']);
                    $pumontagesite = $pumontage[$template->site_code()];
                    if($pumontagesite > 0) break;
               }

               if($pumontagesite>0  && $tarifsls > 0){

                    $sql = "SELECT artid, a.artcode, qte, a.libelle".$template->langue." AS libelle, montage, montage_heures";
                    $sql .= " FROM article a";
                    $sql .= " INNER JOIN lignefacture USING(artid)";
                    $sql .= " WHERE factureid = ".$factureid;
                    $sql.= " GROUP BY artid";
                    $lignes = $DB_site->query($sql);
                    if($DB_site->num_rows($lignes) > 0){

                         $tarifmontage = $tarifsls;
                         $tarifmontagebarre = $portslsbarre;

                         if($sitepro) $pumontagesite = $pumontagesite/(1+$tvafrance['taux1']/100);

                         while($ligne = $DB_site->fetch_array($lignes)) {

                              $table = 2;

                              if($ligne['montage']==1){

                                   $tarifmontage+=$ligne['qte']*$ligne['montage_heures']*$pumontagesite;
                                   $tarifmontagebarre+=$ligne['qte']*$ligne['montage_heures']*$pumontagesite;
                                   $table="";
                              }

                              $template->parts["montage_table".$table] .= ""
                              	   . "<div class=\"col-md-2 col-sm-2 hidden-xs\"><img src=\"images/".$ligne['artid']."_50_50.jpg\"></div>"
                                   . "<div class=\"col-md-8 col-sm-8 col-xs-10\">".$ligne['libelle']."<br>".$ligne['artcode']."</div>"
                                   . "<div class=\"col-md-2 col-sm-2 col-xs-2\" class=\"qte\">".$ligne['qte']."</div>";
                         }

                         if($template->parts["montage_table2"]!="") $template->evaltemplate("MontageNonInclus","montage_non_inclus");
                         $template->parts["choix4prix"]=$template->price_format($tarifmontage);
                         if(!is_null($portslsbarre)) $template->parts["choix4prixbarre"]=$template->price_format($tarifmontagebarre);
                         if($template->parts["montage_table"]!="") $template->evaltemplate("ChoixLivr4","choix4livr");
                    }
               }
               
               $template->parts["poidstotal"] = round($poidtotal/1000);

               if(isset($_COOKIE['admin_username'])){
                    $template->parts["textlivr5"] = "Sont inclus le transport, les frais de déplacement, la manutention, l'installation à l'endroit souhaité et la mise en route.";
                    $template->evaltemplate("ChoixLivr5", "choix5livr");
               }
              
               $template->parts["etape"] = "<img src=\"images/templates/".$template->langue."/commande3.jpg\" class=\"img img-responsive\">";

               $template->evaltemplate("PanierLivraison","pagecommande");

               $htmlmess = ""
               . $user_info['prenom']." ".$user_info['nom']."<br>".$user_info['adresse']."<br>".$user_info['codepostal']." ".$user_info['ville']."<br>".$user_info['telephone']
               . "<div>IP : ".$_SERVER["REMOTE_ADDR"]." (".gethostbyaddr($_SERVER["REMOTE_ADDR"])."), pays : ".ip2country($_SERVER["REMOTE_ADDR"])."</div>"
               . "<table cellspacing=\"0\" border=\"1\">"
                    . "<tr>"
                         . "<td>Article</td>"
                         . "<td>Quantité</td>"
                         . "<td>Prix Unit.</td>"
                         . "<td>Prix</td>"
                    . "</tr>"
                    . $htmlmess
                    . "<tr>"
                         . "<td colspan=\"3\">Frais de port</td>"
                         . "<td align=\"right\">".$template->price_format($prixport)."</td>"
                    . "</tr>"
                    . "<tr>"
                         . "<td colspan=\"3\">Total</td>"
                         . "<td align=\"right\">".$prixtotalpanier."</td>"
                    . "</tr>"
               . "</table>";

               $email=new email();
               $email->destinataire="pr@mon-espace-cuisson.com";
               $email->expediteur=$template->translate("/site/[site_code]/mail_expediteur");
               $email->subject="Récapitulatif de commande sur ".$template->translate("/site/nom");
               $email->body=stripslashes($htmlmess);
               //$email->sendmail();
          }    
     } else { //Utilisateur inconnu

          $template->parts["zonedeliste_pays"] = "";

          $sql = "SELECT *";
          $sql .= " FROM pays";
          $sql .= " WHERE paysid IN (";
          $sql .= "             SELECT DISTINCT(paysid)";
          $sql .= "             FROM fraisport";
          $sql .= ")";
          $sql .= " ORDER BY libelle ASC";
          $payss = $DB_site->query($sql);
          while($pays = $DB_site->fetch_array($payss)){
              
               $template->parts["zonedeliste_pays"] .= "<option value = ".$pays['paysid']." ".($pays['paysid'] == 57 ? "selected" : "").">".$pays['libelle']."</option>";
          }
	      
          $step = 2;
          
          $template->parts["connusite"] = ""
          . "<select name=\"connu\">"
               . "<option></option>";
          
          $sql = "SELECT *";
          $sql .= " FROM formulaire_champ_valeur";
          $sql .= " WHERE formulairechampid = 74";
          $sql .= " ORDER BY valeur";
          $creq = $DB_site->query($sql);
          while($cdata = $DB_site->fetch_array($creq)){
               $template->parts["connusite"] .= "<option value=\"".$cdata['valeur'.$template->langue]."\">".$cdata['valeur'.$template->langue]."</option>";
          }
          
          $template->parts["connusite"] .= "</select>";
          $template->parts["etape"] = "<img src=\"images/templates/commande2.jpg\" class=\"img img-responsive\">";

          if(isset($_POST['action2']) && $_POST['action2']=="inscription2")
		    $template->parts["jsinscription"]="document.getElementById('register1').style.display='none';\ndocument.getElementById('register2').style.display='block';";
          if($_SERVER['HTTP_REFERER']=="") $_SERVER['HTTP_REFERER']="/";
          $template->parts["returnurl"]=$_SERVER['HTTP_REFERER'];
          $template->parts['provenance'] = "panier";
          $template->evaltemplate("Register","pagecommande");
     }
} else {
     
     switch($action){
          
          case 'inscription':
               
               if (isset($civilite) and $civilite!=""){
                    
                    if($civilite=='0') { $selectmr="selected"; }
                    if($civilite=='1') { $selectmme="selected"; }
                    if($civilite=='2') { $selectmelle="selected"; }
               }
               
               $zonedeliste_pays = "";
               
               $sql = "SELECT *";
               $sql .= " FROM pays";
               $sql .= " WHERE 1";
               $sql .= " ORDER BY libelle ASC";
               $payss = $DB_site->query($sql);
               while($pays = $DB_site->fetch_array($payss)){
                    if($pays['paysid'] == 57){ 
                        $selected="selected";
                    } else { 
                        $selected="";
                    }

                    $zonedeliste_pays .= "<option value=\"".$pays['paysid']."\" ".$selected.">".$pays['libelle']."</option>";
               }

               $step = 2;
               
               $connusite = ""
               . "<select name=\"connu\">"
                    . "<option></option>";
               
               $sql = "SELECT *";
               $sql .= " FROM formulaire_champ_valeur";
               $sql .= " WHERE formulairechampid = 74";
               $sql .= " ORDER BY valeur";
               $creq = $DB_site->query($sql);
               while($cdata=$DB_site->fetch_array($creq)){
                    
                    $connusite .= "<option value=\"".$cdata['valeur']."\">".$cdata['description']."</option>";
               }
               
               $connusite .= "</select>";

               $template->parts["dest_panier"] = "registerPanier";
               $template->evaltemplate("Register","pagecommande");
               break ;
               
          case 'connexion':
               
               $step = 2;
               eval( "\$TemplateLogging = \"$templatecache[ConnexionSeule]\";" );
               
               break ;
               
          case 'modifad':
	    $step=3;
            $rs_facture_coordonnees=$DB_site->query_first("SELECT * FROM facture WHERE userid = '$user_info[userid]' AND facturid = '$factureid'");
            if (isset($civilite) && $civilite != "")
                $rs_facture_coordonnees[civilite] = $civilite;
                
            if (isset($nom) && $nom != "")
                $rs_facture_coordonnees[nom] = $nom;
                
            if (isset($prenom) && $prenom != "")
                $rs_facture_coordonnees[prenom] = $prenom;
                
            if (isset($adresse) && $adresse != "")
                $rs_facture_coordonnees[adresse] = $adresse;
                
            if (isset($codepostal) && $codepostal != "")
                $rs_facture_coordonnees[codepostal] = $codepostal;
                
            if (isset($ville) && $ville != "")
                $rs_facture_coordonnees[ville] = $ville;
                
            if (isset($telephone) && $telephone != "")
                $rs_facture_coordonnees[telephone] = $telephone;
                
            if (isset($raisonsociale) && $raisonsociale != "")
                $rs_facture_coordonnees[raisonsociale] = $raisonsociale;
                
            if (isset($monpays) && $monpays != "")
                $rs_facture_coordonnees[paysid] = $monpays;
                
            if ($rs_facture_coordonnees[civilite] == '0') 
                $selectmr="selected"; 
                
            if ($rs_facture_coordonnees[civilite] == '1') 
                $selectmme="selected"; 
                
            if ($rs_facture_coordonnees[civilite] =='2') 
                $selectmelle="selected"; 
                
            $payss=$DB_site->query("SELECT * FROM pays WHERE paysid IN (select DISTINCT(paysid) FROM fraisport) ORDER BY libelle ASC");
            $Cde2Autre_lstPays = "" ;
            while ($pays=$DB_site->fetch_array($payss)){
                if ($rs_facture_coordonnees[paysid] == $pays[paysid]) 
                    $selected = "selected";
                else 
                    $selected = "";
                    
                $Cde2Autre_lstPays .= "<option value=$pays[paysid] $selected>$pays[libelle]</option>";
            }
            eval( "\$TemplateCde2AdresseLivr = \"$templatecache[Cde2AdresseLivrFormu]\";" );
            break ;
    }
}


//Paramétrage des META référencement de la page
if($sitepro)
{    
     $title_trad = '/index/meta_title_pro';
     $title_desc = '/index/meta_description_pro';
}
else
{
     $title_trad = '/index/meta_title';
     $title_desc = '/index/meta_description';
}
//BarreCookie
$template->parts["stylePanier"]="style=\"bottom:0;\"";

$template->pageparams($host, $template->translate($title_trad), $template->translate($title_desc));

$template->parts["remarketing"]=google_remarketing('',"purchase",'');

$template->evaltemplate("PanierPage","main");

$template->out();


?>
