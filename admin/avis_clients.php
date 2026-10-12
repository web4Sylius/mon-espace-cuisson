<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "./fonctions.php";
include("facture.class.php");

$optcomm="<option value=\"\">Tous</option>";
$commercials=$DB_site2->query(" SELECT username FROM parametresgeneraux WHERE commissions ='1' ORDER BY username ASC ");
while($commercial=$DB_site2->fetch_array($commercials)){
    $optcomm.="<option value=\"".$commercial[username]."\"".(isset($_POST['commercial']) && $_POST['commercial']==$commercial[username]?" selected":"").">".UCfirst($commercial[username])."</option>";
}

if(!isset($_POST['avis'])) $_POST['avis']=0;

if(!isset($_POST['order'])){
    $_POST['order']="heurefacture";
    $_POST['sens']="DESC";
}

$dfrom=new DateTime();
if(!isset($_POST['from'])){
    $dfrom->modify("2 month ago");
}else{
    $dfrom=DateTime::createFromFormat("j/n/Y",$_POST['from']);
}
$dfrom->setTime(0,0,0);

$dto=new DateTime();
if(!isset($_POST['to'])){
    $dto->modify("2 weeks ago");
}else{
    $dto=DateTime::createFromFormat("j/n/Y",$_POST['to']);
}
$dto->setTime(0,0,0);

$srcform="<div id=\"formurecherche\" class=\"table_factures\">
                <form name=\"src\" method=\"post\">
                <input type=\"hidden\" name=\"order\" value=\"".$_POST['order']."\">
                <input type=\"hidden\" name=\"sens\" value=\"".$_POST['sens']."\">
                <select name=\"avis\">
                    <option value=\"0\"".($_POST['avis']==0?" selected":"").">A traiter</option>
                    <option value=\"1\"".($_POST['avis']==1?" selected":"").">Envoyé</option>
                    <option value=\"2\"".($_POST['avis']==2?" selected":"").">Pas d'envoi</option>
                </select>
                <select name=\"commercial\">
                    ".$optcomm."
                </select>
                Du <input type=\"text\" id=\"from\" name=\"from\" value=\"".$dfrom->format("j/n/Y")."\" onclick=\"calendrier_show(this.id,this.value)\" readonly>
                au <input type=\"text\" id=\"to\" name=\"to\" value=\"".$dto->format("j/n/Y")."\" onclick=\"calendrier_show(this.id,this.value)\" readonly>
                <input type=\"submit\" value=\"Lancer\">
                </form>
          </div>";
        
$infoavis=array(0=>"<span class=\"avis0\">A traiter</span>",
1=>"<span class=\"avis1\">Envoyé</span>",
2=>"<span class=\"avis2\">Pas d'envoi</span>");
          
echo "<h1>Avis clients</h1>";

$factures=new facture($DB_site2);

$sql="valide IN (5,8) AND dateexpedition BETWEEN '".$dfrom->format("Y-m-d")."' AND '".$dto->format("Y-m-d")."' AND provenance=''";
$sql.=" AND avis=".intval($_POST['avis']);
if(isset($_POST['commercial']) && $_POST['commercial']!="") $sql.=" AND commercial_attribution='".$_POST['commercial']."'";
$sql.=" ORDER BY ".$_POST['order']." ".$_POST['sens'];
$list=$factures->search($sql);

$d=new DateTime("2 weeks ago");
$d->setTime(0,0,0);
$avismindate=$d->getTimestamp();

$rowalt=2;
$colsort=array();
$colsort[$_POST['order']]="<i class=\"fa fa-caret-".($_POST['sens']=="ASC"?"up":"down")."\"></i> ";

print <<<HTML
<script type="text/javascript">
    function setorder(order){
        var oldorder=document.getElementsByName("order")[0].value;
        document.getElementsByName("order")[0].value=order;
        if(order!=oldorder){
            document.getElementsByName('sens')[0].value="DESC";
        }else{
            var sens=document.getElementsByName('sens')[0].value;
            document.getElementsByName('sens')[0].value=sens=="ASC"?"DESC":"ASC";
        }
        document.forms.src.submit();
    }
</script>
$srcform
<br>
<table class="table_factures">
<tr>
    <td class="td_head_factures" onclick="setorder('numerofacture');">$colsort[numerofacture]<b>N° Fac</b></td>
    <td class="td_head_factures" onclick="setorder('facturid');">$colsort[facturid]<b>N° Cde</b></td>
    <td class="td_head_factures" onclick="setorder('heurefacture');">$colsort[heurefacture]<b>Date</b></td>
    <td class="td_head_factures"><b>Montant TTC</b></td>
    <td class="td_head_factures"><b>Moyen paiement</b></td>
</tr>
HTML;

foreach($list as $facture){
    $userid=$facture->utilisateur->userid;
    $utilisateur_nom=$facture->utilisateur->nom;
    $utilisateur_ville=$facture->utilisateur->ville;
    $dep=$facture->utilisateur->paysid==57?substr($facture->utilisateur->codepostal,0,2):"";
    $datecommande=date("d/m/Y H:i",$facture->datecommande);
    $dateexp=date("d/m/Y",$facture->dateexpedition);
    $totalttc=number_format($facture->totalttc,2, ',', ' ')." ".$devises->symbole($rs_facture['devise']);
    $moyen=$facture->getMoyen($facture->moyenid);
    if($facture->dateexpedition<=$avismindate)
        $info=$infoavis[$facture->avisclient];

    $rowalt = $rowalt==1?2:1;
    print <<<HTML
        <tr class="td_factures_alt$rowalt">
            <td>$facture->numerofacture<br>$dateexp</td>
            <td>$facture->facturid<br>$facture->nom<br><a href="utilisateur.php?action=editer&amp;userid=$userid" targer="_blank">$utilisateur_nom</a><br>$dep $utilisateur_ville</td>
            <td>$datecommande</td>
            <td>$totalttc</td>
            <td>$facture->validetxt<br>$moyen</td>
            <td><a href="facture.php?action=editer&amp;facturid=$facture->facturid" target="_blank">Editer</a></td>
            <td>$info</td>
        </tr>
HTML;
}
      
print "</table>";

include "./footer.php";
?>
