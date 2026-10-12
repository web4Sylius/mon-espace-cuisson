
// site version

var d;
var dd;
var bissextile;
var dcount=new Array(31,28,31,30,31,30,31,31,30,31,30,31);
var monthes=new Array();
monthes['FR']=new Array("Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Aout","Septembre","Octobre","Novembre","Décembre");
monthes['EN']=new Array("January","February","March","April","May","June","July","August","September","October","November","December");
monthes['SP']=new Array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
var montharray=new Array();
var weekdays=new Array();
weekdays['FR']=new Array("D","L","M","M","J","V","S");
weekdays['EN']=new Array("Su","Mo","Tu","We","Th","Fr","Sa");
weekdays['SP']=new Array("D","L","M","M","J","V","S");
var caldiv="cccal";
var bgdiv=false;
var returninput;
var firstyear=0;
var cal_timestamp=0;
var cal_callback=undefined;
var showtime=undefined;
var timemode=1;
var hoursrange=undefined;
var selectuser=undefined;
var mints=undefined;
var mobileDevice=mobileDevice || false;
var dfirst=0;

function JoursFeries (an){
	var JourAn = new Date(an, "00", "01")
	var FeteTravail = new Date(an, "04", "01")
	var Victoire1945 = new Date(an, "04", "08")
	var FeteNationale = new Date(an,"06", "14")
	var Assomption = new Date(an, "07", "15")
	var Toussaint = new Date(an, "10", "01")
	var Armistice = new Date(an, "10", "11")
	var Noel = new Date(an, "11", "25")
	var SaintEtienne = new Date(an, "11", "26")
	
	var G = an%19
	var C = Math.floor(an/100)
	var H = (C - Math.floor(C/4) - Math.floor((8*C+13)/25) + 19*G + 15)%30
	var I = H - Math.floor(H/28)*(1 - Math.floor(H/28)*Math.floor(29/(H + 1))*Math.floor((21 - G)/11))
	var J = (an*1 + Math.floor(an/4) + I + 2 - C + Math.floor(C/4))%7
	var L = I - J
	var MoisPaques = 3 + Math.floor((L + 40)/44)
	var JourPaques = L + 28 - 31*Math.floor(MoisPaques/4)
	var Paques = new Date(an, MoisPaques-1, JourPaques)
	var VendrediSaint = new Date(an, MoisPaques-1, JourPaques-2)
	var LundiPaques = new Date(an, MoisPaques-1, JourPaques+1)
	var Ascension = new Date(an, MoisPaques-1, JourPaques+39)
	var Pentecote = new Date(an, MoisPaques-1, JourPaques+49)
	var LundiPentecote = new Date(an, MoisPaques-1, JourPaques+50)
	
	return new Array(JourAn, Paques, LundiPaques, FeteTravail, Victoire1945, Ascension, Pentecote, LundiPentecote, FeteNationale, Assomption, Toussaint, Armistice, Noel, SaintEtienne)
}



function initcalendrier(withtime,lang){
	 if(lang==undefined) lang="FR";
	 montharray=monthes[lang];
	 var dcal=document.createElement('div');
         dcal.setAttribute('id',caldiv);
         document.body.appendChild(dcal);
	 var css="cal.css";
	 if(mobileDevice){
	    css="calmob.css";
	    bgdiv=document.createElement("div");
	    bgdiv.className="calbg";
	    document.body.appendChild(bgdiv);
	 }
	 var fileref=document.createElement("link");
	 fileref.setAttribute("rel", "stylesheet");
	 fileref.setAttribute("type", "text/css");
	 fileref.setAttribute("href", "css/"+css);
	 document.getElementsByTagName("head")[0].appendChild(fileref);
//          document.body.onclick=calendrier_close;

}

