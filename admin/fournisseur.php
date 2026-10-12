<?php

require "admin_global.php";

include "./header.php";
include "./menu.php";
require_once(SITE_PATH."/class/DAOgarantie.class.php");
require_once(SITE_PATH."/class/garantie.class.php");
require_once(SITE_PATH."/class/DAOfournisseur.class.php");
require_once(SITE_PATH."/class/DAOlignefacturegarantie.class.php");
require_once(SITE_PATH."/class/frais_port.php");
require_once(SITE_PATH."/class/facture.class.php");
require_once(SITE_PATH."/class/fournisseur.class.php");
include "./marge.php";

include "../class/stock.class.php";

if(isset($_GET['action2']) && $_GET['action2'] == 'supprimer'){

     if(!isset($_GET['facturid']) || empty($_GET['facturid'])){

          echo 'Numéro de facture manquant ou invalide';

     } else {
          
          $sql = "DELETE FROM facture_fournisseur_autre";
          $sql .= " WHERE facture = '".$_GET['facturid']."'";
          $DB_site2->query($sql);
          
          $sql = "UPDATE envoi_fournisseur_ligne SET";
          $sql .= " facture = ''";
          $sql .= " WHERE facture='".$_GET['facturid']."'";
          $DB_site2->query($sql);
          
          $sql = "UPDATE mp_envoi_fournisseur_ligne SET";
          $sql .= " facture = ''";
          $sql .= " WHERE facture='".$_GET['facturid']."'";
          $DB_site2->query($sql);
          
          $sql = "DELETE FROM avoir_ligne";
          $sql .= " WHERE ffid = '".$_GET['facturid']."'";
          $DB_site2->query($sql);
          
          $sql = "DELETE FROM facture_fournisseur";
          $sql .= " WHERE numero = '".$_GET['facturid']."'";
          $DB_site2->query($sql);
          
          $msg_action = "La facture n°".$_GET['facturid']." a été supprimée";

     }

}

$sql = "SELECT f.societe, fc.devise";
$sql .= " FROM fournisseur f";
$sql .= " INNER JOIN fournisseur_condition fc USING(societe)";
$sql .= " WHERE f.type = 'facture'";
$sql .= " AND fc.activite = '1'";
$sql .= " GROUP BY f.societe";
$requete=$DB_site2->query($sql);
while($f=$DB_site2->fetch_array($requete)){
    $fournisseurs[]=$f['societe'];
    if($f['devise']=="")
		$f['devise']=$devises->defaut;
    $jsdevise.="devise_fournisseur['".addslashes($f['societe'])."']='".$f['devise']."';\n";
}

$opt="<option></option>";
foreach($fournisseurs as $f) $opt.="<option value=\"$f\"".($_POST['fournisseur']==$f?" selected":"").">$f</option>";
$optdate="";
$typesdate=array("date_facture"=>"Date de facture","echeance"=>"Date d'échéance","date_saisie"=>"Date de saisie");
foreach($typesdate as $typedate=>$datetxt)
	$optdate.="<option value=\"$typedate\"".($_POST['typedate']==$typedate?" selected":"").">$datetxt</option>";

if($_POST['du']==""){
	$date_du=date("d/m/Y", mktime(date("H"), date("i"), date("s"), date("n"), date("j"), date("Y")-2));
} else {
	$date_du=$_POST['du'];
}
if($_POST['au']==""){
	$date_au=date("d/m/Y", mktime(date("H"), date("i"), date("s"), date("n"), date("j")+1));
} else {
	$date_au=$_POST['au'];
}

$recherche="<form action=\"fournisseur.php?action=rechercher\" method=\"post\">
<div style=\"border:solid 1px #CCC;padding:2px;\">Rechercher par n° de facture : <input type=\"text\" name=\"src\"> <input type=\"submit\"></div>
<div style=\"margin-top:10px;border:solid 1px #CCC;padding:2px;\">
    Rechercher par fournisseur : <select name=\"fournisseur\">$opt</select>
    <select name=\"typedate\">$optdate</select>
    Entre le <input type=\"text\" id=\"du\" name=\"du\" value=\"".$date_du."\" onclick=\"calendrier_show(this.id,this.value)\" size=\"8\" readonly>
	et le <input type=\"text\" id=\"au\" name=\"au\" value=\"".$date_au."\" onclick=\"calendrier_show(this.id,this.value)\" size=\"8\" readonly> inclus
    <input type=\"submit\">
</div>
<p><a href=\"fournisseur.php?action=ajouter\">Ajouter une facture ou un avoir</a></p>
</form>";

print "<LINK href=\"cal.css\" type=\"text/css\" rel=\"stylesheet\">
<script language=\"javascript\" src=\"calendrier.js\"></script>
<script language=\"javascript\" src=\"../javascripts/host.js\"></script>
<script language=\"javascript\" src=\"fournisseur.js\"></script>
<script language=\"javascript\">
initcalendrier();
var devise_fournisseur=new Array();
$jsdevise
function change_devise(fournisseur) {
    var devise=devise_fournisseur[fournisseur];
    document.getElementById('montant_ht_devise').innerHTML=devise;
    document.getElementById('montant_ttc_devise').innerHTML=devise;
    document.getElementsByName('devise')[0].value=devise;
}

var site_code=\"base\";
function selectcdc(id){
    for(i=1;i<=6;i++) document.getElementById('cdc'+i).style.borderColor='#888888';
    document.getElementById('cdc'+id).style.borderColor='red';
    document.getElementsByName('cdcid')[0].value=id;
    document.getElementById('modif').style.display='block';
}

function srcartid(src){
    if(src.length<=3) return;
    var url=\"marketplace/src.php?txt=\"+src+\"&active=1&site_code=\"+site_code;
    ajaxrequest(url,srcCallback);
    document.getElementById('resultats').style.display='block';
	document.getElementById('resultats').innerHTML='<img src=\"/configurations/".$host."/images/templates/ajax_wait.gif\">';
}

