        <nav class="sb-topnav navbar navbar-expand navbar-light bg-light">
            <!-- Navbar Brand-->
            <a class="navbar-brand ps-3" href="/admin/index.php">Mon Espace Cuisson</a>
            <!-- Sidebar Toggle-->
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <!-- Navbar Search-->
			<form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div class="input-group">
				    <!--
                    <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                    <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
					-->
                </div>
            </form>
            <!-- Navbar-->
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
						<li><a class="dropdown-item"><?php print $user_info['nom_contact']; ?></a></li>
						<li><hr class="dropdown-divider" /></li>
                        <li><a class="dropdown-item" href="/admin/logout.php">Déconnexion</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
						
						
						
						
						
<?php
// include("menu.class.php");
$position=0;

//Récupération de tous les commerciaux actifs dans les 30 derniers jours
$sql =  "  SELECT commercial FROM utilisateur WHERE commercial = '".$user_info['username']."'";
$sql .= "  AND TO_DAYS(NOW()) - TO_DAYS(dateinscription) <= 30"; // 900 ?
$sql .= "  GROUP BY commercial ORDER BY commercial";
$commercial = $DB_site2->query_first($sql);

function showmenu($menui,$menuname,$menutmp){
	global $user_info;
	$total_acces=false;
	if($user_info['admin_access']==""){
		$total_acces=true;
	}
	$droits_acces=explode(",",$user_info['admin_access']);
	
	$accesmenu=0;
	foreach($menutmp as $key => $val) {
		if($total_acces || in_array($key, $droits_acces) ){
			$accesmenu++;
		}
	}
	if($accesmenu>0){
		?>
								<a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapse<?= $menui ?>" aria-expanded="false" aria-controls="collapse<?= $menui ?>">
									<div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
									<?= $menuname ?>
									<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
								</a>
								<div class="collapse" id="collapse<?= $menui ?>" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
									<nav class="sb-sidenav-menu-nested nav">
		<?php
	}
	foreach($menutmp as $key => $val) {
		if($total_acces || in_array($key, $droits_acces) ){
			echo "<a class=\"nav-link\" href=\"".$key."\">".$val."</a>\n";
		}
	}
	if($accesmenu>0){
		echo "</nav></div>";
	}
}
?>						
						
						
						
						
                            <div class="sb-sidenav-menu-heading">Menu</div>
                            <a class="nav-link" href="index.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Accueil
                            </a>
<?php


showmenu(1,"Paramètres",array(
				"devises.php"=>"Devises",
				"moyens_paiement.php"=>"Moyens de paiement",
				"tva.php"=>"Taux TVA",
				"fraisport2.php"=>"Franco de port",
			));
showmenu(2,"Front Office",array(
				"emails_clients.php"=>"Gestion emails",
				"fournisseur_livraison.php"=>"Infos livraison transporteurs",
				"menu_gestion.php"=>"Gestion menu",
				"conseil.php"=>"Conseils d'experts",
				"institutionnel.php"=>"Pages institutionnelles",
				"page_faq.php"=>"FAQ",
				"menu_footer.php"=>"Menu Footer",
				"showroom.php"=>"Showroom",
				"presse.php"=>"Dans la presse"
			));
showmenu(3,"Traduction",array(
				"traduction.php"=>"Traductions",
				"traduction_liste.php"=>"Traductions à faire",
				"gammes.php"=>"Gammes",
				"gammes.php?field=libelle"=>"Libellés"
			));
showmenu(4,"Fournisseurs",array(
				"fournisseurs_liste.php"=>"Fournisseurs",
				"fournisseurs_liste.php?action=marques"=>"Attribuer frn/marque",
				"garanties.php"=>"Garanties",
				"save_pjmail.php"=>"Signatures Fournisseurs",
				"rubriques/livraison_type/index.php"=>"Types de livraison"
			));
showmenu(5,"Catalogue",array(
				"catalogue.php"=>"Catalogue",
				"marques.php"=>"Marques",
				"caracteristique.php"=>"Options",
				"modif_groupe.php"=>"Modification groupée",
				"gestion_video.php"=>"Gestion vidéo",
				"icones.php"=>"Icones"
			));
