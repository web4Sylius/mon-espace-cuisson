var editcolor='';
var bodytop='';
var autoclosediv=undefined;
var destfield;
var sendform=false;
var server_pj=new Array();
var exp_list=new Array();
var exp_username;
var pagepj=false;
var mail_lang="FR"
var mail_site="base";

function mail_auto(exp,dest,cci,subject,body,callback,cb_txt,provenance,visiteur=null,relance=null,relance_stock=null){
    //si page visiteur.php on met automatiquement contact en expediteur et le mail type relance visiteur part (tâche 7261)
    if(visiteur!=null){
        document.getElementsByName('mail_expname')[0].value=exp;
        service_select(251,'Relance visiteurs part');
    }
    if(exp!=undefined){
	    document.getElementsByName('mail_exp')[0].value=exp;
    }
    if(dest!=undefined) document.getElementsByName('mail_dest')[0].value=dest;
    if(cci!=undefined) document.getElementsByName('mail_cci')[0].value=cci;
    if(subject!=undefined) document.getElementsByName('mail_subject')[0].value=subject; 
    if(body!=undefined){
      document.getElementsByName('mail_body')[0].value=body;
      getIFrameDocument('wys').body.innerHTML=body;
    }
    if(callback!=undefined) document.getElementById('mail_cb').name=callback;
    if(cb_txt!=undefined) bodytop=cb_txt;
    if(relance!=null) document.getElementById('envoiid').value=relance;
    if(relance_stock!=null) document.getElementById('relance_stock').value='true';
    if(provenance == true){
      document.getElementById('cadreenvoi').style.display="block";
      document.getElementById('check_cadre').value='1';
    }else{
      document.getElementById('cadreenvoi').style.display="none";
      document.getElementById('check_cadre').value='0';
    }
    sendform=false;
    pagepj=false;
    show_mail(true,visiteur);
}

/**
 * Fonction qui lance la génération d'un email en envoyant toutes les informations en ajax à mailbox_ajax.php
 *
 * @param  {string} exp       Email expéditeur
 * @param  {string} dest      Email(s) destinataire(s) (séparateur ;)
 * @param  {string} cci       Email(s) en copie cachée (séparateur ;)
 * @param  {int}    emailid   Identifiant de l'email dans la table "emails_clients"
 * @param  {string} langue    Langue du template (ex : FR / EN / ES...)
 * @param  {string} site_code Origine du template (ex : base / uk...)
 * @param  {string} params    Chaine de paramètres à transmettre à mailbox_ajax.php (séparateur "&" et débute avec "&")
 */
function mail_enreg(exp,dest,cci,emailid,langue,site_code,params)
{
    if(langue != undefined)
    {
        mail_lang = langue;
        signature();
    }

    if(exp != undefined){
        document.getElementsByName('mail_exp')[0].value=exp;
    }

    if(dest != undefined) document.getElementsByName('mail_dest')[0].value=dest;
    if(cci != undefined) document.getElementsByName('mail_cci')[0].value=cci;
    
    filter_exp(site_code,langue);
    sendform = false;
    var urlbase = "";
    if(params != undefined) urlbase = params;

    ajaxrequest("mailbox_ajax.php?emailid=" + emailid + "&dest=" + dest + "&mail_lang=" + mail_lang + "&site_code=" + site_code + urlbase, emailid_cb);
}

function emailid_cb(httpRequest){
//     console.log('httpRequest', httpRequest);
    if(!httpRequest.responseXML && httpRequest.responseText) {
      var parser = new DOMParser();
      var xml = parser.parseFromString(httpRequest.responseText, "application/xml");
    } else {
      var xml=httpRequest.responseXML.documentElement;
    }
    if(xml.getElementsByTagName('email')[0]){
    	var email=xml.getElementsByTagName('email')[0];
    	var subject=email.getAttribute('sujet');
    	var body=email.firstChild.nodeValue;
    	mail_auto(undefined,undefined,undefined,dyn_fields(subject),dyn_fields(body),undefined,undefined,undefined);
    }
}

function mail_services(langue,site_code,services){
    mail_lang=langue;
    mail_site=site_code;
    signature();
    filter_exp(site_code,langue);
    if(services!="")
	ajaxrequest("mailbox_ajax.php?services="+services+"&site_code="+site_code,services_cb);
    else
	document.getElementById('mailservice').style.display="none";
}

