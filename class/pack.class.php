<?php

class Pack{
     private $DB;
     
     public function __construct($DB){
          $this->DB = $DB;
     }
     
     public function getDetails($pack_id){
          $sql = "SELECT a.artid,a.artcode, a.libelleFR, a.libelleEN, a.libelleSP, a.delailivraison, a_s.prix as prix_pack_ttc, a_s.active AS active, ap.publie AS publie";
          $sql .= " FROM article a";
          $sql .= " LEFT JOIN article_pack ap ON (ap.packid = a.artid)
		    LEFT JOIN article_site a_s ON (a_s.artid=ap.packid)";
          $sql .= " WHERE a.artid = '".$pack_id."'";
          $sql .= " LIMIT 0,1";
          $tab = $this->DB->fetch_array($this->DB->query($sql));

          return $tab;
     }
     
     public function image($pack_id,$langue=null){
	  $return=false;
	  $sql="SELECT articlephotoid,langue FROM articlephoto WHERE artid=".$pack_id;
	  if(!is_null($langue)) $sql.=" AND langue='".$langue."'";
	  $req=$this->DB->query($sql);
	  while($img=$this->DB->fetch_array($req)){
	      if(is_null($langue))
		  $return[$img['langue']]=$img['articlephotoid'];
	      else
		  $return=$img['articlephotoid'];
	  }
	  return $return;
     }
     
     public function getProduits($pack_id){
	  $tab=array();
          $sql = "SELECT  *, ap.prix AS prixpack";
          $sql .= " FROM article_pack ap";
          $sql .= " INNER JOIN article a ON (a.artid = ap.artid)";
          $sql .= " WHERE packid = '".$pack_id."'";
          $sql .= " ORDER BY a. prix DESC";
          $req = $this->DB->query($sql);
          while($row = $this->DB->fetch_array($req)){
               $tab[$row['artid']] = $row;
          }
          
          return $tab;
     }
     
     public function isPack($artid){
	  $sql="SELECT COUNT(*) FROM article_pack WHERE packid=".intval($artid);
	  $p=$this->DB->query_first($sql);
	  return $p[0]>0;
     }
     
     public function active($pack_id){
	  $sql="SELECT COUNT(artid) FROM article_pack 
	  INNER JOIN article_site a_s USING(artid)
	  INNER JOIN article a USING(artid) 
	  WHERE a_s.site_code='base' AND packid=".intval($pack_id)." AND (a_s.active!=1 OR a.commandable!='1')";
	  $p=$this->DB->query_first($sql);
	  return $p[0]==0;
     }
     
     public function newref($artid){
	  $base=$this->DB->query_first("SELECT artcode FROM article WHERE artid=".intval($artid));
	  preg_match("/^ACI-([A-Z0-9]+)-?.*$/",$base['artcode'],$m);
	  
	  $strSqlRefMax = "SELECT COUNT(*)
	   FROM article
	   WHERE artcode LIKE 'PAK-".$m[1]."-%'";
	  
	  $arRefMax = $this->DB->query_first($strSqlRefMax);
	  $intRefMax = $arRefMax[0]+1;
	  
	  return "PAK-".$m[1]."-".$intRefMax;
     }

     public function newoffre($artid){
	  $base=$this->DB->query_first("SELECT artcode FROM article WHERE artid=".intval($artid));
	  preg_match("/^ACI-([A-Z0-9]+)-?.*$/",$base['artcode'],$m);
	  
	  $strSqlRefMax = "SELECT COUNT(*)
	   FROM article
	   WHERE artcode LIKE 'OFR-".$m[1]."-%'";
	  
	  $arRefMax = $this->DB->query_first($strSqlRefMax);
	  $intRefMax = $arRefMax[0]+1;
	  
	  return "OFR-".$m[1]."-".$intRefMax;
     }
    
     public function contains($artid, $sitepro=null){
	  $tab=array();
	  $in = " AND publie IN(".($sitepro == null ? "'1','3'" : "'2','3'").")";
	  $sql="SELECT ap.* FROM article_pack ap WHERE artid=".$artid.$in;
	  $req=$this->DB->query($sql);
	  while($row=$this->DB->fetch_array($req)){
	    $tab[]=$row['packid'];
	  }
	  return $tab;
     }
     
