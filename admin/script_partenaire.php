<?php

ini_set("display_errors","on");

include_once("host_config_admin.php");

chdir($rootpath);

include($rootpath."class/template.class.php");
include($rootpath."class/devises.class.php");
include($rootpath."class/email_client.php");
include($rootpath."class/email.php");

$DB_site= clone $DB_site2;

$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);

$options=getopt("pd:");
if(isset($options['d'])) $_GET['verifdate']=$options['d'];
$preview=isset($_GET['preview']) || isset($options['p']);

$flog=fopen($rootpath."admin/partenaires.log","a");

// vérification pour envoi partenaires

$delai=20;
$verifdate=mktime(0,0,0,date("n"),date("j"),date("Y"))-$delai*86400;
if(isset($_GET['verifdate'])){
  $d=explode("/",$_GET['verifdate']);
  $verifdate=mktime(0,0,0,$d[1],$d[0],$d[2]);
}

$txt="\nEnvois partenaire caviste commandes expédiées le ".date("d/m/Y",$verifdate);
$requete=$DB_site->query("SELECT f.facturid AS facturid, f.montantcadeau AS montantcadeau, cv.type AS type, cv.mini, c.email AS cav_email, c.raisonsociale AS cav_raison, u.nom AS nom, u.prenom AS prenom, u.userid FROM facture f, code_caviste cv, caviste c, utilisateur u WHERE f.valide IN (5,8) AND f.cadeauid = cv.code AND c.cavisteid=cv.caviste AND f.userid=u.userid AND f.dateexpedition='".date("Y-m-d",$verifdate)."'");
while($facture=$DB_site->fetch_array($requete)){
      switch($facture['type']){
           case 1:
     if($facture['montantcadeau']>$facture['mini']){
        $mail=new email();
        $mail->destinataire=$facture['cav_email'];
        $mail->destname=$facture['cav_raison'];
        $mail->expediteur="contact@ma-cave-a-vin.fr";
        $mail->expname="Ma Cave à Vin";
        $mail->to=array("cecile@ma-cave-a-vin.fr","contact@ma-cave-a-vin.fr","webmaster4@ma-cave-a-vin.fr");
        $mail->copymode="bcc";
        $mail->subject="Partenariat Ma Cave à Vin #CDE".$facture['facturid']."#";
        $mail->body="Bonjour, 
        <p>Nous vous remercions de votre confiance et de nous avoir envoyé votre client $facture[nom] $facture[prenom]. En effet celui-ci nous a passé commande dernièrement.</p>
        <p>Suite à nos accords, vous trouverez en pièce jointe, une copie du bon d'achat d'une valeur de ".number_format($facture['montantcadeau'],2,',','')." euros, que nous lui transmettrons, par mail, sous 10 jours.</p>
        <p>Nous vous remercions de nous transmettre, dès réception de ce mail, une facture avec TVA, correspondant à la valeur de ce bon d'achat (".number_format($facture['montantcadeau'],2,',','')." euros TTC), (envoie possible par mail ou courrier) afin que notre service comptabilité puisse effectuer le virement sur votre compte bancaire, sous 24h. </p>
        <p>Nous vous remercions donc, de prendre en compte et d'accepter ce bon d'achat, qui permettra au client d'acheter, chez vous, les produits de son choix et pour le montant minimum indiqué.</p>
        <p>Nous vous rappelons certains points importants :
        <li>Cette somme vous est acquise, que le client utilise ou pas ce bon d'achat,
        <li>Ce bon n'est pas échangeable contre de l'argent,
        <li>Contrôlez la date de validité du bon d achat (valable 6 mois),
        <li>Vérifiez attentivement a ce que celui-ci ne soit utilisé qu'une seule fois, et par la bonne personne,
        <li>Le client ne pourra pas recevoir de facture de votre part pour cet achat réalisé pour le montant du Bon d'achat. Toutefois si le client le désire ou si son achat est d'un montant supérieur, vous devrez déduire le montant total du bon de sa facture.</p>
        <p>A très bientôt.</p>
        <p>L'Equipe de Ma Cave à Vin </p>
        <div style=\"display:none;font-size:1px;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;\">#CDE".$facture[facturid]."#</div>";
        $facturid=$facture['facturid'];
        $data="";
        //$f=fopen("https://www.ma-cave-a-vin.fr/admin/bondachat.php?facturid=".$facturid,"r");
        $f=fopen("https://www.ma-cave-a-vin.fr/admin/bondachat.php?facturid=".$facturid,"r",false,stream_context_create($arrContextOptions));
        while(!feof($f)) $data.=fread($f,1024);
        fclose($f);
        $mail->attachstring($data,"Bon d'achat.pdf","application/pdf");
        if(!$preview){
                    $mail->sendmail();
                    $DB_site->query("INSERT INTO mail_outbox SET date=".time().", utilisateur='', `to`='".$mail->destinataire."', subject='".addslashes($mail->subject)."', body='".addslashes($mail->body)."', pj='Bon d\'achat.pdf', facturid='".$facture['facturid']."',userid='".$facture['userid']."', uniqueid='".$mail->uniqueid."', compta ='non'");
                    
                    $result=$DB_site->query("INSERT INTO user_files SET userid=".intval($facture['userid']).", facturid=".intval($facture['facturid']).", date=".time().", name='Bon d\'achat.pdf', size=".strlen($data).", ext='pdf', state=1");
                    if($result){
                        $fid=$DB_site->insert_id();
                        file_put_contents($rootpath."admin/userfiles/".$fid.".pdf",$data);
                    }
        }
        $txt.="\nEmail bon d'achat envoyé à ".$mail->destname." (".$mail->destinataire.") pour la commande ".$facture['facturid'];
     }
           break;

           case 2:
           $mail=new email();
           $mail->destinataire=$facture['cav_email'];
           $mail->destname=$facture['cav_raison'];
           $mail->expediteur="contact@ma-cave-a-vin.fr";
           $mail->expname="Ma Cave à Vin";
           $mail->to=array("pr@ma-cave-a-vin.fr","commercial@ma-cave-a-vin.fr","webmaster3@ma-cave-a-vin.fr","cecile@ma-cave-a-vin.fr","adv@ma-cave-a-vin.fr","webmaster4@ma-cave-a-vin.fr");
           $mail->copymode="bcc";
           $mail->subject="Partenariat Ma Cave à Vin #CDE".$facture['facturid']."#";
           $mail->body="Bonjour, 
           <p>Nous vous remercions de votre confiance et de nous avoir envoyé votre client $facture[nom] $facture[prenom]. En effet celui-ci nous a passé commande dernièrement.</p>
           <p>Suite à nos accords, vous trouverez en pièce jointe, le montant de votre commission, d'une valeur de ".number_format($facture['montantcadeau'],2,',','')." euros, que notre service comptabilité, vous réglera dès réception de votre facture avec TVA, correspondant à la valeur de la commission (envoie possible par mail ou courrier). Le règlement sera effectué sous 24H par virement sur votre compte bancaire.</p>
           <p>A très bientôt.</p>
           <p>L'Equipe de Ma Cave à Vin</p><div style=\"display:none;font-size:1px;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;\">#CDE".$facture[facturid]."#</div>";
           $facturid=$facture['facturid'];
     $data="";
     $f=fopen("https://www.ma-cave-a-vin.fr/admin/commission.php?facturid=".$facturid,"r",false,stream_context_create($arrContextOptions));
           while(!feof($f)) $data.=fread($f,1024);
           fclose($f);
           $mail->attachstring($data,"Bon.pdf","application/pdf");
     if(!$preview){
                $mail->sendmail();
                $DB_site->query("INSERT INTO mail_outbox SET date=".time().", utilisateur='', `to`='".$mail->destinataire."', subject='".addslashes($mail->subject)."', body='".addslashes($mail->body)."', pj='Bon.pdf', facturid='".$facture['facturid']."',userid='".$facture['userid']."', uniqueid='".$mail->uniqueid."', compta ='non'");

                $result=$DB_site->query("INSERT INTO user_files SET userid=".intval($facture['userid']).", facturid=".intval($facture['facturid']).", date=".time().", name='Commission.pdf', size=".strlen($data).", ext='pdf', state=1");
                if($result){
                    $fid=$DB_site->insert_id();
                    file_put_contents($rootpath."admin/userfiles/".$fid.".pdf",$data);
                }
     }
           $txt.="\nEmail commission envoyé à ".$mail->destname." (".$mail->destinataire.") pour la commande ".$facture['facturid'];
           break;
      }
    
}

