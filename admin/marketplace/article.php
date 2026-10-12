<?php

// MULTISITE

class article{
public $artid;
public $artcode;
public $catid;
public $active;
public $prix;
public $prixht;
public $prixachat;
public $paht;
public $prixtrspt;
public $ecopartht;
public $caract_prix;
public $caract_paht;
public $fraisport;
public $fraisport_origin;
public $achatport;
public $portgratuit;
public $tva;
public $libelle;
public $artcode_fabricant;
public $artcode_eurocave;
public $artcode_amazon;
public $gamme;
public $marque;
public $lot;
public $poids_emballage;
public $reappro;
public $argument;
public $commandable;
public $DB;
public $use_temp_price=false;
public $site_code="base";

function article($DB){
	$this->DB = $DB;
    $requete=$this->DB->query("SELECT * FROM pays WHERE paysid=57");
    $taux=$this->DB->fetch_array($requete);
    $this->taux[1]=$taux['TVAtauxnormal'];
    $this->taux[2]=$taux['TVAtauxreduit'];
}

function load($id){
     $sql = "SELECT article.*, m.libelle AS marque, m.marqueid";
     $sql .= " FROM article";
     $sql .= " INNER JOIN article_marque am USING(artid)";
     $sql .= " INNER JOIN marque m USING(marqueid)";
     $sql .= " WHERE artid = $id";
     $requete = $this->DB->query($sql);
     
     if($this->DB->num_rows($requete)==0 || $requete==false) return false;
     
     $row = $this->DB->fetch_array($requete);
     
     $this->internal_load($row);
     
     return true;
}


function internal_load($array){
    $this->artid=intval($array['artid']);
    $this->artcode=$array['artcode'];
    $this->catid=intval($array['catid']);
    $this->libelle=$array['libelleFR'];
    $this->artcode_eurocave=$array['artcode_eurocave'];
    $this->artcode_amazon=$array['artcode_amazon'];
	$this->gamme=$array['gammeFR'];
	$this->marque=$array['marque'];
    $this->marqueid=$array['marqueid'];
	$this->visible=$array['commandable'];
	$this->commandable=$array['commandable'];
	$this->paht=$array['paht'];
	$this->prixtrspt=$array['prixtrspt'];
	$this->poids_emballage=$array['emballage_poids'];
	$this->date_appro=$array['date_appro'];
	$this->argument=$array['argument'];

	$listeprix=$this->article_site();
	$this->prix=floatval($listeprix[$this->site_code]['prix']);
	$this->tva=floatval($this->taux[$listeprix[$this->site_code]['tauxchoisi']]);
	$this->choixtva=$listeprix[$this->site_code]['tauxchoisi'];
	$this->prixht=floatval($this->prix/(1+$this->tva/100));
	$this->ecopartht=$array['ecopartht'];
    $this->strike_out=floatval($listeprix[$this->site_code]['prixconseilleht']);
	$this->active=intval($listeprix[$this->site_code]['active']);
	$this->prixachat=round(($array['ecopartht']+$array['paht'])*(1+$this->tva/100),2);
        
	if($this->use_temp_price){
	    $req=$this->DB->query("SELECT * FROM temp_price WHERE artid=".$this->artid." AND site_code='".$this->site_code."' AND ".mktime(0,0,0)." BETWEEN du AND au");
	    $temp_price=$this->DB->fetch_array($req);
	    if($temp_price['id']!=""){
		$this->prix=$temp_price['prix'];
		$this->prixht=floatval($this->prix/(1+$this->tva/100));
		$this->strike_out=$temp_price['prixconseilleht'];
		$this->prixachat=round(($array['ecopartht']+$temp_price['paht'])*(1+$this->tva/100),2);
		$this->paht=$temp_price['paht'];
	    }
	}
	
	$this->stock=0;
	$r=$this->DB->query("SELECT artid,artids FROM stock_mini WHERE artid=".$this->artid." OR artids LIKE '%".$this->artid."%'");
	while($stock=$this->DB->fetch_array($r)){
	    $spartid=explode(",",$stock['artids']);
	    if(in_array($this->artid,$spartid) || $stock['artid']==$this->artid) $this->stock=1;
	}

	$r=$this->DB->query("SELECT id FROM article_lot WHERE lotid=".$this->artid);
	$this->lot=$this->DB->num_rows($r);
}

function similaire(){
	if($this->artid==null) return array();
	
	$ret=array();
	$r=$this->DB->query("SELECT * FROM article_similaire WHERE gid=(SELECT gid FROM article_similaire WHERE artid=".intval($this->artid)." LIMIT 1)");
	while($a=$this->DB->fetch_array($r)) $ret[$a['gid']][]=$a['artid'];
	return $ret;
}

function article_site() {
	$return=array();
	$req=$this->DB->query("SELECT a_s.*, a.commandable FROM article_site as a_s INNER JOIN article as a USING(artid) WHERE artid=".$this->artid);
	while($as=$this->DB->fetch_array($req)) {
	    $return[$as['site_code']]=array("prix"=>$as['prix'],"prixconseilleht"=>$as['prixconseilleht'],"tauxchoisi"=>$as['tauxchoisi'],"active"=>$as['active'], "commandable"=>$as['commandable']);
	}
	return $return;
}

function article_fournisseur() {
	$return=array();
	$req=$this->DB->query("SELECT * FROM article_fournisseur WHERE artid=".$this->artid);
	while($af=$this->DB->fetch_array($req)) {
	    $site_codes=explode(",",$af['principal']);
	    foreach($site_codes as $site_code)
		$return[$site_code]=array("paht"=>$af['prix'],"trspt"=>$af['trspt'],"ecopartht"=>$af['ecopartht'],"devise"=>$af['devise']);
	}
	return $return;
}

function search($condition="",$order="",$group="",$limit=""){
	$return=array();
	$sql="SELECT *, m.libelle AS marque, a_s.active AS sactive, a_s.site_code AS scode FROM article INNER JOIN article_marque am USING(artid) INNER JOIN marque m USING(marqueid) INNER JOIN article_site a_s USING(artid)";
	if($condition!="") $sql.=" WHERE ".$condition;
	if($this->showenabled) $sql.=" AND `show`=1";
	if($order!="") $sql.=" ORDER BY ".$order;
	if($group!="") $sql.=" GROUP BY ".$group;
	if($limit!="") $sql.=" LIMIT ".$limit;
	$result = @$this->DB->query($sql);

	while($model = @$this->DB->fetch_array($result)) {
		if($model==false) return FALSE;
		$c=new article($this->DB);
		$c->internal_load($model);
		$return[$model['artid']]=$c;
	}
	return $return;
}

function caracteristiques($artid=null){
	if(is_null($artid)) $artid=$this->artid;
	$optmin=$optval=$optpaval=array();
    $ret=array();
    $requete=$this->DB->query("SELECT c.libelleFR as caractname,cv.caractvalid AS caractvalid,cv.caractid,cv.position, cv.libelleFR AS caractvalname,ac.differenceprix,ac.achat
								FROM article_caractval ac, caracteristiquevaleur cv, caracteristique c
								WHERE ac.caractvalid=cv.caractvalid AND c.caractid=cv.caractid AND ac.artid=".$artid."
								ORDER BY c.position ASC");
    while($car=$this->DB->fetch_array($requete)){
        $ret[$car['caractname']][$car['caractvalid']]=$car['caractvalname'];
		if(!isset($optmin[$car['caractid']]) || $car->position<$optmin[$car['caractid']]){
			$optmin[$car['caractid']]=$car['position'];
			$optval[$car['caractid']]=$car['differenceprix'];
			$optpaval[$car['caractid']]=$car['achat'];
		}
	}
	$this->caract_prix=array_sum($optval);
	$this->caract_paht=array_sum($optpaval);
    return $ret;
}

function setpays($paysid){
	$requete=$this->DB->query("SELECT 0,TVAtauxnormal,TVAtauxreduit FROM pays WHERE paysid=".$paysid);
	$tauxpays=$this->DB->fetch_array($requete);
	$this->prix=round($this->prixht*(1+$tauxpays[$this->choixtva]/100),2);
	$this->tva=$tauxpays[$this->choixtva];
}

function ventes($ts){
	$req=$this->DB->query("SELECT sum(qte) FROM `lignefacture` inner join facture on (factureid=facturid) where valide in (1,5,7,8) and heurefacture>".intval($ts)." AND artid=".$this->artid);
	if($req==false) return 0;
	$data=$this->DB->fetch_array($req);
	$req=$this->DB->query("SELECT sum(qte) FROM `lignefacturediss` inner join facture on (factureid=facturid) where valide in (1,5,7,8) and heurefacture>".intval($ts)." AND artid=".$this->artid);
	$data2=$this->DB->fetch_array($req);
	return intval($data[0]+$data2[0]);
}

function arguments(){
	$req=$this->DB->query("SELECT artcode,argument FROM article INNER JOIN article_lot USING(artid) WHERE lotid=".intval($this->artid));
	if($this->DB->num_rows($req)>0){
	  while($art=$this->DB->fetch_array($req)) if($art['argument']!="") $this->argument.="-".$art['artcode']." : ".$art['argument'];
	}

	return ($this->argument!="");
}

function calcport(){
	$req=$this->DB->query("SELECT * FROM fraisport WHERE ".intval($this->poids_emballage*1000)." BETWEEN poiddebut AND poidfin AND paysid=57");
	$fraisport=$this->DB->fetch_array($req);
	$this->fraisport=$fraisport['prix'];
	$this->fraisport_origin=$fraisport['prix'];
	if($this->prix>=$fraisport['gratuit']) $this->fraisport=0;
	$this->achatport=$fraisport['achat'];
	$this->portgratuit=$fraisport['gratuit'];
}

}

?>