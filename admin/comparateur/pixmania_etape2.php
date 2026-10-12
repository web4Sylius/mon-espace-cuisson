<?php
require_once __DIR__.'/../../secrets.php';
//ETAPE 2
//Récupération du résultat de l'intégration
//Pour les produits en statut FAI (matching EAN introuvable) => Etape 2.5 : Création du fichier récupéré par Pixmania pour création des FUPID
//Pour les produits en statut CRE (matching EAN trouvé), récupérer le FUPID créé => Etape 3
require_once("../admin_global.php");
ini_set("max_execution_time",180);
ini_set("display_errors","on");

$rootpath   = ROOT_PATH;

$host="www.ma-cave-a-vin.fr" ;
$nostat=1;
require_once($rootpath."configurations/$host/config_mysql.php");
require_once($rootpath."class/mysql.php");
require_once($rootpath."class/template.class.php");
require_once($rootpath."admin/mpfields.php");
require_once($rootpath."admin/export/xlexport.php");
require_once($rootpath."class/fonctions.php");
require_once($rootpath."class/devises.class.php");
require_once($rootpath."class/stock.class.php");

$DB_site2=new DB_Sql;
$DB_site2->appname='boutique';
$DB_site2->appshortname='boutique';
$DB_site2->database=$DBname;
$DB_site2->server=$db_server;
$DB_site2->user=$DBusername;
$DB_site2->password=$DBpassword;
$DB_site2->connect();

$DB_site = clone $DB_site2;

$template = new template("FR",$DB_site2);

$params = array(
    'tag'  => 'etape1',
    'mpid' => 9
);
$last_revision = getLastRevision($params);

//Paramétrage pour envoi fichier par méthode POST (cURL)
$sSerial     = 'fb08ffbc168c00fe0171bc486788e271';
$sHttpLogin  = PIXMANIA_HTTP_LOGIN;
$sHttpPwd    = PIXMANIA_HTTP_PASSWORD;
$sCalledurl  = 'https://pixplace-ws.pixmania.com/index.php?d=webServices_Server&c=ServerRest&rm=exportFile&rf=getReportFile&PARENT_FILE_ID='.$last_revision.'&FILE_TYPE_CODE=MPD&FILE_CONTENT_TYPE_CODE=[FILE_TYPE]&sl='.$sSerial;      

function getLastRevision($params){
     global $DB_site2;
     
     $sql = "SELECT market_place_suivi_flag";
     $sql .= " FROM market_place_suivi";
     $sql .= " WHERE 1";
     $sql .= (isset($params['mpid']) && !empty($params['mpid']) ? " AND market_place_id = '".$params['mpid']."'" : "");
     $sql .= (isset($params['tag']) && !empty($params['tag']) ? " AND market_place_suivi_tag = '".$params['tag']."'" : "");
     $sql .= " ORDER BY market_place_suivi_date DESC";
     $sql .= " LIMIT 0,1";
     $last_revision = $DB_site2->query_first($sql);
     
     return $last_revision['market_place_suivi_flag'];
}

function nice($var, $color = 'black'){
          echo "<div style=\"color:".$color.";\">";
          echo "<p style=\"color:red; font-weight:bold;\">Contenu de la variable :</p>";
          if(is_array($var) || is_object($var)){
                    echo "<pre>";
                    print_r($var);
                    echo "</pre>";   
          } else {
                    var_dump($var);
          }
          echo "</div>";
}

function nicex($var){
          nice($var);
          exit();
}

function addSuiviMP($params){
     global $DB_site2;
     
     $sql = "INSERT INTO market_place_suivi (";
     $sql .= "market_place_suivi_tag";
     $sql .= ", market_place_suivi_contenu";
     $sql .= ", market_place_suivi_flag";
     $sql .= ", market_place_suivi_date";
     $sql .= ", market_place_id";
     $sql .= ") VALUES (";
     $sql .= (isset($params['tag']) && !empty($params['tag']) ? "'".$params['tag']."'" : "''");
     $sql .= ", ".(isset($params['description']) && !empty($params['description']) ? "'".addslashes($params['description'])."'" : "''");
     $sql .= ", ".(isset($params['flag']) && !empty($params['flag']) ? "'".$params['flag']."'" : "''");
     $sql .= ", NOW()";
     $sql .= ", ".(isset($params['mpid']) && !empty($params['mpid']) ? "'".$params['mpid']."'" : "''");
     $sql .= ")";
     $DB_site2->query($sql); 
}

