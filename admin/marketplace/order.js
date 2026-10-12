var prices=new Array();
var buyprices=new Array();
var descriptions=new Array();
var barcodes=new Array();
var stocks=new Array();
var strike_out_prices=new Array();
var artids=new Array();
var valid;

function copybilling(){
  if(document.getElementsByName('copy')[0].checked){
    document.getElementsByName('shipping_company')[0].value     = document.getElementsByName('billing_company')[0].value;
    document.getElementsByName('shipping_civi')[0].value        = document.getElementsByName('billing_civi')[0].value;
    document.getElementsByName('shipping_last_name')[0].value   = document.getElementsByName('billing_last_name')[0].value;
    document.getElementsByName('shipping_first_name')[0].value  = document.getElementsByName('billing_first_name')[0].value;
    document.getElementsByName('shipping_address')[0].value     = document.getElementsByName('billing_address')[0].value;
    document.getElementsByName('shipping_zip')[0].value         = document.getElementsByName('billing_zip')[0].value;
    document.getElementsByName('shipping_city')[0].value        = document.getElementsByName('billing_city')[0].value;
    document.getElementsByName('shipping_country')[0].value     = document.getElementsByName('billing_country')[0].value;
    document.getElementsByName('shipping_phone')[0].value       = document.getElementsByName('billing_phone')[0].value;
  }else{
    document.getElementsByName('shipping_company')[0].value     = '';
    document.getElementsByName('shipping_last_name')[0].value   = '';
    document.getElementsByName('shipping_first_name')[0].value  = '';
    document.getElementsByName('shipping_address')[0].value     = '';
    document.getElementsByName('shipping_zip')[0].value         = '';
    document.getElementsByName('shipping_city')[0].value        = '';
    document.getElementsByName('shipping_city')[0].value        = '';
    document.getElementsByName('shipping_phone')[0].value       = '';
    document.getElementsByName('shipping_fax')[0].value         = ''
  }
}

function srcprod(){
  var src=document.getElementById('newsrc').value;
  if(src=='') return;
  var url="src.php?txt="+src+"&active=1&site_code=base";
  ajaxrequest(url,srcCallback);
}

function srcCallback(httprequest){
  var xml=httprequest.responseXML.documentElement;
  var html="";
  var vistxt=new Array();

  vistxt[0]="<br><span style=\"color:red;\">Invisible</span>";
  vistxt[1]="<br>Visible";

  if(xml.getElementsByTagName('prod')[0]){
    html+="<table width='100%' class='tabProduit'><tr>";

    //xml.getElementsByTagName('prod').length -> on affiche seulement 15 résultats maxi
    for(i=0;i<xml.getElementsByTagName('prod').length;i++){
      var description = xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
      var id = xml.getElementsByTagName('prod')[i].getAttribute('id');
      var artid = xml.getElementsByTagName('prod')[i].getAttribute('artid');
      var ref = xml.getElementsByTagName('prod')[i].getAttribute('ref');
      var marq = xml.getElementsByTagName('prod')[i].getAttribute('marque');
      var price = xml.getElementsByTagName('prod')[i].getAttribute('price');
      var tva = xml.getElementsByTagName('prod')[i].getAttribute('tva');
      var strike_out = xml.getElementsByTagName('prod')[i].getAttribute('strike_out');
      var paht = xml.getElementsByTagName('prod')[i].getAttribute('paht');
      var barcode = xml.getElementsByTagName('prod')[i].getAttribute('barcode');
      var stock = xml.getElementsByTagName('prod')[i].getAttribute('stock');
      var active = xml.getElementsByTagName('prod')[i].getAttribute('active');
      var visible = xml.getElementsByTagName('prod')[i].getAttribute('visible');

      html+="<tr><td><img src='/configurations/www.ma-cave-a-vin.fr/images/produits/"+artid+".jpg' width='30'></td><td>"+id+"<br>"+ref+"</td><td>"+marq+"</td><td>"+description+"</td><td>"+price+"€</td><td><a href=\"javascript:selectprod('"+id+"')\"><img src=\"../images/icon/edit_add_22.png\" width=\"20\"></a></td></tr>";

      prices[id]=price;
      strike_out_prices[id]=strike_out;
      buyprices[id]=paht;
      descriptions[id]=description;
      barcodes[id]=barcode;
      stocks[id]=stock;
      artids[id]=artid;
    }
    html+="</table>";
  }else
    html="Pas de résultat";

  document.getElementById('mpprodresults').innerHTML = html;
  document.getElementById('mpprodresults').style.visibility = 'visible';
  document.getElementById('mpprodresults').style.position = 'relative';
}

