<?php
ini_set("max_execution_time",180);
ini_set("display_errors",1);

require_once($_SERVER['DOCUMENT_ROOT']."/class/host.php");
$host_conf = new Host();
$host=$host_conf->host;
require_once($_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/config_variables.php");

$vendeur_redoute_id = '11604'; //Numéro vendeur à ajouter quand on nous le transmet
        

$nostat = 1;

require_once($rootpath."configurations/$host/config_mysql.php");
require_once($rootpath."class/mysql.php");
require_once($rootpath."class/template.class.php");
require_once($rootpath."admin/mpfields.php");
require_once($rootpath."class/fonctions.php");
require_once($rootpath."class/devises.class.php");
require_once($rootpath."class/stock.class.php");

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
 
          // On compte le nombre de caractères
          $nbr = strlen($caracteres);

          // On choisit un caractère au hasard dans la chaine sélectionnée :
          $nbr = mt_rand(0,($nbr-1));

          // Pour finir, on écrit le résultat :
          $pwd .= $caracteres[$nbr];
     }
     
     return $pwd;
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
     global $vendeur_redoute_id;
     
     //Récupération des valeurs de champs (titre, description...) définis dans l'admin
     $sql = "SELECT champ, valeur";
     $sql .= " FROM market_place_catid";
     $sql .= " WHERE market_place = 'redoute'";
     $sql .= " AND catid = '".$article['catid']."'";
     $requete=$DB_site2->query($sql);
     while($champ = $DB_site2->fetch_array($requete)){
          $mp_field[$champ['champ']] = mpfields($article, $champ['valeur'],'<br/><br><br />');
     }
     
     //Données marque
     $sql = "SELECT marqueid, libelle, redouteid";
     $sql .= " FROM article_marque";
     $sql .= " INNER JOIN marque USING(marqueid)";
     $sql .= " WHERE artid = '".$article['artid']."'";
     $marque = $DB_site2->query_first($sql);

     //Données code EAN
     $sqlcode = "SELECT codebarre";
     $sqlcode .= " FROM code_barre";
     $sqlcode .= " WHERE artid = '".$article['artid']."'";
     if($coloris != false){
          $sqlcode .= " AND caractvalid = '".intval($coloris['caractvalid'])."'";
     }
     $codebarre = $DB_site2->query_first($sqlcode);

     //Données version de porte (vitrée ou non)
     $sql = "SELECT rt.*";
     $sql .= " FROM articlerecherche_typeporte at";
     $sql .= " INNER JOIN recherche_typeporte rt ON (rt.recherchetypeporteid = at.recherchetypeporteid)";
     $sql .= " WHERE at.artid = '".$article['artid']."' ";
     $recherche_typeporte = $DB_site2->query_first($sql);     
     
     $imageprincipale = "https://".$host."/configurations/".$host."/images/produits/article/".$article['artid'].".".$article['image'];
     $image = $imageprincipale;
     
     //Mise en forme du tableau des attributs
     $tabAttributes = array();
     $i = 0;
     
     //Label energétique
     if(!empty($article['energy_label'])){
          $tabAttributes[$i]['code']        = $class_energy['AttributeCode'];
          $tabAttributes[$i]['code_value']  = $class_energy['AttributeValues'][$article['energy_label']];
          $tabAttributes[$i]['value_label'] = $article['energy_label'];
          
          $i++;
     }
     
     //Type de pose
     $tabAttributes[$i]['code'] = $type_pose['AttributeCode'];
     if($article['catid'] != 7){
          $tabAttributes[$i]['code_value']  = $type_pose['AttributeValues']['Pose libre'];
          $tabAttributes[$i]['value_label'] = 'Pose libre';
     } else {
          $tabAttributes[$i]['code_value']  = $type_pose['AttributeValues']['Encastrable'];
          $tabAttributes[$i]['value_label'] = 'Encastrable';
     }
     $i++;
     
     //Type de porte
     if(!empty($recherche_typeporte)){
          if($recherche_typeporte['recherchetypeporteid'] == 1 || $recherche_typeporte['recherchetypeporteid'] == 3){
               $tabAttributes[$i]['code']        = $porte_vitree['AttributeCode'];
               $tabAttributes[$i]['code_value']  = $porte_vitree['AttributeValues']['Non'];
               $tabAttributes[$i]['value_label'] = 'Non';
          } else {
               $tabAttributes[$i]['code']        = $porte_vitree['AttributeCode'];
               $tabAttributes[$i]['code_value']  = $porte_vitree['AttributeValues']['Oui'];
               $tabAttributes[$i]['value_label'] = 'Oui';
          }
          
          $i++;
     }
     
     //Capacités de stockage
     if(!empty($article['capacite_bouteilles']) && is_numeric($article['capacite_bouteilles'])){
          $tabAttributes[$i]['code'] = $capacite_bouteilles['AttributeCode'];
          if($article['capacite_bouteilles'] < 100){
               $tabAttributes[$i]['code_value']  = $capacite_bouteilles['AttributeValues']['100moins'];
               $tabAttributes[$i]['value_label'] = '< 100 bouteilles';
          } else if($article['capacite_bouteilles'] >= 100 && $article['capacite_bouteilles'] <= 200){
               $tabAttributes[$i]['code_value']  = $capacite_bouteilles['AttributeValues']['100-200'];
               $tabAttributes[$i]['value_label'] = 'De 100 à 200 bouteilles';
          } else {
               $tabAttributes[$i]['code_value']  = $capacite_bouteilles['AttributeValues']['200plus'];
               $tabAttributes[$i]['value_label'] = '> 200 bouteilles';
          }
          
          $i++;
     }
     
     //Type de cave
     if(!empty($article['usage'])){
          $tabAttributes[$i]['code'] = $type_cave['AttributeCode'];
          switch($article['usage']){
               case 'Vieillissement':
               case 'Conservation' :
                    $tabAttributes[$i]['code_value']  = $type_cave['AttributeValues']['vieillissement'];
                    $tabAttributes[$i]['value_label'] = 'Vieillissement';
                    break;
               case 'Multi-usages':
               case 'Multiusages':
                    $tabAttributes[$i]['code_value']  = $type_cave['AttributeValues']['polyvalente'];
                    $tabAttributes[$i]['value_label'] = 'Polyvalente';
                    break;
               case 'Service':
                    $tabAttributes[$i]['code_value']  = $type_cave['AttributeValues']['mise_en_temperature'];
                    $tabAttributes[$i]['value_label'] = 'Mise en température';
                    break;
          }
     }
     
     //Construction du XML
     $xml = utf8_encode(""
     . "<item:RootNode xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:item=\"http://RedouteFrance/REF-VENTE/Product/Item/2.0\" xsi:schemaLocation=\"http://RedouteFrance/REF-VENTE/Product/Item/2.0 VendorItem_1.0.xsd\">"
          . "<EAN>".$codebarre[0]."</EAN>"
          . "<Classification>"
               . "<ClassificationValue>".$mp_field['ClassificationValue']."</ClassificationValue>"
          . "</Classification>"
          . "<Product>"
               . "<ProductId>".$article['artid']."</ProductId>"
               . "<ProductTitles>"
                    . "<ProductTitle language=\"fr\"><![CDATA[".$mp_field['ProductTitle']."]]></ProductTitle>"
               . "</ProductTitles>"
               . "<ProductDescriptions>"
                    . "<ProductDescription language=\"fr\"><![CDATA[".$mp_field['ProductDescription']."]]></ProductDescription>"
               . "</ProductDescriptions>"
               . "<ProductBrand>"
                    . "<BrandCode>".$marque['redouteid']."</BrandCode>"
               . "</ProductBrand>"
               . "<Medias>"
                    . "<Media>"
                         . "<MediaType>Photo</MediaType>"
                         . "<MediaURL>".$imageprincipale."</MediaURL>"
                         . "<MediaEncoding>".$article['image']."</MediaEncoding>"
                         . "<Checksum ChecksumType=\"MD5\">".md5($article['artid'].$marque['marqueid'])."</Checksum>"
                         . "<PublicationRank>1</PublicationRank>"
                    . "</Media>"
                    . "<Media>"
                         . "<MediaType>Logo</MediaType>"
                         . "<MediaURL>https://".$host."/configurations/www.ma-cave-a-vin.fr/images/marques/br/".$marque['marqueid'].".jpg</MediaURL>"
                         . "<MediaEncoding>jpg</MediaEncoding>"
                         . "<Checksum ChecksumType=\"MD5\">".md5($marque['marqueid'].$marque['libelle'])."</Checksum>"
                         . "<PublicationRank>2</PublicationRank>"
                    . "</Media>"
               . "</Medias>"
          . "</Product>"
          . "<Vendor>"
               . "<VendorId>".$vendeur_redoute_id."</VendorId>"
          . "</Vendor>");
          
          if(!empty($tabAttributes)){
               
               $xml .= "<Attributes>";
               
               foreach($tabAttributes as $l_attribut){

                    $xml .= utf8_encode(""
                    . "<Attribute>"
                         . "<AttributeCode>".$l_attribut['code']."</AttributeCode>"
                         . "<AttributeValues>"
                              . "<AttributeValueCode>".$l_attribut['code_value']."</AttributeValueCode>"
                              . "<AttributeValueLabel language=\"String\">".$l_attribut['value_label']."</AttributeValueLabel>"
                         . "</AttributeValues>"
                    . "</Attribute>");
               }
               
               $xml .= "</Attributes>";
          }
          
     $xml .= "</item:RootNode>";

     return $xml;

}

