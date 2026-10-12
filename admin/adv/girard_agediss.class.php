<?php
require_once __DIR__.'/../../secrets.php';

class girard_agediss {

    private $chargeur=11112;
    private $DB;
    private $adv;
    private $error;
    private $facturid;
    private $origin;
    private $valeurdeclaree;
    private $date_imperative;
    private $infotrspt;
    private $pjid;
    
    private $ftp_host="ftp.girard-agediss.com";
    private $ftp_login=GIRARD_FTP_LOGIN;
    private $ftp_pass=GIRARD_FTP_PASSWORD;
    
    private $situations=array(
        "INI"=>"intégration EDI",
        "MLR"=>"réception hub",
        "MLV"=>"départ hub",
        "RDV"=>"rdv client",
        "AAR"=>"arrivage plateforme distribution",
        "CHG"=>"départ plateforme distribution",
        "LIV"=>"livraison",
        "REN"=>"problème livraison",
        "RST"=>"contact client",
        "POD"=>"numérisation BL",
        "DIF"=>"différé"
    );
    
    private $justifications=array(
        "INF"=>"ok",
        "RCA"=>"refusée avarie",
        "MQP"=>"partielle",
        "AVA"=>"avec réserves",
        "CFM"=>"conforme",
        "MQT"=>"non chargé",
        "NCF"=>"non conforme",
        "DIA"=>"destinataire inconnu",
        "INC"=>"inaccessible",
        "DAF"=>"e-mail",
        "SRP"=>"sans réponse",
        "REP"=>"message répondeur",
        "TEL"=>"téléphone",
        "CCL"=>"confirmation créneaux livraison",
        "ANN"=>"annulation",
        "PRI"=>"pris",
        "CAD"=>"problème adresse/téléphon",
        "CLT"=>"par le client"
    );
    
    function __construct($DB,$adv=null){
        $this->DB=$DB;
        $this->adv=$adv;
        
        if(!is_null($adv)){
            $this->facturid=$this->adv->facturid;
            $this->time=0;
            
            $facture=new facture($this->DB,$this->facturid);
            if($facture->facturid==$this->facturid){
                $this->from_facture($facture);
                $this->origin="cmd";
            }
            
            $this->colis=$this->adv->colis();
            if($this->colis==array()) $this->colis[1]=$this->adv->lignes;
            foreach($this->colis as $colisid=>$lines){
                foreach($lines as $lid=>$qte)
                    $this->poidscolis[$colisid]+=$qte*$this->poidsu[$lid];
            }
            $this->valeurdeclaree=isset($this->adv->fields['slscoutassur']) && $this->adv->fields['slscoutassur']==1 ? $this->adv->fields['valeurtotalsls'] : 0;
            
            $this->infotrspt=$this->adv->fields['infocomplement'];
            
            $this->date_livraison=datetime::createFromFormat("j/n/Y",$this->adv->fields["slsmaxdate"]);
            
            $this->livrtype="LV2";
            if(isset($this->adv->fields['typetrspt'])){
                $oFournisseur = new Fournisseur($this->DB);
                $aDetailsLivraison = $oFournisseur->getDetailsTypeLivraison(intval($this->adv->fields['typetrspt']));
                $this->livrtype=substr($aDetailsLivraison['livr_type_nom'],0,3);
            }
            
            $this->prestation="";
            switch($this->livrtype){
                case "LV2":
                $this->prestation.="LIV";
                break;
                
                case "LV4":
                $this->prestation.="L4S";
                break;
            }
            
            $this->prestation.="B"; // aucune prestation
            if($this->adv->fields['slsobj']=="install") //installation
                $this->prestation.="C";
            else
                $this->prestation.="B"; 
            switch($this->adv->fields['recup']){ // recupération
                case "1":
                $this->prestation.="B";
                break;
                
                case "2":
                $this->prestation.="C";
                break;
                
                default:
                $this->prestation.="A";
            }
            $this->prestation.="0"; //administratif
            $this->prestation.="0"; //spécifique
            $this->prestation.="0"; //réservé
        }
        
        $this->plan();
    }

