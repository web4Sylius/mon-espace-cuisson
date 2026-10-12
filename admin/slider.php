<?php

// MULTISITE

require "admin_global.php";
include "./header.php";
include "./menu.php";

$ext=array("jpg");
$actif=array(0=>"<span color=\"red\">Invisible</span>",1=>"Visible",2=>"<span color=\"red\">Supprimé</span>");
if(!isset($slsite)){
	$slsite = "base";
	$sllang = "FR";
	$slider = "index";
}
if(isset($action)){
	switch($action){
		case "up":
			$s=$DB_site2->query_first("SELECT * FROM slider WHERE id=".$_GET['id']);
			$DB_site2->query("UPDATE slider SET position=position+1 WHERE position=".($s['position']-1)." AND slidername='".$slider."' AND langue='$sllang' AND site_code='$slsite'");
			$DB_site2->query("UPDATE slider SET position=position-1 WHERE id=".$_GET['id']." AND slidername='".$slider."' AND langue='$sllang'");
			unset($action);
			break;
			
		case "down":
			$s=$DB_site2->query_first("SELECT * FROM slider WHERE id=".$_GET['id']);
			$DB_site2->query("UPDATE slider SET position=position-1 WHERE position=".($s['position']+1)." AND slidername='".$slider."' AND langue='$sllang' AND site_code='$slsite'");
			$DB_site2->query("UPDATE slider SET position=position+1 WHERE id=".$_GET['id']." AND slidername='".$slider."' AND langue='$sllang'");
			unset($action);
			break;
			
		case "del":
			$DB_site2->query("DELETE FROM slider WHERE id=".$_GET['id']);
			$pos=1;
			$r=$DB_site2->query("SELECT id FROM slider WHERE langue='$sllang' AND slidername='$slider' AND site_code='$slsite' ORDER BY position");
			while($s=$DB_site2->fetch_array($r)){
				$DB_site2->query("UPDATE slider SET position=$pos WHERE id=$s[0]");
				$pos++;
			}
			unset($action);
			break;
			
		case "visible":
			$DB_site2->query("UPDATE slider SET visible=".intval($visible)." WHERE id='$id' AND slidername='$slider' AND langue='$sllang'");
			unset($action);
			break;
			
		case "pro":
			$DB_site2->query("UPDATE slider SET pro=".intval($_GET['pro'])." WHERE id='$id' AND slidername='$slider' AND langue='$sllang'");
			unset($action);
			break;
			
		case "modif2":
			if(!isset($_POST['pro'])) $_POST['pro']=array(0);
			if(isset($_FILES['img']['name'])){
				$extension = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
				$dir="../configurations/".$host."/images/autopromo/";
				$file=$_POST['modif'].".jpg";
				if(in_array(strtolower($extension),$ext)){
					if(move_uploaded_file($_FILES['img']['tmp_name'],$dir.$file)) exec("jpegoptim ".$dir.$file);
				}else
					$erreur.="Ce type de fichier ne peut pas être envoyé";
			}

			if($_POST['dbt']!='' && $_POST['fin']!=''){
				$dfrom=preg_split('/[- :\/h]/',$_POST['dbt']);
	    		$dbt=mktime($dfrom[3],$dfrom[4],0,$dfrom[1],$dfrom[0],$dfrom[2]);
	    		$dto=preg_split('/[- :\/h]/',$_POST['fin']);
	    		$fin=mktime($dto[3],$dto[4],0,$dto[1],$dto[0],$dto[2]);
			}else{
				$dbt = 0;
				$fin = 0;
			}
			
			if($_POST['land'] == "")
				$lien = $_POST['lien'];
			else
				$lien = $_POST['land'];

			$DB_site2->query("UPDATE slider SET lien='".$lien."',texte='".mysqli_real_escape_string($DB_site2->link_id,$_POST['texte'])."',btn='".mysqli_real_escape_string($DB_site2->link_id,$_POST['btn'])."', artid=".intval($_POST['artid']).",pro=".array_sum($_POST['pro']).", date_dbt=".$dbt.", date_fin=".$fin." WHERE id=".$_POST['modif']);
			
			unset($action);
			break;
			
		case "add2":
			$extension = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
			$dir="../configurations/".$host."/images/autopromo/";
			$file="upload.jpg";
			$_POST['artid']=intval($_POST['artid']);

			if(!isset($_POST['pro'])) $_POST['pro']=array(0);
			if($_POST['dbt']!='' && $_POST['fin']!=''){
				$dfrom=preg_split('/[- :\/h]/',$_POST['dbt']);
	    		$dbt=mktime($dfrom[3],$dfrom[4],0,$dfrom[1],$dfrom[0],$dfrom[2]);
	    		$dto=preg_split('/[- :\/h]/',$_POST['fin']);
	    		$fin=mktime($dto[3],$dto[4],0,$dto[1],$dto[0],$dto[2]);
			}else{
				$dbt = 0;
				$fin = 0;
			}

			if($_POST['land'] == "")
				$lien = $_POST['lien'];
			else
				$lien = $_POST['land'];

			$s=$DB_site2->query_first("SELECT COUNT(id) FROM slider WHERE slidername='$slider' AND langue='$sllang'");
			if($_POST['artid']==0){
				if(in_array(strtolower($extension),$ext)){
					if(!move_uploaded_file($_FILES['img']['tmp_name'],$dir.$file)) $file="";
					if($file!=""){
						exec("jpegoptim ".$dir.$file);
						$req = $DB_site2->query("SELECT * FROM slider WHERE slidername='$slider' AND langue='$sllang'");
						while($lignes = $DB_site2->fetch_array($req)){
							$DB_site2->query("UPDATE slider SET position=position+1 WHERE id=".$lignes['id']." AND slidername='".$slider."' AND langue='$sllang'");
						}
						$DB_site2->query("INSERT INTO slider SET slidername='$slider',site_code='$slsite',langue='$sllang',lien='".$lien."',texte='".mysqli_real_escape_string($DB_site2->link_id,$_POST['texte'])."',btn='".mysqli_real_escape_string($DB_site2->link_id,$_POST['btn'])."', visible=".intval($_POST['visible']).",pro=".array_sum($_POST['pro']).",position=1, artid=0, date_dbt=".$dbt.", date_fin=".$fin."");
						rename($dir."upload.jpg",$dir.$DB_site2->insert_id().".jpg");
					}
				}else
					$erreur.="Ce type de fichier ne peut pas être envoyé";
			}else{
				$req = $DB_site2->query("SELECT * FROM slider WHERE slidername='$slider' AND langue='$sllang'");
				while($lignes = $DB_site2->fetch_array($req)){
					$DB_site2->query("UPDATE slider SET position=position+1 WHERE id=".$lignes['id']." AND slidername='".$slider."' AND langue='$sllang'");
				}
				$DB_site2->query("INSERT INTO slider SET slidername='$slider',site_code='$slsite', langue='$sllang',lien='',texte='',btn='".mysqli_real_escape_string($DB_site2->link_id,$_POST['btn'])."', visible=".intval($_POST['visible']).",pro=".array_sum($_POST['pro']).",position=1, artid=".$_POST['artid'].",date_dbt=".$dbt.",date_fin=".$fin);
			}
			unset($action);
			break;
	}
}

