<?php

/**

* Exemple de classe pour se connecter à l'API de Cocktel

*/

class cocktel 
{
    
    private $host;
    private $username;
    private $pass;
    private $output;
    private $sessionstore=false;
    
    // Identifiant de session php (peut être stocké pour être réutilisé plus tard)
    private $sessionid;
    
    // Paramètres de Cocktel qui peuvent servir à l'application client
    private $params;
    
    // Infos utilisateur données par Cocktel
    private $nom;
    private $prenom;
    private $email;
    
    
    //--------------------------------------------------------------------------------------------    
    /**
    * Construction de la classe avec les infos nécessaires
    *
    * @param String $host Url du serveur Cocktel (identique à celle utilisée pour se connecter en web)
    * @param String $username Nom d'utilisateur pour s'identifier
    * @param String $password Mot de passe de l'utilisateur
    */
    public function __construct($host,$username,$password,$output="json",$sessionstore=false)
    {
		$this->host=preg_replace("/https?:\/\//","",$host);
		$this->username=$username;
		$this->pass=$password;
		$this->output=$output;
		if(is_dir($sessionstore) && is_writable($sessionstore)){
            $this->sessionstore=rtrim($sessionstore,"/");
            if(file_exists($this->sessionstore."/".$this->username))
                $this->sessionid=trim(file_get_contents($this->sessionstore."/".$this->username));
        }
    }
    
    //--------------------------------------------------------------------------------------------
    /**
    * Infos serveur cocktel
    *
    * @return String réponse en JSON
    */
    public function version()
    {
        $url = '/api/system/version';
        return $this->requete($url);
    }
   
    //--------------------------------------------------------------------------------------------    
    /**
    * Fonction de déconexion
    * Les autres fonctions de l'API utilisent le même modèle
    *
    * @return String Réponse en JSON
    */
    public function Deconnexion()
    {
        $url = '/api/unlog';
        return $this->requete($url);	
	}	
    
    //--------------------------------------------------------------------------------------------    
    /**
    * Fonction pour lister les contacts dans l'annuaire
    * Les autres fonctions de l'API utilisent le même modèle
    *
    * @param String typeannuaire Type d'annuaire (E=externe,I=interne,P=personnel) Tous par défaut
    *
    * @return String Réponse en JSON
    */
    function annuaire($typeannuaire=null)
    {
        $url="/api/phonebook/search/list?output=json";
        if (!is_null($typeannuaire))
        {
            if (is_array($typeannuaire)) $typeannuaire=explode(",",$typeannuaire);
                $url.="&typesannuaire=".$typeannuaire;
        }
            
        return $this->requete($url);
    }
    
    //--------------------------------------------------------------------------------------------
    /**
    * Fonction pour retrouver toutes les infos d'un contact
    *
    * @param String contactid Identifiant contact Cocktel
    *
    * @return contact
    */
    function contact($contactid)
    {
        $url="/api/phonebook/search/id/".intval($contactid);
        return $this->requete($url);
    }
    //--------------------------------------------------------------------------------------------
    /**
    * Fonction pour retrouver toutes les infos d'un contact
    *
    * @param String contactid Identifiant contact MCAV
    *
    * @return contact
    */
    function contact_site($contactid)
    {
        $url="/api/phonebook/search/extid/".intval($contactid);
        return $this->requete($url);
    }

    //--------------------------------------------------------------------------------------------    
    /**
    * Fonction pour ajouter une liste de contacts
    *
    * @return String Réponse en JSON
    */
    function AjoutListeContacts($xml)
    {
        $url = "/api/phonebook/update/list";
        return $this->requete($url, $xml);
    }
    
    //--------------------------------------------------------------------------------------------
    /**
    * Fonction pour ajouter plusieurs contacts en csv
    *
    * @return String Réponse
    */
    function AjoutContactsCSV($csv)
    {
        // "id","champ1","champ2","champ8"
        //  "","nom","prénom","0400000000"

        $url = "/api/phonebook/update/csv";
        return $this->requete($url,$csv);
    }
    
    //--------------------------------------------------------------------------------------------    
    /**
    * Fonction pour ajouter ou modifier un contact
    *
    * @return String Réponse
    */
    function AjoutUnContact($nbFields, $stringField)
    {
        $url = "/api/phonebook/update/contact";
        return $this->requete($url, null, $nbFields, $stringField);
    }

    //--------------------------------------------------------------------------------------------    
    /**
    * Fonction pour supprimer des contacts
    *
    * @return String Réponse
    */
    function SuppressionContact($param)
    {
        //  $url = '/api/phonebook/delete/contacts?contacts=' . $param;
    
        // Paramètre URL indiqué dans la doc => on passe directement les $param dans l'url (sans ?)
        $url = '/api/phonebook/delete/' . $param;
        return $this->requete($url);
    }
	
	//--------------------------------------------------------------------------------------------    
    /**
    * Fonction pour lister les utilisateurs
    *
    * @return String Réponse
    */
    function ListeUtilisateurs()
    {
        $url = '/api/users/list';
        return $this->requete($url);
    }
	