    function from_facture($facture){
        $this->userid=$facture->userid;
	
        $this->raison_sociale=$facture->raisonsociale;
        $this->nom=$facture->nom;
        $this->prenom=$facture->prenom;
        $this->adresse=$facture->adresse;
        $this->codepostal=$facture->codepostal;
        $this->ville=$facture->ville;
        $this->pays=$facture->diminutif;
        $this->paysid=$facture->paysid;
        $this->commentaires=$facture->infotranspo;
        $this->telephone=$facture->telephone;
        if($this->telephone=="") $this->telephone=$facture->utilisateur->telephone;
        if($this->telephone=="") $this->telephone=$facture->utilisateur->portable;
        $this->email=$facture->mail;

        $this->telephone=preg_replace("/[^\d\+]/","",$this->telephone);
	
        $this->poids=0;
        $this->poidsu=array();
        $this->volume=0;
        $this->libelle=array();
        $lignefacturedissid=array();
        foreach($this->adv->lignes as $lid=>$qte){
            $lignefacture=$this->DB->query_first("SELECT a.*,l.lignefacturedissid FROM lignefacture l, article a WHERE l.artid=a.artid AND lignefactureid=".$lid);
            $this->poids+=$qte*$lignefacture['emballage_poids'];
            $this->poidsu[$lid]=$lignefacture['emballage_poids'];
            $this->volume+=$qte*($lignefacture['emballage_hauteur']*$lignefacture['emballage_largeur']*$lignefacture['emballage_profondeur'])/pow(10,9);
            $this->libelle[$lid]=html_entity_decode($lignefacture['libelleFR']);
            $this->artcode[$lid]=$lignefacture['artcode'];
            if($this->adv->fields['slsobj']=="install"){
                if($lignefacture['lignefacturedissid']==0)
                    $this->time+=60*$qte*$lignefacture['montage_heures'];
                else
                    $lignefacturedissid[]=$lignefacture['lignefacturedissid'];
            }
        }
        
        if($lignefacturedissid!=array()){
            $req=$this->DB->query("SELECT a.*,lignefacturediss.qte FROM lignefacturediss INNER JOIN article a USING(artid) WHERE lignefactureid IN (".implode(",",array_unique($lignefacturedissid)).")");
            while($lignefacture=$this->DB->fetch_array($req)){
                $this->time+=60*$lignefacture['qte']*$lignefacture['montage_heures'];
            }
        }
    }
    
    function plan(){
        $ptfile=__DIR__."/PTgirardagediss.csv";
        if(!file_exists($ptfile)) return false;
        $f=fopen($ptfile,"r");
        $cp=str_replace(" ","",$this->codepostal);
        
        while(!feof($f)){
            list($pays,$cp,$codehub,$codepf,$gasociete,$gaadresse,$gacp,$gaville,$gapays,$gatel,$activite)=fgetcsv($f,1024,";");
            if($pays==$this->pays && ($this->pays!="FR" || $cp==ltrim(substr($this->codepostal,0,2),"0") || $cp==$this->codepostal)){
                $this->codepf=$codepf;
                $this->ga_societe=$gasociete;
                $this->ga_adresse=$gaadresse;
                $this->ga_cp=$gacp;
                $this->ga_ville=$gaville;
                $this->ga_pays=$gapays;
                $this->ga_tel=$gatel;
                break;
            }
        }
    }
    
    function trackid(){
        return $this->adv->id;
    }
    
