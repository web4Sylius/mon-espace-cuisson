<?php
class rueducommerce
{
	private $DB;
	private $url   = 'https://marchands.rueducommerce.fr/merchant/syndication/orders/ma%20cave/';
	private $debut; 
	private $fin; 
	private $flux;

	private $aRequetes = array
	(
		'encours' => 'processing_orders_248a0a1bd20c59d74ef20eef3e365d578558d8fa.xml',
		'annule'  => 'cancelled_orders_248a0a1bd20c59d74ef20eef3e365d578558d8fa.xml',
		'rest'    => 'restocking_orders_248a0a1bd20c59d74ef20eef3e365d578558d8fa.xml',
		'ship'    => 'shipped_orders_248a0a1bd20c59d74ef20eef3e365d578558d8fa.xml',
		'new'     => 'new_orders_248a0a1bd20c59d74ef20eef3e365d578558d8fa.xml'
	);

	public $orders = array();

	public function __construct($DB, $debut = null, $fin = null)
	{
		$this->DB = $DB;
		$this->setDebut($debut);
		$this->setFin($fin);
		$this->flux = ""
		. "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n"
			. "<mmie version=\"2.0\">\r\n"
				. "<orders>\r\n"
					. "<sent morid=\"MOR-01M1234\" modid=\"MOD-12141M987\" datetime=\"0000-00-00T00:00:00Z\">\r\n"
						. "<tracking></tracking>\r\n"
					. "</sent>\r\n"
					. "<sent morid=\"MOR-01M1234\" modid=\"MOD-53544M988\" datetime=\"0000-00-00T00:00:00Z\">\r\n"
						. "<tracking></tracking>\r\n"
					. "</sent>\r\n"
				. "</orders>\r\n"
			. "</mmie>";
	}

	public function load($url)
	{
		$aFlux = array('requests_xml' => $this->flux);

	    $oCurlNew = curl_init();
	    curl_setopt($oCurlNew, CURLOPT_HEADER, false);
	    curl_setopt($oCurlNew, CURLOPT_POST, TRUE);
	    curl_setopt($oCurlNew, CURLOPT_POSTFIELDS, $aFlux);
	    curl_setopt($oCurlNew, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($oCurlNew, CURLOPT_URL, $url );
	    curl_setopt($oCurlNew, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($oCurlNew, CURLOPT_SSL_VERIFYPEER, 0); 
	    $iResultNew =  curl_exec($oCurlNew);
	    $httpCode = curl_getinfo($oCurlNew, CURLINFO_HTTP_CODE);
	    curl_close($oCurlNew);

	    if($httpCode == 200){ 
	        $xmlNew = new SimpleXMLElement($iResultNew);
	        return $xmlNew->order;
	    }

	    return false;
	}

	//Setters
	private function setDebut($debut = null)
	{
		try
		{
			if(!is_null($debut))
			{	
				if(!preg_match("#^([0-9]{4})-([0-9]{2})-([0-9]{2})$#", $debut)) throw new Exception("La date de début n'est pas au format YYYY-MM-DD");
				$this->debut = $debut;
			}
			else 
			{
				$this->debut = date("Y-m-d",strtotime("-1 month"));
			}
		}
		catch(Exception $e)
		{
			$this->getError($e);
		}
	}

	private function setFin($fin = null)
	{
		try
		{
			if(!is_null($fin))
			{	
				if(!preg_match("#^([0-9]{4})-([0-9]{2})-([0-9]{2})$#", $fin)) throw new Exception("La date de fin n'est pas au format YYYY-MM-DD");
				$this->fin = $fin;
			}
			else 
			{
				$this->fin = date("Y-m-d");
			}
		}
		catch(Exception $e)
		{
			$this->getError($e);
		}
	}

	//Getters
	public function getOrders()
	{		
		try
		{
			foreach($this->aRequetes as $type => $la_requete)
			{
				//ici on tri seulement par date seulement celles qui ont étés annulées ou envoyés. trier celle en cours rajouter || $type == 'encours' dans le if
				if($type == 'annule' || $type == 'ship' ) $la_requete .= "?start=" . $this->debut . "&end=" . $this->fin;

				$res = $this->load($this->url . $la_requete);

				if($res === false)
				{
					throw new Exception("L'API RueDuCommerce est indisponible pour le moment");
				}

				$this->orders[$type] = $res;
			}
		}
		catch(Exception $e)
		{
			$this->getError($e);
		}
	}

	public function getError($error)
	{
		exit("<p style=\"color: red; font-weight: bold;\">Erreur : " . $error->getMessage() . "</p>");
	}
}