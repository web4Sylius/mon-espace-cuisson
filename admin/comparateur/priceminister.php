<?php
ini_set("max_execution_time",180);
ini_set("display_errors","on");
$prov = "comp" ;

//$rootpath="/media/disk/www/macave/site2012/";
$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";

$host   = "www.ma-cave-a-vin.fr" ;
$nostat = 1;

require_once($rootpath."configurations/$host/config_mysql.php");
require_once($rootpath."class/mysql.php");
require_once($rootpath."class/template.class.php");
require_once($rootpath."admin/mpfields.php");
require_once($rootpath."class/fonctions.php");
require_once($rootpath."class/devises.class.php");

$DB_site2 = new DB_Sql;
$DB_site2->appname='boutique';
$DB_site2->appshortname='boutique';
$DB_site2->database=$DBname;
$DB_site2->server=$db_server;
$DB_site2->user=$DBusername;
$DB_site2->password=$DBpassword;
$DB_site2->connect();

$DB_site=clone $DB_site2;

$template=new template("FR",$DB_site2);

$today = mktime(0,0,0);

$sql = "SELECT 0 AS taux0, TVAtauxnormal AS taux1, TVAtauxreduit AS taux2";
$sql .= " FROM pays";
$sql .= "  WHERE paysid=57";
$tvafrance=$DB_site2->query_first($sql);

if(!is_dir($rootpath."configurations/$host/exports")){
     mkdir($rootpath."configurations/$host/exports",0777);
}
$filename = $rootpath."configurations/$host/exports/priceminister.csv";

if(!$handle = fopen($filename, 'w')){
     echo "Impossible d'ouvrir le fichier ($filename)";
     exit;
}

$contenu = " ;Référence fabricant;Référence interne;Prix de vente;Prix public;Quantité;Qualité;Commentaire annonce;URL Image 1;URL Image 2;Titre;Description;Catégorie;Type de Produit;Fabricant;Couleur;Matière dominante;Plage de poids;Style;Emplacement;Dimensions;Expédition, Retrait;Téléphone;Code postal;Pays";

if(fwrite($handle, stripslashes(html_entity_decode($contenu,ENT_COMPAT,"ISO-8859-15"))) === FALSE){
     echo "Impossible d'écrire dans le fichier ($filename)";
     exit;
}

$sql = "SELECT a.*";
$sql .= " FROM article a";
$sql .= " INNER JOIN categorie c USING(catid)";
$sql .= " INNER JOIN article_site a_s USING(artid)";
$sql .= " WHERE a_s.active = '1'";
$sql .= " AND commandable = '1'";
$sql .= " GROUP BY artcode";
$sql .= " ORDER BY a.artid";
$articles = $DB_site2->query($sql);
while($article = $DB_site2->fetch_array($articles)){
     
     // nombre de coloris ==> nombre de ligne dans l'export
     $sql = "SELECT COUNT(*)";
     $sql .= " FROM article_caractval";
     $sql .= " INNER JOIN caracteristiquevaleur USING(caractvalid)";
     $sql .= " WHERE artid = '".$article['artid']."'";
     $sql .= " AND caractid = '1'";
     $nbcouleurs = $DB_site2->query_first($sql);
     
     if($nbcouleurs[0] > 0){ // on a plusieurs lignes
          
          $sql = "SELECT *";
          $sql .= " FROM article_caractval ac";
          $sql .= " INNER JOIN caracteristiquevaleur c USING(caractvalid)";
          $sql .= " WHERE ac.artid = '".$article['artid']."'";
          $sql .= " AND c.caractid = '1'";
          $couleurs = $DB_site2->query($sql);
          while ($couleur=$DB_site2->fetch_array($couleurs)){
               
               $couleur['libelleFR'] = stripslashes($couleur['libelleFR']);
               generationligne($article['artid'], $couleur['libelleFR']) ;
               
          }
     } else {// on a une ligne
          
          generationligne($article['artid'], $article['coloris']) ;
          
     }
     
     //05/11/14 : Probablement obsolète, la table n'a plus du tout cette structure !
     /*if(isset($_GET['tr'])){
          
          $sql = "INSERT INTO market_place_champs SET";
          $sql .= " market_place = 'Priceminister'";
          $sql .= ", artcode     = '".$article['artcode']."'";
          $sql .= ", champ       = 'Type de Produit'";
          $sql .= ", valeur      = 'Cave à vin'";
          $sql .= ", limite      = 50";
          $sql .= ", enabled     = 1";
          $sql .= ", caractvalid = 0";
          
          mysql_query($sql);	
     }*/
     
}

