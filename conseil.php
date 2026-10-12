<?php

require ("global.php");
$libelle="libelle".$template->langue;
$ref_question="ref_question".$template->langue;
$ref_reponse="ref_reponse".$template->langue;
$description="description".$template->langue;

$url_actuelle = $_SERVER[REQUEST_URI];
$url_actuelle = explode(",",$url_actuelle);
$last_niveau = count($url_actuelle) - 1;
// on récupère le réel dernier niveau de l'URL

if (substr($_SERVER[REQUEST_URI],-4)!="html"){
	$instit=$DB_site->query_first("SELECT $libelle FROM $table_conseil WHERE conseilid = '$id'");
	if (url_rewrite($instit[$libelle]) == ""){
		$url_reel = "-.htm";
    header("Location: /");
    exit(); 
	}else
		$url_reel = url_rewrite($instit[$libelle]).".htm";
	
	if ($url_actuelle[$last_niveau] != $url_reel){
		if ($url_actuelle[$last_niveau] == "")
			$url_redirect = "int,$id,$url_reel.htm";
		else
			$url_redirect = str_replace("$url_actuelle[$last_niveau]","$url_reel",$_SERVER[REQUEST_URI]);
		header("Status: 301 Moved Permanently");
		header("Location: $url_redirect");
		exit();	
	}
}
//pour site part et pro sauf pro en
if($template->langue == 'EN' && $sitepro){
  $template->parts["follow"]='<meta NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">';
}else{
  $template->parts["follow"]='<meta NAME="ROBOTS" CONTENT="INDEX, FOLLOW, ALL">';
}

$id=$_GET['id'];
if (isset($id) and $id!=""){
	$temp="title$langue";	
	$institutionnel = $DB_site->query_first(" SELECT $libelle, libelleFR,libelleEN, conseilparent, conseilid, bandeau_defilant, ref_title".$template->langue.", ref_description".$template->langue.", site_code, $ref_question,$ref_reponse FROM ".$table_conseil." WHERE conseilid = '$id' ");
	if($institutionnel['site_code']=="uk"){
                $redir_page=$DB_site->query_first("SELECT conseilid,$libelle FROM ".$table_conseil." WHERE libelleFR='".mysqli_real_escape_string($DB_site->link_id,$institutionnel['libelleFR'])."' AND site_code='base'");
                if($redir_page['conseilid']!="") $template->redirect($template->mkurl("conseil",$redir_page['conseilid'],$redir_page),301);
	}
} 
//ici on fais afficher les balise hreflang pour fr et en
foreach($template->sites() as $site_code=>$site){
    foreach($site['hosts'] as $url=>$deflangue){
      if($site['actif'][$url]){
        if($institutionnel['libelle'.$deflangue] != '')
          $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url."/conseil,".$id.",".url_rewrite($institutionnel['libelle'.$deflangue]).".htm\">\n";
      }
    }
}
$i=0;
$nb_colonne=0;
$impair=false;
$conseils=$DB_site->query("SELECT conseilid, libelle".$template->langue." AS libelle FROM ".$table_conseil." WHERE conseilparent=0 AND site_code='".$template->site_code()."' ORDER BY position");
if($DB_site->num_rows($conseils)%2 == 1){
  //$impair=true;
}

while($conseil=$DB_site->fetch_array($conseils)){
  $nb_colonne++;
  if($conseil['libelle'] != ''){
    $ssmenu="";
    $sql="";
    $selectclass="";
    if($conseil['conseilid']==$id )
      $selectclass="selected";

    $sql="SELECT conseilid, libelle".$template->langue." AS libelle FROM ".$table_conseil." WHERE conseilparent = '".$conseil['conseilid']."' ORDER BY position ASC ";
    $ssmenus = $DB_site->query($sql);
    if($DB_site->num_rows($ssmenus)>0){
      $ssmenu="<li style=\"list-style-type: none;\"><ul style=\"margin-top:0;margin-left:-25px;\">";
      while($ssm=$DB_site->fetch_array($ssmenus)){
        if($ssm['libelle'] !=''){
          $selectclass2="";
          if($ssm['conseilid']==$id) $selectclass2="selected";
            $ssmenu.="<li class=\"".$selectclass2." ssmconseils\"><a href=\"conseil,$ssm[conseilid],".url_rewrite($ssm['libelle']).".htm\">$ssm[libelle]</a></li>";
        }
      }
      $ssmenu.="</ul></li>";
    }
    if($nb_colonne==$DB_site->num_rows($conseils) && $impair==true){
      $ssmenu .="";
    }elseif(($i == 1 && $impair==true && $nb_colonne!==$DB_site->num_rows($conseils)-1) || $i == 1 && $impair==false){
      $ssmenu .="</ul></div><div class=\"autres\"><ul>";
      $i = -1;
    }
    $template->parts["menuautres"].="<li class=\"".$selectclass." titre_princi_cons\" style=\"font-weight:bold;margin-top:6px;\"><a href=\"conseil,$conseil[conseilid],".url_rewrite($conseil['libelle']).".htm\">$conseil[libelle]</a><span class=\"fleche\" onclick=\"deroule_ss_menu(this,true)\">▼</span></li>".$ssmenu;
    $i++;
  }
}

