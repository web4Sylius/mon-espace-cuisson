<?php

class mailreader {
    private $parts;
    private $files=array();
    private $date;
    private $subject;
    private $from;
    private $to;

    function __construct($data){
	    $this->getparts($data);
    }

    function parts(){
	    return $this->parts;
    }

    function date(){
	    return $this->date;
    }

    function subject(){
	    return $this->subject;
    }

    function from(){
	    return $this->from;
    }

    function to(){
	    return $this->to;
    }

    function messageid(){
        return $this->messageid;
    }
    
    function files(){
	    return $this->files;
    }

    function message($type=null){
	    if(is_null($type)) $type=($this->parts['html']!=""?"html":"text");
	    return $this->parts[$type];
    }
    
    function include_img($path){
        $return=$this->message();
        foreach($this->files as $f){
            if($f->contentid!=""){
                $p=strpos($f->filename,$path);
                $src=substr($f->filename,$p);
                $return=preg_replace("/src=\"cid:".$f->contentid."\"/i","src=\"".$src."\"",$return);
            }
        }
        $return=preg_replace("/<(img.*?)(height)=['\"]\d+['\"](.*?)>/i","<$1$3>",$return);
        $return=preg_replace("/<(img.*?)(width)=['\"]\d+['\"](.*?)>/i","<$1$3>",$return);
        return $return;
    }

    private function getvar($data,$name){
	    $p=preg_match("/^".$name.": ((.*)(\n\s+(.*))*)/im",$data,$m);
	    if($p>0){
            $dec=preg_replace_callback("#\=\?(.+)\?(.+)\?(.+)\?\=#",function($matches){
                switch(strtoupper($matches[2])){
                    case "Q":
                    $r=quoted_printable_decode($matches[3]);
                    break;

                    case "B":
                    $r=base64_decode($matches[3]);
                    break;
                    
                    default:
                    $r=$matches[3];
                }
                return $r;
            },trim($m[1]));

            return preg_replace("/[\r\n\t]\s/","",$dec);
        }
   
	    return false;
    }

    private function address($data,$name){
        $var=$this->getvar($data,$name);
        if(preg_match_all('/(?P<from>([A-Za-z0-9])(([A-Za-z0-9\._\-])*([A-Za-z0-9]))\@([A-Za-z0-9])' .'(([A-Za-z0-9-\.])*([A-Za-z0-9]))+' . '(\.([A-Za-z0-9])([A-Za-z0-9_\-])?([a-z0-9])+)+)/i',$var,$m)){
            $return=array();
            foreach($m['from'] as $email)
                $return[]=$email;
            return $return;

        }else{
            $p=preg_match("/\n".$name.": (.+)(;|\n|$)/iU",$var,$m);
            if($p>0)
                return array(0=>$m[1]);
        }
    }

    private function getboundary($data) {
        $p=preg_match('/boundary=(.+)/i',$data,$m);
        if($p) return str_replace(array(";","\""),"",$m[1]);
        return false;
    }

    private function getparts($data){
        $msgpos=strpos($data,"\r\n\r\n");
        if($msgpos==false) $msgpos=strpos($data,"\n\n");
        $parthead=trim(substr($data,0,$msgpos));
        $partbody=substr($data,$msgpos);
        $ret['head']=$parthead;

        $from=$this->address($parthead,"From");
        $this->from=$from[0];
        $to=$this->address($parthead,"To");
        $cc=$this->address($parthead,"cc");
        if(!is_null($cc)) $to=array_merge($to,$cc);
        $this->to=$to;
        $this->date=$this->maildate($this->getvar($parthead,"Date"));
        $this->subject=$this->getsubject($this->getvar($parthead,"Subject"));
        $this->messageid=$this->getvar($parthead,"Message-ID");
        $contenttype=$this->getvar($parthead,"Content-Type");
        preg_match("/(.+\/.+);/",$contenttype,$m);
        $contenttype=$m[1];

        if($contenttype==false || $contenttype=="text/plain" || $contenttype=="text/html")
            $spsource=array($data);
        else {
            $boundary=$this->getboundary($parthead);
            if($boundary==false)
                $spsource=array($partbody);
            else
                $spsource=explode($boundary,$partbody);
        }
        
        $i=0;
        $html=false;

        $ret['text']=$ret['html']="";
        foreach($spsource as $part){
            $partprops=$this->getpart($part);
            foreach($partprops as $partprop){
                if($partprop['content-type']=="text/html"){
                    $html=true;
                    $ret['html'].=$partprop['message'];
                }
                if($partprop['content-type']=="text/plain" || !$partprop['content-type']) $ret['text'].=nl2br($partprop['message']);
                if($partprop['name']!="") $this->files[$partprop['name']]=new attachment($partprop['message'],$partprop['content-type'],$partprop['name'],$partprop['contentid']);
                $i++;
            }
        }

        $this->parts=$ret;
        return $ret;

    }


