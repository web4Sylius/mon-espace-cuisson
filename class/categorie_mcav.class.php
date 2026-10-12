<?php
class categorie{

  private $DB     = null;
  private $langue = "FR";

  public $loaded;
  public $imghost     = "";
  public $hauteurs    = array(0,700,900,1300,1500,1700,3000);
  public $largeurs    = array(0,300,500,550,600,650,700,1000,1600,2000,3000);
  public $profondeurs = array(0,500,550,600,650,700,800,10000);
  public $origines    = array(0=>"France",1=>"Europe",2=>"Asie",3=>"USA");
  public $listeprix   = array(0,100,250,400,800,1200,1600,2000,3000,4000,6000,10000,25000,100000);
  public $capacites   = array(0,10,20,50,100,150,200,250,350,500,750,1000,1500,2000,4000,6000,10000);

  public $catid=null;
  public $parentid;
  public $libelle;
  public $description;
  public $description2;
  public $ref_title;
  public $image;
  public $image_small;
  public $image_cat;
  public $position;
  public $artcount=0;
  public $rechid;
  public $onglet;
  public $color;
  public $defaultorder;
  public $url;
  
  public $promo_url;
  public $promo_libelle;
  public $promo_description;
  public $promo_ref_title;
  public $promo_type;
  
  public $idkdos_url;
  public $idkdos_libelle;
  public $idkdos_description;
  public $idkdos_ref_title;
  
  public $gamme_url;
  public $gamme_libelle;
  public $gamme_description;
  public $gamme_ref_title;
  
  public $archive_url;
  public $archive_title;
  
  public $subcatids        = array();
  public $marqueids        = array();
  public $usageids         = array();
  public $capaciteids      = array();
  public $emplacementids   = array();
  public $typeporteids     = array();
  public $colorisids       = array();
  public $colorisencastrableids = array();
  public $moderangementids = array();
  public $finitionids      = array();
  public $nbtemperature    = null;
  public $largeurids       = array();
  public $hauteurids       = array();
  public $profondeurids    = array();
  public $prixids          = array();
  public $prix             = array();
  public $origineids       = array();
  public $energy_label     = array();
  public $sensporteids     = array();
  public $serrure          = array();
  public $pagegamme        = array();
  public $typearticleids   = array();

  public $mintemp=null;
  public $maxtemp=null;
  public $esthetique=null;
  public $bonsplans=array();
  public $secondchoix=null;
  public $largeurmin=null;
  public $largeurmam=null;
  public $hauteurmin=null;
  public $hauteurmax=null;
  public $profondeurmin=null;
  public $profondeurmax=null;
  public $pro=null;
  public $site_code=array("base");
  public $active=array(1);
  
  private $parents=array();

  function __construct($DB,$catid=0,$langue="FR"){
      $this->DB=$DB;
      $this->langue=$langue;
      $this->loaded=true;
      if(intval($catid)>0) 
	  $this->load($catid);
      else
	  $this->catid=0;

//       $this->parents();
  }

  function getMenus(){
      $sql = "SELECT idmenu, position, libelle" . $this->langue . " AS libelle, display" . $this->langue . " AS display, description" . $this->langue . " AS description, url" . $this->langue . " as url,displayResp".$this->langue." as displayResp, selection, gamme, categorie, capacite, prix, marque";
      $sql .= " FROM menus";
      $sql .= " ORDER BY position";
      $req = $this->DB->query($sql);
      while($m = $this->DB->fetch_array($req))
      {
          $return[$m['idmenu']] = $m;
      }
      return $return;
  }
  
  function getMenu($idMenu){
      $sql = "SELECT catid, libelle".$this->langue." as libelle";
      $sql .= " FROM categorie";
      $sql .= " WHERE menu_appartenance = ".$idMenu;
	  $sql .= " AND affichage_menu = 1";
      $sql .= " ORDER by position";
      $req = $this->DB->query($sql);
      while($row = $this->DB->fetch_array($req)){
          $aReturn[] = $row;
      }

      return $aReturn;
  }

  function getArticles($idCategorie){

      $champs = array(
          'a.artid',
          'rf.finitionid as finition_id',
          'rf.nom'.$this->langue.' as finition_libelle',
          'c1.catid as sous_sous_cat_id',
          'c1.libelle'.$this->langue.' as sous_sous_cat_libelle',
          'c1.position as sous_sous_cat_position',
          'c2.catid as sous_cat_id',
          'c2.libelle'.$this->langue.' as sous_cat_libelle',
          'c2.position as sous_cat_position',
          'c3.catid as cat_id',
          'c4.catid as sous_sous_sous_cat_id',
          'c3.libelle'.$this->langue.' as cat_libelle',
          'c3.position as cat_position',
          'm.marqueid as marque_id',
          'm.libelle as marque_libelle',
          //'acave.capacite_max as capacite',
          'a.energy_label as classe_energetique'
      );

      $sql = "SELECT ".implode(',',$champs);
      $sql .= " FROM article a";
      $sql .= " LEFT JOIN articlerecherche_finition af ON (af.artid = a.artid)";
      $sql .= " LEFT JOIN recherche_finition rf ON (rf.finitionid = af.finitionid)";
      $sql .= " INNER JOIN article_site asite ON (asite.artid = a.artid)";
      $sql .= " INNER JOIN article_marque am ON (am.artid = a.artid)";
     // $sql .= " INNER JOIN article_cave acave ON (acave.artid = a.artid)";
      $sql .= " INNER JOIN marque m ON (m.marqueid = am.marqueid)";
      $sql .= " INNER JOIN categorie c1 ON (c1.catid = a.catid)";
      $sql .= " LEFT JOIN categorie c2 ON (c2.catid = c1.parentid)";
      $sql .= " LEFT JOIN categorie c3 ON (c3.catid = c2.parentid)";
      $sql .= " LEFT JOIN categorie c4 ON (c4.catid = c3.parentid)";
      $sql .= " WHERE 1";
      $sql .= " AND asite.site_code IN ('".implode("','",$this->site_code)."')";
      $sql .= " AND asite.active = 1";
//       $sql .= " AND a.commandable = '1'";
      if(!is_null($this->pro))
	  $sql .= " AND a.pro IN ('".intval($this->pro)."','3')";
      $sql .= " AND (";
      $sql .= "       c1.catid = ".$idCategorie;
      $sql .= "     OR";
      $sql .= "       c2.catid = ".$idCategorie;
      $sql .= "     OR";
      $sql .= "       c3.catid = ".$idCategorie;
      $sql .= "     )";
      $sql .= " GROUP BY a.artid";
      $sql .= " ORDER BY m.libelle";
      $req = $this->DB->query($sql);
      while($row = $this->DB->fetch_array($req)){
          $aReturn[] = $row;
      }

      return $aReturn;
  }

  function load($catid){
      if(is_null($this->DB)) return false;
      if($catid==0) return true;

      if(is_array($catid)){
    	  $c=$catid;
      }else{
    	  $c=$this->DB->query_first("SELECT * FROM categorie WHERE catid=".intval($catid));
    	  if($c['catid']!=$catid) return false;
      }

      $this->internal_load($c);
      return true;
  }

  function load_from_url($url,&$type=null){
        if($url=="") return false;

        if(is_null($type)){
                $caterror=false;
                $path=explode("/",$url);
                $first=array_shift($path);
                $path[0]=$first."/".$path[0];
                $path=array_reverse($path);

                $this->page=1;
                if(preg_match("/^([0-9]+)\.htm$/",$path[0],$m)){
                    $this->page=$m[1];
                    array_shift($path);
                }else{
                    $path[0]=str_replace(".htm","",$path[0]);
                }

                foreach($path as $part){
                    if(preg_match("/^([^\.]+)-m$/",$part,$m)){
                        $marque=$this->DB->query_first("SELECT marqueid FROM marque WHERE url_rewrite(libelle) LIKE '".mysqli_real_escape_string($this->DB->link_id,$m[1])."' ");
                        if($marque=="") $caterror=true;
                        $this->marqueids=array($marque['marqueid']);
                    }else{
                        $c=$this->DB->query_first("SELECT catid FROM categorie WHERE REPLACE(url".$this->langue.",'.htm','') LIKE '".mysqli_real_escape_string($this->DB->link_id,$part)."'"); 
                        if($c=="") $caterror=true;
                        if(is_null($cat)) $cat=$part;
                    }
                }

                if(!$caterror){
                    $c=$this->DB->query_first("SELECT * FROM categorie WHERE url".$this->langue." LIKE '%".mysqli_real_escape_string($this->DB->link_id,$cat)."%' ORDER BY LENGTH(url".$this->langue.")");
                    if($c!=""){
                        /*$this->subcatids=array($c['catid']);
                        while($c['parentid']!=0){
                            $c=$this->DB->query_first("SELECT * FROM categorie WHERE catid=".$c['parentid']);
                        }
                            if($this->subcatids[0]==$c['catid']) $this->subcatids=array();
                        */
                        $this->internal_load($c);
                        $type="categorie";
                        return true;
                        }
                }
        }
      
      
        if(is_null($type) || $type=="promo"){
                $c=$this->DB->query_first("SELECT c.* FROM categorie c INNER JOIN categorie_promo cp USING (catid) WHERE cp.url".$this->langue."='".mysqli_real_escape_string($this->DB->link_id,$url)."'");
                if($c['catid']!=""){
                    $this->internal_load($c);
                    $this->promo_load("categorie");
                    $type="promo";
                    return true;
                }
        }
        
        if(is_null($type)){
                $c=$this->DB->query_first("SELECT * FROM marque_promo WHERE url".$this->langue."='".mysqli_real_escape_string($this->DB->link_id,$url)."'");
                if($c['marqueid']!=""){
                    $this->internal_load(array());
                    $this->marqueids=array($c['marqueid']);
                    $this->promo_load("marque");
                    $this->catid=0;
                    $type="promo";
                    return true;
                }
        }
      
        
        
        if(is_null($type) || $type=="gamme"){
            $c=$this->DB->query_first("SELECT c.*,cg.gamme FROM categorie_gamme cg LEFT JOIN categorie c USING(catid) WHERE cg.url".$this->langue."='".mysqli_real_escape_string($this->DB->link_id,$url)."'");
            if($c!=""){
                $this->internal_load($c);
                $this->pagegamme=array($c['gamme']);
                $this->gamme_load();
                $type="gamme";
                return true;
            }
        }
        if(is_null($type) || $type=="idkdos"){
            $c=$this->DB->query_first("SELECT c.* FROM categorie c INNER JOIN categorie_idkdos cp USING (catid) WHERE cp.url".$this->langue."='".mysqli_real_escape_string($this->DB->link_id,$url)."'");
            if($c['catid']!=""){
                $this->internal_load($c);
                $this->idkdos_load("categorie");
                $type="idkdos";
                return true;
            }

            $c=$this->DB->query_first("SELECT * FROM `categorie_idkdos` WHERE '".mysqli_real_escape_string($this->DB->link_id,$url)."' REGEXP replace(`url".$this->langue."`,'%u','([0-9]+)')");
            if($c!=""){
                $this->prix=sscanf($url,$c['url'.$this->langue]);
                if(!in_array($this->prix[0],$this->listeprix) || !in_array($this->prix[1],$this->listeprix)) return false;
                $this->idkdos_load("prix");
                $type="idkdos";
                return true;
            }
        }
        
        if(is_null($type)){
                $c=$this->DB->query_first("SELECT * FROM marque_gamme WHERE url".$this->langue."='".mysqli_real_escape_string($this->DB->link_id,$url)."'");
                if($c['marqueid']!=""){
                    $this->internal_load(array());
                    $this->marqueids=array($c['marqueid']);
                    $this->pagegamme=array($c['gamme']);
                    $this->gamme_load("marque");
                    $this->catid=0;
                    $type="gamme";
                    return true;
                }
        }
      
        if(is_null($type) || $type=="archive"){
            $c=$this->DB->query_first("SELECT c.* FROM categorie c INNER JOIN categorie_archive ca USING(catid) WHERE ca.url".$this->langue."='".mysqli_real_escape_string($this->DB->link_id,$url)."'");
            if($c!=""){
                $this->internal_load($c);
                $this->archive_load();
                $type="archive";
                return true;
            }
        }
        
        return false;
  }

