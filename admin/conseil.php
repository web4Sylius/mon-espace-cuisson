<?php
require "admin_global.php";
setcookie("hostname",$host,time()+3600,"/");
include "./header.php";
include "./menu.php";
include "../class/fonctions.php";

$superusers=array("cc","philippe","benoit", "ariel","jessica","jbandre");
$filter_langue=array_diff($langues,$langue_access)!=array();
$bloc = "bloc";
if(isset($_GET['version'])) {
	$table_conseil 			= "conseil_".$_GET['version'];
	$table_conseil_bloc 	= "conseil_bloc_".$_GET['version'];
	$table_conseil_article 	= "conseil_article_".$_GET['version'];
	if($_GET['version'] == 'pro') $bloc = "blocpro";
}

if(isset($_POST['consid'])){
	$considpage=$DB_site2->query_first("SELECT conseil_articleid_page FROM $table_conseil_article WHERE conseilid=".intval(htmlspecialchars($conseilid))." ORDER BY conseil_articleid_page DESC");
	$consid=$DB_site2->query_first("SELECT conseil_articleid_page FROM $table_conseil_article WHERE artid =".$_POST['consid']." AND conseilid=".intval(htmlspecialchars($conseilid)) );
	$existe_artid = $DB_site2->query_first("SELECT * FROM $table_conseil_article WHERE conseilid=".intval(htmlspecialchars($conseilid))." AND artid=".$_POST['artid']);
	if (!$existe_artid) {
			if($considpage && $consid ){
				//Si il y a déja 12 articles enregistrés sur cette page
				$DB_site2->query("UPDATE $table_conseil_article SET artid=".$_POST['artid']." WHERE artid=".$_POST['consid']." AND conseilid=".intval(htmlspecialchars($conseilid)));
			}elseif($considpage && !$consid ){
				//Si il n'y a pas encore les 12 articles selectionnes sur cette page
				$ordre = $DB_site2->query_first("SELECT COUNT(*) from $table_conseil_article WHERE conseilid=".intval(htmlspecialchars($conseilid))." ORDER By ordre ASC");
				$ordre = intval($ordre[0]) + 1 ;
				$DB_site2->query("INSERT INTO $table_conseil_article (conseil_articleid_page, artid, conseilid, ordre) VALUES ( ".($considpage[0]+1)." ,".$_POST['artid']." , ".intval(htmlspecialchars($conseilid))." ,".$ordre.")");
			}elseif(!$considpaged && !$consid){
				//Si aucun article n'a encore été selectionné sur cette page
				$DB_site2->query("INSERT INTO $table_conseil_article (conseil_articleid_page, artid, conseilid, ordre) VALUES (1, ".$_POST['artid']." , ".intval(htmlspecialchars($conseilid))." ,1)");
			}	
		
	}else{

		print "<script type=\"text/javascript\">
		alert('Attention cet article est déjà rajouté'); 
		</script>";
	}
}

echo "
	<style>
  	#articles_table li:hover{cursor:pointer;}
 	#modif {display:none;}
 	#articles_table ul{}
 	#articles_table li{display:inline-block;width:300px;height:185px;border:1px black solid;margin:1px;}
	</style> 
	<script type=\"text/javascript\">
	\$=\$jQuery;";

?>
	$(document).ready(function () {
	    $('#articles_table').sortable({
	    	placeholder: 'placeholder',
	        stop: function (event, ui) {
		        var sortable_data = $(this).sortable('serialize');
		        var conseilid = $('#idpage').val();
		        var version = $('#version').val();
	           //$('#articles_table').disableSelection();
	            $.ajax({ 
				    data: sortable_data+'&idpage='+conseilid+'&version='+version,
				    type: 'POST',
				    url: '/admin/conseil_script.php', // save.php - file with database update
				    //success:function(html){alert(html);}
				  });    
	            
		}
	    });
	});

<?php

