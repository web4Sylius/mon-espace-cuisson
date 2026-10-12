<?php
/**
 * Cette classe permet de récupérer des données concernant les installateurs
 */
class installateur
{
	private $DB;

	/**
	 * Constructeur de la classe
	 *
	 * @param object $DB  Connecteur de la BDD
	 */
	public function __construct($DB)
	{
		$this->DB = $DB;
	}

	/**
	 * Retourne une liste de tarifs et de tranches de poids pour l'installateur Girard Agediss
	 *
	 * @param  array  $params Contient le paramètre servant à filtrer la recherche (des rajouts peuvent être fait selon les besoin)
	 *
	 * @return array  $return Liste des tarifs par tranche
	 */
	public function getTarifsGA($params = array())
	{
		if($params == array()) return false;

		$sql = "SELECT *";
		$sql .= " FROM sls s";
		$sql .= " INNER JOIN slszones sz USING(zone)";
		$sql .= " WHERE 1";

		if(isset($params['cp']) && is_numeric($params['cp']))
		{
			$sql .= " AND sz.dep = " . $params['cp'];
		}

		$req = $this->DB->query($sql);
		while($row = $this->DB->fetch_array($req))
		{
			$return[] = $row;
		}

		return $return;
	}

	/**
	 * Retourne une liste de tarifs pour l'installateur Enigma (UK)
	 *
	 * @return array  $return Liste des tarifs par tranche
	 */
	public function getTarifsEnigma()
	{
		$sql = "SELECT paht, `from`";
		$sql .= " FROM enigma";
		$sql .= " GROUP BY `from`";
		$req = $this->DB->query($sql);
		while($row = $this->DB->fetch_array($req))
		{
			$return[] = $row;
		}

		return $return;
	}
}