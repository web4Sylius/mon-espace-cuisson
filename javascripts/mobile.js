function toggleMenu(){
    var visible=document.getElementById('menu').className=="menu visible";
    var mclass="menu"+(visible?"":" visible");
    document.getElementById('menu').className=mclass;
    hideSousmenus();
}

function hideSousmenus(){
    var menu=document.getElementById('menu');
    var divlist=menu.getElementsByTagName("div");
    for(i=0;i<divlist.length;i++){
	 if(divlist[i].id.match(/sous-menu-[0-9]+/))
	      divlist[i].className="sous-menu";
    }
}

function toggleSousmenu(id){
    hideSousmenus();
    if(id!=undefined){
      document.getElementById('menu').className="menu visible ssmenu";
      document.getElementById('sous-menu-'+id).className="sous-menu visible";
    }else
      document.getElementById('menu').className="menu visible";
}

function toggleIndexMenu(id){
    var active=$('.indexmenu').children('.item:eq('+(id-1)+')').hasClass("active");
    $('.sous-menu').removeClass("visible");
    $('.indexmenu').children('.item').removeClass("active");
    if(!active){
	var iddest=id-1;
	if(id%2==1) iddest=id;
	$('.indexmenu').children('.item:eq('+(id-1)+')').addClass("active");
	$('#menu-index-'+id).insertAfter($('.indexmenu').children('.item')[iddest]);
	var autoHeight=$('#menu-index-'+id).addClass("visible").css('height', 'auto').height();
	$('#menu-index-'+id).height(0).animate({height:autoHeight},600,"swing",function(){$(this).height("auto");});
    }
}

function scrollslider(sens){
    var offset=document.getElementById('container').clientWidth;
//     document.getElementById('container').scrollLeft+=sens*offset;

    $('#container').animate({scrollLeft:"+="+sens*offset},1000);
    clearInterval(slider_to);
}

function resize(){
    if(document.getElementById('container'))
	document.getElementById('container').scrollLeft=0;
    
    $('#visuel').scrollLeft(0);
}

var slider_to;
function mslide(){
    var cont=document.getElementById('container');
    if(cont){
	var maxscroll=cont.scrollWidth-cont.clientWidth;
	if(cont.scrollLeft>=maxscroll)
	    $('#container').animate({scrollLeft:0},1000);
	else
	    scrollslider(1);
	slider_to=setTimeout(mslide,5000);
    }
}

function change_reassurance(){
	if($('.reassurance.slide').length==0) return;
	var imgclass=$('.reassurance.slide').children("div:first")[0].className;
	var c2=/img([0-9])/.exec(imgclass);

	if(c2==undefined){
	     $('.reassurance.slide').children("div:first").addClass("img1");
	}else{
	    var nextimg=parseInt(c2[1])+1;
	    if(nextimg>8) nextimg=1;
	    $('.reassurance.slide').children("div:first").removeClass("img"+c2[1]).addClass("img"+nextimg);
	}
	
	setTimeout(change_reassurance,5000);
}

function affiche_tri(tri){
    
    if($('.popuptri').hasClass("visible")){
      $('.popuptri').removeClass("visible");
      if(tri=="tri") return;
    }
     
    if($('.popupaffiner').hasClass("visible")){
	$('.popupaffiner').removeClass("visible");
	if(tri=="affiner") return;
    }
     
    var autoHeight = $('.popup'+tri).addClass("visible").css('height', 'auto').height();
    $('.popup'+tri).height(0).animate({height:autoHeight},600,"swing",function(){$(this).height("auto");});
}

function affiche_contact(type){
    if($('#minicontact').hasClass("visible")){
	$('#minicontact').removeClass("visible");
	$('#minicontact').prev().removeClass("open");
	if(type=="minicontact") return;
    }
    if($('#rappelinst').hasClass("visible")){
	$('#rappelinst').removeClass("visible");
	$('#rappelinst').prev().removeClass("open");
	if(type=="rappelinst") return;
    }
    var autoHeight = $('#'+type).addClass("visible").css('height', 'auto').height();
    $('#'+type).height(0).animate({height:autoHeight},600,"swing",function(){$(this).height("auto");});
    $('#'+type).prev().addClass("open");
}

function affiche_art_block(id){
    var visible=$('#'+id).hasClass("visible");
    var btn=$('#'+id).prev();
    if($('#'+id).hasClass("fade")) $('#'+id).removeClass("fade");
    if(visible){
	$('#'+id).removeClass("visible");
	btn.removeClass("open");
    }else{
	var autoHeight = $('#'+id).addClass("visible").css('height', 'auto').height();
	$('#'+id).height(0).animate({height:autoHeight},600,"swing",function(){$(this).height("auto");});
	btn.addClass("open");
    }
}

