<?php

// MULTISITE

class DAOgarantie {

    public static function loadAllGarantiesAbo($DB,$site_code,$langue, $index = null){
      $sql = "SELECT * FROM garantie WHERE abonnement = 1";
      $garantiesAbo = $DB->query($sql);
      $lesGarantiesAbo = array();
      while($garantie =  $DB->fetch_array($garantiesAbo)){
        $laGarantie = new garantie($DB,$site_code, $langue, $garantie["garantieid"]);
        if(is_null($index)) {                    
            $lesGaranties[] = $laGarantie;    
        } else {                      
            $lesGaranties[$laGarantie->garantieid]=$laGarantie;
        }
      }
      return $lesGaranties;
    }


    public static function loadAllGaranties($DB,$site_code,$langue,$index=null){
          
          $sql = "SELECT *";
          $sql .= " FROM garantie ";
          $garanties = $DB->query($sql);
          
          $lesGaranties = array();
          while($garantie = $DB->fetch_array($garanties)){
                    
                    $laGarantie = new garantie($DB,$site_code, $langue, $garantie["garantieid"]);
                    if(is_null($index)) {
                         
                         $lesGaranties[] = $laGarantie;
                         
                    } else {
                              
                         $lesGaranties[$laGarantie->garantieid]=$laGarantie;
                    }
          }
          
          return $lesGaranties;
    }

    public static function loadAllGarantiesFournGar($DB,$site_code,$langue){
          
          $sql = "SELECT *";
          $sql .= " FROM garantie";
          $sql .= " WHERE utilisation_garantie = 1";
          $garanties = $DB->query($sql);
          
          $lesGaranties = array();
          while ($garantie = $DB->fetch_array($garanties)){
               
               $laGarantie = new garantie($DB,$site_code,$langue,$garantie["garantieid"]);
               $lesGaranties[] = $laGarantie;
          }
          
          return $lesGaranties;
    }

    public static function loadGarantiesByFournisseur($DB,$site_code,$langue,$fournisseur,$index=null){
          
          $sql = "SELECT *";
          $sql .= " FROM garantie";
          $sql .= " WHERE fournisseur = \"".$fournisseur."\"";
          $garanties = $DB->query($sql);
          
          $lesGaranties = array();
          while($garantie = $DB->fetch_array($garanties)){
               
               $laGarantie = new garantie($DB,$site_code,$langue,$garantie["garantieid"]);
               if(is_null($index)){
                    $lesGaranties[] = $laGarantie;
               } else {
                    $lesGaranties[$laGarantie->garantieid] = $laGarantie;
               }
          }
          
          return $lesGaranties;
    }

    public static function loadGarantiesByMarque($DB,$site_code,$langue,$marquearticle,$index=null){
          
          $allGaranties = DAOgarantie::loadAllGaranties($DB,$site_code,$langue);
          $lesGaranties = array();
          foreach($allGaranties as $laGarantie) {
               
               if(in_array($marquearticle, $laGarantie->marques)){
                   
                    if(is_null($index)) {
                         $lesGaranties[] = $laGarantie;
                    } else {
                         $lesGaranties[$laGarantie->garantieid] = $laGarantie;
                    }
               }
          }
          
          return $lesGaranties;
    }

     public static function loadGarantiesByType($DB,$site_code,$langue,$typearticle,$index=null){

           $allGaranties = DAOgarantie::loadAllGaranties($DB,$site_code,$langue);
           $lesGaranties = array();
           foreach ($allGaranties as $laGarantie) {

                if (in_array($typearticle, $laGarantie->types_produits)) {

                     if (is_null($index)) {
                               $lesGaranties[]=$laGarantie;
                     } else {
                               $lesGaranties[$laGarantie->garantieid]=$laGarantie;
                     }
                }
           }

           return $lesGaranties;
     }
     
     public static function loadGarantiesByDestinataire($DB,$site_code,$langue,$typedestinataire,$index=null){
           
          $allGaranties = DAOgarantie::loadAllGaranties($DB,$site_code,$langue);
          $lesGaranties = array();
          foreach ($allGaranties as $laGarantie) {
                    
               if ($typedestinataire == $laGarantie->destinataire_garantie) {
                              
                    if (is_null($index)) {
                              $lesGaranties[]=$laGarantie;
                    } else {
                              $lesGaranties[$laGarantie->garantieid]=$laGarantie;
                    }
               }
          }
          
          return $lesGaranties;
     }
     
     public static function loadGarantiesByTypeMarque($DB,$site_code,$langue,$typearticle,$marquearticle){

           $allGaranties = DAOgarantie::loadAllGaranties($DB,$site_code,$langue);

           $lesGaranties = array();
           foreach ($allGaranties as $laGarantie) {

                if (in_array($typearticle, $laGarantie->types_produits) && in_array($marquearticle, $laGarantie->marques)) {

                     $lesGaranties[]=$laGarantie;
                }
           }

           return $lesGaranties;
     }

     public static function checkGarantiesArt($DB,$langue,$artid){
          
          $sql = "SELECT *";
          $sql .= " FROM article_garantie_liste";
          $sql .= " WHERE artid = ".$artid;
          $req = $DB->query($sql);
          
          $artInTable = $DB->num_rows($req);
          if($artInTable > 0) {
               
               $lesGarantiesArt = array();
               
               $sql = "SELECT *";
               $sql .= " FROM article_garantie_liste";
               $sql .= " WHERE artid = ".$artid;
               $garanties = $DB->query($sql);
               while($laGarantieArt=$DB->fetch_array($garanties)){
                    $lesGarantiesArt[]=$laGarantieArt;
               }
          } else {
               $lesGarantiesArt=0;
          }
          
          return $lesGarantiesArt;
     }

