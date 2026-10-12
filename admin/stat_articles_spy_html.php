<?php

include "./header.php";
include "./menu.php";

$trsites="<td></td>";
foreach($spysitelist as $spysite)
    $trsites.="<td>".ucwords($spysite)."</td>";
    
    
$i=0;
$now=time();
$lefttable=$righttable="";

if($showtable){
    foreach($mainlist as $code=>$value){
	  $artid=$artids[$code];
	  $prix=$listeprix[$artid];

	  $instock=$stock->in_stock($artid);
	  $qtestock=intval($instock->qte);
	  
	  $reqlot=$DB_site2->query("SELECT a.artcode,al.qte FROM article_lot al INNER JOIN article a USING(artid) WHERE al.lotid=".$artids[$code]);
	  $lotartnb=$DB_site2->num_rows($reqlot);
	  if($lotartnb==0)
	      $spycodes=array($code=>1);
	  else{
	      $spycodes=array();
	      while($al=$DB_site2->fetch_array($reqlot)) $spycodes[$al['artcode']]=$al['qte'];
	  }
	  
	  $trspylist="<td class=\"div\">";
	  if($lotartnb>0){
	      foreach($spycodes as $spycode=>$qte)
		  $trspylist.="<div>".$spycode."</div>";
	      $trspylist.="<div class=\"total\">".$code."</div>";
	  }
	  $trspylist.="</td>";
	  foreach($spysitelist as $site){
	      $content=$red="";
	      $totalspy=0;
	      $qtespy=0;
	      $spyaction="";
	      foreach($spycodes as $spycode=>$qte){
			  if(isset($spylist[$spycode][$site])){
				  $qtespy++;
				  $pc=$pc2="";
				  if($prix['prix']>0 && $lotartnb==0) $pc=round(-100*($prix['prix']-$spylist[$spycode][$site])/$prix['prix']);
				  if($prix['prixconseille']>0 && $lotartnb==0) $pc2=round(-100*($prix['prixconseille']-$spylist[$spycode][$site])/$prix['prixconseille']);
				  
				  $red=$pc<0?" red":"";
				  if($pc!=="") $pc="<br>".($pc>0?"+":"").$pc."%";
				  if($pc2!="") $pc.="<br>(".($pc2>0?"+":"").$pc2."%)";
				  $totalspy+=$spylist[$spycode][$site]*$qte;
				  $content.="<div class=\"nb$red\">".number_format($spylist[$spycode][$site],2,',','')."&euro;".$pc."</div>";
			  }else
				  $content.="<div></div>";
	      }
	      if($qtespy==$lotartnb && $lotartnb>0){
			  $pc="";
			  if($prix['prix']>0)
				  $pc="<br>".round(-100*($prix['prix']-$totalspy)/$prix['prix'],1)."%";
			  $red=$pc<0?" red":"";
			  $content.="<div class=\"total nb$red\">".number_format($totalspy,2,',','')."&euro;".$pc."</div>";
			  
			  if($totalspy>0 && (!isset($spymini[$code]) || $totalspy<$spymini[$code])){
				  $spymini[$code]=$totalspy;
				  $spyminisite[$code]=$site;
			  }
	      }else{
			  // print_r($spydata[$code][$site]);
			  if($lotartnb==0)
				  $spyaction=" onclick=\"spyurl(this,'".$code."');\"";
			  if(isset($spydata[$code][$site])){
				  $error=$spydata[$code][$site][5];
				  if($error!="")
					$content="Erreur !!!";
				  elseif(!isset($spylist[$code][$site]))
					$content="En attente";
				  // echo "pour ".$code." - ".$site." : ".$spydata[$code][$site][2]." ||| ";
				  $spyaction=" onclick=\"spyurl(this,'".$code."','".$spydata[$code][$site][6]."','".$spydata[$code][$site][2]."');\"";
			  } else {
				  //echo "on n'a pas ".$site." dans ";
				 // print_r($spydata[$code]);
			  }
	      }
	      $trspylist.="<td".($lotartnb>0?" class=\"div\"":"").$spyaction.">".$content."</td>";
	      
	  }
	  
	  if(isset($spymini[$code])){
	      $spyht=$spymini[$code]/(1+$prix['tva']/100);
	      $margespy=$spyht-$prix['totalachat'];
	      $pcmargespy=100*$margespy/$spyht;
	  }
	  
	  if($prix['prix']>0)
	      $pcspymini=round(-100*($prix['prix']-$spymini[$code])/$prix['prix']);
	  else
	      $pcspymini="";
	      
	  $i++;
	  $lefttable.="<tr>
	    <td>".$img[$code]."</td>
	    <td><a name=\"".$code."\"></a><a href=\"ajout_produit.php?artid=".$artids[$code]."&prec=modif\" target=\"_blank\">".$code."</a><br><i>".$pfab[$code]."</i><br>".$visibletxt[$pactive[$code]]."<br>".$pmarq[$code]."</td>
	  </tr>";
	  
	  $righttable.="<tr>";
	  
	  if($typesrc!=3){
	    $righttable.="<td class=\"nb\">".$pvus[$code]."</td>
	    <td class=\"nb\">".$plist[$code]."<br><span class=\"grey\">mp: ".intval($pvente[$code]['mp']['qte'])."<br>sav: ".intval($pvente[$code]['sav']['qte'])."</span></td>
	    <td class=\"nb\">".number_format($pcaht[$code],2,',','')."&euro;<span class=\"grey\"><br>mp: ".number_format($pvente[$code]['mp']['caht'],2,',','')."&euro;<br>sav: ".number_format($pvente[$code]['sav']['caht'],2,',','')."&euro;</span></td>";
	  }else{
	    foreach($tsperiode as $periode=>$ts)
		$righttable.="<td class=\"nb\">".intval($vus_periode[$artid][$periode])."</td>";
		
	    foreach($tsperiode as $periode=>$ts)
		$righttable.="<td class=\"nb\">".intval($ventes_periode[$artid][$periode]['']['qte'])."<br><span class=\"grey\">mp:&nbsp;".intval($ventes_periode[$artid][$periode]['mp']['qte'])."<br>sav:&nbsp;".intval($ventes_periode[$artid][$periode]['mp']['qte'])."</span></td>";
	  }
	    
	  $righttable.="
	    <td class=\"sep\"></td>";
	  
	  if($typesrc!=3){
		  /*
	    $righttable.="<td class=\"nb\">".number_format($prix['paht'],2,',','')."&euro;</td>
	    <td class=\"nb\">".number_format($prix['totalachat'],2,',','')."&euro;</td>
	    <td class=\"nb\">".number_format($prix['prixconseille'],2,',','')."&euro;</td>";
		*/
	  }else{
	    $righttable.="<td class=\"nb\">";
	    if(isset($price_history[$artid]))
		$righttable.=number_format($price_history[$artid],2,',','')."&euro;<br>".date("d/m/Y",$price_change[$artid]);
	    $righttable.="</td>";
	  }
	    
	  $righttable.="
	    <td class=\"nb\">".number_format($prix['prix'],2,',','')."&euro;<br>".$prix['pcremise']."%</td>
	    <td class=\"center\">".$qtestock."</td>
	    <td class=\"nb\">".round($prix['pcmarge'])."%<br>".number_format($prix['marge'],2,',','')."&euro;</td>
	    <td class=\"sep\"></td>
	    <td class=\"nb".($pcspymini<0?" red":"")."\">".($spymini[$code]!=""?number_format($spymini[$code],2,',','')."&euro;<br>".$pcspymini."%":"")."</td>";
	  
	  if($typesrc!=3)
	    $righttable.="<td class=\"center\">".count($spylist[$code])."</td>";

	  $righttable.="
	    <td>".$spyminisite[$code]."</td>
	    <td class=\"nb\">".(isset($spymini[$code])?round($pcmargespy)."%<br>".number_format($margespy,2,',','')."&euro;":"")."</td>
	    <td class=\"nb\"><input type=\"text\" class=\"proposition\" onkeyup=\"calcmarge($artid,this.value,".$prix['totalachat'].",".$prix['tva'].", ".$prix['prixport'].");\"><br><span id=\"prop_$artid\"></td>
	    <td class=\"sep\"></td>
	    ".$trspylist."
	  </tr>";
    }
    
    $export_link="<a href=\"javascript:export_table();\" class=\"export\"><img src=\"images/icon/xls.png\"> Exporter</a>";
}

