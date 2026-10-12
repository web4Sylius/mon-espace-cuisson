<?php

require ("global.php");

$departement = array(
  '01' => 'Ain',
  '02' => 'Aisne',
  '03' => 'Allier',
  '04' => 'Alpes de Haute Provence',
  '05' => 'Alpes (Hautes)',
  '06' => 'Alpes Maritimes',
  '07' => 'Ardéche',
  '08' => 'Ardennes',
  '09' => ' Ariége',
  '10' => 'Aube',
  '11' => 'Aude',
  '12' => 'Aveyron',
  '13' => 'Bouches du Rhône',
  '14' => 'Calvados',
  '15' => 'Cantal',
  '16' => 'Charente',
  '17' => 'Charente Maritime',
  '18' => 'Cher',
  '19' => 'Corréze',
  '23' => 'Creuse ',
  '24' => 'Dordogne',
  '25' => 'Doubs',
  '26' => 'Drôme',
  '27' => 'Eure',
  '28' => 'Eure et Loir',
  '29' => 'Finistére',
  '30' => 'Gard',
  '31' => 'Garonne (Haute)',
  '32' => 'Gers',
  '33' => 'Gironde',
  '34' => 'Hérault',
  '35' => 'Ile et Vilaine',
  '36' => 'Indre',
  '37' => 'Indre et Loire',
  '38' => 'Isére',
  '39' => 'Jura',
  '40' => 'Landes',
  '41' => 'Loir et Cher',
  '42' => 'Loire',
  '43' => 'Loire (Haute)',
  '44' => 'Loire Atlantique',
  '45' => 'Loiret',
  '46' => 'Lot',
  '47' => 'Lot et Garonne',
  '48' => 'Lozére',
  '49' => 'Maine et Loire',
  '50' => 'Manche',
  '51' => 'Marne',
  '52' => 'Marne (Haute)',
  '53' => 'Mayenne',
  '54' => 'Meurthe et Moselle',
  '55' => 'Meuse',
  '56' => 'Morbihan',
  '57' => 'Moselle',
  '58' => 'Niévre',
  '59' => 'Nord',
  '60' => 'Oise',
  '61' => 'Orne',
  '62' => 'Pas de Calais',
  '63' => 'Puy de Dôme',
  '64' => 'Pyrénées Atlantiques',
  '65' => 'Pyrénées (Hautes)',
  '66' => 'Pyrénées Orientales',
  '67' => 'Rhin (Bas)',
  '68' => 'Rhin (Haut)',
  '69' => 'Rhône',
  '70' => 'Saône (Haute)',
  '71' => 'Saône et Loire',
  '72' => 'Sarthe',
  '73' => 'Savoie',
  '74' => 'Savoie (Haute)',
  '75' => 'Paris',
  '76' => 'Seine Maritime',
  '77' => 'Seine et Marne',
  '78' => 'Yvelines',
  '79' => 'Sèvres (Deux)',
  '80' => 'Somme',
  '81' => 'Tarn',
  '82' => 'Tarn et Garonne',
  '83' => 'Var',
  '84' => 'Vaucluse',
  '85' => 'Vendée',
  '86' => 'Vienne',
  '87' => 'Vienne (Haute)',
  '88' => 'Vosges',
  '89' => 'Yonne',
  '90' => 'Belfort (Territoire de)',
  '91' => 'Essonne',
  '92' => 'Hauts de Seine',
  '93' => 'Seine Saint Denis',
  '94' => 'Val de Marne',
  '976' => 'Mayotte',
  '971' => 'Guadeloupe',
  '973' => 'Guyane',
  '972' => 'Martinique',
  '974' => 'Réunion',
  '21' => 'Côte d\'or',
  '2A' => 'Corse du sud',
  '22' => 'Côtes d\'armor',
  '2B' => 'Haute corse',
  '95' => 'Val d\'oise'
);

