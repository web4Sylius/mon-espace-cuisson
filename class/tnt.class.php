<?php
require_once(SITE_PATH."/class/fpdf.php");

class tnt {

    private $DB;
    private $adv;
    private $soapclient;
    
    private $username;
    private $pass;
    private $wsdl      = 'http://www.tnt.fr/service/?wsdl';
    private $wsdl_cout = 'http://www.tnt.fr/service/cost?wsdl';

    public $test_mode = false;
    public $error     = false;

    public $compte = null;
    public $format_etiquette = null;

    public $expediteur    = array();
    private $expediteur_id      = '';
    private $expediteur_societe = '';
    private $expediteur_adresse = '';
    private $expediteur_cp      = '';
    private $expediteur_ville   = '';
    private $expediteur_nom     = '';
    private $expediteur_prenom  = '';
    private $expediteur_email   = '';
    private $expediteur_tel     = '';

    public $destinataire    = array();
    private $destinataire_type    = '';
    private $destinataire_type_id = '';
    private $destinataire_societe = '';
    private $destinataire_adresse = '';
    private $destinataire_cp      = '';
    private $destinataire_ville   = '';
    private $destinataire_instr   = '';
    private $destinataire_nom     = '';
    private $destinataire_prenom  = '';
    private $destinataire_email   = '';
    private $destinataire_tel     = '';
    private $destinataire_codeporte   = '';
    private $destinataire_etage   = '';

    public $pickUpRequest  = '';
    public $parcelsRequest = array();
    public $services       = '';
    public $service        = '';
    public $colis          = array();

    private $date_expedition_fr = '';
    private $date_expedition_en = '';

    private $code_option  = '';
    private $code_produit = '';

    private $trackid = '';
    private $pjid    = '';

    private $cout = array();

    public function __construct($DB,$adv=null,$format = 'STDA4',$username,$pass){
		
        $this->DB     = $DB;
        $this->username = $username;
        $this->pass = $pass;
        if(is_null($adv)) return;
        $this->adv    = $adv;
        $this->format_etiquette = $format;

    	$this->setConnexion();

    	$this->facture = new facture($this->DB,$this->adv->facturid);
        if($this->facture->facturid==$this->adv->facturid){
            $this->origin="cmd";
        }else{
            $this->stock_cmd=new stock_cmd($this->DB,$this->adv->facturid);
            if($this->stock_cmd->id==$this->adv->facturid)
                $this->origin="stock";
        }

        $this->setExpediteur();
        $this->setDestinataire();
        $this->setDateExpedition();
        $this->setCompte(); //On détermine le compte en fonction du lieu d'expédition des marchandises (défini dans setDateExpedition())
        $this->faisabilite();
        $this->setColis();
        $this->setCout();
    }

    private function setConnexion(){
    	
    	$connexion = ""
    	. "<wsse:Security xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">"
	    	. "<wsse:UsernameToken>"
		    	. "<wsse:Username>%s</wsse:Username>"
		    	. "<wsse:Password>%s</wsse:Password>"
	    	. "</wsse:UsernameToken>"
    	. "</wsse:Security>";
		
		$authheader = sprintf($connexion, htmlspecialchars($this->username), htmlspecialchars($this->pass));

		$authvars = new SoapVar($authheader, XSD_ANYXML);
		$header   = new SoapHeader("http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd", "Security", $authvars);

        try {
            $this->soapclient = new SoapClient($this->wsdl, array(
                'trace'=> 1,
                'stream_context'=>stream_context_create(
                    array(
                        'http'=> array(
                            'user_agent' => 'PHP/SOAP',
                            'accept' => 'application/xml'
                    )
                ))
            ));

            $this->soapclient->__setSOAPHeaders(array($header));
        } 
        catch(SoapFault $e){

            $this->error = 'Erreur de connexion au webservice (expédition)';
            echo $this->error;

        }

        try {
            $this->soapclient_cout = new SoapClient($this->wsdl_cout, array(
                'trace'=> 1,
                'stream_context'=>stream_context_create(
                    array(
                        'http'=> array(
                            'user_agent' => 'PHP/SOAP',
                            'accept' => 'application/xml'
                    )
                ))
            ));

            $this->soapclient_cout->__setSOAPHeaders(array($header));
        } 
        catch(SoapFault $e){

            $this->error = 'Erreur de connexion au webservice (coût)';
            nice($e);

        }

    }

