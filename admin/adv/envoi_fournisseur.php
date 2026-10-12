<?php

class envoi_fournisseur{
  public $DB;

  public $id;
  public $facturid;
  public $date;
  public $destinataire;
  public $tarif;
  public $devise;
  public $type;
  public $assur_comp;
  public $trackid;
  public $lieu_enlev;
  public $lieu_livr;
  public $trspt;
  public $date_enlev;
  public $date_livr;
  public $comment;

  public $lignes;

  function __construct($DB,$id=null){
      $this->DB=$DB;
      
      if($id!=null){
	  $data=$this->DB->query_first("SELECT * FROM envoi_fournisseur WHERE id=".intval($id));
	  $this->loaddata($data);
	  $this->loadlignes();
      }
  }

  function loaddata($data){
      $this->id=$data['id'];
      $this->facturid=$data['facturid'];
      $this->date=$data['date'];
      $this->destinataire=$data['destinataire'];
      $this->tarif=$data['tarif'];
      $this->devise=$data['devise'];
      $this->type=$data['type'];
      $this->assur_comp=$data['assur_comp'];
      $this->trackid=$data['trackid'];
      $this->lieu_enlev=$data['enlev'];
      $this->lieu_livr=$data['livr'];
      $this->trspt=$data['trspt'];
      $this->date_enlev=$data['enlevement'];
      $this->date_livr=$data['livraison'];
      $this->comment=$data['comment'];
  }

  function loadlignes(){
      if($this->id==null) return false;
      $this->lignes=array();
      $list=$this->DB->query("SELECT * FROM envoi_fournisseur_ligne WHERE efid=".$this->id);
      while($data=$this->DB->fetch_array($list)){
	  $efl=new envoi_fournisseur_ligne($this->DB);
	  $efl->loaddata($data);
	  $this->lignes[]=$efl;
      }
  }

  function create(){
      $result=$this->DB->query("INSERT INTO envoi_fournisseur SET date=".time().", facturid=".$this->facturid.", destinataire='".mysqli_real_escape_string($this->DB->link_id,$this->destinataire)."', tarif=".floatval($this->tarif).", devise='".$this->devise."', type='".$this->type."', assur_comp=".floatval($this->assur_comp).", trackid='".$this->trackid."', enlev=".intval($this->lieu_enlev).", livr=".intval($this->lieu_livr).", trspt='".mysqli_real_escape_string($this->DB->link_id,$this->trspt)."'");
      $this->id=$this->DB->insert_id();
      return $result;
  }

  function create_lines($eflines){
      foreach($eflines as $line){
	  $line->efid=$this->id;
	  $this->lignes[]=$line;
	  $line->create();
      }
  }

  function update(){
      // if(!$this->facturid || !$this->destinataire || !$this->devise || !$this->type || !$this->assur_comp || !$this->trackid || !$this->lieu_enlev || !$this->lieu_livr || !$this->trspt || !$this->date_enlev || !$this->date_livr || !$this->id) return false;
      if(!$this->id) return false;

      $q = 'SET';
      if($this->facturid) {
        if($q != 'SET') $q .= ',';
        $q .= " facturid=".$this->facturid;
      }
      if($this->destinataire) {
        if($q != 'SET') $q .= ',';
        $q .= " destinataire='".mysqli_real_escape_string($this->DB->link_id,$this->destinataire)."'";
      }
      if($this->tarif) {
        if($q != 'SET') $q .= ',';
        $q .= " tarif=".floatval($this->tarif);
      }
      if($this->devise) {
        if($q != 'SET') $q .= ',';
        $q .= " devise='".$this->devise."'";
      }
      if($this->type) {
        if($q != 'SET') $q .= ',';
        $q .= " type='".$this->type."'";
      }
      if($this->assur_comp) {
        if($q != 'SET') $q .= ',';
        $q .= " assur_comp=".floatval($this->assur_comp);
      }
      if($this->trackid) {
        if($q != 'SET') $q .= ',';
        $q .= " trackid='".$this->trackid."'";
      }
      if($this->lieu_enlev) {
        if($q != 'SET') $q .= ',';
        $q .= " enlev=".intval($this->lieu_enlev);
      }
      if($this->lieu_livr) {
        if($q != 'SET') $q .= ',';
        $q .= " livr=".intval($this->lieu_livr);
      }
      if($this->trspt) {
        if($q != 'SET') $q .= ',';
        $q .= " trspt='".mysqli_real_escape_string($this->DB->link_id,$this->trspt)."'";
      }
      if($this->date_enlev) {
        if($q != 'SET') $q .= ',';
        $q .= " enlevement=".intval($this->date_enlev);
      }
      if($this->date_livr) {
        if($q != 'SET') $q .= ',';
        $q .= " livraison=".intval($this->date_livr);
      }
      if($this->date) {
        if($q != 'SET') $q .= ',';
        $q .= " date='".$this->date."'";
      }

      if($q == 'SET') return false;

      // $result=$this->DB->query("UPDATE envoi_fournisseur SET facturid=".$this->facturid.", destinataire='".mysqli_real_escape_string($this->DB->link_id,$this->destinataire)."', tarif=".floatval($this->tarif).", devise='".$this->devise."', type='".$this->type."', assur_comp=".floatval($this->assur_comp).", trackid='".$this->trackid."', enlev=".intval($this->lieu_enlev).", livr=".intval($this->lieu_livr).", trspt='".mysqli_real_escape_string($this->DB->link_id,$this->trspt)."', enlevement=".intval($this->date_enlev).", livraison=".intval($this->date_livr)." WHERE id=".$this->id);
      $result=$this->DB->query("UPDATE envoi_fournisseur " .$q. " WHERE id=".$this->id);
      return $result;
  }
  
