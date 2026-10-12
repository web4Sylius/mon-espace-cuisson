<?php

class requetage{
    private $DB;
    private $langue;

    private $display_order=array(
      "titre" => "[?typeproduit] [?usage] [?marque][?posevitrine][?typecadre] [?capacite]",
      "texte" => "[?typeproduit] [?usage] [?marque][?posevitrine][?typecadre] [?capacite], [?finition]"
    );
    private $categorie;
    private $kwcache=array();
    private $txtcache=array();
    private $valuetxt;
    private $title_parts=array();
    private $used_regex=array();
    private $url_construct=array(
      "typesrep1" => array("usage","marque","capacite","finition"),
      "typesrep2" => array("posevitrine"),
      "typesrep3" => array(),
      "typesrep4" => array("typecadre"),
      "typesrep5" => array()
    );
    private $url;
    
    public $requetes;
    
    
    function __construct($DB,$langue="FR",$categorie=null){
	$this->DB=$DB;
	$this->langue=$langue;
	$this->categorie=$categorie;
	
	$req=$this->DB->query("SELECT type,value,kw".$this->langue." AS kw,txt".$this->langue." AS txt FROM requetage");
	while($r=$this->DB->fetch_array($req)){
	    $kwtmp[$r['type']][$r['kw']]=$r['value'];
	    $this->txtcache[$r['kw']]=$r['txt'];
	    $this->valuetxt[$r['value']]=$r['txt'];
	}
	
	$this->kwcache=array('typeproduit'=>$kwtmp['typeproduit']);
	unset($kwtmp['typeproduit']);
	foreach($kwtmp as $type=>$values){
	    $this->kwcache[$type]=$values;
	}
    }
    
    function requetes($requetes){
	$this->requetes=$requetes;
	foreach($this->requetes as $type=>$value){
	    if($type=="capacite"){
		$txt="capacite1";
		if($value==0){
		    $txt="capacite0";
		    $value=1;
		}
	    
		$this->title_parts[$type]=sprintf($this->valuetxt[$txt],$this->categorie->capacites[$value],$this->categorie->capacites[$value+1]);
	    }else
		$this->title_parts[$type]=$this->valuetxt[$value];
	}
    }
    
    function analyse($url){
	$return=array();
	$this->url=$url;
	$this->title_parts=array();
	$this->used_regex=array();
	foreach($this->kwcache as $type=>$kwlist){
	    if($return['typeproduit']=="typesrep1" && $type=="posevitrine") continue;
	    if($return['typeproduit']=="typesrep2" && $type=="pose") continue;
	    foreach($kwlist as $kw=>$value){
		$regex=str_replace("%s","(\w+)",$kw);
		if(preg_match("/".$regex."/i",$url,$matches)){
		    $this->used_regex[$type]=$regex;
		    if(count($matches)==1){
			$return[$type]=trim($value);
			$this->title_parts[$type]=$this->txtcache[$kw];
		    }else{
			$return[$type]=array_search($matches[1],$this->categorie->capacites);
			$this->title_parts[$type]=vsprintf($this->txtcache[$kw],array_slice($matches,1));
		    }
		}
	    }
	}
	$this->requetes=$return;
	return $return;
    }
    
    function valid_url(){
	$typeproduit=$this->requetes['typeproduit'];
	$regex=array($this->used_regex['typeproduit']);
	foreach($this->url_construct[$typeproduit] as $type){
	    $regex[]=$this->used_regex[$type];
	    if($type=="capacite"){
		if($this->requetes["capacite"]>count($this->categorie->capacites)) return false;
	    }
	}
	
	if(count($this->used_regex) != count($this->url_construct[$typeproduit])+1) return false;
	return (bool)preg_match("/^".implode("-",$regex)."$/",$this->url);
    }
    
    function titre(){
	    $return = preg_replace_callback("/\[\?(.*?)\]/", 
	    	function($matches){
	     		$caracts = array("[","?","]");
	     		foreach ($matches as $match) {
	     			$match = str_replace($caracts, '', $match);
	     			return $this->title_parts[$match];
	     		}
	     	}, 
	     	$this->display_order['titre']);
		//$return=preg_replace("/\[\?(.*?)\]/ie","\$this->title_parts[$1]",$this->display_order['titre']);
		return trim($return);
    }
    
