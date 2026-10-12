#!/usr/bin/php

<?php

/*$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";

include_once($rootpath."configurations/www.ma-cave-a-vin.fr/config_mysql.php");
include_once($rootpath."class/mysql.php");

include_once($rootpath."admin/email.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();*/

require "admin_global.php";
include "./header.php";
include "./menu.php";

$logfile="/var/log/mail.info";

$regex="/(\w{3} \d{1,2} [0-9:]+) .+ postfix\/smtp\[.+\]: ([0-9A-Z]+): to=(.+), relay=([^,]+), .+ status=([a-z]+) (.+)/";

// Sep 16 09:46:16 ma-cave-a-vin postfix/smtp[22858]: 99F924B0008F: to=<fabienne.bonnet@groupe-heppner.fr>, relay=mail2.groupe-heppner.com[194.98.255.212]:25, delay=0.45, delays=0.09/0/0.25/0.11, dsn=4.0.0, status=deferred (host mail2.groupe-heppner.com[194.98.255.212] said: 450 <fabienne.bonnet@groupe-heppner.fr>: Recipient address rejected: Policy Rejection- Greylisting in action , Please try 2 mn later  (in reply to RCPT TO command))

$old=isset($_GET['old']) ? intval($_GET['old']) : 12;


$mailadmin=array();
$req=$DB_site2->query("SELECT username,mailFR FROM admin_contact WHERE site_code='base'");
while($user=$DB_site2->fetch_array($req)){
    $mailadmin[$user['username']]=$user['mailFR'];
}

$timelimit=new DateTime($old." hour ago");

$logdata=file_get_contents($logfile);

preg_match_all($regex,$logdata,$m,PREG_SET_ORDER);

$messageids=array();

foreach($m as $mdata){
    if($mdata[5]!="sent"){
	$d=new DateTime($mdata[1]);

// 	var_dump($mdata);
	if($d>$timelimit && !in_array($mdata[2],$messageids)){
	    $destinataire=trim($mdata[3]," <>");
	    $date=$d->format("d/m/Y H:i:s");
	    
	    $from=$page="";
	    $origin="";
	    
	    $messageids[]=$mdata[2];
	    
	    if(preg_match("/".$mdata[2].": from=<(.+)>,/i",$logdata,$mo)){
		
		if($mo[1]=="macave@ma-cave-a-vin.fr"){
		    $mail=$DB_site2->query_first("SELECT * FROM mail_outbox WHERE `to` LIKE '%".mysqli_real_escape_string($DB_site2->link_id,$destinataire)."%' AND date BETWEEN ".($d->gettimestamp()-2)." AND ".($d->gettimestamp()+2));
		    
		    if($mail!=""){
			$from=$mailadmin[$mail['utilisateur']];
			switch($mail['suivitype']){
			    case "suivi_commande":
			    $page="<a href=\"http://www.ma-cave-a-vin.fr/admin/facture.php?action=editer&facturid=".$mail['suiviid']."\">Commande ".$mail['suiviid']."</a>";
			    break;
			    
			    case "suivi_client":
			    $page="<a href=\"http://www.ma-cave-a-vin.fr/admin/utilisateur.php?action=editer&userid=".$mail['suiviid']."\">Client ".$mail['suiviid']."</a>";
			    break;
			}
		    }
		    $origin="site";
		}else{
		    $origin="pc";
		    $from=$mo[1];
		}
	    }
	    
	    
	    if(isset($errors[$destinataire])){
		$errors[$destinataire]['nb']++;
	    }else{
		$client=$DB_site2->query_first("SELECT userid,nom,prenom FROM utilisateur WHERE mail='".mysqli_real_escape_string($DB_site2->link_id,$destinataire)."'");
		$nom="";
		if($client!="")
		    $nom="<a href=\"http://www.ma-cave-a-vin.fr/admin/utilisateur.php?action=editer&userid=".$client['userid']."\">".$client['prenom']." ".$client['nom']."</a>";
		else{
		    $fournisseur=$DB_site2->query_first("SELECT societe,nom,prenom FROM fournisseur WHERE mail='".mysqli_real_escape_string($DB_site2->link_id,$destinataire)."'");
		    if($fournisseur!="") $nom=$fournisseur['societe']." ".$fournisseur['nom']." ".$fournisseur['prenom'];
		}
		$errors[$destinataire]=array("nb"=>1,"nom"=>$nom);
	    }
	    
	    $errors[$destinataire]['envoi'][$d->gettimestamp()]=array("page"=>$page,"from"=>$from,"status"=>$mdata[5],"msg"=>$mdata[6],"messageid"=>$mdata[2],"origin"=>$origin);
	
	}
    }
}

$tr=array();

$msg=<<<HTML
  <table style="font-size:12px;">
  <tr>
    <td>Date</td>
    <td>Source</td>
    <td>Expéditeur</td>
    <td>Page site</td>
    <td>Statut</td>
    <td>Message d'erreur</td>
  </tr>
HTML;

foreach($errors as $destinataire=>$error){
    $msg.=<<<HTML
    <tr style="background:#ccc;">
      <td colspan="6">$error[nom] $destinataire</td>
    </tr>
HTML;
    
    foreach($error['envoi'] as $ts=>$infos){
	$date=date("d/m/Y H:i:s",$ts);
	
    
	$msg.=<<<HTML
	    <tr>
	      <td>$date</td>
	      <td>$infos[origin]</td>
	      <td>$infos[from]</td>
	      <td>$infos[page]</td>
	      <td>$infos[status]</td>
	      <td>$infos[msg]</td>
	    </tr>
	    <tr>
	      <td>&nbsp;</td>
	    </tr>
HTML;

	$tr[$infos['from']].=<<<HTML
	<tr>
	  <td>$date</td>
	  <td>$infos[origin]</td>
	  <td>$error[nom] $destinataire</td>
	  <td>$infos[page]</td>
	  <td>$infos[status]</td>
	  <td>$infos[msg]</td>
	</tr>
HTML;
    }
}

$msg.="</table>";

$hours=array(1,2,6,12,24,48,72);
$opts="";
foreach($hours as $hour){
    $lib=$hour>=24?round($hour/24)."j":$hour."h";
    $opts.="<option value=\"".$hour."\"".($old==$hour?" selected":"").">".$lib."</option>";
}

print <<<HTML
<h1>Erreurs d'envoi d'emails</h1>
<p>Ancienneté : <select onchange="document.location='script_maillog.php?old='+this.value;">$opts</select></p>
HTML;

print $msg;

/*
foreach($tr as $from=>$tbl){
    print empty($from)?"?":$from;
    print "<table>$tbl</table>";
}
*/

include "./footer.php";

?>