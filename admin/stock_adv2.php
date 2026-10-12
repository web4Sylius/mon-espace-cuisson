<?php


$preview=isset($_GET['preview']);
$advid=$_GET['advid'];
$output=isset($_GET['output'])?$_GET['output']:"html";


require_once("admin_global.php");
include_once("adv/webtel.class.php");
include_once("../class/fpdf.php");
include_once("adv/pdf.class.php");
include_once("../class/php-barcode.php");
include_once("adv/gls.class.php");
include_once("adv/heppner.class.php");
include_once("../class/tnt.class.php");
include_once("export/xlexport.php");

if($preview){
  // require_once "admin_global.php";
  include_once "../class/stock.class.php";
  include_once "../class/template.class.php";
  include_once "adv/adv.class.php";
}

$js_popup_pj="";

// choix du traitement fournisseur ou transporteur
if($actionadv=="advfournisseur" || $actionadv=="advfournisseur2"){
    $adv=new adv($DB_site2,$advid);

    switch($adv->type){
    case "fourn":
      mail_fourn($adv,$preview);
      break;

    case "trspt":
      mail_trspt($adv,$preview);
      break;
    }

    unset($advid);
    unset($type);
}

/************************************************************************** 
 *    function mail fournisseur
 *
 */

function mail_fourn($adv,$preview=false){
    global $DB_site2;
    global $user_info;
    global $devises;
    global $host;
    global $rootpath;
    
    $facturid=$adv->facturid;

    $template = new template("FR",$DB_site2);
    $stock = new stock($DB_site2);
    $stock_cmd = new stock_cmd($DB_site2,$facturid);
    $stock_cmd->items();
    $stock_cmd->groupitems();
    $subject="";
  
    switch($adv->fields['typecmd']){
    case "new":
      $subject="Commande de stock #CDE".$facturid."#";
      $newcmd=true;
      $text.="Bonjour,<br>Veuillez trouver ci-joint une nouvelle commande de stock #CDE$facturid#";
      $fin="Nous vous remercions de votre envoi, par mail ou fax, de votre confirmation de commande.<br>Cordialement<br>$user_info[nom_contact] - ACILYON - www.ma-cave-a-vin.fr<br>Tél : + 33.4.26.03.05.44 -  Fax: +33.4.26.03.05.49";
      break;

    case "delete":
      $subject="Annulation de la commande de stock #CDE".$facturid."#";
      $newcmd=false;
      $text.="Bonjour,<br>Merci d'annuler les produits suivants de la commande de stock #CDE$facturid#";
      $fin="Merci de nous confirmer l'annulation de ces produits par retour de ce mail.<br>Cordialement<br>$user_info[nom_contact] - ACILYON - www.ma-cave-a-vin.fr<br>Tél : + 33.4.26.03.05.44 -  Fax: +33.4.26.03.05.49";
      break;

    case "modif":
      $subject="Modification de la commande de stock #CDE".$facturid."#";
      $ef=new envoi_fournisseur($DB_site2,$adv->efid);
      $newcmd=true;
      $text.="Bonjour,<br>Cette modification de commande annule et remplace la commande de stock #CDE".$facturid."# envoyée par mail le ".date("d/m/Y à H:i",$ef->date);
      $fin="Merci de nous confirmer cette modification par retour de ce mail.<br>Cordialement<br>$user_info[nom_contact] - ACILYON - www.ma-cave-a-vin.fr<br>Tél : + 33.4.26.03.05.44 -  Fax: +33.4.26.03.05.49";
      break;

    default:
      $newcmd=true;
    }

    if($adv->fields['infocomplement']!="")
    $text.="<p>".nl2br($adv->fields['infocomplement'])."</p>";

    $imperatif=0;
    $tsindate=$tsoutdate=0;
  
    if($adv->fields['trsptmode']=="parenlevement"){
    if($adv->fields['parenlevindate']!=""){
      $tsindate=$adv->date2ts($adv->fields['parenlevindate']);
    }
    }elseif($adv->fields['trsptmode']=="parvossoins"){
    if($adv->fields['parvossoinsindate']!=""){
      $tsindate=$adv->date2ts($adv->fields['parvossoinsindate']);
    }
    
    if($adv->fields['parvossoinsoutdate']!=""){
      $tsoutdate=$adv->date2ts($adv->fields['parvossoinsoutdate']);
    } 
    
    if($adv->fields['typedatelivrfourn']=="imperative") $imperatif=1;
    }

    $text.="<p><b>Produits :</b></p>";
    $paht=0;
    $ecopart=0;
    $garantie=0;
    $insertids=array();
    $annultxt;
  
    // tableau liste de produit
    $text .= "\n<table border=\"2\" cellpadding=\"5\" cellspacing=\"1\" width=\"100%\">";
    $text .= "\n<thead><tr><td style=\"font-weight:bold\">Qtités:</td><td style=\"font-weight:bold\">Références:</td><td align=\"left\" style=\"font-weight:bold\">&nbsp;Libelle:</td><td style=\"font-weight:bold\">&nbsp;Prix Total HT:</td></tr></thead>";
    $text .= "\n<tbody>";
    
    // debut boucle articles
    foreach($stock_cmd->items as $item){
    //if($item->state[-1]){
      $text .="\n<tr>";
      $text .="<td align=\"center\" valign=\"center\" width=\"8%\">".$item->qte."</td>";
      // Attention item->ref correspond en fait a artcode_eurocave !!!!
      $text .="<td align=\"center\" valign=\"center\" width=\"12%\">".$item->ref."</td>";
      $text .="<td width=\"65%\">".$item->libelle."<br>";
      
      $nbprix=count($adv->fields['listeprix_'.$item->artid]);
      if($nbprix>0)
      {
        $text.="<u>Prix offre spéciale</u> : ";
        ksort($adv->fields['listeprix_'.$item->artid]);
        $pi=0;
        
        foreach($adv->fields['listeprix_'.$item->artid] as $pahtg=>$qteg){
          $pi++;
          $text.=$qteg."x ".($pahtg>0?number_format($pahtg,2,',','').$devises->symbole($adv->devise):"offert").($pi<$nbprix?" + ":"");
        }
        
        if($item->artecopartht>0) $text.=" + ".$item->qte."x ".$item->artecopartht.$devises->symbole($adv->devise);
        
        $text.="<br><br>";
      }else{
        $text.="<u>Prix unitaire</u> : ".number_format($adv->fields['price1_'.$item->artid],2,',',' ').$devises->symbole($adv->devise)."";
        
        if($item->artecopartht>0) $text.=" + Eco ".number_format($item->artecopartht,3,',',' ').$devises->symbole($adv->devise);
        $text .= "<br><br>";
      }
      
      $text .= "</td>";
     
      $paht+=floatval($item->qte*$adv->fields['price1_'.$item->artid]);
      $ecopart+=$item->qte*$item->artecopartht;
      
      $totLigneHT = floatval($item->qte*$adv->fields['price1_'.$item->artid]) + $item->qte*$item->artecopartht;
      
      $text .= "<td valign=\"center\" align=\"right\" width=\"15%\">".number_format($totLigneHT,2,',',' ')."&nbsp;".$devises->symbole($adv->devise)."</td>"; 
    //}
      $text .= "</tr>";
      
    }
    // fin boucle tableau articles
    

    if($newcmd==true)
    {
      $textInfos.="<p><b>Infos produits</b>";
      
      if($adv->fields['uncolis']==1) $textInfos.="<br>Attention : mettre tous les articles dans 1 seul colis";
      if($adv->fields['palette']==1) $textInfos.="<br>Attention : Article(s) à mettre sur palette + cerclage pour le transport";
      if($adv->fields['suremballage']==1) $textInfos.="<br>Attention :  Prévoir un suremballage du produit + palette + cerclage pour le transport";
      
      $textInfos.="</p>";
  
      if($adv->fields['nopj']!="0"){
        $textInfos.="<p><b>Identification à mettre sur le colis</b>";
        
        if($adv->fields['blpj']==1) $textInfos.="<br>Bon de livraison";
        if($adv->fields['facturepj']==1) $textInfos.="<br>Facture";
        if($adv->fields['glspj']==1) $textInfos.="<br>Bon GLS ou FEDEX Express";
        if($adv->fields['trsptpja4']==1 || $adv->fields['trsptpja5']==1) $textInfos.="<br>Note importante pour le transport<br>Etiquette fragile";
        
        //if($adv->fields['otherpj']==1) $text.="<br>".$_POST['otherpjname'];
        //if(isset($_POST['identpj'])) $text.="<br>Texte sur colis : Commande N° $facturid De Ma cave à vin - ACILYON pour $adv_facture[nom] $adv_facture[prenom] $adv_facture[codepostal] $adv_facture[ville] $adv_facture[libelle]";
        
        $textInfos.="</p>";
      }
  
      $trsptval=0;
      $livrid=-1;
      $textInfos.="<p><b>Délai de mise à disposition / livraison</b><br>";
  
      if($adv->fields['trsptmode']=="parenlevement"){
        $textInfos.="Enlevement à notre charge en vos locaux par ".$adv->fields['selecttrspt']." <span id=\"indate\">le ".$adv->fields['parenlevindate']."</span>";
        if(isset($adv->fields['enlev2sls'])) $blfourn=$adv->fields['enlev2sls'];
        $advtrspt=$adv->fields['selecttrspt'];
      }elseif($adv->fields['trsptmode']=="parvossoins"){
        $trsptval=floatval($adv->fields['mazetcout']);
        $txtlivr="Livraison au plus tard";
        
        if($adv->fields['typedatelivrfourn']=="imperative") $txtlivr="Livraison <u>impérative</u>";
        
        $lieustock=$DB_site2->query_first("SELECT * FROM fournisseur INNER JOIN pays USING(paysid) WHERE id=".$stock_cmd->livraison);
        $textInfos.="Livraison par vos soins par votre transporteur à notre lieu de stockage :
        <br>".$lieustock['societe']."<br>".$lieustock['nom']." ".$lieustock['prenom']."
        <br>".$lieustock['adresse1'].
        ($lieustock['adresse2']!=""?"<br>".$lieustock['adresse2']:"")."
        <br>".$lieustock['cp']." ".$lieustock['ville']."
        <br>".$lieustock['libelle']."
        <br>".$lieustock['telephone']."
        <br><br>Départ maximum : <span id=\"indate\">".$adv->fields['parvossoinsindate']."</span>
        <br>$txtlivr : ".$adv->fields['parvossoinsoutdate'];
      }
    
      if($adv->fields['chkinfotrspt']==1) $textInfos.="<br>Info pour transporteur : ".stripslashes($adv->fields['infofournothertxt']);
      
      $textInfos.="</p>";
        
      $total=$paht+$ecopart+$trsptval+$garantie;
      
      if($adv->fields['lieustock']==0){

        // Preparation recapitulatif ecoparticipation
        $valPaht=number_format($paht,2,',',' ');
        
        if($ecopart!=0) {
          $valEcopart=number_format($ecopart,2,',',' ');
          $txtEcopart="<br>+ (éco-participation)";
        }
        
        // Ajout des lignes transport et garantie dans le tableau qd elle existent + preparation des infos recapitulatif
        if($trsptval!=0) {
          $valTransport = number_format($trsptval,2,',',' ');
          $txtTransport = "<br>+ (transport)";
          $text .= "<tr><td align=\"center\">1</td><td></td><td>"."Transport"."</td><td align=\"right\">".number_format($trsptval,2,',',' ')."&nbsp;".$devises->symbole($adv->devise)."</td>";
        }
        if($garantie!=0)
        { $valGarantie = number_format($garantie,2,',',' ');
          $txtGarantie = "<br>+ (garantie)";
          $text .= "<tr><td align=\"center\">1</td><td></td><td>"."Garantie"."</td><td align=\"right\">".number_format($garantie,2,',',' ')."&nbsp;".$devises->symbole($adv->devise)."</td>";
        }
        
        // Ajout du montant total   
        $text .= "<tr><td colspan=\"3\" valign=\"center\" align=\"right\"><b>MONTANT TOTAL HT&nbsp;</b></td><td align=\"right\"><b>".number_format($total,2,',',' ')."&nbsp;&euro;</b></td></tr>";
        // Ajout du recapitulatif               
        $text .= "<tr><td colspan=\"3\" valign=\"top\" align=\"right\" style=\"color:#666666\"><u>Récapitulatif:</u>&nbsp;&nbsp;"."(articles & options)".$txtEcopart.$txtTransport.$textGuarantie."</td>";
        $text .= "<td valign=\"top\" align=\"right\" style=\"color:#666666\">".$valPaht."<br>".$valEcopart."<br>".$valTransport."<br>".$valGuarantie."</td></tr>";
        
      }
    }
    
    
    $text .= "\n</table>\n</tbody>\n";
    $text.=$textInfos; 

    $text.=$fin;

    if(!$preview){
        if($adv->fields['typecmd']=="delete" || $adv->fields['typecmd']=="modif"){
        }
  
        if($newcmd==true){
            $stock_cmd->destinataire=$adv->destinataire;
            $stock_cmd->enlevement=$adv->fields['selectenlev'];
            $stock_cmd->date=time();
            $stock_cmd->update();
            foreach($stock_cmd->items(-1) as $item){
                $item->paht=$adv->fields['price1_'.$item->artid];
                $item->ecopartht=$item->artecopartht;
                $item->state=0;
                $item->date=time();
                $item->utilisateur=$user_info['username'];
                if($item->date_enlevement==0){
                  $item->date_enlevement_origin=$tsindate;
                }
                if($item->date_livraison==0){
                  $item->date_livraison_origin=$tsoutdate;
                }
                $item->date_enlevement=$tsindate;
                $item->date_livraison=$tsoutdate;
                $item->update();
            }
        }

        $attachements=array();
        $pj=array();
        $mail=new email();
        $mail->destinataire=$adv->fields['fournisseur'];
        $mail->copymode="bcc";
        $mail->to=array("pr@mon-espace-cuisson.com","adv@mon-espace-cuisson.com");
        $mail->expediteur="adv@mon-espace-cuisson.com";
        $mail->expname="Ma Cave à Vin";
        $mail->subject=$subject;
        $mail->body=$text;
        
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );
        
        if($newcmd==true){
            if($adv->fields['blpj']==1){
            $data=file_get_contents(BACK_URL."/stock_bl.php?cmdid=".$facturid, false,stream_context_create($mail->arrContextOptions));
            $mail->attachstring($data,"Bon $facturid.pdf","application/pdf");
            $pj[]="Bon $facturid.pdf";
            $attachements[]=BACK_URL."/stock_bl.php?cmdid=".$facturid;
            }
            
            if($adv->fields['glspj']==1){
            $bon=$DB_site2->query_first("SELECT * FROM user_files WHERE id=".intval($adv->fields['bongls']));
            $path=$_SERVER['DOCUMENT_ROOT']."/admin/userfiles/".$bon['id'].".".$bon['ext'];
            if(file_exists($path)){
                $data=file_get_contents($path);
                $mail->attachstring($data,"Bon_transport.pdf","application/pdf");
                $pj[]="Bon_transport.pdf";
                $attachements[]=$path;
            }
            }
            
            if($adv->fields['trsptpja4']==1){
                $data=file_get_contents("pdf/fragile.pdf");
                $mail->attachstring($data,"fragile.pdf","application/pdf");
                $data=file_get_contents("pdf/transportA4.pdf");
                $mail->attachstring($data,"note_importante.pdf","application/pdf");
                $pj[]="note_importante.pdf";
            }
            
            if($adv->fields['trsptpja5']==1){
                $data=file_get_contents("pdf/transportA5.pdf");
                $mail->attachstring($data,"note_importante.pdf","application/pdf");
                $pj[]="note_importante.pdf";
            }
        }

        if($adv->fields['fournisseur']!="0"){
            $result=$mail->sendmail();
            $DB_site2->query("INSERT INTO mail_outbox SET date=".time().", utilisateur='".$user_info[username]."', `to`='".$mail->destinataire."', subject='".$mail->subject."', body='".mysqli_real_escape_string($DB_site2->link_id,$mail->body)."', pj='".mysqli_real_escape_string($DB_site2->link_id,implode(',',$pj))."', stock_cmd_id=".$facturid);
            //$DB_site2->query("INSERT INTO suivi_commande SET facturid=$facturid, date=".time().", commercial='".$user_info[username]."', suivi='".addslashes("Passage de commande à ".$adv->destinataire."<br>".$mail->body)."'");

             /// enregistre les pièces jointes :
          $mailid = $DB_site2->insert_id();
          $i=0;
          foreach($attachements as $attachement){
               $data = file_get_contents($attachement,FALSE,stream_context_create($arrContextOptions));
               $ext = strtolower(pathinfo($pj[$i], PATHINFO_EXTENSION));
               $sql = "INSERT INTO mail_attachment SET moyen='mail_out', mailid='$mailid', name='".mysqli_real_escape_string($DB_site2->link_id,$pj[$i])."', ext='$ext', size='".strlen($data)."'";
               $DB_site2->query($sql);
               $pjid = $DB_site2->insert_id();
               file_put_contents($rootpath."configurations/".$host."/mails/attachments/2021/".$pjid.".".$ext,$data);
               $i++;
          }
        }
    }else{
      apercu($adv->fields['fournisseur'],$subject,$text);
    }
}

