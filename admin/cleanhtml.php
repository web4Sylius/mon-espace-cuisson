<?php

include("admin_global.php");

$r=$DB_site2->query("SELECT artcode,descriptionFR FROM article WHERE descriptionFR LIKE '%\\\\\\%'");
while($art=$DB_site2->fetch_array($r)){
    print "<br>".$art['artcode']."<textarea cols=\"100\" rows=\"5\">".$art['descriptionFR']."</textarea>";
}

?>