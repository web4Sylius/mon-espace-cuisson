<?php

require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
header('Content-Type: text/html; charset=utf-8');
set_time_limit(20);

$i=0;
$liste_etats_encours=array(1,2,3,7,8,9);

/*
error_reporting(2047);
ini_set('display_errors', 1); 
*/

/*
// premiere partie : on regarde le dernier suivi client et on met le client dans le même état.
$sql = " SELECT MAX(scs.id) AS id FROM suivi_client scs WHERE rappel>0 GROUP BY userid "; 
echo $sql."<br>";
$requete = $DB_site2->query($sql);
while($s = $DB_site2->fetch_array($requete)){
	$r = $DB_site2->query_first("SELECT * FROM suivi_client INNER JOIN utilisateur u USING(userid) WHERE id='".$s['id']."' ");
	$perdu = $DB_site2->query_first("SELECT * FROM suivi_client WHERE userid='".$r['userid']."' ORDER BY date DESC LIMIT 1 ");
	if($perdu['etat']==0 ){
		echo $r['userid']." => etat = ".$perdu['etat']."<br>";
		$sql = "UPDATE utilisateur SET projet_etatid='6' WHERE userid = '".$r['userid']."' "; 
		$DB_site2->query($sql);
	}
	if($perdu['etat']==5 ){
		echo $r['userid']." => etat = ".$perdu['etat']."<br>";
		$sql = "UPDATE utilisateur SET projet_etatid='5' WHERE userid = '".$r['userid']."' "; 
		$DB_site2->query($sql);
	}
	$i++;
}
*/


/*
// deuxième partie : on regarde le dernier suivi client et on met le rappel dans le projet s'il existe
$sql = " SELECT MAX(scs.id) AS id FROM suivi_client scs WHERE rappel>0 GROUP BY userid "; 
$requete = $DB_site2->query($sql);
while($s = $DB_site2->fetch_array($requete)){
	$r = $DB_site2->query_first("SELECT * FROM suivi_client INNER JOIN utilisateur u USING(userid) WHERE id='".$s['id']."' ");
	$user = $DB_site2->query_first("SELECT * FROM utilisateur WHERE userid='".$r['userid']."' ");
	if($r[projetid]!=0){
		// on met à jour la date de rappel du projet
		$sql="UPDATE projet SET rappel='".$r['rappel']."' WHERE projetid='".$r['projetid']."' ";
		echo $sql."<br>";
		 $DB_site2->query($sql);
	} else {
		// on met à jour la date de rappel du client
		$sql="UPDATE utilisateur SET projet_rappel='".$r['rappel']."' WHERE userid='".$r['userid']."' ";
		echo $sql."<br>";
		 $DB_site2->query($sql);
	}
	$i++;
}
*/


/*
// troisième partie : on regarde les projets et on met à jour le dernier rappel qu'on trouve
$sql = " SELECT * from projet WHERE etatid IN(1,2,3,4,7,8) "; 
// $sql = " SELECT * from projet WHERE projetid=51488 "; 
$requete = $DB_site2->query($sql);
while($s = $DB_site2->fetch_array($requete)){
	$user = $DB_site2->query_first("SELECT * FROM utilisateur WHERE userid='".$s['userid']."' ");
	if($user['utilisateur_admin']==0){
		$r = $DB_site2->query_first("SELECT MAX(id) AS id FROM suivi_client WHERE rappel>0  and projetid='".$s['projetid']."' ");
		if($r[id]==""){ // aucun suivi client
			echo "user : ".$s['userid']." : aucun suivi<br>";
		} else {
			$t = $DB_site2->query_first("SELECT rappel FROM suivi_client WHERE id='".$r['id']."' ");
			// on met à jour la date de rappel du projet
			$sql="UPDATE projet SET rappel='".$t['rappel']."' WHERE projetid='".$s['projetid']."' ";
			echo $sql."<br>";
			$DB_site2->query($sql);
		}
		$i++;
	}
}
*/