echo <<<HTML
  <h1>Etude concurrentielle site</h1>
  <div class="commande_detail">
  <form name="filter" method="post" style="margin:10px;">
    <input type="hidden" name="order" value="">
    <input type="hidden" name="export" value="0">
    <p><b>Type de recherche :</b> <input type="radio" name="typesrc" value="1" onclick="changetype(1)" $chktypesrc1>&nbsp;Les plus vus <input type="radio" name="typesrc" value="2" onclick="changetype(2)" $chktypesrc2>&nbsp;Les vendus <input type="radio" name="typesrc" value="0" onclick="changetype(0)" $chktypesrc0>&nbsp;Spy <input type="radio" name="typesrc" value="3" onclick="changetype(3)" $chktypesrc3>&nbsp;Réunion</p> 
    <!--<p><b>Commandes :</b> <input type="checkbox" name="valide[]" value="1,7" $chkvalid> validées <input type="checkbox" name="valide[]" value="5,8" $chkfacture> facturées</p>-->
    <p class="column"><b>Marque :</b> <select name="marque"><option value="">Toutes</option>$optmarques</select></p>
    <p class="column"><b>Type d'article :</b> <select name="typearticle"><option value="">Tous</option>$opttypearticle</select></p>
    <p class="column"><b>Site concurrent :</b> <select name="site"><option value="">Tous</option>$optsites</select></p>
	<div class="clear"></div>
	<input type="checkbox" name="climadiff_only" value="1" $climadiff_only_checked> Groupe Frio seulement (écrase tous les critères pécédents)
    <div class="clear"></div>
    <p id="choixdate" style="$displaydate"><b>A partir du</b> <input type="text" name="from" id="from" size="10" value="$from" onclick="calendrier_show('from',this.value);" readonly> <b>jusqu'au</b> <input type="text" name="to" id="to" size="10" value="$to" onclick="calendrier_show('to',this.value);" readonly></p>
	
	
    <input type="submit" class="skinned" value="Envoyer"> $export_link
  </form>
  </div>
  <div class="clear"></div>
