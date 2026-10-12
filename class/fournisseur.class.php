<?php
/**
 * Classe de gestion des fournisseurs
 */
class fournisseur
{
	private $DB;

	public $fournisseur_id;
	public $fournisseur_nom;
	public $fournisseur_types;
	public $fournisseur_devise;
	public $fournisseur_compte;
	public $fournisseur_langue;
	public $fournisseur_tarif;
	public $fournisseur_commande;
	public $fournisseur_livraison;
	public $fournisseur_sav;
	public $fournisseur_divers;
	public $fournisseur_export;
	public $fournisseur_codes;
	public $fournisseur_actif;
	public $fournisseur_produits;
	public $fournisseur_ar;
	public $fournisseur_tvaintra;
	public $fournisseur_admin;
	public $fournisseur_note;

	/**
	 * Constructeur de la classe
	 *
	 * @param object $DB  Connecteur de la BDD
	 * @param string $nom Nom du fournisseur (à gérer par ID dans le futur...)
	 */
	public function __construct($DB, $nom = '', $id = '')
	{
		$this->DB = $DB;

		$load = false;
		if($nom != '')
		{
			$load = true;
			$this->fournisseur_nom = $nom;
		} 
		if($id != '')
		{
			$load = true;
			$this->fournisseur_id = $id;
		} 

		if($load) $this->load();
	}

	/**
	 * Charge les informations sur le fournisseur
	 *
	 * @param  string $nom Nom du fournisseur
	 */
	private function load()
	{
		$sql = "SELECT *";
		$sql .= " FROM fournisseur_condition";
		$sql .= " WHERE 1";
		if($this->fournisseur_id != '') $sql .= " AND id = '" . mysqli_real_escape_string($this->DB->link_id,$this->fournisseur_id) . "'";
		if($this->fournisseur_nom != '') $sql .= " AND societe = '" . mysqli_real_escape_string($this->DB->link_id,$this->fournisseur_nom) . "'";
		$aFournisseur = $this->DB->query_first($sql);

		$this->fournisseur_id        = $aFournisseur['id'];
		$this->fournisseur_nom       = $aFournisseur['societe'];
		$this->fournisseur_types     = explode(',',$aFournisseur['types']);
		$this->fournisseur_devise    = $aFournisseur['devise'];
		$this->fournisseur_compte    = $aFournisseur['compte_client'];
		$this->fournisseur_langue    = $aFournisseur['langue'];
		$this->fournisseur_tarif     = $aFournisseur['tarif'];
		$this->fournisseur_commande  = $aFournisseur['commande'];
		$this->fournisseur_livraison = $aFournisseur['livraison'];
		$this->fournisseur_sav       = $aFournisseur['sav'];
		$this->fournisseur_divers    = $aFournisseur['divers'];
		$this->fournisseur_export    = $aFournisseur['export'];
		$this->fournisseur_codes     = $aFournisseur['codes'];
		$this->fournisseur_actif     = $aFournisseur['activite'];
		$this->fournisseur_produits  = $aFournisseur['pdts_vendus'];
		$this->fournisseur_ar        = $aFournisseur['ar'];
		$this->fournisseur_tvaintra  = $aFournisseur['tvaintra'];
		$this->fournisseur_admin     = $aFournisseur['fourn_admin'];
		$this->fournisseur_note      = $aFournisseur['fourn_note'];
	}