function services_cb(httpRequest){
    var xml=httpRequest.responseXML.documentElement;
    var emails=xml.getElementsByTagName('email');
    document.getElementById('mailservicelist').innerHTML="";
    var emlist=new Array();
    for(i=0;i<emails.length;i++){
      if(emlist[emails[i].getAttribute('service')]==undefined) emlist[emails[i].getAttribute('service')]="";
        var a=document.createElement("a");
        a.innerHTML=emails[i].firstChild.nodeValue;
        a.href="javascript:service_select("+emails[i].getAttribute('id')+",\""+emails[i].firstChild.nodeValue+"\");";
	emlist[emails[i].getAttribute('service')]+=a.outerHTML;
    }
    for(s in emlist) if(emlist.hasOwnProperty(s)) document.getElementById('mailservicelist').innerHTML+="<br><br><b>"+s+"</b>"+emlist[s];
    document.getElementById('mailservice').style.display="block";
}
   
function service_select(id,libelle){
  if(id==0){
    document.getElementById('check_cadre').value='0';
    var div=document.getElementById('mailservicelist');
      if(div.style.display=="none")
	      div.style.display="block";
      else
	      div.style.display="none";
      return;
  }
  document.getElementById('mailservicelist').style.display="none";
  document.getElementById('mailserviceselect').value=libelle;
  mail_enreg(undefined,undefined,undefined,id,mail_lang,mail_site);
}

function reply(mailid,type,re){
    if(re==undefined) re=0;
    if(type!="inbox" && type!="outbox") return;
    ajaxrequest("mailbox_ajax.php?mailid="+parseInt(mailid)+"&type=" +type+"&re="+parseInt(re),reply_cb);
}

function reply_cb(httpRequest){
    var xml=httpRequest.responseXML.documentElement;
    if(xml.getElementsByTagName('mail')[0]){
    	var email=xml.getElementsByTagName('mail')[0];
    	var subject=email.getAttribute('psubject')+email.getAttribute('subject');
    	var infos=xml.getElementsByTagName('info')[0].firstChild.nodeValue;
    	var body="<br><p>"+infos+"</p>"+email.firstChild.nodeValue;
      //var typesuivi = xml.getElementsByTagName('typesuivi')[0].firstChild.nodeValue;
      //document.getElementById('suivitype').value = typesuivi;
      //var to=email.getAttribute('from');
    	var to=email.getAttribute('response');
    	
    	var pjlist=xml.getElementsByTagName('pj');
    	for(i=0;i<pjlist.length;i++)
    	  addserverpj(pjlist[i].getAttribute('name'),pjlist[i].getAttribute('path'));
    	
    	mail_auto(undefined,to,undefined,subject,body,undefined,undefined,undefined);
    }
}

function dyn_fields(txt){
    var re=new RegExp("\\[([0-9a-z_-]+)\\]","gi");
    match=txt.replace(re,repl);
    return match;
}

function repl(v) {
  v=v.replace(/[\[ \]]/gi,"");
  if(!document.getElementsByName(v)[0] && !document.getElementById(v)) return "";
  var valeur = '';
  if(document.getElementsByName(v)[0])
  {
      valeur = document.getElementsByName(v)[0].value; 
  }
  else if(document.getElementById(v))
  {
      valeur = document.getElementById(v).value; //Si pas de valeur pour le "name" de l'input, on cherche dans l'"id"
  }
  return valeur; 
}

function show_mail(state,visiteur=null){
    if(state==undefined) state=true;
    if(state){
      sendform=false;
      document.getElementById('mail_div').style.display='block';
      // if(document.body.scrollTop>0 && visiteur === null) document.getElementById('mail_div').style.top=document.body.scrollTop+'px';
      // if(visiteur != null) {
        document.getElementById('mail_div').style.top='150px';
        document.getElementById('mail_div').style.left='400px';
      //}
      if(window.body){
		window.body.onbeforeunload = doBeforeUnload; // IE
      }else{
		window.onbeforeunload = doBeforeUnload; // FX
	  }
    }else{
      document.getElementById('mail_div').style.display='none';
      document.getElementById('mail_icon').innerHTML="<img src=\"/admin/images/editer.gif\"> e mail";
    }
//     document.getElementById('pagepj').style.display="none";

    lockdest(false);
}

