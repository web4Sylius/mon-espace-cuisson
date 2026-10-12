<?php
require_once __DIR__.'/../../../secrets.php';
require_once("admin_global.php");

//$rootpath="/var/www/ma-cave-a-vin.fr/";
        
$host   = "www.ma-cave-a-vin.fr" ;
$nostat = 1;
$passage_ligne = "\r\n";


require_once(ROOT_PATH."configurations/$host/config_mysql.php");
require_once(ROOT_PATH."class/mysql.php");
require_once(ROOT_PATH."class/template.class.php");
require_once(ROOT_PATH."admin/mpfields.php");
require_once(ROOT_PATH."class/fonctions.php");
require_once(ROOT_PATH."class/devises.class.php");
require_once(ROOT_PATH."class/stock.class.php");
require_once(ROOT_PATH."class/email.php");

//Production
$mcav_dossier  = 'admin/export/cdiscount/';

function product($article, $coloris=false){
     global $DB_site2;
     global $template;
     global $headkeys;
     global $tabCouleurs;

     $line = array();

     //Remplacement des contenus de champs paramétré
     $sql = "SELECT champ, valeur";
     $sql .= " FROM market_place_catid";
     $sql .= " WHERE market_place = 'cdiscount'";
     $sql .= " AND catid = '".$article['catid']."'";
     $requete = $DB_site2->query($sql);
     while($champ = $DB_site2->fetch_array($requete)){
          $mp_field[$champ['champ']] = mpfields($article, $champ['valeur']);
     }

     //Informations sur la marque
     $sql = "SELECT m.*";
     $sql .= " FROM article_marque am";
     $sql .= " INNER JOIN marque m";
     $sql .= " USING(marqueid)";
     $sql .= " WHERE am.artid = ".$article['artid'];
     $marque = $DB_site2->query_first($sql);

     //Récupération du code barre en fonction du coloris
     $sql = "SELECT codebarre";
     $sql .= " FROM code_barre";
     $sql .= " WHERE artid = '".$article['artid']."'";
     $sql .= ($coloris != false ? " AND caractvalid = ".$coloris['caractvalid'] : "");
     $ean = $DB_site2->query_first($sql);

     //Récupération du pays
     $sql = "SELECT paysid, libelle";
     $sql .= " FROM pays";
     $sql .= " WHERE paysid = ".$article['paysid'];
     $pays = $DB_site2->query_first($sql);

     //Récupération des visuels de l'article
     $sql = "SELECT articlephotoid";
     $sql .= " FROM articlephoto";
     $sql .= " WHERE artid = ".$article['artid'];
     $sql .= " ORDER BY position";
     $r = $DB_site2->query($sql);
     $photo = array();
     while($im = $DB_site2->fetch_array($r)){
          $photo[] = $im[0];
     }

     //Vérification de l'intitulé de coloris, pour qu'il soit conforme à la norme imposée par Cdiscount
     if(!$coloris){
          $couleur_produit = $template->translate($article['coloris']);
     } else {
          $couleur_produit = $coloris['libelleFR'];
     }
     if(!in_array($couleur_produit, $tabCouleurs)){
          $couleur_produit = '';
     }

     $profondeur = !empty($article['profondeur']) ? $article['profondeur']/10 : '';
     $largeur    = !empty($article['largeur']) ? $article['largeur']/10 : '';
     $hauteur    = !empty($article['hauteur']) ? $article['hauteur']/10 : '';
     $poid       = !empty($article['poid']) ? $article['poid']/1000 : '';
      $test = "test";

    $xml = //avec $test et $test1 à recuperer 
    "<Product>
    <ProductEanList>
        <ProductEan>".$ean[0]."</ProductEan>
    <ProducteanList>
    <ProductModelProperties></ProductModelProperties>
    <ProductPictures></ProductPictures>
    <Attribute>
        <Width>".$largeur."</Width>
        <Weight>".$poids."</Weight>
        <ShortLabel>".$mp_field['libelle_court']."</ShortlLabel>
        <SellerProductId>".$article['artid']."</SellerProdutId>
        <ProductKind>Standard</ProductKind>
        <Navigation>".$test."</Naviation> 
        <Model>".$test1."</Model>
        <LongLabel>".$mp_field['libelle_long']."</LongLabel>
        <Length>".$profondeur."</Lenght>
        <Height>".$hauteur."</Height>
        <Description>".$mp_field['description']."</Description>
        <BrandName>".$marque['libelle']."</BrandName>
    </Attribute>
</Product>";
    return($xml);
}

