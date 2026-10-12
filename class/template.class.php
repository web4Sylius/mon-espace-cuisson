<?php


class template{
    
  public $langue="FR";
  public $host;
  public $path;
  public $pro;
  public $mobile;
  public $host_conf;
  
  private $DB;
  public $cachedelai=3600;
  public $cacheroot="cache";

  public $parts=array();
  private $tempcache=array();
  public $translation=array();
  
  private $site;
  private $money_format;
  private $money_sep;
  private $devises;
  public  $devise;
  private $tauxref;
  private $paysref;
  private $actif;
  
  private $com_pages=array("index.php","switch.php","categorie.php","article.php","marques.php","marque.php","institutionnel.php","client.php","contacter.php","promotions.php","kdos.php","vente_flash.php","cde1.php");

  function __construct($langue="FR",$DB){
      $host = new Host();
      $this->host_conf = $host;
      $this->DB=$DB;
      $this->devises=new devises($this->DB);
      $this->site();
      $this->host = $this->host_conf->http_host;
      if($langue == "") $langue = "FR";
      $this->langue=$langue;
      $this->parts['langue']=$this->langue;
      var_dump($_SERVER['DOCUMENT_ROOT']);

      $this->cacheroot=$_SERVER['DOCUMENT_ROOT']."/mec/configurations/".$this->host_conf->path."/cache";
      $this->path=$_SERVER['DOCUMENT_ROOT']."/mec/configurations/".$this->host_conf->path."/template/".$this->langue;
  }
  
  function site(){
      $this->host_conf->initialise($this->DB);
      $sp_host=explode(".",$_SERVER['HTTP_HOST']);
      var_dump($sp_host[0]);
      $this->pro="localhost" ;
      //$this->pro=($sp_host[1]=="pro" || $sp_host[0]=="pro" || $sp_host[0]=="testpro" || $sp_host[0]=="localhost" || $sp_host[0]=="dediepro");

      $this->mobile="m";
      // $this->mobile=($sp_host[1]=="m" || $sp_host[0]=="m" || strpos($sp_host[0],"mtest")!==false);
      // TSI Modification pour utilisation des sites pro en localhost : localhost.m.ma-cave-a-vin.fr et localhost.pro.ma-cave-a-vin.fr
      $this->site=$this->host_conf->site_code;
      $this->langue=$this->host_conf->langue;
      $this->money_format=$this->host_conf->money_format;
      $this->money_sep=$this->host_conf->money_sep;
      $this->devise=$this->host_conf->devise;
      $this->paysref=$this->host_conf->pays_ref;
      $this->tauxref=$this->taux_tva($this->paysref);
      $this->actif=$this->host_conf->actif;

      return true;
  }
  
  function evaltemplate($template,$var){
    if(!isset($this->tempcache[$template])){
	    $temppath=$this->path."/".$template.".html";
	    if(!file_exists($temppath)) return false;
	    $this->tempcache[$template]=$this->short_links(addslashes(trim(file_get_contents($temppath))));
    }
    if(!isset($this->parts[$var])) $this->parts[$var]="";
    $this->parts[$var].=preg_replace_callback("/\[\?(.*?)\]/i", function ($m) { return $this->parts[$m[1]]; },$this->tempcache[$template]); // nouvelle ligne

  }
  
  function evalvartemplate($template,$var){
      if(!isset($this->parts[$var])) $this->parts[$var]="";
      $this->parts[$var].=preg_replace_callback("/\[\?(.*?)\]/i",function ($m) { return $this->parts[$m[1]]; },$template);
      return $this->parts[$var];
  }

  function changesite($site_code,$langue,$mobile=false){
      $site=$this->DB->query_first("SELECT * FROM site WHERE code='".$site_code."'");
      if($site['code']!=$site_code) return false;
      $this->site=$site['code'];
      $this->mobile=$mobile;
      $this->money_format=$site['money_format'];
      $this->money_sep=$site['money_sep'];
      $this->devise=$site['devise'];
      $this->paysref=$site['paysid_ref'];
      $this->tauxref=$this->taux_tva($this->paysref);
      $this->newlangue($langue);
      return true;
  }
  
