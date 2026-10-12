<script language="javascript">
var cp="<?=$rs_facture['codepostal']; ?>";
var dep="<?=substr($rs_facture['codepostal'],0,2); ?>";
var ville="<?=strtolower($rs_facture['ville']); ?>";
var pays="<?=$rs_facture['paysid']; ?>";
var depsaison=new Array('06','11','13','20','30','34','66','83','84','98');
var poids;
var fourn_paht=new Array();
<?=$fourn_paht.$jsdevise; ?>
var devise='';
var ajaxtimeout=new Array();

pctrsptassurance=0.6;
valtrsptassurance=0;

var autosaveid;

function adv_save(name,step){
    
    /*if(document.getElementById(name+'-ok')){
        document.getElementById(name+'-ok').innerHTML='<img src=\"/configurations/www.ma-cave-a-vin.fr/images/templates/ajax_wait.gif\" width=\"16\" align=\"absmiddle\">';
        document.getElementById(name+'-ok').style.display = 'inline';
    }*/
    
    //Loader utilisé pour éviter d'aller trop vite dans les étapes
    //Il arrivait que certaines données ne soient pas sauvegardées car l'ajax n'avait pas le temps de se terminer
    //Commenté depuis que chaque étape de l'adv se charge en AJAX
    /*$jQuery('.loader-adv').css('display','block');
    var milieu = $jQuery('.loader-adv').height();
    milieu = milieu / 2;
    $jQuery('.loader-adv').find('img').css('margin-top',milieu);*/
// \"

    var requestid=Math.floor(Math.random() * 12000000000000+1000000000);
    var post = new Array();
    post['action'] = 'update';
    post['requestid'] = requestid;
    post['advid']  = <?php echo "'".$adv->id."'"; ?>;
    
    if (name instanceof Array){
        post['name']  = new Array();
        post['value'] = new Array();

        for(i=0;i<name.length;i++){
            post['name['+i+']']  = name[i];
            post['value['+i+']'] = document.getElementsByName(name[i])[0].value;
        }

    } else {
        post['name']  = name;
        post['value'] = document.getElementsByName(name)[0].value;
    }

    if(step != undefined) post['step'] = step;

    ajaxrequest('adv/adv_ajax.php',updcb,post);
    ajaxtimeout[requestid]=setTimeout(function(){
        adv_save(name,step);
    },2000);
}

function adv_calsave(){
    adv_save(returninput);
}

function adv_autosave(name){
    clearTimeout(autosaveid);
    autosaveid = setTimeout("adv_save('"+name+"')",1000);
}

function fournisseur_save(name,devise){
    document.getElementsByName('fournisseurname')[0].value = name;
    
    var post = new Array();
    post['action']       = 'fournisseur';
    post['advid']        = <?php echo "'".$adv->id."'"; ?>;
    post['destinataire'] = name;
    if(devise != undefined) post['devise'] = devise;

    ajaxrequest('adv/adv_ajax.php',updcb,post);
}

function updcb(httpRequest){

    var xml = httpRequest.responseXML.documentElement;

    if(xml.getElementsByTagName("request")[0]){
        var requestid=xml.getElementsByTagName("request")[0].firstChild.nodeValue;
        clearTimeout(ajaxtimeout[requestid]);
    }
    
    if(xml.getElementsByTagName('field')[0]){
        var field=xml.getElementsByTagName('field')[0];
        var name=field.getAttribute('name');

        $jQuery('.loader-adv').css('display','none');

        /*if(document.getElementById(name+'-ok')){
            document.getElementById(name+'-ok').style.display='none';
        }*/
    }

    if(xml.getElementsByTagName('fournisseur')[0]){
        var f=xml.getElementsByTagName('fournisseur')[0];
        var heure=f.getAttribute('heure');
        if(document.getElementById('henvoi')) document.getElementById('henvoi').innerHTML='à '+heure+'h';
    }
}

var selcolis = 0;
function colis_prepare(checked){

    document.getElementById('colis_conf').style.display = (checked && checked != 0 ? 'block' : ' none');
    
    //Suppression des différents colis
    if(!checked || checked == 0){

        //document.getElementsByName('pcolis')[0].value=0;
        //document.getElementById('pcolis').checked=false;
        //adv_save('pcolis');
    
        var colis_list = document.getElementById('colis_list');
        var inputs = colis_list.getElementsByTagName('input');

        for(i in inputs){
            if(inputs.hasOwnProperty(i)) del_field(inputs[i].name);
        }
    }
    
    //Suppression de tous les colis pour reconstruction
    document.getElementById('art_list').innerHTML = '';
    while(document.getElementById('colis_list').getElementsByTagName('div').length > 0){ 
            var div = document.getElementById('colis_list').getElementsByTagName('div')[0];
            document.getElementById('colis_list').removeChild(div);
    }
    
    //Aucun colisage sélectionné : on sort
    if(!checked) return;

    var post=new Array();
    post['advid']  = <?php echo "'".$adv->id."'"; ?>;
    post['action'] = 'fields';
    
    ajaxrequest('adv/adv_ajax.php',function(httpRequest){

        var xml = httpRequest.responseXML.documentElement;
        
        var colisdata    = new Array();
        var colistype    = new Array();
        var colisdim     = new Array();
        var linecolis    = new Array();
        var linesbycolis = new Array();
        var fields       = xml.getElementsByTagName('field');
        
        for(f=0;f<fields.length;f++){

            var fname      = fields[f].getAttribute('name');
            var colisfield = fname.match(/colis_([0-9]+)/);

            if(colisfield){
                colisdata[colisfield[1]]    = fields[f].getAttribute('value');
                linesbycolis[colisfield[1]] = new Array();

                if(colisdata[colisfield[1]]!=''){
                    var lines = colisdata[colisfield[1]].split(',');
                    for(l=0;l<lines.length;l++){
                        var linedata = lines[l].split('-');
                        if(linecolis[linedata[1]] == undefined) linecolis[linedata[1]]=0;
                        linecolis[linedata[1]] += parseInt(linedata[0]);
                        linesbycolis[colisfield[1].toString()][linedata[1].toString()] = parseInt(linedata[0]);
                    }
                }
            }

            var typefield  = fname.match(/colistype_([0-9]+)/);

            if(typefield){
                colistype[typefield[1]] = fields[f].getAttribute('value');
            }
            
            var dimfield=fname.match(/colis_([hlp])_([0-9]+)/);
            if(dimfield){
                if(colisdim[dimfield[2]]==undefined){
                    var defdim=new Array();
                    defdim['h']=defdim['l']=defdim['p']="";
                    colisdim[dimfield[2]]=defdim;
                }
                colisdim[dimfield[2]][dimfield[1]]=fields[f].getAttribute('value');
            }
        }
        
        var cpt=1;
        for(colisid in linesbycolis){ 
            if(linesbycolis.hasOwnProperty(colisid)){
                
                colis_add(colistype[colisid],colisdata[colisid]);
                var colis=document.getElementById('colisdiv_'+colisid);
                var poids=0;
                
                for(lid in linesbycolis[colisid]){
                    if(linesbycolis[colisid].hasOwnProperty(lid)){
                        var div=document.createElement('p');
                        div.id='line_'+colisid+'_'+lid;
                        div.innerHTML=linesbycolis[colisid][lid]+' '+document.getElementsByName('artcode_'+lid)[0].value+' '+document.getElementById('libelle_'+lid).innerHTML+'<img src="images/supprimer.gif" onclick="colis_del_line('+colisid+','+lid+')">';
                        colis.appendChild(div);

                        poids+=linesbycolis[colisid][lid]*parseFloat(document.getElementsByName('poids_'+lid)[0].value);
                    }
                }
                var colis_h=colis_l=colis_p="";
                if(colisdim[colisid]!=undefined){
                    colis_h=colisdim[colisid]['h'];
                    colis_l=colisdim[colisid]['l'];
                    colis_p=colisdim[colisid]['p'];
                }
                colis.getElementsByTagName('b')[0].innerHTML+='(H:<input type="text" name="colis_h_'+colisid+'" onkeypress="nbonly(event,true,this);" onblur="adv_save(this.name)" value="'+colis_h+'"> L:<input type="text" name="colis_l_'+colisid+'" onkeypress="nbonly(event,true,this);" onblur="adv_save(this.name)" value="'+colis_l+'"> P:<input type="text" name="colis_p_'+colisid+'" onkeypress="nbonly(event,true,this);" onblur="adv_save(this.name)" value="'+colis_p+'">mm '+poids+'kg)';
                
                var input = document.createElement('input');
                input.name = 'colis_poids[]';
                input.value = poids;
                input.type = 'hidden';
                colis.appendChild(input);

                var input = document.createElement('input');
                input.name = 'colispoids_'+cpt;
                input.value = poids;
                input.type = 'hidden';
                colis.appendChild(input);
                cpt++;
            }
        }
        
        //Création des lignes de colisage pour chaque produit
        var chklist = document.getElementsByName('produits2[]');
        if(chklist.length==0) chklist = document.getElementsByName('produits3[]');
        
        for(i=0;i<chklist.length;i++){
            
            if(chklist[i].checked){

                var lid =chklist[i].value;
                var qtename=(document.getElementsByName("qte2_"+lid).length>0?"qte2_":"qte3_")+lid;
                var qte =document.getElementsByName(qtename)[0].value;
                
                if(linecolis[lid]!=undefined) qte-=parseInt(linecolis[lid]);
                
                if(qte>0){
                    var p      = document.createElement('p');
                    var selqte = document.createElement('select');
                    selqte.id='qtecolis_'+lid;
                    for(q=1;q<=qte;q++){
                        var opt=document.createElement('option');
                        opt.value=q;
                        opt.innerHTML=q;
                        if(q==qte) opt.selected=true;
                        selqte.appendChild(opt);
                    }
                    var btn=document.createElement('input');
                    btn.type='button';
                    btn.setAttribute('onclick','colis_add_line('+lid+');');
                    btn.value='<=';
                    var lib=document.createElement('span');
                    lib.id='lib_'+lid;
                    lib.innerHTML=document.getElementsByName('artcode_'+lid)[0].value+' '+document.getElementById('libelle_'+lid).innerHTML;
                    p.appendChild(btn);
                    p.appendChild(selqte);
                    p.appendChild(lib);

                    document.getElementById('art_list').appendChild(p);
                }
            }
        }
    },post);
    
}

