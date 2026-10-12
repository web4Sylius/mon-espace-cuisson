<?php

include("admin_global.php");
include("../class/fonctions.php");

include "./header.php";
include "./menu.php";

$DB_site = $DB_site2;

$filter_langue=array_diff($langues,$langue_access)!=array();

$sql = '';
if(isset($_POST['lid'])){
	foreach($langues as $langue) if(in_array($langue,$langue_access)) $sql.=", `libelle".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$_POST['libelle'.$langue])."', `description".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$_POST['description'.$langue])."', `linktxt".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$_POST['linktxt'.$langue])."', `link".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$_POST['link'.$langue])."', `ref_title".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_title'.$langue])."', `ref_description".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_description'.$langue])."', `url".$langue."`='".$_POST['url'.$langue]."', `imgtext".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$_POST['imgtext'.$langue])."', `imglink".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$_POST['imglink'.$langue])."', `footer".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$_POST['footer'.$langue])."',urlproduits".$langue."='".mysqli_real_escape_string($DB_site2->link_id,$_POST['urlproduits'.$langue])."'";
	if($_POST['lid']==0)
		$sql="INSERT INTO landing SET `type`='".$_POST['type']."', landingid=".intval($_POST['landingid']).$sql;
	else
		$sql="UPDATE landing SET ".substr($sql,1)." WHERE id=".intval($_POST['lid']);
	$DB_site2->query($sql);
	if($_POST['lid']==0) $_POST['lid']=$DB_site2->insert_id();

	$errors="";
	foreach($langues as $langue){
		if($_FILES['img'.$langue]['tmp_name']!=""){
			$info=getimagesize($_FILES['img'.$langue]['tmp_name']);
			$size=filesize($_FILES['img'.$langue]['tmp_name']);
			$error=false;
			if($info[0]!=690) $error="La taille de l'image ".$langue." n'est pas de 690px de large <a href=\"landing.php?type=".$_POST['type']."&landingid=".$_POST['lid']."\">Voir</a>";
			if($info['mime']!="image/jpeg") $error="Le type de l'image ".$langue." doit être jpg <a href=\"landing.php?type=".$_POST['type']."&landingid=".$_POST['lid']."\">Voir</a>";
			if($size>153600) $error="L'image est trop lourde, elle ne doit pas dépasser 100 ko <a href=\"landing.php?type=".$_POST['type']."&landingid=".$_POST['lid']."\">Voir</a>";
			if($error==false)
				move_uploaded_file($_FILES['img'.$langue]['tmp_name'],"../configurations/".$host."/images/landing/".$langue."/".$_POST['lid'].".jpg");
			else
				$errors.="<br>".$error;
		}
	}
}

if(isset($_POST['action'])){
	foreach($langues as $langue)
		$sql .= ", `libelle".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$_POST['libelle'.$langue])."', `description".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$_POST['description'.$langue])."', `linktxt".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$_POST['linktxt'.$langue])."', `link".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$_POST['link'.$langue])."', `ref_title".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_title'.$langue])."', `ref_description".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_description'.$langue])."', `url".$langue."`='".$_POST['url'.$langue]."', `footer".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$_POST['footer'.$langue])."'";
	$sql = "INSERT INTO landing SET `type`='".$_POST['type']."', landingid=0".$sql;
	$DB_site2->query($sql);
}

if(isset($_POST['cdcid'])){
	$landing=$DB_site2->query_first("SELECT produits FROM landing WHERE id=".intval($_POST['id']));
	$cdclist=array();
	if($landing['produits']!="")
		$cdclist=explode(",",$landing['produits']);

	$cdclist[$_POST['cdcid']]=intval($_POST['artid']);
	$DB_site2->query("UPDATE landing SET produits='".implode(",",$cdclist)."' WHERE id=".intval($_POST['id']));
}

if(isset($_POST['delcdcid'])){
	$landing=$DB_site2->query_first("SELECT produits FROM landing WHERE id=".intval($_POST['id']));
	$cdclist=explode(",",$landing['produits']);
	$cdclist=array_diff($cdclist,array($_POST['delcdcid']));
	$DB_site2->query("UPDATE landing SET produits='".implode(",",$cdclist)."' WHERE id=".intval($_POST['id']));
}

if(isset($_POST['clearcdc'])){
	$DB_site2->query("UPDATE landing SET produits='' WHERE id=".intval($_POST['id']));
}

if($_FILES['imgglobal']['tmp_name']!=""){
	if(!isset($_POST['imglangue']) || $_POST['imglangue']=="") $_POST['imglangue']="FR";
	$info=getimagesize($_FILES['imgglobal']['tmp_name']);
	$size=filesize($_FILES['imgglobal']['tmp_name']);
	$error=false;
	if($info[0]!=690) $error="La taille de l'image n'est pas de 690px de large";
	if($info['mime']!="image/jpeg") $error="Le type de l'image doit être jpg";
	if($size>153600) $error="L'image est trop lourde, elle ne doit pas dépasser 100 ko";
	if($error==false){
		if(isset($_POST['categorie'])){
			$req=$DB_site2->query("SELECT * FROM landing WHERE type='categorie'");
			while($landing=$DB_site2->fetch_array($req)){
				copy($_FILES['imgglobal']['tmp_name'],"../configurations/".$host."/images/landing/".$_POST['imglangue']."/".$landing['id'].".jpg");
			}
			if(!isset($_POST['noimglink'])) $DB_site2->query("UPDATE landing SET imglink".$_POST['imglangue']."='".$_POST['imglink']."' WHERE type='categorie'");
			if(!isset($_POST['noimgtext'])) $DB_site2->query("UPDATE landing SET imgtext".$_POST['imglangue']."='".$_POST['imgtext']."' WHERE type='categorie'");
		}
		if(isset($_POST['marque'])){
			$req=$DB_site2->query("SELECT * FROM landing WHERE type='marque'");
			while($landing=$DB_site2->fetch_array($req)){
				copy($_FILES['imgglobal']['tmp_name'],"../configurations/".$host."/images/landing/".$_POST['imglangue']."/".$landing['id'].".jpg");
			}
			if(!isset($_POST['noimglink'])) $DB_site2->query("UPDATE landing SET imglink".$_POST['imglangue']."='".$_POST['imglink']."' WHERE type='marque'");
			if(!isset($_POST['noimgtext'])) $DB_site2->query("UPDATE landing SET imgtext".$_POST['imglangue']."='".$_POST['imgtext']."' WHERE type='marque'");
		}
	}else
	$errors.="<br>".$error;


}

echo "<style>
.divlangue { display:none; width:800px; margin:auto; }
.onglets { text-align:center; margin:auto; }
.onglet { background-color:#CCC; width:100px; height:20px; margin-left:5px; text-align:center; border:solid thin #888; cursor:pointer; }
.onglet_on { width:100px; height:20px; margin-left:5px; text-align:center; border:solid thin black; cursor:pointer; }
#indextable { margin:auto; }
#indextable td { width:200px; height:150px; border:solid thin #888888; vertical-align:top; }
#indextable td > img { float:right; }
#submitinfo { display:none; text-align:center; }
</style>";

echo "<script language=\"javascript\">
var langues=new Array('".implode("','",$langues)."');
function switchonglet(langue){
	for(i=0;i<langues.length;i++){
		document.getElementById('landing'+langues[i]).style.display='none';
		document.getElementById('onglet'+langues[i]).className='onglet';
	}
	document.getElementById('submitinfo').style.display='none';
	if(langue!=undefined){
		document.getElementById('landing'+langue).style.display='block';
		document.getElementById('onglet'+langue).className='onglet_on';
		document.getElementById('submitinfo').style.display='block';
	}
}

function srcartid(src){
	if(src.length<=3) return;
	var url=\"marketplace/src.php?txt=\"+src
	ajaxrequest(url,srcCallback);
	document.getElementById('results').innerHTML='<img src=\"/configurations/".$host."/images/templates/ajax_wait.gif\">';
}

function srcCallback(httprequest){
	var xml=httprequest.responseXML.documentElement;
	var html='';

	var vistxt=new Array();
	vistxt[0]='<br><span style=\"color:red;\">Invisible</span>';
	vistxt[1]='<br>Visible';
	if(xml.getElementsByTagName('prod')[0]){
		html+='<table width=\"100%\" style=\"border:solid thin #808080;\"><tr>';
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
			if(actif<2) html+='<tr id=\"tr_'+artid+'\"><td><img src=\"/configurations/".$host."/images/produits/br/'+artid+'.jpg\" width=\"80\"></td><td style=\"border-bottom:solid thin #cccccc;\">'+marque+'<br>'+id+'<br>'+ref+'<br>'+vistxt[actif]+'</td><td style=\"border-bottom:solid thin #cccccc;\"><b>'+description+'</b><br>Ventes : '+ventes+'<br>Ventes 1 an : '+ventes1an+'<br>'+stock+'</td><td style=\"border-bottom:solid thin #cccccc;\"><a href=\"javascript:selectprod('+artid+')\">Sélectionner</a></td></tr>';
		}
		html+='</table>';
	}else
	html='Pas de résultat';

	document.getElementById('results').innerHTML=html;
}

function selectprod(artid){
	document.getElementsByName('artid')[0].value=artid;
	document.forms.modifcdc.submit();
}

function delcdc(artid){
	document.getElementsByName('delcdcid')[0].value=artid;
	document.forms.delcdcform.submit();
}
</script>";

echo "<h1>Landing page</h1>";

if(isset($_GET['type'])){
	if(!isset($_GET['landingid']) && !isset($_GET['action'])){
		if(isset($errors)) print "<p style=\"color:red;\">".$errors."</p>";

		switch($_GET['type']){
			// LANDING MARQUE
			case "marque":
				print "<h3>Marques</h3>";
				print "<a href=\"landing.php\">Retour</a>
				<table align=\"center\">";
				$sql="SELECT marqueid AS landingid,libelle FROM marque ORDER BY libelle";
				$req=$DB_site2->query($sql);
				while($marque=$DB_site2->fetch_array($req)){
					$landing=$DB_site2->query_first("SELECT id,produits,urlFR FROM landing WHERE type='".$_GET['type']."' AND landingid=".$marque['landingid']);
					$color=$url=$imglist="";
					if($landing['id']!=""){
						$color="green";
						$url="<br><a href=\"/cave/".$landing['urlFR'].".html\" target=\"_blank\">cave/".$landing['urlFR'].".html</a>";
						if($landing['produits']!=""){
							$cdclist=explode(",",$landing['produits']);
							foreach($cdclist as $cdcid){
								$art=$DB_site2->query_first("SELECT active,artcode,libelleFR FROM article WHERE artid=".$cdcid);
								if($art['active']==1) $imglist.="<img src=\"/configurations/".$host."/images/produits/".$cdcid.".jpg\" title=\"".$art['libelleFR']." ".$art['artcode']."\" width=\"30\"> ";
							}
						}
					}
					print "<tr><td style=\"color:$color;\">".$marque['libelle'].$url."</td><td>".$imglist."</td><td><a href=\"landing.php?type=".$_GET['type']."&landingid=".$marque['landingid']."\"><img src=\"images/editer.gif\"></a></td></tr>";
				}
				print "</table>";
			break;

			// LANDING CATEGORIE
			case "categorie":
				print "<h3>Catégories</h3>";
				print "<a href=\"landing.php\">Retour</a>
				<table align=\"center\">";

				//Sélection de la liste des catégories
				$sql = "SELECT catid AS landingid, libelleFR AS libelle";
				$sql .= " FROM categorie";
				$sql .= " WHERE 1";
				//$sql .= " AND parentid = 0";
				$sql .= " AND menu_appartenance != 0";
				$sql .= " ORDER BY menu_appartenance, position";
				$req = $DB_site2->query($sql);
				while($marque=$DB_site2->fetch_array($req)){
					$sql = "SELECT id, produits, urlFR";
					$sql .= " FROM landing";
					$sql .= " WHERE type = '".$_GET['type']."'";
					$sql .= " AND landingid = ".$marque['landingid'];
					$landing = $DB_site2->query_first($sql);

					$color = $url = $imglist = "";

			    	//La landing existe déjà : le lien s'affiche
					if($landing['id']!=""){
						$color = "green";
						$url   = "<br><a href=\"/cave/".$landing['urlFR'].".html\" target=\"_blank\">cave/".$landing['urlFR'].".html</a>";

						if($landing['produits'] != ""){
							$cdclist = explode(",",$landing['produits']);

							foreach($cdclist as $cdcid){
								$sql = "SELECT active, artcode, libelleFR";
								$sql .= " FROM article";
								$sql .= " WHERE artid = ".$cdcid;
								$art = $DB_site2->query_first($sql);

								if($art['active'] == 1) $imglist.="<img src=\"/configurations/".$host."/images/produits/".$cdcid.".jpg\" title=\"".$art['libelleFR']." ".$art['artcode']."\" width=\"30\"> ";
							}
						}
					}

					print ""
					. "<tr>"
					. "<td style=\"color:".$color.";font-size:14px;border-top:solid 1px black;\">".$marque['libelle'].$url."</td>"
					. "<td style=\"border-top:solid 1px black;\">".$imglist."&nbsp;</td>"
					. "<td style=\"border-top:solid 1px black;\"><a href=\"landing.php?type=".$_GET['type']."&landingid=".$marque['landingid']."\"><img src=\"images/editer.gif\"></a></td>"
					. "</tr>";

					$req2=$DB_site2->query("SELECT catid AS landingid,libelleFR AS libelle FROM categorie WHERE parentid=".$marque['landingid']." ORDER BY position");
					if($DB_site2->num_rows($req2)>0){
						print "<tr><td style=\"padding-left:10px;\">
						<table>";
						while($sscat=$DB_site2->fetch_array($req2)){
							$landing=$DB_site2->query_first("SELECT id,produits,urlFR FROM landing WHERE type='".$_GET['type']."' AND landingid=".$sscat['landingid']);
							$color=$url=$imglist="";
							if($landing['id']!=""){
								$color="green";
								$url="<br><a href=\"/cave/".$landing['urlFR'].".html\" target=\"_blank\">cave/".$landing['urlFR'].".html</a>";
								if($landing['produits']!=""){
									$cdclist=explode(",",$landing['produits']);
									foreach($cdclist as $cdcid){
										$art=$DB_site2->query_first("SELECT active,artcode,libelleFR FROM article WHERE artid=".$cdcid);
										if($art['active']==1) $imglist.="<img src=\"/configurations/".$host."/images/produits/".$cdcid.".jpg\" title=\"".$art['libelleFR']." ".$art['artcode']."\" width=\"30\"> ";
									}
								}
							}
							print "<tr><td style=\"color:$color;\">".$sscat['libelle'].$url."</td><td>".$imglist."</td><td><a href=\"landing.php?type=".$_GET['type']."&landingid=".$sscat['landingid']."\"><img src=\"images/editer.gif\"></a></td></tr>";
						}
						print "</table>
						</td></tr>";
					}
				}
				print "</table>";
			break;

			case "index":
			case "promos":
				$_GET['landingid']=0;
			break;

			case "slider":
				print "<h3>Sliders</h3>";
				print "<table align=\"center\">";

				// Sélection de la liste des landing
				$sql = " SELECT * FROM landing WHERE type='slider' ";
				$req = $DB_site2->query($sql);
				while($slider = $DB_site2->fetch_array($req)){
					$url = "<a href=\"/cave/".$slider['urlFR'].".html\" target=\"_blank\">cave/".$slider['urlFR'].".html</a>";

					print "<tr>
						<td>".$slider['libelleFR']."<br>".$url."</td>
						<td><a href=\"landing.php?type=".$_GET['type']."&landingid=".$slider['landingid']."\"><img src=\"images/editer.gif\"></a></td>
					</tr>";

				}
				print "</table>";
				print "<a href=\"landing.php?type=slider&action=add\">Ajouter un landing</a>
				<a href=\"landing.php\">Retour</a>";
			break;
		}
	}
}else{
	$marnb=$DB_site2->query_first("SELECT COUNT(*) FROM landing WHERE type='marque'");
	$catnb=$DB_site2->query_first("SELECT COUNT(*) FROM landing WHERE type='categorie'");
	$slinb=$DB_site2->query_first("SELECT COUNT(*) FROM landing WHERE type='slider'");

	if(isset($errors)) print "<p style=\"color:red;\">".$errors."</p>";

	print "<p><a href=\"landing.php?type=index&landingid=0\">Index</a></p>
	<p><a href=\"landing.php?type=marque\">Marques</a> (".$marnb[0].")</p>
	<p><a href=\"landing.php?type=categorie\">Catégories</a> (".$catnb[0].")</p>
	<p><a href=\"landing.php?type=promos\">Promos</a></p>
	<p><a href=\"landing.php?type=slider\">Sliders</a> (".$slinb[0].")</p>";

	if(!$filter_langue) {
		print "<p>&nbsp;</p>
		<form method=\"post\" enctype=\"multipart/form-data\">
			<fieldset>
			<legend>Changer toutes les images</legend>
			<table>
				<tr>
					<td>Image (690 x 90px jpg <100 ko) :</td>
					<td><input type=\"file\" name=\"imgglobal\"></td>
				</tr>
				<tr>
					<td>Lien sur l'image (facultatif) :</td>
					<td><input type=\"text\" name=\"imglink\" value=\"\" size=\"60\" readonly> <input type=\"checkbox\" name=\"noimglink\" onclick=\"document.getElementsByName('imglink')[0].readOnly=this.checked\" checked> Ne pas changer</td>
				</tr>
				<tr>
					<td>Texte pour référencement :</td>
					<td><input type=\"text\" name=\"imgtext\" value=\"\" size=\"60\" readonly> <input type=\"checkbox\" name=\"noimgtext\" onclick=\"document.getElementsByName('imgtext')[0].readOnly=this.checked\" checked> Ne pas changer</td>
				</tr>
				<tr>
					<td>Langue :</td>
					<td>";
				foreach($langues as $l) 
					print "<input type=\"radio\" name=\"imglangue\" value=\"$l\"".($l=="FR"?" checked":"")."> ".$l;
				print "</td>
				</tr>
				<tr>
					<td>Appliquer sur :</td>
					<td><input type=\"checkbox\" name=\"categorie\" value=\"1\" checked> catégories &nbsp; <input type=\"checkbox\" name=\"marque\" value=\"1\" checked> marques</td>
				</tr>
				<tr>
					<td><input type=\"submit\" value=\"Envoyer\"></td>
				</tr>
			</table>
			</fieldset>
		</form>";
	}
}

if(isset($_GET['landingid'])){

	switch($_GET['type']){
		case "marque":
			$title=$DB_site2->query_first("SELECT libelle FROM marque WHERE marqueid=".$_GET['landingid']);
		break;

		case "categorie":
			$title=$DB_site2->query_first("SELECT libelleFR AS libelle FROM categorie WHERE catid=".$_GET['landingid']);
		break;

		case "index":
			$title=$DB_site2->query_first("SELECT libelleFR AS libelle FROM landing WHERE type='index'");
			$_GET['landingid']=0;
		break;

		case "promos":
			$title=$DB_site2->query_first("SELECT libelleFR AS libelle FROM landing WHERE type='promos'");
			$_GET['landingid']=0;
		break;

		case "slider":
			$title=$DB_site2->query_first("SELECT libelleFR AS libelle FROM landing WHERE type='slider'");
		break;
	}

	$landing=$DB_site2->query_first("SELECT * FROM landing WHERE type='".$_GET['type']."' AND landingid=".intval($_GET['landingid']));

	print "<h3>".$title['libelle']."</h3>
	<form action=\"landing.php?type=".$_GET['type']."\" method=\"post\" enctype=\"multipart/form-data\">
		<input type=\"hidden\" name=\"lid\" value=\"".intval($landing['id'])."\">
		<input type=\"hidden\" name=\"type\" value=\"".$_GET['type']."\">
		<input type=\"hidden\" name=\"landingid\" value=\"".intval($_GET['landingid'])."\">";

	print "<table class=\"onglets\">
	<tr><td>Infos : </td>";
	foreach($langues as $langue){
		$on="";
		$style=($landing['libelle'.$langue].$landing['url'.$langue]==""?"color:red;":"color:green;");
		print "<td class=\"onglet$on\" id=\"onglet$langue\" style=\"$style\" onclick=\"switchonglet('$langue');\">$langue <img src=\"images/drapeaux/".$langue.".gif\" width=\"20\"></div>";
	}
	print "</tr>
	</table>";

	foreach($langues as $langue){
		$readonly=!in_array($langue,$langue_access);
		print "<div id=\"landing$langue\" class=\"divlangue\">
			<p>URL <img src=\"images/drapeaux/".$langue.".gif\" width=\"20\"> : ".($readonly?$landing['url'.$langue]."<hr>":"<input type=\"text\" name=\"url".$langue."\" value=\"".$landing['url'.$langue]."\" size=\"50\">")."</p>
			<p>Libellé <img src=\"images/drapeaux/".$langue.".gif\" width=\"20\"> : ".($readonly?$landing['libelle'.$langue]."<hr>":"<input type=\"text\" name=\"libelle".$langue."\" value=\"".$landing['libelle'.$langue]."\" size=\"50\">")."</p>
			<p><img src=\"images/drapeaux/$langue.gif\" width=\"20\"> Description $langue : </p>" ;

		if($readonly)
			echo $landing['description'.$langue]."<hr>";
		else
			echo "<textarea name=\"description".$langue."\" class=\"ckeditor\">".$landing['description'.$langue]."</textarea>";

		print "<p>Texte lien haut page <img src=\"images/drapeaux/".$langue.".gif\" width=\"20\"> : ".($readonly?$landing['linktxt'.$langue]."<hr>":"<input type=\"text\" name=\"linktxt".$langue."\" value=\"".$landing['linktxt'.$langue]."\" size=\"40\">")."</p>";

		if($_GET['type'] == 'categorie')
			print	"<p>Lien page produits <img src=\"images/drapeaux/".$langue.".gif\" width=\"20\"> : ".($readonly?$landing['linktxt'.$langue]."<hr>":"<input type=\"text\" name=\"urlproduits".$langue."\" value=\"".$landing['urlproduits'.$langue]."\" size=\"40\">")."</p>";

		print	"<p>Image <img src=\"images/drapeaux/".$langue.".gif\" width=\"20\"></p>";

		$imgpath="../configurations/".$host."/images/landing/".$langue."/".$landing['id'].".jpg";
		if(file_exists($imgpath)){
			print "<img src=\"".$imgpath."\">";
		}else{
			print "<div style=\"border:solid thin; width:690px; height:90px; text-align:center;\">Pas d'image</div>";
		}

		if($readonly)
			print "<br>Lien sur l'image : ".$landing['imglink'.$langue]."
		<br>Texte pour référencement : ".$landing['imgtext'.$langue]."<hr>";
		else
			print "<br>Envoyer une image (690 x 90px jpg <100 ko) <input type=\"file\" name=\"img".$langue."\">
		<br>Lien sur l'image (facultatif) : <input type=\"text\" name=\"imglink".$langue."\" value=\"".$landing['imglink'.$langue]."\" size=\"60\">
		<br>Texte pour référencement : <input type=\"text\" name=\"imgtext".$langue."\" value=\"".$landing['imgtext'.$langue]."\" size=\"60\">";

		echo "<p><img src=\"images/drapeaux/$langue.gif\" width=\"20\"> Lien $langue : </p>" ;

		if($readonly)
			echo $landing['link'.$langue]."<hr>";
		else
			echo "<textarea name=\"link".$langue."\" class=\"ckeditor\">".$landing['link'.$langue]."</textarea>";

		echo "<p>Texte lien footer <img src=\"images/drapeaux/$langue.gif\" width=\"20\"> : ".($readonly?$landing['footer'.$langue]."<hr>":"<input type=\"text\" name=\"footer$langue\" value=\"".$landing['footer'.$langue]."\" size=\"40\">")."</p>
			<p><img src=\"images/drapeaux/$langue.gif\" width=\"20\"> Meta title $langue : ".($readonly?$landing['ref_title'.$langue]."<hr>":"<input type=\"text\" name=\"ref_title$langue\" value=\"".$landing['ref_title'.$langue]."\" size=\"60\">")."</p>
			<p><img src=\"images/drapeaux/$langue.gif\" width=\"20\"> Meta description :<br>".($readonly?$landing['ref_description'.$langue]."<hr>":"<textarea name=\"ref_description$langue\" cols=\"80\" rows=\"4\">".$landing['ref_description'.$langue]."</textarea>")."</p>
		</div>";
	}

	print "<p id=\"submitinfo\"><input type=\"button\" value=\"Annuler\" onclick=\"switchonglet()\"> <input type=\"submit\" value=\"Enregistrer\"></p>
	</form>";

	if($landing['id']!=""){
		$cdc=array();
		if($landing['produits']!=""){
			$cdc=explode(",",$landing['produits']);
			print "<p>Les produits sont choisis manuellement, pour les mettre en automatique, <a href=\"javascript:document.forms.clearprod.submit()\">supprimez les produits</a></p>";
		}else{
			print "<p>Les produits s'affichent automatiquement en fonction des meilleures ventes</p>";
		}
		print "<table id=\"indextable\"><tr>";

		$artlist=array();
		$cpt=0;
		foreach($cdc as $cdcid) {
			$art=$DB_site2->query_first("SELECT artid,artcode,libelleFR,active FROM article WHERE artid=".intval($cdcid));
			$prix=article_price($cdcid,true,"base");
			$art['prix']=$prix;
			$artlist[$prix+$cpt]=$art;
			$cpt++;
		}
		ksort($artlist);

		$c=0;
		foreach($artlist as $art){
			print "<td><img src=\"images/supprimer.gif\" onclick=\"delcdc(".$art['artid'].")\" style=\"cursor:pointer;\">".vignette($art)."</td>";
			$c++;
			if($c==4){ print "</tr><tr>"; $c=0; }

		}
		print "</table>
		<form name=\"modifcdc\" method=\"post\">
		<input type=\"hidden\" name=\"id\" value=\"".$landing['id']."\">
		<input type=\"hidden\" name=\"cdcid\">
		<input type=\"hidden\" name=\"artid\">
		Ajouter une référence : <input type=\"text\" onkeyup=\"srcartid(this.value)\">
		<div id=\"results\"></div>
		</form>

		<form name=\"delcdcform\" method=\"post\">
		<input type=\"hidden\" name=\"id\" value=\"".$landing['id']."\">
		<input type=\"hidden\" name=\"delcdcid\">
		</form>

		<form name=\"clearprod\" method=\"post\">
		<input type=\"hidden\" name=\"id\" value=\"".$landing['id']."\">
		<input type=\"hidden\" name=\"clearcdc\" value=\"1\">
		</form>";
	}

	print "<a href=\"landing.php?type=".$_GET['type']."\">Retour à la liste</a>";

}elseif(isset($_GET['action'])){

	$title = "Nouveau landing <b>".$_GET['type']."</b>";

	print "<h3>".$title."</h3>
	<form action=\"landing.php?type=".$_GET['type']."\" method=\"post\" enctype=\"multipart/form-data\">
		<input type=\"hidden\" name=\"type\" value=\"".$_GET['type']."\">
		<input type=\"hidden\" name=\"action\" value=\"add\">
		<table class=\"onglets\">
			<tr>
				<td>Infos : </td>";
				foreach($langues as $langue){
					$on="";
					print "<td class=\"onglet$on\" id=\"onglet$langue\" style=\"$style\" onclick=\"switchonglet('$langue');\">$langue <img src=\"images/drapeaux/".$langue.".gif\" width=\"20\"></div>";
				}
	print  "</tr>
		</table>";

	foreach($langues as $langue){
		$readonly=!in_array($langue,$langue_access);
		print "<div id=\"landing".$langue."\" class=\"divlangue\">
			<p>URL <img src=\"images/drapeaux/".$langue.".gif\" width=\"20\"> : <input type=\"text\" name=\"url".$langue."\" size=\"50\"></p>
			<p>Libellé <img src=\"images/drapeaux/".$langue.".gif\" width=\"20\"> : <input type=\"text\" name=\"libelle".$langue."\" size=\"50\"></p>
			<p>Description <img src=\"images/drapeaux/".$langue.".gif\" width=\"20\"> : </p>
			<textarea name=\"description".$langue."\" class=\"ckeditor\"></textarea>

			<p>Texte lien haut page <img src=\"images/drapeaux/".$langue.".gif\" width=\"20\"> : <input type=\"text\" name=\"linktxt".$langue."\" size=\"40\"></p>
			<br>Texte pour référencement : <input type=\"text\" name=\"imgtext".$langue."\" size=\"60\">
			<p>Lien <img src=\"images/drapeaux/".$langue.".gif\" width=\"20\"> : </p>
			<textarea name=\"link".$langue."\" class=\"ckeditor\">".$landing['link'.$langue]."</textarea>

			<p>Texte lien footer <img src=\"images/drapeaux/$langue.gif\" width=\"20\"> : <input type=\"text\" name=\"footer$langue\" size=\"40\"></p>
			<p>Meta title <img src=\"images/drapeaux/$langue.gif\" width=\"20\"> : <input type=\"text\" name=\"ref_title$langue\" size=\"60\"></p>
			<p>Meta description <img src=\"images/drapeaux/$langue.gif\" width=\"20\"> : <br> <textarea name=\"ref_description$langue\" cols=\"80\" rows=\"4\"></textarea></p>
		</div>";
	}

	print "<p id=\"submitinfo\"><input type=\"button\" value=\"Annuler\" onclick=\"switchonglet()\"> <input type=\"submit\" value=\"Enregistrer\"></p>
	</form>";


}


include "./footer.php";

?>