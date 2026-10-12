<?php

class garantie {
     
     private $DB;
     private $langue;
     private $site_code;

     public $garantieid;
     public $fournisseur;
     public $marques;
     public $types_produits;
     public $utilisation_garantie;
     public $pays;
     public $duree;
     public $type_garantie;
     public $prix_achat;
     public $prix_vente;
     public $prix_conseille;
     public $paht;
     public $prix;
     public $prixsites;
     public $ppi;
     public $ppisites;
     public $cumul;
     public $start;
     public $comm;
     public $destinataire_garantie;
     public $devise;
     public $qte_garantie;

     public function __construct(){
          $argv = func_get_args();
          switch( func_num_args() ) {
               case 4:
                    self::__construct1($argv[0],$argv[1],$argv[2],$argv[3]);
                    break;
               case 5:
               case 6:
                    self::__construct2($argv[0],$argv[1],$argv[2],$argv[3],$argv[4],isset($argv[5]) ? $argv[5] : 0);
                    break;
               case 18:
                    self::__construct3($argv[0],$argv[1],$argv[2],$argv[3],$argv[4],$argv[5],$argv[6],$argv[7],$argv[8],$argv[9],$argv[10],$argv[11],$argv[12],$argv[13],$argv[14],$argv[15],$argv[16],$argv[17]);
                    break;
          }
     }

     public function __construct1($DB,$site_code,$langue='FR',$id_garantie){
          
          $this->DB     = $DB;
          $this->langue = $langue;
          
          $sql = "SELECT *";
          $sql .= " FROM garantie";
          $sql .= " WHERE garantieid = ".$id_garantie;
          $r = $this->DB->query_first($sql);
          if($r['garantieid'] != $id_garantie) return false;
          
          $this->garantieid           = $r['garantieid'];
          $this->fournisseur          = $r['fournisseur'];
          $this->pays                 = explode(',', $r['pays']);
          $this->marques              = explode(',', $r['marques']);
		  //$this->marques              = $r['marques'];
          $this->types_produits       = explode(',', $r['types_produits']);
          $this->utilisation_garantie = $r['utilisation_garantie'];
          $this->duree                = $r['duree'];
          $this->type_garantie        = $r['type_garantie'];
          $this->paht                 = $r['paht'];
          $this->devise               = $r['devise'];
          $this->prixsites            = unserialize($r['prix']);
          $this->ppisites             = unserialize($r['ppi']);
          
          if(!is_null($site_code)){
               
               if($this->prixsites === false){ // compatibilitÃ© monosite
                    
                 $this->prix = floatval($r['prix']);
                 $this->ppi  = floatval($r['ppi']);
                 
               } else {
                    
                 $this->prix = isset($this->prixsites[$site_code]) ? $this->prixsites[$site_code] : 0;
                 $this->ppi  = isset($this->ppisites[$site_code]) ? $this->ppisites[$site_code] : 0;
               }
          }
          
          $this->cumul                 = $r['cumul'];
          $this->start                 = $r['start'];
          $this->comm                  = $r['comm'];
          $this->destinataire_garantie = $r['garantie_client_id'];
     }