//attention les types sont réutilisés pour récupérer le texte dans la table traduction
$types=array("restaurants-bars-a-vin"=>"restaurants bars à vin","club-d-oenologie"=>"club oenologie","cavistes"=>"Cavistes","producteurs-de-vin"=>"Producteurs de vin","epicerie-fine"=>"epiceries fines","agences-site-web"=>"agences et site web");

$url_actuelle = $_SERVER[REQUEST_URI];
$url_actuelle = explode(",",$url_actuelle);
$last_niveau = count($url_actuelle) - 1;

$empty = true;

$activite = htmlspecialchars($_GET['type']);
$activiteTitle = $types[$_GET['type']];
$template->parts["description"] = $template->translate("/partenaires/".$activite);
// if (isset($id) and $id!=""){
if (isset($type) and $type!=""){
          // $type="";
          // $id="";
          if(isset($_GET['type']) && $_GET['type']!="tous") $type=" AND activite='".AddSlashes($activite)."'";
          // print_r("SELECT * FROM caviste WHERE pays = 'FRANCE' AND activite = '" . AddSlashes($activite) . "' ORDER BY codepostal");
          $partenaires=$DB_site->query("SELECT * FROM caviste WHERE LOWER(pays) = 'france' AND activite = '" . AddSlashes($activite) . "' ORDER BY codepostal");
          if ($DB_site->num_rows($partenaires) > 0){
            $dep = '';
           while ($caviste=$DB_site->fetch_array($partenaires)){
                $empty = false;
                 if($lastactivite=="") $lastactivite=$caviste[activite];
                 $image="&nbsp;";
                 if($caviste['image']!="" && file_exists($caviste['image'])){
                       $extension = pathinfo($caviste['image'], PATHINFO_EXTENSION);

                       switch($extension){
                            case "jpg":
                            case "gif":
                            case "png":
                            $image="<img src=\"/$caviste[image]\" width=\"160\" alt=\"$caviste[raisonsociale]\">";
                            break;
                            case "swf":
                            $sizes=GetImageSize($caviste['image']);
                            $image="<object type=\"application/x-shockwave-flash\" data=\"/".$caviste['image']."\" width=\"".$sizes[0]."\" height=\"".$sizes[1]."\">
                            <param name=\"allowScriptAccess\" value=\"sameDomain\" />
                            <param name=\"movie\" value=\"/".$caviste['image']."\" />
                            <param name=\"quality\" value=\"high\" />
                            <param name=\"bgcolor\" value=\"#ffffff\" />
                            </object>";
                            break;
                       }
                       //$image= "ext:".$extension;
                 }
                 $site="";
                 if($caviste[affiche_site]==1 && $caviste[site]!=""){
                       if(substr($caviste[site],0,7)!="http://") $caviste[site]="http://".$caviste[site];
                       $site="<a href=\"$caviste[site]\" target=\"_blank\">".str_replace("http://","",$caviste[site])."</a>";
                 }
                 $activite=$caviste[activite];
		 $caviste[description]=nl2br($caviste[description]);

                $dpNum = substr($caviste['codepostal'], 0, 2);

                if($dp != $dpNum) {
                  $dp = $dpNum;
                  $template->parts["articles"].="<div class=\"first_bloc categorie_desc kdosPage_title\"><h2>($dp) $departement[$dp]</h2></div>";
                }

                 $template->parts["articles"].="<div class=\"partenaire\">
		   
		    <div class=\"coord col-xs-12 col-md-3\"><strong>$caviste[raisonsociale]</strong><br>$caviste[adresse]<br>$caviste[codepostal] $caviste[ville]<br>Téléphone : $caviste[telephone]<br>$site</div>
         <div class=\"image img-responsive col-xs-12 col-md-3\">$image</div>
		    <div class=\"description col-xs-12 col-md-6\">$caviste[description]</div>
		    <div class=\"clear\"></div>
		 </div>";

            }
	    
	  } else {
	    /*$template->parts["articles"]="<div class=\"partenaire\">
		Nous n'avons pas encore renseigné de partenaire pour ce département.
	    </div>";*/

    }

	  
}

