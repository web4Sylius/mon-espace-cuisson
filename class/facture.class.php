<?php

class facture{

     private $DB;

     public $facturid;
     public $numerofacture;
     public $clientcde;
     public $passefacture;
     public $utilisateur;
     public $lignes=array();
     public $lignesdiss=array();
     public $lignesnondiss=array();
     public $userid;
     public $raisonsociale;
     public $nom;
     public $prenom;
     public $adresse;
     public $ville;
     public $codepostal;
     public $pays;
     public $paysid;
     public $iso3166;
     public $departement;
     public $region;
     public $diminutif;
     public $telephone;
     public $mail;
     public $infotranspo;
     public $incoterms;
     public $credit;
     public $teg_taux;
     public $no_dossier;
     public $mensualites;
     public $note;
     public $douane;
     public $commission;
     
     public $datecommande;
     public $dateexpedition;
     public $datelivraison;
     public $exp_accord_client;
     public $date_exp_accord_client;
     public $valide;
     public $validetxt;
     public $moyenid;
     public $provenance;
     public $provenanceid;
     public $mp_facturid;
     public $mp_name;
     public $commercial;
     public $commercial_attribution;
     public $cadeauid;
     public $codepromo;
     public $site_code;
     public $avisclient;
     public $txtlivr;

     public $porttva;
     public $portht;
     public $portttc;
     public $modelivraison;
     public $totallignesht;
     public $totallignesttc;
     public $totaltauxht;
     public $cadeauht   = 0;
     public $cadeauttc  = 0;
     public $partcadeau = array();
     public $cadeautype;
     public $totalht  = 0;
     public $totalttc = 0;
     public $installation_ttc   = 0;
     public $installation_ht    = 0;
     public $installation_desc  = 0;
     public $gclid;

     private $validetxts = array(
                              0 => "Non traitée",
                              1 => "Validée",
                              2 => "Refus commerçant",
                              3 => "Refusée",
                              4 => "Abandon internaute",
                              5 => "Expédiée",
                              6 => "Remboursée",
                              7 => "Validée en attente de paiement",
                              8 => "Expédiée en attente de paiement"
                         );
     
     private $marketplaces = array(
                              "01" => "Amazon",
                              "04" => "Price Minister",
                              "05" => "Pixmania",
                              "06" => "Windill",
                              "07" => "Rue du Commerce",
                              "08" => "Edengo",
                              "10" => "Ebay",
                              "11" => "2x moins cher",
                              "12" => "Fnac",
                              "13" => "CDiscount",
                              "19" => "Conforama",
                              "18" => "Delamaison",
                              "20" => "Idealo"
                         );

