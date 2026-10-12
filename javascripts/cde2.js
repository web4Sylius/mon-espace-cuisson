var check_delay = 200;
var tab_req     = new Array();
var max_req     = 1;
var max_var     = 3;

function selectChoix(trspttype,factureid) {
  for(i=0;i<=7;i++){
    if(document.getElementById('livrtype'+i)){
      if(trspttype==i){
        if(document.getElementById('livrtype'+i).className.match(/selected/)==undefined){
          document.getElementById('livrtype'+i).className+=" selected";
          $('.fa-dot-circle').removeClass('fa-dot-circle').addClass('fa-circle');
          $('#livrtype'+i+' .fa-circle').removeClass('fa-circle').addClass('fa-dot-circle');
        }
      }else
        document.getElementById('livrtype'+i).className=document.getElementById('livrtype'+i).className.replace(" selected","");

      var scroll=!(document.getElementById('rlivrtype'+i).checked) && (trspttype==i);
      document.getElementById('rlivrtype'+i).checked=(trspttype==i);
      if(!document.getElementById('valid_step1')){
        document.getElementById('opts'+i).className="opts"+(trspttype==i?" show":"");
        if(scroll){
          ss.scrollToDiv("livrtype"+trspttype,-100);
        }
      }
    }
  }
  document.cookie = "trspt=" + trspttype + ";" ;
  ajaxrequest("cde2_ajax.php?factureid="+factureid+"&type="+trspttype,checkResponseLivr);
}


function checkResponseLivr(httprequest) {

}

var destdiv=false;
function loadform(langue,formid,modelivraison,div,affiche_options,facture_id,div_options){
  document.getElementById('choix11').innerHTML="";
  document.getElementById('choix12').innerHTML="";
  if(document.getElementById('choix21')) document.getElementById('choix21').innerHTML="";
  if(document.getElementById('choix22')) document.getElementById('choix22').innerHTML="";
  if(document.getElementById('choix71')) document.getElementById('choix71').innerHTML="";
  if(document.getElementById('choix72')) document.getElementById('choix72').innerHTML="";
  if(document.getElementById('choix31')) document.getElementById('choix31').innerHTML="";
  if(document.getElementById('choix32')) document.getElementById('choix32').innerHTML="";
  if(document.getElementById('choix41')) document.getElementById('choix41').innerHTML="";
  if(document.getElementById('choix42')) document.getElementById('choix42').innerHTML="";
  if(document.getElementById('choix51')) document.getElementById('choix51').innerHTML="";
  if(document.getElementById('choix52')) document.getElementById('choix52').innerHTML="";
  destdiv=div;
  ajaxrequest("cde2_form.php?formid="+formid+"&modelivraison="+modelivraison+"&affiche_options="+affiche_options+"&div_options="+div_options+"&facture_id="+facture_id,loadform_cb);
//       document.getElementById('nextstep').style.display=(formid==1?"block":"none");
//document.getElementById(destdiv).innerHTML="<img src=\"/images/loader.gif\" class=\"wait desktop\">";
document.getElementById('opt'+div).checked=true;
}
function loadform_cb(httprequest){
  if(!document.getElementById(destdiv)) return false;
  document.getElementById(destdiv).innerHTML=httprequest.responseText;
}

