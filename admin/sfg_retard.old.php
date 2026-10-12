<?php
require_once __DIR__.'/../secrets.php';


$rootpath="/homez.368/macaveav/www/";
$host="www.ma-cave-a-vin.fr" ;
include("admin_global.php");

include_once($rootpath."admin/ftp.php");


$facture=$DB_site2->query_first("SELECT * FROM facture f WHERE f.facturid=".$facturid);
$user=$DB_site2->query_first("SELECT * FROM utilisateur u WHERE u.userid=".$facture['userid']);
$produits=$DB_site2->query("SELECT * FROM lignefacturecaracteristique lc, lignefacture l WHERE l.factureid=".$facture['facturid']." AND (lc.caractvalid =10 OR lc.caractvalid =11) AND lc.lignefactureid = l.lignefactureid");

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

if($DB_site2->num_rows($produits)==0)
  print "Il n'y a pas d'article sous garantie";
else{
  print "<p>Articles</p>";
  while($p=$DB_site2->fetch_array($produits)){
    print $p['qte']." ".$p['libelle']." ".$p['artcode']."<br>";
  }
  mysql_data_seek($produits,0);
  if(!isset($_GET['send'])) print "<input type=\"button\" value=\"Envoyer la garantie\" onclick=\"document.location='sfg_retard.php?facturid=$facturid&send=1';\">";
}

if(isset($_GET['send'])){
    $filename="mcav_".date("Y_m_d")."_".$facturid.".xml";

    $f=fopen($rootpath."admin/sfg/".$filename,"w");
    fputs($f,generatexml());
    fclose($f);

    $ftp=new ftp();
    $ftp->server="ftp.sfg.fr";
    $ftp->user=SFG_OLD_FTP_USER;
    $ftp->pass=SFG_OLD_FTP_PASSWORD;
    $result=$ftp->put($rootpath."admin/sfg/".$filename,$filename);
    if($result)
        print "<p>Envoi réussi <a href=\"javascript:window.close();\">Fermer</a></p>";
    else
        print "<p>Erreur d'envoi : ".$ftp->error."</p>";
}



function generatexml(){
  global $DB_site2;
  global $facture,$user,$produits;
  $datelivr=date("d/m/Y");

  $xml="<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n<Contrats>\n";

  $xml.="\t<Contrat>
             <LCOSTID>MCAV</LCOSTID>
             <SNUMCO>$facture[facturid]</SNUMCO>
             <DACHATC>".$datefacture."</DACHATC>
             <SCIVILITE></SCIVILITE>
             <SNOM>".xml_entities(trim($user[raisonsociale]." ".$user[nom]))."</SNOM>
             <SPRENOM>".xml_entities($user['prenom'])."</SPRENOM>
             <SADRESSE1>".xml_entities($user['adresse'])."</SADRESSE1>
             <SADRESSE2></SADRESSE2>
             <SCP>$user[codepostal]</SCP>
             <SVILLE>".xml_entities($user['ville'])."</SVILLE>
             <STELGSM></STELGSM>
             <STELDOM>$user[telephone]</STELDOM>
             <EMAIL>$user[mail]</EMAIL>
             <SOBSERVATION></SOBSERVATION>\n";

  while($produit=mysql_fetch_object($produits)){
            $marque=$DB_site2->query_first("SELECT * FROM article_marque am, marque m WHERE am.artid=".$produit->artid." AND am.marqueid=m.marqueid");
            $article=$DB_site2->query_first("SELECT * FROM article WHERE artid=".$produit->artid);
	    $grefs=$DB_site2->query("SELECT * FROM article_garantie WHERE artid=".$produit->artid);

	    if($DB_site2->num_rows($grefs)==0){
		$xml.="\t<PRODUIT>
		<SMARQUE>$marque[libelle]</SMARQUE>
		<SMODEL>".substr($produit->artcode." / ".xml_entities($article['artcode_eurocave']),0,25)."</SMODEL>
		<LIBELLE>".xml_entities($produit->libelle)."</LIBELLE>
		<NPRIX>$produit->prix</NPRIX>
		<SN></SN>
		<DACHAT>$datelivr</DACHAT>
		<DEFFET>$datelivr</DEFFET>
		<TDUREE_GAR>".dureegarantie($produit->caractvalid)."</TDUREE_GAR>
		<TDUREE_EXT>0</TDUREE_EXT>
		<SOBSERVATION></SOBSERVATION>
		</PRODUIT>\n";
	    }else{
		while($gref=$DB_site2->fetch_array($grefs)){
		    for($i=0;$i<$produit->qte;$i++){
			$xml.="<PRODUIT>
		      <SMARQUE>$marque[libelle]</SMARQUE>
		      <SMODEL>".substr($produit->artcode." / ".xml_entities($gref['ref']),0,25)."</SMODEL>
		      <LIBELLE>".xml_entities($produit->libelle)."</LIBELLE>
		      <NPRIX>".$gref['prix']."</NPRIX>
		      <SN></SN>
		      <DACHAT>$datelivr</DACHAT>
		      <DEFFET>$datelivr</DEFFET>
		      <TDUREE_GAR>".dureegarantie($produit->caractvalid)."</TDUREE_GAR>
		      <TDUREE_EXT>0</TDUREE_EXT>
		      <SOBSERVATION></SOBSERVATION>
		      </PRODUIT>\n";
		    }
		}
	    }

    }

    $xml.="\t</Contrat>\n";
    $xml.="</Contrats>";

    return $xml;
}

function dureegarantie($caractvalid){
         switch($caractvalid){
              case 10:return 2;
              case 11:return 5;
         }
         return 0;
}

function xml_entities($string) {
    $string=html_entity_decode($string);
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