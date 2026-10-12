function showbarform(id, left){
  /*if (rappelsihiding[id])
  return;*/
  if(!document.getElementById(id)) return;
  //document.getElementById(id+'span').className = 'fa fa-caret-right fa-lg';

  showaide(false);

  $('#popup_comparerbtn').css('width', '169px');
  $('#popup_comparerbtn').css('opacity', '1');
  $('#popup_comparerbtn').css('bottom', '0');
  $('#popup_configbtn').css('opacity', '1');
  $('#popup_configbtn').css('bottom', '0');
  $('#articlevusbarbtn').css('width', '169px');
  $('#articlevusbarbtn').css('opacity', '1');
  $('#articlevusbarbtn').css('bottom', '0');
  $('#articlevusbar').hide();
  $('#popup_config').hide();
  $('#popup_comparer').hide();

  if(id === "popup_comparer") {
   if($(window).width() < 1199) {
    $('#popup_comparerbtn').css('width', '169px');
  }
  else {
    $('#popup_comparerbtn').css('width', '330px');
    $('#popup_comparerbtn').css('bottom', '-30px');
  }
  $('#popup_comparerbtn').css('opacity', '0');
  $('#popup_comparer').show();
}

if(id === "articlevusbar") {
  if($(window).width() < 1199) {
    $('#articlevusbarbtn').css('width', '169px');
  }
  else {
   $('#articlevusbarbtn').css('width', '329px');
   $('#articlevusbarbtn').css('bottom', '-30px');
 }

 $('#articlevusbarbtn').css('opacity', '0');
 $('#articlevusbar').show();
}

if(id === "popup_config") {
  $('#popup_configbtn').css('opacity', '0');
  $('#popup_config').css('bottom', '0px');
  $('#popup_config').show();
}

  //document.getElementById(id+"fleche").className="fleche2";
  document.getElementById(id).style.bottom="10px";

}

$(window).on("scroll", function () {
  $(window).resize(function(){
    if($(window).width() < 1580 && $(window).width() > 971) {
      if ($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
        $('#articlebar').hide();
      }
      else {
        $('#articlebar').show();
      }
    }
    else {
      $('#articlebar').show();
    }
  });
  if($(window).width() < 1580 && $(window).width() > 971) {
    if ($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
      $('#articlebar').hide();
    }
    else {
      $('#articlebar').show();
    }
  }
  else {
    $('#articlebar').show();
  }
});

$(window).on("scroll", function () {
  $(window).resize(function(){
    if($(window).width() < 991 && $(window).width() > 760) {
      if ($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
        $('.btnvf').hide();
      }
      else {
         $('.btnvf').show();
      }
      }
        else {
         $('.btnvf').show();
      }
    })
   if($(window).width() < 991 && $(window).width() > 760) {
      if ($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
        $('.btnvf').hide();
      }
        else {
         $('.btnvf').show();
      }
      }
        else {
         $('.btnvf').show();
      }
});

function closebarform(id) {
 if(id === "popup_comparer") {
  $('#popup_comparerbtn').css('width', '169px');
  $('#popup_comparerbtn').css('opacity', '1');
  $('#popup_comparerbtn').css('bottom', '0');
  $('#popup_comparer').hide();
}
if(id === "popup_config") {
  $('#popup_configbtn').css('opacity', '1');
  $('#popup_configbtn').css('bottom', '0');
  $('#popup_config').hide();
}
if(id === "articlevusbar") {
  $('#articlevusbarbtn').css('width', '169px');
  $('#articlevusbarbtn').css('opacity', '1');
  $('#articlevusbarbtn').css('bottom', '0');
  $('#articlevusbar').hide();
}
}

function autoaide(delai){
 setTimeout("showaide(true)",parseInt(delai*1000));
}

function showaide(state){
  showhelpform();
  if(!state) setCookie("aide","1",1);
  if(document.getElementById('bulle_aide')) document.getElementById('bulle_aide').style.display=(state?"block":"none");
}


