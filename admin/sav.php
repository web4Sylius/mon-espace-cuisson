<?php
//**********************************//
//*PAGE OBSOLETE DE VERSION DES SAV*//
//*GERE LES SAV JUSQU'AU SAV N°404 *//
//*AU-DELA, SE REFERER A SAV2.PHP  *//
//**********************************//

// if($user_info['username']!="cc") exit;

$fournisseurs=array();
$requete2=$DB_site2->query("SELECT societe FROM fournisseur WHERE type='facture' OR type='sav' GROUP BY societe ORDER BY societe");
while($f=$DB_site2->fetch_array($requete2)) $fournisseurs[]=$f['societe'];

if(isset($_POST['newsav'])){
    $DB_site2->query("INSERT INTO sav SET facturid=$facturid, date=".time().", utilisateur='".$user_info['username']."'");
    $savid=$DB_site2->insert_id();
}

$savfields=array();
if(isset($_POST['savid'])){
    $pour=$hide=$tsrappel=0;
    if($_POST['rappel']!=""){
	  $hide=intval(isset($_POST['hide']));
	  $pour=$_POST['pour'];
	  $sp=preg_split('/[- :\/h]/',$_POST['rappel']);
	  $tsrappel=mktime($sp[3],$sp[4],0,$sp[1],$sp[0],$sp[2]);
    }
    $DB_site2->query("INSERT INTO sav_steps SET savid=".$_POST['savid'].", step=".$_POST['step'].", utilisateur='".$user_info['username']."', date=".time().", rappel=".$tsrappel.", pour='$pour',hide=$hide, texte='".$_POST['texte']."'");
    foreach($_POST as $name=>$value){
	if(substr($name,0,4)=="sav_"){
	    $oldvalue=$DB_site2->query_first("SELECT id FROM sav_fields WHERE savid=".$_POST['savid']." AND name='".$name."'");
	    if($oldvalue['id']=="")
	      $DB_site2->query("INSERT INTO sav_fields SET savid=".$_POST['savid'].", name='$name', value='$value'");
	    else
	      $DB_site2->query("UPDATE sav_fields SET value='$value' WHERE id=".$oldvalue['id']);
	}
    }
    if(isset($_POST['sav_dateclos']) && $_POST['sav_dateclos']!=""){
      $sp=preg_split('/[- :\/h]/',$_POST['sav_dateclos']);
      $datecloture=mktime($sp[3],$sp[4],0,$sp[1],$sp[0],$sp[2]);
      $DB_site2->query("UPDATE sav SET fin=".$datecloture." WHERE id=".$_POST['savid']);
    }
}

if(isset($savid)){
    if(isset($_POST['savligne'])){
	$lignes=implode(",",$_POST['savligne']);
	$DB_site2->query("UPDATE sav SET lignes='$lignes' WHERE id=".$savid);
    }
    
    $fields=$DB_site2->query("SELECT * FROM sav_fields WHERE savid=".$savid);
    while($field=$DB_site2->fetch_array($fields)) $savfields[$field['name']]=$field['value'];
}

$maxstep=-1;
if(isset($savid)){
  $maxstep=0;
  $steps=$DB_site2->query_first("SELECT MAX(step) FROM sav_steps WHERE savid=".$savid);
  if($steps[0]!="") $maxstep=$steps[0];

  $sav=$DB_site2->query_first("SELECT * FROM sav WHERE id=".$savid);
  $savlignes=explode(",",$sav['lignes']);
}

$status=array(0=>"Non aboutie",1=>"<span style=\"color:blue;\">Validée</span>",2=>"<span style=\"color:red;\">Refusée</span>",3=>"<span style=\"color:red;\">Refusée</span>",4=>"Abandon",5=>"<span style=\"color:green;\">Expédiée</span>",7=>"<span style=\"color:blue;\">Validée non payée</span>",8=>"<span style=\"color:green;\">Expédiée non payée</span>");

$requete=$DB_site2->query("SELECT * FROM fournisseur WHERE type='savclient' ORDER BY societe");
while($f=$DB_site2->fetch_array($requete)) $savclient.="<br>".$f['societe']." : ".autofrtel($f['telephone']);

include("sav_steps.php");