HTML;


$tdvar="<td><a href=\"javascript:order('vus')\">Nb vus</a>$vussort</td>
<td><a href=\"javascript:order('qte')\">Nb vendus</a>$qtesort</td>
<td><a href=\"javascript:order('ca')\">CA HT</a>$casort</td>";

$tdvar2="";
/*
$tdvar2="<td>Prix achat HT</td>
	<td>Prix achat HT total</td>
	<td>PPI TTC</td>";
*/

$tdvar3="<td>Nb concur.</td>";

if($typesrc==3){
    $tdvar="";
    $tdvar3="";
    foreach($periodes as $periode=>$ptxt)
	$tdvar.="<td><a href=\"javascript:order('vus$periode')\">Nb vus $ptxt</a>".${"vus".$periode."sort"}."</td>";

    foreach($periodes as $periode=>$ptxt)
	$tdvar.="<td><a href=\"javascript:order('qte$periode')\">Nb vendus $ptxt</a>".${"qte".$periode."sort"}."</td>";
	
    $tdvar2="<td>Ancien prix</td>";
}
	
if($showtable){
    echo <<<HTML
    <div id="theaddiv">
		<table id="theadleft" class="ca">
    	<thead>
    		<tr>
				<td></td>
				<td><a href="javascript:order('artcode')">Code</a>$codesort<br><a href="javascript:order('marque')">Marque</a>$marquesort</td>
    		</tr>
   		</thead>
	  	</table>
  		<div id="theadrightdiv">
      		<table id="theadright" class="ca">
		  	<thead>
		  		<tr>
			      	$tdvar
			      	<td class="sep"></td>
			      	$tdvar2
			      	<td>PV MCAV TTC</td>
			      	<td>Stock</td>
			      	<td>Marge HT MCAV</td>
			      	<td class="sep"></td>
			      	<td><a href="javascript:order('diffspy')">Prix le plus bas</a>$spyminsort</td>
			      	$tdvar3
			      	<td>Site mini</td>
			      	<td>Marge si prix + bas</td>
			      	<td>Proposition mcav</td>
			      	<td class="sep"></td>
			      	$trsites
			  	</tr>
		  	</thead>
	      	</table>
	  	</div>
  	</div>
      
  	<div id="tbodyleftdiv">
  		<table id="tbodyleft" class="ca">
      		$lefttable
	  	</table>
  	</div>
      
  	<div id="tbodyrightdiv" onscroll="scrolldiv()">
  		<table id="tbodyright" class="ca">
     		$righttable
	  	</table>
  	</div>
HTML;
}else
    echo "<p>Vous devez sélectionner au moins une marque ou un type d'article</p>";
    
    
