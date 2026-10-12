<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

include("../class/showroom.class.php");

$filter_langue=array_diff($langues,$langue_access)!=array();

print "<h1>Showroom</h1>";

$showroom=new showroom($DB_site2,"FR");
$showroom->load(1);
// $showroom->create_positions();
if(isset($_POST['action'])) {
    if($_POST['action']=="update") {
	$links=explode(",",$_POST['links']);
	$sr_article=new showroom_article($DB_site2,"FR");
	$sr_article->update($_POST['artid'],$_POST['description'],$links);
	$action="list";
    }
    
    if($_POST['action']=="position") {
	$sr_article=new showroom_article($DB_site2,"FR");
	$sr_article->order_position($_POST['artid'],$_POST['sens']);
	$action="list";
    }
    
    if($_POST['action']=="del") {
	$sr_article=new showroom_article($DB_site2,"FR");
	$sr_article->delete($_POST['artid']);
	$action="list";
    }
    
    if($_POST['action']=="showroom") {
	$showroom->titles=$_POST['title'];
	$showroom->contenus=$_POST['contenu'];
	$showroom->metatitles=$_POST['ref_title'];
	$showroom->metadescriptions=$_POST['ref_description'];
	$showroom->update();
	$action="list";
    }
}

$visible=array(0=>"<span style=\"color:red;\">Invisible</span>",1=>"Visible",2=>"<span style=\"color:red;\">Supprimé</span>");

if(isset($_GET['action'])) $action=$_GET['action'];
if(is_null($action)) $action="list";