/************************************************************************** 
 *    function Mail transporteur
 *
 */

function mail_trspt($adv,$preview=false){
    global $DB_site2;
    global $user_info;
    global $adv_facture;
    global $js_popup_pj;
    global $host;
    global $rootpath;
  
    $facturid=$adv->facturid;
    $stock_cmd=new stock_cmd($DB_site2,$facturid);

    $subject="";
    $text="";


    switch($adv->fields['trspttype']){
        case "rdv":
            $advtrspt="avec rdv";
            $trspttypetxt=" AVEC prise de RDV";
            $rdvtxt="<br>Merci de prendre RDV au ".$rdv_tel;
            break;

        case "nordv":
            $advtrspt="sans rdv";
            $trspttypetxt=" directe SANS RDV";
            $rdvtxt="";
            break;

        case "gold":
            $advtrspt="express";
            $trspttypetxt=" *** EXPRESS GOLD ***";
            $rdvtxt="";
            break;
    }

    switch($adv->fields['trsptobj']){
        case "cmd":
            $subject="Commande$trspttypetxt n°".$facturid;
            $text.=" Bonjour,<br>Veuillez trouver ci-joint une nouvelle commande de transport$trspttypetxt n°$facturid";
            $fin="Nous vous remercions de votre envoi, par mail ou par fax, de votre confirmation de commande<br><br>Cordialement<br>$user_info[nom_contact] - Acilyon - www.ma-cave-a-vin.fr<br>Tél : + 33.4.26.03.05.44 - Fax : + 33.4.26.03.05.49";
            $newcmd=true;
            break;
        
        case "avarie":
            $subject="Commande en service$trspttypetxt n°".$facturid." - Suite avarie commande n°".$adv_facture['provenanceid'];
            $text.=" Bonjour,<br>Veuillez trouver ci-joint une nouvelle commande de transport$trspttypetxt n°$facturid, cette livraison ne doit pas nous être facturée suite avarie de transport commande ".$adv_facture['provenanceid'];
            $fin="Nous vous remercions de votre envoi, par mail ou par fax, de votre confirmation de commande<br><br>Cordialement<br>$user_info[nom_contact] - Acilyon - www.ma-cave-a-vin.fr<br>Tél : + 33.4.26.03.05.44 - Fax : + 33.4.26.03.05.49";
            $newcmd=true;
            break;
        
        case "annule":
            $subject="Annulation de la commande n°".$facturid;
            $text.="Bonjour,<br>Merci d'annuler le transport sur la commande n°$facturid et nous le confirmer par retour de ce mail";
            $fin="<br><br>Cordialement<br>$user_info[nom_contact] - Acilyon - www.ma-cave-a-vin.fr<br>Tél : + 33.4.26.03.05.44 - Fax : + 33.4.26.03.05.49";
            $newcmd=false;
            break;
        
        case "modif":
            $ef=new envoi_fournisseur($DB_site2,$adv->efid);
            $subject="Modification de la commande n°".$facturid;
            $text.="Bonjour,<br>Cette modification de commande annule et remplace la commande n°".$facturid." envoyée par mail le ".date("d/m/Y à H:i",$ef->date);
            $fin="Merci de nous confirmer cette modification par retour de ce mail.<br>Cordialement<br>$user_info[nom_contact] - ACILYON - www.ma-cave-a-vin.fr<br>Tél : + 33.4.26.03.05.44 -  Fax: +33.4.26.03.05.49";
            $newcmd=true;
            break;
        
        default:
            $newcmd=true;
    }

    if($adv->fields['infocomplement']!="")
    $text.="<p>".nl2br($adv->fields['infocomplement'])."</p>";
    $text.=$rdvtxt;

    $text.="<p><b>Produits :</b>";
    $paht=0;
    $ecopart=0;
    $poids=0;
    $tsindate=$tsoutdate=0;
    $annultxt="";
    $imperatif=0;
  
    if($adv->fields['trsptenlfourndate']!=""){
        $tsindate=$adv->date2ts($adv->fields['trsptenlfourndate']);
    }
  
    if($adv->fields['trsptotherdate']!=""){
        $tsoutdate=$adv->date2ts($adv->fields['trsptotherdate']);
    }
  
    if($adv->fields['trsptdatemax']=="trsptdateauto") $imperatif=1;

    $insertids=array();
    foreach($adv->lignes as $p=>$qte){
        $item=new stock_item($DB_site2);
        $item->loadartid($p);
        $text.="<br>qte : ".$qte." - ".$item->libelle." ".$item->poids_brut." kg";
        $poids+=$qte*$item->poids_brut;
    }
  
    $text.="</p>";
    $text.="<p><b>Poids total :</b> ".$poids." kg</p>";

    if($newcmd==true){
        if(isset($adv->fields['uncolis'])) $text.="<p>Attention : un seul colis pour tous les articles</p>";
        if(isset($adv->fields['hayon'])) $text.="<p>Camion avec hayon indispensable</p>";
        $text.="<p><b>Lieu d'enlèvement :</b><br>";

        $fourn=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id='".$stock_cmd->enlevement."'");//var_dump($stock_cmd->enlevement);
        $text.="$fourn[societe] $fourn[fonction]<br>$fourn[prenom] $fourn[nom]<br>$fourn[adresse1]".($fourn[adresse2]!=""?"<br>$fourn[adresse2]":"")."<br>$fourn[cp] $fourn[ville]<br>Tel $fourn[telephone] / fax $fourn[fax]<br>$fourn[horaires]";
        
        $text.="</p>";

        switch($adv->fields['trsptenl']){
        case "0":
            break;
        case "fournisseur";
            $text.="<p><b>Date d'enlèvement / mise à disposition : </b> ".date("d/m/Y",$tsindate)."</p>";
            break;
        case "eberhardt":
            $text.="<p>Date livraison en vos locaux par le transporteur du fournisseur : ".date("d/m/Y",$tsindate);
            break;
        case "other":
            $text.=$adv->fields['trsptenlother'];
            break;
        }

        $text.="<p><b>Identification à mettre sur le colis</b> :";
        
        if($adv->fields['trsptbl']==1) $text.="<br>BL fournit ci-joint";
        if($adv->fields['trsptfacture']==1) $text.="<br>Facture ci-jointe";
        if($adv->fields['trsptfragile']==1) $text.="<br>Etiquette Fragile ci-jointe";
        if($adv->fields['trspttext']==1) $text.="<br>Texte sur le colis : commande n°$facturid de Ma Cave à Vin - Acilyon pour $adv_facture[nom] $adv_facture[prenom] $adv_facture[codepostal] $adv_facture[ville] $adv_facture[libelle]";
        if($adv->fields['trsptimportant']==1) $text.="<br>Important : enlever tous les documents de livraison du fournisseur";
        if($adv->fields['trspttextother']==1) $text.="<br>".$adv->fields['trsptothertext'];
        
        $text.="</p>";

        
        switch($adv->fields['trsptdatemax']){
        case "0":
            break;
        case "trsptdateauto":
            $text.="<p><b>à livrer <u>précisément et exclusivement</u> le</b> : ".date("d/m/Y",$tsoutdate)."</p>";
            break;
        case "trsptdateother":
            $text.="<p><b>Date livraison maximum</b> : ".date("d/m/Y",$tsoutdate)."</p>";
            break;
        }
        
        if($adv->fields['livraisonplustot']==1) $text.="Voir la possibilité de livrer plus tôt";

        $text.="<p><b>Type de livraison : ".$trspttypetxt."</b></p>";

        $text.="<p><b>Adresse livraison : </b>";
        $livrid=-1;
        $blsls=false;

        $livr=$DB_site2->query_first("SELECT * FROM fournisseur INNER JOIN pays USING(paysid) WHERE id='".$stock_cmd->livraison."'");
        $text.="<br>$livr[raisonsociale]<br>$livr[nom] $livr[prenom]<br>$livr[adresse1]".($livr[adresse2]!=""?"<br>$livr[adresse2]":"")."<br>$livr[cp] $livr[ville]<br>$livr[libelle]<br>$livr[telephone]";

        if($adv->fields['infotrspt']=="other") $text.="<p><b>Informations pour transporteur</b> : ".stripslashes($adv->fields['othertrsptinfo'])."</p>";

        $text.="<p><b>Tarif :</b>";

        $tvafrance=$DB_site2->query_first("SELECT TVAtauxnormal FROM pays WHERE paysid=57");
        $tarif=0;
        $assur_comp=0;
        
        if($adv->fields['trsptcout']==1){ $tarif+=floatval($adv->fields['trsptcoutval']); $text.=$tarif." €"; }
        
        if(isset($adv->fields['chkopt'])){
        foreach($adv->fields['chkopt'] as $key=>$value){
            $text.="<br>+ ".$adv->fields['priceopt'][$key]." &euro; (".$adv->fields['optname'][$key].")";
            $tarif+=floatval($adv->fields['priceopt'][$key]);
        }
        }

        $text.="<br><b>= $tarif &euro; HT (hors taxe gas oil)</b>
        <br>valeur déclarée : ".$adv->fields['valeurdeclaree']." &euro; HT, ".number_format($adv->fields['valeurdeclaree']*(1+$tvafrance[0]/100),2,',','')." &euro; TTC</p>";
    }

    $text.=$fin;

    if(!$preview){
        if($adv->fields['trsptobj']=="annule" || $adv->fields['trsptobj']=="modif"){
        
        }

        if($newcmd==true){
            $stock_cmd->items(0);
            $pahtu=round($tarif/count($adv->lignes),2);
            
            foreach($adv->lignes as $artid=>$qte){
                foreach($stock_cmd->items as $item){
                    if($artid==$item->artid && $item->date_livraison==0){
                        $item->trspt=$adv->destinataire;
                        if($item->date_enlevement==0){
                          $item->date_enlevement_origin=$tsindate;
                        }
                        $item->date_enlevement=$tsindate;
                        $item->date_livraison=$tsoutdate;
                        $item->date_livraison_origin=$tsoutdate;
                        $item->prix_trspt=($pahtu/$qte);
                        $item->update();
                    }
                }
            }
        
            if($adv->fields["envoiedi"]=="1"){
                //Génération de l'étiquette par webservice GLS
                if($adv->destinataire == "GLS"){
                    include_once($rootpath."configurations/".$host."/transporteurs/gls/gls.php");
                    $gls = new gls($DB_site2,$adv,$clientid);
                    $gls->send(false,$parent);
                    
                    if($gls->error() !== false){
                        $suivi=new stock_suivi($DB_site2);
                        $suivi->stock_cmd_id=$facturid;
                        $suivi->commercial=$adv->utilisateur;
                        $suivi->suivi="Erreur EDI GLS\n".$gls->error();
                        $suivi->create();
                    }else{
                        $js_popup_pj="window.open('fournfiles/".$gls->pjid().".pdf')";
                    }
                }
                
                                //Génération de l'étiquette par webservice fedex
                if($adv->destinataire == "FEDEX Express"){
                    include_once($rootpath."configurations/".$host."/transporteurs/tnt/tnt.php");
                    $tnt = new tnt($DB_site2, $adv,$username,$pass);

                    if(ESPACE != 'prod') $tnt->test_mode = true; //Permet d'éviter de faire la demande de ramassage à TNT
                    $tnt->expedition();
                    
                    if($tnt->getError() !== false){
                        
                        $suivi=new stock_suivi($DB_site2);
                        $suivi->stock_cmd_id=$facturid;
                        $suivi->commercial=$adv->utilisateur;
                        $suivi->suivi="Erreur EDI FEDEX Express\n".$tnt->getError();
                        $suivi->create();

                    } else {

                            $tnt->etiquette(true);

                            $js_popup_pj = "window.open('fournfiles/".$tnt->pjid().".pdf')";

                    }
                }
                
                //Génération de l'étiquette par WEBTEL dans le cas de Schenker
                if($adv->destinataire == "DB Schenker France"){
                    $webtel = new webtel($DB_site2,$adv,'schenkernat');
                    
                    $webtel->filename = "expeditions_schenker";
                    $webtel->ext      = "txt";
                    $webtel->dossier  = '/configurations/www.ma-cave-a-vin.fr/exports/';

                    $webtel->lignes = array(
                        "S", //Séparateur de champs
                        $webtel->date_exp, //Date expédition
                        "01", //Code transporteur/prestation
                        $webtel->export?"60":$webtel->trspttype, //Code produit transport/facturation
                        "01", //Code expéditeur
                        "", //Libellé expéditeur
                        "", //Code destinataire
                        $webtel->raison_sociale, //Nom 1
                        $webtel->compl_nom,	//Nom 2
                        "",	//Nom 3
                        $webtel->adresse, //Adresse 1
                        $webtel->telephone != '' ? 'Tél : '.$webtel->telephone : '', //Adresse 2
                        "",	//Adresse 3
                        $webtel->pays, //Pays
                        $webtel->codepostal, //Code postal
                        $webtel->ville, //Localité
                        $webtel->interlocuteur,	//Contact
                        $webtel->interlocuteur != '' ? $webtel->telephone : '',	//Téléphone
                        "",	//Récépissé
                        $webtel->facturid, //Référence d'expédition
                        "P",	//Port ou Incoterm
                        $webtel->nb_manu, //Nombre d'UM
                        $webtel->nb_colis, //Nombre de colis
                        $webtel->nb_manu_consigne, //Nombre de supports consignés
                        $webtel->manu_consigne_type,	//Type de supports consignés
                        $webtel->poids,	//Poids réel
                        "", //Unités taxables
                        "",	//Type d'unités taxables
                        $webtel->volume,	//Volume
                        $webtel->ctr, //Contre-remboursement
                        $webtel->ctr != '' ? 'EUR' : '',	//Devise C-R
                        "", //$webtel->valeur_declaree, //Valeur déclarée
                        $webtel->valeur_declaree != '' ? 'EUR' : '',	//Devise V D
                        "",	//Non utilisé actuellement
                        "",	//Non utilisé actuellement
                        $webtel->nature,	//Nature de marchandises
                        $webtel->info_trspt,	//Instructions de livraison
                        $webtel->info_comp,	//Remarques de livraison
                        $webtel->date_imperative,	//Date impérative de livraison
                        "",	//Code imprimante
                        $webtel->particulier, //Livraison particulier
                        $webtel->annonce, //Annonce au destinataire
                        $webtel->interlocuteur != '' ? $webtel->portable : '',	//Téléphone n°2 (portable)
                        $webtel->interlocuteur != '' ? $webtel->email : '',	//Adresse mail
                        "",	//Mètre plancher
                        "",	//Date de début livraison souhaitée
                        "",	//Heure de début livraison souhaitée
                        "",	//Date de fin livraison souhaitée
                        ""	//Heure de fin livraison souhaitée
                    );

                    $webtel->makeCSV();
                }
                
                //Génération de l'étiquette par WEBTEL dans le cas d'Heppner
                if($adv->destinataire == "HEPPNER Le Mans"){
                  include_once($rootpath."configurations/".$host."/transporteurs/heppner/heppner.php");
                    $heppner=new heppner($DB_site2,$adv,$ftp,$host);
                    $heppner->etiquette(true);
                    $heppner->send();
                }
            
            }
            $attachements=array();
            $pj=array();
            $mail=new email();
            if($adv->fields["envoiedi"]!="1")
                $mail->destinataire=$adv->fields['typetrspt'];
            //$mail->destinataire="mr.cc@free.fr";
            $mail->copymode="bcc";
            $mail->to=array("pr@mon-espace-cuisson.com","adv@mon-espace-cuisson.com");
            $mail->expediteur="adv@mon-espace-cuisson.com";
            $mail->expname="Ma Cave à Vin";
            $mail->subject=$subject;
            $mail->body=$text;
            
            $arrContextOptions=array(
                "ssl"=>array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ),
            );

            /*if($adv->fields['facturetrsptpj']==1){
                //$_GET['join']=true;
                //$mail->attachstring(include("../facture2.php"),"Facture $facturid.pdf","application/pdf");
                $data=file_get_contents("http://www.ma-cave-a-vin.fr/facture2.php?facturid=$facturid&nopub=1");
                $mail->attachstring($data,"Facture $facturid.pdf","application/pdf");
                $pj[]="Facture $facturid.pdf";
            }*/
            
            if($adv->fields['trsptbl']==1){
                $data=file_get_contents(SITE_URL."/admin/stock_bl.php?cmdid=".$facturid,FALSE,stream_context_create($arrContextOptions));
                $mail->attachstring($data,"Bon $facturid.pdf","application/pdf");
                $pj[]="Bon $facturid.pdf";
                $attachements[]=SITE_URL."/admin/stock_bl.php?cmdid=".$facturid;
            }
        
            if($adv->fields['othertrsptpj']==1 && !empty($_FILES['othertrsptpjfile'])){
                $data=file_get_contents($_FILES['othertrsptpjfile']['tmp_name']);
                $mime=$DB_site2->fetch_array($DB_site2->query("SELECT * FROM MimeType WHERE g_extension='".pathinfo($_FILES['othertrsptpjfile']['tmp_name'], PATHINFO_EXTENSION)."'"));
                $mail->attachstring($data,$_FILES['othertrsptpjfile']['name'],$mime['g_mimeType']);
                $pj[]=$_FILES['othertrsptpjfile']['name'];
                $attachements[]=$_FILES['othertrsptpjfile']['tmp_name'];
            }
            
        }
        
        $action="editer";
        
        if($adv->fields['typetrspt']!="0"){ //desactivé
            $mail->sendmail();  // on envoie pas de mail s'il n'y a pas de transport
            $DB_site2->query("INSERT INTO mail_outbox SET date=".time().", utilisateur='".$user_info[username]."', `to`='".$mail->destinataire."', subject='".$mail->subject."', body='".mysqli_real_escape_string($DB_site2->link_id,$mail->body)."', pj='".mysqli_real_escape_string($DB_site2->link_id,implode(',',$pj))."', stock_cmd_id=".$facturid);
            //$DB_site2->query("INSERT INTO suivi_commande SET facturid=$facturid, date=".time().", commercial='".$user_info[username]."', suivi='".addslashes("Passage de commande à ".$_POST['trsptname']."<br>".$mail->body)."'");

            /// enregistre les pièces jointes :
          $mailid = $DB_site2->insert_id();
          $i=0;
          foreach($attachements as $attachement){
               $data = file_get_contents($attachement,FALSE,stream_context_create($arrContextOptions));
               $ext = strtolower(pathinfo($pj[$i], PATHINFO_EXTENSION));
               $sql = "INSERT INTO mail_attachment SET moyen='mail_out', mailid='$mailid', name='".mysqli_real_escape_string($DB_site2->link_id,$pj[$i])."', ext='$ext', size='".strlen($data)."'";
               $DB_site2->query($sql);
               $pjid = $DB_site2->insert_id();
               file_put_contents($rootpath."configurations/".$host."/mails/attachments/2021/".$pjid.".".$ext,$data);
               $i++;
          }
        }
        
    }else{
        apercu($adv->fields['typetrspt'],$subject,$text);
    }
}

/************************************************************************** 
 *    function apercu email
 *
 */


function apercu($dest,$sujet,$text){
  global $output;

  if($output=="html"){
    $body="Destinataire:".$dest."<br>Sujet:".$sujet."<br>".$text;
    if($dest=="0") $body="Pas d'envoi de mail";
    
    print <<<HTML
    <html>
    <head>
      <title>Aperçu</title>
      <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
    </head>
    <body>
      $body
    </body>
    </html>
HTML;
  }elseif($output=="xml"){
    header("Cache-Control: no-cache");
    header("Content-type:text/xml");
      
    print "<?xml version=\"1.0\" encoding=\"iso-8859-15\"?>
    <xml>
      <to>$dest</to>
      <subject>$sujet</subject>
      <body><![CDATA[$text]]></body>
    </xml>";
  }
}
?>
