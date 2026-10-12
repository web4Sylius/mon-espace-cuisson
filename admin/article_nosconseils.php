<?php

$article=$DB_site2->query_first("SELECT * FROM article WHERE artid=".$artid);
$marque=$DB_site2->query_first("SELECT m.* FROM marque m INNER JOIN article_marque am USING(marqueid) WHERE am.artid=".$artid);
$trmarque=strtolower(preg_replace("/[^a-zA-Z0-9]/","",$marque['libelle']));

$listconseil=array();
$activconseil=array();
$req=$DB_site2->query("SELECT * FROM article_nosconseils WHERE artid=".$artid);
while($conseil=$DB_site2->fetch_array($req)){
    $activconseil[]=$conseil['path']; //"<p class=\"\">".$template->translate($conseil['path'])." <img src=\"images/icon-x.svg\" width=\"20\" align=\"absmiddle\" onclick=\"supp_conseil(".$conseil['id'].")\"></p>";
}

foreach(array("conseil_installation","conseil_utilisation","conseil_securite","conseil_entretien") as $typeconseil){
    $req=$DB_site2->query("SELECT path,FR FROM traduction WHERE path LIKE '/article/".$typeconseil."/".$trmarque."%'");
    while($conseil=$DB_site2->fetch_array($req)){
        $listconseil[$typeconseil].="<p><input type=\"checkbox\" name=\"conseil[".$typeconseil."][]\" value=\"".$conseil['path']."\"".(in_array($conseil['path'],$activconseil)?" checked":"").">&nbsp;".$conseil['FR']."</p>";
    }
}

$newconseil="";
foreach($langues as $l){
    $newconseil.="<p>".$l." <input type=\"text\" name=\"".$l."\" size=\"40\"></p>";
}

$pdfinstructions="";
if(count($listconseil)>0){
    $pdfinstructions="<p><a href=\"instructions.php?artid=".$artid."\" target=\"_blank\"><img src=\"images/icon/pdf.png\" width=\"40\"> Nos conseils</a></p>";
}

$contenu_onglet .= "<h3>Nos conseils</h3>
		<font class=\"cost\">$erreur</font>
		<form name=\"formu\" method=\"post\" enctype=\"multipart/form-data\" style=\"display:inline\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"del\" value=\"\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
        $pdfinstructions
        <p><input type=\"checkbox\" name=\"instructions\"".($article['instructions']==1?" checked":"")."> Activer l'envoi du pdf</p>
		<table align=\"center\" width=\"100%\">
		<tr id=\"tr_conseil_installation\">
		  <td>Conseils d'installation :<br><a href=\"javascript:newconseil('conseil_installation')\">Nouveau</a></td>
		  <td>".$listconseil['conseil_installation']."</td>
		</tr>
		<tr>
		  <td colspan=\"4\" style=\"border-bottom:solid 1px black;\">&nbsp;</td>
		</tr>
		<tr id=\"tr_conseil_utilisation\">
		  <td>Conseils d'utilisation :<br><a href=\"javascript:newconseil('conseil_utilisation')\">Nouveau</a></td>
		  <td>".$listconseil['conseil_utilisation']."</td>
		</tr>
		<tr>
		  <td colspan=\"4\" style=\"border-bottom:solid 1px black;\">&nbsp;</td>
		</tr>
		<tr id=\"tr_conseil_entretien\">
		  <td>Conseils d'entretien :<br><a href=\"javascript:newconseil('conseil_entretien')\">Nouveau</a></td>
		  <td>".$listconseil['conseil_entretien']."</td>
		</tr>
		</table>
		</form>
		<form name=\"newconseil\" method=\"post\" style=\"display:none;\">
            <input type=\"hidden\" name=\"artid\" value=\"$artid\">
            <input type=\"hidden\" name=\"old\" value=\"nosconseils\">
            <input type=\"hidden\" name=\"action2\" value=\"nosconseils\">
            <input type=\"hidden\" name=\"typeconseil\" value=\"\">
            <input type=\"hidden\" name=\"marque\" value=\"".$trmarque."\">
            Ajouter un conseil :
            ".$newconseil."
            <input type=\"button\" value=\"Annuler\" onclick=\"document.forms['newconseil'].style.display='none';\">
            <input type=\"submit\" value=\"Ajouter\">
		</form>";

$contenu_onglet.="<script language=\"javascript\">
function newconseil(typeconseil){
    var pos=document.getElementById('tr_'+typeconseil).getClientRects();
    var styles={'position':'absolute','border':'solid 1px black','display':'block','left':pos[0].x,'top':pos[0].y,'padding':'10px','background':'white'};
    for(x in styles)
        document.forms['newconseil'].style[x]=styles[x];
    
    document.getElementsByName('typeconseil')[0].value=typeconseil;
}
</script>";

?>