echo "
<td valign=\"top\">
<script language=\"javascript\">
	var site_code='$slsite';
	function srcartid(src){
		if(src.length<=3) return;
		var url=\"marketplace/src.php?txt=\"+src
		ajaxrequest(url,srcCallback);
		document.getElementById('results').innerHTML='<img src=\"/configurations/".$host."/images/templates/ajax_wait.gif\">';
		document.getElementById('divimg').style.display='none';
	}

	function srcCallback(httprequest){
		var xml=httprequest.responseXML.documentElement;
		var html='';
		var vistxt=new Array();
		vistxt[0]='<br><span style=\"color:red;\">Invisible</span>';
		vistxt[1]='<br>Visible';
		if(xml.getElementsByTagName('prod')[0]){
			html+='<table width=\"100%\" style=\"border:solid thin #808080;\" class=\"txt\"><tr>';
			for(i=0;i<xml.getElementsByTagName('prod').length;i++){
				var description=xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
				var id=xml.getElementsByTagName('prod')[i].getAttribute('id');
				var artid=xml.getElementsByTagName('prod')[i].getAttribute('artid');
				var actif=xml.getElementsByTagName('prod')[i].getAttribute('active_'+site_code);
				var stock=xml.getElementsByTagName('prod')[i].getAttribute('stock');
				var marque=xml.getElementsByTagName('prod')[i].getAttribute('marque');
				var ref=xml.getElementsByTagName('prod')[i].getAttribute('ref');
				var lot=xml.getElementsByTagName('prod')[i].getAttribute('lot');
				if(actif<2) html+='<tr id=\"tr_'+artid+'\"><td><img src=\"/configurations/".$host."/images/produits/br/'+artid+'.jpg\" width=\"80\"></td><td style=\"border-bottom:solid thin #cccccc;\">'+marque+'<br>'+id+'<br>'+ref+'<br>'+vistxt[actif]+'</td><td style=\"border-bottom:solid thin #cccccc;\">'+description+'<td style=\"border-bottom:solid thin #cccccc;\"><a href=\"javascript:selectprod('+artid+')\">Sélectionner</a></td></tr>';
			}
			html+='</table>';
		}else
			html='Pas de résultat';

		document.getElementById('results').innerHTML=html;
	}

	function selectprod(artid){
		var trs=document.getElementById('results').getElementsByTagName('tr');
		for(i=0;i<trs.length;i++){
			if(trs[i].id!='tr_'+artid)
				trs[i].style.display='none';
			else
				trs[i].style.backgroundColor='#FFAAAA';
		}
		document.getElementsByName('artid')[0].value=artid;
	}
	
	function cal_delete(value){
		var myValue = document.getElementsByName(value);
		myValue[0].value = '';
	}

	function inputUrl(value){
		if(value == 'lien'){
			document.getElementById('lien').style.display = '';
			document.getElementById('land').style.display = 'none';
			document.getElementsByName('land')[0].value = '0'
		}else if(value == 'land'){
			document.getElementById('lien').style.display = 'none';
			document.getElementById('land').style.display = '';
		}
	}
