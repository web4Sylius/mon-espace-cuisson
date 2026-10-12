<?php
require "admin_global.php";

include "../class/categorie.class.php";
include "../class/fonctions.php";

include "./header.php";
include "./menu.php";

print <<<CSS
<style>
.main { width:100%; }
.mainpage { width:calc(100% - 200px); }
#theaddiv { overflow-x:hidden; overflow-y:hidden; }
#theadrightdiv { float:left; width:calc(100% - 310px); overflow:hidden; }
#theadleft { width:300px; float:left; }
.thead { table-layout:fixed; border-collapse:collapse; }
.thead td { height:100px; background:white; text-align:center; font-weight:bold; font-size:11px; font-family:arial; border:none; }
.thead .ftitle > div { width: 30px; transform: translate(0px, 40px) rotate(315deg); }
.thead .ftitle > div > span { border-top: 1px solid #ccc; padding-left:10px; white-space:nowrap; }
#tbodyleftdiv { width:300px; height:700px; float:left; overflow:hidden; }
#tbodyrightdiv { float:left; width:calc(100% - 300px); height:700px; overflow:auto; }
.tbody { table-layout:fixed; border:solid 1px #CCC; border-collapse:collapse; }
.tbody tr:nth-child(odd) { background:#DEDEDE; }
.tbody td { min-width:10px; border:solid 1px #CCC; padding:2px; }
.tbody img { width:50px; }
.tbody span img { width:20px; }
.tbody .sep, .thead .sep { background:yellow; width:10px; }

.saved { background:#BFFFDB!important; }
.nowrap { white-space:nowrap; }
.textnb { width:50px; }
</style>
CSS;

print "<h1>Paramètres articles</h1>";

$active=array(0=>"<span style=\"color:orange\"><img src=\"images/icon/orange.png\"> Inactif</span>",1=>"<span style=\"color:green\"><img src=\"images/icon/green.png\"> Actif</span>",2=>"<span style=\"color:red\"><img src=\"images/icon/red.png\"> Supprimé</span>",3=>"Archivé");
$commandable=array(0=>"Indiponible sans prix",1=>"Commandable",2=>"Indisponible");
$paysfabrication=array(0=>"France",1=>"Europe",2=>"Asie",3=>"USA");
$payss=$DB_site2->query("SELECT paysid,libelle FROM pays");
while($p=$DB_site2->fetch_array($payss)){
    $pays[$p['paysid']]=$p['libelle'];
}

$showfields=array("nbr_temperature"=>true,"plage_temperature"=>false,"capacite_bouteilles"=>true,"recherche_usage"=>false,
"sep","article_deselection"=>false,"montage"=>false,"montage_heures"=>false,"pro"=>false,
"sep","recherche_finition"=>false,"recherche_typepose"=>false,"esthetique"=>false,
"sep","version_porte"=>true,"sensporte"=>false,"recherche_typeporte"=>false,"hauteur_porte"=>false,"serrure"=>true,"serrure_info"=>false,
"sep","coloris"=>true,"recherche_coloris"=>false,
"sep","recherche_moderangement"=>false,
"sep","temp_instal_min"=>true,"temp_instal_max"=>true,"recherche_emplacement"=>false,
"sep","paysid"=>true,"paysfabrication"=>true);


$fields=array();
$tableinfo=array();
$req=$DB_site2->query("SHOW FIELDS FROM article");
while($f=$DB_site2->fetch_array($req)){
    $fields[$f['Field']]=$f['Type'];
}
$req=$DB_site2->query("SHOW TABLES WHERE Tables_in_$DBname LIKE 'recherche_%'");
while($tbl=$DB_site2->fetch_array($req)){
    $pri=null;
    $infos=$DB_site2->query("SHOW FIELDS FROM ".$tbl[0]);
    while($tblinfo=$DB_site2->fetch_array($infos)){
	if($tblinfo['Key']=="PRI" || is_null($pri)) $pri=$tblinfo['Field'];
	if(substr($tblinfo['Field'],-2)=="FR") $lib=$tblinfo['Field'];
    }
    
    if(!is_null($pri)){
	$values=$DB_site2->query("SELECT * FROM ".$tbl[0]);
	while($value=$DB_site2->fetch_array($values)){
	    $tableinfo[$tbl[0]][$value[$pri]]=$value[$lib];
	}
    }
    $fields[$tbl[0]]="table(".$tbl[0].")";
}
$tableinfo['article_deselection'][0]="Sélection";
$fields['article_deselection']="table(article_deselection)";

/*foreach($tableinfo as $table=>$infos){
    $i=0;
    foreach($infos as $info){
	$sp[$i]=explode(" ",strtolower($info));
	$i++;
    }
    $inter=call_user_func_array("array_intersect",$sp);
    var_dump($inter);
}*/

// enregistrement d'un article
if(isset($_POST['artid'])){
    $fieldnames=array_keys($fields);
    foreach($_POST as $name=>$value){
	if(in_array($name,$fieldnames)){
	    $result=save_field($_POST['artid'],$name,$value,$fields[$name]);
	}
    }
    modifhistory($_POST);
}


$catids=array(1,2,3,7,265,79);
$categorie=new categorie($DB_site2);
$allcatids=$categorie->getsubcatids($catids);
$categorie->setparam("subcatids",$allcatids);
$categorie->setparam("bonsplans",array("selection"));
$categorie->setparam("site_code","base");
$categorie->setparam("active",array(1,0));
$marqueids=$categorie->getmarques();

$listemarques=array(""=>"<option></option>");
foreach($marqueids as $param){
    $marque=new marque($DB_site2,$param->id);
    $listemarques[$marque->libelle]="<option value=\"".$marque->marqueid."\"".($_GET['marqueid']==$marque->marqueid?" selected":"").">".$marque->libelle." (".$param->qte.")</option>";
}


print "<form name=\"filter\" method=\"get\">
Marque : <select name=\"marqueid\" onchange=\"document.forms.filter.submit()\">".implode("",$listemarques)."</select>
</form>";
if(isset($_GET['marqueid'])){
    $categorie->setparam("marqueids",array(intval($_GET['marqueid'])));
    $marque=new marque($DB_site2,$_GET['marqueid']);
    
    if(!isset($_GET['pagenumber'])) $_GET['pagenumber'] = 1;
     $debut_url = '';
     $url_pagenav = '';
     foreach($_GET as $param => $value){
          if($param != 'pagenumber'){
               $url_pagenav .= empty($url_pagenav) ? '?' : '&';
               $url_pagenav .= $param.'='.$value;
          }
     }
    $perpage = 30;
    
    $params_page = array(
        'pagenumber' => $_GET['pagenumber'],
        'perpage'    => $perpage
    );
    
    $pagination = "<div class=\"pagination_new\">".getpagenav($categorie->nb_articles("artcode"), $url_pagenav, $params_page)."</div>";
    
    $paramsArticles = array(
        'debut' => ($_GET['pagenumber'] - 1) * $perpage,
        'nb'    => $perpage
    );
    
    $tabArticle = $categorie->articles("artcode", true, $paramsArticles);
    
    $sqlfields="";
    foreach($showfields as $name=>$ro){
	if($ro!=="sep"){
	    $headfields.="<td class=\"ftitle\"><div><span>".fieldtitle($name)."</span></div></td>";
	    if(substr($fields[$name],0,5)!="table") $sqlfields.=",".$name;
	}else
	    $headfields.="<td><div></div></td>";
    }
    
    print $pagination."<div id=\"theaddiv\">
      <table id=\"theadleft\" class=\"thead\">
      <tr>
	<td class=\"ftitle\"><div><span>Catégorie</span></div></td>
	<td class=\"ftitle\"><div><span></span></div></td>
	<td class=\"ftitle\"><div><span>Libelle</span></div></td>
      </tr>
      </table>
    
      <div id=\"theadrightdiv\">
	<table id=\"theadright\" class=\"thead\">
	<tr>
	  $headfields
	</tr>
	</table>
      </div>
    </div>";
    
    foreach($tabArticle as $artid){
	$article=$DB_site2->query_first("SELECT artid,artcode,active,commandable,catid,libelleFR,artcode_eurocave".$sqlfields." FROM article WHERE artid=".$artid);
	$catarticle=new categorie($DB_site2,$article['catid']);
	$history=$DB_site2->query_first("SELECT COUNT(*) FROM article_history WHERE artid=".$artid." AND date>".(time()-600)." AND `key` IN ('".implode("','",array_keys($showfields))."')");
	
	$tbodyleft.="<tr id=\"tr_".$artid."\" class=\"".($history[0]>0?"saved":"")."\">
	  <td>
	    ".$catarticle->libelle."
	    <br><br>".$active[$article['active']]."
	    <br>".$commandable[$article['commandable']]."
	  </td>
	  <td>
	    <img src=\"/configurations/".$host."/images/produits/br/".$artid.".jpg\">
	    <br>".$marque->libelle."
	    <br><a href=\"ajout_produit.php?artid=".$article['artid']."&prec=modif\" target=\"_blank\">".$article['artcode']."</a>
	    <br>".wordwrap($article['artcode_eurocave'],12,"<br>",true)."
	  </td>
	  <td>".$article['libelleFR']."<br><input type=\"button\" onclick=\"update_art(".$artid.")\" value=\"Valider\"></td>
	 </tr>";
	  
	 $tbodyright.="<tr class=\"".($history[0]>0?"saved":"")."\">";
	 foreach($showfields as $name=>$ro){
	    if($ro!=="sep"){
		$innerhtml=display_field($artid,$name,$article[$name],$fields[$name],$ro);
		$tbodyright.="<td".(strpos($innerhtml,"input")>-1?" class=\"nowrap\"":"").">".$innerhtml."</td>";
	    }else
		$tbodyright.="<td class=\"sep\">&nbsp;</td>";
	 }
	 
	 $tbodyright.="</tr>";
     }
     
    print "<div id=\"tbodyleftdiv\">
	<table id=\"tbodyleft\" class=\"tbody\">
	$tbodyleft
	</table>
      </div>
      <div id=\"tbodyrightdiv\" onscroll=\"scrolldiv()\">
	<table id=\"tbodyright\" class=\"tbody\">
	$tbodyright
	</table>
      </div><div class=\"clear\" style=\"margin-bottom: 25px;\"></div>".$pagination;
}

foreach($showfields as $name=>$ro){
    if(!$ro) $jsfield[]="'".$name."'";
}
$jsfield=implode(",",$jsfield);

$scrolltop="";
if(isset($_POST['scrolltop'])) 
  $scrolltop="document.getElementById('tbodyrightdiv').scrollTop=".intval($_POST['scrolltop']).";
  scrolldiv();";

//Reconstruction de l'url pour formulaire
$url_form = '';
foreach($_GET as $param => $value){
     $url_form .= empty($url_form) ? '?' : '&';
     $url_form .= $param.'='.$value;
}
     
print <<<HTML
<form name="update" method="post" action="$url_form">
  <input type="hidden" name="artid">
  <input type="hidden" name="scrolltop">
</form>

<script language="javascript">
var fields=new Array($jsfield);
function update_art(artid){
    document.getElementsByName('artid')[0].value=artid;
    document.getElementsByName('scrolltop')[0].value=document.getElementById('tbodyrightdiv').scrollTop;
    
    for(i=0;i<fields.length;i++){
	var inputs=document.getElementsByName(fields[i]+"["+artid+"]");
	var value=undefined;
	if(inputs[0].nodeName=="INPUT"){
	    switch(inputs[0].type){
	      case "radio":
	      for(j=0;j<inputs.length;j++){ 
		  if(inputs[j].checked) value=inputs[j].value;
	      }
	      if(value!=undefined){
		var sinput=document.createElement("input");
		sinput.type="hidden";
		sinput.name=fields[i];
		sinput.value=value;
		document.forms.update.appendChild(sinput);
	      }
	      break;
	      
	      case "checkbox":
	      for(j=0;j<inputs.length;j++){
		  var sinput=document.createElement("input");
		  sinput.type="hidden";
		  sinput.name=fields[i]+"[]";
		  sinput.value=inputs[j].checked ? inputs[j].value : 0;
		  document.forms.update.appendChild(sinput);
	      }
	      break;
	      case "text":
	      case "hidden":
	      var sinput=document.createElement("input");
	      sinput.type="hidden";
	      sinput.name=fields[i];
	      sinput.value=inputs[0].value;
	      document.forms.update.appendChild(sinput);
	      break;
	    }
	}

	if(inputs[0].nodeName=="SELECT"){
	    var value=inputs[0].options[inputs[0].selectedIndex].value;
	    var sinput=document.createElement("input");
	    sinput.type="hidden";
	    sinput.name=fields[i];
	    sinput.value=value;
// 	    console.debug(fields[i]);
	    document.forms.update.appendChild(sinput);
	}
    }
    document.forms.update.submit();
}

function colwidth(){
    var td=document.getElementById('tbodyleft').getElementsByTagName('tr')[0].getElementsByTagName('td');
    for(i=0;i<td.length;i++){
	document.getElementById('theadleft').getElementsByTagName('td')[i].style.width=(td[i].offsetWidth-4)+'px';
    }
    
    var td=document.getElementById('tbodyright').getElementsByTagName('tr')[0].getElementsByTagName('td');
    for(i=0;i<td.length;i++){
	document.getElementById('theadright').getElementsByTagName('td')[i].style.width=(td[i].offsetWidth-4)+'px';
    }
    document.getElementById('theadright').style.width=document.getElementById('tbodyright').offsetWidth+'px';
    document.getElementById('tbodyrightdiv').style.height=(window.innerHeight-document.getElementById('tbodyrightdiv').offsetTop)+'px';
    document.getElementById('tbodyleftdiv').style.height=document.getElementById('tbodyrightdiv').style.height;
    
    var tbodyleft=document.getElementById('tbodyleft');
    var tr=document.getElementById('tbodyright').getElementsByTagName('tr');
    for(i=0;i<tr.length;i++){
	var leftheight=tbodyleft.getElementsByTagName('tr')[i].offsetHeight;
	var h=Math.max(leftheight,tr[i].offsetHeight);
	tbodyleft.getElementsByTagName('tr')[i].style.height=h+'px';
	tr[i].style.height=h+'px';
    }
}

function scrolldiv(){
    var scrollleft=document.getElementById('tbodyrightdiv').scrollLeft;
    document.getElementById('theadright').style.marginLeft=(-scrollleft)+'px';
    
    document.getElementById('tbodyleft').style.marginTop=(-document.getElementById('tbodyrightdiv').scrollTop)+'px';
}


window.addEventListener( "load", colwidth, false );
$scrolltop
</script>
HTML;

include "./footer.php";


function display_field($artid,$name,$value,$fieldinfo,$readonly){
    global $template;
    global $DB_site2;
    global $tableinfo;

    $match=preg_match("/([a-z0-9]+)\(([a-z0-9_',]+)\)/i",$fieldinfo,$m);
    if($match){
	$fieldtype=$m[1];
	$fieldtypeinfo=$m[2];
    }else
	$fieldtype=$fieldinfo;

    switch($fieldtype){
	case "varchar":
	    if($readonly)
	      return $template->translate($value);
	    else
	      return selecttranslate($name."[".$artid."]","/article/".$name,$value);
	break;
	
	case "longtext":
	if($readonly)
	   return  $value;
	break;
	
	case "tinyint":
	case "smallint":
	case "int":
	case "float":
	case "enum":

	$function="display_".$name;
	if(function_exists($function))
	   return $function($artid,$value,$readonly);
	   
	if($readonly)
	  return $value;
	else
	  return "<input type=\"text\" name=\"".$name."[".$artid."]\" value=\"".$value."\" class=\"textnb\">";
	
	break;
	
	case "table":

	if($fieldtypeinfo=="article_deselection"){
	    $article=$DB_site2->query_first("SELECT COUNT(*) FROM article_deselection WHERE artid=".$artid);
	    return display_article_deselection($artid,$article[0],$readonly);
	}else{
	    $req=$DB_site2->query("SELECT * FROM article".$fieldtypeinfo." WHERE artid=".$artid);
	    while($article_val=$DB_site2->fetch_array($req)){
		foreach($article_val as $key=>$val){
		    if(!is_int($key) && $key!="artid") $value[]=$val;
		}
	    }
	}
	
	$return="";
	foreach($tableinfo[$fieldtypeinfo] as $key=>$lib){
	    $return.="<br><input type=\"checkbox\" name=\"".$name."[".$artid."]\" value=\"".$key."\"".(!is_null($value) && in_array($key,$value)?" checked":"").">".$lib;
	}
	return $return;
	break;
    }
}

function display_serrure_info($artid,$value,$ro=true){
    $values=array(0=>"Pas de serrure",1=>"Serrure",2=>"Serrure en option");
    if($ro) return $values[$value];
    
    $return="<select name=\"serrure_info[$artid]\">";
    foreach($values as $key=>$txt)
	$return.="<option value=\"".$key."\"".($key==$value?" selected":"").">".$txt."</option>";
    $return.="</select>";
    
    return $return;
}

function display_sensporte($artid,$value,$ro=true){
    $values=array(0=>"Porte à droite",1=>"Porte à gauche",2=>"Porte réversible");
    if($ro) return $values[$value];
    
    $return="<select name=\"sensporte[$artid]\">";
    foreach($values as $key=>$txt)
	$return.="<option value=\"".$key."\"".($key==$value?" selected":"").">".$txt."</option>";
    $return.="</select>";
    
    return $return;
}

function display_paysfabrication($artid,$value,$ro=true){
    global $paysfabrication;
    if($ro) return $paysfabrication[$value];
}

function display_paysid($artid,$value,$ro=true){
    global $pays;
    if($ro) return $pays[$value];
}

function display_esthetique($artid,$value,$ro=true){
    if($ro){
    
    }else
      return "<input type=\"checkbox\" name=\"esthetique[".$artid."]\" value=\"1\"".($value==1?" checked":"").">";
}

function display_article_deselection($artid,$value,$ro=true){
    if($ro){
    
    }else
      return "<input type=\"checkbox\" name=\"article_deselection[".$artid."]\" value=\"1\"".($value==0?" checked":"").">";
}

function display_pro($artid,$value,$ro=true){
    $values=array(1=>"Particulier",3=>"Pro + part");
    if($ro){
      $return=$values[$value];
    }else{
      $return="<select name=\"pro[".$artid."]\">";
      foreach($values as $val=>$txt)
	$return.="<option value=\"".$val."\"".($value==$val?" selected":"").">".$txt."</option>";
      $return.="</select>";
    }
    return $return;
}

function display_montage($artid,$value,$ro=true){
    return "<input type=\"checkbox\" name=\"montage[".$artid."]\" value=\"1\"".($value==1?" checked":"").">";
}

function save_field($artid,$name,$value,$fieldinfo){
    global $template;
    global $DB_site2;
    global $tableinfo;

    $match=preg_match("/([a-z0-9]+)\(([a-z0-9_',]+)\)/i",$fieldinfo,$m);
    if($match){
	$fieldtype=$m[1];
	$fieldtypeinfo=$m[2];
    }else
	$fieldtype=$fieldinfo;

    switch($fieldtype){
	case "varchar":
	case "enum":
	case "longtext":
	return $DB_site2->query("UPDATE article SET `".$name."`='".mysqli_real_escape_string($DB_site2->link_id,stripslashes($value))."' WHERE artid=".$artid);
	break;
	
	case "tinyint":
	case "smallint":
	case "int":
	if(is_array($value)) $value=$value[0];
	return $DB_site2->query("UPDATE article SET `".$name."`='".intval($value)."' WHERE artid=".$artid);
	break;
	
	case "float":
	return $DB_site2->query("UPDATE article SET `".$name."`='".floatval($value)."' WHERE artid=".$artid);
	break;
	
	case "table":
	if($fieldtypeinfo=="article_deselection"){
	    $exist=$DB_site2->query_first("SELECT COUNT(*) FROM article_deselection WHERE artid=".$artid);
	    if($exist[0]>=1 && $value[0]==1)
		return $DB_site2->query("DELETE FROM article_deselection WHERE artid=".$artid);
	    if($exist[0]==0 && $value[0]==0)
		return $DB_site2->query("INSERT INTO article_deselection SET artid=".$artid);

	}else{
	    $field="";
	    $infos=$DB_site2->query("SHOW FIELDS FROM article".$fieldtypeinfo);
	    while($f=$DB_site2->fetch_array($infos))
		if($f['Field']!="artid") $field=$f['Field'];
	    
	    $DB_site2->query("DELETE FROM article".$fieldtypeinfo." WHERE artid=".$artid);
	    if(!is_array($value)) $value=array($value);
	    foreach($value as $v)
		  if($v>0) $DB_site2->query("INSERT INTO article".$fieldtypeinfo." SET `".$field."`=".intval($v).", artid=".$artid);
	}
	break;
    }
}

function modifhistory($data){
	global $DB_site2;
	global $user_info;
	$nohistory=array("old","infos","mq2","quitter","modif","action2","artid","catid","scrolltop","marqueid");
	if(!array_key_exists("artid",$data)) return false;

	$artid=$data['artid'];
	if($artid=="") return false;
  
	foreach($data as $key=>$value){
	      if(is_array($value)) $value=mysqli_real_escape_string($DB_site2->link_id,trim(str_replace("array","",var_export($value,true))));
	      if(!in_array($key,$nohistory)) $DB_site2->query("INSERT INTO article_history SET artid=$artid, utilisateur='".$user_info['username']."', date=".time().", `key`='$key', value='".mysqli_real_escape_string ($DB_site2->link_id,$value)."'");
	}
	
}

function fieldtitle($name){
    $titles=array("nbr_temperature"=>"Nb temp.","plage_temperature"=>"Plage temp.","capacite_bouteilles","Capacité",
      "article_deselection"=>"Sélection","serrure_info"=>"Recherche serrure","sensporte"=>"Sens porte",
      "paysid"=>"Pays fabrication","paysfabrication"=>"Origine fabrication","pro"=>"Site pro");
      
    if(array_key_exists($name,$titles)) return $titles[$name];
    
    return ucfirst(str_replace("_"," ",$name));
}


?>