function remove_mail(){
    document.getElementById('mail_div').style.display='none';
    document.getElementsByName('mail_dest')[0].value="";
    document.getElementsByName('mail_cci')[0].value="";
    document.getElementsByName('mail_subject')[0].value="";
    document.getElementsByName('mail_body')[0].value=""
    document.getElementById('mailservice').style.display="none";
    document.getElementById('mailserviceselect').value="Choisir";
    document.getElementsByName('mail_subject')[0].value="";
    if(document.getElementsByName('choixsav')[0] != undefined)
      document.getElementsByName('choixsav')[0].selectedIndex="0";
    if(document.getElementById('chx_sav') != null)
      document.getElementById('chx_sav').classList.remove("cadreenvoi_select");
    if(document.getElementById('chx_cde')!=null)
      document.getElementById('chx_cde').classList.remove("cadreenvoi_select");
    if(document.getElementById('chx_pjt') !=null)
      document.getElementById('chx_pjt').classList.remove("cadreenvoi_select");

    document.getElementById('tracking_type').value='';
    document.getElementById('tracking_id').value='';
    document.getElementById('check_cadre').value='0';



    getIFrameDocument('wys').body.innerHTML="";
    if(window.body)
	window.body.onbeforeunload = undefined; // IE
      else
	window.onbeforeunload = undefined; // FX
    document.getElementById('mail_icon').innerHTML="e mail";
    document.getElementById('exp_list_supp').innerHTML="";
    exp_username=document.getElementById('mail_default_user').value;
    sendform=false;
    lockdest(false);
    filter_exp();
    
    if(server_pj.length>0) for(i in server_pj) server_pj=new Array();
    server_pj=new Array();
}

function mail_suivi(name,id){
    document.getElementById('suivitype').value=name;
    document.getElementById('suiviid').value=id;
}

function mail_pagepj(f,args){
    document.getElementById('mail_pagepj').onclick=function(){
	eval(f+"("+args.join(",")+");");
	document.getElementById('mail_div').style.display='none';
    }
    document.getElementById('mail_pagepj').style.display='inline';
}

function mail_addpjs(data){
    for(i=0;i<data.length;i++){
	addserverpj(data[i].name,data[i].path);
    }
    document.getElementById('mail_div').style.display='block';
}

function lockdest(state){
     if(state){
       document.getElementsByName('mail_dest')[0].setAttribute("readonly","readonly");
       document.getElementById('addressbook1').style.display='none';
     }else{
	document.getElementsByName('mail_dest')[0].removeAttribute("readonly");
	document.getElementById('addressbook1').style.display='inline';
     }
}

function doBeforeUnload() {
   var UNLOAD_MSG = "Un email est en cours d'édition";
   if(window.event)
      window.event.returnValue = UNLOAD_MSG; // IE
   else
      return UNLOAD_MSG; // FX
}


function doRichEditCommand(aName, aArg){
  var wys=getIFrameDocument('wys');
  wys.execCommand(aName,false, aArg);
  document.getElementById('wys').contentWindow.focus()
}
function getIFrameDocument(aID){
  // if contentDocument exists, W3C compliant (Mozilla)
  if (document.getElementById(aID).contentDocument){
    return document.getElementById(aID).contentDocument;
  } else {
    // IE
    return document.frames[aID].document;
  }
}