     function __construct($DB, $facturid = null){

        $this->DB = $DB;

        if($facturid == null) return;

        if(is_array($facturid)){
            $f = $facturid;
        } else {
            $sql = "SELECT f.*, p.libelle AS pays, p.diminutif, p.iso3166";
            $sql .= " FROM facture f";
            $sql .= " INNER JOIN pays p USING(paysid)";
            $sql .= " WHERE facturid = ".intval($facturid);
            $f = $this->DB->query_first($sql);

            if($f['facturid'] != $facturid) return false;
        }

        $this->facturid      = $f['facturid'];
        if($f['valide']>=5)
            $this->numerofacture = $f['numerofacture'];
        $this->clientcde     = $f['clientcde'];
        $this->passefacture  = $f['passefacture'];
        $this->userid        = $f['userid'];
        $this->civilite      = $f['civilite'];
        $this->raisonsociale = $f['raisonsociale'];
        $this->nom           = $f['nom'];
        $this->prenom        = $f['prenom'];
        $this->adresse       = $f['adresse'];
        $this->adresse2      = $f['adresse2'];
        $this->codepostal    = $f['codepostal'];
        $this->ville         = $f['ville'];
        $this->pays          = $f['pays'];
        $this->paysid        = $f['paysid'];
        $this->diminutif     = $f['diminutif'];
        $this->iso3166       = $f['iso3166'];
        $this->telephone     = $f['telephone'];
        $this->mail          = $f['mail'];
        $this->datecommande  = $f['heurefacture'];
        
        $d = explode("-",$f['dateexpedition']);
        
        if($d[2]=="0000"){
            $this->dateexpedition = 0;
        } else {
            $this->dateexpedition = mktime(0,0,0,$d[1],$d[2],$d[0]);
        }
        
        $this->valide        = $f['valide'];
        $this->validetxt     = $this->validetxts[$this->valide];
        $this->validecolor="#000000";
        switch($this->valide){
            case 1: // Validée
            case 7:
                $this->validecolor="#0000FF";
            break;
            case 2: // refus commercant
                $this->validecolor="#FF0000";
            break;
            case 3: // refus carte bleue
                $this->validecolor="#FF0000";
            break;
            case 4: // abandon internaute
                $this->validecolor="#FF0000";
            break;
            case 5: // expédié
            case 8:
                $this->validecolor="#00FF00";
            break;
        }
    
        $this->moyenid       = $f['moyenid'];
        $this->provenance    = $f['provenance'];
        $this->provenanceid  = $f['provenanceid'];
        $this->mp_facturid   = $f['mp_facturid'];
        $this->mp_name       = @$this->marketplaces[$f['provenanceid']];
        $this->commercial    = $f['commercial'];
        $this->commercial_attribution = $f['commercial_attribution'];
        $this->porttva       = $f['tvaport'];
        $this->portttc       = $f['prixport'];
        $this->portht        = $this->portttc/(1+$f['tvaport']/100);
        $this->modelivraison = $f['modelivraison'];
        $this->infotranspo   = $f['infotranspo'];
        $this->incoterms     = $f['incoterms'];
        $this->datelivraison = $f['datelivraison'];
        $this->exp_accord_client = $f['exp_accord_client'];
        $this->date_exp_accord_client = $f['date_exp_accord_client'];
        $this->site_code     = (empty($f['site_code'])?"base":$f['site_code']);
        $this->cadeauid      = $f['cadeauid'];
        $this->cadeauttc     = $f['montantcadeau'];
        $this->credit        = $f['montant_credit'];
        $this->teg_taux      = $f['TEG_annuel'];
        $this->no_dossier    = $f['num_dossier'];
        $this->mensualites   = $f['nb_mensualites'];
        $this->note          = $f['info_importante'];
        $this->douane        = $f['douane'];
        $this->commission    = $f['commission'];
        $this->avisclient    = $f['avis'];
        $this->txtlivr       = $f['txtlivr'];    
        $this->gclid         = $f['gclid'];    
    
        $this->utilisateur = new utilisateur($DB,$this->userid);

         //Récupération des informations concernant l'installation
     $tabInstallation = array();
     $sql = "SELECT *";
     $sql .= " FROM lignefactureinstallation";
     $sql .= " WHERE factureid = ".$this->facturid;
     $req = $this->DB->query($sql);
     
     $affiche_installation = false;
     $installation_ttc     = 0;
     $installation_ht      = 0;
     if($this->DB->num_rows($req) > 0){
          
          $affiche_installation = true;
          $chaine_intitule      = '';
          while($row = $this->DB->fetch_array($req)){
               
               $aTemp = explode('|', $row['lignefactureinstallation_typearticles']);
          
               $aTypeTemp = array();
               foreach($aTemp as $ligne){

                    if(is_numeric($ligne)) $aTypeTemp[] = $ligne;
               }

               foreach($aTypeTemp as $typearticleid){

                    $sql = "SELECT libelle";
                    $sql .= " FROM typearticle";
                    $sql .= " WHERE typearticleid = ".$typearticleid;
                    $libelle_type = $this->DB->query_first($sql);

                    $chaine_intitule .= $chaine_intitule == '' ? $libelle_type['libelle'] : ' + '.$libelle_type['libelle'];
               }
               
               $installation_ttc += $row['lignefactureinstallation_differenceprix'];
               $installation_ht  += $row['lignefactureinstallation_differenceprix'] / (1 + $row['lignefactureinstallation_tauxtva'] / 100);
          }
          
          $description_installation = 'Mise en service et installation de votre << '.$chaine_intitule.' >>';
     }

     if($affiche_installation){
          
          $this->installation_desc = $description_installation;
          $this->installation_ht   = $installation_ht;
          $this->installation_ttc  = $installation_ttc;
     }

        $this->lignes();
        $this->calccadeau($f);
        
        if(is_array($facturid) && isset($f['portHT'])){
            $this->totalht       = $this->totallignesht + $f['portHT'] - $f['remiseHT'];
       }
       else{
          $this->totalht       = $this->totallignesht + $this->portht - $this->cadeauht;
          }
        $this->totalttc      = $this->totallignesttc + $this->portttc - $this->cadeauttc;
        $this->portht        = round($this->portht,2);
        $this->totaltauxht[$this->porttva]+=$this->portht;
        $this->totallignesht = round($this->totallignesht,2);
    }  

     function lignes(){
          
          $this->totallignesttc = $this->totallignesht = 0;
          
          $sql = "SELECT lf.*, a.artcode_eurocave, (a.emballage_poids1+ a.emballage_poids2+ a.emballage_poids3) AS emballage_poids, a.montage, a.montage_heures, a.typearticleid,a.douanier,a.paysid AS origine, m.marqueid, m.libelle AS marque, ta.bdd_table";
          $sql .= " FROM lignefacture lf";
          $sql .= " LEFT JOIN article a USING(artid)";
          $sql .= " LEFT JOIN article_marque USING(artid)";
          $sql .= " LEFT JOIN marque m USING(marqueid)";
          $sql .= " INNER JOIN typearticle ta ON a.typearticleid=ta.typearticleid";
          $sql .= " WHERE factureid = ".intval($this->facturid);
          $sql .= " ORDER BY lf.lignefactureid";
          $r = $this->DB->query($sql);

          while($ligne = $this->DB->fetch_array($r)){
               
              $this->lignes[$ligne['lignefactureid']] = new lignefacture($this->DB,$ligne);

              //Ce code est pour la fonction RENVOI  EMAIL DE CONFIRMATION si la facture n'est pas expediee on affiche les lignes non dissociées
              if($ligne['lignefacturedissid'] != 0){
               $l = $this->DB->query_first("SELECT *,a.montage,a.montage_heures FROM lignefacturediss INNER JOIN article a USING(artid) WHERE lignefactureid = '".$ligne['lignefacturedissid']."'");
                  $this->lignesnondiss[$ligne['lignefacturedissid']] = new lignefacture($this->DB,$l);
              }else{  
                  $this->lignesnondiss[$ligne['lignefactureid']] = new lignefacture($this->DB,$ligne);
              }
              $this->totallignesttc += $ligne['qte']*$ligne['prix'];
              $ligneht=$ligne['qte']*($ligne['prix']/(1+$ligne['tva']/100));
              $this->totallignesht  += $ligneht;
              if(!isset($this->totaltauxht[$ligne['tva']])) $this->totaltauxht[$ligne['tva']]=0;
              $this->totaltauxht[$ligne['tva']]+=$ligneht;
          }

          $sql = "SELECT lignefacturediss.*,a.montage,a.montage_heures";
          $sql .= " FROM lignefacturediss";
          $sql .= " INNER JOIN article a USING(artid)";
          $sql .= " WHERE factureid = ".intval($this->facturid);
          $r = $this->DB->query($sql);
          
          while($ligne = $this->DB->fetch_array($r)){
               $this->lignesdiss[$ligne['lignefactureid']] = new lignefacture($this->DB,$ligne);
          }
     }

