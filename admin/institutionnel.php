<?php
require "admin_global.php";
setcookie("hostname",$host,time()+3600,"/");
include "./header.php";
include "./menu.php";
include "../class/fonctions.php";

$superusers=array("cc","philippe","ariel","benoit","jessica","jbandre");
$filter_langue=array_diff($langues,$langue_access)!=array();


//suppression d'un article
if(isset($_POST['del_instart'])){
	$instid = mysqli_real_escape_string($DB_site2->link_id,$_GET['institutionnelid']);
	$DB_site2->query("DELETE FROM institutionnel_article WHERE institutionnelid='$instid' AND artid='".$_POST['artid_instart']."'");
}
		
//on change l'ancien article par le nouveau sélectionné concernant l'affichages des produits administrables de la page instit
if(isset($_POST['instid'])){
	$instidpage=$DB_site2->query_first("SELECT institutionnel_articleid_page FROM institutionnel_article WHERE institutionnelid=".intval(htmlspecialchars($institutionnelid))." ORDER BY institutionnel_articleid_page DESC");
	$instid=$DB_site2->query_first("SELECT institutionnel_articleid_page FROM institutionnel_article WHERE artid =".$_POST['instid']." AND institutionnelid=".intval(htmlspecialchars($institutionnelid)) );
	$existe_artid = $DB_site2->query_first("SELECT * FROM institutionnel_article WHERE institutionnelid=".intval(htmlspecialchars($institutionnelid))." AND artid=".$_POST['artid']);
	if (!$existe_artid) {
			if($instidpage && $instid ){
				//Si l'article n'est pas enregistré
				$DB_site2->query("UPDATE institutionnel_article SET artid=".$_POST['artid']." WHERE artid=".$_POST['instid']." AND institutionnelid=".intval(htmlspecialchars($institutionnelid)));
			}elseif($instidpage && !$instid ){
				//Si l'article est enregistré
				$ordre = $DB_site2->query_first("SELECT COUNT(*) from institutionnel_article WHERE institutionnelid=".intval(htmlspecialchars($institutionnelid))." ORDER By ordre ASC");
				$ordre = intval($ordre[0]) + 1 ;
				$DB_site2->query("INSERT INTO institutionnel_article (institutionnel_articleid_page, artid, institutionnelid, ordre) VALUES ( ".($instidpage[0]+1)." ,".$_POST['artid']." , ".intval(htmlspecialchars($institutionnelid))." ,".$ordre.")");
			}elseif(!$instidpaged && !$instid){
				//Si aucun article n'a encore été selectionné sur cette page
				$DB_site2->query_first("INSERT INTO institutionnel_article (institutionnel_articleid_page, artid, institutionnelid, ordre) VALUES (1, ".$_POST['artid']." , ".intval(htmlspecialchars($institutionnelid))." ,1)");
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
 	#articles_table li{display:inline-block;width:300px;height:185px;border:1px black solid;margin:1px;position:relative;}
 	.border_bottom{border-bottom:1px solid black;}
	</style> 
	<script type=\"text/javascript\">
	\$=\$jQuery;";

?>
	$(document).ready(function () {
	    $('#articles_table').sortable({
	    	placeholder: 'placeholder',
	        stop: function (event, ui) {
		        var sortable_data = $(this).sortable('serialize');
		        var institutionnelid = $('#page_id').val();
	           //$('#articles_table').disableSelection();
	            $.ajax({ 
				    data: sortable_data+'&idpage='+institutionnelid,
				    type: 'POST',
				    url: '/admin/institutionnel_script.php', // save.php - file with database update
				    //success:function(html){alert(html);}
				  });    
	   		}
	    });
	});

<?php

echo "
	function selectcdc(id){
      document.getElementsByName('instid')[0].value=id;
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
      document.getElementsByName('artid')[0].value=artid;
      document.forms.modifinstart.submit();
  }
  function valideform(){
  	document.forms.form_institutionnel.submit();
  }
  function validComBloc(blocid){
  	var commentaire = $('#bloc_com_'+blocid).val();
  	$.ajax({
  			url: 'institutionnel_com.php',
  			type: 'GET',
  			data: 'blocid='+blocid+'&contenu=' + commentaire,
  			dataType: 'html'
  		});
  }
  function del(artid){
  	document.getElementsByName('artid_instart')[0].value=artid;
  	document.forms.del_art.submit();
  }
  </script>
";

$menus=array(0=>"Commande et garanties (hors footer)",1=>"Ma Cave à vin",2=>"Nos marques",3=>"Les promotions",4=>"(inactif)",5=>"Archives (temporaire)",6=>"(Footer - Barre sur fond marron)");

echo "<h1>Gestion des pages institutionnelles</h1>";

$optsite="";
if(!isset($_GET['site_code'])) $optsite="<option></option>";
foreach($sites as $site_code=>$site){
    $optsite.="<option value=\"".$site_code."\"".($_GET['site_code']==$site_code?" selected":"").">$site_code</option>";
}

echo "<p>Site : <select onchange=\"document.location='institutionnel.php?site_code='+this.value\">".$optsite."</select></p>";

////////////////////////////////////////////////////////////////////////////////////////////////////////
// ACTIVER UNE PAGE
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( isset( $action ) && $action == "Activer" )
	{
	$rq_modifier_institutionnel = "UPDATE institutionnel
			 SET active='1' 
			 WHERE institutionnelid = $institutionnelid";
	$DB_site2->query($rq_modifier_institutionnel);
	//$rq_modifier_appartenance_menu = "UPDATE institutionnel SET menuid_instit = 1";
	// Pour réafficher les pages institutionnelles
	$action = "";
	}

if( isset( $action ) && $action == "Activer_foot" )
	{
	$rq_modifier_institutionnel = "UPDATE institutionnel
			 SET active_footer='1' 
			 WHERE institutionnelid = $institutionnelid";
	$DB_site2->query($rq_modifier_institutionnel);
	// Pour réafficher les pages institutionnelles
	$action = "";
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// DESACTIVER UNE PAGE
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( isset( $action ) && $action == "Désactiver" )
	{
	$rq_modifier_institutionnel = "UPDATE institutionnel
			 SET active='0' 
			 WHERE institutionnelid = $institutionnelid";
	$DB_site2->query($rq_modifier_institutionnel);

	// Pour réafficher les pages institutionnelles
	$action = "";
}
if(isset($action) && $action== "menu"){
	echo "Dans quel menu Voulez vous affecter cette page ?";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////
// TRAITEMENT APRES AVOIR CHOISI LE MENU POUR DESARCHIVER UNE PAGE INSTIT
////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($_POST['num_menu'])){
	$DB_site2->query("UPDATE institutionnel SET menuid_instit =".intval($_POST['num_menu'])." WHERE institutionnelid=".$_POST['institutionnelid']);
	$action="";

}

if( isset( $action ) && $action == "Désactiver_foot" )
	{
	$rq_modifier_institutionnel = "UPDATE institutionnel
			 SET active_footer='0' 
			 WHERE institutionnelid = $institutionnelid";
	$DB_site2->query($rq_modifier_institutionnel);

	// Pour réafficher les pages institutionnelles
	$action = "";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////
// ARCHIVER UNE PAGE INSTITUTIONNELLE (action=archiver)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "archiver" )
{
	$req= "UPDATE institutionnel SET menuid_instit=2, active=\"0\" WHERE institutionnelid=".$_GET['institutionnelid'];
	$DB_site2->query($req);
	$action="";
}



////////////////////////////////////////////////////////////////////////////////////////////////////////
// MONTER D'UNE POSITION
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( isset( $action ) && $action == "up" )
{
	$i_position = 1;
	$position = $DB_site2->query_first( "SELECT position FROM institutionnel
										 WHERE institutionnelid = '$institutionnelid'" );						
	$nouvelle_position_page = $position[0]-1;
	$position2 = $DB_site2->query_first( "SELECT institutionnelid FROM institutionnel
									  	  WHERE position = '$nouvelle_position_page' AND institutionnelparent = '$institutionnelparent'" );
	$rq_modifier_position = "UPDATE institutionnel SET position='$nouvelle_position_page' 
							 WHERE institutionnelid = '$institutionnelid'";
	$DB_site2->query( $rq_modifier_position );
	$rq_modifier_position2 = "UPDATE institutionnel SET position='$position[0]' 
							  WHERE institutionnelid = '$position2[institutionnelid]'";
	$DB_site2->query( $rq_modifier_position2 );

	$action = "";	
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// DESCENDRE D'UNE POSITION
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( isset( $action ) && $action == "down" )
{
	$i_position = 1;
	$position = $DB_site2->query_first( "SELECT position FROM institutionnel
										 WHERE institutionnelid = '$institutionnelid'" );						
	$nouvelle_position_page = $position[0]+1;
	$position2 = $DB_site2->query_first( "SELECT institutionnelid FROM institutionnel
									  	  WHERE position = '$nouvelle_position_page' AND institutionnelparent = '$institutionnelparent'" );
	$rq_modifier_position = "UPDATE institutionnel SET position='$nouvelle_position_page' 
							 WHERE institutionnelid = '$institutionnelid'";
	$DB_site2->query( $rq_modifier_position );
	$rq_modifier_position2 = "UPDATE institutionnel SET position='$position[0]' 
							  WHERE institutionnelid = '$position2[institutionnelid]'";
	$DB_site2->query( $rq_modifier_position2 );

	$action = "";	
}

if(isset($action) && $action=="pro") { 
	$DB_site2->query("UPDATE institutionnel SET pro=".intval($_GET['pro'])." WHERE institutionnelid=".intval($_GET['institutionnelid']));
	unset($action);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AFFICHER LES PAGES INSTITUTIONNELLES
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( (!isset( $action ) || $action == "") && isset($_GET['site_code']))
{
	$wysiwyg_suppr = $DB_site2->query( "DELETE FROM wysiwyg" );

	$num_menu_new = $DB_site2->query("SELECT * FROM menu_institutionnel order by menuid");
	while ($row = $DB_site2->fetch_array($num_menu_new)) {
			$i_institutionnel = 0; // Compte le nombre de pages institutionnelles
			$rq_institutionnel = $DB_site2->query( "SELECT * FROM institutionnel WHERE institutionnelparent=0 AND menuid_instit=".intval($row['menuid'])." AND site_code='".$_GET['site_code']."' ORDER BY position" );
			echo "<h3>Menu ".$row['menuid']." ".$row['libelle']."</h3>
			<table class=\"table_modules_boutiques\">";
			$nb_pages = $DB_site2->num_rows( $rq_institutionnel );
			if ($nb_pages > 1)
			{
			echo "<tr>
						<td align=\"center\"><b>Titre</b></td>
						<td align=\"center\"><b>Affichage</b></td>";
			//if(intval($row['menuid']) == 1) echo "<td align=\"center\" width=\"25%\"><!--<b>Affichage (footer)</b>--></td>";
			echo "<td align=\"center\"><b>Version site</b></td>
			<td align=\"center\" width=\"30%\"><b>Action 2</b></td>
				  </tr>";
			}
			while  ($rs_institutionnel=$DB_site2->fetch_array($rq_institutionnel)){
				$rowalt = "td_".getrowbg();
				$i_institutionnel++;
				$images_position = "";
				if ($nb_pages > 1 && !$filter_langue)
				{
					if ($i_institutionnel == 1){ // Première page
						$images_position = "<img src=\"images/vide.gif\" height=\"7\">
										<a href=\"institutionnel.php?action=down&site_code=".$_GET['site_code']."&institutionnelid=$rs_institutionnel[institutionnelid]&institutionnelparent=$rs_institutionnel[institutionnelparent]&menu=$rs_institutionnel[menu]\">
										<img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>";
					}
					elseif( $i_institutionnel == $nb_pages ) // Dernière page
					{
					$images_position = "<a href=\"institutionnel.php?action=up&site_code=".$_GET['site_code']."&institutionnelid=$rs_institutionnel[institutionnelid]&institutionnelparent=$rs_institutionnel[institutionnelparent]&menu=$rs_institutionnel[menu]\">
										<img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>
										<img src=\"images/vide.gif\" height=\"7\">";
					}
					else
					{
					$images_position = "<a href=\"institutionnel.php?action=up&site_code=".$_GET['site_code']."&institutionnelid=$rs_institutionnel[institutionnelid]&institutionnelparent=$rs_institutionnel[institutionnelparent]&menu=$rs_institutionnel[menu]\">
										<img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>
										<a href=\"institutionnel.php?action=down&site_code=".$_GET['site_code']."&institutionnelid=$rs_institutionnel[institutionnelid]&institutionnelparent=$rs_institutionnel[institutionnelparent]&menu=$rs_institutionnel[menu]\">
										<img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>";
					}
				}
				echo "<tr class=\"border_bottom\" ><td >$images_position $rs_institutionnel[$libelle]</td>";
				$active_action = "";
				if(!$filter_langue) {
			   		if ($rs_institutionnel[active] == "1")
				 	 $active_action = "<a href=\"institutionnel.php?action=Désactiver&site_code=".$_GET['site_code']."&institutionnelid=$rs_institutionnel[institutionnelid]\" target=\"_self\">Désactiver</a>";
			   	else
					$active_action = "<a href=\"institutionnel.php?action=Activer&site_code=".$_GET['site_code']."&institutionnelid=$rs_institutionnel[institutionnelid]\" target=\"_self\">Activer</a>";
				}
				$active_footer_action = "";
				
				$suppr="";
				if(in_array($user_info['username'],$superusers)) $suppr=" | <a href=\"institutionnel.php?action=supprimer&site_code=".$_GET['site_code']."&institutionnelid=$rs_institutionnel[institutionnelid]&menu=$rs_institutionnel[menu]\" target=\"_self\">Supprimer</a>";

				list($pro,$part)=str_split(str_pad(decbin($rs_institutionnel['pro']),2,0,STR_PAD_LEFT));
				if(!$filter_langue)
			  	  $lienspro="<a href=\"institutionnel.php?action=pro&site_code=".$_GET['site_code']."&pro=".intval(abs($part-1)+2*$pro)."&institutionnelid=".$rs_institutionnel['institutionnelid']."\" style=\"color:".($part==1?"green":"red").";\">Part</a>&nbsp;<a href=\"institutionnel.php?action=pro&pro=".intval(2*abs($pro-1)+$part)."&institutionnelid=".$rs_institutionnel['institutionnelid']."\" style=\"color:".($pro==1?"green":"red").";\">Pro</a>";
			
				$voir="| ";
				foreach($sites[$_GET['site_code']]['hosts'] as $url=>$l)
			   		 $voir.="<a href=\"http://".$url."/int,$rs_institutionnel[institutionnelid],".url_rewrite($rs_institutionnel['libelle'.$l]).".htm\" target=\"_blank\">$l</a>&nbsp;";
			
				echo "<td align=\"center\" width=\"20%\">
					  $active_action
				  </td>";
				//if($row['menuid'] == 1) echo "<td align=\"center\">".$active_footer_action."</td>";
				echo "<td align=\"center\">$lienspro</td>
				<td  align=\"center\" width=\"20%\">
				  <a href=\"institutionnel.php?action=editer&site_code=".$_GET['site_code']."&institutionnelid=$rs_institutionnel[institutionnelid]&i_langue=0\" target=\"_self\">Editer</a>";
				  if($rs_institutionnel['active']==0 && $rs_institutionnel['menuid_instit']!=2) {echo " | <a href=\"institutionnel.php?action=archiver&site_code=".$_GET['site_code']."&institutionnelid=$rs_institutionnel[institutionnelid]&i_langue=0\" target=\"_self\" >Archiver</a>";
					}elseif($rs_institutionnel['active']==1 && $rs_institutionnel['menuid_instit']==2){echo " | <a href=\"institutionnel.php?action=desarchiver&site_code=".$_GET['site_code']."&institutionnelid=$rs_institutionnel[institutionnelid]&i_langue=0\" target=\"_self\" >Désarchiver</a>";}

				  echo "
					  $suppr
					  $voir 
					  <a href=\"institutionnel.php?action=menu&site_code=".$_GET['site_code']."&institutionnelid=$rs_institutionnel[institutionnelid]&i_langue=0\" target=\"_self\">Menu</a>
				 </td>
				</tr>";

				$i=0;
		$req_child=$DB_site2->query("SELECT * FROM institutionnel WHERE institutionnelparent=".$rs_institutionnel['institutionnelid']." ORDER BY position");
		$images_position = "";
		while($child=$DB_site2->fetch_array($req_child)){
		    $i++;
		    
		    if($i>1) 
		      $images_position = "<a href=\"institutionnel.php?action=up&site_code=".$_GET['site_code']."&institutionnelid=$child[institutionnelid]&institutionnelparent=$child[institutionnelparent]\"><img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>";
		    else
		      $images_position = "<img src=\"images/vide.gif\" height=\"7\">";
		      
		    if($i<$DB_site2->num_rows($req_child))
		      $images_position .= " <a href=\"institutionnel.php?action=down&site_code=".$_GET['site_code']."&institutionnelid=$child[institutionnelid]&institutionnelparent=$child[institutionnelparent]\"><img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>";
		    else
		      $images_position .= "<img src=\"images/vide.gif\" height=\"7\">";

		    echo "<tr class=\"border_bottom\" ><td >____ $images_position $child[$libelle]</td>";

		  $active_action = "";
		  if(!$filter_langue) {
		  	if ($child[active] == "1")
		  		$active_action = "<a href=\"institutionnel.php?action=Désactiver&site_code=".$_GET['site_code']."&institutionnelid=$child[institutionnelid]&institutionnelparent=$child[institutionnelparent]\" target=\"_self\">Désactiver</a>";
		  	else
		  		$active_action = "<a href=\"institutionnel.php?action=Activer&site_code=".$_GET['site_code']."&institutionnelid=$child[institutionnelid]&institutionnelparent=$child[institutionnelparent]\" target=\"_self\">Activer</a>";
		  }
		  $active_footer_action = "";

		  $suppr="";
		  if(in_array($user_info['username'],$superusers)) $suppr=" | <a href=\"institutionnel.php?action=supprimer&site_code=".$_GET['site_code']."&institutionnelid=$child[institutionnelid]&institutionnelparent=$child[institutionnelparent]&menu=$child[menu]\" target=\"_self\">Supprimer</a>";

		  list($pro,$part)=str_split(str_pad(decbin($child['pro']),2,0,STR_PAD_LEFT));
		  if(!$filter_langue)
		  	$lienspro="<a href=\"institutionnel.php?action=pro&site_code=".$_GET['site_code']."&pro=".intval(abs($part-1)+2*$pro)."&institutionnelid=".$child['institutionnelid']."&institutionnelparent=$child[institutionnelparent]\" style=\"color:".($part==1?"green":"red").";\">Part</a>&nbsp;<a href=\"institutionnel.php?action=pro&pro=".intval(2*abs($pro-1)+$part)."&institutionnelid=".$child['institutionnelid']."&institutionnelparent=$child[institutionnelparent]\" style=\"color:".($pro==1?"green":"red").";\">Pro</a>";

		  $voir="| ";
		  foreach($sites[$_GET['site_code']]['hosts'] as $url=>$l)
		  	$voir.="<a href=\"http://".$url."/int,".$child['institutionnelid'].",".url_rewrite($child['libelle'.$l]).".htm\" target=\"_blank\">$l</a>&nbsp;";

		  echo "<td align=\"center\" width=\"20%\">
		  $active_action
		  </td>";
				//if($row['menuid'] == 1) echo "<td align=\"center\">".$active_footer_action."</td>";
		  echo "<td align=\"center\">$lienspro</td>
		  <td  align=\"center\" width=\"20%\">
		  <a href=\"institutionnel.php?action=editer&site_code=".$_GET['site_code']."&institutionnelid=$child[institutionnelid]&institutionnelparent=$child[institutionnelparent]&i_langue=0\" target=\"_self\">Editer</a>";
		  if($child['active']==0 && $child['menuid_instit']!=2) {echo " | <a href=\"institutionnel.php?action=archiver&site_code=".$_GET['site_code']."&institutionnelid=$child[institutionnelid]&institutionnelparent=$child[institutionnelparent]&i_langue=0\" target=\"_self\" >Archiver</a>";
		}elseif($child['active']==1 && $child['menuid_instit']==2){echo " | <a href=\"institutionnel.php?action=desarchiver&site_code=".$_GET['site_code']."&institutionnelid=$child[institutionnelid]&institutionnelparent=$child[institutionnelparent]&i_langue=0\" target=\"_self\" >Désarchiver</a>";}
		    
		    echo "
		      $suppr
				  $voir
				  
				  
			  </td>
		    </tr>";
		}
			}
			if( $i_institutionnel == 0 )
			echo "<tr><td align=\"center\"><br>Il n'y a aucune page</td></tr>";

			echo "</table>";
			if(!$filter_langue)
		   	 echo "<br><a href=\"institutionnel.php?action=ajouter&site_code=".$_GET['site_code']."&menu=".$row['menuid']."\" target=\"_self\">Ajouter une page institutionnelle</a>
			<br><br>";
		} 


	
	echo "
	<div align=\"left\">
	<b>NOTA</b>
	<bR>Nous mettons à votre disposition des Conditions Générales de Ventes, libre à vous de les utiliser.<br>
	Le cas échéant, il vous faut simplement remplacer le texte **SOCIETE** par le nom de votre société, puis activer la page.<br>
	Vous pouvez bien entendu modifier à votre guise ces Conditions Générales de Vente.</div>";

////////////////////////////////////////////////////////////////////////////////////////////////////////
// EDITER UNE PAGE INSTITUTIONNELLE (action=editer)
////////////////////////////////////////////////////////////////////////////////////////////////////////
} 
elseif( $action == "editer" && isset( $institutionnelid ) )
{
	//Création / Mise à jour de la page institutionnelle
    if(isset($_POST['institutionnelid']))
    {
    	$rq_modifier_institutionnel = "institutionnelparent='".$_POST['institutionnelparent']."',";
    	$virg = '';
		foreach($langues as $langue)
		{
			if(in_array($langue,$langue_access)) 
			{
			    $rq_modifier_institutionnel .= $virg . " contenu" . $langue . "='" . mysqli_real_escape_string($DB_site2->link_id,$_POST['content' . $langue]) . "'";
			    $rq_modifier_institutionnel .= ", libelle" . $langue . "='" . mysqli_real_escape_string($DB_site2->link_id,$_POST['libelle' . $langue]) . "'";
			    $rq_modifier_institutionnel .= ", libelleFooter" . $langue . "='" . mysqli_real_escape_string($DB_site2->link_id,$_POST['libelleFooter' . $langue]) . "'";
			    $rq_modifier_institutionnel .= ", ref_title" . $langue . "='" . mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_title' . $langue]) . "'";
			    $rq_modifier_institutionnel .= ", ref_description" . $langue . "='" . mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_description' . $langue]) . "'";
			    $virg = ",";
			} 
		}

		(!is_null($_POST['sans_lien'])?$rq_modifier_institutionnel .= ", sans_lien =" . $_POST['sans_lien']:$rq_modifier_institutionnel .= ", sans_lien =false");

		if($_POST['institutionnelid'] > 0) //La page existe déjà : mode "mise à jour"
		{
			$rq_modifier_institutionnel = "UPDATE institutionnel SET " . $rq_modifier_institutionnel . " WHERE institutionnelid = " . $institutionnelid;
			$DB_site2->query($rq_modifier_institutionnel);
		}
		else //La page n'existe pas : mode "création"
		{
			$rq_modifier_institutionnel = "INSERT INTO institutionnel SET menu = " . $_POST['menu'] . ", site_code = '" . $_POST['site_code'] . "',menuid_instit ='1' ," . $rq_modifier_institutionnel;
			$DB_site2->query($rq_modifier_institutionnel);

			$institutionnelid = $DB_site2->insert_id();
		}


    }
    /*********************** SUPPRIMER UN BLOC *************************************/
	if(isset($_GET['suppr2'])){
		$path = "../configurations/".$host."/images/bloc_instit/";
		$blocid = $_GET['blocid'];
		$institutionnelid = $_GET['institutionnelid'];
		$l = $_GET['lg'];
		$position_bloc = $DB_site2->query_first("SELECT ordre, image FROM institutionnel_bloc WHERE institutionnel_blocid=".$blocid);
		if(is_null($position_bloc[0])) exit;
		$image = $position_bloc['image'];
		$position_bloc = $position_bloc['ordre'];
		$autres_blocs = $DB_site2->query("SELECT institutionnel_blocid, ordre FROM institutionnel_bloc WHERE institutionnelid='$institutionnelid' AND langue='$l' AND ordre > $position_bloc ORDER BY ordre ASC");
		while ($req_autres_blocs = $DB_site2->fetch_array($autres_blocs)) {
			$position_temp = $req_autres_blocs['ordre'] - 1 ;
			$DB_site2->query("UPDATE institutionnel_bloc SET ordre = $position_temp WHERE institutionnel_blocid =".$req_autres_blocs['institutionnel_blocid']);
		}

		$DB_site2->query("DELETE FROM institutionnel_bloc WHERE institutionnel_blocid=".$blocid);
		if(!is_null($image)) unlink($path.$blocid.".".$image);
	}
    /************ AJOUTER UN BLOC ***************************/
	if (isset($_POST['ajout_content'])) {
		$institutionnelid = $_POST['ajout_content'];
		$typeid = $_POST['ajout_type'];
		if(isset($_POST['alt_img'])) $alt_img = mysqli_real_escape_string($DB_site2->link_id,$_POST['alt_img']);
		if(isset($_POST['url_img'])) $url_img = mysqli_real_escape_string($DB_site2->link_id,$_POST['url_img']);
		$req = $DB_site2->query_first("SELECT institutionnel_blocid FROM institutionnel_bloc ORDER BY institutionnel_blocid DESC LIMIT 1");
		$blocid = intval($req[0])+1;
		$l = mysqli_real_escape_string($DB_site2->link_id,$_POST['ajout_lg']);
		$extensions_valides = array( 'jpg','png' );
		$path = "../configurations/".$host."/images/bloc_instit/";
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
		}
		if(isset($_POST['bloc_new']))
			$bloc_content = mysqli_real_escape_string($DB_site2->link_id,$_POST['bloc_new']);//on récupère le contenu text

		//on recherche l'ordre du bloc
		$ordre = $DB_site2->query_first("SELECT ordre FROM institutionnel_bloc WHERE institutionnelid ='".$institutionnelid."' AND langue ='".$l."' ORDER BY ordre DESC LIMIT 1");
		$new_ordre = intval($ordre[0])+1;

		if($bloc_content == '' && $image == 'null'){
			echo "<script>alert('Attention le bloc est vide, celui-ci ne sera pas enregistré');</script>";
		}else{
			//on insère le contenu dans la base
			if($image=='')
				$DB_site2->query("INSERT INTO institutionnel_bloc (institutionnel_blocid,institutionnelid,typeid,contenu,langue,ordre) VALUES ($blocid,$institutionnelid,$typeid,'$bloc_content','$l',$new_ordre); ");
			elseif ($bloc_content =='') {
				$DB_site2->query("INSERT INTO institutionnel_bloc (institutionnel_blocid,institutionnelid,typeid,image,langue,ordre, alt_img,url_img) VALUES ($blocid,$institutionnelid,$typeid,'$image','$l',$new_ordre,'$alt_img','$url_img'); ");
			}
			else
				$DB_site2->query("INSERT INTO institutionnel_bloc (institutionnel_blocid,institutionnelid,typeid,image,contenu,langue,ordre, alt_img,url_img) VALUES ($blocid,$institutionnelid,$typeid,'$image','$bloc_content','$l',$new_ordre, '$alt_img','$url_img'); ");

			//header('Location:'.BACK_URL.'/institutionnel.php?action=editer&institutionnelid='.$institutionnelid.'&site_code=base',false);
			//exit;
		}
		

	}

    //changer l'ordre des blocs de contenu
	if(isset($_GET['upbloc'])){
		$blocid = htmlspecialchars($_GET['blocid']);
		$l = htmlspecialchars($_GET['lg']);
		$institutionnelid = htmlspecialchars($_GET['institutionnelid']);
		$positionBloc = $DB_site2->query_first("SELECT ordre FROM institutionnel_bloc WHERE institutionnel_blocid=".$blocid);
		$position1 = $positionBloc[0];
		$new_position = $position1 - 1;
		$changed = $DB_site2->query_first("SELECT institutionnel_blocid FROM institutionnel_bloc WHERE langue='".$l."' AND ordre = ".$new_position." AND institutionnelid =".$institutionnelid);
		if(!is_null($changed[0])){
			$DB_site2->query("UPDATE institutionnel_bloc SET ordre ='".$new_position."' WHERE institutionnel_blocid =".$blocid);
			$DB_site2->query("UPDATE institutionnel_bloc SET ordre ='".$position1."' WHERE institutionnel_blocid =".$changed[0]);
		}

	}
	if(isset($_GET['downbloc'])){
		$blocid = htmlspecialchars($_GET['blocid']);
		$l = htmlspecialchars($_GET['lg']);
		$institutionnelid = htmlspecialchars($_GET['institutionnelid']);
		$positionBloc = $DB_site2->query_first("SELECT ordre FROM institutionnel_bloc WHERE institutionnel_blocid=".$blocid);
		$position1 = $positionBloc[0];
		$new_position = $position1 + 1;
		$changed = $DB_site2->query_first("SELECT institutionnel_blocid FROM institutionnel_bloc WHERE langue='".$l."' AND ordre = ".$new_position." AND institutionnelid =".$institutionnelid);
		if(!is_null($changed[0])){
			$DB_site2->query("UPDATE institutionnel_bloc SET ordre ='".$new_position."' WHERE institutionnel_blocid ='".$blocid."' ");
			$DB_site2->query("UPDATE institutionnel_bloc SET ordre ='".$position1."' WHERE institutionnel_blocid =".$changed[0]);
		}
	}
    
    //Chargements des informations de la page post-modifications, pour qu'elle soit à jour
    $sql = "SELECT i.*";
    $sql .= " FROM institutionnel i";
    $sql .= " WHERE i.institutionnelid = " . $institutionnelid;
    $rs_institutionnel = $DB_site2->query_first($sql);

    echo "<p style=\"color:red;font-size:25px;font-weight:600;\">ATTENTION , POUR MODIFIER LE CONTENU DE CETTE PAGE VOUS DEVEZ AVOIR RECU AU PREALABLE UNE FORMATION DU SERVICE TECHNIQUE   !!!</p>";
    echo ""
    . "<div align=\"center\">"
    	. "<h3>Modifier une page</h3>"
    . "</div>"
    . "<form method=\"post\" name=\"form_institutionnel\" action=\"institutionnel.php?action=editer&site_code=".$_GET['site_code']."&institutionnelid=".$_POST['institutionnelid']."&i_langue=0\">";
    //. "<p>Marque : <select name=\"marque_institutionnelle\">" . $optmarque . "</select><br><span style=\"font-size:10px; color:red;\">Si cette page institutionnelle fait référence à une marque, sélectionnez-là dans la liste. Autrement, ne tenez pas compte de ce champs.</span></p>";


	$rs_instit = $DB_site2->query_first( "select * FROM institutionnel where institutionnelid = $institutionnelid" );		
    if ($rs_instit[institutionnelparent] == "0")
		$liste_parents = "<option value=\"0\" selected>Aucun parent</>";
	else
		$liste_parents = "<option value=\"0\">Aucun parent</>";
	$parents = $DB_site2->query("SELECT * FROM institutionnel WHERE institutionnelparent=0 AND site_code='".$_GET['site_code']."' AND active='1' ORDER BY libelleFR ASC");
	while( $parent = $DB_site2->fetch_array( $parents ) )
		{
		if ($parent[institutionnelid] == $rs_instit[institutionnelparent])
			$liste_parents .= "<option value=\"$parent[institutionnelid]\" selected>$parent[$libelle]</>";
		else
			$liste_parents .= "<option value=\"$parent[institutionnelid]\">$parent[$libelle]</>";
		}
	$select_parent = "<select name=\"institutionnelparent\">$liste_parents</select>";	

	/// sans_lien, pouvoir activer ou désactiver les liens dans les titres
	$sanslien="";
	if($rs_instit['sans_lien']==true){
		$sanslien="checked";
	}

	if($filter_langue)
	    echo "<input type=\"hidden\" name=\"institutionnelparent\" value=\"$rs_instit[institutionnelparent]\">";
	else
	    echo "<p>Bloc PARENT : $select_parent</p>
	<p>Titre sans lien <input type=\"checkbox\" name=\"sans_lien\" value=\"true\" $sanslien></p>";

    $pathImgBloc = "../configurations/".$host."/images/bloc_instit/";
    foreach($langues as $langue)
    {
		$flag = $langue;
		if($langue=="EN") $flag = "GB";
		if($langue=="SP") $flag = "ES";
		$readonly =! in_array($langue,$langue_access);

		//Récupération des blocs de contenu
		$blocs = $DB_site2->query("SELECT institutionnel_blocid,institutionnelid,typeid,image ,contenu, ordre, commentaire, bloc_libelle as title FROM institutionnel_bloc as cb INNER JOIN institutionnel_bloc_type as ibt ON cb.typeid=ibt.ibtid WHERE institutionnelid =".$institutionnelid." AND langue='".$langue."' ORDER BY ordre");

		//on cherche la première position
		$first_position = $DB_site2->query_first("SELECT ordre FROM institutionnel_bloc WHERE institutionnelid=".$institutionnelid." AND langue='".$langue."' ORDER BY ordre ASC LIMIT 1");
		$first_position = intval($first_position[0]);

		if($first_position != ''){
			//on cherche la dernière position
			$last_position = $DB_site2->query_first("SELECT ordre FROM institutionnel_bloc WHERE institutionnelid=".$institutionnelid." AND langue='".$langue."' ORDER BY ordre DESC LIMIT 1");
			$last_position = intval($last_position[0]);
		}else{
			$first_position = 0;
		}
		
		
		echo "<br>"
		. "<p><img src=\"images/drapeaux/" . $flag . ".gif\" width=\"20\"> Titre de la page " . $langue . " : " . ($readonly ? $rs_institutionnel['libelle' . $langue] : "<input type=\"text\" name=\"libelle" . $langue . "\" value=\"" . $rs_institutionnel['libelle' . $langue] . "\" size=\"60\">") . "</p>"
		. "<p><img src=\"images/drapeaux/" . $flag . ".gif\" width=\"20\"> Titre court (footer) de la page " . $langue . " : ".($readonly ? $rs_institutionnel['libelleFooter' . $langue] : "<input type=\"text\" name=\"libelleFooter" . $langue . "\" value=\"" . $rs_institutionnel['libelleFooter' . $langue] . "\" size=\"60\">") . "</p>"
		. "<p><img src=\"images/drapeaux/" . $flag . ".gif\" width=\"20\"> CONTENU " . $langue . " : </p>" ;

		//Affichage des Blocs Existants
		echo "<p>Liste des Blocs existants :</p>";
		if($DB_site2->num_rows($blocs) != 0){
		    while($leBloc = $DB_site2->fetch_array($blocs)){
		    	if($leBloc['image'] != '' )
		    		$filemtime = filemtime($pathImgBloc.$leBloc['institutionnel_blocid'].".".$leBloc['image']);

	    		echo "<div class=\"row\"><div class=\"col-xs-1\" >";
	    		if($leBloc['ordre'] != $first_position){
	    			echo "<a href=\"institutionnel.php?action=editer&upbloc=1&blocid=".$leBloc['institutionnel_blocid']."&lg=".$langue."&institutionnelid=".$institutionnelid."&site_code=base\"><img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>";
	    		}
	    		if($leBloc['ordre'] != $last_position){
	    			echo "<a href=\"institutionnel.php?action=editer&downbloc=1&blocid=".$leBloc['institutionnel_blocid']."&lg=".$langue."&institutionnelid=".$institutionnelid."&site_code=base\"><img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>";
	    		}
	    		echo "</div>";
	    		echo "<div class=\"col-xs-4\"><p id=\"bloc_".$leBloc['institutionnel_blocid']."\" style=\"display:inline-block\"><strong>".$leBloc['ordre']."</strong> - ".$leBloc['title']." </p></div><div class=\"col-xs-1\"><a href=\"editbloc_instit.php?modif=1&blocid=".$leBloc['institutionnel_blocid']."&institutionnelid=".$institutionnelid."&lg=$langue\">Editer</a></div><div class=\"col-xs-2\"><a href=\"editbloc_instit.php?suppr=1&blocid=".$leBloc['institutionnel_blocid']."&institutionnelid=".$institutionnelid."&lg=$langue\">supprimer</a></div> <div class=\"col-xs-1\"><a onclick=\"apercuBloc('".$leBloc['institutionnel_blocid']."')\" style=\"cursor:pointer;\">Voir</a></div><div class=\"col-xs-3\"><textarea id=\"bloc_com_".$leBloc['institutionnel_blocid']."\" onchange=\"validComBloc('".$leBloc['institutionnel_blocid']."')\">".$leBloc['commentaire']."</textarea></div><br>";
	    		echo "<div class=\"row\" style=\"margin:0;\"><div id=\"apercu_bloc_".$leBloc['institutionnel_blocid']."\" class=\"col-xs-12 voir_bloc\" style=\"display:none;\">";
	    		switch ($leBloc['typeid']) {
	    			case '1':
	    				$contenu_apercu = "<div class=\"col-xs-6\">".$leBloc['contenu']."</div><div class=\"col-xs-6\"><img class=\"img-responsive\" src=\"".$pathImgBloc.$leBloc['institutionnel_blocid'].".".$leBloc['image']."?".$filemtime."\"></div>";
	    				break;
	    			case '2':
	    				$contenu_apercu = "<div class=\"col-xs-6\"><img class=\"img-responsive\" src=\"".$pathImgBloc.$leBloc['institutionnel_blocid'].".".$leBloc['image']."?".$filemtime."\"></div><div class=\"col-xs-6\">".$leBloc['contenu']."</div>";
	    				break;
	    			case '3' :
	    			case '4' :
	    			case '5' :
	    			case '7' :
	    			case '9' :
	    			case '10':
	    			case '11':
	    			case '12':
	    				$contenu_apercu = "<div class=\"col-xs-offset-3 col-xs-6\">".$leBloc['contenu']."</div>";
	    				break;				
	    			case '6':
	    				$contenu_apercu = "<div class=\"col-xs-offset-3 col-xs-6\"><img class=\"img-responsive\" src=\"".$pathImgBloc.$leBloc['institutionnel_blocid'].".".$leBloc['image']."?".$filemtime."\"></div>";
	    				break;
	    			case '8':
	    				$contenu_apercu = "<div class=\"col-xs-offset-3 col-xs-6\"><a class=\"bouton-bloc\">".$leBloc['contenu']."</a></div>";
	    				break;	
	    		}
	    		echo $contenu_apercu;
	    		echo "</div></div></div>";
		    	
		    }
		}else {
			echo "<p>Aucun contenu pour cette langue</p>";
		}
	    echo "<br><br>";

	    //AJOUT D'UN NOUVEAU BLOC de contenu
	    echo "<div class=\"row\"><p>Ajouter un nouveau bloc :</p></div>";
	    $typeBloc = $DB_site2->query("SELECT ibtid,bloc_libelle as title FROM institutionnel_bloc_type");
	    $selectbloc = "<select id=\"typeid_".$langue."\"><option value=\"0\">Choisissez un type de Bloc </option>";
	    //$lstTypebloc="<ul>";
	    while($desBloc = $DB_site2->fetch_array($typeBloc)){
	    	$selectbloc .="<option value=\"".$desBloc['ibtid']."\">".$desBloc['title']."</option>";
	    	//$lstTypebloc.= "<li><a href=\"editbloc_instit.php?create=1&typeid=".$desBloc['ibtid']."&institutionnelid=".$institutionnelid."&lg=".$langue."\">".$desBloc['title']."</a></li>";
	    }
	    //$lstTypebloc .="</ul>";
	    $selectbloc .="</select>";
	    //echo $lstTypebloc;
	    echo "<div class=\"row\">
	    	  	<div class=\"col-xs-6\">
	    	  		<form action=\"editbloc_instit.php\" method=\"GET\">
	    	  			<div class=\"col-xs-6\">";
	    			echo $selectbloc;
	    			echo "<input type=\"hidden\" id=\"create_".$langue."\" value=\"1\">
	    				  <input type=\"hidden\" id=\"institutionnelid_".$langue."\" value=\"".$institutionnelid."\">
	    				  <input type=\"hidden\" id=\"lg_".$langue."\" value=\"".$langue."\">";
	    		echo   "</div>			   
	   				</form>
	   			</div>
	   			<div class=\"col-xs-6\">
	    			<button type=\"button\" onclick=\"newBloc('".$langue."');\">Ajouter</button>
	    		</div>";

	    echo "<br><br><br><br>";

		
		echo ""
		. "<p><img src=\"images/drapeaux/" . $flag . ".gif\" width=\"20\"> Meta title " . $langue . " : ".($readonly ? $rs_institutionnel['ref_title' . $langue] : "<input type=\"text\" name=\"ref_title" . $langue . "\" value=\"" . $rs_institutionnel['ref_title' . $langue] . "\" size=\"60\">") . "</p>"
		. "<p><img src=\"images/drapeaux/" . $flag . ".gif\" width=\"20\"> Meta description :<br>".($readonly ? $rs_institutionnel['ref_description' . $langue] : "<textarea name=\"ref_description" . $langue . "\" cols=\"80\" rows=\"4\">" . $rs_institutionnel['ref_description' .$langue] . "</textarea>")."</p>"
		. "<hr><br>";
	}

	//Rajouter ici le code pour administrer les produits.
	echo "<h3>Administration des produits sur la page :</h3>"
		."<h4>Pour ajouter ou modifier un article cliquez sur un cadre. Pour choisir un ordre d'affichage déplacer l'article à l'endroit souhaité</h4>"
		."<p>Les produits inactifs ne seront pas affiché côté client</p>" ;
	echo "<input type=\"hidden\" name=\"site_code\" value=".$_GET['site_code']." >";

	//Création de la requête SQL pour récupérer tous les produits administrable correspondants à la page sélectionnée 
	$requete = $DB_site2->query("SELECT a.artcode, a.artid, a_s.prix, a.libelleFR, a_s.active, i_a.institutionnel_articleid_page, i_a.ordre FROM institutionnel_article i_a INNER JOIN article a USING(artid) LEFT JOIN article_site a_s USING(artid) WHERE i_a.institutionnelid=".$institutionnelid." AND a_s.site_code=\"".htmlspecialchars($_GET['site_code'])."\"  ORDER BY i_a.ordre ASC");
	print "<ul id=\"articles_table\">";
	$art=array();
	$ligne = 0;
		
	while ($instit_art = $DB_site2->fetch_array($requete)) {
		$art[$instit_art['artid']]=$instit_art;
		$ligne++;
	}
	$fill = 6;
	if(intval($institutionnelid) == 221 || intval($institutionnelid) == 234) $fill = 36;
	if($ligne !=0){
    	$devise_site=$devises->symbole($sites[$_GET['site_code']]['devise']);
	    //on affiches les articles dans un tableau
	    foreach($art as $articles){
		$instit_art=$articles;
		print "<li id=\"instart_".$articles['artid']."\" onclick=\"selectcdc(".$articles['artid'].")\">".vignette($instit_art,$devise_site)."<span class=\"closee\" onclick=\"del(".$articles['artid'].")\">X</span></li>";
	    }
    //si il n'y a pas 6 articles déjà sélectionnés on creer des cases avec possibilité de rajouter des articles
	    if(($fill-$ligne) !=0){
	    	for($y=1;$y<=($fill-$ligne);$y++){
	    		print "<li id=\"instart_".($y + $ligne)."\" onclick=\"selectcdc(".($y + $ligne).")\"> </li>";
	    	}
	    }
	}else{
		$art=array_fill(0,$fill,"");
		for($i=1;$i<=$fill;$i++){
			print "<li id=\"instart_".$i."\" onclick=\"selectcdc(".$i.")\"><a>Ajouter un produit</a></li>";
		}
	}
	print "</ul>";
	
	// Champs "hidden" 
	echo ""
		. "<input type=\"hidden\" name=\"action\" value=\"editer\">"
		. "<input id=\"page_id\" type=\"hidden\" name=\"institutionnelid\" value=\"" . $institutionnelid . "\">"
		. "</form>";
	echo "<div id=\"modif\">
			<form name=\"modifinstart\" method=\"post\" action=\"#articles_table\">
			<input type=\"hidden\" name=\"instid\">
			<input type=\"hidden\" name=\"artid\">
			Changer la référence : <input type=\"text\" onkeyup=\"srcartid(this.value)\">
			<div id=\"results\"></div>
			</form>
			</div>";
	echo "<form id=\"del_art\" method=\"post\" action=\"institutionnel.php?action=editer&site_code=base&institutionnelid=".$institutionnelid."#articles_table\">
			<input type=\"hidden\" name=\"artid_instart\" value=\"\">
			<input type=\"hidden\" name=\"del_instart\" value=\"1\">
		</form>";		
	echo "<br>";	
	echo "<br>";
	echo "<br>";		
	echo "<button type=\"button\" onclick=\"valideform()\"  >Enregistrer</button>";		
	echo "<div align=\"center\"><a href=\"institutionnel.php\" target=\"_self\">Annuler</a></div>"
	. "<script language=\"javascript\">"
		. "setTimeout(\"autosave()\",900000);"
		. "function autosave(){"
			. "if(confirm(\"Par sécurite la page doit être enregistrée, voulez-vous le faire maintenant ?\"))"
			. "document.forms.form_institutionnel.submit();"
			. "else"
			. "setTimeout(\"autosave()\",900000);"
		. "}"
		."function newBloc(langue){
				var create = $('#create_'+langue).val();
				var typeid = $('#typeid_'+langue).val();
				var lg = $('#lg_'+langue).val();
				var institutionnelid = $('#institutionnelid_'+langue).val();
				if(typeid != 0){
					document.location.href='editbloc_instit.php?create='+create+'&typeid='+typeid+'&institutionnelid='+institutionnelid+'&lg='+lg ;
				}
			}"
		."function apercuBloc(blocid){
  			$('#apercu_bloc_'+blocid).toggleClass('apercu_bloc');
  		  }"	
		. "CKEDITOR.config.width='1020px';"
		. "CKEDITOR.config.height='500px';"
		//. "CKEDITOR.config.filebrowserBrowseUrl='ckbrowser.php';"
		//. "//CKEDITOR.config.filebrowserUploadUrl='ckupload.php';"
		. "CKEDITOR.config.filebrowserBrowseUrl = '/ckeditor/kcfinder/browse.php?type=files';"
		. "CKEDITOR.config.filebrowserImageBrowseUrl = '/ckeditor/kcfinder/browse.php?type=images';"
		. "CKEDITOR.config.filebrowserUploadUrl = '/ckeditor/kcfinder/upload.php?type=files';"
		. "CKEDITOR.config.filebrowserImageUploadUrl = '/ckeditor/kcfinder/upload.php?type=images';"
	. "</script>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AJOUTER UNE PAGE INSTITUTIONNELLE (action=ajouter)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "ajouter" )
{
	echo ""
	. "<div align=\"center\"><h3>Ajouter une page</h3></div>"
	. "<form method=\"post\" name=\"form_institutionne\">"
		. "<input type=\"hidden\" name=\"site_code\" value=\"" . $_GET['site_code'] . "\">";

	$liste_parents = "<option value=\"0\">Aucun parent</>";
	$parents = $DB_site2->query("SELECT * FROM institutionnel WHERE institutionnelparent=0 AND menuid_instit=".intval($menu)." AND site_code='".$_GET['site_code']."' ORDER BY libelleFR ASC");

	/// sans_lien, pouvoir activer ou désactiver les liens dans les titres
	$sanslien="";
	if($rs_instit['sans_lien']==true){
		$sanslien="checked";
	}

	while( $parent = $DB_site2->fetch_array( $parents ) )
		{
		$liste_parents .= "<option value=\"$parent[institutionnelid]\">$parent[$libelle]</>";
		}
	print "<p>Page parent : <select name=\"institutionnelparent\">$liste_parents</select></p>
	<p>Titre sans lien <input type=\"checkbox\" name=\"sans_lien\" value=\"true\" $sanslien></p>";

	foreach($langues as $langue)
	{
		$flag = $langue;
		if($langue=="EN") $flag = "GB";
		if($langue=="SP") $flag = "ES";

		echo ""
		. "<p><img src=\"images/drapeaux/" . $flag . ".gif\" width=\"20\"> Titre de la page " . $langue . " : <input type=\"text\" name=\"libelle" . $langue . "\" value=\"" . $rs_institutionnel['libelle'.$langue] . "\" size=\"60\"></p>"
		. "<p><img src=\"images/drapeaux/" . $flag . ".gif\" width=\"20\"> Titre court (footer) de la page " . $langue . " : <input type=\"text\" name=\"libelle" . $langue . "\" value=\"" . $rs_institutionnel['libelle' . $langue] . "\" size=\"60\"></p>"
		. "<p><img src=\"images/drapeaux/" . $flag . ".gif\" width=\"20\"> Meta title " . $langue . " : <input type=\"text\" name=\"ref_title" . $langue . "\" value=\"" . $rs_institutionnel['ref_title' . $langue] . "\" size=\"60\"></p>"
		. "<p><img src=\"images/drapeaux/" . $flag . ".gif\" width=\"20\"> Meta description :<br><textarea name=\"ref_description" . $langue . "\" cols=\"80\" rows=\"4\">" . $rs_institutionnel['ref_description' . $langue]."</textarea></p>"
		. "<p style=\"color:red;\">Pour ajouter du contenu, il faut d'abord Ajouter cette nouvelle page et ensuite l'éditer. </p>"
		. "<hr><br>";
	}
		
	// Champs "hidden" 
	echo ""
		. "<input type=\"hidden\" name=\"action\" value=\"editer\">"
		. "<input type=\"hidden\" name=\"institutionnelid\" value=\"0\">"
		. "<input type=\"hidden\" name=\"menu\" value=\"$menu\">"
		. "<input type=\"submit\" value=\"Enregistrer\" >"
	. "</form>"
	. "<div align=\"center\"><a href=\"institutionnel.php\" target=\"_self\">Annuler</a></div>"
	. "<script language=\"javascript\">"
		. "setTimeout(\"autosave()\",900000);"
		. "function autosave(){"
			. "if(confirm(\"Par sécurite la page doit être enregistrée, voulez-vous le faire maintenant ?\"))"
				. "document.forms.form_institutionnel.submit();"
			. "else"
				. "setTimeout(\"autosave()\",900000);"
		. "}"
		. "CKEDITOR.config.width='1020px';"
		. "CKEDITOR.config.height='500px';"
		. "CKEDITOR.config.filebrowserBrowseUrl='ckbrowser.php'"
		. "CKEDITOR.config.filebrowserUploadUrl='ckupload.php';"
	. "</script>";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////
// DESARCHIVER UNE PAGE INSTITUTIONNELLE (action=archiver)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "desarchiver" || $action == "menu")
{	
	$action ="";
	$libelle_page = $DB_site2->query_first("SELECT libelleFR FROM institutionnel WHERE institutionnelid=".$_GET['institutionnelid']);
	echo "Sur quel menu voulez-vous affecter la page <strong>".$libelle_page[0]." </strong>?" ;
	echo "<form id=\"selection_menu\" method=\"post\" action=\"#\" >
	<select name=\"num_menu\" >
		<option value=\"0\"> </option>";
		$menus = $DB_site2->query("SELECT * FROM menu_institutionnel");
		while($row = $DB_site2->fetch_array($menus)){
			echo "<option value=\"".$row['menuid']."\" >".$row['libelle']."</option>";
		}

echo "	</select>
	<input type=\"hidden\" name=\"institutionnelid\" value=".$_GET['institutionnelid']." >
	<input type=\"submit\" value=\"valider\" > 
	</form> ";


	
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER UNE PAGE INSTITUTIONNELLE (action=supprimer)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "supprimer" )
{
	// Confirmation de suppression
	echo "Etes-vous sûr(e) de vouloir supprimer cette page institutionnelle ?<br><br>
		  <a href=\"institutionnel.php?action=supprimer2&institutionnelid=$institutionnelid&menu=$menu\">Oui</a>
		  | <a href=\"institutionnel.php\">Non</a>";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER UNE PAGE INSTITUTIONNELLE (action=supprimer2)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "supprimer2" )
{
	// On décalle toutes les positions des pages suivantes
	$position_page = $DB_site2->query_first( "SELECT position FROM institutionnel
											  WHERE institutionnelid = $institutionnelid" );
	$position_page = $position_page[0];
	
	$rq_positions_suivantes = $DB_site2->query( "SELECT institutionnelid, position FROM institutionnel
												 WHERE position > $position_page AND menu = '$menu'" );
	
	while( $rs_positions_suivantes = $DB_site2->fetch_array( $rq_positions_suivantes ) ) 
	{
		$position_temp = $rs_positions_suivantes[position] - 1;
		$rq_modifier_position = "UPDATE institutionnel SET position = '$position_temp' 
								 WHERE institutionnelid = '$rs_positions_suivantes[institutionnelid]'";
		$DB_site2->query( $rq_modifier_position );
	}
	
	// Suppression
	$rq_supprimer = "DELETE FROM institutionnel WHERE institutionnelid = $institutionnelid";
	$DB_site2->query( $rq_supprimer );

	echo "<script language=\"javascript\">location.href=\"institutionnel.php\";</script>";
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