  function internal_load($c){
      $this->catid=$c['catid'];
      $this->parentid=$c['parentid'];

      $this->libelle=$c['libelle'.$this->langue];
      $this->description=$c['description'.$this->langue];
      $this->description2=$c['description2'.$this->langue];
      $this->image=$this->imghost."/configurations/www.mon-espace-cuisson.com/images/categories/FR/".$c['catid'].".".$c['imageFR'];
      $this->image_small=$this->imghost."/configurations/www.mon-espace-cuisson.com/images/categories/FR/br/".$c['catid'].".".$c['imageFR'];
      $this->image_cat=$this->imghost."/configurations/www.mon-espace-cuisson.com/images/categories/FR/description/".$c['catid'].".".$c['imageFR'];
      $this->position=$c['position'];
      $this->rechid=$c['rechid'];
      $this->onglet=explode(",",$c['onglet']);
      $this->color=$c['color'];
      $this->defaultorder=($c['orderby']==""?"ventes":$c['orderby']);
      $this->menu_appartenance=$c['menu_appartenance'];
      $this->filtres=$c['filtres'];
      if($c['url'.$this->langue]!="")
	       $this->url=(substr($c['url'.$this->langue],0,1)!="/"?"/":"").$c['url'.$this->langue];
      else 
	       $this->url="/".url_rewrite($c['libelle'.$this->langue])."-cat,".$this->catid.".htm";
      // 	$this->url="/cat,".$this->catid.",".url_rewrite($c['libelle'.$this->langue]).".htm";

      
      $this->ref_title=$this->libelle;
      if($c['ref_title'.$this->langue]!="") $this->ref_title=$c['ref_title'.$this->langue];
      
      $this->promo_url=$this->url;
      $this->promo_libelle=$this->libelle;
      $this->promo_description=$this->description;
      $this->promo_ref_title=$this->ref_title;
      
      $this->loaded=true;
  }
  
  function promo_load($type="categorie"){
      $promo=false;
      $this->promo_url=$this->promo_libelle=$this->promo_description=$this->promo_ref_title=null;
      
      if($type=="categorie")
	  $promo=$this->DB->query_first("SELECT * FROM categorie_promo WHERE catid=".$this->catid);

      if($type=="marque" && $this->marqueids!=array())
	  $promo=$this->DB->query_first("SELECT * FROM marque_promo WHERE marqueid=".$this->marqueids[0]);
	  
      if($promo!=false) {
	  $this->promo_url=(substr($promo['url'.$this->langue],0,1)!="/"?"/":"").$promo['url'.$this->langue];
	  $this->promo_libelle=$promo['title'.$this->langue];
    $this->promo_footer_libelle=$promo['titleFooter'.$this->langue];
	  $this->promo_description=$promo['description'.$this->langue];
	  $this->promo_ref_title=$promo['ref_title'.$this->langue];
	  return true;
      }
      return false;
  }
  
    function idkdos_load($type="categorie") {
        $idkdos=false;
        $this->idkdos_url=$this->idkdos_libelle=$this->idkdos_description=$this->idkdos_ref_title=null;
        
        if($type=="categorie"){
            $idkdos=$this->DB->query_first("SELECT * FROM categorie_idkdos WHERE catid=".$this->catid);

            if($idkdos!=false) {
                $this->idkdos_url=(substr($idkdos['url'.$this->langue],0,1)!="/"?"/":"").$idkdos['url'.$this->langue];
                $this->idkdos_libelle=$idkdos['title'.$this->langue];
                $this->idkdos_description=$idkdos['description'.$this->langue];
                $this->idkdos_ref_title=$idkdos['ref_title'.$this->langue];
                return true;
            }
        }

        if($type=="prix" && count($this->prix)==2){
            $idkdos=$this->DB->query_first("SELECT * FROM categorie_idkdos WHERE catid=-1");

            if($idkdos!=false) {
                $tranche=$this->prix;

                $this->idkdos_url=sprintf((substr($idkdos['url'.$this->langue],0,1)!="/"?"/":"").$idkdos['url'.$this->langue],$tranche[0],$tranche[1]);
                $this->idkdos_libelle=sprintf($idkdos['title'.$this->langue],$de,$a);
                $this->idkdos_description=$idkdos['description'.$this->langue];
                $this->idkdos_ref_title=sprintf($idkdos['ref_title'.$this->langue],$de,$a);
                return true;
            }
        }
        return false;
    }
  
    function gamme_load($type="categorie"){
            $this->gamme_url=$this->gamme_libelle=$this->gamme_description=$this->gamme_ref_title=null;
            
            $gamme=false;
            switch($type){
                case "categorie":
                $gamme=$this->DB->query_first("SELECT * FROM categorie_gamme WHERE gamme='".mysqli_real_escape_string($this->DB->link_id,$this->pagegamme[0])."' AND catid=".intval($this->catid));
                break;
                
                case "marque":
                $gamme=$this->DB->query_first("SELECT * FROM marque_gamme WHERE gamme='".mysqli_real_escape_string($this->DB->link_id,$this->pagegamme[0])."' AND marqueid=".intval($this->marqueids[0]));
                break;
            }
            
            if($gamme!=false){
                $this->gamme_url=(substr($gamme['url'.$this->langue],0,1)!="/"?"/":"").$gamme['url'.$this->langue];
                $this->gamme_libelle=$gamme['title'.$this->langue];
                $this->gamme_description=$gamme['description'.$this->langue];
                $this->gamme_ref_title=$gamme['ref_title'.$this->langue];
                return true;
            }
            return false;
    }

    function verif_url($url){
        if($url=="") return false;
        
        /*$c=$this->DB->query_first("SELECT * FROM categorie WHERE url".$this->langue."='".$url."'");
        if($c==false || $c['catid']==""){
            $c=$this->DB->query_first("SELECT * FROM categorie_url WHERE langue='".$this->langue."' AND url='".$url."'");
            if($c==false || $c['catid']=="")
            return false;
            else{
            $cat=$this->DB->query_first("SELECT url".$this->langue." FROM categorie WHERE catid=".$c['catid']);
            return $cat[0];
            }
        }else
        return $url;
        */
        
        if($this->load_from_url($url,$type)){
            return $type;
        } else {
            return false;
        }
    }
  
    function new_url($url){
        $path=explode("/",$url);
        $first=array_shift($path);
        $path[0]=$first."/".$path[0];
        
        $params=array();
        $var="catid";
        foreach($path as $part){
            $part=preg_replace("/.htm$/","",$part);
            if(preg_match("/^[0-9]+$/",$part)){
                $params['page']=$part;
                break;
            }
            
            if(preg_match("/^(.+)-m$/",$part,$m)){
                $marque=$this->DB->query_first("SELECT marqueid FROM marque WHERE url_rewrite(libelle) LIKE '".mysqli_real_escape_string($this->DB->link_id,$m[1])."'  ");
                $params['marqueids']=$marque['marqueid'];
                continue;
            }
            
            $c=$this->DB->query_first("SELECT * FROM categorie WHERE REPLACE(url".$this->langue.",'.htm','')='".mysqli_real_escape_string($this->DB->link_id,$part)."'");
            if($c==false || $c['catid']==""){
                $c=$this->DB->query_first("SELECT catid FROM categorie_url WHERE langue='".$this->langue."' AND REPLACE(url,'.htm','')='".mysqli_real_escape_string($this->DB->link_id,$part)."'");
                if($c==false || $c['catid']=="")
                    return false;
            }
            $params[$var]=$c['catid'];
            $var="subcatids";
        }
        
        return $this->mkurl($params)['url'];
    }

