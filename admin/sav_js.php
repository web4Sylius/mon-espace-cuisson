<?php

print "\n<script language=\"javascript\" src=\"../javascripts/ajax.js\"></script>";
print "\n<script language=\"javascript\">
var serverpj=new Array();
$js
function choose1(){
    document.getElementById('litige0').style.display='none';
    document.getElementById('litige1').style.display='none';
    document.getElementById('litige2').style.display='none';
    document.getElementById('litige3').style.display='none';
    for(i=0;i<document.getElementsByName('typelitige').length;i++){
	if(document.getElementsByName('typelitige')[i].checked){
	  if(document.getElementById('litige'+i)) document.getElementById('litige'+i).style.display='block';
	  document.getElementsByName('sav_typelitige')[0].value=document.getElementsByName('typelitige')[i].value;
	}
    }
}

function verifupload(){
     setTimeout('verifupload1()',500);
}
function verifupload1(){
    ajaxrequest('sav_ul.php?progress=$savid',verifupload2);
}
function verifupload2(httprequest){
     data=httprequest.responseText.split('|');
     document.getElementById('ulprogress').innerHTML=data[1];
     if(data[0]=='1' && document.getElementById('et6tmppj')){
	document.getElementById('et6tmppj').innerHTML+='<br><input type=\"checkbox\" name=\"et6pj\" value=\"'+data[2]+'\"><a href=\"sav/'+data[2]+'.'+data[4]+'\" target=\"_blank\">'+data[3]+'</a> <img src=\"images/supprimer.gif\" title=\"Supprimer\" onclick=\"delpj('+data[2]+')\"><br>';
	serverpj[data[2]]=new Array();
	serverpj[data[2]]['url']='sav/'+data[2]+'.'+data[4];
	serverpj[data[2]]['name']=data[3];
     }
     if(data[0]=='0') setTimeout('verifupload()',500);
}
function delpj(id){
    ajaxrequest('sav_ul.php?savid=$savid&delpj='+id,removecb);
}
function removecb(httprequest){
    if(!document.getElementById('et6tmppj')) return;
    var lines=httprequest.responseText.split('\\n');
    document.getElementById('et6tmppj').innerHTML='';
    serverpj=new Array();
    for(i=0;i<lines.length;i++){
	if(lines[i]!=''){
	  var data=lines[i].split('|');
	  serverpj[data[2]]=new Array();
	  serverpj[data[2]]['url']='sav/'+data[2]+'.'+data[4];
	  serverpj[data[2]]['name']=data[3];
	  document.getElementById('et6tmppj').innerHTML+='<input type=\"checkbox\" name=\"et6pj\" value=\"'+data[2]+'\"><a href=\"sav/'+data[2]+'.'+data[4]+'\" target=\"_blank\">'+data[3]+'</a> <img src=\"images/supprimer.gif\" title=\"Supprimer\" onclick=\"delpj('+data[2]+')\"><br>';
	}
    }
}

function calcsum2(){
    if(!document.getElementById('resultsum2')) return;
    var total=0;
    for(i=0;i<=16;i++){
	document.getElementsByName('sav_chk2a'+i)[0].value=document.getElementById('chk2a'+i).checked?'1':'0';
	if(document.getElementsByName('sav_chk2a'+i)[0].value=='1') total+=parseFloat(document.getElementsByName('sav_val2a'+i)[0].value);
    }
    if(isNaN(total)) total=0;
    document.getElementById('resultsum2').innerHTML=total+' &euro;';
}
function calcsum5(){
    if(!document.getElementById('resultsum5')) return;
    var total=0;
    for(i=0;i<=16;i++){
	document.getElementsByName('sav_chk5a'+i)[0].value=document.getElementById('chk5a'+i).checked?'1':'0';
	if(document.getElementsByName('sav_chk5a'+i)[0].value=='1') total+=parseFloat(document.getElementsByName('sav_val5a'+i)[0].value);
    }
    if(isNaN(total)) total=0;
    document.getElementById('resultsum5').innerHTML=total+' &euro;';
}

function calcsum22(){
    if(!document.getElementById('resultsum22')) return;
    var total=0;
    for(i=0;i<=11;i++){
	document.getElementsByName('sav_chk22'+i)[0].value=document.getElementById('chk22'+i).checked?'1':'0';
    }
    for(i=0;i<=7;i++){
	total+=parseFloat(document.getElementsByName('sav_val22'+i)[0].value);
    }
    if(isNaN(total)) total=0;
    document.getElementById('resultsum22').innerHTML=total+' &euro;';
}

