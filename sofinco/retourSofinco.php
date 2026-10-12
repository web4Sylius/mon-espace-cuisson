<?php

//Enregistrement du paiement dans les logs
$logfile = "responseSOF.log";
$fp = fopen($logfile, "a");
fwrite($fp, "\n///// PAIEMENT SOFINCO ///// \n");
fwrite($fp, "Date => ".date('d-m-Y H:i:s')."\n");
foreach ($_POST as $key => $value){
     fwrite($fp, "$key => $value\n");
}
fwrite($fp, "///// PAIEMENT SOFINCO ///// \n\n");
fclose ($fp);