     public static function loadPaysByGarantie($DB, $idGarantie){
       $pays = $DB->query_first("SELECT pays FROM garantie WHERE garantieid= '".$idGarantie."'");
       return $pays = explode(',', $pays[0]);
     }

     public static function loadAllGarantiesByPays($DB,$site_code,$langue,$idPays){
          
          $sql = "SELECT typePays";
          $sql .= " FROM pays";
          $sql .= " WHERE paysid = ".$idPays;
          $req = $DB->query($sql);
          $typePays = $DB->fetch_array($req)[0];
          
          if($typePays != 'EE') {
               $idPays = $typePays;
          }
          
          $allGaranties = DAOgarantie::loadAllGaranties($DB,$site_code,$langue);
          $lesGaranties = array();
          foreach($allGaranties as $laGarantie) {
               
               if (in_array($idPays, $laGarantie->pays)) {
                         
                    $lesGaranties[] = $laGarantie;
               }
          }
          
          return $lesGaranties;
     }
     
     public static function loadAllGarantiesByFournArtPays($DB,$site_code,$langue,$artid,$idPays,$fournisseur,$destinataire = ''){

          $return = array();
          $artid  = intval($artid);
          $idPays = intval($idPays);

          $sql = "SELECT typePays";
          $sql .= " FROM pays";
          $sql .= " WHERE paysid = ".$idPays;
          $t = $DB->query_first($sql);
          $typePays = $t[0];
          if ($typePays!='EE') {
                    $idPays=$typePays;
          }
          
          $sql = "SELECT garantieid, pays";
          $sql .= " FROM garantie";
          $sql .= " INNER JOIN article_garantie_liste USING(garantieid)";
          $sql .= " WHERE artid=".$artid;
          $sql .= " AND fournisseur='".mysqli_real_escape_string($DB->link_id,$fournisseur)."'";
          $sql .= !empty($destinataire) ? " AND garantie_client_id = ".$destinataire : "";
          $req=$DB->query($sql);
          while($g = $DB->fetch_array($req)){
               
               $paysgar = explode(",",$g['pays']);
               
               if(in_array($idPays,$paysgar)){
                    
                    $return[] = new garantie($DB,$site_code,$langue,$g['garantieid'],$artid);
               }
          }

          return $return;
     }
     
