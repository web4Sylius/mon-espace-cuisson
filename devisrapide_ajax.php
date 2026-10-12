<?php


include("global.php");


if(!isset($_GET['mail']) || $_GET['mail']=="") exit;
if(!isset($_GET['nom']) || $_GET['nom']=="") exit;
$devisartid=intval($_GET['devisartid']);

$payslivr=$_GET['pays'];

if($_GET['livrid']!="true" || $_GET['payslivr']==""){
    $_GET['payslivr']=$_GET['pays'];
    $_GET['societe_livr']=$_GET['societe'];
    $_GET['nom_livr']=$_GET['nom'];
    $_GET['prenom_livr']=$_GET['prenom'];
    $_GET['adresse1_livr']=$_GET['adresse1'];
    $_GET['cp_livr']=$_GET['cp'];
    $_GET['ville_livr']=$_GET['ville'];
    $_GET['telephone_livr']=$_GET['telephone'];
}


$testuser=$DB_site->query_first("SELECT * FROM utilisateur WHERE mail='".mysqli_real_escape_string($DB_site->link_id, $_GET['mail'])."'");

$client = $testuser;

$result=$DB_site->query("INSERT INTO facture SET userid='".$client[userid]."', datefacture=NOW(), heurefacture=".mktime().", raisonsociale='".mysqli_real_escape_string($DB_site->link_id, $_GET['societe_livr'])."', nom='".mysqli_real_escape_string($DB_site->link_id, $_GET['nom_livr'])."', prenom='".mysqli_real_escape_string($DB_site->link_id, $_GET['prenom_livr'])."', codepostal='".mysqli_real_escape_string($DB_site->link_id, $_GET['cp_livr'])."', adresse='".mysqli_real_escape_string($DB_site->link_id, $_GET['adresse1_livr'])."', ville='".mysqli_real_escape_string($DB_site->link_id, $_GET['ville_livr'])."', telephone='".mysqli_real_escape_string($DB_site->link_id, $_GET['telephone_livr'])."', paysid='".intval($_GET['payslivr'])."',mail='".mysqli_real_escape_string($DB_site->link_id, $client['mail'])."',modelivraison='0', site_code='".$template->site_code()."'");

$facturid=$DB_site->insert_id();

$taux=$DB_site->query_first("SELECT 0, TVAtauxnormal, TVAtauxreduit,cee,tvaintracom FROM pays WHERE paysid = '".intval($_GET['payslivr'])."'");

$articles=array();
$packs=new Pack($DB_site);
if($packs->isPack($devisartid)){
    foreach($packs->getProduits($devisartid) as $article)
		$articles[]=$article;
}else{
    $article=$DB_site->query_first("SELECT * FROM article WHERE artid='".$devisartid."'");
    $articles=array($article);
}

