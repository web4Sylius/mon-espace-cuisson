<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(SITE_PATH."/class/article.class.php");
require_once(BACK_PATH."/export/xlexport.php");

$DB_site = clone $DB_site2;

$article = new produit($DB_site);
$csv     = new xlexport("csv");

$csv->name      = "export_articles_".date('dmY_his');
$csv->csvsep    = ";";
$csv->striptags = false;

$tabEntetes = array(
     'Identifiant type article',
     'Type article',
     'Identifiant catégorie',
     'Catégorie',
     'Identifiant marque',
     'Marque',
     'Réf. ACILYON',
     'Réf. Fabricant',
     'Réf. Associées',
     'Libellé',
     'Description',
     'Gamme',
     'Prix TTC',
     'Prix HT',
     'Prix public HT',
     'Eco-part TTC',
     'Eco-part HT',
     'TVA (%)',
     'Identifiant fournisseur',
     'Fournisseur',
     'Prix achat',
     'Délais livraison',
     'Prix transport',
     'Poids produit',
     'Hauteur emballage',
     'Largeur emballage',
     'Profondeur emballage',
     'Poids emballage',
     'Origine de fabrication',
     'Identifiant pays',
     'Pays de fabrication',
     'Date derniere modification'
);

$recherche      = isset($_GET['recherche']) && $_GET['recherche'] != "" ? $_GET['recherche'] : '';
$active         = isset($_GET['active']) ? $_GET['active'] : array();
$commandable    = isset($_GET['commandable']) ? $_GET['commandable'] : array();
$reappro        = isset($_GET['reappro']) ? 'reappro' : '';
$cdc            = isset($_GET['cdc']) ? 'cdc' : '';
$promo          = isset($_GET['promo']) ? 'promo' : '';
$idkdos         = isset($_GET['idkdos']) ? 'idkdos' : '';
$deselection    = isset($_GET['deselection']) ? 'deselection' : '';
$erreurs        = isset($_GET['erreurs']) ? 'erreurs' : '';
$type_id        = isset($_GET['type']) && !empty($_GET['type']) ? $_GET['type'] : '';
$categorie_id   = isset($_GET['categorie']) && !empty($_GET['categorie']) ? $_GET['categorie'] : '';
$marque_id      = isset($_GET['marque']) && !empty($_GET['marque']) ? $_GET['marque'] : '';
$fournisseur_id = isset($_GET['fournisseur']) && !empty($_GET['fournisseur']) ? $_GET['fournisseur'] : '';

if($recherche != ''){ //Input texte de recherche
    
     if(preg_match("/[0-9]{13}/",$recherche)){ //Recherche sur code barre
          
          $requete .= " AND a.artid IN (";
          $requete .= "    SELECT artid";
          $requete .= "    FROM code_barre ";
          $requete .= "    WHERE codebarre = '".$recherche."'";
          $requete .= ")";
          
     } else { //Recherche sur libellé ou réf. interne ou réf. fabricant
          
          $requete.=" AND (";
          $requete .= "   a.".$libelle." LIKE '%".$recherche."%'";
          $requete .= "   OR";
          $requete .= "   a.artcode LIKE '%".$recherche."%'";
          $requete .= "   OR";
          $requete .= "   a.artcode_eurocave LIKE '%".$recherche."%'";
          $requete .= ")";
          
     }
}
if(!empty($active))       $requete .= " AND a.active IN ('".implode("','",$active)."')";
if(!empty($commandable))  $requete .= " AND a.commandable IN('".implode("','",$commandable)."')";
if(!empty($type_id))      $requete .= " AND a.typearticleid = ".$type_id;
if(!empty($categorie_id)) $requete .= " AND c.catid = ".$categorie_id;
if(!empty($marque_id))    $requete .= " AND am.marqueid = ".$marque_id;
if(!empty($fournisseur_id)){
     $requete .= " AND a.artid = (";
     $requete .= "     SELECT af.artid";
     $requete .= "     FROM fournisseur_condition fc";
     $requete .= "     INNER JOIN article_fournisseur af ON (af.fournisseur LIKE fc.societe)";
     $requete .= "     WHERE af.artid = a.artid";
     $requete .= "     AND fc.id = ".$fournisseur_id;
     $requete .= ")";
}
if(!empty($reappro)){ //Filtre sur produits en reappro 
    $requete .= " AND a.artid IN (";
    $requete .= "        SELECT artid";
    $requete .= "        FROM article_fournisseur";
    $requete .= "        WHERE reappro > ".time();
    $requete .= "        GROUP BY artid";
    $requete .= ")";  
}
if(!empty($cdc)){ //Filtre sur produits "coups de coeur"   
     $requete .= " AND a.artid = (";
     $requete .= "    SELECT artid";
     $requete .= "    FROM coupdecoeur";
     $requete .= "    WHERE artid = a.artid";
     $requete .= ")";  
}

if(!empty($promo)){ //Filtre sur produits en promo  
     $requete .= " AND a.artid = (";
     $requete .= "    SELECT artid";
     $requete .= "    FROM promo";
     $requete .= "    WHERE artid = a.artid";
     $requete .= ")";  
}
if(!empty($idkdos)){ //Filtre sur idées cadeau 
     $requete .= " AND a.artid = (";
     $requete .= "    SELECT artid";
     $requete .= "    FROM idkdos";
     $requete .= "    WHERE artid=a.artid";
     $requete .= ")";  
}
if(!empty($deselection)){ //Filtre sur "Hors sélection"   
     $requete .= " AND a.artid = (";
     $requete .= "    SELECT artid";
     $requete .= "    FROM article_deselection";
     $requete .= "    WHERE artid = a.artid";
     $requete .= ")";  
}
if(!empty($erreurs)){ //Filtre sur produits en erreur 
     $verif  = verif_articles();
     $artids = array_keys($verif);
     
     $requete .= " AND a.artid IN (".implode(",",$artids).")";  
}

