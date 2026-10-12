<?php

include("global.php");
include("class/configurateur.class.php");

$config['typeproduit']=array(
  "typesrep1" => array(1,2,3,7,265),
  "typesrep2" => array(283),
  "typesrep3" => array(),
  "typesrep4" => array(270)
);
$config['pose']=array(
  "poserep1" => array(1,2,3,265),
  "poserep2" => array(50,66,256,51),
  "poserep3" => array(),
  "poserep4" => array(69)
);
$config['dimensions']=array("largeurmin","largeurmax","hauteurmin","hauteurmax","profondeurmin","profondeurmax");
$config['temperatures']=array(3,4);
$config['classe']=array(
  1 => array("A","A+","A++","A+++"),
  2 => array("A","A+","A++","A+++","B")
);
$config['origine']=array(0 => array(0), 1=> array(0,1));
$config['sensporte']=array(
  "sensporte0" => array(0,2),
  "sensporte1" => array(1,2)
);
$config['serrure']=array(
  "serrure0" => array(0,1,2),
  "serrure1" => array(1,2)
);

$configurateur=new configurateur($DB_site);
if(!$configurateur->verif_type($_GET['rep'])) exit;

$categorie=new categorie($DB_site);
$categorie->setparam("site_code",$template->site_code());

if($_GET['rep']!="start"){
    $loaded=$configurateur->loadcookie();
    if(!$loaded){
		return_data("","","Cookie error !");
		exit;
    }
    
    $reponses=$configurateur->reponses();
    
    // vérification retour en arrière
    foreach($reponses as $reponse){
		if($reponse->type==$_GET['rep']){
		  $configurateur->del_reponses($reponse->nb+1);
		  $reponses=$configurateur->reponses();
		  break;
		}
    }
    $repnb=count($reponses);
}

