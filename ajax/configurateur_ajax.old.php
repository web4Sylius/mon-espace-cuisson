<?php


if(isset($_POST['action']) && $_POST['action']=="logging"){
      
    $provenance="configurateur";
}

include("../global.php");
include_once("../class/configurateur.class.php");
include("../class/configurateur_display.php");
ini_set("display_errors","on");

$configurateur=new configurateur($DB_site);

if(isset($_POST['contact'])){
    $message = "" ;
    $erreur = false ;
 
    foreach($_POST as $name=>$value) $template->parts[$name]=$value;

    $message="<p>Bonjour,</p>
    <p>Une personne vient de remplir le formulaire de la page contacter de votre boutique</p>
    <p><b>Nom, prénom</b> : ".strip_tags($_POST['nom'])."
    <br><b>E-mail expéditeur</b> : ".strip_tags($_POST['expediteur'])."
    <br><b>Société / raison sociale</b> : ".strip_tags($_POST['societe'])."
    <br><b>Adresse</b> : ".strip_tags($_POST['adresse'])."
    <br><b>Code postal + ville</b> : ".strip_tags($_POST['code'])."
    <br><b>Pays</b> : ".strip_tags($_POST['pays'])."
    <br><b>Comment nous avez-vous connu ?</b> ".strip_tags($_POST['connaissance'])."
    <br><b>Téléphone</b> : ".strip_tags($_POST['telephone'])."
    <br><b>Sujet</b> : ".strip_tags($_POST['sujet'])."
    <br><b>Date de rappel souhaitée</b> : ".strip_tags($_POST['date_rappel'])."
    <br><b>Heure de rappel souhaitée</b> : ".strip_tags($_POST['heure_rappel'])."
    <br><b>Message</b> : ".strip_tags($_POST['message']);

    if($_POST['nom']=="" || $_POST['expediteur']=="" || $_POST['telephone']=="" || $_POST['message']=="") $erreur=true;

    if (!$erreur){
        $mail = new email();
        $mail->expediteur   = $template->translate("/site/[site_code]/mail_expediteur");
        $mail->expname      = $template->translate("/site/nom","F");
        $mail->destinataire = $template->translate("/site/[site_code]/mail_expediteur");
        $mail->to           = explode(",",$template->translate("/site/[site_code]/mail_commercial"));
        $mail->subject      = "Ma cave à vin : Réponse au formulaire de votre boutique.";
        $mail->body         = $message;
        $mail->sendmail();

        $sql = "INSERT INTO contact SET";
        $sql .= " date           = ".time()."";
        $sql .= ", nom           = '".mysql_real_escape_string(htmlentities($_POST['nom']))."'";
        $sql .= ", raisonsociale = '".mysql_real_escape_string(htmlentities($_POST['societe']))."'";
        $sql .= ", adresse       = '".mysql_real_escape_string(htmlentities($_POST['adresse']))."'";
        $sql .= ", ville         = '".mysql_real_escape_string(htmlentities($_POST['code']))."'";
        $sql .= ", pays          = '".mysql_real_escape_string(htmlentities($_POST['pays']))."'";
        $sql .= ", mail          = '".mysql_real_escape_string(htmlentities($_POST['expediteur']))."'";
        $sql .= ", telephone     = '".mysql_real_escape_string(htmlentities($_POST['telephone']))."'";
        $sql .= ", sujet         = '".mysql_real_escape_string(htmlentities($_POST['sujet']))."'";
        $sql .= ", daterappel    = '".mysql_real_escape_string(htmlentities($_POST['date_rappel']))."'";
        $sql .= ", heurerappel   = '".mysql_real_escape_string(htmlentities($_POST['heure_rappel']))."'";
        $sql .= ", message       = '".mysql_real_escape_string(htmlentities($_POST['message']))."'";
        $sql .= ", connu         = '".mysql_real_escape_string(htmlentities($_POST['connaissance']))."'";
    	$DB_site->query($sql);
    	
        return_data("",0,"ok");
    }else{
        return_data("",0,"error");
    }
}


