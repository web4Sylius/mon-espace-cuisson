<?php

function display_contact(){
    global $template;
    $template->evaltemplate("ConfigurateurContact","html");
}

function display_limite($reponse=null){
    global $categorie;
    global $configurateur;
    global $template;
    
    for($i=0;$i<=3;$i++){
	$template->parts["checklimite".$i]="";
	if(isset($reponse) && $reponse->value==$i){
	    $template->parts["checklimite".$i]="checked";
	    $template->parts["limite".$i."class"]="clicked";
	}
    }
    
    $template->evaltemplate("ConfigurateurLimite","html");
}

function display_typeproduit($reponse=null){
    global $categorie;
    global $configurateur;
    global $template;
    
    $nb=0;
    $onlykey=false;
    $return=false;
    foreach($configurateur->config('typeproduit') as $key=>$catids){
		$subcatids=$categorie->getsubcatids($catids);
		if($catids!=false && $categorie->articlecount($subcatids)>0){
			$template->parts[$key]=implode(",",$catids);
			$template->parts["check".$key]="";
			$nb++;
			$onlykey=$key;
			$return=$catids;
			if(!is_null($reponse) && $reponse->value==$catids){
			  $template->parts["check".$key]="checked";
			  $template->parts[$key."class"]="clicked";
			}
		}else
			$template->parts[$key."class"]="hidden";
    }
	
    if($nb==1){
		$template->parts["check".$onlykey]="checked";
		$template->parts[$onlykey."class"]="clicked";
    }else
		$return=false;
	
    $template->evaltemplate("ConfigurateurTypeproduit","html");
    return $return;
}

function display_pose($reponse=null){
    global $categorie;
    global $configurateur;
    global $template;
    
    $nb=0;
    $onlykey=false;
    $return=false;
    $availcatids=$categorie->getavailsubcatids();
    foreach($configurateur->config('pose') as $key=>$catids){
	    $class="hidden";
	    
	    foreach($catids as $catid){
		if(in_array($catid,$availcatids)){
		    $class="";
		    $nb++;
		    $onlykey=$key;
		    $return=$catids;
		    break;
		}
	    }
	    if(!is_null($reponse) && $reponse->value==$catids){
		$class="clicked";
		$template->parts["check".$key]="checked";
	    }
	    $template->parts[$key."class"]=$class;
	    $template->parts[$key]=implode(",",$catids);
    }
    
    if($nb==1){
	$template->parts["check".$onlykey]="checked";
	$template->parts[$onlykey."class"]="clicked";
    }else
	$return=false;
    
    $template->evaltemplate("ConfigurateurPose","html");
    return $return;
}

function display_posevitrine($reponse=null){
    global $categorie;
    global $configurateur;
    global $template;

    $availcatids=$categorie->getavailsubcatids();

    //récupère le type de pose sélectionné dans matiere/coloris/finition. Catalogue
    //$availpose=$categorie->gettypepose();

    foreach($configurateur->config('posevitrine') as $key=>$catids){
	    $class="hidden";
	    foreach($catids as $catid)
	      if(in_array($catid,$availcatids)) $class="";
	  	
	    if(!is_null($reponse) && $reponse->value==$catids){
			$class="clicked";
			$template->parts["check".$key]="checked";
	    }
	    $template->parts[$key."class"]=$class;
	    $template->parts[$key]=implode(",",$catids);
    }
    $template->evaltemplate("ConfigurateurPosevitrine","html");
}

function display_typecadre($reponse=null){
    global $categorie;
    global $configurateur;
    global $template;
    
    foreach($configurateur->config('typecadre') as $key=>$catids){
	$class="hidden";
	foreach($catids as $catid)
	  if(in_array($catid,$categorie->subcatids)) $class="";
	  
	if(!is_null($reponse) && $reponse->value==$catids){
	    $class="clicked";
	    $template->parts["check".$key]="checked";
	}
	$template->parts[$key."class"]=$class;
	$template->parts[$key]=implode(",",$catids);
    }
    $template->evaltemplate("ConfigurateurTypecadre","html");
}

function display_typeencastrable($reponse=null){
    global $categorie;
    global $configurateur;
    global $template;
    
    $availcatids=$categorie->getavailsubcatids();
    foreach($configurateur->config('typeencastrable') as $key=>$catids){
	$class="hidden";
	foreach($catids as $catid)
	    if(in_array($catid,$availcatids)) $class="";
	    
	if(!is_null($reponse) && $reponse->value==$catids){
	    $class="clicked";
	    $template->parts["check".$key]="checked";
	}
	$template->parts[$key."class"]=$class;
	$template->parts[$key]=implode(",",$catids);
    }
    $template->evaltemplate("ConfigurateurTypeencastrable","html");
}

function display_largeur($reponse=null){
    global $categorie;
    global $configurateur;
    global $template;
    
    $availcatids=$categorie->getavailsubcatids();
    foreach($configurateur->config('largeur') as $key=>$catids){
	$class="hidden";
	foreach($catids as $catid)
	    if(in_array($catid,$availcatids)) $class="";
	    
	if(!is_null($reponse) && $reponse->value==$catids){
	    $class="clicked";
	    $template->parts["check".$key]="checked";
	}
	$template->parts[$key."class"]=$class;
	$template->parts[$key]=implode(",",$catids);
    }
    $template->evaltemplate("ConfigurateurLargeur","html");
}
function display_hauteurniche($reponse=null){
    global $categorie;
    global $configurateur;
    global $template;
    
    $availcatids=$categorie->getavailsubcatids();
    foreach($configurateur->config('hauteurniche') as $key=>$catids){
	$class="hidden";
	foreach($catids as $catid)
	    if(in_array($catid,$availcatids)) $class="";
	    
	if(!is_null($reponse) && $reponse->value==$catids){
	    $class="clicked";
	    $template->parts["check".$key]="checked";
	}
	$template->parts[$key."class"]=$class;
	$template->parts[$key]=implode(",",$catids);
    }
    $template->evaltemplate("ConfigurateurHauteurNiche","html");
}