function toggleaide(){
  if(document.getElementById('bulle_aide').style.display == 'block'){
    $("#bulle_aide").slideUp(1000);
    $(".popup_contact").show(1000);
    $(".popup_contact_en").show(1000);
  } else {

    $("#bulle_aide").css('bottom', '0px');
    $(".popup_contact").hide();
    $(".popup_contact_en").hide();
    $("#bulle_aide").slideDown(1000);
    $("#bulle_aide").css('display', 'block');
    showhelpform();
  }
}

$(document).on("click", function(event){
  var $trigger = $("#bulle_aide");
  var $trigger2 = $("#prix_sur_demande");
  if($trigger !== event.target && !$trigger.has(event.target).length){
    var $trigger = $(".aide");
    if($trigger !== event.target && !$trigger.has(event.target).length && !$trigger2.has(event.target).length && $trigger2 !== event.target){
      $("#bulle_aide").slideUp(1000);
      $(".popup_contact").show();
      $(".popup_contact_en").show();
    }
  }
});

function showhelpform(id){
  if(id!=undefined) var display=document.getElementById(id).style.display;
  document.getElementById('rappelinst').style.display="none";
  // document.getElementById('minicontact').style.display="none";

  if(id!=undefined && display=="none"){
    document.getElementById(id).style.display="block";
    var h=$('#bulle_aide').height();
    $('#'+id).height(h).width(0).animate({width:335},500);
  }
}

function hidebarform(id) {
  //document.getElementById(id+'span').className = 'fa fa-caret-up fa-lg';
  rappelsihiding[id] = true;
  var max=document.getElementById(id).offsetHeight;
  if(document.getElementById(id).style.bottom=="") document.getElementById(id).style.bottom='0px';
  var h=parseInt(document.getElementById(id).style.bottom,10);
  
  if(h>-max){
    h-=15;
//     document.getElementById(id).style.height=h+"px";
document.getElementById(id).style.bottom=h+'px';
setTimeout("hidebarform('"+id+"')",10);
return;
}
  // document.getElementById(id).style.height="0px";
  // document.getElementById(id+"fleche").className="fleche";//
  rappelsihiding[id] = false;
}

function hideallbarforms(event){
  if(!document.getElementById('rappelinst')) return;
  var h=parseInt(document.getElementById('rappelinst').style.bottom,10);
  if (h>-100) hidebarform('rappelinst');

  // var h=parseInt(document.getElementById('minicontact').style.bottom,10);
  // if (h>-100) hidebarform('minicontact');

  if(document.getElementById('popup_comparer')){
    var h=parseInt(document.getElementById('popup_comparer').style.bottom,10);
    if(h>-25) hidebarform('popup_comparer');

  }
  if(document.getElementById('popup_config')){
    var h=parseInt(document.getElementById('popup_config').style.bottom,10);
    if(h>-50) hidebarform('popup_config');

  }
  if(document.getElementById('articlevusbar')){
    var h=parseInt(document.getElementById('articlevusbar').style.bottom,10);
    if (h>-25) hidebarform('articlevusbar');
  }
}

function donthideform(event){
  if(event.stopPropagation)
    event.stopPropagation();
  else
    window.event.cancelBubble = true;
}

var rappelsihiding = new Array();
var html=document.getElementsByTagName('html')[0];
if(html.addEventListener)
  html.addEventListener("click", hideallbarforms, false);
else
  html.attachEvent('onclick',hideallbarforms);


function showrappelinst(){
  var h=document.getElementById('rappelinst').offsetHeight;
  if(h<340){
    h+=20;
    document.getElementById('rappelinst').style.height=h+"px";
    setTimeout("showrappelinst()",50);
    return;
  }
  document.getElementById('rappelinst').style.height="";
  document.getElementById('rappelinst_form').style.display='block';
}

function checked_options(){
  var regexpr = 'checked_opt_';
  var liste_caractvalid = '';
  var identifiant = new RegExp(regexpr, "g");
  mesDiv = document.getElementsByTagName("input");

  for(var j=0; j<mesDiv.length; j++){
    var libelle = mesDiv[j].id;
    if (libelle.match(identifiant)){
      var caractvalid = document.getElementById(libelle).value;
      liste_caractvalid += ','+caractvalid;
    }
  }
  return liste_caractvalid;
}

