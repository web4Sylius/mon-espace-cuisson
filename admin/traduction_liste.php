<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

if(!isset($_GET['langue'])) {
    $langue="EN";
    if(array_diff($langues,$langue_access)!=array()) $langue=$langue_access[0];
}else
    $langue=$_GET['langue'];
    
$types_page=array("categorie"=>"Catégorie","caract"=>"Caractéristiques","caractval"=>"Options","libelle"=>"Libellés produits","gamme"=>"Gammes produits",
"landing"=>"Landing pages","marques"=>"Marques","email"=>"Emails","conseil"=>"Conseils","institutionnel"=>"Pages institutionnelles",
"showroom"=>"Showroom","venteflash"=>"Vente flash","gallery"=>"Galerie photo","traduction"=>"Traductions");

$testmenu=new menu("");
$testmenu->set_access($user_info['admin_access']);

print "<h1>Traductions</h1>";

$pages=array("catalogue.php"=>"Catégories",
"caracteristique.php"=>"Caractéristiques",
"caracteristique.php"=>"Options",
"gammes.php?field=libelle"=>"Libellés produits",
"gammes.php"=>"Gammes produits",
"landing.php"=>"Landing pages",
"marques.php"=>"Marques",
"emails_clients.php"=>"E mails",
"conseil.php"=>"Conseils d'experts",
"institutionnel.php"=>"Pages institutionnelles",
"showroom.php"=>"Showroom",
"vente_flash.php"=>"Vente flash",
"gallery.php"=>"Galerie photo",
"traduction.php"=>"Traductions");

print "<h3>Liste des pages à vérifier</h3>";
print "<ul>";
foreach($pages as $url=>$nom) if($testmenu->has_access($url)) print "<li><a href=\"/admin/$url\" target=\"_blank\">$nom</a></li>";

print "</ul>";

print "<h3>Traductions manquantes</h3>";

print "Langue : <select onchange=\"document.location='traduction_liste.php?langue='+this.value\">";
foreach($langues as $l) if($l!="FR" && in_array($l,$langue_access)) print "<option value=\"$l\"".($langue==$l?" selected":"").">$l</option>";
print "</select>";

$check=check_trad($langue);
foreach($types_page as $tp=>$txt) {
    $ecount=count($check[$tp]);
    if($ecount>0) {
	print "<h3 onclick=\"showdiv('$tp');\"><img src=\"images/plus.jpg\" id=\"icon_$tp\"> $txt (".$ecount.")</h3>
	<ul id=\"$tp\" class=\"listdiv\">";
	if(isset($check[$tp]))
	  print "<li>".implode("</li><li>",$check[$tp])."</li>";
	print "</ul>";
    }
}

print <<<CSS
<style>
h3 { cursor:pointer; }
h3 img { vertical-align:middle; }
.listdiv { display:none; margin-left:20px; }
</style>
CSS;

print <<<JS
<script language="javascript">
function showdiv(id) {
    if(document.getElementById(id).style.display=="block"){
	document.getElementById(id).style.display="none";
	document.getElementById('icon_'+id).src="images/plus.jpg";
    }else{
	document.getElementById(id).style.display="block";
	document.getElementById('icon_'+id).src="images/moins.jpg";
    }
}
</script>
JS;

include "./footer.php";


