<?php

class DAOarticle {

    public static function typeArticleByIDart($DB,$langue,$idart){
		$typeArt=$DB->fetch_array($DB->query("SELECT * FROM typearticle WHERE typearticleid=(SELECT typearticleid FROM article WHERE artid=".$idart.")"));
		return $typeArt;
    }

    public static function marqueArticleByIDart($DB,$langue,$idart){
		$marqueArt=$DB->fetch_array($DB->query("SELECT * FROM marque WHERE marqueid=(SELECT marqueid FROM article_marque WHERE artid=".$idart.")"));
		return $marqueArt;
    }

    public static function getFournisseurPrincByProduct($DB,$langue,$idart,$site_code){
		$fournisseurPrinc=false;
		$f=$DB->query("SELECT fournisseur,principal FROM article_fournisseur WHERE artid=".$idart.";");
		while($af=$DB->fetch_array($f)){
		    $principaux=explode(",",$af['principal']);
		    if($principaux[0]=="1" || in_array($site_code,$principaux))
			$fournisseurPrinc=$af[0];
		}

		return $fournisseurPrinc;
    }

    public static function getFournisseurGarantieByProduct($DB,$langue,$idart){
		$fournisseursGaranties=$DB->query("SELECT DISTINCT(g.fournisseur) FROM article_garantie_liste ag LEFT JOIN garantie g ON ag.garantieid=g.garantieid WHERE ag.artid=".$idart." AND g.fournisseur IN (SELECT DISTINCT(societe) FROM fournisseur WHERE type=\"garadv\");");
		$lesFounisseursGaranties = array();
		while ($fournisseurGarantie=$DB->fetch_array($fournisseursGaranties)){
			$lesFounisseursGaranties[]=$fournisseurGarantie[0];
		}
		return $lesFounisseursGaranties;
    }

    public static function loadAllArticles($DB,$index=null){
		$articles=$DB->query("SELECT a.*, af.fournisseur FROM article a INNER JOIN article_fournisseur af ON a.artid=af.artid AND af.principal=1;");
		$lesArticles = array();
		while ($article=$DB->fetch_array($articles)){
			if (is_null($index)) {
				$lesArticles[]=$article;
			} else {
				$lesArticles[$article['artid']]=$article;
			}
		}
		return $lesArticles;
    }

    public static function loadAllArticlesExceptLotEtPack($DB,$index=null){
		$articles=$DB->query("SELECT a.*, af.fournisseur FROM article a INNER JOIN article_fournisseur af ON a.artid=af.artid AND af.principal=1 WHERE a.artid NOT IN (SELECT artid FROM article_lot) AND a.artid NOT IN (SELECT artid FROM article_pack);");
		$lesArticles = array();
		while ($article=$DB->fetch_array($articles)){
			if (is_null($index)) {
				$lesArticles[]=$article;
			} else {
				$lesArticles[$article['artid']]=$article;
			}
		}
		return $lesArticles;
    }

    public static function loadAllArticlesByMarque($DB,$marqueArt,$index=null){
		$articles=DAOarticle::loadAllArticles($DB);
		$lesArticles = array();
		foreach ($articles as $article) {
			$marque=DAOarticle::marqueArticleByIDart($DB,'FR',$article['artid']);
			if ($marque['marqueid']==$marqueArt) {
				if (is_null($index)) {
					$lesArticles[]=$article;
				} else {
					$lesArticles[$article['artid']]=$article;
				}
			}
		}
		return $lesArticles;
    }

    public static function loadAllArticlesByType($DB,$typeArt,$index=null){
		$articles=DAOarticle::loadAllArticles($DB);
		$lesArticles = array();
		foreach ($articles as $article) {
			if ($article['typearticleid']==$typeArt) {
				if (is_null($index)) {
					$lesArticles[]=$article;
				} else {
					$lesArticles[$article['artid']]=$article;
				}
			}
		}
		return $lesArticles;
    }

    public static function loadAllArticlesByFourn($DB,$fournArt,$index=null){
		$articles=DAOarticle::loadAllArticles($DB);
		$lesArticles = array();
		foreach ($articles as $article) {
			if ($article['fournisseur']==$fournArt) {
				if (is_null($index)) {
					$lesArticles[]=$article;
				} else {
					$lesArticles[$article['artid']]=$article;
				}
			}
		}
		return $lesArticles;
    }

    public static function artInPack($DB, $artid) {
    	$articles=$DB->query("SELECT * FROM article_pack WHERE artid=".$artid.";");
    	$lesArticles = array();
		while ($article=$DB->fetch_array($articles)){
			$lesArticles[]=$article;
		}
		return $lesArticles;
    }

    public static function artInLot($DB, $artid) {
    	$articles=$DB->query("SELECT * FROM article_lot WHERE artid='".$artid."' ");
    	$lesArticles = array();
		while ($article=$DB->fetch_array($articles)){
			$lesArticles[]=$article;
		}
		return $lesArticles;
    }
    
     public function getPhotos($DB, $artid){
          $sql = "SELECT *";
          $sql .= " FROM articlephoto";
          $sql .= " WHERE artid = '".$artid."'";
          $sql .= " AND visible=1";
          $sql .= " AND (langue = '' OR langue='".$template->langue."')";
          $tab = $DB->query_first($sql);
          
          return $tab;
     }
     
     public function getValeursCarac($DB, $langue, $artid, $aCarac){
          $sql = "SELECT ac.*, cv.*, cv.libelle".$langue." AS libelle, cv.description".$langue." AS description";
          $sql .= " FROM caracteristiquevaleur cv, article_caractval ac";
          $sql .= " WHERE cv.caractvalid = ac.caractvalid";
          $sql .= " AND caractid IN (";
          foreach($aCarac as $la_carac){
               $sql .= $la_carac.', ';
          }
          $sql .= "-1)";
          $sql .= " AND artid = '".$artid."'";
          $sql .= " ORDER BY cv.position ASC";
          $tab = $DB->query($sql);
          
          return $tab;
     }
     
     public function isLot($DB, $artid){
          
          $sql = "SELECT id";
          $sql .= " FROM article_lot";
          $sql .= " WHERE lotid = '".$artid."'" ;
          $result = $DB->query_first($sql);
          
          if(!empty($result)){
               return true;
          } else {
               return false;
          }
     }
     
     public static function listeArticlesLots($DB, $artid){
          
          $sql = "SELECT *";
          $sql .= " FROM article_lot";
          $sql .= " WHERE lotid = ".$artid;
          $result = $DB->query($sql);
          
          while($row = $DB->fetch_array($result)){
               $tabRetour[] = $row;
          }
          
          return $tabRetour;
     }

    /**
     * Retourne la liste des articles coups de coeur de la page d'accueil
     *
     * @param  object  $DB        Objet pour accès à la BDD
     * @param  string  $site_code Identification du site (base, uk, ...)
     * @param  boolean $sitepro   Si on est sur le site pro ou non
     *
     * @return array              Tableau d'identifiants produits
     */
    public static function getCDC($DB, $site_code = 'base', $sitepro = false){

    	$sql = "SELECT artid";
    	$sql .= " FROM cdc_index";
    	$sql .= " WHERE 1";
    	$sql .= " AND site_code = '".$site_code."'";
    	$sql .= " AND site      = '".($sitepro ? "pro" : "part")."'";
    	$sql .= " ORDER BY id";
    	$req = $DB->query($sql);
    	while($row = $DB->fetch_array($req)){
               $aRetour[] = $row;
		}
          
		return $aRetour;
    }
}