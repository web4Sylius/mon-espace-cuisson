// JavaScript Document
function submitrappel_immediat(){
    var tel=document.getElementById('rappel_immediat_telephone').value;
    var pays=document.getElementById('rappel_immediat_pays').value;
    var nom=document.getElementById('rappel_immediat_nom').value;
    var cgv =document.getElementById('cgv').checked;
    document.getElementById('rappel_immediat_error').style.display="none";
    if(tel=="" || nom=="" || !cgv){
      document.getElementById('rappel_immediat_error').style.display="block";
      return;
    }

    var post=new Array();
    post['rappelinst_tel']=tel;
    post['rappelinst_nom']=nom;
    post['rappelinst_mail']=document.getElementById('rappel_immediat_mail').value;
    post['rappelinst_pays']=pays;
    post['msg']='Formulaire Rappel immediat sortie site';
    post['url']=document.URL;
    document.getElementById('rappel_immediat_form').innerHTML="<img src=\"/configurations/www.ma-cave-a-vin.fr/images/templates/ajax-loader.gif\" align=\"absmiddle\">";
    document.getElementById('rappel_immediat').getElementsByTagName('a')[2].style.display="none";
    document.getElementById('rappel_immediat').getElementsByTagName('a')[1].style.display="none";
    document.getElementById('rappel_immediat_cgv').style.display="none";

    postcontact(post,rappel_immediat_cb);
}

function rappel_immediat_cb(httprequest){
    document.getElementById('rappel_immediat_form').innerHTML=httprequest.responseText;
    setTimeout("hidebarform('rappel_immediat')",5000);
}


function __doPostBack(eventTarget, eventArgument) {
   if (!theForm.onsubmit || (theForm.onsubmit() != false)) {
       theForm.__EVENTTARGET.value = eventTarget;
       theForm.__EVENTARGUMENT.value = eventArgument;
       theForm.submit();
   }

}

function litnombre(aChaineNombre)
{
	// retire les espaces les points et autres caract?res diff?rents des nombres
	var li=0;
	var lChaine=""+aChaineNombre;
	while (li<eval(lChaine.length))
	{
		if (lChaine.charCodeAt(li)<48 || lChaine.charCodeAt(li)>57)
		{
			lChaine=lChaine.substring(0,li)+lChaine.substring(li+1,lChaine.length);
			li--;
		}
		li++;
	}
	return lChaine;
}
function ouvrircaract(){
	if(document.getElementById("caract_complet2_block")) document.getElementById("caract_complet2_block").style.display='block';
	if(document.getElementById("caract_complet2_none")) document.getElementById("caract_complet2_none").style.display='none';
	if(document.getElementById("caract_complet3_block")) document.getElementById("caract_complet3_block").style.display='block';
	if(document.getElementById("caract_complet3_none")) document.getElementById("caract_complet3_none").style.display='none';
	if(document.getElementById("caract_complet4_block")) document.getElementById("caract_complet4_block").style.display='block';
	if(document.getElementById("caract_complet4_none")) document.getElementById("caract_complet4_none").style.display='none';
	if(document.getElementById("caract_complet5_block")) document.getElementById("caract_complet5_block").style.display='block';
	if(document.getElementById("caract_complet5_none")) document.getElementById("caract_complet5_none").style.display='none';
}

function litnombreCompte(aChaineNombre)
{
	var li=0;
	var lNb;
	var lChaine=""+aChaineNombre;

	while (li<eval(lChaine.length))
	{
		if (lChaine.charCodeAt(li)<48 || lChaine.charCodeAt(li)>57)
		{
			if (lChaine.charCodeAt(li)>=65 || lChaine.charCodeAt(li)<=90)
			{
				lNb=lChaine.charCodeAt(li)-64;
				if (lNb>9)
				{
					lNb=lNb-9;
				}

				if (lNb>9)
				{
					lNb=lNb-8;
				}

				lChar=""+lNb;
				lChaine=lChaine.substring(0,li)+lChar+lChaine.substring(li+1,lChaine.length);
			}
			else
			{
				if (lChaine.charCodeAt(li)>=97 || lChaine.charCodeAt(li)<=122)
				{
					lNb=lChaine.charCodeAt(li)-96;
					if (lNb>9)
					{
						lNb=lNb-9;
					}

					if (lNb>9)
					{
						lNb=lNb-8;
					}
	
					lChar=""+lNb;
					lChaine=lChaine.substring(0,li)+lChar+lChaine.substring(li+1,lChaine.length);
				}
				else
				{	
					// on retire le caract?re ind?sirable
					lChaine=lChaine.substring(0,li)+lChaine.substring(li+1,lChaine.length);
					li--;
				}
			}
		}

		li++;
	}
	return lChaine;
}

function litnombreIBAN(aChaineNombre)
{
	// retire les espaces les points et autres caract?res diff?rents des nombres
	// pour les lettres A ? Z, remplacement par un nombre :
	// A remplac? par 10 ... Z remplac? par 35
	var li=0;
	var lNb;
	var lChaine=""+aChaineNombre;
	var lChaineRes="";

	while (li<eval(lChaine.length))
	{
		if (lChaine.charCodeAt(li)<48 || lChaine.charCodeAt(li)>57)
		{
			if (lChaine.charCodeAt(li)>=65 || lChaine.charCodeAt(li)<=90)
			{
				lNb=lChaine.charCodeAt(li)-55;

				lChar=""+lNb;
				lChaineRes=lChaineRes+lChar;
			}
			else
			{
				if (lChaine.charCodeAt(li)>=97 || lChaine.charCodeAt(li)<=122)
				{
					lNb=lChaine.charCodeAt(li)-87;
					lChar=""+lNb;
					lChaineRes=lChaineRes+lChar;
				}
				else
				{	
					// on retire le caract?re ind?sirable
				}
			}
		}
		else
		{
			lChaineRes=lChaineRes+lChaine.substring(li,li+1);
		}
		li++;
	}
	return lChaineRes;
}

