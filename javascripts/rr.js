  var rrint=0;

  function rrprop(e){
    var rr=$(".rechercherapide")[0].value;
    //if(rr.length<=3) return;
    if(rr.length<=3) {
      $(".btn-rechercher").prop('disabled', true);
      return;
    }
    else {
     $(".btn-rechercher").prop('disabled', false);
    }
    var TouchKeyUp = e.keyCode? e.keyCode : e.charCode;
    if(TouchKeyUp==13){
     rechercherapide();
     return;
   }

      /*document.getElementById('divprop').style.left=document.getElementById('whiteboxrr').offsetLeft+'px';
      document.getElementById('divprop').style.top=(22+document.getElementById('whiteboxrr').offsetTop)+'px';*/
      $('#divprop').html="<div class=\"cadre_recherche\"></div><img class=\"loader\" src=\"/images/templates/ajax-loader.gif\" align=\"absmiddle\"><p class=\"loader\">Recherche en cours...</p>";
      if(rrint!=0) clearTimeout(rrint);
      rrint=setTimeout("rrrequest(\""+rr+"\")",100);
    }

    function rrrequest(txt){
      ajaxrequest("/rr.php?txt="+txt,rrcallback);
    }

    function rrcallback(httprequest){

      if(httprequest.responseText!=""){
        //document.getElementById('divprop').innerHTML="<div class=\"close\"onclick=\"document.getElementById('divprop').style.display='none';\"></div>"+httprequest.responseText;
        $('#divprop').html(httprequest.responseText);
      }else{
        //document.getElementById('divprop').innerHTML="<div class=\"cadre_recherche\"></div><p class=\"loader\" onclick=\"document.getElementById('divprop').style.display='none';\">Aucune suggestion</p>";
        $('#divprop').html="<div class=\"cat\" onclick=\"document.getElementById('divprop').style.display='none';\"><span class=\"line2\"></span><strong>Aucune suggestion</strong><span class=\"line2\"></span></div>";
      }
      //Affiche la divprop en déroulement vers le bas
      //$('#divprop').slideDown(500);
      $("#divprop").css('display', 'block');
      $("#divprop").css('padding-top', '40px');
      if ($(window).width() < 767) {
       $(".croix").css('display', 'block');
     }
     if($(window).width() < 991) {
      $(".item.cat-recherche.item-nb4").css('display', 'none');
      $(".item.cat-recherche.item-nb5").css('display', 'none');
      $(".item.cat-recherche.item-nb6").css('display', 'none');
      $(".articles-result.item-nb4").css('display', 'none');
      $(".articles-result.item-nb5").css('display', 'none');
      $(".articles-result.item-nb6").css('display', 'none');
      $(".item.item-marques.item-nb5").css('display', 'none');
      $(".item.item-marques.item-nb6").css('display', 'none');
    }

    else {
      $(".item.cat-recherche.item-nb4").css('display', 'block');
      $(".item.cat-recherche.item-nb5").css('display', 'block');
      $(".item.cat-recherche.item-nb6").css('display', 'block');
      $(".articles-result.item-nb4").css('display', 'flex');
      $(".articles-result.item-nb5").css('display', 'flex');
      $(".articles-result.item-nb6").css('display', 'flex');
      $(".item.item-marques.item-nb5").css('display', 'block');
      $(".item.item-marques.item-nb6").css('display', 'block');
    }

  }

  function selectrr(txt){
   $(".rechercherapide")[0].value=txt;
   $('#divprop').css('display', 'none');
   $('#ok_rr').css('display', 'none');
   $('#wait_rr').css('display', 'inline');
   document.forms.rechercheform.submit();
 }

 if($(window).width() < 991) {
  $(".item.cat-recherche.item-nb4").css('display', 'none');
  $(".item.cat-recherche.item-nb5").css('display', 'none');
  $(".item.cat-recherche.item-nb6").css('display', 'none');
  $(".articles-result.item-nb4").css('display', 'none');
  $(".articles-result.item-nb5").css('display', 'none');
  $(".articles-result.item-nb6").css('display', 'none');
  $(".item.item-marques.item-nb5").css('display', 'none');
  $(".item.item-marques.item-nb6").css('display', 'none');
}
else if($(window).width() < 767) {
if($(".bloc-recherche").css("display") === 'block' && $("#divprop").css("display") === 'none') {
    $(".croix").css('display', 'none');
  }
  if($("#divprop").css("display") === 'block') {
    $(".croix").css('display', 'block');
  }
}
else {
  $(".item.cat-recherche.item-nb4").css('display', 'block');
  $(".item.cat-recherche.item-nb5").css('display', 'block');
  $(".item.cat-recherche.item-nb6").css('display', 'block');
  $(".articles-result.item-nb4").css('display', 'flex');
  $(".articles-result.item-nb5").css('display', 'flex');
  $(".articles-result.item-nb6").css('display', 'flex');
  $(".item.item-marques.item-nb5").css('display', 'block');
  $(".item.item-marques.item-nb6").css('display', 'block');
  if($(".bloc-recherche").css("display") === 'block') {
    $(".croix").css('display', 'block');
  }
  else {
    $(".croix").css('display', 'none');
  }
}

