<?php
require_once __DIR__.'/../../../secrets.php';
ini_set("max_execution_time",180);
ini_set("display_errors",1);

if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false){
    $rootpath = "d:/wamp/www/ma-cave-a-vin/";
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test2015') !== false) {
    $rootpath = "/home/ma-cave-a-vin-2015/";
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test') !== false) {
    $rootpath = $_SERVER['DOCUMENT_ROOT']."/";
} else {
    $rootpath   = "/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
}
$_SERVER['HTTP_HOST']="www.ma-cave-a-vin.fr";
include_once($rootpath."class/host.php");
$host_conf = new Host();
$host=$host_conf->host;
//$rootpath="/var/www/ma-cave-a-vin.fr/";

$vendeur_redoute_id = '11604'; //Num?ro vendeur ? ajouter quand on nous le transmet
        
$nostat = 1;
$passage_ligne = "\r\n";

require_once($rootpath."configurations/$host/config_mysql.php");
require_once($rootpath."class/mysql.php");
require_once($rootpath."class/template.class.php");
require_once($rootpath."admin/mpfields.php");
require_once($rootpath."class/fonctions.php");
require_once($rootpath."class/devises.class.php");
require_once($rootpath."class/stock.class.php");
require_once($rootpath."class/email.php");

// ini_set("display_errors","on");

$libelle     = "libelleFR";
$description = "descriptionFR";

$today = mktime(0,0,0)+86400;

$DB_site2 = new DB_Sql;
$DB_site2->appname='boutique';
$DB_site2->appshortname='boutique';
$DB_site2->database=$DBname;
$DB_site2->server=$db_server;
$DB_site2->user=$DBusername;
$DB_site2->password=$DBpassword;
$DB_site2->connect();

$DB_site = clone $DB_site2;

$template = new template("FR",$DB_site2);

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

function generateBoundary(){
     $caracteres = "abcdefghijklmnopqrstuvwyxz0123456789";
     $nb_caract  = 20;
     
     for($i = 1; $i <= $nb_caract; $i++) {
 
          // On compte le nombre de caract?res
          $nbr = strlen($caracteres);

          // On choisit un caract?re au hasard dans la chaine s?lectionn?e :
          $nbr = mt_rand(0,($nbr-1));

          // Pour finir, on ?crit le r?sultat :
          $pwd .= $caracteres[$nbr];
     }
     
     return $pwd;
}

function createAttachment($sXml, $fileName, $apikey, $rootpath)
    {
          //le flux à envoyer est ouvert et lu
        $fp       = fopen($rootpath."admin/comparateur/log/laredoute/Autourduvin/".$fileName, 'r');
        $contents = fread($fp, filesize($rootpath."admin/comparateur/log/laredoute/Autourduvin/".$fileName));
        fclose($fp);

        $mime_parts = explode('/', "application/xml");

        $part1['type']     = TYPEMULTIPART;
        $part1['subtype']  = 'Related';
     
        //encodage du flux
        $part2['type']             = TYPEAPPLICATION;
        $part2['subtype']          = strtoupper($mime_parts[1]);
        $part2['encoding']         = ENCBASE64;
        $part2['id']               = '<' . basename($fileName) . '>';
        $part2["contents.data"]    = base64_encode($contents);

        // création de la requête WS
        $xmlPart['type']             = 'text';
        $xmlPart['subtype']          = 'XML';
        $xmlPart['id']               = '<main_envelope>';
        $contentsData =  "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ns=\"http://RedouteFrance/seller/items/1.1\"><soapenv:Header/><soapenv:Body><ns:injectItems><api-key>".$apikey."</api-key></ns:injectItems></soapenv:Body></soapenv:Envelope>";   
        $xmlPart['contents.data']    = $contentsData;
        $body[2] = $part1;
        $body[1] = $xmlPart;
        $body[3] = $part2;

        $envelope = array();
     
        //composition de la requête
        return imap_mail_compose($envelope, $body);
    }


