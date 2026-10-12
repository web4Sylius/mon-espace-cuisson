<?php

require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");

/*$sql = "SELECT * FROM mail_outbox WHERE userid=0 AND facturid != 0";
$result = $DB_site2->query($sql);
while ($row = $DB_site2->fetch_array($result)) {
	$facture = $DB_site2->query_first("SELECT userid FROM facture WHERE facturid =".$row['facturid']);
	//var_dump($facture['facturid']);
	$userid = $facture['userid'];
	$DB_site2->query("UPDATE mail_outbox SET userid = '$userid' WHERE facturid=".$row['facturid']);
}

$sql = "SELECT * FROM mail_inbox WHERE userid=0 AND facturid != 0";
$result = $DB_site2->query($sql);
while ($row = $DB_site2->fetch_array($result)) {
	$facture = $DB_site2->query_first("SELECT userid FROM facture WHERE facturid =".$row['facturid']);
	//var_dump($facture['facturid']);
	$userid = $facture['userid'];
	$DB_site2->query("UPDATE mail_inbox SET userid = '$userid' WHERE facturid=".$row['facturid']);
}
*/

//pj 
/*
$sql = "SELECT * FROM mail_attachment WHERE moyen ='mail_out' ";
$result = $DB_site2->query($sql);
while ($row = $DB_site2->fetch_array($result)) {
	$mail = $DB_site2->query_first("SELECT date FROM mail_outbox WHERE id=".$row['mailid']);
	$year = date("Y", $mail['date']);
	//var_dump(file_exists('../mail/attachments/'.$row['id'].'.'.$row['ext']));
	if(file_exists('mail/attachments/'.$row['id'].'.'.$row['ext'])){
		rename('mail/attachments/'.$row['id'].'.'.$row['ext'], 'mail/attachments/'.$year.'/'.$row['id'].'.'.$row['ext']);
	}

}

$sql = "SELECT * FROM mail_attachment WHERE moyen ='mail_in' ";
$result = $DB_site2->query($sql);
while ($row = $DB_site2->fetch_array($result)) {
	$mail = $DB_site2->query_first("SELECT date FROM mail_inbox WHERE id=".$row['mailid']);
	$year = date("Y", $mail['date']);
	//var_dump(file_exists('../mail/attachments/'.$row['id'].'.'.$row['ext']));
	if(file_exists('mail/attachments/'.$row['id'].'.'.$row['ext'])){
		rename('mail/attachments/'.$row['id'].'.'.$row['ext'], 'mail/attachments/'.$year.'/'.$row['id'].'.'.$row['ext']);
	}

}*/

$sql = "SELECT * FROM sav_files";
$result = $DB_site2->query($sql);
while ($row = $DB_site2->fetch_array($result)) {
	var_dump($year);
	$year = date("Y", $row['date']);
	if(file_exists('sav/'.$row['id'].'.'.$row['ext'])){
		rename('sav/'.$row['id'].'.'.$row['ext'], 'sav/'.$year.'/'.$row['id'].'.'.$row['ext']);
	}
}