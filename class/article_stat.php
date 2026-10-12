<?php

// MULTISITE

class article_stat {

private $DB;
private $template;
private $site_code;
private $ht;
private $tauxtva;
private $langue;
private $artids;
private $tvafrance;
private $today;

public $max_pc;
public $min_pc;

public $max_reduc;
public $best_artcode;
public $best_libelle;
public $best_marque;
public $best_prix;

public $marques;

public $ventes_libelle;
public $ventes_artcode;
public $ventes_marque;
public $ventes_prix;

public $vu_libelle;
public $vu_artcode;
public $vu_marque;
public $vu_prix;

public $max_prix;
public $min_libelle;
public $min_artcode;
public $min_marque;
public $min_prix;

private $fields=array("max_pc"=>"% max de réduction","min_pc"=>"% min de réduction","max_reduc"=>"Réduction maximum",
"min_libelle"=>"Libellé du produit le moins cher","min_marque"=>"Marque du produit le moins cher","min_artcode"=>"Code article du produit le moins cher","min_prix"=>"Prix de l'article le moins cher",
"max_prix"=>"Prix de l'article le plus cher",
"best_artcode"=>"Code article avec la meilleure réduction","best_libelle"=>"Libellé article avec la meilleure réduction",
"best_marque"=>"Marque de l'article avec la meilleure réduction",
"best_prix"=>"Prix de l'article avec la meilleure réduction","marques"=>"Liste des marques",
"ventes_libelle"=>"Libellé article le + vendu","ventes_artcode"=>"Code article le + vendu","ventes_marque"=>"Marque article le + vendu","ventes_prix"=>"Prix de l'article le + vendu",
"vu_libelle"=>"Libellé article le + vu","vu_artcode"=>"Code article le + vu","vu_marque"=>"Marque article le + vu","vu_prix"=>"Prix article le + vu");

function __construct($DB,$template=null,$artids=null) {
    $this->DB=$DB;
    if(!is_null($template)){
	$this->template=$template;
	$this->site_code=$template->site_code();
	$this->ht=$template->pro;
	$this->tauxtva=$template->tva_ref();
	$this->langue=$template->langue;
    }
    if(!is_null($artids)) {
	$this->artids=$artids;
	$this->tvafrance=$this->DB->query_first("SELECT 0,TVAtauxnormal,TVAtauxreduit FROM pays WHERE paysid=57");
	$this->today=mktime(0,0,0);
    }
}

function pc_reduc() {
    unset($this->min_pc);
    unset($this->max_pc);
    unset($this->max_reduc);
    unset($this->min_prix);
    unset($this->max_prix);

    if($this->artids==false || is_null($this->artids) || count($this->artids)==0) return $ret;

    $req=$this->DB->query("SELECT artid,artcode,prix,prixconseilleht,tauxchoisi,libelle".$this->langue." AS libelle,m.libelle AS marque FROM article INNER JOIN article_marque USING(artid) INNER JOIN marque m USING(marqueid) WHERE artid IN (".implode(",",$this->artids).") AND active='1'");
    if($this->DB->num_rows($req)==0) return false;
    while($art=$this->DB->fetch_array($req)) {
	/*$promo=$this->DB->query_first("SELECT * FROM temp_price WHERE artid=".$art['artid']." AND ".$this->today." BETWEEN du AND au");
	if($promo['artid']==$art['artid']){
	   $art['prix']=$promo['prix'];
	   $art['prixconseilleht']=$promo['prixconseilleht'];
	}
      
	$vfarticle=$this->DB->query_first("SELECT * FROM vente_flash_article vfa INNER JOIN vente_flash vf ON (vf.id=vfa.vfid) WHERE (".time()." BETWEEN vf.du AND vf.au) AND vfa.artcode='$art[artcode]'");
	if($vfarticle['id']!="") {
	   $art['prix']=$vfarticle['prix'];
	}

	$tauxtva=1+($this->tvafrance[$art['tauxchoisi']]/100);
	$art['prixconseille']=$art['prixconseilleht']*$tauxtva;

	$pc=100-100*$art['prix']/$art['prixconseille'];*/
	$prices=article_prices($art['artid'],$this->tauxtva,$this->site_code,$this->ht);
	$pc=$prices['remise'];
	if(!isset($this->min_pc)  || $pc<$this->min_pc) $this->min_pc=$pc;
	if(!isset($this->max_pc) || $pc>$this->max_pc) $this->max_pc=$pc;
	if($prices['prixconseille']-$prices['prix'] > $this->max_reduc) {
	    $this->max_reduc=$prices['prixconseille']-$prices['prix'];
	    $this->best_libelle=$art['libelle'];
	    $this->best_artcode=$art['artcode'];
	    $this->best_marque=$art['marque'];
	    $this->best_prix=$prices['prix'];
	}

	if(is_null($this->min_prix) || $prices['prix']<$this->min_prix) {
	    $this->min_prix=$prices['prix'];
	    $this->min_artcode=$art['artcode'];
	    $this->min_libelle=$art['libelle'];
	    $this->min_marque=$art['marque'];
	}
  
	if($prices['prix']>$this->max_prix) $this->max_prix=$prices['prix'];
    }

    $this->max_pc=round($this->max_pc);
    $this->min_pc=round($this->min_pc);
    $this->max_reduc=$this->template->price_format($this->max_reduc);
    $this->best_prix=$this->template->price_format($this->best_prix);
    $this->min_prix=$this->template->price_format($this->min_prix);
    $this->max_prix=$this->template->price_format($this->max_prix);
    
    return array("min"=>$this->min_pc,"max"=>$this->max_pc,"best"=>array("artcode"=>$this->best_artcode,"libelle"=>$this->best_libelle,"marque"=>$this->best_marque,"reduc"=>$this->max_reduc,"prix"=>$this->best_prix));
}

function marques() {
    $ret=array();
    $this->marques=array();
    if($this->artids==false || is_null($this->artids) || count($this->artids)==0) return $ret;
    $req=$this->DB->query("SELECT libelle FROM marque INNER JOIN article_marque USING(marqueid) INNER JOIN article USING(artid) WHERE artid IN (".implode(",",$this->artids).") AND active='1'");
    while($m=$this->DB->fetch_array($req))
	$ret[]=$m['libelle'];
    
    $ret=array_unique($ret);
    asort($ret);
    $this->marques=implode(", ",$ret);
    return $ret;
}

function ventes() {
    $ret=array();
    
    if($this->artids==false || is_null($this->artids) || count($this->artids)==0) return $ret;

    $art=$this->DB->query_first("SELECT artid,artcode,prix,prixconseilleht,tauxchoisi,libelle".$this->langue." AS libelle,m.libelle AS marque FROM article INNER JOIN article_marque USING(artid) INNER JOIN marque m USING(marqueid) WHERE artid IN (".implode(",",$this->artids).") AND active='1' ORDER BY ventes DESC LIMIT 1");
    $this->ventes_artcode=$art['artcode'];
    $this->ventes_libelle=$art['libelle'];
    $this->ventes_marque=$art['marque'];
    
    /*$promo=$this->DB->query_first("SELECT * FROM temp_price WHERE artid=".$art['artid']." AND ".$this->today." BETWEEN du AND au");
    if($promo['artid']==$art['artid']){
	$art['prix']=$promo['prix'];
	$art['prixconseilleht']=$promo['prixconseilleht'];
    }
  
    $vfarticle=$this->DB->query_first("SELECT * FROM vente_flash_article vfa INNER JOIN vente_flash vf ON (vf.id=vfa.vfid) WHERE (".time()." BETWEEN vf.du AND vf.au) AND vfa.artcode='$art[artcode]'");
    if($vfarticle['id']!="") {
	$art['prix']=$vfarticle['prix'];
    }*/
    $prices=article_prices($art['artid'],$this->tauxtva,$this->site_code,$this->ht);
    $this->ventes_prix=$this->template->price_format($prices['prix']);
    
    return array("ventes_artcode"=>$this->ventes_artcode,"ventes_libelle"=>$this->ventes_libelle,"ventes_marque"=>$this->ventes_marque,"ventes_prix"=>$this->ventes_prix);
}

function vus() {
    $ret=array();
    
    if($this->artids==false || is_null($this->artids) || count($this->artids)==0) return $ret;

    $art=$this->DB->query_first("SELECT artid,artcode,prix,prixconseilleht,tauxchoisi,libelle".$this->langue." AS libelle,m.libelle AS marque FROM article a INNER JOIN article_marque USING(artid) INNER JOIN marque m USING(marqueid) WHERE artid IN (".implode(",",$this->artids).") AND active='1' ORDER BY (SELECT COUNT(*) FROM article_vu WHERE artid=a.artid AND date>".(time()-60*86400).") DESC LIMIT 1");
    $this->vu_artcode=$art['artcode'];
    $this->vu_libelle=$art['libelle'];
    $this->vu_marque=$art['marque'];
    
    /*$promo=$this->DB->query_first("SELECT * FROM temp_price WHERE artid=".$art['artid']." AND ".$this->today." BETWEEN du AND au");
    if($promo['artid']==$art['artid']){
	$art['prix']=$promo['prix'];
	$art['prixconseilleht']=$promo['prixconseilleht'];
    }
  
    $vfarticle=$this->DB->query_first("SELECT * FROM vente_flash_article vfa INNER JOIN vente_flash vf ON (vf.id=vfa.vfid) WHERE (".time()." BETWEEN vf.du AND vf.au) AND vfa.artcode='$art[artcode]'");
    if($vfarticle['id']!="") {
	$art['prix']=$vfarticle['prix'];
    }*/
    $prices=article_prices($art['artid'],$this->tauxtva,$this->site_code,$this->ht);
    $this->vu_prix=$this->template->price_format($prices['prix']);
    
    return array("vu_artcode"=>$this->vu_artcode,"vu_libelle"=>$this->vu_libelle,"vu_marque"=>$this->vu_marque,"vu_prix"=>$this->vu_prix);
}

function replace($txt) {
    if(!isset($this->max_pc)) $this->pc_reduc();
    if(!isset($this->marques)) $this->marques();
    if(!isset($this->ventes_artcode)) $this->ventes();
    if(!isset($this->vu_artcode)) $this->vus();

    if(preg_match_all("/\[(.*?)\]/i",$txt,$m)){
        foreach($m[1] as $tag){
            if(isset($this->$tag))
                $txt=preg_replace("/\[".$tag."\]/",$this->$tag,$txt);
        }
    }
    
    return $txt;
}

function fieldlist(){
    $ret="<b>Infos variables :</b>";
    foreach($this->fields as $field=>$desc)
	$ret.="<br>[$field] = $desc";

    return $ret;
}

}

?>