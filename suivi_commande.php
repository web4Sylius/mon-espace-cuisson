<?php


require ("global.php");

if(!isset($_GET['facturid'])){
    header("location:/");
    exit;
}

$facture=new facture($DB_site,$_GET['facturid']);
if($_GET['suiviid']!=$facture->suiviId()) exit;

if(!in_array($facture->valide,array(1,5,7,8))) exit;

$template->parts["facturid"]=$facture->facturid;
$template->parts["livraison"]="<span class=\"suivimea\">".$facture->codepostal." ".$facture->ville."</span> ".$facture->pays;
$template->parts["date_valid"]=date("d/m/Y",$facture->datecommande);

$envois=array();

$stock=new stock($DB_site);
$lieuxstock=$stock->lieux();

$infosacilyon=$DB_site->query_first("SELECT contact".$facture->utilisateur->langue." AS contact, infos".$facture->utilisateur->langue." AS infos FROM fournisseur_livraison WHERE societe='acilyon'");

$req=$DB_site->query("SELECT * FROM envoi_fournisseur WHERE destinataire!='0' AND destinataire!='' AND livr>-1 AND facturid=".$facture->facturid." AND type IN('fourn','stock','trspt','sls') ORDER BY type DESC");
while($envoi=$DB_site->fetch_array($req)){
    
    if(isset($lieuxstock[$envoi['livr']])) continue;
    $req2=$DB_site->query("SELECT ligne_facture_id,envoi_fournisseur_ligne.qte,lignefacture.artcode FROM envoi_fournisseur_ligne INNER JOIN lignefacture ON(ligne_facture_id=lignefactureid) WHERE efid=".$envoi['id']." AND artid>0");

    $lfids=array();
    while($efl=$DB_site->fetch_array($req2)){
        $lfids[$efl['ligne_facture_id']]=$efl['qte'];
    }
    
    $ef=md5(array_sum(array_keys($lfids)));
    if(!isset($envois[$ef])) $envois[$ef]=array();
    $envois[$ef]['lfids']=$lfids;

    switch($envoi['type']){
        case "fourn":
        case "stock":
        if($envoi['trspt']=="transporteur fournisseur"){
            $envois[$ef]['trsptenlev']=$envoi['enlevement'];
            $envois[$ef]['trsptlivr']=$envoi['livraison'];
        }
        break;
    
        case "trspt":
        $envois[$ef]['trsptenlev']=$envoi['enlevement'];
        $envois[$ef]['trsptlivr']=$envoi['livraison'];
        $envois[$ef]['infos']=$DB_site->query_first("SELECT contact".$facture->utilisateur->langue." AS contact, infos".$facture->utilisateur->langue." AS infos FROM fournisseur_livraison WHERE societe='".mysqli_real_escape_string($DB_site->link_id,$envoi['destinataire'])."'");
        $envois[$ef]['trackid']=$envoi['trackid'];
        break;
        
        case "sls":
        /*if($envoi['enlev']>0){
            $fourn=$DB_site->query_first("SELECT type FROM fournisseur WHERE id=".$envoi['enlev']);
            if($fourn['type']!="trspt"){
                $envois[$ef]['trsptenlev']=$envoi['enlevement'];
                $envois[$ef]['trsptlivr']=$envoi['livraison'];
            }else
                $envois[$ef]['slslivr']=$envoi['livraison'];
        }else*/
            $envois[$ef]['slsenlev']=$envoi['enlevement'];
            $envois[$ef]['slslivr']=$envoi['livraison'];
            $envois[$ef]['infos']=$DB_site->query_first("SELECT contact".$facture->utilisateur->langue." AS contact, infos".$facture->utilisateur->langue." AS infos FROM fournisseur_livraison WHERE societe='".mysqli_real_escape_string($DB_site->link_id,$envoi['destinataire'])."'");
        break;
    }
}


$lfids=array();
$i=1;
$nbEnvois = count($envois);

