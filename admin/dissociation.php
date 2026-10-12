<?php

// GARANTIES

class dissociation{

     private $DB;
     public $facturid;
     public $site_code;
     public $prixport;

     function __construct($DB,$facturid){
          
          $this->facturid = $facturid;
          $this->DB       = $DB;
          
          $sql = "SELECT *";
          $sql .= " FROM lignefacture";
          $sql .= " WHERE factureid = ".$facturid;
          $lignes = $this->DB->query($sql);
          
          while($ligne=$this->DB->fetch_array($lignes)) $this->dissligne($ligne);
     }

     function dissligne($ligne){
	  
          $sql = "SELECT heurefacture, userid, site_code, prixport";
          $sql .= " FROM facture";
          $sql .= " WHERE facturid = ".$ligne['factureid'];
          $datecmd = $this->DB->query_first($sql);
          
	     $this->site_code = $datecmd['site_code'];
	     if($this->site_code=="") $this->site_code="base";
          $this->prixport = $datecmd['prixport'];
	    
          // on recupère tout les articles du lot
          $sql = "SELECT al.id, al.caractvalids, al.qte, al.artid, als.prix, als.paht, a.prixht";
          $sql .= " FROM article_lot al";
          $sql .= " INNER JOIN article_lot_site als ON (als.alid = al.id)";
          $sql .= " INNER JOIN article a ON (a.artid = al.artid)";
          $sql .= " WHERE lotid = ".$ligne['artid']."";
          $sql .= " AND als.site_code = '".$this->site_code."'";
          $artlot = $this->DB->query($sql);

          // on vérifie si chèque cadeau
          $sql = "SELECT artcode, artid, qte";
          $sql .= " FROM lignefacture";
          $sql .= " WHERE artid = ".$ligne['artid']." AND artcode LIKE '%ACI-KDO%' AND lignefacturedissid = 0 AND lignefactureid=".$ligne['lignefactureid'];
          $cheques_kdo = $this->DB->query($sql);
          
          $sql = "SELECT *";
          $sql .= " FROM utilisateur";
          $sql .= " WHERE userid = ".intval($datecmd['userid']);
	     $user = $this->DB->query_first($sql);
          
	     $langue = $user['langue'];

	     $tscmd = mktime(0,0,0,date("n",$datecmd[0]),date("j",$datecmd[0]),date("Y",$datecmd[0]));
	     $totallot = $totaldiss = 0;
          $txt ="<br>";

          //s'il y a plusieurs chèques cadeau d'un même montant, on dissocie la ligne pour avoir plusieurs PDF (et donc plusieurs code promo)
          if($this->DB->num_rows($cheques_kdo) > 0 && $ligne['qte'] > 1){
               $sql = "INSERT INTO lignefacturediss";
               $sql .= " SELECT *";
               $sql .= " FROM lignefacture";
               $sql .= " WHERE lignefactureid = ".$ligne['lignefactureid'];
               $this->DB->query($sql);
               $lfdid = $this->DB->insert_id();

               for($i=0;$i<$ligne['qte'];$i++){
                    $sql = "INSERT INTO lignefacture SET";
                    $sql .= " factureid = ".$ligne['factureid'];
                    $sql .= ", artid    = ".$ligne['artid'];
                    $sql .= ", catid    = ".$ligne['catid'];
                    $sql .= ", artcode  = '".$ligne['artcode']."'";
                    $sql .= ", qte      = 1";
                    $sql .= ", libelle  = '".mysqli_real_escape_string($this->DB->link_id,$ligne['libelle'])."'";
                    $sql .= ", prix     = ".$ligne['prix'];
                    $sql .= ", tva      = ".$ligne['tva'];
                    $sql .= ", paht     = ".$ligne['paht'];
                    $sql .= ", ecopart  = ".$ligne['ecopart'];
                    $sql .= ", lignefacturedissid = ".intval($lfdid);
                    $this->DB->query($sql);
               }
               $sql = "DELETE FROM lignefacture";
               $sql .= " WHERE lignefactureid = ".$ligne['lignefactureid'];
               $this->DB->query($sql); 
          }

	     if($this->DB->num_rows($artlot) > 0){
               
               $garanties = $this->garanties_lot($ligne['lignefactureid']);
               //on copie la ligne non dissocié dans lignefacturediss pour ensuite faire la dissocoation
               $sql = "INSERT INTO lignefacturediss";
               $sql .= " SELECT *";
               $sql .= " FROM lignefacture";
               $sql .= " WHERE lignefactureid = ".$ligne['lignefactureid'];
               $this->DB->query($sql);

               $lfdid = $this->DB->insert_id();
               $txt .= " Commande: #CDE".$ligne['factureid']."#";
               //On vérifie le prix total de vente du lot. 
               //S'il est à 0, c'est que le produit est offert, donc on va bien indiquer 0 (et non le prix normal) dans la dissociation plus bas.
               $sql = "SELECT prix, tva";
               $sql .= " FROM lignefacture";
               $sql .= " WHERE lignefactureid = ".$ligne['lignefactureid'];
               $prix_vente = $this->DB->query_first($sql);
               $prix_vente = $prix_vente['prix'];

               while($art = $this->DB->fetch_array($artlot)){//while chaque article du lot
                    
                    $artcaract = array();
                    
                    if($art['caractvalids'] != "") $artcaract = explode(",",$art['caractvalids']);
		          //prix temporaire
                    $sql = "SELECT tpl.*";
                    $sql .= " FROM temp_price_lot tpl";
                    $sql .= " INNER JOIN temp_price tp ON (tp.id = tpl.tpid)";
                    $sql .= " WHERE tp.artid = ".$ligne['artid'];
                    $sql .= " AND tpl.alid = ".$art['id'];
                    $sql .= " AND ".$tscmd." BETWEEN tp.du AND (tp.au + 86399)";
                    $tp = $this->DB->query_first($sql);
                    
                    if($tp['alid'] == $art['id']){
                        
                         $art['prix'] = $tp['prix'];
                         $art['paht'] = $tp['paht'];
                    }
                    
                    $sql = "SELECT TVAtauxnormal AS taux1, TVAtauxreduit AS taux2";
                    $sql .= " FROM pays";
                    $sql .= " WHERE paysid = '57'";
                    $tvafrance = $this->DB->query_first($sql);
                    
                    $sql = "SELECT artcode, catid, tauxchoisi, libelle".$langue." AS libelle, ecopart";
                    $sql .= " FROM article";
                    $sql .= " WHERE artid = ".$art['artid'];
                    $article = $this->DB->query_first($sql);
                    //on recupère le prix de l'article puis on calcul le ttc
                    $art['prix'] = $art['prix']/(1+$tvafrance['taux'.$article['tauxchoisi']]/100);
                    $art['prix'] = $art['prix']*(1+$ligne['tva']/100);
                    $txt .="<br><br>".$art['artcode']." <br>prix unitaire article: ".$art['prix'];
                    $txt .="  qte article du lot : ".$art['qte'].", qte lot (qte ligne) : ".$ligne['qte'];
                    $totallot += ($art['prix']*$art['qte']*$ligne['qte']);

                    $sql = "INSERT INTO lignefacture SET";
                    $sql .= " factureid = ".$ligne['factureid'];
                    $sql .= ", artid    = ".intval($art['artid']);
                    $sql .= ", catid    = ".intval($article['catid']);
                    $sql .= ", artcode  = '".$article['artcode']."'";
                    $sql .= ", qte      = ".($art['qte']*$ligne['qte']);
                    $sql .= ", libelle  = '".mysqli_real_escape_string($this->DB->link_id,$article['libelle'])."'";
                    $sql .= ", prix     = ".($prix_vente > 0 ? $art['prix'] : 0);
                    $sql .= ", tva      = ".$ligne['tva'];
                    $sql .= ", paht     = ".$art['paht'];
                    $sql .= ", ecopart  = ".$article['ecopart'];
                    $sql .= ", lignefacturedissid = ".intval($lfdid);
                    $this->DB->query($sql);
                    
                    $newlineid = $this->DB->insert_id();

                    foreach($artcaract as $caractvalid){
                          
                         $sql = "SELECT cv.libelle".$langue." AS caractvalname, c.libelle".$langue." AS caractname, cv.ref, ac.differenceprix,ac.differenceprixht, ac.achat";
                         $sql .= " FROM caracteristiquevaleur cv";
                         $sql .= " INNER JOIN caracteristique c USING (caractid)";
                         $sql .= " INNER JOIN article_caractval ac USING (caractvalid)";
                         $sql .= " WHERE caractvalid = ".intval($caractvalid);
                         $sql .= " AND ac.artid = ".$art['artid'];
                         $dbcaract = $this->DB->query_first($sql);

                         if($ligne['tva']==0){
                              $differenceprix = unserialize($dbcaract['differenceprixht']);
                         }
                         else{
                              $prixsites = unserialize($dbcaract['differenceprix']);
                              $differenceprix = $prixsites[$this->site_code]/(1+$tvafrance['taux'.$article['tauxchoisi']]/100);
                              $differenceprix = $differenceprix*(1+$ligne['tva']/100);
                         }

                         
                         $sql = "INSERT INTO lignefacturecaracteristique SET";
                         $sql .= " lignefactureid     = ".$newlineid."";
                         $sql .= ", caractvalid       = ".intval($caractvalid)."";
                         $sql .= ", libelle_caract    = '".mysqli_real_escape_string($this->DB->link_id,$dbcaract['caractname'])."'";
                         $sql .= ", libelle_caractval = '".mysqli_real_escape_string($this->DB->link_id,$dbcaract['caractvalname'])."'";
                         $sql .= ", ref               = '".$dbcaract['ref']."'";
                         $sql .= ", differenceprix    = ".floatval($differenceprix)."";
                         $sql .= ", paht              = ".floatval($dbcaract['achat']);
                         $this->DB->query($sql);
                    }
                    
                    $sql = "SELECT *";
                    $sql .= " FROM lignefacturecaracteristique";
                    $sql .= " WHERE lignefactureid = ".$ligne['lignefactureid'];
                    $caracts = $this->DB->query($sql);
                    
                    while($caract = $this->DB->fetch_array($caracts)){

                         $libelle_ral = preg_replace('/ mat [a-zA-Z: ]*/','',$caract['libelle_caractval']);
                         $libelle_ral = preg_replace('/ brillant [a-zA-Z: ]*/','',$libelle_ral);
                         $liste_ral=$this->DB->query_first("SELECT caractvalid FROM caractval_ral WHERE ral LIKE '%".$libelle_ral."%'");
                         
                         ///si l'option est une couleur RAL :
                         if(!is_null($liste_ral)){
                              $subcaractid = $this->DB->query_first("SELECT subcaractid FROM `caracteristiquevaleur` WHERE `caractvalid` = '".$caract['caractvalid']."'");
                              $caracvals = $this->DB->query_first("SELECT caractvalid FROM `caracteristiquevaleur` WHERE `libelleFR` = '".$caract['libelle_caractval']."' AND caractid = ".$subcaractid['subcaractid']);
                              $sql = "SELECT cv.libelle".$langue." AS caractvalname, c.libelle".$langue." AS caractname, cv.ref, ac.differenceprix, ac.differenceprixht, ac.achat, cv.prix_fixe, cv.pvttc, cv.pvht, cv.paht, c.libelleFR as libelleral";
                              $sql .= " FROM caracteristiquevaleur cv";
                              $sql .= " INNER JOIN caracteristique c USING (caractid)";
                              $sql .= " INNER JOIN article_caractval ac USING (caractvalid)";
                              $sql .= " WHERE caractvalid=".intval($caract['caractvalid']);
                              $sql .= " AND ac.artid = ".$art['artid'];
                         }
                         else{
                              $sql = "SELECT cv.libelle".$langue." AS caractvalname, c.libelle".$langue." AS caractname, cv.ref, ac.differenceprix, ac.differenceprixht, ac.achat, cv.prix_fixe, cv.pvttc, cv.pvht, cv.paht";
                              $sql .= " FROM caracteristiquevaleur cv";
                              $sql .= " INNER JOIN caracteristique c USING (caractid)";
                              $sql .= " INNER JOIN article_caractval ac USING (caractvalid)";
                              $sql .= " WHERE caractvalid=".intval($caract['caractvalid']);
                              $sql .= " AND ac.artid = ".$art['artid'];
                         }
                         
                         $dbcaract=$this->DB->query_first($sql);

                         if($dbcaract['prix_fixe'] == '1'){
                              if($ligne['tva']==0){
                                   $differenceprix=$dbcaract['pvht'];
                              }
                              else{
                                   $differenceprix=$dbcaract['pvttc'];
                              }
                              $achat=$dbcaract['paht'];
                         }
                         else{
                              if($ligne['tva']==0){
                                   $differenceprix=unserialize($dbcaract['differenceprixht'])['base'];
                              }
                              else{
                                   $prixsites = unserialize($dbcaract['differenceprix']);
                                   $differenceprix = $prixsites[$this->site_code]/(1+$tvafrance['taux'.$article['tauxchoisi']]/100);
                                   $differenceprix = $differenceprix*(1+$ligne['tva']/100);
                              }
                              $achat=$dbcaract['achat'];
                         }


                         if(!in_array($caract['caractvalid'],$artcaract) && $dbcaract[0]!=""){
                             
                              $sql = "INSERT INTO lignefacturecaracteristique SET";
                              $sql .= " lignefactureid     = ".$newlineid."";
                              $sql .= ", caractvalid       = ".intval($caract['caractvalid'])."";
                              $sql .= ", libelle_caract    = '".mysqli_real_escape_string($this->DB->link_id,$caract['libelle_caract'])."'";
                              $sql .= ", libelle_caractval = '".mysqli_real_escape_string($this->DB->link_id,$caract['libelle_caractval'])."'";
                              $sql .= ", ref               = '".$caract['ref']."'";
                              $sql .= ", differenceprix    = ".$differenceprix."";
                              $sql .= ", paht              = ".$achat;
                              $this->DB->query($sql);
                              
                              if($differenceprix > 0){
                                   $sql = "UPDATE lignefacture SET";
                                   $sql .= " prix = prix + ".$differenceprix;
                                   $sql .= " WHERE lignefactureid = ".$newlineid;
                                   $this->DB->query($sql);
                                   $totaldiss += $differenceprix*$ligne['qte']*$art['qte'];
                                   $txt .="<br>caractéristique : ".$caract['libelle_caract'].". Options : ".$caract['libelle_caractval'].". Prix unitaire option: ".$differenceprix.". Prix total option:".$differenceprix*$ligne['qte']*$art['qte'];
                              }
                         }
                    }
                    
                    //Récupération des garanties associées à l'article
                    $artgarlist = $this->garanties_article($art['artid']);
                    
                    if($artgarlist != array()){ //Reconstruction des différentes garanties, uniquement dans le cas d'un lot
                         
                         $artgarids = array_keys($artgarlist);

                         //Boucles sur les garanties enregistrées pour le lot
                         foreach($garanties as $garantieid => $lotgarantie){

                              //Si la garantie de l'article correspond bien à une garantie du lot
                              if(in_array($garantieid, $artgarids) || intval($lotgarantie['type']) == 6){
                                   
                                   $garantie = new garantie($this->DB, $this->site_code, $langue, $garantieid, $art['artid'], $art['prixht']);
                                   
                                   $sql = "INSERT INTO lignefacturegarantie SET";
                                   $sql .= " lignefactureid   = ".$newlineid."";
                                   $sql .= ", garantieid      = '".$garantieid."'";
                                   $sql .= ", libelle         = '".$lotgarantie['libelle']."'";
                                   $sql .= ", differenceprix  = '".$garantie->prix."'";
                                   $sql .= ", paht            = '".$garantie->paht."'";
                                   $sql .= ", ppi             = '".$garantie->ppi."'";
                                   $sql .= ", duree           = '".$lotgarantie['duree']."'";
                                   $sql .= ", fournisseur     = '".$lotgarantie['fournisseur']."'";
                                   $sql .= ", codeFournisseur = '".$lotgarantie['codeFournisseur']."'";
                                   $sql .= ", pays            = '".$lotgarantie['pays']."'";
                                   $sql .= ", dureeReelle     = '".$lotgarantie['dureeReelle']."'";
                                   $sql .= ", type            = '".$lotgarantie['type']."'";
                                   $this->DB->query($sql);

                                   if($garantie->prix > 0){

                                        $sql = "UPDATE lignefacture SET";
                                        $sql .= " prix = prix + ".floatval($garantie->prix);
                                        $sql .= " WHERE lignefactureid = ".$newlineid;
                                        $this->DB->query($sql);
                                        $totaldiss += floatval($garantie->prix);
                                        $txt .="<br>garantie prix: ".floatval($garantie->prix);
                                   }

                                   //on met a jour la ou les nouvelles lignefactureid dans la table platinehome
                                   if(intval($lotgarantie['type']) == 6){
                                        if(is_null($oldLine)){
                                            
                                            $oldLine = $this->DB->query_first("SELECT * FROM platinehome WHERE lignefactureid = '".$ligne['lignefactureid']."'");

                                            $sql = "UPDATE platinehome SET"; 
                                            $sql .= " lignefactureid = '".$newlineid."', count = 1 ";
                                            $sql .= " WHERE lignefactureid = '".$ligne['lignefactureid']."'";
                                            $this->DB->query($sql); 
                                        }else{
                                             for($i=0;$i<$ligne['qte'];$i++){
                                                 $sql = "INSERT INTO platinehome SET"; 
                                                 $sql .= " lignefactureid = '".$newlineid."' , factureid ='".$oldLine['factureid']."',";
                                                 $sql .=" userid ='".$oldLine['userid']."', naissance='".$oldLine['naissance']."'," ;
                                                 $sql .=" telephone ='".$oldLine['telephone']."', iban ='".$oldLine['iban']."', bic='".$oldLine['bic']."',";
                                                 $sql .=" offre ='".$oldLine['offre']."', offre_prix='".$oldLine['offre_prix']."',";
                                                 $sql .=" offre_categorie_id='".$oldLine['offre_categorie_id']."', count = 1";
                                                 $this->DB->query($sql); 
                                             }
                                        }
                                   }
                              }
                         }
                    }
               }

               $sql = "SELECT *";
               $sql .= " FROM vente_flash_article vfa";
               $sql .= " INNER JOIN vente_flash vf ON (vf.id = vfa.vfid)";
               $sql .= " WHERE (".$datecmd[0]." BETWEEN vf.du AND vf.au)";
               $sql .= " AND vfa.artcode = '".$ligne['artcode']."'";
               $vfarticle = $this->DB->query_first($sql);
	      
               if($vfarticle['id'] != ""){
		
                    $sql = "SELECT artcode, catid, tauxchoisi, libelle".$langue." AS libelle";
                    $sql .= " FROM article";
                    $sql .= " WHERE artid = ".$ligne['artid'];
                    $article = $this->DB->query_first($sql);
                    
                    $vfarticle['prix'] = $vfarticle['prix']/(1+$tvafrance['taux'.$article['tauxchoisi']]/100);
                    $vfarticle['prix'] = $vfarticle['prix']*(1+$ligne['tva']/100);
                    $vfdiff =- $totallot+$vfarticle['prix'];
                    
                    $sql = "INSERT INTO lignefacture SET";
                    $sql .= " factureid = ".$ligne['factureid']."";
                    $sql .= ", artid    = 0";
                    $sql .= ", catid    = 0";
                    $sql .= ", artcode  = ''";
                    $sql .= ", qte      = ".$ligne['qte']."";
                    $sql .= ", libelle  = 'Réduction vente flash'";
                    $sql .= ", prix     = ".floatval($vfdiff)."";
                    $sql .= ", tva      = ".$ligne['tva']."";
                    $sql .= ", paht     = 0";
                    $sql .= ", lignefacturedissid = ".intval($lfdid);
                    $this->DB->query($sql);
	          }

               $sql = "SELECT *";
               $sql .= " FROM vente_privee_article vfa";
               $sql .= " INNER JOIN vente_privee vf ON (vf.id = vfa.vfid)";
               $sql .= " WHERE (".$datecmd[0]." BETWEEN vf.du AND vf.au)";
               $sql .= " AND vfa.artcode = '".$ligne['artcode']."'";
               $vparticle = $this->DB->query_first($sql);

               if($vparticle['id'] != ""){
          
                    $sql = "SELECT artcode, catid, tauxchoisi, libelle".$langue." AS libelle";
                    $sql .= " FROM article";
                    $sql .= " WHERE artid = ".$ligne['artid'];
                    $article = $this->DB->query_first($sql);

                    $vparticle['prix'] = $vparticle['prix']/(1+$tvafrance['taux'.$article['tauxchoisi']]/100);
                    $vparticle['prix'] = $vparticle['prix']*(1+$ligne['tva']/100);
                    $vparticle['prix'] = $ligne['qte']*$vparticle['prix'];
                    $vpdiff =- $totallot+$vparticle['prix'];

                    $sql = "INSERT INTO lignefacture SET";
                    $sql .= " factureid = ".$ligne['factureid']."";
                    $sql .= ", artid    = 0";
                    $sql .= ", catid    = 0";
                    $sql .= ", artcode  = ''";
                    $sql .= ", qte      = 1";
                    $sql .= ", libelle  = 'Réduction vente privee'";
                    $sql .= ", prix     = ".floatval($vpdiff)."";
                    $sql .= ", tva      = ".$ligne['tva']."";
                    $sql .= ", paht     = 0";
                    $sql .= ", lignefacturedissid = ".intval($lfdid);
                    $this->DB->query($sql);
               }

               $sql = "DELETE FROM lignefacture";
               $sql .= " WHERE lignefactureid = ".$ligne['lignefactureid'];
               $this->DB->query($sql); 

               //on vérifie que tout c'est bien passé pour le prix lors de la diss
               $this->verif_diss(intval($lfdid),($totallot+$totaldiss),$txt);  
	     }
     }
  
private function garanties_lot($lfid){
     
     $return = array();
     
     $sql = "SELECT *";
     $sql .= " FROM lignefacturegarantie";
     $sql .= " WHERE lignefactureid = ".intval($lfid);
     $req = $this->DB->query($sql);
     
     while($gar = $this->DB->fetch_array($req)) $return[$gar['garantieid']] = $gar;

     return $return;
}
  
private function garanties_article($artid){
     
     $return = array();
     
     $sql = "SELECT *";
     $sql .= " FROM article_garantie_liste";
     $sql .= " WHERE artid = ".intval($artid);
     $req = $this->DB->query($sql);
     
     while($artgar = $this->DB->fetch_array($req)){
          
          $prix = @unserialize($artgar['prix']);
          $ppi  = @unserialize($artgar['ppi']);
          
          if($prix !== false && is_array($prix)){
              
               $artgar['prix'] = $prix[$this->site_code];
               $artgar['ppi']  = $ppi[$this->site_code];
          }

          $return[$artgar['garantieid']] = $artgar;
     }
     
     return $return;
}

private function verif_diss($lfdid,$totallot,$txt){
     $lignediss = $this->DB->query_first("SELECT prix,factureid FROM lignefacturediss WHERE lignefactureid=".$lfdid);
     if(intval($totallot) != intval($lignediss['prix'])){
          $txt .= "Cet article a été payé ".$lignediss['prix']." EUR avant dissociation et est maintenant à ".$totallot." après dissociation.<br> Le détail ci-dessous<br><br>";
          $mail=new email();
          $mail->destinataire="webmaster@mon-espace-cuisson.com";
          $mail->expediteur="contact@mon-espace-cuisson.com";
          $mail->copymode="bcc";
          $mail->to=array("adv@mon-espace-cuisson.com","pr@mon-espace-cuisson.com");
          $mail->expname="Ma cave à vin";
          $mail->subject="problème à la dissociation";
          $mail->body="Attention il y a un soucis de prix au niveau de la dissociation commande <a href=\"https://www.ma-cave-a-vin.fr/admin/facture.php?action=editer&facturid=".$lignediss['factureid']."\" target=\"_blank\">#CDE".$lignediss['factureid']."#</a><br>".$txt."<br><br>A VERIFIER !!";
          $mail->sendmail();
     }
}
  
  function log($txt){
	  $f=fopen("diss.txt","r");
	  fputs($f,$txt);
	  fclose($f);
  }

}
	 
    
?>