$( window ).resize(function() {
  if($(window).width() < 991) {
    $(".item.cat-recherche.item-nb4").css('display', 'none');
    $(".item.cat-recherche.item-nb5").css('display', 'none');
    $(".item.cat-recherche.item-nb6").css('display', 'none');
    $(".articles-result.item-nb4").css('display', 'none');
    $(".articles-result.item-nb5").css('display', 'none');
    $(".articles-result.item-nb6").css('display', 'none');
    $(".item.item-marques.item-nb5").css('display', 'none');
    $(".item.item-marques.item-nb6").css('display', 'none');
}
else if($(window).width() < 767) {
if($(".bloc-recherche").css("display") === 'block' && $("#divprop").css("display") === 'none') {
    $(".croix").css('display', 'none');
  }
  if($("#divprop").css("display") === 'block') {
    $(".croix").css('display', 'block');
  }
}
else {
  $(".item.cat-recherche.item-nb4").css('display', 'block');
  $(".item.cat-recherche.item-nb5").css('display', 'block');
  $(".item.cat-recherche.item-nb6").css('display', 'block');
  $(".articles-result.item-nb4").css('display', 'flex');
  $(".articles-result.item-nb5").css('display', 'flex');
  $(".articles-result.item-nb6").css('display', 'flex');
  $(".item.item-marques.item-nb5").css('display', 'block');
  $(".item.item-marques.item-nb6").css('display', 'block');
  if($(".bloc-recherche").css("display") === 'block') {
    $(".croix").css('display', 'block');
  }
  else {
    $(".croix").css('display', 'none');
  }
}
})

function rrclick(){

if($("#boxrrstatic").css('display') === 'none') {
      $("#menu-niveau-1-6 ").css('z-index', '0');
    $("#whiteboxrr").prop('disabled', true);
      $("#boxrrstatic").show();
 
}
else {
   $("#whiteboxrr").prop('disabled', false);
     $("#boxrrstatic").hide();
       $("#menu-niveau-1-6 ").css('z-index', '100');
}


  $(".bloc-recherche").toggle();
  $(".fond-sombre").toggle();

  if($(window).width() > 767) {
    $(".croix").css('display', 'block');
  }

  if($(window).width() < 767 && $('#divprop').css('display') === 'block') {
   $(".croix").css('display', 'none');
 }
 if($(window).width() < 767 && $('#divprop').html() != '') {
   $(".croix").css('display', 'block');
 }

    //Si quand on clique la divprop est affichée, on doit la fermer. Et on fait remonter le container
    if($('#divprop').css('display') === 'block' && $('#divprop').html() != '') {
      $("#divprop").css('display', 'none');
      //$("#divprop").css('padding-top', '0px');
    }
    else if($('#divprop').css('display') === 'none' && $('#divprop').html() != '') {
      //$('#divprop').slideDown(500);
      $("#divprop").css('display', 'block');
      $("#divprop").css('padding-top', '40px !important');
      $('#divprop').css('padding-bottom', '27px !important');
    }

   //Focus dans l'input, permet d'écrire directement dans la recherche
   $(".rechercherapide").focus();

}