    function encode(){
        $lines=array();
        
        $lines["ES1E01"]=array(
            "ES1E01",
            str_pad($this->trackid(),35),
            date("ymd"),
            $this->date_livraison->format("ymd"),
            str_pad(count($this->colis),6,"0",STR_PAD_LEFT),
            str_pad(round($this->poids,2),15,"0",STR_PAD_LEFT),
            str_repeat(" ",15),
            str_pad($this->nom,35),
            str_pad($this->prenom,35),
            str_pad($this->raison_sociale,35),
            str_pad($this->adresse,35),
            str_repeat(" ",35),
            str_repeat(" ",35),
            str_pad($this->codepostal,9),
            str_pad($this->ville,35),
            str_pad($this->pays,3),
            "000",
            str_repeat(" ",10),
            "000",
            str_repeat(" ",17),
            str_pad($this->facturid,35),
            str_repeat(" ",15),
            str_pad(round($this->adv->fields['sls_ctrremb_montant'],2),15,"0",STR_PAD_LEFT),
            str_pad(round($this->valeurdeclaree,2),15,"0",STR_PAD_LEFT),
            str_pad(substr($this->infotrspt,0,125),125),
            str_pad(substr(utf8_decode($this->commentaires),0,125),125),
            str_pad($this->chargeur,6),
            str_pad($this->telephone,15),
            str_repeat(" ",15),
            str_repeat(" ",15),
            str_pad($this->email,150),
            str_repeat(" ",8),
            str_pad(round($this->volume,3),15,"0",STR_PAD_LEFT),
            str_repeat(" ",15),
            str_pad($this->time,5,"0",STR_PAD_LEFT),
            str_pad($this->prestation,25),
        );
        
        $lines["ES1M01"]=array(
            "ES1M01",
            str_repeat(" ",17),
            str_repeat(" ",25),
            str_pad(substr(implode(",",$this->libelle),0,70),70),
            str_pad(1,6,"0",STR_PAD_LEFT),
            str_pad(count($this->colis),6,"0",STR_PAD_LEFT),
            str_pad(round($this->poids,2),15,"0",STR_PAD_LEFT),
            str_pad(round($this->volume,3),15,"0",STR_PAD_LEFT),
            str_repeat(" ",15),
            str_repeat(" ",3),
            str_repeat(" ",35),
            str_repeat(" ",25),
            str_repeat(" ",35),
            str_pad("E",5)
        );
        
        foreach($this->colis as $colisid=>$clines){
            $h=isset($this->adv->fields['colis_h_'.$colisid]) ? $this->adv->fields['colis_h_'.$colisid] : 0;
            $l=isset($this->adv->fields['colis_l_'.$colisid]) ? $this->adv->fields['colis_l_'.$colisid] : 0;
            $p=isset($this->adv->fields['colis_p_'.$colisid]) ? $this->adv->fields['colis_p_'.$colisid] : 0;
            $v=$h*$l*$p/pow(10,9);
            foreach($clines as $lid=>$qte){
                $libelle=substr($this->libelle[$lid],0,70);
                break;
            }
        
            $lines["ES1C02".$colisid]=array(
                "ES1C02",
                str_pad($this->barcode($colisid),35),
                str_pad(round($this->poidscolis[$colisid],3),15,"0",STR_PAD_LEFT),
                str_repeat(" ",70),
                str_pad(round($v,3),15,"0",STR_PAD_LEFT),
                str_pad(round($h,2),15,"0",STR_PAD_LEFT),
                str_pad(round($l,2),15,"0",STR_PAD_LEFT),
                str_pad(round($p,2),15,"0",STR_PAD_LEFT),
                str_pad($libelle,70),
                str_repeat(" ",30)
            );
        }
        
        $return="";
        foreach($lines as $line){
            $return.=implode("~",$line)."\r\n";
        }
        
        return $return;
    }
    
    function barcode($colisid){
        return str_pad($this->codepf,4,"0",STR_PAD_LEFT)
        .$this->chargeur
        .str_pad($this->adv->id,16,"0",STR_PAD_LEFT)
        .str_pad($colisid,3,"0",STR_PAD_LEFT);
    }
    
    function etiquette($pj=false, $parent=0){
        $this->pdf=new fpdf();
        $this->pdf->setautopagebreak(false);
        
        foreach($this->colis as $colisid=>$lines){
            $this->page($colisid);
        }
        
        if($pj){
                $file="/tmp/".$this->adv->id.".pdf";
            $this->pdf->output($file,"F");
            $pdfsize=filesize($file);
            if($this->origin=="cmd"){
                    $this->DB->query("INSERT INTO user_files SET userid=".$this->userid.", facturid=".$this->facturid.", parent=$parent, date=".time().", utilisateur='".$this->adv->utilisateur."', name='Etiquette AGEDISS ".date("Ymd-Hi").".pdf', size=".$pdfsize.", ext='pdf', state=1");
                    $this->pjid=$this->DB->insert_id();
                    rename($file,__DIR__."/../userfiles/".$this->pjid.".pdf");
                }
                if($this->origin=="stock"){
                    $this->DB->query("INSERT INTO fournisseur_files SET fournisseur='".mysqli_real_escape_string($this->DB->link_id,$this->adv->destinataire)."', stock_cmd_id=".$this->facturid.", date=".time().", utilisateur='".$this->adv->utilisateur."', name='Etiquette AGEDISS ".date("Ymd-Hi").".pdf', size=".$pdfsize.", ext='pdf', state=1");
                    $this->pjid=$this->DB->insert_id();
                    rename($file,__DIR__."/../fournfiles/".$this->pjid.".pdf");
                }
        }else
            $this->pdf->output();
    }
    
