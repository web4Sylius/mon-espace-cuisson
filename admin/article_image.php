<?php

$typesconseil=array("installation","utilisation","entretien");

$images_sup="<script language=\"javascript\">
function modiflangue(imgid,langue){
    document.location='ajout_produit.php?artid=$artid&old=img&action2=img&langue='+langue+'&imgid='+imgid;
}
function modifutil(imgid,util){
    document.location='ajout_produit.php?artid=$artid&old=img&action2=img&util='+util+'&imgid='+imgid;
}
function modifconseil(imgid,conseil){
    document.location='ajout_produit.php?artid=$artid&old=img&action2=img&conseil='+conseil+'&imgid='+imgid;
}
function modifsite(imgid,site){
    document.location='ajout_produit.php?artid=$artid&old=img&action2=img&site='+site+'&imgid='+imgid;
}
function createposition(){
    var inp=document.createElement('input');
    inp.type='hidden';
    inp.name='createposition';
    inp.value=1;
    document.forms.formu.appendChild(inp);
    clique('img','img');
}
function setambiance(imgid,state){
    document.location='ajout_produit.php?artid=$artid&old=img&action2=img&ambiance='+Number(state)+'&imgid='+imgid;
}
</script>
<table id=\"articlephoto\">";

// création positions si aucune
$psum=$DB_site2->query_first("SELECT SUM(position) FROM articlephoto WHERE artid=$artid");
if($psum[0]==0){
    $imgs=$DB_site2->query("SELECT * FROM articlephoto WHERE artid=$artid ORDER BY articlephotoid");
    $p=0;
    while($ap=$DB_site2->fetch_array($imgs)){
        $DB_site2->query("UPDATE articlephoto SET position=$p WHERE articlephotoid=".$ap['articlephotoid']);
        $p++;
    }
}
if(isset($_GET['move'])){
    $img=$DB_site2->query_first("SELECT * FROM articlephoto WHERE articlephotoid=".$_GET['imgid']);
    if($_GET['move']=="up"){
        $before=$DB_site2->query_first("SELECT * FROM articlephoto WHERE artid=$artid AND position<".$img['position']." ORDER BY position DESC");
        if($before['articlephotoid']!="") $DB_site2->query("UPDATE articlephoto SET position=position+1 WHERE articlephotoid=".$before['articlephotoid']);
        $DB_site2->query("UPDATE articlephoto SET position=position-1 WHERE articlephotoid=".$img['articlephotoid']);  
    }
    if($_GET['move']=="down"){
        $after=$DB_site2->query_first("SELECT * FROM articlephoto WHERE artid=$artid AND position>".$img['position']." ORDER BY position");
        if($after['articlephotoid']!="") $DB_site2->query("UPDATE articlephoto SET position=position-1 WHERE articlephotoid=".$after['articlephotoid']);
        $DB_site2->query("UPDATE articlephoto SET position=position+1 WHERE articlephotoid=".$img['articlephotoid']);  
    }
}

if (isset($del) and $del == '2')
{
    $txt_sup = "<bR>Pour rafraîchir les images, appuyer sur la touche F5 puis cliquez sur OK" ;
}
else
{
    $txt_sup = "" ;
}
					
$article=$DB_site2->query_first("select * from article where artid = '$artid'") ;

if ($article[image] != "" or $article[image] != NULL)
    {
    $imagedebase = "<table>
                        <tr>
                            <td valign=\"middle\">
                                <a href=\"".$imghost."configurations/$host/images/produits/$article[artid].$article[image]?t=".time()."\" target=\"blank\"><img src=\"".$imghost."configurations/$host/images/produits/article/$article[artid].$article[image]?t=".time()."\" name=\"base\"></a>
                            </td>
                            <td valign=\"top\">
                                &nbsp; <a href=\"Javascript: suppr('$article[artid]','0') ;\"><img src=\"images/supprimer.gif\"> Supprimer</a><br>
                                &nbsp; <a href=\"".$imghost."configurations/$host/images/produits/$article[artid].$article[image]\" target=\"blank\">voir la photo en production sans cache</a><br>
                                &nbsp; (faire Ctrl+F5 sur la nouvelle page)<br><br>
                                &nbsp; <span style=\"color:red;font-weight:700;\">FORMAT DE L'IMAGE DE BASE : 800 X 600 !</span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan=\"2\">
                                Légende : <input type=\"text\" name=\"legendebase\" value=\"$article[legende]\">
                            </td>
                        </tr>
                    </table>" ;
    }
