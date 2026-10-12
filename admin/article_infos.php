<?php

$liste_marques = "<option value=\"0\"></option>" ;
$marques=$DB_site2->query("SELECT * from marque order by libelle");
while ($marque=$DB_site2->fetch_array($marques)) 
    {
    if (isset($mq2) and $mq2 != 0)
        {
        if ($marque[marqueid] == $mq2)
            {
            $liste_marques .= "<option value=\"$marque[marqueid]\" selected>$marque[libelle]</option>" ;
            }
        else
            {
            $liste_marques .= "<option value=\"$marque[marqueid]\">$marque[libelle]</option>" ;
            }
        }
    else
        {
        if ($marque[marqueid] == $mq)
            {
            $liste_marques .= "<option value=\"$marque[marqueid]\" selected>$marque[libelle]</option>" ;
            }
        else
            {
            $liste_marques .= "<option value=\"$marque[marqueid]\">$marque[libelle]</option>" ;
            }
        }
    }
$frais=$DB_site2->query(" select fraisgestionid, $libelle from fraisgestion");
$options="<select name=\"fraisgestion\"><option value=\"0\">Aucun</option>";
while ($frai=$DB_site2->fetch_array($frais)){
    if ($fraisgestion==$frai[fraisgestionid]) {
        $selected="selected";
    } else {
        $selected="";
    }
    $options.="<option value=\"$frai[fraisgestionid]\" $selected>$frai[$libelle]</option>";
}
if ($checkedv1 == "" and $checkedv0 == "")
    {
    $checkedv1 = "checked" ;
    }
if ($checkedcd1.$checkedcd0.$checkedcd2 == "")
    {
    $checkedcd1 = "checked" ;
    }
$options.="</select>";

$liste_typearticle = "<option value=\"0\"></option>" ;
$typearticles=$DB_site2->query("SELECT * from typearticle order by libelle");
while ($ta=$DB_site2->fetch_array($typearticles))
  $liste_typearticle .= "<option value=\"$ta[typearticleid]\" ".(($ta['typearticleid'] == $article['typearticleid'])?"selected":"").">$ta[libelle]</option>" ;


if($artid!=""){
    $artcodes=$DB_site2->query("SELECT artcode,active,artid,c.libelleFR FROM article INNER JOIN categorie c USING(catid) WHERE artcode='".mysqli_real_escape_string($DB_site2->link_id,$artcode)."' AND artid<>".$artid);
    $txtartcodes="";
    if($DB_site2->num_rows($artcodes)>0){
    $txtartcodes="<div style=\"color:red;\">Référence déjà existante :";
    while($aartcode=$DB_site2->fetch_array($artcodes)) $txtartcodes.="<br><a href=\"ajout_produit.php?artid=".$aartcode['artid']."&prec=modif\" target=\"_blank\">".$aartcode['artcode']." ".$aartcode['libelleFR']." (".$active[$aartcode['active']].")</a> ";
    $txtartcodes.="</div>";
    }
    $artcodes=$DB_site2->query("SELECT artcode,active,artid,c.libelleFR FROM article INNER JOIN categorie c USING(catid) WHERE artcode_eurocave='".mysqli_real_escape_string($DB_site2->link_id,$artcode_eurocave)."' AND artid<>".$artid);
    $txtartcodeseur="";
    if($DB_site2->num_rows($artcodes)>0){
    $txtartcodeseur="<div style=\"color:red;\">Référence déjà existante :";
    while($aartcode=$DB_site2->fetch_array($artcodes)) $txtartcodeseur.="<br><a href=\"ajout_produit.php?artid=".$aartcode['artid']."&prec=modif\" target=\"_blank\">".$aartcode['artcode']." ".$aartcode['libelleFR']." (".$active[$aartcode['active']].")</a> ";
    $txtartcodeseur.="</div>";
    }
}
$select_marque = "<select name=\"mq\" onchange=\"setmodif();\">$liste_marques</select>" ;
$select_typearticle = "<select name=\"typearticle\" onchange=\"setmodif();clique('$action2','$action2');\" style=\"float:left;\">$liste_typearticle</select>" ;
$contenu_onglet .= "
<h3>Informations produit</h3>
<font class=\"cost\">$erreur</font>
<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
<input type=\"hidden\" name=\"old\" value=\"\">
<input type=\"hidden\" name=\"quitter\" value=\"\">
<input type=\"hidden\" name=\"mq2\" value=\"\">
<input type=\"hidden\" name=\"modif\" value=\"0\">
<input type=\"hidden\" name=\"action2\" value=\"\">
<input type=\"hidden\" name=\"artid\" value=\"$artid\">
<input type=\"hidden\" name=\"catid\" value=\"$catid\">
<table width=\"90%\" align=\"center\">
<tr>
<td align=\"left\">Artid : </td>
<td align=\"left\">".$article['artid']."</td>
</tr>
<tr>
<td align=\"left\">Référence : </td>
<td align=\"left\">&nbsp;<input type=\"text\" name=\"artcode\" value=\"$artcode\" onchange=\"setmodif();\">$txtartcodes</td>
</tr>
<tr>
<td align=\"left\">Rèf. constructeur : </td>
<td align=\"left\">&nbsp;<input type=\"text\" name=\"artcode_eurocave\" value=\"$artcode_eurocave\" onchange=\"setmodif();\" size=\"55\">$txtartcodeseur</td>
</tr>
<tr>
<td>Références constructeur<br>(une par ligne)<br><small>utilisé pour besoin d'accessoires et recherche rapide</small></td>
<td><textarea name=\"refscons\" cols=\"30\" rows=\"6\" onchange=\"setmodif();\">$refscons</textarea></td>
</tr>
<tr>
<td align=\"left\">Second-choix : </td>
<td><input type=\"checkbox\" name=\"secondchoix\" ".($article['secondchoix']==1?'checked':'')."> Second-choix</td>
</tr>
<tr>
<td align=\"left\">Marque : </td>
<td align=\"left\">$select_marque &nbsp;&nbsp;&nbsp; <a href=\"Javascript: resize() ;\">Nouvelle</a></td>
</tr>
<tr>
<td align=\"left\">Gamme FR : </td>
<td align=\"left\">&nbsp;<input type=\"text\" name=\"gammeFR\" value=\"".$article['gammeFR']."\" size=\"55\" onchange=\"setmodif();\"></td>
</tr>
<!--<tr>
<td align=\"left\">Gamme EN : </td>
<td align=\"left\">&nbsp;<input type=\"text\" name=\"gammeEN\" value=\"".$article['gammeEN']."\" size=\"55\" onchange=\"setmodif();\"></td>
</tr>-->
<!--<tr>
<td align=\"left\">Gamme SP : </td>
<td align=\"left\">&nbsp;<input type=\"text\" name=\"gammeSP\" value=\"".$article['gammeSP']."\" size=\"55\" onchange=\"setmodif();\"></td>
</tr>-->
<!--<tr>
<td align=\"left\">Rèf. recherche rapide : </td>
<td align=\"left\">&nbsp;<textarea name=\"artcode_fabricant\" cols=\"60\" rows=\"2\" onchange=\"setmodif();\" onkeypress=\"nolf(event)\">$artcode_fabricant</textarea></td>
</tr>-->
<tr><td><br></td></tr>" ;
if(in_array("FR" , $langues))
    {
    $content_avec_dieses = preg_replace('#diese#', '#', $descriptionFR,-1) ;
    $contenu_onglet .= "<tr>
    <td align=\"left\">Libellé : </td>
    <td align=\"left\">&nbsp;<textarea name=\"libelleFR\" cols=\"60\" rows=\"2\" onchange=\"setmodif();\" onkeypress=\"nolf(event)\">$libelleFR</textarea></td>
    </tr>";
    }