    private function setCompte(){

        switch($this->expediteur_id){

            //Acilyon
            case 217:

                $this->compte = '06393453';

                break;

            //Climadiff/La Sommelière
            case 631267:
            case 631268:
            case 59:
                
                $this->compte = '08926137';

                break;

            //L'Atelier du Vin
            case 62:

                $this->compte = '08926142';

                break;
        }

    }

    private function getFournisseur($id){

        $sql = "SELECT f.societe, f.prenom, f.nom, f.mail, f.telephone, f.adresse1, f.cp, f.ville, p.diminutif";
        $sql .= " FROM fournisseur f";
        $sql .= " INNER JOIN pays p ON (p.paysid = f.paysid)";
        $sql .= " WHERE f.id = ".intval($id);
        $aFourn = $this->DB->query_first($sql);

        return $aFourn;

    }

    public function setPickUpRequest(){

        $this->pickUpRequest = array(
            'media'          => 'EMAIL', //Type de media pour la notification liée aux anomalies de ramassage
            'faxNumber'      => '', //Numéro de fax (OBSOLETE – non traité)
            'emailAddress'   => MAIL_ADV, //E-mail (peut être répété jusqu’à 10 fois)
            'notifySuccess'  => 1, //Indicateur de notification également en cas de succès du ramassage (1 : oui / 0 : non)
            'service'        => '', //Service à contacter à l’adresse de ramassage
            'lastName'       => $this->expediteur_nom, //Nom du contact pour le ramassage
            'firstName'      => $this->expediteur_prenom, //Prénom du contact pour le ramassage
            'phoneNumber'    => $this->expediteur_tel, //Numéro de téléphone du contact pour le ramassage
            'closingTime'    => '17:00',
            'instructions'   => ''
        );

    }

    private function setExpediteur(){
		
        //Récupération des informations sur les coordonnées du lieu d'expédition
        if($this->origin=="cmd")
            $this->expediteur_id = substr($this->adv->fields['lieutrspt'],5);
        if($this->origin=="stock")
            $this->expediteur_id=$this->stock_cmd->enlevement;
            
        $aFournisseur = $this->getFournisseur($this->expediteur_id);

        $telDest = $aFournisseur['telephone'] != '' ? $aFournisseur['telephone'] : $aFournisseur['portable'];
        $telDest = str_replace(array('-','.',' '),'',$telDest);

		$this->expediteur_societe = $aFournisseur['societe'];
		$this->expediteur_nom     = $aFournisseur['nom'];
		$this->expediteur_prenom  = $aFournisseur['prenom'];
		$this->expediteur_adresse = $aFournisseur['adresse1'];
		$this->expediteur_cp      = $aFournisseur['cp'];
		$this->expediteur_ville   = $aFournisseur['ville'];
		$this->expediteur_email   = $aFournisseur['mail'];
		$this->expediteur_tel     = $telDest;

        //FORMATAGE TABLEAU COORDONNES EXPEDITEUR
        if($this->expediteur_societe != ''){
            $nom_formate = $this->expediteur_societe . '<br>' . $this->expediteur_prenom.' '.$this->expediteur_nom;
        } else {
            $nom_formate = $this->expediteur_prenom.' '.$this->expediteur_nom;
        }
        $this->expediteur = array(
            "type"             => 'ENTERPRISE',
            "typeId"           => '',
            "name"             => $this->expediteur_societe, 
            "address1"         => $this->expediteur_adresse,
            "zipCode"          => $this->expediteur_cp, 
            "city"             => $this->expediteur_ville, 
            "contactLastName"  => $this->expediteur_nom, 
            "contactFirstName" => $this->expediteur_prenom, 
            "emailAddress"     => $this->expediteur_email, 
            "phoneNumber"      => $this->expediteur_tel
        );

    }

