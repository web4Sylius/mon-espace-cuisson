<?php

include_once(__DIR__."/../class/facture.class.php");

class projets {
    private $DB;
    
    private $etats=array();
    private $encours=array();
    private $clos=array(5,6);
    
    function __construct(){
        global $DB_site;
        global $DB_site2;
    
        if(isset($DB_site)) $this->DB=$DB_site;
        if(isset($DB_site2)) $this->DB=$DB_site2;
        
        $sql="SELECT * FROM projet_etat";
        $req=$this->DB->query($sql);
        while($etat=$this->DB->fetch_array($req)){
            $this->etats[$etat['etatid']]=$etat['libelle'];
            if($etat['encours']==1) $this->encours[]=$etat['etatid'];
        }
    }
    
    function search($commercial=null,$etat=null,$budget_type=null,$fromrappel=null,$torappel=null,$rappelcommercial=null,$nobudget=null,$noecheance=null,$nocmddelai=null,$txt=null,$dateecheance=null){
        $sql="SELECT projetid FROM projet WHERE 1";
        if(!is_null($fromrappel) && !is_null($torappel)){
            if(is_a($fromrappel,"datetime")) $fromrappel=$fromrappel->gettimestamp();
            if(is_a($torappel,"datetime")) $torappel=$torappel->gettimestamp();
            if($torappel===false) $torappel=mktime(0,0,0,1,1,2038);
            $sql.=" AND (rappel BETWEEN ".intval($fromrappel)." AND ".intval($torappel)." OR date(from_unixtime(echeance_livraison)) = DATE_ADD('".date("Y-m-d",$fromrappel)."',interval delai_cmd day))";
        }
        if(!empty($rappelcommercial)){
            if(is_array($rappelcommercial)) $rappelcommercial=implode("','",$rappelcommercial);
            $sql.=" AND rappel_commercial IN ('".mysqli_real_escape_string($this->DB->link_id,$rappelcommercial)."')";
        }

        if(!empty($commercial)){
            if(is_array($commercial)){
                $commercial=implode("','",$commercial);
            }
            $sql.=" AND commercial IN ('".mysqli_real_escape_string($this->DB->link_id,$commercial)."')";
        }
        if(!empty($etat)){
            if(is_array($etat)){
                $etat=implode(",",$etat);
            }
            $sql.=" AND etatid IN (".$etat.")";
        }
        if(!is_null($budget_type) && $budget_type!==""){
            $sql.=$this->sql_budget($budget_type);
        }
        
        if($nobudget)
            $sql.=" AND budget=0";
        if($noecheance)
            $sql.=" AND (echeance_livraison=0 OR delai_cmd=0)";
        if($nocmddelai)
            $sql.=" AND delai_cmd=0";
            
        if(is_array($dateecheance))
            $sql.=" AND delai_cmd>0 AND FROM_UNIXTIME(echeance_livraison)>=DATE_ADD('".date("Y-m-d",$dateecheance[0])."',interval delai_cmd day) AND FROM_UNIXTIME(echeance_livraison)<=DATE_ADD('".date("Y-m-d",$dateecheance[1])."',interval delai_cmd day)";
         
        if(!is_null($txt)){
            if(is_numeric($txt)){
                $sql.=" AND (projetid=".intval($txt)."
                OR projet.userid=".intval($txt)."
                OR projet.projetid IN (SELECT projetid FROM projet_relation WHERE userid=".intval($txt)."))";
            }elseif(preg_match("/^#?PJT(\d+)#?$/i",$txt,$m)){
                $sql.=" AND projetid=".$m[1];
            }else{
                $sql.=" AND (libelle LIKE '%".mysqli_real_escape_string($this->DB->link_id,$txt)."%'
                OR projet.userid IN (SELECT userid FROM utilisateur WHERE nom LIKE '%".mysqli_real_escape_string($this->DB->link_id,$txt)."%' OR raisonsociale LIKE '%".mysqli_real_escape_string($this->DB->link_id,$txt)."%')
                OR projet.projetid IN (SELECT projetid FROM projet_relation INNER JOIN utilisateur USING (userid) WHERE nom LIKE '%".mysqli_real_escape_string($this->DB->link_id,$txt)."%' OR raisonsociale LIKE '%".mysqli_real_escape_string($this->DB->link_id,$txt)."%'))";
            }
        }
            
        $sql.=" GROUP BY projetid";