$categorie=new categorie($DB_site);
$categorie->setparam("site_code",$template->site_code());
$categorie->setparam("secondchoix",0);
$categorie->setparam("active",1);

if(isset($register_action)){
    if($user_info==array()){
	$error=$template->parts[($register_action=="inscription2"?"errormessage":"raison")];
	return_data("","",$error);
	exit;
    }else{
	$loaded=$configurateur->loadcookie();
	$configurateur->userid=$user_info['userid'];
	$configurateur->update();
	$configurateur->config_categorie($categorie);
	$articles=articlecount();
	$configurateur->display_max=count($articles['total']);
// 	$configurateur->save_articles($articles['total']);
	$pageview=($register_action=="inscription2"?"/compte/configurateur-ouverture-ok.php":null);
	
	display_affine(null,"",$configurateur->reponses());
	$affinage=stripslashes($template->parts['html']);
	$template->parts['html']="";

	return_data("",0,"",$articles,$pageview,$affinage);
	exit;
    }
}

if(isset($_POST['affine'])){
    $loaded=$configurateur->loadcookie();
    $configurateur->setaffine($_POST['affine']);
    
    $configurateur->next();
    $configurateur->config_categorie($categorie);
    $articles=articlecount();
    $configurateur->display_max=count($articles['total']);
//     $configurateur->save_articles($articles['total']);
    
    
    display_affine(null,null,$configurateur->reponses(),$_POST['affine']);

    return_data("",0,"",$articles,null,stripslashes($template->parts['html']));
    exit;
}

if(!$configurateur->verif_type($_GET['rep'])) exit;
if(isset($_GET['rep']) && !in_array($_GET['rep'],array("start","reload"))){
    $loaded=$configurateur->loadcookie();
    if(!$loaded){
	return_data("","","Cookie error !");
	exit;
    }
    
    $reponses=$configurateur->reponses();
    
    // vérification retour en arrière
    foreach($reponses as $reponse){
	if($reponse->type==$_GET['rep'] && !$reponse->affine){
	  $configurateur->del_reponses($reponse->nb+1);
	  $configurateur->setaffine(null);
	  $reponses=$configurateur->reponses();
	  break;
	}
    }
    $repnb=count($reponses);
    
}



