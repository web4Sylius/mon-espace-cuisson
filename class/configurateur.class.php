<?php

class configurateur{ 

private $DB;
private $cookielen=604800;

public $configurateurid;
public $date;
public $userid;
public $session;
public $commercial;
public $artids;
public $configtype;
public $affine;

public $display_max=10;
public $display_msg=10;

private $config=array();

public $config_info=array();

private $types=array();


function __construct($DB,$configurateurid=null,$type=null){
    $this->DB=$DB;
    if(!is_null($configurateurid)){
	$data=$this->DB->query_first("SELECT * FROM configurateur WHERE configurateurid=".intval($configurateurid));
	$this->load($data);
    }else
	$this->loadconfigtype($type);
}

function loadcookie(){
    if(isset($_COOKIE['conf'])){
	$data=$this->DB->query_first("SELECT * FROM configurateur WHERE configurateurid=".intval($_COOKIE['conf']));
	$this->load($data);
	return true;
    }
    return false;
}

function load($data){
    $this->configurateurid=$data['configurateurid'];
    $this->date=$data['date'];
    $this->userid=$data['userid'];
    $this->session=$data['s'];
    $this->commercial=$data['commercial'];
    $this->artids=unserialize($data['artids']);
    $this->affine=unserialize($data['affine']);
    $this->loadconfigtype($data['type']);
}

function loadconfigtype($type){

    if(is_null($type)) $type="";
    switch($type){
	case "":
	case "complet":
	$this->configtype="complet";
	break;
	
	case "artevino":
	$this->configtype="artevino";
	break;
	
	case "climadiff":
	$this->configtype="climadiff";
	break;
	
	case "budget":
	$this->configtype="budget";
	break;
	
	default:
	$this->configtype="complet";
    }
    
    $classname="configurateur_type_".$this->configtype;
    $this->configurateur_type=new $classname($this->DB,$this);
    $this->config=$this->configurateur_type->config;
    $this->types=$this->configurateur_type->types;
    $this->config_info=$this->configurateur_type->config_info;
}

function get_list($session,$commercial="",$max=10){
    $return=array();
    $sql="SELECT * FROM configurateur WHERE ";
    if($commercial=="" || is_null($commercial) || $commercial==false)
      $sql.=" s='".mysqli_real_escape_string($this->DB->link_id,$session)."'";
    else
      $sql.=" commercial='".mysqli_real_escape_string($this->DB->link_id,$commercial)."'";
    $sql.=" ORDER BY date DESC LIMIT ".intval($max);
    $req=$this->DB->query($sql);
    while($data=$this->DB->fetch_array($req)){
	$conf=new configurateur($this->DB);
	$conf->load($data);
	$return[$conf->configurateurid]=$conf;
    }
    return $return;
}

function cookie(){
    setcookie('conf',$this->configurateurid,time()+$this->cookielen,"/");
}

function clear(){
    unset($_COOKIE['conf']);
    return setcookie('conf',null,-1,"/");
}

function insert(){
    $return=$this->DB->query("INSERT INTO configurateur SET date=".time().", type='".mysqli_real_escape_string($this->DB->link_id,$this->configtype)."', userid=".intval($this->userid).", s='".$this->session."', commercial='".$this->commercial."'");
    $this->configurateurid=$this->DB->insert_id();
    $this->cookie();
    return $return;
}

function update(){
    return $this->DB->query("UPDATE configurateur SET userid=".intval($this->userid)." WHERE configurateurid=".$this->configurateurid);
}

function save_articles($artids){
    if(intval($this->configurateurid)==0) return false;
    if(!is_array($artids) || count($artids)==0){
        return $this->DB->query("UPDATE configurateur SET artids='' WHERE configurateurid=".$this->configurateurid);
    }
    $artids=array_slice($artids,0,10);
    return $this->DB->query("UPDATE configurateur SET artids='".serialize($artids)."' WHERE configurateurid=".$this->configurateurid);
}

function reponses(){
    $return=array();
    $req=$this->DB->query("SELECT * FROM configurateur_reponse WHERE configurateurid=".intval($this->configurateurid)." ORDER by nb");
    while($r=$this->DB->fetch_array($req)){
		$reponse=new configurateur_reponse($this->DB,$r);
		if(!$reponse->affine) $this->configurateur_type->config['affine']=array_diff($this->configurateur_type->config['affine'],array($reponse->type));
		$return[$reponse->nb]=$reponse;
    }
    ksort($return);
    return $return;
}

function get_response($type){
    $r=$this->DB->query_first("SELECT * FROM configurateur_reponse WHERE configurateurid=".intval($this->configurateurid)." AND type='".mysqli_real_escape_string($this->DB->link_id,$type)."'");
    if($r=="") return false;
    $reponse=new configurateur_reponse($this->DB,$r);
    return $reponse;
}

function reponses_count(){
    $return=$this->DB->query_first("SELECT COUNT(*) FROM configurateur_reponse WHERE configurateurid=".intval($this->configurateurid));
    return $return[0];
}

function del_reponses($fromnb){
    $return=$this->DB->query("DELETE FROM configurateur_reponse WHERE configurateurid=".intval($this->configurateurid)." AND nb>=".intval($fromnb));
    return $return;
}

function del_reponses2(){
    $return=$this->DB->query("DELETE FROM configurateur_reponse WHERE configurateurid=".intval($this->configurateurid));
    return $return;
}

function last_reponse(){
    $r=$this->DB->query_first("SELECT * FROM configurateur_reponse WHERE configurateurid=".$this->configurateurid." ORDER BY nb DESC");
    if($r=="") return false;
    return new configurateur_reponse($this->DB,$r);
}

function verif_type($type){
    return in_array($type,$this->types);
}

function config($name){
    $this->config=$this->configurateur_type->config;
    return $this->config[$name];
}

function laststep(){
    return $this->configurateur_type->laststep;
}

function is_affine($type){
    return in_array($type,$this->configurateur_type->config['affine']);
}

function in_artids($artid){
    if($this->artids==false) return false;
    return in_array($artid,$this->artids);
}

function requetage(&$categorie,$requetes){
    foreach($requetes as $type=>$value){
	if(isset($this->config[$type][$value])) $value=$this->config[$type][$value];
// 	if($type=="capacite") $value=array_search($value,$categorie->capacites);
	$reponse=new configurateur_reponse($this->DB,array("type"=>$type,"value"=>serialize($value)));

	$this->config_categorie($categorie,$reponse);
    }
}

function config_categorie(&$categorie,$reponse=null){

    if(is_null($reponse)){
		$reponses=$this->reponses();
    }else
	$reponses=array($reponse);
    if($categorie->subcatids==array()){
		$startcatids=0;
		if(!is_null($this->config('catids'))){
		    $startcatids=$this->config('catids');
		}
		$categorie->setsubcatids($startcatids);
    }
    foreach($reponses as $reponse){
		switch($reponse->type){
		    case "utilisation":
		    	if($reponse->value==1) $categorie->setparam("pro",2);
		    break;
		    
		    case "typeproduit":
			    $subcatids=$categorie->getsubcatids($reponse->value);
			    $categorie->setparam("subcatids",$subcatids);
		    break;
		    
		    case "posevitrine":
		    case "typevitrine":
		    case "pose":
			    $subcatids=$categorie->getsubcatids($reponse->value);
			    $categorie->setparam("subcatids",$subcatids);
		    break;

		    
		    case "typeencastrable":
		    case "largeur":
		    case "hauteurniche":
			    $subcatids=$categorie->getsubcatids($reponse->value);
			    $categorie->setparam("subcatids",$subcatids);
		    break;
		    
		    case "dimensionsvitrine":
		    case "dimensions":
		    foreach($this->config["dimensions"] as $key=>$var){
			if($reponse->value[$key]>0) $categorie->setparam($var,$reponse->value[$key]);
		    }
		    break;
		    
		    case "typecadre":
			    $subcatids=$categorie->getsubcatids($reponse->value);
			    $categorie->setparam("subcatids",$subcatids);
		    break;
		    
		    case "usage":
		    	if($reponse->value>0) $categorie->setparam("usageids",$reponse->value);
		    break;
		    
		    case "temperatures":
			    $value=intval(is_array($reponse->value)?$reponse->value[0]:$reponse->value);
			    if($value>0){
					$categorie->setparam("nbtemperature",$value);
			    }
		    break;
		    
		    case "capacite":
		    	$categorie->setparam("capaciteids",$reponse->value);
		    	$categorie->setparam("subcatids",$subcatids);
		    break;
		    
		    case "finition":
		    	if($reponse->value[0]!=0) $categorie->setparam("finitionids",$reponse->value);
		    break;
		    
		    case "esthetique":
		    	if($reponse->value[0]==1) $categorie->setparam("esthetique",1);
		    break;
		    
		    case "mintemp":
		    	if($reponse->value<1000) $categorie->setparam("mintemp",$reponse->value);
		    break;
		  
		    case "maxtemp":
		    	if($reponse->value<1000) $categorie->setparam("maxtemp",$reponse->value);
		    break;
		    
		    case "installtemp":
			    if($reponse->value[0]<1000) $categorie->setparam("mintemp",$reponse->value[0]);
			    if($reponse->value[1]<1000) $categorie->setparam("maxtemp",$reponse->value[1]);
		    break;
		    
		    case "typeporte":
			    $value=intval(is_array($reponse->value)?$reponse->value[0]:$reponse->value);
			    if($value>0) $categorie->setparam("typeporteids",$value);
		    break;
		    
		    case "rangement":
		    	if($reponse->value!=array(0)) $categorie->setparam("moderangementids",$reponse->value);
		    break;
		    
		    case "classe":
			    $value=$reponse->value[0];
			    if($value!=0){
					$categorie->setparam("energy_label",$this->config['classe'][$value]);
			    }
		    break;
		    
		    case "prix":
		    case "budget":
		    	if($reponse->value!=array(1000)) $categorie->setparam("prixids",$reponse->value);
		    break;
		   
		    
		    case "colorisencastrable":
		    	 if($reponse->value!=array(0)) {
					$categorie->setparam("colorisencastrableids",$reponse->value);
			    }
		    break;
		    case "coloris":
			    if($reponse->value!=array(0))
					$categorie->setparam("colorisids",$reponse->value);
		    break;
		    
		    case "marque":
			    if($reponse->value!=array(0))
					$categorie->setparam("marqueids",$reponse->value);
		    break;
		    
		    case "origine":
			    if($reponse->value!=array(1000))
					$categorie->setparam("origineids",$reponse->value);
		    break;
		    
		    case "sensporte":
			    if($reponse->value!=array(1000))
					$categorie->setparam("sensporteids",$reponse->value);
		    break;
		    
		    case "serrure":
		    	if($reponse->value!=array(1000))
		    	$categorie->setparam("serrure",$reponse->value);
		    break;
		}
    }
}

function next(){

    return $this->configurateur_type->next();
}

function setaffine($type){
    if($type==null){
	$this->affine=false;
	return $this->DB->query("UPDATE configurateur SET affine='' WHERE configurateurid=".$this->configurateurid);
    }

    if($this->affine==false){
	$this->affine=array($type);
    }else{
	$this->affine[]=$type;
	$this->affine=array_unique($this->affine);
    }
    return $this->DB->query("UPDATE configurateur SET affine='".serialize($this->affine)."' WHERE configurateurid=".$this->configurateurid);
}

function getaffine($type){
    if($this->affine==false) return false;
    return in_array($type,$this->affine);
}	

}