if($DB_site->num_rows($requete)==0) $txt.="\nAucun email partenaire caviste envoyé\n";
if(!$preview) fwrite($flog,$txt);


// vérification pour les bons d'achat client
$delai=30;    //jours
$verifdate=mktime(0,0,0,date("n"),date("j"),date("Y"))-$delai*86400;
if(isset($_GET['verifdate'])){
  $d=explode("/",$_GET['verifdate']);
  $verifdate=mktime(0,0,0,$d[1],$d[0],$d[2]);
}
$txt2="\nEnvois bons d'achat commandes expédiées le ".date("d/m/Y",$verifdate);


$sql="SELECT f.facturid AS facturid, f.montantcadeau AS montantcadeau, cv.type AS type, cv.mini, c.email AS cav_email, c.raisonsociale AS cav_raison, u.nom AS nom, u.prenom AS prenom, u.mail AS user_mail,u.langue,f.site_code FROM facture f, code_caviste cv, caviste c, utilisateur u WHERE f.valide IN (5,8) AND f.cadeauid = cv.code AND c.cavisteid=cv.caviste AND f.userid=u.userid AND cv.type=1 AND f.dateexpedition='".date("Y-m-d",$verifdate)."'";
$requete=$DB_site->query($sql);
while($facture=$DB_site->fetch_array($requete)){
    if($facture['montantcadeau']>$facture['mini']){
      $template=new template($facture['langue'],$DB_site);
      $template->changesite($facture['site_code'],$facture['langue']);
      //  $template->path=$rootpath.$template->path;

      $facturid=$facture['facturid'];
  
      $mail=new email();
      $mail->destinataire=$facture['user_mail'];
      $mail->destname=ucfirst($facture['nom'])." ".ucfirst($facture['prenom']);
      $mail->expediteur=$template->translate("/site/mail_expediteur");
      $mail->expname=$template->translate("/site/nom");
      $mail->to=array("pr@ma-cave-a-vin.fr","commercial@ma-cave-a-vin.fr","webmaster3@ma-cave-a-vin.fr","cecile@ma-cave-a-vin.fr","adv@ma-cave-a-vin.fr","webmaster4@ma-cave-a-vin.fr");
      $mail->copymode="bcc";
      
      $tracking ="<div style=\"display:none;font-size:1px;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;\">#CDE$facturid#</div>";

      $vars=array("nom"=>$facture['nom'],"prenom"=>$facture['prenom'],"montantcadeau"=>number_format($facture['montantcadeau'],2,',',''),"facturid"=>$facturid,"tracking"=>$tracking);
      $emc=new email_client($DB_site,$template,77,null,$vars,true);
      $mail->subject=$emc->subject." #CDE".$facture['facturid']."#";
      $mail->body=$emc->body;

      $data="";
      $f=fopen("https://www.ma-cave-a-vin.fr/admin/bondachat.php?facturid=".$facturid,"r");
      while(!feof($f)) $data.=fread($f,1024);
      fclose($f);
      $mail->attachstring($data,$template->translate("/client/bon_achat","F").".pdf","application/pdf");
      if(!$preview){
            $mail->sendmail();
            $DB_site->query("INSERT INTO mail_outbox SET date=".time().", utilisateur='', `to`='".$mail->destinataire."', subject='".addslashes($mail->subject)."', body='".addslashes($mail->body)."', pj='Bon d\'achat.pdf', facturid='".$facture['facturid']."',userid='".$facture['userid']."', uniqueid='".$mail->uniqueid."', compta='non'");
      }else
          print $mail->subject."<br>".$mail->body."<hr>";
      $txt2.="\nEmail bon d'achat client envoyé à ".$mail->destname." (".$mail->destinataire.") pour la commande ".$facture['facturid'];
    }
}

if($DB_site->num_rows($requete)==0) $txt2.="\nAucun email bon d'achat partenaire envoyé";
if(!$preview) fwrite($flog,$txt2);

print "$txt<br>$txt2";

?>