<?php
/// page FAQ pour les utilisateurs

require "admin_global.php";
setcookie("hostname",$host,time()+3600,"/");
include "./header.php";
include "./menu.php";
include "../class/fonctions.php";

$superusers=array("cc","philippe","benoit", "ariel","jessica","jbandre");
$filter_langue=array_diff($langues,$langue_access)!=array();
$bloc = "faqbloc";

echo "<h1>Gestion page FAQ</h1>" ;

if(isset($_GET['version'])) {
	$table_faq 			= "faq_".$_GET['version'];
	$table_faq_bloc 	= "faq_bloc_".$_GET['version'];
	$table_faq_article 	= "faq_article_".$_GET['version'];
	if($_GET['version'] == 'pro') $bloc = "faqblocpro";
}


if(isset($_POST['faqartid'])){
	$faqpage=$DB_site2->query_first("SELECT faq_articleid_page FROM $table_faq_article WHERE faqid=".intval(htmlspecialchars($faqid))." ORDER BY faq_articleid_page DESC");
	$faq_id=$DB_site2->query_first("SELECT faq_articleid_page FROM $table_faq_article WHERE artid =".$_POST['faqartid']." AND faqid=".intval(htmlspecialchars($faqid)) );
	$existe_artid = $DB_site2->query_first("SELECT * FROM $table_faq_article WHERE faqid=".intval(htmlspecialchars($faqid))." AND artid=".$_POST['artid']);
	if (!$existe_artid) {
			if($faqidpage && $faq_id ){
				//Si il y a déja 12 articles enregistrés sur cette page
				$DB_site2->query("UPDATE $table_faq_article SET artid=".$_POST['artid']." WHERE artid=".$_POST['faqartid']." AND faqid=".intval(htmlspecialchars($faqid)));
			}elseif($faqidpage && !$faq_id){
				//Si il n'y a pas encore les 12 articles selectionnes sur cette page
				$ordre = $DB_site2->query_first("SELECT COUNT(*) from $table_faq_article WHERE faqid=".intval(htmlspecialchars($faqid))." ORDER By ordre ASC");
				$ordre = intval($ordre[0]) + 1 ;
				$DB_site2->query("INSERT INTO $table_faq_article (faq_articleid_page, artid, faqid, ordre) VALUES ( ".($faqidpage[0]+1)." ,".$_POST['artid']." , ".intval(htmlspecialchars($faqid))." ,".$ordre.")");
			}elseif(!$faqidpage && !$faq_id){
				//Si aucun article n'a encore été selectionné sur cette page
				$DB_site2->query("INSERT INTO $table_faq_article (faq_articleid_page, artid, faqid, ordre) VALUES (1, ".$_POST['artid']." , ".intval(htmlspecialchars($faqid))." ,1)");
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
		        var faqid = $('#idpage').val();
		        var version = $('#version').val();
	           //$('#articles_table').disableSelection();
	            $.ajax({ 
				    data: sortable_data+'&idpage='+faqid+'&version='+version,
				    type: 'POST',
				    url: '/admin/faq_script.php', // save.php - file with database update
				    //success:function(html){alert(html);}
				  });    
	            
		}
	    });
	});

<?php

echo "
	function selectcdc(id){
      
      document.getElementsByName('faqartid')[0].value=id;
      document.getElementById('modif').style.display='block';
  	}

  	  function srcartid(src){
               if(src.length<=3) return;
               var url=\"marketplace/src.php?txt=\"+src+\"&active=1&site_code=base\";
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
      document.forms.modiffaqart.submit();
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
  			url: 'faq_com.php',
  			type: 'GET',
  			data: 'blocid='+blocid+'&contenu=' + commentaire + '&version=' + version,
  			dataType: 'html'
  		});
  }
  </script>";

echo "<input type=\"hidden\" id=\"idpage\" value=\"$faqid\">";


$optsite="";
if(!isset($_GET['version'])) $optsite = "<option></option>";

$optsite.="<option value=\"part\"".($_GET['version']=="part"?" selected":"").">part</option>";
$optsite.="<option value=\"pro\"".($_GET['version']=="pro"?" selected":"").">pro</option>";

echo "<p>Site : <select id=\"version\" onchange=\"document.location='page_faq.php?version='+this.value\">".$optsite."</select></p>";

