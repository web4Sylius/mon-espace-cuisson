function affich_recherche(){
	document.getElementById('recherche_avancee').style.display='block';
}


function cadreenvoi(cadre,id){

	document.querySelector('#suivimaildiv #tracking_type').value = cadre; 
	document.querySelector('#suivimaildiv #tracking_id').value = id;
	document.querySelector('#suivimaildiv #tracking_chk').value = 1;
}

function subject_select(choix){
	var subject = '';
	switch (choix){
		case 'pjt':
			subject = '#PJT'+document.querySelector('#tracking_id').value+'-'+document.getElementById('suiviid').value+'#';			
	}
	document.getElementsByName('mail_subject')[0].value = subject;
}

function besoin(id,elementid){
    document.getElementById(elementid).style.display='block';
	var ta=document.getElementById(elementid);
	ta.style.border='solid thin black';
	ta.style.backgroundColor='white';
	if(ta.value!='') if(!confirm("Attention : vous êtes sur le point d'effacer l'ancien besoin")) return;
	if(document.getElementById('updbesoin')) document.getElementById('updbesoin').style.display='block';
	
	switch(id){
	  	
	  	case 1: // Cuisinières et Pianos
	    	ta.value ="Cuisinières et Pianos";
			ta.value +="\n\n Critères principaux : ";
			ta.value +="\n\t Type de table : gaz, vitrocéramique, induction, mixte";
			ta.value +="\n\t Nombre de four : 1, 2, 3, 4, ne sait pas";
			ta.value +="\n\t Type de four : chaleur brassée, chaleur pulsée, convection naturelle, vapeur, ne sait pas";
			ta.value +="\n\t Nettoyage four : catalyse, pyrolyse, e-mail, hydrolyse, manuel, ne sait pas";
			ta.value +="\n\t Design : contemporain, retro/classique";
			ta.value +="\n\t Coloris : ";
			ta.value +="\n\t Niveau de gamme : simple, élaboré, raffiné";
			ta.value +="\n\t Largeur (cm) : 120, 100, 90, 70, 60, 50";
			ta.value +="\n\t Technologie four : électrique, gaz, ne sait pas";
			ta.value +="\n\t Pose : pose libre ou incorporable entre 2 meubles";
			ta.value +="\n\n Critères secondaires : ";
			ta.value +="\n\t Budget :";
			ta.value +="\n\t Origine de fabrication : France, Europe, autres";
		    ta.style.height='400px';
	    break;

	    case 2: // Tables de cuisson 
	    	ta.value ="Tables de cuisson";
			ta.value +="\n\n Critères principaux :";
			ta.value +="\n\t Technologie : gaz, induction, vitrocéramique, mixte, électrique";
			ta.value +="\n\t Type : domino ou plaque standard, ne sait pas";
			ta.value +="\n\t Niveau de gamme : simple, élaboré, raffiné";
			ta.value +="\n\t Nombre de foyers : 1, 2, 3, 4, 5, 6, 7, 8, ne sait pas";
			ta.value +="\n\t Pose : encastrable, pose libre";
			ta.value +="\n\t Coloris :";
			ta.value +="\n\t Largeur (en fonction de la niche d’encastrement) : 60, 70, 80, 90, 100";
			ta.value +="\n\n Critères secondaires :";
			ta.value +="\n\t Budget :";
			ta.value +="\n\t Origine de fabrication : France, Europe, autres";		
			ta.style.height = '400px';
	    break;

	    case 3:// Fours

	    	ta.value ="Fours";
			ta.value +="\n\n Critères principaux :";
			ta.value +="\n\t Type four : four multifonction, vapeur, tiroir chauffant, combine four et micro-onde,";
			ta.value +="\n\t combi électrique vapeur, ne sait pas";
			ta.value +="\n\t Pose : encastrable, pose libre";
			ta.value +="\n\t Type cuisson : multifonction air brassé, multifonction air pulsé, vapeur, convection naturelle, ne sait pas";
			ta.value +="\n\t Nettoyage : catalyse, pyrolyse, hydrolyse,";
			ta.value +="\n\t e-mail/manuel, ne sait pas";
			ta.value +="\n\t Volume : petit (>35L), standard (35/65L), grand (< 65L), ne sait pas";
			ta.value +="\n\t Coloris :";
			ta.value +="\n\t Dimensions encastrement :";
			ta.value +="\n\t Niveau de gamme : simple, élaboré, raffiné";
			ta.value +="\n\n Critères secondaires :";
			ta.value +="\n\t Budget :";
			ta.value +="\n\t Origine de fabrication : France, Europe, autres";
			ta.style.height = '400px';
	    break;

	    case 4 : // Hotte

	    	ta.value ="Hotte";
			ta.value +="\n\n Critères principaux :";
			ta.value +="\n\t Type de hotte : murale, îlot, plafond, plan de travail, incorporable meuble haut, tiroir, escamotable, plan de travail, ne sait pas";
			ta.value +="\n\t Evacuation : recyclage, évacuation extérieure, ne sait pas";
			ta.value +="\n\t Utilisation : cuisine légère, cuisine intensive, ne sait pas";
			ta.value +="\n\t Type aspiration : classique, périphérique, ne sait pas";
			ta.value +="\n\t Hotte silencieuse ? Oui / Non";
			ta.value +="\n\t Coloris : ";
			ta.value +="\n\t Forme : design, traditionnelle, rectangulaire, pyramide, ronde, ne sait pas";
			ta.value +="\n\t Commande : Bouton, tactile, avec télécommande , ne sait pas";
			ta.value +="\n\n Critères secondaires :";
			ta.value +="\n\t Budget : ";
			ta.value +="\n\t Origine de fabrication : France, Europe, autres";
		    ta.style.height='400px';
	    break;

	    case 5 : // Barbecue
	    	ta.value ="Barbecue";
			ta.value +="\n\n Critères principaux :";
			ta.value +="\n\t Energie : électrique, gaz, bois";
			ta.value +="\n\t Nombre de personne / surface = taille barbecue : 4, 8, 12 personnes";
			ta.value +="\n\t Emplacement : destine à rester dehors , rangé à l’intérieur ?";
			ta.value +="\n\t Rangement requis sous le barbecue : Oui, non, ne sait pas";
			ta.value +="\n\t Plan de travail requis : Oui, non, ne sait pas";
			ta.value +="\n\t Barbecue portable ? Oui, non, ne sait pas";
			ta.value +="\n\n Critères secondaires :";
			ta.value +="\n\t Budget :";
			ta.value +="\n\t Origine de fabrication : France, Europe, autres";
	    	ta.style.height='400px';
	    break;
		
		case 6 : // Micro-ondes
	    	ta.value ="Micro-ondes";
			ta.value +="\n\n Critères principaux :";
			ta.value +="\n\t Type de pose : pose libre / encastrable";
			ta.value +="\n\t Fonctions recherchées : uniquement micro-ondes, micro-onde combine avec un four, décongélation, micro-ondes + cuisson (dans ce cas il faut un micro-ondes Gril), ne sait pas";
			ta.value +="\n\t Volume (en L) :";
			ta.value +="\n\t Niveau de gamme : simple et fonctionnel, élaboré et soigné, haut de gamme, ne sait pas";
			ta.value +="\n\n Critères secondaires :";
			ta.value +="\n\t Budget :";
			ta.value +="\n\t Origine de fabrication : France, Europe, autres";
	    	ta.style.height='400px';
	    break;
	}
}