function product($article,$coloris){
     global $DB_site2;
     global $host;
     global $template;
     global $class_energy;
     global $type_pose;
     global $porte_vitree;
     global $capacite_bouteilles;
     global $type_cave;
     global $passage_ligne;
     global $vendeur_redoute_id;
     
     //R?cup?ration des valeurs de champs (titre, description...) d?finis dans l'admin
     $sql = "SELECT champ, valeur";
     $sql .= " FROM market_place_catid";
     $sql .= " WHERE market_place = 'redoute'";
     $sql .= " AND catid = '".$article['catid']."'";
     $requete=$DB_site2->query($sql);
     while($champ = $DB_site2->fetch_array($requete)){
          $mp_field[$champ['champ']] = mpfields($article, $champ['valeur'], '<br/><br><br />');

          //Gestion encodage
          $aBR = array('<br/>','<br>','<br />');
          $mp_field[$champ['champ']] = html_entity_decode($mp_field[$champ['champ']]);
          $mp_field[$champ['champ']] = str_replace($aBR,'\r\n',$mp_field[$champ['champ']]);
          $mp_field[$champ['champ']] = htmlentities($mp_field[$champ['champ']], ENT_COMPAT, "UTF-8");
          $mp_field[$champ['champ']] = str_replace('\r\n','<br/>',$mp_field[$champ['champ']]);

          if(strpos($mp_field[$champ['champ']], 'CDATA') == false) $mp_field[$champ['champ']] = str_replace('<br/>', '<![CDATA[<br>]]>', $mp_field[$champ['champ']]);
          /*if(strpos($mp_field[$champ['champ']], 'CDATA') == false) $mp_field[$champ['champ']] = str_replace('<br>', '<![CDATA[<br>]]>', $mp_field[$champ['champ']]);
          if(strpos($mp_field[$champ['champ']], 'CDATA') == false) $mp_field[$champ['champ']] = str_replace('<br />', '<![CDATA[<br>]]>', $mp_field[$champ['champ']]);*/
     }

     //Donn?es marque
     $sql = "SELECT marqueid, libelle, redouteid";
     $sql .= " FROM article_marque";
     $sql .= " INNER JOIN marque USING(marqueid)";
     $sql .= " WHERE artid = '".$article['artid']."'";
     $marque = $DB_site2->query_first($sql);

     //Donn?es code EAN
     $sqlcode = "SELECT codebarre";
     $sqlcode .= " FROM code_barre";
     $sqlcode .= " WHERE artid = '".$article['artid']."'";
     if($coloris != false){
          $sqlcode .= " AND caractvalid = '".intval($coloris['caractvalid'])."'";
     }
     $codebarre = $DB_site2->query_first($sqlcode);

     //Donn?es version de porte (vitr?e ou non)
     $sql = "SELECT rt.*";
     $sql .= " FROM articlerecherche_typeporte at";
     $sql .= " INNER JOIN recherche_typeporte rt ON (rt.recherchetypeporteid = at.recherchetypeporteid)";
     $sql .= " WHERE at.artid = '".$article['artid']."' ";
     $recherche_typeporte = $DB_site2->query_first($sql);     
     
     $imageprincipale = "http://".$host."/configurations/".$host."/images/produits/".$article['artid'].".".$article['image'];
     $image = $imageprincipale;
     
     //Mise en forme du tableau des attributs
     $tabAttributes = array();
     $i = 0;
      
    //Construction du XML
    $xml = ""
     . "<n1:VendorItem>".$passage_ligne
          . "<n1:EAN>".$codebarre[0]."</n1:EAN>".$passage_ligne
          . "<n1:Classification>".$passage_ligne
               . "<n1:ClassificationValue>".$mp_field['ClassificationValue']."</n1:ClassificationValue>".$passage_ligne
          . "</n1:Classification>".$passage_ligne
          . "<n1:Product>".$passage_ligne
               . "<n1:ProductId>".$article['artid']."</n1:ProductId>".$passage_ligne
               . "<n1:ProductTitles>".$passage_ligne
                    . "<n1:ProductTitle language=\"fr\">".$mp_field['ProductTitle']."</n1:ProductTitle>".$passage_ligne
               . "</n1:ProductTitles>".$passage_ligne
               . "<n1:ProductDescriptions>".$passage_ligne
                    . "<n1:ProductDescription language=\"fr\">".$mp_field['ProductDescription']."</n1:ProductDescription>".$passage_ligne
               . "</n1:ProductDescriptions>".$passage_ligne
               . "<n1:ProductBrand>".$passage_ligne
                    . "<n1:BrandCode>".$marque['redouteid']."</n1:BrandCode>".$passage_ligne
               . "</n1:ProductBrand>".$passage_ligne
               . "<n1:Medias>".$passage_ligne
                    . "<n1:Media>".$passage_ligne
                         . "<n1:MediaType>Photo</n1:MediaType>".$passage_ligne
                         . "<n1:MediaURL>".$imageprincipale."</n1:MediaURL>".$passage_ligne
                         . "<n1:MediaEncoding>".$article['image']."</n1:MediaEncoding>".$passage_ligne
                         . "<n1:Checksum>".md5($article['artid'].$marque['marqueid'])."</n1:Checksum>".$passage_ligne
                         . "<n1:ChecksumType>MD5</n1:ChecksumType>".$passage_ligne
                         . "<n1:PublicationRank>1</n1:PublicationRank>".$passage_ligne
                    . "</n1:Media>".$passage_ligne
                    . "<n1:Media>".$passage_ligne
                         . "<n1:MediaType>Logo</n1:MediaType>".$passage_ligne
                         . "<n1:MediaURL>http://".$host."/configurations/www.ma-cave-a-vin.fr/images/marques/br/".$marque['marqueid'].".jpg</n1:MediaURL>".$passage_ligne
                         . "<n1:MediaEncoding>jpg</n1:MediaEncoding>".$passage_ligne
                         . "<n1:Checksum>".md5($marque['marqueid'].$marque['libelle'])."</n1:Checksum>".$passage_ligne
                         . "<n1:ChecksumType>MD5</n1:ChecksumType>".$passage_ligne
                         . "<n1:PublicationRank>2</n1:PublicationRank>".$passage_ligne
                    . "</n1:Media>".$passage_ligne
               . "</n1:Medias>".$passage_ligne
          . "</n1:Product>".$passage_ligne
          . "<n1:Vendor>".$passage_ligne
               . "<n1:VendorId>".$vendeur_redoute_id."</n1:VendorId>".$passage_ligne
          . "</n1:Vendor>".$passage_ligne;
       
        
          
     $xml .= "</n1:VendorItem>".$passage_ligne;

     return $xml;
}