////////////////////////////////////////////////////////////////////////////////////////////////////////
// ACTIVER UNE PAGE
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( isset( $action ) && $action == "Activer" )
	{
	$rq_modifier_faq = "UPDATE $table_faq
			 SET active='1' 
			 WHERE faqid = $faqid";
	$DB_site2->query($rq_modifier_faq);
	$action = "";
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// DESACTIVER UNE PAGE
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( isset( $action ) && $action == "Désactiver" )
	{
	$rq_modifier_faq = "UPDATE $table_faq
			 SET active='0' 
			 WHERE faqid = $faqid";
	$DB_site2->query($rq_modifier_faq);
	$action = "";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// MONTER D'UNE POSITION
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( isset( $action ) && $action == "up" )
{
	$i_position = 1;
	$position = $DB_site2->query_first( "SELECT position FROM $table_faq
										 WHERE faqid = '$faqid'" );						
	$nouvelle_position_page = $position[0]-1;
	$position2 = $DB_site2->query_first( "SELECT faqid FROM $table_faq
									  	  WHERE position = '$nouvelle_position_page' AND faqparent = '$faqparent'");
	$rq_modifier_position = "UPDATE $table_faq SET position='$nouvelle_position_page' 
							 WHERE faqid = '$faqid'";
	$DB_site2->query( $rq_modifier_position );
	$rq_modifier_position2 = "UPDATE $table_faq SET position='$position[0]' 
							  WHERE faqid = '$position2[faqid]'";
	$DB_site2->query( $rq_modifier_position2 );

	$action = "";	
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// DESCENDRE D'UNE POSITION
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( isset( $action ) && $action == "down" )
{
	$i_position = 1;
	$position = $DB_site2->query_first( "SELECT position FROM $table_faq WHERE faqid = '$faqid'" );						
	$nouvelle_position_page = $position[0]+1;
	$position2 = $DB_site2->query_first( "SELECT faqid FROM $table_faq WHERE position = '$nouvelle_position_page'  AND faqparent = '$faqparent'");
	$rq_modifier_position = "UPDATE $table_faq SET position='$nouvelle_position_page' WHERE faqid = '$faqid'";
	$DB_site2->query( $rq_modifier_position );
	$rq_modifier_position2 = "UPDATE $table_faq SET position='$position[0]' WHERE faqid = '$position2[faqid]'";
	$DB_site2->query( $rq_modifier_position2 );

	$action = "";	
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AFFICHER LES PAGES INSTITUTIONNELLES
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( (!isset( $action ) || $action == "") && isset($_GET['version']))
{
	$wysiwyg_suppr = $DB_site2->query( "DELETE FROM wysiwyg" );
	$i_faq = 0; // Compte le nombre de pages institutionnelles
	$rq_faq = $DB_site2->query( "SELECT * FROM $table_faq WHERE faqparent=0 ORDER BY position ASC" );
	echo "<h3>questions existantes</h3>
		<table class=\"table_modules_boutiques\">";
	$nb_pages = $DB_site2->num_rows( $rq_faq );
	if ($nb_pages > 1)
		{
		echo "<tr>
					<td align=\"center\"><b>Titre</b></td>
					<td align=\"center\"><b>Affichage</b></td>
					<td align=\"center\" width=\"25%\"><b>Action</b></td>
			  </tr>";
		}
	while  ($rs_faq=$DB_site2->fetch_array($rq_faq)) 
		{
		$rowalt = "td_".getrowbg();
		$i_faq++;
		if ($nb_pages > 1 && !$filter_langue)
			{
			if ($i_faq == 1) // Première page
				{
				$images_position = "<img src=\"images/vide.gif\" height=\"7\">
									<a href=\"page_faq.php?action=down&faqid=$rs_faq[faqid]&faqparent=$rs_faq[faqparent]&version=".$_GET['version']."\">
									<img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>";
				}
			elseif( $i_faq == $nb_pages ) // Dernière page
				{
				$images_position = "<a href=\"page_faq.php?action=up&faqid=$rs_faq[faqid]&faqparent=$rs_faq[faqparent]&version=".$_GET['version']."\">
									<img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>
									<img src=\"images/vide.gif\" height=\"7\">";
				}
			else
				{
				$images_position = "<a href=\"page_faq.php?action=up&faqid=$rs_faq[faqid]&faqparent=$rs_faq[faqparent]&version=".$_GET['version']."\">
									<img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>
									<a href=\"page_faq.php?action=down&faqid=$rs_faq[faqid]&faqparent=$rs_faq&version=".$_GET['version']."\">
									<img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>";
				}
			}
		echo "<tr><td >$images_position $rs_faq[$libelle]</td>";
		$active_action = "";
			if ($rs_faq[active] == "1")
				$active_action = "<a href=\"page_faq.php?action=Désactiver&version=".$_GET['version']."&faqid=$rs_faq[faqid]\" target=\"_self\">Désactiver</a>";
			else
				$active_action = "<a href=\"page_faq.php?action=Activer&version=".$_GET['version']."&faqid=$rs_faq[faqid]\" target=\"_self\">Activer</a>";

		$suppr="";
		if(in_array($user_info['username'],$superusers)) $suppr="| <a href=\"page_faq.php?action=supprimer&faqid=$rs_faq[faqid]&version=$_GET[version]\" target=\"_self\">Supprimer</a>";
		
		$voir="| ";
		foreach($sites['base']['hosts'] as $url=>$l){
			if($l!=="SP"){
		    	$voir.="<a href=\"https://www.$url/faq,$rs_faq[faqid],".url_rewrite($rs_faq['libelle'.$l]).".htm\" target=\"_blank\">$l</a>&nbsp;";
			}
		}
		    
		
		echo "<td align=\"center\" width=\"20%\">$active_action</td>
			  <td  align=\"center\" width=\"20%\">
				  <a href=\"page_faq.php?action=editer&faqid=$rs_faq[faqid]&version=".$_GET['version']."\" target=\"_self\">Editer</a>
				  $suppr
				  $voir
			  </td>
			</tr>";
			
		$i=0;
		$req=$DB_site2->query("SELECT * FROM $table_faq WHERE faqparent=".$rs_faq['faqid']." ORDER BY position");
		while($child=$DB_site2->fetch_array($req)){
		    $i++;
		    
		    if($i>1) 
		      $images_position = "<a href=\"page_faq.php?action=up&faqid=$child[faqid]&faqparent=$child[faqparent]&version=".$_GET['version']."\"><img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>";
		    else
		      $images_position = "<img src=\"images/vide.gif\" height=\"7\">";
		      
		    if($i<$DB_site2->num_rows($req))
		      $images_position .= " <a href=\"page_faq.php?action=down&faqid=$child[faqid]&faqparent=$child[faqparent]&version=".$_GET['version']."\"><img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>";
		    else
		      $images_position .= "<img src=\"images/vide.gif\" height=\"7\">";
		    
		    $suppr="";
		    if(in_array($user_info['username'],$superusers)) $suppr="| <a href=\"page_faq.php?action=supprimer&faqid=$child[faqid]&version=".$_GET['version']."\" target=\"_self\">Supprimer</a>";
		      
		    $voir="| ";
		    foreach($sites['base']['hosts'] as $url=>$l){
		    	if($l!=="SP"){
					$voir.="<a href=\"https://www.$url/faq,$child[faqid],".url_rewrite($child['libelle'.$l]).".htm\" target=\"_blank\">$l</a>&nbsp;";
				}
		    }
		    $active_action = "";
		    	if ($child[active] == "1")
		    		$active_action = "<a href=\"page_faq.php?action=Désactiver&faqid=$child[faqid]&version=".$_GET['version']."&faqparent=$child[faqparent]\" target=\"_self\">Désactiver</a>";
		    	else
		    		$active_action = "<a href=\"page_faq.php?action=Activer&faqid=$child[faqid]&version=".$_GET['version']."&faqparent=$child[faqparent]\" target=\"_self\">Activer</a>";

		    echo "<tr>
		      <td>____ ".$images_position." ".$child['libelleFR']."</td>
		      <td align=\"center\" width=\"20%\">$active_action</td>
		      <td  align=\"center\" width=\"20%\">
				  <a href=\"page_faq.php?action=editer&faqid=$child[faqid]&version=".$_GET['version']."\" target=\"_self\">Editer</a>
				  $suppr
				  $voir
			  </td>
		    </tr>";
		}
		
		}
		
	
	if( $i_faq == 0 )
		echo "<tr><td align=\"center\"><br>Il n'y a aucune question</td></tr>";
	
	echo "</table>";
	
	if(!$filter_langue)
	    echo "<br><a href=\"page_faq.php?action=ajouter&version=".$_GET['version']."\" target=\"_self\">Ajouter une question</a>";

////////////////////////////////////////////////////////////////////////////////////////////////////////
// EDITER un faq INSTITUTIONNELLE (action=editer)
////////////////////////////////////////////////////////////////////////////////////////////////////////
} elseif( $action == "editer" && isset( $faqid ) ){
	if(isset($_POST['faqid'])){
		$rq_modifier_institutionnel = "faqparent='".$_POST['faqparent']."'";
		foreach($langues as $langue) {
			if(in_array($langue,$langue_access)) {
				if($langue!=="SP"){
					$rq_modifier_institutionnel.=", contenu$langue='".mysqli_real_escape_string($DB_site2->link_id,$_POST['content'.$langue])."', libelle$langue='".mysqli_real_escape_string($DB_site2->link_id,$_POST['libelle'.$langue])."', ref_title$langue='".mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_title'.$langue])."', ref_description$langue='".mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_description'.$langue])."'";
					$rq_modifier_institutionnel.=", ref_question".$langue."='".mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_question'.$langue])."', ref_reponse".$langue."='".mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_reponse'.$langue])."' ";
				}
			}
		}
		(!is_null($_POST['sans_lien'])?$rq_modifier_institutionnel .= ", sans_lien =" . $_POST['sans_lien']:$rq_modifier_institutionnel .= ", sans_lien =false");	
		if($_POST['faqid']==0){
		    $max=$DB_site2->query_first("SELECT MAX(position) FROM $table_faq WHERE faqparent=".$_POST['faqparent']);
		    $rq_modifier_institutionnel="INSERT INTO $table_faq SET position=".($max[0]+1).",".$rq_modifier_institutionnel;
		    $DB_site2->query($rq_modifier_institutionnel);
		    $faqid=$DB_site2->insert_id();
		}else{
		    $rq_modifier_institutionnel="UPDATE $table_faq SET ".$rq_modifier_institutionnel." WHERE faqid = ".$_POST['faqid'];
		    $DB_site2->query($rq_modifier_institutionnel);
		    $faqid=$_POST['faqid'];
		}
	}
	/************** supprimer un bloc ******************/
	if(isset($_GET['suppr2'])){
		$path = "../configurations/".$host."/images/".$bloc."/";
		$blocid = $_GET['blocid'];
		$faqid = $_GET['faqid'];
		$l = $_GET['lg'];
		$position_bloc = $DB_site2->query_first("SELECT ordre, image FROM $table_faq_bloc WHERE faq_blocid='".$blocid."'");
		if(is_null($position_bloc[0])) exit;
		$image = $position_bloc['image'];
		$position_bloc = $position_bloc['ordre'];
		$autres_blocs = $DB_site2->query("SELECT faq_blocid, ordre FROM $table_faq_bloc WHERE faqid='$faqid' AND langue='$l' AND ordre > $position_bloc ORDER BY ordre ASC");
		while ($req_autres_blocs = $DB_site2->fetch_array($autres_blocs)) {
			$position_temp = intval($req_autres_blocs['ordre']) - 1 ;
			$DB_site2->query("UPDATE $table_faq_bloc SET ordre = $position_temp WHERE faq_blocid =".$req_autres_blocs['faq_blocid']);
		}

		$DB_site2->query("DELETE FROM $table_faq_bloc WHERE faq_blocid=".$blocid);
		if(!is_null($image)) unlink($path.$blocid.".".$image);
	}
	/******* AJOUT BLOC CONTENU POST  *********************************************************/
	if (isset($_POST['ajout_content'])) {
		$faqid = $_POST['ajout_content'];
		$typeid =$_POST['ajout_type'];
		if(isset($_POST['alt_img'])) $alt_img = mysqli_real_escape_string($DB_site2->link_id,$_POST['alt_img']);
		if(isset($_POST['url_img'])) $url_img = mysqli_real_escape_string($DB_site2->link_id,$_POST['url_img']);
		$req = $DB_site2->query_first("SELECT faq_blocid FROM $table_faq_bloc ORDER BY faq_blocid DESC LIMIT 1");
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
		$ordre = $DB_site2->query_first("SELECT ordre FROM $table_faq_bloc WHERE faqid ='".$faqid."' AND langue ='".$l."' ORDER BY ordre DESC LIMIT 1");
		$new_ordre = intval($ordre[0])+1;

		if($bloc_content == '' && $image == null){
			echo "<script>alert('Attention le bloc est vide, celui-ci ne sera pas enregistré');</script>";
			header('Location:'.BACK_URL.'/page_faq.php?action=editer&faqid='.$faqid.'&version='.$_GET['version'],false);
			exit;
		}else{
			//on insère le contenu dans la base
			if($image=='')
				$DB_site2->query("INSERT INTO $table_faq_bloc (faq_blocid,faqid,typeid,contenu,langue,ordre) VALUES ($blocid,$faqid,$typeid,'$bloc_content','$l',$new_ordre); ");
			else
				$DB_site2->query("INSERT INTO $table_faq_bloc (faq_blocid,faqid,typeid,image,contenu,langue,ordre,alt_img,url_img) VALUES ($blocid,$faqid,$typeid,'$image','$bloc_content','$l',$new_ordre,'$alt_img','$url_img'); ");
		}
	}

	/********** changer l'ordre des blocs de contenu  **********************/
	if(isset($_GET['upbloc'])){
		$blocid = htmlspecialchars($_GET['blocid']);
		$l = htmlspecialchars($_GET['lg']);
		$faqid = htmlspecialchars($_GET['faqid']);
		$positionBloc = $DB_site2->query_first("SELECT ordre FROM $table_faq_bloc WHERE faq_blocid='".$blocid."'");
		$position1 = $positionBloc['ordre'];
		$new_position = intval($position1) - 1;
		$changed = $DB_site2->query_first("SELECT faq_blocid FROM $table_faq_bloc WHERE langue='".$l."' AND ordre = ".$new_position." AND faqid =".$faqid);
		if(!is_null($changed[0])){
			$DB_site2->query("UPDATE $table_faq_bloc SET ordre ='".$new_position."' WHERE faq_blocid ='".$blocid."'");
			$DB_site2->query("UPDATE $table_faq_bloc SET ordre ='".$position1."' WHERE faq_blocid =".$changed[0]);
		}

	}
	if(isset($_GET['downbloc'])){
		$blocid = htmlspecialchars($_GET['blocid']);
		$l = htmlspecialchars($_GET['lg']);
		$faqid = htmlspecialchars($_GET['faqid']);
		$positionBloc = $DB_site2->query_first("SELECT ordre FROM $table_faq_bloc WHERE faq_blocid=".$blocid);
		$position1 = $positionBloc['ordre'];
		$new_position = $position1 + 1;
		$changed = $DB_site2->query_first("SELECT faq_blocid FROM $table_faq_bloc WHERE langue='".$l."' AND ordre = ".$new_position." AND faqid =".$faqid);
		if(!is_null($changed[0])){
			$DB_site2->query("UPDATE $table_faq_bloc SET ordre ='".$new_position."' WHERE faq_blocid ='".$blocid."' ");
			$DB_site2->query("UPDATE $table_faq_bloc SET ordre ='".$position1."' WHERE faq_blocid =".$changed[0]);
		}
	}

	//$rootpath = "/home/arobases/www/vendre-en-ligne.fr/";
	echo "<a href=\"/admin/page_faq.php?version=".$_GET['version']."\" style=\"float:right;\">Retour</a>";
	echo "<p style=\"color:red;font-size:25px;font-weight:600;\">ATTENTION , POUR MODIFIER LE CONTENU DE CETTE PAGE VOUS DEVEZ AVOIR RECU AU PREALABLE UNE FORMATION DU SERVICE TECHNIQUE   !!!</p>";
	
	echo "<div align=\"center\"><h3>Modifier une question</h3></div>";
	
	$rs_faq = $DB_site2->query_first( "select * FROM $table_faq where faqid = $faqid" );		
	
	echo "<form method=\"post\" name=\"form_institutionnel\" action=\"page_faq.php?action=editer&faqid=".$faqid."&version=".$_GET['version']."\">";

	
	if ($rs_faq[faqparent] == "0")
		$liste_parents = "<option value=\"0\" selected>Aucun parent</>";
	else
		$liste_parents = "<option value=\"0\">Aucun parent</>";
	$parents = $DB_site2->query("SELECT * FROM $table_faq WHERE faqparent = '0'");
	while( $parent = $DB_site2->fetch_array( $parents ) )
		{
		if ($parent[faqid] == $rs_faq[faqparent])
			$liste_parents .= "<option value=\"$parent[faqid]\" selected>$parent[$libelle]</>";
		else
			$liste_parents .= "<option value=\"$parent[faqid]\">$parent[$libelle]</>";
		}
	$select_parent = "<select name=\"faqparent\">$liste_parents</select>";	
	
	/// sans_lien, pouvoir activer ou désactiver les liens dans les titres
	$sanslien="";
	if($rs_faq['sans_lien']==true){
		$sanslien="checked";
	}

	if($filter_langue)
	    echo "<input type=\"hidden\" name=\"faqparent\" value=\"$rs_faq[faqparent]\">";
	else
	    echo "<p>Bloc PARENT : $select_parent</p>
	<p>Titre sans lien <input type=\"checkbox\" name=\"sans_lien\" value=\"true\" $sanslien></p>";

	$pathImgBloc = "../configurations/".$host."/images/".$bloc."/";

	foreach($langues as $langue){
		$flag=$langue;

	   	//récupération des blocs //
			$blocs = $DB_site2->query("SELECT faq_blocid,faqid,typeid,image ,contenu, ordre, commentaire, bloc_libelle as title FROM $table_faq_bloc as cb INNER JOIN faq_bloc_type as cbt ON cb.typeid=cbt.cbtid WHERE faqid =".$faqid." AND langue='".$langue."' ORDER BY ordre");

		//on cherche la première position
			$first_position = $DB_site2->query_first("SELECT ordre FROM $table_faq_bloc WHERE faqid=".$faqid." AND langue='".$langue."' ORDER BY ordre ASC LIMIT 1");
			$first_position = intval($first_position[0]);
			if($first_position != ''){
			//on cherche la dernière position
				$last_position = $DB_site2->query_first("SELECT ordre FROM $table_faq_bloc WHERE faqid=".$faqid." AND langue='".$langue."' ORDER BY ordre DESC LIMIT 1");
				$last_position = intval($last_position[0]);
			}else{
				$first_position = 0;
			}

			if($langue=="EN") $flag="GB";
			$readonly=!in_array($langue,$langue_access);
			if($langue !== "SP"){
	    //visualisation de la page sur le site en fonction des faqs pro ou part
			if($_GET['version'] == 'pro'){
				if(SITE_PREFIX == 'localhost'){
					$domain = "http://localhost.".$_GET['version'].".ma-cave-a-vin.fr";
				}elseif (SITE_PREFIX == 'test') {
					$domain = "http://test".$_GET['version'].".ma-cave-a-vin.fr";
				}else{
					$domain = "https://".$_GET['version'].".ma-cave-a-vin.fr";
				}
			}else{
				$domain = "";
			}

			$lien = "<a href=\"".$domain."/faq,$faqid,".url_rewrite($rs_faq['libelle'.$langue]).".htm\" target=\"_blank\" style=\"font-weight:700;font-size:15px;color:black;float:right;\">Voir la page sur le site $langue</a>&nbsp;";
			echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> Titre de la page $langue : ".($readonly?$rs_faq['libelle'.$langue]:"<input type=\"text\" name=\"libelle$langue\" value=\"".$rs_faq['libelle'.$langue]."\" size=\"60\">")." $lien</p>";

			echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> CONTENU $langue -- CREATION / MODIFICATION DES BLOCS</p>" ;
			echo "<p>Liste des Blocs existants :</p>";
			if($DB_site2->num_rows($blocs) != 0){
				while($leBloc = $DB_site2->fetch_array($blocs)){
					if($leBloc['image'] != '' )
						$filemtime = filemtime($pathImgBloc.$leBloc['faq_blocid'].".".$leBloc['image']);

					echo "<div class=\"row\"><div class=\"col-xs-1\" >";
					if(intval($leBloc['ordre']) != $first_position){
						echo "<a href=\"page_faq.php?action=editer&upbloc=1&blocid=".$leBloc['faq_blocid']."&lg=".$langue."&faqid=".$faqid."&version=".$_GET['version']."\"><img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>";
					}
					if(intval($leBloc['ordre']) != $last_position){
						echo "<a href=\"page_faq.php?action=editer&downbloc=1&blocid=".$leBloc['faq_blocid']."&lg=".$langue."&faqid=".$faqid."&version=".$_GET['version']."\"><img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>";
					}
					echo "</div>";
					echo "<div class=\"col-xs-4\"><p id=\"bloc_".$leBloc['faq_blocid']."\" style=\"display:inline-block\"><strong>".$leBloc['ordre']."</strong> - ".$leBloc['title']." </p></div><div class=\"col-xs-1\"><a href=\"editbloc_faq.php?modif=1&blocid=".$leBloc['faq_blocid']."&faqid=".$faqid."&lg=$langue&version=".$_GET['version']."\">Editer</a></div><div class=\"col-xs-2\"><a href=\"editbloc_faq.php?suppr=1&blocid=".$leBloc['faq_blocid']."&faqid=".$faqid."&lg=$langue&version=".$_GET['version']."\">supprimer</a></div> <div class=\"col-xs-1\"><a onclick=\"apercuBloc('".$leBloc['faq_blocid']."')\">Voir</a></div><div class=\"col-xs-3\"><textarea id=\"bloc_com_".$leBloc['faq_blocid']."\" onchange=\"validComBloc('".$leBloc['faq_blocid']."','".$_GET['version']."')\">".$leBloc['commentaire']."</textarea></div><br>";
					echo "<div class=\"row\" style=\"margin:0;\"><div id=\"apercu_bloc_".$leBloc['faq_blocid']."\" class=\"col-xs-12 voir_bloc\" style=\"display:none;\">";
					switch ($leBloc['typeid']) {
						case '1':
						$contenu_apercu = "<div class=\"col-xs-6\">".$leBloc['contenu']."</div><div class=\"col-xs-6\"><img class=\"img-responsive\" src=\"".$pathImgBloc.$leBloc['faq_blocid'].".".$leBloc['image']."?".$filemtime."\"></div>";
						break;
						case '2':
						$contenu_apercu = "<div class=\"col-xs-6\"><img class=\"img-responsive\" src=\"".$pathImgBloc.$leBloc['faq_blocid'].".".$leBloc['image']."?".$filemtime."\"></div><div class=\"col-xs-6\">".$leBloc['contenu']."</div>";
						break;
						case '3':
						case '4':
						case '5':
						case '7':
						case '9':
						$contenu_apercu = "<div class=\"col-xs-offset-3 col-xs-6\">".$leBloc['contenu']."</div>";
						break;			
						case '6':
						$contenu_apercu = "<div class=\"col-xs-offset-3 col-xs-6\"><img class=\"img-responsive\" src=\"".$pathImgBloc.$leBloc['faq_blocid'].".".$leBloc['image']."?".$filemtime."\"></div>";
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
			$typeBloc = $DB_site2->query("SELECT cbtid,bloc_libelle as title FROM faq_bloc_type");
			$selectbloc = "<select id=\"typeid_".$langue."\"><option value=\"0\">Choisissez un type de Bloc </option>";
	    //$lstTypebloc="<ul>";
			while($desBloc = $DB_site2->fetch_array($typeBloc)){
				$selectbloc .="<option value=\"".$desBloc['cbtid']."\">".$desBloc['title']."</option>";
	    	//$lstTypebloc.= "<li><a href=\"editbloc.php?create=1&typeid=".$desBloc['cbtid']."&faqid=".$faqid."&lg=".$langue."\">".$desBloc['title']."</a></li>";
			}
	    //$lstTypebloc .="</ul>";
			$selectbloc .="</select>";
	    //echo $lstTypebloc;
			echo "<div class=\"\">
			<div class=\"col-xs-6\">
			<form action=\"editbloc_faq.php\" method=\"GET\">
			<div class=\"col-xs-6\">";
			echo $selectbloc;
			echo "<input type=\"hidden\" id=\"create_".$langue."\" value=\"1\">
			<input type=\"hidden\" id=\"faqid_".$langue."\" value=\"".$faqid."\">
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
		echo $rs_faq['contenu'.$langue];
	    else
	    echo "<textarea name=\"content".$langue."\">".$rs_faq['contenu'.$langue]."</textarea>";*/

	    echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> Meta title $langue : ".($readonly?$rs_faq['ref_title'.$langue]:"<input type=\"text\" name=\"ref_title$langue\" value=\"".$rs_faq['ref_title'.$langue]."\" size=\"60\">")."</p>";
	    echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> Meta description :<br>".($readonly?$rs_faq['ref_description'.$langue]:"<textarea name=\"ref_description$langue\" cols=\"80\" rows=\"4\">".$rs_faq['ref_description'.$langue]."</textarea>")."</p>";

	    echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> Position 0 (en bref) : Question : ".($readonly?$rs_faq['ref_question'.$langue]:"<input type=\"text\" name=\"ref_question".$langue."\" value=\"".$rs_faq['ref_question'.$langue]."\" size=\"120\">")."</p>";
	    echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> Position 0 (en bref) : Réponse : ".($readonly?$rs_faq['ref_reponse'.$langue]:"<input type=\"text\" name=\"ref_reponse".$langue."\" value=\"".$rs_faq['ref_reponse'.$langue]."\" size=\"120\">")."</p>";
	    
	    echo "<hr><br>";
}
}
	echo "<h1>Contenu de la page: </h1><p>Liste des produits présents sur la page :</p>";

	//Création de la requête SQL pour récupérer tous les produits administrable correspondants à la page sélectionnée 
	$requete = $DB_site2->query("SELECT a.artcode, a.artid, a_s.prix, a.libelleFR, a_s.active, c_a.faq_articleid_page, c_a.ordre FROM $table_faq_article c_a INNER JOIN article a USING(artid) LEFT JOIN article_site a_s USING(artid) WHERE c_a.faqid=".$faqid." AND a_s.site_code=\"base\"  ORDER BY c_a.ordre ASC");
	print "<ul id=\"articles_table\">";
	$art=array();
	$ligne = 0;
	while ($instit_art = $DB_site2->fetch_array($requete)) {
			$art[$instit_art['artid']]=$instit_art;
			$ligne++;
		}

	if($ligne !=0){

		//$c=0;
    	$devise_site=$devises->symbole($sites['base']['devise']);
	    //on affiches les articles dans un tableau
	    foreach($art as $articles){
		$instit_art=$articles;
		print "<li id=\"faqart_".$articles['artid']."\" onclick=\"selectcdc(".$articles['artid'].")\">".vignette($instit_art,$devise_site)."</li>";
	    }
    //si il n'y a pas 12 articles déjà sélectionnés on creer des cases avec possibilité de rajouter des articles
	    if((6-$ligne) !=0){
	    	for($y=1;$y<=(6-$ligne);$y++){
	    		print "<li id=\"faqart_".($y + $ligne)."\" onclick=\"selectcdc(".($y + $ligne).")\"> </li>";
	    	}
	    }
	}else{
		$art=array_fill(0,6,"");
		for($i=1;$i<=6;$i++){
			print "<li id=\"faqart_".$i."\" onclick=\"selectcdc(".$i.")\"><a>Ajouter un produit</a></li>";
		}
	}
	echo "</ul>";
	// Champs "hidden" 
	echo "<input type=\"hidden\" name=\"version\" value=\"".$_GET['version']."\">";
	echo "<input type=\"hidden\" name=\"action\" value=\"editer\">";
	echo "<input type=\"hidden\" name=\"faqid\" value=\"$faqid\">";

	echo "</form>";

	echo "<div id=\"modif\">
			<form name=\"modiffaqart\" method=\"post\" action=\"#articles_table\">
				<input type=\"hidden\" name=\"faqartid\">
				<input type=\"hidden\" name=\"artid\">
				Changer la référence : <input type=\"text\" onkeyup=\"srcartid(this.value)\">
				<div id=\"results\"></div>
			</form>
			</div>";
	echo "<br>";	
	echo "<br>";
	echo "<br>";	
	echo "<button type=\"button\" onclick=\"valideform()\"  >Enregistrer</button>";				

	
	echo "<div align=\"center\"><a href=\"page_faq.php?version=".$_GET['version']."\" target=\"_self\">Annuler</a></div>";

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
		var faqid = $('#faqid_'+langue).val();
		if(typeid != 0){
			document.location.href='editbloc_faq.php?create='+create+'&typeid='+typeid+'&faqid='+faqid+'&lg='+lg+'&version='+version ;
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
// AJOUTER un faq INSTITUTIONNELLE (action=ajouter)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "ajouter" ){

	echo "<p style=\"color:red;font-size:25px;font-weight:600;\">ATTENTION , POUR MODIFIER LE CONTENU DE CETTE PAGE VOUS DEVEZ AVOIR RECU AU PREALABLE UNE FORMATION DU SERVICE TECHNIQUE   !!!</p>";

	echo "<h3>Ajouter une question </h3>";
	
	echo "<form method=\"post\" name=\"form_institutionnel\" >
	<input type=\"hidden\" name=\"version\" value=\"".$_GET['version']."\">
	<input type=\"hidden\" name=\"action\" value=\"editer\">
	<input type=\"hidden\" name=\"faqid\" value=\"0\">";
	
	$liste_parents = "<option value=\"0\">Aucun parent</>";
	$parents = $DB_site2->query("SELECT * FROM $table_faq WHERE faqparent = '0'");
	while( $parent = $DB_site2->fetch_array( $parents ) )
		{
		$liste_parents .= "<option value=\"$parent[faqid]\">$parent[$libelle]</>";
		}

	/// sans_lien, pouvoir activer ou désactiver les liens dans les titres
	$sanslien="";
	if($rs_faq['sans_lien']==true){
		$sanslien="checked";
	}

	print "<p>question parente : <select name=\"faqparent\">$liste_parents</select></p>
	<p>Titre sans lien <input type=\"checkbox\" name=\"sans_lien\" value=\"true\" $sanslien></p>";
	
	foreach($langues as $l){
		$flag=$l;

			if($l=="EN") $flag="GB";
			if($l!=="SP"){
				echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> Titre de la page $l : <input type=\"text\" name=\"libelle$l\" value=\"\" size=\"60\"></p>";

	    //echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> CONTENU $l </p>" ;

	    //echo "<textarea name=\"content".$l."\" ></textarea>";
				echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> Meta title $l : <input type=\"text\" name=\"ref_title$l\" value=\"\" size=\"60\"></p>";
				echo "<p><img src=\"images/drapeaux/$flag.gif\" width=\"20\"> Meta description :<br><textarea name=\"ref_description$l\" cols=\"80\" rows=\"4\"></textarea></p>";
				echo "<p style=\"color:red;\">Pour ajouter du contenu, il faut d'abord Ajouter cette nouvelle page et ensuite l'éditer. </p>";
				echo "<hr><br>";
			}
		}


	echo "<input type=\"submit\" value=\"Ajouter\">
	</form>";


	echo "<div align=\"center\"><a href=\"page_faq.php?version=".$_GET['version']."\" target=\"_self\">Annuler</a></div>";

	echo "<script language=\"javascript\">
	//CKEDITOR.replace('contentFR');
	//CKEDITOR.replace('contentEN');
	</script>";
	
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER un faq INSTITUTIONNELLE (action=supprimer)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "supprimer" )
{
	// Confirmation de suppression
	echo "Etes-vous sûr(e) de vouloir supprimer ce faq ?<br><br>
		  <a href=\"page_faq.php?action=supprimer2&faqid=$faqid&version=$_GET[version]\">Oui</a>
		  | <a href=\"page_faq.php\">Non</a>";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER un faq INSTITUTIONNELLE (action=supprimer2)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "supprimer2" )
{
	// On décalle toutes les positions des pages suivantes
	$position_page = $DB_site2->query_first( "SELECT position FROM $table_faq
											  WHERE faqid = $faqid" );
	$position_page = $position_page[0];
	
	$rq_positions_suivantes = $DB_site2->query( "SELECT faqid, position FROM $table_faq
												 WHERE position > $position_page" );
	
	while( $rs_positions_suivantes = $DB_site2->fetch_array( $rq_positions_suivantes ) ) 
	{
		$position_temp = $rs_positions_suivantes[position] - 1;
		$rq_modifier_position = "UPDATE $table_faq SET position = '$position_temp' 
								 WHERE faqid = '$rs_positions_suivantes[faqid]'";
		$DB_site2->query( $rq_modifier_position );
	}
	
	// Suppression
	$rq_supprimer = "DELETE FROM $table_faq WHERE faqid = $faqid";
	$DB_site2->query( $rq_supprimer );

	echo "<script language=\"javascript\">location.href=\"page_faq.php?version=$_GET[version]\";</script>";
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
