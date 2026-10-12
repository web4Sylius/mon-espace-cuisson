

var bloc_fermeture_option = true;
var showtimer=false;
var mobile=false;

var hparams=hashparams();
if(hparams!==false)
	if(hparams['utm_source']=="mobile") setCookie("mobile","0",365);

var mobile_url=mobile_alternate();
if(isMobile() && mobile_url!=false && getCookie("mobile")==="")
	document.location=mobile_url;

// ===== Scroll to Top ==== 
$(window).scroll(function() {
    if ($(this).scrollTop() >= 50) {        // If page is scrolled more than 50px
        $('#return-to-top').fadeIn(200);    // Fade in the arrow
    } else {
        $('#return-to-top').fadeOut(200);   // Else fade out the arrow
    }
});
$(window).scroll(function() {
    if ($(this).scrollTop() >= 300 && $(this).scrollTop() <= 1650) {        // If page is scrolled more than 50px
        $('#articlesimu').fadeIn(200);    // Fade in the arrow
    } else {
        $('#articlesimu').fadeOut(200);   // Else fade out the arrow
    }
});
$('#return-to-top').click(function() {      // When arrow is clicked
    $('body,html').animate({
        scrollTop : 0                       // Scroll to top of body
    }, 500);
});

$(window).resize(function(){
	var iWindowsSize = $(document).width();
	//Rollover sur vignettes de la page d'accueil
    $('.index_vignette').unbind();
	if(iWindowsSize > 1182){
		$('.index_vignette').mouseenter(function(){
			$(this).find('.index_hover').show();
			$(this).find('.img_ambiance').hide();
		}).mouseleave(function(){
                        $(this).find('.index_hover').hide();
                        $(this).find('.img_ambiance').show();
		});
	}else{
            $(this).find('.index_hover').hide();
            $(this).find('.img_ambiance').show();
    }
	
	
	$('.vignette').unbind();
	$('.encart_option').unbind();
        if(iWindowsSize > 974 ){
        //Rollover sur vignette de la page catégorie
		$('.vignette').mouseenter(function(){
			$(this).find('img.visuel').css('display','none');
			$(this).find('.categorie_hover').show();
			$(this).find('.vfimg').hide();
		}).mouseleave(function(){
			$(this).find('img.visuel').css('display','block');
			$(this).find('.categorie_hover').hide();
			$(this).find('.vfimg').show();
		});

		//survol des options avec zoom d'un article
		$('.encart_option').mousemove(function(event){
	          var objDivZoom = $(this).next('div.visuel_zoom');
	          var Hzoom = objDivZoom.css('height');
	          if(Hzoom){
	               Hzoom = Hzoom.substr(0, Hzoom.length - 2);
	               var middleWindow = $(window).height() / 2;
	               //if(event.screenY <= middleWindow){
	                    var positionTop = parseInt(event.pageY) ;
	              // } else {
	                 //   var positionTop = parseInt(event.pageY) - parseInt(Hzoom) ;
	               //}

	               objDivZoom.css({
	                    'left' : event.pageX *0.5,
	                    'top' : 'unset'
	               });
	               objDivZoom.show();
	          }
	     }).mouseleave(function(event){
	          if($(this).next('div.visuel_zoom')){
	               $(this).next('div.visuel_zoom').hide();
	          }
	     });

	}else{
                $(this).find('img.visuel').css('display','block');
                $(this).find('.categorie_hover').hide();
                $(this).find('.vfimg').show();
        }
});

