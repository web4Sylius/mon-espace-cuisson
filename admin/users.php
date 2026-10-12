<?php

ini_set("display_errors","1");
error_reporting(E_ALL);

require "admin_global.php";
include "./header.php";
include "./menu.php";

//echo "cette page n'est plus active.<br>Veuillez contacter le service informatique.";
// page désactivée par Benoit le 09/10/2019.
// la raison de cette désactivation : ça fout la merde dans le champ "admin_access"
// il faut donc terminer la tache #5397 avant de réactiver cette page.
//exit;

$superusers = array("philippe", "cc", "benoit", "ariel","jessica","jbandre");
$is_superuser = in_array($user_info['username'], $superusers);
if(!$is_superuser){
	exit;
}

// En attendant multisite
$req=$DB_site2->query("SELECT * FROM site WHERE 1");
while($s=$DB_site2->fetch_array($req))
    $sites[$s['code']]=$s;
    

$error="";
if(isset($_POST['aaccess'])){
    if(isset($_POST['fullaccess'])){
   $sql="SET admin_access=''";
    }else{
   $sql="SET admin_access='".implode(',',$_POST['aaccess'])."'";
    }
    $result=$DB_site2->query("UPDATE parametresgeneraux ".$sql." WHERE username='".$_POST['user']."'");
    if($result==false)
      $error="<span style=\"color:red;\"> Une erreur s'est produite : </span>".mysqli_error($DB_site2->link_id);
    else
      $error="<span style=\"color:green\"> Mise à jour OK</span>";
}

if(isset($_POST['username'])){
  if($_POST['service'] !== ""){
    $DB_site2->query("UPDATE parametresgeneraux SET nom_contact='".$_POST['nom']."', username='".$_POST['username']."', actif='".$_POST['actif']."', service=".$_POST['service']." WHERE username='".$_POST['user']."'");
  }
  else{
    $DB_site2->query("UPDATE parametresgeneraux SET nom_contact='".$_POST['nom']."', username='".$_POST['username']."', actif='".$_POST['actif']."' WHERE username='".$_POST['user']."'");
  }
    
    $_GET['edit']=$_POST['username'];
}

if(isset($_POST['ips'])){
    $DB_site2->query("UPDATE parametresgeneraux SET ip_acces='".$_POST['ips']."' WHERE username='".$_POST['user']."'");
}

if(isset($_POST['pclist'])){
    $DB_site2->query("UPDATE parametresgeneraux SET commissions='".implode(",",$_POST['pclist'])."' WHERE username='".$_GET['edit']."'");
}

if(isset($_POST['obj2012'])){
    $obj=array();
    for($y=2012;$y<=date("Y")+1;$y++){
  $obj[$y]=$_POST['obj'.$y];
    }
    $DB_site2->query("UPDATE parametresgeneraux SET objectifs='".serialize($obj)."' WHERE username='".$_GET['edit']."'");
}

if(isset($_POST['site_code'])){
    $sql="";
    foreach($langues as $langue)
  $sql.=", `mail".$langue."`='".$_POST['mail'.$langue]."', `tel".$langue."`='".$_POST['tel'.$langue]."', `poste".$langue."`='".$_POST['poste'.$langue]."', `fonction".$langue."`='".$_POST['fonction'.$langue]."'";
    $exist=$DB_site2->query_first("SELECT * FROM admin_contact WHERE username='".$_GET['edit']."' AND site_code='".$_POST['site_code']."'");
    if($exist['site_code']!=""){
  $sql="UPDATE admin_contact SET ".substr($sql,1)." WHERE site_code='".$_POST['site_code']."' AND username='".$_GET['edit']."'";
    }else{
  $sql="INSERT INTO admin_contact SET site_code='".$_POST['site_code']."', username='".$_GET['edit']."'".$sql;
    }
    $DB_site2->query($sql);
}

//print "<head>";

