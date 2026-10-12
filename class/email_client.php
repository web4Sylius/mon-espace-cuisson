<?php


class email_client {

private $DB;
private $body;
private $body_original;
private $subject;
private $libelle;

function __construct($DB,&$template,$emailid,$emailcode=null,$vars=null){
    $this->DB=$DB;
    $template=clone $template;
    $template->changesite($template->site_code(),$template->langue);
    
    $sql="SELECT sujet".$template->langue." AS sujet,contenu".$template->langue." AS contenu,template,libelle FROM emails_clients WHERE 1";
    if(!is_null($emailcode))
	$sql.=" AND emailcode='".$emailcode."' AND site_code='".$template->site_code()."'";
    else
	$sql.=" AND emailid=".intval($emailid);

    $data=$this->DB->query_first($sql);
    
    if(!is_null($vars)){
    	foreach($vars as $var=>$value){
    	    $template->parts[$var]=$value;
    	}
    }
    
    $template->parts["autocolor"]="#a88d5c";
    $template->parts["urlsite"]=str_replace("https://","",$template->translate("/site/url"));
    /*if($template->pro){
      $template->parts["pro"]="_pro";
      $template->parts["urlsite"]=str_replace("www","pro",$template->parts["urlsite"]);
      $template->parts["autocolor"]="#0393c2";
    }*/
    
    $template->parts["body"]="";
    $template->parts["mail_body"]="";
    $template->parts["subject"]="";
    
    $template->evalvartemplate($data['sujet'],"subject");
    $this->subject=$template->parts["subject"];
    $this->libelle=$data['libelle'];

    $template->evalvartemplate($data['contenu'],"mail_body");
    // TSI : dans le cas ou il n'y a pas de template email definit dans le champ template de la table email client, pour l'email code demandé: on prend MailGenerique
    $htmltemplate=$data['template']==""?"MailGenerique":$data['template'];
    $template->evaltemplate($htmltemplate,"body");

    $template->parts["body"]=stripslashes($template->parts["body"]);
    
    $path=pathinfo($_SERVER['SCRIPT_NAME'],PATHINFO_DIRNAME);
    if($path!="/"){
		$nbpar=substr_count($path,"/");
		$template->parts["body"]=$this->change_path($template->parts["body"],$nbpar);
		$body_original = $this->change_path($template->parts["body"],$nbpar+1);
    }
    
    $this->body=$template->parts["body"];
	$this->body_original=$body_original;
}

function change_path($body,$nbparents){
    $parentpath=str_repeat("../",$nbparents);
    // $body=preg_replace('/< *img([^>]*)src *= *["]?([^"]*)"/i','<img$1src="'.$parentpath.'$2"',$body);
	$body=preg_replace('/< *img([^>]*)src *= *["]?([^"]*)"/i','<img$1src="$2"',$body);
    return $body;
}

function __get($param){
    return $this->$param;
}

}
