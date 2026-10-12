<?php 
require_once __DIR__.'/../../secrets.php';

ini_set("display_errors",1);
require_once(SITE_PATH."/class/fpdf.php"); 

class gls {

    private $DB;
    private $adv;

    //private $url = "https://www-new.gls-france.com/cgi-bin/glsboxGI.cgi";
    private $url="http://www-old.gls-france.com/cgi-bin/glsboxGI.cgi";
    private $url_test="https://www-new.gls-france.com/cgi-bin/glsboxGI.cgi";
    
    private $edi_countries=array(57,181,111);

    private $depot; //T8700
    private $clientid; //T8915
    private $contactid; //T8914
    private $exp_ref;
    private $code_produit="02";
    private $produit="AA";
    
    private $facturid;
    private $date_obj;
    private $date_exp; //T540
    private $poids; //T530
    private $raison_sociale; //T860
    private $contact;
    private $adresse; //T863
    private $codepostal; //T330
    private $ville; //T864
    private $pays; //T100
    private $paysid;
    private $commentaires; //T8906
    private $telephone; //T871
    private $userid; //T859
    private $email; //T1229
    private $portable; //T1230
    private $incoterms; //T210
    
    private $exp_raisonsociale; //T810
    private $exp_adresse; //T820
    private $exp_pays; //T821
    private $exp_codepostal; //T822
    private $exp_ville; //T823

    private $t200; //T200
    private $t750; //T750
    
    private $cle_exp; //T110
    private $cle_rec; // T310
    private $pays_dest; //T100
    private $depot_gls; //T101
    private $tournee_livr; //T320
    private $cp_dest; //T330
    private $trackid; //T8913
    private $datamatrix1; //T8902
    private $datamatrix2; //T8903
    
    private $colis=array();
    
    private $uniship=false;
    private $error;
    private $pjid;

    private $chargeurs=array("69003"=>"26953","72230"=>"26953","60120"=>"26953","73420"=>"26953","69800"=>"26953","69009"=>"26953");
    private $depots=array("69003"=>"FR0069","35949"=>"FR0072","60120"=>"FR0060","73420"=>"FR0073","69003"=>"FR0069","69009"=>"FR0069");
    function __construct($DB,$adv,$clientid) {
	$this->DB=$DB;
	$this->adv=$adv;
    $this->clientid=$clientid;
	
	$this->exp_ref=$this->adv->id;
	$this->facturid=$adv->facturid;
	$this->date_obj=date_create_from_format("j/n/Y",$adv->fields['trsptenlfourndate']);
	if($this->date_obj==false) $this->date_obj=date_create();
	$this->date_exp=$this->date_obj->format("Ymd");
	
	$facture=new facture($this->DB,$this->facturid);
	if($facture->facturid==$this->facturid){
            $this->from_facture($facture);
            $this->origin="cmd";
	}else{
            $stock_cmd=new stock_cmd($this->DB,$this->facturid);
            if($stock_cmd->id==$this->facturid){
                $this->from_stock($stock_cmd);
                $this->origin="stock";
            }
	}
	
	
	$this->colis=$this->adv->colis();
	if($this->colis==array()) $this->colis[1]=$this->adv->lignes;
	foreach($this->colis as $colisid=>$lines){
	    foreach($lines as $lid=>$qte)
		$this->poidscolis[$colisid]+=$qte*$this->poidsu[$lid];
	}

	$this->uniship=!in_array($this->paysid,$this->edi_countries);
	if($this->uniship){
	    $this->code_produit="01";
	    $this->produit="CC";
	}

	$bs5=str_repeat("\\",5);
	$this->tagdebut=$bs5."GLS".$bs5;
	$this->tagfin="/////GLS/////";
    }
    
    function from_facture($facture){
        $this->userid=$facture->userid;
	
	if($this->adv->fields['trsptlivr']=="client" || $this->adv->fields['trsptlivr']=="societe"){
        if($facture->raisonsociale!=""){
            $this->raison_sociale = $facture->raisonsociale;
        }else{
            $this->raison_sociale = "";
            $this->code_produit ="18";
            $this->t200 = "FDF";
            $this->t750 = "Flex Delivery Service";
        }
	    $this->contact=$facture->nom." ".$facture->prenom;
	    $this->adresse=$facture->adresse;
	    $this->codepostal=$facture->codepostal;
	    $this->ville=$facture->ville;
	    $this->pays=$facture->diminutif;
	    $this->paysid=$facture->paysid;
	    $this->commentaires=$facture->infotranspo;
	    $this->telephone=$facture->telephone;
	    if($this->telephone=="") $this->telephone=$facture->utilisateur->telephone;
	    if($this->telephone=="") $this->telephone=$facture->utilisateur->portable;
	    $this->email=$facture->mail;
	    $pays=$this->DB->query_first("SELECT cee,iso3166 FROM pays WHERE paysid=".$facture->paysid);
	    if($pays['cee']==0) $this->incoterms=20;
	    $this->iso3166=$pays['iso3166'];
	}
	if(preg_match("/(fourn|stock|sls)([0-9]+)/",$this->adv->fields['trsptlivr'],$m)){
	    $sls=$this->DB->query_first("SELECT f.*,p.diminutif,p.cee,p.iso3166 FROM fournisseur f INNER JOIN pays p USING(paysid) WHERE id='$m[2]'");
	    $this->raison_sociale=$sls['societe'];
	    $this->contact=$sls['nom']." ".$sls['prenom'];
	    $this->adresse=trim($sls['adresse1']." ".$sls['adresse2']);
	    $this->codepostal=$sls['cp'];
	    $this->ville=$sls['ville'];
	    $this->pays=$sls['diminutif'];
	    $this->paysid=$sls['paysid'];
// 	    $this->commentaires=$facture->infotranspo;
	    $this->telephone=$sls['telephone'];
	    $this->email=$sls['mail'];
	    if($sls['cee']==0) $this->incoterms=20;
	    $this->iso3166=$sls['iso3166'];
        }
        $this->telephone=preg_replace("/[^\d]/","",$this->telephone);
	
	$this->poids=0;
	$this->poidsu=array();
	foreach($this->adv->lignes as $lid=>$qte){
	    $lignefacture=$this->DB->query_first("SELECT a.* FROM lignefacture l, article a WHERE l.artid=a.artid AND lignefactureid=".$lid);
	    $this->poids+=$qte*$lignefacture['emballage_poids1'];
	    $this->poidsu[$lid]=$lignefacture['emballage_poids1'];
	}

	if(preg_match("/fourn([0-9]+)/",$this->adv->fields['lieutrspt'],$m)){
	    $fourn=$this->DB->query_first("SELECT f.*,p.diminutif FROM fournisseur f INNER JOIN pays p USING(paysid) WHERE id=".$m[1]);
	    if(!isset($this->chargeurs[$fourn['cp']])){
		$this->error="Aucun chargeur";
		return;
	    }
	    $this->chargeur=$this->chargeurs[$fourn['cp']];
	    $this->contactid="250".$this->chargeur."09";
	    $this->depot=$this->depots[$fourn['cp']];
	    $this->exp_raisonsociale=$fourn['societe'];
	    $this->exp_adresse=trim($fourn['adresse1']);
	    $this->exp_pays=$fourn['diminutif'];
	    $this->exp_codepostal=$fourn['cp'];
	    $this->exp_ville=$fourn['ville'];
	    
	}else{
	    $this->error="Aucun lieu d'expédition";
	}

    if($this->raison_sociale=="") {
        $this->code_produit ="18";
        $this->t200 = "FDF";
        $this->t750 = "Flex Delivery Service";
    }
    }
    
