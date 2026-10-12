<?php

/*
Configurateur complet d'origine
*/

class configurateur_type_complet {
    private $parent;
    private $DB;

    public $config=array(
    'catids' => array(0),
    'typeproduit' => array(
      "typesrep1" => array(1,2,3,7),
      "typesrep2" => array(283,266,282,339,483,482)
    ),
    'pose' => array(
      "poserep1" => array(1,2,3,265),
      "poserep2" => array(50,66,256,51,69),
      "poserep3" => array(254)
    ),
    'usage' => array(
      "usagerep1" => array(1),
      "usagerep3" => array(3),
      "usagerep4" => array(4)
    ),
    'finition' => array(
      "finitionrep1" => array(1),
      "finitionrep2" => array(2),
      "finitionrep3" => array(3)
    ),
    'typeencastrable' => array(
      "encasrep1" => array(50),
      "encasrep2" => array(66),
      "encasrep3" => array(256),
      "encasrep4" => array(51),
      "encasrep5" => array(69)
    ),
    'largeur' => array(
    	"largrep1" => array(303),//15cm
    	"largrep2" => array(304,545),//30cm
    	"largrep3" => array(428),//40cm 
    	"largrep4" => array(305,448),//60cm
    	"largrep5" => array(308) //120cm
    ),
    'hauteurniche' =>array(
    	"hauteurniche1" => array(438),//45cm
    	"hauteurniche2" => array(439),//60cm
    	"hauteurniche3" => array(440),//88cm
    	"hauteurniche4" => array(441),//123cm
    	"hauteurniche5" => array(442)//178cm
    ),
    'dimensions' => array("largeurmin","largeurmax","hauteurmin","hauteurmax","profondeurmin","profondeurmax"),
    'dimensionsvitrine' => array("largeurmin","largeurmax","hauteurmin","hauteurmax","profondeurmin","profondeurmax"),
    'temperatures' => array(3,4),
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
    'typecadre' => array(
      "typecadre1" => array(301),
      "typecadre2" => array(302),
      "typecadre3" => array(340),
      "typecadre4" => array(341)
    ),
    'posevitrine' => array(
      "posevitrine1" => array(266,282,283,339,555,556),//pose libre
      "posevitrine2" => array(482,483,554),	   //encastrable
      "posevitrine3" => array(283,339) 	   //ilot central
    ),
    'typevitrine' => array(
      "typevitrine1" => array(266), //faible profondeur pose libre
      "typevitrine2" => array(283), //traversante 2 faces vitrees ilot
      "typevitrine3" => array(282), //3 faces virtees pose libre
      "typevitrine4" => array(339), //4 faces vitrees ilot
      "typevitrine5" => array(482), //faible profondeur encastrable
      "typevitrine6" => array(483), //1 face vitree encastrable
      "typevitrine7" => array(555), //3 faces vitrees faible profondeur pose libre
      "typevitrine8" => array(554), //traversante 2 faces vitrees encastrable

    ),
    'marques' => array(1,12,117,58,109,99,10,6,104,9,2),
    'budget' => array(0,800,1200,2000,3000),// 0 800 1200 2000 3000
    'affine' => array("coloris","serrure","sensporte","classe","prix","marque","origine","dimensions","colorisencastrable")
    );
    
    public $config_info=array(
      "limite" => array("Configurateur normal","Configurateur par marque","Configurateur par budget","Configurateur par gamme"),
      "typeproduit" => array("Cave à vin","Vitrine à vin","Cave à vin sur mesure","Cadre à vin","Cave à vin sur chariot mobile"),
      "pose" => array("Pose libre","Encastrable","","Tout intégrable"),
      "posevitrine" => array("Pose libre","Encastrable","ilot central"),
      "typevitrine" => array("Faible profondeur pose libre","Traversante 2 faces vitrees ilot" ,"3 faces vitrées pose libre", "4 faces vitrées ilot","Faible profondeur encastrable","1 face vitrée encastrable","3 faces vitrées faible profondeur pose libre","Traversante 2 faces vitrées encastrable"),
      "typeencastrable" => array("Sous plan","En colonne","A tiroir","Grande hauteur","Tout intégrable"),
      "largeur" => array("15 cm","30 cm","40 cm","60 cm","120 cm"),
      "dimensions" => array("Largeur mini","Largeur maxi","Hauteur mini","Hauteur maxi","Profondeur mini","Profondeur maxi"),
      "dimensionsvitrine" => array("Largeur mini","Largeur maxi","Hauteur mini","Hauteur maxi","Profondeur mini","Profondeur maxi"),
      "classe" => array("A ou A+","A ou B"),
      "origine" => array("France","Europe"),
      "sensporte" => array("Porte à droite","Porte à gauche"),
      "serrure" => array("Serrure facultative","Serrure obligatoire")
    );

