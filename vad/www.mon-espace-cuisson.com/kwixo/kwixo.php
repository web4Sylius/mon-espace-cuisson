<?php
$mtkwixo = $totalmontant;

//Récupération des informations sur le client et la commande
$sql = "SELECT u.mail umail, u.civilite ucivilite, u.nom unom, u.prenom uprenom, u.codepostal ucodepostal, u.adresse uadresse, u.ville uville, u.telephone utelephone, u.raisonsociale uraisonsociale, p1.libelle upays, p1.paysid AS upaysid, u.portable uportable, f.facturid, f.prixport, f.tvaport, f.civilite, f.nom, f.prenom, f.codepostal, f.adresse, f.ville, f.telephone, f.raisonsociale, p2.libelle, p2.paysid AS paysid, f.mail, f.montantremise, f.montantcadeau, f.ip, f.timestamp";
$sql .= " FROM pays p1, pays p2, facture f";
$sql .= " INNER JOIN utilisateur u on (f.userid = u.userid)";
$sql .= " WHERE p1.paysid = u.paysid";
$sql .= " AND p2.paysid = f.paysid";
$sql .= " AND f.facturid = '".$factureid."'";
$infosxml = $DB_site->query_first($sql);

//Formatage de la civilité - FACTURATION
switch ($infosxml['ucivilite']){
     case 0:
               $infosxml['ucivilite'] = "monsieur" ;
               break;
     case 1:
               $infosxml['ucivilite'] = "madame" ;
               break;
     case 2:
               $infosxml['ucivilite'] = "mademoiselle" ;
               break;
}

//Formatage du pays - FACTURATION
if ($infosxml['upaysid'] == 57 || $infosxml['upaysid'] == 181){
     $infosxml['upays']="France";
}	
if ($infosxml['paysid'] == 57 || $infosxml['paysid'] == 181){
     $infosxml['libelle']="France";
}

//Formatage de la civilité - LIVRAISON
switch ($infosxml['civilite'])
{
case 0:
          $infosxml['civilite'] = "monsieur" ;
          break;
case 1:
          $infosxml['civilite'] = "madame" ;
          break;
case 2:
          $infosxml['civilite'] = "mademoiselle" ;
          break;
}

//Formatage du montant total
$mtkwixo = str_replace(" ", "", $mtkwixo) ;
$mtkwixo = str_replace(",", ".", $mtkwixo) ;

//Formatage des informations produits
$sql = "SELECT artcode, qte, libelle, artid";
$sql .= " FROM lignefacture";
$sql .= "  WHERE factureid = '".$factureid."'";
$lignesfactures = $DB_site->query($sql);
$nb = $DB_site->num_rows($lignesfactures);

$lstartid = array();
$xmlProduits = '';
while ($lignefacture = $DB_site->fetch_array($lignesfactures)){
     $lignefacture['libelle'] = str_replace("\"", " ", $lignefacture['libelle']) ;
     $lignefacture['libelle'] = str_replace("'", " ", $lignefacture['libelle']) ;
     $xmlProduits .= "<produit type='5' ref='".$lignefacture['artcode']."' nb='".$lignefacture['qte']."'>".$lignefacture['libelle']."</produit>" ;
     $lstartid[] = $lignefacture['artid'];
}

//$xml = str_replace("&", "et", $xml);

//Formatage de la date de commande et de la date de livraison
$datecom = date("Y-m-d H:i:s");
$datelivr = date("Y-m-d",datelivraison($factureid)) ;

//Intégration de la librairie pour décryptage de la clé de sécurité
require("fianet_key_64bits.php");
$Sample = new EncodingKey();
$key = $Sample->giveHashCode2($kwixo_hash, substr($datecom, -2, 2), $infosxml['mail'], $factureid, $mtkwixo, $infosxml['nom']);

