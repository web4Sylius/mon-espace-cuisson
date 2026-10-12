<?php

$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
// $rootpath="/home/ma-cave-a-vin.fr/";

chdir($rootpath);
$_SERVER['DOCUMENT_ROOT']=$rootpath;
$_SERVER['SCRIPT_NAME']="admin/script_inscription.php";

include_once($rootpath."configurations/www.ma-cave-a-vin.fr/config_mysql.php");
include_once($rootpath."class/mysql.php");
include($rootpath."class/fonctions.php");
include($rootpath."class/email_client.php");
include_once($rootpath."class/devises.class.php");
include_once($rootpath."class/template.class.php");
include_once($rootpath."class/pack.class.php");
include_once($rootpath."admin/email.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

$delais=3600;
$subdate2=time()-$delais;
$subdate1=$subdate2-$delais;

$sql="SELECT * FROM utilisateur WHERE userid = 19884";

// $sql="SELECT * FROM utilisateur WHERE userid=1704";
$req=$DB_site->query($sql);
while($user=$DB_site->fetch_array($req)){
    print "<p>".$user['nom']." ".$user['prenom']." ".$user['mail']."</p>";
    if($user['site_code']=="") $user['site_code']="base";
    if($user['langue']=="") $user['langue']="FR";
    $template=new template($user['langue'],$DB_site);
    $template->changesite($user['site_code'],$user['langue']);
    $pro=($user['raisonsociale']!="" && $user['site_code']!="uk");
    $template->parts["pro"]=($pro?"_pro":"");
    $template->parts["backcolor"]=($pro?"#ff5a08":"#a40030");
    
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
  
    $vars=array("nom"=>$user['nom'],"prenom"=>$user['prenom']);
    
    $emc=new email_client($DB_site,$template,null,"relanceauto".$template_articles.$template->parts["pro"],$vars);
//     var_dump($emc->body);
    
    $mail=new email();
    $mail->destinataire = $user['mail'];
    $mail->destname     = $user['prenom']." ".$user['nom'];
    $mail->expediteur   = $template->translate("/site/[site_code]/mail_expediteur");
    $mail->expname      = $template->translate("/site/nom");
    $mail->copymode     = "bcc";
    $mail->to           = array("contact@ma-cave-a-vin.fr","webmaster@ma-cave-a-vin.fr");
    $mail->subject      = $emc->subject;
    $mail->body         = $emc->body;
    $mail->sendmail();
    
    $DB_site->query("INSERT INTO mail_outbox SET date=".time().", utilisateur='', `to`='".$user['mail']."', subject='".addslashes($emc->subject)."', body='".addslashes($emc->body_original)."', suivitype='suivi_client', suiviid='".$user['userid']."'");
}

print "<p>".$DB_site->num_rows($req)." résultats</p>";


function vignette_mail($artid){
    global $DB_site;
    global $template;

    $article=$DB_site->query_first("SELECT artid,typearticleid,libelle".$template->langue." AS libelle,artcode,(SELECT active FROM article_site WHERE artid=".intval($artid)." AND site_code='".$template->site_code()."' LIMIT 1) AS active,commandable,gamme".$template->langue." AS gamme,modele FROM article WHERE artid=".intval($artid));
    if($article[0]=="") return "";
    
    $mq=$DB_site->query_first("select libelle,m.marqueid,image,m.type".$template->langue." AS mtype from marque m, article_marque am where m.marqueid = am.marqueid and am.artid = '$artid'") ;
    
    $host=$template->translate("/site/[site_code]/url");
    if($template->parts["pro"]!="") $host=str_replace("www","pro",$host);
    $template->parts["arturl"]=$host."/art,$artid,".url_rewrite($article['libelle']).".htm";
    $template->parts['libelle']=$article['libelle'];
    $template->parts['ref']=strtoupper($mq['libelle']." ".$article['gamme']."<br>".$article['artcode']);
    $template->parts["logomarque"]="<img src=\"configurations/www.ma-cave-a-vin.fr/images/marques/br/".$mq[marqueid].".jpg\" alt=\"".$mq['mtype']."\" width=\"90\" style=\"width:90px;max-height:24px;\">";
    
    $imgartid=$artid;
    $packs=new Pack($DB_site);
    $kdopid=$packs->kdos_pack($artid);
    $width=132;
    $height=202;
    $imgpath="http://www.ma-cave-a-vin.fr".article_image($artid,$width,$height,$kdopid);
    $template->parts["artimg"]="<a href=\"".$template->parts["arturl"]."\"><img src=\"".$imgpath."\" alt=\"".$article['libelle']." ".$mq[0]."\" width=\"".$width."\" height=\"".$height."\"></a>";
    
    $taxtxt=($sitepro?"/site/ht":"/site/ttc");
    $article_prices=article_prices($artid,$template->tva_ref(),$template->site_code(),$sitepro);
    $promo=$DB_site->query_first("SELECT COUNT(*) FROM promo WHERE artid=".$artid);
    $secondchoix=$DB_site->query_first("SELECT COUNT(*) FROM secondchoix WHERE artid=".$artid);

    $pc="";
    if(round($article_prices['prix'])!=round($article_prices['prixconseille']) && ($promo[0]>0 || $secondchoix[0]>0)){
	$strikeout="<s>".$template->price_format($article_prices['prixconseille'])." ".$template->translate($taxtxt,"U")."</s>";
	$pc="<img src=\"http://www.ma-cave-a-vin.fr/images/mail/reduc.php?size=40&pc=".$article_prices['remise']."\">";
    }
    $template->parts['prix']="<table width=\"100%\" border=\"0\" style=\"font-size:12px;\">
    <tr>
      <td height=\"20\">".$strikeout."</td>
      <td rowspan=\"2\">".$pc."</td>
    </tr>
    <tr>
      <td height=\"20\" style=\"color:#E5124F;font-size:13px;font-weight:bold;\">".$template->price_format($article_prices['prix'])." ".$template->translate($taxtxt,"U")."</td>
    </tr>
    </table>";
    
    $template->parts['article']="";
    $template->evaltemplate("vignetteMail","article");
    return $template->parts['article'];
}

?>