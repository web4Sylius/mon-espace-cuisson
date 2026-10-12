<?php
class produit {

	private $DB        = null;
	private $langue    = null;
	private $site_code = null;

	//Article
	public $id                     = null;
	public $ref_aci                = null;
	public $ref_fab                = null;
	public $code_barre             = null;
	public $visuel                 = null;
	public $active                 = null;
	public $prix_ht                = null;
	public $prix_remise_ht         = null;
	public $prix_ttc               = null;
	public $prix_remise_ttc        = null;
	public $ppi_ht                 = null;
	public $ppi_remise_ht          = null;
	public $ppi_ttc                = null;
	public $ppi_remise_ttc         = null;
	public $prix_ttc_format        = null;
	public $prix_remise_ttc_format = null;
	public $prix_ht_format         = null;
	public $prix_remise_ht_format  = null;
	public $ppi_ht_format          = null;
	public $ppi_remise_ht_format   = null;
	public $ppi_ttc_format         = null;
	public $ppi_remise_ttc_format  = null;
	public $remise_format          = null;
	public $date_debut_remise      = null;
	public $date_fin_remise        = null;
	public $vente_flash            = false;
	public $garantie_offerte       = null;
	public $class_energie          = null;
	public $etiquette_energie      = null;
	public $type_porte_libelle     = null;
	public $type_porte_id          = null;
	public $capacite	           = null;

	//Type
	public $type_id      = null;
	public $type_libelle = null;

	//Marque
	public $marque_id      = null;
	public $marque_libelle = null;
	public $marque_visuel  = null;

	//Catégorie
	public $categorie      = array();

	public function __construct(){
          $argv = func_get_args();
          switch( func_num_args() ) {
               case 2:
                    self::__construct1($argv[0],$argv[1]);
                    break;
               case 5:
                    self::__construct2($argv[0],$argv[1],$argv[2],$argv[3],$argv[4]);
                    break;
          }
     }

	/**
	 * Constructeur de la classe
	 *
	 * @param object $DB     	Objet pour accès à la BDD
	 * @param int 	 $artid  	Identifiant de l'article
	 *
	 * Utile pour l'utilisation de fonctions simples ne nécessitant pas la construction complète de l'objet
	 */
	public function __construct1($DB, $artid){
		$this->DB        = $DB;
		$this->id        = intval($artid);
	}

	/**
	 * Second Constructeur de la classe
	 *
	 * @param object $DB     	Objet pour accès à la BDD
	 * @param string $langue 	Langue des données à récupérer
	 * @param string $site_code Code du site (base, UK...)
	 * @param int 	 $artid  	Identifiant de l'article
	 */
	public function __construct2($DB, $langue = 'FR', $site_code = 'base', $taux, $artid){
		$this->DB        = $DB;
		$this->langue    = $langue;
		$this->site_code = $site_code;
		$this->id        = intval($artid);
		$this->taux      = $taux;

		if(is_numeric($this->id)) $this->load();
	}

	/**
	 * Renseignement des données article
	 */
	private function load(){
		$this->loadArticle();
		$this->loadType();
		$this->loadMarque();
		$this->loadCategories();
		$this->loadCodeBarre();
		$this->loadVisuels();
		$this->loadGaranties();
	}

	/**
	 * Transforme les données issues d'une requête en un tableau
	 *
	 * @param  resource $req Données de la requête de sélection
	 *
	 * @return array Tableau de données
	 */
	private function formatReq($req){
		while($row = $this->DB->fetch_array($req)){
			$tabReturn[] = $row;
		}

		return $tabReturn;
	}

