<script language="javascript" src="calendrier.js"></script>
<script language="javascript" src="../javascripts/prototype.js"></script>
<script language="javascript" src="filebox.js"></script>
<script language="javascript">
<!--
	function resize()
		{
		window.open ('petit_marque.php', 'Marque', 'Width=400, Height=175, toolbar=no, directories=no, location=no, menubar=no, resizable=no, scrollbars=no, status=no') ;
		}
	function resize2(caract)
		{
		window.open ('petit_caract.php?caractid='+caract+'', 'Caractéristique', 'Width=470, Height=250, toolbar=no, directories=no, location=no, menubar=no, resizable=no, scrollbars=no, status=no') ;
		}
	function resize3(art)
		{
		window.open ('petit_champ.php?artid='+art+'', 'Champ', 'Width=450, Height=300, toolbar=no, directories=no, location=no, menubar=no, resizable=no, scrollbars=no, status=no') ;
		}
	function suppr(art1,art2)
		{
		window.open ('supprimage.php?art='+art1+'&sup='+art2+'', 'Marque', 'Width=400, Height=175, toolbar=no, directories=no, location=yes, menubar=no, resizable=no, scrollbars=no, status=no') ;
		}
	function passerbase(art1,art2)
		{
		window.open ('passerbase.php?art='+art1+'&sup='+art2+'', 'PasserBase', 'Width=1300, Height=500, toolbar=no, directories=no, location=yes, menubar=no, resizable=no, scrollbars=no, status=no') ;
		}
	function changecat(action_envoyee,categ)
		{
		document.formu.action2.value=action_envoyee;
		document.formu.old.value=action_envoyee;
		document.formu.moncatid.value=categ;
		document.formu.submit() ;
		}
	function Deplacer(l1,l2)
		{
		var selectedList = new Array();
		for (var i=0; i < l1.options.length; i++)
			{
			if (l1.options[i].selected)
				{
				//selectedList.push(l1.options[i].selectedindex);
				o=new Option(l1.options[i].text,l1.options[l1.options.selectedIndex].text);
				l2.options[l2.options.length]=o;
				}
			}
		effacer(l1);
		}
	function effacer(l1)
		{
		var compteselect=0;
		for (yo = 0 ; yo < l1.length ; yo++)
			{
			if (l1.options[yo].selected == true)
				{
				compteselect++;
				}
			if (compteselect > 0)
				{
				for (yo = 0 ; yo < l1.length ; yo++)
					{
					if (l1.options[yo].selected == true)
						{
						l1.options[yo] = null;
						}
					}
				effacer(l1);
				}
			}
		}
	function supprimer_piece(piece)
		{
		window.open ('suppr_piece.php?pieceid='+piece+'', 'Pièce', 'Width=400, Height=175, toolbar=no, directories=no, location=yes, menubar=no, resizable=no, scrollbars=no, status=no') ;
		}
	function pascommandable()
		{

		if (document.getElementsByName('prix_aff')[0].checked == false)
			{
			document.getElementById('prix').style.display='none';
			}
		}
	function commandable()
		{

		document.getElementById('prix').style.display='block';
		}
	function affichepasprix()
		{
		document.formu.prix.value='0';
		document.getElementById('prix').style.display='none';
		}
	function afficheprix(prix)
		{
		document.formu.prix.value=prix;
		document.getElementById('prix').style.display='block';
		}
	function supprimg_caractval(artid,caractvalid)
		{
		window.open ('supprimg_caractval.php?artid='+artid+'&caractvalid='+caractvalid+'', 'Caractéristique', 'Width=400, Height=175, toolbar=no, directories=no, location=yes, menubar=no, resizable=no, scrollbars=no, status=no') ;
		}
	function reappro(commandable){
		switch(commandable){
		    case '0':
		    case '1':
		    document.getElementById('divappro').style.display='none';
		    break;
		    case '2':
		    document.getElementById('divappro').style.display='block';
		    break;
		}
	}
	function nbonly(event,decimal,input) {
	  // Compatibilité IE / Firefox
	  if(!event && window.event) {
	    event=window.event;
	  }

	  if(decimal==undefined) decimal=false;

	  // IE
	  if((event.keyCode < 48 || event.keyCode > 57) && event.keyCode!=8) {
	      if(!decimal || event.keyCode!=46 || input.value.indexOf('.')>0){
		  event.returnValue = false;
		  event.cancelBubble = true;
	      }
	  }

	  // DOM
	  if(event.which!=0 && (event.which < 48 || event.which > 57) && event.which!=8) {
	    if(!decimal || event.which!=46 || input.value.indexOf('.')>0){
		  event.preventDefault();
		  event.stopPropagation();
	    }
	  }
	}