  function newlangue($langue){
      $this->langue=$langue;
      $this->parts['langue']=$langue;
      $this->path=$_SERVER['DOCUMENT_ROOT']."/configurations/".$this->host_conf->path."/template/".$this->langue;
      $this->tempcache=array();
      $this->translation=array();
  }

  function translate($path,$case=null,$langue='FR'){
      if(substr($path,0,1)!="/") return $path;
      
      $path=str_replace("[site_code]",$this->site,$path);
      
      if(is_null($langue)){
/*          echo '<pre>';
          var_dump($this->translation[$path]);
          echo '</pre>';*/
	  if(!isset($this->translation[$path])){
	    $tr=$this->DB->query_first("SELECT ".$this->langue." FROM traduction WHERE path='".mysqli_real_escape_string($this->DB->link_id,$path)."'");
	    $this->translation[$path] = mb_detect_encoding($tr[0]) != 'UTF-8' ? utf8_encode($tr[0]) : $tr[0];
    // 	 if($tr[0]=="") mail("webmaster@ma-cave-a-vin.fr","Erreur traduction",$_SERVER['REQUEST_URI']."\n".$path);
	  }
	  
	  return $this->format_case($this->translation[$path],$case);
      }else{
	  $tr=$this->DB->query_first("SELECT ".$langue." FROM traduction WHERE path='".mysqli_real_escape_string($this->DB->link_id,$path)."'");
	  return $this->format_case($tr[0],$case);
      }
  }
 
  
  function format_case($txt,$case){
      switch($case){
	case "U":
	return mb_strtoupper($txt,'utf-8');

	case "L":
	return mb_strtolower($txt,'utf-8');

	case "F":
// 	$maj = strtr($txt[0],"äâàáåãéèëêòóôõöøìíîïùúûüýñçþÿæ?ðø","ÄÂÀÁÅÃÉÈËÊÒÓÔÕÖØÌÍÎÏÙÚÛÜÝÑÇÞÝÆ?ÐØ");
// 	$txt = substr_replace($txt,$maj,0,1);
	return mb_strtoupper(mb_substr($txt, 0, 1,"utf-8"),"utf-8").mb_strtolower(mb_substr($txt, 1,mb_strlen($txt),"utf-8"),"utf-8");

	case "W":
	return ucwords($txt); 
      }
      return $txt;
  }

  
  function getcache($template,$key=""){
      $cachefile=$this->cacheroot."/".$this->langue."/".$template.$key.".html";
      $fexist=file_exists($cachefile);
      if($fexist){
	if(time()-filemtime($cachefile)<$this->cachedelai || date("G")!=1){
	  $this->parts[$template]=file_get_contents($cachefile);
	  return true;
	}else{
	  unlink($cachefile);
	  return false;
	}
      }else
	return false;
  }

  function cachefiles(){
      $return=array();
       $scan = glob($this->cacheroot.'/*');
      foreach($scan as $index=>$path) $return=array_merge($return,$this->recurscan($path));
      return $return;
  }

  function recurscan($path){
      $return=array();
      if(is_file($path)){
	$return[]=$path;
      }else{
	$scan = glob($path."/*");
	foreach($scan as $index=>$path) $return=array_merge($return,$this->recurscan($path));
      }
      return $return;
  }

  function savecache($template,$key){
      $dir=$this->cacheroot."/".$this->langue;
      if(!is_dir($dir)) mkdir($dir,0775,true);

      file_put_contents($dir."/".$template.$key.".html",stripslashes($this->parts[$template]));
  }

  /**
   * Paramétrage des META pour le référencement
   *
   * @param  string $host        Hôte (ex : www.ma-cave-a-vin.fr)
   * @param  string $title       Titre de la page
   * @param  string $description Description de la page
   * 
   */
  function pageparams($host,$title,$description){
//       $this->parts['host']            = $host;
      $this->parts['pagetitle']       = strip_tags($title);
	  if(trim($description)==""){
		  $description=$title;
	  }
      $this->parts['pagedescription'] = preg_replace_callback("#\n|\t|\r|\"#",function ($m) { return ""; },strip_tags(html_entity_decode($description)));
  }