	/**
	 * Récupère les données de la table article
	 */
	private function loadArticle(){
		$sql = "SELECT a.*, asi.active as article_etat, rt.recherchetypeporteid as type_porte_id, rt.nom as type_porte
				FROM article a
				INNER JOIN article_site asi ON (asi.artid = a.artid)
				LEFT JOIN articlerecherche_typeporte art ON (art.artid = a.artid)
				LEFT JOIN recherche_typeporte rt ON (rt.recherchetypeporteid = art.recherchetypeporteid)
				WHERE 1 
				AND asi.site_code LIKE '".$this->site_code."'
				AND a.artid = ".$this->id;
		$tab = $this->DB->query_first($sql);

		$this->ref_aci            = $tab['artcode'];
		$this->ref_fab            = $tab['artcode_eurocave'];
		$this->active             = $tab['article_etat'];
		$this->type_id            = $tab['typearticleid'];
		$this->libelle            = $tab['libelle'.$this->langue];
		$this->description        = $tab['description'.$this->langue];
		$this->visuel_ext         = $tab['image'];
		$this->tva                = 1 + $this->taux["taux".$tab['tauxchoisi']] / 100;
		$this->class_energie      = $tab['energy_label'];
		$this->etiquette_energie  = "http://www.mon-espace-cuisson.com/fiche_energetique.php?artid=".$this->id;
		$this->type_porte_libelle = $tab['type_porte'];
		$this->type_porte_id      = $tab['type_porte_id'];
		$this->capacite           = $tab['capacite_bouteilles'];
		$this->capacite_l         = $tab['capacite_l'];
		$this->matiere            = $tab['matiere'];
		$this->poids              = $tab['poid'];
		$this->bruit              = $tab['niveau_bruit'];
		$this->puissance          = $tab['puissance_electrique'];
		$this->hauteur            = $tab['hauteur'];
		$this->largeur            = $tab['largeur'];
		$this->profondeur         = $tab['profondeur'];

		$prix = array(
			'prix_ttc' => $tab['prix'],
			'ppi_ht'  => $tab['prixconseilleht']
		);
		$this->setPrix($prix);

		$this->loadPrixTemporaire();
		$this->loadPrixVenteFlash();
	}

	/**
	 * Récupère les données de la table typearticle
	 */
	private function loadType(){
		$sql = "SELECT *
				FROM typearticle
				WHERE typearticleid = ".$this->type_id;
		$tab = $this->DB->query_first($sql);

		$this->type_libelle = $tab['libelle'];
	}

	/**
	 * Récupère les données de la table marque
	 */
	private function loadMarque(){
		$sql = "SELECT *
				FROM article_marque am
				INNER JOIN marque m ON (m.marqueid = am.marqueid)
				WHERE am.artid = ".$this->id;
		$tab = $this->DB->query_first($sql);

		$this->marque_id      = $tab['marqueid'];
		$this->marque_libelle = $tab['libelle'];
		if($tab['image'] != ''){
			$this->marque_visuel['lien'] = "/configurations/www.mon-espace-cuisson.com/images/marques/br/".$tab['marqueid'].".".$tab['image'];
			$this->marque_visuel['alt']  = $tab['type'.$this->langue];
		}
	}

	/**
	 * Récupère la liste des catégories du produit
	 */
	private function loadCategories(){
		$sql = "SELECT IFNULL(c3.catid,0) as cat2, IFNULL(c2.catid,0) as cat1, c.catid as cat0";
	    $sql .= " FROM article a";
	    $sql .= " INNER JOIN categorie c ON (c.catid = a.catid)";
	    $sql .= " LEFT JOIN categorie c2 ON (c2.catid = c.parentid)";
	    $sql .= " LEFT JOIN categorie c3 ON (c3.catid = c2.parentid)";
	    $sql .= " WHERE a.artid = ".$this->id;
	    $tab = $this->DB->query_first($sql);

	    for($i=0;$i<=2;$i++){

	    	$aCategorie = array();
	    	
	    	if($tab['cat'.$i] > 0){
	    		
	    		$aCategorie = $this->loadCategorie($tab['cat'.$i]);

				$this->categorie[$i]['categorie_id']      = $aCategorie['catid'];
				$this->categorie[$i]['categorie_libelle'] = $aCategorie['libelle'];
	    	}
	    }
	}

	/**
	 * Récupère les données de la table catégorie
	 */
	private function loadCategorie($categorie_id){
		$sql = "SELECT catid, libelle".$this->langue." as libelle
				FROM categorie
				WHERE catid = ".$categorie_id;
		$tab = $this->DB->query_first($sql);

		return $tab;
	}

	/**
	 * Retourne les informations concernant le code barre (EAN) de l'article
	 *
	 * @return array Données sur le code barre
	 */
	private function loadCodeBarre(){
		$sql = "SELECT *
				FROM code_barre
				WHERE artid = ".$this->id;
		$tab = $this->DB->query_first($sql);

		$this->code_barre = $tab['codebarre'];
	}

