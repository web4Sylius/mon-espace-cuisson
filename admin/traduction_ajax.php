<?php

require "admin_global.php";

if(isset($_GET['where'])){
    $key=str_replace("/","\/",$_GET['where']);
    $root=opendir("..");
    while($file=readdir($root)){
	$ext=pathinfo("../".$file,PATHINFO_EXTENSION);
	if($ext=="php"){
	    $data=file_get_contents("../".$file);
	    if(preg_match("/translate\(\"".$key."\"\)/",$data)>0){
		print "<p><b>".$file."</b>";
		preg_match_all("/[\n](.*)translate\(\"$key\"\)(.*)[\n]/",$data,$matches,PREG_SET_ORDER);
		foreach($matches as $m) print "<br>".htmlentities($m[0],ENT_COMPAT,"ISO-8859-15");
		print "</p>";
	    }
	}
    }
    closedir($root);
}

if(isset($_GET['path'])){
    $req=$DB_site2->query("SELECT * FROM traduction WHERE path LIKE '".$_GET['path']."/%'");
    print "<li onclick=\"settranslate('$name','','');\"><i>(Vide)</i></li>";
    print "<li onclick=\"showtranslate('$name');newtranslation('".$name."','".$_GET['path']."')\"><i>Ajouter</i></li>";
    while($key=$DB_site2->fetch_array($req)) print "<li onclick=\"settranslate('".$_GET['name']."','".$key['path']."','".addslashes($key[$template->langue])."');\">".$key[$template->langue]."</li>";
}

if(isset($_POST['create'])){
    $error="OK";
    $name=str_replace(" ","_",stripslashes($_POST['tr_FR']));
    if($name=="") 
	$error="Il faut remplir la valeur FR";
    else{
	$path=$_POST['path']."/".substr(str_replace(array("'","\"","/",",",";","\\"),"",$name),0,100);
	$req=$DB_site2->query_first("SELECT COUNT(*) FROM traduction WHERE path='".$path."'");
	if($req[0]>0) $error="Cette traduction existe déjà";
    }
    if($error=="OK"){
	$sql="INSERT INTO traduction SET path='".mysqli_real_escape_string($DB_site2->link_id,$path)."'";
	foreach($langues as $langue) $sql.=", `".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$_POST['tr_'.$langue])."'";
	
	$result=$DB_site2->query($sql);
	if($result==false) $error="Erreur lors de la création de la traduction";
    }
    header("Cache-Control: no-cache");
    header("Content-type:text/xml");
    print "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>
    <xml>
    <error>$error</error>
    <name>".$_POST['create']."</name>
    <path>".$path."</path>";
    foreach($langues as $langue) print "<".$langue.">".stripslashes($_POST['tr_'.$langue])."</".$langue.">";
    print "</xml>";
}

?>