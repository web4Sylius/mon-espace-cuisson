<?php

$steps=array();

//2

if($maxstep>=1){
$steps[2][0]="<div class=\"advgroup\">
  <form name=\"et2form\" method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"2\">
  <p>Etape 2 : Analyse du litige</p>
  ".maj(2)."
  <div id=\"edit2\" style=\"display:".($maxstep==1?"block":"none")."\">
      <table>
      <tr><td>Analyse produit</td><td></td><td>Montant HT</td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk2a0\" value=\"".intval($savfields['sav_chk2a0'])."\"><input type=\"checkbox\" id=\"chk2a0\" value=\"1\" onclick=\"calcsum2()\"".($savfields['sav_chk2a0']=="1"?" checked":"")."> Remplacement produit (+ récupération ancien)</td><td><select name=\"sav_fourn2a0\">".fournopts($savfields['sav_fourn2a0'])."</select></td><td><input type=\"text\" name=\"sav_val2a0\" value=\"".floatval($savfields['sav_val2a0'])."\" size=\"5\" onblur=\"calcsum2()\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk2a1\" value=\"".intval($savfields['sav_chk2a1'])."\"><input type=\"checkbox\" id=\"chk2a1\" value=\"1\" onclick=\"calcsum2()\"".($savfields['sav_chk2a1']=="1"?" checked":"")."> Remplacement produit (sans récupération ancien)</td><td><select name=\"sav_fourn2a1\">".fournopts($savfields['sav_fourn2a1'])."</select></td><td><input type=\"text\" name=\"sav_val2a1\" value=\"".floatval($savfields['sav_val2a1'])."\" size=\"5\" onblur=\"calcsum2()\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk2a2\" value=\"".intval($savfields['sav_chk2a2'])."\"><input type=\"checkbox\" id=\"chk2a2\" value=\"1\" onclick=\"calcsum2()\"".($savfields['sav_chk2a2']=="1"?" checked":"")."> Retour produit chez transporteur</td><td><select name=\"sav_fourn2a2\">".fournopts($savfields['sav_fourn2a2'])."</select></td><td><input type=\"text\" name=\"sav_val2a2\" value=\"".floatval($savfields['sav_val2a2'])."\" size=\"5\" onblur=\"calcsum2()\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk2a3\" value=\"".intval($savfields['sav_chk2a3'])."\"><input type=\"checkbox\" id=\"chk2a3\" value=\"1\" onclick=\"calcsum2()\"".($savfields['sav_chk2a3']=="1"?" checked":"")."> Retour produit chez fournisseur</td><td><select name=\"sav_fourn2a3\">".fournopts($savfields['sav_fourn2a3'])."</select></td><td><input type=\"text\" name=\"sav_val2a3\" value=\"".floatval($savfields['sav_val2a3'])."\" size=\"5\" onblur=\"calcsum2()\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk2a4\" value=\"".intval($savfields['sav_chk2a4'])."\"><input type=\"checkbox\" id=\"chk2a4\" value=\"1\" onclick=\"calcsum2()\"".($savfields['sav_chk2a4']=="1"?" checked":"")."> Retour produit chez Acilyon</td><td><select name=\"sav_fourn2a4\">".fournopts($savfields['sav_fourn2a4'])."</select></td><td><input type=\"text\" name=\"sav_val2a4\" value=\"".floatval($savfields['sav_val2a4'])."\" size=\"5\" onblur=\"calcsum2()\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk2a5\" value=\"".intval($savfields['sav_chk2a5'])."\"><input type=\"checkbox\" id=\"chk2a5\" value=\"1\" onclick=\"calcsum2()\"".($savfields['sav_chk2a5']=="1"?" checked":"")."> Retour produit plateforme transporteur Lyon</td><td><select name=\"sav_fourn2a5\">".fournopts($savfields['sav_fourn2a5'])."</select></td><td><input type=\"text\" name=\"sav_val2a5\" value=\"".floatval($savfields['sav_val2a5'])."\" size=\"5\" onblur=\"calcsum2()\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk2a6\" value=\"".intval($savfields['sav_chk2a6'])."\"><input type=\"checkbox\" id=\"chk2a6\" value=\"1\" onclick=\"calcsum2()\"".($savfields['sav_chk2a6']=="1"?" checked":"")."> Geste commercial</td><td><select name=\"sav_fourn2a6\">".fournopts($savfields['sav_fourn2a6'])."</select></td><td><input type=\"text\" name=\"sav_val2a6\" value=\"".floatval($savfields['sav_val2a6'])."\" size=\"5\" onblur=\"calcsum2()\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk2a7\" value=\"".intval($savfields['sav_chk2a7'])."\"><input type=\"checkbox\" id=\"chk2a7\" value=\"1\" onclick=\"calcsum2()\"".($savfields['sav_chk2a7']=="1"?" checked":"")."> Geste commercial</td><td><select name=\"sav_fourn2a7\">".fournopts($savfields['sav_fourn2a7'])."</select></td><td><input type=\"text\" name=\"sav_val2a7\" value=\"".floatval($savfields['sav_val2a7'])."\" size=\"5\" onblur=\"calcsum2()\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk2a8\" value=\"".intval($savfields['sav_chk2a8'])."\"><input type=\"checkbox\" id=\"chk2a8\" value=\"1\" onclick=\"calcsum2()\"".($savfields['sav_chk2a8']=="1"?" checked":"")."> Relivraison avec geste commercial</td><td><select name=\"sav_fourn2a8\">".fournopts($savfields['sav_fourn2a8'])."</select></td><td><input type=\"text\" name=\"sav_val2a8\" value=\"".floatval($savfields['sav_val2a8'])."\" size=\"5\" onblur=\"calcsum2()\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk2a9\" value=\"".intval($savfields['sav_chk2a9'])."\"><input type=\"checkbox\" id=\"chk2a9\" value=\"1\" onclick=\"calcsum2()\"".($savfields['sav_chk2a9']=="1"?" checked":"")."> Réparation chez client couvert par transporteur</td><td><select name=\"sav_fourn2a9\">".fournopts($savfields['sav_fourn2a9'])."</select></td><td><input type=\"text\" name=\"sav_val2a9\" value=\"".floatval($savfields['sav_val2a9'])."\" size=\"5\" onblur=\"calcsum2()\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk2a10\" value=\"".intval($savfields['sav_chk2a10'])."\"><input type=\"checkbox\" id=\"chk2a10\" value=\"1\" onclick=\"calcsum2()\"".($savfields['sav_chk2a10']=="1"?" checked":"")."> Réparation chez fournisseur couvert par transporteur</td><td><select name=\"sav_fourn2a10\">".fournopts($savfields['sav_fourn2a10'])."</select></td><td><input type=\"text\" name=\"sav_val2a10\" value=\"".floatval($savfields['sav_val2a10'])."\" size=\"5\" onblur=\"calcsum2()\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk2a11\" value=\"".intval($savfields['sav_chk2a11'])."\"><input type=\"checkbox\" id=\"chk2a11\" value=\"1\" onclick=\"calcsum2()\"".($savfields['sav_chk2a11']=="1"?" checked":"")."> Remboursement</td><td><select name=\"sav_fourn2a11\">".fournopts($savfields['sav_fourn2a11'])."</select></td><td><input type=\"text\" name=\"sav_val2a11\" value=\"".floatval($savfields['sav_val2a11'])."\" size=\"5\" onblur=\"calcsum2()\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk2a12\" value=\"".intval($savfields['sav_chk2a12'])."\"><input type=\"checkbox\" id=\"chk2a12\" value=\"1\" onclick=\"calcsum2()\"".($savfields['sav_chk2a12']=="1"?" checked":"")."> Cadeau <input type=\"text\" name=\"sav_kdos2\" value=\"".$savfields['sav_kdos2']."\"></td><td><select name=\"sav_fourn2a12\">".fournopts($savfields['sav_fourn2a12'])."</select></td><td><input type=\"text\" name=\"sav_val2a12\" value=\"".floatval($savfields['sav_val2a12'])."\" size=\"5\" onblur=\"calcsum2()\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk2a13\" value=\"".intval($savfields['sav_chk2a13'])."\"><input type=\"checkbox\" id=\"chk2a13\" value=\"1\" onclick=\"calcsum2()\"".($savfields['sav_chk2a13']=="1"?" checked":"")."> Autre <input type=\"text\" name=\"sav_autre2\" value=\"".$savfields['sav_autre2']."\"></td><td><select name=\"sav_fourn2a13\">".fournopts($savfields['sav_fourn2a13'])."</select></td><td><input type=\"text\" name=\"sav_val2a13\" value=\"".floatval($savfields['sav_val2a13'])."\" size=\"5\" onblur=\"calcsum2()\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk2a14\" value=\"".intval($savfields['sav_chk2a14'])."\"><input type=\"checkbox\" id=\"chk2a14\" value=\"1\" onclick=\"calcsum2()\"".($savfields['sav_chk2a14']=="1"?" checked":"")."> Transport aller</td><td><select name=\"sav_fourn2a14\">".fournopts($savfields['sav_fourn2a14'])."</select></td><td><input type=\"text\" name=\"sav_val2a14\" value=\"".floatval($savfields['sav_val2a14'])."\" size=\"5\" onblur=\"calcsum2()\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk2a15\" value=\"".intval($savfields['sav_chk2a15'])."\"><input type=\"checkbox\" id=\"chk2a15\" value=\"1\" onclick=\"calcsum2()\"".($savfields['sav_chk2a15']=="1"?" checked":"")."> Transport retour</td><td><select name=\"sav_fourn2a15\">".fournopts($savfields['sav_fourn2a15'])."</select></td><td><input type=\"text\" name=\"sav_val2a15\" value=\"".floatval($savfields['sav_val2a15'])."\" size=\"5\" onblur=\"calcsum2()\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk2a16\" value=\"".intval($savfields['sav_chk2a16'])."\"><input type=\"checkbox\" id=\"chk2a16\" value=\"1\" onclick=\"calcsum2()\"".($savfields['sav_chk2a16']=="1"?" checked":"")."> Aucune suite à donner</td><td><select name=\"sav_fourn2a16\">".fournopts($savfields['sav_fourn2a16'])."</select></td><td><input type=\"text\" name=\"sav_val2a16\" value=\"".floatval($savfields['sav_val2a16'])."\" size=\"5\" onblur=\"calcsum2()\"></td></tr>
      <tr><td style=\"font-weight:bold;\">Analyse du coût total prévisionnel du litige</td><td colspan=\"2\" id=\"resultsum2\" align=\"right\"></td></tr>
      </table>
      <div style=\"margin-top:10px;font-style:italic;\">MEMO : dans le cas d'un litige de transport, il ne faut pas oublier de facturer le produit au transporteur ainsi que les frais de transport ALLER (et retour si la cave doit être rapatriée).<br>L'envoi du mail automatique permet de demander au client d'envoyer la lettre de réclamation (sous 48H) au transporteur pour ouvrir le dossier de litige.</div>
      <div style=\"margin-top:10px;\">
      Commentaires INTERNES :<br><textarea name=\"sav_com2\" cols=\"70\" rows=\"4\">".$savfields['sav_com2']."</textarea>".suivi(2)."
      </div>
      <div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Enregistrer\"></div>
  </div>
  <div id=\"resum2\" style=\"margin-top:10px;display:".($maxstep>1?"block":"none")."\">
      <table>
       <tr><td>Analyse produit</td><td></td><td>Montant HT</td></tr>";
      if(intval($savfields['sav_chk2a0']==1)) $steps[2][0].="<tr><td>Remplacement produit (+ récupération ancien)</td><td>".$savfields['sav_fourn2a0']."</td><td>".floatval($savfields['sav_val2a0'])."</td></tr>";
      if(intval($savfields['sav_chk2a1']==1)) $steps[2][0].="<tr><td>Remplacement produit (sans récupération ancien)</td><td>".$savfields['sav_fourn2a1']."</td><td>".floatval($savfields['sav_val2a1'])."</td></tr>";
      if(intval($savfields['sav_chk2a2']==1)) $steps[2][0].="<tr><td>Retour produit chez transporteur</td><td>".$savfields['sav_fourn2a2']."</td><td>".floatval($savfields['sav_val2a2'])."</td></tr>";
      if(intval($savfields['sav_chk2a3']==1)) $steps[2][0].="<tr><td>Retour produit chez fournisseur</td><td>".$savfields['sav_fourn2a3']."</td><td>".floatval($savfields['sav_val2a3'])."</td></tr>";
      if(intval($savfields['sav_chk2a4']==1)) $steps[2][0].="<tr><td>Retour produit chez Acilyon</td><td>".$savfields['sav_fourn2a4']."</td><td>".floatval($savfields['sav_val2a4'])."</td></tr>";
      if(intval($savfields['sav_chk2a5']==1)) $steps[2][0].="<tr><td>Retour produit plateforme transporteur Lyon</td><td>".$savfields['sav_fourn2a5']."</td><td>".floatval($savfields['sav_val2a5'])."></td></tr>";
      if(intval($savfields['sav_chk2a6']==1)) $steps[2][0].="<tr><td>Geste commercial</td><td>".$savfields['sav_fourn2a6']."</td><td>".floatval($savfields['sav_val2a6'])."</td></tr>";
      if(intval($savfields['sav_chk2a7']==1)) $steps[2][0].="<tr><td>Geste commercial</td><td>".$savfields['sav_fourn2a7']."</td><td>".floatval($savfields['sav_val2a7'])."</td></tr>";
      if(intval($savfields['sav_chk2a8']==1)) $steps[2][0].="<tr><td>Relivraison avec geste commercial</td><td>".$savfields['sav_fourn2a8']."</td><td>".floatval($savfields['sav_val2a8'])."</td></tr>";
      if(intval($savfields['sav_chk2a9']==1)) $steps[2][0].="<tr><td>Réparation chez client couvert par transporteur</td><td>".$savfields['sav_fourn2a9']."</td><td>".floatval($savfields['sav_val2a9'])."</td></tr>";
      if(intval($savfields['sav_chk2a10']==1)) $steps[2][0].="<tr><td>Réparation chez fournisseur couvert par transporteur</td><td>".$savfields['sav_fourn2a10']."</td><td>".floatval($savfields['sav_val2a10'])."</td></tr>";
      if(intval($savfields['sav_chk2a11']==1)) $steps[2][0].="<tr><td>Remboursement</td><td>".$savfields['sav_fourn2a11']."</td><td>".floatval($savfields['sav_val2a11'])."</td></tr>";
      if(intval($savfields['sav_chk2a12']==1)) $steps[2][0].="<tr><td>Cadeau : ".$savfields['sav_kdos2']."</td><td>".$savfields['sav_fourn2a12']."</td><td>".floatval($savfields['sav_val2a12'])."</td></tr>";
      if(intval($savfields['sav_chk2a13']==1)) $steps[2][0].="<tr><td>Autre : ".$savfields['sav_autre2']."</td><td>".$savfields['sav_fourn2a13']."</td><td>".floatval($savfields['sav_val2a13'])."</td></tr>";
      if(intval($savfields['sav_chk2a14']==1)) $steps[2][0].="<tr><td>Transport aller</td><td>".$savfields['sav_fourn2a14']."</td><td>".floatval($savfields['sav_val2a14'])."</td></tr>";
      if(intval($savfields['sav_chk2a15']==1)) $steps[2][0].="<tr><td>Transport retour</td><td>".$savfields['sav_fourn2a15']."</td><td>".floatval($savfields['sav_val2a15'])."</td></tr>";
      if(intval($savfields['sav_chk2a16']==1)) $steps[2][0].="<tr><td>Aucune suite à donner</td><td>".$savfields['sav_fourn2a16']."</td><td>".floatval($savfields['sav_val2a16'])."</td></tr>";
      $steps[2][0].="</table>
      <div style=\"margin-top:10px;\">Commentaires</div>".suivi(2,false)."
      <input type=\"button\" value=\"Modifier\" onclick=\"modifstep(2);\">
  </div>
  </form>
  </div>";

$steps[2][1]="<div class=\"advgroup\">
  <form name=\"et2form\" method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"2\">
  <p>Etape 2 : Analyse du litige</p>
  ".maj(2)."
  <b>Analyse préliminaire :</b>
  <div style=\"margin-top:8px;margin-bottom:48px;font-size:14px;line-height:22px;\">
  <input type=\"hidden\" name=\"sav_pbtemp\" value=\"".intval($savfields['sav_pbtemp'])."\"><input type=\"checkbox\" onclick=\"document.getElementsByName('sav_pbtemp')[0].value=this.checked?'1':'0';\" ".($savfields['sav_pbtemp']=="1"?"checked":"")."><b>La cave est mal régulée (problème de température)</b>
  <div style=\"margin-left:20px;\"><input type=\"hidden\" name=\"sav_mauvutil\" value=\"".intval($savfields['sav_mauvutil'])."\"><input type=\"checkbox\" onclick=\"document.getElementsByName('sav_mauvutil')[0].value=this.checked?'1':'0';\" ".($savfields['sav_mauvutil']=="1"?"checked":"")."> - Solution possible : Mauvaise utilisation ou connaisse du produit, demander au client de voir la notice d'utilisaiton</div>
  <div style=\"margin-left:20px;\"><input type=\"hidden\" name=\"sav_patienter\" value=\"".intval($savfields['sav_patienter'])."\"><input type=\"checkbox\" onclick=\"document.getElementsByName('sav_patienter')[0].value=this.checked?'1':'0';\" ".($savfields['sav_patienter']=="1"?"checked":"")."> - Si appel client au bout de 2 ou 3 jours, lui demander de patienter car la régulation peut prendre plus de temps. Lui demander de rappeler d'ici plusieurs jours.</div>
  <br><input type=\"hidden\" name=\"sav_bruyant\" value=\"".intval($savfields['sav_bruyant'])."\"><input type=\"checkbox\" onclick=\"document.getElementsByName('sav_bruyant')[0].value=this.checked?'1':'0';\" ".($savfields['sav_bruyant']=="1"?"checked":"")."><b>Produit très bruyant</b> : la plupart temps, c'est normal (à voir selon le modèle, si modèle bruyant).
  </div>
  <div style=\"margin:8px;\">Sinon, faire l'analyse suivante :</div>
  <b>Détermination action et responsabilité</b>
  <table>
  <tr><td><input type=\"hidden\" name=\"sav_chk220\" value=\"".intval($savfields['sav_chk220'])."\"><input type=\"checkbox\" id=\"chk220\" value=\"1\" onclick=\"calcsum22()\"".($savfields['sav_chk220']=="1"?" checked":"")."> Remplacement produit (+ récupération ancien)</td><td><select name=\"sav_fourn220\">".fournopts($savfields['sav_fourn220'])."</select></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk221\" value=\"".intval($savfields['sav_chk221'])."\"><input type=\"checkbox\" id=\"chk221\" value=\"1\" onclick=\"calcsum22()\"".($savfields['sav_chk221']=="1"?" checked":"")."> Echange produit (+ récupération ancien)</td><td><select name=\"sav_fourn221\">".fournopts($savfields['sav_fourn221'])."</select></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk222\" value=\"".intval($savfields['sav_chk222'])."\"><input type=\"checkbox\" id=\"chk222\" value=\"1\" onclick=\"calcsum22()\"".($savfields['sav_chk222']=="1"?" checked":"")."> Récupération produit suite annulation</td><td><select name=\"sav_fourn222\">".fournopts($savfields['sav_fourn222'])."</select></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk223\" value=\"".intval($savfields['sav_chk223'])."\"><input type=\"checkbox\" id=\"chk223\" value=\"1\" onclick=\"calcsum22()\"".($savfields['sav_chk223']=="1"?" checked":"")."> Geste commercial<br>montant et prise en charge : <input type=\"text\" name=\"sav_geste223\" value=\"".$savfields['sav_geste223']."\"></td><td><select name=\"sav_fourn223\">".fournopts($savfields['sav_fourn223'])."</select></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk224\" value=\"".intval($savfields['sav_chk224'])."\"><input type=\"checkbox\" id=\"chk224\" value=\"1\" onclick=\"calcsum22()\"".($savfields['sav_chk224']=="1"?" checked":"")."> Geste commercial<br>montant et prise en charge : <input type=\"text\" name=\"sav_geste224\" value=\"".$savfields['sav_geste224']."\"></td><td><select name=\"sav_fourn224\">".fournopts($savfields['sav_fourn224'])."</select></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk225\" value=\"".intval($savfields['sav_chk225'])."\"><input type=\"checkbox\" id=\"chk225\" value=\"1\" onclick=\"calcsum22()\"".($savfields['sav_chk225']=="1"?" checked":"")."> Remboursement<br>raison : <input type=\"text\" name=\"sav_raison225\" value=\"".$savfields['sav_raison225']."\"></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk226\" value=\"".intval($savfields['sav_chk226'])."\"><input type=\"checkbox\" id=\"chk226\" value=\"1\" onclick=\"calcsum22()\"".($savfields['sav_chk226']=="1"?" checked":"")."> Aucune suite, dossier à solder<br>raison : <input type=\"text\" name=\"sav_raison226\" value=\"".$savfields['sav_raison226']."\"></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk227\" value=\"".intval($savfields['sav_chk227'])."\"><input type=\"checkbox\" id=\"chk227\" value=\"1\" onclick=\"calcsum22()\"".($savfields['sav_chk227']=="1"?" checked":"")."> Réparation chez client</td><td><select name=\"sav_fourn227\">".fournopts($savfields['sav_fourn227'])."</select></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk228\" value=\"".intval($savfields['sav_chk228'])."\"><input type=\"checkbox\" id=\"chk228\" value=\"1\" onclick=\"calcsum22()\"".($savfields['sav_chk228']=="1"?" checked":"")."> Réparation chez constructeur</td><td><select name=\"sav_fourn228\">".fournopts($savfields['sav_fourn228'])."</select></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk229\" value=\"".intval($savfields['sav_chk229'])."\"><input type=\"checkbox\" id=\"chk229\" value=\"1\" onclick=\"calcsum22()\"".($savfields['sav_chk229']=="1"?" checked":"")."> Autre <input type=\"text\" name=\"sav_autre22\" value=\"".$savfields['sav_autre22']."\"></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk2210\" value=\"".intval($savfields['sav_chk2210'])."\"><input type=\"checkbox\" id=\"chk2210\" value=\"1\" onclick=\"calcsum22()\"".($savfields['sav_chk2210']=="1"?" checked":"")."> Prise en charge panne par SAV fabricant - client renvoyé vers SAV fabricant, Voir étape 7 directement</td><td><select name=\"sav_fourn2210\">".fournopts($savfields['sav_fourn2210'])."</select></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk2211\" value=\"".intval($savfields['sav_chk2211'])."\"><input type=\"checkbox\" id=\"chk2211\" value=\"1\" onclick=\"calcsum22()\"".($savfields['sav_chk2211']=="1"?" checked":"")."> Prise en charge panne par SFG - client renvoyé vers SFG</td></tr>
  </table>
  <div style=\"margin-top:10px;\">
  <b>N° de téléphone à donner au client :</b>
  $savclient</div>
  <br><b>Analyse du coût prévisionnel du litige</b>
  <table>
  <tr><td>Prix achat cave</td><td><input type=\"text\" name=\"sav_val220\" value=\"".floatval($savfields['sav_val220'])."\" size=\"5\" onblur=\"calcsum22()\"></td></tr>
  <tr><td>Réparation</td><td><input type=\"text\" name=\"sav_val221\" value=\"".floatval($savfields['sav_val221'])."\" size=\"5\" onblur=\"calcsum22()\"></td></tr>
  <tr><td>Transport aller</td><td><input type=\"text\" name=\"sav_val222\" value=\"".floatval($savfields['sav_val222'])."\" size=\"5\" onblur=\"calcsum22()\"></td></tr>
  <tr><td>Transport retour</td><td><input type=\"text\" name=\"sav_val223\" value=\"".floatval($savfields['sav_val223'])."\" size=\"5\" onblur=\"calcsum22()\"></td></tr>
  <tr><td>Geste commercial</td><td><input type=\"text\" name=\"sav_val224\" value=\"".floatval($savfields['sav_val224'])."\" size=\"5\" onblur=\"calcsum22()\"></td></tr>
  <tr><td>Geste commercial</td><td><input type=\"text\" name=\"sav_val225\" value=\"".floatval($savfields['sav_val225'])."\" size=\"5\" onblur=\"calcsum22()\"></td></tr>
  <tr><td>Remboursement</td><td><input type=\"text\" name=\"sav_val226\" value=\"".floatval($savfields['sav_val226'])."\" size=\"5\" onblur=\"calcsum22()\"></td></tr>
  <tr><td>Autre : <input type=\"text\" name=\"sav_autreval22\" value=\"".$savfields['sav_autreval22']."\"></td><td><input type=\"text\" name=\"sav_val227\" value=\"".floatval($savfields['sav_val227'])."\" size=\"5\" onblur=\"calcsum22()\"></td></tr>
  <tr><td>Total HT : </td><td id=\"resultsum22\" align=\"right\"></td></tr>
  </table>
  <div style=\"margin-top:10px;\">
  Commentaires INTERNES :<br><textarea name=\"sav_com2\" cols=\"70\" rows=\"4\">".$savfields['sav_com2']."</textarea>".suivi(2)."
  <div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Enregistrer\"></div>
  </form>
  </div>";

  $steps[2][2]="<div class=\"advgroup\">
  <form name=\"et2form\" method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"2\">
  <p>Etape 2 : Analyse du litige</p>
  ".maj(2)."
  <b>Actions</b>
  <table>
  <tr><td><input type=\"hidden\" name=\"sav_chk231\" value=\"".intval($savfields['sav_chk231'])."\"><input type=\"checkbox\" id=\"chk231\" value=\"1\" onclick=\"calcsum23()\"".($savfields['sav_chk231']=="1"?" checked":"")."> Renvoi produits manquants</td><td><select name=\"sav_fourn231\">".fournopts($savfields['sav_fourn231'])."</select></td><td><input type=\"text\" name=\"sav_val231\" value=\"".floatval($savfields['sav_val231'])."\" size=\"5\" onblur=\"calcsum23()\"></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk232\" value=\"".intval($savfields['sav_chk232'])."\"><input type=\"checkbox\" id=\"chk232\" value=\"1\" onclick=\"calcsum23()\"".($savfields['sav_chk232']=="1"?" checked":"")."> Geste commercial<br>Montant et prise en charge : <input type=\"text\" name=\"sav_geste232\" value=\"".$savfields['sav_geste232']."\"></td><td><select name=\"sav_fourn232\">".fournopts($savfields['sav_fourn232'])."</select></td><td><input type=\"text\" name=\"sav_val232\" value=\"".floatval($savfields['sav_val232'])."\" size=\"5\" onblur=\"calcsum23()\"></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk233\" value=\"".intval($savfields['sav_chk233'])."\"><input type=\"checkbox\" id=\"chk233\" value=\"1\" onclick=\"calcsum23()\"".($savfields['sav_chk233']=="1"?" checked":"")."> Geste commercial<br>Montant et prise en charge : <input type=\"text\" name=\"sav_geste233\" value=\"".$savfields['sav_geste233']."\"></td><td><select name=\"sav_fourn233\">".fournopts($savfields['sav_fourn233'])."</select></td><td><input type=\"text\" name=\"sav_val233\" value=\"".floatval($savfields['sav_val233'])."\" size=\"5\" onblur=\"calcsum23()\"></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk234\" value=\"".intval($savfields['sav_chk234'])."\"><input type=\"checkbox\" id=\"chk234\" value=\"1\" onclick=\"calcsum23()\"".($savfields['sav_chk234']=="1"?" checked":"")."> Renvoi produit</td><td><select name=\"sav_fourn234\">".fournopts($savfields['sav_fourn234'])."</select></td><td><input type=\"text\" name=\"sav_val234\" value=\"".floatval($savfields['sav_val234'])."\" size=\"5\" onblur=\"calcsum23()\"></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk235\" value=\"".intval($savfields['sav_chk235'])."\"><input type=\"checkbox\" id=\"chk235\" value=\"1\" onclick=\"calcsum23()\"".($savfields['sav_chk235']=="1"?" checked":"")."> Retour mauvais produit</td><td><select name=\"sav_fourn235\">".fournopts($savfields['sav_fourn235'])."</select></td><td><input type=\"text\" name=\"sav_val235\" value=\"".floatval($savfields['sav_val235'])."\" size=\"5\" onblur=\"calcsum23()\"></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk236\" value=\"".intval($savfields['sav_chk236'])."\"><input type=\"checkbox\" id=\"chk236\" value=\"1\" onclick=\"calcsum23()\"".($savfields['sav_chk236']=="1"?" checked":"")."> Aucune suite - dossier à solder</td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk237\" value=\"".intval($savfields['sav_chk237'])."\"><input type=\"checkbox\" id=\"chk237\" value=\"1\" onclick=\"calcsum23()\"".($savfields['sav_chk237']=="1"?" checked":"")."> Annulation</td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk238\" value=\"".intval($savfields['sav_chk238'])."\"><input type=\"checkbox\" id=\"chk238\" value=\"1\" onclick=\"calcsum23()\"".($savfields['sav_chk238']=="1"?" checked":"")."> Autre <input type=\"text\" name=\"sav_autre22\" value=\"".$savfields['sav_autre23']."\"></td><td></td><td><input type=\"text\" name=\"sav_val238\" value=\"".floatval($savfields['sav_val238'])."\" size=\"5\" onblur=\"calcsum23()\"></td></tr>
  <tr><td colspan=\"2\">Total HT : </td><td id=\"resultsum23\" align=\"right\"></td></tr>
  </table>
  <div style=\"margin-top:10px;\">
  Commentaires INTERNES :<br><textarea name=\"sav_com2\" cols=\"70\" rows=\"4\">".$savfields['sav_com2']."</textarea>".suivi(2)."
  <div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Enregistrer\"></div>
  </form>
  </div>";

  $steps[2][3]="<div class=\"advgroup\">
  <form name=\"et2form\" method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"2\">
  <p>Etape 2 : Analyse du dossier</p>
  ".maj(2)."
  <div style=\"margin-top:10px;\">
  <b>Retractation pendant délai légal de 7 jours :</b><input type=\"hidden\" name=\"sav_retrac7j\" value=\"".intval($savfields['sav_retrac7j'])."\"><input type=\"radio\" name=\"retrac7j\" onclick=\"document.getElementsByName('sav_retrac7j')[0].value='1';\"".($savfields['sav_retrac7j']=='1'?" checked":"").">Oui &nbsp; <input type=\"radio\" name=\"retrac7j\" onclick=\"document.getElementsByName('sav_retrac7j')[0].value='0';\"".($savfields['sav_retrac7j']=='0'?" checked":"").">Non
  <br><b>Demande lettre client :</b><input type=\"hidden\" name=\"sav_demandelettre\" value=\"".intval($savfields['sav_demandelettre'])."\"><input type=\"radio\" name=\"demandelettre\" onclick=\"document.getElementsByName('sav_demandelettre')[0].value='1';\"".($savfields['sav_demandelettre']=='1'?" checked":"").">Oui &nbsp; <input type=\"radio\" name=\"demandelettre\" onclick=\"document.getElementsByName('sav_demandelettre')[0].value='0';\"".($savfields['sav_demandelettre']=='0'?" checked":"").">Non
  </div>
  <div style=\"margin-top:10px;\">
  <b>Responsabilité : client</b>
  <table>
  <tr><td>Retour produit</td><td><input type=\"hidden\" name=\"sav_retouracilyon\" value=\"".intval($savfields['sav_retouracilyon'])."\"><input type=\"checkbox\" onclick=\"document.getElementsByName('sav_retouracilyon')[0].value=this.checked?'1':'0';\" ".($savfields['sav_retouracilyon']=="1"?"checked":"")."> Acilyon</td>
    <td><input type=\"hidden\" name=\"sav_retourfab\" value=\"".intval($savfields['sav_retourfab'])."\"><input type=\"checkbox\" onclick=\"document.getElementsByName('sav_retourfab')[0].value=this.checked?'1':'0';\" ".($savfields['sav_retourfab']=="1"?"checked":"")."> Fabricant</td></tr>
  <tr><td>Retour produit</td><td><input type=\"hidden\" name=\"sav_retourchargeclient\" value=\"".intval($savfields['sav_retourchargeclient'])."\"><input type=\"checkbox\" onclick=\"document.getElementsByName('sav_retourchargeclient')[0].value=this.checked?'1':'0';\" ".($savfields['sav_retourchargeclient']=="1"?"checked":"")."> Charge du client</td>
    <td><input type=\"hidden\" name=\"sav_retourchargeacilyon\" value=\"".intval($savfields['sav_retourchargeacilyon'])."\"><input type=\"checkbox\" onclick=\"document.getElementsByName('sav_retourchargeacilyon')[0].value=this.checked?'1':'0';\" ".($savfields['sav_retourchargeacilyon']=="1"?"checked":"")."> Charge Acilyon avec remboursement ultérieur</td></tr>
  <tr><td>Retour produit</td><td colspan=\"2\" valign=\"top\"><input type=\"hidden\" name=\"sav_retouramiable\" value=\"".intval($savfields['sav_retouramiable'])."\"><input type=\"checkbox\" onclick=\"document.getElementsByName('sav_retouramiable')[0].value=this.checked?'1':'0';\" ".($savfields['sav_retouramiable']=="1"?"checked":"")."> Accord à l'amiable : <textarea name=\"sav_retouramiabletxt\" cols=\"40\"></textarea></td></tr>
  </table>
  <br><input type=\"hidden\" name=\"sav_retracperso\" value=\"".intval($savfields['sav_retracperso'])."\"><input type=\"checkbox\" onclick=\"document.getElementsByName('sav_retracperso')[0].value=this.checked?'1':'0';\" ".($savfields['sav_retracperso']=="1"?"checked":"")."> Droit de rétractation ne peut pas s'exercer à cause personnalisation produit
  <br><br><div style=\"width:150px;display:inline-block;\">Dossier sans suite</div><input type=\"text\" name=\"sav_sanssuite\" size=\"60\" value=\"".$savfields['sav_sanssuite']."\">
  <br><div style=\"width:150px;display:inline-block;\">Autres</div><input type=\"text\" name=\"sav_retracautre\" size=\"60\" value=\"".$savfields['sav_retracautre']."\">
  <br>Lettre client de rétractation reçue sous 7 jours (à compter de la livraison) :<input type=\"hidden\" name=\"sav_recu7j\" value=\"".intval($savfields['sav_recu7j'])."\"><input type=\"radio\" name=\"recu7j\" onclick=\"document.getElementsByName('sav_recu7j')[0].value='1';\"".($savfields['sav_recu7j']=='1'?" checked":"").">Oui &nbsp; <input type=\"radio\" name=\"recu7j\" onclick=\"document.getElementsByName('sav_recu7j')[0].value='0';\"".($savfields['sav_recu7j']=='0'?" checked":"").">Non
  </div>
  <div style=\"margin-top:10px;\">
  <a href=\"javascript:void(0);\" onclick=\"javascript:mail_auto(undefined,undefined,undefined,subjectretourprod,mailretourprod);mail_suivi('sav_2','$savid');\">Envoi procédure de retour produit</a>
  </div>
  <div style=\"margin-top:10px;\">
  Commentaires INTERNES :<br><textarea name=\"sav_com2\" cols=\"70\" rows=\"4\">".$savfields['sav_com2']."</textarea>".suivi(2)."
  <div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Enregistrer\"></div>
  </form>
  </div>";
}