    private function setDestinataire(){
       
        if($this->origin=="cmd"){ // commande client

            if(in_array($this->adv->fields['trsptlivr'], array('client','societe'))){

                $telDest = $this->facture->telephone != '' && $this->facture->telephone != 'inconnu' ? $this->facture->telephone : $this->facture->portable;
                if(substr($telDest,0,1) == '+') $telDest = '0'.substr($telDest,3);
                $telDest = str_replace(array('-','.',' '),'',$telDest);         

                switch($this->adv->fields['trsptlivr'])
                {
                    case 'client':
                        
                        $this->code_option       = 'Z';
                        $this->destinataire_type = 'INDIVIDUAL';
                        
                        //Récupération code porte et etage
                        $aInfoTranspo = explode(',',$this->facture->infotranspo);

                        if($aInfoTranspo != ''){
                            foreach($aInfoTranspo as $l_info){
                                preg_match('#Digicode:([a-zA-Z0-9]+)$#', $l_info, $matchesCP);
                                preg_match('#Etage:([a-zA-Z0-9]+)$#', $l_info, $matchesEtage);

                                if(isset($matchesCP[1])) $this->destinataire_codeporte = $matchesCP[1];
                                if(isset($matchesEtage[1])) $this->destinataire_etage = $matchesEtage[1];
                            }  
                        }
                        
                        break;
                    case 'societe':
                        
                        $this->code_option        = '';
                        $this->destinataire_type  = 'ENTERPRISE';
                        $this->destinataire_instr = $this->adv->fields['infocomplement'];
                        
                        break;
                }
                
                $this->destinataire_societe   = $this->facture->raisonsociale;
                $this->destinataire_nom       = $this->facture->nom;
                $this->destinataire_prenom    = $this->facture->prenom;
                $this->destinataire_adresse   = $this->facture->adresse;
                $this->destinataire_adresse2  = $this->facture->adresse2;
                $this->destinataire_cp        = $this->facture->codepostal;
                $this->destinataire_ville     = $this->facture->ville;
                $this->destinataire_email     = $this->facture->mail;
                $this->destinataire_tel       = $telDest; 

            } else if($this->adv->fields['trsptlivr'] == 'relaiscolis'){

                $telDest = $this->facture->telephone != '' && $this->facture->telephone != 'inconnu' ? $this->facture->telephone : $this->facture->portable;
                if(substr($telDest,0,1) == '+') $telDest = '0'.substr($telDest,3);
                $telDest = str_replace(array('-','.',' '),'',$telDest);

                $this->code_option          = 'D';
                $this->destinataire_type    = 'DROPOFFPOINT';
                $this->destinataire_type_id = $this->adv->fields['relaiscolisxett']; //Code xett (uniquement pour point relais)
                $this->destinataire_cp      = $this->adv->fields['relaiscoliscp'];
                $this->destinataire_ville   = $this->adv->fields['relaiscolisville'];
                $this->destinataire_nom     = $this->facture->nom;
                $this->destinataire_prenom  = $this->facture->prenom;
                $this->destinataire_email   = $this->facture->mail;
                $this->destinataire_tel     = $telDest;

            } else {

                if(strpos($this->adv->fields['trsptlivr'],'sls') !== false) {
                    $fournisseur_id = substr($this->adv->fields['trsptlivr'],3);
                } else if(strpos($this->adv->fields['trsptlivr'],'stock') !== false){
                    $fournisseur_id = substr($this->adv->fields['trsptlivr'],5);
                }
                $aFournisseur = $this->getFournisseur($fournisseur_id);

                $telDest = $aFournisseur['telephone'] != '' ? $aFournisseur['telephone'] : $aFournisseur['portable'];
                if(substr($telDest,0,1) == '+') $telDest = '0'.substr($telDest,3);
                $telDest = str_replace(array('-','.',' '),'',$telDest);

                $this->code_option          = '';
                $this->destinataire_type    = 'ENTERPRISE';
                $this->destinataire_societe = $aFournisseur['societe'];
                $this->destinataire_adresse = $aFournisseur['adresse1'];
                $this->destinataire_cp      = $aFournisseur['cp'];
                $this->destinataire_ville   = $aFournisseur['ville'];
                $this->destinataire_email   = $aFournisseur['mail'];
                $this->destinataire_tel     = $telDest;
                $this->destinataire_instr   = $this->adv->fields['infocomplement'];

            }
        }elseif($this->origin=="stock"){ //STOCK            
            
            $aFournisseur = $this->getFournisseur($this->stock_cmd->livraison);

            $telDest = $aFournisseur['telephone'] != '' ? $aFournisseur['telephone'] : $aFournisseur['portable'];
            if(substr($telDest,0,1) == '+') $telDest = '0'.substr($telDest,3);
            $telDest = str_replace(array('-','.',' '),'',$telDest);

            $this->code_option          = '';
            $this->destinataire_type    = 'ENTERPRISE';
            $this->destinataire_societe = $aFournisseur['societe'];
            $this->destinataire_adresse = $aFournisseur['adresse1'];
            $this->destinataire_cp      = $aFournisseur['cp'];
            $this->destinataire_ville   = $aFournisseur['ville'];
            $this->destinataire_email   = $aFournisseur['mail'];
            $this->destinataire_tel     = $telDest;
            $this->destinataire_instr   = $this->adv->fields['othertrsptinfo'];
        }

        //FORMATAGE TABLEAU COORDONNES DESTINATAIRE
        if($this->destinataire_societe != ''){
            $nom_formate = $this->destinataire_societe . '<br>' . $this->destinataire_nom.' '.$this->destinataire_prenom;
        } else {
            $nom_formate = $this->destinataire_nom.' '.$this->destinataire_prenom;
        }
        $this->destinataire = array(
            "type"             => $this->destinataire_type,
            "typeId"           => $this->destinataire_type_id,
            "name"             => $this->destinataire_societe, 
            "address1"         => $this->destinataire_adresse,
        	"address2"         => $this->destinataire_adresse2,
            "zipCode"          => $this->destinataire_cp, 
            "city"             => $this->destinataire_ville,
            "instructions"     => $this->destinataire_instr, //Pris en compte uniquement pour type "ENTERPRISE"
            "contactLastName"  => $this->destinataire_nom, 
            "contactFirstName" => $this->destinataire_prenom,             
            "emailAddress"     => $this->destinataire_email, 
            "phoneNumber"      => $this->destinataire_tel, 
            "accessCode"       => $this->destinataire_codeporte,
            "floorNumber"      => $this->destinataire_etage
        );	

    }

    
    private function setDateExpedition(){

        $date = datetime::createFromFormat("j/n/Y",$this->adv->fields['trsptenlfourndate']);
        $this->date_expedition_en = $date->format("Y-m-d");
    }