function addEvent( obj, type, fn ) {
    if (obj.addEventListener) {
	obj.addEventListener( type, fn, false );
    } else if (obj.attachEvent) {
	obj["e"+type+fn] = fn;
	obj[type+fn] = function() { obj["e"+type+fn]( window.event ); }
	obj.attachEvent( "on"+type, obj[type+fn] );
    }
}

function submitbarrappel(){
    var tel=document.getElementById('rappelinst_tel').value;
    var pays=document.getElementById('rappelinst_pays').value;
    var mail=document.getElementById('rappelinst_mail').value;
    document.getElementById('rappelinst_error').style.display="none";
    if(tel=="" || pays=="" || !testmail(mail)){
      document.getElementById('rappelinst_error').style.display="block";
      return;
    }

    var post=new Array();
    post['rappelinst_tel']=tel;
    post['rappelinst_nom']=document.getElementById('rappelinst_nom').value;
//     post['rappelinst_societe']=document.getElementById('rappelinst_societe').value;
    post['rappelinst_pays']=pays;
    post['rappelinst_mail']=document.getElementById('rappelinst_mail').value;
    post['url']=document.URL;
    document.getElementById('rappelinst_msg').innerHTML="<img src=\"/configurations/www.ma-cave-a-vin.fr/images/templates/ajax-loader.gif\" align=\"absmiddle\">";
    document.getElementById('rappelinst').getElementsByTagName('a')[0].style.display="none";
    postcontact(post,rappelinstcb);
}

function rappelinstcb(httprequest){
    document.getElementById('rappelinst_msg').innerHTML=httprequest.responseText;
}


function submitbarcontact(){
    var tel=document.getElementById('minicontact_telephone').value;
    var pays=document.getElementById('minicontact_pays').value;
    document.getElementById('minicontact_error').style.display="none";
    if(tel=="" || pays==""){
      document.getElementById('minicontact_error').style.display="block";
      return;
    }

    var post=new Array();
    post['rappelinst_tel']=tel;
    post['rappelinst_nom']=document.getElementById('minicontact_nom').value;
    post['rappelinst_mail']=document.getElementById('minicontact_mail').value;
    post['rappelinst_pays']=pays;
    post['msg']=document.getElementById('minicontact_message').value;
    post['url']=document.URL;
    document.getElementById('minicontact_form').innerHTML="<img src=\"/configurations/www.ma-cave-a-vin.fr/images/templates/ajax-loader.gif\" align=\"absmiddle\">";
    document.getElementById('minicontact').getElementsByTagName('a')[0].style.display="none";
    postcontact(post,minicontactcb);
}

function minicontactcb(httprequest){
    document.getElementById('minicontact_form').innerHTML=httprequest.responseText;
}

function postcontact(post,cb){
    ajaxrequest("/contact_ajax.php",cb,post);
}

function closeFauxSelects(){
	$('.faux_select').each(function(){
		$(this).find('.fleche').removeClass('open');
		$(this).find('ul').css('display', 'none');
	});
}

function filter_link(link,name){
	link.href+='#'+name+document.documentElement.scrollTop;
	$('.wait').removeClass('hidden');
}

function scroll_search(){
	var h=document.location.hash;
	var re=/\#([a-z]+)([0-9]+)/;
	var infos=re.exec(h);
	if(infos!=undefined){
	    window.scrollTo(0,infos[2]);
	    setTimeout(function(){
	      $('#filtre_'+infos[1]).find('ul').css('display', 'block');
	    },100);
	    $('.popupaffiner').addClass('visible');
	}
	$('.wait').addClass('hidden');
}

function scroll_visuel(sens){
	var visuel=document.getElementById('visuel');
	var offset=visuel.clientWidth;

	$('#visuel').animate({scrollLeft:"+="+sens*offset},500,"swing",function(){
	    var maxscroll=visuel.scrollWidth-visuel.clientWidth;
	    document.getElementById('visuel_right').style.display=(visuel.scrollLeft>=maxscroll?"none":"");
	    document.getElementById('visuel_left').style.display=(visuel.scrollLeft==0?"none":"");
	});
}

function setLoaderOptions(){
     if($('#loader_options')){
          
          var div_loader = $('#loader_options');
          var div_options = $('#encart_options');
          
          if(div_loader.css('display') == 'none'){
               
               div_loader.html('<img src="/images/loader.gif">');
               div_loader.css('display', 'block');
               div_options.css('opacity', '0.2');
               
          } else {
               
               div_loader.empty();
               div_loader.css('display', 'none');
               div_options.css('opacity', '1');
               
          }
     }
}