     function from_mp_facturid($mp_facturid){
            $data=$this->DB->query_first("SELECT * FROM facture WHERE mp_facturid='".mysqli_real_escape_string($this->DB->link_id,$mp_facturid)."'");
            if($data!=false) $this->__construct($this->DB,$data);
            return $data!=false;
     }
     
     function region(){
          
          if($this->paysid == 57 && strlen($this->codepostal) == 5){
               
               $dep = substr($this->codepostal,0,2);
               
               $sql = "SELECT *";
               $sql .= " FROM departement";
               $sql .= " INNER JOIN region USING(id_region)";
               $sql .= " WHERE code = ".intval($dep);
               $region = $this->DB->query_first($sql);
               
               $this->departement=$region['nom_departement'];
               $this->region=$region['nom_region'];
          }
     }

     function calccadeau($facture){
          
          $totalreduc = array();
          
          if ($this->cadeauid != ""){
               
               $rayons = array();
               
               $sql = "SELECT *";
               $sql .= " FROM cadeau";
               $sql .= " WHERE cadeauid = '".$this->cadeauid."'";
               $infoscadeau = $this->DB->query_first($sql);
               
               if($infoscadeau['code'] != ""){
                    
                    $this->codepromo  = $infoscadeau['code'];
                    $this->cadeautype = "general";
                    
                    $sql = "SELECT artcode, a.artid";
                    $sql .= " FROM article a, article_cadeau ac";
                    $sql .= " WHERE a.artid = ac.artid";
                    $sql .= " AND cadeauid = '".$this->cadeauid."'";
                    $article = $this->DB->query_first($sql);
                    
                    if (empty($article[0]) === false){
                         
                         $sql = "SELECT c.catid";
                         $sql .= " FROM categorie c, categorie_cadeau cc";
                         $sql .= " WHERE c.catid = cc.catid";
                         $sql .= " AND cadeauid = '".$this->cadeauid."'";
                         $rayon = $this->DB->query_first($sql);
                    } else {
                         $this->cadeautype = "article";
                    }

                    if(empty($rayon['catid']) === false){
                         
                         $this->cadeautype = "rayon";
                         
                         $rayons[] = $rayon['catid'];
                         
                         $sql = "SELECT catid";
                         $sql .= " FROM categorie";
                         $sql .= " WHERE parentid = ".$rayon['catid'];
                         $ssrayons = $this->DB->query($sql);
                         while($ssrayon = $this->DB->fetch_array($ssrayons)){
                              
                              $rayons[] = $ssrayon['catid'];
                              
                              $sql = "SELECT catid";
                              $sql .= " FROM categorie";
                              $sql .= " WHERE parentid = ".$ssrayon['catid'];
                              $ss2rayons = $this->DB->query($sql);
                              
                              while($ss2rayon = $this->DB->fetch_array($ss2rayons)) $rayons[] = $ss2rayon['catid'];
                         }
                    }   

                    foreach($this->lignes as $ligneid => $lignefacture){
                          
                         $sql = "SELECT SUM(differenceprix)";
                         $sql .= " FROM lignefacturecaracteristique";
                         $sql .= " WHERE lignefactureid = ".$ligneid;
                         $caract = $this->DB->query_first($sql);
                         
                         if(empty($lignefacture->artid) === false && $lignefacture->artid == $article[1] || in_array($lignefacture->catid,$rayons) || ($article[0] == "" && $rayons == array())){
                              $totalreduc[$lignefacture->tva]+=$lignefacture->qte*($lignefacture->prix-$caract[0]);    
                         }      
                    }

                    foreach($this->lignesdiss as $ligneid => $lignefacture){
                         
                         $sql = "SELECT SUM(differenceprix)";
                         $sql .= " FROM lignefacturecaracteristique";
                         $sql .= " WHERE lignefactureid = ".$ligneid;
                         $caract = $this->DB->query_first($sql);
                         
                         if($lignefacture->artid == $article[1]){
                              $totalreduc[$lignefacture->tva]+=$lignefacture->qte*($lignefacture->prix-$caract[0]);
                         }
                    }

                    $this->cadeauttc = $facture['montantcadeau'];
               }

               $sql = "SELECT *";
               $sql .= " FROM code_caviste";
               $sql .= " WHERE code = '".$this->cadeauid."'";
               $code_caviste = $this->DB->query_first($sql);
               
               if($code_caviste['code'] != ""){
                    
                    $this->codepromo  = $code_caviste['code'];
                    $this->cadeautype = "caviste".$code_caviste['type'];
                    
                    if($code_caviste['type'] == 2 || ($code_caviste['type'] == 1 && $facture['montantcadeau'] < $code_caviste['mini'])){
                         
                         $this->cadeauttc=$facture['montantcadeau'];
                         
                         foreach($this->lignes as $ligneid=>$lignefacture){
                             
                              $sql = "SELECT SUM(differenceprix)";
                              $sql .= " FROM lignefacturecaracteristique";
                              $sql .= " WHERE lignefactureid = ".$ligneid;
                              $caract = $this->DB->query_first($sql);
                             
                              $totalreduc[$lignefacture->tva] += $lignefacture->qte * ($lignefacture->prix - $caract[0]);
                         }
                    }
               }

               if(array_sum($totalreduc) != 0){
                    
                    foreach($totalreduc as $txtva => $totalttc){
                         
                        $this->partcadeau[$txtva] = round($this->cadeauttc * $totalttc / array_sum($totalreduc),2);
                        $this->cadeauht          += round($this->partcadeau[$txtva] / (1+$txtva/100),2);
                        $this->totaltauxht[$txtva]-=round($this->partcadeau[$txtva] / (1+$txtva/100),2);
                    }
               }
          }
     }