function popdevis(id){
    if(!confirm("Confirmez-vous l'envoi de l'email au client ?")) return;
    var page="../maildevis.php?id="+id;
       var width=900;
       var height=500;

       var top=(screen.height-height)/2;
       var left=(screen.width-width)/2;
       window.open(page,"","toolbar=no, location=no, directories=no, status=no, scrollbars=yes, resizable=no, copyhistory=yes, width="+width+", height="+height+", left="+left+", top="+top);
}

function popadddevis(id){
	var height = $jq(window).scrollTop();
	$jq('#popup_devis_projet').show();
	$jq('#popup_devis_projet').css('top',height+400);
	$jq('.num_devis').text(id);
	$jq('.inputnum_devis').val(id);
}

function poprmdevis(devisid,projetid,libelle){
	var height = $jq(window).scrollTop();
	$jq('#popup_devis_projet_delie').show();
	$jq('#popup_devis_projet_delie').css('top',height+400);
	$jq('.num_devis').text(devisid);
	$jq('.inputnum_devis').val(devisid);
	$jq('.num_projet').text(projetid);
	$jq('.inputnum_projet').val(projetid);
	$jq('.libelle_projet').text(libelle);
}

function popaddcmd(id,commande=null){
	var height = $jq(window).scrollTop();
	$jq('#popup_cmd_projet').show();
	$jq('#popup_cmd_projet').css('top',height+400);
	$jq('.num_cmd').text(id);
	$jq('.inputnum_cmd').val(id);

}

