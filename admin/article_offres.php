<?php

include_once("../class/pack.class.php");

$contenu_onglet.= "<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
<input type=\"hidden\" name=\"old\" value=\"\">
<input type=\"hidden\" name=\"quitter\" value=\"\">
<input type=\"hidden\" name=\"mq2\" value=\"\">
<input type=\"hidden\" name=\"modif\" value=\"0\">
<input type=\"hidden\" name=\"action2\" value=\"\">
<input type=\"hidden\" name=\"artid\" value=\"$artid\">
<input type=\"hidden\" name=\"catid\" value=\"$catid\">";

$actives=array(0=>"<font color=\"red\">Invisible</font>",1=>"<font color=\"green\">Visible</font>",2=>"<font color=\"red\">Archivé</font>");

$packs=new Pack($DB_site2);
$liste=$packs->offres_pack($artid);

if(count($liste)>1)
    $contenu_onglet .= "<h3>Offres commerciales contenants cet article</h3>";
else
    $contenu_onglet .= "<h3>Offre commerciale contenants cet article</h3>";

$newref=$packs->newoffre($artid);
$contenu_onglet.= <<<HTML
<p><a href="javascript:shownewoffre()"><img src="images/icon/edit_add_22.png" align="absmiddle"> Créer une offre avec ce produit</a></p>
<div id="newoffre" style="display:none;">
    Référence : <input type="text" id="artcode" value="$newref"> <input type="button" value="OK" onclick="createnewoffre()">
</div>
<script language="javascript">
var newref="$newref";
function shownewoffre(){
    document.getElementById('newoffre').style.display='block';
}
function createnewoffre(){
    document.getElementsByName('artcode')[0].value=document.getElementById('artcode').value;
    document.forms.newoffreform.submit();
    document.getElementById('newoffre').style.display='none';
    var refdata=newref.match("OFR-([A-Z0-9]+)-([0-9]+)");
    var artcodenum=parseInt(refdata[2]);
    newref="OFR-"+refdata[1]+"-"+(artcodenum+1);
    document.getElementById('artcode').value=newref;
}
</script>
HTML;

if(count($liste)>0){
    $tr="";
    foreach($liste as $packid){
        $pack=$packs->getDetails($packid);
        $articles=$packs->getProduits($packid);
        $photos="";
        foreach($articles as $article){
            $photos.="<img src=\"../configurations/".$host."/images/produits/".$article['artid'].".jpg\" height=\"80\">";
        }

        $tr.="<tr>
        <td>".$photos."</td>
        <td><i>".$pack['artcode']." (".$pack['artcode_eurocave'].")</i> :<br>".$pack['libelleFR']." ".$actives[$pack['active']]."</td>
        <td><a href=\"ajout_offre.php?artid=".$pack['artid']."\" target=\"_blank\"><img src=\"images/editer.gif\"></a></td>
        </tr>"; 
    }

    $contenu_onglet.="<table class=\"table_coupscoeur\">".$tr."</table>";
}else
    $contenu_onglet.="<p>Ce produit n'est dans aucune offre</p>";

$contenu_onglet.="</form>
<form name=\"newoffreform\" method=\"post\" target=\"_blank\" action=\"ajout_offre.php\">
<input type=\"hidden\" name=\"artcode\">
<input type=\"hidden\" name=\"addartid\" value=\"$artid\">
<input type=\"hidden\" name=\"old\" value=\"infos\">
<input type=\"hidden\" name=\"action\" value=\"infos\">
</form>";

?>