     function search($sql_req){
          
          $ret = array();
          
          $sql = "SELECT facturid";
          $sql .= " FROM facture WHERE ".$sql_req;
          $r = $this->DB->query($sql);
          
          while($f = $this->DB->fetch_array($r)){
               $ret[$f['facturid']] = new facture($this->DB,$f['facturid']);
          }

          return $ret;
     }

     function verif_avant_exp(){
         
          $return  = array();
          $eftypes = array(
                         "fourn" => "fournisseur produit",
                         "trspt" => "transport",
                         "sls"   => "installateur"
                    );
         
          foreach($this->lignes as $lfid => $ligne){
             
               $ef = array(
                         "fourn" => 0,
                         "trspt" => 0,
                         "sls"   => 0
                    );
               
               if($ligne->artid>0){
                    
                    $sql = "SELECT efid, qte, type";
                    $sql .= " FROM envoi_fournisseur_ligne";
                    $sql .= " WHERE ligne_facture_id = ".$lfid;
                    $efllist = $this->DB->query($sql);
                    
                    while($efl = $this->DB->fetch_array($efllist)){
                         $ef[$efl['type']] = $efl['qte'];
                    }
                    
                    foreach($ef as $type=>$qtecmd){
                         
                         if($qtecmd == 0){
                            $return[] = $eftypes[$type]." ".$ligne->artcode." non commandé";
                         } elseif($qtecmd < $ligne->qte){
                            $return[] = ($ligne->qte-$qtecmd)." non commandé pour ".$eftypes[$type];
                         }
                    }
                    
                    $checkGaranties = $ligne->checkGaranties();
                    
                    if($checkGaranties!=array()){
                         $return[] = "Erreur garantie ".$ligne->artcode." (".implode(",",$checkGaranties).")";
                    }
               }
          }

          return $return;
     }

     function has_options(){
          
          $aTypeArticle  = array();
          $aForfait      = array();
          $aInstallation = array();        
          
          $sql = "SELECT DISTINCT(a.typearticleid)";
          $sql .= " FROM lignefacture lf";
          $sql .= " INNER JOIN article a ON (a.artid = lf.artid)";
          $sql .= " WHERE lf.factureid = ".$this->facturid;
          $req = $this->DB->query($sql);
          while($row = $this->DB->fetch_array($req)){
               $aTypeArticle[] = $row['typearticleid'];
          }
          
          $sql = "SELECT installation_id";
          $sql .= " FROM installation i";
          $sql .= " WHERE i.installation_type_id = 1";
          foreach($aTypeArticle as $typearticleid){
               $sql .= " AND installation_typearticle LIKE '%|".$typearticleid."|%'";
          }
          $req = $this->DB->query($sql);
          while($row = $this->DB->fetch_array($req)){
               $aForfait[] = $row;
          }
     
          foreach($aTypeArticle as $typearticleid){
               $sql = "SELECT installation_id";
               $sql .= " FROM installation i";
               $sql .= " WHERE i.installation_type_id = 2";
               $sql .= " AND installation_typearticle = '|".$typearticleid."|'";
               $req = $this->DB->query($sql);
               while($row = $this->DB->fetch_array($req)){
                    $aInstallation[] = $row;
               }
          }
          
          if(!empty($aForfait) || !empty($aInstallation)) return true;
          
          return false;
     }
     
     public function addSuivi($vars){
          $sql = "INSERT INTO suivi_commande SET";
          $sql .= " type = ''";
          $sql .= ", destinataire = 0";
          $sql .= ", moyen = ''";
          $sql .= ", facturid = ".$this->facturid;
          $sql .= ", date = ".time();
          $sql .= ", commercial = '".$_COOKIE['admin_username']."'";
          $sql .= ", suivi = '".$vars['suivi']."'";
          $sql .= ", rappel = 0";
          $sql .= ", rappeltype = ''";
          $sql .= ", jour = 0";
          $sql .= ", pour = ''";
          $sql .= ", hide = 0";

          $this->DB->query($sql);
     }
     
     public function getDetailsReception($reception_id){
          $sql = "SELECT reglement_reception_libelle";
          $sql .= " FROM reglement_reception";
          $sql .= " WHERE reglement_reception_id = ".$reception_id;
          $tabRcpt = $this->DB->query_first($sql);
          
          return $tabRcpt['reglement_reception_libelle'];
     }
     
     public function getMoyen($moyen_id){
          $sql = "SELECT libelle".$this->utilisateur->langue." AS libelle";
          $sql .= " FROM moyenpaiement";
          $sql .= " WHERE moyenid = ".$moyen_id;
          $tabMoyen = $this->DB->query_first($sql);
          
          return $tabMoyen['libelle'];
     } 
     
     public function getModalite($reglement_delais_id){
          $sql = "SELECT reglement_delais_libelle";
          $sql .= " FROM reglement_delais";
          $sql .= " WHERE reglement_delais_id = ".$reglement_delais_id;
          $tabDelais = $this->DB->query_first($sql);
          
          return $tabDelais['reglement_delais_libelle'];
     }
     