class configurateur_reponse{

private $DB;
public $id;
public $configurateurid;
public $date;
public $type;
public $nb;
public $value;
public $affine;

function __construct($DB,$data=null){
    $this->DB=$DB;
    if(!is_null($data)){
	$this->load($data);
    }
}

private function load($data){
    $this->id=$data['id'];
    $this->configurateurid=$data['configurateurid'];
    $this->date=$data['date'];
    $this->type=$data['type'];
    $this->nb=$data['nb'];
    $this->value=unserialize($data['value']);
    $this->affine=(bool) $data['affine'];
}

function setvalue($value){
    if(is_array($value)){
	$this->value=array();
	foreach($value as $v) $this->value[]=intval($v);
    }else
	$this->value=strip_tags(stripslashes($value));
}

function exists(){
    $this->id=0;
    if(intval($this->configurateurid)==0 || $this->type=="") return false;
    $rep=$this->DB->query_first("SELECT * FROM configurateur_reponse WHERE configurateurid='".$this->configurateurid."' AND type='".mysqli_real_escape_string($this->DB->link_id,$this->type)."'");
    $this->id=intval($rep['id']);
    if($this->id!==0) $this->load($rep);
    return $this->id!==0;
}

function save(){
    $sql="configurateurid='".intval($this->configurateurid)."', type='".mysqli_real_escape_string($this->DB->link_id,$this->type)."', nb=".intval($this->nb).", value='".mysqli_real_escape_string($this->DB->link_id,serialize($this->value))."', affine=".intval($this->affine);
    if($this->id==0)
		$sql="INSERT INTO configurateur_reponse SET date=".time().", ".$sql;
    else
		$sql="UPDATE configurateur_reponse SET ".$sql." WHERE id='".$this->id."'";
	
    return $this->DB->query($sql);
}

function infos(){
    $configurateur=new configurateur($this->DB);
    switch($this->type){
	case "limite":
	return $configurateur->config_info['limite'][$this->value];
    
	case "utilisation":
	return $this->value==1?"Utilisation pro":"Utilisation personnelle";
	
	case "typeproduit":
	foreach(array_values($configurateur->config('typeproduit')) as $id=>$value)
	    if($this->value==$value) return $configurateur->config_info['typeproduit'][$id];
	return "<i>Type produit indéterminé</i>";

	case "typevitrine":
	foreach(array_values($configurateur->config($this->type)) as $id=>$value)
	    if($this->value==$value) return $configurateur->config_info[$this->type][$id];
	return "<i>Type vitrine indéterminé</i>";
	    
	case "pose":
	case "posevitrine":
	foreach(array_values($configurateur->config($this->type)) as $id=>$value)
	    if($this->value==$value) return $configurateur->config_info[$this->type][$id];
	return "Type de pose indéterminé</i>";
	 
	case "largeur":
	foreach(array_values($configurateur->config('largeur')) as $id=>$value)
	    if($this->value==$value) return $configurateur->config_info['largeur'][$id];
	return "<i>Largeur indéterminé</i>";
	 
	case "typeencastrable":
	foreach(array_values($configurateur->config('typeencastrable')) as $id=>$value)
	    if($this->value==$value) return $configurateur->config_info['typeencastrable'][$id];
	return "<i>Type encastrable indéterminé</i>";
	 
	case "dimensions":
	$txtlist=array();
	foreach($configurateur->config('dimensions') as $id=>$key){
	    if($this->value[$id]>0) $txtlist[]=$configurateur->config_info['dimensions'][$id]." ".$this->value[$id]." mm ";
	}
	if($txtlist==array()) return "<i>Dimensions indéterminées</i>";
	return trim(implode(", ",$txtlist));
	
	case "usage":
	if($this->value>0){
	    $usage=$this->DB->query_first("SELECT nomFR FROM recherche_usage WHERE rechercheusageid=".$this->value);
	    return $usage[0];
	}
	return "<i>Usage indéterminé</i>";
	
	case "temperatures":
	$value=is_array($this->value) ? $this->value[0] : $this->value;
	if($value>0 && $value<=5) return $value." températures";
	if($value==1000) return "Multi température";
	return "<i>Nombre de températures indéterminé</i>";
	
	case "capacite":
	$categorie=new categorie($this->DB);
	$txtlist=array();
	$capacites=is_array($this->value) ? $this->value : explode(",",$this->value);
	if(is_array($capacites)){
	    foreach($capacites as $capaciteid){
	      if($capaciteid==0)
		  $txtlist[]="Capacité inférieure à ".$categorie->capacites[1];
	      elseif($capaciteid==count($categorie->capacites))
		  $txtlist[]="Capacité supérieure à ".$categorie->capacites[$capaciteid];
	      else
		  $txtlist[]="Capacité entre ".$categorie->capacites[$capaciteid]." et ".$categorie->capacites[$capaciteid+1];
	    }
	}
	if($txtlist==array()) return "<i>Capacité indéterminée</i>";
	return trim(implode(", ",$txtlist));
	
	case "finition":
	$txt="";
	if(!is_array($this->value)) $this->value=array($this->value);
	if($this->value[0]!=0){
	    $finitions=$this->DB->query("SELECT nomFR FROM recherche_finition WHERE finitionid IN (".implode(",",$this->value).")");
	    while($finition=$this->DB->fetch_array($finitions)){
		$txt.=" ".$finition[0];
	    }
	    return $txt;
	}
	return "<i>Niveau de gamme indéterminé</i>";
	
	case "esthetique":
	$value=is_array($this->value) ? $this->value[0] : $this->value;
	if($value==1) return "Produit esthétique";
	return "<i>Esthétique indifférente</i>";
	
	case "mintemp":
	if($this->value<1000) return "Température mini ".$this->value;
	return "";
	
	case "maxtemp":
	if($this->value<1000) return "Température maxi ".$this->value;
	return "";
	
	case "installtemp":
	$return="";
	if($this->value!=array(1000,1000)){
	    $return="Température";
	    if($this->value[0]<1000) $return.=" mini ".$this->value[0];
	    if($this->value[1]<1000) $return.=" maxi ".$this->value[1];
	}else
	    $return="<i>Température d'installation indéterminée</i>";

	return $return;
	
	case "typeporte":
	$value=(is_array($this->value) ? $this->value[0] : $this->value);
	if($value>0){
	    $typeporte=$this->DB->query_first("SELECT nomFR FROM recherche_typeporte WHERE recherchetypeporteid=".$value);
	    return $typeporte[0];
	}
	return "<i>Type de porte indéterminée</i>";
	
	case "rangement":
	$txt="";
	if(is_array($this->value)){
	    $rangements=$this->DB->query("SELECT nomFR FROM recherche_moderangement WHERE recherchemoderangementid IN (".implode(",",$this->value).")");
	    while($rangement=$this->DB->fetch_array($rangements)){
	      $txt.=$rangement[0]." ";
	    }
	}
	if($txt=="") $txt="<i>Type de rangement indéterminé</i>";
	return trim($txt);
	
	case "classe":
	if($this->value[0]>0) return $configurateur->config_info['classe'][$this->value[0]-1];
	return "<i>Toutes classes énergétiques</i>";
	
	case "prix":
	$txt="";
	if(is_array($this->value)){
	    $categorie=new categorie($this->DB);
	    foreach($this->value as $prixid){
		if($prixid==0) $txt.="Prix inférieur à ".$categorie->listeprix[1]." ";
		if($prixid==count($categorie->listeprix)) $txt.="Prix supérieur à ".$categorie->listeprix[$prixid]." ";
		$txt.="Prix entre ".$categorie->listeprix[$prixid]." et ".$categorie->listeprix[$prixid+1]." ";
	    }
	}
	if($txt=="") $txt="<i>Prix indéterminé</i>";
	return trim($txt);
	
	case "budget":
	$txtlist=array();
	$configbudget=$configurateur->config("budget");
	$categorie=new categorie($this->DB);
	foreach($this->value as $key) {
	    if($key<1000){
			if($key==3)
				//$txtlist[]="Inférieur à ".$configbudget[1];
				$txtlist[]="Inférieur à ".$categorie->listeprix[$key];
			elseif($key==8)
				//$txtlist[]="Supérieur à ".$configbudget[$key];
				$txtlist[]="Supérieur à ".$categorie->listeprix[$key];
			elseif($key==5 || $key==6)
				$txtlist[]="Entre ".$categorie->listeprix[5]." et ".$categorie->listeprix[7];
			else
				//$txtlist[]="Entre ".$configbudget[$key]." et ".$configbudget[$key+1];
				$txtlist[]="Entre ".$categorie->listeprix[$key]." et ".$categorie->listeprix[$key+1];
	    }
	}
	if($txtlist==array()) return "<i>Budget indéterminé</i>";
	return trim(implode(", ",$txtlist));
	break;
	
	case "coloris":
	$txt="";
	if(is_array($this->value)){
	    $coloris=$this->DB->query("SELECT nomFR FROM recherche_coloris WHERE recherchecolorisid IN (".implode(",",$this->value).")");
	    while($color=$this->DB->fetch_array($coloris))
	      $txt.=$color[0]." ";
	}
	if($txt=="") $txt="<i>Coloris indéterminé</i>";
	return trim($txt);

	case "colorisencastrable":
	if(is_array($this->value)){
		$colorisencastrable=$this->DB->query("SELECT t.FR FROM traduction t WHERE t.FR LIKE '%".$this->value."%'");
	 	while($color=$this->DB->fetch_array($colorisencastrable))
	      $txt.=$color[0]." ";
	}
	if($txt=="") $txt="<i>Coloris indéterminé</i>";
	return trim($txt);

	case "marque":
	$txtlist=array();
	if(is_array($this->value)){
	    $marques=$this->DB->query("SELECT libelle FROM marque WHERE marqueid IN (".implode(",",$this->value).")");
	    while($marque=$this->DB->fetch_array($marques)){
		$txtlist[]=$marque[0];
	    }
	}
	if($txtlist==array()) return "<i>Marque indéterminée</i>";
	return trim(implode(", ",$txtlist));
	
	case "origine":
	if(is_array($this->value)){
	    foreach(array_values($configurateur->config('origine')) as $id=>$value){
		if($value==$this->value) return "Origine ".$configurateur->config_info['origine'][$id];
	    }
	}
	return "<i>Origine indéterminée</i>";
	
	case "sensporte":
	if(is_array($this->value)){
	    foreach(array_values($configurateur->config('sensporte')) as $id=>$value){
		if($value==$this->value) return $configurateur->config_info['sensporte'][$id];
	    }
	}
	return "<i>Sens porte indéterminée</i>";
	
	case "serrure":
	if(is_array($this->value)){
	    foreach(array_values($configurateur->config('serrure')) as $id=>$value){
		if($value==$this->value) return $configurateur->config_info['serrure'][$id];
	    }
	}
	return "<i>Option serrure indéterminée</i>";
    }
    return false;
}

}
?>