function poprmcmd(facturid,projetid,libelle){
	var height = $jq(window).scrollTop();
	$jq('#popup_projet_cmd_delie').show();
	$jq('#popup_projet_cmd_delie').css('top',height+400);
	$jq('.num_cmd').text(facturid);
	$jq('.inputnum_cmd').val(facturid);
	$jq('.num_projet').text(projetid);
	$jq('.inputnum_projet').val(projetid);
	$jq('.libelle_projet').text(libelle);
}

function back_popup_devisprojet(){
	$jq('#popup_devis_projet').hide();
	$jq('#popup_cmd_projet').hide();
	$jq('#popup_projet_cmd_delie').hide();
	$jq('#popup_devis_projet_delie').hide();
	$jq('.num_devis').text('');
	$jq('.num_projet').text('');
	$jq('.num_cmd').text('');
	$jq('.libelle_projet').text('');
}

function orderby(key,sens){
   document.getElementsByName('orderby')[0].value=key;
   document.getElementsByName('ordersens')[0].value=sens;
   document.forms.srcform.submit();
}

var dispolist=new Array();
var dispofourn=new Array();
function todispoart(state,artcode,qte,prix,enlevement,livraison,reappro,libelle,artfourn){
    var enlevementtxt='';
    if(enlevement>0){
        var d=new Date();
        d.setTime(enlevement*1000);
        enlevementtxt=d.getDate()+'/'+(d.getMonth()+1)+'/'+d.getFullYear();
    }
    var livraisontxt='';
    if(livraison>0){
        var d=new Date();
        d.setTime(livraison*1000);
        livraisontxt=d.getDate()+'/'+(d.getMonth()+1)+'/'+d.getFullYear();
    }
    if(reappro==0) reappro="";
    
    if(state){
        dispolist[artcode]="<tr><td>"+qte+"</td><td>"+artcode+"</td><td>"+libelle+"</td><td>"+prix+"</td><td>"+reappro+"</td><td>"+enlevementtxt+"</td><td>"+livraisontxt+"</td><td></td></tr>";
        dispofourn[artcode]=artfourn;
    }else{
        dispolist[artcode]=undefined;
        dispofourn[artcode]=undefined;
    }
}
  
