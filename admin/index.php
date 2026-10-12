<?php

ini_set("display_errors","1");
error_reporting(E_ALL);

require "admin_global.php";
include "./header.php";
include "./menu.php";
include_once($rootpath."class/DAOarticle.class.php");
include_once($rootpath."class/article.class.php");
include_once($rootpath."class/fonctions.php");
include_once($rootpath."class/stock.class.php");

// $adminCA = array("philippe", "benoit", "alexis", "thibault", "cc"); // plus utilisé
$adminMP = array("philippe", "benoit", "adv", "qualite", "cc", "ariel","jessica","jbandre");
$adminCommercial = array("dylan","alexis", "philippe", "benoit", "cc", "ariel","jessica","jbandre");
$adminMarketing = array("morgane", "nia","benoit","ariel","cc","philippe","jessica","jbandre");

print "<style>
a.button_ca { 
    text-decoration: none;
    background-color: #363E42;
    color: white;
    border-radius: 2px;
    padding: 5px 8px;
    font-weight: bold;
    margin: -18px -4px 0px 0px;
}

a.button_ca:hover {
    background-color: #AA1147;
}
</style>";

print "<script language=\"javascript\">
function showCA(){
    document.getElementById('actionCA').setAttribute('onclick', 'hideCA();');
    document.getElementById('actionCA').innerHTML = 'Cacher CA';
    document.getElementById('ica_tab').style.display = '';
    document.getElementById('ica_tabf').style.display = '';
}
function hideCA(){
    document.getElementById('actionCA').setAttribute('onclick', 'showCA();');
    document.getElementById('actionCA').innerHTML = 'Afficher CA';
    document.getElementById('ica_tab').style.display = 'none';
    document.getElementById('ica_tabf').style.display = 'none';
}
window.localStorage.removeItem('gclid');
</script>";

if (isset($parentid) && $parentid=="")
    {
    $parentid=0;
    }

$i=0;

$test_req = microtime(true);