function fCalcRib(aCodeBanque, aCodeGuichet, aNoCompte)
{
	var lCodeBanque, lCodeGuichet, lNoCompte;
	var lint ;
	var lCompteNum;
	var lCompteStr;
	var lA,	lB, lC, lD, lE, lF, lG, lH, lI, lLength, lLoop,	lAsc, lNb;

	// retire les espaces les points et autres caract?res diff?rents des nombres
	lCodeBanque=litnombre(aCodeBanque);
	lCodeGuichet=litnombre(aCodeGuichet);
	lNoCompte=litnombreCompte(aNoCompte);

	if (lCodeBanque.length!=5)
	{
		return 0;
	}

	if (lCodeGuichet.length!=5)
	{
		return 0;
	}

	if (lNoCompte.length>11)
	{
		return 0;
	}

	lA=parseFloat(lCodeBanque);
	lB=parseFloat(lCodeGuichet);
	lC=parseFloat(lNoCompte);

	lD=8*lA;

	lA=lD % 97;

	lE=15*lB;
	lB=97-(lE % 97);
	lF=3*lC;
	lC=97-(lF % 97);
	lG=lA+lB+lC;
	lI=lG % 97;

	if (lI==0)
	{
		return 97;
	}
	else
	{
		return lI;
	}
}

function fCalcIBAN(acodepays, aRIB)
{

	var lcodepays, lRIB, lConcat, lNb, lIBAN, lCodeNum, lCodeStr, li, lRetenue, lCle, lNbInterm, lStrInterm;

	lRIB=litnombreIBAN(aRIB);
	lcodepays=litnombreIBAN(acodepays);

	if ((acodepays.length!=2) || (lcodepays.length!=4))
	{
		return "";
	}

	lConcat=lRIB+lcodepays+"00";
	
	// d?coupage par bloc de 9 chiffres pour calculer le modulo (pb de pr?cision)
	li=0;
	lRetenue="";
	while (li<eval(lConcat.length))
	{
		lStrInterm=lRetenue+lConcat.substring(li, li+9);
		lNbInterm=parseFloat(lStrInterm);
		lCle=lStrInterm % 97;
		lRetenue=""+lCle;
		
		li=li+9;
	}

	lCodeNum=98-(lCle % 97);

	if (lCodeNum<10)
	{
		lCodeStr="0"+lCodeNum;
	}
	else
	{
		lCodeStr=""+lCodeNum;
	}
	
	lIBAN=acodepays+lCodeStr+aRIB;


	return lIBAN;

}

function fCopierRib(aCodeBanque, aCodeGuichet, aNoCompte, aCleRib)
{
	var lCodeBanque, lCodeGuichet, lNoCompte, lCleRib, lCodeStr, lRib, lNbCleRib;

	lCodeBanque=litnombre(aCodeBanque);
	lCodeGuichet=litnombre(aCodeGuichet);
	lNoCompte=aNoCompte;
	lCleRib=litnombre(aCleRib);


	if (lCodeBanque.length!=5)
	{
		return 0;
	}

	if (lCodeGuichet.length!=5)
	{
		return 0;
	}

	if (lNoCompte.length!=11)
	{
		return 0;
	}
	
        if ((lCleRib.length>2)||(lCleRib.length==0))
	{
		return 0;
	}

	lNbCleRib=parseFloat(lCleRib) % 100;

        if ((lNbCleRib<1) || (lNbCleRib>97))
	{
		return 0;
	}

	if (lCleRib<10)
	{
		lCodeStr="0"+lNbCleRib;
	}
	else
	{
		lCodeStr=""+lNbCleRib;
	}

	lRib=lCodeBanque+lCodeGuichet+lNoCompte+lCodeStr;

	return lRib;
}

function cliqueaffiliation()
	{
	document.rib.clerib.value=fCalcRib(document.rib.codebanque.value, document.rib.codeguichet.value, document.rib.nocompte.value) ;
	document.rib.submit() ;
	}

function arrondir(nombre, decimales) // affiliation
	{
	  if (isNaN(nombre)) return "";
	  if (decimales == 0) 
		{
		 var n = Math.round(nombre);
		 return n.toString();
	  }
  else if (decimales > 0) 
	{
	 if (document.all) 
		var undefined; // Netscape 4 ne conna?t pas la constante undefined
	 var p10 = Math.pow(10, decimales);
	 var n = Math.round(nombre * p10);
	 n = n.toString();
	 var point = n.length - decimales;
	 if (point >= 0) 
		n = n.substring(0, point) + "." + n.substring(point);
	 else 
		n = "0." + formater(n, decimales);
	  }
  else 
	{
	 var p10 = Math.pow(10, -decimales);
	 var n = Math.round(nombre / p10) * p10;
	 n = n.toString();
	  }
  if (nombre.signe_decimal == undefined) 
	return n;
  else 
	return n.replace(".", nombre.signe_decimal);
	}	




function recommander(article)
	{
	window.open ('recommander.php?artid='+article+'', 'Recommander', 'Width=600, Height=450, toolbar=no, directories=no, location=no, menubar=no, resizable=no, scrollbars=no, status=no') ;
	}



function agrandir(article, largeur, hauteur)
	{
	var newlargeur = largeur * 3.3 ;
	var newhauteur = hauteur * 2.7 ;
	/*var newlargeur = 700 ;
	var newhauteur = 700 ;*/
	window.open ('photos.php?artid='+article+'', 'Photos', 'Width='+newlargeur+', Height='+newhauteur+', toolbar=no, directories=no, location=no, menubar=no, resizable=no, scrollbars=no, status=no') ;
	}


function recalculer() 
	{
	document.formu.submit();
	}

function etape2() 
	{
	document.formu.action='recapitulatif.html';
	document.formu.submit();
	}


function continuer() 
{
	window.location = 'cde1.php?action=delpanier_complet' ;
}



function ferme(menu) 
	 {
	 document.getElementById(menu).style.display='none';
	 }

