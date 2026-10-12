<?php

require "admin_global.php";
//include "./header.php";
//include "./menu.php";
require_once(BACK_PATH."/export/xlexport.php");
header('Content-Type: text/html; charset=utf-8');
$DB_site=$DB_site2;
    $csv     = new xlexport("csv");

          $csv->name      = "export_articles_".date('dmY_his');
          $csv->csvsep    = ";";
          $csv->striptags = false;

          $tabEntetes = array(
               'Date inscription',
               'Nom',
               'Prénom',
               'Adresse',
               'Code Postal',
               'Ville',
               'Mail',
               'Téléphone'
          );
          
          //Filtre recherche sur "nom/société/email"
          if(isset($_GET['src']) && $_GET['src']!=""){
               $src = " AND (";
               $src .= " u.nom LIKE '%".mysqli_real_escape_string($DB_site->link_id,$_GET['src'])."%'";
               $src .= " OR u.raisonsociale LIKE '%".mysqli_real_escape_string($DB_site->link_id,$_GET['src'])."%'";
               $src .= " OR u.userid='".mysqli_real_escape_string($DB_site->link_id,$_GET['src'])."'";
               $src .= " OR u.mail LIKE '".mysqli_real_escape_string($DB_site->link_id,$_GET['src'])."'";
               $src .= " OR u.telephone LIKE '".mysqli_real_escape_string($DB_site->link_id,$_GET['src'])."'";
               //Utilisateur Associé
			   $src .= " OR ua.utilisateur_assoc_nom LIKE '%".mysqli_real_escape_string($DB_site->link_id,$_GET['src'])."%'";
			   $src .= " OR ua.userid='".mysqli_real_escape_string($DB_site->link_id,$_GET['src'])."'";
			   $src .= " OR ua.utilisateur_assoc_email LIKE '".mysqli_real_escape_string($DB_site->link_id,$_GET['src'])."'";
               $src .= " OR ua.utilisateur_assoc_tel LIKE '".mysqli_real_escape_string($DB_site->link_id,$_GET['src'])."'";
               $src .= ")";
          }
          
          //Filtre recherche sur "département / code postal"
          if(isset($_GET['dpt']) && $_GET['dpt']!=""){
               if(strlen($_GET['dpt']) > 3){ //Code postal
                    $src .= " AND codepostal = '".$_GET['dpt']."'";
               } else { //Département
                    $src .= " AND codepostal LIKE '".$_GET['dpt']."%'";
               }
          }
          
          //Filtre recherche "Commercial"
          if(isset($_GET['commercial']) && $_GET['commercial']!="0"){
               $src .= " AND commercial='".$_GET['commercial']."'";
          }
          
          //Filtre recherche "Statut client"
          if(isset($_GET['encours']) && !empty($_GET['encours'])){
               switch($_GET['encours']){
                    //Statut "en cours"
                    case 1:
                         $src .= " AND (";
                         $src .= "      SELECT etat";
                         $src .= "      FROM suivi_client";
                         $src .= "      WHERE userid = u.userid";
                         $src .= "      ORDER BY date DESC LIMIT 1";
                         $src .= ") = 1 AND (";
                         $src .= "      SELECT valide";
                         $src .= "      FROM facture";
                         $src .= "      WHERE userid = u.userid";
                         $src .= "      AND supprimee = '0'";
                         $src .= "      ORDER BY heurefacture DESC";
                         $src .= "      LIMIT 1";
                         $src .= ") = 0";
                         break;
                    //Statut "en commande"
                    case 2:
                         $src .= " AND (";
                         $src .= "      SELECT valide";
                         $src .= "      FROM facture";
                         $src .= "      WHERE userid = u.userid";
                         $src .= "      AND supprimee = '0'";
                         $src .= "      ORDER BY heurefacture DESC";
                         $src .= "      LIMIT 1";
                         $src .= ") IN (1,5,7,8)";
                         break;
                    //Statut "suivi automatique
                    case 3:
                    break;
                    //Statut "dossier sous-traité"
                    case 4:
                    $src .= " AND (";
                    $src .= "      SELECT etat";
                    $src .= "      FROM suivi_client";
                    $src .= "      WHERE userid = u.userid";
                    $src .= "      ORDER BY date DESC";
                    $src .= "      LIMIT 1";
                    $src .= ") = 4";
                    break;
                    //Statut "perdu"
                    case "0":
                    $src.=" AND (";
                    $src .= "      SELECT etat";
                    $src .= "      FROM suivi_client";
                    $src .= "      WHERE userid = u.userid";
                    $src .= "      ORDER BY date DESC";
                    $src .= "      LIMIT 1";
                    $src .= ") = 0";
                    break;
                    //statut "non fidélisé"
                    case "nf":
                    $src.=" AND (";
                    $src .= "      SELECT COUNT(*)";
                    $src .= "      FROM fidelisation";
                    $src .= "      WHERE userid = u.userid";
                    $src .= ") = 0";
                    $src .= " AND (";
                    $src .= "      SELECT valide";
                    $src .= "      FROM facture";
                    $src .= "      WHERE userid = u.userid";
                    $src .= "      AND supprimee = '0'";
                    $src .= "      ORDER BY heurefacture DESC";
                    $src .= "      LIMIT 1";
                    $src .= ") IN (5,8)";
                    break;
                    //statut "en fidélisation"
                    case "fid":
                    $src .=" AND (";
                    $src .= "      SELECT COUNT(*)";
                    $src .= "      FROM fidelisation";
                    $src .= "      WHERE userid = u.userid";
                    $src .= "      AND (";
                    $src .= "           date > UNIX_TIMESTAMP(NOW())";
                    $src .= "           OR periode > 0";
                    $src .= "      )";
                    $src .= ") > 0";
                    break;
               }
          }
          
          //Filtre recherche "Type de client"
          if(isset($_GET['type']) && !empty($_GET['type'])){
               switch($_GET['type']){
                    case 'part':
                         $src .= " AND raisonsociale = ''";
                         break;
                    case 'pro':
                         $src .=" AND raisonsociale <> ''";
                         break;
               }
          }
          
          //Filtre recherche "Profil client"
          if(isset($_GET['vip']) && $_GET['vip']!=""){
               $src .= " AND vip = ".$_GET['vip'];
          }

          //Filtre recherche "Code 401"
          if(isset($_GET['code401']) && $_GET['code401']!=""){
               $src .= " AND u.code411 ".($_GET['code401'] == 1 ? '!=' : '=')." ''";
          }

          //Filtre recherche "Provenance"
          if(isset($_GET['prov']) && $_GET['prov'] !=""){
          	   $src .= " AND u.provenance ".($_GET['prov'] == 1 ? '!=' : '=')." 'mobile'";
          }

          //Filtre recherche "Type de suivi"
          if(isset($_GET['suivi']) && $_GET['suivi'] !=""){
          	   $src .= " AND u.utilisateur_type_suivi_id = ".$_GET['suivi'];
          }
          
          //Filtre recherche "Inscrit à la newsletter"
          if(isset($_GET['newsletter']) && $_GET['newsletter'] !="")
          {
          		if ($_GET['newsletter']== "1")
          		{
          			$src .= " AND u.allowemail = '1'";
          		}
          		elseif ($_GET['newsletter']=="2")
          		{
          			$src .= " AND u.allowemail = '0'";
          		}
          		
          }

          //Filtre recherche "Inscrit au SMS"
          if(isset($_GET['SMS']) && $_GET['SMS'] !="")
          {
          		if ($_GET['SMS']== "1")
          		{
          			$src .= " AND u.allowsms = '1'";
          		}
          		elseif ($_GET['SMS']=="2")
          		{
          			$src .= " AND u.allowsms = '0'";
          		}
          		
          }

          //filtre recherche date de début //filtre recherche date de fin
          if(isset($_GET['orderdate']) && $_GET['orderdate'] !="" && isset($_GET['deliverdate']) && $_GET['deliverdate'] !="")
          {
          	$date_debut_tab = explode('/', $_GET['orderdate']);
          	$date_debut_tab_bis = explode(' ', $date_debut_tab[2]);
          	$date_debut = $date_debut_tab_bis[0].'-'.$date_debut_tab[1].'-'.$date_debut_tab[0];

          	$date_fin_tab = explode('/', $_GET['deliverdate']);
          	$date_fin_tab_bis = explode(' ', $date_fin_tab[2]);
          	$date_fin = $date_fin_tab_bis[0].'-'.$date_fin_tab[1].'-'.$date_fin_tab[0];
          	$src .= " AND u.dateinscription BETWEEN  \"".$date_debut."\" AND \"".$date_fin."\"";
          }

          
          //Construction de la requête à partir des filtres précédents
          $sql = "FROM utilisateur u";
          if(isset($_GET['src']) && $_GET['src']!="") $sql .= " LEFT JOIN utilisateur_assoc ua USING(userid)";
          $sql .= " WHERE 1";
          $sql .= $src;
          
          //Filtre sur clients à rappeler aujourd'hui
          if(isset($_GET['calltoday']) && $_GET['calltoday'] == 1){
               $sql = " FROM suivi_client sc";
               $sql .= " INNER JOIN utilisateur u ON (u.userid = sc.userid)";
               $sql .= " WHERE (";
               $sql .= "      SELECT rappel";
               $sql .= "      FROM suivi_client";
               $sql .= "      WHERE userid = u.userid";
               $sql .= "      ORDER BY date DESC";
               $sql .= "      LIMIT 1";
               $sql .= ") BETWEEN '".mktime(0,0,0,date("m"),date("d"),date("Y"))."' AND '".$tomorrow."'";
               $sql .= " GROUP BY sc.userid";
          }

          //echo $sql;
          //Récupération du nombre de résultats
          $sql2 = " SELECT * ".$sql;
          $req = $DB_site2->query($sql2);
          while($row = $DB_site->fetch_array($req))
          {
               $tab_inscription = explode('-',$row['dateinscription']);
               $tab_inscription_deb = explode(' ',$tab_inscription[2]);
               $dateinscription = $tab_inscription_deb[0].'/'.$tab_inscription[1].'/'.$tab_inscription[0];
                $tabCSV[] = array(
                   $dateinscription,
                   $row['nom'],
                   $row['prenom'],
                   $row['adresse'],
                   $row['codepostal'],
                   $row['ville'],
                   $row['mail'],
                   $row['telephone']
               );
          }	
          //Ajout des lignes dans le fichier
          $csv->addrow($tabEntetes);
          foreach($tabCSV as $ligne_csv){ 
               $csv->addrow($ligne_csv);
          }
          $csv->buffer = chr(239) . chr(187) . chr(191) . $csv->buffer; // Permet au fichier csv de s'ouvrir en utf8
          //Lancement du téléchargement
          $csv->export();
      
?>
