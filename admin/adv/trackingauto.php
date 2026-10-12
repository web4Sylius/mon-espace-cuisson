<?php

ini_set("display_errors",1);

include_once("../host_config_admin.php");
include($rootpath."class/tnt.class.php");
include($rootpath."class/facture.class.php");
include($rootpath."admin/adv/adv.class.php");
include($rootpath."admin/adv/heppner.class.php");
//include($rootpath."admin/adv/schenker.class.php");
include($rootpath."admin/adv/girard_agediss.class.php");
include($rootpath."admin/adv/gls.class.php");

$DB_site=clone $DB_site2;

$trackgls=$tracktnt=$trackheppner=$trackschenker=$trackgirard=false;

/*if(count($argv)==1){*/
    $trackgls=true;
    $tracktnt=true;
    $trackheppner=true;
    $trackschenker=true;
    $trackgirard=false;
/*}else{
    for($i=1;$i<count($argv);$i++)
        ${"track".strtolower($argv[$i])}=true;
}*/


if($tracktnt){
    include_once($rootpath."configurations/".$host."/transporteurs/tnt/tnt.php");
    print "FEDEX Express\n";
    $sql="SELECT * FROM envoi_fournisseur WHERE destinataire='FEDEX Express' AND enlevement=0 AND LENGTH(trackid)=16";
    $req=$DB_site->query($sql);
    while($ef=$DB_site->fetch_array($req)){
        $facture=new facture($DB_site,$ef['facturid']);
        $tnt=new tnt($DB_site,null,"STDA4",$username,$pass);
        $tracking=$tnt->getTracking($ef['trackid']);
        $parcel=$tracking->Parcel;
        
        print $ef['trackid'];
        if($parcel->events->processDate!=""){
            $processdate=new DateTime($parcel->events->processDate);
            $DB_site->query("UPDATE envoi_fournisseur SET enlevement=".$processdate->getTimestamp()." WHERE id=".$ef['id']);
            print " expédié ".$processdate->format("d/m/Y H:i");
        }
        print "\n";
    }

    $sql="SELECT * FROM envoi_fournisseur WHERE destinataire='FEDEX Express' and livraison=0 AND LENGTH(trackid)=16";
    $req=$DB_site->query($sql);
    while($ef=$DB_site->fetch_array($req)){
        $facture=new facture($DB_site,$ef['facturid']);
        $tnt=new tnt($DB_site,null,"STDA4",$username,$pass);
        $tracking=$tnt->getTracking($ef['trackid']);
        
        print $ef['trackid'];
        $parcel=$tracking->Parcel;
        if($parcel->events->deliveryDate!=""){
            $deliverydate=new DateTime($parcel->events->deliveryDate);
            print " livré ".$deliverydate->format("d/m/Y H:i");
            
            $DB_site->query("UPDATE envoi_fournisseur SET livraison=".$deliverydate->getTimestamp()." WHERE id=".$ef['id']);
            $suivi="";
            if(isset($parcel->events->notAtHomeDOPDate)){
                $notathomedate=new DateTime($parcel->events->notAtHomeDOPDate);
                $suivi.="Personne sur le lieu de livraison le ".$notathomedate->format("d/m/Y H:i")."<br>";
            }
            $suivi.="Colis FEDEX Express ".$ef['trackid']." : ".implode("<br>",$parcel->longStatus);
            if(isset($parcel->dropOffPoint)){
                $droppoint=$parcel->dropOffPoint;
                $suivi.="<br>".$droppoint->xETTCode." ".$droppoint->name."<br>".$droppoint->address."<br>".$droppoint->zipCode." ".$droppoint->city;
            }
            $DB_site->query("INSERT INTO suivi_commande SET facturid='".$facture->facturid."', date=".time().", suivi='".mysqli_real_escape_string($DB_site->link_id,$suivi)."'");
            if(isset($parcel->primaryPODUrl)){
                $data=file_get_contents($parcel->primaryPODUrl);
                $DB_site->query("INSERT INTO user_files SET userid='".$facture->userid."', facturid='".$facture->facturid."', date=".time().", name='".mysqli_real_escape_string($DB_site->link_id,"Signature TNT ".$ef['trackid'])."', size=".strlen($data).", ext='png', state=1");
                $fileid=$DB_site->insert_id();
                file_put_contents($rootpath."admin/userfiles/".$fileid.".png",$data);
            }
        }
        print "\n";
    }
}

