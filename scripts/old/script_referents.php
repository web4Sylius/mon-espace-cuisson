<?php
set_time_limit(600) ; 

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

$boutiques=$DB_site->query("SELECT distinct(domaine) FROM bou_domaine bd, bou_client bc where  bc.clientid = bd.clientid 
							and tablename != '' and bc.username != 'pretaporter' and bc.username != 'demimesure' 
							and bc.username != 'surmesure' and bc.demo != '3'") ;

while ($boutique=$DB_site->fetch_array($boutiques))
	{
	include "/home/boutique/www/configurations/$boutique[domaine]/config.php";
	$DB_site2=new DB_Sql;
	$DB_site2->appname='boutique';
	$DB_site2->appshortname='boutique';
	$DB_site2->database=$DBname;
	$DB_site2->server=$db_server;
	$DB_site2->user=$DBusername;
	$DB_site2->password=$DBpassword;
	$DB_site2->connect();
	$DBpassword="";
	
	$finanalyse=101;
	
	// ici : à rajouter !!!! ///
	$chainearechercher="<h2 class=r><a href=\"";
	// ici : à rajouter !!!! ///
	
	$referents=$DB_site2->query("SELECT * FROM referents WHERE (referent='google'  and position='0') ");
	
	while($referent=$DB_site2->fetch_array($referents)) 
		{
		if($referent[motcle]!="t") 
			{
			$i=0;
			$position=0;
			$f="";
			$affpos="";
			$positionbatnight=0;
			while($i<$finanalyse and $positionbatnight==0) 
				{
				$lines = file( "http://www.google.fr/search?q=".urlencode($referent[motcle])."&hl=fr&lr=&start=$i&sa=N" );
				foreach ($lines as $line_num => $line) 
					{
					$f.= $line;
					}
				$j=0;
				while($j<10) 
					{
					$i++;
					$j++;
					
					// nouvelle version ////
					//
					$pos = strpos($f, $chainearechercher );
					$tempof=substr($f,$pos+ strlen($chainearechercher),50);
					//
					// fin nouvelle version //
					
					$monnom = str_replace("www.", "", $host) ;
					if( strlen(str_replace("$monnom","",$tempof))<50 and $positionbatnight!=1 ) 
						{
						$positionbatnight=1;
						$DB_site2->query("UPDATE referents SET position='$i' WHERE referentid='$referent[referentid]' ");
						$ibatnight=$i;
						$j=10;
						}				
					$f=substr($f,$pos+20);
					}
				}
			if($positionbatnight!=0) 
				{
				echo "mot cle : $referent[motcle] | position : ";
				echo "$ibatnight<br><br>";
				} 
			else 
				{
				echo "mot cle : $referent[motcle] | position : ";
				echo "inconnue ($referent[referantid])<br><br>";
				$DB_site2->query("UPDATE referents SET position='100' WHERE referentid='$referent[referentid]' ");
				}
			flush;
			} 
		else 
			{
			echo "mot cle : $referent[motcle] | position : inconnue<br><br>";
			$DB_site2->query("UPDATE referents SET position='100' WHERE referentid='$referent[referentid]' ");
			}
		}
	}

$referent_facture_query = "SELECT referentid, total, nb_fact 
								FROM referents 
							INNER JOIN (SELECT facture.referentid, SUM(lf.subtotal+facture.prixport+facture.prixgestion) as total, COUNT(facture.facturid) as nb_fact FROM facture 
											INNER JOIN 
 												(SELECT SUM(lignefacture.prix) as subtotal, lignefacture.factureid FROM lignefacture GROUP BY factureid) as lf 
											ON(lf.factureid = facture.facturid)
										WHERE valide IN(1,5)
										GROUP BY referentid) as f
							USING(referentid);";
							
$referent_facture_req = $DB_site->query($referent_facture_query);
while($referent_facture = $DB_site->fetch_array($referent_facture_req)) {
	$DB_site->query("UPDATE referents SET nb_commande = '".$referent_facture['nb_fact']."', montant_commande = '".$referent_facture['total']."' WHERE referentid = '".$referent_facture['referentid']."';");
}

?>