function selectprod(comp_id){
  document.getElementById('mpprodresults').innerHTML = '';
  document.getElementById('mpprodresults').style.visibility = 'hidden';

  document.getElementById('addprod').style.visibility    = 'visible';
  document.getElementById('addprod').style.position      = 'relative';
  document.getElementsByName('prod')[0].value            = comp_id;
  document.getElementsByName('buyprice')[0].value        = buyprices[comp_id];
  document.getElementsByName('price')[0].value           = prices[comp_id];
  document.getElementsByName('strikeout_price')[0].value = strike_out_prices[comp_id];
  document.getElementsByName('description')[0].value     = descriptions[comp_id];
  document.getElementsByName('artid')[0].value           = artids[comp_id];
		
  var complement_url = '';
  if(document.getElementsByName('raisonsociale')[0]){ 
    var raison_sociale = document.getElementsByName('raisonsociale')[0].value;
    if(raison_sociale != ''){
      complement_url = '&destinataire=2'
    } else {
      complement_url = '&destinataire=1'
    }
  }

  var paysid=parseInt(document.getElementById('paysid').value);
  document.getElementById('divopts').innerHTML="<p align=\"center\"><img src=\"../../configurations/www.ma-cave-a-vin.fr/images/templates/circle_animation.gif\"></p>";
  var url="src.php?artid="+artids[comp_id]+"&paysid="+paysid+complement_url;
  ajaxrequest(url,caractCallback);
}

function caractCallback(httprequest){
  document.getElementById('divopts').innerHTML="";
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
      var newselect = document.createElement('select');
      newselect.name="caract_"+i;
      var newname=document.createElement('input');
      newname.type='hidden';
      newname.name='caractname_'+i;
      newname.value=name;
      for(id in options[name]){
        var opt=new Option(options[name][id],id);
        newselect.options[newselect.length]=opt;
      }

      i++;
      var p=document.createElement('p');
      p.style.margin='0px;'
      var txt=document.createElement('span');
      txt.innerHTML=name+" : ";
      p.appendChild(txt);
      p.appendChild(newname);
      p.appendChild(newselect);
      document.getElementById('divopts').appendChild(p);
    }
  }
               
  if(xml.getElementsByTagName("garantie")[0]){
    for(i=0;i<xml.getElementsByTagName('garantie').length;i++){
      var garantiexml=xml.getElementsByTagName('garantie')[i];
      var utilisation=garantiexml.getAttribute("utilisation");

      if(utilisation==0){
        var p=document.createElement('p');
			  var inp=document.createElement("input");
			  inp.type="hidden";
			  inp.name="gar_fourn";
			  inp.value=garantiexml.getAttribute("garantieid");
			  p.innerHTML="Garantie fournisseur : "+garantiexml.firstChild.nodeValue;
			  p.appendChild(inp);
			  document.getElementById('divopts').appendChild(p);
      }else{
			  var opt=new Option(garantiexml.firstChild.nodeValue,garantiexml.getAttribute("garantieid"));
			  if(selectcomp==undefined){
			      var selectcomp=document.createElement('select');
			      selectcomp.name="garantie";
			      var opt1=new Option("Aucune garantie complémentaire",0);
			      selectcomp.options[selectcomp.length]=opt1;
			      var pcomp=document.createElement("p");
			      pcomp.innerHTML="Garantie complémentaire : ";
			  }

			  selectcomp.options[selectcomp.length]=opt;

      }
    }

    if(selectcomp!=undefined){
      pcomp.appendChild(selectcomp);
      document.getElementById('divopts').appendChild(pcomp);
    }
  }
}

function submitline(){
  document.forms.addline.submit();
}

function sendcmd(){
  document.location='index.php?order='+document.getElementsByName('orderid')[0].value+'&sendcmd=1';
}

function delivered_state(){
  if(confirm("Confirmez-vous que la commande à bien été envoyée au client ?"))
    document.location='index.php?order='+document.getElementsByName('orderid')[0].value+'&state=2';
}

function srccmd(){
  document.location="index.php?src="+document.getElementById('src').value;
}

