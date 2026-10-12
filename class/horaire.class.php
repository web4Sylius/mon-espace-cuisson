<?php

class horaire {
    private $DB;
    private $horaires;

    function __construct($DB,$site_code){
	$this->DB=$DB;
	$req=$this->DB->query("SELECT jour,decalage,matin_ouv,matin_ferm,am_ouv,am_ferm FROM horaire WHERE site_code='".$site_code."'");
	while($h=$this->DB->fetch_array($req)){
	    $this->horaires[$h['jour']]=$h;
	}
    }
    
    function ouvert($time=null){
	if(is_null($time)) $time=time();
	$day=intval(date("N",$time));
	if(!isset($this->horaires[$day])) return false;
	if($this->ferie($time)) return false;
	$h=$this->horaires[$day];
	$dectime=$this->hmdec(date("G:i",$time));
	if($dectime>=$this->hmdec($h['matin_ouv'],$h['decalage']) && $dectime<=$this->hmdec($h['matin_ferm'],$h['decalage'])) return true;
	if($dectime>=$this->hmdec($h['am_ouv'],$h['decalage']) && $dectime<=$this->hmdec($h['am_ferm'],$h['decalage'])) return true;
	return false;
    }
    
    private function hmdec($str,$decal=0){
	list($h,$m)=explode(":",$str);
	return ($h-$decal)+(round($m)/60);
    }
    
    function ferie($time=null){
	if(is_null($time)) $time=time();
	
	$ferie=array(1=>array(1),2=>array(),3=>array(),4=>array(),5=>array(1,8),6=>array(),7=>array(14),8=>array(15),9=>array(),10=>array(),11=>array(1,11),12=>array(25));
	$paques=easter_date(date("Y"))+86400;
	if($paques<time()) $paques=easter_date(date("Y")+1)+86400;
	$ferie[date("n",$paques)][]=date("j",$paques);

	$ascension=easter_date(date("Y"))+(39*86400);
	if($ascension<time()) $ascension=easter_date(date("Y")+1)+(39*86400);
	$ferie[date("n",$ascension)][]=date("j",$ascension);

	$pentecote=easter_date(date("Y"))+(50*86400);
	if($pentecote<time()) $pentecote=easter_date(date("Y")+1)+(50*86400);
	$ferie[date("n",$pentecote)][]=date("j",$pentecote);
	
	return in_array(date("j",$time),$ferie[date("n",$time)]);
    }
    
    function format($day){
	if(!isset($this->horaires[$day])) return false;
	$h=$this->horaires[$day];
	$list=array("matin_ouv","matin_ferm","am_ouv","am_ferm");
	foreach($list as $htype){
	    $heures[$htype]=$h[$htype];
	    if(substr($heures[$htype],strpos($heures[$htype],":")+1)=="00") $heures[$htype]=$this->hmdec($heures[$htype])."h";
	}
	if($heures['matin_ferm']==$heures['am_ouv']){
	    return $heures['matin_ouv']."-".$heures['am_ferm'];
	}else
	    return $heures['matin_ouv']."-".$heures['matin_ferm']." ".$heures['am_ouv']."-".$heures['am_ferm'];
	
    }
}

?>