<?php
require_once __DIR__.'/../secrets.php';
//CC
// ZIP version


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load composer's autoloader
require SITE_PATH.'/class/phpmailer/PHPMailer.php';
require SITE_PATH.'/class/phpmailer/SMTP.php';
require SITE_PATH.'/class/phpmailer/Exception.php';


class email{ // debut class

var $to;
var $destinataire;
var $destname;
var $expediteur;
var $expname;
var $subject;
var $encoding="utf-8";
var $body;
var $inline=array();
var $attachment=array();
var $attachmentsmtp=array();
var $source;
var $cidcount=0;
var $save=false;
var $copymode="cc";
var $zippath;
var $style="";
var $mimes=array("jpg"=>"image/jpeg","png"=>"image/png","gif"=>"image/gif");


function email(){
    $this->delimiteur = "-----=".md5(uniqid(rand()));
	$this->delimiteurmixed = "-----=".md5(uniqid(rand()));
	$this->delimiteurhtml = "-----=".md5(uniqid(rand()));
}


function sendmail(){
	global $DB_site;
	global $DB_site2;
	//----------------------------------
	// Construction de l'entête
	//----------------------------------
    $eol="\n";
	global $host;
    $reponse = $this->expediteur;
	
	/*
    $header="Reply-to: $reponse".$eol;
    $header.="Return-Path: $reponse".$eol;
    $header.="MIME-Version: 1.0".$eol;
    $header.="From: ".$this->encodeheader($this->expname)." <".$this->expediteur.">".$eol;
	// $header.="To: ".$this->destname." <".$this->destinataire.">".$eol;
    
    $dest=$this->destinataire;
    if($this->destname!="") 
	$dest="\"".$this->encodeheader($this->destname)."\" <".$this->destinataire.">";

    if(strpos($this->destinataire,";")>-1)
		$this->destinataire=str_replace(";",",",$this->destinataire);
    if(strpos($this->destinataire,",")>-1)
		$dest=$this->destinataire;
	*/
    $bcc="";
    if(is_array($this->to)){
        foreach($this->to as $ccto)
            if($ccto!=$this->destinataire)
				$bcc.=",".$ccto;
        $bcc=substr($bcc,1);
    }else{
         if($this->to!=$this->destinataire) $bcc=$this->to;
    }
    if($bcc!="") $header.=ucfirst($this->copymode).": ".$bcc.$eol;
    
    // #### $header.="Content-Type: multipart/mixed; boundary=\"".$this->delimiteurmixed."\"".$eol.$eol;
	// $header.= "Ce message est au format MIME 1.0 multipart/mixed.".$eol;
	// $header .= "Message-ID: <".time()."-".$this->expediteur.">\r\n";
	// $header .= "Date: ".date('r')."\r\n";
    // #### $this->header=$header;

	//--------------------------------------------------
	// Construction du message proprement dit
	//--------------------------------------------------

    /*
    $msg="";
    $msg .= "--$this->delimiteurmixed".$eol;
    $msg.="Content-Type: multipart/alternative; boundary=\"".$this->delimiteur."\"".$eol;
    $msg.=$eol.$eol;

	//txt
    $msg .= "--$this->delimiteur".$eol;
    $msg .= "Content-Type: text/plain; charset=\"".$this->encoding."\"".$eol;
    $msg .= "Content-Transfer-Encoding:8bit".$eol;
    $msg .= $eol;
    $msg .= strip_tags($this->body);
    $msg .= $eol;

    

	//---------------------------------
	// 1ère partie du message
	// Le code HTML
	//---------------------------------

    $this->scanimg();
    $msg .= "--$this->delimiteur".$eol;
    $msg .= "Content-Type: multipart/related; boundary=\"".$this->delimiteurhtml."\"".$eol.$eol.$eol;

    $msg .= "--$this->delimiteurhtml".$eol;
    $msg .= "Content-Type: text/html; charset=\"".$this->encoding."\"".$eol;
    $msg .= "Content-Transfer-Encoding:8bit".$eol;
    $msg .= $eol;
    $msg .= "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"><html>";
    $msg .= "<head>\n<style>\n".$this->style."\n</style>\n</head>";
    $msg .= "<body>".$this->body."</body>";
    $msg .= "</html>".$eol;
    $msg .= $eol;

    $nb=0;
    foreach($this->inline as $join){
		$msg.="--".$this->delimiteurhtml.$eol;
		$msg.=$join;
    }
    $msg.="--".$this->delimiteurhtml."--".$eol;
    $msg .= $eol."--".$this->delimiteur."--".$eol.$eol;

    $nb=0;
    foreach($this->attachment as $join){
		$msg.="--".$this->delimiteurmixed.$eol;
		$msg.=$join;
    }
    $msg.="--".$this->delimiteurmixed."--";
    $msg.=$eol;

    $this->source=$msg;
	*/

	
	
	// ICI LE DEBUT DE LA MODIF DE BENOIT
	$smtpmail = new PHPMailer(true);                              // Passing `true` enables exceptions
	try {
		//Server settings
		$smtpmail->CharSet = 'UTF-8';
		$smtpmail->SMTPDebug = 2;                                 // Enable verbose debug output
		$smtpmail->isSMTP();                                      // Set mailer to use SMTP
		$smtpmail->Host = 'mail.mon-espace-cuisson.com';                        // Specify main and backup SMTP servers (';' separated)
		$smtpmail->SMTPAuth = true;                               // Enable SMTP authentication
		$smtpmail->SMTPOptions = array(
                        'ssl' => array(
							'verify_peer' => false,
							'verify_peer_name' => false,
							'allow_self_signed' => true
                        )
					);
		$DB="";
		if(isset($DB_site)){
				$DB=$DB_site;
		}
		if(isset($DB_site2)){
				$DB=$DB_site2;
		}
		$sql="SELECT mail_passwdFR, nom_contact FROM parametresgeneraux WHERE mail_contactFR='".$this->expediteur."' ";
		$resFR=$DB->query_first($sql);
		if($resFR[mail_passwdFR]!=""){
			$smtpmail->Password=$resFR[mail_passwdFR];
			$smtpmail->Username=$this->expediteur;
			$smtpmail->setFrom($this->expediteur, $resFR[nom_contact]." Ma Cave A Vin");
		} else {
			$sql="SELECT mail_passwdEN, nom_contact FROM parametresgeneraux WHERE mail_contactEN='".$this->expediteur."' ";
			$resEN=$DB->query_first($sql);
			if($resEN[mail_passwdEN]!=""){
				$smtpmail->Password=$resEN[mail_passwdEN];
				$smtpmail->Username=$this->expediteur;
				$smtpmail->setFrom($this->expediteur, $resEN[nom_contact]." My Wine Cabinet");
			} else {
				$smtpmail->Username = SMTP_USER;
				$smtpmail->Password = SMTP_PASSWORD;
				$smtpmail->setFrom($this->expediteur, "Ma Cave A Vin");
			}
		}
		$smtpmail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		$smtpmail->Port = 25;                                    // TCP port to connect to

		//Recipients
		$smtpmail->addAddress($this->destinataire);     // Add a recipient


		if(is_array($this->to)){
			foreach($this->to as $ccto)
				$smtpmail->addBCC($ccto);
		}else{
			if($this->to!=$this->destinataire)
				$smtpmail->addBCC($this->to);
		}

		
		// $smtpmail->addAddress('webmaster1@mon-espace-cuisson.com', 'Joe User');     // Add a recipient
		// $smtpmail->addAddress('ellen@example.com');               // Name is optional
		// $smtpmail->addReplyTo('info@example.com', 'Information');
		// $smtpmail->addCC('cc@example.com');
		// $smtpmail->addBCC('bcc@example.com');

		//Attachments
		while (list($key, $val) = each($this->attachmentsmtp)) {
				$smtpmail->addAttachment($val['file'] , $val['filename']);
		}
		// $smtpmail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
		// $smtpmail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

		//Content
		$smtpmail->isHTML(true);                                  // Set email format to HTML
		$smtpmail->Subject = $this->subject;
		$smtpmail->Body    = $this->body;
		$smtpmail->AltBody = strip_tags($this->body);

		$smtpmail->send();
		echo 'Message has been sent';
	} catch (Exception $e) {
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $smtpmail->ErrorInfo;
	}

	
    // $ret=mail($dest,$this->encodeheader($this->subject),$msg,$header,"-f".$this->expediteur);
	// ICI LA FIN DE LA MODIF DE BENOIT
	
	
    if($this->save!=false){
        $f=fopen($this->save,"a+");
        fwrite($f,$msg);
        fclose($f);
    }

    return $ret;
}


function scanimg(){
	$match=preg_match_all('/< *img[^>]*src *= *["\']?([^"\']*)/i',$this->body,$results);
	if($match>0){
	    $results[1]=array_unique($results[1]);
	    foreach($results[1] as $imgsrc){	    
			$this->cidcount++;
			$cid="image".$this->cidcount;
			$ext=pathinfo($imgsrc,PATHINFO_EXTENSION);
			$this->body=str_replace($imgsrc,"cid:".$cid,$this->body);
			$imgsrc=str_replace("&amp;","&",$imgsrc);
			if(substr($imgsrc,0,1)=="/")
				$imgsrc=$_SERVER['DOCUMENT_ROOT'].$imgsrc;
			$this->attachfile($imgsrc,$cid,$this->mimes[$ext],"inline");
	    }
	}
	$match=preg_match_all('/background(-image)*: *url\(["\']*([^\'"]+)[\'"]*\)/i',$this->body,$results);
	if($match>0){
	    $results[2]=array_unique($results[2]);
	    foreach($results[2] as $imgsrc){	    
			$this->cidcount++;
			$cid="image".$this->cidcount;
			$ext=pathinfo($imgsrc,PATHINFO_EXTENSION);
			$this->body=str_replace($imgsrc,"cid:".$cid,$this->body);
			$imgsrc=str_replace("&amp;","&",$imgsrc);
			if(substr($imgsrc,0,1)=="/")
				$imgsrc=$_SERVER['DOCUMENT_ROOT'].$imgsrc;
			if(substr($imgsrc,0,3)=="../")
				$imgsrc=$_SERVER['DOCUMENT_ROOT']."/admin/".$imgsrc;
			$this->attachfile($imgsrc,$cid,$this->mimes[$ext],"inline");
	    }
	}
}

function attachfile($fichier,$cid,$typemime="application/octet-stream",$disposition="attachment"){
	// début modif benoit (envoi par SMTP)
	$fichierattache=file_get_contents($fichier);
	$temp_file = tempnam(sys_get_temp_dir(), 'tmp');
	$h = fopen($temp_file, "w");
	fwrite($h, $fichierattache);
	fclose($h);
	$this->attachmentsmtp[]=array("file" => $temp_file, "filename" => $cid, "mime" => $typemime);
	// fin modif benoit (envoi par SMTP)
	$extension = pathinfo($fichier, PATHINFO_EXTENSION);
	$fichierattache = chunk_split(base64_encode($fichierattache));
	$contenttype = "Content-Type: ".$typemime."; name=".$cid.";\n";
	$encoding = "Content-Transfer-Encoding: base64\n";
	$contentdisposition = "";
	$contentid="";
	if(!is_null($disposition)){
		$contentdisposition .= "Content-Disposition: $disposition; size=".strlen($fichierattache)."; filename=\"$cid\"\n";
		if($disposition=="inline")
			$contentid .= "Content-ID: <".$cid.">\n";
	}else{
		$contentid .= "Content-ID: <".$cid.">\n";
	}
	$ret = $contenttype
		.$encoding
		.$contentid
		.$contentdisposition
		."\n"
	.$fichierattache . "\n";
	if($disposition=="attachment")
		$this->attachment[]=$ret;
	else
		$this->inline[]=$ret;
}

function attachstring($str,$name,$mime,$disposition="attachment"){
	// début modif benoit (envoi par SMTP)
	$temp_file = tempnam(sys_get_temp_dir(), 'tmp');
	$h = fopen($temp_file, "w");
	fwrite($h, $str);
	fclose($h);
	$this->attachmentsmtp[]=array("file" => $temp_file, "filename" => $name, "mime" => $mime);
	// fin modif benoit (envoi par SMTP)
	$attach=chunk_split(base64_encode($str));
	$ret = "Content-Type: ".$mime."; name=\"".$name."\"\n";
	$ret .= "Content-Transfer-Encoding: base64\n";
	$ret .= "Content-Disposition: $disposition; size=".strlen($str)."; filename=\"$name\"\n";
	if($disposition=="inline")
		$ret .= "Content-ID: <".$name.">\n";
	$ret .= "\n";
	$ret .= $attach . "\n";
	if($disposition=="attachment")
		$this->attachment[]=$ret;
	else
		$this->inline[]=$ret;
}

function attachfileandzip($fichier,$name){
	if($this->zippath=="")
		return false;
	include($this->zippath);
	$mime="application/zip";
	$data="";
	$fp = fopen($fichier, "rb");
	while(!feof($fp))
		$data.=fgets($fp,1024);
	fclose($fp);
	$zip=new zipfile();
	$zip->addfile($data,$name);
	$attach=$zip->file();
	// début modif benoit (envoi par SMTP)
	$temp_file = tempnam(sys_get_temp_dir(), 'tmp');
	$h = fopen($temp_file, "w");
	fwrite($h, $data);
	fclose($h);
	$this->attachmentsmtp[]=array("file" => $temp_file, "filename" => $name, "mime" => $mime);
	// fin modif benoit (envoi par SMTP)
	$attach=chunk_split(base64_encode($attach));
	$ret = "Content-Type: ".$mime."; name=\"".$name.".zip\"\n";
	$ret .= "Content-Transfer-Encoding: base64\n";
	$ret .= "Content-Disposition: attachment; size=".strlen($str)."; filename=$name\n";
	if($disposition=="inline")
		$ret .= "Content-ID: <".$name.">\n";
	$ret .= "\n";
	$ret .= $attach . "\n";
	$this->attachment[]=$ret;
}

function stylesheet($url){
	if(!file_exists($url))
		return false;
	$this->style=file_get_contents($url);
	return true;
}

function encodeheader($txt){
	if($this->encoding=="utf-8")
	    return "=?utf-8?b?".base64_encode($txt)."?=";
	else
	    return $txt;
}

} // fin de la classe

?>