     public function getReglements(){
	  $ret=array();
	  $sql="SELECT reglement_montant,reglement_commentaires,reglement_date,reglement_statut_id,reglement_moyen
	  FROM reglement 
	  WHERE facturid=".$this->facturid."
	  ORDER BY reglement_date";
	  
	  $req=$this->DB->query($sql);
	  while($r=$this->DB->fetch_array($req)){
	      $ret[]=$r;
	  }
	  return $ret;
     }
     
     function update($params = array()){
          
          $sql = "UPDATE facture SET ";
          
          $nb = 0;
          foreach($params as $champs => $valeur){
               
               switch($champs){
                    case 'ventes':
                         if($valeur){
                              foreach($this->lignes as $key => $article){
                                   $this->lignes[$key]->setVente($article->artid);
                              }
                         }
                         break;
                    default:
                         $sql .= ($nb > 0 ? ", " : "").$champs." = ".$valeur;
                         break;
               }
               
               $this->$champs = $valeur;
               $nb++;
          }
          
          $sql .= " WHERE facturid = ".$this->facturid;
          $this->DB->query($sql);
     }
     
     public function checkCompteAnalytique($site_id){
          
          global $template;
          
          /*$this->utilisateur->paysid = 1;
          $site_id = '13';
          $destinataire_email = 'webmaster1@mon-espace-cuisson.com';*/
          $destinataire_email = 'cecile@mon-espace-cuisson.com,pr@mon-espace-cuisson.com';
          
          $sql = "SELECT code";
          $sql .= " FROM compte_analytique";
          $sql .= " WHERE site LIKE '".$site_id."'";
          $sql .= " AND paysid = ".$this->utilisateur->paysid;
          $tabResult = $this->DB->query_first($sql);
          
          //Pas de compte analytique associé au pays de facturation => On prévient pas email
          if(empty($tabResult)){
               
               if($site_id != 'macaveavin'){
                    
                    $sql = "SELECT market_place_nom";
                    $sql .= " FROM market_place";
                    $sql .= " WHERE market_place_site_id LIKE '".$site_id."'";
                    $tabMP = $this->DB->query_first($sql);
                    $market_place_nom = $tabMP['market_place_nom'];
               } else {
                    $market_place_nom = "Ma Cave à Vin";
               }
               
               $message_mail = ""
               . "Bonjour,<br><br>"
               . "Attention, le code analytique du site <strong>\"".$market_place_nom."\"</strong> n'est pas renseigné pour le pays <strong>\"".$this->utilisateur->pays."\"</strong>.";
               
               $mail = new email();
               $mail->expediteur   = $template->translate("/site/base/mail_expediteur");
               $mail->expname      = $template->translate("/site/nom");
               $mail->destinataire = $destinataire_email;
               $mail->destname     = $template->translate("/site/nom");
               $mail->subject      = 'Ma Cave à Vin : code analytique manquant';
               $mail->body         = $message_mail;
               $mail->sendmail();
          }
     }
	 
	public function clear_panier($session_id){

		$sql = "DELETE FROM panier";
		$sql .= " WHERE sessionid = '".$session_id."'";
		$this->DB->query($sql);

	}

	public function getIdMD5($md5){

		$sql = "SELECT facturid, MD5(CONCAT(facturid,'-".date('Y-m-d')."')) as factureidMD5";
		$sql .= " FROM facture";
		$sql .= " HAVING factureidMD5 = '".$md5."'";
		$facturid = $this->DB->query_first($sql)['facturid'];

		return $facturid;

    }
        
    public function transportsClient(){
        $artlist=array();
        $sql="SELECT * FROM envoi_fournisseur WHERE destinataire NOT IN ('','0') AND type IN ('fourn','stock','trspt','sls') AND facturid=".$this->facturid;
        $req=$this->DB->query($sql);
        while($ef=$this->DB->fetch_array($req)){
            $sql="SELECT * FROM envoi_fournisseur_ligne WHERE efid=".$ef['id'];
            $req2=$this->DB->query($sql);
            while($efl=$this->DB->fetch_array($req2)){
                if(!isset($artlist[$efl['ligne_facture_id']])) $artlist[$efl['ligne_facture_id']]=array();
                $artlist[$efl['ligne_facture_id']][$ef['type']]=$ef;
            }
        }
        
        $trspt=array();
        foreach($artlist as $lfid=>$eftypes){
            foreach(array("fourn","stock","trspt","sls") as $type){;
                if(isset($eftypes[$type]) && $eftypes[$type]['livr']<=0)
                    $trspt[$lfid]=$type;
            }
        }
        
        $return=array();
        foreach($trspt as $lfid=>$type){
            $mailname=$artlist[$lfid][$type]['destinataire'];
            if($mailname=="FEDEX Express"){
                $adv=new adv($this->DB);
                $adv->load_from_lignefactureid($lfid,"trspt");
                $mailname.=" ".$adv->fields['trspttype'];
            }
            if($type=="fourn") $mailname="fourn";
            $return[$artlist[$lfid][$type]['id']]=$mailname;
        }
        
        return $return;
    }
    
    function suiviId(){
        return md5($this->facturid.$this->datecommande."mcav");
    }
    function updateGclid($gclid){
          $this->DB->query("UPDATE facture SET gclid ='".mysqli_real_escape_string($this->DB->link_id,$gclid)."' WHERE facturid ='".$this->facturid."'");
    }
    
    function envoi_devis(){
        $sql="SELECT * FROM envoi_devis WHERE facturid=".$this->facturid;
        $data=$this->DB->query_first($sql);
        if($data=="") return false;
        return array("date"=>$data['date'],"utilisateur"=>$data['utilisateur']);
    }
    function savetotal(){
      $this->DB->query("UPDATE facture SET totalttc = ".floatval($this->totalttc).", totalht=".floatval($this->totalht)." WHERE facturid=".$this->facturid);
    }
}