    function verif_promo_url($url){
        if($url=="") return false;
        
        $c=$this->DB->query_first("SELECT url".$this->langue." FROM categorie_promo WHERE url".$this->langue."='".mysqli_real_escape_string($this->DB->link_id,$url)."' UNION SELECT url".$this->langue." FROM marque_promo WHERE url".$this->langue."='".mysqli_real_escape_string($this->DB->link_id,$url)."'");
        if($c[0]==$url) return true;

        return false;
    }
  
    function verif_idkdos_url($url){
        if($url=="") return false;
        
        $c=$this->DB->query_first("SELECT url".$this->langue." FROM categorie_idkdos WHERE url".$this->langue."='".mysqli_real_escape_string($this->DB->link_id,$url)."'");
        if($c[0]==$url) return true;

        $c=$this->DB->query_first("SELECT * FROM `categorie_idkdos` WHERE '".mysqli_real_escape_string($this->DB->link_id,$url)."' REGEXP replace(`url".$this->langue."`,'%u','([0-9]+)')");
        if($c!="") return true;
        
        return false;
    }
  
    function archive_load(){
        $sql="SELECT * FROM categorie_archive WHERE catid=".$this->catid;

        $data=$this->DB->query_first($sql);
        if($data=="") return false;
        $this->archive_title=$data['title'.$this->langue];
        $this->archive_url=$data['url'.$this->langue];
        return true;
    }
  
    function image($width,$height){
        $imgid=(count($this->subcatids)==1?$this->subcatids[0]:$this->catid);
        if(isset($imgid) && $imgid != '')
            return "/categorie/".$imgid."_".intval($width)."_".intval($height).".jpg";
        else return '';
    }
  
  function url(){
      $catid=$this->catid;
      $parentid=$this->parentid;
      $menu_appartenance=$this->menu_appartenance;
      while($parentid!=0){
	$c=$this->DB->query_first("SELECT catid,parentid,menu_appartenance FROM categorie WHERE catid=".$parentid); 
	$parentid=$c['parentid'];
	$catid=$c['catid'];
	$menu_appartenance=$c['menu_appartenance'];
	$subcatids=$this->catid;
      }

      return $this->mkurl(array("catid"=>$catid,"subcatids"=>$subcatids));
  }
  
function mkurl($params,$newparam=array(),$multi=false, $urldefaut='/categorie.php?'){

    if(!isset($params)) return false;
    $simpleparams=array("catid","subcatids","marqueids","start","page");

    foreach($newparam as $name=>$value){
  	  if(isset($params[$name]) && $multi && $name!="start"){
  		  $arr=explode(",",$params[$name]);
    		if(in_array($value,$arr)){
    		    $arr=array_diff($arr,array($value));
    		    $params[$name]=implode(",",$arr);
    		    if($params[$name]=="") unset($params[$name]);
    		}else{
    		    $params[$name]=$params[$name].",".$newparam[$name];
    		}
  	  }else{
    		if(is_null($newparam[$name]))
    		    unset($params[$name]);
    		else
    		    $params[$name]=$newparam[$name];
  	  }
    }

    foreach($params as $name=>$value){
  	  if(!in_array($name,$simpleparams) && !is_null($value)){
  	      $paramlink=true;
  	      break;
  	  }
    }
//       $paramlink=count(array_diff(array_keys($params),$simpleparams))>0;
//       $params=array_merge($params,$newparam);
    $subcatids=explode(",",$params['subcatids']);
    $marqueids=explode(",",$params['marqueids']);
    if($paramlink || count($subcatids)>1 || count($marqueids)>1)
       return array("url"=>$urldefaut .http_build_query($params),"rel"=>"nofollow");
    else{
       $cat=$this->DB->query_first("SELECT url".$this->langue." AS url,parentid,menu_appartenance FROM categorie WHERE catid=".intval($params['catid']));

    while(intval($cat['parentid'])!=0){
       if(!isset($params['subcatids'])) $params['subcatids']=$params['catid'];
         $subcatids=explode(",",$params['subcatids']);
         $cat=$this->DB->query_first("SELECT catid,url".$this->langue." AS url,parentid,menu_appartenance FROM categorie WHERE catid=".intval($cat['parentid']));
         $params['catid']=$cat['catid'];
    }
    if($cat['url']=="") return array("url"=> $urldefaut .http_build_query($params),"rel"=>"nofollow");
      $url="/".str_replace(".htm","",$cat['url']);
    if(isset($params['subcatids']) && $params['subcatids']!=""){
      $sscat=$this->DB->query_first("SELECT url".$this->langue." AS url FROM categorie WHERE catid=".intval($subcatids[0]));
      if($sscat['url']=="") return array("url"=> $urldefaut .http_build_query($params),"rel"=>"nofollow");
        $url.="/".str_replace(".htm","",$sscat['url']);
    }
    if(isset($params['marqueids']) && $params['marqueids']!=""){
      $marque=$this->DB->query_first("SELECT libelle FROM marque WHERE marqueid=".intval($marqueids[0]));
      $url.="/".url_rewrite($marque['libelle'])."-m";
    }
    if(isset($params['page']) && $params['page']!=1) $url.="/".$params['page'];
      $url.=".htm";
      return array("url"=>$url);
    }
}


  function subcategories($order="position",$onglet=false,$childs=null){
      if(is_null($this->DB)) return false;
      if(is_null($this->catid)) return false;
      if(!in_array($order,array("position","libelle".$this->langue))) $order="position";

      $this->subcatids=array($this->catid);
      $return=array();
      if($maincatid==0){
        	if(is_null($childs))
        	  $this->parents();
        	else
        	  $this->parents=$childs;

        	if(!is_null($this->parents[$this->catid])){
        	$position=1;
        	foreach($this->parents[$this->catid] as $ssc){
        	    if(in_array($onglet,$ssc->onglet) || $onglet==false){
        		if(count($this->marqueids)>0) $ssc->marqueids=$this->marqueids;
        		$return[$position][0]=$ssc;
        		$this->subcatids[]=$ssc->catid;
        		
        		$ssc2list=$ssc->subcategories($order,false,$this->parents);
        		$this->artcount+=$ssc->artcount;
        		if(is_array($ssc->subcatids)) $this->subcatids=array_merge($this->subcatids,$ssc->subcatids);
        		foreach($ssc2list as $p=>$ssc2){
        		    $return[$position][$p]=$ssc2;
        		}
        		$position++;
        	    }
        	}
        	}
        	$this->subcatids=array_unique($this->subcatids);

        	ksort($return);
        	return $return;
      }
  }

  function parents(){
      $this->parents=array();
      $req=$this->DB->query("SELECT * FROM categorie ORDER BY position");
      while($cat=$this->DB->fetch_array($req)){
	  $new=new categorie($this->DB,$cat['catid'],$this->langue);
	  $new->pro=$this->pro;
	  $new->site_code=$this->site_code;
	  $this->parents[$cat['parentid']][]=$new;
      }
  }
  
  function setsubcatids($catids){
      $this->subcatids=$this->getsubcatids($catids);
  }

  function getsubcatids($catids){

      if(is_array($catids)){
      	array_walk($catids,function(&$a,$k){$a=intval($a);});
      	$strcatids=implode("','",$catids);
      	$return=$catids;
      }else{
      	$return[]=intval($catids);
      	$strcatids=intval($catids);
      }

      $r=$this->DB->query("SELECT catid FROM categorie WHERE parentid IN ('".$strcatids."')");
      while($sscat=$this->DB->fetch_array($r)){
    	  $return[]=$sscat[0];
    	  $sscatids=$this->getsubcatids($sscat[0]);
    	  $return=array_merge($return,$sscatids);
      }

      $return=array_unique($return);
      return $return;
  }
  /**
   * retourn toute les categories correspondantes
   * @return array les catégories
   */
  function getavailsubcatids(){
      $return=array();
      $sql="SELECT c.catid,c.parentid FROM article a INNER JOIN article_marque am USING(artid) INNER JOIN categorie c USING(catid) ".$this->getsqlwhere()." GROUP BY c.catid";
      $req=$this->DB->query($sql);
      while($cat=$this->DB->fetch_array($req)){
	  $return[]=$cat['catid'];
	  $return[]=$cat['parentid'];
      }
      return $return;
  }

  /**
   * récupère le type de pose coché dans la fiche article du catalogue. 
   * Onglet matiere coloris finitions
   * @return array un tableau des types de poses pour les catégories correspondantes. 
   */
  function gettypepose(){
      $return=array();
      $sql="SELECT artp.typeposeid FROM articlerecherche_typepose AS artp INNER JOIN article AS a USING(artid) INNER JOIN categorie c USING(catid) ".$this->getsqlwhere()." GROUP BY artp.typeposeid";
      $req=$this->DB->query($sql);
      while($cat=$this->DB->fetch_array($req)){
    $return[]=$cat['typeposeid'];
      }
      return $return;
  }
  function setparam($param,$value){
      
      if(is_null($param) || $value==="" || is_null($value)) return false;
      //if(!isset($this->$param)) return false;
      
      if(is_array($this->$param)){

      	  if(is_array($value))
      	    $this->$param = $value;
      	  else
      	    $this->$param = explode(",",$value);

          array_walk($this->$param,function(&$a,$k){$a=str_replace(array("'","%","\"","="),"",$a);});

      } else

          $this->$param = intval($value);
  }