function ouvre(afermer, menu) 
	 {
	 if (afermer != '')
		{
		ferme(afermer) ;
		}
	 document.getElementById(menu).style.display='block';
	 }


function voir(article) 
	 {
	 document.getElementById(article).style.display = 'block';
	 }

function cacher(article) 
	 {
	 document.getElementById(article).style.display = 'none';
	 }



function MM_preloadImages() 
		{
		var d=document ;
		if (d.images)
			{
			if (!d.MM_p)
				d.MM_p=new Array();
			var i, j = d.MM_p.length, a = MM_preloadImages.arguments ;
			for (i = 0 ; i < a.length ; i++)
			if (a[i].indexOf("#") != 0)
				{
				d.MM_p[j] = new Image ; 
				d.MM_p[j++].src = a[i] ;
				}
			}	
		}

/*function changeImgAnnonce(srcImg,imagesize0,imagesize1)
	{
	var largeur_en_cours = imagesize0 ;
	var hauteur_en_cours = imagesize1 ;
	if (imagesize0 > 600 || imagesize1 > 600)
		{
		if (largeur_en_cours > 600) // si largeur > 112
			{
			var width = 600 ; // largeur = 112
			var height = Math.floor (600 * hauteur_en_cours / largeur_en_cours) ; // hauteur = 112 * (768/1024)
			largeur_en_cours = width ;
			hauteur_en_cours = height ;
			} 
		if (hauteur_en_cours > 600) 
			{
			var width = Math.floor(largeur_en_cours * 600 / hauteur_en_cours) ;
			var height = 600 ;
			largeur_en_cours = width ;
			hauteur_en_cours = height ;
			}
		} 
	document.getElementById('diapo').innerHTML = '<img src='+srcImg+' width='+largeur_en_cours+' name=big>'; // height='+hauteur_en_cours+'
	}	
*/
function changeImgAnnonce(id,total,suiv){
	/*if (suiv==1){
		document.getElementById(id).style.display = 'none';
		document.getElementById('suivant'+id).style.display = 'none';
		id=id-1;
		document.getElementById(id).style.display = 'block';
		document.getElementById('suivant'+id).style.display = 'block';
	}
	*/	
	if (suiv==2){
		if (id==total){
			document.getElementById(id).style.display = 'none';
			document.getElementById('suivant'+id).style.display = 'none';
			document.getElementById('1').style.display = 'block';
			document.getElementById('suivant1').style.display = 'block';
		}else{
			document.getElementById(id).style.display = 'none';
			document.getElementById('suivant'+id).style.display = 'none';
			/*if (id==1){
				document.getElementById('precedent2').style.display = 'block';
			}else{
				document.getElementById('precedent'+id).style.display = 'none';
			}*/
			id=id+1;
			document.getElementById(id).style.display = 'block';
			document.getElementById('suivant'+id).style.display = 'block';
			//document.getElementById('precedent'+id).style.display = 'block';
		}
	}	
}

function changeImgAnnoncenew(srcImg)
	{
	document.getElementById('diapo').innerHTML = '<img src='+srcImg+' name=big>'; // height='+hauteur_en_cours+'
	}	

function toggle(id,p)
	{
	var myChild = document.getElementById(id);
	if(myChild.style.display!='block')
		{
		myChild.style.display='block';
		document.getElementById(p).className='folderOpen';
		}
	else
		{
		myChild.style.display='none';
		document.getElementById(p).className='folder';
		}
	}

function vider(objet)
	{
	if (objet.value == "recherche" || objet.value == "Adresse mail" || objet.value == "passe" || objet.value == "Votre e-mail" || objet.value == "Mot de passe")
		{
		objet.value = "" ;
		}
	}

function viderpanier()
	{
	window.location = 'cde1.php?action=delpanier_complet' ;
	}
	
function changeImgAnnonce2(srcImg)
	{
	document.getElementById('big').src = srcImg ;
	}	


function clic_onglet(mon_onglet) {
	switch(mon_onglet){
		case'cdc':
			document.getElementById('promo').style.color='#808080';
			document.getElementById('top_vente').style.color='#808080';
			document.getElementById('nouveaute').style.color='#808080';
			document.getElementById('cdc').style.color='#A5CB32';
			document.getElementById('tops_contents').className = 'tops_contents0' ;
			affiche(0);
			break;
		case 'promo':
			document.getElementById('cdc').style.color='#808080';
			document.getElementById('top_vente').style.color='#808080';
			document.getElementById('nouveaute').style.color='#808080';
 			document.getElementById('promo').style.color='#A5CB32';
			document.getElementById('tops_contents').className = 'tops_contents1' ;
			affiche(1);
			break;
		case'top_vente':
			document.getElementById('cdc').style.color='#808080';
			document.getElementById('promo').style.color='#808080';
			document.getElementById('nouveaute').style.color='#808080';
 			document.getElementById('top_vente').style.color='#A5CB32';
			document.getElementById('tops_contents').className = 'tops_contents2' ;
			affiche(2);
			break;
		case'nouveaute':
			document.getElementById('cdc').style.color='#808080';
			document.getElementById('promo').style.color='#808080';
			document.getElementById('top_vente').style.color='#808080';
 			document.getElementById('nouveaute').style.color='#A5CB32';
			document.getElementById('tops_contents').className = 'tops_contents3' ;
			affiche(3);
			break;
		}
	
}

// D?but script pour le menu horizontal d?roulant 
window.onload=montre;
function montre(id) {
	var d = document.getElementById(id);
	var tabdd = document.getElementsByTagName('dd');
	for (var i=0;i<tabdd.length;i++) {
		document.getElementById(tabdd[i].id).style.display='none';
		}
/*


	alert(tabdd);
	for (var i = 1; i<=10; i++) {
		if (document.getElementById('smenu'+i)) {
			document.getElementById('smenu'+i).style.display='none';
			}
		}*/
	if (d) {
		d.style.display='block';
	}
}

