var scrollbottom=false;


function ajaxrequest(url,callback){

        var httpRequest = false;

        if (window.XMLHttpRequest) { // Mozilla, Safari,...
            httpRequest = new XMLHttpRequest();
            if (httpRequest.overrideMimeType) {
                httpRequest.overrideMimeType('text/xml');
                // Voir la note ci-dessous à propos de cette ligne
            }
        }
        else if (window.ActiveXObject) { // IE
            try {
                httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
            }
            catch (e) {
                try {
                    httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
                }
                catch (e) {}
            }
        }

        if (!httpRequest) {
            return false;
        }

        httpRequest.onreadystatechange = function() {

            if (httpRequest.readyState == 4) {
               if (httpRequest.status == 200) {
                  callback(httpRequest);
               }
            }
        };

        httpRequest.open('GET', url, true);
        httpRequest.send(null);
}
function verification(box){
  if(box){
    jQuery('#dmdAvoir').hide();
    jQuery('#assocCmd').hide();
    jQuery('#afsitefacture').prop('required',false);
    jQuery('#afsitefacture').html('');
  }else{
    jQuery('#dmdAvoir').show();
    jQuery('#assocCmd').show();
    jQuery('#afsitefacture').prop('required',true);
  }
}

function veriftotal(){
	ttc=parseFloat(document.getElementsByName('montant_ht')[0].value)*(1+(parseFloat(document.getElementsByName('tva')[0].value)/100));
	ttc=Math.round(ttc*100)/100;
	montant_ttc=parseFloat(document.getElementsByName('montant_ttc')[0].value);
	if(montant_ttc>=ttc-0.01 && montant_ttc<=ttc+0.01) return true
	return false;
}

function addfacture(){
	if(veriftotal())
	    document.forms.ajoutfacture.submit();
	    //alert("ok");
	else
	    alert("Le total T.T.C ne correspond pas au total H.T avec ce taux de T.V.A");
}

function searchprod(facturid,fournisseur){
	if(facturid=="") return;
	document.getElementById('results').innerHTML="<img src=\"../configurations/"+myHost(window.location.host)+"/images/templates/ajax_wait.gif\"> Veuillez patienter";
	ajaxrequest("fournisseur_src.php?src=1&factureid="+facturid+"&fournisseur="+fournisseur,displayresults);
}

function displayresults(httpcallback){
	document.getElementById('results').innerHTML=httpcallback.responseText;
}

function assoc(){
	document.getElementById('assoc').innerHTML="Mise à jour";
        scrollbottom=true;
	for(i=0;i<document.getElementsByName('chkl[]').length;i++){
	    if(document.getElementsByName('chkl[]')[i].checked){
		var ligne=document.getElementsByName('chkl[]')[i].value;
		var url="fournisseur_src.php?addline="+ligne+"&factureid="+factureid+"&sp="+document.getElementsByName('sp')[0].value+"&price="+document.getElementsByName('price_'+ligne)[0].value+"&qte="+document.getElementsByName('qte_'+ligne)[0].value+"&ecopart="+document.getElementsByName('ecopart_'+ligne)[0].value;
		if(document.getElementById("createavoir_"+ligne) && document.getElementById("createavoir_"+ligne).checked)
		      url+="&avoirqte="+document.getElementsByName("avoirqte_"+ligne)[0].value+"&avoirprix="+document.getElementsByName("avoirprix_"+ligne)[0].value+"&avoirtext="+document.getElementsByName("avoirtext_"+ligne)[0].value+"&avoirtype="+document.getElementsByName("avoirtype_"+ligne)[0].value;

		ajaxrequest(url,displaylines);
	    }
	}
	for(i=0;i<document.getElementsByName('chka[]').length;i++){
	    if(document.getElementsByName('chka[]')[i].checked){
		ligne=document.getElementsByName('chka[]')[i].value;
		ajaxrequest("fournisseur_src.php?addautre="+ligne+"&factureid="+factureid+"&facturid="+document.getElementsByName('facturid_'+ligne)[0].value+"&price="+(document.getElementsByName('price_'+ligne)[0].value*document.getElementsByName('qte_'+ligne)[0].value)+"&type="+document.getElementsByName('type_'+ligne)[0].value+"&description="+document.getElementsByName('description_'+ligne)[0].value+"&field="+document.getElementsByName('field_'+ligne)[0].value,displaylines);
	    }
	}
	document.getElementById('results').innerHTML="";

}

function delline(ligne,sp,stock_cmd_id){
    if(!confirm("Etes vous sur de vouloir enlever cette ligne de cette facture ?")) return;
    scrollbottom=false;
    var url="fournisseur_src.php?&factureid="+factureid+"&delline="+ligne+"&sp="+sp;
    if(stock_cmd_id!=undefined) url+="&stock_cmd_id="+stock_cmd_id;
    ajaxrequest(url,displaylines);
    if(sp=="stock") setTimeout("updatestockcmd()",1000);
    avoirlist();
}