function calendrier_show(input,newdate,withtime,callback,lang){
    returninput=input;
	showtime=withtime;
    curday=-1;
	if(lang==undefined) lang="FR";
	cal_callback=undefined;
	if(callback!=undefined) cal_callback=callback;
    var seldiv=document.getElementById(caldiv);
    if(!seldiv){
        initcalendrier(true,lang);
	    var seldiv=document.getElementById(caldiv);
//      return;
    }
    if(newdate!=undefined && newdate!=""){
	    matches=newdate.match(/\d+/g);
	    if(matches[3]==undefined) matches[3]=0;
	    if(matches[4]==undefined) matches[4]=0;
	    d=new Date(matches[2],matches[1]-1,matches[0],matches[3],matches[4],0);

    }else{
        d=new Date();
        curday=d.getDate();
    }
    
    var d1=new Date(d.getFullYear(),d.getMonth(),1);
    dd=d1;
	tmp=d1.getTime()-86400000;
	prevd1=new Date();
	prevd1.setTime(tmp);
	prevd1=new Date(prevd1.getFullYear(),prevd1.getMonth(),1);
	tmp=d1.getTime()+(86400000*32);
	nextd1=new Date();
	nextd1.setTime(tmp);
    day1=d1.getDay();

    days=daycount(d);
    grille="<table id=\"cal\">";
    grille+="<tr align=\"center\" class=\"cal_weekdays\">";
	
	for(i=0;i<=6;i++) grille+="<td>"+weekdays[lang][i]+"</td>";
	grille+="</tr><tr align=\"center\">";
	
	for(i=0;i<day1-1;i++){
        grille+="<td></td>";
    }
    var isJf = 0;
    var jf = JoursFeries(d.getFullYear());
    //on vérifie si le 1er jour du mois est ferié (ex 01/05 ou 01/01)
    $.each(jf,function(index){
	   		var jj=jf[index];
	   		if(jj.getMonth()==dd.getMonth() && jj.getDate()==dd.getDate() && jj.getFullYear()==dd.getFullYear()){
	   			dfirst = 1;
	   		}	   		
	   	});
    dprev=new Date();
    dprev.setTime(d1.getTime()-8640000);
    if(day1!=0) grille+="<td></td>"; // grille+="<td class=\"cal_days\" onclick=\"calendrier_show(returninput,'1/"+(dprev.getMonth()+1)+"/"+dprev.getFullYear()+"')\">"+dprev.getDate()+"</td>";

    for(i=1;i<=days;i++){
        if(day1==7){
            day1=0;
            grille+="</tr><tr align=\"center\">";
	   	}
	   	bg="";
        if(i==curday) bg=" bgcolor=\"#C70000\"";
        d1=new Date(d.getFullYear(),d.getMonth(),i);
	   	var wday=d1.getDay();
	   	//on cherche a voir si le jour est un jour férié.
	   	$.each(jf,function(index){
	   		var jj=jf[index];
	   		if(jj.getMonth()==d1.getMonth() && jj.getDate()==d1.getDate() && jj.getFullYear()==d1.getFullYear()){
	   			isJf = 1;
	   		}	   		
	   	});
	   	if(mints==undefined || d1.getTime()>=mints && wday!=0 && wday!=6 && isJf==0){
	   		grille+="<td id=\"td_"+i+"\""+bg+" class=\"cal_days\" onclick=\"calcdatetime("+i+");\" style=\"cursor:pointer;\">"+i+"</td>";
	   	}
	   	else{
			grille+="<td id=\"td_"+i+"\""+bg+" class=\"cal_days_lock\">"+i+"</td>";
		}
            day1++;
            isJf = 0;
    }

    fdate=1;
         /*for(i=day1;i<7;i++){
             grille+="<td class=\"cal_days\" onclick=\"calendrier_show(returninput,'"+fdate+"/"+(d.getMonth()+2)+"/"+d.getFullYear()+"')\">"+fdate+"</td>";
             fdate++;
         }*/
    grille+="</tr>";

	grille+="</table>";
	 
    seldiv.innerHTML="<div class=\"cal_date\"><a href=\"javascript:calendrier_show(returninput,'1/'+(prevd1.getMonth()+1)+'/'+prevd1.getFullYear(),showtime,cal_callback)\" class=\"cal_prec\"></a><a href=\"javascript:calmonth("+d.getFullYear()+")\" class=\"cal_datelink\">"+montharray[d.getMonth()]+"</a><a href=\"javascript:calyear('"+(d.getMonth()+1)+"')\" class=\"cal_datelink\">"+d.getFullYear()+"</a><a href=\"javascript:calendrier_show(returninput,'1/'+(nextd1.getMonth()+1)+'/'+nextd1.getFullYear(),showtime,cal_callback)\" class=\"cal_suiv\"></a><a href=\"javascript:calendrier_close()\" class=\"cal_close\"></a></div>";
	seldiv.innerHTML+="<div class=\"cal\"><div id=\"userdiv\"></div>"+grille+"<div id=\"datetime\" class=\"cal_datetime\"></div><div id=\"cal_ok\" class=\"cal_ok\" onclick=\"returndate();\"></div>";
	 
	calcdatetime();
	dfirst = 0;
	 
	curvisibility=seldiv.style.display;
        if(curvisibility!='block'){
    	    seldiv.style.display='block';
            seldiv.style.position='absolute';
	    	if(bgdiv) bgdiv.style.display='block';
	     	if(!mobileDevice){
				var position=getPosition(document.getElementById(input));
				seldiv.style.left=position[0]+'px';
				seldiv.style.top=position[1]+'px';
	     	}else{
				var w = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
				seldiv.style.left=Math.round((w-seldiv.clientWidth)/2)+'px';
	    	}
	     	seldiv.style.zIndex=100;
        }
}

