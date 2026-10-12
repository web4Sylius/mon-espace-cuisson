<?php
set_time_limit(1200) ; 

require "admin_global.php";
include "./header.php";
include "./menu.php";

print "<h1>Nettoyage des prospects sans projet</h1>";

$liste_etats_encours=array(1,2,3,4,7,8,9);

$i=0;
echo "On passe en 'en cde' les prospects qui ont déjà commandé<br>";
$users=$DB_site2->query("SELECT u.userid as u_userid, u.*, p.* FROM utilisateur AS u LEFT JOIN projet AS p on(u.userid=p.userid) 
						WHERE u.projet_etatid IN(".implode(",",$liste_etats_encours).")
						AND u.utilisateur_admin='0'
						AND p.projetid IS NULL 
						AND (SELECT COUNT(*) FROM causeperte WHERE userid=u.userid)=0");
while($s = $DB_site2->fetch_array($users)){
	$r = $DB_site2->query_first("SELECT COUNT(*) FROM facture WHERE valide IN(1,5,7,8) AND userid='".$s[u_userid]."' ");
	if($r[0]>0){ //  commande passée
		$rappel_sql=$DB_site2->query_first("SELECT id, rappel FROM suivi_client WHERE userid='".$s[u_userid]."' AND rappel>0 ORDER BY id DESC LIMIT 1 ");
		if($rappel_sql[id]!=""){
			$rappel_txt=date('d/m/Y', $rappel_sql['rappel']);
		} else {
			echo "<a href=\"/admin/utilisateur.php?action=editer&userid=".$s['u_userid']."\" target=\"_blank\">".$s['nom']." ".$s['prenom']."</a> a déjà commandé et n'a pas de rappel old<br>";
			$i++;
			$sql="UPDATE utilisateur SET projet_etatid='5' WHERE userid='".$s[u_userid]."' ";
			$DB_site2->query($sql);
			// echo $sql;
		}
	}
}
echo "$i résultats<br><br>";
$i=0;
echo "On passe en 'perdu' les prospects qui n'ont pas demandé de conseil<br>";
$users=$DB_site2->query("SELECT u.userid as u_userid, u.*, p.* FROM utilisateur AS u LEFT JOIN projet AS p on(u.userid=p.userid) 
						WHERE u.projet_etatid IN(".implode(",",$liste_etats_encours).")
						AND u.utilisateur_admin='0'
						AND p.projetid IS NULL 
						AND u.conseils='non'
						AND (SELECT COUNT(*) FROM causeperte WHERE userid=u.userid)=0");	
while($s = $DB_site2->fetch_array($users)){
	$rappel_sql=$DB_site2->query_first("SELECT id, rappel FROM suivi_client WHERE userid='".$s[u_userid]."' AND rappel>0 ORDER BY id DESC LIMIT 1 ");
	if($rappel_sql[id]!=""){
		$rappel_txt=date('d/m/Y', $rappel_sql['rappel']);
	} else {
		echo "<a href=\"/admin/utilisateur.php?action=editer&userid=".$s['u_userid']."\" target=\"_blank\">".$s['nom']." ".$s['prenom']."</a> ne veux pas de conseils et n'a pas de rappel old<br>";
		$i++;
		$sql="UPDATE utilisateur SET projet_etatid='6' WHERE userid='".$s[u_userid]."' ";
		 $DB_site2->query($sql);
		// echo $sql;
	}
}
echo "$i résultats<br><br>";

include "./footer.php";

?>