<?php

class facture{

private $DB;

public $facturid;
public $numerofacture;
public $passefacture;
public $utilisateur;
public $lignes=array();
public $lignesdiss=array();
public $userid;
public $raisonsociale;
public $nom;
public $prenom;
public $adresse;
public $ville;
public $codepostal;
public $pays;
public $paysid;
public $diminutif;
public $telephone;
public $infotranspo;
public $incoterms;
public $avisclient;

public $datecommande;
public $dateexpedition;
public $valide;
public $validetxt;
public $moyenid;
public $provenance;
public $provenanceid;
public $commercial;
public $cadeauid;
public $codepromo;

public $porttva;
public $portht;
public $portttc;
public $modelivraison;
public $totallignesht;
public $totallignesttc;
public $cadeauht=0;
public $cadeauttc=0;
public $partcadeau=array();
public $cadeautype;
public $totalht=0;
public $totalttc=0;

private $validetxts=array(0=>"Non traitée",1=>"Validée",2=>"Refus commerçant",3=>"Refusée",4=>"Abandon internaute",5=>"Expédiée",6=>"Remboursée",7=>"Validée en attente de paiement",8=>"Expédiée en attente de paiement");

function __construct($DB,$facturid=null){
    $this->DB=$DB;
    if($facturid==null) return;

    if(is_array($facturid))
      $f=$facturid;
    else{
      $f=$this->DB->query_first("SELECT f.*,p.libelle AS pays,p.diminutif,p.paysid FROM facture f INNER JOIN pays p USING(paysid) WHERE facturid=".intval($facturid));
      if($f['facturid']!=$facturid) return false;
    }

    $this->facturid=$f['facturid'];
    $this->numerofacture=$f['numerofacture'];
    $this->passefacture=$f['passefacture'];
    $this->userid=$f['userid'];
    $this->raisonsociale=$f['raisonsociale'];
    $this->nom=$f['nom'];
    $this->prenom=$f['prenom'];
    $this->adresse=$f['adresse'];
    $this->codepostal=$f['codepostal'];
    $this->ville=$f['ville'];
    $this->pays=$f['pays'];
    $this->paysid=$f['paysid'];
    $this->diminutif=$f['diminutif'];
    $this->telephone=$f['telephone'];
    $this->datecommande=$f['heurefacture'];
    $d=explode("-",$f['dateexpedition']);
    $this->dateexpedition=mktime(0,0,0,$d[1],$d[2],$d[0]);
    $this->valide=$f['valide'];
    $this->validetxt=$this->validetxts[$this->valide];
    $this->moyenid=$f['moyenid'];
    $this->provenance=$f['provenance'];
    $this->provenanceid=$f['provenanceid'];
    $this->commercial=$f['commercial'];
    $this->porttva=$f['tvaport'];
    $this->portttc=$f['prixport'];
    $this->portht=round($this->portttc/(1+$f['tvaport']/100),2);
    $this->modelivraison=$f['modelivraison'];
    $this->infotranspo=$f['infotranspo'];
    $this->incoterms=$f['incoterms'];
    $this->avisclient=$f['avis'];

    $this->cadeauid=$f['cadeauid'];
    $this->utilisateur=new utilisateur($DB,$this->userid);

    $this->lignes();
    $this->calccadeau($f);
    $this->totalht=$this->totallignesht+$this->portht-$this->cadeauht;
    $this->totalttc=$this->totallignesttc+$this->portttc-$this->cadeauttc;
}  

function lignes(){
    $this->totallignesttc=$this->totallignesht=0;
    $r=$this->DB->query("SELECT * FROM lignefacture WHERE factureid=".intval($this->facturid));
    while($ligne=$this->DB->fetch_array($r)){
	$this->lignes[$ligne['lignefactureid']]=new lignefacture($this->DB,$ligne);
	$this->totallignesttc+=$ligne['qte']*$ligne['prix'];
	$this->totallignesht+=round($ligne['qte']*($ligne['prix']/(1+$ligne['tva']/100)),2);
    }
    $r=$this->DB->query("SELECT * FROM lignefacturediss WHERE factureid=".intval($this->facturid));
    while($ligne=$this->DB->fetch_array($r)){
	$this->lignesdiss[$ligne['lignefactureid']]=new lignefacture($this->DB,$ligne);
    }
}

function calccadeau($facture){
    $totalreduc=array();
    if ($this->cadeauid != ""){
	    $rayons=array();
            $infoscadeau=$this->DB->query_first("SELECT * FROM cadeau WHERE cadeauid = '".$this->cadeauid."'");
            if($infoscadeau['code']!=""){
	      $this->codepromo=$infoscadeau['code'];
	      $this->cadeautype="general";
	      $article=$this->DB->query_first("SELECT artcode,a.artid from article a, article_cadeau ac where a.artid = ac.artid and cadeauid = '".$this->cadeauid."'");
	      if ($article[0] == "")
                $rayon=$this->DB->query_first("SELECT c.catid from categorie c, categorie_cadeau cc where c.catid = cc.catid and cadeauid = '".$this->cadeauid."'");
	      else
		$this->cadeautype="article";
	      
	      if($rayon['catid']!=""){
		$this->cadeautype="rayon";
		$rayons[]=$rayon['catid'];
		$ssrayons=$this->DB->query("SELECT catid FROM categorie WHERE parentid=".$rayon['catid']);
		while($ssrayon=$this->DB->fetch_array($ssrayons)){
		  $rayons[]=$ssrayon['catid'];
		  $ss2rayons=$this->DB->query("SELECT catid FROM categorie WHERE parentid=".$ssrayon['catid']);
		  while($ss2rayon=$this->DB->fetch_array($ss2rayons)) $rayons[]=$ss2rayon['catid'];
		}
	      }   
              
	      foreach($this->lignes as $ligneid=>$lignefacture){
		    $caract=$this->DB->query_first("SELECT SUM(differenceprix) FROM lignefacturecaracteristique WHERE lignefactureid=".$ligneid);
		    if($lignefacture->artcode==$article[0] || in_array($lignefacture->catid,$rayons) || ($article[0]=="" && $rayons==array()))
			$totalreduc[$lignefacture->tva]+=$lignefacture->qte*($lignefacture->prix-$caract[0]);
	      }

	      foreach($this->lignesdiss as $ligneid=>$lignefacture){
		    $caract=$this->DB->query_first("SELECT SUM(differenceprix) FROM lignefacturecaracteristique WHERE lignefactureid=".$ligneid);
		    if($lignefacture->artid==$article[1])
			$totalreduc[$lignefacture->tva]+=$lignefacture->qte*($lignefacture->prix-$caract[0]);
	      }

	      $this->cadeauttc=$facture['montantcadeau'];
	    }
	    

	    $code_caviste=$this->DB->query_first("SELECT * FROM code_caviste WHERE code='".$this->cadeauid."'");
	    if($code_caviste['code']!=""){
		$this->codepromo=$code_caviste['code'];
		$this->cadeautype="caviste".$code_caviste['type'];
		if($code_caviste['type']==2 || ($code_caviste['type']==1 && $facture['montantcadeau']<$code_caviste['mini'])){
		    $this->cadeauttc=$facture['montantcadeau'];
		    foreach($this->lignes as $ligneid=>$lignefacture){
			$caract=$this->DB->query_first("SELECT SUM(differenceprix) FROM lignefacturecaracteristique WHERE lignefactureid=".$ligneid);
			$totalreduc[$lignefacture->tva]+=$lignefacture->qte*($lignefacture->prix-$caract[0]);
		    }
		}
		
	    }

	    foreach($totalreduc as $txtva=>$totalttc){
		$this->partcadeau[$txtva]=round($this->cadeauttc*$totalttc/array_sum($totalreduc),2);
		$this->cadeauht+=round($this->partcadeau[$txtva]/(1+$txtva/100),2);
	    }
    }

}

function search($sql){
    $ret=array();
    $r=$this->DB->query("SELECT facturid FROM facture WHERE ".$sql);
    while($f=$this->DB->fetch_array($r))
	$ret[$f['facturid']]=new facture($this->DB,$f['facturid']);

    return $ret;
}