// enregistrement réponse + affichage suite
switch($_GET['rep']){
    /*case "start":
    $configurateur->userid=$user_info['userid'];
    $configurateur->session=$s;
    if(isset($_COOKIE['admin_username']) && $_COOKIE['admin_username']!="") $configurateur->commercial=$_COOKIE['admin_username'];
    $configurateur->insert();
    
    $template->parts["repnb"]=1;
    $template->evaltemplate("ConfigurateurUtilisation","html");
    return_data(stripslashes($template->parts["html"]),$repnb+1);
    break;*/
    
    case "start":
        $configurateur->userid=$user_info['userid'];
        $configurateur->session=$s;
        $configurateur->loadconfigtype($_GET['value']);
        if(isset($_COOKIE['admin_username']) && $_COOKIE['admin_username']!="") $configurateur->commercial=$_COOKIE['admin_username'];
        $configurateur->insert();
        $configurateur->config_categorie($categorie);
        
        /*$resp=new configurateur_reponse($DB_site);
        $resp->configurateurid=$configurateur->configurateurid;
        $resp->type="utilisation";
        if(!$resp->exists())
    	$resp->nb=0;
        
        $resp->setvalue(intval($template->pro));
        $resp->save();*/
        
        $template->parts["repnb"]=1;
        $next=$configurateur->next();
        $pageview="/configurateur/".$next;
        $func="display_".$next;
        if(function_exists($func)) $func();
        return_data(stripslashes($template->parts["html"]),1,"",array(),$pageview);
        break;
    
    case "ident":
        $template->parts["zonedeliste_pays"]="";
        $payss=$DB_site->query("SELECT * FROM pays INNER JOIN fraisport USING(paysid) WHERE sites='".$template->site_code()."' GROUP BY paysid ORDER BY libelle ASC");
        while($pays=$DB_site->fetch_array($payss)){
    	   $template->parts["zonedeliste_pays"] .= "<option value=$pays[paysid] ".($pays[paysid]==57?"selected":"").">$pays[libelle]</option>";
        }
        
        $template->evaltemplate("ConfigIdent","main");
        return_data(stripslashes($template->parts["main"]),$repnb+1,"");
        break;
    
    case "reload":
        $configurateur=new configurateur($DB_site,$_GET['value']);

    //     if($configurateur->session!=$s) exit;
        $configurateur->cookie();
        $reponses=$configurateur->reponses();
        $questions=array();

        foreach($reponses as $reponse){
    	$template->parts["html"]="";
    	$func="display_".$reponse->type;
    	
    	if(function_exists($func) && !$reponse->affine){
    	    $func($reponse);
    	    $questions[$reponse->nb]=$template->parts["html"];
    	}
    	$configurateur->config_categorie($categorie,$reponse);
    	
    	if(in_array($reponse->type,$configurateur->laststep())){
    	    $configurateur->display_max=1000;
    	}
        }
        
        if(count($reponses)>0) $articles=articlecount();
    //     $configurateur->save_articles($articles['total']);
        
        $template->parts["html"]="";
        $next=$configurateur->next();
        $func="display_".$next;// && count($articles['total'])>$configurateur->display_msg
        if(!is_null($next) && function_exists($func)){
    	$func();
    	$questions[$reponse->nb+1]=$template->parts["html"];
        }
        

        
        $affinage=null;
        if(count($articles['total'])<=$configurateur->display_max || count($articles['total'])<=$configurateur->display_msg){
        	display_affine(null,"",$reponses);
    	$affinage=stripslashes($template->parts['html']);
    	$template->parts['html']="";
        }
        
        return_data($questions,0,"",$articles,"",$affinage);
        break;
        
    case "limite":
        $resp=new configurateur_reponse($DB_site);
        $resp->configurateurid=$configurateur->configurateurid;
        $resp->type="limite";
        if(!$resp->exists())
    	   $resp->nb=$repnb+1;
    	

        $resp->setvalue($_GET['value']);
        $resp->save();
        $configurateur->config_categorie($categorie);
        $articles=articlecount();
    //     $configurateur->save_articles($articles['total']);
        
        $next=$configurateur->next();
        $pageview="/configurateur/".$next;
        $func="display_".$next;
        if(function_exists($func)) $func();
        
        return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",array(),$pageview);
        break;
    
    case "typeproduit":
        $resp=new configurateur_reponse($DB_site);
        $resp->configurateurid=$configurateur->configurateurid;
        $resp->type="typeproduit";
        if(!$resp->exists())
    	$resp->nb=$repnb+1;
        
        $resp->setvalue(explode(",",$_GET['value']));
        $resp->save();
        
        $configurateur->config_categorie($categorie);
        $articles=articlecount();
    //     $configurateur->save_articles($articles['total']);
        
        
        $template->parts["repnb"]=$resp->nb+1;
        $config_typeproduit=$configurateur->config('typeproduit');

        $next=$configurateur->next();
        $pageview="/configurateur/".$next;
        if($next=="contact") $articles=array();
        $func="display_".$next;
        if(function_exists($func)) $func();
        
        return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles,$pageview);
        break;
    
    /*case "pose":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="pose";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue(explode(",",$_GET['value']));
    $resp->save();
    
    $configurateur->config_categorie($categorie);
    $articles=articlecount();
    $configurateur->save_articles($articles['total']);
    
    $next=$configurateur->next();
    $pageview=$next;
    $func="display_".$next;
    if(function_exists($func)) $func();  
    if($next="") $configurateur->display_max=count($articles['total']);
    $template->parts["repnb"]=$resp->nb+1;
    
    
    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles,$pageview);
    break;*/
    
    case "posevitrine":
        $resp=new configurateur_reponse($DB_site);
        $resp->configurateurid=$configurateur->configurateurid;
        $resp->type="posevitrine";
        if(!$resp->exists())
    	$resp->nb=$repnb+1;
        
        $resp->setvalue(explode(",",$_GET['value']));
        $resp->save();
        
        $configurateur->config_categorie($categorie);
        $articles=articlecount();
    //     $configurateur->save_articles($articles['total']);
        
        $next=$configurateur->next();
        $func="display_".$next;
        if(function_exists($func)) $func();
        $pageview="/configurateur/".$next;
        
        $template->parts["repnb"]=$resp->nb+1;
        return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles,$pageview);
        break;
    
    /*case "typeencastrable":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="typeencastrable";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue(explode(",",$_GET['value']));
    $resp->save();
    
    $configurateur->config_categorie($categorie);
    $articles=articlecount();
    $configurateur->save_articles($articles['total']);
    
    $next=$configurateur->next();
    $func="display_".$next;
    if(function_exists($func)) $func();
    $pageview=$next;
    

    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles,$pageview);
    break;*/
    
    case "dimensions":
        $resp=new configurateur_reponse($DB_site);
        $resp->configurateurid=$configurateur->configurateurid;
        $resp->type="dimensions";
        if(!$resp->exists())
    	$resp->nb=$repnb+1;
        
        $resp->setvalue(explode(",",$_GET['value']));
        $resp->save();
        
        $configurateur->config_categorie($categorie);
        $articles=articlecount();
    //     $configurateur->save_articles($articles['total']);
        if(count($articles['total'])==0)
          $template->evaltemplate("ConfigError","html");
        else{
          $next=$configurateur->next();
          $func="display_".$next;
          if(function_exists($func)) $func();
          $pageview="/configurateur/".$next;
          /*$typeproduit=$configurateur->get_response("typeproduit");
          $config_typeproduit=$configurateur->config("typeproduit");
          
          $pose=$configurateur->get_response("pose");
          $config_pose=$configurateur->config("pose");
          switch($typeproduit->value){
    	  case $config_typeproduit['typesrep1']:
    	  if($pose->value!=$config_pose["poserep2"]) // encastrable
    	      display_usage();
    	  break;
    	  
    	  case $config_typeproduit['typesrep2']:
    	  display_capacite();
    	  break;
    	  
    	  default:
    	  display_usage();
          }*/
          
        }
        
        $template->parts["repnb"]=$resp->nb+1;
        
        return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles,$pageview);
        break;
    
    case "typecadre":
        $resp=new configurateur_reponse($DB_site);
        $resp->configurateurid=$configurateur->configurateurid;
        $resp->type="typecadre";
        if(!$resp->exists())
    	$resp->nb=$repnb+1;
        
        $resp->setvalue(explode(",",$_GET['value']));
        $resp->save();
        
        $configurateur->config_categorie($categorie);
        $articles=articlecount();
    //     $configurateur->save_articles($articles['total']);
        $configurateur->display_max=count($articles['total']);

        return_data("",0,"",$articles);
        break;
    
    case "usage":
        $resp=new configurateur_reponse($DB_site);
        $resp->configurateurid=$configurateur->configurateurid;
        $resp->type="usage";
        if(!$resp->exists())
	       $resp->nb=$repnb+1;
    
        $resp->setvalue($_GET['value']);
        $resp->save();
    
        $configurateur->config_categorie($categorie);
        $articles=articlecount();
