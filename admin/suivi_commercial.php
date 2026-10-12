<?php
require_once("admin_global.php");

include_once("./header.php");
include_once("./menu.php");

$today     = mktime(0,0,0);
$tomorrow  = $today+86400;
$tomorrow2 = $today+(2*86400);
$yesterday = $today-(100*86400);

$user_rappel  = isset($_GET['user_rappel']) ? $_GET['user_rappel'] : '';
$type_suivi   = isset($_GET['type_suivi']) ? $_GET['type_suivi'] : '';
$statut_suivi = isset($_GET['type']) ? $_GET['type'] : '';
$vip          = isset($_GET['vip']) ? $_GET['vip'] : '';
$etat         = isset($_GET['etat']) ? $_GET['etat'] : '';
$Conseils     = isset($_GET['conseils']) ? $_GET['conseils'] : '';
        
?>
<style>
.tbl { border:solid 1px black; border-collapse:collapse; width:800px; }
.tbl td { background-color:#DDD; border-bottom:solid 2px white; padding:2px; }
.tbl .done td { background-color:#BBFFBB; }
</style>

<script language="javascript">
     
     function showrappel(name){
          if(document.getElementById(name).style.display == 'none'){
               document.getElementById('ic'+name).src = 'images/moins.jpg';
               document.getElementById(name).style.display = 'table';
          }else{
               document.getElementById('ic'+name).src = 'images/plus.jpg';
               document.getElementById(name).style.display = 'none';
          }
     }
     
     function ajaxReloadTri(div, tri, order, display){
          divChange = div;
          
          //Remplacement du tableau par le loader
          document.getElementById('tab_'+divChange).innerHTML = "<img src=\"images/loader.gif\" style=\"margin:0 auto; width: 50px; margin: 25px 0 25px 0;\">";
          document.getElementById('nb_contact_'+divChange).innerHTML = "<img src=\"images/loader.gif\" style=\"width: 10px;\">";
          document.getElementById('valeur_'+divChange).innerHTML = "<img src=\"images/loader.gif\" style=\"width: 10px;\">";
          document.getElementById('nb_contact_total').innerHTML = "<img src=\"images/loader.gif\" style=\"width: 10px;\">";
          document.getElementById('valeur_total').innerHTML = "<img src=\"images/loader.gif\" style=\"width: 10px;\">";
          
		  // ajout
		  
		  
		  if(display==''){
			  liste_ids=document.getElementById('liste_ids').value;
		  } else {
			  liste_ids='';
		  }
		  
          //Chargement du tableau en ajax
          ajaxrequest("includes/suivi_commercial/tableau.php?user_rappel=<?php echo $user_rappel; ?>&user_commercial=<?php echo $user_commercial; ?>&type_suivi=<?php echo $type_suivi; ?>&type=<?php echo $statut_suivi; ?>&vip=<?php echo $vip; ?>&etat=<?php echo $etat; ?>&conseils=<?php echo $conseils; ?>&tableau="+div+"&tri="+tri+"&order="+order+"&display="+display+"&liste_ids="+liste_ids, ajaxReloadTri_cb,null,1);
     }
     
     function ajaxReloadTri_cb(httprequest){
          
          //Remplacement du loader par le tableau rechargé
          document.getElementById('tab_'+divChange).innerHTML = httprequest.responseText;
          
          document.getElementById('nb_contact_'+divChange).innerHTML = document.getElementById('tab_nb_'+divChange).value;
          document.getElementById('valeur_'+divChange).innerHTML = document.getElementById('tab_valeur_'+divChange).value;
		  
		  // ajout
		  document.getElementById('liste_ids').value = document.getElementById('tab_ids_'+divChange).value;
		  
          
          majTotal();
     }
     
     function majTotal(){
          
          var nbTot = 0;
          var valTot = 0;
          
          if(document.getElementById('tab_nb_nouveau')){
               nbTot += parseInt(document.getElementById('tab_nb_nouveau').value);
               valTot += parseInt(document.getElementById('tab_valeur_brut_nouveau').value);
          }
          
          if(document.getElementById('tab_nb_nrappel')){
               nbTot += parseInt(document.getElementById('tab_nb_nrappel').value);
               valTot += parseInt(document.getElementById('tab_valeur_brut_nrappel').value);
          }
          
          if(document.getElementById('tab_nb_jour')){
               nbTot += parseInt(document.getElementById('tab_nb_jour').value);
               valTot += parseInt(document.getElementById('tab_valeur_brut_jour').value);
          }
          
          if(document.getElementById('tab_nb_retard')){
               nbTot += parseInt(document.getElementById('tab_nb_retard').value);
               valTot += parseInt(document.getElementById('tab_valeur_brut_retard').value);
          }
          
          if(document.getElementById('tab_nb_demain')){
               nbTot += parseInt(document.getElementById('tab_nb_demain').value);
               valTot += parseInt(document.getElementById('tab_valeur_brut_demain').value);
          }
          
          if(document.getElementById('tab_nb_norap')){
               nbTot += parseInt(document.getElementById('tab_nb_norap').value);
               valTot += parseInt(document.getElementById('tab_valeur_brut_norap').value);
          }
          
          if(document.getElementById('tab_nb_futur')){
               nbTot += parseInt(document.getElementById('tab_nb_futur').value);
               valTot += parseInt(document.getElementById('tab_valeur_brut_futur').value);
          }
          
          document.getElementById('nb_contact_total').innerHTML = nbTot+' contacts';
          document.getElementById('valeur_total').innerHTML = number_format(valTot,2,',',' ')+' &euro;';
     }
     
     function number_format(number, decimals, dec_point, thousands_sep) {

          number = (number + '')
          .replace(/[^0-9+\-Ee.]/g, '');
          var n = !isFinite(+number) ? 0 : +number,
          prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
          sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
          dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
          s = '',
          toFixedFix = function(n, prec) {
               var k = Math.pow(10, prec);
               return '' + (Math.round(n * k) / k)
               .toFixed(prec);
          };
          // Fix for IE parseFloat(0.55).toFixed(0) = 0;
          s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
            .split('.');
          if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
          }
          if ((s[1] || '')
          .length < prec) {
               s[1] = s[1] || '';
               s[1] += new Array(prec - s[1].length + 1)
              .join('0');
          }
          return s.join(dec);
     }

     
     document.title = "Suivi commercial";
     
</script>

<h1>Suivi commercial</h1>
<?php

//Récupération de tous les commerciaux actifs dans les 30 derniers jours
$sql = "SELECT commercial";
$sql .= " FROM utilisateur";
$sql .= "  WHERE commercial <> ''";
$sql .= "  AND TO_DAYS(NOW()) - TO_DAYS(dateinscription) <= 30";
// $sql .= "  AND TO_DAYS(NOW()) - TO_DAYS(dateinscription) <= 900";
$sql .= "  GROUP BY commercial";
$sql .= "  ORDER BY commercial";
$req = $DB_site2->query($sql);
$optcom = "<option value=\"\">Tous</option>";
$optcomatt = "<option value=\"\">Tous</option>";
while($commercial = $DB_site2->fetch_array($req)){
    $optcom .= "<option value=\"".$commercial[0]."\" ".($_GET['user_rappel'] == $commercial[0] ? "selected" : "").">$commercial[0]</option>";
	$optcomatt .= "<option value=\"".$commercial[0]."\" ".($_GET['user_commercial'] == $commercial[0] ? "selected" : "").">$commercial[0]</option>";
}
$optcomatt .= "<option value=\"na\" ".($_GET['user_commercial'] == "na" ? "selected" : "").">Non attribué</option>";


//Récupération de la liste des type de suivi
$sql = "SELECT *";
$sql .= " FROM utilisateur_type_suivi";
$req = $DB_site2->query($sql);
while($row = $DB_site2->fetch_array($req)){
     $aTypeSuivi[] = $row;
}

?>
<form method="get" action="suivi_commercial.php">
     Conseils :
     <select style="width: 70px;" name="conseils">
          <option value="" <?php echo ($_GET['conseils'] == "" ? " selected" : ""); ?> >Tous</option>
          <option value="1" <?php echo ($_GET['conseils'] == "1" ? " selected" : ""); ?> >Oui</option> 
          <option value="0" <?php echo ($_GET['conseils'] == "0" ? " selected" : ""); ?> >Non</option>
          <option value="2" <?php echo ($_GET['conseils'] == "2" ? " selected" : ""); ?> >Non défini</option>    
     </select>
     &nbsp;
     Rappel à : 
     <select style="width: 70px;" name="user_rappel">
          <?php echo $optcom; ?>
     </select>
     &nbsp;
	 Attribué à : 
     <select style="width: 70px;" name="user_commercial">
          <?php echo $optcomatt; ?>
     </select>
     &nbsp;
     Type de client : 
     <select style="width: 70px;" name="type">
          <option value="" <?php echo ($_GET['type'] == "" ? " selected" : ""); ?>>Tous</option>
          <option value="pro" <?php echo ($_GET['type'] == "pro" ? " selected" : ""); ?>>Pro</option>
          <option value="part" <?php echo ($_GET['type'] == "part" ? " selected" : ""); ?>>Particuliers</option>
     </select>
	 <br>
     &nbsp;
     Type de suivi : 
     <select style="width: 70px;" name="type_suivi">
          <option value="">Tous</option>
          <option value="0" <?php echo isset($_GET['type_suivi']) && $_GET['type_suivi'] == 0 && $_GET['type_suivi'] != '' ? 'selected' : ''; ?>>Non défini</option>
          <?php foreach($aTypeSuivi as $le_type_suivi){ ?>
          <option value="<?php echo $le_type_suivi['utilisateur_type_suivi_id']; ?>" <?php echo isset($_GET['type_suivi']) && $_GET['type_suivi'] == $le_type_suivi['utilisateur_type_suivi_id'] ? 'selected' : ''; ?>><?php echo $le_type_suivi['utilisateur_type_suivi_nom']; ?></option>
          <?php } ?>
     </select>
     &nbsp;
     Profil client :
      <select style="width: 70px;" name="vip">
          <option value="">Tous</option>
          <option value="-1"<?php echo ($_GET['vip']==-1?"selected":""); ?>>Client blacklisté</option>
          <option value="-2"<?php echo ($_GET['vip']==-2?"selected":""); ?>>Client difficile</option>
          <option value="3"<?php echo ($_GET['vip']=="3"?"selected":""); ?>>Client normal</option>
          <option value="1"<?php echo ($_GET['vip']==1?"selected":""); ?>>Client VIP</option>
          <option value="2"<?php echo ($_GET['vip']==2?"selected":""); ?>>Partenaire</option>
     </select>
     &nbsp;
	 Etat :
      <select style="width: 70px;" name="etat">
          <option value="">Tous</option>
          <option value="1"<?php echo ($_GET['etat']=="1"?"selected":""); ?>>à suivre</option>
          <option value="2"<?php echo ($_GET['etat']=="2"?"selected":""); ?>>attente info client</option>
          <option value="3"<?php echo ($_GET['etat']=="3"?"selected":""); ?>>à chiffrer</option>
     </select>
     &nbsp;
     <input type="submit" value="OK">
</form>
<?php

$affiche_resultats = false;
if(isset($_GET['user_rappel']) && isset($_GET['type_suivi']) && isset($_GET['type']) && isset($_GET['vip']) /*&& isset($_GET['conseils'])*/) $affiche_resultats = true;

if($affiche_resultats){
     ?>
     <table class="table_alternate_color" style="margin-top:10px; width: 550px;">
          <tr>
               <td class="entete3" align="center"><strong>Tableau</strong></td>
               <td class="entete3" align="center"><strong>Nb contacts</strong></td>
               <td class="entete3" align="center"><strong>Tot. commandes</strong></td>
          </tr>
          <tr>
               <td class="td_alt1"><a href="#nouveau">Nouveaux contacts - Prioritaire</a></td>
               <td class="td_alt1" align="center"><span id="nb_contact_nouveau"></span></td>
               <td class="td_alt1" align="center"><span id="valeur_nouveau"></span></td>
          </tr>
          <tr>
               <td class="td_alt2"><a href="#nrappel">Contacts en cours sans rappel - Prioritaire</a></td>
               <td class="td_alt2" align="center"><span id="nb_contact_nrappel"></span></td>
               <td class="td_alt2" align="center"><span id="valeur_nrappel"></span></td>
          </tr>
          <tr>
               <td class="td_alt1"><a href="#jour">Rappels du jour</a></td>
               <td class="td_alt1" align="center"><span id="nb_contact_jour"></span></td>
               <td class="td_alt1" align="center"><span id="valeur_jour"></span></td>
          </tr>
          <tr>
               <td class="td_alt2"><a href="#retard">Rappels en retard</a></td>
               <td class="td_alt2" align="center"><span id="nb_contact_retard"></span></td>
               <td class="td_alt2" align="center"><span id="valeur_retard"></span></td>
          </tr>
          <tr>
               <td class="td_alt1"><a href="#demain">Rappels de demain</a></td>
               <td class="td_alt1" align="center"><span id="nb_contact_demain"></span></td>
               <td class="td_alt1" align="center"><span id="valeur_demain"></span></td>
          </tr>
          <tr>
               <td class="td_alt2"><a href="#norap">Dossiers sous-traités</a></td>
               <td class="td_alt2" align="center"><span id="nb_contact_norap"></span></td>
               <td class="td_alt2" align="center"><span id="valeur_norap"></span></td>
          </tr>
          <tr>
               <td class="td_alt1"><a href="#futur">Rappels futurs</a></td>
               <td class="td_alt1" align="center"><span id="nb_contact_futur"></span></td>
               <td class="td_alt1" align="center"><span id="valeur_futur"></span></td>
          </tr>
          <tr style="font-weight:bold;">
               <td class="td_alt2">Total</td>
               <td class="td_alt2" align="center"><span id="nb_contact_total"></span></td>
               <td class="td_alt2" align="center"><span id="valeur_total"></span></td>
          </tr>
     </table>
	
	
	<input type="hidden" id="liste_ids" name="liste_ids" value="">
	
	
     <div id="tab_nouveau" style="text-align: center; margin: 10px 0;"></div>
     <div id="tab_nrappel" style="text-align: center; margin: 20px 0 10px;"></div>
     <div id="tab_jour" style="text-align: center; margin: 20px 0 10px;"></div>
     <div id="tab_retard" style="text-align: center; margin: 20px 0 10px;"></div>
     <div id="tab_demain" style="text-align: center; margin: 20px 0 10px;"></div>
     <div id="tab_norap" style="text-align: center; margin: 20px 0 10px;"></div>
     <div id="tab_futur" style="text-align: center; margin: 20px 0 10px;"></div>
     <?php
     //foreach($display_order as $key) print $html[$key];
} else {
	?>
	<p style="text-align: center; color: #A71145; font-weight: bold; margin: 20px 0 0;">Filtrez pour afficher des résultats</p>
	<?php
}
?>
<script type="text/javascript">
     ajaxReloadTri('nrappel','tag','desc','');
     ajaxReloadTri('jour','date_rappel','asc','');
     ajaxReloadTri('retard','date_rappel','asc','');
     ajaxReloadTri('demain','date_rappel','asc','');
     ajaxReloadTri('norap','tag','desc','');
     ajaxReloadTri('futur','date_rappel','asc','');
	 ajaxReloadTri('nouveau','tag','desc','');
</script>
<?php
include "footer.php";