     public function getMoyen($moyen_id){
          $sql = "SELECT libelle".$this->utilisateur->langue." AS libelle";
          $sql .= " FROM moyenpaiement";
          $sql .= " WHERE moyenid = ".$moyen_id;
          $tabMoyen = $this->DB->query_first($sql);
          
          return $tabMoyen['libelle'];
     }

}

class lignefacture {

private $DB;

public $lignefactureid;
public $facturid;
public $artid;
public $artcode;
public $catid;
public $qte;
public $libelle;
public $marque;
public $prix;
public $prixht;
public $tva;
public $paht;

public $totalht;
public $totalttc;

function __construct($DB,$data){
     $this->DB=$DB;
     if(is_array($data))
	$this->load($data);
     else{
	$l=$this->DB->query_first("SELECT lf.*,m.libelle AS marque FROM lignefacture lf LEFT JOIN article_marque am USING(artid) INNER JOIN marque m USING(marqueid) WHERE lignefactureid=".intval($lignefactureid));
	$this->load($l);
     }
}

function load($l){
     $this->lignefactureid=$l['lignefactureid'];
     $this->facturid=$l['factureid'];
     $this->artid=$l['artid'];
     $this->artcode=$l['artcode'];
     $this->catid=$l['catid'];
     $this->qte=$l['qte'];
     $this->libelle=$l['libelle'];
     $this->marque=$l['marque'];
     $this->prix=$l['prix'];
     $this->tva=$l['tva'];
     $this->paht=$l['paht'];
     $this->prixht=round($this->prix/(1+$this->tva/100),2);
     $this->totalht=$this->prixht*$this->qte;
     $this->totalttc=$this->prix*$this->qte;
}

function caracteristiques(){
    $ret=array();
    $rq_caracteristiques=$this->DB->query("SELECT lfc.*,cv.caractid FROM lignefacturecaracteristique lfc INNER JOIN caracteristiquevaleur cv USING(caractvalid) WHERE lignefactureid='".$this->lignefactureid."'") ;
    while ($rs_caracteristiques=$this->DB->fetch_array($rq_caracteristiques)){
	$ret[$rs_caracteristiques[caractid]]="$rs_caracteristiques[libelle_caract] : $rs_caracteristiques[libelle_caractval]";
	
    }
    return $ret;
}

}



