<?php

$article=$DB_site2->query_first("select a.*, ac.* from article AS a INNER JOIN article_capacite AS ac USING(artid) where artid = '$artid'");

    
    $info_lot=array("capacite"=>array(),"capacite_mini"=>array(),"capacite_maxi"=>array());
    
    for($i=1;$i<6;$i++){
        $info_capacite{$i}=$info_capacite_maxi{$i}=$info_capacite_mini{$i}="aa";
        $var = "info_capacite".$i;
        $var2 = "info_capacite_maxi".$i;
        $var3 = "info_capacite_mini".$i;
        $req=$DB_site2->query("SELECT ac.capacite_valeur$i,ac.capacite_unite$i,ac.capacite_maxi$i,ac.capacite_mini$i,qte FROM article INNER JOIN article_capacite AS ac USING(artid) INNER JOIN article_lot USING(artid) WHERE lotid='$artid'");
        while($art_lot=$DB_site2->fetch_array($req)){
            $info_lot["capacite".$i][$art_lot['capacite_unite'.$i]]+=($art_lot['qte'.$i]*$art_lot['capacite_valeur'.$i]);
            $info_lot["capacite_mini".$i][$art_lot['capacite_unite'.$i]]+=($art_lot['qte'.$i]*$art_lot['capacite_mini'.$i]);
            $info_lot["capacite_maxi".$i][$art_lot['capacite_unite'.$i]]+=($art_lot['qte'.$i]*$art_lot['capacite_maxi'.$i]);
        }
        if(is_array($info_lot["capacite".$i])) foreach($info_lot["capacite".$i] as $unite=>$capacite) if($unite!="") ${$var}.=$capacite." ".$template->translate($unite)." ";
        if(is_array($info_lot["capacite_maxi".$i])) foreach($info_lot["capacite_maxi".$i] as $unite=>$capacite) if($unite!="") ${$var2}.=$capacite." ".$template->translate($unite)." ";
        if(is_array($info_lot["capacite_mini".$i])) foreach($info_lot["capacite_mini".$i] as $unite=>$capacite) if($unite!="") ${$var3}.=$capacite." ".$template->translate($unite)." ";

    }
    
    $contenu_onglet .= "
    <form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
    <input type=\"hidden\" name=\"old\" value=\"\">
    <input type=\"hidden\" name=\"quitter\" value=\"\">
    <input type=\"hidden\" name=\"mq2\" value=\"\">
    <input type=\"hidden\" name=\"action2\" value=\"\">
    <input type=\"hidden\" name=\"modif\" value=\"0\">
    <input type=\"hidden\" name=\"artid\" value=\"$artid\">
    <input type=\"hidden\" name=\"catid\" value=\"$catid\">
    <input type=\"hidden\" name=\"delref\" value=\"\">
    <h3>Capacités 1</h3>
    <table width=\"90%\" align=\"center\">
    <tr>
    <td>Unité capacité</td>
    <td>".selecttranslate("capacite_unite1","/article/unite",$article['capacite_unite1'])."</td>
    </tr>
    <tr>
    <td align=\"left\">Capacité du modèle : </td>
    <td align=\"left\">&nbsp;<input type=\"text\" name=\"capacite_valeur1\" value=\"$article[capacite_valeur1]\" size=\"45\" onchange=\"setmodif();\"> ".$info_capacite1."</td>
    </tr>                                                                                                                       
    <tr>
    <td align=\"left\">Capacité du modèle mini : </td>
    <td align=\"left\">&nbsp;<input type=\"text\" name=\"capacite_mini1\" value=\"$article[capacite_mini1]\" size=\"45\" onchange=\"setmodif();\"> ".$info_capacite_mini1."</td>
    </tr>                                                                                                                       
    <tr>
    <td align=\"left\">Capacité du modèle maxi : </td>
    <td align=\"left\">&nbsp;<input type=\"text\" name=\"capacite_maxi1\" value=\"$article[capacite_maxi1]\" size=\"45\" onchange=\"setmodif();\"> ".$info_capacite_maxi1."</td>
    </tr>
    </table>

    <h3>Capacités 2</h3>
    <table width=\"90%\" align=\"center\">
    <tr>
    <td>Unité capacité</td>
    <td>".selecttranslate("capacite_unite2","/article/unite",$article['capacite_unite2'])."</td>
    </tr>
    <tr>
    <td align=\"left\">Capacité du modèle : </td>
    <td align=\"left\">&nbsp;<input type=\"text\" name=\"capacite_valeur2\" value=\"$article[capacite_valeur2]\" size=\"45\" onchange=\"setmodif();\"> ".$info_capacite2."</td>
    </tr>                                                                                                                       
    <tr>
    <td align=\"left\">Capacité du modèle mini : </td>
    <td align=\"left\">&nbsp;<input type=\"text\" name=\"capacite_mini2\" value=\"$article[capacite_mini2]\" size=\"45\" onchange=\"setmodif();\"> ".$info_capacite_mini2."</td>
    </tr>                                                                                                                       
    <tr>
    <td align=\"left\">Capacité du modèle maxi : </td>
    <td align=\"left\">&nbsp;<input type=\"text\" name=\"capacite_maxi2\" value=\"$article[capacite_maxi2]\" size=\"45\" onchange=\"setmodif();\"> ".$info_capacite_maxi2."</td>
    </tr>
    </table>

    <h3>Capacités 3</h3>
    <table width=\"90%\" align=\"center\">
    <tr>
    <td>Unité capacité</td>
    <td>".selecttranslate("capacite_unite3","/article/unite",$article['capacite_unite3'])."</td>
    </tr>
    <tr>
    <td align=\"left\">Capacité du modèle : </td>
    <td align=\"left\">&nbsp;<input type=\"text\" name=\"capacite_valeur3\" value=\"$article[capacite_valeur3]\" size=\"45\" onchange=\"setmodif();\"> ".$info_capacite3."</td>
    </tr>                                                                                                                       
    <tr>
    <td align=\"left\">Capacité du modèle mini : </td>
    <td align=\"left\">&nbsp;<input type=\"text\" name=\"capacite_mini3\" value=\"$article[capacite_mini3]\" size=\"45\" onchange=\"setmodif();\"> ".$info_capacite_mini3."</td>
    </tr>                                                                                                                       
    <tr>
    <td align=\"left\">Capacité du modèle maxi : </td>
    <td align=\"left\">&nbsp;<input type=\"text\" name=\"capacite_maxi3\" value=\"$article[capacite_maxi3]\" size=\"45\" onchange=\"setmodif();\"> ".$info_capacite_maxi3."</td>
    </tr>
    </table>

    <h3>Capacités 4</h3>
    <table width=\"90%\" align=\"center\">
    <tr>
    <td>Unité capacité</td>
    <td>".selecttranslate("capacite_unite4","/article/unite",$article['capacite_unite4'])."</td>
    </tr>
    <tr>
    <td align=\"left\">Capacité du modèle : </td>
    <td align=\"left\">&nbsp;<input type=\"text\" name=\"capacite_valeur4\" value=\"$article[capacite_valeur4]\" size=\"45\" onchange=\"setmodif();\"> ".$info_capacite4."</td>
    </tr>                                                                                                                       
    <tr>
    <td align=\"left\">Capacité du modèle mini : </td>
    <td align=\"left\">&nbsp;<input type=\"text\" name=\"capacite_mini4\" value=\"$article[capacite_mini4]\" size=\"45\" onchange=\"setmodif();\"> ".$info_capacite_mini4."</td>
    </tr>                                                                                                                       
    <tr>
    <td align=\"left\">Capacité du modèle maxi : </td>
    <td align=\"left\">&nbsp;<input type=\"text\" name=\"capacite_maxi4\" value=\"$article[capacite_maxi4]\" size=\"45\" onchange=\"setmodif();\"> ".$info_capacite_maxi4."</td>
    </tr>
    </table>

    <h3>Capacités 5</h3>
    <table width=\"90%\" align=\"center\">
    <tr>
    <td>Unité capacité</td>
    <td>".selecttranslate("capacite_unite5","/article/unite",$article['capacite_unite5'])."</td>
    </tr>
    <tr>
    <td align=\"left\">Capacité du modèle : </td>
    <td align=\"left\">&nbsp;<input type=\"text\" name=\"capacite_valeur5\" value=\"$article[capacite_valeur5]\" size=\"45\" onchange=\"setmodif();\"> ".$info_capacite5."</td>
    </tr>                                                                                                                       
    <tr>
    <td align=\"left\">Capacité du modèle mini : </td>
    <td align=\"left\">&nbsp;<input type=\"text\" name=\"capacite_mini5\" value=\"$article[capacite_mini5]\" size=\"45\" onchange=\"setmodif();\"> ".$info_capacite_mini5."</td>
    </tr>                                                                                                                       
    <tr>
    <td align=\"left\">Capacité du modèle maxi : </td>
    <td align=\"left\">&nbsp;<input type=\"text\" name=\"capacite_maxi5\" value=\"$article[capacite_maxi5]\" size=\"45\" onchange=\"setmodif();\"> ".$info_capacite_maxi5."</td>
    </tr>
    </table>
    </form>";

?>