function verifmailform(){
    var wys=getIFrameDocument('wys');
    document.getElementsByName('mail_body')[0].value=wys.body.innerHTML;
    //on récupère l'id du mail
    if(document.getElementById('com_mail_id') != null && document.getElementById('com_mail_id').value != ''){
        document.getElementById('emailid').value = document.getElementById('com_mail_id').value;
    }else if(document.getElementsByName('email_relance')[0] != null && document.getElementsByName('email_relance')[0].value != ''){
        document.getElementById('emailid').value = document.getElementsByName('email_relance')[0].value;
    }

    err="";
    if(document.getElementsByName('mail_exp')[0].value=='') err+="Il faut mettre un expéditeur\n";
    if(document.getElementsByName('mail_dest')[0].value=='') err+="Il faut mettre au moins un destinataire\n";
    if(document.getElementsByName('mail_subject')[0].value=='') err+="Il faut mettre un sujet\n";
    if((document.getElementById('tracking_type').value=='' || document.getElementById('tracking_id').value=='') && document.getElementById('check_cadre').value == '1') err+="Il faut sélectionner un cadre d'envoi \n";
    
    document.getElementsByName('serverpj')[0].value="";
    for(i=0;i<server_pj.length;i++){ 
        if(server_pj[i]!=undefined) document.getElementsByName('serverpj')[0].value+=server_pj[i]['name']+"="+escape(server_pj[i]['url'])+",";
    }
    
    document.getElementById('mail_cb').value=bodytop+document.getElementsByName('mail_body')[0].value;
    
    if(err==""){
        if(window.body)
            window.body.onbeforeunload = undefined; // IE
        else
            window.onbeforeunload = undefined; // FX
            
        if(sendform!=false){
            if(sendform.getAttribute('action')!=null)
                document.forms.mail_form.action=sendform.getAttribute('action');
            
            for(i=0;i<sendform.getElementsByTagName("INPUT").length;i++){
                var oi=sendform.getElementsByTagName("INPUT")[i];
                if(oi.name!=document.getElementById('mail_cb').name){
                    var ni=document.createElement("INPUT");
                    ni.type="hidden";
                    ni.name=oi.name;
                    ni.value=oi.value;
                    if(document.forms.mail_form.elements[ni.name] && ni.name.indexOf("[")==-1) document.forms.mail_form.removeChild(document.forms.mail_form.elements[ni.name]);
                
                    switch(oi.type){
                    case "text":
                    case "hidden":
                    case "radio":
                    case "date":
                        if(ni.name!="") document.forms.mail_form.appendChild(ni);
                    break;
                    case "checkbox":
                        if(ni.name!="" && oi.checked) document.forms.mail_form.appendChild(ni);
                    break;
                    }
                    
                }
            }
            for(i=0;i<sendform.getElementsByTagName("SELECT").length;i++){
                var oi=sendform.getElementsByTagName("SELECT")[i];
                if(oi.name!=document.getElementById('mail_cb').name){
                    var ni=document.createElement("INPUT");
                    ni.type="hidden";
                    ni.name=oi.name;
                    ni.value=oi.value;
                    if(document.forms.mail_form.elements[ni.name]) document.forms.mail_form.removeChild(document.forms.mail_form.elements[ni.name]);
                    if(ni.name!="") document.forms.mail_form.appendChild(ni);
                }
            }
            for(i=0;i<sendform.getElementsByTagName("TEXTAREA").length;i++){
                var oi=sendform.getElementsByTagName("TEXTAREA")[i];
                if(oi.name!=document.getElementById('mail_cb').name){
                var ni=document.createElement("INPUT");
                    ni.type="hidden";
                    ni.name=oi.name;
                    ni.value=oi.value;
                    if(document.forms.mail_form.elements[ni.name]) document.forms.mail_form.removeChild(document.forms.mail_form.elements[ni.name]);
                    if(ni.name!="") document.forms.mail_form.appendChild(ni);
                }
            }
        }
        if(document.getElementById('envoiid').value !== ''){
            if(document.getElementById('relance_stock').value !== ''){
                relance_mail(document.getElementById('envoiid').value,document.getElementById('relance_stock').value);
            }
            else {
                relance_mail(document.getElementById('envoiid').value);
            }
        }
        document.forms.mail_form.submit();
    }else{
        alert("Le message ne peut être envoyé :\n"+err);
        return false;
    }
}

function addserverpj(name,url){
  
//     for(i=0;i<server_pj.length;i++) alert(i);
//       if(server_pj[i]['url']==url) return;
    
    var nbspj=server_pj.length;
    server_pj[nbspj]=new Array();
    server_pj[nbspj]['name']=name;
    server_pj[nbspj]['url']=url;
    
    var div=document.createElement('div');
    div.id="serverpj"+nbspj;
    div.innerHTML="<a href=\"javascript:removespj("+nbspj+")\" title=\"Supprimer\">X</a> "+name;
    document.getElementById('td_pj').appendChild(div);
}

var inputnb=1;
function addpj(input){
    if(input.files[0].size>10485760) {
	alert("Cette pièce jointe est trop grosse, impossible de l'ajouter");
	return;
    }
    input.style.display='none';

    var div=document.createElement('div');
    div.innerHTML="<a href=\"javascript:removepj("+inputnb+")\" title=\"Supprimer\">X</a> "+input.value;
    div.id='pj_'+inputnb;
    document.getElementById('td_pj').appendChild(div);
    
    inputnb++;
    var inputfile=document.createElement('input');
    inputfile.setAttribute('type','file');
    inputfile.setAttribute('name','mail_pj'+inputnb);
    inputfile.setAttribute('onchange','addpj(this)');
    document.getElementById('td_pj').appendChild(inputfile);
    
}