switch($action) {
  case "list":
  print "<table class=\"onglets\"><tr>";
  foreach($langues as $langue) {
      $flag=$langue;
      if($langue=="EN") $flag="GB";
      if($langue=="SP") $flag="ES";
      print "<td id=\"onglet$langue\" class=\"onglet\" onclick=\"switchonglet('$langue')\">$langue <img src=\"images/drapeaux/br/$flag.gif\"></td>";
  }
  print "</tr></table>";
  
  print "<form method=\"post\">
  <input type=\"hidden\" name=\"action\" value=\"showroom\">";
  
  foreach($showroom->contenus as $langue=>$contenu) {
      $readonly=!in_array($langue,$langue_access);
      
      print "<div id=\"showroom$langue\">
	<p>Titre $langue : ".($readonly?$showroom->titles[$langue]."<hr>":"<input type=\"text\" name=\"title[$langue]\" value=\"".$showroom->titles[$langue]."\" size=\"60\">")."</p>
	<p>Contenu $langue &nbsp; <img src=\"images/societe.com.gif\" width=\"20\" align=\"absmiddle\"> <small>Mettre [articles] pour afficher les article</small></p>
	".($readonly?$contenu."<hr>":"<textarea name=\"contenu[$langue]\" class=\"ckeditor\">$contenu</textarea>")."
	<p>Méta title :</p>
	".($readonly?$showroom->metatitles[$langue]."<hr>":"<input type=\"text\" name=\"ref_title[$langue]\" value=\"".$showroom->metatitles[$langue]."\" size=\"50\">")."
	<p>Méta description :</p>
	".($readonly?$showroom->metadescriptions[$langue]:"<textarea name=\"ref_description[$langue]\" cols=\"50\">".$showroom->metadescriptions[$langue]."</textarea>")."
	<p align=\"center\"><input type=\"submit\" value=\"Mettre à jour\"></p>
      </div>";
  }
  
  print "</form>";
  
  
  
  print "<h2>Articles</h2>";
  
  if(!$filter_langue) print "<p><a href=\"showroom.php?action=ajouter\">Ajouter un article</a></p>";
  print "<table>";

  $catarticles=$showroom->articles(true);
  ksort($catarticles);
  foreach($catarticles as $catid=>$articles){
      $categorie=$DB_site2->query_first("SELECT libelleFR FROM categorie WHERE catid=".$catid);
      ksort($articles);
      print <<<HTML
	<tr>
	  <td colspan="3"><h3>$categorie[libelleFR]</h3></td>
	</tr>
HTML;

      foreach($articles as $sr_article) {
	  $article=$DB_site2->query_first("SELECT libelleFR,artcode FROM article WHERE artid=".$sr_article->artid);
	  $artid=$sr_article->artid;
	  $description=$sr_article->description;
	  
	  $delbtn=($filter_langue?"":"<a href=\"javascript:del($artid)\"><img src=\"images/supprimer.gif\"></a>");
	  
	  $position_btn="";
	  if($sr_article->position>0) $position_btn.="<a href=\"javascript:position(".$sr_article->artid.",'up')\"><img src=\"images/monter.gif\"></a>";
	  if($sr_article->position<count($articles)-1) $position_btn.="<a href=\"javascript:position(".$sr_article->artid.",'down')\"><img src=\"images/descendre.gif\"></a>";
	  print <<<HTML
	  <tr>
	    <td>
	      $position_btn
	    </td>
	    <td>
	      <img src="../configurations/$host/images/produits/$artid.jpg" width="100">
	    </td>
	    <td>
	      $article[libelleFR]
	      <br><b>$article[artcode]</b>
	      <br>$description
	    </td>
	    <td>
	      <a href="showroom.php?action=modif&amp;artid=$artid"><img src="images/editer.gif"></a> 
	      $delbtn
	    </td>
	  </tr>
HTML;
  }
  }
  
  print "</table>
  <form action=\"showroom.php\" name=\"delform\" method=\"post\">
  <input type=\"hidden\" name=\"action\" value=\"del\">
  <input type=\"hidden\" name=\"artid\">
  </form>
  <form name=\"positionform\" method=\"post\">
  <input type=\"hidden\" name=\"action\" value=\"position\">
  <input type=\"hidden\" name=\"artid\">
  <input type=\"hidden\" name=\"sens\">
  </form>";
      
  break;
  
  case "ajouter":
  $sr_article=new showroom_article($DB_site2,"FR");
  $links="";
  $htmlarticle="";
  $srcartid="<input type=\"text\" onkeyup=\"srcartid(this.value)\">";
  $submittxt="Ajouter";
  $htmllinks="";
  case "modif":
  if(isset($_GET['artid'])) {
    $sr=new showroom($DB_site2,"FR");
    $sr_article=$sr->article($_GET['artid']);
    $links=implode(",",$sr_article->links);
    $srcartid="";
    $submittxt="Modifier";

    $article=$DB_site2->query_first("SELECT libelleFR,artcode,artcode_eurocave,libelle AS marque,active FROM article INNER JOIN article_marque USING(artid) INNER JOIN marque USING(marqueid) WHERE artid=".$sr_article->artid);
    $htmlarticle="<table>
    <tr style=\"background-color:#FFAAAA;\">
      <td><img src=\"/configurations/".$host."/images/produits/br/".$sr_article->artid.".jpg\" width=\"80\"></td>
      <td style=\"border-bottom:solid thin #cccccc;\">".$article['marque']."<br>".$article['artcode']."<br>".$article['artcode_eurocave']."<br>".$visible[$article['active']]."</td>
      <td style=\"border-bottom:solid thin #cccccc;\"><b>".$article['libelleFR']."</b></td>
    </tr>
    </table>";
    
    foreach($sr_article->links as $link) {
	$article=$DB_site2->query_first("SELECT artcode FROM article WHERE artid=".intval($link));
	if($article) $htmllinks.="<span id=\"link$link\" class=\"link\">".$article['artcode']." <img src='images/supprimer.gif' onclick='rmlink(".$link.")'></span>";
    }
  }
  
  $descriptions="";
  foreach($langues as $l)
      if(in_array($l,$langue_access))
	  $descriptions.="<tr><td>Description $l&nbsp;:</td><td><textarea name=\"description[$l]\" class=\"ckeditor\">".$sr_article->descriptions[$l]."</textarea></td></tr>"; 
      else
	  $descriptions.="<tr><td>Description $l&nbsp;:</td><td>".$sr_article->descriptions[$l]."</td></tr>";
	  
  if(!$filter_langue) $trlinks='<tr><td>Articles même gamme&nbsp;:</td><td><input type="text" onkeyup="srclink(this.value)"><span id="links">$htmllinks</span><div id="resultlinks"></div></td></tr>';
  
  print <<<HTML
  <form action="showroom.php" method="post">
  <input type="hidden" name="action" value="update">
  <input type="hidden" name="artid" value="$sr_article->artid">
  <input type="hidden" name="links" value="$links">
  <table>
  <tr><td>Article :</td><td>$srcartid<div id="results">$htmlarticle</div></td></tr>
  $descriptions
  $trlinks
  <tr><td colspan="2"><input type="button" value="Annuler" onclick="document.location='showroom.php'"> <input type="submit" value="$submittxt"></td></tr>
  </table>
  </form>
HTML;
  break;

}