	/**
	 * Retourne la liste des types de livraison pour un fournisseur donné
	 *
	 * @return array Liste de types de livraison
	 */
	public function getListeTypeLivraison($params = array())
	{
		$sql = "SELECT lt.livr_type_id, lt.livr_type_nom";
		$sql .= " FROM livraison_type lt";
		if((isset($params['poids']) && intval($params['poids']) > 0) || (isset($params['recup_cave']) && $params['recup_cave'] == 'recup_cave'))
		{
			$sql .= " INNER JOIN zone_type zt ON (zt.livr_type_id = lt.livr_type_id)";
			$sql .= " INNER JOIN prix_installateur pi ON (pi.prix_id = zt.prix_id)";
			$sql .= " INNER JOIN zone z ON (z.zone_id = pi.zone_id)";
		}
		if(isset($params['departement']) && $params['departement'] > 0)
		{
			$sql .= " INNER JOIN zone_departement zd ON (zd.zone_id = z.zone_id)";
			$sql .= " INNER JOIN departement d ON (d.id_departement = zd.departement_id)";
		}	
		if(isset($params['pays']) && $params['pays'] > 0)
		{
			$sql .= " INNER JOIN zone_pays zp ON (zp.zone_id = z.zone_id)";
		}	
		$sql .= " WHERE lt.fc_id = " . $this->fournisseur_id;
		if((isset($params['poids']) && $params['poids'] > 0) || (isset($params['recup_cave']) && $params['recup_cave'] == 'recup_cave'))
		{
			$sql .= "  AND " . ceil($params['poids'])*1000 . " BETWEEN pi.prix_poidsmin AND pi.prix_poidsmax";
		}
		if(isset($params['departement']) && $params['departement'] > 0)
		{
			$sql .= " AND d.code = '" . $params['departement'] . "'";
		} 
		if(isset($params['pays']) && $params['pays'] > 0)
		{
			$sql .= " AND zp.paysid = " . $params['pays'];
		} 
		$req = $this->DB->query($sql);
		
		$aReturn = array();
		if($this->DB->num_rows($req) > 0)
		{
			while($ligne = $this->DB->fetch_array($req))
			{
				$aReturn[] = $ligne;
			}
		}

		return $aReturn;
	}

	public function getTarifTransport($params)
	{
		$sql = "SELECT pi.prix_paht";
		$sql .= " FROM prix_installateur pi";
		$sql .= " INNER JOIN zone z ON (z.zone_id = pi.zone_id)";
		if($params['departement'] != '')
		{
			$sql .= " INNER JOIN zone_departement zd ON (zd.zone_id = z.zone_id)";
			$sql .= " INNER JOIN departement d ON (d.id_departement = zd.departement_id)";
		} 
		$sql .= " INNER JOIN zone_pays zp ON (zp.zone_id = z.zone_id)";
		if($params['type'] != '')
		{
			$sql .= " INNER JOIN zone_type zt ON (zt.prix_id = pi.prix_id)";
			$sql .= " INNER JOIN livraison_type lt ON (lt.livr_type_id = zt.livr_type_id)";
		} 
		$sql .= " WHERE 1";
		$sql .= " AND z.fc_id = " . $this->fournisseur_id;
		$sql .= " AND " . $params['poids'] . "*1000 BETWEEN pi.prix_poidsmin AND pi.prix_poidsmax";
		if($params['departement'] != '')
		{
			$sql .= " AND d.code = '" . $params['departement'] . "'";
		} 
		$sql .= " AND zp.paysid = " . $params['pays'];
		if($params['type'] != '')
		{
			$sql .= " AND zt.livr_type_id = " . $params['type'];
		}

		$tarif = $this->DB->query_first($sql);

		return $tarif['prix_paht'];
	}

	public function getDetailsTypeLivraison($type_id)
	{
		$sql = "SELECT *";
		$sql .= " FROM livraison_type";
                if(is_int($type_id))
                    $sql .= " WHERE livr_type_id = " . $type_id;
                else
                    $sql .= " WHERE livr_type_nom='".mysqli_real_escape_string($this->DB->link_id,$type_id)."'";
		$aType = $this->DB->query_first($sql);

		return $aType;
	}

	public function getListeSocFacturation(){
          $sql = "SELECT *";
          $sql .= " FROM societe_facturation";
          $r = $this->DB->query($sql);

          while($m = $this->DB->fetch_array($r)){
               $return[] = $m;
          }

          return $return;
     }
     
     public function getSocieteFacturation($societe_id){
          $sql = "SELECT societe_facturation_nom";
          $sql .= " FROM societe_facturation";
          $sql .= " WHERE societe_facturation_id = ".$societe_id;
          $return = $this->DB->query_first($sql);
          return $return['societe_facturation_nom'];
     }
}
