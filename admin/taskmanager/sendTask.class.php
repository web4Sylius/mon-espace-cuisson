<?php


class send_task {

private $remotes;
private $remotercv="taskmanager/rcvTask.php";

function __construct($remotes) {
    $this->remotes=$remotes;
}

function test() {
    $post['action']="test";
    
    return $this->sendall($post);
}

function create_task($task,$from) {
    $serialized=serialize($task);
    $post['task']=base64_encode($serialized);
    $post['action']="add";
    $post['from']=$from;
    
    return $this->sendall($post);
}

function get_users() {
    $post['action']="users";
    
    return $this->sendall($post);
}

function get_log($site,$taskid) {
    if(!isset($this->remotes[$site])) return false;

    $post['action']="log";
    $post['taskid']=$taskid;
    
    $xml=$this->request($this->remotes[$site]."/".$this->remotercv,$post);
    $data=$this->read_xml($xml);
    
    return array($site=>unserialize($data['log']));
}

function sendall($post) {
    $return=array();
    foreach($this->remotes as $site=>$remote) {
	$xml=$this->request($remote.$this->remotercv,$post);
	$data=$this->read_xml($xml);
	$return[$site]=$data;
    }
    return $return;
}

function request($serverurl,$post){

    $timeout = 3;
    $old = ini_set('default_socket_timeout', $timeout);

    $contenu = http_build_query($post);
    $headers = $this->http_build_headers( array('Content-Type' => 'application/x-www-form-urlencoded','Content-Length' => strlen( $contenu) ) );
    $options = array( 'http' => array('method' => 'POST','content' => $contenu,'header' => $headers ) );	
    $contexte = stream_context_create( $options );
    
    try{
      $f=fopen($serverurl,"r",false,$contexte);
    }catch(Exception $e){
      return false;
    }
    
    ini_set('default_socket_timeout', $old);
   
    $ret=false;
    if($f==false) return false;
    while(!feof($f))
      $ret.=fgets($f,1024);
    fclose($f);

    return $ret;
}

function http_build_headers( $headers ) {

       $headers_brut = '';

       foreach( $headers as $nom => $valeur ) {
               $headers_brut .= $nom . ': ' . $valeur . "\r\n";
       }

       return $headers_brut;
}

function read_xml($xml) {
    $ret=array();

    $doc=new DOMDocument();
    $doc->loadXML($xml);
    $root=$doc->documentElement;
    if($root->childNodes->length>0) {
	foreach($root->childNodes as $dom_child) {
	    if ($dom_child->nodeType == XML_ELEMENT_NODE) {
		if(isset($ret[$dom_child->nodeName])) {
		    if(!is_array($ret[$dom_child->nodeName])) $ret[$dom_child->nodeName]=array($ret[$dom_child->nodeName]);
		    $ret[$dom_child->nodeName][]=utf8_decode($dom_child->nodeValue);
		}else
		    $ret[$dom_child->nodeName]=utf8_decode($dom_child->nodeValue);
	    }
	}
    }
    
    return $ret;
}

}

?>