echo <<<HTML
<LINK href="cal.css" type="text/css" rel="stylesheet">
<script language="javascript" src="calendrier.js"></script>
<script language="javascript">
var lastsender;
var spymodif=new Array();
var current_order="$order";

function order(field){
    document.getElementsByName('order')[0].value=field;
    document.forms.filter.submit();
}

function spyurl(sender,artcode,id,url){
    var spyurldiv=document.getElementById('spyurldiv');
    if(spyurldiv) spyurldiv.parentNode.removeChild(spyurldiv);
    
    spyurldiv=document.createElement('div');
    spyurldiv.id='spyurldiv';
    var input=document.createElement('input');
    input.type="text";
    input.id="spyurl";
    input.onclick=function(){ this.select(); }
    if(url!=undefined) input.value=url;
    spyurldiv.appendChild(input);
    var sub=document.createElement('input');
    sub.type="button";
    sub.value=(id==undefined?"Ajouter":"Modifier");
    sub.onclick=updspyurl;
    var cancel=document.createElement('input');
    cancel.type="button";
    cancel.value="Annuler";
    cancel.onclick=function(){ spyurldiv.parentNode.removeChild(spyurldiv); }
    var pv=document.createElement('input');
    pv.type="button";
    pv.value="Voir";
    pv.onclick=function(){ newwindow=window.open("redirect.php?url="+document.getElementById('spyurl').value,'name','height=900,width=900'); }
    spyurldiv.appendChild(sub);
    spyurldiv.appendChild(pv);
    if(url!=undefined){
	var delbtn=document.createElement('input');
	delbtn.type="button";
	delbtn.value="Supprimer";
	delbtn.style.color="red";
	delbtn.onclick=function(){ if(confirm("Etes-vous sur de vouloir effacer cette adresse ?")) delspyurl(); }
	spyurldiv.appendChild(delbtn);
    }
    spyurldiv.appendChild(cancel);
    document.body.appendChild(spyurldiv);
    
    spymodif['id']=(id!=undefined ? id : '');
    spymodif['artcode']=artcode;
    
    if(lastsender) lastsender.style.backgroundColor='';
    lastsender=sender;
    
    var left=0;
    var top=sender.offsetHeight-1;
    for(obj=sender; obj.nodeName!=null; obj=obj.parentNode){
	if(obj.nodeName=="TD" || obj.nodeName=="TABLE"){
	    top+=obj.offsetTop;
	    left+=obj.offsetLeft;
	}
	if(obj.nodeName=="TABLE") break;
    }
    left-=document.getElementById('tbodyrightdiv').scrollLeft;
    top-=document.getElementById('tbodyrightdiv').scrollTop;
    spyurldiv.style.left=left+'px';
    spyurldiv.style.top=top+'px';
    sender.style.backgroundColor='#88FF88';
}

function updspyurl(){
    var post=spymodif;
    post['action']="add";
    post['url']=document.getElementById('spyurl').value;
    ajaxrequest("spy_ajax.php",upd_cb,post);
}

function upd_cb(httprequest){
    var spyurldiv=document.getElementById('spyurldiv');
    spyurldiv.innerHTML="";
    var p=document.createElement('p');
    p.innerHTML=httprequest.responseText;
    spyurldiv.appendChild(p);
    lastsender.innerHTML="En attente";
}

function delspyurl(){
    var post=spymodif;
    post['action']="del";
    ajaxrequest("spy_ajax.php",del_cb,post);
}

function del_cb(httprequest){
    var spyurldiv=document.getElementById('spyurldiv');
    spyurldiv.innerHTML="";
    var p=document.createElement('p');
    p.innerHTML=httprequest.responseText;
    spyurldiv.appendChild(p);
    lastsender.innerHTML="";
}