$('.fond-sombre').click(function(e) {
  if (e.target.class != 'bloc-recherche') {
    rrclick();
  }
});


function rrsearch(){
  var boxvalue=document.getElementsByName('rechercherapide')[0].value;
  document.getElementsByName('rechercherapide')[0].style.color='black';
    //document.getElementById('divprop').style.display='none';
  }

  function rechercherapide(){

    var rr=$(".rechercherapide")[0].value;
    if(rr.length<3) return;
    document.location="/recherche-rapide.php?action="+rr
  }

  //enregistre le mot clé recherché dans la base de donnée (table stat_recherches)
  function saveMotCle(text){
    $.ajax({           
      url : '/ajax/saveMotCle.php',
      dataType : 'text',
      type : 'GET',
      data : 'txt='+ text
    })
  }


  //pour changer la couleur des blocs 'marques' / 'categories' / 'produits' selon la page choisi, sans recharger la page
  var url = window.location.href;
  if(url.includes('&actif=marques')) {
    redirection_choix('marques');
  }
  else if(url.includes('&actif=produits')) {
    redirection_choix('produits');
  }
  else if(url.includes('&actif=cats')) {
    redirection_choix('cats');
  }
  else if(url.includes('/recherche-rapide.php?action=') && url.includes('&actif=') === false) {
    redirection_choix('produits');
     if($(window).width() < 767) {
      $(".container.container-main").css('padding', '0px');
    }
  }

  function redirection_choix(choix) {

    if(choix === "produits") {
      var url = window.location.href;
      url = url.replace('marques', 'produits', url);
      url = url.replace('cats', 'produits', url);
    window.history.pushState("", "", url); //modifie l'url sans charger la page
    $(".barre_filtre").show();
    $(".resultats_par_marques").hide();
    $(".resultats_par_cats").hide();
    $(".resultats_par_articles").show();
    $("#choix_produits").css({backgroundColor: 'black', color: 'white'});
    $("#choix_cat").css({backgroundColor: 'rgba(0,0,0,0)', color: 'black'});
    $("#choix_marques").css({backgroundColor: 'rgba(0,0,0,0)', color: 'black'});
    if($(window).width() < 767) {
      $(".container.container-main").css('padding', '0px');
    }
  }
  if(choix === "marques") {
    var url = window.location.href;
    url = url.replace('cats', 'marques', url);
    url = url.replace('produits', 'marques', url);
    window.history.pushState("", "", url);
    $(".barre_filtre").hide();
    $(".resultats_par_marques").show();
    $(".resultats_par_cats").hide();
    $(".resultats_par_articles").hide();
    $("#choix_produits").css({backgroundColor: 'rgba(0,0,0,0)', color: 'black'});
    $("#choix_cat").css({backgroundColor: 'rgba(0,0,0,0)', color: 'black'});
    $("#choix_marques").css({backgroundColor: 'black', color: 'white'});
    if($(window).width() < 767) {
      $(".container.container-main").css('padding', '0px');
    }
  }
  if(choix === "cats") {
    var url = window.location.href;
    url = url.replace('marques', 'cats', url);
    url = url.replace('produits', 'cats', url);
    window.history.pushState("", "", url);
    $(".barre_filtre").hide();
    $(".resultats_par_marques").hide();
    $(".resultats_par_cats").show();
    $(".resultats_par_articles").hide();
    $("#choix_produits").css({backgroundColor: 'rgba(0,0,0,0)', color: 'black'});
    $("#choix_cat").css({backgroundColor: 'black', color: 'white'});
    $("#choix_marques").css({backgroundColor: 'rgba(0,0,0,0)', color: 'black'});
    if($(window).width() < 767) {
      $(".container.container-main").css('padding', '0px');
    }
  }
}
