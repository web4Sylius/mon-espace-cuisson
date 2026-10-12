<?php
include_once("host_config_admin.php");
include($rootpath."class/email.php");
$DB_site= clone $DB_site2;

$today=mktime(0,0,0,date("m"),date("d"),date("Y"));
$tomorrow=$today+86400;

$lastuser="";
$txt="<table>
<thead>
<tr><td>Date rappel</td><td>Pour</td><td>Commande</td><td>Client</td><td>Suivi</td></tr>
</thead>";
$requete=$DB_site->query("SELECT * FROM suivi_commande WHERE (rappel BETWEEN $today AND $tomorrow) AND pour<>'' ORDER BY pour,rappel");
while($rappel=$DB_site->fetch_array($requete)){
    $facture=$DB_site->query_first("SELECT nom,prenom FROM facture WHERE facturid=".intval($rappel['facturid']));
    if($lastuser!=$rappel['pour']){
      $txt.="<tr><td colspan=\"4\">&nbsp;</td></tr>";
      $lastuser=$rappel['pour'];
    }
    $txt.="<tr><td>".date("d/m/Y H:i",$rappel['rappel'])."</td><td>".$rappel['pour']."</td><td><a href=\"https://www.ma-cave-a-vin.fr/admin/facture.php?action=editer&facturid=$rappel[facturid]\">".$rappel['facturid']."</a></td><td>".$facture['nom']." ".$facture['prenom']."</td><td>".stripslashes($rappel['suivi'])."</td></tr>";
}

$requete=$DB_site->query("SELECT * FROM sav_steps WHERE (rappel BETWEEN $today AND $tomorrow) AND pour<>'' ORDER BY pour,rappel");
if($DB_site->num_rows($requete)>0){
    $txt.="<p>Commandes en SAV</p>
    <table>
    <thead>
    <tr><td>Date rappel</td><td>Pour</td><td>Commande</td><td>Client</td><td>Suivi</td></tr>
    </thead>";
    while($rappel=$DB_site->fetch_array($requete)){
	$sav=$DB_site->query_first("SELECT * FROM sav WHERE id=".$rappel['savid']);
	$facture=$DB_site->query_first("SELECT nom,prenom FROM facture WHERE facturid=".intval($sav['facturid']));
	$txt.="<tr><td>".date("d/m/Y H:i",$rappel['rappel'])."</td><td>".$rappel['pour']."</td><td><a href=\"https://www.ma-cave-a-vin.fr/admin/facture.php?action=editer&facturid=$rappel[facturid]&savid=$sav[id]#sav\">".$rappel['facturid']."</a></td><td>".$facture['nom']." ".$facture['prenom']."</td><td>".stripslashes($rappel['texte'])."</td></tr>";
    }
    $txt.="</table>";
}

$body="<style>
body{font-size:12px;}
table {font-size:12px; padding-top:10px; border-collapse:collapse;}
thead {font-weight:bold;}
td { border:solid thin #808080; padding:2px;}
</style>
<p>Rappels commande programmés :</p>".$txt;

$mail=new email();
$mail->expediteur="contact@mon-espace-cuisson.com";
$mail->expname="Ma Cave à Vin";
$mail->destinataire="adv@mon-espace-cuisson.com";
$mail->destname="";
$mail->to="pr@mon-espace-cuisson.com";
$mail->subject="Rappels commandes";
$mail->body=$body;
$mail->sendmail();

?>