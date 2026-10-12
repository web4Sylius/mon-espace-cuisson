<?php
$ip_allow = array('127.0.0.1','213.44.169.37','80.12.82.212','146.59.188.71','134.214.83.4','51.77.27.253');
 // lecture de l'ip en cours
 $ip = isset($_SERVER['REMOTE_ADDR']) ? trim($_SERVER['REMOTE_ADDR']) : '';

 if ( array_search($ip, $ip_allow) === FALSE ){
 	header('Location: https://www.mon-espace-cuisson.com/');
 	exit;
 }

include 'global.php';

$contactid = mysqli_real_escape_string($DB_site->link_id,$_GET['contactid']);
$nom = mysqli_real_escape_string($DB_site->link_id,$_GET['nom']);
$annuaire = mysqli_real_escape_string($DB_site->link_id,$_GET['annuaire']);
$extension = mysqli_real_escape_string($DB_site->link_id,$_GET['extension']);
$num = mysqli_real_escape_string($DB_site->link_id,$_GET['num']);
$projetid = $commercial = "";
$extension=106;
//cas numero non reconnu
if($contactid == ""){

	$ext = $extension;

//cas numero reconnu	
}else{

	//recup dernier projet en cours du client
	$liste_etatid="2,3,4,7,8";
	$sql = "SELECT projetid, commercial FROM projet WHERE userid = '$contactid' AND etatid IN ($liste_etatid) ORDER BY projetid DESC";
	$req = $DB_site->query_first($sql);
	
	if($req["projetid"] != ""){// projet ouvert existe
		
		$projetid = $req["projetid"];
		
		if($req['commercial'] != ""){//avec commercial attribué
		
			$commercial = $req["commercial"];
		
		}
	
	}

	//projet non existant ou pas de commercial attribué au projet
	if($projetid == "" || $commercial == ""){
		$sql = "SELECT commercial FROM utilisateur WHERE userid = '$contactid'";
		$commercial =  $DB_site->query_first($sql);
		$commercial = $commercial[0];
	}

	if($commercial == ""){//pas de commercial attribué on envoi sur l'extension appelee
	
		$ext = $extension;

	
	}else{ //commercial reconnu on recupere son extension
		$sql = "SELECT telFR FROM parametresgeneraux WHERE username = '$commercial' AND actif='1'";
		$tel =$DB_site->query_first($sql);
		if($tel[0] != "")
			$ext = $tel[0];
		else
			$ext = $extension;
	}
	

}
header('Content-Type: application/xml; charset=utf-8');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<extension>$ext</extension>\n";