</script>

<h1>Sliders</h1>" ;

$sel="";
if(isset($slider)) $sel=$slider;
print "<form name=\"f\" action=\"slider.php\" method=\"post\">
Site <select name=\"slsite\" onchange=\"document.forms.f.submit()\">
<option></option>";
foreach($sites as $site_code=>$site) print "<option value=\"".$site_code."\"".($slsite==$site_code?" selected":"").">".$site_code."</option>";
print "</select>";
if(isset($slsite)){
    print " &gt; langue <select name=\"sllang\" onchange=\"document.forms.f.submit()\">
    <option></option>";
    $site_langues=$sites[$slsite]['hosts'];
    if(!in_array($sllang,$site_langues)) unset($sllang);
    foreach($site_langues as $l) print "<option value=\"$l\"".($sllang==$l?" selected":"").">$l</option>";
    print "</select>
     &gt; page <select name=\"slider\" onchange=\"document.forms.f.submit()\"><option></option>";
    $sliders=$DB_site2->query("SELECT slidername FROM slider GROUP BY slidername");
    while($s=$DB_site2->fetch_array($sliders)){
	print "<option value=\"$s[slidername]\"".($sel==$s['slidername']?" selected":"").">$s[slidername]</option>";
    }
    print "</select>";
}
print "</form>";