if($trackgls){
    print "GLS\n";
    $glsdir=$rootpath."/configurations/".$host."/transporteurs/gls/";
    $files=glob($glsdir."/*.txt");
    foreach($files as $file){
        $f=fopen($file,"r");
        while(!feof($f)){
            $line=fgets($f,1024);
            if(trim($line)=="") continue;
            $data=explode(";",$line);
            $trackid=$data[17];
            $facturid=$data[6];
            $deliverydate=new DateTime($data[5]);
            $h=explode(".",$data[16]);
            $deliverydate->settime($h[0],$h[1],$h[2]);
            $state=$data[13];
            $infos=$data[14];
            $suivi="Colis GLS ".$trackid." ".utf8_encode($infos)." ".$deliverydate->format("d/m/Y H:i");
            
            switch($state){
                case "PRIS EN CHARGE":
                    $DB_site->query("UPDATE envoi_fournisseur SET enlevement=".$deliverydate->getTimestamp()." WHERE facturid='".mysqli_real_escape_string($DB_site->link_id,$facturid)."' AND trackid='".mysqli_real_escape_string($DB_site->link_id,$trackid)."'");
                break;
            
                case "LIVRE":
                case "LIVRE.":
                    $DB_site->query("UPDATE envoi_fournisseur SET livraison=".$deliverydate->getTimestamp()." WHERE facturid='".mysqli_real_escape_string($DB_site->link_id,$facturid)."' AND trackid='".mysqli_real_escape_string($DB_site->link_id,$trackid)."'");
                break;
                
                case "INDISPONIBILITE DESTINATAIRE":
                    
                break;
                
                case "RECEPTIONNE":
                    
                break;
                
                case "COLIS NON PRESENTÉ":
                
                break;
                
                case "RETOURS / REEXPEDITIONS":
                
                break;
                
                case "Lu en travée détail":
                
                break;
            }
            
            $DB_site->query("INSERT INTO suivi_commande SET facturid='".$facturid."', date=".time().", suivi='".mysqli_real_escape_string($DB_site->link_id,$suivi)."'");
            print $suivi."\n";
        }
        fclose($f);
        rename($file,$glsdir."/".date("Ymd-His").".old");
//         unlink($file);
    }

    //Tracking avec la nouvelle API
    $sql="SELECT * FROM envoi_fournisseur WHERE destinataire='GLS' AND (enlevement=0 OR livraison=0) AND trackid != ''";
    $req=$DB_site->query($sql);
    while($ef=$DB_site->fetch_array($req)){

        $facture=new facture($DB_site,$ef['facturid']);
        $adv=new adv($DB_site);
        $adv->load_from_efid($ef['id']);
        $gls=new gls($DB_site,$adv,$clientid);

        $tracking=$gls->getTracking($ef['trackid']);
        $facturid = $tracking->ParcelDetailResponse->UnitDetail->ShipmentUnitReference;
        $history = $tracking->ParcelDetailResponse->UnitDetail->History;

            // On boucle sur le suivi du colis
        if(isset($history) && !is_null($history)){
            foreach($history as $history_info){
                $infos = $history_info->Description;
                $deliverydate=new DateTime($history_info->Date);
                $statut = $history_info->StatusCode;

                $suivi="Colis GLS ".$ef['trackid']." ".utf8_encode($infos)." ".$deliverydate->format("d/m/Y H:i");

                if($facturid != '' && !is_null($facturid)){
                        // On vérifie que le suivi n'a pas déjà été fait avant de le créer
                    $suivi_exist = $DB_site->query_first("SELECT * FROM suivi_commande WHERE facturid='".$facturid."' AND suivi='".mysqli_real_escape_string($DB_site->link_id,$suivi)."'");
                    if($suivi_exist == '' || is_null($suivi_exist)){
                        $DB_site->query("INSERT INTO suivi_commande SET facturid='".$facturid."', date=".time().", suivi='".mysqli_real_escape_string($DB_site->link_id,$suivi)."'");

                        switch($statut){
                            case "HUB":
                            $DB_site->query("UPDATE envoi_fournisseur SET enlevement=".$deliverydate->getTimestamp()." WHERE facturid='".mysqli_real_escape_string($DB_site->link_id,$facturid)."' AND trackid='".mysqli_real_escape_string($DB_site->link_id,$ef['trackid'])."'");
                            break;

                            case "DELIVERED":
                            $DB_site->query("UPDATE envoi_fournisseur SET livraison=".$deliverydate->getTimestamp()." WHERE facturid='".mysqli_real_escape_string($DB_site->link_id,$facturid)."' AND trackid='".mysqli_real_escape_string($DB_site->link_id,$ef['trackid'])."'");
                            break;
                        }
                    }
                }
            }
        }
    }
}

