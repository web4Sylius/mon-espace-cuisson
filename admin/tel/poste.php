<?php

if(!isset($_GET['poste'])) exit;

$poste=$_GET['poste'];

while(true) {
  if(file_exists($poste) && time()-filemtime($poste)<20){
      readfile($poste);
//       unlink($poste);
      exit;
  }
  sleep(1);

}

?>