function verifCdeCallback(httprequest){
  var xml = httprequest.responseXML.documentElement;
  var html = "";
  var value = xml.getElementsByTagName('cde')[0];

  if(value){
    var mp = xml.getElementsByTagName('cde')[0].getAttribute('mp');
    var fac = xml.getElementsByTagName('cde')[0].getAttribute('fac');
    html = "<img src=\"../images/icon/cancel.png\" width=\"12\"> N°" + value.firstChild.nodeValue + " " + mp + " déjà traitée sur la commande " + fac;
    document.getElementById('cdeResult').style.color = "red";
    document.getElementById('pClient').innerHTML = "";
    document.getElementById('tabClient').innerHTML = "";
  }else{
    html = "<img src=\"../images/icon/succes.png\" width=\"12\"> N° de commande disponible";
    document.getElementById('cdeResult').style.color = "green";
  }

  document.getElementById('cdeResult').innerHTML = html;
}

function verifCde(){
  var id = document.getElementsByName('setorderid')[0].value;
  var url ="src.php?cde="+id;
  valide = 0;

  if(!id){
    document.getElementById('cdeResult').innerHTML = "<img src=\"../images/icon/cancel.png\" width=\"12\"> Veuillez renseigner un n° de commande";
    document.getElementById('cdeResult').style.color = "red";
    valide = 1;
  }   
  else
    ajaxrequest(url, verifCdeCallback);
}

function checkCde(name = ''){ 
  verifCde();
  document.getElementById('pClient').style.color = '#f00';
  if(document.getElementsByName('orderdate')[0].value == ''){    
    document.getElementById('pClient').innerHTML = "Veuillez renseigner la date de commande";
  }else if(document.getElementsByName('deliverdate')[0].value == ''){   
    document.getElementById('pClient').innerHTML = "Veuillez renseigner la date de livraison";
  }else if(document.getElementById('orderdate_hidden').value > document.getElementById('deliverdate_hidden').value){
    document.getElementById('pClient').innerHTML = "La date de commande est supérieur à la date de livraison";
}else if (valide == 1){
  
  document.getElementById('pClient').innerHTML = "Veuillez renseigner un n° de commande";
}else{
    document.getElementById('pClient').style.color = 'black';
    document.getElementById('pClient').innerHTML = "Rechercher un client : <input type=\"text\" size=\"14\" name=\"setclient\" value=\"\"><input type=\"button\" value=\"OK\" onclick=\"verifClient();\"> OU <a href=\"javascript:void(0)\" onclick=\"selectClient1('add');\"><img src=\"../images/icon/add_user.png\" width=\"25\">Nouveau client</a>";
  }

if(name != ''){
  document.getElementsByName('setclient')[0].value = name;
  verifClient();
}
  
}

function verifClient(){
  var value = document.getElementsByName('setclient')[0].value;
  var url ="src.php?client="+value;
  ajaxrequest(url, verifClientCallback);
}

function verifClientCallback(httprequest){
  var xml = httprequest.responseXML.documentElement;
  var html = "";

  if(xml.getElementsByTagName('client')[0]){
    html += "<table width='800' align='center' id='listeclients' class='tabClient'>";
    html += "<thead><tr><td>Date Inscription</td><td>N°client</td><td>Client</td><td>Adresse</td><td>Ville</td><td>Téléphone</td><td>Email</td></tr></thead>"
    for(i=0; i<xml.getElementsByTagName('client').length; i++){
      var client  = xml.getElementsByTagName('client')[i].firstChild.nodeValue;
      var id      = xml.getElementsByTagName('client')[i].getAttribute('id');
      var date    = xml.getElementsByTagName('client')[i].getAttribute('date');
      var nom     = xml.getElementsByTagName('client')[i].getAttribute('nom');
      var prenom  = xml.getElementsByTagName('client')[i].getAttribute('prenom');
      var societe = xml.getElementsByTagName('client')[i].getAttribute('societe');
      if(societe!="") societe+="<br>";
      var adresse = xml.getElementsByTagName('client')[i].getAttribute('adresse');
      var cp      = xml.getElementsByTagName('client')[i].getAttribute('cp');
      var ville   = xml.getElementsByTagName('client')[i].getAttribute('ville');
      var pays    = xml.getElementsByTagName('client')[i].getAttribute('pays');
      var tel     = xml.getElementsByTagName('client')[i].getAttribute('tel');
      var mail    = xml.getElementsByTagName('client')[i].getAttribute('mail');
      var civil   = xml.getElementsByTagName('client')[i].getAttribute('civilite');
      var date    = xml.getElementsByTagName('client')[i].getAttribute('inscription');

      html += "<tr><td>"+date+"</td><td>"+id+"</td><td>"+societe+client+"</td><td>"+adresse+"</td><td>"+cp+"<br>"+ville+"</td><td>"+tel+"</td><td>"+mail+"</td><td><a href=\"javascript:selectClient("+id+")\"><img src=\"../images/icon/edit_add_22.png\" width=\"20\"></a></td></tr>";
    }

    html += "</table>";
  }else
    html = "Pas de résultat";

  document.getElementById('clientResults').innerHTML = html;
  var table=document.getElementById("listeclients");
  if(table!=undefined){
    var cols=table.getElementsByTagName("tr")[0].getElementsByTagName("td");
    for(i=0;i<cols.length;i++){
            cols[i].setAttribute("onclick","sortTable(document.getElementById('listeclients'),"+i+")");
    }
    sortTable(table,2);
  }
}