showmenu(6,"Tarif",array(
				"gestion_prix.php"=>"Gestion des prix",
				"verif_ppi.php"=>"Verification des PPI",
				"offre_fournisseur.php"=>"Offres achat fournisseur",
				"prix_temporaires.php"=>"Prix vente temporaires",
				"suivi_tarifs.php"=>"Suivis des tarifs",
				"rubriques/port_installateur/index.php"=>"PA livraison confort",
				"sls.php"=>"PV livraison confort",
				"montage.php"=>"Frais montage",
				"fraisport.php"=>"PA et PV livraison éco",
				"tnt_grille.php"=>"Tarifs FEDEX Express"
			));
showmenu(7,"Commercial",array(
				"utilisateur.php"=>"Fiches client",
				"utilisateur.php?action=new"=>"Nouveau contact",
				"projets_new.php"=>"Projets",
				"projets_new.php?commercial=".$user_info['username'] =>"Suivi Projets",
				"cadeau.php"=>"Bons de réduction",
				"contacts.php"=>"Historique contacts",
				"contacts_leads.php"=>"Historique contacts campagne",
				"geolocalisation.php"=>"Géolocalisation",
				"visiteur.php"=>"Visiteurs",
				"statconsent.php"=>"Stat RGPD",
				"historique_config.php"=>"Historique config"
			));
showmenu(8,"Stats commerciales",array(
				"chiffre.php"=>"Chiffre d'affaires facturé",
				"chiffre_enregistre.php"=>"Chiffre d'affaires enregistré",
				"commande_attribution.php"=>"Commandes à attribuer",
				"stats_origine_ca.php"=>"Stats origine ventes",
				"stats_commerciaux.php"=>"Stats commerciaux",
				"chiffre_export.php"=>"Chiffre d'affaires export",
				"chiffre_pro.php"=>"Chiffre d'affaires pro/part",
				"stat_articles.php"=>"Stats articles vendus",
				"stat_ca.php"=>"Stats CA",
				"stats_objectifs_commerciaux.php"=>"Stats obj commercial",
				"recapfacture.php"=>"Analyse marge commandes"
			));
showmenu(9,"Commande",array(
				"facture.php"=>"Commandes site",
				"marketplace/index.php"=>"Market place",
				"suivi_adv.php?nocmd=1"=>"Suivi des cde à traiter",
				"suivi_adv.php"=>"Suivi des expéditions",
				"suivi_adv.php?tdate=outdate"=>"Suivi des livraisons",
				"suivi_ar.php"=>"Suivi des AR",
				"douane_adv.php"=>"Suivi documents export"
			));
showmenu(10,"Stock",array(
				"stock.php"=>"Gestion du stocks",
				"stock.php?action=listcmd"=>"Liste des commandes",
				"stock.php?action=suivi"=>"Suivi",
				"stock_ajust.php"=>"Inventaire du stock",
				"stock.php?action=transfert"=>"Transfert de Stock"
			));
showmenu(11,"Qualité SAV",array(
				"facture.php?sav=1"=>"Commandes SAV",
				"script_sav_liste.php"=>"Liste SAV rang 2++",
				"liste_sfg.php"=>"Garanties SFG",
				"sav_global.php"=>"Suivi global",
				"sav_config.php"=>"Menus/étapes/résumés"
			));
showmenu(12,"Marketing",array(
				"cdc_index.php"=>"Coup de coeur accueil",
				"landing.php"=>"Landing pages",
				"slider.php"=>"Sliders",
				"style.php"=>"Fond d'écran",
				"logopromo.php"=>"Pages promo et kdo",
				"vente_flash.php"=>"Vente flash",
				"vente_privee.php"=>"Vente privée",
				"gallery.php"=>"Galerie photos",
				"encarts.php"=>"Encarts",
				"depense_marketing.php"=>"Dépenses marketing",
				"export_adcom.php"=>"Export Adcom",
				"export_mailing.php"=>"Export mailing &amp; SMS",
				"newsletter_email.php"=>"Désinscription newsletter",
				"etude_ppi.php"=>"Etude de PPI",
				"tags_leads.php"=>"Tags des leads",
				"avis_clients.php"=>"Avis clients"
			));