$files=array();
$files['catalogue']="catalogue.php,modifier_categorie.php,supprimer_categorie.php,deplacer_categorie.php,ajout_produit.php,ajouter_categorie.php,deplacer_produit.php,copier_produit.php,supprimer_produit.php,impressionfacture.php,rechercher.php,passerbase.php,supprimage.php,petit_champ.php,petit_caract.php,petit_marque.php,supprimg_caractval.php,supprimage.php,marketplace/src.php,traduction_ajax.php,article_ul.php,article_files.php,article_simulateur.php,art_simu_ajax.php,categorie_promo.php,categorie_idkdos.php,categorie_archive.php,ajout_pack.php,prix_temporaires.php,article_img.php,ajout_offre.php,categorie_gamme.php";
//$files['code_barre']="script_codebarre.php";
$files['Modification articles groupés']="modif_groupe.php";
$files['caracteristique']="caracteristique.php,ajouter_caracteristique.php,ajouter_caracteristique_cat.php";
$files['marques']="marques.php";
$files['landing pages']="landing.php";
$files['autopromo']="autopromo.php";
$files['emails_clients']="emails_clients.php";
$files['menu_gestion']="menu_gestion.php";
$files['frais_port']="fraisport.php,fraisport2.php";
$files['sls']="sls.php,montage.php";
$files['gestion_onglets']="onglets.php";
$files['moyens_paiement']="moyens_paiement.php";
$files['page_contact']="pagecontact.php";
$files['page_recherche']="pagerecherche.php";
$files['page_institutionnelles']="institutionnel.php,institutionnel_com.php,editbloc_instit.php";
$files['page_faq']="page_faq.php,editbloc_faq.php";
$files['menu_footer']="menu_footer.php";
$files['menu_garanties']="garanties.php";
$files['conseils_expert']="conseil.php,conseil_com.php,editbloc.php";
$files['tva']="tva.php";
$files['logo_facture']="logofacture.php";
$files['delai_livraison']="delaioichatel.php";
$files['texte_loi_chatel']="texteloichatel.php";
$files['commandes']="facture.php,facture_ajax.php,adv/adv2.php,adv/adv_ul.php,adv/adv_ajax.php,adv/check_edi_ajax.php,suivi_adv.php,sav2_ul.php,sav2_ajax.php,user_files.php,user_ul.php,suivi_sav.php,suivi_ar.php,station_chargeur.php,sav_global.php,bordereau_exp.php,sav_config.php";
$files['sav_rang2']="script_sav_liste.php";
$files['commandes_modif']="facture_create.php";
$files['commandes_attribution']="commande_attribution.php";
$files['sfg']="liste_sfg.php,sfg_retard.php,liste_gcp.php";
$files['contacts_leads']="contacts_leads.php";
$files['clients']="utilisateur.php,clientnavig.php,contacts.php,includes/apercu_relance.php,userfiles/index.php,projet_ul.php";
$files['suivi_commercial']="suivi_commercial.php,includes/suivi_commercial/tableau.php";
$files['suivi_projet']="projet.php,projets_new.php,projets.php,script_prospect_projet.php,stats_objectifs_commerciaux.php";
$files['codes_promo']="cadeau.php,marketplace/src.php";
$files['cavistes']="cavistes.php";
$files['codes_caviste']="code_caviste.php,chequekdo.php";
$files['promotions']="promotions.php";
$files['newsletter']="newsletter.php";
$files['mots_clé']="pagetitle.php";
$files['export_market_place']="comparateur.php,comparateur/amazon.php,comparateur/priceminister.php,comparateur/rueducommerce.php,comparateur/windil.php,comparateur/pixmania.php,comparateur/fnac.php";
$files['commandes_market_place']="marketplace/index.php,marketplace/src.php,marketplace/ajax.php,marketplace2/index.php,marketplace2/src.php,commande_fnac.php,commande_laredoute.php,commande_amazon.php,commande_cdiscount.php,commande_rdc.php";
$files['liens']="liens.php";
$files['partenaires']="partenaires.php";
$files['bandeau_defilant']="bandeau_defilant.php";
$files['statistiques']="statistique.php,referents.php,referentslink.php,referentstri.php,referentsword.php";
$files['chiffre_affaire']="chiffre.php";
$files['chiffre_enregistre']="chiffre_enregistre.php";
$files['chiffre_commercial']="chiffre_commercial.php";
$files['comptabilite']="fournisseur.php,fournisseur_src.php,compta.php,export/export_compta2.php,export/export_compta.php,export/export_fournisseur.php,export/export_nonfacture.php,export/export.php,avoir_fournisseur.php,fournisseur_frais.php,douane.php,compta_sofinco.php,taxe_eco.php";
$files['deb']="deb_export.php,deb_import.php";
$files['save_pjmail']="save_pjmail.php";
$files['spy']="spy.php,export/export_spy.php,export/export_spy_site.php,spy_ajax.php";
$files['sliders']="slider.php";
$files['stats']="stat_articles.php,stat_cmd.php,stat_ca.php,stat_articles_vus.php,stat_reduction_gamme.php,stat_mp.php,export/export_ventes_mp.php,export/export_spy_adcom.php,stat_recherches.php";
$files['stat_recherches']="stat_recherches.php";
$files['stat_spy']="stat_articles_spy.php";
$files['style']="style.php,logopromo.php";
$files['fournisseurs']="fournisseurs_liste.php,fournisseur_files.php,fournisseur_ul.php,fournfiles/dl.php,fournisseur_livraison.php";
$files['crm']="crm.php";
$files['vente_flash']="vente_flash.php,vente_flash_tab.php,includes/vente_flash/tableau.php";
$files['vente_privee']="vente_privee.php,ajax/vp_maj.php";
$files['config_marketplace']="mp_cats.php,mp_cats_ajax.php";
$files['stock']="stock.php,stock_bl.php,stock_adv2.php,includes/stock/form.php";
$files['stock_ajust']="stock_ajust.php";
$files['traduction']="traduction.php,traduction_ajax.php";
$files['coup de coeur accueil']="cdc_index.php,marketplace/src.php";
$files['icones produits']="icones.php";
$files['mailbox']="mailbox.php,mailbox_ajax.php";
$files['remise cheque']="remise_cheque.php,cheque_ajax.php";
$files['taches']="task.php,task.php?bug,taskmanager/fichiers.php,taskmanager/fichier_ul.php,taskmanager/link_search.php,taskmanager/ajax/liste_taches.php,planning_task.php";
$files['gammes']="gammes.php";
$files['gallery']="gallery.php";
$files['presse']="presse.php";
$files['showroom']="showroom.php";
$files['traduction_liste']="traduction_liste.php";
$files['tnt_grille']="tnt_grille.php";
$files['gestion_prix']="gestion_prix.php";
$files['export_uk']="export/export_uk.php";
$files['descriptions']="descriptions.php";
$files['places_de_marche']="pdm_contenus.php,pdm_articles.php,pdm_exports.php,export/export_mp_articles.php";
$files['offres_fournisseur']="offre_fournisseur.php";
$files['export_mailing']="export_mailing.php,export/clients.php,newsletter_email.php";
$files['encarts']="encarts.php";
$files['marque_client']="marque_client.php";
$files['depenses_marketing']="depense_marketing.php";
$files['export_adcom']="export_adcom.php,export/export_adcom.php";
$files['cache']="cloudflare.php";
$files['stat_marge']="marge_articles.php,marge_archive.php,export/export_marge_article.php";
$files['tags_leads']="tags_leads.php";
$files['avis_clients']="avis_clients.php";
$files['geolocalisation']="geolocalisation.php";
$files['visiteurs']="visiteur.php";
$files['stats_rgpd']="statconsent.php";
$files['historique_config']="historique_config.php";
$files['lpb']="lpb.php";
$files['rh']="jour_ferie.php,absence.php,objectifs_commerciaux.php";
$files['tarif']="suivi_tarifs.php,rubriques/port_installateur/index.php,sls.php,montage.php,fraisport.php,tnt_grille.php";
//$files['gestion_prospection']="gestion_prospection.php,gestion_prospection_ajax.php,gestion_prospection_maj.php";