  function getsqlwhere($params=null, $virtuel=true){
      
    $subcatids        = $this->subcatids;
    $marqueids        = $this->marqueids;
    $usageids         = $this->usageids;
    //$capacite         = $this->capacite;
    $capaciteids      = $this->capaciteids;
    $emplacementids   = $this->emplacementids;
    $typeporteids     = $this->typeporteids;
    $colorisids       = $this->colorisids;
    $colorisencastrableids = $this->colorisencastrableids;
    $moderangementids = $this->moderangementids;
    $finitionids      = $this->finitionids;
    $largeurids       = $this->largeurids;
    $hauteurids       = $this->hauteurids;
    $profondeurids    = $this->profondeurids;
    $prixids          = $this->prixids;
    $prix             = $this->prix;
    $origineids       = $this->origineids;
    $bonsplans        = $this->bonsplans;

    if(!is_null($params)){
        foreach($params as $name=>$value){
            if(isset($$name)) $$name=$value;
        }
    }

      //$sql=" WHERE a.active='1' AND (a.catid IN ('".implode("','",$subcatids)."')";
      
    $sql = " WHERE 1";
    if(!is_null($subcatids) && count($subcatids)>0){
        $sql.=" AND (a.catid IN ('".implode("','",$subcatids)."')";
        if($virtuel) $sql .= " OR (SELECT COUNT(artid) FROM vcategorie WHERE catid IN ('".implode("','",$subcatids)."') AND artid=a.artid)>0";
        $sql .= ")";
    }
    
    if(count($this->typearticleids)>0){
        $sql.=" AND a.typearticleid IN (".implode(",",$this->typearticleids).")";
    }
    
    if(!is_null($this->pro)){
        $sql .= " AND a.pro IN ('".intval($this->pro)."','3')";
    } 
    
    if(count($marqueids) > 0){
        $sql .= " AND am.marqueid IN ('".implode("','",$marqueids)."')";
    }
    
    if(count($usageids) > 0){
        $sql .= " AND (SELECT COUNT(artid) FROM articlerecherche_usage WHERE rechercheusageid IN ('".implode("','",$usageids)."') AND artid = a.artid) > 0"; 
    } 
    /*if(count($capacite) > 0){
        $sql .= " AND capacite_max BETWEEN ".intval($capacite[0])." AND ".intval($capacite[1]);
    }*/
    if(count($capaciteids)>0){
        foreach($capaciteids as $capaciteid) $sqlc[]="capacite_max BETWEEN ".intval($this->capacites[$capaciteid])." AND ".intval($this->capacites[$capaciteid+1]);
        $sql.=" AND (".implode(" OR ",$sqlc).")";
    }
    
    if(count($finitionids) > 0){ 
        $sql .= " AND (";
        $sql .= "     SELECT COUNT(artid)";
        $sql .= "     FROM articlerecherche_finition";
        $sql .= "     WHERE finitionid IN ('".implode("','",$finitionids)."')";
        $sql .= "     AND artid = a.artid";
        $sql .= " ) > 0";
    } 
      
    $sqlp="";
    if(count($prix)>0) $sqlp=" AND a_s.prix".$ht." BETWEEN ".floatval($this->prix[0])." AND ".floatval($this->prix[1]);
    if(count($prixids)>0){
        foreach($prixids as $prixid) $sqlp[]="a_s.prix".$ht." BETWEEN ".intval($this->listeprix[$prixid])." AND ".intval($this->listeprix[$prixid+1]);
        $sqlp=" AND (".implode(" OR ",$sqlp).")";
    }
      
    if(count($moderangementids) > 0){
        $sql .= " AND (";
        $sql .= "     SELECT COUNT(artid)";
        $sql .= "     FROM articlerecherche_moderangement";
        $sql .= "     WHERE recherchemoderangementid IN ('".implode("','",$moderangementids)."')";
        $sql .= "     AND artid = a.artid";
        $sql .= " ) > 0";
    } 
    //if(count($capaciteids)>0) $sql.=" AND (SELECT COUNT(artid) FROM articlerecherche_capacitebouteilles WHERE recherchecapacitebouteillesid IN ('".implode("','",$capaciteids)."') AND artid=a.artid)>0";
    if(count($emplacementids)>0) $sql.=" AND (SELECT COUNT(artid) FROM articlerecherche_emplacement WHERE rechercheemplacementid IN ('".implode("','",$emplacementids)."') AND artid=a.artid)>0";
    if(count($typeporteids)>0) $sql.=" AND (SELECT COUNT(artid) FROM articlerecherche_typeporte WHERE recherchetypeporteid IN ('".implode("','",$typeporteids)."') AND artid=a.artid)>0";
    if(count($colorisids)>0) $sql.=" AND (SELECT COUNT(artid) FROM articlerecherche_coloris WHERE recherchecolorisid IN ('".implode("','",$colorisids)."') AND artid=a.artid)>0";
    if(count($colorisencastrableids)>0) {
     $sql_color = "SELECT nomFR, recherchecolorisid FROM recherche_coloris WHERE recherchecolorisid IN ('".implode("','",$colorisencastrableids)."')";
     $r_color = $this->DB->query($sql_color);  
        while($r_color_res = $this->DB->fetch_array($r_color)){
            if($r_color_res['recherchecolorisid'] == 1) {
                $sqlcolorporte[] = "(a.coloris_porte NOT LIKE '%noir%' AND a.coloris_porte NOT LIKE '%bois%' AND a.coloris_porte NOT LIKE '%inox%' AND a.coloris_porte NOT LIKE '%blanc%')";
            }
            $sqlcolorporte[] = "a.coloris_porte LIKE '%".$r_color_res['nomFR']."%'";
        }
    $sql .= " AND (".implode(" OR ",$sqlcolorporte).")";
    }  
      
    if(count($largeurids)>0){
        foreach($largeurids as $largeurid) $sqll[]="ROUND(a.largeur) BETWEEN ".intval($this->largeurs[$largeurid])." AND ".intval($this->largeurs[$largeurid+1]);
        $sql.=" AND (".implode(" OR ",$sqll).")";
    }
    if(count($hauteurids)>0){
        foreach($hauteurids as $hauteurid) $sqlh[]="ROUND(a.hauteur) BETWEEN ".intval($this->hauteurs[$hauteurid])." AND ".intval($this->hauteurs[$hauteurid+1]);
        $sql.=" AND (".implode(" OR ",$sqlh).")";
    }
    if(count($profondeurids)>0){
        foreach($profondeurids as $profondeurid) $sqlr[]="ROUND(a.profondeur) BETWEEN ".intval($this->profondeurs[$profondeurid])." AND ".intval($this->profondeurs[$profondeurid+1]);
        $sql.=" AND (".implode(" OR ",$sqlr).")";
    }
    if(!is_null($this->nbtemperature)){
        if($this->nbtemperature==1000)
            $sql.=" AND ac.nbr_temperature LIKE '%multi%'";
        else
            $sql.=" AND ac.nbr_temperature LIKE '%/".intval($this->nbtemperature)."'";
    }
    if(!is_null($this->mintemp)) $sql.=" AND ac.temp_instal_min<=".floatval($this->mintemp);
    if(!is_null($this->maxtemp)) $sql.=" AND ac.temp_instal_max>=".floatval($this->maxtemp);
    if(count($this->energy_label)>0){
        $sqlel=array();
        foreach($this->energy_label as $lettre)
            if(preg_match("/^[A-G]\+{0,3}$/",$lettre)>0) $sqlel[]="energy_label LIKE '".$lettre."'";
        
        if(count($sqlel)>0) $sql.=" AND (".implode(" OR ",$sqlel).")";
    }
    
    if(!is_null($this->esthetique)) $sql.=" AND esthetique=1";
    if(count($this->sensporteids)>0) $sql.=" AND sensporte IN (".implode(",",$this->sensporteids).")";
    if(count($this->serrure)>0) $sql.=" AND serrure_info IN ('".implode(",",$this->serrure)."')";
    if(count($this->pagegamme)>0) $sql.=" AND page_gamme IN ('".implode("','",$this->pagegamme)."')";
      
    if(!is_null($this->largeurmin)) $sql.=" AND ROUND(a.largeur)>=".intval($this->largeurmin);
    if(!is_null($this->largeurmax)) $sql.=" AND ROUND(a.largeur)<=".intval($this->largeurmax);
    if(!is_null($this->hauteurmin)) $sql.=" AND ROUND(a.hauteur)>=".intval($this->hauteurmin);
    if(!is_null($this->hauteurmax)) $sql.=" AND ROUND(a.hauteur)<=".intval($this->hauteurmax);
    if(!is_null($this->profondeurmin)) $sql.=" AND ROUND(a.profondeur)>=".intval($this->profondeurmin);
    if(!is_null($this->profondeurmax)) $sql.=" AND ROUND(a.profondeur)<=".intval($this->profondeurmax);
      
      
    if(count($this->site_code)>0) $sql.=" AND (SELECT COUNT(artid) FROM article_site a_s WHERE a_s.artid=a.artid AND a_s.active IN (".implode(",",$this->active).") AND a_s.site_code IN ('".implode("','",$this->site_code)."')$sqlp)>0";
    if(count($origineids)>0) $sql.=" AND paysfabrication IN ('".implode("','",$origineids)."')";
    if(count($bonsplans)>0){
        $sqlsite="";
        if(count($this->site_code)>0) $sqlsite=" AND site_code IN ('".implode("','",$this->site_code)."')";
        $sqlbp=array();
        if(in_array("cdc",$bonsplans)) $sqlbp[]="(SELECT COUNT(artid) FROM coupdecoeur WHERE artid=a.artid $sqlsite)>0";
        if(in_array("promo",$bonsplans)) $sqlbp[]="(SELECT COUNT(artid) FROM promo WHERE artid=a.artid AND ((dbt_promo<".time()." AND fin_promo>".time().") OR (dbt_promo = 0 AND fin_promo = 0)) $sqlsite)>0";
        if(in_array("idkdos",$bonsplans)) $sqlbp[]="(SELECT COUNT(artid) FROM idkdos WHERE artid=a.artid $sqlsite)>0";
        if(count($sqlbp)>0) $sql.=" AND (".implode(" OR ",$sqlbp).")";
    }
    
    if(!in_array("selection",$bonsplans)) $sql.=" AND (SELECT COUNT(artid) FROM article_deselection WHERE artid=a.artid)=0";
    if(!is_null($this->secondchoix)) $sql.=" AND (SELECT COUNT(artid) FROM secondchoix WHERE artid=a.artid)=".intval($this->secondchoix);

    return $sql;
  }
  