// enregistrement réponse + affichage suite
switch($_GET['rep']){
    case "start":
    $configurateur->userid=$user_info['userid'];
    $configurateur->session=$s;
    $configurateur->insert();
    
    $template->parts["repnb"]=1;
    $template->evaltemplate("ConfigurateurUtilisation","html");
    return_data(stripslashes($template->parts["html"]),$repnb+1);
    break;
    
    case "utilisation":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="utilisation";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue($_GET['value']);
    $resp->save();
    
    loadreponses();
    $articles=articlecount();
    
    foreach($config['typeproduit'] as $key=>$catids){
	$subcatids=$categorie->getsubcatids($catids);
	if($categorie->articlecount($subcatids)>0)
	  $template->parts[$key]=implode(",",$catids);
	else
	  $template->parts[$key."class"]="hidden";
    }
    
    $template->parts["repnb"]=$resp->nb+1;
    $template->evaltemplate("ConfigurateurTypeproduit","html");
    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles);
    break;
    
    case "typeproduit":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="typeproduit";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue(explode(",",$_GET['value']));
    $resp->save();
    
    loadreponses();
    $articles=articlecount();
    
    
    $template->parts["repnb"]=$resp->nb+1;
    if($resp->value==$config['typeproduit']['typesrep1']){
	foreach($config['pose'] as $key=>$catids){
	    $class="hidden";
	    foreach($catids as $catid)
	      if(in_array($catid,$categorie->subcatids)) $class="";
	    $template->parts[$key."class"]=$class;
	    $template->parts[$key]=implode(",",$catids);
	}
	$template->evaltemplate("ConfigurateurPose","html");
    }else
	display_dimensions();
	
    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles);
    break;
    
    case "pose":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="pose";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue(explode(",",$_GET['value']));
    $resp->save();
    
    loadreponses();
    $articles=articlecount();
    
    $template->parts["repnb"]=$resp->nb+1;
    display_dimensions();
    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles);
    break;
    
    case "dimensions":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="dimensions";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue(explode(",",$_GET['value']));
    $resp->save();
    
    loadreponses();
    $articles=articlecount();
    
    $usages=array_keys($categorie->getusages($articles['total']));
    for($i=1;$i<=4;$i++){
	if(!in_array($i,$usages)) $template->parts["rep".$i."class"]="hidden";
    }
    
    $template->parts["repnb"]=$resp->nb+1;
    $template->evaltemplate("ConfigurateurUsage","html");
    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles);
    break;
    
    case "usage":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="usage";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue($_GET['value']);
    $resp->save();
    
    loadreponses();
    $articles=articlecount();
    
    $template->parts["repnb"]=$resp->nb+1;
    if(in_array($resp->value,$config["temperatures"])){
	$nbtemperature=array_keys($categorie->getnbtemperature());
	for($i=1;$i<=1000;$i++){
	    if(!in_array($i,$nbtemperature)) $template->parts["temprep".$i."class"]="hidden";
	    if($i==4) $i=999;
	}
	$template->evaltemplate("ConfigurateurTemperatures","html");	    
    }else
	display_capacite();
	
    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles);
    break;
    
    case "temperatures":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="temperatures";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue($_GET['value']);
    $resp->save();
    
    loadreponses();
    $articles=articlecount();
    
    $template->parts["repnb"]=$resp->nb+1;
    display_capacite();
    return_data(stripslashes($template->parts["html"]),$resp->nb+1,$categorie->getsqlwhere(),$articles);
    break;
    
    case "capacite":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="capacite";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue($_GET['value']);
    $resp->save();
    
    loadreponses();
    $articles=articlecount();
    
    $template->parts["listefinitions"]="";
    $finitionids=$categorie->getfinition();
    foreach($finitionids as $finitionid=>$param){
	$template->parts["listefinitions"].="<li><input type=\"radio\" id=\"finition".$finitionid."\" name=\"finition\" onclick=\"next('finition','".$finitionid."')\"><label for=\"finition".$finitionid."\"> ".$param->libelle."</label>".$param->qte."</li>";
    }
    
    $template->parts["repnb"]=$resp->nb+1;
    $template->evaltemplate("ConfigurateurFinition","html");
    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles);
    break;
    
    case "finition":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="finition";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue($_GET['value']);
    $resp->save();
    
    loadreponses();
    $articles=articlecount();
    
    if($categorie->getesthetique()==0) $template->parts["repesth1"]="hidden";
    
    $template->parts["repnb"]=$resp->nb+1;
    $template->evaltemplate("ConfigurateurEsthetique","html");
    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles);
    break;
    
    case "esthetique":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="esthetique";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue($_GET['value']);
    $resp->save();
    
    loadreponses();
    $articles=articlecount();
    
    $tempinstal=$categorie->gettempinstal();
    if(!is_null($tempinstal)){
	$template->parts["listetempmin"]="<li><select onchange=\"next('mintemp',this.value);document.getElementById('mintemp0').checked=false;\">
	<option value=\"1000\"></option>";
	for($i=$tempinstal['min'];$i<=$tempinstal['max'];$i++)
	    $template->parts["listetempmin"].="<option value=\"".$i."\">".$i."°C</option>";
	$template->parts["listetempmin"].="</select></li>";
    }
    
    $template->parts["repnb"]=$resp->nb+1;
    $template->evaltemplate("ConfigurateurMintemp","html");
    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles);
    break; 
    
    case "mintemp":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="mintemp";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue($_GET['value']);
    $resp->save();
    
    loadreponses();
    $articles=articlecount();
    
    $tempinstal=$categorie->gettempinstal();
    if(!is_null($tempinstal)){
	$template->parts["listetempmax"]="<li><select onchange=\"next('maxtemp',this.value);document.getElementById('maxtemp0').checked=false;\">
	<option value=\"1000\"></option>";
	if($resp->value<1000) $tempinstal['min']=$resp->value;
	for($i=$tempinstal['min'];$i<=$tempinstal['max'];$i++)
	    $template->parts["listetempmax"].="<option value=\"".$i."\">".$i."°C</option>";
	$template->parts["listetempmax"].="</select></li>";
    }
    
    $template->parts["repnb"]=$resp->nb+1;
    $template->evaltemplate("ConfigurateurMaxtemp","html");
    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles);    
    break;
    
    case "maxtemp":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="maxtemp";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue($_GET['value']);
    $resp->save();
    
    loadreponses();
    $articles=articlecount();
    
    $template->parts["listetypeporte"]="";
    $typesporte=$categorie->gettypeporte($articles['total']);
    foreach($typesporte as $typeporteid=>$param){
	$template->parts["listetypeporte"].="<li><input type=\"radio\" id=\"typeporte".$typeporteid."\" name=\"typeporte\" onclick=\"next('typeporte','".$typeporteid."')\"><label for=\"typeporte".$typeporteid."\"> ".$param->libelle."</label>".$param->qte."</li>";
    }
    
    $template->parts["repnb"]=$resp->nb+1;
    $template->evaltemplate("ConfigurateurTypeporte","html");
    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles);    
    break;
    
    case "typeporte":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="typeporte";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue($_GET['value']);
    $resp->save();
    
    loadreponses();
    $articles=articlecount();
    
    $template->parts["listemoderangement"]="";
    $moderangements=$categorie->getmoderangement($articles['total']);
    foreach($moderangements as $moderangement=>$param){
	$template->parts["listerangement"].="<li><input type=\"checkbox\" name=\"rangement\" id=\"rangement".$moderangement."\" value=\"".$moderangement."\" onclick=\"next('rangement',return_checkboxes('rangement'))\"><label for=\"rangement".$moderangement."\"> ".$param->libelle."</label>".$param->qte."</li>";
    }
    
    $template->parts["repnb"]=$resp->nb+1;
    $template->evaltemplate("ConfigurateurRangement","html");
    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles);     
    break;
    
    case "rangement":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="rangement";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue(explode(",",$_GET['value']));
    $resp->save();
    
    loadreponses();
    $articles=articlecount();
    
    display_affine($resp->nb+1);
    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles);    
    break;
    
    case "classe":
    case "prix":
    case "coloris":
    case "marque":
    case "serrure":
    case "origine":
    case "sensporte":
    
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type=$_GET['rep'];
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue(explode(",",$_GET['value']));
    $resp->save();
    
    loadreponses();
    $articles=articlecount();
    
    return_data("",0,"",$articles);
    break;
}

