<?php


class frais_port {
  private $DB;
  
  private $limit_emballage=array("hauteur"=>1800,
				"largeur"=>2400,
				"profondeur"=>1200);
		
  private $zone_poidsvolume=array(1=>250,2=>250,3=>250,4=>250,5=>250,6=>200,7=>200,8=>200,9=>200,10=>200);
  private $supplement_taille_value=17;
  private $taxe_surete=array(0=>0.75,3=>1.5,10=>2.25,30=>5.5,125=>8.5);
  private $taxe_carburant=20.22;
  private $remise_carburant=40;
  private $assurance_value=0.6;
  private $assurance_mini=5;
  private $suisse_supplement=10;
  private $marge=15;
  
  private $artids;
  private $paysid;
  private $site_code;
  private $totalttc;
  private $totalpaht;
  
  private $supplement_taille_apply=false;
  private $volume;
  private $poids;
  private $poidsvolume;
  private $zone;
  private $dimensions;
  
  public $paht_grille;
  public $taxe_surete_paht;
  public $taxe_carburant_paht;
  public $assurance_paht;
  public $supplement_pays_paht;
  public $supplement_taille_paht;
  public $paht;
  public $prixht;
  public $prixbarreht;
  public $livre;
  public $pro;
  public $userid;

  function __construct($DB,$artids,$paysid=null,$totalttc=0,$totalpaht=0,$site_code="base",$pro=false,$userid=null) {
      $this->DB=$DB;
      $this->pro = $pro;
      $this->userid = $userid;

      if(is_array($artids)) {
      	$this->artids=$artids;
      	$this->paysid=intval($paysid);
      	$this->site_code=$site_code;
      	$this->totalttc=$totalttc;
      	$this->totalpaht=$totalpaht;
      }else{
	      $this->loadfacture($artids);
      }
      $this->volume();
      $this->zone=$this->zone();
      $zone_livraison=$this->zone;
      
      if($this->zone===false){
	     $this->livre=false;
	     return false;
      }
      if($this->zone===0) {
	     $this->national($this->poids*1000);
	     $this->livre=true;
	     if($this->paht>0) return true;
	     $this->zone=$this->zone("base");
      }
      $this->poidsvolume=floatval($this->volume*$this->zone_poidsvolume[$this->zone]);
      if($this->poids>$this->poidsvolume) $this->poidsvolume=$this->poids;
      
      $this->paht_grille=$this->paht_grille();
      if($this->supplement_taille_apply) $this->supplement_taille_paht=$this->supplement_taille_value;
      $this->taxe_surete_paht=$this->taxe_surete();
      $this->taxe_carburant_paht=$this->taxe_carburant($this->paht_grille);
      $this->assurance_paht=$this->assurance();
      if($this->paysid==158) $this->supplement_pays_paht=$this->suisse_supplement;

      $this->paht=$this->paht_grille + floatval($this->supplement_taille_paht) + floatval($this->taxe_surete_paht) + floatval($this->taxe_carburant_paht) + floatval($this->assurance_paht) + floatval($this->supplement_pays_paht);
      if($zone_livraison>0) $this->prixvente();
      $this->livre=true;

  }

  private function loadfacture($facturid) {
      $facture=$this->DB->query_first("SELECT facturid,prixport,tvaport,paysid,site_code, userid,raisonsociale FROM facture WHERE facturid=".intval($facturid));
      if($facture['facturid']!=$facturid) return false;
      $this->userid=$facture['userid'];
      $this->paysid=$facture['paysid'];
      $this->site_code=(empty($facture['site_code']) ? "base" : $facture['site_code']);
      $client = $this->DB->query_first("SELECT raisonsociale FROM utilisateur WHERE userid='".$this->userid."'");
      if($client['raisonsociale'] != '') $this->pro = true;
      $this->totalpaht=0;
      $this->totalttc=0;
      $this->artids=array();
      $lignes=$this->DB->query("SELECT artid,paht,prix,qte FROM lignefacture WHERE factureid=".$facture['facturid']);
      while($ligne=$this->DB->fetch_array($lignes)) {
    	  $this->totalttc+=$ligne['prix']*$ligne['qte'];
    	  $this->totalpaht+=$ligne['paht']*$ligne['qte'];
    	  $this->artids[$ligne['artid']]+=$ligne['qte'];
      }
      
      return true;
  }
  
