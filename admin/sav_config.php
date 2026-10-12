<?php
require "admin_global.php";
include "./header.php";
include "./menu.php";
include "../class/fonctions.php";


////////////////////////////////////////////////////////////////////////////////////////////////////////
// MONTER D'UNE POSITION
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( isset( $action ) && $action == "up" ){
	$position = $DB_site2->query_first( "SELECT ordre FROM sav_etape WHERE etapeid = '".$etapeid."'" );
	$nouvelle_position = $position[0]-1;
	$position2 = $DB_site2->query_first( "SELECT etapeid FROM sav_etape WHERE ordre = '".$nouvelle_position."' AND menuid = '".$menuid."'" );
	$rq_modifier_position = "UPDATE sav_etape SET ordre='".$nouvelle_position."' WHERE etapeid = '".$etapeid."'";
	$DB_site2->query( $rq_modifier_position );
	$rq_modifier_position2 = "UPDATE sav_etape SET ordre='".$position[0]."' WHERE etapeid = '".$position2[etapeid]."'";
	$DB_site2->query( $rq_modifier_position2 );
	$action = "";	
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// DESCENDRE D'UNE POSITION
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( isset( $action ) && $action == "down" ){
	$position = $DB_site2->query_first( "SELECT ordre FROM sav_etape WHERE etapeid = '".$etapeid."'" );
	$nouvelle_position = $position[0]+1;
	$position2 = $DB_site2->query_first( "SELECT etapeid FROM sav_etape WHERE ordre = '".$nouvelle_position."' AND menuid = '".$menuid."'" );
	$rq_modifier_position = "UPDATE sav_etape SET ordre='".$nouvelle_position."' WHERE etapeid = '".$etapeid."'";
	$DB_site2->query( $rq_modifier_position );
	$rq_modifier_position2 = "UPDATE sav_etape SET ordre='".$position[0]."' WHERE etapeid = '".$position2[etapeid]."'";
	$DB_site2->query( $rq_modifier_position2 );
	$action = "";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AJOUTER UNE ETAPE (action=doadd)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
if( $action == "doadd" ){
	$menuid=$_POST['menuid'];
	$menu = $DB_site2->query_first("SELECT * FROM sav_menu WHERE menuid='".$menuid."' ");
	$libelle=$_POST[libelle];
	$maxordre=$DB_site2->query_first("SELECT MAX(ordre) FROM sav_etape WHERE menuid='".$menuid."' ");
	if($maxordre[0]==""){
		$max_ordre=1;
	} else {
		$max_ordre=$maxordre[0]+1;
	}
	$DB_site2->query("INSERT INTO sav_etape (menuid, libelle, ordre) VALUES ('".$menuid."' , '".addslashes($libelle)."' , '".$max_ordre."' ) ");
	$action="";
}


////////////////////////////////////////////////////////////////////////////////////////////////////////
// MODIFIER UNE ETAPE (action=doedit)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
if( $action == "doedit" ){
	$etapeid=$_POST['etapeid'];
	$DB_site2->query("UPDATE sav_etape SET libelle= '".addslashes($libelle_etape)."' WHERE etapeid='".$etapeid."' ");
	$action="";
}



////////////////////////////////////////////////////////////////////////////////////////////////////////
// AFFICHER LES ETAPES
////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!isset( $action ) || $action == ""){
	echo "<div align=\"center\"><h3>listes des étapes par litiges</h3></div>";
	
	$typelitiges = $DB_site2->query("SELECT * FROM sav_typelitige ORDER BY ordre ASC");
	while ($typelitige = $DB_site2->fetch_array($typelitiges)) {
		// boucle sur les types de litiges
		echo "<strong>Litige : ".$typelitige[libelle]."</strong><br>\n";
		$menus = $DB_site2->query("SELECT * FROM sav_menu WHERE typelitigeid='".$typelitige[typelitigeid]."' ORDER BY ordre ASC");
		while ($menu = $DB_site2->fetch_array($menus)) {
			echo "<i>---- Menu : ".$menu[libelle]."</i><br>\n";
			$i=0;
			$etapes = $DB_site2->query("SELECT * FROM sav_etape WHERE menuid='".$menu[menuid]."' ORDER BY ordre ASC");
			$maxordre=$DB_site2->query_first("SELECT MAX(ordre) FROM sav_etape WHERE menuid='".$menu[menuid]."' ");
			
			while ($etape = $DB_site2->fetch_array($etapes)) {
				$i++;
				echo "---- ---- Etape :";
				
				if($i>1){
					echo "<a href=\"sav_config.php?action=up&menuid=".$menu[menuid]."&etapeid=".$etape[etapeid]."\"><img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\"></a>";
				} else {
					echo " &nbsp ";
				}
				if($i<$maxordre[0]){
					echo "<a href=\"sav_config.php?action=down&menuid=".$menu[menuid]."&etapeid=".$etape[etapeid]."\"><img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\"></a>";
				} else {
					echo " &nbsp ";
				}
				echo $etape[libelle];
				echo "<a href=\"sav_config.php?action=edit&etapeid=".$etape[etapeid]."\"><img src=\"images/editer.gif\" alt=\"Modifier\"></a><br>\n";
				
				
			}
			
			
			if( $i == 0 ){
				echo "Il n'y a pas d'étape dans ce menu<br>\n";
			}
			echo "---- ---- <a href=\"sav_config.php?action=add&typelitigeid=".$typelitige[typelitigeid]."&menuid=".$menu['menuid']."\">Ajouter une étape dans ce menu</a>
			<br><br>";
		}
		echo "<hr>";
	}
}



////////////////////////////////////////////////////////////////////////////////////////////////////////
// AJOUTER UNE ETAPE (action=add)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
if( $action == "add" ){
	$menuid=$_GET['menuid'];
	$menu = $DB_site2->query_first("SELECT * FROM sav_menu WHERE menuid='".$menuid."' ");
	$typelitigeid=$_GET['typelitigeid'];
	$typelitige = $DB_site2->query_first("SELECT * FROM sav_typelitige WHERE typelitigeid='".$typelitigeid."' ");
	echo ""
	. "<div align=\"center\"><h3>Ajouter une étape dans un menu</h3></div>"
	. "<form method=\"post\" name=\"form_add\" action=\"sav_config.php\">"
		. "<strong>Litige : ".$typelitige[libelle]."</strong><br>"
		. "<strong>---- Menu : ".$menu[libelle]."</strong><br>"
		. "---- ---- Etape : <input type=\"text\" name=\"libelle\" value=\"\"><br>"
		. "<input type=\"hidden\" name=\"action\" value=\"doadd\">"
		. "<input type=\"hidden\" name=\"menuid\" value=\"".$menuid."\">"
		. "<input type=\"hidden\" name=\"typelitigeid\" value=\"".$typelitigeid."\">"
		. "<input type=\"submit\" value=\"Enregistrer\" >"
	. "</form>"
	. "<div align=\"center\"><a href=\"sav_config.php\" target=\"_self\">Annuler</a></div>";
}


////////////////////////////////////////////////////////////////////////////////////////////////////////
// MODIFIER UNE ETAPE (action=edit)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
if( $action == "edit" ){
	$etapeid=$_GET['etapeid'];
	$etape = $DB_site2->query_first("SELECT * FROM sav_etape WHERE etapeid='".$etapeid."' ");
	echo ""
	. "<div align=\"center\"><h3>Modifier une étape dans un menu</h3></div>"
	. "<form method=\"post\" name=\"form_edit\" action=\"sav_config.php\">"
		. "Libelle <input type=\"text\" name=\"libelle_etape\" value=\"".$etape[libelle]."\"><br>"
		. "<input type=\"hidden\" name=\"action\" value=\"doedit\">"
		. "<input type=\"hidden\" name=\"etapeid\" value=\"".$etapeid."\">"
		. "<input type=\"submit\" value=\"Enregistrer\" >"
	. "</form>"
	. "<div align=\"center\"><a href=\"sav_config.php\" target=\"_self\">Annuler</a></div>";
}



include "./footer.php";
 
?>