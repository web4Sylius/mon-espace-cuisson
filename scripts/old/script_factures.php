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

$ajd = date ("Y-m-01") ; // date du jour au format 'aaaa-mm-jj'
$factures=$DB_site->query("select bd.domaine, bd.clientid from bou_domaine bd, bou_client bc where bc.clientid = bd.clientid 
							and bc.demo = '0' and tablename != ''");
while ($facture=$DB_site->fetch_array($factures)) 
	{
	$domaine = $facture[domaine] ;
	$clientid = $facture[clientid] ;
	$existe_fact=$DB_site->query_first("select * from facture_location where clientid = '$clientid' and  datefacture = '$ajd' ");
	if ($existe_fact[factureid] == '')
		{
		include "/home/boutique/www/configurations/$domaine/config.php"; 
		$passe = ""; 
		$chars = array("2","3","4","5","6","7","8","9",
					   "a","b","c","d","e","f","g","h","j","k","m","n","p","q","r","s","t","u","v","w","x","y","z",
					   "A","B","C","D","E","F","G","H","J","K","L","M","N","P","Q","R","S","T","U","V","W","X","Y","Z");
		$count = count($chars) - 1; 
		srand((float) microtime()*1000000);
		for ($i = 0; $i < 10; $i++) 
			$passe .= $chars[rand(0, $count)] ;
		$nouvelle_facture = $DB_site->query("INSERT INTO facture_location (clientid, factureid, montanttotal, tva, datefacture, 
		passefacture, etatfacture) VALUES ('$clientid', '', '0', '0', '$ajd', '$passe', '0')");
		$facture_en_cours = $DB_site->query_first("select factureid from facture_location where clientid = '$clientid' and datefacture = '$ajd'") ;
		$factureid = $facture_en_cours[factureid] ;
		$montant_total = 50 ;
		$i = 0 ;
		while ($i<count($modules))
			{
			$mod = $modules[$i] ;
			$module_en_cours = $DB_site->query_first("select prix from modules_boutique where modules_boutiqueid = '$mod'") ;
			$module_prix = $module_en_cours [prix] ;
			$nouvelle_ligne_facture = $DB_site->query("INSERT INTO ligne_facture_location (lignefactureid, factureid, moduleid, prix, tva)
			VALUES ('', '$factureid', '$mod', '$module_prix', '19.6')");
			$montant_total += $module_prix ; // calcul du montant avec la somme des prix des modules loués
			$i++ ;
			}
		$tva = $montant_total * 0.196 ;
		$modif_facture = $DB_site->query("UPDATE facture_location SET montanttotal = '$montant_total', tva = '$tva' WHERE factureid = '$factureid'");
		}
	}

?>