class lignefacture {

private $DB;

public $lignefactureid;
public $lignefacturedissid;
public $facturid;
public $artid;
public $artcode;
public $catid;
public $qte;
public $libelle;
public $marqueid;
public $marque;
public $prix;
public $prixht;
public $ecopart;
public $ecopartht;
public $tva;
public $paht;
public $montage_heures;
public $emballage_poids;
public $typearticleid;
public $ref;
public $gaz;
public $garantieid;
public $code_douanier;
public $origine; //paysid
public $articletable='article_cave';

public $totalht;
public $totalttc;

function __construct($DB,$data){
  $this->DB=$DB;
  if(is_array($data)){
	  $this->load($data);
  }else{

     $lf=$this->DB->query_first("SELECT * FROM lignefacture WHERE lignefactureid=".intval($data));
     //if(intval($lf[artid]) == 0) exit;
     $sql = "SELECT bdd_table FROM typearticle";
     $sql .= " INNER JOIN article a USING(typearticleid)";
     $sql .=" WHERE a.artid= '".$lf[artid]."'";
     $table_type = $this->DB->query_first($sql);
     if(!is_null($table_type[0])) $this->articletable = $table_type[0];
	$l=$this->DB->query_first("SELECT a.*, spec.* ,m.marqueid,m.libelle AS marque, a.paysid AS origine FROM article a INNER JOIN ".$this->articletable." AS spec USING(artid) LEFT JOIN article_marque am USING(artid) INNER JOIN marque m USING(marqueid) WHERE artid=".intval($lf[artid]));

     $l = @array_merge($lf,$l);

	if(is_null($l)){
          $l=$this->DB->query_first("SELECT lf.*,a.artcode_eurocave,a.montage,(a.emballage_poids1+a.emballage_poids2+a.emballage_poids3) AS emballage_poids,a.montage_heures,m.marqueid,m.libelle AS marque,a.typearticleid,a.douanier,a.paysid AS origine,a.prixht FROM lignefacturediss lf LEFT JOIN article a USING(artid) LEFT JOIN article_marque am USING(artid) INNER JOIN marque m USING(marqueid) INNER JOIN ".$this->articletable." AS spec ON a.artid=spec.artid WHERE lignefactureid=".intval($data));
    }
    $this->load($l);
  }
}

function load($l){
        $this->lignefactureid=$l['lignefactureid'];
        $this->lignefacturedissid=$l['lignefacturedissid'];
        $this->facturid=$l['factureid'];
        $this->artid=$l['artid'];
        $this->artcode=$l['artcode'];
        $this->ref=$l['artcode_eurocave'];
        $this->catid=$l['catid'];
        $this->qte=$l['qte'];
        $this->libelle=$l['libelle'];
        $this->marqueid=$l['marqueid'];
        if($this->artid>0) $this->marque=$l['marque'];
        $this->prix=$l['prix'];
        $this->tva=$l['tva'];
        $this->paht=$l['paht'];
        $this->ecopart=$l['ecopart'];
        $this->ecopartht=round($this->ecopart/(1+$this->tva/100),2);
        $this->montage_heures=($l['montage']==1?$l['montage_heures']:false);
        $this->prixht=round($this->prix/(1+$this->tva/100),2);
        $this->totalht=$this->prixht*$this->qte;
        $this->totalttc=$this->prix*$this->qte;
        $this->emballage_poids=$l['emballage_poids'];
        $this->typearticleid=$l['typearticleid'];
        //$this->gaz=$l['gaz'];
        $this->origine=$l['origine'];
        $this->code_douanier=$l['douanier'];
        $this->articletable=$l['bdd_table'];

        //Sélection de la meilleure garantie sélectionnée sur le produit pour retirer son montant du prix
        $sql = "SELECT differenceprix, garantieid";
        $sql .= " FROM lignefacturegarantie lfg";
        $sql .= " INNER JOIN garantie_type gt ON (gt.id_type = lfg.type)";
        $sql .= " WHERE lfg.lignefactureid = ".$this->lignefactureid;
        $sql .= " ORDER BY gt.libelle_ponderation DESC";
        $sql .= " LIMIT 0,1";

        //Sélection de la meilleure garantie sélectionnée sur le produit pour retirer son montant du prix
        $sql2 = "SELECT garantieid";
        $sql2 .= " FROM lignefacturegarantie lfg";
        $sql2 .= " WHERE lfg.lignefactureid = ".$this->lignefactureid;
        $sql2 .= " ORDER BY type DESC";
        $sql2 .= " LIMIT 0,1";
        //var_dump($sql2);
        
        if($this->lignefactureid != '') {
            $tabDiffPrix = $this->DB->query_first($sql);
            $this->prix_nogarantie = !empty($tabDiffPrix) ? $l['prix'] - $tabDiffPrix['differenceprix'] : $l['prix'];

            /// pour que sur les devis les garanties affichées utilisent le "prix de vente HT" au lieu du "prix" dans la table article (voir tâche 6344) :
            $this->prix_nogarantie_devis = !empty($tabDiffPrix) ? $l['prixht'] - $tabDiffPrix['differenceprix'] : @$l['prixht'];

            $garantieid = $this->DB->query_first($sql2);
            if (empty($garantieid['garantieid']) === false) {
                $this->garantieid = $garantieid['garantieid'];
            }

        }


}

function gaz($template,$path){
    return $template->translate($path);
}

function setVente($artid){
     
     $sql = "UPDATE article SET ";
     $sql .= " ventes = ventes + 1";
     $sql .= " WHERE artid = ".$artid;
     $this->DB->query($sql);
}

function caracteristiques($return_array=false){
    $ret=array();
    $rq_caracteristiques=$this->DB->query("SELECT lfc.*,cv.caractid FROM lignefacturecaracteristique lfc INNER JOIN caracteristiquevaleur cv USING(caractvalid) WHERE lignefactureid='".$this->lignefactureid."'") ;
    while ($rs_caracteristiques=$this->DB->fetch_array($rq_caracteristiques)){
		if($return_array)
			$ret[$rs_caracteristiques[caractid]]=array($rs_caracteristiques[libelle_caract],$rs_caracteristiques[libelle_caractval]); 
		else
			$ret[$rs_caracteristiques[caractid]]="$rs_caracteristiques[libelle_caract] : $rs_caracteristiques[libelle_caractval]";
    }
    return $ret;
}
function caracteristiquesval($return_array=false){
    $ret=array();
    $rq_caracteristiques=$this->DB->query("SELECT cv.caractid,cv.caractvalid FROM lignefacturecaracteristique lfc INNER JOIN caracteristiquevaleur cv USING(caractvalid) WHERE lignefactureid='".$this->lignefactureid."'") ;
    while ($rs_caracteristiques=$this->DB->fetch_array($rq_caracteristiques)){
      $ret[$rs_caracteristiques[caractid]]=$rs_caracteristiques['caractvalid'];
    }
    return $ret;
}

//Spécifique pour cde25.php/mailconfirmation.php (depuis l'ajout de plusieurs RAL)
function caracteristiques25($return_array=false){
    $ret=array();
	$rq_caracteristiques=$this->DB->query("SELECT lfc.*,cv.caractid FROM lignefacturecaracteristique lfc INNER JOIN caracteristiquevaleur cv USING(caractvalid) INNER JOIN caracteristique c ON c.caractid = cv.caractid WHERE lignefactureid='".$this->lignefactureid."' ORDER BY c.position") ;
    while ($rs_caracteristiques=$this->DB->fetch_array($rq_caracteristiques)){
		if($return_array)
			$ret[$rs_caracteristiques[lignefacturecaracteristiqueid]]=array($rs_caracteristiques[libelle_caract],$rs_caracteristiques[libelle_caractval]); 
		else
			$ret[$rs_caracteristiques[lignefacturecaracteristiqueid]]="$rs_caracteristiques[libelle_caract] : $rs_caracteristiques[libelle_caractval]";
    }
    return $ret;
}

function getlignefacturediss(){
    $return=array();
    $req=$this->DB->query("SELECT lf.*,a.artcode_eurocave,m.libelle AS marque FROM lignefacture lf LEFT JOIN article a USING(artid) LEFT JOIN article_marque am USING(artid) INNER JOIN marque m USING(marqueid) WHERE lignefacturedissid=".intval($this->lignefactureid));
    while($data=$this->DB->fetch_array($req))
      $return[$data['lignefactureid']]=new lignefacture($this->DB,$data);

    return $return;
}

function garanties($best=false){
    $garanties=DAOgarantie::getGarantiesByProduitCmd($this->DB,$this->lignefactureid);
    if(!$best){
      return $garanties;
    }else{
      $maxtype=0;
      if(count($garanties)>0){
	  foreach($garanties as $garantie){
	      if($garantie['type_garantie']>$maxtype){ 
               $bestgarantie=$garantie;
               $maxtype=$garantie['type_garantie'];
	      } else if($garantie['type_garantie'] == $maxtype && $garantie['cumul'] == 1){
               $bestgarantie['dureeAffiche'] += $garantie['duree'];
           }
	  }
      }
      
      return $bestgarantie;
    }
}

function checkGaranties(){
    $return=array();
    
    $lfgcmd=$this->DB->query_first("SELECT * FROM lignefacturegarantiecmd WHERE lignefactureid=".$this->lignefactureid);
    if($lfgcmd['lignefactureid']!=""){
	$garanties=DAOgarantie::getGarantiesByProduitCmd($this->DB,$this->lignefactureid);
	$garantie_comp=$garantie_fourn=array();
	foreach($garanties as $garantie){
	    if($garantie['utilisation_garantie']==0)
		$garantie_fourn[]=$garantie;
	    else
		$garantie_comp[]=$garantie;
	}
	$maxtype=0;
	$maxduree=0;
	foreach($garantie_fourn as $g){
	    if($g['type_garantie']>$maxtype) $maxtype=intval($g['type_garantie']);
	    if($g['duree']>$maxduree) $maxduree=intval($g['duree']);
	}
	
	foreach($garantie_comp as $g){
	    if($g['cumul']==2){
		$maxtype=intval($g['type_garantie']);
		$maxduree=intval($g['duree']);
	    }else{
		$maxduree+=intval($g['duree']);
	    }
	}
	
	if($maxtype!=$lfgcmd['type']) $return[]="type";
	if($maxduree<$lfgcmd['duree']) $return[]="durée";
	
	if($garantie_comp!=array()){
	    $adv=new adv($this->DB);
	    $cmdOK=$adv->load_from_lignefactureid($this->lignefactureid,"garantie");
	    if(!$cmdOK) $return[]="commande";
	}
	
	
	
    }

    return $return;
}

function libelle_typearticle(){
  $result = $this->DB->query_first("SELECT libelle FROM typearticle WHERE typearticleid ='".$this->typearticleid."'");
  return $result['libelle'];
}

}



