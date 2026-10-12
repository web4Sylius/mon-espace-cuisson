<?php
require_once __DIR__.'/../../secrets.php';

class schenker {
    
    private $agences=array(
        "H1"=>array(
            "code"=>"H1",
            "nom"=>"HLU Lyon",
            "adresse"=>"8 rue de Dole - Port Edouard Herriot",
            "cp"=>"69007",
            "ville"=>"Lyon",
            "telephone"=>"04.72.47.40.00",
            "fax"=>"04.78.90.65.88",
            "siret"=>"31179945602105",
            "gtf"=>"211223",
            "3lc"=>"LIO",
            "belt"=>"20"
        )
    );
    
    private $chargeurs=array(
        "69800"=>"H1",
        "69003"=>"H1",
        "72230"=>"H1",
        "69009"=>"H1",
    );
    
    /*private $compte_facturation="697474";
    
    private $ftp=array(
        "test"=>array(
            "host"=>"81.200.201.149",
            "login"=>SCHENKER_FTP_LOGIN,
            "pass"=>SCHENKER_TEST_FTP_PASSWORD
        ),
        "prod"=>array(
            "host"=>"81.200.197.149",
            "login"=>SCHENKER_FTP_LOGIN,
            "pass"=>SCHENKER_PROD_FTP_PASSWORD
        ),
        "plan"=>array(
            "host"=>"ftp.sword.dbschenker.com",
            "login"=>SCHENKER_PL_FTP_LOGIN,
            "pass"=>SCHENKER_PL_FTP_PASSWORD
        )
    );*/
    
    private $ptfile;
    private $compte_facturation;
    private $ftp;
    private $DB;
    private $host;
    private $adv;
    private $error;
    private $facturid;
    private $typetrspt;
    private $typetrsptlabel;
    private $code_port;
    private $regime;
    private $origin;
    private $valeurdeclaree;
    private $date_imperative;
    private $infotrspt;
    private $type_etiquette;
    private $pjid;
    private $nature;
    private $code_direction;
    