     public static function loadAllGarantiesByArtid($DB, $site_code, $langue, $idPays, $artid, $destinataire = '', $prix_vente = 1){
          
          /*
           * Pour les pays principaux de l'Europe, on recherche par identifiant de pays
           * Pour les Reste de l'Europe et le Reste du Monde, il faut rechercher par "RE" ou "RM"
           */
          $sql = "SELECT typePays";
          $sql .= " FROM pays";
          $sql .= " WHERE paysid = ".$idPays;
          $req = $DB->query($sql);
          $typePays = $DB->fetch_array($req);
          $typePays = $typePays[0];
          if ($typePays!='EE') {
                    $idPays=$typePays;
          }
          
          if(DAOarticle::isLot($DB, $artid)){ //Traitement différent dans le cas des lots
               
               //Récupération d'un tableau contenant chaque produit du lot
               $tabArticlesLot = DAOarticle::listeArticlesLots($DB, $artid);
               
               $listeGaranties = array();
               foreach($tabArticlesLot as $le_produit){

                    //Listing des garanties disponible dans la tranche de prix du produit
                    $tabGarantie = self::getInfosGarantiesParTranche($DB, $le_produit['artid'], $destinataire, $le_produit['prix'], 'ASC');

                    //Boucle sur les garantie du produit du lot
                    foreach($tabGarantie as $la_garantie){

                         //Formatage du prix (unserialize)
                         $prix = self::getPrixGarantie($site_code, $la_garantie['prix']);
                         
                         $laGarantie = new garantie($DB, $site_code, $langue, $la_garantie['garantieid'], $le_produit['artid'], $le_produit['prix']);

                          //FILTRE : pays de l'internaute et pays couverts par garanties identiques
                          if (in_array($idPays, $laGarantie->pays)) {

                               //FILTRE : fournisseur de la garantie et fournisseur du produit identiques
                               if(($laGarantie->fournisseur == DAOarticle::getFournisseurPrincByProduct($DB,$langue,$le_produit['artid'],$site_code)) || (in_array($laGarantie->fournisseur, DAOarticle::getFournisseurGarantieByProduct($DB,$langue,$le_produit['artid'])))) {

                                    $laGarantie->multiplie($le_produit['qte']);
                                    
                                    $listeGaranties[$le_produit['artid']][] = $laGarantie;
                               }
                          }
                    }   
               }
               
               //Boucle sur les garanties retournées, consolidation des montants
               $lesGaranties = array();
               if(!empty($listeGaranties)){
                    
                    //Tri des garanties par durée
                    $listeGaranties = self::triDuree($listeGaranties);
                    
                    $tabArtids = array_keys($listeGaranties);
                    
                    //Stockage des identifiants des garanties du premier produit, pour les appliquer ensuite au lot
                    foreach($listeGaranties[$tabArtids[0]] as $la_garantie){
                         
                         $tabGarantiesIds[] = $la_garantie->garantieid;
                         $tabRetourGarantie[$la_garantie->garantieid] = $la_garantie;
                    }
                    
                    //S'il y a plus d'un produit, on boucle sur les autres pour comparer et additionner les garanties si besoin
                    if(count($tabArtids) > 1){
                         
                         for($i=1;$i<count($tabArtids);$i++){
                              
                              foreach($listeGaranties[$tabArtids[$i]] as $la_garantie){
                                   
                                   if(in_array($la_garantie->garantieid, $tabGarantiesIds)){
                                        
                                        $tabGarantie['paht'] = $tabRetourGarantie[$la_garantie->garantieid]->paht + $la_garantie->paht;
                                        $tabGarantie['prix'] = $tabRetourGarantie[$la_garantie->garantieid]->prix + $la_garantie->prix;
                                        $tabGarantie['ppi']  = $tabRetourGarantie[$la_garantie->garantieid]->ppi + $la_garantie->ppi;
                                        
                                        //Mise à jour des montants de la garantie
                                        $tabRetourGarantie[$la_garantie->garantieid]->setMontants($tabGarantie);
                                   }
                              }
                         }
                    }
                    
                    foreach($tabRetourGarantie as $garantie_retour){
                         
                         $lesGaranties[] = $garantie_retour;
                    }
               }
               
          } else { //Traitement pour un produit classique (hors lot)
               
               //On vérifie que l'article n'est pas une déclinaison de plusieurs caves, auquel cas, prise en compte du montant le plus élevé
               $tabArtGarantie = self::checkMultiProduit($DB, $artid);
               $change_prix = false;
               if(!empty($tabArtGarantie) && $prix_vente > 20000){
                    $change_prix = true;
                    $pvente = $tabArtGarantie['prix'];
               } else {
                    $pvente = $prix_vente;
               }
               
               //Listing des garanties disponible dans la tranche de prix du produit
               $tabGarantie = self::getInfosGarantiesParTranche($DB, $artid, $destinataire, $pvente, 'ASC');
               
               //Boucle sur les garantie du produit du lot
               foreach($tabGarantie as $la_garantie){
                    
                    //Formatage du prix (unserialize)
                    $prix = self::getPrixGarantie($site_code, $la_garantie['prix']);
                    
                    if($change_prix){
                         $prix_to_garantie = $prix_vente;
                    } else {
                         $prix_to_garantie = $pvente;
                    }
                    
                    $laGarantie = new garantie($DB, $site_code, $langue, $la_garantie['garantieid'], $artid, $prix_to_garantie);

                   //FILTRE : pays de l'internaute et pays couverts par garanties identiques
                   if (in_array($idPays, $laGarantie->pays)) {

                        //FILTRE : fournisseur de la garantie et fournisseur du produit identiques
                        if (($laGarantie->fournisseur == DAOarticle::getFournisseurPrincByProduct($DB,$langue,$artid,$site_code)) || (in_array($laGarantie->fournisseur, DAOarticle::getFournisseurGarantieByProduct($DB,$langue,$artid)))) {

                             $lesGaranties[] = $laGarantie;
                        }
                   }
               }
          }
          
          return $lesGaranties;
    }