function dimvalues(url,name){
	var dimurl='';
	var dimfields=new Array();
	var dimparam=new Array("larg_min","larg_max","haut_min","haut_max","prof_min","prof_max");
	
	for(i=0;i<dimparam.length;i++) 
	    if(document.getElementById(dimparam[i]))
		dimfields[dimparam[i]]=document.getElementById(dimparam[i]).value;
	    
	for(index in dimfields) 
	    if(dimfields.hasOwnProperty(index) && dimfields[index]!='') 
		dimurl+=index+'='+dimfields[index]+'&';
	    
	url+=(url.indexOf("?")>-1?"&":"?");
	document.location=url+dimurl+'#'+name+document.documentElement.scrollTop;
	$('.wait').removeClass('hidden');
}

var bloc_fermeture_option = true;
function checkOption(obj, artid, caractid, caractvalid, numOption, ral, color){
          
          setLoaderOptions();
          
          /********************\
           * INCOMPATIBILITES *
          \********************/

          //Si l'option n'est pas désactivée, on lance les actions
          if($(obj).prop('class').indexOf('disabled') == '-1'){

               /*if($(obj).prop('class').indexOf('selected') == '-1'){ //Si l'utilisateur active une option

		               var option_unchecked = '';

		               //On détermine si une autre option est déjà sélectionnée dans la même caractéristique
		               $.ajax({

		                    type : 'GET',
		                    url : '/ajax/get_options_caracteristiques.php',
		                    dataType : 'text',
		                    data : { artid: artid, caractid: caractid },
		                    async : false

		               }).done(function(chaine){

		                    var tabOptions = chaine.split('|');
		                    jQuery.each(tabOptions, function(i, option_id){

		                         var lien_id = '#option'+option_id;
		                         if($(lien_id)){ 
		                              var is_checked = $(lien_id).prop('class').indexOf('selected');
		                              if(is_checked != '-1' && caractvalid != option_id){ 

		                                   option_unchecked = option_id;

		                              }
		                         }
		                    });

		               });
		               
		               //Si une autre option de la même caractéristique est cochée, on la décoche, et on réactive les incompatibilités associées
		               if(option_unchecked != ''){
		                    active_incompatibilite(artid, caractid, option_unchecked);
		               }

		               desactive_incompatibilite(artid, caractid, caractvalid);

		          } else { //Si l'utilisateur désactive une option

		               active_incompatibilite(artid, caractid, caractvalid);

		          }*/
          
          /************************\
           * FIN INCOMPATIBILITES *
          \************************/
          
          if($(obj).prop('class').indexOf('selected') != '-1'){
               var etat_bouton = 'selected';
          } else {
               var etat_bouton = 'free';
          }

          //Si action = cochage option
          if(etat_bouton == 'free'){
               //Récupération de la DIV parent de l'option sélectionnée
               var objParent = $(obj).parent().parent();

               //Récupération de l'encart précédent à fermer
               var objPrevBtn = $(objParent).prev('div').find('div.centre_options');

               //Récupération de l'encart suivant à ouvrir
               var objNextBtn = $(objParent).next('div').find('div.centre_options');

               //Ouverture de l'option suivante / Fermeture de la précédente
               if(!bloc_fermeture_option){
                    $(objPrevBtn).slideUp();
               }
               $(objNextBtn).slideDown();

               //Changement de sens des flèches de l'encart précédent | Changement texte "afficher/masquer les options"
               if(!bloc_fermeture_option){ 
                    var objPrevTxt = objParent.next('div').find('div.libelle_option').children('p#txt_options');
                    $(objPrevTxt).empty();
                    $(objPrevTxt).html($(objPrevTxt).data('open')+' <i class="fa fa-chevron-down fa-lg"></i>');
               }

               //Changement de sens des flèches de l'encart suivant | Changement texte "afficher/masquer les options"
               var objNextTxt = objParent.next('div').find('div.libelle_option').children('p#txt_options');
               $(objNextTxt).empty();
               $(objNextTxt).html($(objNextTxt).data('close')+' <i class="fa fa-chevron-up fa-lg"></i>');

               //Décochage de toutes les options
               $(objParent).find('div.encart_option').each(function(i){
                    //Suppression du contour orange sur la DIV
                    $(this).removeClass('selected');

                    //Décochage du bouton
                    $(this).find('input:radio').prop('checked', false);
               });

               //Ajout du contour orange sur la DIV cliquée
               $(obj).addClass('selected');

               //Cochage du bouton
               $(obj).find('input:radio').prop('checked', true);

               //Recalcul du prix total
               doRequest(artid); 

          
          //Si action = décochage option
          } else {
               
               //Suppression du contour orange sur la DIV cliquée
               $(obj).removeClass('selected');

               //Décochage du bouton
               $(obj).children('input:radio').prop('checked', false);
               
               //Recalcul du prix total
               doRequest(artid); 
               
          }
          
     }
     
     //Mise à jour des informations affichées pour les options désactivées
     set_infos_disabled(artid);
     
}

