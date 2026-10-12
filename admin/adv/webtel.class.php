<?php

class webtel {

    private $DB;
    private $adv;

    public $filename;
    public $ext;
    public $dossier;
    public $lignes;

    public $date_obj;
    public $date_exp;
    public $particulier;
    public $annonce;
    public $raison_sociale;
    public $compl_nom;
    public $adresse;
    public $codepostal;
    public $ville;
    public $pays;
    public $commentaires;
    public $telephone;
    public $userid;
    public $email;

    public $trspttype;
    private $aTypeTrspt = array(
            "heppner" => array(
                    "rdvpro"      => 'RDV',
                    "rdvpart"     => 'PAR',
                    "stardate"    => 'D18',
                    "stardate13h" => 'D13',
                    "starprio"    => 'P18',
                    "starprio13h" => 'P13'
            ),
            "schenkernat" => array(
                    "standard" => '01',
                    "top18"    => '13',
                    "top13"    => '12'
            )
    );
    
    private $national=array(57,181,111);

    public $ctr;
    public $valeur_declaree;

    public $date_imperative;
    public $nb_manu;
    public $nb_manu_consigne;
    public $manu_consigne_type;
    public $nb_colis;

    public $info_trspt;
    public $info_comp;
    public $poids;
    public $poidsnet;
    public $volume;

    public $facturid;

    function __construct($DB,$adv,$trspt) {

        $this->DB  = $DB;
        $this->adv = $adv;
        $this->trspt=$trspt;

        $this->facturid = $adv->facturid;
        $this->date_obj = date_create_from_format("j/n/Y",$adv->fields['trsptenlfourndate']);
        $this->date_obj = $this->date_obj == false ? date_create() : $this->date_obj;
        $this->date_exp = $this->date_obj->format("Ymd");
		
        $facture = new facture($this->DB,$this->facturid);
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
    }
    
