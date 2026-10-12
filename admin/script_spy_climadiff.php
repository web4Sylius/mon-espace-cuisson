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


$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

$echo="<h1>Spy Ma Cave A Vin / Frio</h1>
	Liste des produits sur les sites du Groupe Frio &agrave; prix <span style=\"color:red;font-weight:bold;\">inf&eacute;rieur</span> ou <span style=\"font-style:italic;\">&eacute;gal</span> au prix MCAV :
	<table cellspacing=0 cellpading=0 border=1>
		<tr>
			<th>artcode</th>
			<th>Ref. Fab.</th>
			<th>PV TTC MCAV</th>
			<th>Site</th>
			<th>PV TTC site</th>
		</tr>";
$timestamp_mini=mktime(0,0,0,date("n"),date("j")-1); // depuis hier à 0:00.
$articles=$DB_site->query("	SELECT a.artcode, a.artcode_eurocave, a.artid, a.prix FROM article AS a
							INNER JOIN article_marque AS am USING (artid)
							INNER JOIN marque AS m USING (marqueid)
							WHERE m.marqueid IN (6, 12, 13, 14, 16, 20, 21, 22, 67)
							AND a.active='1' AND a.commandable='1' ");
while($article=$DB_site->fetch_array($articles)){
	$ligne="";
	$historys=$DB_site->query("	SELECT artcode, timestamp, price, site FROM history
								WHERE artcode='".$article[artcode]."'
								AND site IN('climadiff','lasommeliere','vinokado')
								AND timestamp>".$timestamp_mini." ");
	while($history=$DB_site->fetch_array($historys)){
		if($history[price]<=$article[prix]){
			if($history[price]<$article[prix]){
				$color="color:red;font-weight:bold";
			} else {
				$color="font-style:italic";
			}
			$ligne="<tr style=\"".$color."\"><td>".$article[artcode]."</td><td>".$article[artcode_eurocave]."</td><td align=\"right\">".number_format($article[prix], 2, ',', ' ')." &euro;</td><td>".$history[site]."</td><td align=\"right\">".number_format($history[price], 2, ',', ' ')." &euro;</td></tr>";
		}
	}
	$echo.=$ligne;
}

$echo.="</table>";

$echo.="<br><br>Liste des produits sur les sites du Groupe Frio &agrave; prix <span style=\"\">l&eacute;g&eacute;rement sup&eacute;rieur (<10%)</span> au prix MCAV :
	<table cellspacing=0 cellpading=0 border=1>
		<tr>
			<th>artcode</th>
			<th>Ref. Fab.</th>
			<th>PV TTC MCAV</th>
			<th>Site</th>
			<th>PV TTC site</th>
		</tr>";

$articles=$DB_site->query("	SELECT a.artcode, a.artcode_eurocave, a.artid, a.prix FROM article AS a
							INNER JOIN article_marque AS am USING (artid)
							INNER JOIN marque AS m USING (marqueid)
							WHERE m.marqueid IN (6, 12, 13, 14, 16, 20, 21, 22, 67) 
							AND a.active='1' AND a.commandable='1' ");
while($article=$DB_site->fetch_array($articles)){
	$ligne="";
	$historys=$DB_site->query("	SELECT artcode, timestamp, price, site FROM history
								WHERE artcode='".$article[artcode]."'
								AND site IN('climadiff','lasommeliere','vinokado')
								AND timestamp>".$timestamp_mini." ");
	while($history=$DB_site->fetch_array($historys)){
		if($history[price]<=$article[prix]*1.1 && $history[price]>$article[prix]){
			$color="";
			$ligne="<tr style=\"".$color."\"><td>".$article[artcode]."</td><td>".$article[artcode_eurocave]."</td><td align=\"right\">".number_format($article[prix], 2, ',', ' ')." &euro;</td><td>".$history[site]."</td><td align=\"right\">".number_format($history[price], 2, ',', ' ')." &euro;</td></tr>";
		}
	}
	$echo.=$ligne;
}

$echo.="</table>";


echo $echo;

$mail=new email();
$mail->expediteur="contact@ma-cave-a-vin.fr";
$mail->expname="Ma Cave à Vin";
$destinataire="marketing1@ma-cave-a-vin.fr";
$mail->destinataire=$destinataire;
$to=array("contact@ma-cave-a-vin.fr");
$mail->body=$echo;
$mail->subject="Spy Ma Cave à Vin / FRIO";
$mail->to=array_unique($to);
$mail->sendmail();



$echo="<h1>Spy Ma Cave A Vin / Liebherr</h1>
	Liste des produits sur les sites du Groupe Liebherr &agrave; prix <span style=\"color:red;font-weight:bold;\">inf&eacute;rieur</span> ou <span style=\"font-style:italic;\">&eacute;gal</span> au prix MCAV :
	<table cellspacing=0 cellpading=0 border=1>
		<tr>
			<th>artcode</th>
			<th>Ref. Fab.</th>
			<th>PV TTC MCAV</th>
			<th>Site</th>
			<th>PV TTC site</th>
		</tr>";
$timestamp_mini=mktime(0,0,0,date("n"),date("j")-1); // depuis hier à 0:00.
$articles=$DB_site->query("	SELECT a.artcode, a.artcode_eurocave, a.artid, a.prix FROM article AS a
							INNER JOIN article_marque AS am USING (artid)
							INNER JOIN marque AS m USING (marqueid)
							WHERE m.marqueid IN (9)
							AND a.active='1' AND a.commandable='1' ");
while($article=$DB_site->fetch_array($articles)){
	$ligne="";
	$historys=$DB_site->query("	SELECT artcode, timestamp, price, site FROM history
								WHERE artcode='".$article[artcode]."'
								AND site IN('liebherr-electromenager')
								AND timestamp>".$timestamp_mini." ");
	while($history=$DB_site->fetch_array($historys)){
		if($history[price]<=$article[prix]){
			if($history[price]<$article[prix]){
				$color="color:red;font-weight:bold";
			} else {
				$color="font-style:italic";
			}
			$ligne="<tr style=\"".$color."\"><td>".$article[artcode]."</td><td>".$article[artcode_eurocave]."</td><td align=\"right\">".number_format($article[prix], 2, ',', ' ')." &euro;</td><td>".$history[site]."</td><td align=\"right\">".number_format($history[price], 2, ',', ' ')." &euro;</td></tr>";
		}
	}
	$echo.=$ligne;
}

$echo.="</table>";

$echo.="<br><br>Liste des produits sur les sites du Groupe Liebherr &agrave; prix <span style=\"\">l&eacute;g&eacute;rement sup&eacute;rieur (<10%)</span> au prix MCAV :
	<table cellspacing=0 cellpading=0 border=1>
		<tr>
			<th>artcode</th>
			<th>Ref. Fab.</th>
			<th>PV TTC MCAV</th>
			<th>Site</th>
			<th>PV TTC site</th>
		</tr>";
$timestamp_mini=mktime(0,0,0,date("n"),date("j")-1); // depuis hier à 0:00.
$articles=$DB_site->query("	SELECT a.artcode, a.artcode_eurocave, a.artid, a.prix FROM article AS a
							INNER JOIN article_marque AS am USING (artid)
							INNER JOIN marque AS m USING (marqueid)
							WHERE m.marqueid IN (9)
							AND a.active='1' AND a.commandable='1' ");
while($article=$DB_site->fetch_array($articles)){
	$ligne="";
	$historys=$DB_site->query("	SELECT artcode, timestamp, price, site FROM history
								WHERE artcode='".$article[artcode]."'
								AND site IN('liebherr-electromenager')
								AND timestamp>".$timestamp_mini." ");
	while($history=$DB_site->fetch_array($historys)){
		if($history[price]<=$article[prix]*1.1 && $history[price]>$article[prix]){
			$color="";
			$ligne="<tr style=\"".$color."\"><td>".$article[artcode]."</td><td>".$article[artcode_eurocave]."</td><td align=\"right\">".number_format($article[prix], 2, ',', ' ')." &euro;</td><td>".$history[site]."</td><td align=\"right\">".number_format($history[price], 2, ',', ' ')." &euro;</td></tr>";
		}
	}
	$echo.=$ligne;
}

$echo.="</table>";

echo $echo;


$mail=new email();
$mail->expediteur="contact@ma-cave-a-vin.fr";
$mail->expname="Ma Cave à Vin";
$destinataire="marketing1@ma-cave-a-vin.fr";
$mail->destinataire=$destinataire;
$to=array("contact@ma-cave-a-vin.fr");
$mail->body=$echo;
$mail->subject="Spy Ma Cave à Vin / Liebherr";
$mail->to=array_unique($to);
$mail->sendmail();



$echo="<h1>Spy Ma Cave A Vin / DOMETIC</h1>
	Liste des produits sur les sites du Groupe DOMETIC &agrave; prix <span style=\"color:red;font-weight:bold;\">inf&eacute;rieur</span> ou <span style=\"font-style:italic;\">&eacute;gal</span> au prix MCAV :
	<table cellspacing=0 cellpading=0 border=1>
		<tr>
			<th>artcode</th>
			<th>Ref. Fab.</th>
			<th>PV TTC MCAV</th>
			<th>Site</th>
			<th>PV TTC site</th>
		</tr>";
$timestamp_mini=mktime(0,0,0,date("n"),date("j")-1); // depuis hier à 0:00.
$articles=$DB_site->query("	SELECT a.artcode, a.artcode_eurocave, a.artid, a.prix FROM article AS a
							INNER JOIN article_marque AS am USING (artid)
							INNER JOIN marque AS m USING (marqueid)
							WHERE m.marqueid IN (10)
							AND a.active='1' AND a.commandable='1' ");
while($article=$DB_site->fetch_array($articles)){
	$ligne="";
	$historys=$DB_site->query("	SELECT artcode, timestamp, price, site FROM history
								WHERE artcode='".$article[artcode]."'
								AND site IN('dometic')
								AND timestamp>".$timestamp_mini." ");
	while($history=$DB_site->fetch_array($historys)){
		if($history[price]<=$article[prix]){
			if($history[price]<$article[prix]){
				$color="color:red;font-weight:bold";
			} else {
				$color="font-style:italic";
			}
			$ligne="<tr style=\"".$color."\"><td>".$article[artcode]."</td><td>".$article[artcode_eurocave]."</td><td align=\"right\">".number_format($article[prix], 2, ',', ' ')." &euro;</td><td>".$history[site]."</td><td align=\"right\">".number_format($history[price], 2, ',', ' ')." &euro;</td></tr>";
		}
	}
	$echo.=$ligne;
}

$echo.="</table>";

$echo.="<br><br>Liste des produits sur les sites du Groupe DOMETIC &agrave; prix <span style=\"\">l&eacute;g&eacute;rement sup&eacute;rieur (<10%)</span> au prix MCAV :
	<table cellspacing=0 cellpading=0 border=1>
		<tr>
			<th>artcode</th>
			<th>Ref. Fab.</th>
			<th>PV TTC MCAV</th>
			<th>Site</th>
			<th>PV TTC site</th>
		</tr>";
$timestamp_mini=mktime(0,0,0,date("n"),date("j")-1); // depuis hier à 0:00.
$articles=$DB_site->query("	SELECT a.artcode, a.artcode_eurocave, a.artid, a.prix FROM article AS a
							INNER JOIN article_marque AS am USING (artid)
							INNER JOIN marque AS m USING (marqueid)
							WHERE m.marqueid IN (10)
							AND a.active='1' AND a.commandable='1' ");
while($article=$DB_site->fetch_array($articles)){
	$ligne="";
	$historys=$DB_site->query("	SELECT artcode, timestamp, price, site FROM history
								WHERE artcode='".$article[artcode]."'
								AND site IN('dometic')
								AND timestamp>".$timestamp_mini." ");
	while($history=$DB_site->fetch_array($historys)){
		if($history[price]<=$article[prix]*1.1 && $history[price]>$article[prix]){
			$color="";
			$ligne="<tr style=\"".$color."\"><td>".$article[artcode]."</td><td>".$article[artcode_eurocave]."</td><td align=\"right\">".number_format($article[prix], 2, ',', ' ')." &euro;</td><td>".$history[site]."</td><td align=\"right\">".number_format($history[price], 2, ',', ' ')." &euro;</td></tr>";
		}
	}
	$echo.=$ligne;
}

$echo.="</table>";

echo $echo;


$mail=new email();
$mail->expediteur="contact@ma-cave-a-vin.fr";
$mail->expname="Ma Cave à Vin";
$destinataire="marketing1@ma-cave-a-vin.fr";
$mail->destinataire=$destinataire;
$to=array("contact@ma-cave-a-vin.fr");
$mail->body=$echo;
$mail->subject="Spy Ma Cave à Vin / DOMETIC";
$mail->to=array_unique($to);
$mail->sendmail();



?>