// var_dump($sql);
        return $this->results($sql);
    }
    
    function norappel($commercial=null,$etat=null,$budget_type=null){
        $time=new datetime("tomorrow 0:0:0");
        $sql="SELECT projet.projetid FROM projet WHERE rappel=0";
        if(!empty($commercial)){
            if(is_array($commercial)){
                $commercial=implode("','",$commercial);
            }
            $sql.=" AND projet.commercial IN ('".mysqli_real_escape_string($this->DB->link_id,$commercial)."')";
        }
        if(!empty($etat)){
            if(is_array($etat)){
                $etat=implode(",",$etat);
            }
            $sql.=" AND etatid IN (".$etat.")";
        }
        if(!is_null($budget_type) && $budget_type!==""){
            $sql.=$this->sql_budget($budget_type);
        }
        $sql.=" GROUP BY projetid";
//         var_dump($sql);
        return $this->results($sql);
    }
    
    function utilisateur($userid){
        $sql="SELECT * FROM projet WHERE userid=".intval($userid)." OR projetid IN (SELECT projetid FROM projet_relation WHERE userid=".intval($userid).")";
        return $this->results($sql);
    }
    
    private function sql_budget($budget_type){
        $budget=$this->DB->query_first("SELECT * FROM projet_type WHERE projet_typeid=".intval($budget_type));
        if(is_null($budget['superieur'])){
            $sql=" AND projet_typeid=".intval($budget_type);
            if($budget_type==0) $sql.=" AND budget=0";
        }else{
            $budget2=$this->DB->query_first("SELECT * FROM projet_type WHERE superieur>".floatval($budget['superieur']));
            $sql=" AND (projet_typeid=".intval($budget_type)." OR";
            if($budget2=="")
                $sql.=" (projet_typeid=0 AND budget>=".floatval($budget['superieur']).")";
            else
                $sql.=" (projet_typeid=0 AND budget>".($budget['superieur']>0?"=":"").floatval($budget['superieur'])." AND budget<".floatval($budget2['superieur']).")";
            $sql.=")";
        }
        return $sql;
    }
    
    private function results($sql){
        $return=array();
        $req=$this->DB->query($sql);
        while($data=$this->DB->fetch_array($req)){
            $return[]=new projet($data['projetid']);
        }
        
        return $return;
    }

    function budget_total_encours($commercial=null,$etat=null){
        $state="";
        if(!empty($etat)){
            if(is_array($etat)){
                $etat=implode(",",$etat);
            }
            $state.=" AND etatid IN (".$etat.")";
        }

        if(!is_null($commercial)){
            $sql="SELECT SUM(budget) as budget_tt FROM projet WHERE commercial = '".$commercial."'".$state;
        }
        else{
            $sql="SELECT SUM(budget) as budget_tt FROM projet WHERE 1".$state;
        }
        $req=$this->DB->query_first($sql);
        
        return number_format($req['budget_tt'],2,',',' ');
    }
    
    function budget_types(){
        $return=array();
        $sql="SELECT * FROM projet_type WHERE 1";
        $req=$this->DB->query($sql);
        while($data=$this->DB->fetch_array($req)){
            $return[$data['projet_typeid']]=$data['libelle'];
        }
        
        return $return;
    }
    
    function stats($commercial=null){
        $budget_types=array();
        $budget_tranche=array();
        $sql="SELECT * FROM projet_type WHERE 1";
        $req=$this->DB->query($sql);
        while($data=$this->DB->fetch_array($req)){
            $budget_types[$data['projet_typeid']]=$data['libelle'];
            if(!is_null($data['superieur'])) $budget_tranche[$data['projet_typeid']]=$data['superieur'];
        }
        asort($budget_tranche);
        
        $today=new datetime("today 23:59:59");
        
        $periodes=array(
            "semaine"=>array(new datetime("monday this week 0:0:0"),new datetime("sunday this week 23:59:59")),
            "semaineprochaine"=>array(new datetime("monday next week 0:0:0"),new datetime("sunday next week 23:59:59")),
            "mois"=>array(new datetime("first day of this month 0:0:0"),new datetime("last day of this month 23:59:59"))
        );
        
        $statdelai=array("passe"=>array(),"sans"=>array());
        $statbudget=array();
        $statinfos=array();
        $sql="SELECT projetid,projet.userid,dateline,projet.commercial,budget,etatid,projet_typeid,echeance_livraison,delai_cmd,rappel FROM projet";
//         $sql.=" LEFT JOIN suivi_client sc USING (projetid)";
        $sql.=" WHERE etatid IN (".implode(",",$this->encours).")";
        if(!empty($commercial)) $sql.=" AND projet.commercial='".mysqli_real_escape_string($this->DB->link_id,$commercial)."'";
        $sql.=" GROUP BY projetid";
        $req=$this->DB->query($sql);
        while($data=$this->DB->fetch_array($req)){
            $budget_type=0;
            if($data['projet_typeid']==0){
                if($data['budget']>0){
                    foreach($budget_tranche as $btid=>$sup)
                        if($data['budget']>=$sup)
                            $budget_type=$btid;
                }
            }else
                $budget_type=$data['projet_typeid'];
            
            if(!isset($statbudget[$budget_type])) $statbudget[$budget_type]=array();
            if(!isset($statbudget[$budget_type][$data['etatid']])) $statbudget[$budget_type][$data['etatid']]=array("qte"=>0,"total"=>0);
            $statbudget[$budget_type][$data['etatid']]['qte']++;
            $statbudget[$budget_type][$data['etatid']]['total']+=$data['budget'];

            if(!isset($statinfos[$budget_type])) $statinfos[$budget_type]=array("budget"=>0,"echeance"=>0,"delai"=>0,"rappel"=>0);
            if($data['budget']==0) $statinfos[$budget_type]['budget']++;
            if($data['echeance_livraison']==0) $statinfos[$budget_type]['echeance']++;
            if($data['delai_cmd']==0) $statinfos[$budget_type]['delai_cmd']++;
            if($data['rappel']==0) $statinfos[$budget_type]['rappel']++;
            
            if($data['delai_cmd']>0 && $data['echeance_livraison']>0){
                $datecmd=new datetime();
                $datecmd->setTimestamp($data['echeance_livraison']);
                $datecmd->modify("-".$data['delai_cmd']." days");
                $diff=$today->diff($datecmd);
                if($diff->invert==1){
                    if(!isset($statdelai["passe"][$budget_type])) $statdelai["passe"][$budget_type]=array("qte"=>0,"total"=>0);
                    $statdelai["passe"][$budget_type]['qte']++;
                    $statdelai["passe"][$budget_type]['total']+=$data['budget'];
                }
                
                foreach($periodes as $key=>$periode){
                    if($datecmd>=$periode[0] && $datecmd<=$periode[1]){
                        if(!isset($statdelai[$key][$budget_type])) $statdelai[$key][$budget_type]=array("qte"=>0,"total"=>0);
                        $statdelai[$key][$budget_type]['qte']++;
                        $statdelai[$key][$budget_type]['total']+=$data['budget'];
                    }
                }
                
                $m=$datecmd->format("Y-m");
                if(!isset($statdelai[$m][$budget_type])) $statdelai[$m][$budget_type]=array("qte"=>0,"total"=>0);
                $statdelai[$m][$budget_type]['qte']++;
                $statdelai[$m][$budget_type]['total']+=$data['budget'];
            }else{
                if(!isset($statdelai["sans"][$budget_type])) $statdelai["sans"][$budget_type]=array("qte"=>0,"total"=>0);
                $statdelai["sans"][$budget_type]['qte']++;
                $statdelai["sans"][$budget_type]['total']+=$data['budget'];
//                 if($budget_type==1)var_dump($data['projetid']);
            }
        }

        return array("budget"=>$statbudget,"infos"=>$statinfos,"delai"=>$statdelai);
    }
    
    function __get($var){
        return isset($this->$var) ? $this->$var : null;
    }
}