if(!is_dir($rootpath."admin/comparateur/log/laredoute/Autourduvin")){
     mkdir($rootpath."admin/comparateur/log/laredoute/Autourduvin",0777);
}

$filename = $vendeur_redoute_id.".MKP_Products.002.".date('y-m-d-H-i-s').".xml";
$filehost = $rootpath."admin/comparateur/log/laredoute/Autourduvin/".$filename;
$filelink = "http://".$host."/admin/comparateur/log/laredoute/Autourduvin/".$filename;

if(!$handle = fopen($filehost, 'w')){
     echo "Impossible d'ouvrir le fichier (".$filehost.")";
     exit;
}


$sql = "SELECT a.*";
$sql .= " FROM market_place_champs mpc";
$sql .= " INNER JOIN article a ON (a.artcode = mpc.artcode)";
$sql .= " INNER JOIN article_site a_s USING(artid)";
$sql .= " WHERE a_s.active = 1";
$sql .= " AND a.commandable = '1'";
$sql .= " AND mpc.enabled = 1";
$sql .= " AND mpc.market_place LIKE 'redoute'";
$sql .= " AND (SELECT mpc.valeur FROM market_place_catid mpc WHERE mpc.catid = a.catid AND mpc.market_place = 'redoute' AND mpc.champ = 'ClassificationValue') = 'S1086'"; //On récupère uniquement les produits de type "autour du vin"
$sql .= " GROUP BY artcode";
$sql .= " ORDER BY a.artid";
$articles = $DB_site2->query($sql);

$xml = ""
. "<?xml version=\"1.0\" encoding=\"UTF-8\"?>".$passage_ligne
. "<n1:VendorItems xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:item=\"http://RedouteFrance/REF-VENTE/Product/Item/2.0\" xsi:schemaLocation=\"http://RedouteFrance/CAT-EXT-INTG/Product/VendorItems/1.0 file:///S:/Temporaire/BBEL/VendorItems_1.0.xsd\" xmlns:n1=\"http://RedouteFrance/CAT-EXT-INTG/Product/VendorItems/1.0\">".$passage_ligne;