function display_capacite(){
    global $categorie;
    global $template;
    
    $capacites=$categorie->getcapacite();
    foreach($capacites as $capaciteid=>$param){
	$txt=sprintf($template->translate("/categorie/entre_bouteilles","F"),$param->from,$param->to);
	if($capaciteid==0) $txt=sprintf($template->translate("/categorie/inferieur_bouteilles","F"),$param->to);
	if($capaciteid==(count($categorie->capacites)-2)) $txt=sprintf($template->translate("/categorie/superieur_bouteilles","F"),$param->from);
	if($param->qte>0) $template->parts["listecapacite"].="<li><input type=\"checkbox\" id=\"capacite".$capaciteid."\" name=\"capacite\" value=\"".$capaciteid."\" onclick=\"next('capacite',return_checkboxes('capacite'))\"><label for=\"capacite".$capaciteid."\"> ".$txt."</label> ".$param->qte."</li>";
    }

    $template->evaltemplate("ConfigurateurCapacite","html");
}

function display_dimensions(){
    global $categorie;
    global $template;
    global $config;
    
    foreach($config["dimensions"] as $key=>$var){
	if($resp->value[$key]>0) $categorie->setparam($var,$resp->value[$key]);
    }
    
    $template->evaltemplate("ConfigurateurDimensions","html");
}

function display_affine($nbstart){
    global $DB_site;
    global $configurateur;
    global $categorie;
    global $template;
    global $config;
    
    $listerep=array("classe","prix","coloris","marque","serrure","origine","sensporte");
    foreach($listerep as $rep){
	$template->parts["repnb".$rep]=$nbstart;
	$nbstart++;
    }
    
    $articles=$categorie->articles("prix",false);
    
    $classes=$categorie->getenergylabel();
    foreach($config["classe"] as $key => $classerep){
	$template->parts["classerep".$key."class"]="hidden";
	foreach($classerep as $classe){
	    if(in_array($classe,$classes)){
	      $template->parts["classerep".$key."class"]="";
	      $template->parts["classerep".$key]=$key;
	    }
	}
    }
    
    $listeprix=$categorie->getprix();
    foreach($listeprix as $id=>$param){
      $txt=sprintf($template->translate("/categorie/entre_euro","F"),$template->price_format($param->from,0),$template->price_format($param->to,0));
      if($id==0) $txt=sprintf($template->translate("/categorie/inferieur_euro","F"),$template->price_format($param->to,0));
      if($id==(count($categorie->listeprix)-2)) $txt=sprintf($template->translate("/categorie/superieur_euro"),$template->price_format($param->from,0));
      $template->parts["listeprix"].="<li><input type=\"checkbox\" name=\"prix\" id=\"prix".$id."\" value=\"".$param->id."\" onclick=\"next('prix',return_checkboxes('prix'))\"><label for=\"prix".$id."\"> ".$txt."</label></li>";
     }
     
    $coloris=$categorie->getcoloris($articles);
    foreach($coloris as $colorisid=>$param){
      $template->parts["listecoloris"].="<li><input type=\"checkbox\" id=\"coloris".$colorisid."\" name=\"coloris\" value=\"".$colorisid."\" onclick=\"next('coloris',return_checkboxes('coloris'))\"><label for=\"coloris".$colorisid."\"> ".$param->libelle."</label></li>";
    }
    
    $subcatmarques=$categorie->getmarques();
    foreach($subcatmarques as $p=>$param){
      $subcatmarque=new marque($DB_site,$param->id);
      $template->parts["listemarques"].="<li><input type=\"checkbox\" id=\"marque".$param->id."\" name=\"marque\" value=\"".$param->id."\" onclick=\"next('marque',return_checkboxes('marque'))\"><label for=\"marque".$param->id."\"> ".$subcatmarque->libelle."</label></li>";
    }
    
    $origines=$categorie->getorigine();
    foreach($origines as $origineid=>$param){
      if(isset($config['origine'][$origineid])) $template->parts["listeorigines"].="<li><input type=\"radio\" name=\"origine\" id=\"origine".$origineid."\" onclick=\"next('origine','".implode(",",$config['origine'][$origineid])."')\"><label for=\"origine".$origineid."\"> ".$param->libelle."</label></li>";
    }
    
    $sensporte=$categorie->getsensporte();
    foreach($config["sensporte"] as $key=>$sensporteids){
	$template->parts[$key."class"]="hidden";
	foreach($sensporte as $sensporteid=>$param){
	    if(in_array($sensporteid,$sensporteids)){
		$template->parts[$key."class"]="";
		$template->parts[$key]=implode(",",$sensporteids);
		break;
	    }
	}
    }
    
    $serrure=$categorie->getserrure();
    $serrurekeys=array_keys($serrure);
    foreach($config["serrure"] as $key=>$serrureids){
	$template->parts[$key."class"]="hidden";
	foreach($serrurekeys as $serrurekey){
	    if(in_array($serrurekey,$serrureids)){
		$template->parts[$key."class"]="";
		$template->parts[$key]=implode(",",$serrureids);
		break;
	    }
	}
    }
    
    $template->evaltemplate("ConfigurateurAffine","html");
}

