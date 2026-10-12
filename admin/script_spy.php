<?php


if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false){ // LOCAL
    $rootpath   = "d:/wamp/www/ma-cave-a-vin/"; 
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test2015') !== false) {
    $rootpath   = "/home/ma-cave-a-vin-2015/";
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test') !== false) {
    $rootpath   = $_SERVER['DOCUMENT_ROOT']."/";
} else {
    $rootpath   = "/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
	$_SERVER['SERVER_NAME']='';
}

ini_set('display_errors',1);

$host="www.ma-cave-a-vin.fr" ;
$nostat=1;
include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."class/mysql.php");
include_once($rootpath."class/email.php");
include_once($rootpath."admin/spy.class.php");

// echo "on commence";


$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

$spy=new spy("cc","ab48711722774f256b2059bafa547c16");
$spy->request("src");
$data=$spy->getcsv();
foreach($data as $fields){
	$site_tmp=strtolower($fields[1]);
	$spydata[$fields[0]][$site_tmp]=$fields;
}

$spy=new spy("cc","ab48711722774f256b2059bafa547c16");
$history=$DB_site->query_first("SELECT MAX(timestamp) FROM history");
// echo "depuis :".$history[0];
// DELETE FROM  `history` WHERE  `timestamp` >1522879200
// $history[0]="1522879200";
$servertime=$spy->request("lastdata");
if($servertime!=false){
	if($servertime>$history[0]){
		$data=$spy->request("db","&fromdate=".intval($history[0]));
		$result=savedata($data);
		if($result)
			$serverinfo="Données synchronisées";
		else
			$serverinfo="Erreur de synchronisation ".mysqli_error($DB_site->link_id);
	}else
		$serverinfo="Données à jour";
}else
	$serverinfo="Serveur non connecté";



/*$data="";
$f=fopen("http://www.ma-cave-a-vin.fr/admin/export/export_spy_ooo.php","r");
while(!feof($f)) $data.=fread($f,2048);
fclose($f);*/

$mail=new email();
$mail->expediteur="info@ma-cave-a-vin.fr";
$mail->expname="Ma Cave à Vin";
$mail->destinataire="contact@ma-cave-a-vin.fr";
$mail->to="webmaster@ma-cave-a-vin.fr";
$mail->subject="SPY";
$mail->body=date("d/M/Y")." : ".$serverinfo;
// $mail->attachstring($data,"spy_".date("Y-m-d").".ods","application/vnd.oasis.opendocument.spreadsheet");
$mail->sendmail();

function savedata($data){
	global $spydata, $DB_site, $rootpath;
	$i=0;
    $sql=explode(";",$data);
    foreach($sql as $line){
		if($line!=""){
			$DB_site->query($line);
			// echo $line."<br>";

			// if($i<=10){ // on limite à 10 pour nos tests
				$insert_id=$DB_site->insert_id();
				if($insert_id!=0){
					$insert_data=$DB_site->query_first("SELECT * FROM history WHERE id='".$insert_id."' ");
					$site=strtolower($insert_data['site']);
					$artcode=$insert_data['artcode'];
					
					$last_data=$DB_site->query_first("	SELECT * FROM history 
														WHERE artcode='".$artcode."' 
														AND site='".$insert_data['site']."' 
														AND id!='".$insert_data['id']."' 
														ORDER BY timestamp DESC LIMIT 1 ");
					
					if($insert_data['price']!=$last_data['price']){
						// echo $insert_data['artcode']." > (".$site.") ".$insert_data[price];
						// echo " =? ".$last_data['price'];
						// echo "yes<br>";
						$i++;
						$url=$spydata[$artcode][$site][2];
						// on génére un PDF ...
						$article=$DB_site->query_first("SELECT artid FROM article WHERE artcode='".$artcode."' ");
						if($article[artid]!=""){
							$DB_site->query("	INSERT INTO history_pdf
												(historyid, artid, dateline, site, url, price, generated)
												VALUES
												(".$insert_id.", ".$article[artid].", ".$insert_data['timestamp'].", '".$insert_data['site']."', '".$url."', ".$insert_data['price'].", '0') ");
						}
						/*
						$out_dir=$rootpath."configurations/www.ma-cave-a-vin.fr/pdf/".$article[artid];
						if (!file_exists($out_dir)) {
							mkdir($out_dir, 0777, true);
						}
						try{
							
							$out_file = $out_dir."/".date("Ymd-His", $insert_data['timestamp'])."-".str_replace(" ","-", $site).".pdf";
							$url=$spydata[$artcode][$site][2];
							// create the API client instance
							$client = new \Pdfcrowd\HtmlToPdfClient("acilyon", "ffe78c425ee529804ccff730d594e949");
							// run the conversion and write the result to a file
							$client->setPageHeight(-1);
							$client->setBlockAds(true);
							$client->setInitialZoomType('fit-width');
							$client->convertUrlToFile("https://www.darty.com/nav/achat/gros_electromenager/cave_vin-cave/cave_multi-temperatures/thomson_millesime_99.html", $out_file);
							echo "PDF ".$out_file." ajouté pour URL ".$url."<br>";
						} catch(\Pdfcrowd\Error $why) {
							// report the error to the standard error stream
							echo "Pdfcrowd Error: ".$why."\n";
						}
						*/
					//} else {
						// echo "pas de changement de prix pour cet article<br>";
					}
				}
			// }
		}
    }
	// echo "il y a eut ".$i." changement de prix.";
    return true;
}

?>