    function from_stock($stock_cmd){
        
        $this->userid=$stock_cmd->livraison;
        $lieu=$this->DB->query_first("SELECT fournisseur.*,pays.diminutif,pays.paysid,pays.libelle AS pays FROM fournisseur INNER JOIN pays USING(paysid) WHERE id=".$stock_cmd->livraison);
	

        $this->raison_sociale=$lieu['societe'];
        $this->contact=$lieu['nom']." ".$lieu['prenom'];
        $this->adresse=$lieu['adresse1'];
        $this->codepostal=$lieu['cp'];
        $this->ville=$lieu['ville'];
        $this->pays=$lieu['diminutif'];
        $this->paysid=$lieu['paysid'];
        $this->commentaires=$this->adv->fields['infotrspt'] == 'other' ? $this->adv->fields['othertrsptinfo'] : '';
        $this->telephone=$lieu['telephone'];
        if($this->telephone=="") $this->telephone=$lieu['portable'];
        $this->email=$lieu['mail'];
        $pays=$this->DB->query_first("SELECT cee,iso3166 FROM pays WHERE paysid=".$lieu['paysid']);
        if($pays['cee']==0) $this->incoterms=20;
        $this->iso3166=$pays['iso3166'];
        $this->telephone=preg_replace("/[^\d]/","",$this->telephone);
	
	$this->poids=0;
	$this->poidsu=array();
	foreach($this->adv->lignes as $lid=>$qte){
	    $lignefacture=$this->DB->query_first("SELECT a.* FROM article a WHERE a.artid=".$lid);
	    $this->poids+=$qte*$lignefacture['emballage_poids1'];
	    $this->poidsu[$lid]=$lignefacture['emballage_poids1'];
	}

	
        $fourn=$this->DB->query_first("SELECT f.*,p.diminutif FROM fournisseur f INNER JOIN pays p USING(paysid) WHERE id=".$stock_cmd->enlevement);
        if(!isset($this->chargeurs[$fourn['cp']])){
            $this->error="Aucun chargeur";
            return;
        }
        $this->chargeur=$this->chargeurs[$fourn['cp']];
        $this->contactid="250".$this->chargeur."09";
        $this->depot=$this->depots[$fourn['cp']];
        $this->exp_raisonsociale=$fourn['societe'];
        $this->exp_adresse=trim($fourn['adresse1']);
        $this->exp_pays=$fourn['diminutif'];
        $this->exp_codepostal=$fourn['cp'];
        $this->exp_ville=$fourn['ville'];
	    
	
    }
    
    function encode($colisid=null){
        $t530=$this->poids;
        $t8973=$t8904=1;
        $t8702=$t8905=1;
        $t210="";
        if(!is_null($colisid)){
            $t8973=$t8904=$colisid;
            $t8702=$t8905=count($this->colis);
            $t530=$this->poidscolis[$colisid]<100?$this->poidscolis[$colisid]:99.99;
        }
        if($this->incoterms!="") $t210="|T210:".$this->incoterms;
        if(empty($this->email)) $this->email="adv@ma-cave-a-vin.fr";

        $return = ""
        . $this->tagdebut
        . "|T540:" . $this->date_exp
        . "|T859:" . $this->facturid
        . "|T860:" . $this->raison_sociale
        . "|T8914:" . $this->contactid
        . "|T8975:" . $this->code_produit . str_pad($this->exp_ref,10,"0",STR_PAD_LEFT) . "0000" . $this->pays
        . "|T530:" . number_format($t530,2,'.','')
        . "|T8973:" . $t8973
        . "|T8904:" . $t8904
        . "|T8702:" . $t8702
        . "|T8905:" . $t8905
        . "|T863:" . $this->adresse
        . "|T100:" . $this->pays . $t210
        . "|T330:" . $this->codepostal
        . "|T864:" . $this->ville
        . ($this->pays=="FR" ? "|T082:UNIQUENO" : "")
        . "|T871:" . $this->telephone
        . "|T1229:" . $this->email
        . "|T090:NOSAVE"
        . "|T8700:" . $this->depot;

        if($this->raisonsociale == "") {
            $return .= "|T200:".$this->t200."|T750:".$this->t750;
        }

        $return.=  "|T8915:" . $this->clientid
        . "|T810:" . $this->exp_raisonsociale
        . "|T820:" . $this->exp_adresse
        . "|T821:" . $this->exp_pays
        . "|T822:" . $this->exp_codepostal
        . "|T823:" . $this->exp_ville
        . "|" . $this->tagfin;

        return $return;
    }
    
    function decode($data){
        $start=stripos($data,$this->tagdebut)+strlen($this->tagdebut);
        $end=stripos($data,$this->tagfin);
    // 	preg_match("#".$this->tagdebut."(.+)".$this->tagfin."#",$data,$m);
        $data=substr($data,$start,$end-$start);
        $arr=explode("|",$data);
        $return=array();
        foreach($arr as $tagvalue){
            list($tag,$value,$value2)=explode(":",$tagvalue);
            $return[$tag]=$value;
            if($tag=="RESULT" && $value!="E000")
            $this->error="Erreur GLS : ".$value." ".$value2;
        }

        return $return;
    }
    