	/**
	 * Retourne les visuels de l'article
	 */
	private function loadVisuels(){
		$sql = "SELECT * 
				FROM articlephoto 
				WHERE 1
				AND visible = 1
				AND ambiance = 0
				AND (langue = '' OR langue = '".$this->langue."')
				AND artid = ".$this->id."
				ORDER BY position ASC";
		$req = $this->DB->query($sql);

		if($this->DB->num_rows($req) > 0){
			$i = 0;
			while($row = $this->DB->fetch_array($req)){
				if($row['image'] != ''){
					$this->visuel[$i]['lien'] = "/configurations/www.mon-espace-cuisson.com/images/produits/article/".$this->id."_".$row['articlephotoid'].".".$row['image'];
					$this->visuel[$i]['alt']  = $row['legende'];
				
					$i++;
				}
			}
		} else {
			if($this->visuel_ext != ''){
				$this->visuel[0]['lien'] = "/configurations/www.mon-espace-cuisson.com/images/produits/article/".$this->id.".".$this->visuel_ext;
				$this->visuel[0]['alt']  = '';
			}
		}
	}

	/**
	 * Met à jour les tarifs avec prix temporaire, s'il y en a un
	 */
	private function loadPrixTemporaire(){
		$sql = "SELECT *, DATE_FORMAT(du,'%Y-%m-%d') as date_debut_en, DATE_FORMAT(du,'%Y-%m-%d') as date_fin_en 
				FROM temp_price 
				WHERE 1
				AND artid     = $this->id 
				AND site_code LIKE '".$this->site_code."' 
				AND ".mktime(0,0,0)." BETWEEN du AND au";
		$tab = $this->DB->query_first($sql);
		if(!empty($tab)){
			$prix = array(
				'prix_remise_ttc' => $tab['prix'],
				'ppi_remise_ht'   => $tab['prixconseilleht'],
				'date_debut'	  => $tab['date_debut_en'],
				'date_fin'		  => $tab['date_fin_en']
			);
			$this->setPrixRemise($prix);
		}
	}

	/**
	 * Met à jour les tarifs avec vente flash, s'il y en a un
	 */
	private function loadPrixVenteFlash(){
		$sql = "SELECT *, DATE_FORMAT(du,'%Y-%m-%d') as date_debut_en, DATE_FORMAT(du,'%Y-%m-%d') as date_fin_en  
				FROM vente_flash_article vfa 
				INNER JOIN vente_flash vf ON (vf.id = vfa.vfid) 
				WHERE 1
				AND (".time()." BETWEEN vf.du AND vf.au) 
				AND site_code   LIKE '".$this->site_code."' 
				AND vfa.artcode LIKE '".$this->ref_aci."'";
		$tab = $this->DB->query_first($sql);
		if(!empty($tab)){
			$this->vente_flash = true;

			$prix = array(
				'prix_ttc'   => $tab['prix'],
				'ppi_ht'     => $this->ppi_ht,
				'date_debut' => $tab['date_debut_en'],
				'date_fin'   => $tab['date_fin_en']
			);
			$this->setPrixRemise($prix);
		}
	}

	private function loadGaranties(){
		$tabGarantie = DAOgarantie::loadMaxGarantieArtByPays($this->DB, 'base', 'FR', 57, 3018, 1, $this->prix_ttc);
		$this->garantie_offerte = $tabGarantie[0];
		
		//TODO : Compléter avec les garanties payantes
	}