     public function __construct2($DB,$site_code,$langue='FR',$id_garantie,$idArt, $pv_article){
          
          $this->DB     = $DB;
          $this->langue = $langue;
          
          $sql = "SELECT *";
          $sql .= " FROM garantie";
          $sql .= " WHERE garantieid = ".intval($id_garantie);
          $r = $this->DB->query_first($sql);
          if($r['garantieid'] != $id_garantie) return false;
          
          $this->garantieid            = $r['garantieid'];
          $this->fournisseur           = $r['fournisseur'];
          $this->pays                  = explode(',', $r['pays']);
          $this->marques               = explode(',', $r['marques']);
          $this->types_produits        = explode(',', $r['types_produits']);
          $this->utilisation_garantie  = $r['utilisation_garantie'];
          $this->duree                 = $r['duree'];
          $this->type_garantie         = $r['type_garantie'];
          $this->qte_garantie          = $this->lotGaranties($idArt, $pv_article);
          $this->devise                = $r['devise'];
          $this->destinataire_garantie = $r['garantie_client_id'];
          
          $tabArtGarantie = DAOgarantie::checkMultiProduit($DB, $idArt);
          if(!empty($tabArtGarantie)  && $pv_article > 20000){
               $pv_article = $tabArtGarantie['prix'];
               $this->qte_garantie = $tabArtGarantie['nb'];
          }
          
          //Récupération de la liste des tranches de prix
          $sql = "SELECT agl.id, agl.artid, agl.garantieid, g.garantie_client_id, gt.garantie_tranche_paht as paht, gt.garantie_tranche_prix as prix, gt.garantie_tranche_ppi as ppi";
          $sql .= " FROM article_garantie_liste agl";
          $sql .= " INNER JOIN garantie_tranche gt ON (gt.garantieid = agl.garantieid)";
          $sql .= " INNER JOIN garantie g ON (g.garantieid = agl.garantieid)";
          $sql .= " WHERE agl.garantieid = ".$id_garantie;
          $sql .= " AND agl.artid = ".$idArt;
          $sql .= " AND '".$pv_article."' BETWEEN gt.garantie_tranche_prix_mini AND gt.garantie_tranche_prix_maxi";
          $prixByArt = $this->DB->query_first($sql);
          
          if (!empty($prixByArt)) {
               
               $this->prixsites = @unserialize($prixByArt['prix']);
               $this->ppisites  = @unserialize($prixByArt['ppi']);
               $this->paht      = $prixByArt['paht'];
               
               if(!is_null($site_code)){
                    
                    if($this->prixsites === false){
                        
                         $this->prix = floatval($prixByArt['prix']);
                         $this->ppi  = floatval($prixByArt['ppi']);
                         
                    }else{
                         
                        $this->prix = $this->prixsites[$site_code];
                        $this->ppi  = $this->ppisites[$site_code];
                    }
               }
               
               $this->multiplie($this->qte_garantie);
               
          } else {
               
               $this->prixsites = @unserialize($r['prix']);
               $this->ppisites  = @unserialize($r['ppi']);
               $this->paht      = $r['paht'];
               
               if(!is_null($site_code)){
                    
                    if($this->prixsites === false){
                         
                         $this->prix = floatval($r['prix']);
                         $this->ppi  = floatval($r['ppi']);
                        
                    } else {
                         
                         $this->prix = $this->prixsites[$site_code];
                         $this->ppi  = $this->ppisites[$site_code];
                    }
               }
          }
          
          $this->cumul = $r['cumul'];
          $this->start = $r['start'];
          $this->comm  = $r['comm'];
     }

     public function __construct3($DB,$langue = 'FR', $garantieid = 0, $fournisseur, $marques, $types_produits, $utilisation_garantie, $pays, $duree, $type_garantie, $paht = '', $prix = '', $ppi = '', $cumul, $comm = '', $devise='', $garantie_client_id = 1, $start=0){
          
          $this->DB     = $DB;
          $this->langue = $langue;
          
          $this->garantieid           = $garantieid;
          $this->fournisseur          = $fournisseur;
          $this->marques              = $marques;
          $this->types_produits       = $types_produits;
          $this->utilisation_garantie = $utilisation_garantie;
          $this->pays                 = $pays;
          $this->duree                = $duree;
          $this->type_garantie        = $type_garantie;
          $this->paht                 = '';
          $this->prixsites            = '';
          $this->ppisites             = '';
          
          if(!empty($paht)){
               if(isset($paht[0])){
                    $this->paht = number_format($paht[0],2);
               } else {
                    $this->paht = $paht;
               }
          }
          if(!empty($prix)){
               foreach($prix as $tranche_id => $values){
                    if($tranche_id != 0){
                         foreach($values as $site_code => $val){
                              $prix[$tranche_id][$site_code] = @number_format($val,2);
                         }
                    } else {
                         $prix[$site_code] = number_format($val,2);
                    }
               }
               $this->prixsites = $prix;
          }
          if(!empty($ppi)){
               foreach($ppi as $tranche_id => $values){
                    if($tranche_id != 0){
                         foreach($values as $site_code => $val){
                              $ppi[$tranche_id][$site_code] = @number_format($val,2);
                         }
                    } else {
                         $ppi[$site_code] = number_format($val,2);
                    }
               }
               $this->ppisites  = $ppi;
          }
          
          $this->cumul                 = $cumul;
          $this->start                 = $start;
          $this->comm                  = $comm;
          $this->devise                = $devise;
          $this->destinataire_garantie = $garantie_client_id;
          $this->tranches[]            = '';
          
          if($this->garantieid != 0){
               //Récupération de la liste des tranches de prix
               $sql = "SELECT garantie_tranche_prix_mini, garantie_tranche_prix_maxi, garantie_tranche_paht, garantie_tranche_prix, garantie_tranche_ppi";
               $sql .= " FROM garantie_tranche";
               $sql .= " WHERE garantieid = ".$this->garantieid;
               $req = $this->DB->query($sql);
               while($row = $this->DB->fetch_array($req)){
                    $this->tranches[] = $row;
               }
          }
    }

