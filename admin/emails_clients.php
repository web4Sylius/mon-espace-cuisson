<?php
require "admin_global.php";
setcookie("hostname",$host,time()+3600,"/");
include "./header.php";
include "./menu.php";

echo "<h1>Personnaliser les e-mails clients</h1>" ;
echo "<script type=\"text/javascript\">
	function afficheMailArchive(){
		jQuery('#mailsarchive').toggle();
		jQuery('#titrearchive').find('span').toggleClass('fa-chevron-down');
		jQuery('#titrearchive').find('span').toggleClass('fa-chevron-up');
	};

	function change_fourn(){
		var libelle = jQuery('#emailcode option:selected').text();
		jQuery('input[name=libelle]').val(libelle);
	}

	//si service livraison, affiche la liste des install/trspt :
	function affich_select(){
		var service = jQuery('#service option:selected').val();
		if(service == 'livraison'){
			jQuery('#liste_select').show();
		}
		else{
			jQuery('#liste_select').hide();
		}
	}
</script>";

$filter_langue=array_diff($langues,$langue_access)!=array();

if (!isset($_GET['emailid'])){
	$contenu_onglet = "";
	echo "Personnalisez ici les e-mails qui sont envoyés automatiquement à vos clients :<br><br>";
	
	if(!$filter_langue) echo "<a href=\"emails_clients.php?emailid=0\">Créer un email</a><br><br>";

	print "<form name=\"search\" method=\"get\">
	<p>Site <select name=\"site_code\" onchange=\"document.forms.search.submit()\">";
	foreach($sites as $site_code=>$site)
	    print "<option value=\"".$site_code."\"".($_GET['site_code']==$site_code?" selected":"").">".$site_code."</option>";
	print "</select> ";
	
	$requete=$DB_site2->query("SELECT service FROM emails_clients GROUP BY service");
	while($em=$DB_site2->fetch_array($requete)) $services[]="<option value=\"$em[0]\"".($_GET['service']==$em[0]?" selected":"").">$em[0]</option>"; 
	print "Service : <select name=\"service\" onchange=\"document.forms.search.submit()\">".implode("",$services)."</select>
	</p>
	</form>";
      
	if(isset($_GET['service']) && isset($_GET['site_code'])){
		//mail  archivés
	    print "<div class=\"row \"><div class=\"col-xs-12\"><p id=\"titrearchive\" class=\"mailsarchive\" onclick=\"afficheMailArchive();\">Voir les mails Archivés <span class=\"fa fa-chevron-down\"></span></p></div></div>";
	    print "<div id=\"mailsarchive\" class=\"row\" style=\"display:none;\"><div class=\"col-xs-12\">";
	    $requete=$DB_site2->query("SELECT emailid,libelle FROM emails_clients WHERE site_code='".$_GET['site_code']."' AND service='".$_GET['service']."' AND archive = 1 ORDER BY libelle");
	    $i = 0;
	    while($em=$DB_site2->fetch_array($requete)){
	    	if($i % 2 == 0)
	    		$color = "style=\"background-color:#E0E0E0;\"";
	    	else
	    		$color = "style=\"background-color:white;\"";

	    	print ($i == 0?"":"<br>")."<div><a href=\"emails_clients.php?site_code=".$_GET['site_code']."&emailid=$em[emailid]\" class=\"col-xs-7\" $color>$em[libelle]</a><a href=\"emails_clients.php?site_code=".$_GET['site_code']."&emailid=$em[emailid]&archive=0&service=".$_GET['service']."\" class=\"col-xs-3\" $color>Désarchiver</a></div>";
	    	$i++;
	    }
	    print "<div class=\"col-xs-2\">".$contenu_onglet."</div>";
	    print "</div></div><br>";

		//mail non archivés
	    print "<div class=\"row\"><div class=\"col-xs-12\">";
	    $requete=$DB_site2->query("SELECT emailid,libelle FROM emails_clients WHERE site_code='".$_GET['site_code']."' AND service='".$_GET['service']."' AND archive = 0 ORDER BY libelle");
	    $i = 0;
	    while($em=$DB_site2->fetch_array($requete)){
	    	if($i % 2 == 0)
	    		$color = "style=\"background-color:#E0E0E0;\"";
	    	else
	    		$color = "style=\"background-color:white;\"";

	    	print "<br><div><a href=\"emails_clients.php?site_code=".$_GET['site_code']."&emailid=$em[emailid]\" class=\"col-xs-7\" $color>$em[libelle]</a><a href=\"emails_clients.php?site_code=".$_GET['site_code']."&emailid=$em[emailid]&archive=1&service=".$_GET['service']."\" class=\"col-xs-3\" $color>Archiver</a></div>";
	    	$i++;
	    }
	    print "<div class=\"col-xs-2\">".$contenu_onglet."</div>";
	    print "</div></div>";
	}
}
	
