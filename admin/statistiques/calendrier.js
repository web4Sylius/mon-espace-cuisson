var d;
var bissextile;
var dcount=new Array(31,28,31,30,31,30,31,31,30,31,30,31);
var montharray=new Array("Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Aout","Septembre","Octobre","Novembre","Décembre");
var caldiv="cccal";
var returninput;
var firstyear=0;
var cal_timestamp=0;
var cal_callback=undefined;
var cal_jour;
var showtime=undefined;
var timemode=1;
var hoursrange=undefined;
var selectuser=undefined;
var mints=undefined;

var Mouse_X;
var Mouse_Y;

function initcalendrier(withtime){
         var dcal=document.createElement('div');
         dcal.setAttribute('id',caldiv);
         document.body.appendChild(dcal);
	 var fileref=document.createElement("link");
	 fileref.setAttribute("rel", "stylesheet");
	 fileref.setAttribute("type", "text/css");
	 fileref.setAttribute("href", "cal.css");
	 document.getElementsByTagName("head")[0].appendChild(fileref);
         if(withtime!=undefined) showtime=true;
         //document.body.onclick=calendrier_close;

}

function calendrier_show(input,newdate,withtime,callback){
	returninput = input;
	showtime = withtime;
	curday = 0;
	cal_callback = undefined;
	
	if(callback != undefined) cal_callback = callback;
	
	var seldiv = document.getElementById(caldiv);
	
	if(!seldiv){
		initcalendrier(true);
	}
	
	curvisibility=seldiv.style.display;
	
	if(curvisibility != 'block'){
		seldiv.style.display = 'block';
		seldiv.style.position = 'absolute';
		var position = getPosition(document.getElementById(input));
		seldiv.style.left = position[0]+'px';
		seldiv.style.top = position[1]+'px';
		seldiv.style.zIndex = 9500;
	}

	if(newdate != undefined && newdate != ""){
		matches = newdate.match(/\d+/g);
		if(matches[3] == undefined) matches[3] = 0;
		if(matches[4] == undefined) matches[4] = 0;
		d = new Date(matches[2],matches[1]-1,matches[0],matches[3],matches[4],0);
	}else{
		d = new Date();
		curday = d.getDate();
	}

	d1 = new Date(d.getFullYear(),d.getMonth(),1);
	tmp = d1.getTime()-86400000;
	prevd1 = new Date();
	prevd1.setTime(tmp);
	prevd1 = new Date(prevd1.getFullYear(),prevd1.getMonth(),1);
	tmp = d1.getTime()+(86400000*32);
	nextd1 = new Date();
	nextd1.setTime(tmp);
	day1 = d1.getDay();

	days = daycount(d);
	grille = "<table id=\"cal\">";
	grille+= "<tr align=\"center\" class=\"cal_weekdays\"><td>D</td><td>L</td><td>M</td><td>M</td><td>J</td><td>V</td><td>S</td></tr><tr align=\"center\">";

	for(i=0; i<day1-1; i++){
		grille+= "<td></td>";
	}
	
	dprev = new Date();
	dprev.setTime(d1.getTime()-8640000);
	
	if(day1 != 0) grille+="<td></td>"; // grille+="<td class=\"cal_days\" onclick=\"calendrier_show(returninput,'1/"+(dprev.getMonth()+1)+"/"+dprev.getFullYear()+"')\">"+dprev.getDate()+"</td>";

	for(i=1; i<=days; i++){
		if(day1 == 7){
			day1 = 0;
			grille+= "</tr><tr align=\"center\">";
		}
		
		bg = "";
		
		if(i == curday) bg = " bgcolor=\"#C70000\"";
		
		d1 = new Date(d.getFullYear(),d.getMonth(),i);
		var wday = d1.getDay();
		
		if(mints == undefined || d1.getTime()>=mints && wday!=0 && wday!=6)
			grille+= "<td id=\"td_"+i+"\""+bg+" class=\"cal_days\" onclick=\"calcdatetime("+i+");\" style=\"cursor:pointer;\">"+i+"</td>";
		else
			grille+= "<td id=\"td_"+i+"\""+bg+" class=\"cal_days_lock\">"+i+"</td>";
		
		day1++;
	}
         
	fdate = 1;
	
	/*for(i=day1;i<7;i++){
		grille+="<td class=\"cal_days\" onclick=\"calendrier_show(returninput,'"+fdate+"/"+(d.getMonth()+2)+"/"+d.getFullYear()+"')\">"+fdate+"</td>";
		fdate++;
	}*/
	
	grille+= "</tr>";

	if(showtime!=undefined && showtime!=false && showtime!="planning") grille+="<tr><td colspan=\"7\" nowrap>"+caltime()+"</td></tr>";
	grille+="</table>";
	 
	seldiv.innerHTML="<div class=\"cal_date\"><a href=\"javascript:calendrier_show(returninput,'1/'+(prevd1.getMonth()+1)+'/'+prevd1.getFullYear(),showtime,cal_callback)\" class=\"cal_arrow\"> < </a> <a href=\"javascript:calmonth("+d.getFullYear()+")\" class=\"cal_title\">"+montharray[d.getMonth()]+"</a> <a href=\"javascript:calyear('"+(d.getMonth()+1)+"')\" class=\"cal_title\">"+d.getFullYear()+"</a> <a href=\"javascript:calendrier_show(returninput,'1/'+(nextd1.getMonth()+1)+'/'+nextd1.getFullYear(),showtime,cal_callback)\" class=\"cal_arrow\"> > </a><a href=\"javascript:calendrier_close()\" class=\"cal_close\">X</a></div>";
	
	seldiv.innerHTML+="<div class=\"cal\"><div id=\"userdiv\"></div>"+grille+"<div id=\"datetime\" class=\"cal_datetime\"></div><div id=\"cal_ok\" class=\"cal_ok\" onclick=\"returndate();\"><span>OK </span></div>";
	
	if(showtime == "planning"){
	    seldiv.style.width = "400px";
		seldiv.innerHTML+= planning();
		document.getElementById('cal_ok').style.visibility = "hidden";
		requestusers();
	}
	   
	selectday(d.getDate());
	calcdatetime();
}

