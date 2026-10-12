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
var dispolist=new Array();
var dispofourn=new Array();

var params=location.search.substring(1).split("&");
for(i in params){
    var p=params[i].toString().split("=");
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

function typemodal(lienDevisModal,lienDevisDetailModal,lienDevisEnvoi){
	jQuery('#devis').attr('href', lienDevisModal);
	jQuery('#devis-detail').attr('href', lienDevisDetailModal);
	jQuery('#devis-envoi').attr('href', lienDevisEnvoi);
	jQuery('#devis').attr('target', '_blank');
	jQuery('#devis-detail').attr('target', '_blank');
	jQuery(".modal-devis").toggle('slow');
}

jQuery(".close-modal").click(function(){
	jQuery(".modal-devis").hide('slow');
});


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

/**
 * Permet de changer une image lors d'une action (ex: clique sur un bouton)
 *
 * @param  alphanumérique id Identifiant de la balise img
 * @param  string img1 1ère image à switch
 * @param  string img2 2ème image à switch
 */
function change_image(id, img1, img2){

	if(document.getElementById(id)){
		if(document.getElementById(id).src == img1){
			document.getElementById(id).src = img2;
		} else {
			document.getElementById(id).src = img1;
		}
	}
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
function srcfournisseurid(fid){
    document.getElementById('srcfournisseur').style.display='block';
    document.getElementById('srcfournisseur').innerHTML="<img src=\"/images/templates/ajax-loader.gif\">";
    ajaxrequest('mailbox_ajax.php?fid='+fid,srcfournisseurcb);
}
function srcfournisseur(txt,id,add_fourn=null){
    if(txt.length<3){
        document.getElementById(id).style.display='none';
        return;
    }
    document.getElementById(id).style.display='block';
    document.getElementById(id).innerHTML="<img src=\"/images/templates/ajax-loader.gif\">";
    //Permet de lancer la recherche de fournisseur selon l'endroit ou on se trouve (contacts ou suivis)
    if(add_fourn == null){
		ajaxrequest('mailbox_ajax.php?txt='+txt+'&id='+id,srcfournisseurcb);
	}
	else{
		ajaxrequest('mailbox_ajax.php?txt='+txt+'&id='+id,srcfournisseurpjt);
	}
}
function srcfournisseurcb(httpRequest){
    var xml=httpRequest.responseXML.documentElement;
    
    var outlook="";
    
    if(xml.getElementsByTagName("contact")[0]){
	var html="<span class=\"close\" onclick=\"this.parentNode.style.display='none';\">X</span><table>";
	var id_src=xml.getElementsByTagName("contact")[0].getAttribute("typesrc");
	if(id_src=='srcfournisseur'){
		html+="<tr class=\"srcth\"><th>Société</th><th>Type</th><th>Nom/prénom</th><th>Fonction</th><th>Email</th><th>Portable</th><th>Fixe</th></tr>";
	}
	else{
		html+="<tr class=\"srcth\"><th>Société</th><th>Nom/prénom</th><th>Fonction</th><th>CP/Ville</th><th>Pays</th><th>Email</th><th>Portable</th><th>Fixe</th></tr>";
	}
	for(i=0;i<xml.getElementsByTagName("contact").length;i++){
	    var id=xml.getElementsByTagName("contact")[i].getAttribute("id");
	    var mail=xml.getElementsByTagName("contact")[i].getAttribute("mail");
	    var societe=xml.getElementsByTagName("contact")[i].getAttribute("societe");
	    var type_fourn=xml.getElementsByTagName("contact")[i].getAttribute("type_fourn");
	    var nom=xml.getElementsByTagName("contact")[i].getAttribute("nom");
	    var fonction=xml.getElementsByTagName("contact")[i].getAttribute("fonction") || "";
	    var ville=xml.getElementsByTagName("contact")[i].getAttribute("ville");
	    var cp=xml.getElementsByTagName("contact")[i].getAttribute("codepostal");
	    var type=xml.getElementsByTagName("contact")[i].getAttribute("type");
	    var telephone=xml.getElementsByTagName("contact")[i].getAttribute("telephone");
	    var pays=xml.getElementsByTagName("contact")[i].getAttribute("pays");
	    var portable=xml.getElementsByTagName("contact")[i].getAttribute("portable");
	    var tel=xml.getElementsByTagName("contact")[i].getAttribute("tel");
	    var caller=xml.getElementsByTagName("contact")[i].getAttribute("caller");
	    //var languemail=xml.getElementsByTagName('contact')[i].getAttribute("languemail");
        var site_code=xml.getElementsByTagName('contact')[i].getAttribute("site_code");
        var mailcontact=xml.getElementsByTagName('contact')[i].getAttribute("mailcontact");

	    var suiviid=facturid;
	    var suivitype="suivi_commande";
	    if(facturid==undefined && userid!=undefined){
            suiviid=userid;
            suivitype="suivi_client";
	    }
	    html+="<tr>";
	    html+="<td> "+societe+"</td>";
	    if(id_src=='srcfournisseur'){
	    	html+="<td>"+type_fourn+"</td>";
		}
	    html+="<td><span class=\"nom\">"+nom+"</span></td>";
	    html+="<td>"+fonction+"</td>";
	    if(id_src!=='srcfournisseur'){
	    	html+="<td>"+cp+", "+ville+"</td>";
	    	html+="<td>"+pays+"</td>";
		}

	    var action="mail_auto(undefined,'"+mail+"',undefined,undefined,undefined,undefined,undefined,true);";
        if(document.location.href.match(/projet/)){
            action="suiviauto('mail_out','pro',"+id+");document.getElementById('dest').value='"+mail+"';";
            const urlParams = new URLSearchParams(document.location.search);
            var tracking="#PJT"+urlParams.get('projetid')+"#";
            outlook="<a href=\"mailto:"+mail+"?subject="+tracking+"\" class=\"suivilink mail_out\">Ecrire avec Outlook</a>";
        }

	    if(mail!="") {
            html+="<td class=\"suivilink mail_out\"><span onclick=\"show_choix_suivi(this)\">"+mail+"</span>\
            <span class=\"addsuivi\">\
            <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>\
            <span class=\"suivilink\" onclick=\""+action+"document.getElementById('srcclient').style='none';mail_suivi('"+suivitype+"',"+suiviid+");mail_services('FR','base','adv,commercial,sav');srcfournisseur_hide();suivi_hide();add_exp_list('contact','"+mailcontact+"','"+site_code+"');\" title=\""+mail+"\">Ecrire email</span>\
            <span class=\"suivilink mail_in\" onclick=\"document.getElementById('srcclient').style='none';suiviauto('mail_in','"+type+"',"+id+",'Email reçu de "+addslashes(societe)+" ("+addslashes(nom)+")');\">Email reçu</span>\
            <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span>\
            "+outlook+"\
            </span></td>";
        }
        else {
        	html+="<td></td>";
        }
       
	    if(portable!="") {
	    	html+="<td class=\"suivilink tel_out\"><span onclick=\"show_choix_suivi(this)\">"+portable+"</span>\
            <span class=\"addsuivi\">\
            <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>\
            <span class=\"suivilink\" onclick=\"document.getElementById('srcclient').style='none';suiviauto('tel_out','"+type+"',"+id+",'Appel téléphone "+addslashes(societe)+" ("+addslashes(nom)+")',"+(tel==1?"'"+portable+"'":undefined)+");epigy_call('"+portable+"','"+caller+"');\">Click to call</span>\
            <span class=\"suivilink tel_in\" onclick=\"document.getElementById('srcclient').style='none';suiviauto('tel_in','"+type+"',"+id+",'Appel reçu de "+addslashes(societe)+" ("+addslashes(nom)+")');\">Appel reçu</span>\
            <span class=\"suivilink tel_out\" onclick=\"document.getElementById('srcclient').style='none';suiviauto('tel_out','"+type+"',"+id+",'Appel de "+addslashes(societe)+" ("+addslashes(nom)+")');\">Appel effectué</span>\
            <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span>\
            </span></td>";
        }
        else {
        	html+="<td></td>";
        }

        if(telephone!="") { html+="<td class=\"suivilink tel_out\"><span onclick=\"show_choix_suivi(this)\">"+telephone+"</span>\
            <span class=\"addsuivi\">\
            <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>\
            <span class=\"suivilink\" onclick=\"document.getElementById('srcclient').style='none';suiviauto('tel_out','"+type+"',"+id+",'Appel téléphone "+addslashes(societe)+" ("+addslashes(nom)+")',"+(tel==1?"'"+telephone+"'":undefined)+");epigy_call('"+telephone+"','"+caller+"');\">Click to call</span>\
            <span class=\"suivilink tel_in\" onclick=\"document.getElementById('srcclient').style='none';suiviauto('tel_in','"+type+"',"+id+",'Appel reçu de "+addslashes(societe)+" ("+addslashes(nom)+")');\">Appel reçu</span>\
            <span class=\"suivilink tel_out\" onclick=\"document.getElementById('srcclient').style='none';suiviauto('tel_out','"+type+"',"+id+",'Appel de "+addslashes(societe)+" ("+addslashes(nom)+")');\">Appel effectué</span>\
            <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span>\
            </span></td>";
        }
        else {
        	html+="<td></td>";
        }

        
        html+="</tr>";
	}
	html+="</table>";
    }

    var id = xml.getElementsByTagName("type")[0].getAttribute("id");
    document.getElementById(id).innerHTML=html;
}

//Fonction qui va générer les noms des fournisseurs selon le onkeyup pour l'envoyer vers projet.php
function srcfournisseurpjt(httpRequest){

	var xml=httpRequest.responseXML.documentElement;
	var projetid=window.location.search.substr(1);
    
    if(xml.getElementsByTagName("contact")[0]){
	var html="<span class=\"close\" onclick=\"this.parentNode.style.display='none';\">X</span><form method=\"post\" ><input type=\"hidden\" name=\"action\" value=\"relation_fourn\"><table>";
	
	for(i=0;i<xml.getElementsByTagName("contact").length;i++){
	    var societe=xml.getElementsByTagName("contact")[i].getAttribute("societe");
	    
	    html+="<tr>";
	    html+="<td> "+societe+" <input type=\"checkbox\" name=\"societe_fourn\" value=\""+societe+"\"></td>"; 
        html+="</tr>";
	}
	html+="<tr><td><input type=\"submit\" value=\"OK\"></td></tr></table></form>";
    }

    var id = xml.getElementsByTagName("type")[0].getAttribute("id");
    document.getElementById(id).innerHTML=html;
}

function show_choix_suivi(val,ficheinfo=null) {
	jQuery('.addsuivi').find('.suivilink').hide();
	jQuery('.addsuivi').find('.close').hide();
	if(ficheinfo != null) { /// pour les mails et appels des infos contacts, et pas le suivi
		var addsuivi = jQuery(val).next('.addsuivi').children();
		for (let i = 0; i < addsuivi.length; i++) {
			console.log(addsuivi[i]);
			addsuivi[i].style.display='block';
		}
	}
	else { /// pour le suivi
		var addsuivi = jQuery(val).parent().children().last();
		for (let i = 0; i < addsuivi[0].children.length; i++) {
			addsuivi[0].children[i].style.display='block';
		}
	}
}

function close_choix_suivi(val) {
	jQuery(val).hide();
	jQuery(val).nextUntil().hide();
}

function copy(val) {
	var toCopy  = jQuery(val).parent().prev();
	toCopy.after('<input type="text" value="'+toCopy.text()+'" id="temp_input" />');
	const copyText = document.getElementById("temp_input");
	copyText.select();
  	document.execCommand("copy");
  	jQuery('#temp_input').remove();
  	jQuery(val).parent().children().hide();
	jQuery(val).parent().children().nextUntil().hide();
  	alert(toCopy.text() + ' a bien été copié.');
}

function suiviauto(moyen,type,destinataire,txt,tel){
    document.getElementsByName('moyen')[0].value=moyen;
    document.getElementsByName('type')[0].value=type;
    document.getElementsByName('destinataire')[0].value=destinataire;
    document.getElementsByName('callid')[0].value="";
    document.getElementById('suivititle').innerHTML=txt;
    
    if(moyen=="mail_out"){
        document.getElementById('suividiv').style.display='none';
        maildivdisplay='block';
        document.getElementById('userid_suivi').value=type=="client"?destinataire:"";
    }else{
        document.getElementById('suividiv').style.display='block';
        maildivdisplay='none';
    }
    
    if(document.getElementById('suivimaildiv'))
        document.getElementById('suivimaildiv').style.display=maildivdisplay;
    
    document.getElementById('srcfournisseur').style.display='none';
    
    if(tel!=undefined) document.getElementById('suivititle').innerHTML+="&nbsp; <span class=\"epigy\" onclick=\"epigy_call('"+tel+"');\"><img src=\"images/ring_phone_2.gif\">"+tel+"</span>";
    
    /*switch(type){
        case "":
        document.getElementsByName('rappeltype')[0].checked=true;
        break;
        case "client":
        document.getElementsByName('rappeltype')[0].checked=true;
        break;
        case "pro":
        document.getElementsByName('rappeltype')[2].checked=true;
        break;
    }*/
}
function suivi_hide(){
	  document.getElementById('suividiv').style.display='none';
}

function filtersuivi(){
    var filterchk=new Array("pro","client","int","sav");
    var types=new Array();
    /*for(i=0;i<filterchk.length;i++)
      types[filterchk[i]]=document.getElementsByName('filter'+filterchk[i])[0].checked;
     */
    var linkids=new Array();
    for(i=0;i<document.getElementsByName('savlinks').length;i++){
      var input=document.getElementsByName('savlinks')[i];
      linkids[input.value]=input.checked;
    }
    showsuivi(linkids);
}

function showsuivi(linkids){
	  var table=document.getElementById('tablesuivi');
	  var trs=table.getElementsByTagName('tr');
	  var rg=new RegExp("^([a-z]{3,6})-[0-9]{1,6}-([0-9]{5,6})$");
	  for(i=0;i<trs.length;i++){
	      m=trs[i].id.match(rg);
	      if(m!=null) trs[i].style.display=(linkids[m[2]]?"table-row":"none");
	  }
}

function callid_suivi_client(data){
    var callid=document.getElementsByName('callid');
    if(callid.length>0) callid[0].value=data;
}

/*function showsuivi(types,linkids){
	  var table=document.getElementById('tablesuivi');
	  var trs=table.getElementsByTagName('tr');
	  var rg=new RegExp("^([a-z]{3,6})-[0-9]{1,6}-([0-9]{5,6})$");
	  for(i=0;i<trs.length;i++){
	      m=trs[i].id.match(rg);
	      console.log(m);
	      if(m!=null) trs[i].style.display=(types[m[1]] && linkids[m[2]]?"table-row":"none");
			//if(trs[i].id.substr(0,type.length)==type) trs[i].style.display=(state?"table-row":"none");
	  }
}*/

function planningcb(){

	var inputjour = document.getElementsByName('jour');
	var inputpour = document.getElementsByName('pour');

	for(i=0;i<inputjour.length;i++){
	   inputjour[i].value=cal_jour; 
	   inputpour[i].value=document.getElementById('user').value;
	 }
}

function planningmailcb(){
    document.getElementById('poursuivi').value=document.getElementById('user').value;
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
    newurl+="&datelivr_client="+cal_timestamp;

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
    document.getElementById('bon_result').inerHTML='<img src=\"/images/templates/ajax_wait.gif\">';
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

var articleid=0;
function move_prop(input){
    articleid=input.id.match(/[0-9]+/);
    var left=input.offsetLeft;
    var top=input.offsetTop;
    var width=input.offsetWidth;
    document.getElementById('propositions_tab').style.left=(left+width)+'px';
    document.getElementById('propositions_tab').style.top=top+'px';
    document.getElementById('propositions_tab').style.display='block';
    
    document.getElementById('article_mail_result').style.left=(left+width)+'px';
    document.getElementById('article_mail_result').style.top=top+'px';
    document.getElementById('article_mail_result').style.display='none';
}

function prop_tab(tab){
    var tabs=new Array("prop_av","prop_prop");
    
    var visible=(tab!=undefined && document.getElementById('td_'+tab).className=="prop_tab_selected");
    
    for(i=0;i<tabs.length;i++){
	document.getElementById('td_'+tabs[i]).className="";
	document.getElementById(tabs[i]).style.display="none";
	document.getElementById('td_'+tabs[i]).getElementsByTagName('img')[0].src="images/plus.jpg";
    }
    if(tab!=undefined && !visible){
      document.getElementById('td_'+tab).className="prop_tab_selected";
      document.getElementById(tab).style.display="block";
      document.getElementById('td_'+tab).getElementsByTagName('img')[0].src="images/moins.jpg";
    }
}

function add_prop(artid,artcode,libelle){

    select_article_mail(artid,artcode,libelle);
    document.getElementById('propositions_tab').style.display='none';
}

function article_mail(txt,id,prop){
    if(prop == undefined){ 

    	prop_tab();
		//articleid=id;
    	document.getElementById('propositions_tab').style.display=(txt.length>0?'none':'block');
    }
    
    if(txt.length<=3) return;
    var site_code=document.getElementById('site_code').value;
    var url="marketplace/src.php?txt="+txt+"&active=1&site_code="+site_code;
    ajaxrequest(url,article_mail_cb);
    
    document.getElementById('article_mail_result').style.display='block';
    document.getElementById('article_mail_result').innerHTML='<img src=\"/images/templates/ajax_wait.gif\">';
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
		var actif=xml.getElementsByTagName('prod')[i].getAttribute('active_base');
		var stock=xml.getElementsByTagName('prod')[i].getAttribute('stock');
		var marque=xml.getElementsByTagName('prod')[i].getAttribute('marque');
		var ref=xml.getElementsByTagName('prod')[i].getAttribute('ref');
		var lot=xml.getElementsByTagName('prod')[i].getAttribute('lot');
		var ventes=xml.getElementsByTagName('prod')[i].getAttribute('ventes');
		var ventes1an=xml.getElementsByTagName('prod')[i].getAttribute('ventes1an');
		var stock='Pas en stock';
		if(xml.getElementsByTagName('prod')[i].getAttribute('stock')=='1') stock='En stock : '+xml.getElementsByTagName('prod')[i].getAttribute('stockqte');
		if(actif==1) html+="<tr id=\"tr_"+artid+"\"><td><img src=\"/configurations/"+myHost(window.location.host)+"/images/produits/br/"+artid+".jpg\" width=\"80\" id=\"articleimg"+artid+"\"></td><td style=\"border-bottom:solid thin #cccccc;\">"+marque+"<br>"+id+"<br>"+ref+"<br>"+vistxt[actif]+"</td><td style=\"border-bottom:solid thin #cccccc;\"><b>"+description+"</b><br>Ventes : "+ventes+"<br>Ventes 1 an : "+ventes1an+"<br>"+stock+"</td><td style=\"border-bottom:solid thin #cccccc;\"><a href='javascript:select_article_mail("+artid+",\""+id+"\",\""+description.replace(/\'/g,"&apos;")+"\")'>Sélectionner</a></td></tr>";
	  }
	  html+='</table>';
      }else
	  html='Pas de résultat';

      document.getElementById('article_mail_result').innerHTML=html;
}

function select_article_mail(artid,artcode,libelle){
      var siteurl=document.getElementById('siteurl').value;
      if(artid!=undefined){
	document.getElementById('article'+articleid).innerHTML="<img src=\"/images/produits/br/"+artid+".jpg\" width=\"50\"> <img src=\"images/supprimer.gif\" onclick=\"articleid="+articleid+";select_article_mail();\">";
	mail_articles[articleid]=artid;
	document.getElementById('srcarticle'+articleid).value=artcode;
	document.getElementById('article_mail_result').innerHTML="";
      }else{
	document.getElementById('article'+articleid).innerHTML="";
	mail_articles[articleid]=undefined;
	document.getElementById('srcarticle'+articleid).value="";
      }
}

var mail_articles=new Array();

function com_mail_generate(){
      var siteurl=document.getElementById('siteurl').value;
      var langue=document.getElementById('langue').value;
      var site_code=document.getElementById('site_code').value;
      var params="";
      var subject = "";
      
      params+="&pro="+document.getElementById('pro').value;
      
      if(mail_articles[0]!=undefined) params+="&selection="+mail_articles[0];
      
      var param_articles=new Array();
      for(i=1;i<=6;i++)
	 if(Number(mail_articles[i])) param_articles.push(mail_articles[i]);
      if(param_articles.length>0) params+="&artids="+param_articles.join(",");
	
      if(document.getElementById('codepromo_code').value!="") params+="&cadeau="+document.getElementById('codepromo_code').value;
      
      if(document.getElementById('pourquoi').checked) params+="&pourquoi=1";
      
      if(document.getElementById('marques').checked) params+="&marques=1";
      
      params+="&footer="+document.getElementById('footer').value;
      
      var sel_exp=document.getElementById('com_exp');
      var selected=sel_exp.options[sel_exp.selectedIndex];
      var mail_exp=selected.getAttribute('data-mail');
      params+="&username="+sel_exp.value;

      /* Params pour tracking Email*/
      params+="&tracking="+document.querySelector('#suivimaildiv #tracking_id').value;
      params+="&tracking_type="+document.querySelector('#suivimaildiv #tracking_type').value;
      params+="&userid="+document.getElementById('userid_suivi').value;

      if(document.querySelector('#tracking_chk') != undefined && document.querySelector('#tracking_chk').value == 0){
            alert('Veuillez choisir un cadre d\'envoi.');
      }else{
            mail_auto(undefined,undefined);
            change_exp(sel_exp.value,site_code,langue);
            var mailservice=document.querySelector("#com_mail_id option:checked").dataset.service;
            if(mailservice!="" && mailservice!=undefined){
                switch(mailservice){
                    case "commercial":
                    params+="&use_base=1";
                    show_signature(false);
                    break;
                    
                    case "projet":
                    params+="&notemplate=1";
                    show_signature(true);
                    break;
                }
                mail_enreg(mail_exp,document.getElementById('dest').value,undefined,document.getElementById('com_mail_id').value,langue,site_code,params);
            }else{
            	if(document.querySelector('#suivimaildiv #tracking_id').value != 0) subject = "#PJT"+document.querySelector('#suivimaildiv #tracking_id').value+"#";
                mail_auto(mail_exp,document.getElementById('dest').value,undefined,subject);
                show_signature(true);
            }
            
            
            if(document.forms['mail_etat']!=undefined)
                setTimeout(function(){
                    sendform=document.forms['mail_etat'];
                },1000);
      }
      
}

function com_rapide_mail_generate(){
      var params="";
      var siteurl=document.getElementById('siteurl').value;
      var langue=document.getElementById('langue').value;
      var site_code=document.getElementById('site_code').value;
      
      params+="&pro="+document.getElementById('pro').value;
      params+="&footer=complet";
      params+="&artids="+document.getElementsByName("relance_artids")[0].value;
      params+="&marques=1";
     
      
      mail_suivi('suivi_client',userid);
      mail_auto(undefined,undefined);
      show_signature(false);
      mail_enreg(undefined,document.getElementById('dest').value,undefined,document.getElementsByName('email_relance')[0].value,langue,site_code,params);
      if(document.getElementById("etat_rapide").value>=0) setTimeout(function(){ sendform=document.forms['form_relance']; },2000);
}

function cadreenvoicmd(cadre,id){
    document.querySelector('#mail_div #tracking_type').value = cadre; 
	document.querySelector('#mail_div #tracking_id').value = id;
}
function css_btn_tracking(elmt){
	jQuery('span').removeClass('cadreenvoi_select');
	jQuery(elmt).addClass('cadreenvoi_select')
}
function reset_btn_tracking(id){
	switch(id){
		case 'cd_cli':
		case 'cd_cpt':
        	jQuery('#cd_pjt select').prop('selectedIndex',0);
        	jQuery('#cd_sav select').prop('selectedIndex',0);
        	jQuery('#cd_cde select').prop('selectedIndex',0);
		break;
		case 'cd_pjt':
        	jQuery('#cd_sav select').prop('selectedIndex',0);
        	jQuery('#cd_cde select').prop('selectedIndex',0);
		break;
		case 'cd_sav':
        	jQuery('#cd_pjt select').prop('selectedIndex',0);
        	jQuery('#cd_cde select').prop('selectedIndex',0);
		break;
		case 'cd_cde':
        	jQuery('#cd_sav select').prop('selectedIndex',0);
        	jQuery('#cd_pjt select').prop('selectedIndex',0);
		break;
		case 'chx_cde':
			jQuery('#chx_sav select').prop('selectedIndex',0);
		break;
	}
}

function subject_select(choix){
	var subject = '';
	switch (choix){
		case 'cde':
			subject = '#CDE'+document.getElementsByName('tracking_id')[0].value+'#';
			break;
		case 'sav':
			subject = '#SAV'+document.getElementById('suiviid').value+'-'+document.getElementsByName('tracking_id')[0].value+'#';
			break;	
	}
	document.getElementsByName('mail_subject')[0].value = subject;
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

function mailconfirm(){
    return confirm('Etes-vous sûr de vouloir envoyer à nouveau la confirmation de commande au client ?');
}

function mailfacture(){
    return confirm('Etes-vous sûr de vouloir envoyer à nouveau le mail contenant la facture au client ?');
}

function annulation_impossible(fourncmd,reserve){
    var txt="Il est impossible d'annuler cette commande :\n";
    if(fourncmd>0){
	if(fourncmd==1)
	    txt+="une commande fournisseur n'est pas annulée\n";
	else
	    txt+="des commandes fournisseur ne sont pas annulées\n";
    }
      
    if(reserve>0){
	if(reserve==1)
	   txt+="un article est réservé en stock";
	else
	   txt+="des articles sont reservés en stock";
    }
	
    if(fourncmd+reserve>0){
	alert(txt);
	return false;
    }else
	return true;
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

function newcontremarque(state){
    if(state){
	document.getElementById('addcontremarquelink').style.display="none";
	document.getElementById('hidecontremarquelink').style.display="block";
	document.getElementById('contremarquetbl').style.display="table";
	document.getElementsByName('addcontremarque')[0].value=1;
    }else{
	document.getElementById('addcontremarquelink').style.display="block";
	document.getElementById('hidecontremarquelink').style.display="none";
	document.getElementById('contremarquetbl').style.display="none";
	document.getElementsByName('addcontremarque')[0].value=0;
    }
}

function save_etat(sav_id){
	if(jQuery('#statut_sav')){
		var content = jQuery('#statut_sav'+sav_id).val();
		
		jQuery.ajax({
			method: "POST",
			url: "ajax/majSAV.php",
			data: { sav_id: sav_id, content: content }
		}).success(function() {
			jQuery('#maj_txt'+sav_id).css('display', 'block');
		});
	}
}

function del_sav(sav_id, facture_id){
	if(confirm('Supprimer le SAV n°'+sav_id+' ?')){
		location.href = 'facture.php?action=del_sav&sav_id='+sav_id+'&facturid='+facture_id+'&lst_trietat=';
	}
}

function create_sav(sav_id){
	jQuery.ajax({
		method: "GET",
		url: "ajax/majSAV.php?action=insert&sav="+sav_id,
	})
}

function limit_len(event,input,limit,info){
	var keycode = event.which || event.keyCode;
	var un=1;
	if(keycode==8) un=0;

	var remain=(limit-input.value.length-un);
	document.getElementById(info).innerHTML="Reste "+remain+" caractère"+(remain>1?"s":"");
	document.getElementById(info).style.color='';
	
	if(keycode!=8 && input.value.length+un>=limit){
	    event.preventDefault();
	    document.getElementById(info).style.color='red';
	    return false;
	}
}

function setSavPrio(savid, val){
	jQuery.ajax({
		method: "POST",
		url: "ajax/majSAV.php",
		data: { sav_id: savid, valeur: val }
	}).success(function() {
		jQuery('#maj_txt'+savid).css('display', 'block');
	});
}

function todispo(state,facturid,lignefactureid,artcode,qte,prix,enlevement,livraison,reappro,libelle,artfourn){
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
	if(state){ 
		dispolist[lignefactureid] = "<tr><td>#CDE"+facturid+"#</td><td>"+qte+"</td><td>"+artcode+"</td><td>"+libelle+"</td><td>"+prix+"</td><td>"+reappro+"</td><td>"+enlevementtxt+"</td><td>"+livraisontxt+"</td><td></td></tr>";
		dispofourn[artcode]=artfourn;
	}else{
		dispolist[lignefactureid]=undefined;
		delete dispofourn[artcode];
	}
}

function setAction(action, facturid){
	switch(action){
		case 'demande_dispo':
		maildispo(facturid);
		break;
	}
}

function maildispo(facturid = ''){
	document.getElementById('produits_actions').selectedIndex = 0;
      /*if(dispolist.length==0){
	  alert('Il faut cocher les articles pour lesquels il faut demander la disponibilité');
	  return;
      }*/
      
      var to=undefined;
      
      var activefourn=new Array();
      var nbart=Object.keys(dispofourn).length;
      if(nbart>0){
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
	  
	  var selectfourn=new Array();
	  for(l in activefourn){
	      if(activefourn.hasOwnProperty(l) && activefourn[l]==nbart)
		  selectfourn.push(l);
	  }
	  if(selectfourn.length==0){
	    var close=document.createElement('a');
	    close.onclick=function(){document.getElementById('dispodest').style.display='none';}
	    close.innerHTML='Fermer';
	    close.href="javascript:void(0);";
	    document.getElementById('dispodest').innerHTML='Aucun fournisseur en commun n\'a été trouvé<br>';
	    document.getElementById('dispodest').appendChild(close);
	    document.getElementById('dispodest').style.display='block';
	    return;
	  }
	  if(selectfourn.length==1){
	    to=advfourn[selectfourn[0]];
	  }else{
	    document.getElementById('dispodest').innerHTML='Plusieurs fournisseurs sont disponibles :<br>';
	    var br=document.createElement('br');
	    for(i=0;i<selectfourn.length;i++){
	      var a=document.createElement('a');
	      a.href="javascript:void(0);";
	      a.setAttribute('onclick',"document.getElementsByName('mail_dest')[0].value='"+advfourn[selectfourn[i]]+"';document.getElementById('dispodest').style.display='none';");
	      a.innerHTML=selectfourn[i];
	      document.getElementById('dispodest').appendChild(br);
	      document.getElementById('dispodest').appendChild(a);
	    }
	    document.getElementById('dispodest').style.display='block';
	  }
      }
      
      var form=document.createElement("form");
      form.method="post";
      document.body.appendChild(form);
      
      var msg="<p>Bonjour,</p><p>Pourriez vous nous communiquer la disponibilité pour la(les) référence(s) suivantes, nous confirmer prix d'achat et nous confirmer/indiquer la(les) date(s) d'enlèvement(s) :</p><table border=1 cellspacing=0><tr><td>Commande</td><td>Qté</td><td>Réf. fabricant</td><td>Désignation</td><td>Prix Unit &euro;HT</td><td>Date réappro</td><td>Date enlèvement souhaitée par nos soins</td><td>Date livraison souhaitée par vos soins</td><td>Infos fournisseur</td></tr>";
      for(l in dispolist){
		  if(dispolist[l]!=undefined && !isNaN(l)){
		    msg+=dispolist[l];
            var input=document.createElement("input");
            input.type="hidden";
            input.name="dispo["+l+"]";
            input.value=l;
            form.appendChild(input);
		  }
      }

      msg+="</table><p>Merci par avance de votre réponse, en répondant directement sur ce mail.</p>";
      

      mail_auto(undefined,to,undefined,"Demande de disponibilité #CDE"+facturid+"#",msg);
      if(facturid != '') mail_suivi('suivi_commande',facturid);

      sendform=form;
}

function checkAllDispo(){
	$jQuery('.checkbox_dispo').each(function(){
		$jQuery(this).prop('checked', true);
	});
}

function selectavis(select){
    var list=select.querySelector("div");
    var btn=select.querySelector("span");
    list.style.left=btn.offsetLeft+"px";
    list.style.top=(btn.offsetTop+btn.offsetHeight+2)+"px";
    if(list.style.display=="block")
        list.style.display="none";
    else
        list.style.display="block";
}
  
function envoiavis(code){
    if(code==1){
        if(!confirm("Confirmez-vous l'envoi de l'email d'avis client ?"))
            return;
    }
    var form=document.createElement("form");
    form.method="POST";
    var input=document.createElement("input");
    input.type="hidden";
    input.name="action";
    input.value="avis";
    form.appendChild(input);
    var input=document.createElement("input");
    input.type="hidden";
    input.name="avis";
    input.value=code;
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

function liersav(src){
	if(src.length<=5) return;
	if(confirm('Êtes vous sûr de vouloir lier cette commande a la commande '+src+' ?')){
		document.forms.lierSav.submit();
	}
}

function liersavsrc(src){
	if(src.length<=5) return;
    var url="marketplace/src.php?liersav="+src;
    ajaxrequest(url,liersavsrc_cb);
    document.getElementById('liersavclient').style.display='block';
	document.getElementById('btnliersav').value = src;
}

function liersavsrc_cb(httprequest){
// 	console.log(httprequest);
	var xml=httprequest.responseXML.documentElement;
	var html='';
	if(xml.getElementsByTagName('client')[0]){
		html+='<table width=\"100%\" style=\"border:solid thin #808080;\">';
		html+='<tr><td><b>N° client</b></td><td><b>Nom</b></td><td><b>Prenom</b></td></tr>';
		var userid = xml.getElementsByTagName('client')[0].getAttribute('id');
		var nom = xml.getElementsByTagName('client')[0].getAttribute('nom');
		var prenom = xml.getElementsByTagName('client')[0].getAttribute('prenom');
		html+='<tr><td>'+userid+'</td><td>'+nom+'</td><td>'+prenom+'</td></tr>';
		html+='</table>';
	}else
	html='Pas de résultat';

	document.getElementById('liersavclient').innerHTML=html;

}
function validEtudeRenta(etat){
	document.getElementById('etatRenta').value = etat;
	document.forms.etuderenta.submit();
}
function srcuser(txt){
  if(txt.length<3){
  document.getElementById('user_results').style.display='none';
  return;
  }
  ajaxrequest('mailbox_ajax.php?txt='+txt,user_cb);
}
function user_cb(httpRequest){
	var xml=httpRequest.responseXML.documentElement;
    var html='';
    if(xml.getElementsByTagName('contact')[0]){
		for(i=0;i<xml.getElementsByTagName('contact').length;i++){
	    	var mail=xml.getElementsByTagName('contact')[i].getAttribute('mail');
	      	var nom=xml.getElementsByTagName('contact')[i].getAttribute('nom');
	      	var type=xml.getElementsByTagName('contact')[i].getAttribute('type');
	      	var userid=xml.getElementsByTagName('contact')[i].getAttribute('userid');
	      	var fonction=xml.getElementsByTagName('contact')[i].getAttribute('fonction');
	      	var societe=xml.getElementsByTagName('contact')[i].getAttribute('societe');
	      	var ville=xml.getElementsByTagName('contact')[i].getAttribute('ville');
	      	var cp=xml.getElementsByTagName('contact')[i].getAttribute('codepostal');
	      	if(userid!=undefined) html+="<a href=\"javascript:cadeauuser("+userid+",'"+nom+"')\"><i>n°"+userid+"</i><br>"+societe+" "+nom+"<br>"+cp+" "+ville+"</a><hr>";
	  	}
    }
    document.getElementById('user_results').innerHTML=html;
    document.getElementById('user_results').style.display='block';
}
function cadeauuser(userid,nom){
	document.getElementById('user_results').style.display='none';
	document.getElementById('useridmf_new').value=userid;
	document.forms.form_mf.submit();
}
function updadvcomment2(type,facturid){
    var comment=document.getElementById(type+'advcomment2').value;
    var post = new Array();
    post['advcomment'] = comment;
    post['commenttype'] = type;
    post['action'] = 'updatecomment';
    post['facturid'] = facturid;

    ajaxrequest('adv/adv_ajax.php',updatecommentcb,post);

    //document.location='facture.php?action=editer&facturid='+facturid+'&advcomment='+comment+'&commenttype='+type+'#adv';
   //document.location.href='/admin/facture.php?action=editer&facturid='+facturid+'#adv';
}
function updatecommentcb(httpRequest){
	document.location.href='facture.php?action=editer&facturid='+facturid;
}

lastreq="";
function loadsuivi(userid,req,all,pjt,fourn,destinataire,interne,search){
    if(req==undefined) req=lastreq;
    if(all==undefined) all=0;
    $jq("<img>").attr("src","../images/templates/wait-line.gif").css({"display":"block","margin":"20px auto"}).prependTo('#tab_suivi_client');
    var url='ajax/suivi_client.php?request='+req+'&all='+all;
    if(userid!=undefined) url+='&userid='+userid;
    if(fourn!=undefined){
        if(fourn.length>0){
            fourn.forEach(function(e){
                url+="&fourn[]="+e;
            });
        }else
            url+="&fourn=";
    }
    if(destinataire!=undefined){
        if(destinataire.length>0){
            destinataire.forEach(function(e){
                url+="&destinataire[]="+e;
            });
        }else
            url+="&destinataire=";
    }
    if(interne!=undefined) url+="&interne="+parseInt(interne);
    if(search!=undefined) url+="&search="+encodeURI(search);
    if(pjt!=undefined) url+='&pjt='+pjt;
    $jq.ajax(url,   // request url
    {
        success: function (data, status, xhr) {    // success callback function
            $jq('#tab_suivi_client').html(data);
        }
    });
    lastreq=req;
}

function exp_accord_client(input){
	var val = $jq('input[name=accord_client]:checked').val();
	if(val=='undefined' || val == undefined){
		var val = null;
	}
	var form=document.createElement("form");
    form.method="POST";
    var input=document.createElement("input");
    input.type="hidden";
    input.name="action";
    input.value="exp_accord";
    form.appendChild(input);
    if(cal_timestamp!==null){
		var input=document.createElement("input");
		input.type="hidden";
		input.name="date_accord";
		input.value=cal_timestamp;
		form.appendChild(input);
	}
    var input=document.createElement("input");
    input.type="hidden";
    input.name="accord";
    input.value=val;
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}