     /*
      * Retourne la liste des garanties offertes, disponibles pour un produit
      * 
      * @param object $DB Connecteur à la BDD
      * @param string $site_code Code du site (ex : 'uk', 'base'...)
      * @param string $langue Langue à affiche (ex : 'FR', 'EN'...)
      * @param int $idPays Identifiant du pays du client
      * @param int $artid Identifiant du produit
      * @param int $destinataire Destinataire de la garantie ("Pro" ou "Particulier")
      * @param float $prix_vente Prix de vente de l'article
      * 
      * @return array Liste des garanties OFFERTES pour le produit
      * 
      */
     public static function loadMaxGarantieArtByPays($DB, $site_code, $langue, $idPays, $artid, $destinataire = '', $prix_vente = 1, $allowExt = true){
          
          /*
           * Pour les pays principaux de l'Europe, on recherche par identifiant de pays
           * Pour les Reste de l'Europe et le Reste du Monde, il faut rechercher par "RE" ou "RM"
           */
          $sql = "SELECT typePays";
          $sql .= " FROM pays";
          $sql .= " WHERE paysid = ".$idPays;
          $req = $DB->query($sql);
          $typePays = $DB->fetch_array($req);
          $typePays = $typePays[0];
          if($typePays != 'EE') {
               $idPays = $typePays;
          }
          
          $lesGaranties = array();
          
          if(DAOarticle::isLot($DB, $artid)){ //Traitement différent dans le cas des lots
               
               //Récupération d'un tableau contenant chaque produit du lot
               $tabArticlesLot = DAOarticle::listeArticlesLots($DB, $artid);
               
               $listeGaranties = array();
               foreach($tabArticlesLot as $le_produit){
                    
                    //Listing des garanties disponible dans la tranche de prix du produit
                    $tabGarantie = self::getInfosGarantiesParTranche($DB, $le_produit['artid'], $destinataire, $le_produit['prix']);
                    
                    //Boucle sur les garantie du produit du lot
                    foreach($tabGarantie as $la_garantie){
                         
                         //Formatage du prix (unserialize)
                         $prix = self::getPrixGarantie($site_code, $la_garantie['prix']);
                         
                         //Si c'est une garantie fournisseur (0), comparaison avec le fournisseur du produit
                         $allow_fourn = true;
                         if($la_garantie['utilisation_garantie'] == 0){
                              
                              $allow_fourn = ($la_garantie['fournisseur'] == DAOarticle::getFournisseurPrincByProduct($DB, $langue, $le_produit['artid'], $site_code));
                         }
                         
						//Si on ne veut pas récupérer les extensions, on bloque
						if(!$allowExt){
							if($la_garantie['cumul'] == 1) $allow_fourn = false;
						}
						 
                         //FILTRE : garantie offerte (gratuite) && fournisseurs identiques
                         if($prix == 0 && $allow_fourn){
                              
                              $laGarantie = new garantie($DB, $site_code, $langue, $la_garantie['garantieid'], $le_produit['artid']);
                              
                              //FILTRE : pays de l'internaute et pays couverts par garanties identiques
                              if (in_array($idPays, $laGarantie->pays)) {

                                   $listeGaranties[$le_produit['artid']][] = $laGarantie;
                              }
                         }
                    }
               }
               
               //Boucle sur les garanties retournées, et renvoi d'une seule garantie
               
               if(!empty($listeGaranties)){
                    
                    $tabArtids = array_keys($listeGaranties);
                    
                    //Pas besoin de calculs, le montant des garantie sera forcément de 0
                    $lesGaranties = $listeGaranties[$tabArtids[0]];
               }
               
          } else { //Traitement pour un produit classique (hors lot)
               
               //On vérifie que l'article n'est pas une déclinaison de plusieurs caves, auquel cas, prise en compte du montant le plus élevé
               $tabArtGarantie = self::checkMultiProduit($DB, $artid);
               if(!empty($tabArtGarantie)  && $prix_vente > 20000){
                    $prix_vente = $tabArtGarantie['prix'];
               }
               
               //Listing des garanties disponible dans la tranche de prix du produit
               $tabGarantie = self::getInfosGarantiesParTranche($DB, $artid, $destinataire, $prix_vente);
               
               //Boucle sur les garantie du produit du lot
               foreach($tabGarantie as $la_garantie){
                    
                    //Formatage du prix (unserialize)
                    $prix = self::getPrixGarantie($site_code, $la_garantie['prix']);
                    
                    //Si c'est une garantie fournisseur (0), comparaison avec le fournisseur du produit
                    $allow_fourn = true;
                    if($la_garantie['utilisation_garantie'] == 0){

                         $allow_fourn = ($la_garantie['fournisseur'] == DAOarticle::getFournisseurPrincByProduct($DB, $langue, $artid, $site_code));
                    }
					
					//Si on ne veut pas récupérer les extensions, on bloque
					if(!$allowExt){
						if($la_garantie['cumul'] == 1) $allow_fourn = false;
					}
					
                    //FILTRE : garantie offerte (gratuite) && fournisseurs identiques
                    if($prix == 0 && $allow_fourn){

                         $laGarantie = new garantie($DB, $site_code, $langue, $la_garantie['garantieid'], $artid);

                         //FILTRE : pays de l'internaute et pays couverts par garanties identiques
                         if (in_array($idPays, $laGarantie->pays)) {

                              $lesGaranties[] = $laGarantie;
                         }
                    }
               }
          }
          
          return $lesGaranties;
     }
     public static function qteArticlesLot($tabArticlesLot){
        $tabReturn = array();
        foreach ($tabArticlesLot as $article) {
          $tabReturn[$article['artid'].$article['caractvalids']] = $tabReturn[$article['artid']]+1;
        }

        return $tabReturn;
     }
     