$( document ).ready(function() {
	var iWindowsHeight = $(window).height();
	var iWindowsWidth = $(window).width();
	vignscrollSize();

	//gestion du gclid
	$('#gclid1').val($('#gclid_field').val());
	$('#gclidc').val($('#gclid_field').val()); //gclid page contacter.html

	$('#ddown').on('click', function(){
		var elm = $('#ipid');
		var totalHeight = $('#ipid')[0].scrollHeight;
		var height = elm.height();
		var toScroll = height / 2 ;
		elm.scrollTop(elm.scrollTop() + toScroll);
	});
	$('#dup').on('click', function(){
		var elm = $('#ipid');
		var totalHeight = $('#ipid')[0].scrollHeight;
		var height = elm.height();
		var toScroll = height / 2 ;
		elm.scrollTop(elm.scrollTop() - toScroll);
	});

	function affichageFirst(){
		var idLigne = $('#affichage_first').val();
		$('#acces_panier_title_'+idLigne).show();
		$('#scroll_accessoires_'+idLigne).show();
		$('#scrollbtnsparent_'+idLigne).show();
		$('#text_vignscroll_'+idLigne).css('display','table');
		$('#chev_left_'+idLigne).removeClass('fa-chevron-down').addClass('fa-chevron-up');
		$('#chev_right_'+idLigne).removeClass('fa-chevron-down').addClass('fa-chevron-up');
	}
	if($('#affichage_first')) affichageFirst($('#affichage_first').val());

	$('#devisrap').click(function(){
		var artid = $('#devisrapartid').val();
		var gclid = $('#gclid_field').val();
		$.ajax({      
			type : 'GET',
			url : '/devisrapide.php',
			dataType : 'text',
			data : { artid : artid, gclid: gclid },
			success: function(result){ 
				$("#test-popup").html(result);
			}
		});
	});

	/*bouton Accessoire mobile*/
	$('#access-btn-xs').click(function(){
		if($('#access-btn-xs').hasClass('check')){
			$('#access-btn-xs').removeClass('check');
			$('#access_index_xs').hide();
		}else{
			$('#access-btn-xs').addClass('check');			
			$('#access_index_xs').show();
		}
	});

	/* menu responsive */
	$('#menu_resp_close').click(function(){
		//$('#menu_resp').hide();
		$('#menu_resp_b').show();
		$('#site_pusher').toggleClass('push');
		$('#menu_resp').toggleClass('width-menu');
		$('.container_site').toggleClass('ovhidden');
	});
	$('#menu_resp_b').click(function(){
		//$('#menu_resp').show();
		$('#menu_resp_b').hide();
		$('#site_pusher').toggleClass('push');
		$('#menu_resp').toggleClass('width-menu');
		$('.container_site').toggleClass('ovhidden');
	});

	$('.container_menu_2').click(function(){
		$(this).find('.arrow-img').toggleClass('arrow-img-rotate');
	})



  	//creation d'un timer pour l'apparition de l'overlay
  	var myTimer = new Date();
  	myTimer = myTimer.getTime();

    //Gestion rollover sur bouton panier
	$('#btn_panier').mouseover(function(){

		$('#nb_articles_panier').css('background-color', '#a88d5c');

	}).mouseout(function(){

		$('#nb_articles_panier').css('background-color', 'black');

	});

	$('.btn-menu').click(function(){
		if($('.menu').hasClass("visible"))
		    $('.menu').removeClass("visible");
		else
		    $('.menu').addClass("visible");
	});

	$('.niveau1-1').click(function(){
		var id = $(this).attr('id');
		var num = id.substring(id.lastIndexOf('-') + 1, id.length);
		if(num>=3){
			$('#menu_resp').toggleClass('move-up');
		}	

	});

	/*$('.niveau1-1').click(function(){
		var idName = 'sous-menu-2';
		var hgt = 0;
		var marginhgt = 0;
		var id = $(this).attr('id');
		var num = id.substring(id.lastIndexOf('-') + 1, id.length);
		if(num>=4)
		$('#menu_resp').animate({top: -102 });

	});*/


	
	
	//Gestion rollover sur boutons menu niveau 1
	$('.niveau1').mouseenter(function(){
		if(typeof timer != 'undefined') clearTimeout(timer);
	  
		//Activation du bouton survolé
		toggleButtonMenu(this, true);

		//Masquage des sous-menu
		hideClass('sous-menu');
		hideClass('sous-sous-menu');
		
		

		//Affichage du sous menu demandé
		showMenu(this, 'sous-menu');

	}).mouseleave(function(){
                timer = setTimeout(function(){
                    toggleButtonMenu(this, false);
                    hideClass('sous-menu');
                    hideClass('sous-sous-menu');
                },300);
        }).click(function(){

		//Clique sur la div entraine clic sur le lien de la balise <a>
		$(this).find('a').get(0).click();

	});
	
	/*$('.niveau1').mouseleave(function(){
		timer = setTimeout(function(){
		  toggleButtonMenu(this, false);
		  hideClass('sous-menu');
		},100);
	});*/

	//Gestion de la fermeture du menu
	$('.menu').mouseenter(function(){

		if(typeof timer != 'undefined') clearTimeout(timer);

	});
	
	$('.sous-sous-menu').mouseenter(function(){
		if(typeof timer != 'undefined') clearTimeout(timer);
	});
	
	$('.sous-sous-menu').mouseleave(function(){

		//Timer pour fermer le sous-menu, au cas ou il faudrait un délai (0 = fermeture instantanée)
		timer = setTimeout(function(){
			$('.menu').removeClass("visible");
			hideClass('sous-menu');
			hideClass('sous-sous-menu');
			toggleButtonMenu(this, false);
			toggleButtonSousMenu(this, false);
		}, 100);

	});

	$('.sous-menu').mouseenter(function(){
		if(typeof timer != 'undefined') clearTimeout(timer);
	});
	
	//Gestion rollover sur boutons menu niveau 2
	$('.niveau2').mouseenter(function(){
		if(typeof timer != 'undefined') clearTimeout(timer);
		//Activation du bouton du sous-menu
		toggleButtonSousMenu(this, true);

	}).click(function(){

		//Clique sur la div entraine clic sur le lien de la balise <a>
		$(this).find('a').get(0).click();

	});
	
	$('.niveau2').mouseleave(function(){
		timer = setTimeout(function(){	
		    hideClass('sous-menu');
		    hideClass('sous-sous-menu');
		    toggleButtonMenu(this, false);
		},200);
	  
	});

	var iWindowsSize = $(document).width();

	//Rollover sur vignettes de la page d'accueil
	if(iWindowsSize > 1182){
		$('.index_vignette').mouseenter(function(){
			$(this).find('.index_hover').show();
			$(this).find('.img_ambiance').hide();
		}).mouseleave(function(){
                        $(this).find('.index_hover').hide();
                        $(this).find('.img_ambiance').show();
		});
	}
	//Rollover sur vignette de la page catégorie
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

	/* FAUX SELECT */
	$(document).on('click',function(e){
		if($(e.target).closest('.faux_select').length===0){
		    closeFauxSelects();
		}
	});
	
	$('.select-choix').click(function(){

		var listeChoix = $(this).next('ul');

		//Affichage de la liste de choix lors du clic
		if(listeChoix.css('display') == 'none'){
			//Fermeture de tous les autres select
			closeFauxSelects();

			listeChoix.css('display', 'block');
		} else {
			listeChoix.css('display', 'none');
		}
	});

	$('.choice').parent().mouseenter(function(){

		//Changement de couleur au mouseover
		$(this).css("background-color", "#a1a1a1");

	}).mouseleave(function(){

		//Changement de couleur au mouseover
		$(this).css("background-color", "#e3e2e0");

	});

	$('.choice').prev('input').click(function(){

		var element        = $(this).next('a');
		var contentElement = $(this).parent().parent().prev('p').find('a');
		var classElement   = $(this).parent().attr('class');
		var input          = $(this);

		if(input.prop('checked')){

			//Gestion contenu champs principal
			cocheOption(element, input, contentElement);
		} else {

			//Gestion contenu champs principal
			decocheOption(input, contentElement, classElement);
		}
	});

	$('.choice').click(function(){

		var element        = $(this);
		var contentElement = $(this).parent().parent().prev('p').find('a');
		var classElement   = $(this).parent().attr('class');
		var input          = $(this).prev('input');

		if(input.prop('checked')){
			//Décochage checkbox
			input.prop('checked', false);

			//Gestion contenu champs principal
			decocheOption(input, contentElement, classElement);
		} else {
			//Cochage checkbox
			input.prop('checked', true);

			//Gestion contenu champs principal
			cocheOption(element, input, contentElement);
		}
	});

	$('a.valider').click(function(){

		//Récupération de l'url
		var div   = $(this).closest('div');
		var input = div.prev('p').prev('input');
		
		var url = input.val();

		//Récupération des paramètres cochés
		var liste_val = '';
		div.find('li').each(function(){
			if($(this).attr('class') == 'filtre_dimensions'){
				var input_min = $(this).find('input');
				var input_max = $(this).find('input').next('input');

				var min_value = input_min.val();
				var max_value = input_max.val();

				if(input_min.attr('id') == 'larg_min') var libelle = 'largeur';
				if(input_min.attr('id') == 'haut_min') var libelle = 'hauteur';
				if(input_min.attr('id') == 'prof_min') var libelle = 'profondeur';

				url = url.replace('%5B%3Fdim_min%5D',min_value);
				url = url.replace('%5B%3Fdim_max%5D',max_value);
			}

			if($(this).find('input').prop('checked')){
				classLi = $(this).attr('class');
				var aTemp = classLi.split('-');
				if(aTemp[1]){
					if(liste_val != '') liste_val += ',';
					liste_val += aTemp[1];
				} 
			}
		});

		location.href = url.replace('%5B%3Furl_values%5D',liste_val);
	});

	//LISTE DE CHOIX DE L'ORDRE DE TRI (PAGE CATEGORIE)
	$('.tri_page').on('change', function(){
		location.href = $(this).val();
	});

	//LISTE DE CHOIX DU NOMBRE DE RESULTATS PAR PAGE (PAGE CATEGORIE)
	$('.nb_page').on('change', function(){
		location.href = $(this).val();
	});

	//PAGE DETAIL PRODUIT
     //Script permettant l'ouverture/fermeture d'une DIV contenant des options
     $('.libelle_option').each(function(){
          $(this).click(function(){
               var objTxtEnfant = $(this).children('p#txt_options');
               var objLienEnfant = objTxtEnfant.children('i');
               
               $(this).next('div').slideToggle();
               
               if($(objLienEnfant).hasClass('fa-chevron-down')){
                    $(objTxtEnfant).empty();
                    $(objTxtEnfant).html($(objTxtEnfant).data('close')+' <i class="fa fa-chevron-up fa-lg"></i>');
               } else {
                    $(objTxtEnfant).empty();
                    $(objTxtEnfant).html($(objTxtEnfant).data('open')+' <i class="fa fa-chevron-down fa-lg"></i>');
               }
          });
     });
     
     //PAGE DETAIL PRODUIT
     //Script permettant l'ouverture de toutes les options
     $('#libelle_encart').click(function(){
          var opened = false;
          var objParentFleche = $(this).children('a');
          
          $(this).nextAll('.centre_options').each(function(){
               if($(this).css('height') === '1px' || $(this).css('display') === 'none'){
                    var objDivPrev = $(this).prev('.libelle_option');
                    var objTxt = $(objDivPrev).children('p#txt_options');
                    var objFleche = objTxt.children('i');
                    
                    if($(objFleche).hasClass('fa-chevron-down')){
                         $(objTxt).empty();
                         $(objTxt).html($(objTxtEnfant).data('close')+' <i class="fa fa-chevron-up fa-lg"></i>');
                    } else {
                         $(objTxt).empty();
                         $(objTxt).html($(objTxtEnfant).data('open')+' <i class="fa fa-chevron-down fa-lg"></i>');
                    }
                    
                    $(this).slideToggle();
                    opened = true;
               }
          });
          
          //Si tout est déjà ouvert, on referme tout
          if(!opened){
               $(this).nextAll('.centre_options').each(function(){
                    var objDivPrev = $(this).prev('.libelle_option');
                    var objTxt = $(objDivPrev).children('p#txt_options');
                    var objFleche = objTxt.children('i');
                    
                    if($(objFleche).hasClass('fa-chevron-down')){
                         $(objTxt).empty();
                         $(objTxt).html($(objTxt).data('close')+' <i class="fa fa-chevron-up fa-lg"></i>');
                    } else {
                         $(objTxt).empty();
                         $(objTxt).html($(objTxt).data('open')+' <i class="fa fa-chevron-down fa-lg"></i>');
                    }
                    
                    $(this).slideToggle();
               });
          
               $(objParentFleche).removeClass();
               $(objParentFleche).addClass('fleche');
               
               $('#action_bouton').empty();
               $('#action_bouton').html('Afficher toutes les caractéristiques');
               
               bloc_fermeture_option = false;
          } else {
               $(objParentFleche).removeClass();
               $(objParentFleche).addClass('fleche_on');
               
               $('#action_bouton').empty();
               $('#action_bouton').html('Masquer toutes les caractéristiques');
               
               bloc_fermeture_option = true;
          }
     });

     //fonction pour faire afficher l overlay lorsque l'utilisateur sors la souris du navigateur.
     if(iWindowsSize > 1182){
		$(document).mousemove(function(e) {
			var y = e.pageY - (document.body.scrollTop || document.documentElement.scrollTop);
			var date = new Date();
			var heure = date.getHours();
			var jour = date.getDay();
			var time = date.getTime() - myTimer ; //myTimer est déclaré en haut
			$('#exitpopup').css('left', (window.innerWidth/2 - $('#exitpopup').width()/2));
			$('#exitpopup').css('top', (window.innerHeight/2 - $('#exitpopup').height()/2));
			if(getCookie('admin_username') == ""){
				if( y <= 5 && getCookie('popup_exit') == '' && ((heure>=9 && heure <= 13) || (heure>=14 && heure <= 19)) && (jour >= 1 && jour <= 5) && time>=3000) {
					tb_show('', '/rappelimmediat.php?width=400&height=300',false);
					setCookie('popup_exit',1,1);		
				}	
			}
		});
	}

	$('#rappel_immediat_non').click(tb_remove);
			

     if(iWindowsSize > 974 ){
	     $('.encart_option').mousemove(function(event){
	          var objDivZoom = $(this).next('div.visuel_zoom');
	          var Hzoom = objDivZoom.css('height');
	          if(Hzoom){
	               Hzoom = Hzoom.substr(0, Hzoom.length - 2);
	               var middleWindow = $(window).height() / 2;
	               //if(event.screenY <= middleWindow){
	                    var positionTop = parseInt(event.pageY) ;
	              // } else {
	                 //   var positionTop = parseInt(event.pageY) - parseInt(Hzoom) ;
	               //}

	               objDivZoom.css({
	                    'left' : event.pageX * 0.5,
	                    'top' : 'unset'
	               });
	               objDivZoom.show();
	          }
	     }).mouseleave(function(event){
	          if($(this).next('div.visuel_zoom')){
	               $(this).next('div.visuel_zoom').hide();
	          }
	     });
	 }
     
      $(window).scroll(function() {
	  if(!$('.menu').hasClass("fixed") && ($(window).scrollTop() > 125)) {
	      $('.menu').addClass("fixed");
	      $('.sous-menu').addClass("fixed");
	      $('.sous-sous-menu').addClass("fixed");
	      $('.sous-menu').each(function(){
		if($(this).css('display')=="block"){
		    $('.menu').addClass("visible");
		    return false;
		}
	      });
	      $('#divprop').css("display","none");
	   
	      if($(window).width()>1000){
		 		$('#menu_sm').find('#bouton_panier').detach().appendTo('#navbar-nav');
		 		$('.minilogo').show();
	      }
	  }
	  else if ($('.menu').hasClass("fixed") && ($(window).scrollTop() < 125)) {
	      $('.menu').removeClass("fixed");
	      $('.sous-menu').removeClass("fixed");
	      $('.sous-sous-menu').removeClass("fixed");
	      $('#navbar-nav').find('#bouton_panier').detach().prependTo('.icons');
	      $('.minilogo').hide();
	  }
	  
	  if($('.faux_select:hover').length==0) closeFauxSelects();
      });
      
      // footer
      $('.footerbastop').click(function(){
	if($('.footerbastop').hasClass('open')){
	    $('.footerbas').hide();
	    $('.footerbastop').removeClass('open');
	}else{
	    $('.footerbas').show();
		$('.lazyf').lazy();
	    $('.footerbastop').addClass('open');
	}
      });
      
      if($('.reassurance.slide').length>0){
	  change_reassurance();
      }
      
      if($('#encart_options').length>0)
	  arrange_options();


	//Affichage de l'encart Sofinco sur les pages articles du FRONT
	if($('#artid_sofinco').length){

		var artid = $('#artid_sofinco').val();

		$.ajax({
	          
			type : 'GET',
			url : '/ajax/sofinco.php',
			dataType : 'text',
			data : { 
          		artid : artid, 
          		type : 'article' 
          	}

		}).done(function(responseText){

			$('#sofinco_content').css('text-align', 'left'); //Le centrage est utile pour le loader
			$('#sofinco_content').html(responseText);
		});
	}

	//Affichage de l'encart Sofinco sur les pages commandes du FRONT
	if($('#facturid_sofinco').length){

		var facturid = $('#facturid_sofinco').val();
		var mensualites = $('#mensualites_sofinco').val();
		var moyenpaiement = $('#moyenpaiement_sofinco').val();

		$.ajax({
	          
			type : 'GET',
			url : '/ajax/sofinco.php',
			dataType : 'text',
			data : { 
	          	facturid : facturid,
	          	mensualites : mensualites,
	          	moyenpaiement : moyenpaiement,
	          	type : 'commande' 
			}

		}).done(function(responseText){

			$('#sofinco_content'+moyenpaiement).css('text-align', 'left'); //Le centrage est utile pour le loader
			$('#sofinco_content'+moyenpaiement).html(responseText);
		});
	}

	/********************/
	/* CHARGEMENTS AJAX */
	/********************/
	//loadAjaxPanier();
	//loadAjaxCompte();
	//loadAjaxNbArticlesVus();
	//loadAjaxArticlesVus();
	//loadAjaxNbPdtsComparateur();
	//loadAjaxConfig();
	//loadAjaxNbConfig();
	console.log('loadAjax');
	loadAjax();
	loadAjaxDelaisLivraison();
});

