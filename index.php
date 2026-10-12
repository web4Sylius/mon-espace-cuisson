<?php

include_once('global.php');

//Instanciations
$oMarque  = new marque($DB_site);
$oArticle = new DAOarticle();

$aParams = array('active' => true, 'tri' => 'libelle');
$aLstMarque = $oMarque->getListe($aParams); //Liste des marques actives
$aLstCDC    = $oArticle->getCDC($DB_site,$template->site_code(),$sitepro); //Liste des articles coups de coeur

//Paramétrage des META référencement de la page
if($sitepro)
{	
	$title_trad = '/index/meta_title_pro';
	$title_desc = '/index/meta_description_pro';
	$template->parts["follow"]='<meta NAME="ROBOTS" CONTENT="INDEX, FOLLOW, ALL">';
}
else
{
	$title_trad = '/index/meta_title';
	$title_desc = '/index/meta_description';
}

$template->pageparams($host, $template->translate($title_trad), $template->translate($title_desc));
$template->parts['css_page'] = "<link href=\"http://localhost/mec/css/css-slider.min.css\" rel=\"stylesheet\" type=\"text/css\">\n";
$template->parts['js_page']="<script src=\"http://localhost/mec/javascripts/slider.min.js\" defer></script>";

//Mise en place du slider
//************************************/
/* AUTOMATISATION DES SLIDES : TODO */
/************************************/
$i=0;
$classid=1;
$btns=$arrows=array();
$firstslide=1;
$ap=$DB_site->query("SELECT * FROM slider WHERE slidername='index' AND visible=1 AND site_code='".$template->site_code()."' AND langue='".$template->langue."' AND MID(CONV(HEX(pro),16,2),".($sitepro?-2:-1).",1)=1 ORDER BY position");
while($apim=$DB_site->fetch_array($ap)){
	$dbt = $apim['date_dbt'];
	$fin = $apim['date_fin'];
	if( ($dbt==0 && $fin==0) || (time() >= $dbt && time() <= $fin) ){
		if($firstslide==1){
			$im="<img data-u=\"image\" src=\"/images/autopromo/".$apim['id'].".jpg?t=".time()."\" class=\"autopromo\" alt=\"$apim[texte]\">";
			$firstslide=0;
		} else {
			$im="<img data-u=\"image\" data-src=\"/images/autopromo/".$apim['id'].".jpg?t=".time()."\" class=\"lazy autopromo\" alt=\"$apim[texte]\">";
		}
		if($apim['lien']!="") $im="<a href=\"$apim[lien]\">".$im."</a>";
		if($apim['artid']!=0) $im=slider($apim['artid']);

		$template->parts["autopromoimages"].=<<<HTML
		<div>
			$im
		</div>
HTML;
		$i++;
	}
}

$template->evaltemplate("IndexSlider","slider");

//Mise en place d'une liste de choix multiples de marques
$template->parts['listemarques'] = '';
if(!empty($aLstMarque)){
    foreach($aLstMarque as $la_marque){
        $template->parts['listemarques'] .= "<option value=\"".$la_marque['marqueid']."\">".$la_marque['libelle']."</option>";
    }
}

$liste_marque_homes=$DB_site->query("SELECT marqueid FROM marque WHERE menu_home='1' ORDER BY RAND() ");
while($liste_marque_home=$DB_site->fetch_array($liste_marque_homes)){
	$marqueid=$liste_marque_home['marqueid'];
    $marque=new marque($DB_site,$marqueid,$template->langue);
    $template->parts["marques_slides"].=<<<HTML
    <div class="slide">
      <a href="$marque->link"><img data-u="image" alt="$marque->type" class="lazy" src="/marques/{$marqueid}_100_100.jpg" width="100" height="100"></a>
    </div>
HTML;
}

foreach((array) $aLstCDC as $le_cdc){
	vignette($le_cdc['artid'],"selectionslide");
}


//Affichage des blocs à gauche et à droite du slider


$template->evaltemplate("IndexIdeeCadeau", "blocideecadeau");
$template->evaltemplate("IndexAide","blocaide");
$template->evaltemplate("IndexProjet", "blocprojet");
$template->evaltemplate("IndexAccessoires","blocaccessoires");

if($vfarticle['id']!="")
  $template->evaltemplate("IndexVF","blocselection");
else
  $template->evaltemplate("IndexSelection","blocselection");

