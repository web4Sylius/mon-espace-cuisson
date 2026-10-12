

var trsptprice=Class.create();

trsptprice.prototype={
  
    initialize: function(societe,poids,paht,cp,pays){
      this.societe=societe.toLowerCase();
      this.poids=poids;
      this.paht=paht;
      this.cp=cp;
	  this.pays=pays;
      this.opts=new Array();
      this.fields=new Array();
      this.postaction=""
      switch(this.societe){
	case "transco colmar national":return this.transco();
	
	case "transco colmar international":return this.transcoi();
	
	case "mazet lille france":
	case "mazet lyon national":
	case "mazet gonesse national":
	return this.mazet();
	
	case "mazet lyon international":
	case "mazet lille international":
	case "mazet gonesse international":
	return this.mazeti();
	
	case "mory mulhouse national":
	case "mory sedan nat int":
	case "mory lyon national":
	case "mory lyon international":
	case "mory lyon enlev ext":
	case "mory-saint-louis":
	case "mory global lyon":
	case "mory global sedan":
	return this.mory();
	break;
	
	case "mory mulhouse international":return this.moryi();
	
	case "heppner le mans national":return this.heppner();
	case "heppner le mans":return this.heppner();
	case "heppner lyon":return this.heppnerlyon();
	
	case "heppner le mans international":return this.heppneri();
	
	case "Geodis Calberson Fourmies":return this.geodis();
	case "geodis calberson":return this.geodis();
	
	case "alloin le mans nat int":
	case "alloin mulhouse":
	return this.alloin();
	break;
	
	case "schenker mulhouse": return this.schenker();

	case "db schenker lyon": return this.db_schenker();
	case "db schenker chambery": return this.db_schenkerc();
	case "db schenker national": return this.db_schenker();
	case "db schenker international": return this.db_schenkeri();
	
	case "charvin transport":return this.charvin();

	case "sotrapoise":return this.sotrapoise();
	
	case "trans bk logistique":return this.transbk();
	
	case "bmv lyon":return this.bmv();
	
	case "gls":return this.gls();
	
      }

      return false;
    },

    transco:function(){
      var ret=new Array();
      
      ret.push({name:"Taxe fixe par position",price:2.5,ro:true});
      ret.push({name:"Taxe RDV",price:2.5,ro:true});
      
      var assur=0.002*this.paht;
      if(assur<5) assur=5;
      ret.push({name:"Assurance transport obligatoire (0.2% achat ttc, min 5&euro;)",price:assur,ro:false});
      
      ret.push({name:"Localité montagne et iles",price:10.8,ro:true});
      
      this.opts=ret;
      return ret;
    },
    
    transcoi:function(){
	var ret=new Array();
      
	ret.push({name:"Taxe RDV",price:2.5,ro:true});
      
	var assur=0.003*this.paht;
	if(assur<5) assur=5;
	ret.push({name:"Assurance transport obligatoire (0.3% achat ttc, min 5&euro;)",price:assur,ro:false});
      
	ret.push({name:"Frais de douane export Suisse",price:35,ro:true});
      
	this.opts=ret;
	return ret;
    },
    
    mazet:function(){
	var ret=new Array();
      
	ret.push({name:"Taxe RDV",price:2,ro:true});
	ret.push({name:"Contribution ECOSUR / Ecotaxe / sécurité routière",price:0.5,ro:true});
	if(this.paht<1000) price=7.5;
	if(this.paht>=1000 && this.paht<7600) price=0.007*this.paht;
	if(this.paht>=7600) price=0.005*this.paht;
	ret.push({name:"Assurance transport complémentaire (+1000 &euro;)",price:price,ro:false,color:(this.paht>1000?"red":"")});
	ret.push({name:"Taxe grande métropole par position Paris, Lyon, marseille, Monaco)",price:2.5,ro:true});
	
	this.opts=ret;
	return ret;
    },
    
    mazeti:function(){
	return this.opts;
    },
    
    mory:function(){
	var ret=new Array();
	
	var price=0.005*this.paht;
	ret.push({name:"Assurance transport obligatoire 0.5%",price:Math.round(price*100)/100,ro:false});
	var price=5.1;
	if(this.poids>100) price=Math.ceil(this.poids/100)*5.1;
	ret.push({name:"Livraison estivales cotières (du 15/06 au 15/09)",price:Math.round(price*100)/100,ro:true});
	
	this.opts=ret;
	this.postaction="document.getElementById('tdgold').style.display='table-cell';";
	return ret;
    },
    
    moryi:function(){
	var ret=new Array();
	
	var price=(0.005*this.paht);
	ret.push({name:"Assurance transport obligatoire 0.5%",price:Math.round(price*100)/100,ro:false});
	ret.push({name:"Frais de douane export Suisse",price:50,ro:true});
	
	this.opts=ret;
	this.postaction="document.getElementById('tdgold').style.display='table-cell';";
	return ret;
    },
    
    heppner:function(){
	var ret=new Array();
	
	ret.push({name:"Taxe RDV",price:8.5,ro:true});
	var price=0.006*this.paht;
	if(price<10) price=10;
	//Supplément pour livraison a destination des départements de la région parisienne (3€)
	var rp      = new Array(75,77,78,91,92,93,94,95);
	var dep   = this.cp.substr(0,2);
	var rpcolor = "";
	for(i=0;i<rp.length;i++)
	  	if(dep == rp[i]) rpcolor = "red";
	
	//ret.push({name  : "Taxe région parisienne <br> (75,77,78,91,92,93,94,95)",price : 3,ro: true,color : rpcolor});
	
	ret.push({name:"Assurance transport si complémentaire 0.6% min. 10&euro;",price:Math.round(price*100)/100,ro:false,color:"red"});
	ret.push({name:"Taxe contribution sûreté environnement",price:2.20,ro:true});
	//ret.push({name:"Taxe contribution sûreté sanitaire (CSSE)",price:0.29,ro:true});
	ret.push({name:"Localité montagne et Îles",price:41.10,ro:true});
	ret.push({name:"Service Star Date/Priority",price:0,ro:false});
	
	this.opts=ret;
	return ret;
    },

    heppnerlyon:function(){
	var ret=new Array();
	var dep   = this.cp.substr(0,2);
	
	ret.push({name:"Taxe RDV",price:3.41,ro:true});
	var price=0.006*this.paht;
	if(price<3) price=3;
	var pricefr=0.004*this.paht;
	if(pricefr<3) pricefr=17.60;
	//Supplément pour livraison a destination des départements de la région parisienne (3.20€)
	var rp      = new Array(75,77,78,91,92,93,94,95);
	var rpcolor = "";
	for(i=0;i<rp.length;i++)
	  	if(dep == rp[i]) rpcolor = "red";
	//Stardate
	if(this.poids < 10){
		var star = 6;
	}
	else if(this.poids < 40){
		var star = 7.2;
	}
	else if(this.poids < 70){
		var star = 9.6;
	}
	else if(this.poids < 100){
		var star = 11.9;
	}
	else if(this.poids < 250){
		var star = 17.7;
	}
	else if(this.poids < 500){
		var star = 29.6;
	}
	else if(this.poids < 1001){
		var star = 47.2;
	}
	ret.push({name  : "Taxe région parisienne <br> (75,77,78,91,92,93,94,95)",price : 3.20,ro: true,color : rpcolor});	
	ret.push({name:"Assurance transport si complémentaire 0.4% min. 17.60&euro; en France",price:Math.round(pricefr*100)/100,ro:true,color:(this.paht>1000||33*this.poids<=1000?"red":"")});
	ret.push({name:"Assurance transport si complémentaire 0.6% min. 3&euro; en Europe",price:Math.round(price*100)/100,ro:true,color:(this.paht>1000||33*this.poids<=1000?"red":"")});
	ret.push({name:"Taxe contribution sûreté au national",price:1.07,ro:true});
	ret.push({name:"Taxe contribution sûreté à l'international",price:2.25,ro:true});
	ret.push({name:"Localité montage et Îles",price:38,ro:true});
	ret.push({name:"Service Star Date/Priority",price:star,ro:false});
	
	this.opts=ret;
	return ret;
    },
    
    heppneri:function(){
	var ret=new Array();
	
	ret.push({name:"Frais d'étiquetage",price:0.80,ro:true});
	var price=0.004*this.paht;
	if(price<19.10) price=19.10;
	ret.push({name:"Assurance transport si complémentaire 0.4% min. 19.10&euro;",price:Math.round(price*100)/100,ro:false,color:"red"});
	ret.push({name:"Frais de douane export Suisse",price:40,ro:true});
	ret.push({name:"Service Star Date/Priority",price:0,ro:false});
	
	this.opts=ret;
	return ret;
    },
    
    geodis:function(){
	var ret=new Array();
	
	var dep=this.cp.substr(0,2);
	var rp=new Array(75, 77, 78, 91, 92, 93, 94, 95);
	for(i=0;i<rp.length;i++){
	    if(dep==rp[i]){
		var pricerp=2.5;
		if(this.poids>=30 && this.poids<100) pricerp=5;
		if(this.poids>=100) pricerp=7.5;
		ret.push({name:"Taxe région parisienne",price:pricerp,ro:true,color:"red"});
	    }
	}
	ret.push({name:"Contribution ECOSUR / Ecotaxe / sécurité routière",price:0.79,ro:true,color:"red"});
	var price=0.004*this.paht;
	ret.push({name:"Assurance transport obligatoire 0.4%",price:Math.round(price*100)/100,ro:false,color:"red"});
	
	this.opts=ret;
	return ret;
    },
    
    alloin:function(){
	var ret=new Array();
	
	var price=10+0.008*this.paht;
	ret.push({name:"Assurance transport obligatoire 0.8% +10&euro;",price:Math.round(price*100)/100,ro:false});
	ret.push({name:"Taxe grande métropole par position (Paris, Lyon, Marseille, Monaco)",price:5.4,ro:true});
	
	this.opts=ret;
	return ret;
    },
    
    schenker:function(){
	var ret=new Array();
	var rp=new Array(75,77,78,91,92,93,94,95);
	var ete=new Array("06000","06110","06130","06140","06160","06190","06210","06220","06230","06240","06250","06270","06310","06320","06330","06340","06360","06370","06390","06400","06410","06500","06510","06520","06550","06560","06570","06580","06590","06600","06610","06640","06650","06670","06700","06730","06800","11100","11110","11130","11210","11370","11430","11440","11480","11510","11560","13110","13260","13500","13600","13620","13960","14360","14390","14600","14800","17000","17110","17111","17113","17120","17123","17132","17137","17140","17150","17190","17200","17230","17240","17300","17310","17320","17340","17370","17390","17410","17420","17440","17450","17480","17530","17550","17560","17570","17580","17590","17600","17620","17630","17640","17650","17670",
			  "17690","17730","17740","17750","17780","17840","17880","17890","17920","17940","20000","20100","20110","20111","20112","20113","20114","20115","20116","20117","20118","20119","20121","20122","20123","20125","20126","20127","20128","20129","20130","20131","20132","20133","20134","20135","20136","20137","20138","20139","20140","20141","20142","20143","20144","20145","20146","20147","20148","20150","20151","20152","20153","20157","20160","20163","20164","20165","20166","20167","20168","20169","20170","20171","20172","20173","20190","20200","20212","20213","20214","20215","20217","20218","20219","20220","20221","20222","20224","20225","20226","20227","20228","20229","20230","20231","20232","20233","20234","20235","20236","20237","20238","20239",
		   "20240","20242","20243","20244","20245","20246","20248","20250","20251","20252","20253","20259","20260","20270","20272","20275","20276","20279","20287","20290","20600","20620","22100","22240","22370","22380","22410","22430","22500","22520","22550","22560","22680","22700","22730","22750","22870","29120","29160","29170","29217","29242","29253","29259","29340","29350","29360","29660","29680","29750","29760","29770","29900","29910","29930","29940","29950","29980","29990","30220","30240","33120","33121","33123","33148","33260","33340","33380","33470","33480","33510","33590","33680","33740","33780","33930","33950","33980","33990","34110","34140","34200","34250","34280","34300","34340","34350","34410","34450","34540","34750","35260","35350","35400","35430",
		   "35800","40130","40140","40150","40160","40170","40180","40200","40220","40230","40260","40390","40420","40440","40460","40480","40510","40530","40550","40560","40600","40660","44210","44250","44380","44410","44420","44490","44500","44510","44580","44600","44730","44740","44760","44770","56130","56170","56190","56260","56290","56340","56360","56370","56400","56410","56450","56470","56510","56520","56570","56590","56610","56640","56680","56700","56730","56740","56750","56760","56780","56840","56870","56950","59123","62152","62155","62176","62520","62600","62630","62780","62930","64100","64122","64200","64210","64220","64240","64250","64310","64340","64430","64480","64500","64600","64700","64780","66140","66160","66190","66200","66250","66290","66420",
		   "66440","66470","66480","66650","66660","66690","66700","66750","76111","76260","76450","76460","76470","76790","76980","80120","80230","80550","83120","83140","83150","83170","83190","83230","83240","83250","83260","83270","83310","83320","83330","83350","83370","83380","83400","83420","83430","83440","83520");
	var d=new Date();
	var color="";
	var month=d.getMonth();
	if(month>=5 && month<=7) {
	    for(i=0;i<ete.length;i++)
	      if(this.cp==ete[i]) color="red";
	}
	
	var rpcolor="";
	var dep=this.cp.substr(0,2);
	for(i=0;i<rp.length;i++)
	  if(dep==rp[i]) rpcolor="red";
	
	var price=0.0068*this.paht;
	if(price<7.3) {
		price = 7.3;
	}
	ret.push({name:"Taxe de surete obligatoire",price:0.92,ro:true,checked:true,color:"red"});
	ret.push({name:"Assurance transport 0.68% min 7.3&euro;",price:Math.round(price*100)/100,ro:true,color:(this.paht>1000||33*this.poids<=1000?"red":"")});
	ret.push({name:"Taxe région parisienne",price:3.87,ro:true,color:rpcolor});
	ret.push({name:"Surtaxe 10% du 01/06 au 31/08",price:0,ro:true,color:color,priceevent:"document.getElementsByName('priceopt[3]')[0].value=this.value*0.1"});
	ret.push({name:"Livraison professionel sur Rendez-vous",price:4.4,ro:true,color:rpcolor});
	ret.push({name:"Livraison pour les expéditions à destination des zones urbaines",price:9.37,ro:true,color:rpcolor});
	ret.push({name:"Contribution sûreté et qualité",price:1.24,ro:true,color:rpcolor});

	this.opts=ret;
	this.postaction="document.getElementById('tdgold').style.display='none';";
	return ret;
    },

    db_schenker:function(){
		
		var ret   = new Array();
		var d     = new Date();
		var month = d.getMonth();
		var dep   = this.cp.substr(0,2);

		var tarif_date_imperative = 3;
		var tarif_livr_particulier = 4.25;
		if(this.poids > 100){
			var coeff = this.poids - 100;
			coeff = (Math.floor(coeff / 10)) + 1;
			coeff = coeff * 0.1;
			coeff = 1 + ((coeff * 100) / 100);

			tarif_date_imperative = tarif_date_imperative * coeff;
			tarif_date_imperative = tarif_date_imperative.toFixed(2);

			tarif_livr_particulier = tarif_livr_particulier * coeff;
			tarif_livr_particulier = tarif_livr_particulier.toFixed(2);
		}

		//Taxe obligatoire
		ret.push({
			name 	: "Taxe de sureté obligatoire",
			price 	: 1.17,
			ro 	  	: true,
			checked : true,
			color 	: "red"
		});

		//Date impérative de livraison
		ret.push({
			name 	: "Date impérative de livraison",
			price 	: tarif_date_imperative,
			ro 	  	: true,
		});

		//Livraison particulier sur rendez-vous
		ret.push({
			name 	: "Livraison particulier sur rendez-vous",
			price 	: tarif_livr_particulier,
			ro 	  	: true,
		});

		//Supplément pour livraison a destination des départements de la région parisienne (3€)
		var rp      = new Array(75,77,78,91,92,93,94,95);
		var rpcolor = "";
		for(i=0;i<rp.length;i++)
		  	if(dep == rp[i]) rpcolor = "red";
		
		ret.push({
			name  : "Taxe région parisienne <br> (75,77,78,91,92,93,94,95)",
			price : 3.64,
			ro 	  : true,
			color : rpcolor
		});

		//Garantie en valeur déclarée => désactivée
		/*var price = 0.005 * this.paht;
		if(price < 6) price = 6; //Minimum de 6€
		ret.push({
			name  : "Assurance transport 0.5% min 6&euro;",
			price : Math.round(price*100)/100,
			ro 	  : true,
			color : (this.paht > 1000 ? "red" : "")
		});*/
			
		//Département avec majoration pour les livraisons en période estivale (10%)
		var ete   = new Array('04','06','11','13','20','30','34','40','64','66','83','84');
		var color = "";
		var dep   = this.cp;
		dep = dep.substr(0,2);

		if(month >= 5 && month <= 7){
		    for(i=0;i<ete.length;i++){
		    	if(dep == ete[i]) color = "red";
		    }
		}
		
		ret.push({
			name 	   : "Surtaxe 10% du 01/06 au 31/08 pour certains départements",
			price 	   : 0,
			ro 		   : true,
			color 	   : color,
			priceevent : "document.getElementsByName('priceopt[5]')[0].value = this.value * 0.1;"
		});

		this.opts       = ret;
		//this.postaction = "document.getElementById('tdgold').style.display='none';";

		return ret;
    },
    db_schenkerc:function(){
		
		var ret   = new Array();
		var d     = new Date();
		var month = d.getMonth();
		var dep   = this.cp.substr(0,2);

		var tarif_date_imperative = 5.83;
		var tarif_livr_particulier = 4.60;
		if(this.poids > 100){
			var coeff = this.poids - 100;
			coeff = (Math.floor(coeff / 10)) + 1;
			coeff = coeff * 0.1;
			coeff = 1 + ((coeff * 100) / 100);

			tarif_date_imperative = tarif_date_imperative * coeff;
			tarif_date_imperative = tarif_date_imperative.toFixed(2);

			tarif_livr_particulier = tarif_livr_particulier * coeff;
			tarif_livr_particulier = tarif_livr_particulier.toFixed(2);
		}

		//Taxe obligatoire
		ret.push({
			name 	: "Taxe de sureté obligatoire",
			price 	: 1.26,
			ro 	  	: true,
			checked : true,
			color 	: "red"
		});

		//Date impérative de livraison
		ret.push({
			name 	: "Date impérative de livraison",
			price 	: tarif_date_imperative,
			ro 	  	: true,
		});

		//Livraison particulier sur rendez-vous
		ret.push({
			name 	: "Livraison particulier sur rendez-vous",
			price 	: tarif_livr_particulier,
			ro 	  	: true,
		});

		//Supplément pour livraison a destination des départements de la région parisienne (3€)
		var rp      = new Array(75,77,78,91,92,93,94,95);
		var rpcolor = "";
		for(i=0;i<rp.length;i++)
		  	if(dep == rp[i]) rpcolor = "red";
		
		ret.push({
			name  : "Taxe région parisienne <br> (75,77,78,91,92,93,94,95)",
			price : 3.93,
			ro 	  : true,
			color : rpcolor
		});

		//Garantie en valeur déclarée => désactivée
		var price = 0.0075 * this.paht;
		if(price < 15) price = 15; //Minimum de 15€
		ret.push({
			name  : "Assurance transport 0.75% min 15&euro;",
			price : Math.round(price*100)/100,
			ro 	  : true,
			color : (this.paht > 1000 ? "red" : "")
		});
			
		//Département avec majoration pour les livraisons en période estivale (10%)
		var ete   = new Array('04','06','11','13','20','30','34','40','64','66','83','84');
		var color = "";
		var dep   = this.cp;
		dep = dep.substr(0,2);

		if(month >= 5 && month <= 7){
		    for(i=0;i<ete.length;i++){
		    	if(dep == ete[i]) color = "red";
		    }
		}
		
		ret.push({
			name 	   : "Surtaxe 10% du 01/06 au 31/08 pour certains départements",
			price 	   : 0,
			ro 		   : true,
			color 	   : color,
			priceevent : "document.getElementsByName('priceopt[5]')[0].value = this.value * 0.1;"
		});

		this.opts       = ret;
		//this.postaction = "document.getElementById('tdgold').style.display='none';";

		return ret;
    },
	
	db_schenkeri:function(){
	  var ret=new Array();
	
	  var price=0.0075*this.paht;
	  if(price<15) price=15;
	  ret.push({name:"Assurance transport 0.75% min 15&euro;",price:Math.round(price*100)/100,ro:true,color:(this.paht>1000||33*this.poids<=1000?"red":"")});

	  ret.push({name:"Frais de douane export",price:47.71,ro:true});
	  ret.push({name:"Frais de douane import",price:65.59,ro:true});
	  
	  this.opts=ret;
	  return ret;
	},
    
    charvin:function(){
      var ret=new Array();
      var price=0.007*this.paht;
      ret.push({name:"Assurance transport 0.7%",price:Math.round(price*100)/100,ro:true,color:(this.paht>1000||33*this.poids<=1000?"red":"")});
      this.opts=ret;
      return ret;
    },
    
    sotrapoise:function(){
      var ret=new Array();
      var price=0.5*this.paht/100;
      ret.push({name:"Assurance transport",price:Math.round(price*100)/100,ro:true,color:(this.paht>1000||33*this.poids<=1000?"red":"")});
      this.opts=ret;
      
      return ret;
    },
    
    transbk:function(){
      var ret=new Array();
      var price=0.003*this.paht;
      if(price<8) price=8;
      ret.push({name:"Assurance transport",price:Math.round(price*100)/100,ro:true,color:(this.paht>1000?"red":"")});
      this.opts=ret;
    },
	
	gls:function(){ 
        var ret=new Array(); 
        ret.push({name:"Livraison en Corse",price:28.33,ro:true,color:(this.pays==181?"red":"")});
        ret.push({name:"Livraison en zone montagneuse",price:5.14,ro:true});
        this.opts=ret;
    },
	
	bmv:function(){
	  var ret=new Array();
	  var price=0.006*this.paht;
	  if(price<16.66) price=16.66;
	  ret.push({name:"Assurance transport 0.60% min 16.66&euro;",price:Math.round(price*100)/100,ro:true,color:(this.paht>1000||33*this.poids<=1000?"red":"")});
	  ret.push({name:"Enlèvement extérieur",price:27,ro:true});

	  this.opts=ret;
	  return ret;
	},
    
    generate:function(){
	var html="<table width=\"100%\">";
	var js="";
	for(i=0;i<this.opts.length;i++){
	  if(this.opts[i].priceevent){
	  	document.getElementsByName('trsptcoutval')[0].setAttribute("onkeyup","adv_save(this.name);"+this.opts[i].priceevent);
	  } else {
	  	document.getElementsByName('trsptcoutval')[0].setAttribute("onkeyup","adv_save(this.name);");
	  }
	  if(this.fields!=undefined && this.fields["priceopt["+i+"]"]!=undefined) this.opts[i].price=this.fields["priceopt["+i+"]"];
	  var ro="";
	  if(this.opts[i].ro) ro=" readonly";
	  var value="<input type=\"text\" name=\"priceopt["+i+"]\" value=\""+this.opts[i].price+"\" size=\"5\" onkeyup=\"adv_autosave(this.name)\""+ro+">";

	  html+="<tr><td width=\"20\"><input type=\"checkbox\" name=\"chkopt["+i+"]\" value=\"1\" onclick=\"trspttarif();this.value=Number(this.checked);adv_save(new Array('priceopt["+i+"]','chkopt["+i+"]','optname["+i+"]'));\""+((this.fields!=undefined&&this.fields["chkopt["+i+"]"]==1)||this.opts[i].checked?" checked":"")+"></td><td style=\"color:"+this.opts[i].color+";\"><input type=\"hidden\" name=\"optname["+i+"]\" value=\""+this.opts[i].name+"\">"+this.opts[i].name+"</td><td>"+value+"</td></tr>";
	  if(this.opts[i].checked) this.postaction+="adv_save(new Array('priceopt["+i+"]','chkopt["+i+"]','optname["+i+"]'));";
	}
	html+="</table><input type=\"hidden\" name=\"priceoptnb\" value=\""+i+"\">";
	
	return html;
    }
    
}