<?php

print "<style>
.tab_on a { background-color:white; border:solid thin black; text-align:center; text-decoration:none; padding:10px; }
.tab_off { padding-top:10px; }
.tab_off:hover { padding-top:0px; }
.tab_off a { background-color:#e4e4e4; border:solid thin #cccccc; text-align:center; text-decoration:none; padding:10px; }
.advgroup { background-color:#f4f4f4; margin-bottom:10px; font-size:12px; text-align:left; }
.advgroup p { margin:6px; color:white; font-weight:bold; font-size:14px; background-color:AA0000; }
.advgroup table { font-size:12px; border-collapse:collapse; width:100%; }
.advgroup td { border-bottom:solid thin #808080; padding-top:5px; padding-bottom:5px; }
</style>";


print "<script language=\"javascript\">
function tab(nb){
         for(i=0;i<=4;i++){
             document.getElementById('tab'+i).style.visibility='hidden';
             document.getElementById('tab'+i).style.position='absolute';
             document.getElementById('tablink'+i).className='tab_off';
         }

         document.getElementById('tab'+nb).style.position='relative';
         document.getElementById('tab'+nb).style.visibility='visible';
         document.getElementById('tablink'+nb).className='tab_on';
}

function valid1(showpreview){
       error=\"\";
       fourn=false;
       for(i=0;i<document.getElementsByName('fournisseur').length;i++){
            if(document.getElementsByName('fournisseur')[i].checked) fourn=true;
       }
       if(fourn==false) error=\"Il faut s�lectionner un fournisseur\";

       prod=false;
       for(i=0;i<document.getElementsByName('produits[]').length;i++){
            if(document.getElementsByName('produits[]')[i].checked) prod=true;
       }
       if(prod==false) error=\"Aucun produit n'est s�lectionn�\";

       trspt=false;
       for(i=0;i<document.getElementsByName('trspt').length;i++){
            if(document.getElementsByName('trspt')[i].checked) trspt=true;
       }
       if(trspt==false) error=\"Il faut s�lectionner un mode de transport\";

       if(error==\"\"){
           if(showpreview==true){
               preview(form1);
           }else{
               document.forms.form1.action='index.php?order=$mporder->id';
		document.forms.form1.target = \"_self\";
               document.forms.form1.submit();
           }
       }else
           alert(error);
}

function sumprod(){
         total=0;
         ecopart=0;
         gartotal=0;
         for(i=0;i<document.getElementsByName('produits[]').length;i++){

              if(document.getElementsByName('produits[]')[i].checked){
                  pid=document.getElementsByName('produits[]')[i].value;
                  total+=parseFloat(document.getElementsByName('qte_'+pid)[0].value*document.getElementsByName('price1_'+pid)[0].value);
                  if(document.getElementsByName('gar_'+pid)[0].checked) gartotal+=parseFloat(document.getElementsByName('qte_'+pid)[0].value*document.getElementsByName('garval_'+pid)[0].value);
                  ecopart+=parseFloat(document.getElementsByName('ecopart_'+pid)[0].value);
              }
         }

         fourn=0;
         if(document.getElementsByName('trspt')[0].checked) fourn=0;
         if(document.getElementsByName('trspt')[1].checked) fourn=parseFloat(document.getElementsByName('fournisseurcout')[0].value);
         if(document.getElementsByName('trspt')[2].checked) fourn=parseFloat(document.getElementsByName('colissimocout')[0].value);
         if(document.getElementsByName('trspt')[3].checked) fourn=parseFloat(document.getElementsByName('aslscout')[0].value);
         if(document.getElementsByName('trspt')[4].checked) fourn=parseFloat(document.getElementsByName('mazetcout')[0].value);
         if(document.getElementsByName('trspt')[5].checked) fourn=parseFloat(document.getElementsByName('installslscout')[0].value);
         if(document.getElementsByName('trspt')[6].checked) fourn=parseFloat(document.getElementsByName('acilyoncout')[0].value);
         if(document.getElementsByName('trspt')[7].checked) fourn=parseFloat(document.getElementsByName('othertrsptcout')[0].value);
         if(isNaN(fourn)) fourn=0;

         document.getElementById('prod1total').innerHTML=total+' �';
         document.getElementById('prixprodtotal').innerHTML=total+' �';
         document.getElementById('prixecoparttotal').innerHTML=ecopart+' �';
         document.getElementById('prixtrspttotal').innerHTML=fourn+' �';
         document.getElementById('prixgartotal').innerHTML=gartotal+' �';
         document.getElementById('prixfourntotal').innerHTML=(total+ecopart+fourn+gartotal)+' �';

}


function trspttarif(){
      total=0;

      if(document.getElementsByName('trsptcout')[0].checked) total+=parseFloat(document.getElementsByName('trsptcoutval')[0].value);
      if(document.getElementsByName('trsptecotax')[0].checked) total+=parseFloat(document.getElementsByName('trsptecotax')[0].value);
      if(document.getElementsByName('trsptenlext')[0].checked) total+=parseFloat(document.getElementsByName('trsptenlext')[0].value);
      if(document.getElementsByName('trsptparis')[0].checked) total+=parseFloat(document.getElementsByName('trsptparis')[0].value);
      if(document.getElementsByName('trsptlyon')[0].checked) total+=parseFloat(document.getElementsByName('trsptlyon')[0].value);
      if(document.getElementsByName('trsptmarseille')[0].checked) total+=parseFloat(document.getElementsByName('trsptmarseille')[0].value);
      if(document.getElementsByName('trsptclientpart')[0].checked) total+=parseFloat(document.getElementsByName('trsptclientpart')[0].value);
      if(document.getElementsByName('trsptclientpro')[0].checked) total+=parseFloat(document.getElementsByName('trsptclientpro')[0].value);
      if(document.getElementsByName('trsptetage')[0].checked) total+=parseFloat(document.getElementsByName('trsptetage')[0].value);
      if(document.getElementsByName('trsptassurcomp')[0].checked) total+=parseFloat(document.getElementsByName('trsptassurcompval')[0].value);
      if(document.getElementsByName('trsptcoutother')[0].checked) total+=parseFloat(document.getElementsByName('trsptcoutotherval')[0].value);

      document.getElementById('totaltrspt').innerHTML=total+' �';

}
function totalassurance(){
         totalarticles=0;
         poidstotal=0;
         for(i=0;i<document.getElementsByName('produits2[]').length;i++){
             if(document.getElementsByName('produits2[]')[i].checked){
                 ligneid=document.getElementsByName('produits2[]')[i].value;
                 totalarticles+=parseFloat(document.getElementsByName('price_'+ligneid)[0].value);
                 poidstotal+=parseFloat(document.getElementsByName('poids_'+ligneid)[0].value);
             }
         }

         document.getElementsByName('trsptassurcompval')[0].value=Math.round(totalarticles*0.6)/100;
         document.getElementById('poidstotal').innerHTML=poidstotal;
	 document.getElementById('coutpoids').innerHTML='poids : '+poidstotal+'kg,';

}

function valid2(showpreview){
         if(showpreview==true){
                preview(form2);
         }else{
                document.forms.form2.action='index.php?order=$mporder->id';
		document.forms.form2.target = \"_self\";
                document.forms.form2.submit();
         }
}

function totalsls(){
         total=0;
         if(document.getElementsByName('slscouttrspt')[0].checked) total+=parseFloat(document.getElementsByName('slscouttrsptval')[0].value);
         if(document.getElementsByName('slscoutcorse')[0].checked) total+=parseFloat(document.getElementsByName('slscorsetotal')[0].value);
         if(document.getElementsByName('slscoutreprise')[0].checked) total+=parseFloat(document.getElementsByName('slscoutrepriseval')[0].value);
         if(document.getElementsByName('slscoutassur')[0].checked) total+=parseFloat(document.getElementsByName('slscoutassurval')[0].value);
         if(document.getElementsByName('slscoutother')[0].checked) total+=parseFloat(document.getElementsByName('slscoutotherval')[0].value);
         document.getElementById('totalsls').innerHTML=Math.round(total*100)/100+' �';
}
function assurancesls(){
         totalarticles=0;
         poids=0;
         for(i=0;i<document.getElementsByName('produits3[]').length;i++){
             if(document.getElementsByName('produits3[]')[i].checked){
                 ligneid=document.getElementsByName('produits3[]')[i].value;
                 totalarticles+=parseFloat(document.getElementsByName('price3_'+ligneid)[0].value);
                 poids+=parseFloat(document.getElementsByName('poids3_'+ligneid)[0].value);
             }
         }

         document.getElementsByName('slscoutassurval')[0].value=Math.round(totalarticles*0.35)/100;
         document.getElementById('poidstotal3').innerHTML=poids;
         document.getElementsByName('slscorsetotal')[0].value=Math.round(poids*document.getElementById('prixkgcorse').value*100)/100;
	 document.getElementById('coutpoidssls').innerHTML='Poids '+poids+'kg';
}

function valid3(showpreview){
        error=\"\";

        livr=false;
        for(i=0;i<document.getElementsByName('livrsls').length;i++){
             if(document.getElementsByName('livrsls')[i].checked) livr=true;
        }
        if(livr==false) error=\"Il faut s�lectionner une agence\";

        if(error==\"\"){
            if(showpreview==true){
                preview(form3);
            }else{
                document.forms.form3.action='index.php?order=$mporder->id';
		document.forms.form3.target = \"_self\";
                document.forms.form3.submit();
            }
       }else
           alert(error); 
}

function preview(formid){
   my_form = eval(formid);
   window.open(\"adv2.php?preview=1\", \"popup\", \"height=440,width=640,menubar='no',toolbar='no',location='no',status='no',scrollbars='no'\");
   my_form.action=\"adv2.php?preview=1&facturid=$mporder->id\";
   my_form.target = \"popup\";
   my_form.submit();
}

function tarifs(type){
   switch(type){
      case 'tnt':
      case 'mory':
	  document.getElementById('trecotax').style.visibility='visible';
	  document.getElementById('trmarseille').style.height='';
	  document.getElementById('trexterieur').style.visibility='visible';
	  document.getElementById('trmarseille').style.height='';
	  document.getElementById('trmarseille').style.visibility='hidden';
	  document.getElementById('trmarseille').style.height='0px';
	  document.getElementById('tretage').style.visibility='hidden';
	  document.getElementById('trmarseille').style.height='0px';

	  document.getElementsByName('trsptparis')[0].value=6.4;
	  document.getElementById('prixparis').innerHTML='6,4 &euro;';
	  document.getElementsByName('trsptlyon')[0].value=4.9;
	  document.getElementById('prixlyon').innerHTML='4,9 &euro;';
	  pctrsptassurance=0.6;

	  document.getElementsByName('trsptclientpart')[0].value=7;
	  document.getElementById('prixparticulier').innerHTML='7 &euro;';
      break;
    
      case 'mazet':
	  document.getElementById('trecotax').style.visibility='hidden';
	  document.getElementById('trmarseille').style.height='0px';
	  document.getElementById('trexterieur').style.visibility='hidden';
	  document.getElementById('trmarseille').style.height='0px';
	  document.getElementById('trmarseille').style.visibility='visible';
	  document.getElementById('trmarseille').style.height='';
	  document.getElementById('tretage').style.visibility='visible';
	  document.getElementById('trmarseille').style.height='';
	 
	  document.getElementsByName('trsptparis')[0].value=2.5;
	  document.getElementById('prixparis').innerHTML='2,5 &euro;';
	  document.getElementsByName('trsptlyon')[0].value=2.5;
	  document.getElementById('prixlyon').innerHTML='2,5 &euro;';
	  document.getElementsByName('trsptmarseille')[0].value=2.5;
	  document.getElementById('prixmarseille').innerHTML='2,5 &euro;';
	  pctrsptassurance=0.7;

	  document.getElementsByName('trsptclientpart')[0].value=0;
	  document.getElementById('prixparticulier').innerHTML='0 &euro;';
      break;

      
   }
   document.getElementById('txtassurance').innerHTML='Assurance compl�mentaire '+pctrsptassurance+'% valeur';
   totalassurance();
}
</script>";

print "<table height=\"30\" style=\"margin-top:20px;\">
<tr><td class=\"tab_on\" id=\"tablink0\"><a href=\"javascript:tab(0);\">Suivi commande</a></td><td class=\"tab_off\" id=\"tablink1\"><a href=\"javascript:tab(1);\">Fournisseur</a></td><td class=\"tab_off\" id=\"tablink2\"><a href=\"javascript:tab(2);\">Transporteur</a></td><td class=\"tab_off\" id=\"tablink3\"><a href=\"javascript:tab(3);\">Installateur</a></td><td class=\"tab_off\" id=\"tablink4\"><a href=\"javascript:tab(4);\">R�capitulatif</a></td></tr>
</table>";

$fournisseurs=array("acilyon test"=>"pr@mon-espace-cuisson.com","stock acilyon"=>"contact@ma-cave-a-vin.fr","eurocave - caves"=>"mbrun@eurocave.com;clefrancois@eurocave.com","eurocave accessoires"=>"fgrzunov@eurocave.com","climadif"=>"ndufloux@climadiff.com;h.fehrenbach@vinosafe.com","vinosafe"=>"h.fehrenbach@vinosafe.com","la sommeliere"=>"isabelle.reze@lasommeliere.com","liebherr"=>"isabelle.andres@eberhardt.fr","dometic"=>"veronique.bocque@dometic.fr;laurence.martin@dometic.fr");

$suivi2=$DB_site2->fetch_array($DB_site2->query("SELECT suivi2 FROM mp_orders WHERE id='".$mporder->id."'"));
print "<div id=\"tab0\" style=\"\">

<h3>Suivi commande</h3>
<form name=\"form0\" method=\"post\" enctype=\"multipart/form-data\">
<input type=\"hidden\" name=\"action\" value=\"advsuivi\">
<div class=\"advgroup\">
<textarea name=\"suivitxt\" cols=\"60\" rows=\"6\">".$suivi2[0]."</textarea>
<br><input type=\"submit\" value=\"Enregistrer le suivi commande\">
</div>
</form>
</div>";


print "<div id=\"tab1\" style=\"visibility:hidden; position:absolute;\">

<h3>Fournisseur de caves + accessoires</h3>
<form name=\"form1\" method=\"post\" enctype=\"multipart/form-data\">
<input type=\"hidden\" name=\"action\" value=\"advfournisseur\">
<input type=\"hidden\" name=\"fournisseurname\">
<div class=\"advgroup\">
<p>1. Choix du fournisseur</p>
<table><tr>";
$i=0;
$c=0;
foreach($fournisseurs as $name=>$mail){
                      $i++;
                      $c++;
                      print "<td><input type=\"radio\" name=\"fournisseur\" id=\"fourn$i\" value=\"".strtolower($mail)."\" onclick=\"document.getElementsByName('fournisseurname')[0].value='$name';\"></td><td><a href=\"#\" onclick=\"javascript:document.getElementById('fourn$i').checked=true;\" title=\"$mail\">".ucfirst($name)."</a> </td>";
                      if($c==2){
                           $c=0;
                           print "</tr><tr>";
                      }
}

print "</tr></table>
</div>";

print "<div class=\"advgroup\">
<p>2. Pi�ces jointes</p>
<table>
<tr><td><input type=\"checkbox\" name=\"nopj\" value=\"0\"></td><td>Aucune</td></tr>
<tr><td><input type=\"checkbox\" name=\"blpj\" value=\"1\"></td><td>Bon de livraison</td></tr>
<tr><td><input type=\"checkbox\" name=\"facturepj\" value=\"1\"></td><td>Facture (pour commandes export uniquement)</td></tr>
<tr><td><input type=\"checkbox\" name=\"tntpj\" value=\"1\"></td><td>Bon FEDEX Express</td><td><input type=\"file\" name=\"bontnt\"></td></tr>
<tr><td><input type=\"checkbox\" name=\"glspj\" value=\"1\"></td><td>Bon GLS</td><td><input type=\"file\" name=\"bongls\"></td></tr>
<tr><td><input type=\"checkbox\" name=\"fragilepj\" value=\"1\"></td><td>Etiquette fragile</td></tr>
<tr><td><input type=\"checkbox\" name=\"identpj\" value=\"1\"></td><td colspan=\"2\">Texte � inscrire sur colis : Commande N� $mporder->id De Ma cave � vin - ACILYON pour $mporder->shipping_last_name $mporder->shipping_first_name $mporder->shipping_zip $mporder->shipping_city $mporder->shipping_country</td></tr>
<tr><td><input type=\"checkbox\" name=\"otherpj\" value=\"1\"></td><td>Autre : <input type=\"text\" name=\"otherpjname\" size=\"60\"></td><td><input type=\"file\" name=\"otherpjfile\"></td></tr>
</table>
</div>";

print "<div class=\"advgroup\">
<p>3. Objet du mail</p>
<input type=\"radio\" name=\"mailsubject\" value=\"cmd\" checked> Commande n�$mporder->id
<br><input type=\"radio\" name=\"mailsubject\" value=\"other\"> Autre : <input type=\"text\" name=\"othershbject\" size=\"80\">
</div>";

print "<div class=\"advgroup\">
<p>4. Texte d'intro</p>
<input type=\"radio\" name=\"intro\" value=\"bonjour\" checked /> Bonjour, Veuillez trouver ci-joint une nouvelle commande n� $mporder->id
<br><input type=\"radio\" name=\"intro\" value=\"annule\" /> Bonjour, Ce mail annule et remplace le mail pr�c�demment envoy� pour la commande n�$mporder->id
<br><input type=\"radio\" name=\"intro\" value=\"other\" /> Autre : <br><textarea name=\"otherintro\" cols=\"80\" rows=\"3\"></textarea>
</div>";

print "<div class=\"advgroup\">
<p>5. Produits</p>
<table>
<tr><td></td><td>qte</td><td>d�tail</td><td>marque</td><td>ref acilyon</td><td>ref fournisseur</td><td>prix achat</td><td>eco part</td><td>Garantie fourniseur</td></tr>";
$lignefactures=$mporder->list_lines();
foreach ($lignefactures as $lignefacture){
      $article=$DB_site2->query_first("SELECT * FROM article a, article_marque am, marque m WHERE am.artid=a.artid AND am.marqueid=m.marqueid AND a.artcode='".$lignefacture->ref_tin."'");
      $envoi=$DB_site2->query_first("SELECT * FROM mp_envoi_fournisseur_ligne WHERE type='fourn' AND ligne_facture_id=".$lignefacture->id);
      $style="";
      if($envoi['date']!="") $style="background-color:green;";
      print "<tr><td style=\"$style\"><input type=\"checkbox\" name=\"produits[]\" value=\"$lignefacture->id\" onclick=\"sumprod()\"></td><td><input type=\"hidden\" name=\"qte_$lignefacture->id\" value=\"$lignefacture->number\">$lignefacture->number</td><td><textarea name=\"desc_$lignefacture->id\" cols=\"30\" rows=\"3\">$lignefacture->product_name</textarea></td><td>$article[libelle]</td><td>$lignefacture->ref_tin</td><td><input type=\"text\" name=\"ref_$lignefacture->id\" value=\"$article[artcode_eurocave]\" size=\"10\"></td><td><input type=\"text\" name=\"price1_$lignefacture->id\" value=\"$article[paht]\" size=\"5\"></td><td><input type=\"text\" name=\"ecopart_$lignefacture->id\" value=\"$article[ecopartht]\" size=\"5\"></td><td><input type=\"checkbox\" name=\"gar_$lignefacture->id\" value=\"1\" onclick=\"sumprod()\"><input type=\"text\" name=\"garval_$lignefacture->id\" size=\"5\"></td></tr>";
}
print "<tr><td colspan=\"7\">Total :</td><td id=\"prod1total\"></td></tr>
</table>
</div>";

print "<div class=\"advgroup\">
<p>6. Mode de transport</p>
<table>
<tr><td></td><td></td><td>Date d�part maximum</td><td>Date livraison au plus tard le</td><td>Cout transport</td></tr>
<tr><td><input type=\"radio\" name=\"trspt\" value=\"mazet\" onclick=\"sumprod()\"></td><td>Enlevement � notre charge en vos locaux par <select name=\"selecttrspt\"><option style=\"font-weight:bold;\">Choisir ...</option><option value=\"Mazet\">Mazet</option><option value=\"Mory\">Mory</option><option value=\"TNT\">FEDEX Express</option><option value=\"GLS\">GLS</option></select> le</td><td><input type=\"text\" name=\"mazetdepart\"></td><td></td><td></td><td></td></tr>
<tr><td><input type=\"radio\" name=\"trspt\" value=\"fournisseur\" onclick=\"sumprod()\"></td><td>Livraison par vos soins par votre transporteur directement chez notre client professionnel </td><td></td><td><input type=\"text\" name=\"fournisseurlivraison\"></td><td><input type=\"text\" name=\"fournisseurcout\" size=\"5\"></td></tr>
<tr><td><input type=\"radio\" name=\"trspt\" value=\"colissimo\" onclick=\"sumprod()\"></td><td>Livraison par vos soins par colissimo directement chez notre client</td><td><input type=\"text\" name=\"colissimodepart\" value=\"sous 24/48 heures maxi\"></td><td><input type=\"text\" name=\"colissimolivraison\"></td><td><input type=\"text\" name=\"colissimocout\" size=\"5\"></td></tr>
<tr><td><input type=\"radio\" name=\"trspt\" value=\"asls\" onclick=\"sumprod()\"></td><td>Livraison par vos soins par votre transporteur directement chez SLS (adresse sur BL)</td><td><input type=\"text\" name=\"aslsdepart\"></td><td><input type=\"text\" name=\"aslslivraison\"></td><td><input type=\"text\" name=\"aslscout\" size=\"5\"></td></tr>
<tr><td><input type=\"radio\" name=\"trspt\" value=\"livraisonmazet\" onclick=\"sumprod()\"></td><td>Livraison par vos soins par votre transporteur directement chez <select name=\"selectmazet\"><option value=\"\" style=\"font-weight:bold;\" selected>Choisir ...</option><option value=\"Mazet Colmar<br>ZI Nord - BP 257<br>68005 Colmar<br>T�l : 03.89.21.09.40 Fax : 03.89.21.09.49\">Mazet Colmar</option><option value=\"Mazet Gonesse<br>1 RD 370 - BP 45<br>95500 Gonesse<br>T�l : 01.34.07.72.00 Fax : 01.39.87.07.37\">Mazet Gonesse</option><option value=\"Mazet Montelimar<br>ZI Du Gournier<br>26205 Mont�limar<br>T�l : 04.75.53.15.90 Fax : 04.75.00.72.29\">Mazet Montelimar</option><option value=\"Mory Valence<br>45 Avenue Marseille<br>26000 Valence<br>T�l : 04.75.78.60.12\">Mory Valence</option></select></td><td><input type=\"text\" name=\"livraisonmazetdepart\"></td><td><input type=\"text\" name=\"mazetdatelivraison\"></td><td><input type=\"text\" name=\"mazetcout\" size=\"5\" value=\"\"></td></tr>
<tr><td><input type=\"radio\" name=\"trspt\" value=\"installsls\" onclick=\"sumprod()\"></td><td>Livraison par vos soins avec installation par SLS </td><td><input type=\"text\" name=\"installslsdepart\"></td><td><input type=\"text\" name=\"installslslivraison\"></td><td><input type=\"text\" name=\"installslscout\" size=\"5\" value=\"\"></td></tr>
<tr><td><input type=\"radio\" name=\"trspt\" value=\"acilyon\" onclick=\"sumprod()\"></td><td title=\"ACILYON\n150 rue paul Bert\n69003 Lyon\nT�l : 04.26.03.05.40 / fax :04.26.03.05.49\">Livraison par vos soins directement chez ACILYON</td><td></td><td><input type=\"text\" name=\"acilyonlivraison\"></td><td><input type=\"text\" name=\"acilyoncout\" size=\"5\" value=\"\"></td></tr>
<tr><td><input type=\"radio\" name=\"trspt\" value=\"other\" onclick=\"sumprod()\"></td><td>Autre : <input type=\"text\" name=\"othertrspt\" size=\"60\"></td><td><input type=\"text\" name=\"othertrsptdepart\"></td><td><input type=\"text\" name=\"othertrsptlivraison\"></td><td><input type=\"text\" name=\"othertrsptcout\" size=\"5\" value=\"\"></td></tr>
</table>
</div>";

print "<div class=\"advgroup\">
<p>7. Prix</p>
<table>
<tr><td>Prix des produits</td><td id=\"prixprodtotal\"></td></tr>
<tr><td>Eco participation</td><td id=\"prixecoparttotal\"></td></tr>
<tr><td>Transport</td><td id=\"prixtrspttotal\"></td></tr>
<tr><td>Garantie fournisseur</td><td id=\"prixgartotal\"></td></tr>
<tr><td>Montant total commande HT :</td><td id=\"prixfourntotal\"></td></tr>
</table>
</div>";

print "<div class=\"advgroup\">
<p>8. Texte de fin</p>
<table>
<tr><td><input type=\"checkbox\" name=\"endtxt1\" value=\"montant\" checked></td><td>Autre :<br><textarea name=\"otherendtxt\" cols=\"60\" rows=\"3\"></textarea></td></tr>
<tr><td><input type=\"checkbox\" name=\"endtxt2\" value=\"coord\" checked></td><td>Nous vous remercions de votre envoi, par mail ou fax, de votre confirmation de commande.<br>Cordialement<br>$user_info[nom_contact] - ACILYON - www.ma-cave-a-vin.fr<br>T�l : + 33.4.26.03.05.43 -  Fax: +33.4.26.03.05.49</td></tr>
</table>
</div>";

print "<input type=\"button\" onclick=\"valid1()\" value=\"Envoyer la commande au fournisseur\" /> <input type=\"button\" value=\"Aper�u\" onclick=\"valid1(true);\"></div>
</form>";

print "<div id=\"tab2\" style=\"visibility:hidden; position:absolute;\">
<h3>Fournisseur de TRANSPORT</h3>
<form name=\"form2\" method=\"post\" enctype=\"multipart/form-data\">
<input type=\"hidden\" name=\"action\" value=\"advtransport\">
<input type=\"hidden\" name=\"trsptname\">
<div class=\"advgroup\">
<p>1. Type de livraison</p>
<table>
<tr><td><input type=\"radio\" name=\"typetrspt\" value=\"0\" onclick=\"document.getElementsByName('trsptname')[0].value='Pas de transport';\"></td><td>Pas de transport</td><td><input type=\"radio\" name=\"typetrspt\" value=\"pr@mon-espace-cuisson.com\" onclick=\"document.getElementsByName('trsptname')[0].value='Acilyon test';\"></td><td>Acilyon test</td></tr>
<tr>
  <td><input type=\"radio\" name=\"typetrspt\" value=\"0\" onclick=\"document.getElementsByName('trsptname')[0].value='TNT/GLS Express d�part Acilyon'; tarifs('tnt');\"></td>
  <td class=\"tnt\">FEDEX Express/GLS Express d�part Acilyon</td>
  <td><input type=\"radio\" name=\"typetrspt\" value=\"0\" onclick=\"document.getElementsByName('trsptname')[0].value='TNT/GLS Express d�part fournisseur'; tarifs('tnt');\"></td>
  <td class=\"tnt\">FEDEX Express/GLS Express d�part fournisseur</td>
</tr>

<!-- Mazet -->

<tr>
  <td><input type=\"radio\" name=\"typetrspt\" value=\"jbuinoud@mazet.fr;kbensouna@mazet.fr\" onclick=\"document.getElementsByName('trsptname')[0].value='Mazet Enl. fournisseur pour livraison France'; tarifs('mazet');\"></td>
  <td class=\"mazet\">Mazet - Enl fourniseur pour livraison France</td>
  <td><input type=\"radio\" name=\"typetrspt\" value=\"smarzouki@mazet.fr;mcapuano@mazet.fr\" onclick=\"document.getElementsByName('trsptname')[0].value='Mazet enl. fournisseur pour livraison export'; tarifs('mazet');\"></td>
  <td class=\"mazet\">Mazet - Enl fournisseur pour livraison export</td>
</tr>

<!-- Mory -->


<tr>
  <td><input type=\"radio\" name=\"typetrspt\" value=\"psalel@morygroup.com;jroudier@morygroup.com;bagier@morygroup.com\" onclick=\"document.getElementsByName('trsptname')[0].value='Mory Group Enl. fournisseur pour livraison France'; tarifs('mory');\"></td>
  <td class=\"mory\">Mory Group - Enl fourniseur pour livraison France</td>
  <td><input type=\"radio\" name=\"typetrspt\" value=\"psalel@morygroup.com;vtrolat@morygroup.com;bagier@morygroup.com\" onclick=\"document.getElementsByName('trsptname')[0].value='Mory group enl. fournisseur pour livraison export'; tarifs('mory');\"></td>
  <td class=\"mory\">Mory Group - Enl fournisseur pour livraison export</td>
</tr>
</table>
</div>";

print "<div class=\"advgroup\">
<p>2. Pi�ces jointes</p>
<input type=\"checkbox\" name=\"notrsptpj\" value=\"0\"> Aucune
<br><input type=\"checkbox\" name=\"bonstrsptpj\" value=\"1\" checked> Bon de livraison
<br><input type=\"checkbox\" name=\"facturetrsptpj\" value=\"1\"> Facture (pour commande export uniquement)
<br><input type=\"checkbox\" name=\"othertrsptpj\" value=\"1\"> Autre : <input type=\"file\" name=\"othertrsptpjfile\">
</div>";

print "<div class=\"advgroup\">
<p>3. Objet du mail</p>
<input type=\"radio\" name=\"trsptobj\" value=\"cmd\" checked> Commande n�$mporder->id
<br><input type=\"radio\" name=\"trsptobj\" value=\"other\"> Autre : <input type=\"text\" name=\"otherobj\" size=\"80\">
</div>";

print "<div class=\"advgroup\">
<p>4. Texte d'intro</p>
<input type=\"radio\" name=\"trsptintro\" value=\"bonjour\" checked> Bonjour,<br>Veuillez trouver ci-joint une nouvelle commande de transport n�$mporder->id
<br><input type=\"radio\" name=\"trsptintro\" value=\"annule\"> Bonjour,<br>Ce mail annule et remplace le mail pr�c�demment envoy� pour la commande n�$mporder->id
<br><input type=\"radio\" name=\"trsptintro\" value=\"other\"> Autre : <br><textarea name=\"othertrsptintro\" cols=\"80\" rows=\"3\"></textarea>
</div>";

print "<div class=\"advgroup\">
<p>5. Produits</p>
<table>
<tr><td></td><td>qte</td><td>D�tail</td><td>Poids brut en kg</td></tr>";
$lignefactures=$mporder->list_lines();
foreach ($lignefactures as $lignefacture){
      $article=$DB_site2->query_first("SELECT * FROM article a, article_marque am, marque m WHERE am.artid=a.artid AND am.marqueid=m.marqueid AND a.artcode='".$lignefacture->ref_tin."'");
      $envoi=$DB_site2->query_first("SELECT * FROM mp_envoi_fournisseur_ligne WHERE type='trspt' AND ligne_facture_id=$lignefacture->id");
      $style="";
      if($envoi['date']!="") $style="background-color:green;";
      print "<tr><td style=\"$style\"><input type=\"hidden\" name=\"price_$lignefacture->id\" value=\"$article[paht]\"><input type=\"checkbox\" name=\"produits2[]\" value=\"$lignefacture->id\" onclick=\"totalassurance()\"></td><td>$lignefacture->number</td><td>$lignefacture->product_name</td><td><input type=\"hidden\" name=\"poids_$lignefacture->id\" value=\"$article[emballage_poids]\">$article[emballage_poids]</td></tr>";
}
print "<tr><td></td><td></td><td>Poids total</td><td id=\"poidstotal\"></td></tr>
</table>
</div>";

print "<div class=\"advgroup\">
<p>6. Lieu d'enl�vement</p>
<table>
<tr><td><input type=\"radio\" name=\"lieutrspt\" value=\"eurocave\"></td><td>Eurocave</td><td></td></tr>
<tr><td><input type=\"radio\" name=\"lieutrspt\" value=\"vinosafe\"></td><td>Vinosafe</td><td></td></tr>
<tr><td><input type=\"radio\" name=\"lieutrspt\" value=\"dometic\"></td><td>trspt Mazet pour Dometic</td><td>Indiquer le n� Dometic imp�rativement <input type=\"text\" name=\"dometictrsptnb\"></td></tr>
<tr><td><input type=\"radio\" name=\"lieutrspt\" value=\"lasommeliere\"></td><td>La Sommeli�re</td><td></td></tr>
<tr><td><input type=\"radio\" name=\"lieutrspt\" value=\"liebherr\"></td><td>Livraison par Eberhardt</td><td>Le ou les colis vous seront livr�s directement � votre entrep�t de <select name=\"livraisoneb\"><option style=\"font-weight:bold;\">Choisir ...</option><option value=\"Colmar\">Colmar</option><option value=\"Gonesse\">Gonesse</option><option value=\"Mont�limar\">Mont�limar</option><option value=\"Valence\">Valence</option></select> par le transporteur de la soci�t� EBERHARDT fr�res</td></tr>
<tr><td><input type=\"radio\" name=\"lieutrspt\" value=\"other\"></td><td>Autre </td><td><textarea name=\"otherlieutrspt\" cols=\"60\" rows=\"3\"></textarea></td></tr>
</table>
</div>";

print "<div class=\"advgroup\">
<p>7. Identification � mettre sur le colis</p>
<input type=\"checkbox\" name=\"trsptbl\" value=\"1\"> BL fournit ci-joint
<br><input type=\"checkbox\" name=\"trsptfacture\" value=\"1\"> Facture ci-jointe
<br><input type=\"checkbox\" name=\"trsptfragile\" value=\"1\"> Etiquette Fragile ci-jointe
<br><input type=\"checkbox\" name=\"trspttext\" value=\"1\"> Texte sur le colis : commande n�$mporder->id de Ma Cave � Vin - Acilyon pour $mporder->shipping_first_name $mporder->shipping_last_name
<br><input type=\"checkbox\" name=\"trsptimportant\" value=\"1\"> Important : enlever tous les documents de livraison de Eberhardt fr�res
<br><input type=\"checkbox\" name=\"trspttextother\" value=\"1\"> Autre : <input type=\"text\" name=\"trsptothertext\" size=\"60\">
</div>";

print "<div class=\"advgroup\">
<p>8. Date enl�vement</p>
<table>
<tr><td><input type=\"radio\" name=\"trsptenl\" value=\"0\"></td><td>Aucune</td></tr>
<tr><td><input type=\"radio\" name=\"trsptenl\" value=\"fournisseur\"></td><td>Date enl�vement chez fournisseur</td><td><input type=\"text\" name=\"trsptenlfourndate\"></td></tr>
<tr><td><input type=\"radio\" name=\"trsptenl\" value=\"eberhardt\"></td><td>Date livraison en vos locaux par le transporteur de Eberhardt</td><td><input type=\"text\" name=\"trsptlivreberhardtdate\"></td></tr>
<tr><td><input type=\"radio\" name=\"trsptenl\" value=\"other\"></td><td>Autre : <input type=\"text\" name=\"trsptenlother\" size=\"60\"></td></tr>
</table>
</div>";

print "<div class=\"advgroup\">
<p>9. Adresse livraison</p>
<table>
<tr><td><input type=\"radio\" name=\"trsptlivr\" value=\"particulier\"></td><td>Adresse client particulier = sur RDV (voir BL)</td>
<td><input type=\"radio\" name=\"trsptlivr\" value=\"pro\"></td><td>Adresse client professionnel (voir BL)</td></tr>
<tr><td><input type=\"radio\" name=\"trsptlivr\" value=\"slsblois\"></td><td>SLS BLOIS pour 18/28/36/37/41/45/58/72/86/89</td>
<td><input type=\"radio\" name=\"trsptlivr\" value=\"slsbordeaux\"></td><td>SLS BORDEAUX  16/17/24/33/40/47/64/87</td></td></tr>
<tr><td><input type=\"radio\" name=\"trsptlivr\" value=\"slsclermont\"></td><td>SLS CLERMONT  03/15/19/23/43/63</td></td>
<td><input type=\"radio\" name=\"trsptlivr\" value=\"slslille\"></td><td>SLS LILLE  02/59/62/80 </td></tr>
<tr><td><input type=\"radio\" name=\"trsptlivr\" value=\"slslyon\"></td><td>SLS LYON 01/07/26/38/42/69/71/73/74</td>
<td><input type=\"radio\" name=\"trsptlivr\" value=\"slsmetz\"></td><td>SLS METZ  08/10/21/25/39/51/52/54/55/57/67/68/70/88/90</td></tr>
<tr><td><input type=\"radio\" name=\"trsptlivr\" value=\"slsmiramas\"></td><td>SLS MIRAMAS  04/05/06/13/20/30/83/84</td>
<td><input type=\"radio\" name=\"trsptlivr\" value=\"slsrennes\"></td><td>SLS RENNES 14/22/29/35/44/49/50/53/56/61/79/85</td></tr>
<tr><td><input type=\"radio\" name=\"trsptlivr\" value=\"slstoulouse\"></td><td>SLS TOULOUSE 09/11/12/31/32/34/46/48/65/66/81/82</td>
<td><input type=\"radio\" name=\"trsptlivr\" value=\"slsvillabe\"></td><td>SLS VILLABE  27/60/75/76/77/78/91/92/93/94/95</td></tr>
</table>
</div>";

print "<div class=\"advgroup\">
<p>10. Date livraison maximum</p>
<input type=\"radio\" name=\"trsptdatemax\" value=\"0\"> Aucune
<br><input type=\"radio\" name=\"trsptdatemax\" value=\"trsptdateauto\">Date livraison imp�rative <input type=\"text\" name=\"trsptdate\">
<br><input type=\"radio\" name=\"trsptdatemax\" value=\"trsptdateother\">Date livraison maximum <input type=\"text\" name=\"trsptotherdate\">
</div>";

print "<div class=\"advgroup\">
<p>11. Information transporteur</p>
<input type=\"radio\" name=\"infotrspt\" value=\"0\"> Aucune
<br><input type=\"radio\" name=\"infotrspt\" value=\"rdv\"> Prendre RDV avec le client pour livraison
<br><input type=\"radio\" name=\"infotrspt\" value=\"other\"> Autre : <input type=\"text\" name=\"othertrsptinfo\" size=\"60\">
</div>";

print "<div class=\"advgroup\">
<p>12. Tarifs</p>
<table>
<tr><td><input type=\"checkbox\" name=\"trsptcout\" value=\"1\" onclick=\"trspttarif()\"></td><td colspan=\"2\">Cout transport <div style=\"float:right;\"><span id=\"coutpoids\"></span> CP $mporder->shipping_zip - </div></td><td><input type=\"text\" name=\"trsptcoutval\" value=\"\" size=\"5\"> �</td></tr>
<tr id=\"trecotax\"><td><input type=\"checkbox\" name=\"trsptecotax\" value=\"1\" onclick=\"trspttarif()\"></td><td>Ecotax</td><td></td><td>1 �</td></tr>
<tr id=\"trexterieur\" style=\"visibility:hidden;\"><td><input type=\"checkbox\" name=\"trsptenlext\" value=\"12.7\" onclick=\"trspttarif()\"></td><td>Enl�vement ext�rieur</td><td></td><td>12,70 �</td></tr>
<tr><td><input type=\"checkbox\" name=\"trsptparis\" value=\"6.40\" onclick=\"trspttarif()\"></td><td>Grande m�tropole Paris</td><td></td><td id=\"prixparis\">6.40 �</td></tr>
<tr><td><input type=\"checkbox\" name=\"trsptlyon\" value=\"4.9\" onclick=\"trspttarif()\"></td><td>Grande m�tropole Lyon</td><td></td><td id=\"prixlyon\">4,90 �</td></tr>
<tr id=\"trmarseille\" style=\"visibility:hidden; height:0px;\"><td><input type=\"checkbox\" name=\"trsptmarseille\" value=\"4.9\" onclick=\"trspttarif()\"></td><td>Grande m�tropole Marseille</td><td></td><td id=\"prixmarseille\">4,90 �</td></tr>
<tr id=\"tretage\" style=\"visibility:hidden;\"><td><input type=\"checkbox\" name=\"trsptetage\" value=\"25\" onclick=\"trspttarif()\"></td><td>Livraison �tage simple</td><td></td><td>25 �</td></tr>
<tr><td><input type=\"checkbox\" name=\"trsptclientpart\" value=\"7\" onclick=\"trspttarif()\"></td><td>Client particulier pour RDV</td><td></td><td id=\"prixparticulier\">7,00 �</td></tr>
<tr><td><input type=\"checkbox\" name=\"trsptclientpro\" value=\"0\" onclick=\"trspttarif()\"></td><td>Client professionnel sans RDV</td><td></td><td>0 �</td></tr>
<tr><td><input type=\"checkbox\" name=\"trsptassurcomp\" value=\"1\" onclick=\"trspttarif()\"></td><td id=\"txtassurance\">Assurance compl�mentaire 0,6% valeur</td><td></td><td><input type=\"text\" name=\"trsptassurcompval\" value=\"\" size=\"5\"> �</td></tr>
<tr><td><input type=\"checkbox\" name=\"trsptcoutother\" value=\"1\" onclick=\"trspttarif()\"></td><td>Autre : <input type=\"text\" name=\"trsptcoutothername\" size=\"80\"></td><td></td><td><input type=\"text\" name=\"trsptcoutotherval\" value=\"\" size=\"5\"> �</td></tr>
<tr><td></td><td>Total HT (hors taxe gas oil)</td><td></td><td id=\"totaltrspt\"></td></tr>
</table>
</div>";

print "<div class=\"advgroup\">
<p>13. Texte de fin</p>
<input type=\"radio\" name=\"trsptfin\" value=\"remercie\" checked> Nous vous remercions de votre envoi, par mail ou par fax, de votre confirmation de commande.<br>Cordialement<br>$user_info[nom_contact] - Acilyon - www.ma-cave-a-vin.fr<br>T�l : + 33.4.26.03.05.43 - Fax : + 33.4.26.03.05.49
</div>";

print "<input type=\"button\" onclick=\"valid2()\" value=\"Envoyer la commande au transporteur\"> <input type=\"button\" value=\"Aper�u\" onclick=\"valid2(true);\"></div>
</form>";


print "<div id=\"tab3\" style=\"visibility:hidden; position:absolute;\">
<h3>Installateur de mat�riel SLS</h3>
<form name=\"form3\" method=\"post\" enctype=\"multipart/form-data\">
<input type=\"hidden\" name=\"action\" value=\"advsls\">
<div class=\"advgroup\">
<p>1. Agence de livraison et d'installation</p>
<table>
<tr><td><input type=\"radio\" name=\"livrsls\" value=\"0\" checked></td><td>Pas d'installation</td>
<td><input type=\"radio\" name=\"livrsls\" value=\"acilyon\"></td><td>Acilyon test</td></tr>
<tr><td><input type=\"radio\" name=\"livrsls\" value=\"slsblois\"></td><td>SLS BLOIS pour 18/28/36/37/41/45/58/72/86/89</td>
<td><input type=\"radio\" name=\"livrsls\" value=\"slsbordeaux\"></td><td>SLS BORDEAUX  16/17/24/33/40/47/64/87</td></tr>
<tr><td><input type=\"radio\" name=\"livrsls\" value=\"slsclermont\"></td><td>SLS CLERMONT  03/15/19/23/43/63</td>
<td><input type=\"radio\" name=\"livrsls\" value=\"slslille\"></td><td>SLS LILLE 02/59/62/80 </td></tr>
<tr><td><input type=\"radio\" name=\"livrsls\" value=\"slslyon\"></td><td>SLS LYON 01/07/26/38/42/69/71/73/74</td>
<td><input type=\"radio\" name=\"livrsls\" value=\"slsmetz\"></td><td>SLS METZ  08/10/21/25/39/51/52/54/55/57/67/68/70/88/90</td></tr>
<tr><td><input type=\"radio\" name=\"livrsls\" value=\"slsmiramas\"></td><td>SLS MIRAMAS  04/05/06/13/20/30/83/84</td>
<td><input type=\"radio\" name=\"livrsls\" value=\"slsrennes\"></td><td>SLS RENNES 14/22/29/35/44/49/50/53/56/61/79/85</td></tr>
<tr><td><input type=\"radio\" name=\"livrsls\" value=\"slstoulouse\"></td><td>SLS TOULOUSE 09/11/12/31/32/34/46/48/65/66/81/82</td>
<td><input type=\"radio\" name=\"livrsls\" value=\"slsvillabe\"></td><td>SLS VILLABE  27/60/75/76/77/78/91/92/93/94/95</td></tr>
</table>
</div>";

print "<div class=\"advgroup\">
<p>2. Pi�ces jointes</p>
<input type=\"checkbox\" name=\"slspj\" value=\"0\"> Aucune
<br><input type=\"checkbox\" name=\"bonslspj\" value=\"1\" checked> Bon de livraison
<br><input type=\"checkbox\" name=\"factureslspj\" value=\"1\"> Facture
<br><input type=\"checkbox\" name=\"otherslspj\" value=\"1\"> Autre : <input type=\"file\" name=\"otherslspjfile\">
</div>";

print "<div class=\"advgroup\">
<p>3. Objet du mail</p>
<input type=\"radio\" name=\"slsobj\" value=\"cmd\" checked> Commande de livraison et d'installation n�$mporder->id
<br><input type=\"radio\" name=\"slsobj\" value=\"other\"> Autre : <input type=\"text\" name=\"slsotherobj\" size=\"80\">
</div>";

print "<div class=\"advgroup\">
<p>4. Texte d'intro</p>
<input type=\"radio\" name=\"slsintro\" value=\"bonjour\" checked> Bonjour,<br>Veuillez trouver ci-joint une nouvelle commande pour livraison et intallation n�$mporder->id
<br><input type=\"radio\" name=\"slsintro\" value=\"annule\"> Bonjour,<br>Ce mail annule et remplace le mail pr�c�demment envoy� pour la commande n�$mporder->id
<br><input type=\"radio\" name=\"slsintro\" value=\"otherslsintro\"> Autre : <br><textarea name=\"otherslsintro\" cols=\"80\" rows=\"3\"></textarea>
</div>";

print "<div class=\"advgroup\">
<p>5. Produits</p>
<table>
<tr><td></td><td>qte</td><td>D�tail</td><td>Poids brut en kg</td></tr>";
$lignefactures=$mporder->list_lines();
foreach ($lignefactures as $lignefacture){
      $article=$DB_site2->query_first("SELECT * FROM article a, article_marque am, marque m WHERE am.artid=a.artid AND am.marqueid=m.marqueid AND a.artcode='".$lignefacture->ref_tin."'");
      $envoi=$DB_site2->query_first("SELECT * FROM mp_envoi_fournisseur_ligne WHERE type='sls' AND ligne_facture_id=$lignefacture->id");
      $style="";
      if($envoi['date']!="") $style="background-color:green;";
      print "<tr><td style=\"$style\"><input type=\"hidden\" name=\"price3_$lignefacture->id\" value=\"$article[paht]\"><input type=\"checkbox\" name=\"produits3[]\" value=\"$lignefacture->id\" onclick=\"assurancesls()\"></td><td>$lignefacture->number</td><td>$lignefacture->product_name</td><td><input type=\"hidden\" name=\"poids3_$lignefacture->id\" value=\"$article[emballage_poids]\">$article[emballage_poids]</td></tr>";
}
print "<tr><td></td><td></td><td>Poids total</td><td id=\"poidstotal3\"></td></tr>
</table>
</div>";

print "<div class=\"advgroup\">
<p>6. Date livraison en vos entrepots</p>
<table>
<tr><td></td><td></td><td>Date livraison</td></tr>
<tr><td><input type=\"radio\" name=\"slslivr\" value=\"0\"></td><td>Aucune</td></tr>
<tr><td><input type=\"radio\" name=\"slslivr\" value=\"mory\"></td><td>Le ou les colis vous seront livr�s directement � votre entrepot par Mory Group</td><td><input type=\"text\" name=\"slslivrmorydate\"></td></tr>
<tr><td><input type=\"radio\" name=\"slslivr\" value=\"vinosafe\"></td><td>Le ou les colis vous seront livr�s directement � votre entrepot par transporteur de Vinosafe</td><td><input type=\"text\" name=\"slslivrvinosafedate\"></td></tr>
<tr><td><input type=\"radio\" name=\"slslivr\" value=\"eberhadt\"></td><td>Le ou les colis vous seront livr�s directement � votre entrepot par transporteur de Eberhadt fr�res</td><td><input type=\"text\" name=\"slslivreberhadtdate\"></td></tr>
<tr><td><input type=\"radio\" name=\"slslivr\" value=\"dometic\"></td><td>Le ou les colis vous seront livr�s directement � votre entrepot par transporteur de Dometic</td><td><input type=\"text\" name=\"slslivrdometicdate\"></td></tr>
<tr><td><input type=\"radio\" name=\"slslivr\" value=\"other\"></td><td>Autre : <input type=\"text\" name=\"otherslslvr\" size=\"60\"></td><td><input type=\"text\" name=\"slslivrotherdate\"></td></tr>
</table>
</div>";

print "<div class=\"advgroup\">
<p>7. Identification sur le colis</p>
<input type=\"checkbox\" name=\"slsidentbl\" value=\"1\"> BL fournit ci-joint
<br><input type=\"checkbox\" name=\"slsidenttxt\" value=\"1\"> Texte sur colis : commande n�$mporder->id de Ma Cave � Vin - Acilyon pour <input type=\"text\" name=\"slsidentpour\" value=\"$user[nom] $user[prenom]\">
<br><input type=\"checkbox\" name=\"slsidentimportant\" value=\"1\"> Important : enlever tous les documents de livraison de EBERHARDT Fr�res
<br><input type=\"checkbox\" name=\"slsidentother\" value=\"1\"> Autre : <input type=\"text\" name=\"slsidentothertxt\" size=\"60\">
</div>";

print "<div class=\"advgroup\">
<p>8. Adresse de livraison</p>
<input type=\"radio\" name=\"slsadrlivr\" value=\"particulier\"> Adresse client particulier = sur RDV (voir BL)
<br><input type=\"radio\" name=\"slsadrlivr\" value=\"pro\"> Adresse client professionnel (voir BL)
</div>";

print "<div class=\"advgroup\">
<p>9. Date livraison et installation maximum</p>
<input type=\"radio\" name=\"maxslslivr\" value=\"0\"> Aucune
<br><input type=\"radio\" name=\"maxslslivr\" value=\"imperatif\"> Date installation imp�rative <input type=\"text\" name=\"slsimperatifdate\">
<br><input type=\"radio\" name=\"maxslslivr\" value=\"max\"> Date installation maximum <input type=\"text\" name=\"slsmaxdate\">
<br><input type=\"radio\" name=\"maxslslivr\" value=\"other\"> <input type=\"text\" name=\"slslivrotherdate\">
</div>";

print "<div class=\"advgroup\">
<p>10. Information transporteur et d'installation</p>
<input type=\"checkbox\" name=\"slsinfo\" value=\"0\"> Aucune
<br><input type=\"checkbox\" name=\"slsrdv\"> Prendre RDV avec le client pour livraison
<br><input type=\"checkbox\" name=\"slsbranchement\"> Important : ne pas brancher �lectriquement la cave a vin. Branchement a effectuer par le client 48h apres votre instalation
<br><input type=\"checkbox\" name=\"slsmiseniveau\"> Important : mettre � niveau la cave � vin
<br><input type=\"checkbox\" name=\"slsmontage\"> Important : Si besoin, assurer le montage de la poign�e de porte et installer les clayettes a l interieur de la cave.
<br><input type=\"checkbox\" name=\"slsinfoother\"> Autre : <input type=\"text\" name=\"slsinfoothertxt\" size=\"80\">
</div>";

print "<div class=\"advgroup\">
<p>11. R�cup�ration ancienne cave</p>
<input type=\"radio\" name=\"recup\" value=\"0\"> Aucune
<br><input type=\"radio\" name=\"recup\" value=\"1\"> Attention vous devez r�cup�rer une ancienne cave qui remplace celle que vous installerez. Poids : <input type=\"text\" name=\"recuppoids\" size=\"5\">
<br><input type=\"radio\" name=\"recup\" value=\"other\"> <input type=\"text\" name=\"recupother\" size=\"80\">
</div>";

print "<div class=\"advgroup\">
<p>12. Tarifs</p>
<table>
<tr><td><input type=\"checkbox\" name=\"slscouttrspt\" value=\"1\" onclick=\"totalsls()\"></td><td>Cout transport et installation <span id=\"coutpoidssls\" style=\"float:right;\"></span></td><td></td><td><input type=\"text\" name=\"slscouttrsptval\" value=\"\" size=\"5\"></td></tr>
<tr><td><input type=\"checkbox\" name=\"slscoutcorse\" value=\"1\" onclick=\"totalsls()\"></td><td>Passage maritime Corse</td><td><input type=\"text\" id=\"prixkgcorse\" value=\"1.28\" size=\"5\"> �/kg</td><td><input type=\"text\" name=\"slscorsetotal\" size=\"5\">�</td></tr>
<tr><td><input type=\"checkbox\" name=\"slscoutreprise\" value=\"1\" onclick=\"totalsls()\"></td><td>Reprise ancienne cave</td><td></td><td><input type=\"text\" name=\"slscoutrepriseval\" value=\"\" size=\"5\"></td></tr>
<tr><td><input type=\"checkbox\" name=\"slscoutassur\" value=\"1\" onclick=\"totalsls()\"></td><td>Assurance compl�mentaire 0,35% valeur</td><td></td><td><input type=\"text\" name=\"slscoutassurval\" value=\"\" size=\"5\"></td></tr>
<tr><td><input type=\"checkbox\" name=\"slscoutother\" value=\"1\" onclick=\"totalsls()\"></td><td>Autre : <input type=\"text\" name=\"slscoutothername\" size=\"80\"></td><td></td><td><input type=\"text\" name=\"slscoutotherval\" value=\"\" size=\"5\"></td></tr>
<tr><td></td><td>Total HT (Hors taxe gas oil)</td><td></td><td id=\"totalsls\"></td></tr>
</table>
</div>";

print "<div class=\"advgroup\">
<p>13. Texte de fin</p>
<input type=\"radio\" name=\"slsfin\" value=\"1\" checked> Nous vous remercions de votre envoi, par mail ou par fax, de votre confirmation de commande.<br>Cordialement<br>$user_info[nom_contact] - Acilyon - www.ma-cave-a-vin.fr<br>T�l : +33.4.26.03.05.43 - fax : +33.4.26.03.05.49
</div>";

print "<input type=\"button\" value=\"Envoyer la commande au transporteur\" onclick=\"valid3()\"> <input type=\"button\" value=\"Aper�u\" onclick=\"valid3(true);\"></div>
</form>";

print "<div id=\"tab4\" style=\"visibility:hidden; position:absolute;\">
<h3>R�capitulatif</h3>";

$total=0;
$requete=$DB_site2->query("SELECT * FROM mp_envoi_fournisseur WHERE type='fourn' AND facturid='$mporder->id'");
print "<div class=\"advgroup\">
     <p>Fournisseur produit</p>";
if($DB_site2->num_rows($requete)>0){
    print "<table>";
    while($envoi=$DB_site2->fetch_array($requete)){
     print "<tr><td>Envoy� le ".date("d/m/Y � H:i",$envoi['date'])." � ".$envoi['destinataire']."</td><td>".round($envoi['tarif'],2)."</td></tr>";
     $total+=$envoi['tarif'];
    }
    print "</table>";
}else{
     print "Aucune commande enregistr�e";
}
print "</div>";

$requete=$DB_site2->query("SELECT * FROM mp_envoi_fournisseur WHERE type='trspt' AND facturid='$mporder->id'");
print "<div class=\"advgroup\">
     <p>Transporteurs</p>";
if($DB_site2->num_rows($requete)>0){
    print "<table>";
    while($envoi=$DB_site2->fetch_array($requete)){
     print "<tr><td>Envoy� le ".date("d/m/Y � H:i",$envoi['date'])." � ".$envoi['destinataire']."</td><td>".round($envoi['tarif'],2)."</td></tr>";
     $total+=$envoi['tarif'];
    }
    print "</table>";
}else{
     print "Aucune commande enregistr�e";
}
print "</div>";

$requete=$DB_site2->query("SELECT * FROM mp_envoi_fournisseur WHERE type='sls' AND facturid='$mporder->id'");
print "<div class=\"advgroup\">
     <p>Installateur</p>";
if($DB_site2->num_rows($requete)>0){
    print "<table>";
    while($envoi=$DB_site2->fetch_array($requete)){
     print "<tr><td>Envoy� le ".date("d/m/Y � H:i",$envoi['date'])." � ".$envoi['destinataire']."</td><td>".round($envoi['tarif'],2)."</td></tr>";
     $total+=$envoi['tarif'];
    }
    print "</table>";
}else{
     print "Aucune commande enregistr�e";
}
print "</div>";
print "<div class=\"advgroup\">
     <p>Total</p>
     Total Achat HT : $total �
     <br>Total commande HT : $totalht �
     <br>Marge brute HT : ".round($totalht-$total,2)." �
     <br>Marge % : ".round(100*(1-$total/$totalht),2)." % 
     </div>";
print "</div>";

//print "</body>";


?>