if(in_array("DE" , $langues))
    {
    $contenu_onglet .= "<tr>
    <td align=\"left\">Libellé DE : </td>
    <td align=\"left\">&nbsp;<textarea name=\"libelleDE\" cols=\"60\" rows=\"2\" onchange=\"setmodif();\" onkeypress=\"nolf(event)\">$libelleDE</textarea></td>
    </tr>";
    }
/*if(in_array("EN" , $langues))
    {
    $contenu_onglet .= "<tr>
    <td align=\"left\">Libellé EN : </td>
    <td align=\"left\">&nbsp;<textarea name=\"libelleEN\" cols=\"60\" rows=\"2\" onchange=\"setmodif();\" onkeypress=\"nolf(event)\">$libelleEN</textarea></td>
    </tr>";
    }*/
if(in_array("IT" , $langues))
    {
    $contenu_onglet .= "<tr>
    <td align=\"left\">Libellé IT : </td>
    <td align=\"left\">&nbsp;<textarea name=\"libelleIT\" cols=\"60\" rows=\"2\" onchange=\"setmodif();\" onkeypress=\"nolf(event)\">$libelleIT</textarea></td>
    </tr>";
    }


$autresarticles=$DB_site2->query("SELECT artid,artcode,libelleFR,catid FROM article WHERE artcode='$article[artcode]' AND artid != '$artid' ");
while ($autrearticle=$DB_site2->fetch_array($autresarticles)){
    $categ=$DB_site2->query_first("SELECT libelleFR FROM categorie WHERE catid='$autrearticle[catid]' ");
    $contenu_onglet .= "<tr><td colspan=\"2\">($categ[libelleFR]) <a href=\"ajout_produit.php?artid=$autrearticle[artid]&prec=modif\" target=\"_blank\">$autrearticle[libelleFR]</a></td></tr>";
}

$contenu_onglet .= "
<tr>
<td align=\"left\">
  Type d'article :
</td>
<td align=\"left\" valign=\"middle\">
  $select_typearticle
</td>
</tr>";


$optpagegamme="";

$contenu_onglet.="
<tr><td>Finition :</td><td><table>";
$emplacements=$DB_site2->query("SELECT * FROM recherche_finition");
while ($emplacement=$DB_site2->fetch_array($emplacements)){
      $countemplacement=$DB_site2->query_first("SELECT count(*) as nb FROM articlerecherche_finition WHERE artid='$artid' AND finitionid='$emplacement[finitionid]' ");
      if ($countemplacement[nb]==1)
          ${"finitionchecked".$emplacement[finitionid]}="checked";
}
$listefinition=$DB_site2->query("SELECT * FROM recherche_finition");
while($finition=$DB_site2->fetch_array($listefinition))
    $contenu_onglet.="<tr>
        <td><input type=\"checkbox\" name=\"finition$finition[finitionid]\" ".${'finitionchecked'.$finition[finitionid]}."></td>
        <td>$finition[nom]</td>
    </tr>";        

$contenu_onglet .= "</table></td></tr></table>
$appel_fonction" ;



?>
