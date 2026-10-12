<?php
class heppner {
    
    private $agences=array(
        "40"=>array("Lyon","211016","95751445800133","0472234071"),
        "71"=>array("Le Mans","211014","76980020200160","0243244242")
    );
    
    private $chargeurs=array(
        "72230"=>"71", //Si lieu enlevement FRIO alors forcement heppner Le Mans
        "69800"=>"40", //Si lieu enlevement GINKO alors forcement heppner Lyon
        "69003"=>"40"  //Si lieu enlevement Avilyon alors forcement heppner Lyon
    );

    private $codes_chargeurs=array("69003"=>"941536","69800"=>"723778");
    
    private $mode="PP";
    private $regime=3;
    private $siret="45294738500038"; //siret ACILYON
    private $code_chargeur;
    
    private $DB;
    private $adv;
    private $host;
    private $error;
    private $facturid;
    private $typetrspt;
    private $origin;
    private $agence;
    private $travee;
    private $routage;
    private $code_direction;
    private $valeurdeclaree;
    private $date_imperative;
    private $infotrspt;
    private $type_etiquette;
    private $pjid;
    private $ftp;
    
    private $situations=array(
        "AAR"=>"arrivage",
        "EXP"=>"expédition",
        "LIV"=>"livraison",
        "PAQ"=>"P.A.Q.",
        "PCH"=>"prise en charge",
        "REN"=>"refusé"
    );
    
    private $justifications=array(
        "ATL"=>"attente trop longue",
        "AVA"=>"avarie",
        "AVI"=>"destinataire avisé",
        "CAD"=>"adresse incomplète",
        "CFM"=>"conforme",
        "DAF"=>"destinataire fermé ou absent",
        "DEM"=>"destinataire déménagé",
        "DIA"=>"destinataire inconnu",
        "DIV"=>"réserves diverses",
        "DOC"=>"document manquant",
        "FCO"=>"fermé pour congés ou inventaire",
        "FHB"=>"fermeture hebdomadaire",
        "LNA"=>"complément d'adresse",
        "MQP"=>"manquant partiel",
        "MQT"=>"manquant total",
        "NJU"=>"non justifié",
        "NRV"=>"nouveau rendez-vous",
        "OUV"=>"colis ouvert",
        "PCO"=>"porte codée",
        "PVE"=>"poids visiblement erroné",
        "RCA"=>"réserves pour avarie",
        "RMA"=>"réserves pour avaries et manquants",
        "RSY"=>"réserves systématiques",
        "SDI"=>"souffrance diverse",
        "SNC"=>"non commandé",
        "SPD"=>"port dû",
        "SPL"=>"souffrance partielle",
        "SPO"=>"réserves spoliation",
        "SRB"=>"contre remboursement",
        "TAR"=>"retard",
        "VVE"=>"volume visiblement erroné"
    );

    function __construct($DB,$adv=null,$ftp=null,$host=null){
        $this->DB=$DB;
        $this->adv=$adv;
        if(!is_null($ftp)) $this->ftp = $ftp;

        if(!is_null($host)){ 
            $this->host = $host;
        }else{
            $this->error = "Host manquant";
        }

        if(!is_null($adv)){
            $this->facturid=$this->adv->facturid;
            
            $facture=new facture($this->DB,$this->facturid);
            if($facture->facturid==$this->facturid){
                $this->from_facture($facture);
                $this->origin="cmd";
            }else{
                $stock_cmd=new stock_cmd($this->DB,$this->facturid);
                if($stock_cmd->id==$this->facturid){
                    $this->from_stock($stock_cmd);
                    $this->origin="stock";
                }
            }

            $this->exp_adresse2="";
            if(strlen($this->exp_adresse)>35){
                $this->exp_adresse2=substr($this->exp_adresse,35);
                $this->exp_adresse=substr($this->exp_adresse,0,35);
            }
            
            $this->colis=$this->adv->colis();
            if($this->colis==array()) $this->colis[1]=$this->adv->lignes;
            foreach($this->colis as $colisid=>$lines){
                foreach($lines as $lid=>$qte)
                    $this->poidscolis[$colisid]+=$qte*$this->poidsu[$lid];
            }
            $this->valeurdeclaree=$this->adv->fields['valeurdeclaree'];
            
            if($this->adv->fields['trsptdatemax']=="trsptdateauto"){
                $this->date_imperative = datetime::createFromFormat("j/n/Y",$this->adv->fields["trsptotherdate"]);
            }
                
            $this->infotrspt=$this->adv->fields['infotrspt'] == 'other' ? $this->adv->fields['othertrsptinfo'] : '';
            
            if(in_array($this->paysid,array(57,181,111))){
                $this->volume=0;
                $this->type_etiquette="GTF";
            }else
                $this->type_etiquette="SSCC";
            
            switch($this->adv->fields['trspttype']){
                case "starprio":
                $this->trspttype="P18";
                break;
                case "starprio13h":
                $this->trspttype="P13";
                break;
                case "stardate":
                $this->trspttype="D18";
                break;
                case "stardate13h":
                $this->trspttype="D13";
                break;
                case "rdvpro":
                $this->trspttype="RDV";
                break;
                case "rdvpart":
                $this->trspttype="PAR";
                break;
            }
            
            $this->plan();
        }
    }
    