//3
if($maxstep>=2){
$steps[3][0]="<div class=\"advgroup\">
  <form name=\"et3form\" method=\"post\" enctype=\"multipart/form-data\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"3\">
  <p>Etape 3 : Ouverture du dossier litige</p>
  ".maj(3)."
  <div id=\"edit3\" style=\"display:".($maxstep==2?"block":"none")."\">
      <div style=\"margin-top:10px;\"><a href=\"javascript:void(0);\" onclick=\"mail_auto(undefined,'$rs_facture[mail]',undefined,subjectprocedure,mailprocedure);mail_suivi('sav_3','$savid');\">Générer mail procédure client</a></div>
      <b>Lettre client envoyée</b>
      <div>
      <input type=\"hidden\" name=\"sav_lettrem3\" value=\"".intval($savfields[sav_lettrem3])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_lettrem3']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_lettrem3')[0].value=this.checked?'1':'0';\"> Lettre réclamation client envoyée sous 3 jours
      <br><input type=\"hidden\" name=\"sav_lettrehd\" value=\"".intval($savfields[sav_lettrehd])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_lettrehd']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_lettrehd')[0].value=this.checked?'1':'0';\"> Lettre réclamation client envoyée hors délai
      <br><input type=\"hidden\" name=\"sav_reclamacilyon\" value=\"".intval($savfields[sav_reclamacilyon])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_reclamacilyon']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_reclamacilyon')[0].value=this.checked?'1':'0';\"> Lettre réclamation client envoyée par Acilyon
      <br>(Lorsque le client a refusé la cave)
      </div>
      <div style=\"margin-top:10px;\">
      Envoi de fichier : <input type=\"file\" name=\"sendfile\"> <input type=\"button\" onclick=\"document.forms.et3form.action='sav_ul.php';document.forms.et3form.target='fupload';document.forms.et3form.submit();verifupload();\" value=\"Envoyer\">
      <div id=\"ulprogress\"></div>
      <iframe name=\"fupload\" src=\"sav_ul.php?savid=$savid\" style=\"display:none;\"></iframe>
      </div>
      <div style=\"margin-top:10px;\"><a href=\"javascript:void(0);\" onclick=\"mail_auto(undefined,undefined,undefined,subjectreclamtrspt,mailreclamtrspt);mail_suivi('sav_3','$savid');\">Générer mail réclamation transporteur</a>
	<br><br>Confirmation ouverture du dossier litige n°<input type=\"text\" name=\"sav_ndossier\" value=\"".$savfields['sav_ndossier']."\"> le <input type=\"text\" name=\"sav_dateouvre\" id=\"sav_dateouvre\" value=\"".$savfields['sav_dateouvre']."\" size=\"10\" onclick=\"calendrier_show(this.id,this.value)\" readonly></div>
      <div style=\"margin-top:10px;\">
      Commentaires INTERNES :<br><textarea name=\"sav_com3\" cols=\"70\" rows=\"4\">".$savfields['sav_com3']."</textarea>".suivi(3)."
      </div>
      <div style=\"text-align:center;padding:10px;\"><input type=\"button\" onclick=\"document.forms.et3form.action='';document.forms.et3form.target='';document.forms.et3form.submit();\" value=\"Enregistrer\"></div>
  </div>
  <div id=\"resum3\" style=\"margin-top:10px;display:".($maxstep>2?"block":"none")."\">";
      if(intval($savfields[sav_lettrem3])>0) $steps[3][0].="<li>Lettre réclamation client envoyée sous 3 jours</li>";
      if(intval($savfields[sav_lettrehd])>0) $steps[3][0].="<li>Lettre réclamation client envoyée hors délai</li>";
      if(intval($savfields[sav_reclamacilyon])>0) $steps[3][0].="<li>Lettre réclamation client envoyée par Acilyon</li>";
      $steps[3][0].="<div>Confirmation ouverture du dossier litige n°".$savfields['sav_ndossier']." le ".$savfields['sav_dateouvre']."</div>
      <div style=\"margin-top:10px;\">Commentaires</div>".suivi(3,false)."
      <input type=\"button\" value=\"Modifier\" onclick=\"modifstep(3);\">";
  $steps[3][0].="</div>
  </form>
  </div>";

