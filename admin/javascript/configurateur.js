$(window).resize(function(){
	if($('#start_conf').css('display') == 'block'){
		$('#helpconf_desk').hide();
		$('#newconf_desk').hide();
		$('#helpconf_tab').hide();
		$('#newconf_tab').hide();
		$(".etat_rappelconfig").hide();
	}else{
		if(window.innerWidth >1840 || (window.innerWidth < 1591 && window.innerWidth>1545)){
			$('#helpconf_desk').show();
			$('#newconf_desk').show();
			$(".etat_rappelconfig").hide();
		}else{
			$(".etat_rappelconfig_desk").hide();
			$('#helpconf_desk').hide();
			$('#newconf_desk').hide();
		}
		if((window.innerWidth < 1840 && window.innerWidth >1591) || (window.innerWidth < 1545)){
			$('#helpconf_tab').show();
			$('#newconf_tab').show();
			$(".etat_rappelconfig_desk").hide();
			if($('#titre_config').css('display') == 'block') {
				$('#titre_config').hide();
				$('#helpconf_tab').parent().removeClass().addClass('col-xs-5 col-sm-6 col-md-5 col-lg-4 no-padding');
			}else{
				$('.tab_bloc').removeClass().addClass('col-xs-12 tab_bloc');
			}
		}else{
			$('#helpconf_tab').hide();
			$('#newconf_tab').hide();
			$(".etat_rappelconfig_desk").hide();
		}
	}
});

$( document ).ready(function() {
	if($('#start_conf').css('display') == 'block'){
		$('#helpconf_desk').hide();
		$('#newconf_desk').hide();
		$('#helpconf_tab').hide();
		$('#newconf_tab').hide();
		$(".etat_rappelconfig").hide();
		
	}else{
		if(window.innerWidth >1840 || (window.innerWidth < 1591 && window.innerWidth>1545)){
			$('#helpconf_desk').show();
			$('#newconf_desk').show();
			$(".etat_rappelconfig").hide();
		}
		if((window.innerWidth < 1840 && window.innerWidth >1591) || (window.innerWidth < 1545)){
			$('#helpconf_tab').show();
			$('#newconf_tab').show();
			$(".etat_rappelconfig_desk").hide();
		}
	}
	if(window.innerWidth <768){
		$(".configurateur").parents('.container').css("padding", "0");
	}
});

var cur_resp_nb=0;

function start(){
	document.getElementById('start_conf').style.display="none";

	if(window.innerWidth >1840 || (window.innerWidth < 1591 && window.innerWidth>1545)){
		$('#helpconf_desk').show();
		$('#newconf_desk').show();
		$('#titre_config').show();
		$(".etat_rappelconfig").hide();
		
	}
	if((window.innerWidth < 1840 && window.innerWidth >1591) || (window.innerWidth < 1545)){
		$('#helpconf_tab').show();
		$('#newconf_tab').show();
		$(".etat_rappelconfig_desk").hide();
		$('#titre_config').hide();
		$('.tab_bloc').removeClass().addClass('col-xs-12 tab_bloc');
	}

	var configtype=document.getElementById('configtype').value;
	next("start",configtype);

}