if(isset($_GET['list'])){
     $f = fopen($_GET['list'],"r");
     $i = 0;
     while(!feof($f)){
          $line = fgets($f,512);
          
          $sql = "UPDATE market_place_champs SET";
          $sql .= " enabled = 1";
          $sql .= " WHERE artcode = '".strtoupper(chop($line))."'";
          $result = $DB_site2->query($sql);
          
          print "<br>".$line." ".$result;
          $i++;
     }
     fclose($f);
     print "<br>".$i." activés";
     exit;
}

if(!is_dir($rootpath."configurations/".$host."/exports")){
     mkdir($rootpath."configurations/".$host."/exports",0777);
}

$filename = $vendeur_redoute_id.".MKP_Products.002.".date('y-m-d-H-i-s').".xml";
$filehost = $rootpath."configurations/$host/exports/".$filename;
$filelink = "http://".$host."/configurations/".$host."/exports/".$filename;

if(!$handle = fopen($filehost, 'w')){
     echo "Impossible d'ouvrir le fichier (".$filehost.")";
     exit;
}

if(fwrite($handle, stripslashes(html_entity_decode($contenu,ENT_COMPAT,"ISO-8859-15"))) === FALSE){
     echo "Impossible d'écrire dans le fichier (".$filehost.")";
     exit;
}

