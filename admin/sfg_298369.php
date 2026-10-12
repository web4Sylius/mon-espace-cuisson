<?php
require_once __DIR__.'/../secrets.php';

// fichier xml pour SFG

//$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";


//$host="www.ma-cave-a-vin.fr" ;
$nostat=1;
$rootpath="/home/mon-espace-cuisson.com/";
include_once("host_config_admin.php");
//include_once($rootpath."configurations/$host/config_mysql.php");
//include_once($rootpath."class/mysql.php");
//include_once($rootpath."class/email.php");
include_once($rootpath."admin/ftp.php");
include_once($rootpath."class/garantie.class.php");
include_once($rootpath."admin/adv/adv.class.php");
include_once($rootpath."admin/adv/envoi_fournisseur.php");
include_once($rootpath."admin/adv/adv.class.php");

$print="<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>
<Contrats>\n";

//$expdate=$env;
$expdate=time()-86400;
$pasenvoi=" AND efid=0";
if(isset($_GET['test'])){
	$expdate=intval($_GET['test']);
	$pasenvoi="";
}
$writefile=false;
$expdate = 1693823083;
$startmsg="Garanties des commandes expédiées le ".date("d/m/Y",$expdate);
$listmsg="";
$aCmds = array();
$error = false;
var_dump('test');
// $reqfacture=mysql_query("SELECT *,UNIX_TIMESTAMP(datefacture) AS tsdate,UNIX_TIMESTAMP(dateexpedition) AS tsenvoi FROM facture WHERE dateexpedition='".date("Y-m-d",$expdate)."' AND valide IN (5,8)");
$sql = "SELECT f.*,UNIX_TIMESTAMP(datefacture) AS tsdate, UNIX_TIMESTAMP(dateexpedition) AS tsenvoi,adv.id AS advid";
$sql .= " FROM facture f";
$sql .= " INNER JOIN adv USING(facturid)";
$sql .= " WHERE adv.type = 'garantie'";
$sql .= " AND adv.destinataire = 'SFG'";
$sql .= " AND dateexpedition = '".date("Y-m-d",$expdate)."'";
$sql .= " AND valide IN (5,8)";
$sql .= " AND f.facturid = 298369";
$reqfacture = $DB_site2->query($sql);
var_dump($sql);
//Boucle sur les garanties a transmettre
while($facture = $DB_site2->fetch_array($reqfacture)){
	$writexml = false;
	var_dump('test');
     //Récupération des données client de la commande
	$sql = "SELECT *";
	$sql .= " FROM utilisateur u, pays p";
	$sql .= " WHERE userid = '".$facture['userid']."'";
	$sql .= " AND u.paysid = p.paysid";
	$requser = $DB_site2->query($sql);
	$user = $DB_site2->fetch_array($requser);

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
	var_dump($tabInfosGarantie);
	$numFacture = $facture['facturid'];

     //Récupération des informations sur la garantie qui a été transmise au fournisseur
	$sql = "SELECT *";
	$sql .= " FROM envoi_fournisseur";
	$sql .= " WHERE facturid = ".$facture['facturid'];
	$sql .= " AND type = 'garantie'";
	$nbGarInFact=$DB_site2->query($sql);

	if($DB_site2->num_rows($nbGarInFact)>0){
		$numFacture .= chr(64 + $DB_site2->num_rows($nbGarInFact));
	}

     //Récupération des garanties à transmettre, pour cette commande
	$sql = "SELECT *";
	$sql .= " FROM adv";
	$sql .= " WHERE facturid = ".$facture['facturid'];
	$sql .= " AND CAST(FROM_UNIXTIME(adv.date) as date) <= '".date("Y-m-d",$expdate)."'";
	$sql .= " AND adv.type = 'garantie'";
	$sql .= " AND adv.destinataire = 'SFG'";
	$advs = $DB_site2->query($sql); 
	$lignesFac="";
	var_dump($sql);
     //Boucle sur chaque garantie a transmettre 
	while($adv=$DB_site2->fetch_array($advs)){ 
          //Découpage des identifiants de produits commandés (table lignefacture)
		$lignesFacSp = explode("-", $adv['lignes']);

          //On vérifie que la commande n'a pas déjà été transmise à SFG
		$sql = "SELECT *";
		$sql .= " FROM envoi_fournisseur ef, envoi_fournisseur_ligne efl";
		$sql .= " WHERE ef.id = efl.efid";
		$sql .= " AND efl.ligne_facture_id = ".$lignesFacSp[1];
		$sql .= " AND ef.facturid = ".$facture['facturid'];
		$sql .= " AND ef.type = 'garantie'";
		$lignesFacInDb = $DB_site2->query($sql);
		var_dump($DB_site2->num_rows($lignesFacInDb));
          //Si la commande n'a pas été transmise => incrémentation des ID de produits commandés
		if($DB_site2->num_rows($lignesFacInDb)==0){
			$lignesFac .= $lignesFacSp[1].',';
		}
	}

	if (($lignesFac)!="") {

          //Suppression de la dernière virgule
		$lignesFac = substr($lignesFac, 0, -1);

          //Récupération du détails des garanties de la commande a transmettre à SFG
		$sql = "SELECT lfg.*, l.*, g.garantie_client_id";
		$sql .= " FROM lignefacture l";
		$sql .= " INNER JOIN lignefacturegarantie lfg ON (lfg.lignefactureid = l.lignefactureid)";
		$sql .= " INNER JOIN garantie g ON (g.garantieid = lfg.garantieid)";
		$sql .= " WHERE l.factureid = ".$facture['facturid'];
		$sql .= " AND l.lignefactureid IN (".$lignesFac.")";
		$sql .= " AND lfg.fournisseur = 'SFG'";
		$produits = $DB_site2->query($sql);
		var_dump($sql);
          //Si il y a des enregistrements trouvés, on écrit le XML
		if($produits!=false && $DB_site2->num_rows($produits)>0){
			$writexml=true;
		}
		var_dump('ici'.$writexml);
		$datefacture = date("d/m/Y",$facture['tsdate']);
		$dateenvoi = date("d/m/Y",$facture['tsenvoi']);

          //Ecriture du XML
		if($writexml){ 
			$writefile = true;

			$print.="<Contrat>"
			. "<LCOSTID>".SITE_CODE."</LCOSTID>"
			. "<SNUMCO>$numFacture</SNUMCO>"
			. "<DACHATC>".$datefacture."</DACHATC>"
			. "<SCIVILITE></SCIVILITE>"
			. "<SNOM>".xml_entities($user['nom'])."</SNOM>"
			. "<SPRENOM>".xml_entities($user['prenom'])."</SPRENOM>"
			. "<SADRESSE1>".xml_entities($user['adresse'])."</SADRESSE1>"
			. "<SADRESSE2></SADRESSE2>"
			. "<SCP>".$user['codepostal']."</SCP>"
			. "<SVILLE>".$user['ville']."</SVILLE>"
			. "<STELGSM></STELGSM>"
			. "<STELDOM>".$user['telephone']."</STELDOM>"
			. "<EMAIL>".$user['mail']."</EMAIL>"
			. "<SOBSERVATION></SOBSERVATION>\n";
			var_dump('ici3'.$print);
			$listmsg.="\n\nCommande ".$numFacture." du ".$datefacture."\n".$user['nom']." ".$user['prenom'];
			$total = 0;
			$totalpaht=0;
			$listefl = array();
			var_dump($DB_site2->num_rows($produits));
               //Boucle sur chaque garantie a transmettre
			while($produit = $DB_site2->fetch_array($produits)){
				var_dump('ok');
				var_dump($produit['artid']);
                    //Récupération de la marque du produit à garantir
				$sql = "SELECT *";
				$sql .= " FROM article_marque am, marque m";
				$sql .= " WHERE am.artid=".$produit['artid'];
				$sql .= " AND am.marqueid = m.marqueid";
				$marque = $DB_site2->query_first($sql);
				var_dump($sql);
                    //Récupération des informations sur le produit à garantir
				$sql = "SELECT *";
				$sql .= " FROM article";
				$sql .= " WHERE artid = ".$produit['artid'];
				$article = $DB_site2->query_first($sql);
				var_dump($sql);
                    //Boucle sur la quantite du produit (2 produits identiques commandés = 2 contrats de garantie)
				for($i=0; $i<$produit['qte']; $i++){
					var_dump('blop'.$produit['qte']);
                         //Si c'est une garantie classique (et non une extension de garantie)
					if ($produit['duree'] == $produit['dureeReelle']) {
						$dureeGarantie = $produit['dureeReelle'];
						$dureeExtGarantie = 0;

                              //Récupération des dates pour mise en place du contrat
						$sql = "SELECT date";
						$sql .= " FROM adv";
						$sql .= " WHERE lignes = '".$produit['qte']."-".$produit['lignefactureid']."'";
						$sql .= " AND type = 'garantie'";
						$sql .= " AND facturid = '".$facture['facturid']."'";
						$dateGarantie = $DB_site2->fetch_array($DB_site2->query($sql))[0];
						var_dump('ici2'.$dateGarantie);
						$dateEffetGarantie = date_create(date("Y-m-d",$dateGarantie));
						$dateEffetGarantie = date_format($dateEffetGarantie,"d/m/Y");
						$dateEffetGarantie ="04/09/2023";
						$listmsg .= "\n".$produit['artcode']." (".html_entity_decode($produit['libelle'],ENT_NOQUOTES,'ISO-8859-15').") garantie ".$dureeGarantie." ans à partir du ".$dateEffetGarantie;
                         //Sinon, si c'est une extension de garantie
					} else {
						$dureeExtGarantie = $produit['dureeReelle'];
						$dureeGarantie = 0;

                              //Récupération des dates pour mise en place du contrat
						$sql = "SELECT date";
						$sql .= " FROM adv";
						$sql .= " WHERE lignes='".$produit['qte']."-".$produit['lignefactureid']."'";
						$sql .= " AND type='garantie'";
						$sql .= " AND facturid='".$facture['facturid']."'";
						$dateGarantie = $DB_site2->fetch_array($DB_site2->query($sql))[0];
						var_dump('ici3'.$dateGarantie);
                              //On fait en sorte que la garantie commence bien à la bonne date (now + X années, durant Y années)
						$dateEffetGarantie = date_create(date("Y-m-d",$dateGarantie));


						date_add($dateEffetGarantie,date_interval_create_from_date_string($produit['duree'] - $produit['dureeReelle']." years"));
						date_add($dateEffetGarantie,date_interval_create_from_date_string("-1 day"));
						$dateEffetGarantie = date_format($dateEffetGarantie,"d/m/Y");

						$dateEffetGarantie ="04/09/2023";
                              //$dureeGarantie = $produit->duree - $produit->dureeReelle;
						$dureeExtGarantie = $produit['dureeReelle'];
						$listmsg .= "\n".$produit['artcode']." (".html_entity_decode($produit['libelle'],ENT_NOQUOTES,'ISO-8859-15').") extension de garantie ".$dureeExtGarantie." ans à partir du ".$dateEffetGarantie;
					}
					var_dump('teeeest');
					$print .= ""
					. "<PRODUIT>"
					. "<SMARQUE>".xml_entities($marque['libelle'])."</SMARQUE>"
					. "<SMODEL>".xml_entities($produit['artcode'])." / ".xml_entities($article['artcode_eurocave'])."</SMODEL>"
					. "<LIBELLE>".xml_entities($produit['libelle'])."</LIBELLE>"
					. "<NPRIX>".($produit['prix_revendeur'] > 0 ? $produit['prix_revendeur'] : $produit['prix'])."</NPRIX>"
					. "<SN></SN>"
					. "<DACHAT>$dateEffetGarantie</DACHAT>"
					. "<DEFFET>$dateEffetGarantie</DEFFET>"
					. "<TDUREE_GAR>".$dureeGarantie."</TDUREE_GAR>"
					. "<TDUREE_EXT>".$dureeExtGarantie."</TDUREE_EXT>"
					. "<SOBSERVATION></SOBSERVATION>";
					var_dump($print);
					$garType = '';
					if($produit['garantie_client_id'] == 2){
						switch($produit['dureeReelle']){
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

					$print .= ""
					. "<SGARTYPE>".$garType."</SGARTYPE>\n"
					. "</PRODUIT>\n";

					//Mise à jour de la table des envois à SFG (pour historique)
				$efl = new envoi_fournisseur_ligne($DB_site2);
				var_dump($efl);
                    //$efl->expediteur=$user_info['username'];
				$efl->type = "garantie";
				$efl->qte = $produit['qte'];
				$efl->paht = $produit['paht'];
				$efl->lignefactureid = $produit['lignefactureid'];
				$totalpaht+=$produit['paht'];
				$listefl[] = $efl;
				}
				
				$ef = new envoi_fournisseur($DB_site2);
                    //$ef->facturid=$facture['facturid'];
				$ef->facturid = $numFacture;
				$ef->destinataire = 'SFG';
				$ef->tarif = $totalpaht;
				$ef->type = 'garantie';
				$ef->create();
				$ef->create_lines($listefl);
			}

			$print.="</Contrat>\n";
		}
	}
}

$print.="</Contrats>";

//Si le dossier local des demandes SFG n'existe pas, on le crée
if(!is_dir($rootpath."admin/sfg")){
	mkdir($rootpath."admin/sfg");
}

//Nom du fichier
$filename_local = "mec_".date("Y_m_d").".xml";
$filename_distant = "xml/mec_".date("Y_m_d").".xml";

$ftp = new ftp();
$ftp->server = "ftp.sfg.fr";
$ftp->user = "mec";
$ftp->pass = SFG_FTP_PASSWORD;

//Si il y a eu génération de XML => envoi
if($writefile){
     //Création du fichier XML dans le dossier SFG local
	$f = fopen($rootpath."admin/sfg/".$filename_local,"w");
	fputs($f,$print);
	fclose($f);

     //Envoi du fichier sur le FTP de SFG, avec le même nom que le fichier local
	$result = $ftp->put($rootpath."admin/sfg/".$filename_local,$filename_local);

	if($result){
		$msg = "Envoi vers le ftp réussi";
	} else {
		$msg = "Erreur d'envoi : ".$ftp->error;
		var_dump($ftp->error);
		$error = true;


	}

	$startmsg .= $listmsg;
} else {
	$msg = "Aucun produit n'a de garantie";
}

print "<br>".$msg."\n";

if($error == true){
	$mail = new email();
	$mail->expediteur   = "contact@ma-cave-a-vin.fr";
	$mail->expname      = "Ma Cave à Vin";
	$mail->destinataire = "webmaster4@ma-cave-a-vin.fr";
	$mail->subject      = "ERREUR envoi de fichiers de garantie";
	$mail->body         = $startmsg."\n".$msg."\n".$msg2;
	$mail->sendmail();
}

if($writefile){
	$file_size   = filesize($rootpath."admin/sfg/".$filename_local);
	$file_ext    = substr($filename_local,strrpos($filename_local,'.')+1,strlen($filename_local));

	//Copie dans les pièces jointes de chaque commande
	foreach($aCmds as $facturid => $userid){
		$sql = "INSERT INTO user_files (";
		$sql .= "userid";
		$sql .= ", facturid";
		$sql .= ", parent";
		$sql .= ", `date`";
		$sql .= ", utilisateur";
		$sql .= ", name";
		$sql .= ", size";
		$sql .= ", ext";
		$sql .= ", state";
		$sql .= ") VALUES (";
		$sql .= $userid;
		$sql .= ", ".$facturid;
		$sql .= ", 0";
		$sql .= ", '".time()."'";
		$sql .= ", ''";
		$sql .= ", '".$filename_local."'";
		$sql .= ", '".$file_size."'";
		$sql .= ", '".$file_ext."'";
		$sql .= ", 1";
		$sql .= ")";
		$DB_site2->query($sql);

		//ID créé pour nommage du fichier
		$file_id = $DB_site2->insert_id();

		$file_source = $rootpath."admin/sfg/".$filename_local;
		$file_dest	 = $rootpath."admin/userfiles/".$file_id.'.'.$file_ext;

		copy($file_source, $file_dest);
	}
}

//Paramétrage de la réception des contrats sur le FTP de SFG
$files = $ftp->listdir('pdf');
var_dump($files);
$local = $rootpath."admin/sfg_pdf";

//Si le dossier local des fichiers PDF SFG n'existe pas, on le crée
if(!is_dir($local)){
	mkdir($local);
}

$i = 0;
$msg2 = "Aucun fichier PDF récupéré";
foreach($files as $f){
	$dossier_distant = "pdf/";
	$fichier_distant = str_replace($dossier_distant,'',$f);
var_dump($fichier_distant);
     //On vérifie l'existence de la commande récupérée sur le FTP de SFG
	$fid = str_replace(".pdf","",$fichier_distant);
	$sql = "SELECT *";
	$sql .= " FROM sfg";
	$sql .= " WHERE facturid = '".$fid."'";
	$requete = $DB_site2->query($sql);

     //Si le contrat n'a pas déjà été récupéré, on le récupère
	if($DB_site2->num_rows($requete)==0){
		$result = $ftp->get($f,$local."/".$fichier_distant);
		if($result != false){
			$sql = "INSERT INTO sfg SET";
			$sql .= " facturid='".$fid."'";
			$sql .= ", receive=".mktime();
			$DB_site2->query($sql);
		}
		else{
			$error = true;
			var_dump($ftp->error);
		}
		$i++;
	}
}

if($i>0){
	$msg2 = $i." pdf récupérés";
}

if($error == true){
	$mail = new email();
	$mail->expediteur   = "contact@ma-cave-a-vin.fr";
	$mail->expname      = "Ma Cave à Vin";
	$mail->destinataire = "webmaster4@ma-cave-a-vin.fr";
	$mail->subject      = "ERREUR envoi de fichiers de garantie";
	$mail->body         = $startmsg."\n".$msg."\n".$msg2;
	$mail->sendmail();
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
