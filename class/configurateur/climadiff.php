<?php

/*
Configurateur climadiff avintage
*/

class configurateur_type_climadiff {
    private $parent;
    private $DB;

    private $config=array(
    'catids' => array(1,2,3,7),
    'pose' => array(
      "poserep1" => array(1,2,3),
      "poserep2" => array(50,66,51),
      "poserep3" => array(254)
    ),
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
    'typeencastrable' => array(
      "encasrep1" => array(50),
      "encasrep2" => array(66),
      "encasrep3" => array(256),
      "encasrep4" => array(51),
      "encasrep5" => array(69)
    ),
    'dimensions' => array("largeurmin","largeurmax","hauteurmin","hauteurmax","profondeurmin","profondeurmax"),
    'temperatures' => array(3,4),
    'classe' => array(
      1 => array("A","A+","A++","A+++"),
      2 => array("A","A+","A++","A+++","B")
    ),
    'origine' => array(0 => array(0), 1=> array(0,1)),
    'serrure' => array(
      "serrure0" => array(0,1,2),
      "serrure1" => array(1,2)
    ),
    'affine' => array("serrure","classe","prix","origine")
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
      "utilisation","typeproduit","pose","typeencastrable","dimensions","usage","temperatures","capacite","finition","esthetique","mintemp","maxtemp","installtemp","typeporte","rangement","classe","prix","coloris","colorisencastrable","marque","serrure","origine","sensporte","typecadre","couleurcadre","posevitrine");
    
    private $laststep=array("rangement","colorisencastrable");
    
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
	    $marques->setvalue(array(6,12));
	    $marques->save();
	    return "pose";
	}
	
	switch($last->type){
	    
	    case "pose":
	      switch($last->value){
		case $this->config['pose']['poserep1']: // pose libre
		case $this->config['pose']['poserep3']:
		return "dimensions";
		
		case $this->config['pose']['poserep2']: // encastrable
		return "typeencastrable";
		
		default:
		return "";
	      }
	    break;
	    
	    
	    case "typeencastrable":
	      switch($last->value){
		case $this->config['typeencastrable']['encasrep1']: // sous plan
		case $this->config['typeencastrable']['encasrep2']: // colonne
		return "temperatures";
		
		case $this->config['typeencastrable']['encasrep4']: // grande hauteur
		return "usage";
		
		case $this->config['typeencastrable']['encasrep3']: // tiroir
		case $this->config['typeencastrable']['encasrep5']: // intégrable
		return "";
	      }
	    break;
	    
	    case "dimensions":
	      $typeproduit=$this->parent->get_response('typeproduit');
	      switch($typeproduit->value){
		case $this->config['typeproduit']['typesrep1']:
		$pose=$this->parent->get_response('pose');
		if($pose->value!=$this->config['pose']['poserep2'])
		  return "usage";
		  
		
		default:
		return "usage";
	      }
	    break;
	    
	    
	    case "usage":
	    if(in_array($last->value,$this->config['temperatures']))
	      return "temperatures";
	    else
	      return "capacite";
	    break;
	    
	    case "temperatures":
	    return "capacite";
	    break;
	    
	    case "capacite":
	    $typeproduit=$this->parent->get_response('typeproduit');

	    if($typeproduit->value==$this->config['typeproduit']['typesrep1']){ // cave
	      $pose=$this->parent->get_response('pose');
	      if($pose->value==$this->config['pose']['poserep1']){ // pose libre
		  return "esthetique";
	      }else
		  return "finition";
	    }
	    break;
	    
	    case "finition":
	    $pose=$this->parent->get_response('pose');
	    if($pose->value==$this->config['pose']['poserep2']){ // encastrable
	      $typeencastrable=$this->parent->get_response("typeencastrable");
	      switch($typeencastrable->value){
		case $this->config['typeencastrable']['encasrep1']: // sous plan
		case $this->config['typeencastrable']['encasrep2']: // colonne
		return "colorisencastrable";
		
		case $this->config['typeencastrable']['encasrep4']: // grande hauteur
		return "typeporte";
	      }
	    }else{
	      return "installtemp";
	    }
	    break;
	    
	    case "esthetique":
	    return "finition";
	    break;
	    
	    case "installtemp":
	    $pose=$this->parent->get_response('pose');
	    if($pose->value==$this->config['pose']['poserep1'])
	      return "typeporte";
	    break;
	    
	    case "typeporte":
	    $pose=$this->parent->get_response('pose');
	    if($pose->value==$this->config['pose']['poserep2']){ // encastrable
		return "colorisencastrable";
	    }else{
		return "rangement";
	    }
	    break;
	    
	    case "rangement":
	    return "affine";
	    
	    case "colorisencastrable":
	    return "affineencastrable";
	}
    }
}

?>