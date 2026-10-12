<?php
class tva
{
	private $DB;

	public $taux_normal = null;
	public $taux_reduit = null;
	public $taux_local = null;

	public function __construct($DB)
	{
		$this->DB = $DB;
	}

	private function load()
	{
		$sql = "SELECT TVAtauxnormal, TVAtauxreduit, TVAlocale";
		$sql .= " FROM pays";
		$sql .= " WHERE paysid = " . $this->pays_id;
		$req = $this->DB->query($sql);
		$aTVA = $this->DB->fetch_array($req);

		try
		{
			if(!$aTVA) throw new Exception("Cet ID pays n'existe pas");

			$this->taux_normal = $aTVA['TVAtauxnormal'];
			$this->taux_reduit = $aTVA['TVAtauxreduit'];
			$this->taux_local  = $aTVA['TVAlocale'];
		}
		catch(Exception $e)
		{
			$this->getError($e);
		}
	}

	//Setters
	public function setPays($id)
	{
		try
		{
			if(!is_numeric($id)) throw new Exception("L'ID pays doit être un entier");

			$this->pays_id = $id;

			$this->load();
		}
		catch(Exception $e)
		{
			$this->getError($e);
		}
	}

	//Getters
	public function getError($error)
	{
		exit('Erreur : ' . $error->getMessage());
	}
}