foreach($langues as $langue) $jslangue.="langues['$langue']='$langue'\n";
print "<script language=\"javascript\">
function faccess(){
    if(!document.getElementsByName('fullaccess')[0].checked)
  document.getElementById('chklist').style.visibility='visible';
    else
  document.getElementById('chklist').style.visibility='hidden';
}

function edit_contact(site){
    if(site==undefined){
  for (l in langues){
      document.getElementsByName('mail'+l)[0].value='';
      document.getElementsByName('tel'+l)[0].value='';
      document.getElementsByName('poste'+l)[0].value='';
  }
  document.getElementsByName('site_code')[0].readonly='';
    }else{
  var selectsite=document.getElementsByName('site_code')[0];
  for(i=0;i<selectsite.options.length;i++)
      selectsite.options[i].selected=(site==selectsite.options[i].value)
  selectsite.readonly='true';
  for(l in langues){
      document.getElementsByName('mail'+l)[0].value=document.getElementById('mail'+site+l).innerHTML;
      document.getElementsByName('tel'+l)[0].value=document.getElementById('tel'+site+l).innerHTML;
      document.getElementsByName('poste'+l)[0].value=document.getElementById('poste'+site+l).innerHTML;
      document.getElementsByName('fonction'+l)[0].value=document.getElementById('fonction'+site+l).innerHTML;
  }
    }
    document.getElementById('edit_contact').style.display='table';
}

var langues=new Array();
$jslangue
</script>";

print "<style>
#chklist td { border-top:solid 2px #CCC; vertical-align:top; }
</style>";

//print "</head>
//<body onload=\"faccess()\">";

$requete=$DB_site2->query("SELECT username FROM parametresgeneraux WHERE actif=1");
print "Actifs : ";
while($user=$DB_site2->fetch_array($requete)){
    print "<a href=\"users.php?edit=".$user[0]."\">".$user[0]."</a> ";
}
$requete=$DB_site2->query("SELECT username FROM parametresgeneraux WHERE actif=0");
print "<br>Inactifs : ";
while($user=$DB_site2->fetch_array($requete)){
    print "<a href=\"users.php?edit=".$user[0]."\">".$user[0]."</a> ";
}