var nb_palette = 0;
function colis_add(type, data=undefined){
    var colis_list=document.getElementById('colis_list');
    var colis_div=colis_list.getElementsByTagName('div');
    var type_manu = 'Colis';

    if(type == 'palette') type_manu = 'Palette';

    var div=document.createElement('div');
    var colisnb=colis_div.length+1;
    div.id='colisdiv_'+colisnb;
    div.setAttribute('onclick','select_colis('+colisnb+')');
    div.innerHTML='<b>'+type_manu+' '+colisnb+'</b><img src="images/supprimer.gif" class="del" onclick="colis_del(this.parentNode)" title="Supprimer le colis">';
    
    //Création d'un input hidden avec name "colis_X"
    var input = document.createElement('input');
    input.type = 'hidden';
    input.id   = input.name = 'colis_'+colisnb;
    if(data!=undefined) input.value=data;
    div.appendChild(input);

    //Création d'un input indiquant le type
    var input2 = document.createElement('input');
    input2.type  = 'hidden';
    input2.id    = input2.name = 'colistype_'+colisnb;
    input2.value = type;
    div.appendChild(input2);

    colis_list.appendChild(div);
    select_colis(colisnb);
    adv_save(input.name);
    adv_save(input2.name);
}

function colis_del(colisdiv){
    var input=colisdiv.getElementsByTagName('input')[0];
    var colisfield=input.id.match(/colis_([0-9]+)/);
    var colisid=parseInt(colisfield[1]);
    del_field(input.id);
    input.parentNode.removeChild(input);

    
    var colis_list=document.getElementById('colis_list');
    var colis_div=colis_list.getElementsByTagName('div');
    var colisnb=colis_div.length;
    
    if(colisid<colisnb){
        var newnames=new Array();
        for(i=colisid+1;i<=colisnb;i++){
        var newname='colis_'+(i-1);
        newnames.push(newname);
        document.getElementsByName('colis_'+i)[0].name=newname;
        del_field('colis_'+i);
        }
        adv_save(newnames);
        console.debug(newnames);
    }
    
    
    setTimeout(function(){
        colis_prepare(true);
    },500);
}

function select_colis(colisid){
    var colis_list=document.getElementById('colis_list');
    var colis_div=colis_list.getElementsByTagName('div');
    for(i=0;i<colis_div.length;i++){
        var cl=(colis_div[i].id=='colisdiv_'+colisid?'selected':'');
        colis_div[i].className=cl;
    }
    selcolis=colisid;
}

function colis_add_line(lid){
    var data=document.getElementById('colis_'+selcolis).value;
    if(data=='')
        var lines=new Array();
    else
        var lines=data.split(',');

    var qte=document.getElementById('qtecolis_'+lid).value;
    lines.push(qte+'-'+lid);

    document.getElementById('colis_'+selcolis).value=lines.join(',');
    adv_save('colis_'+selcolis);
    setTimeout(function(){
        colis_prepare(true);
    },500);
}

function colis_del_line(colisid,lid){
    var data=document.getElementById('colis_'+colisid).value;
    var lines=data.split(',');
    for(i in lines){
            if(lines.hasOwnProperty(i)){
            if(lines[i].indexOf('-'+lid)>-1){
                lines.splice(i,1);
            }
            }
    }

    document.getElementById('colis_'+colisid).value=lines.join(',');
    adv_save('colis_'+colisid);
    setTimeout(function(){
        colis_prepare(true);
    },500);
}

function show_colis(val){
    if(val == 0){
        document.getElementById('colis_conf').style.display = 'none';
    } else {
        document.getElementById('colis_conf').style.display = 'block';
    }
}


function del_field(name){
    var post=new Array();
    post['action']='delfield';
    post['advid']='<?=$adv->id; ?>';
    post['name']=name;
    ajaxrequest('adv/adv_ajax.php',updcb,post);
}

function select_install(name) {
    document.getElementsByName('livrslsname')[0].value=name;
    if(prixmontage[name]!=undefined){
        document.getElementById('tauxmontage').innerHTML='(Taux horaire : '+prixmontage[name]+'&euro;HT)';
        document.getElementsByName('slscoutmontageval')[0].value=prixmontage[name]*montage_heures;
        document.getElementById('trmontage').style.display='table-row';
    }else
        document.getElementById('trmontage').style.display='none';
    adv_save('slscoutmontageval');
}

function updligne(ligneid,qte,step){
    var post=new Array();
    post['advid']='<?=$adv->id; ?>';
    post['action']='ligne';
    post['ligneid']=ligneid;
    post['qte']=qte;
    if(step!=undefined) post['step']=step;
    ajaxrequest('adv/adv_ajax.php',updcb,post);
}

function reserve_stock(artid,lieuid,qte,annule){
    var post=new Array();
    post['action']='reserve';
    post['artid']=artid;
    post['lieuid']=lieuid;
    post['qte']=qte;
    post['facturid']='<?=$facturid; ?>';
    if(annule!=undefined) post['annule']=1;
    ajaxrequest('adv/adv_ajax.php',reservecb,post);
}

function reservecb(httpRequest){
    var xml=httpRequest.responseXML.documentElement;
    if(xml.getElementsByTagName('stock')[0]){
        var stock=xml.getElementsByTagName('stock')[0];
        var lieuid=stock.getAttribute('lieuid');
        var artid=stock.getAttribute('artid');
        if(document.getElementById('reserve'+lieuid+artid)) document.getElementById('reserve'+lieuid+artid).innerHTML=stock.firstChild.nodeValue;
    }
}