echo "
	function selectcdc(id){
      
      document.getElementsByName('consid')[0].value=id;
      document.getElementById('modif').style.display='block';
  	}

  	  function srcartid(src){
               if(src.length<=3) return;
               var url=\"marketplace/src.php?txt=\"+src+\"&active=1&site_code=\"+document.getElementsByName('site_code')[0].value;
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
                    html+='<table width=\"100%\" style=\"border:solid thin #808080;\" class=\"txt\"><tr>';
                    for(i=0;i<xml.getElementsByTagName('prod').length;i++){
                         var description=xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
                         var id=xml.getElementsByTagName('prod')[i].getAttribute('id');
                         var artid=xml.getElementsByTagName('prod')[i].getAttribute('artid');
			 var actif=xml.getElementsByTagName('prod')[i].getAttribute('active_'+'site_code');
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
  	console.log(artid);
      document.getElementsByName('artid')[0].value=artid;
      document.forms.modifconseilart.submit();
  }

  function valideform(){
  	document.forms.form_institutionnel.submit();
  }
  function editbloc(blocid){
  	$('bloc_'+blocid).click(function(){
  		$('this').append('<textarea id=\"contenu_bloc_'+blocid+'\" class=\"ckeditor\"></textarea>');

  	});
  	
  }
  function validComBloc(blocid, version){
  	var commentaire = $('#bloc_com_'+blocid).val();
  	$.ajax({
  			url: 'conseil_com.php',
  			type: 'GET',
  			data: 'blocid='+blocid+'&contenu=' + commentaire + '&version=' + version,
  			dataType: 'html'
  		});
  }
  </script>";

echo "<input type=\"hidden\" id=\"idpage\" value=\"$conseilid\">";

echo "<h1>Gestion des conseils d'expert</h1>" ;

$optsite="";
if(!isset($_GET['site_code'])) $_GET['site_code'] = "base";
if(!isset($_GET['version'])) $optsite = "<option></option>";

$optsite.="<option value=\"part\"".($_GET['version']=="part"?" selected":"").">part</option>";
$optsite.="<option value=\"pro\"".($_GET['version']=="pro"?" selected":"").">pro</option>";

echo "<p>Site : <select id=\"version\" onchange=\"document.location='conseil.php?version='+this.value\">".$optsite."</select></p>";

////////////////////////////////////////////////////////////////////////////////////////////////////////
// MONTER D'UNE POSITION
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( isset( $action ) && $action == "up" )
{
	$i_position = 1;
	$position = $DB_site2->query_first( "SELECT position FROM $table_conseil
										 WHERE conseilid = '$conseilid'" );						
	$nouvelle_position_page = $position[0]-1;
	$position2 = $DB_site2->query_first( "SELECT conseilid FROM $table_conseil
									  	  WHERE position = '$nouvelle_position_page' AND conseilparent = '$conseilparent' AND site_code='".$_GET['site_code']."'" );
	$rq_modifier_position = "UPDATE $table_conseil SET position='$nouvelle_position_page' 
							 WHERE conseilid = '$conseilid'";
	$DB_site2->query( $rq_modifier_position );
	$rq_modifier_position2 = "UPDATE $table_conseil SET position='$position[0]' 
							  WHERE conseilid = '$position2[conseilid]'";
	$DB_site2->query( $rq_modifier_position2 );

	$action = "";	
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// DESCENDRE D'UNE POSITION
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( isset( $action ) && $action == "down" )
{
	$i_position = 1;
	$position = $DB_site2->query_first( "SELECT position FROM $table_conseil WHERE conseilid = '$conseilid'" );						
	$nouvelle_position_page = $position[0]+1;
	$position2 = $DB_site2->query_first( "SELECT conseilid FROM $table_conseil WHERE position = '$nouvelle_position_page'  AND conseilparent = '$conseilparent' AND site_code='".$_GET['site_code']."'");
	$rq_modifier_position = "UPDATE $table_conseil SET position='$nouvelle_position_page' WHERE conseilid = '$conseilid'";
	$DB_site2->query( $rq_modifier_position );
	$rq_modifier_position2 = "UPDATE $table_conseil SET position='$position[0]' WHERE conseilid = '$position2[conseilid]'";
	$DB_site2->query( $rq_modifier_position2 );

	$action = "";	
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AFFICHER LES PAGES INSTITUTIONNELLES
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( (!isset( $action ) || $action == "") && isset($_GET['site_code']) && isset($_GET['version']))
{
	$wysiwyg_suppr = $DB_site2->query( "DELETE FROM wysiwyg" );
	$i_conseil = 0; // Compte le nombre de pages institutionnelles
	$rq_conseil = $DB_site2->query( "SELECT * FROM $table_conseil WHERE conseilparent=0 AND site_code='".$_GET['site_code']."' ORDER BY position ASC" );
	echo "<h3>Conseils existants</h3>
		<table class=\"table_modules_boutiques\">";
	$nb_pages = $DB_site2->num_rows( $rq_conseil );
	if ($nb_pages > 1)
		{
		echo "<tr>
					<td align=\"center\"><b>Titre</b></td>
					<td align=\"center\" width=\"25%\"><b>Action</b></td>
			  </tr>";
		}
	while  ($rs_conseil=$DB_site2->fetch_array($rq_conseil)) 
		{
		$rowalt = "td_".getrowbg();
		$i_conseil++;
		if ($nb_pages > 1 && !$filter_langue)
			{
			if ($i_conseil == 1) // Première page
				{
				$images_position = "<img src=\"images/vide.gif\" height=\"7\">
									<a href=\"conseil.php?action=down&conseilid=$rs_conseil[conseilid]&conseilparent=$rs_conseil[conseilparent]&site_code=".$_GET['site_code']."&version=".$_GET['version']."\">
									<img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>";
				}
			elseif( $i_conseil == $nb_pages ) // Dernière page
				{
				$images_position = "<a href=\"conseil.php?action=up&conseilid=$rs_conseil[conseilid]&conseilparent=$rs_conseil[conseilparent]&site_code=".$_GET['site_code']."&version=".$_GET['version']."\">
									<img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>
									<img src=\"images/vide.gif\" height=\"7\">";
				}
			else
				{
				$images_position = "<a href=\"conseil.php?action=up&conseilid=$rs_conseil[conseilid]&conseilparent=$rs_conseil[conseilparent]&site_code=".$_GET['site_code']."&version=".$_GET['version']."\">
									<img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>
									<a href=\"conseil.php?action=down&conseilid=$rs_conseil[conseilid]&conseilparent=$rs_conseil&site_code=".$_GET['site_code']."&version=".$_GET['version']."\">
									<img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>";
				}
			}
		echo "<tr><td >$images_position $rs_conseil[$libelle]</td>";

		$suppr="";
		if(in_array($user_info['username'],$superusers)) $suppr="| <a href=\"conseil.php?action=supprimer&conseilid=$rs_conseil[conseilid]&version=$_GET[version]\" target=\"_self\">Supprimer</a>";
		
		$voir="| ";
		foreach($sites[$_GET['site_code']]['hosts'] as $url=>$l)
		    $voir.="<a href=\"http://www.$url/conseil,$rs_conseil[conseilid],".url_rewrite($rs_conseil['libelle'.$l]).".htm\" target=\"_blank\">$l</a>&nbsp;";
		    
		
		echo "
			  <td  align=\"center\" width=\"20%\">
				  <a href=\"conseil.php?action=editer&conseilid=$rs_conseil[conseilid]&site_code=".$_GET['site_code']."&version=".$_GET['version']."\" target=\"_self\">Editer</a>
				  $suppr
				  $voir
			  </td>
			</tr>";
			
		$i=0;
		$req=$DB_site2->query("SELECT * FROM $table_conseil WHERE conseilparent=".$rs_conseil['conseilid']." ORDER BY position");
		while($child=$DB_site2->fetch_array($req)){
		    $i++;
		    
		    if($i>1) 
		      $images_position = "<a href=\"conseil.php?action=up&conseilid=$child[conseilid]&conseilparent=$child[conseilparent]&site_code=".$_GET['site_code']."&version=".$_GET['version']."\"><img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>";
		    else
		      $images_position = "<img src=\"images/vide.gif\" height=\"7\">";
		      
		    if($i<$DB_site2->num_rows($req))
		      $images_position .= " <a href=\"conseil.php?action=down&conseilid=$child[conseilid]&conseilparent=$child[conseilparent]&site_code=".$_GET['site_code']."&version=".$_GET['version']."\"><img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>";
		    else
		      $images_position .= "<img src=\"images/vide.gif\" height=\"7\">";
		    
		    $suppr="";
		    if(in_array($user_info['username'],$superusers)) $suppr="| <a href=\"conseil.php?action=supprimer&conseilid=$child[conseilid]&site_code=".$_GET['site_code']."&version=".$_GET['version']."\" target=\"_self\">Supprimer</a>";
		      
		    $voir="| ";
		    foreach($sites[$_GET['site_code']]['hosts'] as $url=>$l)
			$voir.="<a href=\"http://www.$url/conseil,$child[conseilid],".url_rewrite($child['libelle'.$l]).".htm\" target=\"_blank\">$l</a>&nbsp;";
		    
		    echo "<tr>
		      <td>____ ".$images_position." ".$child['libelleFR']."</td>
		      <td  align=\"center\" width=\"20%\">
				  <a href=\"conseil.php?action=editer&conseilid=$child[conseilid]&site_code=".$_GET['site_code']."&version=".$_GET['version']."\" target=\"_self\">Editer</a>
				  $suppr
				  $voir
			  </td>
		    </tr>";
		}
		
		}
		
	
	if( $i_conseil == 0 )
		echo "<tr><td align=\"center\"><br>Il n'y a aucun conseil</td></tr>";
	
	echo "</table>";
	
	if(!$filter_langue)
	    echo "<br><a href=\"conseil.php?action=ajouter&site_code=".$_GET['site_code']."&version=".$_GET['version']."\" target=\"_self\">Ajouter un conseil</a>";

////////////////////////////////////////////////////////////////////////////////////////////////////////
// EDITER un conseil INSTITUTIONNELLE (action=editer)
////////////////////////////////////////////////////////////////////////////////////////////////////////
} elseif( $action == "editer" && isset( $conseilid ) ){
	if(isset($_POST['conseilid'])){
		$rq_modifier_institutionnel = "conseilparent='".$_POST['conseilparent']."'";
		foreach($langues as $langue) {
		    if(in_array($langue,$langue_access)) {
				$rq_modifier_institutionnel.=", contenu$langue='".mysqli_real_escape_string($DB_site2->link_id,$_POST['content'.$langue])."', libelle$langue='".mysqli_real_escape_string($DB_site2->link_id,$_POST['libelle'.$langue])."', ref_title$langue='".mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_title'.$langue])."', ref_description$langue='".mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_description'.$langue])."'";
				$rq_modifier_institutionnel.=", ref_question".$langue."='".mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_question'.$langue])."', ref_reponse".$langue."='".mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_reponse'.$langue])."' ";
		    }
		}	
		if($_POST['conseilid']==0){
		    $max=$DB_site2->query_first("SELECT MAX(position) FROM $table_conseil WHERE site_code='".$_GET['site_code']."' AND conseilparent=".$_POST['conseilparent']);
		    $rq_modifier_institutionnel="INSERT INTO $table_conseil SET site_code='".$_GET['site_code']."', position=".($max[0]+1).",".$rq_modifier_institutionnel;
		    $DB_site2->query($rq_modifier_institutionnel);
		    $conseilid=$DB_site2->insert_id();
		}else{
		    $rq_modifier_institutionnel="UPDATE $table_conseil SET ".$rq_modifier_institutionnel." WHERE conseilid = ".$_POST['conseilid'];
		    $DB_site2->query($rq_modifier_institutionnel);
		    $conseilid=$_POST['conseilid'];
		}
	}
	/************** supprimer un bloc ******************/
	if(isset($_GET['suppr2'])){
		$path = "../configurations/".$host."/images/".$bloc."/";
		$blocid = $_GET['blocid'];
		$conseilid = $_GET['conseilid'];
		$l = $_GET['lg'];
		$position_bloc = $DB_site2->query_first("SELECT ordre, image FROM $table_conseil_bloc WHERE conseil_blocid='".$blocid."'");
		if(is_null($position_bloc[0])) exit;
		$image = $position_bloc['image'];
		$position_bloc = $position_bloc['ordre'];
		$autres_blocs = $DB_site2->query("SELECT conseil_blocid, ordre FROM $table_conseil_bloc WHERE conseilid='$conseilid' AND langue='$l' AND ordre > $position_bloc ORDER BY ordre ASC");
		while ($req_autres_blocs = $DB_site2->fetch_array($autres_blocs)) {
			$position_temp = intval($req_autres_blocs['ordre']) - 1 ;
			$DB_site2->query("UPDATE $table_conseil_bloc SET ordre = $position_temp WHERE conseil_blocid =".$req_autres_blocs['conseil_blocid']);
		}

		$DB_site2->query("DELETE FROM $table_conseil_bloc WHERE conseil_blocid=".$blocid);
		if(!is_null($image)) unlink($path.$blocid.".".$image);
	}
	/******* AJOUT BLOC CONTENU POST  *********************************************************/
	if (isset($_POST['ajout_content'])) {
		$conseilid = $_POST['ajout_content'];
		$typeid =$_POST['ajout_type'];
		if(isset($_POST['alt_img'])) $alt_img = mysqli_real_escape_string($DB_site2->link_id,$_POST['alt_img']);
		$req = $DB_site2->query_first("SELECT conseil_blocid FROM $table_conseil_bloc ORDER BY conseil_blocid DESC LIMIT 1");
		$blocid = intval($req[0])+1;
		$l = mysqli_real_escape_string($DB_site2->link_id,$_POST['ajout_lg']);
		$extensions_valides = array( 'jpg','png' );
		$path = "../configurations/".$host."/images/".$bloc."/";
		$maxsize = 10485760;
		$maxwidth= 430;
		$maxheight=280;
		$image = '';
		$bloc_content='';
		if($typeid == 6){
			$maxwidth = $maxwidth * 2.3;
			$maxheight = $maxheight * 2.3;
		}
		//on check la photo
		if(isset($_FILES['ajout_image']) && $_FILES['ajout_image']['size'] != 0){
			$extension_upload = strtolower(pathinfo($_FILES['ajout_image']['name'], PATHINFO_EXTENSION));
			if ( in_array($extension_upload,$extensions_valides) ){ //on check le type de photo
				if ($_FILES['ajout_image']['size'] < $maxsize){ // on check la taille de la photo
					$image_sizes = getimagesize($_FILES['ajout_image']['tmp_name']);
						$widthDest = $image_sizes[0];
						$heightDest = $image_sizes[1];
						if ($heightDest > $maxwidth) $widthDest = $maxwidth;
						if ($heightDest > $maxheight) $heightDest = $maxheight;
						$nom = $path.$blocid.".".$extension_upload;
						//on copie la photo et on la resize dans le bon répertoire
						$upload = resizeImage($_FILES['ajout_image']['tmp_name'],$widthDest,$heightDest,$nom); 
						if($upload) $image = $extension_upload;
				}else{
					echo "<script>alert('attention le fichier est trop lourd');</script>";
				}

			}else{
				if($_FILES['ajout_image']['size'] != '0')
					echo "<script>alert('attention le type de fichier n\'est pas autorisé');</script>";
			}		
		}else{
			$image = null;
		}
		if(isset($_POST['bloc_new']))
			$bloc_content = mysqli_real_escape_string($DB_site2->link_id,$_POST['bloc_new']);//on récupère le contenu text

		//on recherche l'ordre du bloc
		$ordre = $DB_site2->query_first("SELECT ordre FROM $table_conseil_bloc WHERE conseilid ='".$conseilid."' AND langue ='".$l."' ORDER BY ordre DESC LIMIT 1");
		$new_ordre = intval($ordre[0])+1;

		if($bloc_content == '' && $image == null){
			echo "<script>alert('Attention le bloc est vide, celui-ci ne sera pas enregistré');</script>";
			header('Location:'.BACK_URL.'/conseil.php?action=editer&conseilid='.$conseilid.'&site_code=base'.'&version='.$_GET['version'],false);
			exit;
		}else{
			//on insère le contenu dans la base
			if($image=='')
				$DB_site2->query("INSERT INTO $table_conseil_bloc (conseil_blocid,conseilid,typeid,contenu,langue,ordre) VALUES ($blocid,$conseilid,$typeid,'$bloc_content','$l',$new_ordre); ");
			else
				$DB_site2->query("INSERT INTO $table_conseil_bloc (conseil_blocid,conseilid,typeid,image,contenu,langue,ordre,alt_img) VALUES ($blocid,$conseilid,$typeid,'$image','$bloc_content','$l',$new_ordre,'$alt_img'); ");
		}
	}

	/********** changer l'ordre des blocs de contenu  **********************/
	if(isset($_GET['upbloc'])){
		$blocid = htmlspecialchars($_GET['blocid']);
		$l = htmlspecialchars($_GET['lg']);
		$conseilid = htmlspecialchars($_GET['conseilid']);
		$positionBloc = $DB_site2->query_first("SELECT ordre FROM $table_conseil_bloc WHERE conseil_blocid='".$blocid."'");
		$position1 = $positionBloc['ordre'];
		$new_position = intval($position1) - 1;
		$changed = $DB_site2->query_first("SELECT conseil_blocid FROM $table_conseil_bloc WHERE langue='".$l."' AND ordre = ".$new_position." AND conseilid =".$conseilid);
		if(!is_null($changed[0])){
			$DB_site2->query("UPDATE $table_conseil_bloc SET ordre ='".$new_position."' WHERE conseil_blocid ='".$blocid."'");
			$DB_site2->query("UPDATE $table_conseil_bloc SET ordre ='".$position1."' WHERE conseil_blocid =".$changed[0]);
		}

	}
	if(isset($_GET['downbloc'])){
		$blocid = htmlspecialchars($_GET['blocid']);
		$l = htmlspecialchars($_GET['lg']);
		$conseilid = htmlspecialchars($_GET['conseilid']);
		$positionBloc = $DB_site2->query_first("SELECT ordre FROM $table_conseil_bloc WHERE conseil_blocid=".$blocid);
		$position1 = $positionBloc['ordre'];
		$new_position = $position1 + 1;
		$changed = $DB_site2->query_first("SELECT conseil_blocid FROM $table_conseil_bloc WHERE langue='".$l."' AND ordre = ".$new_position." AND conseilid =".$conseilid);
		if(!is_null($changed[0])){
			$DB_site2->query("UPDATE $table_conseil_bloc SET ordre ='".$new_position."' WHERE conseil_blocid ='".$blocid."' ");
			$DB_site2->query("UPDATE $table_conseil_bloc SET ordre ='".$position1."' WHERE conseil_blocid =".$changed[0]);
		}
	}

	//$rootpath = "/home/arobases/www/vendre-en-ligne.fr/";
	echo "<a href=\"/admin/conseil.php?site_code=base&version=".$_GET['version']."\" style=\"float:right;\">Retour</a>";
	echo "<p style=\"color:red;font-size:25px;font-weight:600;\">ATTENTION , POUR MODIFIER LE CONTENU DE CETTE PAGE VOUS DEVEZ AVOIR RECU AU PREALABLE UNE FORMATION DU SERVICE TECHNIQUE   !!!</p>";
	
	echo "<div align=\"center\"><h3>Modifier un conseil</h3></div>";
	
	$rs_conseil = $DB_site2->query_first( "select * FROM $table_conseil where conseilid = $conseilid" );		
	
	echo "<form method=\"post\" name=\"form_institutionnel\" action=\"conseil.php?action=editer&conseilid=".$conseilid."&site_code=".$_GET['site_code']."&version=".$_GET['version']."\">";

	
	if ($rs_conseil[conseilparent] == "0")
		$liste_parents = "<option value=\"0\" selected>Aucun parent</>";
	else
		$liste_parents = "<option value=\"0\">Aucun parent</>";
	$parents = $DB_site2->query("SELECT * FROM $table_conseil WHERE conseilparent = '0' AND site_code='".$_GET['site_code']."'");
	while( $parent = $DB_site2->fetch_array( $parents ) )
		{
		if ($parent[conseilid] == $rs_conseil[conseilparent])
			$liste_parents .= "<option value=\"$parent[conseilid]\" selected>$parent[$libelle]</>";
		else
			$liste_parents .= "<option value=\"$parent[conseilid]\">$parent[$libelle]</>";
		}
	$select_parent = "<select name=\"conseilparent\">$liste_parents</select>";	
	
	if($filter_langue)
	    echo "<input type=\"hidden\" name=\"conseilparent\" value=\"$rs_conseil[conseilparent]\">";
	else
	    echo "<p>CONSEIL PARENT : $select_parent</p>";

	$pathImgBloc = "../configurations/".$host."/images/".$bloc."/";

	foreach($langues as $langue){
	    $flag=$langue;
	   	//récupération des blocs //
		$blocs = $DB_site2->query("SELECT conseil_blocid,conseilid,typeid,image ,contenu, ordre, commentaire, bloc_libelle as title FROM $table_conseil_bloc as cb INNER JOIN conseil_bloc_type as cbt ON cb.typeid=cbt.cbtid WHERE conseilid =".$conseilid." AND langue='".$langue."' ORDER BY ordre");

		//on cherche la première position
		$first_position = $DB_site2->query_first("SELECT ordre FROM $table_conseil_bloc WHERE conseilid=".$conseilid." AND langue='".$langue."' ORDER BY ordre ASC LIMIT 1");
		$first_position = intval($first_position[0]);
		if($first_position != ''){
			//on cherche la dernière position
			$last_position = $DB_site2->query_first("SELECT ordre FROM $table_conseil_bloc WHERE conseilid=".$conseilid." AND langue='".$langue."' ORDER BY ordre DESC LIMIT 1");
			$last_position = intval($last_position[0]);
		}else{
			$first_position = 0;
		}

	    if($langue=="EN") $flag="GB";
	    if($langue=="SP") $flag="ES";
	    $readonly=!in_array($langue,$langue_access);

	    //visualisation de la page sur le site en fonction des conseils pro ou part
	    if($_GET['version'] == 'pro'){
	    	if(SITE_PREFIX == 'localhost'){
	    		if($langue=="EN"){
	    			$domain = "http://localhost.".$_GET['version'].".my-wine-cabinet.com";
	    		}
	    		else{
	    			$domain = "http://localhost.".$_GET['version'].".ma-cave-a-vin.fr";
	    		}
		    }elseif (SITE_PREFIX == 'test') {
		    	if($langue=="EN"){
	    			$domain = "http://test.".$_GET['version'].".my-wine-cabinet.com";
	    		}
	    		else{
	    			$domain = "http://test".$_GET['version'].".ma-cave-a-vin.fr";
	    		}
		    }else{
		    	if($langue=="EN"){
	    			$domain = "https://".$_GET['version'].".my-wine-cabinet.com";
	    		}
	    		else{
	    			$domain = "https://".$_GET['version'].".ma-cave-a-vin.fr";
	    		}
	    	}
	    }else{
	    	$domain = "";
	    }

	    $lien = "<a href=\"".$domain."/conseil,$conseilid,".url_rewrite($rs_conseil['libelle'.$langue]).".htm\" target=\"_blank\" style=\"font-weight:700;font-size:15px;color:black;float:right;\">Voir la page sur le site $langue</a>&nbsp;";
	    echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> Titre de la page $langue : ".($readonly?$rs_conseil['libelle'.$langue]:"<input type=\"text\" name=\"libelle$langue\" value=\"".$rs_conseil['libelle'.$langue]."\" size=\"60\">")." $lien</p>";

	    echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> CONTENU $langue -- CREATION / MODIFICATION DES BLOCS</p>" ;
	    echo "<p>Liste des Blocs existants :</p>";
	    if($DB_site2->num_rows($blocs) != 0){
		    while($leBloc = $DB_site2->fetch_array($blocs)){
		    	if($leBloc['image'] != '' )
		    		$filemtime = filemtime($pathImgBloc.$leBloc['conseil_blocid'].".".$leBloc['image']);

	    		echo "<div class=\"row\"><div class=\"col-xs-1\" >";
	    		if(intval($leBloc['ordre']) != $first_position){
	    			echo "<a href=\"conseil.php?action=editer&upbloc=1&blocid=".$leBloc['conseil_blocid']."&lg=".$langue."&conseilid=".$conseilid."&site_code=base&version=".$_GET['version']."\"><img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>";
	    		}
	    		if(intval($leBloc['ordre']) != $last_position){
	    			echo "<a href=\"conseil.php?action=editer&downbloc=1&blocid=".$leBloc['conseil_blocid']."&lg=".$langue."&conseilid=".$conseilid."&site_code=base&version=".$_GET['version']."\"><img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>";
	    		}
	    		echo "</div>";
	    		echo "<div class=\"col-xs-4\"><p id=\"bloc_".$leBloc['conseil_blocid']."\" style=\"display:inline-block\"><strong>".$leBloc['ordre']."</strong> - ".$leBloc['title']." </p></div><div class=\"col-xs-1\"><a href=\"editbloc.php?modif=1&blocid=".$leBloc['conseil_blocid']."&conseilid=".$conseilid."&lg=$langue&version=".$_GET['version']."\">Editer</a></div><div class=\"col-xs-2\"><a href=\"editbloc.php?suppr=1&blocid=".$leBloc['conseil_blocid']."&conseilid=".$conseilid."&lg=$langue&version=".$_GET['version']."\">supprimer</a></div> <div class=\"col-xs-1\"><a onclick=\"apercuBloc('".$leBloc['conseil_blocid']."')\">Voir</a></div><div class=\"col-xs-3\"><textarea id=\"bloc_com_".$leBloc['conseil_blocid']."\" onchange=\"validComBloc('".$leBloc['conseil_blocid']."','".$_GET['version']."')\">".$leBloc['commentaire']."</textarea></div><br>";
	    		echo "<div class=\"row\" style=\"margin:0;\"><div id=\"apercu_bloc_".$leBloc['conseil_blocid']."\" class=\"col-xs-12 voir_bloc\" style=\"display:none;\">";
	    		switch ($leBloc['typeid']) {
	    			case '1':
	    				$contenu_apercu = "<div class=\"col-xs-6\">".$leBloc['contenu']."</div><div class=\"col-xs-6\"><img class=\"img-responsive\" src=\"".$pathImgBloc.$leBloc['conseil_blocid'].".".$leBloc['image']."?".$filemtime."\"></div>";
	    				break;
	    			case '2':
	    				$contenu_apercu = "<div class=\"col-xs-6\"><img class=\"img-responsive\" src=\"".$pathImgBloc.$leBloc['conseil_blocid'].".".$leBloc['image']."?".$filemtime."\"></div><div class=\"col-xs-6\">".$leBloc['contenu']."</div>";
	    				break;
	    			case '3':
	    			case '4':
	    			case '5':
	    			case '7':
	    			case '9':
	    				$contenu_apercu = "<div class=\"col-xs-offset-3 col-xs-6\">".$leBloc['contenu']."</div>";
	    				break;			
	    			case '6':
	    				$contenu_apercu = "<div class=\"col-xs-offset-3 col-xs-6\"><img class=\"img-responsive\" src=\"".$pathImgBloc.$leBloc['conseil_blocid'].".".$leBloc['image']."?".$filemtime."\"></div>";
	    				break;
	    			case '8':
	    				$contenu_apercu = "<div class=\"col-xs-offset-3 col-xs-6\"><a class=\"bouton-bloc\">".$leBloc['contenu']."</a></div>";
	    				break;
	    		}
	    		echo $contenu_apercu;
	    		echo "</div></div></div>";
		    }
		}else{
			echo "<p>Aucun contenu pour cette langue</p>";
		}
	    echo "<br><br>";

	    echo "<div class=\"row\"><p>Ajouter un nouveau bloc :</p></div>";
	    $typeBloc = $DB_site2->query("SELECT cbtid,bloc_libelle as title FROM conseil_bloc_type");
	    $selectbloc = "<select id=\"typeid_".$langue."\"><option value=\"0\">Choisissez un type de Bloc </option>";
	    //$lstTypebloc="<ul>";
	    while($desBloc = $DB_site2->fetch_array($typeBloc)){
	    	$selectbloc .="<option value=\"".$desBloc['cbtid']."\">".$desBloc['title']."</option>";
	    	//$lstTypebloc.= "<li><a href=\"editbloc.php?create=1&typeid=".$desBloc['cbtid']."&conseilid=".$conseilid."&lg=".$langue."\">".$desBloc['title']."</a></li>";
	    }
	    //$lstTypebloc .="</ul>";
	    $selectbloc .="</select>";
	    //echo $lstTypebloc;
	    echo "<div class=\"\">
	    	  	<div class=\"col-xs-6\">
	    	  		<form action=\"editbloc.php\" method=\"GET\">
	    	  			<div class=\"col-xs-6\">";
	    			echo $selectbloc;
	    			echo "<input type=\"hidden\" id=\"create_".$langue."\" value=\"1\">
	    				  <input type=\"hidden\" id=\"conseilid_".$langue."\" value=\"".$conseilid."\">
	    				  <input type=\"hidden\" id=\"lg_".$langue."\" value=\"".$langue."\">
	    				  <input type=\"hidden\" id=\"version\" value=\"".$_GET['version']."\">";
	    		echo   "</div>			   
	   				</form>
	   			</div>
	   			<div class=\"col-xs-6\">
	    			<button type=\"button\" onclick=\"newBloc('".$langue."');\">Ajouter</button>
	    		</div>";

	    echo "<br><br><br><br>";
	    /*if($readonly)
		echo $rs_conseil['contenu'.$langue];
	    else
		echo "<textarea name=\"content".$langue."\">".$rs_conseil['contenu'.$langue]."</textarea>";*/
		
	    echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> Meta title $langue : ".($readonly?$rs_conseil['ref_title'.$langue]:"<input type=\"text\" name=\"ref_title$langue\" value=\"".$rs_conseil['ref_title'.$langue]."\" size=\"60\">")."</p>";
	    echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> Meta description :<br>".($readonly?$rs_conseil['ref_description'.$langue]:"<textarea name=\"ref_description$langue\" cols=\"80\" rows=\"4\">".$rs_conseil['ref_description'.$langue]."</textarea>")."</p>";
		
		echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> Position 0 (en bref) : Question : ".($readonly?$rs_conseil['ref_question'.$langue]:"<input type=\"text\" name=\"ref_question".$langue."\" value=\"".$rs_conseil['ref_question'.$langue]."\" size=\"120\">")."</p>";
		echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> Position 0 (en bref) : Réponse : ".($readonly?$rs_conseil['ref_reponse'.$langue]:"<input type=\"text\" name=\"ref_reponse".$langue."\" value=\"".$rs_conseil['ref_reponse'.$langue]."\" size=\"120\">")."</p>";
	    
		echo "<hr><br>";

	}
	echo "<h1>Contenu de la page: </h1><p>Liste des produits présents sur la page :</p>";

	//Création de la requête SQL pour récupérer tous les produits administrable correspondants à la page sélectionnée 
	$requete = $DB_site2->query("SELECT a.artcode, a.artid, a_s.prix, a.libelleFR, a_s.active, c_a.conseil_articleid_page, c_a.ordre FROM $table_conseil_article c_a INNER JOIN article a USING(artid) LEFT JOIN article_site a_s USING(artid) WHERE c_a.conseilid=".$conseilid." AND a_s.site_code=\"".htmlspecialchars($_GET['site_code'])."\"  ORDER BY c_a.ordre ASC");
	print "<ul id=\"articles_table\">";
	$art=array();
	$ligne = 0;
	while ($instit_art = $DB_site2->fetch_array($requete)) {
			$art[$instit_art['artid']]=$instit_art;
			$ligne++;
		}

	if($ligne !=0){

		//$c=0;
    	$devise_site=$devises->symbole($sites[$_GET['site_code']]['devise']);
	    //on affiches les articles dans un tableau
	    foreach($art as $articles){
		$instit_art=$articles;
		print "<li id=\"conseilart_".$articles['artid']."\" onclick=\"selectcdc(".$articles['artid'].")\">".vignette($instit_art,$devise_site)."</li>";
	    }
    //si il n'y a pas 12 articles déjà sélectionnés on creer des cases avec possibilité de rajouter des articles
	    if((6-$ligne) !=0){
	    	for($y=1;$y<=(6-$ligne);$y++){
	    		print "<li id=\"conseilart_".($y + $ligne)."\" onclick=\"selectcdc(".($y + $ligne).")\"> </li>";
	    	}
	    }
	}else{
		$art=array_fill(0,6,"");
		for($i=1;$i<=6;$i++){
			print "<li id=\"conseilart_".$i."\" onclick=\"selectcdc(".$i.")\"><a>Ajouter un produit</a></li>";
		}
	}
	echo "</ul>";
	
	// Champs "hidden" 
	echo "<input type=\"hidden\" name=\"site_code\" value=\"".$_GET['site_code']."\">";
	echo "<input type=\"hidden\" name=\"version\" value=\"".$_GET['version']."\">";
	echo "<input type=\"hidden\" name=\"action\" value=\"editer\">";
	echo "<input type=\"hidden\" name=\"conseilid\" value=\"$conseilid\">";

	echo "</form>";

	echo "<div id=\"modif\">
			<form name=\"modifconseilart\" method=\"post\" action=\"#articles_table\">
				<input type=\"hidden\" name=\"consid\">
				<input type=\"hidden\" name=\"artid\">
				Changer la référence : <input type=\"text\" onkeyup=\"srcartid(this.value)\">
				<div id=\"results\"></div>
			</form>
			</div>";
	echo "<br>";	
	echo "<br>";
	echo "<br>";	
	echo "<button type=\"button\" onclick=\"valideform()\"  >Enregistrer</button>";				

	
	echo "<div align=\"center\"><a href=\"conseil.php?site_code=".$_GET['site_code']."&version=".$_GET['version']."\" target=\"_self\">Annuler</a></div>";

	echo "<script language=\"javascript\">
	setTimeout(\"autosave()\",900000);
	function apercuBloc(blocid){
  		$('#apercu_bloc_'+blocid).toggleClass('apercu_bloc');
  	}
	function autosave(){
	    if(confirm(\"Par sécurite la page doit être enregistrée, voulez-vous le faire maintenant ?\"))
		document.forms.form_institutionnel.submit();
	    else
		setTimeout(\"autosave()\",900000);
	}\n
	function newBloc(langue){
		var create = $('#create_'+langue).val();
		var typeid = $('#typeid_'+langue).val();
		var lg = $('#lg_'+langue).val();
		var version = $('#version').val();
		var conseilid = $('#conseilid_'+langue).val();
		if(typeid != 0){
			document.location.href='editbloc.php?create='+create+'&typeid='+typeid+'&conseilid='+conseilid+'&lg='+lg+'&version='+version ;
		}
	}

	//CKEDITOR.replace('contentFR');
	//CKEDITOR.replace('contentEN');
	//CKEDITOR.replace('contentSP');
	//CKEDITOR.config.filebrowserBrowseUrl = '/ckeditor/kcfinder/browse.php?type=files';
	//CKEDITOR.config.filebrowserImageBrowseUrl = '/ckeditor/kcfinder/browse.php?type=images';
	//CKEDITOR.config.filebrowserUploadUrl = '/ckeditor/kcfinder/upload.php?type=files';
    //CKEDITOR.config.filebrowserImageUploadUrl = '/ckeditor/kcfinder/upload.php?type=images';
	</script>";

}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AJOUTER un conseil INSTITUTIONNELLE (action=ajouter)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "ajouter" ){

	echo "<p style=\"color:red;font-size:25px;font-weight:600;\">ATTENTION , POUR MODIFIER LE CONTENU DE CETTE PAGE VOUS DEVEZ AVOIR RECU AU PREALABLE UNE FORMATION DU SERVICE TECHNIQUE   !!!</p>";

	echo "<h3>Ajouter un conseil </h3>";
	
	echo "<form method=\"post\" name=\"form_institutionnel\" >
	<input type=\"hidden\" name=\"site_code\" value=\"".$_GET['site_code']."\">
	<input type=\"hidden\" name=\"version\" value=\"".$_GET['version']."\">
	<input type=\"hidden\" name=\"action\" value=\"editer\">
	<input type=\"hidden\" name=\"conseilid\" value=\"0\">";
	
	$liste_parents = "<option value=\"0\">Aucun parent</>";
	$parents = $DB_site2->query("SELECT * FROM $table_conseil WHERE conseilparent = '0' AND site_code='".$_GET['site_code']."'");
	while( $parent = $DB_site2->fetch_array( $parents ) )
		{
		$liste_parents .= "<option value=\"$parent[conseilid]\">$parent[$libelle]</>";
		}
	print "<p>Conseil parent : <select name=\"conseilparent\">$liste_parents</select></p>";
	
	foreach($langues as $l){
	    $flag=$l;
	    if($l=="EN") $flag="GB";
	    if($l=="SP") $flag="ES";
	    
	    echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> Titre de la page $l : <input type=\"text\" name=\"libelle$l\" value=\"\" size=\"60\"></p>";

	    //echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> CONTENU $l </p>" ;
	    
	    //echo "<textarea name=\"content".$l."\" ></textarea>";
	    echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> Meta title $l : <input type=\"text\" name=\"ref_title$l\" value=\"\" size=\"60\"></p>";
	    echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> Meta description :<br><textarea name=\"ref_description$l\" cols=\"80\" rows=\"4\"></textarea></p>";
	    echo "<p style=\"color:red;\">Pour ajouter du contenu, il faut d'abord Ajouter cette nouvelle page et ensuite l'éditer. </p>";
	    echo "<hr><br>";
	}

	echo "<input type=\"submit\" value=\"Ajouter\">
	</form>";
	
	echo "<div align=\"center\"><a href=\"conseil.php?site_code=".$_GET['site_code']."&version=".$_GET['version']."\" target=\"_self\">Annuler</a></div>";
	
	echo "<script language=\"javascript\">
	//CKEDITOR.replace('contentFR');
	//CKEDITOR.replace('contentEN');
	//CKEDITOR.replace('contentSP');
	</script>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER un conseil INSTITUTIONNELLE (action=supprimer)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "supprimer" )
{
	// Confirmation de suppression
	echo "Etes-vous sûr(e) de vouloir supprimer ce conseil ?<br><br>
		  <a href=\"conseil.php?action=supprimer2&conseilid=$conseilid&version=$_GET[version]\">Oui</a>
		  | <a href=\"conseil.php\">Non</a>";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER un conseil INSTITUTIONNELLE (action=supprimer2)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "supprimer2" )
{
	// On décalle toutes les positions des pages suivantes
	$position_page = $DB_site2->query_first( "SELECT position FROM $table_conseil
											  WHERE conseilid = $conseilid" );
	$position_page = $position_page[0];
	
	$rq_positions_suivantes = $DB_site2->query( "SELECT conseilid, position FROM $table_conseil
												 WHERE position > $position_page" );
	
	while( $rs_positions_suivantes = $DB_site2->fetch_array( $rq_positions_suivantes ) ) 
	{
		$position_temp = $rs_positions_suivantes[position] - 1;
		$rq_modifier_position = "UPDATE $table_conseil SET position = '$position_temp' 
								 WHERE conseilid = '$rs_positions_suivantes[conseilid]'";
		$DB_site2->query( $rq_modifier_position );
	}
	
	// Suppression
	$rq_supprimer = "DELETE FROM $table_conseil WHERE conseilid = $conseilid";
	$DB_site2->query( $rq_supprimer );

	echo "<script language=\"javascript\">location.href=\"conseil.php?version=$_GET[version]\";</script>";
}



include "./footer.php";

function resizeImage($srcFile, $dstWidth = 120, $dstHeight = 90,$dstFile = null, $jpegQuality = 100){
    global $msg2;
    if (!file_exists($srcFile)) {
        $msg2="$srcFile doesn't exist! ";
        return false;        
    }
    if (!$dstFile) {
        $dstFile = 'tn_' . basename($srcFile);
    }
    if (!is_writable($dstDir = dirname($dstFile))) {
        $msg2="$dstDir is not a writeable directory! ";
        return false;
    }

    list($srcWidth, $srcHeight, $type, ) = getimagesize($srcFile);

    switch ($type) {
    case 1 :
        $srcHandle = imagecreatefromgif($srcFile);
        break;
    case 2 :
        $srcHandle = imagecreatefromjpeg($srcFile);
        break;
    case 3 :
        $srcHandle = imagecreatefrompng($srcFile);
        break;
    default :
        $msg2='File Type Not Supported! ';
        return false;
    }
    if (!$srcHandle) {
        $msg2='Could not execute imagecreatefrom() function! ';
        return false;
    }

    // Source is wider than the Destination
    $width = $dstWidth;
    $xOffset = 0;

    $height = round(($width / $srcWidth) * $srcHeight);
    $yOffset = 0;

    $dstHandle = imagecreatetruecolor($width, $height);

    // bool imagecopyresampled ( resource dst_image, resource src_image, int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h )
    if (!imagecopyresampled($dstHandle, $srcHandle, $xOffset, $yOffset, 0, 0, $width, $height, $srcWidth, $srcHeight)) {
        $msg2='Could not execute imagecopyresampled() function! ';
        return false;
    }
    imagedestroy($srcHandle);

    switch ($type) {
    case 1 :
        imagegif($dstHandle, $dstFile);
        break;
    case 2 :
        imagejpeg($dstHandle, $dstFile, $jpegQuality);
        break;
    case 3 :
        imagepng($dstHandle, $dstFile);
        break;
    default :
        $msg2='File Type Not Supported! ';
        return false;
    }

    imagedestroy($dstHandle);

    return true;
} 
?>