function calcsum23(){
    if(!document.getElementById('resultsum23')) return;
    var total=0;
    for(i=0;i<=8;i++){
	if(document.getElementById('chk23'+i)) document.getElementsByName('sav_chk23'+i)[0].value=document.getElementById('chk23'+i).checked?'1':'0';
    }
    for(i=0;i<=8;i++){
	if(document.getElementsByName('sav_val23'+i)[0]) total+=parseFloat(document.getElementsByName('sav_val23'+i)[0].value);
    }
    if(isNaN(total)) total=0;
    document.getElementById('resultsum23').innerHTML=total+' &euro;';
}

function calcsum24(){
    if(!document.getElementById('resultsum4')) return;
    var total=0;
   for(i=0;i<=2;i++){
	total+=parseFloat(document.getElementsByName('sav_val24'+i)[0].value);
    }
    if(isNaN(total)) total=0;
    document.getElementById('resultsum4').innerHTML=total+' &euro;';
}

function calcsum52(){
    if(!document.getElementById('resultsum52')) return;
    var total=0;
    for(i=0;i<=11;i++){
	document.getElementsByName('sav_chk52'+i)[0].value=document.getElementById('chk52'+i).checked?'1':'0';
    }
    for(i=0;i<=7;i++){
	total+=parseFloat(document.getElementsByName('sav_val52'+i)[0].value);
    }
    if(isNaN(total)) total=0;
    document.getElementById('resultsum52').innerHTML=total+' &euro;';
}

function maildossier(type,step){
    var body=mailenvoidossier;
    var subject=subjectenvoidossier;
    document.forms.et3form.action='';
    if(document.forms.et6form) document.forms.et6form.action='';
    etform=document.forms.et6form;
    switch(type){
    case 1:
      body=mailenvoidossier1;
    break;
    case 2:
      body=mailenvoidossier1;
      subject=subjectenvoidossier;
      etform=document.forms.et3form;
    break;
    case 3:
      body=mailenvoidossier3;
      subject=subjectenvoidossier3;
      etform=document.forms.et3form;
    break;
    }
    mail_auto(undefined,undefined,undefined,subject,body);
    mail_suivi('sav_'+step,'$savid');
    for(i=0;i<document.getElementsByName('et6pj').length;i++){
      if(document.getElementsByName('et6pj')[i].checked){
	var fileid=document.getElementsByName('et6pj')[i].value;
	addserverpj(serverpj[fileid]['name'],serverpj[fileid]['url']);
      }
    }
}

function modifstep(step){
    document.getElementById('edit'+step).style.display='block';
    document.getElementById('resum'+step).style.display='none';
}\n";