    function from_facture($facture){
    
        if($this->adv->fields['trsptotherdate'] != '') $aDate = explode('/',$this->adv->fields['trsptotherdate']);
        if(isset($aDate[1]) && strlen($aDate[1]) == 1) $aDate[1] = '0'.$aDate[1];
        if(isset($aDate[0]) && strlen($aDate[0]) == 1) $aDate[0] = '0'.$aDate[0];
        $this->date_imperative = $this->adv->fields['trsptdatemax'] == 'trsptdateauto' && isset($aDate) ? $aDate[2].$aDate[1].$aDate[0] : '';

        if(in_array($this->adv->fields['trsptlivr'], array('client','societe')) == 'client'){
                $this->particulier    = $facture->raisonsociale != '' || $this->date_imperative!="" ? 'N' : 'O';
                $this->annonce        = 'A';
                $this->raison_sociale = ($facture->raisonsociale != '' ? $facture->raisonsociale : $facture->nom.' '.$facture->prenom);
                $this->compl_nom      = ($facture->raisonsociale != '' ? $facture->nom.' '.$facture->prenom : '');
                $this->interlocuteur  = $facture->nom." ".$facture->prenom;
                $this->adresse        = $facture->adresse;
                $this->codepostal     = $facture->codepostal;
                $this->ville          = $this->cleanVille($facture->ville);
                $this->pays           = $facture->diminutif;
                $this->portable       = $facture->utilisateur->telephone;
                if($this->portable == 'inconnu') $this->portable = '';
                $this->telephone      = $facture->telephone != '' && $facture->telephone != 'inconnu' ? $facture->telephone : $this->portable;			
                $this->email          = $facture->mail;

                //Instructions transmises dans le cas d'un destinataire particulier
                if($this->particulier == 'O'){
                        $this->info_trspt = $facture->infotranspo;
                } else {
                        $this->info_trspt = $facture->nom." ".$facture->prenom.($this->telephone != '' ? ' - '.$this->telephone : '').($facture->infotranspo != '' ? ' - '.$facture->infotranspo : '');
                }
                
                $this->export=!in_array($facture->paysid,$this->national);

        } else {
                $this->particulier = 'N';
                $this->annonce     = '';

                if(strpos($this->adv->fields['trsptlivr'],'sls') !== false) {
                        $fournisseur_id = substr($this->adv->fields['trsptlivr'],3);
//                         $tel = '';
//                         if($facture->utilisateur->telephone != '' && $facture->utilisateur->telephone != 'inconnu') $tel = $facture->utilisateur->telephone;
//                         if($facture->telephone != '' && $facture->telephone != 'inconnu') $tel = $facture->telephone;
                        $this->info_trspt = "CDE pour " . $facture->nom . " " . $facture->prenom . " - " . $facture->adresse . " - " . $facture->codepostal . " " . $this->cleanVille($facture->ville) ;
                } else if(strpos($this->adv->fields['trsptlivr'],'stock') !== false){
                        $fournisseur_id = substr($this->adv->fields['trsptlivr'],5);
                        $this->info_trspt = '';
                }

                $sql = "SELECT f.societe, f.nom, f.mail, f.telephone, f.adresse1, f.cp, f.ville, p.diminutif, p.paysid";
                $sql .= " FROM fournisseur f";
                $sql .= " INNER JOIN pays p ON (p.paysid = f.paysid)";
                $sql .= " WHERE f.id = ".$fournisseur_id;
                $aFourn = $this->DB->query_first($sql);

                $this->raison_sociale = ($aFourn['societe'] != '' ? $aFourn['societe'] : $aFourn['nom'].' '.$aFourn['prenom']);
                $this->compl_nom      = ($aFourn['societe'] == '' ? $aFourn['nom'].' '.$aFourn['prenom'] : '');
                $this->interlocuteur  = '';
                $this->adresse        = $aFourn['adresse1'];
                $this->codepostal     = $aFourn['cp'];
                $this->ville          = $this->cleanVille($aFourn['ville']);
                $this->pays           = $aFourn['diminutif'];
                $this->telephone      = $aFourn['telephone'];
                $this->portable		  = '';
                $this->email          = $aFourn['mail'];
                
                $this->export=!in_array($aFourn['paysid'],$this->national);
        }

        $this->commentaires    = $facture->infotranspo;
        $this->trspttype       = isset($this->aTypeTrspt[$this->trspt][$this->adv->fields['trspttype']]) ? $this->aTypeTrspt[$this->trspt][$this->adv->fields['trspttype']] : '';
        $this->ctr             = $this->adv->fields['trspt_ctr_remb'] == 1 && $this->adv->fields['ctr_remb_montant'] > 0 ? str_replace(',','.',$this->adv->fields['ctr_remb_montant']) : '';
        //optname[4] pour Schenker, mais sûrement différent pour les autres, à vérifier !
        $this->valeur_declaree = isset($this->adv->fields['optname[4]']) && $this->adv->fields['optname[4]'] != '' ? str_replace(',','.',$this->adv->fields['valeurdeclaree']) : ''; 	

        $this->setManutention();

        $this->info_comp  = $this->adv->fields['infotrspt'] == 'other' ? $this->adv->fields['othertrsptinfo'] : '';

        //Cas particulier HEPPNER
        if($this->trspt == 'heppner'){ 
                if(strtoupper($this->ville) == 'PARIS' || strtoupper($this->ville) == 'LYON' || strtoupper($this->ville) == 'MARSEILLE') $this->ville = $this->ville.' '.substr($this->codepostal,-2); //Arrondissement à préciser
                $this->ctr = $this->ctr != '' ? $this->ctr * 100 : ''; //Format de valeur déclarée différent de chez Schenker
                $this->valeur_declaree = 0;
                $this->valeur_declaree = isset($this->adv->fields['optname[1]']) && $this->adv->fields['optname[1]'] != '' ? $this->adv->fields['valeurdeclaree'] * 100 : ''; //Format de valeur déclarée différent de chez Schenker
                if($this->adv->fields['manutentionconsignetype'] == 'PAL') $this->manu_consigne_type = 'PAE'; //Palette Europe chez Heppner
                $this->interlocuteur = '';
                switch($this->trspttype){
                        case 'P18':
                        case 'P13':
                        case 'PAR':
                        case 'RDV':
                                $this->trspttypecontact = $this->telephone;
                                break;
                        default :
                                $this->trspttypecontact = '';
                                break;
                }
        }

        $this->poids=0;
        $this->volume=0;
        
        $natures=array();
        $req=$this->DB->query("SELECT * FROM typearticle");
        while($t=$this->DB->fetch_array($req))
            $typesarticle[$t['typearticleid']]=$t['libelle'];
        
        foreach($this->adv->lignes as $lid=>$qte){
            $sql = "SELECT a.*,(a.emballage_poids1+a.emballage_poids2+a.emballage_poids3) as emballage_poids,(a.emballage_hauteur1+a.emballage_hauteur2+a.emballage_hauteur3) as emballage_hauteur, (a.emballage_largeur1+a.emballage_largeur2+a.emballage_largeur3) as emballage_largeur,  (a.emballage_profondeur1+a.emballage_profondeur2+a.emballage_profondeur3) as emballage_profondeur";
            $sql .= " FROM lignefacture l, article a";
            $sql .= " WHERE l.artid = a.artid";
            $sql .= " AND lignefactureid = ".$lid;
                
            $lignefacture = $this->DB->query_first($sql);
            $this->poids += $qte * $lignefacture['emballage_poids'];
            $this->poidsnet += $qte * ($lignefacture['poids']/1000);
            $this->volume += $qte * ($lignefacture['emballage_hauteur']*$lignefacture['emballage_largeur']*$lignefacture['emballage_profondeur'])/pow(10,6);
            $this->nb_colis = $this->nb_colis - $this->nb_manu_consigne;
            $natures[]=$typesarticle[$lignefacture['typearticleid']];
        }
        $this->nature=implode(",",array_unique($natures));
        $this->volume=round($this->volume);
    }
    