    function plan(){
        $ptfile=__DIR__."/PT".$this->agence."CP.TXT";
        if(!file_exists($ptfile)) return false;
        $f=fopen($ptfile,"r");
        $cp=str_replace(" ","",$this->codepostal);

        while(!feof($f)){
            $line=fgets($f,1024);
            if(preg_match("/(?<agence>\d{2})(?<regime>.)(?<pays>.{3})(?<zip1>.{9})(?<zip2>.{9})(?<direction>.{3})(?<libelle>.{10})(?<travee>\d{3})/",$line,$data)){
                $pays=trim($data['pays']);
                if($pays==$this->pays){
                    $zip1=trim($data['zip1']);
                    $zip2=trim($data['zip2']);
                    if((strcasecmp($cp,$zip1)>=0 && strcasecmp($cp,$zip2)<=0) || $zip1.$zip2==""){
                        $this->travee=trim($data['travee']);
                        $this->routage=trim($data['libelle']);
                        $this->code_direction=$data['direction'];;
                        break;
                    }
                }
            }
        }
    }
    
    function from_facture($facture){
        $this->userid=$facture->userid;


	
        if(preg_match("/client|societe/",$this->adv->fields['trsptlivr'])){
            $this->raison_sociale=$facture->raisonsociale;
            $this->contact=$facture->nom." ".$facture->prenom;
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
        }
        if(preg_match("/(stock|sls)([0-9]+)/",$this->adv->fields['trsptlivr'],$m)){
            $sls=$this->DB->query_first("SELECT f.*,p.diminutif,p.cee,p.iso3166 FROM fournisseur f INNER JOIN pays p USING(paysid) WHERE id='$m[2]'");
            $this->raison_sociale=$sls['societe'];
            $this->contact=$sls['nom']." ".$sls['prenom'];
            $this->adresse=trim($sls['adresse1']." ".$sls['adresse2']);
            $this->codepostal=$sls['cp'];
            $this->ville=$sls['ville'];
            $this->pays=$sls['diminutif'];
            $this->paysid=$sls['paysid'];
    	    $this->commentaires="";
            $this->telephone=$sls['telephone'];
            $this->email=$sls['mail'];
            }
            $this->telephone=preg_replace("/[^\d]/","",$this->telephone);
            $this->date_enlev=DateTime::createFromFormat("j/n/Y",$this->adv->fields["trsptenlfourndate"]);

        
        $this->poids=0;
        $this->poidsu=array();
        $this->volume=0;
        foreach($this->adv->lignes as $lid=>$qte){
            $lignefacture=$this->DB->query_first("SELECT a.* FROM lignefacture l, article a WHERE l.artid=a.artid AND lignefactureid=".$lid);
            $this->poids+=$qte*$lignefacture['emballage_poids'];
            $this->poidsu[$lid]=$lignefacture['emballage_poids'];
            $this->volume+=$qte*($lignefacture['emballage_hauteur']*$lignefacture['emballage_largeur']*$lignefacture['emballage_profondeur'])/pow(10,6);
        }

        if(preg_match("/fourn([0-9]+)/",$this->adv->fields['lieutrspt'],$m)){
            $fourn=$this->DB->query_first("SELECT f.*,p.diminutif FROM fournisseur f INNER JOIN pays p USING(paysid) WHERE id=".$m[1]);
            if(!isset($this->chargeurs[$fourn['cp']])){
            $this->error="Aucun chargeur";
            return;
            }
            $this->agence=$this->chargeurs[$fourn['cp']];
            $this->code_chargeur=$this->codes_chargeurs[$fourn['cp']];
            $this->exp_raisonsociale=$fourn['societe'];
            $this->exp_adresse=trim($fourn['adresse1']);
            $this->exp_pays=$fourn['diminutif'];
            $this->exp_codepostal=$fourn['cp'];
            $this->exp_ville=$fourn['ville'];
            
        }else{
            $this->error="Aucun lieu d'expédition";
        }
    }
    