function displaylines(httpRequest){
      document.getElementById('assoc').innerHTML=httpRequest.responseText;
      if(scrollbottom){
          var box=document.getElementById("tabs").getBoundingClientRect();
          var bodyRect = document.body.getBoundingClientRect();
            var scroll=Math.round(box.top-bodyRect.top);
          window.scrollTo(0,scroll);
          console.log("scroll to "+scroll);
      }
}

function createline(sitefacturid){
      document.getElementById('results').innerHTML="Veuillez patienter";
      ajaxrequest("fournisseur_src.php?createline="+sitefacturid+"&facturid="+factureid,displayresults);
}

function action(id){
      for(i=1;i<=4;i++){
	  var btn=document.getElementById('action_'+i);
	  btn.className='tab_off';
	  document.getElementById('tra_'+i).style.display='none';
      }
      btn=document.getElementById('action_'+id);
      btn.className='tab_on';
      document.getElementById('tra_'+id).style.display='table-row';
}

function avoirlist(){
      var url="fournisseur_src.php?avoirlist="+fournisseur;
      ajaxrequest(url,function(httpRequest){
	  document.getElementById('selectavoir').innerHTML=httpRequest.responseText;
      });
}

function selectavoir(){
      var select=document.getElementById('selectavoir');
      var option=select.options[select.selectedIndex];
      if(option.value==""){
	  document.getElementById('addavoir').style.display="none";
	  return;
      }
      
      document.getElementById('addavoir').style.display="block";
      document.getElementById('avoir_desc').value=option.dataset.desc;
      var qte=option.dataset.qte;
      document.getElementById('avoir_prix').value=qte*option.dataset.prix;
      document.getElementById('avoir_ecopart').value=qte*option.dataset.ecopart;
      document.getElementById('avoir_facturid').value=option.dataset.facturid;
      document.getElementById('typeavoir').value=option.dataset.type;
      document.getElementsByName('avoirid')[0].value=option.value;

}

function stockligne(checked){
      var facturid=document.getElementsByName('sitefacture')[0];
      if(checked){
	  facturid.readonly=true;
	  facturid.value="STOCK";
      }else{
	  facturid.readonly=false;
	  facturid.value="";
      }
}

function selectstock(cmdid,artid,qte,paht,ecopartht,type,comment){
    var liste=document.getElementById('cmdstock').getElementsByTagName('span');
    for(i=0;i<liste.length;i++) liste[i].className="";
    document.getElementById('selstock'+cmdid+artid).className="selstock";
    document.getElementById('stockaddline').style.display="block";
    document.getElementsByName('paht')[0].value=paht;
    document.getElementsByName('qte')[0].value=qte;
    document.getElementById('spanqte').style.display=(qte==0?"none":"block");
    document.getElementsByName('cmdid')[0].value=cmdid;
    document.getElementsByName('artid')[0].value=artid;
    document.getElementsByName('type')[0].value=type;
    document.getElementById('stecopart').innerHTML=ecopartht==0?"":" + "+ecopartht+" eco part.";
    document.getElementById('stcreateavoir').checked=false;
    document.getElementById('stavoir').style.display='none';
    document.getElementById('stavoirqte').innerHTML="";
    for(i=1;i<=qte;i++){
        var opt=document.createElement("option");
        opt.value=i;
        opt.innerHTML=i;
        if(qte==i) opt.selected=true;
        document.getElementById('stavoirqte').appendChild(opt);
    }
    document.getElementById('stprixavoir').value=0;
    document.getElementById('stavoirtext').value="";
    if(comment!=undefined && comment!="") comment="<span style=\"color:red;\">⚠</span> "+comment;
    document.getElementById('stockcomment').innerHTML=comment;
}

function assocstock(){
      scrollbottom=true;
      var url="fournisseur_src.php?sp=stock&factureid="+factureid+"&cmdid="+document.getElementsByName('cmdid')[0].value+"&addline="+document.getElementsByName('artid')[0].value+"&qte="+document.getElementsByName('qte')[0].value+"&price="+document.getElementsByName('paht')[0].value+"&type="+document.getElementsByName('type')[0].value;
      if(document.getElementById('stcreateavoir').checked)
	  url+=url+="&avoirqte="+document.getElementById('stavoirqte').value+"&avoirprix="+document.getElementById('stprixavoir').value+"&avoirtext="+document.getElementById('stavoirtext').value;
      ajaxrequest(url,displaylines);
      setTimeout("updatestockcmd()",1000);
}

function updatestockcmd(){
      document.getElementById('stockaddline').style.display="none";
      document.getElementById('cmdstock').innerHTML="<img src=\"../configurations/"+myHost(window.location.host)+"/images/templates/ajax_wait.gif\">";
      ajaxrequest("fournisseur_src.php?stockassoc="+fournisseur,function(httpRequest){
	  document.getElementById('cmdstock').innerHTML=httpRequest.responseText;
      });
}

var assoctimeout=false;
function associnfos(facturid){
        clearTimeout(assoctimeout);
      if(facturid.length<3) return false;
      var url="fournisseur_src.php?associnfos="+facturid;
      assoctimeout=setTimeout(function(){
        ajaxrequest(url,function(httpRequest){
            document.getElementById('associnfos').style.display="block";
            document.getElementById('associnfos').innerHTML=httpRequest.responseText;
        });
      },500);
}


