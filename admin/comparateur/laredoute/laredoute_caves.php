<?php
ini_set("max_execution_time",180);
ini_set("display_errors",1);

header('Content-Type: text/html; charset=utf-8');

$vendeur_redoute_id = '11604'; //Num?ro vendeur ? ajouter quand on nous le transmet
        
include_once($_SERVER['DOCUMENT_ROOT'].'/admin/host_config_admin.php');

$nostat = 1;
$passage_ligne = "\r\n";

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
    global $rootpath;
    
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
        $mp_field[$champ['champ']] = html_entity_decode($mp_field[$champ['champ']], ENT_QUOTES, "UTF-8");
        $mp_field[$champ['champ']] = str_replace($aBR,'\r\n',$mp_field[$champ['champ']]);
        $mp_field[$champ['champ']] = htmlentities($mp_field[$champ['champ']], ENT_QUOTES, "UTF-8");
        $mp_field[$champ['champ']] = str_replace('\r\n','<br />',$mp_field[$champ['champ']]);
        $mp_field[$champ['champ']] = html_entity_decode($mp_field[$champ['champ']], ENT_QUOTES, "UTF-8");
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
    
    $imageprincipale = "https://".$host."/configurations/".$host."/images/produits/".$article['artid'].".".$article['image'];
    $image = $imageprincipale;
    $imagemodif=filemtime($rootpath."configurations/".$host."/images/produits/".$article['artid'].".".$article['image']);

    //Mise en forme du tableau des attributs
    $tabAttributes = array();
    $i = 0;
    
    if($mp_field['ClassificationValue'] == 'S697'){
        //Label energ?tique
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
        
        //Capacit?s de stockage
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
        $tabAttributes[$i]['code']        = $type_cave['AttributeCode'];
        $tabAttributes[$i]['code_value']  = $type_cave['AttributeValues'][mpfields($article,$mp_field['ProductType'])];
        $tabAttributes[$i]['value_label'] = mpfields($article,$mp_field['ProductType']);
    }
    
    $etiqmedia="";
    if($article['energy_label']!=""){
            $etiqurl="https://".$host."/images/mail/fiche_energetique.php?artid=".$article['artid'];
            $etiqmedia="<n1:Media>".$passage_ligne
                         . "<n1:MediaType>Photo</n1:MediaType>".$passage_ligne
                         . "<n1:MediaURL>".$etiqurl."</n1:MediaURL>".$passage_ligne
                         . "<n1:MediaEncoding>jpg</n1:MediaEncoding>".$passage_ligne
                         . "<n1:Checksum>".md5($article['artid'].$article['energy_label'])."</n1:Checksum>".$passage_ligne
                         . "<n1:ChecksumType>MD5</n1:ChecksumType>".$passage_ligne
                         . "<n1:PublicationRank>2</n1:PublicationRank>".$passage_ligne
                    . "</n1:Media>".$passage_ligne;
    }
      
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
                    . "<n1:ProductTitle language=\"fr\"><![CDATA[".html_entity_decode($article['libelleFR']). " - ".$article['artcode']."]]></n1:ProductTitle>".$passage_ligne
               . "</n1:ProductTitles>".$passage_ligne
               . "<n1:ProductDescriptions>".$passage_ligne
                    . "<n1:ProductDescription language=\"fr\"><![CDATA[".html_entity_decode(strip_tags($article['descriptionFR']))."]]></n1:ProductDescription>".$passage_ligne
               . "</n1:ProductDescriptions>".$passage_ligne
               . "<n1:ProductBrand>".$passage_ligne
                    . "<n1:BrandCode>".$marque['redouteid']."</n1:BrandCode>".$passage_ligne
               . "</n1:ProductBrand>".$passage_ligne
               . "<n1:Medias>".$passage_ligne
                    . "<n1:Media>".$passage_ligne
                         . "<n1:MediaType>Photo</n1:MediaType>".$passage_ligne
                         . "<n1:MediaURL>".$imageprincipale."</n1:MediaURL>".$passage_ligne
                         . "<n1:MediaEncoding>".$article['image']."</n1:MediaEncoding>".$passage_ligne
                         . "<n1:Checksum>".md5($article['artid'].$imagemodif)."</n1:Checksum>".$passage_ligne
                         . "<n1:ChecksumType>MD5</n1:ChecksumType>".$passage_ligne
                         . "<n1:PublicationRank>1</n1:PublicationRank>".$passage_ligne
                    . "</n1:Media>".$passage_ligne
               . "</n1:Medias>".$passage_ligne
          . "</n1:Product>".$passage_ligne
          . "<n1:Vendor>".$passage_ligne
               . "<n1:VendorId>".$vendeur_redoute_id."</n1:VendorId>".$passage_ligne
          . "</n1:Vendor>".$passage_ligne;
       
          if(!empty($tabAttributes)){
             
               $xml .= "<n1:Attributes>".$passage_ligne;

               foreach($tabAttributes as $l_attribut){

                    $xml .= ""
                    . "<n1:Attribute>".$passage_ligne
                         . "<n1:AttributeCode>".$l_attribut['code']."</n1:AttributeCode>".$passage_ligne
                         . "<n1:AttributeValues>".$passage_ligne
                              . "<n1:AttributeValueCode>".$l_attribut['code_value']."</n1:AttributeValueCode>".$passage_ligne
                              . "<n1:AttributeValueLabel language=\"String\">".str_replace('<','Inf.',$l_attribut['value_label'])."</n1:AttributeValueLabel>".$passage_ligne
                         . "</n1:AttributeValues>".$passage_ligne
                    . "</n1:Attribute>".$passage_ligne;
               }

               $xml .= "</n1:Attributes>".$passage_ligne;
          }
          
          
        $xmlimg="";
        $photos=$DB_site2->query("select * from articlephoto where artid = '$article[artid]' order by articlephotoid limit 8") ;
        $PublicationRank=2;
        while ($photo=$DB_site2->fetch_array($photos)) {
            $imgurl="https://$host/configurations/$host/images/produits/$article[artid]"."_"."$photo[articlephotoid].$photo[image]" ;
            $imagedatemodif=filemtime($rootpath."configurations/$host/images/produits/$article[artid]"."_"."$photo[articlephotoid].$photo[image]");
            
            $xmlimg.="<n1:Media>".$passage_ligne
                    . "<n1:MediaType>Photo</n1:MediaType>".$passage_ligne
                    . "<n1:MediaURL>".$imgurl."</n1:MediaURL>".$passage_ligne
                    . "<n1:MediaEncoding>".$photo['image']."</n1:MediaEncoding>".$passage_ligne
                    . "<n1:Checksum>".md5($article['artid'].$imagedatemodif)."</n1:Checksum>".$passage_ligne
                    . "<n1:ChecksumType>MD5</n1:ChecksumType>".$passage_ligne
                    . "<n1:PublicationRank>".$PublicationRank."</n1:PublicationRank>".$passage_ligne
            . "</n1:Media>".$passage_ligne;
            $PublicationRank++;
        }
          
          $xml.= "<n1:Medias>"
                  . "<n1:Media>".$passage_ligne
                         . "<n1:MediaType>Photo</n1:MediaType>".$passage_ligne
                         . "<n1:MediaURL>".$imageprincipale."</n1:MediaURL>".$passage_ligne
                         . "<n1:MediaEncoding>".$article['image']."</n1:MediaEncoding>".$passage_ligne
                         . "<n1:Checksum>".md5($article['artid'].$imagemodif)."</n1:Checksum>".$passage_ligne
                         . "<n1:ChecksumType>MD5</n1:ChecksumType>".$passage_ligne
                         . "<n1:PublicationRank>1</n1:PublicationRank>".$passage_ligne
                    . "</n1:Media>".$passage_ligne
                    .$xmlimg
                    . $etiqmedia
                    ."</n1:Medias>";
        
          
    $xml .= "</n1:VendorItem>".$passage_ligne;

    return $xml;
}