class projet {
    private $DB;
    
    private $projetid;
    private $pjt;
    private $userid;
    private $relationids=array();
    private $date;
    private $commercial;
    private $libelle;
    private $besoin;
    private $budget;
    private $budget_type;
    private $budget_devis;
    private $etatid;
    private $etat;
    private $utilisateur_type;
    private $dateecheance;
    private $echeance_type;
    private $delai_cmd;
    private $facturid;
    private $devis=array();
    private $configs=array();
    private $comparateur=array();
    private $articles_vus=array();
    private $contactids=array();
    private $fournisseurs=array();
    private $rappel;
    private $rappel_type;
    private $rappel_commercial;
    private $rappel_edit=false;
    private $suivi_externe;
    
    private $etats;
    private $etatcolor=array();
    private $editfields=array();
    private $fields_names=array(
    "userid"=>"Client n°",
    "commercial"=>"commercial",
    "libelle"=>"libellé",
    "besoin"=>"besoin",
    "budget"=>"budget",
    "suivi_externe"=>"suivi externe",
    "etat"=>"état",
    "dateecheance"=>"date d'échéance",
    "echeance_type"=>"type d'échéance",
    "delai_cmd"=>"délai pour passer commande",
    "facturid"=>"commande n°",
    "contactids"=>"contacts n°",
    "devis"=>"devis liés",
    "fournisseurs"=>"fournisseurs liés",
    "comparateur"=>"comparateur",
    "articles_vus"=>"articles vus");
    