  private function volume() {
      $this->volume=$this->poids=0;
      foreach($this->artids as $artid=>$qte) {
	  $reqlot=$this->DB->query("SELECT qte,artid FROM article_lot WHERE lotid=".$artid);
	  if($this->DB->num_rows($reqlot)>0) {
	      while($artlot=$this->DB->fetch_array($reqlot))
		  $this->article_data($artlot['artid'],$artlot['qte']*$qte);
	  }else
	      $this->article_data($artid,$qte);
      }

      return $this->volume;
  }
  
  private function article_data($artid,$qte) {
      $art=$this->DB->query_first("SELECT emballage_poids1,emballage_largeur1,emballage_hauteur1,emballage_profondeur1,emballage_poids2,emballage_largeur2,emballage_hauteur2,emballage_profondeur2,emballage_poids3,emballage_largeur3,emballage_hauteur3,emballage_profondeur3 FROM article WHERE artid=".$artid);

      $this->dimensions[$artid]['hauteur'] = $art['emballage_hauteur1'] + $art['emballage_hauteur2'] + $art['emballage_hauteur3'];
      $hauteur_temp = $art['emballage_hauteur1'] + $art['emballage_hauteur2'] + $art['emballage_hauteur3'];
      $largeur_temp = $art['emballage_largeur1'] + $art['emballage_largeur2'] + $art['emballage_largeur2'];
      $profondeur_temp = $art['emballage_profondeur1'] + $art['emballage_profondeur2'] + $art['emballage_profondeur3'];
      $poids_temp = $art['emballage_poids1'] + $art['emballage_poids2'] + $art['emballage_poids3'];

      if($largeur_temp > $profondeur_temp){
               $this->dimensions[$artid]['largeur'] = $largeur_temp;
               $this->dimensions[$artid]['profondeur'] = $profondeur_temp;
          }else{
               $this->dimensions[$artid]['largeur'] = $profondeur_temp;
               $this->dimensions[$artid]['profondeur'] = $largeur_temp;
          }
      foreach($this->limit_emballage as $field=>$limit){
           if(!$this->supplement_taille_apply && $this->dimensions[$artid][$field]>$limit){
                $this->supplement_taille_apply=true;
           }
      }
		  
      $this->volume += $qte * $hauteur_temp * $largeur_temp * $profondeur_temp * pow(10,-9);
      $this->poids += $qte * $poids_temp;
  }
  
  private function zone($site_code=null) {
      $z=$this->DB->query_first("SELECT zone_tnt FROM fraisport WHERE paysid=".$this->paysid);
      $zone=$z?intval($z[0]):false;
      return $zone;
  }
  
  private function paht_grille() {
      $max=$this->DB->query_first("SELECT MAX(fin) FROM `tnt_grille` WHERE debut<1000000");
      if($this->poidsvolume<$max[0]/1000){
	  $sql="SELECT paht FROM tnt_grille WHERE ".floatval(1000*$this->poidsvolume)." BETWEEN debut AND fin AND zone=".intval($this->zone);
	  $value=$this->DB->query_first($sql);
	  return floatval($value['paht']);
      }else{
	  $sql="SELECT paht FROM tnt_grille WHERE fin=".$max[0]." AND zone=".intval($this->zone);
	  $value=$this->DB->query_first($sql);
	  $tranches20=ceil(($this->poidsvolume-round($max[0]/1000))/20);
	  $tranchesupp=$this->DB->query_first("SELECT paht FROM tnt_grille WHERE debut=1000000 AND zone=".intval($this->zone));
	  return floatval($value['paht']+$tranches20*$tranchesupp[0]);
      }      
  }
  