function reload_config(confid){
	//wait_config();
	document.getElementById('start_conf').style.display="none";
    //affichage des diffÃ©rents elements "besoin aide", "nouvelle config" en fonction de la taille de la fenetre
    if(window.innerWidth >1840 || (window.innerWidth < 1591 && window.innerWidth>1545)){
    	document.getElementById('helpconf_desk').style.display="block";
    	document.getElementById('newconf_desk').style.display="block";
    	$(".etat_rappelconfig").hide();
    	$('#titre_config').show();
    }
    if((window.innerWidth < 1840 && window.innerWidth >1591) || (window.innerWidth < 1545)){
    	$('#titre_config').hide();
    	$('.tab_bloc').removeClass().addClass('col-xs-12 tab_bloc');
    	$('#helpconf_tab').parent().removeClass().addClass('col-xs-5 col-sm-6 col-md-5 col-lg-4 no-padding');
    	$('#helpconf_tab').show();
    	$('#newconf_tab').show();
    	$(".etat_rappelconfig_desk").hide();
    }
//console.log('reload');
    var url="/ajax/configurateur_ajax.php?rep=reload&value="+confid;
    ajaxrequest(url,display_configurateur);
}
function next_ident(value){
	//console.log('next_ident');
	var url="/ajax/configurateur_ajax.php?rep=ident&value="+value;
	ajaxrequest(url,display_ident2);
}
function display_ident2(httprequest){
	//document.getElementById('wait').style.display="none";
	
	var xml=httprequest.responseXML.documentElement;
	var error=xml.getElementsByTagName('error')[0];
	var html="";
	switch(error.firstChild.nodeValue){
		case "":
		if(xml.getElementsByTagName('html')[0].firstChild)	html=xml.getElementsByTagName('html')[0].firstChild.nodeValue;
		if(html!=""){
				var div=document.createElement('div');
				div.id="rep_ident";
				div.innerHTML=html;
				document.getElementById('configurateur').appendChild(div);
				var resultats_nb_conf = document.getElementById('tout_nb').innerHTML;
				$(".resultats_nb_conf").first().text(resultats_nb_conf);

				//jQuery('body,html').animate({scrollTop : jQuery('#rep_ident').offset().top-100}, 500);
			}
			break;

			default:
			document.getElementById('ident_error').style.display="block";
			document.getElementById('ident_error').innerHTML=error.firstChild.nodeValue;
		}  	
	}

	function next(rep,value){
		var url="/ajax/configurateur_ajax.php?rep="+rep+"&value="+value;
		ajaxrequest(url,display_configurateur);
	}

	function display_configurateur(httprequest){



		/*if(document.getElementById('wait') !=undefined){
			document.getElementById('wait').style.display="none";
		}*/
		var xml=httprequest.responseXML.documentElement;

		var html="";
		if(xml.getElementsByTagName('html')[0].firstChild)
			html=xml.getElementsByTagName('html')[0].firstChild.nodeValue;
		var nb=parseInt(xml.getElementsByTagName('nb')[0].firstChild.nodeValue);
		if(nb>0){
			if(nb<=cur_resp_nb){
			for(i=nb;i<=cur_resp_nb;i++){ //si on reviens en arrriÃ¨re on supprime les div des rep suivantes
				var rmdiv=document.getElementById('rep'+i);
				if(rmdiv) document.getElementById('configurateur').removeChild(rmdiv);
			}
			//si on avait crÃ©Ã© la div pour l'identification, on la supprime en cas de retour en arriÃ¨re
			var rmident = document.getElementById('rep_ident');
			if(rmident) document.getElementById('configurateur').removeChild(rmident);
		}
		var prevrep=document.getElementById('rep'+(nb-2));
		if(prevrep){
			var ul=prevrep.getElementsByTagName("ul")[0];
			var ulheight=ul.offsetHeight;
			ul.style.display="none";
			var divs=prevrep.getElementsByTagName('div');
			
			for(i=0;i<divs.length;i++){
				
				if(divs[i].className=="close"){
					divs[i].className="open";
					var txt=divs[i].getElementsByTagName('div')[0];
					txt.innerHTML=divs[i].getAttribute('data-open');
				}
			}
			prevrep.querySelector(".reponse").style.display="inline";
			var scrolltop=document.documentElement.scrollTop;
			  //document.documentElement.scrollTop=scrolltop-ulheight;
			}
		//console.log('nb : '+nb);
		
		$('#rep'+(nb-1)+' .nextbtn').hide();
		
		if ($('#rep'+(nb-1)+' .nextbtn').length){
			//console.log('ca existe...');

		}
		
		if(html!=""){
			var div=document.createElement('div');
			div.id="rep"+nb;
			div.innerHTML=html;
			
			document.getElementById('configurateur').appendChild(div);
			//window.scrollTo(0,div.offsetTop);
			//ss.scrollToDiv("rep"+nb,mobile?0:-100);
			if($("#rep2").css('display') != 'block') {
				$(window).scrollTop(0);	
			}
			else {
				
				cur_resp_nb=nb;
			}
		}
	}

		// ferme la question du dessus 
		if(nb>1) {
			var nbprev = nb - 1;
			$("#rep"+nbprev).find("ul").css("display","none");
		}


	var questions=xml.getElementsByTagName("question");

	if(document.getElementById('rep'+(nb-1))) {
		var prevrep=document.getElementById('rep'+(nb-1));

		var divs=prevrep.getElementsByTagName('div');
		for(j=0;j<divs.length;j++){
			if(divs[j].className=="close"){
				divs[j].className="open";
				var txt=divs[j].getElementsByTagName('div')[0];
				txt.innerHTML=divs[j].getAttribute('data-open');
			}
		}
		prevrep.querySelector(".reponse").style.display="inline";
		var texte_rep = prevrep.querySelector(".reponse").innerHTML.toLowerCase();
		var texte_rep = texte_rep.replace(/ /g, '_');
		var texte_quest = prevrep.querySelector(".reponse").getAttribute('id');
		var texte_quest = texte_quest.replace(/^rep/, '');
	}

	if(questions.length>0){
		
		for(i=0;i<questions.length;i++){
			var nb=questions[i].getAttribute('nb');
			var visible=questions[i].getAttribute('visible');
			var divexist=document.getElementById('rep'+nb);
			
			if(divexist) divexist.parentNode.removeChild(divexist);
			var div=document.createElement('div');
			div.id="rep"+nb;
			if(visible=="0") div.style.display="none";
			div.innerHTML=questions[i].firstChild.nodeValue;
			
			
			document.getElementById('configurateur').appendChild(div);

			var lilist=div.getElementsByTagName('li');
			var rep="";
			for(j=0;j<lilist.length;j++){
				if(lilist[j].className.search(/clicked/i)>-1 && lilist[j].onclick!=undefined){
					var h3=lilist[j].getElementsByTagName('h3')[0];
					rep+=(rep!=""?", ":"")+h3.innerHTML.replace(/(<([^>]+)>)/ig,"");
				}
			}

			var lastone=(i==questions.length-1);
			var question=div.getElementsByTagName('div')[0];
			var qdiv=question.getElementsByTagName('div');
			var reponse=false;
			for(j=0;j<qdiv.length;j++){ 
				
				if(qdiv[j].className=="reponse") reponse=qdiv[j];
				if(qdiv[j].className=="close" && !lastone){
					qdiv[j].className="open";
					qdiv[j].getElementsByTagName('div')[0].innerHTML=qdiv[j].getAttribute('data-open');
				}
			}
			
						if(reponse){
				reponse.innerHTML="";
				var w=0;
				for(i2=0;i2<question.children.length;i2++)
					if(question.children[i2].className!=reponse) w+=question.children[i2].offsetWidth;
				reponse.style.width=(question.offsetWidth-w-52)+'px';
				reponse.innerHTML=rep;
				if(!lastone) reponse.style.display="inline";
			}

			if(!lastone){
				var ul=div.getElementsByTagName('ul')[0];
				ul.style.display="none";
			}

			cur_resp_nb=nb;
		}

		var scrolldiv="rep"+(visible=="0"?cur_resp_nb-1:cur_resp_nb);

	}
	var articles_selection=xml.getElementsByTagName('selection')[0];
	var articles_selection_count=articles_selection.getAttribute("count");
	var articles_total=xml.getElementsByTagName('total')[0];
	var articles_total_count=articles_total.getAttribute("count");
    //document.getElementById('selection_nb').style.fontSize=(articles_selection_count>=1000?30:40)+'px';
    //document.getElementById('selection_nb').innerHTML=articles_selection_count;
    //document.getElementById('selection_nb2').innerHTML=articles_selection_count;
    document.getElementById('tout_nb').innerHTML=articles_total_count;
    var resultats_nb_conf = document.getElementById('tout_nb').innerHTML;
    
    ////tache 7093 : mise en place du Datalayer
    var reponse1 = $("#rep1").last().find("ul").find("li.clicked").find("input").attr("id");
    if(nb-1 === 1){
    	window.dataLayer = window.dataLayer || [];
    	window.dataLayer.push({
    		'event': 'config_start',
    		'config_choix_produit': texte_rep,
    		'config_cave_results': resultats_nb_conf
    	});
    }
    else{
    	datalayer(reponse1,texte_quest,texte_rep,resultats_nb_conf);
    }


	$("#barresults").find(".resultats_nb_conf").text(resultats_nb_conf);

    var display=articles_selection.getAttribute('display'); 
    switch(display){
    	case "0":
    	document.getElementById('results').style.display=(articles_total_count>0?"block":"none");
    	document.getElementById('resultstart').style.display=(articles_total_count==0?"block":"none");
    	document.getElementById('result_choix').style.display="none";
    	document.getElementById('result_trop').style.display="block";
	//document.getElementById('result_ident').style.display="none";
	document.getElementById('products').style.display="none";
	break;

	case "ok":
	document.getElementById('resultstart').style.display="none";
	document.getElementById('results').style.display="block";
	document.getElementById('result_trop').style.display="none";
	document.getElementById('result_choix').style.display="none";
	//document.getElementById('result_ident').style.display="block";
	//document.getElementById('selection_btn2').setAttribute("onclick","show_products()");
	document.getElementById('products').style.display="none";

	hide_questions();
	break;

	case "msg":
	document.getElementById('resultstart').style.display="none";
	document.getElementById('results').style.display="block";
	document.getElementById('result_trop').style.display="none";
	//document.getElementById('result_choix').style.display="block";
	next_ident(0);
	//document.getElementById('result_ident').style.display="none";
	document.getElementById('products').style.display="none";
	/*if(articles_selection.firstChild.nodeValue+articles_total.firstChild.nodeValue!="")
	    document.getElementById('selection_btn').setAttribute("onclick","show_products()");
	else
		document.getElementById('selection_btn').setAttribute("onclick","next_ident(0)");*/
	hide_questions();
	break;

	case "ident":
	if(document.getElementById('rep_ident') == undefined) next_ident(0);
	document.getElementById('resultstart').style.display="none";
	document.getElementById('results').style.display="none";
	document.getElementById('result_choix').style.display="none";
	document.getElementById('result_trop').style.display="none";
	//document.getElementById('result_ident').style.display="block";
	//document.getElementById('selection_btn2').setAttribute("onclick","next_ident(0)");
	document.getElementById('products').style.display="none";
	
	hide_questions();
	break;

	case "results":

	document.getElementById('resultstart').style.display="none";
	document.getElementById('results').style.display="none";
	document.getElementById('products').style.display="block";
	hide_questions();
	break;
}


if((articles_selection.firstChild.nodeValue!="" || articles_total.firstChild.nodeValue!="")){
	var articles_selec_firstchild = articles_selection.firstChild.nodeValue;
	var articles_total_firstchild = articles_total.firstChild.nodeValue;

		$("#barresults").show();
		$("#barresults_ident").hide();
		var resultats_nb_conf = document.getElementById('tout_nb').innerHTML;
		$("#barresults").find(".resultats_nb_conf").text(resultats_nb_conf);

	if(articles_selec_firstchild!=""){
		$('#products_selection').html(articles_selec_firstchild);
	  //document.getElementById('onglet_selection').style.display="block";
	$('.lazy').lazy();
	}else{
		document.getElementById('products_selection').innerHTML="";
	  //document.getElementById('onglet_selection').style.display="none";
	  show_results("total");
	}
	
	if(articles_total.firstChild.nodeValue!=""){
		document.getElementById('products_total').innerHTML=articles_total.firstChild.nodeValue;
		document.getElementById('onglet_total').style.display="block";
	}else{
		document.getElementById('products_total').innerHTML="";
		document.getElementById('onglet_total').style.display="none";
		show_results("selection");
	}
	

	var iWindowsSize = $(document).width();
	//Rollover sur vignette de la page config
	if(iWindowsSize > 974 ){
		$('.vignette').mouseenter(function(){
			$(this).find('img.visuel').css('display','none');
			$(this).find('.categorie_hover').show();
			$(this).find('.vfimg').hide();
		}).mouseleave(function(){
			$(this).find('img.visuel').css('display','block');
			$(this).find('.categorie_hover').hide();
			$(this).find('.vfimg').show();
		});
	}


// S'il n'y a plus qu'un produit dans les résultats avant d'affiner, on n'affiche pas le bloc "affiner votre recherche"
	if($(".choix_criteres").find(".libellesuppr").css('display') === 'block' && $("#products_selection").find(".vignette").length < 2) {
		$("#conf_affinage").show();
	}
	else if($(".bloc-critereaffin").find(".choix_criteres")) {
		$("#conf_affinage").show();
	}
	else {
		$("#conf_affinage").hide();
	}

	if($(".choix_criteres").find(".libellesuppr").css('display') === 'none') {
		$(".reinitialiser").hide();
	}
	else {
		$(".reinitialiser").show();
	}

	var options_affinage=xml.getElementsByTagName('affinage')[0];
	if(options_affinage && options_affinage.firstChild.nodeValue!=""){
		document.getElementById('conf_affinage').innerHTML=options_affinage.firstChild.nodeValue;

		$(".ligne").find(".affine").find(".clicked").each(function() {
			var selec_critere = $.trim($(this).text());
			var idname = $(this).find("input").attr('id');
			var name = $(this).find("input").attr('name');

			if(selec_critere != "" && idname != 'colorisencastrable0' && idname != 'coloris0' && idname != 'origine1000' && idname != 'marque0' && idname != 'prix1000' && idname != 'sensporte1000' && idname != 'serrure1000') {	
				$(".bloc-critereaffin").show();
				$("#affin_"+name).css('background-color', '#DCD1BE');
				$("#affin_"+name).find('.fleche').css('color', 'black');
				$(".choix_criteres").append("<div class='libellesuppr suppcrit_"+idname+"' style='font-weight: normal;font-size: 12px'>"+selec_critere+"<img src='/images/templates/croix.png' alt='delete' onclick=\"supp_critere_affin('"+idname+"', '"+name+"')\"></div>");
				
			}
		});


	   /* var questions=document.getElementById('questions_affines').getElementsByTagName('div');
	   // console.log(questions);
	    for(i=0;i<questions.length;i++){
		 if(questions[i].className.match(/question/)){
		      var ul=questions[i].nextElementSibling;
		      var lilist=ul.getElementsByTagName('li');
		      var rep="";
		      for(j=0;j<lilist.length;j++){
			  if(lilist[j].className.search(/clicked/i)>-1 && lilist[j].onclick!=undefined){
			      var h3=lilist[j].getElementsByTagName('h3')[0];
			      rep+=(rep!=""?", ":"")+h3.innerHTML.replace(/(<([^>]+)>)/ig,"");
			  }
		      }
		      
		      var reponse=false;
		      var qdiv=questions[i].getElementsByTagName('div');
		     // console.log(qdiv);
		      for(j=0;j<qdiv.length;j++){
				  if(qdiv[j].className=="reponse") reponse=qdiv[j];
				  if(qdiv[j].className=="open" && ul.className=="affine"){
				      qdiv[j].className="close";
				      qdiv[j].getElementsByTagName('div')[0].innerHTML=qdiv[j].getAttribute('data-close');
				  }
		      }
		      if(reponse) reponse.innerHTML=rep;
		 }
		} */
	}
}

if(nb > 1) {
	setTimeout(function(){
		var nbprev = nb - 1;
		jQuery('body,html').animate({scrollTop : jQuery('#rep'+nbprev).offset().top}, 2000);
	});
}

var preview=xml.getElementsByTagName('preview')[0];
if(preview.firstChild.nodeValue!="")
	document.getElementById('result_img').innerHTML=preview.firstChild.nodeValue;

//     document.getElementById('new_conf').style.display="block";
//     document.getElementById('avis').style.display="block";

var pageview=xml.getElementsByTagName('pageview')[0];
if(pageview.firstChild && pageview.firstChild.nodeValue!="")
	if(typeof(ga) === "function"){
		ga('send', 'pageview', "/"+pageview.firstChild.nodeValue);
	}

var error=xml.getElementsByTagName('error')[0];
    $('.prop').children('.clear').remove(); //retirÃ© car provoque un mauvais placement des balises li en flex. Plus simple que de revoire reprendre tous les fichiers php !
    $('#products_selection').children('.clear').remove();
    $('#products_selection').css({'display': '-ms-flexbox','display': '-webkit-flex','display': 'flex'});
//     if(error.firstChild)
//       console.debug(error.firstChild.nodeValue);
}