    function __construct($projetid=null, $userid=null){
        global $DB_site;
        global $DB_site2;
    
        if(isset($DB_site)) $this->DB=$DB_site;
        if(isset($DB_site2)) $this->DB=$DB_site2;
        
        $sql="SELECT * FROM projet_etat ORDER BY ordre";
        $req=$this->DB->query($sql);
        while($etat=$this->DB->fetch_array($req)){
            $this->etats[$etat['etatid']]=$etat['libelle'];
            $this->etatcolor[$etat['etatid']]=$etat['color'];
        }
        
        if(!is_null($projetid)){
            $sql="SELECT * FROM projet WHERE projetid=".intval($projetid);
            $data=$this->DB->query_first($sql);
            if($data!=""){
                $this->projetid=$data['projetid'];
                $this->userid=$data['userid'];
                $this->pjt="#PJT".$this->projetid."#";
                $this->date=new datetime();
                $this->date->setTimestamp($data['dateline']);
                $this->commercial=$data['commercial'];
                $this->libelle=$data['libelle'];
                $this->besoin=$data['besoin'];
                $this->budget=$data['budget'];
                $this->suivi_externe=$data['suivi_externe'];
                $this->etatid=$data['etatid'];
                $this->etat=$this->etats[$this->etatid];
                $this->budget_type=$data['projet_typeid'];
                $this->budget_devis=$data['budget_devis'];
                $this->utilisateur_type=$data['utilisateur_type'];
                $this->delai_cmd=$data['delai_cmd'];
                if($data['echeance_livraison']>0){
                    $this->dateecheance=new datetime();
                    $this->dateecheance->setTimestamp($data['echeance_livraison']);
                }
                $this->echeance_type=$data['echeance_type'];
                $this->facturid=$data['facturid'];
                if($data['devis']!="")
                    $this->devis=unserialize($data['devis']);
                if($data['contacts']!="")
                    $this->contactids=unserialize($data['contacts']);
                if($data['configs']!="")
                    $this->configs=unserialize($data['configs']);
                if($data['fournisseurs']!="")
                    $this->fournisseurs=unserialize($data['fournisseurs']);
                if($data['comparateur']!="")
                    $this->comparateur=unserialize($data['comparateur']);
                if($data['articles_vus']!="")
                    $this->articles_vus=unserialize($data['articles_vus']);
                if($data['rappel']>0){
                    $this->rappel=new datetime();
                    $this->rappel->setTimestamp($data['rappel']);
                    $this->rappel_commercial=$data['rappel_commercial'];
                    $this->rappel_type=$data['rappel_type'];
                }
                
                $this->loadrelations();
            }
        }elseif(!is_null($userid)){
            $this->userid = intval($userid);
        }
    }
    
    private function loadrelations(){
        $this->relationids=array();
        $sql="SELECT * FROM projet_relation WHERE projetid=".intval($this->projetid);
        $req=$this->DB->query($sql);
        while($data=$this->DB->fetch_array($req)){
            $this->relationids[]=$data['userid'];
        }
    }
 
    function users($type=null){
        $return=array();
        
        if(is_null($type) || $type=="client")
            $return[]=new utilisateur($this->DB,$this->userid);
    
        if(is_null($type) || $type=="relations"){
            foreach($this->relationids as $relationid){
                if($relationid!=$this->userid)
                    $return[]=new utilisateur($this->DB,$relationid);
            }
        }
        
        return $return;
    }
 