//Mise en place du XML
$xml = "<?xml version='1.0' encoding='ISO-8859-1'?>"
     . "<control>"

          . "<utilisateur type='facturation' qualite='2'>"
               . "<nom titre='".$infosxml['ucivilite']."'>".$infosxml['unom']."</nom>"
               . "<prenom>".$infosxml['uprenom']."</prenom>"
               . "<societe>".$infosxml['uraisonsociale']."</societe>"
               . "<telhome>".$infosxml['utelephone']."</telhome>"
               . "<teloffice></teloffice>"
               . "<telmobile>".$infosxml['uportable']."</telmobile>"
               . "<telfax></telfax>"
               . "<email>".$infosxml['umail']."</email>"
          . "</utilisateur>"

          . "<adresse type='facturation' format='1'>"
               . "<rue1>".$infosxml['uadresse']."</rue1>"
               . "<rue2></rue2>"
               . "<cpostal>".$infosxml['ucodepostal']."</cpostal>"
               . "<ville>".$infosxml['uville']."</ville>"
               . "<pays>".$infosxml['upays']."</pays>"
               . "<appartement>"
                    . "<digicode1></digicode1>"
                    . "<digicode2></digicode2>"
                    . "<escalier></escalier>"
                    . "<etage></etage>"
                    . "<nporte></nporte>"
                    . "<batiment></batiment>"
               . "</appartement>"
          . "</adresse>"

          . "<utilisateur type='livraison' qualite='2'>"
               . "<nom titre='".$infosxml['civilite']."'>".$infosxml['nom']."</nom>"
               . "<prenom>".$infosxml['prenom']."</prenom>"
               . "<societe>".$infosxml['raisonsociale']."</societe>"
               . "<telhome>".$infosxml['telephone']."</telhome>"
               . "<teloffice></teloffice>"
               . "<telmobile></telmobile>"
               . "<telfax></telfax>"
               . "<email>".$infosxml['mail']."</email>"
          . "</utilisateur>"

          . "<adresse type='livraison' format='1'>"
               . "<rue1>".$infosxml['adresse']."</rue1>"
               . "<rue2></rue2>"
               . "<cpostal>".$infosxml['codepostal']."</cpostal>"
               . "<ville>".$infosxml['ville']."</ville>"
               . "<pays>".$infosxml['libelle']."</pays>"
               . "<appartement>"
                    . "<digicode1></digicode1>"
                    . "<digicode2></digicode2>"
                    . "<escalier></escalier>"
                    . "<etage></etage>"
                    . "<nporte></nporte>"
                    . "<batiment></batiment>"
               . "</appartement>"
          . "</adresse>"

          . "<infocommande>"
               . "<refid>".$factureid."</refid>"
               . "<siteid>".$kwixo_site_id."</siteid>"
               . "<montant devise='EUR'>".$mtkwixo."</montant>"
               . "<ip timestamp='".$infosxml['timestamp']."'>".$infosxml['ip']."</ip>"
               . "<transport>"
                    . "<type>4</type>"
                    . "<rapidite>2</rapidite>"
                    . "<nom>GLS</nom>"
               . "</transport>"
               . "<list nbproduit='".$nb."'>"
                    . $xmlProduits
               . "</list>"
          . "</infocommande>"

          . "<wallet version='1.0'>"
               . "<datelivr>".$datelivr."</datelivr>"
               . "<datecom>".$datecom."</datecom>"
               . "<crypt version='2.0'>".$key."</crypt>"
          . "</wallet>"
     . "</control>";

//Nettoyage du fichier
$xml = trim($xml);
$xml = str_replace("\\'","'",$xml);
$xml = str_replace("\\\"","\"",$xml);
$xml = str_replace("\\\\","\\",$xml);
$xml = str_replace("\t","",$xml);
$xml = str_replace("\n","",$xml);
$xml = str_replace("\r","",$xml);

if ($moyen == 8){
     //Création du formulaire
     if ($mtkwixo > 6000){
          $pagepaiementreceiveandpay =  "Ce moyen de paiement n'est pas disponible pour des commandes supérieures à 6000 euros." ;
     } else {
          $pagepaiementreceiveandpay =  ""
               . "<form name=\"formulaire\" method=\"post\" action=\"".$kwixo_url."\">"
                    . "<input type=\"hidden\" name=\"MerchID\" value=\"".$kwixo_site_id."\">"
                    . "<input type=\"hidden\" name=\"XMLInfo\" value=\"".$xml."\">"
                    . "<input type=\"hidden\" name=\"TypeIHM\" value=\"1\">"
                    . "<input type=\"hidden\" name=\"URLCall\" value=\"".$kwixo_url_call."\">"
                    . "<input type=\"hidden\" name=\"URLSys\" value=\"".$kwixo_url_sys."\">"
                    . "<input type=\"hidden\" name=\"XMLParam\" value=\"".$XMLParam."\">"
                    . "<p align=\"center\"><img src=\"".(defined('SITE_HOST') ? SITE_HOST : 'http://www.mon-espace-cuisson.com')."/configurations/images/templates/kwixo.png\"></p>"
                    . "<p>Vous allez être automatiquement redirigé vers Kwixo dans quelques secondes, si cela ne fonctionne pas, cliquez sur le bouton ci dessous<br></p>"
                    . "<p align=\"center\"><input type=\"submit\" value=\"Continuer\"></p>"
               . "</form>"
               . "<script language=\"javascript\">"
                    . "window.onload=function(){"
                         . "document.forms.formulaire.submit();"
                    . "}"
               . "</script>" ;
     }

} elseif ($moyen == 9) {
	
     $TypeIHM = 2 ;
     if ($mtkwixo > 4000) $TypeIHM = 1;
          
     $pagepaiementreceiveandpayplusieursfois =  ""
          . "<form name=\"formulaire\" method=\"post\" action=\"$kwixo_url\">"
               . "<input type=\"hidden\" name=\"MerchID\" value=\"$kwixo_site_id\">"
               . "<input type=\"hidden\" name=\"XMLInfo\" value=\"$xml\">"
               . "<input type=\"hidden\" name=\"TypeIHM\" value=\"".$TypeIHM."\">"  
               . "<input type=\"hidden\" name=\"URLCall\" value=\"".$kwixo_url_call."\">"
               . "<input type=\"hidden\" name=\"URLSys\" value=\"".$kwixo_url_sys."\">"
               . "<input type=\"hidden\" name=\"XMLParam\" value=\"".$XMLParam."\">"
               . "<p align=\"center\"><img src=\"".(defined('SITE_HOST') ? SITE_HOST : 'http://www.mon-espace-cuisson.com')."/configurations/images/templates/kwixo.png\"></p>"
               . "<p>Vous allez être automatiquement redirigé vers Kwixo dans quelques secondes, si cela ne fonctionne pas, cliquez sur le bouton ci dessous</p>"
               . "<p align=\"center\"><input type=\"submit\" value=\"Continuer\"></p>"
          . "</form>"
          . "<script language=\"javascript\">"
               . "window.onload=function(){"
                    . "document.forms.formulaire.submit();"
               . "}"
          . "</script>" ;
}