function display_finition($reponse=null){
    global $categorie;
    global $configurateur;
    global $template;
    
    $config=$configurateur->config("typeencastrable");
    $typeencastrable=$configurateur->get_response("typeencastrable");
    switch($typeencastrable->value){
    case $config['encasrep1']:
    case $config['encasrep2']:
    $suff="-1";
    break;
    
    case $config['encasrep4']:
    $suff="-2";
    break;
    
    default:
      $suff="";
    }
    
    for($i=1;$i<=4;$i++){
	$template->parts["finition".$i."class"]="hidden";
	$template->parts["finitionimg".$i]="/images/configurateur/finition".$i.$suff.".jpg";
    }
    $finitionids=$categorie->getfinition();
    if(!is_null($reponse) && !is_array($reponse->value)) $reponse->value=explode(",",$reponse->value);
    
    $nb=0;
    $lastkey=false;
    foreach($finitionids as $finitionid=>$param){
	if($param->qte>0){
	    $template->parts["finition".$param->id."class"]="";
	    $template->parts["checkfinition".$param->id]="";
	    
	    if(!is_null($reponse) && in_array($finitionid,$reponse->value)){
		$template->parts["finition".$param->id."class"]="clicked";
		$template->parts["checkfinition".$param->id]="checked";
	    }
	    $nb++;
	    $lastkey=$param->id;
	}
    }
    if(!is_null($reponse) && $reponse->value[0]===0){
	$template->parts["finition5class"]="clicked";
	$template->parts["checkfinition5"]="checked";
    }
    
    $return=false;
    if($nb==1){
	$template->parts["finition".$lastkey."class"]="clicked";
	$template->parts["checkfinition".$lastkey]="checked";
	$return=array($lastkey);
    }
    
    $template->evaltemplate("ConfigurateurFinition","html");
    return $return;
}

function display_esthetique($reponse=null){
    global $categorie;
    global $configurateur;
    global $template;

    $return=false;
    $template->parts["repesth1"]=$template->parts["repesth0"]="";
    $template->parts["checkesth0"]=$template->parts["checkesth1"]="";
    if($categorie->getesthetique()==0){
	$template->parts["repesth1"]="hidden";
	$template->parts["checkesth0"]="checked";
	$template->parts["repesth0"]="clicked";
	$return=array(0);
    }
    if(!is_null($reponse)){
	$rep=intval($reponse->value[0]);
	$template->parts["checkesth" .$rep]="checked";
	$template->parts["repesth".$rep]="clicked";
    }
    $template->evaltemplate("ConfigurateurEsthetique","html");
    return $return;
}

function display_rangement($reponse=null){
    global $categorie;
    global $configurateur;
    global $template;
    
    
    $articles=$categorie->articles("prix",false);
    for($i=1;$i<=4;$i++) $template->parts["rangement".$i."class"]="hidden";
    
    $nb=0;
    $lastkey=false;
    $moderangements=$categorie->getmoderangement($articles);
    foreach($moderangements as $moderangement=>$param){
	if($param->qte>0){
	    $template->parts["rangement".$param->id."class"]="";
	    if(!is_null($reponse) && in_array($param->id,$reponse->value)){
		$template->parts["rangement".$param->id."class"]="clicked";
		$template->parts["checkrangement".$param->id]="checked";
	    }
	    $nb++;
	    $lastkey=$param->id;
	}
    }
    if(!is_null($reponse) && $reponse->value==array(0)){
	$template->parts["rangement0class"]="clicked";
	$template->parts["checkrangement0"]="checked";
    }
    
    $return=false;
    if($nb==1){
	$template->parts["rangement".$lastkey."class"]="clicked";
	$template->parts["checkrangement".$lastkey]="checked";
	$return=array($lastkey);
    }
    
    $template->evaltemplate("ConfigurateurRangement","html");
    return $return;
}

function display_capacite($reponse=null){
    global $categorie;
    global $template;
    
    $li=array();
    $checkedlist=array();
    $return=false;
    $capacites=$categorie->getcapacitetranche(null,true);
    foreach($capacites as $capaciteid=>$param){
	$txt=sprintf($template->translate("/categorie/entre_bouteilles","F"),$param->from,$param->to);
	if($capaciteid==0) $txt=sprintf($template->translate("/categorie/inferieur_bouteilles","F"),$param->to);
	if($capaciteid==(count($categorie->capacites)-2)) $txt=sprintf($template->translate("/categorie/superieur_bouteilles","F"),$param->from);
	if($param->qte>0){
	  
	  $value=(is_array($reponse->value)?$reponse->value:array($reponse->value));
	  if(!is_null($reponse) && in_array($capaciteid,$value)){
	      $checkedlist[]=$capaciteid;
	  }
	  $li[$capaciteid]=$txt;
	}
    }
    
    foreach($li as $capaciteid=>$txt){
	$class=$checked="";
	$icon="far fa-square";
	if(in_array($capaciteid,$checkedlist) || count($li)==1){
	      $class="clicked";
	      $checked="checked";
	      $icon="far fa-check-square";
	}
	$template->parts["listecapacite"].="<li class=\"li6 $class\" onclick=\"clickli(this,'capacite".$capaciteid."',false)\">
	    <h3><span>".$txt."</span></h3>
	    <div class=\"checkbox hidden-xs\"><i class=\"".$icon."\"></i></div>
	    <input type=\"checkbox\" id=\"capacite".$capaciteid."\" name=\"capacite\" value=\"".$capaciteid."\" onclick=\"clickli(this,'capacite".$capaciteid."',false);show_next('capacite');\" $checked class=\"hidden-xs\">
	  </li>";
    }
    
    if(count($li)==1) $return=reset(array_keys($li));
    
    $template->evaltemplate("ConfigurateurCapacite","html");
    return $return;
}