//Requête de sélection des produits
$sql = "SELECT "
     . "a.typearticleid as type_id,"
     . "t.libelle as type_libelle,"
     . "c.catid as categorie_id,"
     . "c.libelleFR as categorie_libelle,"
     . "m.marqueid as marque_id,"
     . "m.libelle as marque_libelle,"
     . "a.artcode,"
     . "a.artid,"
     . "a.artcode_eurocave,"
     . "a.refs_fabricant,"
     . "a.libelleFR as article_libelle,"
     . "a.descriptionFR as article_description,"
     . "a.gammeFR,"
     . "a.prix,"
     . "a.prixht,"
     . "a.prixconseilleht,"
     . "a.ecopart,"
     . "a.ecopartht,"
     . "a.tauxchoisi,"
     . "fc.id as fournisseur_id,"
     . "af.fournisseur,"
     . "a.paht,"
     . "af.delai,"
     . "af.trspt,"
     . "a.poid,"
     . "a.emballage_hauteur1+a.emballage_hauteur2+a.emballage_hauteur3 AS emballage_hauteur,"
     . "a.emballage_largeur1+a.emballage_largeur2+a.emballage_largeur3 AS emballage_largeur,"
     . "a.emballage_profondeur1+a.emballage_profondeur2+a.emballage_profondeur3 AS emballage_profondeur,"
     . "a.emballage_poids1+a.emballage_poids2+a.emballage_poids3 AS emballage_poids,"
     . "a.paysfabrication,"
     . "p.paysid as pays_id,"
     . "p.libelle as pays_libelle";
$sql .= " FROM article a";
$sql .= " LEFT JOIN typearticle t ON (t.typearticleid = a.typearticleid)";
$sql .= " LEFT JOIN categorie c ON (c.catid = a.catid)";
$sql .= " LEFT JOIN article_marque am ON (am.artid = a.artid)";
$sql .= " LEFT JOIN marque m ON (m.marqueid = am.marqueid)";
$sql .= " LEFT JOIN article_fournisseur af ON (af.artid = a.artid AND af.principal = 1)";
$sql .= " LEFT JOIN fournisseur_condition fc ON (fc.societe = af.fournisseur)";
$sql .= " LEFT JOIN pays p ON (p.paysid = a.paysid)";
$sql .= " LEFT JOIN article_history a_h ON (a_h.artid = a.artid)";
$sql .= " WHERE 1";
$sql .= $requete;
$sql .= " GROUP BY a.artcode";

$req = $DB_site->query($sql);
while($row = $DB_site->fetch_array($req)){

     //Formatage des références associées
     $refs_associees = str_replace('<br />', ' | ', nl2br($row['refs_fabricant']));
     
     //Formatage TVA
     switch($row['tauxchoisi']){
          case '1':
               $row['tva'] = '20';
               break;
          case '2':
               $row['tva'] = '5.5';
               break;
          case '3':
               $row['tva'] = '10';
               break;
     }
     
     //Formatage ORIGINE DE FABRICATION
     switch($row['paysfabrication']){
          case '0':
               $row['origine_fab'] = 'France';
               break;
          case '1':
               $row['origine_fab'] = 'Europe';
               break;
          case '2':
               $row['origine_fab'] = 'Asie';
               break;
          case '3':
               $row['origine_fab'] = 'US';
               break;
     }
         
     $sql2 = "SELECT date as date_last_update FROM article_history WHERE artid=".$row['artid']." ORDER BY date_last_update DESC LIMIT 1";
     $req2 = $DB_site->query_first($sql2);

     $tabCSV[] = array(
         $row['type_id'],
         utf8_encode($row['type_libelle']),
         $row['categorie_id'],
         utf8_encode($row['categorie_libelle']),
         $row['marque_id'],
         $row['marque_libelle'],
         $row['artcode'],
         $row['artcode_eurocave'],
         $refs_associees,
         utf8_encode($row['article_libelle']),
         utf8_encode($row['article_description']),
         $row['gammeFR'],
         $row['prix'],
         $row['prixht'],
         $row['prixconseilleht'],
         $row['ecopart'],
         $row['ecopartht'],
         $row['tva'],
         $row['fournisseur_id'],
         $row['fournisseur'],
         $row['paht'],
         $row['delai'],
         $row['trspt'],
         $row['poid']/1000,
         $row['emballage_hauteur'],
         $row['emballage_largeur'],
         $row['emballage_profondeur'],
         $row['emballage_poids'],
         $row['origine_fab'],
         $row['pays_id'],
         $row['pays_libelle'],
         ($req2['date_last_update']!=null ? date('Y-m-d',$req2['date_last_update']) : '')
     );
     
}

//Ajout des lignes dans le fichier
$csv->addrow($tabEntetes,true,false,false,true);
foreach($tabCSV as $ligne_csv){ 
     $csv->addrow($ligne_csv,true,false,false,true);
}

//Lancement du téléchargement
$csv->export();