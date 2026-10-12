<?php

header("refresh:2;".$_GET['url']);

print "Redirection vers ".htmlspecialchars($_GET['url']);

?>