     /*
      * Retourne la liste des garanties PAYANTES, disponibles pour un produit
      * 
      * @param object $DB Connecteur à la BDD
      * @param string $site_code Code du site (ex : 'uk', 'base'...)
      * @param string $langue Langue à affiche (ex : 'FR', 'EN'...)
      * @param int $idPays Identifiant du pays du client
      * @param int $artid Identifiant du produit
      * @param int $destinataire Destinataire de la garantie ("Pro" ou "Particulier")
      * @param float $prix_vente Prix de vente de l'article
      * 
      * @return array Liste des garanties PAYANTES pour le produit
      * 
      */
     public static function loadGarantiesPayArtByPays($DB, $site_code, $langue, $idPays, $artid, $destinataire = '', $prix_vente = 1, $txtva=null, $ligne_facture_id=null){
          
          /*
           * Pour les pays principaux de l'Europe, on recherche par identifiant de pays
           * Pour les Reste de l'Europe et le Reste du Monde, il faut rechercher par "RE" ou "RM"
           */
          $sql = "SELECT typePays";
          $sql .= " FROM pays";
          $sql .= " WHERE paysid = ".$idPays;
          $req = $DB->query($sql);
          $typePays = $DB->fetch_array($req);
          $typePays = $typePays[0];
          if ($typePays!='EE') {
                    $idPays=$typePays;
          }
          
          if(DAOarticle::isLot($DB, $artid)){ //Traitement différent dans le cas des lots
               
               //Récupération d'un tableau contenant chaque produit du lot
               $tabArticlesLot = DAOarticle::listeArticlesLots($DB, $artid);
               $qteArticles = self::qteArticlesLot($tabArticlesLot);
               $listeGaranties = array();

               $prixoptions=0;

               foreach($tabArticlesLot as $le_produit){
                    
                    $pvproduit=$le_produit['prix'];

                    //Si on est dans un lot
                    if($ligne_facture_id != null){
                         //On réccupère l'artid des articles qui composent les lots
                         $sql = "SELECT al.artid FROM article_lot al INNER JOIN article_garantie_liste agl ON agl.artid = al.artid INNER JOIN lignefacture lf ON lf.artid = al.lotid WHERE lf.lignefactureid = '".$ligne_facture_id."' GROUP BY lf.artid";
                         $artids = $DB->query($sql);
                         $liste_artids = array();

                         while($artid = $DB->fetch_array($artids)){
                              //On rassemble les artid dans un tableau
                              $liste_artids[]=$artid['artid'];
                         }
                         $liste_artids = implode(",",$liste_artids);

                         if(!empty($liste_artids) && $liste_artids!= ''){
                              //Permet de calculer la somme des options des caves en lot
                              $sql = "SELECT SUM(lfc.differenceprix) FROM lignefacturecaracteristique lfc INNER JOIN lignefacture lf ON (lfc.lignefactureid = lf.lignefactureid) INNER JOIN article_caractval cc ON cc.caractvalid = lfc.caractvalid WHERE lfc.lignefactureid = '".$ligne_facture_id."' AND cc.artid IN (".$liste_artids.")";
                              $prixcaract = $DB->query_first($sql);
                              //Le total des options comprend toutes les options d'un lot
                              $prixoptions= intval($prixcaract[0]);
                              //Si on a plusieurs fois la même option, on divise le total par la quantité, afin d'avoir le montant des options d'une seule cave pour le calcul des tranches de garanties
                              $prixoptions=$prixoptions/intval($le_produit['qte']);
                              //On ajoute le prix des options au prix de la cave
                              $pvproduit+=$prixoptions;
                         }
                    } 

                    if(!is_null($txtva)){
                        $art=$DB->query_first("SELECT tauxchoisi FROM article WHERE artid=".$le_produit['artid']);
                        $tvafrance=$DB->query_first("SELECT TVAtauxnormal AS taux1,TVAtauxreduit AS taux2,0 AS taux0 FROM pays WHERE paysid=57");
                        $pvproduit/=(1+$tvafrance['taux'.$art['tauxchoisi']]/100);
                        $pvproduit*=(1+intval($txtva)/100);
                    }
      
                    //Listing des garanties disponible dans la tranche de prix du produit
                    $tabGarantie = self::getInfosGarantiesParTranche($DB, $le_produit['artid'], $destinataire, $pvproduit, 'ASC');

                    //Boucle sur les garantie du produit du lot
                    foreach($tabGarantie as $la_garantie){
                         //Formatage du prix (unserialize)
                         $prix = self::getPrixGarantie($site_code, $la_garantie['prix']);
                         
                         //FILTRE : garantie payante
                         if($prix > 0){
                              $laGarantie = new garantie($DB, $site_code, $langue, $la_garantie['garantieid'], $le_produit['artid'], $pvproduit, $ligne_facture_id);

                              //FILTRE : pays de l'internaute et pays couverts par garanties identiques
                              if (in_array($idPays, $laGarantie->pays)) {

                                   //FILTRE : fournisseur de la garantie et fournisseur du produit identiques
                                   if(($laGarantie->fournisseur == DAOarticle::getFournisseurPrincByProduct($DB,$langue,$le_produit['artid'],$site_code)) || (in_array($laGarantie->fournisseur, DAOarticle::getFournisseurGarantieByProduct($DB,$langue,$le_produit['artid'])))) {

                                        $laGarantie->multiplie($le_produit['qte']);

                                        //$le_produit['caractvalids'] est essentiel car il permet de bien additionner les garanties quand il y a 2 produits identique (même artid) avec une option différente (caractvalids différente)
                                        $listeGaranties[$le_produit['artid'].$le_produit['caractvalids']][] = $laGarantie;
                                   }
                              }
                         }
                    }   
               }
               
               //Boucle sur les garanties retournées, consolidation des montants
               $lesGaranties = array();

               if(!empty($listeGaranties)){
                    //Tri des garanties par durée
                    $listeGaranties = self::triDuree($listeGaranties);
                    $tabArtids = array_keys($listeGaranties);

                    //Stockage des identifiants des garanties du premier produit, pour les appliquer ensuite au lot
                    foreach($listeGaranties[$tabArtids[0]] as $la_garantie){
                         $tabGarantiesIds[] = $la_garantie->garantieid;
                         //les 3 lignes suivantes dans le cas ou on ajoute plusieurs fois séparemment le même article (options différentes).
                         $la_garantie->prix = $la_garantie->prix * $qteArticles[$tabArtids[0]];
                         $la_garantie->paht = $la_garantie->paht * $qteArticles[$tabArtids[0]];
                         $la_garantie->ppi = $la_garantie->ppi * $qteArticles[$tabArtids[0]];
                         
                         $tabRetourGarantie[$la_garantie->garantieid] = $la_garantie;
                    }
                    
                    //S'il y a plus d'un produit, on boucle sur les autres pour comparer et additionner les garanties si besoin
                    if(count($tabArtids) > 1){
                         
                         for($i=1;$i<count($tabArtids);$i++){
                              
                              foreach($listeGaranties[$tabArtids[$i]] as $la_garantie){
                                   
                                   if(in_array($la_garantie->garantieid, $tabGarantiesIds)){
                                        $tabGarantie['paht'] = ($tabRetourGarantie[$la_garantie->garantieid]->paht + $la_garantie->paht)*$qteArticles[$tabArtids[$i]];
                                        $tabGarantie['prix'] = ($tabRetourGarantie[$la_garantie->garantieid]->prix + $la_garantie->prix)*$qteArticles[$tabArtids[$i]];
                                        $tabGarantie['ppi']  = ($tabRetourGarantie[$la_garantie->garantieid]->ppi + $la_garantie->ppi)*$qteArticles[$tabArtids[$i]];
                                        
                                        //Mise à jour des montants de la garantie
                                        $tabRetourGarantie[$la_garantie->garantieid]->setMontants($tabGarantie);
                                   }
                              }
                         }
                    }
                    
                    foreach($tabRetourGarantie as $garantie_retour){
                         
                         $lesGaranties[] = $garantie_retour;
                    }
               }
               
          } else { //Traitement pour un produit classique (hors lot)
               
               //On vérifie que l'article n'est pas une déclinaison de plusieurs caves, auquel cas, prise en compte du montant le plus élevé
               $tabArtGarantie = self::checkMultiProduit($DB, $artid);
               $change_prix = false;
               if(!empty($tabArtGarantie) && $prix_vente > 20000){
                    $change_prix = true;
                    $pvente = $tabArtGarantie['prix'];
               } else {
                    $pvente = $prix_vente;
               }
               
               //Listing des garanties disponible dans la tranche de prix du produit
               $tabGarantie = self::getInfosGarantiesParTranche($DB, $artid, $destinataire, $pvente, 'ASC');
               
               //Boucle sur les garantie du produit du lot
               foreach($tabGarantie as $la_garantie){
                    
                    //Formatage du prix (unserialize)
                    $prix = self::getPrixGarantie($site_code, $la_garantie['prix']);
                    
                    if($change_prix){
                         $prix_to_garantie = $prix_vente;
                    } else {
                         $prix_to_garantie = $pvente;
                    }
                    
                    //FILTRE : garantie payante
                    if($prix > 0){

                         $laGarantie = new garantie($DB, $site_code, $langue, $la_garantie['garantieid'], $artid, $prix_to_garantie, $ligne_facture_id);

                         //FILTRE : pays de l'internaute et pays couverts par garanties identiques
                         if (@in_array($idPays, $laGarantie->pays)) {

                              //FILTRE : fournisseur de la garantie et fournisseur du produit identiques
                              if (($laGarantie->fournisseur == DAOarticle::getFournisseurPrincByProduct($DB,$langue,$artid,$site_code)) || (in_array($laGarantie->fournisseur, DAOarticle::getFournisseurGarantieByProduct($DB,$langue,$artid)))) {

                                   $lesGaranties[] = $laGarantie;
                              }
                         }
                    }
               }
          }
          return $lesGaranties;
     }