    function request($test=false,$colisid=null){
        $urldata=parse_url($test?$this->url_test:$this->url);
        if(!isset($urldata['port'])) $urldata['port']=80;
        
        $dbhandle = fsockopen($urldata['host'], $urldata['port'], $errno, $errstr);
        if (!$dbhandle) {
            echo "error hash";
            if($debug) echo "$errstr ($errno)<br>\n";
            return;
        }
        
        $req=$this->encode($colisid);
        
        $request = "POST ".$urldata['path']." HTTP/1.1\r\n";
        $request .= "Host: ".$urldata['host']."\r\n";
    // 	$request .= "Content-Type: text/xml\r\n";
        $request .= "Content-Length: " . strlen($req) . "\r\n";
        $request .= "Connection: close\r\n\r\n";
        $request .= $req;
        
        fputs($dbhandle, $request); 
        $data = "";
            
        while(!feof($dbhandle))
            $data.=fread($dbhandle, 2048);
            
        fclose($dbhandle);
        
        $f=fopen(__DIR__."/gls/".$this->adv->id,"w");
        fwrite($f,$data);
        fclose($f);
    // 	var_dump($data);
        $pos = strpos($data,"\r\n\r\n");
        if($pos != false) 
        {
            $body = substr($data, $pos);
            $header = substr($data,0, $pos);

            $tags=$this->decode($body);
            $this->cle_exp=$tags["T110"];
            $this->cle_rec=$tags["T310"];
            $this->pays_dest=$tags["T100"];
            $this->depot_gls=$tags["T101"];
            $this->tournee_livr=$tags["T320"];
            $this->cp_dest=$tags["T330"];
            $this->trackid=$tags["T8913"];
            $this->trackids[$colisid]=$tags["T8913"];
            $this->datamatrix1[$colisid]=$tags["T8902"];
            $this->datamatrix2[$colisid]=$tags["T8903"];

            return $tags;
        }
        
        return($data);
    }

    function cancelShipitapi($test=false,$trackid){
        //Cette fonction permet d'annuler une commande de transport
        $xml = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:typ="http://fpcs.gls-group.eu/v1/ShipmentProcessing/types">
   <soapenv:Header/>
   <soapenv:Body>
      <typ:TrackID>$trackid</typ:TrackID>
   </soapenv:Body>
</soapenv:Envelope>
XML;

$soap_do = curl_init(); 
        if($test == true){
            curl_setopt($soap_do, CURLOPT_URL,"https://shipit-wbm-test01.gls-group.eu:8443/backend/ShipmentProcessingService/ShipmentProcessingPortType?wsdl"); 
        }
        else{
            curl_setopt($soap_do, CURLOPT_URL,"https://wbm-fr02.shipit.gls-group.com:443/backend/ShipmentProcessingService/ShipmentProcessingPortType?wsdl"); 
        }
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10); 
        curl_setopt($soap_do, CURLOPT_TIMEOUT,        10); 
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);  
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($soap_do, CURLOPT_POST,           true ); 
        curl_setopt($soap_do, CURLOPT_POSTFIELDS,    $xml); 
        curl_setopt($soap_do, CURLOPT_HTTPHEADER,     array('Content-Type: text/xml; charset=utf-8', 'Content-Length: '.strlen($xml) )); 
        if($test == true){
            curl_setopt($soap_do, CURLOPT_USERPWD, "glsfrtestuser:iNsUWuUnLfSHOQvZCdq0");
        }
        else{
            curl_setopt($soap_do, CURLOPT_USERPWD, "25032319ST:Smyq5fFZ62xsfQjeQY9x");
        }

$result = curl_exec($soap_do);
$err = curl_error($soap_do);  
// Vérification des erreurs
if ($test === true) {
var_dump($err);
    // Affichage des détails de la requête et de la réponse en cas d'erreur
    $info = curl_getinfo($soap_do);
    echo '<pre>', htmlspecialchars($info['request_header']), '</pre>'; // En-tête de la requête
    echo '<pre>', htmlspecialchars($xml), '</pre>'; // Contenu de la requête
    echo '<pre>', htmlspecialchars($result), '</pre>'; // Réponse
}
$response1 = str_replace("<soap:Body>","",$result);
$response2 = str_replace("</soap:Body>","",$response1);

// convertingc to XML
$parser = simplexml_load_string($response2);

