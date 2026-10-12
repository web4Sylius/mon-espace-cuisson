<?php

include_once("../class/pack.class.php");

$contenu_onglet.= "<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
<input type=\"hidden\" name=\"old\" value=\"\">
<input type=\"hidden\" name=\"quitter\" value=\"\">
<input type=\"hidden\" name=\"mq2\" value=\"\">
<input type=\"hidden\" name=\"modif\" value=\"0\">
<input type=\"hidden\" name=\"action2\" value=\"\">
<input type=\"hidden\" name=\"artid\" value=\"$artid\">
<input type=\"hidden\" name=\"catid\" value=\"$catid\">

<h3>Pack contenants cet article</h3>";

$actives=array(0=>"<font color=\"red\">Invisible</font>",1=>"<font color=\"green\">Visible</font>",2=>"<font color=\"red\">Archivé</font>");

$packs=new Pack($DB_site2);
$liste=$packs->contains($artid);
$offres=$packs->offres_pack($artid);

$newref=$packs->newref($artid);
$contenu_onglet.= <<<HTML
<p><a href="javascript:shownewpack()"><img src="images/icon/edit_add_22.png" align="absmiddle"> Créer un pack avec ce produit</a></p>
<div id="newpack" style="display:none;">
    Référence : <input type="text" id="artcode" value="$newref"> <input type="button" value="OK" onclick="createnewpack()">
</div>
<script language="javascript">
var newref="$newref";
function shownewpack(){
    document.getElementById('newpack').style.display='block';
}
function createnewpack(){
    document.getElementsByName('artcode')[0].value=document.getElementById('artcode').value;
    document.forms.newpackform.submit();
    document.getElementById('newpack').style.display='none';
    var refdata=newref.match("PAK-([A-Z0-9]+)-([0-9]+)");
    var artcodenum=parseInt(refdata[2]);
    newref="PAK-"+refdata[1]+"-"+(artcodenum+1);
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
            $photos.="<img src=\"../configurations/".$host."/images/produits/br/".$article['artid'].".jpg\" height=\"80\">";
        }
        if(!in_array($packid,$offres)){ // On ne prends pas en compte les offres commerciales
            $tr.="<tr>
            <td>".$photos."</td>
            <td><i>".$pack['artcode']." (".$pack['artcode_eurocave'].")</i> :<br>".$pack['libelleFR']." ".$actives[$pack['active']]."</td>
            <td><a href=\"ajout_pack.php?artid=".$pack['artid']."\" target=\"_blank\"><img src=\"images/editer.gif\"></a></td>
            </tr>"; 
        }    
    }

    $contenu_onglet.="<table class=\"table_coupscoeur\">".$tr."</table>";
}else
    $contenu_onglet.="<p>Ce produit n'est dans aucun pack</p>";

$contenu_onglet.="</form>
<form name=\"newpackform\" method=\"post\" target=\"_blank\" action=\"ajout_pack.php\">
<input type=\"hidden\" name=\"artcode\">
<input type=\"hidden\" name=\"addartid\" value=\"$artid\">
<input type=\"hidden\" name=\"old\" value=\"infos\">
<input type=\"hidden\" name=\"action\" value=\"infos\">
</form>";

?>