    function from_stock($stock_cmd){
        $lieu=$this->DB->query_first("SELECT fournisseur.*,pays.diminutif,pays.libelle AS pays,pays.paysid FROM fournisseur INNER JOIN pays USING(paysid) WHERE id=".$stock_cmd->livraison);

        $this->particulier    = 'N';
        $this->annonce        = 'A';
        $this->raison_sociale = $lieu['societe'];
        $this->compl_nom      = $lieu['nom']." ".$lieu['prenom'];
        $this->interlocuteur  = $lieu['nom']." ".$lieu['prenom'];
        $this->adresse        = $lieu['adresse1'];
        $this->codepostal     = $lieu['cp'];
        $this->ville          = $this->cleanVille($lieu['ville']);
        $this->pays           = $lieu['diminutif'];
        $this->portable       = $lieu['portable'];
        $this->telephone      = $lieu['telephone'];			
        $this->email          = $lieu['mail'];
        
        $this->export=!in_array($lieu['paysid'],$this->national);
        
        $this->info_trspt = "";
        $this->commentaires    = "";
        
        $this->trspttype       = isset($this->aTypeTrspt[$this->trspt][$this->adv->fields['trspttype']]) ? $this->aTypeTrspt[$this->trspt][$this->adv->fields['trspttype']] : '';
        $this->ctr             = '';
        //optname[4] pour Schenker, mais sûrement différent pour les autres, à vérifier !
        $this->valeur_declaree = str_replace(',','.',$this->adv->fields['valeurdeclaree']); 	

        $dt=date_create_from_format("d/m/Y",$this->adv->fields['trsptotherdate']);
        $this->date_imperative = $this->adv->fields['trsptdatemax']=="trsptdateauto" ? $dt->format("Ymd") : "";

        $this->setManutention();

        $this->info_comp  = $this->adv->fields['infotrspt'] == 'other' ? $this->adv->fields['othertrsptinfo'] : '';

        //Cas particulier HEPPNER
        if($this->trspt == 'heppner'){ 
                if(strtoupper($this->ville) == 'PARIS' || strtoupper($this->ville) == 'LYON' || strtoupper($this->ville) == 'MARSEILLE') $this->ville = $this->ville.' '.substr($this->codepostal,-2); //Arrondissement à préciser
                $this->ctr = $this->ctr != '' ? $this->ctr * 100 : ''; //Format de valeur déclarée différent de chez Schenker
                $this->valeur_declaree = 0;
                $this->valeur_declaree = isset($this->adv->fields['optname[1]']) && $this->adv->fields['optname[1]'] != '' ? $this->adv->fields['valeurdeclaree'] * 100 : ''; //Format de valeur déclarée différent de chez Schenker
//                 if($this->adv->fields['manutentionconsignetype'] == 'PAL') $this->manu_consigne_type = 'PAE'; //Palette Europe chez Heppner
                $this->interlocuteur = '';
                switch($this->trspttype){
                        case 'P18':
                        case 'P13':
                        case 'PAR':
                        case 'RDV':
                                $this->trspttypecontact = $this->telephone;
                                break;
                        default :
                                $this->trspttypecontact = '';
                                break;
                }
        }

        $natures=array();
        $req=$this->DB->query("SELECT * FROM typearticle");
        while($t=$this->DB->fetch_array($req))
            $typesarticle[$t['typearticleid']]=$t['libelle'];
        
        $this->poids=0;
        foreach($this->adv->lignes as $lid=>$qte){
                $sql = "SELECT a.*";
                $sql .= " FROM article a";
                $sql .= " WHERE a.artid=".$lid;
            $lignefacture = $this->DB->query_first($sql);
            $this->poids += $qte * $lignefacture['emballage_poids'];
            $this->poidsnet += $qte * round($lignefacture['poid']/1000);
            $this->nb_colis = $this->nb_colis - $this->nb_manu_consigne;
            $this->volume += $qte * ($lignefacture['emballage_hauteur']*$lignefacture['emballage_largeur']*$lignefacture['emballage_profondeur'])/pow(10,6);
            $natures[]=$typesarticle[$lignefacture['typearticleid']];
        }
        $this->nature=implode(",",array_unique($natures));
        $this->volume=round($this->volume);
        
    }

