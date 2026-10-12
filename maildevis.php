<?php

require_once("global.php");
include_once("class/projet.class.php");
ini_set("display_errors",1);

$facture=new facture($DB_site,$_GET['id']);
$client=$facture->utilisateur;
$projets=new projets();
$clprojets=$client->projets($projets->encours);
$projetsclos=$client->projets($projets->clos);
$template->newlangue($client->langue);
if(!isset($_COOKIE['admin_username']) || isset($_POST['projetid']) ){

    $commercial="";
    if(isset($_COOKIE['admin_username']) && $_COOKIE['admin_username']!="") {
        $commercial=$_COOKIE['admin_username'];

        $com = $DB_site->query_first("SELECT * FROM admin_contact WHERE username='$commercial'");
        $template->parts['nom_complet'] = $com['nom_contact'.$template->langue] ;
        $template->parts['fonction']    = $com['fonction'.$template->langue];
        $template->parts['mail']        = $com['mail'.$template->langue];
        $template->parts['tel']         = $com['tel'.$template->langue];
        $template->parts['url']         = $template->translate("/site/url");
        $template->evaltemplate("SignatureMail","contact");
    }else{
        $template->parts['contact'] = $template->translate("/devis/mail_signature_defaut");
    }
    
    $tracking="#CDE".$facture->facturid."#";
    if(isset($_POST['projetid']) && !empty($_POST['projetid'])){
        $projet=new projet($_POST['projetid']);
        $projet->adddevis($facture->facturid);
        $tracking=$projet->pjt;
    }

    $DB_site->query("INSERT INTO envoi_devis SET facturid=".$facture->facturid.", date=".time().", utilisateur='$commercial', mail='".$client->mail."'");

    $p=strrev(md5($facture->facturid."devis"));

    $url = rewrite_url($client->raisonsociale,$template->langue,"",$template,$host);

    $vars=array("facturid"=>$facture->facturid,"p"=>$p,"prenom"=>$client->prenom,"nom"=>$client->nom,"url"=>$url);
    $ec=new email_client($DB_site,$template,null,"devis",$vars);

    $site_url_devis = $template->translate("/site/[site_code]/url");

    $paiements="";
    if(isset($_GET['paiements'])){
        $paiements="&paiements=".$_GET['paiements'];
        $listpaiements=json_decode($_GET['paiements']);
        $txt="Facilités de paiement :";
        foreach($listpaiements as $paiement){
            $moyen=$DB_site->query_first("SELECT * FROM moyenpaiement WHERE moyenid=".intval($paiement->moyen));
            $delais=$DB_site->query_first("SELECT * FROM reglement_delais WHERE reglement_delais_id=".intval($paiement->delais));
            $txt.="<br>".$template->price_format($paiement->value)." par ".$moyen['libelleFR']." ".$delais['reglement_delais_libelle']." le ".$paiement->date;
        }

        $DB_site->query("INSERT INTO suivi_commande SET facturid=".$facture->facturid.", date=".time().", commercial='".mysqli_real_escape_string($DB_site->link_id,$commercial)."', suivi='".mysqli_real_escape_string($DB_site->link_id,$txt)."'");
    }
    //affichage du prix des options
    if(isset($_POST['afficheopt'])){
        $afficheopt = $_POST['afficheopt'];
    }else{
        $afficheopt = 1;
    }
   
    $mail=new email();
    $mail->destinataire=$client->mail;
    $mail->destname=$client->prenom." ".$client->nom;
    $mail->to=array(MAIL_CONTACT);
    $mail->expediteur=$template->translate("/site/base/mail_expediteur");
    $mail->expname=$template->translate("/site/nom");
    $mail->copymode="bcc";
    $mail->subject=$ec->subject;
    $mail->body=$ec->body ;
    //Pour que les devis soient bien créés à partir des commandes passées sur le test
	$filedevis="https://www.mon-espace-cuisson.com/devis.php?devisid=".$facture->facturid."&p=".$p.$paiements."&opt=".$afficheopt;
	// echo $filedevis;
    $mail->attachfile($filedevis, $template->translate("/devis/devis","F")." ".$facture->facturid.".pdf","application/pdf");
    //ajout pj des articles tâche 7209
    $nbpj=1;
    foreach ($facture->lignes as $ligne) {
        if($_POST['pj_'.$ligne->artid]=='1'){
            $req = $DB_site->query("SELECT * FROM article_devisdesc WHERE artid='".$ligne->artid."' AND active='1' AND langue='".$template->langue."'");
            while ($article_pj = $DB_site->fetch_array($req)) {
                if($article_pj['img_ext']!==''){
                    $img_pj = explode('.',$article_pj['img_ext']);
                    $filename = $rootpath."configurations/".$host."/article_description/".$article_pj['langue']."/".$article_pj['img_ext'];
                    $mail->attachfile($filename,$template->translate("/devis/devis","F")." ".$facture->facturid."_$nbpj.".$img_pj[1],mime_content_type($filename));
                }
                else{
                    $filename = $rootpath."configurations/".$host."/article_description/".$article_pj['langue']."/".$article_pj['id'].".".$article_pj['ext'];
                    $mail->attachfile($filename,$template->translate("/devis/devis","F")." ".$facture->facturid."_$nbpj.".$article_pj['ext'],mime_content_type($filename));
                }
                $nbpj++;
            }
        }
    }
    $result=$mail->sendmail();
    $content="<p align=\"center\">";
    if($result){
        //$content.=sprintf($template->translate("/devis/devis_envoye"),$client->mail);
        $texte="<texte_devis>".sprintf($template->translate("/devis/devis_envoye"),$client->mail)."</texte_devis>";
    }
    else{
        //$content.=$template->translate("/devis/erreur_envoi");
        $texte="<texte_devis>".$template->translate("/devis/erreur_envoi")."</texte_devis>";
    }
    $content.="</p>";
    
    $autoclose="autoclose()";
}else{

    $optpjt="<option value=\"\">Aucun</option>";
    if(count($clprojets)>0) {
        usort($clprojets,"tri");
        $optpjt.="<option value=''>En cours :</option>";
    }
    foreach($clprojets as $projet){
        $optpjt.="<option value=\"".$projet->projetid."\">".($projet->facturid==0?'':'CDE'.$projet->facturid.' - ')." PJT ".$projet->projetid." du ".$projet->date->format("d/m/Y")." - ".$projet->libelle.(!is_null($projet->dateecheance)?' - Ech.' .$projet->dateecheance->format("d/m/Y"):"")." - ".$projet->commercial."</option>";
    }
    if(count($projetsclos)>0) {
        usort($projetsclos,"tri");
        $optpjt.="<option value=''>Clos :</option>";
    }
    foreach($projetsclos as $projet){
        $optpjt.="<option value=\"".$projet->projetid."\">".($projet->facturid==0?'':'CDE'.$projet->facturid.' - ')." PJT ".$projet->projetid." du ".$projet->date->format("d/m/Y")." - ".$projet->libelle.(!is_null($projet->dateecheance)?' - Ech.' .$projet->dateecheance->format("d/m/Y"):"")." - ".$projet->commercial."</option>";
    }

    //// tache 7033 et 7209
    $is_pj=$is_options=false;
    foreach ($facture->lignes as $ligne){
        $req_options = $DB_site->query("SELECT * FROM article_caractval WHERE artid='".$ligne->artid."'");
        if($DB_site->num_rows($req_options) > 0 && !is_null($req_options)){
            $is_options = true;
        }

        $img_pj=$texte_pj="";
        $req = $DB_site->query("SELECT * FROM article_devisdesc WHERE artid='".$ligne->artid."' AND active='1' AND langue='".$template->langue."'");
        while ($article_pj = $DB_site->fetch_array($req)) {
            $texte_pj="<div style=\"margin-left:20px\">Images envoyées pour produit ".$ligne->artcode."<input type=\"radio\" id=\"pjoui_".$ligne->artid."\" name=\"pj_".$ligne->artid."\" value=\"1\"><label for=\"pjoui_".$ligne->artid."\">Oui</label>
    <input type=\"radio\" id=\"pjnon_".$ligne->artid."\" name=\"pj_".$ligne->artid."\" value=\"0\" checked><label for=\"pjnon_".$ligne->artid."\">Non</label><br>";
            $is_pj = true;
            $img_pj.="<img src=\"/configurations/www.mon-espace-cuisson.com/article_description/".$article_pj['langue']."/".$article_pj['img_ext']."\" style=\"max-width:100px;max-height:100px;margin-right: 20px;display:inline-block;\" />";
        }
        $textes_pj.=$texte_pj.$img_pj."</div>";
    }

    if($is_pj == true){
        $afficher_pj = "<p>Afficher les pièces jointes avec le devis ? ".$textes_pj."</p>";
    }
    
    if($is_options == true){
        $afficher_options = '<p>Afficher le prix des options ? 
    <input type="radio" id="ouiopt" name="afficheopt" value="1"><label for="ouiopt">Oui</label>
    <input type="radio" id="nonopt" name="afficheopt" value="0" checked><label for="nonopt">Non</label>
    </p>';
    }
    
    $content=<<<HTML
    <form method="post">
    <p>Associer à un projet : <select name="projetid">$optpjt</select></p>
    $afficher_options
    $afficher_pj
    <input type="submit">
    </form>
HTML;
    
    $autoclose="";
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html>

<head>
<title></title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<script language="javascript">
function autoclose(){
    setTimeout(function(){
        window.close();
    },5000);
}

</script>

</head>
<body bgcolor="#FFFFFF" onload="<?=$autoclose; ?>">

<?=$content; ?>
<?=$texte; ?>
</body>

</html>
<?php
function tri($a, $b){ // tri par date desc
    if ($a->date == $b->date) {
        return 0;
    }
    return ($a->date > $b->date) ? -1 : 1;

} ?>