function set_infos_disabled(artid){
     
     //Bouclage sur la liste des input cachés, afin de récupérer les identifiants d'incompatibilités affichées
     $('.input_hidden_option').each(function(){
          
          var text_incomp = $(this).prop('value');
          
          //Si l'option vérifiée est désactivée
          if(text_incomp != ''){
               
               var tab_incomp = new Array();
               tab_incomp = text_incomp.split('|');
               
               //Pour chaque incompatibilité actuellement en cours, on cherche celle qui n'est désactivée
               tab_incomp.each(function(value){
                    
                    $.getJSON( "/ajax/get_options_incompatibles.php?incompatibilite="+value+"&artid="+artid, function( data ) {
                         
                         var opt_intitule_selected = '';
                         $.each( data, function( key, val ) {
                              if($('#option'+key).prop('class').indexOf('selected') != '-1'){
                                   opt_intitule_selected = val;
                              }
                         });
                         
                         $.each( data, function( key, val ) {
                              if($('#option'+key).prop('class').indexOf('selected') == '-1'){
                                   $('#info_disabled_'+key).css('display','block');
                                   $('#lst_opt_dis_'+key).text(opt_intitule_selected);
                              }
                         });
                    });
               });
          } else {
               
               var tab_names = $(this).prop('name').split('_');
               var opt_id = tab_names[2];
               $('#info_disabled_'+opt_id).css('display','none');
               $('#lst_opt_dis_'+opt_id).empty();
          }
     });
     
     setLoaderOptions();
}

function scrolling(){
      var scrolltop=document.body.scrollTop || document.documentElement.scrollTop;
      document.getElementById('goup').style.display=(scrolltop>100?"block":"none");
}

function moyen_infos(btn){
      var desc=btn.nextSibling;
      htmlalert(desc.innerHTML);
}

function devisrapide(){
      err='';
      if(document.getElementById('nom').value=='') err=document.getElementById('err_nom').value;
      if(document.getElementById('prenom').value=='') err=document.getElementById('err_prenom').value;
      if(document.getElementById('mail').value=='') err=document.getElementById('err_mail').value;
      if(document.getElementById('portable').value+document.getElementById('telephone').value=='') err=document.getElementById('err_portable').value;
      if(document.getElementById('pays').value=='') err=document.getElementById('err_pays').value;
      if(document.getElementById('livrid').checked && document.getElementById('payslivr').value=='') err=document.getElementById('err_pays').value;

      if(err!=''){
	  htmlalert(err);
	  return;
      }

      document.getElementById('sendbtn').style.display='none';
      document.getElementById('waiticon').style.display='block';

      params='societe='+document.getElementById('societe').value;
	  params+='&nom='+document.getElementById('nom').value;
      params+='&civ='+document.getElementById('civ').value;
      params+='&prenom='+document.getElementById('prenom').value;
	  params+='&adresse1='+document.getElementById('adresse1').value;
      params+='&cp='+document.getElementById('cp').value;
      params+='&ville='+document.getElementById('ville').value;
	  params+='&mail='+document.getElementById('mail').value;
      params+='&telephone='+document.getElementById('telephone').value;
	  params+='&portable='+document.getElementById('portable').value;
	  params+='&pays='+document.getElementById('pays').value;
      params+='&payslivr='+document.getElementById('payslivr').value;
	  params+='&livrid='+document.getElementById('livrid').checked;
      params+='&devisartid='+document.getElementById('devisartid').value;
      params+='&societe_livr='+document.getElementById('societe_livr').value;
	  params+='&nom_livr='+document.getElementById('nom_livr').value;
      params+='&prenom_livr='+document.getElementById('prenom_livr').value;
	  params+='&adresse1_livr='+document.getElementById('adresse1_livr').value;
      params+='&cp_livr='+document.getElementById('cp_livr').value;
      params+='&ville_livr='+document.getElementById('ville_livr').value;
	  params+='&telephone_livr='+document.getElementById('telephone_livr').value;

      ajaxrequest('devisrapide_ajax.php?'+params,function(){
	  document.getElementById('main').innerHTML="";
	  document.getElementById('devisenvoye').style.display="block";
      });
    
}


