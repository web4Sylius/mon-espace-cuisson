<?php
/**
 * CLASSE PERMETTANT DE GERER LES CREATIONS ET MODIFICATIONS D'ENREGISTREMENT DES PAGES
 */
class form
{
	private $DB;

	public $header;
	public $menu;
	public $footer;

	public $edition = false;

	private $titre       = ''; //Titre de la page en cours
	private $fil_ariane  = ''; //Fil d'ariane de la page en cours
	private $content     = ''; //Contenu intégral de la page
	private $lien_retour = ''; //Lien de retour vers l'étape de liste
	private $form_vars   = array(); //Contient la liste des champs retournés
	private $valid       = true;
	private $edit_id     = ''; //Identifiant de l'enregistrement en cours d'édition

	/**
	 * Constructeur de la classe
	 *
	 * @param object $DB Connecteur de la BDD
	 */
	public function __construct($DB, $form_vars = array(), $url_vars = array())
	{
		$this->DB = $DB;

		$this->header = (defined('BACK_PATH') ? BACK_PATH : '') . '/header.php';
		$this->menu   = (defined('BACK_PATH') ? BACK_PATH : '') . '/menu.php';
		$this->footer = (defined('BACK_PATH') ? BACK_PATH : '') . '/footer.php';

		if($form_vars != array()) $this->form_vars = $form_vars;

		if(isset($url_vars['id']) && is_numeric($url_vars['id'])) $this->edit_id = $url_vars['id'];
	}

	/**
	 * Détermine le titre de la rubrique en cours
	 *
	 * @param string $titre Titre de la rubrique
	 */
	public function setTitre($titre)
	{
		$this->titre = $titre;
	}

	/**
	 * Met en place le fil d'ariane de la navigation en cours
	 *
	 * @param array $aParams Tableau contenant des intitulés et éventuellement des liens, ordonnés du la racine jusqu'à la rubrique actuelle
	 */
	public function setFilAriane($aParams)
	{
		$this->setLienRetour($aParams[count($aParams)-2]['lien']);

		foreach($aParams as $le_fil)
		{
			if($this->fil_ariane != '') $this->fil_ariane .= " > ";

			$lien_debut = '';
			$lien_fin   = '';
			if(isset($le_fil['lien']) && $le_fil['lien'] != '')
			{
				$lien_debut = "<a href=\"" . BACK_URL . '/rubriques' . (substr($le_fil['lien'],0,1 != '/') ? '/' : '') . $le_fil['lien'] . "\" style=\"color: #363E42;\">";
				$lien_fin   = "</a>";
			}

			$this->fil_ariane .= $lien_debut . $le_fil['titre'] . $lien_fin;
		}
	}

	/**
	 * Récupère la liste des champs éditables
	 *
	 * @param array $champs Liste de champs avec caractéristiques suivantes :
	 * - "libelle" : Libellé du champs
	 * - "type" : Type de champs (valeurs possibles : "select", "input", "radio", "checkbox", "textarea")
	 * - "sql" : Requête permettant de remplir les SELECT (<option value=" %selection1% "> %selection2% </option>)
	 * - "req" : Indique si le champs est requis (obligatoire)
	 */
	public function setChamps($champs)
	{
		$this->cells = $champs;
	}

	private function setLienRetour($lien)
	{
		if($lien != '') $this->lien_retour = BACK_URL . '/rubriques' . (substr($lien,0,1 != '/') ? '/' : '') . $lien;
	}

	/**
	 * Retourne le tableau de résultats formaté en HTML
	 *
	 * @return string Tableau formaté
	 */
	private function getTabFormate()
	{
		$tableau = ""
		. "<form method=\"post\" action=\"\" name=\"\">"
			. ($this->edit_id != '' ? "<input type=\"hidden\" name=\"edit_id\" value=\"" . $this->edit_id . "\">" : "")
			. "<div class=\"cadre_form\">"
				. "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";

				//Création de la ligne de résultats
				foreach($this->cells as $la_cellule)
				{
					$tableau .= ""
					. "<tr>"
						. "<td class=\"libelle\">" . ($la_cellule['req'] ? "<span style=\"color: red;\">*</span> " : '') . $la_cellule['libelle'] . "</td>"
						. "<td class=\"champs\">" . $this->getChamps($la_cellule) . "</td>"
					. "</tr>";
				} 
		
		$tableau .= ""
				. "</table>"
			. "</div>"
			."<div class=\"action_buttons\">"
				. "<a href=\"" . $this->lien_retour . "\"><i class=\"fa fa-times fa-2x\"></i><br>Annuler</a>"
				. "<button type=\"submit\"><i class=\"fa fa-floppy-o fa-2x\"></i><br>Enregistrer</button>"
			. "</div>"
		. "</form>";

		return $tableau;
	}

