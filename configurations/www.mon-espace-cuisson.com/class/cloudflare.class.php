<?php

/* Documentation sur l'API de cloudflare :
    https://api.cloudflare.com/#zone-purge-individual-files-by-url-and-cache-tags
	https://api.cloudflare.com/#zone-list-zones
	zone Id : f7cad87460fa491891e49b63b91cef01
	clé API : 2ed4a283e3f5d8747014ff3ec70980ea2c7a1
	
*/

$cloudflare_email = "webmaster1@ma-cave-a-vin.fr";
$cloudflare_api = "2ed4a283e3f5d8747014ff3ec70980ea2c7a1";
//$cloudflare_api = "c3b1f52e2cbdfeb07316d981aac192f7";
$cloudflare_zone_id="67f25f351886fa8cd4d03f283e9f28c3";

/**
 * Made by RashFlash
 * Dec 10th 2015
 */
 
 
 
 /*
purger du cache les images des articles : 
 => à mettre dans /admin/ajout_produit.php à la ligne 5853
 */
 
 
class cloudflare_api {
	private $DB;
	private $rootpath;
	public $cloudflare_email = "webmaster1@ma-cave-a-vin.fr";
	public $cloudflare_api = "2ed4a283e3f5d8747014ff3ec70980ea2c7a1";
	public $cloudflare_zone_id = "67f25f351886fa8cd4d03f283e9f28c3";

    //Timeout for the API requests in seconds
    const TIMEOUT = 15;
    //Interval values for Stats
    const INTERVAL_365_DAYS = 10;
    const INTERVAL_30_DAYS = 20;
    const INTERVAL_7_DAYS = 30;
    const INTERVAL_DAY = 40;
    const INTERVAL_24_HOURS = 100;
    const INTERVAL_12_HOURS = 110;
    const INTERVAL_6_HOURS = 120;

    function __construct($DB=null,$rootpath=null){
    	$this->DB = $DB;
    	$this->rootpath = $rootpath;
    }
	
    public function getUserDetail($email, $api) {
        $result=$this->curlGetRequest("https://api.cloudflare.com/client/v4/user", $email, $api);        
        return $result;
    }
    
    public function purgeFiles($email, $api, $zone_id, $files) {
        $json=  json_encode(array(
            'files'=>$files
        ));
        $result=$this->curlDeleteRequest("https://api.cloudflare.com/client/v4/zones/".$zone_id."/purge_cache", $email, $api, $json);
        return $result;
    }
	
