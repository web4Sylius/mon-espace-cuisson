<?php

include("global.php");

$template->parts["optionspays"]="<option></option>";

$sql="";
if($_GET['modelivraison']==2){
    $install=pays_install($user_info['paysid']);
    if($install=="sls") $payssls=payssls();
    if($install=="enigma") $payssls=pays_enigma();
    if(!is_null($payssls)) $sql="AND paysid IN (".implode(",",$payssls).")";
    
}
$listpays=$DB_site->query("SELECT p.libelle,p.paysid FROM pays p INNER JOIN fraisport USING(paysid) WHERE sites='".$template->site_code()."' $sql GROUP BY paysid ORDER BY libelle");
while($pays=$DB_site->fetch_array($listpays))
  $template->parts["optionspays"].="<option value=\"".$pays['paysid']."\">".$pays['libelle']."</option>";

if($_GET['formid']==1){
  $pays_livraison=$DB_site->query_first("SELECT libelle FROM pays WHERE paysid=".intval($user_info['paysid']));
  $template->parts["adresse"]=$user_info['raisonsociale']."<br><b>".$user_info['nom']." ".$user_info['prenom']."</b>
  <br>".$user_info['adresse']."
  <br>".$user_info['codepostal']." ".$user_info['ville']."
  <br>".$pays_livraison['libelle']."<br>&nbsp;";
}

$template->parts['civilite']=$user_info['civilite'];
$template->parts['raisonsociale']=$user_info['raisonsociale'];
$template->parts['nom']=$user_info['nom'];
$template->parts['prenom']=$user_info['prenom'];
$template->parts['rueadresse']=$user_info['adresse'];
$template->parts['codepostal']=$user_info['codepostal'];
$template->parts['ville']=$user_info['ville'];
$template->parts['paysid']=$user_info['paysid'];
$template->parts['telephone']=$user_info['portable'];
if(trim($user_info['portable'])=="") $template->parts['telephone']=$user_info['telephone'];
$template->parts['mail']=$user_info['mail'];
if($_GET['modelivraison']==2 && $template->langue=="FR") $template->evaltemplate("RecupCave","recup");

$adresse_complete = false;
$template->parts["etape_suivante1"] = "alert('Votre adresse postale est incomplète. Rendez-vous dans votre compte et complétez la, avant de poursuivre votre commande');";
$template->parts["etape_suivante2"] = "alert('Votre adresse postale est incomplète. Rendez-vous dans votre compte et complétez la, avant de poursuivre votre commande');";
/*if(!empty($user_info[adresse]) && !empty($user_info[codepostal]) && !empty($user_info[ville])){
     $adresse_complete = true;
     if(isset($_GET['affiche_options']) && $_GET['affiche_options'] == 1){
          $template->parts["etape_suivante1"] = "check_informations_livraison(".$_GET['facture_id'].", '".$_GET['div_options']."');";
          $template->parts["etape_suivante2"] = "veriflivr(".$_GET['facture_id'].",'".$_GET['div_options']."');";
     } else {
          $template->parts["etape_suivante1"] = "check_informations_livraison();";
          $template->parts["etape_suivante2"] = "veriflivr();";
     }
}*/

$template->evaltemplate("LivrForm".intval($_GET['formid']),"form");

print stripslashes($template->parts["form"]);

?>