    function texte(){
    	$return = preg_replace_callback("/\[\?(.*?)\]/", 
	    	function($matches){
	     		$caracts = array("[","?","]");
	     		foreach ($matches as $match) {
	     			$match = str_replace($caracts, '', $match);
	     			return $this->title_parts[$match];
	     		}
	     	}, 
	     	$this->display_order['texte']);
		return trim($return);
    }
    
    function url($requetes=null){
	if(is_null($requetes)) $requetes=$this->requetes;
	$typeproduit=$requetes['typeproduit'];
	$url=array(array_search($typeproduit,$this->kwcache['typeproduit']));
	foreach($this->url_construct[$typeproduit] as $type){
	    if($type!="capacite"){
		$url[]=array_search($requetes[$type],$this->kwcache[$type]);
	    }else{
		$capacite=$requetes[$type];
		$key="capacite1";
		if($capacite==0){
		    $key="capacite0";
		    $capacite++;
		}

		$url[]=sprintf(array_search($key,$this->kwcache[$type]),$this->categorie->capacites[$capacite],$this->categorie->capacites[$capacite+1]);
	    }
	}
	return implode("-",$url);
    }
    
    function titre_part($type){
	return $this->title_parts[$type];
    }
    
    function random_link($typeproduit,$usage=null){
	$return=array();
	
	$this->requetes=array();
	$this->requetes['typeproduit']=$typeproduit;
	$this->title_parts=array('typeproduit'=>$this->valuetxt[$typeproduit]);
	foreach($this->url_construct[$typeproduit] as $type){
	    if(!is_null($usage) && $type=="usage")
		$rand=$usage;
	    else
		$rand=$this->random_value($type);
		
	    $this->requetes[$type]=$rand;
	    
	    if($type=="capacite"){
		$value="capacite1";
		if($rand==0){
		    $value="capacite0";
		    $rand=1;
		}

		$this->title_parts[$type]=sprintf($this->valuetxt[$value],$this->categorie->capacites[$rand],$this->categorie->capacites[$rand+1]);
	    }else{
		$this->title_parts[$type]=$this->valuetxt[$rand];
	    }
	}
	return $this->requetes;
    }
    
    function random_value($type){
	if($type!="capacite"){
	    $keys=array_keys($this->kwcache[$type]);
	    $max=count($keys);
	    $rand=rand(0,$max-1);
	    return $this->kwcache[$type][$keys[$rand]];
	}else{
	    $max=count($this->categorie->capacites);
	    $rand=rand(0,9);
	    return $rand;
	}
    }
    
    function from_cache($typeproduit,$usage,$nb){
	$return=array();
	$sql="SELECT * FROM requetage_cache WHERE articles>0 AND requetes LIKE '%".$typeproduit."%'";
	if(in_array("usage",$this->url_construct[$typeproduit])) $sql.=" AND requetes LIKE '%".$usage."%'";
	$sql.=" LIMIT ".$nb;

	$req=$this->DB->query($sql);
	while($cache=$this->DB->fetch_array($req)){
	    $r=new requetage($this->DB,$this->langue,$this->categorie);
	    $r->requetes(unserialize($cache['requetes']));
	    $return[]=$r;
	}
	return $return;
    }
    
    function get_cache(){
	$infos=$this->DB->query_first("SELECT * FROM requetage_cache WHERE requetes='".serialize($this->requetes)."'");
	if($infos=="") return false;
	if($infos['timestamp']<time()-86400) return false;
	return intval($infos['articles']);
    }
    
    function update_cache($articles){
	$infos=$this->DB->query_first("SELECT * FROM requetage_cache WHERE requetes='".serialize($this->requetes)."'");
	if($infos==""){
	    return $this->DB->query("INSERT INTO requetage_cache SET requetes='".serialize($this->requetes)."', timestamp=".time().", articles=".intval($articles));
	}else{
	    return $this->DB->query("UPDATE requetage_cache SET timestamp=".time().", articles=".intval($articles)." WHERE requetes='".serialize($this->requetes)."'");
	}
    }
    
    function typesproduits(){
	return $this->kwcache["typeproduit"];
    }
    
    function usages($typeproduit){
	if(!in_array("usage",$this->url_construct[$typeproduit])) return false;
	return $this->kwcache["usage"];
    }
}


?>