function display_dimensions($reponse=null){
    global $categorie;
    global $configurateur;
    global $template;
   
    $template->parts["largclass"]=$template->parts["hautclass"]=$template->parts["profclass"]="hidden";
    if(!is_null($reponse)){
		if($reponse->value==array_fill(0,7,0)){
		    $template->parts["dim1class"]="clicked";
		    $template->parts["dim1check"]="checked";
		    $template->parts["dim2class"]="";
		    $template->parts["dim2check"]="";
		}else{
		    $config=$configurateur->config("dimensions");
		    foreach($config as $key=>$name){
			if($reponse->value[$key]!=0) $template->parts[$name]=$reponse->value[$key];
		    }
		    
		    $template->parts["dim1class"]="";
		    $template->parts["dim1check"]="";
		    $template->parts["dim2class"]="clicked";
		    $template->parts["dim2check"]="checked";
		    $template->parts["largclass"]=$template->parts["hautclass"]=$template->parts["profclass"]="";
		}
    }
    
    $template->evaltemplate("ConfigurateurDimensions","html");
}
function display_dimensionsvitrine($reponse=null){
    global $categorie;
    global $configurateur;
    global $template;
   
    $template->parts["largclass"]=$template->parts["hautclass"]=$template->parts["profclass"]="hidden";
    if(!is_null($reponse)){
		if($reponse->value==array_fill(0,7,0)){
		    $template->parts["dim1class"]="clicked";
		    $template->parts["dim1check"]="checked";
		    $template->parts["dim2class"]="";
		    $template->parts["dim2check"]="";
		}else{
		    $config=$configurateur->config("dimensionsvitrine");
		    foreach($config as $key=>$name){
			if($reponse->value[$key]!=0) $template->parts[$name]=$reponse->value[$key];
		    }
		    
		    $template->parts["dim1class"]="";
		    $template->parts["dim1check"]="";
		    $template->parts["dim2class"]="clicked";
		    $template->parts["dim2check"]="checked";
		    $template->parts["largclass"]=$template->parts["hautclass"]=$template->parts["profclass"]="";
		}
    }
    
    $template->evaltemplate("ConfigurateurDimensionsVitrine","html");
}
function display_typevitrine($reponse=null){
    global $categorie;
    global $configurateur;
    global $template;

    $availcatids=$categorie->getavailsubcatids();

    foreach($configurateur->config('typevitrine') as $key=>$catids){
	    $class="hidden";
	    foreach($catids as $catid)
	      if(in_array($catid,$availcatids)) $class="";

	    if(!is_null($reponse) && $reponse->value==$catids){
		$class="clicked";
		$template->parts["check".$key]="checked";
	    }
	    $template->parts[$key."class"]=$class;
	    $template->parts[$key]=implode(",",$catids);
    }
    $template->evaltemplate("ConfigurateurTypevitrine","html");
}

function display_usage($reponse=null){
    global $categorie;
    global $configurateur;
    global $template;

    $config=$configurateur->config("typeencastrable");
    $typeencastrable=$configurateur->get_response("typeencastrable");
    $encastrable=($typeencastrable->value==$config['encasrep4']);

    $articles=$categorie->articles("prix",false);
    $usages=array_keys($categorie->getusages($articles));
    for($i=1;$i<=4;$i++){
	$template->parts["rep".$i."class"]="";
	$template->parts["checkusage".$i]="";
	if(!in_array($i,$usages)){
	    $template->parts["rep".$i."class"]="hidden";
	}elseif(!is_null($reponse)){
	    if(intval($reponse->value)==$i){
		$template->parts["rep".$i."class"]="clicked";
		$template->parts["checkusage".$i]="checked";
	    }
	}
	$template->parts["usageimg".$i]="/images/configurateur/usage".$i.($encastrable?"-1":"").".jpg";
    }
    $template->evaltemplate("ConfigurateurUsage","html");
}

function display_temperatures($reponse=null){
    global $categorie;
    global $configurateur;
    global $template;

    $articles=$categorie->articles("prix",false);
    $nbtemperature=array_keys($categorie->getnbtemperature($articles));
    $singlerep=count($nbtemperature)==1;
    for($i=1;$i<=1000;$i++){
		if(!in_array($i,$nbtemperature)){
		  $template->parts["temprep".$i."class"]="hidden";
		}else{
		  if((!is_null($reponse) && in_array($i,$reponse->value)) || $singlerep){
		      $template->parts["temprep".$i."class"]="clicked";
		      $template->parts["checktemp".$i]="checked";
		  }
		}
		if($i==7) $i=999;
    }
    if(!is_null($reponse) && $reponse->value==0){
	$template->parts["temprep0class"]="clicked";
	$template->parts["checktemp0"]="checked";
    }
    $template->evaltemplate("ConfigurateurTemperatures","html");
    return ($singlerep?$nbtemperature[0]:false);
}