    private $types=array("start","ident","reload","limite",
    "utilisation","typeproduit","pose","typeencastrable","largeur","hauteurniche","dimensions","dimensionsvitrine","usage","temperatures","capacite","finition","esthetique","mintemp","maxtemp","installtemp","typeporte","rangement","classe","prix","coloris","colorisencastrable","marque","serrure","origine","sensporte","typecadre","couleurcadre","posevitrine","typevitrine","budget");
    
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
		if($last->affine) return null;
		//if($last==false) return "limite"; //pour demarrer etape marque, budget, gamme, jsp 
		if($last==false) return "typeproduit"; //pour demarrer direct etape typeproduit
		
		switch($last->type){
		    case "utilisation":
		    	return "typeproduit";
		    
		    case "limite":
		      switch($last->value){
				  case 0:
				  return "typeproduit";
				  
				  case 1:
				  return "marque";
				  
				  case 2:
				  return "budget";
				  
				  case 3:
				  return "finition";
		      }
		    
		    break;
		    
		    case "typeproduit":
		      switch($last->value){

				case $this->config['typeproduit']['typesrep1']: // cave à vin
				return "pose";
				
				case $this->config['typeproduit']['typesrep2']: // vitrine
				return "posevitrine";
				
				case $this->config['typeproduit']['typesrep3']: // sur mesure
				return "contact";
				
				case $this->config['typeproduit']['typesrep4']: // cadre à vin
				return "typecadre";
				
				case $this->config['typeproduit']['typesrep5']: // chariot
				return "";
		      }
		    break;
		    
		    case "pose":
		      switch($last->value){
			case $this->config['pose']['poserep1']: // pose libre
			case $this->config['pose']['poserep3']: // ilot central
			return "dimensions";
			
			case $this->config['pose']['poserep2']: // encastrable
			return "typeencastrable";
			
			default:
			return "";
		      }
		    break;

		    case 'typevitrine':
		    	return "dimensionsvitrine";
		    break;
		    
		    case "posevitrine":
		      return "typevitrine";
		    break;
		    
		    case "typeencastrable":
		      switch($last->value){
				case $this->config['typeencastrable']['encasrep1']: // sous plan
				return "largeur";

				case $this->config['typeencastrable']['encasrep2']: // colonne
				return "hauteurniche";
				
				case $this->config['typeencastrable']['encasrep4']: // grande hauteur
				return "temperatures";
				
				case $this->config['typeencastrable']['encasrep3']: // tiroir
				case $this->config['typeencastrable']['encasrep5']: // intégrable
				return "";
		      }
		    break;

		    case "largeur":
		    	$typeencas=$this->parent->get_response('typeencastrable');
		    	if($typeencas->value==$this->config['typeencastrable']['encasrep1']) {
			    	switch($last->value){
					case $this->config['largeur']['largrep2']: // 30cm
					case $this->config['largeur']['largrep4']: // 60cm
		    	case $this->config['largeur']['largrep1']: // 15cm
					case $this->config['largeur']['largrep3']: // 40cm
					case $this->config['largeur']['largrep5']: // 120cm
		    		return "temperatures";
		    		}	
			    }
			break;

		    case "hauteurniche":
		    	return "temperatures";
		    break;	
		    
		    case "dimensions":
		      $typeproduit=$this->parent->get_response('typeproduit');
		      switch($typeproduit->value){
				case $this->config['typeproduit']['typesrep1']:
					$pose=$this->parent->get_response('pose');
					if($pose->value!=$this->config['pose']['poserep2']){
					  return "usage";
					}else{
						return "rangement";
					}
				  
				case $this->config['typeproduit']['typesrep2']: // vitrine
					return "capacite";
				
				default:
					return "usage";
		      }
		    break; 

		    case "dimensionsvitrine":
		      $typeproduit=$this->parent->get_response('typeproduit');
		      switch($typeproduit->value){ 
				
				case $this->config['typeproduit']['typesrep2']: // vitrine
					return "capacite";
				break;

				case $this->config['pose']['poserep2']: //encastrable
			    	return null;
			    break;	

				default:
				return "usage";
		      }
		    break;
		    
		    case "typecadre":
		    return "";
		    break;
		    
		    case "usage":
		      return "capacite";
		    break;
		    
		    case "temperatures":
		    	$poseproduit=$this->parent->get_response('pose');
		    	$typeencas=$this->parent->get_response('typeencastrable');
		    	$usage=$this->parent->get_response('usage');

		    	if($typeencas->value==$this->config['typeencastrable']['encasrep1']) {
			    	return null;
			    }
			    else if($poseproduit->value==$this->config['pose']['poserep2']){//encastrable
			    	return "dimensions";
			    }
			    else{
			    	if($usage != "") {
			      	return "esthetique";
			      	}
			      else {
			      	return "capacite";
			      }
			    }
		    
		    break;
		    
		    case "capacite":
		    	$typeproduit=$this->parent->get_response('typeproduit');
		    	$usage=$this->parent->get_response('usage');
			    if($typeproduit->value==$this->config['typeproduit']['typesrep2']){ // vitrine
			      return null;
			    }elseif($typeproduit->value==$this->config['typeproduit']['typesrep1']){ // cave
			      $pose=$this->parent->get_response('pose');
			      if($pose->value==$this->config['pose']['poserep1']){ // pose libre
			      	if($usage != "") {
			      		return "temperatures";
			      	}
			      	else {
			      		return "esthetique";
			      	}
			      }else{
				  	$limite=$this->parent->get_response("limite");
					  /*if($limite->value!=3)
					      return "finition";
					  else*/
					      return $this->next_finition();
			      }
			    }
		    break;
		    
		    case "finition":
			    $limite=$this->parent->get_response("limite");
			    if($limite->value==3){
					$this->config['typeproduit']["typesrep3"]=false;
					return "typeproduit";
			    }
			    return $this->next_finition();
		    break;
		    
		    case "esthetique":
		    	$limite=$this->parent->get_response("limite");
		    	/*if($limite->value!=3)
					return "finition";
		    	else*/
				return $this->next_finition();
		    break;
		    
		    case "installtemp":
			    $pose=$this->parent->get_response('pose');
			    if($pose->value==$this->config['pose']['poserep1'])
			      return "typeporte";
		    break;
		    
		    case "typeporte":
			    $pose=$this->parent->get_response('pose');
			    if($pose->value==$this->config['pose']['poserep2']){ // encastrable
					return null;
			    }else{
					return "rangement";
			    }
		    break;
		    
		    case "marque":
		    $limite=$this->parent->get_response("limite");
		    if($limite->value==1){
			$this->config['typeproduit']["typesrep3"]=false;
			return "typeproduit";
		    }
		    break;
		    
		    case "budget":
		    case "prix":
		    $this->config['typeproduit']["typesrep3"]=false;
		    return "typeproduit";
		    break;
		    
		    case "rangement":
		    $limite=$this->parent->get_response("limite");
		    switch($limite->value){
		      case 1:
		      $this->config['affine']=array_diff($this->config['affine'],array("marque"));
		      break;
		      
		      case 2:
		      $this->config['affine']=array_diff($this->config['affine'],array("prix"));
		      break;
		    }
		    return "";
			//return "affine";
		    
		    case "colorisencastrable":
		    $this->config['affine'][]="installtemp";
		    $this->config['affine']=array_diff($this->config['affine'],array("coloris"));
		    return "";
		}
    }
    
    function next_finition(){
	$pose=$this->parent->get_response('pose');
	if($pose->value==$this->config['pose']['poserep2']){ // encastrable
	  $typeencastrable=$this->parent->get_response("typeencastrable");
	  switch($typeencastrable->value){
	    case $this->config['typeencastrable']['encasrep1']: // sous plan
	    case $this->config['typeencastrable']['encasrep2']: // colonne
	    return null;
	    
	    case $this->config['typeencastrable']['encasrep4']: // grande hauteur
	    return "typeporte";
	  }
	}else{
	  return "installtemp";
	}
    }
}

?>
