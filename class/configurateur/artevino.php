<?php

/*
Configurateur artevino transtherm
*/

class configurateur_type_artevino {
    private $parent;
    private $DB;

    private $config=array(
    'catids' => array(1,2,3,7,265),
    'usage' => array(
      "usagerep1" => array(1),
      "usagerep2" => array(1),
      "usagerep3" => array(3),
      "usagerep4" => array(4)
    ),
    'finition' => array(
      "finitionrep1" => array(1),
      "finitionrep2" => array(2),
      "finitionrep3" => array(3),
      "finitionrep4" => array(4)
    ),
    'classe' => array(
      1 => array("A","A+","A++","A+++"),
      2 => array("A","A+","A++","A+++","B")
    ),
    'origine' => array(0 => array(0), 1=> array(0,1)),
    'sensporte' => array(
      "sensporte0" => array(0,2),
      "sensporte1" => array(1,2)
    ),
    'serrure' => array(
      "serrure0" => array(0,1,2),
      "serrure1" => array(1,2)
    ),
    'affine' => array("coloris","classe")
    );
    
    public $config_info=array(
      "typeproduit" => array("Cave à vin","Vitrine à vin","Cave à vin sur mesure"),
      "pose" => array("Pose libre","Encastrable","","Tout intégrable"),
      "posevitrine" => array("Pose libre","Ilot central","Encastrable"),
      "typeencastrable" => array("Sous plan","En colonne","A tiroir","Grande hauteur","Tout intégrable"),
      "dimensions" => array("Largeur mini","Largeur mini","Hauteur mini","Hauteur maxi","Profondeur mini","Profondeur maxi"),
      "classe" => array("A ou A+","A ou B"),
      "origine" => array("France","Europe"),
      "sensporte" => array("Porte à droite","Porte à gauche"),
      "serrure" => array("Serrure facultative","Serrure obligatoire")
    );

    private $types=array("start","ident","reload",
      "usage","capacite","finition","typeporte","rangement","classe","prix","coloris","marque");
    
    private $laststep=array("rangement");
    
    function __construct($DB,$parent){
	$this->DB=$DB;
	$this->parent=$parent;

    }
    
    function __get($var){
	if(!is_null($this->$var))
	    return $this->$var;
    }

    function next(){
	$last=$this->parent->last_reponse();
	if($last==false){
	    $marques=new configurateur_reponse($this->DB);
	    $marques->type="marque";
	    $marques->date=time();
	    $marques->configurateurid=$this->parent->configurateurid;
	    $marques->setvalue(array(1,2));
	    $marques->save();
	    return "usage";
	}
	
	switch($last->type){
	    case "usage":
	    return "capacite";
	    
	    case "capacite":
	    return "finition";
	    break;
	    
	    case "finition":
	    return "typeporte";
	    break;
	    
	    case "typeporte":
	    return "rangement";
	    break;
	    
	    case "rangement":
	    return "affine";
	    break;
	    
	}
    }
}

?>