     public static function searchGarantieToAddExtension($DB,$type_garantie,$tabGaranties,$garantieid) {
          
          //On cherche dans le tableau qui contient toutes les garanties, celle du fournisseur des années de garanties avant l'extension et du même type
          $pointers = in_object_array($type_garantie,$tabGaranties,"type_garantie");

          $dureeCompare = 0;
          for($i=0; $i < count($pointers); $i++) {
               
               //si la garantie n'est pas l'extension elle même
               if($tabGaranties[$pointers[$i]]->garantieid != $garantieid) {
                    
                    //SI cette garantie plus longue que celle d'avant
                    if ($tabGaranties[$pointers[$i]]->duree>$dureeCompare) {

                         $dureeCompare = $tabGaranties[$pointers[$i]]->duree;
                         $pointer = $pointers[$i];
                    }
               }
          }
          
          return $pointer;
     }

     public static function loadGarantiesArtByPaysSaufExtensions($DB,$site_code,$langue,$idPays,$artid,$destinataire = ''){
          
          $sql = "SELECT typePays";
          $sql .= " FROM pays";
          $sql .= " WHERE paysid = ".$idPays;
          $typePays = $DB->fetch_array($DB->query($sql))[0];
          
          if($typePays != 'EE') {
               $idPays = $typePays;
          }
          
          $qte = DAOgarantie::lotGaranties($DB,$artid);
          
          $lesGaranties = array();
          
          $sql = "SELECT g.*";
          $sql .= " FROM garantie g, article_garantie_liste ag";
          $sql .= " WHERE g.garantieid = ag.garantieid";
          $sql .= " AND ag.artid = '".$artid."'";
          $sql .= " AND g.cumul = 2";
          $sql .= !empty($destinataire) ? " AND g.garantie_client_id = ".$destinataire : "";
          $sql .= " ORDER BY g.duree ASC";
          $garanties = $DB->query($sql);
          
          while ($garantie = $DB->fetch_array($garanties)){
               
               $laGarantie = new garantie($DB,$site_code,$langue,$garantie["garantieid"],$artid);

               if (in_array($idPays, $laGarantie->pays)) {
                    
                    if (($laGarantie->fournisseur==DAOarticle::getFournisseurPrincByProduct($DB,$langue,$artid,$site_code)) || (in_array($laGarantie->fournisseur, DAOarticle::getFournisseurGarantieByProduct($DB,$langue,$artid)))) {
                         
                         $lesGaranties[]=$laGarantie;
                    }
               }
          }
          
          return $lesGaranties;
     }
    
