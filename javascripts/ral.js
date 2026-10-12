var ral_vars=new Array();

function ral_show(raldiv,state,sender){
    var display=(state?"block":"none");
    ral_vars['div']=raldiv;

    if(sender!=undefined){
    	var pos=findPos(sender);
      pos.top = 0;
    	document.getElementById(raldiv).style.left="33%";
    	document.getElementById(raldiv).style.top=pos.top+"px";
	    if(document.documentElement.clientWidth<pos.left+600) document.getElementById(raldiv).style.left=(pos.left-550)+"px";
    }
    document.getElementById(raldiv).style.display=display;
    document.body.onmousedown = function (e) { 
      var target = (e && e.target) || (event && event.srcElement);
      if(!checkParent(target,raldiv)) ral_show(raldiv,false); 
    }
}

function article_ral_select(caractid,caractvalid,ral_caractvalid,color,artid){
	document.getElementById('lst_ral_'+caractid+'_'+caractvalid).value=ral_caractvalid;
    document.getElementById(ral_vars['div']).style.display="none";
    checkOption($('#option'+caractvalid), artid, caractid, caractvalid, '', true, color);
    //document.getElementById('lst_ral_'+caractid+'_'+caractvalid).checked=true;
} 

function panier_ral_select(caractvalid,caractvalid,ral_caractvalid,color){
    var ral_select=document.getElementsByName("lst_caract["+ral_vars['lignepanierid']+"]["+ral_vars['caractid']+"]");

    for(i=0;i<ral_select.length;i++) if(ral_select[i].checked) ral_select[i].value=ral_caractvalid;
//     ral_select.options[ral_select.selectedIndex].value=ral_caractvalid;
    document.formu.submit();
}

function set_ral_vars(a){
    ral_vars=a;
}

function findPos(obj){
    var curleft=curtop=0;
    if(obj.offsetParent){
      do {
	      curleft+=obj.offsetLeft;
	      curtop+=obj.offsetTop;
      } while(obj=obj.offsetParent); 
    }
    return {"left":curleft,"top":curtop};
}

function checkParent(t,id) {
        while(t.parentNode) { 
          if( t == document.getElementById(id) ) {return true;} 
          t = t.parentNode;
        } 
        return false;
}