/*function wait_config(){
	var wdiv=document.getElementById('wait');
	if(!wdiv){
		var wdiv=document.createElement('div');
		wdiv.id="wait";
		var wimg=document.createElement('img');
		wimg.src="/images/templates/wait.gif";
		wdiv.appendChild(wimg);
		document.getElementById('configurateur').appendChild(wdiv);
	}
	document.getElementById('configurateur').removeChild(wdiv);
	document.getElementById('configurateur').appendChild(wdiv);
	wdiv.style.display="block";

	if(document.getElementById('conf_affinage') && document.getElementById('conf_affinage').innerHTML!=""){
		var wdiv2=wdiv.cloneNode(true);
		wdiv.remove();
		wdiv2.id="wait2";
		document.getElementById('conf_affinage').appendChild(wdiv2);
	}
}*/

function show_elements(elements,state){
	for(i=0;i<elements.length;i++)
		if(document.getElementById(elements[i])){
			document.getElementById(elements[i]).style.display=(state?"block":"none");
			document.getElementById(elements[i]).classList.remove("hidden");
		}
	}

	function showdim(state){
		document.getElementById("lilarg").className="li5 clicked"+(state?"":" hidden");
		document.getElementById("lihaut").className="li5 clicked"+(state?"":" hidden");
		document.getElementById("liprof").className="li5 clicked"+(state?"":" hidden");
		document.getElementById("nextdim").style.display=(state?"block":"none");
		if(document.getElementById("arrowdim"))
			document.getElementById("arrowdim").style.display=(state?"block":"none");
	}

	function return_installtemp(){
		var temp=new Array();
		temp[0]=1000;
		temp[1]=1000;
		if(!document.getElementById('mintemp0').checked){
			var s=document.getElementById('mintemp')
			temp[0]=s.options[s.selectedIndex].value;
		}
		if(!document.getElementById('maxtemp0').checked){
			var s=document.getElementById('maxtemp')
			temp[1]=s.options[s.selectedIndex].value;
		}
		return temp.join(',');
	}

	function selectdim(sender){
		var type=sender.id.substr(0,4);
		var valtype=sender.id.substr(4);
		if(valtype=="0"){
			var disabled=sender.checked;
			document.getElementById(type+"minval").disabled=disabled;
			document.getElementById(type+"maxval").disabled=disabled;
		}
	}

	function returndim(){
		var types=new Array("larg","haut","prof");
		var retarr=new Array();
		for(i=0;i<types.length;i++){
			if(document.getElementById(types[i]+"0").checked || document.getElementById("dim1").checked){
				retarr.push("");
				retarr.push("");
			}else{
				retarr.push(document.getElementById(types[i]+"minval").value.replace(/^\s+|\s+$/gm,''));
				retarr.push(document.getElementById(types[i]+"maxval").value.replace(/^\s+|\s+$/gm,''));
			}
		}
		return retarr.join(",");
	}

	function return_checkboxes(name){
		var retarr=new Array();
		var chk=document.getElementsByName(name);
		if(chk){
			for(i=0;i<chk.length;i++){
				if(chk[i].checked) retarr.push(chk[i].value);
			}
		}
		return retarr.join(',');
	}

	function uncheck(name,except){
		var chk=document.getElementsByName(name);
		if(chk){
			for(i=0;i<chk.length;i++)
				if(chk[i]!=except) chk[i].checked=false;
		}
	}

	function show_next(name){
		var display="none";
		var chk=document.getElementsByName(name);
		if(chk){
			for(i=0;i<chk.length;i++){
				if(chk[i].checked) display="block";
			}
		}
		document.getElementById("next"+name).style.display=display;
	}

	function clicklichoix(li) {

		/// quand on clique sur une valeur, si "je ne sais ou peu importe" était cochée, elle se décoche automatiquement 
		var liparent = $("#"+li).attr("name"); 
		if($("#"+li).parent().parent().find("#"+liparent+"_all").hasClass("clicked")) {
    		$("#"+li).parent().parent().find("#"+liparent+"_all").removeClass("clicked");
    		$("#"+li).parent().parent().find("#"+liparent+"_all").children().prop('checked',false);
			$("#"+li).parent().parent().find("#"+liparent+"_all").children().removeAttr('checked');		
    	}
		//

		if($("#"+li).is(':checked')) {
			$("#"+li).prop('checked',false);
			$("#"+li).removeAttr('checked');
			$("#"+li).parent().removeClass('clicked');
		}
		else {
			$("#"+li).prop('checked',true);
			$("#"+li).parent().addClass('clicked');
		}
	}

	function clickli(li,input,close){
		var ul=li.parentNode;
		var lis=ul.getElementsByTagName('li');
		var inp=document.getElementById(input);

		var reponse=document.getElementById('rep'+inp.name);

		if((window.innerWidth < 1840 && window.innerWidth >1591) || (window.innerWidth < 1545)){
			if($('#titre_config').css("display") == "block"){
				$('#titre_config').hide();
				$('.tab_bloc').removeClass().addClass('col-xs-12 tab_bloc');
				$('#helpconf_tab').parent().removeClass().addClass('col-xs-5 col-sm-6 col-md-5 col-lg-4 no-padding');
				$('#newconf_tab').parent().removeClass().addClass('col-xs-7 col-sm-5 col-md-4 col-lg-3');
				$('#newconf_tab').show();
			}
		}
		for(i=0;i<lis.length;i++){
			var classlist=lis[i].className;
			lis[i].className=classlist.replace("clicked","").replace(/^\s+|\s+$/gm,'');
		}
		switch(inp.type){
			case "radio":
			var classlist=li.className;
			li.className=classlist+" clicked";
			var h3=li.getElementsByTagName('h3')[0];
			var rep=h3.innerHTML.replace(/(<([^>]+)>)/ig,"");
			break;

			case "checkbox":
			var rep="";
			var icon;
			var classicon="";
			for(i=0;i<lis.length;i++){
				var liinp=lis[i].getElementsByTagName('input')[0];
				icon=lis[i].getElementsByClassName('fa-square')[0];
				classicon="far fa-check-square";
				if(icon==undefined){
					icon=lis[i].getElementsByClassName('fa-check-square')[0];
					classicon="far fa-square";
				}
				var classlist=lis[i].className;

				if(liinp.checked){
					lis[i].className=classlist+" clicked";
					if(icon!=undefined){
						icon.className=classicon;
					} 
					var h3=lis[i].getElementsByTagName('h3')[0];
					rep+=(rep!=""?", ":"")+h3.innerHTML.replace(/(<([^>]+)>)/ig,"");
				}
			}
			break;
		}

		if(close==undefined) close=true;
		close=false;

		if(inp){

			var line=reponse.parentNode;
			if(reponse){
				reponse.innerHTML="";
				var w=0;
				for(i=0;i<line.children.length;i++)
					if(line.children[i]!=reponse) w+=line.children[i].offsetWidth;
				reponse.style.width=(line.offsetWidth-w-52)+'px';
				reponse.innerHTML=rep;
			}
			if(close){
				var btn=document.getElementById('btn'+inp.name);
				if(btn) btn.className="open";

				li.parentNode.style.display="none";
			}
		
			inp.click();
		}
	}

	function clickbtn(btn,name,value){
		next(name,value);
//     btn.parentNode.style.display="none";
}

