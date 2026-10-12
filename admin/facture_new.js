/*/////////////////////////
// FONCTIONS AJAX	
/////////////////////////*/

var client = null;
var client_recherche_article = null;
var client_recherche_caract = null;
var client_modif = null;
var client_calcul = null;
var effectuer_modif = false;
var lignefactureid_last_modif = 0;
var facturid;

var params=location.search.substring(1).split("&");
for(i in params){
    var p=params[i].split("=");
    if(p[0]=="facturid") facturid=p[1];
}

/*///////////
// Fonction recherche_article()
// Affiche un select contenant la liste des articles d'une categorie
///////////*/
function recherche_article()
{
	//Si une recherche était en cours, on l'annule
	if(client_recherche_article)
		client_recherche_article.abort();

	
	var div_liste_caracteristiques = document.getElementById("liste_caracteristiques_nouveau_aticle");
	var div_liste_article = document.getElementById("liste_articles_nouveau_article");
	var liste_article = document.getElementById("select_articles_nouveau_article");
	var categorie = document.getElementById("select_categories_nouveau_article");
	var catid = categorie.options[categorie.selectedIndex].value;
	
	//On désactive le select des articles pendant la recherche
	if(liste_article)
	{
		liste_article.disabled = true;
		liste_article.length = 1;
		liste_article.options[0].value = "-1";
		if (catid == -1)
			liste_article.options[0].innerHTML = "Articles";
		else		
			liste_article.options[0].innerHTML = "Recherche...";
	}
	div_liste_caracteristiques.innerHTML = ''
	
	if (catid != -1)
	{
		//Une catégorie a été sélectionnée
		client_recherche_article = creer_xmlhttprequest();
		client_recherche_article.open('POST', 'facture_ajax.php');
		client_recherche_article.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		client_recherche_article.onreadystatechange = function()
		{
			if(client_recherche_article.readyState == 4 && client_recherche_article.status == 200)
			{
				//On affiche le nouveau select
				div_liste_article.innerHTML = client_recherche_article.responseText;
				
				if(!liste_article)	//Seulement la 1ère fois
					var liste_article = document.getElementById("select_articles_nouveau_article");
				
				liste_article.disabled = false;
				client_recherche_article = null;
			}
		}
		client_recherche_article.send('categorie=' + catid + '&action=liste_article_categorie');
	}
}

/*///////////
// Fonction recherche_caracteristique()
// Retourne un tableau avec la liste des caracteristiques d'un article
///////////*/
function recherche_caracteristique()
{
	//Si une recherche était en cours, on l'annule
	if (client_recherche_caract)
		client_recherche_caract.abort;
	//Si une recherche d'article est en cours, on attends la fin de celle-ci
	while(client_recherche_article){};
	
	var div_liste_caracteristiques = document.getElementById("liste_caracteristiques_nouveau_aticle");
	var tab_liste_caracteristiques = document.getElementById("tableau_caracteristiques_nouveau_article");
	var article = document.getElementById("select_articles_nouveau_article");
	var quantite = document.getElementById("qte_nouveau_article");
	var artid = article.options[article.selectedIndex].value;
	
	//On supprime le tableau pendant la recherche
	if(tab_liste_caracteristiques)
		div_liste_caracteristique.innerHTML = '';
	
	if (artid != -1)
	{
		//Une catégorie a été sélectionnée
		client_recherche_caract = creer_xmlhttprequest();
		client_recherche_caract.open('POST', 'facture_ajax.php');
		client_recherche_caract.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		client_recherche_caract.onreadystatechange = function()
		{
			if(client_recherche_caract.readyState == 4 && client_recherche_caract.status == 200)
			{
				div_liste_caracteristiques.innerHTML = client_recherche_caract.responseText;
				if (client_recherche_caract.responseText == '<div id="nb_caract_nouveau_article" style="display:none">0</div>')
					div_liste_caracteristiques.innerHTML = 'Aucune caracteristique' + client_recherche_caract.responseText;
				
				quantite.focus();
				client_recherche_caract = null;
			}
		}
		client_recherche_caract.send('article=' + artid + '&action=liste_caracteristique_article');
	}else
	{
		quantite.value='';
		div_liste_caracteristiques.innerHTML = '';
	}
}

function submit_nouveau_article(evt, facturid)
{
	if(evt.keyCode == 13)
		ajouter_article(facturid);
}

