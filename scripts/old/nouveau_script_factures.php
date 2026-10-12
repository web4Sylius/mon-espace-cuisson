<?
include "/home/arobases/www/config.php";
include_once "/home/arobases/www/mysql.php";
$DB_site=new DB_Sql;
$DB_site->appname='arobases';
$DB_site->appshortname='arobases';
$DB_site->database=$dbname;
$DB_site->server=$servername;
$DB_site->user=$dbusername;
$DB_site->password=$dbpassword;
$DB_site->connect();
$dbpassword="";
$DB_site->password="";

$ajd = date ("Y-m-01") ; 
$factures=$DB_site->query("select bc.username, bd.clientid, bd.domaine from bou_domaine bd, bou_client bc where bc.clientid = bd.clientid 
							and bc.demo = '0' and tablename != ''");
while ($facture=$DB_site->fetch_array($factures)) 
	{
	$username = $facture[username] ;
	$clientid = $facture[clientid] ;
	$domaine = $facture[domaine] ;
	$existe_fact=$DB_site->query_first("select * from facture_location where clientid = '$clientid' and  datefacture = '$ajd' ");
	if ($existe_fact[factureid] == '')
		{
		$passession = "oui" ;
		include "/home/boutique/www/configurations/$domaine/config.php"; 
		$DB_site2=new DB_Sql;
		$DB_site2->appname='boutique';
		$DB_site2->appshortname='boutique';
		$DB_site2->database=$DBname;
		$DB_site2->server=$db_server;
		$DB_site2->user=$DBusername;
		$DB_site2->password=$DBpassword;
		$DB_site2->connect();
		$DBpassword="";
		$le_mois_dernier = date("m")-1 ;
		$envoiSMS = 0 ;
		if (strlen($le_mois_dernier) == 1)
			{
			$le_mois_dernier = "0".$le_mois_dernier ;
			}
		$smsenvoyes=$DB_site2->query("SELECT date, nb_rcpt FROM sms WHERE date LIKE '$le_mois_dernier%'");
		while ($smsenvoye=$DB_site2->fetch_array($smsenvoyes))
			{
			$envoiSMS++ ;
			}					 
		$passe = ""; 
		$chars = array("2","3","4","5","6","7","8","9",
					   "a","b","c","d","e","f","g","h","j","k","m","n","p","q","r","s","t","u","v","w","x","y","z",
					   "A","B","C","D","E","F","G","H","J","K","L","M","N","P","Q","R","S","T","U","V","W","X","Y","Z");
		$count = count($chars) - 1; 
		srand((float) microtime()*1000000);
		for ($i = 0 ; $i < 10 ; $i++) 
			{
			$passe .= $chars[rand(0, $count)] ;
			}
		$mtt=$DB_site->query_first("select montantlocation from aro_client where username = '$username'") ;
		$locSMS=$DB_site->query_first("select SUM(montant) from location_sms where clientid = '$clientid' and date LIKE '$le_mois_dernier%'") ;
		$locMAILS=$DB_site->query_first("select SUM(montant) from location_mails where clientid = '$clientid' and date LIKE '$le_mois_dernier%'") ;
		$envoiSMS = 0 ;
		$applique_tva=$DB_site->query_first("select tva_applicable from aro_client where username = '$username'");
		if ($applique_tva[0] == '1')
			{
			$tva = ($mtt[0] + $envoiSMS + $locSMS[0] + $locMAILS[0]) * 0.196 ;
			}
		else
			{
			$tva = 0 ;
			}
		$DB_site->query("INSERT INTO facture_location (clientid, montanttotal, envoiSMS, locationSMS, locationMAILS, tva, datefacture, 
		passefacture, etatfacture) VALUES ('$clientid', '$mtt[0]', '$envoiSMS', '$locSMS[0]', '$locMAILS[0]', '$tva', '$ajd', '$passe', '0')");
		}
	}

?>