$template->evaltemplate("IndexMarques","blocmarques");
$template->evaltemplate("IndexSelectionSlide","blocselectionslide");
/*
$template->parts["criteo"]=<<<JS
	<script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>
	<script type="text/javascript">
	window.criteo_q = window.criteo_q || [];
	window.criteo_q.push(
	{ event: "setAccount", account: 2995 },
	{ event: "setEmail", email: ["$user_info[mail]"] },
	{ event: "setSiteType", type: "d" },
	{ event: "viewHome" }
	);
	</script>

JS;
*/
$liens=$DB_site->query("SELECT lien,libelle,script FROM liens WHERE encarttexte='1' ");
$encarttexte="";
if ($DB_site->num_rows($liens)) {
    while($lien=$DB_site->fetch_array($liens)) {
	if($lien['script']!="")
	    $encarttexte.=$lien['script']." | ";
	else
	    $encarttexte.="<a target=\"_blank\" href=\"".htmlentities($lien[lien],ENT_COMPAT,"ISO-8859-15")."\" title=\"$lien[libelle]\">$lien[libelle]</a> | ";
    }
    $template->parts["encarttexte"]=substr($encarttexte,0,strlen($encarttexte)-3);
}

$encartimage="";
$liens=$DB_site->query("SELECT lien,libelle,logo,lienid FROM liens WHERE encartimage='1' ORDER BY RAND() LIMIT 6");
if ($DB_site->num_rows($liens)) {
    $i=0;
    $TemplatePartenairesIndexBit="";
    while($lien=$DB_site->fetch_array($liens)) {
	$i++;
	$encartimage.="<td onmouseover=\"mouseoverscroll=true;\" onmouseout=\"mouseoverscroll=false;\"><a target=\"_blank\" href=\"".htmlentities($lien[lien],ENT_COMPAT,"ISO-8859-15")."\" title=\"$lien[libelle]\"><img src=\"/images/liens/$lien[lienid].$lien[logo]\" alt=\"$lien[libelle]\"></a></td>";
    }
    $scrolllen=$i*150;
}
$template->parts["encartimage"]="<table id=\"scrollertbl\"><tr>".$encartimage."</tr></table>";

$template->parts["remarketing"]=google_remarketing('',"home",'');

/*if(ESPACE == 'prod'){
	//Tags de retargeting Nextperformance
	$template->parts["nextperformance"] = "<script type=\"text/javascript\" src=\"https://nxtck.com/act.php?tag=43955\" async></script>";
}*/

$template->evaltemplate("srcCave","srccave");
$template->parts["conseilclass"]=" vign316";
$template->evaltemplate("Conseil","conseil");
$template->evaltemplate("Garanties","garanties");

include("barrefixe.php");

if(!$template->mobile){
    foreach($template->sites() as $site_code=>$site){
	foreach($site['hosts'] as $url=>$deflangue){
	    if($site['actif'][$url])
		$template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url."\">\n";
	}
    }
}

$template->evaltemplate("Footer1","footer1");

// $template->evaltemplate("IndexBlocClients","clients");
if(isset($user_info['userid'])){
	$template->parts['lienBlocNewsletter'] = "javascript:void(0);";
	$template->parts['lienBlocNewsletterJS'] ="onclick=\"document.location='/client.php?mail_nl='+document.getElementById('mail_nl').value;\"";
}else{
	$template->parts['lienBlocNewsletter'] = "javascript:void(0);";
	$template->parts['lienBlocNewsletterJS'] ="onclick=\"document.location='/client.php?mail_nl='+document.getElementById('mail_nl').value;\"";

}


$template->parts['paiement_accueil'] = 'style="display: block;"';
$template->evaltemplate("IndexBlocFevad","fevad");

$template->parts['fevadproshow'] = 'fevadprohide';
$template->evaltemplate("IndexBlocNewsletter","newsletter");
$template->evaltemplate("IndexBlocContact","contact");
$template->evaltemplate("IndexBlocContactResp","contactResp");
$template->evaltemplate("IndexBlocCheque","cheques");
$template->evaltemplate("IndexBlocDelivery","worldwidedelivery");
$template->evaltemplate("IndexBlocContact","fab_fr");
$template->evaltemplate("IndexBlocSelection","selection");
$template->parts["add_style"]="padding:0;";

$template->evaltemplate("IndexBlocPaiement","paiements");
$template->evaltemplate("IndexBlocMarques","encartmarques");
$template->evaltemplate("IndexBlocConseils","blocconseils");
$template->evaltemplate("FooterPub","footer_pub");

$template->parts["nextperformance"] = "<script type=\"text/javascript\" src=\"https://nxtck.com/act.php?tag=43955\" async></script>";

$template->evaltemplate("IndexPage","main");

$template->out();
