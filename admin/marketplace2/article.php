<?php

class article{

var $artid;
var $artcode;
var $catid;
var $active;
var $prix;
var $prixht;
var $libelle;
var $artcode_fabricant;
var $artcode_eurocave;
var $artcode_amazon;

function load($id){
         $requete=mysql_query("SELECT * FROM article WHERE artid=$id");
         if(mysql_num_rows($requete)==0 || $requete==false) return false;
         $row=mysql_fetch_array($requete);
         $this->internal_load($row);
         return true;
}

function internal_load($array){
        $this->artid=intval($array['artid']);
        $this->artcode=$array['artcode'];
        $this->catid=intval($array['catid']);
        $this->active=intval($array['active']);
        $this->prix=floatval($array['prix']);
        $this->prixht=floatval($array['prixht']);
        $this->strike_out=floatval($array['prixconseilleht']);
        $this->libelle=$array['libelleFR'];
        $this->artcode_eurocave=$array['artcode_eurocave'];
        $this->artcode_amazon=$array['artcode_amazon'];
}

function search($condition="",$order="",$group="",$limit=""){
                 $return=array();
                 $sql="SELECT * FROM article";
                 if($condition!="") $sql.=" WHERE ".$condition;
                 if($this->showenabled) $sql.=" AND `show`=1";
                 if($order!="") $sql.=" ORDER BY ".$order;
                 if($group!="") $sql.=" GROUP BY ".$group;
                 if($limit!="") $sql.=" LIMIT ".$limit;
                 $result = @mysql_query($sql);

                 while($model = @mysql_fetch_array($result)) {
                     if($model==false) return FALSE;
                     $c=new article();
                     $c->internal_load($model);
                     $return[$model['artid']]=$c;
                 }
                 return $return;
}

function caracteristiques($artid){
         $ret=array();
         $requete=mysql_query("SELECT c.libelleFR as caractname,cv.caractvalid AS caractvalid, cv.libelleFR AS caractvalname FROM article_caractval ac, caracteristiquevaleur cv, caracteristique c WHERE ac.caractvalid=cv.caractvalid AND c.caractid=cv.caractid AND cv.caractid<>3 AND ac.artid=".$artid);
         while($car=mysql_fetch_object($requete)){
             $ret[$car->caractname][$car->caractvalid]=$car->caractvalname;
         }
         return $ret;
}

}

?>