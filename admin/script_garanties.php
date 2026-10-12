<?php

ini_set("display_errors","on");

include_once("host_config_admin.php");

$nostat=1;
include_once($rootpath."class/facture.class.php");
include_once($rootpath."class/garantie.class.php");
include_once($rootpath."class/devises.class.php");
include_once($rootpath."class/template.class.php");
include_once($rootpath."class/email.php");
include_once($rootpath."admin/ftp.php");
include_once($rootpath."admin/adv/adv.class.php");
include_once($rootpath."admin/adv/envoi_fournisseur.php");

$DB_site = clone $DB_site2;

$devises=new devises($DB_site);
$template=new template("FR",$DB_site);

$dtexp=new datetime("-1 day");
if(isset($_GET['test'])) $dtexp=new datetime($_GET['test']);

$msg="Contrats commandes expédiées le ".$dtexp->format("d/m/Y");

$sql="SELECT f.*,UNIX_TIMESTAMP(datefacture) AS tsdate, UNIX_TIMESTAMP(dateexpedition) AS tsenvoi,adv.id AS advid
 FROM facture f INNER JOIN adv USING(facturid)
 WHERE adv.type='garantie' AND adv.destinataire!='SFG' AND adv.destinataire != 'Enigma Kitchen Solutions Ltd' AND efid=0 AND dateexpedition='".$dtexp->format("Y-m-d")."' AND valide IN (5,8)";

$reqfacture=$DB_site->query($sql);
if($DB_site->num_rows($reqfacture)==0) $msg.="\nAucune commande par mail";

