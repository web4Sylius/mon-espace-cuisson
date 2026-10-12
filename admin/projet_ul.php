<?php

include("admin_global.php");

ini_set("max_input_time",300);
ini_set("upload_max_filesize","24M");

if(!isset($_GET['projetid'])) exit;

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
		$name = clean($_FILES['sendfile']['name']);
		
		$DB_site2->query("INSERT INTO projet_files SET userid='".$_GET['userid']."', projetid='".($_GET['projetid'])."', parent='".$_GET['parent']."', date=".time().", utilisateur='".$user_info['username']."', name='".mysqli_real_escape_string($DB_site2->link_id,$name)."', size=".$_FILES['sendfile']['size'].", ext='$extension'");
		
		$id=$DB_site2->insert_id();
		$year=date("Y",time());
		
		if(!is_dir("projetfiles/".$year)) mkdir("projetfiles/".$year);
		if(!move_uploaded_file($_FILES['sendfile']['tmp_name'], "projetfiles/".$year."/".$id.".".$extension)){
			$msg2="Votre fichier n'a pas pu être envoyé";
			$DB_site2->query("UPDATE projet_files SET state=-1 WHERE id=$id");
		}else
			$DB_site2->query("UPDATE projet_files SET state=1 WHERE id=$id");
		
		$msg2="Envoi fichier ".strip_tags($_FILES['sendfile']['name'])." terminé";
		
		if(isset($_GET['action']) && $_GET['action']!="") $onload.="window.parent.".$_GET['action'];
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
<div id=\"msg\" style=\"display:none;\">Envoi en cours <img src=\"/configurations/www.ma-cave-a-vin.fr/images/templates/ajax_wait.gif\" width=\"16\" align=\"absmiddle\"></div>
<div id=\"files\">$fichiers</div>
</body>
</html>";

function clean($file){
	$caracteres = array('Á' => 'a', 'Â' => 'a', 'Ä' => 'a', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', '@' => 'a', 'È' => 'e', 'É' => 'e', 'Ê' => 'e', 'Ë' => 'e', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', '€' => 'e', 'Ì' => 'i', 'Í' => 'i', 'Î' => 'i', 'Ï' => 'i', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Ö' => 'o', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o', 'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'µ' => 'u', 'Œ' => 'oe', 'œ' => 'oe', '$' => 's', '&' => '-');
	
	$name = strtr($file, $caracteres);
	//$name = preg_replace('#[^A-Za-z0-9]+#', '-', $name);
	//$name = trim($name, '-');
	//$name = strtolower($name);

	return $name;
}

?>