function loadAjaxDelaisLivraison()
{
	//Nombre de produits au panier (header)
	if($('#delais_livraison_ajax').length)
	{
		//$('#delais_livraison_ajax').html('<img src="/images/loader.gif" style="width: 10px;">');

		$.ajax({
	          
         	type : 'GET',
			url : '/ajax/article/delais_livraison.php',
			dataType : 'text',
			data : { 
	          	artid : $('#article_id').val(),
			}

		}).done(function(responseText){

			$('#delais_livraison_ajax').html(responseText);

		});
	}
	/*
	//Nombre de produits au panier (header)
	if($('#date_livraison_ajax').length)
	{
		$('#date_livraison_ajax').html('<img src="/images/loader.gif" style="width: 10px;">');

		$.ajax({
	          
          	type : 'GET',
			url : '/ajax/article/date_livraison.php',
			dataType : 'text',
			data : { 
	          	artid : $('#article_id').val(),
			}

		}).done(function(responseText){

			$('#date_livraison_ajax').html(responseText);

		});
	}
	*/
}

function loadAjaxConfig()
{
	//Articles vus (barre footer)
	$.ajax({
	          
		url : '/ajax/footer_barre/configurations.php',
		dataType : 'text'

	}).done(function(responseText){

		$('#popup_config').html(responseText);
	});
}