/*///////////
// Fonction ajouter_article()
// Paramètres : facturid
// Paramètres à fournir : facturid, catid, artid, caract_libelle[], caractval_libelle[], qte
// Permet de rajouter un nouvel article à la facture
///////////*/
function ajouter_article(facturid)
{
	var categorie = document.getElementById('select_categories_nouveau_article');
	var catid = categorie.options[categorie.selectedIndex].value;
	var quantite = parseInt(document.getElementById('qte_nouveau_article').value);
	var paysid = document.getElementById('paysid');
	//paysid = paysid.options[paysid.selectedIndex].value;
	paysid = paysid.value;
	var div_liste_caracteristiques = document.getElementById('liste_caracteristiques_nouveau_aticle');
	var caractval = null;
	var caract_libelle = null;
	var caractval_libelle = null;
	var caractvalid = null;
	var liste_caract_libelle = '';
	var liste_caractval_libelle = '';
	var liste_caractvalid = '';
	
	//Un ajout est déjà en cours, donc on ne fait rien pour éviter des problèmes dans la base de données
	//Sinon, il faudrait gérer les transactions pour annuler les requetes déjà effectuée avant le client.abort()
	//console.log('deb');
	if (client == null)
		{
		if(catid != -1)
			{//Categorie sélectionnée
			var article = document.getElementById('select_articles_nouveau_article');
			var artid = article.options[article.selectedIndex].value;
			var date = new Date();
			if(artid != -1)
				{//Article sélectionné
				var nb_caract = parseInt(document.getElementById("nb_caract_nouveau_article").innerHTML);
				//console.log('ok');
				if (!isNaN(quantite) || quantite <= 0)
					{
					//window.alert('ok');
					if(nb_caract > 0)
						{
						//L'article possède au moins une caracteristique
						for(var i=0;i < nb_caract; i++)
							{
							//On récupère les caractéristiques du produit | = %5B%5D
							caract_libelle = document.getElementById('caract_nouveau_article_' + i).innerHTML;
							caractval = document.getElementById('caractval_nouveau_article_' + i);
							caractval_libelle = caractval.options[caractval.selectedIndex].text;
							caractvalid = caractval.options[caractval.selectedIndex].value;
							if(i == 0)
								{
								liste_caract_libelle = caract_libelle;
								liste_caractval_libelle = caractval_libelle;
								liste_caractvalid = caractvalid;
								}
							else
								{
								liste_caract_libelle += '|' + caract_libelle;
								liste_caractval_libelle += '|' + caractval_libelle;
								liste_caractvalid += '|' + caractvalid;
								}
							}
						}
					//On peut maintenant appeler la fonction d'ajout de ligne
					div_liste_caracteristiques.innerHTML = 'Ajout en cours...';
					client = creer_xmlhttprequest();
					client.open('POST', 'facture_ajax.php');
					client.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
					client.onreadystatechange = 
					function()
						{
						if(client.readyState == 4 && client.status == 200)
							{
							categorie.selectedIndex = 0;
							document.getElementById('qte_nouveau_article').value = '';
							location.replace('facture.php?action=editer&facturid=' + facturid + '&time=' + date.getTime() +'#detail_facture');
							client = null;
							}
						}
					//Paramètres à fournir : facturid, catid, artid, caract_libelle, caractval_libelle, qte
					var parametres = 'facturid=' + facturid + '&catid=' + catid + '&artid=' + artid + '&qte=' + quantite;
					parametres += '&caract_libelle=' + encodeURIComponent(liste_caract_libelle) + '&caractval_libelle=' + encodeURIComponent(liste_caractval_libelle);
					parametres += '&nbcaract=' + nb_caract + '&caractvalid=' + encodeURIComponent(liste_caractvalid) +'&paysid=' + paysid + '&action=ajouter_article';
					client.send(parametres);
					}
				else
					{
					document.getElementById('qte_nouveau_article').focus();
					window.alert('Vous devez saisir une quantité numérique et positif');
					}
				}
			else
				{
				div_liste_caracteristiques.innerHTML = '';
				window.alert('Vous devez sélectionner un article');
				}
			}
		else
			{
			div_liste_caracteristiques.innerHTML = '';
			window.alert('Vous devez sélectionner une categorie');
			}
		}		
	}

/*//////////
// Fonction supprimer_article
// Parametres : lignefactureid, facturid
// Supprime un article de la commande
//////////*/
function supprimer_article(lignefactureid,facturid)
{
	if(lignefactureid > 0 && facturid > 0)
	{
		var date = new Date();
		if(client)
			client.abort();

		if(client = creer_xmlhttprequest())
		{
			client.open('POST', 'facture_ajax.php');
			client.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
			var caracteristique = document.getElementById('div_caracteristique_liste_article_' + lignefactureid);
			caracteristique.innerHTML = 'Suppression en cours...';
			client.onreadystatechange = function()
			{
				if(client != null && client.readyState == 4 && client.status == 200)
					var tableau = document.getElementById('table_article');
					if(tableau)
					{
						var ligne = document.getElementById('article_' + lignefactureid).rowIndex;
						tableau.deleteRow(ligne);
						calculer_prix_total();
						client = null;
					}
					//location.replace('facture.php?action=editer&facturid=' + facturid + 'time=' + date.getTime() +'#detail_facture');
			}
			
			client.send('lignefactureid=' + lignefactureid + '&facturid=' + facturid + '&action=supprimer_article');
		}
	}
}

/*///////////
// Fonction submit_modifier_ligne(evt,lignefactureid)
// Modification de la quantite
///////////*/
function submit_modifier_article(lignefactureid)
	{
	var div_qte = document.getElementById('qte_' + lignefactureid);
	var qte = parseInt(div_qte.value);

	if (div_qte.value != '')
		{
		if (!isNaN(qte) || qte < 1)
			modifier_article(lignefactureid);
		}
	}

function modifier_article_quantite(event,lignefactureid)
{
	var evt_touche = window.event || event;
	if (evt_touche.keyCode == 13)
		submit_modifier_article(lignefactureid);
}


