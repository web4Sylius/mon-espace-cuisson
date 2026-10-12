<?php

class lignefacturegarantie {

  private $DB;

  public $lignefacturegarantieid;
  public $lignefactureid;
  public $garantieid;
  public $libelle;
  public $differenceprix;
  public $paht;
  public $ppi;
  public $duree;
  public $fournisseur;
  public $codeFournisseur;
  public $pays;
  public $dureeReelle;
  public $type;

  function __construct($DB,$lignefacturegarantieid=0,$lignefactureid=null,$garantieid=null,$libelle=null,$differenceprix=null,$paht=null,$ppi=null,$duree=null,$fournisseur=null,$codeFournisseur=null,$pays=null,$dureeReelle=null,$type=null) {
      $this->DB=$DB;
      $this->lignefacturegarantieid=$lignefacturegarantieid;
      $this->lignefactureid=$lignefactureid;
      $this->garantieid=$garantieid;
      $this->libelle=$libelle;
      $this->differenceprix=floatval($differenceprix);
      $this->paht=floatval($paht);
      $this->ppi=floatval($ppi);
      $this->duree=intval($duree);
      $this->fournisseur=$fournisseur;
      $this->codeFournisseur=$codeFournisseur;
      $this->pays=$pays;
      $this->dureeReelle=$dureeReelle;
      $this->type=$type;
  }

  function getGarantie($garantieid=null) {
      if(is_null($garantieid)) $garantieid=$this->garantieid;
      if(is_null($garantieid)) return false;

      return new garantie($this->DB,'FR',$this->garantieid);
  }

  function delete(){
      if(is_null($this->lignefacturegarantieid)) return false;

      return $this->DB->query("DELETE FROM lignefacturegarantie WHERE lignefacturegarantie=".intval($this->lignefacturegarantieid));
  }

  function list_by_lignefactureid($lignefactureid) {

      $return=array();
      $req=$this->DB->query("SELECT * FROM lignefacturegarantie WHERE lignefactureid=".intval($lignefactureid));
      while($lfg=$this->DB->fetch_array($req))
	  $return[]=new lignefacturegarantie($this->DB,$lfg['lignefacturegarantieid'],$lfg['lignefactureid'],$lfg['garantieid'],$lfg['libelle'],$lfg['differenceprix'],$lfg['paht'],$lfg['ppi'],$lfg['duree'],$lfg['fournisseur'],$lfg['codeFournisseur'],$lfg['pays'],$lfg['dureeReelle'],$lfg['type']);

      return $return;
  }

}

?>