$steps[3][1]="<div class=\"advgroup\">
  <form name=\"et3form\" method=\"post\" enctype=\"multipart/form-data\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"3\">
  <p>Etape 3 : Ouverture du dossier litige</p>
  ".maj(3)."
  <div>
    <a href=\"javascript:void(0);\" onclick=\"mail_auto(undefined,'$rs_facture[mail]',undefined,subjectprocedure2,mailprocedure2');mail_suivi('sav_3','$savid');\">Envoi mail procédure client (panne déballage)</a>
    <br><a href=\"javascript:void(0);\" onclick=\"mail_auto(undefined,'$rs_facture[mail]',undefined,subjectproceduresfg,mailproceduresfg);mail_suivi('sav_3','$savid');\">Envoi mail procédure client (panne prise en charge par garantie constructeur ou SFG)</a>
  </div>
  <br><b>Lettre client envoyée au fabricant</b>
  <table>
  <tr><td><input type=\"hidden\" name=\"sav_lettrereclam\" value=\"".intval($savfields['sav_lettrereclam'])."\"><input type=\"checkbox\" ".($savfields['sav_lettrereclam']=='1'?"checked":"")." onclick=\"document.getElementsByName('sav_lettrereclam')[0].value=this.checked?'1':'0';\"> Lettre réclamation client envoyée</td>
    <td><input type=\"hidden\" name=\"sav_lettrereclamacilyon\" value=\"".intval($savfields['sav_lettrereclamacilyon'])."\"><input type=\"checkbox\" ".($savfields['sav_lettrereclamacilyon']=='1'?"checked":"")." onclick=\"document.getElementsByName('sav_lettrereclamacilyon')[0].value=this.checked?'1':'0';\"> Lettre réclamation client envoyée par Acilyon</td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_lettrereclamhd\" value=\"".intval($savfields['sav_lettrereclamhd'])."\"><input type=\"checkbox\" ".($savfields['sav_lettrereclamhd']=='1'?"checked":"")." onclick=\"document.getElementsByName('sav_lettrereclamhd')[0].value=this.checked?'1':'0';\"> Lettre réclamation client hors délai (panne déballage)</td>
    <td><input type=\"hidden\" name=\"sav_lettrereclam7j\" value=\"".intval($savfields['sav_lettrereclam7j'])."\"><input type=\"checkbox\" ".($savfields['sav_lettrereclam7j']=='1'?"checked":"")." onclick=\"document.getElementsByName('sav_lettrereclam7j')[0].value=this.checked?'1':'0';\"> Lettre réclamation client envoyée sous 7 jours (panne déballage)</td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_lettrereclam0\" value=\"".intval($savfields['sav_lettrereclam0'])."\"><input type=\"checkbox\" ".($savfields['sav_lettrereclam0']=='1'?"checked":"")." onclick=\"document.getElementsByName('sav_lettrereclam0')[0].value=this.checked?'1':'0';\"> Pas de lettre de réclamation à envoyer</td></tr>
  </table>
  <div style=\"margin-top:10px;\">
  Envoi de fichier : <input type=\"file\" name=\"sendfile\"> <input type=\"button\" onclick=\"document.forms.et3form.action='sav_ul.php';document.forms.et3form.target='fupload';document.forms.et3form.submit();verifupload();\" value=\"Envoyer\">
  <div id=\"ulprogress\"></div>
  <iframe name=\"fupload\" src=\"sav_ul.php?savid=$savid.php\" style=\"border:none;height:30px;\"></iframe>
  </div>
  <div style=\"margin-top:10px;\">Confirmation ouverture du dossier litige n°<input type=\"text\" name=\"sav_ndossier\" value=\"".$savfields['sav_ndossier']."\"> le <input type=\"text\" name=\"sav_dateouvre\" id=\"sav_dateouvre\" value=\"".$savfields['sav_dateouvre']."\" size=\"10\" onclick=\"calendrier_show(this.id,this.value)\" readonly></div>
  <div style=\"margin-top:10px;\">
  Commentaires INTERNES :<br><textarea name=\"sav_com3\" cols=\"70\" rows=\"4\">".$savfields['sav_com3']."</textarea>".suivi(3)."
  </div>
  <div style=\"text-align:center;padding:10px;\"><input type=\"button\" onclick=\"document.forms.et3form.action='';document.forms.et3form.target='';document.forms.et3form.submit();\" value=\"Enregistrer\"></div>
  </form>
  </div>";