else
    {
    $imagedebase = "<table><tr><td align=\"right\">Fichier sur mon disque dur : </td><td align=\"left\"><input type=\"file\" name=\"articleimg\"></td></tr>
    <tr><td align=\"right\">Ou URL : </td><td align=\"left\"><input type=\"text\" size=\"33\" name=\"articleimgurl\"></td></tr>
    <tr><td align=\"right\">Légende : </td><td align=\"left\"><input type=\"text\" name=\"legende\" size=\"15\"></td></tr></table>" ;
    }
				
$articleimages=$DB_site2->query("select * from articlephoto where artid = '$artid' ORDER BY position") ;
$nbimages=$DB_site2->num_rows($articleimages);
$i=0;

while ($articleimage=$DB_site2->fetch_array($articleimages)){
    $i++;
    $move="";
    $ratio = "";
    if($articleimage['ambiance']==1){ // check le ratio de l'image d'ambiance
        $ratio = "<p>Le ratio largeur / hauteur doit être de 1.71</p>";
        $ambImg = getimagesize($imghost."configurations/$host/images/produits/".$articleimage[artid]."_".$articleimage[articlephotoid].".".$articleimage[image]);
        if(round($ambImg[0]/$ambImg[1], 2) != 1.71 && round($ambImg[0]/$ambImg[1], 2) != floatval(1.72) )
            $ratio = "<p style=\"color:red;\">Le ratio largeur / hauteur doit être de 1.71.<br>Veuillez changer la photo.</p>";
    }
    
    $util="<br><input type=\"radio\" name=\"util".$articleimage['articlephotoid']."\" value=\"0\" onclick=\"modifutil(".$articleimage['articlephotoid'].",0)\"".($articleimage['visible']==0?" checked":"")."> Image complémentaire<br><input type=\"radio\" name=\"util".$articleimage['articlephotoid']."\" value=\"1\" onclick=\"modifutil(".$articleimage['articlephotoid'].",1)\"".($articleimage['visible']==1?" checked":"")."> Image principale<br><input type=\"radio\" name=\"util".$articleimage['articlephotoid']."\" value=\"2\" onclick=\"modifutil(".$articleimage['articlephotoid'].",2)\"".($articleimage['visible']==2?" checked":"")."> Désactivée";
    $langueselect="<input type=\"radio\" name=\"langue".$articleimage['articlephotoid']."\"".($articleimage['langue']==""?" checked":"")." onclick=\"modiflangue(".$articleimage['articlephotoid'].",'')\"> Tous";
    $ambiance="<input type=\"checkbox\" id=\"ambiance".$articleimage['articlephotoid']."\" name=\"ambiance".$articleimage['articlephotoid']."\"".($articleimage['ambiance']==1?" checked":"")." onclick=\"setambiance(".$articleimage['articlephotoid'].",this.checked)\"> <label for=\"ambiance".$articleimage['articlephotoid']."\"> Ambiance</label>".$ratio;
    
    foreach($langues as $langue) $langueselect.="<br><input type=\"radio\" name=\"langue".$articleimage['articlephotoid']."\"".($articleimage['langue']==$langue?" checked":"")." onclick=\"modiflangue(".$articleimage['articlephotoid'].",'$langue')\"> ".$langue;
    
    if($i>1) $move="<a href=\"ajout_produit.php?artid=$artid&action2=img&move=up&imgid=$articleimage[articlephotoid]\"><img src=\"images/monter.gif\" title=\"Monter\"></a>";
    if($i<$nbimages) $move.=" &nbsp; <a href=\"ajout_produit.php?artid=$artid&action2=img&move=down&imgid=$articleimage[articlephotoid]\"><img src=\"images/descendre.gif\" title=\"Descendre\"></a>";

    $optconseil="<br><input type=\"radio\" name=\"conseil".$articleimage['articlephotoid']."\" value=\"".$typeconseil."\" onchange=\"modifconseil(".$articleimage['articlephotoid'].",this.value)\"".($articleimage['conseil']==""?" checked":"")."> Aucun";
    foreach($typesconseil as $typeconseil){
        $optconseil.="<br><input type=\"radio\" name=\"conseil".$articleimage['articlephotoid']."\" value=\"".$typeconseil."\" onchange=\"modifconseil(".$articleimage['articlephotoid'].",this.value)\"".($typeconseil==$articleimage['conseil']?" checked":"")."> ".$typeconseil;
    }
    
    $chkpro=array(0=>"",1=>"",2=>"");
    $chkpro[$articleimage['pro']]="checked";
    
    $images_sup .= "
        <tr class=\"head\">
            <td colspan=\"3\">Général</td>
            <td colspan=\"2\">Fiche article/devis</td>
            <td>Instructions</td>
        </tr>
        <tr>
            <td valign=\"middle\">
                <a href=\"".$imghost."configurations/$host/images/produits/$articleimage[artid]_$articleimage[articlephotoid].$articleimage[image]?t=".time()."\" target=\"blank\"><img src=\"".$imghost."/configurations/$host/images/produits/article/$articleimage[artid]_$articleimage[articlephotoid].$articleimage[image]?t=".time()."\" width=\"300\"></a>
            </td>
            <td>
                <p>$move Position n°$articleimage[position]</p>
                <p><a href=\"Javascript: suppr('$article[artid]','$articleimage[articlephotoid]') ;\"><img src=\"images/supprimer.gif\"> Supprimer</a>
                <br><a href=\"".$imghost."configurations/$host/images/produits/$articleimage[artid]_$articleimage[articlephotoid].$articleimage[image]\" target=\"blank\">voir sans cache</a>
                <br>(faire Ctrl+F5)</p>
                <p>Légende :<br><input type=\"text\" name=\"legendesuplem[$articleimage[articlephotoid]]\" value=\"$articleimage[legende]\"></p>
            </td>
            <td valign=\"top\">Langue<br>".$langueselect."</td>
            <td>
                <a href=\"Javascript: passerbase('$article[artid]','$articleimage[articlephotoid]') ;\">Passer en image de base</a>
                <p>".$util."</p>
                <p>".$ambiance."</p>
            </td>
            <td>
                Type site<br>
                <input type=\"radio\" name=\"typeclient".$articleimage['articlephotoid']."\" value=\"0\" onclick=\"modifsite(".$articleimage['articlephotoid'].",0)\" $chkpro[0]> Tous
                <br><input type=\"radio\" name=\"typeclient".$articleimage['articlephotoid']."\" value=\"1\" onclick=\"modifsite(".$articleimage['articlephotoid'].",1)\" $chkpro[1]> Part
                <br><input type=\"radio\" name=\"typeclient".$articleimage['articlephotoid']."\" value=\"2\" onclick=\"modifsite(".$articleimage['articlephotoid'].",2)\" $chkpro[2]> Pro
            </td>
            <td>
                Type de conseil : ".$optconseil."
            </td>
        </tr>" ;
}

