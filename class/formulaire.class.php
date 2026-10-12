<?php
/**
 * La classe Form permet de gérer la construction d'un formulaire automatiquement
 *
 * UTILISATION :
 * Instancier la class en transmettant le $_POST
 * Paramétrer la balise form si besoin (cf. propriétés ci-dessous)
 * Enregistrer tous les champs avec registerInput()
 * Ouvrir le formulaire avec start() et le fermer avec end()
 * Afficher chaque input avec input()
 * Tester la soumission avec getErreur()
 * Avant ou après la mise à jour des champs en BDD, mettre à jour les champs du formulaire avec update()
 */
class Formulaire {

	private $erreur = '';

	public $name   = ''; //Nom du formulaire
	public $id     = ''; //ID du formulaire
	public $method = 'post'; //GET ou POST (POST par défaut)
	public $action = ''; //Lien vers lequel pointe la validation du formulaire
	public $target = '';
	public $styles = array(); //Tableau contenant les styles	
	public $inputs = array(); //Tableaux contenant tous les champs du formulaire

	/**
	 * Constructeur
	 *
	 * @param array $post_values Tableau de champs de formulaires ($_POST)
	 */
	public function __construct($post_values = array())
	{
		$this->post_values = $post_values;
	}

	/**
     * Ecrit l'ouverture de balise <form> complète (avec tous ses paramètres)
     *
     * @return string Balise d'ouverture de formulaire
     */
	public function start()
	{
		//Concaténation des styles CSS éventuellement transmis
		$styles = '';
		if(count($this->styles) > 0)
		{
			$styles = $this->parseStyles($this->styles);
		}

		return "<form method=\"".$this->method."\" action=\"".$this->action."\" ".($this->name != '' ? 'name="'.$this->name.'"' : '')." ".($this->id != '' ? 'id="'.$this->id.'"' : '')." ".($this->target != '' ? 'target="'.$this->target.'"' : '')." ".$styles.">";
	}

	/**
     * Ecrit la fermeture de balise <form>
     *
     * @return string Balise de fermeture de formulaire
     */
	public function end()
	{
		return "</form>";
	}

	/**
     * Ecrit l'input demandé
     *
     * @param string $name Nom de l'input (attribut "name" du champs)
     */
	public function input($name)
	{
		try 
		{
			//Vérification de l'existence du champs $name
			if(!isset($this->inputs[$name])) throw new Exception("input() : L'input \"".$name."\" n'existe pas.");

			$myInput = $this->inputs[$name];

			//Vérification de la présence d'un type
			if(!isset($myInput['type'])) throw new Exception("input() : L'input \"".$name."\" n'a pas de \"type\" défini.");

			//Pour retourner le bon type de bouton
			switch($myInput['type'])
			{
				case 'text':
				case 'hidden':
				case 'submit':
					return $this->createInput($myInput);
					break;
				case 'select':
					return $this->createSelect($myInput);
					break;
				case 'radio':
					return $this->createRadio($myInput);
					break;
				case 'checkbox':
					return $this->createCheckbox($myInput);
					break;
				case 'textarea':
					return $this->createTextarea($myInput);
					break;
			}
		}

		catch(Exception $erreur) 
		{
			echo "Erreur : " . $erreur->getMessage();
		}
	}

	/**
     * Ajoute un un input dans le formulaire
     * Clés obligatoires : name / type
     * Clés facultatives : id / value / styles / options / class
     *
     * @param array $params Liste des attributs/valeurs de l'input
     */
	public function registerInput($params)
	{	
		try
		{
			//Si l'input n'a pas de nom ni de type ou que l'1 des 2 est vide, la classe ne peut pas fonctionner correctement
			if(!array_key_exists('name', $params)) throw new Exception("addInput() : Attribut \"name\" manquant.");
			if(!array_key_exists('type', $params)) throw new Exception("addInput() : Attribut \"type\" manquant.");
			if($params['name'] == '') throw new Exception("addInput() : Attribut \"name\" vide.");
			if($params['type'] == '') throw new Exception("addInput() : Attribut \"type\" vide.");

			//Récupère tous les attributs de l'input dans un tableau
			$this->setInput($params);

			$myInput = $this->inputs[$params['name']];

			//Si le formulaire a été soumis, vérification des champs obligatoires
			if(count($this->post_values) > 0 && isset($myInput['obligatoire']) && isset($this->post_values[$myInput['name']]) && $this->post_values[$myInput['name']] == '')
			{
				$this->inputs[$params['name']]['class']  .= ' champs_missing'; 
				$this->inputs[$params['name']]['options']['placeholder'] = "Champs obligatoire";

				if($this->getErreur() == '') $this->setErreur('Champs obligatoire(s) manquant(s)');
			}
		}

		catch(Exception $erreur) 
		{
			echo "Erreur : " . $erreur->getMessage();
		}
	}