    private function getpart($part){
	    $i=0;
	    $part=trim($part);
	    $msgpos=strpos($part,"\r\n\r\n");
        if($msgpos==false) $msgpos=strpos($part,"\n\n");
	    $parthead=trim(substr($part,0,$msgpos));
	    $partbody=trim(substr($part,$msgpos));

	    
        $content=$this->getvar($parthead,"Content-Type");
        if(preg_match("/(.+\/.+);/U",$content,$m)) $content=$m[1];
	    
	    if($content==false){
            $partbody=$part;
            $parthead="";
        }
	    
	    $ret=array();
	    $ret[$i]['content-type']=$content;

	    if($content=="multipart/alternative" || $content=="multipart/related"){
            $boundary=$this->getboundary($parthead);
            $sppart=explode($boundary,$partbody);

            foreach($sppart as $subpart){
                //print "<part id=".$i.">".$subpart."</part>";
                $subdata=$this->getpart($subpart);
                if($subdata!=false){
                    foreach($subdata as $subdata2) {
                        $ret[$i]['name']=$subdata2['name'];
                        $ret[$i]['message']=$subdata2['message'];
                        $ret[$i]['content-type']=$subdata2['content-type'];
                        $ret[$i]['contentid']=$subdata2['contentid'];
                        $i++;
                    }
                }
            }
            return $ret;
	    }else {
            $ret[$i]['message']=$this->decode($parthead,$partbody);
        // 	     $ret[$i]['message']=$partbody;
            $ret[$i]['head']=$parthead;
	    }
	    
// 	    $npos=strpos($parthead,"name");
	    $ret[$i]['name']="";
	    $ret[$i]['contentid']="";
	    
	    $npos=preg_match("/[^a-z]name=[\"']([^\"]+)[\"']/",$parthead,$m);
	    if($npos==0)
                $npos=preg_match("/[^a-z]name=([^\s]+)/",$parthead,$m);
	    if($npos>0){
// 		      $npos=strpos($parthead,"=",$npos)+1;
// 		      $ctend=strpos($parthead,"\n",$npos);
//             $name=substr($parthead,$npos,$ctend-$npos);
            $dec=preg_replace_callback("#\=\?(.+)\?(.+)\?(.+)\?\=#",function($matches){
                switch(strtoupper($matches[2])){
                    case "Q":
                    $r=quoted_printable_decode($matches[3]);
                    break;

                    case "B":
                    $r=base64_decode($matches[3]);
                    break;
                    
                    default:
                    $r=$matches[3];
                }
                return $r;
            },trim($m[1]));

            $ret[$i]['name']=preg_replace("/[\r\n\t]\s/","",$dec);
        }
        
        if(preg_match("/content-id:\s?<([^>]+)>/i",$parthead,$m)){
            $ret[$i]['contentid']=$m[1];
        }

	    return $ret;

    }

    private function decode($head,$str) {
	    $encoding=$this->getvar($head,"Content-Transfer-Encoding");

	    switch($encoding){
            case "quoted-printable":
            $str=quoted_printable_decode($str);
            break;

            case "base64":
            $str=base64_decode($str);
            break;
	    }

	    if(strpos(strtolower($head),"iso-")>-1) $str=utf8_encode($str);
	    return $str;
    }

    private function maildate($date) {
        $dt=new DateTime($date,new DateTimeZone("Europe/Paris"));
        return $dt->getTimestamp();
    }

    function findemailinmail(){
	    $pattern="/([\s]*)([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*([ ]+|)@([ ]+|)([a-zA-Z0-9-]+\.)+([a-zA-Z]{2,}))([\s]*)/i";
	    $ret=preg_match($pattern,$this->message(),$m);
	    return $m[0];
    }

    private function getsubject($txt){
	    $return=preg_match("#\=\?(.+)\?(.+)\?(.+)\?\=#",$txt,$matches);
	    $ret="";
	    if($return>0){
            switch($matches[2]){
                case "Q":
                $ret=quoted_printable_decode($matches[3]);
                break;

                case "B":
                $ret=base64_decode($matches[3]);
                break;
            }
            //if(strtoupper($matches[1])=="UTF-8") $ret=utf8_decode($ret);
            return $ret;
	    }else
            return $txt;
    }

}

class attachment{
    public $name;
    public $ext;
    public $mime;
    public $size;
    public $type;
    public $contentid;
    public $data;
    public $filename;

    function __construct($data,$contenttype,$name,$contentid){
        $this->name=$name;
        $this->ext=strtolower(pathinfo($name,PATHINFO_EXTENSION));
        $this->size=strlen($data);
        $this->type=$contenttype;
        $this->contentid=$contentid;
        $this->data=$data;
    }

    function save($filename){
        $return=file_put_contents($filename,$this->data);
        $this->filename=$filename;
        chmod($filename,0644);
        return $return;
    }

    function getext($DB){
        $fi=new finfo(FILEINFO_MIME_TYPE);
        $mime=$fi->buffer($this->data);
        $mimetype=$DB->query_first("SELECT g_extension FROM MimeType WHERE g_mimeType='".$mime."'");
        if($mimetype==false) return "";
        $this->ext=$mimetype[0];
        return $this->ext;
    }
}

?>