function display_typeporte($reponse=null){
    global $categorie;
    global $configurateur;
    global $template;
    
    $nb=0;
    $lastkey=false;
    $articles=$categorie->articles("prix",false);
    for($i=1;$i<=4;$i++) $template->parts["typeporte".$i."class"]="hidden";
    $typesporte=$categorie->gettypeporte($articles);
    foreach($typesporte as $typeporteid=>$param){
	if($param->qte>0){
	    $template->parts["typeporte".$param->id."class"]="";
	    if(!is_null($reponse) && $reponse->value==$typeporteid){
		$template->parts["typeporte".$param->id."class"]="clicked";
		$template->parts["checktypeporte".$param->id]="checked";
	    }
	    $nb++;
	    $lastkey=$param->id;
	}
    }
    if(!is_null($reponse) && $reponse->value==0){
	$template->parts["typeporte0class"]="clicked";
	$template->parts["checktypeporte0"]="checked";
    }
    
    $return=false;
    if($nb==1){
	$template->parts["typeporte".$lastkey."class"]="clicked";
	$template->parts["checktypeporte".$lastkey]="checked";
	$return=$lastkey;
    }
    
    $template->evaltemplate("ConfigurateurTypeporte","html");
    return $return;
}

/*function display_colorisencastrable($reponse=null){
    global $categorie;
    global $configurateur;
    global $template;

    $articles=$categorie->articles("prix",true);
    $coloris=$categorie->getcoloris($articles);
    for($i=1;$i<=3;$i++) $template->parts["coloris".$i."class"]="hidden";

    $nb=0;
    $lastkey=false;
    foreach($coloris as $colorisid=>$param){
	if($param->qte>0){
	    $template->parts["coloris".$param->id."class"]="";
	    if((!is_null($reponse) && in_array($param->id,$reponse->value))){
		$template->parts["coloris".$param->id."class"]="clicked";
		$template->parts["checkcoloris".$param->id]="checked";
	    }
	    $lastkey=$param->id;
	    $nb++;
	}
    }
    
    if(!is_null($reponse) && $reponse->value==array(0)){
	$template->parts["coloris0class"]="clicked";
	$template->parts["checkcoloris0"]="checked";
    }
    
    $return=false;
    if($nb==1){
	$template->parts["coloris".$lastkey."class"]="clicked";
	$template->parts["checkcoloris".$lastkey]="checked";
	$return=array($lastkey);
    }
    $template->evaltemplate("ConfigurateurColorisencastrable","html");
    return $return;
}
*/
function display_installtemp($reponse=null){
    global $categorie;
    global $configurateur;
    global $template;
    
    $tempinstal=$categorie->gettempinstal();
    if(!is_null($tempinstal)){
	$template->parts["mintempclass"]=$template->parts["maxtempclass"]="hidden";
	$template->parts["installtemp0class"]=$template->parts["installtemp1class"]="";
	$template->parts["checkinstalltemp0"]=$template->parts["checkinstalltemp1"]="";
	if(!is_null($reponse)){
	    if($reponse->value==array(1000,1000)){
			$template->parts["installtemp0class"]="clicked";
			$template->parts["checkinstalltemp0"]="checked";
	    }else{
			$mintemp=$reponse->value[0];
			$maxtemp=$reponse->value[1];
			$template->parts["mintempclass"]=$template->parts["maxtempclass"]="";
			$template->parts["installtemp1class"]="clicked";
			$template->parts["checkinstalltemp1"]="checked";
	    }
	}
	$template->parts["listetempmin"]="<select id=\"mintemp\" onclick=\"document.getElementById('mintemp0').checked=false;\">
	<option value=\"1000\"></option>";
	for($i=$tempinstal['min'];$i<=$tempinstal['max'];$i++)
	    $template->parts["listetempmin"].="<option value=\"".$i."\"".($mintemp==$i?" selected":"").">".$i."</option>";
	$template->parts["listetempmin"].="</select>";
	
	$template->parts["listetempmax"]="<select id=\"maxtemp\" onclick=\"document.getElementById('maxtemp0').checked=false;\">
	<option value=\"1000\"></option>";
	if($resp->value<1000) $tempinstal['min']=$resp->value;
	for($i=$tempinstal['min'];$i<=$tempinstal['max'];$i++)
	    $template->parts["listetempmax"].="<option value=\"".$i."\"".($maxtemp==$i?" selected":"").">".$i."</option>";
	$template->parts["listetempmax"].="</select>";
    }
    
    $template->evaltemplate("ConfigurateurInstalltemp","html");
}

function display_prix($reponse=null){
    global $categorie;
    global $configurateur;
    global $template;
    
    $listeprix=$categorie->getprixtranche();
    foreach($listeprix as $id=>$param){
	$txt=sprintf($template->translate("/categorie/entre_euro","F"),$template->price_format($param->from,0),$template->price_format($param->to,0));
	if($id==0) $txt=sprintf($template->translate("/categorie/inferieur_euro","F"),$template->price_format($param->to,0));
	if($id==(count($categorie->listeprix)-2)) $txt=sprintf($template->translate("/categorie/superieur_euro"),$template->price_format($param->from,0));
	
	$check=$class="";
	$icon="far fa-square";
	if(!is_null($reponse) && in_array($id,$reponse->value)){
	    $class=" clicked";
	    $check=" checked";
	    $icon="far fa-check-square";
	}
	$template->parts["listeprix"].="<li class=\"li6".$class."\" onclick=\"clickli(this,'prix".$id."',false)\">
	  <h3><span>$txt</span></h3>
	  <div class=\"checkbox hidden-xs\"><i class=\"".$icon."\"></i></div>
	  <input type=\"checkbox\" name=\"prix\" id=\"prix".$id."\" value=\"".$param->id."\" onclick=\"show_next('prix')\"".$check." class=\"hidden-xs\">
	</li>";
    }
    if(!is_null($reponse) && $reponse->value==array(1000)){
	$template->parts["prix1000class"]="clicked";
	$template->parts["checkprix1000"]="checked";
    }
    $template->evaltemplate("ConfigurateurPrix","html");
}