if($maxstep>=0){

  print "<a name=\"sav\"></a><div class=\"advgroup\">
  <form name=\"et1form\" method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"1\">
  <p>Etape 1 : Constatation du litige</p>
  ".maj(1)."
  <input type=\"hidden\" name=\"sav_typelitige\" value=\"".intval($savfields['sav_typelitige'])."\">
  <table width=\"100%\">
  <tr><td><input type=\"radio\" name=\"typelitige\" id=\"typelitige0\" value=\"0\" onclick=\"choose1()\"".($savfields['sav_typelitige']=="0"?" checked":"")."> Transport/Installation</td>
  <td><input type=\"radio\" name=\"typelitige\" id=\"typelitige1\" value=\"1\" onclick=\"choose1()\"".($savfields['sav_typelitige']=="1"?" checked":"")."> Panne</td>
  <td><input type=\"radio\" name=\"typelitige\" id=\"typelitige2\" value=\"2\" onclick=\"choose1()\"".($savfields['sav_typelitige']=="2"?" checked":"")."> Erreur produit/manque</td>
  <td><input type=\"radio\" name=\"typelitige\" id=\"typelitige3\" value=\"3\" onclick=\"choose1()\"".($savfields['sav_typelitige']=="3"?" checked":"")."> Rétractation/annulation</td></tr>
  </table>
  <div id=\"litige0\" style=\"background-color:#cccccc;".($savfields['sav_typelitige']=="0"?"":"display:none;")."\">
  Type d'avarie :
  <br><input type=\"hidden\" name=\"sav_typelitige00\" value=\"".intval($savfields[sav_typelitige00])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige00']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige00')[0].value=this.checked?'1':'0';\"> Refus produit sans réserve sur BT
  <br><input type=\"hidden\" name=\"sav_typelitige01\" value=\"".intval($savfields[sav_typelitige01])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige01']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige01')[0].value=this.checked?'1':'0';\"> Refus produit avec réserve sur BT
  <br><input type=\"hidden\" name=\"sav_typelitige02\" value=\"".intval($savfields[sav_typelitige02])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige02']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige02')[0].value=this.checked?'1':'0';\"> Produit conservé sans réserve
  <br><input type=\"hidden\" name=\"sav_typelitige03\" value=\"".intval($savfields[sav_typelitige03])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige03']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige03')[0].value=this.checked?'1':'0';\"> Produit conservé avec réserve sur BT
  <br><input type=\"hidden\" name=\"sav_typelitige04\" value=\"".intval($savfields[sav_typelitige04])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige04']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige04')[0].value=this.checked?'1':'0';\"> Avarie occulte
  <br><input type=\"hidden\" name=\"sav_typelitige05\" value=\"".intval($savfields[sav_typelitige05])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige05']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige05')[0].value=this.checked?'1':'0';\"> Annulation commande suite retard transporteur
  <br><input type=\"hidden\" name=\"sav_typelitige06\" value=\"".intval($savfields[sav_typelitige06])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige06']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige06')[0].value=this.checked?'1':'0';\"> Autre : <input type=\"text\" name=\"sav_typelitige0autre\" value=\"".$savfields['sav_typelitige0autre']."\">
  </div>
  <div id=\"litige1\" style=\"background-color:#cccccc;".($savfields['sav_typelitige']=="1"?"":"display:none;")."\">
  Type de panne :
  <br><input type=\"hidden\" name=\"sav_typelitige10\" value=\"".intval($savfields[sav_typelitige10])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige10']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige10')[0].value=this.checked?'1':'0';\"> Panne sous garantie PMOD constructeur
  <br><input type=\"hidden\" name=\"sav_typelitige11\" value=\"".intval($savfields[sav_typelitige11])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige11']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige11')[0].value=this.checked?'1':'0';\"> Panne sous garantie PMOD SFG (sérénité)
  <br><input type=\"hidden\" name=\"sav_typelitige12\" value=\"".intval($savfields[sav_typelitige12])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige12']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige12')[0].value=this.checked?'1':'0';\"> Panne sous garantie pièce
  <br><input type=\"hidden\" name=\"sav_typelitige13\" value=\"".intval($savfields[sav_typelitige13])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige13']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige13')[0].value=this.checked?'1':'0';\"> Panne au déballage (7j ap livraison)
  <br><input type=\"hidden\" name=\"sav_typelitige14\" value=\"".intval($savfields[sav_typelitige14])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige14']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige14')[0].value=this.checked?'1':'0';\"> Sans garantie
  <br><input type=\"hidden\" name=\"sav_typelitige16\" value=\"".intval($savfields[sav_typelitige16])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige16']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige16')[0].value=this.checked?'1':'0';\"> Autre : <input type=\"text\" name=\"sav_typelitige1autre\" value=\"".$savfields['sav_typelitige1autre']."\">
  </div>
  <div id=\"litige2\" style=\"background-color:#cccccc;".($savfields['sav_typelitige']=="2"?"":"display:none;")."\">
  Type d'erreur / manque produit :
  <br><input type=\"hidden\" name=\"sav_typelitige20\" value=\"".intval($savfields[sav_typelitige20])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige20']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige20')[0].value=this.checked?'1':'0';\"> Livraison incomplète - produit conservé sans réserve BT
  <br><input type=\"hidden\" name=\"sav_typelitige21\" value=\"".intval($savfields[sav_typelitige21])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige21']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige21')[0].value=this.checked?'1':'0';\"> Livraison incomplète - produit conservé avec réserve BT
  <br><input type=\"hidden\" name=\"sav_typelitige22\" value=\"".intval($savfields[sav_typelitige22])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige22']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige22')[0].value=this.checked?'1':'0';\"> Livraison incomplète - produit refusé sans réserve BT
  <br><input type=\"hidden\" name=\"sav_typelitige23\" value=\"".intval($savfields[sav_typelitige23])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige23']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige23')[0].value=this.checked?'1':'0';\"> Livraison incomplète - produit refusé avec réserve BT
  <br><input type=\"hidden\" name=\"sav_typelitige24\" value=\"".intval($savfields[sav_typelitige24])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige24']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige24')[0].value=this.checked?'1':'0';\"> Erreur livraison - refus produit sans réserve
  <br><input type=\"hidden\" name=\"sav_typelitige25\" value=\"".intval($savfields[sav_typelitige25])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige25']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige25')[0].value=this.checked?'1':'0';\"> Erreur livraison - refus produit avec réserve
  <br><input type=\"hidden\" name=\"sav_typelitige26\" value=\"".intval($savfields[sav_typelitige26])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige26']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige26')[0].value=this.checked?'1':'0';\"> Erreur livraison - produit conservé sans réserve
  <br><input type=\"hidden\" name=\"sav_typelitige27\" value=\"".intval($savfields[sav_typelitige27])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige27']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige27')[0].value=this.checked?'1':'0';\"> Erreur livraison - produit conservé avec réserve
  </div>
  <div id=\"litige3\" style=\"background-color:#cccccc;".($savfields['sav_typelitige']=="3"?"":"display:none;")."\">
  Droit de rétractation :
  <br><input type=\"hidden\" name=\"sav_typelitige30\" value=\"".intval($savfields[sav_typelitige30])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige30']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige30')[0].value=this.checked?'1':'0';\"> Produit livré
  <br><input type=\"hidden\" name=\"sav_typelitige31\" value=\"".intval($savfields[sav_typelitige31])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_typelitige31']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_typelitige31')[0].value=this.checked?'1':'0';\"> Refus livraison
  </div>";

  print "<div style=\"margin-top:10px;\">
  <b>Produits concernés :</b>";

  $lignes=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid=$rs_facture[facturid]");
  while($ligne=$DB_site2->fetch_array($lignes)){
    $article=$DB_site2->query_first("SELECT artcode_eurocave,garantie_incluse FROM article WHERE artid=".$ligne['artid']);
    print "<br><input type=\"checkbox\" name=\"savligne[]\" value=\"$ligne[lignefactureid]\" onclick=\"(this.checked) ? document.getElementById('detail$ligne[lignefactureid]').style.display='table' : document.getElementById('detail$ligne[lignefactureid]').style.display='none'\" ".(in_array($ligne[lignefactureid],$savlignes)?" checked":"")."> $ligne[qte] $ligne[libelle] $ligne[artcode] - $article[artcode_eurocave]
    <table id=\"detail$ligne[lignefactureid]\" style=\"border:solid thin black;".(in_array($ligne[lignefactureid],$savlignes)?"":"display:none;")."\">
    <tr><td colspan=\"4\">Rappel commande passée :</td></tr>";
    $requete=$DB_site2->query("SELECT efl.paht,ef.destinataire,efl.type,ef.assur_comp FROM envoi_fournisseur_ligne efl INNER JOIN envoi_fournisseur ef ON(ef.id=efl.efid) WHERE efl.ligne_facture_id=".$ligne['lignefactureid']);
    while($efl=$DB_site2->fetch_array($requete)){
	switch($efl['type']){
	    case "fourn";
	    if($efl['destinataire']!="0") print "<tr><td>".$ligne['qte']." ".$ligne['artcode']." - ".$article['artcode_eurocave']."</td><td>".$efl['destinataire']."</td><td align=\"right\">".$efl['paht']." &euro; HT</td><td></td></tr>";
	    break;
	    case "trspt":
	    if($efl['destinataire']!="0") print "<tr><td>1 transport</td><td>".$efl['destinataire']."</td><td align=\"right\">".$efl['paht']." &euro; HT</td><td>".($efl['assur_comp']=='1'?"assurance complémentaire":"")."</td></tr>";
	    break;
	    case "sls":
	    if($efl['destinataire']!="0") print "<tr><td>1 installateur</td><td>".$efl['destinataire']."</td><td align=\"right\">".$efl['paht']." &euro; HT</td><td>".($efl['assur_comp']=='1'?"assurance complémentaire":"")."</td></tr>";
	    break;
	}
    }
    if($article['garantie_incluse']=="") $article['garantie_incluse']="aucune";
    print "<tr><td colspan=\"2\">Garantie constructeur : ".$article['garantie_incluse']."</td>";
    
    $caract=$DB_site2->query_first("SELECT lfc.libelle_caract,lfc.libelle_caractval,cv.libelleFR FROM lignefacturecaracteristique lfc INNER JOIN caracteristiquevaleur cv ON (cv.caractvalid=lfc.caractvalid) WHERE cv.caractid=3 AND lfc.lignefactureid=".$ligne['lignefactureid']);
    if($caract[0]==""){
	print "<td colspan=\"2\">Garantie sérénité : aucune</td>";
    }else
	print "<td colspan=\"2\">Garantie sérénité : ".$caract['libelleFR']."</td>";

    print "</tr></table>";
  }
  
  if($savfields['sav_datedepart']!="") $txtdatedepart=$savfields['sav_datedepart'];
  if($savfields['sav_datelivr']!="") $txtdatelivr=$savfields['sav_datelivr'];
  if($savfields['sav_datelitige']!="") $txtdatelitige=$savfields['sav_datelitige'];
  if($savfields['sav_dateappel']!="") $txtdateappel=$savfields['sav_dateappel'];

  print "<table style=\"margin-top:10px;width:auto;\">
  <tr><td>Date de départ produit :</td><td><input type=\"text\" name=\"sav_datedepart\" id=\"sav_datedepart\" value=\"$txtdatedepart\" onclick=\"calendrier_show(this.id,this.value)\" readonly></td></tr>
  <tr><td>Date de livraison :</td><td><input type=\"text\" name=\"sav_datelivr\" id=\"sav_datelivr\" value=\"$txtdatelivr\" onclick=\"calendrier_show(this.id,this.value)\" readonly></td></tr>
  <tr><td>Date de constatation du litige :</td><td><input type=\"text\" name=\"sav_datelitige\" id=\"sav_datelitige\" value=\"$txtdatelitige\" onclick=\"calendrier_show(this.id,this.value)\" readonly></td></tr>
  <tr><td>Date appel client :</td><td><input type=\"text\" name=\"sav_dateappel\" value=\"$txtdateappel\" id=\"sav_dateappel\" onclick=\"calendrier_show(this.id,this.value,true)\" readonly></td></tr>
  <tr><td colspan=\"2\">&nbsp;</td></tr>
  <tr><td>Numéro du bon de transport :</td><td><input type=\"text\" name=\"sav_nbon\" value=\"".$savfields['sav_nbon']."\"></td></tr>
  <tr><td>Nom du transporteur :</td><td><input type=\"text\" name=\"sav_nomtrspt\" value=\"".$savfields['sav_nomtrspt']."\"></td></tr>
  </table>
  <br><b>Questions à poser au client (uniquement pour avarie) :</b>
  <div style=\"padding-left:10px;\">
  <input type=\"hidden\" name=\"sav_emballage\" value=\"".intval($savfields[sav_emballage])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_emballage']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_emballage')[0].value=this.checked?'1':'0';\"> Emballage conservé
  <br><input type=\"hidden\" name=\"sav_geste\" value=\"".intval($savfields[sav_geste])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_geste']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_geste')[0].value=this.checked?'1':'0';\"> Est-ce que vous accepteriez un geste commercial pour conserver le produit ?
  <br><input type=\"hidden\" name=\"sav_reparable\" value=\"".intval($savfields[sav_reparable])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_reparable']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_reparable')[0].value=this.checked?'1':'0';\"> Selon vous, est-ce que la cave est réparable ?
  <br><b>En Option (sauf si client hors délai) : demander photo + lettre</b>
  </div>
  <div style=\"margin-top:10px;\">
  Détail précis du problème :<br><textarea name=\"sav_detail1\" cols=\"70\" rows=\"4\">".$savfields['sav_detail1']."</textarea>
  <br><br>Détail des réserves écrites sur le bon de transport (pour avarie de transport) :<br><textarea name=\"sav_reserves\" cols=\"70\" rows=\"4\">".$savfields['sav_reserves']."</textarea>
  <br><br>Commentaires INTERNES :<br><textarea name=\"sav_com1\" cols=\"70\" rows=\"4\">".$savfields['sav_com1']."</textarea>".suivi(1)."
  <div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Enregistrer\"></div>
  </form>
  </div>";

  print "</div>";
}

