<?php

class platinehome {

    private $DB;
    private $idVendeur;
    public  $lignefactureid;
    public  $civilite;
    public  $langue;
    public  $naissance; //date de naissance
    public  $date; //date achat
    public  $adresse;
    public  $ville
    public 	$nom;
    public 	$prenom;
    public  $paysid;
    public 	$codePays;
    public 	$codepostal;
    public 	$telephone;
    public 	$email;
    public  $iban;
    public  $artcode;
    public  $codeProduit;
    public  $prix; //prix vente (prix payé par le client)
    public  $qte;
    public  $platineOffre;
    public  $factureid;
    public  $xml;
    public  $marque;
    public  $dateXML;

    function __construct($DB,$lignefactureid){
        $this->DB = $DB;
    	$this->idVendeur = "idVendeur";
        $this->lignefactureid = $lignefactureid;
        $this->load($this->lignefactureid);

    }

   public function load($lignefactureid){
        $ligne = $this->DB->query_first("SELECT factureid, artcode, prix, qte, artid FROM lignefacture WHERE lignefactureid='".$lignefactureid."'");
        $facture = $this->DB->query_first("SELECT userid, datefacture FROM facture WHERE facturid ='".$ligne['factureid']."'");
        $user = $this->DB->query_first("SELECT mail, civilite, nom, prenom, codepostal, adresse, ville, paysid, telephone, langue FROM utilisateur WHERE userid='".$facture['userid']."'");
        $this->factureid = $ligne['factureid'];
        $this->artcode = $ligne['artcode'];
        $this->prix = $ligne['prix'];
        $this->qte = $ligne['qte'];
        $this->date = $this->dateFormat($facture['datefacture']);
        $this->email = $user['mail'];
        $this->civilite = $user['civilite'];
        $this->nom = $user['nom'];
        $this->prenom = $user['prenom'];
        $this->codepostal = $user['codepostal'];
        $this->adresse = $user['adresse'];
        $this->ville = $user['ville'];
        $this->paysid = $user['paysid'];
        $this->telephone = $user['telephone'];
        $this->langue = $user['langue'];
        $this->marque = $this->getMarque();
        $this->codeProduit = $this->codeAppareil($ligne['artid']);
        $this->codePays = $this->codePays();
        $this->xml = $this->createXML();

    }

   public function codeAppareil($artid){ //retourne le code article SFG correspondant a nos type article

        $codesArticle = array(
                            1 => 'cvin'
                        ) 

        $typeArticle = $this->DB->query_first("SELECT typearticleid FROM article WHERE artid ='".$artid."'");
        if(in_array($typeArticle[0],$codesArticle)){
            return $codesArticle[$typeArticle[0]];
        }


    }

    public function codePays(){
        $pays = $this->DB->query_first("SELECT diminutif FROM pays WHERE paysid ='".$this->paysid."'");
        return $pays[0];
    }

    public function getMarque(){
        $marque = $this->DB->query_first("SELECT m.libelle FROM marque AS m JOIN article_marque AS am USING(marqueid) WHERE am.artid='".$this->artid."'");
        return $marque[0];
    }

    public function dateFormat($date){
        $newDate = explode("-", $date);
        return $newDate[2].".".$newDate[1].".".$newDate[0];
    }

    public function createXML(){
        $this->dateXML = date("d.m.Y");
        $xml = ""
    ."<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>"
        ."<ewp:applications xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:ewp=\"http://www.wertgarantie.de\" xsi:schemaLocation=\"http://www.wertgarantie.de/XMLSchema/ewp_application.xsd\">"
            ."<application>"
                "<agent_number>".$this->idVendeur."</agent_number>"
               ."<record_number>".$this->factureid."</record_number>"
               ."<client_data>"
                    ."<address>"
                        ."<salutation>".$this->civilite."</salutation>"
                        ."<first_name>".$this->prenom."</first_name>"
                        ."<last_name>".$this->nom."</last_name>"
                        ."<house_number></house_number>"
                        ."<street><![CDATA[ ".$this->adresse." ]]></street>"
                        ."<country_code>".$this->codePays."</country_code>"
                        ."<postal_code>".$this->codepostal."</postal_code>"
                        ."<city>".$this->ville."</city>"
                    ."</address>"
                    ."<birthdate>".$this->naissance."</birthdate>"
                    ."<phone1>".$this->telephone."</phone1>"
                    ."<mobile></mobile>"
                    ."<email>".$this->email."</email>"
                    ."<language_mark>".$this->langue."</language_mark>"
                ."</client_data>"
                ."<sepa_information>"
                    ."<account_holder>".$this->nom."</account_holder>"
                    ."<IBAN>".$this->iban."</IBAN>"
                ."</sepa_information>"
                ."<object_data>"
                    ."<manufacturer>".$this->marque."</manufacturer>"
                    ."<model>".$this->artcode"</model>"
                    ."<object_code>".$this->codeProduit."</object_code>"
                    ."<number></number>"
                    ."<purchase_date>".$this->date."</purchase_date>"
                    ."<purchase_price>".$this->prix."</purchase_price>"
                ."</object_data>"
                ."<policy_type>EWP_SFG</policy_type>"
                ."<product_type>".$this->platineOffre."</product_type>" //ONE_ELECTRO_1 ou ONE_ELECTRO_2 ou ONE_ELECTRO_3
                ."<application_code>EWP FR ATM 0416_2</application_code>"
                ."<payment_schedule>1</payment_schedule>"
                ."<payment_method>EINZUG</payment_method>"
                ."<application_date>Date de souscription du contrat format JJ.MM.AAAA</application_date>"
                ."<export_date>".$this->dateXML."</export_date>"
            ."</application>"
        ."</ewp:applications>";

    }


}

?>