/*if(!is_dir($rootpath."admin/comparateur/log/laredoute/Caves")){
     mkdir($rootpath."admin/comparateur/log/laredoute/Caves",0777);
}*/

$filename = $vendeur_redoute_id.".MKP_Products.002.".date('y-m-d-H-i-s').".xml";
$filehost = $rootpath."admin/comparateur/log/laredoute/".$filename;
$filelink = "http://".$host."/admin/comparateur/log/laredoute/".$filename;

if(!$handle = fopen($filehost, 'w')){
     echo "Impossible d'ouvrir le fichier (".$filehost.")";
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
          'E'     => 'V021204',
          'F'     => 'V045614',
          'G'     => 'V045615'
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
$sql .= " INNER JOIN article a ON (a.artcode = mpc.artcode)";
$sql .= " INNER JOIN article_site a_s USING(artid)";
$sql .= " WHERE a_s.active = 1";
$sql .= " AND a.commandable = '1'";
$sql .= " AND mpc.enabled = 1";
$sql .= " AND mpc.market_place LIKE 'redoute'";
$sql .= " AND (SELECT mpc.valeur FROM market_place_catid mpc WHERE mpc.catid = a.catid AND mpc.market_place = 'redoute' AND mpc.champ = 'ClassificationValue') = 'S697'"; //On récupère uniquement les produits de type "cave à vin"
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

/*echo "Votre export de catalogue pour La Redoute a bien ?t? mis ? jour (produit export?s : ".$compteur.")<br>
Il est disponible ? l'adresse suivante : <a href=\"".$filelink."\"><strong>".$filelink."</strong></a>" ;*/

//Data, connection, auth
$soapUrl = "https://marketplace.laredoute.com/Redcats/File/ESB-TIBCO/File/1.0"; // asmx URL of WSDL

$boundary = "=_".generateBoundary();

$xml_post_string = ""
     ."--".$boundary.$passage_ligne
     . "Content-Type: application/soap+xml; charset=UTF-8;".$passage_ligne
     . "Content-Transfer-Encoding: 8bit".$passage_ligne
     . "Content-ID: <webmaster1@ma-cave-a-vin.fr>".$passage_ligne
     . $passage_ligne
     . "<?xml version=\"1.0\" encoding=\"utf-8\"?>".$passage_ligne
     . "<SOAP-ENV:Envelope xmlns:SOAP-ENV=\"http://www.w3.org/2003/05/soap-envelope\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:SOAP-ENC=\"http://schemas.xmlsoap.org/soap/encoding/\" xmlns:ns3797=\"http://tempuri.org\">".$passage_ligne
          . "<SOAP-ENV:Body>".$passage_ligne
               . "<InjectFileRequest_1.0 xmlns=\"http://Redcats/File/ESB-TIBCO/File/1.0/InjectFile/1.0\">".$passage_ligne
                    . "<EUserId>".$vendeur_redoute_id."</EUserId>".$passage_ligne
                    . "<ESystem>400</ESystem>".$passage_ligne
                    . "<FlowName>MKP_Products</FlowName>".$passage_ligne
                    . "<FlowVersion>002</FlowVersion>".$passage_ligne
                    . "<FileName>".$filename."</FileName>".$passage_ligne
                    . "<fileAttachedWithSoapMsg>true</fileAttachedWithSoapMsg>".$passage_ligne
               . "</InjectFileRequest_1.0>".$passage_ligne
          . "</SOAP-ENV:Body>".$passage_ligne
     . "</SOAP-ENV:Envelope>".$passage_ligne
     . $passage_ligne
     . "--".$boundary.$passage_ligne
     . "Content-Type: text/xml; charset=UTF-8;".$passage_ligne
     . "Content-Transfer-Encoding: 8bit".$passage_ligne
     . "Content-ID: <".$filename.">".$passage_ligne
     . "Content-Disposition: attachment; name=\"".$filename."\", filename=\"".$filename."\"".$passage_ligne
     . $passage_ligne
     . $xml; // data from the form, e.g. some ID number

$headers = array(
     "Content-Type: multipart/related; type=\"application/soap+xml\"; boundary=\"".$boundary."\"",
     "SOAPAction: \"InjectFile_1.0\"",
     "Content-Length: ".strlen($xml_post_string),
     "User-Agent: STREAM-CONTEXT"
 ); //SOAPAction: your op URL

$url = $soapUrl;
//echo $xml_post_string;
$fp = fopen(dirname(__FILE__).'/error-curl-redoute.txt', 'w');

// PHP cURL  for https connection 
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_STDERR, $fp);