  function sqlorder($type,$date,$limit=null,$exclude=null) {
      $return="";
  
      $excludesql="";
      if(!is_null($exclude) && is_array($exclude) && count($exclude)>0) $excludesql=" AND a.artid NOT IN (".implode(",",$exclude).")";
      switch($type) {
	  case "articles_vus":
	  $return="(SELECT count(*) FROM article_vu av WHERE av.artid=a.artid $excludesql AND date>".intval($date).") DESC";
	  break;
	  
	  case "ventes":
	  $return="ventes*prix DESC";
	  break;
      }
      
      if(!is_null($limit)) $return.=" LIMIT ".intval($limit);
      return $return;
  }

  function getcdc($order="position",$limit=null){
      return $this->getfromlist("coupdecoeur",$order,$limit);
  }
  function getpromo($order="prix",$limit=null){
      return $this->getfromlist("promo",$order,$limit);
  }
  function getidkdos($order="prix",$limit=null){
      return $this->getfromlist("idkdos",$order,$limit);
  }
  function getfromlist($list,$order="prix",$limit=null){
      if(is_null($this->DB)) return false;

      $return=array();
      if(!is_null($limit)) $limit=" LIMIT ".$limit;
      if(!is_null($order)) $order=" ORDER BY ".$order;
      if(count($this->subcatids)>0){
	  $sql="SELECT p.artid FROM $list p INNER JOIN article a USING(artid) INNER JOIN article_marque am USING(artid)";
	  $sql.=$this->getsqlwhere(array("bonsplans"=>array($list)),false);
	  $sql.=$order.$limit;
	  $cdclist=$this->DB->query($sql);

	  if($this->DB->num_rows($cdclist)==0) return false;
	  while($cdc=$this->DB->fetch_array($cdclist)){
	    $return[]=$cdc['artid'];
	  }
	  ksort($return);
	  $return=array_unique($return);
      }
      return $return;
  }

  function articles_vus($s,$limit=2){
      $return=array();
      $r=$this->DB->query("SELECT artid FROM article_vu WHERE s='$s' ORDER BY date DESC LIMIT ".$limit);
      while($art=$this->DB->fetch_array($r)){
	  $return[]=$art[0];
      }
      return $return;
  }

  function articlecount($catids,$virtuel=true){
      $sql="SELECT COUNT(a.artid) FROM article a INNER JOIN article_marque am USING(artid)";
      $sql.=$this->getsqlwhere(array("subcatids"=>$catids),$virtuel);
      $r=$this->DB->query_first($sql);
      return $r[0];
  }

  function autoremoveparam(){
      $removeparam=array("hauteurids","largeurids","marqueids","emplacementids","colorisids","moderangementids","finitionids","capaciteids");
      foreach($removeparam as $p){
	if($this->$p!=array()){
	    $this->$p=array();
	    $c=$this->articlecount($this->subcatids);
	    if($c>0) return $this->articles();
	}
      }
  }

  function getmarques($artids = null){
      
      $return = array();
      
      $strsubcatids = implode("','",$this->subcatids);

      if(is_null($artids)){

          $sql = "SELECT am.marqueid, m.libelle, COUNT(am.marqueid)";
          $sql .= " FROM article_marque am";
          $sql .= " INNER JOIN article a USING(artid)";
          $sql .= " INNER JOIN marque m USING(marqueid)";
      	  $sql .= $this->getsqlwhere(array("marqueids" => array()));
      	  $sql .= " GROUP BY am.marqueid";
          $sql .= " ORDER BY m.libelle";

      } else {
          
          $sql = "SELECT am.marqueid, m.libelle, COUNT(am.marqueid)";
          $sql .= " FROM article a";
          $sql .= " INNER JOIN article_site a_s USING(artid)";
          $sql .= " INNER JOIN article_marque am USING(artid)";
          $sql .= " INNER JOIN marque m USING(marqueid)";
          $sql .= " WHERE a_s.active IN (".implode(",",$this->active).")";
          $sql .= " AND a_s.site_code IN ('".implode("','",$this->site_code)."')";
          $sql .= " AND artid IN ('".implode("','",$artids)."')";
          $sql .= " GROUP BY am.marqueid";
          $sql .= " ORDER BY m.libelle";

      }

      $r = $this->DB->query($sql);
      while($ru = $this->DB->fetch_array($r)){
        	$param = new param($ru[0],$ru[1],false,$ru[2]);
        	$param->setchecked($this->marqueids);
        	$return[$ru[0]]=$param;
      }

      return $return;
  }


//////////////////////////////////////// TSI SI# 2267 /////////////////////////////////////////
// Recuperation de du libelle / nom de la marque
  function getlibellemarque($marqueid = null){
      
      $return = null;

      if(!is_null($marqueid)){

          $sql = "SELECT m.libelle";
          $sql .= " FROM article a";
          $sql .= " INNER JOIN article_site a_s USING(artid)";
          $sql .= " INNER JOIN article_marque am USING(artid)";
          $sql .= " INNER JOIN marque m USING(marqueid)";
          $sql .= " WHERE a_s.active IN (".implode(",",$this->active).")";
          $sql .= " AND a_s.site_code IN ('".implode("','",$this->site_code)."')";
          $sql .= " AND marqueid = '".$marqueid."'";
          $sql .= " GROUP BY am.marqueid";
          $sql .= " ORDER BY m.libelle";

      }

      $r = $this->DB->query($sql);
      while($ru = $this->DB->fetch_array($r)){
          $return=$ru;
      }

      return $return;
  }

  function getusages($artids=null){
     
      $return=array();
//       if($artids==false) return $return;
      $strsubcatids=implode("','",$this->subcatids);
      if(is_null($artids)){
	  $sql="SELECT rechercheusageid,nom".$this->langue.",COUNT(rechercheusageid) FROM articlerecherche_usage INNER JOIN article a USING(artid) INNER JOIN article_site a_s USING(artid) INNER JOIN recherche_usage USING(rechercheusageid) WHERE a_s.active IN (".implode(",",$this->active).") AND a_s.site_code IN ('".implode("','",$this->site_code)."')";
	  if($strsubcatids!="") $sql.=" AND (a.catid IN ('".$strsubcatids."')  OR a.artid IN (SELECT artid FROM vcategorie WHERE catid IN ('".$strsubcatids."')))";
	  $sql.=" GROUP BY rechercheusageid";
      }else
	  $sql="SELECT rechercheusageid,nom".$this->langue.",COUNT(rechercheusageid) FROM articlerecherche_usage INNER JOIN recherche_usage USING(rechercheusageid) INNER JOIN article a USING(artid) INNER JOIN article_site a_s USING(artid)  WHERE a_s.active IN (".implode(",",$this->active).") AND a_s.site_code IN ('".implode("','",$this->site_code)."') AND artid IN ('".implode("','",$artids)."') GROUP BY rechercheusageid";
      $r=$this->DB->query($sql);
      while($ru=$this->DB->fetch_array($r)){
	$param=new param($ru[0],$ru[1],false,$ru[2]);
	$param->setchecked($this->usageids);
	$return[$ru[0]]=$param;
      }
      return $return;
  }


    function getcapacite($artids = null){
        $return = array();
        
        $return=array();
        $sql="SELECT ac.capacite_max FROM article a INNER JOIN article_marque am USING(artid)";
        $sql.=$this->getsqlwhere(array("capaciteids"=>array()));
        $r=$this->DB->query($sql);
        while($article=$this->DB->fetch_array($r)){
      for($i=0;$i<count($this->capacites);$i++) if($article['capacite_max']>=$this->capacites[$i] && $article['capacite_max']<=$this->capacites[$i+1]) $return[$i]++;
        }
        foreach($return as $id=>$nb){
      $param=new param($id,$this->capacites[$id],false,$nb);
      $param->from=$this->capacites[$id];
      $param->to=$this->capacites[$id+1];
      $param->setchecked($this->capaciteids);
      $return[$id]=$param;
        }
        ksort($return);
        return $return;
    }
    
    function getcapacitetranche($artids=null,$config=false){

	$return=array();
	$sql="SELECT ac.capacite_max FROM article a INNER JOIN article_marque am USING(artid)";
    if($config) $sql .=" INNER JOIN article_cave ac USING(artid)";
	$sql.=$this->getsqlwhere(array("capaciteids"=>array()));
  $sql.= " AND a.active='1'";
	$r=$this->DB->query($sql);
	while($article=$this->DB->fetch_array($r)){
	    for($i=0;$i<count($this->capacites);$i++) if($article['capacite_max']>=$this->capacites[$i] && $article['capacite_max']<=$this->capacites[$i+1]) $return[$i]++;
	}
	foreach($return as $id=>$nb){
	    $param=new param($id,$this->capacites[$id],false,$nb);
	    $param->from=$this->capacites[$id];
	    $param->to=$this->capacites[$id+1];
	    $param->setchecked($this->capaciteids);
	    $return[$id]=$param;
	}
	ksort($return);
	return $return;
    }

  function getemplacement($artids=null){
      
      $return = array();

      if($artids == false) return $return;

      $strsubcatids=implode("','",$this->subcatids);
      
      if(is_null($artids)){
	  
          $sql = "SELECT rechercheemplacementid, nom".$this->langue.", COUNT(rechercheemplacementid)";
          $sql .= " FROM articlerecherche_emplacement";
          $sql .= " INNER JOIN article a USING(artid)";
          $sql .= " INNER JOIN article_site a_s USING(artid)";
          $sql .= " INNER JOIN recherche_emplacement USING(rechercheemplacementid)";
          $sql .= " WHERE a_s.active IN (".implode(",",$this->active).")";
          $sql .= " AND a_s.site_code='".$this->site_code."'";
          $sql .= " AND (";
          $sql .= "     a.catid IN ('".$strsubcatids."')";
          $sql .= "   OR";
          $sql .= "     a.artid IN (SELECT artid FROM vcategorie WHERE catid IN ('".$strsubcatids."'))";
          $sql .= " )";
          $sql .= " GROUP BY rechercheemplacementid";

      } else {

          $sql = "SELECT rechercheemplacementid, nom".$this->langue.", COUNT(rechercheemplacementid)";
          $sql .= " FROM articlerecherche_emplacement";
          $sql .= " INNER JOIN recherche_emplacement USING(rechercheemplacementid)";
          $sql .= " INNER JOIN article a USING(artid)";
          $sql .= " INNER JOIN article_site a_s USING(artid)";
          $sql .= " WHERE a_s.active IN (".implode(",",$this->active).")";
          $sql .= " AND artid IN ('".implode("','",$artids)."')";
          $sql .= " GROUP BY rechercheemplacementid";
      }

      $r = $this->DB->query($sql);

      while($ru=$this->DB->fetch_array($r)){
      	  $param=new param($ru[0],$ru[1],false,$ru[2]);
      	  $param->setchecked($this->emplacementids);
      	  $return[$ru[0]]=$param;
      }

      return $return;
  }