function loadAjax()
{
	$('#nb_configs').html('<img src="/images/loader.gif" style="width: 10px;">');
	$('#nb_pdts_comparateur').html('<img src="/images/loader.gif" style="width: 10px;">');
	$('#articlevusbarbtn').html('<img src="/images/loader.gif" style="width: 10px;">');
	var gclid = $('#gclid_field').val();
	if(gclid!=''){
		var gclidtxt='&gclid='+gclid;
	} else {
		var gclidtxt='';
	}
	$.ajax({
	          
		url : '/ajax/loadAjax.php?r=' + encodeURIComponent(window.location.pathname + window.location.search + window.location.hash + gclidtxt),
		dataType : 'json'

	}).done(function(data){
		if(data['list_pdts_comparateur']!=""){
			var artids = data['list_pdts_comparateur'].split(",");
			artids.forEach(function(artid){ 
				$('body #btn_comparaison'+artid).children('i').remove();
				$('body #btn_comparaison'+artid).prepend('<i class="fas fa-check"></i>');
				$('body #btn_comparaison'+artid).removeClass('btn_comparer_off').addClass('btn_comparer_on');
				$('body #span_comparaison'+artid).text(data['bouton_comparateur']);
			});
		}
		$('#nb_configs').html(data['nb_configs']);
		$('#nb_pdts_comparateur').html(data['nb_pdts_comparateur']);
		$('#articlevusbarbtn').html(data['nb_articles_vus']);
		$('#articlevusbar').html(data['articles_vus']);
		$('#popup_config').html(data['config']);
		$(data['popup_panier']).insertAfter('#bouton_panier');
		$('#nb_articles_panier').html(data['nb_articles_panier']);
		if(data['nb_articles_panier']>0){
			$("#bouton_panier").hover(function(){
			  popup_header('popup_panier',true);
			  }, function(){
			  popup_header('popup_panier',false);
			});
		}
		$(data['popup_compte']).insertAfter('#bouton_compte');
	});
	toggleCompare(0);
}