// On enregistre dans le suivi commande
if(isset($parser->CancelParcelResponse) && !is_null($parser->CancelParcelResponse)){
    $suivi = "Annulation commande GLS : Trackid " . $parser->CancelParcelResponse->TrackID . " : " . $parser->CancelParcelResponse->result;
    $this->DB->query("INSERT INTO suivi_commande SET facturid='".$this->facturid."', date=".time().", suivi='".mysqli_real_escape_string($this->DB->link_id,$suivi)."'");
}
    }

    function shipitapi($test=false,$colisid=null,$parent=0){
        $poidsapi = number_format(($this->poidscolis[$colisid]<100?$this->poidscolis[$colisid]:99.99),2,'.','');
        $incotermapi = $contactPerson = $service = $note1 = $note2 = '';
        if($this->incoterms!="") $incotermapi="<typ:IncotermCode>".$this->incoterms."</typ:IncotermCode>";
        //$this->t750 étant de savoir si c'est flex delivery (et donc particulier), ou un pro
        $name1 = ($this->raison_sociale=="" ? $this->contact : $this->raison_sociale);
        $dateexp = $this->date_obj->format("Y-m-d");
        if($this->raison_sociale==""){
            $service = "<typ:Service>
                    <com:Service>
                        <com:ServiceName>service_flexdelivery</com:ServiceName>
                    </com:Service>
                </typ:Service>";
        }
        else{
            $contactPerson = "<com:ContactPerson>".$this->contact."</com:ContactPerson>";
        }
        if($this->commentaires !== '' && strlen($this->commentaires)>50){
            $note1 = substr($this->commentaires,0,50);
            $note2 = substr($this->commentaires,50,50);
        }
        elseif($this->commentaires !== ''){
            $note1 = $this->commentaires;
        }

        $xml = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:typ="http://fpcs.gls-group.eu/v1/ShipmentProcessing/types" xmlns:com="http://fpcs.gls-group.eu/v1/Common">
    <soapenv:Header/>
    <soapenv:Body>
        <typ:ShipmentRequestData>
            <typ:Shipment>
                <typ:ShippingDate>$dateexp</typ:ShippingDate>
                $incotermapi
                <typ:Product>Parcel</typ:Product>
                <typ:Consignee>
                    <com:Address>
                        <com:Name1>$name1</com:Name1>
                        <com:CountryCode>$this->pays</com:CountryCode>
                        <com:ZIPCode>$this->codepostal</com:ZIPCode>
                        <com:City>$this->ville</com:City>
                        <com:Street>$this->adresse</com:Street>
                        <com:eMail>$this->email</com:eMail>
                        $contactPerson
                        <com:MobilePhoneNumber>$this->telephone</com:MobilePhoneNumber>
                    </com:Address>
                </typ:Consignee>
                <typ:Shipper>
                    <com:ContactID>$this->clientid</com:ContactID>
                    <com:AlternativeShipperAddress>
                        <com:Name1>$this->exp_raisonsociale</com:Name1>
                        <com:CountryCode>$this->exp_pays</com:CountryCode>
                        <com:ZIPCode>$this->exp_codepostal</com:ZIPCode>
                        <com:City>$this->exp_ville</com:City>
                        <com:Street>$this->exp_adresse</com:Street>
                    </com:AlternativeShipperAddress>
                </typ:Shipper>
                <typ:ShipmentUnit>
                    <typ:ShipmentUnitReference>$this->facturid</typ:ShipmentUnitReference>
                    <typ:Weight>$poidsapi</typ:Weight>
                    <typ:Note1>$note1</typ:Note1>
                    <typ:Note2>$note2</typ:Note2>
                </typ:ShipmentUnit>
                $service
            </typ:Shipment>
            <typ:PrintingOptions>
                <typ:ReturnLabels>
                    <typ:TemplateSet>NONE</typ:TemplateSet>
                    <typ:LabelFormat>PDF</typ:LabelFormat>
                </typ:ReturnLabels>
            </typ:PrintingOptions>
            <typ:ReturnOptions>
                <typ:ReturnPrintData>true</typ:ReturnPrintData>
                <typ:ReturnRoutingInfo>true</typ:ReturnRoutingInfo>
            </typ:ReturnOptions>
        </typ:ShipmentRequestData>
    </soapenv:Body>
</soapenv:Envelope>
XML;

        $soap_do = curl_init(); 
        if($test == true){
            curl_setopt($soap_do, CURLOPT_URL,"https://shipit-wbm-test01.gls-group.eu:8443/backend/ShipmentProcessingService/ShipmentProcessingPortType?wsdl"); 
        }
        else{
            curl_setopt($soap_do, CURLOPT_URL,"https://wbm-fr02.shipit.gls-group.com:443/backend/ShipmentProcessingService/ShipmentProcessingPortType?wsdl"); 
        }
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10); 
        curl_setopt($soap_do, CURLOPT_TIMEOUT,        10); 
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);  
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($soap_do, CURLOPT_POST,           true ); 
        curl_setopt($soap_do, CURLOPT_POSTFIELDS,    $xml); 
        curl_setopt($soap_do, CURLOPT_HTTPHEADER,     array('Content-Type: text/xml; charset=utf-8', 'Content-Length: '.strlen($xml) )); 
        if($test == true){
            curl_setopt($soap_do, CURLOPT_USERPWD, "glsfrtestuser:iNsUWuUnLfSHOQvZCdq0");
        }
        else{
            curl_setopt($soap_do, CURLOPT_USERPWD, "25032319ST:Smyq5fFZ62xsfQjeQY9x");
        }

        $result = curl_exec($soap_do);
        $err = curl_error($soap_do);  
// Vérification des erreurs
        if($test === true) {
            var_dump($err);
    // Affichage des détails de la requête et de la réponse en cas d'erreur
            $info = curl_getinfo($soap_do);
            echo '<pre>', htmlspecialchars($info['request_header']), '</pre>'; // En-tête de la requête
            echo '<pre>', htmlspecialchars($xml), '</pre>'; // Contenu de la requête
            echo '<pre>', htmlspecialchars($result), '</pre>'; // Réponse
        }
        $response1 = str_replace("<soap:Body>","",$result);
        $response2 = str_replace("</soap:Body>","",$response1);

// On enregistre le fichier que l'on envoie dans le dossier out
        file_put_contents(__DIR__."/gls/out/".$this->adv->id. '.xml', $xml);
// Et on enregistre le fichier que l'on reçoit en réponse dans le dossier in
        file_put_contents(__DIR__."/gls/in/".$this->adv->id. '.xml', $result);

// convertingc to XML
        $parser = simplexml_load_string($response2);

// si erreur on l'enregistre dans le suivi commande
        if(isset($parser->Fault->faultstring) && !is_null($parser->Fault->faultstring)){
            $suivi = "Erreur d'envoi EDI, voir avec le service informatique. Erreur : " . $parser->Fault->faultstring . " : " . $parser->Fault->detail->InvalidFieldValueFault->field[1]->value;
            $this->DB->query("INSERT INTO suivi_commande SET facturid='".$this->facturid."', date=".time().", suivi='".mysqli_real_escape_string($this->DB->link_id,$suivi)."'");
        }

// On crée le PDF avec les données en réponse
        $binaryData = base64_decode($parser->CreateParcelsResponse->CreatedShipment->PrintData->Data);