function swipedetect(el, callback){
  
    var touchsurface = el,
    swipedir,
    startX,
    startY,
    distX,
    distY,
    threshold = 100, //required min distance traveled to be considered swipe
    restraint = 100, // maximum distance allowed at the same time in perpendicular direction
    allowedTime = 500, // maximum time allowed to travel that distance
    elapsedTime,
    startTime,
    handleswipe = callback || function(swipedir){}
  
    touchsurface.addEventListener('touchstart', function(e){
        var touchobj = e.changedTouches[0]
        swipedir = 'none'
        dist = 0
        startX = touchobj.pageX
        startY = touchobj.pageY
        startTime = new Date().getTime() // record time when finger first makes contact with surface
//         e.preventDefault()
    }, false)
  
    touchsurface.addEventListener('touchmove', function(e){
        //e.preventDefault() // prevent scrolling when inside DIV
    }, false)
  
    touchsurface.addEventListener('touchend', function(e){
        var touchobj = e.changedTouches[0]
        distX = touchobj.pageX - startX // get horizontal dist traveled by finger while in contact with surface
        distY = touchobj.pageY - startY // get vertical dist traveled by finger while in contact with surface
        elapsedTime = new Date().getTime() - startTime // get time elapsed
        if (elapsedTime <= allowedTime){ // first condition for awipe met
            if (Math.abs(distX) >= threshold && Math.abs(distY) <= restraint){ // 2nd condition for horizontal swipe met
                swipedir = (distX < 0)? 'left' : 'right' // if dist traveled is negative, it indicates left swipe
            }
            else if (Math.abs(distY) >= threshold && Math.abs(distX) <= restraint){ // 2nd condition for vertical swipe met
                swipedir = (distY < 0)? 'up' : 'down' // if dist traveled is negative, it indicates up swipe
            }
        }
        handleswipe(swipedir,e)
        
    }, false)
}

var mobileDevice=false;
$( document ).ready(function() {
	$('.select-choix').click(function(){

		var fleche = $(this).find('.fleche');
		var listeChoix = $(this).next('ul');

		//Affichage de la liste de choix lors du clic
		if(listeChoix.css('display') == 'none'){
			//Fermeture de tous les autres select
			closeFauxSelects();

			var autoheight=listeChoix.css('display', 'block').css('height', 'auto').height();
			listeChoix.height(0).animate({height:autoheight},400);
			fleche.addClass('open');
		} else {
			listeChoix.css('display', 'none');
			fleche.removeClass('open');
		}
	});
	
	 //Script permettant l'ouverture/fermeture d'une DIV contenant des options
	$('.libelle_option').each(function(){
	      $(this).click(function(){
		  var objLienEnfant = $(this).children('p#txt_options').children('i');
		  
		  $(this).next('div').slideToggle();
		  if($(objLienEnfant).hasClass('fa-chevron-down')){
			$(objLienEnfant).removeClass("fa-chevron-down").addClass("fa-chevron-up");
		  } else {
			$(objLienEnfant).removeClass("fa-chevron-up").addClass("fa-chevron-down");
		  }
	      });
	});
	
	mobileDevice = (/iphone|ipad|ipod|android|iemobile|windows phone|blackberry|BB10|opera mini|sailfish/i).test(navigator.userAgent.toLowerCase()); // you can add more mobile devices here
	if($('#visuel').length) 
	  swipedetect($('#visuel')[0],function(dir,e){
	      if(dir=="left"){
		  scroll_visuel(1);
		  e.preventDefault()
	      }
	      if(dir=="right"){
		  scroll_visuel(-1);
		  e.preventDefault()
	      }
	  });
	
	if($('#slider1 #container').length)
	  swipedetect($('#slider1 #container')[0],function(dir,e){
	      if(dir=="left"){
		  scrollslider(1);
		  e.preventDefault();
		  clearInterval(slider_to);
	      }
	      if(dir=="right"){
		  scrollslider(-1);
		  e.preventDefault();
		  clearInterval(slider_to);
	      }
	  });
	  
	var $menu = $('#menu'); 
	$(document).on('click', function (e) {
	    // if element is opened and click target is outside it, hide it 
	    if ($menu.hasClass("visible") && !$menu.is(e.target) && !$menu.has(e.target).length && !$('.menubtn').is(e.target) && !$('.menubtn').has(e.target).length) {
		toggleMenu(); 
	    }
	});
});

var mobile=true;
addEvent(window,'load',change_reassurance);
addEvent(window,'load',mslide);
addEvent(window,'resize',resize);
addEvent(window,'scroll',scrolling);