    function makeCSV(){

    	$csv =  new xlexport("csv");

		$csv->csvsep          = ";";
		$csv->striptags       = false;
		if(substr(SITE_PATH,-1,1)=="/") $this->dossier=ltrim($this->dossier,"/");
		$csv->filename_source = SITE_PATH.$this->dossier.$this->filename.'.'.$this->ext;

		//Entêtes
		$debut_file = "";
		if($this->csv_exists()){
			$debut_file = "\n";
		}

		$csv->addrow($this->lignes, false, true);

		$f=fopen($csv->filename_source,"ab");
		if($f){
		     fputs($f,$debut_file.utf8_decode($csv->buffer)); //L'interface WEBTEL est encodée en ISO. Problèmes d'accents si on laisse le fichier en UTF8...
		     fclose($f);
		}

		$this->addSuivi();
    }

    function getCSV(){
    	$csv =  new xlexport("csv");

		$csv->csvsep          = ";";
		$csv->striptags       = false;
		$csv->filename_source = SITE_PATH.$this->dossier.$this->filename.'.'.$this->ext;

		//Entêtes
		$debut_file = "";
		if($this->csv_exists()){
			$debut_file = "\n";
		}

		$csv->addrow($this->lignes, false, true);

		header("Content-type: application/force-download");
		header("Content-Disposition: attachment; filename=".$this->filename.'.'.$this->ext);

		echo $debut_file.utf8_decode($csv->buffer);
    }

    function setManutention(){

		$nbColis   = 0;
		$nbPalette = 0;
		$i         = 1;

    	while(array_key_exists('colis_'.$i, $this->adv->fields)){
    		
    		if($this->adv->fields['colistype_'.$i] == 'palette') $nbPalette++;
			$nbColis++;
    		$i++;
    	}
    	
    	if($nbColis == 0) $nbColis = 1;

    	$this->nb_manu            = $nbColis;
		$this->nb_manu_consigne   = $nbPalette > 0 ? $nbPalette : '';
		$this->manu_consigne_type = $nbPalette > 0 ? 'PAL' : '';
		$this->nb_colis           = $nbColis;
		$this->nb_colis_strict    = $nbColis - $nbPalette;
    }

    function csv_exists(){
    	if(file_exists(SITE_PATH.$this->dossier.$this->filename.'.'.$this->ext)) return true;
    	return false;
    }

    function addSuivi(){

        if($this->origin=="cmd")
            $sql = "INSERT INTO suivi_commande SET";
                    $sql .= " type = ''";
                    $sql .= ", destinataire = 0";
                    $sql .= ", moyen = ''";
                    $sql .= ", facturid = ".$this->facturid;
                    $sql .= ", date = ".time();
                    $sql .= ", commercial = '".$_COOKIE['admin_username']."'";
                    $sql .= ", suivi = 'Création du fichier pour station chargeur'";
                    $sql .= ", rappel = 0";
                    $sql .= ", rappeltype = ''";
                    $sql .= ", jour = 0";
                    $sql .= ", pour = ''";
                    $sql .= ", hide = 0";
                    
        if($this->origin=="stock")
            $sql="INSERT INTO suivi_stock SET
            stock_cmd_id=".$this->facturid.",
            date=".time().",
            commercial='".$_COOKIE['admin_username']."',
            suivi='Création du fichier pour station chargeur'";

        if(!is_null($sql))
		return $this->DB->query($sql);
    }

    function cleanVille($ville){ //A perfectionner, les accents foirent
    	//$ville = strtr($ville,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    	$ville = str_replace('-',' ',$ville);
    	$ville = str_replace('\'',' ',$ville);
    	return $ville;
    }
}