function veriflivr(type){
  if(type == 2){

    var err=false;
    var err2=false;

    document.getElementById('errnom').style.display="none";
    document.getElementById('errprenom').style.display="none";
    document.getElementById('erradresse').style.display="none";
    document.getElementById('errcp').style.display="none";
    document.getElementById('errville').style.display="none";
    document.getElementById('errtel').style.display="none";
    document.getElementById('errpays').style.display="none";
    document.getElementById('errmail').style.display="none";
    document.getElementsByName('telephone')[0].style.border='';
    document.getElementsByName('nom')[0].style.border='';
    document.getElementsByName('prenom')[0].style.border='';
    document.getElementsByName('adresse')[0].style.border='';
    document.getElementsByName('codepostal')[0].style.border='';
    document.getElementsByName('ville')[0].style.border='';
    document.getElementsByName('mail')[0].style.border='';
    document.getElementsByName('monpays')[0].style.border='';

    if(document.getElementsByName('nom')[0].value=="") {
      err=true;
      document.getElementById('errnom').style.display="table-cell";
      document.getElementsByName('nom')[0].style.border='solid 1px red';
    }
    if(document.getElementsByName('prenom')[0].value=="") {
      err=true;
      document.getElementById('errprenom').style.display="table-cell";
      document.getElementsByName('prenom')[0].style.border='solid 1px red';
    }
    if(document.getElementsByName('adresse')[0].value=="") {
      err=true;
      document.getElementById('erradresse').style.display="table-cell";
      document.getElementsByName('adresse')[0].style.border='solid 1px red';
    }
    if(document.getElementsByName('codepostal')[0].value=="") {
      err=true;
      document.getElementById('errcp').style.display="table-cell";
      document.getElementsByName('codepostal')[0].style.border='solid 1px red';
    }
    if(document.getElementsByName('ville')[0].value=="") {
      err=true;
      document.getElementById('errville').style.display="table-cell";
      document.getElementsByName('ville')[0].style.border='solid 1px red';
    }
    if(document.getElementsByName('telephone')[0].value=="") {
      err=true;
      document.getElementById('errtel').style.display="table-cell";
      document.getElementsByName('telephone')[0].style.border='solid 1px red';
    }
    if(document.getElementsByName('mail')[0].value.length<4) {
      err=true;
      document.getElementById('errmail').style.display="table-cell";
      document.getElementsByName('mail')[0].style.border='solid 1px red';
    }
    if(document.getElementsByName('monpays')[0].value=="") {
      err=true;
      document.getElementById('errpays').style.display="table-cell";
      document.getElementsByName('monpays')[0].style.border='solid 1px red';
    }
    if(document.getElementsByName('fac_adr')[0].value == "") {
      err=true;
      err_client();
    }
    if(document.getElementsByName('fac_cp')[0].value == "") {
      err=true;
      err_client();
    }
    if(document.getElementsByName('fac_ville')[0].value == "") {
      err=true;
      err_client();
    }

    if(!err) {
      //if(!facture_id){
           submitcde2();
      //} else {
        //   affiche_encart_options(facture_id, 'installation'+div_options);
          // cache_div('choix'+div_options);
      //}
    }

  }else if(type == 1){
    var err=false;
    document.getElementById('err_client').style.display="none";

    if(document.getElementsByName('adresse')[0].value == "")
      err=true;
    if(document.getElementsByName('codepostal')[0].value == "")
      err=true;
    if(document.getElementsByName('ville')[0].value == "")
      err=true;

    if(!err){
      document.getElementById('err_client').style.display="none";
      submitcde2();
    }else err_client();  
  }
}

function err_client(){
  document.getElementById('err_client').style.display="block";
  for(i=0;i<=4;i++){
    if(document.getElementById('livrtype'+i)){
      if(document.getElementById('livrtype'+i).className.match(/selected/)){
        document.getElementById('opts'+i).style.display = "none";
        break;
      }
    }
  }
  ss.scrollToDiv("whiteboxrr",-100);
}

function submitcde2(){

 document.forms.cde2form.submit();
}

function submitcde25()
{
    var moyenchecked = false;

    for(i=0;i<document.getElementsByName('moyen').length;i++)
    {
        if(document.getElementsByName('moyen')[i].checked)
        {
            moyenchecked = true;
            var moyenid = document.getElementsByName('moyen')[i].value;
            
            if(document.getElementById('delpanier'+moyenid) && document.getElementById('delpanier'+moyenid).value==1)
            {
                var sign = "?";
                if(document.forms.cde25form.action.indexOf("?")>-1) sign = "&";
                document.forms.cde25form.action += sign + "action=delpanier_complet";
            }
        }
    }

    //on enregitre le gclid 
    var gclid = document.getElementById('gclid_field').value;
    var gclid1 = '';
    if(gclid != '') gclid1 = '&gclid='+gclid;

    document.getElementById('errmoy').style.display = (moyenchecked ? "none" : "block");
    if(!moyenchecked) return false;
    var acceptcgv = document.getElementsByName('acceptecgv')[0].checked;
    document.getElementById('errcgv').style.display = (acceptcgv ? "none" : "block");
    if(!acceptcgv) return false;

    //Avant de soumettre le formulaire, on vérifie (en ajax) que la commande n'a pas déjà été traitée
    ajaxrequest("ajax/check_traitement_cmd.php?facture_id="+document.getElementsByName('factureid')[0].value+gclid1,submitcde25_cb);
}

