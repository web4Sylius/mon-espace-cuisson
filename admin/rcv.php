#!/usr/bin/php

<?php

$fd = fopen("php://stdin", "r");
$email = "";
while (!feof($fd)) {
$email .= fread($fd, 1024);
}
fclose($fd);




?>