function srcCallback(httprequest){
    var xml=httprequest.responseXML.documentElement;
    var html='';	   
	var vistxt=new Array();
	vistxt[0]='<br><span style=\"color:red;\">Invisible</span>';
	vistxt[1]='<br>Visible';
    if(xml.getElementsByTagName('prod')[0]){
        html+='<table width=\"100%\" style=\"border:solid thin #808080;\" class=\"txt\"><tr>';
        for(i=0;i<xml.getElementsByTagName('prod').length;i++){
            var description=xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
            description = addslashes(description);
            var id=xml.getElementsByTagName('prod')[i].getAttribute('id');
            var artid=xml.getElementsByTagName('prod')[i].getAttribute('artid');
            var artcode=xml.getElementsByTagName('prod')[i].getAttribute('id');
            var paht_base=xml.getElementsByTagName('prod')[i].getAttribute('paht_base');
            var ecopartht_base=xml.getElementsByTagName('prod')[i].getAttribute('ecopartht_base');
            var prixht = parseFloat(paht_base);
	     	var actif=xml.getElementsByTagName('prod')[i].getAttribute('active_'+site_code);
			var stock=xml.getElementsByTagName('prod')[i].getAttribute('stock');
			var marque=xml.getElementsByTagName('prod')[i].getAttribute('marque');
			var ref=xml.getElementsByTagName('prod')[i].getAttribute('ref');
			var lot=xml.getElementsByTagName('prod')[i].getAttribute('lot');
             if(actif<2) html+='<tr id=\"tr_'+artid+'\"><td><img src=\"/configurations/".$host."/images/produits/br/'+artid+'.jpg\" width=\"80\"></td><td style=\"border-bottom:solid thin #cccccc;\">'+marque+'<br>'+id+'<br>'+ref+'<br>'+vistxt[actif]+'</td><td style=\"border-bottom:solid thin #cccccc;\">'+description+'<td style=\"border-bottom:solid thin #cccccc;\"><a href=\"javascript:selectprod('+prixht+',\''+artcode+'\',\''+description+'\','+ecopartht_base+')\">Sélectionner</a></td></tr>';
        }
        html+='</table>';
    }else
        html='Pas de résultat';

    document.getElementById('resultats').innerHTML=html;
  }

function selectprod(prixht,artcode,description, ecopartht){
    jQuery('#article').val(artcode);
   	if(jQuery('#type').val() == 'fourn'){
		jQuery('#prix').val(Math.round(prixht*100)/100);
		jQuery('#description').val(description);
		jQuery('#afecopart').val(Math.round(ecopartht*100)/100);
		jQuery('#ecoparthtSpan').html(Math.round(ecopartht*100)/100);
   	}else{
   		jQuery('#description').val('Garantie pour : '+description);
   		jQuery('#afecopart').val(0);
   	}
   	jQuery('#resultats').hide();
   	jQuery('#resultats').html('');
}\n";
  ?>
