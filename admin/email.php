<?php
//CC
// ZIP version


class email{

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
var $source;
var $cidcount=0;
var $save=false;
var $copymode="cc";
var $zippath;
var $style="";

var $mimes=array("jpg"=>"image/jpeg","png"=>"image/png","gif"=>"image/gif");


function __construct(){
         $this->delimiteur = "-----=".md5(uniqid(rand()));
	 $this->delimiteurmixed = "-----=".md5(uniqid(rand()));
	 $this->delimiteurhtml = "-----=".md5(uniqid(rand()));
}


function sendmail(){

//----------------------------------
// Construction de l'entête
//----------------------------------
    $eol="\n";

    $reponse= $this->expediteur;

    $header="Reply-to: $reponse".$eol;
    $header.="Return-Path: $reponse".$eol;
    $header.="MIME-Version: 1.0".$eol;
    $header.="From: ".$this->encodeheader($this->expname)." <".$this->expediteur.">".$eol;
//     $header.="To: ".$this->destname." <".$this->destinataire.">".$eol;
    
    $dest=$this->destinataire;
    if($this->destname!="") 
	$dest="\"".$this->encodeheader($this->destname)."\" <".$this->destinataire.">";

    if(strpos($this->destinataire,";")>-1) $this->destinataire=str_replace(";",",",$this->destinataire);
    if(strpos($this->destinataire,",")>-1) $dest=$this->destinataire;

    $bcc="";
    if(is_array($this->to)){
        foreach($this->to as $ccto)
                if($ccto!=$this->destinataire) $bcc.=",".$ccto;
        $bcc=substr($bcc,1);
    }else{
         if($this->to!=$this->destinataire) $bcc=$this->to;
    }
    if($bcc!="") $header.=ucfirst($this->copymode).": ".$bcc.$eol;
    
    $header.="Content-Type: multipart/mixed; boundary=\"".$this->delimiteurmixed."\"".$eol.$eol;
    $header.= "Ce message est au format MIME 1.0 multipart/mixed.".$eol;
//     $header .= "Message-ID: <".time()."-".$this->expediteur.">\r\n";
//     $header .= "Date: ".date('r')."\r\n";
    $this->header=$header;

//--------------------------------------------------
// Construction du message proprement dit
//--------------------------------------------------

    
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
    $msg .= $eol."--$this->delimiteur--".$eol.$eol;

    $nb=0;
    foreach($this->attachment as $join){
	$msg.="--".$this->delimiteurmixed.$eol;
	$msg.=$join;
	
    }
    $msg.="--".$this->delimiteurmixed."--";
    $msg.=$eol;

    $this->source=$msg;

    $ret=mail($dest,$this->encodeheader($this->subject),$msg,$header);

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
		if(substr($imgsrc,0,1)=="/") $imgsrc=$_SERVER['DOCUMENT_ROOT'].$imgsrc;
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
		if(substr($imgsrc,0,1)=="/") $imgsrc=$_SERVER['DOCUMENT_ROOT'].$imgsrc;
		$this->attachfile($imgsrc,$cid,$this->mimes[$ext],"inline");
	    }
	}
}

function attachfile($fichier,$cid,$typemime="application/octet-stream",$disposition="attachment"){

	 $extension = pathinfo($fichier, PATHINFO_EXTENSION);
	
	 $fichierattache=file_get_contents($fichier);
         $fichierattache = chunk_split(base64_encode($fichierattache));

         $contenttype = "Content-Type: ".$typemime."; name=".$cid.";\n";
         $encoding = "Content-Transfer-Encoding: base64\n";
         $contentdisposition = "";
         $contentid="";
         
         if(!is_null($disposition)){
	    $contentdisposition .= "Content-Disposition: $disposition; size=".strlen($fichierattache)."; filename=\"$cid\"\n";
	    if($disposition=="inline") $contentid .= "Content-ID: <".$cid.">\n";
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
         $attach=chunk_split(base64_encode($str));

         $ret = "Content-Type: ".$mime."; name=\"".$name."\"\n";
         $ret .= "Content-Transfer-Encoding: base64\n";
	 $ret .= "Content-Disposition: $disposition; size=".strlen($str)."; filename=\"$name\"\n";
         if($disposition=="inline") $ret .= "Content-ID: <".$name.">\n";
         $ret .= "\n";
         $ret .= $attach . "\n";

         if($disposition=="attachment")
	  $this->attachment[]=$ret;
	 else
	  $this->inline[]=$ret;
}

function attachfileandzip($fichier,$name){
	 if($this->zippath=="") return false;
	 include($this->zippath);

         $mime="application/zip";

	 $data="";
	 $fp = fopen($fichier, "rb");
         while(!feof($fp)) $data.=fgets($fp,1024);
         fclose($fp);

	 $zip=new zipfile();
	 $zip->addfile($data,$name);
	 $attach=$zip->file();
	 $attach=chunk_split(base64_encode($attach));

         $ret = "Content-Type: ".$mime."; name=\"".$name.".zip\"\n";
         $ret .= "Content-Transfer-Encoding: base64\n";
	 $ret .= "Content-Disposition: attachment; size=".strlen($str)."; filename=$name\n";
         if($disposition=="inline") $ret .= "Content-ID: <".$name.">\n";
         $ret .= "\n";
         $ret .= $attach . "\n";
         
	  $this->attachment[]=$ret;
	 
}

function stylesheet($url){
	if(!file_exists($url)) return false;
	$this->style=file_get_contents($url);
	return true;
}

function encodeheader($txt){
	if($this->encoding=="utf-8")
	    return "=?utf-8?b?".base64_encode($txt)."?=";
	else
	    return $txt;
}
}

?>