  function emptycache(){
      $this->recursivedel($this->cacheroot);
  }

  function recursivedel($str){
      if(is_file($str)){
	return @unlink($str);
      }elseif(is_dir($str)){
	$scan = glob(rtrim($str,'/').'/*');
	foreach($scan as $index=>$path) $this->recursivedel($path);
      }
  }
  
  function redirect($url,$http_code,$userid=null,$s=null,$prenom=null,$password=null) {
      switch($http_code) {
	case 301:
	header("HTTP/1.1 301 Moved Permanently");
	break;
	
	case 302:
	
	break;
	
	default:
	  return false;
      }
      if($userid!==null){
        $url_from=explode(".", $_SERVER['HTTP_HOST']);
        end($url_from);
        setcookie("userid", $userid, time()+3600, "/", ".".prev($url_from).".".end($url_from));
        setcookie("s", $s, time()+3600, "/", ".".prev($url_from).".".end($url_from));
        setcookie("prenom", $prenom, time()+3600, "/", ".".prev($url_from).".".end($url_from));
        setcookie("password", $password, time()+3600, "/", ".".prev($url_from).".".end($url_from));
      }
      header("location:".$url);
      header("Connection: close");
  }
  
  function price_format($price,$decimal=2,$html=true,$itemprop=false,$thousand_sep='',$ht=false) {
      $price=number_format($price,$decimal,$this->money_sep,$thousand_sep);
      if($ht) $price .=" HT";
      $before=array("%f","%s");
      if($itemprop && $html) $price="<span itemprop=\"price\">".$price."</span>";
      $after=array($price,$this->devises->symbole($this->devise,!$html));
      if(!$html){
	      $before[]="&nbsp;";
	      $after[]=" ";
      }
      return str_replace($before,$after,$this->money_format);
  }
  function price_itemprop($price,$decimal=2,$thousand_sep=''){
    $price=number_format($price,$decimal,$this->money_sep,$thousand_sep);
    $price= str_replace(",", ".", $price);
    return $price;
  }
  
  function site_code(){
      return $this->site;
  }
  
  function devise(){
      return $this->devise;
  }
  
  function taux_tva($paysid){
      $taux=$this->DB->query_first("SELECT 0 AS taux0, TVAtauxnormal AS taux1, TVAtauxreduit AS taux2 FROM pays WHERE paysid=".intval($paysid));
      return $taux;
  }
  
  function tva_ref($tauxchoisi=null){
      if(is_null($tauxchoisi)) return $this->tauxref;
      return floatval($this->tauxref[$tauxchoisi]);
  }
  
  function pays_ref(){
      return $this->paysref;
  }
  
  function actif(){
      return $this->actif;
  }
  
  function searchtemplate($regex){
      $return=array();
      if(!is_dir($this->path)) return $return;
      $d=opendir($this->path);
      
      while($f=readdir($d)){
	  if(preg_match("/(".$regex.")\.html/i",$f,$m))
	      $return[$m[1]]=file_get_contents($this->path."/".$f);
      }
      return $return;
  }
  
  function sites() {
      $return=array();
      
      $req=$this->DB->query("SELECT hosts,code,description,devise,paysid_ref,locales,actif FROM site");
      while($s=$this->DB->fetch_array($req)){
	  $s['hosts']=unserialize($s['hosts']);
	  $s['locales']=unserialize($s['locales']);
	  $s['actif']=unserialize($s['actif']);
	  $return[$s['code']]=$s;
      }
	  
      return $return;
  }
  
  function urlcode($site_code=null,$langue=null){
      if(is_null($site_code)) $site_code=$this->site;
      if(is_null($langue)) $langue=$this->langue;
      
      $site=$this->DB->query_first("SELECT hosts FROM site WHERE code='".$site_code."'");
      if($site[0]=="") return "?";
      $revhosts=array_flip(unserialize($site['hosts']));
      if(!isset($revhosts[$langue])) return "?";
      $url=$revhosts[$langue];
      $expurl=explode(".",$url);
      $init="";
      $exphost=explode("-",$expurl[0]);
      foreach($exphost as $ex)
		$init.=$ex[0];
      $init.=$expurl[count($expurl)-1];
      return $init;
  }
  
