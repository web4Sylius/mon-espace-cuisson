<?php

$projets=new projets();
//Récupération de tous les commerciaux actifs dans les 30 derniers jours
$sql =  "  SELECT commercial FROM utilisateur WHERE commercial <> ''";
$sql .= "  AND TO_DAYS(NOW()) - TO_DAYS(dateinscription) <= 30"; // 900 ?
$sql .= "  GROUP BY commercial ORDER BY commercial";
$req = $DB_site2->query($sql);
$optcom = "<option value=\"\">-- Tous</option>";
$optpjtcom = "<option value=\"\">-- Tous</option>";
while($result = $DB_site2->fetch_array($req)){
    $optcom .= "<option value=\"".$result[0]."\" ".($commercial == $result[0] ? "selected" : "").">".$result[0]."</option>";
	$optpjtcom .= "<option value=\"".$result[0]."\" ".($rappel_com == $result[0] ? "selected" : "").">".$result[0]."</option>";
}
$optcom .= "<option value=\"na\" ".($commercial == "na" ? "selected" : "").">-- Non attribué</option>";


$type_page = $_GET['type_page'];

$liste_etat_txt = "<option value=\"\">-- Tous</option>";
$selected="";
if($projet_etatid==implode(",",$projets->encours)){
	$selected="selected";
}
$liste_etat_txt .= "<option value=\"".implode(",",$projets->encours)."\" ".$selected.">-- En cours uniquement</option>";
$selected="";
if($projet_etatid==implode(",",$projets->clos)){
	$selected="selected";
}
$liste_etat_txt .= "<option value=\"".implode(",",$projets->clos)."\" ".$selected.">-- Clos uniquement</option>";
foreach($projets->etats as $etatid=>$libelle){
	$liste_etat_txt.="<option value=\"".$etatid."\"  ".($projet_etatid == strval($etatid) ? "selected" : "").">".$libelle."</option>";
}

$liste_type_txt = "<option value=\"\">-- Tous</option>";
foreach($projets->budget_types() as $typeid=>$libelle){
	$liste_type_txt.="<option value=\"".$typeid."\"  ".($projet_typeid!==null && $projet_typeid!=="" && intval($projet_typeid) === $typeid ? "selected" : "").">".$libelle."</option>";
}

$rappel_until="display:none;";
switch($rappel){
    case "":
	$selected_rappel_tous="selected";
    break;

    case "sans_rappel":
	$selected_sans_rappel="selected";
	break;

    case "rappels_retard":
	$selected_rappels_retard="selected";
    break;
    
    case "rappels_jour":
	$selected_rappels_jour="selected";
    $rappel_until="";
	break;

    case "rappels_futur":
	$selected_rappel_futur="selected";
	$rappel_until="";
	break;
}

$futur=array(0=>"journée",1=>"+1j",7=>"+1 sem",14=>"+2 sem",30=>"+1 mois",60=>"+2 mois",180=>"+6 mois",365=>"+1 an",10000=>"∞");
$optuntil="";
$until=isset($_GET['until']) ? intval($_GET['until']) : 0;
foreach($futur as $nbj=>$txt)
    $optuntil.="<option value=\"".$nbj."\"".($until==$nbj?" selected":"").">".$txt."</option>";

//Reccuppère la liste des pays pour les mettre dans le menu select
$optpays="<option value=''>Pays</option>";
$req=$DB_site2->query("SELECT * FROM pays ORDER BY libelle");
while($pays=$DB_site2->fetch_array($req)){
    $optpays.=sprintf("<option value=\"%u\"%s>%s</option>",$pays['paysid'],($_GET['paysid']==$pays['paysid']?" selected":""),$pays['libelle']);
}

?>
<div class="commande_detail">
    <div class="commande_detail_bis" style="padding:10px;">

        <form method="get" target="_blank" action="utilisateur.php" >
            <strong>Recherche Client :</strong><br>
            <div class="bloc_recherche">
                <input type="text" placeholder="Nom" name="nom" size="60" style="width:120px;" value="">&nbsp;
                <input type="text" placeholder="Prénom" name="prenom" size="60" style="width:120px;" value="">&nbsp;
                <input type="text" placeholder="Société / Email / Tél. / Client" name="src" size="60" style="width:165px;" value="">
                <input type="text" placeholder="N° dép. / CP" name="dpt" size="16" value="<?php echo $_GET['dpt']; ?>"style="width:100px">
                <select name="paysid"><?=$optpays; ?></select>
                <input type="submit" value="OK"><?php echo($liens)?>
            </div>
        </form>
        