    function facture(){
        if($this->facturid!=0 && !is_null($this->facturid)){
            return new facture($this->DB,$this->facturid);
        }
        return null;
    }
    
    function budget_type_lib(){
        if($this->budget_type==0){
            if($this->budget>0){
                $sql="SELECT * FROM projet_type WHERE superieur<=".intval($this->budget)." ORDER BY superieur DESC LIMIT 1";
            }else{
                $sql="SELECT * FROM projet_type WHERE projet_typeid=0";
            }
        }else{
            $sql="SELECT * FROM projet_type WHERE projet_typeid=".intval($this->budget_type);
        }

        $type=$this->DB->query_first($sql);
        return $type['libelle'];
    }
    
    function rappels(){

        $return=array();
        if(!is_null($this->rappel))
            $return[]=array("origin"=>"commercial","ts"=>$this->rappel->gettimestamp(),"date"=>$this->rappel,"jour"=>0,"commercial"=>$this->rappel_commercial);
        
        $datecmd=$this->datecmd();
        if(!is_null($datecmd)){
            $return[]=array("origin"=>"delai_cmd","ts"=>$datecmd->gettimestamp(),"date"=>$datecmd,"jour"=>1,"commercial"=>"","user"=>"");
        }
        
        return $return;
    }
    
    function fichiers($parent=0){
        $return=array();
        $sql="SELECT * FROM projet_files WHERE projetid=".intval($this->projetid);
        if(!is_null($parent)) $sql.=" AND parent=".intval($parent);
        $req=$this->DB->query($sql);
        while($data=$this->DB->fetch_array($req)){
            $return[]=$data;
        }
        return $return;
    }
    
    function fichier_move($fid,$parent){
        $sql="UPDATE projet_files SET parent=".intval($parent)." WHERE id=".intval($fid);
        return $this->DB->query($sql);
    }
    
    function dossiers($parent=0){
        $return=array();
        $sql="SELECT * FROM projet_files WHERE projetid=".intval($this->projetid)." AND parent=".intval($parent)." AND ext=''";
        $req=$this->DB->query($sql);
        while($data=$this->DB->fetch_array($req)){
            $subfolders=$this->dossiers($data['id']);
            if($subfolders!=array()) $data['subfolders']=$subfolders;
            $return[]=$data;
        }
        return $return;
    }
    
