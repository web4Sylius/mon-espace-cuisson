<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$typearticleid = $_GET['typearticleid'];

if(!isset($typearticleid) || $typearticleid == ''){
     exit;
}

//R?cup?ration des produits
$tabProduits=array();
$articles_comparateur=$comparateur->articles(true);
if(isset($articles_comparateur[$typearticleid])){
    foreach($articles_comparateur[$typearticleid] as $article_comparateur){
	
	$options=$DB_site->query_first("SELECT COUNT(*) FROM article_caractval WHERE artid=".$article_comparateur->artid);
	
	//R?cup?ration d?tail produits
	$article=$DB_site->query_first("SELECT * FROM article WHERE artid=".$article_comparateur->artid);
	$article_prix=article_prices($article_comparateur->artid,$template->tva_ref(),$template->site_code(),$sitepro);
	$promo=$DB_site->query_first("SELECT COUNT(*) FROM promo WHERE site_code='".$template->site_code()."' AND artid=".$article_comparateur->artid);
	$secondchoix=$DB_site->query_first("SELECT COUNT(*) FROM secondchoix WHERE artid=".$article_comparateur->artid);
	
	$article['options']=intval($options[0]);
	$article['prix']=$article_prix['prix'];
	$article['prixconseille']=$article_prix['prixconseille'];
	$article['remise']=$article_prix['remise'];
	$article['promo']=($promo[0]>0);
	$article['secondchoix']=($secondchoix[0]>0);
	
	$tabProduits[$article['prix']][] = $article;
    }
}
ksort($tabProduits); //Tri par prix croissant

$nbPdtsComparateur = count($articles_comparateur[$typearticleid]);

$template->parts["typearticleid"]=$typearticleid;

foreach($tabProduits as $articles){
    foreach($articles as $article){
	$mq=$DB_site->query_first("SELECT marqueid,type".$template->langue." AS mtype,libelle FROM marque m INNER JOIN article_marque am USING(marqueid) WHERE artid=".$article['artid']);
	$article['marque']=$mq['libelle'];
	$template->parts["artid"]=$article['artid'];
	$libelle=ucfirst(strtolower($article['libelle'.$template->langue]));
	$template->parts["libelle"]=Truncate($libelle,60,false);
	$template->parts["ref"]=$article['artcode'];
	$template->parts["logomarque"]="<img src=\"/marques/".$mq[marqueid]."_180_25.jpg\" class=\"marque\" alt=\"".$mq['mtype']."\">";
	
	if($article['commandable']==1){
            $template->parts["prix"]=$template->price_format($article['prix']);
            if($article['prix']!=$article['prixconseille'] && ($article['promo'] || $article['secondchoix']))
                $template->parts["prix"].="<span class=\"reduc\">".round(-$article['remise'])."%</span>";
        }else
            $template->parts["prix"]="&nbsp;";
	    
	// ajouter reduction
	$template->parts["url"]=$template->mkurl("article",$article['artid'],$article);
	$template->parts["addbasket"]="";
	if($article['options']==0 && $article['commandable']==1) $template->parts["addbasket"]="<a class=\"commander\" href=\"javascript:void(0)\" onclick=\"ajaxAddProduct2('[?artid]'); return false;\">".$template->translate("/comparateur/ajout_panier","F")."</a>";
	$template->evaltemplate("ComparateurArticle","blocproduits");

	
	$template->parts["titre_critere"].="<td class=\"titre_critere\">&nbsp;</td>";
	$template->parts["capacite"].="<td>".$article['capacite_bouteilles']." ".$template->translate($article['capacite_unite'])."</td>";
	
	$finition=$DB_site->query_first("SELECT finitionid,nom".$template->langue." FROM articlerecherche_finition INNER JOIN recherche_finition USING(finitionid) WHERE artid=".$article['artid']);
	$template->parts["gamme"].="<td>".$finition[1]."</td>";
	$template->parts['versionporte'].="<td>".$template->translate($article['version_porte'],"F")."</td>";
	$template->parts["coloris"].="<td>".$template->translate($article['coloris'],"F")."</td>";
	$template->parts["coloriscadre"].="<td>".$template->translate($article['coloris_porte'],"F")."</td>";
	$temp_install=($article['temp_instal_max']>0?$article['temp_instal_min']."°C / ".$article['temp_instal_max']."°C":"");
	$template->parts["temperatureinstallation"].="<td>".$temp_install."</td>";
	$template->parts["classe"].="<td>".$article['energy_label']."</td>";
	$d=delailivraison($article);
	$template->parts["delailivraison"].="<td>".$d['delailivraison']."</td>";
	$template->parts["dimensions"].="<td>".$article['hauteur']." * ".$article['largeur']." * ".$article['profondeur']." mm / ".round($article['poid']/1000)." kg</td>";
	$imgfabrication="<img src=\"/images/templates/fabrication_".$article[paysfabrication].".jpg\" alt=\"".$template->translate("/article/origines/".$article[paysfabrication],"F")."\" align=\"absmiddle\" height=\"26\"> ".$template->translate("/article/origines/".$article[paysfabrication],"F");
	$template->parts["origine"].="<td>".$imgfabrication."</td>";
	
	$template->parts["hygrometrie"].="<td>".$template->translate($article['hygrometrie'])."</td>";
	$template->parts["hygrometre"].="<td>".$template->translate($article['hygrometre'])."</td>";
	$template->parts["filtre_charbon"].="<td>".$template->translate($article['filtre_charbon'])."</td>";
	$template->parts["serrure"].="<td>".$template->translate($article['serrure'])."</td>";
	$template->parts["alarme"].="<td>".$template->translate($article['alarme'])."</td>";
	$template->parts["eclairage"].="<td>".$template->translate($article['eclairage'])."</td>";
	
	$template->parts["nbtemperature"].="<td>".$template->translate($article['nbr_temperature'])."</td>";
	$template->parts["plagereglage"].="<td>".$template->translate($article['plage_temperature'])."</td>";
	$template->parts["regulation"].="<td>".$template->translate($article['regulation'])."</td>";
	$template->parts["affichage"].="<td>".$template->translate($article['affichage'])."</td>";
	
	$template->parts["productionfroid"].="<td>".$template->translate($article['production_froid'])."</td>";
	$template->parts["productionchaud"].="<td>".$template->translate($article['production_chaud'])."</td>";
	$template->parts["fonctionhiver"].="<td>".$template->translate($article['fonction_hiver_techno'])."</td>";
	$template->parts["revetement"].="<td>".$template->translate($article['revetement_exterieur'])."</td>";
	$template->parts["gaz"].="<td>".$template->translate($article['gaz'])."</td>";
	
	$template->parts["consokw"].="<td>".round(floatval($article['conso_kw'])*365)."</td>";
	$template->parts["bruit"].="<td>".$article['niveau_bruit']."</td>";
	$article[conso_euro]=round($article['conso_kw']*$prixkw*365);
	$template->parts["conso"].="<td>".round($article[conso_kw],1)." kW/24h ".$template->translate("/article/champs/soit_env")." $article[conso_euro] &euro; ".$template->translate("/site/ttc").$template->translate("/article/champs/par_an")."</td>";
    }
}
$template->evaltemplate("ComparateurGrille".$typearticleid,"blocgrille");
$template->evaltemplate("ComparateurCriteres".$typearticleid,"bloccriteres");



$template->evaltemplate("ComparateurPage","comparateur");
print stripslashes($template->parts["comparateur"]);

?>