function affiche(id) {
	for(i=0;i<4;i++){
		valeur = "calque" + i;
		document.getElementById(valeur).style.display="none";
	}
	valeur = "calque" + id;
	document.getElementById(valeur).style.display="block";
}


	
function poplink(msg)
	{
		//alert(document.body.scrollLeft);
	var content = '<img src="'+msg+'">';
	//if (domok)
	//	{
		document.getElementById("topdecklink").style.left=(MouseX+5)+"px";
		document.getElementById("topdecklink").style.top=(MouseY+5)+"px";
	  	document.getElementById("topdecklink").innerHTML = content;
		document.getElementById("topdecklink").style.display = "block";
  	//	}
	}

function get_mouse(e){
	var x = (navigator.appName.substring(0,3) == "Net") ? e.pageX : event.x+document.documentElement.scrollLeft;
	var y = (navigator.appName.substring(0,3) == "Net") ? e.pageY : event.y+document.documentElement.scrollTop;
	skn.left = x - 15 +"px";
	skn.top = y + 20 +"px";
	
}
function killlink()
	{
	//if (domok)
  		document.getElementById("topdecklink").style.display = "none";
	}

var defile;
var psinit = 50 ;
var pscrnt = psinit ;

/*function texteDefile() { 
	if (!defile) defile = document.getElementById('txt2') ; 
	if (defile) { 
		if (pscrnt < ( - defile.offsetWidth) ) { 
			pscrnt = psinit; 
			} 
		else { 
			pscrnt+= -1;
			} 
		defile.style.left = pscrnt+"px"; 
		} 
	}
setInterval("texteDefile()",10);
*/
function affiche_conseil(div)
	{
	var madiv = document.getElementById(div);
	if(madiv.style.display!='block')
		{
		madiv.style.display='block';
		}
	else
		{
		madiv.style.display='none';
		}
	}

function ferme_caract (num)
	{
	document.getElementById('caract_complet'+num+'_block').style.display='none';	
	document.getElementById('caract_complet'+num+'_none').style.display='block';
	}
function ouvre_caract (num)
	{
	document.getElementById('caract_complet'+num+'_none').style.display='none';	
	document.getElementById('caract_complet'+num+'_block').style.display='block';
	}
function ajoute1(id) {
	if (!id)
		document.getElementById('qte').value = parseFloat(document.getElementById('qte').value)+1;
	else {
		document.getElementById(id).value = parseFloat(document.getElementById(id).value)+1;
		}
	}
	
function enleve1(id) {
	if (!id) {
		if (parseFloat(document.getElementById('qte').value) > 1)
			document.getElementById('qte').value = parseFloat(document.getElementById('qte').value)-1;
		}
	else {
		if (parseFloat(document.getElementById(id).value) > 1) {
			document.getElementById(id).value = parseFloat(document.getElementById(id).value)-1;
			}
		}
	}
	
function accsrc(marqueid){
	if(marqueid == 0 || marqueid == ""){
		document.getElementsByName('modele_cave_fab')[0].disabled = "disabled";
		document.getElementsByName('modele_cave_aci')[0].disabled = "disabled";
		document.getElementsByName('modele_cave_aci')[0].options[0].innerHTML ='Modèle avec référence MA CAVE A VIN';
		document.getElementsByName('modele_cave_fab')[0].options[0].innerHTML ='Modèle avec référence constructeur';
		document.getElementsByName('modele_cave_fab_xs')[0].disabled = "disabled";
		document.getElementsByName('modele_cave_aci_xs')[0].disabled = "disabled";
		document.getElementsByName('modele_cave_aci_xs')[0].options[0].innerHTML ='Modèle avec référence MA CAVE A VIN';
		document.getElementsByName('modele_cave_fab_xs')[0].options[0].innerHTML ='Modèle avec référence constructeur';
		return;
	} 
	document.getElementsByName('modele_cave_fab')[0].disabled = "";
	document.getElementsByName('modele_cave_aci')[0].disabled = "";
	document.getElementsByName('modele_cave_aci')[0].options[0].innerHTML ='Modèle avec référence MA CAVE A VIN';
	document.getElementsByName('modele_cave_fab')[0].options[0].innerHTML ='Modèle avec référence constructeur';
	document.getElementsByName('modele_cave_fab_xs')[0].disabled = "";
	document.getElementsByName('modele_cave_aci_xs')[0].disabled = "";
	document.getElementsByName('modele_cave_aci_xs')[0].options[0].innerHTML ='Modèle avec référence MA CAVE A VIN';
	document.getElementsByName('modele_cave_fab_xs')[0].options[0].innerHTML ='Modèle avec référence constructeur';
	/*document.getElementById('accsubmit').style.display="none";*/
	ajaxrequest("/accsrc.php?mid="+marqueid,accsrccb);
}
function accsrc_lg(marqueid){
	if(marqueid == 0 || marqueid == ""){
		document.getElementsByName('modele_cave_fab_lg')[0].disabled = "disabled";
		document.getElementsByName('modele_cave_aci_lg')[0].disabled = "disabled";
		document.getElementsByName('modele_cave_aci_lg')[0].options[0].innerHTML ='Modèle avec référence MA CAVE A VIN';
		document.getElementsByName('modele_cave_fab_lg')[0].options[0].innerHTML ='Modèle avec référence constructeur';
		return;
	}
	document.getElementsByName('modele_cave_fab_lg')[0].disabled = "";
	document.getElementsByName('modele_cave_aci_lg')[0].disabled = "";
	document.getElementsByName('modele_cave_aci_lg')[0].options[0].innerHTML ='Modèle avec référence MA CAVE A VIN';
	document.getElementsByName('modele_cave_fab_lg')[0].options[0].innerHTML ='Modèle avec référence constructeur';
	/*document.getElementById('accsubmit').style.display="none";*/
	ajaxrequest("/accsrc.php?mid="+marqueid,accsrccb_lg);
}
function accsrc_sm(marqueid){
	if(marqueid == 0 || marqueid == ""){
		document.getElementsByName('modele_cave_fab_sm')[0].disabled = "disabled";
		document.getElementsByName('modele_cave_aci_sm')[0].disabled = "disabled";
		document.getElementsByName('modele_cave_aci_sm')[0].options[0].innerHTML ='Modèle avec référence MA CAVE A VIN';
		document.getElementsByName('modele_cave_fab_sm')[0].options[0].innerHTML ='Modèle avec référence constructeur';
		return;
	}
	document.getElementsByName('modele_cave_fab_sm')[0].disabled = "";
	document.getElementsByName('modele_cave_aci_sm')[0].disabled = "";
	document.getElementsByName('modele_cave_aci_sm')[0].options[0].innerHTML ='Modèle avec référence MA CAVE A VIN';
	document.getElementsByName('modele_cave_fab_sm')[0].options[0].innerHTML ='Modèle avec référence constructeur';
	/*document.getElementById('accsubmit').style.display="none";*/
	ajaxrequest("/accsrc.php?mid="+marqueid,accsrccb_sm);
}
function accsrc_xs(marqueid){
	if(marqueid == 0 || marqueid == ""){
		document.getElementsByName('modele_cave_fab_xs')[0].disabled = "disabled";
		document.getElementsByName('modele_cave_aci_xs')[0].disabled = "disabled";
		document.getElementsByName('modele_cave_aci_xs')[0].options[0].innerHTML ='Modèle avec référence MA CAVE A VIN';
		document.getElementsByName('modele_cave_fab_xs')[0].options[0].innerHTML ='Modèle avec référence constructeur';
		return;
	}
	document.getElementsByName('modele_cave_fab_xs')[0].disabled = "";
	document.getElementsByName('modele_cave_aci_xs')[0].disabled = "";
	document.getElementsByName('modele_cave_aci_xs')[0].options[0].innerHTML ='Modèle avec référence MA CAVE A VIN';
	document.getElementsByName('modele_cave_fab_xs')[0].options[0].innerHTML ='Modèle avec référence constructeur';
	/*document.getElementById('accsubmit').style.display="none";*/
	ajaxrequest("/accsrc.php?mid="+marqueid,accsrccb_xs);
}

