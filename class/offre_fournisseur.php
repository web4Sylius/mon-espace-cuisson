<?php

class offre_fournisseur {
    
    private $DB;
    
    private $id;
    private $du;
    private $au;
    private $date_valide;
    private $fournisseur;
    private $articles;
    
    function __construct($DB,$offreid=null){
	$this->DB=$DB;
	if(!is_null($offreid)) $this->load($offreid);
    }
    
    function __set($var,$value){
	if(($var=="du" || $var=="au") && preg_match("/\d{1,2}\/\d{1,2}\/\d{4}/",$value)){
	    $d=date_create_from_format("d/m/Y",$value);
	    $d->settime(0,0,0);
	    $value=$d->getTimestamp();
	}
    
	$this->$var=$value;
    }
    
    function __get($var){
	return $this->$var;
    }
    
    function load($offreid){
	$sql="SELECT fo.id,fo.fournisseur,fo.du,fo.au,foa.artid,foa.offre,foa.value,foa.qte 
	FROM fournisseur_offre fo INNER JOIN fournisseur_offre_article foa ON(fo.id=foa.offreid) 
	WHERE fo.id=".intval($offreid);
	
	$req=$this->DB->query($sql);
	while($data=$this->DB->fetch_array($req)){
	    $this->id=$data['id'];
	    $this->du=$data['du'];
	    $this->au=$data['au'];
	    $this->date_valide=($this->du<=time() && ($this->au>0 || $this->au<=time()));
	    $this->fournisseur=$data['fournisseur'];
	    $this->articles[$data['artid']]=array("qte"=>$data['qte'],"offre"=>$data['offre'],"value"=>$data['value']);
	}
    }
    