    private function setColis(){

        $i = 1;
        while(array_key_exists('colispoids_'.$i, $this->adv->fields)){

            $this->parcelsRequest[] = array(
                'sequenceNumber'    => $i,
                'customerReference' => $this->facture->facturid,
                'weight'            => $this->adv->fields['colispoids_'.$i], //OBLIGATOIRE : Récupérer le poids du colis
            );

            $i++;
        }
        
        if(count($this->parcelsRequest)==0) {
            $poids=0;
            foreach($this->adv->lignes as $lid=>$qte){
                switch($this->origin){
                    case "cmd":
                    $sql="SELECT a.* FROM lignefacture l, article a WHERE l.artid=a.artid AND lignefactureid=".$lid;
                    break;
                    
                    case "stock":
                    $sql="SELECT a.* FROM article a WHERE a.artid=".$lid;
                    break;
                }
                $lignefacture=$this->DB->query_first($sql);
                $poids+=$qte*$lignefacture['emballage_poids'];
            }
            $this->parcelsRequest[] = array(
                'sequenceNumber' => 1,
                'customerReference' => $this->adv->facturid,
                'weight' => $poids
            );
        }
    }

    private function faisabilite(){

        $aParameters = array(
            "shippingDate"  => $this->date_expedition_en,
            "accountNumber" => $this->compte,  
            "sender"        => array(
                "zipCode" => $this->expediteur_cp, 
                "city"    => $this->expediteur_ville
            ), 
            "receiver"      => array(
                "zipCode" => $this->destinataire_cp, 
                "city"    => $this->destinataire_ville, 
                "type"    => $this->destinataire_type
            )
        );
        
        try {

            $this->services = $this->soapclient->feasibility(array('parameters' => $aParameters));

            switch($this->adv->fields['trspttypedetails']){
                
                case 'express':
                    $this->code_produit = 'J';
                    break;

                case 'express9':
                    $this->code_produit = 'A';
                    break;

                case 'express10':
                    $this->code_produit = 'T';
                    break;

                case 'express12':
                    $this->code_produit = 'M';
                    break;

                default:
                    $this->code_produit = 'J';
                    break;
            }

            $code_demande = $this->code_produit.$this->code_option;

            //On récupère le code qui nous intéresse, en fonction de ce qui a été saisi dans la commande
            $code_valide = false; 
            if(is_object($this->services->Service)){

                $this->service = $this->services->Service;
                $code_valide   = true;

            } else {

                foreach($this->services->Service as $le_service){
                    
                    if($le_service->serviceCode == $code_demande){

                        $this->service = $le_service;
                        $code_valide   = true;
                        break;

                    }
                }
            }

            if(!$code_valide){

                $this->error = 'Erreur : le code '.$code_demande.' n\'apparait pas dans la liste des types de livraison possibles '.$this->soapclient->__soap_fault->faultstring;

            }

        } catch( SoapFault $e ) {
            
            $this->error = 'Erreur : '.$this->soapclient->__soap_fault->faultstring;

        }
    }