	public function purgeArticle($artid) {
		// return "/".url_rewrite($data['libelle'.$this->langue]."-".$data['marque'])."-art".$id.".htm";
		$article=$this->DB->query_first("SELECT libelleFR, libelleEN, catid FROM article WHERE artid=".$artid);
		$marque=$this->DB->query_first("SELECT marque.libelle, marque.urlFR, marque.urlEN, marqueid FROM marque INNER JOIN article_marque USING(marqueid) WHERE article_marque.artid=".$artid);
		$url1=array();
		// les pages d'accueil
		$url1[]="https://www.mon-espace-cuisson.com/";
		// les pages article
		$url1[]="https://www.mon-espace-cuisson.com/".url_rewrite($article['libelleFR']."-".$marque['libelle'])."-art".$artid.".htm";
		// les pages catégories
		$parentid=$article[catid];
		while($parentid!=0){
			$categorie=$this->DB->query_first("SELECT libelleFR, urlFR, urlEN, parentid FROM categorie WHERE catid=".$parentid);
			$url1[]="https://www.mon-espace-cuisson.com/".$categorie['urlFR'];
			if(substr($categorie['urlFR'], -4)==".htm"){
				$i=2;
				$nbarticle=$this->DB->query_first("SELECT COUNT(*) FROM article WHERE catid=".$parentid);
				$nbpage=ceil($nbarticle[0]/20);
				while($i<$nbpage && $i<=10){
					$url1[]="https://www.mon-espace-cuisson.com/".substr($categorie['urlFR'], 0, -4) ."/".$i.".htm";
					$i++;
				}
			} else {
				echo "l'URL FR indiqu&eacute;e en base de donn&eacute;es de la catégorie ".$categorie[libelleFR]." n'est pas bonne ... Ca serai bien de les corriger.<br>";
			}
			
			$parentid=$categorie[parentid];
		}
		// les pages marque
		$url1[]="https://www.mon-espace-cuisson.com/".url_rewrite($marque['libelle'])."-".$marque['marqueid'].".htm";
		// les photos principales et les photos secondaires en admin
		$url1[]="https://www.mon-espace-cuisson.com/configurations/www.mon-espace-cuisson.com/images/produits/".$artid.".jpg";
		$url1[]="https://www.mon-espace-cuisson.com/configurations/www.mon-espace-cuisson.com/images/produits/article/".$artid.".jpg";
		$url1[]="https://www.mon-espace-cuisson.com/configurations/www.mon-espace-cuisson.com/images/produits/categorie/".$artid.".jpg";
		$url1[]="https://www.mon-espace-cuisson.com/configurations/www.mon-espace-cuisson.com/images/produits/cdc_categ/".$artid.".jpg";
		$url1[]="https://www.mon-espace-cuisson.com/configurations/www.mon-espace-cuisson.com/images/produits/cdc_index/".$artid.".jpg";
		$url1[]="https://www.mon-espace-cuisson.com/configurations/www.mon-espace-cuisson.com/images/produits/nouveautes_categ/".$artid.".jpg";
		$url1[]="https://www.mon-espace-cuisson.com/configurations/www.mon-espace-cuisson.com/images/produits/nouveautes_index/".$artid.".jpg";
		$url1[]="https://www.mon-espace-cuisson.com/configurations/www.mon-espace-cuisson.com/images/produits/promos_categ/".$artid.".jpg";
		$url1[]="https://www.mon-espace-cuisson.com/configurations/www.mon-espace-cuisson.com/images/produits/promos_index/".$artid.".jpg";
		$url1[]="https://www.mon-espace-cuisson.com/configurations/www.mon-espace-cuisson.com/images/produits/topventes_categ/".$artid.".jpg";
		$url1[]="https://www.mon-espace-cuisson.com/configurations/www.mon-espace-cuisson.com/images/produits/topventes_index/".$artid.".jpg";
		$url1[]="https://www.mon-espace-cuisson.com/configurations/www.mon-espace-cuisson.com/images/produits/br/".$artid.".jpg";
		$url1[]="https://www.mon-espace-cuisson.com/configurations/www.mon-espace-cuisson.com/images/produits/photos/".$artid.".jpg";
		// les photos principales et les photos secondaires sur site
		$dir=$this->rootpath."images/cache/";
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if(substr($file,0,strlen($artid)+1)==$artid."_"){
					// echo "il existe : ".$file."<br>";
					$url1[]="https://www.mon-espace-cuisson.com/images/".$file;
				}
			}
			closedir($dh);
		}
		// comptage des URL
		$count1=count($url1);
		// $count2=count($url2);
		$count=$count1+$count2;
		// echo "il y a ".$count." URL à envoyer.<br>";
		// var_dump($url);
		
		$return=array("success"=>true, "url1" => $url1);
		$urlencours=array();
		$urlencours=array_chunk($url1, 30, true);
		while (list($key, $val) = each($urlencours)) {
			$results = $this->purgeFiles($this->cloudflare_email, $this->cloudflare_api, $this->cloudflare_zone_id, $val);
			if($results->success === false) {
				$return[success]=false;
			}
		}
		$urlencours=array();
		/*
		$urlencours=array_chunk($url2, 30, true);
		while (list($key, $val) = each($urlencours)) {
			$results = $this->purgeFiles($this->cloudflare_email, $this->cloudflare_api, $this->cloudflare_zone_id, $val);
			if($results->success === false) {
				$return[success]=false;
			}
		}
		*/
		return $return;
    }
    
    public function purgeAll($email, $api, $zone_id) {
        $json=  json_encode(array(
            'purge_everything'=>true
        ));
        $result=$this->curlDeleteRequest("https://api.cloudflare.com/client/v4/zones/".$zone_id."/purge_cache", $email, $api, $json);
        return $result;
    }
    
    public function getZonesList($email, $api, $domain) {
        $result=$this->curlGetRequest("https://api.cloudflare.com/client/v4/zones?name=$domain",$email, $api);
        return $result;
    }
       
    public function curlGetRequest($url, $email, $api_key) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $headers = array(
            'X-Auth-Email: ' . $email,
            'X-Auth-Key: ' . $api_key,
            'Content-Type: application/json',
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result);
    }
    
    public function curlDeleteRequest($url, $email, $api_key, $json) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $headers = array(
            'X-Auth-Email: ' . $email,
            'X-Auth-Key: ' . $api_key,
            'Content-Type: application/json'
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result);
    }
}

?>