class utilisateur {

     private $DB;

     public $userid;
     public $nom;
     public $prenom;
     public $mail;
     public $civilite;
     public $codepostal;
     public $adresse;
     public $ville;
     public $telephone;
     public $portable;
     public $raisonsociale;
     public $dateinscription;
     public $paysid;
     public $pays;
     public $region;
     public $departement;
     public $diminutif;
     public $iso3166;
     public $tvaintracom;
     public $apply_intracom;
     public $profession;
     public $vip;
     public $langue;
     public $site_code;
     public $code411;
     public $redirection_site;
     public $site;
     public $provenance;
     public $connu;
     public $ancien;

    function __construct($DB,$userid=null){
        $this->DB=$DB;
         
        if(!is_null($userid)){
            $u=$DB->query_first("SELECT u.*,p.libelle,p.diminutif,p.iso3166,p.tvaintracom AS paysintracom FROM utilisateur u INNER JOIN pays p USING (paysid) WHERE userid=".intval($userid));

            $this->userid=$u['userid'];
            $this->nom=$u['nom'];
            $this->prenom=$u['prenom'];
            $this->mail=$u['mail'];
            $this->civilite=$u['civilite'];
            $this->codepostal=$u['codepostal'];
            $this->adresse=$u['adresse'];
            $this->ville=$u['ville'];
            $this->telephone=$u['telephone'];
            $this->portable=$u['portable'];
            $this->raisonsociale=$u['raisonsociale'];
            if($u['dateinscription']!=""){
            $d=explode("-",$u['dateinscription']);
            //$this->dateinscription=mktime(0,0,0,$d[1],$d[2],$d[0]);
            $this->dateinscription=mktime(0,0,0);
            }
            $this->paysid=$u['paysid'];
            $this->pays=$u['libelle'];
            $this->diminutif=$u['diminutif'];
            $this->iso3166=$u['iso3166'];
            $this->tvaintracom=$u['tvaintracom'];
            $this->apply_intracom=($this->raisonsociale!="" && $this->tvaintracom!="" && $u['paysintracom']==1);
            $this->profession=$u['profession'];
            $this->langue=$u['langue'];
            $this->site_code=$u['site_code'];
            $this->code411=$u['code411'];
            $this->vip=$u['vip'];
            $this->redirection_site=$u['redirection_site'];
            $this->site=$u['site'];
            $this->provenance=$u['provenance'];
            $this->connu=$u['connu'];
        }
    }

