
var picturewidth=690;
var stoptime=6000;

var nbp=0;
var posim=0;
var mint=0;
var def=0;
var defint=0;
var scrstep=0.1;
var playim=true;


function movel(){
    clearInterval(mint);
    if(!document.getElementById('scrollimtb')) return;
    
    posim+=(scrstep*10);
    for(i=1;i<=nbp;i++){
        document.getElementById('l'+i).className='sliderbtn0';
        document.getElementById('arr'+i).className='slarrow0';
    }
    document.getElementById('l'+(posim+1)).className='sliderbtn1';
    document.getElementById('arr'+(posim+1)).className='slarrow1';
    defint=setInterval('move2()',50);
}
function move2(){
  def+=scrstep;
  //document.getElementById('alert').innerHTML=Math.round(def*10)/10;
  document.getElementById('scroller').scrollLeft=Math.round(def*picturewidth);

  if(Math.round(def*10)/10==posim){
    if(posim>=nbp-1) scrstep=-0.1;
    if(posim<=0){
      scrstep=0.1;
      document.getElementById('scroller').scrollLeft=0;
    }
    clearInterval(defint);
    if(playim) mint=setInterval('movel()',stoptime);
  }
  
}

function gotoim(nb){
  clearInterval(defint);
  clearInterval(mint);
  
  if(nb<0 || nb>nbp-1) return;
  
  playim=false;
  if(nb<Math.round(def*10)/10)
    scrstep=-0.1;
  else
    scrstep=0.1;

  posim=(nb-10*scrstep);
  movel();
}

function initim(){
    if(!document.getElementById('scrollimtb')) return;
    nbp=document.getElementById('scrollimtb').getElementsByTagName("td").length;
    document.getElementById('scroller').style.width=picturewidth+'px';
    document.getElementById('scroller').style.visibility='visible';

    mint=setInterval('movel()',stoptime);
}


function slgo_btns(state){
    if(state){
  document.getElementById('slgoleft').style.display='block';
  document.getElementById('slgoright').style.display='block';
    }else{
  document.getElementById('slgoleft').style.display='none';
  document.getElementById('slgoright').style.display='none';
    }
}

addEvent(window,'load',initim);
