<?php
include 'admin_global.php';

if(!isset($_GET['id'])) exit;

$file = $DB_site2->query_first("SELECT * FROM mail_attachment WHERE id = '".$_GET['id']."'");

$file_name = $file[name];



switch ($file[moyen]) {
	case 'mail_in':
		$table = 'mail_inbox';
		break;
	
	case 'mail_out':
		$table = 'mail_outbox';
		break;

	case 'mail_to_check':
		$table = 'mail_to_check';
		break;	
}

$date = $DB_site2->query_first("SELECT date FROM $table WHERE id='".$file[mailid]."' ");

$year = date("Y", $date[0]);





// On force le telechargement du fichier
header("Content-Type: application/octet-stream");
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"".$file_name."\""); 

readfile($rootpath."configurations/".$host."/mails/attachments/$year/".$file[id].".".$file[ext]);