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
var client = undefined;
var horaire_bis;

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
         if(withtime!=undefined && withtime!='planning' && withtime!='planning_rdv' && withtime!='planning_suivi') showtime=true;
         //document.body.onclick=calendrier_close;

}

function calendrier_show(input,newdate,withtime,callback,val=null,horaire=null){
	if (horaire == null)
	{
		horaire_bis = null; 
	}
	else
	{
		horaire_bis = 1;
	}
	returninput = input; 
	showtime = withtime; 
	curday = 0;
	cal_callback = undefined;
	
	if(callback != undefined) cal_callback = callback;

	if(!document.getElementById(caldiv)){
		initcalendrier(showtime);
	}

	var seldiv = document.getElementById(caldiv);
	
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

	d1 = new Date(d.getFullYear(),d.getMonth(),1); //today + 1 jour
	tmp = d1.getTime()-86400000;//on enlève un jour (86400000 miliseconde) car on a mis 1 jour lors de la création de d1 (j'imagine que c'était obligatoire) //today
	prevd1 = new Date();
	prevd1.setTime(tmp); //set à today
	prevd1 = new Date(prevd1.getFullYear(),prevd1.getMonth(),1); //set à today + 1 jour
	tmp = d1.getTime()+(86400000*32); //today + 33 jours
	nextd1 = new Date();
	nextd1.setTime(tmp); //today + 33jours
	day1 = d1.getDay(); // 1 jour

	days = daycount(d);//recupere le nombre de jour du mois courant
	grille = "<table id=\"cal\">";
	grille+= "<tr align=\"center\" class=\"cal_weekdays\"><td>D</td><td>L</td><td>M</td><td>M</td><td>J</td><td>V</td><td>S</td></tr><tr align=\"center\">"; // lettre de la semaine

	for(i=0; i<day1-1; i++){ //si day1 != 1 mais day1 est toujours egale à 1. ca doit etre pour une condition que je voit pas encore
		grille+= "<td></td>";
	}
	
	dprev = new Date();//today à effacer imo dprev useless af
	dprev.setTime(d1.getTime()-8640000);//touours today
	
	if(day1 != 0) grille+="<td></td>"; // grille+="<td class=\"cal_days\" onclick=\"calendrier_show(returninput,'1/"+(dprev.getMonth()+1)+"/"+dprev.getFullYear()+"')\">"+dprev.getDate()+"</td>";
	//j'imagine que c'est pour l'espace avant le 1 (genre si le 1 comme autre que lundi)
	for(i=1; i<=days; i++){ //on va afficher les jours du mois
		if(day1 == 7){ //reset a la fin de la ligne
			day1 = 0;
			grille+= "</tr><tr align=\"center\">";
		}
		
		bg = "";
		
		if(i == curday) bg = " bgcolor=\"#C70000\""; //on met en rouge le jour selectonné 
		
		d1 = new Date(d.getFullYear(),d.getMonth(),i); //today + i
		var wday = d1.getDay(); //jour + i
		
		if(mints == undefined || d1.getTime()>=mints && wday!=0 && wday!=6)
			grille+= "<td id=\"td_"+i+"\""+bg+" class=\"cal_days\" onclick=\"calcdatetime("+i+");\" style=\"cursor:pointer;\">"+i+"</td>";
		else //mints est toujours undefined (set comme ca et jamais change) donc on rentre jamais dans le else
			grille+= "<td id=\"td_"+i+"\""+bg+" class=\"cal_days_lock\">"+i+"</td>";
		
		day1++;
	}
         
	fdate = 1;
	
	
	grille+= "</tr>";

	if (horaire_bis == null)
	{	
		if(showtime!=undefined && showtime!=false && showtime!="planning" && showtime!="planning_rdv" && showtime!="planning_suivi") grille+="<tr><td colspan=\"7\" nowrap>"+caltime()+"</td></tr>";
	}
	grille+="</table>";
	

	seldiv.innerHTML="<div class=\"cal_date\"><a href=\"javascript:calendrier_show(returninput,'1/'+(prevd1.getMonth()+1)+'/'+prevd1.getFullYear(),showtime,cal_callback, null, horaire_bis)\" class=\"cal_arrow\"> < </a> <a href=\"javascript:calmonth("+d.getFullYear()+")\" class=\"cal_title\">"+montharray[d.getMonth()]+"</a> <a href=\"javascript:calyear('"+(d.getMonth()+1)+"')\" class=\"cal_title\">"+d.getFullYear()+"</a> <a href=\"javascript:calendrier_show(returninput,'1/'+(nextd1.getMonth()+1)+'/'+nextd1.getFullYear(),showtime,cal_callback, null, horaire_bis)\" class=\"cal_arrow\"> > </a><a href=\"javascript:calendrier_close()\" class=\"cal_close\">X</a></div>"; //gere les fleches pour passer les mois
	
	seldiv.innerHTML+="<div class=\"cal\"><div id=\"userdiv\"></div>"+grille+"<div id=\"datetime\" class=\"cal_datetime\"></div><div id=\"cal_ok\" class=\"cal_ok\" onclick=\"returndate();\"><span>OK </span></div>";

	if(showtime == "planning" || showtime == "planning_rdv" || showtime == "planning_suivi"){ 
		var type = "";
	    seldiv.style.width = "400px";
		seldiv.innerHTML+= planning(showtime);
		document.getElementById('cal_ok').style.visibility = "hidden";
		if(val != null) client = 1;
		if(showtime!=="planning"){ type = showtime; }
		requestusers(type);
	}
	   
	selectday(d.getDate());
	calcdatetime();
}