/*///////////
// Fonction modifier_article(lignefactureid)
// Parametres : lignefactureid
// Modifie un article
///////////*/
function modifier_article(lignefactureid)
	{
	var div_modif_en_cours = document.getElementById('modif_en_cours_' + lignefactureid);
	var div_liste_caract = document.getElementById('div_caracteristique_liste_article_' + lignefactureid);
	var text_qte = document.getElementById('qte_' + lignefactureid);
	div_liste_caract.style.display = 'none';
	div_modif_en_cours.style.display = 'block';
	text_qte.disabled = 'yes';
		
	var facturid = document.getElementById('div_facturid').innerHTML;			
	var artid = document.getElementById('artid_' + lignefactureid).value;
	var catid = document.getElementById('catid_' + lignefactureid).value;
	var qte = document.getElementById('qte_' + lignefactureid).value;
	var lignefacturecaractid = document.getElementById('lignefacturecaractid_' + lignefactureid).value;
	var caractvalid = '';
	
	//DOM HTML pour les caracteristiques
	var div_caracteristique = document.getElementById('div_caracteristique_liste_article_' + lignefactureid);
	var table_caracteristique = div_caracteristique.firstChild;
	
	
	//Mise en forme des caracteristiques pour le passage en PHP
	if (table_caracteristique.length)
		{
		if(table_caracteristique.rows[0].cells.length > 0)
			{
			//L'article possède des caracteristiques
			for(var i=0;i<table_caracteristique.rows[0].cells.length;i++)
				{
				select_caract = table_caracteristique.rows[0].cells[i].lastChild;
				caractvalid += select_caract.options[select_caract.selectedIndex].value;
				if (i != (table_caracteristique.rows[0].cells.length - 1))
					caractvalid += '|';
				}
			}
		}
	//On possède toutes les informations, on peut lancer la mise à jour
	client_modif = creer_xmlhttprequest();
	client_modif.open('POST', 'facture_ajax.php');
	client_modif.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	client_modif.onreadystatechange = function()
		{
		if(client_modif.readyState == 4 && client_modif.status == 200)
			{
			calculer_prix_article(lignefactureid);
			div_liste_caract.style.display = 'block';
			div_modif_en_cours.style.display = 'none';
			text_qte.disabled = '';
			client_modif = null;
			}
		}
	//Paramètres à fournir : facturid, catid, artid, caract_libelle, caractval_libelle, qte
	var parametres = 'facturid=' + facturid + '&lignefactureid=' + lignefactureid + '&catid=' + catid + '&artid=' + artid + '&qte=' + qte;
	parametres += '&lignefacturecaractid=' + lignefacturecaractid + '&caractvalid=' + caractvalid + '&action=modifier_article';
	client_modif.send(parametres);
	}

/*///////////
// Fonction calculer_prix_total
// Calcule le prix total de la commande
///////////*/
function calculer_prix_total()
{
	var div_prix_total = document.getElementById('prix_total_facture');
	var div_prix_article = '';
	var div_remise = null;			//id=montant_remise
	var div_frais_de_port = null;	//id=fraisdeport
	var div_prix_cadeau = null;		//id=prixcadeau
	var div_frais_gestion = null;	//id=fraisdegestion
	var div_contre_remb = null;		//id=contreremboursement
	var facturid = document.getElementById('div_facturid').innerHTML;
	var prix_frais_port = 0;
	var prix_total = 0;
	var i = 2;
	var article = true;
	var ligne_frais_port = null;
	var tableau = document.getElementById('table_article');
	
	if(tableau && (tableau.rows.length > 2))
	{
		//On additionne le prix de tous les articles
		while(article)
			{
			if(tableau.rows[i].id != 'nouveau_article' && tableau.rows[i].id != 'champ_libre' && tableau.rows[i].id != 'ligne_frais_port' && tableau.rows[i].id != '')
				{
				//On récupère la div contenant le prix
				div_prix_article = tableau.rows[i].cells[5].firstChild;
				prix_total += parseFloat(div_prix_article.innerHTML);
				i++;
				}
			else
				article = false;
		}
	}
	
	//On ajoute les frais supplementaires
	if(div_remise = document.getElementById('montant_remise'))
	{
		montant = div_remise.innerHTML;
		prix_total += parseFloat(montant.replace(/,/,'.'));
	}
	if(div_prix_cadeau = document.getElementById('prixcadeau'))
	{
		montant = div_prix_cadeau.innerHTML;
		prix_total += parseFloat(montant.replace(/,/,'.'));
	}
	if(div_frais_gestion = document.getElementById('fraisdegestion'))
	{
		montant = div_frais_gestion.innerHTML;
		prix_total += parseFloat(montant.replace(/,/,'.'));
	}
	if(div_contre_remb = document.getElementById('contreremboursement'))
	{
		montant = div_contre_remb.innerHTML;
		prix_total += parseFloat(montant.replace(/,/,'.'));
	}
	
	//Gestion des frais_de_port
	client_calcul = creer_xmlhttprequest();
	client_calcul.open('POST', 'facture_ajax.php');
	client_calcul.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	client_calcul.onreadystatechange = function()
	{
		if(client_calcul.readyState == 4 && client_calcul.status == 200)
		{
			ligne_frais_port = document.getElementById('ligne_frais_port');
			prix_frais_port = client_calcul.responseText;
			prix_total += parseFloat(prix_frais_port.replace(/,/,'.'));
			if (prix_frais_port == '0')
				ligne_frais_port.style.display = 'none';
			else
				ligne_frais_port.style.display = '';
				
			prix_total = Math.round(prix_total*100)/100;
			prix_total_final = prix_total.toString();
			prix_total_final = prix_total_final.replace(/\./,',');
			chaine = prix_total_final.match(',');
			if (chaine == null)
				prix_total_final += ',00';			
			div_prix_total.innerHTML = prix_total_final;
			client_calcul = null;
		}
	}
	
	var parametres = 'facturid=' + facturid + '&action=calcul_frais_de_port';
	client_calcul.send(parametres);
}

