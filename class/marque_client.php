<?php

class marque_client {
    private $DB;
    private $langue;
    private $img_path="configurations/www.ma-cave-a-vin.fr/images/marques_client/";

    private $id;
    private $nom;
    private $contenu;
    private $position;
    private $image;
    
    function __construct($DB,$clientid=0,$langue="FR"){
        $this->DB=$DB;
        $this->langue=$langue;
        
        if($clientid>0){
            $data=$this->DB->query_first("SELECT * FROM marque_client WHERE id=".intval($clientid));
            $this->load($data);
        }
    }
    
    function load($data){
        $this->id=$data['id'];
        $this->nom=$data['nom'];
        $this->contenu=$data['contenu'.$this->langue];
        $this->contenuFR=$date['contenuFR'];
        $this->contenuEN=$date['contenuEN'];
        $this->contenuSP=$date['contenuSP'];
        $this->position=$data['position'];
        $this->image=$this->img_path.$this->id.".jpg";
    }
    
    function getlist($order="position",$nb=false){
        $return=array();
        
        if($order!="position" && $order!="nom") return false;
        $limit="";
        if($nb!=false) $limit=" LIMIT ".intval($nb);
    
        $req=$this->DB->query("SELECT * FROM marque_client ORDER BY ".$order.$limit);
        while($data=$this->DB->fetch_array($req)){
            $m=new marque_client($this->DB);
            $m->load($data);
            $return[]=$m;
        }
        
        return $return;
    }
    
    function logo_url($width,$height){
        if(is_null($this->id)) return false;
        return "clients/".$this->id."_".intval($width)."_".intval($height).".jpg";
    }
    
    function update(){
        $sql="nom='".mysqli_real_escape_string($this->DB->link_id,$this->nom)."',
        contenuFR='".mysqli_real_escape_string($this->DB->link_id,$this->contenuFR)."',
        contenuEN='".mysqli_real_escape_string($this->DB->link_id,$this->contenuEN)."',
        contenuSP='".mysqli_real_escape_string($this->DB->link_id,$this->contenuSP)."'";
    
        if(is_null($this->id)){
            $last=$this->last_position();
            if(is_null($last))
                $this->position=0;
            else
                $this->position=$last+1;
            $sql="INSERT INTO marque_client SET ".$sql.", position=".intval($this->position);
        }else
            $sql="UPDATE marque_client SET ".$sql." WHERE id=".$this->id;

        $return=$this->DB->query($sql);
        if($return && is_null($this->id)) $this->id=$this->DB->insert_id();
        return $return;
    }
    
    function move($sens){
        switch($sens){
            case "up":
            if($this->position==0) return false;
            $this->DB->query("UPDATE marque_client SET position=".intval($this->position)." WHERE position=".intval($this->position-1));
            $this->position-=1;
            return $this->DB->query("UPDATE marque_client SET position=".intval($this->position)." WHERE id=".$this->id);
            break;
            
            case "down":
            if($this->position==$this->last_position()) return false;
            $this->DB->query("UPDATE marque_client SET position=".intval($this->position)." WHERE position=".intval($this->position+1));
            $this->position+=1;
            return $this->DB->query("UPDATE marque_client SET position=".intval($this->position)." WHERE id=".$this->id);
            break;
        }
    }
    
    function last_position(){
        $maxposition=$this->DB->query_first("SELECT MAX(position) FROM marque_client");
        return $maxposition[0];
    }
    
    function upload_img($name){
        if(!isset($_FILES[$name]) || $_FILES[$name]['name']=="") return false;
        if(is_null($this->id)) return false;
        
        $pi=pathinfo($_FILES[$name]['name']);
        if(strtolower($pi['extension'])!="jpg") return false;
        
        return move_uploaded_file($_FILES[$name]['tmp_name'],__DIR__."/../".$this->img_path.$this->id.".jpg");
    }
    
    function __get($var){
        return isset($this->$var) ? $this->$var : null;
    }

    function __set($var,$value){
        $this->$var=$value;
    }
}

?>