function loadreponses(){
    // mise à jour paramètres categorie déjà enregistrés
    global $configurateur;
    global $categorie;
    global $config;
    
    $reponses=$configurateur->reponses();
    foreach($reponses as $reponse){
	switch($reponse->type){
	    case "utilisation":
	    if($reponse->value==1) $categorie->setparam("pro",2);
	    break;
	    
	    case "typeproduit":
	    $subcatids=$categorie->getsubcatids($reponse->value);
	    $categorie->setparam("subcatids",$subcatids);
	    break;
	    
	    case "pose":
	    $subcatids=$categorie->getsubcatids($reponse->value);
	    $categorie->setparam("subcatids",$subcatids);
	    break;
	    
	    case "dimensions":
	    foreach($config["dimensions"] as $key=>$var){
		if($reponse->value[$key]>0) $categorie->setparam($var,$reponse->value[$key]);
	    }
	    break;
	    
	    case "usage":
	    if($reponse->value>0) $categorie->setparam("usageids",$reponse->value);
	    break;
	    
	    case "temperatures":
	    if($reponse->value>0){
		$categorie->setparam("nbtemperature",$reponse->value);
	    }
	    break;
	    
	    case "capacite":
	    $categorie->setparam("capaciteids",$reponse->value);
	    break;
	    
	    case "finition":
	    $categorie->setparam("finitionids",$reponse->value);
	    break;
	    
	    case "esthetique":
	    if($reponse->value==1) $categorie->setparam("esthetique",1);
	    break;
	    
	    case "mintemp":
	    if($reponse->value<1000) $categorie->setparam("mintemp",$reponse->value);
	    break;
	  
	    case "maxtemp":
	    if($reponse->value<1000) $categorie->setparam("maxtemp",$reponse->value);
	    break;
	    
	    case "typeporte":
	    if($reponse->value>0) $categorie->setparam("typeporteids",$reponse->value);
	    break;
	    
	    case "rangement":
	    if($reponse->value!=array(0)) $categorie->setparam("moderangementids",$reponse->value);
	    break;
	    
	    case "classe":
	    $value=$reponse->value[0];
	    if($value!=0){
		$categorie->setparam("energy_label",$config['classe'][$value]);
	    }
	    break;
	    
	    case "prix":
	    if($reponse->value!=array(1000)) $categorie->setparam("prixids",$reponse->value);
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

function articlecount(){
    global $categorie;
    
    if($categorie->subcatids==array()){
	$subcatids=$categorie->getsubcatids(0);
	$categorie->setparam("subcatids",$subcatids);
    }
   
    $selection=$categorie->articles("ventes DESC",true);
    $categorie->setparam("bonsplans",array("selection"));
    $total=$categorie->articles("ventes DESC",true);
    $categorie->setparam("bonsplans",array());
    return array("selection"=>$selection,"total"=>$total);
}

function return_data($html,$nb,$error="",$articles=array()){
    global $categorie;
//     $error=$categorie->getsqlwhere();
    header("Cache-Control: no-cache");
    header("Content-type:text/xml");
    
    $total=count($articles['total']);
    $selection=count($articles['selection']);
    
    $vignettes="";
    if($selection<=30 && $selection>0){
	foreach($articles['selection'] as $artid)
	    $vignettes.=stripslashes(artcons($artid));
    }

    print "<?xml version=\"1.0\" encoding=\"iso-8859-15\"?>";
    print "<xml>";
    
    print "<nb>".$nb."</nb>";
    print "<html><![CDATA[".$html."]]></html>";
    print "<articles selection=\"".$selection."\" total=\"".$total."\"><![CDATA[".$vignettes."]]></articles>";
    print "<error>".$error."</error>";
    
    print "</xml>";
}

?>