     public function save(){
          
          (is_array($this->marques)) ? $saveMarques=implode(",", $this->marques) : $saveMarques=$this->marques;
          (is_array($this->types_produits)) ? $saveTypes_produits=implode(",", $this->types_produits) : $saveTypes_produits=$this->types_produits;
          (is_array($this->pays)) ? $savePays=implode(",", $this->pays) : $savePays=$this->pays;

          if($this->garantieid == 0){
               
               $sql = "INSERT INTO garantie SET";
               $sql .= " fournisseur           = '".mysqli_real_escape_string($this->DB->link_id,$this->fournisseur)."'";
               $sql .= ", marques              = '".$saveMarques."'";
               $sql .= ", types_produits       = '".$saveTypes_produits."'";
               $sql .= ", utilisation_garantie = '".intval($this->utilisation_garantie)."'";
               $sql .= ", pays                 = '".$savePays."'";
               $sql .= ", duree                = '".intval($this->duree)."'";
               $sql .= ", type_garantie        = '".intval($this->type_garantie)."'";
               $sql .= ", paht                 = '".floatval($this->paht)."'";
               /*$sql .= ", prix                 = '".serialize($this->prixsites)."'";
               $sql .= ", ppi                  = '".serialize($this->ppisites)."'";*/
			   $sql .= ", prix                 = ''";
               $sql .= ", ppi                  = ''";
               $sql .= ", cumul                = '".intval($this->cumul)."'";
               $sql .= ", start                = ".intval($this->start);
               $sql .= ", comm                 = '".mysqli_real_escape_string($this->DB->link_id,$this->comm)."'";
               $sql .= ", devise               = '".$this->devise."'";
               $sql .= ", garantie_client_id   = ".$this->destinataire_garantie;
               $this->DB->query($sql);
               
          }
          
          $this->garantieid = $this->DB->insert_id();
     }

