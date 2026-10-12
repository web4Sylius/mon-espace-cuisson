<?php
require_once("admin_global.php");

$logpath="/comparateur/log/delamaison";
$tab_fichier = scandir(BACK_PATH.$logpath);
if (!empty($tab_fichier))
{
    foreach ($tab_fichier as $valeur)
    {
        if(preg_match("/([^\d]+)(\d+)\.(.+)/",$valeur,$m)){
            $files[$m[2]][$m[1]]=$valeur;
        }
    }
}

krsort($files);


print <<<HTML

<div>
<p>Mise à jour des produits automatique du lundi au vendredi à 10h00</p>
<p>Mise à jour des offres automatique avec 2 fichiers (courantes et nouvelles) du lundi au vendredi à 17h30</p>
<p>Produit désactivé si délais livraison > 8j ou date réappro > 7j</p>
<p>Prix produit sans frais de port ni prix temporaire</p>
</div>

<br>
<table style="border-collapse: collapse;">
    <tr>
        <th>Date</th>
        <th>Type</th>
        <th>Fichiers Exporté</th>
    </tr>
HTML;

foreach($files as $ts=>$exfiles){
    $date=date("d/m/Y H:i",$ts);
    foreach($exfiles as $name=>$file){
        print <<<HTML
        <tr>
            <td class="ligne">$date</td>
            <td class="ligne">$name</td>
            <td class="ligne">
                <form method="post" style="margin: 0px;" align="center" type="button" action="/admin/includes/market_place/download.php">
                    <input type="submit" value=" " class="dlbutton"/>
                    <input type="hidden" name="page" value="$logpath/$file"/>
                </form>
            </td>
        </tr>
HTML;
    }
}

print "</table>";