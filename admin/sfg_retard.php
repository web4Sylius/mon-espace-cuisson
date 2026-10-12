<?php
require_once __DIR__.'/../secrets.php';


$host="www.mon-espace-cuisson.com" ;
include("admin_global.php");

include_once($rootpath."admin/ftp.php");
include_once($rootpath."admin/adv/adv.class.php");
include_once($rootpath."class/garantie.class.php");
include_once($rootpath."admin/adv/envoi_fournisseur.php");

$facture=$DB_site2->query_first("SELECT f.*,UNIX_TIMESTAMP(datefacture) AS tsdate, UNIX_TIMESTAMP(dateexpedition) AS tsenvoi FROM facture f WHERE f.facturid=".$facturid);
$user=$DB_site2->query_first("SELECT * FROM utilisateur u WHERE u.userid=".$facture['userid']);
// $produits=$DB_site2->query("SELECT * FROM lignefacturecaracteristique lc, lignefacture l WHERE l.factureid=".$facture['facturid']." AND (lc.caractvalid =10 OR lc.caractvalid =11) AND lc.lignefactureid = l.lignefactureid");
$advid=$DB_site2->query_first("SELECT adv.id AS advid FROM adv WHERE adv.type='garantie' AND adv.destinataire='SFG' AND facturid=".$facture['facturid']);

//Si client présent dans facture_infos_garantie, il faut prendre ces informations là
$tabInfosGarantie = array();
$sql = "SELECT *";
$sql .= " FROM facture_infos_garantie";
$sql .= " WHERE factureid = ".$facture['facturid'];
$tabInfosGarantie = $DB_site2->query_first($sql);

//Remplacement des informations
if(!empty($tabInfosGarantie)){
    $user['nom']        = $tabInfosGarantie['facture_infos_garantie_nom'];
    $user['prenom']     = $tabInfosGarantie['facture_infos_garantie_prenom'];
    $user['adresse']    = $tabInfosGarantie['facture_infos_garantie_adresse'];
    $user['codepostal'] = $tabInfosGarantie['facture_infos_garantie_cp'];
    $user['ville']      = $tabInfosGarantie['facture_infos_garantie_ville'];
    $user['telephone']  = $tabInfosGarantie['facture_infos_garantie_tel'];
    $user['mail']       = $tabInfosGarantie['facture_infos_garantie_mail'];
}

print "Commande ".$facture['facturid']." du ".$facture['datefacture']."<br>".$user['nom']." ".$user['prenom']."<br>".$user['codepostal']." ".$user['ville'];


if($facture['valide']!=5 && $facture['valide']!=8){
  print "Cette facture n'est pas expédiée";
  exit;
}

$sfg=$DB_site2->query_first("SELECT * FROM sfg WHERE facturid=".$facturid);
if($sfg['receive']!=""){
  print "Cette facture a déjà été traitée";
  exit;
}

if($advid=="")
  print "Il n'y a pas d'article sous garantie";
else{
  $adv=new adv($DB_site2,$advid['advid']);
  print "<p>Articles</p>";
  foreach($adv->lignes as $lignefactureid=>$qte){
    $article=$DB_site2->query_first("SELECT lf.artid,lf.prix,a.artcode_eurocave,a.artcode,a.libelleFR,m.libelle AS marque FROM lignefacture lf INNER JOIN article a USING(artid) INNER JOIN article_marque USING(artid) INNER JOIN marque m WHERE lignefactureid=".$lignefactureid);
    print $qte." ".$article['libelleFR']." ".$article['artcode']."<br>";
  }
  if(!isset($_GET['send'])) print "<input type=\"button\" value=\"Envoyer la garantie\" onclick=\"document.location='sfg_retard.php?facturid=$facturid&send=1';\">";
}

if(isset($_GET['send'])){
  $filename="mec_".date("Y_m_d")."_".$facturid.".xml";

  $f=fopen($rootpath."admin/sfg/".$filename,"w");
  fputs($f,generatexml());
  fclose($f);    
  
  foreach($adv->lignes as $lignefactureid=>$qte){
     $efl = new envoi_fournisseur_ligne($DB_site2);
    //$efl->expediteur=$user_info['username'];
    $efl->type = "garantie";
    $efl->qte = $qte;
    $efl->paht = $adv->fields["paht_".$lignefactureid];
    $efl->lignefactureid = $lignefactureid;
    $listefl[] = $efl;
    $totalpaht+=$adv->fields["paht_".$lignefactureid];
  }

  $ef = new envoi_fournisseur($DB_site2);
  //$ef->facturid=$facture['facturid'];
  $ef->facturid = $facture[facturid];
  $ef->destinataire = 'SFG';
  $ef->tarif = $totalpaht;
  $ef->type = 'garantie';
  $ef->date = time();
  $gar = $DB_site2->query_first("SELECT id FROM envoi_fournisseur WHERE facturid =".$facture[facturid]." AND destinataire = 'SFG'");
  if($gar[0] != ''){
    $ef->id = $gar[0];
    $ef->update();
  }else{
    $ef->create();
  }
  
  $ef->create_lines($listefl);
  $adv->efid=$ef->id;
  $adv->update();
  
  $ftp=new ftp(false,20);
   $ftp->server="ftp.sfg.fr";
  $ftp->user="mec";
  $ftp->pass=SFG_FTP_PASSWORD;
  $result=$ftp->put($rootpath."admin/sfg/".$filename,$filename);

  if($result)
      print "<p>Envoi réussi <a href=\"javascript:window.close();\">Fermer</a></p>";
  else 
      print "<p>Erreur d'envoi : ".$ftp->error."</p>";

  $ftp->quit();  
}