foreach($articles as $article){
    $prixht=($client['tvaintracom']!="" && $taux['tvaintracom']==1) || $taux[$article['tauxchoisi']]==0;

    $article_prices=article_prices($article['artid'],$template->tva_ref(),$template->site_code(),$prixht);
    $tva=($prixht ? 0 : floatval($taux[$article['tauxchoisi']]));

    $DB_site->query("INSERT INTO lignefacture SET factureid='$facturid',artid='".intval($article['artid'])."', catid='".$article['catid']."', artcode='".$article['artcode']."', qte=1, libelle='".addslashes($article['libelle'.$template->langue])."', prix='".floatval($article_prices['prix'])."', tva=".$tva.", paht=".floatval($article['paht']).", ecopart=".round($article['ecopartht']*(1+$tva/100),2));
    $lfid=$DB_site->insert_id();

    $prixcaract=0;
    $art_caracts=$DB_site->query("SELECT caractid FROM article_caractval ac inner join caracteristiquevaleur cv using(caractvalid) WHERE ac.artid=".intval($article['artid'])." AND subcaractid=0 AND caractid NOT IN (SELECT subcaractid fROM caracteristiquevaleur WHERE subcaractid>0) GROUP BY caractid");
    while($art_caract=$DB_site->fetch_array($art_caracts)){
		$caract=$DB_site->query_first("SELECT ac.caractvalid,ac.achat,ac.differenceprix,cv.libelle".$template->langue." AS caractvalname,c.libelle".$template->langue." AS caractname FROM article_caractval ac INNER JOIN caracteristiquevaleur cv USING(caractvalid) INNER JOIN caracteristique c USING(caractid) WHERE ac.artid=".intval($article['artid'])." AND cv.caractid=".$art_caract[0]." ORDER BY cv.position LIMIT 1");
		$DB_site->query("INSERT INTO lignefacturecaracteristique SET lignefactureid=$lfid, caractvalid='".$caract['caractvalid']."', libelle_caract='".mysqli_real_escape_string($DB_site->link_id, $caract['caractname'])."', libelle_caractval='".mysqli_real_escape_string($DB_site->link_id, $caract['caractvalname'])."', differenceprix=".floatval($caract['differenceprix']).", paht=".floatval($caract['achat']));
		$prixcaract+=$caract['differenceprix'];
    }

    $lesGaranties=DAOgarantie::loadMaxGarantieArtByPays($DB_site,$template->site_code(),'FR',$_GET['pays'],$article['artid']);
    $dureeBase=0;
    $garantieDeBaseAffiche="";

    if(count($lesGaranties)>0){

		for ($i=0; $i < count($lesGaranties); $i++){

			$duree=$lesGaranties[$i]->duree;
			$dureeAf=$duree;
			$typeGarantie=$template->translate("/article/garantie_type/garantie",'F');

			if ($lesGaranties[$i]->cumul==1) {
			    //on cherche dans le tableau qui contient toutes les garanties offertes celle du fournisseur de produit
				$pointer=DAOgarantie::searchGarantieToAddExtension($DB_site,$lesGaranties[$i]->type_garantie,$lesGaranties,$lesGaranties[$i]->garantieid);
			    //on prend la durée de cette garantie pour l'ajouter à l'extension
			    $dureeAf+=$lesGaranties[$pointer]->duree;
			    $typeGarantie=$template->translate("/article/garantie_type/extension_garantie",'F')." +";
			}

			$an=$template->translate("/article/garantie_type/an");
			if ($lesGaranties[$i]->duree>1) {
			    $an=$template->translate("/article/garantie_type/ans");
			}

			//pour la meilleure garantie incluse/gratuite
			if($i==0){
	  			$ppi=$lesGaranties[$i]->ppi;
	  			$prix=$lesGaranties[$i]->prix;
	  			$dureeBase=$lesGaranties[$i]->duree;
	  			$dureeDeBaseAf=$dureeAf;

	  			if($dureeAf!=99){
	    			$anneeGarantie=$dureeAf." ".$an;
	  			} else {
	    			$anneeGarantie=$template->translate("/article/garantie_type/a_vie",'F');
  				}

	  			$garantieDeBaseAffiche=$anneeGarantie." ".$template->translate("/article/garantie_type/".$lesGaranties[$i]->type_garantie);
	  			$pointer=0;

	  			if ($lesGaranties[$i]->cumul==1) {
	    			//on cherche dans le tableau qui contient toutes les garanties offertes celle du fournisseur de produit
	    			$pointer=DAOgarantie::searchGarantieToAddExtension($DB_site,$lesGaranties[$i]->type_garantie,$lesGaranties,$lesGaranties[$i]->garantieid);
	    			//on prend la durée de cette garantie pour l'ajouter à l'extension
				    $dureeBase+=$lesGaranties[$pointer]->duree;
				    $ppi+=$lesGaranties[$pointer]->ppi;
				    $prix+=$lesGaranties[$pointer]->prix;
	  			}

	  			$auLieuDe="";
	  			if ($ppi>$prix) {
	    			$auLieuDe=" ".$template->translate("/article/garantie_type/au_lieu_de")." ".$ppi."&euro;";
	  			}
			}

			$libGarantie=$typeGarantie." ".$duree." ".$an." ".$template->translate("/article/garantie_type/".$lesGaranties[$i]->type_garantie)." ".DAOpays::getPaysById($DB_site,$_GET['pays']);
	
			$add=true;
			//on vérifie que ce fournisseur de garantie n'est pas déjà présent dans la table avec une garantie non cumulable dont la durée serait supérieure à celle-ci
			if ($DB_site->num_rows($DB_site->query("SELECT * FROM lignefacturegarantie lfg INNER JOIN garantie g ON g.garantieid=lfg.garantieid WHERE lignefactureid=".$lfid." AND lfg.fournisseur='".$lesGaranties[$i]->fournisseur."' AND g.cumul=2 AND dureeReelle>".$lesGaranties[$i]->duree." AND lfg.garantieid<>".$lesGaranties[$i]->garantieid.";"))>0) {
	  			$add=false;
			}

			if ($DB_site->num_rows($DB_site->query("SELECT * FROM lignefacturegarantie WHERE lignefactureid=".$lfid." AND garantieid=".$lesGaranties[$i]->garantieid.";"))==0 && $add) {
	  		//si elles n'existent pas déjà on enregistre la garantie constructeur/de base
	  		DAOlignefacturegarantie::insert($DB_site,$template->site_code(),$lesGaranties[$i]->garantieid,$article['artid'],$lfid,$typeGarantie,$dureeAf,$lesGaranties[$i]->fournisseur,DAOfournisseur::loadCodeFournisseurByName($DB_site,$lesGaranties[$i]->fournisseur),$_GET['pays'],$duree,$lesGaranties[$i]->type_garantie,$lesGaranties[$i]->prix,$lesGaranties[$i]->ppi,$lesGaranties[$i]->paht);
			}
      	}
    }

    $DB_site->query("UPDATE lignefacture SET prix=prix+".$prixcaract." WHERE lignefactureid=".$lfid);
}