function loadAjaxNnconfig(){
	$('#nb_configs').html('<img src="/images/loader.gif" style="width: 10px;">');

	$.ajax({
	          
		url : '/ajax/loadAjax.php',
		dataType : 'text'

	}).done(function(responseText){

		$('#nb_configs').html(responseText);
		
	});
}

function loadAjaxNbPdtsComparateur()
{
	$('#nb_pdts_comparateur').html('<img src="/images/loader.gif" style="width: 10px;">');

	//Articles vus (barre footer)
	$.ajax({
	          
		url : '/ajax/footer_barre/nb_pdts_comparateur.php',
		dataType : 'text'

	}).done(function(responseText){

		$('#nb_pdts_comparateur').html(responseText);
	});
}

function loadAjaxArticlesVus()
{
	//Articles vus (barre footer)
	$.ajax({
	          
		url : '/ajax/footer_barre/articles_vus.php',
		dataType : 'text'

	}).done(function(responseText){

		$('#articlevusbar').html(responseText);
	});
}

function loadAjaxNbArticlesVus()
{
	$('#articlevusbarbtn').html('<img src="/images/loader.gif" style="width: 10px;">');

	//Nb articles vus (barre footer)
	$.ajax({
	          
		url : '/ajax/footer_barre/nb_articles_vus.php',
		dataType : 'text'

	}).done(function(responseText){

		$('#articlevusbarbtn').html(responseText);
	});
}

function loadAjaxPanier()
{
	//Nombre de produits au panier (header)
	//if($('#nb_articles_panier').length)
	//{
		$('#nb_articles_panier').html('<img src="/images/loader.gif" style="width: 7px;">');
		
		
		
		$.ajax({
	          
			url : '/ajax/panier/nb_produits.php?r=' + window.location.pathname + window.location.search + window.location.hash,
			dataType : 'text'

		}).done(function(responseText){

			$('#nb_articles_panier').html(responseText);

		});
	//}

	//Popup contenu panier (header)
	$.ajax({
		          
		url : '/ajax/panier/popup_produits.php',
		dataType : 'text'

	}).done(function(responseText){

		$(responseText).insertAfter('#bouton_panier');

	});
}

