<?php

class DAOtype {
	public static function getTypeById($DB,$idType) {
		$t=$DB->query_first("SELECT libelle_type FROM garantie_type WHERE id_type=".$idType.";");
		$nomType=$t[0];
        return $nomType;
	}

	public static function getTypes($DB){ 
		$return=array();
		$types=$DB->query("SELECT id_type,libelle_type FROM garantie_type");
		while($type=$DB->fetch_array($types))
		    $return[$type['id_type']]=$type['libelle_type'];
		    
		return $return;
	}
	
	public static function loadMarquesByTypes($DB,$idType) {
		return $DB->query("SELECT DISTINCT(m.marqueid),m.libelle FROM marque m, article_marque am, article a WHERE m.marqueid=am.marqueid AND a.artid=am.artid AND a.typearticleid=".$idType.";");
	}
}

?>