//les infos sont enregistrées directement dans la base de donnée pour heppner
if($trackheppner) {
    include($rootpath."configurations/".$host."/transporteurs/heppner/heppner.php");    
    print "Heppner\n";
    $heppner=new heppner($DB_site,null,$ftp,$host);
    print implode("\r\n",$heppner->suivis())."\r\n";
}

if($trackschenker){
    $situations=array(
    "AAR"=>"arrivage",
    "EXP"=>"expédition",
    "LIV"=>"livraison",
    "PCH"=>"prise en charge",
    "POD"=>"signature destinataire",
    "MLV"=>"mise en livraison",
    "REN"=>"refusé");
    
    $justifications=array(
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
    "VVE"=>"volume visiblement erroné");

    $dir=$rootpath."/configurations/".$host."/transporteurs/schenker";
    $files=glob($dir."/REPORT_FRNACILYON_*");
    print "Schenker\n";
    foreach($files as $file){
        $f=fopen($file,"r");
        while(!feof($f)){
            $line=fgets($f,1024);
            if(trim($line)=="") continue;
            $fields=explode(";",$line);
            $facturid=trim($fields[0]);
            $facture=new facture($DB_site,$facturid);
            $trackid=trim($fields[1]);
            if($fields[5]=="") $fields[5]="000000";
            $deliverydate=DateTime::CreateFromFormat("YmdHis",$fields[4].$fields[5]);
            $situation=substr($fields[6],0,3);
            $justification=substr($fields[6],3);
            
            switch($situation){
                case "LIV":
                $DB_site->query("UPDATE envoi_fournisseur SET livraison=".$deliverydate->getTimestamp()." WHERE facturid='".mysqli_real_escape_string($DB_site->link_id,$facturid)."' AND trackid='".mysqli_real_escape_string($DB_site->link_id,$trackid)."'");
                break;
                
                case "PCH":
                $DB_site->query("UPDATE envoi_fournisseur SET enlevement=".$deliverydate->getTimestamp()." WHERE facturid='".mysqli_real_escape_string($DB_site->link_id,$facturid)."' AND trackid='".mysqli_real_escape_string($DB_site->link_id,$trackid)."'");
                break;
                
                case "POD":
                if($fields[10]!=""){
                    $html=file_get_contents($fields[10]);
                    if(preg_match("/data=\"\/([^\"]+)\"/",$html,$m)){
                        $pdfurl=$m[1];
                        if(substr($m[1],4)!="http"){
                            $url=parse_url($fields[10]);
                            $pdfurl=$url['scheme']."://".$url['host']."/".$pdfurl;
                        }
                        $data=file_get_contents($pdfurl);
                        
                        $DB_site->query("INSERT INTO user_files SET userid='".$facture->userid."', facturid='".$facture->facturid."', date=".time().", name='".mysqli_real_escape_string($DB_site->link_id,"Signature Schenker ".$trackid)."', size=".strlen($data).", ext='pdf', state=1");
                        $fileid=$DB_site->insert_id();
                        file_put_contents($rootpath."admin/userfiles/".$fileid.".pdf",$data);
                    }
                }
                break;
            }
            
            $suivi="Colis Schenker ".$trackid." ".$situations[$situation]." ".$justifications[$justification]." ".$deliverydate->format("d/m/Y");
            $DB_site->query("INSERT INTO suivi_commande SET facturid='".$facturid."', date=".time().", suivi='".mysqli_real_escape_string($DB_site->link_id,$suivi)."'");
            print $suivi."\n";
        }
        fclose($f);
        unlink($file);
    }
}

if($trackgirard){
    print "AGEDISS\n";
    $girard=new girard_agediss($DB_site);
    print implode("\r\n",$girard->suivis())."\r\n";
}
