<?
set_time_limit(600) ; 


function getgoogleresult( $cle , $q , $start ){
	$query["key"] = $cle; 
	$query["q"] = $q; 
	$query["start"] = $start; 
	$query["maxResults"] = 10; 
	$query["filter"] = true; 
	$query["restrict"]="countryFR"; 
	$query["safeSearch"]=false; 
	$query["lr"]=""; 
	$query["ie"]="UTF-8"; 
	$query["oe"]="UTF-8"; 
	$server="api.google.com";
	$port=80;
	$errno="";
	$errstr="";
	$soap_data="<?xml version=\"1.0\"?>
	<SOAP-ENV:Envelope xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:SOAP-ENC=\"http://schemas.xmlsoap.org/soap/encoding/\" xmlns:si=\"http://soapinterop.org/xsd\" xmlns:ns6=\"urn:GoogleSearch\"  SOAP-ENV:encodingStyle=\"http://schemas.xmlsoap.org/soap/encoding/\">
	<SOAP-ENV:Body>
	<ns6:doGoogleSearch>
	<key xsi:type=\"xsd:string\">$query[key]</key>
	<q xsi:type=\"xsd:string\">$query[q]</q>
	<start xsi:type=\"xsd:int\">$query[start]</start>
	<maxResults xsi:type=\"xsd:int\">$query[maxResults]</maxResults>
	<filter xsi:type=\"xsd:boolean\">$query[filter]</filter>
	<restrict xsi:type=\"xsd:string\">$query[restrict]</restrict>
	<safeSearch xsi:type=\"xsd:boolean\">$query[safeSearch]</safeSearch>
	<lr xsi:type=\"xsd:string\">$query[lr]</lr>
	<ie xsi:type=\"xsd:string\">$query[ie]</ie>
	<oe xsi:type=\"xsd:string\">$query[oe]</oe>
	</ns6:doGoogleSearch>
	</SOAP-ENV:Body>
	</SOAP-ENV:Envelope>";
	$fp = fsockopen($server, $port, $errno, $errstr);
	$outgoing_payload =  "POST /search/beta2 HTTP/1.0\r\n". 
						 "User-Agent: SOAPx4 v0.5\r\n". 
						 "Host: ".$server."\r\n". 
						 "Content-Type: text/xml\r\nContent-Length: ".strlen($soap_data)."\r\n". 
						 "SOAPAction: \"doGoogleSearch\"\r\n\r\n". 
						 $soap_data; 
	
	if(!fputs($fp, $outgoing_payload, strlen($outgoing_payload))) { 
		return false;
	}
	while($data = fread($fp, 32768)) { 
		$incoming_payload .= $data; 
	} 
	fclose($fp); 
	$pos=strpos($incoming_payload,"<?xml");
	$incoming=substr($incoming_payload,$pos);
	
	$result=array();
	$i=0;
	while($pos=strpos($incoming,"<URL xsi:type=\"xsd:string\">") ){
		$incoming=substr($incoming,$pos+strlen("<URL xsi:type=\"xsd:string\">"));
		//echo $incoming;
		$pos2=strpos($incoming,"</URL>");
		$result[]=substr($incoming,0,$pos2)."<br>";
		$i++;
	}
	return $result;
}


include_once "/home/macavavin/www/mysql.php";
include "/home/macavavin/www/configurations/www.ma-cave-a-vin.fr/config.php";

$DB_site2=new DB_Sql;
$DB_site2->appname='boutique';
$DB_site2->appshortname='boutique';
$DB_site2->database=$DBname;
$DB_site2->server=$db_server;
$DB_site2->user=$DBusername;
$DB_site2->password=$DBpassword;
$DB_site2->connect();
$DBpassword="";

	/////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////// PARAMETRAGES ///////////////////////////
	/////////////////////////////////////////////////////////////////////////////////
	$perpage=5; // nombre de mots clés à requetter
	$maliste = array($host); // les hosts à requetter
	/////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////// PARAMETRAGES ///////////////////////////
	/////////////////////////////////////////////////////////////////////////////////

	$cles = file ("http://www.arobases.fr/google-api/index.php?req=$perpage");
	if($cles[0]=="NOK"){
		echo "plus de clé.";
		exit;
	}
	$cle=$cles[0];
	$nb_par_page = 10;
	echo "host : $host<br>";
	//$google = new GoogleApiSearch ;
	$referents=$DB_site2->query("SELECT * FROM referents  WHERE ( referent='google'  and position='0') and (dateline='".date("Y-m-d",mktime(0,0,0,date("m"),date("d")-1))."') ORDER BY hit DESC LIMIT $perpage ");
	while($referent=$DB_site2->fetch_array($referents)) {
		if($referent[motcle]!="t") {
			echo "-----> $referent[motcle] :";
			$k = 0 ;
			$q = stripslashes($referent[motcle]) ;
			$p=1;
			$trouve=0;
			while ($k < 3) {
				$start = $k * 10 ;
				$result = getgoogleresult($cle, $q, $start);
				if ($result == false) {
					echo "La requête a échoué. ($cle)";
					exit;
				} else {
				//	print_r($result);
					while (list($key, $val) = each($result)) {
						$pos = strpos($val, "/" , 8 );
						if ($pos === false) {
							$resultaturl=substr($val , 7 );
						} else {
							$resultaturl=substr($val , 7 , $pos-7 );
						}
						if (in_array($resultaturl, $maliste)) {
							$trouve=1;
							break(2);
						}
						$p++;
					}
				}
				$k++;
			}
			if ($trouve != 1) {
				$p=$p-1;
				echo "mot cle : $referent[motcle] -> non trouvé<br>";
				$position=100;
			} else {
				echo "mot cle : $referent[motcle] | position : $p<br>";
				$position=$p;
			}
			$DB_site2->query("UPDATE referents SET position='$position' WHERE referentid='$referent[referentid]' ");
		} else {
			$DB_site2->query("UPDATE referents SET position='100' WHERE referentid='$referent[referentid]' ");
		}
	}
?>