print "var subjectprocedure=\"Ma Cave A Vin : procédure de litige\";
var mailprocedure=\"".str_replace("\n","","<p>Bonjour,</p>
<p>Nous faisons suite au problème que vous avez rencontré lors de la livraison de vos produits. Veuillez trouver ci joint la procédure à suivre :</p>
<li>Dans un premier temps, merci d'envoyer, à notre partenaire <span style='color:red;'>ALLOIN</span> par courrier recommandé, une lettre datée et signée détaillant précisément les conditions de livraison et le(s) choc(s) subi(s) par le(s) produit(s). Nous vous rappelons que vous disposez d'un délai légal de 3 jours à partir de la date de livraison pour envoyer cette lettre.
<li>Prenez un maximum de photos des dégâts sur le(s) produit(s) pour illustrer votre demande et les transmettre avec le courrier. Prenez également des photos de l'emballage si des traces apparaissent sur le colis.
<li>Indiquez bien sur le courrier de réclamation votre numéro de commande $facturid ainsi que le numéro du bon de transport <b>$savfields[sav_nbon]</b>. Ces éléments sont indispensables pour traiter correctement votre problème. 
<li>Merci d'envoyer tous ces éléments à l'adresse suivante :
<div style='text-align:center; color:red;'>
<p>ALLOIN TRANSPORTS
<br>A l'attention de Yolande GAGLIO
<br>Espace d'Activités de L'étoile 
<br>72650 Trangé</p>
<p>(Produit au départ de CLIMADIFF) 
<br>GLS France - Service Litiges Régional
<br>ZAC du Pole Industriel Nord
<br>Metropole Lorraine
<br>Rue Georges Claude
<br>57365 ENNERY
<br>France</p>
<p>(Produit au départ d'ACILYON ou SYSTENA)
<br>GLS France - Service Litiges Régional
<br>14 rue Michel Labrousse
<br>BP 93730
<br>31037 TOULOUSE CEDEX 1
<br>France</p>
<p>(Produit au départ de La SOMMELIERE  ou  ATELIER DU VIN)
<br>GLS France - Service Litiges Régional
<br>60-70 Rue Berthie Albrecht
<br>94400 VITRY SUR SEINE
<br>France</p>
<p>(Produit au départ d'ACILYON)
<br>GLS France - Service Litiges Régional
<br>91 Rue des Sources
<br>69230 Saint Genis Laval
<br>France</p>
<p>MAZET France
<br>A l'attention de Lavenac Libé
<br>20 Boulevard Lucien Sampaix
<br>69191 Saint Fons cedex
<br>France</p>
<p>SLS-GCI
<br>A l'attention de Mme BAUER Brigitte
<br>Centre d'activité Eurologistics Bat E - 
<br>91100 Villabe
<br>France métropolitaine</p>
</div>
<li>Vous pouvez nous faire parvenir une copie de cette lettre accompagnée des photos par mail à l'adresse suivante : <b>adv@mon-espace-cuisson.com</b>. Nous privilégions le mail qui est plus rapide et qui nous permettra de traiter plus rapidement votre dossier. Mais si vous préférez, vous pouvez nous envoyer tous ces éléments par voix postale à l'adresse suivante : 
<p style='text-align:center;'><b>ACILYON
<br>Service litiges</b>
<br>150 Rue Paul Bert
<br>69003 Lyon</p>
<p>Pour gagner du temps et éviter de vous déplacer à la poste, vous avez la possibilité de le faire par internet via le site internet <a href='https://lettreenligne.laposte.fr/lregp/accueil.action'>https://lettreenligne.laposte.fr/lregp/accueil.action</a>.</p>
<li><b>TRES IMPORTANT : Pensez à bien conserver tous les emballages, notices , etc.. (en cas de retour de produit). Dans le cas contraire, vous n'aurez aucun recours pour résoudre ce litige.</b>
<p>Une fois tous ces éléments en notre possession, le dossier sera analysé et suivi auprès de notre transporteur.</p>
<p>Nous vous remercions pour votre coopération et nous nous excusons pour la gêne occasionnée. Nous vous tenons informé de l'avancée de votre dossier.</p>
<p><b>Nous vous remercions de votre confiance</b></p>
<p><b>A très bientôt</b></p>")."\";";

print "\nvar subjectaccordconclu=\"Confirmation accord dossier litige n°$savfields[sav_ndossier]\";
var mailaccordconclu=\"".str_replace("\n","","<p>Bonjour,</p>
<p>Nous faisons suite à nos différents échanges concernant le litige sur le transport n°$savfields[sav_nbon] de la commande n°$facturid</p>
<p>Voici ce qui a été conclu :</p>
<p>Nous vous remercions de nous confirmer, dans les plus brefs délais, cet accord par retour de ce mail.</p>
<p>Nous vous remercions par avance pour votre coopération.</p>")."\";";

print "var subjectenvoidossier=\"Dossier de litige n°$savfields[sav_ndossier] - commande n°$facturid\";
var mailenvoidossier=\"".str_replace("\n","","<p>Bonjour</p>
<p>Veuillez trouver ci joint le dossier de litige suite au transport n°$savfields[sav_nbon].</p>
<p>Merci de nous tenir informé,</p>")."\";
var mailenvoidossier1=\"".str_replace("\n","","<p>Bonjour</p>
<p>Veuillez trouver ci joint le dossier de litige suite à la commande n°$facturid.</p>
<p>Merci de nous tenir informé,</p>")."\";";

print "\nvar subjectprocedure2=\"Ma Cave A Vin : procédure de litige\";
var mailprocedure2=\"".str_replace("\n","","<p>Bonjour,</p>
<p>Nous faisons suite au problème que vous rencontrez lors de la mise en marche de vo(s) produit(s). Veuillez trouver ci joint la procédure à suivre :</p>
<p>Dans un premier temps, merci d'envoyer, à notre partenaire <span style='color:red;'>CLIMADIFF</span>, par courrier recommandé, une lettre datée et signée détaillant précisément le problème constaté. Nous vous rappelons que vous avez un délai légal de 7 jours pour vous manifester.</p>
<p>Indiquez bien sur le courrier de réclamation votre numéro de commande $rs_facture[facturid]. Ces éléments sont indispensables pour traiter correctement votre problème.</p>
<p>Merci d'envoyer tous ces éléments à l'adresse suivante :</p>
<div style='text-align:center; color:red;'>
<p>ASWO
<br>49-51 Rue Casimir Perrier
<br>95873 Bezons Cedex
<br>France métropolitaine (hors Corse)</p>

<p>CLIMADIFF 
<br>Vezin Vanessa
<br>Les Docks - Atrium 10.5
<br>13567 Marseille Cedex
<br>France métropolitaine (hors Corse)</p>

<p>ACILYON
<br>150 rue Paul Bert
<br>69003 Lyon
<br>France métropolitaine (hors Corse)</p>

<p>DOMETIC
<br>Service qualité
<br>ZA du Pré de la Dame Jeanne
<br>60128 PLAILLY
<br>France métropolitaine (hors Corse)</p>

<p>EBHERARDT
<br>Lutz Mireille
<br>18 Rue des Frères Eberts
<br>67024 Strasboug Cedex 1
<br>France métropolitaine (hors Corse)</p>

<p>EUROCAVE
<br>121 Rue Maire Coppeaux
<br>59610 Fourmies
<br>France métropolitaine (hors Corse)</p>

<p>JALSTON
<br>Service qualité
<br>7 Rue de Copenhague
<br>67300 Schiltigheim
<br>France métropolitaine (hors Corse)</p>

<p>L'ATELIER DU VIN
<br>Antonin RAVEL
<br>ZI Route de Chepoix
<br>60120 Breteuil sur Noye
<br>France métropolitaine (hors Corse)</p>

<p>LA SOMMELIERE
<br>Reze Isabelle
<br>ZA Portes De L'océane Ouest
<br>72650 St Saturnin
<br>France métropolitaine (hors Corse)</p>

<p>SYSTENA - FRIAX
<br>Jacquot Eric
<br>Savoie Technolac - 34 allée du lac d'Aiguebelette
<br>73377 Le Bourget du lac
<br>France métropolitaine (hors Corse)</p>
</div>

<p>Vous pouvez nous faire parvenir une copie de cette lettre accompagnée des photos par mail à l'adresse suivante : <b>adv@mon-espace-cuisson.com</b>. Nous privilégions le mail qui est plus rapide et qui nous permettra de traiter plus rapidement votre dossier. Mais si vous préférez, vous pouvez nous envoyer tous ces éléments par voix postale à l'adresse suivante : 

<p align='center'><b>ACILYON
<br>Service litiges</b>
<br>150 Rue Paul Bert
<br>69003 Lyon</p>


<p>Pour gagner du temps et éviter de vous déplacer à la poste, vous avez la possibilité de le faire par internet via le site internet <a href='https://lettreenligne.laposte.fr/lregp/accueil.action'>https://lettreenligne.laposte.fr/lregp/accueil.action</a>.</p>

<p><b>TRES IMPORTANT : Pensez à bien conserver tous les emballages, notices , etc.. (en cas de retour de produit). Dans le cas contraire, vous n'aurez aucun recours pour résoudre ce litige.</b>
<br>Une fois tous ces éléments en notre possession, le dossier sera analysé et suivi auprès de notre partenaire.</p>

<p>Nous vous remercions pour votre coopération et nous nous excusons pour la gêne occasionnée. Nous vous tenons informé de l'avancée de votre dossier.</p>

<p>Nous vous remercions de votre confiance</p>

<p>A très bientôt</p>")."\";";

print "\nvar subjectproceduresfg=\"Ma Cave A Vin : procédure suite à la panne de votre produit\";
var mailproceduresfg=\"".str_replace("\n","","<p>Bonjour,</p>

<p>Nous faisons suite au problème que vous rencontrez lors de la mise en marche de vo(s) produit(s). Votre produit est couvert par la garantie <span style='color:red;'>constructeur/Sérénité (2 ou 5 ans</span> PMOD), ainsi, nous vous invitons à appeler le numéro suivant :</p>
<p style='color:red;'>$savclient</p>
<p>Nous vous invitons à vous munir du numéro de série de votre appareil (situé généralement à l'arrière ou à l'intérieur), du numéro de commande suivant $rs_facture[facturid] ainsi que de votre facture d'achat.</p>
<p>Nous vous remercions pour votre coopération et nous restons à votre disposition pour tout renseignement complémentaire</p>")."\";";

print "\nvar subjectcontratsfg=\"Ma Cave à Vin : votre contrat SFG\";
var mailcontratsfg=\"".str_replace("\n","","<p>Bonjour,</p>
<p>Veuillez trouver ci-joint votre contrat de garantie sérénité souscrit lors de l'achat de votre produit.</p>
<p>Nous vous remercions pour votre commande et pour l'intérêt porté à nos produits. Nous restons à votre disposition pour tout renseignement complémentaire.</p>")."\";";

print "\nvar subjectretourprod=\"Ma Cave A Vin : retour de votre produit\";
var mailretourprod=\"".str_replace("\n","","<p>Bonjour,</p>
<p>Veuillez trouver ci-dessous la procédure de retour des produits dans nos locaux suite à votre souhait d'annuler votre commande.</p>
<p>Tout d'abord, le produit doit être en parfait état : aucune rayure ou quelconque dysfonctionnement ne pourra donner suite au remboursement du produit. Par ailleurs, le produit doit être retourné dans son emballage d'origine avec tous les éléments associés (notices, polystyrène, carton d'emballage, accessoires associés,...).</p>
<p>De plus, l'organisation du retour de la marchandise et son coût sont entièrement à votre charge. En outre, les avaries de transport éventuelles qui pourraient survenir relèveront de vôtre responsabilité et vous ne pourrez plus obtenir aucun remboursement de notre part. Il vous appartiendra alors de vous retourner auprès de votre prestataire de transport.</p>
<p>Nous vous remercions de retourner le produit, sous 72h, à l'adresse suivante :</p> 

<p style='color:red;text-align:center;'>ACILYON
<br>SERVICE SAV
<br>150 Rue Paul Bert
<br>69003 Lyon
<br>
<br>OU
<br>
<br>ADRESSE FOURNISSEUR</p>

<p>Une fois le produit réceptionné, il sera analysé par notre service qualité. S'il s'avère qu'il est en parfait état de fonctionnement, nous procéderons au remboursement de votre commande sous 1 mois. Dans le cas contraire, le produit endommagé sera mis à disposition pendant 2 semaines dans nos locaux si vous souhaitez le récupérer. Aucun remboursement ne pourra être réclamé (conformément à nos conditions générales de vente).</p>
<p>Soyez assuré que nous regrettons que le produit ne corresponde pas à vos attentes.</p>
<p>Nous vous remercions toutefois pour votre commande et pour l'intérêt porté à notre activité.</p>
<p>Nous vous remercions de votre confiance et de l'intérêt que vous  portez à nos produits. Je reste à votre entière disposition pour toute  information complémentaire.</p>")."\";";

print "\nvar subjectenvoidossier3=\"Dossier de litige n°$savfields[sav_ndossier] suite annulation commande n°$facturid\";
var mailenvoidossier3=\"".str_replace("\n","","<p>Bonjour</p>
<p>Veuillez trouver ci joint le dossier de litige suite à l'annulation de la commande n°$facturid.</p>
<p>Merci de nous tenir informé,</p>")."\";";

print "\nvar subjectreclamtrspt=\"Réclamation commande n°$facturid livraison n°$savfields[sav_nbon]\";
var mailreclamtrspt=\"".str_replace("\n","","<p>Bonjour</p>
<p>Veuillez trouver ci-joint le détail de l'avarie survenue sur le bon de transport n°$savfields[sav_nbon] de la livraison du $savfields[sav_datelivr].</p>
<p style='color:red;'>Détail avarie</p>
<p>Merci de nous confirmer l'ouverture du dossier de litige</p>")."\";";

print "calcsum2();
calcsum22();
calcsum23();
calcsum24();
calcsum5();
calcsum52();";

print "\n</script>\n";
