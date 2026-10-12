<?php

class DAOfournisseur
{
	public static function loadCodeFournisseurByName($DB,$fournisseur) {
		$f=$DB->query_first("SELECT code FROM fournisseur_code WHERE fournisseur='".$fournisseur."';");
		return $f['code'];
	}
}

?>