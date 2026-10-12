<?php

// GARANTIES
// faire comptabilité multi devises

class marge{

public $DB;
public $articles=array();
public $installation=0;

public $facture;

public $poids=0;
public $total=0;
public $totalgarantie=0;

public $ventearticles=0;
public $codepromo=0;
public $venteport=0;
public $venteportttc=0;
public $totalvente=0;
public $totalventettc=0;

public $marge=0;
public $pcmarge=0;

function marge($DB,$facturid){
    $this->DB=$DB;
    $this->facture=$this->DB->query_first("SELECT * FROM facture WHERE facturid=".$facturid);
    if($this->facture==false) return false;
    $installtype=12;
    $montage_heures=0;
    $lignes=$this->DB->query("SELECT lignefactureid FROM lignefacture WHERE factureid=".$facturid);
    while($ligne=$this->DB->fetch_array($lignes)){
        $l=new achat($this->DB,$facturid,$ligne[0]);
        $this->articles[$ligne[0]]=$l;
        $this->totalgarantie+=$l->garantie;
        $this->total+=$l->total;
        $this->poids+=$l->poids;
        $montage_heures+=$l->qte*$l->montage_heures;
        if($this->maxweight($l->artid)>=110) $installtype=13;
    }

    if($this->facture['modelivraison']=='1' || $this->facture['modelivraison']=='4'){
        
        $installateur=new fournisseur($this->DB,"AGEDISS");
        $params=array("poids"=>$this->poids,"type"=>$installtype,"pays"=>$this->facture['paysid']);
        if(in_array($this->facture['paysid'],array(57,181))){
            $dep=substr($this->facture['codepostal'],0,2);
            $params["departement"]=$dep;
        }
        $this->installation=$installateur->getTarifTransport($params);
        
        if($this->facture['modelivraison']=='4'){
            $montage=$this->DB->query_first("SELECT paht FROM montage WHERE paysid=".$this->facture['paysid']." AND actif=1");
            if($montage!="") $this->installation+=$montage['paht']*$montage_heures;
        }
        
        $this->total+=$this->installation;
    }

    $this->vente();

    $this->marge=$this->totalvente-$this->total;
    if($this->totalvente==0)
        $this->pcmarge=0;
    else
        $this->pcmarge=round(100*$this->marge/$this->totalvente,2);
}



//// calcul de la marge: fonction qui est écrite pour correspondre au calcul de la marge dans le fichier facture.php (ref: Boucle sur toutes les commandes liées, pour déterminer la marge)
function margetotal($DB,$facturid){
    $return=array();
    $this->DB=$DB;

$parent     = $this->getparentsav($facturid);
$links      = $this->savlink($parent);
$savlinks   = $this->savlinkids($parent,$links);

 foreach($savlinks as $linkid){


$factures=$this->DB->query("SELECT facturid,montantcadeau,prixport,tvaport,commission,provenance FROM facture WHERE facturid=".$linkid." AND valide IN (1,5,7,8)");

    foreach($factures as $facture){

        $lignes=$this->DB->query("SELECT lf.prix, lf.qte, lf.tva FROM lignefacture lf WHERE factureid=".$facture['facturid']);

        while($ligne=$this->DB->fetch_array($lignes)){
            if($ligne["tva"] == 20) {
                $tva = 1.2;
            }
            elseif($ligne["tva"] == 10) {
                $tva = 1.1;
            }
            elseif($ligne["tva"] == 5) {
                $tva = 1.055;
            }
            else {
                $tva = 1;
            }

            $totalfactureht += ($ligne["prix"] * $ligne["qte"])/$tva;
        }
        $totalfactureht -= round($facture["montantcadeau"]/(1+20/100),2);
        $totalfactureht += round($facture["prixport"]/(1+$facture['tvaport']/100),2);

        $sql = "SELECT efl.paht, efl.ecopart, efl.qte";
        $sql .= " FROM envoi_fournisseur ef";
        $sql .= " INNER JOIN envoi_fournisseur_ligne efl ON (efl.efid = ef.id)";
        $sql .= " INNER JOIN lignefacture lf ON (efl.ligne_facture_id=lf.lignefactureid)";
        $sql .= " WHERE ef.destinataire <> '0'";
        $sql .= " AND ef.facturid = ".$facture['facturid'];
        $requete = $this->DB->query($sql);

        while($ligne = $this->DB->fetch_array($requete)){
            $totalachat += $ligne['qte'] * ($ligne['paht'] + $ligne['ecopart']);
        }

        $sql = "SELECT prix,type";
        $sql .= " FROM facture_fournisseur_autre";
        $sql .= " WHERE facturid = ".$facture['facturid'];
        $requete = $this->DB->query($sql);
        $commission_recue = FALSE; 
        while($ligne = $this->DB->fetch_array($requete)){
            $totalachat     += $ligne['prix']; 
            if($ligne['type'] == "com") {
                $commission_recue = TRUE;
            }
        }

        $sql = "SELECT a.paht, COUNT(*) AS qte, s.paht";
        $sql.= " FROM stock_item s";
        $sql.= " INNER JOIN article a USING(artid)";
        $sql.= " WHERE origin ='retour:".$facture['facturid']."'";
        $sql.= " GROUP BY date";
        $req = $this->DB->query($sql);
        while($retour = $this->DB->fetch_array($req)){
            $totalachat = $totalachat - ($retour['qte']*$retour['paht']);
        }
        if($facture["provenance"] == "mp" && $facture["commission"] != 0 && $commission_recue == FALSE){
            $totalachat = $totalachat + $facture['commission'];
        }

        $sql = "SELECT paht, ecopart, qte, date_recu";
        $sql .= " FROM avoir_ligne";
        $sql .= " WHERE facturid = ".$facture['facturid'];
        $requete =  $this->DB->query($sql);
        while($ligne= $this->DB->fetch_array($requete)){
            if($ligne['date_recu'] == 0){
                $totalachat -= ($ligne['paht']+$ligne['ecopart']);
            }
        }       
    }
}

    $totalachat = (round($totalachat,2) == '-0') ? '0' : round($totalachat,2);
    $margett     = $totalfactureht-$totalachat;
    $return["margett"] = number_format($margett,2,',','');
    $return["totalachat"] = $totalachat;
    $return["totalfactureht"] = $totalfactureht;
    $return["parentid"] = $savlinks[0];
    if($totalfactureht == 0.00 || $totalachat == 0.00) {
        return;
    }
    else {

    $return["margett_pc"] = round(100*(1-$totalachat/$totalfactureht),2);
    return $return;
}
    
}

function vente(){
    $ventettc=0;
    $totalttc=array();
    $requete=$this->DB->query("SELECT * FROM lignefacture WHERE factureid=".$this->facture['facturid']);
    while($ligne=$this->DB->fetch_array($requete)){
        $caracts=$this->DB->query_first("SELECT SUM(differenceprix) FROM lignefacturecaracteristique WHERE lignefactureid=".$ligne['lignefactureid']);
        $this->ventearticles+=($ligne['prix']*$ligne['qte'])/(1+$ligne['tva']/100);
        $this->articles[$ligne['lignefactureid']]->vente(($ligne['prix']*$ligne['qte']),$ligne['tva']);
        $ventettc+=$ligne['prix']*$ligne['qte'];
        $totalttc[$ligne['tva']]+=($ligne['prix']-$caract[0])*$ligne['qte'];
    }
    
    $this->venteport=($this->facture['prixport']/(1+$this->facture['tvaport']/100));
    if(array_sum($totalttc)>0){
        foreach($totalttc as $tx=>$value){
            $this->codepromo-=($this->facture['montantcadeau']*$value/array_sum($totalttc))/(1+$tx/100);
        }
    }
    $this->totalvente=$this->ventearticles+$this->venteport+$this->codepromo;
    $this->venteportttc=$this->facture['prixport'];
    $this->codepromottc=$this->facture['montantcadeau'];
    $this->totalventettc=$ventettc+$this->venteportttc-$this->codepromottc;

}

function set_marge($pc){
    $return=array();
    $return['pc']=$pc;
    $txtva=(1+$this->facture['tvaport']/100);
    $return['tx']=$txtva;
    $return['pv']=$this->total/(1-$pc/100);
    $return['pvttc']=$return['pv']*$txtva;

    $return['remise']=$this->totalvente-$return['pv'];
    $return['remisettc']=$return['remise']*$txtva;
    if($this->totalvente==0)
      $return['pcremise']=0;
    else
      $return['pcremise']=100*(1-$return['pv']/$this->totalvente);
    $return['marge']=$return['pv']-$this->total;

    return $return;
}

function maxweight($artid){
    if($artid=="" || $artid==0) return 0;
    $lot=$this->DB->query_first("SELECT (emballage_poids1+0), (emballage_poids1+0),(emballage_poids1+0) FROM article a INNER JOIN article_lot al USING(artid) WHERE al.lotid=".intval($artid));
    
    if(is_null($lot[0])){
        $article=$this->DB->query_first("SELECT (emballage_poids1+0), (emballage_poids1+0),(emballage_poids1+0) FROM article WHERE artid=".intval($artid));
        
        return $article[0];
    }else
        return $lot[0];
    
}

///// pour la marge

function getparentsav($linkid){
    global $DB_site2;

    $savfrom=$DB_site2->query_first("SELECT provenanceid FROM facture WHERE provenance IN ('sav','avoir') AND facturid=".intval($linkid));

    if($savfrom[0]!="")
        return $this->getparentsav($savfrom[0]);
    else
        return $linkid;
}

function savlink($linkid){
    global $DB_site2;

    $return=array();

    if($sens=="to" || is_null($sens)){
        $r=$DB_site2->query("SELECT facturid FROM facture WHERE provenance IN ('sav','avoir') AND provenanceid=".intval($linkid));

        while($savto=$DB_site2->fetch_array($r)){
            $return[$savto[0]]=$this->savlink($savto[0]);
        }

        if($DB_site2->num_rows($r)==0) $return=false;
    }

    return $return;
}

function savlinkids($parent,$links){
    $return=array($parent);
    IF($links==false) return $return;
    foreach($links as $link=>$a){
        $return=array_merge($return,$this->savlinkids($link,$a));
    }

    return $return;
}

}