function artmaildispo(){
      /*if(dispolist.length==0){
	  alert('Il faut cocher les articles pour lesquels il faut demander la disponibilité');
	  return;
      }*/

    var activefourn=new Array();
    if(dispofourn.length>0){
        for(l in dispofourn){
            if(dispofourn.hasOwnProperty(l)){
                for(f in dispofourn[l]){
                    if(dispofourn[l].hasOwnProperty(f)){
                    if(activefourn[dispofourn[l][f]]==undefined)
                        activefourn[dispofourn[l][f]]=1;
                    else
                        activefourn[dispofourn[l][f]]++;
                    }
                }
            }
        }
    }
    
    var msg="<p>Bonjour,</p><p>Pourriez vous nous communiquer la disponibilité pour la(les) référence(s) suivantes, nous confirmer prix d'achat et nous confirmer/indiquer la(les) date(s) d'enlèvement(s) :</p>";
    msg+="<table border=\"1\" cellspacing=\"0\" style=\"font-size:12px;\">";
    msg+="<tr><td>Qté</td><td>Réf. fabricant</td><td>Désignation</td><td>Prix Unit &euro;HT</td><td>Date réappro</td><td>Date enlèvement souhaitée par nos soins</td><td>Date livraison souhaitée par vos soins</td><td>Infos fournisseur</td></tr>";
    
    
    for(l in dispolist){
        if(dispolist[l]!=undefined && dispolist.hasOwnProperty(l)){
            msg+=dispolist[l];
        }
    }

    msg+="</table><p>Merci par avance de votre réponse, en répondant directement sur ce mail.</p>";
    var subject="Demande de disponibilité";
    if(document.querySelector('#suivimaildiv #tracking_id').value != 0) subject += " #PJT"+document.querySelector('#suivimaildiv #tracking_id').value+"#";
    mail_auto(undefined,undefined,undefined,subject,msg);
    show_signature(true);
    mail_suivi("suivi_client",userid);
    //       sendform=document.forms.addcalform;
}


function show_av(statid){
	var d=document.getElementById('art'+statid).style.display;
	if(d=='none'){
		document.getElementById('art'+statid).style.display='block';
		document.getElementById('plus'+statid).src='images/moins.jpg';
	}else{
		document.getElementById('art'+statid).style.display='none';
		document.getElementById('plus'+statid).src='images/plus.jpg';
	}
}

function show_av_all(state){
	var div=document.getElementById('stat').getElementsByTagName('div');
	for(i=0;i<div.length;i++){
	    var statid=div[i].id.substr(3);
	    div[i].style.display=(state?'block':'none');
	    document.getElementById('plus'+statid).src=(state?'images/moins.jpg':'images/plus.jpg');
	}
}
		
function editinput(input){
	document.getElementById('trsubmit').style.display='table-row';
	input.className='editinputon';
	var fields=document.getElementsByName('edituser')[0].value.split(',');
	var newfields=new Array();
	for(i=0;i<fields.length;i++) if(fields[i]!=input.name && fields[i]!='') newfields.push(fields[i]);
	newfields.push(input.name);
	document.getElementsByName('edituser')[0].value=newfields.join(',');
}

function chk(input,value,state){
		      for(i=0;i<document.getElementsByName(input).length;i++) if(document.getElementsByName(input)[i].value==value) document.getElementsByName(input)[i].checked=state;
}

function verifsuivi(){
	for(i=0;i<document.getElementsByName('etat').length;i++)
	    if(document.getElementsByName('etat')[i].checked) return true;
	alert("Il faut choisir l'état du client");
	return false;
}


function srcsimilaire(txt){
	if(txt.length<=3) return;
	ajaxrequest('marketplace/src.php?active=1&site_code=base&txt='+txt,srcsimilaire_cb);
}
		