    function save(){
        
        $sql="userid=".intval($this->userid).",
        commercial='".mysqli_real_escape_string($this->DB->link_id,$this->commercial)."',
        libelle='".mysqli_real_escape_string($this->DB->link_id,$this->libelle)."',
        besoin='".mysqli_real_escape_string($this->DB->link_id,$this->besoin)."',
        budget=".floatval($this->budget).",
        budget_devis=".intval($this->budget_devis).",
        suivi_externe=".floatval($this->suivi_externe).",
        etatid=".intval($this->etatid).",
        projet_typeid=".intval($this->budget_type).",
        echeance_livraison=".(!is_null($this->dateecheance)?$this->dateecheance->gettimestamp():0).",
        echeance_type=".intval($this->echeance_type).",
        delai_cmd=".intval($this->delai_cmd).",
        facturid=".intval($this->facturid).",
        devis='".serialize($this->devis)."',
        configs='".serialize($this->configs)."',
        comparateur='".serialize($this->comparateur)."',
        fournisseurs='".serialize($this->fournisseurs)."',
        articles_vus='".serialize($this->articles_vus)."',
        contacts='".serialize($this->contactids)."'";
        if(is_null($this->projetid)){
            $sql="INSERT INTO projet SET dateline=".time().", ".$sql;
        }else{
            $sql="UPDATE projet SET ".$sql." WHERE projetid=".$this->projetid;
        }
                
        $return=$this->DB->query($sql);
        if(is_null($this->projetid)){
            $this->projetid=$this->DB->insert_id();
            $suivi="[infos_projet] Création projet ".$this->projetid;
            $suivi.="<br><br>userid : ".$this->userid."
            <br>commercial : ".$this->commercial."
            <br>libellé : ".$this->libelle."
            <br>besoin : ".$this->besoin."
            <br>etat : ".$this->etat;
            if(!is_null($this->dateecheance))
                $suivi.="<br>date d'écheance : ".$this->dateecheance->format("d/m/Y");
            if($this->delai_cmd>0)
                $suivi.="<br>date pour passer commande : ".$this->delai_cmd;
            
            $this->suivi($suivi);

        }else{
            if(count($this->editfields)>0){
                $suivi="[infos_projet] Modification projet ".$this->projetid;
                foreach($this->editfields as $var=>$oldvalue){
                    if(is_a($oldvalue,"datetime"))
                        $oldvalue=$oldvalue->format("d/m/Y H:i");
                    elseif(is_array($oldvalue))
                        $oldvalue=implode(", ",$oldvalue);
                    elseif(is_null($oldvalue))
                        $oldvalue="inconnu";
                        
                    if(is_a($this->$var,"datetime"))
                        $newvalue=$this->$var->format("d/m/Y H:i");
                    elseif(is_array($this->$var))
                        $newvalue=implode(", ",$this->$var);
                    elseif(is_null($this->$var))
                        $newvalue="inconnu";
                    else
                        $newvalue=$this->$var;

                    ///Affiche le type d'échéance dans le suivi
                    if($var == "echeance_type"){
                        if($oldvalue == "1"){
                            $oldvalue = "info du client";
                            $newvalue = "estimé par MCAV";
                        }
                        elseif($oldvalue == "0"){
                            $newvalue = "info du client";
                            $oldvalue = "estimé par MCAV";
                        }
                    }

                    ///Affiche la cause si état perdu dans le suivi
                    if($var == "etat" && $newvalue == 'Perdu'){
                        $cause = $this->DB->query_first("SELECT cause FROM causeperte WHERE commentaire ='PJT".$this->projetid."' ORDER BY id DESC LIMIT 1");
                        switch($cause['cause']){
                            case 10:
                            $causeperte = "Achat ailleurs";
                            break;
                            case 2:
                            $causeperte = "Projet abandonné";
                            break;
                            case 7:
                            case 11:
                            $causeperte = "Client injoignable / Faux numéro / Faux email";
                            break;
                            case 12:
                            case 13:
                            $causeperte = "Petit dossier / Sans conseil / Sans suivi / Projet décliné par MCAV / Répondu trop tard";
                            break;
                            case 8:
                            $causeperte = "Double projet / Double compte / Compte interne";
                            break;
                        }
                        $newvalue = $newvalue . ". Cause de la perte : ".$causeperte;
                    }
                    
                    
                    $suivi.="<br>".$this->fields_names[$var]." : ".$oldvalue." =&gt; ".$newvalue;
                }
                $this->suivi($suivi);
            }
        }
        
        
        return $return;
    }
    
    function addrelation($userid){
        if($userid!=$this->userid && !in_array($userid,$this->relationids)){
            $sql="INSERT INTO projet_relation SET projetid=".$this->projetid.", userid=".intval($userid).", dateline=".time();
            $return=$this->DB->query($sql);
            $this->loadrelations();
            $suivi="[infos_projet] Ajout relation ".$userid;
            $this->suivi($suivi);
            return $return;
        }
        return false;
    }
    
    function rmrelation($userid){
        if(in_array($userid,$this->relationids)){
            $sql="DELETE FROM projet_relation WHERE userid=".intval($userid)." AND projetid=".intval($this->projetid);
            $return=$this->DB->query($sql);
            $this->loadrelations();
            $suivi="[infos_projet] Suppression relation ".$userid;
            $this->suivi($suivi);
            return $return;
        }
        return false;
    }
    
    function swaprelation($userid){
        if(in_array($userid,$this->relationids)){
            $this->rmrelation($userid);
            $olduserid=$this->userid;
            $this->userid=$userid;
            $this->addrelation($olduserid);
            $return=$this->save();
            $this->loadrelations();
            return $return;
        }
        return false;
    }
    
    function datecmd(){
        if(!is_null($this->dateecheance)){
            $datecmd=clone $this->dateecheance;
            $datecmd->modify("-".$this->delai_cmd." days");
            $datecmd->modify("-5 days");
            switch($datecmd->format("w")){
                case 6:
                $datecmd->modify("-1 day");
                break;
                
                case 0:
                $datecmd->modify("-2 days");
                break;
            }
            return $datecmd;
        }else
            return null;
    }
    