function show_question(question){
	var parent=document.getElementsByName(question)[0].parentNode;
	while(parent.nodeName!="UL")
		parent=parent.parentNode;

	var display=parent.style.display;
	if(window.innerWidth<768){
		var btn=document.getElementById('btnresp'+question);
		var lesclass = ' col-xs-1 visible-xs no-padding';
	}else{
		var btn=document.getElementById('btn'+question);
		var lesclass=''
	}
	var txt=btn.getElementsByTagName('div')[0];
	var reponse=btn.nextElementSibling;
	if(display.indexOf('flex') != -1 || display == '') {
		parent.style.display="none";
		btn.className="open"+lesclass;
		txt.innerHTML=btn.getAttribute('data-open');
		if(reponse) {
			reponse.style.display="block";
			reponse.classList.remove("hidden");
		}
	} else {
		//parent.style.display="block";
		$(parent).css({'display': '-ms-flexbox','display': '-webkit-flex','display': 'flex'}).removeClass("hidden");
		btn.className="close"+lesclass;
		txt.innerHTML=btn.getAttribute('data-close');
		if(reponse)
			reponse.style.display="none";
	}
}

function hide_questions(){
	
	var questions=document.getElementById('configurateur').getElementsByTagName('div');
	for(i=0;i<questions.length;i++){
		if(questions[i].id.match(/rep[0-9]+/) && questions[i].firstChild.className.match(/question/)){
			var question=questions[i].firstChild.getAttribute('onclick').match(/show_question\('(.+)'\)/)[1];
			var ul=questions[i].getElementsByTagName('ul')[0];
			ul.style.display="none";
			$('#btn'+question).addClass('open');
			$('#btn'+question).first().next().html($('#btn'+question).attr('data-open'));
		}
	}
}