function submitcde25_cb(httprequest)
{
    var reponse = httprequest.responseText;

    if(reponse == 'ok')
    {
        document.forms.cde25form.submit();
    } 
    else 
    {
        document.getElementById('errmoy').innerHTML = 'Votre commande a déjà été traité';
        document.getElementById('errmoy').style.display = 'block';
    }
}

var typegarantie;
function select_destinataire_garantie(facture_id, destinataire_id){
  typegarantie=destinataire_id;
  for(i=0;i<=2;i++){
   document.getElementById('dg'+i).className="destinataire"+(destinataire_id==i?" selected":"");
   document.getElementById('liste_garanties'+i).innerHTML="";
 }
 var radio=document.getElementsByName("destinataire_garantie")[destinataire_id];
 radio.checked=true;

 //document.getElementById('liste_garanties'+destinataire_id).innerHTML = "<img src=\"/images/loader.gif\" class=\"wait desktop\">";

 ajaxrequest("ajax/affiche_garanties.php?facture_id="+facture_id+"&destinataire_id="+destinataire_id,affiche_encart_garanties_cb);
}

function affiche_encart_garanties_cb(httprequest){

 if(document.getElementById('liste_garanties_step2'))
   document.getElementById('liste_garanties_step2').innerHTML=httprequest.responseText;
 else
   document.getElementById('liste_garanties'+typegarantie).innerHTML = httprequest.responseText;

 document.getElementsByClassName('next_step_garantie')[0].style.display = 'block';
}

function choisigarantie(lfid,garantieid){
  var parent=document.getElementById("bloc_garantie_"+lfid+"_"+garantieid).parentNode;
  for(i=0;i<parent.childNodes.length;i++){
   var elem=parent.childNodes[i];
   var re=new RegExp("bloc_garantie_"+lfid+"_[0-9]+","g");
   if(elem.id && elem.id.match(re))
     elem.className=elem.className.replace(" selected","");
 }
 document.getElementById("bloc_garantie_"+lfid+"_"+garantieid).className+=" selected";
 document.getElementById("garantie_"+lfid+"_"+garantieid).checked=true;
 if(document.getElementById('selectph_'+lfid) != null){
    document.getElementById('selectph_'+lfid).value = 0;
    $('#form_platine_home_'+lfid).hide();
    $('#validForm1').show();

 }
}

function livr_step2(){
  var radiobtns=document.getElementsByName("rlivrtype");
  var checked=false;
  for(i=0;i<radiobtns.length;i++){
   if(radiobtns[i].checked){
     checked=radiobtns[i].id.match(/[0-9]/)[0];
   }
 }
 if(!checked){
//  document.getElementById('errmodelivr').style.display="block";
document.location="#errmodelivr";
htmlalert(document.getElementById('errmodelivr').innerHTML);
}else{
  document.getElementById("livr_step1").style.display="none";
  document.getElementById("valid_step1").style.display="none";
  document.getElementById("opts"+checked).className+=" show";
  document.getElementById("step1_btn").style.display="block";
}
}

function livr_step1(){
  document.getElementById("livr_step1").style.display="block";
  document.getElementById("valid_step1").style.display="block";
  document.getElementById("step1_btn").style.display="none";
  for(i=0;i<=4;i++)
   if(document.getElementById("opts"+i))
     document.getElementById("opts"+i).className="opts";
 }

 function garantie_step2(){
  if(document.getElementsByName('action2').length==0){
   document.forms.frmchoixGarantie.submit();
 }else{
   document.getElementById('liste_garanties_step2').style.display="block";
   document.getElementById('valid_step2').style.display="block";
   document.getElementById('liste_garanties_step1').style.display="none";
   document.getElementById("step1_btn").style.display="block";
 }
}

function garantie_step1(){
  document.getElementById('liste_garanties_step2').style.display="none";
  document.getElementById('valid_step2').style.display="none";
  document.getElementById('liste_garanties_step1').style.display="block";
  document.getElementById("step1_btn").style.display="none";
}

function validIpid(){
  if(!document.getElementById('validipid').checked){
    htmlalert(document.getElementById('ipiderr').value);
  }else{
    $('#ipidfond').hide();
  }

}

