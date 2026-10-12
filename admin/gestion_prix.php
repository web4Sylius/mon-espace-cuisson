<script language="javascript" src="./calendrier.js"></script>
<script language="javascript">
function calctxtva(artid){
     var tauxtva = parseFloat(document.getElementsByName('tvapc_'+artid)[0].value);
     document.getElementsByName('prixht_'+artid)[0].value = round(parseFloat(document.getElementsByName('prix_'+artid)[0].value/(1+tauxtva/100)),2);
     document.getElementsByName('prixconseilleht_'+artid)[0].value = round(parseFloat(document.getElementsByName('prixconseille_'+artid)[0].value/(1+tauxtva/100)),2);
}

function changeorder(order,sens){
    document.getElementsByName('order')[0].value=order;
    document.getElementsByName('sens')[0].value=sens;
    document.forms.frmTri.submit();
}

function calcmarge(artid){
     var change_fournisseur = parseFloat(document.getElementById('change_fournisseur').value);
     var change_site = parseFloat(document.getElementById('change_site').value);
     var tauxtva = parseFloat(document.getElementsByName('tvapc_'+artid)[0].value);
     var achatport = parseFloat(document.getElementsByName('achatport_'+artid)[0].value);
     var garantie = parseFloat(document.getElementsByName('garantie_'+artid)[0].value);
     var prixport = parseFloat(document.getElementsByName('prixport_'+artid)[0].value);
     var paht = parseFloat(document.getElementsByName('paht_'+artid)[0].value/change_fournisseur);
     var ecopartht = parseFloat(document.getElementsByName('ecopart_'+artid)[0].value/(1+tauxtva/100));
     ecopartht=ecopartht/change_fournisseur;
     var prixtrspt = parseFloat(document.getElementsByName('prixtrspt_'+artid)[0].value/change_fournisseur);
     var prix = parseFloat(document.getElementsByName('prix_'+artid)[0].value/(1+tauxtva/100));
     prix=prix/change_site;
     
     var totalachat=achatport+garantie+paht+ecopartht+prixtrspt;
     var totalvente=prixport+prix;
     var marge=Math.round(1000*(totalvente-totalachat))/1000;
     var pcmarge=Math.round(10000*marge/totalvente)/100;
     document.getElementById('marge_'+artid).innerHTML=marge+'&euro;';
     document.getElementById('margepc_'+artid).innerHTML=pcmarge+'%';
     var color="";
     if(pcmarge<20) color="red";
     document.getElementById('marge_'+artid).style.color=color;
     document.getElementById('margepc_'+artid).style.color=color;
}

function nbonly(event,decimal,input) {
    // Compatibilité IE / Firefox
    if(!event && window.event) {
      event=window.event;
    }

    if(decimal==undefined) decimal=false;

    // IE
    if((event.keyCode < 48 || event.keyCode > 57) && event.keyCode!=8) {
    	if(!decimal || event.keyCode!=46 || input.value.indexOf('.')>0){
    	    event.returnValue = false;
    	    event.cancelBubble = true;
    	}
    }

    // DOM
    if(event.which!=0 && (event.which < 48 || event.which > 57) && event.which!=8) {
      if(!decimal || event.which!=46 || input.value.indexOf('.')>0){
	    event.preventDefault();
	    event.stopPropagation();
      }
    }
}

function veriftri(){
    var s=document.getElementsByName('triFournisseur')[0];

    if(s.options[s.selectedIndex].value==""){
    	alert("Il faut choisir un fournisseur");
    	s.style.borderColor='red';
    	return false;
        }
    
    return true;
}
</script>
<?php

require_once("admin_global.php"); 
include_once("header.php");
include_once("menu.php");
require_once("../class/DAOarticle.class.php");
require_once("../class/garantie.class.php");
require_once("../class/DAOgarantie.class.php");
include_once("../class/frais_port.php");

$visibletxt=array(1=>"<img src=\"images/icon/green.png\">",0=>"<img src=\"images/icon/orange.png\">");

$site_code="base";
if(isset($_POST['site_code'])) $site_code=$_POST['site_code'];

$order="artcode_eurocave";
if(isset($_POST['order'])) $order=$_POST['order'];
$sens="asc";
if(isset($_POST['sens'])) $sens=$_POST['sens'];
if(isset($_POST['actif'])) $actif=$_POST['actif'];
$articles=$articlestri=array();