/*///////////
// Fonction calculer_prix_article(lignefactureid)
// Parametres : lignefactureid
// Calcule le prix de l'article
///////////*/
function calculer_prix_article(lignefactureid)
	{
	var tableau = document.getElementById('table_article');
	if(tableau)
		{
		//On sélectionne la ligne de l'article modifié
		var ligne = document.getElementById('article_' + lignefactureid).rowIndex;
		var div_caracteristique = document.getElementById('div_caracteristique_liste_article_' + lignefactureid);
		var table_caracteristique = div_caracteristique.firstChild;
		var div_prix_article = document.getElementById('prix_article_' + lignefactureid);
		var div_qte = document.getElementById('qte_' + lignefactureid);
		var qte = parseInt(document.getElementById('qte_' + lignefactureid).value);
		var select_caract = null;
		var diff_prix = 0;
		var prix_article = 0;
		var prix_base_article = parseFloat(document.getElementById('prix_base_article_' + lignefactureid).value);
		//On regarde si des caracteristiques sont présentes
		if (table_caracteristique.length)
			{
			if(table_caracteristique.rows[0].cells.length > 0)
					{
					//L'article possède des caracteristiques, le prix de l'article peut dont être modifié
					for(var i=0;i<table_caracteristique.rows[0].cells.length;i++)
						{
						select_caract = table_caracteristique.rows[0].cells[i].lastChild;
						diff_prix += parseInt(select_caract.options[select_caract.selectedIndex].title);
						}
					}
			}
		prix_article = qte * (prix_base_article + diff_prix);
		div_qte.value = qte;
		prix_article = Math.round(prix_article*100)/100;
		prix_article = prix_article.toString();
		prix_article = prix_article.replace(/\./,',');
		if (prix_article == '0')
			prix_article = '0,00' ;
		div_prix_article.innerHTML = prix_article;
		calculer_prix_total();
		}
	}


/*///////////
// Fonction change(id)
// Parametres : id
// Affiche ou cache le formulaire de recherche
///////////*/
function change(id) 
{
	if (document.getElementById(id).style.display == 'block')
		document.getElementById(id).style.display = 'none'
	else
		document.getElementById(id).style.display = 'block'
}

/*///////////
// Fonction ajuster_taille_champ_libre()
// Ajuste la taille des champs libre
///////////*/
function ajuster_taille_champ_libre()
{
	/*var champ_libre_artcode = document.getElementById('champ_libre_artcode');
	var champ_libre_libelle = document.getElementById('champ_libre_libelle');
	var champ_libre_qte = document.getElementById('champ_libre_qte');
	var champ_libre_montant = document.getElementById('champ_libre_montant');
	var table_article = document.getElementById('table_article');

	window.alert(table_article.rows[1].cells[1].offsetWidth);
	champ_libre_artcode.width = table_article.rows[1].cells[1].offsetWidth;
	champ_libre_libelle.style.width = table_article.rows[1].cells[2].offsetWidth + table_article.rows[1].cells[3].offsetWidth;
	champ_libre_qte.style.width = table_article.rows[1].cells[4].offsetWidth;
	champ_libre_montant.style.width = table_article.rows[1].cells[5].offsetWidth;
	*/
}


/*///////////
// Fonction creer_xmlhttprequest()
// Retourne un objet xmlHttpRequest adapté au navigateur
///////////*/
function creer_xmlhttprequest()
{
	var xhr = null; 
	 
	if(window.XMLHttpRequest) // Firefox et autres
	   xhr = new XMLHttpRequest(); 
	else if(window.ActiveXObject){ // Internet Explorer 
	   try {
                xhr = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                xhr = new ActiveXObject("Microsoft.XMLHTTP");
            }
	}
	else { // XMLHttpRequest non supporté par le navigateur 
	   alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
	   xhr = false; 
	}
	return xhr;
}

/*/////////////////////////
// FIN FONCTIONS AJAX	
/////////////////////////*/

function showcmdstat(){
      if(document.getElementById('divstat').style.display=='none'){
	  document.getElementById('divstat').style.display='block';
	  document.getElementById('plusstat').innerHTML='- Info statistiques';
      }else{
	  document.getElementById('divstat').style.display='none';
	  document.getElementById('plusstat').innerHTML='+ Info statistiques';
      }
}
function showcontacts(){
      if(document.getElementById('contacttbl').style.display=='none'){
	  document.getElementById('contacttbl').style.display='block';
	  document.getElementById('contactdiv').innerHTML='- Historique contacts';
      }else{
	  document.getElementById('contacttbl').style.display='none';
	  document.getElementById('contactdiv').innerHTML='+ Historique contacts';
      }
}