$steps[3][2]="<div class=\"advgroup\">
  <form name=\"et3form\" method=\"post\" enctype=\"multipart/form-data\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"3\">
  <p>Etape 3 : Ouverture du dossier litige</p>
  ".maj(3)."
  <div style=\"margin-top:10px;\">
  Dossier litige n°<input type=\"text\" name=\"sav_ndossier\" value=\"".$savfields['sav_ndossier']."\">
  </div>
    <br><b>Pièces à joindre au dossier :</b>
  <div>
    <input type=\"hidden\" name=\"sav_nbseriemp\" value=\"".intval($savfields['sav_nbseriemp'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_nbseriemp']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_nbseriemp')[0].value=this.checked?'1':'0';\"> Numéro de série mauvais produit (si possible)
    <br><input type=\"hidden\" name=\"sav_factureclient\" value=\"".intval($savfields['sav_factureclient'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_factureclient']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_factureclient')[0].value=this.checked?'1':'0';\"> <a href=\"../facture2.php?facturid=$facturid&passe=$rs_facture[passefacture]\">Facture client Ma Cave à Vin</a>
    <br><input type=\"hidden\" name=\"sav_lettreclient\" value=\"".intval($savfields['sav_lettreclient'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_lettreclient']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_lettreclient')[0].value=this.checked?'1':'0';\"> Lettre/mail explicatif du problème (par le client ou Acilyon)
    <br><input type=\"hidden\" name=\"sav_copiefacturefourn\" value=\"".intval($savfields['sav_copiefacturefourn'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_copiefacturefourn']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_copiefacturefourn')[0].value=this.checked?'1':'0';\"> Facultatif : copie facture fournisseur
    <br><input type=\"hidden\" name=\"sav_newcmd\" value=\"".intval($savfields['sav_newcmd'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_newcmd']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_newcmd')[0].value=this.checked?'1':'0';\"> Facultatif : nouvelle commande (expédier produit manquant ou remboursement transport
    <br><input type=\"hidden\" name=\"sav_demandeavoir\" value=\"".intval($savfields['sav_demandeavoir'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_demandeavoir']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_demandeavoir')[0].value=this.checked?'1':'0';\"> Facultatif : demander d'avoir (si produit remplacé facturé)
    <br>
    <br><b>Les pièces du dossier :</b>
    <div style=\"margin:0px;padding:0px;\" id=\"et6tmppj\">
    <input type=\"checkbox\" name=\"et6pj\" value=\"$facturid\"><a href=\"../facture2.php?facturid=$facturid&passe=$rs_facture[passefacture]&nopub=1\" target=\"_blank\">Facture n°$facturid</a>";
    $js.="serverpj[$facturid]=new Array();
    serverpj[$facturid]['url']=\"http://".$_SERVER['SERVER_NAME']."/facture2.php?facturid=$facturid&passe=$rs_facture[passefacture]&nopub=1\";
    serverpj[$facturid]['name']=\"Facture $facturid.pdf\";\n";
   
   $requete=$DB_site2->query("SELECT * FROM facture WHERE userid<>".intval($rs_facture['userid'])." AND provenance='stat/antispy.phpsav' AND provenanceid=".$facturid);
   while($fsav=$DB_site2->fetch_array($requete)){
	$js.="serverpj[$fsav[facturid]]=new Array();
	serverpj[$fsav[facturid]]['url']=\"http://".$_SERVER['SERVER_NAME']."/facture2.php?facturid=$fsav[facturid]&passe=$fsav[passefacture]&nopub=1\";
	serverpj[$fsav[facturid]]['name']=\"Facture SAV $fsav[facturid].pdf\";\n";
	$steps[3][2].="<br><input type=\"checkbox\" name=\"et6pj\" value=\"$fsav[facturid]\"><a href=\"../facture2.php?facturid=$fsav[facturid]&passe=$fsav[passefacture]&nopub=1\" target=\"_blank\">Facture SAV n°$fsav[facturid]</a>";
   }

   $pjfiles=$DB_site2->query("SELECT * FROM sav_files WHERE savid=$savid AND state=1 ORDER BY date");
   while($pj=$DB_site2->fetch_array($pjfiles)){
	$js.="serverpj[$pj[id]]=new Array();
	serverpj[$pj[id]]['url']=\"sav/$pj[id].$pj[ext]\";
	serverpj[$pj[id]]['name']=\"$pj[name]\";\n";
	$steps[3][2].="<br><input type=\"checkbox\" name=\"et6pj\" value=\"$pj[id]\" ".($pj['size']>10785760?"style=\"visibility:hidden\"":"")."><a href=\"sav/$pj[id].$pj[ext]\" target=\"_blank\">$pj[name]</a> ".round($pj['size']/1024)." ko <img src=\"images/supprimer.gif\" title=\"Supprimer\" onclick=\"delpj($pj[id])\">";
   }
   $steps[3][2].="</div>
    Envoi de fichier : <input type=\"file\" name=\"sendfile\"> <input type=\"button\" onclick=\"document.forms.et3form.action='sav_ul.php';document.forms.et3form.target='fupload';document.forms.et3form.submit();verifupload();\" value=\"Envoyer\">
    <div id=\"ulprogress\"></div>
    <iframe name=\"fupload\" src=\"sav_ul.php?savid=$savid\" style=\"display:none;\"></iframe>
  <div style=\"margin:5px;\"><a href=\"javascript:void(0);\" onclick=\"maildossier(2,3);\">Dossier à envoyer par mail</a></div>
  <div style=\"margin:10px;font-style:italic;\">MEMO : le renvoi des produits doit être effectué à la charge du fournisseur, ou facturé ultérieurement. S'il faut ramasser le mauvais produit expédier pour le rapatrier chez le fabricant, alors il faut aussi se faire rembourser le transport.</div>
  <div>
    Dossier complet envoyé le <input type=\"text\" name=\"sav_envoidossier\" id=\"sav_envoidossier\" value=\"".$savfields['sav_envoidossier']."\" onclick=\"calendrier_show(this.id,this.value)\"> par <input type=\"hidden\" name=\"sav_envoimail\" value=\"".intval($savfields['sav_envoimail'])."\"><input type=\"checkbox\" value=\"1\" ".($savfields['sav_envoimail']?"checked":"")." onclick=\"document.getElementsByName('sav_envoimail')[0].value=this.checked?'1':'0';\"> mail &nbsp; <input type=\"hidden\" name=\"sav_envoiposte\" value=\"".intval($savfields['sav_envoiposte'])."\"><input type=\"checkbox\"".($savfields['sav_envoiposte']?"checked":"")." onclick=\"document.getElementsByName('sav_envoiposte')[0].value=this.checked?'1':'0';\"> La Poste
  </div>
  <div style=\"margin-top:10px;\">
  Commentaires INTERNES :<br><textarea name=\"sav_com3\" cols=\"70\" rows=\"4\">".$savfields['sav_com3']."</textarea>".suivi(3)."
  </div>
  <div style=\"text-align:center;padding:10px;\"><input type=\"button\" onclick=\"document.forms.et3form.action='';document.forms.et3form.target='';document.forms.et3form.submit();\" value=\"Enregistrer\"></div>
  </form>
  </div>";

$steps[3][3]="<div class=\"advgroup\">
  <form name=\"et3form\" method=\"post\" enctype=\"multipart/form-data\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"3\">
  <p>Etape 3 : Montage du dossier (Si retour chez fournisseur)</p>
  ".maj(3)."
  <div style=\"margin-top:10px;\">
  Dossier litige n°<input type=\"text\" name=\"sav_ndossier\" value=\"".$savfields['sav_ndossier']."\">
  </div>
    <br><b>Pièces à joindre au dossier :</b>
  <div>
    <input type=\"hidden\" name=\"sav_nbseriemp\" value=\"".intval($savfields['sav_nbseriemp'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_nbseriemp']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_nbseriemp')[0].value=this.checked?'1':'0';\"> Numéro de série mauvais produit (si possible)
    <br><input type=\"hidden\" name=\"sav_factureclient\" value=\"".intval($savfields['sav_factureclient'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_factureclient']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_factureclient')[0].value=this.checked?'1':'0';\"> <a href=\"../facture2.php?facturid=$facturid&passe=$rs_facture[passefacture]\">Facture client Ma Cave à Vin</a>
    <br><input type=\"hidden\" name=\"sav_lettreclient\" value=\"".intval($savfields['sav_lettreclient'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_lettreclient']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_lettreclient')[0].value=this.checked?'1':'0';\"> Lettre/mail explicatif du problème (par le client ou Acilyon)
    <br><input type=\"hidden\" name=\"sav_lettreclient2\" value=\"".intval($savfields['sav_lettreclient2'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_lettreclient2']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_lettreclient2')[0].value=this.checked?'1':'0';\"> Lettre client
    <br><input type=\"hidden\" name=\"sav_demandeavoir\" value=\"".intval($savfields['sav_demandeavoir'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_demandeavoir']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_demandeavoir')[0].value=this.checked?'1':'0';\"> Facultatif : demander d'avoir (si produit remplacé facturé)
    <br>
    <br><b>Les pièces du dossier :</b>
    <div style=\"margin:0px;padding:0px;\" id=\"et6tmppj\">
    <input type=\"checkbox\" name=\"et6pj\" value=\"$facturid\"><a href=\"../facture2.php?facturid=$facturid&passe=$facture[passefacture]&nopub=1\" target=\"_blank\">Facture n°$facturid</a>";
    $js.="serverpj[$facturid]=new Array();
    serverpj[$facturid]['url']=\"http://".$_SERVER['SERVER_NAME']."/facture2.php?facturid=$facturid&passe=$facture[passefacture]&nopub=1\";
    serverpj[$facturid]['name']=\"Facture $facturid.pdf\";\n";
   
   $requete=$DB_site2->query("SELECT * FROM facture WHERE userid<>".intval($rs_facture['userid'])." AND provenance='sav' AND provenanceid=".$facturid);
   while($fsav=$DB_site2->fetch_array($requete)){
	$js.="serverpj[$fsav[facturid]]=new Array();
	serverpj[$fsav[facturid]]['url']=\"http://".$_SERVER['SERVER_NAME']."/facture2.php?facturid=$fsav[facturid]&passe=$fsav[passefacture]&nopub=1\";
	serverpj[$fsav[facturid]]['name']=\"Facture SAV $fsav[facturid].pdf\";\n";
	$steps[3][3].="<br><input type=\"checkbox\" name=\"et6pj\" value=\"$fsav[facturid]\"><a href=\"../facture2.php?facturid=$fsav[facturid]&passe=$fsav[passefacture]&nopub=1\" target=\"_blank\">Facture SAV n°$fsav[facturid]</a>";
   }

   $pjfiles=$DB_site2->query("SELECT * FROM sav_files WHERE savid=$savid AND state=1 ORDER BY date");
   while($pj=$DB_site2->fetch_array($pjfiles)){
	$js.="serverpj[$pj[id]]=new Array();
	serverpj[$pj[id]]['url']=\"sav/$pj[id].$pj[ext]\";
	serverpj[$pj[id]]['name']=\"$pj[name]\";\n";
	$steps[3][3].="<br><input type=\"checkbox\" name=\"et6pj\" value=\"$pj[id]\" ".($pj['size']>10785760?"style=\"visibility:hidden\"":"")."><a href=\"sav/$pj[id].$pj[ext]\" target=\"_blank\">$pj[name]</a> ".round($pj['size']/1024)." ko <img src=\"images/supprimer.gif\" title=\"Supprimer\" onclick=\"delpj($pj[id])\">";
   }
   $steps[3][3].="</div>
    Envoi de fichier : <input type=\"file\" name=\"sendfile\"> <input type=\"button\" onclick=\"document.forms.et3form.action='sav_ul.php';document.forms.et3form.target='fupload';document.forms.et3form.submit();verifupload();\" value=\"Envoyer\">
    <div id=\"ulprogress\"></div>
    <iframe name=\"fupload\" src=\"sav_ul.php?savid=$savid\" style=\"display:none;\"></iframe>
  <div style=\"margin:5px;\"><a href=\"javascript:void(0);\" onclick=\"maildossier(3,3);\">Dossier à envoyer par mail</a></div>
  <div style=\"margin:10px;font-style:italic;\"> MEMO : dans le cas d'un droit de rétractation, le client doit envoyer une lettre par recommandé sous 7 jours à compter de la date de commande. De plus, c'est à lui d'organiser le rapatriement de la marchandise et le produit doit être envoyé dans son emballage d'origine et en parfait état. Si retour chez fabricant, bien leur demander de vérifier le colis dès réception</div>
  <div>
    Dossier complet envoyé le <input type=\"text\" name=\"sav_envoidossier\" id=\"sav_envoidossier\" value=\"".$savfields['sav_envoidossier']."\" onclick=\"calendrier_show(this.id,this.value)\"> par <input type=\"hidden\" name=\"sav_envoimail\" value=\"".intval($savfields['sav_envoimail'])."\"><input type=\"checkbox\" value=\"1\" ".($savfields['sav_envoimail']?"checked":"")." onclick=\"document.getElementsByName('sav_envoimail')[0].value=this.checked?'1':'0';\"> mail &nbsp; <input type=\"hidden\" name=\"sav_envoiposte\" value=\"".intval($savfields['sav_envoiposte'])."\"><input type=\"checkbox\"".($savfields['sav_envoiposte']?"checked":"")." onclick=\"document.getElementsByName('sav_envoiposte')[0].value=this.checked?'1':'0';\"> La Poste
  </div>
  <div style=\"margin-top:10px;\">
  Commentaires INTERNES :<br><textarea name=\"sav_com3\" cols=\"70\" rows=\"4\">".$savfields['sav_com3']."</textarea>".suivi(3)."
  </div>
  <div style=\"text-align:center;padding:10px;\"><input type=\"button\" onclick=\"document.forms.et3form.action='';document.forms.et3form.target='';document.forms.et3form.submit();\" value=\"Enregistrer\"></div>
  </form>
  </div>";

}

//4
if($maxstep>=3){
$steps[4][0]="<div class=\"advgroup\">
  <form name=\"et4form\" method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"4\">
  <p>Etape 4 : Accord conclu</p>
  ".maj(4)."
  <div id=\"edit4\" style=\"display:".($maxstep==3?"block":"none")."\">
      <table>
      <tr><td></td><td>Détails</td><td>Montant HT</td></tr>
      <tr><td>Client</td><td><input type=\"text\" name=\"sav_accordclient\" value=\"".$savfields['sav_accordclient']."\" size=\"50\"></td><td><input type=\"text\" name=\"sav_accordclientval\" value=\"".floatval($savfields['sav_accordclientval'])."\" size=\"5\"></td></tr>
      <tr><td>Acilyon</td><td><input type=\"text\" name=\"sav_accordacilyon\" value=\"".$savfields['sav_accordacilyon']."\" size=\"50\"></td><td><input type=\"text\" name=\"sav_accordacilyonval\" value=\"".floatval($savfields['sav_accordacilyonval'])."\" size=\"5\"></td></tr>
      <tr><td>Transporteur</td><td><input type=\"text\" name=\"sav_accordtrspt\" value=\"".$savfields['sav_accordtrspt']."\" size=\"50\"></td><td><input type=\"text\" name=\"sav_accordtrsptval\" value=\"".floatval($savfields['sav_accordtrsptval'])."\" size=\"5\"></td></tr>
      <tr><td>Installateur</td><td><input type=\"text\" name=\"sav_accordsls\" value=\"".$savfields['sav_accordsls']."\" size=\"50\"></td><td><input type=\"text\" name=\"sav_accordslsval\" value=\"".floatval($savfields['sav_accordslsval'])."\" size=\"5\"></td></tr>
      <tr><td>Fournisseur</td><td><input type=\"text\" name=\"sav_accordfourn\" value=\"".$savfields['sav_accordfourn']."\" size=\"50\"></td><td><input type=\"text\" name=\"sav_accordfournval\" value=\"".floatval($savfields['sav_accordfournval'])."\" size=\"5\"></td></tr>
      </table>
      <div style=\"margin-top:10px;text-align:right;\"><a href=\"javascript:void(0);\" onclick=\"mail_auto(undefined,undefined,undefined,subjectaccordconclu,mailaccordconclu);mail_suivi('sav_4','$savid');\">Mail de confirmation accord conclu</a></div>
      <div style=\"margin-top:10px;\">
      Commentaires INTERNES :<br><textarea name=\"sav_com4\" cols=\"70\" rows=\"4\">".$savfields['sav_com4']."</textarea>".suivi(4)."
      </div>
      <div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Enregistrer\"></div>
  </div>
  <div id=\"resum4\" style=\"margin-top:10px;display:".($maxstep>3?"block":"none")."\">
      <table>
      <tr><td></td><td>Détails</td><td>Montant HT</td></tr>
      <tr><td>Client</td><td>".$savfields['sav_accordclient']."</td><td>".floatval($savfields['sav_accordclientval'])."</td></tr>
      <tr><td>Acilyon</td><td>".$savfields['sav_accordacilyon']."</td><td>".floatval($savfields['sav_accordacilyonval'])."</td></tr>
      <tr><td>Transporteur</td><td>".$savfields['sav_accordtrspt']."</td><td>".floatval($savfields['sav_accordtrsptval'])."</td></tr>
      <tr><td>Installateur</td><td>".$savfields['sav_accordsls']."</td><td>".floatval($savfields['sav_accordslsval'])."</td></tr>
      <tr><td>Fournisseur</td><td>".$savfields['sav_accordfourn']."</td><td>".floatval($savfields['sav_accordfournval'])."</td></tr>
      </table>
      <div style=\"margin-top:10px;\">Commentaires</div>".suivi(4,false)."
      <input type=\"button\" value=\"Modifier\" onclick=\"modifstep(4);\">
  </div>
  </form>
  </div>";

$steps[4][1]="<div class=\"advgroup\">
  <form name=\"et4form\" method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"4\">
  <p>Etape 4 : Accord conclu</p>
  ".maj(4)."
  <table>
  <tr><td></td><td>Détails</td><td>Montant HT</td></tr>
  <tr><td>Client</td><td><input type=\"text\" name=\"sav_accordclient\" value=\"".$savfields['sav_accordclient']."\" size=\"50\"></td><td><input type=\"text\" name=\"sav_accordclientval\" value=\"".floatval($savfields['sav_accordclientval'])."\" size=\"5\"></td></tr>
  <tr><td>Acilyon</td><td><input type=\"text\" name=\"sav_accordacilyon\" value=\"".$savfields['sav_accordacilyon']."\" size=\"50\"></td><td><input type=\"text\" name=\"sav_accordacilyonval\" value=\"".floatval($savfields['sav_accordacilyonval'])."\" size=\"5\"></td></tr>
  <tr><td>Transporteur</td><td><input type=\"text\" name=\"sav_accordtrspt\" value=\"".$savfields['sav_accordtrspt']."\" size=\"50\"></td><td><input type=\"text\" name=\"sav_accordtrsptval\" value=\"".floatval($savfields['sav_accordtrsptval'])."\" size=\"5\"></td></tr>
  <tr><td>Installateur</td><td><input type=\"text\" name=\"sav_accordsls\" value=\"".$savfields['sav_accordsls']."\" size=\"50\"></td><td><input type=\"text\" name=\"sav_accordslsval\" value=\"".floatval($savfields['sav_accordslsval'])."\" size=\"5\"></td></tr>
  <tr><td>Fournisseur</td><td><input type=\"text\" name=\"sav_accordfourn\" value=\"".$savfields['sav_accordfourn']."\" size=\"50\"></td><td><input type=\"text\" name=\"sav_accordfournval\" value=\"".floatval($savfields['sav_accordfournval'])."\" size=\"5\"></td></tr>
  </table>
  <div>
  <input type=\"hidden\" name=\"sav_pecsav\" value=\"".intval($savfields['sav_pecsav'])."\"><input type=\"checkbox\" ".($savfields['sav_pecsav']=='1'?"checked":"")." onclick=\"document.getElementsByName('sav_pecsav')[0].value=this.checked?'1':'0';\"> Prise en charge panne par SAV fabricant - client renvoyé vers SAV fabricant
  <br><input type=\"hidden\" name=\"sav_pecsfg\" value=\"".intval($savfields['sav_pecsfg'])."\"><input type=\"checkbox\" ".($savfields['sav_pecsfg']=='1'?"checked":"")." onclick=\"document.getElementsByName('sav_pecsfg')[0].value=this.checked?'1':'0';\"> Prise  en charge panne par SFG - client renvoyé vers SFG
  </div>
  <div style=\"margin-top:10px;text-align:right;\"><a href=\"javascript:void(0);\" onclick=\"mail_auto(undefined,undefined,undefined,subjectaccordconclu,mailaccordconclu);mail_suivi('sav_4','$savid');\">Mail de confirmation accord conclu</a></div>
  <div style=\"margin-top:10px;\">
  Commentaires INTERNES :<br><textarea name=\"sav_com4\" cols=\"70\" rows=\"4\">".$savfields['sav_com4']."</textarea>".suivi(4)."
  </div>
  <div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Enregistrer\"></div>
  </form>
  </div>";

$steps[4][2].="<div class=\"advgroup\">
  <form name=\"et4form\" method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"4\">
  <p>Etape 4 : Réponse du fournisseur / Action</p>
  ".maj(4)."
  <table style=\"margin-top:10px;\">
  <tr><td colspan=\"2\"><b>Confirmation envoi des produits manquants</b> <input type=\"text\" name=\"sav_dateenvoiprod\" id=\"dateenvoiprod\" size=\"10\" onclick=\"calendrier_show(this.id,this.value);\" readonly></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_prodremp\" value=\"".intval($savfields['sav_prodremp'])."\"><input type=\"checkbox\" onclick=\"document.getElementsByName('sav_prodremp')[0].value=this.checked?'1':'0';\"".($savfields['sav_prodremp']=="1"?" checked":"")."> Produit remplacé facturé</td><td><input type=\"text\" name=\"sav_val240\" value=\"".floatval($savfields['sav_val240'])."\" onblur=\"calcsum24();\" size=\"5\"></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_rappprod\" value=\"".intval($savfields['sav_rappprod'])."\"><input type=\"checkbox\" onclick=\"document.getElementsByName('sav_rappprod')[0].value=this.checked?'1':'0';\"".($savfields['sav_rappprod']=="1"?" checked":"")."> Rapatriement des produits à notre charge</td><td><input type=\"text\" name=\"sav_val241\" value=\"".floatval($savfields['sav_val241'])."\" onblur=\"calcsum24();\" size=\"5\"></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_trsptfact\" value=\"".intval($savfields['sav_trsptfact'])."\"><input type=\"checkbox\" onclick=\"document.getElementsByName('sav_trsptfact')[0].value=this.checked?'1':'0';\"".($savfields['sav_trsptfact']=="1"?" checked":"")."> Transport facturé</td><td><input type=\"text\" name=\"sav_val242\" value=\"".floatval($savfields['sav_val242'])."\" onblur=\"calcsum24();\" size=\"5\"></td></tr>
  <tr><td>Total :</td><td colspan=\"2\" id=\"resultsum4\" align=\"right\"></td></tr>
  </table>
  <br><a href=\"facture.php?action=creer&createfrom=".$facturid."\" target=\"_blank\">Création nouvelle commande</a> &nbsp; <a href=\"facture.php?action=creer&createfromuser=".$facturid."\" target=\"_blank\">Création avoir</a>
  <div style=\"margin-top:10px;\">
  Commentaires INTERNES :<br><textarea name=\"sav_com4\" cols=\"70\" rows=\"4\">".$savfields['sav_com4']."</textarea>".suivi(4)."
  </div>
  <div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Enregistrer\"></div>
  </form>
  </div>";

$steps[4][3]="<div class=\"advgroup\">
  <form name=\"et4form\" method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"4\">
  <p>Etape 4 : Analyse du retour produit - Accord remboursement</p>
  ".maj(4)."
  <div style=\"margin-top:10px;\">
  <b>Retour produit le</b> <input type=\"text\" id=\"sav_dateretour\" name=\"sav_dateretour\" value=\"".$savfields['sav_dateretour']."\" size=\"10\" onclick=\"calendrier_show(this.id,this.value);\" readonly>
  <br><input type=\"hidden\" name=\"sav_pasretour\" value=\"".intval($savfields['sav_pasretour'])."\"><input type=\"checkbox\" onclick=\"document.getElementsByName('sav_pasretour')[0].value=this.checked?'1':'0';\"".($savfields['sav_pasretour']=="1"?" checked":"").">  Pas de retour produit (pas de commande passée chez fournisseur)
  </div>
  <table>
  <tr><td>Analyse produit</td><td><input type=\"hidden\" name=\"sav_etatprod\" value=\"".intval($savfields['sav_etatprod'])."\"><input type=\"radio\" onclick=\"document.getElementsByName('sav_etatprod')[0].value='1';\"".($savfields['sav_etatprod']=="1"?"checked":"")."> Parfait état</td><td><input type=\"radio\" onclick=\"document.getElementsByName('sav_etatprod')[0].value='1';\"".($savfields['sav_etatprod']=="2"?"checked":"")."> Endommagé</td></tr>
  <tr><td>Remboursement client</td><td><input type=\"hidden\" name=\"sav_rembclient\" value=\"".intval($savfields['sav_rembclient'])."\"><input type=\"radio\" name=\"rembclient\" onclick=\"document.getElementsByName('sav_rembclient')[0].value='1';\"".($savfields['sav_rembclient']=="1"?"checked":"")."> Oui</td><td><input type=\"radio\" name=\"rembclient\" onclick=\"document.getElementsByName('sav_rembclient')[0].value='1';\"".($savfields['sav_rembclient']=="2"?"checked":"")."> Non</td></tr>
  <tr><td>Client remboursé le</td><td><input type=\"text\" name=\"sav_daterembourse\" value=\"".$savfields['sav_daterembourse']."\" onclick=\"calendrier_show(this.id,this.value);\" readonly></td></tr>
  <tr><td>Montant à rembourser</td><td><input type=\"text\" name=\"sav_montantremb\" value=\"".floatval($savfields['sav_montantremb'])."\">&euro;</td></tr>
  <tr><td>Remboursement fournisseur</td><td><input type=\"hidden\" name=\"sav_rembfourn\" value=\"".intval($savfields['sav_rembfourn'])."\"><input type=\"radio\" name=\"rembfourn\" onclick=\"document.getElementsByName('sav_rembfourn')[0].value='1';\"".($savfields['sav_rembfourn']=="1"?"checked":"")."> Oui</td><td><input type=\"radio\" name=\"rembfourn\" onclick=\"document.getElementsByName('sav_rembfourn')[0].value='1';\"".($savfields['sav_rembfourn']=="2"?"checked":"")."> Non</td></tr>
  </table>
  <div style=\"margin-top:10px;\">
    <a href=\"facture.php?action=creer&createfrom=".$facturid."\" target=\"_blank\">Création nouvelle commande</a> &nbsp; <a href=\"facture.php?action=creer&createfromuser=".$facturid."\" target=\"_blank\">Création avoir</a>";
  
  $r=$DB_site2->query("SELECT f.facturid,u.raisonsociale FROM facture f INNER JOIN utilisateur u ON (u.userid=f.userid) WHERE f.provenance='sav' AND f.provenanceid=$facturid");
  while($jfacture=$DB_site2->fetch_array($r)){
      $steps[4][3].="<br>- <a href=\"facture.php?action=editer&facturid=".$jfacture['facturid']."\" target=\"_blank\">Commande n°".$jfacture['facturid']." ".$jfacture['raisonsociale']."</a>";
  }
  $steps[4][3].="</div>
  <div style=\"margin-top:10px;\">
  Commentaires INTERNES :<br><textarea name=\"sav_com4\" cols=\"70\" rows=\"4\">".$savfields['sav_com4']."</textarea>".suivi(4)."
  </div>
  <div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Enregistrer\"></div>
  </form>
  </div>";

}

//5
if($maxstep>=4){
$steps[5][0]="<div class=\"advgroup\">
  <form name=\"et5form\" method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"5\">
  <p>Etape 5 : Actions</p>
  ".maj(5)."
  <div id=\"edit5\" style=\"display:".($maxstep==4?"block":"none")."\">
      <table>
      <tr><td>Analyse produit</td><td></td><td>Montant HT</td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk5a0\" value=\"".intval($savfields['sav_chk5a0'])."\"><input type=\"checkbox\" id=\"chk5a0\" onclick=\"calcsum5()\" value=\"1\"".($savfields['sav_chk5a0']=="1"?" checked":"")."> Remplacement produit (+ récupération ancien)</td><td><select name=\"sav_fourn5a0\">".fournopts($savfields['sav_fourn5a0'])."</select></td><td><input type=\"text\" name=\"sav_val5a0\" value=\"".floatval($savfields['sav_val5a0'])."\" onblur=\"calcsum5()\" size=\"5\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk5a1\" value=\"".intval($savfields['sav_chk5a1'])."\"><input type=\"checkbox\" id=\"chk5a1\" onclick=\"calcsum5()\" value=\"1\"".($savfields['sav_chk5a1']=="1"?" checked":"")."> Remplacement produit (sans récupération ancien)</td><td><select name=\"sav_fourn5a1\">".fournopts($savfields['sav_fourn5a1'])."</select></td><td><input type=\"text\" name=\"sav_val5a1\" value=\"".floatval($savfields['sav_val5a1'])."\" onblur=\"calcsum5()\" size=\"5\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk5a2\" value=\"".intval($savfields['sav_chk5a2'])."\"><input type=\"checkbox\" id=\"chk5a2\" onclick=\"calcsum5()\" value=\"1\"".($savfields['sav_chk5a2']=="1"?" checked":"")."> Retour produit chez transporteur</td><td><select name=\"sav_fourn5a2\">".fournopts($savfields['sav_fourn5a2'])."</select></td><td><input type=\"text\" name=\"sav_val5a2\" value=\"".floatval($savfields['sav_val5a2'])."\" onblur=\"calcsum5()\" size=\"5\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk5a3\" value=\"".intval($savfields['sav_chk5a3'])."\"><input type=\"checkbox\" id=\"chk5a3\" onclick=\"calcsum5()\" value=\"1\"".($savfields['sav_chk5a3']=="1"?" checked":"")."> Retour produit chez fournisseur</td><td><select name=\"sav_fourn5a3\">".fournopts($savfields['sav_fourn5a3'])."</select></td><td><input type=\"text\" name=\"sav_val5a3\" value=\"".floatval($savfields['sav_val5a3'])."\" onblur=\"calcsum5()\" size=\"5\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk5a4\" value=\"".intval($savfields['sav_chk5a4'])."\"><input type=\"checkbox\" id=\"chk5a4\" onclick=\"calcsum5()\" value=\"1\"".($savfields['sav_chk5a4']=="1"?" checked":"")."> Retour produit chez Acilyon</td><td><select name=\"sav_fourn5a4\">".fournopts($savfields['sav_fourn5a4'])."</select></td><td><input type=\"text\" name=\"sav_val5a4\" value=\"".floatval($savfields['sav_val5a4'])."\" onblur=\"calcsum5()\" size=\"5\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk5a5\" value=\"".intval($savfields['sav_chk5a5'])."\"><input type=\"checkbox\" id=\"chk5a5\" onclick=\"calcsum5()\" value=\"1\"".($savfields['sav_chk5a5']=="1"?" checked":"")."> Retour produit plateforme transporteur Lyon</td><td><select name=\"sav_fourn5a5\">".fournopts($savfields['sav_fourn5a5'])."</select></td><td><input type=\"text\" name=\"sav_val5a5\" value=\"".floatval($savfields['sav_val5a5'])."\" onblur=\"calcsum5()\" size=\"5\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk5a6\" value=\"".intval($savfields['sav_chk5a6'])."\"><input type=\"checkbox\" id=\"chk5a6\" onclick=\"calcsum5()\" value=\"1\"".($savfields['sav_chk5a6']=="1"?" checked":"")."> Geste commercial</td><td><select name=\"sav_fourn5a6\">".fournopts($savfields['sav_fourn5a6'])."</select></td><td><input type=\"text\" name=\"sav_val5a6\" value=\"".floatval($savfields['sav_val5a6'])."\" onblur=\"calcsum5()\" size=\"5\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk5a7\" value=\"".intval($savfields['sav_chk5a7'])."\"><input type=\"checkbox\" id=\"chk5a7\" onclick=\"calcsum5()\" value=\"1\"".($savfields['sav_chk5a7']=="1"?" checked":"")."> Geste commercial</td><td><select name=\"sav_fourn5a7\">".fournopts($savfields['sav_fourn5a7'])."</select></td><td><input type=\"text\" name=\"sav_val5a7\" value=\"".floatval($savfields['sav_val5a7'])."\" onblur=\"calcsum5()\" size=\"5\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk5a8\" value=\"".intval($savfields['sav_chk5a8'])."\"><input type=\"checkbox\" id=\"chk5a8\" onclick=\"calcsum5()\" value=\"1\"".($savfields['sav_chk5a8']=="1"?" checked":"")."> Relivraison avec geste commercial</td><td><select name=\"sav_fourn5a8\">".fournopts($savfields['sav_fourn5a8'])."</select></td><td><input type=\"text\" name=\"sav_val5a8\" value=\"".floatval($savfields['sav_val5a8'])."\" onblur=\"calcsum5()\" size=\"5\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk5a9\" value=\"".intval($savfields['sav_chk5a9'])."\"><input type=\"checkbox\" id=\"chk5a9\" onclick=\"calcsum5()\" value=\"1\"".($savfields['sav_chk5a9']=="1"?" checked":"")."> Réparation chez client couvert par transporteur</td><td><select name=\"sav_fourn5a9\">".fournopts($savfields['sav_fourn5a9'])."</select></td><td><input type=\"text\" name=\"sav_val5a9\" value=\"".floatval($savfields['sav_val5a9'])."\" onblur=\"calcsum5()\" size=\"5\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk5a10\" value=\"".intval($savfields['sav_chk5a10'])."\"><input type=\"checkbox\" id=\"chk5a10\" onclick=\"calcsum5()\" value=\"1\"".($savfields['sav_chk5a10']=="1"?" checked":"")."> Réparation chez fournisseur couvert par transporteur</td><td><select name=\"sav_fourn5a10\">".fournopts($savfields['sav_fourn5a10'])."</select></td><td><input type=\"text\" name=\"sav_val5a10\" value=\"".floatval($savfields['sav_val5a10'])."\" onblur=\"calcsum5()\" size=\"5\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk5a11\" value=\"".intval($savfields['sav_chk5a11'])."\"><input type=\"checkbox\" id=\"chk5a11\" onclick=\"calcsum5()\" value=\"1\"".($savfields['sav_chk5a11']=="1"?" checked":"")."> Remboursement</td><td><select name=\"sav_fourn5a11\">".fournopts($savfields['sav_fourn5a11'])."</select></td><td><input type=\"text\" name=\"sav_val5a11\" value=\"".floatval($savfields['sav_val5a11'])."\" onblur=\"calcsum5()\" size=\"5\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk5a12\" value=\"".intval($savfields['sav_chk5a12'])."\"><input type=\"checkbox\" id=\"chk5a12\" onclick=\"calcsum5()\" value=\"1\"".($savfields['sav_chk5a12']=="1"?" checked":"")."> Cadeau <input type=\"text\" name=\"sav_kdos5\" value=\"".$savfields['sav_kdos5']."\"></td><td><select name=\"sav_fourn5a12\">".fournopts($savfields['sav_fourn5a12'])."</select></td><td><input type=\"text\" name=\"sav_val5a12\" value=\"".floatval($savfields['sav_val5a12'])."\" onblur=\"calcsum5()\" size=\"5\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk5a13\" value=\"".intval($savfields['sav_chk5a13'])."\"><input type=\"checkbox\" id=\"chk5a13\" onclick=\"calcsum5()\" value=\"1\"".($savfields['sav_chk5a13']=="1"?" checked":"")."> Autre <input type=\"text\" name=\"sav_autre5\" value=\"".$savfields['sav_autre5']."\"></td><td><select name=\"sav_fourn5a13\">".fournopts($savfields['sav_fourn5a13'])."</select></td><td><input type=\"text\" name=\"sav_val5a13\" value=\"".floatval($savfields['sav_val5a13'])."\" onblur=\"calcsum5()\" size=\"5\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk5a14\" value=\"".intval($savfields['sav_chk5a14'])."\"><input type=\"checkbox\" id=\"chk5a14\" onclick=\"calcsum5()\" value=\"1\"".($savfields['sav_chk5a14']=="1"?" checked":"")."> Transport aller</td><td><select name=\"sav_fourn5a14\">".fournopts($savfields['sav_fourn5a14'])."</select></td><td><input type=\"text\" name=\"sav_val5a14\" value=\"".floatval($savfields['sav_val5a14'])."\" onblur=\"calcsum5()\" size=\"5\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk5a15\" value=\"".intval($savfields['sav_chk5a15'])."\"><input type=\"checkbox\" id=\"chk5a15\" onclick=\"calcsum5()\" value=\"1\"".($savfields['sav_chk5a15']=="1"?" checked":"")."> Transport retour</td><td><select name=\"sav_fourn5a15\">".fournopts($savfields['sav_fourn5a15'])."</select></td><td><input type=\"text\" name=\"sav_val5a15\" value=\"".floatval($savfields['sav_val5a15'])."\" onblur=\"calcsum5()\" size=\"5\"></td></tr>
      <tr><td><input type=\"hidden\" name=\"sav_chk5a16\" value=\"".intval($savfields['sav_chk5a16'])."\"><input type=\"checkbox\" id=\"chk5a16\" onclick=\"calcsum5()\" value=\"1\"".($savfields['sav_chk5a16']=="1"?" checked":"")."> Aucune suite à donner</td><td><select name=\"sav_fourn5a16\">".fournopts($savfields['sav_fourn5a16'])."</select></td><td><input type=\"text\" name=\"sav_val5a16\" value=\"".floatval($savfields['sav_val5a16'])."\" onblur=\"calcsum5()\" size=\"5\"></td></tr>
      <tr><td>Analyse du coût total prévisionnel du litige</td><td colspan=\"2\" id=\"resultsum5\" align=\"right\"></td></tr>
      </table>
      <div style=\"margin-top:10px;\">
	<a href=\"facture.php?action=creer&createfrom=".$facturid."\" target=\"_blank\">Création nouvelle commande</a> &nbsp; <a href=\"facture.php?action=creer&createfromuser=".$facturid."\" target=\"_blank\">Création avoir</a>";
      
      $r=$DB_site2->query("SELECT f.facturid,u.raisonsociale FROM facture f INNER JOIN utilisateur u ON (u.userid=f.userid) WHERE f.provenance='sav' AND f.provenanceid=$facturid");
      while($jfacture=$DB_site2->fetch_array($r)){
	  $steps[5][0].="<br>- <a href=\"facture.php?action=editer&facturid=".$jfacture['facturid']."\" target=\"_blank\">Commande n°".$jfacture['facturid']." ".$jfacture['raisonsociale']."</a>";
      }
      $steps[5][0].="</div>
      <div style=\"margin-top:10px;\">
	<br>Commentaires INTERNES :<br><textarea name=\"sav_com5\" cols=\"70\" rows=\"4\">".$savfields['sav_com5']."</textarea>".suivi(5)."
      </div>
      <div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Enregistrer\"></div>
  </div>
  <div id=\"resum5\" style=\"margin-top:10px;display:".($maxstep>4?"block":"none")."\">
      <table>
      <tr><td>Analyse produit</td><td>Montant HT</td></tr>";
      if(intval($savfields['sav_chk5a0'])==1) $steps[5][0].="Remplacement produit (+ récupération ancien)</td><td>".$savfields['sav_fourn5a0']."</td><td>".floatval($savfields['sav_val5a0'])."</td></tr>";
      if(intval($savfields['sav_chk5a1'])==1) $steps[5][0].="Remplacement produit (sans récupération ancien)</td><td>".$savfields['sav_fourn5a1']."</td><td>".floatval($savfields['sav_val5a1'])."</td></tr>";
      if(intval($savfields['sav_chk5a2'])==1) $steps[5][0].="Retour produit chez transporteur</td><td>".$savfields['sav_fourn5a2']."</td><td>".floatval($savfields['sav_val5a2'])."</td></tr>";
      if(intval($savfields['sav_chk5a3'])==1) $steps[5][0].="Retour produit chez fournisseur</td><td>".$savfields['sav_fourn5a3']."</td><td>".floatval($savfields['sav_val5a3'])."</td></tr>";
      if(intval($savfields['sav_chk5a4'])==1) $steps[5][0].="Retour produit chez Acilyon</td><td>".$savfields['sav_fourn5a4']."</td><td>".floatval($savfields['sav_val5a4'])."</td></tr>";
      if(intval($savfields['sav_chk5a5'])==1) $steps[5][0].="Retour produit plateforme transporteur Lyon</td><td>".$savfields['sav_fourn5a5']."</td><td>".floatval($savfields['sav_val5a5'])."</td></tr>";
      if(intval($savfields['sav_chk5a6'])==1) $steps[5][0].="Geste commercial</td><td>".$savfields['sav_fourn5a6']."</td><td>".floatval($savfields['sav_val5a6'])."</td></tr>";
      if(intval($savfields['sav_chk5a7'])==1) $steps[5][0].="Geste commercial</td><td>".$savfields['sav_fourn5a7']."</td><td>".floatval($savfields['sav_val5a7'])."</td></tr>";
      if(intval($savfields['sav_chk5a8'])==1) $steps[5][0].="Relivraison avec geste commercial</td><td>".$savfields['sav_fourn5a8']."</td><td>".floatval($savfields['sav_val5a8'])."</td></tr>";
      if(intval($savfields['sav_chk5a9'])==1) $steps[5][0].="Réparation chez client couvert par transporteur</td><td>".$savfields['sav_fourn5a9']."</td><td>".floatval($savfields['sav_val5a9'])."</td></tr>";
      if(intval($savfields['sav_chk5a10'])==1) $steps[5][0].="Réparation chez fournisseur couvert par transporteur</td><td>".$savfields['sav_fourn5a10']."</td><td>".floatval($savfields['sav_val5a10'])."</td></tr>";
      if(intval($savfields['sav_chk5a11'])==1) $steps[5][0].="Remboursement</td><td>".$savfields['sav_fourn5a11']."</td><td>".floatval($savfields['sav_val5a11'])."</td></tr>";
      if(intval($savfields['sav_chk5a12'])==1) $steps[5][0].="Cadeau : ".$savfields['sav_kdos5']."</td><td>".$savfields['sav_fourn5a12']."</td><td>".floatval($savfields['sav_val5a12'])."</td></tr>";
      if(intval($savfields['sav_chk5a13'])==1) $steps[5][0].="Autre : ".$savfields['sav_autre5']."</td><td>".$savfields['sav_fourn5a13']."</td><td>".floatval($savfields['sav_val5a13'])."</td></tr>";
      if(intval($savfields['sav_chk5a14'])==1) $steps[5][0].="Transport aller</td><td>".$savfields['sav_fourn5a14']."</td><td>".floatval($savfields['sav_val5a14'])."</td></tr>";
      if(intval($savfields['sav_chk5a15'])==1) $steps[5][0].="Transport retour</td><td>".$savfields['sav_fourn5a15']."</td><td>".floatval($savfields['sav_val5a15'])."</td></tr>";
      if(intval($savfields['sav_chk5a16'])==1) $steps[5][0].="Aucune suite à donner</td><td>".$savfields['sav_fourn5a16']."</td><td>".floatval($savfields['sav_val5a16'])."</td></tr>";
      $steps[5][0].="</table>
      <div style=\"margin-top:10px;\">";
      $r=$DB_site2->query("SELECT f.facturid,u.raisonsociale FROM facture f INNER JOIN utilisateur u ON (u.userid=f.userid) WHERE f.provenance='sav' AND f.provenanceid=$facturid");
      while($jfacture=$DB_site2->fetch_array($r)){
	  $steps[5][0].="<br>- <a href=\"facture.php?action=editer&facturid=".$jfacture['facturid']."\" target=\"_blank\">Commande n°".$jfacture['facturid']." ".$jfacture['raisonsociale']."</a>";
      }
      $steps[5][0].="</div>
      <div style=\"margin-top:10px;\">Commentaires</div>".suivi(5,false)."
      <input type=\"button\" value=\"Modifier\" onclick=\"modifstep(5);\">
  </div>
  </form>
  </div>";

  $liensfg="";
  if(file_exists("sfg_pdf/$facturid.pdf")) $liensfg="<a href=\"sfg_pdf/$facturid.pdf\">Contrat SFG</a> <a href=\"javascript:void(0);\" onclick=\"mail_auto(undefined,'$rs_facture[mail]',undefined,subjectcontratsfg,mailcontratsfg);addserverpj('Facture SFG $facturid.pdf','sfg_pdf/$facturid.pdf');mail_suivi('sav_5','$savid');\">Envoi par mail contrat SFG au client</a>";
  $steps[5][1]="<div class=\"advgroup\">
  <form name=\"et5form\" method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"5\">
  <p>Etape 5 : Actions</p>
  ".maj(5)."
  <b>Détermination action et responsabilité</b>
  <table>
  <tr><td><input type=\"hidden\" name=\"sav_chk520\" value=\"".intval($savfields['sav_chk520'])."\"><input type=\"checkbox\" id=\"chk520\" value=\"1\" onclick=\"calcsum52()\"".($savfields['sav_chk520']=="1"?" checked":"")."> Remplacement produit (+ récupération ancien)</td><td><select name=\"sav_fourn520\">".fournopts($savfields['sav_fourn520'])."</select></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk521\" value=\"".intval($savfields['sav_chk521'])."\"><input type=\"checkbox\" id=\"chk521\" value=\"1\" onclick=\"calcsum52()\"".($savfields['sav_chk521']=="1"?" checked":"")."> Echange produit (+ récupération ancien)</td><td><select name=\"sav_fourn521\">".fournopts($savfields['sav_fourn521'])."</select></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk522\" value=\"".intval($savfields['sav_chk522'])."\"><input type=\"checkbox\" id=\"chk522\" value=\"1\" onclick=\"calcsum52()\"".($savfields['sav_chk522']=="1"?" checked":"")."> Récupération produit suite annulation</td><td><select name=\"sav_fourn522\">".fournopts($savfields['sav_fourn522'])."</select></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk523\" value=\"".intval($savfields['sav_chk523'])."\"><input type=\"checkbox\" id=\"chk523\" value=\"1\" onclick=\"calcsum52()\"".($savfields['sav_chk523']=="1"?" checked":"")."> Geste commercial<br>montant et prise en charge : <input type=\"text\" name=\"sav_geste523\" value=\"".$savfields['sav_geste523']."\"></td><td><select name=\"sav_fourn523\">".fournopts($savfields['sav_fourn523'])."</select></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk524\" value=\"".intval($savfields['sav_chk524'])."\"><input type=\"checkbox\" id=\"chk524\" value=\"1\" onclick=\"calcsum52()\"".($savfields['sav_chk524']=="1"?" checked":"")."> Geste commercial<br>montant et prise en charge : <input type=\"text\" name=\"sav_geste524\" value=\"".$savfields['sav_geste524']."\"></td><td><select name=\"sav_fourn524\">".fournopts($savfields['sav_fourn524'])."</select></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk525\" value=\"".intval($savfields['sav_chk525'])."\"><input type=\"checkbox\" id=\"chk525\" value=\"1\" onclick=\"calcsum52()\"".($savfields['sav_chk525']=="1"?" checked":"")."> Remboursement<br>raison : <input type=\"text\" name=\"sav_raison525\" value=\"".$savfields['sav_raison525']."\"></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk526\" value=\"".intval($savfields['sav_chk526'])."\"><input type=\"checkbox\" id=\"chk526\" value=\"1\" onclick=\"calcsum52()\"".($savfields['sav_chk526']=="1"?" checked":"")."> Aucune suite, dossier à solder<br>raison : <input type=\"text\" name=\"sav_raison526\" value=\"".$savfields['sav_raison526']."\"></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk527\" value=\"".intval($savfields['sav_chk527'])."\"><input type=\"checkbox\" id=\"chk527\" value=\"1\" onclick=\"calcsum52()\"".($savfields['sav_chk527']=="1"?" checked":"")."> Réparation chez client</td><td><select name=\"sav_fourn527\">".fournopts($savfields['sav_fourn527'])."</select></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk528\" value=\"".intval($savfields['sav_chk528'])."\"><input type=\"checkbox\" id=\"chk528\" value=\"1\" onclick=\"calcsum52()\"".($savfields['sav_chk528']=="1"?" checked":"")."> Réparation chez constructeur</td><td><select name=\"sav_fourn528\">".fournopts($savfields['sav_fourn528'])."</select></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk529\" value=\"".intval($savfields['sav_chk529'])."\"><input type=\"checkbox\" id=\"chk529\" value=\"1\" onclick=\"calcsum52()\"".($savfields['sav_chk529']=="1"?" checked":"")."> Autre <input type=\"text\" name=\"sav_autre52\" value=\"".$savfields['sav_autre52']."\"></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk5210\" value=\"".intval($savfields['sav_chk5210'])."\"><input type=\"checkbox\" id=\"chk5210\" value=\"1\" onclick=\"calcsum52()\"".($savfields['sav_chk5210']=="1"?" checked":"")."> Prise en charge panne par SAV fabricant - client renvoyé vers SAV fabricant, Voir étape 7 directement</td><td><select name=\"sav_fourn5210\">".fournopts($savfields['sav_fourn5210'])."</select></td></tr>
  <tr><td><input type=\"hidden\" name=\"sav_chk5211\" value=\"".intval($savfields['sav_chk5211'])."\"><input type=\"checkbox\" id=\"chk5211\" value=\"1\" onclick=\"calcsum52()\"".($savfields['sav_chk5211']=="1"?" checked":"")."> Prise en charge panne par SFG - client renvoyé vers SFG</td></tr>
  </table>
  <br><b>Analyse du coût prévisionnel du litige</b>
  <table>
  <tr><td>Prix achat cave</td><td><input type=\"text\" name=\"sav_val520\" value=\"".floatval($savfields['sav_val520'])."\" size=\"5\" onblur=\"calcsum52()\"></td></tr>
  <tr><td>Réparation</td><td><input type=\"text\" name=\"sav_val521\" value=\"".floatval($savfields['sav_val521'])."\" size=\"5\" onblur=\"calcsum52()\"></td></tr>
  <tr><td>Transport aller</td><td><input type=\"text\" name=\"sav_val522\" value=\"".floatval($savfields['sav_val522'])."\" size=\"5\" onblur=\"calcsum52()\"></td></tr>
  <tr><td>Transport retour</td><td><input type=\"text\" name=\"sav_val523\" value=\"".floatval($savfields['sav_val523'])."\" size=\"5\" onblur=\"calcsum52()\"></td></tr>
  <tr><td>Geste commercial</td><td><input type=\"text\" name=\"sav_val524\" value=\"".floatval($savfields['sav_val524'])."\" size=\"5\" onblur=\"calcsum52()\"></td></tr>
  <tr><td>Geste commercial</td><td><input type=\"text\" name=\"sav_val525\" value=\"".floatval($savfields['sav_val525'])."\" size=\"5\" onblur=\"calcsum52()\"></td></tr>
  <tr><td>Remboursement</td><td><input type=\"text\" name=\"sav_val526\" value=\"".floatval($savfields['sav_val526'])."\" size=\"5\" onblur=\"calcsum52()\"></td></tr>
  <tr><td>Autre : <input type=\"text\" name=\"sav_autreval52\" value=\"".$savfields['sav_autreval52']."\"></td><td><input type=\"text\" name=\"sav_val527\" value=\"".floatval($savfields['sav_val527'])."\" size=\"5\" onblur=\"calcsum52()\"></td></tr>
  <tr><td>Total HT : </td><td id=\"resultsum52\" align=\"right\"></td></tr>
  </table>
  <div style=\"margin-top:10px;\">
    <a href=\"facture.php?action=creer&createfrom=".$facturid."\" target=\"_blank\">Création nouvelle commande</a> &nbsp; <a href=\"facture.php?action=creer&createfromuser=".$facturid."\" target=\"_blank\">Création avoir</a> &nbsp; ".$liensfg;
  
  $r=$DB_site2->query("SELECT f.facturid,u.raisonsociale FROM facture f INNER JOIN utilisateur u ON (u.userid=f.userid) WHERE f.provenance='sav' AND f.provenanceid=$facturid");
  while($jfacture=$DB_site2->fetch_array($r)){
      $steps[5][1].="<br>- <a href=\"facture.php?action=editer&facturid=".$jfacture['facturid']."\" target=\"_blank\">Commande n°".$jfacture['facturid']." ".$jfacture['raisonsociale']."</a>";
  }

  $steps[5][1].="<div style=\"margin-top:10px;\">
    <br>Commentaires INTERNES :<br><textarea name=\"sav_com5\" cols=\"70\" rows=\"4\">".$savfields['sav_com5']."</textarea>".suivi(5)."
  </div>
  <div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Enregistrer\"></div>
  </form>
  </div>";

  $steps[5][2]="<div class=\"advgroup\">
  <form name=\"et5form\" method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"5\">
  <p>Etape 5 : Cloture du dossier litige</p>
  ".maj(5);
  $r=$DB_site2->query("SELECT f.facturid,u.raisonsociale,f.valide FROM facture f INNER JOIN utilisateur u ON (u.userid=f.userid) WHERE f.provenance='sav' AND f.provenanceid=$facturid");
  while($jfacture=$DB_site2->fetch_array($r)){
      $steps[5][2].="<br>- <a href=\"facture.php?action=editer&facturid=".$jfacture['facturid']."\" target=\"_blank\">Commande n°".$jfacture['facturid']." ".$jfacture['raisonsociale']."</a> ".$status[$jfacture['valide']];
  }
  $steps[5][2].="<div style=\"margin-top:10px;\">
  Confirmation fournisseur que le dossier a été soldé : <input type=\"text\" name=\"sav_datedossiersolde\" id=\"sav_datedossiersolde\" value=\"".$savfields['sav_datedossiersolde']."\" onclick=\"calendrier_show(this.id,this.value)\" readonly>
  <br>si remboursement client (suite à geste commercial),<br>client remboursé le : <input type=\"text\" name=\"sav_daterembourse\" id=\"sav_daterembourse\" value=\"".$savfields['sav_daterembourse']."\" onclick=\"calendrier_show(this.id,this.value)\" readonly>
  <br><br>Dossier clos le <input type=\"text\" name=\"sav_dateclos\" id=\"sav_dateclos\" value=\"".$savfields['sav_dateclos']."\" onclick=\"calendrier_show(this.id,this.value)\" readonly>
  </div>
  <div style=\"margin-top:10px;\">
    Commentaires INTERNES :<br><textarea name=\"sav_com5\" cols=\"70\" rows=\"4\">".$savfields['sav_com5']."</textarea>".suivi(5)."
  </div>
  <div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Enregistrer\"></div>
  </form>
  </div>";

  $steps[5][3]="<div class=\"advgroup\">
  <form name=\"et5form\" method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"5\">
  <p>Etape 5 : Cloture du dossier litige</p>
  ".maj(5);
  $r=$DB_site2->query("SELECT f.facturid,u.raisonsociale,f.valide FROM facture f INNER JOIN utilisateur u ON (u.userid=f.userid) WHERE f.provenance='sav' AND f.provenanceid=$facturid");
  while($jfacture=$DB_site2->fetch_array($r)){
      $steps[5][3].="<br>- <a href=\"facture.php?action=editer&facturid=".$jfacture['facturid']."\" target=\"_blank\">Commande n°".$jfacture['facturid']." ".$jfacture['raisonsociale']."</a> ".$status[$jfacture['valide']];
  }
  $steps[5][3].="<br><br>Dossier clos le <input type=\"text\" name=\"sav_dateclos\" id=\"sav_dateclos\" value=\"".$savfields['sav_dateclos']."\" onclick=\"calendrier_show(this.id,this.value)\" readonly>
  </div>
  <div style=\"margin-top:10px;\">
    Commentaires INTERNES :<br><textarea name=\"sav_com5\" cols=\"70\" rows=\"4\">".$savfields['sav_com5']."</textarea>".suivi(5)."
  </div>
  <div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Enregistrer\"></div>
  </form>
  </div>";

}

//6
if($maxstep>=5){
$js="";
$steps[6][0]="<div class=\"advgroup\">
  <form name=\"et6form\" method=\"post\" enctype=\"multipart/form-data\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"6\">
  <p>Etape 6 : Montage du dossier litige</p>
  ".maj(6)."
  <div id=\"edit6\" style=\"display:".($maxstep==5?"block":"none")."\">
      <br><b>Pièces à joindre au dossier :</b>
      <div>
	<input type=\"hidden\" name=\"sav_factureacilyon\" value=\"".intval($savfields['sav_factureacilyon'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_factureacilyon']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_factureacilyon')[0].value=this.checked?'1':'0';\"> Facture Acilyon
	<br><input type=\"hidden\" name=\"sav_facturefourn\" value=\"".intval($savfields['sav_facturefourn'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_facturefourn']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_facturefourn')[0].value=this.checked?'1':'0';\"> Copie facture fournisseur
	<br><input type=\"hidden\" name=\"sav_factureclient\" value=\"".intval($savfields['sav_factureclient'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_factureclient']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_factureclient')[0].value=this.checked?'1':'0';\"> <a href=\"../facture2.php?facturid=$facturid&passe=$rs_facture[passefacture]\">Facture client Ma Cave à Vin</a>
	<br><input type=\"hidden\" name=\"sav_lettreclient\" value=\"".intval($savfields['sav_lettreclient'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_lettreclient']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_lettreclient')[0].value=this.checked?'1':'0';\"> Lettre client
	<br><input type=\"hidden\" name=\"sav_attestation\" value=\"".intval($savfields['sav_attestation'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_attestation']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_attestation')[0].value=this.checked?'1':'0';\"> Facultatif : attestation sur l'honneur (en cas perte colis)
	<br><input type=\"hidden\" name=\"sav_advalorem\" value=\"".intval($savfields['sav_advalorem'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_advalorem']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_advalorem')[0].value=this.checked?'1':'0';\"> Facultatif : copie valeur AD VALOREM
	<br>
	<br><b>Les pièces du dossier :</b>
	<div style=\"margin:0px;padding:0px;\" id=\"et6tmppj\">
	<input type=\"checkbox\" name=\"et6pj\" value=\"$facturid\"><a href=\"../facture2.php?facturid=$facturid&passe=$rs_facture[passefacture]&nopub=1\" target=\"_blank\">Facture n°$facturid</a>";
	$js.="serverpj[$facturid]=new Array();
	serverpj[$facturid]['url']=\"http://".$_SERVER['SERVER_NAME']."/facture2.php?facturid=$facturid&passe=$rs_facture[passefacture]&nopub=1\";
	serverpj[$facturid]['name']=\"Facture $facturid.pdf\";\n";
      $requete=$DB_site2->query("SELECT * FROM facture WHERE userid<>".intval($rs_facture['userid'])." AND provenance='sav' AND provenanceid=".$facturid);
      while($fsav=$DB_site2->fetch_array($requete)){
	    $js.="serverpj[$fsav[facturid]]=new Array();
	    serverpj[$fsav[facturid]]['url']=\"http://".$_SERVER['SERVER_NAME']."/facture2.php?facturid=$fsav[facturid]&passe=$fsav[passefacture]&nopub=1\";
	    serverpj[$fsav[facturid]]['name']=\"Facture SAV $fsav[facturid].pdf\";\n";
	    $steps[6][0].="<br><input type=\"checkbox\" name=\"et6pj\" value=\"$fsav[facturid]\"><a href=\"../facture2.php?facturid=$fsav[facturid]&passe=$fsav[passefacture]&nopub=1\" target=\"_blank\">Facture SAV n°$fsav[facturid]</a>";
      }

      $pjfiles=$DB_site2->query("SELECT * FROM sav_files WHERE savid=$savid AND state=1 ORDER BY date");
      while($pj=$DB_site2->fetch_array($pjfiles)){
	    $js.="serverpj[$pj[id]]=new Array();
	    serverpj[$pj[id]]['url']=\"sav/$pj[id].$pj[ext]\";
	    serverpj[$pj[id]]['name']=\"$pj[name]\";\n";
	    $steps[6][0].="<br><input type=\"checkbox\" name=\"et6pj\" value=\"$pj[id]\" ".($pj['size']>10785760?"style=\"visibility:hidden\"":"")."><a href=\"sav/$pj[id].$pj[ext]\" target=\"_blank\">$pj[name]</a> ".round($pj['size']/1024)." ko <img src=\"images/supprimer.gif\" title=\"Supprimer\" onclick=\"delpj($pj[id])\">";
      }
	$steps[6][0].="</div>
	Envoi de fichier : <input type=\"file\" name=\"sendfile\"> <input type=\"button\" onclick=\"document.forms.et6form.action='sav_ul.php';document.forms.et6form.target='fupload';document.forms.et6form.submit();verifupload();\" value=\"Envoyer\">
	<div id=\"ulprogress6\"></div>
	<br><br>Dossier à envoyer par la Poste à l'adresse facturation
	<br>ou <a href=\"javascript:void(0)\" onclick=\"maildossier(0,6);\">dossier à envoyer par mail</a>
      </div>
    
      <div style=\"margin:10px;font-style:italic;\">MEMO : Il est impératif de s'assurer que le numéro de litige du transporteur est bien inscrit dans la facture ACILYON (facturation transporteur) pour que le litige soit étudié</div>
      <div>
	Dossier complet envoyé le <input type=\"text\" name=\"sav_envoidossier\" id=\"sav_envoidossier\" value=\"".$savfields['sav_envoidossier']."\" onclick=\"calendrier_show(this.id,this.value)\"> par <input type=\"hidden\" name=\"sav_envoimail\" value=\"".intval($savfields['sav_envoimail'])."\"><input type=\"checkbox\" value=\"1\" ".($savfields['sav_envoimail']?"checked":"")." onclick=\"document.getElementsByName('sav_envoimail')[0].value=this.checked?'1':'0';\"> mail &nbsp; <input type=\"hidden\" name=\"sav_envoiposte\" value=\"".intval($savfields['sav_envoiposte'])."\"><input type=\"checkbox\"".($savfields['sav_envoiposte']?"checked":"")." onclick=\"document.getElementsByName('sav_envoiposte')[0].value=this.checked?'1':'0';\"> La Poste
      </div>
      <div style=\"margin-top:10px;\">
	<br>Commentaires INTERNES :<br><textarea name=\"sav_com6\" cols=\"70\" rows=\"4\">".$savfields['sav_com6']."</textarea>".suivi(6)."
      </div>
      <div style=\"text-align:center;padding:10px;\"><input type=\"button\" onclick=\"document.forms.et6form.action='';document.forms.et6form.target='';document.forms.et6form.submit();\" value=\"Enregistrer\"></div>
  </div>
  <div id=\"resum6\" style=\"margin-top:10px;display:".($maxstep>5?"block":"none")."\"><b>Les pièces à joindre au dossier :</b>";
    if(intval($savfields['sav_factureacilyon'])==1) $steps[6][0].="<li>Facture Acilyon</li>";
    if(intval($savfields['sav_facturefourn'])==1) $steps[6][0].="<li>Copie facture fournisseur</li>";
    if(intval($savfields['sav_factureclient'])==1) $steps[6][0].="<li><a href=\"../facture2.php?facturid=$facturid&passe=$rs_facture[passefacture]\">Facture client Ma Cave à Vin</a></li>";
    if(intval($savfields['sav_lettreclient'])==1) $steps[6][0].="<li>Lettre client</li>";
    if(intval($savfields['sav_attestation'])==1) $steps[6][0].="<li>Facultatif : attestation sur l'honneur (en cas perte colis)</li>";
    if(intval($savfields['sav_advalorem'])==1) $steps[6][0].="<li>Facultatif : copie valeur AD VALOREM</li>";
    
    $steps[6][0].="<br><b>Pièces du dossier</b>";
    $requete=$DB_site2->query("SELECT * FROM facture WHERE userid<>".intval($rs_facture['userid'])." AND provenance='sav' AND provenanceid=".$facturid);
    while($fsav=$DB_site2->fetch_array($requete)){
	  $steps[6][0].="<li><a href=\"../facture2.php?facturid=$fsav[facturid]&passe=$fsav[passefacture]&nopub=1\" target=\"_blank\">Facture SAV n°$fsav[facturid]</a></li>";
    }

    $pjfiles=$DB_site2->query("SELECT * FROM sav_files WHERE savid=$savid AND state=1 ORDER BY date");
    while($pj=$DB_site2->fetch_array($pjfiles)){
	  $steps[6][0].="<li><a href=\"sav/$pj[id].$pj[ext]\" target=\"_blank\">$pj[name]</a> ".round($pj['size']/1024)." ko</li>";
    }
    if($savfields['sav_envoidossier']!="") $step[6][0].="<div>Dossier complet envoyé le ".$savfields['sav_envoidossier']." par ".(intval($savfields['sav_envoimail'])==1?"mail":"")." ".(intval($savfields['sav_envoiposte'])==1?"La Poste":"")."</div>";
    $steps[6][0].="<div style=\"margin-top:10px;\">Commentaires</div>".suivi(6,false)."
    <input type=\"button\" value=\"Modifier\" onclick=\"modifstep(6);\">
    </div>
    </form>
    </div>";

  $steps[6][1]="<div class=\"advgroup\">
  <form name=\"et6form\" method=\"post\" enctype=\"multipart/form-data\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"6\">
  <p>Etape 6 : Montage du dossier litige</p>
  ".maj(6)."
  <br><b>Pièces à joindre au dossier :</b>
  <div>
   <input type=\"hidden\" name=\"sav_nbserie\" value=\"".intval($savfields['sav_nbserie'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_nbserie']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_nbserie)[0].value=this.checked?'1':'0';\"> Numéro de série
   <br><input type=\"hidden\" name=\"sav_facturefourn\" value=\"".intval($savfields['sav_facturefourn'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_facturefourn']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_facturefourn')[0].value=this.checked?'1':'0';\"> Copie facture fournisseur
   <br><input type=\"hidden\" name=\"sav_factureclient\" value=\"".intval($savfields['sav_factureclient'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_factureclient']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_factureclient')[0].value=this.checked?'1':'0';\"> <a href=\"../facture2.php?facturid=$facturid&passe=$rs_facture[passefacture]\">Facture client Ma Cave à Vin</a>
   <br><input type=\"hidden\" name=\"sav_photos\" value=\"".intval($savfields['sav_photos'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_photos']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_photos')[0].value=this.checked?'1':'0';\"> Facultatif : photos
   <br><input type=\"hidden\" name=\"sav_lettreexpl\" value=\"".intval($savfields['sav_lettreexpl'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_lettreexpl']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_lettreexpl')[0].value=this.checked?'1':'0';\"> Lettre explicative du problème (par le client ou Acilyon)
  <br>
    <br><b>Les pièces du dossier :</b>
    <div style=\"margin:0px;padding:0px;\" id=\"et6tmppj\">
    <input type=\"checkbox\" name=\"et6pj\" value=\"$facturid\"><a href=\"../facture2.php?facturid=$facturid&passe=$rs_facture[passefacture]&nopub=1\" target=\"_blank\">Facture n°$facturid</a>";
    $js.="serverpj[$facturid]=new Array();
    serverpj[$facturid]['url']=\"http://".$_SERVER['SERVER_NAME']."/facture2.php?facturid=$facturid&passe=$rs_facture[passefacture]&nopub=1\";
    serverpj[$facturid]['name']=\"Facture $facturid.pdf\";\n";
   
   $requete=$DB_site2->query("SELECT * FROM facture WHERE userid<>".intval($rs_facture['userid'])." AND provenance='sav' AND provenanceid=".$facturid);
   while($fsav=$DB_site2->fetch_array($requete)){
	$js.="serverpj[$fsav[facturid]]=new Array();
	serverpj[$fsav[facturid]]['url']=\"http://".$_SERVER['SERVER_NAME']."/facture2.php?facturid=$fsav[facturid]&passe=$fsav[passefacture]&nopub=1\";
	serverpj[$fsav[facturid]]['name']=\"Facture SAV $fsav[facturid].pdf\";\n";
	$steps[6][1].="<br><input type=\"checkbox\" name=\"et6pj\" value=\"$fsav[facturid]\"><a href=\"../facture2.php?facturid=$fsav[facturid]&passe=$fsav[passefacture]&nopub=1\" target=\"_blank\">Facture SAV n°$fsav[facturid]</a>";
   }

   $pjfiles=$DB_site2->query("SELECT * FROM sav_files WHERE savid=$savid AND state=1 ORDER BY date");
   while($pj=$DB_site2->fetch_array($pjfiles)){
	$js.="serverpj[$pj[id]]=new Array();
	serverpj[$pj[id]]['url']=\"sav/$pj[id].$pj[ext]\";
	serverpj[$pj[id]]['name']=\"$pj[name]\";\n";
	$steps[6][1].="<br><input type=\"checkbox\" name=\"et6pj\" value=\"$pj[id]\" ".($pj['size']>10785760?"style=\"visibility:hidden\"":"")."><a href=\"sav/$pj[id].$pj[ext]\" target=\"_blank\">$pj[name]</a> ".round($pj['size']/1024)." ko <img src=\"images/supprimer.gif\" title=\"Supprimer\" onclick=\"delpj($pj[id])\">";
   }
    $steps[6][1].="</div>
    Envoi de fichier : <input type=\"file\" name=\"sendfile\"> <input type=\"button\" onclick=\"document.forms.et6form.action='sav_ul.php';document.forms.et6form.target='fupload';document.forms.et6form.submit();verifupload();\" value=\"Envoyer\">
    <div id=\"ulprogress6\"></div>
  <br><a href=\"javascript:void(0)\" onclick=\"maildossier(1,6);\">dossier à envoyer par mail</a>
  <div style=\"margin:10px;font-style:italic;\">Panne sans garantie PMOD, le client doit prendre à sa charge le déplacement des techniciens nécessaires à la réparation de la cave. Le fabricant ne prend à sa charge que le remplacement des pièces.</div>
  <div>
    Dossier complet envoyé le <input type=\"text\" name=\"sav_envoidossier\" id=\"sav_envoidossier\" value=\"".$savfields['sav_envoidossier']."\" onclick=\"calendrier_show(this.id,this.value)\"> par <input type=\"hidden\" name=\"sav_envoimail\" value=\"".intval($savfields['sav_envoimail'])."\"><input type=\"checkbox\" value=\"1\" ".($savfields['sav_envoimail']?"checked":"")." onclick=\"document.getElementsByName('sav_envoimail')[0].value=this.checked?'1':'0';\"> mail &nbsp; <input type=\"hidden\" name=\"sav_envoiposte\" value=\"".intval($savfields['sav_envoiposte'])."\"><input type=\"checkbox\"".($savfields['sav_envoiposte']?"checked":"")." onclick=\"document.getElementsByName('sav_envoiposte')[0].value=this.checked?'1':'0';\"> La Poste
  </div>
  <div style=\"margin-top:10px;\">
    <br>Commentaires INTERNES :<br><textarea name=\"sav_com6\" cols=\"70\" rows=\"4\">".$savfields['sav_com6']."</textarea>".suivi(6)."
  </div>
  <div style=\"text-align:center;padding:10px;\"><input type=\"button\" onclick=\"document.forms.et6form.action='';document.forms.et6form.target='';document.forms.et6form.submit();\" value=\"Enregistrer\"></div>
  </form>
  </div>";
}

if($maxstep>=6){

  $steps[7][0]="<div class=\"advgroup\">
  <form name=\"et7form\" method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"7\">
  <p>Etape 7 : Cloture du dossier litige</p>
  ".maj(7)."
  <div id=\"edit7\" style=\"display:".($maxstep==6?"block":"none")."\">
      <div>Litige : <input type=\"radio\" name=\"sav_accordlitige\" value=\"1\" ".($savfields['sav_accordlitige']=="1"?"checked":"")."> Accordé &nbsp;  <input type=\"radio\" name=\"sav_accordlitige\" value=\"0\" ".($savfields['sav_accordlitige']=="0"?"checked":"")."> Refusé &nbsp; <input type=\"radio\" name=\"sav_accordlitige\" value=\"2\" ".($savfields['sav_accordlitige']=="2"?"checked":"")."> Pris en charge par garantie fabricant ou SFG
	<br>Date : <input type=\"text\" name=\"sav_dateaccord\" id=\"sav_dateaccord\" value=\"".$savfields['sav_dateaccord']."\" onclick=\"calendrier_show(this.id,this.value)\" readonly>
	<br>Raison : <textarea name=\"sav_raison\" cols=\"60\">".$savfields['sav_raison']."</textarea>
      </div>";
      
      $requete=$DB_site2->query("SELECT * FROM facture WHERE provenance='sav' AND provenanceid=".$facturid);
      if($DB_site2->num_rows($requete)>0){
	$steps[7][0].="<div style=\"margin-top:10px;\"><b>Commandes SAV :</b><br>";
	while($fsav=$DB_site2->fetch_array($requete)){
	  $steps[7][0].="Commande n°".$fsav['facturid']." : ".$status[$fsav['valide']]."<br>";
	}
	$steps[7][0].="</div>";
      }
      $steps[7][0].="<div style=\"margin-top:10px;\">Confirmation fournisseur que le problème a été réparé : <input type=\"text\" name=\"sav_daterepare\" id=\"sav_daterepare\" value=\"".$savfields['sav_daterepare']."\" onclick=\"calendrier_show(this.id,this.value)\">
	<br>Client remboursé (suite à geste commercial, ...) <input type=\"hidden\" name=\"sav_chkclientremb\" value=\"".intval($savfields['sav_chkclientremb'])."\"><input type=\"checkbox\" value=\"1\"".($savfields['sav_chkclientremb']=="1"?" checked":"")." onclick=\"document.getElementsByName('sav_chkclientremb')[0].value=this.checked?'1':'0';\"> le <input type=\"text\" name=\"sav_daterembourse\" id=\"sav_daterembourse\" value=\"".$savfields['sav_daterembourse']."\" onclick=\"calendrier_show(this.id,this.value)\">
	<br><br>Dossier clos le <input type=\"text\" name=\"sav_dateclos\" id=\"sav_dateclos\" value=\"".$savfields['sav_dateclos']."\" onclick=\"calendrier_show(this.id,this.value)\">
      </div>
      <div style=\"margin-top:10px;\">
	<br>Commentaires INTERNES :<br><textarea name=\"sav_com7\" cols=\"70\" rows=\"4\">".$savfields['sav_com7']."</textarea>".suivi(7)."
      </div>
      <div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Enregistrer\"></div>
  </div>
  <div id=\"resum7\" style=\"margin-top:10px;display:".($maxstep>6?"block":"none")."\">
     Litige ";
     switch($savfields['sav_accordlitige']){
      case "":
      $steps[7][0].="non renseigné";
      break;
      case "1":
      $steps[7][0].="accordé";
      break;
      case "0":
      $steps[7][0].="refusé";
      break;
      case "2":
      $steps[7][0].="Pris en charge par garantie fabricant ou SFG";
      break;
     }
     $steps[7][0].=" le ".$savfields['sav_dateaccord']."
     <br>Raison : ".$savfields['sav_raison'];
    
     $requete=$DB_site2->query("SELECT * FROM facture WHERE provenance='sav' AND provenanceid=".$facturid);
      if($DB_site2->num_rows($requete)>0){
	$steps[7][0].="<div style=\"margin-top:10px;\"><b>Commandes SAV :</b><br>";
	while($fsav=$DB_site2->fetch_array($requete)){
	  $steps[7][0].="Commande n°".$fsav['facturid']." : ".$status[$fsav['valide']]."<br>";
	}
	$steps[7][0].="</div>";
      }
     $steps[7][0].="<div style=\"margin-top:10px;\">";
     if($savfields['sav_daterepare']!="") $steps[7][0].="Confirmation fournisseur que le problème a été réparé le ".$savfields['sav_daterepare'];
     if(intval($savfields['sav_chkclientremb'])==1) $steps[7][0].="<br>Client remboursé (suite à geste commercial, ...) le ".$savfields['sav_daterembourse'];
     if($savfields['sav_dateclos']!="") $steps[7][0].="<br>Dossier clos le ".$savfields['sav_dateclos'];

     $steps[7][0].="<div style=\"margin-top:10px;\">Commentaires</div>".suivi(7,false)."
    <input type=\"button\" value=\"Modifier\" onclick=\"modifstep(7);\">
  </div>
  </form>
  </div>";

  $steps[7][1]=$steps[7][0];

}

?>