<?php


require "admin_global.php";
ini_set("max_input_time",300);

$acceptfile=array("jpg","jpeg","png","bmp","avi","mp4","3gp","mov","wmv","pdf","doc","docx","xls","xlsx","ppt","pptx","wav","mp3");

if(isset($_FILES['sendfile'])){
    print "Envoi du fichier";
    $extension = strtolower(pathinfo($_FILES['sendfile']['name'], PATHINFO_EXTENSION));
    if(!in_array($extension,$acceptfile)){
	print "Type de fichier interdit";
    }else{
      $DB_site2->query("INSERT INTO sav_files SET savid=".$_POST['savid'].", date=".time().", utilisateur='".$user_info['username']."', name='".$_FILES['sendfile']['name']."', size=".$_FILES['sendfile']['size'].", ext='$extension'");
      $id=$DB_site2->insert_id();
      $year=date("Y",time());
      if(!move_uploaded_file($_FILES['sendfile']['tmp_name'], "sav/$year/".$id.".".$extension)){
              print "Votre fichier n'a pas pu être envoyé";
	      $DB_site2->query("UPDATE sav_files SET state=-1 WHERE id=$id");
      }else
	$DB_site2->query("UPDATE sav_files SET state=1 WHERE id=$id");
    }
}

if(isset($_GET['progress'])){
  $r=$DB_site2->query("SELECT * FROM sav_files WHERE savid=".$_GET['progress']." AND utilisateur='".$user_info['username']."' ORDER BY date DESC LIMIT 1");
  $lastul=$DB_site2->fetch_array($r);
  if($lastul==""){
	  print "0|";
	  exit;
  }
  $year=date("Y",$lastul['date']);
  $filename=$lastul['id'].".".$lastul['ext'];
  if($lastul['state']=="1") print utf8_encode("1|Fichier $lastul[name] envoyé|$lastul[id]|$lastul[name]|$lastul[ext]");
  if($lastul['state']=="-1") print "-1|Erreur d'envoi|0|";
  if($lastul['state']=="0"){
	  $filesize=filesize("sav/$year/".$filename);
	  print utf8_encode("0|".$filesize." ".round(100*$filesize/$lastul['size'])."%|$lastul[id]|$lastul[name]|$lastul[ext]");
  }
}

if(isset($_GET['delpj'])){
      $DB_site2->query("UPDATE sav_files SET state=2 WHERE id=".$_GET['delpj']);
      $r=$DB_site2->query("SELECT * FROM sav_files WHERE savid=".$_GET['savid']." AND state=1 ORDER BY date");
      while($file=$DB_site2->fetch_array($r)){
	      print utf8_encode("1| |$file[id]|$file[name]|$file[ext]\n");
      }
}

?>