    function fournisseurs(){
        $return=array();
        if(is_null($this->projetid)) return array();
        $sql="SELECT destinataire FROM suivi_client WHERE projetid=".intval($this->projetid)." AND type='pro' AND destinataire>0";
        $req=$this->DB->query($sql);
        while($suivi=$this->DB->fetch_array($req))
            $return[]=$suivi['destinataire'];

        foreach(array("mail_inbox","mail_outbox") as $table){
            $sql="SELECT f.id FROM fournisseur f INNER JOIN ".$table." m ON (m.from=f.mail or m.to=f.mail) WHERE m.from!='' AND m.fournisseur!='' AND m.projetid=".intval($this->projetid);
            $req=$this->DB->query($sql);
            while($suivi=$this->DB->fetch_array($req)){
                $return[]=$suivi['id'];
            }
        }
        
        return array_unique($return);
    }
    
    function suivi($txt){
        global $user_info;
        $sql="INSERT INTO suivi_client SET commercial='".$user_info['username']."', date=".time().", suivi='".mysqli_real_escape_string($this->DB->link_id,$txt)."', projetid=".$this->projetid;
        return $this->DB->query($sql);
    }
    
    function rappel($date,$user,$type=null){
        if(is_a($date,"datetime")){
            $this->rappel=$date;
            $ts=$date->gettimestamp();
        }else{
            if($date==0){
                $this->rappel=null;
                $user="";
                $ts=0;
            }else{
                $ts=intval($date);
                $this->rappel=new datetime();
                $this->rappel->settimestamp($ts);
            }
        }

        $this->rappel_commercial=$user;
        $this->rappel_edit=true;
        return $this->DB->query("UPDATE projet SET rappel=".$ts.", rappel_commercial='".mysqli_real_escape_string($this->DB->link_id,$this->rappel_commercial)."', rappel_type = '".$type."' WHERE projetid=".$this->projetid);
    }
    
    function propositions(){
        $return=array();
        $sql="SELECT * FROM projet_prop WHERE projetid=".intval($this->projetid);
        $req=$this->DB->query($sql);
        while($prop=$this->DB->fetch_array($req)){
            $return[]=$prop;
        }
        return $return;
    }
    
    function addprop($artid,$qte,$caract){
        global $user_info;
        
        if(is_array($caract)) $caract=implode(",",$caract);
        $sql="INSERT INTO projet_prop SET projetid=".$this->projetid.", artid=".intval($artid).", qte=".intval($qte).", caractvalid='".mysqli_real_escape_string($this->DB->link_id,$caract)."', date=".time().", utilisateur='".$user_info['username']."'";
        return $this->DB->query($sql);
    }
    
    function rmprop($artid){
        $sql="DELETE FROM projet_prop WHERE projetid=".$this->projetid." AND artid=".intval($artid);
        return $this->DB->query($sql);
    }
    
    function addconfig($configid){
        $this->configs[]=$configid;
        $this->editfields['config']=$this->configs;
        $sql="SELECT configs FROM projet WHERE configs LIKE '%\"".$configid."\"%' AND projetid=".$this->projetid;
        $config=$this->DB->query($sql);
        if($this->DB->num_rows($config) > 0) {
            return;
        }
        else {
            return $this->save();
        }
    }

    function rmconfig($configid){
        if (($key = array_search($configid, $this->configs)) !== false) {
            unset($this->configs[$key]);
            return $this->save();
        }
        return false;
    }

    function adddevis($facturid){
        $this->devis[]=$facturid;
        $this->editfields['devis']=$this->devis;
        //Si dans le projet du client, c'est coché "budget automatique dernier devis", on enregistre le montant de ce devis dans le budget :
        if($this->budget_devis == 1){
            $facture=new facture($this->DB,$facturid);
            $this->budget = $facture->totalht;
        }
        return $this->save();
    }
    
    function rmdevis($facturid){
        if (($key = array_search($facturid, $this->devis)) !== false) {
            unset($this->devis[$key]);
            return $this->save();
        }
        return false;
    }

    function addcompare($artid){
        $this->editfields['comparateur']=$this->comparateur;
        $this->comparateur[]=$artid;
        $sql="SELECT comparateur FROM projet WHERE comparateur LIKE '%\"".$artid."\"%' AND projetid=".$this->projetid;
        $comparateur=$this->DB->query($sql);
        if($this->DB->num_rows($comparateur) > 0) {
            return;
        }
        else {
            return $this->save();
        }
    }