    function page($colisid){
        $this->pdf->setfillcolor(0);
        $this->pdf->settextcolor(0);
	
        if($colisid%4==1) $this->pdf->addpage("P","A4");
        $this->pdf->setfont('arial','',8);
        $pos=array(10,10);
        if($colisid%2==0) $pos[0]+=100;
        if(ceil($colisid/2)%2==0) $pos[1]+=125;
        
        $this->pdf->line($pos[0],$pos[1],$pos[0]+90,$pos[1]);
        $this->pdf->line($pos[0],$pos[1]+27,$pos[0]+90,$pos[1]+27);
        $this->pdf->line($pos[0],$pos[1]+48,$pos[0]+90,$pos[1]+48);
        $this->pdf->line($pos[0],$pos[1]+81,$pos[0]+90,$pos[1]+81);
        $this->pdf->line($pos[0],$pos[1],$pos[0],$pos[1]+110);
        $this->pdf->line($pos[0]+45,$pos[1],$pos[0]+45,$pos[1]+81);
        $this->pdf->line($pos[0]+75,$pos[1],$pos[0]+75,$pos[1]+27);
        
        $this->pdf->setfont("arial","UI",7);
        $this->pdf->setXY($pos[0]+1,$pos[1]+1);
        $this->pdf->cell(20,4,"EXPEDITEUR");
        
        $this->pdf->setfont("arial","I",7);
        $this->pdf->setXY($pos[0]+46,$pos[1]+1);
        $this->pdf->multicell(30,3,"LIVRAISON PLATEFORME");
        $this->pdf->setXY($pos[0]+76,$pos[1]+1);
        $this->pdf->cell(20,4,"ACTIVITE");
        $this->pdf->setXY($pos[0]+46,$pos[1]+28);
        $this->pdf->cell(20,4,"DESTINATAIRE FINAL");
        $this->pdf->setXY($pos[0]+1,$pos[1]+28);
        $this->pdf->multicell(20,5,utf8_decode("DATE :\nARTICLE :\nDésignation :"));
        $this->pdf->setXY($pos[0]+1,$pos[1]+49);
        $this->pdf->multicell(45,5,utf8_decode("N° BL :\nN° COMMANDE :"));
        $this->pdf->setXY($pos[0],$pos[1]+62);
        $this->pdf->cell(45,5,utf8_decode("N° de colis / Nombre total colis"),0,0,"C");
        
        $this->pdf->setfont("arial","B",8);
        $this->pdf->setXY($pos[0]+1,$pos[1]+10);
        $this->pdf->cell(25,4,"ACILYON");
        $this->pdf->setfont("arial","B",7);
        $this->pdf->setXY($pos[0]+45,$pos[1]+8);
        $this->pdf->multicell(30,3,$this->ga_societe."\n".$this->ga_adresse."\n".$this->ga_pays." ".$this->ga_cp." ".$this->ga_ville,0,'L');
        $this->pdf->setfont("arial","B",12);
        $this->pdf->setXY($pos[0]+76,$pos[1]+10);
        $this->pdf->cell(25,4,$this->livrtype);
        
        $this->pdf->setfont("arial","B",7);
        $this->pdf->setXY($pos[0]+20,$pos[1]+28);
        $this->pdf->multicell(25,5,date("d/m/Y")."\n");
        
        $this->pdf->setfont("arial","",7);
        foreach($this->colis[$colisid] as $lid=>$qte){
            $this->pdf->setXY($pos[0]+20,$pos[1]+34);
            $this->pdf->cell(45,4,$this->artcode[$lid]);
            $this->pdf->setXY($pos[0],$pos[1]+42);
            $this->pdf->multicell(45,3,substr(utf8_decode(html_entity_decode($this->libelle[$lid])),0,60),0,'L');
            break;
        }
    
        $this->pdf->setfont("arial","B",8);
        $this->pdf->setXY($pos[0]+46,$pos[1]+32);
        $this->pdf->multicell(45,3,trim(utf8_decode($this->raison_sociale."\n".$this->prenom." ".$this->nom."\n".$this->adresse."\n".$this->pays." ".$this->codepostal." ".$this->ville)),0,"L");
        
        $this->pdf->setXY($pos[0]+22,$pos[1]+49);
        $this->pdf->multicell(20,5,$this->adv->id."\n".$this->facturid);
        
        $this->pdf->setfont("arial","B",16);
        $this->pdf->setXY($pos[0],$pos[1]+68);
        $this->pdf->cell(45,5,$colisid." / ".count($this->colis),0,0,"C");
        
        $this->pdf->setfont("arial","B",24);
        $this->pdf->setXY($pos[0]+45,$pos[1]+49);
        $this->pdf->cell(45,27,$this->pays." ".str_pad($this->codepf,3,"0",STR_PAD_LEFT),0,0,"C");
        
        $barcode=$this->barcode($colisid);
        barcode::fpdf($this->pdf,"#000000",$pos[0]+45,$pos[1]+90,0,"code128",$barcode,0.3,13);
        $this->pdf->setfont("arial","",8);
        $this->pdf->setXY($pos[0],$pos[1]+100);
        $this->pdf->cell(90,4,$barcode,0,0,"C");
    }
    