    private function setCout(){

        $aParameters = array(
            "shippingDate"      => $this->date_expedition_en, //YYYY-MM-DD
            "accountNumber"     => $this->compte, //Numéro sur 8 digits 
            "sender"            => array(
                "zipCode" => $this->expediteur_cp, 
                "city"    => $this->expediteur_ville
            ), 
            "receiver"          => array(
                "zipCode" => $this->destinataire_cp, 
                "city"    => $this->destinataire_ville, 
                "type"    => $this->destinataire_type
            ),
            "receiverType"      => $this->destinataire_type,
            "serviceCode"       => $this->service->serviceCode, //2 caractères max. Cf. valeurs retournées par le webservice de faisabilité
            "quantity"          => count($this->parcelsRequest), //Nombre compris entre 1 et 30
            "totalWeight"       => '', //Si non renseigné, ParcelsRequest est obligatoire
            "parcelsRequest"    => $this->parcelsRequest, //cf. tableau structure correspondant Si non renseigné, TotalWeight est obligatoire
            "saturdayDelivery"  => $jour_semaine == 6 ? 1 : 0, //1 : oui / 0 : non
            "pickUpRequest"     => '1' //1 : oui / 0 : non => Utiliser 1 pour le ramassage occasionnel
        );

        try {

            $this->cout = $this->soapclient_cout->expeditionCost(array('parameters' => $aParameters));

        } catch( SoapFault $e ) {
            
            $this->error = 'Erreur : '.$this->soapclient_cout->__soap_fault->faultstring;

        }

    }

