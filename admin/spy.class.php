<?php

class spy {
    private $serverurl="http://94.23.211.169/spy/sync.php";
    private $data;

    function __construct($username,$pass){
		$this->serverurl.="?username=".$username."&password=".$pass;
    }

    function request($action,$other=""){
		$timeout = 3;
		$old = ini_set('default_socket_timeout', $timeout);
		if(is_array($other)){
		  $contenu = http_build_query($other);
		  $headers = $this->http_build_headers( array('Content-Type' => 'application/x-www-form-urlencoded','Content-Length' => strlen( $contenu) ) );
		  $options = array( 'http' => array('method' => 'POST','content' => $contenu,'header' => $headers ) );	
		  $contexte = stream_context_create( $options );
		  $other="";
		  try{
			$f=@fopen($this->serverurl."&action=".$action.$other,"r",false,$contexte);
		  }catch(Exception $e){
			return false;
		  }
		}else{
		  try{
			 // echo $this->serverurl."&action=".$action.$other;
			$f=@fopen($this->serverurl."&action=".$action.$other,"r");
		  }catch(Exception $e){
			return false;
		  }
		}
		ini_set('default_socket_timeout', $old);
		  
		$this->data=$ret=false;
		if($f==false) return false;
		while(!feof($f))
		  $ret.=fgets($f,1024);
		fclose($f);
		if($ret !=""){
			$this->data=gzinflate($ret);
		}else{
			$this->data="";
		}
		
		// echo $this->data;
		return $this->data;
    }
    
    function data(){
		return $this->data;
    }

    function getcsv(){
		$return=array();
		
		$lines=explode("\r\n",$this->data);
		foreach($lines as $key=>$line){
			$fields=explode("\",\"",$line);
			$fields[0]=substr($fields[0],1);
			$last=count($fields);
	// 	    $fields[$last]=substr($fields[$last],0,-1);
			$return[$key]=$fields;
		}
		
		return $return;
    }
    
    function http_build_headers( $headers ) {
		$headers_brut = '';
		foreach( $headers as $nom => $valeur ) {
		  $headers_brut .= $nom . ': ' . $valeur . "\r\n";
		}
		return $headers_brut;
    }

}

?>