<?
require_once __DIR__.'/../../secrets.php';


die();

error_reporting(E_ALL);
$dblink = mysql_connect("localhost", TRANSFER_DB_USER, TRANSFER_DB_PASSWORD);
mysql_select_db("sports", $dblink);

//Select stock

$stock_query = mysql_query("SELECT * FROM stock LEFT JOIN article_caractval USING(artid) LEFT JOIN caracteristiquevaleur USING(caractvalid) LEFT JOIN caracteristique USING(caractid) ORDER BY artid, caractid, caractvalid");

$array_stock = array();

while($stocks = mysql_fetch_assoc($stock_query)) {
	
	$array_stock[$stocks['artid']]['nombre'] = $stocks['nombre'];
	if($stocks['caractid'] || $stocks['caractvalid'])
		$array_stock[$stocks['artid']]['caracts'][$stocks['caractid']][] = $stocks['caractvalid'];
	
}

function stock_decrement($dbLink, $artid, $caractval_array = array(), $delta = '-1') {

	//requete pour remettre les caracteristique dans l'ordre
	if(sizeof($caractval_array)) {
		$caractval_query = "SELECT caractvalid, caractid FROM caracteristiquevaleur INNER JOIN article_caractval USING(caractvalid) WHERE caractvalid IN(".implode(",",$caractval_array).") AND article_caractval.artid = '$artid' ORDER BY caractid ASC;";
	
		$caractval_req = $dbLink->query($caractval_query);
		$caractval_array = array();
		
		while($caractval = $dbLink->fetch_array($caractval_req)) {
	
			$nb_caract_req = $dbLink->query_first("SELECT count(*) FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) WHERE caractid = '$caractval[caractid]' AND artid = '$artid';");
	
			if($nb_caract_req[0] > 1)			
				$caractval_array[] = $caractval['caractvalid'];
		}
	}
	
	//verifier que la branche de l'arbre existe
	$verify_query = "SELECT stockid FROM stocks WHERE artid = '$artid' AND caractvalid = '0' AND parentid = '0' LIMIT 1;";
	$verify_req = $dbLink->query_first($verify_query);
	if($verify_req) {
		$lastparentid = $verify_req['stockid'];
		//verifier les sous branches existent
		foreach($caractval_array as $key => $value) {
			$verify_query = "SELECT stockid FROM stocks WHERE artid = '$artid' AND caractvalid = '$value' AND parentid = '$lastparentid' LIMIT 1;";	
			$verify_req = $dbLink->query_first($verify_query);
			if(!$verify_req) {
				$dbLink->query("INSERT INTO stocks (caractvalid, artid, parentid) VALUES('$value', '$artid', '$lastparentid');");
				$lastparentid = $dbLink->insert_id();
			}
			else {
				$lastparentid = $verify_req['stockid'];
			}
		}
	}
	else {
		$dbLink->query("INSERT INTO stocks (artid) VALUES('$artid');");
		$lastparentid = $dbLink->insert_id();
	}
	
	$to_update = sizeof($caractval_array) ? end($caractval_array) : 0;
	//Mettra a jour la ligne qui nous interesse dans l'arbre
	$update_query = "UPDATE stocks SET total = $delta WHERE stockid = '$lastparentid';";
	$dbLink->query($update_query);

}

class dbo {
	public $dblnk;
	
	public function query($sql) {
		return mysql_query($sql, $this->dblnk);
	}
	
	public function query_first($sql) {
		return mysql_fetch_array(mysql_query($sql, $this->dblnk));
	}
	
	public function insert_id() {
		return mysql_insert_id($this->dblnk);
	}
	
	public function fetch_array($res) {
		return mysql_fetch_array($res);
	}
}

$dbolink = new dbo;
$dbolink->dblnk=$dblink;

//parcourir l'array
foreach ($array_stock as $artid => $infos) {
	
	//on compte les caractéristique
	$number_comb = 1;
	$array_comb = array(array(0));
	if(isset($infos['caracts'])) {
		foreach ($infos['caracts'] as $caractid => $vals) {
			$number_comb *= count($vals);

			$temp_array_comb = array();
			for($c = 0 ; $c < count($vals) ; $c++) {
				$temp_array_comb = array_merge($temp_array_comb, $array_comb);
			}
			
			reset($vals);
			$array_comb = $temp_array_comb;
			foreach($array_comb as $key => $con) {
				$array_comb[$key][] = current($vals);
				if(!next($vals)) reset($vals);
			}
					
		}
	}
	
	//generer l'array de stocks
	$array_values = array();
	for($s = 0 ; $s < $number_comb ; $s++) {
		$array_values[] = floor($infos['nombre']/$number_comb);
	}
	
	$array_values[0] += $infos['nombre']-$number_comb*floor($infos['nombre']/$number_comb);
	
	echo '<pre>';
	print_r($array_comb);
	echo '</pre>';
	
	reset($array_values);
	foreach($array_comb as $combs) {
		echo "trouduc !!!";
		stock_decrement($dbolink, $artid, $combs, current($array_values));
		next($array_values);
	}
	
}




?>