function calmonth(year){
	 var grille="<div class=\"cal_date\">"+year+"<a href=\"javascript:calendrier_close()\"class=\"cal_close\">X</a></div>";
         grille+="<table class=\"cal\"><tr>";
         for(i=0;i<12;i++){
             if(i==3 || i==6 || i==9) grille+="</tr><tr>";
             grille+="<td><a href=\"javascript:calendrier_show('"+returninput+"','1/"+(i+1)+"/"+year+"',showtime,cal_callback)\">"+montharray[i]+"</a></td>";
         }
         grille+="</table>";
         document.getElementById(caldiv).innerHTML=grille;
	 document.getElementById(caldiv).style.width="200px";
}

function calyear(month,year){
	 cd=new Date();
	 if(year==undefined) year=cd.getFullYear();
	 var grille="<div class=\"cal_date\"><a href=\"javascript:calendrier_close()\"class=\"cal_close\">X</a></div>";
         grille+="<table class=\"cal\"><tr><td><a href=\"javascript:calyear("+month+","+(year-1)+")\">&lt;&lt;</a></td>";
         
	 var c=0;
	 for(i=year-2;i<=(year+5);i++){
             grille+="<td><a href=\"javascript:calendrier_show('"+returninput+"','1/"+month+"/"+i+"',showtime,cal_callback)\">"+i+"</a></td>";
	     c++;
	     if(c==4) grille+="</tr><tr>";
	 }
         grille+="<td><a href=\"javascript:calyear("+month+","+(year+1)+")\">&gt;&gt;</a></td></tr></table>";
         document.getElementById(caldiv).innerHTML=grille;
	 document.getElementById(caldiv).style.width="200px";
}