print <<<JS
  <script language="javascript">
  var resultdiv="results";
  var selectfct="selectprod";
  function srclink(src) {
      if(src.length<=3) return;
      resultdiv="resultlinks";
      selectfct="selectlink";
      var url="marketplace/src.php?txt="+src
      ajaxrequest(url,srcCallback);
      document.getElementById(resultdiv).innerHTML='<img src="/configurations/www.ma-cave-a-vin.fr/images/templates/ajax_wait.gif">';
  }
  
  function srcartid(src){
      if(src.length<=3) return;
      resultdiv="results";
      selectfct="selectprod";
      var url="marketplace/src.php?txt="+src
      ajaxrequest(url,srcCallback);
      document.getElementById(resultdiv).innerHTML='<img src="/configurations/www.ma-cave-a-vin.fr/images/templates/ajax_wait.gif">';
  }
  
  function srcCallback(httprequest){
      var xml=httprequest.responseXML.documentElement;
      var html='';
  
      var vistxt=new Array();
      vistxt[0]='<br><span style="color:red;">Invisible</span>';
      vistxt[1]='<br>Visible';
      if(xml.getElementsByTagName('prod')[0]){
	  html+='<table width="100%" style="border:solid thin #808080;"><tr>';
	  for(i=0;i<xml.getElementsByTagName('prod').length;i++){
		var description=xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
		var id=xml.getElementsByTagName('prod')[i].getAttribute('id');
		var artid=xml.getElementsByTagName('prod')[i].getAttribute('artid');
		var actif=xml.getElementsByTagName('prod')[i].getAttribute('active');
		var stock=xml.getElementsByTagName('prod')[i].getAttribute('stock');
		var marque=xml.getElementsByTagName('prod')[i].getAttribute('marque');
		var ref=xml.getElementsByTagName('prod')[i].getAttribute('ref');
		var lot=xml.getElementsByTagName('prod')[i].getAttribute('lot');
		var ventes=xml.getElementsByTagName('prod')[i].getAttribute('ventes');
		var ventes1an=xml.getElementsByTagName('prod')[i].getAttribute('ventes1an');
		var stock='Pas en stock';
		if(xml.getElementsByTagName('prod')[i].getAttribute('stock')=='1') stock='En stock : '+xml.getElementsByTagName('prod')[i].getAttribute('stockqte');
		if(actif<2) html+='<tr id="tr_'+artid+'"><td><img src="/configurations/www.ma-cave-a-vin.fr/images/produits/br/'+artid+'.jpg" width="80"></td><td style="border-bottom:solid thin #cccccc;">'+marque+'<br>'+id+'<br>'+ref+'<br>'+vistxt[actif]+'</td><td style="border-bottom:solid thin #cccccc;"><b>'+description+'</b><br>Ventes : '+ventes+'<br>Ventes 1 an : '+ventes1an+'<br>'+stock+'</td><td style="border-bottom:solid thin #cccccc;"><a href="javascript:'+selectfct+'('+artid+',\''+id+'\')">Sélectionner</a></td></tr>';
	  }
	  html+='</table>';
      }else
	  html='Pas de résultat';

      document.getElementById(resultdiv).innerHTML=html;
  }
  
  function selectprod(artid) {
      document.getElementsByName('artid')[0].value=artid;
      var tr=document.getElementById('results').getElementsByTagName('tr');
      for(i=0;i<tr.length;i++)
	if(tr[i].id!='tr_'+artid)
	  tr[i].style.display='none';
	else
	  tr[i].style.backgroundColor='#FFAAAA';
  }
  
  function selectlink(artid,artcode) {
      var linkexist=false;
      var links=document.getElementsByName('links')[0].value.split(",");
      for(i=0;i<links.length;i++)
	  if(links[i]==artid) linkexist=true;
      
      if(!linkexist) {
	  var span=document.createElement('span');
	  span.id="link"+artid;
	  span.innerHTML=artcode+" <img src='images/supprimer.gif' onclick='rmlink("+artid+")'>";
	  span.className='link';
	  document.getElementById('links').appendChild(span);
	  links.push(artid);
	  if(links[0]=="") links.splice(0,1);
	  document.getElementsByName('links')[0].value=links.join(",");
      }
      document.getElementById('resultlinks').innerHTML="";
  }
  
  function rmlink(artid) {
      var links=document.getElementsByName('links')[0].value.split(",");
      for(i=0;i<links.length;i++)
	  if(links[i]==artid) links.splice(i,1);
	  
      document.getElementsByName('links')[0].value=links.join(",");
      document.getElementById('links').removeChild(document.getElementById('link'+artid));
	  
  }
  
  function del(artid) {
      if(!confirm("Etes vous sur de vouloir effacer cet article ?")) return;
      
      var delform=document.forms.delform;
      document.getElementsByName('artid')[0].value=artid;
      delform.submit();
  }
  
  function position(artid,sens) {
      var position_form=document.forms.positionform;
      var inputs=position_form.getElementsByTagName('input');
      for(i=0;i<inputs.length;i++) {
	  if(inputs[i].name=='artid') inputs[i].value=artid;
	  if(inputs[i].name=='sens') inputs[i].value=sens;
      }
      position_form.submit();
  }
  
  var langues=new Array('FR','EN','SP');
  function switchonglet(langue){
    for(i=0;i<langues.length;i++){
      document.getElementById('showroom'+langues[i]).style.display='none';
      document.getElementById('onglet'+langues[i]).className='onglet';
    }
    
    if(langue!=undefined){
      document.getElementById('showroom'+langue).style.display='block';
      document.getElementById('onglet'+langue).className='onglet_on';
    } 
  }
  </script>

JS;

print <<<CSS
<style>
.link { border:solid 1px #CCC; margin:2px; padding:2px; }
.link img { cursor:pointer; }
.onglets { margin: auto; text-align: center; }
.onglet { background-color: #CCCCCC; border: thin solid #888888; cursor: pointer; height: 20px; margin-left: 5px; text-align: center; width: 100px; }
.onglet_on { background-color:white; border: thin solid #888888; cursor: pointer; height: 20px; margin-left: 5px; text-align: center; width: 100px; }
#showroomFR, #showroomEN, #showroomSP { display:none; border:solid 1px #CCC; }
</style>
CSS;

include "./footer.php";

?>