function colwidth(){
    var td=document.getElementById('tbodyleft').getElementsByTagName('tr')[0].getElementsByTagName('td');
    for(i=0;i<td.length;i++){
	var col=document.getElementById('theadleft').getElementsByTagName('td')[i];
	var w=Math.max(col.offsetWidth,td[i].offsetWidth);
	col.style.width=w+"px";
	td[i].style.width=w+"px";
    }
    
    var td=document.getElementById('tbodyright').getElementsByTagName('tr')[0].getElementsByTagName('td');
    for(i=0;i<td.length;i++){
	var col=document.getElementById('theadright').getElementsByTagName('td')[i];
	var w=Math.max(col.offsetWidth,td[i].offsetWidth);
	col.style.width=w+"px";
	td[i].style.width=w+"px";
    }
    document.getElementById('tbodyright').style.width=document.getElementById('tbodyright').offsetWidth+'px';
    document.getElementById('theadright').style.width=document.getElementById('tbodyright').offsetWidth+'px';
    document.getElementById('theadleft').style.height=document.getElementById('theadright').offsetHeight+'px';
    document.getElementById('tbodyrightdiv').style.height=(window.innerHeight-document.getElementById('tbodyrightdiv').offsetTop)+'px';
    document.getElementById('tbodyleftdiv').style.height=document.getElementById('tbodyrightdiv').style.height;
    document.getElementById('tbodyleftdiv').style.width=document.getElementById('theadleft').offsetWidth;
    
    var tbodyleft=document.getElementById('tbodyleft');
    var tr=document.getElementById('tbodyright').getElementsByTagName('tr');
    for(i=0;i<tr.length;i++){
	var leftheight=tbodyleft.getElementsByTagName('tr')[i].offsetHeight;
	var h=Math.max(leftheight+1,tr[i].offsetHeight);
	tbodyleft.getElementsByTagName('tr')[i].style.height=h+'px';
	tr[i].style.height=h+'px';
    }
}

function scrolldiv(){
    var scrollleft=document.getElementById('tbodyrightdiv').scrollLeft;
    document.getElementById('theadright').style.marginLeft=(-scrollleft)+'px';
    
    document.getElementById('tbodyleft').style.marginTop=(-document.getElementById('tbodyrightdiv').scrollTop)+'px';
}

function changetype(type){
    switch(type){
	case 0:
	case 1:
	case 2:
	document.getElementById('choixdate').style.display="block";
	break;
	
	case 3:
	document.getElementById('choixdate').style.display="none";
	break;
    }
}

function calcmarge(id,prop,totalachat,tva,port){
    var propht=parseFloat(prop)/(1+tva/100)+port;
    var marge=Math.round(100*(propht-totalachat))/100;
    var margepc=Math.round(100*marge/propht);
    document.getElementById('prop_'+id).innerHTML=(!isNaN(marge) ? margepc+"%<br>"+marge+"&euro;" : "");
}

function export_table(){
    document.getElementsByName("export")[0].value=1;
    document.getElementsByName('order')[0].value=current_order;
    document.forms.filter.submit();
    document.getElementsByName("export")[0].value=0;
    document.getElementsByName('order')[0].value="";
}

window.addEventListener( "load", colwidth, false );
</script>
HTML;

echo <<<CSS
<style>
.column { float:left; margin:5px 10px 5px 0; }
.ca { border-collapse:collapse; table-layout:fixed; }
.ca tbody tr:nth-child(2n+1) { background:#DDD; }
.ca td { border:solid 1px black; color:black; min-width:10px; padding:2px; }
.ca td.div { vertical-align:top; }
.ca td.div div { min-height:20px; border-bottom:solid 1px #666; color:#666; }
.ca td.div div.total { border:none; color:black; }
.ca thead { text-align:center; font-weight:bold; background-color:#ccc; }
.ca .nb { text-align:right; }
.ca .nb .grey { color:#888; }
.ca .nb.red { color:red; }
.ca .center { text-align:center; }
.ca .sep { width:5px; background:yellow; padding:0; }
.proposition { width:60px; }
.export { display:block; float:right; vertical-align:middle; text-decoration:none!important; }
.export img { width:20px; vertical-align:middle; }
#spyurldiv { position:absolute; background:#88FF88; border-style:none solid solid none; border-width:1px; border-color:black; padding:4px; text-align:center; }
#spyurldiv input[type="text"] { display:block; width:400px; }
#spyurldiv input[type="button"] { margin:4px; }

.main { width:100%; }
.mainpage { width:calc(100% - 200px); }
#theaddiv { overflow-x:hidden; overflow-y:hidden; }
#theadrightdiv { float:left; width:calc(100% - 300px); overflow:hidden; }
#theadleft { float:left; }
#tbodyleftdiv { width:200px; height:700px; float:left; overflow:hidden; }
#tbodyrightdiv { float:left; width:calc(100% - 300px); height:700px; overflow:auto; }
</style>
CSS;

include "./footer.php";

?>