	/**
     * Sépare tous les attributs de l'input dans un tableau
     *
     * @param array $params Liste des attributs/valeurs de l'input
     */
	private function setInput($params)
	{
		//Récupération de chaque attribut
		foreach($params as $le_param => $value)
		{
			if($le_param == 'name') $input_name = $value;
			$aInput[$le_param] = $value;
		} 

		//Insertion du nouveau champs dans le tableau "général" des inputs
		$this->inputs[$input_name] = $aInput;
	}

	/**
     * Met en forme un input (type submit / text / hidden)
     *
     * @param array $myInput Tableau d'attributs du bouton
     *
     * @return string Balise input à intégrer
     */
	private function createInput($myInput)
	{
		return "<input " . $this->get_type($myInput) . " " . $this->get_name($myInput) . " " . $this->get_id($myInput) . " " . $this->get_value($myInput) . " " . $this->get_options($myInput) . " " . $this->get_class($myInput) . " " . $this->get_styles($myInput) . ">";
	}

	/**
     * Met en forme une liste déroulante (select)
     *
     * @param array $myInput Tableau d'attributs du bouton
     *
     * @return string Balise select à intégrer
     */
	private function createSelect($myInput)
	{
		return "<select " . $this->get_name($myInput) . " " . $this->get_id($myInput) . " " . $this->get_options($myInput) . " " . $this->get_class($myInput) . " " . $this->get_styles($myInput) . ">" . $this->get_values($myInput) . "</select>";
	}

	/**
     * Met en forme une textarea
     *
     * @param array $myInput Tableau d'attributs du bouton
     *
     * @return string Balise textarea à intégrer
     */
	private function createTextarea($myInput)
	{
		return "<textarea " . $this->get_name($myInput) . " " . $this->get_id($myInput) . " " . $this->get_options($myInput) . " " . $this->get_class($myInput) . " " . $this->get_styles($myInput) . ">" . $this->get_value($myInput) . "</textarea>";
	}

	/**
     * Retourne la syntaxe de l'attribut "type"
     *
     * @param array $element Tableau d'attributs du bouton
     *
     * @return string Attribut "type" formaté
     */
	private function get_type($element)
	{
		return "type=\"" . $element['type'] . "\"";
	}

	/**
     * Retourne la syntaxe de l'attribut "name"
     *
     * @param array $element Tableau d'attributs du bouton
     *
     * @return string Attribut "name" formaté
     */
	private function get_name($element)
	{
		return "name=\"" . $element['name'] . "\"";
	}

	/**
     * Retourne la syntaxe de l'attribut "id"
     *
     * @param array $element Tableau d'attributs du bouton
     *
     * @return string Attribut "id" formaté
     */
	private function get_id($element)
	{
		$l_id = '';
		if(isset($element['id']) && $element['id'] != '') $l_id = "id=\"" . $element['id'] . "\"";

		return $l_id;
	}

	/**
     * Retourne la syntaxe de l'attribut "value"
     * Fonctionne pour textarea et input
     *
     * @param array $element Tableau d'attributs du bouton
     *
     * @return string Attribut "value" formaté
     */
	private function get_value($element)
	{
		$la_value = '';
		if(in_array($element['type'],array('text','hidden','submit')) == 'input') $la_value = "value=\"";
		/*if(isset($this->post_values[$element['name']]) && $this->post_values[$element['name']] != '')
		{
			$la_value .= $this->post_values[$element['name']];
		} 
		else if(isset($element['value']))
		{
			$la_value .= stripslashes($element['value']);
		}*/
		if(isset($element['value']))
		{
			$la_value .= stripslashes($element['value']);
		}
		if(in_array($element['type'],array('text','hidden','submit')) == 'input') $la_value .= "\"";

		return $la_value;
	}

