<?php
class produit_four extends produit {

	//Article cave
	
	public $class_energie          = null;
	public $etiquette_energie      = null;
	public $type_porte_libelle     = null;
	public $type_porte_id          = null;
	public $capacite	           = null;
	public $usage	               = null;
	public $couleur	               = null;



	public function __construct($DB, $langue = 'FR', $site_code = 'base', $taux, $artid){
		parent::__construct($DB, $langue, $site_code, $taux, $artid);
		$this->loadArticle_cave();
	}

	/**
	 * Renseignement des données article
	 */
	/*private function load(){
		$this->loadArticle_cave();

		/*$this->loadCategories();
		$this->loadCodeBarre();
		$this->loadVisuels();
		$this->loadGaranties();
	}*/

	/**
	 * Récupère les données de la table article
	 */
	private function loadArticle_cave(){
		$sql = "SELECT ac.*, asi.active as article_etat, rt.recherchetypeporteid as type_porte_id, rt.nom as type_porte
				FROM article_cave ac
				INNER JOIN article_site asi ON (asi.artid = ac.artid)
				LEFT JOIN articlerecherche_typeporte art ON (art.artid = ac.artid)
				LEFT JOIN recherche_typeporte rt ON (rt.recherchetypeporteid = art.recherchetypeporteid)
				WHERE 1 
				AND asi.site_code LIKE '".$this->site_code."'
				AND ac.artid = ".$this->id;
		$tab = $this->DB->query_first($sql);

		$this->class_energie      = $tab['energy_label'];
		$this->etiquette_energie  = "https://www.mon-espace-cuisson.com/fiche_energetique.php?artid=".$this->id;
		$this->type_porte_libelle = $tab['type_porte'];
		$this->type_porte_id      = $tab['type_porte_id'];
		$this->usage      		  = $tab['usage'];
		$this->capacite           = $tab['capacite_bouteilles'];
		$this->capacite_l         = $tab['capacite_l'];
		$this->matiere            = $tab['matiere'];
		$this->bruit              = $tab['niveau_bruit'];
		$this->puissance          = $tab['puissance_electrique'];
		$this->couleur         	  = $tab['coloris'];	

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
					$this->visuel[$i]['lien'] = "/configurations/www.mon-espace-cuisson.com/images/produits/".$this->id."_".$row['articlephotoid'].".".$row['image'];
					$this->visuel[$i]['alt']  = $row['legende'];
				
					$i++;
				}
			}
		} else {
			if($this->visuel_ext != ''){
				$this->visuel[0]['lien'] = "/configurations/www.mon-espace-cuisson.com/images/produits/".$this->id.".".$this->visuel_ext;
				$this->visuel[0]['alt']  = '';
			}
		}
	}

}