//-->
</script>

<script language="JavaScript">
<!--
// PROCESS : Permute les element d'un "Select"
// INPUT : 	menu - Ref. du "Select"
//			way	 - Sens de permuttation (-1 = UP / +1 = DOWN)
function menuswitch(menu, way)
	{
	// Init
	var menumax = menu.length -2;
	var menusel = menu.selectedIndex;

	// Debordement
	if ((menusel < 0) || (menusel < 1 && way == -1) || (menusel > menumax && way == 1))
		{
		return false;
		}

	// Permutation
	tmpopt = new Option( menu.options[menusel+way].text, menu.options[menusel+way].value );

	menu.options[menusel+way].text = menu.options[menusel].text;
	menu.options[menusel+way].value = menu.options[menusel].value;
	menu.options[menusel+way].selected = true;

	menu.options[menusel].text = tmpopt.text;
	menu.options[menusel].value = tmpopt.value;
	menu.options[menusel].selected = false;
	return true;
	}
/*
// PROCESS : Selectionner tout les éléments avant de les soumettres
function menusubmit(menu)
	{
	var menulen = menu.length;

	for (i=0; i < menulen; i++)
		{
		menu.options[i].selected = true;
		}
	}*/
// AUTHOR : Thierry 'Vynka' LAGASSE
//-->

function setmodif(){
    document.getElementsByName('modif')[0].value='1';
}

function nolf(event){
    if(event.keyCode==13){
	event.preventDefault();
	event.stopPropagation();
    }
}

function srcsimilaire(txt){
    if(txt.length<=3) return;
    document.getElementById('results').innerHTML='';
    ajaxrequest('marketplace/src.php?sim='+txt,srcsimilaire_cb);
}
function srcsimilaire_cb(httprequest){
    var xml=httprequest.responseXML.documentElement;
    var html='';
    var vistxt=new Array();
    vistxt[0]='<span style="color:red;">Invisible</span>';
    vistxt[1]='Visible';
    vistxt[2]='Supprimé';

    for(i=0;i<xml.childNodes.length;i++){
	var node=xml.childNodes[i];
	if(node.nodeName=='group'){
	    var content='';
	    var prods=node.getElementsByTagName('prod');
	    for(i2=0;i2<prods.length;i2++) content+='<br><img src="/configurations/images/produits/br/'+prods[i2].getAttribute('artid')+'.jpg" height="50" align="absmiddle"> '+prods[i2].getAttribute('id')+' ('+prods[i2].getAttribute('ref');
	    html+='<p><a href="javascript:document.getElementsByName(\'gartid\')[0].value='+prods[0].getAttribute('artid')+';clique(\'similaire\',\'similaire\');"><img src="images/plus.gif" align="absmiddle"> Ajouter au groupe</a>'+content+'</p><hr>';
	}
	if(node.nodeName=='prod'){
	     var visible=node.getAttribute('visible');
	     var artid=node.getAttribute('artid');
	     if(artid!=<?php print intval($artid); ?>) html+='<div><img src="/configurations/images/produits/br/'+artid+'.jpg" height="70" align="left"> <a href="javascript:document.getElementsByName(\'gartid\')[0].value='+artid+';clique(\'similaire\',\'similaire\');"><img src="images/plus.gif" align="absmiddle"> Nouveau groupe avec '+node.getAttribute('id')+' ('+node.getAttribute('ref')+')</a><br>'+vistxt[visible]+'</div><br clear="all"><hr>';
	}
    }

    if(html=='') html='Aucun résultat';
    document.getElementById('results').innerHTML=html;
}