if(isset($_GET['edit'])){
    $requete=$DB_site2->query("SELECT * FROM parametresgeneraux WHERE username='".$_GET['edit']."'");
    $user_m=$DB_site2->fetch_array($requete);
    $aaccess_m=explode(",",$user_m['admin_access']);
    $pclist=explode(",",$user_m['commissions']);
    $obj=unserialize($user_m['objectifs']);
    $services = $DB_site2->query("SELECT * FROM services");
    $option = "<option></option>";
    while ($service = $DB_site2->fetch_array($services)) {
      $option .= "<option value=\"".$service['id_service']."\"".($user_m['service']==$service['id_service']?" selected":"")." >".$service['nom']."</option>";
    }

    print "<p>Modifier ".htmlentities($_GET['edit'],ENT_COMPAT,"ISO-8859-15")."</p>
    <div style=\"border:solid thin #aaaaaa;\">
      <form method=\"post\">
      <input type=\"hidden\" name=\"user\" value=\"".htmlentities($_GET['edit'],ENT_COMPAT,"ISO-8859-15")."\">
      User name : <input type=\"text\" name=\"username\" value=\"".$user_m['username']."\">
      <br>Nom prénom : <input type=\"text\" name=\"nom\" value=\"".$user_m['nom_contact']."\">
      <br><input type=\"checkbox\" name=\"actif\" value=\"1\"".($user_m['actif']==1?" checked":"")."> Actif
      <br>Service : <select name=\"service\">".$option."</select>
      <br>
      <br><input type=\"submit\" value=\"Modifier\">
      </form>
    </div>
    <br>
    <div style=\"border:solid thin #aaaaaa;\">
      <form method=\"post\">
      <input type=\"hidden\" name=\"user\" value=\"".htmlentities($_GET['edit'],ENT_COMPAT,"ISO-8859-15")."\">
      IPs : <input type=\"text\" name=\"ips\" size=\"40\" value=\"".$user_m['ip_acces']."\"> <input type=\"submit\" value=\"Modifier\">
      </form>
    </div>
    <br>
    <!--<div style=\"border:solid thin #aaaaaa;\">
      <form method=\"post\">
  <p>Commissions : </p>
  <table>
  <tr><td>- de 300</td><td><input type=\"text\" name=\"pclist[]\" value=\"".floatval($pclist[0])."\" size=\"2\">%</td></tr>
  <tr><td>300 - 1000</td><td><input type=\"text\" name=\"pclist[]\" value=\"".floatval($pclist[1])."\" size=\"2\">%</td></tr>
  <tr><td>1000 - 1500</td><td><input type=\"text\" name=\"pclist[]\" value=\"".floatval($pclist[2])."\" size=\"2\">%</td></tr>
  <tr><td>+ de 1500</td><td><input type=\"text\" name=\"pclist[]\" value=\"".floatval($pclist[3])."\" size=\"2\">%</td></tr>
  </table>
  <input type=\"submit\" value=\"Mettre à jour\">
      </form>
    </div>
    <br>
    <div style=\"border:solid thin #aaaaaa;\">
    <form method=\"post\">
    <p>Objectifs :</p>";
    for($y=2012;$y<=date("Y")+1;$y++){
  print "<table style=\"border:solid thin #aaa;float:left;margin-left:10px;\">
  <tr><td colspan=\"2\" align=\"center\">$y</td></tr>";
  for($m=1;$m<=12;$m++){
      print "<tr><td>".$m."/".$y."</td><td><input type=\"text\" name=\"obj".$y."[]\" value=\"".floatval($obj[$y][$m-1])."\" value=\"\" size=\"4\">&euro;</td></tr>";
  }
  print "</table>";
    }
    print "<div style=\"clear:both;\"></div>
    <input type=\"submit\" value=\"Mettre à jour\">
    </form>
    </div>-->";
    
    print "<div style=\"border:solid thin #aaaaaa;\">
    Contact <a href=\"javascript:edit_contact();\">Ajouter</a>
    <table border=\"1\">
    <tr>
      <td>Langue</td>
      <td>E mail</td>
      <td>Téléphone</td>
      <td>Poste téléphone</td>
      <td>Fonction</td>
    </tr>";
    $req=$DB_site2->query("SELECT * FROM admin_contact WHERE username='".$_GET['edit']."' ORDER BY site_code");
    while($adm_contact=$DB_site2->fetch_array($req)){
  print "<tr>
    <td><b>".$adm_contact['site_code']."</b> <img src=\"images/editer.gif\" onclick=\"edit_contact('".$adm_contact['site_code']."')\"></td>
  </tr>";
  foreach($langues as $langue){
      print "<tr>
        <td>".$langue."</td>
        <td id=\"mail".$adm_contact['site_code'].$langue."\">".$adm_contact['mail'.$langue]."</td>
        <td id=\"tel".$adm_contact['site_code'].$langue."\">".$adm_contact['tel'.$langue]."</td>
        <td id=\"poste".$adm_contact['site_code'].$langue."\">".$adm_contact['poste'.$langue]."</td>
        <td id=\"fonction".$adm_contact['site_code'].$langue."\">".$adm_contact['fonction'.$langue]."</td>
      </tr>";
  }
    }
    
    $optsite="";
    foreach($sites as $site_code=>$site)
  $optsite.="<option value=\"$site_code\">$site_code</option>";
    
    print "</table>
    <form method=\"post\">
    <table id=\"edit_contact\" style=\"display:none;\">
    <tr>
      <td>Site</td>
      <td><select name=\"site_code\">$optsite</select></td>
    </tr>";
    
    foreach($langues as $langue)
      print "<tr>
  <td>Mail $langue</td>
  <td><input type=\"text\" name=\"mail$langue\"></td>
      </tr>
      <tr>
  <td>Tél $langue</td>
  <td><input type=\"text\" name=\"tel$langue\"></td>
      </tr>
      <tr>
  <td>Poste $langue</td>
  <td><input type=\"text\" name=\"poste$langue\"></td>
      </tr>
      <tr>
  <td>Fonction $langue</td>
  <td><input type=\"text\" name=\"fonction$langue\" size=\"40\"></td>
      </tr>";
      
    print "<tr>
      <td><input type=\"submit\"></td>
    </tr>
    </table>
    </form>
    </div><br>";
    
    print "<div style=\"border:solid thin #aaaaaa;\">
      <form method=\"post\">
      <input type=\"hidden\" name=\"user\" value=\"".htmlentities($_GET['edit'],ENT_COMPAT,"ISO-8859-15")."\">";
      print "<p style=\"color:red;\"><input type=\"checkbox\" name=\"fullaccess\" value=\"1\" onclick=\"faccess();\"".($user_m['admin_access']==""?" checked":"").">Accès total</p>";
      print "<p>".chkaccess($files['mailbox'])." Envoi d'email</p>";
      print "<table id=\"chklist\">
      <tr>
  <td>Catalogue</td>
  <td>".chkaccess($files['catalogue'])." Catalogue
  <br>".chkaccess($files['gestion_prix'])." Gestion des prix
  <br>".chkaccess($files['marques'])." Marques
  <br>".chkaccess($files['caracteristique'])." Caractéristiques
  <br>".chkaccess($files['Modification articles groupés'])." Modification groupée
  <br>".chkaccess($files['Modification articles energie'])." Modification groupée énergie
  <br>".chkaccess($files['offres_fournisseur'])." Offres achat fournisseur
  <br>Prix vente temporairee &gt; Catalogue".
  //<br>".chkaccess($files['code_barre'])." Génération code barre
  "</td>
      </tr>
      <tr>
  <td>Tarif</td>
  <td>".chkaccess($files['tarif'])." suivi des tarifs &gt;
  <br>Frais installateurs &gt; Prix de vente installation
  <br>Frais montage &gt; Frais de port
  <br>Tarif Fedex
  </td>
      </tr>
      <tr>
  <td>Paramètres</td>
  <td>Affichage marques &gt; Accès total
  <br>".chkaccess($files['emails_clients'])." Gestion emails
  <br>".chkaccess($files['menu_gestion'])." Gestion menu
  <br>".chkaccess($files['conseils_expert'])." Conseils d'experts
  <br>".chkaccess($files['menu_garanties'])." Garanties
  <br>".chkaccess($files['page_institutionnelles'])." Pages institutionnelles
  <br>".chkaccess($files['page_faq'])." FAQ
  <br>".chkaccess($files['moyens_paiement'])." Moyens de paiement
  <br>".chkaccess($files['showroom'])." Showroom
  </td>
      </tr>
      <tr>
  <td>Places de marché</td>
  <td>
  ".chkaccess($files['places_de_marche'])." Gérer les produits
  <br>Gérer les contenus &gt; Gérer les produits
  <br>Exports &gt; Gérer les produits
  <br>Téléchargement récap &gt; Gérer les produits
  </td>
      </tr>
      <tr>
  <td>Animations</td>
  <td>".chkaccess($files['coup de coeur accueil'])." Coup de coeur accueil
  <br>".chkaccess($files['icones produits'])." Icones produits
  <br>".chkaccess($files['landing pages'])." Landing pages
  <br>".chkaccess($files['sliders'])." Sliders
  <br>".chkaccess($files['style'])." Fond de page
  <br>Logo promos et kdo &gt; Fond de page
  <br>".chkaccess($files['vente_flash'])." Vente flash
  <br>".chkaccess($files['vente_privee'])." Vente privée
  <br>Affichage des catégories &gt; Accès total
  <br>".chkaccess($files['gallery'])." Gallerie photos
  <br>".chkaccess($files['presse'])." Dans la presse
  <br>".chkaccess($files['encarts'])." Encarts
  <br>".chkaccess($files['marque_client'])." Marques clients
  <br>".chkaccess($files['depenses_marketing'])." Dépenses marketing
  <br>".chkaccess($files['export_adcom'])." Export Adcom
  </td>
      </tr>
      <tr>
  <td>Commercial</td>
  <td>".chkaccess($files['clients'])." Fiches client
  <br>Nouveau contact &gt; Fiches client
  <br>".chkaccess($files['suivi_commercial'])." Suivi commercial
  <br>".chkaccess($files['suivi_projet'])." Suivi Projets
  <br>".chkaccess($files['codes_promo'])." Codes promo
  <br>".chkaccess($files['spy'])." Spy
  <br>".chkaccess($files['export_market_place'])." Export market place
  <br>".chkaccess($files['config_marketplace'])." Config market place
  <br>Historique contact &gt; fiches client
  <br>".chkaccess($files['contacts_leads'])." Historique contacts campagne
  <br>Commissions &gt; Accès total
  <br>Erreurs emails &gt; Accès total
  <br>".chkaccess($files['export_mailing'])." Export mailing
  <br>Désinscription newsletter &gt; Export mailing
  <br>".chkaccess($files['tags_leads'])." Tags leads
  <br>".chkaccess($files['avis_clients'])." Avis clients
  <br>".chkaccess($files['geolocalisation'])." Géolocalisation
  <br>".chkaccess($files['visiteurs'])." Visiteurs
  <br>".chkaccess($files['stats_rgpd'])." Stats RGPD
  <br>".chkaccess($files['historique_config'])." Historique configurateur
  </td>
      </tr>
      <tr>
  <td>Commande</td>
  <td>".chkaccess($files['commandes'])." Commandes site
  <br>".chkaccess($files['commandes_modif'])." Modification commandes
  <br>".chkaccess($files['commandes_market_place'])." Market place
  <br>Suivi des commandes à traiter &gt; Commandes site
  <br>Suivi des expéditions &gt; Commandes site
  <br>Suivi des livraisons &gt; Commandes site
  <br>Suivi des AR &gt; Commandes site
  </td>
      </tr>
      <tr>
  <td>Stock</td>
  <td>".chkaccess($files['stock'])." Gestion du stock
  <br>Commandes en attente &gt; Gestion du stock
  <br>Suivi &gt; Gestion du stock
  <br>".chkaccess($files['stock_ajust'])." Inventaire du stock
  </td>
      </tr>
      <tr>
  <td>Qualité SAV</td>
  <td>Commandes SAV &gt; Commandes site
  <br>Suivi SAV &gt; Commandes site
  <br>".chkaccess($files['sav_rang2'])." Liste SAV rang 2++
  <br>".chkaccess($files['sfg'])." Garanties SFG
  <br>Garanties GC+ &gt; Garantie SFG
  <br>".chkaccess($files['export_uk'])." Export UK
  </td>
      </tr>
      <tr>
  <td>Partenariats</td>
  <td>".chkaccess($files['cavistes'])." Partenaires
  <br>".chkaccess($files['codes_caviste'])." Codes partenaires
  <br>".chkaccess($files['liens'])." Espace liens
  <br>".chkaccess($files['partenaires'])." Espace partenaires
  <br>".chkaccess($files['crm'])." CRM
  <br>".chkaccess($files['lpb'])." Le Petit Ballon
  </td>
      </tr>
      <tr>
  <td>Fournisseurs</td>
  <td>".chkaccess($files['fournisseurs'])." Fournisseurs
  <br>Attribuer marques &gt; Fournisseurs
  <br>".chkaccess($files['save_pjmail'])."Signatures fournisseurs
  <br>".chkaccess($files['sls'])." Prix de vente installation
  <br>Frais de montage &gt; Frais de port SLS
  <br>Infos livraison transporteurs &gt; Fournisseurs
  </td>
      </tr>
      <tr>
  <td>Pays</td>
  <td>".chkaccess($files['frais_port'])." Frais de port
  <br>".chkaccess($files['tva'])." Taux TVA
  <br>Franco de port &gt; Frais de port
  <br>".chkaccess($files['tnt_grille'])." Tarifs FEDEX Express
  </td>
      </tr>
      <tr>
  <td>Traduction</td>
  <td>".chkaccess($files['traduction'])." Traduction
  <br>".chkaccess($files['traduction_liste'])." Traductions à faire
  <br>".chkaccess($files['gammes'])." Traduction gammes
  <br>Traduction libellés &gt; Traduction gammes
  <br>".chkaccess($files['descriptions'])." Descriptions
  </td>
      </tr>
      <tr>
  <td>Comptabilité</td>
  <td>".chkaccess($files['comptabilite'])." Factures fournisseur
  <br>Frais généraux &gt; Factures fournisseur
  <br>Avoirs fournisseur &gt; Factures fournisseur
  <br>".chkaccess($files['remise cheque'])." Remise de chèques
  <br>Codes analytiques &gt; Accès total
  <br>Comptes de charge &gt; Accès total
  <br>Suivi fraude &gt; Commandes site
  <br>Factures fournisseur
  <br>Douanes &gt; Factures fournisseur
  <br>Sofinco
  <br>".chkaccess($files['deb'])." DEB
    <br>".chkaccess($files['rh'])." RH - jours fériés
    <br>RH - absences
    <br>RH - objectifs commerciaux
  </td>
      </tr>
      <tr>
  <td>Stats</td>
  <td>".chkaccess($files['chiffre_affaire'])." Chiffre d'affaire facturé
  <br>".chkaccess($files['chiffre_enregistre'])." Chiffre enregistré
  <br>Chiffres d'affaires export &gt; Accès total
  <br>Stats cde enregistrées &gt; Stats articles
  <br>".chkaccess($files['stats'])." Stats articles vendus
  <br>Stats articles vus &gt; Stats articles vendus
  <br>Stats réduction gamme &gt; Stats articles
  <br>Stats CA &gt; Stats articles
  <br>Stats ventes marketplaces &gt; Stats articles
  <br>".chkaccess($files['chiffre_commercial'])." Stat commercial
  <br>Stats objectif commercial &gt; Projets
  <br>Clients perdus &gt; Accès total
  <br>Stats articles
  <br>".chkaccess($files['stat_recherches'])." Stats recherches effectuées
  <br>".chkaccess($files['stat_spy'])." Etude concurrentielle site
  <br>".chkaccess($files['stat_marge'])." Etude marge article
  <br>".chkaccess($files['commandes_attribution'])." Attribution commandes
        </td>
      </tr>
      <tr>
  <td>Suivi interne</td>
  <td>".chkaccess($files['taches'])." Taches en cours
  <br>Gestion des bugs &gt; Taches en cours
  </td>
      </tr>
      <tr>
        <td>Cache</td>
        <td>".chkaccess($files['cache'])."</td>
      </tr>
      </table>";
      /*print "<table id=\"chklist\"><tr>";
      $c=0;
      ksort($files);
      foreach($files as $menu=>$pages){
  $checked="";
  $page1=split(',',$pages);
  if(in_array($page1[0],$aaccess_m)) $checked=" checked";
      
  print "<td><input type=\"checkbox\" name=\"aaccess[]\" value=\"$pages\" $checked> $menu</td>";
  $c++;
  if($c==2){
    print "</tr><tr>";
    $c=0;
  }
      }
      print "</tr></table>";*/
      print "<p><input type=\"submit\" value=\"Mettre à jour\"></p>
      </form>
    </div>";


  print "<br>
  <div style=\"border:solid thin #aaaaaa;\">
  Connexions
  <table cellpadding=\"5\" style=\"font-size:11px;\" border=\"1\">
  <tr style=\"text-align:center; font-weight:bold;\"><td>Date connexion</td><td>Dernière activité</td><td>Pages affichées</td><td>IP</td><td>Connexion</td></tr>";
  $suivi=array();
  $totalhours=0;
  $requete=$DB_site2->query("SELECT * FROM acces_admin WHERE user='".$_GET['edit']."' AND connectts>".(time()-(365*86400))." ORDER BY connectts DESC");
  while($connect=$DB_site2->fetch_array($requete)){
    $totalhours+=($connect['timestamp']-$connect['connectts'])/3600;
    print "<tr><td>".($connect['connectts']!=0?date("D d/m/Y H:i",$connect['connectts']):"")."</td><td>".date("D d/m/Y H:i",$connect['timestamp'])."</td><td>".$connect['pages']."</td><td>".$connect['ip']."</td><td>".($connect['connecte']==1?"OK":"<span style=\"color:red;\">Erreur</span>")."</td><td><a href=\"users.php?edit=".$_GET['edit']."&details=".$connect['connectts']."#details\">Détails</a></td></tr>";
    if(isset($_GET['details']) && $_GET['details']==$connect['connectts']){
  $rmail=$DB_site2->query("SELECT * FROM mail_outbox WHERE utilisateur='".$_GET['edit']."' AND date BETWEEN ".$connect['connectts']." AND ".$connect['timestamp']." ORDER BY date");
  while($mail=$DB_site2->fetch_array($rmail)){
     $suivi[$mail['date']]="<tr style=\"background-color:#CCCCCC;\"><td valign=\"top\">".date("H:i",$mail['date'])."<br><img src=\"images/send_mail.png\"></td><td valign=\"top\">Email à ".$mail['to']."<br>".$mail['subject']."</td><td>".formatsuivi($mail['body'])."</td></tr>";
  }
  $rsuivi=$DB_site2->query("SELECT * FROM suivi_commande WHERE commercial='".$_GET['edit']."' AND date BETWEEN ".$connect['connectts']." AND ".$connect['timestamp']." ORDER BY date");
  while($s=$DB_site2->fetch_array($rsuivi)){
     $suivi[$s['date']]="<tr style=\"background-color:#CCCCCC;\"><td valign=\"top\">".date("H:i",$s['date'])."<br><img src=\"images/panier.png\" width=\"32\"></td><td valign=\"top\">Suivi commercial <a href=\"facture.php?action=editer&facturid=$s[facturid]\" target=\"_blank\">".$s['facturid']."</a><br>".($s['rappel']!=0?"rappel le ".date("d/m/Y",$s['rappel'])." pour ".$s['pour']:"")."</td><td>".formatsuivi($s['suivi'])."</td></tr>"; 
  }
  $etats=array(0=>"<span color=\"red\">Client perdu</span>",1=>"<span style=\"color:#0000AA;\">En cours</span>",2=>"<span style=\"color:#0000FF;\">Presque sûr</span>",3=>"<span style=\"color:#000088;\">Peu probable</span>");
  $rsuivi=$DB_site2->query("SELECT sc.*,CONCAT(u.nom,' ',u.prenom) AS client FROM suivi_client sc INNER JOIN utilisateur u ON(u.userid=sc.userid) WHERE sc.commercial='".$_GET['edit']."' AND date BETWEEN ".$connect['connectts']." AND ".$connect['timestamp']." ORDER BY date");
  while($s=$DB_site2->fetch_array($rsuivi)){
     $suivi[$s['date']]="<tr style=\"background-color:#CCCCCC;\"><td valign=\"top\">".date("H:i",$s['date'])."<br><img src=\"images/client.png\"></td><td valign=\"top\">Suivi client <a href=\"utilisateur.php?action=editer&userid=$s[userid]\" target=\"_blank\">".$s['client']."</a><br>".($s['rappel']!=0?"rappel le ".date("d/m/Y",$s['rappel'])." pour ".$s['pour']:"")."<br>Etat : ".$etats[$s['etat']]."</td><td>".formatsuivi($s['suivi'])."</td></tr>"; 
  }
  $rcrm=$DB_site2->query("SELECT c.*,UNIX_TIMESTAMP(c.date_entered) AS date FROM crm_calls c WHERE c.created_by='".$_GET['edit']."' AND UNIX_TIMESTAMP(c.date_entered) BETWEEN ".$connect['connectts']." AND ".$connect['timestamp']);
  while($s=$DB_site2->fetch_array($rcrm)){
      $r=$DB_site2->query("SELECT * FROM crm_accounts WHERE id='".$s['parent_id']."'");
      $account=$DB_site2->fetch_array($r);
      $suivi[$s['date']]="<tr style=\"background-color:#CCCCCC;\"><td valign=\"top\">".date("H:i",$s['date'])."<br><img src=\"images/sugar_crm.png\"></td><td valign=\"top\">Suivi CRM <a href=\"crm.php?id=$s[parentid]\" target=\"_blank\">".$account['name']."</a><br>".($s['reminder_time']!=0?"rappel le ".date("d/m/Y",$s['reminder_time'])." pour ".$s['assigned_user_id']:"")."</td><td>".formatsuivi($s['description'])."</td></tr>"; 
  }
  $rsav=$DB_site2->query("SELECT * FROM sav_steps WHERE utilisateur='".$_GET['edit']."' AND date BETWEEN ".$connect['connectts']." AND ".$connect['timestamp']);
  while($s=$DB_site2->fetch_array($rsav)){
      $r=$DB_site2->query("SELECT * FROM sav WHERE id=".$s['savid']);
      $facture=$DB_site2->fetch_array($r);
      $suivi[$s['date']]="<tr style=\"background-color:#CCCCCC;\"><td valign=\"top\">".date("H:i",$s['date'])."<br><img src=\"images/sav.png\"</td><td valign=\"top\">Etape ".$s['step']." SAV <a href=\"facture.php?action=editer&facturid=$facture[facturid]&savid=$s[savid]\" target=\"_blank\">".$facture['facturid']."</a><br>".($s['rappel']!=0?"rappel le ".date("d/m/Y",$s['rappel'])." pour ".$s['pour']:"")."</td><td>".formatsuivi($s['texte'])."</td></tr>"; 
  }
  $rsav=$DB_site2->query("SELECT * FROM sav WHERE utilisateur='".$_GET['edit']."' AND date BETWEEN ".$connect['connectts']." AND ".$connect['timestamp']);
  while($s=$DB_site2->fetch_array($rsav)){
      $suivi[$s['date']]="<tr style=\"background-color:#CCCCCC;\"><td valign=\"top\">".date("H:i",$s['date'])."<br><img src=\"images/sav.png\"></td><td valign=\"top\">Création SAV <a href=\"facture.php?action=editer&facturid=$s[facturid]&savid=$s[id]\" target=\"_blank\">".$s['facturid']."</a></td><td></td></tr>"; 
  }
  $valide=array(0=>"<span style=\"color:#000000\">Non traitée</span>",1=>"<span style=\"color:#0000FF\">Validée</span>",2=>"<span style=\"color:#FF0000\">Refus commerçant</span>",3=>"<span style=\"color:#FF0000\">Refus CB</span>",4=>"<span style=\"color:#FF0000\">Abandon internaute</span>",5=>"<span style=\"color:#00FF00\">Expédiée</span>",7=>"<span style=\"color:blue\">Validée non payée</span>",8=>"<span style=\"color:green\">Expédiée non payée</span>");
  $rfact=$DB_site2->query("SELECT facturid,heurefacture,valide,nom,prenom,commercial FROM facture WHERE commercial='".$_GET['edit']."' AND heurefacture BETWEEN ".$connect['connectts']." AND ".$connect['timestamp']);
  while($s=$DB_site2->fetch_array($rfact)){
      $suivi[$s['heurefacture']]="<tr style=\"background-color:#CCCCCC;\"><td valign=\"top\">".date("H:i",$s['heurefacture'])."<br><img src=\"images/panier.png\" width=\"32\"></td><td valign=\"top\">Commande par téléphone <a href=\"facture.php?action=editer&facturid=$s[facturid]\" target=\"_blank\">".$s['facturid']."</a><br>".$valide[$s['valide']]."</td><td></td></tr>"; 
  }
  $rcom=$DB_site2->query("SELECT * FROM facture_fournisseur WHERE utilisateur='".$_GET['edit']."' AND date_saisie BETWEEN ".$connect['connectts']." AND ".$connect['timestamp']);
  while($s=$DB_site2->fetch_array($rcom)){
      $suivi[$s['date_saisie']]="<tr style=\"background-color:#CCCCCC;\"><td valign=\"top\">".date("H:i",$s['date_saisie'])."<br><img src=\"images/pro.png\" width=\"32\"></td><td valign=\"top\">Création facture fournisseur <a href=\"fournisseur.php?action=editer&facturid=$s[numero]\" target=\"_blank\">".$s['numero']."</a><br>".$s['fournisseur']."</td><td></td></tr>";
  }
  $radv=$DB_site2->query("SELECT ef.facturid,ef.destinataire,ef.date FROM envoi_fournisseur ef INNER JOIN envoi_fournisseur_ligne efl ON(efl.efid=ef.id) WHERE efl.expediteur='".$_GET['edit']."' AND ef.destinataire<>'0' AND ef.date BETWEEN ".$connect['connectts']." AND ".$connect['timestamp']);
  while($s=$DB_site2->fetch_array($radv)){
      $suivi[$s['date']]="<tr style=\"background-color:#CCCCCC;\"><td valign=\"top\">".date("H:i",$s['date'])."<br><img src=\"images/pro.png\" width=\"32\"></td><td valign=\"top\">Commande fournisseur <a href=\"facture.php?action=editer&facturid=$s[facturid]#adv\" target=\"_blank\">".$s['facturid']."</a><br>".$s['destinataire']."</td><td></td></tr>";
  }
  $arth=$DB_site2->query("SELECT * FROM article_history WHERE utilisateur='".$_GET['edit']."' AND date BETWEEN ".$connect['connectts']." AND ".$connect['timestamp']." ORDER BY date");
  while($s=$DB_site2->fetch_array($arth)){
      if($s['date']!=$last){
        $req=$DB_site2->query("SELECT artcode,libelleFR FROM article WHERE artid=".$s['artid']);
        $article=$DB_site2->fetch_array($req);
        $suivi[$s['date']].="<tr style=\"background-color:#CCCCCC;\"><td valign=\"top\">".date("H:i",$s['date'])."<br><img src=\"images/cave.png\" width=\"32\"></td><td valign=\"top\">Modification article <a href=\"ajout_produit.php?artid=$s[artid]&prec=modif\" target=\"_blank\">".$article['artcode']."</a> (".$article['libelleFR'].")</td><td></td></tr>";
      }
      $last=$s['date'];
  }
  print "<tr><td colspan=\"6\" style=\"padding-left:10px;\"><a name=\"details\"></a>";
  if(count($suivi)>0){
    ksort($suivi);
    print "<table style=\"font-size:11px;\">";
    foreach($suivi as $tr) print $tr;
    print "</table>";
  }else
    print "Aucune info";
    print "</td></tr>";
    }
  }
  print "<br>".round($totalhours);
}

print "</table>";

function chkaccess($pages){
    global $aaccess_m;
    $page1=explode(',',$pages);
    $checked=(in_array($page1[0],$aaccess_m)?"checked":"");
    return "<input type=\"checkbox\" name=\"aaccess[]\" value=\"".$pages."\" ".$checked.">";
}

include "./footer.php";
?>