	/**
     * Retourne la syntaxe des liste de choix "options" d'un select
     *
     * @param array $element Tableau d'attributs du bouton
     *
     * @return string Liste des options formatée
     */
	private function get_values($element)
	{
		$les_values = '';
		foreach($element['values'] as $value => $libelle)
		{
			$selected = '';
			if((isset($this->post_values[$element['name']]) && $this->post_values[$element['name']] == $value && $this->post_values[$element['name']] != '') || (!isset($this->post_values[$element['name']]) && $element['value'] == $value && $element['value'] != ''))
			{
				$selected = 'selected';
			}

			$les_values .= "<option value=\"" . $value . "\" " . $selected . ">" . stripslashes($libelle) . "</option>";
		}

		return $les_values;
	}

	/**
     * Retourne la syntaxe de l'attribut "style"
     *
     * @param array $element Tableau d'attributs du bouton
     *
     * @return string Liste des styles formatée
     */
	private function get_styles($element)
	{
		$input_styles = '';
		if(isset($element['styles']) && count($element['styles']) > 0)
		{
			$input_styles = $this->parseStyles($element['styles']);
		}

		return $input_styles;
	}

	/**
     * Retourne une liste d'attributs d'un champs
     * e.g. onmouserover, onmouseout, size, length, etc... 
     *
     * @param array $element Tableau d'attributs du bouton
     *
     * @return string Liste des attributs formatée
     */
	private function get_options($element)
	{
		$input_options = '';
		if(isset($element['options']) && count($element['options']) > 0)
		{
			$input_options = $this->parseOptions($element['options']);
		}

		return $input_options;
	}

	/**
     * Retourne la syntaxe de l'attribut "class"
     *
     * @param array $element Tableau d'attributs du bouton
     *
     * @return string Attribut class formaté
     */
	private function get_class($element)
	{
		$la_class = '';
		if(isset($element['class']) && $element['class'] != '') $la_class = "class=\"".$element['class']."\"";

		return $la_class;
	}

	/**
     * Regroupe les styles d'un tableau afin de former une chaine
     *
     * @param array $styles Tableau de styles
     *
     * @return string Liste des styles formatée
     */
	private function parseStyles($styles)
	{
		$les_styles = ' style="';
		foreach($styles as $key => $value) $les_styles .= $key.':'.$value.';';
		$les_styles .= '"';

		return $les_styles;
	}

	/**
     * Regroupe les élements passés en clé "option" 
     *
     * @param array $options Tableau d'options
     *
     * @return string Liste des attributs "supplémentaires"
     */
	private function parseOptions($options)
	{
        $les_options = '';
		foreach($options as $option_type => $l_option) {
            $les_options .= " " . $option_type . "=\"" . $l_option . "\"";
        }

		return $les_options;
	}

	/**
     * Retourne la liste des erreurs de traitement du formulaire
     *
     * @return string Erreurs de traitement du formulaire
     */
	public function getErreur()
	{
		return $this->erreur;
	}

	/**
     * Ajoute une erreur de traitement à la liste déjà en place
     *
     * @param string $erreur L'erreur à ajouter
     */
	private function setErreur($erreur)
	{
		if($this->getErreur() != '') $this->erreur .= '<br>';
		$this->erreur .= $erreur;
	}

	/**
     * Met à jour les champs de la classe
     * A utilisé après avoir soumis un formulaire et vérifié que tous les champs étaient valides
     */
	public function update()
	{
		foreach($this->post_values as $input_name => $input_value)
        { 
    		if(is_array($input_value))
    		{
    			foreach($input_value as $key => $value)
    			{
    				$input_name = $input_name.'['.$key.']';
    				if(isset($this->inputs[$input_name])) $this->inputs[$input_name]['value'] = $value;
    			}
    		}
    		else 
    		{
    			if(isset($this->inputs[$input_name])) $this->inputs[$input_name]['value'] = $input_value;
    		}
        } 
	}
} 