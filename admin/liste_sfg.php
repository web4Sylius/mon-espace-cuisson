<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

if(isset($_GET['send']))
    $DB_site2->query("UPDATE sfg SET customer_sent=".mktime()." WHERE facturid=".$_GET['send']);


$expdate=time()-86400;

echo "<h1>Gestion SFG</h1>
<form method=\"post\">
    Rechercher une commande : <input type=\"text\" name=\"src\" value=\"".(isset($_POST['src'])?$_POST['src']:"")."\"> <input type=\"submit\" value=\"OK\">
</form>
<a href=\"liste_sfg.php?errors=1\">Afficher les erreurs</a> <a href=\"liste_sfg.php\">Tout afficher</a><br>&nbsp;
    <table cellspacing=\"0\" class=\"table_factures\">
    <tr>
        <td class=\"td_head_factures\" width=\"120\">Date Facture</td>
        <td class=\"td_head_factures\" width=\"80\">N°</td>
        <td class=\"td_head_factures\">Client</td>
        <td class=\"td_head_factures\">Pris en compte</td>
    </tr>";

$sql="SELECT f.facturid, UNIX_TIMESTAMP(f.dateexpedition) AS datefacture,f.valide,u.raisonsociale, u.nom, u.prenom,sfg.receive,sfg.customer_sent,sfg.annule, sfg.couverture FROM facture f INNER JOIN lignefacture l ON(l.factureid=f.facturid) INNER JOIN lignefacturegarantie lg USING(lignefactureid) INNER JOIN utilisateur u USING(userid) LEFT JOIN sfg ON(sfg.facturid=f.facturid) WHERE f.valide IN (1,5,7,8) AND lg.fournisseur='SFG'";
if(isset($_POST['src'])) $sql.=" AND f.facturid='".$_POST['src']."'";
if(isset($_GET['errors'])) $sql.=" AND ISNULL(receive) AND f.valide IN (5,8)";
$sql.=" GROUP BY facturid ORDER BY datefacture DESC";

$alt=1;
$requete=$DB_site2->query($sql);
while($f=$DB_site2->fetch_array($requete)){
    $pdftd=$senttd="";
    $adv=$DB_site2->query_first("SELECT * FROM adv WHERE facturid=".$f['facturid']." AND type='garantie'");
    //$env=$DB_site2->query_first("SELECT * FROM envoi_fournisseur WHERE type='garantie'");
    
    if($adv==""){
        $pdftd="Non programmé";
        $senttd="<a href=\"facture.php?facturid=$f[facturid]&action=editer#adv\">Programmer</a>";
    }else{
        $ef=$DB_site2->query_first("SELECT * FROM envoi_fournisseur WHERE destinataire='SFG' AND facturid=".$f['facturid']." ORDER BY id DESC LIMIT 1");
        if($ef!==false){
            if($f['receive']===NULL){
                // Commande pas Expédié
                if($f['datefacture'] == "" || $f['datefacture'] == 0){
                    $pdftd="<span style=\"color:red;\">En attente</span>";
                    $senttd="<span style=\"color:#FFAA00;\">Commande pas Expédié</span>";
                // Erreur
                }elseif($ef['date']<$expdate-(5*86400)){
                    $pdftd="<span style=\"color:red;\">Erreur</span>";
                    $pdftd.="<br><span style=\"color:#FFAA00;\">Envoyé depuis le ".date("d/m/Y",$ef['date'])."</span>";
                $senttd.="<br><a href=\"sfg_retard.php?facturid=".$f['facturid']."\" target=\"_blank\">Envoi manuel</a>";
               // Commandé
                }elseif($ef['facturid']==$f['facturid']){
                    $pdftd="<span style=\"color:#FFAA00;\">Commandé le ".date("d/m/Y à H:i",$ef['date'])."</span>";
                }
            }elseif (intval($f['couverture']) == 1) {
                $pdftd="<span style=\"color:green;\">Non couvert</span>";
            }elseif($f['datefacture']<$expdate ){
                $pdftd="<a href=\"sfg_pdf/$f[facturid].pdf\" style=\"color:green; font-weight:bold;\">OK</a>";
                if($f['annule']>0) 
                    $pdftd="<a href=\"sfg_pdf/$f[facturid]_annule.pdf\" style=\"color:green;\">Annulé</a>";
            }
        }else{
            $pdftd="<span style=\"color:red;\">Non commandé</span>";
            if(in_array($f['valide'],array(5,8)))
                $senttd="<a href=\"sfg_retard.php?facturid=".$f['facturid']."\" target=\"_blank\">Envoi de la garantie</a>";
        }
    }
    
    $alt=$alt==1?2:1;
    
    echo "<tr>
        <td class=\"td_factures_alt".$alt."\">".($f['datefacture']>0?date("d/m/Y",$f['datefacture']):"Non facturé")."</td>
        <td class=\"td_factures_alt".$alt."\"><a href=\"facture.php?facturid=$f[facturid]&action=editer\" target=\"_blank\">$f[facturid]</a></td>
        <td class=\"td_factures_alt".$alt."\">$f[raisonsociale] $f[nom] $f[prenom]</td>
        <td class=\"td_factures_alt".$alt."\" style=\"text-align:center;\">".$pdftd."</td>
        <td class=\"td_factures_alt".$alt."\" style=\"text-align:center;\">".$senttd."</td>
    </tr>";
}

echo "</table>";


include "./footer.php";

function ar($facturid){
       global $files;
       return (in_array($facturid.".pdf",$files));
}