function showcaract(caractid){
    if(document.getElementById('caract'+caractid).style.display=='none'){
      document.getElementById('caract'+caractid).style.display='block';
      document.getElementById('icon'+caractid).src='images/moins.jpg';
    }else{
      document.getElementById('caract'+caractid).style.display='none';
      document.getElementById('icon'+caractid).src='images/plus.jpg';
    }
}

function srcartid(src){
      if(src.length<=3) return;
      var url="marketplace/src.php?txt="+src
      ajaxrequest(url,srcCallback);
      document.getElementById('results').innerHTML='<img src="/configurations/images/templates/ajax_wait.gif">';
}

function srcCallback(httprequest){
      var xml=httprequest.responseXML.documentElement;
      var html='';

      var vistxt=new Array();
      vistxt[0]='<br><span style="color:red;">Invisible</span>';
      vistxt[1]='<br>Visible';
      if(xml.getElementsByTagName('prod')[0]){
	  html+='<table width="100%" style="border:solid thin #808080;"><tr>';
	  for(i=0;i<xml.getElementsByTagName('prod').length;i++){
		var description=xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
		var id=xml.getElementsByTagName('prod')[i].getAttribute('id');
		var artid=xml.getElementsByTagName('prod')[i].getAttribute('artid');
		var actif=xml.getElementsByTagName('prod')[i].getAttribute('active');
		var stock=xml.getElementsByTagName('prod')[i].getAttribute('stock');
		var marque=xml.getElementsByTagName('prod')[i].getAttribute('marque');
		var ref=xml.getElementsByTagName('prod')[i].getAttribute('ref');
		var lot=xml.getElementsByTagName('prod')[i].getAttribute('lot');
		var ventes=xml.getElementsByTagName('prod')[i].getAttribute('ventes');
		var ventes1an=xml.getElementsByTagName('prod')[i].getAttribute('ventes1an');
		var stock='Pas en stock';
		if(xml.getElementsByTagName('prod')[i].getAttribute('stock')=='1') stock='En stock : '+xml.getElementsByTagName('prod')[i].getAttribute('stockqte');
		if(actif<2) html+='<tr id="tr_'+artid+'"><td><img src="/configurations/images/produits/br/'+artid+'.jpg" width="80"></td><td style="border-bottom:solid thin #cccccc;">'+marque+'<br>'+id+'<br>'+ref+'<br>'+vistxt[actif]+'</td><td style="border-bottom:solid thin #cccccc;"><b>'+description+'</b><br>Ventes : '+ventes+'<br>Ventes 1 an : '+ventes1an+'<br>'+stock+'</td><td style="border-bottom:solid thin #cccccc;"><a href="javascript:selectprod('+artid+')">Sélectionner</a></td></tr>';
	  }
	  html+='</table>';
      }else
	  html='Pas de résultat';

      document.getElementById('results').innerHTML=html;
}


function selectprod(artid){
    document.getElementsByName('remp_artid')[0].value=artid;
    clique('visible','visible');
}

function rmremp(id){
    document.getElementsByName('rempos')[0].value=id;
    document.getElementsByName('remp_artid')[0].value=0;
    clique('visible','visible');
}

function selectremp(id){
      for(i=1;i<=3;i++) document.getElementById('remp'+i).style.borderColor='#888888';
      document.getElementById('remp'+id).style.borderColor='red';
      document.getElementsByName('rempos')[0].value=id;
      document.getElementById('modif').style.display='table-cell';
}


function srcredirect(src){
      if(src.length<=3) return;
      var url="marketplace/src.php?txt="+src
      ajaxrequest(url,srcredirectCallback);
      document.getElementById('results2').innerHTML='<img src="/configurations/images/templates/ajax_wait.gif">';
}