$class_energy = array(
     'AttributeCode' => 'A0339',
     'AttributeValues' => array(
          'A++'   => 'V001463',
          'A+'    => 'V001464',
          'A'     => 'V001465',
          'B'     => 'V001466',
          'C'     => 'V001467',
          'A+++'  => 'V001468',
          'A++++' => 'V001469',
          'D'     => 'V021203',
          'E'     => 'V021204'
     )
);

$type_pose = array(
     'AttributeCode' => 'A0342',
     'AttributeValues' => array(
          'Pose libre'  => 'V001528',
          'Encastrable' => 'V001529'
     )
);

$porte_vitree = array(
     'AttributeCode' => 'A0352',
     'AttributeValues' => array(
          'Non' => 'V015125',
          'Oui' => 'V015302'
     )
);

$capacite_bouteilles = array(
     'AttributeCode' => 'A0353',
     'AttributeValues' => array(
          '100-200'  => 'V001560',
          '100moins' => 'V001562',
          '200plus'  => 'V001563'
     )
);

$type_cave = array(
     'AttributeCode' => 'A0354',
     'AttributeValues' => array(
          'Mise en température' => 'V001583',
          'Vieillissement'      => 'V001584',
          'Polyvalente'         => 'V001585'
     )
);

$sql = "SELECT a.*";
$sql .= " FROM market_place_champs mpc";
$sql .= " INNER JOIN article a ON (a.artcode LIKE mpc.artcode)";
$sql .= " INNER JOIN article_site a_s USING(artid)";
$sql .= " INNER JOIN market_place_catid mp ON (a.catid = mp.catid AND mp.market_place LIKE 'redoute')";
$sql .= " WHERE a_s.active = 1";
$sql .= " AND a.commandable = '1'";
$sql .= " AND mpc.enabled = 1";
$sql .= " AND mpc.market_place LIKE 'redoute'";
$sql .= " GROUP BY artcode";
$sql .= " ORDER BY a.artid";
$articles = $DB_site2->query($sql);

