function epigy_call(tel,poste,linkid,cb){

   // Modif TSI pour appel sur numéros en +XX 
   if (tel.charAt(0)=="+") {
        var telmod = "00"+tel.substring(1);
        
        tel = telmod; 
    }
    
    if(poste==undefined) return false;
//     if(linkid!=undefined) document.getElementById('epigy_icon_'+linkid).src="images/ring_phone.gif";
//     ajaxrequest("tel/makecall.php?tel="+tel,epigy_callback);
//     ajaxrequest("http://192.168.1.246/clicktodial.cgi?user=admin&pass=XXXXXXXX&caller=100&dest="+tel,epigy_callback);

    /*var iframe=document.createElement("iframe");
    iframe.src="https://192.168.2.246:448/clicktodial.cgi?user="+poste+"&pass=12345600&caller="+poste+"&dest="+tel;
    iframe.style.display='none';
    iframe.setAttribute("onload","epigy_callback('"+linkid+"')");
   
    document.body.appendChild(iframe);*/
    ajaxrequest("/admin/tel/clicktocall.php?caller="+poste+"&dest="+tel,function(data){
        if(linkid!="") document.getElementById('epigy_icon_'+linkid).src="images/ring_phone_2.gif";
        if(cb!=undefined) cb(data.responseText);
    });
}

function epigy_callback(linkid){
    if(linkid!="") document.getElementById('epigy_icon_'+linkid).src="images/ring_phone_2.gif";
}

function poste_connect(poste){
    ajaxrequest("http://test.ma-cave-a-vin.fr/admin/tel/poste.php?poste="+poste,undefined);
}    