function accsrccb(httprequest){
	var rep = httprequest.responseXML.documentElement;
	var modele_cave_aci=document.getElementsByName('modele_cave_aci')[0];
	var opt0=modele_cave_aci.options[0];
	modele_cave_aci.innerHTML="";
	modele_cave_aci.style.display="block";

	document.getElementsByName('modele_cave_aci')[0].options[0]=opt0;
	if(rep.getElementsByTagName('artcode')[0]){
	  for(i=0;i<rep.getElementsByTagName('artcode').length;i++){
		 var artcode=rep.getElementsByTagName('artcode')[i].firstChild.nodeValue;
		 var url=rep.getElementsByTagName('artcode')[i].getAttribute('url');
		 document.getElementsByName('modele_cave_aci')[0].options[i+1]=new Option(artcode,url);
	  }
	}
	
	var modele_cave_fab=document.getElementsByName('modele_cave_fab')[0];
	var opt0=modele_cave_fab.options[0];
	modele_cave_fab.innerHTML="";
	modele_cave_fab.style.display="block";

	document.getElementsByName('modele_cave_fab')[0].options[0]=opt0;
	if(rep.getElementsByTagName('ref')[0]){
	  for(i=0;i<rep.getElementsByTagName('ref').length;i++){
		 var artcode=rep.getElementsByTagName('ref')[i].firstChild.nodeValue;
		 var url=rep.getElementsByTagName('ref')[i].getAttribute('url');
		 document.getElementsByName('modele_cave_fab')[0].options[i+1]=new Option(artcode,url);
	  }
	}
	//document.getElementById('accsubmit').style.display="block";
}
function accsrccb_lg(httprequest){
	var rep = httprequest.responseXML.documentElement;
	var modele_cave_aci=document.getElementsByName('modele_cave_aci_lg')[0];
	var opt0=modele_cave_aci.options[0];
	modele_cave_aci.innerHTML="";
	modele_cave_aci.style.display="block";

	document.getElementsByName('modele_cave_aci_lg')[0].options[0]=opt0;
	if(rep.getElementsByTagName('artcode')[0]){
	  for(i=0;i<rep.getElementsByTagName('artcode').length;i++){
		 var artcode=rep.getElementsByTagName('artcode')[i].firstChild.nodeValue;
		 var url=rep.getElementsByTagName('artcode')[i].getAttribute('url');
		 document.getElementsByName('modele_cave_aci_lg')[0].options[i+1]=new Option(artcode,url);
	  }
	}
	
	var modele_cave_fab=document.getElementsByName('modele_cave_fab_lg')[0];
	var opt0=modele_cave_fab.options[0];
	modele_cave_fab.innerHTML="";
	modele_cave_fab.style.display="block";

	document.getElementsByName('modele_cave_fab_lg')[0].options[0]=opt0;
	if(rep.getElementsByTagName('ref')[0]){
	  for(i=0;i<rep.getElementsByTagName('ref').length;i++){
		 var artcode=rep.getElementsByTagName('ref')[i].firstChild.nodeValue;
		 var url=rep.getElementsByTagName('ref')[i].getAttribute('url');
		 document.getElementsByName('modele_cave_fab_lg')[0].options[i+1]=new Option(artcode,url);
	  }
	}
	//document.getElementById('accsubmit').style.display="block";
}
function accsrccb_sm(httprequest){
	var rep = httprequest.responseXML.documentElement;
	var modele_cave_aci=document.getElementsByName('modele_cave_aci_sm')[0];
	var opt0=modele_cave_aci.options[0];
	modele_cave_aci.innerHTML="";
	modele_cave_aci.style.display="block";

	document.getElementsByName('modele_cave_aci_sm')[0].options[0]=opt0;
	if(rep.getElementsByTagName('artcode')[0]){
	  for(i=0;i<rep.getElementsByTagName('artcode').length;i++){
		 var artcode=rep.getElementsByTagName('artcode')[i].firstChild.nodeValue;
		 var url=rep.getElementsByTagName('artcode')[i].getAttribute('url');
		 document.getElementsByName('modele_cave_aci_sm')[0].options[i+1]=new Option(artcode,url);
	  }
	}
	
	var modele_cave_fab=document.getElementsByName('modele_cave_fab_sm')[0];
	var opt0=modele_cave_fab.options[0];
	modele_cave_fab.innerHTML="";
	modele_cave_fab.style.display="block";

	document.getElementsByName('modele_cave_fab_sm')[0].options[0]=opt0;
	if(rep.getElementsByTagName('ref')[0]){
	  for(i=0;i<rep.getElementsByTagName('ref').length;i++){
		 var artcode=rep.getElementsByTagName('ref')[i].firstChild.nodeValue;
		 var url=rep.getElementsByTagName('ref')[i].getAttribute('url');
		 document.getElementsByName('modele_cave_fab_sm')[0].options[i+1]=new Option(artcode,url);
	  }
	}
	//document.getElementById('accsubmit').style.display="block";
}
function accsrccb_xs(httprequest){
	var rep = httprequest.responseXML.documentElement;
	var modele_cave_aci=document.getElementsByName('modele_cave_aci_xs')[0];
	var opt0=modele_cave_aci.options[0];
	modele_cave_aci.innerHTML="";
	modele_cave_aci.style.display="block";

	document.getElementsByName('modele_cave_aci_xs')[0].options[0]=opt0;
	if(rep.getElementsByTagName('artcode')[0]){
	  for(i=0;i<rep.getElementsByTagName('artcode').length;i++){
		 var artcode=rep.getElementsByTagName('artcode')[i].firstChild.nodeValue;
		 var url=rep.getElementsByTagName('artcode')[i].getAttribute('url');
		 document.getElementsByName('modele_cave_aci_xs')[0].options[i+1]=new Option(artcode,url);
	  }
	}
	
	var modele_cave_fab=document.getElementsByName('modele_cave_fab_xs')[0];
	var opt0=modele_cave_fab.options[0];
	modele_cave_fab.innerHTML="";
	modele_cave_fab.style.display="block";

	document.getElementsByName('modele_cave_fab_xs')[0].options[0]=opt0;
	if(rep.getElementsByTagName('ref')[0]){
	  for(i=0;i<rep.getElementsByTagName('ref').length;i++){
		 var artcode=rep.getElementsByTagName('ref')[i].firstChild.nodeValue;
		 var url=rep.getElementsByTagName('ref')[i].getAttribute('url');
		 document.getElementsByName('modele_cave_fab_xs')[0].options[i+1]=new Option(artcode,url);
	  }
	}
	//document.getElementById('accsubmit').style.display="block";
}