class achat{

public $DB;

public $artid;
public $artcode;
public $qte=0;
public $paht=0;
public $ecopart=0;
public $prixtrspt=0;
public $garantie=0;
public $options=0;
public $transport=0;
public $montage_heures;

public $prixht;
public $prix;

public $poids=0;
public $total=0;

public $marge=0;
public $pcmarge=0;

public $garcaractvalid=array(9,10,11,154,155,132,133,134);

function achat($DB,$facturid,$lfid){
    $this->DB=$DB;

     $facture=$this->DB->query_first("SELECT * FROM facture WHERE facturid=".$facturid);
     $lf=$this->DB->query_first("SELECT * FROM lignefacture WHERE lignefactureid=".$lfid);
     $article=$this->DB->query_first("SELECT paht,ecopartht,prixtrspt,emballage_poids1,emballage_poids2,emballage_poids3,montage,montage_heures FROM article WHERE artid='".$lf['artid']."'");
     $article['emballage_poids'] = $article['emballage_poids3']+$article['emballage_poids2']+$article['emballage_poids1'];
     $this->artid=$lf['artid'];
     $this->artcode=$lf['artcode'];
     if($this->artcode=="") $this->artcode=substr($lf['libelle'],0,10)."...";
     $this->qte=$lf['qte'];
     $this->paht=$lf['paht'];
     $this->ecopart=floatval($article['ecopartht']);
     $this->prixtrspt=floatval($article['prixtrspt']);
     $this->poids=$lf['qte']*floatval($article['emballage_poids']);
     $this->montage_heures=($article['montage']==1?$article['montage_heures']:false);

     $caracts=$this->DB->query("SELECT * FROM lignefacturecaracteristique WHERE lignefactureid=".$lfid);
      while($caract=$this->DB->fetch_array($caracts)){
      $achat=$this->DB->query_first("SELECT * FROM article_caractval WHERE caractvalid=".$caract['caractvalid']." AND artid=".$lf['artid']);
//    if(in_array($caract['caractvalid'],$this->garcaractvalid))
//        $this->garantie+=$achat['achat'];
//    else
          $this->options+=$achat['achat'];
      }
      
      $garanties=DAOgarantie::getGarantiesByProduitCmd($DB,$lfid);
      foreach($garanties as $garantie)
      $this->garantie+=$garantie['paht'];

      /*
      $fraisport=$this->DB->query_first("SELECT * FROM fraisport WHERE paysid=".$facture['paysid']." AND ".(1000*$this->poids)." BETWEEN poiddebut AND poidfin");
      $this->transport=$fraisport['achat'];
      if($this->paht>=750) $this->transport+=(0.008*$this->paht+10);
      */
      
      $frais_port=new frais_port($this->DB,array($this->artid=>$this->qte),$facture['paysid'],0,$this->paht+$this->ecopart);
      $this->transport=$frais_port->paht;
      
      $this->total=($this->paht+$this->ecopart+$this->prixtrspt+$this->garantie+$this->options)*$this->qte;
      $this->total+=$this->transport;

}

function vente($prix,$tx){
      $this->prixht=$prix/(1+$tx/100);
      $this->prix=$prix;
      if($this->prixht<0){
    $this->total=0;
    $this->paht=0;
    $this->ecopart=0;
    $this->garantie=0;
    $this->options=0;
    $this->transport=0;
      }
      $this->marge=$this->prixht-$this->total;
      if($this->prixht==0)
      $this->pcmarge=0;
      else
      $this->pcmarge=round(100*$this->marge/$this->prixht,2);

}
}
