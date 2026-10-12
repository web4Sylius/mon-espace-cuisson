<?php

ini_set("display_errors",1);

require "admin_global.php";
include "./header.php";
include "./menu.php";

include($rootpath."class/marque_client.php");

$msg="";
if(isset($_POST['mcid'])){
    $mc=new marque_client($DB_site2,$_POST['mcid']);
    $mc->nom=$_POST['nom'];
    if($mc->update()){
        $mc->upload_img("logo");
        $msg="Le client a été ajouté";
    }
}

if(isset($_GET['move'])){
    $mc=new marque_client($DB_site2,$_GET['move']);
    $mc->move($_GET['sens']);
}

$action=isset($_GET['action'])?$_GET['action']:"";

echo "<h1>Marques clients</h1>";

$editid=0;
$editnom="";
$logo="";
switch($action){
    case "editer":
    $mc=new marque_client($DB_site2,$_GET['id']);
    $editid=$mc->id;
    $editnom=$mc->nom;
    $logo="<img src=\"../".$mc->image."\"><br><br>Changer :";

    case "ajouter":
    echo $msg."
	<form method=\"post\" enctype=\"multipart/form-data\">
	<input type=\"hidden\" name=\"mcid\" value=\"$editid\">
	<table>
		<tr>
			<td>Nom : </td>
			<td><input type=\"text\" name=\"nom\" value=\"$editnom\"></td>
		</tr>
		<tr>
			<td>Logo : </td>
			<td>$logo<input type=\"file\" name=\"logo\"></td>
		</tr>	
	</table>
	<bR><input type=\"submit\" value=\"Valider\">
	<br></form><br><a href=\"marque_client.php\">Retour</a>";
    break;
    
    default:
    echo "<br><br><table class=\"table_moyen_paiement\">
        <tr>
                <td class=\"td_head_comparateurs\"><strong>Position</strong></td>
                <td class=\"td_head_comparateurs\" colspan=\"2\"><strong>Nom</strong></td>
                <td class=\"td_head_comparateurs\"><strong>ACTION</strong></td>
        </tr>" ;
        
    $mc=new marque_client($DB_site2);
    $liste=$mc->getlist();
    $i=0;

    foreach($liste as $client){
        $btnposition="";
        if($i>0) $btnposition.="<a href=\"marque_client.php?move=".$client->id."&sens=up\"><img src=\"images/monter.gif\" alt=\"Monter\" title=\"Monter\" height=\"7\" border=\"0\"></a>";
        if($i<count($liste)-1) $btnposition.="<a href=\"marque_client.php?move=".$client->id."&sens=down\"><img src=\"images/descendre.gif\" alt=\"Descendre\" title=\"Descendre\" height=\"7\" border=\"0\"></a>";
    
        $logo="<img src=\"../".$client->image."\" style=\"max-height:50px;\">";
    
        print <<<HTML
        <tr>
            <td align="center">$btnposition</td>
            <td>$logo</td>
            <td>$client->nom</td>
            <td align="center"><a href="marque_client.php?action=editer&id=$client->id"><img src="images/editer.gif"></a></td>
        </tr>
HTML;
        $i++;
    }

    print "</table><br>";

    print "<a href=\"marque_client.php?action=ajouter\">Ajouter un client</a>";
}

include "./footer.php";

?>