if(isset($slider) && $slider!="" && isset($sllang) && $sllang!="" && isset($slsite)){
    if($action=="add") {

    	$optland = "<option value=\"\">...</option>";
		$req = $DB_site2->query("SELECT libelle".$sllang." AS libelle, url".$sllang." AS url FROM landing WHERE type='slider'");
		while($landing=$DB_site2->fetch_array($req)){
			$optland .= "<option value=\"/cave/".$landing['url'].".html\">".$landing['libelle']."</option>";
		}

		print "<form method=\"post\" enctype=\"multipart/form-data\">
		<input type=\"hidden\" name=\"slider\" value=\"".$slider."\">
		<input type=\"hidden\" name=\"langue\" value=\"".$sllang."\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"action\" value=\"".$action."2\">
		<input type=\"hidden\" name=\"artid\" value=\"\">
		<h3>Ajouter un article</h3>
		Référence : <input type=\"text\" onkeyup=\"srcartid(this.value);\">
		<div id=\"results\"></div>
		<div id=\"divimg\">
		  <p>OU</p>
		  <h3>Ajouter une image (format 690 x 285px jpg)</h3>
		  Image : <input type=\"file\" name=\"img\">
		  <br><select onchange=\"inputUrl(this.value);\">
				<option value=\"lien\">Lien personnalisé</option>
				<option value=\"land\">Lien landing slider</option>
			</select>
			<input type=\"text\" name=\"lien\" id=\"lien\" size=\"60\">
			<select name=\"land\" id=\"land\" style=\"display:none;\">".$optland."</select>
		  <br>Texte référencement : <input type=\"text\" name=\"texte\" value=\"".$s['texte']."\" size=\"60\">
		</div>
		<!--<p>Texte du bouton : <input type=\"text\" name=\"btn\" value=\"".$s['btn']."\" size=\"60\"></p>-->
		<p><input type=\"checkbox\" name=\"pro[]\" value=\"1\"".($part==1?" checked":"")."> Site particuliers
		<br><input type=\"checkbox\" name=\"pro[]\" value=\"2\"".($pro==1?" checked":"")."> Site pro</p>
		<p><input type=\"checkbox\" name=\"visible\" value=\"1\"> Visible
		<p>Date de début : <input id=\"dbt\" name=\"dbt\" onclick=\"calendrier_show(this.id,this.value,true);\" size=\"14\" type=\"text\">
			<a href=\"javascript:cal_delete('dbt');\" class=\"cal_delete\" id=\"\">&nbsp;X&nbsp;</a></p>
		<p>Date de fin : <input id=\"fin\" name=\"fin\" onclick=\"calendrier_show(this.id,this.value,true);\" size=\"14\" type=\"text\">
			<a href=\"javascript:cal_delete('fin');\" class=\"cal_delete\" id=\"\">&nbsp;X&nbsp;</a></p>
		<br>
		<p><input type=\"button\" value=\"Retour\" onclick=\"document.location='slider.php?slider=".$slider."&sllang=$sllang&slsite=$slsite'\"> <input type=\"submit\" value=\"Envoyer\"></p>
		</form>";
    }

    if($action=="modif") {
		$s=$DB_site2->query_first("SELECT * FROM slider WHERE id=".$_GET['id']);

		$optland = "<option value=\"\">...</option>";
		$req = $DB_site2->query("SELECT libelle".$sllang." AS libelle, url".$sllang." AS url FROM landing WHERE type='slider'");
		while($landing=$DB_site2->fetch_array($req)){
			$optland .= "<option value=\"/cave/".$landing['url'].".html\">".$landing['libelle']."</option>";
		}
		
		print "<form method=\"post\" enctype=\"multipart/form-data\">
		<input type=\"hidden\" name=\"slider\" value=\"".$slider."\">
		<input type=\"hidden\" name=\"langue\" value=\"".$sllang."\">
		<input type=\"hidden\" name=\"modif\" value=\"".intval($_GET['id'])."\">
		<input type=\"hidden\" name=\"action\" value=\"".$action."2\">
		<input type=\"hidden\" name=\"artid\" value=\"".$s['artid']."\">
		<h3>Modifier un slider</h3>";
		
		if($s['artid']>0) {
			$article=$DB_site2->query_first("SELECT artid,artcode,libelleFR,active FROM article WHERE artid=".$s['artid']);
			print "<img src=\"/configurations/".$host."/images/produits/".$article['artid'].".jpg\" width=\"100\" align=\"left\">
			".$article['libelleFR']."
			<br>".$article['artcode']."
			<br>".$actif[$article['active']]."<br>
			<div>
			Rechercher : <input type=\"text\" onkeyup=\"srcartid(this.value);\">
			<div id=\"results\"></div>
			</div>";
		}else{
			$imgpath="../configurations/".$host."/images/autopromo/".$s['id'].".jpg";
			$size=getimagesize($imgpath);
			print "<img src=\"$imgpath\">
			<br><small>".$size[0]."px x ".$size[1]."px</small>
			<div id=\"divimg\">
			<table>
			  <tr>
				<td>Changer l'image<br>(format 690 x 285px jpg) :</td>
				<td><input type=\"file\" name=\"img\"></td>
			  </tr>
			  <tr>
				<td>
					<select onchange=\"inputUrl(this.value);\">
						<option value=\"lien\">Lien personnalisé</option>
						<option value=\"land\">Lien landing slider</option>
					</select>
				</td>
				<td>
					<input type=\"text\" name=\"lien\" id=\"lien\"value=\"".$s['lien']."\" size=\"60\">
					<select name=\"land\" id=\"land\" style=\"display:none;\">".$optland."</select>
				</td>
			  </tr>
			  <tr>
				<td>Texte référencement :</td>
				<td><input type=\"text\" name=\"texte\" value=\"".$s['texte']."\" size=\"60\"></td>
			  </tr>
			</table>
			</div>";
		}

		list($pro,$part)=str_split(str_pad(decbin($s['pro']),2,0,STR_PAD_LEFT));
		//print "<p>Texte du bouton : <input type=\"text\" name=\"btn\" value=\"".$s['btn']."\" size=\"60\"></p>
		print "<p><input type=\"checkbox\" name=\"pro[]\" value=\"1\"".($part==1?" checked":"")."> Site particuliers
		<br><input type=\"checkbox\" name=\"pro[]\" value=\"2\"".($pro==1?" checked":"")."> Site pro</p>
		<p>Date de début : <input id=\"dbt\" name=\"dbt\" onclick=\"calendrier_show(this.id,this.value,true);\" size=\"14\" type=\"text\" value=\"".($s['date_dbt']!=0 ? date("d/m/Y H:i",$s['date_dbt']) : '')."\">
			<a href=\"javascript:cal_delete('dbt');\" class=\"cal_delete\" id=\"\">&nbsp;X&nbsp;</a></p>
		<p>Date de fin : <input id=\"fin\" name=\"fin\" onclick=\"calendrier_show(this.id,this.value,true);\" size=\"14\" type=\"text\" value=\"".($s['date_fin']!=0 ? date("d/m/Y H:i",$s['date_fin']) : '')."\">
			<a href=\"javascript:cal_delete('fin');\" class=\"cal_delete\" id=\"\">&nbsp;X&nbsp;</a></p>
		<br>
		<p><input type=\"button\" value=\"Retour\" onclick=\"document.location='slider.php?slider=".$slider."&sllang=$sllang&slsite=$slsite'\"> <input type=\"submit\" value=\"Envoyer\"></p>
		</form>";
    }

    if(!isset($action)){
		$req=$DB_site2->query("SELECT * FROM slider WHERE slidername='".$slider."' AND site_code='".$slsite."' AND langue='$sllang' ORDER BY position");
		$nb=$DB_site2->num_rows($req);

		print "<table class=\"table_moyen_paiement\">";
		print "<tr><td colspan=\"3\" align=\"center\"><a href=\"slider.php?slider=".$slider."&action=add&sllang=$sllang&slsite=$slsite\">Ajouter une image</a></td></tr>";

		while($s=$DB_site2->fetch_array($req)){
		    $up=$down="";
		    if($s['position']<$nb) $down="<a href=\"slider.php?action=down&id=$s[id]&slider=".$slider."&sllang=$sllang&slsite=$slsite\"><img border=\"0\" height=\"7\" title=\"Descendre\" src=\"images/descendre.gif\"></a>";
		    if($s['position']>1) $up="<a href=\"slider.php?action=up&id=$s[id]&slider=".$slider."&sllang=$sllang&slsite=$slsite\"><img border=\"0\" height=\"7\" title=\"Monter\" src=\"images/monter.gif\"></a>";
		    if(intval($s['artid'])==0){
			$img="<img src=\"../configurations/".$host."/images/autopromo/".$s['id'].".jpg\">";
		    }else{
			$article=$DB_site2->query_first("SELECT artid,artcode,libelleFR,active FROM article WHERE artid=".$s['artid']);
			$img="<img src=\"../configurations/".$host."/images/produits/".$s['artid'].".jpg\" height=\"285\" align=\"left\">".$article['libelleFR']."<br>".$article['artcode']."<br>".$actif[$article['active']];
		    }
		    
		    list($pro,$part)=str_split(str_pad(decbin($s['pro']),2,0,STR_PAD_LEFT));

		    print "<tr><td nowrap>$up $down</td>
			      <td>$img</td>
			      <td nowrap id=\"slider".$s[id]."\">
				  <a href=\"slider.php?action=modif&id=$s[id]&slider=".$slider."&sllang=$sllang&slsite=$slsite\"><img src=\"images/editer.gif\"></a>
				  <a href=\"slider.php?action=del&id=$s[id]&slider=".$slider."&sllang=$sllang&slsite=$slsite\"><img src=\"images/supprimer.gif\"></a>
				  <br><a href=\"slider.php?action=pro&id=$s[id]&slider=".$slider."&sllang=$sllang&slsite=$slsite&pro=".intval(abs($part-1)+2*$pro)."#slider$s[id]\" style=\"color:".($part==1?"green":"red").";\">Part</a>
				  <br><a href=\"slider.php?action=pro&id=$s[id]&slider=".$slider."&sllang=$sllang&slsite=$slsite&pro=".intval(2*abs($pro-1)+$part)."#slider$s[id]\" style=\"color:".($pro==1?"green":"red").";\">Pro</a>
				  <br><input type=\"checkbox\" value=\"1\"".($s['visible']==1?" checked":"")." onclick=\"document.location='slider.php?action=visible&id=$s[id]&slider=".$slider."&sllang=$sllang&slsite=$slsite&visible='+(this.checked?1:0)+'#slider$s[id]';\">Visible
				  <br><br>du ".($s['date_dbt']==0 ? '<b>/</b>' : "<b>".date("d/m/Y",$s['date_dbt'])."</b><br> <center>à ".date("H:i",$s['date_dbt'])."</center>")."
				  <br><br>au ".($s['date_fin']==0 ? '<b>/</b>' : "<b>".date("d/m/Y",$s['date_fin'])."</b><br> <center>à ".date("H:i",$s['date_fin'])."</center>")."
			      </td>
		    </tr>";
		}
		print "</table>";
   	}
}

include "./footer.php";

?>