//     $configurateur->save_articles($articles['total']);
    
        $template->parts["repnb"]=$resp->nb+1;

        $next=$configurateur->next();
        $func="display_".$next;
        if(function_exists($func)) $func();
            $pageview="/configurateur/".$next;
    
        $html=stripslashes($template->parts["html"]);
        
        $affine=null;
        if($user_info['userid']!=array()){
	       display_affine(null,null,$configurateur->reponses());
	       $affine=stripslashes($template->parts['html']);
        }
    
        return_data($html,$resp->nb+1,"",$articles,$pageview,$affine);
        break;
    
    /*case "temperatures":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="temperatures";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue($_GET['value']);
    $resp->save();
    
    $configurateur->config_categorie($categorie);
    $articles=articlecount();
    $configurateur->save_articles($articles['total']);
    
    $next=$configurateur->next();
    $func="display_".$next;
    if(function_exists($func)) $func();
    $pageview=$next;
  
    $template->parts["repnb"]=$resp->nb+1;
//     display_capacite();
    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles,$pageview);
    break;*/
    
    /*case "capacite":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="capacite";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue($_GET['value']);
    $resp->save();
    
    $configurateur->config_categorie($categorie);
    $articles=articlecount();
    $configurateur->save_articles($articles['total']);
    
    $next=$configurateur->next();
    $pageview=$next;
    $func="display_".$next;
    if(function_exists($func)) $func();
    if($next=="") $configurateur->display_max=count($articles['total']);
    
    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles,$pageview);
    break;*/
    
    /*case "finition":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="finition";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue(explode(",",$_GET['value']));
    $resp->save();
    
    $configurateur->config_categorie($categorie);
    $articles=articlecount();
    $configurateur->save_articles($articles['total']);
    
    
    $next=$configurateur->next();
    $pageview=$next;
    $func="display_".$next;
    if(function_exists($func)) $func();
    
    $template->parts["repnb"]=$resp->nb+1;
    
    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles,$pageview);
    break;*/
    
    /*case "esthetique":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="esthetique";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue(explode(",",$_GET['value']));
    $resp->save();
    
    $configurateur->config_categorie($categorie);
    $articles=articlecount();
    $configurateur->save_articles($articles['total']);
    
    $template->parts["repnb"]=$resp->nb+1;    
//     display_installtemp();
    $next=$configurateur->next();
    $pageview=$next;
    $func="display_".$next;
    if(function_exists($func)) $func();

    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles,"installtemp");
    break;*/
    
    /*case "installtemp":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="installtemp";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue(explode(",",$_GET['value']));
    $resp->save();
    
    $configurateur->config_categorie($categorie);
    $articles=articlecount();
    $configurateur->save_articles($articles['total']);

    $next=$configurateur->next();
    $pageview=$next;
    $func="display_".$next;
    if(function_exists($func)) $func();
    
    $template->parts["repnb"]=$resp->nb+1;
    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles,$pageview); 
    break;*/
    
    /*case "typeporte":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="typeporte";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
    
    $resp->setvalue($_GET['value']);
    $resp->save();
    
    $configurateur->config_categorie($categorie);
    $articles=articlecount();
    $configurateur->save_articles($articles['total']);
    
    
    $next=$configurateur->next();
    $pageview=$next;
    $func="display_".$next;
    if(function_exists($func)) $func();
    
    $template->parts["repnb"]=$resp->nb+1;
    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles,$pageview);     
    break;*/
    
    case "rangement":
        $resp=new configurateur_reponse($DB_site);
        $resp->configurateurid=$configurateur->configurateurid;
        $resp->type="rangement";
        if(!$resp->exists())
    	$resp->nb=$repnb+1;
        
        $resp->setvalue(explode(",",$_GET['value']));
        $resp->save();
        
        $configurateur->config_categorie($categorie);
        $articles=articlecount();
        $configurateur->display_max=count($articles['total']);
    //     $configurateur->save_articles($articles['total']);
        
        $affinage=null;
        if(in_array("rangement",$configurateur->laststep())){
    	display_affine();
    	$affinage=stripslashes($template->parts['html']);
    	$template->parts['html']="";
        }
        return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles,$pageview,$affinage);    
        break;
    
    /*case "budget":
    $resp=new configurateur_reponse($DB_site);
    $resp->configurateurid=$configurateur->configurateurid;
    $resp->type="budget";
    if(!$resp->exists())
	$resp->nb=$repnb+1;
	
    $resp->setvalue($_GET['value']);
    $resp->save();
    
    $configurateur->config_categorie($categorie);
    $articles=articlecount();
    $configurateur->save_articles($articles['total']);
    
    $next=$configurateur->next();
    $pageview=$next;
    $func="display_".$next;
    if(function_exists($func)) $func();
    
    $template->parts["repnb"]=$resp->nb+1;
    return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles,$pageview); 
    break;*/
    
    case "colorisencastrable":
        $resp=new configurateur_reponse($DB_site);
        $resp->configurateurid=$configurateur->configurateurid;
        $resp->type=$_GET['rep'];
        if(!$resp->exists())
    	$resp->nb=$repnb+1;
        
        $resp->setvalue(explode(",",$_GET['value']));
        $resp->save();
        
        $configurateur->config_categorie($categorie);
        $articles=articlecount();
        $configurateur->display_max=count($articles['total']);
    //     $configurateur->save_articles($articles['total']);
        
    //     display_affine($resp->nb+1,"encastrable");
        $next=$configurateur->next();
        $pageview="/configurateur/".$next;
        $func="display_".$next;
        if(function_exists($func)) $func($resp->nb+1);
        return_data(stripslashes($template->parts["html"]),$resp->nb+1,"",$articles,"affine");
        break;
    
    case "capacite":
    case "installtemp":
    case "typeporte":
    case "esthetique":
    case "typeencastrable":
    case "temperatures":
    case "finition":
    case "pose":
    case "marque":
    case "classe":
    case "prix":
    case "coloris":
    case "serrure":
    case "origine":
    case "sensporte":
    case "budget":
    
        $resp=new configurateur_reponse($DB_site);
        $resp->configurateurid=$configurateur->configurateurid;
        $resp->type=$_GET['rep'];
        if(!$resp->exists()){
        	$repnb++;
        	$resp->nb=$repnb;
        }
        
       
        $resp->affine=$configurateur->getaffine($resp->type);
        $resp->setvalue(explode(",",$_GET['value']));
        $resp->save();
        
        $configurateur->config_categorie($categorie);
        $html=array();
        
        do {
        	$next=$configurateur->next();
        	$pageview="/configurateur/".$next;
        	$func="display_".$next;
    	
        	if(function_exists($func)){
        	    $singlerep=$func();
        	    $html[$repnb+1]=stripslashes($template->parts["html"]);
        	    $template->parts["html"]="";
        	}
        	if($singlerep!=false){
        	    $resp2=new configurateur_reponse($DB_site);
        	    $resp2->configurateurid=$configurateur->configurateurid;
        	    $resp2->type=$next;
        	    $repnb++;
        	    $resp2->nb=$repnb;
        	    $resp2->setvalue($singlerep);
        	    $resp2->save();
        	    $configurateur->config_categorie($categorie,$resp2);
        	}
        } while(!is_null($next) && $singlerep!=false);
        
        $articles=articlecount();
        
        if(is_null($next)){
        	$configurateur->display_max=count($articles['total']);
        	$pageview="/configurateur/affine";
        	$template->parts["html"]="";
        }
        
        
        $affine=null;
        if($user_info['userid']!=array()){
        	display_affine(null,null,$configurateur->reponses());
        	$affine=stripslashes($template->parts['html']);
        }
        return_data($html,$resp->nb+1,"",$articles,$pageview,$affine);
        break;
}