function srcsimilaire_cb(httprequest){
	var xml=httprequest.responseXML.documentElement;
	var vistxt=new Array();
	vistxt[0]="<span style=\"color:red;\">Invisible</span>";
	vistxt[1]="Visible";
	vistxt[2]="Supprimé";

    document.getElementById('results').innerHTML='';
    if(xml.childNodes.length>0){
        var ref;
        var artid;
        
        for(i=0;i<xml.childNodes.length;i++){
            var node=xml.childNodes[i];
            if(node.nodeName=='prod'){
                var visible=node.getAttribute('active');
                artid=node.getAttribute('artid');
                ref=node.getAttribute('id');
                var div=document.createElement("div");
                var img=document.createElement("img");
                img.src="/configurations/"+myHost(window.location.host)+"/images/produits/br/"+artid+".jpg";
                div.appendChild(img);
                div.appendChild(document.createTextNode(node.getAttribute('id')+" ("+node.getAttribute('ref')+")"));
                div.appendChild(document.createElement("br"));
                div.appendChild(document.createTextNode(vistxt[visible]));
                var sel=document.createElement("a");
                sel.innerHTML="Sélectionner";
                sel.href="javascript:void(0)";
                sel.onclick=function(artid,ref){
                    return function(){
                        select_prop(artid,ref);
                    }
                }(artid,ref);
                div.appendChild(sel);
                document.getElementById('results').appendChild(div);
            }
        }
    }else
        document.getElementById('results').innerHTML='Aucun résultat';

}

function select_prop(artid,ref){
	var html="<form method=\"post\" name=\"select_prop\">\
	<img src=\"/configurations/"+myHost(window.location.host)+"/images/produits/br/"+artid+".jpg\" height=\"70\" align=\"left\" hspace=\"10\">\
    <input type=\"hidden\" name=\"prop\" value=\""+artid+"\">\
    <input type=\"hidden\" name=\"action\" value=\"prop\">\
    <b>"+ref+"</b>\
    <br>Qté : <input type=\"text\" id=\"propqte\" name=\"propqte\" value=\"1\" size=\"1\">\
    <table id=\"prop_opt\"></table>\
    <input type=\"hidden\" id=\"caractinput\">";
    var action="document.location='utilisateur.php?action=editer&userid="+userid+"&prop="+artid+"&qte='+document.getElementById('propqte').value+'&caract='+document.getElementById('caractinput').value+'#propositions_articles'";
    if(document.location.href.match(/projet/)){
        action="this.closest('form').submit()";
    }
    html+="<br><input type=\"button\" value=\"Sélectionner\" onclick=\"upd_caract();"+action+"\"></form>";
	document.getElementById('results').innerHTML=html;
	ajaxrequest("marketplace/src.php?artid="+artid,prop_caract_cb);
	// 	document.location="utilisateur.php?action=editer&userid="+userid+"&prop="+artid+"&qte="+document.getElementById('qte_'+artid).value;
}

function prop_caract_cb(httprequest){
	var options=new Array();
	var xml=httprequest.responseXML.documentElement;
 	if(xml.getElementsByTagName('caract')[0]){
		for(i=0;i<xml.getElementsByTagName('caract').length;i++){
		      var id=xml.getElementsByTagName('caract')[i].getAttribute('id');
		      var name=xml.getElementsByTagName('caract')[i].getAttribute('name');
		      var libelle=xml.getElementsByTagName('caract')[i].getAttribute('value');
		      if(!options[name]) options[name]=new Array();
		      options[name][id]=libelle;
		}
		
		var i=0;

		for(name in options){
		    if(options.hasOwnProperty(name)){
                var newselect = document.createElement('select');
                newselect.name="caract_"+i;
                newselect.setAttribute("onchange","upd_caract()");
                
                for(id in options[name]){
                    if(options[name].hasOwnProperty(id)){
                        var opt=new Option(options[name][id],id);
                        newselect.options[newselect.length]=opt;
                    }
                }

                i++;
                
                var tr=document.createElement('tr');
                var libelle=document.createElement('td');
                libelle.innerHTML=name+" : ";
                tr.appendChild(libelle);
                var sel=document.createElement('td');
                sel.appendChild(newselect);
                tr.appendChild(sel);
                document.getElementById('prop_opt').appendChild(tr);
		    }
		}
    }
    
    var caractvalids=new Array();
    var selects=document.querySelectorAll("#prop_opt select");
    for(s in selects){
        caractvalids.push(selects[s].value);
    }
    document.getElementById("caractinput").value=caractvalids.join(',');
}