	/**
	 * Met à jour le tarif de base du produit
	 *
	 * @param array $aPrix Données de mise à jour
	 */
	private function setPrix($aPrix){
		if(isset($aPrix['prix_ttc'])){
			$this->prix_ttc = floatval($aPrix['prix_ttc']);
			$this->prix_ht  = floatval($this->prix_ttc / $this->tva);
		} 

		if(isset($aPrix['ppi_ht']) && $aPrix['ppi_ht'] > 0){
			$this->ppi_ht  = floatval($aPrix['ppi_ht']);
			$this->ppi_ttc = floatval($this->ppi_ht * $this->tva);
			$this->remise  = floatval(100 - (($this->prix_ttc * 100) / $this->ppi_ttc));		
		} else {
			$this->ppi_ht  = $this->prix_ht;
			$this->ppi_ttc = $this->prix_ttc;
			$this->remise  = 0;
		}

		$this->prix_ttc_format = number_format($this->prix_ttc,2,',',' ');
		$this->prix_ht_format  = number_format($this->prix_ht,2,',',' ');
		$this->ppi_ht_format   = number_format($this->ppi_ht,2,',',' ');
		$this->ppi_ttc_format  = number_format($this->ppi_ttc,2,',',' ');
		$this->remise_format   = number_format($this->remise,2,',',' ');
	}

	/**
	 * Met à jour le tarif remisé du produit
	 *
	 * @param array $aPrix Données de mise à jour
	 */
	private function setPrixRemise($aPrix){
		if(isset($aPrix['prix_remise_ttc'])){
			$this->prix_remise_ttc   = floatval($aPrix['prix_remise_ttc']);
			$this->prix_remise_ht    = floatval($this->prix_remise_ttc / $this->tva);
			$this->remise            = floatval(100 - (($this->prix_remise_ttc * 100) / $this->ppi_ttc));
			$this->date_debut_remise = $aPrix['date_debut'];
			$this->date_fin_remise   = $aPrix['date_fin'];
		}

		if(isset($aPrix['ppi_remise_ht']) && $aPrix['ppi_remise_ht'] > 0){
			$this->ppi_remise_ht  = floatval($aPrix['ppi_remise_ht']);
			$this->ppi_remise_ttc = floatval($this->ppi_remise_ht * $this->tva);
			$this->remise         = floatval(100 - (($this->prix_remise_ttc * 100) / $this->ppi_remise_ttc));		
		} else {
			$this->ppi_remise_ht  = $this->prix_ht;
			$this->ppi_remise_ttc = $this->prix_ttc;
		}

		$this->prix_remise_ttc_format = number_format($this->prix_ttc,2,',',' ');
		$this->prix_remise_ht_format  = number_format($this->prix_ht,2,',',' ');
		$this->remise_format          = number_format($this->remise,2,',',' ');
	}

	public function getCaracteristiquesPrincipales($artid){
          $sql = "SELECT c.*";
          $sql .= " FROM article_caractval ac";
          $sql .= " INNER JOIN caracteristiquevaleur cv ON (cv.caractvalid = ac.caractvalid)";
          $sql .= " INNER JOIN caracteristique c ON (c.caractid = cv.caractid)";
          $sql .= " WHERE ac.artid = '".$artid."'";
          $sql .= " GROUP BY c.caractid ASC";
          //$sql .= " ORDER BY c.caracteristique_position ASC"; exit($sql);
          $r = $this->DB->query($sql);

          while($m = $this->DB->fetch_array($r)){
               $return[] = $m;
          }

          return $return;
     }

     public function getOptions($artid, $caractid = ''){
          $sql = "SELECT ac.*, cv.*, cv.description as desc_caract_val";
          $sql .= " FROM article_caractval ac";
          $sql .= " INNER JOIN caracteristiquevaleur cv ON (cv.caractvalid = ac.caractvalid)";
          $sql .= " INNER JOIN caracteristique c ON (c.caractid = cv.caractid)";
          $sql .= " WHERE ac.artid = '".$artid."'";
          if(!empty($caractid)) $sql .= " AND cv.caractid = '".$caractid."'";
          $sql .= " ORDER BY cv.position ASC";
          $r = $this->DB->query($sql);

          while($m = $this->DB->fetch_array($r)){
               $return[] = $m;
          }

          return $return;
     }

	public function hasOptions(){
		$sql = "SELECT c.caractid";
		$sql .= " FROM article_caractval ac";
		$sql .= " INNER JOIN caracteristiquevaleur cv ON (cv.caractvalid = ac.caractvalid)";
		$sql .= " INNER JOIN caracteristique c ON (c.caractid = cv.caractid)";
		$sql .= " WHERE ac.artid = '".$this->id."'";
		$sql .= " LIMIT 0,1";
		$r = $this->DB->query_first($sql); 
		if(is_array($r)) return true;

		return false;
	}
}