//on a ici des fonctions presque doublons pour afficher soit la personne cherchée dans la barre de recherche soit préremplir les champs pour "automatise" le passage de commande (1)


function selectClient(id){
  document.getElementById('clientResults').innerHTML = ''; 
  ajaxrequest("ajax.php?client="+id, ajaxFormClient_cb);
}

function selectClient1(id){
  document.getElementById('clientResults').innerHTML = ''; 
  ajaxrequest("ajax.php?client="+id, ajaxFormClient_cb1);
}

function ajaxFormClient_cb(httprequest){
document.getElementById('tabClient').innerHTML = httprequest.responseText;
}

function ajaxFormClient_cb1(httprequest){
  var count = 0;
  var form = document.getElementById("formCreate")[0];
  document.getElementById('tabClient').innerHTML = httprequest.responseText;
  if (document.getElementsByName("facturation_societe")[0])
    document.getElementsByName("billing_company")[0].value = document.getElementsByName("facturation_societe")[0].value;
  if (document.getElementsByName("facturation_nom")[0])
    document.getElementsByName("billing_last_name")[0].value = document.getElementsByName("facturation_nom")[0].value;
  if (document.getElementsByName("facturation_prenom")[0])
    document.getElementsByName("billing_first_name")[0].value = document.getElementsByName("facturation_prenom")[0].value;  
  if (document.getElementsByName("facturation_adresse")[0])
    document.getElementsByName("billing_address")[0].value = document.getElementsByName("facturation_adresse")[0].value;
  if(document.getElementsByName("facturation_cp")[0])
    document.getElementsByName("billing_zip")[0].value=document.getElementsByName("facturation_cp")[0].value;
  if (document.getElementsByName("facturation_pays")[0])
  {
      var paysid=57;
      switch(document.getElementsByName("facturation_pays")[0].value.toUpperCase()){
          case "DE":
          case "DEU":
          case "GERMANY":
              paysid=64;
            break;
            
          case "ES":
          case "ESP":
          case "SPAIN":
              paysid=153;
            break;
            
          case "BE":
          case "BEL":
          case "BELGIUM":
              paysid=17;
            break;
            
          case "IT":
          case "ITA":
          case "ITALY":
              paysid=83;
            break;
            
          case "LU":
          case "LUX":
          case "LUXEMBOURG":
              paysid=99;
            break;
      }
      document.getElementsByName("billing_country")[0].value=paysid;
  }
  if (document.getElementsByName("facturation_ville")[0])
    document.getElementsByName("billing_city")[0].value = document.getElementsByName("facturation_ville")[0].value;
  if (document.getElementsByName("facturation_tel")[0])
    document.getElementsByName("billing_phone")[0].value = document.getElementsByName("facturation_tel")[0].value;
  if (document.getElementsByName("livraison_societe")[0])
    document.getElementsByName("shipping_company")[0].value = document.getElementsByName("livraison_societe")[0].value;
  if (document.getElementsByName("livraison_nom")[0])
    document.getElementsByName("shipping_last_name")[0].value = document.getElementsByName("livraison_nom")[0].value;
  if (document.getElementsByName("livraison_prenom")[0])
    document.getElementsByName("shipping_first_name")[0].value = document.getElementsByName("livraison_prenom")[0].value;  
  if (document.getElementsByName("livraison_adresse")[0])
    document.getElementsByName("shipping_address")[0].value = document.getElementsByName("livraison_adresse")[0].value;
  if(document.getElementsByName("livraison_cp")[0])
    document.getElementsByName("shipping_zip")[0].value=document.getElementsByName("livraison_cp")[0].value;
  if (document.getElementsByName("livraison_pays")[0])
  {
      var paysid=57;
      switch(document.getElementsByName("livraison_pays")[0].value.toUpperCase()){
          case "DE":
          case "DEU":
          case "GERMANY":
              paysid=64;
            break;
            
          case "ES":
          case "ESP":
          case "SPAIN":
              paysid=153;
            break;
            
          case "BE":
          case "BEL":
          case "BELGIUM":
              paysid=17;
            break;
            
          case "IT":
          case "ITA":
          case "ITALY":
              paysid=83;
            break;
            
          case "LU":
          case "LUX":
          case "LUXEMBOURG":
              paysid=99;
            break;
      }
      document.getElementsByName("shipping_country")[0].value=paysid;
  }
  if (document.getElementsByName("livraison_ville")[0])
    document.getElementsByName("shipping_city")[0].value = document.getElementsByName("livraison_ville")[0].value;
  if (document.getElementsByName("livraison_tel")[0])
    document.getElementsByName("shipping_phone")[0].value = document.getElementsByName("livraison_tel")[0].value;
  
}

