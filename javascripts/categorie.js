var curcatid=0;
var cursscatid=0;
var tong=0;
var oong=0;

function showongletdiv(id,color){
    if(curcatid!=0 && id!=curcatid) hideongletdiv2(curcatid);
    if(id==curcatid) clearTimeout(tong);
    clearTimeout(oong);
    oong=setTimeout('showongletdiv2('+id+',\"'+color+'\")',300);
  
}
function showongletdiv2(id,color){
    document.getElementById('ongletdiv'+id).style.display='block';
    document.getElementById('onglet'+id).style.backgroundColor=color;
    curcatid=id;
}
function hideongletdiv(id){
    clearTimeout(oong);
    tong=setTimeout('hideongletdiv2('+id+')',100);
}
function hideongletdiv2(id){
    document.getElementById('ongletdiv'+id).style.display='none';
    document.getElementById('onglet'+id).style.backgroundColor='';
}

function cdcResponse(httprequest){
  
    var rep = httprequest.responseXML.documentElement;
    var xmldata=rep.getElementsByTagName('data')[0];
    var xmlcatid=xmldata.getAttribute("id");
    //if(catid==cursscatid) document.getElementById('cdc'+curcatid).innerHTML=data.firstChild.nodeValue;
    cdclist[xmlcatid]=xmldata.firstChild.nodeValue;
    if(cursscatid==xmlcatid) showcdc(xmlcatid);
}

function showcdc(id){
    cursscatid=id;
    if(cdclist[id]!=undefined) document.getElementById('cdc'+curcatid).innerHTML=cdclist[id];
}


function setsubcatids(catid,parent){
    var result=new Array();
    var rem=false;
    var liste=document.getElementsByName('subcatids')[0].value.split(",");
    for(i=0;i<liste.length;i++){
    if(liste[i]==catid)
        rem=true;
    else
        if(liste[i]!="" && liste[i]!=parent) result.push(liste[i]);
    }
    if(!rem){
      result.push(catid);
      document.getElementById('chk'+catid).className="checked";
    }else
      document.getElementById('chk'+catid).className="";
    
    document.getElementsByName('subcatids')[0].value=result.join(",");
    document.getElementsByName('start')[0].value=0;
    document.forms.rechmenu.submit();
}

function setmarqueids(mid){
    var result=new Array();
    var rem=false;
    var liste=document.getElementsByName('marqueids')[0].value.split(",");
    for(i=0;i<liste.length;i++){
    if(liste[i]==mid)
        rem=true;
    else
        if(liste[i]!="") result.push(liste[i]);
    }
    if(!rem){
      result.push(mid);
      document.getElementById('mar'+mid).className="checked";
    }else
      document.getElementById('mar'+mid).className="";
    
    document.getElementsByName('marqueids')[0].value=result.join(",");
    document.getElementsByName('start')[0].value=0;
    document.forms.rechmenu.submit();
}

function setparam(param,id,multi){
    var result=new Array();
    var rem=false;
    
    if(multi){
      var liste=document.getElementsByName(param)[0].value.split(",");
      for(i=0;i<liste.length;i++){
    if(parseInt(liste[i])==parseInt(id))
        rem=true;
    else
        if(liste[i]!="") result.push(liste[i]);
      }
    }else{
    rem=(document.getElementsByName(param)[0].value==id);
    }
    if(!rem){
      result.push(id);
      document.getElementById(param.substr(0,3)+id).className="checked";
    }else
      document.getElementById(param.substr(0,3)+id).className="";
    
    document.getElementsByName(param)[0].value=result.join(",");
    document.getElementsByName('start')[0].value=0;
    document.forms.rechmenu.submit();
}

function unsetparam(param){
    document.getElementsByName(param)[0].value="";
    document.getElementsByName('start')[0].value=0;
    document.forms.rechmenu.submit();
}

function changeorder(order){
    document.getElementsByName('sensorder')[0].value=order;
    document.forms.rechmenu.submit();
}

function changestart(start){
    document.getElementsByName('start')[0].value=start;
    document.forms.rechmenu.submit();
}

function showparam(param){
    if(document.getElementById(param+"list").className=="menuli"){
    document.getElementById(param+"list").className="menulihidden";
    document.getElementById(param+"btn").className="openparam";
    }else{
    document.getElementById(param+"list").className="menuli";
    document.getElementById(param+"btn").className="closeparam";
    }
}

function show_quickview(div,state){
    var qv=quickview_link(div);
    if(qv){
      if(state){    
    qv.style.display="block";   
      }else{
    qv.style.display="none";
      }
    }
}


function vignettes(){
//     vignettes_type("articles");
    var articles=document.getElementById("articles");
    if(articles!=null){
    var vignettes=articles.getElementsByTagName('div');
    for(i=0;i<vignettes.length;i++){
        var classname=vignettes[i].className;
        if((classname.indexOf("vignette ")>-1 || classname=="vignette") && quickview_link(vignettes[i])){
        vignettes[i].onmouseenter=new Function("show_quickview(this,true)");
        vignettes[i].onmouseleave=new Function("show_quickview(this,false)");
        }
    }
    }
}

function quickview_link(div){
    var a=div.getElementsByTagName('a');
    for(j=0;j<a.length;j++)
      if(a[j].className=="apercu_rapide") return a[j];
    return false;
}

function vignettes_type(type){
    var articles=document.getElementById(type);
    if(articles!=null){
    var links=articles.getElementsByTagName('a');
    if(links==null) return;
    for(i=0;i<links.length;i++){
        if(links[i].id.substr(0,4)=="link"){
        var artid=links[i].id.substr(4);
        var div=document.getElementById('article'+artid);
        if(div!=null) div.onclick=new Function("vignette_click('"+links[i].href+"')");
        }
    }
    } 
}

function vignette_click(url){
    document.location=url;
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

addEvent(window,'load',vignettes);
