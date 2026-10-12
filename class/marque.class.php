<?php
class marque{

	private $DB;
	public $imghost ="https://www.mon-espace-cuisson.com";
	private $langue;

    public $marqueid;
    public $libelle;
    public $link;
    public $archive_link;
    public $image;
    public $image_small;
    public $artcount;
    public $type;
    
    public $promo_url;
    public $promo_link;
    public $promo_libelle;
    public $promo_description;
    public $promo_ref_title;
    
    public $archive_url;
    public $archive_title;
    public $archive_description;
    
    public $gamme_url;
    public $gamme_link;
    public $gamme_libelle;
    public $gamme_description;
    public $gamme_ref_title;

    public function __construct($DB, $marqueid = 0, $langue = "FR"){
		$this->DB     = $DB;
		$this->langue = $langue;

		if($marqueid > 0) $this->load($marqueid);
    }

    function load($marqueid){
	if(!is_array($marqueid)){
	    $r=$this->DB->query_first("SELECT * FROM marque WHERE marqueid=".$marqueid);
	    if($r['marqueid']!=$marqueid) return false;
	}else
	    $r=$marqueid;

	$this->marqueid=$r['marqueid'];
	$this->libelle=$r['libelle'];
	$this->type=$r['type'.$this->langue];
	if($r['url'.$this->langue]=="") $r['url'.$this->langue]=$this->libelle;
	$this->link=url_rewrite($r['url'.$this->langue])."-".$this->marqueid.".htm";
	$this->image=$this->imghost."/configurations/www.ma-cave-a-vin.fr/images/marques/".$this->marqueid.".".$r['image'];
	$this->image_small=$this->imghost."/configurations/www.ma-cave-a-vin.fr/images/marques/br/".$this->marqueid.".".$r['image'];
    }
    
    function promo_load() {
	$promo=$this->DB->query_first("SELECT * FROM marque_promo WHERE marqueid=".$this->marqueid);
	$this->promo_url=$promo['url'.$this->langue];
	$this->promo_link=$promo['link'.$this->langue];
	$this->promo_libelle=$promo['title'.$this->langue];
	$this->promo_description=$promo['description'.$this->langue];
	$this->promo_ref_title=$promo['ref_title'.$this->langue];
    }
    
    function gamme_load($gamme) {
	$g=$this->DB->query_first("SELECT * FROM marque_gamme WHERE marqueid=".$this->marqueid." AND gamme='".$gamme."'");
	$this->gamme_url=$g['url'.$this->langue];
	$this->gamme_link=$g['link'.$this->langue];
	$this->gamme_libelle=$g['title'.$this->langue];
	$this->gamme_description=$g['description'.$this->langue];
	$this->gamme_ref_title=$g['ref_title'.$this->langue];
    }
    
    function archive_from_url($url){
	$url=str_replace("-archive.html","",$url);
	$url=stripslashes($url);
	$data=$this->DB->query_first("SELECT marqueid FROM marque_archive WHERE url".$this->langue."='".mysqli_real_escape_string($this->DB->link_id,$url)."'");
	if($data==""){
	    $sp=explode("-",$url);
	    $last=array_pop($sp);
	    $req=$this->DB->query("SELECT marqueid,libelle FROM marque WHERE libelle LIKE '%".$last."%'");
	    if($this->DB->num_rows($req)==0) return false;
	    $okmarque=false;
	    while($data=$this->DB->fetch_array($req)){
		$marque_url="-".url_rewrite($data['libelle']);
		if(strpos($url,$marque_url)>-1){
		    $okmarque=true;
		    break;
		}		    
	    }
	    
	    if($okmarque)
		$this->archive_categorie=str_replace($marque_url,"",$url);
	    else{
		$this->archive_categorie=null;
		return false;
	    }
	}
	$this->load($data['marqueid']);
	$this->archive_load();
	return $this->marqueid;
    }
    
    function archive_load(){
	$archive=$this->DB->query_first("SELECT * FROM marque_archive WHERE marqueid=".$this->marqueid);
	if(!is_null($archive)){
		$this->archive_url=strtolower($archive['url'.$this->langue]."-archive.html");
	}else{
		$this->archive_url="/";
	}
	
	$this->archive_description=$archive['description'.$this->langue];
	$this->archive_title=$archive['title'.$this->langue];
    }
    
    function archive_categorie_url($categorie){
	return $categorie->archive_url."-".url_rewrite($this->libelle)."-archive.html";
    }
    
    function listall($order = "marqueid", $pro = null, $site_code = null, $active = 1){

    	if(is_array($active)){ //concerne surtout la page plan_site.php
    		$i=0;
    		$sqlActive = 'IN (';
    		foreach ($active as $activ) {
    			if($i==0) 
    				$sqlActive.=intval($activ);
    			else
    				$sqlActive .=','.intval($activ);
    			$i++;
    		}
    		$sqlActive .=')';

    	}else{// pour les autres pages ou on liste les marques
    		$active = intval($active);
    		$sqlActive = "=".$active;
    	}

		$sql = "SELECT *";
		$sql .= " FROM marque";
		$sql .= " WHERE 1";
		$sql .= " ORDER BY ".$order;

		if(!is_null($site_code)){

			$sqlsite = " AND (";
			$sqlsite .= "		SELECT active";
			$sqlsite .= "		FROM article_site a_s";
			$sqlsite .= "		WHERE 1";
			$sqlsite .= "		AND a_s.artid = a.artid";
			$sqlsite .= "		AND a_s.site_code = '".$site_code."'";
			$sqlsite .= "		AND a_s.active ".$sqlActive;
			$sqlsite .= "		LIMIT 1";
			$sqlsite .= "	 ) ".$sqlActive;
		} 

		if(!is_null($pro)){

			$sql = "SELECT m.*";
			$sql .= " FROM article a";
			$sql .= " INNER JOIN article_marque am USING (artid)";
			$sql .= " INNER JOIN marque m USING (marqueid)";
			$sql .= " WHERE pro IN ('".intval($pro)."','3')";
			$sql .= $sqlsite;
			$sql .= " GROUP BY m.marqueid";
			$sql .= " ORDER BY ".$order;
		} 
		$r = $this->DB->query($sql);

		while($m = $this->DB->fetch_array($r)){
		    $mclass = new marque($this->DB,$m,$this->langue);

		    $return[$m['marqueid']] = $mclass;
		}

		return $return;
    }
    
     /*
     * Retourne une liste de marques
     * 
     * @params array $params : paramètres pour filtration de recherche
     * 
     * @return array $return : tableau de marques
     */
    function getListe($params = array()){
         $sql = "SELECT *";
         $sql .= " FROM marque";
         $sql .= isset($params['active']) && $params['active'] == true ? " INNER JOIN display_marque USING (marqueid)" : "";
         $sql .= " WHERE 1";
         $sql .= isset($params['liste_ids']) && !empty($params['liste_ids']) ? " AND marqueid IN (".$params['liste_ids'].")" : "";
         $sql .= isset($params['tri']) && !empty($params['tri']) ? " ORDER BY ".$params['tri'] : "";
         $r = $this->DB->query($sql);
         
         while($la_marque = $this->DB->fetch_array($r)){
              $return[$la_marque['marqueid']] = $la_marque;
         }
         
         return $return;
    }
    
    function logo($width,$height){
	return "/marques/".$this->marqueid."_".$width."_".$height.".jpg";
    }
}


?>