function marge(paht,pvo,tx,type){
    if(type=="ttc"){
	var pv=document.getElementById('manpvttc').value/tx;
	document.getElementById('manpv').value=Math.round(pv*100)/100;
    }else{
	var pv=document.getElementById('manpv').value;
	document.getElementById('manpvttc').value=Math.round(pv*tx*100)/100;
    }
    
    document.getElementById('manpc').innerHTML=Math.round((pv-paht)/pv*1000)/10+"%";
    document.getElementById('manremise').innerHTML=Math.round((pvo-pv)*100)/100;
    document.getElementById('manremisettc').innerHTML=Math.round((pvo-pv)*tx*100)/100;
    document.getElementById('manremisepc').innerHTML=Math.round(10000*(1-(pv/pvo)))/100;
    document.getElementById('manmarge').innerHTML=Math.round((pv-paht)*100)/100;
      
}

function srcfournisseur_hide(){
    document.getElementById('srcfournisseur').style.display='none';
}
function srcfournisseur(txt){
    if(txt.length<3) return;
    document.getElementById('srcfournisseur').style.display='block';
    document.getElementById('srcfournisseur').innerHTML="<img src=\"/configurations/www.ma-cave-a-vin.fr/images/templates/ajax_wait.gif\">";
    ajaxrequest('mailbox_ajax.php?txt='+txt,srcfournisseurcb);
}
function srcfournisseurcb(httpRequest){
    var xml=httpRequest.responseXML.documentElement;

    if(xml.getElementsByTagName("contact")[0]){
	var html="<table>";
	for(i=0;i<xml.getElementsByTagName("contact").length;i++){
	    var id=xml.getElementsByTagName("contact")[i].getAttribute("id");
	    var mail=xml.getElementsByTagName("contact")[i].getAttribute("mail");
	    var societe=xml.getElementsByTagName("contact")[i].getAttribute("societe");
	    var nom=xml.getElementsByTagName("contact")[i].getAttribute("nom");
	    var fonction=xml.getElementsByTagName("contact")[i].getAttribute("fonction") || "";
	    var type=xml.getElementsByTagName("contact")[i].getAttribute("type");
	    var telephone=xml.getElementsByTagName("contact")[i].getAttribute("telephone");
	    var portable=xml.getElementsByTagName("contact")[i].getAttribute("portable");
	    var tel=xml.getElementsByTagName("contact")[i].getAttribute("tel");
	    
	    var suiviid=facturid;
	    var suivitype="suivi_commande";
	    if(facturid==undefined && userid!=undefined){
		suiviid=userid;
		suivitype="suivi_client";
	    }
	    html+="<tr><td><img src=\""+icon(type)+"\" width=\"26\"></td>";
	    html+="<td> "+societe+"<br>"+fonction+"<br><span class=\"nom\">"+nom+"</span></td>";
	    html+="<td><span class=\"suivilink\" onclick=\"suiviauto('tel_out','"+type+"',"+id+",'Appel téléphone "+addslashes(societe)+" ("+addslashes(nom)+")',"+(tel==1?"'"+telephone+"'":undefined)+");\">"+telephone;
	    if(portable!="") html+="<br>"+portable;
	    html+="</span><br><span class=\"suivilink\" onclick=\"suiviauto('tel_in','"+type+"',"+id+",'Appel reçu de "+addslashes(societe)+" ("+addslashes(nom)+")');\">Appel reçu</span> <span class=\"suivilink\" onclick=\"suiviauto('mail_in','"+type+"',"+id+",'Email reçu de "+addslashes(societe)+" ("+addslashes(nom)+")');\">Email reçu</span>";
	    html+="<br><span class=\"suivilink\" onclick=\"mail_auto(undefined,'"+mail+"');mail_suivi('"+suivitype+"',"+suiviid+");srcfournisseur_hide();suivi_hide();\" title=\""+mail+"\">"+mail+"</span></td></tr>";
	}
	html+="</table>";
    }
    document.getElementById('srcfournisseur').innerHTML=html;
}

function suiviauto(moyen,type,destinataire,txt,tel){
	  document.getElementsByName('moyen')[0].value=moyen;
	  document.getElementsByName('type')[0].value=type;
	  document.getElementsByName('destinataire')[0].value=destinataire;
	  document.getElementById('suivititle').innerHTML=txt;
	  if(moyen=="mail_out"){
	    document.getElementById('suividiv').style.display='none';
	    maildivdisplay='block';
	  }else{
	    document.getElementById('suividiv').style.display='block';
	    maildivdisplay='none';
	  }
	  if(document.getElementById('suivimaildiv')) document.getElementById('suivimaildiv').style.display=maildivdisplay;
	  document.getElementById('srcfournisseur').style.display='none';
	  if(tel!=undefined) document.getElementById('suivititle').innerHTML+="&nbsp; <span class=\"epigy\" onclick=\"epigy_call('"+tel+"');\"><img src=\"images/ring_phone_2.gif\">"+tel+"</span>";
	  switch(type){
	    case "":
	    document.getElementsByName('rappeltype')[0].checked=true;
	    break;
	    case "client":
	    document.getElementsByName('rappeltype')[1].checked=true;
	    break;
	    case "pro":
	    document.getElementsByName('rappeltype')[2].checked=true;
	    break;
	  }
}
function suivi_hide(){
	  document.getElementById('suividiv').style.display='none';
}