function removepj(id){
    if(!document.getElementById('pj_'+id)) return false;
    document.getElementById('td_pj').removeChild(document.getElementById('pj_'+id));
    document.getElementById('td_pj').removeChild(document.getElementsByName('mail_pj'+id)[0]);
}

function removespj(id){
    server_pj[id]=undefined;
    document.getElementById('td_pj').removeChild(document.getElementById('serverpj'+id));
}

if(navigator.appName.substring(0,3) == "Net")
         document.captureEvents(Event.MOUSEMOVE);
         document.onmousemove = position;

var movemail=false;

function position(e){
  vgMouseX = (navigator.appName.substring(0,3) == "Net") ? e.pageX : event.x+document.body.scrollLeft;
  vgMouseY = (navigator.appName.substring(0,3) == "Net") ? e.pageY : event.y+document.body.scrollTop;

  if(movemail){
    document.getElementById('mail_div').style.top=(vgMouseY-Yoff)+'px';
    document.getElementById('mail_div').style.left=(vgMouseX-Xoff)+'px';
  }
}
function move_mail(state){
    if(state){
	Xoff=vgMouseX-document.getElementById('mail_div').offsetLeft;
	Yoff=vgMouseY-document.getElementById('mail_div').offsetTop;
    }
    movemail=state;
    //document.onselectstart = new Function ("return "+state);
    /*if(window.sidebar){
		document.onmousedown = state;
		document.onclick = !state;
    }*/

}

document.onclick=check;
function check(e){
  if(autoclosediv==undefined) return;
  var target = (e && e.target) || (event && event.srcElement);
  var obj = document.getElementById(autoclosediv);
  
  if(target!=obj && target.parentNode!=obj){
    obj.style.display='none'
    autoclosediv=undefined;
  }
  
} 

function show_contacts(dest,id){
    destfield=dest;
    document.getElementById('mail_contact_'+id).style.display='block';
    setTimeout("autoclosediv='mail_contact_"+id+"';",1000);
}

function show_signature(state){
    if(state){
      document.getElementById('divsignature').style.visibility='visible';
      document.getElementsByName('mail_signatureshow')[0].checked=true;
    }else{
      document.getElementById('divsignature').style.visibility='hidden';
      document.getElementsByName('mail_signatureshow')[0].checked=false;
    }
}
function hide_notif(){
    document.getElementById('mail_notif').style.display='none';
}

function showpicker(edit){
    editcolor=edit;
    document.getElementById('colorpicker').style.display='block';
}

function closepicker(){
    document.getElementById('colorpicker').style.display='none';
}

function createlink(){
    link=prompt('Adresse du lien');
    if(link=="") return;
    //if(link.substring(0,7)!='http://') link='http://'+link;
    doRichEditCommand('createLink',link);
}

function request_contact(txt,id){
    if(txt.length<3) return;
    ajaxrequest('mailbox_ajax.php?txt='+txt+'&id='+id,contact_cb);
}

function contact_cb(httpRequest){
//     document.getElementById('srcresults').innerHTML=httprequest.responseText;
 var xml=httpRequest.responseXML.documentElement;
    var html="";
    var id_src=xml.getElementsByTagName("contact")[0].getAttribute("typesrc");
    var typesrc=xml.getElementsByTagName("type")[0].getAttribute("id");
    html+="<table>";
    if(id_src=='srcfournisseur'){
        html+="<tr class=\"srcth\"><th>Société</th><th>Type</th><th>Nom/prénom</th><th>Fonction</th><th>Mail</th></tr>";
    }
    else{
        html+="<tr class=\"srcth\"><th>Société</th><th>Nom/prénom</th><th>Fonction</th><th>CP/Ville</th><th>Mail</th></tr>";
    }
    if(xml.getElementsByTagName("contact")[0]){
        for(i=0;i<xml.getElementsByTagName("contact").length;i++){
            var type_fourn=xml.getElementsByTagName("contact")[i].getAttribute("type_fourn");
            var mail=xml.getElementsByTagName("contact")[i].getAttribute("mail");
            var nom=xml.getElementsByTagName("contact")[i].getAttribute("nom");
            var type=xml.getElementsByTagName("contact")[i].getAttribute("type");
            var ville=xml.getElementsByTagName("contact")[i].getAttribute("ville");
            var cp=xml.getElementsByTagName("contact")[i].getAttribute("codepostal");
            var fonction=xml.getElementsByTagName("contact")[i].getAttribute("fonction");
            if(fonction==null) fonction="";
            var societe=xml.getElementsByTagName("contact")[i].getAttribute("societe");
            html+="<tr onclick=\"addcontact('"+mail+"','"+nom+"','"+id_src+"');\" title=\""+mail+"\">";
            html+="<td> "+societe+"</td>";
            if(id_src=='srcfournisseur'){
                html+="<td>"+type_fourn+"</td>";
            }
            html+="<td><span class=\"nom\">"+nom+"</span></td>";
            html+="<td>"+fonction+"</td>";
            if(id_src!=='srcfournisseur'){
                html+="<td>"+cp+", "+ville+"</td>";
            }
            html+="<td>"+mail+"</td>";
            html+="</tr>";
        }
    }
     html+="</table>";
    document.getElementById('srcresults_'+typesrc).innerHTML=html;
}