function calmonth(year){
	 var grille="<div class=\"cal_date\">"+year+"<a href=\"javascript:calendrier_close()\"class=\"cal_close\"></a></div>";
         grille+="<table class=\"cal\"><tr>";
         for(i=0;i<12;i++){
             if(i==3 || i==6 || i==9) grille+="</tr><tr>";
             grille+="<td class=\"cal_days\"><a href=\"javascript:calendrier_show('"+returninput+"','1/"+(i+1)+"/"+year+"',showtime,cal_callback)\">"+montharray[i]+"</a></td>";
         }
         grille+="</table>";
         document.getElementById(caldiv).innerHTML="<div class=\"cal\">"+grille+"</div>";
}

function calyear(month){
	 var grille="<div class=\"cal_date\"><a href=\"javascript:calendrier_close()\"class=\"cal_close\"></a></div>";
         grille+="<table>";
         cd=new Date();

         for(i=cd.getFullYear()-1;i<=(cd.getFullYear()+2);i++){
             grille+="<tr><td class=\"cal_days\"><a href=\"javascript:calendrier_show('"+returninput+"','1/"+month+"/"+i+"',showtime,cal_callback)\">"+i+"</a></td></tr>";
         }
         grille+="</table>";
         document.getElementById(caldiv).innerHTML="<div class=\"cal\">"+grille+"</div>";
}


function selectday(day){
	 var tds=document.getElementsByTagName('td');
	 for(i=0;i<tds.length;i++){
	    
	     if(tds[i].id.substr(0,3)=="td_") tds[i].style.backgroundColor='';
	 }
	 document.getElementById('td_'+day).style.backgroundColor='red';
}

function daycount(d){
         days=dcount[d.getMonth()];
         bissextile = d.getFullYear() %4;
         if(d.getMonth()==1 && bissextile==0) days=29;
         return days;
}

function calcdatetime(day){
	if(day!=undefined){
	  d.setDate(day);
	  selectday(day);
	}
	
	//console.log(d);
	var jf = JoursFeries(d.getFullYear());
	var wday=d.getDay();
	if(wday==0) d.setDate(d.getDate()+1);
	if(wday==6) d.setDate(d.getDate()+2);
	if((mints!=undefined && d.getTime()<mints)) d.setTime(mints);
	//si le 1er jour du mois est selectionné on vérifi si ce jour n'est pas férié pour pas qu'il soit sélectionné automatiquement.
	if(d.getDate()==dd.getDate() && d.getMonth()==dd.getMonth() && d.getFullYear()==dd.getFullYear() && dfirst == 1){
		selectday(d.getDate()+1);
	}else{
		selectday(d.getDate());
	}

	document.getElementById('datetime').innerHTML=d.getDate()+"/"+(d.getMonth()+1)+"/"+d.getFullYear();
}

function returndate(){
         if(returninput!="" && document.getElementById(returninput)){
	    var m=d.getMonth()+1;
	    if(m<10) m="0"+m;
	    document.getElementById(returninput).value=d.getDate()+"/"+m+"/"+d.getFullYear();
	 }
         var rdate=new Date(d.getFullYear(),d.getMonth(),d.getDate());
         cal_timestamp=(rdate.getTime()/1000);
         calendrier_close();
         if(cal_callback!=undefined) cal_callback();
}


function calendrier_close(){
         document.getElementById(caldiv).style.display='none';
	 if(bgdiv) bgdiv.style.display='none';
}

function round5(x){
    return (x % 5) >= 2.5 ? parseInt(x / 5) * 5 + 5 : parseInt(x / 5) * 5;
}

function getPosition(obj){
    var topValue= 0,leftValue= 0;
    while(obj){
	leftValue+= obj.offsetLeft;
	topValue+= obj.offsetTop;
	obj= obj.offsetParent;
    }
    return new Array(leftValue,topValue);
}