  function gettypeporte($artids = null){
      
      $return = array();

      if($artids == false) return $return;

      $strsubcatids = implode("','",$this->subcatids);

      if(is_null($artids)){

          $sql = "SELECT recherchetypeporteid, nom".$this->langue.", COUNT(recherchetypeporteid)";
          $sql .= " FROM articlerecherche_typeporte";
          $sql .= " INNER JOIN article a USING(artid)";
          $sql .= " INNER JOIN article_site a_s USING(artid)";
          $sql .= " INNER JOIN recherche_typeporte USING(recherchetypeporteid)";
          $sql .= " WHERE a_s.active IN (".implode(",",$this->active).")";
          $sql .= " AND a_s.site_code='".$this->site_code."'";
          $sql .= " AND (";
          $sql .= "     a.catid IN ('".$strsubcatids."') ";
          $sql .= "   OR ";
          $sql .= "     a.artid IN (SELECT artid FROM vcategorie WHERE catid IN ('".$strsubcatids."'))";
          $sql .= " )";
          $sql .= " GROUP BY recherchetypeporteid";

      } else {

          $sql = "SELECT recherchetypeporteid, nom".$this->langue.", COUNT(recherchetypeporteid)";
          $sql .= " FROM articlerecherche_typeporte";
          $sql .= " INNER JOIN recherche_typeporte USING(recherchetypeporteid)";
          $sql .= " INNER JOIN article a USING(artid)";
          $sql .= " INNER JOIN article_site a_s USING(artid)";
          $sql .= " WHERE a_s.active IN (".implode(",",$this->active).")";
          $sql .= " AND artid IN ('".implode("','",$artids)."')";
          $sql .= " GROUP BY recherchetypeporteid";
      } 

      $r = $this->DB->query($sql);
      
      while($ru=$this->DB->fetch_array($r)){
      	  $param=new param($ru[0],$ru[1],false,$ru[2]);
      	  $param->setchecked($this->typeporteids);
      	  $return[$ru[0]]=$param;
      }

      return $return;
  }

  function getmoderangement($artids = null){

      $return = array();
      
      if($artids == false) return $return;

      $strsubcatids = implode("','",$this->subcatids);
      
      if(is_null($artids)){

          $sql = "SELECT recherchemoderangementid, nom".$this->langue.", COUNT(recherchemoderangementid)";
          $sql .= " FROM articlerecherche_moderangement";
          $sql .= " INNER JOIN article a USING(artid)";
          $sql .= " INNER JOIN article_site a_s USING(artid)";
          $sql .= " INNER JOIN recherche_moderangement USING(recherchemoderangementid)";
          $sql .= " WHERE a_s.active IN (".implode(",",$this->active).")";
          $sql .= " AND a_s.site_code = '".implode(",",$this->site_code)."'";
          $sql .= " AND (a.catid IN ('".$strsubcatids."')  OR a.artid IN (SELECT artid FROM vcategorie WHERE catid IN ('".$strsubcatids."')))";
          $sql .= " GROUP BY recherchemoderangementid";
      } else {

          $sql = "SELECT recherchemoderangementid, nom".$this->langue.", COUNT(recherchemoderangementid)";
          $sql .= " FROM articlerecherche_moderangement";
          $sql .= " INNER JOIN recherche_moderangement USING(recherchemoderangementid)";
          $sql .= " INNER JOIN article a USING(artid)";
          $sql .= " INNER JOIN article_site a_s USING(artid)";
          $sql .= " WHERE a_s.active IN (".implode(",",$this->active).") AND artid IN ('".implode("','",$artids)."')";
          $sql .= " GROUP BY recherchemoderangementid";
      }

      $r = $this->DB->query($sql);
      
      while($ru=$this->DB->fetch_array($r)){
      	  $param = new param($ru[0],$ru[1],false,$ru[2]);
      	  $param->setchecked($this->moderangementids);
      	  $return[$ru[0]] = $param;
      }

      return $return;
  }

  function getfinition($artids = null){
      
      $return = array();
      
      if(is_null($artids)){

          $sql = "SELECT finitionid, nom".$this->langue.", COUNT(finitionid)";
          $sql .= " FROM articlerecherche_finition";
          $sql .= " INNER JOIN article a USING(artid)";
          $sql .= " INNER JOIN article_marque am USING(artid)";
          $sql .= " INNER JOIN recherche_finition USING(finitionid)";
      	  $sql .= $this->getsqlwhere(array("finitionids" => array()));
      	  $sql .= " GROUP BY finitionid";
          $sql .= " ORDER BY finitionid";

      } else {

          $sql = "SELECT finitionid, nom".$this->langue.", COUNT(finitionid)";
          $sql .= " FROM articlerecherche_finition";
          $sql .= " INNER JOIN recherche_finition USING(finitionid)";
          $sql .= " INNER JOIN article a USING(artid)";
          $sql .= " WHERE a.active IN (".implode(",",$this->active).")";
          $sql .= " AND artid IN ('".implode("','",$artids)."')";
          $sql .= " GROUP BY finitionid";
      }

      $r = $this->DB->query($sql);

      while($ru = $this->DB->fetch_array($r)){
          $param = new param($ru[0],$ru[1],false,$ru[2]);
          $param->setchecked($this->finitionids);
          $return[$ru[0]] = $param;
      }

      return $return;
  }

    function getprix(){

      $return=array();
      $ht=($this->pro==1)?"":"ht";
      $sql="SELECT a.prix".$ht." AS prix FROM article a INNER JOIN article_marque am USING(artid)";
      $sql.=$this->getsqlwhere(array("prixids"=>array()));
      $r=$this->DB->query($sql);
      while($article=$this->DB->fetch_array($r)){
        for($i=0;$i<count($this->listeprix);$i++) if($article['prix']>=$this->listeprix[$i] && $article['prix']<=$this->listeprix[$i+1]) $return[$i]++;
      }
      foreach($return as $id=>$nb){
        $param=new param($id,$this->listeprix[$id],false,$nb);
        $param->from=$this->listeprix[$id];
        $param->to=$this->listeprix[$id+1];
        $param->setchecked($this->prixids);
        $return[$id]=$param;
      }
      ksort($return);
      return $return;
  }

  
  function getprixtranche(){
      $return=array();
      $ht=(is_null($this->pro)?"":"ht");
      $sql="SELECT a.prix".$ht." AS prix FROM article a INNER JOIN article_marque am USING(artid)";
      $sql.=$this->getsqlwhere(array("prixids"=>array()));
      $r=$this->DB->query($sql);
      while($article=$this->DB->fetch_array($r)){
	for($i=0;$i<count($this->listeprix);$i++) if($article['prix']>=$this->listeprix[$i] && $article['prix']<=$this->listeprix[$i+1]) $return[$i]++;
      }
      foreach($return as $id=>$nb){
	$param=new param($id,$this->listeprix[$id],false,$nb);
	$param->from=$this->listeprix[$id];
	$param->to=$this->listeprix[$id+1];
	$param->setchecked($this->prixids);
	$return[$id]=$param;
      }
      ksort($return);
      return $return;
  }

  function getnbtemperature($artids=null){
      $return=array();
      $sql="SELECT nbr_temperature,COUNT(nbr_temperature),artid FROM article a INNER JOIN article_marque am USING(artid)";
      if(is_null($artids))
            $sql.=$this->getsqlwhere();
        else
            $sql.=" INNER JOIN article_cave ac USING(artid) WHERE a.artid IN (".implode(",",$artids).")";
      $sql.=" GROUP BY nbr_temperature";
      
      $r=$this->DB->query($sql);
      while($ru=$this->DB->fetch_array($r)){
	  $arr=explode("/",$ru[0]);
	  $v=array_pop($arr);
	  $ru[0]=is_numeric($v) ? $v : 0;
	  if(intval($ru[0])==0) $ru[0]=1000;
	  $param=new param($ru[0],$ru[0],false,$ru[1]);
	  $param->setchecked(array());
	  $return[$ru[0]]=$param;
      }
      return $return;
  }
  
  function getesthetique(){
      $sql="SELECT COUNT(artid) FROM article a INNER JOIN article_marque am USING(artid) ".$this->getsqlwhere()." AND esthetique=1";
      $esth=$this->DB->query_first($sql);
      return $esth[0];
  }
  
  function getsensporte($cave=false){
      $return=array();
      $sql="SELECT ouverture_porte,COUNT(artid) FROM article a INNER JOIN article_marque am USING(artid) ";
      if($cave) $sql .="INNER JOIN article_cave ac USING(artid)";
      $sql.=$this->getsqlwhere()." GROUP BY ouverture_porte";
      $r=$this->DB->query($sql);
      while($ru=$this->DB->fetch_array($r)){
	  $param=new param($ru[0],$ru[0],false,$ru[1]);
	  $param->setchecked($this->sensporteids);
	  $return[$ru[0]]=$param;
      }
      return $return;
  }
  
