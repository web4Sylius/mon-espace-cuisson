<?php

class showroom {
  private $DB;
  private $langue="FR";
  
  public $id;
  public $title;
  public $contenu;
  public $metatitle;
  public $metadescription;
  
  public $titles;
  public $contenus=array();
  public $metatitles=array();
  public $metadescriptions=array();
  
  function __construct($DB,$langue="FR") {
      $this->DB = $DB;
      $this->langue=$langue;
  }
  
  function load($id) {
      $data=$this->DB->query_first("SELECT * FROM showroom WHERE id=".intval($id));
      if($data['id']=="") return false;
      $this->id=$data['id'];
      $this->title=$data['title'.$this->langue];
      $this->contenu=$data['contenu'.$this->langue];
      $this->metatitle=$data['ref_title'.$this->langue];
      $this->metadescription=$data['ref_description'.$this->langue];
      
      $keys=array_keys($data);
      foreach($keys as $key) {
	  $r=preg_match("/contenu(.{2})/i",$key,$m);
	  if($r==1) {
	      $this->titles[$m[1]]=$data['title'.$m[1]];
	      $this->contenus[$m[1]]=$data['contenu'.$m[1]];
	      $this->metatitles[$m[1]]=$data['ref_title'.$m[1]];
	      $this->metadescriptions[$m[1]]=$data['ref_description'.$m[1]];
	  }
      }
      
      return true;
  }
  
  function articles($group=false) {
      $return=array();
      $articles=array();
      $req=$this->DB->query("SELECT * FROM showroom_article");
      while($article=$this->DB->fetch_array($req))
	  $articles[]=new showroom_article($this->DB,$this->langue,$article);
      
      if($group)
	  foreach($articles as $article) {
	      $catid=$article->catid();
	      $return[$catid][$article->position]=$article;
	  }
      else
	  $return=$articles;
	  
      return $return;
  }
  
  function article($artid) {
      $data=$this->DB->query_first("SELECT * FROM showroom_article WHERE artid=".intval($artid));
      if($data['artid']=="") return false;
      return new showroom_article($this->DB,$this->langue,$data);
  }
  
  function update() {
      $sql="UPDATE showroom SET id=".$this->id;
      foreach($this->titles as $langue=>$txt){
	  $txt=stripslashes($txt);
	  $this->titles[$langue]=$txt;
	  $sql.=", `title".$langue."`='".mysqli_real_escape_string($this->DB->link_id,$txt)."'";
      }
      foreach($this->contenus as $langue=>$html){
	  $html=stripslashes($html);
	  $this->contenus[$langue]=$html;
	  $sql.=", `contenu".$langue."`='".mysqli_real_escape_string($this->DB->link_id,$html)."'";
      }
      foreach($this->metatitles as $langue=>$html){
	  $html=stripslashes($html);
	  $this->metatitles[$langue]=$html;
	  $sql.=", `ref_title".$langue."`='".mysqli_real_escape_string($this->DB->link_id,$html)."'";
      }
      foreach($this->metadescriptions as $langue=>$html){
	  $html=stripslashes($html);
	  $this->metadescriptions[$langue]=$html;
	  $sql.=", `ref_description".$langue."`='".mysqli_real_escape_string($this->DB->link_id,$html)."'";
      }
      $sql.=" WHERE id=".$this->id;
	  
      return $this->DB->query($sql);
  }

  function in_showroom($artid) {
      $artid=intval($artid);
      $sql="SELECT COUNT(*) FROM showroom_article WHERE artid='$artid' OR CONCAT(',',links,',') LIKE '%,$artid,%'";
      $art=$this->DB->query_first($sql);
      return $art[0]>0;
  }
  
  function create_positions() {
      $req=$this->DB->query("SELECT * FROM showroom_article ORDER BY position");
      while($article=$this->DB->fetch_array($req)){
	  $art=new showroom_article($this->DB,$this->langue,$article);
	  $catarticles[$art->catid()][]=$art;
      }

      foreach($catarticles as $catid=>$articles) {
	  $p=0;
	  foreach($articles as $article){
	      $article->update_position($p);
	      $p++;
	  }
      }
  }
}

