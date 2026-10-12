<?php

include("admin_global.php");

// $requete=$DB_site2->query_first("SELECT * FROM mail_inbox WHERE `to`='".$user_info[mail_contact]."' AND id=".$_GET['id']);
$requete=$DB_site2->query_first("SELECT * FROM mail_inbox WHERE id=".$_GET['id']);
print $requete['body'];


?>