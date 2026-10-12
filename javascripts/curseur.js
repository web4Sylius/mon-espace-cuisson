var dbcurseur=Class.create();

dbcurseur.prototype={
  initialize: function(parent,name){
     if(!document.getElementById(parent)) return;
     this.name=name;
     this.back=document.createElement("div");
     this.back.id=name;
     this.back.className="curseur";
     this.back.setAttribute("onmousemove",name+".drag(event)");
     
     document.getElementById(parent).appendChild(this.back);
     this.cmin=document.createElement('div');
     this.cmin.id=name+"_cmin";
     this.cmin.className="cmin";
     this.cmin.setAttribute("onmousedown",name+".startdragmin(event)");
     this.cmin.setAttribute("onmouseup",name+".stopdrag(true)");
     this.cmin.setAttribute("onmousemove",name+".drag(event)");
     this.cmin.setAttribute("onselectstart",name+".selectstart()");
     this.back.appendChild(this.cmin);
     this.cmax=document.createElement('div');
     this.cmax.id=name+"_cmax";
     this.cmax.className="cmax";
     this.cmax.setAttribute("onmousedown",name+".startdragmax(event)");
     this.cmax.setAttribute("onmouseup",name+".stopdrag(true)");
     this.cmax.setAttribute("onmousemove",name+".drag(event)");
     this.cmax.setAttribute("onselectstart",name+".selectstart()");
     this.back.appendChild(this.cmax);
//      Event.observe(document.getElementById(name+"_cmax"),"mousedown",this.startdragmax.bind(this));
//      Event.observe(document.getElementById(name+"_cmax"),"mouseup",this.stopdrag.bind(this));
     this.vmax=100;
     this.vmin=0;
     
     this.cmax.style.left=parseInt(this.back.offsetWidth-this.cmax.offsetWidth)+"px";
     this.dragcursor=false;
     this.left=this.back.offsetLeft;
     this.top=this.back.offsetTop;
     this.width=this.back.offsetWidth-this.cmax.offsetWidth-this.cmin.offsetWidth;
     this.height=this.back.offsetHeight;
     this.min=0;
     this.max=100;
     this.cmin.innerHTML=this.min;
     this.cmax.innerHTML=this.max;
     this.callback=undefined;
  },
  
							
  startdragmax: function(e){
      var mouse=this.getposition(e);alert(mouse);
      this.xdiff=mouse.x-this.left-this.cmax.offsetLeft;
      this.dragcursor=this.cmax;
      return false;
  },
  startdragmin: function(e){
      var mouse=this.getposition(e);
      this.xdiff=mouse.x-this.left-this.cmin.offsetLeft;
      this.dragcursor=this.cmin;
      return false;
  },
  
  stopdrag: function(cb){
    if(cb){
      if(this.callback!=undefined && this.dragcursor==this.cmax) this.callback(this.name+"_max",this.vmax);
      if(this.callback!=undefined && this.dragcursor==this.cmin) this.callback(this.name+"_min",this.vmin);
    }
    this.dragcursor=false;
  },
  drag: function(e){
    e.returnValue=false;
    if(e.preventDefault) e.preventDefault();
    if(this.dragcursor==false || this.dragcursor==undefined) return;
    var cwidth=parseInt(this.dragcursor.offsetWidth);
    
    var mouse=this.getposition(e);
    var x=mouse.x-this.xdiff-this.left;
    var y=mouse.y-this.top;

    if(y>this.height || y<0){ this.stopdrag(); return; }
    if(this.dragcursor==this.cmax){
      if(x>this.width+cwidth || x<this.cmin.offsetLeft+cwidth){ this.stopdrag(false); return; }
      this.vmax=parseInt(this.min+(x-cwidth)*(this.max-this.min)/this.width);
      this.dragcursor.innerHTML=this.vmax;
    }
    if(this.dragcursor==this.cmin){
      if(x+cwidth>this.cmax.offsetLeft|| x<0){ this.stopdrag(false); return; }
      this.vmin=parseInt(this.min+x*(this.max-this.min)/this.width);
      this.dragcursor.innerHTML=this.vmin;
    }

    this.dragcursor.style.left=parseInt(x)+'px';
    
    return false;
  },
  
  setmin: function(value){
      if(value<this.min || value==undefined) return;
      this.vmin=value;
      var x=parseInt((this.vmin-this.min)/((this.max-this.min)/this.width));
      this.cmin.style.left=x+"px";
      this.cmin.innerHTML=this.vmin;
  },
  setmax: function(value){
      if(value>this.max || value==undefined) return;
      this.vmin=value;
      var x=parseInt(this.cmax.offsetWidth+(this.vmin-this.min)/((this.max-this.min)/this.width));
      this.cmax.style.left=x+"px";
      this.cmax.innerHTML=this.vmin;
  },
  minmax: function(min,max){
      this.max=max;
      this.min=min;
      this.setmin(min);
      this.setmax(max);
  },
  selectstart: function(){
      return false;
  },
  getposition: function(e){
    var isIE = document.all ? true : false;
    if (!isIE) {
	var mousex = e.pageX;
	var mousey = e.pageY;
    }else{
	var mousex= e.clientX + document.documentElement.scrollLeft;
	var mousey = e.clientY + document.documentElement.scrollTop;
    }
    return {x:mousex,y:mousey};
  }
}