function accsrc_submit(){
	var modele_cave_aci=document.getElementsByName('modele_cave_aci')[0];
	if(modele_cave_aci.selectedIndex>0){
	    document.location=modele_cave_aci.options[modele_cave_aci.selectedIndex].value;
	    return;
	}
	
	var modele_cave_fab=document.getElementsByName('modele_cave_fab')[0];
	if(modele_cave_fab.selectedIndex>0){
	    document.location=modele_cave_fab.options[modele_cave_fab.selectedIndex].value;
	    return;
	}

	if(document.getElementsByName('marque_clayette')[0].selectedIndex == 0){
		alert(document.getElementById('access_index').dataset.msgbrand);
	} else {
		alert(document.getElementById('access_index').dataset.msgmodel);
	}
}
function accsrc_lg_submit(){
	var modele_cave_aci=document.getElementsByName('modele_cave_aci_lg')[0];
	if(modele_cave_aci.selectedIndex>0){
	    document.location=modele_cave_aci.options[modele_cave_aci.selectedIndex].value;
	    return;
	}
	
	var modele_cave_fab=document.getElementsByName('modele_cave_fab_lg')[0];
	if(modele_cave_fab.selectedIndex>0){
	    document.location=modele_cave_fab.options[modele_cave_fab.selectedIndex].value;
	    return;
	}

	if(document.getElementsByName('marque_clayette_lg')[0].selectedIndex == 0){
		alert(document.getElementById('access_index_lg').dataset.msgbrand);
	} else {
		alert(document.getElementById('access_index_lg').dataset.msgmodel);
	}
}
function accsrc_sm_submit(){
	var modele_cave_aci=document.getElementsByName('modele_cave_aci_sm')[0];
	if(modele_cave_aci.selectedIndex>0){
	    document.location=modele_cave_aci.options[modele_cave_aci.selectedIndex].value;
	    return;
	}
	
	var modele_cave_fab=document.getElementsByName('modele_cave_fab_sm')[0];
	if(modele_cave_fab.selectedIndex>0){
	    document.location=modele_cave_fab.options[modele_cave_fab.selectedIndex].value;
	    return;
	}

	if(document.getElementsByName('marque_clayette_sm')[0].selectedIndex == 0){
		alert(document.getElementById('access_index_sm').dataset.msgbrand);
	} else {
		alert(document.getElementById('access_index_sm').dataset.msgmodel);
	}
}
function accsrc_xs_submit(){
	var modele_cave_aci=document.getElementsByName('modele_cave_aci_xs')[0];
	if(modele_cave_aci.selectedIndex>0){
	    document.location=modele_cave_aci.options[modele_cave_aci.selectedIndex].value;
	    return;
	}
	
	var modele_cave_fab=document.getElementsByName('modele_cave_fab_xs')[0];
	if(modele_cave_fab.selectedIndex>0){
	    document.location=modele_cave_fab.options[modele_cave_fab.selectedIndex].value;
	    return;
	}

	if(document.getElementsByName('marque_clayette_xs')[0].selectedIndex == 0){
		alert(document.getElementById('access_index_xs').dataset.msgbrand);
	} else {
		alert(document.getElementById('access_index_xs').dataset.msgmodel);
	}
}