     /*
      * Retourne les informations sur la garantie choisie lors d'un achat de produit
      */
     public static function getGarantiesByProduitCmd($DB, $ligne_facture_id){
          $tabGarantie = array();
          
          $sql = "SELECT g.*, gt.*, lfg.lignefacturegarantie, lfg.garantieid, lfg.duree AS dureeAffiche, lfg.paht AS paht";
          $sql .= " FROM lignefacturegarantie lfg";
          $sql .= " INNER JOIN garantie g USING(garantieid)";
          $sql .= " INNER JOIN garantie_type gt ON (gt.id_type = g.type_garantie)";
          $sql .= " WHERE lfg.lignefactureid = '".$ligne_facture_id."'";
		$sql .= " ORDER BY lfg.lignefacturegarantie ASC";

          $infosGarantie = $DB->query($sql);
          if($DB->num_rows($infosGarantie) > 0){
               while($la_garantie = $DB->fetch_array($infosGarantie)){
                    $tabGarantie[] = $la_garantie;
               }
          } 
          
          return $tabGarantie;
     }
     
     /*
      * Retourne les informations sur la meilleure garantie choisie lors d'un achat de produit
      */
     public static function getBestGarantiesByProduitCmd($DB, $ligne_facture_id){
          $tabGarantie = array();
          
          $sql = "SELECT g.garantieid, g.duree, g.cumul, g.type_garantie, lfg.differenceprix as prix, g.garantie_client_id";
          $sql .= " FROM lignefacturegarantie lfg";
          $sql .= " INNER JOIN garantie g USING(garantieid)";
          $sql .= " INNER JOIN garantie_type gt ON (gt.id_type = g.type_garantie)";
          $sql .= " WHERE lfg.lignefactureid = '".$ligne_facture_id."'";
          $sql .= " ORDER BY gt.libelle_ponderation ASC, lfg.duree DESC";
          $sql .= " LIMIT 0,1";
          $infosGarantie = $DB->query_first($sql); 
          
          return $infosGarantie;
     }
     
     //Retourne la quantité d'articles dans le lot
     //Change la valeur de $artid avec le produit le plus cher du lot (pour calculer le montant de la garantie)
     public static function lotGaranties($DB, &$artid, &$prix_vente = 0){
	  
          $glist    = array();
	  $nbgarart = 0;
	  
          $sql = "SELECT *";
          $sql .= " FROM article_lot";
          $sql .= " WHERE lotid = ".intval($artid);
          $lot = $DB->query($sql);
          
	  if($DB->num_rows($lot) == 0) return false;
	  
          while($art = $DB->fetch_array($lot)){
               
               $garanties = DAOgarantie::checkGarantiesArt($DB,"FR",$art['artid']);
               
               if(is_array($garanties)){
                    
                    $nbgarart += $art['qte'];
                    
                    foreach($garanties as $g){
                        
                         $glist[$art['artid']][] = intval($g['garantieid']);
                    }
               }
	  }
	  
	  if(count($glist) == 0) return false;

	  $error   = false;
	  $testgar = $glist;
	  $comp    = array_pop($testgar);
	  
          foreach($testgar as $g){
               
               if(array_diff($comp,$g)!=array()){
                    
                    $error=true;
                    break;
               }
               
               $comp = array_pop($testgar);
	  }
	  
	  if(!$error){
               
               //$artid : id du lot
               //$prix_vente : prix de vente à modifier par le total
               
               $sql = "SELECT al.prix";
               $sql .= " FROM article_lot al";
               $sql .= " WHERE al.lotid = ".$artid;
               $sql .= " ORDER BY al.prix DESC";
               $sql .= " LIMIT 1";
               $art = $DB->query_first($sql);
               $prix_vente = $art['prix'];
               
               $artid = array_pop(array_keys($glist));
               
               return $nbgarart;
               
	  } else {
               
               $sql = "SELECT a.artid, al.qte, al.prix";
               $sql .= " FROM article a";
               $sql .= " INNER JOIN article_lot al ON(al.artid = a.artid)";
               $sql .= " WHERE al.lotid = ".$artid;
               $sql .= " ORDER BY al.prix DESC";
               $sql .= " LIMIT 1";
               $art = $DB->query_first($sql);
               
               $artid      = $art['artid'];
               $prix_vente = $art['prix'];
               
               return $art['qte'];
	  }
     }
     
     /*
      * Retourne une liste de garanties, en fonction du prix du produit
      * 
      * @param object $DB Connecteur à la BDD
      * @param int $artid Identifiant du produit
      * @param int $destinataire Destinataire de la garantie ("Pro" ou "Particulier")
      * @param float $prix_vente Prix de vente de l'article
      * 
      * @return array Liste des garanties
      * 
      */
     public static function getInfosGarantiesParTranche($DB, $artid, $destinataire = '', $prix_vente = 1, $order_duree = 'DESC'){
          
          //Construction de la requête de sélection
          $sql = "SELECT g.*, gtranche.garantie_tranche_paht as paht, gtranche.garantie_tranche_prix as prix, gtranche.garantie_tranche_ppi as ppi";
          $sql .= " FROM (garantie g, article_garantie_liste ag, garantie_type gt)";
          $sql .= " INNER JOIN garantie_tranche gtranche ON (gtranche.garantieid = g.garantieid)";
          $sql .= " WHERE g.garantieid = ag.garantieid";
          $sql .= " AND g.type_garantie = gt.id_type AND gt.id_type!=6";
          $sql .= " AND ag.artid = '".$artid."'";
          $sql .= !empty($destinataire) ? " AND g.garantie_client_id = ".$destinataire : "";
          $sql .= " AND '".$prix_vente."' BETWEEN gtranche.garantie_tranche_prix_mini AND gtranche.garantie_tranche_prix_maxi";
          $sql .= " ORDER BY gt.libelle_ponderation ASC, g.cumul ASC, g.duree ".$order_duree;
          $req = $DB->query($sql);

          $tabGarantie = array();
          if($DB->num_rows($req) > 0){
               
               while($row = $DB->fetch_array($req)){

                    $tabGarantie[] = $row;
               }
          }
          
          return $tabGarantie;
     }
     public static function getInfosGarantiesParTrancheAbo($DB, $artid, $garantieid, $prix_vente = 1, $order_duree = 'DESC'){
        $sql = "SELECT  gtranche.garantie_tranche_prix as prix, gtranche.garantie_tranche_prix_mini as prixmini, gtranche.garantie_tranche_prix_maxi as prixmaxi";
        $sql .=" FROM garantie g";
        $sql .=" INNER JOIN garantie_tranche gtranche ON (gtranche.garantieid = g.garantieid)";
        $sql .=" WHERE g.garantieid = '".$garantieid."'";
        $sql .= " AND '".$prix_vente."' BETWEEN gtranche.garantie_tranche_prix_mini AND gtranche.garantie_tranche_prix_maxi";
        $sql .= " ORDER BY prix ";
        $req = $DB->query($sql);
        if($DB->num_rows($req) > 0){
          $tabGarantie = $DB->query_first($sql); 
        }
          
          return $tabGarantie;
     }
     