if($maxstep>=1) print $steps[2][$savfields['sav_typelitige']];

if($maxstep>=2) print $steps[3][$savfields['sav_typelitige']];

if($maxstep>=3) print $steps[4][$savfields['sav_typelitige']];

if($maxstep>=4) print $steps[5][$savfields['sav_typelitige']];

if($maxstep>=5) print $steps[6][$savfields['sav_typelitige']];

if($maxstep>=6) print $steps[7][$savfields['sav_typelitige']];


include("sav_js.php");

function fournopts($value){
    global $fournisseurs;
    $ret="<option value=\"\"></option>";
    $ret.="<option value=\"Le client\"".($value=="Le client"?" selected":"").">Le client</option>";
    foreach($fournisseurs as $fournisseur){
	$ret.="<option value=\"$fournisseur\"".($value==$fournisseur?" selected":"").">$fournisseur</option>";
    }
    return $ret;
}

function maj($step){
    global $savid;
    global $DB_site2;

    $ret="<div style=\"font-size:10px;\">Mises à jour :<br>";
    $usteps=$DB_site2->query("SELECT * FROM sav_steps WHERE savid=$savid AND step=$step ORDER BY date");
    while($s=$DB_site2->fetch_array($usteps)){
      $ret.=date("d/m/Y H:i",$s['date'])." par ".$s['utilisateur'];
      if($s['rappel']>0) $ret.=" rappel le ".date("d/m/Y à H:i",$s['rappel'])." pour ".$s['pour'];
      $ret.="<br>";
    }
    $ret.="</div>";
  
    return $ret;

}