//$rootpath = "/home/arobases/www/vendre-en-ligne.fr/";


if (isset($_GET['emailid'])) {// Expediée

	if(isset($_GET['archive']) && intval($_GET['archive']) == 1){
		$DB_site2->query("UPDATE emails_clients SET archive = 1 WHERE emailid=".mysqli_real_escape_string($DB_site2->link_id,$_GET['emailid']))."";
		echo "<script language=\"javascript\">location.href=\"emails_clients.php?site_code=".$_GET['site_code']."&service=".$_GET['service']."\";</script>";
	}
	if(isset($_GET['archive']) && intval($_GET['archive']) == 0){
		$DB_site2->query("UPDATE emails_clients SET archive = 0 WHERE emailid=".mysqli_real_escape_string($DB_site2->link_id,$_GET['emailid']))."";
		echo "<script language=\"javascript\">location.href=\"emails_clients.php?site_code=".$_GET['site_code']."&service=".$_GET['service']."\";</script>";
	}

	if(isset($action3) and $action3 == "modifier"){

		if($_POST['newservice'] != "") $_POST['service']=$_POST['newservice'];
		
		$sql = "";
		foreach($langues as $langue){
			if(in_array($langue,$langue_access)){
				$sql .= ", sujet".$langue."   = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['sujet'.$langue])."'";
				$sql .= ", contenu".$langue." ='".mysqli_real_escape_string($DB_site2->link_id,$_POST['content'.$langue])."'";
			} 
		}

		if($_GET['emailid'] > 0){
		    
		    $sqlUpd = "UPDATE emails_clients SET";
		    $sqlUpd .= " service  		= '".$_POST['service']."'";
		    $sqlUpd .= ", libelle 		= '".mysqli_real_escape_string($DB_site2->link_id,$_POST['libelle'])."'";
		    $sqlUpd .= ", emailcode 		= '".mysqli_real_escape_string($DB_site2->link_id,$_POST['emailcode'])."'";
		    $sqlUpd .= ", email_relance = ".(isset($_POST['relance_rapide']) && $_POST['relance_rapide'] == 1 ? 1 : 0);
		    $sqlUpd .= $sql;
		    $sqlUpd .= " WHERE emailid = '".$_GET['emailid']."'";
		    $DB_site2->query($sqlUpd);

		} else {

			$sqlUpd = "INSERT INTO emails_clients SET";
			$sqlUpd .= " site_code 		= '".$_POST['site_code']."'";
			$sqlUpd .= ", service 		= '".$_POST['service']."'";
			$sqlUpd .= ", libelle 		= '".mysqli_real_escape_string($DB_site2->link_id,$_POST['libelle'])."'";
			$sqlUpd .= ", emailcode 		= '".mysqli_real_escape_string($DB_site2->link_id,$_POST['libelle'])."'";
			$sqlUpd .= ", email_relance = ".(isset($_POST['relance_rapide']) && $_POST['relance_rapide'] == 1 ? 1 : 0);
			$sqlUpd .= $sql;
		    $DB_site2->query($sqlUpd);

			echo "<script language=\"javascript\">location.href=\"emails_clients.php?site_code=".$_GET['site_code']."&service=".$_POST['service']."\";</script>";
		}
	}
	$actu=array();
	if($_GET['duplicate']>0) $actu=$DB_site2->query_first("select * from emails_clients where emailid = '".$_GET['duplicate']."'");
	if($_GET['emailid']>0) $actu=$DB_site2->query_first("select * from emails_clients where emailid = '".$_GET['emailid']."'");
	$content = $actu[$contact] ;
	$requete=$DB_site2->query("SELECT service FROM emails_clients GROUP BY service");
	while($em=$DB_site2->fetch_array($requete)) $services[]="<option value=\"$em[0]\"".($actu['service']==$em[0]?" selected":"").">$em[0]</option>";
	$liste_fourn = $DB_site2->query("SELECT fournisseur.societe, fournisseur.id FROM fournisseur LEFT OUTER JOIN fournisseur_condition fc USING(societe) WHERE (type = 'trsptadv' OR type='slsadv') AND fc.activite = '1' AND (fc.types LIKE '%transporteur%' OR fc.types LIKE '%installateur%') GROUP BY societe ORDER BY societe");
	while($fourn_libell=$DB_site2->fetch_array($liste_fourn)) $fourns[]="<option value=\"$fourn_libell[1]\"".($actu['libelle']==$fourn_libell[0]?" selected":"").">$fourn_libell[0]</option>";

	print "<form method=\"post\">
		<h3>$actu[libelle]</h3> ".($_GET['emailid']>0 && !$filter_langue?"<a href=\"emails_clients.php?emailid=0&duplicate=".$_GET['emailid']."\">Dupliquer</a>":"")."
		<input type=\"hidden\" name=\"action3\" value=\"modifier\">";
		
		if($_GET['emailid']==0){
		    print "<p>Site : <select name=\"site_code\">";
		    foreach($sites as $site_code=>$site) print "<option value=\"".$site_code."\">".$site_code."</option>";
		    print "</select></p>";
		}
		
		if($filter_langue){
	    	print ""
	    	. "<input type=\"hidden\" name=\"service\" value=\"".$actu['service']."\">"
	    	. "<input type=\"hidden\" name=\"libelle\" value=\"$actu[libelle]\">";
		} else {
			print ""
	    	. "<p>Service : "
	    		. "<select id=\"service\" name=\"service\" onchange=\"document.getElementById('newservice').value='';\" onclick=\"affich_select()\">"
	    		. implode("",$services)
	    		. "</select>"
	    		. " ou autre <input type=\"text\" name=\"newservice\" id=\"newservice\" size=\"10\" onchange=\"document.getElementsByName('service')[0].value='';\">"
    		. "</p>"
    		. "<p id=\"liste_select\" style=\"display:none;\">Liste transporteurs/installateurs : "
    		. "<select name=\"emailcode\" id=\"emailcode\" onclick=\"change_fourn();\">
    		<option value=\"\"></option>"
	    		. implode("",$fourns)
	    		. "</select>"
	    	."</p>"
	    	. "<p>Nom email : <input type=\"text\" name=\"libelle\" size=\"40\" value=\"$actu[libelle]\"></p>"
	    	. "<p>emailcode (dev) : <input type=\"text\" name=\"emailcode\" size=\"40\" value=\"$actu[emailcode]\"></p>"
	    	. "<p><input type=\"checkbox\" name=\"relance_rapide\" value=\"1\" ".($actu['email_relance'] == 1 ? 'checked' : '')."> Email de relance rapide (sans personnalisation à apporter au contenu)</p>";
		}
	
	foreach($langues as $langue){
		$flag=$langue;
		if($langue=="EN") $flag="GB";
		if($langue=="SP") $flag="ES";
		
		$readonly=!in_array($langue,$langue_access);
		
		echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> Sujet $langue : ".($readonly?$actu["sujet".$langue]:"<input type=\"text\" name=\"sujet$langue\" size=\"40\" value=\"".$actu["sujet".$langue]."\">")."</p>";
				
		if($readonly)
		  echo $actu['contenu'.$langue];
		else
		  echo "<textarea name=\"content$langue\" >".$actu['contenu'.$langue]."</textarea>";

		echo "<hr><br><script>CKEDITOR.replace('content$langue',{uiColor: '#9AB8F3', extraPlugins: ['justify','sourcearea','div','image','richcombo','panel','floatpanel','font']});</script>";
	}

	print "<p align=\"center\"><input type=\"submit\" value=\"Modifier\"></p>
	  <p align=\"center\"><a href=\"emails_clients.php?site_code=".$actu['site_code']."&service=".$actu['service']."\" target=\"_self\">Retour</a></p>
	  </form>";

	}


include "./footer.php";
?>