showmenu(13,"Stats Marketing",array(
				"stats_ca_objectifs.php"=>"CA sur objectifs",
				"marge_articles.php"=>"Etudes de marges",
				"stats_inscription.php"=>"Stats inscriptions",
				"stat_cmd.php"=>"Stats cde enregistrées",
				"stat_reduction_gamme.php"=>"Stats réduction gamme",
				"causeperte.php"=>"Stats clients perdus",
				"stat_recherches.php"=>"Stats recherches effectuées",
				"stats_export.php"=>"Exports CSV"
			));
showmenu(14,"Comptabilité",array(
				"fournisseur.php"=>"Factures fournisseur",
				"fournisseur_frais.php"=>"Factures frais généraux",
				"remise_cheque.php"=>"Remise de chèques",
				"avoir_fournisseur.php"=>"Avoir fournisseur",
				"code_analytique.php"=>"Codes analytiques",
				"comptes_charge.php"=>"Comptes de charge",
				"sav_fraude.php"=>"Suivi fraude",
				"deb_export.php"=>"DEB export",
				"deb_import.php"=>"DEB import",
				"douane.php"=>"Douanes",
				"compta_sofinco.php"=>"SOFINCO",
				"jour_ferie.php"=>"RH - jours fériés",
				"absence.php"=>"RH - absences",
				"objectifs_commerciaux.php"=>"RH - objectifs commerciaux",
				"verif_offres_com.php"=>"Verif offres commerciales",
				"taxe_eco.php"=>"Taxe écologique"
			));
showmenu(15,"Informatique",array(
				"task.php"=>"Suivi interne",
				"erreurs.php"=>"Logs d'erreurs",
				"cloudflare.php"=>"Cache"
			));
showmenu(16,"Direction",array(
				"users.php"=>"Administrateurs",
				"export/export.php"=>"Export BDD articles - Global",
				"export/export.php?actifs=1"=>"Export BDD articles - Actifs",
				"export/clients.php?complet=1"=>"Export Liste clients",
				"export/export_frais_transport.php"=>"Export transports"
			));

if($aaccess==false || in_array("mailbox.php",$aaccess)){
    //include("mailbox.php"); 2025
}

?>
							<div id="rappels"></div>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
						
                        <div class="small">
							Mon Espace Cuisson<br>
							150 rue Paul Bert<br>
							69003 LYON<br>
							Tél : 04 81 91 90 40
						</div>
                    </div>
                </nav>
            </div>   
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
<?php
// print $m_accueil->js();

$userRappel = '';
if(isset($_GET['user_rappel'])){
    $userRappel = "?user_rappel=".$_GET['user_rappel'];
}
?>
<script type="text/javascript">
    //livestat.php : Charge les stats des visites courantes sur le site, dans le menu de gauche
    //auto_rappel.php : Charge la liste des différents rappels dans le menu de gauche
    function livestat(){
        ajaxrequest("./auto_rappel.php<?php echo $userRappel; ?>", rappelcb);
    }
    function rappelcb(httprequest){
        document.getElementById('rappels').innerHTML = httprequest.responseText;
        // 	if(document.getElementById('alert').innerHTML!="") alert("<b>alert</b>");
        var al = document.getElementById('alert');
        
        if(al && al.innerHTML != ""){
            var bg = document.getElementById('alertbg');
            bg.style.left    = '0px';
            bg.style.top     = '0px';
            bg.style.height  = document.body.offsetHeight+'px';
            bg.style.display = 'block';

            al.style.left    = (document.body.clientWidth/2-150)+'px';
            al.style.top     = ((document.body.clientHeight-al.offsetHeight)/2+document.body.scrollTop)+'px';
            al.style.display = 'block';
        }
    }
    function rappelcmd_cb(httprequest){
        document.getElementById('rappels_cmd').innerHTML = httprequest.responseText;
    }
    function rappelhide(id,type,page=''){
        if(type==undefined) type="";
        //if(!confirm("Confirmez-vous la suppression de ce rappel ?")) return;
        if(page != ''){
            if(page == 'rappels_cmd'){
                ajaxrequest("./auto_rappel.php?hide"+type+"="+id, rappelcb);
            }
        } else {
            ajaxrequest("./auto_rappel.php?hide"+type+"="+id, rappelcb);
        }
    }
    function alerthide(){
		document.getElementById('alertbg').style.display='none';
		document.getElementById('alert').style.display='none';
    }
    //Affichage stats de visite site + rappels du menu de gauche, au chargement de la page
    $jQuery(document).ready(function(){
        livestat();
    });