function check_trad($langue,$page="all") {
    global $DB_site2;
    global $testmenu;
    
    $return=array();
    
    switch($page) {
	case "all":
	case "categorie":
	$categories=$DB_site2->query("SELECT catid,parentid,onglet,libelleFR,libelle$langue,description$langue FROM categorie WHERE 1");
	while($categorie=$DB_site2->fetch_array($categories)) {
	    $errors="";
	    if(trim($categorie['libelle'.$langue])=="") $errors.="libelle,";
	    if($categorie['parentid']==0 && $categorie['onglet']==1 && trim($categorie['description'.$langue])=="") $errors.="description,";
	    if($errors!="") $return["categorie"][]="<a href=\"modifier_categorie.php?catid=".$categorie['catid']."\">".$categorie['libelleFR']."</a> (".substr($errors,0,-1).")";
	}
	
	if(!$testmenu->has_access("modifier_categorie.php")) $return["categorie"]=array();
	if($page!="all") break;
	
	case "caract":
	$caracts=$DB_site2->query("SELECT * FROM caracteristique WHERE libelle$langue='' OR LENGTH(libelle$langue)<=3");
	while($caract=$DB_site2->fetch_array($caracts))
	    $return["caract"][]="<a href=\"ajouter_caracteristique.php?caractid=".$caract['caractid']."&action=insert\" target=\"_blank\">".$caract["libelleFR"]."</a>";
	
	if(!$testmenu->has_access("ajouter_caracteristique.php")) $return["caract"]=array();
	if($page!="all") break;
	
	case "caractval":
	$caractvals=$DB_site2->query("SELECT * FROM caracteristiquevaleur WHERE libelle$langue='' OR LENGTH(libelle$langue)<=3");
	while($caractval=$DB_site2->fetch_array($caractvals))
	    $return["caractval"][]="<a href=\"ajouter_caracteristique.php?caractid=".$caractval['caractid']."&action=insert\" target=\"_blank\">".$caractval["libelleFR"]."</a>";
	
	if(!$testmenu->has_access("ajouter_caracteristique.php")) $return["caractval"]=array();
	if($page!="all") break;
	
	case "libelle":
	$libelles=$DB_site2->query("SELECT libelleFR,libelle$langue,artcode,artid FROM article WHERE active IN ('0','1') AND libelle$langue='' GROUP BY libelleFR");
	while($libelle=$DB_site2->fetch_array($libelles))
	    $return["libelle"][]="<a href=\"gammes.php?field=libelle&ntrad=1&flangue=$langue\" target=\"_blank\">".$libelle['libelleFR']."</a>";
	
	if(!$testmenu->has_access("gammes.php")) $return["libelle"]=array();
	if($page!="all") break;
	
	case "gamme":
	$gammes=$DB_site2->query("SELECT gammeFR,gamme$langue,artcode,artid FROM article WHERE active IN ('0','1') AND gamme$langue='' GROUP BY gammeFR");
	while($gamme=$DB_site2->fetch_array($gammes))
	    $return["gamme"][]="<a href=\"gammes.php?field=gamme&ntrad=1&flangue=$langue\" target=\"_blank\">".$gamme['gammeFR']."</a>";
	
	if(!$testmenu->has_access("gammes.php")) $return["gamme"]=array();
	if($page!="all") break;
	
	case "landing":
	$landings=$DB_site2->query("SELECT id,landingid,type,libelleFR,libelle$langue,description$langue,linktxt$langue,ref_title$langue,ref_description$langue,url$langue,imglink$langue,imgtext$langue,footer$langue FROM landing WHERE 1");
	while($landing=$DB_site2->fetch_array($landings)) {
	    $errors="";
	    if($landing['libelle'.$langue]=="") $errors.="libelle,";
	    if($landing['description'.$langue]=="") $errors.="description,";
	    if($landing['linktxt'.$langue]=="") $errors.="texte lien,";
	    if($landing['ref_title'.$langue]=="") $errors.="meta title,";
	    if($landing['ref_description'.$langue]=="") $errors.="meta description,";
	    if($landing['url'.$langue]=="") $errors.="url,";
	    if($landing['imglink'.$langue]=="") $errors.="image,";
	    if($landing['imgtext'.$langue]=="") $errors.="texte image,";
	    if($landing['footer'.$langue]=="") $errors.="footer,";
	    if($errors!="") $return['landing'][]="<a href=\"landing.php?type=".$landing['type']."&landingid=".$landing['landingid']."\" target=\"_blank\">".$landing['libelleFR']."</a> (".substr($errors,0,-1).")";
	}
	
	if(!$testmenu->has_access("landing.php")) $return["landing"]=array();
	if($page!="all") break;
	
	case "marques":
	$marques=$DB_site2->query("SELECT marqueid,libelle,url$langue,type$langue FROM marque WHERE 1");
	while($marque=$DB_site2->fetch_array($marques)) {
	    $errors="";
	    if($marque['url'.$langue]=="") $errors.="url,";
	    if($marque['type'.$langue]=="") $errors.="type marque,";
	    if($errors!="") $return['marques'][]="<a href=\"marques.php?action=modifier&marqueid=".$marque['marqueid']."\" target=\"_blank\">".$marque['libelle']."</a> (".substr($errors,0,-1).")";
	}
	
	if(!$testmenu->has_access("marques.php")) $return["marques"]=array();
	if($page!="all") break;
	
	case "email":
	$emails=$DB_site2->query("SELECT emailid,libelle,sujet$langue,contenu$langue FROM emails_clients");
	while($email=$DB_site2->fetch_array($emails)) {
	    if($email['sujet'.$langue]=="" && $email['contenu'.$langue]=="") $return['email'][]="<a href=\"emails_clients.php?emailid=".$email['emailid']."\" target=\"_blank\">".$email['libelle']."</a>";
	}
	
	if(!$testmenu->has_access("emails_clients.php")) $return["email"]=array();
	if($page!="all") break;
	
	case "conseil":
	$conseils=$DB_site2->query("SELECT conseilid,libelleFR,libelle$langue,contenu$langue,ref_title$langue,ref_description$langue FROM conseil_part WHERE 1");
	while($conseil=$DB_site2->fetch_array($conseils)){
	    $errors="";
	    if($conseil['libelle'.$langue]=="") $errors.="libelle,";
	    if($conseil['libelle'.$langue]=="") $errors.="contenu,";
	    if($conseil['ref_title'.$langue]=="") $errors.="meta titre,";
	    if($conseil['ref_description'.$langue]=="") $errors.="meta description,";
	    if($errors!="") $return['conseil'][]="<a href=\"conseil.php?action=editer&conseilid=".$conseil['conseilid']."&i_langue=0\" target=\"_blank\">".$conseil['libelleFR']."</a> (".substr($errors,0,-1).")";
	}
	
	if(!$testmenu->has_access("conseil.php")) $return["conseil"]=array();
	if($page!="all") break;
	
	case "institutionnel":
	$conseils=$DB_site2->query("SELECT institutionnelid,libelleFR,libelle$langue,contenu$langue,ref_title$langue,ref_description$langue FROM institutionnel WHERE active=1");
	while($conseil=$DB_site2->fetch_array($conseils)){
	    $errors="";
	    if($conseil['libelle'.$langue]=="") $errors.="libelle,";
	    if($conseil['libelle'.$langue]=="") $errors.="contenu,";
	    if($conseil['ref_title'.$langue]=="") $errors.="meta titre,";
	    if($conseil['ref_description'.$langue]=="") $errors.="meta description,";
	    if($errors!="") $return['institutionnel'][]="<a href=\"institutionnel.php?action=editer&institutionnelid=".$conseil['institutionnelid']."&i_langue=0\" target=\"_blank\">".$conseil['libelleFR']."</a> (".substr($errors,0,-1).")";
	}
	
	if(!$testmenu->has_access("institutionnel.php")) $return["institutionnel"]=array();
	if($page!="all") break;
	
	case "showroom":
	include("../class/showroom.class.php");
	$sr=new showroom($DB_site2,$langue);
	$sr->load(1);
	$errors="";
	if($sr->title=="") $errors.="titre,";
	if($sr->contenu=="") $errors.="contenu,";
	if($sr->metatitle=="") $errors.="meta titre,";
	if($sr->metadescription=="") $errors.="meta description,";
	if($errors!="") $return['showroom'][]="<a href=\"showroom.php\" target=\"_blank\">".$sr->titles['FR']."</a>";
	
	if(!$testmenu->has_access("showroom.php")) $return["showroom"]=array();
	if($page!="all") break;
	
	case "venteflash":
	$vf=$DB_site2->query_first("SELECT id,text0$langue,text1$langue,text2$langue,ref_title$langue,ref_description$langue FROM vente_flash WHERE 1");
	$errors="";
	if($vf['text0'.$langue]=="") $errors.="texte avant,";
	if($vf['text1'.$langue]=="") $errors.="texte pendant,";
	if($vf['text2'.$langue]=="") $errors.="texte après,";
	if($vf['ref_title'.$langue]=="") $errors.="meta titre,";
	if($vf['ref_description'.$langue]=="") $errors.="meta description,";
	if($errors!="") $return['venteflash'][]="<a href=\"vente_flash.php\" target=\"_blank\">Vente flash</a> (".substr($errors,0,-1).")";
	
	if(!$testmenu->has_access("vente_flash.php")) $return["venteflash"]=array();
	if($page!="all") break;
	
	case "gallery":
	include("../class/gallery.class.php");
	$g=new gallery($DB_site2,$langue);
	$types=$g->getTypes();
	foreach($types as $type) {
	    $glist=$g->getListByType($type);
	    foreach($glist as $gid=>$gallery){
		$errors="";
		if($gallery->url=="") $errors.="url,";
		if($gallery->libelle=="") $errors.="libelle,";
		if($gallery->description=="") $errors.="description,";
		$photos=$gallery->load_photos();
		$plist="";
		foreach($photos as $photo) {
		    $perrors="";
		    if($photo->libelle=="") $perrors.="libelle,";
		    if($photo->link=="") $perrors.="lien,";
		    if($photo->legende=="") $perrors.="légende,";
		    if($perrors!="") $plist.="<br>Photo ".$photo->libelleFR." (".substr($perrors,0,-1).")";
		}
		if($errors!="" || $plist!="") $return['gallery'][]="<a href=\"gallery.php?action=modif&id=".$gallery->id."\" target=\"_blank\">".$gallery->libelleFR."</a>".($errors!=""?" (".substr($errors,0,-1).")":"").($plist!=""?$plist:"");
	    }
	}
	
	if(!$testmenu->has_access("gallery.php")) $return["gallery"]=array();
	if($page!="all") break;
	
	case "traduction":
	$tradlist=$DB_site2->query("SELECT path FROM traduction WHERE `$langue`='' ORDER BY path");
	while($trad=$DB_site2->fetch_array($tradlist)) {
	    $return['traduction'][]="<a href=\"traduction.php?open=".$trad['path']."\" target=\"_blank\">".$trad['path']."</a>";
	}
	
	if(!$testmenu->has_access("traduction.php")) $return["traduction"]=array();
	if($page!="all") break;
      }
     
     return $return;
}
?>
