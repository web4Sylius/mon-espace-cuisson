<?php

include("mailreader.php");

print "<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-15\">
</head>
<body>";

$file="inbox/".$_GET['file'];
if(!file_exists($file)){
    print "Fichier introuvable";
    exit;
}
$data=file_get_contents($file);
$r=getparts($data);

if($r['html']=="")
  print $r['text'];
else
  print $r['html']; 

print "</body>";

?>