function icon(type){
    switch(type){
      case "macave":
	return "/favicon.ico";
      case "pro":
	return "/admin/images/pro.png";
      case "client":
	return "/admin/images/client.png";
    }
    return "";
}

function addcontact(mail,nom,id){
    var oldvalue=document.getElementsByName(destfield)[0].value;
    if(oldvalue.indexOf(mail)>-1) return;
    if(oldvalue!='') oldvalue+=';';
    
    document.getElementsByName(destfield)[0].value=oldvalue+mail;
    document.getElementById('mail_contact_'+id).style.display='none';
}

function add_exp_list(username,mail,site_code,langue='FR'){
//     exp_list.push(username);
//     document.getElementById('mail_exp_arrow').style.display='inline';
    document.getElementById('exp_list_supp').innerHTML+="<a href=\"javascript:change_exp('"+username+"','"+site_code+"','"+langue+"')\"><img src=\"images/drapeaux/br/"+langue+".gif\"> "+mail+"</a>";
}

function show_exp_list(){
    var display=document.getElementById('exp_list').style.display;
    if(display=="none"){
	/*var default_exp_user=document.getElementById('mail_default_user').value;
	document.getElementById('exp_list').innerHTML="<a href=\"javascript:change_exp('"+default_exp_user+"');\">"+default_exp_user+"</a>";
	for(i=0;i<exp_list.length;i++){
	    document.getElementById('exp_list').innerHTML+="<a href=\"javascript:change_exp('"+exp_list[i]+"');\">"+exp_list[i]+"</a>";
	}*/
	document.getElementById('exp_list').style.display="block";
    }else{
	document.getElementById('exp_list').style.display="none";
    }
}

function change_exp(username,site_code,langue)
{
    exp_username = username;
    mail_lang    = langue;
    mail_site    = site_code;
    
    signature();
    
    document.getElementById('exp_list').style.display = "none";
}

function filter_exp(site_code,langue){
    var exp_list=document.getElementById('exp_list').getElementsByTagName('a');
    var patt=new RegExp("exp_[a-z]+_"+site_code+"_"+langue);
    for(i=0;i<exp_list.length;i++){
	if(patt.test(exp_list[i].id) || site_code==undefined)
	  exp_list[i].style.display="block";
	else
	  exp_list[i].style.display="none";
    }
}

function signature(lang)
{
    if(lang!=undefined) mail_lang = lang;

    var url = "mailbox_ajax.php?signature=1&mail_lang=" + mail_lang + "&site_code=" + mail_site;
    if(exp_username != undefined) url += "&utilisateur=" + exp_username;
    
    ajaxrequest(url,signature_cb);
    //document.getElementsByName('mail_lang')[0].value=mail_lang;
}

function signature_cb(httpRequest)
{
    var xml = httpRequest.responseXML.documentElement;

    document.getElementsByName('mail_signature')[0].value = xml.getElementsByTagName('signature')[0].firstChild.nodeValue;
    document.getElementById('divsignature').innerHTML     = document.getElementsByName('mail_signature')[0].value;
    document.getElementsByName('mail_exp')[0].value       = xml.getElementsByTagName('exp')[0].getAttribute('mail');
    document.getElementsByName('mail_cci')[0].value       = xml.getElementsByTagName('exp')[0].getAttribute('mail');
    document.getElementsByName('mail_expname')[0].value   = xml.getElementsByTagName('exp')[0].firstChild.nodeValue;
}

function relance_mail(envoi_id,stock_cmd=null){
    if(stock_cmd==null){
        var url = "mailbox_ajax.php?relance=" + envoi_id;
    }
    else {
        var url = "mailbox_ajax.php?relance=" + envoi_id + "&stockrelance=true";
    }
ajaxrequest(url);
}