    function save(){
	$insert=(!isset($this->id) || $this->id==0);
	
	if($insert)
	    $sql="INSERT INTO fournisseur_offre SET ";
	else
	    $sql="UPDATE fournisseur_offre SET ";
	$sql.="du=".intval($this->du).", au=".intval($this->au).", fournisseur='".mysqli_real_escape_string($this->DB->link_id,$this->fournisseur)."'";
	if(!$insert) $sql.=" WHERE id=".$this->id;

	$result=$this->DB->query($sql);
	
	if($insert)
	    $this->id=$this->DB->insert_id();
	else
	    $this->DB->query("DELETE FROM fournisseur_offre_article WHERE offreid=".$this->id);
	    
	foreach($this->articles as $artid=>$infos){
	    $this->DB->query("INSERT INTO fournisseur_offre_article SET 
	    offreid=".intval($this->id).", 
	    artid=".intval($artid).", 
	    qte=".intval($infos['qte']).", 
	    offre='".mysqli_real_escape_string($this->DB->link_id,$infos['offre'])."', 
	    value=".floatval($infos['value']));
	}
	
	return $result;
    }
    
    function delete(){
	$this->DB->query("DELETE FROM fournisseur_offre WHERE id=".$this->id);
	$this->DB->query("DELETE FROM fournisseur_offre_article WHERE offreid=".$this->id);
    }
    
    function remove_articles(){
	$this->articles=array();
    }
    
    function new_article($artid,$qte,$offre,$value){
	$artid=intval($artid);
	if(!is_null($this->articles) && array_key_exists($artid,$this->articles)) return false;
	
	$this->articles[$artid]=array("qte"=>intval($qte),"offre"=>$offre,"value"=>floatval($value));
	return $this->articles[$artid];
    }
    
    function paht_articles($artids){
	$return=array();
	
	$this->verif_articles($artids);

	foreach($this->articles as $artid=>$infos){
	    $sql="SELECT prix FROM article_fournisseur WHERE
		fournisseur='".mysqli_real_escape_string($this->DB->link_id,$this->fournisseur)."' AND artid=".$artid;
	    $pa=$this->DB->query_first($sql);
	    $this->articles[$artid]['paorigin']=$pa['prix'];
	
	    switch($infos['offre']){
		case "temp":
		$this->articles[$artid]['paht']=$infos['value'];
		$offreqte=$infos['qte'];
		break;
	    
		case "reduc":
		$this->articles[$artid]['paht']=$pa['prix']-$infos['value'];
		$offreqte=$infos['qte'];
		break;
		
		case "pc":
		$this->articles[$artid]['paht']=$pa['prix']*(1-$infos['value']/100);
		$offreqte=$infos['qte'];
		break;
		
		case "offert":
		$this->articles[$artid]['paht']=0;
		$offreqte=$infos['value'];
		break;
		
		case "":
		$this->articles[$artid]['paht']=$pa['prix'];
		$offreqte=0;
		break;
	    }
	    
	    $return[$artid][strval($this->articles[$artid]['paht'])]=intval($offreqte*$this->nboffre);
	    if($this->nboffre*$offreqte<$artids[$artid])
		$return[$artid][$pa['prix']]=$artids[$artid]-($this->nboffre*$offreqte);
	
	}
	
	$nonoffre=array_diff(array_keys($artids),array_keys($this->articles));
	foreach($nonoffre as $artid){
	    $sql="SELECT prix FROM article_fournisseur WHERE
		fournisseur='".mysqli_real_escape_string($this->DB->link_id,$this->fournisseur)."' AND artid=".$artid;
	    $pa=$this->DB->query_first($sql);
	    if($pa!="")
		$return[$artid][$pa['prix']]=$artids[$artid];
		
	    $autres_offres=$this->search_artids(array($artid=>$artids[$artid]),null,time(),true);
	    foreach($autres_offres as $autre_offre){
		if($autre_offre->du==$this->du && $autre_offre->au==$this->au){
		    $paht_list=$autre_offre->paht_articles(array($artid=>$artids[$artid]));
		    $return[$artid]=$paht_list[$artid];
		}
	    }
	}

	return $return;
    }
    
    function search_fournisseur($fournisseur,$expire=false){
	$sql="SELECT id FROM fournisseur_offre
	WHERE fournisseur='".mysqli_real_escape_string($this->DB->link_id,$fournisseur)."'";
	if(!$expire) $sql.=" AND (au=0 OR au>".time().")";
	
	$return=array();
	$req=$this->DB->query($sql);
	while($data=$this->DB->fetch_array($req)){
	    $offre=new offre_fournisseur($this->DB,$data['id']);
	    $return[]=$offre;
	}
	return $return;
    }
    
    function search_active(){
	$now=time();
	$sql="SELECT id FROM fournisseur_offre
	WHERE (du=0 OR du<".$now.") AND (au=0 OR au>".$now.")";
	$return=array();
	$req=$this->DB->query($sql);
	while($data=$this->DB->fetch_array($req)){
	    $offre=new offre_fournisseur($this->DB,$data['id']);
	    $return[]=$offre;
	}
	return $return;
    }
    
    function search_artids($artids,$group=null,$ts=null,$futur=null){
	$return=array();
	if(count($artids)==0) return $return;
	if(is_null($ts)) $ts=time();
	
	$sqlart=array();
	foreach($artids as $artid=>$qte)
	    $sqlart[]="(artid=".$artid." AND qte<=".$qte.")";
	    
	$sql="SELECT id FROM fournisseur_offre 
	WHERE id IN (SELECT offreid FROM fournisseur_offre_article WHERE ".implode(" OR ",$sqlart).")";
	if(!is_null($futur))
	    $sql.="AND au>".$ts;
	else
	    $sql.="AND (du<=".$ts.") AND (au=0 OR (au+86400)>=".$ts.")";
	
	$req=$this->DB->query($sql);
	while($data=$this->DB->fetch_array($req)){
	    $offre=new offre_fournisseur($this->DB,$data['id']);
	    if($offre->verif_articles($artids)!=false){
		if(!is_null($group))
		    $return[$offre->$group][]=$offre;
		else
		    $return[]=$offre;
	    }
	}
	return $return;
    }
    
    function artids_facture($facturid,$noexp=false){
	$artids=array();
	$sql="SELECT artid,qte FROM lignefacture WHERE factureid=".intval($facturid);
	if($noexp) $sql.=" AND (SELECT COUNT(*) FROM envoi_fournisseur_ligne WHERE ligne_facture_id=lignefactureid AND type='fourn')=0";
	$req=$this->DB->query($sql);
	while($lf=$this->DB->fetch_array($req))
	    $artids[$lf['artid']]+=$lf['qte'];
	    
	return $artids;
    }
    
    function verif_articles($artids){

	foreach($this->articles as $artid=>$infos){
	    if(array_key_exists($artid,$artids)){
		$nboffre[]=floor($artids[$artid]/$infos['qte']);
		if($nboffre==0) return false;
	    }else
		return false;
	}
	$this->nboffre=min($nboffre);
	return $this->nboffre;
    }
    
    function fournisseurs(){
	$return=array();
	$sql="SELECT fournisseur FROM fournisseur_offre GROUP BY fournisseur";
	$req=$this->DB->query($sql);
	while($f=$this->DB->fetch_array($req)){
	    $return[]=$f[0];
	}
	ksort($return);
	return $return;
    }
    
    function reduc_devise(){
	$condition=$this->DB->query_first("SELECT devise FROM fournisseur_condition WHERE societe='".mysqli_real_escape_string($this->DB->link_id,$this->fournisseur)."'");
	return $condition['devise'];
    }
}

?>