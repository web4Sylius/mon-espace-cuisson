<head>
<style>
.menu { width:150px; height:20px; line-height:20px; background-color:#DDD; border-style:solid; border-width:2px; border-color:#CCC #888 #888 #CCC; color:blue; padding-left:5px; cursor:pointer; font-weight:bold; font-family:arial; font-size:11px; color:#2B2320; }
.menu:hover { background-color:#cdcdFF; }
.menu a { text-decoration:none; color:#2B2320; }
.menu .arrow { float:right; margin-right:5px; }
.ssmenu { width:100px; display:none; position:absolute; margin-left:5px; }
</style>
</head>

<body>

<?php

include("menu.class.php");
$position=0;

$accueil=new menu("Accueil","index.php");
$accueil->directory="/admin";
print $accueil->draw();

$catalogue=new menu("Catalogue");
$catalogue->position=$position;
$catalogue->directory="/admin";
$catalogue->addssmenu(new menu("Catalogue","catalogue.php"));
$catalogue->addssmenu(new menu("Marques","marques.php"));
$catalogue->addssmenu(new menu("Caractéristiques","caracteristique.php"));
$catalogue->addssmenu(new menu("Modification groupée","modif_groupe.php"));
print $catalogue->draw();

$param=new menu("Paramètres");
$param->position=$position;
$param->directory="/admin";
$param->addssmenu(new menu("Affichage marque","display_marque.php"));
$param->addssmenu(new menu("Gestion emails","emails_clients.php"));
$param->addssmenu(new menu("Conseils d'experts","conseil.php"));
$param->addssmenu(new menu("Garanties","garanties.php"));
$param->addssmenu(new menu("Pages institutionnelles","institutionnel.php"));
$param->addssmenu(new menu("Moyens de paiement","moyens_paiement.php"));
print $param->draw();

$anim=new menu("Animations");
$anim->position=$position;
$anim->directory="/admin";
$anim->addssmenu(new menu("Coup de coeur accueil","cdc_index.php"));
$anim->addssmenu(new menu("Envoi newsletter","newsletter.php"));
$anim->addssmenu(new menu("Icones","icones.php"));
$anim->addssmenu(new menu("Landing pages","landing.php"));
$anim->addssmenu(new menu("Sliders","slider.php"));
$anim->addssmenu(new menu("Style du site","style.php"));
$anim->addssmenu(new menu("Vente flash","vente_flash.php"));
$anim->addssmenu(new menu("Icones","icones.php"));
$anim->addssmenu(new menu("Affichage des catégories","display_categorie.php"));
print $anim->draw();

$commercial=new menu("Commercial");
$commercial->position=$position;
$commercial->directory="/admin";
$commercial->addssmenu(new menu("Fiches client","utilisateur.php"));
$commercial->addssmenu(new menu("Nouveau contact","utilisateur.php?action=new"));
$commercial->addssmenu(new menu("Suivi commercial","suivi_commercial.php"));
$commercial->addssmenu(new menu("Bons de réduction","cadeau.php"));
$commercial->addssmenu(new menu("SPY","spy.php"));
$commercial->addssmenu(new menu("Comparateurs de prix","comparateur.php"));
$commercial->addssmenu(new menu("Config market place","mp_cats.php"));
$commercial->addssmenu(new menu("Historique contacts","contacts.php"));
$commercial->addssmenu(new menu("Commission commercial","comcom.php"));
print $commercial->draw();

$commande=new menu("Commande");
$commande->position=$position;
$commande->directory="/admin";
$commande->addssmenu(new menu("Commandes site","facture.php"));
$commande->addssmenu(new menu("Market place","marketplace/index.php"));
$commande->addssmenu(new menu("Suivi des cde à traiter","suivi_adv.php?nocmd=1"));
$commande->addssmenu(new menu("Suivi des expéditions","suivi_adv.php"));
$commande->addssmenu(new menu("Suivi des livraisons","suivi_adv.php?tdate=outdate"));
$commande->addssmenu(new menu("Gestion du stock","stock.php"));
print $commande->draw();

$qualite=new menu("Qualité SAV");
$qualite->position=$position;
$qualite->directory="/admin";
$qualite->addssmenu(new menu("Commandes SAV","facture.php?sav=1"));
$qualite->addssmenu(new menu("Suivi SAV","suivi_sav.php"));
$qualite->addssmenu(new menu("Garanties SFG","liste_sfg.php"));
$qualite->addssmenu(new menu("Garanties GC+","liste_gcp.php"));
print $qualite->draw();

$partenariat=new menu("Partenariats");
$partenariat->position=$position;
$partenariat->directory="/admin";
$partenariat->addssmenu(new menu("Partenaires","caviste.php"));
$partenariat->addssmenu(new menu("Codes Partenaire","code_caviste.php"));
$partenariat->addssmenu(new menu("Espace liens","liens.php"));
$partenariat->addssmenu(new menu("Espace partenaires","partenaires.php"));
$partenariat->addssmenu(new menu("CRM","crm.php"));
print $partenariat->draw();

$fournisseur=new menu("Fournisseurs");
$fournisseur->position=$position;
$fournisseur->directory="/admin";
$fournisseur->addssmenu(new menu("Fournisseurs","fournisseurs_liste.php"));
$fournisseur->addssmenu(new menu("Attribuer frn/marque","fournisseurs_liste.php?action=marques"));
$fournisseur->addssmenu(new menu("Frais port SLS","sls.php"));
print $fournisseur->draw();

$pays=new menu("Pays");
$pays->position=$position;
$pays->directory="/admin";
$pays->addssmenu(new menu("Frais de port","fraisport.php"));
$pays->addssmenu(new menu("Taux TVA","tva.php"));
$pays->addssmenu(new menu("Franco de port","fraisport2.php"));
$pays->addssmenu(new menu("Traductions","traduction.php"));
print $pays->draw();

$compta=new menu("Comptabilité");
$compta->position=$position;
$compta->directory="/admin";
$compta->addssmenu(new menu("Factures fournisseur","fournisseur.php"));
$compta->addssmenu(new menu("Factures frais généraux","fournisseur_frais.php"));
$compta->addssmenu(new menu("Avoir fournisseur","avoir_fournisseur.php"));
$compta->addssmenu(new menu("Codes analytiques","code_analytique.php"));
print $compta->draw();

$stats=new menu("Stats");
$stats->position=$position;
$stats->directory="/admin";
$stats->addssmenu(new menu("Chiffre d'affaire facturé","chiffre.php"));
$stats->addssmenu(new menu("Stats articles","stat_articles.php"));
$stats->addssmenu(new menu("Stats commandes","stat_cmd.php"));
$stats->addssmenu(new menu("Stats CA","stat_ca.php"));
$stats->addssmenu(new menu("Stats clients perdus","causeperte.php"));
print $stats->draw();

print $accueil->js();

?>

</body>