function caltime(){
	 var dm=d.getMinutes();
	 var dh=d.getHours();
	 var ret="<select id=\"hours\" onchange=\"calcdatetime()\">";
	 if(hoursrange!=undefined)
	    for(i=0;i<hoursrange.length;i++) ret+="<option value=\""+hoursrange[i]+"\""+(dh==hoursrange[i]?" selected":"")+">"+hoursrange[i]+"</option>";
	 else
	    for(i=0;i<=23;i++) ret+="<option value=\""+i+"\""+(dh==i?" selected":"")+">"+i+"</option>";
	 ret+="</select>";
	 ret+=" h ";
	 ret+="<select id=\"minutes\" onchange=\"calcdatetime()\">";
	 for(i=0;i<=59;i+=timemode) ret+="<option value=\""+i+"\""+(dm==i?" selected":"")+">"+(i<10?"0":"")+i+"</option>";
	 ret+="</select>";

	 return ret;
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

function calcdatetime(day,hour,min,jour){
	if(day!=undefined){
	  d.setDate(day);
	  selectday(day);
	}
	document.getElementById('datetime').innerHTML=d.getDate()+"/"+(d.getMonth()+1)+"/"+d.getFullYear();
	if(showtime!=undefined && showtime!=false && showtime!="planning"){
	  d.setHours(document.getElementById('hours').value);
	  d.setMinutes(document.getElementById('minutes').value);
	  document.getElementById('datetime').innerHTML+=" "+d.getHours()+"h"+(d.getMinutes()<10?"0":"")+d.getMinutes();
	}
	if(showtime=="planning" ){
	   if(hour!=undefined){
	      document.getElementById('cal_ok').style.visibility="visible";
	      d.setHours(hour);
	      d.setMinutes(min);
	      if(jour==undefined)
		  document.getElementById('datetime').innerHTML+=" "+d.getHours()+"h"+(d.getMinutes()<10?"0":"")+d.getMinutes();

	   }else
	      document.getElementById('cal_ok').style.visibility="hidden";
	   
	   requestplanning();
	}
	cal_jour=jour;
}

function returndate(){
         if(returninput!="" && document.getElementById(returninput)){
	    document.getElementById(returninput).value=d.getDate()+"/"+(d.getMonth()+1)+"/"+d.getFullYear();
	    if(showtime!=undefined && cal_jour==undefined) document.getElementById(returninput).value+=" "+d.getHours()+"h"+(d.getMinutes()<10?"0":"")+d.getMinutes();
	 }
         var rdate=new Date(d.getFullYear(),d.getMonth(),d.getDate());
	 if(showtime!=undefined) rdate=new Date(d.getFullYear(),d.getMonth(),d.getDate(),d.getHours(),d.getMinutes(),0);
	 cal_timestamp=Math.round(rdate.getTime()/1000);
         calendrier_close();
         if(cal_callback!=undefined) cal_callback();
}

function calendrier_close(){
         document.getElementById(caldiv).style.display='none';
}

function planning(){
	 var html="<div id=\"cal_planning\" class=\"cal_planning\"><table>";
	 for(h=9;h<20;h++){
	    for(m=0;m<60;m+=5){
	      html+="<tr onclick=\"calcdatetime(undefined,"+h+","+m+")\"><td>"+h+":"+(m<10?"0":"")+m+"</td><td></td></tr>";
	    }
	 }
	 html+="</table></div><div style=\"clear:both;\"></div>";
	 
	 return html;
}

function requestusers(){
	 ajaxrequest("planning.php?users=1",requestuserscb);
}
function requestuserscb(httpRequest){
	 var xml=httpRequest.responseXML.documentElement;
         var html="<select id=\"user\" onchange=\"requestplanning(this.value);\">";
	 if(xml.getElementsByTagName("user")[0]){
	    for(i=0;i<xml.getElementsByTagName("user").length;i++){
		var user=xml.getElementsByTagName("user")[i].firstChild.nodeValue;
		var selected=0;
		if(selectuser!=undefined)
		  selected=(user==selectuser?1:0);
		else
		  selected=xml.getElementsByTagName("user")[i].getAttribute("selected");
		html+="<option value=\""+user+"\""+(selected==1?" selected":"")+">"+user+"</option>";
	    }
	 }
	 html+="</select>";
	 document.getElementById('userdiv').innerHTML=html;
	 requestplanning();
}

function requestplanning(user){
	 if(user==undefined && document.getElementById('user')) user=document.getElementById('user').value;
	 if(user!=undefined){
	   ajaxrequest("planning.php?user="+user+"&date="+(d.getTime()/1000),requestplanningcb);
	   selectuser=user;
	 }
}
function requestplanningcb(httpRequest){
	 var xml=httpRequest.responseXML.documentElement;

	 var rappel=new Array();
	 var nohour=new Array();
	 nohour[1]="";
	 nohour[2]="";
	 if(xml.getElementsByTagName("rappel")[0]){
	    for(i=0;i<xml.getElementsByTagName("rappel").length;i++){
		var daterappel=xml.getElementsByTagName("rappel")[i].getAttribute("date");
		var hide=xml.getElementsByTagName("rappel")[i].getAttribute("hide");
		var nom=xml.getElementsByTagName("rappel")[i].firstChild.nodeValue;
		var jour=xml.getElementsByTagName("rappel")[i].getAttribute("jour");
		if(hide==0) nom="<font color=\"#FF0000\">"+nom+"</font>";
		var rdate=new Date();
		rdate.setTime(daterappel*1000);
		rdate.setMinutes(round5(rdate.getMinutes()));
		rdate.setSeconds(0);
		var key=rdate.getHours()+":"+rdate.getMinutes();
		if(jour==0){
		  if(rappel[key]==undefined) 
		    rappel[key]=nom;
		  else
		    rappel[key]+="<br>"+nom;
		}else
		  nohour[jour]+="<br>"+nom;
	    }
	 }
	 var now=new Date();
	 var nowts=now.getTime();
	 var click="";
	 var html="<table>";
	 var rdate=new Date(d.getFullYear(),d.getMonth(),d.getDate());
	 rdate.setHours(14);
	 if(nowts>rdate.getTime())
	    pclass="cal_planning_lock";
	 else{
	    click="onclick=\"calcdatetime(undefined,0,0,1)\"";
	    pclass="cal_planning_select";
	 }
	    
	 html+="<tr "+click+" class=\""+pclass+"\"><td>Matin</td><td>"+nohour[1]+"</td></tr>";
	 for(h=9;h<20;h++){
	    if(h==14){
	      var rdate=new Date(d.getFullYear(),d.getMonth(),d.getDate());
	      rdate.setHours(20);
	      if(nowts>rdate.getTime())
		  pclass="cal_planning_lock";
	      else{
		  click="onclick=\"calcdatetime(undefined,0,0,1)\"";
		  pclass="cal_planning_select";
	      }
	      click="onclick=\"calcdatetime(undefined,0,0,2)\"";
	      html+="<tr "+click+" class=\""+pclass+"\"><td>Après-midi</td><td>"+nohour[2]+"</td></tr>";
	    }
	    for(m=0;m<60;m+=5){
	      var rdate=new Date(d.getFullYear(),d.getMonth(),d.getDate());
	      rdate.setHours(h);
	      rdate.setMinutes(m);
	      rdate.setSeconds(0);
	      var pclass="cal_planning_select";
	      var click="";
	      var txt="";
	      if(rappel[h+":"+m]!=undefined){
		txt=rappel[h+":"+m];
		pclass="cal_planning_item";
	      }
	      if(nowts>rdate.getTime())
		pclass="cal_planning_lock";
	      else
		if(txt=="") click="onclick=\"calcdatetime(undefined,"+h+","+m+")\"";
	      
	      html+="<tr "+click+" class=\""+pclass+"\"><td>"+h+":"+(m<10?"0":"")+m+"</td><td>"+txt+"</td></tr>";
	    }
	 }

	 html+="</table>";
	 document.getElementById('cal_planning').innerHTML=html;
}
function round5(x){
    return (x % 5) >= 2.5 ? parseInt(x / 5) * 5 + 5 : parseInt(x / 5) * 5;
}

function getPosition(obj){
    var topValue= 0,leftValue= 0;
    if(obj==undefined){
	leftValue=screen.width/2;
	topValue=document.body.scrollTop+(screen.height/2);
    }
    while(obj){
	leftValue+= obj.offsetLeft;
	topValue+= obj.offsetTop;
	obj= obj.offsetParent;
    }
    return new Array(leftValue,topValue);
}