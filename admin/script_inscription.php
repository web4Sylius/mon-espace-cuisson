<?php
include_once("host_config_admin.php");
chdir($rootpath);

include($rootpath."class/fonctions.php");
include($rootpath."class/email_client.php");
include_once($rootpath."class/devises.class.php");
include_once($rootpath."class/template.class.php");
include_once($rootpath."class/pack.class.php");
include_once($rootpath."class/email.php");

$DB_site=clone $DB_site2;

$delais=3600;
$subdate2=time()-$delais;
$subdate1=$subdate2-$delais;


echo "envoi de mail aux nouveaux inscrits :<br>";

$sql="SELECT * FROM utilisateur WHERE
  UNIX_TIMESTAMP(dateinscription) BETWEEN ".$subdate1." AND ".$subdate2." AND provenance IN ('site','configurateur')
  AND (SELECT COUNT(facturid) FROM facture WHERE userid=utilisateur.userid AND moyenid>0)=0
  AND (SELECT COUNT(facturid) FROM envoi_devis INNER JOIN facture USING(facturid) WHERE userid=utilisateur.userid)=0";


$req=$DB_site->query($sql);
while($user=$DB_site->fetch_array($req)){
    // print "<p>".$user['nom']." ".$user['prenom']." ".$user['mail']."</p>";
    if($user['site_code']=="") $user['site_code']="base";
    if($user['langue']=="") $user['langue']="FR";
    $template=new template($user['langue'],$DB_site);
    $template->changesite($user['site_code'],$user['langue']);
    $template->pro=$pro=($user['raisonsociale']!="" && $user['site_code']!="uk");
    $template->parts["pro"]=($pro?"_pro":"");
    $template->parts["autocolor"]=($pro?"#0393c2":"#a88d5c");
    
    $c=0;
    $sql = "SELECT av.artid";
    $sql .= " FROM article_vu av";
    $sql .= " INNER JOIN article_site asite ON (asite.artid = av.artid)";
    $sql .= " AND asite.site_code = '".$user['site_code']."'";
    $sql .= " AND asite.active = 1";
    $sql .= " WHERE av.userid = ".$user['userid'];
    $sql .= " GROUP BY av.artid";
    $sql .= " ORDER BY av.nb DESC, av.date DESC";
    $sql .= " LIMIT 6";
    $articles_vus=$DB_site->query($sql);
    while($article_vu=$DB_site->fetch_array($articles_vus)){
		if($c==2){
			$template->parts['vignettes_articles'].="</tr>
			<tr><td colspan=\"2\" style=\"height:1px;border-top:solid 1px #808080;\"></td></tr>
			<tr>";
			$c=0;
		}
		$template->parts['vignettes_articles'].="<td>".vignette_mail($article_vu['artid'])."</td>";
		$c++;
    }
    
    $template_articles="";
    if($DB_site->num_rows($articles_vus)>0){
		$template->evaltemplate("ArticlesRelance","articles");
		$template_articles="_articles";
    }
  
    $template->evaltemplate("PourquoiRelance","pourquoinouschoisir");
  	//inséré dans le mail pour pour tracking email
    $tracking = "<div style=\"display:none;font-size:1px;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;\">#CLI".$user['userid']."#</div>";
    $vars=array("nom"=>$user['nom'],"prenom"=>$user['prenom'],"tracking"=>$tracking);
    
    $emc=new email_client($DB_site,$template,null,"relanceauto".$template_articles.$template->parts["pro"],$vars);
    // var_dump($emc->body);
    
    $mail=new email();
    $mail->destinataire = $user['mail'];
    $mail->destname     = $user['prenom']." ".$user['nom'];
    $mail->expediteur   = $template->translate("/site/[site_code]/mail_expediteur");
    $mail->expname      = $template->translate("/site/nom");
    $mail->copymode     = "bcc";
    $mail->to           = array("contact@mon-espace-cuisson.com");
    $mail->subject      = $emc->subject;
    $mail->body         = $emc->body;
    $mail->sendmail();
    echo "- ".$user['mail']."<br>";
    $DB_site->query("INSERT INTO mail_outbox SET date=".time().", utilisateur='', `to`='".$user['mail']."', subject='".addslashes($emc->subject)."', body='".addslashes($emc->body_original)."', userid='".$user['userid']."', uniqueid='".$mail->uniqueid."', compta='non'");
}
print "<p>".$DB_site->num_rows($req)." mails envoy&eacute;s</p>";



// passage en client perdu (raison 'sans conseil') les nouveaux inscrits particuliers qui ont cochés 'non' aux conseils et 'non' à la newsletter et qui n'ont pas de suivi commercial
echo "<br><br>passage en client perdu les conseils>non et newsletter>non :<br>";
$subdate2=time()-(2*60*60); // il y a 2 heures
$subdate1=$subdate2-(24*60*60); // il y a 24 heures

