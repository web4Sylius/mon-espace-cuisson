<?php

/*Fichier qui gére le host */

class Host{
	public $path;
	public $host;
	public $http_host;
	public $langue;
	public $langues=array();
	public $devise;
	public $money_sep;
	public $money_format;
	public $pays_ref;
	public $site_actif;
	public $site_code;
	public $ndds = array ("www.ma-cave-a-vin.fr" 		 		=> "www.ma-cave-a-vin.fr",
						  "ma-cave-a-vin.fr" 		 		    => "www.ma-cave-a-vin.fr",
						  "dedie.ma-cave-a-vin.fr"				=> "www.ma-cave-a-vin.fr",
						  "dediepro.ma-cave-a-vin.fr"			=> "www.ma-cave-a-vin.fr",
						  "pro.ma-cave-a-vin.fr"				=> "www.ma-cave-a-vin.fr",
						  "www.my-wine-cabinet.com"		 		=> "www.ma-cave-a-vin.fr",
						  "my-wine-cabinet.com"					=> "www.ma-cave-a-vin.fr",
						  "pro.my-wine-cabinet.com"				=> "www.ma-cave-a-vin.fr",
						  "dedie.my-wine-cabinet.com"			=> "www.ma-cave-a-vin.fr",
						  "dediepro.my-wine-cabinet.com"		=> "www.ma-cave-a-vin.fr",
			              "localhost.ma-cave-a-vin.fr" 	  		=> "www.ma-cave-a-vin.fr",
			              "localhost.pro.ma-cave-a-vin.fr"  	=> "www.ma-cave-a-vin.fr",
			              "localhost.my-wine-cabinet.com"   	=> "www.ma-cave-a-vin.fr",
			              "localhost.pro.my-wine-cabinet.com"	=> "www.ma-cave-a-vin.fr",
			              "test.ma-cave-a-vin.fr"			 	=> "www.ma-cave-a-vin.fr",
			              "testpro.ma-cave-a-vin.fr"		 	=> "www.ma-cave-a-vin.fr",
			              "test.my-wine-cabinet.com"			=> "www.ma-cave-a-vin.fr",
			              "testpro.my-wine-cabinet.com"			=> "www.ma-cave-a-vin.fr",
			              "www.mon-espace-cuisson.com"		 	=> "www.mon-espace-cuisson.com",
			              "localhost.mon-espace-cuisson.com"	=> "www.mon-espace-cuisson.com",
			              "fusion.ma-cave-a-vin.fr"				=> "www.mon-espace-cuisson.com",
			              "dedie.mon-espace-cuisson.com"		=> "www.mon-espace-cuisson.com",
			              "http://127.0.0.1/mec/"	=> "www.mon-espace-cuisson.com"
			          );
	// public $langues_site = array("www.ma-cave-a-vin.fr" => array("FR","EN") ,"www.mon-espace-cuisson.com" => array("FR"));

	function __construct(){
		
		// $this->http_host = $_SERVER['HTTP_HOST'];
		// $this->host = $this->ndds[$this->http_host];
		$this->path = 'www.mon-espace-cuisson.com';
		//foreach($this->langues_site[$this->host] as $value){
			$this->langues[] = 'FR';
		//}
	}

	function initialise($DB){
		// $result = $DB->query_first("SELECT * FROM sites WHERE http_host = '".mysqli_real_escape_string($DB->link_id, $this->http_host)."'");
		// if($result[0]!=""){
			$this->langue 		= 'FR';
			$this->devise 		= 'EUR';
			$this->money_format = '%f&nbsp;%s';
			$this->money_sep 	= ',';
			$this->pays_ref 	= 57;
			$this->actif 	    = 1;
			$this->site_code 	= 'base';
		// }else{
		// 	return false;
		// }
	}

}


/*
function Host(){
	
	$hosttemp=$_SERVER[HTTP_HOST];

	$host = $ndds[$hosttemp];

	return $host;
}*/