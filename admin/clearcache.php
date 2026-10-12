<?php
include_once("host_config_admin.php");
include($rootpath."configurations/".$host."/class/cloudflare.class.php");

$cloud = new cloudflare_api($DB_site2,$rootpath);
foreach ($cloudflare_zone_id as $zone) {
	$cloud->purgeAll($cloudflare_email, $cloudflare_api, $zone);
}

?>