/*
// quatrième partie : on regarde les projets et on met à jour l'étatid qu'on trouve
$sql = " SELECT * from projet "; 
$requete = $DB_site2->query($sql);
while($s = $DB_site2->fetch_array($requete)){
	$user = $DB_site2->query_first("SELECT * FROM utilisateur WHERE userid='".$s['userid']."' ");
	if($user['utilisateur_admin']==0){
		$r = $DB_site2->query_first("SELECT MAX(id) AS id FROM suivi_client WHERE projetid='".$s['projetid']."' ");
		if($r[id]==""){ // aucun suivi client
			// echo "projet : ".$s['projetid']." (user : ".$s['userid'].") : aucun suivi<br>";
		} else {
			$t = $DB_site2->query_first("SELECT etat FROM suivi_client WHERE id='".$r['id']."' ");
			// on met à jour l'état du projet
			if($t['etat']==0 ){ // perdu
				// echo $s['projetid']." => etat = ".$t['etat']."<br>";
				$sql = "UPDATE projet SET etatid='6' WHERE projetid = '".$s['projetid']."' "; 
				echo $sql."<br>";
				$DB_site2->query($sql);
			}
			if($t['etat']==5 ){ // en commande
				// echo $s['projetid']." => etat = ".$t['etat']."<br>";
				$sql = "UPDATE projet SET etatid='5' WHERE projetid = '".$s['projetid']."' "; 
				echo $sql."<br>";
				$DB_site2->query($sql);
			}
		}
		$i++;
	}
}
*/

/*
// cinquième partie : on passe les commandes sans suivi en état "en commande"
$sql = " SELECT * from utilisateur WHERE utilisateur_admin='0' AND projet_etatid='1' "; // tous les utilisateurs dans leur état par défaut
$requete = $DB_site2->query($sql);
while($s = $DB_site2->fetch_array($requete)){

	$r = $DB_site2->query_first("SELECT COUNT(*) FROM facture WHERE valide IN(1,5,7,8) AND userid='".$s['userid']."' ");
	if($r[0]=="0"){ // aucune commande passée
		// echo "projet : ".$s['projetid']." (user : ".$s['userid'].") : aucun suivi<br>";
	} else {
		// on regarde si ils ont aucun projet
		$p = $DB_site2->query_first("SELECT COUNT(*) FROM projet WHERE userid='".$s['userid']."' ");
		if($p[0]==0){ // aucun projet, on peux donc le passer en état 'en commande'
			$sql = "UPDATE utilisateur SET projet_etatid='5' WHERE userid = '".$s['userid']."' "; 
			echo $sql."<br>";
			$DB_site2->query($sql);
		}
	}
	$i++;
}
*/

/*
// sixieme partie : on passe les prospects qui sont pointé en perdus en état "perdus"
$sql = " SELECT * from utilisateur WHERE utilisateur_admin='0' AND projet_etatid='1' "; // tous les utilisateurs dans leur état par défaut
$requete = $DB_site2->query($sql);
while($s = $DB_site2->fetch_array($requete)){

	$r = $DB_site2->query_first("SELECT COUNT(*) FROM facture WHERE valide IN(1,5,7,8) AND userid='".$s['userid']."' ");
	if($r[0]=="0"){ // aucune commande passée
		// on regarde si ils ont aucun projet
		$p = $DB_site2->query_first("SELECT COUNT(*) FROM projet WHERE userid='".$s['userid']."' ");
		if($p[0]==0){ // aucun projet, on peux donc le passer en état 'perdu'
			$sql = " SELECT etat FROM suivi_client WHERE userid='".$s['userid']."' ORDER BY date DESC LIMIT 1"; 
			$q = $DB_site2->query_first($sql);
			if($q['etat']==0 ){ // perdu
				// echo $s['projetid']." => etat = ".$t['etat']."<br>";
				$sql = "UPDATE utilisateur SET projet_etatid='6' WHERE userid = '".$s['userid']."' "; 
				echo $sql."<br>";
				$DB_site2->query($sql);
				$i++;
			}
		}
	}
}
*/