    function from_stock($stock_cmd){
        
        $this->userid=$stock_cmd->livraison;
        $lieu=$this->DB->query_first("SELECT fournisseur.*,pays.diminutif,pays.paysid,pays.libelle AS pays FROM fournisseur INNER JOIN pays USING(paysid) WHERE id=".$stock_cmd->livraison);
	

        $this->raison_sociale=$lieu['societe'];
        $this->contact=$lieu['nom']." ".$lieu['prenom'];
        $this->adresse=$lieu['adresse1'];
        $this->codepostal=$lieu['cp'];
        $this->ville=$lieu['ville'];
        $this->pays=$lieu['diminutif'];
        $this->paysid=$lieu['paysid'];
        $this->commentaires=$this->adv->fields['infotrspt'] == 'other' ? $this->adv->fields['othertrsptinfo'] : '';
        $this->telephone=$lieu['telephone'];
        if($this->telephone=="") $this->telephone=$lieu['portable'];
        $this->email=$lieu['mail'];

        $this->telephone=preg_replace("/[^\d]/","",$this->telephone);
	
        $this->poids=0;
        $this->poidsu=array();
        foreach($this->adv->lignes as $lid=>$qte){
            $lignefacture=$this->DB->query_first("SELECT a.* FROM article a WHERE a.artid=".$lid);
            $this->poids+=$qte*$lignefacture['emballage_poids'];
            $this->poidsu[$lid]=$lignefacture['emballage_poids'];
        }

        $this->date_enlev=DateTime::createFromFormat("j/n/Y",$this->adv->fields["parenlevindate"]);
        if($this->date_enlev==false) $this->date_enlev=new datetime();
	
        $fourn=$this->DB->query_first("SELECT f.*,p.diminutif FROM fournisseur f INNER JOIN pays p USING(paysid) WHERE id=".$stock_cmd->enlevement);
        if(!isset($this->chargeurs[$fourn['cp']])){
            $this->error="Aucun chargeur";
            return;
        }
        $this->agence=$this->chargeurs[$fourn['cp']];
        $this->code_chargeur=$this->codes_chargeurs[$fourn['cp']];
        $this->exp_raisonsociale=$fourn['societe'];
        $this->exp_adresse=trim($fourn['adresse1']);
        $this->exp_pays=$fourn['diminutif'];
        $this->exp_codepostal=$fourn['cp'];
        $this->exp_ville=$fourn['ville'];
    }
    
    function trackid(){
        return $this->adv->id;
    }
    
