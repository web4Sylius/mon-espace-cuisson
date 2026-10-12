<?php
ini_set('display_errors',1);

include_once("host_config_admin.php");
include_once($rootpath."class/email.php");

$DB_site=clone $DB_site2;

$trente_jours = strtotime("-30 days");
$six_mois = strtotime("-180 days");
$deux_ans = strtotime("-730 days");

// echo $trente_jours;
flush;

$tabStat=array();

$sql = "SELECT * FROM stat ";
$sql.= " WHERE timestamp < " . $trente_jours . "   "; // LIMIT 1000
$query = $DB_site->query($sql);

while($row = $DB_site->fetch_array($query)) {
	$sql = "INSERT IGNORE INTO stat_arch2 (id, ip, referant, agent, host, timestamp, cid, user)";
	$sql.= " VALUES(".$row[id].", '".$row[ip]."', '".mysqli_real_escape_string($DB_site->link_id,$row[referant])."', '".mysqli_real_escape_string($DB_site->link_id,$row[agent])."', '".mysqli_real_escape_string($DB_site->link_id,$row[host])."', ".$row[timestamp].", ".$row[cid].", '".$row[user]."')";
	//echo $sql."<br>";	
	$DB_site->query($sql);
	$sql = "DELETE FROM stat WHERE id = " . $row[id];
	//echo $sql."<br>";
	$DB_site->query($sql);
}


$tabStatpage=array();

$sql = "SELECT * FROM stat_page ";
$sql.= " WHERE timestamp < " . $trente_jours . "  ";
$query = $DB_site->query($sql);


while($row = $DB_site->fetch_array($query)) {
	$sql = "INSERT IGNORE INTO stat_page_arch2 (id, statid, timestamp, page, detail, status)";
	$sql.= " VALUES(".$row[id].", ".$row[statid].", ".$row[timestamp].", '".mysqli_real_escape_string($DB_site->link_id,$row[page])."', '".$row[detail]."', '".$row[status]."' )";
	//echo $sql."<br>";
	$DB_site->query($sql);
	$sql = "DELETE FROM stat_page WHERE id = " . $row[id];
	//echo $sql."<br>";
	$DB_site->query($sql);
}


$sql = "SELECT * FROM stat_page_arch2 ";
$sql.= " WHERE timestamp < " . $six_mois . "  ";
$query = $DB_site->query($sql);


while($row = $DB_site->fetch_array($query)) {
	$sql = "INSERT IGNORE INTO stat_page_arch (id, statid, timestamp, page, detail, status)";
	$sql.= " VALUES(".$row[id].", ".$row[statid].", ".$row[timestamp].", '".mysqli_real_escape_string($DB_site->link_id,$row[page])."', '".$row[detail]."', '".$row[status]."' )";
	//echo $sql."<br>";
	$DB_site->query($sql);
	$sql = "DELETE FROM stat_page_arch2 WHERE id = " . $row[id];
	//echo $sql."<br>";
	$DB_site->query($sql);
}

$sql = "DELETE FROM article_vu WHERE userid=0 AND date < " . $deux_ans . "  ";
$query = $DB_site->query($sql);


echo "done";

?>