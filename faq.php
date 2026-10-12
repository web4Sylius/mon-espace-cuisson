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
	$faq=$DB_site->query_first("SELECT $libelle FROM $table_faq WHERE faqid = '$id'");
	if (url_rewrite($faq[$libelle]) == ""){
		$url_reel = "-.htm";
    header("Location: /");
    exit(); 
	}else
		$url_reel = url_rewrite($faq[$libelle]).".htm";
	
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
	$faq = $DB_site->query_first(" SELECT $libelle, libelleFR,libelleEN, faqparent,active, faqid, bandeau_defilant, ref_title".$template->langue.", ref_description".$template->langue.", $ref_question,$ref_reponse FROM ".$table_faq." WHERE active = '1' AND faqid = '$id' ");
} 
//ici on fais afficher les balise hreflang pour fr et en
foreach($template->sites() as $site_code=>$site){
    foreach($site['hosts'] as $url=>$deflangue){
      if($site['actif'][$url]){
        if($faq['libelle'.$deflangue] != '') {
          $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url."/faq,".$id.",".url_rewrite($faq['libelle'.$deflangue]).".htm\">\n";
        }
      }
    }
}
$i=0;
$questions=$DB_site->query("SELECT faqid, libelle".$template->langue." AS libelle, sans_lien FROM ".$table_faq." WHERE active = '1' AND faqparent=0 ORDER BY position");
while($question=$DB_site->fetch_array($questions)){
  if($question['libelle'] != ''){
    $ssmenu="";
    $sql="";
    $selectclass="";
    if($question['faqid']==$id )
      $selectclass="selected";

    $sql="SELECT faqid, libelle".$template->langue." AS libelle, sans_lien FROM ".$table_faq." WHERE active = '1' AND faqparent = '".$question['faqid']."' ORDER BY position ASC ";
    $ssmenus = $DB_site->query($sql);
     if($DB_site->num_rows($ssmenus)>0){
      $ssmenu="<li style=\"list-style-type: none;\"><ul style=\"margin-top:0;margin-left: -18px;\">";
      while($ssm=$DB_site->fetch_array($ssmenus)){
        if($ssm['libelle'] !=''){
          $selectclass2="";
          if($ssm['faqid']==$id) $selectclass2="selected";
            $ssmenu.="<li class=\"".$selectclass2." ssmquestions\" style=\"font-weight:normal;\">".($ssm['sans_lien']==false?"<a href=\"faq,$ssm[faqid],".url_rewrite($ssm['libelle']).".htm\">$ssm[libelle]</a>":"<span>".$ssm[libelle]."</span>")."</li>";
        }
      }
      $ssmenu.="</ul></li>";
     }
     $template->parts["menuautresfaq"].="<ul class=\"ul_bloc\"><li class=\"".$selectclass." titre_princi_faq\">".($question['sans_lien']==false?"<a href=\"faq,$question[faqid],".url_rewrite($question['libelle']).".htm\">$question[libelle]</a> <span class=\"fleche\" onclick=\"deroule_ss_menu(this)\">".($i==0?'▲':'▼')."</span>":$question[libelle]." <span class=\"fleche\" onclick=\"deroule_ss_menu(this)\">".($i==0?'▲':'▼')."</span>").$ssmenu."</ul>";

     $i++;
  }
}


//Récupération des articles correspondants à la page
$articles_page =$DB_site->query("SELECT c_a.artid FROM ".$table_faq_article." c_a INNER JOIN article a USING(artid) WHERE faqid=".intval($id)." AND a.active=\"1\" ORDER BY ordre ASC LIMIT 5");
$c=1;
while($row = $DB_site->fetch_array($articles_page) ){
  $class="";
  //affichages des articles dans la page (template InstituPage.html)
  vignette(intval($row['artid']),"categorie",$class);
  $c++;
}
$template->parts["articles"].="<div class=\"clear\"></div>";


$template->parts["content"]='';
$path = "/images/".$blocfaq."/";
$image='';
$blocs = $DB_site->query("SELECT * FROM ".$table_faq_bloc." WHERE faqid=".$id." AND langue ='".$template->langue."' ORDER BY ordre ASC");
while ($leBloc = $DB_site->fetch_array($blocs)) {
  $eval = $DB_site->query_first("SELECT bloc_eval FROM faq_bloc_type WHERE cbtid =".$leBloc['typeid']."");
  if(($leBloc['image'] != 'NULL') && ($leBloc['image'] != '')){
    $imageSrc = $path.$leBloc['faq_blocid'].".".$leBloc['image'];
    $imagesize = getimagesize($rootpath."configurations/".HOST.$imageSrc);
    if($leBloc['url_img']!==''){
      $image = "<a href=\"".$leBloc['url_img']."\" style=\"display:block;\"><img src=\"".$imageSrc."\" width=\"".$imagesize[0]."\" height=\"".$imagesize[1]."\" alt=\"".$leBloc['alt_img']."\"></a>";
    }
    else{
      $image = "<img src=\"".$imageSrc."\" width=\"".$imagesize[0]."\" height=\"".$imagesize[1]."\" alt=\"".$leBloc['alt_img']."\">";
    }
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

$encarts=encarts($template->site_code(),$template->langue,"faq",$id);
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

$template->evaltemplate("MenuFaq","faqmenu");

$template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$faq[$libelle]."</span><meta property=\"position\" content=\"2\"></span>";
$template->parts["title"]=$faq[$libelle];


//en bref bloc
if($faq[$ref_reponse]!='' && $faq[$ref_question] != ''){
  $template->parts['ref_question']=$faq[$ref_question];
  $template->parts['ref_reponse']=$faq[$ref_reponse];
  $template->parts['ref_url']=SITE_URL.$_SERVER[REQUEST_URI];
  $template->evaltemplate("ConseilBref","bref");
}

include("barrefixe.php");

$template->pageparams($host,$faq['ref_title'.$template->langue],$faq['ref_description'.$template->langue]);
$template->evaltemplate("FaqPage","main");

$template->parts["remarketing"]=google_remarketing('',"siteview",'');

$template->out();



?>