  function common_page(){
      return in_array(substr($_SERVER['SCRIPT_NAME'],1),$this->com_pages);
  }
  
function short_links($txt){
  $regex="/\[([a-z]+)-(\d+)\]/";
  $return=preg_replace_callback($regex,
    function($match){

      switch($match[1]){
        case "categorie":
          $categorie=new categorie($this->DB,$match[2],$this->langue);
          return $this->mkurl("categorie",$categorie->catid,$categorie);
        break;
        
        case "article":
          $article=$this->DB->query_first("SELECT libelle".$this->langue." FROM article WHERE artid=".$match[2]);
          return $this->mkurl("article",$match[2],$article);
        break;
        
        case "landingmarque":
        case "landingcategorie":
          $type=str_replace("landing","",$match[1]);
          $landing=$this->DB->query_first("SELECT url".$this->langue." FROM landing WHERE type='".$type."' AND landingid=".$match[2]);
          return $this->mkurl("landing",$match[2],$landing);
        break;
        
        case "marque":
          $marque=new marque($this->DB,$match[2],$this->langue);
          return $this->mkurl("marque",$marque->marqueid,$marque);
        break;
        
        case "institutionnel":
          $inst=$this->DB->query_first("SELECT libelle".$this->langue." FROM ".$match[1]." WHERE ".$match[1]."id=".$match[2]);
          return $this->mkurl($match[1],$match[2],$inst);
        break;

        case "conseil":
          if($this->pro)
            $site = "_pro";
          else
            $site = "_part";

          $cons=$this->DB->query_first("SELECT libelle".$this->langue." FROM ".$match[1].$site." WHERE ".$match[1]."id=".$match[2]);
          return $this->mkurl($match[1],$match[2],$cons);
        break;
    }
      },
      $txt);
      
      return $return;
  }
  
    function mkurl($type,$id,$data){
        switch($type){
            case "categorie":
                return $data->url()['url'];
                break;
            
            case "promo":
                $slash=substr($data->promo_url,0,1)!="/"?"/":"";
                return $slash.$data->promo_url;
                break;
                
            case "gamme":
                return $data->gamme_url;
                break;
            
            case "article":
//                 return "/art,".$id.",".url_rewrite($data['libelle'.$this->langue]).".htm";
                return "/".url_rewrite($data['libelle'.$this->langue]."-".$data['marque'])."-art".$id.".htm";
                break;
            
            case "landing":
                return "/cave/".$data['url'.$this->langue].".html";
                break;
            
            case "institutionnel":
                return "/int,".$id.",".url_rewrite($data['libelle'.$this->langue]).".htm";
                break;
            
            case "conseil":
                return "/conseil,".$id.",".url_rewrite($data['libelle'.$this->langue]).".htm";
                break;
            
            case "marque":
                return "/".$data->link;
                break;
        }
    }

  function out($pagehtml="Page"){
      $page=addslashes(file_get_contents($this->path."/".$pagehtml.".html"));
      $this->html=preg_replace_callback("/\[\?(.*?)\]/i",function ($m) { return $this->parts[$m[1]]; },$page);
//       print preg_replace_callback('(\r|\n|\t)', '',stripslashes($this->html));
      print stripslashes($this->html);
  }

  function check_utf8($str) { 
    $len = strlen($str); 
    for($i = 0; $i < $len; $i++){ 
        $c = ord($str[$i]); 
        if ($c > 128) { 
            if (($c > 247)) return false; 
            elseif ($c > 239) $bytes = 4; 
            elseif ($c > 223) $bytes = 3; 
            elseif ($c > 191) $bytes = 2; 
            else return false; 
            if (($i + $bytes) > $len) return false; 
            while ($bytes > 1) { 
                $i++; 
                $b = ord($str[$i]); 
                if ($b < 128 || $b > 191) return false; 
                $bytes--; 
            } 
        } 
    } 
    return true; 
  }
}

?>