function submitbarrappel(){
  var tel=document.getElementById('rappelinst_tel').value;
  var ville=document.getElementById('rappelinst_ville').value;
  var societe=document.getElementById('rappelinst_societe').value;
  var cp=document.getElementById('rappelinst_cp').value;
  var adresse=document.getElementById('rappelinst_adresse').value;
  var pays=document.getElementById('rappelinst_pays').value;
  var mail=document.getElementById('rappelinst_mail').value;
  var cgv =document.getElementById('rappelinst_cgv').getElementsByTagName('input')[0].checked;
  var gclid = document.getElementById('gclid_field').value;
  document.getElementById('rappelinst_error').style.display="none";
  if(tel=="" || pays=="" || ville=="" || !cgv || !testmail(mail)){
    document.getElementById('rappelinst_error').style.display="block";
    return;
  }

    //recuperation de options du produit, si demande sur fiche produit
    var liste_caractvalid = '';
    liste_caractvalid = checked_options();


    var post=new Array();
    if(liste_caractvalid != '') post['checked_opt'] = liste_caractvalid;
    post['gclid'] = gclid;
    post['rappelinst_tel']=tel;
    post['rappelinst_ville']=ville;
    post['rappelinst_adresse']=adresse;
    post['rappelinst_cp']=cp;
    post['rappelinst_societe']=societe;
    post['rappelinst_nom']=document.getElementById('rappelinst_nom').value;
    post['rappelinst_pays']=pays;
    post['rappelinst_mail']=document.getElementById('rappelinst_mail').value;
    post['url']=document.URL;
    document.getElementById('rappelinst_msg').innerHTML="<img src=\"/configurations/www.ma-cave-a-vin.fr/images/templates/ajax-loader.gif\" align=\"absmiddle\">";
    document.getElementById('rappelinst').getElementsByTagName('a')[1].style.display="none";
    document.getElementById('rappelinst_cgv').style.display="none";
    postcontact(post,rappelinstcb);
    
  }

  function rappelinstcb(httprequest){
    document.getElementById('rappelinst_msg').innerHTML=httprequest.responseText;
    setTimeout("hidebarform('rappelinst')",5000);
  }


  function submitbarcontact(){
    var tel=document.getElementById('minicontact_telephone').value;
    var ville=document.getElementById('minicontact_ville').value;
    var societe=document.getElementById('minicontact_societe').value;
    var cp=document.getElementById('minicontact_cp').value;
    var adresse=document.getElementById('minicontact_adresse').value;
    var pays=document.getElementById('minicontact_pays').value;
    var message=document.getElementById('minicontact_message').value;
    var cgv =document.getElementById('minicontact_cgv').getElementsByTagName('input')[0].checked;
    var gclid = document.getElementById('gclid_field').value;
    //document.getElementById('minicontact_besoin').style.display="none";
    document.getElementById('minicontact_error').style.display="none";
    if(tel=="" || pays=="" || ville=="" || message=="" || !cgv){
      document.getElementById('minicontact_error').style.display="block";
      return;
    }

    //recuperation de options du produit, si demande sur fiche produit
    var liste_caractvalid = '';
    liste_caractvalid = checked_options();

    var post=new Array();
    if(liste_caractvalid != '') post['checked_opt'] = liste_caractvalid;
    post['gclid'] = gclid;
    post['rappelinst_tel']=tel;
    post['rappelinst_nom']=document.getElementById('minicontact_nom').value;
    post['rappelinst_mail']=document.getElementById('minicontact_mail').value;
    post['rappelinst_ville']=ville;
    post['rappelinst_adresse']=adresse;
    post['rappelinst_cp']=cp;
    post['rappelinst_societe']=societe;
    post['rappelinst_pays']=pays;
    post['msg']=message
    post['url']=document.URL;
    document.getElementById('minicontact_form').innerHTML="<img src=\"/configurations/www.ma-cave-a-vin.fr/images/templates/ajax-loader.gif\" align=\"absmiddle\">";
    document.getElementById('minicontact').getElementsByTagName('a')[1].style.display="none";
    document.getElementById('minicontact_cgv').style.display='none';
    postcontact(post,minicontactcb);
  }

  function minicontactcb(httprequest){
    document.getElementById('minicontact_form').innerHTML=httprequest.responseText;
    setTimeout("hidebarform('minicontact')",5000);
    document.getElementById('minicontact_txt').style.display='none';
  }


  function submitrappelrem(){
    var tel=document.getElementById('rappelrem_tel').value;
    if(tel=="") return;
    
    var post=new Array();
    post['rappelinst_tel']=tel;
    post['rappelinst_nom']=document.getElementById('rappelrem_nom').value;
    if(document.getElementById('rappelinst_prenom')) post['rappelrem_prenom']=document.getElementById('rappelrem_prenom').value;
    post['rappelinst_mail']=document.getElementById('rappelrem_mail').value;
    post['url']=document.URL;
    document.getElementById('rappelrem_form').style.display='none';
    document.getElementById('rappelrem_msg').style.display='block';
    document.getElementById('rappelrem_msg').innerHTML="<img src=\"/images/templates/wait.gif\">";
    
    postcontact(post,rappelremcb);
  }

  function rappelremcb(httprequest){
    document.getElementById('rappelrem_msg').innerHTML=httprequest.responseText;
  }


  function postcontact(post,cb){
    ajaxrequest("/contact_ajax.php",cb,post);
  }