function generatexml(){
  global $DB_site2;
  global $facture,$user,$adv;
  
  $datefacture=date("d/m/Y",$facture[tsdate]);
  $dateenvoi=date("d/m/Y",$facture[tsenvoi]);

  $snom = trim($user[raisonsociale]." ".$user[nom]);
  if(strlen($snom) > 44){
    $snom = substr($snom, 0,44);
  }

  $xml="<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n<Contrats>\n";

  $xml.="\t<Contrat>
             <LCOSTID>MEC</LCOSTID>
             <SNUMCO>$facture[facturid]</SNUMCO>
             <DACHATC>".$datefacture."</DACHATC>
             <SCIVILITE></SCIVILITE>
             <SNOM>".xml_entities($snom)."</SNOM>
             <SPRENOM>".xml_entities($user['prenom'])."</SPRENOM>
             <SADRESSE1>".xml_entities($user['adresse'])."</SADRESSE1>
             <SADRESSE2></SADRESSE2>
             <SCP>$user[codepostal]</SCP>
             <SVILLE>".xml_entities($user['ville'])."</SVILLE>
             <STELGSM></STELGSM>
             <STELDOM>$user[telephone]</STELDOM>
             <EMAIL>$user[mail]</EMAIL>
             <SOBSERVATION></SOBSERVATION>\n";

  foreach($adv->lignes as $lignefactureid=>$qte){
            $article=$DB_site2->query_first("SELECT lf.artid,lf.prix,lf.prix_revendeur,lfg.dureeReelle,lfg.duree,lfg.garantieid,a.artcode_eurocave,a.artcode,a.libelleFR,m.libelle AS marque FROM lignefacture lf INNER JOIN lignefacturegarantie lfg USING(lignefactureid) INNER JOIN article a USING(artid) INNER JOIN article_marque USING(artid) INNER JOIN marque m WHERE lignefactureid=".$lignefactureid." AND fournisseur = 'SFG'");

	    $garantie=new garantie($DB_site2,$facture['site_code'],$facture['langue'],$article['garantieid']);
	    
	    if($garantie->cumul==2){ // pas d'extension
		$dureeGarantie = $garantie->duree;
		$dureeExtGarantie = 0;
		$dateEffetGarantie = date_create(date("Y-m-d",$facture[tsenvoi]));
		$dateEffetGarantie = date_format($dateEffetGarantie,"d/m/Y");
		
	    }else{ // extension
		$dureeExtGarantie = $garantie->duree;
		$dureeGarantie = 0;
		$dateEffetGarantie = date_create(date("Y-m-d",$facture[tsenvoi]));
                date_add($dateEffetGarantie,date_interval_create_from_date_string("2 years"));
                date_add($dateEffetGarantie,date_interval_create_from_date_string("-1 day"));
                $dateEffetGarantie = date_format($dateEffetGarantie,"d/m/Y");
	    }
	    
	    for($i=0;$i<$qte;$i++){
		$xml.="\t<PRODUIT>
		<SMARQUE>".xml_entities($article['marque'])."</SMARQUE>
		<SMODEL>".substr($article['artcode']." / ".xml_entities($article['artcode_eurocave']),0,25)."</SMODEL>
		<LIBELLE>".xml_entities($article['libelleFR'])."</LIBELLE>
		<NPRIX>".($article['prix_revendeur']>0?$article['prix_revendeur']:$article['prix'])."</NPRIX>
		<SN></SN>
		<DACHAT>".$dateEffetGarantie."</DACHAT>
		<DEFFET>".$dateEffetGarantie."</DEFFET>
		<TDUREE_GAR>".$dureeGarantie."</TDUREE_GAR>
		<TDUREE_EXT>".$dureeExtGarantie."</TDUREE_EXT>
		<SOBSERVATION></SOBSERVATION>";
		
		$garType = '';
		      if($garantie->destinataire_garantie == 2){
			  switch($article['dureeReelle']){
				case 2:
				    $garType = '2ANSPRO';
				    break;
				case 3:
				    $garType = '3AP2PRO';
				    break;
				case 5:
				    $garType = '5ANSPRO';
				    break;
			  }
		      }

		      $xml .= ""
			  . "<SGARTYPE>".$garType."</SGARTYPE>\n"
		      . "</PRODUIT>\n";
	    }

    }
    

    $xml.="\t</Contrat>\n";
    $xml.="</Contrats>";

    return $xml;
}


function xml_entities($string) {
    $string=html_entity_decode($string,ENT_COMPAT,"ISO-8859-15");
    return strtr(
        $string, 
        array(
            "<" => "&lt;",
            ">" => "&gt;",
            '"' => "&quot;",
            "'" => "&apos;",
            "&" => "&amp;",
        )
    );
}

?>