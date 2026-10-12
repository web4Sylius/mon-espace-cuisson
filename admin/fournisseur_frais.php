<?php

require "admin_global.php";

include "./header.php";
include "./menu.php";

$moyens['CB']="411CVCB";
$moyens['Chèque']="411CH";
$moyens['Mandat']="411CVMAND";
$moyens['Virement']="411VR";
$moyens['Paypal']="411PP";
ksort($moyens);

$default=array();

if(isset($_POST['fournisseur'])){
    $datefacture=strtots($_POST['date_facture']);
    $dateecheance=strtots($_POST['date_echeance']);
    $exist=$DB_site2->query_first("SELECT * FROM facture_fournisseur_frais WHERE numero='".$_POST['numero']."' AND fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur'])."'");
    if($exist['numero']!=""){
        $error="Une facture existe déjà avec le numéro ".$exist['numero']." chez le fournisseur ".$exist['fournisseur'];
        $default=$_POST;
    }else{
        $fourn=$DB_site2->query_first("SELECT * FROM fournisseur_frais WHERE fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur'])."'");
        $devise=$DB_site2->query_first("SELECT d.code FROM devises d INNER JOIN fournisseur_condition fc ON d.code = fc.devise WHERE fc.societe = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur'])."'");

        $DB_site2->query("INSERT INTO facture_fournisseur_frais SET 
        date_saisie=".time().", fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$_POST['fournisseur'])."', nom='".$_POST['nom']."',
        date_facture=".intval($datefacture).", numero='".$_POST['numero']."',
        montant_ht=".floatval($_POST['montant_ht']).", tva=".floatval($_POST['tva']).", 
        montant_ttc=".floatval($_POST['montant_ttc']).", date_echeance=".intval($dateecheance).",
        moyen='".$_POST['moyen']."', code401='".$fourn['code401']."',compte='".$fourn['compte']."', 
        code_analytique='".$fourn['code_analytique']."', devise='".$devise['code']."'");

        if(isset($_POST['avoirid']) && $_POST['avoirid']!=""){
            $DB_site2->query("UPDATE avoir_ligne_fg SET date_recu=".time()." WHERE id=".intval($_POST['avoirid']));
        }
    }
}

if(isset($_POST['prixavoir'])){
    $totalligne=($_POST['paht']-$_POST['prixavoir']);

    $sql="INSERT INTO avoir_ligne_fg SET ffid='".mysqli_real_escape_string($DB_site2->link_id,$_POST['ffid'])."',
    date=".time().",
    utilisateur='".mysqli_real_escape_string($DB_site2->link_id,$user_info['username'])."',
    destinataire='".mysqli_real_escape_string($DB_site2->link_id,$_POST['destinataire'])."',
    prix=".floatval($totalligne).",
    description='".mysqli_real_escape_string($DB_site2->link_id,$_POST['avoirtext'])."'";
    $DB_site2->query($sql);
}

$sql2 = "SELECT ff.fournisseur, fc.devise FROM fournisseur_frais ff INNER JOIN fournisseur_condition fc ON (fc.societe = ff.fournisseur) ORDER BY ff.fournisseur";
$requete=$DB_site2->query($sql2);
while($f=$DB_site2->fetch_array($requete)){
    $fournisseurs[]=$f['fournisseur'];
    if($f['devise']=="") $f['devise']=$devises->defaut;
    $jsdevise.="devise_fournisseur['".addslashes($f['fournisseur'])."']='".$f['devise']."';\n";
}

print <<<JAVASCRIPT
<LINK href="cal.css" type="text/css" rel="stylesheet">
<script language="javascript" src="calendrier.js"></script>
<script language="javascript">
function modeauto(code401,compte,code_ana){
    if(code401==''){
  document.getElementById('trmoyen').style.display='table-row';
    }else{
  document.getElementById('trmoyen').style.display='none';
    }
    document.getElementsByName('code401')[0].value=code401;
    document.getElementsByName('compte')[0].value=compte;
    document.getElementsByName('code_ana')[0].value=code_ana;
}

function veriftotal(){
  var ttc=parseFloat(document.getElementsByName('montant_ht')[0].value)*(1+(parseFloat(document.getElementsByName('tva')[0].value)/100));
  ttc=Math.round(ttc*100)/100;
  montant_ttc=parseFloat(document.getElementsByName('montant_ttc')[0].value);
  if(montant_ttc>=ttc-0.01 && montant_ttc<=ttc+0.01) return true
  return false;
}

function addfacture(){
  if(veriftotal())
      document.forms.ajoutfacture.submit();
      //alert(\"ok\");
  else
      alert("Le total T.T.C ne correspond pas au total H.T avec ce taux de T.V.A");
}

var devise_fournisseur=new Array();
$jsdevise
function change_devise(fournisseur) {
    var devise=devise_fournisseur[fournisseur];
    document.getElementById('montant_ht_devise').innerHTML=devise;
    document.getElementById('montant_ttc_devise').innerHTML=devise;
    document.getElementsByName('devise')[0].value=devise;
}

function envoi(){

    html="<p>Bonjour</p><p>Après vérification de votre facture, il s'avère que nous contestons le montant des éléments suivants : </p>";
    html+="<table style='margin-top:10px;border:solid thin black;font-size:12px;' border='1' cellspacing='0'><tr><td>N° facture</td><td>Dénomination</td><td>Prix facturé &euro; HT</td><td>Prix commandé &euro;HT</td><td>Avoir demandé</td><td>Votre commentaire</td></tr>";
    
    var ffid=document.getElementsByName('ffid')[0].value;
    var paht=document.getElementsByName("paht")[0].value;
    var prixavoir=document.getElementsByName("prixavoir")[0].value;
    var totalligne=paht-prixavoir;
    var totallignes=totalligne;
    var libelle=document.getElementsByName("avoirtext")[0].value;

	html+="<tr><td>"+ffid+"</td><td>"+libelle+"</td><td>"+(Math.round(100*paht)/100)+" &euro;</td><td>"+(Math.round(100*prixavoir)/100)+" &euro;</td><td>"+(Math.round(totalligne*100)/100)+"&euro;</td><td></td></tr>";
        
    html+="<tr><td colspan='4' align='right'>Total avoir : </td><td><b>"+(Math.round(totallignes*100)/100)+" &euro;</b></td></tr></table>";
    html+="<p>Nous vous remercions de nous faire parvenir les avoirs correspondants sous 15 jours maximum.</p>";

    mail_auto(undefined,"","$user_info[mail_contact]","Demande d'avoirs",html);
    sendform=document.getElementById('avoirform');
}

function filteravoir(fourn){
    var options=document.getElementsByName("avoirid")[0].options;
    for(x=1;x<options.length;x++){
        options[x].style.display=options[x].dataset.fourn==fourn?"":"none";
    }
}
</script>
<style>
#trmoyen { display:none; }
#avoirform { display:none; }
</style>
JAVASCRIPT;

echo "<h1>Frais généraux</h1>";

if(!isset($_GET['action'])){
    $sql = "SELECT ff.*";
    $sql .= " FROM fournisseur_frais ff";
    //$sql .= " INNER JOIN fournisseur_condition fc ON (fc.societe = ff.fournisseur)";
    $sql .= " ORDER BY ff.fournisseur";
    $fournisseurs = $DB_site2->query($sql);

    $optf = "<option value=\"\">-- Sélectionner --</option>";
    while($f = $DB_site2->fetch_array($fournisseurs))
    {
        $optf .= "<option " . ($_GET['fournisseur'] == $f['fournisseur'] ? " selected" : "") . ">" . $f['fournisseur'] . "</option>";
    } 
    
    echo ""
    . "<form method=\"get\" action=\"\">"
        . "<p>"
            . "Rechercher par fournisseur : <select name=\"fournisseur\">" . $optf . "</select>"
            . "<br><br>"
            . "Rechercher par facture : <input type=\"text\" name=\"facture_numero\" value=\"\" placeholder=\"n°\">"
            . "<br><br>"
            . "<input type=\"submit\" value=\"Rechercher\">"
        . "</p>"
    . "</form>";

    //Si un recherche a été lancé
    if(isset($_GET['fournisseur']))
    { 
        $_GET['fournisseur'] = mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur']);
        $sql = "SELECT fff.*";
        $sql .= " FROM facture_fournisseur_frais fff";
        //$sql .= " INNER JOIN fournisseur_condition fc ON (fc.societe = fff.fournisseur)";
        $sql .= " WHERE 1";
        if($_GET['fournisseur'] != '') $sql .= " AND fff.fournisseur = '" . $_GET['fournisseur'] . "'";
        if($_GET['facture_numero'] != '') $sql .= " AND fff.numero = '" . $_GET['facture_numero'] . "'";
        $sql .= " ORDER BY fff.date_facture DESC";
        $req = $DB_site2->query($sql); 
        
        if($DB_site2->num_rows($req) > 0)
        {
            print "<table>
            <thead style=\"font-weight:bold;\">
            <tr><td>Fournisseur</td><td>Numéro</td><td>Date facture</td><td>Montant TTC</td></tr>
            </thead>";
            while($facture=$DB_site2->fetch_array($req)){
            print "<tr><td>".$facture['fournisseur']." ".$facture['nom']."</td><td>".$facture['numero']."</td><td>".date("d/m/Y",$facture['date_facture'])."</td><td>".$facture['montant_ttc']." ".$devises->symbole($facture['devise'])."</td><td><a href=\"fournisseur_frais.php?action=editer&id=".$facture['id']."\">Détail</a></td></tr>";
            }
            print "</table>";
        }else{
      print "<p>Ce fournisseur n'a pas de facture</p>";
  }
    }
    print "<p align=\"center\"><a href=\"fournisseur_frais.php?action=ajouter\">Ajouter une facture</a></p>";
}


if($_GET['action']=="supprimer"){
	if($_GET['id']!=""){
		$sql="DELETE FROM facture_fournisseur_frais WHERE id='".$_GET['id']."' ";
		$DB_site2->query($sql);
		echo "facture de frais fournisseur supprimée avec succès<br><a href=\"fournisseur_frais.php\">retour</a>";
	}
}


if($_GET['action']=="ajouter"){
    
    $optsm=$optf="<option></option>";
    $fournisseurs=$DB_site2->query("SELECT ff.* FROM fournisseur_frais ff  ORDER BY ff.fournisseur");
    while($f=$DB_site2->fetch_array($fournisseurs)) $optf.="<option value=\"".$f['fournisseur']."\"".($default['fournisseur']==$f['fournisseur']?" selected":"").">".$f['fournisseur']."</option>";

    foreach($moyens as $txt=>$code401) $optsm.="<option value=\"$txt\" onclick=\"document.getElementsByName('code401')[0].value='$code401';\"".($default['moyen']==$code401?" selected":"").">$txt</option>";
    
    $optav="<option></option>";
    $req=$DB_site2->query("SELECT * FROM avoir_ligne_fg WHERE date_recu=0");
    while($av=$DB_site2->fetch_array($req)){
        $optav.="<option data-fourn=\"".$av['destinataire']."\" value=\"".$av['id']."\">Facture ".$av['ffid']." ".$av['description']."</option>";
    }
    
    print "<p style=\"color:red;\">$error</p>";
    print "<form name=\"ajoutfacture\" method=\"post\">
    <input type=\"hidden\" name=\"code401\"><input type=\"hidden\" name=\"compte\"><input type=\"hidden\" name=\"code_ana\" value=\"".$default['code_ana']."\">
    <table>
    <tr><td>Fournisseur</td><td><select name=\"fournisseur\" onchange=\"filteravoir(this.value)\">$optf</select></td></tr>
    <tr><td>Avoir reçu</td><td><select name=\"avoirid\">$optav</select></td></tr>
    <!--<tr><td>Nom</td><td><input type=\"text\" name=\"nom\"></td></tr>-->
    <tr><td>Date facture</td><td><input type=\"text\" name=\"date_facture\" id=\"date_facture\" value=\"".$default['date_facture']."\" size=\"8\" onclick=\"calendrier_show('date_facture')\" readonly></td></tr>
    <tr><td>N° facture</td><td><input type=\"text\" name=\"numero\" value=\"".$default['numero']."\" size=\"8\"></td></tr>
    <tr><td>Montant H.T.</td><td><input type=\"text\" name=\"montant_ht\" value=\"".$default['montant_ht']."\" size=\"5\"></td></tr>
    <tr><td>Taux T.V.A</td><td><select name=\"tva\"><option value=\"0\"".($default['tva']=="0"?" selected":"").">0%</option><option value=\"5.5\"".($default['tva']=="5.5"?" selected":"").">5.5%</option><option value=\"7\"".($default['tva']=="7"?" selected":"").">7%</option><option value=\"10\"".($default['tva']=="10"?" selected":"").">10%</option><option value=\"19.6\"".($default['tva']=="19.6"?" selected":"").">19.6%</option><option value=\"20\"".($default['tva']=="20"?" selected":"").">20%</option></select></td></tr>
    <tr><td>Montant T.T.C</td><td><input type=\"text\" name=\"montant_ttc\" value=\"".$default['montant_ttc']."\" size=\"5\"></td></tr>
    <tr><td>Date échéance</td><td><input type=\"text\" name=\"date_echeance\" id=\"echeance\" value=\"".$default['date_echeance']."\" onclick=\"calendrier_show('echeance')\" size=\"8\" readonly></td></tr>
    <tr id=\"trmoyen\"><td>Mode de paiement</td><td><select name=\"moyen\">$optsm</select></td></tr>
    <tr><td align=\"right\"><input type=\"button\" value=\"Ajouter\" onclick=\"addfacture()\"></td><td><input type=\"button\" value=\"Retour\" onclick=\"document.location='fournisseur_frais.php';\"></td></tr>
    </table>
    </form>";

}

if($_GET['action']=="editer"){
    $facture=$DB_site2->query_first("SELECT fff.*, fc.devise FROM facture_fournisseur_frais fff LEFT JOIN fournisseur_condition fc ON (fc.societe = fff.fournisseur) WHERE fff.id=".intval($_GET['id']));
    print "<table>
		<tr><td>Fournisseur</td><td>".$facture['fournisseur']." ".$facture['nom']."</td></tr>
		<tr><td>Numéro</td><td>".$facture['numero']."</td></tr>
		<tr><td>Date</td><td>".date("d/m/Y",$facture['date_facture'])."</td></tr>
		<tr><td>Total H.T</td><td>".$facture['montant_ht']." ".$devises->symbole($facture['devise'])."</td></tr>
		<tr><td>T.V.A</td><td>".$facture['tva']."%</td></tr>
		<tr><td>Total T.T.C</td><td>".$facture['montant_ttc']." ".$devises->symbole($facture['devise'])."</td></tr>
		<tr><td>Date échéance</td><td>".date("d/m/Y",$facture['date_echeance'])."</td></tr>";
	
	$avoir="";
	$req=$DB_site2->query("SELECT * FROM avoir_ligne_fg WHERE ffid='".mysqli_real_escape_string($DB_site2->link_id,$facture['numero'])."' AND date_recu=0");
    if($DB_site2->num_rows($req)>0){
        $avoir="<p><b>Avoir en attente :</b></p>
        <table style=\"border:solid thin black; width:400px;\">";
        while($av=$DB_site2->fetch_array($req)){
            $avoir.="<tr>
                <td>".date("d/m/Y",$av['date'])."</td>
                <td>".$av['description']."</td>
                <td>".number_format($av['prix'],2,",","")."</td>
            </tr>";
        }
        $avoir.="</table>";
    }else{
        $avoir=<<<HTML
        <a href="javascript:void(0)" onclick="document.getElementById('avoirform').style.display='block';this.style.display='none';">Effectuer une demande d'avoir</a>
HTML;
    }
	
	?>
	
		<tr>
			<td colspan="2">
				 <a href="javascript:void(0);" onclick="if(confirm('Etes-vous certain de vouloir supprimer la facture de frais fournisseur n°<?php echo $facture['numero']; ?> ?')){ document.getElementById('suppression_facture').submit(); }">Supprimer</a>
				 <form id="suppression_facture" method="get" action="?" style="padding: 0; margin: 0;">
					<input type="hidden" name="action" value="supprimer">
					<input type="hidden" name="id" value="<?php echo $facture['id']; ?>">
				 </form>
		   </td>
		</tr>
    </table>
    <p><input type="button" value="Retour" onclick="document.location='fournisseur_frais.php';"></p>
    <?=$avoir; ?>
    <form method="post" id="avoirform">
        <p>Demande d'avoir :</p>
        <input type="hidden" name="ffid" value="<?=$facture['numero']; ?>">
        <input type="hidden" name="destinataire" value="<?=$facture['fournisseur']; ?>">
        <input type="hidden" name="paht" value="<?=$facture['montant_ht']; ?>">
        <p>PU HT réel : <input name="prixavoir" value="0" size="4" type="text"></p>
        <p>Infos : <textarea name="avoirtext" rows="3" cols="60"></textarea></p>
        <input type="button" value="Annuler" onclick="this.parentNode.style.display='none';this.parentNode.previousElementSibling.style.display='block';"> <input type="button" value="Envoyer la demande d'avoir" onclick="envoi();">
	</form>
	<?php
}


include "./footer.php";

?>