  function getserrure($artids=null){
      $return=array();
      $sql="SELECT serrure_info,COUNT(serrure_info) FROM article a INNER JOIN article_marque am USING(artid)";
      if(is_null($artids))
            $sql.=$this->getsqlwhere();
        else
            $sql.=" INNER JOIN article_cave ac USING(artid) WHERE a.artid IN (".implode(",",$artids).")";
      $sql.=" GROUP BY serrure_info";
      $r=$this->DB->query($sql);
      while($ru=$this->DB->fetch_array($r)){
	  $param=new param($ru[0],$ru[0],false,$ru[1]);
	  $param->setchecked($this->serrure);
	  $return[$ru[0]]=$param;
      }
      return $return;
  }
  
  function getcoloris($artids=null){
      
      $return=array();
      
      if($artids==false) return $return;
      
      $strsubcatids=implode("','",$this->subcatids);
      
      if(is_null($artids)){

          $sql = "SELECT recherchecolorisid, nom".$this->langue.", COUNT(recherchecolorisid)";
          $sql .= " FROM articlerecherche_coloris";
          $sql .= " INNER JOIN article a USING(artid)";
          $sql .= " INNER JOIN article_site a_s USING(artid)";
          $sql .= " INNER JOIN recherche_coloris USING(recherchecolorisid)";
          $sql .= " WHERE a_s.active IN (".implode(",",$this->active).")";
          $sql .= " AND a_s.site_code='".implode(",",$this->site_code)."'";
          $sql .= " AND (";
          $sql .= "     a.catid IN ('".$strsubcatids."')";
          $sql .= "   OR";
          $sql .= "     a.artid IN (SELECT artid FROM vcategorie WHERE catid IN ('".$strsubcatids."'))";
          $sql .= " )";
          $sql .= " GROUP BY recherchecolorisid";

      } else {

          $sql = "SELECT recherchecolorisid, nom".$this->langue.", COUNT(recherchecolorisid)";
          $sql .= " FROM articlerecherche_coloris";
          $sql .= " INNER JOIN recherche_coloris USING(recherchecolorisid)";
          $sql .= " INNER JOIN article a USING(artid)";
          $sql .= " INNER JOIN article_site a_s USING(artid)";
          $sql .= " WHERE a_s.active IN (".implode(",",$this->active).")";
          $sql .= " AND artid IN ('".implode("','",$artids)."')";
          $sql .= " GROUP BY recherchecolorisid";
          $sql .= " ORDER BY position";
      }
	  
      $r = $this->DB->query($sql);      
      
      while($ru=$this->DB->fetch_array($r)){
          $param=new param($ru[0],$ru[1],false,$ru[2]);
          $param->setchecked($this->colorisids);
          $return[$ru[0]]=$param;
      }

      return $return;
  }
  /// coloris de la porte :
    function getcolorisencastrable($artids=null){

        $return=array();
        if($artids==false) return $return;
        $strsubcatids=implode("','",$this->subcatids);

        /// récupère les couleurs dans "traduction" via le coloris_porte des articles
        if(is_null($artids)){
            $sql = "SELECT t.".$this->langue;
            $sql .= " FROM article a";
            $sql .= " INNER JOIN article_cave ac";
            $sql .= " INNER JOIN traduction t ON ac.coloris_porte = t.path";
            $sql .= " INNER JOIN article_site a_s ON a.artid=a_s.artid";
            $sql .= " WHERE a_s.active IN (".implode(",",$this->active).")";
            $sql .= " AND a_s.site_code='".implode(",",$this->site_code)."'";
            $sql .= " AND (";
            $sql .= "     a.catid IN ('".$strsubcatids."')";
            $sql .= "   OR";
            $sql .= "     a.artid IN (SELECT artid FROM vcategorie WHERE catid IN ('".$strsubcatids."'))";
            $sql .= " )";
            $sql .= " GROUP BY t.".$this->langue;
        } else {
            $sql = "SELECT t.".$this->langue;
            $sql .= " FROM article a";
            $sql .= " INNER JOIN article_cave ac";
            $sql .= " INNER JOIN traduction t ON ac.coloris_porte = t.path";
            $sql .= " INNER JOIN article_site a_s ON a.artid=a_s.artid";
            $sql .= " WHERE a_s.active IN (".implode(",",$this->active).")";
            $sql .= " AND a_s.site_code='".implode(",",$this->site_code)."'";
            $sql .= " AND a.artid IN ('".implode("','",$artids)."')";
            $sql .= " GROUP BY t.".$this->langue;
        }

        $liste_couleurs = array();
        $r = $this->DB->query($sql); 
        while($ru=$this->DB->fetch_array($r)){
            /// si le coloris_porte de l'article n'est pas vide
            if($ru['FR'] != '') {
                $sql2 = "SELECT nom".$this->langue." FROM recherche_coloris WHERE nomFR LIKE '%".utf8_encode($ru[$this->langue])."%' GROUP BY nomFR";
                $r2 = $this->DB->query($sql2); 
              /// si le coloris_porte ne correspond pas aux résultats de recherche_coloris, lui assigner "autres couleurs"
                $sqlautre = "SELECT nom".$this->langue." FROM recherche_coloris WHERE nomFR NOT LIKE '%".utf8_encode($ru[$this->langue])."%' GROUP BY nomFR";
                $rautre = $this->DB->query($sqlautre); 
                if($this->DB->num_rows($rautre)!=0) {
                    $liste_couleurs[] = "'autres couleurs'";
                }
                while($ru2=$this->DB->fetch_array($r2)){
                $liste_couleurs[] = "'".$ru2['nom'.$this->langue]."'";
                }
            }
        }
        /// transformer le résultat en string pour le rentrer dans le IN de la requête
        $couleurs = implode(",", $liste_couleurs);

        if($couleurs != '') {
            $sql3 = "SELECT recherchecolorisid, nom".$this->langue.", COUNT(recherchecolorisid) FROM recherche_coloris WHERE nom".$this->langue." IN (".$couleurs.")";
            $sql3 .= " GROUP BY recherchecolorisid";
            $sql3 .= " ORDER BY position"; 
            $r3 = $this->DB->query($sql3);  
            while($ru=$this->DB->fetch_array($r3)){
                $param=new param($ru[0],$ru[1],false,$ru[2]);
                $param->setchecked($this->colorisencastrableids);
                $return[$ru[0]]=$param;
            }

            return $return;
        }
        else {
            return $return;
        }
    }

  function getdimids(){
      $return=array("largeur"=>array(),"hauteur"=>array(),"profondeur"=>array());
      $sql="select ac.hauteur, ac.largeur, ac.profondeur,at.hauteur, at.largeur, at.profondeur,ad.hauteur, ad.largeur, ad.profondeur,amb.hauteur, amb.largeur, amb.profondeur,acl.hauteur, acl.largeur, acl.profondeur,aci.hauteur, aci.largeur, aci.profondeur,am.hauteur, am.largeur, am.profondeur,ar.hauteur, ar.largeur, ar.profondeur,aco.hauteur, aco.largeur, aci.profondeur,aci.hauteur, aci.largeur, aci.profondeur,aci.hauteur, aci.largeur, aci.profondeur,aci.hauteur, aci.largeur, aci.profondeur,aci.hauteur, aci.largeur, aco.profondeur,acfcv.hauteur, acfcv.largeur, acfcv.profondeur,aa.hauteur, aa.largeur, aa.profondeur ,amp.hauteur, amp.largeur, amp.profondeur ,amn.hauteur, amn.largeur, amn.profondeur ,ach.hauteur, ach.largeur, ach.profondeur,achoc.hauteur, achoc.largeur, achoc.profondeur,aca.hauteur, aca.largeur, aca.profondeur,acc.hauteur, acc.largeur, acc.profondeur,acongel.hauteur, acongel.largeur, acongel.profondeur,af.hauteur, af.largeur, af.profondeur,afrigo.hauteur, afrigo.largeur, afrigo.profondeur,afm.hauteur, afm.largeur, afm.profondeur,amag.hauteur, amag.largeur, amag.profondeur,amat.hauteur, amat.largeur, amat.profondeur FROM article_cave ac, article_trancheuse as at, article_distributeur ad, article_minibar am, article_climatiseur acl, article_cigare aci, article_meubles amb, article_rafraichisseur ar, article_coffre aco, article_cfcv acfcv, article_arc aa, article_armoire_pos amp, article_armoire_neg amn, article_charcuterie ach, article_chocolat achoc, article_combine_armoire aca, article_combine_cave acc, article_congelmcav acongel, article_frigomcav afrigo, article_froid_mobile afm, article_fromage af, article_mag amag, article_maturation amat INNER JOIN article_marque USING(artid)";
     //$sql="SELECT a.largeur,a.hauteur,a.profondeur FROM article a INNER JOIN article_marque am USING(artid)";
      //$sql.=$this->getsqlwhere(array("largeurids"=>array(),"hauteurids"=>array(),"profondeurids"=>array()));
      $r=$this->DB->query($sql);
      while($article=$this->DB->fetch_array($r)){
    for($i=0;$i<count($this->largeurs);$i++) if($article['largeur']>=$this->largeurs[$i] && $article['largeur']<=$this->largeurs[$i+1]) $return['largeur'][$i]++;
    for($i=0;$i<count($this->hauteurs);$i++) if($article['hauteur']>=$this->hauteurs[$i] && $article['hauteur']<=$this->hauteurs[$i+1]) $return['hauteur'][$i]++;
    for($i=0;$i<count($this->profondeurs);$i++) if(intval($article['profondeur'])>=$this->profondeurs[$i] && intval($article['profondeur'])<=$this->profondeurs[$i+1]) $return['profondeur'][$i]++;
      }
   
      foreach($return['largeur'] as $largeurid=>$nb){
    $param=new param($largeurid,$this->largeurs[$largeurid],false,$nb);
    $param->from=$this->largeurs[$largeurid];
    $param->to=$this->largeurs[$largeurid+1];
    $param->setchecked($this->largeurids);
    $return['largeur'][$largeurid]=$param;
      }
      ksort($return['largeur']);
      foreach($return['hauteur'] as $hauteurid=>$nb){
    $param=new param($hauteurid,$this->hauteurs[$hauteurid],false,$nb);
    $param->from=$this->hauteurs[$hauteurid];
    $param->to=$this->hauteurs[$hauteurid+1];
    $param->setchecked($this->hauteurids);
    $return['hauteur'][$hauteurid]=$param;
      }
      ksort($return['hauteur']);
      foreach($return['profondeur'] as $profondeurid=>$nb){
    $param=new param($profondeurid,$this->profondeurs[$profondeurid],false,$nb);
    $param->from=$this->profondeurs[$profondeurid];
    $param->to=$this->profondeurs[$profondeurid+1];
    $param->setchecked($this->profondeurids);
    $return['profondeur'][$profondeurid]=$param;
      }
      ksort($return['profondeur']);
      return $return;
  }

