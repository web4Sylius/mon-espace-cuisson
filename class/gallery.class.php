<?php

class gallery {
  public $id;
  public $date_creation;
  public $utilisateur;
  public $type;
  public $type_id;
  public $url;
  public $libelle;
  public $description;
  public $photos=array();
  public $types=array("marque"=>"marque","categorie"=>"categorie");
  public $path;
  
  private $DB;
  private $langue="FR";

  function __construct($DB,$langue,$id=null){
      $this->DB=$DB;
      $this->langue=$langue;
      
      if(!is_null($id)) return $this->load($id);
  }
  
  function load($id){
      $g=$this->DB->query_first("SELECT * FROM gallery WHERE id=".intval($id));
      if($g==false) return false;
      
      return $this->internal_load($g);
  }
  
  function loadUrl($url){
      $url=stripslashes($url);
      $g=$this->DB->query_first("SELECT * FROM gallery WHERE `url".$this->langue."`='".mysqli_real_escape_string($this->DB->link_id,$url)."'");
      if($g==false) return false;
      
      return $this->internal_load($g);
  }

function loadUrlHreflang($url,$template_langue){//Rajouté par Ariel le 13/02/18 pour recuperer l'url anglais de la page pour la balise hreflang
    $url=stripslashes($url);
    if($this->langue != $template_langue)
      $g=$this->DB->query_first("SELECT * FROM gallery WHERE `url".$template_langue."`='".mysqli_real_escape_string($this->DB->link_id,$url)."'");
    else
      $g=$this->DB->query_first("SELECT * FROM gallery WHERE `url".$this->langue."`='".mysqli_real_escape_string($this->DB->link_id,$url)."'");
    
    if($g==false) return false;
    
    return $this->internal_load($g);
}  

  function loadIndex(){
      $g=$this->DB->query_first("SELECT * FROM gallery WHERE type='index'");
      if($g==false) return false;
      
      return $this->internal_load($g);
  }
  
  function internal_load($g){
      $this->id=$g['id'];
      $this->date_creation=$g['date_creation'];
      $this->utilisateur=$g['utilisateur'];
      $this->type=$g['type'];
      $this->type_id=$g['type_id'];
      $this->url=$g['url'.$this->langue];
      $this->libelle=$g['libelle'.$this->langue];
      $this->description=$g['description'.$this->langue];
      $this->urlFR=$g['urlFR'];
      $this->libelleFR=$g['libelleFR'];
      $this->descriptionFR=$g['descriptionFR'];
      $this->urlEN=$g['urlEN'];
      $this->libelleEN=$g['libelleEN'];
      $this->descriptionEN=$g['descriptionEN'];
      $this->urlSP=$g['urlSP'];
      $this->libelleSP=$g['libelleSP'];
      $this->descriptionSP=$g['descriptionSP'];
      
      return true;
  }
  
  function load_photos(){
      $this->photos=array();
      $req=$this->DB->query("SELECT id FROM gallery_photo WHERE parent_id=".intval($this->id));
      while($photo=$this->DB->fetch_array($req)){
	  $gp=new gallery_photo($this->DB,$this->langue,$photo['id']);
	  $this->photos[$gp->position]=$gp;
      }
      ksort($this->photos);
      return $this->photos;
  }
  
  function nb_photos(){
      $count=$this->DB->query_first("SELECT COUNT(*) FROM gallery_photo WHERE parent_id=".intval($this->id));
      return $count[0];
  }
  
  function first_photo(){
      $p=$this->DB->query_first("SELECT id FROM gallery_photo WHERE parent_id=".intval($this->id)." ORDER BY position");
      if($p=="") return false;
      $gp=new gallery_photo($this->DB,$this->langue,$p['id']);
      return $gp;
  }
  
  function getListByType($type){
      $type=stripslashes($type);
      $req=$this->DB->query("SELECT id FROM gallery WHERE type='".mysqli_real_escape_string($this->DB->link_id,$type)."'");
      $return=array();
      while($g=$this->DB->fetch_array($req)){
	  $return[$g['id']]=new gallery($this->DB,$this->langue,$g['id']);
	  $return[$g['id']]->path=$this->path;
      }
      
      return $return;
   }
   
   function getTypes(){
      $return=array();
      $req=$this->DB->query("SELECT type FROM gallery GROUP BY type");
      while($t=$this->DB->fetch_array($req))
	  $return[]=$t['type'];
	  
      return $return;
   }
   
   function getFullUrl(){
      $ext=".htm";
      return "/".$this->path."/".$this->url.$ext;
   }

