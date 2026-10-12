<?php

$article=$DB_site2->query_first("select a.*, spec.* from article AS a INNER JOIN ".$table_type[0]." AS spec USING(artid)  where artid = '$artid'");
    $checkedfab0=$checkedfab1=$checkedfab2=$checkedfab3="";
    ${"checkedfab".$article[paysfabrication]}="selected";

    $paysidfabopt="<option value=\"0\"></option>";
    $reqpays=$DB_site2->query("SELECT libelle, paysid FROM pays ORDER BY libelle");
    while($p=$DB_site2->fetch_array($reqpays)){
          $paysidfabopt.="<option value=\"$p[paysid]\"".($p['paysid']==$article['paysid']?" selected":"").">$p[libelle]</option>";
    }

    $reqcb=$DB_site2->query("SELECT * FROM code_barre left join `caracteristiquevaleur` using(caractvalid) WHERE artid=".$artid);
    while($cb=$DB_site2->fetch_array($reqcb)){
          $codebarre.=$cb['codebarre']." ".($cb['libelleFR']!=null?$cb['libelleFR']:"")."<br>";
    }
    
    $contenu_onglet .= "
    <h3>Origine de fabrication - codes</h3>
    <form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
    <input type=\"hidden\" name=\"old\" value=\"\">
    <input type=\"hidden\" name=\"quitter\" value=\"\">
    <input type=\"hidden\" name=\"mq2\" value=\"\">
    <input type=\"hidden\" name=\"action2\" value=\"\">
    <input type=\"hidden\" name=\"modif\" value=\"0\">
    <input type=\"hidden\" name=\"artid\" value=\"$artid\">
    <input type=\"hidden\" name=\"catid\" value=\"$catid\">
    <input type=\"hidden\" name=\"delref\" value=\"\">
    <table width=\"90%\" align=\"center\">
    <tr>
    <td align=\"left\">Origine fabrication : </td>
    <td align=\"left\"><select name=\"paysfabrication\" onchange=\"setmodif();\"><option value=\"0\" $checkedfab0>France</option><option value=\"1\" $checkedfab1>Europe</option><option value=\"2\" $checkedfab2>Asie</option><option value=\"3\" $checkedfab3>US</option></select></td>
    </tr>
    <tr>
    <td>Pays fabrication (pour export) :</td>
    <td><select name=\"paysidfab\" onchange=\"setmodif();\">$paysidfabopt</select></td>
    </tr>
    <tr>
    <td>Code douanier :</td>
    <td><input type=\"text\" name=\"douanier\" value=\"$article[douanier]\" onchange=\"setmodif();\"></td>
    </tr>
    <tr>
    <td>Code barre fabricant :</td>
    <td><input type=\"text\" name=\"codebarre\" value=\"$article[codebarre]\" onchange=\"setmodif();\"></td>
    </tr>
    <tr>
    <td><br>Code(s) barres Acilyon :</td>
    <td><br>$codebarre</td>
    </tr>
    </table>
    </form>";


?>