$libelle     = "libelleFR";
$description = "descriptionFR";

$today = mktime(0,0,0);

$DB_site2=new DB_Sql;
$DB_site2->appname='boutique';
$DB_site2->appshortname='boutique';
$DB_site2->database=$DBname;
$DB_site2->server=$db_server;
$DB_site2->user=$DBusername;
$DB_site2->password=$DBpassword;
$DB_site2->connect();

$template=new template("FR",$DB_site2);

//Préparation du fichier csv pour le GEM
$nomFichier = "export_cdiscount_produits_test".strtotime(date("d-m-Y H:i")).".txt";
$filename_source      = ROOT_PATH.$mcav_dossier.$nomFichier;
$monfichier = fopen($filename_source, 'a+');
//echo $filename_source;
//Tableau des couleurs acceptées par Cdiscount
//Si une couleur d'article ne correspond à aucune de celles ci-dessous, elle sera remplacée par "Non communiqué"
//La couleur apparaissant dans l'intitulé, ce n'est pas un problème
$tabCouleurs = array(
     'Beige',
     'Blanc',
     'Bleu',
     'Gris',
     'Jaune',
     'Marron',
     'Noir',
     'Orange',
     'Rose',
     'Rouge',
     'Vert',
     'Violet'
);


//Récupération de tous les produits actifs et commandables du site
$sql = "SELECT a.*";
$sql .= " FROM article a";
$sql .= " INNER JOIN categorie c USING(catid)";
$sql .= " WHERE active = '1'";
$sql .= " AND commandable = '1'";
$sql .= " GROUP BY artcode";
$sql .= " ORDER BY a.artid";
$articles = $DB_site2->query($sql);
while ($article = $DB_site2->fetch_array($articles)){
     $mp_field = array();

     //Récupération de tous les produits que l'on souhaite vendre sur la market place Cdiscount
     $sql = "SELECT enabled";
     $sql .= " FROM market_place_champs";
     $sql .= " WHERE market_place = 'cdiscount'";
     $sql .= " AND artcode LIKE '".$article['artcode']."'";
     $allenabled = $DB_site2->query_first($sql);

     //Si le produit est activé et qu'il qu'il n'est pas en cours de réappro
     if($allenabled[0] == "1" && $article['date_appro'] < $today){

          //Récupération des visuels du produit dans ses différents coloris
          $sql = "SELECT ac.image as imagecaractvalid, cv.image, ac.caractvalid, ac.artid, cv.".$libelle;
          $sql .= " FROM article_caractval ac";
          $sql .= " INNER JOIN caracteristiquevaleur cv USING(caractvalid)";
          $sql .= " WHERE ac.artid = '".$article['artid']."'";
          $sql .= " AND cv.caractid = '1'";
          $coloriss = $DB_site2->query($sql);

          $xml .= product($article);
     }
}
//echo $xml;
fputs($monfichier, $xml);
fclose($monfichier);

    //Appel API Cdiscount Pour recuperer le token. sans token  = pas d'appel api
$user = CDISCOUNT_MP_API_LOGIN;
    $pass = CDISCOUNT_MP_API_PASSWORD;
    $auto = $user.":".$pass;
    $authentication = base64_encode($auto); 
    $httpheader = array("Authorization: Basic ".$authentication);
    $itemsFeedURL = "https://sts.preprod-cdiscount.com/users/httpIssue.svc/?realm=https://wsvc.preprod-cdiscount.com/MarketplaceAPIService.svc"; //preprod
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $itemsFeedURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS,5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
    $response = curl_exec($ch);
    curl_close($ch);


