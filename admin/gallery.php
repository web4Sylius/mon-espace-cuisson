<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

include("../class/gallery.class.php");

$filter_langue=array_diff($langues,$langue_access)!=array();

$photopath="../configurations/".$host."/images/gallery";
$gallerypath="images-gallery";

if(isset($_POST['action'])){
    switch($_POST['action']){
	case "add":
	$errors="";
	$new_gal=new gallery($DB_site2,"FR");
	$new_gal->utilisateur=$user_info['username'];
	$new_gal->type=$_POST['type'];
	$new_gal->type_id=$_POST['type_id'];
	foreach($langues as $l){
	    $new_gal->{"libelle".$l}=$_POST['libelle'.$l];
	    $new_gal->{"description".$l}=$_POST['description'.$l];
	    $new_gal->{"url".$l}=$_POST['url'.$l];
	}
	$new_gal->create($langues);
	if(isset($_FILES['photos'])){
	    foreach($_FILES['photos']['name'] as $id=>$name){
		$gal_photo=new gallery_photo($DB_site2,"FR");
		$gal_photo->photopath=$photopath;
		$gal_photo->utilisateur=$user_info['username'];
		$gal_photo->parent_id=$new_gal->id;
		$result=$gal_photo->upload_insert($name,$_FILES['photos']['tmp_name'][$id],$langues);
		if($result!=true) $errors.="Erreur ajout image ".$name."<br>";
	    }
	}
	break;
	
	case "modif":
	$modif_gal=new gallery($DB_site2,"FR",$_POST['id']);
	foreach($langues as $l){
	    if(in_array($l,$langue_access)) {
		$modif_gal->{"libelle".$l}=$_POST['libelle'.$l];
		$modif_gal->{"description".$l}=$_POST['description'.$l];
		$modif_gal->{"url".$l}=$_POST['url'.$l];
	    }
	}
	$modif_gal->update($langues);
	if(isset($_FILES['photos'])){
	    foreach($_FILES['photos']['name'] as $id=>$name){
		$gal_photo=new gallery_photo($DB_site2,"FR");
		$gal_photo->photopath=$photopath;
		$gal_photo->utilisateur=$user_info['username'];
		$gal_photo->parent_id=$modif_gal->id;
		$result=$gal_photo->upload_insert($name,$_FILES['photos']['tmp_name'][$id],$langues);
		if($result!=true) $errors.="Erreur ajout image ".$name."<br>";
	    }
	}
	$photolist=$modif_gal->load_photos();
	foreach($photolist as $photo){
	    foreach($langues as $l){
		if(in_array($l,$langue_access)) {
		    $photo->{"libelle".$l}=$_POST['photo'.$photo->id.$l];
		    $photo->{"link".$l}=$_POST['link'.$photo->id.$l];
		    $photo->{"legende".$l}=$_POST['legende'.$photo->id.$l];
		}
	    }
	    $photo->update($langues);
	}
	$modif_gal->reorder();
	break;

	case "del":
	$del_gal=new gallery($DB_site2,"FR",$_POST['id']);
	$del_gal->delete();
	break;
	
	case "delgalphoto":
	$delphoto=new gallery_photo($DB_site2,"FR",$_POST['delgalphotoid']);
	$parent=new gallery($DB_site2,"FR",$delphoto->parent_id);
	$delphoto->delete();
	$parent->reorder();
	break;
	
	case "movegalphoto":
	$modif_gal=new gallery($DB_site2,"FR",$_GET['id']);
	$modif_gal->photo_move($_POST['movegalphotoid'],$_POST['moveoffset']);
	break;
    }
}