function cancelIpid(){
  $('#ipidfond').hide();
  var lfid = $('#cancelIpid').val();
  if($('#selectph_'+lfid) != null){
    $('#selectph_'+lfid).val(0);
 }
 $('#form_platine_home_'+lfid).hide();
 $('.bloc_garantie_base_'+lfid).addClass('selected');
 $('.bloc_garantie_base_'+lfid+' input:radio').prop("checked", true);
 $('.bloc_garantie_sup_'+lfid).removeClass('selected');
 $('.bloc_garantie_sup_'+lfid+' input:radio').prop('checked', false);
}

function checkph(lfid){
  $('#form_platine_home_'+lfid).toggle();
  $('#selectph_'+lfid).val(1);
  $('#validipid').prop("checked", false);
  $('#ipidfond').fadeIn(500);
  $('#ipid').scrollTop(0);
  $("#cancelIpid").val(lfid);
}

function checkGarantieMulti(){

  var err ='';
  var lfid = document.getElementsByName('lfid');
  var regexTel = new RegExp("^(0[67])(?:[ _.-]?(\\d{2})){4}$");
  var regexdatejj = new RegExp("^\\d{2}$");
  var regexdatemm = new RegExp("^\\d{2}$");
  var regexdateaa = new RegExp("^\\d{4}$");
  var y ;
  for(y = 0; y < lfid.length; y++){
    var lfidval = lfid[y].value;
    var selectph = document.getElementById('selectph_'+lfidval);
    var iban = document.getElementById('ibanph_'+lfidval);
    var telephone = document.getElementById('telephoneph_'+lfidval);
    var naissancejj = document.getElementById('datenaissancephjj_'+lfidval);
    var naissancemm = document.getElementById('datenaissancephmm_'+lfidval);
    var naissanceaa = document.getElementById('datenaissancephaa_'+lfidval);
    var accepte1 = document.getElementById('accepte1_'+lfidval);
    var accepte2 = document.getElementById('accepte2_'+lfidval);

    if(selectph != null && selectph.value == '1' ){
      if(!regexTel.test(telephone.value)) err += "<i class='fa fa-exclamation-triangle'></i>"+document.getElementById('telephonepherr_'+lfidval).value+"<br>";
      if(!regexdatejj.test(naissancejj.value) || !regexdatemm.test(naissancemm.value) || !regexdateaa.test(naissanceaa.value))  err +="<i class='fa fa-exclamation-triangle'></i>"+document.getElementById('datenaissancepherr_'+lfidval).value+"<br>";
      ibanf = iban.value.trim();
      ibanf = ibanf.replace(/ /gi,'');
      iban.value = ibanf;
      if(!IBAN.isValid(ibanf)) err +="<i class='fa fa-exclamation-triangle'></i>"+document.getElementById('ibanpherr_'+lfidval).value+"<br>";
      if(!accepte1.checked || !accepte2.checked) err +="<i class='fa fa-exclamation-triangle'></i>"+document.getElementById('accepteerr_'+lfidval).value;
    }
  }
  if(err != '')
      htmlalert("<p>"+err+"</p>");
  else
      document.forms.frmchoixGarantie.submit();
}

function validinfoIpid(lfid){
  var err ='';
  var regexTel = new RegExp("^(0[67])(?:[ _.-]?(\\d{2})){4}$");
  var regexdatejj = new RegExp("^\\d{2}$");
  var regexdatemm = new RegExp("^\\d{2}$");
  var regexdateaa = new RegExp("^\\d{4}$");
  //var selectph = document.getElementsByName('selectph');
  var iban = document.getElementById('ibanph_'+lfid);
  var telephone = document.getElementById('telephoneph_'+lfid);
  var naissancejj = document.getElementById('datenaissancephjj_'+lfid);
  var naissancemm = document.getElementById('datenaissancephmm_'+lfid);
  var naissanceaa = document.getElementById('datenaissancephaa_'+lfid);
  var accepte1 = document.getElementById('accepte1_'+lfid);
  var accepte2 = document.getElementById('accepte2_'+lfid);

  if(!regexTel.test(telephone.value)) err += "<i class='fa fa-exclamation-triangle'></i>"+document.getElementById('telephonepherr_'+lfid).value+"<br>";
  if(!regexdatejj.test(naissancejj.value) || !regexdatemm.test(naissancemm.value) || !regexdateaa.test(naissanceaa.value))  err +="<i class='fa fa-exclamation-triangle'></i>"+document.getElementById('datenaissancepherr_'+lfid).value+"<br>";
  ibanf = iban.value.trim();
  ibanf = ibanf.replace(/ /gi,'');
  iban.value = ibanf;
  if(!IBAN.isValid(ibanf)) err +="<i class='fa fa-exclamation-triangle'></i>"+document.getElementById('ibanpherr_'+lfid).value+"<br>";
  if(!accepte1.checked || !accepte2.checked) err +="<i class='fa fa-exclamation-triangle'></i>"+document.getElementById('accepteerr_'+lfid).value;

  if(err != '')
      htmlalert("<p>"+err+"</p>");
  else
      document.getElementById('form_platine_home_'+lfid).style.display = "none";
}