	/**
	 * Affiche la page en cours
	 */
	public function showContent()
	{
		if($this->fil_ariane != '') $this->push($this->getFilArianeFormate()); 
		if($this->titre != '') $this->push($this->getTitreFormate());
		if(!$this->valid) $this->push($this->getErrorMessage());
		$this->push($this->getTabFormate());

		$this->show();
	}

	private function push($content)
	{
		$this->content .= $content;
	}

	private function show()
	{
		echo $this->content;
	}

	private function getErrorMessage()
	{
		return "<p class=\"erreur_form\">Veuillez remplir les champs obligatoires</p>";
	}

	/**
	 * Retourne le titre formaté en HTML
	 *
	 * @return string Titre de la page formaté
	 */
	private function getTitreFormate()
	{
		return "<p class=\"titre_page\">" . $this->titre . "</p>";
	}

	/**
	 * Retourne le fil d'ariane formaté en HTML
	 *
	 * @return string Fil d'ariane formaté
	 */
	private function getFilArianeFormate()
	{
		return "<p class=\"filariane_page\">" . $this->fil_ariane . "</p>";
	}

	private function getChamps($params)
	{
		$input = '';

		switch($params['type'])
		{
			case 'select':

				if(!isset($params['sql'])) return '';

				$req = $this->DB->query($params['sql']);

				$input = ""
				. "<select " . ($params['req'] && $this->form_vars != array() && $this->form_vars[$params['name']] == '' ? "style=\"border: 2px solid red;\"" : '') . " name=\"" . $params['name'] . ($params['multiple'] ? '[]' : '') . "\" " . ($params['multiple'] ? 'multiple' : '') . ">"
					. (!isset($params['multiple']) ? "<option value=\"\">-</option>" : "");
				while($ligne = $this->DB->fetch_row($req))
				{
					$input .= "<option value=\"" . $ligne[0] . "\" " . (isset($this->form_vars[$params['name']]) && $this->form_vars[$params['name']] == $ligne[0] ? 'selected' : '') . ">" . $ligne[1] . "</option>";
				}
				$input .= "</select>";

				if($params['multiple']) $input .= "<br><span class=\"note\">ALT + clic pour sélectionner plusieurs options distinctes.</span><br><span class=\"note\">SHIFT + clic pour sélectionner une série d'options.</span>";

				break;

			case 'text':

				$input = "<input type=\"text\" name=\"" . $params['name'] . "\" value=\"" . (isset($this->form_vars[$params['name']]) ? $this->form_vars[$params['name']] : '')  . "\" " . ($params['req'] && $this->form_vars != array() && $this->form_vars[$params['name']] == '' ? "style=\"border: 2px solid red;\"" : '') . ">";

				break;

			case 'textarea':

				$input = "<textarea name=\"" . $params['name'] . "\" " . ($params['req'] && $this->form_vars != array() && $this->form_vars[$params['name']] == '' ? "style=\"border: 2px solid red;\"" : '') . ">" . (isset($this->form_vars[$params['name']]) ? $this->form_vars[$params['name']] : '')  . "</textarea>";

				if(isset($params['desc']) && $params['desc'] != '') $input .= "<br><span class=\"note\">" . $params['desc'] . "</span>";

				break;
		}

		return $input;
	}

	/**
	 * Remplissage des champs lors d'une édition.
	 * Ne remplace pas si le formulaire a été soumis, afin de réécrire les valeurs précédemment saisies
	 *
	 * @param string $sql Requête de sélection avec chaque champs nommé à l'identique des champs d'édition
	 */
	public function setValues($sql)
	{
		$aResults = $this->DB->query_first($sql);

		foreach($aResults as $key => $value) if(is_string($key) && !isset($this->form_vars[$key])) $this->form_vars[$key] = $value; 
	}

	public function isValid()
	{
		foreach($this->cells as $la_cellule)
		{
			$this->valid = true;
			if($la_cellule['req'] && (!isset($this->form_vars[$la_cellule['name']]) || (!is_array($this->form_vars[$la_cellule['name']]) && $this->form_vars[$la_cellule['name']] == '') || (is_array($this->form_vars[$la_cellule['name']]) && $this->form_vars[$la_cellule['name']] == array())))
			{
				$this->valid = false;
				return false;
			} 
		}

		return true;
	}
}