/*
// septieme partie : on passe les prospects qui sont en 'conseil:non' et 'newsletter:non' en perdus (s'ils sont dans un état par défaut et qu'aucun commercial les a contacté)
$sql = " SELECT * from utilisateur WHERE utilisateur_admin='0' AND projet_etatid='1' AND conseils='non' AND allowemail='0' "; // tous les utilisateurs dans leur état par défaut
$requete = $DB_site2->query($sql);
while($s = $DB_site2->fetch_array($requete)){

	$r = $DB_site2->query_first("SELECT COUNT(*) FROM facture WHERE valide IN(1,5,7,8) AND userid='".$s['userid']."' ");
	if($r[0]=="0"){ // aucune commande passée
		// on regarde si ils ont aucun projet
		$p = $DB_site2->query_first("SELECT COUNT(*) FROM projet WHERE userid='".$s['userid']."' ");
		if($p[0]==0){ // aucun projet, on peux donc le passer en état 'perdu'
			// echo $s['projetid']." => etat = ".$t['etat']."<br>";
			$sql = " SELECT COUNT(*) FROM suivi_client WHERE userid='".$s['userid']."' AND commercial!='' "; 
			$q = $DB_site2->query_first($sql);
			if($q[0]==0 ){ // aucun suivi
				$sql = "UPDATE utilisateur SET projet_etatid='6' WHERE userid = '".$s['userid']."' "; 
				// echo $sql."<br>";
				// $DB_site2->query($sql);
				$i++;
			} else {
				echo "le user ".$s['userid']." a un suivi commercial ...<br>";
			}
		}
	}
}
*/

/*
// huitieme partie : on passe les prospects qui sont pointé en perdus en état "perdus"
$sql = " SELECT * from utilisateur WHERE utilisateur_admin='0' AND projet_etatid='1' "; // tous les utilisateurs dans leur état par défaut
$requete = $DB_site2->query($sql);
while($s = $DB_site2->fetch_array($requete)){

	$r = $DB_site2->query_first("SELECT COUNT(*) FROM facture WHERE valide IN(1,5,7,8) AND userid='".$s['userid']."' ");
	if($r[0]=="0"){ // aucune commande passée
		// on regarde si ils ont aucun projet
		$p = $DB_site2->query_first("SELECT COUNT(*) FROM projet WHERE userid='".$s['userid']."' ");
		if($p[0]==1){ // 1 seul projet, on peux donc le passer en état 'perdu'
			$sql = " SELECT etat FROM suivi_client WHERE userid='".$s['userid']."' ORDER BY date DESC LIMIT 1"; 
			$q = $DB_site2->query_first($sql);
			if($q['etat']==0 ){ // perdu
				// echo $s['projetid']." => etat = ".$t['etat']."<br>";
				$sql = "UPDATE utilisateur SET projet_etatid='6' WHERE userid = '".$s['userid']."' "; 
				echo $sql."<br>";
				$DB_site2->query($sql);
				$sql = "UPDATE projet SET etatid='6' WHERE userid = '".$s['userid']."' "; 
				echo $sql."<br>";
				$DB_site2->query($sql);
				$i++;
			}
		}
	}
}
*/

/*
// neuvieme partie : on passe les projet en perdus pour les clients qui sont pointé en perdus
$sql = " SELECT * from utilisateur WHERE utilisateur_admin='0' AND projet_etatid='6' "; // tous les utilisateurs dans leur état par défaut
$requete = $DB_site2->query($sql);
while($s = $DB_site2->fetch_array($requete)){
	$r = $DB_site2->query_first("SELECT COUNT(*) FROM facture WHERE valide IN(1,5,7,8) AND userid='".$s['userid']."' ");
	if($r[0]=="0"){ // aucune commande passée
		// on regarde si il y a un projet
		$p = $DB_site2->query_first("SELECT COUNT(*) FROM projet WHERE userid='".$s['userid']."' ");
		if($p[0]==1){ // 1 seul projet, on peux donc le passer en état 'perdu'
			$sql = " SELECT etat FROM suivi_client WHERE userid='".$s['userid']."' ORDER BY date DESC LIMIT 1"; 
			$q = $DB_site2->query_first($sql);
			if($q['etat']==0 ){ // perdu
				// echo $s['projetid']." => etat = ".$t['etat']."<br>";
				// $sql = "UPDATE utilisateur SET projet_etatid='6' WHERE userid = '".$s['userid']."' "; 
				// echo $sql."<br>";
				// $DB_site2->query($sql);
				$sql = "UPDATE projet SET etatid='6' WHERE userid = '".$s['userid']."' "; 
				echo $sql."<br>";
				$DB_site2->query($sql);
				$i++;
			}
		}
	}
}
*/



