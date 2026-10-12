<?php
$tab_temporaire = array();
$tab_temporaire = explode(' ', $utilisateur['dateinscription']);
$date_inscription = $tab_temporaire[0]; 
$heure_inscription = $tab_temporaire[1];

$utilisateur['userid'] = $_GET['userid'];
$tab_temporaire = array();
$tab_temporaire = explode('-', $date_inscription);
$date_inscription = $tab_temporaire[2].'/'.$tab_temporaire[1].'/'.$tab_temporaire[0];

$tab_temporaire = array();
$tab_temporaire = explode(':', $heure_inscription);
$heure_inscription = $tab_temporaire[0].'h'.$tab_temporaire[1];

$date_inscription .= ' à '.$heure_inscription;

switch ($utilisateur['civilite']){
     case '0':
          $utilisateur['civilite'] = "Monsieur" ;
          break ;
     case '1':
          $utilisateur['civilite'] = "Madame" ;
          break ;
     case '2':
          $utilisateur['civilite'] = "Madame" ;
          break ;
}

//var_dump($utilisateur);
$paysid = empty($utilisateur['paysid']) ? 57 : $utilisateur['paysid'];
$sql = "SELECT libelle";
$sql .= " FROM pays";
$sql .= " WHERE paysid = $paysid";
$pays_lib = $DB_site2->query_first($sql);

$comlist = "<option value=\"0\">-- Sélectionner --</option>";
$requete = $DB_site2->query("SELECT username FROM parametresgeneraux WHERE actif=1");
while($com = $DB_site2->fetch_array($requete)){
     $comlist.="<option value=\"$com[0]\"".($utilisateur['commercial']==$com[0]?" selected":"").">$com[0]</option>";
}

$typeprolist= "<option value=\"0\">-- Sélectionner --</option>";
$reqtypepro= $DB_site2->query("SELECT * FROM typeclient_pro WHERE 1 ORDER BY position");
while($typepro = $DB_site2->fetch_array($reqtypepro)){
     $typeprolist.="<option value=\"".$typepro['id']."\"".($utilisateur['typeclient_pro']==$typepro[0]?" selected":"").">".$typepro['libelle']."</option>";
}

$listch = explode(",",$utilisateur['contacthour']);

$sql = "SELECT COUNT(*)";
$sql .= " FROM user_files";
$sql .= " WHERE ext <> ''";
$sql .= " AND facturid = 0";
$sql .= " AND userid = ".$_GET['userid'];
$pj = $DB_site2->query_first($sql);

$communication = explode(",",$utilisateur['communication']);
$divcom = ""
. "<div class=\"div_communication\">"
     . "<form name=\"contact\" method=\"post\">"
          . "<input type=\"hidden\" name=\"comform\" value=\"1\">"
          . "<center><img src=\"".BACK_URL."/images/bulles.png\" style=\"vertical-align:middle;\"> <span class=\"title_infos\">Communication client</span></center>"
          . "<p>Moyen de communication : <input type=\"checkbox\" name=\"communication[]\" value=\"1\"".(in_array("1",$communication)?" checked":"")."> Tél &nbsp; <input type=\"checkbox\" name=\"communication[]\" value=\"2\"".(in_array("2",$communication)?" checked":"")."> Mail</p>"
          . "<p>Jour de rappel : <input type=\"checkbox\" name=\"contacthour[]\" value=\"5\"".(in_array(5,$listch)?" checked":"").">Lu <input type=\"checkbox\" name=\"contacthour[]\" value=\"6\"".(in_array(6,$listch)?" checked":"").">Ma <input type=\"checkbox\" name=\"contacthour[]\" value=\"7\"".(in_array(7,$listch)?" checked":"").">Me <input type=\"checkbox\" name=\"contacthour[]\" value=\"8\"".(in_array(8,$listch)?" checked":"").">Je <input type=\"checkbox\" name=\"contacthour[]\" value=\"9\"".(in_array(9,$listch)?" checked":"").">Ve</p>"
          . "<p>Heure de rappel : <input type=\"checkbox\" name=\"contacthour[]\" value=\"1\"".(in_array(1,$listch)?" checked":"").">9-12 <input type=\"checkbox\" name=\"contacthour[]\" value=\"2\"".(in_array(2,$listch)?" checked":"").">12-14 <input type=\"checkbox\" name=\"contacthour[]\" value=\"3\"".(in_array(3,$listch)?" checked":"").">14-18 <input type=\"checkbox\" name=\"contacthour[]\" value=\"4\"".(in_array(4,$listch)?" checked":"").">18-20</p>"
          . "Info newsletter mail : <input type=\"checkbox\" name=\"newsletterMail\" value=\"1\" ".($utilisateur['allowemail']==1?"checked":"").">"
      . "Info newsletter sms : <input type=\"checkbox\" name=\"newsletterSms\" value=\"1\" ".($utilisateur['allowsms']==1?"checked":"").">"
          . "<br />"
          . "<center><input type=\"submit\" style=\"background:linear-gradient(to bottom,#FAB700,#D98100); border-radius:10px; padding:5px; font-weight:bold; cursor:pointer; border:none; color:#363E42;\" value=\"Modifier\"></center>"
     . "</form>"
. "</div>";

//Suppression d'un utilisateur associé
if(isset($_GET['delete_assoc']) && !empty($_GET['delete_assoc'])){
     $sql = "DELETE FROM utilisateur_assoc";
     $sql .= " WHERE utilisateur_assoc_id = ".$_GET['delete_assoc'];
     $DB_site2->query($sql);
}

//Mettre un utilisateur associé en ancien contact
if(isset($_GET['old_assoc']) && $_GET['old_assoc'] !== ''){
     $actif_assoc = ($_GET['old_assoc']=='1'?0:1);
     $sql = "UPDATE utilisateur_assoc";
     $sql .= " SET actif = ".$actif_assoc;
     $sql .= " WHERE utilisateur_assoc_id = ".$_GET['id_assoc'];
     $DB_site2->query($sql);
     echo "<script>document.location=\"utilisateur.php?action=editer&userid=".$utilisateur['userid']."\";</script>";
}

//Récupération des utilisateurs associés
$sql = "SELECT *";
$sql .= " FROM utilisateur_assoc";
$sql .= " WHERE userid = ".$_GET['userid'];
$sql .= " ORDER BY actif DESC, utilisateur_assoc_nom";
$req = $DB_site2->query($sql);
while($row = $DB_site2->fetch_array($req)){
     $tabUserAssoc[] = $row;
}

//Vérification des champs manquants
$champs_vides = '';
if($utilisateur['prenom'] == '') $champs_vides .= '<br />Prénom manquant';
if($utilisateur['nom'] == '') $champs_vides .= '<br />Nom manquant';
if($utilisateur['raisonsociale'] != '' && $utilisateur['tvaintracom'] == '') $champs_vides .= '<br />TVA intra. manquante';
if($utilisateur['adresse'] == '') $champs_vides .= '<br />Adresse manquante';
if($utilisateur['codepostal'] == '') $champs_vides .= '<br />Code postal manquant';
if($utilisateur['ville'] == '') $champs_vides .= '<br />Ville manquante';
if($utilisateur['mail'] == '') $champs_vides .= '<br />Email manquant';
// Modification suite a action anormale, bloquage de cette adresse pour eviter un suivi des mails/info de ce compte
if($utilisateur['mail']=="pr@mon-espace-cuisson.com")
{
  $utilisateur['mail']="";
  $champs_vides .= '<br />EMAIL NON AUTORISE !';
}