  private function national($poids) {
      $poids=intval($poids);
      $tvafrance=$this->DB->query_first("SELECT TVAtauxnormal FROM pays WHERE paysid=57");
      $gratuitpro = "";
      if($this->pro) $gratuitpro = "pro";
      $sql="SELECT achat,prix,gratuit".$gratuitpro." as gratuit FROM fraisport WHERE sites='".$this->site_code."' AND ".$poids." BETWEEN poiddebut AND poidfin AND paysid=".intval($this->paysid);
      $port=$this->DB->query_first($sql);
      // Si c'est un pro on va voir si il a un tarif franco de port personnalisé dans sa fiche client. Sinon on prend le tarif pro par defaut.
      if($this->pro && intval($this->paysid) !== 181){
        if(!is_null($this->userid)){
          $portpro = $this->DB->query_first("SELECT francodeport FROM utilisateur WHERE userid ='".$this->userid."'");
          if(!is_null($portpro[0])){
            $port['gratuit'] = $portpro[0];     
          }
        }
      }
      if($port['gratuit']>0 && $port['gratuit']<=$this->totalttc) {
	     $this->prixbarreht=$port['prix']/(1+$tvafrance[0]/100);
	     $port['prix']=0;
      }
      $this->prixht=floatval($port['prix']/(1+$tvafrance[0]/100));
      $this->paht=floatval($port['achat']);
  }
  
  private function taxe_surete() {
      ksort($this->taxe_surete);
      $return=0;
      foreach($this->taxe_surete as $from=>$paht) {
	  if($this->poidsvolume>=$from) $return=$paht;
      }
      return $return;
  }
 
  private function taxe_carburant($paht) {
      $return=$paht*($this->taxe_carburant/100)*(1-$this->remise_carburant/100);
      return $return;
  }
  
  private function assurance() {
      $value=$this->totalpaht*($this->assurance_value/100);
      if($value<$this->assurance_mini) $value=$this->assurance_mini;
      return $value;
  }

  
  private function prixvente() {
      $this->prixht=$this->paht/((100-$this->marge)/100);
      return $this->prixht;
  }
  
  public function prixttc($taux,$change=1) {
      $taux=floatval($taux);
      $change=floatval($change);
      if($change==0) $change=1;
      if ($this->zone===0)
            $prix=round($this->prixht*(1+$taux/100)*$change,1);
      else
            $prix=ceil($this->prixht*(1+$taux/100)*$change);
      return $prix;
  }
  
  public function prixbarrettc($taux,$change=1) {
      $taux=floatval($taux);
      $change=floatval($change);
      if($change==0) $change=1;
      return ceil($this->prixbarreht*(1+$taux/100)*$change);
  }
  
  function debug() {
      return array(
	"artids"=>$this->artids,
	"paysid"=>$this->paysid,
	"totalttc"=>$this->totalttc,
	"totalpaht"=>$this->totalpaht,
	"supplement_taille_apply"=>$this->supplement_taille_apply,
	"volume"=>$this->volume,
	"poids"=>$this->poids,
	"poidsvolume"=>$this->poidsvolume,
	"zone"=>$this->zone,
	"dimensions"=>$this->dimensions,
	"paht_grille"=>$this->paht_grille,
	"taxe_surete"=>$this->taxe_surete_paht,
	"taxe_carburant"=>$this->taxe_carburant_paht,
	"assurance"=>$this->assurance_paht,
	"supplement_pays"=>$this->supplement_pays_paht,
	"supplement_taille"=>$this->supplement_taille_paht,
	"paht"=>$this->paht,
	"prixht"=>$this->prixht,
	"prixbarreht"=>$this->prixbarreht,
	"livre"=>$this->livre);
  }

  function getPoidsGramme(){
    $poids = round($this->poids) * 1000;
    return $poids;
  }
  function getPoidsKg(){
    return round($this->poids) ;
  }
}

?>