<?php

include("sav.class.php");
$allowclose=array("cc","philippe","sylvain");

$sav=new sav($DB_site2,$_GET['savid']);

if(isset($_POST['step'])) $sav->update_step($_POST['step']);

print "<style>
.etape { width:100%; background-color:#F4F4F4; border-collapse:collapse; }
.etape td { border-bottom:solid thin #AAA; }
.etapetd { background-color:#AA0000; width:48%; color:white;font-size:14px;font-weight:bold; }
.etapesep { width:2%; background-color:#AAA; }
.client { color:black; }
.modifstep { float:right; font-size:10px; color:white; font-weight:normal; }
.photosav { height:40px; border:solid thin #AAA; padding:2px; }
</style>";

print "<script language=\"javascript\" src=\"../javascripts/prototype.js\"></script>
<script language=\"javascript\" src=\"filebox.js\"></script>";

print "<script language=\"javascript\">
var autosaveid=0;
function showverif(name,value){
    document.getElementById(name+'_span').style.display='inline';
    document.getElementsByName(name+'_verif')[0].value=value;
}

function sav_save(name){
    if(document.getElementById(name+'-ok')){
      document.getElementById(name+'-ok').innerHTML='<img src=\"/configurations/www.ma-cave-a-vin.fr/images/templates/ajax_wait.gif\" width=\"16\" align=\"absmiddle\">';
      document.getElementById(name+'-ok').style.display='inline';
    }
    ajaxrequest('sav2_ajax.php?action=update&savid=".$sav->id."&name='+name+'&value='+document.getElementsByName(name)[0].value,sav_save_cb);
}

function sav_save_cb(httpRequest){
    var xml=httpRequest.responseXML.documentElement;

    if(xml.getElementsByTagName('field')[0]){
	var field=xml.getElementsByTagName('field')[0];
	var name=field.getAttribute('name');
	if(document.getElementById(name+'-ok')){
	  document.getElementById(name+'-ok').innerHTML='OK';
	  document.getElementById(name+'-ok').style.display='none';
	}
	if(document.getElementById(name+'-date')){
	    var d=field.getAttribute('date');
	    var utilisateur=field.getAttribute('utilisateur');
	    document.getElementById(name+'-date').innerHTML=utilisateur+' '+d;
	}
    }
}

function sav_calsave(){
    sav_save(returninput);
}

function sav_autosave(name){
    clearTimeout(autosaveid);
    autosaveid=setTimeout(\"sav_save('\"+name+\"')\",1000);
}

function nbonly(event,decimal,input) {
  // Compatibilité IE / Firefox
  if(!event && window.event) {
    event=window.event;
  }

  if(decimal==undefined) decimal=false;

  // IE
  if((event.keyCode < 48 || event.keyCode > 57) && event.keyCode!=8) {
       if(!decimal || event.keyCode!=46 || input.value.indexOf('.')>0){
          event.returnValue = false;
          event.cancelBubble = true;
       }
  }

  // DOM
  if((event.which < 48 || event.which > 57) && event.which!=8) {
    if(!decimal || event.which!=46 || input.value.indexOf('.')>0){
          event.preventDefault();
          event.stopPropagation();
    }
  }
}

function calcsum22(){
    if(!document.getElementById('resultsum22')) return;
    var total=0;
    for(i=0;i<=7;i++){
	total+=parseFloat(document.getElementsByName('sav_val22'+i)[0].value);
    }
    if(isNaN(total)) total=0;
    document.getElementById('resultsum22').innerHTML=Math.round(100*total)/100+' &euro;';
}

function sumverif(max){
    var sum=0;
    for(i=1;i<=max;i++) sum+=parseInt(document.getElementsByName('sav_verif'+i)[0].value);
    document.getElementById('savclos').style.display=(sum==max?'block':'none');
}

function maildossier(emailid){
    mail_enreg(undefined,undefined,undefined,emailid);
    mail_suivi('suivi_commande','$facturid');
    for(i=0;i<document.getElementsByName('checkboxfile').length;i++){
      if(document.getElementsByName('checkboxfile')[i].checked){
	var fileid=document.getElementsByName('checkboxfile')[i].value;
	var name=document.getElementById('filename'+fileid).value;
	var url=document.getElementById('fileurl'+fileid).value;
	addserverpj(name,url);
      }
    }
}

function loadfilebox(){
    document.getElementById('fileboxlist').innerHTML='';
    ajaxrequest('sav2_ajax.php?action=files&savid=".$sav->id."',loadfilebox_cb);
}

function loadfilebox_cb(httpRequest){
    var xml=httpRequest.responseXML.documentElement;
    var html='';
    if(xml.getElementsByTagName('file')){
	for(i=0;i<xml.getElementsByTagName('file').length;i++){
	    var f=xml.getElementsByTagName('file')[i];
	    var id=f.getAttribute('id');
	    var path=f.getAttribute('path');
	    var name='<span onclick=\"window.open(\''+path+'\')\">'+f.getAttribute('name')+'</span>';
	    var ext=f.getAttribute('ext');
	    var mod=f.getAttribute('mod');
	    var utilisateur=f.getAttribute('utilisateur');
	    var date=f.getAttribute('date');
	    var size=f.getAttribute('size');
	    var img=' ';
	    if(ext=='jpg' || ext=='png'){
		ext='img';
		img='<img src=\"'+path+'\">';
	    }
	    var d=new Date();
	    d.setTime(date*1000);
	    date=d.getDate()+'/'+(1+d.getMonth())+'/'+d.getFullYear()+' '+d.getHours()+':'+d.getMinutes();
	    if(size>1048576)
	      size=(Math.round(size/10485.76)/100)+' Mo';
	    else
	      size=(Math.round(size/10.24)/100)+' ko';
	    var mouseover='onmouseover=\"document.getElementById(\'del'+id+'\').style.display=\'block\';\" onmouseout=\"document.getElementById(\'del'+id+'\').style.display=\'none\';\"';
	    if(mod=='ro') mouseover='';
	    html+='<div class=\"file\" '+mouseover+'><span id=\"info'+id+'\" class=\"fileinfo\" onclick=\"this.style.display=\'none\';\">'+utilisateur+'<br>le '+date+'<br>'+size+'</span><span id=\"del'+id+'\" class=\"filedel\"><img src=\"images/supprimer.gif\" onclick=\"delfile('+id+')\"> <img src=\"images/interrogation.gif\" onclick=\"document.getElementById(\'info'+id+'\').style.display=\'block\';\"> <img src=\"images/editer.gif\" onclick=\"renamefile('+id+',\''+f.getAttribute('name')+'\')\"></span><div class=\"file'+ext+'\" onclick=\"window.open(\''+path+'\')\">'+img+'</div>'+name+'</div>';
	    if((i+1)%4==0) html+='<div style=\"clear:both;\"></div>';
	}
    }
    document.getElementById('fileboxlist').innerHTML=html;
}

function showfilebox(state){
    var display=(state?'block':'none');
    document.getElementById('filebox').style.display=display;
    if(state){
      document.getElementById('filebox').style.top=document.body.scrollTop+'px';
      loadfilebox();
    }
}

function refreshpj(){
    document.getElementById('et6tmppj').innerHTML='<img src=\"/configurations/www.ma-cave-a-vin.fr/images/templates/ajax_wait.gif\">';
    ajaxrequest('sav2_ajax.php?action=files&savid=".$sav->id."',refreshpj_cb);
}

function refreshpj_cb(httpRequest){
    var xml=httpRequest.responseXML.documentElement;
    var html='';
    if(xml.getElementsByTagName('file')){
	for(i=0;i<xml.getElementsByTagName('file').length;i++){
	    var f=xml.getElementsByTagName('file')[i];
	    var id=f.getAttribute('id');
	    var name=f.getAttribute('name');
	    var path=f.getAttribute('path');
	    html+='<br><input type=\"checkbox\" name=\"checkboxfile\" value=\"'+id+'\"><input type=\"hidden\" id=\"filename'+id+'\" value=\"'+name+'\"><input type=\"hidden\" id=\"fileurl'+id+'\" value=\"'+path+'\"> <a href=\"'+path+'\" target=\"_blank\">'+name+'</a>'
	}
    }
    document.getElementById('et6tmppj').innerHTML=html;
}

function delfile(fid){
    if(confirm('Etes-vous sur de vouloir supprimer ce fichier ?')) ajaxrequest('sav2_ajax.php?action=delfile&savid=".$sav->id."&fid='+fid,loadfilebox_cb);
}

function renamefile(fid,old){
    var newname=prompt('Nouveau nom de '+old+' :',old);
    if(newname==null || newname=='') return false;
    ajaxrequest('sav2_ajax.php?action=rename&savid=".$sav->id."&fid='+fid+'&newname='+newname,loadfilebox_cb);
}


mail_pagepj('fb.showselect',['undefined',mail_addpjs]);

</script>";

include("stock_sav".$sav->typelitige.".php");

?>