function suivi($step){
    global $DB_site2;
    global $pouruser;
    global $savid;

    $id=uniqid();
    $html="<div style=\"text-align:center; margin:4px;\"><a href=\"javascript:void(0);\" onclick=\"mail_auto(undefined,undefined,undefined,undefined,undefined);mail_suivi('sav_$step','$savid');\">Envoyer un email</a>
    <br>Date de rappel : <input type=\"text\" name=\"rappel\" id=\"rappel$id\" onclick=\"calendrier_show(this.id,this.value,true);\" readonly> pour <select name=\"pour\">$pouruser</select> <input type=\"checkbox\" name=\"box\" value=\"1\" checked> Afficher le rappel";
    $html.="<input type=\"hidden\" name=\"texte\">";

    $requete=$DB_site2->query("SELECT * FROM sav_steps WHERE step=$step AND texte<>'' AND savid=$savid ORDER BY date");
    $rmail=$DB_site2->query("SELECT * FROM mail_outbox WHERE suivitype='sav_$step' AND suiviid=$savid");
    if($DB_site2->num_rows($requete)>0 || $DB_site2->num_rows($rmail)>0){
      $html.="<br><table>";
      while($suivi=$DB_site2->fetch_array($requete)){
	$suivitd[$suivi['date']]="<tr style=\"background-color:#dddddd;\"><td>".date("d/m/Y",$suivi['date'])."</td><td>".$suivi['utilisateur']."</td><td>".formatsuivi($suivi['texte'])."</td></tr>";
      }
      while($mail=$DB_site2->fetch_array($rmail))
	$suivitd[$mail['date']]="<tr style=\"background-color:#dddddd;\"><td>".date("d/m/Y",$mail['date'])."</td><td>".$mail['utilisateur']."</td><td>Email envoyé à ".$mail['to']."<br>".$mail['subject']."<br>".formatsuivi($mail['body']).($mail['pj']!=""?"<br>PJ : ".$mail['pj']:"")."</td></tr>";
      
      krsort($suivitd);
      foreach($suivitd as $tr) $html.=$tr;
      $html.="</table>";
    }
    $html.="</div>";
    return $html;
}

?>