function loadAjaxCompte()
{
	//Popup "mon compte" (header)
	$.ajax({
	          
		url : '/ajax/compte/popup_compte.php',
		dataType : 'text'

	}).done(function(responseText){

		$(responseText).insertAfter('#bouton_compte');

	});
}

function decocheOption(input, contentElement, classElement){
	
	//Gestion contenu champs principal
	removedElement = contentElement.find('.'+classElement);
	removedElement.remove();

	//Si le champs est vide, on réécrit "tous"
	
	var defaultChoice = contentElement.find('.choix-0');
	var content = defaultChoice.next('span');
	if(typeof(content.attr('class')) == 'undefined') defaultChoice.css('display','inline');

	//Nettoyage du contenu (virgules au bon endroit)
	if(defaultChoice.css('display') == 'none'){
		var i = 0;
		contentElement.find('span').each(function(){

			currentElementClass = $(this).attr('class');
			nextElementClass = $(this).next('span').attr('class');

			//Début de chaine : pas de virgule
			if(i == 1 && currentElementClass == 'sep'){
				$(this).remove();
			}

			//Fin de chaine : pas de virgule
			if(typeof(nextElementClass) == 'undefined' && currentElementClass == 'sep'){
				$(this).remove();
			}

			//2 libellés à la suite sans virgule, on en ajoute une
			if(i > 0 && currentElementClass != 'sep' && nextElementClass != 'sep') {
				$(this).after('<span class="sep">, </span>');
			}

			//2 virgules à la suite, on en supprime une
			if(i > 0 && currentElementClass == 'sep' && nextElementClass == 'sep') {
				$(this).remove();
			}

			i += 1;
		});
	}
}

function cocheOption(element, input, contentElement){

	//Gestion contenu champs principal
	var spanContenu = contentElement.find('.choix-0');
	var displaySpan = spanContenu.css('display');

	if(displaySpan == 'inline'){
		spanContenu.css('display','none');
		contentElement.append('<span class="'+element.parent().attr('class')+'">'+element.text()+'</span>');
	} else {
		contentElement.append('<span class="sep">, </span><span class="'+element.parent().attr('class')+'">'+element.text()+'</span>');
	}


}

function toggleButtonMenu(obj, state){
	
	$('.niveau1').each(function(){
		$(this).children('.tiret').removeClass('selected');
		$(this).find('a').removeClass('selected');
	});

	if(state){
		$(obj).children('.tiret').addClass('selected');
		$(obj).css('cursor','pointer');
		$(obj).find('a').addClass('selected');
	}
}

function toggleButtonSousMenu(obj, state){

	$('.niveau2').each(function(){
		$(this).removeClass('selected');
	});

	if(state){
		$(obj).addClass('selected');
	}
}

function showMenuResp(id){
	var idName = 'sous-menu-2';
	var hgt = 0;
	var marginhgt = 0;
	var num = id.substring(id.lastIndexOf('-') + 1, id.length);
	if(num>=4)
		$('#menu_resp').animate({top: -102 });	
}



function showMenu(obj, idName){
	var id = $(obj).attr('id');
	var num = id.substring(id.lastIndexOf('-') + 1, id.length);


	var menu1_pos = $('#'+id).position();
	var menu2_pos = $('#'+idName+'-'+num).position();
	var fond_menu_pos = $('#fond-menus').position();
	var left = 0;

	if(menu2_pos != undefined){
		menu2_pos.left = menu1_pos.left - fond_menu_pos.left;
		left = menu2_pos.left;
	}

	$('#'+idName+'-'+num).show().css("left",left);
	
	if(idName=="sous-menu"){
	    var autoHeight=55;
	    var width=0;
	    $('#'+idName+'-'+num).find('.niveau2').each(function(){
		width ++;
	    });
	    width = 100 / width;
		var childwidth = Math.floor(width);
		childwidth =childwidth - ((1/width)*2.8);
		childwidth = childwidth+'%';
		/*$('#'+idName+'-'+num).find('.niveau2').each(function(){
		  //$(this).width(childwidth);
		});*/
	}
	/*if(idName=="sous-sous-menu"){
	    var autoHeight =$('#'+idName+'-'+num).find('.ssmenu-inner').height();
	}*/
	
	//$('#'+idName+'-'+num).delay(200).animate({height: autoHeight}, 500);
}

function hideClass(className){
	$('.'+className).hide();
}

function closeFauxSelects(){
	$('.faux_select').each(function(){
				$(this).find('ul').css('display', 'none');
	});
}

function toggleSlide(obj, id){
	var divid = '#'+id;
	$(divid).slideToggle('fast',function(){
		if($(divid).css('display') == 'none'){
			$(obj).removeClass('opened');
		} else {
			$(obj).addClass('opened');
		}
	});
}