$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";

$compteur = 0;
while($article = $DB_site2->fetch_array($articles)){
     
     $xml .= product($article, false);
     
     $compteur++;
    
}

//Data, connection, auth
$soapUrl = "http://marketplace.laredoute.com/"; // asmx URL of WSDL

$boundary = generateBoundary();

$xml_post_string = ""
     . "--".$boundary."\r\n"
     . "Content-Type: application/soap+xml; charset=UTF-8\r\n"
     . "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n"
     . "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">\r\n"
          . "<soap:Body>\r\n"
               . "<InjectFileRequest_1.0 xmlns=\"http://Redcats/File/ESB-TIBCO/File/1.0/InjectFile/1.0\">\r\n"
                    . "<EUserId>".$vendeur_redoute_id."</EUserId>\r\n"
                    . "<ESystem>400</ESystem>\r\n"
                    . "<FlowName>MKP_Products</FlowName>\r\n"
                    . "<FlowVersion>002</FlowVersion>\r\n"
                    . "<FileName>".$filename."</FileName>\r\n"
                    . "<fileAttachedWithSoapMsg>true</fileAttachedWithSoapMsg>\r\n"
               . "</InjectFileRequest_1.0>\r\n"
          . "</soap:Body>\r\n"
     . "</soap:Envelope>\r\n"
     . "\r\n"
     . "--".$boundary."\r\n"
     . "Content-Disposition: attachment; name=\"".$filename."\"; filename=\"".$filename."\"\r\n"
     . "Content-Type: text/xml; charset=UTF-8\r\n"
     . "Content-ID: <".$filename.">\r\n"
     . "\r\n"
     . utf8_decode($xml);   // data from the form, e.g. some ID number

$headers = array(
     "Content-type: multipart/related; type=\"application/soap+xml\"",
     "Pragma: no-cache",
     "SOAPAction: InjectFile_1.0", 
     "boundary=".$boundary,
     "Content-length: ".strlen($xml_post_string),
     "MIME-Version: 1.0",
     "Connection: close",
     "User-Agent: STREAM-CONTEXT"
 ); //SOAPAction: your op URL

$url = $soapUrl;

$fp = fopen(dirname(__FILE__).'/error-curl-redoute.txt', 'w');

// PHP cURL  for https connection with auth
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_STDERR, $fp); 

// converting
$response = curl_exec($ch); 

curl_close($ch);

// converting
$response1 = str_replace("<soap:Body>","",$response);
$response2 = str_replace("</soap:Body>","",$response1);

// convertingc to XML
$parser = simplexml_load_string($response2);

// user $parser to get your data out of XML response and to display it.