    function encode(){
        $lines=array();
        $nblines=5;
        $lines[]="1"
            .str_pad($this->agences[$this->agence][2],17)
            .str_pad($this->siret,17)
            .date("dmY")
            .str_repeat(" ",35)
            .str_repeat(" ",242);
        
        $contact=$this->enc_field(trim($this->raison_sociale." ".$this->contact),70);

        $lines[]="2"
        ."  "
        .str_pad($this->trackid(),15)
        .str_pad($this->facturid,15)
        .$this->regime
        .$this->mode
        ."N"
        .substr($contact,0,35)
        .substr($contact,35)
        .$this->enc_field($this->adresse,35)
        .str_repeat(" ",35)
        .str_pad($this->codepostal,9)
        .$this->enc_field($this->ville,35)
        .str_pad($this->pays,3)
        .str_pad(count($this->colis),5,"0",STR_PAD_LEFT)
        .str_pad(count($this->colis),5,"0",STR_PAD_LEFT)
        .str_pad(round($this->poids*10),5,"0",STR_PAD_LEFT)
        .str_repeat("0",4)
        .str_repeat("0",4)
        .str_repeat("0",15)
        ."EUR"
        .str_pad(floatval($this->valeurdeclaree)*100,15,"0",STR_PAD_LEFT)
        ."EUR"
        .str_pad(!is_null($this->date_imperative)?$this->date_imperative->format("dmY"):"",8)
        .str_repeat(" ",11)
        .str_repeat("0",4)
        .str_repeat("0",4)
        .str_repeat("0",4)
        .str_pad($this->code_chargeur,6);
        
        $lines[]="3"
        .str_repeat(" ",20)
        .$this->enc_field($this->infotrspt,45)
        .$this->enc_field($this->commentaires,45)
        .$this->enc_field($this->contact,35)
        .str_pad($this->telephone,25)
        ."N"
        .str_repeat(" ",148);
        
        $lines[]="4"
        .$this->enc_field($this->exp_raisonsociale,35)
        .str_repeat(" ",35)
        .$this->enc_field($this->exp_adresse,35)
        .$this->enc_field($this->exp_adresse2,35)
        .$this->enc_field($this->exp_codepostal,9)
        .$this->enc_field($this->exp_ville,35)
        .str_pad($this->exp_pays,3)
        .str_pad(round($this->poids*10),5,"0",STR_PAD_LEFT)
        .str_pad(round($this->volume),7,"0",STR_PAD_LEFT)
        .str_repeat("0",7)
        .str_pad(floatval($this->valeurdeclaree)*100,15,"0",STR_PAD_LEFT)
        ."EUR"
        .str_repeat("0",15)
        ."EUR"
        .str_repeat(" ",42)
        .str_repeat(" ",35);
        
        if(!is_null($this->trspttype)){
            $lines[]="6"
            .$this->trspttype
            .str_repeat(" ",20)
            .$this->enc_field($this->telephone,15)
            .$this->enc_field($this->email,50)
            .str_repeat(" ",230);
            
            $nblines++;
        }
        
        
        foreach($this->colis as $colisid=>$clines){
            $lines[]="8"
            .str_pad(str_pad($this->adv->id,8,"0",STR_PAD_LEFT).str_pad($colisid,3,"0",STR_PAD_LEFT),35)
            .str_pad($this->barcode($colisid),35)
            ."PAL "
            .str_pad(count($this->colis),5,"0",STR_PAD_LEFT)
            .str_repeat(" ",240);
        }
        $nblines+=count($this->colis);
    
        $lines[]="9"
        .str_pad($this->agences[$this->agence][2],17)
        .str_pad($this->siret,17)
        .date("dmY")
        .str_pad($nblines,5,"0",STR_PAD_LEFT)
        .str_repeat(" ",273);
        
        return implode("\r\n",$lines);
    }
    