$images_sup .= "</table>
<table>
<tr>
    <td colspan=\"2\" align=\"center\"><strong>Autre image</strong></td>
</tr>
<tr>
    <td align=\"right\">Fichiers sur mon disque dur : </td>
    <td align=\"left\">";

for($i=0;$i<=7;$i++) $images_sup.= "<input type=\"file\" name=\"articleimgsup$i\"><br>";

$images_sup.="</td></tr>
<tr>
    <td align=\"right\">URL : </td>
    <td align=\"left\"><input type=\"text\" name=\"articleimgsupurl\" size=\"33\"></td>
</tr> 
<tr>
    <td align=\"right\">Légende : </td>
    <td align=\"left\"><input type=\"text\" name=\"legendesup\" size=\"15\"></td>
</tr>
</table>" ;

$contenu_onglet .= "<h3>Images</h3>
		<font class=\"cost\">$erreur</font>
		<form name=\"formu\" method=\"post\" enctype=\"multipart/form-data\" style=\"display:inline\">
            <input type=\"hidden\" name=\"old\" value=\"\">
            <input type=\"hidden\" name=\"action2\" value=\"\">
            <input type=\"hidden\" name=\"modif\" value=\"0\">
            <input type=\"hidden\" name=\"del\" value=\"\">
            <input type=\"hidden\" name=\"catid\" value=\"$catid\">
            <input type=\"hidden\" name=\"quitter\" value=\"\">
            <input type=\"hidden\" name=\"artid\" value=\"$artid\">
            $txt_sup
            <h4>Image de base</h4>
            $imagedebase
            <hr>
            <h4>Images supplémentaires</h4>
            <p><a href=\"javascript:createposition()\">Recréer l'ordre des images</a> <img src=\"/configurations/$host/images/templates/i.png\" align=\"absmiddle\" title=\"Cliquer ici quand les images ne se rangent pas dans l'ordre désiré\"></p>
            $images_sup
		</form>" ;

?>