     function region(){
         if($this->paysid==57 && strlen($this->codepostal)==5){
     	$dep=substr($this->codepostal,0,2);
     	$region=$this->DB->query_first("SELECT * FROM departement INNER JOIN region USING(id_region) WHERE code='".$dep."'");
     	$this->departement=$region['nom_departement'];
     	$this->region=$region['nom_region'];
         }
     }

     function provenance() {
          if(isset($this->raisonsociale) && ($this->raisonsociale!="")) $pro = true;
          else $pro = false;

          $host = 'www.mon-espace-cuisson.com';
          /*if($this->langue == 'FR' && $pro) $host = 'pro.ma-cave-a-vin.fr';
          else if($this->langue == 'EN' && !$pro) $host = 'www.my-wine-cabinet.com';
          else if($this->langue == 'EN' && $pro) $host = 'pro.my-wine-cabinet.com';*/

          return $host;
     }

     function redirection() {
          if(isset($this->redirection_site) && ($this->redirection_site!="")) $host = $this->redirection_site;
          else $host = $this->provenance();

          return $host;
     }
     function contacts(){
        $contacts=array();
        $req=$this->DB->query("SELECT * FROM utilisateur_assoc WHERE userid=".$this->userid." ORDER BY actif DESC");
        while($contact=$this->DB->fetch_array($req)){
            $u=new utilisateur($this->DB);
            $u->userid=$contact['utilisateur_assoc_id'];
            $u->raisonsociale=$this->raisonsociale;
            $u->nom=$contact['utilisateur_assoc_nom'];
            $u->prenom=$contact['utilisateur_assoc_prenom'];
            $u->mail=$contact['utilisateur_assoc_email'];
            $u->telephone=$contact['utilisateur_assoc_tel'];
            $u->portable=$contact['utilisateur_assoc_portable'];
            $u->fonction=$contact['utilisateur_assoc_fonction'];
            $u->langue=$this->langue;
            $u->ancien=$contact['actif'];
            $contacts[]=$u;
        }
        
        return $contacts;
    }
    
    function factures($valide=null){
        $sql="SELECT * FROM facture WHERE userid=".$this->userid." AND supprimee='0'";
        if(!is_null($valide)) $sql.=" AND valide IN (".implode(",",$valide).")";
        $sql.=" ORDER BY heurefacture";
        $req=$this->DB->query($sql);
        $return=array();
        while($f=$this->DB->fetch_array($req)){
            $return[]=new facture($this->DB,$f);
        }
        return $return;
    }
    
    function devis(){
        $sql="SELECT * FROM facture INNER JOIN envoi_devis USING(facturid) WHERE userid=".$this->userid." AND supprimee='0' AND valide=0 ORDER BY heurefacture";
        $req=$this->DB->query($sql);
        $return=array();
        while($f=$this->DB->fetch_array($req)){
            $return[]=new facture($this->DB,$f);
        }
        return $return;
    }
    
    function projets($etat=null){
        $sql="SELECT * FROM projet WHERE (userid=".$this->userid." OR projetid IN (SELECT projetid FROM projet_relation WHERE userid=".$this->userid."))";
        if(!is_null($etat)){
            if(is_array($etat)){
                $etat=implode(",",$etat);
            }
            $sql.=" AND etatid IN (".$etat.")";
        }
        $sql.=" ORDER BY projetid";
        $req=$this->DB->query($sql);
        
        $return=array();
        while($f=$this->DB->fetch_array($req)){
            $return[]=new projet($f['projetid']);
        }
        return $return;
    }
}

?>