function show_results(type){
    //document.getElementById('onglet_selection').className=(type=="selection"?"clicked":"");
    document.getElementById('onglet_total').className=(type=="total"?"clicked":"");
    if(type=="selection"){
    	$('#products_selection').show();
    }
    else {
    	$('#products_selection').hide();
    }
    document.getElementById('products_total').style.display=(type=="total"?"block":"none");
}

function show_products(){
	document.getElementById('results').style.display="none";
	document.getElementById('products').style.display="block";
	document.getElementById('cta_config_bloc').style.display="block";

	$('.lazy').lazy();
}

function show_invisible_div(){
	for(i=1;i<=cur_resp_nb;i++){
		document.getElementById('rep'+i).style.display="block";
		// $('#rep'+i).css({'display': '-ms-flexbox','display': '-webkit-flex','display': 'flex'});
		if(i==cur_resp_nb){
		    // var ul=document.getElementById('rep'+i).getElementsByTagName('ul')[0];
		    // ul.style.display="block";
		    $('#rep'+i).find('ul').first().css({'display': '-ms-flexbox','display': '-webkit-flex','display': 'flex'});
		}
	}
}

function configurateur_contact(){
	var post=new Array();
	post['contact']=1;
	var inputs=document.getElementById('tbl_contact').getElementsByTagName('input');
	for(i=0;i<inputs.length;i++){
		post[inputs[i].name]=inputs[i].value;
	}
	var inputs=document.getElementById('tbl_contact').getElementsByTagName('textarea');
	for(i=0;i<inputs.length;i++){
		post[inputs[i].name]=inputs[i].value;
	}
	var inputs=document.getElementById('tbl_contact').getElementsByTagName('select');
	for(i=0;i<inputs.length;i++){
		post[inputs[i].name]=inputs[i].options[inputs[i].selectedIndex].value;
	}

	//wait_config();
	ajaxrequest("/ajax/configurateur_ajax.php",display_contact,post);
}

function display_contact(httprequest){
	//document.getElementById('wait').style.display="none";
	var xml=httprequest.responseXML.documentElement;

	var error=xml.getElementsByTagName('error')[0].firstChild.nodeValue;
	if(error=="ok"){
		document.getElementById('tbl_contact').style.display="none";
		document.getElementById('contact_ok').style.display="block";
	}else{
		document.getElementById('contact_error').style.display="block";
		var verif=["nom","expediteur","telephone","message"];
		for(i=0;i<verif.length;i++){
			switch(document.getElementsByName(verif[i])[0].tagName){
				case "SELECT":
				var select=document.getElementsByName(verif[i])[0];
				var value=select.options[select.selectedIndex].value;
				break;
				case "INPUT":
				case "TEXTAREA":
				var value=document.getElementsByName(verif[i])[0].value;
				break;
			}
			if(value=="") document.getElementsByName(verif[i])[0].style.borderColor='red';
		}
	}
}
function Gclid(){
	document.getElementById('gclid1').value = document.getElementById('gclid_field').value ;
}

function identification(){
	var post=new Array();
	post['action']="logging";
	post['mail_logging']=document.getElementById('email_log').value;
	post['pass_logging']=document.getElementById('pass_logging').value;
	post['gclid1'] = document.getElementById('gclid1').value;
	post['redirection'] = document.getElementById('redirection').value;
   ajaxrequest("/ajax/configurateur_ajax.php",display_ident,post);
}


function verifPassword(){
	var email=document.getElementById('email_log').value;
	var password=document.getElementById('pass_logging').value;
	var erreurmail = document.getElementById('errormail').value;
	var erreurmdp = document.getElementById('errorpassword').value;
	$.ajax({
		url : '/ajax/checkPassword.php',
		type : 'GET',
		data : 'password='+password+'&email='+email,
		       dataType : 'html', 
		       success : function(code_html, statut){
		       	if(code_html === 'no'){
		       		$(".errormsg").text(erreurmail);
		       	}
		       	else if(code_html === 'nopassword'){
		       		$(".errormsg").text(erreurmdp);
		       	}else{
		           	document.getElementById('gclid').value = document.getElementById('gclid_field').value;//gestion du gclid
		           	identification();
		           }
		       }
		   });
}

function identificationResponsive(){
	var post=new Array();
	post['action']="logging";
	post['mail_logging']=document.getElementById('email3').value;
	post['pass_logging']=document.getElementById('mdp3').value;
	ajaxrequest("/ajax/configurateur_ajax.php",display_ident2,post);
}

