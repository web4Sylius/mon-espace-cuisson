<?php
/**
 * CLASSE PERMETTANT DE CREER DES PAGES DE LISTINGS
 *
 * TODO
 * - Commenter et organisation des nouvelles méthodes
 * - Mettre en place la gestion automatique de la pagination
 */
class liste 
{
	private $DB;

	public $header;
	public $menu;
	public $footer;
	
	private $titre         = ''; //Titre de la page en cours
	private $fil_ariane    = ''; //Fil d'ariane de la page en cours
	private $content       = ''; //Contenu intégral de la page
	private $entetes       = array(); //Entêtes de colonne du tableau des résultats
	private $tris          = array(); //Libellés des champs en BDD pour le tri
	private $cells         = array(); //Liste des résultats de la requête
	private $valide_msg    = ''; //Texte indiquant que la suppression d'enregistrement s'est bien déroulée
	private $params_url    = array(); //Paramètres de l'URL
	private $params_post   = array(); //Paramètres POST
	private $url_default   = ''; //URL avec les paramètres par défaut (hors "tri" et "ordre")
	private $url_tri       = ''; //URL contenant les paramètres de tris
	private $nb_colonnes   = ''; //Nombre de colonnes (hors suppression/edition)
	private $tableau       = ''; //Tableau général des résultats
	private $page          = 1; //Page affichée par défaut
	private $results_total = 0; //Nombre total de résultats de la recherche
	private $nb_pages      = 1; //Nombre de pages

	//PARAMETRABLES
	public $creation      = false; //Par défaut, on empêche la création d'un enregistrement
	public $edition       = false; //Par défaut, on empêche la modification d'un enregistrement
	public $suppression   = false; //Par défaut, on empêche la suppression d'un enregistrement
	public $recherche     = false; //Par défaut, on masque la recherche
	public $nb_results    = 20; //Nombre de résultats par page
	public $range_results = 5; //Nombre de pages affichées dans la pagination