$xmlResponse = simplexml_load_string(trim($response));
    
    
    //echo $token;
    
    //Premiere methode pour recupere Les produit que l'on peut ulpoad
    $method = "GetAllowedCategoryTree";
    $callHeaderHttp = array('Content-Type: text/xml;charset=UTF-8', 'SOAPAction: '.'"http://www.cdiscount.com/IMarketplaceAPIService/'.$method.'"');
    $data = "<s:Envelope xmlns:s=\"http://schemas.xmlsoap.org/soap/envelope/\">
    <s:Body>
        <GetAllowedCategoryTree xmlns=\"http://www.cdiscount.com\">
            <headerMessage xmlns:a=\"http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages\" xmlns:i=\"http://www.w3.org/2001/XMLSchema-instance\">
                <a:Context>
                    <a:CatalogID>1</a:CatalogID>
                    <a:CustomerPoolID>1</a:CustomerPoolID>
                    <a:SiteID>100</a:SiteID>
                </a:Context>
                <a:Localization>
                    <a:Country>Fr</a:Country>
                    <a:Currency>Eur</a:Currency>
                    <a:DecimalPosition>2</a:DecimalPosition>
                    <a:Language>Fr</a:Language>
                </a:Localization>
                <a:Security>
                    <a:DomainRightsList i:nil=\"true\" />
                    <a:IssuerID i:nil=_\"true\" />
                    <a:SessionID i:nil=\"true\" />
                    <a:SubjectLocality i:nil=\"true\" />
                    <a:TokenId>".$token."</a:TokenId>
                    <a:UserName i:nil=\"true\" />
                </a:Security>
                <a:Version>1.0</a:Version>
            </headerMessage>
        </GetAllowedCategoryTree>
    </s:Body>
</s:Envelope>";