function inscription(){
	var pro ="";
	if(document.getElementById('propart').value == '2') {
		pro = "pro";
	}
	var post=new Array();
	post['action']="logging";
	post['action2']="inscription2";
	post['propart']=document.getElementById("propart").value;
	var civ=document.getElementsByName('civ'+pro);
	var civilite=0;
	for(i=0;i<civ.length;i++)
		if(civ[i].checked) civilite=civ[i].value;
	post['civilite'+pro]=civilite;
	post['nom'+pro]=document.getElementById('nom'+pro).value;
	post['prenom'+pro]=document.getElementById('prenom'+pro).value;
	post['mail']=document.getElementById('mail').value;
	post['adresse'+pro]=document.getElementById('adresse'+pro).value;
	post['codepostal'+pro]=document.getElementById('codepostal'+pro).value;
	post['ville'+pro]=document.getElementById('ville'+pro).value;
	post['passwordinscrconfirm']=post['passwordinscr']=document.getElementById('passwordinscr').value;
	post['telephone'+pro]=document.getElementById('telephone'+pro).value;
	post['paysid'+pro]=document.getElementById('paysid'+pro).value;
	post['raisonsociale']=document.getElementById('raisonsociale').value;
	post['pordiv'+pro]=document.getElementById('pordiv'+pro).value;
	post['tvaintracom']=document.getElementById('tvaintracom').value;
	post['gclid']=document.getElementById('gclid').value; 
	if(document.getElementById('newsletter'))
		post['news_mail']='';
	post['news_sms'] = '';
	if(document.getElementsByName('news_sms')[0].checked){
		post['news_sms'] = document.getElementById('s1').value;
	}else{
		post['news_sms'] =document.getElementById('s0').value;
	}
	if(document.getElementsByName('news_mail')[0].checked){
		post['news_mail'] = document.getElementById('s1').value;
	}else{
		post['news_mail'] =document.getElementById('s0').value;
	}
	////tache 7093 : mise en place du Datalayer
	var type = 'professionnel';
	var newsmail = 'non';
	var newssms = 'non';
	if(post['raisonsociale'] == ''){
		type = 'particulier';
	}
	if(post['news_mail'] === '1'){
		newsmail = 'oui';
	}
	if(post['news_sms'] === '1'){
		newssms = 'oui';
	}
	window.dataLayer = window.dataLayer || [];
	window.dataLayer.push({
		'event': 'compte_creation_validation',
		'compte_type': type,
		'compte_creation_origine': 'configurateur',
		'compte_newsletter_mail': newsmail,
		'compte_newsletter_sms': newssms
	});
	////fin
	ajaxrequest("/ajax/configurateur_ajax.php",display_ident,post);
}

function display_ident(httprequest){

	var xml=httprequest.responseXML.documentElement;
	var error=xml.getElementsByTagName('error')[0];

	if(error.firstChild){
		switch(error.firstChild.nodeValue){

			case "":
			document.getElementById('ident_page').style.display="none";

			//var scrolltop=document.documentElement.scrollTop;
			//document.documentElement.scrollTop=scrolltop-100;
			var url = window.location.href;
			if(url.match(/(pro\.)/)){
				datalayer('connexion','professionnel',null,null);
			}
			else{
				datalayer('connexion','particulier',null,null);
			}
			display_configurateur(httprequest);
			break;

			default:
			document.getElementById('ident_error').style.display="block";
			document.getElementById('ident_error').innerHTML=error.firstChild.nodeValue;
		}
	}
}

function show_register(state){
	if(state==undefined) state=true;
	document.getElementById('ident_page').querySelector('.ident').style.display=(state?"none":"block");
	document.getElementById('ident_page').querySelector('.new_account').style.display=(state?"block":"none");
	document.getElementById('ident_error').innerHTML="";
}

function switch_affine(sender,type){
    /*var divs=document.getElementById('questions_affines').getElementsByTagName('div');
    for(i=0;i<divs.length;i++){
	if(divs[i].className=="question affine"){
	  var action=divs[i].getAttribute('onclick');
	  var question=action.match(/show_question\('(.+)'\)/);
	  if(question[1]==type)
	      divs[i].className="question affine";
	  else
	      divs[i].className="question affine hidden";
	  
	  var parent=document.getElementsByName(question[1])[0].parentNode;
	  while(parent.nodeName!="UL")
	    parent=parent.parentNode;
	  parent.style.display="none";
	}
    }
    
    var parent=sender.parentNode;
    var lis=parent.getElementsByTagName('li');
    for(i=0;i<lis.length;i++)
	lis[i].className=lis[i].className.replace("clicked","");
    
    sender.className="li6 clicked";
    var input=sender.getElementsByTagName("input")[0];
    input.checked=true;*/

 	if(type === "sensporte") {  //// permet de n'afficher que le bon critère dans "vos critères", quand les valeurs comportent plusieurs valeurs (0,1) par exemple
    		var value = $("input[name='"+type+"']:checked").val();
    		var url="/ajax/configurateur_ajax.php?rep="+type+"&value="+value;
    }
    else {
    		var url="/ajax/configurateur_ajax.php";
    }

    if($("#affin_"+type).next().css('display') === 'block') {
    	$("#affin_"+type).removeClass('clicked');
    	$("#affin_"+type).next().toggleClass('affine hidden');
    }
    else {
    	//wait_config();
    	var post=new Array();
    	post['affine']=type;
    	ajaxrequest(url,display_configurateur,post);
    }
    
}