if ( in_array( "4",$modules ) ){
           $etat_stocks=$DB_site2->query(" SELECT distinct(article.artid) FROM article
                                    LEFT JOIN stocks USING(artid)
                                    LEFT JOIN stock USING(artid)
                                    LEFT JOIN stocks as cs ON(stocks.stockid = cs.parentid) 
                                    WHERE (cs.stockid <=> NULL AND stocks.total < stock.seuil)
                                    OR (stocks.stockid <=> NULL) 
                                    AND article.artid NOT IN (SELECT article_lie FROM liens_articles)");
    //}
    $etat_stocks=$DB_site2->num_rows($etat_stocks);
    if ($etat_stocks=="0")
        $etat_stocks="Aucun produit en dessous du seuil d'alerte.";
    else
        $etat_stocks=$template->translate("/admin/index/il_y_a")."<a href=\"recapstocks.php\">$etat_stocks produit(s)</a> en dessous du seuil d'alerte";
    $etat_stocks="<tr><td class=\"td_modules_boutiques\"><strong>Etat de mes stocks</strong></td><td class=\"td_modules_boutiques\">$etat_stocks</td></tr>";
}

$test_end_req = number_format(microtime(true)-$test_req, 2, ',', '');

//echo '<!-- Stock Query In '.$test_end_req.' (s) -- Query ('."SELECT article.artid FROM article LEFT JOIN stocks USING(artid) LEFT JOIN stock USING(artid) LEFT JOIN stocks as cs ON(stocks.stockid = cs.parentid) WHERE (cs.stockid <=> NULL AND stocks.total < stock.seuil) OR (stocks.stockid <=> NULL) AND article.catid IN(".implode(',',$result_array).") GROUP BY article.artid".') -->';

if( in_array( "21", $modules ) )
{
    // Module Coups de coeur
    $nb_coupsdecoeur = $DB_site2->query_first( "select count(*) from coupdecoeur" );
    if( $nb_coupsdecoeur[0] == "0" )
        $coupsdecoeur = "Aucun coup de coeur";
    else                          
        $coupsdecoeur = $template->translate("/admin/index/il_y_a")."<a href=\"alerte_coupdecoeur.php\">$nb_coupsdecoeur[0] coup(s) de coeur</a>";
    $coupsdecoeur = "<tr><td class=\"td_modules_boutiques\"><strong>Coups de coeur</strong></td><td class=\"td_modules_boutiques\">$coupsdecoeur</td></tr>";
}

if( in_array( "5", $modules ) )
{
    // Module Promotions
    $nb_promotions = $DB_site2->query_first( "select count(*) from promo WHERE dbt_promo=0 OR ".time()." BETWEEN dbt_promo AND fin_promo" );
    if( $nb_promotions[0] == "0" )
        $promotions = "Aucune promotion en cours";
    else                          
        $promotions = $template->translate("/admin/index/il_y_a")."<a href=\"alerte_promo.php\">$nb_promotions[0] promotion(s)</a> en cours";
    $promotions = "<tr><td class=\"td_modules_boutiques\"><strong>Promotions</strong></td><td class=\"td_modules_boutiques\">$promotions</td></tr>";
}

if( in_array( "3", $modules ) )
{
    // Module Remises
    $nb_remises = $DB_site2->query_first("select count(*) as nb from remise where actif = '1'");
    if ($nb_remises['nb'] == 0)
        {
        $liste_remise = "Aucune remise sur le catalogue";
        }
    elseif ($nb_remises['nb'] == 1)
        {
        $liste_remise = $template->translate("/admin/index/il_y_a")."<a href=\"remise.php\">$nb_remises[nb] remise</a> sur le catalogue";
        }
    else
        {
        $liste_remise = $template->translate("/admin/index/il_y_a")."<a href=\"remise.php\">$nb_remises[nb] remises</a> sur le catalogue";
        }
    $liste_remises = "<tr><td class=\"td_modules_boutiques\"><strong>Remises de gros</strong></td><td class=\"td_modules_boutiques\">$liste_remise</td></tr>";
}

$en_cours_crea=$DB_site2->query_first("SELECT COUNT(*) FROM article WHERE active='6'");
$a_controler=$DB_site2->query_first("SELECT COUNT(*) FROM article WHERE active='4'");
$actif=$DB_site2->query_first("SELECT COUNT(*) FROM article WHERE active='1'");
$inactif=$DB_site2->query_first("SELECT COUNT(*) FROM article WHERE active='0'");
$supprime=$DB_site2->query_first("SELECT COUNT(*) FROM article WHERE active='2'");
$archiv_temp_avc=$DB_site2->query_first("SELECT COUNT(*) FROM article_visible a_v INNER JOIN article a ON a.artid = a_v.artid WHERE a.active = '5' AND date>UNIX_TIMESTAMP(NOW())");
$archiv_temp_ss=$DB_site2->query_first("SELECT COUNT(*) FROM article a WHERE a.active='5' AND not exists (select a_v.artid from article_visible a_v where a.artid = a_v.artid)");
$archiv_def=$DB_site2->query_first("SELECT COUNT(*) FROM article WHERE active='3'");
$prixsurdemande=$DB_site2->query_first("SELECT COUNT(*) FROM article WHERE commandable='0' AND active IN ('1')");
$noncommandable=$DB_site2->query_first("SELECT COUNT(*) FROM article WHERE commandable='2' AND active IN ('1')");
$nbreappro=$DB_site2->query_first("SELECT COUNT(*) FROM article WHERE date_appro>".time());
$nbcdc=$DB_site2->query_first("SELECT COUNT(*) FROM coupdecoeur");
$nbpromo=$DB_site2->query_first("SELECT COUNT(*) FROM promo WHERE dbt_promo=0 OR ".time()." BETWEEN dbt_promo AND fin_promo");
$nbidkdos=$DB_site2->query_first("SELECT COUNT(*) FROM idkdos");

if($aaccess==false || in_array("catalogue.php",$aaccess)) $invisibles="
<tr><td><img src=\"images/en_cours.png\" title=\"En cours de création sur site \" width=\"30\"></td><td class=\"td_modules_boutiques\"><b>".ucfirst($template->translate("/admin/index/en_cours_crea"))."</b></td><td>".$template->translate("/admin/index/il_y_a")." <a href=\"rechercher.php?active[]=6\">$en_cours_crea[0] ".$template->translate("/admin/index/produits")."</a></td></tr>
<tr><td><img src=\"images/new.png\" title=\"Nouveau à contrôler\" width=\"30\"></td><td class=\"td_modules_boutiques\"><b>".ucfirst($template->translate("/admin/index/a_controler"))."</b></td><td>".$template->translate("/admin/index/il_y_a")." <a href=\"rechercher.php?active[]=4\">$a_controler[0] ".$template->translate("/admin/index/produits")."</a></td></tr>

<tr><td><img src=\"images/visible.png\" title=\"Visible sur site $site_code\" width=\"30\"></td><td class=\"td_modules_boutiques\"><b>".ucfirst($template->translate("/admin/index/actifs"))." </b></td><td>".$template->translate("/admin/index/il_y_a")."  <a href=\"rechercher.php?active[]=1\">$actif[0] ".$template->translate("/admin/index/produits")."</a></td></tr>
<tr><td><img src=\"images/invisible.png\" title=\"Invisible sur site $site_code\" width=\"30\"></td><td class=\"td_modules_boutiques\"><b>".ucfirst($template->translate("/admin/index/inactifs"))."</b></td><td>".$template->translate("/admin/index/il_y_a")." <a href=\"rechercher.php?active[]=0\">$inactif[0] ".$template->translate("/admin/index/produits")."</a></td></tr>
<tr><td><img src=\"images/supprime.png\" title=\"Supprimé sur site $site_code\" width=\"30\"></td><td class=\"td_modules_boutiques\"><b>".ucfirst($template->translate("/admin/index/supprimes"))."</b></td><td>".$template->translate("/admin/index/il_y_a")." <a href=\"rechercher.php?active[]=2\">$supprime[0] ".$template->translate("/admin/index/produits")."</a></td></tr>
<tr><td><img src=\"images/archive_temp.png\" title=\"Archivé temporairement sur site $site_code\" width=\"30\"></td><td class=\"td_modules_boutiques\"><b>".ucfirst($template->translate("/admin/index/archive_temp_avec"))."</b></td><td>".$template->translate("/admin/index/il_y_a")." <a href=\"rechercher.php?active[]=5&datevisible=1\">$archiv_temp_avc[0] ".$template->translate("/admin/index/produits")."</a></td></tr>
<tr><td><img src=\"images/archive_temp.png\" title=\"Archivé temporairement sur site $site_code\" width=\"30\"></td><td class=\"td_modules_boutiques\"><b>".ucfirst($template->translate("/admin/index/archive_temp_sans"))."</b></td><td>".$template->translate("/admin/index/il_y_a")." <a href=\"rechercher.php?active[]=5&datevisible=0\">$archiv_temp_ss[0] ".$template->translate("/admin/index/produits")."</a></td></tr>
<tr><td><img src=\"images/archive.png\" title=\"Archivé définitivement sur site $site_code\" width=\"30\"></td><td class=\"td_modules_boutiques\"><b>".ucfirst($template->translate("/admin/index/archive_def"))."</b></td><td>".$template->translate("/admin/index/il_y_a")." <a href=\"rechercher.php?active[]=3\">$archiv_def[0] ".$template->translate("/admin/index/produits")."</a></td></tr>
<tr><td style=\"padding-top: 15px;\"></td><td></td><td></td></tr>
<tr><td></td><td class=\"td_modules_boutiques\"><b>".ucfirst($template->translate("/admin/index/prixsurdemande"))."</b></td><td>".$template->translate("/admin/index/il_y_a")." <a href=\"rechercher.php?commandable[]=0&active[]=1\">$prixsurdemande[0] ".$template->translate("/admin/index/produits")."</a></td></tr>
<tr><td></td><td class=\"td_modules_boutiques\"><b>".ucfirst($template->translate("/admin/index/non_commandables"))."</b></td><td>".$template->translate("/admin/index/il_y_a")." <a href=\"rechercher.php?commandable[]=2&active[]=1\">$noncommandable[0] ".$template->translate("/admin/index/produits")."</a></td></tr>
<tr><td style=\"padding-top: 15px;\"></td><td></td><td></td></tr>
<tr><td></td><td class=\"td_modules_boutiques\"><b>".ucfirst($template->translate("/admin/index/reappro"))."</b></td><td>".$template->translate("/admin/index/il_y_a")." <a href=\"rechercher.php?reappro=1\">$nbreappro[0] ".$template->translate("/admin/index/produits")."</a></td></tr>
<tr><td></td><td class=\"td_modules_boutiques\"><b>".ucfirst($template->translate("/admin/index/coup_coeur"))."</b></td><td>".$template->translate("/admin/index/il_y_a")." <a href=\"rechercher.php?cdc=1\">$nbcdc[0] ".$template->translate("/admin/index/produits")."</a></td></tr>
<tr><td></td><td class=\"td_modules_boutiques\"><b>".ucfirst($template->translate("/admin/index/promotions"))."</b></td><td>".$template->translate("/admin/index/il_y_a")." <a href=\"rechercher.php?promo=1\">$nbpromo[0] ".$template->translate("/admin/index/produits")."</a></td></tr>
<tr><td></td><td class=\"td_modules_boutiques\"><b>".ucfirst($template->translate("/admin/index/idees_cadeau"))."</b></td><td>".$template->translate("/admin/index/il_y_a")." <a href=\"rechercher.php?idkdos=1\">$nbidkdos[0] ".$template->translate("/admin/index/produits")."</a></td></tr>";




echo "<h1>Liste de mes alertes</h1>
      <center>
      <h3>".$template->translate("/admin/index/raccourci")."</h3>
      <table class=\"table_index\">
	  $etat_stocks
	  $invisibles
	  $liste_remises
      </table>
      </center>";

 if ($aaccess==false || in_array("facture.php",$aaccess)){
     
        $facturecount=$DB_site2->query_first("SELECT count(*) FROM facture WHERE valide = 0 and moyenid NOT IN (0,1)");
        $facturecount2=$DB_site2->query_first("SELECT count(*) FROM facture WHERE valide = 1");
        echo "<br><bR><center>
            <h3>".$template->translate("/admin/index/dernieres_factures")."</h3>
            ".$template->translate("/admin/index/il_y_a")." <a href=\"facture.php?lst_trietat=1\">$facturecount2[0]</a> ".$template->translate("/admin/index/factures_validees").".
        </center>" ;
        echo "<br><bR><center>
            <h3>".$template->translate("/admin/index/factures_validees")."</h3>
            ".$template->translate("/admin/index/il_y_a")." <a href=\"facture.php?lst_trietat=0\">$facturecount[0]</a> factures à traiter.
        </center>" ;


} 



// Affichage stats CA
/* mise en commentaire, car déplacé dans une autre page (stats_ca_objectifs.php)
if(in_array($user_info['username'],$adminCA)){
    echo "<a href=\"javascript:void(0)\" class=\"button_ca\" onclick=\"showCA()\" id=\"actionCA\"> Afficher CA</a>
    <br><br>";
    include "stats_index.php";
}
*/

if(in_array($user_info['username'],$adminMP)) //seulement pour l adv
{

?>
<div id='commande_marketplace'>
   <center>
           <h3><div id='listeMP' style='visibility:hidden'>Commandes Marketplace</div></h3> 
           <div id='commande_fnac'></div>
           <div id='commande_laredoute'></div>
           <div id='commande_amazon'></div>
           <div id='commande_cdiscount'></div>
           <div id='marketplaces'></div>
    </center>
<a href="javascript:void(0)" class="button_ca" onclick="ajaxReloadTri();" id="actionMP"> Afficher Market places</a>  
  <!-- ne rien mettre derrière ajaxReload('amazon') sinon bug --> 
</div>
<script type="text/javascript">


function ajaxReloadTri(){//pour chaque market place on va choisir la page vers laquelle on redirige + la fonction qui va choisir div ou l 'on va afficher le resultat
        document.getElementById('actionMP').style.visibility='hidden';
        document.getElementById('listeMP').style.visibility='visible'
        
        document.getElementById('marketplaces').innerHTML = "<img src=\"images/loader.gif\" style=\"width: 50px;\">";
        ajaxrequest("commande_fnac.php",ajaxmpcb, null, 1);
        ajaxrequest("commande_laredoute.php",ajaxmpcb, null, 1);
        ajaxrequest("commande_amazon.php",ajaxmpcb, null, 1);
        ajaxrequest("commande_cdiscount.php",ajaxmpcb, null, 1);
        ajaxrequest("commande_rdc.php",ajaxmpcb, null, 1);

}

function ajaxmpcb(httprequest){
        if(document.querySelector("#marketplaces img")) document.getElementById('marketplaces').innerHTML="";
        document.getElementById('marketplaces').innerHTML+=httprequest.responseText;
}

</script>
<?php
}
echo "<br>";
////////////*********************************************************************************************************///////////////////////////
/////**************************************** DEBUT DES AFFICHEGES DES ALERTES *************************************////////////////////////////
//////**************************************************************************************************************//////////////////////////
echo "<center><h2>ALERTES</h2></center><table id=\"alerteAccueilAdmin\">";
// on recherche les produits en page d'accueil qui sont désactivés ou délai de livraison trop élevé et on affiche une alerte sur accueil admin
$tab=array();
$DB_site = $DB_site2;
$livraison=false;
$active=false;
$cmd=false;
$oArticle = new DAOarticle();
//on rempli le tableau avec les articles en page d'accueil avec le site_code 'base' car pour l'instant on n'utilise que celui là et de sa version(pro/part)
$tab["base==>part"] =$oArticle->getCDC($DB_site2);
$tab["base==>pro"]=$oArticle->getCDC($DB_site2,'base',true);


$art = false;
$txt ="";
foreach($tab as $key => $aLstCDC){
    $site_code_version = explode("==>", $key);
    if(!is_null($aLstCDC)){
        foreach($aLstCDC as $le_cdc){//pour chaque article on va recuperer ses infos pour voir si il est actif
            $article = $DB_site2->query_first("SELECT a_s.active as active, a.artcode, a.commandable as cmd FROM article a INNER JOIN article_site as a_s USING(artid) WHERE a.artid=".$le_cdc['artid']." AND a_s.site_code='".$site_code_version[0]."' ");
            $delai_livraison = delailivraison($le_cdc['artid']); //on check la date de livraison
            if($article['active'] != '1' || $article['cmd'] != '1' || intval($delai_livraison['delailivraison']) >= 45){
                if ($article['active'] != '1')
                    $active=" est désactivé ";
                if (intval($delai_livraison['delailivraison']) >= 45)
                    $livraison = " le délai de livraison dépasse 90 jours ";
                if($article['cmd'] != '1')
                    $cmd = ", n'est pas commandable ";

                if(in_array($user_info['username'], $adminCommercial) || in_array($user_info['username'],$adminMarketing) ){
                if(!$art)
                    $art = true;
                    $txt .= "<p class=\"articlesAccueil\" style=\"color:red;text-align:center;font-size:14px;\">L'article <strong>".$article['artcode']."</strong> qui se trouve sur la page d'accueil du site ".$key." ".$active.(($livraison&&$active) ? ' et ':'').$livraison." ".$cmd.".<a href=\"".SITE_URL."/admin/cdc_index.php?site_code=$site_code_version[0]&version=$site_code_version[1]\">Remplacer</a></p>";
                }
               $active =false;
               $livraison=false;
               $cmd=false;
            }
        }
    }
}
if($art){
    echo '<tr id="articlesAccueilLigne"><td><h3>ARTICLES ACCUEIL</h3>';
    echo $txt ;
    echo '</td></tr>'; 
}


//alerte des produits où les dimensions brutes ne sont pas rensignées
 if(in_array($user_info['username'], $adminCommercial) || in_array($user_info['username'],$adminMP) ){
    $req=$DB_site2->query("SELECT DISTINCT artcode,artid,emballage_poids1,emballage_largeur1,emballage_hauteur1,emballage_profondeur1 FROM article INNER JOIN article_lot USING(artid) WHERE (emballage_hauteur1 = 0 OR emballage_largeur1 = 0 OR emballage_profondeur1=0 OR emballage_poids1 = 0) AND active='1' AND commandable='1'");
    if($DB_site2->num_rows($req)>0){
        echo '<tr><td><h3>DIMENSIONS BRUTES</h3>';
        echo "<p style=\"color:red;font-size:15px;\">Attention, des produits n'ont pas leurs dimensions brutes renseignées. <a href=\"liste_err_dimensions_brutes.php\">Cliquez-ici</a> pour les enregistrer.</p><br>";
        echo '</td></tr>';
    }
}



//alerte des produits archivés qui n'ont pas d'articles de remplacement. ATTENTION code pour les catégories 1 11 et 5
if(in_array($user_info['username'], $adminCommercial) || in_array($user_info['username'],$adminMP) ){
    echo '<tr><td><h3>PRODUITS DE REMPLACEMENT</h3>';
    $req = $DB_site2->query("SELECT DISTINCT a.artcode as artcode,a.artid as artid, a.libelleFR as libelle FROM article a INNER JOIN typearticle USING(typearticleid) WHERE a.typearticleid IN (1,11,5) AND a.active=\"3\" AND a.artid NOT IN (select artid FROM article_remplacement) ORDER BY a.catid");
    if($DB_site2->num_rows($req)>0){
        echo "<p style=\"color:red;font-size:15px;\">Attention, des produits archivés n'ont pas de produits de remplacement. <a href=\"liste_articles_sans_remplacement.php\">Cliquez-ici</a> pour les mettre à jour.</p><br>";
    }
    echo '</td></tr>';
}


// alerte si il y a moins que 4 sliders actifs sur les site FR , EN version pro et part
$slider = check_nb_slider_accueil();
if($slider !='') {
    echo '<tr><td><h3>SLIDER</h3>';
    echo $slider;
    echo '</td></tr>';
}

echo "</table>";

include "./footer.php";
?>