fclose($handle);

echo "Votre export de catalogue pour PriceMinister a bien été mis à jour.<br>
Electroménager : <a href=\"http://$host/configurations/$host/exports/priceminister.csv\"><strong>http://$host/configurations/$host/exports/priceminister.csv</strong></a>
<br>Art de la table : <a href=\"http://$host/configurations/$host/exports/priceminister_arttable.csv\"><strong>http://$host/configurations/$host/exports/priceminister_arttable.csv</strong></a>";

function generationligne($artid,$couleur){

     global $host,$DB_site2,$today;
     global $template;
     global $handle;
     global $tvafrance;
     $selecthandle=$handle;
     
     $sql = "SELECT *";
     $sql .= " FROM article";
     $sql .= " WHERE artid = '".$artid."'";
     $article = $DB_site2->query_first($sql);
     if($article['date_appro'] > $today){
          return false;
     }
	
     $ligne = "";
     $article['libelleFR'] = html_entity_decode($article['libelleFR'],ENT_COMPAT,"ISO-8859-15");
     $article['libelleFR'] = str_replace("Températures","T°",$article['libelleFR']);	
     $article['libelleFR'] = str_replace("Température","T°",$article['libelleFR']);		
     $article['libelleFR'] = str_replace("températures","T°",$article['libelleFR']);	
     $article['libelleFR'] = str_replace("température","T°",$article['libelleFR']);			
     $article['libelleFR'] = str_replace(";"," ",$article['libelleFR']);
     
     //image
     $imagecoloris['caractvalid'] = 0;
     $article['urlimage'] = "";
     
     if($couleur != ""){
          
          $coloris  = explode("'",$couleur);
          
          $sql = "SELECT caractvalid";
          $sql .= " FROM caracteristiquevaleur";
          $sql .= " WHERE caractid = '1'";
          $sql .= " AND libelleFR LIKE '%".$coloris[0]."%'";
          $infocaractvalid = $DB_site2->query_first($sql);
          
          $sql = "SELECT image";
          $sql .= " FROM article_caractval";
          $sql .= " WHERE caractvalid = '".$infocaractvalid['caractvalid']."'";
          $sql .= " AND artid = '".$artid."'";
          $existeimg = $DB_site2->query_first($sql);
          
          if($existeimg['image'] == "jpg"){
               $article['urlimage'] = "http://".$host."/configurations/".$host."/images/produits/article_caractval/".$article['artid']."_".$infocaractvalid['caractvalid'].$article['image'];
          } elseif($article['image'] != ""){
               
               $article['urlimage'] = "http://".$host."/configurations/".$host."/images/produits/".$article['artid'].$article['image'];
          
          } elseif ($article['image'] != ""){
               
               $article['urlimage'] = "http://".$host."/configurations/".$host."/images/produits/".$article['artid'].$article['image'];
          } 
     }
          
     //image2
     $article['urlimage2'] = "";
     if($couleur != ""){

          $coloris = explode("'",$couleur);

          $sql = "SELECT image, caractvalid";
          $sql .= " FROM caracteristiquevaleur";
          $sql .= " WHERE caractid = '1'";
          $sql .= " AND libelleFR LIKE '%".$coloris[0]."%'";
          $imagecoloris = $DB_site2->query_first($sql);

          if($imagecoloris['image'] == "1"){

               $article['urlimage2'] = "http://".$host."/configurations/".$host."/images/caracteristiquevaleur/zoom/".$imagecoloris['caractvalid'].".jpg" ;

          }

     }
	
     //commentaire et titre
     $commentaire_annonce = " Achetez chez un Expert en caves à vin (couleur et inox) de 6 à 1000 bouteilles pour tous les usages : le vieillissement, la conservation, le service, la dégustation, et la préservation du vin et du champagne - Pour particuliers et professionnels - Livraison assurée par des professionnels en France et en Europe";

     $sql = "SELECT libelleFR";
     $sql .= " FROM article_caractval";
     $sql .= " INNER JOIN caracteristiquevaleur USING(caractvalid)";
     $sql .= " WHERE artid = '".$article['artid']."'";
     $sql .= " AND caractid = '1'";
     $couleurs_cave = $DB_site2->query($sql);

     $liste_coloris = "";

     if($DB_site2->num_rows($couleurs_cave) > 1){

          while ($couleur_cave = $DB_site2->fetch_array($couleurs_cave)){

               $liste_coloris .= $couleur_cave['libelleFR'].", ";

          }

     } else {

          $liste_coloris = $article['coloris'];

     }

     $sql = "SELECT libelleFR";
     $sql .= " FROM article_caractval";
     $sql .= " INNER JOIN caracteristiquevaleur USING(caractvalid)";
     $sql .= " WHERE artid = ".$article['artid'];
     $sql .= " AND differenceprix = 0";
     $sql .= " AND caractvalid <> 9";
     $sql .= " AND caractid IN (3,22)";
     $sql .= " ORDER BY position";
     $garantie = $DB_site2->query_first($sql);

     $commentaire_annonce .= ($garantie['libelleFR']!=""?" - Garantie : ".$garantie['libelleFR']:"")." - Délai de livraison : ".$article['delailivraison_txt'];	

     //description
     $article['rangements_txt'] = str_replace("<br>"," - ",$article['rangements_txt']);
     $article['rangements_txt'] = str_replace("\n","",$article['rangements_txt']);
     $article['rangements_txt'] = substr($article['rangements_txt'], 0, -3);

     //$descarticle = ($article[version_porte]!=""?"Porte : $article[version_porte]":"").($article[rangements_txt]!=""?" - Rangement inclus : $article[rangements_txt]":"");
     //references
     $artcode = $article['artcode'];

     if(trim($imagecoloris['caractvalid']) != ""){
          $artcode .= "-".$imagecoloris['caractvalid'];
     }

     //$artcode .= abbreviation_codecouleur($couleur,$article[version_porte]);
     //$artcode .= "-".abbreviation_versionporte($article[version_porte]);
     /*if (eregi("combiné",$article[libelleFR]))
             $artcode .= "M";
     else
             $artcode .= "D";*/

     if(eregi("vitrée",$article['version_porte']) && !eregi("pleine",$article['version_porte'])){

          $sql = "SELECT COUNT(*)";
          $sql .= " FROM article_caractval";
          $sql .= " INNER JOIN caracteristiquevaleur USING(caractvalid)";
          $sql .= " WHERE artid = '".$article['artid']."'";
          $sql .= " AND caractid = '2'";
          $sql .= " AND caractvalid = '7'";
          $noir = $DB_site2->query_first($sql);

          $sql = "SELECT COUNT(*)";
          $sql .= " FROM article_caractval";
          $sql .= " INNER JOIN caracteristiquevaleur using(caractvalid)";
          $sql .= " WHERE artid = '".$article['artid']."'";
          $sql .= " AND caractid = '2'";
          $sql .= " AND caractvalid = '8'";
          $inox = $DB_site2->query_first($sql);

          $sql = "SELECT COUNT(*)";
          $sql .= " from article_caractval";
          $sql .= " inner join caracteristiquevaleur using(caractvalid)";
          $sql .= " where artid = '$article[artid]'";
          $sql .= "  and caractid = '2'";
          $sql .= " and caractvalid = '12'";
          $naturel = $DB_site2->query_first($sql);

          $nb_possiblites = $noir[0] + $inox[0] + $naturel[0];

          if($noir[0] == "1" and $naturel[0] == "1"){

               //$artcode .= "N";
               $descarticle .= " - Cadre Vitre :  Alu Noir (Naturel sur simple demande)";

          } elseif($inox[0] == "1" && $noir[0] == "0" && $naturel[0] == "0"){

               //$artcode .= "I";
               $descarticle .= " - Cadre Vitre :  Inox";

          } elseif($noir[0] == "1" or $naturel[0] == "1"){
                    
               if ($noir[0] == "1"){
                         
                    //$artcode .= "N";
                    $descarticle .= " - Cadre Vitre :  Alu Noir";
                    
               }elseif($naturel[0] == "1"){
                    
                    //$artcode .= "S";
                    $descarticle .= " - Cadre Vitre :  Alu Naturel";
                    
               }
               
          }
          
     }
     
     //$titrearticle = "$article[libelleFR] $article[gamme] $artcode".($article[nbr_temperature]!=""?" - Nbre T° : $article[nbr_temperature]":"").($article[capacite_bouteilles]!=""?" - Bouteilles : $article[capacite_bouteilles]":"")." - Coloris : ";
     //$titrearticle .= "$couleur";

     $sql = "SELECT *";
     $sql .= " FROM market_place_catid";
     $sql .= " WHERE market_place = 'Priceminister'";
     $sql .= " AND catid = '".$article['catid']."'";
     $req = $DB_site2->query($sql);
     
     while($row = $DB_site2->fetch_array($req)){
          
          $mpc[$row['champ']] = $row['valeur'];
          
     }

     $sql = "SELECT *";
     $sql .= " FROM market_place_champs";
     $sql .= " WHERE market_place LIKE 'Priceminister'";
     $sql .= " AND artcode = '".$article['artcode']."'";
     $req = $DB_site2->query($sql);
     
     $rowprod = $DB_site2->fetch_array($req);
     
     //On regarde si cet article est lié à un autre dans le stock
     $sql = "SELECT artid";
     $sql .= " FROM stock_mini";
     $sql .= " WHERE artids LIKE '%".intval($article['artid'])."%'";
     $artPrincipal = $DB_site2->query_first($sql);
     
     $stock_id = $artid;
     if(isset($artPrincipal['artid']) && !empty($artPrincipal['artid']) && !is_null($artPrincipal['artid'])) $stock_id = $artPrincipal['artid'];
     
     //Récupération de la date de réapprovisionnement pour cette référence
     $sql = "SELECT si2.date_livraison as date_reappro";
     $sql .= " FROM stock_item si2";
     $sql .= " WHERE si2.artid = ".intval($stock_id);
     $sql .= " AND si2.state = 0";
     $sql .= " ORDER BY si2.date_livraison";
     $sql .= " DESC LIMIT 0,1";
     $tabAppro = $DB_site2->query_first($sql);
     
     if($rowprod['enabled'] == 0){
          return false;
     }
     
     /*
      * Conditions pour que le produit ne soit pas importé :
      * - Produit désactivé de la vente sur MCAV
      * - Produit non commandable sur MCAV
      * - Produit désactivé sur PriceMinister
      * - Date de réappro supérieure à 7 jours
      */
     if((isset($tabAppro['date_reappro']) && !empty($tabAppro['date_reappro']) && mktime(0,0,0,date('m'),date('d')+7,date('Y')) < $tabAppro['date_reappro'])){
          $quantite = 0;
     } else {
          $quantite = 10;
     }

     $titrearticle = mpfields($article,$mpc['Titre']);
     $descarticle  = mpfields($article,$mpc['Description']);
     $descarticle  = str_replace("\"","'",$descarticle);
     $descarticle  = str_replace("&rsquo;","'",$descarticle);
     $descarticle  = html_entity_decode($descarticle,ENT_COMPAT,"ISO-8859-15");
     $descarticle  = str_replace(";",",",$descarticle);
	
     //$optionporte = $DB_site2->query_first("SELECT COUNT(*) FROM article_caractval WHERE artid = '$article[artid]' AND caractvalid = '13'");	
     //if ($optionporte[0] > 0)
     //$descarticle .= " - Charnières Porte : à droite ( à Gauche sur simple demande)";
     //$descarticle .= ($article[temperature_cave]!=""?" - T° installation cave : $article[temperature_cave]":"").($article[conso_euro]!=""?" - Consommation €/an : $article[conso_euro] TTC":"");
     //$descarticle .= ($article[regulation]!=""?" - Régulation : $article[regulation]":"").($article[affichage]!=""?" - Affichage : $article[affichage]":"").($article[hygrometrie]!=""?" - Hygrométrie : ".stripslashes($article[hygrometrie]):"").($article[serrure]!=""?" - Serrures : $article[serrure]":"").($article[eclairage]!=""?" - Eclairage : $article[eclairage]":"").($article[interrupteur]!=""?" - Interrupteur Marche/Arrêt : $article[interrupteur]":"");
     //prix de vente TTC
  
     $article_prices = article_prices($article['artid'],$tvafrance,"base",false,false);
	
     $sql = "SELECT p.prix as prixport, a.poid,p.gratuit";
     $sql .= " FROM fraisport p, article a";
     $sql .= " WHERE p.paysid = '57'";
     $sql .= " AND a.poid BETWEEN p.poiddebut AND p.poidfin";
     $sql .= " AND a.artid = '".$article['artid']."'";
     $port = $DB_site2->query_first($sql);
        
     if($article_prices['prix'] >= $port['gratuit']){
          
          $port['prixport'] = 0;
          
     }
     
     $prixvente = $article_prices['prix'] + $port['prixport'] - portpm($article['emballage_poids'],$article_prices['prix']);	
     $prixvente = number_format($prixvente, 2, ',', '');
     //prix de vente TTC
     $prixpublic = $article_prices['prixconseille'] + $port['prixport'];	
     $prixpublic = number_format($prixpublic, 2, ',', '');

     $sql = "SELECT libelle";
     $sql .= " FROM article_marque";
     $sql .= " INNER JOIN marque USING(marqueid)";
     $sql .= " WHERE artid = '".$article['artid']."'";
     $marque = $DB_site2->query_first($sql);
	    
     //debut de la ligne
     $ligne.="\n";	
     // 
     $ligne.="\"".$mpc['typefichier']."\";";
     // reference_fabricant
     $ligne.="\"".$artcode."\";";
     // reference interne
     $ligne.="\"".$artcode."\";";
     // prix de vente TTC
     $ligne.="\"".$prixvente."\";";
     // prix public TTC
     $ligne.="\"".$prixpublic."\";";
     // quantité
     $ligne.="\"".$quantite."\";";
     // qualité
     $ligne.="\"N\";";
     // Commentaire annonce
     $ligne.="\"".$commentaire_annonce."\";";
     // url image 1
     $ligne.="\"".$article['urlimage']."\";";
     // url image 2
     $ligne.="\"".$article['urlimage2']."\";";
     // titre
     $ligne.="\"".$titrearticle."\";";
     // fiche technique
     $ligne.="\"".preg_replace("/\r\n|\n|\r/","",$descarticle)."\";";
     // catégorie
     $ligne.="\"".$mpc['categorie']."\";";
     // type de produit
     $ligne.="\"".preg_replace("/\r\n|\n|\r/","",mpfields($article,$mpc['Type']))."\";";
     // marque
     $ligne.="\"".$marque['libelle']."\";";
     // Couleur
     $ligne.="\"".$template->translate($couleur)."\";";
     // Matière dominante
     $ligne.="\"".$mpc['matiere']."\";";
     // Poids
     $ligne.="\"".($article['emballage_poids']*1000)."\";";
     // style
     $ligne.="\"".$mpc['style']."\";";
     // emplacement
     $ligne.="\"".$mpc['emplacement']."\";";
     // dimensions
     $ligne.="\"".$article['dimensions']."\";";
     // expéditon, enlèvement
     $ligne.="\"EXP\";";
     // telephone
     $ligne.="\"+33(0)426030543\";";
     // code postal
     $ligne.="\"69003\";";
     // pays
     $ligne.="\"France\"";

     fwrite($handle,stripslashes(html_entity_decode($ligne,ENT_COMPAT,"ISO-8859-15")));

     return $ligne;

}