    function enc_field($str,$len){
        $utf8 = array(
                'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ|Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
                'd'=>'đ|Đ',
                'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ|É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
                'i'=>'í|ì|ỉ|ĩ|ị|Í|Ì|Ỉ|Ĩ|Ị',
                'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ|Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
                'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự|Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
                'y'=>'ý|ỳ|ỷ|ỹ|ỵ|Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        );
        foreach($utf8 as $ascii=>$uni) $str = preg_replace("/($uni)/i",$ascii,$str);
        $str=preg_replace('/[[:^print:]]/', '',$str);
        $str=iconv("UTF-8","ASCII",$str);
        if(strlen($str)>$len) $str=substr($str,0,$len);
        
        return str_pad($str,$len);
    }
    
    function barcode($colisid){
        switch($this->type_etiquette){
            case "GTF":
            $return="*";
            $return.=substr($this->codepostal,0,2);
            $return.=$this->code_direction;
            $return.=$this->agences[$this->agence][1];
            $return.=$this->code_chargeur;
            $return.=str_pad($this->adv->id,8,"0",STR_PAD_LEFT).str_pad($colisid,3,"0",STR_PAD_LEFT);
            $return.="300";
            break;
            
            case "SSCC":
            $return="33";
            $return.="60661";
            $return.=str_pad($this->adv->id,7,"0",STR_PAD_LEFT).str_pad($colisid,3,"0",STR_PAD_LEFT);
            $sum=0;
            $len=strlen($return);
            for($i=0;$i<$len;$i++){
                $d=substr($return,$len-$i-1,1);
                if($i%2==0) $d*=3;
                $sum+=$d;
            }
            $sum=10-($sum%10);
            if($sum==10) $sum=0;
            $return.=$sum;
            break;
        }
        
        return $return;
    }
    
    function etiquette($pj=false, $parent=0){
        $this->pdf=new PDF();
        $this->pdf->setautopagebreak(false);
        
        foreach($this->colis as $colisid=>$lines){
            $this->page($colisid);
        }
        
        if($pj){
            $file="/tmp/".$this->adv->id.".pdf";
            $this->pdf->output($file,"F");
            $pdfsize=filesize($file);
            if($this->origin=="cmd"){
                $this->DB->query("INSERT INTO user_files SET userid=".$this->userid.", facturid=".$this->facturid.", parent='".$parent."', date=".time().", utilisateur='".$this->adv->utilisateur."', name='Etiquette Heppner ".date("Ymd-Hi").".pdf', size=".$pdfsize.", ext='pdf', state=1");
                $this->pjid=$this->DB->insert_id();
                rename($file,__DIR__."/../userfiles/".$this->pjid.".pdf");
            }
            if($this->origin=="stock"){
                $this->DB->query("INSERT INTO fournisseur_files SET fournisseur='".mysqli_real_escape_string($this->DB->link_id,$this->adv->destinataire)."', stock_cmd_id=".$this->facturid.", date=".time().", utilisateur='".$this->adv->utilisateur."', name='Etiquette Heppner ".date("Ymd-Hi").".pdf', size=".$pdfsize.", ext='pdf', state=1");
                $this->pjid=$this->DB->insert_id();
                rename($file,__DIR__."/../fournfiles/".$this->pjid.".pdf");
            }
        }else
            $this->pdf->output();
    }
    
    function page($colisid){
        $this->pdf->setfillcolor(0);
        $this->pdf->settextcolor(0);
        switch($this->type_etiquette){
            case "GTF":
            if($colisid%4==1) $this->pdf->addpage("P","A4");
            $this->pdf->setfont('arial','',8);
            $pos=array(10,10);
            if($colisid%2==0) $pos[0]+=100;
            if(ceil($colisid/2)%2==0) $pos[1]+=125;
            
            $x=$pos[0]+80;
            $y=$pos[1]+10;
            $this->pdf->setXY($x,$y);
            $this->pdf->cell(15,4,$this->code_chargeur);
            
            $x=$pos[0];
            $y=$pos[1]+10;
            $this->pdf->setXY($x,$y);
            $this->pdf->cell(10,4,"Exp :");
            
            $y=$pos[1]+14;
            $this->pdf->setXY($x,$y);
            $this->pdf->cell(10,4,"Ref. Exp :");
            $this->pdf->line($pos[0],$pos[1]+18,$pos[0]+90,$pos[1]+18);
            
            $y=$pos[1]+19;
            $this->pdf->setXY($x,$y);
            $this->pdf->cell(10,4,"Dest :");
            
            $y=$pos[1]+40;
            $this->pdf->setXY($x,$y);
            $this->pdf->cell(10,4,"Instructions:");
            $this->pdf->line($pos[0],$pos[1]+47,$pos[0]+90,$pos[1]+47);
            $this->pdf->line($pos[0]+38,$pos[1]+47,$pos[0]+38,$pos[1]+87);
            
            $y=$pos[1]+47;
            $this->pdf->setXY($x,$y);
            $this->pdf->cell(10,4,utf8_decode("Récépissé :"));
            
            $y=$pos[1]+52;
            $this->pdf->setXY($x,$y);
            $this->pdf->cell(10,4,"Colis :");
            $this->pdf->line($pos[0],$pos[1]+57,$pos[0]+38,$pos[1]+57);
            
            $y=$pos[1]+57;
            $this->pdf->setXY($x,$y);
            $this->pdf->cell(10,4,"Date exp :");
            
            $y=$pos[1]+61;
            $this->pdf->setXY($x,$y);
            $this->pdf->cell(10,4,"Date liv :");
            
            $y=$pos[1]+65;
            $this->pdf->setXY($x,$y);
            $this->pdf->cell(10,4,"Poids :");
            
            $y=$pos[1]+70;
            $this->pdf->setXY($x,$y);
            $this->pdf->cell(10,4,"CR :");
            $this->pdf->line($pos[0],$pos[1]+74,$pos[0]+38,$pos[1]+74);
            $this->pdf->line($pos[0]+38,$pos[1]+77,$pos[0]+90,$pos[1]+77);
            
            $y=$pos[1]+78;
            $this->pdf->setXY($x,$y);
            $this->pdf->cell(10,4,"Travee :");
            $this->pdf->line($pos[0],$pos[1]+87,$pos[0]+90,$pos[1]+87);
            
            $this->pdf->setfont('arial','',9);
            
            $x=$pos[0]+20;
            $y=$pos[1]+10;
            $this->pdf->setXY($x,$y);
            $this->pdf->cell(10,4,"ACILYON");
            
            $y=$pos[1]+14;
            $this->pdf->setXY($x,$y);
            $this->pdf->cell(10,4,$this->trackid());
            
            $y=$pos[1]+19;
            $this->pdf->setXY($x,$y);
            $dest=trim(utf8_decode(preg_replace('/[[:^print:]]/', '',$this->raison_sociale."\n".$this->contact."\n".$this->adresse."\n".$this->pays." ".$this->codepostal." ".$this->ville."\nTél : ".$this->telephone)));
            $this->pdf->multicell(70,4,$dest);
            
            $this->pdf->setfont('arial','',8);
            $y=$pos[1]+40;
            $this->pdf->setXY($x,$y);
            $this->pdf->multicell(70,3,trim(utf8_decode(strtolower($this->commentaires))),0,'L');
            
            $this->pdf->setfont('arial','',9);
            $y=$pos[1]+52;
            $this->pdf->setXY($x,$y);
            $this->pdf->multicell(70,4,$colisid."/".count($this->colis));
            
            $y=$pos[1]+57;
            $this->pdf->setXY($x,$y);
            $this->pdf->multicell(70,4,$this->date_enlev->format("d/m/Y"));
            
            if(!is_null($this->date_imperative)){
                $y=$pos[1]+61;
                $this->pdf->setXY($x,$y);
                $this->pdf->multicell(70,4,$this->date_imperative->format("d/m/Y"));
            }
            
            $y=$pos[1]+65;
            $this->pdf->setXY($x,$y);
            $this->pdf->multicell(70,4,number_format($this->poidscolis[$colisid],3,",",""));
            
            $y=$pos[1]+70;
            $this->pdf->setXY($x,$y);
            $this->pdf->multicell(70,4,"NON");
        
            $y=$pos[1]+74;
            $this->pdf->setXY($x,$y);
            $this->pdf->settextcolor(255);
            $this->pdf->setfillcolor(0);
            $this->pdf->setfont("arial","B",22);
            $this->pdf->cell(18,13,$this->travee,0,0,'C',1);
            $this->pdf->settextcolor(0);
            $this->pdf->setfillcolor(255);
            
            $x=$pos[0]+38;
            $y=$pos[1]+46;
            $this->pdf->setXY($x,$y);
            $this->pdf->setfont("arial","B",60);
            $this->pdf->cell(50,30,substr($this->codepostal,0,2),0,0,'C');
            
            $this->pdf->setFont("arial","B",20);
            $this->pdf->setXY($pos[0]+40,$pos[1]+76);
            $this->pdf->cell(50,11,$this->routage,0,0,'C');
            $this->pdf->setfont("arial","",9);
            
            $x=$pos[0]+40;
            $y=$pos[1]+98;
            $barcode=$this->barcode($colisid);
            barcode::fpdf($this->pdf,"#000000",$x,$y,0,"code128",$barcode,0.3,13);
            $this->pdf->setXY($pos[0]+5,$y+15);
            preg_match("/(.)(\d{2})(\d{3})(\d{6})(\d{6})(\d{8})(\d{3})(\d)(\d{2})/",$barcode,$m);
            array_shift($m);
            $this->pdf->cell(65,4,implode(" ",$m));
            
            $this->pdf->line($pos[0],$pos[1]+117,$pos[0]+90,$pos[1]+117);
            $this->pdf->line($pos[0]+74,$pos[1]+117,$pos[0]+74,$pos[1]+127);
            
            $this->pdf->image(__DIR__."/../images/heppner_etiquette.jpg",$pos[0],$pos[1]+117,34);
            $this->pdf->setXY($pos[0]+38,$pos[1]+118);
            $this->pdf->setfont("arial","B",9);
            $this->pdf->cell(35,4,"HEPPNER ".strtoupper($this->agences[$this->agence][0]),0,0,'R');
            $this->pdf->setXY($pos[0]+75,$pos[1]+117);
            $this->pdf->cell(15,10,"MESS");
            
            $this->pdf->setfont("arial","",8);
            $this->pdf->setXY($pos[0]+46,$pos[1]+122);
            $num=$this->agences[$this->agence][3];
            $exp=str_split($num,2);
            $num=implode(".",$exp);
            $this->pdf->cell(25,4,"Tel : ".$num);
            
            break;
            
            case "SSCC":
            if($colisid%4==1) $this->pdf->addpage("P","A4");
            $pos=array(10,10);
            if($colisid%2==0) $pos[0]+=100;
            if(ceil($colisid/2)%2==0) $pos[1]+=125;
            $this->pdf->image(__DIR__."/../images/heppner_etiquette.jpg",$pos[0],$pos[1],34);
            $this->pdf->setXY($pos[0],$pos[1]+9.5);
            $this->pdf->setfont("arial","",7);
            $num=$this->agences[$this->agence][3];
            $exp=str_split($num,2);
            $exp[0]=ltrim($exp[0],"0");
            $num="         Tel:+33".implode(".",$exp);
            $this->pdf->cell(90,4,"HEPPNER ".strtoupper($this->agences[$this->agence][0]).$num);
            $this->pdf->line($pos[0],$pos[1]+13,$pos[0]+90,$pos[1]+13);
            
            $this->pdf->line($pos[0]+45,$pos[1]+13,$pos[0]+45,$pos[1]+35);
            $this->pdf->line($pos[0],$pos[1]+35,$pos[0]+90,$pos[1]+35);
            
            $this->pdf->setXY($pos[0],$pos[1]+14);
            $this->pdf->multicell(44,3,"From (exp.) :   ".$this->code_chargeur."\nACILYON\nLYON 03");
            
            $this->pdf->setXY($pos[0]+45,$pos[1]+14);
            $this->pdf->multicell(45,3,"To (Dest) : ".trim(utf8_decode($this->raison_sociale."\n".$this->contact."\n".$this->adresse."\n".$this->pays." ".$this->codepostal." ".$this->ville."\nTel : ".$this->telephone)));
            
            $this->pdf->setXY($pos[0],$pos[1]+35);
            $this->pdf->multicell(22,3.5,utf8_decode("Récépissé :\nDate expédition :\nInstruction :"));
            
            $this->pdf->setXY($pos[0]+22,$pos[1]+35);
            $this->pdf->multicell(70,3.5,utf8_decode("\n".$this->date_enlev->format("d/m/Y")."\n".$this->commentaires));
            
            $this->pdf->setXY($pos[0]+45,$pos[1]+38);
            if(!is_null($this->date_imperative))
                $this->pdf->multicell(45,4,"Date livraison :    ".$this->date_imperative->format("d/m/Y"));
            
            $this->pdf->setXY($pos[0]+30,$pos[1]+50);
            $this->pdf->multicell(12,7,"Poids :\nColis :\nPort du :\nC.R. :");
            $this->pdf->setXY($pos[0]+42,$pos[1]+50);
            $this->pdf->multicell(18,7,number_format($this->poids,3,",","")." kg\n".$colisid."/".count($this->colis)."\nNON\nNON");
            
            $this->pdf->setXY($pos[0],$pos[1]+79);
            $this->pdf->cell(31,4,utf8_decode("Référence :     ".$this->facturid));
            
            $this->pdf->setXY($pos[0]+3,$pos[1]+85);
            $this->pdf->multicell(5,4,"S\nS\nC\nC");
        
            $barcode=$this->barcode($colisid);
            barcode::fpdf($this->pdf,"#000000",$pos[0]+42,$pos[1]+93,0,"code128",$barcode,0.3,13);
            $this->pdf->setXY($pos[0]+20,$pos[1]+104);
            preg_match("/(\d{2})(\d{5})(\d{10})(\d)/",$barcode,$m);
            array_shift($m);
            $this->pdf->cell(65,4,"(00) ".implode(" ",$m));
            
            $this->pdf->line($pos[0],$pos[1]+50,$pos[0]+90,$pos[1]+50);
            
            $this->pdf->setfont('arial',"B",20);
            $this->pdf->settextcolor(255);
            $this->pdf->setfillcolor(0);
            $this->pdf->setXY($pos[0],$pos[1]+50);
            $cp=$this->pays."\n".$this->codepostal;
            $this->pdf->multicell(30,round(28/$this->pdf->NbLines(30,$cp)),$cp,0,'C',1);
            
            $this->pdf->setfont('arial',"",14);
            $this->pdf->setXY($pos[0]+60,$pos[1]+50);
            $this->pdf->multicell(30,14,"I".$this->code_direction,0,'C',1);
            $this->pdf->setXY($pos[0]+60,$pos[1]+63);
            $this->pdf->multicell(30,round(14/$this->pdf->NbLines(30,$this->routage)),$this->routage,0,'C',1);
            
            $this->pdf->line($pos[0],$pos[1]+78,$pos[0]+90,$pos[1]+78);
            $this->pdf->line($pos[0],$pos[1]+83,$pos[0]+90,$pos[1]+83);
            break;
        }
    }
    
    function send($test=false){
        $mode=$test?"test":"prod";
        $path=dirname(dirname(__DIR__))."/configurations/".$this->host."/transporteurs/heppner/out/".date("YmdHis")."-".$this->facturid;
        file_put_contents($path,$this->encode());
        $conn=ftp_connect($this->ftp[$mode]["host"]);
        if($conn){
            if(ftp_login($conn,$this->ftp[$mode]["login"],$this->ftp[$mode]["pass"])){
                ftp_pasv($conn,true);
                $return=ftp_put($conn,"in/".date("YmdHis")."-".$this->facturid,$path,FTP_ASCII);
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
        $mode="prod";
        $conn=ftp_connect($this->ftp[$mode]["host"]);
        if($conn){
            if(ftp_login($conn,$this->ftp[$mode]["login"],$this->ftp[$mode]["pass"])){
                ftp_pasv($conn,true);
                ftp_chdir($conn,"out");
                $contents = ftp_nlist($conn, "."); 
                foreach ($contents as $file) {
                    if ($file == '.' || $file == '..') 
                        continue;
                        
                    $localfile= dirname(dirname(__DIR__))."/configurations/".$this->host."/transporteurs/heppner/in/".$file;
                    ftp_get($conn,$localfile, $file, FTP_BINARY);
                    ftp_delete($conn,$file);
                    
                    $f=fopen($localfile,"r");
                    while(!feof($f)){
                        $line=fgets($f,1024);
                        if(preg_match("/^EV(.{8})(.{35})(.{15})(.{15})(.{6})(.{3})(.{3})(.{8})(.{4})(.{35})/",$line,$m)){
                            $facturid=trim($m[4]);
                            $trackid=trim($m[3]);
                            $deliverydate=DateTime::CreateFromFormat("Ymd",$m[8]);
                            $deliverydate->settime(0,0,0);
                            $situation=$m[6];
                            $justification=$m[7];
                            
                            switch($situation){
                                case "LIV":
                                    $this->DB->query("UPDATE envoi_fournisseur SET livraison=".$deliverydate->getTimestamp()." WHERE facturid='".mysqli_real_escape_string($this->DB->link_id,$facturid)."' AND trackid='".mysqli_real_escape_string($this->DB->link_id,$trackid)."'");
                                break;
                                
                                case "PCH":
                                    $this->DB->query("UPDATE envoi_fournisseur SET enlevement=".$deliverydate->getTimestamp()." WHERE facturid='".mysqli_real_escape_string($this->DB->link_id,$facturid)."' AND trackid='".mysqli_real_escape_string($this->DB->link_id,$trackid)."'");
                                break;
                            }
                
                            if(isset($this->situations[$situation])){
                                $suivi="Colis Heppner ".$trackid." ".$this->situations[$situation]." ".$this->justifications[$justification]." ".$deliverydate->format("d/m/Y");
                                $this->DB->query("INSERT INTO suivi_commande SET facturid='".$facturid."', date=".time().", suivi='".mysqli_real_escape_string($this->DB->link_id,$suivi)."'");
                                $return[]=$suivi;
                            }
                        }
                    }
                }
                ftp_close($conn);
            }else{
                ftp_close($conn);
                $this->error="Could not login";
                return false;
            }
        }else{
            $this->error="Could not connect to ftp";
            return false;
        }
        
        return $return;
    }
    
}

?>