function upd_caract(){
	var caractvalid="";
	var select=document.getElementById('prop_opt').getElementsByTagName("select");
	for(i=0;i<select.length;i++){
	    caractvalid+=","+select[i].value;
	}
	document.getElementById('caractinput').value=caractvalid.substr(1);
}

function suppr_fidel(id){
	if(confirm("Etes-vous sur de vouloir effacer cette fidélisation ?"))
	  document.location="utilisateur.php?action=editer&userid="+userid+"&del_fidel="+id;
}

function show_config_info(id){
	var display=document.getElementById('configinfo'+id).style.display;
	if(display=="block"){
	    document.getElementById('configinfo'+id).style.display="none";
	    document.getElementById('configplus'+id).src="images/plus.jpg";
	}else{
	    document.getElementById('configinfo'+id).style.display="block";
	    document.getElementById('configplus'+id).src="images/moins.jpg";
	}
}

function show_compare_info(id){
	var display=document.getElementById('compareinfo'+id).style.display;
	if(display=="block"){
	    document.getElementById('compareinfo'+id).style.display="none";
	    document.getElementById('compareplus'+id).src="images/plus.jpg";
	}else{
	    document.getElementById('compareinfo'+id).style.display="block";
	    document.getElementById('compareplus'+id).src="images/moins.jpg";
	}
}

function show_artvu_info(id){
	var display=document.getElementById('artvuinfo'+id).style.display;
	if(display=="block"){
	    document.getElementById('artvuinfo'+id).style.display="none";
	    document.getElementById('artvuplus'+id).src="images/plus.jpg";
	}else{
	    document.getElementById('artvuinfo'+id).style.display="block";
	    document.getElementById('artvuplus'+id).src="images/moins.jpg";
	}
}

function affiche_div(show_id, hide_id){
     if(document.getElementById(show_id).style.display !== 'revert'){
          document.getElementById(show_id).style.display = 'revert';
     } else {
          document.getElementById(show_id).style.display = 'none';
     }
     if(hide_id) document.getElementById(hide_id).style.display = 'none';
}

function valider_suppression(url){
     if(confirm("Etes-vous sûr de vouloir supprimer cet utilisateur ?")){
          document.location = url;
     }
}

function ancien_contact(url){
	document.location = url;
}

function modifconseils(rep){
	document.getElementById('modifconseils').value = rep ;
	if(rep == 0 )
		document.getElementById('conseils').value = 'non';
	else if(rep == 1)
		document.getElementById('conseils').value = 'oui';

	document.forms.conseils.submit();
}

function random_password_generate()
{
    var passwordChars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    var randPwLen = 8;
    var randPassword = Array(randPwLen).fill(passwordChars).map(function(x) { return x[Math.floor(Math.random() * x.length)] }).join('');
    return randPassword;
}

lastreq="";
function loadsuivi(userid,req,all,pjt,date){
    if(req==undefined) req=lastreq;
    if(all==undefined) all=0;
    if(date==undefined) date = $jq('#annee_suivi').val();
    $jq("<img>").attr("src","../images/templates/wait-line.gif").css({"display":"block","margin":"20px auto"}).prependTo('#tab_suivi_client');
    var url='ajax/suivi_client.php?request='+req+'&all='+all;
    if(userid!=undefined) url+='&userid='+userid;
    if(pjt!=undefined) url+='&pjt='+pjt;
    if(date!=undefined) url+='&date='+date;
    $jq.ajax(url,   // request url
    {
        success: function (data, status, xhr) {    // success callback function
            $jq('#tab_suivi_client').html(data);
        }
    });
    lastreq=req;
}

function user_pjt(state){
    var projets=document.querySelectorAll("#projet_infos > div");
    for(p=0;p<projets.length;p++){
        var display=(state==="" || projets[p].dataset.encours==state)?"":"none";
        projets[p].style.display=display;
    }
}