    function rmcompare($artid){
        if (($key = array_search($artid, $this->comparateur)) !== false) {
            unset($this->comparateur[$key]);
            return $this->save();
        }
        return false;
    }

    function rmrelationfourn($fournid){
            $sql="DELETE FROM projet_relation_fournisseur WHERE relationid=".intval($fournid)." AND projetid=".intval($this->projetid);
            $return=$this->DB->query($sql);
            $this->loadrelations();
            $suivi="[infos_projet] Suppression relation fournisseur ".$fournid;
            $this->suivi($suivi);
            return $return;
    }

    function addartvu($artid){
        $this->editfields['articles_vus']=$this->articles_vus;
        $this->articles_vus[]=$artid;
        $sql="SELECT articles_vus FROM projet WHERE articles_vus LIKE '%\"".$artid."\"%' AND projetid=".$this->projetid;
        $articles_vus=$this->DB->query($sql);
        if($this->DB->num_rows($articles_vus) > 0) {
            return;
        }
        else {
            return $this->save();
        }
    }

    function rmartvu($artid){
        if (($key = array_search($artid, $this->articles_vus)) !== false) {
            unset($this->articles_vus[$key]);
            return $this->save();
        }
        return false;
    }

    function configs(){
        $return=array();
        $arr_config=array_unique($this->configs);
        arsort($arr_config);
        foreach($arr_config as $config){
            $return[]=$config;
        }
        return $return;
    }

    function comparateur(){
        $return=array();
        $arr_compar=array_unique($this->comparateur);
        arsort($arr_compar);
        foreach($arr_compar as $art){
            $return[]=$art;
        }
        return $return;
    }

    function articles_vus(){
        $return=array();
        $arr_artvu=array_unique($this->articles_vus);
        arsort($arr_artvu);
        foreach($arr_artvu as $art){
            $return[]=$art;
        }
        return $return;
    }
    
    function devis(){
        $return=array();
        arsort($this->devis);
        foreach($this->devis as $facturid){
            if($facturid==$this->facturid) continue;
            $return[]=new facture($this->DB,$facturid);
        }
        return $return;
    }
    
    function __get($var){
        return isset($this->$var) ? $this->$var : null;
    }
    
    function __set($var,$val){
    
        switch($var){
            case "projetid":
            case "userid":
            case "etatid":
            case "facturid":
            case "budget_type":
            case "budget_devis":
            case "delai_cmd":
            if(intval($val)!=$this->$var){
                if($var=="etatid"){
                    $this->editfields['etat']=$this->etat;
                    $this->etat=$this->etats[$val];
                }else
                    $this->editfields[$var]=$this->$var;
            }

            $this->$var=intval($val);
            break;
            
            case "commercial":
            case "libelle":
            case "besoin":
            case "budget":
            case "utilisateur_type":
            case "echeance_type":
            case "suivi_externe":
            if($val!=$this->$var)
                $this->editfields[$var]=$this->$var;
            $this->$var=$val;
            break;

            case "comparateur":
            case "articles_vus":
            if(is_array($val)){
                if(!in_array($val,$this->$var)){
                    $this->editfields[$var]=$this->$var;
                    $this->$var=$val;
                }
            }
            else{
                $this->$var=array();
            }
            break;
            
            case "contactids":
            case "fournisseurs":
            if(is_array($val)){
                if(!in_array($val,$this->$var)){
                    $this->editfields[$var]=$this->$var;
                    $this->$var=array();
                    $this->$var=$val;
                }
            }
            else{
                $this->$var=array();
            }
            break;
            
            case "dateecheance":
            $olddate=$this->$var;
            if(is_int($var)){
                $this->$var=new datetime();
                $this->$var->setTimestamp($val);
            }else{
                $this->$var=$val;
            }
            if($this->$var!=$olddate)
                $this->editfields[$var]=$olddate;
            break;
        }
        
        if($var=="budget" && $var<0) $this->budget=0;

            
    }
/**
 * Si le projet est classé en perdu
 * la fonction le reactive en mettant 
 * sur etat à chiffrer
 * utiliser dans rcvmail.php
 */
    function reactive(){
        if($this->etatid == 6){
            $this->etatid = 3;
            $this->save();
        }
    }
}
