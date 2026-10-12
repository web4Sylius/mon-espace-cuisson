<?php

include("admin_global.php");

if(isset($_GET['empty'])) $template->emptycache();

$files=$template->cachefiles();
if(count($files)>0){
  asort($files);
  print "<table>";
  foreach($files as $f) print "<tr><td>".$f."</td><td align=\"right\">".round(filesize($f)/1024)." ko</td><td>".date("d/m/Y H:i",filemtime($f))."</td></tr>";
  print "</table>";
}else
  print "Cache vide";

print "<p><a href=\"templatecache.php?empty=1\">Vider le cache</a></p>";

?>