	//--------------------------------------------------------------------------------------------    
    /**
    * Fonction pour lister les appels
    *
    * @return String Réponse
    */
    function ListeAppels($params)
    {
        $url = '/api/calls/search';
        $fields_string="";
        foreach($params as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        $fields_string=rtrim($fields_string, '&');
        return $this->requete($url,null,count($params),$fields_string);
	}
	
	//--------------------------------------------------------------------------------------------    
    /**
    * Fonction de composition d'un numéro de téléphone
    *
    * @return String Réponse
    */
    function ComposerNumeroTelephone($numero,$poste=null)
    {
        if(!is_null($poste))
            $url = '/api/phone/call/' . $poste . "/" . $numero;
        else
            $url = '/api/phone/call/' . $numero;
        return $this->requete($url);
    }
	
	//--------------------------------------------------------------------------------------------    
    /**
    * Fonction d'appel entrant
    *
    * @return String Réponse
    */
    function AppelsEnCours($instantane)
    {
        if($instantane) // option pour avoir la réponse sans attendre un événement (au chargement)
            $url = '/api/system/extensions/status';	
        else
            $url = '/api/system/extensions/pushstatus';

    //   $url = '/api/?page=calls';
        return $this->requete($url);
    }
	
    //--------------------------------------------------------------------------------------------    
    /**
    * Identification sur Cocktel
    *
    */
    function identification()
    {
        unset($this->sessionid);
        
        $fields=array("username"=>$this->username,"password"=>$this->pass);
        $fields_string="";
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        $fields_string=rtrim($fields_string, '&');
        
        $ch=curl_init($this->host."/api/auth");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT,30);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	
        
        // Exécution de la requète
        $ret=curl_exec($ch);
        if($ret==false)
            return false;

        $doc= new DOMDocument();
        $doc->loadXML($ret);

        $log= $doc->getElementsByTagName("log");
        if ($log->item(0)->nodeValue==1)
        {
            // Identifiant de session, présent dans le xml en plus du cookie PHPSESSID
            $this->sessionid=$log->item(0)->getAttribute("session");
            if($this->sessionstore!=false)
                file_put_contents($this->sessionstore."/".$this->username,$this->sessionid);
            
            // Infos utilisateur
            $user=$doc->getElementsByTagName("user");
            $this->nom=$user->item(0)->getAttribute("name");
            $this->prenom=$user->item(0)->getAttribute("surname");
            $this->email=$user->item(0)->getAttribute("email");
            
            // Infos de configuration utiles pour l'application
            $params=$doc->getElementsByTagName("param");
            foreach($params as $param)
            {
                $name=$param->getAttribute("name");
                $this->params[$name]=$param->nodeValue;
            }
            return true;
        }
        else
        {
            // Erreur
            return false;
        }
    }
    
	//--------------------------------------------------------------------------------------------
    /**
    * Requete à l'API Cocktel
    *
    * @param String url Url de l'API
    *
    * @return String réponse de l'API (XML ou JSON) ou false en cas d'erreur
    */
    private function requete($url, $xml = null, $nbPostFields = null, $stringPostField = null)
	{
        if (substr($url,-1,1)!="/") 
		   $url="/".$url;
        if(!preg_match("/\?/",$url)) $url.="?";
        $url.="&output=".$this->output;
	
        // On s'identifie si on n'a pas de session
        // En cas d'erreur on retourne false
    	if (is_null($this->sessionid))
        {
            if (!$this->identification()) 
                return false;
        }
	
		// Définitions de infos pour cURL pour faire la requète
		$ch=curl_init($this->host.$url);
		curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID='.$this->sessionid); // Cookie de session php qui permet de nous identifier
	
	    if ($xml != null)
        {	
            $headers = array(                                        
                "Content-type: application/xml;charset=\"utf-8\"",
                "Content-length: " . ($xml != null ) ? strlen($xml) : strlen($stringPostField)
                );   
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
		else
        {	         
            curl_setopt($ch, CURLOPT_HEADER,0);
        }
		   
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT,30);
	
	
	    // Transmission du flux XML  
	    if ($xml != null)
        {            
            curl_setopt($ch ,CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        }	
		else 
            if ($nbPostFields != null)	
            {
                curl_setopt($ch ,CURLOPT_POST, $nbPostFields);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $stringPostField);
            }   

		// Exécution de la requète
		$ret = curl_exec($ch);
		
		// On récupère les infos de la requète
		$info = curl_getinfo($ch);

		// L'utilisateur n'est pas identifié
		if ($info['http_code'] != 200) 
        {
            // On demande à s'identifier
            if (!$this->identification()) 
                return false; // On retourne false en cas d'erreur
            
            // sinon on refait la requete
			/* MISE EN COMMENTAIRE DE BENOIT SUITE A MAIL DU 7 NOVEMBRE 2023 */
            // return $this->requete($url,$xml,$nbPostFields,$stringPostField);
			
        } 
	    else 
        {
            // On retourne les données reçues
            return $ret;
        }
	}
 
    /**
    * Pour récupérer les paramètres de l'utilisateur
    *
    * @name String nom du paramètre
    *
    * @return String valeur du paramètre
    **/
    function param($name){
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }

    function encodeNum($num){
        return str_replace(array(" ",".","%20"), "", $num);
    }
 
} // Fin de la classe

?>