$taux=$DB_site->query_first("SELECT * FROM pays WHERE paysid = '$client[paysid_livraison]'");

if($taux[port] == '0')
    $tauxport = 0 ;
elseif($prixht)
	$tauxport = 0 ;
else
    $tauxport = $taux[TVAtauxnormal] ;

$devises = new devises($DB_site);
$change  = $devises->getchange();

$pro = false;                           
if($user_info['raisonsociale'] != "") $pro = true;

$frais_port=new frais_port($DB_site,array($article['artid']=>1),$client['paysid_livraison'],floatval($article['prix']),floatval($article['paht']),$template->site_code(),$pro, $client[userid]);
$DB_site->query("UPDATE facture SET prixport=".$frais_port->prixttc($tauxport,$change[$template->devise()]).", tvaport='".floatval($tauxport)."' WHERE facturid=".$facturid);

$p=strrev(md5($facturid."devis"));

$url = rewrite_url($client['raisonsociale'],$template->langue,"",$template,$host);

$vars=array("nom"=>$client['nom'],"prenom"=>$client['prenom'],"facturid"=>$facturid,"p"=>$p,"url"=>$url);
$ec=new email_client($DB_site,$template,null,"devis",$vars);

$session = $_COOKIE['s'];

$mail=new email();
$mail->destinataire=$client['mail'];
$mail->destname=$client['prenom']." ".$client['nom'];
$mail->to=array(MAIL_COM1);
$mail->expediteur=$template->translate("/site/mail_expediteur");
$mail->expname=$template->translate("/site/nom");
$mail->copymode="bcc";
$mail->subject=$ec->subject;
$mail->body=$ec->body;
$mail->attachfile("https://".$host."/devis.php?devisid=$facturid&p=$p&session=$session",$template->translate("/devis/devis","F")." ".$facturid.".pdf","application/pdf");
$result=$mail->sendmail();
if($result==true) 
  print sprintf($template->translate("/devis/devis_rapide_envoye"),$client['mail']);

$commercial="";
if(isset($_COOKIE['admin_username']) && $_COOKIE['admin_username']!="") $commercial=$_COOKIE['admin_username'];
$DB_site->query("INSERT INTO envoi_devis SET facturid=".$facturid.", date=".time().", utilisateur='".mysqli_real_escape_string($DB_site->link_id, $commercial)."', mail='".mysqli_real_escape_string($DB_site->link_id, $client['mail'])."'");

function newpass(){

    $chrlist="abcdefghijklmnopqrstuvwxyz0123456789";
    $maxchr=strlen($chrlist);
    $chrnb=7;

    for($i=0;$i<intval($chrnb);$i++){
           $rdm=rand(0,$maxchr-1);
           $chr=substr($chrlist,$rdm,1);
           $result.=$chr;
    }

    return $result;
}

?>