   function getTypeName(){
      $return="";
      switch($this->type){
	  case "marque":
	  $marque=$this->DB->query_first("SELECT libelle FROM marque WHERE marqueid=".intval($this->type_id));
	  $return=$marque[0];
	  break;

	  case "categorie":
	  $categorie=$this->DB->query_first("SELECT libelle".$this->langue." FROM categorie WHERE catid=".$this->type_id);
	  $return=$categorie[0];
	  break;
      }

      return $return;
   }
   
   function create($langues){
      $this->type=stripslashes($this->type);
      $this->utilisateur=stripslashes($this->utilisateur);
      $sql="INSERT INTO gallery SET type='".mysqli_real_escape_string($this->DB->link_id,$this->type)."', type_id='".$this->type_id."',
      date_creation=".time().", utilisateur='".$this->utilisateur."'";
      foreach($langues as $langue){
	  $url=trim(stripslashes($this->{"url".$langue}));
	  $libelle=trim(stripslashes($this->{"libelle".$langue}));
	  $description=stripslashes($this->{"description".$langue});
	  $sql.=", `url".$langue."`='".mysqli_real_escape_string($this->DB->link_id,$url)."', `libelle".$langue."`='".mysqli_real_escape_string($this->DB->link_id,$libelle)."', `description".$langue."`='".mysqli_real_escape_string($this->DB->link_id,$description)."'";
      }
      $result=$this->DB->query($sql);
      $this->id=$this->DB->insert_id();
      return $result;
   }
   
   function update($langues){
      $this->type=stripslashes($this->type);

      $sql="UPDATE gallery SET type='".mysqli_real_escape_string($this->DB->link_id,$this->type)."', type_id='".$this->type_id."'";
      foreach($langues as $langue){
	  $url=trim(stripslashes($this->{"url".$langue}));
	  $libelle=trim(stripslashes($this->{"libelle".$langue}));
	  $description=stripslashes($this->{"description".$langue});
	  $sql.=", `url".$langue."`='".mysqli_real_escape_string($this->DB->link_id,$url)."', `libelle".$langue."`='".mysqli_real_escape_string($this->DB->link_id,$libelle)."', `description".$langue."`='".mysqli_real_escape_string($this->DB->link_id,$description)."'";
      }
      $sql.=" WHERE id=".$this->id;
      
      $result=$this->DB->query($sql);
      return $result;
   }
  
   function delete(){
      $photos=$this->load_photos();
      foreach($photos as $photo) $photo->delete();
      
      return $this->DB->query("DELETE FROM gallery WHERE id=".intval($this->id));
   }
   
   function reorder(){
      $sql="SELECT id,position FROM gallery_photo ORDER BY position";
      $req=$this->DB->query($sql);
      $i=0;
      while($p=$this->DB->fetch_array($req)){
	 $this->DB->query("UPDATE gallery_photo SET position=".$i." WHERE id=".$p['id']);
	 $i++;
      }
   }
   
   function photo_move($id,$offset){
      $photo=new gallery_photo($this->DB,$this->langue,$id);
      if($offset<0){
	  $before=$photo->load_before();
	  if($before!=false){
	      $beforepos=$before->position;
	      $before->position=$photo->position;
	      $before->update();
	      $photo->position=$beforepos;
	      $photo->update();
	  }else
	      return false;
      }else{
	  $after=$photo->load_after();
	  if($after!=false){
	      $afterpos=$after->position;
	      $after->position=$photo->position;
	      $after->update();
	      $photo->position=$afterpos;
	      $photo->update();
	  }else
	      return false;
      }
      return true;
   }
}


class gallery_photo {
  public $id;
  public $parent_id;
  public $date_creation;
  public $utilisateur;
  public $libelle;
  public $link;
  public $legende;
  public $position;
  public $photopath;
  public $file;
  
  
  private $DB;
  private $langue;
  
  function __construct($DB,$langue,$id=null){
      $this->DB=$DB;
      $this->langue=$langue;
      
      if(!is_null($id)) $this->load($id);
  }
  
  function load($id){
      $photo=$this->DB->query_first("SELECT * FROM gallery_photo WHERE id=".intval($id));
      if(is_null($photo)) return false;
      
      $this->id=$photo['id'];
      $this->parent_id=$photo['parent_id'];
      $this->date_creation=$photo['date_creation'];
      $this->utilisateur=$photo['utilisateur'];
      $this->libelle=$photo['libelle'.$this->langue];
      $this->link=$this->correctLink($photo['link'.$this->langue]);
      $this->legende=$photo['legende'.$this->langue];
      
      $this->libelleFR=$photo['libelleFR'];
      $this->libelleEN=$photo['libelleEN'];
      $this->libelleSP=$photo['libelleSP'];
      $this->linkFR=$photo['linkFR'];
      $this->linkEN=$photo['linkEN'];
      $this->linkSP=$photo['linkSP'];
      $this->legendeFR=$photo['legendeFR'];
      $this->legendeEN=$photo['legendeEN'];
      $this->legendeSP=$photo['legendeSP'];
      $this->position=$photo['position'];
      $this->file=$this->id.".jpg";
      
      return true;
  }
  