function edittxtlivr(id){
  var txt = document.getElementById(id).innerHTML;
  document.getElementById('txt5').innerHTML = '';
  document.getElementById('txt5').innerHTML = txt;

}

function txtincoterms(val,modelivraison){
  document.getElementById('txt'+modelivraison).innerHTML = document.getElementById(val).innerHTML;
}
function check_informations_livraison(facture_id, div_id){
     
     var modechecked = false;
     var err         = false;
     var radios      = document.getElementsByName("choixlivr");
     
     for(i=0;i<radios.length;i++){
          if(radios[i].checked) modechecked = true;
     }
     
     if(document.getElementsByName('telephone')[0].value.length < 6){
          
          err = true;
          
          document.getElementById('errtel').className="error";
          document.getElementsByName('telephone')[0].style.borderColor='red';
     }
     
     if(document.getElementsByName('mail')[0].value.length < 6){
          
          err = true;
          
          document.getElementById('errmail').className="error";
          document.getElementsByName('mail')[0].style.borderColor='red';
     }
      
     if(modechecked && !err){
           
          if(facture_id && div_id){
               
               affiche_encart_options(facture_id, 'installation'+div_id);
               cache_div('choix'+div_id);
          } else {
               
               submitcde2();
          }
          
     } else {
          
          document.getElementById('errmodelivr').style.display="block";
     }
  
}

function toggle_installation(state){
     
     switch(state){
          
          case 'affiche':
               
               $('#list_installations').fadeIn('slow');
               
               break;
          case 'masque':
               
               $('#list_installations').fadeOut('slow');
               
               break;
     }
}
function affiche_encart_options(facture_id, div){
     div_include = div;
     
     document.getElementById(div_include).innerHTML = 'images/templates/wait.gif';
     
     ajaxrequest("ajax/forfaits_installation.php?facture_id="+facture_id,affiche_encart_options_cb);
}
function affiche_encart_options_cb(httprequest){
     
     if(!div_include) return false;
     
     document.getElementById(div_include).innerHTML = httprequest.responseText;
}
function cache_div(div){
     document.getElementById(div).style.display = 'none';
}
function verif_checkboxes_options(){
     
     var valide_formulaire = false;
     var tabCheckbox = document.getElementsByName('installation[]');
     
     for(i=0;i<tabCheckbox.length;i++){
          if(tabCheckbox[i].checked == true){
               valide_formulaire = true;
          }
     }
     
     if(document.getElementById('unchecker').checked == true) valide_formulaire = true;
     
     if(!valide_formulaire){
          alert('Veuillez sélectionner au moins une des options proposées');
     } else {
          submitcde2();
     }
}
function uncheck_options(obj){
     
     //Si coché, on décoche toutes les options sélectionnées auparavant
     if(obj.checked){
          var tabCheckbox = document.getElementsByName('installation[]');
          for(i=0;i<tabCheckbox.length;i++){
               tabCheckbox[i].checked = false;
          }
     }
}

function uncheck(id){
     document.getElementById(id).checked = false;
}
function maj_total_options(obj,montant,override_value){
     
     montant        = parseFloat(montant); 
     
     if(typeof(override_value) == 'undefined'){
          
          var total = document.getElementById('total_options_hidden').value; 
          total = parseFloat(total);
          
          if(total == 0){
               total = montant;
          } else {
               if(obj.checked){
                    total = total + montant;
               } else {
                    total = total - montant;
               }
          }
     } else {
          override_value = parseFloat(override_value);
          
          var total = override_value;
     }
     
     document.getElementById('total_options_hidden').value = total;
     document.getElementById('total_options').innerHTML = number_format(total,2,',',' ')+' &euro;';
}
// reloadcache();
