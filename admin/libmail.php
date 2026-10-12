<?php

/*


    this class encapsulates the PHP mail() function.
    implements CC, Bcc, Priority headers


@version    1.3 

- added ReplyTo( $address ) method
- added Receipt() method - to add a mail receipt
- added optionnal charset parameter to Body() method. this should fix charset problem on some mail clients
         
@example

    include "libmail.php";
    
    $m= new Mail; // create the mail
    $m->From( "leo@isp.com" );
    $m->To( "destination@somewhere.fr" );
    $m->Subject( "the subject of the mail" );   

    $message= "Hello world!\nthis is a test of the Mail class\nplease ignore\nThanks.";
    $m->Body( $message);    // set the body
    $m->Cc( "someone@somewhere.fr");
    $m->Bcc( "someoneelse@somewhere.fr");
    $m->Priority(4) ;   // set the priority to Low 
    $m->Attach( "/home/leo/toto.gif", "image/gif" ) ;   // attach a file of type image/gif
    $m->Send(); // send the mail
    echo "the mail below has been sent:<br><pre>", $m->Get(), "</pre>";

    
LASTMOD
    Fri Oct  6 15:46:12 UTC 2000

@author Leo West - lwest@free.fr

*/


class Mail
{
    /*
    list of To addresses
    @var    array
    */
    var $sendto = array();
    /*
    @var    array
    */
    var $acc = array();
    /*
    @var    array
    */
    var $abcc = array();
    /*
    paths of attached files
    @var array
    */
    var $aattach = array();
    /*
    list of message headers
    @var array
    */
    var $xheaders = array();
    /*
    message priorities referential
    @var array
    */
    var $priorities = array( '1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)' );
    /*
    character set of message
    @var string
    */
    var $charset = "iso-8859-1";
    var $ctencoding = "7bit";
    var $receipt = 0;
    

/*

    Mail contructor
    
*/

function Mail()
{
    $this->autoCheck( true );
    $this->boundary= "--" . md5( uniqid("myboundary") );
}


/*      

activate or desactivate the email addresses validator
ex: autoCheck( true ) turn the validator on
by default autoCheck feature is on

@param boolean  $bool set to true to turn on the auto validation
@access public
*/
function autoCheck( $bool )
{
    if( $bool )
        $this->checkAddress = true;
    else
        $this->checkAddress = false;
}


/*

Define the subject line of the email
@param string $subject any monoline string

*/
function Subject( $subject )
{
    $this->xheaders['Subject'] = strtr( $subject, "\r\n" , "  " );
}


/*

set the sender of the mail
@param string $from should be an email address

*/
 
function From( $from )
{

    if( ! is_string($from) ) {
        echo "Class Mail: error, From is not a string";
        exit;
    }
    $this->xheaders['From'] = $from;
}

/*
 set the Reply-to header 
 @param string $email should be an email address

*/ 
function ReplyTo( $address )
{

    if( ! is_string($address) ) 
        return false;
    
    $this->xheaders["Reply-To"] = $address;
        
}


/*
add a receipt to the mail ie.  a confirmation is returned to the "From" address (or "ReplyTo" if defined) 
when the receiver opens the message.

@warning this functionality is *not* a standard, thus only some mail clients are compliants.

*/
 
function Receipt()
{
    $this->receipt = 1;
}


/*
set the mail recipient
@param string $to email address, accept both a single address or an array of addresses

*/

function To( $to )
{

    // TODO : test validité sur to
    if( is_array( $to ) )
        $this->sendto= $to;
    else 
        $this->sendto[] = $to;

    if( $this->checkAddress == true )
        $this->CheckAdresses( $this->sendto );

}


/*      Cc()
 *      set the CC headers ( carbon copy )
 *      $cc : email address(es), accept both array and string
 */

function Cc( $cc )
{
    if( is_array($cc) )
        $this->acc= $cc;
    else 
        $this->acc[]= $cc;
        
    if( $this->checkAddress == true )
        $this->CheckAdresses( $this->acc );
    
}



/*      Bcc()
 *      set the Bcc headers ( blank carbon copy ). 
 *      $bcc : email address(es), accept both array and string
 */

function Bcc( $bcc )
{
    if( is_array($bcc) ) {
        $this->abcc = $bcc;
    } else {
        $this->abcc[]= $bcc;
    }

    if( $this->checkAddress == true )
        $this->CheckAdresses( $this->abcc );
}


/*      Body( text [, charset] )
 *      set the body (message) of the mail
 *      define the charset if the message contains extended characters (accents)
 *      default to us-ascii
 *      $mail->Body( "mél en français avec des accents", "iso-8859-1" );
 */
function Body( $body, $charset="" )
{
    $this->body = $body;
    
    if( $charset != "" ) {
        $this->charset = strtolower($charset);
        if( $this->charset != "us-ascii" )
            $this->ctencoding = "8bit";
    }
}


/*      Organization( $org )
 *      set the Organization header
 */
 
function Organization( $org )
{
    if( trim( $org != "" )  )
        $this->xheaders['Organization'] = $org;
}


/*      Priority( $priority )
 *      set the mail priority 
 *      $priority : integer taken between 1 (highest) and 5 ( lowest )
 *      ex: $mail->Priority(1) ; => Highest
 */
 
function Priority( $priority )
{
    if( ! intval( $priority ) )
        return false;
        
    if( ! isset( $this->priorities[$priority-1]) )
        return false;

    $this->xheaders["X-Priority"] = $this->priorities[$priority-1];
    
    return true;
    
}


/*  
 Attach a file to the mail
 
 @param string $filename : path of the file to attach
 @param string $filetype : MIME-type of the file. default to 'application/x-unknown-content-type'
 @param string $disposition : instruct the Mailclient to display the file if possible ("inline") or always as a link ("attachment") possible values are "inline", "attachment"
 */

function Attach( $filename, $filetype = "", $disposition = "attachment" )
{
 if ($filetype==""){
    if (substr($filename,strlen($filename)-3,3)=='dwg') $filetype='application/acad';
    if (substr($filename,strlen($filename)-3,3)=='doc') $filetype='application/msword';
    if (substr($filename,strlen($filename)-4,4)=='ccad') $filetype='application/clariscad';
    if (substr($filename,strlen($filename)-3,3)=='drw') $filetype='application/drafting';
    if (substr($filename,strlen($filename)-3,3)=='dxf') $filetype='application/dxf';
    if (substr($filename,strlen($filename)-3,3)=='unv') $filetype='application/i-deas';
    if (substr($filename,strlen($filename)-4,4)=='iges') $filetype='application/iges';
    if (substr($filename,strlen($filename)-3,3)=='igs') $filetype='application/iges';
    if (substr($filename,strlen($filename)-3,3)=='bin') $filetype='application/octet-stream';
    if (substr($filename,strlen($filename)-3,3)=='oda') $filetype='application/oda';
    if (substr($filename,strlen($filename)-3,3)=='pdf') $filetype='application/pdf';
    if (substr($filename,strlen($filename)-3,3)=='dwg') $filetype='application/pdf';
    if (substr($filename,strlen($filename)-3,3)=='eps') $filetype='application/postscript';
    if (substr($filename,strlen($filename)-2,2)=='ai') $filetype='application/postscript';
    if (substr($filename,strlen($filename)-2,2)=='ps') $filetype='application/postscript';
    if (substr($filename,strlen($filename)-3,3)=='prt') $filetype='application/pro_eng';
    if (substr($filename,strlen($filename)-3,3)=='rtf') $filetype='application/rtf';
    if (substr($filename,strlen($filename)-3,3)=='set') $filetype='application/set';
    if (substr($filename,strlen($filename)-3,3)=='stl') $filetype='application/sla';
    if (substr($filename,strlen($filename)-3,3)=='dwg') $filetype='application/solids';
    if (substr($filename,strlen($filename)-4,4)=='step') $filetype='application/step';
    if (substr($filename,strlen($filename)-3,3)=='vda') $filetype='application/vda';
    if (substr($filename,strlen($filename)-3,3)=='mif') $filetype='application/x-mif';
    if (substr($filename,strlen($filename)-3,3)=='dwg') $filetype='application/x-csh';
    if (substr($filename,strlen($filename)-3,3)=='dvi') $filetype='application/x-dvi';
    if (substr($filename,strlen($filename)-3,3)=='hdf') $filetype='application/hdf';
    if (substr($filename,strlen($filename)-5,5)=='latex') $filetype='application/x-latex';
    if (substr($filename,strlen($filename)-3,3)=='dwg') $filetype='application/x-sh';
    if (substr($filename,strlen($filename)-3,3)=='tcl') $filetype='application/x-tcl';
    if (substr($filename,strlen($filename)-3,3)=='tex') $filetype='application/x-tex';
    if (substr($filename,strlen($filename)-3,3)=='man') $filetype='application/x-troff-man';
    if (substr($filename,strlen($filename)-2,2)=='me') $filetype='application/x-troff-me';
    if (substr($filename,strlen($filename)-2,2)=='ms') $filetype='application/x-troff-ms';
    if (substr($filename,strlen($filename)-3,3)=='src') $filetype='application/x-wais-source';
    if (substr($filename,strlen($filename)-5,5)=='bcpio') $filetype='application/x-bcpio';
    if (substr($filename,strlen($filename)-4,4)=='cpio') $filetype='application/x-cpio';
    if (substr($filename,strlen($filename)-4,4)=='gtar') $filetype='application/x-gtar';
    if (substr($filename,strlen($filename)-4,4)=='shar') $filetype='application/x-shar';
    if (substr($filename,strlen($filename)-7,7)=='sv4cpio') $filetype='application/x-sv4cpio';
    if (substr($filename,strlen($filename)-6,6)=='sc4crc') $filetype='application/x-sv4crc';
    if (substr($filename,strlen($filename)-3,3)=='tar') $filetype='application/x-tar';
    if (substr($filename,strlen($filename)-3,3)=='man') $filetype='application/x-ustar';
    if (substr($filename,strlen($filename)-3,3)=='man') $filetype='application/zip';
    if (substr($filename,strlen($filename)-3,3)=='man') $filetype='image/gif';
    if (substr($filename,strlen($filename)-3,3)=='ief') $filetype='image/ief';
    if (substr($filename,strlen($filename)-3,3)=='jpg') $filetype='image/jpeg';
    if (substr($filename,strlen($filename)-4,4)=='jpeg') $filetype='image/jpeg';
    if (substr($filename,strlen($filename)-3,3)=='jpe') $filetype='image/jpeg';
    if (substr($filename,strlen($filename)-3,3)=='cmu') $filetype='image/x-cmu-raster';
    if (substr($filename,strlen($filename)-3,3)=='pnm') $filetype='image/x-portable-anymap';
    if (substr($filename,strlen($filename)-3,3)=='pbm') $filetype='image/x-portable-bitmap';
    if (substr($filename,strlen($filename)-3,3)=='pgm') $filetype='image/x-portable-graymap';
    if (substr($filename,strlen($filename)-3,3)=='ppm') $filetype='image/x-portable-pixmap';
    if (substr($filename,strlen($filename)-3,3)=='rgb') $filetype='image/x-rgb';
    if (substr($filename,strlen($filename)-3,3)=='xbm') $filetype='image/x-xbitmap';
    if (substr($filename,strlen($filename)-3,3)=='xpm') $filetype='image/x-xpixmap';
    if (substr($filename,strlen($filename)-3,3)=='man') $filetype='image/x-xwindowdump';
    if (substr($filename,strlen($filename)-3,3)=='zip') $filetype='multipart/x-zip';
    if (substr($filename,strlen($filename)-2,2)=='gz') $filetype='multipart/x-gzip';
    if (substr($filename,strlen($filename)-4,4)=='gzip') $filetype='multipart/x-gzip';
    if (substr($filename,strlen($filename)-3,3)=='htm') $filetype='text/html';
    if (substr($filename,strlen($filename)-4,4)=='html') $filetype='text/html';
    if (substr($filename,strlen($filename)-3,3)=='txt') $filetype='text/plain';
    if (substr($filename,strlen($filename)-3,3)=='rtx') $filetype='text/richtext';
    if (substr($filename,strlen($filename)-3,3)=='tsv') $filetype='text/tab-separated-value';
    if (substr($filename,strlen($filename)-3,3)=='etx') $filetype='text/x-setext';
    if (substr($filename,strlen($filename)-3,3)=='avi') $filetype='video/msvideo';
    if (substr($filename,strlen($filename)-5,5)=='movie') $filetype='video/x-sgi-movie';
  }
  if( $filetype == "" )
     $filetype = "application/x-unknown-content-type";
  $this->aattach[] = $filename;
  $this->actype[] = $filetype;
  $this->adispo[] = $disposition;
}

/*

Build the email message

@access protected

*/
function BuildMail()
{

    // build the headers
    $this->headers = "";
//  $this->xheaders['To'] = implode( ", ", $this->sendto );
    
    if( count($this->acc) > 0 )
        $this->xheaders['CC'] = implode( ", ", $this->acc );
    
    if( count($this->abcc) > 0 ) 
        $this->xheaders['BCC'] = implode( ", ", $this->abcc );
    

    if( $this->receipt ) {
        if( isset($this->xheaders["Reply-To"] ) )
            $this->xheaders["Disposition-Notification-To"] = $this->xheaders["Reply-To"];
        else 
            $this->xheaders["Disposition-Notification-To"] = $this->xheaders['From'];
    }
    
    if( $this->charset != "" ) {
        $this->xheaders["Mime-Version"] = "1.0";
        $this->xheaders["Content-Type"] = "text/html; charset=$this->charset";
        $this->xheaders["Content-Transfer-Encoding"] = $this->ctencoding;
    }

    $this->xheaders["X-Mailer"] = "Php/libMailv1.3";
    
    // include attached files
    if( count( $this->aattach ) > 0 ) {
        $this->_build_attachement();
    } else {
        $this->fullBody = $this->body;
    }

    reset($this->xheaders);
    while( list( $hdr,$value ) = each( $this->xheaders )  ) {
        if( $hdr != "Subject" )
            $this->headers .= "$hdr: $value\n";
    }
    

}

/*      
    fornat and send the mail
    @access public
    
*/ 
function Send()
{
    $this->BuildMail();
    
    $this->strTo = implode( ", ", $this->sendto );
    
    // envoie du mail
    $res = @mail( $this->strTo, $this->xheaders['Subject'], $this->fullBody, $this->headers );

}



/*
 *      return the whole e-mail , headers + message
 *      can be used for displaying the message in plain text or logging it
 */

function Get()
{
    $this->BuildMail();
    $mail = "To: " . $this->strTo . "\n";
    $mail .= $this->headers . "\n";
    $mail .= $this->fullBody;
    return $mail;
}


/*
    check an email address validity
    @access public
    @param string $address : email address to check
    @return true if email adress is ok
 */
 
function ValidEmail($address)
{
    if( ereg( ".*<(.+)>", $address, $regs ) ) {
        $address = $regs[1];
    }
    if(ereg( "^[^@  ]+@([a-zA-Z0-9\-]+\.)+([a-zA-Z0-9\-]{2}|net|com|gov|mil|org|edu|int)\$",$address) ) 
        return true;
    else
        return false;
}


/*

    check validity of email addresses 
    @param  array $aad - 
    @return if unvalid, output an error message and exit, this may -should- be customized

 */
 
function CheckAdresses( $aad )
{
    for($i=0;$i< count( $aad); $i++ ) {
        if( ! $this->ValidEmail( $aad[$i]) ) {
            echo "L'adresse mail que vous avez choisie est invalide, merci de la resaisir en cliquant sur le bouton 'précédent' de votre navigateur.$aad[$i]";
            exit;
        }
    }
}


/*
 check and encode attach file(s) . internal use only
 @access private
*/

function _build_attachement()
{

    $this->xheaders["Content-Type"] = "multipart/mixed;\n boundary=\"$this->boundary\"";

    $this->fullBody = "This is a multi-part message in MIME format.\n--$this->boundary\n";
    $this->fullBody .= "Content-Type: text/html; charset=$this->charset\nContent-Transfer-Encoding: $this->ctencoding\n\n" . $this->body ."\n";
    
    $sep= chr(13) . chr(10);
    
    $ata= array();
    $k=0;
    
    // for each attached file, do...
    for( $i=0; $i < count( $this->aattach); $i++ ) {
        
        $filename = $this->aattach[$i];
        $basename = basename($filename);
        $ctype = $this->actype[$i]; // content-type
        $disposition = $this->adispo[$i];
        
        if( ! file_exists( $filename) ) {
            echo "Class Mail, method attach : file $filename can't be found"; exit;
        }
        $subhdr= "--$this->boundary\nContent-type: $ctype;\n name=\"$basename\"\nContent-Transfer-Encoding: base64\nContent-Disposition: $disposition;\n  filename=\"$basename\"\n";
        $ata[$k++] = $subhdr;
        // non encoded line length
        $linesz= filesize( $filename)+1;
        $fp= fopen( $filename, 'r' );
        $ata[$k++] = chunk_split(base64_encode(fread( $fp, $linesz)));
        fclose($fp);
    }
    $this->fullBody .= implode($sep, $ata);
}


} // class Mail


?>