  function load_before(){
      $photo=$this->DB->query_first("SELECT id FROM gallery_photo WHERE position<".$this->position);
      if(is_null($photo)) return false;
      return new gallery_photo($this->DB,$this->langue,$photo['id']);
  }
  
  function load_after(){
      $photo=$this->DB->query_first("SELECT id FROM gallery_photo WHERE position<>".$this->position);
      if(is_null($photo)) return false;
      return new gallery_photo($this->DB,$this->langue,$photo['id']);
  }
  
  function getpath($dir,$width,$height){
      if(is_null($this->id)) return false;
      if(substr($dir,-1)=="/") $dir=substr($dir,0,strlen($dir)-1);
      $path=sprintf("%s/%d_%d_%d.jpg",$dir,$this->id,$width,$height);
      return $path;
  }
  
  function insert($langues){
      $this->utilisateur=stripslashes($this->utilisateur);
      $sql="INSERT INTO gallery_photo SET parent_id=".intval($this->parent_id).", date_creation=".time().", utilisateur='".mysqli_real_escape_string($this->DB->link_id,$this->utilisateur)."'";
      foreach($langues as $langue){
	  $libelle=stripslashes($this->{"libelle".$langue});
	  $link=stripslashes($this->{"link".$langue});
	  $legende=stripslashes($this->{"legende".$langue});
	  $sql.=", `libelle".$langue."`='".mysqli_real_escape_string($this->DB->link_id,$libelle)."', `link".$langue."`='".mysqli_real_escape_string($this->DB->link_id,$link)."', `legende".$langue."`='".mysqli_real_escape_string($this->DB->link_id,$legende)."'";
      }
      $sql.=", position=".($this->lastPosition()+1);
      $result=$this->DB->query($sql);
      $this->id=$this->DB->insert_id();
      return $result;
  }
  
  function update($langues=null){
      $sql="UPDATE gallery_photo SET position=".intval($this->position);
      if(!is_null($langues)){
	  foreach($langues as $langue){
	      $libelle=stripslashes($this->{"libelle".$langue});
	      $link=stripslashes($this->{"link".$langue});
	      $legende=stripslashes($this->{"legende".$langue});
	      $sql.=", `libelle".$langue."`='".mysqli_real_escape_string($this->DB->link_id,$libelle)."', `link".$langue."`='".mysqli_real_escape_string($this->DB->link_id,$link)."', `legende".$langue."`='".mysqli_real_escape_string($this->DB->link_id,$legende)."'";
	  }
      }
      $sql.=" WHERE id=".intval($this->id);
      $result=$this->DB->query($sql);
      return $result;
  }
  
  function upload_insert($name,$tmp_name,$langues){
      $test=$this->test_upload($name,$tmp_name);
      if($test!=true) return $test;
      
      $this->insert($langues);
      
      return $this->upload($tmp_name);
  }
  
  function test_upload($name,$tmp_name){
      $ext=strtolower(pathinfo($name,PATHINFO_EXTENSION));
      if($ext!="jpg") return false;
      if(filesize($tmp_name)>pow(10,6)) return false;
      return true;
  }
  
  function upload($tmp_name){
      return move_uploaded_file($tmp_name,$this->photopath."/".$this->id.".jpg");
  }
  
  function delete($delfile=false){
      if($delfile && file_exists($this->photopath."/".$this->name)) unlink($this->photopath."/".$this->name);
      return $this->DB->query("DELETE FROM gallery_photo WHERE id=".intval($this->id));
  }
  
  function lastPosition(){
      $position=$this->DB->query_first("SELECT MAX(position) FROM gallery_photo WHERE parent_id=".intval($this->parent_id));
      return $position[0];
  }

  function correctLink($link){
      if($link=="") return "";
      if(!preg_match("/\[.+\]/",$link)){
	if(preg_match("/^https?:\/\//",$link)) return $link;
	if(substr($link,0,1)!="/") $link="/".$link;
      }
      return $link;
  }

  function getSize(){
      return getimagesize($this->photopath."/".$this->id.".jpg");
  }
}


?>