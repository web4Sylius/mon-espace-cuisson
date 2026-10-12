<?php
set_time_limit(1200) ; 

$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
$HTTP_HOST="www.ma-cave-a-vin.fr";
$host="www.ma-cave-a-vin.fr";

$passession = "oui" ;
include_once $rootpath."mysql.php";
include $rootpath."configurations/www.ma-cave-a-vin.fr/config.php";

$DB_site2=new DB_Sql;
$DB_site2->appname='boutique';
$DB_site2->appshortname='boutique';
$DB_site2->database=$DBname;
$DB_site2->server=$db_server;
$DB_site2->user=$DBusername;
$DB_site2->password=$DBpassword;
$DB_site2->connect();
$DBpassword="";

print "<table>";
$requete=$DB_site2->query("SELECT * FROM code_barre");
while($cb=$DB_site2->fetch_array($requete)){
    $o=substr($cb['codebarre'],0,12);
    $new=calcbar($o);
    $new2=calculcodebarre($o);
//     $DB_site2->query("UPDATE code_barre SET codebarre='$new' WHERE codebarreid=".$cb['codebarreid']);
    print "<tr><td>$cb[artid]</td><td>".$cb['codebarre']."</td><td".($cb['codebarre']!=$new?" style=\"color:red;\"":"").">".$new."</td><td>$new2</td></tr>";
}
print "</table>";

function calcbar($code){
       $ch=chunk_split($code);
       $pair=$ch[1]+$ch[3]+$ch[5]+$ch[7]+$ch[9]+$ch[11];
       $pair*=3;
       $impair=$ch[0]+$ch[2]+$ch[4]+$ch[6]+$ch[8]+$ch[10];
       $pimp=$pair+$impair;
       $arrsup=ceil($pimp/10)*10;
       $key=$arrsup-$pimp;
       return $code.$key;
}

function calculcodebarre($calcul1){
	//2.additionner les chiffres du code de rang pair
	$calcul2 = intval(substr($calcul1,0,1)) + intval(substr($calcul1,2,1)) + intval(substr($calcul1,4,1)) + intval(substr($calcul1,6,1)) + intval(substr($calcul1,8,1)) + intval(substr($calcul1,10,1));
	//3.multiplier le résultat par trois
	$calcul3 = $calcul2 * 3;
	//4.additionner les chiffres du code de rang impair
	$calcul4 = intval(substr($calcul1,1,1)) + intval(substr($calcul1,3,1)) + intval(substr($calcul1,5,1)) + intval(substr($calcul1,7,1)) + intval(substr($calcul1,9,1)) + intval(substr($calcul1,11,1));
	//5.additionner les étapes 3 et 4
	$calcul5 = $calcul3 + $calcul4;
	//6.soustraire le résultat de l’étape 5 du multiple de 10 supérieur
	$calcul6 = ((ceil($calcul5/10)) * 10) - $calcul5;
	return $calcul1.$calcul6;
}

?>