<?php
//CC
// ZIP version

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;




if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false){ // LOCAL
    $rootpathemail   = "d:/wamp/www/mon-espace-cuisson/"; 
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test') !== false) {
    $rootpathemail   = $_SERVER['DOCUMENT_ROOT']."/";
} else {
    $rootpathemail   = "/var/www/vhosts/mon-espace-cuisson.com/httpdocs/";
}

require __DIR__.'/phpmailer/PHPMailer.php';
require __DIR__.'/phpmailer/SMTP.php';
require __DIR__.'/phpmailer/Exception.php';


class email{ // debut class

    private $to;
    private $cc;
    private $bcc;
    private $destinataires=array();
    private $destname;
    private $expediteur;
    private $expname;
    private $subject;
    private $encoding="utf-8";
    public $body;
    private $inline=array();
    private $attachment=array();
    private $attachmentsmtp=array();
    private $source;
    private $cidcount=0;
    // private $save=ROOT_PATH.'/mailenvoyes.txt'; // false;
    private $save=false;
    private $copymode="cc";
    private $zippath;
    private $style="";
    private $mimes=array("jpg"=>"image/jpeg","png"=>"image/png","gif"=>"image/gif");
    private $uniqueid;
    public  $arrContextOptions=array("ssl"=>array("verify_peer"=>false,"verify_peer_name"=>false));



    function __construct(){

    }