function filtersuivi(){
    var filterchk=new Array("pro","client","int");
    var types=new Array();
    for(i=0;i<filterchk.length;i++)
      types[filterchk[i]]=document.getElementsByName('filter'+filterchk[i])[0].checked;
    var linkids=new Array();
    for(i=0;i<document.getElementsByName('savlinks').length;i++){
      var input=document.getElementsByName('savlinks')[i];
      linkids[input.value]=input.checked;
    }
    showsuivi(types,linkids);
}

function showsuivi(types,linkids){
	  var table=document.getElementById('tablesuivi');
	  var trs=table.getElementsByTagName('tr');
	  var rg=new RegExp("^([a-z]{3,6})-[0-9]{1,6}-([0-9]{5})$");
	  for(i=0;i<trs.length;i++){
	      m=trs[i].id.match(rg);
	      if(m!=null) trs[i].style.display=(types[m[1]] && linkids[m[2]]?"table-row":"none");
// 	      if(trs[i].id.substr(0,type.length)==type) trs[i].style.display=(state?"table-row":"none");
	  }
}

function planningcb(){
    document.getElementsByName('jour')[0].value=cal_jour; 
    document.getElementsByName('pour')[0].value=document.getElementById('user').value;
}

function addslashes(txt) {
    return (txt + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}
 
function changedatelivr(){
    var url=""+document.location+"";
    var file=url.substr(0,url.indexOf("?"));
    var paramstr=url.substr(url.indexOf("?"));
    var params=paramstr.split("&");
    var newurl=file;
    for(i=0;i<params.length;i++){
	if(params[i].indexOf("#")>-1) params[i]=params[i].substr(0,params[i].indexOf("#"));
	var part=params[i].split("=");
	if(part[0]!="datelivr") newurl+=params[i]+"&";
    }
    newurl+="&datelivr="+cal_timestamp;

    document.location=newurl;
}

function paiement_mail(){
    if(confirm("Voulez-vous envoyer le mail de réception de paiement ?")) document.getElementsByName("envoi_mail")[0].value=1;
    document.forms.paiement_form.submit();
}

function codepromo_src(txt){
    if(txt.length<=3) return;
    var url="marketplace/src.php?codepromo="+txt;
    ajaxrequest(url,codepromo_cb);
    document.getElementById('bon_result').inerHTML='<img src=\"/configurations/www.ma-cave-a-vin.fr/images/templates/ajax_wait.gif\">';
}

function codepromo_cb(httprequest){
    var xml=httprequest.responseXML.documentElement;
    var html='';
    if(xml.getElementsByTagName('codepromo')[0]){
	html+="<table width=\"100%\" style=\"border:solid thin #808080;\">";
	html+="<tr><td>code</td><td>Valeur</td><td>Validité</td><td>Applicable sur</td><td>Client</td><td>Options offertes</td></tr>";
	for(i=0;i<xml.getElementsByTagName('codepromo').length;i++){
	    var cp=xml.getElementsByTagName('codepromo')[i];
	    var code=cp.getAttribute('code');
	    var valeur=cp.getAttribute('valeur');
	    var active=cp.getAttribute('active');
	    var datefin=cp.getAttribute('datefin');
	    var minimum=cp.getAttribute('minimum');
	    var applicable=cp.getAttribute('applicable');
	    var client=cp.getAttribute('client');
	    var options=cp.getAttribute('options');
	    var port=cp.getAttribute('port');
	    html+="<tr><td style=\"color:"+(active==1?"green":"red")+"\">"+code+"</td><td>"+valeur+"</td><td>"+datefin+"</td><td>"+applicable+(minimum>0?"<br>Mini "+minimum+"&euro;":"")+"</td><td>"+client+"</td><td>"+port+"<br>"+options+"</td><td><a href=\"javascript:select_codepromo('"+code+"')\">Sélectionner</a></td></tr>";
	}
    }else
	html="Pas de résultat";
    
    document.getElementById('bon_result').style.display='block';
    document.getElementById('bon_result').innerHTML=html;
}

function select_codepromo(code){
    if(code==undefined) code="";
    document.getElementById('codepromo_code').value=code;
    document.getElementById('selected_codepromo').style.display=(code!=""?"inline":"none");
    document.getElementById('bon_result').style.display='none';
}

function prop_tab(tab){
    var tabs=new Array("prop_accessoires","prop_vin","prop_av","prop_autourvin","prop_rafr");
    for(i=0;i<tabs.length;i++){
	document.getElementById('td_'+tabs[i]).className="";
	document.getElementById(tabs[i]).style.display="none";
    }
    if(tab!=undefined){
      document.getElementById('td_'+tab).className="prop_tab_selected";
      document.getElementById(tab).style.display="block";
    }
}

function add_prop(artid,artcode,libelle){
    var free=0;
    for(i=1;i<=9;i++){
	var v=document.getElementsByName('article'+i+'artid')[0].value;
	if(v==artid){alert("Cet article est déjà sélectionné");return;}
	if(v=="" && free==0 && document.getElementById('article'+i).style.display!="none") free=i;
    }
    if(free>0){
	articleid=free;
	select_article_mail(artid,artcode,libelle);
    }else
	alert("Le maximum d'article est déjà atteint");
}

var articleid=0;
function article_mail(txt,id){
    prop_tab();
    articleid=id;
    if(txt.length<=3) return;
    var url="marketplace/src.php?txt="+txt;
    ajaxrequest(url,article_mail_cb);
    document.getElementById('article_mail_result').innerHTML='<img src=\"/configurations/www.ma-cave-a-vin.fr/images/templates/ajax_wait.gif\">';
}

function article_mail_cb(httprequest){
    var xml=httprequest.responseXML.documentElement;
      var html='';
  
      var vistxt=new Array();
      vistxt[0]='<br><span style=\"color:red;\">Invisible</span>';
      vistxt[1]='<br>Visible';
      if(xml.getElementsByTagName('prod')[0]){
	  html+='<table width=\"100%\" style=\"border:solid thin #808080;\">';
	  for(i=0;i<xml.getElementsByTagName('prod').length;i++){
		var description=xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
		var id=xml.getElementsByTagName('prod')[i].getAttribute('id');
		var artid=xml.getElementsByTagName('prod')[i].getAttribute('artid');
		var actif=xml.getElementsByTagName('prod')[i].getAttribute('active');
		var stock=xml.getElementsByTagName('prod')[i].getAttribute('stock');
		var marque=xml.getElementsByTagName('prod')[i].getAttribute('marque');
		var ref=xml.getElementsByTagName('prod')[i].getAttribute('ref');
		var lot=xml.getElementsByTagName('prod')[i].getAttribute('lot');
		var ventes=xml.getElementsByTagName('prod')[i].getAttribute('ventes');
		var ventes1an=xml.getElementsByTagName('prod')[i].getAttribute('ventes1an');
		var stock='Pas en stock';
		if(xml.getElementsByTagName('prod')[i].getAttribute('stock')=='1') stock='En stock : '+xml.getElementsByTagName('prod')[i].getAttribute('stockqte');
		if(actif<2) html+="<tr id=\"tr_"+artid+"\"><td><img src=\"/configurations/www.ma-cave-a-vin.fr/images/produits/br/"+artid+".jpg\" width=\"80\" id=\"articleimg"+artid+"\"></td><td style=\"border-bottom:solid thin #cccccc;\">"+marque+"<br>"+id+"<br>"+ref+"<br>"+vistxt[actif]+"</td><td style=\"border-bottom:solid thin #cccccc;\"><b>"+description+"</b><br>Ventes : "+ventes+"<br>Ventes 1 an : "+ventes1an+"<br>"+stock+"</td><td style=\"border-bottom:solid thin #cccccc;\"><a href='javascript:select_article_mail("+artid+",\""+id+"\",\""+description.replace(/\'/g,"&apos;")+"\")'>Sélectionner</a></td></tr>";
	  }
	  html+='</table>';
      }else
	  html='Pas de résultat';

      document.getElementById('article_mail_result').innerHTML=html;
}

function select_article_mail(artid,artcode,libelle){
      var siteurl=document.getElementById('siteurl').value;
      if(artid!=undefined){
	document.getElementById('article'+articleid).innerHTML="<img src=\"/configurations/www.ma-cave-a-vin.fr/images/produits/br/"+artid+".jpg\" width=\"50\"> <img src=\"images/supprimer.gif\" onclick=\"articleid="+articleid+";select_article_mail();\">";
	document.getElementsByName('article'+articleid+'artid')[0].value=artid;
	document.getElementsByName('article'+articleid+'desc')[0].value=libelle+" "+artcode;
	document.getElementById('srcarticle'+articleid).value=artcode;
	document.getElementById('article_mail_result').innerHTML="";
      }else{
	document.getElementById('article'+articleid).innerHTML="";
	document.getElementsByName('article'+articleid)[0].value=""
	document.getElementsByName('article'+articleid+'artid')[0].value="";
	document.getElementsByName('article'+articleid+'desc')[0].value="";
	document.getElementById('srcarticle'+articleid).value="";
      }
}

function com_mail_generate(){
      var siteurl=document.getElementById('siteurl').value;
      var langue=document.getElementById('langue').value;
      
      for(l=0;l<=2;l++){
	  var large=document.getElementsByName('l'+(l+1)+'v')[0].checked;
	  for(i=1;i<=3;i++){
	      var articleid=3*l+i;
	      var artid=document.getElementsByName('article'+articleid+'artid')[0].value;
	      var desc=document.getElementsByName('article'+articleid+'desc')[0].value;
	      if(artid!="")
		document.getElementsByName('article'+articleid)[0].value="<a href=\""+siteurl+"/art,"+artid+",-.htm\"><img src=\""+siteurl+"/images/mail/"+langue+"/vignette-"+artid+(large?"":"S")+".jpg\" border=\"0\" alt=\""+desc+"\" hspace=\"2\" vspace=\"2\"></a>";
	      else
		document.getElementsByName('article'+articleid)[0].value="";
	  }
      }

      document.getElementsByName('univers')[0].value="";
      if(document.getElementById('univers1').value!=""){
	  document.getElementsByName('univers')[0].value="<tr><td height=\"10\"></td></tr><tr><td align=\"center\"><img src=\""+siteurl+"/images/mail/"+langue+"/commercial_univers.jpg\"></td></tr><tr><td height=\"10\"></td></tr>";
	  document.getElementsByName('univers')[0].value+="<tr><td align=\"center\">";
	  document.getElementsByName('univers')[0].value+="<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"590\">";
	  document.getElementsByName('univers')[0].value+="<tr><td><a href=\""+siteurl+"/"+document.getElementById('universurl'+document.getElementById('univers1').value).value+"\"><img src=\""+siteurl+"/images/mail/"+langue+"/univers-"+document.getElementById('univers1').value+"R.jpg\" border=\"0\"></a></td>";

	  if(document.getElementById('univers2').value!="")
	      document.getElementsByName('univers')[0].value+="<td><a href=\""+siteurl+"/"+document.getElementById('universurl'+document.getElementById('univers2').value).value+"\"><img src=\""+siteurl+"/images/mail/"+langue+"/univers-"+document.getElementById('univers2').value+".jpg\" align=\"right\" border=\"0\"></a></td>";
      
	  document.getElementsByName('univers')[0].value+="</tr></table>";
	  document.getElementsByName('univers')[0].value+="</td></tr>";
      }
      
      if(document.getElementById('codepromo_code').value!="") document.getElementsByName('codepromo')[0].value="<img src=\""+siteurl+"/images/mail/bon_mail.php?code="+document.getElementById('codepromo_code').value+"\">";
      
//       var footerimg="<a href=\""+siteurl+"\"><img src=\"/images/mail/"+langue+"/commercial_footer.jpg\" border=\"0\"></a>";
//       document.getElementsByName('marques')[0].value=(document.getElementById('footer').checked?footerimg:"");
      
      mail_auto(undefined,undefined);
      mail_enreg(undefined,document.getElementById('dest').value,undefined,document.getElementById('com_mail_id').value,langue);
      add_exp_list("contact");
}

function typevignette(ligne){
      var large=document.getElementsByName('l'+ligne+'v')[0].checked;
      var lastv=ligne*3;
      if(large){
	  document.getElementById('tdarticle'+lastv).style.display="none";
	  document.getElementById('article'+lastv).style.display="none";
	  document.getElementById('srcarticle'+lastv).value="";
	  document.getElementById('article'+lastv).innerHTML="";
      }else{
	  document.getElementById('tdarticle'+lastv).style.display="table-cell";
	  document.getElementById('article'+lastv).style.display="table-cell";
      }
}

function selectinfolivr(infolivr){
    document.getElementById('infolivr0').style.color=document.getElementById('infolivr1').style.color="";
    document.getElementsByName('infolivr')[0].value=Number(infolivr);
    document.getElementById('infolivr'+infolivr).style.color='red';
    document.getElementById('infofacture').style.display='block';
}
function expedier_cmd(infofacture){
    document.getElementsByName('infofacture')[0].value=infofacture;
    document.forms.expedier.submit();
}

function edit_reglement(reglement_id,montant,moyenid,delaisid,date,commentaire){
    document.getElementsByName('reglement_id')[0].value=reglement_id;
    document.getElementsByName('reglement_date')[0].value=date;
    document.getElementsByName('reglement_moyen')[0].value=moyenid;
    document.getElementsByName('reglement_delais')[0].value=delaisid;
    document.getElementsByName('reglement_montant')[0].value=montant;
    document.getElementsByName('reglement_commentaire')[0].innerHTML=commentaire;
    document.getElementById('edit_reglement').style.display="block";
}

function edit_reception_paiement(reglement_id){
    document.getElementsByName('reglement_rcpt_id')[0].value=reglement_id;
    document.getElementById('edit_reception').style.display="block";
}

function add_reglement(montant){
    montant=parseFloat(montant);
    if(montant==undefined) montant=0;
    document.getElementsByName('reglement_id')[0].value=0;
    document.getElementsByName('reglement_date')[0].value="";
    document.getElementsByName('reglement_moyen')[0].value=1;
    document.getElementsByName('reglement_montant')[0].value=montant;
    document.getElementById('edit_reglement').style.display="block";
}

function rm_reglement(id){
    if(!confirm("Etes-vous sur de vouloir effacer ce règlement ?")) return;
    document.getElementsByName('rm_reglement')[0].value=id;
    document.forms.form_rm_reglement.submit();
}

function valid_reglement(id,moyen,montant) {
    if(!confirm("Confirmez-vous la réception de ce paiement par "+moyen+" de "+montant+" ?")) return;
    if(confirm("Envoyer l'email au client ?")) document.getElementsByName('valid_reglement_email')[0].value=1;
    document.getElementsByName('valid_reglement_id')[0].value=id;
    document.forms.form_valid_reglement.submit();
}

function toggle_historique(divid){
     if(document.getElementById(divid).style.display === 'none'){
          document.getElementById(divid).style.display = 'block';
     } else {
          document.getElementById(divid).style.display = 'none';
     }
}

function fireEvent(element,event){
    if (document.createEventObject){
        // dispatch for IE
        var evt = document.createEventObject();
        return element.fireEvent('on'+event,evt)
    }
    else{
        // dispatch for firefox + others
        var evt = document.createEvent("HTMLEvents");
        evt.initEvent(event, true, true ); // event type,bubbling,cancelable
        return !element.dispatchEvent(evt);
    }
}