function display_marque($reponse=null,$affine=false){
    global $DB_site;
    global $configurateur;
    global $categorie;
    global $template;

    if($affine){
		$template->parts["classaffine"]="affine";
		$template->parts["btnclass"]="open";
		$template->parts["btntxt"]="Voir les critères";
		$template->parts["li"]=$li="li6";
    }else{
		$template->parts["classaffine"]="";
		$template->parts["btnclass"]="close";
		$template->parts["btntxt"]="Masquer les critères";
		$marques=$configurateur->config("marques");
		$template->parts["li"]=$li="li5";
    }
    
    $template->parts["listemarques"]="";
    $subcatmarques=$categorie->getmarques();
    foreach($subcatmarques as $p=>$param){
		if(!isset($marques) || in_array($param->id,$marques)){
			$subcatmarque=new marque($DB_site,$param->id);
			$class=$check="";
			$icon="far fa-square";
			if(isset($reponse) && in_array($param->id,$reponse->value)){
				$class=" clicked";
				$check=" checked";
				$icon="far fa-check-square";
			}
			$template->parts["listemarques"].="<li class=\"".$li." marque".$class."\" onclick=\"clickli(this,'marque".$param->id."',false)\">
			  <h3><span>".$subcatmarque->libelle."</span></h3>
			  <img src=\"".$subcatmarque->logo(130,40)."\" alt=\"\" class=\"hidden-xs\">
			  <div class=\"checkbox hidden-xs\"><i class=\"".$icon."\"></i></div>
			  <input type=\"checkbox\" id=\"marque".$param->id."\" name=\"marque\" value=\"".$param->id."\" onclick=\"show_next('marque')\"".$check." class=\"hidden-xs\">
			</li>";
		}
    }
    if(isset($reponse) && $reponse->value[0]==0){
		$template->parts["marque0class"]="clicked";
		$template->parts["checkmarque0"]="checked";
    }
    $template->evaltemplate("ConfigurateurMarque","html");
}

function display_budget($reponse=null){
    global $DB_site;
    global $configurateur;
    global $categorie;
    global $template;

    $listebudget=$configurateur->config("budget");

    foreach($listebudget as $key=>$pmin){
		$pmax=($key==count($listebudget)?0:$listebudget[$key+1]);
		$txt=sprintf($template->translate("/categorie/entre_euro","F"),$template->price_format($pmin,0),$template->price_format($pmax,0));
		if($pmin==$listebudget[0]) $txt=sprintf($template->translate("/categorie/inferieur_euro","F"),$template->price_format($pmax,0));
		if($pmax==0) $txt=sprintf($template->translate("/categorie/superieur_euro"),$template->price_format($pmin,0));
		

		$values=array();
		$check=$class="";
		$icon="far fa-square";
		$listeprix=$categorie->getprixtranche();
		
		foreach($listeprix as $id=>$param){
		    if(!isset($listebudget[$key+1])) $listebudget[$key+1]=100000;
		    if($param->from>=$pmin && $param->from<$listebudget[$key+1]){
			$values[]=$param->id;
			if(!is_null($reponse) && in_array($param->id,$reponse->value)){
			    $class=" clicked";
			    $check=" checked";
			    $icon="far fa-check-square";
			}
		    }
		}

		$template->parts["listeprix"] .= ""
		. "<li class=\"li6".$class."\" onclick=\"clickli(this,'prix".$key."',false)\">"
			. "<h3><span>".$txt."</span></h3>"
			. "<div class=\"checkbox hidden-xs\"><i class=\"".$icon."\"></i></div>"
			. "<input type=\"checkbox\" name=\"prix\" id=\"prix".$key."\" value=\"".implode(",",$values)."\" onclick=\"show_next('prix')\"".$check." class=\"hidden-xs\">"
		. "</li>";
		$i++;
    }
    
    if(!is_null($reponse) && $reponse->value==array(1000)){
	$template->parts["prix1000class"]="clicked";
	$template->parts["checkprix1000"]="checked";
    }
    
    $template->evaltemplate("ConfigurateurBudget","html");
}

function display_affineencastrable($nbstart=null,$reponses=null){ 
    display_affine($nbstart=null,"encastrable",$reponses);
}