function envoidate_save(){
    if(document.getElementsByName('envoiauto')[1].checked){
        document.getElementById('envoi1').style.display='none';
        document.getElementById('envoi0').style.display='inline';
        var d=0;
    }else{
        document.getElementById('envoi0').style.display='none';
        document.getElementById('envoi1').style.display='inline';
        var d=document.getElementById('envoidate').value;
    }
    if(d=='') d=0;
    var post=new Array();
    post['action']='envoi';
    post['advid']='<?=$adv->id; ?>';
    post['value']=d;
    ajaxrequest('adv/adv_ajax.php',updcb,post);
}

function preview(){
    var advid='<?=$adv->id; ?>';
    if(advid=='') return;
//    my_form = eval(formid);
    window.open("adv/adv2.php?preview=1&facturid=<?=$facturid; ?>&advid=<?=$adv->id; ?>&actionadv=advfournisseur", "popup", "height=640,scrollbars=yes,width=640,menubar='no',location='no',status='no'");
//    my_form.action=\"adv2.php?preview=1&facturid=$facturid\";
//    my_form.target = \"popup\";
//    my_form.submit();
}

function getElementsByRegExpName(p_regexp, p_element, p_tagName) {
	p_element = p_element === undefined ? document : p_element;
	p_tagName = p_tagName === undefined ? '*' : p_tagName;
	var v_return = [];
	var v_inc = 0;
	for(var v_i = 0, v_il = p_element.getElementsByTagName(p_tagName).length; v_i < v_il; v_i++) {
		if(p_element.getElementsByTagName(p_tagName).item(v_i).name && p_element.getElementsByTagName(p_tagName).item(v_i).name.match(p_regexp)) {
			v_return[v_inc] = p_element.getElementsByTagName(p_tagName).item(v_i);
			v_inc++;
		}
	}
	return v_return;
}

function showitemlist(id,sender){
	if(sender!=undefined)
	    var icon=sender.getElementsByTagName('img')[0];

	var display=document.getElementById(id).style.display;
	if(display=='none'){
	    display='';
	    var iconimg='images/moins.gif';
	}else{
	    display='none';
	    var iconimg='images/plus.gif';
	}
	document.getElementById(id).style.display=display;
	if(icon!=undefined) icon.src=iconimg;
}

