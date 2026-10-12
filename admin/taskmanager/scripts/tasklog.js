var taskId=0;

function srcfournisseur(txt,taskid){
    if(txt.length<3) return;
    document.getElementById('srcfournisseur').style.display='block';
    document.getElementById('srcfournisseur').innerHTML="<img src=\"/configurations/images/templates/ajax_wait.gif\">";
    taskId=taskid;
    ajaxrequest('mailbox_ajax.php?txt='+txt,srcfournisseurcb);
}

function srcfournisseurcb(httpRequest){
    var xml=httpRequest.responseXML.documentElement;

    if(xml.getElementsByTagName("contact")[0]){
	var html="<table>";
	for(i=0;i<xml.getElementsByTagName("contact").length;i++){
	    var contact=xml.getElementsByTagName("contact")[i];
	    var id=contact.getAttribute("id") || contact.getAttribute("userid");
	    var mail=contact.getAttribute("mail");
	    var societe=contact.getAttribute("societe");
	    var nom=contact.getAttribute("nom");
	    var fonction=contact.getAttribute("fonction") || "";
	    var type=contact.getAttribute("type");
	    var telephone=contact.getAttribute("telephone");
	    var portable=contact.getAttribute("portable");
	    var tel=contact.getAttribute("tel");
	    

	    html+="<tr><td><img src=\""+icon(type)+"\" width=\"26\"></td>";
	    html+="<td> "+societe+"<br>"+fonction+"<br><span class=\"nom\">"+nom+"</span></td>";
	    html+="<td><span class=\"suivilink\" onclick=\"suiviauto('tel_out','"+type+"',"+id+",'Appel téléphone "+addslashes(societe)+" ("+addslashes(nom)+")',"+(tel==1?"'"+telephone+"'":undefined)+");\">"+telephone;
	    if(portable!="") html+="<br>"+portable;
	    html+="</span><br><span class=\"suivilink\" onclick=\"suiviauto('tel_in','"+type+"',"+id+",'Appel reçu de "+addslashes(societe)+" ("+addslashes(nom)+")');\">Appel reçu</span> <span class=\"suivilink\" onclick=\"suiviauto('mail_in','"+type+"',"+id+",'Email reçu de "+addslashes(societe)+" ("+addslashes(nom)+")');\">Email reçu</span>";
	    html+="<br><span class=\"suivilink\" onclick=\"mail_auto(undefined,'"+mail+"');mail_suivi('suivi_interne',"+taskId+");srcfournisseur_hide();suivi_hide();\" title=\""+mail+"\">"+mail+"</span></td></tr>";
	}
	html+="</table>";
    }
    document.getElementById('srcfournisseur').innerHTML=html;
}

function addslashes(txt) {
    return (txt + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
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
}

function srcfournisseur_hide(){
    document.getElementById('srcfournisseur').style.display='none';
}

function planningcb(){
	  document.getElementsByName('pour')[0].value=document.getElementById('user').value;
}