    public function expedition(){
            
        $this->setPickUpRequest();

        //Il faut déterminer si la date de livraison est un samedi, pour l'indiquer à TNT
        $aDateLivraison = explode('/',$this->adv->fields['trsptotherdate']);
        $jour_semaine = date('N',mktime(0, 0, 0, $aDateLivraison[1], $aDateLivraison[0], $aDateLivraison[2]));

        if(!$this->error){

            $aParameters = array(
                "shippingDate"     => $this->date_expedition_en, //YYYY-MM-DD
                "accountNumber"    => $this->compte, //Numéro sur 8 digits
                "sender"           => $this->expediteur, //cf. tableau structure correspondant
                "receiver"         => $this->destinataire, //cf. tableau structure correspondant
                "serviceCode"      => $this->service->serviceCode, //2 caractères max. Cf. valeurs retournées par le webservice de faisabilité
                "quantity"         => count($this->parcelsRequest), //Nombre compris entre 1 et 30
                "parcelsRequest"   => $this->parcelsRequest, //Détail des colis de l’expédition
                "saturdayDelivery" => $jour_semaine == 6 ? 1 : 0, //Demande de livraison impérative le samedi (1 : oui / 0 : non)
                "labelFormat"      => $this->format_etiquette
            );

            if(!$this->test_mode) $aParameters['pickUpRequest'] = $this->pickUpRequest; //Demande de ramassage pour l’expédition

            try {

                $this->expedition = $this->soapclient->expeditionCreation(array('parameters' => $aParameters));

                $this->trackid = $this->expedition->Expedition->parcelResponses->parcelNumber;

            } catch( SoapFault $e ) {
            
                $this->error = 'Erreur : '.$this->soapclient->__soap_fault->faultstring;

            }
            
        }
    }

    public function getCout(){

        if(isset($this->cout->ShippingCost)){
            return $this->cout->ShippingCost->cost;
        } else {
            return 0;
        }

    }
    
    public function etiquette($pj = false, $parent=0){
        
        if($this->getError() !== false) return false;

        if($pj){

            $filename = 'Etiquette TNT '.date("Ymd-Hi");
            $data=$this->expedition->Expedition->PDFLabels;

            if($this->origin=="cmd"){
                //Enregistrement de l'étiquette dans les PJ de la commande
                $sql = "INSERT INTO user_files (";
                $sql .= "userid";
                $sql .= ", facturid";
                $sql .= ", parent";
                $sql .= ", date";
                $sql .= ", utilisateur";
                $sql .= ", name";
                $sql .= ", ext";
                $sql .= ", state";
                $sql .= ", size";
                $sql .= ") VALUES (";
                $sql .= $this->facture->utilisateur->userid;
                $sql .= ", ".$this->facture->facturid;
                $sql .= ", ".$parent;
                $sql .= ", ".time();
                $sql .= ", '".$this->adv->utilisateur."'";
                $sql .= ", '".$filename."'";
                $sql .= ", 'pdf'";
                $sql .= ", 1";
                $sql .= ", ".strlen($data);
                $sql .= ")";
                $this->DB->query($sql);

                $this->pjid = $this->DB->insert_id();

                $fileurl  = BACK_URL.'/userfiles/'.$this->pjid.'.pdf';
                $filepath = BACK_PATH.'/userfiles/'.$this->pjid.'.pdf';
                
            }
            
            if($this->origin=="stock"){
                //Enregistrement de l'étiquette dans les PJ de la commande
                $this->DB->query("INSERT INTO fournisseur_files SET fournisseur='".mysqli_real_escape_string($this->DB->link_id,$this->adv->destinataire)."', stock_cmd_id=".$this->stock_cmd->id.", date=".time().", utilisateur='".$this->adv->utilisateur."', name='".$filename."', size=".strlen($data).", ext='pdf', state=1");

                $this->pjid = $this->DB->insert_id();

                $fileurl  = BACK_URL.'/fournfiles/'.$this->pjid.'.pdf';
                $filepath = BACK_PATH.'/fournfiles/'.$this->pjid.'.pdf';
            }
            
            $fp = fopen($filepath, 'wb');
            fwrite($fp, $data);
            fclose($fp);

        }
    }

    public function getError(){

        return $this->error;

    }

    public function trackid(){

        return $this->trackid;

    }

    public function pjid(){

        return $this->pjid;

    }
    
    function getTracking($trackid){
        $this->setConnexion();
        
        return $this->soapclient->trackingByConsignment(array("parcelNumber"=>$trackid));
    }
}