//************************ fonctions inscription configurateur  ****************************//
function heureLocale(){
	var d = new Date();
	var utc = parseInt(d.getTimezoneOffset() / 60);
	document.getElementsByName('fuseau_horaire')[0].value = utc;
}
function verif(){
	var pro = false;
	var name = '';
	if(document.getElementById('propart').value == '2' ) {
		pro = true;
		name ='pro';  
	}
	var ret=true;
	var tva = new RegExp("^[A-Z]{2}[0-9]+$");

	seterror('err_nom'+name);
	seterror('err_civ');
	seterror('err_prenom'+name);
	seterror('err_mail');
	seterror('err_adresse'+name);
	seterror('err_cp'+name);
	seterror('err_ville'+name);
	seterror('err_mdp1');
	seterror('err_mdp2');
	seterror('err_mdp3');
	seterror('err_tel'+name);
	seterror('err_mnews');
	seterror('err_snews');
	seterror("err_tva");
	seterror("err_tva2");
	seterror("err_tva3");
	seterror("err_fuseau_horaire");
	seterror('err_cgv');
	seterror('err_conseils');
	seterror('err_civilite'+name);
	//seterror('err_connu');

	if(!document.getElementById('cgv_sub').checked) ret=seterror("err_cgv",true);
	if(document.getElementsByName('nom'+name)[0].value=="") ret=seterror("err_nom"+name,true);
	if(document.getElementsByName('prenom'+name)[0].value=="") ret=seterror("err_prenom"+name,true);
	if(document.getElementsByName('mail')[0].value=="" || testmail(document.getElementsByName('mail')[0].value)==false) ret=seterror("err_mail",true);
	if(document.getElementsByName('adresse'+name)[0].value=="") ret=seterror("err_adresse"+name,true);
	if(document.getElementsByName('codepostal'+name)[0].value=="") ret=seterror("err_cp"+name,true);
	if(document.getElementsByName('ville'+name)[0].value=="") ret=seterror("err_ville"+name,true);
	if(document.getElementsByName('passwordinscr')[0].value=="") ret=seterror("err_mdp1",true);
	if(document.getElementsByName('passwordinscr')[0].value.length<6) ret=seterror("err_mdp2",true);
	if(document.getElementsByName('passwordinscr')[0].value!=document.getElementsByName('passwordinscrconfirm')[0].value) ret=seterror("err_mdp3",true);
	if(document.getElementsByName('telephone'+name)[0].value=="" && document.getElementsByName('pordiv'+name)[0].value=="") ret=seterror("err_tel"+name,true);
	if(!document.getElementById('civilite1'+name).checked && !document.getElementById('civilite2'+name).checked) ret=seterror('err_civilite'+name, true);
	
	if(pro){
		if(document.getElementsByName('tvaintracom')[0].value!=""){
			if(!tva.test(document.getElementsByName('tvaintracom')[0].value.replace(/\s/g,""))) ret=seterror("err_tva2",true);
		}
		if(document.getElementsByName('raisonsociale')[0].value=="") ret=seterror("err_tva",true);

	}else{
  		//on vide les champs au cas ou
  		document.getElementsByName('tvaintracom')[0].value = "";
  		document.getElementsByName('raisonsociale')[0].value="";
  		//if(document.getElementById('connu').selectedIndex < 1) ret=seterror("err_connu", true);
  	}

  	var m0 = document.getElementById('m0'); m1 = document.getElementById('m1');
  	if(m0.checked == false && m1.checked == false) ret=seterror("err_mnews",true);

  	var s0 = document.getElementById('s0'); s1 = document.getElementById('s1');
  	if(s0.checked == false && s1.checked == false) ret=seterror("err_snews",true);

  	if(!pro){
  		var conseils0 = document.getElementById('conseils0'); conseils1 = document.getElementById('conseils1');
  		if(conseils0.checked == false && conseils1.checked == false) ret=seterror("err_conseils",true);
  	}


  	if(ret==false)
  		alert("Le formulaire comporte des erreurs, merci de les corriger");
  	else{
  		document.getElementById('gclid').value = document.getElementById('gclid_field').value;
  		inscription();
  		if(typeof(ga) === "function"){
  			ga('send', 'pageview', '/compte/ouverture-ok.php');
  		}
  	}
  }
  function seterror(id,state){
  	var cont = document.getElementById(id);
  	if(id == 'err_mnews' || id == 'err_snews'){
  		cont = document.getElementById('err_news');
  	}

  	if(cont){
  		cont.style.display = (state?"block":"none");

  		if(id == 'err_mnews'){
  			var td = document.getElementsByClassName(id);       
  		}else if(id == 'err_snews'){
  			var td = document.getElementsByClassName(id);
  		}else{
  			var input = cont.parentNode.getElementsByTagName("input");  
  		}
  		/*else if(id == 'err_connu'){
			var td = document.getElementById('connu');
		}*/
  		if(input){
  			input[0].style.border = state ? "solid 1px red" : "none";
  		}else if(td){
  			td[0].style.border = state ? "solid 1px red" : "none";
  			td[0].style.padding = state ? "3px": "3px";
  		}
  	}
  	return !state;
  }
  function showsubscribeConf() {
  	var email = document.getElementById('email').value;
  	$.get( "/ajax/panier/checkEmail.php", { email: email }, function( data ) {
  		if(data !== 'no') {
  			document.getElementById('register_error').innerHTML = 'Cet e-mail est déjà associé à un compte';
  			document.getElementById('register_error').style.display = 'block';
  			document.getElementById('email_log').value = email;
  		} else {
  			document.getElementById('introIdent').style.display="none";				
  			document.getElementById('register1').style.display="none";
  			document.getElementById('register2').style.display="block";
  			if(document.getElementById('email').value.match(/@/)) { ///si un mail n'est pas rentré, ça enlève "votre email"
  				document.getElementsByName('mail')[0].value=document.getElementById('email').value;
  				////tache 7093 : mise en place du Datalayer
  				window.dataLayer = window.dataLayer || [];
  				window.dataLayer.push({
  					'event': 'compte_creation_start',
  					'compte_creation_origine': 'configurateur'
  				});
				////fin
  			}
      //document.getElementsByName('newsletter')[0].checked=document.getElementById('nl').checked;
  }
});
  }


  function display_affinage_config() {
  	$(".ligne").toggle();
  	if($('#affinage').find('i').hasClass('fa-chevron-up')) {
  		$('#affinage').find('i').toggleClass('fa-chevron-up fa-chevron-down');
  	}
  	else if($('#affinage').find('i').hasClass('fa-chevron-down')) {
  		$('#affinage').find('i').toggleClass('fa-chevron-down fa-chevron-up');
  	}
  }


  function reini_affine() {
  	$(".ligne").find(".affine").find(".clicked").each(function() {
  		var name = $(this).find("input").attr('name');
  		if(name === 'origine' || name === 'prix' || name === 'serrure' || name === 'sensporte') {
  			clicklichoix(name+'1000');
  			next(name, '1000');
  		}
  		else {
  			uncheck(name);
  			clickbtn(this,name,return_checkboxes(name));
  		}
  	});

  }

  function supp_critere_affin(id, name) {

  	if(name === 'origine' || name === 'sensporte') {
  		clicklichoix(name+'1000');
  		next(name, '1000');
  	}
  	else {
  		$("#affin_"+name).css('background-color', 'white');
  		$("#affin_"+name).find('.fleche').css('color', '#cfcfcf');
  		clicklichoix(id);
  		var valeur_prix = return_checkboxes(name);
  		if(!valeur_prix && name === "prix") {
  			uncheck(name,this);
  			next(name, '1000');
  		}
  		else {
  			clickbtn(this,name,return_checkboxes(name));
  		}
  		$(".suppcrit_"+id).remove();
  	}
  }


/*
if (window.addEventListener)
  window.addEventListener("load", start, false);
else if (window.attachEvent)
  window.attachEvent("onload", start);
else window.onload = start;
*/
function toggleaideconfig() {
	if($("#newconf_tab").css("display") === "block") {
		$(".etat_rappelconfig").toggle();
	}
	else {
		$(".etat_rappelconfig_desk").toggle();
	}
}