class utilisateur {

    private $DB;

    public $userid;
    public $nom;
    public $prenom;
    public $mail;
    public $civilite;
    public $codepostal;
    public $adresse;
    public $ville;
    public $telephone;
    public $portable;
    public $raisonsociale;
    public $dateinscription;
    public $paysid;
    public $pays;
    public $diminutif;
    public $tvaintracom;
    public $langue='FR';
    public $code411;
    public $fonction;
    public $ancien;

    function __construct($DB,$userid=null){
        $this->DB=$DB;
        
        if(is_null($userid)){
            $u=$DB->query_first("SELECT * FROM utilisateur INNER JOIN pays USING (paysid) WHERE userid=".intval($userid));

            $this->userid=$u['userid'];
            $this->nom=$u['nom'];
            $this->prenom=$u['prenom'];
            $this->mail=$u['mail'];
            $this->civilite=$u['civilite'];
            $this->codepostal=$u['codepostal'];
            $this->adresse=$u['adresse'];
            $this->ville=$u['ville'];
            $this->telephone=$u['telephone'];
            $this->portable=$u['portable'];
            $this->raisonsociale=$u['raisonsociale'];
            $d=explode("-",$u['dateinscription']);
            $this->dateinscription=mktime(0,0,0,$d[1],$d[2],$d[0]);
            $this->paysid=$u['paysid'];
            $this->pays=$u['libelle'];
            $this->diminutif=$u['diminutif'];
            $this->tvaintracom=$u['tvaintracom'];
            if(!is_null($u['langue']) || $u['langue'] !='') $this->langue=$u['langue'];
            $this->code411=$u['code411'];
            $this->fonction=$u['profession'];
        }
    }
    
    function contacts(){
        $contacts=array();
        $req=$this->DB->query("SELECT * FROM utilisateur_assoc WHERE userid=".$this->userid);
        while($contact=$this->DB->fetch_array($req)){
            $u=new utilisateur($this->DB);
            $u->raisonsociale=$this->raisonsociale;
            $u->nom=$contact['utilisateur_assoc_nom'];
            $u->prenom=$contact['utilisateur_assoc_prenom'];
            $u->mail=$contact['utilisateur_assoc_email'];
            $u->telephone=$contact['utilisateur_assoc_tel'];
            $u->portable=$contact['utilisateur_assoc_portable'];
            $u->fonction=$contact['utilisateur_assoc_fonction'];
            $u->ancien=$contact['actif'];
            $u->langue=$this->langue;
            $contacts[]=$u;
        }
        
        return $contacts;
    }
}

?>
