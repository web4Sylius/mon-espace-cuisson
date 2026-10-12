var check_delay = 200;
var tab_req     = new Array();
var file        = "articleprix_ajax.php";
var max_req     = 1;
var max_var     = 3;

/*
 * Récupère toutes les options cochées pour un produit
 * afin de les transmettre à un fichier (ajax) pour 
 * recalculer le prix total (en cas d'option payante)
 * 
 * @param {integer} artid : Identifiant d'un produit/pack
 * @returns N/A
 */
function doRequest(artid, packid) {
    /*if(tab_req.length > 0) alert("Pas prêt...");*/

    max_req = 1;

    // Transmission des données
    data = "";
	
    //data += 'qte='+qte+'&';
    data += '?artid='+artid;
    
    //Si l'option concerne un pack, on l'ajoute dans l'url ajax
    if(packid && packid != ''){
        data += '&packid='+packid;
    }
    var regexpr = 'lst_';
    var liste_caractvalid = "0";
    var identifiant = new RegExp(regexpr, "g");
    mesDiv = document.getElementsByTagName("input");
    
    for(var j=0; j<mesDiv.length; j++){
        var libelle = mesDiv[j].id;
        if (libelle.match(identifiant)){
            if (document.getElementById(libelle).checked){
                var caractvalid = document.getElementById(libelle).value;
                liste_caractvalid += ','+caractvalid;
            }
        }
    }	
			
    data += '&liste_caractvalid='+liste_caractvalid;

    var garinput=document.getElementsByTagName('gar');
    if(garinput.length>0){
          for(i=0;i<garinput.length;i++)
               if(garinput.checked) data += "&garantieid="+garinput[i].value;
    }
    //alert(data);
    ajaxrequest(file+data,checkResponse);
}

/*
 * Interprète le résultat du calcul du nouveau prix total d'un produit
 * suite à l'ajout d'options.
 * 
 * @param {string} httprequest : Prix séparés par '|||'
 * @returns N/A
 */
function checkResponse(httprequest) {
     var rep = httprequest.responseText;

     myresultat = rep.split('|||');

     var oldprice = document.getElementById('prix_par_caract').innerHTML;
     oldprice = oldprice.replace(",",".");
     oldprice = parseFloat(oldprice.substring(0,oldprice.length - 1));
     document.getElementById('prix_par_caract').innerHTML=myresultat[1];
     if(document.getElementById('prix_par_caract_new')){
          document.getElementById('prix_par_caract_new').innerHTML=myresultat[1];
     }

     var newprice = document.getElementById('prix_par_caract').innerHTML;
     newprice = newprice.replace(",",".");
     newprice = newprice.replace("¤","");
     newprice = parseFloat(newprice.substring(0,newprice.length - 1));

     if(document.getElementById('prixbarrecaract')){
          var prixbarrecaract = document.getElementById('prixbarrecaract').innerHTML;
          prixbarrecaract = prixbarrecaract.replace(",",".");
          prixbarrecaract = parseFloat(prixbarrecaract.substring(0,prixbarrecaract.length - 1));
          prixbarrecaract = prixbarrecaract+(newprice-oldprice);
          prixbarrecaract = prixbarrecaract.toFixed(2).replace(".",",");
          
          if(document.getElementById('prixbarrecaract')){
               document.getElementById('prixbarrecaract').innerHTML=prixbarrecaract+'¤';
          }
          
          if(document.getElementById('prix_par_caract_promo')){
               $('.strikeout').each(function(){
                    $(this).empty();
                    $(this).html(prixbarrecaract+'¤');
               });
          }         
     }
}

function addToDiv(txt) {
	document.getElementById("dData").innerHTML = txt;
}

function changeSrc(txt) {
	var ibig = document.getElementById("big") ;
	if (ibig)
		ibig.src = txt;
	//document.getElementById("dData").scrollTop = document.getElementById("dData").scrollHeight;
}

function toggleCode(b, d) {
    if(b.value == "montrer") {
        b.value = "masquer";
        document.getElementById(d).style.display = "block";
    }
    else {
        b.value = "montrer";
        document.getElementById(d).style.display = "none";
    }
}