//Ajout/Suppression d'un produit au/du comparateur
function toggleCompare(artid){
    //clearTimeout(timeout);
    var rand = Math.floor(Math.random()*Math.pow(10,9))+Math.pow(10,6);
    ajaxrequest('/ajax/toggle_compare.php?artid='+artid+'&rand='+rand,compare_cb);

  }

  function compare_cb(httprequest){
    var arrayResponse = httprequest.responseText.split('|||');
    var artid = arrayResponse[0];
    var className = arrayResponse[1];
    var catComparateur = arrayResponse[2];
    var txtBtnCompare = arrayResponse[3];
    var catPdt = arrayResponse[4];
    var nbPdtsCat = arrayResponse[5];
    var nbPdtsTot = arrayResponse[6];
    var checkAjout = arrayResponse[7];
    var btn_article_txt = arrayResponse[8];
    var comparateur_txt = arrayResponse[9];
    var check = arrayResponse[10];

  //Changement du style du bouton en fonction du résultat
  if(artid > 0){
    if(document.getElementById('btn_comparaison')){
      var btn1 = document.getElementById('btn_comparaison');
      btn1.className=className+' btn_comparer';
      var btn_check = document.createElement('i');
      btn_check.className = check;
      btn1.firstChild.remove();
      btn1.prepend(btn_check);
      var span=btn1.getElementsByTagName('span')[0];
      span.innerHTML=btn_article_txt;
    }
    if(document.getElementById('btn_comparaison'+artid)){
      $('body #btn_comparaison'+artid).children('i').remove();
      $('body #btn_comparaison'+artid).prepend('<i class="'+check+'"></i>');
      $('body #btn_comparaison'+artid).removeClass('btn_comparer_off btn_comparer_on').addClass( className );
      $('body #btn_comparaison_p'+artid).children('i').remove();
      $('body #btn_comparaison_p'+artid).prepend('<i class="'+check+'"></i>');
      $('body #btn_comparaison_p'+artid).removeClass('btn_comparer_off btn_comparer_on').addClass( className );
      $('body #span_comparaison'+artid).text(btn_article_txt);
      $('body #span_comparaison_p'+artid).text(btn_article_txt);
    }

            //Ouverture de la popup quand un produit lors de l'ajout d'un produit, si la popup est fermée
            if(checkAjout === '1'){
              clearTimeout(timeout);
              var timeout = setTimeout('hidebarform("popup_comparer")', 4000);

              var popup_pos=parseInt(document.getElementById('popup_comparer').style.bottom);
              if(isNaN(popup_pos) || popup_pos<=0){
                showbarform('popup_comparer');
              }
            }
            
            //Fermeture de la popup lors de la suppression d'un produit, si la popup est ouverte
            if(checkAjout === '2' && document.getElementById('popup_comparer').style.display === 'block'){
              hidebarform('popup_comparer');
            }
          }

          $('#nb_pdts_comparateur').html(txtBtnCompare);

        //Changement des intitulés en fonctions du nombre de produits encore présents dans le comparateur
        if(document.getElementById('txt_comparateur'))
          document.getElementById('txt_comparateur').innerHTML=comparateur_txt;

        //Si ajout de produit, on affiche le nouveau produit ajouté dans la popup
        if(artid > 0 && document.getElementById('article_ajoute')){
          ajaxrequest('/ajax/affiche_article_compare.php?artid='+artid,affiche_article_cb);
          document.getElementById('ajout_comparateur').style.display=(checkAjout == '1' ? "block" : "none");
        }

        $('#trop').hide();
        if(checkAjout==='3' && document.getElementById('trop')){
          $('#trop').show();
          clearTimeout(timeout);
          var timeout = setTimeout('hidebarform("popup_comparer")', 4000);

          var popup_pos=parseInt(document.getElementById('popup_comparer').style.bottom);
          if(isNaN(popup_pos) || popup_pos<=0){
            showbarform('popup_comparer');
          }
        }
        
        //Remplacement du contenu de la div comparaison footer
        if(document.getElementById('comparer_categorie'))
         document.getElementById('comparer_categorie').innerHTML=catComparateur;

       if(document.getElementById('comparateur')){
        if(nbPdtsCat > 0){
          var tbtitle=document.getElementById('TB_ajaxWindowTitle').innerHTML;
          tb_show(tbtitle,'/comparateur.php?width=945&typearticleid='+catPdt,false);
        } else {
          tb_remove();
        }
      }
    }

    function affiche_article_cb(httprequest){
     document.getElementById('article_ajoute').innerHTML = httprequest.responseText;
   }

   function vider_comparateur(typearticleid){
    var rand = Math.floor(Math.random()*Math.pow(10,9))+Math.pow(10,6);
    ajaxrequest("/ajax/clean_compare.php?typearticleid="+typearticleid+"&rand="+rand, vider_cb);

  }

  function vider_config(s){
    ajaxrequest("/ajax/clean_config.php?s="+s, vider_div_config);
  }

  function vider_artvu(s){
    ajaxrequest("/ajax/clean_artvu.php?s="+s, vider_div_artvu);
  }

  function vider_div_config(httprequest) {
    $("#"+httprequest.responseText).find('.msg').hide();
    $('#popup_configbtn').find('#nb_configs').text('0');
  }

  function vider_div_artvu(httprequest) {
    $('#articlevusbarbtn').css('width', '169px');
    $('#articlevusbarbtn').css('opacity', '1');
    $('#articlevusbarbtn').css('bottom', '0');
    $('#articlevusbar').hide();
    $("#"+httprequest.responseText).find('.artvu').hide();
    $('#articlevusbarbtn').find('#nb_configs').text('0');
  }


  function vider_cb(httprequest){
    var data=httprequest.responseText.split("|||");
    var del=data[0].split(",");
    var btn_txt=data[1];
    
    for(i=0;i<del.length;i++){
      if(document.getElementById('btn_comparaison'+del[i])){
        document.getElementById('btn_comparaison'+del[i]).className="btn_comparer_off btn_comparer";
        document.getElementById('span_comparaison'+del[i]).innerHTML=btn_txt;
        $('body #btn_comparaison'+del[i]).children('i').remove();
        $('body #btn_comparaison'+del[i]).prepend('<i class="far fa-square"></i>');
      }
    }
    
    if(document.getElementById('btn_comparaison')){
      document.getElementById('btn_comparaison').className="btn_comparer_off btn_comparer";
      document.getElementById('btn_comparaison').innerHTML=btn_txt;

    }
    
  //Mise à jour de la popup du footer
  toggleCompare(0);

  //Fermeture du comparateur
  tb_remove();
  $("#ajout_comparateur").css('display', 'none');
}

function comparateur_load(){
  toggleCompare(0);
}

  if($(".btnvf").css('display') === 'block') {
     $(window).resize(function(){
       if($(window).width() < 1199) {
    $("#articlebar .center .button").css('margin', '0 2px 0 0');
  }
  else {
     $("#articlebar .center .button").css('margin', '0 10px 0 0');
  }
});
          if($(window).width() < 1199) {
    $("#articlebar .center .button").css('margin', '0 2px 0 0');
  }
  else {
     $("#articlebar .center .button").css('margin', '0 10px 0 0');
  }
}
