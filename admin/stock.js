  function stock_cmd_open(){
    var table="<table><tr style='font-weight:bold;'><td>Qté</td><td>Réf</td><td>PU HT</td><td>PU eco HT</td><td>Total HT</td></tr>";
    var chk=document.getElementsByName('cmdartid');
    var nb=0;
    for(i=0;i<chk.length;i++){
      if(chk[i].checked){
	table+="<tr><td><input type='text' name='qte_"+chk[i].value+"' value='"+document.getElementsByName('mini_'+chk[i].value)[0].value+"' size='1' onchange='calc_cmd()'></td><td>"+document.getElementById('lib_'+chk[i].value).value+"</td><td><input type='text' name='paht_"+chk[i].value+"' value='"+document.getElementById('paht_'+chk[i].value).value+"' size='7' onchange='calc_cmd()'><input type='hidden' name='ecopartht_"+chk[i].value+"' value='"+document.getElementById('ecopartht_'+chk[i].value).value+"'></td><td>"+document.getElementById('ecopartht_'+chk[i].value).value+"</td><td id='total_"+chk[i].value+"'></td></tr>";
	nb++;
      }
    }
//     table+="<tr><td></td><td colspan='3'>Frais de port</td><td><input type='text' name='fraisport' value='0' size='4' onchange='calc_cmd()'></td></tr>"
    table+="<tr style='font-weight:bold;'><td colspan='4'>Total</td><td id='cmd_total'></td></tr></table>";
    document.getElementById('stock_cmd_details').innerHTML=table;
    if(nb>0){
      calc_cmd();
      document.getElementById('stock_cmd').style.display='block';
      document.getElementById('stock_cmd').style.top=document.body.scrollTop+'px';
    }else
      alert("aucun article n'est sélectionné !");
  }

  function calc_cmd(){
    var total=0;
    var chk=document.getElementsByName('cmdartid');
    for(i=0;i<chk.length;i++){
      if(chk[i].checked){
	var tligne=parseInt(document.getElementsByName('qte_'+chk[i].value)[0].value)*(parseFloat(document.getElementsByName('paht_'+chk[i].value)[0].value)+parseFloat(document.getElementById('ecopartht_'+chk[i].value).value));
	if(isNaN(tligne)) tligne=0;
	total+=tligne;
	document.getElementById('total_'+chk[i].value).innerHTML=Math.round(tligne*100)/100;
      }
    }
//     if(isNaN(parseFloat(document.getElementsByName('fraisport')[0].value))) document.getElementsByName('fraisport')[0].value=0;
//     total+=parseFloat(document.getElementsByName('fraisport')[0].value);
    document.getElementById('cmd_total').innerHTML=Math.round(total*100)/100;
  }

  function createmail(){
    if(document.getElementsByName('lieu')[0].value==''){
	alert('Merci de préciser un lieu de livraison');
	return;
    }
    var html="<p>Bonjour,</p><p>Veuillez trouver une commande N°"+document.getElementsByName('cmdnb')[0].value+" de produits pour notre stock</p>";
    html+="<table border='1' cellspacing='0'><tr><td>Qté</td><td>Ref.</td><td>PU HT</td><td>PU ecopart HT</td><td>Total</td><td>Délai souhaité</td></tr>";
    var chk=document.getElementsByName('cmdartid');
    for(i=0;i<chk.length;i++){
      if(chk[i].checked) html+="<tr><td>"+document.getElementsByName('qte_'+chk[i].value)[0].value+"</td><td>"+document.getElementById('lib_'+chk[i].value).value+"</td><td>"+(Math.round(100*document.getElementsByName('paht_'+chk[i].value)[0].value)/100)+"</td><td>"+(Math.round(100*document.getElementById('ecopartht_'+chk[i].value).value)/100)+"</td><td>"+(Math.round(100*document.getElementById('total_'+chk[i].value).innerHTML)/100)+"</td><td>"+document.getElementById('date_'+chk[i].value).value+"</td></tr>";
    }
    if(parseFloat(document.getElementsByName('fraisport')[0].value)>0) html+="<tr><td></td><td colspan='3'>Frais de port</td><td>"+Math.round(100*document.getElementsByName('fraisport')[0].value)/100+"</td></tr>";
    html+="<tr><td colspan='4'></td><td>"+document.getElementById('cmd_total').innerHTML+"</td></tr></table>";
    html+="<p>Adresse de livraison : <br>"+document.getElementsByName('livraison')[0].value+"</p>"
    html+="<p>Merci de nous confirmer la commande et de nous confirmer le délai de livraison en retour de mail.</p>";

    mail_auto(undefined,document.getElementsByName('mails')[0].value,undefined,"Commande de stock n°"+document.getElementsByName('cmdnb')[0].value,html);
//     addserverpj('Bon.pdf','stock_bl.php?cmdid='+document.getElementsByName('cmdnb')[0].value);
    sendform=document.forms.stock_cmd_form;
    mail_suivi("suivi_stock",document.getElementsByName('cmdnb')[0].value);
  }

  function fourncoord(id){
    document.getElementsByName('livraison')[0].value='';
    ajaxrequest('facture_ajax.php?type=fourn&contactinfo='+id,fourncoord_cb);
  }
  function fourncoord_cb(httprequest){
    var data=httprequest.responseText.split('|');
    document.getElementsByName('livraison')[0].value=data[0]+'<br>'+data[1]+' '+data[2]+'<br>'+data[5]+'<br>'+data[6]+' '+data[7];
  }

  function selectlieu(societe){
      var lieutxt='';
      var lieuid=0;
      if(lieufourn[societe]!=undefined){
	lieutxt=lieufourn[societe].societe+' '+lieufourn[societe].ville;
	lieuid=lieufourn[societe].id;
      }
      document.getElementById('enlevement').innerHTML=lieutxt;
      document.getElementsByName('enlevement')[0].value=lieuid;
  }

  function trsptcmd(cmdid){
      document.getElementById('enlevmenttxt').innerHTML=document.getElementById('enlevement_'+cmdid).value;
      document.getElementById('livraisontxt').innerHTML=document.getElementById('livraison_'+cmdid).value;
      document.getElementsByName('cmdid')[0].value=cmdid;
      document.getElementById('stock_cmd').style.display='block';
      document.getElementById('stock_cmd').style.top=document.body.scrollTop+'px';
  }

  function notrsptcmd(cmdid){
      document.getElementsByName('cmdid')[0].value=cmdid;
      document.getElementsByName('transporteur')[0].value=0;
      document.forms.trsptcmd.submit();
  }

  function createtrsptmail(){
      var cmdid=document.getElementsByName('cmdid')[0].value;
      var html="<p>Bonjour,<br>Veuillez trouver ci-joint une nouvelle commande de transport n°"+cmdid+"</p>";
      html+="<p><b>Lieu d'enlèvement :</b><br>"+document.getElementById('enlevmenttxt').innerHTML+"</p>";
      html+="<p><b>Date d'enlèvement / mise à disposition :</b> "+document.getElementById('indate').value+"</p>";
      html+="<p><b>Date de livraison :</b> "+document.getElementById('outdate').value+"</p>";
      html+="<p><b>Adresse livraison :</b><br>"+document.getElementById('livraisontxt').innerHTML+"</p>";
      html+="<p><b>Tarif :</b> "+document.getElementsByName('tarif')[0].value+" &euro;<br><b>= "+document.getElementsByName('tarif')[0].value+" &euro; HT (hors taxe gas oil)</b></p>";
      html+="<p>Nous vous remercions de votre envoi, par mail ou par fax, de votre confirmation de commande</p>";

      mail_auto(undefined,document.getElementsByName('mails')[0].value,undefined,"Commande n°"+cmdid,html);
      addserverpj("Bon "+cmdid+".pdf","http://"+servername+"/admin/stock_bl.php?cmdid="+cmdid);
      sendform=document.forms.trsptcmd;
      mail_suivi("suivi_stock",cmdid);
  }
  
  function setqte(qte){
      document.getElementsByName('qte')[0].innerHTML='';
      for(i=1;i<=qte;i++) document.getElementsByName('qte')[0].options[i-1]=new Option(i,i);
  }

  function srcartid(src){
		document.getElementById('modiftitle').innerHTML='';
		document.getElementById('submitartid').value='Ajouter';
               if(src.length<=3) return;
               var url="marketplace/src.php?txt="+src
               ajaxrequest(url,srcCallback);
		document.getElementById('add').style.display='none';
		document.getElementById('liste').style.display='none';
		document.getElementById('results').innerHTML="<img src=\"/configurations/www.ma-cave-a-vin.fr/images/templates/ajax_wait.gif\">";
  }
  
  function srcCallback(httprequest){
               var xml=httprequest.responseXML.documentElement;
               var html='';
	   
	       var vistxt=new Array();
	       vistxt[0]="<br><span style=\"color:red;\">Invisible</span>";
	       vistxt[1]="<br>Visible";
               if(xml.getElementsByTagName('prod')[0]){
                    html+="<table width=\"100%\" style=\"border:solid thin #808080;\" class=\"txt\"><tr>";
                    for(i=0;i<xml.getElementsByTagName('prod').length;i++){
                         var description=xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
                         var id=xml.getElementsByTagName('prod')[i].getAttribute('id');
                         var artid=xml.getElementsByTagName('prod')[i].getAttribute('artid');
			 var actif=xml.getElementsByTagName('prod')[i].getAttribute('active');
			 var stock=xml.getElementsByTagName('prod')[i].getAttribute('stock');
			 var marque=xml.getElementsByTagName('prod')[i].getAttribute('marque');
			 var ref=xml.getElementsByTagName('prod')[i].getAttribute('ref');
			 var lot=xml.getElementsByTagName('prod')[i].getAttribute('lot');
                         if(actif<2 && lot==0) html+="<tr id=\"tr_"+artid+"\"><td><img src=\"/configurations/www.ma-cave-a-vin.fr/images/produits/br/"+artid+".jpg\" width=\"80\"></td><td style=\"border-bottom:solid thin #cccccc;\">"+marque+"<br>"+id+"<br>"+ref+"<br>"+vistxt[actif]+"</td><td style=\"border-bottom:solid thin #cccccc;\">"+description+"<td style=\"border-bottom:solid thin #cccccc;\"><a href=\"javascript:selectprod("+artid+")\">Sélectionner</a></td></tr>";
                    }
                    html+="</table>";
               }else
                   html="Pas de résultat";

               document.getElementById('results').innerHTML=html;
  }

  function selectprod(artid){
      var trs=document.getElementById('results').getElementsByTagName('tr');
      for(i=0;i<trs.length;i++){
	  if(trs[i].id!='tr_'+artid) trs[i].style.display='none';
      }
      document.getElementById('add').style.display='block';
      document.getElementsByName('artid')[0].value=artid;
  }

  function updatemini(artid,lieu){
      document.getElementsByName('lieu')[0].value=lieu;
      document.getElementsByName('artid')[0].value=artid;
      document.getElementsByName('qte')[0].value=parseInt(document.getElementById('qte_'+artid+'_'+lieu).value);
      document.getElementsByName('qtemini')[0].value=parseInt(document.getElementById('alert_'+artid+'_'+lieu).value);
      document.getElementsByName('artids')[0].value=document.getElementById('artids_'+artid+'_'+lieu).value;
      document.getElementsByName('delai')[0].value=document.getElementById('delai_'+artid+'_'+lieu).value;
      document.getElementById('modiftitle').innerHTML=document.getElementById('artname_'+artid+'_'+lieu).innerHTML.replace(/<br>/gi,',');
      document.getElementById('submitartid').value='Modifier';
      document.getElementById('simbtn').innerHTML=document.getElementById('simbtn_'+artid+'_'+lieu).innerHTML;
      document.getElementById('add').style.display='block';
      document.getElementById('add').style.top=document.body.scrollTop;
  }
  
  function delmini(artid,lieu){
      if(confirm("Etes-vous sur de vouloir supprimer la gestion de cet article dans ce lieu de stockage ?")){
	   document.getElementById('delartid').value=artid;
	   document.getElementById('dellieu').value=lieu;
	   document.forms.formdelmini.submit();
      }
  }
	
  function srcsimilaire(txt){
      document.getElementById('similaires').innerHTML='';
      if(txt.length<=3) return;
      ajaxrequest("marketplace/src.php?txt="+txt,srcsimilaire_cb);
  }
  function srcsimilaire_cb(httprequest){
      var xml=httprequest.responseXML.documentElement;
      var html="<img src=\"images/icon-x.svg\" onclick=\"document.getElementById('similaires').style.display='none';\" align=\"right\" width=\"24\">";
      
      var vistxt=new Array();
      vistxt[0]="<br><span style=\"color:red;\">Invisible</span>";
      vistxt[1]="<br>Visible";
      if(xml.getElementsByTagName('prod')[0]){
	  for(i=0;i<xml.getElementsByTagName('prod').length;i++){
		var description=xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
		var id=xml.getElementsByTagName('prod')[i].getAttribute('id');
		var artid=xml.getElementsByTagName('prod')[i].getAttribute('artid');
		var active=xml.getElementsByTagName('prod')[i].getAttribute('active');
		var stock=xml.getElementsByTagName('prod')[i].getAttribute('stock');
		var marque=xml.getElementsByTagName('prod')[i].getAttribute('marque');
		var ref=xml.getElementsByTagName('prod')[i].getAttribute('ref');
		var lot=xml.getElementsByTagName('prod')[i].getAttribute('lot');
		if(active<2 && lot==0){
                    
                    var p=document.createElement("p");
                    var img=document.createElement("img");
                    img.src="/configurations/www.ma-cave-a-vin.fr/images/produits/br/"+artid+".jpg";
                    img.style.width="50px";
                    img.style.verticalAlign="middle";
                    var txt=document.createTextNode(id+" ("+ref.trim()+")");
                    
                    var a=document.createElement("a");
                    a.href="javascript:addsimilaire('"+artid+"','"+id+"');";
                    a.appendChild(img);
                    a.appendChild(txt);
                    p.appendChild(a);
                
                    document.getElementById('similaires').appendChild(p);
                }
	  }
      }else
	  document.getElementById('similaires').innerHTML="Pas de résultat";

      document.getElementById('similaires').style.display='block';
  }

  function addsimilaire(artid,artcode){
    var lieu=document.getElementsByName("lieu")[0].value;
      ajaxrequest("marketplace/src.php?artid="+artid+"&stock=1",function(artrequest){
            var artxml=artrequest.responseXML.documentElement;
            var lieuxml=artxml.querySelector("lieu[id='"+lieu+"']");
            
            if(lieuxml!=null){
                alert("Ce produit est déjà dans ce stock");
                return;
            }
            
            var artids=document.getElementsByName('artids')[0].value.split(',');
            for(i=0;i<artids.length;i++) if(artids[i]==artid) return;
            artids.push(artid);
            document.getElementsByName('artids')[0].value=artids.join(',');
            document.getElementById('simbtn').innerHTML+="<span id=\"sim_"+artid+"\" class=\"simbtn\">"+artcode+" <a href=\"javascript:rmsimilaire("+artid+")\">X</a></span>";
      });
  }
  function rmsimilaire(artid){
      var newartid=new Array();
      var artids=document.getElementsByName('artids')[0].value.split(',');
      for(i=0;i<artids.length;i++) if(artids[i]!=artid) newartid.push(artids[i]);
      document.getElementsByName('artids')[0].value=newartid.join(',');
      var d=document.getElementById('simbtn');
      var rm=document.getElementById('sim_'+artid);
      d.removeChild(rm);
  }

  function details(id){
      if(document.getElementById('cmd'+id).style.display=='none'){
	  document.getElementById('cmd'+id).style.display='table-row';
	  document.getElementById('icon'+id).src='images/moins.gif';
      }else{
	  document.getElementById('cmd'+id).style.display='none';
	  document.getElementById('icon'+id).src='images/plus.gif';
      }
  }

  function showlist(id){
      if(document.getElementById('liste_'+id).style.display=='none'){
	  document.getElementById('liste_'+id).style.display='table';
	  document.getElementById('icon_'+id).src='images/moins.jpg';
      }else{
	  document.getElementById('liste_'+id).style.display='none';
	  document.getElementById('icon_'+id).src='images/plus.jpg';
      }
  }

  function show_hide_column(do_show) {

    var stl = (do_show?'table-cell':'none');
    if(!document.getElementById('stock_table')) return false;
      
    var tbl  = document.getElementById('stock_table');
    var rows = tbl.getElementsByTagName('tr');

    for (var row=0; row<rows.length;row++) {
        var cels = rows[row].getElementsByTagName('td');
        if(cels.length>14)
            for(i=14;i<=20;i++) cels[i].style.display=stl;
    }
  }

  document.body.onload=function(){
  show_hide_column(false);
  }

  
  var selcmdid=false;
  function srcfournisseur(txt,cmdid){
    selcmdid=cmdid;
    if(txt.length<3) return;
    document.getElementById('srcfournisseur'+cmdid).style.display='block';
    document.getElementById('srcfournisseur'+cmdid).innerHTML="<img src=\"/configurations/www.ma-cave-a-vin.fr/images/templates/ajax_wait.gif\">";
    ajaxrequest("mailbox_ajax.php?txt="+txt,srcfournisseurcb);
  }
  function srcfournisseurcb(httpRequest){
      var xml=httpRequest.responseXML.documentElement;

      if(xml.getElementsByTagName('contact')[0]){
	      var html="<table>";
        html+="<span class=\"close\" onclick=\"this.parentNode.style.display='none';\">X</span><table>";
        html+="<tr class=\"srcth\"><th>Société</th><th>Nom/prénom</th><th>Fonction</th><th>Email</th><th>Portable</th><th>Fixe</th></tr>";
	  for(i=0;i<xml.getElementsByTagName('contact').length;i++){
	      var id=xml.getElementsByTagName('contact')[i].getAttribute('id');
	      var mail=xml.getElementsByTagName('contact')[i].getAttribute('mail');
	      var societe=xml.getElementsByTagName('contact')[i].getAttribute('societe');
        var type=xml.getElementsByTagName("contact")[i].getAttribute("type");
	      var nom=xml.getElementsByTagName('contact')[i].getAttribute('nom');
	      var fonction=xml.getElementsByTagName('contact')[i].getAttribute('fonction') || '';
	      var type=xml.getElementsByTagName('contact')[i].getAttribute('type');
	      var telephone=xml.getElementsByTagName('contact')[i].getAttribute('telephone');
	      var portable=xml.getElementsByTagName('contact')[i].getAttribute('portable');
	      var tel=xml.getElementsByTagName('contact')[i].getAttribute('tel');
	      var caller=xml.getElementsByTagName("contact")[i].getAttribute("caller");
        
	      html+="<tr>";
        html+="<td> "+societe+"</td>";
        html+="<td><span class=\"nom\">"+nom+"</span></td>";
        html+="<td>"+fonction+"</td>";

        if(mail!="") {
            html+="<td class=\"suivilink mail_out\"><span onclick=\"show_choix_suivi(this)\">"+mail+"</span>\
            <span class=\"addsuivi\">\
            <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>\
            <span class=\"suivilink\"onclick=\"mail_auto(undefined,'"+mail+"');mail_suivi('suivi_stock',"+selcmdid+");mail_services('FR','base','adv,sav');srcfournisseur_hide("+selcmdid+");suivi_hide("+selcmdid+");\" title=\""+mail+"\">Ecrire email</span>\
            <span class=\"suivilink mail_in\" onclick=\"suiviauto("+selcmdid+",'mail_in',"+id+",'Email reçu de "+addslashes(societe)+" ("+addslashes(nom)+")');\">Email reçu</span>\
            <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span>\
            </span></td>";
        }
        else {
          html+="<td></td>";
        }
       
      if(portable!="") {
        html+="<td class=\"suivilink tel_out\"><span onclick=\"show_choix_suivi(this)\">"+portable+"</span>\
            <span class=\"addsuivi\">\
            <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>\
            <span class=\"suivilink\" onclick=\"suiviauto("+selcmdid+",'tel_out','"+id+"','Appel téléphone "+addslashes(societe)+" ("+addslashes(nom)+") "+(tel==1?portable:undefined)+"');epigy_call('"+portable+"','"+caller+"');\">Click & call</span>\
            <span class=\"suivilink tel_in\" onclick=\"suiviauto("+selcmdid+",'tel_in',"+id+",'Appel reçu de "+addslashes(societe)+" ("+addslashes(nom)+")');\">Appel reçu</span>\
            <span class=\"suivilink tel_out\" onclick=\"suiviauto("+selcmdid+",'tel_out',"+id+",'Appel de "+addslashes(societe)+" ("+addslashes(nom)+")');\">Appel effectué</span>\
            <span class=\"suivilink\" onclick=\"copy(this)\">Copier</span>\
            </span></td>";
        }
        else {
          html+="<td></td>";
        }

        if(telephone!="") { html+="<td class=\"suivilink tel_out\"><span onclick=\"show_choix_suivi(this)\">"+telephone+"</span>\
            <span class=\"addsuivi\">\
            <span class=\"close\" onclick=\"close_choix_suivi(this)\">X</span>\
            <span class=\"suivilink\" onclick=\"suiviauto("+selcmdid+",'tel_out',"+id+",'Appel téléphone "+addslashes(societe)+" ("+addslashes(nom)+")',"+(tel==1?"'"+telephone+"'":undefined)+");epigy_call('"+telephone+"','"+caller+"');\">Click & call</span>\
            <span class=\"suivilink tel_in\" onclick=\"suiviauto("+selcmdid+",'tel_in',"+id+",'Appel reçu de "+addslashes(societe)+" ("+addslashes(nom)+")');\">Appel reçu</span>\
            <span class=\"suivilink tel_out\" onclick=\"suiviauto("+selcmdid+",'tel_out',"+id+",'Appel de "+addslashes(societe)+" ("+addslashes(nom)+")');\">Appel effectué</span>\
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
      document.getElementById('srcfournisseur'+selcmdid).innerHTML=html;
  }


  function show_choix_suivi(val) {
    var addsuivi = jQuery(val).parent().children().last();
    for (let i = 0; i < addsuivi[0].children.length; i++) {
      addsuivi[0].children[i].style.display='block';
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
  
  function addslashes(txt) {
    return (txt + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
  }
  
  function suiviauto(cmdid,moyen,destinataire,txt){
	  document.forms.suiviform.cmdid.value=cmdid;
	  document.getElementsByName('moyen')[0].value=moyen;
	  document.getElementsByName('destinataire')[0].value=destinataire;
	  document.getElementById('trsuivi'+cmdid).style.display='table-row';
	  document.getElementById('srcfournisseur'+cmdid).style.display='none';
	  document.getElementById('suivititle'+cmdid).innerHTML=txt;
// 	  if(tel!=undefined) document.getElementById('suivititle').innerHTML+="&nbsp; <span class=\"epigy\" onclick=\"epigy_call('"+tel+"');\"><img src=\"images/telephone.png\">"+tel+"</span>";
  }
 
  function srcfournisseur_hide(cmdid){
    document.getElementById('srcfournisseur'+cmdid).style.display='none';
  }
  
  function suivi_hide(cmdid){
	  document.getElementById('trsuivi'+cmdid).style.display='none';
  }
  
  function submitsuivi(cmdid){
	  document.getElementsByName('suivi')[0].value=document.getElementById('txt'+cmdid).value;
	  document.forms.suiviform.submit();
  }
  
  function planningcb(){
	  document.getElementsByName('pour')[0].value=document.getElementById('user').value;
	  document.getElementsByName('rappel')[0].value=cal_timestamp;
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
  
  function suivi_annee(annee){
      var display=document.getElementById('suivi'+annee).style.display;
      if(display=='none'){
	  document.getElementById('suivi'+annee).style.display="table";
	  document.getElementById('btn'+annee).src="images/moins.jpg";
      }else{
	  document.getElementById('suivi'+annee).style.display="none";
	  document.getElementById('btn'+annee).src="images/plus.jpg";
      }
  }

  //fonction qui enlève des articles de la commande de stock
  function supprimer_article_cmdStock(select,article_id) {
    var qte = $jQuery(select).val() ;
    if(qte == "") return ;
    if(confirm ('voulez-vous vraiment supprimer '+ qte + ' articles ?')) {
      $jQuery("body").append('<form method="post" id="form_suppression_articles"><input type="text" name="qte_produit_supprime" value='+qte+' hidden /><input type="text" name="id_produit_supprime" value='+article_id+' hidden /><input type="submit" name="submit_produit_supprime" hidden /></form>') ;
      $jQuery("#form_suppression_articles").submit();
    } ;
  }

  //fonction qui ajoute des articles de la commande de stock
  function ajouter_article_cmdStock(select,article_id) {
    var qte = $jQuery(select).val() ;
    if(qte == "") return ;
    if(confirm ('voulez-vous vraiment ajouter '+ qte + ' articles ?')) {
      $jQuery("body").append('<form method="post" id="form_ajout_articles"><input type="text" name="qte_produit_ajout" value='+qte+' hidden /><input type="text" name="id_produit_ajout" value='+article_id+' hidden /><input type="submit" name="submit_produit_ajout" hidden /></form>') ;
      $jQuery("#form_ajout_articles").submit();
    } ;
  }
  
  function search_cmdid(cmdid){
        document.location="stock.php?action=cmd&stock_cmd_id="+cmdid;
  }
function planningcb(){
    document.getElementsByName('jour')[0].value=cal_jour; 
    document.getElementsByName('pour')[0].value=document.getElementById('user').value;
}

function planningmailcb(){
    document.getElementById('poursuivi').value=document.getElementById('user').value;
}

function wait_chargement(host){
  $jQuery("#div_wait").html("<img src=\"/configurations/"+host+"/images/templates/ajax_wait.gif\">");
}