function checkAdr(){
  err = false;
  html = "";

  if(document.getElementsByName('shipping_last_name')[0].value == ''){
    err = true;
    html = "Veuillez renseigner un NOM";
  }
  if(document.getElementsByName('shipping_first_name')[0].value == ''){
    err = true;
    html = "Veuillez renseigner un PRENOM";
  }
  if(document.getElementsByName('shipping_address')[0].value == ''){
    err = true;
    html = "Veuillez renseigner l'ADRESSE";
  }
  if(document.getElementsByName('shipping_zip')[0].value == ''){
    err = true;
    html = "Veuillez renseigner un CODE POSTAL";
  }
  if(document.getElementsByName('shipping_city')[0].value == ''){
    err = true;
    html = "Veuillez renseigner la VILLE";
  }
  if(document.getElementsByName('shipping_phone')[0].value == ''){
    err = true;
    html = "Veuillez renseigner un N° DE TELEPHONE";
  }

  if(!err){
    document.getElementById("formCreate").submit();
  }else{
    document.getElementById("err").innerHTML = html;
    document.getElementById("err").style.color = "red";
  }
}

function sortTable(table,col) {
  var rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
  switching = true;
  //Set the sorting direction to ascending:
  dir = "asc"; 
  /*Make a loop that will continue until
  no switching has been done:*/
  while (switching) {
    //start by saying: no switching is done:
    switching = false;
    rows = table.rows;
    /*Loop through all table rows (except the
    first, which contains table headers):*/
    for (i = 1; i < (rows.length - 1); i++) {
      //start by saying there should be no switching:
      shouldSwitch = false;
      /*Get the two elements you want to compare,
      one from current row and one from the next:*/
      x = rows[i].getElementsByTagName("TD")[col];
      y = rows[i + 1].getElementsByTagName("TD")[col];
      /*check if the two rows should switch place,
      based on the direction, asc or desc:*/
      var xval=x.innerHTML.toLowerCase();
      var yval=y.innerHTML.toLowerCase();
      if(!isNaN(xval)){
        xval=parseInt(x.innerHTML);
        yval=parseInt(y.innerHTML);
      }
      
      if (dir == "asc") {
        if (xval > yval) {
          //if so, mark as a switch and break the loop:
          shouldSwitch= true;
          break;
        }
      } else if (dir == "desc") {
        if (xval < yval) {
          //if so, mark as a switch and break the loop:
          shouldSwitch = true;
          break;
        }
      }
    }
    if (shouldSwitch) {
      /*If a switch has been marked, make the switch
      and mark that a switch has been done:*/
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
      //Each time a switch is done, increase this count by 1:
      switchcount ++;      
    } else {
      /*If no switching has been done AND the direction is "asc",
      set the direction to "desc" and run the while loop again.*/
      if (switchcount == 0 && dir == "asc") {
        dir = "desc";
        switching = true;
      }
    }
  }
  
  var tdcols=table.querySelectorAll("thead td");
  for(i=0;i<tdcols.length;i++){
      if(i==col)
          tdcols[i].className=dir;
      else
            tdcols[i].className="";
  }
}