$sql="SELECT * FROM utilisateur WHERE
  UNIX_TIMESTAMP(dateinscription) BETWEEN ".$subdate1." AND ".$subdate2." AND provenance IN ('site','configurateur')
  AND conseils='non' AND allowemail='0' AND raisonsociale=''
  AND (SELECT COUNT(facturid) FROM facture WHERE userid=utilisateur.userid AND moyenid>0)=0";
//  echo $sql;
$req=$DB_site->query($sql);
while($user=$DB_site->fetch_array($req)){
	// on vérifie qu'il n'y ai pas de suivi commercial 
	$sqlSub1 = "SELECT COUNT(*) FROM facture ";
	$sqlSub1 .= " WHERE facture.userid = '".$user[userid]."'";
	
	$suivi_commande=$DB_site->query_first($sqlSub1);
	
	$sqlSub2 = "SELECT COUNT(*)";
	$sqlSub2 .= " FROM suivi_client ";
	$sqlSub2 .= " WHERE userid = ".$user[userid];
	$sqlSub2 .= " AND suivi_client.commercial NOT IN ('client') ";
	$sqlSub2 .= " AND etat != 0";
	
	 echo $sqlSub2."<br>";
	$suivi_client=$DB_site->query_first($sqlSub2);
	
	if($suivi_commande[0]==0 && $suivi_client[0]==0){
        $req = "SELECT COUNT(*) FROM suivi_client WHERE userid = ".$user[userid]." AND commercial='auto' AND moyen ='cron' AND etat=0";
        $deja_marque = $DB_site->query_first($req);
        if($deja_marque[0] == 0){
    		echo "- ".$user['mail']."<br>";
    		$sql="INSERT INTO suivi_client (type,moyen,destinataire,userid,commercial,date,suivi,rappel,rappeltype,etat)
    				VALUES ('client','cron','".$user[userid]."','".$user[userid]."','auto','".time()."','le client a été marqué automatiquement comme perdu car pas de conseil et pas de newsletter',0,'int','0')";

    		$DB_site->query($sql);
    		$sql="INSERT INTO causeperte (userid,date,cause)
    				VALUES ('".$user[userid]."','".time()."','13')";
    		
    		$DB_site->query($sql);
		}
	}
}




// passage en client perdu les nouveaux inscrits particuliers qui ont cochés 'oui' à la newsletter et qui n'ont pas de suivi commercial sans nouvelles depuis 3 jours

echo "<br><br>passage en client perdu:<br>";
$subdate2 = time()-(120*60*60); // il y a 5 jours
$subdate1 = $subdate2 - (24*60*60); //il y a 6 jours

$sql="SELECT * FROM utilisateur WHERE
  UNIX_TIMESTAMP(dateinscription) BETWEEN ".$subdate1." AND ".$subdate2." AND provenance IN ('site','configurateur') AND conseils='oui' AND langue='FR'
  AND (SELECT COUNT(facturid) FROM facture WHERE userid=utilisateur.userid AND moyenid>0)=0 AND (SELECT COUNT(id) FROM suivi_client WHERE type='client' AND moyen='cron' AND userid=utilisateur.userid AND suivi='le client a été marqué automatiquement comme perdu car pas de conseil et 3 jours d attente') = 0";

$req=$DB_site->query($sql);
while($user=$DB_site->fetch_array($req)){
	// on vérifie qu'il n'y ai pas de suivi commercial 
	$sqlSub1 = "SELECT COUNT(*) FROM facture ";
	$sqlSub1 .= " WHERE facture.userid = '".$user[userid]."'";
	// echo $sqlSub1."<br>";
	$suivi_commande=$DB_site->query_first($sqlSub1);
	
	$sqlSub2 = "SELECT COUNT(*)";
	$sqlSub2 .= " FROM suivi_client ";
	$sqlSub2 .= " WHERE userid = ".$user[userid];
	$sqlSub2 .= " AND suivi_client.commercial NOT IN ('', 'client') ";
	//echo $sqlSub2."<br>";
	$suivi_client=$DB_site->query_first($sqlSub2);
	
	if($suivi_commande[0]==0 && $suivi_client[0]==0){
		
		
		if($user['site_code']=="") $user['site_code']="base";
		if($user['langue']=="") $user['langue']="FR";
		$template=new template($user['langue'],$DB_site);
		$template->changesite($user['site_code'],$user['langue']);
		$template->pro=$pro=($user['raisonsociale']!="" && $user['site_code']!="uk");
		$template->parts["pro"]=($pro?"_pro":"");
		$template->parts["autocolor"]=($pro?"#0393c2":"#a88d5c");
		$template->parts['nom'] =  $user['nom'];
		$template->parts['prenom'] = $user['prenom'];

		//inséré dans le mail pour pour tracking email
    	/*$tracking = "<div style=\"display:none;font-size:1px;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;\">#CLI".$user['userid']."#</div>";
		$vars=array("nom"=>$user['nom'],"prenom"=>$user['prenom'],"tracking"=>$tracking);
		$emc=new email_client($DB_site,$template,null,"relanceauto_conseils".$template->parts["pro"],$vars);
		
		$mail=new email();
		$mail->destinataire = $user['mail'];
		$mail->destname     = $user['prenom']." ".$user['nom'];
		$mail->expediteur   = $template->translate("/site/[site_code]/mail_expediteur");
		$mail->expname      = $template->translate("/site/nom");
		$mail->copymode     = "bcc";
		$mail->to           = array("pr@mon-espace-cuisson.com");
		$mail->subject      = $emc->subject;
		$mail->body         = $emc->body;
		// $mail->save			= "./log/log_mail.txt";
	    $mail->sendmail();*/
		echo "- ".$user['mail']."<br>";
	    //$DB_site->query("INSERT INTO mail_outbox SET date=".time().", utilisateur='', `to`='".$user['mail']."', subject='".addslashes($emc->subject)."', body='".addslashes($emc->body_original)."', userid='".$user['userid']."', uniqueid='".$mail->uniqueid."', compta='non'");

        $sql="INSERT INTO suivi_client (type,moyen,destinataire,userid,commercial,date,suivi,rappel,rappeltype,etat)
                VALUES ('client','cron','".$user[userid]."','".$user[userid]."','','".(time()+120)."','le client a été marqué automatiquement comme perdu car pas de conseil et 3 jours d attente',0,'int','0')";
        $DB_site->query($sql);

        $sql="INSERT INTO causeperte (userid,date,cause)
                VALUES ('".$user[userid]."','".(time()+120)."','13')";
        $DB_site->query($sql);
	}

}