     public function update(){
         
          (is_array($this->marques)) ? $saveMarques=implode(",", $this->marques) : $saveMarques=$this->marques;
          (is_array($this->types_produits)) ? $saveTypes_produits=implode(",", $this->types_produits) : $saveTypes_produits=$this->types_produits;
          (is_array($this->pays)) ? $savePays=implode(",", $this->pays) : $savePays=$this->pays;

          if($this->garantieid > 0){
               
               $sql = "UPDATE garantie SET";
               $sql .= " fournisseur           = '".mysqli_real_escape_string($this->DB->link_id,$this->fournisseur)."'";
               $sql .= ", marques              = '".$saveMarques."'";
               $sql .= ", types_produits       = '". $saveTypes_produits."'";
               $sql .= ", utilisation_garantie = '".intval($this->utilisation_garantie)."'";
               $sql .= ", pays                 = '".$savePays."'";
               $sql .= ", duree                = '".intval($this->duree)."'";
               $sql .= ", type_garantie        = '".intval($this->type_garantie)."'";
               /*$sql .= ", paht                 = '".floatval($this->paht)."'";
               $sql .= ", prix                 = '".serialize($this->prixsites)."'";
               $sql .= ", ppi                  = '".serialize($this->ppisites)."'";*/
               $sql .= ", cumul                = '".$this->cumul."'";
               $sql .= ", start                = ".intval($this->start);
               $sql .= ", comm                 = '".mysqli_real_escape_string($this->DB->link_id,$this->comm)."'";
               $sql .= ", garantie_client_id   = ".$this->destinataire_garantie;
               $sql .= " WHERE garantieid=".$this->garantieid;
               $this->DB->query($sql);
               
               //Obsolète depuis la mise à jour des tranches de prix SFG
               /*$sql = "UPDATE article_garantie_liste SET";
               $sql .= " paht  = '".floatval($this->paht)."'";
               $sql .= ", prix = '".serialize($this->prixsites)."'";
               $sql .= ", ppi  = '".serialize($this->ppisites)."'";
               $sql .= " WHERE garantieid = ".$this->garantieid;
               $this->DB->query($sql);*/
               
			   if(!empty($this->paht)){
				   
				   foreach($this->paht as $tranche_id => $prix_achat){
                    
                    $sql = "UPDATE garantie_tranche SET";
                    $sql .= " garantie_tranche_paht  = '".floatval($prix_achat)."'";
                    $sql .= ", garantie_tranche_prix = '".serialize($this->prixsites[$tranche_id])."'";
                    $sql .= ", garantie_tranche_ppi  = '".serialize($this->ppisites[$tranche_id])."'";
                    $sql .= " WHERE garantie_tranche_id = ".$tranche_id;
                    $this->DB->query($sql);
					}
			   }
          }
     }

    public function delete(){
		if($this->garantieid>0) $this->DB->query("DELETE FROM garantie WHERE garantieid=".$this->garantieid);
    }

    public function getTypeGarantie(){
    	$r=$this->DB->query_first("SELECT libelle_type FROM garantie_type WHERE id_type=".$this->type_garantie);
	return $r[0];
    }

    public function getMarques(){
    	$strMarques="";
    	for ($i=0; $i < (count($this->marques) - 1); $i++) {
    		$comma=", ";
    		if ($i==count($this->marques)-1) {
    			$comma="";
    		}
    		$m=$this->DB->query_first("SELECT libelle FROM marque WHERE marqueid=".$this->marques[$i]);
    		$strMarques .=$m[0].$comma;
    	}
    	return $strMarques;
    }

    public function getTypes(){
    	$strTypes="";
    	for ($i=0; $i < count($this->types_produits); $i++) {
    		$comma=", ";
    		if ($i==count($this->types_produits)-1) {
    			$comma="";
    		}
    		$r=$this->DB->query_first("SELECT libelle FROM typearticle WHERE typearticleid=".$this->types_produits[$i]);
    		$strTypes .= $r[0].$comma;
    	}
    	return $strTypes;
    }

    public function getPays(){
    	$strPays="";
    	for ($i=0; $i < count($this->pays); $i++) {
    		$comma=", ";
    		if ($i==count($this->pays)-1) {
    			$comma="";
    		}
    		if ($this->pays[$i]=="RE" || $this->pays[$i]=="RM") {
    			($this->pays[$i]=="RE") ? $strPays .= "Reste de l'Europe".$comma : $strPays .= "Reste du Monde".$comma;
    		} else {
    			$p=$this->DB->query_first("SELECT libelle FROM pays WHERE paysid=".$this->pays[$i]);
    			$strPays .= $p[0].$comma;
    		}
    	}
    	return $strPays;
    }
    
    public function deviseFournisseur(){
	$conditions=$this->DB->query_first("SELECT devise FROM fournisseur_condition WHERE societe='".mysqli_real_escape_string($this->DB->link_id,$this->fournisseur)."'");
	return $conditions['devise'];
    }
    
