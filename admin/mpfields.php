<?php

function mpfields($artid,$code,$allowable_tags="",$limite_car=""){
      global $DB_site2;
      global $template;
      if(is_array($artid) && $artid['artid']!=""){
	  $marque=$DB_site2->query_first("SELECT libelle FROM marque m INNER JOIN article_marque am ON (m.marqueid=am.marqueid) WHERE am.artid=$artid[artid]");
	  $article=$artid;
	  $article['marque']=$marque[0];
      }elseif(is_numeric($artid))
	  $article=$DB_site2->query_first("SELECT a.*,m.libelle AS marque FROM article a INNER JOIN article_marque am ON (am.artid=a.artid) INNER JOIN marque m ON (m.marqueid=am.marqueid) WHERE a.artid=$artid");
      else
	  return "false";


	$t = preg_replace("!\[artcode]!Ui",$article[artcode],$code);
	$t = preg_replace("!\[description]!Ui",$article[descriptionFR],$t);
	$t = preg_replace("!\[reffab]!Ui",$article[artcode_eurocave],$t);
	$t = preg_replace("!\[libelle]!Ui",$article[libelleFR],$t);
	$t = preg_replace("!\[marque]!Ui",$article[marque],$t);
	$t = preg_replace("!\[gamme]!Ui",$article[gammeFR],$t);
	$t = preg_replace("!\[modele]!Ui",$article[modele],$t);
	$t = preg_replace("!\[dimensions]!Ui",$article[dimensions],$t);
	$t = preg_replace("!\[porte]!Ui",$template->translate($article[version_porte]),$t);
	$t = preg_replace("!\[couleur]!Ui",$template->translate($article[coloris]),$t);
	$t = preg_replace("!\[couleurcadre]!Ui",$template->translate($article[coloris_porte]),$t);
	$t = preg_replace("!\[capacite]!Ui",$article[capacite_bouteilles],$t);
	$t = preg_replace("!\[capacitemini]!Ui",$article[capacite_mini],$t);
	$t = preg_replace("!\[capacitemaxi]!Ui",$article[capacite_maxi],$t);
	$t = preg_replace("!\[tempinstall]!Ui",$template->translate($article[temperature_cave]),$t);
	$t = preg_replace("!\[nbretemperatures]!Ui",$template->translate($article[nbr_temperature]),$t);
	$t = preg_replace("!\[plage_temperature]!Ui",$template->translate($article[plage_temperature]),$t);
	$t = preg_replace("!\[vibrations]!Ui",$template->translate($article[absence_vibration]),$t);
	$t = preg_replace("!\[filtre]!Ui",$template->translate($article[filtre_charbon]),$t);
	$t = preg_replace("!\[rangements]!Ui",$article[rangements_txt],$t);
	$t = preg_replace("!\[consoenergie]!Ui",round($article[conso_kw],2),$t);
	$t = preg_replace("!\[consoeuro]!Ui",$article[conso_euro],$t);
	$t = preg_replace("!\[poids]!Ui",round($article[poid]/1000),$t);
	$t = preg_replace("!\[prixht]!Ui",$article[prixht],$t);
	$t = preg_replace("!\[prixttc]!Ui",$article[prix],$t);
	$t = preg_replace("!\[prixconseille]!Ui",$article[prixconseilleht],$t);
	$t = preg_replace("!\[ecopartht]!Ui",$article[ecopartht],$t);
	$t = preg_replace("!\[ecopartttc]!Ui",$article[ecopart],$t);
	$t = preg_replace("!\[tauxtva]!Ui",$article[tauxchoisi],$t);
	$t = preg_replace("!\[delailivraison]!Ui",$article[delailivraison_txt],$t);
	$t = preg_replace("!\[paysfabrication]!Ui",origine($article[paysfabrication]),$t);
	$t = preg_replace("!\[refsfab]!Ui",$article[refs_fabricant],$t);
	$t = preg_replace("!\[garantie]!Ui",$article[garantie_incluse],$t);
	$t = preg_replace("!\[regulation]!Ui",$template->translate($article[regulation]),$t);
	$t = preg_replace("!\[bouton]!Ui",$template->translate($article[boutons_reglage]),$t);
	$t = preg_replace("!\[reglage]!Ui",$template->translate($article[reglage]),$t);
	$t = preg_replace("!\[affichage]!Ui",$template->translate($article[affichage]),$t);
	$t = preg_replace("!\[temperature]!Ui",$template->translate($article[temperature]),$t);
	$t = preg_replace("!\[thermometre]!Ui",$template->translate($article[thermometre]),$t);
	$t = preg_replace("!\[hygrometrie]!Ui",$template->translate($article[hygrometrie]),$t);
	$t = preg_replace("!\[hygrometre]!Ui",$template->translate($article[hygrometre]),$t);
	$t = preg_replace("!\[pieds]!Ui",$template->translate($article[pieds_reglables]),$t);
	$t = preg_replace("!\[serrure]!Ui",$template->translate($article[serrure]),$t);
	$t = preg_replace("!\[poignee]!Ui",$template->translate($article[poignee]),$t);
	$t = preg_replace("!\[modularite]!Ui",$template->translate($article[modularite_interieure]),$t);
	$t = preg_replace("!\[interrupteur]!Ui",$template->translate($article[interrupteur]),$t);
	$t = preg_replace("!\[eclairage]!Ui",$template->translate($article[eclairage]),$t);
	$t = preg_replace("!\[boutontest]!Ui",$template->translate($article[bouton_test]),$t);
	$t = preg_replace("!\[videpoche]!Ui",$article[vide_poche],$t);
	$t = preg_replace("!\[clayettefil]!Ui",$article[clayette_fil],$t);
	$t = preg_replace("!\[paniertransport]!Ui",$article[panier_transport],$t);
	$t = preg_replace("!\[vinokit]!Ui",$article[vinokit],$t);
	$t = preg_replace("!\[productionfroid]!Ui",$template->translate($article[production_froid]),$t);
	$t = preg_replace("!\[productionchaud]!Ui",$template->translate($article[production_chaud]),$t);
	$t = preg_replace("!\[declenchementchaud]!Ui",$template->translate($article[declenchement_chaud]),$t);
	$t = preg_replace("!\[fonctionhiver]!Ui",$template->translate($article[fonction_hiver_techno]),$t);
	$t = preg_replace("!\[puissance]!Ui",$article[puissance_electrique],$t);
	$t = preg_replace("!\[intensite]!Ui",$article[intensite_electrique],$t);
	$t = preg_replace("!\[conso24h]!Ui",$article[consommation_h],$t);
	$t = preg_replace("!\[conso1an]!Ui",$article[consommation_an],$t);
	$t = preg_replace("!\[bruit]!Ui",$article[niveau_bruit],$t);
	$t = preg_replace("!\[hauteur]!Ui",$article[hauteur],$t);
	$t = preg_replace("!\[largeur]!Ui",$article[largeur],$t);
	$t = preg_replace("!\[profondeur]!Ui",$article[profondeur],$t);
	$t = preg_replace("!\[volume]!Ui",$article[volume],$t);
	$t = preg_replace("!\[usage]!Ui",$article[usage],$t);
	$t = preg_replace("!\[classe_energie]!Ui",$article[energy_label],$t);
	$t = preg_replace("!\[classe_climatique]!Ui",$article[classe_clima],$t);
	$t = preg_replace("!\[gaz]!Ui",$article[gaz],$t);
	$t = strip_tags($t,$allowable_tags);
	$t=preg_replace("/\n|\r|\r\n|\t/","",$t);
	$t=preg_replace("/&rsquo;/","'",$t);
	$t=preg_replace("/&hellip;/","...",$t);
	$t=preg_replace("/&ldquo;|&rdquo;/","\"",$t);
        
        if(!empty($limite_car)){
             $t = substr($t, 0, ($limite_car - 3)).'...';
        }

	return $t;
}

function origine($id){
    switch($id){
      case 0:return "France";
      case 1:return "Europe";
      case 2:return "Asie";
      case 3:return "USA";
    }
}

?>