if($nbEnvois>0){
    foreach($envois as $ef=>$envoi){
        $template->parts["articles"]="";
        $template->parts["validation"]=$template->parts["transporteur"]=$template->parts["installateur"]=$template->parts["destinataire"]=$template->parts["infoslivraison"]="";
        
        foreach($envoi['lfids'] as $lfid=>$qte){
            $lfids[]=$lfid;
        
            $lignefacture=new lignefacture($DB_site,$lfid);
            $image="<img src=\"/images/".$lignefacture->artid."_47_84.jpg\">";
            
            $template->parts["articles"].="<div class=\"suivi_article\">"
            .$image
            ."<div class=\"libelle\">"
            .$lignefacture->libelle
            ."<span class=\"marque\">".$lignefacture->marque."</span>
            <span class=\"ref\">".$lignefacture->artcode."</span>"
            .$template->translate("/client/qte")." : ".$qte
            ."</div>
            <div class=\"clear\"></div>
            </div>";
        }
        
        $template->parts["classinstall"]=$template->parts["classdest"]=$template->parts["classtrspt"]=$template->parts["classvalid"]="";
        
        $template->parts["classvalid"]="waiting";
        if($_COOKIE['admin_username']=='jessica'){
            var_dump($envoi['trsptenlev']);
        }
        if(!is_null($envoi['trsptenlev'])){ // transport commandé
            if($envoi['trsptenlev']>0 || $envoi['trsptlivr']>0){ // expédié ou livré
                $template->parts["classvalid"]="";
                if($envoi['trsptenlev']>0){
                    $template->parts["date_trspt"]=date("d/m/Y",$envoi['trsptenlev']);
                    $template->parts["classtrspt"]=$envoi['trsptlivr']==0?"shipping":"";
                }
                if($envoi['trsptlivr']>0){ // livré
                    if(is_null($envoi['slslivr'])){ // pas d'installateur
                        $template->parts["date_dest"]=date("d/m/Y",$envoi['trsptlivr']);
                    }else{ // installateur commandé
                        if($envoi['slslivr']==0){ // pas livré
                            $template->parts["classinstall"]="shipping";
                            $template->parts["classdest"]="waiting";
                        }else{ // livré
                            $template->parts["date_dest"]=date("d/m/Y",$envoi['slslivr']);
                        }
                        $template->parts["date_install"]=date("d/m/Y",$envoi['trsptlivr']);
                        $template->evaltemplate("SuiviCmdInstall","installateur");
                    }
                }else{ // pas livré
                    $template->parts["classdest"]="waiting";
                    if(!is_null($envoi['slslivr'])){ // installateur en attente
                        $template->parts["classinstall"]="waiting";
                        $template->parts["classdest"]="waiting";
                        $template->evaltemplate("SuiviCmdInstall","installateur");
                    }
                }
            }else{ // pas expédié
                $template->parts["classtrspt"]=$template->parts["classdest"]="waiting";
            }
            $template->evaltemplate("SuiviCmdTrspt","transporteur");
        }else{ // pas de transport
        
            if(!is_null($envoi['slslivr'])){ // enlevement par installateur
                if($envoi['slsenlev']==0){ // pas enlevé
                    $template->parts["classdest"]="waiting";
                    $template->parts["classinstall"]="waiting";
                }else{ // enlevé par l'installateur
                    $template->parts["classvalid"]="";
                    if($envoi['slslivr']==0){ // pas livré
                        $template->parts["classinstall"]="shipping";
                        $template->parts["classdest"]="waiting";
                    }else{ // livré
                        $template->parts["date_dest"]=date("d/m/Y",$envoi['slslivr']);
                    }
                    $template->parts["classvalid"]="";
                    $template->parts["date_install"]=date("d/m/Y",$envoi['slsenlev']);
                }
                $template->evaltemplate("SuiviCmdInstall","installateur");
            }else
                $template->parts["classtrspt"]=$template->parts["classdest"]="waiting";

        }

        $template->parts['colisnum'] = ": ".$template->translate("/client/expedition_n")." ".$i;
        if($i <= $nbEnvois && $i > 1) $template->parts["sep"] = "<br><br><div class=\"col-md-offset-2 col-md-8 sep\"></div>";
            
        $template->parts["suivi_infos"]=$template->parts["suivi_contact"]="";
        if(isset($envoi['trackid'])){
            $template->parts["trackid"]=$envoi['trackid'];
            $template->parts["suivi_contact"]=sprintf("<h4>%s%s</h4>",$template->translate("/client/expedition_n"),$envoi['trackid']);
        }
        $infoscontact=isset($envoi['infos']) ? $envoi['infos'] : $infosacilyon;
        $template->evalvartemplate($infoscontact['contact'],"suivi_contact");
        $template->evalvartemplate($infoscontact['infos'],"suivi_infos");
        
        $template->evaltemplate("SuiviCmdInfos","infoslivraison");
        $template->evaltemplate("SuiviCmdValidation","validation");
        $template->evaltemplate("SuiviCmdDest","destinataire");
        $template->evaltemplate("SuiviCmdArticles","content");
        $i++;
    }
}


$template->parts["articles"]=$template->parts["validation"]=$template->parts["transporteur"]=$template->parts["installateur"]=$template->parts["destinataire"]=$template->parts["infoslivraison"]="";
foreach($facture->lignes as $lfid=>$lignefacture){
    if(!in_array($lfid,$lfids) && $lignefacture->artcode!="" && $lignefacture->artid>0){
        $reqnocmd=$DB_site->query_first("SELECT COUNT(destinataire) FROM envoi_fournisseur ef INNER JOIN envoi_fournisseur_ligne efl ON ( efl.efid = ef.id ) INNER JOIN lignefacture ON(ligne_facture_id=lignefactureid) WHERE ligne_facture_id =".$lfid." AND destinataire = '0' AND ef.type IN ( 'fourn', 'trspt', 'sls' )");
        if($_SERVER['REMOTE_ADDR']=="80.12.82.212")var_dump($lfid,$reqnocmd);
        $image="<img src=\"/images/".$lignefacture->artid."_47_84.jpg\">";
        
        $template->parts["articles"].="<div class=\"suivi_article\">"
        .$image
        ."<div class=\"libelle\">"
        .$lignefacture->libelle
        ."<span class=\"marque\">".$lignefacture->marque."</span>
        <span class=\"ref\">".$lignefacture->artcode."</span>"
        .$template->translate("/client/qte")." : ".$lignefacture->qte
        ."</div>
        <div class=\"clear\"></div>
        </div>";
    }
}

if($template->parts["articles"]!=""){
    $template->parts['colisnum'] = ": ".$template->translate("/client/expedition_n")." ".$i;

    if($reqnocmd[0]!=3)
        $template->parts["classvalid"]="waiting";
    else
        $template->evaltemplate("SuiviCmdDest","destinataire");
    $template->evaltemplate("SuiviCmdValidation","validation");
    $template->evaltemplate("SuiviCmdArticles","content");
}

$template->evaltemplate("SuiviCmd","main");
$template->out();

?>