function popdevis(id){
        var page='maildevis.php?id='+id;
    
        var nbpaiements=document.getElementsByName("nbpaiements");
        if(nbpaiements.length>0 && nbpaiements[0].value>0){ 
                var paiements=[]
                var nb=parseInt(nbpaiements[0].value);
                for(i=0;i<nb;i++) {
                        var paiement={};
                        paiement.value=document.getElementsByName('paiement['+i+']')[0].value;
                        paiement.moyen=document.getElementsByName('moyens['+i+']')[0].value;
                        paiement.delais=document.getElementsByName('delais['+i+']')[0].value;
                        paiement.date=document.getElementsByName('date['+i+']')[0].value;
                        paiements.push(paiement);
                }
                page+="&paiements="+JSON.stringify(paiements);
        }

        var width=200;
        var height=100;

        var top=(screen.height-height)/2;
        var left=(screen.width-width)/2;
        window.open(page,"","toolbar=no, location=no, directories=no, status=no, scrollbars=yes, resizable=no, copyhistory=yes, width="+width+", height="+height+", left="+left+", top="+top);
}

function devisajax(id){
      var page='maildevis.php?id='+id;
      ajaxrequest(page,function(httprequest){
	  if(document.getElementById('devismsg')){
	      var text=httprequest.responseText.match(/<p\b[^>]*>([\s\S]+)<\/p>/i)[1];
	      document.getElementById('devismsg').innerHTML=text;
	  }
      });
}

function waitsrc(){
      if(document.getElementsByName('catid')[0].value==""){
	  alert("Vous devez choisir l'usage de votre cave");
	  return;
      }
      document.getElementById('rech_btn').className="wait_src";
      document.getElementById('rech_btn').innerHTML="<img src=\"configurations/www.ma-cave-a-vin.fr/images/templates/ajax_wait.gif\" width=\"27\">";
      document.getElementById('txtrecherche').innerHTML="Recherche en cours";
      document.forms.rechercheexpert.submit();
}

function openfooter(){
      var listli=document.getElementById('footerlinks').getElementsByTagName('li');
      for(i=0;i<listli.length;i++)
	if(listli[i].className=='hidden') listli[i].className='';
      document.getElementById('plusfooter').style.display='none';
}

function testmail(tmail){
      var reg = new RegExp('^[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*@[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*[\.]{1}[a-z]{2,6}$', 'i');

	if(reg.test(tmail))
	  return(true);
	else
	  return(false);
	
}

function upddecompte(){
	if(!document.getElementById('vfdecompte')) return;
	
	dn.setTime(dn.getTime()+1000);
	var diff=d.getTime()-dn.getTime();
	if(diff<=1000) location.reload();

	document.getElementById('vfdecompte').innerHTML=conversion_seconde_heure(diff/1000);
	setTimeout('upddecompte()',1000);
}
function conversion_seconde_heure(time) {
	var reste=time;
	var result='';
			
	var nbJours=Math.floor(reste/(3600*24));
	reste -= nbJours*24*3600;
				
	var nbHours=Math.floor(reste/3600);
	reste -= nbHours*3600;
				
	var nbMinutes=Math.floor(reste/60);
	reste -= nbMinutes*60;
 
	var nbSeconds=reste;
 
	if (nbJours>0) result=result+nbJours+dstr.days+' ';
	if (nbHours>0)result=result+nbHours+dstr.hours+' ';
	if (nbMinutes>0) result=result+nbMinutes+dstr.minutes+' ';
	if (nbSeconds>0) result=result+Math.round(nbSeconds)+dstr.seconds+' ';
				
	return result;
}

function nbonly(event,decimal,input) {
	  // Compatibilité IE / Firefox
	  if(!event && window.event) {
	    event=window.event;
	  }

	  if(decimal==undefined) decimal=false;

	  // IE
	  if(event.keyCode>0 && (event.keyCode < 48 || event.keyCode > 57) && event.keyCode!=8 && event.keyCode!=9) {
	      if(!decimal || event.keyCode!=46 || input.value.indexOf('.')>0) {
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

function setCookie(name, value, days) {
    var expires;
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    }
    else {
        expires = "";
    }
    document.cookie = name + "=" + value + expires + "; path=/";
    return true;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length,c.length);
        }
    }
    return "";
} 

function movefsmsg(){
  var height=document.getElementById("fsmsg").offsetHeight;//hauteur de l'?l?ment ? positionner
  var width=document.getElementById("fsmsg").offsetWidth;//largeur de l'?l?ment ? positionner
 
  var pHeight=window.innerHeight;
  var pWidth=window.innerWidth;
  var sTop=document.documentElement.scrollTop;

  var posY=(pHeight/2)-(height/2)+sTop;//Calcul de la position en Y
  var posX=(pWidth/2)-(width/2);//Calcul de la position en X
  document.getElementById("fsmsg").style.top=posY+"px";
  document.getElementById("fsmsg").style.left=posX+"px";
}

function window_size(){
    var size=new Array();
      
    // the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight
      
    if (typeof window.innerWidth != 'undefined')
    {
	  size['width'] = window.innerWidth,
	  size['height'] = window.innerHeight
    }
      
    // IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)
    
    else if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth !='undefined' && document.documentElement.clientWidth != 0)
    {
	  size['width'] = document.documentElement.clientWidth,
	  size['height'] = document.documentElement.clientHeight
    }
      
    // older versions of IE
      
    else
    {
	  size['width'] = document.getElementsByTagName('body')[0].clientWidth,
	  size['height'] = document.getElementsByTagName('body')[0].clientHeight
    }
    
    return size;
}

var timerpc=new Array();
function popup_header(id,state){
    if(state){
	hideClass('sous-menu');
	hideClass('sous-sous-menu');
	clearTimeout(timerpc[id]);
	$('.'+id).removeClass("hidden");
	timerpc[id]=undefined;
	for(h in timerpc) if(h!=id) $('.'+h).addClass("hidden");
    }else{
	timerpc[id]=setTimeout(function(){
	    $('.'+id).addClass("hidden");
	},500);
    }
}

