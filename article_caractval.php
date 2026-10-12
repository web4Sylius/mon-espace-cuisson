<?php

include("global.php");

if($_GET['catid']!="") $sqlcatid="catid IN (".$_GET['catid'].") AND";
$req=$DB_site->query("SELECT artid,artcode,libelleFR FROM article a INNER JOIN article_marque am USING(artid) WHERE $sqlcatid marqueid=".$_GET['marqueid']);
while($art=$DB_site->fetch_array($req)){
    $color="black";
    if(isset($_GET['caractvalid'])){
      $opt=$DB_site->query_first("SELECT * FROM article_caractval WHERE artid=".$art['artid']." AND caractvalid=".$_GET['caractvalid']);
      if($opt['artid']==$art['artid']) $color="red";
      if(isset($_GET['go']) && $opt['artid']==""){
	    $r=$DB_site->query("INSERT INTO article_caractval SET artid=".$art['artid'].", caractvalid=".$_GET['caractvalid'].", achat=".floatval($_GET['paht']));
	    if($r) $color="green";
      }
    }
    print "<font style=\"color:$color\">".$art['artcode']." ".$art['libelleFR']."</font><br>";
}
print $DB_site->num_rows($req);

?>