// Et on va l'enregistrer
        $file="/tmp/".$this->adv->id.".pdf";
        file_put_contents($file, $binaryData);
        $pdfsize=filesize($file);
        if($this->origin=="cmd"){
            $this->DB->query("INSERT INTO user_files SET userid=".$this->userid.", facturid=".$this->facturid.", parent=$parent, date=".time().", utilisateur='".$this->adv->utilisateur."', name='Etiquette GLS ".date("Ymd-Hi").".pdf', size=".$pdfsize.", ext='pdf', state=1");
            $this->pjid=$this->DB->insert_id();
            rename($file,__DIR__."/../userfiles/".$this->pjid.".pdf");
        }
        if($this->origin=="stock"){
            $this->DB->query("INSERT INTO fournisseur_files SET fournisseur='".mysqli_real_escape_string($this->DB->link_id,$this->adv->destinataire)."', stock_cmd_id=".$this->facturid.", date=".time().", utilisateur='".$this->adv->utilisateur."', name='Etiquette GLS ".date("Ymd-Hi").".pdf', size=".$pdfsize.", ext='pdf', state=1");
            $this->pjid=$this->DB->insert_id();
            rename($file,__DIR__."/../fournfiles/".$this->pjid.".pdf");
        }

// On enregistre le trackid
        $this->trackid = $parser->CreateParcelsResponse->CreatedShipment->ParcelData->TrackID;

}

    function send($test=false,$parent=0){
        foreach($this->colis as $colisid=>$lines){
            $this->shipitapi($test,$colisid,$parent);
        }
    }
    
    function etiquette($pj=false,$version="_small", $parent=0){
        if($this->error()!==false) $this->uniship=true;

        $this->pdf=new pdf();
        $this->pdf->setautopagebreak(false);
        foreach($this->colis as $colisid=>$lines){
            $this->{"page".$version}($colisid); //appel la fonction page
        }

        if($pj){
            $file="/tmp/".$this->adv->id.".pdf";
            $this->pdf->output($file,"F");
            $pdfsize=filesize($file);
            if($this->origin=="cmd"){
                $this->DB->query("INSERT INTO user_files SET userid=".$this->userid.", facturid=".$this->facturid.", parent=$parent, date=".time().", utilisateur='".$this->adv->utilisateur."', name='Etiquette GLS ".date("Ymd-Hi").".pdf', size=".$pdfsize.", ext='pdf', state=1");
                $this->pjid=$this->DB->insert_id();
                rename($file,__DIR__."/../userfiles/".$this->pjid.".pdf");
            }
            if($this->origin=="stock"){
                $this->DB->query("INSERT INTO fournisseur_files SET fournisseur='".mysqli_real_escape_string($this->DB->link_id,$this->adv->destinataire)."', stock_cmd_id=".$this->facturid.", date=".time().", utilisateur='".$this->adv->utilisateur."', name='Etiquette GLS ".date("Ymd-Hi").".pdf', size=".$pdfsize.", ext='pdf', state=1");
                $this->pjid=$this->DB->insert_id();
                rename($file,__DIR__."/../fournfiles/".$this->pjid.".pdf");
            }
        }else
            $this->pdf->output();
        
    }
    
    
    function page($colisid){
        
        $this->pdf->addpage(2,"A4");
        
        $this->pdf->setfillcolor(0);
        $this->pdf->settextcolor(255);
        $this->pdf->setfont('arial','B',28);
        $this->pdf->setXY(9.5,8.5);
        $this->pdf->cell(128,16.5,"Specimen client",0,0,'C',1);
        
        $this->pdf->settextcolor(0);
        $this->pdf->setfont('arial','',16);
        $this->pdf->setXY(31,30.6);
        $this->pdf->cell(46,5,"Votre GLS Track ID");
        
        $this->pdf->setfont('arial','B',20);
        $this->pdf->setXY(84,30.6);
        $this->pdf->cell(46,5,$this->trackids[$colisid]);
        
        $this->pdf->setdrawcolor(0);
        $this->pdf->setlinewidth(0.5);
        $this->pdf->line(9.5,39,137.5,39);
        
        $this->pdf->setfont('arial','B',16);
        $this->pdf->setXY(9.5,43);
        $this->pdf->multicell(70,7,utf8_decode("Numéro colis\nDate d'expédition\nPoids\nColis"));
        
        $this->pdf->setXY(70,43);
        $this->pdf->multicell(60,7,$this->trackids[$colisid]."\n".$this->date_obj->format("d/m/Y")."\n".number_format($this->poidscolis[$colisid],2,'.','')."KG\n".str_pad($colisid,3,"0",STR_PAD_LEFT)."/".str_pad(count($this->colis),3,"0",STR_PAD_LEFT)."");
        
        $this->pdf->setfont('arial','',14);
        $this->pdf->setXY(9.5,73.8);
        $this->pdf->cell(60,5,"Site GLS");
        $this->pdf->setXY(70,73.8);
        $this->pdf->cell(60,5,$this->depot);
        
        if(!$this->uniship){
            $this->pdf->setlinewidth(2);
            $this->pdf->line(160,8.5,290,8.5);
            
            $this->pdf->setlinewidth(0.5);
            $x=160;
            $this->pdf->line($x,24.6,$x+129,24.6);
            $this->pdf->line($x,39.6,$x+129,39.6);
            $this->pdf->line($x,75.5,$x+129,75.5);
            
            
            $this->pdf->setlinewidth(1);
            $frx=160;
            $fry=40;
            $frs=35;	//taille coté
            $frl=4;	// long trait
            $this->pdf->line($frx,$fry,$frx+$frl,$fry);
            $this->pdf->line($frx+$frs-$frl,$fry,$frx+$frs,$fry);
            $this->pdf->line($frx,$fry,$frx,$fry+$frl);
            $this->pdf->line($frx+$frs,$fry,$frx+$frs,$fry+$frl);
            $this->pdf->line($frx,$fry+$frs-$frl,$frx,$fry+$frs);
            $this->pdf->line($frx,$fry+$frs,$frx+$frl,$fry+$frs);
            $this->pdf->line($frx+$frs-$frl,$fry+$frs,$frx+$frs,$fry+$frs);
            $this->pdf->line($frx+$frs,$fry+$frs-$frl,$frx+$frs,$fry+$frs);
            
            $this->pdf->setFont('arial','B',32);
            $this->pdf->setXY(160,9.5);
            $this->pdf->cell(41,16,$this->cle_exp);
            
            $this->pdf->setFont('arial','B',28);
            $this->pdf->setXY(160,28.4);
            $this->pdf->cell(30,10,$this->tournee_livr);
            
            $this->pdf->setFont('arial','B',8);
            $this->pdf->setXY(190,26);
            $this->pdf->cell(14,5,"Code Postal",0,0,'C');
            
            $this->pdf->setFont('arial','B',16);
            $this->pdf->setXY(188,32);
            $this->pdf->cell(15,6,$this->codepostal);
            
            
            $this->pdf->settextcolor(255);
            $this->pdf->setFont('arial','B',36);
            $this->pdf->setXY(201.7,9.5);
            $this->pdf->cell(9.4,14.9,$this->cle_rec,0,0,'C',1);
            
            $this->pdf->settextcolor(0);
            $this->pdf->setFont('arial','B',32);
            $this->pdf->setXY(230,9.5);
            $this->pdf->cell(15.2,16,$this->pays_dest,0,0,'C');
            
            $this->pdf->settextcolor(255);
            $this->pdf->setFont('arial','B',32);
            $this->pdf->setXY(252,9);
            $this->pdf->cell(39,15.8,$this->depot_gls,0,0,'C',1);
            
            $this->pdf->settextcolor(0);
            $this->pdf->setFont('arial','B',8);
            $this->pdf->setXY(212,25.7);
            $this->pdf->cell(25,5,"Votre GLS Track ID");
            
            $this->pdf->setFont('arial','B',16);
            $this->pdf->setXY(212,32);
            $this->pdf->cell(25,6,$this->trackids[$colisid]);

            $this->pdf->setFont('arial','B',17);
            $this->pdf->setXY(272,32);
            $this->pdf->cell(20,5,$this->t200);
            
            $dm1file=$this->datamatrix($this->datamatrix1[$colisid]);
            $this->pdf->image($dm1file,163,42.8,29,29,"jpg");
            unlink($dm1file);
            
            $dm2file=$this->datamatrix($this->datamatrix2[$colisid]);
            $this->pdf->image($dm2file,259,43,29,29,"jpg");
            unlink($dm2file);
            
            $this->pdf->setFont('arial','',12);
            $this->pdf->setXY(214,70);
            $this->pdf->cell(25,6,$this->trackids[$colisid]);
            
            $this->pdf->setFont('arial','B',14);
            $this->pdf->setXY(160,79);
            $this->pdf->cell(18,4,$this->depot);
        }else{
            $data=$this->uniship($colisid);
            $dmfile=$this->datamatrix($data);
            $this->pdf->image($dmfile,200,32.8,42,42,"jpg");
            unlink($dmfile);
        }
        $this->pdf->setFont('arial','B','16');
        $this->pdf->setXY(215,79);
        $this->pdf->cell(20,4,number_format($this->poidscolis[$colisid],2,'.','')."KG");
        
        $this->pdf->setfont('arial','',11);
        $this->pdf->setXY(245,80);
        $this->pdf->cell(10,4,str_pad($colisid,3,"0",STR_PAD_LEFT)."/".str_pad(count($this->colis),3,"0",STR_PAD_LEFT)."");
        
        for($tx=11.5;$tx<300;$tx+=150){
            $ty=85;
            $this->pdf->setlinewidth(0.25);
            $this->pdf->rect($tx,$ty,126,110);
            $this->pdf->line($tx+103,$ty,$tx+103,$ty+110);
            $this->pdf->line($tx,$ty+40,$tx+103,$ty+40);
            $this->pdf->line($tx,$ty+80,$tx+103,$ty+80);
            
            $this->pdf->setFont('arial','',11);
            $this->pdf->rotate(-90,$tx+126,$ty+1);
            $this->pdf->setXY($tx+126,$ty+2);
            $this->pdf->multicell(100,4,utf8_decode("Expediteur   Client ".$this->clientid." Contact ID ".$this->contactid."\n".$this->exp_raisonsociale."\n".$this->exp_adresse."\n".$this->exp_pays." ".$this->exp_codepostal." ".$this->exp_ville),0,'L');
            $this->pdf->rotate(0);
            
            $this->pdf->setfont('arial','',14);
            $this->pdf->setXY($tx+1,$ty+44);
            $dest=utf8_decode($this->raison_sociale."\n".$this->adresse."\n".$this->pays." ".$this->codepostal." ".$this->ville);
            $this->pdf->multicell(102,6,$dest);
            
            $this->pdf->setFont('arial','',11);
            $this->pdf->setXY($tx+62,$ty+41);
            $this->pdf->cell(22,4,"Ref Dest : ".$this->userid);
            
            $this->pdf->setXY($tx+1,$ty+81);
            $infos=utf8_decode("Contact : ".$this->contact."\n"."Tel : ".$this->telephone."\nNote : ".$this->commentaires."\nRef : ".$this->facturid);
            $this->pdf->multicell(102,5,$infos);
        }
	
    }
    
    function page_small($colisid){
        $this->pdf->addpage('P',"A4");
        
        $x=9;
        $y=11;
        $w=188;
        $h=86;
        
        $this->pdf->setlinewidth(0.1);
        $this->pdf->rect($x,$y,$w,$h);
        $this->pdf->line($x+101,$y,$x+101,$y+$h);
        $this->pdf->line($x+172,$y,$x+172,$y+$h);
        $this->pdf->line($x+101,$y+46,$x+172,$y+46);
        $this->pdf->line($x+101,$y+71,$x+172,$y+71);
    
        
        $this->pdf->setlinewidth(0.5);
        $this->pdf->line($x,$y+$h+8,$x+$w,$y+$h+8);
        
        $this->pdf->image(__DIR__."/../images/gls_etiquette.jpg",$x+0.5,$y+$h-9.5,88);
    
        $this->pdf->setFont('arial','',6);
        $this->pdf->setXY($x+144,$y+46);
        $this->pdf->cell(20,3,"Ref. Client");
        
        $this->pdf->setFillColor(0,0,0);
        $this->pdf->settextcolor(255);
        $this->pdf->setfont('arial','',10);
        $this->pdf->setXY($x,$y);
        $this->pdf->cell($w,10,utf8_decode("Merci de vous assurer que la partie basse de cet imprimé est clairement lisible une fois placé sous enveloppe plastique"),0,0,'C',1);
        
        $this->pdf->setfillcolor(255);
        $this->pdf->settextcolor(0);
        
        
        if(!$this->uniship){
            $this->pdf->setfillcolor(0);
            $this->pdf->settextcolor(255);
    
            $this->pdf->setlinewidth(0.4);
            $this->pdf->line($x,$y+36,$x+88,$y+36);
            $this->pdf->line($x,$y+46,$x+88,$y+46);
            $this->pdf->line($x,$y+71,$x+88,$y+71);
            
            $this->pdf->setlinewidth(1);
            $this->pdf->line($x+0.5,$y+26,$x+88,$y+26);
    
            $this->pdf->setFont('arial','B',26);
            $this->pdf->setXY($x+28,$y+26);
            $this->pdf->cell(10,10,$this->cle_rec,0,0,'C',1);
            
            $this->pdf->setXY($x+63.5,$y+26);
            $this->pdf->cell(25,10,$this->depot_gls,0,0,'C',1);
                            
            $this->pdf->setfillcolor(255);
            $this->pdf->settextcolor(0);
            
            $this->pdf->setFont('arial','',6);
            $this->pdf->setXY($x+20,$y+36.5);
            $this->pdf->cell(20,3,"Code Postal");
            $this->pdf->setXY($x+37,$y+36.5);
            $this->pdf->cell(20,3,"Votre GLS Track ID");
            
            $this->pdf->setFont('arial','B',26);
            $this->pdf->setXY($x,$y+26.5);
            $this->pdf->cell(10,10,$this->cle_exp);
            
            $this->pdf->setXY($x+46,$y+26.5);
            $this->pdf->cell(10,10,$this->pays_dest);
            
            $this->pdf->setFont('arial','',22);
            $this->pdf->setXY($x,$y+39);
            $this->pdf->cell(20,5,$this->tournee_livr);
            
            $this->pdf->setFont('arial','',12);
            $this->pdf->setXY($x+20,$y+41);
            $this->pdf->cell(20,5,$this->codepostal);
            
            $this->pdf->setXY($x+36,$y+41);
            $this->pdf->cell(20,5,$this->trackids[$colisid]);

            $this->pdf->setFont('arial','B',17);
            $this->pdf->setXY($x+75,$y+39);
            $this->pdf->cell(20,5,$this->t200);
            
            
            $this->pdf->setlinewidth(0.6);
            $frx=$x+1;
            $fry=$y+47;
            $frs=23;	//taille coté
            $frl=2;	// long trait
            $this->pdf->line($frx,$fry,$frx+$frl,$fry);
            $this->pdf->line($frx+$frs-$frl,$fry,$frx+$frs,$fry);
            $this->pdf->line($frx,$fry,$frx,$fry+$frl);
            $this->pdf->line($frx+$frs,$fry,$frx+$frs,$fry+$frl);
            $this->pdf->line($frx,$fry+$frs-$frl,$frx,$fry+$frs);
            $this->pdf->line($frx,$fry+$frs,$frx+$frl,$fry+$frs);
            $this->pdf->line($frx+$frs-$frl,$fry+$frs,$frx+$frs,$fry+$frs);
            $this->pdf->line($frx+$frs,$fry+$frs-$frl,$frx+$frs,$fry+$frs);
            
            $dm1file=$this->datamatrix($this->datamatrix1[$colisid]);
            $this->pdf->image($dm1file,$x+2.5,$y+48,20,20,"jpg");
            unlink($dm1file);
            
            $dm2file=$this->datamatrix($this->datamatrix2[$colisid]);
            $this->pdf->image($dm2file,$x+68,$y+48,20,20,"jpg");
            unlink($dm2file);
        }else{
            $data=$this->uniship($colisid);
            $dmfile=$this->datamatrix($data);
            $this->pdf->image($dmfile,$x+30,$y+20,42,42,"jpg");
            unlink($dmfile);
        }
        
        $this->pdf->setFont('arial','',10);
        $this->pdf->setXY($x,$y+72);
        $this->pdf->cell(20,4,$this->depot);
        
        $this->pdf->setXY($x+38,$y+72);
        $this->pdf->cell(20,4,number_format($this->poidscolis[$colisid],2)."KG");
        
        $this->pdf->setFont('arial','',7);
                
        $this->pdf->setXY($x+20,$y+72.5);
        $this->pdf->cell(10,4,$this->date_obj->format("d/m/Y"));
        
        $this->pdf->setXY($x+58,$y+72.5);
        $this->pdf->cell(10,4,str_pad($colisid,3,"0",STR_PAD_LEFT)."/".str_pad(count($this->colis),3,"0",STR_PAD_LEFT)."");
        
        //cadre expediteur
        $this->pdf->setFont('arial','',7);
        $this->pdf->rotate(-90,$x+$w,$y+10);
        $this->pdf->setXY($x+$w,$y+10);
        $this->pdf->multicell(70,3,utf8_decode("Expediteur   Client ".$this->clientid." Contact ID ".$this->contactid."\n".$this->exp_raisonsociale."\n".$this->exp_adresse."\n".$this->exp_pays." ".$this->exp_codepostal." ".$this->exp_ville),0,'L');
        $this->pdf->rotate(0);
        
        //cadre t200
        $this->pdf->setfont('arial','',12);
        $this->pdf->setXY($x+102,$y+10);
        $flex = "";
        if($this->raison_sociale==''){
           $flex = "Flex Delivery Service";
        }
        $this->pdf->multicell(70,5,$flex); 

        //cadre infos destinataire
        $this->pdf->setfont('arial','',10);
        $this->pdf->setXY($x+102,$y+49);
        $dest=utf8_decode($this->raison_sociale."\n".$this->adresse."\n".$this->pays." ".$this->codepostal." ".$this->ville);
        $this->pdf->multicell(70,5,$dest);
        
        $this->pdf->setFont('arial','',7);
        $this->pdf->setXY($x+102,$y+72);
        $infos=utf8_decode("Contact : ".$this->contact."\n"."Tel : ".$this->telephone."\nNote : ".$this->commentaires."\nRef : ".$this->facturid);
        $this->pdf->multicell(70,3,$infos);

    }
    
    function datamatrix($code){
        $path=tempnam("/tmp","gls");
        $w=$h=500;
        $x=$y=$w/2;
        $angle=0;
        $type="datamatrix";
        
        $im = imagecreatetruecolor(500, 500);
        $black  = ImageColorAllocate($im,0x00,0x00,0x00);
        $white  = ImageColorAllocate($im,0xff,0xff,0xff);
        imagefilledrectangle($im, 0, 0, 500, 500, $white);
        $cols=BarcodeDatamatrix::getLenghtColumn($code);
        $width=$cols>0?floor($w/$cols):10;
        $data = Barcode::gd($im, $black, $x, $y, $angle, $type, array('code'=>$code), $width);
        
        imagejpeg($im,$path);
        
        return $path;
    }
    
    function uniship($colisid){
            // 

        $data="A|".
        $this->clientid."|".
        $this->contactid."|".
        $this->produit."|".
        str_pad($this->iso3166,3,"0",STR_PAD_LEFT)."|".
        $this->codepostal."|".
        count($this->colis)."|".
        $colisid."|".
        $this->exp_ref."|".
        utf8_decode($this->raison_sociale)."|".
        utf8_decode($this->contact)."||".
        utf8_decode($this->adresse)."||".
        utf8_decode($this->ville)."|".
        $this->telephone."|".
        $this->facturid."|".
        $this->code_produit.str_pad($this->exp_ref,10,"0",STR_PAD_LEFT)."0000".$this->pays."|".
        str_pad(number_format($this->poidscolis[$colisid],2,'.',''),5,"0",STR_PAD_LEFT)."|";
        
        $data=str_pad($data,303," ",STR_PAD_RIGHT)."|";
    
        return $data;

    }

    function getTracking($trackid){
        $xml = <<<XML
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:trac="http://fpcs.gls-group.eu/v1/Tracking">
            <soapenv:Header/>
            <soapenv:Body>
                <trac:DetailsReferenceData>
                    <trac:TrackID>$trackid</trac:TrackID>
                </trac:DetailsReferenceData>
            </soapenv:Body>
        </soapenv:Envelope>
XML;
        $soap_do = curl_init(); 
        if($test == true){
            curl_setopt($soap_do, CURLOPT_URL,"https://shipit-wbm-test01.gls-group.eu:8443/backend/TrackingService/TrackingPortType?wsdl"); 
        }
        else{
            curl_setopt($soap_do, CURLOPT_URL,"https://wbm-fr02.shipit.gls-group.com:443/backend/TrackingService/TrackingPortType?wsdl"); 
        }
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10); 
        curl_setopt($soap_do, CURLOPT_TIMEOUT,        10); 
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);  
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($soap_do, CURLOPT_POST,           true ); 
        curl_setopt($soap_do, CURLOPT_POSTFIELDS,    $xml); 
        curl_setopt($soap_do, CURLOPT_HTTPHEADER,     array('Content-Type: text/xml; charset=utf-8', 'Content-Length: '.strlen($xml) )); 
        if($test == true){
            curl_setopt($soap_do, CURLOPT_USERPWD, "glsfrtestuser:iNsUWuUnLfSHOQvZCdq0");
        }
        else{
            curl_setopt($soap_do, CURLOPT_USERPWD, "25032319ST:Smyq5fFZ62xsfQjeQY9x");
        }

        $result = curl_exec($soap_do);
        $err = curl_error($soap_do);  
        // Vérification des erreurs
        if ($test === true) {
            var_dump($err);
            // Affichage des détails de la requête et de la réponse en cas d'erreur
            $info = curl_getinfo($soap_do);
            echo '<pre>', htmlspecialchars($info['request_header']), '</pre>'; // En-tête de la requête
            echo '<pre>', htmlspecialchars($xml), '</pre>'; // Contenu de la requête
            echo '<pre>', htmlspecialchars($result), '</pre>'; // Réponse
        }
        $response1 = str_replace("<soap:Body>","",$result);
        $response2 = str_replace("</soap:Body>","",$response1);

        // convertingc to XML
        $parser = simplexml_load_string($response2);

        return $parser;
    }
    
    
    function tracking($trackid){
	    $gls_user=GLS_USER;
	    $gls_password=GLS_PASSWORD;
	    $xml=<<<XML
	      <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns="http://gls-group.eu/Tracking/">
		<soapenv:Header/>
		<soapenv:Body>
		  <TuDetailsRequest>
		      <RefValue>$trackid</RefValue>
		      <Credentials>
			<UserName>{$gls_user}</UserName>
			<Password>{$gls_password}</Password>
		      </Credentials>
		  </TuDetailsRequest>
		</soapenv:Body>
	      </soapenv: Envelope>
XML;

        $client = new SoapClient('http://www.gls-group.eu/276-I-PORTAL-WEBSERVICE/services/Tracking/wsdl/Tracking.wsdl', array('login' => GLS_USER, 'password' => GLS_PASSWORD, 'trace'=>true));
        $reference = array(
            'Credentials' => array('UserName' => GLS_USER, 'Password' => GLS_PASSWORD),
            'RefValue' => '009NDFFT'
        );
    // 	  $result = $client->GetTuDetail($trackid);
        $reference=array("Credentials"=>array('UserName' => GLS_USER, 'Password' => GLS_PASSWORD),"DateFrom"=>array("Year"=>2016,"Month"=>7,"Day"=>1,"Hour"=>0,"Minut"=>0),"DateTo"=>array("Year"=>2016,"Month"=>7,"Day"=>15,"Hour"=>0,"Minut"=>0));
        $result=$client->GetTuList($reference);
        var_dump($client->__getLastRequest());
        var_dump($result);
    /*
        $urldata=parse_url("http://gls-group.eu/Tracking/TUDetailOperation");
            $dbhandle = fsockopen($urldata['host'], 80, $errno, $errstr);
            if (!$dbhandle) {
                echo "error hash";
                if($debug) echo "$errstr ($errno)<br>\n";
                return;
            }

            $req=$xml;
            
            $request = "POST ".$urldata['path']." HTTP/1.1\r\n";
            $request .= "Host: ".$urldata['host']."\r\n";
        // 	$request .= "Content-Type: text/xml\r\n";
            $request .= "Content-Length: " . strlen($req) . "\r\n";
            $request .= "Connection: close\r\n\r\n";
            $request .= $req;
            
            fputs($dbhandle, $request); 
                
            $data=fread($dbhandle, 2048);
                var_dump($data);
            fclose($dbhandle);
*/
    }
    
    function error(){
        return $this->error==""?false:$this->error;
    }
    
    function trackid(){
        return $this->trackid;
    }

    function pjid(){
        return $this->pjid;
    }
}


?>