function nbonly(event,decimal,input) {
    // Compatibilité IE / Firefox
    if(!event && window.event) {
        event=window.event;
    }

    if(decimal==undefined) decimal=false;

    // IE
    if(event.keyCode>0 && (event.keyCode < 48 || event.keyCode > 57) && event.keyCode!=8 && event.keyCode!=9) {
        if(!decimal || event.keyCode!=46 || input.value.indexOf('.')>0){
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

function verif_ar(lfid,td){
    var value=parseInt(document.getElementsByName(td+'_'+lfid)[0].value);
    if(td=='artcode'){
        var opt=ar_options(lfid);
        value=value && opt;
    }
    var tdid='td'+lfid+td;
    var colors=new Array();
    colors[0]='#F99';
    colors[1]='#99dd99';
    document.getElementById(tdid).style.backgroundColor=colors[value];
    ar_color();
}

function ar_options(lfid){
    var ret=1;
    var inp=getElementsByRegExpName('option_([0-9]+)_'+lfid);
    if(inp.length==0) return 1;
    for(oi=0;oi<inp.length;oi++){
        if(inp[oi].value==0) ret=0;
        var optname=inp[oi].name.split('_');
        document.getElementById('li_'+optname[1]+'_'+optname[2]).style.color=(inp[oi].value==0?'red':'green');
    }
    return ret;
}

function ar_color(){
    var lfids=document.getElementsByName('arlfids')[0].value.split(',');
    var fields=new Array('qte','artcode','date','livr','paht','trspt');
    var ok=0;
    var unk=0;
    var nb=0;
    for(l=0;l<lfids.length;l++){
        for(i=0;i<fields.length;i++){
        
            if(document.getElementsByName(fields[i]+'_'+lfids[l]).length>0){
                var value=document.getElementsByName(fields[i]+'_'+lfids[l])[0].value;
                if(value=='') unk++;
                value=Number(value);
                if(value==1 && fields[i]=='artcode'){
                    var opt=ar_options(lfids[l]);
                    value=value && opt;
                }
                if(value==1) ok++;
                nb++;
            }
        }
    }
    if(ok==nb){
        document.getElementsByName('ar_valide')[0].value=1;
        document.getElementById('new_ar').style.backgroundColor='#0D0';
    }else{
        if(unk==0)
            document.getElementById('new_ar').style.backgroundColor='#F22';
        document.getElementsByName('ar_valide')[0].value=0;
    }
      
    if(ok==nb || unk==0){
        if(document.getElementsByName('ar_date')[0].value!='' && document.getElementsByName('ar_date_recu')[0].value!=''){
            document.getElementById('valid_ar').style.display='block';
        }
    }
//     console.debug(ok);console.debug(unk);
}

function send_ar(){
    if(document.getElementsByName('ar_valide')[0].value==0){
        var post=new Array();
        post['advid']='<?=$adv->id; ?>';
        post['action']='mail';
        ajaxrequest('adv/adv_ajax.php',ar_error_mail,post);
    }else
	document.forms.arform.submit();
}

function ar_error_mail(httpRequest){
	var ar_num=document.getElementsByName('ar_num')[0].value;
	var ar_date=document.getElementsByName('ar_date')[0].value;
	
	var fields=new Array();
	var xml=httpRequest.responseXML.documentElement;

	var msg="<p style='color:red;'>Bonjour,</p><p style='color:red;'>Nous avons constaté une ou plusieurs erreurs/anomalies sur votre accusé de réception "+ar_num+" du "+ar_date+". Vous trouverez ci-dessous le détail sur la commande initiale.</p>";
	msg+="<p style='color:red;'>Nous vous remercions de bien vouloir nous retourner un AR conforme.</p>";

	mailbody=xml.getElementsByTagName('body')[0].firstChild.nodeValue;
	var firstmail=new DOMParser().parseFromString(mailbody,'text/html');
	var destinataire=xml.getElementsByTagName('to')[0].firstChild.nodeValue;
	
	
	var lfids=document.getElementsByName('arlfids')[0].value.split(',');
	for(l=0;l<lfids.length;l++){
        var lfid=lfids[l];
        
        if(document.getElementsByName('qte_'+lfid)[0].value==0){
            firstmail.getElementById('qte_'+lfid).style.color='red';
            firstmail.getElementById('qte_'+lfid).style.fontWeight='bold';
        }
        if(document.getElementsByName('artcode_'+lfid)[0].value==0){
            firstmail.getElementById('artcode_'+lfid).style.color='red';
            firstmail.getElementById('artcode_'+lfid).style.fontWeight='bold';
        }
        var options=getElementsByRegExpName('option_([0-9]+)_'+lfid);
        if(options.length>0){
            for(oi=0;oi<options.length;oi++){
                if(options[oi].value==0){
                    var optname=options[oi].name.split('_');
                    var span=firstmail.getElementById('opt_'+lfid+'_'+optname[1]);
                    if(span){
                        span.style.color='red';
                        span.style.fontWeight='bold';
                    }
                }
            }
        }
        if(document.getElementsByName('date_'+lfid)[0].value==0 && firstmail.getElementById('indate')){
            firstmail.getElementById('indate').style.color='red';
            firstmail.getElementById('indate').style.fontWeight='bold';
        }
        if(document.getElementsByName('livr_'+lfid)[0].value==0 && firstmail.getElementById('outdate')){
            firstmail.getElementById('outdate').style.color='red';
            firstmail.getElementById('outdate').style.fontWeight='bold';
        }
        if(document.getElementsByName('paht_'+lfid)[0].value==0){
            firstmail.getElementById('paht_'+lfid).style.color='red';
            firstmail.getElementById('paht_'+lfid).style.fontWeight='bold';
        }
        if(document.getElementsByName('trspt_'+lfid)[0].value==0){
            firstmail.getElementById('trsptval').style.color='red';
            firstmail.getElementById('trsptval').style.fontWeight='bold';
        }
        var infos=document.getElementsByName('info_'+lfid)[0].value;
        if(infos!=''){
            var p=firstmail.createElement('p');
            p.style.color='red';
            p.innerHTML=infos;
            firstmail.getElementById('lf_'+lfid).appendChild(p);
        }
	}
	
	msg+="<blockquote style='border-left:4px solid #CCC;padding-left: 8px;'>"+firstmail.body.innerHTML+"</blockquote>";
	msg+="<p>Merci par avance de votre réponse, en répondant directement sur ce mail.</p>";
	mail_auto(undefined,destinataire,undefined,"AR "+ar_num+" commande <?=$adv->facturid; ?>",msg);
	mail_suivi('suivi_commande','<?=$adv->facturid; ?>');
	sendform=document.forms.arform;
}

function modif_ar(){
	document.forms.arformedit.submit();
}


function change_ar_date(lfid,olddate,typedate){
	document.getElementById('old'+typedate+'_'+lfid).style.display='none';
	document.getElementById('new'+typedate+'_'+lfid).style.display='block';
	var d=new Date();
	d.setTime(olddate*1000);
	document.getElementById('arlivr_'+lfid).value=('0'+d.getDate()).slice(-2)+'/'+('0'+(d.getMonth()+1)).slice(-2)+'/'+d.getFullYear();
}

function change_ar_price(lfid,oldprice,name){
	document.getElementById('old'+name+'_'+lfid).style.display='none';
	document.getElementById('new'+name+'_'+lfid).style.display='block';
	document.getElementsByName('new'+name+'val_'+lfid)[0].value=parseFloat(oldprice);
}

// Fonction qui génére le cout transport selon
// le TYPE de livraison et le LIEU d'enlèvement
function auto_cout(){
	var type = document.getElementsByName('trsptname')[0].value;
	var lieu = document.getElementsByName('lieutrspt')[0].value;
	switch (type) {
		case 'GLS':
			if(lieu == 'fourn217' || lieu == 'fourn630900')
				document.getElementsByName('trsptcoutval')[0].value = 5.73; //avant 5.62
			else 
				document.getElementsByName('trsptcoutval')[0].value = 7.26;
			break;
	}
}


var devise='<?=$adv->devise; ?>';

var fields=new Array();

<?php
if(!is_null($adv->fields)){
    foreach($adv->fields as $name=>$value){
        if(is_array($value)){
                print "fields['$name']=\"".preg_replace("/\n|\r|\r\n|\t/","",$value)."\";\n";
        } else {
                print "fields['$name']=\"".addslashes(preg_replace("/\n|\r|\r\n|\t/","",$value))."\";\n";
        }
    }
}

if($adv->type=="fourn"){
    if(!is_null($activefournkeys)) foreach($activefournkeys as $fkey=>$value) print "activefournkeys['$fkey']=$value;\n";
    if(!is_null($lieustocknb)) foreach($lieustocknb as $lieuid=>$value) print "lieustocknb['$lieuid']=$value;\n";
    print "sumprod();";
}

if($adv->type=="trspt" && $adv->destinataire!=""){
     print ""
     . "totalassurance();"
     . "tarifs('".$adv->destinataire."',fields);"
     . "trspttarif();";
     
}

if($adv->fields['pcolis']==1)
	print "colis_prepare(true);";
	
if($adv->type=="sls"){
    print "assurancesls();
    totalsls();";
    if(!is_null($totalmontage)) print "\nvar montage_heures=".floatval($totalmontage).";";
    print "\nvar prixmontage=new Array();";
    if(!is_null($prixmontage)) foreach($prixmontage as $societe=>$paht) print "\nprixmontage['$societe']=$paht;";
}

?>

$jQuery( document ).ready(function() {
    <?php echo $js_popup_pj; ?>
});

function majStepContenu(div_contenu, file_ajax, functions = ''){
    $jQuery(div_contenu).css('text-align','center');
    $jQuery(div_contenu).html('<img src="/admin/images/loader.gif" style="width: 30px; margin: 25px auto;">');

    $jQuery.ajax({
        
        type : 'GET',
        url : file_ajax,
        dataType : 'text'

    }).done(function(responseText){

        $jQuery(div_contenu).css('text-align','left');
        $jQuery(div_contenu).html(responseText);

        if(functions != ''){
            eval(functions);
        } 

        if(document.getElementById('cplivr')) document.getElementById('cplivr').innerHTML = document.getElementById('cplivr_temp').value;
        if(document.getElementById('bonslsname')) document.getElementById('bonslsname').innerHTML = document.getElementById('bonslsname_temp').value;
        if(document.getElementsByName('bonstrsptslspj')[0] && document.getElementById('bonstrsptslspj_temp').value == 1) document.getElementsByName('bonstrsptslspj')[0].checked = true;
        if(document.getElementsByName('bonstrsptpj')[0] && document.getElementById('bonstrsptpj_temp').value == 0) document.getElementsByName('bonstrsptpj')[0].checked = false;
    });
}

function loadStep(step, file_ajax, functions){

    var stepID = '#step'+step;

    $jQuery(stepID).empty();
    $jQuery(stepID).show();
    majStepContenu(stepID, file_ajax, functions);

    //On masque toutes les étapes suivantes affichées
    var m=step.match('^([a-z]+)([0-9]{1,2})$');
    if(m==null) return;
    m[2]++;
    while(document.getElementById('step'+m[1]+m[2])){
        document.getElementById('step'+m[1]+m[2]).style.display='none';
        m[2]++;
    }
}

function checkPoidsColis(limite, transporteur){

    if(limite > 0){
        if(document.getElementsByName('colis_poids[]')[0]){ //Colisage multiple
            var chklist = document.getElementsByName('colis_poids[]');
            var num_colis = 0;
            for(i=0;i<chklist.length;i++){
                num_colis = i+1;
                if(chklist[i].value >= limite){
                    alert('Transport par '+transporteur+' impossible, un des colis pèse plus de '+limite+' kg');
                    return false;
                } else {
                    adv_save('colispoids_'+num_colis);
                }
            }
            
        } else { //Colisage simple
            var poidstotal = 0;
            var chklist = document.getElementsByName('produits2[]');
            var ligneid = 0;

            for(i=0;i<chklist.length;i++){
                if(chklist[i].checked){
                    ligneid = chklist[i].value;
                    poidstotal += document.getElementsByName('qte2_'+ligneid)[0].value * parseFloat(document.getElementsByName('poids_'+ligneid)[0].value);
                }
            }

            if(poidstotal >= limite){
                alert('Transport par '+transporteur+' impossible, un des colis pèse plus de '+limite+' kg');
                return false;
            } else {
                var colis = document.getElementById('colis_conf');
                var input = document.createElement('input');
                input.name = 'colispoids_1';
                input.value = poidstotal;
                input.type = 'hidden';
                colis.appendChild(input);
                adv_save('colispoids_1');
            }
        }
    }

    return true;		
}

function getRelaisColisTNT(cp, ville, advid, facturid, name){

    if(cp != '' && ville != ''){
        $jQuery.ajax({
        
            type : 'GET',
            url : 'adv/ajax/getRelaisColisTNT.php',
            dataType : 'text',
            data : { 'cp' : cp, 'ville' : ville, 'advid' : advid, 'facturid' : facturid, 'name' : name }

        }).done(function(responseText){

            $jQuery('#relais_results').html(responseText);

        });
    } else {
        alert('Veuillez saisir un code postal et une commune');
        return false;
    }

    return true;
}

function showDetailsTNT(val, fournisseur_nom, commande_id, adv_id, val_edit = '', annule = ''){

    $jQuery('#steptrsptdetails').empty();

    switch(val){
        case 'tnt_pro':
        case 'tnt_part':				

            var opt1 = $jQuery("<td id=\"trspt_opt1\"></td>");
            var opt2 = $jQuery("<td id=\"trspt_opt2\"></td>");
            var opt3 = $jQuery("<td id=\"trspt_opt3\"></td>");
            var opt4 = $jQuery("<td id=\"trspt_opt4\"></td>");

            opt1.appendTo('#steptrsptdetails');
            opt2.appendTo('#steptrsptdetails');
            opt3.appendTo('#steptrsptdetails');
            opt4.appendTo('#steptrsptdetails');

            var checkopt1 = '';
            var checkopt2 = '';
            var checkopt3 = '';
            var checkopt4 = '';

            if(val_edit != ''){
                if(val_edit == 'express') checkopt1 = 'checked';
                if(val_edit == 'express9') checkopt2 = 'checked';
                if(val_edit == 'express10') checkopt3 = 'checked';
                if(val_edit == 'express12') checkopt4 = 'checked';
            }

            var disabled = '';
            if(annule == 1){
                disabled = 'disabled';
            } 

            var input0 = $jQuery("<input type=\"hidden\" name=\"trspttypedetails\" value=\""+val_edit+"\">");
            var input1 = $jQuery("<input type=\"radio\" id=\"opt_tnt1\" name=\"opttrspttypedetails\" value=\"express\" "+checkopt1+" "+disabled+" onclick=\"document.getElementsByName('trspttypedetails')[0].value=this.value; loadStep('trspt5', '/admin/adv/ajax/majProduits.php?name="+fournisseur_nom+"&facturid="+commande_id+"&advid="+adv_id+"'); adv_save('trspttypedetails',45);\" style=\"cursor: pointer;\">");
            var input2 = $jQuery("<input type=\"radio\" id=\"opt_tnt2\" name=\"opttrspttypedetails\" value=\"express9\" "+checkopt2+" "+disabled+" onclick=\"document.getElementsByName('trspttypedetails')[0].value=this.value; loadStep('trspt5', '/admin/adv/ajax/majProduits.php?name="+fournisseur_nom+"&facturid="+commande_id+"&advid="+adv_id+"'); adv_save('trspttypedetails',45);\" style=\"cursor: pointer;\"> Express 9h00");
            var input3 = $jQuery("<input type=\"radio\" id=\"opt_tnt3\" name=\"opttrspttypedetails\" value=\"express10\" "+checkopt3+" "+disabled+" onclick=\"document.getElementsByName('trspttypedetails')[0].value=this.value; loadStep('trspt5', '/admin/adv/ajax/majProduits.php?name="+fournisseur_nom+"&facturid="+commande_id+"&advid="+adv_id+"'); adv_save('trspttypedetails',45);\" style=\"cursor: pointer;\"> Express 10h00");
            var input4 = $jQuery("<input type=\"radio\" id=\"opt_tnt4\" name=\"opttrspttypedetails\" value=\"express12\" "+checkopt4+" "+disabled+" onclick=\"document.getElementsByName('trspttypedetails')[0].value=this.value; loadStep('trspt5', '/admin/adv/ajax/majProduits.php?name="+fournisseur_nom+"&facturid="+commande_id+"&advid="+adv_id+"'); adv_save('trspttypedetails',45);\" style=\"cursor: pointer;\"> Express 12h00");

            input0.appendTo(opt1);
            input1.appendTo(opt1);
            input1.after('<label for="opt_tnt1" style="cursor: pointer;">Express</label>');
            input2.appendTo(opt2);
            input2.after('<label for="opt_tnt2" style="cursor: pointer;">Express 9h00</label>');
            input3.appendTo(opt3);
            input3.after('<label for="opt_tnt3" style="cursor: pointer;">Express 10h00</label>');
            input4.appendTo(opt4);
            input4.after('<label for="opt_tnt4" style="cursor: pointer;">Express 12h00</label>');

            $jQuery('#steptrsptdetails').show();

            break;
        case 'tnt_relais':

            loadStep('trspt5', '/admin/adv/ajax/majProduits.php?name='+fournisseur_nom+'&facturid='+commande_id+'&advid='+adv_id);

            break;
    }
}

function majTypeInstallateur(installateur)
{
    $jQuery.ajax({
        type : 'GET',
        url : 'adv/ajax/get_type_trspt_installateur.php',
        dataType : 'text',
        data : { 'installateur' : installateur, 'poids' : poids, 'departement' : dep, 'pays' : pays }
    }).done(function(responseText){
        $jQuery('#type_trspt_install').html(responseText);
    });
}

function tarif_transport(fournisseur_id)
{
    var transport_type = document.getElementsByName('typetrspt')[0].value;

    $jQuery.ajax({
        type : 'POST',
        url : 'adv/adv_ajax.php',
        dataType : 'xml',
        data : { 'action' : 'transport_tarif', 'fournisseur_id' : fournisseur_id, 'transport_poids' : poids, 'transport_type' : transport_type, 'transport_pays' : pays, 'transport_dep' : dep }
    }).done(function(xml){
        var prix = $jQuery(xml).find('tarif').text();
        document.getElementsByName('slscouttrsptval')[0].value = prix;
    });
}

function edit_tracking(id){
    document.getElementById('tracklink'+id).style.display='none';
    document.getElementById('trackedit'+id).style.display='block';
}

function change_devise(fdevise) {
    var spanlist=document.getElementsByTagName('span');
    for(i=0;i<spanlist.length;i++)
    if(spanlist[i].id.substr(-7)=='_devise') spanlist[i].innerHTML=devises[fdevise];
    
    devise=fdevise;
}


function arEdit(adv,id,lignes,ar){
    document.getElementById('tabAR'+adv).innerHTML = '';
    ajaxrequest('adv/ajax/adv_ar.php?adv='+adv+'&envoi='+id+'&lignes='+lignes+'&ar='+ar,function(httpRequest){
        document.getElementById('tabAR'+adv).innerHTML = httpRequest.responseText;
    });
}

Array.prototype.in_array = function(p_val) {
    for(var i = 0, l = this.length; i < l; i++) {
        if(this[i] == p_val) {
        rowid = i;
        return true;
        }
    }
    return false;
}

function getUrlVars(){
var vars = [], hash;
var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');

for(var i = 0; i < hashes.length; i++){
    hash = hashes[i].split('=');
    vars.push(hash[0]);
    vars[hash[0]] = hash[1];
}

return vars;
}

get=getUrlVars();
if(get['efl']!=undefined) document.location='#adv';

function showstep(step){
    document.getElementById('step'+step).style.display='block';
    var m=step.match('^([a-z]+)([0-9]{1,2})$');
    if(m==null) return;
    m[2]++;
    while(document.getElementById('step'+m[1]+m[2])){
        document.getElementById('step'+m[1]+m[2]).style.display='none';
        m[2]++;
    }
}

function installname(){
    var installname=document.getElementsByName('livrslsname')[0].value;
    if(installname.match(/girard|agediss/gi)) return 'sls';
    if(installname.match(/globe/gi)) return 'globe';
    if(installname.match(/enigma/gi)) return 'enigma';
    return installname;
}

var lieustocknb=new Array();
function selstock(sender,lieuids){
var lieuid=lieuids.split(',');
    var value=(sender.checked?1:-1);
    for(i=0;i<lieuid.length;i++){
        if(isNaN(lieustocknb[lieuid[i]])) lieustocknb[lieuid[i]]=0;
        lieustocknb[lieuid[i]]+=value;
    }

    var prodnb=0;
    var produits=document.getElementsByName('produits[]');
    for(i=0;i<produits.length;i++) if(produits[i].checked) prodnb++;

    for(l in lieustocknb){
        if(document.getElementById('fournstocktd'+l)){
        document.getElementById('fournstocktd'+l).style.display=(lieustocknb[l]==prodnb?'table-cell':'none');
        }
    }
}

function setlieustock(id){
    document.getElementsByName('lieustock')[0].value=id;
    adv_save('lieustock');
}

function setcmdtype(){
    var states=new Array();
    var chkproduit=document.getElementsByName('produits[]');
    for(i=0;i<chkproduit.length;i++){
        if(chkproduit[i].checked){
        var state=document.getElementsByName('envoi_'+chkproduit[i].value)[0].value;
        states[state]=true;
        }
    }

    document.getElementById('newcmd').checked=false;
    document.getElementById('newcmd').style.display=(states[2]?'none':'inline');
    document.getElementById('delcmd').checked=false;
    document.getElementById('delcmd').style.display=(states[1]?'none':'inline');
    document.getElementById('step4msg').style.display=((states[1]&&states[2])?'inline':'none');
}

var activefournkeys=new Array();
function showlink(sender,fournkeys){
    for(i=0;i<fournkeys.length;i++){
        if(isNaN(activefournkeys[fournkeys[i]])) activefournkeys[fournkeys[i]]=0;
        activefournkeys[fournkeys[i]]+=(sender.checked?1:-1);
    }
    
    var prodnb=0;
    var produits=document.getElementsByName('produits[]');
    for(i=0;i<produits.length;i++) if(produits[i].checked) prodnb++;

    for(l in activefournkeys){
        if(document.getElementById(l)) document.getElementById(l).style.display=(activefournkeys[l]==prodnb?'table-cell':'none');
    }
}

function copydate(from,to,delai){
    if(!document.getElementsByName(from)) return;
    if(!document.getElementsByName(to)) return;

    var sp=document.getElementsByName(from)[0].value.split('/');
    if(sp==false){
        document.getElementsByName(to)[0].value='';
        return;
    }
    var d=new Date(sp[2],sp[1],sp[0]);
    d.setTime(d.getTime()+delai*1000);
    document.getElementsByName(to)[0].value=d.getDate()+'/'+d.getMonth()+'/'+d.getFullYear();
}

function tarifs(type,fields){
    totalassurance();
        
    var opts = new trsptprice(type,poidstotal,totalarticles,cp,pays);
    opts.fields = fields;
    document.getElementById('priceopts').innerHTML = opts.generate();
    document.getElementById('totaltrspt').innerHTML = '0 €';
    if(opts.postaction != '') eval(opts.postaction);
}

function totalsls(){
    total=0;
    if(document.getElementsByName('slscouttrspt')[0].value==1) total+=parseFloat(document.getElementsByName('slscouttrsptval')[0].value);
    if(document.getElementsByName('slscoutctrremb')[0].value==1) total+=parseFloat(document.getElementsByName('slscoutctrrembval')[0].value);
    if(document.getElementsByName('slscoutcorse')[0].value==1) total+=parseFloat(document.getElementsByName('slscorsetotal')[0].value);
    if(document.getElementsByName('slscoutreprise')[0].value==1) total+=parseFloat(document.getElementsByName('slscoutrepriseval')[0].value);
    if(document.getElementsByName('slscoutassur')[0].value==1) total+=parseFloat(document.getElementsByName('slscoutassurval')[0].value);
    if(document.getElementsByName('slscoutother')[0].value==1) total+=parseFloat(document.getElementsByName('slscoutotherval')[0].value);
    if(document.getElementsByName('enigmacoutassur')[0].value==1) total+=parseFloat(document.getElementsByName('enigmacoutassurval')[0].value);
    if(document.getElementsByName('enigmaprise')[0].value==1) total+=parseFloat(document.getElementsByName('enigmapriseval')[0].value);

    document.getElementById('totalsls').innerHTML=Math.round(total*100)/100+'&nbsp;'+devises[devise];
}

function valid3(showpreview){
    error="";
    livr=false;

    if(document.getElementsByName('livrsls')[0].value!='') livr=true;
    if(livr==false) error="Il faut sélectionner une agence";

    if(error==""){
        if(showpreview==true){
        preview(form3);
        }else{
            document.forms.form3.action='facture.php?facturid=<?=$facturid; ?>&action=editer';
            document.forms.form3.target = "_self";
            document.forms.form3.submit();
        }
    }else
        alert(error); 
}



upddatetype='';
efl='';
function updadvdate(){
    document.location='facture.php?action=editer&facturid=<?=$facturid; ?>&upddatetype='+upddatetype+'&efl='+efl+'&ts='+cal_timestamp+'#adv';
}

function deladvcmd(id){
    if(confirm('Etes vous sur de vouloir effacer cette commande fournisseur ?')) document.location='facture.php?action=editer&facturid=<?=$facturid; ?>&deladvcmd='+id+'#adv';
}

function updcomptacomment(id){
  var comment=document.getElementById('comment'+id).value;
  comment = comment.replace(/#/g,' ');
  document.location='facture.php?action=editer&facturid=<?=$facturid; ?>&comment='+comment+'&efid='+id+'#adv';
}

function updadvcomment(type){
    var comment=document.getElementById(type+'advcomment').value;
    document.location='facture.php?action=editer&facturid=<?=$facturid; ?>&advcomment='+comment+'&commenttype='+type+'#adv';
}

function cmdtxt(cmdtype,lid,txt){
    document.location='facture.php?action=editer&facturid=<?=$facturid; ?>&lid='+lid+'&mtype='+cmdtype+'&txt='+txt+'#adv';
}

var gcmdtype='';
var glid=0;
var gtypedate='';
function cmddate(input,cmdtype,lid,typedate){
    gcmdtype=cmdtype;
    glid=lid;
    gtypedate=typedate;
    calendrier_show(document.getElementById(input).id,document.getElementById(input).value,undefined,cmddate2);
}

function cmddate2(){
    document.location='facture.php?action=editer&facturid=<?=$facturid; ?>&lid='+glid+'&mtype='+gcmdtype+'&typedate='+gtypedate+'&ts='+cal_timestamp+'#adv';
}

efid=0;
function updlivrcmd(){
    document.location='facture.php?action=editer&facturid=<?=$facturid; ?>&datelivr='+cal_timestamp+'&efid='+efid+'#adv';
}
function updenlevcmd(){
    document.location='facture.php?action=editer&facturid=<?=$facturid; ?>&dateenlev='+cal_timestamp+'&efid='+efid+'#adv';
}

function deladv(advid,envoidate){
    var txt='Etes-vous sûr de vouloir effacer cette commande ?';
    if(envoidate>0) txt+='\\nAttention un envoi est programmé';
    if(confirm(txt)) document.location='facture.php?action=editer&facturid=<?=$facturid; ?>&deladv='+advid+'#adv';
}

function assurancesls()
{
    totalarticles = 0;
    poids         = 0;
    max           = 0;
    prixmult      = 1;

    var qtecave = 0;

    //Boucle sur les produits envoyés à l'installateur
    for(i=0;i<document.getElementsByName('produits3[]').length;i++)
    {
        if(document.getElementsByName('produits3[]')[i].checked)
        {
            var ligneid = document.getElementsByName('produits3[]')[i].value;
            var qte     = document.getElementsByName('qte3_' + ligneid)[0].value;

            totalarticles += qte * (parseFloat(document.getElementsByName('price3_' + ligneid)[0].value) + parseFloat(document.getElementsByName('ecopart3_' + ligneid)[0].value));
            poids         += qte * parseFloat(document.getElementsByName('poids3_' + ligneid)[0].value);

            if(document.getElementsByName('typearticle_' + ligneid)[0].value == 1) qtecave += qte;
            if(parseFloat(document.getElementsByName('poids3_' + ligneid)[0].value) > max) max = parseFloat(document.getElementsByName('poids3_' + ligneid)[0].value);
        }
    }

    document.getElementById('poidstotal3').innerHTML  = poids;
    document.getElementById('coutpoidssls').innerHTML = 'Poids ' + poids + 'kg';
    document.getElementById('valeurtotalsls').value          = totalarticles; //Valeur de la marchandise

    switch(installname())
    {
        case 'sls':
            
            document.getElementsByName('slscoutctrrembval')[0].value = 22; //Frais de contre remboursement
            document.getElementsByName('slscorsetotal')[0].value     = Math.round(poids * document.getElementById('prixkgcorse').value * 100) / 100; //Passage maritime corse
            document.getElementsByName('slscoutrepriseval')[0].value = 20; //Frais de reprise DEEE

            //Assurance complémentaire 1% de la valeur de la marchandise
            var cout_assurance = Math.round(totalarticles * 1) / 100;
            if(cout_assurance < 10) cout_assurance = 10;
            document.getElementsByName('slscoutassurval')[0].value   = cout_assurance; //Cout assurance (1% de la valeur de la marchandise)
            valeurrembourse = poids * 33; //Montant remboursé = 33€ par kg
            if(valeurrembourse > 1000) valeurrembourse = 1000; //Remboursement maximal fixé à 1000€
            document.getElementById('valeurremboursesls').value      = Math.round(valeurrembourse) + ' €'; //Montant du remboursement
            
            //Si la valeur remboursée est inférieure à la valeur total de la marchandise, on écrit le texte en rouge pour inciter l'ADV à cocher la case.
            if(valeurrembourse < totalarticles) document.getElementById('valeurremboursesls').style.color = 'red'; 

            document.getElementById('valeurremboursesls').style.color  = 'black';
            document.getElementsByName('enigmacoutassur')[0].value     = '';
            document.getElementsByName('enigmacoutassurval')[0].value  = 0;
            document.getElementById('trassurancesls').style.display    = 'table-row';
            document.getElementById('trcorse').style.display           = 'table-row';
            document.getElementById('trassuranceenigma').style.display = 'none';
            document.getElementById('trprise').style.display           = 'none';
            
            break;


        case 'globe':

            document.getElementsByName('slscoutassurval')[0].value = Math.round(totalarticles * 0.45) / 100;
            document.getElementsByName('slscorsetotal')[0].value   = Math.round(poids * document.getElementById('prixkgcorse').value * 100) / 100;
            document.getElementById('valeurtotalsls').value        = totalarticles;
            
            valeurrembourse = poids * 33;
            if(valeurrembourse > 1000) valeurrembourse = 1000;
            if(valeurrembourse < totalarticles) 
                document.getElementById('valeurremboursesls').style.color = 'red';
            else
                document.getElementById('valeurremboursesls').style.color = 'black';

            document.getElementById('valeurremboursesls').value        = Math.round(valeurrembourse);
            document.getElementsByName('enigmacoutassur')[0].value     = '';
            document.getElementsByName('enigmacoutassurval')[0].value  = 0;
            document.getElementById('trassurancesls').style.display    = 'table-row';
            document.getElementById('trcorse').style.display           = 'none';
            document.getElementById('trassuranceenigma').style.display = 'none';
            document.getElementById('trprise').style.display           = 'none';

            break;
            
        case 'JARDEL SERVICES':

            document.getElementsByName('slscoutassurval')[0].value = Math.round(totalarticles * 0.35) / 100;
            document.getElementsByName('slscorsetotal')[0].value   = Math.round(poids * document.getElementById('prixkgcorse').value * 100) / 100;
            document.getElementById('valeurtotalsls').value        = totalarticles;
            
            valeurrembourse = poids * 33;
            if(valeurrembourse > 1000) valeurrembourse = 1000;
            if(valeurrembourse < totalarticles) 
                document.getElementById('valeurremboursesls').style.color = 'red';
            else
                document.getElementById('valeurremboursesls').style.color = 'black';

            document.getElementById('valeurremboursesls').value        = Math.round(valeurrembourse);
            document.getElementsByName('enigmacoutassur')[0].value     = '';
            document.getElementsByName('enigmacoutassurval')[0].value  = 0;
            document.getElementById('trassurancesls').style.display    = 'table-row';
            document.getElementById('trcorse').style.display           = 'none';
            document.getElementById('trassuranceenigma').style.display = 'none';
            document.getElementById('trprise').style.display           = 'none';

            break;
    }

    adv_save(new Array('valeurtotalsls','slscoutassurval','slscorsetotal','enigmacoutassur','enigmacoutassurval','enigmaprise','enigmapriseval','slscoutrepriseval'));
}

function tab(tabname){
    var tabs=new Array('fourn','stock','trspt','sls','recap','garantie');
    for(i=0;i<tabs.length;i++){
        if(document.getElementById('tab'+tabs[i])) document.getElementById('tab'+tabs[i]).style.display='none';
        document.getElementById('tablink'+tabs[i]).className='tab_off';
        if(document.getElementById(tabs[i]+'_recap'))
        document.getElementById(tabs[i]+'_recap').style.display='none';
    }

    if(document.getElementById('tab'+tabname)) document.getElementById('tab'+tabname).style.display='table';
    document.getElementById('tablink'+tabname).className='tab_on';
    if(tabname=='recap'){
        for(i=0;i<tabs.length;i++) if(document.getElementById(tabs[i]+'_recap')) document.getElementById(tabs[i]+'_recap').style.display='table';
    }else
        if(document.getElementById(tabname+'_recap')) document.getElementById(tabname+'_recap').style.display='table';
}

function valid1(showpreview){ 
    error="";

    fourn=document.getElementsByName('fournisseur')[0].value;
    if(fourn=='') error="Il faut sélectionner un fournisseur";

    prod=false;
    for(i=0;i<document.getElementsByName('produits[]').length;i++){
        if(document.getElementsByName('produits[]')[i].checked) prod=true;
    }
    if(prod==false) error="Aucun produit n'est sélectionné";

    trspt=false;
    if(document.getElementsByName('trsptmode')[0].value!='') trspt=true;
    if(fourn=='0') trspt=true;  
    if(document.getElementsByName('typecmd')[0].value=='delete') trspt=true;
    if(trspt==false) error="Il faut sélectionner un mode de transport";
    
    if(error==""){
        if(showpreview==true){
            preview(form1);
        }else{
            document.forms.form1.action='facture.php?facturid=<?=$facturid; ?>&action=editer';
            document.forms.form1.target = "_self";
            document.forms.form1.submit();
        }
    }else
        alert(error);
}

function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        
    var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',

    toFixedFix = function(n, prec) {
        var k = Math.pow(10, prec);
        return '' + (Math.round(n * k) / k).toFixed(prec);
    };
        
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
        
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
        
    return s.join(dec);
}


function sumprod(){
    total=0;
    ecopart=0;
    gartotal=0;
    var fournisseur=document.getElementsByName('fournisseurname')[0].value;

    for(i=0;i<document.getElementsByName('produits[]').length;i++){
        if(document.getElementsByName('produits[]')[i].checked){
        pid=document.getElementsByName('produits[]')[i].value;
        var qte=document.getElementsByName('qte_'+pid)[0].value;
        total+=parseFloat(qte*document.getElementsByName('price1_'+pid)[0].value);
            if(document.getElementById('gar_'+pid+'['+fournisseur+']')) gartotal+=parseFloat(qte*document.getElementById('gar_'+pid+'['+fournisseur+']').value);
            //if(document.getElementsByName('gar_'+pid)[0].value==1) gartotal+=parseFloat(document.getElementsByName('caract_'+pid+'[]')[i2].checked+' '+document.getElementsByName('qte_'+pid)[0].value*document.getElementsByName('garval_'+pid)[0].value);

            var options=getElementsByRegExpName('^caract_'+pid+'+');
        //for(i2=0;i2<document.getElementsByName('caract_'+pid+'[]').length;i2++){
            for(i2=0;i2<options.length;i2++){
            caractvalid=options[i2].value;
            if(options[i2].value!='') total += parseFloat(qte*document.getElementsByName('caractval_'+pid+'['+caractvalid+']')[0].value);
            }

            ecopart+=parseFloat(qte*document.getElementsByName('ecopart_'+pid)[0].value);
        }
    }

    fourn=0;

    if(document.getElementsByName('trsptmode')[0].value=='parenlevement') fourn=0;
    if(document.getElementsByName('trsptmode')[0].value=='parvossoins') fourn=parseFloat(document.getElementsByName('mazetcout')[0].value);       
    if(isNaN(fourn)) fourn=0;
    if(devise == '') devises[devise] = '¤';

    var montant = number_format(total,2,',',' ');
    var montant_total = number_format(Math.round(100*(total+ecopart+fourn+gartotal))/100,2,',',' ');
    document.getElementById('prod1total').innerHTML = montant + '&nbsp;' + devises[devise];
    document.getElementById('prixprodtotal').innerHTML = montant + '&nbsp;' + devises[devise];
    document.getElementById('prixecoparttotal').innerHTML = number_format(ecopart,2,',',' ') +'&nbsp;'+devises[devise];
    document.getElementById('prixtrspttotal').innerHTML= number_format(fourn,2,',',' ') +'&nbsp;'+devises[devise];
    document.getElementById('prixgartotal').innerHTML= number_format(gartotal,2,',',' ') +'&nbsp;'+devises[devise];
    document.getElementById('prixfourntotal').innerHTML = montant_total +'&nbsp;'+devises[devise];
    document.getElementsByName('blslspj')[0].checked=false;

    if(document.getElementsByName('trsptmode')[0].value=='parenlevement'){
        document.getElementsByName('blslspj')[0].checked=(document.getElementsByName('chkenlev2sls')[0].value==1);
    }

    if(document.getElementsByName('trsptmode')[0].value=='parvossoins'){
        document.getElementsByName('blslspj')[0].checked=(document.getElementsByName('opttrspt')[2].checked || document.getElementsByName('opttrspt')[3].checked);
    }

    document.getElementsByName('blpj')[0].checked=!document.getElementsByName('blslspj')[0].checked;
}

function selectfournlivr(type){
    document.getElementById('parenlevement').style.display='none';
    document.getElementById('parvossoins').style.display='none';
    document.getElementById('intervparvossoins').style.display='none';
    if(document.getElementById(type)) document.getElementById(type).style.display='block';
    document.getElementById('tdparenlevement').style.display='none';
    document.getElementById('tdparvossoins').style.display='none';
    if(document.getElementById(type)) document.getElementById('td'+type).style.display='table-cell';
}

function trspttarif(){
    total = 0;

    if(document.getElementsByName('trsptcout')[0] && document.getElementsByName('trsptcout')[0].value == 1){
        total += parseFloat(document.getElementsByName('trsptcoutval')[0].value);
    }
        
    if(document.getElementsByName('trspt_ctr_remb')[0] && document.getElementsByName('trspt_ctr_remb')[0].value == 1){
        total += parseFloat(document.getElementsByName('ctr_remb_coutval')[0].value);
    }

    if(document.getElementsByName('priceoptnb')){
        var optnb = document.getElementsByName('priceoptnb')[0].value;
        for(i=0;i<optnb;i++){
            if(document.getElementsByName('chkopt['+i+']')[0].checked){
                total += parseFloat(document.getElementsByName('priceopt['+i+']')[0].value);
            }
        }
    }

    document.getElementById('totaltrspt').innerHTML = total+'&nbsp;'+devises[devise];
}

function totalassurance(){
    totalarticles = 0;
    totalpv       = 0;
    poidstotal    = 0;
    var volumetotal = 0;
        
    for(i=0;i<document.getElementsByName('produits2[]').length;i++){
        if(document.getElementsByName('produits2[]')[i].checked){
            ligneid = document.getElementsByName('produits2[]')[i].value;
            totalarticles += document.getElementsByName('qte2_'+ligneid)[0].value * (parseFloat(document.getElementsByName('price_'+ligneid)[0].value) + parseFloat(document.getElementsByName('ecopart2_'+ligneid)[0].value));

            totalpv       += document.getElementsByName('qte2_'+ligneid)[0].value*parseFloat(document.getElementsByName('pv_'+ligneid)[0].value);
            poidstotal    += document.getElementsByName('qte2_'+ligneid)[0].value*parseFloat(document.getElementsByName('poids_'+ligneid)[0].value);
            volumetotal   += document.getElementsByName('qte2_'+ligneid)[0].value*parseFloat(document.getElementsByName('volume_'+ligneid)[0].value);
        }
    }

    if(document.getElementById('poidstotal')) document.getElementById('poidstotal').innerHTML = Math.round(poidstotal);
    if(document.getElementById('valeurht')) document.getElementById('valeurht').innerHTML = Math.round(totalarticles*100)/100+'&euro;';
    if(document.getElementById('coutpoids')) document.getElementById('coutpoids').innerHTML = poidstotal+'kg';
    if(document.getElementById('volumetotal')) document.getElementById('volumetotal').innerHTML = Math.round(volumetotal*100)/100+'m<sup>3</sup>';
    if(document.getElementById('poidsvolume100')) document.getElementById('poidsvolume100').innerHTML = Math.round(volumetotal*10000)/100;
    if(document.getElementById('poidsvolume250')) document.getElementById('poidsvolume250').innerHTML = Math.round(volumetotal*25000)/100;
    if(document.getElementById('poidsvolume300')) document.getElementById('poidsvolume300').innerHTML = Math.round(volumetotal*30000)/100;

    if(document.getElementsByName('valeurdeclaree')[0]){
        document.getElementsByName('valeurdeclaree')[0].value = Math.round(totalarticles*100)/100;
        adv_save('valeurdeclaree');
    }
}

function setpaht(fkey,fdevise){
    devise=fdevise;
    sumprod(); // pour mettre à jour le prix de la garantie, à supprimer ensuite
    return false; // pour l'instant on garde le prix dans la commande (pour les prix temporaires)

    var select_fourn=fourn_paht[fkey];
    if(select_fourn==undefined) return;
    
    for(l in select_fourn){
        if(select_fourn.hasOwnProperty(l)){
            var field='price1_'+l
            if(document.getElementsByName(field)[0]){
                document.getElementsByName(field)[0].value=parseFloat(select_fourn[l]);
                adv_save(field);
            }    
        }
    }
    
    sumprod();
}
function verif_edi(){
    var post=new Array();
    post['advid']='<?=$adv->id; ?>';

    ajaxrequest('adv/check_edi_ajax.php',checkedi_callback,post);
}
function check_edi_callback(httpRequest){
    var xml = httpRequest.responseXML.documentElement;

    if(xml.getElementsByTagName("error")[0]){

        var lignes = xml.getElementsByTagName('error');
        
        for(f=0;f<lignes.length;f++){
            
        }

    }
}

function valid2(showpreview){
    prod=false;
    error="";

    for(i=0;i<document.getElementsByName('produits2[]').length;i++){
        if(document.getElementsByName('produits2[]')[i].checked) prod=true;
    }
    if(prod==false) error="Il faut sélectionner un produit";

    if(error==""){
        if(showpreview==true){
            preview();
        }else{
            document.forms.form2.action='facture.php?facturid=<?=$facturid; ?>&action=editer';
            document.forms.form2.target = "_self";
            document.forms.form2.submit();
        }
    }else
        alert(error);
}

function mail_trspt(fournisseur,efid){
    if(!confirm("Envoyer les consignes de livraison ?")) return;
    var url='facture.php?facturid=<?=$facturid; ?>&action=editer';
    var form=document.createElement("form");
    document.body.appendChild(form);
    form.action=url;
    form.method="POST";
    var input=document.createElement("input");
    input.type="hidden";
    input.name="mailname";
    input.value=fournisseur;
    form.appendChild(input);
    var input=document.createElement("input");
    input.type="hidden";
    input.name="action";
    input.value="mail_trspt";
    form.appendChild(input);
    if(efid!=undefined){
        var input=document.createElement("input");
        input.type="hidden";
        input.name="efid";
        input.value=efid;
        form.appendChild(input);
    }
    form.submit();
}

function nocmd(type){
    if(!confirm("Validez-vous le pas de cde ?")) return;
    var url="facture.php?facturid=<?=$facturid; ?>&action=editer#adv";
    var form=document.createElement("form");
    document.body.appendChild(form);
    form.action=url;
    form.method="POST";
    var input=document.createElement("input");
    input.type="hidden";
    input.name="type";
    input.value=type;
    form.appendChild(input);
    var input=document.createElement("input");
    input.type="hidden";
    input.name="nocmd";
    input.value=1;
    form.appendChild(input);
    form.submit();
}

</script>