function srcredirectCallback(httprequest){
      var xml=httprequest.responseXML.documentElement;
      var html='';

      var vistxt=new Array();
      vistxt[0]='<br><span style="color:red;">Invisible</span>';
      vistxt[1]='<br>Visible';
      if(xml.getElementsByTagName('prod')[0]){
	  html+='<table width="100%" style="border:solid thin #808080;"><tr>';
	  for(i=0;i<xml.getElementsByTagName('prod').length;i++){
		var description=xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
		var id=xml.getElementsByTagName('prod')[i].getAttribute('id');
		var artid=xml.getElementsByTagName('prod')[i].getAttribute('artid');
		var actif=xml.getElementsByTagName('prod')[i].getAttribute('active');
		var stock=xml.getElementsByTagName('prod')[i].getAttribute('stock');
		var marque=xml.getElementsByTagName('prod')[i].getAttribute('marque');
		var ref=xml.getElementsByTagName('prod')[i].getAttribute('ref');
		var lot=xml.getElementsByTagName('prod')[i].getAttribute('lot');
		var ventes=xml.getElementsByTagName('prod')[i].getAttribute('ventes');
		var ventes1an=xml.getElementsByTagName('prod')[i].getAttribute('ventes1an');
		var stock='Pas en stock';
		if(xml.getElementsByTagName('prod')[i].getAttribute('stock')=='1') stock='En stock : '+xml.getElementsByTagName('prod')[i].getAttribute('stockqte');
		if(actif<2) html+='<tr id="tr_'+artid+'"><td><img src="/configurations/images/produits/br/'+artid+'.jpg" width="80"></td><td style="border-bottom:solid thin #cccccc;">'+marque+'<br>'+id+'<br>'+ref+'<br>'+vistxt[actif]+'</td><td style="border-bottom:solid thin #cccccc;"><b>'+description+'</b><br>Ventes : '+ventes+'<br>Ventes 1 an : '+ventes1an+'<br>'+stock+'</td><td style="border-bottom:solid thin #cccccc;"><a href="javascript:selectredirect('+artid+')">Sélectionner</a></td></tr>';
	  }
	  html+='</table>';
      }else
	  html='Pas de résultat';

      document.getElementById('results2').innerHTML=html;
}

function selectredirect(artid){
      document.getElementsByName('redirect')[0].value=artid;
      clique('visible','visible');
}

var fb=undefined;
function createfb(){
    fb=new filebox('fb');
    fb.dataurl='article_files.php?artid=<?php print $artid ?>&action=files&parent=0';
}

createfb();

function filtretable() {
	document.formu.action2.value = 'stocks';
	document.formu.old.value = 'stocks';
	document.formu.submit();
}
function filtreempty() {
	document.formu.action2.value = 'stocks';
	document.formu.old.value = 'stocks';
	toempty = document.formu.elements;
	for(i = 0 ; i < toempty.length ; i++)
		if(toempty[i].type == "select-one")
			toempty[i].selectedIndex = 0;
	document.formu.submit();
}
function tritable(order,sens) {
	document.formu.action2.value = 'stocks';
	document.formu.old.value = 'stocks';
	document.formu.orderby.value = order;
	document.formu.sens.value = sens;
	document.formu.submit() ;
}

function clique(action_envoyee,ancienne_action)
	{
	document.formu.action2.value=action_envoyee;
	document.formu.old.value=ancienne_action;
	document.formu.submit() ;
	}
function changelangue(nouvelle,navig)
	{
	document.formu.action2.value = 'desc' ;
	document.formu.old.value = 'desc' ;
	document.formu.autrelang.value = nouvelle ;
	document.formu.submit () ;
	}
function terminer (quitte,navig)
	{
	document.formu.action2.value = quitte ;
	document.formu.old.value = quitte ;
	document.formu.quitter.value = '1' ;
	document.formu.submit () ;
	}

function itemaction(tab,action,id,confirm_text)
	{
	if (confirm_text)
	  if (!confirm(confirm_text))
	    return false;
	document.formu.item_action.value = action ;
	document.formu.item_id.value = id ;
	document.formu.old.value = tab;
	document.formu.action2.value=tab;
	if (!document.formu.action2.value)
	  document.formu.action2.value = tab;
	document.formu.action += '#iaction';
	document.formu.submit () ;
	}
</script>