$style=<<<HTML
<style>
#optcategorie { display:none; }
#optmarque { display:none; }
#photos { margin:10px 0; border:solid 1px black; }
#photos p { margin-left:10px; }
#photos table { border-collapse: separate; border-spacing:5px; }
#photos table td { border:solid 1px #888; }
#photos table td input { width:300px; }
.delphoto { margin-left:10px; cursor:pointer; }
.onphoto { position:absolute; width:20px; margin-left:0px; background:white; border:sold 1px #888; }
.onphoto .delphoto { display:block; margin:5px auto; }
.monter { display:block; margin:5px auto; cursor:pointer; }
.descendre { display:block; margin:5px auto; cursor:pointer; }
.thumb { width:300px; margin-right:10px; }
.tdlink { border-bottom:solid 1px black; }
.gallerytype { margin-top:10px; width:500px; border:solid 1px black; }
</style>
HTML;

$js=<<<HTML
<script language="javascript">
function selectGalleryType(type){
    document.getElementById('optcategorie').style.display='none';
    document.getElementById('optcategorie').name='';
    document.getElementById('optmarque').style.display='none';
    document.getElementById('optmarque').name='';
    if(document.getElementById('opt'+type)){
      document.getElementById('opt'+type).style.display='inline';
      document.getElementById('opt'+type).name='type_id';
    }
}

function delGallery(id){
    if(!confirm('Etes vous sur de vouloir supprimer cette galerie photo ?')) return;
    document.forms.delgallery.id.value=id;
    document.forms.delgallery.submit();
}

function addPhoto(){
    var photonb=Math.floor(Math.random()*10000000);
    var p=document.createElement('p');
    p.id='photo'+photonb;
    var input=document.createElement('input');
    input.type='file';
    input.name='photos[]';
    input.setAttribute('onchange','addPhoto()');
    var delphoto=document.createElement('img');
    delphoto.src='images/supprimer.gif';
    delphoto.className='delphoto';
    delphoto.setAttribute('onclick','delPhoto(\'photo'+photonb+'\')');
    p.appendChild(input);
    p.appendChild(delphoto);
    document.getElementById('photos').appendChild(p);
}

function delPhoto(elem){
    if(document.getElementById(elem)){
	var element = document.getElementById(elem);
	element.parentNode.removeChild(element);
    }
}

function delGalPhoto(id){
    if(!confirm('Etes vous sur de vouloir effacer cette photo ?')) return;
    
    document.getElementsByName('delgalphotoid')[0].value=id;
    document.forms.delgalphoto.submit();
}

function moveGalPhoto(id,offset){
    document.getElementsByName('movegalphotoid')[0].value=id;
    document.getElementsByName('moveoffset')[0].value=offset;
    document.forms.movegalphoto.submit();
}
</script>
<script src="./../ckeditor/ckeditor.js"></script>
HTML;

print $style.$js;

print "<h1>Galerie photo</h1>";


$gallery=new gallery($DB_site2,"FR");

if(!isset($_GET['action'])){
    if(!$filter_langue) print "<a href=\"gallery.php?action=add\">Ajouter une galerie</a>";
    $types=$gallery->getTypes();

    if($types==array())
      print "<p>Il n'y a pas de galerie photo</p>";
    else{
	$gindex=new gallery($DB_site2,"FR");
	$gindex->path=$gallerypath;
	$gindex->loadIndex();
	print "<table class=\"gallerytype\">
	<tr>
	   <td colspan=\"2\"><b>Index</b></td>
	</tr>
	<tr>
	   <td>".$gindex->libelle."</td>
	   <td></td>
	   <td></td>
	   <td><img src=\"images/editer.gif\" onclick=\"document.location='gallery.php?action=modif&id=".$gindex->id."'\"></td>
	</tr>
	<tr>
	    <td colspan=\"4\" class=\"tdlink\"><a href=\"".$gindex->getFullUrl()."\" target=\"_blank\">".$gindex->getFullUrl()."</a></td>
	</tr>
	</table>";

	foreach($types as $type){
	    $list=$gallery->getListByType($type);
	    if($type!="index"){
		print "<table class=\"gallerytype\">
		<tr><td colspan=\"2\"><b>".ucfirst($gallery->types[$type])."</b></td></tr>";
		foreach($list as $g)
		    print "<tr>
			<td>".$g->libelle."</td>
			<td>".$g->getTypeName()."</td>
			<td>".$g->nb_photos()." photo(s)</td>
			<td><img src=\"images/editer.gif\" onclick=\"document.location='gallery.php?action=modif&id=".$g->id."'\"> ".($filter_langue?"":"<img src=\"images/supprimer.gif\" onclick=\"delGallery(".$g->id.")\">")."<td>
		    </tr>
		    <tr>
			<td colspan=\"4\" class=\"tdlink\"><a href=\"/images-caves-a-vin/".$g->url.".htm\" target=\"_blank\">/".$gallerypath."/".$g->url.".htm</a></td>
		    </tr>";
		    
		print "</table>";
	    }

	    print "<form name=\"delgallery\" method=\"post\">
	    <input type=\"hidden\" name=\"action\" value=\"del\">
	    <input type=\"hidden\" name=\"id\" value=\"\">
	    </form>";
	}
    }
    
}else{
    switch($_GET['action']){
	case "add":
	$catids=$marqueids=array();
	$optcat="<option style=\"font-style:italic\">Choisir...</option>";
	$galcat=$gallery->getListByType("categorie");
	foreach($galcat as $g) $catids[]=$g->type_id;
	$req=$DB_site2->query("SELECT catid,libelleFR FROM categorie WHERE ".($catids!=array()?"catid NOT IN (".implode(",",$catids).")":1)." ORDER BY parentid,position");
	while($categorie=$DB_site2->fetch_array($req)) $optcat.="<option value=\"".$categorie['catid']."\">".$categorie['libelleFR']."</option>";
	
	$optmar="<option style=\"font-style:italic\">Choisir...</option>";
	$galmar=$gallery->getListByType("marque");
	foreach($galmar as $g) $marqueids[]=$g->type_id;
	$req=$DB_site2->query("SELECT marqueid,libelle FROM marque WHERE ".($marqueids!=array()?"marqueid NOT IN (".implode(",",$marqueids).")":1)." ORDER BY libelle");
	while($marque=$DB_site2->fetch_array($req)) $optmar.="<option value=\"".$marque['marqueid']."\">".$marque['libelle']."</option>";
	
	$opttypes="<option></option>";
	foreach($gallery->types as $gtype=>$name){
	    $opttypes.="<option value=\"$gtype\">$name</option>";
	    
	}
	    
	$tdlibelle=$tddescription=$tdurl="";
	foreach($langues as $l){
	    $tdlibelle.="<tr>
	      <td>Libellé $l :</td>
	      <td><input type=\"text\" name=\"libelle$l\" size=\"50\"></td>
	      </tr>";
	    
	    $tddescription.="<tr>
	      <td>Description $l :</td>
	      <td><textarea name=\"description$l\" class=\"ckeditor\">".$gal_modif->{"description".$l}."</textarea></td>
	      </tr>";
	      
	    $tdurl.="<tr>
	      <td>URL $l :</td>
	      <td>".$gallerypath."/<input type=\"text\" name=\"url$l\">.htm</td>
	      </tr>";
	}
	
	print "<h2>Ajouter une galerie</h2>
	<form action=\"gallery.php\" method=\"post\" enctype=\"multipart/form-data\">
	<input type=\"hidden\" name=\"action\" value=\"add\">
	<table>
	  <tr>
	    <td>Type :</td>
	    <td><select name=\"type\" onchange=\"selectGalleryType(this.value)\">$opttypes</select> <select id=\"optcategorie\">$optcat</select><select id=\"optmarque\">$optmar</select></td>
	  </tr>
	  $tdlibelle
	  $tddescription
	  $tdurl
	  </table>
	  <p>Photos : jpg affiché 325px x 243px proportion 4/3 <a href=\"javascript:addPhoto()\">Ajouter</a></p>
	  <div id=\"photos\">
	  <p id=\"photo1\"><input type=\"file\" name=\"photos[]\" onchange=\"addPhoto()\"><img src=\"images/supprimer.gif\" class=\"delphoto\" onclick=\"delPhoto('photo1')\"></p>
	  </div>
	  <input type=\"button\" value=\"Annuler\"> <input type=\"submit\" value=\"Ajouter\">
	</form>";
	break;
	
	case "modif":
	$gal_modif=new gallery($DB_site2,"FR",$_GET['id']);
	
	$tdlibelle=$tddescription=$tdurl="";
	foreach($langues as $l) {
	    $readonly=!in_array($l,$langue_access);
	    $flag=$l;
	    if($l=="EN") $flag="GB";
	    if($l=="SP") $flag="ES";
	    $tdlibelle.="<tr>
	      <td>Libellé <img src=\"images/drapeaux/br/$flag.gif\"> :</td>
	      <td>".($readonly?$gal_modif->{"libelle".$l}:"<input type=\"text\" name=\"libelle$l\" value=\"".$gal_modif->{"libelle".$l}."\" size=\"50\">")."</td>
	      </tr>";
	   

	    $tddescription.="
	    <tr>
		<td>Description <img src=\"images/drapeaux/br/$flag.gif\"> :</td>
		<td>".($readonly?$gal_modif->{"description".$l}:"<textarea name=\"description$l\" class=\"ckeditor\">".$gal_modif->{"description".$l}."</textarea>")."</td>
	      </tr>";
	    
	    $tdurl.="
	    <tr>
		  <td>URL <img src=\"images/drapeaux/br/$flag.gif\"> :</td>
		  <td>".$gallerypath."/".($readonly?$gal_modif->{"url".$l}:"<input type=\"text\" name=\"url$l\" value=\"".$gal_modif->{"url".$l}."\" size=\"50\">").".htm</td>
	    </tr>";
	}
	
	switch($gal_modif->type){
	    case "marque":
	    $marque=$DB_site2->query_first("SELECT libelle FROM marque WHERE marqueid=".intval($gal_modif->type_id));
	    $typename=$marque[0];
	    break;
	    
	    case "categorie":
	    $categorie=$DB_site2->query_first("SELECT libelleFR FROM categorie WHERE catid=".intval($gal_modif->type_id));
	    $typename=$categorie[0];
	    break;
	}
	
	$photos=$gal_modif->load_photos();
	$i=$col=0;
	foreach($photos as $photo){
	    $photo->photopath=$photopath;
	    $monter=$descendre="";
	    if($i>0) $monter="<img src=\"images/monter.gif\" class=\"monter\" onclick=\"moveGalPhoto(".$photo->id.",-1)\" title=\"Monter d'une position\">";
	    if($i<count($photos)-1) $descendre="<img src=\"images/descendre.gif\" onclick=\"moveGalPhoto(".$photo->id.",1)\" class=\"descendre\" title=\"Descendre d'une position\">";

	    $photolist.="<td>".($filter_langue?"":
		"<div class=\"onphoto\">
		  <img src=\"images/supprimer.gif\" class=\"delphoto\" onclick=\"delGalPhoto(".$photo->id.")\">
		  $monter
		  $descendre
		</div>")."
		<img src=\"/".$photo->getpath($gallerypath,325,243)."\">";

	    $size=$photo->getSize();
	    if(round($size[0]/$size[1],1)!=1.3) $photolist.="<br><span style=\"color:red;\">Proportion différente de 4/3</span>";
	    
	    foreach($langues as $l) {
		$readonly=!in_array($l,$langue_access);
		$photolist.="<br>légende $l : ".($readonly?$photo->{"legende".$l}:"<input type=\"text\" name=\"legende".$photo->id.$l."\" value=\"".$photo->{"legende".$l}."\">")."
		<br>libellé $l : ".($readonly?$photo->{"libelle".$l}:"<input type=\"text\" name=\"photo".$photo->id.$l."\" value=\"".$photo->{"libelle".$l}."\">")."
		<br>lien $l : ".($readonly?$photo->{"link".$l}:"<input type=\"text\" name=\"link".$photo->id.$l."\" value=\"".$photo->{"link".$l}."\">")."<br>";
	    }
	    
	    $photolist.="</td>";
	    $col++;
	    if($col==2){
		$photolist.="</tr><tr>";
		$col=0;
	    }
	    $i++;
	}

	if($gal_modif->type!="index")
	    $tdphoto="<div id=\"photos\">
	    <p>Photos :</p>
	    <table><tr>
	    $photolist
	    </tr></table>
	    ".($filter_langue?"":"<a href=\"javascript:addPhoto()\">Ajouter</a> : jpg affiché 325px x 243px proportion 4/3
	    <p id=\"photo1\"><input type=\"file\" name=\"photos[]\" onchange=\"addPhoto()\"><img src=\"images/supprimer.gif\" class=\"delphoto\" onclick=\"delPhoto('photo1')\"></p>")."
	    </div>";
	
	print "<h2>Modifier une galerie</h2>
	<form action=\"gallery.php\" method=\"post\" enctype=\"multipart/form-data\">
	<input type=\"hidden\" name=\"action\" value=\"modif\">
	<input type=\"hidden\" name=\"id\" value=\"".$gal_modif->id."\">
	<table>
	  <tr>
	    <td>Type :</td>
	    <td>".$gal_modif->type." ".$typename."</td>
	  </tr>
	  $tdlibelle
	  <tr><td>&nbsp;</td></tr>
	  $tddescription
	  <tr><td>&nbsp;</td></tr>
	  $tdurl
	</table>
	<a name=\"photos\"></a>
	$tdphoto
	<input type=\"button\" value=\"Annuler\" onclick=\"document.location='gallery.php'\"> <input type=\"submit\" value=\"Modifier\">
	</form>
	<form action=\"gallery.php?action=modif&id=".$gal_modif->id."#photos\" name=\"delgalphoto\" method=\"post\">
	<input type=\"hidden\" name=\"action\" value=\"delgalphoto\">
	<input type=\"hidden\" name=\"delgalphotoid\">
	</form>
	<form action=\"gallery.php?action=modif&id=".$gal_modif->id."#photos\" name=\"movegalphoto\" method=\"post\">
	<input type=\"hidden\" name=\"action\" value=\"movegalphoto\">
	<input type=\"hidden\" name=\"movegalphotoid\">
	<input type=\"hidden\" name=\"moveoffset\">
	</form>";
	break;
    }

}
  
include "./footer.php";


?>