if (isset($type) and $type!=""){
          // $type="";
          // $id="";
          if(isset($_GET['type']) && $_GET['type']!="tous") $type=" AND activite='".AddSlashes($activite)."'";
          // print_r("SELECT * FROM caviste WHERE pays = 'FRANCE' AND activite = '" . AddSlashes($activite) . "' ORDER BY codepostal");
          $partenaires=$DB_site->query("SELECT * FROM caviste WHERE LOWER(pays) != 'france' AND activite = '" . AddSlashes($activite) . "' ORDER BY pays");
          if ($DB_site->num_rows($partenaires) > 0){
            $pays = '';
           while ($caviste=$DB_site->fetch_array($partenaires)){
                $empty = false;
                 if($lastactivite=="") $lastactivite=$caviste[activite];
                 $image="&nbsp;";
                 if($caviste['image']!="" && file_exists($caviste['image'])){
                       $extension = pathinfo($caviste['image'], PATHINFO_EXTENSION);

                       switch($extension){
                            case "jpg":
                            case "gif":
                            case "png":
                            $image="<img src=\"/$caviste[image]\" width=\"160\" alt=\"$caviste[raisonsociale]\">";
                            break;
                            case "swf":
                            $sizes=GetImageSize($caviste['image']);
                            $image="<object type=\"application/x-shockwave-flash\" data=\"/".$caviste['image']."\" width=\"".$sizes[0]."\" height=\"".$sizes[1]."\">
                            <param name=\"allowScriptAccess\" value=\"sameDomain\" />
                            <param name=\"movie\" value=\"/".$caviste['image']."\" />
                            <param name=\"quality\" value=\"high\" />
                            <param name=\"bgcolor\" value=\"#ffffff\" />
                            </object>";
                            break;
                       }
                       //$image= "ext:".$extension;
                 }
                 $site="";
                 if($caviste[affiche_site]==1 && $caviste[site]!=""){
                       if(substr($caviste[site],0,7)!="http://") $caviste[site]="http://".$caviste[site];
                       $site="<a href=\"$caviste[site]\" target=\"_blank\">".str_replace("http://","",$caviste[site])."</a>";
                 }
                 $activite=$caviste[activite];
     $caviste[description]=nl2br($caviste[description]);
                if($pays != $caviste['pays']) {
                  $pays = $caviste['pays'];
                  $template->parts["articles"].="<div class=\"first_bloc categorie_desc kdosPage_title\"><h2>$pays</h2></div>";
                }

                 $template->parts["articles"].="<div class=\"partenaire\">
        <div class=\"image img-responsive col-xs-6\">$image</div>
        <div class=\"coord col-xs-6\"><strong>$caviste[raisonsociale]</strong><br>$caviste[adresse]<br>$caviste[codepostal] $caviste[ville]<br>Téléphone : $caviste[telephone]<br>$site</div>
        <div class=\"description col-xs-12\">$caviste[description]</div>
        <div class=\"clear\"></div>
     </div>";

            }
      
    } else {
      /*$template->parts["articles"]="<div class=\"partenaire\">
    Nous n'avons pas encore renseigné de partenaire pour ce département.
      </div>";*/

    }

    
}

// Si aucun partenaires
if($empty) {
  $template->parts["articles"].="<div class=\"first_bloc kdosPage_title _u tcenter\"><h2>Il n'y a pour le moment aucun partenaires dans cette catégorie</h2></div>";
}

$template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><a property=\"item\" typeof=\"WebPage\" href=\"/partenaires\"><span property=\"name\">Nos partenaires</span></a><meta property=\"position\" content=\"2\"></span> &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".ucwords($activite)."</span><meta property=\"position\" content=\"3\"></span>";

include("barrefixe.php");
$template->pageparams($host,ucwords($activite),"");
$template->parts['description_img']="<img class=\"img-responsive\" src=\"/images/partenaires/".$activite.".jpg\">";
$template->parts['title'] = ucwords($activiteTitle);
$template->evaltemplate("KdosPage","main");

$template->out();

?>