// $urlcode=$template->urlcode($utilisateur['site_code'],$utilisateur['langue']);

$myUser = new utilisateur($DB_site2, $_GET['userid']);
$fromHost = $myUser->provenance();
$redirection = $myUser->redirection();

$provenance="";
// if($urlcode!="?") $provenance.="sur <b>".$urlcode."</b>";
$provenance .= "sur <b>$fromHost</b>";
if($utilisateur['provenance']!=""){
    if(strtolower($utilisateur['provenance'])=="administrateur"){
  $dateinscr=new DateTime($utilisateur['dateinscription']);
  $admin=$DB_site2->query_first("SELECT nom_contact FROM suivi_client INNER JOIN parametresgeneraux ON(commercial=username) WHERE suivi LIKE '%Inscription%' AND date=".$dateinscr->gettimestamp());
  if($admin['nom_contact']!="") $utilisateur['provenance']=$admin['nom_contact'];
    }
    $provenance_par= $utilisateur['provenance'];
}

$content_tooltip = ""
. "<strong>Access</strong><br>- Budget < 750€ TTC"
. "<br><br>"
. "<strong>Standard</strong><br>- Budget entre 750€ et 4000€ TTC"
. "<br><br>"
// . "<strong>Pro</strong><br>- PROS<br>- Budget < 4000€ TTC"
// . "<br><br>"
. "<strong>Luxe</strong><br>- Produits sur mesure ou luxe<br>- Budget > 4000€ TTC";

/*
$datemoins12mois=time()-(60*60*24*365);
$sqlCA12mois="SELECT SUM(lf.qte*lf.prix*(1-lf.tva/100)) AS totalht
FROM facture f 
INNER JOIN lignefacture lf ON(lf.factureid=f.facturid) 
WHERE f.heurefacture >'".$datemoins12mois."' AND valide IN (1,5) AND userid='".$utilisateur['userid']."' ";
$CA12mois=$DB_site2->query_first($sqlCA12mois);
if($CA12mois[totalht]==""){
	$CA12mois[totalht]=0;
} else {
	$CA12mois[totalht]=round($CA12mois[totalht]);
}
$sqlCAtotal="SELECT SUM(lf.qte*lf.prix*(1-lf.tva/100)) AS totalht
FROM facture f 
INNER JOIN lignefacture lf ON(lf.factureid=f.facturid) 
WHERE valide IN (1,5) AND userid='".$utilisateur['userid']."' ";
$CAtotal=$DB_site2->query_first($sqlCAtotal);
if($CAtotal[totalht]==""){
	$CAtotal[totalht]=0;
} else {
	$CAtotal[totalht]=round($CAtotal[totalht]);
}
*/
?>
<script type="text/javascript">
     function valide_check(obj){
          if(obj.checked){
               update_admin_ajax(1);
          } else {
               update_admin_ajax(0);
          }
     }
     
     function update_admin_ajax(value){
          ajaxrequest('ajax/maj_utilisateur.php?champs=utilisateur_admin&value='+value+'&userid=<?php echo $utilisateur['userid']; ?>');
     }

      $jQuery( function() {
        $jQuery("#show_adresse").tooltip({
          content: function () {
              return $jQuery(this).prop('title');
          }
      });
      } );
</script>
<div class="commande_detail" id="utilisateur_fiche">
     <table class="table_modules_boutiques no_border">
          <tr>
               <td colspan="4">
                    <div style="float:left;"><a href="clientnavig.php?userid=<?php echo $utilisateur['userid']; ?>" target="_blank"><img src="<?php echo BACK_URL; ?>/images/connexion_compte.png" style="vertical-align:middle; margin:0 10px 0 10px;" title="Se connecter sur le compte du client"></a></div>
                    <div style="float:right;"><span class="fileboxbutton" onclick="fb.show('user_files.php?userid=<?php echo $_GET['userid']; ?>&action=files', 'absolute');"><img src="<?php echo BACK_URL; ?>/images/icon/jpg.png"> Voir les fichiers joints (<?php echo $pj[0]; ?>)</span></div>
                    <h2>FICHE "<?php echo $utilisateur['prenom']." ".$utilisateur['nom']; ?>" n° <?php echo $utilisateur['userid']; ?></h2> <center><?php echo (!empty($utilisateur['code411']) ? "<strong>Code analytique</strong> : <span style=\"color:#0088AA;\">".$utilisateur['code411']."</span>" :""); ?></center>
<?php
                    if(!empty($champs_vides)){ 
?>
          <center><img src="<?php echo BACK_URL; ?>/images/icon/agt_update_critical.png" style="vertical-align: middle;"> <span style="font-weight: bold; color: #CC0000; font-size: 14px;">FICHE INCOMPLETE</span><span style="color: #CC0000;"><?php echo $champs_vides; ?></span></center>
<?php
                    }