function abbreviation_codecouleur($couleur, $porte){
	
     $couleur = strtolower($couleur);
     $porte = strtolower($porte);
     
     if(eregi("bordeaux",$couleur)){
          $code = "B";
     }  elseif(eregi("ambroise",$couleur)){
          $code = "AM";
     } elseif(eregi("poirier rouge",$couleur)){
          $code = "PR";
     } elseif(eregi("calvados",$couleur)){
		$code = "PO";
     } elseif(eregi("noir",$couleur)){
		$code = "NS";
     } elseif(eregi("tabac",$couleur)){
		$code = "PL";
     } elseif(eregi("inox",$couleur)){
          
          if(eregi("vitrée",$porte) and eregi("pleine",$porte)){
               $code = "IM";
          } elseif (eregi("vitrée",$porte)){
               $code = "IV";
          } elseif (eregi("pleine",$porte)){
               $code = "IP";
          } else {
               $code = "IM";
          }
          
     } else {
          $code = "";
     }
     
     return $code;	

}
	
function abbreviation_versionporte($porte){
     
     $porte = strtolower($porte);
     
     if(eregi("vitrée",$porte) && ereg("pleine",$porte)){
          $code = "M";
     } elseif(eregi("vitrée",$porte)){  
          $code = "V";
     }elseif(eregi("pleine",$porte)){
          $code = "P";
     } else {
          $code = "";
     }		
     
     return $code;
     
}

function portpm($poids,$prix){
     
     if($prix>=79) return 0;
	
     switch($poids){
          case $poids>=0 && $poids<0.1:
               return 3.4;
               break;
          case $poids>=0.1 && $poids<0.25:
               return 4.6;
               break;
          case $poids>=0.25 && $poids<0.5:
               return 6.8;
               break;
          case $poids>=0.5 && $poids<3:
               return 8.2;
               break;
          case $poids>=3 && $poids<10:
               return 10.8;
               break;
          case $poids>=10 && $poids<15:
               return 16;
               break;
          case $poids>=15 && $poids<30:
               return 21.5;
               break;
          case $poids>=30 && $poids<50:
               return 34;
               break;
          case $poids>=50 && $poids<70:
               return 54;
               break;
          case $poids>=70 && $poids<100:
               return 74;
               break;
          case $poids>=100:
               return 104;
               break;
     }

}