/*
champs communs:
AF             A
prix          paht
trspt         prixtrspt
delai         delailivraison
reappro       date_appro
*/

$img_asc = "<img src=\"images/monter.gif\">";
$img_desc = "<img src=\"images/descendre.gif\">";

$change=$devises->getchange();
$change['EUR']=1;



//Récupération du taux de TVA pour la France
// $txfrance = $class_article->getTVA();
$taux=$DB_site2->query_first("SELECT 0,TVAtauxnormal,TVAtauxreduit FROM pays WHERE paysid=".$sites[$site_code]['paysid_ref']);
//Mise à jour des montants
if (isset($_POST['Modifier'])){
     foreach ($_POST['idArt'] as $idArt){
          $tauxtva = $_POST['tauxTva_'.$idArt];
          $tvapc = $taux[$tauxtva];
                
                //Récupération des informations avant mise à jour
            //           $tabArticle = $class_article->getDetail($idArt);
      	  $tabArticle=$DB_site2->query_first("SELECT prix AS paht, trspt AS prixtrspt, ecopartht,delai,reappro FROM article_fournisseur WHERE artid=".$idArt." AND fournisseur='".$_POST['triFournisseur']."'");

      	  //Mise à jour des informations produit
                
                $date_appro=strtots($_POST['date_appro_'.$idArt]);
                $delailivraison=intval($_POST['delailivraison_'.$idArt]);
                $paht=floatval($_POST['paht_'.$idArt]);
                $ecopartartht=round($_POST['ecopart_art_'.$idArt],3);
                $ecopartart=round($_POST['ecopart_art_'.$idArt]*(1+($tvapc/100)),0);
                $ecopartfournht=round($_POST['ecopart_fourn_'.$idArt],3);
                $ecopartfourn=round($_POST['ecopart_fourn_'.$idArt]*(1+($tvapc/100)),0);
                $prixtrspt=floatval($_POST['prixtrspt_'.$idArt]);
                $DB_site2->query("UPDATE article_fournisseur SET prix=".$paht.", trspt=".$prixtrspt.", ecopartht=".$ecopartfournht.", ecopart=".$ecopartfourn.", delai=".$delailivraison.", reappro=".$date_appro."  WHERE artid=".$idArt." AND fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$_POST['triFournisseur'])."'");
                
                $prix=floatval($_POST['prix_'.$idArt]);
                $prixconseilleht=round($_POST['prixconseille_'.$idArt]/(1+($tvapc/100)),3);

                $avant=$DB_site2->query_first("SELECT * FROM article_site WHERE artid=".$idArt." AND site_code='".$site_code."'");
                $DB_site2->query("UPDATE article_site SET prix=".$prix.", prixconseilleht=".$prixconseilleht." WHERE artid=".$idArt." AND site_code='".$site_code."'");
                
                $avant2 = $DB_site2->query_first("SELECT ecopartht FROM article WHERE artid=$idArt");
                $tabArticle['ecopartartht'] = $avant2[0];
                if($site_code=="base") $DB_site2->query("UPDATE article SET prix=".$prix.", prixconseilleht=".$prixconseilleht.", ecopartht=".$ecopartartht.", ecopart=".$ecopartart.", delailivraison=".$delailivraison." WHERE artid=".$idArt);
                
                //Comparaison avant/après pour insertion dans suivi
                $tabMaj = array();
                if(round($avant['prix']) != round($prix)){
                     $tabMaj['prix'] = "(".$avant['prix']." => ".$prix.")";
                }
                if(round($avant['prixconseilleht']) != round($prixconseilleht)){
                     $tabMaj['prixconseilleht'] = "(".$avant['prixconseilleht']." => ".$prixconseilleht.")";
                }
      	        if($tabArticle['date_appro'] != $date_appro){
                     $tabMaj['date_appro'] = "(".$tabArticle['date_appro']." => ".$date_appro.")";
                }
                if($tabArticle['delai'] != $delailivraison){
                     $tabMaj['delai'] = "(".$tabArticle['delai']." => ".$delailivraison.")";
                }
                if($tabArticle['paht'] != $paht){
                     $tabMaj['paht'] = "(".$tabArticle['paht']." => ".$paht.")";
                }
                if($tabArticle['ecopartht'] != $ecopartfournht){
                     $tabMaj['ecopartht'] = "(".$tabArticle['ecopartht']." => ".$ecopartfournht.")";
                }
                if($tabArticle['ecopartartht'] != $ecopartartht){
                     $tabMaj['ecopartartht'] = "(".$tabArticle['ecopartartht']." => ".$ecopartartht.")";
                }
                if($tabArticle['prixtrspt'] != $prixtrspt){
                     $tabMaj['trspt'] = "(".$tabArticle['prixtrspt']." => ".$prixtrspt.")";
                }
                
                //Ajout des informations dans le suivi
                if(!empty($tabMaj)){
                     foreach($tabMaj as $key => $value){
      		    $sql = "INSERT INTO article_history (";
                          $sql .= "`artid`";
                          $sql .= ", `utilisateur`";
                          $sql .= ", `date`";
                          $sql .= ", `key`";
                          $sql .= ", `value`";
                          $sql .= ") VALUES (";
                          $sql .= $idArt;
                          $sql .= ", '".$user_info['username']."'";
                          $sql .= ", ".time();
                          $sql .= ", '".$key."'";
                          $sql .= ", '".mysqli_real_escape_string($DB_site2->link_id,$value)."'";
                          $sql .= ")";
                          $DB_site2->query($sql);
                     }
                }
     }
     $scrolltop="<script language=\"javascript\">
     window.addEventListener('load',function(){
     document.body.scrollTop=".intval($_POST['scrolltop']).";
     });
     </script>";
}
if (isset($_POST['Modifier']) || isset($_POST['Valider']) || isset($_POST['triFournisseur'])) {
     if(isset($_POST['actif_string'])) {
          $actif = explode(",", $_POST['actif_string']);
     }

    $sql="SELECT a.artid,a.artcode,a.artcode_eurocave,a.delailivraison,a_s.prix,a_s.prixconseilleht,a_s.tauxchoisi,a_s.active,a.date_appro,af.fournisseur,af.principal,af.prix AS paht,af.trspt,af.ecopartht as ecopartfournht,a.ecopart AS ecopartart,a.ecopartht AS ecopartartht,af.ecopart AS ecopartfourn,af.devise,af.delai
    FROM article a INNER JOIN article_fournisseur af USING(artid) INNER JOIN article_marque am USING(artid) INNER JOIN article_site a_s USING(artid) ".$lot1."
    WHERE a_s.site_code='".$site_code."'";
    if (isset($triMarque) && !empty($triMarque)) $sql.=" AND am.marqueid=".$triMarque;
    if (isset($triFournisseur) && !empty($triFournisseur)) $sql.=" AND af.fournisseur='".mysqli_real_escape_string($DB_site2->link_id,stripslashes($triFournisseur))."'";
    if (isset($triType) && !empty($triType)) $sql.=" AND a.typearticleid=".$triType;
    if (!empty($actif) || $actif=='0') $sql.=" AND a_s.active IN ('".implode("','",$actif)."')";
    if (isset($_POST['voirlots'])) $sql .= " AND af.principal = ''";
    if (empty($_POST['voirlots'])) $sql .= " AND af.principal != ''";
    $req=$DB_site2->query($sql);
    while($a=$DB_site2->fetch_array($req)) $articles[$a['artid']]=$a;
}

//Application du tri sur les critères de marge
foreach($articles as $cle => $valeur){
     //Récupération du montant des frais de port pour le produit en cours
     $change_fournisseur=$change[$valeur['devise']];
     $paht_eur=$valeur['paht']/$change[$valeur['devise']];
     $prixtrspt_eur=$valeur['prixtrspt']/$change[$valeur['devise']];
     $ecopartht_eur=$valeur['ecopartfournht']/$change[$valeur['devise']];
     $frais_port=new frais_port($DB_site2,array($valeur['artid']=>1),$sites[$site_code]['paysid_ref'],$valeur['prix'],$paht_eur);
          
     //Récupération de la liste des garanties associés au produit, pour la France
     $garantie_eur = 0;
     $lesGaranties = DAOgarantie::loadMaxGarantieArtByPays($DB_site2,$site_code,'FR',$sites[$site_code]['paysid_ref'],$valeur['artid']);
          
     //Calcul du prix d'achat total (garantie + extension)
     $i=0;
     foreach ($lesGaranties as $laGarantie) {
          $i++;
          if ($i==1 && $change[$laGarantie->devise] !=0) {
               $garantie_eur = $laGarantie->paht/$change[$laGarantie->devise];
          }
          if ($laGarantie->cumul == 1 && $change[$laGarantie->devise] !=0) {
               $garantie_eur += $laGarantie->paht/$change[$laGarantie->devise];
          }
     }
     
     //Construction de la case "Pack / Lot"
     $valeur['pack_lot']="";
     $lots=$DB_site2->query("SELECT a.artid,a.artcode FROM article_lot al INNER JOIN article a ON(a.artid=al.lotid) WHERE al.artid=".$valeur['artid']);
     while($lot=$DB_site2->fetch_array($lots)) $valeur['pack_lot'].="<a href=\"ajout_produit.php?artid=".$lot['artid']."&prec=modif\">".$lot['artcode']."</a><br>";
          
     //Calculs divers pour prix, marges...
      $tauxtva = $valeur['tauxchoisi'];
     $tvapc=$taux[$tauxtva];
     
     $valeur['prixht']=$valeur['prix']/(1+$tvapc/100);
     $prix_eur=$valeur['prixht']/$change[$sites[$site_code]['devise']];

     $valeur['margeHT'] = ($prix_eur+$frais_port->prixht)-($paht_eur+$prixtrspt_eur+$ecopartht_eur+$garantie+$frais_port->paht);
     $valeur['margePC'] = round(($valeur['margeHT']*100)/($prix_eur+$frais_port->prixht),2);
     $valeur['date_appro'] = ($valeur['date_appro']>0?date("d/m/Y",$valeur['date_appro']):"");
     $principal=explode(",",$valeur['principal']);
     $valeur['principal']=in_array($site_code,$principal);
     $valeur['prixConseilleHT'] = round($valeur['prixconseilleht']*(1+($tvapc/100)),2);
     //$valeur['ecopart']=$valeur['ecopartht']*(1+$tvapc/100);
     $valeur['tvapc']=$tvapc;
     $valeur['garantie']=$garantie_eur;
     $valeur['achatport']=$frais_port->paht;
     $valeur['prixport']=$frais_port->prixht;
     $lot=$DB_site2->query_first("SELECT COUNT(*) FROM article_lot WHERE lotid=".$valeur['artid']);
     $valeur['lot']=($lot[0]>0);
     
     $cle=$valeur[$order];
     $articlestri[$cle][]=$valeur;
    
}


//Mise en place filtre "Marque"
$optmarques .= "<option value=\"\">Toutes les marques</option>";
$r = $DB_site2->query("SELECT marqueid,libelle FROM marque ORDER BY libelle");
while($marques=$DB_site2->fetch_array($r)){
     $selected="";
     if (isset($triMarque) && !empty($triMarque) && $marques['marqueid'] == $triMarque) {
          $selected = "selected";
     }
     $optmarques.="<option value=\"".$marques['marqueid']."\" $selected>".$marques['libelle']."</option>";
}

//Mise en place filtre "Fournisseur"
$optfournisseur.="<option value=\"\">-- Sélectionner --</option>";
$r=$DB_site2->query("SELECT DISTINCT(societe) FROM fournisseur WHERE type=\"fournadv\" ORDER BY societe;");
while($fournisseur=$DB_site2->fetch_array($r)){
     $selected="";
     if (isset($triFournisseur) && !empty($triFournisseur) && $fournisseur['societe'] == $triFournisseur) {
               $selected = "selected";
     }
     $optfournisseur.="<option value=\"".$fournisseur['societe']."\" $selected>".$fournisseur['societe']."</option>";
}

//Mise en place filtre "Type"
$opttype.="<option value=\"\">Tous les types</option>";
$r=$DB_site2->query("SELECT typearticleid,libelle FROM typearticle ORDER BY libelle;");
while($type=$DB_site2->fetch_array($r)){
     $selected="";
     if (isset($triType) && !empty($triType) && $type['typearticleid'] == $triType) {
               $selected = "selected";
     }
     $opttype.="<option value=\"".$type['typearticleid']."\" $selected>".$type['libelle']."</option>";
}

foreach($sites as $code=>$site)
   $selectsite.="<option value=\"".$code."\"".($site_code==$code?" selected":"").">".$code."</option>";

$voirlots=isset($_POST['voirlots']);
$chklot=($voirlots?" checked":"");


    $optactif="
    <table class=\"form_rech_article\">
          <tr>
          <td></td>
          <td></td>
          <td><img src=\"images/en_cours.png\" title=\"En cours de création sur site $site_code\" width=\"30\"></td>
          <td><img src=\"images/new.png\" title=\"Nouveau à contrôler\" width=\"30\"></td>
          <td><img src=\"images/visible.png\" title=\"Visible sur site $site_code\" width=\"30\"></td>
          <td><img src=\"images/invisible.png\" title=\"Invisible sur site $site_code\" width=\"30\"></td>
          <td><img src=\"images/supprime.png\" title=\"Supprimé sur site $site_code\" width=\"30\"></td>
          <td><img src=\"images/archive_temp.png\" title=\"Archivé temporairement sur site $site_code\" width=\"30\"></td>
          <td><img src=\"images/archive.png\" title=\"Archivé définitivement sur site $site_code\" width=\"30\"></td>
          </tr><tr>
          <td>Etat:</td>
          <td><input type=\"checkbox\" name=\"actif[]\" value=\"\" onclick=\"tout_cocher()\"></td>
           <td><input type=\"checkbox\" name=\"actif[]\" value=\"6\"".(isset($actif)?(in_array(6,$actif)?" checked":""):"")."></td>
          <td><input type=\"checkbox\" name=\"actif[]\" value=\"4\"".(isset($actif)?(in_array(4,$actif)?" checked":""):" checked")."></td>
          <td><input type=\"checkbox\" name=\"actif[]\" value=\"1\"".(isset($actif)?(in_array(1,$actif)?" checked":""):" checked")."></td>
          <td><input type=\"checkbox\" name=\"actif[]\" value=\"0\"".(isset($actif)?(in_array(0,$actif)?" checked":""):" checked")."></td>
          <td><input type=\"checkbox\" name=\"actif[]\" value=\"2\"".(isset($actif)?(in_array(2,$actif)?" checked":""):"")."></td>
          <td><input type=\"checkbox\" name=\"actif[]\" value=\"5\"".(isset($actif)?(in_array(5,$actif)?" checked":""):" checked")."></td>
          <td><input type=\"checkbox\" name=\"actif[]\" value=\"3\"".(isset($actif)?(in_array(3,$actif)?" checked":""):"")."></td>
          </tr><tr>
          <td></td>
          <td style=\"font-weight:normal;\">Tout<br>cocher </td>
          <td style=\"font-weight:normal;\">En<br>création</td>
          <td style=\"font-weight:normal;\">A<br>contrôler</td>
          <td style=\"font-weight:normal;\">Actifs</td>
          <td style=\"font-weight:normal;\">Actifs<br>invisibles</td>
          <td style=\"font-weight:normal;\">Supprimés</td>
          <td style=\"font-weight:normal;\">Archivés<br>temporairement</td>
          <td style=\"font-weight:normal;\">Archivés<br>définitivement</td>
          </tr></table>

          <script language=\"javascript\">
               function tout_cocher() {
                    const inputs = frmTri.querySelectorAll('input[name=\"actif[]\"]');
                    const nb_check = inputs.length;
                    const nb_check2 = frmTri.querySelectorAll('input[name=\"actif[]\"]:checked').length;
                    if((nb_check - 1) ===  nb_check2) {
                         inputs.forEach((input) => {
                              input.checked = false;
                         });
                    }
                    else {
                         inputs.forEach((input) => {
                              input.checked = true;
                         });
                    }
               }
          </script>";
?>


<h1>Gestion des prix</h1>
<form name="frmTri" method="post" action="" onsubmit="return veriftri()" enctype="multipart/form-data">
    <input type="hidden" name="order" value="<?=$order; ?>">
    <input type="hidden" name="sens" value="<?=$sens; ?>">
     <table class="tableau_filtres" border="0" cellpadding="0" cellspacing="0">
          <tr>
	      <td>Site :</td>
	      <td>Fournisseur :</td>
              <td>Marque :</td>
              <td>Type de produit :</td>
          </tr>
          <tr>
	      <td><select name="site_code"><?=$selectsite; ?></select></td>
              <td><select name="triFournisseur"><?php echo $optfournisseur; ?></select></td>
	      <td><select name="triMarque"><?php echo $optmarques; ?></select></td>
	      <td><select name="triType"><?php echo $opttype; ?></select></td>
           </tr><tr><td colspan="3"><?php echo $optactif; ?></td>
	      <td><input type="checkbox" name="voirlots" value="1"<?=$chklot; ?>> Voir les lots uniquement</td>
	      <td><input type="submit" value="OK" name="Valider" /></td>
          </tr>
     </table>
</form>

<?php

$site_devise_symbole=$devises->symbole($sites[$site_code]['devise']);

$newsens="asc";
if($sens=="asc") $newsens="desc";

if (count($articles)>0) {

     $nbResultats = count($articles);

     echo "<p>Achat en ".$valeur['devise']." => Vente en ".$sites[$site_code]['devise']."</p>";

     echo "<form name=\"frmUpdArt\" method=\"post\" action=\"#\" onsubmit=\"document.getElementsByName('scrolltop')[0].value=document.body.scrollTop;\">
	  <input type=\"hidden\" id=\"change_fournisseur\" value=\"".$change_fournisseur."\">
	  <input type=\"hidden\" id=\"change_site\" value=\"".$change[$sites[$site_code]['devise']]."\">
          <input type=\"hidden\" name=\"Modifier\" value=\"1\">
          <input type=\"hidden\" name=\"scrolltop\" value=\"0\">
          <table class=\"tableUpdArt table_alternate_color\" width=\"1100px\" border=1>
               <tr>
                    <td class=\"entete\" colspan=\"18\">Liste des ".$nbResultats." article(s)</td>
               </tr>
               <tr>
                    <input type=\"hidden\" name=\"actif_string\" value=\"".implode(",",$actif)."\"/>
                    <input type=\"hidden\" name=\"triMarque\" value=\"".$triMarque."\"/>
                    <input type=\"hidden\" name=\"triFournisseur\" value=\"".stripslashes($triFournisseur)."\"/>
                    <input type=\"hidden\" name=\"triType\" value=\"".$triType."\"/>
                    <td align=\"center\" class=\"entete2\">Enreg.</td>
                    <td class=\"entete2\"><a href=\"javascript:changeorder('artcode_eurocave','".($order=="artcode_eurocave"?$newsens:$sens)."')\">Réf.<br />fabricant</a> ".($order=="artcode_eurocave"?${"img_".$sens}:"")."</td>
                    <td class=\"entete2\" align=\"center\">Fourn<br>princip</td>
                    <td align=\"center\" class=\"entete2\"><a href=\"javascript:changeorder('artcode','".($order=="artcode"?$newsens:$sens)."')\">Réf.<br />Acilyon</a> ".($order=="artcode"?${"img_".$sens}:"")."</td>
                    <td width=\"80\" align=\"center\" class=\"entete2\"><a href=\"javascript:changeorder('active','".($order=="active"?$newsens:$sens)."')\">Etat</a> ".($order=="active"?${"img_".$sens}:"")."</td>
                    <td align=\"center\" class=\"entete2\">Date<br />modif.</td>
                    <td width=\"70\" align=\"center\" class=\"entete2\"><a href=\"javascript:changeorder('paht','".($order=="paht"?$newsens:$sens)."')\">Px achat<br />HT</a> ".($order=="paht"?${"img_".$sens}:"")."</td>
                    <td width=\"70\" align=\"center\" class=\"entete2\"><a href=\"javascript:changeorder('prix','".($order=="prix"?$newsens:$sens)."')\">Px vente<br />TTC</a> ".($order=="prix"?${"img_".$sens}:"")."</td>
                    <td width=\"70\" align=\"center\" class=\"entete2\">Px vente HT</td>
                    <td width=\"70\" align=\"center\" class=\"entete2\">PPI TTC</td>
                    <td width=\"70\" align=\"center\" class=\"entete2\">PPI HT</td>
                    <td width=\"70\" align=\"center\" class=\"entete2\">Transp.<br />Embal.</td>
                    <td align=\"center\" class=\"entete2\">Delai livr.</td>
                    <td align=\"center\" class=\"entete2\">Date réappro</td>
                    <td align=\"center\" class=\"entete2\">Eco-taxe HT fournisseur</td>
                    <td align=\"center\" class=\"entete2\">Eco-taxe HT produit site</td>
                    <td align=\"center\" class=\"entete2\"><a href=\"javascript:changeorder('margeHT','".($order=="margeHT"?$newsens:$sens)."')\">Marge HT</a> ".($order=="margeHT"?${"img_".$sens}:"")."</td>
                    <td align=\"center\" class=\"entete2\"><a href=\"javascript:changeorder('margePC','".($order=="margePC"?$newsens:$sens)."')\">Marge %</a> ".($order=="margePC"?${"img_".$sens}:"")."</td>
                    <td align=\"center\" class=\"entete2\">Lot</td>
               </tr>";
     
     //Boucle sur tous les articles, après application des filtres et du tri
     $cnt = 1;
     if($sens=="desc")
	krsort($articlestri);
     else
	ksort($articlestri);
     foreach ($articlestri as $key => $articleskey) {
	  foreach($articleskey as $article){
	      if(!$article['lot'] || $voirlots){
	      
		  //Mise en place picto "état"
		  $picto_etat = "<img src=\"images/icon/%color%.png\">";
		  switch($article['active']){
		      case 0:
			    $picto_etat = "<img src=\"images/invisible.png\" title=\"Invisible sur site $site_code\" width=\"30\">";
			    break;
		      case 1:
			    $picto_etat = "<img src=\"images/visible.png\" title=\"Visible sur site $site_code\" width=\"30\">";
			    break;
		      case 2:
			    $picto_etat = "<img src=\"images/supprime.png\" title=\"Supprimé sur site $site_code\" width=\"30\">";
			    break;
		      case 3:
			    $picto_etat = "<img src=\"images/archive.png\" title=\"Archivé définitivement sur site $site_code\" width=\"30\">";
			    break;
                case 4:
                   $picto_etat = "<img src=\"images/new.png\" title=\"Nouveau à contrôler\" width=\"30\">";
                   break;
                case 5:
                   $picto_etat = "<img src=\"images/archive_temp.png\" title=\"Archivé temporairement sur site $site_code\" width=\"30\">";
                   break;
                case 6:
                   $picto_etat = "<img src=\"images/en_cours.png\" title=\"En cours de création sur site $site_code\" width=\"30\">";
                   break;
		  }
		  $couleur_alt = $cnt%2 == 0 ? '1' : '2';
		  $cnt++;
		  
		  $sql = "SELECT MAX(date)";
		  $sql .= " FROM article_history";
		  $sql .= " WHERE artid = ".$article['artid'];
		  $sql .= " AND (`key` = 'paht' OR `key` = 'ecopartht' OR `key` = 'prix' OR `key` = 'prixconseilleht' OR `key` = 'ecopartartht')";

		  $bg="";
		  $h = $DB_site2->query_first($sql);
		  if($h[0]>0){
		    $maxDate=date('d/m/Y h:i',$h[0]);
		    if($h[0]>time()-2592000) $bg="#BFFFDB"; // 2592000 = 30j, demandé par alexis tâche 6781
		  }else
		    $maxDate="";
		  if(isset($_POST['voirlots'])) $disabled = 'disabled';

		  echo "<tr class=\"td_alt".$couleur_alt."\" style=\"background-color:$bg;\" align=\"left\">
			    <input type=\"hidden\" name=\"idArt[]\" value=\"".$article['artid']."\"/>
			    <input type=\"hidden\" name=\"tauxTva_".$article['artid']."\" value=\"".$article['tauxchoisi']."\"/>
			    <input type=\"hidden\" name=\"tvapc_".$article['artid']."\" value=\"".$article['tvapc']."\">
			    <input type=\"hidden\" name=\"garantie_".$article['artid']."\" value=\"".$article['garantie']."\">
			    <input type=\"hidden\" name=\"achatport_".$article['artid']."\" value=\"".$article['achatport']."\">
			    <input type=\"hidden\" name=\"prixport_".$article['artid']."\" value=\"".$article['prixport']."\">
			    <td><input  type=\"image\" border=\"0\" src=\"images/icon/save.png\" style=\"width:22px; height:22px;\" title=\"Enregistrer\" alt=\"Enregistrer\" onclick=\"document.forms.frmUpdArt.submit();\" ".$disabled."></td>
			    <td style=\"".($article['principal']?"font-weight:bold;":"")."white-space:pre-wrap;\">".str_replace('/','<br />',$article['artcode_eurocave'])."</td>
			    <td>".($article['principal']?"&#x274c;":"")."</td>
			    <td><a href=\"./ajout_produit.php?artid=".$article['artid']."&prec=modif\" target=\"_blank\"/>".$article['artcode']."</a></td>
			    <td>".$picto_etat."</td>
			    <td>". $maxDate."</td>
			    <td><input type=\"text\" name=\"paht_".$article['artid']."\" value=\"".$article['paht']."\" onkeypress=\"nbonly(event,true,this);\" onkeyup=\"calcmarge('".$article['artid']."');\" ".$disabled.">".$devises->symbole($article['devise'])."</td>
			    <td><input type=\"text\" name=\"prix_".$article['artid']."\" value=\"".$article['prix']."\" onkeypress=\"nbonly(event,true,this);\" onkeyup=\"calctxtva('".$article['artid']."');calcmarge('".$article['artid']."');\" ".$disabled.">".$site_devise_symbole."</td>
                    <td><input type=\"text\" name=\"prixht_".$article['artid']."\" value=\"".round($article['prixht'],2)."\" style=\"background-color:transparent;border:none;\" readonly>".$site_devise_symbole."</td>
			    <td><input type=\"text\" name=\"prixconseille_".$article['artid']."\" value=\"".$article['prixConseilleHT']."\" onkeypress=\"nbonly(event,true,this);\" onkeyup=\"calctxtva('".$article['artid']."');\" ".$disabled.">".$site_devise_symbole."</td>
                   <td><input type=\"text\" name=\"prixconseilleht_".$article['artid']."\" value=\"".round($article['prixconseilleht'],2)."\" style=\"background-color:transparent;border:none;\" readonly>".$site_devise_symbole."</td>
			    <td><input type=\"text\" name=\"prixtrspt_".$article['artid']."\" value=\"".$article['trspt']."\" onkeypress=\"nbonly(event,true,this);\" onkeyup=\"calcmarge('".$article['artid']."');\" ".$disabled.">".$devises->symbole($article['devise'])."</td>
			    <td><input type=\"text\" name=\"delailivraison_".$article['artid']."\" class=\"txt_pt\" value=\"".$article['delai']."\" onkeypress=\"nbonly(event,true,this);\" size=\"8\" ".$disabled."></td>
			    <td><input type=\"text\" name=\"date_appro_".$article['artid']."\" id=\"appro_".$article['artid']."\" value=\"".$article['date_appro']."\" onclick=\"calendrier_show(this.id,this.value,undefined);\" readonly ".$disabled."></td>
			    <td><input type=\"text\" name=\"ecopart_fourn_".$article['artid']."\" class=\"txt_pt\" value=\"".$article['ecopartfournht']."\" onkeypress=\"nbonly(event,true,this);\" onkeyup=\"calcmarge('".$article['artid']."');\" ".$disabled.">".$devises->symbole($article['devise'])."</td>
          <td><input type=\"text\" name=\"ecopart_art_".$article['artid']."\" class=\"txt_pt\" value=\"".$article['ecopartartht']."\" onkeypress=\"nbonly(event,true,this);\" onkeyup=\"calcmarge('".$article['artid']."');\" ".$disabled.">".$devises->symbole($article['devise'])."</td>
			    <td id=\"marge_".$article['artid']."\">".round($article['margeHT'],2)."&euro;</td>
			    <td id=\"margepc_".$article['artid']."\">".round($article['margePC'],1)."%</td>
			    <td class=\"td_lot\">".$article['pack_lot']."</td>
		      </tr>";
	      }
          }
     }
     echo "</table></form>";
     echo $scrolltop;
}else
    echo "<p>Aucun article avec les critères sélectionnés</p>";

include_once("footer.php");