  function getdimensions($artids=null){
      $return=array();
      $strsubcatids=implode("','",$this->subcatids);
      if(is_null($artids)){
	  $sql="SELECT MIN(ROUND(a.largeur)),MIN(ROUND(a.hauteur)),MIN(ROUND(a.profondeur)),MAX(ROUND(a.largeur)),MAX(ROUND(a.hauteur)),MAX(ROUND(a.profondeur)) FROM article a WHERE a.active IN (".implode(",",$this->active).") AND (a.catid IN ('".$strsubcatids."')  OR a.artid IN (SELECT artid FROM vcategorie WHERE catid IN ('".$strsubcatids."')))";
      }else
	  $sql="SELECT MIN(ROUND(a.largeur)),MIN(ROUND(a.hauteur)),MIN(ROUND(a.profondeur)),MAX(ROUND(a.largeur)),MAX(ROUND(a.hauteur)),MAX(ROUND(a.profondeur)) FROM article a WHERE a.active IN (".implode(",",$this->active).") AND artid IN ('".implode("','",$artids)."')";

      $return=$this->DB->query_first($sql);
      return $return;
  }

  function gettempinstal(){
      $return=array();
      $sql="SELECT MIN(temp_instal_min) AS min,MAX(temp_instal_max) AS max FROM article a INNER JOIN article_marque am USING(artid) ".$this->getsqlwhere()." AND temp_instal_max>0";
      $return=$this->DB->query_first($sql);
      return $return;
  }
  
  function getenergylabel(){
      $return=array();
      $sql="SELECT energy_label FROM article a INNER JOIN article_marque am USING(artid) ".$this->getsqlwhere()." GROUP BY energy_label";
      $r=$this->DB->query($sql);
      while($label=$this->DB->fetch_array($r)){
	  $return[]=$label[0];
      }
      return $return;
  }
  
  function getorigine($artids=null){
      $return=array();
      if($artids==false) return $return;
      $strsubcatids=implode("','",$this->subcatids);
      
      if(is_null($artids)){
    $sql="SELECT paysfabrication,COUNT(paysfabrication) FROM article a WHERE (SELECT active FROM article_site a_s  WHERE a_s.artid=a.artid AND site_code IN ('".implode("','",$this->site_code)."') AND a_s.active IN ('".implode(",",$this->active)."') LIMIT 1)=1 AND (a.catid IN ('".$strsubcatids."')  OR a.artid IN (SELECT artid FROM vcategorie WHERE catid IN ('".$strsubcatids."'))) GROUP BY paysfabrication";
      }else
    $sql="SELECT paysfabrication,COUNT(paysfabrication) FROM article a INNER JOIN article_site a_s USING(artid) WHERE a_s.active IN (".implode(",",$this->active).") AND site_code IN ('".implode("','",$this->site_code)."') AND artid IN ('".implode("','",$artids)."') GROUP BY paysfabrication ORDER BY paysfabrication";

      $r=$this->DB->query($sql);      
      while($ru=$this->DB->fetch_array($r)){
    $param=new param($ru[0],$this->origines[$ru[0]],false,$ru[1]);
    $param->setchecked($this->origineids);
    $return[$ru[0]]=$param;
      }
      return $return;
  }

  function getbonsplans(){
      $return=array();
      $strsubcatids=implode("','",$this->subcatids);
      $sql="SELECT SUM((SELECT COUNT(artid) FROM coupdecoeur WHERE artid=a.artid)) AS cdc,SUM((SELECT COUNT(artid) FROM promo WHERE artid=a.artid)) AS promo,SUM((SELECT COUNT(artid) FROM idkdos WHERE artid=a.artid)) AS idkdos,SUM((SELECT COUNT(artid) FROM article_deselection WHERE artid=a.artid)) AS deselection, COUNT(a.artid) AS tout FROM article a INNER JOIN article_marque am USING(artid)";
      $sql.=$this->getsqlwhere(array("bonsplans"=>array("selection")));
      $bp=$this->DB->query_first($sql);
      $bp['selection']=$bp['tout']-$bp['deselection'];
      return $bp;
  }
  
  function getpagegamme($artids){
      $return=array();
      $strsubcatids=implode("','",$this->subcatids);
      $sql="SELECT page_gamme,COUNT(page_gamme) FROM article a WHERE artid IN ('".implode("','",$artids)."') AND (SELECT active FROM article_site a_s  WHERE a_s.artid=a.artid AND site_code IN ('".implode("','",$this->site_code)."') AND a_s.active IN ('".implode(",",$this->active)."') LIMIT 1)=1 AND (a.catid IN ('".$strsubcatids."')  OR a.artid IN (SELECT artid FROM vcategorie WHERE catid IN ('".$strsubcatids."'))) GROUP BY page_gamme";
      $r=$this->DB->query($sql);
      while($ru=$this->DB->fetch_array($r)){
            $param=new param($ru[0],$ru[0],false,$ru[1]);
            $param->setchecked($this->pagegamme);
            $return[$ru[0]]=$param;
      }
      return $return;
  }
  
  function tranche_capacite($capacite){
      $capacite=round($capacite);
      foreach($this->capacites as $id=>$value){
	  if($capacite>=$value && $capacite<$this->capacites[$id+1]) return $id;
      }
      return false;
  }

  function articles($order="prix",$virtuel=true){
      if(count($this->subcatids)==0) return false;

      $return=array();

      $sql = "SELECT a.artid";
      $sql .= " FROM article a";
      $sql .= " INNER JOIN article_marque am USING(artid)";
      $sql .= $this->getsqlwhere(null,$virtuel);
      $sql .= " ORDER BY ".$order;
      $req = $this->DB->query($sql);
      while($a = $this->DB->fetch_array($req)){
          $return[] = $a[0];
      }
      
      //Cas d'un tri par prix => on utilise le prix comme index du tableau
      if(preg_match("/^prix ?(ASC|DESC)?$/",$order,$m) && $return!=array()){

          //Incrémentation pour éviter l'écrasement des produits qui ont le même prix
          $cpt = 0;
      	  foreach($return as $artid){
      	      $prix = 1000 * round(article_price($artid,true,$this->site_code[0])) + $cpt;
      	      $tri[$prix]=$artid;
              $cpt++;
      	  }

      	  if($m[1]=="DESC")
      	      krsort($tri);
      	  else
      	      ksort($tri);
      	  $return=array_values($tri);
      }

      return $return;
  }
  
  function nb_articles($order = "prix", $virtuel = true){
     
     if(count($this->subcatids)==0) return false;

     $return = array();

     $sql = "SELECT COUNT(*) as nb";
     $sql .= " FROM article a";
     $sql .= " INNER JOIN article_marque am USING(artid)";
     $sql .= $this->getsqlwhere(null, $virtuel);
     $req = $this->DB->query($sql);
     $return = $this->DB->fetch_array($req);

     return $return['nb'];
}
  
  function min_price($virtuel=true) {
      $return=false;
      
      $sql="SELECT MIN(a.prix) AS prix, MIN(a.prixht) AS prixht FROM article a INNER JOIN article_marque am USING(artid)";
      $sql.=$this->getsqlwhere(null,$virtuel);
      $return=$this->DB->query_first($sql);
      
      $sql="SELECT MIN(tp.prix) AS prix FROM article a INNER JOIN article_marque am USING(artid) INNER JOIN temp_price tp USING(artid)";
      $sql.=$this->getsqlwhere(null,$virtuel);
      $sql.=" AND ".time()." BETWEEN tp.du AND tp.au";
      $temp=$this->DB->query_first($sql);
      if($temp['prix']>0 && $return['prix']>$temp['prix']) $return=$temp;
      
      return $return;
  }

  function max_pc($virtuel=true) {
      $return=false;

      $sql="SELECT MAX(round( 100 * ( a.prixconseilleht - a.prixht ) / a.prixconseilleht )) AS pc FROM article a INNER JOIN article_marque am USING(artid)";
      $sql.=$this->getsqlwhere(null,$virtuel);
      $return=$this->DB->query_first($sql);

      $tvafrance=$this->DB->query_first("SELECT 0,TVAtauxnormal,TVAtauxreduit FROM pays WHERE paysid=57");
      $txtva=(1+$tvafrance['TVAtauxnormal']/100);
      $sql="SELECT MAX(ROUND(100*((tp.prixconseilleht-tp.prix/$txtva)/tp.prixconseilleht))) AS pc FROM article a INNER JOIN article_marque am USING(artid) INNER JOIN temp_price tp USING(artid)";
      $sql.=$this->getsqlwhere(null,$virtuel);
      $sql.=" AND ".time()." BETWEEN tp.du AND tp.au";
      $temp=$this->DB->query_first($sql);
      if($temp['pc']>0 && $return['pc']<$temp['pc']) $return=$temp;

      return $return;
  }
}

class param {
   public $id;
   public $libelle;
   public $qte;
   public $checked;
   public $from;
   public $to;

   function __construct($id,$libelle,$checked=false,$qte=0){
	$this->id=$id;
	$this->libelle=$libelle;
	$this->checked=$checked;
	$this->qte=$qte;
   }

   function setchecked($ids){
	$this->checked=(in_array($this->id,$ids));
	return $this->checked;
   }
}

?>