	/**
	 * Constructeur de la classe
	 *
	 * @param object $DB Connecteur de la BDD
	 */
	public function __construct($DB, $params_url = array(), $params_post = array())
	{
		$this->DB = $DB;

		$this->header = (defined('BACK_PATH') ? BACK_PATH : '') . '/header.php';
		$this->menu   = (defined('BACK_PATH') ? BACK_PATH : '') . '/menu.php';
		$this->footer = (defined('BACK_PATH') ? BACK_PATH : '') . '/footer.php';

		if($params_url != array())
		{
			$this->params_url = $params_url;

			if(isset($this->params_url['ajout']) && $this->params_url['ajout'] == 1)
			{
				$this->valide_msg = 'Enregistrement créé';
			}
		} 

		$this->setDefaultURL();
		$this->setTriURL();

		if($params_post != array())
		{
			$this->params_post = $params_post;
		}

		//Pagination
		if(isset($this->params_url['page']) && is_numeric($this->params_url['page'])) $this->page = $this->params_url['page'];
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
	 * Détermine les différentes entêtes du tableau de liste
	 *
	 * @param array $aParams Tableau contenant chaque titres d'entêtes, dans l'ordre
	 */
	public function setEntetes($aParams)
	{
		foreach($aParams as $champs_tri => $l_entete)
		{
			$this->setTris($champs_tri);

			if(!is_array($l_entete))
			{
				$this->entetes[]            = $l_entete;
				$this->filtres[$champs_tri] = array();
			}
			else
			{
				$this->entetes[] = $l_entete['libelle'];
				$this->filtres[$champs_tri] = $l_entete;
			}
		}

		$this->nb_colonnes = count($this->entetes);
	}

	/**
	 * Exécute la requête SQL de liste des résultats et stock chaque ligne dans un tableau
	 *
	 * @param string $sql Requête DSL à exécuter
	 */
	public function setContentSQL($sql)
	{
		//Création d'un doublon de la requête qui va faire un COUNT() sans LIMIT permettant de déterminer le nombre total de résultats
		$pos_from = strripos($sql, 'from');
		$sql_nb = substr($sql,$pos_from);
		$sql_nb = "SELECT COUNT(*) as nbrez " . $sql_nb;
		$this->results_total = $this->DB->query_first($sql_nb)['nbrez'];

		$this->nb_pages = ceil($this->results_total / $this->nb_results);

		//Calcul des tris
		if(isset($this->params_url['tri']) && $this->params_url['tri'] != '')
		{
			$tri_courant   = $this->params_url['tri'];
			$ordre_courant = $this->params_url['ordre'];
		}
		else
		{
			$tri_courant   = $this->tris[0]['libelle'];
			$ordre_courant = 'ASC';
		}

		$sql .= " ORDER BY " . $tri_courant . " " . $ordre_courant;
		$sql .= " LIMIT " . ($this->page - 1) * $this->nb_results . "," . $this->nb_results;

		$requete = $this->DB->query($sql);

		while($ligne = $this->DB->fetch_row($requete))
		{
			$this->cells[] = $ligne;
		} 
	}

	/**
	 * Affiche la page en cours
	 */
	public function showContent()
	{
		if($this->fil_ariane != '') $this->setFilArianeFormate(); 
		if($this->titre != '') $this->setTitreFormate();
		if($this->valide_msg != '') $this->setValideMsgFormate();
		$this->setTabFormate();

		$this->show();
	}

	/**
	 * Met en place le tri sur une colonne
	 *
	 * @param string $name Nom de la colonne
	 */
	private function setTris($name)
	{
		$i = count($this->tris);
		$this->tris[$i]['libelle'] = $name;
		$this->tris[$i]['ordre'] = 'ASC';
		$this->tris[$i]['fleche'] = '';
		if(!isset($this->params_url['tri']) && $i == 0)
		{
			$this->tris[$i]['ordre']  = 'DESC';
			$this->tris[$i]['fleche'] = 'caret-down';
		} 
		if(isset($this->params_url['tri']) && $this->params_url['tri'] == $name)
		{
			if($this->params_url['ordre'] == 'ASC')
			{
				$this->tris[$i]['ordre'] = 'DESC';
				$this->tris[$i]['fleche'] = 'caret-down';
			} 
			else
			{
				$this->tris[$i]['fleche'] = 'caret-up';
			}
		}
	}

	/**
	 * Détermine l'URL par défaut, qui ne comprend pas d'arguments de tri, d'ajout, de suppression, etc...
	 */
	private function setDefaultURL()
	{
		if($_SERVER['QUERY_STRING'] != '')
		{
			$aParams = explode('&',$_SERVER['QUERY_STRING']);
			foreach($aParams as $le_parametre)
			{
				$aExplode = explode('=',$le_parametre);
				if($aExplode[0] != 'tri' && $aExplode[0] != 'ordre' && $aExplode[0] != 'page') $this->url_default .= ($this->url_default != '' ? '&' : '?') . $aExplode[0] . '=' . $aExplode[1];
			}
		} 
	}

	/**
	 * Détermine l'URL par défaut, en y incluant les paramètres de tris
	 */
	private function setTriURL()
	{
		$this->url_tris = $this->url_default;

		if($_SERVER['QUERY_STRING'] != '')
		{
			$aParams = explode('&',$_SERVER['QUERY_STRING']);
			foreach($aParams as $le_parametre)
			{
				$aExplode = explode('=',$le_parametre);
				if($aExplode[0] == 'tri' || $aExplode[0] == 'ordre') $this->url_tris .= ($this->url_tris != '' ? '&' : '?') . $aExplode[0] . '=' . $aExplode[1];
			}
		} 
	}

	/**
	 * Ajoute le titre formaté en HTML dans le template général
	 */
	private function setTitreFormate()
	{
		$this->push("<p class=\"titre_page\">" . $this->titre . "</p>");
	}

	/**
	 * Ajoute le fil d'ariane formaté dans le template général
	 */
	private function setFilArianeFormate()
	{
		$this->push("<p class=\"filariane_page\">" . $this->fil_ariane . "</p>");
	}

	/**
	 * Ajoute le message de validation de suppression formaté dans le template général
	 */
	private function setValideMsgFormate()
	{
		$this->push("<p class=\"validemsg_page\">" . $this->valide_msg . "</p>");
	}

	/**
	 * Retourne le bouton servant à créer un enregistrement
	 *
	 * @param  string $emplacement Indique si le bouton est celui qui doit être placé en bas ou en haut du tableau
	 *
	 * @return string $bouton      Bouton "Ajouter"
	 */
	private function getBoutonNouveau($emplacement)
	{	
		if($this->creation)
		{
			$placement = 'top';
			if($emplacement == 'haut') $placement = 'bottom';

			$create_params = "?action=create";
			if($_SERVER['QUERY_STRING'] != '')
			{
				$create_params = $create_params . "&" . $_SERVER['QUERY_STRING'];
			}

			$this->tableau .= ""
			. "<div class=\"block_align\" style=\"text-align: right;\">"
				. "<a class=\"new_enr\" href=\"form.php" . $create_params . "\"><i class=\"fa fa-plus fa-2x\"></i><br>Ajouter</a>"
			. "</div>";
		}
	}

	/**
	 * Met en place les entêtes de colonnes du tableau
	 */
	private function setEntetesFormate()
	{
		$this->tableau .= "<tr>";

		if($this->suppression) $this->tableau .= "<td class=\"entete icons_pt\">&nbsp;</td>";

		$i = 0;
		foreach($this->entetes as $l_entete)
		{
			$this->tableau .= "<td class=\"entete\"><a href=\"javascript:void(0);\" onclick=\"tri_liste('" . $this->url_default . "','" . $this->tris[$i]['libelle'] . "','" . $this->tris[$i]['ordre'] . "')\">" . $l_entete . ($this->tris[$i]['fleche'] != '' ? " <i class=\"fa fa-" . $this->tris[$i]['fleche'] . "\"></i>" : '') . "</a></td>";
			$i++;
		} 
		if($this->edition || $this->suppression || $this->recherche) $this->tableau .= "<td class=\"entete icons\" ".(isset($this->params_post['recherche']) ? "style=\"width: 120px;\"" : "").">&nbsp;</td>";

		$this->tableau .= "</tr>";
	}

	/**
	 * Met en place le contenu du tableau de résultats, exempt des entêtes de colonnes
	 */
	private function setContentFormate()
	{
		if($this->cells != array())
		{
			$i   = 1;
			$cpt = 0;
			foreach($this->cells as $les_cellules)
			{
				$this->tableau .= "<tr>";

				if($this->suppression) $this->tableau .= "<td class=\"bggris" . $i . "\" " . ($cpt == 0 ? "style=\"border-top: none;\"" : "") . "><input type=\"checkbox\" id=\"chk" . $les_cellules[0] . "\" onclick=\"toggle_delete(this.value);\" class=\"delete\" value=\"" . $les_cellules[0] . "\"></td>";

				for($j=1;$j<=$this->nb_colonnes;$j++)
				{
					$this->tableau .= "<td class=\"bggris" . $i . "\" " . ($cpt == 0 ? "style=\"border-top: none;\"" : "") . ">" . $les_cellules[$j] . "</td>";
				} 

				//Ajout éventuel des boutons de modification/suppression
				if($this->edition || $this->suppression)
				{
					$this->tableau .= "<td class=\"bggris" . $i . "\" " . ($cpt == 0 ? "style=\"border-top: none;\"" : "") . ">";
					$this->setManageButtons($les_cellules[0]);
					$this->tableau .= "</td>";
				}
				else if($this->recherche)
				{
					$this->tableau .= "<td class=\"bggris" . $i . "\" " . ($cpt == 0 ? "style=\"border-top: none;\"" : "") . "></td>";
				} 

				$this->tableau .= "</tr>";

				$i++;
				if($i%2 != 0) $i = 1;
				$cpt++;
			} 
		}
		else
		{
			$nb_cols = $this->nb_colonnes + 1;
			if($this->edition || $this->suppression) $nb_cols++;
			$this->tableau .= "<tr><td colspan=\"" . $nb_cols . "\">Aucun enregistrement</td></tr>";
		}
	}

	/**
	 * Met en place l'affichage des bouton Modifier/Supprimer sur chaque ligne de la liste, quand nécessaire
	 *
	 * @param int $enreg_id Identifiant de l'enregistrement à éditer/supprimer
	 */
	private function setManageButtons($enreg_id)
	{
		$edit_action  = "location.href='form.php" . $this->url_default . ($this->url_default != '' ? '&' : '?') . "action=edit&id=" . $enreg_id . "';";
		$suppr_action = "del_field(" . $enreg_id . ");";

		if(!$this->edition || !$this->suppression) //Uniquement 1 bouton
		{
			if($this->edition)
			{
				$texte_bouton  = "<i class=\"fa fa-pencil\"></i> Edit.";
				$action_bouton = $edit_action;
			}
			else
			{
				$texte_bouton  = "<i class=\"fa fa-trash\"></i> Suppr.";
				$action_bouton = $suppr_action;
			}
			
			$this->tableau .= "<a href=\"javascript:void(0);\" class=\"manage_button_simple\" onclick=\"" . $action_bouton . "\">" . $texte_bouton . "</a>";
		}
		else //Les 2 boutons rassemblés
		{

			$edit_bouton   = "<a href=\"javascript:void(0);\" class=\"manage_button\" onclick=\"" . $edit_action . "\" style=\"border-radius: 5px 0 0 5px;\"><i class=\"fa fa-pencil\"></i> Edit.</a>";
			$manage_bouton = "<a href=\"javascript:void(0);\" class=\"manage_button\" onclick=\"toggle_class(this, 'actions_bouton');\" style=\"border-radius: 0 5px 5px 0;\"><i class=\"fa fa-caret-down fa-lg\"></i></a>";
			$manage_encart = ""
			. "<div class=\"actions_bouton\" style=\"width: 100px;\">"
				. "<p><a href=\"javascript: void(0);\" onclick=\"" . $suppr_action . "\"><i class=\"fa fa-trash\"></i> Supprimer</a></p>"
			. "</div>";

			$this->tableau .= "<div style=\"position: relative;\">" . $edit_bouton . $manage_bouton . $manage_encart;
		}
	}

	/**
	 * Met en place l'affichage du nombre de résultats de la requête de listing et affiche le bouton "Ajouter" si besoin
	 *
	 * @param string $location Vaut "haut" ou "bas" pour indiquer l'emplacement du bouton "Ajouter" par rapport au tableau
	 */
	private function setNbResults()
	{
		$this->tableau .= ""
		. "<div class=\"block_align\">"
			. "<p class=\"nb_results\">" . $this->results_total . " résultat(s)</p>"
		. "</div>";
	}

	/**
	 * Retourne le tableau de résultats formaté en HTML
	 *
	 * @return string Tableau formaté
	 */
	private function setTabFormate()
	{
		$this->tableau .= "<div class=\"cadre_resultats\">";
		$this->setNbResults();
		$this->setPagination();
		$this->getBoutonNouveau('haut');
		$this->tableau .= "</div>";

		$this->tableau .= ""
		. "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"formate_results\">"
			. "<form method=\"post\" id=\"form_liste_results\" action=\"" . $this->url_tri . "\">"
				. "<input type=\"hidden\" name=\"recherche\" value=\"1\">";

		$this->setEntetesFormate();
		$this->setRechercheFormate();
		$this->setContentFormate();
		
		$this->tableau .= ""
			. "</form>"
		. "</table>";

		$this->tableau .= "<div class=\"cadre_resultats\">";
		$this->setActionsGroupees();
		$this->setPagination();
		$this->tableau .= ""
			. "<div class=\"block_align\"></div>"
		. "</div>";

		$this->setFormSuppr();

		$this->push($this->tableau);
	}

	/**
	 * Met automatiquement en place le bloc pagination quand nécessaire
	 */
	private function setPagination()
	{
		//Calcul page min/page max
		$min = 1;
		$max = $this->nb_pages;
		$pmoy = $this->range_results / 2;
		$pmin = $this->page - $pmoy >= 0 ? ceil($this->page - $pmoy) : floor($this->page - $pmoy);
		$pmax = floor($this->page + $pmoy);
		if($pmin < $min)
		{
			$diff = $min - $pmin;
			$pmin = $min;
			while($diff > 0)
			{
				if($max > $pmax) $pmax++;
				$diff--;
			}
		} 
		if($pmax > $max)
		{
			$diff = $pmax - $max;
			$pmax = $max;
			while($diff > 0)
			{
				if($pmin > $min) $pmin--;
				$diff--;
			}
		} 

		$class_prev = $this->nb_pages > 1 && $this->page > 1 ? ' premier' : '';
		$class_suiv = $this->nb_pages > 1 && $this->page < $max ? ' dernier' : '';

		//On n'affiche pas la pagination s'il n'y a qu'une seule page
		if($min != $max)
		{
			$this->tableau .= "<p class=\"pagination_liste\">";

			if($this->nb_pages > 1 && $this->page > 1)
			{
				$this->tableau .= ""
				. "<a class=\"" . $class_prev . "\" href=\"" . $this->url_tris . ($this->url_tris != '' ? '&' : '?') . "page=1\"><i class=\"fa fa-angle-double-left fa-2x\"></i></a>"
				. "<a href=\"" . $this->url_tris . ($this->url_tris != '' ? '&' : '?') . "page=" . ($this->page - 1) . "\"><i class=\"fa fa-angle-left fa-2x\"></i></a>";
			}

			for($i=$pmin;$i<=$pmax;$i++)
			{
				$class_num = '';
				if($this->nb_pages > 1 && $this->page == 1 && $i == 1)
				{
					$class_num  = ' premier';
				}
				else if($this->nb_pages > 1 && $this->page == $max && $i == $pmax)
				{
					$class_num  = ' dernier';
				}

				$this->tableau .= "<a href=\"" . $this->url_tris . ($this->url_tris != '' ? '&' : '?') . "page=" . $i . "\" class=\"num" . $class_num . " " . ($this->page == $i ? 'selected' : '') . "\">" . $i . "</a>";
			}

			if($this->nb_pages > 1 && $this->page + 1 <= $this->nb_pages)
			{
				$this->tableau .= ""
				. "<a href=\"" . $this->url_tris . ($this->url_tris != '' ? '&' : '?') . "page=" . ($this->page + 1) . "\"><i class=\"fa fa-angle-right fa-2x\"></i></a>"
				. "<a href=\"" . $this->url_tris . ($this->url_tris != '' ? '&' : '?') . "page=" . $this->nb_pages . "\" class=\"" . $class_suiv . "\"><i class=\"fa fa-angle-double-right fa-2x\"></i></a>";
			}

			$this->tableau .= "</p>";
		}
	}

	/**
	 * Retourne 2 input text "min" / "max" pour filtration
	 *
	 * @param  string $name Nom de l'input de base, auxquels sera rajouté "_min" et "_max" pour les différencier
	 *
	 * @return string       Les 2 champs formatés en HTML
	 */
	private function input_fourch($name)
	{
		$input = ""
		. "<input type=\"text\" name=\"" . $name . "_min\" class=\"minmax\" placeholder=\"Min\" value=\"" . (isset($this->params_post[$name . '_min']) ? $this->params_post[$name . '_min'] : '') . "\">"
		. "&nbsp;&nbsp;"
		. "<input type=\"text\" name=\"" . $name . "_max\" class=\"minmax\" placeholder=\"Max\" value=\"" . (isset($this->params_post[$name . '_max']) ? $this->params_post[$name . '_max'] : '') . "\">";

		return $input;
	}

	/**
	 * Retourne une liste déroulante en tant que filtre
	 *
	 * @param  string $name Nom de l'input
	 * @param  string $sql  Requête de remplissage du select
	 *
	 * @return string       Liste déroulante formatée en HTML
	 */
	private function select($name, $sql, $data)
	{
		$liste_options = "<option value=\"\">-</option>";

		if($sql != '')
		{
			$req = $this->DB->query($sql);
			while($ligne = $this->DB->fetch_row($req)) $liste_options .= "<option value=\"" . $ligne[0] . "\" " . (isset($this->params_post[$name]) && $this->params_post[$name] == $ligne[0] ? 'selected' : '') . ">" . $ligne[1] . "</option>";
		}
		else if(!empty($data))
		{
			foreach($data as $valeur => $libelle) $liste_options .= "<option value=\"" . $valeur . "\" " . (isset($this->params_post[$name]) && $this->params_post[$name] == $valeur ? 'selected' : '') . ">" . $libelle . "</option>";
		}

		$input = "<select name=\"" . $name . "\">" . $liste_options . "</select>";

		return $input;
	}

	/**
	 * Retourne un input text (recherche sur chaine de caractère) pour filtration
	 *
	 * @param  string $name Nom de l'input de base
	 *
	 * @return string       Le champs formaté en HTML
	 */
	private function input_text($name)
	{
		$input = "<input type=\"text\" name=\"" . $name . "\" value=\"" . (isset($this->params_post[$name]) ? $this->params_post[$name] : '') . "\">";

		return $input;
	}

	/**
	 * Affiche le bouton permettant de lancer la filtration ainsi que le bouton de réinitialisation si une filtration est en cours
	 *
	 * @return string Boutons "Rechercher" et éventuellement "Réinitialiser"
	 */
	private function submit_recherche()
	{
		$input = "<button type=\"submit\"><i class=\"fa fa-search-plus\"></i> Rech.</button>";

		if($this->params_post != array() && !isset($this->params_post['post_suppr'])) $input .= "<a href=\"" . $this->url_default . "\" class=\"reinit\"><i class=\"fa fa-eraser\"></i> Réinit.</a>";

		return $input;
	}

	/**
	 * Met en place la barre de recherche en dessous des entêtes de colonnes
	 * Pour fonctionner, $recherche doit valoir "true" et le tableau d'entêtes correctement configuré
	 * Ex : array( 
	 * 'poidsmin'   => array(
	 *		'libelle'     => 'Poids min (kg)',
	 *		'filtre_type' => 'fourchette'
	 * )
	 * 'poidsmin' 	 correspond au nom de l'input (utilisé pour la filtration ET pour le tri)
	 * 'libelle' 	 correspond au libellé de la colonne (entête)
	 * 'filtre_type' correspond au type de filtre à afficher (valeurs possible : 'select','fourchette','libelle')
	 * 'filtre_sql'  requête SQL dont les résultats sont à insérer dans le select ci-dessus
	 * 'filtre_data' tableau de données $value => $libelle à insérer dans le select
	 */
	private function setRechercheFormate()
	{
		if($this->recherche)
		{
			$this->tableau .= "<tr>";
			
			if($this->suppression) $this->tableau .= "<td class=\"recherche\"></td>";

			foreach($this->filtres as $name => $params)
			{
				$champs_filtre = '';
				if(isset($params['filtre_type']))
				{
					switch($params['filtre_type'])
					{
						case 'fourchette':
							
							$champs_filtre = $this->input_fourch($name);
							
							break;

						case 'select' :
							
							$sql  = isset($params['filtre_sql']) ? $params['filtre_sql'] : '';
							$data = isset($params['filtre_data']) ? $params['filtre_data'] : array();

							$champs_filtre = $this->select($name, $sql, $data);
							
							break;

						case 'libelle' :
							
							$champs_filtre = $this->input_text($name);
							
							break;
					}
				}

				$this->tableau .= "<td class=\"recherche\">" . $champs_filtre . "</td>";
			}

			if($this->edition || $this->suppression || $this->recherche) $this->tableau .= "<td class=\"recherche\">" . $this->submit_recherche() . "</td>";

			$this->tableau .= "</tr>";
		}
	}

	/**
	 * Met en forme un formulaire qui contient toutes les ID sélectionnées pour suppression dans la liste
	 * Contient un champs "post_suppr" qui permet d'identifier que c'est le formulaire de suppression des enreg qui vient d'être soumis
	 */
	private function setFormSuppr()
	{
		if($this->suppression && $this->cells != array())
		{
			$this->tableau .= ""
			. "<form method=\"post\" action=\"\" id=\"delete_elements\">"
				. "<input type=\"hidden\" name=\"post_suppr\" value=\"1\">";

				//On recrée les $_POST de filtration pour qu'ils soient toujours en place même après une suppression
				if($this->params_post != array())
				{
					foreach($this->params_post as $name => $value)
					{
						if(strpos($name, 'del_id') === false) $this->tableau .= "<input type=\"hidden\" name=\"" . $name . "\" value=\"" . $value . "\">";
					}
				}

			$this->tableau .= "</form>";
		}
	}

	/**
	 * Ajoute le bouton de gestion des actions groupées dans le tableau final
	 */
	private function setActionsGroupees()
	{
		if($this->suppression && $this->cells != array())
		{
			$this->tableau .= ""
			. "<div class=\"actions_group\">"
				. "<a class=\"bouton\" onclick=\"toggle_class(this, 'actions_bouton');\">Actions groupées <i class=\"fa fa-caret-down\"></i></a>"
				. "<div class=\"actions_bouton\">"
					. "<p>"
						. "<a href=\"javascript: void(0);\" onclick=\"toggle_check('check','delete');\"><i class=\"fa fa-check-square\"></i> Tout sélectionner</a>"
						. "<a href=\"javascript: void(0);\" onclick=\"toggle_check('uncheck','delete');\"><i class=\"fa fa-square-o\"></i> Tout déselectionner</a>"
					. "</p>"
					. "<p class=\"separation\"></p>"
					. "<p><a href=\"javascript: void(0);\" onclick=\"submit_form('delete_elements');\"><i class=\"fa fa-trash\"></i> Supprimer la sélection</a></p>"
				. "</div>"
			. "</div>";
		}
	}

	/**
	 * Exécute la requête de suppression d'un ou plusieurs enregistrement et affiche le message
	 *
	 * @param  string $sql Requête SQL de suppression
	 */
	public function delete($sql)
	{
		$this->DB->query($sql);
		$this->valide_msg = "Enregistrement(s) supprimé(s)";
	}

	/**
	 * Incrémente du contenu dans le template "général" affiché dans la fonction show()
	 *
	 * @param  string $content Contenu à intégrer
	 */
	private function push($content)
	{
		$this->content .= $content;
	}

	/**
	 * Affiche le contenu de la page
	 */
	private function show()
	{
		echo $this->content;
	}
}