function vignette_mail($artid){
    global $DB_site;
    global $template;
    $article=$DB_site->query_first("SELECT artid,typearticleid,libelle".$template->langue." AS libelle,libelle".$template->langue.",artcode,(SELECT active FROM article_site WHERE artid=".intval($artid)." AND site_code='".$template->site_code()."' LIMIT 1) AS active,commandable,gamme".$template->langue." AS gamme,modele FROM article WHERE artid=".intval($artid));
    if($article[0]=="") return "";
    
    $mq=$DB_site->query_first("select libelle,m.marqueid,image,m.type".$template->langue." AS mtype from marque m, article_marque am where m.marqueid = am.marqueid and am.artid = '$artid'") ;
    $article['marque']=$mq['libelle'];
    
    $host=$template->translate("/site/[site_code]/url");
    if($template->parts["pro"]!="") $host=str_replace("www","pro",$host);
    $template->parts["arturl"]=$host.$template->mkurl("article",$artid,$article);
    $template->parts['libelle']=$article['libelle'];
    $template->parts['ref']=strtoupper($mq['libelle']." ".$article['gamme']."<br>".$article['artcode']);
    $template->parts["logomarque"]="<img src=\"https://www.ma-cave-a-vin.fr/configurations/www.ma-cave-a-vin.fr/images/marques/br/".$mq[marqueid].".jpg\" alt=\"".$mq['mtype']."\" width=\"90\" style=\"width:90px;max-height:24px;\">";
    
    $imgartid=$artid;
    $packs=new Pack($DB_site);
    $kdopid=$packs->kdos_pack($artid);
    $width=132;
    $height=202;
    $imgpath="https://www.ma-cave-a-vin.fr".article_image($artid,$width,$height,$kdopid);
    $template->parts["artimg"]="<a href=\"".$template->parts["arturl"]."\"><img src=\"".$imgpath."\" alt=\"".$article['libelle']." ".$mq[0]."\" width=\"".$width."\" height=\"".$height."\"></a>";
    
    $taxtxt=($sitepro?"/site/ht":"/site/ttc");
    $article_prices=article_prices($artid,$template->tva_ref(),$template->site_code(),$sitepro);
    $promo=$DB_site->query_first("SELECT COUNT(*) FROM promo WHERE artid=".$artid);
    $secondchoix=$DB_site->query_first("SELECT COUNT(*) FROM secondchoix WHERE artid=".$artid);

    $pc="";
    if(round($article_prices['prix'])!=round($article_prices['prixconseille']) && ($promo[0]>0 || $secondchoix[0]>0)){
	$strikeout="<s>".$template->price_format($article_prices['prixconseille'])." ".$template->translate($taxtxt,"U")."</s>";
	$pc="<div style=\"width:25px;height:25px;background:#df4126;color:white;padding:5px;font-size:12px;font-weight:bold;text-align:center;line-height:25px;\">".-round($article_prices['remise'])."%</div>";
    }
    $template->parts['prix']="<table width=\"100%\" border=\"0\" style=\"font-size:12px;\">
    <tr>
      <td height=\"20\">".$strikeout."</td>
      <td rowspan=\"2\">".$pc."</td>
    </tr>
    <tr>
      <td height=\"20\" style=\"color:black;font-size:14px;font-weight:bold;white-space:nowrap;\">".$template->price_format($article_prices['prix'])." ".$template->translate($taxtxt,"U")."</td>
    </tr>
    </table>";
    
    $template->parts['article']="";
    $template->evaltemplate("vignetteMail","article");
    return $template->parts['article'];
}

?>