$response = curl_exec($ch);
//nicex($xml_post_string);
// converting
/*if($response === false)
{                
    $err = 'Transfert ?chou? : <span style="color:red;">' . curl_error($ch) . '</span>';
    echo $err;
} 
else 
{
     echo "Transfert r?ussi";
}*/

//nicex($xml_post_string);

curl_close($ch);

$clean_xml = str_ireplace(['SOAP-ENV:', 'SOAP:', 'ns0:'], '', $response);
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
}

if(isset($_POST['URL']) && $_POST['URL'] != ''){
	$date = date("d-m-Y H:i");
	$action = "Mise a jour des fichiers \"Caves Ã  vin\"";
	$market_place = "laredoute";
	//mise Ã  jour du suivi
	$sql = "INSERT INTO suivi_export SET ";
	$sql .= " user = '$_COOKIE[admin_username]'";
	$sql .= ", time = '$date'";
	$sql .= ", action = '$action'";
	$sql .= ", market_place = '$market_place'";
	$DB_site2->query($sql);
	$_POST['URL'] = $_POST['URL']."&update=1";
	header('Location: '.$_POST['URL']);
} else {
	$mail = new email();
	$mail->destinataire = "webmaster1@ma-cave-a-vin.fr";
	//$mail->to           = array("webmaster@ma-cave-a-vin.fr");
	$mail->expname      = "Ma Cave à Vin";
	$mail->expediteur   = "contact@ma-cave-a-vin.fr";
	$mail->subject      = "LaRedoute - Rapport de creation des produits (caves) - ".date('d/m/Y H:i:s');
	$mail->body         = $message; 
	$mail->sendmail();
}