function display_affine($nbstart=null,$affinetype="",$reponses=null,$open=null,$affichage_critere=null){
    global $DB_site;
    global $configurateur;
    global $categorie;
    global $template;


    $afreponses=array();
    if(!is_null($reponses)){
		foreach($reponses as $reponse){
		    if($configurateur->is_affine($reponse->type)) $afreponses[$reponse->type]=$reponse;
		}
    }
    
    $articles=$categorie->articles("prix",false);
    
    $hide=array("hideafcoloris","hideafprix","hideafclasse","hideafserrure","hideafmarque","hideafinstalltemp","hideafsensporte","hideaforigine","hideafcolorisencastrable");
    foreach($hide as $part) $template->parts[$part]="hidden";
    
    if($configurateur->is_affine("classe")){
	$affopt=0;
	$classes=$categorie->getenergylabel();
	foreach($configurateur->config("classe") as $key => $classerep){
	    $template->parts["classerep".$key."class"]="hidden";
	    foreach($classerep as $classe){
		if(in_array($classe,$classes)){
		  $affopt++;
		  $template->parts["classerep".$key."class"]="";
		  $template->parts["classerep".$key]=$key;
		  if(isset($afreponses['classe']) && $afreponses['classe']->value[0]==$key){
		      $template->parts["classerep".$key."class"]="clicked";
		      $template->parts["checkclasserep".$key]="checked";
		  }
		}
	    }
	}
	if(isset($afreponses['classe']) && $afreponses['classe']->value[0]==0){
	    $template->parts["classerep0class"]="clicked";
	    $template->parts["checkclasserep0"]="checked";
	}
	
	if($open=="classe"){
	    $template->parts["hideafclasse"]="clicked";
	    $template->parts["chkafclasse"]="checked";
	    $template->parts["afclasseclasse"]="";
	    $template->parts["ulclasseclasse"]="affine";
	}elseif(isset($afreponses['classe'])){
	    $template->parts["hideafclasse"]="";
	    $template->parts["chkafclasse"]="";
	    $template->parts["afclasseclasse"]="";
	    $template->parts["ulclasseclasse"]="affine hidden";
	}else{
	    $template->parts["hideafclasse"]=($affopt>0?"":"hidden");
	    $template->parts["chkafclasse"]="";
	    $template->parts["afclasseclasse"]="hidden";
	    $template->parts["ulclasseclasse"]="hidden";
	}
	
	$template->evaltemplate("ConfigurateurClasse","configurateurclasse");
    }
    

    if($configurateur->is_affine("prix") && $configurateur->get_response('limite')->value!=2){
	$listeprix=$categorie->getprixtranche();
	foreach($listeprix as $id=>$param){
	    $txt=sprintf($template->translate("/categorie/entre_euro","F"),$template->price_format($param->from,0),$template->price_format($param->to,0));
	    if($id==0) $txt=sprintf($template->translate("/categorie/inferieur_euro","F"),$template->price_format($param->to,0));
	    if($id==(count($categorie->listeprix)-2)) $txt=sprintf($template->translate("/categorie/superieur_euro"),$template->price_format($param->from,0));
	    
	    $check=$class="";
	    $icon="far fa-square";
	    if(isset($afreponses['prix']) && in_array($id,$afreponses['prix']->value)){
		$class=" clicked";
		$check=" checked";
		$icon="far fa-check-square";
	    }
	    $template->parts["listeprix"].="<li class=\"li6".$class."\"  onclick=\"clicklichoix('prix".$id."');show_next('prix')\">
	    <input type=\"checkbox\" name=\"prix\" id=\"prix".$id."\" value=\"".$param->id."\" onclick=\"clicklichoix('prix".$id."');show_next('prix')\"".$check." style=\"display: inline\">
	      <span style=\"vertical-align: baseline\">".utf8_encode($txt)."</span>
	      <div class=\"checkbox hidden-xs\"><i class=\"".$icon."\"></i></div>
	    </li>";
	}
	if(isset($afreponses['prix']) && $afreponses['prix']->value==array(1000)){
	    $template->parts["prix1000class"]="clicked";
	    $template->parts["checkprix1000"]="checked";
	}
	
	if($open=="prix"){
	    $template->parts["hideafprix"]="clicked";
	    $template->parts["chkafprix"]="checked";
	    $template->parts["afprixclasse"]="affine";
	    $template->parts["ulprixclasse"]="affine";
	}elseif(isset($afreponses['prix'])){
	    $template->parts["hideafprix"]="";
	    $template->parts["chkafprix"]="";
	    $template->parts["afprixclasse"]="affine";
	    $template->parts["ulprixclasse"]="affine hidden";
	}else{
	    $template->parts["hideafprix"]=(count($listeprix)>1?"":"hidden");
	    $template->parts["chkafprix"]="";
	    $template->parts["afprixclasse"]="hidden";
	    $template->parts["ulprixclasse"]="hidden";
	}
	
	$template->evaltemplate("ConfigurateurPrix","configurateurprix");
    }
    
    
    if($configurateur->is_affine("coloris")){
	$coloris=$categorie->getcoloris($articles);
	for($i=1;$i<=5;$i++) $template->parts["coloris".$i."class"]="hidden";
	foreach($coloris as $colorisid=>$param){
	  if($param->qte>0){
	      $template->parts["coloris".$param->id."class"]="";
	      if(isset($afreponses['coloris']) && in_array($colorisid,$afreponses['coloris']->value)){
		  $template->parts["coloris".$param->id."class"]="clicked";
		  $template->parts["checkcoloris".$param->id]="checked";
	      }
	  }
	}
	if(isset($afreponses['coloris']) && $afreponses['coloris']->value[0]==0){
	    $template->parts["coloris0class"]="clicked";
	    $template->parts["checkcoloris0"]="checked";
	}

	/// si c'est une cave pose libre ou ilot, on affiche l'affinage coloris
	$configpose=$configurateur->config("pose");
    $typepose=$configurateur->get_response("pose");
	$configposevitrine=$configurateur->config("posevitrine");
    $typeposevitrine=$configurateur->get_response("posevitrine");

	if($open=="coloris"){
	    $template->parts["hideafcoloris"]="clicked";
	    $template->parts["chkafcoloris"]="checked";
	    $template->parts["afcolorisclasse"]="";
	    $template->parts["ulcolorisclasse"]="affine";
	}elseif(isset($afreponses['coloris'])){
	    $template->parts["hideafcoloris"]="";
	    $template->parts["chkafcoloris"]="";
	    $template->parts["afcolorisclasse"]="";
	    $template->parts["ulcolorisclasse"]="affine hidden";
	}else{
	    $template->parts["hideafcoloris"]=(count($coloris)>1&&($typepose->value==$configpose['poserep1']||$typeposevitrine->value==$configposevitrine['posevitrine1']||$typeposevitrine->value==$configposevitrine['posevitrine3'])?"":"hidden");
	    $template->parts["chkafcoloris"]="";
	    $template->parts["afcolorisclasse"]="hidden";
	    $template->parts["ulcolorisclasse"]="hidden";
	}
	$template->evaltemplate("ConfigurateurColoris","configurateurcoloris");
    }

    if($configurateur->is_affine("marque")){
	$subcatmarques=$categorie->getmarques();
	foreach($subcatmarques as $p=>$param){
	    if(!isset($marques) || in_array($param->id,$marques)){
		$subcatmarque=new marque($DB_site,$param->id);
		
		$class=$check="";
		if(isset($afreponses['marque']) && in_array($param->id,$afreponses['marque']->value)){
		    $class=" clicked";
		    $check=" checked";
		}
		$template->parts["listemarques"].="<li class=\"li6 marque".$class."\" onclick=\"clicklichoix('marque".$param->id."');show_next('marque')\">
		  <div class=\"checkbox\"></div>
		  <input type=\"checkbox\" id=\"marque".$param->id."\" name=\"marque\" value=\"".$param->id."\" onclick=\"clicklichoix('marque".$param->id."');show_next('marque')\"".$check." style=\"display: inline\">
	      <span style=\"vertical-align: baseline\">".utf8_encode($subcatmarque->libelle)."</span>
		</li>";
	    }
	}
	if(isset($reponse) && $reponse->value[0]==0){
	    $template->parts["marque0class"]="clicked";
	    $template->parts["checkmarque0"]="checked";
	}
    
	if($open=="marque"){
	    $template->parts["hideafmarque"]="clicked";
	    $template->parts["chkafmarque"]="checked";
	    $template->parts["afmarqueclasse"]="";
	    $template->parts["ulmarqueclasse"]="affine";
	}elseif(isset($afreponses['marque'])){
	    $template->parts["hideafmarque"]="";
	    $template->parts["chkafmarque"]="";
	    $template->parts["afmarqueclasse"]="";
	    $template->parts["ulmarqueclasse"]="affine hidden";
	}else{
	    $template->parts["hideafmarque"]=(count($subcatmarques)>1?"":"hidden");
	    $template->parts["chkafmarque"]="";
	    $template->parts["afmarqueclasse"]="hidden";
	    $template->parts["ulmarqueclasse"]="hidden";
	}

	$template->parts["classaffine"]="affine";
	$template->evaltemplate("ConfigurateurMarque","configurateurmarque");
    }
    
    
    if($configurateur->is_affine("origine")){
	$origines=$categorie->getorigine($articles);
	$config_origine=$configurateur->config('origine');
	for($i=0;$i<=1;$i++) $template->parts["origine".$i."class"]="hidden";
	foreach($origines as $origineid=>$param){
	  if(isset($config_origine[$origineid]) && $param->qte>0){
	    $template->parts["origine".$origineid."class"]="";
	    $template->parts["origine".$origineid]=implode(",",$config_origine[$origineid]);
	    
	    if(isset($afreponses['origine']) && in_array($origineid,$afreponses['origine']->value)){
		$template->parts["origine".$origineid."class"]="clicked";
		$template->parts["checkorigine".$origineid]="checked";
	    }
	  }
	}
	if(isset($afreponses['origine']) && $afreponses['origine']->value[0]==1000){
	    $template->parts["origine1000class"]="clicked";
	    $template->parts["checkorigine1000"]="checked";
	}

	if($open=="origine"){
	    $template->parts["hideaforigine"]="clicked";
	    $template->parts["chkaforigine"]="checked";
	    $template->parts["aforigineclasse"]="";
	    $template->parts["ulorigineclasse"]="affine";
	}elseif(isset($afreponses['origine'])){
	    $template->parts["hideaforigine"]="";
	    $template->parts["chkaforigine"]="";
	    $template->parts["aforigineclasse"]="";
	    $template->parts["ulorigineclasse"]="affine hidden";
	}else{
	    $template->parts["hideaforigine"]="";
	    $template->parts["chkaforigine"]="";
	    $template->parts["aforigineclasse"]="hidden";
	    $template->parts["ulorigineclasse"]="hidden";
	}
	
	if(count($origines)>0){
	    $template->evaltemplate("ConfigurateurOrigine","configurateurorigine");
	}else
	    $template->parts["hideaforigine"]="hidden";
    }
    
    
     if($configurateur->is_affine("colorisencastrable")){
	$coloris=$categorie->getcolorisencastrable($articles);
	for($i=1;$i<=5;$i++) $template->parts["colorisencastrable".$i."class"]="hidden";
	foreach($coloris as $colorisid=>$param){
	  if($param->qte>0){
	      $template->parts["colorisencastrable".$param->id."class"]="";
	      if(isset($afreponses['colorisencastrable']) && in_array($colorisid,$afreponses['colorisencastrable']->value)){
		  $template->parts["colorisencastrable".$param->id."class"]="clicked";
		  $template->parts["checkcolorisencastrable".$param->id]="checked";
	      }
	  }
	}
	if(isset($afreponses['colorisencastrable']) && $afreponses['colorisencastrable']->value[0]==0){
	    $template->parts["colorisencastrable0class"]="clicked";
	    $template->parts["checkcolorisencastrable0"]="checked";
	}

	/// si c'est une cave encastrable, on affiche l'affinage coloris_porte
    $configpose=$configurateur->config("pose");
    $typepose=$configurateur->get_response("pose");
	$configposevitrine=$configurateur->config("posevitrine");
    $typeposevitrine=$configurateur->get_response("posevitrine");
	
	if($open=="colorisencastrable"){
	    $template->parts["hideafcolorisencastrable"]="clicked";
	    $template->parts["chkafcolorisencastrable"]="checked";
	    $template->parts["afcolorisencastrableclasse"]="";
	    $template->parts["ulcolorisencastrableclasse"]="affine";
	}elseif(isset($afreponses['colorisencastrable'])){
	    $template->parts["hideafcolorisencastrable"]="";
	    $template->parts["chkafcolorisencastrable"]="";
	    $template->parts["afcolorisencastrableclasse"]="";
	    $template->parts["ulcolorisencastrableclasse"]="affine hidden";
	}else{
	    $template->parts["hideafcolorisencastrable"]=(count($coloris)>1&&($typepose->value==$configpose['poserep2']||$typeposevitrine->value==$configposevitrine['posevitrine2'])?"":"hidden");
	    $template->parts["chkafcolorisencastrable"]="";
	    $template->parts["afcolorisencastrableclasse"]="hidden";
	    $template->parts["ulcolorisencastrableclasse"]="hidden";
	}
	$template->evaltemplate("ConfigurateurColorisencastrable","configurateurcolorisencastrable");
    }


    if($configurateur->is_affine("sensporte")){
	$sensporte=$categorie->getsensporte(true);

	foreach($configurateur->config("sensporte") as $key=>$sensporteids){
	    $template->parts[$key."class"]="hidden";
	    foreach($sensporte as $sensporteid=>$param){
		if(in_array($sensporteid,$sensporteids)){
		    $template->parts[$key."class"]="";
		    $template->parts[$key]=implode(",",$sensporteids);
		    
		    if(isset($afreponses['sensporte']) && in_array($sensporteid,$afreponses['sensporte']->value)){
		    	//// permet de n'afficher que le bon critère dans "vos critères", quand les valeurs comportent plusieurs valeurs (0,1) par exemple
		    	if($affichage_critere != null) { 
					$key = "sensporte".$affichage_critere;
		    	}
		    	////
			$template->parts[$key."class"]="clicked";
			$template->parts["check".$key]="checked";
		    }
		    break;
		}
	    }
	}
	if(isset($afreponses['sensporte']) && $afreponses['sensporte']->value[0]==1000){
	    $template->parts["sensporte1000class"]="clicked";
	    $template->parts["checksensporte1000"]="checked";
	}
	
	if($open=="sensporte"){
	    $template->parts["hideafsensporte"]="clicked";
	    $template->parts["chkafsensporte"]="checked";
	    $template->parts["afsensporteclasse"]="";
	    $template->parts["ulsensporteclasse"]="affine";
	}elseif(isset($afreponses['sensporte'])){
	    $template->parts["hideafsensporte"]="";
	    $template->parts["chkafsensporte"]="";
	    $template->parts["afsensporteclasse"]="";
	    $template->parts["ulsensporteclasse"]="affine hidden";
	}else{
	    $template->parts["hideafsensporte"]=(count($sensporte)>1?"":"hidden");
	    $template->parts["chkafsensporte"]="";
	    $template->parts["afsensporteclasse"]="hidden";
	    $template->parts["ulsensporteclasse"]="hidden";
	}
	
	$template->evaltemplate("ConfigurateurSensporte","configurateursensporte");
    }
    
    
    if($configurateur->is_affine("serrure")){
	$notempty=false;
	$serrure=$categorie->getserrure();
	$serrurekeys=array_keys($serrure);
	foreach($configurateur->config("serrure") as $key=>$serrureids){
	    //$template->parts[$key."class"]="hidden";
	    foreach($serrurekeys as $serrurekey){
		if(in_array($serrurekey,$serrureids)){
		    $template->parts[$key."class"]="";
		    $template->parts[$key]=implode(",",$serrureids);
		    if($serrurekey>0 && $serrure[$serrurekey]->qte>1) $notempty=true;

		    break;
		}
	    }
	    if(isset($afreponses['serrure']) && $afreponses['serrure']->value==$serrureids){
		$template->parts[$key."class"]="clicked";
		$template->parts["check".$key]="checked";
	    }
	}

	if($open=="serrure"){
	    $template->parts["hideafserrure"]="clicked";
	    $template->parts["chkafserrure"]="checked";
	    $template->parts["afserrureclasse"]="";
	    $template->parts["ulserrureclasse"]="affine";
	}elseif(isset($afreponses['serrure'])){
	    $template->parts["hideafserrure"]="";
	    $template->parts["chkafserrure"]="";
	    $template->parts["afserrureclasse"]="";
	    $template->parts["ulserrureclasse"]="affine hidden";
	}else{
	    $template->parts["hideafserrure"]=($notempty?"":"hidden");
	    $template->parts["chkafserrure"]="";
	    $template->parts["afserrureclasse"]="hidden";
	    $template->parts["ulserrureclasse"]="hidden";
	}
	
	$template->evaltemplate("ConfigurateurSerrure","configurateurserrure");
    }
    
    
    if($configurateur->is_affine("installtemp")){
	$template->parts["classaffine"]=" affine";

	if($open=="installtemp"){
	    $template->parts["hideafinstalltemp"]="clicked";
	    $template->parts["chkafinstalltemp"]="checked";
	    $template->parts["afinstalltempclasse"]="";
	    $template->parts["ulinstalltempclasse"]="affine";
	}elseif(isset($afreponses['installtemp'])){
	    $template->parts["hideafinstalltemp"]="";
	    $template->parts["chkafinstalltemp"]="";
	    $template->parts["afinstalltempclasse"]="";
	    $template->parts["ulinstalltempclasse"]="affine hidden";
	}else{
	    $template->parts["hideafinstalltemp"]="";
	    $template->parts["chkafinstalltemp"]="";
	    $template->parts["afinstalltempclasse"]="hidden";
	    $template->parts["ulinstalltempclasse"]="hidden";
	}
	
	display_installtemp();
	$template->parts["configurateurinstalltemp"]=$template->parts["html"];
    }
    
    $template->parts["html"]="";
    $template->evaltemplate("ConfigurateurAffine","html");

}

function display_affine_new(){
	global $DB_site;
    global $configurateur;
    global $categorie;
    global $template;
}


?>