  function delete(){
      if($this->id==null || $this->id=="") return false;
      $this->DB->query("DELETE FROM envoi_fournisseur WHERE id=".$this->id);
      foreach($this->lignes as $ligne) $ligne->delete();
  }
  
  function update_tarif($frais=null){
      $sum=$this->DB->query_first("SELECT SUM((paht * qte) + ecopart) FROM envoi_fournisseur_ligne WHERE efid=".$this->id);
      $this->tarif=$sum[0] + $frais;
      $this->update();
  }
}


class envoi_fournisseur_ligne{
   public $DB;

   public $id;
   public $efid;
   public $date;
   public $indate;
   public $outdate;
   public $origin_in;
   public $origin_out;
   public $imperatif;
   public $expediteur;
   public $type;
   public $lignefactureid;
   public $paht;
   public $ecopart;
   public $devise;
   public $qte;
   public $facture;

   function __construct($DB){
	$this->DB=$DB;
   }

   function loaddata($data){
	$this->id=$data['id'];
	$this->efid=$data['efid'];
	$this->date=$data['date'];
	$this->indate=$data['indate'];
	$this->outdate=$data['outdate'];
	$this->origin_in=$data['origin_in'];
	$this->origin_out=$data['origin_out'];
	$this->imperatif=$data['imperatif'];
	$this->expediteur=$data['expediteur'];
	$this->type=$data['type'];
	$this->lignefactureid=$data['ligne_facture_id'];
	$this->paht=$data['paht'];
	$this->ecopart=$data['ecopart'];
	$this->devise=$data['devise'];
	$this->qte=$data['qte'];
	$this->facture=$data['facture'];
   }

    function create(){
	$result=$this->DB->query("INSERT INTO envoi_fournisseur_ligne SET efid=".intval($this->efid).", date=".time().", expediteur='".mysqli_real_escape_string($this->DB->link_id,$this->expediteur)."', indate=".intval($this->indate).", outdate=".intval($this->outdate).", origin_in=".intval($this->indate).", origin_out=".intval($this->outdate).", imperatif=".intval($this->imperatif).", type='".$this->type."', ligne_facture_id=".intval($this->lignefactureid).", paht=".floatval($this->paht).", ecopart=".floatval($this->ecopart).", devise='".$this->devise."', qte=".intval($this->qte).", facture='".$this->facture."'");
	$this->id=$this->DB->insert_id();
	return $result;
    }

    function update(){
	$result=$this->DB->query("UPDATE envoi_fournisseur_ligne SET indate=".intval($this->indate).", outdate=".intval($this->outdate).", origin_in=".intval($this->indate).", origin_out=".intval($this->outdate).", imperatif=".intval($this->imperatif).", paht=".floatval($this->paht).", ecopart=".floatval($this->ecopart).", devise='".$this->devise."', qte=".intval($this->qte).", facture='".$this->facture."' WHERE id=".$this->id);

	return $result;
    }
    
    function delete(){
	$this->DB->query("DELETE FROM envoi_fournisseur_ligne WHERE id=".$this->id);
    }

    function lignefacture(){
	$lf=new lignefacture($this->DB,$this->lignefactureid);
	return $lf;
    }
}