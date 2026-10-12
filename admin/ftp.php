<?php

class ftp{

var $server;
var $user;
var $pass;

var $error;

var $ftpid=false;

function connect(){
        if($this->ftpid!=false) return true;
       $this->ftpid=ftp_connect($this->server);
       if($this->ftpid==false){
            $this->error="Connexion impossible au serveur";
             return false;
       }
       $result=ftp_login($this->ftpid,$this->user,$this->pass);
       if($result==false){
            $this->error="Erreur de login";
            return false;
       }
       return true;
}

function listdir($dir=""){
       if($this->connect()==false) return false;

       return ftp_nlist($this->ftpid,$dir);
}

function put($source,$destination,$passiv_mode=false){
       if($this->connect($passiv_mode)==false) return false;
       if(!file_exists($source)){
            $this->error="Le fichier source n'existe pas";
            return false;
       }
       $this->curdirfiles=$this->listdir();
       //if($passiv_mode) ftp_pasv($this->ftpid, true);
       
       return ftp_put($this->ftpid,$destination,$source,FTP_BINARY);
}

function get($source,$destination){
      if($this->connect()==false) return false;
      //return ftp_get($this->ftpid,$source,$destination,FTP_ASCII);
      return ftp_get($this->ftpid,$destination,$source,FTP_BINARY);
}

function quit(){
         $result=ftp_quit($this->ftpid);
         $this->ftpid=false;
}

}

?>