    private function lotGaranties(&$artid, &$pv_article = 0){
	  
          $glist     = array();
	  $nbgarart = 0;
          
          $sql = "SELECT *";
          $sql .= " FROM article_lot";
          $sql .= " WHERE lotid = ".intval($artid);
	  $lot = $this->DB->query($sql);
          
	  if($this->DB->num_rows($lot) == 0) return false;
          
	  while($art = $this->DB->fetch_array($lot)){
               
               $garanties = DAOgarantie::checkGarantiesArt($this->DB,"FR",$art['artid']);
               
               if(is_array($garanties)){
                    
                    $nbgarart += $art['qte'];
                    
                    foreach($garanties as $g){
                        
                         $glist[$art['artid']][] = intval($g['garantieid']);
                    }
               }
	  }
	  
	  if(count($glist)==0) return false;

	  $error = false;
	  $testgar = $glist;
	  $comp = array_pop($testgar);
	  
          foreach($testgar as $g){
               
               if(array_diff($comp,$g)!=array()){
                   
                    $error=true;
                    break;
               }
               
               $comp = array_pop($testgar);
	  }
	  
	  if(!$error){
               
               $sql = "SELECT al.prix";
               $sql .= " FROM article_lot al";
               $sql .= " WHERE al.lotid = ".$artid;
               $sql .= " ORDER BY al.prix DESC";
               $sql .= " LIMIT 1";
               $art = $this->DB->query_first($sql);
               $pv_article = $art['prix'];
               
               $artid = array_pop(array_keys($glist));
               
               return $nbgarart;
               
	  }else{
               
               $sql = "SELECT a.artid, al.qte, al.prix";
               $sql .= " FROM article a";
               $sql .= " INNER JOIN article_lot al ON(al.artid = a.artid)";
               $sql .= " WHERE al.lotid = ".$artid;
               $sql .= " ORDER BY al.prix DESC";
               $sql .= " LIMIT 1";
               $art = $this->DB->query_first($sql);
               
               $artid      = $art['artid'];
               $pv_article = $art['prix'];
               
               return $art['qte'];
	  }
    }
    
     public function multiplie($qte){
          
          $qte = intval($qte);
          
          if($qte == 0) return false;
          
          $this->paht = $this->paht * $qte;
          $this->prix = $this->prix * $qte;
          $this->ppi  = $this->ppi * $qte;
          
          if(!is_null($this->prixsites) && is_array($this->prixsites)){
               
               foreach($this->prixsites as $site_code=>$prix){
                   
                    $this->prixsites[$site_code] = $prix*$qte;
                    $this->ppisites[$site_code]  = $this->ppisites[$site_code]*$qte;
               }
          }
          
          return true;
     }
    
     public function addTranche($formvars){

          $prix = serialize($formvars['prix']);
          $ppi  = serialize($formvars['ppi']);
          
          $sql = "INSERT INTO garantie_tranche (";
          $sql .= " garantie_tranche_prix_mini";
          $sql .= ", garantie_tranche_prix_maxi";
          $sql .= ", garantie_tranche_paht";
          $sql .= ", garantie_tranche_prix";
          $sql .= ", garantie_tranche_ppi";
          $sql .= ", garantieid";
          $sql .= ") VALUES (";
          $sql .= " '".$formvars['mini']."'";
          $sql .= ", '".$formvars['maxi']."'";
          $sql .= ", '".$formvars['paht']."'";
          $sql .= ", '".$prix."'";
          $sql .= ", '".$ppi."'";
          $sql .= ", ".$formvars['garantie_id'];
          $sql .= ")";
          $this->DB->query($sql);
     }
     
     public function deleteTranche($formvars){
          
          $sql = "DELETE FROM garantie_tranche";
          $sql .= " WHERE garantie_tranche_id = ".$formvars['tranche_id'];
          $this->DB->query($sql);
     }
     
     public function setMontants($vars){
          
          $this->paht = $vars['paht'];
          $this->prix = $vars['prix'];
          $this->ppi  = $vars['ppi'];
     }
}