function articlecount(){
    global $categorie;
    
    if($categorie->subcatids==array()){
    	$subcatids=$categorie->getsubcatids(0);
    	$categorie->setparam("subcatids",$subcatids);
    }
   
    $selection=$categorie->articles("prix",true);
    $categorie->setparam("bonsplans",array("selection"));
    $total=$categorie->articles("prix",true);
    $categorie->setparam("bonsplans",array());
    
    $diff=array_diff($total,$selection);
    
    return array("selection"=>$selection,"total"=>$total,"diff"=>$diff);
}

function return_data($html,$nb,$error="",$articles=array(),$pageview=null,$affinage=null){
    global $categorie;
    global $configurateur;
    global $user_info;
    global $template;
    
//     $error=$categorie->getsqlwhere();
    header("Cache-Control: no-cache");
    header("Content-type:text/xml");
    
    $total=count($articles['total']);
    $selection=count($articles['selection']);

    $vignettes_selection=$vignettes_total=$xmlaffinage="";

    //Commenté le 07/02 pour tester la configuration sans avoir besoin d'être log
    //Décommenté le 18/04 pour remettre en place l'inscription obligatoire
    if(($selection<=$configurateur->display_msg || $selection<=$configurateur->display_max) && ($selection+$total)>0 && $user_info['userid']!=""){
    
    //A commenter/décommenter pour activer/désactiver l'étape d'inscription/login
    //if(($selection<=$configurateur->display_msg || $selection<=$configurateur->display_max) && ($selection+$total)>0){ 
	$template->parts["artsim"]="";
	$c=1;
	foreach($articles['selection'] as $artid){
	    vignette($artid,"similaire",$c==1?"premier_acc":""."vignette_acc");
	    $c++;
	    if($c==5) $c=1;
	}
	$vignettes_selection=stripslashes($template->parts["artsim"]);
	if($vignettes_selection!="") $vignettes_selection.="<div class=\"clear\"></div>";
	
	$template->parts["artsim"]="";
	$c=1;
	foreach($articles['diff'] as $artid){
	    vignette($artid,"similaire",$c==1?"premier_acc":""."vignette_acc");
	    $c++;
	    if($c==5) $c=1;
	}
	$vignettes_total=stripslashes($template->parts["artsim"]);
	if($vignettes_total!="") $vignettes_total.="<div class=\"clear\"></div>";
	
	
	if(!is_null($affinage)) $xmlaffinage="<affinage><![CDATA[".$affinage."]]></affinage>";
    }

    $preview="";
    if($selection>0){
	$last=count($articles['selection'])-1;
	$last=round($last/2);
	$preview="<img src=\"/images/".$articles['selection'][$last]."_170_220.jpg\">";
    }

    $hidelast=false;
    if($selection>0 && $selection<=$configurateur->display_max){
    	
        //Commenté le 07/02 pour tester la configuration sans avoir besoin d'être log
        //Décommenté le 18/04 pour remettre en place l'inscription obligatoire
        if($user_info['userid']!=""){
            //if($_POST['action']=="logging")
        		$display="results";
            //else
            //$display="ok";
            $configurateur->save_articles($articles['total']);
        } else
    	    $display="ident";
       
        //$display="results"; //A supprimer pour réactiver l'étape du login

        $hidelast=true;

    } elseif($selection>0 && $selection<=$configurateur->display_msg){
	$display="msg";
	$hidelast=true;
	$configurateur->save_articles($articles['total']);
    }else{
	$display="0";
	$configurateur->save_articles(false);
    }
	
    if(isset($_POST['affine']) || $configurateur->is_affine($_GET['rep'])) $display="results";
    
    $qhtml="";
    if(is_array($html)){
	foreach($html as $nb=>$q){
	    $visible=($q==end($html) && $hidelast && $_GET['rep']!="reload" ? 0 : 1);
	    $qhtml.="<question nb=\"".$nb."\" visible=\"".$visible."\"><![CDATA[".stripslashes($q)."]]></question>";
	}
	$html="";
    }
    
    
    print "<?xml version=\"1.0\"?>";
    print "<xml>";
    
    print "<nb>".$nb."</nb>";
    print "<html><![CDATA[".$html."]]></html>";
    print $qhtml;
    print $xmlaffinage;
    print "<articles>
    <total count=\"".$total."\"><![CDATA[".$vignettes_total."]]></total>
    <selection count=\"".$selection."\" display=\"".$display."\"><![CDATA[".$vignettes_selection."]]></selection>
    </articles>";
    print "<preview><![CDATA[".$preview."]]></preview>";
    print "<pageview>".(!is_null($pageview)?$pageview:"")."</pageview>";
    print "<error><![CDATA[".$error."]]></error>";
    
    print "</xml>";
}

?>