function reloadcache(){
    window.onpageshow = function(evt) {
	if (evt.persisted) {
	    document.body.style.display = "none";
	    location.reload();
	}
    };
}

function htmlalert(txt){
    var cont=document.createElement("div");
    cont.id=Date.now();
    cont.className="wait";
    var bg=document.createElement("div");
    bg.className="bg";
    var msg=document.createElement("div");
    msg.className="msg";
    var btn=document.createElement("div");
    btn.className="ok";
    btn.innerHTML="OK";
    btn.onclick=function(){
	removealert(cont.id);
    };
    msg.innerHTML=txt;
    cont.appendChild(bg);
    msg.appendChild(btn);
    cont.appendChild(msg);
    document.body.appendChild(cont);
}

function removealert(id){
    var cont=document.getElementById(id);
    if(cont) cont.parentNode.removeChild(cont);
}

function cookie_infos(){
    setCookie("cookieinfo",1,365);
    document.getElementById("barrecookies").className="hidden";
    document.getElementById("return-to-top").style.bottom ="47px";
}
$(function() {
	var cookieinfo = getCookie("cookieinfo");
	if(cookieinfo == 1){
		$("#barrecookies").hide();
	}
});

function isMobile(){
    return (/iphone|ipad|ipod|android|iemobile|windows phone|blackberry|BB10|opera mini|sailfish/i).test(navigator.userAgent.toLowerCase()); // you can add more mobile devices here
}

function mobile_cookie(value){
    value=parseInt(value);
    setCookie("mobile",value,30);
}

function comptePopupEmailCheck(lang) {
	var email = document.getElementById('popup_email').value;
	$.get( "/ajax/panier/checkEmail.php", { email: email }, function( data ) {
	  if(data !== 'no') {
	  	if(lang === 'en')
			document.querySelector('#popup_register_error').innerHTML = 'Your e-mail is already associated with an account';
	  	else
			document.querySelector('#popup_register_error').innerHTML = 'Cet e-mail est déjà associé à un compte';
	  	document.getElementById('popup_email_log').value = email;
	  } else {
	  	document.forms.popupregister.submit();
	  }
	});
}
function affichageFirst(idLigne){
	$('#acces_panier_title_'+idLigne).show();
	$('#scroll_accessoires_'+idLigne).show();
	$('#scrollbtnsparent_'+idLigne).show();
	$('#text_vignscroll_'+idLigne).css('display','table');
	$('#chev_left_'+idLigne).removeClass('fa-chevron-down').addClass('fa-chevron-up');
	$('#chev_right_'+idLigne).removeClass('fa-chevron-down').addClass('fa-chevron-up');

}
function next_part(){
	$('#register_part_pro').show();
	$('#register_next_btn1').hide();

}
function nextpro(){
	registerResetPart();
	$('#register_part_pro p.pro').addClass('btn_pro_hover');
	$('#register_part_pro p.part').removeClass('btn_part_hover');
	$('#register_coordonnees_part').hide();
	$('#register_conseils').hide();
	$('#register_coordonnees_pro').show();
	$('#register_newsletter').show();
	$('#register_btn_valide').css('display','block');
	$('span.mentions_pro').css('visibility','visible');
	$('#propart').val(2) ;
}
function nextpart(){
	registerResetPro();

	$('#register_part_pro p.part').addClass('btn_part_hover');
	$('#register_part_pro p.pro').removeClass('btn_pro_hover');
	$('#register_coordonnees_pro').hide();
	$('#register_coordonnees_part').show();
	$('#register_conseils').show();
	$('#register_newsletter').show();
	$('#register_btn_valide').css('display','block');
	$('#propart').val(1) ;
	$('#conseils1').prop('checked',true);
}
function registerResetPro(){
	document.getElementById('civilite1pro').checked = false;
	document.getElementById('civilite2pro').checked = false;
	document.getElementsByName('nompro')[0].value = "";
	document.getElementsByName('prenompro')[0].value = "";
	document.getElementsByName('raisonsociale')[0].value = "";
	document.getElementsByName('tvaintracom')[0].value = "";
	document.getElementsByName('adressepro')[0].value = "";
	document.getElementsByName('codepostalpro')[0].value = "";
	document.getElementsByName('villepro')[0].value = "";
	document.getElementsByName('paysidpro')[0].value = "";
	document.getElementsByName('telephonepro')[0].value = "";
	document.getElementsByName('connupro')[0].value = "";
	$('#paysid').val(57);
}
function registerResetPart(){
	document.getElementById('civilite1').checked = false;
	document.getElementById('civilite2').checked = false;
	document.getElementsByName('nom')[0].value = "";
	document.getElementsByName('prenom')[0].value = "";
	document.getElementsByName('adresse')[0].value = "";
	document.getElementsByName('codepostal')[0].value = "";
	document.getElementsByName('ville')[0].value = "";
	document.getElementsByName('paysid')[0].value = "";
	document.getElementsByName('telephone')[0].value = "";
	document.getElementsByName('connu')[0].value = "";
	$('#paysidpro').val(57);
}
function nextdevispro(){
	$('#register-devis').hide();
	$('.retour-devis p').show();
	$('#register-devis-part').hide();
	$('#register-devis-pro').show();
	$('#propartdevis').val(2);
}
function nextdevispart(){
	$('#register-devis').hide();
	$('.retour-devis p').show();
	$('#register-devis-pro').hide();
	$('#register-devis-part').show();
	$('#propartdevis').val(1);
	$('#conseils1').prop('checked',true);
}
function retourdevis(){
	devisReset();
	$('.retour-devis p').hide();
	$('#register-devis-pro').hide();
	$('#register-devis-part').hide();
	$('#register-devis').show();
	$('#propartdevis').val(0);

}
function devisReset(){
	$('.line input').val('');
	$('.line select').val('');
	$('#conseils1').prop('checked',false);
	$('#conseils0').prop('checked',false);
	$('.checkbox input').prop('checked', false);
}