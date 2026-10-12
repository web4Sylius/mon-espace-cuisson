<?php

class comparateur {
    private $DB;
    private $articles;
    private $artids;
    private $s;
    
    private $compare_types=array(1,10);
    
    function __construct($DB){
	$this->DB=$DB;
	$this->s=$_COOKIE['s'];
	$this->loadarticles();
    }
    
    function loadarticles($removed=false){
	$return=array();
	$this->artids=array();
	$sql="SELECT c.*, IF(a.typearticleid = 10,1,a.typearticleid) as typearticleid FROM comparateur c INNER JOIN article a USING(artid) WHERE s='".$this->s."'".($removed?"":" AND remove=0")." ORDER BY date";
	$r=$this->DB->query($sql);
	while($art=$this->DB->fetch_array($r)){
	    if($this->compare_type($art['typearticleid'])){
		$return[$art['date']]=new comparateur_article($art);
		$this->artids[]=$art['artid'];
	    }
	}
	$this->articles=$return;
	return $return;
    }
    
    function articles($sorted=false){
	if($sorted){
	    $return=array();
	    foreach($this->articles as $article){
		$return[$article->typearticleid][$article->date]=$article;
	    }
	    return $return;
	}else
	    return $this->articles;
    }
    
    function in_articles($artid){
	return in_array(intval($artid),$this->artids);
    }
    
    function count_typearticle($artid){
	$article=$this->DB->query_first("SELECT typearticleid FROM article WHERE artid=".intval($artid));
	$c=0;
	foreach($this->articles as $article_comp)
	  if($article_comp->typearticleid==$article['typearticleid']) $c++;
	  
	return $c;
    }
    
    function add_article($artid,$userid){
	$artid=intval($artid);
	$userid=intval($userid);
	
	$return=$this->DB->query("INSERT INTO comparateur SET s='".$this->s."', artid=".$artid.", date=".time().", userid=".$userid);
	if($return==false) return false;
	$this->loadarticles();
	return $this->select_article($artid);
    }
    
    function select_article($artid){
	$artid=intval($artid);
	foreach($this->articles as $article){
	    if($article->artid==$artid){
	      return $article;
	    }
	}
	return false;
    }
    
    function remove_article($artid){
	$artid=intval($artid);
	$return=$this->DB->query("UPDATE comparateur SET remove=".time()." WHERE artid=".$artid." AND s='".$this->s."'");
	if($return==false) return false;
	$this->loadarticles();
	return true;
    }
    
    function remove_articles($typearticleid){
	$return=array();
	foreach($this->articles as $article){
	    if($article->typearticleid==$typearticleid)
		if($this->remove_article($article->artid)) $return[]=$article->artid;
	}
	return $return;
    }
    
    function compare_type($typearticleid){
	return in_array($typearticleid,$this->compare_types);
    }
}

class comparateur_article {
    public $artid;
    public $typearticleid;
    public $date;
    public $remove;
    
    function __construct($data){
	$this->artid=intval($data['artid']);
	$this->typearticleid=intval($data['typearticleid']);
	$this->date=$data['date'];
	$this->remove=$data['remove'];
    }
}

?>