?>
               </td>
          </tr>
          <tr>
               <td align="left" colspan="4">Inscrit depuis le : <strong><?php echo $date_inscription; ?></strong> <?php echo $provenance ?></td>
          </tr>
		  <!--
		  <tr>
               <td align="left" colspan="4">CA <u>approximatif</u> sur les 12 derniers mois : <?php echo $CA12mois['totalht']; ?> &euro;</td>
          </tr>
		  <tr>
               <td align="left" colspan="4">CA <u>approximatif</u> au total : <?php echo $CAtotal['totalht']; ?> &euro;</td>
          </tr>
           <tr>
          </tr>
		  -->
          <tr>
               <td colspan="2" valign="top">
                    <div id="show_adresse" class="infos_client" style="width: 370px;">
                         
                         <!-- TSI Utilisateur Admin checkbox / Possible d'activer un client compte admin mais bloque la desactivation -->
                          <?php
                                //print_r(" Admin SU: ".implode(",",$adminSU));
                                //print_r(" User: ".$user_info['username']);
                                //onclick="return false" ou onclick="this.checked=true/false" ???
                                //if(in_array($user_info['username'],$adminSU) || ($utilisateur['utilisateur_admin'] == 0 )) {
                          ?>
                                    <span style="font-weight: bold; color: #A71145;"><input type="checkbox" style="vertical-align: -2px;" name="utilisateur_admin" value="1" <?php echo $utilisateur['utilisateur_admin'] == 1 ? 'checked' : ''; ?> onchange="valide_check(this);"> Compte administrateur</span>
                          <?php
                                //} else {                               //onclick="return false" ou onclick="this.checked=true/false" ???
                          ?>
                                <!--    <span style="font-weight: bold; color: #A71145;"><input type="checkbox" style="vertical-align: -2px;" name="utilisateur_admin" value="1" <?php echo $utilisateur['utilisateur_admin'] == 1 ? 'checked' : ''; ?> onclick="this.checked=true"> Compte administrateur</span> -->
                          <?php
                               //};
                          ?>
                         <a href="javascript:void(0)" onclick="affiche_div('edit_adresse', 'show_adresse');"><img src="<?php echo BACK_URL; ?>/images/icon/edit.png" style="float:right;"></a>
                         <center><span class="title_infos">Coordonnées</span></center>
                         <br />
                <table class="clients_statuts">
                  <tr> <!---------------------------------------  CONSEILS PERSONNALISÉS ---------------------->
                    <?php
                      if(isset($_POST['modifconseils'])){ // traitement POST du formulaire "conseils".
                        $rep = $_POST['modifconseils'];
                        if($rep == '1'){$conseils = 'oui';}elseif($rep == '0'){$conseils = 'non';}
                        $DB_site2->query("UPDATE utilisateur SET conseils = '".$conseils."' WHERE userid='".$utilisateur['userid']."' ");
                      }else{
                        //Dans le cas on arrive sur la page sans avoir cliqué sur OUI ou NON.
                        $conseils = $utilisateur['conseils'];
                      }
                      // traitement de l'affichage
                      $classconseils1 = '';
                      $classconseils0 = '';
                      if($conseils == 'oui'){ $classconseils1 = 'conseils1';}elseif ($conseils == 'non'){$classconseils0 = 'conseils0';}
                    ?>
                    <form name="conseils" method="post">
                      <input type="hidden" name="modifconseils" id="modifconseils" value="">
                      <input type="hidden" name="conseils" id="conseils" value="">
                      <input type="hidden" name="edituser" value="1">
                      <input type="hidden" name="nocomplement" value="1">

                    </form>
                    
                    <td><strong>Conseils </strong> :</td><td><span class="<?php echo $classconseils1; ?>" id="conseils1" onclick="modifconseils(1);">Oui</span> &nbsp;/&nbsp; <span id="conseils0" class="<?php echo $classconseils0; ?>" onclick="modifconseils(0);">Non</span> </td>

                  </tr><!---------------------------------------- FIN CONSEILS PERSONNALISÉS --------------------------------------->
                  <?php 

                  ?>
							   <tr>
                  <td><strong>Type de client</strong> : </td>
                  <td>
                  <?php
										if($utilisateur['raisonsociale']==""){
											echo "particulier";
										} else {
											echo "professionnel";
										}
									?>
									</td>
                  <td rowspan="4" style="text-align: center;">
                                        <?php 
                                        $photo_client = glob("../configurations/$host/photos_clients/client_" . $utilisateur['userid'] . ".{jpg,gif,png,jpeg}", GLOB_BRACE)[0];
                                        if(file_exists($photo_client)){
                      ?>
                                            <img class="imgprof" src="<?php echo $photo_client; ?>" alt="Photo client" style="cursor: initial;">
                                            <form method="post" name="photoSuppr" enctype="multipart/form-data">
                                                <input type ="hidden" name="removeImg" value="1">
                                                <a href="javascript:void(0);" onclick="if(confirm('Supprimer la photo ?')){ document.forms.photoSuppr.submit(); }"><img src="images/icon/delete_pt.png" style="margin-top: 3px;"></a>
                                            </form>
                      <?php
                                        }
                                        ?>
                                    </td>
                              </tr>
                              <tr>
                                   <td><strong>Type de suivi</strong> :</td>
                                   <td>
                                        <select onchange="document.location='utilisateur.php?action=typesuivi&userid=<?php echo $utilisateur['userid']; ?>&type_suivi='+this.value;" id="typesuivi">
                                            <option value="0">-- Sélectionner --</option>
                                            <?php
											foreach($tabTypeSuivi as $le_type_suivi){
												 ?>
												<option value="<?php echo $le_type_suivi['utilisateur_type_suivi_id']; ?>" <?php echo ($utilisateur['utilisateur_type_suivi_id'] == $le_type_suivi['utilisateur_type_suivi_id'] ? "selected" : ""); ?>><?php echo $le_type_suivi['utilisateur_type_suivi_nom']; ?></option>
												<?php
											}
											?>
                          </select>
                                        <img src="<?php echo BACK_URL; ?>/images/information.jpg" title="<?php echo $content_tooltip; ?>">
                                    </td>
                                    
                              </tr>
                              <tr>
                                   <td><strong>Profil client</strong> : </td>
                                   <td>
                                        <select onchange="document.location='utilisateur.php?action=vip&userid=<?php echo $utilisateur['userid']; ?>&vip='+this.value;" id="profilclient">
											 <option value="0"<?php echo ($utilisateur['vip']==0?"selected":""); ?>>-- Sélectionner --</option>
                                             <option value="-1"<?php echo ($utilisateur['vip']==-1?"selected":""); ?>>Client blacklisté</option>
                                             <option value="-2"<?php echo ($utilisateur['vip']==-2?"selected":""); ?>>Client difficile</option>
                                             <option value="3"<?php echo ($utilisateur['vip']==3?"selected":""); ?>>Client normal</option>
                                             <option value="1"<?php echo ($utilisateur['vip']==1?"selected":""); ?>>Client VIP</option>
                                             <option value="2"<?php echo ($utilisateur['vip']==2?"selected":""); ?>>Partenaire</option>
                                        </select>
                                   </td>
                                   
                              </tr>

                              <tr>
                                   <td><strong>Commercial</strong> :</td>
                                   <td>
                                        <select name="commercial" id="selectcom" onchange="window.location='utilisateur.php?action=commercial&userid=<?php echo $_GET['userid']; ?>&commercial='+this.value;" class="selecttype"><?php echo $comlist; ?></select>
                                   </td>
                              </tr>

                         </table>

                         <?php if ($utilisateur['raisonsociale']!=''){
                              echo"
                                   <strong>Type pro</strong> : 
                                   <select name=\"typeclientpro\" id=\"selectpro\" onchange=\"window.location='utilisateur.php?action=typepro&userid=".$_GET['userid']."&typepro='+this.value;\" class=\"selecttypepro\">$typeprolist</select><br>
                              ";
                         }
                         ?>
                         
                         <script type="text/javascript">
                                          if(document.getElementById('typesuivi').selectedIndex == 0){
                                            document.getElementById('typesuivi').style.backgroundColor = "red";
                                            document.getElementById('typesuivi').style.color = "white";
                                          }else{
                                            document.getElementById('typesuivi').style.backgroundColor = "#159615";
                                            document.getElementById('typesuivi').style.color = "white";
                                          }
                                          if(document.getElementById('selectcom').selectedIndex == 0){
                                            document.getElementById('selectcom').style.backgroundColor = "red";
                                            document.getElementById('selectcom').style.color = "white";
                                          }else{
                                            document.getElementById('selectcom').style.backgroundColor = "#159615";
                                            document.getElementById('selectcom').style.color = "white";
                                          }
                                        </script>
                         <br />
                         <div style="float:right; margin-right: 20px;"><?php echo usericon($utilisateur['vip'], 50); ?></div>
                         <img src="<?php echo BACK_URL; ?>/<?php echo ($utilisateur['civilite'] == 'Monsieur' ? "images/icon/user_male_pt.png" : "images/icon/user_female_pt.png"); ?>">
                         <span style="font-size:14px;"><strong><?php echo $utilisateur['prenom']." ".$utilisateur['nom']; ?></strong></span>
                         <a href="https://www.google.com/search?q=<?php echo $utilisateur['nom']." ".$utilisateur['prenom']?>" target="_blank"><img src="<?php echo BACK_URL; ?>/images/icone_google.jpg" title="Google" align="absmiddle" style="margin: 0 2px;"></a> 
                         <a href="http://www.societe.com/cgi-bin/liste?dirig=<?php echo $utilisateur['nom']; ?>&pre=<?php echo $utilisateur['prenom']; ?>" target="_blank"><img src="<?php echo BACK_URL; ?>/images/societe.com.gif" align="absmiddle"  title="Societe.com" style="margin: 0 2px;" width="16"></a>
                         <a href="http://www.118000.fr/search?sb_Professionnel1=sb_Particulier1&sb_quiquoi=<?php echo $utilisateur['nom']." ".$utilisateur['prenom']; ?>&sb_Ou=<?php echo $utilisateur['codepostal']; ?>" target="_blank"><img src="<?php echo BACK_URL; ?>/images/pj.ico" title="Annuaire" align="absmiddle" style="margin: 0 2px;" width="16"></a>
                         <?php
                         if($utilisateur['raisonsociale']){
                         ?>
                         <br />
                         Société <strong><?php echo $utilisateur['raisonsociale']; ?></strong>
                         <a href="https://www.google.com/search?q=<?php echo $utilisateur['raisonsociale']; ?>" target="_blank"><img src="<?php echo BACK_URL; ?>/images/icone_google.jpg" title="Google" align="absmiddle"></a>
                         <a href="http://www.societe.com/cgi-bin/mainsrch?champ=<?php echo $utilisateur['raisonsociale']; ?>" target="_blank"><img src="<?php echo BACK_URL; ?>/images/societe.com.gif" title="societe.com" align="absmiddle" width="16"></a>
                         <a href="http://www.118000.fr/search?sb_Professionnel1=sb_professionnel&sb_quiquoi=<?php echo $utilisateur['raisonsociale']; ?>&sb_Ou=<?php echo $utilisateur['codepostal']; ?>" target="_blank"><img src="<?php echo BACK_URL; ?>/images/pj.ico" title="Annaire" align="absmiddle" width="16"></a>
                         <?php } ?>
                         <?php if(!empty($utilisateur['tvaintracom'])){ ?>
                              <br />
                              <strong>TVA Intra.</strong> : <?php echo $utilisateur['tvaintracom']; ?>
                         <?php } ?>
                         <?php if(!empty($utilisateur['siret'])){ ?>
                              <br />
                              <strong>N° SIRET</strong> : <?php echo $utilisateur['siret']; ?>
                         <?php } ?>
                         <?php if(!empty($utilisateur['eori'])){ ?>
                              <br />
                              <strong>N° EORI</strong> : <?php echo $utilisateur['eori']; ?>
                         <?php } ?>
                         <?php if(!empty($utilisateur['adresse'])){ ?>
                         <br />
                         <?php echo $utilisateur['adresse']; ?> <a href="http://maps.google.com/maps?f=q&source=s_q&hl=fr&geocode=&q=<?php echo urlencode($utilisateur['adresse']); ?>+<?php echo $utilisateur['codepostal']; ?>+<?php echo urlencode($utilisateur['ville']); ?>" target="_blank"><img src="<?php echo BACK_URL; ?>/images/localiser.gif" width="20" align="absmiddle" title="Localiser" style="margin: 0 2px;"></a>
                         <?php } ?>
                         <?php if(!empty($utilisateur['adresse2'])){ ?>
                         <br />
                         <?php echo $utilisateur['adresse2']; ?>
                         <?php } ?>
                         <?php 
                         if(!empty($utilisateur['codepostal']) || !empty($utilisateur['ville'])){
                              echo "<br />".$utilisateur['codepostal']." ".$utilisateur['ville'];
                         } 
                         ?>
                         <br />
                        
                         <?php echo $pays_lib[0]; ?> : <?php echo $utilisateur['langue']; ?> <img src="<?php echo BACK_URL; ?>/images/drapeaux/<?php echo $utilisateur['langue']; ?>.gif" width="20">
                         <?php 
                              echo('heure locale: <a href="https://www.google.com/search?q=heure+local+'.$pays_lib[0].'" target="_blank"> google</a>');
                              ?>
                         <br />
                         <strong>Email</strong> : <span><?php echo $utilisateur['mail']; ?></span>
						<?php
							if(!empty($utilisateur['mail'])){
								$sql="SELECT COUNT(*) FROM utilisateur WHERE mail='".$utilisateur['mail']."' ";
								$is_doublon = $DB_site2->query_first($sql);
								if($is_doublon[0]>1){
									echo " &nbsp; <a href=\"utilisateur.php?src=".$utilisateur['mail']."\" target=\"_blank\"><font color=\"red\"><b>!!! Email doublonné !!!</b></font></a>";
								}
							}
						?>
                         <br />
                         <strong>Tél </strong>: <?php echo "<span class=\"suivilink\" onclick=\"show_choix_suivi(this,'ficheinfo')\">".$utilisateur['telephone']."</span>
                         <span class=\"addsuivi\">
                         <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>
                         <span class=\"suivilink\" onclick=\"suiviauto('tel_out','client',".$utilisateur['userid'].",'Appel téléphone ".addslashes($utilisateur['nom']." ".$utilisateur['prenom'])."',undefined);epigy_call('".$utilisateur['telephone']."','".$user_info['telFR']."');\">Click to call</span>
                         <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span>
                         </span>"; ?>

                         <?php if($utilisateur['portable']) { echo "<span class=\"suivilink\" onclick=\"show_choix_suivi(this,'ficheinfo')\">".$utilisateur['portable']."</span>
                         <span class=\"addsuivi\">
                         <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>
                         <span class=\"suivilink\" onclick=\"suiviauto('tel_out','client',".$utilisateur['userid'].",'Appel téléphone ".addslashes($utilisateur['nom']." ".$utilisateur['prenom'])."',undefined);epigy_call('".$utilisateur['portable']."','".$user_info['telFR']."');\">Click to call</span>
                         <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span>
                         </span>"; }?>

                         <a href="https://www.google.com/search?q=<?php echo $utilisateur['telephone']; ?>" target="_blank"><img src="<?php echo BACK_URL; ?>/images/icone_google.jpg" title="Google" align="absmiddle"></a> 
                         <a href="http://www.118000.fr/search?sb_Phone=<?php echo $utilisateur['telephone']; ?>" target="_blank"><img src="<?php echo BACK_URL; ?>/images/pj.ico" title="Annuaire inversé" align="absmiddle" width="16"></a>

                         <br><strong>Profession</strong> : <?php echo $utilisateur['profession']; ?>
                    </div>
                    <div id="edit_adresse" class="infos_client" style="display:none; width: 370px;">
                         <center><span class="title_infos">Coordonnées</span></center>
                         <br />
                         <form method="post" name="form_adresse" style="padding: 0; margin: 0;">
                              <input type="hidden" name="userid" value="<?php echo $utilisateur['userid']; ?>">
                              <input type="hidden" name="action" value="editer">
                              <input type="hidden" name="edituser" value="1">
                              <input type="hidden" name="nocomplement" value="1">
                              <table class="edit">
                                   <tr>
                                        <td style="width:70px;">
                                             Civilité :
                                        </td>
                                        <td>
                                             <select name="civilite">
                                                  <option value="0" <?php echo ($utilisateur['civilite'] == 'Monsieur' ? "selected" : ""); ?>>Monsieur</option>
                                                  <option value="1" <?php echo ($utilisateur['civilite'] == 'Madame' ? "selected" : ""); ?>>Madame</option>
                                             </select>
                                        </td>
                                   </tr>
                                   <tr>
                                        <td>
                                             Prénom :
                                        </td>
                                        <td>
                                             <input type="text" name="prenom" value="<?php echo $utilisateur['prenom'] ?>">
                                        </td>
                                   </tr>
                                   <tr>
                                        <td>
                                             Nom :
                                        </td>
                                        <td>
                                             <input type="text" name="nom" value="<?php echo $utilisateur['nom'] ?>">
                                        </td>
                                   </tr>
                                   <tr>
                                        <td>
                                             Profession :
                                        </td>
                                        <td>
                                             <input type="text" name="profession" value="<?php echo $utilisateur['profession'] ?>">
                                        </td>
                                   </tr>
                                   <tr>
                                        <td>
                                             Société :
                                        </td>
                                        <td>
                                             <input type="text" name="raisonsociale" value="<?php echo $utilisateur['raisonsociale'] ?>">
                                        </td>
                                   </tr>
                                   <tr>
                                        <td>
                                             TVA intra. :
                                        </td>
                                        <td>
                                             <input type="text" name="tvaintracom" value="<?php echo $utilisateur['tvaintracom'] ?>">
                                        </td>
                                   </tr>
                                   <tr>
                                        <td>
                                             N° SIRET :
                                        </td>
                                        <td>
                                             <input type="text" name="siret" value="<?php echo $utilisateur['siret'] ?>">
                                        </td>
                                   </tr>
                                   <tr>
                                        <td>
                                             N° EORI :
                                        </td>
                                        <td>
                                             <input type="text" name="eori" value="<?php echo $utilisateur['eori'] ?>">
                                        </td>
                                   </tr>
                                   <tr>
                                        <td>
                                             Adresse :
                                        </td>
                                        <td>
                                             <input type="text" name="adresse" value="<?php echo $utilisateur['adresse'] ?>">
                                        </td>
                                   </tr>
                                   <!--<tr>
                                        <td>
                                             Complément d'adresse 1:
                                        </td>
                                        <td>
                                             <input type="text" name="adresse2" value="<?php echo $utilisateur['adresse2'] ?>">
                                        </td>
                                   </tr> -->
                                   <tr>
                                        <td>
                                             Code postal :
                                        </td>
                                        <td>
                                             <input type="text" name="codepostal" value="<?php echo $utilisateur['codepostal'] ?>">
                                        </td>
                                   </tr>
                                   <tr>
                                        <td>
                                             Ville :
                                        </td>
                                        <td>
                                             <input type="text" name="ville" value="<?php echo $utilisateur['ville'] ?>">
                                        </td>
                                   </tr>
                                   <tr>
                                        <td style="width:80px;">
                                             Pays :
                                        </td>
                                        <td>
                                             <select name="paysid">
                                                  <?php foreach($tabPays as $le_pays){ ?>
                                                       <option value="<?php echo $le_pays['paysid']; ?>" <?php echo $le_pays['paysid'] == $utilisateur['paysid'] ? 'selected' : ''; ?>><?php echo $le_pays['libelle']; ?></option>
                                                  <?php } ?>
                                             </select>
                                        </td>
                                   </tr>
                                   <tr>
                                        <td>
                                             Langue :
                                        </td>
                                        <td>
                                             <select name="langue">
                                                  <?php foreach($langues as $la_langue){ ?>
                                                       <option value="<?php echo $la_langue; ?>" <?php echo $la_langue == $utilisateur['langue'] ? 'selected' : ''; ?>><?php echo $la_langue; ?></option>
                                                  <?php } ?>
                                             </select>
                                        </td>
                                   </tr>
                                   <tr>
                                        <td>
                                             Email :
                                        </td>
                                        <td>
                                             <input type="text" name="mail" value="<?php echo $utilisateur['mail'] ?>">
                                        </td>
                                   </tr>
                                   <tr>
                                        <td>
                                             Téléphone :
                                        </td>
                                        <td>
                                             <input type="text" name="telephone" value="<?php echo $utilisateur['telephone'] ?>">
                                        </td>
                                   </tr>
                                   <tr>
                                        <td>
                                             Portable :
                                        </td>
                                        <td>
                                             <input type="text" name="portable" value="<?php echo $utilisateur['portable'] ?>">
                                        </td>
                                   </tr> 
                                   <tr>
                                        <td colspan="2" style="text-align: center; padding: 5px 0 0 0;">
                                             <a href="javascript:void(0);" title="Valider" onclick="document.forms.form_adresse.submit();"><img src="<?php echo BACK_URL; ?>/images/icon/succes.png" alt="Valider"></a>
                                             <a href="javascript:void(0);" title="Annuler" onclick="affiche_div('show_adresse', 'edit_adresse');"><img src="<?php echo BACK_URL; ?>/images/icon/cancel.png" alt="Annuler"></a>
                                        </td>
                                   </tr>
                              </table>
                         </form>
                    </div>
                    <?php
                          //mise au format du site web du client
                          $site = strpos($utilisateur['site'], "http");
                          if($site === false){
                              $site = "http://".$utilisateur['site'];
                          }else{
                              $site = $utilisateur['site'];
                          }
                    ?>
                    <div id="show_complementaire" class="infos_client" style="width: 370px;">
                         <a href="javascript:void(0)" onclick="affiche_div('edit_complementaire', 'show_complementaire');"><img src="<?php echo BACK_URL; ?>/images/icon/edit.png" style="float:right;"></a>
                         <center><span class="title_infos">Informations complémentaires</span></center>
                            <br><strong>Inscrit à la newsletter mail</strong> : <?php echo ($utilisateur['allowemail'] == 1 ? "Oui" : "Non"); ?>
                            <br><strong>Inscrit à la newsletter sms</strong> : <?php echo ($utilisateur['allowsms'] == 1 ? "Oui" : "Non"); ?>
                            <br><strong>Connu le site par</strong> : <?php echo $utilisateur['connu']; ?>
                            <br><strong>Site web</strong> : <a href="<?php echo  $site ?>" target="_blank"><?php echo $site; ?></a>
                            <br><strong>Code 411</strong> : <?php echo $utilisateur['code411']; ?>
                            <br><strong>Provenance</strong> : <?php echo $fromHost; ?>
                            <br><strong>Redirection</strong> : <?php echo $redirection; ?>
                            <br><strong>gclid</strong> : <?php echo $utilisateur['gclid']; ?>
                            <br><strong>Inscrit sur le site par</strong> : <?php echo $provenance_par; ?>
                    </div>
                    <div id="edit_complementaire" class="infos_client" style="display:none; width: 370px;">
                         <center><span class="title_infos">Informations complémentaires</span></center>
                         <form method="post" name="form_complementaire" style="padding: 0; margin: 0;" enctype="multipart/form-data">
                              <input type="hidden" name="userid" value="<?php echo $utilisateur['userid']; ?>">
                              <input type="hidden" name="action" value="editer">
                              <input type="hidden" name="edituser" value="1">
                              <table class="edit">
                  								<tr>
                  									<td>Newsletter par mail :</td>
                  									<td><input type="checkbox" name="allowemail" style="vertical-align: -3px;" value="1" <?php echo ($utilisateur['allowemail'] == 1 ? "checked" : ""); ?>></td>
                  								</tr>
                  								<tr>
                  									<td>Newsletter par sms :</td>
                  									<td><input type="checkbox" name="allowsms" style="vertical-align: -3px;" value="1" <?php echo ($utilisateur['allowsms'] == 1 ? "checked" : ""); ?>></td>
                  								</tr>
                                   <tr>
                                        <td style="width:120px;">
                                             Connu le site par :
                                        </td>
                                        <td>
                                             <select name="connu">
                                                  <option value=""></option>
                                                  <?php foreach($tabConnu as $le_connu){ ?>
                                                       <option value="<?php echo $le_connu['valeur']; ?>" <?php echo $le_connu['valeur'] == $utilisateur['connu'] ? 'selected' : ''; ?>><?php echo $le_connu['description']; ?></option>
                                                  <?php } ?>
                                             </select>
                                        </td>
                                   </tr>
                                   <tr>
                                        <td>
                                             Site web :
                                        </td>
                                        <td>
                                             <input type="text" name="site" value="<?php echo $utilisateur['site'] ?>">
                                        </td>
                                   </tr>
                                   <tr>
                                        <td>
                                             Code 411 :
                                        </td>
                                        <td>
                                             <input type="text" name="code411" value="<?php echo $utilisateur['code411'] ?>">
                                        </td>
                                   </tr>
                                   <tr>
                                       <td  colspan="2">
                                            <input type ="hidden" name="checkImg" value="1">
                                            <span><strong>Photo</strong> : <input type="file" id="photo_client" name="photo_client"></span>
                                       </td>
                                   </tr>
                  									<tr>
                  									  <td>Provenance :</td>
                  									  <td><?php echo $fromHost; ?></td>
                  									</tr>
                  									<tr>
                  									  <td>Rediriger vers :</td>
                  									  <td>
                  										<select name="redirect_to">
                  										  <option value="<?php echo $fromHost; ?>">site de provenance</option>
                  										  <option value="www.ma-cave-a-vin.fr" <?php echo $redirection == 'www.ma-cave-a-vin.fr' ? 'selected' : ''; ?>>www.ma-cave-a-vin.fr</option>
                  										  <option value="pro.ma-cave-a-vin.fr" <?php echo $redirection == 'pro.ma-cave-a-vin.fr' ? 'selected' : ''; ?>>pro.ma-cave-a-vin.fr</option>
                  										  <option value="www.my-wine-cabinet.com" <?php echo $redirection == 'www.my-wine-cabinet.com' ? 'selected' : ''; ?>>www.my-wine-cabinet.com</option>
                  										  <option value="pro.my-wine-cabinet.com" <?php echo $redirection == 'pro.my-wine-cabinet.com' ? 'selected' : ''; ?>>pro.my-wine-cabinet.com</option>
                  										</select>
                  										<input type="hidden" name="redirect_from" value="<?php echo $fromHost; ?>">
                  									  </td>
                  									</tr>
                                    <tr>
                                        <td colspan="2" style="text-align: center; padding: 5px 0 0 0;">
                                             <a href="javascript:void(0);" title="Valider" onclick="document.forms.form_complementaire.submit();"><img src="<?php echo BACK_URL; ?>/images/icon/succes.png" alt="Valider"></a>
                                             <a href="javascript:void(0);" title="Annuler" onclick="affiche_div('show_complementaire', 'edit_complementaire'); document.forms.photoAEnvoyer.submit();"><img src="<?php echo BACK_URL; ?>/images/icon/cancel.png" alt="Annuler"></a>
                                        </td>
                                   </tr>
                            </table>
                         </form>
                    </div>
               </td>
               <td colspan="2" valign="top" align="center">
                    <?php
                    if($utilisateur['raisonsociale']!=""){
                        $utilisateur_livraison="<option></option>";
                        if(is_null($utilisateur['francodeport'])){
                          $sql="SELECT gratuitpro FROM pays p, fraisport fp WHERE p.paysid = fp.paysid AND zone_tnt=0 AND sites='base' AND p.paysid = 57";
                          $port = $DB_site2->query_first($sql);
                          $francodeport = $port[0];
                          $defaut = "Tarif par défaut";
                        }else{
                          $francodeport = $utilisateur['francodeport'];
                          $defaut = "tarif personnalisé";
                        }
                        $opts=array("Enlevement par le client sur notre plateforme","Livraison chez notre client pro","Livraison chez le client final");
                        foreach($opts as $opt)
                            $utilisateur_livraison.="<option".($opt==$utilisateur['utilisateur_livraison']?" selected":"").">".$opt."</option>";
                    
                    ?>
                        <div class="infos_client" align="left">
                            <a href="javascript:void(0)" onclick="affiche_div('edit_condpro', 'show_condpro');"><img src="images/icon/edit.png" style="float:right;"></a>
                            <center><span class="title_infos">Conditions client pro</span></center><br>
                            <div id="show_condpro">
                                <strong>Remise</strong> : <?=$utilisateur['pourcentage_remise']; ?>
                                <br><strong>Conditions de règlements</strong> : <?=$utilisateur['conditions_reglement']; ?>
                                <br><strong>Encours client HT</strong> : <?=round($utilisateur['encours_financier'],2); ?>€
                                <br><strong>Garantie</strong> : <?=$utilisateur['garantie']; ?>
                                <br><strong>Livraison commande</strong> : <?=$utilisateur['utilisateur_livraison']; ?>
                                <br><strong>Franco de port</strong> : <?=$francodeport; ?>€ <?=$defaut; ?>
                            </div>
                            <form method="post" id="edit_condpro" style="display:none;">
                                <table class="edit">
                                    <tr>
                                        <td>Remise :</td>
                                        <td><textarea name="pourcentage_remise"><?=$utilisateur['pourcentage_remise']; ?></textarea></td>
                                    </tr>
                                    <tr>
                                        <td>Conditions de règlements :</td>
                                        <td><textarea name="conditions_reglement"><?=$utilisateur['conditions_reglement']; ?></textarea></td>
                                    </tr>
                                    <tr>
                                        <td>Encours client HT :</td>
                                        <td><input type="text" name="encours_financier" value="<?=$utilisateur['encours_financier']; ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>Garantie :</td>
                                        <td><textarea name="garantie"><?=$utilisateur['garantie']; ?></textarea></td>
                                    </tr>
                                    <tr>
                                        <td>Livraison commande</td>
                                        <td><select name="utilisateur_livraison"><?=$utilisateur_livraison; ?></select></td>
                                    </tr>
                                    <tr>
                                        <td>Franco de port</td>
                                        <td><input type="text" name="francodeport" value="<?=$francodeport; ?>"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align: center; padding: 5px 0 0 0;">
                                             <a href="javascript:void(0);" title="Valider" onclick="document.getElementById('edit_condpro').submit();"><img src="<?php echo BACK_URL; ?>/images/icon/succes.png" alt="Valider"></a>
                                             <a href="javascript:void(0);" title="Annuler" onclick="affiche_div('show_condpro', 'edit_condpro');"><img src="<?php echo BACK_URL; ?>/images/icon/cancel.png" alt="Annuler"></a>
                                        </td>
                                   </tr>
                                </table>
                            </form>
                        </div>
                        <div class="clear"></div>
                    
                    <?php } ?>
               
                    <?php
                    if(!empty($tabUserAssoc)){
                    ?>
                    <div class="autres_contacts">
                         <center><span class="title_infos">Autre(s) contact(s)</span></center>
                    <?php
                         $i=0;
                    ?>
                    <!--Tableau Autre(s) contact(s)-->
                    <table class="table_alternate_color">

                         <tr>
                              <th class='entete tri_tableau'>Action</th>
                              <th class='entete tri_tableau'>Old</th>
                              <th class='entete tri_tableau'>Fonction</th>
                              <th class='entete tri_tableau'>Nom / Email / Tel / Portable</th>
                         </tr>

                         <?php
                         $inactif_assoc = 0;
                         foreach($tabUserAssoc as $user_assoc){
                              if($i > 0 && $i%2 == 0) echo '<div style="clear:both;"></div>';
                              $margin = '5px 15px 5px 15px';
                              
                              switch($user_assoc['utilisateur_assoc_civilite']){
                                   case 0:
                                   $civilite_assoc = 'Mr';
                                   break;
                                   case 1:
                                   $civilite_assoc = 'Mme';
                                   break;
                                   case 2:
                                   $civilite_assoc = 'Mlle';
                                   break;
                              }

                              //Permet de rajouter 1 seule fois la ligne "Anciens contacts" si présence d'utilisateurs assoc inactifs
                              if($user_assoc['actif'] == '0' && $inactif_assoc == 0){
                                   $inactif_assoc++;
                              }
                              if($inactif_assoc == 1){
                                   ?>
                                   <tr><td colspan="4" style="font-size: 14px;font-weight: bold;text-align: center;">Anciens contacts</td></tr>
                                   <?php
                                   $inactif_assoc++;
                              }

                              ?>
                              <tr id="show_user_assoc<?php echo $user_assoc['utilisateur_assoc_id']; ?>" style="margin: <?php echo $margin; ?>;">
                                   <td><a href="javascript:void(0);" onclick="valider_suppression('<?php echo $_SERVER['REQUEST_URI']; ?>&delete_assoc=<?php echo $user_assoc['utilisateur_assoc_id']; ?>');"><img class="icon_pt_droite" src="images/icon/cancel.png" alt="Annuler"></a>
                                        <img class="icon_pt_droite" src="images/icon/edit.png" onclick="affiche_div('maj_user_assoc<?php echo $user_assoc['utilisateur_assoc_id']; ?>', 'show_user_assoc<?php echo $user_assoc['utilisateur_assoc_id']; ?>');">
                                   </td>
                                   <td align='center'><input type="checkbox" <?php echo ($user_assoc['actif']=='1'?'':'checked'); ?> onclick="ancien_contact('<?php echo $_SERVER['REQUEST_URI']; ?>&old_assoc=<?php echo $user_assoc['actif']; ?>&id_assoc=<?php echo $user_assoc['utilisateur_assoc_id']; ?>')">
                                   </td>
                                   <td><?php echo !empty($user_assoc['utilisateur_assoc_fonction']) ? $user_assoc['utilisateur_assoc_fonction'].'<br>' : ''; ?>
                                   </td>
                                   <td>
                                        <strong><?php echo $civilite_assoc.' '.$user_assoc['utilisateur_assoc_prenom'].' '.$user_assoc['utilisateur_assoc_nom']; ?></strong><br><?php
                                        echo !empty($user_assoc['utilisateur_assoc_email']) ? '<a href="mailto:'.$user_assoc['utilisateur_assoc_email'].'">'.$user_assoc['utilisateur_assoc_email'].'</a><br>' : '';
                                        if(!empty($user_assoc['utilisateur_assoc_email'])){
                                             $sql="SELECT COUNT(*) FROM utilisateur_assoc WHERE utilisateur_assoc_email='".$user_assoc['utilisateur_assoc_email']."' ";
                                             $is_doublon = $DB_site2->query_first($sql);
                                             if($is_doublon[0]>1){
                                                  echo " &nbsp; <a href=\"utilisateur.php?src=".$user_assoc['utilisateur_assoc_email']."\" target=\"_blank\"><font color=\"red\"><b>!!! Email doublonné !!!</b></font></a>";
                                             }
                                        }

                                        echo !empty($user_assoc['utilisateur_assoc_tel']) ? epigy($user_assoc['utilisateur_assoc_tel'], $utilisateur['langue']) : '';
                                        echo !empty($user_assoc['utilisateur_assoc_portable']) ? epigy($user_assoc['utilisateur_assoc_portable'], $utilisateur['langue']) : ''; ?>
                                   </td>
                              </tr>

                              <tr class="edit_assoc" id="maj_user_assoc<?php echo $user_assoc['utilisateur_assoc_id']; ?>" style="display:none">
                                   <form method="post" name="form_edit_contact_assoc<?php echo $user_assoc['utilisateur_assoc_id']; ?>">
                                        <input type="hidden" name="user_assoc_id" value="<?php echo $user_assoc['utilisateur_assoc_id']; ?>">
                                        <input type="hidden" name="action" value="editer">
                                        <input type="hidden" name="edit_contact_assoc" value="1">

                                        <td>
                                             <a href="javascript:void(0);" title="Valider" onclick="document.forms.form_edit_contact_assoc<?php echo $user_assoc['utilisateur_assoc_id']; ?>.submit();"><img style="width: 15px;" src="<?php echo BACK_URL; ?>/images/icon/succes.png" alt="Valider"></a>
                                             <a href="javascript:void(0);" title="Annuler" onclick="affiche_div('show_user_assoc<?php echo $user_assoc['utilisateur_assoc_id']; ?>', 'maj_user_assoc<?php echo $user_assoc['utilisateur_assoc_id']; ?>');"><img style="width: 15px;" src="<?php echo BACK_URL; ?>/images/icon/cancel.png" alt="Annuler"></a>
                                        </td>
                                        <td></td>
                                        <td><input type="text" name="user_assoc_fonction" placeholder="Fonction" value="<?php echo !empty($user_assoc['utilisateur_assoc_fonction']) ? $user_assoc['utilisateur_assoc_fonction'] : ''; ?>"></td>
                                        <td>
                                             <select name="user_assoc_civilite">
                                                  <option value="0" <?php $user_assoc['utilisateur_assoc_civilite'] == 0 ? 'selected' : ''; ?>>Monsieur</option>
                                                  <option value="1" <?php $user_assoc['utilisateur_assoc_civilite'] == 1 ? 'selected' : ''; ?>>Madame</option>
                                             </select>
                                             <input type="text" name="user_assoc_prenom" placeholder="Prénom" style="width: 210px;" value="<?php echo !empty($user_assoc['utilisateur_assoc_prenom']) ? $user_assoc['utilisateur_assoc_prenom'] : ''; ?>">
                                             <input type="text" name="user_assoc_nom" placeholder="Nom" style="width: 210px;" value="<?php echo !empty($user_assoc['utilisateur_assoc_nom']) ? $user_assoc['utilisateur_assoc_nom'] : ''; ?>">
                                             <input type="text" name="user_assoc_mail" placeholder="Email" style="width: 210px;" value="<?php echo !empty($user_assoc['utilisateur_assoc_email']) ? $user_assoc['utilisateur_assoc_email'] : ''; ?>">
                                             <input type="text" name="user_assoc_tel" placeholder="Téléphone" style="width: 210px;" value="<?php echo !empty($user_assoc['utilisateur_assoc_tel']) ? $user_assoc['utilisateur_assoc_tel'] : ''; ?>">
                                             <input type="text" name="user_assoc_portable" placeholder="Portable" style="width: 210px;" value="<?php echo !empty($user_assoc['utilisateur_assoc_portable']) ? $user_assoc['utilisateur_assoc_portable'] : ''; ?>">
                                        </td>
                                   </form>
                              </tr>        
                    <?php
                         $i++;
                         }
                    ?>
                    </table>
                    </div>
                    <?php
                    }
                    ?>
                    <div class="clear"></div>
                    <a title="Nouveau contact" href="javascript:void(0)" class="nouveau_contact" onclick="document.getElementById('add_contact').style.display='block'"><img alt="Nouveau contact" src="images/icon/add_user.png" class="icon" style="margin: 10px 5px 0 0; padding: 0;">Nouveau contact</a>
                    
                    <div id="add_contact" class="infos_client" style="display:none; width: 370px;">
                         <center><span class="title_infos">Nouveau contact</span></center>
                         <form method="post" name="form_add_contact" style="padding: 0; margin: 0;">
                              <input type="hidden" name="userid" value="<?php echo $utilisateur['userid']; ?>">
                              <input type="hidden" name="action" value="editer">
                              <input type="hidden" name="addcontact" value="1">
                              <table class="edit">
                                   <tr>
                                        <td>
                                             Fonction :
                                        </td>
                                        <td>
                                             <input type="text" name="user_assoc_fonction">
                                        </td>
                                   </tr>
                                   <tr>
                                        <td style="width:70px;">
                                             Civilité :
                                        </td>
                                        <td>
                                             <select name="user_assoc_civilite">
                                                  <option value="0">Monsieur</option>
                                                  <option value="1">Madame</option>
                                             </select>
                                        </td>
                                   </tr>
                                   <tr>
                                        <td>
                                             Prénom :
                                        </td>
                                        <td>
                                             <input type="text" name="user_assoc_prenom">
                                        </td>
                                   </tr>
                                   <tr>
                                        <td>
                                             Nom :
                                        </td>
                                        <td>
                                             <input type="text" name="user_assoc_nom">
                                        </td>
                                   </tr>
                                   <tr>
                                        <td>
                                             Téléphone fixe :
                                        </td>
                                        <td>
                                             <input type="text" name="user_assoc_tel">
                                        </td>
                                   </tr>
                                   <tr>
                                        <td>
                                             Téléphone portable :
                                        </td>
                                        <td>
                                             <input type="text" name="user_assoc_portable">
                                        </td>
                                   </tr>
                                   <tr>
                                        <td>
                                             Email :
                                        </td>
                                        <td>
                                             <input type="text" name="user_assoc_mail">
                                        </td>
                                   </tr>
                                   <tr>
                                        <td colspan="2" style="text-align: center; padding: 5px 0 0 0;">
                                             <a href="javascript:void(0);" title="Valider" onclick="document.forms.form_add_contact.submit();"><img src="<?php echo BACK_URL; ?>/images/icon/succes.png" alt="Valider"></a>
                                             <a href="javascript:void(0);" title="Annuler" onclick="affiche_div('show_adresse', 'add_contact');"><img src="<?php echo BACK_URL; ?>/images/icon/cancel.png" alt="Annuler"></a>
                                        </td>
                                   </tr>
                              </table>
                         </form>
                    </div>
                    <div class="clear"></div>
                    
                    <!--<div class="besoins">
                        <center><span class="title_infos">Besoin(s)</span></center>
                        <br />
                        Sélectionnez un type d'article correspondant au besoin du client :<br />
                        <p>
                            <strong>Cave</strong><a href="javascript:besoin(1,'area_besoin');" title="Cave"><img src="<?php echo BACK_URL; ?>/images/icon/edit_add.png" alt="Cave" align="absmiddle"></a> | 
                            <strong>Cave sur mesure</strong><a href="javascript:besoin(4,'area_besoin');" title="Cave sur mesure"><img src="<?php echo BACK_URL; ?>/images/icon/edit_add.png" alt="Cave sur mesure" align="absmiddle"></a> | 
                            <strong>Climatiseur</strong><a href="javascript:besoin(2,'area_besoin');" title="Climatiseur"><img src="<?php echo BACK_URL; ?>/images/icon/edit_add.png" alt="Climatiseur" align="absmiddle"></a> | 
                            <strong>Autre(s)</strong><a href="javascript:besoin(3,'area_besoin');" title="Autre(s)"><img src="<?php echo BACK_URL; ?>/images/icon/edit_add.png" alt="Autre(s)" align="absmiddle"></a>
                        </p>
                        <form name="formbesoin" method="post">
                            <input type="hidden" name="userid" value="<?php echo $utilisateur['userid']; ?>">
                            <input type="hidden" name="action" value="editer">
                            <textarea id="area_besoin" style="<?php echo empty($utilisateur['besoin']) ? 'display:none;' : 'display:block; background-color:white; border:solid thin black;'; ?>" name="besoin" rows="<?php echo (4 + substr_count($utilisateur['besoin'],"\n")); ?>" onclick="this.style.border='solid thin black'; this.style.backgroundColor='white'; document.getElementById('updbesoin').style.display='block';"><?php echo $utilisateur['besoin']; ?></textarea>
                            <input type="submit" id="updbesoin" value="Mettre à jour">
                        </form>
                        
                    </div>-->
					<div class="clear"></div>
					<?php
					include("includes/utilisateur_fiche_relation.php");
					?>
               </td>
          </tr>
     </table>
</div>
<div class="clear"></div>