    function send(){
        $path=__DIR__."/girard_agediss/out/".date("YmdHis")."-".$this->facturid;
        file_put_contents($path,$this->encode());
        $conn=ftp_connect($this->ftp_host);
        if($conn){
            if(ftp_login($conn,$this->ftp_login,$this->ftp_pass)){
                $return=ftp_put($conn,"emis/GA_ES1_".date("YmdHis"),$path,FTP_ASCII);
                ftp_close($conn);
                if(!$return)
                    $this->error="Could not send file";
                return $return;
            }else{
                ftp_close($conn);
                $this->error="Could not login";
                return false;
            }
        }else{
            $this->error="Could not connect to ftp";
            return false;
        }
    }
    
    function pjid(){
        return $this->pjid;
    }
    
    function error(){
        return $this->error==""?false:$this->error;
    }
    
    function suivis(){
        $return=array();
        $conn=ftp_connect($this->ftp_host);
        if($conn){
            if(ftp_login($conn,$this->ftp_login,$this->ftp_pass)){
                ftp_chdir($conn,"recu");
                $contents = ftp_nlist($conn, "."); 
                foreach ($contents as $file) {
                    if ($file == '.' || $file == '..') 
                        continue;
                        
                    if(pathinfo($file,PATHINFO_EXTENSION)=="csv"){
                        ftp_get($conn,__DIR__."/PTgirardagediss.csv", $file, FTP_BINARY);
                        continue;
                    }
                    
                    $localfile= __DIR__."/girard_agediss/in/".$file;
                    ftp_get($conn,$localfile, $file, FTP_BINARY);
                    ftp_delete($conn,$file);
                    
                    $f=fopen($localfile,"r");
                    while(!feof($f)){
                        $line=fgetcsv($f,0,"|");
                        if(count($line)<12) continue;
                        
                        $trackid=trim($line[1]);
                        if($trackid!=""){
                            $facture=$this->DB->query_first("SELECT facturid FROM envoi_fournisseur WHERE trackid='".mysqli_real_escape_string($this->DB->link_id,$trackid)."'");
                            $deliverydate=DateTime::CreateFromFormat("Y/m/d",$line[8]);
                            $deliverydate->settime(0,0,0);
                            list($situation,$justification)=explode(" ",$line[6]);
                            
                            switch($situation){
                                case "LIV":
                                    $this->DB->query("UPDATE envoi_fournisseur SET livraison=".$deliverydate->getTimestamp()." WHERE trackid='".mysqli_real_escape_string($this->DB->link_id,$trackid)."'");
                                break;
                                
                                case "PCH":
                                    $this->DB->query("UPDATE envoi_fournisseur SET enlevement=".$deliverydate->getTimestamp()." WHERE trackid='".mysqli_real_escape_string($this->DB->link_id,$trackid)."'");
                                break;
                            }

                            $suivi="Colis AGEDISS ".$this->situations[$situation]." ".$this->justifications[$justification]." ".$deliverydate->format("d/m/Y");
                            $this->DB->query("INSERT INTO suivi_commande SET facturid='".$facture['facturid']."', date=".time().", suivi='".mysqli_real_escape_string($this->DB->link_id,$suivi)."'");
                            $return[]=$suivi;
                        }
                    }
                }
                
            }else{
                $this->error="Could not login";
                $return=false;
            }
        }else{
            $this->error="Could not connect to ftp";
            $return=false;
        }
        ftp_close($conn);
        
        return $return;
    }
}

?>
