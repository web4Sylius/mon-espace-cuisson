<?php

include("admin_global.php");

ini_set("max_input_time",300);
ini_set("max_upload_size","24M");

if(!isset($_GET['artid'])) exit;

$msg2style="style=\"display:none;\"";
$formstyle="";
$msg3="Envoyer un fichier :";

$acceptfile=array("jpg","jpeg","png","bmp","avi","mp4","3gp","mov","wmv","pdf","doc","docx","xls","xlsx","ppt","pptx","odt","ods","wav","mp3");
if(isset($_FILES['sendfile'])){
    $msg2style="";
    $formstyle="style=\"display:none;\"";
    $onload="setTimeout('delaimsg2()',2000);";
    $msg3="Envoyer un autre fichier :";

    $extension = strtolower(pathinfo($_FILES['sendfile']['name'], PATHINFO_EXTENSION));
    if(!in_array($extension,$acceptfile)){
	$msg2="Type de fichier interdit";
    }else{
      $DB_site2->query("INSERT INTO article_files SET artid='".$_GET['artid']."', parent=".intval($_GET['parent']).", date=".time().", utilisateur='".$user_info['username']."', name='".mysqli_real_escape_string($DB_site2->link_id,$_FILES['sendfile']['name'])."', size=".$_FILES['sendfile']['size'].", ext='$extension'");
      $id=$DB_site2->insert_id();
      if(!move_uploaded_file($_FILES['sendfile']['tmp_name'], "../configurations/$host/articlefiles/".$id.".".$extension)){
              $msg2="Votre fichier n'a pas pu être envoyé";
	      $DB_site2->query("UPDATE article_files SET state=-1 WHERE id=$id");
      }else{
	$DB_site2->query("UPDATE article_files SET state=1 WHERE id=$id");
	$msg2="Envoi fichier ".strip_tags($_FILES['sendfile']['name'])." terminé";
	if(isset($_GET['action']) && $_GET['action']!="") $onload.="window.parent.".$_GET['action'];
      }
    }
}

// $requete=$DB_site2->query("SELECT name FROM sav_files WHERE savid=".$_GET['savid']." AND date>".(time()-120));
// while($f=$DB_site2->fetch_array($requete)) $fichiers.="<span class=\"file\">".$f['name']."</span>";

print "
<html>
<head>
<script language=\"javascript\">
function delaimsg2(){
    document.getElementById('msg2').style.display='none';
    document.forms.ulform.style.display='block';
}
</script>
<style>
body { font-size:12px; color:#363E42; margin:0; }
form { margin:0; }
#files { margin-top:4px; }
.file { border:solid thin #AAA; padding:2px; margin-right:5px; }
</style>
</head>
<body onload=\"$onload\">
<form name=\"ulform\" method=\"post\" enctype=\"multipart/form-data\" $formstyle>
    $msg3<br>
    <input type=\"file\" name=\"sendfile\" onchange=\"document.forms.ulform.style.display='none';document.getElementById('msg').style.display='block';document.forms.ulform.submit();\">
</form>
<div id=\"msg2\" $msg2style>$msg2</div>
<div id=\"msg\" style=\"display:none;\">Envoi en cours <img src=\"/configurations/$host/images/templates/ajax_wait.gif\" width=\"16\" align=\"absmiddle\"></div>
<div id=\"files\">$fichiers</div>
</body>
</html>";

?>