     /*
      * Retourne le prix d'une garantie, correctement formaté
      * 
      * @param string $site_code Code du site (ex : 'uk', 'base'...)
      * @param float $prix Prix a formater
      * 
      * @return float Prix de la garantie, au bon format
      * 
      */
     public static function getPrixGarantie($site_code, $prix){
          
          $prixsites = @unserialize($prix);
               
          if($prixsites === false){

               $prix = floatval($prix);
               
          } else {

               $prix = $prixsites[$site_code];
          }
          
          return $prix;

     }
     
     public static function triDuree($arrayToOrder){
          $tabReturn = array();
          foreach($arrayToOrder as $key => $values){
               
               if(is_array($values)){
                    
                    $tabTri = array();
                    foreach($values as $obj){
                         
                         $tabTri[] = $obj;
                    }
                    
                    ksort($tabTri);
                    
                    $tabRetour = array();
                    foreach($tabTri as $obj){
                         
                         $tabRetour[$obj->duree] = $obj;
                    }
                    
                    $tabReturn[$key] = $tabRetour;
                    
               } else {
                    
                    $tabTri[$values->duree] = $values;
                    
                    $tabReturn = array_merge($tabReturn, $tabTri);
               }
          }
          
          return $tabReturn;
     }
     
     public static function checkMultiProduit($DB, $artid){
          
          $sql = "SELECT prix";
          $sql .= " FROM article_garantie";
          $sql .= " WHERE artid = '".$artid."'";
          $sql .= " ORDER BY prix DESC";
          $tabPrix = $DB->query_first($sql);
          
          $sql = "SELECT COUNT(*) as nb";
          $sql .= " FROM article_garantie";
          $sql .= " WHERE artid = '".$artid."'";
          $nbPdts = $DB->query_first($sql);
          
          $tabReturn = array();
          if(isset($tabPrix['prix'])){
               $tabReturn['prix'] = $tabPrix['prix'];
               $tabReturn['nb']   = $nbPdts['nb'];
          }

          return $tabReturn;
     }

     public static function platinehome($DB,$garantieid,$lignefacture,$raisonsociale,$paysid){
      $totalttc = round($lignefacture->totalttc / $lignefacture->qte);
        if($raisonsociale!='' || $totalttc > 6000){ //offre pas pour les pro et prix max 6000
          return false;
        }

        $bonType = false ;
        $inPays = false;
        $marque = false;
        $platine = $DB->query_first("SELECT garantieid, types_produits, marques, pays FROM garantie WHERE garantieid='".$garantieid."'");
        //on vérifie si l'article fait parti du type article pris en charge
        $types_produits = explode(',', $platine['types_produits']) ;
        $marques = explode(',', $platine['marques']);
        $pays = explode(',', $platine['pays']);
        if(in_array($lignefacture->typearticleid, $types_produits)) $bonType = true;
        if(in_array($lignefacture->marqueid ,$marques)) $marque = true;
        if(in_array($paysid, $pays)) $inPays = true;

        if($bonType && $inPays && $marque)
          return true;
        else
          return false;
     }

     //Cette fonction verifie que l'article est Eligible au platinehome
     public static function platinehomeArticles($DB, $artid, $garantieid){
      $bonType = $marque = false;
      $platine = $DB->query_first("SELECT garantieid, types_produits, marques, pays FROM garantie WHERE garantieid='".$garantieid."'");
      $articleType = $DB->query_first("SELECT typearticleid FROM article WHERE artid ='".$artid."'");
      $articleMarque = $DB->query_first("SELECT marqueid FROM article_marque WHERE artid='".$artid."'");

      //on vérifie si l'article fait parti du type article pris en charge
      $types_produits = explode(',', $platine['types_produits']) ;
      $marques = explode(',', $platine['marques']);
      if(in_array($articleType[0], $types_produits)) $bonType = true;
      if(in_array($articleMarque[0] ,$marques)) $marque = true;

      if($bonType && $marque)
        return true;
      else
        return false;

     }

     public static function platinehomeID($DB){
        $platine = $DB->query_first("SELECT garantieid, types_produits, marques, pays FROM garantie WHERE utilisation_garantie = 2 AND type_garantie = 6 AND garantie_client_id = 1");
        $platinehome = $platine['garantieid'] ; //idGarantie de platine home
        return $platinehome;
     }
}
