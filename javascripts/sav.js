function changefacturid(){
      document.getElementById('lignes').innerHTML="";
      document.getElementsByName('typelitige')[0].value="";
}

function sav_lignes(){
      if(!document.getElementsByName('facturid')) return;
      if(!document.getElementsByName('typelitige') || document.getElementsByName('typelitige')[0].value=="") return;
      document.getElementById('lignes').innerHTML="<img src=\"configurations/www.ma-cave-a-vin.fr/images/templates/ajax_wait.gif\" width=\"27\">";
      ajaxrequest("sav_ajax.php?facturid="+document.getElementsByName('facturid')[0].value+"&typelitige="+document.getElementsByName('typelitige')[0].value,sav_cb);
}

function sav_cb(httprequest){
      document.getElementById('lignes').innerHTML=httprequest.responseText;
}

function submit1(){
      var sinputs=document.forms.sav1.getElementsByTagName("select");
      var sum=0;
      for(i=0;i<sinputs.length;i++){
	  if(sinputs[i].name.substr(0,3)=="qte") sum+=parseInt(sinputs[i].value);
      }
      document.getElementById('sav_form').style.display=(sum>0?'block':'none');
}

function showligne(id,state){
      if(!document.getElementById(id)) return;
      document.getElementById(id).style.display=(state?"block":"none");
}