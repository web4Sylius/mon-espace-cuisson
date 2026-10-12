//article to article_site et article_visible
<?php
include_once('admin_global.php');
$sql = "SELECT artid,prix, prixconseilleht, active, tauxchoisi FROM article";
$res = $DB_site2->query($sql);
while($art = $DB_site2->fetch_array($res)){
	//$DB_site2->query("INSERT INTO article_site SET artid='".$art['artid']."' , prix='".$art['prix']."' , prixconseilleht='".$art['prixconseilleht']."' , active ='".$art['active']."' , tauxchoisi='".$art['tauxchoisi']."', site_code='base'");
	$active = intval($art['active']);
	if(intval($art['active']) == 2 || intval($art['active']) == 3) $active=1
	$DB_site2->query("INSERT INTO article_visible SET artid='".$art['artid']."', date='".time()."', active='".$active."'");
}