class showroom_article {
  private $DB;
  
  public $artid;
  public $description;
  public $descriptions=array();
  public $links;
  public $position;
  
  function __construct($DB,$langue,$data=null) {
      $this->DB = $DB;
      $this->langue=$langue;
      if(!is_null($data)) $this->internal_load($data);
      
  }
  
  private function load($artid) {
      $data=$this->DB->query_first("SELECT * FROM showroom_article WHERE artid=".intval($artid));
      $this->internal_load($data);
  }
  
  private function internal_load($data) {
      if(!is_null($data)) {
	  $this->artid=$data['artid'];
	  $this->description=$data['description'.$this->langue];
	  $this->links=explode(",",$data['links']);
	  $this->position=$data['position'];
	  $keys=array_keys($data);
	  foreach($keys as $key) {
	      $r=preg_match("/description(.{2})/i",$key,$m);
	      if($r==1) $this->descriptions[$m[1]]=$data[$key];
	  }
      }
  }
  
  function catid() {
      $categorie=$this->DB->query_first("SELECT catid,parentid FROM article INNER JOIN categorie USING(catid) WHERE artid=".intval($this->artid));

      while($categorie['parentid']>0) {
	 $categorie=$this->DB->query_first("SELECT catid,parentid FROM categorie WHERE catid=".intval($categorie['parentid']));
      }
      
      return $categorie['catid'];
  }
  
  function update($artid,$descriptions,$links) {
      $sql="showroom_article SET artid=".intval($artid).", links='".implode(",",$links)."'";
      foreach($descriptions as $langue=>$desc) {
	  $desc=stripslashes($desc);
	  $sql.=", `description".$langue."`='".mysqli_real_escape_string($this->DB->link_id,$desc)."'";
      }
      
      $exist=$this->DB->query_first("SELECT artid FROM showroom_article WHERE artid=".intval($artid));
      if($exist['artid']==$artid)
	 $sql="UPDATE ".$sql." WHERE artid=".intval($artid);
      else{
	 $this->artid=$artid;
	 $max=$this->categorie_max();
	 $sql="INSERT INTO ".$sql.", position=".($max+1);;
      }
	 
      return $this->DB->query($sql);
  }
  
  function update_position($position) {
      $sql="UPDATE showroom_article SET position=".intval($position)." WHERE artid=".$this->artid;
      return $this->DB->query($sql);
  }
  
  function order_position($artid,$sens) {
      $catid=$this->catid();
      $this->load($artid);
      $categorie_articles=$this->categorie_articles();
      switch($sens) {
	  case "up":
	  if($this->position==0) return false;
	  if(isset($categorie_articles[$this->position-1])) $categorie_articles[$this->position-1]->update_position($this->position);
	  $this->update_position($this->position-1);
	  return true;
	  
	  case "down":
	  if($this->position>count($categorie_articles)) return false;
	  if(isset($categorie_articles[$this->position+1])) $categorie_articles[$this->position+1]->update_position($this->position);
	  $this->update_position($this->position+1);
	  return true;
      }
  }
  
  function delete($artid) {
      return $this->DB->query("DELETE FROM showroom_article WHERE artid=".intval($artid));
  }
  
  private function categorie_max(){
      $positions=array_keys($this->categorie_articles());
      if($positions==array()) return -1;
      return max($positions);
  }
  
  private function categorie_articles(){
      $return=array();
      $catid=$this->catid();
      $req=$this->DB->query("SELECT * FROM showroom_article");
      while($article=$this->DB->fetch_array($req)) {
	  $sr_article=new showroom_article($this->DB,$this->langue,$article);
	  if($sr_article->catid()==$catid) $return[$sr_article->position]=$sr_article;
      }
      return $return;
  }
}

?>