function calmonth(year){
	 var grille="<div class=\"cal_date\">"+year+"<a href=\"javascript:calendrier_close()\"class=\"cal_close\">X</a></div>";
         grille+="<table class=\"cal\"><tr>";
         for(i=0;i<12;i++){
             if(i==3 || i==6 || i==9) grille+="</tr><tr>";
             grille+="<td><a href=\"javascript:calendrier_show('"+returninput+"','1/"+(i+1)+"/"+year+"',showtime,cal_callback, null, horaire_bis)\">"+montharray[i]+"</a></td>";
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
             grille+="<td><a href=\"javascript:calendrier_show('"+returninput+"','1/"+month+"/"+i+"',showtime,cal_callback, null, horaire_bis)\">"+i+"</a></td>";
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
         days=dcount[d.getMonth()]; //prend le nombre de jour du mois
         bissextile = d.getFullYear() %4; //check la bissextilite pour savoir s'il faut mettre fevrier à 29
         if(d.getMonth()==1 && bissextile==0) days=29; //getMonth va de 0 à 11 d'où le ==1 
         return days;
}

function calcdatetime(day,hour,min,jour){
	var type = "";
	if(day!=undefined){
		d.setDate(day);
		selectday(day);
	}
	document.getElementById('datetime').innerHTML=d.getDate()+"/"+(d.getMonth()+1)+"/"+d.getFullYear();
	if(showtime!=undefined && showtime!=false && showtime!="planning" && showtime != "planning_rdv" && showtime != "planning_suivi"){
		d.setHours(document.getElementById('hours').value);
		d.setMinutes(document.getElementById('minutes').value);
		document.getElementById('datetime').innerHTML+=" "+d.getHours()+"h"+(d.getMinutes()<10?"0":"")+d.getMinutes();
	}

	if(showtime!=="planning"){
		type = showtime;
	}

	if(showtime=="planning" || showtime=="planning_rdv" || showtime=="planning_suivi"){
		if(hour!=undefined){
			document.getElementById('cal_ok').style.visibility="visible";
			d.setHours(hour);
			d.setMinutes(min);
			if(jour==undefined){
				document.getElementById('datetime').innerHTML+=" "+d.getHours()+"h"+(d.getMinutes()<10?"0":"")+d.getMinutes();
			}

		}else{
			document.getElementById('cal_ok').style.visibility="hidden";
		}

		requestplanning(undefined,type);
	}
	cal_jour=jour;
}

function returndate(){
         if(returninput!="" && document.getElementById(returninput)){
	    document.getElementById(returninput).value=d.getDate()+"/"+(d.getMonth()+1)+"/"+d.getFullYear();
	    if(document.getElementById(returninput+'_hidden')){
	    	document.getElementById(returninput+'_hidden').value=d.getTime();
	    }
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

function planning(type){
	var html="<div id=\"cal_planning\" class=\"cal_planning\"><table>";
	if(type=="planning_rdv"){
		var h_planning = 8;
		var m_planning = 20;
		for(h=h_planning;h<20;h++){
			for(m=0;m<60;m+=m_planning){
				html+="<tr onclick=\"calcdatetime(undefined,"+h+","+m+")\"><td>"+h+":"+(m<10?"0":"")+m+"</td><td></td></tr>";
			}
		}
	}
	if(type=="planning_suivi"){
		for(i=0;i<30;i++){
			html+="<tr onclick=\"calcdatetime(undefined,0,0,2)\"><td>suivi</td><td></td></tr>";
		}
	}
	else{
		var h_planning = 9;
		var m_planning = 5;
		for(h=h_planning;h<20;h++){
			for(m=0;m<60;m+=m_planning){
				html+="<tr onclick=\"calcdatetime(undefined,"+h+","+m+")\"><td>"+h+":"+(m<10?"0":"")+m+"</td><td></td></tr>";
			}
		}
	}
	 
	 html+="</table></div><div style=\"clear:both;\"></div>";
	 
	 return html;
}

function requestusers(type=null){
	if(type==null){
		ajaxrequest("planning.php?users=1",requestuserscb);
	}
	else{
		ajaxrequest("planning.php?users=1&type="+type,requestuserscb);
	}
}
function requestuserscb(httpRequest){
	 var xml=httpRequest.responseXML.documentElement;
	 var type = xml.getElementsByTagName("type")[0].textContent;
	 var html="<select id=\"user\" onchange=\"requestplanning(this.value,'"+type+"');\">";
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
	 requestplanning(undefined,type);
}

function requestplanning(user,type=null){
	 if(user==undefined && document.getElementById('user')) user=document.getElementById('user').value;
	 if(user!=undefined){
	   ajaxrequest("planning.php?user="+user+"&date="+(d.getTime()/1000)+"&type="+type,requestplanningcb);
	   selectuser=user;
	 }
}
function requestplanningcb(httpRequest){
	var xml=httpRequest.responseXML.documentElement;

	var rappel=new Array();
	var nohour=new Array();
	nohour[1]="";
	nohour[2]="";
	var nohoursuivi=new Array();
	nohoursuivi[1]="";
	nohoursuivi[2]="";
	var mat=apr=0;
	var type = xml.getElementsByTagName("type")[0].textContent;
	if(xml.getElementsByTagName("rappel")[0]){
	    for(i=0;i<xml.getElementsByTagName("rappel").length;i++){
			var daterappel=xml.getElementsByTagName("rappel")[i].getAttribute("date");
			var hide=xml.getElementsByTagName("rappel")[i].getAttribute("hide");
			var nom=xml.getElementsByTagName("rappel")[i].firstChild.nodeValue;
			var jour=xml.getElementsByTagName("rappel")[i].getAttribute("jour");
			var to=xml.getElementsByTagName("rappel")[i].getAttribute("from");
			if(hide==0) nom="<font color=\"#FF0000\">"+nom+"</font>";
			var rdate=new Date();
			rdate.setTime(daterappel*1000);
			rdate.setMinutes(round5(rdate.getMinutes()));
			rdate.setSeconds(0);
			var key=rdate.getHours()+":"+rdate.getMinutes();
			if(jour==0){
				if(rappel[key]==undefined){
					rappel[key]=nom;
					var split = key.split(':');
				}else{
					rappel[key]+="<br>"+nom;
				}
			}else{
				nohour[jour]+="<br>"+nom;
				if(jour==1 && to=='client') mat++;
				if(jour==2 && to=='client') apr++;
			}
	    }
	}
	var now=new Date();
	var nowts=now.getTime();
	var click="";
	var html="<table>";
	var rdate=new Date(d.getFullYear(),d.getMonth(),d.getDate());
	rdate.setHours(14);
	 
	
	if(type=="planning_rdv"){

		if(nowts>rdate.getTime())
			pclass="cal_planning_lock";
		else{
			click="onclick=\"calcdatetime(undefined,0,0,1)\"";
			pclass="cal_planning_select mat";
		}

		html+="<tr "+click+" class=\""+pclass+"\"><td>Matin</td><td>"+nohour[1]+"</td></tr>";

		var h_planning=8;
		var m_planning=20;
		for(h=h_planning;h<20;h++){
	    if(h==14){
			var rdate=new Date(d.getFullYear(),d.getMonth(),d.getDate());
			rdate.setHours(20);
			
			if(nowts>rdate.getTime())
				pclass="cal_planning_lock";
			else{
				click="onclick=\"calcdatetime(undefined,0,0,1)\"";
				pclass="cal_planning_select apr";
			}
			
			click="onclick=\"calcdatetime(undefined,0,0,2)\"";
			html+="<tr "+click+" class=\""+pclass+"\"><td>Après-midi</td><td>"+nohour[2]+"</td></tr>";
	    }
		
	    for(m=0;m<60;m+=m_planning){
			var rdate=new Date(d.getFullYear(),d.getMonth(),d.getDate());
			rdate.setHours(h);
			rdate.setMinutes(m);
			rdate.setSeconds(0);
			if(h<13 && m<59){
				var pclass="cal_planning_select mat";
			}else{
				var pclass="cal_planning_select apr";
			}
			
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

	}//// faire avec le planning suivi pour avoir une boucle de 30, sans heure ni minute
	else if(type=="planning_suivi"){
		nohoursuivi_1 = nohour[1].split('<br>');
		nohoursuivi_2 = nohour[2].split('<br>');
		var nb_mat = 0;
		var nb_apr = 0;
		var cmpt_mat = 1;
		var cmpt_apr = 1;
		if(nohoursuivi_1.length > 1){
			nb_mat = nohoursuivi_1.length - 1;
		}
		if(nohoursuivi_2.length > 1){
			nb_apr = nohoursuivi_2.length - 1;
		}

		for(i=0;i<30;i++){
			if(nohoursuivi_1.length > 1 && cmpt_mat < (nohoursuivi_1.length)){
				var pclass="cal_planning_item mat";
				var click="";
				html+="<tr "+click+" class=\""+pclass+"\"><td>suivi</td><td>"+nohoursuivi_1[cmpt_mat]+"</td></tr>";
				cmpt_mat++;
			}
			else if(i < 15){
				var pclass="cal_planning_select mat";
				var click="onclick=\"calcdatetime(undefined,0,0,1)\"";
				html+="<tr "+click+" class=\""+pclass+"\"><td>suivi</td><td></td></tr>";
			}else{
				if(nohoursuivi_2.length > 1 && cmpt_apr < (nohoursuivi_2.length)){
					var click="";
					var pclass="cal_planning_item apr";
					html+="<tr "+click+" class=\""+pclass+"\"><td>suivi</td><td>"+nohoursuivi_2[cmpt_apr]+"</td></tr>";
					cmpt_apr++;
				}
				else if(i > 15){
					var click="onclick=\"calcdatetime(undefined,0,0,2)\"";
					var pclass="cal_planning_select apr";
					html+="<tr "+click+" class=\""+pclass+"\"><td>suivi</td><td></td></tr>";
				}
			}
		}
	}
	else{
		if(nowts>rdate.getTime())
			pclass="cal_planning_lock";
		else{
			click="onclick=\"calcdatetime(undefined,0,0,1)\"";
			pclass="cal_planning_select mat";
		}

		html+="<tr "+click+" class=\""+pclass+"\"><td>Matin</td><td>"+nohour[1]+"</td></tr>";
		var h_planning=9;
		var m_planning=5;
		for(h=h_planning;h<20;h++){
	    if(h==14){
			var rdate=new Date(d.getFullYear(),d.getMonth(),d.getDate());
			rdate.setHours(20);
			
			if(nowts>rdate.getTime())
				pclass="cal_planning_lock";
			else{
				click="onclick=\"calcdatetime(undefined,0,0,1)\"";
				pclass="cal_planning_select apr";
			}
			
			click="onclick=\"calcdatetime(undefined,0,0,2)\"";
			html+="<tr "+click+" class=\""+pclass+"\"><td>Après-midi</td><td>"+nohour[2]+"</td></tr>";
	    }
		
	    for(m=0;m<60;m+=m_planning){
			var rdate=new Date(d.getFullYear(),d.getMonth(),d.getDate());
			rdate.setHours(h);
			rdate.setMinutes(m);
			rdate.setSeconds(0);
			if(h<13 && m<59){
				var pclass="cal_planning_select mat";
			}else{
				var pclass="cal_planning_select apr";
			}
			
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
	}
	
	

	html+="<tr></tr>";
	//html+="<tr><td>Matin :</td><td style=\""+(mat>14?'color:red':'')+"\">"+mat+"</td></tr>";
	//html+="<tr><td>Aprèm :</td><td style=\""+(apr>14?'color:red':'')+"\">"+apr+"</td></tr>";
	html+="</table>";
	document.getElementById('cal_planning').innerHTML=html;

	if(client == 1 && type!=="planning_suivi"){
		if(mat==15 || mat>15){
			//alert('Limite de rappel de la matinée atteinte');
			var myElement = document.getElementsByClassName('mat');
			for (var i=0; i<myElement.length; i++) {
				myElement[i].className ='cal_planning_lock mat';
			}
		}
		if(apr==4 || apr>4){
			//alert('Limite de rappel de l\'après-midi atteinte');
			var myElement = document.getElementsByClassName('apr');
			for (var i=0; i<myElement.length; i++) {
				myElement[i].className ='cal_planning_lock apr';
			}
		}
	}
	
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