<?php
require "../admin_global.php";
header('Content-Type: application/json; charset=utf-8');
// contentType: "application/json; charset=utf-8"
$s = mysqli_real_escape_string($DB_site2->link_id,trim($_GET['term']));

$q['fournisseur'] = $DB_site2->query('
	SELECT `societe` AS `id`, `societe` AS `label`
	FROM `fournisseur`
	WHERE `societe` LIKE "%'.$s.'%"
	GROUP BY `societe`
	ORDER BY `societe`
	LIMIT 20 ');
/*
if(count($explode)>1){
	$conditions='';
	foreach ($explode as $value) {
		$conditions.=' AND ( `raisonsociale` LIKE ("%'.$value.'%") OR `nom` LIKE ("%'.$value.'%") OR `prenom` LIKE ("%'.$value.'%") ) ';
	}
	$q['client'] = $DB_site2->query('
		SELECT `userid` AS `id`, CONCAT(`raisonsociale`, " ", `nom`, " ", `prenom`, " (" , `codepostal` ,")" ) AS `label`
		FROM `utilisateur`
		WHERE 1 '.$conditions.'
		ORDER BY `nom`, `prenom` ');

} else {
	$q['client'] = $DB_site2->query('
		SELECT `userid` AS `id`, CONCAT(`raisonsociale`, " ", `nom`, " ", `prenom`, " (" , `codepostal` ,")" ) AS `label`
		FROM `utilisateur`
		WHERE `raisonsociale` LIKE ("%'.$s.'%")
		OR `nom` LIKE ("%'.$s.'%")
		OR `prenom` LIKE ("%'.$s.'%")
		ORDER BY `nom`, `prenom` ');
}*/
/*
$explode=explode(" ",$s);
$count=count($explode);
if($count==1){
	$search="+".$s."*";
} else {
	$search="";
	foreach ($explode as $value) {
		$search.=" +".$value."* ";
	}
}
*/
$search=str_replace(" ","%", $s);
if($_GET['ignore']!=""){
	$ignore_sql=" AND userid!='".$_GET['ignore']."' ";
}

$sql = "SELECT userid AS id, CONCAT(raisonsociale, ' ', nom, ' ', prenom, ' (' , codepostal ,')' ,' -N° ' , userid ) AS label
		FROM utilisateur 
		WHERE
		(
			userid = '".$search."'
			OR nom LIKE '%".$search."%'
			OR prenom LIKE '%".$search."%'
			OR raisonsociale LIKE '%".$search."%'
		) ".$ignore_sql." ORDER BY userid DESC ";

$q['client'] = $DB_site2->query($sql);

$d = array();
foreach ($q as $type => $query){
	while ($r=$DB_site2->fetch_assoc($query)) {
		$r['type'] = $type;
		if($type=="fournisseur"){
			$r['type2'] = $type;
		} else {
			$r['type2'] = "utilisateur";
		}
		if(is_null($r['value'])){
		  $r['value'] = $r['label'];
		}
		$r['label']=utf8_encode($r['label']);
		$d[] = $r;
	}
}
print json_encode($d);
?>
