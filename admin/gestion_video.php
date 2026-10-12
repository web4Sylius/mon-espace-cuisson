<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

$rq=$DB_site2->query("SELECT * FROM marque ORDER BY libelle");
while($m = $DB_site2->fetch_array($rq)){
    $marques[$m['marqueid']]=$m['libelle'];
    $optmarques.="<option value=\"$m[marqueid]\" ".($_POST['marqueid']==$m['marqueid']?"selected":"").">$m[libelle]</option>";
}

$checkA = ($_POST['content']=='avec'?"selected":"");
$checkS = ($_POST['content']=='sans'?"selected":"");
if(isset($_POST['visible'])) $check1 = (in_array(1,$_POST['visible'])?"checked":"");
if(isset($_POST['visible'])) $check0 = (in_array(0,$_POST['visible'])?"checked":"");
if(isset($_POST['visible'])) $check2 = (in_array(2,$_POST['visible'])?"checked":"");
if(isset($_POST['visible'])) $check3 = (in_array(3,$_POST['visible'])?"checked":"");
if(isset($_POST['visible'])) $check4 = (in_array(3,$_POST['visible'])?"checked":"");
if(isset($_POST['visible'])) $check5 = (in_array(3,$_POST['visible'])?"checked":"");
if(isset($_POST['visible'])) $check6 = (in_array(3,$_POST['visible'])?"checked":"");

$urlFR=$urlEN=$urlSP='';

$actives=array(0=>"<font color=\"red\">Inactif</font>",1=>"<font color=\"green\">Actif</font>",2=>"<font color=\"red\">Supprimé</font>",3=>"<font color=\"red\">Archivé</font>",4=>"<font color=\"red\">A contrôler</font>",5=>"<font color=\"red\">Archiver temporairement</font>",6=>"<font color=\"red\">En création</font>");

print <<<HTML

<h1>Vidéos</h1>

<form name="filter" method="post" action="gestion_video.php" enctype="multipart/form-data">
	<table class="tab_video" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<th>Marque :</th>
			<th>Vidéo :</th>
			<th colspan="4">Visibilité :</th>
		</tr>
		<tr>
			<td><select name="marqueid">$optmarques</select></td>
			<td>
				<select name="content">
					<option value="avec" $checkA>Avec vidéo</option>
					<option value="sans" $checkS>Sans vidéo</option>
				</select>
			</td>
			<td><input type="checkbox" name="visible[]" value="1" $check1>Actif</td>
			<td><input type="checkbox" name="visible[]" value="0" $check0>Inactif</td>
			<td><input type="checkbox" name="visible[]" value="2" $check2>Supprimé</td>
			<td><input type="checkbox" name="visible[]" value="3" $check3>Archivé définitivement</td>
			<td><input type="checkbox" name="visible[]" value="4" $check4>A contrôler</td>
			<td><input type="checkbox" name="visible[]" value="5" $check5>Archiver temporairement</td>
			<td><input type="checkbox" name="visible[]" value="6" $check6>En création</td>
			<td><input type="submit" value="OK" name="Valider" /></td>
		</tr>
	</table>
</form>

HTML;


if(isset($_POST['marqueid']) && isset($_POST['content'])){

	$sql = "SELECT * FROM article a INNER JOIN article_marque a_m USING(artid) INNER JOIN article_site a_s USING(artid) WHERE a_m.marqueid = ".$_POST['marqueid']."";
	if($_POST['content'] == 'avec') $sql .=" AND (videoFR != '' OR videoEN != '' OR videoSP != '')";
	if($_POST['content'] == 'sans') $sql .=" AND (videoFR = '' AND videoEN = '' AND videoSP = '')";
	if(isset($_POST['visible'])) $sql .=" AND a_s.active IN ('".implode("','",$_POST['visible'])."')";
	else $sql .=" AND a_s.active IN ('0','1')";
	$articles = $DB_site2->query($sql);

	$contenu_tab = "<table class=\"tableUpdArt table_alternate_color\">
		<tr>
	      	<td class=\"entete\" colspan=\"6\">".$marques[$_POST['marqueid']]."</td>
	    </tr>
	    <tr class=\"entete_categorie\">
	      	<td class=\"entete2\"></td>
	      	<td class=\"entete2\">Article</td>
	      	<td class=\"entete2\">Vidéo <img src=\"images/drapeaux/FR.gif\" width=\"20\"></td>
	      	<td class=\"entete2\">Vidéo <img src=\"images/drapeaux/EN.gif\" width=\"20\"></td>
	      	<td class=\"entete2\">Vidéo <img src=\"images/drapeaux/SP.gif\" width=\"20\"></td>
	      	<td class=\"entete2\"></td>
    	</tr>";

	while($article = $DB_site2->fetch_array($articles)){

		$contenu_tab .= "<tr class=\"entete_categorie\">
	      	<td><img src=\"../configurations/".$host."/images/produits/".$article['artid'].".jpg\" width=\"50\"></td>
	      	<td><a href=\"ajout_produit.php?artid=".$article['artid']."&prec=modif\" target=\"_blank\">".( $article['artcode'] == '' ? $article['artcode_eurocave'] : $article['artcode'] )."</a>
	      		<br>".$actives[$article['active']]."</td>";

	      	foreach($langues as $langue){
				$yt = explode(",",$article['video'.$langue]);
				if($yt[0] != ''){
					$video = explode('?',$yt[0]);
					$url = "<a href=\"".str_replace('embed/','watch?v=',$video[0])."\" target=\"_blank\"><img src=\"../images/templates/youtube.jpg\" width=\"25\"></a>";
					$contenu_tab .= "<td>".$url."</td>";
				}else{
					$contenu_tab .= "<td></td>";
				}
			}

	      	$contenu_tab .= "<td><a href=\"/article-art".$article['artid'].".htm\" target=\"_blank\"><img src=\"images/icon/viewmag.png\" width=\"20\"></a></td>
    	</tr>";
	}

	print $contenu_tab;
}