function addslashes(ch) { 
	ch = ch.replace(/\\/g,"\\\\"); 
	ch = ch.replace(/\'/g,"\\'"); 
	ch = ch.replace(/\"/g,"\\\""); 
	return ch ;
}
<?php 
print "
</script>
<style>
.detail { border:solid thin black; border-collapse:collapse; }
.detail thead { text-align:center; font-weight:bold; }
.detail td { border:solid thin #888888; }
.del { color:red; font-weight:bold; text-decoration:none; }
.tab_on { height:30px; font-weight:bold;text-align:center;border-style:solid;border-width:thin; border-color:black black white black;cursor:pointer; }
.tab_off { height:30px; text-align:center;border:solid thin #aaaaaa; border-color:#aaa #aaa black #aaa;cursor:pointer; }
.selstock { background-color:#AACCFF; }
#cmdstock { float:left; width:50%; margin:10px; border:solid thin #aaaaaa; }
#cmdstock span { display:block; margin:5px 2px; padding:2px; cursor:pointer; }
#cmdstock span:hover { background:#efefef; }
#cmdstock > img { display:block; margin:auto; }
#stockaddline { float:left; width:40%; display:none; margin:10px; border:solid thin #aaaaaa; padding:5px; }
#associnfos { display:none; margin:10px 0 0 0; border:solid 1px #aaa; padding:5px; }
#associnfos p { margin:5px 0 2px 0; }
#associnfos p img { vertical-align:middle; }
#afavoir, #stavoir { display:none; }
</style>";

asort($fournisseurs);
$moyens=array("Virement","Prélévement","LCR","Chèque");
$error="";
if(isset($action)){
    if($action=="ajouter2"){
		if($_POST['factureid']==""){
			$error="Il faut donner un n° de facture";
		}
		if($_POST['date_facture']=="" || strpos($_POST['date_facture'],"/")==false){
			$error="La date de facture n'est pas valide";
		}
		if($_POST['echeance']=="" || strpos($_POST['echeance'],"/")==false){
			$error="La date d'échéance n'est pas valide";
		}
		if($_POST['moyen']==""){
			$error="Aucun moyen de paiement n'est spécifié";
		}
		if($_POST['fournisseur']==""){
			$error="Il faut sélectionner un fournisseur";
		}
		$result=$DB_site2->query_first("SELECT count(*) FROM facture_fournisseur WHERE numero='".$_POST['factureid']."'");
		if($result[0]!=0){
			$error="Une facture avec ce numéro existe déjà";
		}
		if($_POST['type_document']=="avoir"){
			$_POST['montant_ttc']=-$_POST['montant_ttc'];
			$_POST['montant_ht']=-$_POST['montant_ht'];
		}
		if($error==""){
			$sp=explode("/",$_POST['date_facture']);
			$date_facture=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
			$sp=explode("/",$_POST['echeance']);
			$echeance=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
			$result=$DB_site2->query("INSERT INTO facture_fournisseur SET 
										numero='".mysqli_real_escape_string($DB_site2->link_id,$_POST['factureid'])."',
										date_facture=".$date_facture.",
										montant_ht=".floatval($_POST['montant_ht']).",
										montant_ttc=".floatval($_POST['montant_ttc']).",
										tva=".floatval($_POST['tva']).",
										devise='".$_POST['devise']."',
										echeance=".$echeance.",
										moyen='".$_POST['moyen']."',
										fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur'])."',
										date_saisie=".time().",
										utilisateur='".$user_info['username']."' ");
		
			if($_POST['tva'] == 0){
				$aPaysEuro = array(64,10,17,26,42,45,153,53,56,66,75,81,83,98,99,105,118,132,133,44,136,148,149,157,186);
				$fourn=$DB_site2->query_first("SELECT tvaintra,p.libelle FROM fournisseur f INNER JOIN fournisseur_condition fc ON f.societe = fc.societe INNER JOIN pays p ON p.paysid = f.paysid WHERE f.societe = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur'])."' AND f.paysid IN (".implode(',',$aPaysEuro).")");
				$mail=new email();
				$mail->destinataire = defined('MAIL_DIRECTION') ? MAIL_DIRECTION : 'pr@mon-espace-cuisson.com';
    			$mail->to = array("webmaster4@mon-espace-cuisson.com",defined('MAIL_COMMERCE2') ? MAIL_COMMERCE2 : 'cecile@mon-espace-cuisson.com');
				$mail->expediteur=defined('MAIL_COMMERCE') ? MAIL_COMMERCE : 'contact@mon-espace-cuisson.com';
				$mail->expname="Ma Cave à Vin";
				$mail->subject="DEB Fournisseur à faire";
				$mail->body="<table><tr><td>Nom du fournisseur:</td><td>".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur'])."</td></tr>
				<tr><td>Facture n°:</td><td>".mysqli_real_escape_string($DB_site2->link_id,$_POST['factureid'])."</td></tr>
				<tr><td>TVA intracommunautaire:</td><td>".$fourn['tvaintra']."</td></tr>
				<tr><td>Montant HT:</td><td>".floatval($_POST['montant_ht'])."</td></tr>
				<tr><td>Montant TTC:</td><td>".floatval($_POST['montant_ttc'])."</td></tr>
				<tr><td>Pays facturation:</td><td>".$fourn['libelle']."</td></tr></table>";
				$mail->sendmail();
			}
			$_GET['facturid']=$_POST['factureid'];
			$action="editer";
		}else{
			$action="ajouter";
		}
    }

    if($action=="createline"){
		$lid=$_POST['fline'];
		$tbl="envoi_fournisseur_ligne";
		if($_POST['sp_'.$lid]=="mp"){
			$tbl="mp_envoi_fournisseur_ligne";
		}
		$sql="INSERT INTO $tbl SET date=".time().", expediteur='".$user_info['username']."', type='".$_POST['type']."', ligne_facture_id=".$lid.", paht=".floatval($_POST['price_'.$lid]).", ecopart=".floatval($_POST['ecopart_'.$lid]).", qte=".intval($_POST['qte_'.$lid]).", facture='".$_POST['facturid']."'";
		$DB_site2->query($sql);
		$action="editer";
		$facturid=$_POST['facturid'];
    }

    if($action=="autrefrais"){
		if(isset($_POST['type']) && (isset($_POST['sitefacture']) || isset($_POST['bft']))){
			$ff=$DB_site2->query_first("SELECT * FROM facture_fournisseur WHERE numero='".$facturid."'");
			$sitefacture=0;
			if(isset($_POST['sitefacture'])){
				$sitefacture=$_POST['sitefacture'];
				if($_POST['type']=="gar" && intval($_POST['sitefacture'])!=0){
					$totalfacture=0;
					$garanties=$DB_site2->query("SELECT ffa.prix,ffa.description,ff.numero,ff.date_facture,ff.fournisseur FROM facture_fournisseur_autre ffa INNER JOIN facture_fournisseur ff ON (ff.numero=ffa.facture) WHERE ffa.facturid=".$_POST['sitefacture']." AND ffa.type='gar'");
					while($g=$DB_site2->fetch_array($garanties)){
						$totalfacture+=$g[prix];
						$listefacture.="<br><a href=\"https://".$host."/admin/fournisseur.php?action=editer&facturid=".$g['numero']."\">Facture ".$g['numero']." ".$g['fournisseur']."</a> du ".date("d/m/Y",$g['date_facture'])." : ".$g['description']." ".number_format($g['prix'],2,',','')." &euro;";
					}
				}
				if(intval($_POST['sitefacture'])!=0){
					$test=$DB_site2->query_first("SELECT facturid FROM facture WHERE valide IN(1,5,7,8) AND facturid=".intval($_POST['sitefacture']));
					$test2=$DB_site2->query_first("SELECT id FROM stock_cmd WHERE id=".intval($_POST['sitefacture']));
					if($test[0].$test2[0]=="" || $_POST['sitefacture']==""){
						  $mail=new email();
						  $mail->destinataire="webmaster@mon-espace-cuisson.com;webmaster3@mon-espace-cuisson.com;pr@mon-espace-cuisson.com;cecile@mon-espace-cuisson.com";
						  $mail->expediteur="cecile@mon-espace-cuisson.com";
						  $mail->expname="Ma Cave à Vin";
						  $mail->subject="Facture fournisseur non associée";
						  $mail->body="La ligne <i>".$_POST['description']."</i> de la facture <a href=\"https://".$host."/admin/fournisseur.php?action=editer&facturid=".$ff['numero']."\">".$ff['fournisseur']." n°".$ff['numero']."</a> du ".date("d/m/Y",$ff['date_facture'])." n'a pas été associée à une commande client existante";
						  $mail->sendmail();
					}
				}
			}
			if(!isset($_POST['qte_art']))
				$qte_art = 1;
			elseif(isset($_POST['qte_art']) && $_POST['qte_art'] == '')
				$qte_art = 1;
			else
				$qte_art = $_POST['qte_art'];

			$prix = floatval($_POST['prix']) * $qte_art;
			$ecopart = floatval($_POST['afecopart']) * $qte_art;
			
			if($ecopart>0) $DB_site2->query("INSERT INTO facture_fournisseur_autre SET facture='$facturid', facturid='".$_POST['sitefacture']."', type='eco', prix='".$ecopart."',description='Ecopart pour: ".mysqli_real_escape_string($DB_site2->link_id,$_POST['description'])."', qte='".$qte_art."' , artcode='".$_POST['article']."'");
			
			$DB_site2->query("INSERT INTO facture_fournisseur_autre SET facture='$facturid', facturid='".$sitefacture."', type='".$_POST['type']."', prix='".$prix."',description='".mysqli_real_escape_string($DB_site2->link_id,$_POST['description'])."', qte='".$qte_art."' , artcode='".$_POST['article']."'");
			$ffaid=$DB_site2->insert_id();

			// 
			$margetotal=new marge($DB_site2,$sitefacture);
			$margett = $margetotal->margetotal($DB_site2,$sitefacture);
			if($margett['totalfactureht'] != 0.00 && $margett['totalachat'] != 0.00) {
				if($margett['parentid'] != '') {
            		$DB_site2->query("UPDATE facture SET marge ='".$margett["margett_pc"]."' WHERE facturid ='".$margett['parentid']."'");
        		}
				$DB_site2->query("UPDATE facture SET marge ='".$margett["margett_pc"]."' WHERE facturid ='".$sitefacture."'");
			}
			$facture_user = new facture($DB_site2,$sitefacture);
			if($margett["margett_pc"] < 20 && $margett["margett_pc"] != "") {
				$mail=new email();
				$mail->destinataire="pr@mon-espace-cuisson.com";
				$mail->to = array("webmaster4@mon-espace-cuisson.com");
				$mail->expediteur="contact@mon-espace-cuisson.com";
				$mail->expname="Ma Cave à Vin";
				$mail->subject="Analyse rentabilité";
				$mail->body="Une analyse de rentabilité de commande est tombée sous les 20% :
				<br>Commande N° : <a href='https://www.ma-cave-a-vin.fr/admin/facture.php?action=editer&facturid=".$sitefacture."#detail_marge'>".$sitefacture."</a>
				<br>Nom : " .$facture_user->prenom . " " . $facture_user->nom;
				$mail->sendmail();

				$sql = "INSERT INTO suivi_commande SET";
				$sql .= " facturid    = ".$sitefacture;
				$sql .= ", date       = ".time();
				$sql .= ", commercial = '".$user_info['username']."'";
				$sql .= ", suivi      = 'l\'analyse de marge a trouvé une marge de ".$margett["margett_pc"]."%'";
				$DB_site2->query($sql);


			}
			if(isset($_POST['createavoir'])){
			    $prixavoir=floatval($_POST['prix']-$_POST['prixavoir']) * $qte_art;
			    $DB_site2->query("INSERT INTO avoir_ligne SET ffid='".mysqli_real_escape_string($DB_site2->link_id,$facturid)."', facturid='".$_POST['sitefacture']."', eflid=".$ffaid.", destinataire='".mysqli_real_escape_string($DB_site2->link_id,$ff['fournisseur'])."', article='".mysqli_real_escape_string($DB_site2->link_id,$_POST['description']." ".$_POST['sitefacture'])."', paht=".$prixavoir.", ecopart=".floatval($_POST['afecopart']).", qte=1, description='".mysqli_real_escape_string($DB_site2->link_id,$_POST['avoirtext'])."'");
			    $margetotal=new marge($DB_site2,$sitefacture);
				$margett = $margetotal->margetotal($DB_site2,$sitefacture);
				if($margett['totalfactureht'] != 0.00 && $margett['totalachat'] != 0.00) {
					if($margett['parentid'] != '') {
            			$DB_site2->query("UPDATE facture SET marge ='".$margett["margett_pc"]."' WHERE facturid ='".$margett['parentid']."'");
        			}
					$DB_site2->query("UPDATE facture SET marge ='".$margett["margett_pc"]."' WHERE facturid ='".$sitefacture."'");
				}
				$facture_user = new facture($DB_site2,$sitefacture);
				if($margett["margett_pc"] < 20 && $margett["margett_pc"] != "") {
					$mail=new email();
					$mail->destinataire="pr@mon-espace-cuisson.com";
					$mail->to = array("webmaster4@mon-espace-cuisson.com");
					$mail->expediteur="contact@mon-espace-cuisson.com";
					$mail->expname="Ma Cave à Vin";
					$mail->subject="Analyse rentabilité";
					$mail->body="Une analyse de rentabilité de commande est tombée sous les 20% :
					<br>Commande N° : <a href='https://www.ma-cave-a-vin.fr/admin/facture.php?action=editer&facturid=".$sitefacture."#detail_marge'>".$sitefacture."</a>
					<br>Nom : " .$facture_user->prenom . " " . $facture_user->nom;
					$mail->sendmail();

					$sql = "INSERT INTO suivi_commande SET";
					$sql .= " facturid    = ".$sitefacture;
					$sql .= ", date       = ".time();
					$sql .= ", commercial = '".$user_info['username']."'";
					$sql .= ", suivi      = 'l\'analyse de marge a trouvé une marge de ".$margett["margett_pc"]."%'";
					$DB_site2->query($sql);
				}
			}
		}
		$action="editer";
		$jsscroll="scrollbottom=true;";
    }

    if($action=="addavoir" && $_POST['avoirid']!=""){
    	if(floatval($_POST['prix'])<0) //on transforme le prix de l'avoir en négatif.
    		$prix = floatval($_POST['prix']);
    	else
    		$prix = -1 * floatval($_POST['prix']);

		$DB_site2->query("UPDATE avoir_ligne SET date_recu=".time()." WHERE id=".$_POST['avoirid']);
		$DB_site2->query("INSERT INTO facture_fournisseur_autre SET facture='".$_GET['facturid']."', facturid='".$_POST['sitefacture']."', type='".$_POST['type']."', prix='".$prix."',description='".mysqli_real_escape_string($DB_site2->link_id,$_POST['description'])."', assoc='avoir".$_POST['avoirid']."'");
		$action="editer";
		$jsscroll="scrollbottom=true;";
    }
}

echo "<h1>Factures fournisseur</h1>";

if(isset($msg_action) && !empty($msg_action)) echo "<p style=\"color: red;\">".$msg_action."</p>";

if(isset($action)){
    if($action=="ajouter"){
		foreach($fournisseurs as $f){
			$opts.="<option value=\"$f\">$f</option>";
		}
		foreach($moyens as $m){
			$optsm.="<option value=\"$m\">$m</option>";
		}
		print "<form name=\"ajoutfacture\" action=\"fournisseur.php?action=ajouter2\" method=\"post\">
				<input type=\"hidden\" name=\"devise\" value=\"".$devises->defaut."\">
				<table>
					<tr>
						<td colspan=\"2\" style=\"color:red;\">$error</td>
					</tr>
					<tr class=\"td_alt1\">
						<td align=\"right\">Type de document :</td>
						<td align=\"center\">
							<input type=\"radio\" name=\"type_document\" value=\"facture\" checked>Facture &nbsp;
							<input type=\"radio\" name=\"type_document\" value=\"avoir\">Avoir
						</td>
					</tr>
					<tr class=\"td_alt0\">
						<td align=\"right\">Fournisseur :</td>
						<td align=\"center\">
							<select name=\"fournisseur\" onchange=\"change_devise(this.value)\">
								<option value=\"\">Choisissez</option>
								$opts
							</select></td>
					</tr>
					<tr class=\"td_alt1\">
						<td align=\"right\">Date facture :</td>
						<td align=\"center\"><input type=\"text\" name=\"date_facture\" id=\"date_facture\" size=\"6\" onclick=\"calendrier_show('date_facture')\" readonly></td>
					</tr>
					<tr class=\"td_alt0\">
						<td align=\"right\">N° facture :</td>
						<td align=\"center\"><input type=\"text\" name=\"factureid\" size=\"8\"></td>
					</tr>
					<tr class=\"td_alt1\">
						<td align=\"right\">Montant H.T. :</td>
						<td align=\"center\">
							<span class=\"signe_montant\">+</span> &nbsp;
							<input type=\"text\" name=\"montant_ht\" size=\"5\">
							<span id=\"montant_ht_devise\"></span>
						</td>
					</tr>
					<tr class=\"td_alt0\">
						<td align=\"right\">Taux T.V.A :</td>
						<td align=\"center\"><input type=\"text\" name=\"tva\" size=\"5\" value=\"20\">%</td>
					</tr>
					<tr class=\"td_alt1\">
						<td align=\"right\">Montant T.T.C :</td>
						<td align=\"center\">
							<span class=\"signe_montant\">+</span> &nbsp;
							<input type=\"text\" name=\"montant_ttc\" size=\"5\">
							<span id=\"montant_ttc_devise\"></span>
						</td>
					</tr>
					<tr class=\"td_alt0\">
						<td align=\"right\">Date échéance :</td>
						<td align=\"center\"><input type=\"text\" name=\"echeance\" id=\"echeance\" onclick=\"calendrier_show('echeance')\" size=\"6\" readonly></td>
					</tr>
					<tr class=\"td_alt1\">
						<td align=\"right\">Mode de paiement :</td>
						<td align=\"center\"><select name=\"moyen\">$optsm</select></td>
					</tr>
					<tr class=\"td_alt0\">
						<td align=\"center\" colspan=\"2\"><input type=\"button\" value=\"Ajouter\" onclick=\"addfacture()\"></td>
					</tr>
				</table>
			</form>
			<script>
			( function($) {
				$(document).ready(function(){
					$('input[type=radio]').change( function() {
						console.log('Radio Box has been changed!');
						var type_document=$(this).val();
						if(type_document==\"facture\"){
							$('.signe_montant').text('+');
						} else {
							$('.signe_montant').text('-');
						}
						console.log('val : '+type_document);
					});
				});
			} ) ( jQuery );
			</script>";
    }

    if((isset($action) && $action == 'editer')){
		$sql = "SELECT *";
		$sql .= " FROM facture_fournisseur";
		$sql .= " WHERE numero = '".$_GET['facturid']."'";
		$facture = $DB_site2->query_first($sql);
		?>

          <script language="javascript">
               var factureid = '<?php echo $facture['numero']; ?>';
               
               function afficheArticle(value){
					if(value == 'gar'){
						jQuery('#resultats').hide();
						jQuery('#resultats').html('');
						jQuery('#prix').val('');
						jQuery('#textEcoPart').html('');
						jQuery('#article').val('');
						jQuery('#description').val('');
						jQuery('#afsitefacture').prop('required',true);
						jQuery('#articleSpan').show();


					}else if(value == 'fourn'){
						jQuery('#resultats').hide();
						jQuery('#resultats').html('');
						jQuery('#description').val('');
						jQuery('#prix').val('');
						jQuery('#afsitefacture').prop('required',true);
						jQuery('#qte_art').prop('required',true);
						jQuery('#articleSpan').show();
						jQuery('#textEcoPart').html('(+ <span id="ecoparthtSpan"></span>'+'<?php echo $devises->symbole($facture['devise']); ?>'+' d\'éco-part)');
					}else{
						jQuery('#resultats').hide();
						jQuery('#resultats').html('');
						jQuery('#articleSpan').hide();
						jQuery('#textEcoPart').html('');
						jQuery('#article').val('');
						jQuery('#qte_art').val('');
						jQuery('#description').val('');
						jQuery('#prix').val('');
						jQuery('#afecopart').val('');
						jQuery('#afsitefacture').prop('required',false);
						jQuery('#qte_art').prop('required',false);
					}
				}
				
                <?=$jsscroll; ?>
          </script>
          <div style="border:solid thin black; padding: 5px;">
               Facture n°<?php echo $facture['numero']; ?> de <?php echo $facture['fournisseur']; ?>
               <br>
               Du <?php echo date("d/m/Y",$facture['date_facture']); ?>
               <br>
               Ajoutée par <?php echo $facture['utilisateur']; ?> le <?php echo date("d/m/Y",$facture['date_saisie']); ?>
               <br>
               Total H.T : <?php echo $facture['montant_ht']." ".$devises->symbole($facture['devise']); ?> H.T soit <?php echo $facture['montant_ttc']." ".$devises->symbole($facture['devise']); ?> T.T.C (T.V.A <?php echo $facture['tva']; ?>%)
               <br>
               Date échéance : <?php echo date("d/m/Y",$facture['echeance']); ?></div>
               <p><b>Articles associés</b></p>
               <div id="assoc">
                    <img src="/configurations/<?php echo $host; ?>/images/templates/ajax_wait.gif">
               </div>
               <?php

               $sql = "SELECT *";
               $sql .= " FROM avoir_ligne";
               $sql .= " WHERE ";
               $sql .= " ffid = '".$facturid."'";
               $avoirs = $DB_site2->query($sql);

               if($DB_site2->num_rows($avoirs) > 0){
					?>
                    <p><b>Avoirs en attente</b></p>
                    <table class="detail">
                    <thead>
                        <tr>
                            <td>Date d'envoi</td>
                            <td>Commande</td>
                            <td>Article</td>
                            <td>Montant</td>
                            <td>Relances</td>
                            <td>Reçu</td>
                        </tr>
                    </thead>
                    <?php

                    while($avoir = $DB_site2->fetch_array($avoirs)){
                    ?>
                        <tr>
                            <td><?php echo $avoir['date']>0?date("d/m/Y",$avoir['date']):"à envoyer"; ?></td>
                            <td><?php echo $avoir['facturid']; ?></td>
                            <td><?php echo $avoir['qte']." ".$avoir['article']; ?></td>
                            <td align="right"><?php echo number_format($avoir['qte'] * $avoir['paht'],2,',',''); ?>&nbsp;<?php echo $devises->symbole($avoir['devise']); ?></td>
                            <td><?=$avoir['relancenb']; ?></td>
                            <td><?=$avoir['date_recu']>0?date("d/m/Y",$avoir['date_recu']):""; ?></td>
                        </tr>
                    <?php
                    }
                    ?>
                    </table>
                    <?php
               }
               
               $req=$DB_site2->query("SELECT info,type FROM code_comptable GROUP BY info ORDER BY info");
               while($cc=$DB_site2->fetch_array($req))
					$opttype.="<option value=\"".$cc['type']."\">".ucfirst($cc['info'])."</option>";
               
               ?>
               <table id="tabs" style="width:100%; margin-top:10px; border-collapse:collapse;">
                    <tr>
                         <td id="action_1" class="tab_on" onclick="action(1);">Associer à une commande</td>
                         <td id="action_4" class="tab_off" onclick="action(4);updatestockcmd();">Commande de stock</td>
                         <td id="action_2" class="tab_off" onclick="action(2);">Ajouter des autres frais</td>
                         <td id="action_3" class="tab_off" onclick="action(3);avoirlist();">Ajouter un avoir</td>
                    </tr>
                    <tr id="tra_1">
                         <td colspan="4" style="border:solid thin black;">
                              <br>
                              <div>
                                   <input type="text" id="fid" onkeyup="if(this.value.length>=5)searchprod(this.value,'<?php echo addslashes($facture['fournisseur']); ?>');"> 
                                   <input type="button" value="Rechercher" onclick="searchprod(document.getElementById('fid').value,'<?php echo addslashes($facture['fournisseur']); ?>')">
                              </div>
                              <div id="results"></div>
                         </td>
                    </tr>
                    <tr id="tra_2" style="display:none;">
                         <td colspan="4" style="border:solid thin black;">
                              <form method="post" action="fournisseur.php?action=autrefrais&facturid=<?php echo $_GET['facturid']; ?>">
			      <div align="left">
				<table>
				  <tr>
				    <td>Type : </td>
				    <td><select id="type" name="type" onchange="afficheArticle(this.value)"><?= $opttype; ?></select></td>
				  </tr>
				  <tr id="assocCmd">
				    <td>Associé à la commande : </td><td><input type="text" id="afsitefacture" size="20" maxlength="20" oninput="associnfos(this.value)"><div id="associnfos"></div></td> <!--<input type="checkbox" onclick="stockligne(this.checked);">Commande de stock-->
				  </tr>
				  <tr id="articleSpan" style="display: none;">
				    <td>Article : </td>
				    <td><input type="text" name="article" id="article" size="10" onkeyup="srcartid(this.value)">&nbsp;&nbsp;Qte : <input type="text" name="qte_art" id="qte_art" size="2"></td>
				    </tr>
				  <tr>
				    <td>Description : </td>
				    <td><input type="text" id="description" name="description" size="50"></td>
                                  </tr>
				  <tr id="prixSpan">                              
				    <td>Prix unitaire <?php echo $devises->symbole($facture['devise']); ?> H.T : </td>
				    <td><input type="text" id="prix" name="prix" size="5"> <span id="textEcoPart"></span><input type="hidden" id="afecopart" name="afecopart" value="0"></td>
				  </tr>
				  <tr id="dmdavoir">
				    <td>Demande d'avoir :</td>
				    <td>
					<input type="checkbox" name="createavoir" value="1" onclick="document.getElementById('afavoir').style.display=(this.checked?'block':'none');">
					<div id="afavoir">
					    <input type="hidden" name="avoirqte" value="1">
					    PU HT réel : <input type="text" name="prixavoir" value="0" size="4">
					    <br>Infos : <textarea name="avoirtext" rows="3" cols="60"></textarea>
					</div>
				    </td>
				  </tr>
				  <tr>
				  	<td>Facture non associée :</td>
				  	<td>
				  		<input type="checkbox" name="bft" value="1" onclick="verification(this.checked)">
				  	</td>
				  </tr>
				  <tr>
				    <td></td>
				    <td><input type="submit" value="Ajouter"></td>
				  </tr>
				</table>
                              </div>
                              </form>
                         </td>
                    </tr>
                    <tr id="tra_3" style="display:none;">
			 <td colspan="4" style="border:solid thin black;" id="avoirlist">
			    <br>Sélectionnez un avoir : <select id="selectavoir" onchange="selectavoir();"></select>
			    <div id="addavoir" style="display:none;">
				<form action="fournisseur.php?facturid=<?=$_GET['facturid']; ?>" method="post">
				<input type="hidden" name="action" value="addavoir">
				<input type="hidden" name="avoirid" value="">
				<input type="hidden" id="avoir_facturid" name="sitefacture">
				<input type="hidden" id="typeavoir" name="type">
				Description : <input type="text" id="avoir_desc" name="description" size="40">
				<br>Prix H.T :<input type="text" id="avoir_prix" name="prix" size="5"> &nbsp; dont éco part. : <input type="text" id="avoir_ecopart" name="ecopart" size="5" value="0">
				<br><input type="submit" value="Ajouter">
				</form>
			    </div>
			 </td>
		    </tr>
		    <tr id="tra_4" style="display:none;">
			<td colspan="4" style="border:solid thin black;">
			    <div id="cmdstock"></div>
			    <div id="stockaddline">
                    <input type="hidden" name="cmdid">
                    <input type="hidden" name="artid">
                    <input type="hidden" name="type">
                    <span id="spanqte">Quantité : <input type="text" name="qte" size="1"></span>
                    <br>Prix achat unit. HT : <input type="text" name="paht" size="5"><span id="stecopart"></span>
                    <div id="stockcomment"></div>
                    <br><input type="checkbox" id="stcreateavoir" onclick="document.getElementById('stavoir').style.display=(this.checked?'block':'none');"> Demande d'avoir
                    <div id="stavoir">
                        Qté : <select id="stavoirqte"></select>
                        PU HT réel avec éco part : <input type="text" id="stprixavoir" value="0" size="4">
                        <br>Infos : <textarea id="stavoirtext" rows="3" cols="50"></textarea>
                    </div>
                    <br><input type="button" value="Associer" onclick="assocstock()">
			    </div>
			</td>
		    </tr>
		</table>
		
		<div id="modif">
		    <form name="modifcdc" method="post">
			  <input type="hidden" name="cdcid">
			  <input type="hidden" name="artid">
			  <div id="resultats"></div>
		    </form>
		</div>

<?php
		$avoirattente=$DB_site2->query_first("SELECT COUNT(*) FROM avoir_ligne WHERE ffid='".$facture['numero']."' AND date=0");
		$txtattente="";
		if($avoirattente[0]>0) $txtattente=" (".$avoirattente[0]." à envoyer)";
		print "<p><a href=\"avoir_fournisseur.php?action=ajouter&ffid=".$facture['numero']."\" target=\"_blank\">Faire une demande d'avoir".$txtattente."</a> &nbsp; <a href=\"fournisseur.php\">Retour</a></p>";
		print "<script language=\"javascript\">
			ajaxrequest('fournisseur_src.php?factureid=".$_GET['facturid']."&tableau=1',displaylines);
			var fournisseur=\"".$facture['fournisseur']."\";
		</script>";
    }

    if($action=="rechercher" || $action=="fournisseur"){
		$stock=new stock($DB_site2);
		if($_POST['src']!=""){ // recherche par numéro de facture
			$sql="SELECT * FROM facture_fournisseur WHERE numero LIKE '%".mysqli_real_escape_string($DB_site2->link_id,$_POST['src'])."%'";
		}else{ // recherche par autres critères
			$sql="SELECT * FROM facture_fournisseur WHERE 1 ";
			if($_POST['fournisseur']!=""){
				$sql.=" AND fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur'])."'";
			}
			if($_POST['du']!="" && array_key_exists($_POST['typedate'],$typesdate)){
				$sql.=" AND `".$_POST['typedate']."` > ".DateTime::createFromFormat("j/n/Y",$_POST['du'])->setTime(0,0,0)->getTimestamp()." ";
			}
			if($_POST['au']!="" && array_key_exists($_POST['typedate'],$typesdate)){
				$sql.=" AND `".$_POST['typedate']."` < ".(DateTime::createFromFormat("j/n/Y",$_POST['au'])->setTime(23,59,59)->getTimestamp())." ";
			}
			$sql.=" ORDER BY date_facture DESC";
			if($_POST['fournisseur']!=""){
				$export_link="<a href=\"export/export_facture_fourn.php?fournisseur=".stripslashes($_POST['fournisseur'])."&from=".$_POST['du']."&to=".$_POST['au']."&typedate=".$_POST['typedate']."\">Export csv</a>";
			}
		}
		$sql_count=str_replace("*", "COUNT(*)", $sql);
		$res_count=$DB_site2->query_first($sql_count);
		if($res_count[0] >1000){
			echo "<center><strong>ATTENTION : cette recherche a plus de 1 000 résultats.<br>Seuls les 1 000 premiers résultats sont affichés.<br></strong></center><br><br><br>";
			$sql.=" LIMIT 1000";
		}
		
		$i=0;
		// echo $sql;
		$requete=$DB_site2->query($sql);
		print "<table style=\"border: solid thin black; width:100%\" class=\"table_moyen_paiement\" cellspacing=\"0\" cellpadding=\"2\">
				<tr style=\"text-align:center; font-weight:bold;\">
					<td>N°</td>
					<td>Fournisseur</td>
					<td>Date</td>
					<td>Date échéance</td>
					<td>Date saisie</td>
					<td>Total T.T.C</td>
					<td>Différence calculée</td>
					<td colspan=\"2\">Action</td>
				</tr>";
		while($facture=$DB_site2->fetch_array($requete)){
			$totalht=0;
			$sql = "SELECT efl.id, efl.qte, efl.paht, efl.ecopart FROM envoi_fournisseur_ligne AS efl WHERE efl.facture = '".$facture['numero']."'";
			$requete1 = $DB_site2->query($sql);
			while($assoc = $DB_site2->fetch_array($requete1)){
				$totalht += $assoc['qte'] * ($assoc['paht'] + round($assoc['ecopart'], 3));
			}
			$sql = "SELECT efl.id, efl.qte, efl.paht, efl.ecopart FROM mp_envoi_fournisseur_ligne AS efl WHERE efl.facture = '".$facture['numero']."'";
			$requete2 = $DB_site2->query($sql);
			while($assoc = $DB_site2->fetch_array($requete2)){
				$totalht += $assoc['qte'] * ($assoc['paht'] + $assoc['ecopart']);
			}
			$sql = "SELECT * FROM facture_fournisseur_autre WHERE facture = '".$facture['numero']."'";
			$requete3 = $DB_site2->query($sql);
			while($row = $DB_site2->fetch_array($requete3)){
				$totalht += $row['prix'];
			}
			$cmdlist = $stock->getfacture($facture['numero']);
			foreach($cmdlist as $cmdid => $items){
				if(is_array($items)){
					foreach($items as $item){
						if($item->facture == $facture['numero']){
							$totalht += ($item->paht + $item->ecopartht);
						}
						if($item->facture_trspt == $facture['numero']){
							$totalht += $item->paht;
						} 		
					}
				} elseif(is_a($items,"stock_cmd")){
					$totalht += $item->tariftrspt;
				}
			}
			$totalttc=$totalht*(1+$facture['tva']/100);
			$totalttc=round($totalttc,2);
			$i++;
			$rowalt=$i%2;
			$date_limite_suppression=getLundiPrecedent(time());
			
			?>
			<tr class="td_alt<?= $rowalt ?>">
				<td align="center"><?php echo $facture['numero']; ?></td>
				<td align="center"><?php echo $facture['fournisseur']; ?></td>
				<td align="center"><?php echo date("d/m/Y",$facture['date_facture']); ?></td>
				<td align="center"><?php echo date("d/m/Y",$facture['echeance']); ?></td>
				<td align="center"><?php echo date("d/m/Y",$facture['date_saisie']); ?></td>
				<td align="right" style="color:<?php echo (round($totalttc,2)==round($facture['montant_ttc'],2)?"green":"red; font-weight:bold"); ?>;"><?php echo number_format($facture['montant_ttc'],2,',','')."&nbsp;".$devises->symbole($facture['devise']); ?></td>
				<td align="center"><?php echo number_format($facture['montant_ttc']-$totalttc,2,',','')."&nbsp;".$devises->symbole($facture['devise']); ?></td>
				<td align="center"><a href="fournisseur.php?action=editer&facturid=<?php echo $facture['numero']; ?>">Modifier</a></td>
				<td align="center">
				<?php

				if($facture['date_saisie']>$date_limite_suppression){
					?>
					 <a href="javascript:void(0);" onclick="if(confirm('Etes-vous certain de vouloir supprimer la facture fournisseur n°<?php echo $facture['numero']; ?> ?')){ document.getElementById('suppression_facture<?php echo $facture['numero']; ?>').submit(); }">Supprimer</a>
					 <form id="suppression_facture<?php echo $facture['numero']; ?>" method="post" action="?action2=supprimer&facturid=<?php echo $facture['numero']; ?>" style="padding: 0; margin: 0;">
						  <?php
						  $get_name_exclude = array('facturid', 'action2');
						  foreach($_GET as $name => $value){
							   if(!in_array($name, $get_name_exclude)){
							   ?>
									<input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>">
							   <?php
							   }
						  }
						  foreach($_POST as $name => $value){
							   ?>
							   <input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>">
							   <?php
						  }
						  ?>
					 </form>
					<?php
				} else {
					echo "trop tard";
				}
				?>
			   </td>
			</tr>
			<?php
		}
		if($DB_site2->num_rows($requete)==0)
			print "<tr><td colspan=\"3\">Aucun résultat</td></tr>";
		print "</table>";
		print "<p>&nbsp;</p>".$recherche;
		if(!is_null($export_link))
			print $export_link;
    }
}else{
    print $recherche;

}

function getLundiPrecedent($TimeStampActuel){ // renvoi la date du dernier lundi à 19h.
	if (date('w',$TimeStampActuel)=='0'){
		$joursAsoustraire=6;
	} elseif(date('w',$TimeStampActuel)=='1') {// si lundi
		if(date("H", $TimeStampActuel)<18){// si plus tard que 18h
			$joursAsoustraire=7;
		} else {
			$joursAsoustraire=0;
		}
	} else {
		$joursAsoustraire=date('w',$TimeStampActuel) -1;
	}
	return mktime(19, 0, 0, date("n",$TimeStampActuel) , (date("j", $TimeStampActuel) - $joursAsoustraire), date("Y", $TimeStampActuel));
}


include "./footer.php";

?>