/*
// dixième partie : on passe les commandes en état "en commande" dans le suivi en état "en commande" dans le user
$sql = " SELECT * from utilisateur WHERE utilisateur_admin='0' AND projet_etatid='1' "; // tous les utilisateurs dans leur état par défaut
$requete = $DB_site2->query($sql);
while($s = $DB_site2->fetch_array($requete)){
	$sql = " SELECT etat FROM suivi_client WHERE userid='".$s['userid']."' ORDER BY date DESC LIMIT 1"; 
	$q = $DB_site2->query_first($sql);
	if($q['etat']==5 ){ // en commande
		$sql = "UPDATE utilisateur SET projet_etatid='5' WHERE userid = '".$s['userid']."' "; 
		echo $sql."<br>";
		$DB_site2->query($sql);
		$i++;
	}
}
*/

/*
// onzieme partie : on passe les client en état "perdu" s'ils sont en conseil:non et qu'ils ont aucun suivi et qu'ils datent de plus de 48h00
$sql = " SELECT * from utilisateur WHERE utilisateur_admin='0' AND projet_etatid='1' AND conseils='non' "; // tous les utilisateurs dans leur état par défaut
$requete = $DB_site2->query($sql);
while($s = $DB_site2->fetch_array($requete)){
	$sql = " SELECT COUNT(*) FROM suivi_client WHERE userid='".$s['userid']."' AND commercial!='' "; 
	$q = $DB_site2->query_first($sql);
	if($q[0]==0 ){ // perdu
		$sql = "UPDATE utilisateur SET projet_etatid='0' WHERE userid = '".$s['userid']."' "; 
		echo $sql."<br>";
		$DB_site2->query($sql);
		$i++;
	}
}
*/



/*
// douzieme partie : on passe les client en état "perdu" s'ils sont en conseil:non et qu'ils ont aucun suivi et qu'ils datent de plus de 48h00
$sql = " SELECT u.userid as userid, u.*, p.* FROM utilisateur AS u INNER JOIN projet AS p on(u.userid=p.userid) 
				WHERE p.rappel=0
				AND u.utilisateur_admin='0'
				AND p.etatid IN(1,2,3,4,7,8,9) ";
$requete = $DB_site2->query($sql);
while($s = $DB_site2->fetch_array($requete)){
	$sql = " SELECT COUNT(*) FROM suivi_client WHERE userid='".$s['userid']."' AND commercial!='' "; 
	$q = $DB_site2->query_first($sql);
	if($q[0]==0 ){ // perdu
		$sql = "UPDATE utilisateur SET projet_etatid='0' WHERE userid = '".$s['userid']."' "; 
		echo $sql."<br>";
		$DB_site2->query($sql);
		$i++;
	}
}
*/



// treizieme partie : on regarde tous ceux qu'on trouve en suivi sans rappel en projet
$sql = "SELECT u.userid as u_userid, u.*, p.* FROM utilisateur AS u INNER JOIN projet AS p on(u.userid=p.userid) 
		WHERE u.utilisateur_admin='0'  ";
$requete = $DB_site2->query($sql);
while($projet = $DB_site2->fetch_array($requete)){
	$rappel_sql=$DB_site2->query_first("SELECT id, rappel, pour FROM suivi_client WHERE userid='".$projet[u_userid]."' AND projetid='".$projet[projetid]."' AND rappel>0 ORDER BY id DESC LIMIT 1 ");
	if($rappel_sql[id]!=""){
		$rappel_txt=date('d/m/Y', $rappel_sql['rappel']);
		if($projet[rappel]==0){
			$sql = "UPDATE projet SET rappel='".$rappel_sql[rappel]."', rappel_commercial='".$rappel_sql[pour]."' WHERE projetid = '".$projet[projetid]."' "; 
			echo $sql."<br>";
			$DB_site2->query($sql);
			$i++;
		}
	}
}
//
echo "maj : $i<br>";

?>