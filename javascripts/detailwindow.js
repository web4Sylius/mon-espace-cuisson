 var MouseX; // Variable globale Position X de la Mouse
 var MouseY; // Variable globale Position Y de la Mouse
 var closeint;


  function detailwindow(txt){
               if(!document.getElementById('divdetail')) creatediv();
               var div=document.getElementById('divdetail');
               div.innerHTML="<img src='configurations/www.ma-cave-a-vin.fr/images/templates/i.png' width='15' align='absmiddle' style='padding-right:5px;'>"+txt;
		div.style.left=MouseX+"px";
		div.style.top=MouseY+"px";
		var s=winsize();
		if(MouseX+500>s[0]) div.style.left=(MouseX-480)+'px';
               div.style.visibility='visible';
               clearInterval(closeint);
               closeint=setTimeout('hidedetail()',10000);

  }
 
  function hidedetail(){
               var div=document.getElementById('divdetail');
               div.style.visibility='hidden';
  }

  function creatediv(){
                 var div=document.createElement('div');
                 div.id='divdetail';
                 div.style.border='solid thin black';
                 div.style.backgroundColor='#eeeeee';
                 div.style.position='absolute';
                 div.style.visibility='hidden';
                 div.style.width='480px';
                 //div.style.height='100px';
                 div.style.color='black';
                 div.style.zindex=100;
                 div.style.padding='10px';
                 div.style.paddingLeft='20px';
                 div.style.textAlign='left';
		 div.style.fontSize='12px';
//		 div.style.left='0px;';
//		 div.style.top='0px';
                 document.body.appendChild(div);
                 div.onmouseout=function(){
                      hidedetail()
                 };

  }
  
    function detailwindowbyid(id){
	      if(!document.getElementById(id)) return false;
	      var div=document.getElementById(id);
	      var s=winsize();
	      if(MouseX+500>s[0]) div.style.left=(MouseX-480)+'px';
	      div.style.visibility='visible';
	      clearInterval(closeint);
              closeint=setTimeout('hidebyid("'+id+'")',10000);
    }
  
    function hidebyid(id){
	      var div=document.getElementById(id);
	      if(!div) return false;
	      div.style.visibility='hidden';
    }
    
    
    function winsize(){
      if (document.body){
	return new Array(document.documentElement.clientWidth,document.documentElement.clientHeight);
      }else{
	return new Array(window.innerWidth,window.innerHeight);
      }
    }

    function position(e){
      MouseX = (navigator.appName.substring(0,3) == "Net") ? e.pageX : event.x+document.documentElement.scrollLeft;
      MouseY = (navigator.appName.substring(0,3) == "Net") ? e.pageY : event.y+document.documentElement.scrollTop;
    }

    if(navigator.appName.substring(0,3) == "Net")
	    document.captureEvents(Event.MOUSEMOVE);
	    document.onmousemove = position;