<?php
//Quand on est dans les suivis externes, ne pas mettre la recherche par commande tâche SI#7716 : suivi externe
if($_GET['action'] !== 'suiviexterne' && (isset($_GET['commercial']) && $_GET['action']=='search')){
?>
        <div class="flex_rech" style="justify-content: space-between;">
            <!-- Recherche par commande -->
            <form method="post" action="facture.php">
                <input type="hidden" name="action" value="rechercher">
                <strong>Recherche commande :</strong>
                <div class="bloc_recherche" style="width:max-content;margin-right:34px;padding:8px 5px;">
                    <input type="text" placeholder="Cde / Fact / Nom / Société / Mail / N° tel" name="ref" size="70" value="" style="width:220px;margin-right:5px;">
                    <input type="checkbox" name="suppr" style="vertical-align:top;"<?=isset($suppr)?"checked":""
                    ?>> Supprimées 
                    <input type="submit" title="Rechercher" alt="Rechercher" value="OK">
                </div>
            </form>

            <div>
                <!-- Recherche par article -->
                <form method="post" action="facture.php" style="display:inline;" >
                   <input type="hidden" name="action" value="article">
                   <strong>Recherche article  :</strong>
                   <div class="bloc_recherche" style="width:max-content;margin-right:34px;padding:8px 5px;">
                       <input type="text" placeholder="Code article" name="ref" value="<?= $action=="article"?$ref:"";?>" style=" width:140px;margin-right:5px;"><input type="checkbox" name="valid" style="vertical-align:top;"
                       <?= isset($valid)?" checked":"";?>> Cde validées 
                       <input type="submit" value="OK"><?php echo($liens)?>
                   </div>
               </form>
           </div>

            <div>
                <!-- Recherche par reference transporteur -->
                <form method="get" style="display:inline;" action="facture.php" >
                   <input type="hidden" name="action" value="trspt">
                   <strong>N° transporteur :</strong>
                   <div class="bloc_recherche" style="width:max-content;padding:8px 5px;">
                        <input type="text" placeholder="Réf trspt / inst" name="ref" size="60" value="<?=$action=="trspt"?$ref:"";?>" style="width:120px;margin-right:5px;">
                        <input type="submit" value="OK"><?php echo($liens)?>
                    </div>
                </form>
            </div>

        </div>
<?php
}
?>
        <!-- Recherche par projet -->
        <form method="get" action="projets_new.php" name="search">
            <input type="hidden" name="order" value="<?=$_GET['order']; ?>">
            <input type="hidden" name="sensorder" value="<?=$_GET['sensorder']; ?>">
            <input type="hidden" name="action" value="search">
            <input type="hidden" name="type_page" value="<?= $type_page ?>">
            <strong>Recherche Projet :</strong>
            <div class="bloc_recherche" style="border-bottom:2px solid #5088A1;">
                <input type="text" placeholder="Nom  / Société / N° client / Libellé / N° projet" name="src" size="70" style="display:inline; margin-bottom:12px;" value="<?=$_GET['src']; ?>">
                <input type="submit" title="Rechercher" alt="Rechercher" value="OK">  
            
        
        <p style="cursor:pointer; font-weight:bold;" onclick="$jQuery('#recherche_avancee').show();">Recherche avancée +</p>
        <div id="recherche_avancee" style="display:none;">       
       
            <table class="tab_client">
            <tr>
                <td><b>Commercial :</b></td>
                <td><b>Etat :</b></td>
                <td><b>Type :</b></td>
                <td rowspan="2" style="border:none;"></td>
            </tr>
            <tr>
                <td>
                    <select name="commercial">
                        <?php echo $optcom; ?>
                    </select>
                </td>
                <td>
                    <select name="projet_etatid">
                        <?php echo $liste_etat_txt; ?>
                    </select>
                </td>
                <td>
                    <select name="projet_typeid">
                        <?php echo $liste_type_txt; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><b>Rappel PJT :</b></td>
                <td><b>Rappel PJT Commercial :</b>
            </tr>
            <tr>
                <td>
                    <select name="rappel" onchange="changerappel()">
                        <option value="" <?php echo $selected_rappel_tous; ?>>-- Tous</option>
                        <option value="sans_rappel" <?php echo $selected_sans_rappel; ?>>-- Sans rappel</option>
                        <option value="rappels_retard" <?php echo $selected_rappels_retard; ?>>En retard</option>
                        <option value="rappels_jour" <?php echo $selected_rappels_jour; ?>>Du jour</option>
                        <option value="rappels_futur" <?php echo $selected_rappel_futur; ?>>Demain</option>
                    </select>
                    <span id="rappel_until" style="<?=$rappel_until; ?>">
                        =&gt; <select name="until"><?=$optuntil; ?></select>
                    </span>
                </td>
                <td>
                    <select name="rappel_com">
                        <?php echo $optpjtcom; ?>
                    </select>
                </td>
                <td><a href="utilisateur.php?noprojet=1" target="_blank">Contacts sans projet</a></td>
            </tr>    
            </table>
        </div>
        
    </div>
      </form>
    </div>
</div>
<div class="clear"></div>