    function sendmail(){
        global $DB_site;
        global $DB_site2;
        //----------------------------------
        // Construction de l'entête
        //----------------------------------
        global $host;
        global $rootpathemail;
        
        // $this->save=ROOT_PATH.'/mailenvoyes.txt';
        
        
        // ICI LE DEBUT DE LA MODIF DE BENOIT
        $smtpmail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            //Server settings
            $smtpmail->CharSet = 'UTF-8';
            // $smtpmail->SMTPDebug = 2;                                 // Enable verbose debug output
            $smtpmail->isSMTP();                                      // Set mailer to use SMTP
            $smtpmail->Host = 'smtp.mon-espace-cuisson.com';               // Specify main and backup SMTP servers (';' separated)
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
            $ndd_email="https://www.mon-espace-cuisson.com/";
            $sql="SELECT mail_passwdFR, nom_contact FROM parametresgeneraux WHERE mail_contactFR='".$this->expediteur."' ";
            $resFR=$DB->query_first($sql);
            $smtpmail->Password=$resFR['mail_passwdFR'];
            $smtpmail->Username=$this->expediteur;
            $smtpmail->setFrom($this->expediteur, $resFR['nom_contact']." Mon Espace Cuisson");

            $smtpmail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $smtpmail->Port = 25;                                    // TCP port to connect to
            
            $this->body = str_replace ( "../" , $ndd_email , $this->body );
            
            //Recipients
            
            if(!is_null($this->destinataires)){
                foreach($this->destinataires as $to){
                    $smtpmail->addAddress(trim($to));
                }
            }
            
            
            if(!is_null($this->to)){
                foreach($this->to as $ccto){
                    $smtpmail->{"add".strtoupper($this->copymode)}($ccto);
                }
            }
            
            if(!is_null($this->cc)){
                foreach($this->cc as $ccto){
                    $smtpmail->addCC($ccto);
                }
            }
            
            if(!is_null($this->bcc)){
                foreach($this->bcc as $ccto){
                    $smtpmail->addBCC($ccto);
                }
            }
            
            // fin recipients
            
            
            if($this->body==""){
                $this->body="...";
            }
            
            
            // $smtpmail->addAddress('webmaster1@mon-espace-cuisson.com', 'Joe User');     // Add a recipient
            // $smtpmail->addAddress('ellen@example.com');               // Name is optional
            // $smtpmail->addReplyTo('info@example.com', 'Information');
            // $smtpmail->addCC('cc@example.com');
            // $smtpmail->addBCC('bcc@example.com');

            //Attachments
/*            while (list($key, $val) = each($this->attachmentsmtp)) {
                $smtpmail->addAttachment($val['file'] , $val['filename']);
            }*/
            // $smtpmail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            // $smtpmail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            //Content
            $smtpmail->isHTML(true);                                  // Set email format to HTML
            $smtpmail->Subject = $this->subject;
            $smtpmail->Body    = $this->body;
            $smtpmail->AltBody = strip_tags($this->body);

            $smtpmail->send();
            
            $this->uniqueid = $smtpmail->getUniqueId();

            $par_email_get=preg_replace("/[\r\n\t]/","",var_export($_GET,true));
            $par_email_post=preg_replace("/[\r\n\t]/","",var_export($_POST,true));
            $par_email_cookies=preg_replace("/[\r\n\t]/","",var_export($_COOKIE,true));
            $par_email_to=preg_replace("/[\r\n\t]/","",var_export($this->to,true));
            $par_email_destinataire=preg_replace("/[\r\n\t]/","",var_export($this->destinataires,true));
            
            $log_texte = date("d/m/Y") . ' ||| ' . date("H:i:s") . ' ||| Script : ' . $_SERVER['REQUEST_URI'] . ' ||| GET : ' . $par_email_get . ' ||| POST : ' . $par_email_post . ' ||| COOKIE ' . $par_email_cookies . ' ||| Erreur : ' . $smtpmail->ErrorInfo . '  ||| login : ' . $smtpmail->Username . ' ||| passe : '. $smtpmail->Password . ' ||| dest : ' . $par_email_destinataire .' ||| bcc : ' . $par_email_to . " ||| body \r\n" . $this->body . "\r\n";

            /*$f = fopen($rootpathemail . "/log/sendmail_OK_" . date("Y-m-d") . ".txt","a");
            fputs($f, $log_texte . "\r\n\r\n");
            fclose($f);*/
            
            
            $ret = true;
            // echo 'Message has been sent';
        } catch (Exception $e) {
                // echo 'Message could not be sent.';
                // echo 'Mailer Error: ' . $smtpmail->ErrorInfo;
                // $this->body.=
                // mail("webmaster1@mon-espace-cuisson.com","erreur envoi email",$this->body);
                $par_email_get=preg_replace("/[\r\n\t]/","",var_export($_GET,true));
                $par_email_post=preg_replace("/[\r\n\t]/","",var_export($_POST,true));
                $par_email_cookies=preg_replace("/[\r\n\t]/","",var_export($_COOKIE,true));
                $par_email_to=preg_replace("/[\r\n\t]/","",var_export($this->to,true));
                $par_email_destinataire=preg_replace("/[\r\n\t]/","",var_export($this->destinataires,true));
                
                $log_texte = date("d/m/Y") . ' ||| ' . date("H:i:s") . ' ||| Script : ' . $_SERVER['REQUEST_URI'] . ' ||| GET : ' . $par_email_get . ' ||| POST : ' . $par_email_post . ' ||| COOKIE ' . $par_email_cookies . ' ||| Erreur : ' . $smtpmail->ErrorInfo . '  ||| login : ' . $smtpmail->Username . ' ||| passe : '. $smtpmail->Password . ' ||| dest : ' . $par_email_destinataire .' ||| bcc : ' . $par_email_to . " ||| body \r\n" . $this->body . "\r\n";

                /*$f = fopen($rootpathemail . "/log/sendmail_error_" . date("Y-m-d") . ".txt","a");
                fputs($f, $log_texte . "\r\n\r\n");
                fclose($f);*/
                $ret = false;
        }
        foreach($this->attachmentsmtp as $file){
            if(file_exists($file['file'])){
                unlink($file['file']);
            }
        }
        

        // ICI LA FIN DE LA MODIF DE BENOIT
        
        
        if($this->save!=false){
            $f=fopen($this->save,"a+");
            fwrite($f,$this->body);
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
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false
            )
        );
        $fichierattache=file_get_contents($fichier,FALSE,stream_context_create($arrContextOptions));
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

    function __get($var){
        if($var=="destinataire") return implode(";",$this->destinataires);
        if(isset($this->$var)) return $this->$var;
        return null;
    }
    
    function __set($var,$val){
        switch($var){
            case "destname":
            case "expediteur":
            case "expname":
            case "subject":
            case "encoding":
            case "copymode":
            case "style":
            $this->$var=$val;
            break;
            
            case "destinataire":
            $tolist=array_filter(preg_split("/[,;]/",$val));
            foreach($tolist as $to)
                $this->destinataires[]=trim($to);
            break;
            
            case "to":
            case "cc":
            case "bcc":
            $tolist=$val;
            if(!is_array($val))
                $tolist=preg_split("/[,;]/",$val);
            $tolist=array_filter($tolist);
            foreach($tolist as $to)
                $this->{$var}[]=trim($to);
            break;
        }
    }

} // fin de la classe

?>