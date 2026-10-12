<?php

class devises {
    
    private $DB;
    private $list;
    private $changeurl="http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml";
    private $change;
    private $dailycache;
    
    public $defaut="EUR";

    function __construct($DB){
	$this->DB=$DB;
	$req=$this->DB->query("SELECT * FROM devises");
	while($d=$this->DB->fetch_array($req)){
	    $this->list[$d['code']]=$d;
	    $this->change[$d['code']]=array("achat_site"=>$d['taux_achat_site'],"vente_site"=>$d['taux_vente_site'],"achat_compta"=>$d['taux_achat_compta'],"vente_compta"=>$d['taux_vente_compta']);
	}
    }
    
    function symbole($code,$chr=false){
	return ($chr?chr($this->list[$code]['chr']):$this->list[$code]['symbole']);
    }
    
    function listcodes(){
	return array_keys($this->list);
    }
    
    function dailychange() {
	$doc=new DOMDocument();
	$loaded=$doc->load($this->changeurl);
	if(!$loaded) return false;
	
	$currencies=$doc->getElementsByTagName("Cube");
	foreach($currencies as $currency) {
	    $currencycode=$currency->getAttribute("currency");
	    if($currencycode!="") $return[$currencycode]=1/$currency->getAttribute("rate");
	}
	
	ksort($return);
	$this->dailycache=$return;
	return $return;
    }
    
    function getchange($type="site",$av="vente"){
	$return=array();
	switch($type){
	    case "site":
	    case "compta":
	    foreach($this->change as $devise=>$values)
		$return[$devise]=$values[$av."_".$type];
	    return $return;
	    
	    case "daily":
	    if(!isset($this->dailycache)) $this->dailychange();
	    return $this->dailycache;
	}
	return false;
    }
    
    function change($value,$devise,$type="site",$av="vente"){
	if(strlen($devise)!=3) return false;
	$change=$this->getchange($type,$av);
	if(isset($change[$devise])){
	    return floatval($value)/$change[$devise];
	}else
	    return false;
    }
    
    function in_euro($value,$devise,$type="site",$av="vente"){
    	if(strlen($devise)!=3) return false;
	$change=$this->getchange($type,$av);

	if(isset($change[$devise])){
	    return floatval($value)*$change[$devise];
	}else
	    return false;
    }
}

?>