// fonction à modifier aussi dans mobile.js si nécessaire
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
                    $(this).children('input:radio').prop('checked', false);
               });

               //Ajout du contour orange sur la DIV cliquée
               $(obj).addClass('selected');

               //Cochage du bouton
               $(obj).find('input:radio').prop('checked', true);

               //Recalcul du prix total
               doRequest(artid); 

               //Affichage de l'option sélectionné dans l'encart preview
               option_preview_new(artid, caractid, caractvalid, false, ral, color);

               //enregistrment des options pour envoi de la demande de contact
               var checked_opt = $('#checked_opt_'+caractid);
               checked_opt.val(caractvalid);


          
          //Si action = décochage option
          } else {
               
               //suppression des options enregistrées pour envoi de la demande de contact
               var checked_opt = $('#checked_opt_'+caractid);
               checked_opt.val('');

               //Suppression du contour orange sur la DIV cliquée
               $(obj).removeClass('selected');

               //Décochage du bouton
               $(obj).find('input:radio').prop('checked', false);
               
               //Recalcul du prix total
               doRequest(artid); 
               
               //Affichage de l'option sélectionné dans l'encart preview
               option_preview_new(artid, caractid, caractvalid, true);

          }
          
     }
     
     //Mise à jour des informations affichées pour les options désactivées
     set_infos_disabled(artid);
     
     /*if(!bloc_fermeture_option){
          if(numOption > 1){
               var goToOption = numOption - 1;
          }
          location.href = '#choix_options'+goToOption;
     }*/
}
function checkOptionSimu(artid,caractid,caractvalid,type,time){
	$(".calque"+type).attr("src","/configurations/www.ma-cave-a-vin.fr/images/simulateur/"+artid+"_"+caractid+"_"+caractvalid+".png?"+time);
	$(".calque"+type).attr("value",caractid+"_"+caractvalid);
	var d = new Date();
  	d.setTime(d.getTime() + (1*24*60*60*1000));
	document.cookie = "calque"+type+"="+caractid+"_"+caractvalid+";expires="+d.toUTCString()+";";
}

function option_preview_new(artid, caractid, caractvalid, remove, ral, color){
     $.ajax({
          
          type : 'GET',
          url : '/ajax/checkOption.php',
          dataType : 'text',
          data : { caractvalid: caractvalid, artid: artid }
          
     }).done(function(html){
          
          $("#previewopt"+caractid).empty();
          if(!remove){
               $("#previewopt"+caractid).append(html);
          } else {
               $("#previewopt"+caractid).append("<span style=\"color:#DC081E;\">A définir</span>");
          }
          
          if(!remove && ral){
           		var Element = $('#previewopt'+caractid).find('div');
			    Element.find('img').remove();
			    Element.prepend('<div class="ral-couleur" style="background-color:'+color+';"></div>');
           }
     });
}

function active_incompatibilite(artid, caractid, caractvalid){
     $.ajax({

          type : 'GET',
          url : '/ajax/get_incompatibilites.php',
          dataType : 'xml',
          data : { caractvalid: caractvalid, artid: artid },
          async : false

     }).done(function(xml){

          xmlDoc = $(xml);
               
          xmlDoc.find('incompatibilite').each(function(){
               
               var incompatibilite_id = $(this).attr('value');
               
               $(this).find('caracteristique').each(function(){
                    
                    var caracteristique_id = $(this).attr('value');
                    
                    $(this).find('option').each(function(){

                         var option_id = $(this).text();
                         
                         //Association de l'identifiant incompatibilité à l'option
                         var tab_incomp = new Array();
                         var lst_incomp = $('#incomp_opt'+option_id).attr('value');
                         
                         tab_incomp = lst_incomp.split('|');
                         
                         var j = 0;
                         var recons_incomp = new Array();
                         for(i=0;i<tab_incomp.length;i++){
                              if(tab_incomp[i] != '' && tab_incomp[i] != incompatibilite_id){
                                   recons_incomp[j] = tab_incomp[i];
                              }
                         }
                         
                         var activation = true;
                         if(recons_incomp.length > 0 && recons_incomp[0] != ''){
                              activation = false;
                              var texte_input = recons_incomp.join('|');
                              
                              $('#incomp_opt'+option_id).attr('value', texte_input);
                         } else {
                              $('#incomp_opt'+option_id).attr('value', '');
                         }
                         
                         //Activation de l'option si nécessaire
                         if(activation){
                              $('#option'+option_id).removeClass('disabled');
                              $('#lst_'+caracteristique_id+'_'+option_id).prop('disabled', false);
                         }
                    });
                    
               });
          });
     });
}