$compteur = 0;
while($article = $DB_site2->fetch_array($articles)){
     
     $xml .= product($article, false);
     
     $compteur++; 

     //if($compteur == 1) break;
}

$xml .= "</n1:VendorItems>";

fwrite($handle, $xml);
fclose($handle);
// chmod($filehost,0777);
$login = LAREDOUTE_API_LOGIN;
$password = LAREDOUTE_API_PASSWORD;
$apikey_commande = LAREDOUTE_API_KEY_COMMANDE;

$request = implode('', explode(PHP_EOL, '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Body><attachment/></SOAP-ENV:Body></SOAP-ENV:Envelope>'));
$request = trim(createAttachment($request, $filename, $apikey_commande, $rootpath));
preg_match('/BOUNDARY=".*"/', $request, $aMatches);
$boundary = 'boundary=' . substr($aMatches[0], strlen('BOUNDARY='));
$basicAuth = base64_encode($login . ":" . $password);

$headers = array(
    'Method: POST',
               'Connection: keep-alive',
               'Content-Type: Multipart/Related; type="TEXT/XML"; start="<main_envelope>"; ' . $boundary,
               'SOAPAction: "http://RedouteFrance/seller/items/1.1"',
               'Authorization: Basic ' . $basicAuth,
               'expect:', //cette ligne est correcte et indespensable
               'mime-version: 1.0'
 ); //SOAPAction: your op URL

$soapUrl = "https://api-gateway.laredoute.com/seller/items"; // asmx URL of WSDL
//echo $request;

$fp = fopen(dirname(__FILE__).'/error-curl-redoute.txt', 'w');

// PHP cURL  for https connection
$ch = curl_init($soapUrl);
curl_setopt_array(
               $ch,
               array(
                    CURLOPT_VERBOSE        => false,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => $request,
                    CURLOPT_HEADER         => false,
                    CURLOPT_HTTPHEADER     => $headers,
                    CURLOPT_SSL_VERIFYPEER => false
               )
          );

$response = curl_exec($ch);
//echo($response);
curl_close($ch);


/*$clean_xml = str_ireplace(['SOAP-ENV:', 'SOAP:', 'ns0:', 'ns'], '', $response);
$response = simplexml_load_string($clean_xml);

foreach($response as $key => $value){
     if(is_object($value) && !empty($value)){
          foreach($value as $key2 => $value2){
               if(is_object($value2) && !empty($value2)){
                    foreach($value2 as $key3 => $value3){
                         if(is_object($value3) && !empty($value3)){
                              foreach($value3 as $key4 => $value4){ 
                                   if(is_object($value4) && !empty($value4)){ 
                                        $message .= 'Message => '.$value4[0].'<br>';
                                   } else {
                                        $message .= $key4.' => '.$value4.'<br>';
                                   }
                              }
                         } else {
                              $message .= $key3.' => '.$value3.'<br>';
                         }
                    }
               } else {
                    $message .= $key2.' => '.$value2.'<br>';
               }
          }
     } else {
          $message .= $key.' => '.$value.'<br>';
     }
}*/

if(isset($_POST['URL']) && $_POST['URL'] != ''){
     $date = date("d-m-Y H:i");
     $action = "Mise a jour des fichiers \"Autour du vin\"";
     $market_place = "laredoute";
     //mise Ã  jour du suivit
     $sql = "INSERT INTO suivi_export SET ";
     $sql .= " user = '$_COOKIE[admin_username]'";
     $sql .= ", time = '$date'";
     $sql .= ", action = '$action'";
     $sql .= ", market_place = '$market_place'";
     $DB_site2->query($sql);
     $_POST['URL'] = $_POST['URL']."&update=1";
     header('Location: '.$_POST['URL']);
     exit;
} else {
     $mail = new email();
     $mail->destinataire = "webmaster1@ma-cave-a-vin.fr";
     //$mail->to           = array("webmaster@ma-cave-a-vin.fr");
     $mail->expname      = "Ma Cave à Vin";
     $mail->expediteur   = "contact@ma-cave-a-vin.fr";
     $mail->subject      = "LaRedoute - Rapport de creation des produits (art de la table) - ".date('d/m/Y H:i:s');
     $mail->body         = $message; 
     $mail->sendmail();
}