function texte_info($texte, $couleur = ''){
     echo "<br><span ".(!empty($couleur) ? 'style="color:'.$couleur.'"' : '')."\">".$texte."...</span>";
}

function get_curl($filetype){
     global $sCalledurl;
     global $sHttpLogin;
     global $sHttpPwd;
     
     $oCurl = curl_init();
     //Option: Url to call
     curl_setopt( $oCurl, CURLOPT_URL, str_replace('[FILE_TYPE]',$filetype,$sCalledurl));
     //Option: Do not display the result but store it in a variable
     curl_setopt( $oCurl, CURLOPT_RETURNTRANSFER, 1);
     //Option: Set HTTPS Login And password
     curl_setopt( $oCurl, CURLOPT_USERPWD, $sHttpLogin.':'.$sHttpPwd );
     curl_setopt( $oCurl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
     curl_setopt( $oCurl, CURLOPT_UNRESTRICTED_AUTH, true);
     curl_setopt( $oCurl, CURLOPT_SSL_VERIFYHOST, 0);
     curl_setopt( $oCurl, CURLOPT_SSL_VERIFYPEER, false);
     //Launch Curl Request
     $sCurlResponse = curl_exec( $oCurl ); 
     
     return $sCurlResponse;
}

function get_fileid($chaine){
     $chaine = strrchr($chaine, '-');
     $tabRetour = explode('=>',$chaine);
     $file_id = trim($tabRetour[1]);
     
     return $file_id;
}

function read_csv($nom_fichier, $separateur =";"){
    $row = 0;
    $donnee = array();    
    $f = fopen ($nom_fichier,"r");
    $taille = filesize($nom_fichier)+1;
    while ($donnee = fgetcsv($f, $taille, $separateur)) {
        $result[$row] = $donnee;
        $row++;
    }
    fclose ($f);
    return $result;
}

function getSuivi($params = array()){
     global $DB_site2;
     
     $tabReturn = array();
     $sql = "SELECT *";
     $sql .= " FROM market_place_suivi";
     $sql .= " WHERE 1";
     if(isset($params['etape']) && !empty($params['etape'])) $sql .= " AND market_place_suivi_tag = '".$params['etape']."'";
     if(isset($params['file_id']) && !empty($params['file_id'])) $sql .= " AND market_place_suivi_flag = '".$params['file_id']."'";
     $sql .= " ORDER BY market_place_suivi_date DESC";
     $sql .= " LIMIT 0,1";
     $tabReturn = $DB_site2->query_first($sql);
     
     return $tabReturn;
}

//Récupération du rapport d'upload
texte_info("Récupération du rapport d'upload (Etape 1 : IPT)","blue");

$curlResponse = get_curl('IPT');

if(!empty($curlResponse)){
     
     //Récupération du FILEID pour vérifié que le log n'a pas déjà été enregistré
     $file_id = get_fileid($curlResponse);
     $tabReturn = getSuivi(
          array(
              'etape'   => 'etape2-1',
              'file_id' => $file_id
          )
     );
     
     if(empty($tabReturn)){
          
          $params_suivi = array(
               "tag"         => "etape2-1",
               "description" => "Le fichier a correctement été publié durant l'étape 1. Message de Pixmania : ".addslashes($curlResponse),
               "mpid"        => 9,
               "flag"        => $file_id
          );
          
          texte_info($params_suivi['description'],"green");
          
          //Ajout du résultat dans le suivi
          addSuiviMP($params_suivi);
     
     } else {
          
          texte_info("Le fichier a correctement été publié durant l'étape 1, identifiant de fichier : ".$file_id,"green");
          
     }
     
} else {
     texte_info("Pixmania n'a pas encore accusé réception du fichier","red");
     exit;
}

//Récupération du rapport d'upload
texte_info("<br>Récupération du rapport d'analyse (Etape 2 : ITG)","blue");

$curlResponse = get_curl('ITG');

if(!empty($curlResponse)){
     
     //Récupération du FILEID pour vérifié que le log n'a pas déjà été enregistré
     $tabEtape1 = getSuivi(
          array(
              'etape'   => 'etape2-1'
          )
     );
     
     $file_id = "";
     if(!empty($tabEtape1['market_place_suivi_flag'])) $file_id = $tabEtape1['market_place_suivi_flag'];
     
     $tabReturn = getSuivi(
          array(
              'etape'   => 'etape2-2',
              'file_id' => $file_id
          )
     );
     
     if(empty($tabReturn)){
          
          $params_suivi = array(
               "tag"         => "etape2-2",
               "description" => "Le fichier est en cours d'analyse chez Pixmania. Message de Pixmania : ".addslashes($curlResponse),
               "mpid"        => 9,
               "flag"        => $file_id
          );
          
          texte_info($params_suivi['description'],"green");
          
          //Ajout du résultat dans le suivi
          addSuiviMP($params_suivi);
     
     } else {
          
          texte_info("L'analyse du fichier n'a pas encore commencé","red");
          
     }
}

//Récupération du rapport de réponse
texte_info("<br>Récupération du rapport de réponse (Etape 3 : RPT)","blue");

$curlResponse = get_curl('RPT');

if(!empty($curlResponse)){
     
     //Récupération du FILEID pour vérifié que le log n'a pas déjà été enregistré
     $tabEtape1 = getSuivi(
          array(
              'etape'   => 'etape2-2'
          )
     );
     
     $file_id = "";
     if(!empty($tabEtape1['market_place_suivi_flag'])) $file_id = $tabEtape1['market_place_suivi_flag'];
     
     $tabReturn = getSuivi(
          array(
              'etape'   => 'etape2-3',
              'file_id' => $file_id
          )
     );
     
     if(empty($tabReturn)){
     
          $params_suivi = array(
               "tag"         => "etape2-3",
               "description" => "Le fichier a bien été traité. Le résultat est consultable à l'adresse : https://www.ma-cave-a-vin.fr/admin/export/pixmania/rapport_reponse_".strtotime(date("d-m-Y H:i"))."csv",
               "mpid"        => 9,
               "flag"        => $file_id
          );

          texte_info($params_suivi['description'],"green");

           //Ajout du résultat dans le suivi
          addSuiviMP($params_suivi);
     
     }

      $nom_fichier = $rootpath."/admin/export/pixmania/rapport_reponse_".strtotime(date("d-m-Y H:i"))."csv";

      $handle = fopen($nom_fichier, "w");
      fwrite($handle, $curlResponse);
      fclose($handle);

      //Lecture du fichier CSV retourné
      $tabCSV = read_csv($nom_fichier);

      foreach($tabCSV as $ligne){

           //Récupération des FUPID pour les produits en état CRE (matching effectué) => Le FUPID sera utilisé pour la mise à jour prix/stock, effectué dans l'étape 3
           if(isset($ligne[12]) && $ligne[12] == 'CRE'){
                $sql = "UPDATE code_barre SET";
                $sql .= " fupid = '".$ligne[13]."'";
                $sql .= " WHERE codebarre = '".$ligne[0]."'";
                $DB_site2->query($sql);
           }

           //Stockage des EAN pour insertion dans CSV pour demandede création des FUPID
           if(isset($ligne[12]) && $ligne[12] == 'FAI'){

                $tabEAN[] = $ligne[0];

           }

      }

      //Génération du CSV de demande de génération des FUPID manquants (on le vide à chaque nouveau lancement)
      texte_info("<br>Génération du fichier de création des FUPID","blue");

      $csv =  new xlexport("csv");
      $csv->name       = "pixmania_produits";
      $csv->csvsep     = ";";
      $csv->striptags  = false;
      $filename_source = $rootpath.'admin/export/pixmania/'.$csv->name.'.csv';
      $filename_lien   = 'https://www.ma-cave-a-vin.fr/admin/export/pixmania/'.$csv->name.'.csv';

      $head = array(
           "SKU",
           "Langue",
           "CTRY",
           "EAN",
           "ID Segment",
           "ID Marque",
           "Libellé",
           "Description marketing",
           "URL Image"
      );
      $csv->addrow($head, false, true);

      if(!empty($tabEAN)){

           texte_info(count($tabEAN)." FUPID manquants","red");

           foreach($tabEAN as $l_ean){

                $sql = "SELECT a.*, m.*";
                $sql .= " FROM code_barre cb";
                $sql .= " INNER JOIN article a ON (a.artid = cb.artid)";
                $sql .= " INNER JOIN article_marque am ON (am.artid = a.artid)";
                $sql .= " INNER JOIN marque m ON (m.marqueid = am.marqueid)";
                $sql .= " WHERE cb.codebarre = ".$l_ean;
                $tabArticle = $DB_site2->query_first($sql);

                //On n'exporte que les produits dont la marque est renseignée
                $sql = "SELECT champ, valeur";
                $sql .= " FROM market_place_catid";
                $sql .= " WHERE market_place = 'pixmania'";
                $sql .= " AND catid = '".$tabArticle['catid']."'";
                $requete = $DB_site2->query($sql);
                $insert_article = false;
                while($champ = $DB_site2->fetch_array($requete)){
                    $insert_article = true;
                    $mp_field[$champ['champ']] = mpfields($tabArticle, $champ['valeur'],"<br/><br><br />");
                    $aBR = array('<br/>','<br>','<br />');
                    $mp_field[$champ['champ']] = html_entity_decode($mp_field[$champ['champ']]);
                    $mp_field[$champ['champ']] = str_replace($aBR,'\r\n',$mp_field[$champ['champ']]);
                    $mp_field[$champ['champ']] = htmlentities($mp_field[$champ['champ']], ENT_COMPAT, "UTF-8");
                    $mp_field[$champ['champ']] = str_replace('\r\n','<br/>',$mp_field[$champ['champ']]);

                    if(strpos($mp_field[$champ['champ']], 'CDATA') == false) $mp_field[$champ['champ']] = str_replace('<br/>', '<![CDATA[<br>]]>', $mp_field[$champ['champ']]);
               }

                if($insert_article){
                    $line[0] = $tabArticle['artid']; //SKU
                    $line[1] = "FR"; //Langue
                    $line[2] = "FR"; //CTRY
                    $line[3] = $l_ean; //EAN
                    $line[4] = $mp_field['segment']; //ID Segment
                    $line[5] = $tabArticle['pixid']; //ID Marque
                    $line[6] = $mp_field['title']; //Libellé
                    $line[7] = str_replace(';','-',$mp_field['description']); //Description marketing
                    $line[8] = "https://www.ma-cave-a-vin.fr/configurations/www.ma-cave-a-vin.fr/images/produits/".$tabArticle['artid'].".jpg"; //URL Image
                    $csv->addrow($line, false, true);
                }
           }

      } else {

           texte_info("Aucun FUPID manquant","green");

      }

      //Création du fichier CSV sur le serveur de MCAV
      $handle = fopen($filename_source,"w");
      if($handle){

           fputs($handle,$csv->buffer);
           fclose($handle);

           $params_suivi = array(
                "tag"         => "etape2-4",
                "description" => "Le fichier de création d'export contenant les articles FAI (a créer chez Pixmania) a été créé : ".$filename_lien,
                "mpid"        => 9,
                "flag"        => $file_id
           );

           texte_info($params_suivi['description'],"green");

           //Ajout du résultat dans le suivi
           addSuiviMP($params_suivi);

      } else {

           $params_suivi = array(
                "tag"         => "etape2-4",
                "description" => "Erreur lors de l'écriture du fichier CSV",
                "mpid"        => 9,
                "flag"        => $file_id
           );

           texte_info($params_suivi['description'],"red");

           //Ajout du résultat dans le suivi
           addSuiviMP($params_suivi);
      }
}

if(isset($_POST['URL']) && $_POST['URL'] != ''){
	$date = date("d-m-Y H:i");
	$action = "Recuperation de la liste des produits pouvant etre mis en ligne";
	$market_place = "pixmania";
	//mise à jour du suivit
	$sql = "INSERT INTO suivi_export SET ";
	$sql .= " user = '$_COOKIE[admin_username]'";
	$sql .= ", time = '$date'";
	$sql .= ", action = '$action'";
	$sql .= ", market_place = '$market_place'";
	$DB_site2->query($sql);
	$_POST['URL'] = $_POST['URL']."&update=1";
}