<?php
//Mise à jour des stats et suivis toutes les 30 secondes
// if($user_info['username'] == "cc") print "setInterval(\"livestat()\",30000);";
?>
</script>
<br /><br />
<?php
// <EMBED id="embedsound" autostart="true" loop="false" volume="100" width="1" height="1">
?>
<script language="javascript">
  function closespy(){
      document.getElementById('spysrcdiv').style.display='none';
      document.getElementById('spylink').style.display='block';
      DeleteCookie('spysrc');
  }
  function showspy(){
      document.getElementById('spysrcdiv').style.display='block';
      document.getElementById('spylink').style.display='none';
  }
  function linksrc(srctxt){
      document.getElementsByName('spysrc')[0].value=srctxt;
      document.forms.spysrcform.submit();
  }
  function spyajaxsrc(txt){
      document.getElementById('spyresult').innerHTML="";
      if(txt.length<=3) return;
      var url="marketplace/src.php?txt="+txt;
      ajaxrequest(url,spy_src_cb);
  }
  function spy_src_cb(httprequest){
    var xml=httprequest.responseXML.documentElement;
      var html='';

      var vistxt=new Array();
      vistxt[0]='<br><span style=\"color:red;\">Invisible</span>';
      vistxt[1]='<br>Visible';
      if(xml.getElementsByTagName('prod')[0]){
	  html="<table>";
	  for(i=0;i<xml.getElementsByTagName('prod').length;i++){
	      var id=xml.getElementsByTagName('prod')[i].getAttribute('id');
	      var artid=xml.getElementsByTagName('prod')[i].getAttribute('artid');
	      html+="<tr><td><img src=\"/configurations/www.mon-espace-cuisson.com/images/produits/br/"+artid+".jpg\" width=\"50\"></td><td>"+id+"<br><a href=\"javascript:select_spy('"+id+"')\">Sélectionner</a></td></tr>";
	  }
	  html+="</table>";
      }else
	  html="Aucun article trouvé";
      document.getElementById('spyresult').innerHTML=html;
  }
  function select_spy(artcode){
      document.getElementsByName('spysrc')[0].value=artcode;
      document.forms.spysrcform.submit();
  }
  function getCookieVal(offset) {
    var endstr=document.cookie.indexOf (";", offset);
    if (endstr==-1)
      endstr=document.cookie.length;
    return unescape(document.cookie.substring(offset, endstr));
  }
  function GetCookie (name) {
    var arg=name+"=";
    var alen=arg.length;
    var clen=document.cookie.length;
    var i=0;
    while (i<clen) {
      var j=i+alen;
      if (document.cookie.substring(i, j)==arg)
	return getCookieVal (j);
      i=document.cookie.indexOf(" ",i)+1;
      if (i==0) break;
    }
    return null;
  }
  function DeleteCookie (name) {
    var exp=new Date();
    exp.setTime (exp.getTime() - 100000);
    var cval=GetCookie (name);
    document.cookie=name+"="+cval+"; expires="+exp.toGMTString();
  }

  if(GetCookie("spysrc")!=null){
      document.getElementById('spysrcdiv').style.display='block';
      document.getElementById('spylink').style.display='none';
  }
</script>
<script src="./../ckeditor/ckeditor.js"></script>
