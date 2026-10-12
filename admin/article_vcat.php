<?php

$contenu_onglet .= "
		<script language=\"javascript\">
		function addvcat(vcatid){
			document.getElementsByName('vcatid')[0].value=vcatid;
			document.getElementsByName('vcat_action')[0].value='add';
			clique('vcat','vcat');
		}
		function delvcat(vcatid){
			document.getElementsByName('vcatid')[0].value=vcatid;
			document.getElementsByName('vcat_action')[0].value='delete';
			clique('vcat','vcat');
		}
		</script>
		<h3>Catégories virtuelles</h3>
		<font class=\"cost\">$erreur</font>
		<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"mq2\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<input type=\"hidden\" name=\"vcatid\" value=\"\">
		<input type=\"hidden\" name=\"vcat_action\" value=\"\">";


		$requete=$DB_site2->query("SELECT * FROM vcategorie WHERE artid=".$artid);
		while($c=$DB_site2->fetch_array($requete)) $vcat[$c['catid']]=true;
		$cats=$DB_site2->query("SELECT * FROM categorie WHERE parentid=0");
		$article=$DB_site2->query_first("SELECT catid FROM article WHERE artid=".$artid);
		
		$contenu_onglet.=drawvcat(0,$article[0],$vcat)."
		</form>";

function drawvcat($parent,$catid,$checkcatid){
	global $DB_site2;
	$return="";

	$sscats=$DB_site2->query("SELECT catid,libelleFR FROM categorie WHERE parentid='$parent' ORDER BY position");
	if($DB_site2->num_rows($sscats)>0){
	  $return.="<div style=\"text-align:left;margin-left:5px;padding-left:10px;border-left:solid thin black;\">";
	  while($sscat=$DB_site2->fetch_array($sscats)){
	      $return.="<input type=\"checkbox\" name=\"dcat[]\" value=\"$sscat[catid]\" ".($checkcatid[$sscat['catid']]?" checked":"")." ".($sscat['catid']==$catid?"style=\"display:none;\"":"")."> <span".($sscat['catid']==$catid?" style=\"font-weight:bold;\"":"").">".$sscat['libelleFR']."</span><br>";
	      $return.=drawvcat($sscat['catid'],$catid,$checkcatid);
	  }
	  $return.="</div>";
	}
	return $return;
}
?>