     public function search($artcode,$active=1){
	  if(!is_null($active) && !is_array($active)) $active=array($active);
     
	  $tab=array();
	  $sql="SELECT ap.* FROM article_pack ap WHERE
	    artid IN (SELECT artid FROM article WHERE artcode LIKE '%".$artcode."%')";
	  if(!is_null($active)) $sql.=" AND (SELECT active FROM article_site WHERE artid=ap.packid) IN (".implode(",",$active).")";
	  $sql.=" GROUP BY ap.packid";
	    
	  $req=$this->DB->query($sql);
	  while($row=$this->DB->fetch_array($req)){
	    $tab[]=$row['packid'];
	  }
	  return $tab;
     }
     
     public function kdos_pack($artid,$sitepro=null){
	  $in = " AND ap.publie IN(".($sitepro == null ? "'1','3'" : "'2','3'").")";
	  $sql="SELECT packid 
	  FROM article_pack ap INNER JOIN article_site a_s ON(a_s.artid=ap.packid)
	  WHERE ap.artid=".$artid." AND ap.prix>0 AND offre LIKE 'pdtoffert%' AND a_s.active=1 AND (SELECT COUNT(*) FROM article_pack WHERE packid=ap.packid)=2 ".$in;
	  $data=$this->DB->query_first($sql);
	  return intval($data['packid']);
     }

    public function offres_pack($artid,$sitepro=null){
    	$in = " AND ap.publie IN(".($sitepro == null ? "'1','3'" : "'2','3'").")";
	  	$sql = "SELECT packid 
	  			FROM article_pack ap INNER JOIN article_site a_s ON(a_s.artid=ap.packid)
	  			WHERE ap.artid=".$artid." AND ap.prix>0 AND offre LIKE 'pdtoffert%' AND (SELECT COUNT(*) FROM article_pack WHERE packid=ap.packid)=2 ".$in." 
	  			ORDER BY FIELD(a_s.active,1,0,2)";
	  	$req=$this->DB->query($sql);
	  	while($row=$this->DB->fetch_array($req)){
	    	$tab[]=$row['packid'];
	  	}
	  	return $tab;
     }
     
     public function prices($pack_id,$taux,$ht){
	  $articles=$this->getProduits($pack_id);
	  $prix=$prixconseille=0;
	  $cadeau=array("prix"=>0,"prixconseille"=>0);
	  foreach($articles as $article){
	      $prices=article_prices($article['artid'],$taux,"base",$ht);
	      if($article['prixpack']>0){
		  $prix+=$article['qte']*$prices['prix'];
		  $prixconseille+=$article['qte']*$prices['prixconseille'];
	      }else{
		  $cadeau['prix']+=$article['qte']*$prices['prix'];
		  $cadeau['prixconseille']+=$article['qte']*$prices['prixconseille'];
	      }
	      $offre=$article['offre'];
	  }

	  if($offre!=""){
	      list($type,$valeur)=explode("-",$offre);
	      switch($type){
		  case "euro":
		  $remisepack=$valeur;
		  if($ht) $remisepack=$valeur/(1+$taux['taux1']/100);
		  $prix-=floatval($valeur);
		  break;
		  
		  case "pourcent":
		  $remisepack=$prix*(intval($valeur)/100);
		  $prix-=$remisepack;
		  break;
		  
		  case "pdtoffert":
		  $prixconseille+=$cadeau['prixconseille'];
		  break;
	      }
	  }
	  
	  if($prixconseille!=0)
	    $remise=100-(($prix*100)/$prixconseille);
	  else
	    $remise=0;
	  
	  return array("prix"=>$prix,"prixconseille"=>$prixconseille,"remise"=>$remise,"remisepack"=>$remisepack);
     }
     
     function stats($packid){
	  $packid=intval($packid);
	  $validecmd=array(1,5,7,8);
     
	 $sql="SELECT DISTINCT facturid, valide
	  FROM facture
	  INNER JOIN lignefacture ON ( facturid = factureid )
	  WHERE packid =".$packid;
	  
	  $return=array("devis"=>0,"cmd"=>0);
	  
	  $req=$this->DB->query($sql);
	  while($f=$this->DB->fetch_array($req)){
		$type="devis";
		if(in_array($f['valide'],$validecmd)) $type="cmd";
		$return[$type]++;
	  }
	  
	  $sql="SELECT SUM((prix / ( 1 + tva /100 ) ) * qte)
	  FROM lignefacture INNER JOIN facture ON(facturid=factureid)
	  WHERE packid=".$packid." AND valide in (1,5,7,8)";
	  
	  $prix=$this->DB->query_first($sql);
	  $return['total']=floatval($prix[0]);
	  
	  return $return;
     }
}