function desactive_incompatibilite(artid, caractid, caractvalid){
     
     //Récupération de la liste des options potentiellement incompatibles
     $.ajax({
          
          type : 'GET',
          url : '/ajax/get_incompatibilites.php',
          dataType : 'xml',
          data : { caractvalid: caractvalid, artid: artid },
          async : false
          
     }).done(function(xml){
          
          xmlDoc = $(xml);
               
          xmlDoc.find('incompatibilite').each(function(){
               
               var incompatibilite_id = $(this).attr('value');
               
               $(this).find('caracteristique').each(function(){
                    
                    var caracteristique_id = $(this).attr('value');
                    
                    $(this).find('option').each(function(){

                         var option_id = $(this).text();
                         
                         //Association de l'identifiant incompatibilité à l'option
                         var tab_incomp = new Array();
                         var lst_incomp = $('#incomp_opt'+option_id).attr('value');
                         
                         tab_incomp = lst_incomp.split('|'); 
                         if(tab_incomp.length >= 1 && tab_incomp[0] != ''){
                              tab_incomp[tab_incomp.length] = incompatibilite_id;
                         } else {
                              tab_incomp[0] = incompatibilite_id;
                         }
                         
                         var texte_input = tab_incomp.join('|');
                         
                         $('#incomp_opt'+option_id).attr('value', texte_input);
                         
                         if(caracteristique_id != caractid){
                              //Désactivation de l'option
                              $('#option'+option_id).addClass('disabled');
                              $('#lst_'+caracteristique_id+'_'+option_id).prop('disabled', true);
                         }
                         
                    });
                    
               });
          });
     });
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

function show_ral(caractvalid){
	$('.ral_panel').each(function(){
		if($(this).attr('id') != 'ral_panel_'+caractvalid){
			$(this).hide();
		}
	});

	if(caractvalid){
		$('#ral_panel_'+caractvalid).toggle();
	}
}

function close_ral(){
	$('.ral_panel').each(function(){
		$(this).hide();
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
	}
	$('.wait').addClass('hidden');
}

function change_reassurance(){
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

/**
	Cette fonction ajoute la class selected au boutons vert pour les accessoires conseillés en fonction de la taille de l'écran
**/
function vignscrollSize(){
	var iWindowsSize = $(document).width();
	var i;
	if(iWindowsSize > 1182 && iWindowsSize <1574){
		for(i=0;i<4;i++){
			$("#scroll_accessoires_btns > span.btn:eq("+i+")").addClass("selected");
			$("#scroll_similaire_btns > span.btn:eq("+i+")").addClass("selected");
		}
	}else if(iWindowsSize > 1573){
		for(i=0;i<5;i++){
			$("#scroll_accessoires_btns > span.btn:eq("+i+")").addClass("selected");
			$("#scroll_similaire_btns > span.btn:eq("+i+")").addClass("selected");
		}
	}
}

function vignscroll(divid,action){

      var vignette = $('#scroll_'+divid).children().first();
      var step = $(vignette).width() + parseInt($(vignette).css('margin-right'));
      var scroller=document.getElementById("scroll_"+divid);
      if(!scroller) return false;

      var elemnb=$('#scroll_'+divid+'_btns').find('.btn').length;
      var elemnbSelected = $('#scroll_'+divid+'_btns').find('.selected').length;
      var maxpos = elemnb - elemnbSelected;
      var scrollleft=scroller.scrollLeft;
      var pos=Math.round(scrollleft/step);
      
      var newpos=parseInt(action);
      var rel=action.substr(0,1);
      if(rel=="-") newpos=pos-1;
      if(rel=="+") newpos=pos+1;
      
      if(newpos>maxpos) newpos=maxpos;
      if(newpos<0) newpos=0;
      var btnpos=0;
      $('#scroll_'+divid).animate({scrollLeft:parseInt(step*newpos)},750);
      $('#scroll_'+divid).scrollLeft=parseInt(step*newpos);
      
      $('#scroll_'+divid+'_btns').find('.btn').each(function(){
		  	if(btnpos >= newpos && btnpos < newpos + elemnbSelected)
		    	$(this).addClass("selected");
			else
		    	$(this).removeClass("selected");
			
			btnpos++;
      });
      /*if(newpos==0)
	  $('#scroll_'+divid+'_btns').find('.toleft').css("visibility","hidden");
      else
	  $('#scroll_'+divid+'_btns').find('.toleft').css("visibility","");
      if(newpos==maxpos)
	  $('#scroll_'+divid+'_btns').find('.toright').css("visibility","hidden");
      else
	  $('#scroll_'+divid+'_btns').find('.toright').css("visibility","");
      */
}

function change_artpic(src){
      document.getElementById('artpic').src=src;
}

function arrange_options(){
      $('.centre_options').each(function(){
	  var heights=new Array();
	  var display=$(this).css('display');
	  $(this).css({
		  position:   'absolute',
		  visibility: 'hidden',
		  display:    'block'
	      });
	  $(this).find('.encart_option .title').each(function(){
	      heights.push($(this).outerHeight());
	  });
	  var max=Math.max.apply(Math,heights);
	  $(this).find('.encart_option .title').each(function(){
	      $(this).height(max);
	  });
	  $(this).css({
		position:   '',
		visibility: '',
		display: display
	  });
      });
}

function mobile_alternate(){
      var links=document.getElementsByTagName('link');
      for(x in links){
	  if(links.hasOwnProperty(x) && links[x].getAttribute("rel")=="alternate" && links[x].getAttribute("media")=="handheld")
	      return links[x].getAttribute("href");
      }
      return false;
}

function hashparams(){
      var url=document.location.href;
      var hindex=url.indexOf("#");
      if(hindex==-1) return false;
      var s=url.substring(hindex+1);
      var p=s.split("&");
      var ret=new Array();
      for(i=0;i<p.length;i++){
	  var d=p[i].split("=");
	  ret[d[0]]=d[1];
      }
      
      return ret;
}

function afficheAccessoiresPanier(id,last_ligne, first_ligne){
	var acces = $('#scroll_accessoires_'+id).is(':visible');
	if(acces){
		$('#scroll_accessoires_'+id).hide();
		$('#scrollbtnsparent_'+id).hide();
		$('#acces_panier_title_'+id).hide();
		$('#text_vignscroll_'+id).css('display','none');
		$('#chev_left_'+id).removeClass('fa-chevron-up').addClass('fa-chevron-down');
		$('#chev_right_'+id).removeClass('fa-chevron-up').addClass('fa-chevron-down');
		if(last_ligne ){
			$('#borderTab').show();
		}
	}
	else{
		$('#scroll_accessoires_'+id).show();
		$('#scrollbtnsparent_'+id).show();
		$('#acces_panier_title_'+id).show();
		$('#text_vignscroll_'+id).css('display','table');
		$('#chev_left_'+id).removeClass('fa-chevron-down').addClass('fa-chevron-up');
		$('#chev_right_'+id).removeClass('fa-chevron-down').addClass('fa-chevron-up');
		//$('chev_left_'+id).addClass('fa-chevron-up');
		if(last_ligne){
			$('#borderTab').hide();
		}
	}

}

function afficheBoutonAccesPanier(){
	$('#panier_accessoires').show();
}