//Récupération des articles correspondants à la page
$articles_page =$DB_site->query("SELECT c_a.artid FROM ".$table_conseil_article." c_a INNER JOIN article a USING(artid) WHERE conseilid=".intval($id)." AND a.active=\"1\" ORDER BY ordre ASC LIMIT 5");
$c=1;
while($row = $DB_site->fetch_array($articles_page) ){
  $class="";
  //affichages des articles dans la page (template InstituPage.html)
  vignette(intval($row['artid']),"categorie",$class);
  $c++;
}
$template->parts["articles"].="<div class=\"clear\"></div>";
//on recupère les blocs de la page :
$template->parts["content"]='';
$path = "/images/".$bloc."/";
$image='';
$blocs = $DB_site->query("SELECT * FROM ".$table_conseil_bloc." WHERE conseilid=".$id." AND langue ='".$template->langue."' ORDER BY ordre ASC");
while ($leBloc = $DB_site->fetch_array($blocs)) {
  $eval = $DB_site->query_first("SELECT bloc_eval FROM conseil_bloc_type WHERE cbtid =".$leBloc['typeid']."");
  if(($leBloc['image'] != 'NULL') && ($leBloc['image'] != '')){
    $imageSrc = $path.$leBloc['conseil_blocid'].".".$leBloc['image'];
	$imagesize = getimagesize($rootpath."configurations/".HOST.$imageSrc);
	$image = "<img src=\"".$imageSrc."\" width=\"".$imagesize[0]."\" height=\"".$imagesize[1]."\" alt=\"".$leBloc['alt_img']."\">";
  }
  $content ="";
  if($leBloc['typeid'] == 3 || $leBloc['typeid'] == 9 ) 
    $contenu = str_replace(array('<p>','</p>','<ul>','</ul>'), '', $leBloc['contenu']);
  else
    $contenu = $leBloc['contenu'] ;

  $recherche = array('{contenu}','{image}');
  $remplace = array($contenu,$image);
  $content  = "<div class=\"row\">";
  $content .= str_replace($recherche, $remplace, $eval[0]);
  $content .= "</div>";
  $template->parts["content"] .= $content;

}

$encarts=encarts($template->site_code(),$template->langue,"conseil",$id);
$i=1;
foreach($encarts as $encart){
  $template->parts['marginright0'] = '';
  $template->parts['boots'] = 'encarts_width';
  if($i==3) $template->parts['boots'] .= " hidden-xs hidden-sm";
  if($i==4){ 
    $template->parts['marginright0'] = 'margin-right: 0;';
    $template->parts['boots'] .= " hidden-xs hidden-sm hidden-md encarts_disp";
  }
  $template->evaltemplate($encart,"encarts");
  $i++;
}

$template->evaltemplate("MenuConseils","institumenu");

$template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$institutionnel[$libelle]."</span><meta property=\"position\" content=\"2\"></span>";
$template->parts["title"]=$institutionnel[$libelle];

//en bref bloc
if($institutionnel[$ref_reponse]!='' && $institutionnel[$ref_question] != ''){
  $template->parts['ref_question']=$institutionnel[$ref_question];
  $template->parts['ref_reponse']=$institutionnel[$ref_reponse];
  $template->parts['ref_url']=SITE_URL.$_SERVER[REQUEST_URI];
  $template->evaltemplate("ConseilBref","bref");
}


include("barrefixe.php");

$template->pageparams($host,$institutionnel['ref_title'.$template->langue],$institutionnel['ref_description'.$template->langue]);
$template->evaltemplate("InstituPage","main");

$template->parts["remarketing"]=google_remarketing('',"siteview",'');

$template->out();



?>