$tuCurl = curl_init();
    curl_setopt($tuCurl, CURLOPT_URL, "https://wsvc.preprod-cdiscount.com/MarketplaceAPIService.svc"); 
    curl_setopt($tuCurl, CURLOPT_VERBOSE, false);
    curl_setopt($tuCurl, CURLOPT_HEADER, true);
    curl_setopt($tuCurl, CURLOPT_POST, true);
    curl_setopt($tuCurl, CURLOPT_SSLVERSION, 'all');
    curl_setopt($tuCurl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($tuCurl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($tuCurl, CURLOPT_HTTPHEADER, $callHeaderHttp);
    // Récupération de la réponse SOAP
    echo '<h1>Réponse</h1>';
    $tuData = curl_exec($tuCurl); 
    if(!curl_errno($tuCurl))
    { 
      $info = curl_getinfo($tuCurl); 
      echo '<p>A pris '.$info['total_time'].' secondes pour envoyer la requête sur '.$info['url'].'</p>'; 
    }
    else 
    { 
      echo '<p>Curl error: '.curl_error($tuCurl).'</p>'; 
    }

    // Appel SOAP GetSellerInformation
   /* echo '<h1>Appel</h1>';
    // TODO récupération du token
    
    deuxieme methode qui suit celle du dessus
    $method = "GetModelList";
    // Génération de l'enveloppe SOAP
    $data = "<s:Envelope xmlns:s=\"http://schemas.xmlsoap.org/soap/envelope/\">
    <s:Body>
        <SubmitProductPackage xmlns=\"http://www.cdiscount.com\">
            <headerMessage xmlns:a=\"http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages\" xmlns:i=\"http://www.w3.org/2001/XMLSchema-instance\">
                <a:Context>
                    <a:CatalogID>1</a:CatalogID>
                    <a:CustomerPoolID>1</a:CustomerPoolID>
                    <a:SiteID>100</a:SiteID>
                </a:Context>
                <a:Localization>
                    <a:Country>Fr</a:Country>
                    <a:Currency>Eur</a:Currency>
                    <a:DecimalPosition>2</a:DecimalPosition>
                    <a:Language>Fr</a:Language>
                </a:Localization>
                <a:Security>
                    <a:DomainRightsList i:nil=\"true\" />
                    <a:IssuerID i:nil=\"true\" />
                    <a:SessionID i:nil=\"true\" />
                    <a:SubjectLocality i:nil=\"true\" />
                    <a:TokenId>".$token."</a:TokenId>
                    <a:UserName i:nil=\"true\" />
                </a:Security>
                <a:Version>1.0</a:Version>
            </headerMessage>
            <productPackageRequest xmlns:i=\"http://www.w3.org/2001/XMLSchema-instance\">
                <ZipFileFullPath>".$lienZip."</ZipFileFullPath>
            </productPackageRequest>
        </SubmitProductPackage>
    </s:Body>
</s:Envelope>";
    $callHeaderHttp = array('Content-Type: text/xml;charset=UTF-8', 'SOAPAction: '.'"http://www.cdiscount.com/IMarketplaceAPIService/'.$method.'"');
    
    // Dump de l'appel SOAP
    //echo '<p>'.nl2br(htmlentities($data, ENT_QUOTES | ENT_IGNORE, "UTF-8")).'</p>';
    $tuCurl = curl_init();
    curl_setopt($tuCurl, CURLOPT_URL, "https://wsvc.preprod-cdiscount.com/MarketplaceAPIService.svc"); 
    curl_setopt($tuCurl, CURLOPT_VERBOSE, false);
    curl_setopt($tuCurl, CURLOPT_HEADER, true);
    curl_setopt($tuCurl, CURLOPT_POST, true);
    curl_setopt($tuCurl, CURLOPT_SSLVERSION, 'all');
    curl_setopt($tuCurl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($tuCurl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($tuCurl, CURLOPT_HTTPHEADER, $callHeaderHttp);
    // Récupération de la réponse SOAP
    echo '<h1>Réponse</h1>';
    $tuData = curl_exec($tuCurl); 
    if(!curl_errno($tuCurl))
    { 
      $info = curl_getinfo($tuCurl); 
      echo '<p>A pris '.$info['total_time'].' secondes pour envoyer la requête sur '.$info['url'].'</p>'; 
    }
    else 
    { 
      echo '<p>Curl error: '.curl_error($tuCurl).'</p>'; 
    }
    
    // Libération des ressources
    curl_close($tuCurl); 
    //nice($tuData);
    echo '</br>';
    //nice($tuData);
        //nice($response_tab1);
    //$response1 = utf8_encode($response1);
    $xmlResponse = simplexml_load_string(trim($response_tab1));



    $data = "<s:Envelope xmlns:s=\"http://schemas.xmlsoap.org/soap/envelope/\">
    <s:Body>
        <SubmitProductPackage xmlns=\"http://www.cdiscount.com\">
            <headerMessage xmlns:a=\"http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages\" xmlns:i=\"http://www.w3.org/2001/XMLSchema-instance\">
                <a:Context>
                    <a:CatalogID>1</a:CatalogID>
                    <a:CustomerPoolID>1</a:CustomerPoolID>
                    <a:SiteID>100</a:SiteID>
                </a:Context>
                <a:Localization>
                    <a:Country>Fr</a:Country>
                    <a:Currency>Eur</a:Currency>
                    <a:DecimalPosition>2</a:DecimalPosition>
                    <a:Language>Fr</a:Language>
                </a:Localization>
                <a:Security>
                    <a:DomainRightsList i:nil=\"true\" />
                    <a:IssuerID i:nil=\"true\" />
                    <a:SessionID i:nil=\"true\" />
                    <a:SubjectLocality i:nil=\"true\" />
                    <a:TokenId>".$token."</a:TokenId>
                    <a:UserName i:nil="true" />
                </a:Security>
                <a:Version>1.0</a:Version>
            </headerMessage>
            <productPackageRequest xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                <ZipFileFullPath>".$test."</ZipFileFullPath> //avec test le lien vers le fichier compresser
            </productPackageRequest>
        </SubmitProductPackage>
    </s:Body>
</s:Envelope>
?>