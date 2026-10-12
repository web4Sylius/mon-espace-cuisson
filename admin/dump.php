<?php


$rootpath="/homez.368/macaveav/www/";
$host="www.ma-cave-a-vin.fr" ;
$nostat=1;
include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."mysql.php");
$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();
include_once($rootpath."admin/email.php");

if(!is_dir("backup")){
        mkdir("backup");
        $f=fopen("backup/.htaccess","w");
        fwrite($f,"DENY FROM ALL");
        fclose($f);
}

$txt=date("d/m/Y H:i")." Sauvegarde de la base :";
$tables=array();
$requete=$DB_site->query("SHOW TABLES");
while($t=$DB_site->fetch_array($requete))
    if(strpos($t[0],"stat")===false) $tables[]=$t[0];


foreach($tables as $table){
       $ret=dumptb($table);
       if($ret)
           $txt.="<br>table $table sauvegardée";
       else
           $txt.="<br>erreur sauvegarde $table";
}
/*
$ret=zipdir("backup");
if($ret)
    $txt.="<br>sauvegarde zippée";
else
    $txt.="<br>erreur de zip";   */

$mail=new email();
$mail->destinataire="webmaster@ma-cave-a-vin.fr";
$mail->expediteur="Ma Cave à Vin";
$mail->subject="Sauvegarde de la base Ma Cave à Vin";
$mail->body=$txt;
$mail->sendmail();


function dumptb($table){
    $mode=3;
    $datacount=0;
    $quotetype=array("string","blob","timestamp","datetime");

    $entete = "-- ----------------------\n";
    $entete .= "-- dump de la table ".$table." au ".date("d-M-Y")."\n";
    $entete .= "-- ----------------------\n\n\n";
    $creations = "";
    $insertions = "\n\n";


    $fDump = fopen("backup/dump ".$table.".sql", "wb");
    fwrite($fDump, $entete);


        // si l'utilisateur a demandé la structure ou la totale
        if($mode == 1 || $mode == 3){
                    $creations .= "-- -----------------------------\n";
                    $creations .= "-- creation de la table ".$table."\n";
                    $creations .= "-- -----------------------------\n";
                    $listeCreationsTables = $DB_site->query("show create table `".$table."`");
                    while($creationTable = $DB_site->fetch_array($listeCreationsTables)){
                      fwrite($fDump,$creationTable[1].";\n\n");
                      //$creations .= $creationTable[1].";\n\n";
                    }
                    $tbcount++;
        }
        // si l'utilisateur a demandé les données ou la totale
        if($mode > 1){
                    $donnees = $DB_site->query("SELECT * FROM `".$table."`");
                    $insertions = "-- -----------------------------\n";
                    $insertions .= "-- insertions dans la table ".$table."\n";
                    $insertions .= "-- -----------------------------\n";
                    fwrite($fDump,$insertions);
                    while($nuplet = $DB_site->fetch_array($donnees)){
                                  $insertions = "INSERT INTO ".$table." VALUES(";
                                  for($i=0; $i < mysqli_num_fields($donnees); $i++){
                                            if(is_null($nuplet[$i])) $nuplet[$i]="NULL";
                                            $quote="";
                                            $field_type = mysqli_fetch_field_direct($donnees,$i);
                                            if(in_array($field_type->type,$quotetype)) $quote="'";
                                            if($i != 0) $insertions .=  ", ";

                                            $insertions .=$quote.addslashes($nuplet[$i]).$quote;

                                  }

                                  $insertions .=  ");\n";
                                  fwrite($fDump,$insertions);
                                  $datacount++;
                    }
                    $insertions .= "\n";

        }

 
    fwrite($fDump, $creations);
    fclose($fDump);

    return true;
}

function zipdir($dir){
    $zip = new zipfile () ;

    $d=opendir($dir);
    if($d==false) return $false;

    while($file=readdir($d)){
         $extension = pathinfo($file, PATHINFO_EXTENSION);
         if($extension=="sql"){
                 $file=$dir."/".$file;
                 $f=fopen($file,'r');
                 $data=fread($f,filesize($file));
                 fclose($f);
                 unlink($file);

                 $zip->addfile($data,$file);
         }
    }

    $zipdata=$zip->file();
    $f=fopen($dir."/backup","wb");
    fwrite($f,$zipdata);
    fclose($f);
    return true;

}


?>
