<?php
require_once __DIR__.'/../../secrets.php';
//ETAPE 1
//Génération d'un fichier CSV de matching, pour comparer les EAN déjà existants sur PixPlace avec les notres
//Une fois généré, envoi par le biais d'un webservice, en méthode POST
//Attente du rapport d'intégration avant passage à étape 2
require_once("../admin_global.php");
ini_set("max_execution_time",180);
ini_set("display_errors","on");

$rootpath = ROOT_PATH;

$host="www.ma-cave-a-vin.fr" ;
$nostat=1;
require_once($rootpath."configurations/$host/config_mysql.php");
require_once($rootpath."class/mysql.php");
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

$csv = new xlexport("csv");
$csv->name       = "pixplace_matching_ean";
$csv->csvsep     = ";";

//Paramétrage pour copie fichier sur serveur local
$mcav_dossier    = 'admin/export/fnac/';
$filename_source = $rootpath.$mcav_dossier.$csv->name.'.csv';
$filename_link   = 'https://www.ma-cave-a-vin.fr/'.$mcav_dossier.$csv->name.'.csv';

//Paramétrage pour envoi fichier par méthode POST (cURL)
$sSerial     = 'fb08ffbc168c00fe0171bc486788e271';
$sHttpLogin  = PIXMANIA_HTTP_LOGIN;
$sHttpPwd    = PIXMANIA_HTTP_PASSWORD;
$sCalledurl  = 'https://pixplace-ws.pixmania.com/index.php?d=webServices_Server&c=ServerRest';
$aParameters = array(
    'rm'       => 'importFileContr',
    'rf'       => 'importMatching',
    'sl'       => $sSerial, 
    'FILENAME' => '@'.$filename_source //The path of the file to load - Do not forget '@' before the file to be called
);      

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

texte_info("Lancement de l'export","blue");

/*$csv->addrow(
     array(
          "Code EAN (13 chiffres)",
          "Champ vide",
          "Champ vide",
          "Langue",
          "Sku - Id interne du produit"
     )
);*/

texte_info("Récupération des articles à exporter","blue");

//Récupération de tous les articles a importer sur Pixmania
$sql = "SELECT *";
$sql .= " FROM market_place_champs mpc";
$sql .= " INNER JOIN article a ON (a.artcode = mpc.artcode)";
$sql .= " INNER JOIN code_barre c ON (c.artid = a.artid)";
$sql .= " WHERE mpc.market_place = 'pixmania'";
$sql .= " AND mpc.enabled = '1'";
$sql .= " AND a.active = '1'";
$sql .= " AND a.commandable = '1'";
$sql .= " GROUP BY a.artid";
$articles = $DB_site2->query($sql);

texte_info("Génération des lignes produits avec : EAN / (vide) / (vide) / Langue / SKU","blue");

while ($article = $DB_site2->fetch_array($articles)){
     
     $tabLivraison = delailivraison($article['artid']);
     if(empty($tabLivraison['date_appro']) && $article['date_appro'] > 0) $tabLivraison['date_appro'] = $article['date_appro'];
     
     //Si date d'appro inférieure aux 7 prochains jours, on ajoute
     if(empty($tabLivraison['date_appro']) || mktime(0,0,0,date('m'),date('d')+7,date('Y')) >= $tabLivraison['date_appro']){
          
          $csv->addrow(
               array(
                   $article['codebarre'], //Code EAN
                   "", //Champs vide
                   "", //Champs vide
                   "FR", //Langue
                   $article['artid'] //SKU
               )
          );
          
     }
}

texte_info("Fichier complet : copie sur le serveur de Ma Cave à Vin","blue");

//Suppression du fichier précédent
@unlink($rootpath.$mcav_dossier.$csv->name.'.csv');

//Création du fichier CSV sur le serveur de MCAV
$f=fopen($filename_source,"w");
if($f){
     fputs($f,$csv->buffer);
     fclose($f);
     
     texte_info("Fichier généré avec succès. Consultez le <a href=\"".$filename_link."\">ici</a>","green");
} else {
     texte_info("Erreur lors de la génération du fichier. Le traitement a été stoppé.","red");
     exit;
}

texte_info("<br>Préparation à l'envoi à Pixmania","blue");
$oCurl = curl_init();
//Option: Url to call       
curl_setopt( $oCurl, CURLOPT_URL, $sCalledurl );
//Option: Do not display the result but store it in a variable
curl_setopt( $oCurl, CURLOPT_RETURNTRANSFER, 1);

texte_info("Identification","blue");
//Option: Set HTTPS Login And password
curl_setopt( $oCurl, CURLOPT_USERPWD, $sHttpLogin.':'.$sHttpPwd );
curl_setopt( $oCurl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
curl_setopt( $oCurl, CURLOPT_UNRESTRICTED_AUTH, true);
curl_setopt( $oCurl, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt( $oCurl, CURLOPT_SSL_VERIFYPEER, false);
//Option: Active HTTP Post
curl_setopt( $oCurl, CURLOPT_POST, 1);
curl_setopt( $oCurl, CURLOPT_HTTPHEADER, array("Expect:") );
//Option: Set HTTP Parameters
curl_setopt( $oCurl, CURLOPT_POSTFIELDS, $aParameters );

texte_info("Envoi du fichier","blue");
//Launch Curl Request
$sCurlResponse = curl_exec( $oCurl );

if(empty($sCurlResponse)){
     
     $params_suivi = array(
         "tag"         => "etape1",
         "description" => "Erreur lors du transfert. Le fichier n'a pas été envoyé",
         "mpid"        => 9
     );
     
     texte_info($params_suivi['description'],"red");
     
} else {
     
     //Récupération du numéro de fichier d'intégration
     $chaine = strrchr($sCurlResponse, '-');
     $tabRetour = explode('=>',$sCurlResponse);
     $file_id = trim($tabRetour[1]);
     
     $params_suivi = array(
         "tag"         => "etape1",
         "description" => "Fichier transféré. Réponse du serveur : ".$sCurlResponse,
         "mpid"        => 9,
         "flag"        => $file_id
     );
     
     texte_info($params_suivi['description'],"green");
} 

//Ajout du résultat dans le suivi
addSuiviMP($params_suivi);

if(isset($_POST['URL']) && $_POST['URL'] != ''){
	$date = date("d-m-Y H:i");
	$action = "Mise a jour de la liste des produits pouvant etre mis en ligne";
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