function submitbarrappel_config(desk) {
	var tel=document.getElementById('rappelinst_tel_config'+desk).value;
	var ville=document.getElementById('rappelinst_ville_config'+desk).value;
	var societe=document.getElementById('rappelinst_societe_config'+desk).value;
	var cp=document.getElementById('rappelinst_cp_config'+desk).value;
	var adresse=document.getElementById('rappelinst_adresse_config'+desk).value;
	var pays=document.getElementById('rappelinst_pays_config'+desk).value;
	var mail=document.getElementById('rappelinst_mail_config'+desk).value;
	var cgv =document.getElementById('rappelinst_cgv_config'+desk).getElementsByTagName('input')[0].checked;
	var gclid = document.getElementById('gclid_field').value;
	document.getElementById('rappelinst_error_config'+desk).style.display="none";
	if(tel=="" || pays=="" || ville=="" || !cgv || !testmail(mail)){
		document.getElementById('rappelinst_error_config'+desk).style.display="block";
		return;
	}

	var post=new Array();
	post['gclid'] = gclid;
	post['rappelinst_tel_config']=tel;
	post['rappelinst_ville_config']=ville;
	post['rappelinst_adresse_config']=adresse;
	post['rappelinst_cp_config']=cp;
	post['rappelinst_societe_config']=societe;
	post['rappelinst_nom_config']=document.getElementById('rappelinst_nom_config'+desk).value;
	post['rappelinst_pays_config']=pays;
	post['rappelinst_mail_config']=document.getElementById('rappelinst_mail_config'+desk).value;
	post['url']=document.URL;
	document.getElementById('rappelinst_msg_config'+desk).innerHTML="<img src=\"/configurations/www.ma-cave-a-vin.fr/images/templates/ajax-loader.gif\" align=\"absmiddle\">";
	document.getElementById('rappelinst_config'+desk).getElementsByTagName('a')[1].style.display="none";
	document.getElementById('rappelinst_cgv_config'+desk).style.display="none";
	postcontact(post,rappelinstcb_config);

}

function postcontact(post,cb){
	ajaxrequest("/contact_ajax.php",cb,post);
}

function rappelinstcb_config(httprequest){
	if($('#rappelinst_msg_config').css('display') === 'block') {
		document.getElementById('rappelinst_msg_config').innerHTML=httprequest.responseText;
	}
	else {
		document.getElementById('rappelinst_msg_config_desk').innerHTML=httprequest.responseText;
	}
	setTimeout("hidebarform('rappelinst')",5000);
}

////tache 7093 : mise en place du Datalayer
function datalayer(reponse1,param1,param2,param3){
	if(reponse1==='restart'){
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push({
			'event': 'config_restart'
		});
	}
	else if(reponse1==='config'){
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push({
			'event': 'config_produit_clic',
			'config_produit_clic_type': param3,
			'config_produit_clic_nom': param1,
			'config_produit_clic_ref': param2
		});
	}
	else if(reponse1 === 'connexion'){
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push({
			'event': 'compte_connexion',
			'compte_type': param1,
			'compte_connexion_origine': 'configurateur'
		});
	}
	else if(reponse1 === 'adresse'){
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push({
			'event': 'compte_creation_adresse',
			'compte_type': param1,
			'compte_creation_origine': 'configurateur'
		});
	}
	else if(reponse1 === 'typeproduit1'){
		if(param1 === 'pose'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_cave',
				'config_cave_installation': param2,
				'config_cave_results': param3
			});
		}
		else if(param1 === 'dim'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_cave',
				'config_cave_contrainte_dimension': param2,
				'config_cave_results': param3
			});
		}
		else if(param1 === 'usage'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_cave',
				'config_cave_usage': param2,
				'config_cave_results': param3
			});
		}
		else if(param1 === 'capacite'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_cave',
				'config_cave_capacite': param2,
				'config_cave_results': param3
			});
		}
		else if(param1 === 'temperatures'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_cave',
				'config_cave_temperature': param2,
				'config_cave_results': param3
			});
		}
		else if(param1 === 'typeencastrable'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_cave',
				'config_cave_encastrement': param2,
				'config_cave_results': param3
			});
		}
		else if(param1 === 'largeur'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_cave',
				'config_cave_largeur_niche': param2,
				'config_cave_results': param3
			});
		}
		else if(param1 === 'hauteur'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_cave',
				'config_cave_hauteur_niche': param2,
				'config_cave_results': param3
			});
		}
		else if(param1 === 'hauteurniche'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_cave',
				'config_cave_hauteur_niche': param2,
				'config_cave_results': param3
			});
		}
		else if(param1 === 'esthetique'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_cave',
				'config_cave_design': param2,
				'config_cave_results': param3
			});
		}
		else if(param1 === 'typeporte'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_cave',
				'config_cave_typeporte': param2,
				'config_cave_results': param3
			});
		}
		else if(param1 === 'rangement'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_cave',
				'config_cave_amenagement_int': param2,
				'config_cave_results': param3
			});
		}
		else if(param1 === 'installtemp'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_cave',
				'config_cave_temp_minmax': param2,
				'config_cave_results': param3
			});
		}
	}
	else{
		if(param1 === 'posevitrine'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_vitrine',
				'config_vitrine_installation': param2,
				'config_vitrine_results': param3
			});
		}
		else if(param1 === 'dim'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_vitrine',
				'config_vitrine_contrainte_dimension': param2,
				'config_vitrine_results': param3
			});
		}
		else if(param1 === 'usage'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_vitrine',
				'config_vitrine_usage': param2,
				'config_vitrine_results': param3
			});
		}
		else if(param1 === 'capacite'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_vitrine',
				'config_vitrine_capacite': param2,
				'config_vitrine_results': param3
			});
		}
		else if(param1 === 'temperatures'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_vitrine',
				'config_vitrine_temperature': param2,
				'config_vitrine_results': param3
			});
		}
		else if(param1 === 'typevitrine'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_vitrine',
				'config_vitrine_type': param2,
				'config_vitrine_results': param3
			});
		}
		else if(param1 === 'largeur'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_vitrine',
				'config_vitrine_largeur_niche': param2,
				'config_vitrine_results': param3
			});
		}
		else if(param1 === 'hauteur'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_vitrine',
				'config_vitrine_hauteur_niche': param2,
				'config_vitrine_results': param3
			});
		}
		else if(param1 === 'esthetique'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_vitrine',
				'config_vitrine_design': param2,
				'config_vitrine_results': param3
			});
		}
		else if(param1 === 'typeporte'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_vitrine',
				'config_vitrine_typeporte': param2,
				'config_cave_results': param3
			});
		}
		else if(param1 === 'rangement'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_vitrine',
				'config_vitrine_amenagement_int': param2,
				'config_vitrine_results': param3
			});
		}
		else if(param1 === 'installtemp'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_vitrine',
				'config_vitrine_temp_minmax': param2,
				'config_vitrine_results': param3
			});
		}
		else if(param1 === 'hauteurniche'){
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
				'event': 'config_vitrine',
				'config_vitrine_hauteur_niche': param2,
				'config_vitrine_results': param3
			});
		}
	}
}

//helpconf_desk_fixed



/*$(function () {
	if($("#barresults") !== 'block' && window.innerWidth >1545) {
		$("#products").css('margin-top', '-50px');
	}
	$(window).resize(function(){
		if($("#barresults") !== 'block' ) {
			$("#products").css('margin-top', '-50px');
		}
	});
});*/