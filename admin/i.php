 
<?php
echo phpversion();

$t1=new DateTime();
// $t1->setTimestamp();
$t2=new DateTime("tomorrow");
// $t2->setTimestamp();
$d=$t1->diff($t2)->format("H:i:s");

print $d;

?>