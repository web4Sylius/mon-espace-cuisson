<?php

/*
class menu{
    public $libelle;
    public $url;
    public $directory;
    public $target;
    public $ssmenu=array();
    public $cssclass;
    public $position=0;
    private $access=false;
    private $id;

    function __construct($libelle,$url="",$class="menu"){
	$this->libelle=$libelle;
	$this->url=$url;
	$this->cssclass=$class;
	$this->id=uniqid();
    }

    function addssmenu($ssmenu){
		$ssmenu->access=$this->access;
		$ssmenu->directory=$this->directory;
		$ssmenu->cssclass=$this->cssclass;
		$this->ssmenu[]=$ssmenu;
    }

    //Boucle récursive pour donner accès à tous les fichiers d'un dossier
    function get_file_content($chemin, $base_acces)
    {
    	$aFiles = scandir($chemin);
		foreach($aFiles as $le_fichier)
		{
			if(!in_array($le_fichier,array('.','..')))
			{
				if(strpos($le_fichier, '.php') == true) //Fichier
				{
					$this->access[] = $base_acces . (substr($base_acces,-1) != '/' ? '/' : '') . $le_fichier;
				}
				else //Dossier
				{
					$dossier_acces = $base_acces . (substr($base_acces,-1) != '/' ? '/' : '') . $le_fichier;

					$this->get_file_content($chemin . '/' . $le_fichier, $dossier_acces);
				}
			} 	
		}
    }

    function set_access($str){
		if($str=="") return true;
		$aAccess = explode(",",$str);
		foreach($aAccess as $l_acces) {
			if(strpos($l_acces, '.') === false) //Accès à tous les fichiers du dossier
			{
				$base_dir = $_SERVER['DOCUMENT_ROOT'].'/admin/' . $l_acces;
				$this->get_file_content($base_dir, $l_acces);
			} else {
				$this->access[] = $l_acces;
			}
		}
    }

    function has_access($url){
		if($this->access==false) return true;
		$pa=parse_url($url);
		return in_array($pa['path'],$this->access);
    }



	function draw(){
		$ssm="";
		if($this->ssmenu!=array()){
			foreach($this->ssmenu as $ssmenu)
				if($ssmenu->url=="" || !$this->access || $this->has_access($ssmenu->url)){
					$ssm.=$ssmenu->draw();
				}
			}
			if($ssm!=""){
			  $ssm="<a class=\"nav-link collapsed\" href=\"#\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseLayouts\" aria-expanded=\"false\" aria-controls=\"collapseLayouts\">
						<div class=\"sb-nav-link-icon\"><i class=\"fas fa-columns\"></i></div>
						".$ssm."
						<div class=\"sb-sidenav-collapse-arrow\"><i class=\"fas fa-angle-down\"></i></div>
					</a>";
			}
		}
		if($this->directory!="" && substr($this->directory,-1)!="/"){
			$this->directory.="/";
		}
		$text=$this->libelle;
		if($this->url!=""){
		  $text="<a class=\"nav-link\" href=\"".$this->directory.$this->url."\"".(!is_null($this->target)?" target=\"".$this->target."\"":"").">".$this->libelle."</a>";
		}
		
		
		return $ssm;
	}



    function draw(){
		$return="";
		$mouse="";
		$ssm="";
		$arrow="";
		if($this->ssmenu!=array()){
			$mouse=" onmouseover=\"showmenu('ssm_".$this->id."',".$this->position.",this)\" onmouseout=\"hidemenu('ssm_".$this->id."')\"";
			foreach($this->ssmenu as $ssmenu){
				if($ssmenu->url=="" || !$this->access || $this->has_access($ssmenu->url)){
					$ssm.=$ssmenu->draw();
				}
			}
			if($ssm!=""){
			  $ssm="<div id=\"ssm_".$this->id."\" class=\"".$this->cssclass."ssmenu\"".$mouse.">".$ssm."</div>";
			  $arrow="<div class=\"arrow\">".($this->position==0?"&#9654;":"&#9660;")."</div>";
			}
		}
		if($this->directory!="" && substr($this->directory,-1)!="/") $this->directory.="/";
		$text=$this->libelle;
		$onclick="";
		if($this->url!=""){
		  $text="<a href=\"".$this->directory.$this->url."\"".(!is_null($this->target)?" target=\"".$this->target."\"":"").">".$this->libelle."</a>";
		  $onclick=" onclick=\"document.location='".$this->directory.$this->url."';\"";
		}
		if(($ssm!="") || ($this->access!=false && $this->url!="" && $this->has_access($this->url)) || !$this->access){
			$return="<div id=\"".$this->id."\" class=\"".$this->cssclass."\"".$onclick.$mouse."><div class=\"text\">".$text."</div>".$arrow."</div>";
		}
		$return.=$ssm;

		return $return;
    }





    function js(){
	return "<script language=\"javascript\">
	var to=new Array();
	function showmenu(id,position,parent){
	   if(to[id]!=undefined) clearTimeout(to[id]);
	   if(document.getElementById(id)){
		var elem=document.getElementById(id);
		if(elem.style.display=='block') return;
		elem.style.display='block';
		var offsetleft=0;
		var offsettop=0;
		if(elem.parentNode.id.substr(0,4)!='ssm_'){
		    var coord=getCoord(parent);
		    offsetleft=coord.left;
		    offsettop=coord.top+document.body.scrollTop;
		}else
		    offsettop=parent.parentNode.offsetHeight-parent.offsetHeight;
		if(position==0){
		  elem.style.left=(offsetleft+parent.offsetWidth)+'px';
		  elem.style.top=(offsettop)+'px';
		}else{
		  elem.style.left=(coord.left)+'px';
		  elem.style.top=(coord.top+parent.offsetHeight+document.body.scrollTop)+'px';
		}
	   }
	}

	function hidemenu(id){
	   to[id]=setTimeout('hidetimeout(\"'+id+'\")',100);
	}
	function hidetimeout(id){
	    if(document.getElementById(id)) document.getElementById(id).style.display='none';
	}
	function getCoord(obj){
	    var orig = obj;
	    var left = 0;
	    var top = 0;
	    if(obj.offsetParent){
		    left += obj.offsetLeft;
		    top += obj.offsetTop;
		    while (obj = obj.offsetParent) {
			    left += (obj.offsetLeft-obj.scrollLeft+obj.clientLeft);
			    top += (obj.offsetTop-obj.scrollTop+obj.clientTop);
		    }
	    }
	    return {left:left, top:top, width: orig.offsetWidth, height: orig.offsetHeight};
		}
	</script>";
    }

}
*/
?>