while($f=$DB_site->fetch_array($reqfacture)){
    $facture=new facture($DB_site,$f['facturid']);
    $adv=new adv($DB_site,$f['advid']);
    $conditions=$DB_site->query_first("SELECT langue,devise,compte_client FROM fournisseur_condition WHERE societe='".mysqli_real_escape_string($DB_site->link_id,$adv->destinataire)."'");
    $template->newlangue($conditions['langue']);
    $symboledevise=$devises->symbole($conditions['devise']);
    $total=0;
    $totaltxt="";
    
    $body=sprintf($template->translate("/admin/adv_garanties/intro_mail"),$facture->facturid);
    
    $body.="<p>".$template->translate("/admin/adv_garanties/compte_client")." : <b>".$conditions['compte_client']."</b></p>";
    
    $body.="<p><b>".$template->translate("/admin/adv_garanties/type_garantie")." :</b></p>
    <table border=\"1\" cellspacing=\"0\">
    <tr style=\"font-weight:bold;\">
      <td>".$template->translate("/admin/adv_garanties/garantie","F")."</td>
      <td>".$template->translate("/admin/adv_garanties/qte","F")."</td>
      <td>".$template->translate("/admin/adv_garanties/libelle","F")."</td>
      <td>".$template->translate("/admin/adv_garanties/marque","F")."</td>
      <td>".$template->translate("/admin/adv_garanties/ref_acilyon","F")."</td>
      <td>".$template->translate("/admin/adv_garanties/ref_fab","F")."</td>
      <td>".$template->translate("/admin/adv_garanties/date_depart","F")."</td>
      <td>".$template->translate("/admin/adv_garanties/date_fin","F")."</td>
     </tr>";
     
     $listefl=array();
     foreach($adv->lignes as $lignefactureid=>$qte){
	$garantie=new garantie($DB_site,null,$facture->langue,$adv->fields['garantieid_'.$lignefactureid]);
	$article=$DB_site->query_first("SELECT artcode,artcode_eurocave,libelle".$conditions['langue']." AS libelle, m.libelle AS marque FROM article INNER JOIN article_marque USING(artid) INNER JOIN marque m USING(marqueid) WHERE artid=".$facture->lignes[$lignefactureid]->artid);
	
	$dtdebut=clone $dtexp;
	if($garantie->cumul==1) $dtdebut->modify("+2 years");
	$datefin=clone $dtdebut;
	$datefin->modify("+".$garantie->duree." years");
	$body.="<tr>
	  <td>".$garantie->duree." ".$template->translate("/article/garantie_type/an".($garantie->duree>1?"s":""))." ".$template->translate("/article/garantie_type/".$garantie->type_garantie)."</td>
	  <td>".$qte."</td>
	  <td>".$article['libelle']."</td>
	  <td>".$article['marque']."</td>
	  <td>".$article['artcode']."</td>
	  <td>".$article['artcode_eurocave']."</td>
	  <td>".$dtdebut->format("d/m/Y")."</td>
	  <td>".$datefin->format("d/m/Y")."</td>
	</tr>";
	
	$total+=$qte*$adv->fields["paht_".$lignefactureid];
	$totaltxt.=$qte." x ".$adv->fields["paht_".$lignefactureid]." ".$symboledevise;
	
	$efl = new envoi_fournisseur_ligne($DB_site);
	//$efl->expediteur=$user_info['username'];
	$efl->type = "garantie";
	$efl->qte = $qte;
	$efl->paht = $adv->fields["paht_".$lignefactureid];
	$efl->lignefactureid = $lignefactureid;
	$listefl[] = $efl;
    }
    $body.="</table>";
    
    $body.="<p><b>".$template->translate("/admin/adv_garanties/adresse_client","F")." :</b></p>"
    .($facture->utilisateur->raisonsociale!=""?$facture->utilisateur->raisonsociale."<br>":"")
    .$facture->utilisateur->nom." ".$facture->utilisateur->prenom."<br>"
    .$facture->utilisateur->adresse."<br>"
    .$facture->utilisateur->codepostal." ".$facture->utilisateur->ville."<br>"
    .$facture->utilisateur->pays."<br>" 
    .$facture->utilisateur->telephone."<br>"
    .$facture->utilisateur->mail;
    
    $body.="<p><b>".$template->translate("/admin/adv_garanties/prix_total","F")." :</b></p>"
    .$totaltxt
    ."<br>Total : <b>".number_format($total,2)." ".$symboledevise."</b>";
    $body .= "<div style=\"display:none;font-size:1px;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;\">#CDE".$facture->facturid."#</div>";
    
    $body.=$template->translate("/admin/adv_garanties/fin_mail");
    
    $destinataires=array();
    $fournlist=$DB_site->query("SELECT mail FROM fournisseur WHERE societe='".mysqli_real_escape_string($DB_site->link_id,$adv->destinataire)."' AND type='garadv'");
    while($fourn=$DB_site->fetch_array($fournlist)){
	$destinataires[]=$fourn['mail'];
    }
    
    $msg.="\n\nContrat ".$facture->facturid." ".$adv->destinataire;
    
    if(count($destinataires)>0){
	$mail=new email();
	$mail->destinataire=array_pop($destinataires);
	$destinataires[]="pr@mon-espace-cuisson.com";
	$destinataires[]="adv@mon-espace-cuisson.com";
	$mail->expediteur="adv@mon-espace-cuisson.com";
	$mail->expname="Ma Cave à Vin";
	$mail->copymode="bcc";
	$mail->to=$destinataires;
	$mail->subject=$template->translate("/admin/adv_garanties/contrat_n").$facture->facturid;
	$mail->body=$body;
	if(stripos($adv->destinataire,"enigma")!==false){
	    $data=file_get_contents("https://www.ma-cave-a-vin.fr/admin/enigma_pdf.php?advid=".$adv->id);
	    $mail->attachstring($data,$facture->facturid.".pdf","application/pdf");
	}
	
	$ef = new envoi_fournisseur($DB_site);
	//$ef->facturid=$facture['facturid'];
	$ef->facturid = $facture->facturid;
	$ef->destinataire = $adv->destinataire;
	$ef->tarif = $total;
	$ef->type = 'garantie';
	
	if(!isset($_GET['test'])){
	  $ef->create();
	  $ef->create_lines($listefl);
	  $adv->efid=$ef->id;
	  $adv->update();
	  
	  $result=$mail->sendmail();
	  $msg.=($result?" OK":" Erreur d'envoi");
	  $DB_site->query("INSERT INTO mail_outbox SET date=".time().", utilisateur='', `to`='".$mail->destinataire."', subject='".addslashes($mail->subject)."', body='".addslashes($mail->body)."', facturid='".$facture->facturid."',userid='".$facture->userid."', uniqueid='".$mail->uniqueid."', compta = 'non'");
	}else
	  print $body;
    }else
	$msg.="\nERREUR : Aucun destinataire pour ".$adv->destinataire;
    
}

print $msg;

include_once($rootpath."admin/sfg.php");

?>