    function __construct($DB,$adv,$compte_facturation,$ftp,$host=null){
        $this->DB=$DB;
        $this->ptfile=__DIR__."/PTschenker";
        $this->compte_facturation = $compte_facturation;
        $this->ftp = $ftp;
        if(!is_null($host))
            $this->host = $host;
        else
            $this->error = "Aucun Host";

        if(!is_null($adv)){
            $this->adv=$adv;
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
            
            $this->ville=$this->formatville($this->ville);

            $this->exp_adresse2="";
            if(strlen($this->exp_adresse)>35){
                $this->exp_adresse2=substr($this->exp_adresse,35);
                $this->exp_adresse=substr($this->exp_adresse,0,35);
            }
            
            $this->exp_ville=$this->formatville($this->exp_ville);
            
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

                $this->code_port = "P";

                switch($this->adv->fields['trspttype']){
                    case "standard":
                    $this->typetrspt="01";
                    $this->typetrsptlabel="MES";
                    $this->regime=3;
                    break;
                    
                    case "home":
                    $this->typetrspt="19";
                    $this->typetrsptlabel="HOM";
                    $this->regime=3;
                    break;

                    case "top13":
                    $this->typetrspt="12";
                    $this->typetrsptlabel="P13";
                    $this->regime=1;
                    break;
                    
                    case "top18":
                    $this->typetrspt="13";
                    $this->typetrsptlabel="P18";
                    $this->regime=3;
                    break;
                    
                    case "direct":
                    $this->typetrspt="80";
                    $this->typetrsptlabel="IND";
                    $this->regime=3;
                    break;
                    
                    default:
                    $this->typetrspt="01";
                    $this->typetrsptlabel="MES";
                    $this->regime=3;
                }
            }else{

                $this->code_port = "DAP";

                switch($this->adv->fields['trspttype']){
                    case "standard":
                    $this->typetrspt="60";
                    $this->typetrsptlabel="INT";
                    $this->regime=3;
                    break;

                    case "home":
                    $this->typetrspt="69";
                    $this->typetrsptlabel="HOM encadré";
                    $this->regime=3;
                    break;
                    
                    case "top13":
                    $this->typetrspt="62";
                    $this->typetrsptlabel="P13";
                    $this->regime=3;
                    break;
                    
                    case "top18":
                    $this->typetrspt="63";
                    $this->typetrsptlabel="P18";
                    $this->regime=3;
                    break;
                    
                    case "direct":
                    $this->typetrspt="81";
                    $this->typetrsptlabel="IND";
                    $this->regime=3;
                    break;
                    
                    default:
                    $this->typetrspt="60";
                    $this->typetrsptlabel="INT";
                    $this->regime=3;
                }
            }
            
            $this->plan();
        }
    }
    
    function formatville($str){
        $utf8 = array(
                'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ|Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
                'd'=>'đ|Đ',
                'e'=>'é|è|ë|ê|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ|É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
                'i'=>'í|ì|ỉ|ĩ|ị|Í|Ì|Ỉ|Ĩ|Ị',
                'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ|Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
                'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự|Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
                'y'=>'ý|ỳ|ỷ|ỹ|ỵ|Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        );
        foreach($utf8 as $ascii=>$uni) $str = preg_replace("/($uni)/i",$ascii,$str);
        
        $str=strtoupper($str);
        $str=str_replace("SAINTE","STE",$str);
        $str=str_replace("SAINT","ST",$str);
        $str=str_replace("CEDEX","",$str);
        $str=preg_replace("/[^A-Z\d\s]/", " ", $str);
        $str=trim($str);
        
        return $str;
    }
    
    function plan(){
        
        $results=array();
        
        $dest=$this->pays." ".$this->codepostal."\s*".$this->ville;
        $results=shell_exec("grep -i '^".$dest."\s' '".$this->ptfile."'");
        $b=0;
        while(count($results)==0 && $b<=strlen($this->codepostal)){
            $dest=$this->pays." ".substr($this->codepostal,0,strlen($this->codepostal)-$b);
            $results=shell_exec("grep '^".$dest."\s' '".$this->ptfile."'");
            $b++;
        }

        if(!is_null($results)){
            $plan=reset(preg_split("/\r\n/",$results));
            $transit=substr($plan,64,20);
            $this->transit=str_split(trim($transit),4);
            $this->agence_livr=trim(substr($plan,85,3));
            if(strlen($this->agence_livr)==3) $this->agence_livr=preg_replace("/^(.{2})(.)$/","$1 $2",$this->agence_livr);
            $this->mode_livraison=trim(substr($plan,88,2)).":".substr($plan,90,3);
            $this->type_etiquette=substr($plan,108,4);
            $this->code_direction=substr($plan,93,3);

            return true ;
        }else{

            return false;
        }
    
    }
    
    
    function from_facture($facture){
        $this->userid=$facture->userid;
    
        if($this->adv->fields['trsptlivr']=="client"){
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
        if(preg_match("/(stock|sls|fourn)([0-9]+)/",$this->adv->fields['trsptlivr'],$m)){
            $sls=$this->DB->query_first("SELECT f.*,p.diminutif,p.cee,p.iso3166 FROM fournisseur f INNER JOIN pays p USING(paysid) WHERE id='$m[2]'");
            $this->raison_sociale=$sls['societe'];
            $this->contact=$sls['nom']." ".$sls['prenom'];
            $this->adresse=trim($sls['adresse1']." ".$sls['adresse2']);
            $this->codepostal=$sls['cp'];
            $this->ville=$sls['ville'];
            $this->pays=$sls['diminutif'];
            $this->paysid=$sls['paysid'];
            //$this->commentaires=$facture->infotranspo;
            $this->telephone=$sls['telephone'];
            $this->email=$sls['mail'];
        }
        $this->telephone=preg_replace("/[^\d]/","",$this->telephone);
        $this->date_enlev=DateTime::createFromFormat("j/n/Y",$this->adv->fields["trsptenlfourndate"]);

        $req=$this->DB->query("SELECT * FROM typearticle");
        while($t=$this->DB->fetch_array($req))
            $typesarticle[$t['typearticleid']]=$t['libelle'];

    
        $this->poids=0;
        $this->poidsu=array();
        $this->volume=0;
        $natures = array();
        foreach($this->adv->lignes as $lid=>$qte){
            $lignefacture=$this->DB->query_first("SELECT a.*,(a.emballage_poids1+a.emballage_poids2+a.emballage_poids3) as emballage_poids,(a.emballage_hauteur1+a.emballage_hauteur2+a.emballage_hauteur3) as emballage_hauteur, (a.emballage_largeur1+a.emballage_largeur2+a.emballage_largeur3) as emballage_largeur,  (a.emballage_profondeur1+a.emballage_profondeur2+a.emballage_profondeur3) as emballage_profondeur FROM lignefacture l, article a WHERE l.artid=a.artid AND lignefactureid=".$lid);
            $this->poids+=$qte*$lignefacture['emballage_poids'];
            $this->poidsu[$lid]=$lignefacture['emballage_poids'];
            $this->volume+=$qte*($lignefacture['emballage_hauteur']*$lignefacture['emballage_largeur']*$lignefacture['emballage_profondeur'])/pow(10,9);
            $natures[]=$typesarticle[$lignefacture['typearticleid']];
        }
        $this->nature = implode(",",array_unique($natures));

        if(preg_match("/fourn([0-9]+)/",$this->adv->fields['lieutrspt'],$m)){
            $fourn=$this->DB->query_first("SELECT f.*,p.diminutif FROM fournisseur f INNER JOIN pays p USING(paysid) WHERE id=".$m[1]);
            if(!isset($this->chargeurs[$fourn['cp']])){
                $this->error="Aucun chargeur";
                return;
            }
            $this->agence=$this->chargeurs[$fourn['cp']];
            $this->exp_raisonsociale=$fourn['societe'];
            $this->exp_adresse=trim($fourn['adresse1']);
            $this->exp_pays=$fourn['diminutif'];
            $this->exp_codepostal=$fourn['cp'];
            $this->exp_ville=$fourn['ville'];
            $this->exp_phone=$fourn['telephone'];
            $this->exp_contact=trim($fourn['nom']." ".$fourn['prenom']);
            $this->exp_email=trim(reset(preg_split("/[;,]/",$fourn['mail'])));
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
        $this->volume=0;
        foreach($this->adv->lignes as $lid=>$qte){
            $lignefacture=$this->DB->query_first("SELECT a.*,(a.emballage_poids1+a.emballage_poids2+a.emballage_poids3) as emballage_poids,(a.emballage_hauteur1+a.emballage_hauteur2+a.emballage_hauteur3) as emballage_hauteur, (a.emballage_largeur1+a.emballage_largeur2+a.emballage_largeur3) as emballage_largeur,  (a.emballage_profondeur1+a.emballage_profondeur2+a.emballage_profondeur3) as emballage_profondeur FROM article a WHERE a.artid=".$lid);
            $this->poids+=$qte*$lignefacture['emballage_poids'];
            $this->poidsu[$lid]=$lignefacture['emballage_poids'];
            $this->volume+=$qte*($lignefacture['emballage_hauteur']*$lignefacture['emballage_largeur']*$lignefacture['emballage_profondeur'])/pow(10,9);
        }

        if(isset($this->adv->fields["parenlevindate"]))
            $this->date_enlev=DateTime::createFromFormat("j/n/Y",$this->adv->fields["parenlevindate"]);
    
        $fourn=$this->DB->query_first("SELECT f.*,p.diminutif FROM fournisseur f INNER JOIN pays p USING(paysid) WHERE id=".$stock_cmd->enlevement);
        if(!isset($this->chargeurs[$fourn['cp']])){
            $this->error="Aucun chargeur";
            return;
        }
        $this->agence=$this->chargeurs[$fourn['cp']];
        $this->exp_raisonsociale=$fourn['societe'];
        $this->exp_adresse=trim($fourn['adresse1']);
        $this->exp_pays=$fourn['diminutif'];
        $this->exp_codepostal=$fourn['cp'];
        $this->exp_ville=$fourn['ville'];
        $this->exp_phone=$fourn['telephone'];
        $this->exp_contact=trim($fourn['nom']." ".$fourn['prenom']);
        $this->exp_email=trim(reset(preg_split("/[;,]/",$fourn['mail'])));
    }
    
    function trackid(){
        return $this->adv->id * 101;
    }
    
    function encode(){
        $line=array();
        $line[1]="S"; // séparateur de champ
        $line[2]=is_a($this->date_enlev,"datetime") ? $this->date_enlev->format("Ymd") : ""; // date expédition
        $line[3]="0".(in_array($this->paysid,array(57,181,111))?"1":"2").$this->agences[$this->agence]['code']; // code prestation
        $line[4]=($this->adv->fields['optname[2]']=='Livraison particulier sur rendez-vous'?19:$this->typetrspt); // code produit (01 = national ; 19 = system home)
        $line[5]=$this->compte_facturation; // compte facturation
        $line[6]="ACILYON"; // libellé donneur d'ordre
        $line[7]=""; // réservé
        $line[8]=$this->enc_field($this->contact);            //Nom destinataire. Obligatoire
        $line[9]=$this->enc_field($this->raison_sociale);     //Nom destinataire. Facultatif
        $line[10]="";                       //Nom destinataire. Facultatif
        $line[11]=$this->enc_field($this->adresse);           //Adresse destinataire Obligatoire
        $line[12]="";                       //Adresse destinataire Facultatif
        $line[13]="";                       //Adresse destinataire Facultatif
        $line[14]=$this->enc_field($this->pays);
        $line[15]=$this->codepostal;
        $line[16]=$this->ville;
        $line[17]=$this->enc_field($this->contact);
        $line[18]=preg_replace("/[^\d\+]/","",$this->telephone);
        $line[19]= $this->adv->id * 101;   //numero de recepisse
        $line[20]= $this->facturid;        //reference d'expedition
        $line[21]=$this->code_port;
        $line[22]=count($this->colis);
        $line[23]=count($this->colis);
        $line[24]="";
        $line[25]="";
        $line[26]=$this->poids;
        $line[27]="";
        $line[28]="";
        $line[29]=round($this->volume,3);
        $line[30]="";
        $line[31]="";
        $line[32]="";      //$this->valeurdeclaree; // valeur declaree non remplie pour DB SCHENKER
        $line[33]="";      //"EUR";
        $line[34]="";
        $line[35]="";
        $line[36]=$this->enc_field(mb_strimwidth($this->nature, 0, 26,"")); // nature marchandises
        $line[37]=$this->enc_field(str_replace(";",",",$this->commentaires));
        $line[38]=$this->enc_field(str_replace(";",",",$this->infotrspt));
        $line[39]=!is_null($this->date_imperative) ? $this->date_imperative->format("Ymd") : "";
        $line[40]="";
        $line[41]=($this->adv->fields['optname[2]']=='Livraison particulier sur rendez-vous'?"O":""); //rdv particulier
        $line[42]="";
        $line[43]=preg_replace("/[^\d\+]/","",$this->telephone);
        $line[44]=$this->email;
        
        $um=array();
        foreach($this->colis as $colisid=>$lines){
            $umline=array();
            $umline[1]="UM";
            $umline[2]="PCI";
            $umline[3]="";
            $umline[4]="";
            $umline[5]=$this->barcode($colisid);
            $umline[6]=round($this->poidscolis[$colisid],1);
            $umline[7]="";
            $umline[8]="";
            $umline[9]="";
            $umline[10]="";
            $umline[11]="";
            $umline[12]="";
            $umline[13]="";
            
            $um[]=implode(";",$umline);
        }
        
        return implode(";",$line)."\n".implode("\n",$um)."\n";
    }
    
    function barcode($colisid){

        switch($this->type_etiquette){
            case "JOYA":
            $return="*";
            $return.=substr($this->codepostal,0,2);
            $return.=$this->code_direction;
            $return.=$this->agences[$this->agence]["gtf"];
            $return.="010012";
            $return.=($this->adv->id*101).str_pad($colisid,3,"0",STR_PAD_LEFT);
            $return.=$this->regime;
            $return.="00";
            break;
            
            case "BELT":
            $return="0";
            $return.="250";
            $return.="07";
            $return.=$this->agences[$this->agence]['belt'];
            $return.=substr("010012",-4,4);
            $return.=($this->adv->id*101).str_pad($colisid,3,"0",STR_PAD_LEFT);
            $return.="0";
            break;
        }
        
        return $return;
    }
    
    function etiquette($pj=false, $parent=0){
        $this->pdf=new pdf();
        $this->pdf->setautopagebreak(false);
    
        foreach($this->colis as $colisid=>$lines){
            $this->page($colisid);
        }
        
        if($pj){
                $file="/tmp/".$this->adv->id.".pdf";
            $this->pdf->output($file,"F");
            $pdfsize=filesize($file);
            if($this->origin=="cmd"){
                    $this->DB->query("INSERT INTO user_files SET userid=".$this->userid.", facturid=".$this->facturid.", parent='".$parent."', date=".time().", utilisateur='".$this->adv->utilisateur."', name='Etiquette Schenker ".date("Ymd-Hi").".pdf', size=".$pdfsize.", ext='pdf', state=1");
                    $this->pjid=$this->DB->insert_id();
                    rename($file,__DIR__."/../userfiles/".$this->pjid.".pdf");
                }
                if($this->origin=="stock"){
                    $this->DB->query("INSERT INTO fournisseur_files SET fournisseur='".mysqli_real_escape_string($this->DB->link_id,$this->adv->destinataire)."', stock_cmd_id=".$this->facturid.", date=".time().", utilisateur='".$this->adv->utilisateur."', name='Etiquette Schenker ".date("Ymd-Hi").".pdf', size=".$pdfsize.", ext='pdf', state=1");
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
            case "JOYA":
            if($colisid%4==1) $this->pdf->addpage("P","A4");
            $this->pdf->setfont('arial','',8);
            $pos=array(5,10);
            if($colisid%2==0) $pos[0]+=100;
            if(ceil($colisid/2)%2==0) $pos[1]+=125;
            
            $this->pdf->line($pos[0]+8.4,$pos[1]+9,$pos[0]+98,$pos[1]+9);
            $this->pdf->line($pos[0]+8.4,$pos[1]+26.4,$pos[0]+66.8,$pos[1]+26.4);
            $this->pdf->line($pos[0]+8.4,$pos[1]+41.4,$pos[0]+51.6,$pos[1]+41.4);
            $this->pdf->line($pos[0]+8.4,$pos[1]+67.3,$pos[0]+98.9,$pos[1]+67.3);
            $this->pdf->line($pos[0]+51.6,$pos[1]+26.4,$pos[0]+51.6,$pos[1]+67.3);
            $this->pdf->line($pos[0]+66.8,$pos[1]+9,$pos[0]+66.8,$pos[1]+67.3);
            $this->pdf->line($pos[0]+66.8,$pos[1]+46.5,$pos[0]+98.9,$pos[1]+46.5);
            $this->pdf->line($pos[0]+8.4,$pos[1]+99.6,$pos[0]+98,$pos[1]+99.6);
            $this->pdf->line($pos[0]+80.5,$pos[1]+101.3,$pos[0]+80.5,$pos[1]+107.4);
            
            $this->pdf->setfont("arial","",7);
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+0.3);
            $this->pdf->cell(10,4,"Exped:");
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+8.8);
            $this->pdf->cell(10,4,"Dest:");
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+25.7);
            $this->pdf->cell(10,4,"Instr:");
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+41.4);
            $this->pdf->cell(10,4,utf8_decode("Récépissé:"));
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+46.2);
            $this->pdf->cell(10,4,"Colis");
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+50);
            $this->pdf->multicell(14,2.5,"Support de cond.");
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+55.6);
            $this->pdf->cell(10,4,"Date exped:");
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+59.5);
            $this->pdf->cell(10,4,"Date imp. liv:");
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+63.8);
            $this->pdf->cell(10,4,"Poids envoi:");
            
            $this->pdf->setfont("arial","B",8);
            $this->pdf->setXY($pos[0]+17.5,$pos[1]+0.3);
            //$this->pdf->cell(40,4,utf8_decode($this->exp_raisonsociale));
            $this->pdf->cell(40,4,"ACILYON");
            $this->pdf->setXY($pos[0]+76.7,$pos[1]+3.8);
            $this->pdf->cell(20,4,$this->facturid);
            $this->pdf->setfont("arial","",8);
            $this->pdf->setXY($pos[0]+17.5,$pos[1]+5.3);
            //$this->pdf->cell(40,4,utf8_decode($this->exp_pays." ".$this->exp_codepostal." ".$this->exp_ville));
            $this->pdf->cell(40,4,"FR 69003 LYON");
            
            $this->pdf->setfont("arial","B",8);
            $this->pdf->setXY($pos[0]+16,$pos[1]+9.5);
            $this->pdf->multicell(50,3,trim(utf8_decode($this->raison_sociale." ".$this->contact)),0,'L');
            $this->pdf->setXY($pos[0]+11.7,$pos[1]+20);
            $this->pdf->cell(55,4,utf8_decode($this->pays." ".$this->codepostal." ".$this->ville));
            $this->pdf->setfont("arial","",8);
            $this->pdf->setXY($pos[0]+16,$pos[1]+15);
            $this->pdf->multicell(50,3,utf8_decode($this->adresse));
            $this->pdf->setXY($pos[0]+16,$pos[1]+23);
            $this->pdf->cell(50,4,utf8_decode('Tel: '. $this->telephone));

            $this->pdf->setfont("arial","",7);
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+27.9);
            $this->pdf->multicell(42,3,utf8_decode($this->commentaires));
            
            $this->pdf->setfont("arial","B",8);
            $this->pdf->setXY($pos[0]+29,$pos[1]+41.4);
            $this->pdf->cell(25,4,$this->adv->id*101); //Numero de récépissé
            $this->pdf->setXY($pos[0]+14.7,$pos[1]+46);
            $this->pdf->cell(10,4,$colisid."/".count($this->colis));
            $this->pdf->setfont("arial","",8);
            $this->pdf->setXY($pos[0]+29,$pos[1]+46.5);
            $this->pdf->cell(10,4,($this->adv->id*101).str_pad($colisid,3,"0",STR_PAD_LEFT));// numero de colis
            $this->pdf->setXY($pos[0]+29,$pos[1]+56.1);
            if(is_a($this->date_enlev,"datetime"))
                $this->pdf->cell(25,4,$this->date_enlev->format("d/m/Y"));
            if(!is_null($this->date_imperative)){
                $this->pdf->setXY($pos[0]+29,$pos[1]+60.2);
                $this->pdf->cell(25,4,$this->date_imperative->format("d/m/Y"));
            }
            $this->pdf->setXY($pos[0]+29,$pos[1]+64);
            $this->pdf->cell(25,4,number_format($this->poids,3,",","")." kg");
            
            $this->pdf->setfont("arial","BU",20);
            $this->pdf->setXY($pos[0]+64.3,$pos[1]+40.4);
            $this->pdf->Rotate(-90,$pos[0]+62.3,$pos[1]+40.4);
            $this->pdf->cell(20,4,$this->agence_livr);
            $this->pdf->Rotate(0);
            
            $this->pdf->setfont("arial","B",26);
            $this->pdf->setXY($pos[0]+67.8,$pos[1]+12);
            $this->pdf->multicell(30,intval(36/count($this->transit)),implode("\n",$this->transit));
            
            $this->pdf->setfont("arial","B",20);
            $this->pdf->setXY($pos[0]+67.8,$pos[1]+56.6);
            $this->pdf->cell(30,5,$this->mode_livraison);
            
            $this->pdf->setXY($pos[0]+80,$pos[1]+102);
            $this->pdf->cell(30,5,$this->typetrsptlabel);
            
            $this->pdf->setfont("arial","",10);
            $barcode=$this->barcode($colisid);
            barcode::fpdf($this->pdf,"#000000",$pos[0]+52,$pos[1]+80.6,0,"code128",$barcode,0.3,22);
            $this->pdf->setXY($pos[0]+19.6,$pos[1]+94);
            $this->pdf->cell(50,4,$barcode);
            
            $this->pdf->setfont("arial","B",10);
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+100);
            $this->pdf->cell(50,4,"SCHENKER FRANCE");
            
            $this->pdf->setfont("arial","",8);
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+103.5);
            $this->pdf->multicell(50,3,utf8_decode($this->agences[$this->agence]["nom"]."\nTél: ".$this->agences[$this->agence]["telephone"]));
            break;
            
            case "BELT":
            if($colisid%4==1) $this->pdf->addpage("P","A4");
            $this->pdf->setfont('arial','',8);
            $pos=array(5,10);
            if($colisid%2==0) $pos[0]+=100;
            if(ceil($colisid/2)%2==0) $pos[1]+=125;
            
            $this->pdf->line($pos[0]+8.4,$pos[1]+9,$pos[0]+98,$pos[1]+9);
            $this->pdf->line($pos[0]+8.4,$pos[1]+26.4,$pos[0]+66.8,$pos[1]+26.4);
            $this->pdf->line($pos[0]+8.4,$pos[1]+41.4,$pos[0]+51.6,$pos[1]+41.4);
            $this->pdf->line($pos[0]+8.4,$pos[1]+67.3,$pos[0]+98.9,$pos[1]+67.3);
            $this->pdf->line($pos[0]+51.6,$pos[1]+26.4,$pos[0]+51.6,$pos[1]+67.3);
            $this->pdf->line($pos[0]+66.8,$pos[1]+9,$pos[0]+66.8,$pos[1]+67.3);
            $this->pdf->line($pos[0]+66.8,$pos[1]+46.5,$pos[0]+98.9,$pos[1]+46.5);
            $this->pdf->line($pos[0]+8.4,$pos[1]+99.6,$pos[0]+98,$pos[1]+99.6);
            
            $this->pdf->setfont("arial","",7);
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+0.3);
            $this->pdf->cell(10,4,"Exp:");
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+8.8);
            $this->pdf->cell(10,4,"Rec:");
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+25.7);
            $this->pdf->cell(10,4,"Instr:");
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+41.4);
            $this->pdf->cell(10,4,"Shipment:");
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+46.2);
            $this->pdf->cell(10,4,"Package");
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+51);
            $this->pdf->cell(10,4,"Weight:");
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+56.5);
            $this->pdf->multicell(14,2.5,"Support");
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+62);
            $this->pdf->cell(10,4,"Delivery:");
            
            $this->pdf->setfont("arial","B",8);
            $this->pdf->setXY($pos[0]+17.5,$pos[1]+0.3);
            //$this->pdf->cell(40,4,utf8_decode($this->exp_raisonsociale));
            $this->pdf->cell(40,4,"ACILYON");
            $this->pdf->setXY($pos[0]+76.7,$pos[1]+3.8);
            $this->pdf->cell(20,4,$this->facturid);
            $this->pdf->setfont("arial","",8);
            $this->pdf->setXY($pos[0]+17.5,$pos[1]+5.3);
            //$this->pdf->cell(40,4,utf8_decode($this->exp_pays." ".$this->exp_codepostal." ".$this->exp_ville));
            $this->pdf->cell(40,4,"FR 69003 LYON");
            
            $this->pdf->setfont("arial","B",8);
            $this->pdf->setXY($pos[0]+16,$pos[1]+9);
            $this->pdf->cell(50,4,trim(utf8_decode($this->raison_sociale." ".$this->contact)));
            $this->pdf->setXY($pos[0]+11.7,$pos[1]+18);
            $this->pdf->cell(55,4,utf8_decode($this->pays." ".$this->codepostal." ".$this->ville));
            $this->pdf->setfont("arial","",8);
            $this->pdf->setXY($pos[0]+16,$pos[1]+13.5);
            $this->pdf->cell(50,4,utf8_decode($this->adresse));
            $this->pdf->setXY($pos[0]+16,$pos[1]+23);
            $this->pdf->cell(50,4,utf8_decode('Tel: '. $this->telephone));
            
            $this->pdf->setfont("arial","",7);
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+27.9);
            $this->pdf->multicell(42,4,$this->commentaires);
            
            $this->pdf->setfont("arial","B",8);
            $this->pdf->setXY($pos[0]+29,$pos[1]+41.4);
            $this->pdf->cell(25,4,$this->adv->id*101);
            $this->pdf->setXY($pos[0]+29,$pos[1]+46);
            $this->pdf->cell(10,4,$colisid."/".count($this->colis));
            $this->pdf->setfont("arial","",8);
            $this->pdf->setXY($pos[0]+29,$pos[1]+56.1);
            if(!is_null($this->date_imperative)){
                $this->pdf->setXY($pos[0]+29,$pos[1]+62);
                $this->pdf->cell(25,4,$this->date_imperative->format("d/m/Y"));
            }
            $this->pdf->setXY($pos[0]+29,$pos[1]+51);
            $this->pdf->cell(25,4,number_format($this->poids,3,",","")." kg");
            
            $this->pdf->setfont("arial","BU",20);
            $this->pdf->setXY($pos[0]+64.3,$pos[1]+40.4);
            $this->pdf->Rotate(-90,$pos[0]+62.3,$pos[1]+40.4);
            $this->pdf->cell(20,4,$this->agence_livr);
            $this->pdf->Rotate(0);
            
            $this->pdf->setfont("arial","B",26);
            $this->pdf->setXY($pos[0]+67.8,$pos[1]+12);
            $this->pdf->multicell(30,intval(36/count($this->transit)),implode("\n",$this->transit));
            
            $this->pdf->setfont("arial","B",18);
            $this->pdf->setXY($pos[0]+67.8,$pos[1]+56.6);
            $this->pdf->cell(30,5,$this->mode_livraison);
            
            $this->pdf->setXY($pos[0]+58,$pos[1]+102);
            $this->pdf->cell(18,4,"BELT");
            $this->pdf->setXY($pos[0]+80,$pos[1]+100.5);
            $this->pdf->SetLineWidth(2);
            $this->pdf->cell(18,8,$this->typetrsptlabel,1,0,"C");
            $this->pdf->SetLineWidth(0.2);
            
            $this->pdf->setfont("arial","",10);
            $barcode=$this->barcode($colisid);
            barcode::fpdf($this->pdf,"#000000",$pos[0]+52,$pos[1]+84.6,0,"code128",$barcode,0.5,28);
            
            $this->pdf->setfont("arial","",6);
            $this->pdf->setXY($pos[0]+93,$pos[1]+100);
            $this->pdf->Rotate(90);
            $this->pdf->multicell(50,3,"Unique ID :\n".$barcode);
            $this->pdf->Rotate(0);
            
            $this->pdf->setfont("arial","B",10);
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+100);
            $this->pdf->cell(50,4,"SCHENKER FRANCE");
            
            $this->pdf->setfont("arial","",8);
            $this->pdf->setXY($pos[0]+8.4,$pos[1]+103.5);
            $this->pdf->multicell(50,3,utf8_decode($this->agences[$this->agence]["nom"]."\nTél: ".$this->agences[$this->agence]["telephone"]));
            break;
        }
    }
    
    function update_plan(){
        $this->ptfile=__DIR__."/PTschenker";
        $conn=ftp_connect($this->ftp["plan"]["host"]);
        if($conn){
            if(ftp_login($conn,$this->ftp["plan"]["login"],$this->ftp["plan"]["pass"])){
                ftp_pasv($conn, true);
                $tmp="/tmp/DBSC69.gz";
                if(ftp_get($conn,$tmp,"/from_schkfr/DBSC69.gz",FTP_BINARY)){
                    $gz = gzopen($tmp, 'rb');
                    if (!$gz) {
                        $this->error='Could not open gzip file';
                        return false;
                    }
                    $dest = fopen($this->ptfile, 'wb');
                    if (!$dest) {
                        gzclose($gz);
                        $this->error="Could not open destination file";
                        return false;
                    }
                
                    stream_copy_to_stream($gz, $dest);
                    gzclose($gz);
                    fclose($dest);
                    unlink($tmp);
                }
                
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
    
    function testftp(){
        
        $conn=ftp_connect($this->ftp_host);
        if($conn){
            if(ftp_login($conn,$this->ftp_login,$this->ftp_pass)){
                $return=ftp_put($conn,"test.txt",__DIR__."/test.txt",FTP_BINARY);
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
    
    function send($test=false){
        $mode=$test?"test":"prod";
        $filename="IECSV_ACIL".date("YmdHis")."_".$this->adv->id.".txt";
        $path=dirname(dirname(__DIR__))."/configurations/".$this->host."/transporteurs/schenker/out/".$filename;
        file_put_contents($path,$this->encode());
        $conn=ftp_connect($this->ftp[$mode]["host"]);
        if($conn){
            if(ftp_login($conn,$this->ftp[$mode]["login"],$this->ftp[$mode]["pass"])){
                ftp_pasv($conn,true);
                $return=ftp_put($conn,"from_acilyon/".$filename,$path,FTP_ASCII);
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
    
    function resend($files){
        $mode="prod";
        $conn=ftp_connect($this->ftp[$mode]["host"]);
        if($conn){
            if(ftp_login($conn,$this->ftp[$mode]["login"],$this->ftp[$mode]["pass"])){
                ftp_pasv($conn,true);
                foreach($files as $file){
                    $filename=pathinfo($file,PATHINFO_BASENAME);
//                     print "put ".$file." > from_acilyon/".$filename;
                    $return=ftp_put($conn,"from_acilyon/".$filename,$file,FTP_ASCII);
                }
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
    
    function enc_field($str){
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
        $str=str_replace('’',"'",$str);
        $str=preg_replace('/[[:^print:]]/', '',$str);
        $str=iconv("UTF-8","ASCII",$str);
        
        return $str;
    }
}

?>
