var slm_interval=2000;
function slm_init(){
    if(!document.getElementById('marqueslider')) return;
    if(slm_list==undefined || slm_list.length==0) return;
    slm_interval=2000;
    
    var div=document.createElement('div');
    div.className="col";
    var row=1;
    for(i=0;i<slm_list.length;i++){
	var rowdiv=slm_createimg(slm_list[i]);
	div.appendChild(rowdiv);
// 	if(row==2){
	    document.getElementById('marqueslider').appendChild(div);
	    var div=document.createElement('div');
	    div.className="col";
	    row=0;
// 	}
	row++;
    }
    if(div.innerHTML!=""){
	document.getElementById('marqueslider').appendChild(div);
    }
    if(slm_list.length>=2){
	var div=document.createElement('div');
	div.className="col";
	for(i=0;i<=1;i++){
	    var rowdiv=slm_createimg(slm_list[i]);
	    div.appendChild(rowdiv);
	}
	document.getElementById('marqueslider').appendChild(div);
    }
    document.getElementById('marqueslider').scrollLeft=0;

//     setTimeout(slm_move,slm_interval);
    if(window.addEventListener)
      window.addEventListener("load",slm_move);
    else
      window.attachEvent('onload',slm_move);
}

function slm_move(){
    var sl=document.getElementById('marqueslider').scrollLeft;
    if(sl>=document.getElementById('marqueslider').scrollWidth-document.getElementById('marqueslider').clientWidth) sl=-130;
    document.getElementById('marqueslider').scrollLeft=sl+128;
    setTimeout(slm_move,slm_interval);
}

function slm_createimg(elem){
    var img=document.createElement("img");
    img.src=elem.image;
    img.alt=elem.alt;
    var rowdiv=document.createElement('a');
    rowdiv.className="row";
    rowdiv.href=elem.link;
    rowdiv.appendChild(img);
    return rowdiv;
}