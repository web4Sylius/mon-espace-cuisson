<?php

class DAOpays {
	public static function getPaysById($DB,$idPays) {
		$nomPays=$DB->query_first("SELECT libelle FROM pays WHERE paysid='".$idPays."';");
        return $nomPays[0];
	}
}

?>