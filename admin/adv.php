<?php

$datelivrtxt=($rs_facture['datelivraison']>0?date("d/m/Y",$rs_facture['datelivraison']):"?");

print "<style>
.tab_on a { background-color:white; border:solid thin black; text-align:center; text-decoration:none; padding:10px; }
.tab_off { padding-top:10px; }
.tab_off:hover { padding-top:0px; }
.tab_off a { background-color:#e4e4e4; border:solid thin #cccccc; text-align:center; text-decoration:none; padding:10px; }
.advgroup { background-color:#f4f4f4; margin-bottom:10px; font-size:12px; text-align:left; }
.advgroup p { margin:6px; color:white; font-weight:bold; font-size:14px; background-color:AA0000; }
.advgroup table { font-size:12px; border-collapse:collapse; width:100%; }
.advgroup td { border-top:solid thin #808080; padding-top:5px; padding-bottom:5px; }
#stepfourn2,#stepfourn3,#stepfourn4,#stepfourn5,#stepfourn6,#stepfourn7 { display:none; }
.caracts td { height:20px; border:none; }
.roinput { border:solid thin black; background:none; }
#fournbtn { height:20px; background:white; text-align:center; }
#steptrspt2,#steptrspt3,#steptrspt4,#steptrspt5,#steptrspt6,#steptrspt6,#steptrspt7,#steptrspt8,#steptrspt9,#steptrspt10,#steptrspt11 { display:none; }
#stepsls2,#stepsls3,#stepsls4,#stepsls5,#stepsls6,#stepsls6,#stepsls7,#stepsls8,#stepsls9,#stepsls10,#stepsls11 { display:none; }

.tnt { color:#FFAA00; }
.mory { color:#00AA00; }
.mazet { color:#0000FF; }
</style>";


print "<script language=\"javascript\">

var dep=\"".substr($rs_facture['codepostal'],0,2)."\";
var ville=\"".strtolower($rs_facture['ville'])."\";
var pays=\"".$rs_facture['paysid']."\";
var depsaison=new Array('06','11','13','20','30','34','66','83','84','98');
var poids;

pctrsptassurance=0.6;
valtrsptassurance=0;

function tab(nb){
         for(i=1;i<=4;i++){
             document.getElementById('tab'+i).style.visibility='hidden';
             document.getElementById('tab'+i).style.position='absolute';
             document.getElementById('tablink'+i).className='tab_off';
         }

         document.getElementById('tab'+nb).style.position='relative';
         document.getElementById('tab'+nb).style.visibility='visible';
         document.getElementById('tablink'+nb).className='tab_on';
}

function valid1(showpreview){
       error=\"\";
       fourn=false;
       for(i=0;i<document.getElementsByName('fournisseur').length;i++){
            if(document.getElementsByName('fournisseur')[i].checked) fourn=document.getElementsByName('fournisseur')[i].value;
       }
       if(fourn===false) error=\"Il faut s�lectionner un fournisseur\";

       prod=false;
       for(i=0;i<document.getElementsByName('produits[]').length;i++){
            if(document.getElementsByName('produits[]')[i].checked) prod=true;
       }
       if(prod==false) error=\"Aucun produit n'est s�lectionn�\";

       trspt=false;
       for(i=0;i<document.getElementsByName('trsptmode').length;i++){
            if(document.getElementsByName('trsptmode')[i].checked) trspt=true;
       }
	if(fourn=='0') trspt=true;  
	if(document.getElementsByName('typecmd')[1].checked) trspt=true;
       if(trspt==false) error=\"Il faut s�lectionner un mode de transport\";

       if(error==\"\"){
           if(showpreview==true){
               preview(form1);
           }else{
               document.forms.form1.action='facture.php?facturid=$facturid&action=editer';
		document.forms.form1.target = \"_self\";
               document.forms.form1.submit();
           }
       }else
           alert(error);
}

function sumprod(){
         total=0;
         ecopart=0;
         gartotal=0;

         for(i=0;i<document.getElementsByName('produits[]').length;i++){

              if(document.getElementsByName('produits[]')[i].checked){
                  pid=document.getElementsByName('produits[]')[i].value;
                  total+=parseFloat(document.getElementsByName('qte_'+pid)[0].value*document.getElementsByName('price1_'+pid)[0].value);
                  //if(document.getElementsByName('gar_'+pid)[0].checked) gartotal+=parseFloat(document.getElementsByName('qte_'+pid)[0].value*document.getElementsByName('garval_'+pid)[0].value);

		  for(i2=0;i2<document.getElementsByName('caract_'+pid+'[]').length;i2++){
		      caractvalid=document.getElementsByName('caract_'+pid+'[]')[i2].value;
		      if(document.getElementsByName('caract_'+pid+'[]')[i2].checked) total+=parseFloat(document.getElementsByName('caractval['+caractvalid+']')[0].value);
		  }
		  ecopart+=parseFloat(document.getElementsByName('ecopart_'+pid)[0].value);
              }
         }

         fourn=0;
         if(document.getElementsByName('trsptmode')[0].checked) fourn=0;
         if(document.getElementsByName('trsptmode')[1].checked) fourn=parseFloat(document.getElementsByName('mazetcout')[0].value);       
	 if(isNaN(fourn)) fourn=0;

         document.getElementById('prod1total').innerHTML=total+' �';
         document.getElementById('prixprodtotal').innerHTML=total+' �';
         document.getElementById('prixecoparttotal').innerHTML=ecopart+' �';
         document.getElementById('prixtrspttotal').innerHTML=fourn+' �';
         document.getElementById('prixgartotal').innerHTML=gartotal+' �';
         document.getElementById('prixfourntotal').innerHTML=Math.round(100*(total+ecopart+fourn+gartotal))/100+' �';

	 document.getElementsByName('blslspj')[0].checked=false;
	 if(document.getElementsByName('trsptmode')[0].checked){
	    document.getElementsByName('blslspj')[0].checked=document.getElementById('chkenlev2sls').checked;
	 }
	 if(document.getElementsByName('trsptmode')[1].checked){
	    document.getElementsByName('blslspj')[0].checked=(document.getElementsByName('trspt')[2].checked || document.getElementsByName('trspt')[3].checked);
	 }
	 document.getElementsByName('blpj')[0].checked=!document.getElementsByName('blslspj')[0].checked;
}

function selectfournlivr(type){
	 document.getElementById('parenlevement').style.display='none';
	 document.getElementById('parvossoins').style.display='none';
	 document.getElementById(type).style.display='block';
	 document.getElementById('tdparenlevement').style.display='none';
	 document.getElementById('tdparvossoins').style.display='none';
	 document.getElementById('td'+type).style.display='table-cell';
}

function trspttarif(){
      total=0;

      if(document.getElementsByName('trsptcout')[0].checked) total+=parseFloat(document.getElementsByName('trsptcoutval')[0].value);

      var optnb=document.getElementsByName('priceoptnb')[0].value;
      for(i=0;i<optnb;i++) if(document.getElementsByName('chkopt['+i+']')[0].checked) total+=parseFloat(document.getElementsByName('priceopt['+i+']')[0].value);

      document.getElementById('totaltrspt').innerHTML=total+' �';

}
function totalassurance(){
         totalarticles=0;
	 totalpv=0;
         poidstotal=0;
	 var volumetotal=0;
         for(i=0;i<document.getElementsByName('produits2[]').length;i++){
             if(document.getElementsByName('produits2[]')[i].checked){
                 ligneid=document.getElementsByName('produits2[]')[i].value;
                 totalarticles+=document.getElementsByName('qte2_'+ligneid)[0].value*(parseFloat(document.getElementsByName('price_'+ligneid)[0].value)+parseFloat(document.getElementsByName('ecopart2_'+ligneid)[0].value));
		 totalpv+=document.getElementsByName('qte2_'+ligneid)[0].value*parseFloat(document.getElementsByName('pv_'+ligneid)[0].value);
                 poidstotal+=document.getElementsByName('qte2_'+ligneid)[0].value*parseFloat(document.getElementsByName('poids_'+ligneid)[0].value);
		 volumetotal+=document.getElementsByName('qte2_'+ligneid)[0].value*parseFloat(document.getElementsByName('volume_'+ligneid)[0].value);
             }
         }

         document.getElementById('poidstotal').innerHTML=Math.round(poidstotal);
	 document.getElementById('valeurht').innerHTML=Math.round(totalarticles*100)/100+'&euro;';
	 document.getElementsByName('valeurdeclaree')[0].value=Math.round(totalarticles*100)/100;
	 document.getElementById('coutpoids').innerHTML=poidstotal+'kg';
	 document.getElementById('volumetotal').innerHTML=Math.round(volumetotal*100)/100+'m<sup>3</sup>';
	 document.getElementById('poidsvolume100').innerHTML=Math.round(volumetotal*10000)/100;
	 document.getElementById('poidsvolume250').innerHTML=Math.round(volumetotal*25000)/100;
	 document.getElementById('poidsvolume300').innerHTML=Math.round(volumetotal*30000)/100;

}

function valid2(showpreview){
	prod=false;
	error=\"\";
        for(i=0;i<document.getElementsByName('produits2[]').length;i++){
             if(document.getElementsByName('produits2[]')[i].checked) prod=true;
        }
        if(prod==false) error=\"Il faut s�lectionner un produit\";

        if(error==\"\"){
	  if(showpreview==true){
                preview(form2);
         }else{
                document.forms.form2.action='facture.php?facturid=$facturid&action=editer';
		document.forms.form2.target = \"_self\";
                document.forms.form2.submit();
         }
	}else
	    alert(error);
}

function totalsls(){
         total=0;
         if(document.getElementsByName('slscouttrspt')[0].checked) total+=parseFloat(document.getElementsByName('slscouttrsptval')[0].value);
         if(document.getElementsByName('slscoutcorse')[0].checked) total+=parseFloat(document.getElementsByName('slscorsetotal')[0].value);
         if(document.getElementsByName('slscoutreprise')[0].checked) total+=parseFloat(document.getElementsByName('slscoutrepriseval')[0].value);
         if(document.getElementsByName('slscoutassur')[0].checked) total+=parseFloat(document.getElementsByName('slscoutassurval')[0].value);
         if(document.getElementsByName('slscoutother')[0].checked) total+=parseFloat(document.getElementsByName('slscoutotherval')[0].value);
         document.getElementById('totalsls').innerHTML=Math.round(total*100)/100+' �';
}
function assurancesls(){
         totalarticles=0;
         poids=0;
         for(i=0;i<document.getElementsByName('produits3[]').length;i++){
             if(document.getElementsByName('produits3[]')[i].checked){
                 ligneid=document.getElementsByName('produits3[]')[i].value;
                 totalarticles+=document.getElementsByName('qte3_'+ligneid)[0].value*(parseFloat(document.getElementsByName('price3_'+ligneid)[0].value)+parseFloat(document.getElementsByName('ecopart3_'+ligneid)[0].value));
                 poids+=document.getElementsByName('qte3_'+ligneid)[0].value*parseFloat(document.getElementsByName('poids3_'+ligneid)[0].value);
             }
         }

         document.getElementsByName('slscoutassurval')[0].value=Math.round(totalarticles*0.35)/100;
         document.getElementById('poidstotal3').innerHTML=poids;
	 document.getElementById('coutpoidssls').innerHTML='Poids '+poids+'kg';
	 valeurrembourse=poids*33;
	 if(valeurrembourse>1000) valeurrembourse=1000;
	 document.getElementById('valeurremboursesls').value=Math.round(valeurrembourse)+' �';
	 document.getElementById('valeurtotalsls').value=totalarticles+' �';
	 document.getElementById('valeurremboursesls').style.color='black';
	 if(valeurrembourse<totalarticles) document.getElementById('valeurremboursesls').style.color='red';
         document.getElementsByName('slscorsetotal')[0].value=Math.round(poids*document.getElementById('prixkgcorse').value*100)/100;
	 document.getElementsByName('slscouttrsptval')[0].value=prixsls();
}

function valid3(showpreview){
        error=\"\";

        livr=false;
        for(i=0;i<document.getElementsByName('livrsls').length;i++){
             if(document.getElementsByName('livrsls')[i].checked) livr=true;
        }
        if(livr==false) error=\"Il faut s�lectionner une agence\";

        if(error==\"\"){
            if(showpreview==true){
                preview(form3);
            }else{
                document.forms.form3.action='facture.php?facturid=$facturid&action=editer';
		document.forms.form3.target = \"_self\";
                document.forms.form3.submit();
            }
       }else
           alert(error); 
}
function valid4(showpreview){
        error=\"\";

        livr=false;
        if(document.getElementsByName('livrservice')[0].value!='') livr=true;
        
        if(livr==false) error=\"Il faut s�lectionner une agence\";

        if(error==\"\"){
            if(showpreview==true){
                preview(form4);
            }else{
                document.forms.form4.action='facture.php?facturid=$facturid&action=editer';
        document.forms.form4.target = \"_self\";
                document.forms.form4.submit();
            }
       }else
           alert(error); 
}

function preview(formid){
   my_form = eval(formid);
   window.open(\"adv2.php?preview=1\", \"popup\", \"height=640,width=640,menubar='no',location='no',status='no'\");
   my_form.action=\"adv2.php?preview=1&facturid=$facturid\";
   my_form.target = \"popup\";
   my_form.submit();
}

function tarifs(type){
    totalassurance();
    var opts=new trsptprice(type,poidstotal,totalarticles);
    
    document.getElementById('priceopts').innerHTML=opts.generate();
    document.getElementById('totaltrspt').innerHTML='0 �';
    
}

upddatetype='';
efl='';
function updadvdate(){
   document.location='facture.php?action=editer&facturid=$facturid&upddatetype='+upddatetype+'&efl='+efl+'&ts='+cal_timestamp+'#adv';
}
function deladvcmd(id){
  if(confirm('Etes vous sur de vouloir effacer cette commande fournisseur ?')) document.location='facture.php?action=editer&facturid=$facturid&deladvcmd='+id;
}
function updcomptacomment(id){
  var comment=document.getElementById('comment'+id).value;
  document.location='facture.php?action=editer&facturid=$facturid&comment='+comment+'&efid='+id+'#adv';
}
function updadvcomment(type){
    var comment=document.getElementById(type+'advcomment').value;
    comment = comment.replace(/#/g,' ');
    document.location='facture.php?action=editer&facturid=$facturid&advcomment='+comment+'&commenttype='+type+'#adv';
}

function cmdtxt(cmdtype,lid,txt){
  document.location='facture.php?action=editer&facturid=$facturid&lid='+lid+'&type='+cmdtype+'&txt='+txt+'#adv';
}

var gcmdtype='';
var glid=0;
var gtypedate='';
function cmddate(input,cmdtype,lid,typedate){
  gcmdtype=cmdtype;
  glid=lid;
  gtypedate=typedate;
  calendrier_show(document.getElementById(input).id,document.getElementById(input).value,undefined,cmddate2);
}

function cmddate2(){
  document.location='facture.php?action=editer&facturid=$facturid&lid='+glid+'&type='+gcmdtype+'&typedate='+gtypedate+'&ts='+cal_timestamp+'#adv';
}

efid=0;
function updlivrcmd(){
  document.location='facture.php?action=editer&facturid=$facturid&datelivr='+cal_timestamp+'&efid='+efid+'#adv';
}
function updenlevcmd(){
  document.location='facture.php?action=editer&facturid=$facturid&dateenlev='+cal_timestamp+'&efid='+efid+'#adv';
}

Array.prototype.in_array = function(p_val) {
    for(var i = 0, l = this.length; i < l; i++) {
        if(this[i] == p_val) {
            rowid = i;
            return true;
        }
    }
    return false;
}

function getUrlVars(){
  var vars = [], hash;
  var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');

  for(var i = 0; i < hashes.length; i++){
    hash = hashes[i].split('=');
    vars.push(hash[0]);
    vars[hash[0]] = hash[1];
  }
 
  return vars;
}

get=getUrlVars();
if(get['efl']!=undefined) document.location='#adv';

function showstep(step){
  document.getElementById('step'+step).style.display='block';
  var m=step.match('^([a-z]+)([0-9]{1,2})$');
  if(m==null) return;
  m[2]++;
  while(document.getElementById('step'+m[1]+m[2])){
    document.getElementById('step'+m[1]+m[2]).style.display='none';
    m[2]++;
  }
}

function prixsls(){
  if(poids==0) return 0;
  var tranches=new Array();\n";
  $requete=$DB_site2->query("SELECT * FROM sls INNER JOIN slszones USING(zone) WHERE slszones.dep=".intval(substr($rs_facture['codepostal'],0,2)));
  while($sls=$DB_site2->fetch_array($requete)) print "tranches[".($sls['from']/1000)."]=".$sls['achat'].";\n";
  print "var keys=new Array();
    for(i in tranches) keys.push(i);
    for(i=0;i<keys.length-1;i++)
      if(keys[i]>=poids) return tranches[keys[i-1]];
}

var lieustocknb=new Array();
function selstock(sender,lieuids){
  var lieuid=lieuids.split(',');
  var value=(sender.checked?1:-1);
  for(i=0;i<lieuid.length;i++){
      if(isNaN(lieustocknb[lieuid[i]])) lieustocknb[lieuid[i]]=0;
      lieustocknb[lieuid[i]]+=value;
  }
  var prodnb=0;
  var produits=document.getElementsByName('produits[]');
  for(i=0;i<produits.length;i++) if(produits[i].checked) prodnb++;

  for(l in lieustocknb){
      if(document.getElementById('fournstocktd'+l)){
	document.getElementById('fournstocktd'+l).style.display=(lieustocknb[l]==prodnb?'table-cell':'none');
      }
  }
}

function setlieustock(id){
  document.getElementsByName('lieustock')[0].value=id;
}

function setcmdtype(){
  var states=new Array();
  var chkproduit=document.getElementsByName('produits[]');
  for(i=0;i<chkproduit.length;i++){
      if(chkproduit[i].checked){
	  var state=document.getElementsByName('envoi_'+chkproduit[i].value)[0].value;
	  states[state]=true;
      }
  }
  document.getElementById('newcmd').checked=false;
  document.getElementById('newcmd').style.display=(states[2]?'none':'inline');
  document.getElementById('delcmd').checked=false;
  document.getElementById('delcmd').style.display=(states[1]?'none':'inline');
  document.getElementById('step4msg').style.display=((states[1]&&states[2])?'inline':'none');
}

var activefournkeys=new Array();
function showlink(sender,fournkeys){
  for(i=0;i<fournkeys.length;i++){
     if(isNaN(activefournkeys[fournkeys[i]])) activefournkeys[fournkeys[i]]=0;
     activefournkeys[fournkeys[i]]+=(sender.checked?1:-1);
  }
  
  var prodnb=0;
  var produits=document.getElementsByName('produits[]');
  for(i=0;i<produits.length;i++) if(produits[i].checked) prodnb++;

  for(l in activefournkeys){
      if(document.getElementById(l)) document.getElementById(l).style.display=(activefournkeys[l]==prodnb?'table-cell':'none');
  }
}
</script>
<script language=\"javascript\" src=\"../javascripts/prototype.js\"></script>
<script language=\"javascript\" src=\"trsptprice.js\"></script>";

print "<a name=\"adv\"></a><table height=\"30\" style=\"margin-top:20px;\">
<tr><td class=\"tab_off\" id=\"tablink1\"><a href=\"javascript:tab(1);\">Fournisseur</a></td><td class=\"tab_off\" id=\"tablink2\"><a href=\"javascript:tab(2);\">Transporteur</a></td><td class=\"tab_off\" id=\"tablink3\"><a href=\"javascript:tab(3);\">Installateur</a></td><td class=\"tab_on\" id=\"tablink4\"><a href=\"javascript:tab(4);\">R�capitulatif</a></td></tr>
</table>";

$fournisseurs=array("acilyon test"=>"pr@mon-espace-cuisson.com","stock acilyon"=>"pr@mon-espace-cuisson.com","eurocave - caves"=>"mbrun@eurocave.com;clefrancois@eurocave.com","eurocave accessoires"=>"FGRZUNOV@eurocave.com","climadiff"=>"ndufloux@climadiff.com;secretariat@climadiff.com;adv@vinosafe.com","vinosafe"=>"h.fehrenbach@vinosafe.com;jc.lody@vinosafe.com","la sommeliere"=>"isabelle.reze@lasommeliere.com","liebherr"=>"isabelle.andres@eberhardt.fr","dometic"=>"veronique.bocque@dometic.fr;laurence.martin@dometic.fr","Atelier du VIN"=>"commercial@atelierduvin.com;georges.richard@atelierduvin.com;support@atelierduvin.com","Systena"=>"info@systena.fr","Interfroid"=>"anne.gadeyne@interfroidservices.fr");

$dep="XX";
if(strlen($rs_facture['codepostal'])==5 && ($rs_facture['paysid']==57 || $rs_facture['paysid']==181)) $dep=substr($rs_facture['codepostal'],0,2);

$requete=$DB_site2->query("SELECT * FROM fournisseur WHERE type='trspt' ORDER BY societe,ville");
$optlieutrspt="";
while($lieutrspt=$DB_site2->fetch_array($requete)){
    $optlieutrspt.="<option value=\"$lieutrspt[id]\">$lieutrspt[societe] $lieutrspt[ville] (".substr($lieutrspt['cp'],0,2).")</option>";
}

$fournlink=array();
$list=$DB_site2->query("SELECT * FROM fournisseur WHERE `type`='fournadv' ORDER BY societe");
while($f=$DB_site2->fetch_array($list)){
    $mids=explode(",",$f['marques']);
    foreach($mids as $mid){
      if($f['groupes']!=""){
	  $gids=explode(",",$f['groupes']);
	  foreach($gids as $gid) $fournlink[$mid][]=fournkey($gid);
      }else
	  $fournlink[$mid][]=fournkey($f['societe']);
      $fournlink[$mid]=array_unique($fournlink[$mid]);
    }
}

/*print "<div id=\"tab0\" style=\"\">

<h3>Suivi commande</h3>
<form name=\"form0\" method=\"post\" action=\"facture.php?action=editer&facturid=".$facturid."\" enctype=\"multipart/form-data\">
<div class=\"advgroup\" style=\"display:none;\">
<div style=\"float:left;\">
<textarea name=\"suivitxt\" cols=\"50\" rows=\"15\">".$suivi['suivi2']."</textarea>
</div>
<div style=\"\">
  Enlev. chez fourn. par nos soins ou d�part Trspt fourn
  <br><input type=\"text\" name=\"advdate1\" id=\"advdate1\" value=\"$advdate1\" onclick=\"calendrier_show('advdate1',this.value);\" readonly>
  <br><br>Livr. Chez Instal. / chez Trspt. / chez Acilyon
  <br><input type=\"text\" name=\"advdate2\" id=\"advdate2\" value=\"$advdate2\" onclick=\"calendrier_show('advdate2',this.value);\" readonly>
  <br><br>Livr./inst. Chez client
  <br><input type=\"text\" name=\"advdate3\" id=\"advdate3\" value=\"$advdate3\" onclick=\"calendrier_show('advdate3',this.value);\" readonly>
</div>
<br><input type=\"submit\" value=\"Enregistrer le suivi commande\">
</div>
</form>
</div>";*/

$stock=new stock($DB_site2);

print "<div id=\"tab1\" style=\"".(!isset($_GET['diss'])?"visibility:hidden; position:absolute;":"")."\">

<h3><img src=\"images/cave.png\" width=\"20\" align=\"absmiddle\"> Fournisseur de caves + accessoires</h3>
<form name=\"form1\" action=\"facture.php?action=editer&facturid=".$facturid."\" method=\"post\" enctype=\"multipart/form-data\">
<input type=\"hidden\" name=\"actionadv\" value=\"advfournisseur\">
<input type=\"hidden\" name=\"fournisseurname\">
<input type=\"hidden\" name=\"lieustock\" value=\"0\">";

print "<div id=\"stepfourn1\" class=\"advgroup\">
<p>1. Produits</p>
<table>
<tr><td colspan=\"2\"></td><td>qte</td><td>d�tail</td><td>marque / ref acilyon</td><td>ref fournisseur</td><td>prix achat</td><td>eco part</td></tr>";

$chkstock=array();
$lignefactures=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid = '$facturid'");
while ($lignefacture=$DB_site2->fetch_array($lignefactures)){
      $article=$DB_site2->query_first("SELECT * FROM article a, article_marque am, marque m WHERE am.artid=a.artid AND am.marqueid=m.marqueid AND a.artid=".$lignefacture['artid']);
      $envoi=$DB_site2->query_first("SELECT * FROM envoi_fournisseur_ligne WHERE type='fourn' AND ligne_facture_id=$lignefacture[lignefactureid]");
      $caracts=$DB_site2->query("SELECT * FROM lignefacturecaracteristique WHERE paht<>0 AND lignefactureid=".$lignefacture[lignefactureid]);
      $lignestate=1;
      $style="";
      if($envoi['date']!=""){
	$style="background-color:green;";
	$lignestate=2;
      }

      $lieustock=array();
      $paramstock=array();
      $jsstock="";
      $stockartid=$stock->similaire($lignefacture['artid']);
      $stockitems=$stock->stockbyartid($stockartid);
      foreach($stockitems as $stockitem) if($stockitem->state==$lignestate) $lieustock[$stockitem->lieu]++;
      foreach($lieustock as $lieu=>$qtedispo) 
	if($qtedispo>=$lignefacture[qte]){
	  $paramstock[]=intval($lieu);
	  $chkstock[$lieu][$article[artcode]." (".$article[artcode_eurocave].")"]=$qtedispo;
	}
      $paramstock=array_unique($paramstock);
      $jsstock="selstock(this,'".implode(",",$paramstock)."');";
      $rowspan=$DB_site2->num_rows($caracts)+2;
      $lot=$DB_site2->query_first("SELECT COUNT(*) FROM article_lot WHERE lotid=".$lignefacture['artid']);
      $img="";
      $path="../configurations/".$host."/images/produits/br/".$lignefacture['artid'].".jpg";
      if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
      if($article[libelleFR]=="") $article[libelleFR]=$lignefacture['libelle'];
      if($lot[0]==0){
	print "<tr>
	  <td rowspan=\"$rowspan\" style=\"$style\"><input type=\"checkbox\" name=\"produits[]\" value=\"$lignefacture[lignefactureid]\" onclick=\"sumprod();showstep('fourn2');setcmdtype();$jsstock showlink(this,new Array('".implode("','",$fournlink[$article['marqueid']])."'));\"><input type=\"hidden\" name=\"envoi_$lignefacture[lignefactureid]\" value=\"$lignestate\"></td>
	  <td rowspan=\"$rowspan\">$img</td>
	  <td rowspan=\"$rowspan\"><input type=\"hidden\" name=\"qte_$lignefacture[lignefactureid]\" value=\"$lignefacture[qte]\">$lignefacture[qte]</td>
	  <td rowspan=\"$rowspan\"><textarea name=\"desc_$lignefacture[lignefactureid]\" cols=\"28\" rows=\"3\">$article[libelleFR]</textarea></td>
	  <td>$article[libelle]<br>$lignefacture[artcode]</td>
	  <td><input type=\"text\" name=\"ref_$lignefacture[lignefactureid]\" value=\"$article[artcode_eurocave]\" size=\"30\"></td>
	  <td><input type=\"text\" name=\"price1_$lignefacture[lignefactureid]\" value=\"$lignefacture[paht]\" size=\"7\"></td>
	  <td><input type=\"text\" name=\"ecopart_$lignefacture[lignefactureid]\" value=\"$article[ecopartht]\" size=\"5\"></td>
	</tr>";

	while($caract=$DB_site2->fetch_array($caracts)){
	  print "<tr class=\"caracts\">
		     <td align=\"right\"><input type=\"checkbox\" name=\"caract_".$lignefacture[lignefactureid]."[]\" value=\"$caract[caractvalid]\" onclick=\"sumprod()\"></td>
		     <td>".$caract['libelle_caract']."<br>".stripslashes($caract['libelle_caractval'])."</td>
		     <td><input type=\"text\" name=\"caractval[$caract[caractvalid]]\" value=\"".$caract['paht']."\" size=\"5\"></td>
		  </tr>";
	}
	print "<tr class=\"caracts\">
		<td align=\"right\"><input type=\"checkbox\" name=\"gar_$lignefacture[lignefactureid]\" value=\"1\" onclick=\"sumprod()\"></td>
		<td>Autre garantie constructeur</td>
		<td><input type=\"text\" name=\"garval_$lignefacture[lignefactureid]\" size=\"5\"></td>
	</tr>";
      }else{
	 print "<tr><td style=\"color:red;\">&#9888;</td><td>$lignefacture[qte]</td><td>Cet article est compos� de ".$lot[0]." articles qu'il faut <a href=\"facture.php?action=editer&facturid=$facturid&diss=$lignefacture[lignefactureid]#adv\">dissocier</a> avant de les commander</td><td>$article[libelle]</td><td>$lignefacture[artcode]</td><td colspan=\"4\"></td></tr>";
      }
      
}
print "<tr><td colspan=\"6\">Total :</td><td id=\"prod1total\" nowrap></td></tr>
<tr><td><input type=\"checkbox\" name=\"uncolis\" value=\"1\"></td><td colspan=\"4\">Attention : mettre tous les articles dans 1 seul colis</td></tr>
<tr><td><input type=\"checkbox\" name=\"palette\" value=\"1\"></td><td colspan=\"5\">Attention : Article(s) � mettre sur palette + cerclage pour le transport</td></tr>
<tr><td><input type=\"checkbox\" name=\"suremballage\" value=\"1\"></td><td colspan=\"4\">Attention :  Pr�voir un suremballage du produit + palette + cerclage pour le transport</td></tr>
</table>
</div>";

print "<div id=\"stepfourn2\" class=\"advgroup\">
<p>2. Choix du fournisseur</p>
<table>
<tr><td><input type=\"radio\" name=\"fournisseur\" value=\"0\" onclick=\"document.getElementsByName('fournisseurname')[0].value='0';showstep('fourn7');setlieustock(0);\"> Aucun fournisseur</td><td><input type=\"radio\" name=\"fournisseur\" id=\"fourn0\" value=\"webmaster@mon-espace-cuisson.com\" onclick=\"document.getElementsByName('fournisseurname')[0].value='test';showstep('fourn3');setlieustock(0);\"> <a href=\"#\" title=\"webmaster@mon-espace-cuisson.com\">Test</a> </td></tr>
<tr>";
$i=0;
$c=0;
/*foreach($fournisseurs as $name=>$mail){
                      $i++;
                      $c++;
                      print "<td><input type=\"radio\" name=\"fournisseur\" id=\"fourn$i\" value=\"".strtolower($mail)."\" onclick=\"document.getElementsByName('fournisseurname')[0].value='$name';\"></td><td><a href=\"#\" onclick=\"javascript:document.getElementById('fourn$i').checked=true;\" title=\"$mail\">".ucfirst($name)."</a> </td>";
                      if($c==2){
                           $c=0;
                           print "</tr><tr>";
                      }
}*/

$fmarques=array();
$lignefactures=$DB_site2->query("SELECT am.* FROM lignefacture lf INNER JOIN article_marque am USING (artid) WHERE factureid = '$facturid'");
while($ligne=$DB_site2->fetch_array($lignefactures))
    $fmarques[]=$ligne['marqueid'];
$fmarques=array_unique($fmarques);

$fourn=array();
$list=$DB_site2->query("SELECT * FROM fournisseur WHERE `type`='fournadv' ORDER BY societe");
while($advf=$DB_site2->fetch_array($list)){
    if(advmarque(explode(',',$advf['marques']),$fmarques)){
	if($advf['groupes']!=""){
	  $groups=explode(",",$advf['groupes']);

	  foreach($groups as $group)
	    $fourn[$group][]=$advf['mail'];
	}else
	  $fourn[$advf['societe']][]=$advf['mail'];
    }
}

$c=0;
// $chkstock=array_unique($chkstock);
foreach($chkstock as $lieuid=>$articles){
  $sfourn=$DB_site2->query_first("SELECT id,societe,ville,mail FROM fournisseur WHERE id=".intval($lieuid));
  $qtetxt=array();
  foreach($articles as $artcode_eurocave=>$qte) $qtetxt[]=$artcode_eurocave." : ".$qte;

  if($sfourn['id']!=""){
      print "<td id=\"fournstocktd$lieuid\" style=\"display:none;color:red;\"><input type=\"radio\" name=\"fournisseur\" id=\"fournstock$lieuid\" value=\"".$sfourn['mail']."\" onclick=\"document.getElementsByName('fournisseurname')[0].value='Acilyon';showstep('fourn3');setlieustock($lieuid);\"> Stock $sfourn[societe] $sfourn[ville]<br>".implode("<br>",$qtetxt)."</td>";
      $c++;
  }
  if($c==2){
      $c=0;
      print "</tr><tr>";
  }
}

foreach($fourn as $group=>$groupmails){
	    $i++;
	    $c++;
	    $mailcsv=implode(";",$groupmails);
	    print "<td id=\"".fournkey($group)."\" style=\"display:none;\"><input type=\"radio\" name=\"fournisseur\" id=\"fourn$i\" value=\"".$mailcsv."\" onclick=\"document.getElementsByName('fournisseurname')[0].value='".addslashes($group)."';showstep('fourn3');setlieustock(0);\"> <a href=\"#\" onclick=\"javascript:document.getElementById('fourn$i').checked=true;\" title=\"$mailcsv\">".ucfirst($group)."</a> </td>";
            if($c==2){
                   $c=0;
                   print "</tr><tr>";
            }
}

print "</tr></table>
</div>";

print "<div id=\"stepfourn3\" class=\"advgroup\">
<p>3. Pi�ces jointes</p>
<table>
<tr><td><input type=\"checkbox\" name=\"nopj\" value=\"0\" onclick=\"showstep('fourn4');\"></td><td>Aucune</td></tr>
<tr><td><input type=\"checkbox\" name=\"blpj\" value=\"1\" onclick=\"showstep('fourn4');\"></td><td>Bon de livraison</td></tr>
<tr><td><input type=\"checkbox\" name=\"blslspj\" value=\"1\" onclick=\"showstep('fourn4');\"></td><td>Bon de livraison autre adresse <span id=\"blslsname\"></span></td></tr>
<tr><td><input type=\"checkbox\" name=\"facturepj\" value=\"1\" onclick=\"showstep('fourn4');\"></td><td>Facture (pour commandes export uniquement)</td></tr>
<tr><td><input type=\"checkbox\" name=\"glspj\" value=\"1\" onclick=\"showstep('fourn4');\"></td><td>Bon GLS ou FEDEX Express</td><td><input type=\"file\" name=\"bongls\"></td></tr>
<tr><td><input type=\"checkbox\" name=\"trsptpja4\" value=\"1\" onclick=\"showstep('fourn4');\"></td><td>Fragile + Note importante A4</td></tr>
<tr><td><input type=\"checkbox\" name=\"trsptpja5\" value=\"1\" onclick=\"showstep('fourn4');\"></td><td>Fragile + Note importante A5</td><td></td></tr>
<!--<tr><td><input type=\"checkbox\" name=\"identpj\" value=\"1\" onclick=\"showstep('fourn4');\"></td><td colspan=\"2\">Texte � inscrire sur colis : Commande N� $facturid De Ma cave � vin - ACILYON pour $rs_facture[nom] $rs_facture[prenom] $rs_facture[codepostal] $rs_facture[ville] $pays_fact[libelle]</td></tr>-->
<tr><td><input type=\"checkbox\" name=\"otherpj\" value=\"1\" onclick=\"showstep('fourn4');\"></td><td>Autre : <input type=\"text\" name=\"otherpjname\" size=\"60\"></td><td><input type=\"file\" name=\"otherpjfile\"></td></tr>
</table>
</div>";

print "<div id=\"stepfourn4\" class=\"advgroup\">
<p>4. Objet du mail</p>
<span id=\"newcmd\"><input type=\"radio\" name=\"typecmd\" value=\"new\"  onclick=\"showstep('fourn5');\"> Nouvelle commande</span>
<span id=\"delcmd\"><br><input type=\"radio\" name=\"typecmd\" value=\"delete\" onclick=\"showstep('fourn7');\"> Annuler commande</span>
<span id=\"step4msg\">Les articles s�lectionn�s ne permettent pas de passer ou annuler une commande</span>
<!--<input type=\"radio\" name=\"mailsubject\" value=\"cmd\" onclick=\"document.getElementsByName('intro')[0].checked=true;document.getElementsByName('endtxt2')[0].checked=true;\" checked> Commande n�$facturid
<br><input type=\"radio\" name=\"mailsubject\" value=\"annule\" onclick=\"document.getElementsByName('intro')[1].checked=true;document.getElementsByName('endtxt2')[1].checked=true;\"> Annulation de la commande n�$facturid-->
</div>";

// <option value=\"Mazet Colmar<br>ZI Nord - BP 257<br>68005 Colmar<br>T�l : 03.89.21.09.40 Fax : 03.89.21.09.49\">Mazet Colmar</option><option value=\"Mazet Gonesse<br>1 RD 370 - BP 45<br>95500 Gonesse<br>T�l : 01.34.07.72.00 Fax : 01.39.87.07.37\">Mazet Gonesse</option><option value=\"Mazet Montelimar<br>ZI Du Gournier<br>26205 Mont�limar<br>T�l : 04.75.53.15.90 Fax : 04.75.00.72.29\">Mazet Montelimar</option><option value=\"Mory Valence<br>45 Avenue Marseille<br>26000 Valence<br>T�l : 04.75.78.60.12\">Mory Valence</option>

// $lieuxlivr=$DB_site2->query("SELECT * FROM fournisseur WHERE `type`='

if($rs_facture['modelivraison']=="1"){
  $slschecked="checked";
  $slsstyle="color:red;";
}else{
  $slschecked="";
  $slsstyle="";
}

$opt="<option style=\"font-weight:bold;\">Choisir ...</option>";
$requete=$DB_site2->query("SELECT * FROM fournisseur WHERE type='trspt' GROUP BY societe");
while($t=$DB_site2->fetch_array($requete))
    $opt.="<option value=\"$t[societe]\">$t[societe]</option>";

$optsls="<option value=\"\">Choisir ...</option>";
$requete=$DB_site2->query("SELECT * FROM fournisseur WHERE type='sls'");
while($sls=$DB_site2->fetch_array($requete))
    $optsls.="<option value=\"$sls[id]\"".(strpos($sls['fonction'],$dep)>-1?"style=\"color:red;\"":"").">$sls[societe] $sls[ville] $sls[fonction]</option>";


print "<div id=\"stepfourn5\" class=\"advgroup\">
<p>5. Mode de transport</p>
<b>Date livraison commande : $datelivrtxt</b>
<table>
<tr>
  <td valign=\"top\"><input type=\"radio\" name=\"trsptmode\" value=\"parenlevement\" onclick=\"sumprod();selectfournlivr(this.value);\">Enlevement � notre charge en vos locaux
    <div id=\"parenlevement\" style=\"display:none;\">
      par le transporteur <select name=\"selecttrspt\" onclick=\"showstep('fourn6');showstep('fourn7');\">$opt</select>
      <br><input type=\"checkbox\" id=\"chkenlev2sls\" onclick=\"sumprod()\" $slschecked> <span style=\"$slsstyle\">installation par SLS</span> <select name=\"enlev2sls\" onclick=\"document.getElementById('blslsname').innerHTML=this.options[this.selectedIndex].text;\">$optsls</select>
    </div>
  </td>
  <td id=\"tdparenlevement\" style=\"border-left:solid thin black;display:none;\">
    Date d�part maximum
    <br><input type=\"text\" name=\"parenlevindate\" id=\"parenlevindate\" size=\"10\" onclick=\"calendrier_show(this.id,this.value);\" readonly>
  </td>
</tr>
<tr>
  <td valign=\"top\">
    <input type=\"radio\" name=\"trsptmode\" value=\"parvossoins\" onclick=\"sumprod();selectfournlivr(this.value);\">Livraison par vos soins par votre transporteur 
    <div id=\"parvossoins\" style=\"display:none;\">
      <input type=\"radio\" name=\"trspt\" value=\"fournisseur\" onclick=\"sumprod();showstep('fourn6');showstep('fourn7');\"> Chez notre client
      <br><input type=\"radio\" name=\"trspt\" value=\"colissimo\" onclick=\"sumprod();showstep('fourn6');showstep('fourn7');\"> Par colissimo chez notre client
      <br><input type=\"radio\" name=\"trspt\" value=\"asls\" onclick=\"sumprod();showstep('fourn6');showstep('fourn7');\" $slschecked> <span style=\"$slsstyle\">Chez SLS</span> <select name=\"fourn2sls\" onclick=\"document.getElementById('blslsname').innerHTML=this.options[this.selectedIndex].text;\">$optsls</select>
      <br><input type=\"radio\" name=\"trspt\" value=\"livraisonmazet\" onclick=\"sumprod();showstep('fourn6');showstep('fourn7');\"> Chez le transporteur <select name=\"selectmazet\"onclick=\"document.getElementById('blslsname').innerHTML=this.options[this.selectedIndex].text;\"><option value=\"\" style=\"font-weight:bold;\" selected>Choisir ...</option>$optlieutrspt</select>
      <br><input type=\"radio\" name=\"trspt\" value=\"installsls\" onclick=\"sumprod();showstep('fourn6');showstep('fourn7');\"> Avec installation par votre installateur
      <br><input type=\"checkbox\" name=\"chkinfotrspt\">Info transporteur : <input type=\"text\" name=\"infofournothertxt\" size=\"60\">
    </div>
  </td>
  <td id=\"tdparvossoins\" style=\"border-left:solid thin black;display:none;\">
    Date d�part maximum
    <br><input type=\"text\" name=\"parvossoinsindate\" id=\"parvossoinsindate\" size=\"10\" onclick=\"calendrier_show(this.id,this.value);\" readonly>
    <br><br><input type=\"radio\" name=\"typedatelivrfourn\" value=\"max\" selected>Date livraison au plus tard 
    <br><input type=\"radio\" name=\"typedatelivrfourn\" value=\"imperative\">livraison imp�rative
    <br>le <input type=\"text\" name=\"parvossoinsoutdate\" id=\"parvossoinsoutdate\" size=\"10\" onclick=\"calendrier_show(this.id,this.value);\" readonly>
    <br><br>Cout du transport HT <input type=\"text\" name=\"mazetcout\" size=\"4\" onblur=\"sumprod()\">
  </td>
</tr>
</table>
</div>";

print "<div id=\"stepfourn6\" class=\"advgroup\">
<p>6. Prix</p>
<table>
<tr><td>Prix des produits</td><td id=\"prixprodtotal\"></td></tr>
<tr><td>Eco participation</td><td id=\"prixecoparttotal\"></td></tr>
<tr><td>Transport</td><td id=\"prixtrspttotal\"></td></tr>
<tr><td>Garantie fournisseur</td><td id=\"prixgartotal\"></td></tr>
<tr><td>Montant total commande HT :</td><td id=\"prixfourntotal\"></td></tr>
</table>
<br>Informations suppl�mentaires :<br><textarea name=\"infocomplement\" cols=\"60\"></textarea>
</div>";

print "<div id=\"stepfourn7\"><input type=\"button\" onclick=\"valid1()\" value=\"Envoyer la commande au fournisseur\" /> <input type=\"button\" value=\"Aper�u\" onclick=\"valid1(true);\"></div>";

print "</div>
</form>";

print "<div id=\"tab2\" style=\"visibility:hidden; position:absolute;\">
<h3><img src=\"images/livraison.png\" width=\"25\" align=\"absmiddle\"> Fournisseur de TRANSPORT</h3>
<form name=\"form2\" action=\"facture.php?action=editer&facturid=".$facturid."\" method=\"post\" enctype=\"multipart/form-data\">
<input type=\"hidden\" name=\"actionadv\" value=\"advtransport\">
<input type=\"hidden\" name=\"trsptname\">";

$marqueids=array();
print "<div id=\"steptrspt1\" class=\"advgroup\">
<p>1. Produits</p>
<table>
<tr><td colspan=\"2\"></td><td>qte</td><td>D�tail</td><td>Poids brut en kg</td></tr>";
$lignefactures=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid = '$facturid'");
while ($lignefacture=$DB_site2->fetch_array($lignefactures)){
      $article=$DB_site2->query_first("SELECT * FROM article a, article_marque am, marque m WHERE am.artid=a.artid AND am.marqueid=m.marqueid AND a.artid=".$lignefacture['artid']);
      $envoi=$DB_site2->query_first("SELECT * FROM envoi_fournisseur_ligne WHERE type='trspt' AND ligne_facture_id=$lignefacture[lignefactureid]");
      if($article['marqueid']!=null) $marqueids[]=$article['marqueid'];
      $style="";
      if($envoi['date']!="") $style="background-color:green;";
      $img="";
      $path="../configurations/".$host."/images/produits/br/".$lignefacture['artid'].".jpg";
      if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
      print "<tr>
	<td style=\"$style\"><input type=\"hidden\" name=\"price_$lignefacture[lignefactureid]\" value=\"$article[paht]\"><input type=\"hidden\" name=\"pv_$lignefacture[lignefactureid]\" value=\"$article[prixht]\"><input type=\"hidden\" name=\"ecopart2_$lignefacture[lignefactureid]\" value=\"$article[ecopartht]\"><input type=\"checkbox\" name=\"produits2[]\" value=\"$lignefacture[lignefactureid]\" onclick=\"totalassurance();showstep('trspt2');\"></td>
	<td>$img</td>
	<td><input type=\"hidden\" name=\"qte2_$lignefacture[lignefactureid]\" value=\"$lignefacture[qte]\">$lignefacture[qte]</td>
	<td>$article[libelleFR]</td>
	<td><input type=\"hidden\" name=\"poids_$lignefacture[lignefactureid]\" value=\"$article[emballage_poids]\">$article[emballage_poids]<input type=\"hidden\" name=\"poidsvolume_$lignefacture[lignefactureid]\" value=\"$article[poidsvolume]\"><input type=\"hidden\" name=\"volume_$lignefacture[lignefactureid]\" value=\"$article[volume]\"></td>
      </tr>";
}
$marqueids=array_unique($marqueids);
print "<tr><td colspan=\"3\"></td><td>Poids total</td><td id=\"poidstotal\"></td></tr>
<tr><td><input type=\"checkbox\" name=\"uncolis\" value=\"1\"></td><td colspan=\"3\">Attention : un seul colis pour tous les articles</td></tr>
<tr><td><input type=\"checkbox\" name=\"hayon\" value=\"1\" checked></td><td colspan=\"3\">Camion avec hayon indispensable</td></tr>
</table>
</div>";

print "<div id=\"steptrspt2\" class=\"advgroup\">
<p>2. Type de livraison</p>
<table>
<tr>
  <td><input type=\"radio\" name=\"typetrspt\" value=\"0\" onclick=\"document.getElementsByName('trsptname')[0].value='0';showstep('trspt12');\"></td>
  <td>Pas de transport</td>
  <td><input type=\"radio\" name=\"typetrspt\" value=\"webmaster@mon-espace-cuisson.com\" onclick=\"document.getElementsByName('trsptname')[0].value='Acilyon test';showstep('trspt3');\"></td>
  <td>Acilyon test</td>
</tr>";
/*<tr>
  <td><input type=\"radio\" name=\"typetrspt\" value=\"ph.rodriguez@acilyon.com\" onclick=\"document.getElementsByName('trsptname')[0].value='TNT/GLS Express d�part Acilyon'; tarifs('tnt');\"></td>
  <td class=\"tnt\">TNT/GLS Express d�part Acilyon</td>
  <td><input type=\"radio\" name=\"typetrspt\" value=\"ph.rodriguez@acilyon.com\" onclick=\"document.getElementsByName('trsptname')[0].value='TNT/GLS Express d�part fournisseur'; tarifs('tnt');\"></td>
  <td class=\"tnt\">TNT/GLS Express d�part fournisseur</td>
</tr>";*/

/*<!-- Mazet -->

<tr>
  <td><input type=\"radio\" name=\"typetrspt\" value=\"jbuinoud@mazet.fr;kbensouna@mazet.fr\" onclick=\"document.getElementsByName('trsptname')[0].value='Mazet Enl. fournisseur pour livraison France'; tarifs('mazet');\"></td>
  <td class=\"mazet\">Mazet - Enl fourniseur pour livraison France</td>
  <td><input type=\"radio\" name=\"typetrspt\" value=\"smarzouki@mazet.fr;mcapuano@mazet.fr\" onclick=\"document.getElementsByName('trsptname')[0].value='Mazet enl. fournisseur pour livraison export'; tarifs('mazet');\"></td>
  <td class=\"mazet\">Mazet - Enl fournisseur pour livraison export</td>
</tr>

<!-- Mory -->


<tr>
  <td><input type=\"radio\" name=\"typetrspt\" value=\"psalel@morygroup.com;jroudier@morygroup.com;bagier@morygroup.com\" onclick=\"document.getElementsByName('trsptname')[0].value='Mory Group Enl. fournisseur pour livraison France'; tarifs('mory');\"></td>
  <td class=\"mory\">Mory Group - Enl fourniseur pour livraison France</td>
  <td><input type=\"radio\" name=\"typetrspt\" value=\"psalel@morygroup.com;vtrolat@morygroup.com;bagier@morygroup.com\" onclick=\"document.getElementsByName('trsptname')[0].value='Mory group enl. fournisseur pour livraison export'; tarifs('mory');\"></td>
  <td class=\"mory\">Mory Group - Enl fournisseur pour livraison export</td>
</tr>

<!-- Alloin -->

<tr>
  <td><input type=\"radio\" name=\"typetrspt\" value=\"lemans.SAV@alloin.com;lemans.agence@alloin.com\" onclick=\"document.getElementsByName('trsptname')[0].value='Alloin'; tarifs('alloin');\"></td>
  <td class=\"mory\">Alloin</td>
</tr>*/

$fourn=array();
$list=$DB_site2->query("SELECT * FROM fournisseur WHERE `type`='trsptadv' ORDER BY societe");
while($advf=$DB_site2->fetch_array($list)){
    if($advf['groupes']!=""){
      $groups=explode(",",$advf['groupes']);
      foreach($groups as $group)
	$fourn[$group][]=$advf['mail'];
    }else
      $fourn[$advf['societe']][]=$advf['mail'];
}


foreach($fourn as $group=>$mails){
	    $i++;
	    $c++;
	    $mails=array_unique($mails);
	    $mailcsv=implode(";",$mails);
	    print "<td><input type=\"radio\" name=\"typetrspt\" onclick=\"document.getElementsByName('trsptname')[0].value='$group'; tarifs('$group');showstep('trspt3');\" value=\"$mailcsv\"></td><td title=\"$mailcsv\">".ucfirst($group)."</td>";
            if($c==2){
                 $c=0;
                 print "</tr><tr>";
            }
}

print "</table>
</div>";

print "<div id=\"steptrspt3\" class=\"advgroup\">
<p>3. Pi�ces jointes</p>
<input type=\"checkbox\" name=\"notrsptpj\" value=\"0\" onclick=\"showstep('trspt4');\"> Aucune
<br><input type=\"checkbox\" name=\"bonstrsptpj\" value=\"1\" onclick=\"showstep('trspt4');\"> Bon de livraison client
<br><input type=\"checkbox\" name=\"bonstrsptslspj\" value=\"1\" onclick=\"showstep('trspt4');\"> Bon de livraison adresse installateur <span id=\"bonslsname\"></span>
<br><input type=\"checkbox\" name=\"facturetrsptpj\" value=\"1\" onclick=\"showstep('trspt4');\"> Facture (pour commande export uniquement)
<br><input type=\"checkbox\" name=\"othertrsptpj\" value=\"1\" onclick=\"showstep('trspt4');\"> Autre : <input type=\"file\" name=\"othertrsptpjfile\">
</div>";

print "<div id=\"steptrspt4\" class=\"advgroup\">
<p>4. Objet du mail</p>
<input type=\"radio\" name=\"trsptobj\" value=\"cmd\" onclick=\"showstep('trspt5');\"> Commande n�$facturid
<br><input type=\"radio\" name=\"trsptobj\" value=\"annule\" onclick=\"showstep('trspt12');\"> Annulation de la commande n�$facturid";
if($rs_facture['provenance']=="sav") print "<br><input type=\"radio\" name=\"trsptobj\" value=\"avarie\" onclick=\"showstep('trspt5')\"> Commande n�$facturid - Suite avarie commande n�".$rs_facture['provenanceid']." (pour non paiement des frais de port = avarie transporteur)<input type=\"hidden\" name=\"provenanceid\" value=\"".$rs_facture['provenanceid']."\">";
print "</div>";

/*
print "<div class=\"advgroup\">
<p>4. Texte d'intro</p>
<input type=\"radio\" name=\"trsptintro\" value=\"bonjour\" checked> Bonjour, Veuillez trouver ci-joint une nouvelle commande de transport n�$facturid
<br><input type=\"radio\" name=\"trsptintro\" value=\"annule\"> Merci d'annuler le transport sur la commande n�$facturid et nous le confirmer par retour de ce mail.
</div>";
*/

print "<div id=\"steptrspt5\" class=\"advgroup\">
<p>5. Lieu d'enl�vement</p>
<table>
<tr><td><input type=\"radio\" name=\"lieutrspt\" value=\"fourn217\" onclick=\"showstep('trspt6');\"></td><td colspan=\"2\">Acilyon</td></tr>";

$sql="SELECT * FROM fournisseur WHERE `type`='fourn' ORDER BY societe";
$requete=$DB_site2->query($sql);
while($lieu=$DB_site2->fetch_array($requete)){
    $fournmids=explode(',',$lieu['marques']);
    foreach($marqueids as $mid){
      if(in_array($mid,$fournmids)){
	print "<tr><td><input type=\"radio\" name=\"lieutrspt\" value=\"fourn".$lieu[id]."\" onclick=\"showstep('trspt6');\"></td><td colspan=\"2\" title=\"".$lieu['nom']." ".$lieu['prenom']." - ".$lieu['adresse1']." - ".$lieu['cp']." ".$lieu['ville']."\">".ucfirst($lieu['societe'])." ".$lieu['fonction']." (".$lieu['ville'].")</td></tr>";
// 	break;
      }
    }
}

$r=$DB_site2->query("SELECT f.* FROM envoi_fournisseur ef INNER JOIN fournisseur f ON(f.id=ef.enlev) WHERE facturid=$facturid AND ef.type='fourn'");
while($lieu=$DB_site2->fetch_array($r)){
    print "<tr><td><input type=\"radio\" name=\"lieutrspt\" value=\"fourn".$lieu[id]."\" onclick=\"showstep('trspt6');\"></td><td colspan=\"2\" title=\"".$lieu['nom']." ".$lieu['prenom']." - ".$lieu['adresse1']." - ".$lieu['cp']." ".$lieu['ville']."\">STOCK ".ucfirst($lieu['societe'])." ".$lieu['fonction']." (".$lieu['ville'].")</td></tr>";
}

/*<tr><td><input type=\"radio\" name=\"lieutrspt\" value=\"eurocave\"></td><td>Eurocave</td><td></td></tr>
<tr><td><input type=\"radio\" name=\"lieutrspt\" value=\"vinosafe\"></td><td>Vinosafe</td><td></td></tr>
<tr><td><input type=\"radio\" name=\"lieutrspt\" value=\"dometic\"></td><td>trspt Mazet pour Dometic</td><td>Indiquer le n� Dometic imp�rativement <input type=\"text\" name=\"dometictrsptnb\"></td></tr>
<tr><td><input type=\"radio\" name=\"lieutrspt\" value=\"lasommeliere\"></td><td>La Sommeli�re</td><td></td></tr>
<tr><td><input type=\"radio\" name=\"lieutrspt\" value=\"atelierduvin\"></td><td>Atelier du Vin</td><td></td></tr>
<tr><td><input type=\"radio\" name=\"lieutrspt\" value=\"systena\"></td><td>Systena</td><td></td></tr>
<tr><td><input type=\"radio\" name=\"lieutrspt\" value=\"interfroid\"></td><td>Interfroid</td><td></td></tr>*/

$requete=$DB_site2->query("SELECT * FROM fournisseur WHERE type='fourn' GROUP BY societe ORDER BY societe");
while($f=$DB_site2->fetch_array($requete)) $livrparopt.="<option value=\"$f[societe]\">$f[societe]</option>";

print "<tr><td><input type=\"radio\" name=\"lieutrspt\" value=\"liebherr\" onclick=\"showstep('trspt6');\"></td><td>Livraison par <select name=\"livrpar\">$livrparopt</select></td><td>Le ou les colis vous seront livr�s directement � votre entrep�t de <select name=\"livraisoneb\"><option style=\"font-weight:bold;\">Choisir ...</option>$optlieutrspt</select> par le transporteur de la soci�t�</td></tr>
<!--<tr><td><input type=\"radio\" name=\"lieutrspt\" value=\"other\"></td><td>Autre </td><td><textarea name=\"otherlieutrspt\" cols=\"60\" rows=\"3\"></textarea></td></tr>-->
</table>
</div>";

print "<div id=\"steptrspt6\" class=\"advgroup\">
<p>6. Identification � mettre sur le colis</p>
<input type=\"checkbox\" name=\"trsptbl\" value=\"1\" onclick=\"showstep('trspt7');\"> BL fournit ci-joint
<br><input type=\"checkbox\" name=\"trsptfacture\" value=\"1\" onclick=\"showstep('trspt7');\"> Facture ci-jointe
<br><input type=\"checkbox\" name=\"trsptfragile\" value=\"1\" onclick=\"showstep('trspt7');\"> Etiquette Fragile ci-jointe
<!--<br><input type=\"checkbox\" name=\"trspttext\" value=\"1\"> Texte sur le colis : commande n�$facturid de Ma Cave � Vin - Acilyon pour $rs_facture[nom] $rs_facture[prenom] $rs_facture[codepostal] $rs_facture[ville]-->
<br><input type=\"checkbox\" name=\"trsptimportant\" value=\"1\" onclick=\"showstep('trspt7');\"> Important : enlever tous les documents de livraison du transporteur du fournisseur
<br><input type=\"checkbox\" name=\"trspttextother\" value=\"1\" onclick=\"showstep('trspt7');\"> Autre : <input type=\"text\" name=\"trsptothertext\" size=\"60\">
</div>";

print "<div id=\"steptrspt7\" class=\"advgroup\">
<p>7. Date enl�vement</p>
<b>Date livraison commande : $datelivrtxt</b>
<table>
<!--<tr><td><input type=\"radio\" name=\"trsptenl\" value=\"0\"></td><td>Aucune</td></tr>-->
<tr><td><input type=\"radio\" name=\"trsptenl\" value=\"fournisseur\" onclick=\"showstep('trspt8');\"></td><td>Date enl�vement chez fournisseur</td><td rowspan=\"3\" style=\"border-left:solid thin black;\">Date<br><input type=\"text\" name=\"trsptenlfourndate\" id=\"trsptenlfourndate\" onclick=\"calendrier_show(this.id,this.value);\" readonly></td></tr>
<tr><td><input type=\"radio\" name=\"trsptenl\" value=\"eberhardt\" onclick=\"showstep('trspt8');\"></td><td>Date livraison en vos locaux par le transporteur du fournisseur</td></tr>
<!--<tr><td><input type=\"radio\" name=\"trsptenl\" value=\"other\"></td><td>Autre : <input type=\"text\" name=\"trsptenlother\" size=\"50\"></td></tr>-->
</table>
</div>";

print "<div id=\"steptrspt8\" class=\"advgroup\">
<p>8. Type de transport</p>
<table>
<tr>
  <td><input type=\"radio\" name=\"trspttype\" value=\"rdv\" onclick=\"showstep('trspt9');\"> Adresse client avec RDV (particulier)</td>
  <td><input type=\"radio\" name=\"trspttype\" value=\"gold\" onclick=\"showstep('trspt9');\"> Express - Gold</td>
  <td><input type=\"radio\" name=\"trspttype\" value=\"nordv\" onclick=\"showstep('trspt9');\"> Adresse client sans RDV (professionnel)</td>
</tr>
</table>
</div>";

print "<div id=\"steptrspt9\" class=\"advgroup\">
<p>9. Adresse de livraison</p>";
print "<table>
<tr><td><input type=\"radio\" name=\"trsptlivr\" value=\"client\" onclick=\"document.getElementById('cplivr').innerHTML='$rs_facture[codepostal] - $pays_fact[libelle]';showstep('trspt10');\"></td><td>Adresse client</td></tr>
<tr>";
$c=0;
$requete=$DB_site2->query("SELECT * FROM fournisseur INNER JOIN pays ON (pays.paysid=fournisseur.paysid) WHERE type='sls'");
while($sls=$DB_site2->fetch_array($requete)){
    $c++;
    
    print "<td><input type=\"radio\" name=\"trsptlivr\" value=\"sls$sls[id]\" onclick=\"document.getElementById('bonslsname').innerHTML='$sls[societe] $sls[ville]';document.getElementsByName('bonstrsptslspj')[0].checked=true;document.getElementsByName('bonstrsptpj')[0].checked=false;document.getElementById('cplivr').innerHTML='$sls[cp] - $sls[libelle]';showstep('trspt10');\"></td><td title=\"".$sls['nom']." ".$sls['prenom']." - ".$sls['adresse1']." - ".$sls['cp']." ".$sls['ville']."\"".($rs_facture['modelivraison']=="1" && strpos($sls['fonction'],$dep)>-1?" style=\"color:red;\"":"").">".$sls['societe']." ".$sls['ville']." ".$sls['fonction']."</td>";
    if($c==2){
        $c=0;
	print "</tr><tr>";
    }
}

print "</tr></table>
</div>";

print "<div id=\"steptrspt10\" class=\"advgroup\">
<p>10. Date livraison</p>
<b>Date livraison commande : $datelivrtxt</b>
<br>Date livraison <input type=\"radio\" name=\"trsptdatemax\" value=\"trsptdateother\" checked>maximum <input type=\"radio\" name=\"trsptdatemax\" value=\"trsptdateauto\"> date pr�cise <input type=\"text\" name=\"trsptotherdate\" id=\"trsptotherdate\" onclick=\"calendrier_show(this.id,this.value);if(document.getElementsByName('trsptobj')[0].checked) showstep('trspt11');showstep('trspt12');\" readonly>
<br><input type=\"checkbox\" name=\"livraisonplustot\" value=\"1\">Voir la possibilit� de livrer plus t�t
<br><input type=\"checkbox\" name=\"infotrspt\" value=\"other\"> Info pour transporteur : <input type=\"text\" name=\"othertrsptinfo\" size=\"60\">
</div>";

/*
print "<div class=\"advgroup\">
<p>11. Information transporteur</p>
<input type=\"radio\" name=\"infotrspt\" value=\"0\"> Aucune
<br><input type=\"radio\" name=\"infotrspt\" value=\"rdv\"> Prendre RDV avec le client pour livraison
<br><input type=\"radio\" name=\"infotrspt\" value=\"other\"> Autre : <input type=\"text\" name=\"othertrsptinfo\" size=\"60\">
</div>";
*/

print "<div id=\"steptrspt11\" class=\"advgroup\">
<p>11. Tarifs</p>
<table>
<tr><td width=\"20\"></td><td>Valeur HT</td><td>Poids</td><td>Volume</td><td>Poids volume 100kg</td><td>Poids volume 250kg</td><td>Poids volume 300kg</td></tr>
<tr><td></td><td id=\"valeurht\"></td><td id=\"coutpoids\"></td><td id=\"volumetotal\"></td><td id=\"poidsvolume100\"></td><td id=\"poidsvolume250\"></td><td id=\"poidsvolume300\"></td></tr>
<tr><td><input type=\"checkbox\" name=\"trsptcout\" value=\"1\" onclick=\"trspttarif()\"></td><td colspan=\"6\">Cout transport <div style=\"float:right;\">CP <span id=\"cplivr\">$rs_facture[codepostal] - $pays_fact[libelle]</span></div></td><td><input type=\"text\" name=\"trsptcoutval\" value=\"\" size=\"5\"> �</td></tr>
</table>
<div id=\"priceopts\"></div>
<div style=\"width:100%;text-align:right;font-weight:bold;\">Total : <span id=\"totaltrspt\"></span></div>
<input type=\"hidden\" name=\"valeurdeclaree\" value=\"0\">

<!--<table>
<tr><td><input type=\"checkbox\" name=\"trsptcout\" value=\"1\" onclick=\"trspttarif()\"></td><td colspan=\"2\">Cout transport <div style=\"float:right;\"><span id=\"coutpoids\"></span> CP <span id=\"cplivr\">$rs_facture[codepostal] - $pays_fact[libelle]</span></div></td><td><input type=\"text\" name=\"trsptcoutval\" value=\"\" size=\"5\"> �</td></tr>
<tr id=\"trecotax\"><td><input type=\"checkbox\" name=\"trsptecotax\" value=\"1\" onclick=\"trspttarif()\"></td><td>Ecotax</td><td></td><td>1 �</td></tr>
<tr id=\"trexterieur\" style=\"visibility:hidden;\"><td><input type=\"checkbox\" name=\"trsptenlext\" value=\"12.7\" onclick=\"trspttarif()\"></td><td>Enl�vement ext�rieur</td><td></td><td>12,70 �</td></tr>
<tr id=\"trparis\"><td><input type=\"checkbox\" name=\"trsptparis\" value=\"6.40\" onclick=\"trspttarif()\"></td><td>Grande m�tropole Paris</td><td></td><td id=\"prixparis\">6.40 �</td></tr>
<tr id=\"trlyon\"><td><input type=\"checkbox\" name=\"trsptlyon\" value=\"4.9\" onclick=\"trspttarif()\"></td><td>Grande m�tropole Lyon</td><td></td><td id=\"prixlyon\">4,90 �</td></tr>
<tr id=\"trmarseille\" style=\"visibility:hidden; height:0px;\"><td><input type=\"checkbox\" name=\"trsptmarseille\" value=\"4.9\" onclick=\"trspttarif()\"></td><td>Grande m�tropole Marseille</td><td></td><td id=\"prixmarseille\">4,90 �</td></tr>
<tr id=\"alloincorse\"><td><input type=\"checkbox\" name=\"trsptcorse\" onclick=\"trspttarif()\" value=\"12\"></td><td>Corse</td><td></td><td>12,00 &euro;</td></tr>
<tr id=\"alloinsaison\" style=\"visibility:hidden;\"><td><input type=\"checkbox\" name=\"trsptsaison\" onclick=\"trspttarif()\" value=\"10\"></td><td>Taxe saisonni�re (dep. 06,11,13,20,30,34,66,83,84,98 du 01/06 au 31/08)</td><td></td><td>10%</td></tr>
<tr id=\"alloinville\" style=\"visibility:hidden;\"><td><input type=\"checkbox\" name=\"trsptalloinville\" onclick=\"trspttarif()\" value=\"5.4\"></td><td>Centre ville (Paris, Lyon, Marseille, Bordeaux)</td><td></td><td>5,40 &euro;</td></tr>
<tr id=\"tretage\" style=\"visibility:hidden;\"><td><input type=\"checkbox\" name=\"trsptetage\" value=\"25\" onclick=\"trspttarif()\"></td><td>Livraison �tage simple</td><td></td><td>25 �</td></tr>
<tr id=\"trpart\"><td><input type=\"checkbox\" name=\"trsptclientpart\" value=\"7\" onclick=\"trspttarif()\"></td><td>Client particulier pour RDV</td><td></td><td id=\"prixparticulier\">7,00 �</td></tr>
<tr id=\"trpro\"><td><input type=\"checkbox\" name=\"trsptclientpro\" value=\"0\" onclick=\"trspttarif()\"></td><td>Client professionnel sans RDV</td><td></td><td>0 �</td></tr>
<tr>
    <td><input type=\"checkbox\" name=\"trsptassurcomp\" value=\"1\" onclick=\"trspttarif()\"></td>
    <td id=\"txtassurance\">Assurance compl�mentaire 0,6% valeur</td>
    <td></td>
    <td><input type=\"radio\" name=\"baseassur\" onclick=\"document.getElementsByName('trsptassurcompval')[0].value=valtrsptassurance+Math.round(document.getElementsByName('valeurdeclaree')[0].value*pctrsptassurance)/100;\" checked>PA :<input type=\"text\" name=\"valeurdeclaree\" value=\"0\" size=\"5\" class=\"roinput\" readonly>
	<br><input type=\"radio\" name=\"baseassur\" onclick=\"document.getElementsByName('trsptassurcompval')[0].value=valtrsptassurance+Math.round(document.getElementsByName('valeurpv')[0].value*pctrsptassurance)/100;\">PV :<input type=\"text\" name=\"valeurpv\" value=\"0\" size=\"5\" class=\"roinput\" readonly>
	<br>Remb. :<input type=\"text\" id=\"valeurrembourse\" value=\"0\" class=\"roinput\" size=\"5\" readonly>
	<br><input type=\"text\" name=\"trsptassurcompval\" value=\"\" size=\"5\"> �
    </td>
</tr>
<tr><td><input type=\"checkbox\" name=\"trsptassurcompint\" value=\"1\" onclick=\"trspttarif()\"></td><td>Assurance compl�mentaire export 8.6&euro;/kg</td><td></td><td>Valeur :<input type=\"text\" name=\"valeurdeclaree2\" value=\"0\" size=\"5\" class=\"roinput\" readonly><br>Remb. :<input type=\"text\" id=\"valeurrembourse2\" value=\"0\" class=\"roinput\" size=\"5\" readonly><br><input type=\"text\" name=\"trsptassurcompintval\" value=\"\" size=\"5\"> �</td></tr>
<tr><td><input type=\"checkbox\" name=\"trsptcoutother\" value=\"1\" onclick=\"trspttarif()\"></td><td>Autre : <input type=\"text\" name=\"trsptcoutothername\" size=\"80\"></td><td></td><td><input type=\"text\" name=\"trsptcoutotherval\" value=\"\" size=\"5\"> �</td></tr>
<tr><td></td><td>Total HT (hors taxe gas oil)</td><td></td><td id=\"totaltrspt\"></td></tr>
</table>-->
</div>";

print "<div id=\"steptrspt12\"><input type=\"button\" onclick=\"valid2()\" value=\"Envoyer la commande au transporteur\"> <input type=\"button\" value=\"Aper�u\" onclick=\"valid2(true);\"></div>";

print "
</div>
</form>";


print "<div id=\"tab3\" style=\"visibility:hidden; position:absolute;\">
<h3><img src=\"images/carton.png\" width=\"25\" align=\"absmiddle\"> Installateur de mat�riel SLS</h3>
<form name=\"form3\" action=\"facture.php?action=editer&facturid=".$facturid."\" method=\"post\" enctype=\"multipart/form-data\">
<input type=\"hidden\" name=\"actionadv\" value=\"advsls\">";

print "<div id=\"stepsls1\" class=\"advgroup\">
<p>1. Produits</p>
<table>
<tr><td colspan=\"2\"></td><td>qte</td><td>D�tail</td><td>Poids brut en kg</td></tr>";
$lignefactures=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid = '$facturid'");
while ($lignefacture=$DB_site2->fetch_array($lignefactures)){
      $article=$DB_site2->query_first("SELECT * FROM article a, article_marque am, marque m WHERE am.artid=a.artid AND am.marqueid=m.marqueid AND a.artid=".$lignefacture['artid']);
      $envoi=$DB_site2->query_first("SELECT * FROM envoi_fournisseur_ligne WHERE type='sls' AND ligne_facture_id=$lignefacture[lignefactureid]");
      $style="";
      if($envoi['date']!="") $style="background-color:green;";
      $img="";
      $path="../configurations/".$host."/images/produits/br/".$lignefacture['artid'].".jpg";
      if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
      print "<tr>
	 <td style=\"$style\"><input type=\"hidden\" name=\"qte3_$lignefacture[lignefactureid]\" value=\"$lignefacture[qte]\"><input type=\"hidden\" name=\"price3_$lignefacture[lignefactureid]\" value=\"$article[paht]\"><input type=\"hidden\" name=\"ecopart3_$lignefacture[lignefactureid]\" value=\"$article[ecopartht]\"><input type=\"checkbox\" name=\"produits3[]\" value=\"$lignefacture[lignefactureid]\" onclick=\"assurancesls();showstep('sls2');\"></td>
	 <td>$img</td>
	 <td>$lignefacture[qte]</td><td>$article[libelleFR]</td>
	 <td><input type=\"hidden\" name=\"poids3_$lignefacture[lignefactureid]\" value=\"$article[emballage_poids]\">$article[emballage_poids]</td>
      </tr>";
}
print "<tr><td colspan=\"3\"></td><td>Poids total</td><td id=\"poidstotal3\"></td></tr>
</table>
</div>";

print "<div id=\"stepsls2\" class=\"advgroup\">
<p>2. Agence de livraison et d'installation</p>
<input type=\"hidden\" name=\"livrslsname\" value=\"0\">
<table>
<tr><td><input type=\"radio\" name=\"livrsls\" value=\"0\" onclick=\"document.getElementsByName('livrslsname')[0].value='0';showstep('sls11');\"></td><td>Pas d'installation</td>
<td><input type=\"radio\" name=\"livrsls\" value=\"acilyon\" onclick=\"document.getElementsByName('livrslsname')[0].value='test';showstep('sls3');\"></td><td>Acilyon test</td></tr><tr>";

$fourn=array();
$list=$DB_site2->query("SELECT * FROM fournisseur WHERE `type`='slsadv' ORDER BY societe");
while($advf=$DB_site2->fetch_array($list)){
    if($advf['groupes']!=""){
      $groups=explode(",",$advf['groupes']);
      foreach($groups as $group)
	$fourn[$group][]=$advf['mail'];
    }else
      $fourn[$advf['societe']][]=$advf['mail'];
}

foreach($fourn as $group=>$mails){
	    $i++;
	    $c++;
	    $mails=array_unique($mails);
	    $mailcsv=implode(";",$mails);
// 	    if(strpos($group,$dep)>-1) 
	    print "<td><input type=\"radio\" name=\"livrsls\" value=\"$mailcsv\" onclick=\"document.getElementsByName('livrslsname')[0].value='$group';showstep('sls3');\"></td><td title=\"$mailcsv\"".(strpos($group,$dep)>-1?" style=\"color:red;\"":"").">".ucfirst($group)."</td>";
            if($c==2){
                  $c=0;
                  print "</tr><tr>";
            }
}

/*<tr><td><input type=\"radio\" name=\"livrsls\" value=\"slsblois\"></td><td".(strpos("18/28/36/37/41/45/58/72/86/89",$dep)?" style=\"color:red;\"":"").">SLS BLOIS 18/28/36/37/41/45/58/72/86/89</td>
<td><input type=\"radio\" name=\"livrsls\" value=\"slsbordeaux\"></td><td".(strpos("16/17/33/40/47/64",$dep)?" style=\"color:red;\"":"").">SLS CESTAS 16/17/33/40/47/64</td></tr>
<td><input type=\"radio\" name=\"livrsls\" value=\"slsbordeaux2\"></td><td".(strpos("24/46/87",$dep)?" style=\"color:red;\"":"").">SLS MARSAC SUR L'ISLE 24/46/87</td>
<td><input type=\"radio\" name=\"livrsls\" value=\"slsclermont\"></td><td".(strpos("03/15/19/23/43/63",$dep)?" style=\"color:red;\"":"").">SLS CLERMONT  03/15/19/23/43/63</td></tr>
<tr><td><input type=\"radio\" name=\"livrsls\" value=\"slslille\"></td><td".(strpos("02/59/62/80",$dep)?" style=\"color:red;\"":"").">SLS FEUCHY 02/59/62/80 </td>
<td><input type=\"radio\" name=\"livrsls\" value=\"slslyon\"></td><td".(strpos("01/07/26/38/42/69/73/74",$dep)?" style=\"color:red;\"":"").">SLS CORBAS 01/07/26/38/42/69/73/74</td></tr>
<tr><td><input type=\"radio\" name=\"livrsls\" value=\"slslyon2\"></td".(strpos("21/25/39/71",$dep)?" style=\"color:red;\"":"")."><td>SLS DIJON 21/25/39/71</td>
<td><input type=\"radio\" name=\"livrsls\" value=\"slsmetz\"></td><td".(strpos("54/55/57/67",$dep)?" style=\"color:red;\"":"").">SLS SCY CHAZELLES 54/55/57/67</td></tr>
<tr><td><input type=\"radio\" name=\"livrsls\" value=\"slsmetz2\"></td><td".(strpos("08/10/51",$dep)?" style=\"color:red;\"":"").">SLS SAINT-MARTIN-SUR-LE-PRE 08/10/51</td>
<td><input type=\"radio\" name=\"livrsls\" value=\"slsmetz3\"></td><td".(strpos("52/68/70/88/90",$dep)?" style=\"color:red;\"":"").">SLS GOLBEY 52/68/70/88/90</td></tr>
<tr><td><input type=\"radio\" name=\"livrsls\" value=\"slsmiramas\"></td><td".(strpos("04/05/13/20/30/83/84",$dep)?" style=\"color:red;\"":"").">SLS MIRAMAS 04/05/13/20/30/83/84</td>
<td><input type=\"radio\" name=\"livrsls\" value=\"slsmiramas2\"></td".(strpos("06/98",$dep)?" style=\"color:red;\"":"")."><td>SLS ST LAURENT DU VAR 06/98</td></tr>
<tr><td><input type=\"radio\" name=\"livrsls\" value=\"slsrennes\"></td><td".(strpos("14/22/29/35/44/49/50/53/56/61/79/85",$dep)?" style=\"color:red;\"":"").">SLS GOVEN 14/22/29/35/44/49/50/53/56/61/79/85</td>
<td><input type=\"radio\" name=\"livrsls\" value=\"slstoulouse\"></td><td".(strpos("09/11/12/31/32/65/81/82",$dep)?" style=\"color:red;\"":"").">SLS COLOMIERS 09/11/12/31/32/65/81/82</td></tr>
<tr><td><input type=\"radio\" name=\"livrsls\" value=\"slstoulouse2\"></td><td".(strpos("34/48/66",$dep)?" style=\"color:red;\"":"").">SLS LATTES 34/48/66</td>
<td><input type=\"radio\" name=\"livrsls\" value=\"slsvillabe\"></td><td".(strpos("27/60/75/76/77/78/91/92/93/94/95",$dep)?" style=\"color:red;\"":"").">SLS VILLABE 27/60/75/76/77/78/91/92/93/94/95</td></tr>*/

print "</tr></table>
</div>";

print "<div id=\"stepsls3\" class=\"advgroup\">
<p>3. Pi�ces jointes</p>
<input type=\"checkbox\" name=\"slspj\" value=\"0\" onclick=\"showstep('sls4');\"> Aucune
<br><input type=\"checkbox\" name=\"bonslspj\" value=\"1\" onclick=\"showstep('sls4');\"> Bon de livraison
<br><input type=\"checkbox\" name=\"factureslspj\" value=\"1\" onclick=\"showstep('sls4');\"> Facture
<br><input type=\"checkbox\" name=\"otherslspj\" value=\"1\" onclick=\"showstep('sls4');\"> Autre : <input type=\"file\" name=\"otherslspjfile\">
</div>";

print "<div id=\"stepsls4\" class=\"advgroup\">
<p>4. Objet du mail</p>
<input type=\"radio\" name=\"slsobj\" value=\"cmd\" onclick=\"showstep('sls5');\"> Commande de livraison et d'installation n�$facturid
<br><input type=\"radio\" name=\"slsobj\" value=\"annule\" onclick=\"showstep('sls11');\"> Annulation de la commande n�$facturid
</div>";

/*
print "<div class=\"advgroup\">
<p>4. Texte d'intro</p>
<input type=\"radio\" name=\"slsintro\" value=\"bonjour\" checked> Bonjour, Veuillez trouver ci-joint une nouvelle commande pour livraison et installation n�$facturid
<br><input type=\"radio\" name=\"slsintro\" value=\"annule\"> Merci d'annuler le transport sur la commande n�$facturid et nous le confirmer par retour de ce mail.
<!--<br><input type=\"radio\" name=\"slsintro\" value=\"otherslsintro\"> Autre : <br><textarea name=\"otherslsintro\" cols=\"80\" rows=\"3\"></textarea>-->
</div>";
*/


$requete=$DB_site2->query("SELECT * FROM fournisseur WHERE type='trspt' GROUP BY societe ORDER BY societe");
while($trspt=$DB_site2->fetch_array($requete)){
    $opttrspt.="<option value=\"$trspt[id]\">$trspt[societe]</option>";
}
$requete=$DB_site2->query("SELECT * FROM fournisseur WHERE type='fourn' GROUP BY societe ORDER BY societe");
while($trspt=$DB_site2->fetch_array($requete)){
    $optfourn.="<option value=\"$trspt[id]\">$trspt[societe]</option>";
}
$stock=new stock($DB_site2);
$lieux=$stock->lieux();
foreach($lieux as $lieuid=>$lieu)
    $optlieu.="<option value=\"$lieuid\">$lieu</option>";
print "<div id=\"stepsls5\" class=\"advgroup\">
<p>5. Date livraison en vos entrepots</p>
<b>Date livraison commande : $datelivrtxt</b>
<table>
<!--<tr><td><input type=\"radio\" name=\"slslivr\" value=\"0\"></td><td>Aucune</td></tr>-->
<tr><td><input type=\"radio\" name=\"slslivr\" value=\"livrtrspt\" onclick=\"showstep('sls6');\"></td><td>Le ou les colis vous seront livr�s directement � votre entrepot par <select name=\"livrtrsptid\">$opttrspt</select></td><td rowspan=\"6\" style=\"border-left:solid thin black;\">Date livraison<br><input type=\"text\" name=\"slslivrdate\" id=\"slslivrdate\" onclick=\"calendrier_show(this.id,this.value);\" size=\"10\" readonly></td></tr>
<tr><td><input type=\"radio\" name=\"slslivr\" value=\"livrfourn\" onclick=\"showstep('sls6');\"></td><td>Le ou les colis vous seront livr�s directement � votre entrepot par transporteur de <select name=\"livrfournid\">$optfourn</select></td></tr>
<tr><td><input type=\"radio\" name=\"slslivr\" value=\"livrstock\" onclick=\"showstep('sls6');\"></td><td>Le ou les colis sont � prendre dans le stock <select name=\"livrstockid\">$optlieu</select></td></tr>
<tr><td><input type=\"radio\" name=\"slslivr\" value=\"livrclient\" onclick=\"showstep('sls6');\"></td><td>Le ou les colis ont d�ja �t� livr�s chez le client par un transporteur</td></tr>
</table>
<!--<input type=\"checkbox\" name=\"slsautreinfocheck\" value=\"1\"> Autre :<br><textarea name=\"slsautreinfo\" cols=\"60\" rows=\"3\" onkeyup=\"document.getElementsByName('slsautreinfocheck')[0].checked=(this.value!='')\"></textarea>-->
</div>";

print "<div id=\"stepsls6\" class=\"advgroup\">
<p>6. Identification sur le colis</p>
<input type=\"checkbox\" name=\"slsidentbl\" value=\"1\" onclick=\"showstep('sls7');\"> BL fournit ci-joint
<br><input type=\"checkbox\" name=\"slsidenttxt\" value=\"1\" onclick=\"showstep('sls7');\"> Texte sur colis : commande n�$facturid de Ma Cave � Vin - Acilyon pour <input type=\"text\" name=\"slsidentpour\" value=\"$user[nom] $user[prenom]\">
<!--<br><input type=\"checkbox\" name=\"slsidentimportant\" value=\"1\" onclick=\"showstep('sls7');\"> Important : enlever tous les documents de livraison de EBERHARDT Fr�res-->
<br><input type=\"checkbox\" name=\"trsptenldoc\" value=\"1\" onclick=\"showstep('sls7');\"> Important : enlever tous les documents de livraison du fournisseur et mettre le Bon de livraison ci-joint a la commande
<br><input type=\"checkbox\" name=\"slsidentother\" value=\"1\" onclick=\"showstep('sls7');\"> Autre : <input type=\"text\" name=\"slsidentothertxt\" size=\"60\">
</div>";

/*
print "<div class=\"advgroup\">
<p>8. Adresse de livraison</p>
<input type=\"radio\" name=\"slsadrlivr\" value=\"particulier\"> Adresse client particulier
<br><input type=\"radio\" name=\"slsadrlivr\" value=\"pro\"> Adresse client professionnel
</div>";
*/

print "<div id=\"stepsls7\" class=\"advgroup\">
<p>7. Date livraison et installation maximum</p>
<b>Date livraison commande : $datelivrtxt</b>
<br>Date livraison <input type=\"radio\" name=\"maxslslivr\" value=\"max\" checked>maximum <input type=\"radio\" name=\"maxslslivr\" value=\"precis\"> date pr�cise <input type=\"text\" name=\"slsmaxdate\" id=\"slsmaxdate\" onclick=\"calendrier_show(this.id,this.value);showstep('sls8');\" size=\"10\" readonly>
<br><input type=\"checkbox\" name=\"livraisonplustot\" value=\"1\">Voir la possibilit� de livrer plus t�t
<br><input type=\"checkbox\" name=\"slsinfoother\"> Info pour transporteur : <input type=\"text\" name=\"slsinfoothertxt\" size=\"80\">
</div>";

print "<div id=\"stepsls8\" class=\"advgroup\">
<p>8. Information transporteur et d'installation</p>
<input type=\"checkbox\" name=\"slsinfo\" value=\"0\"> Aucune
<br><input type=\"checkbox\" name=\"slsrdv\" onclick=\"showstep('sls9');\"> Prendre RDV avec le client pour livraison
<br><input type=\"checkbox\" name=\"slsbranchement\" onclick=\"showstep('sls9');\"> Important : ne pas brancher �lectriquement le produit (branchement a effectuer par le client 48h apr�s)
<br><input type=\"checkbox\" name=\"slsmiseniveau\" onclick=\"showstep('sls9');\"> Important : mettre � niveau le produit � l'aide des pieds r�glables
<br><input type=\"checkbox\" name=\"slsmontage\" onclick=\"showstep('sls9');\"> Important : Si besoin, assurer le montage des accessoires (poign�e de porte, clayettes, etc...)
<br><input type=\"checkbox\" name=\"slsemballage\" onclick=\"showstep('sls9');\"> Important : bien penser a r�cup�rer les emballages pour destruction
<br><input type=\"checkbox\" name=\"slsemballage2\" onclick=\"showstep('sls9');\"> Important : merci de laisser les emballages chez le client
</div>";

print "<div id=\"stepsls9\" class=\"advgroup\">
<p>9. R�cup�ration ancien produit</p>
<input type=\"radio\" name=\"recup\" value=\"0\" onclick=\"showstep('sls10');showstep('sls11');\"> Aucune
<br><input type=\"radio\" name=\"recup\" value=\"1\" onclick=\"showstep('sls10');showstep('sls11');\"> r�cup�ration de l'ancien produit, pour destruction. Poids : <input type=\"text\" name=\"recuppoids\" size=\"5\">
<br><input type=\"radio\" name=\"recup\" value=\"2\" onclick=\"showstep('sls10');showstep('sls11');\"> r�emballage/r�cup�ration ancien produit pour retour chez : <select name=\"recupretour\">$optfourn</select> Poids : <input type=\"text\" name=\"recuppoids2\" size=\"5\">
<br><input type=\"radio\" name=\"recup\" value=\"other\"> <input type=\"text\" name=\"recupother\" size=\"80\">
</div>";


print "<div id=\"stepsls10\" class=\"advgroup\">
<p>10. Tarifs</p>
<table>
<tr><td><input type=\"checkbox\" name=\"slscouttrspt\" value=\"1\" onclick=\"totalsls()\"></td><td>Cout transport et installation <div style=\"float:right;\"><span id=\"coutpoidssls\"></span> CP $rs_facture[codepostal] - $pays_fact[libelle]</div></td><td></td><td><input type=\"text\" name=\"slscouttrsptval\" value=\"\" size=\"5\"></td></tr>
<tr><td><input type=\"checkbox\" name=\"slscoutcorse\" value=\"1\" onclick=\"totalsls()\"></td><td>Passage maritime Corse</td><td><input type=\"text\" id=\"prixkgcorse\" value=\"1.28\" size=\"5\"> �/kg</td><td><input type=\"text\" name=\"slscorsetotal\" size=\"5\">�</td></tr>
<tr><td><input type=\"checkbox\" name=\"slscoutreprise\" value=\"1\" onclick=\"totalsls()\"></td><td>Reprise ancien produit pour destruction ou relivraison</td><td></td><td><input type=\"text\" name=\"slscoutrepriseval\" value=\"\" size=\"5\"></td></tr>
<tr><td><input type=\"checkbox\" name=\"slscoutassur\" value=\"1\" onclick=\"totalsls()\"></td><td>Assurance compl�mentaire 0,35% valeur</td><td colspan=\"2\">Valeur :<input type=\"text\" id=\"valeurtotalsls\" name=\"valeurtotalsls\" value=\"0\" size=\"5\" readonly><br>Remb. :<input type=\"text\" id=\"valeurremboursesls\" value=\"0\" size=\"5\" readonly><br><input type=\"text\" name=\"slscoutassurval\" value=\"\" size=\"5\"></td></tr>
<tr><td><input type=\"checkbox\" name=\"slscoutother\" value=\"1\" onclick=\"totalsls()\"></td><td>Autre : <input type=\"text\" name=\"slscoutothername\" size=\"80\"></td><td></td><td><input type=\"text\" name=\"slscoutotherval\" value=\"\" size=\"5\"></td></tr>
<tr><td></td><td>Total HT (Hors taxe gas oil)</td><td></td><td id=\"totalsls\"></td></tr>
</table>
</div>";

print "<div id=\"stepsls11\"><input type=\"button\" value=\"Envoyer la commande au transporteur\" onclick=\"valid3()\"> <input type=\"button\" value=\"Aper�u\" onclick=\"valid3(true);\"></div>";

print "</div>
</form>";

$today=mktime(0,0,0,date("m"),date("d"),date("Y"));
$tomorrow=$today+86400;

print "<div id=\"tab4\" style=\"visibility:visible; position:relative;\">
<h3>R�capitulatif</h3>
<table width=\"100%\">";

$total=0;

$requete=$DB_site2->query("SELECT artcode,lignefactureid FROM lignefacture WHERE factureid=$facturid");
print "<tr><td colspan=\"8\" style=\"color:white;background-color:#AAA;font-weight:bold;\"><img src=\"images/cave.png\" width=\"20\" align=\"absmiddle\"> Fournisseur produit</td></tr>";


$compat=true;
$r=$DB_site2->query("SELECT * FROM envoi_fournisseur WHERE type='fourn' AND facturid=$facturid");
while($envoi=$DB_site2->fetch_array($r)){
  $r2=$DB_site2->query("SELECT * FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) WHERE efid=".$envoi['id']." AND efl.type='fourn'");
  $livrcmd="";
  $nolivr=false;
  if($DB_site2->num_rows($r2)>0){
    $lf1=$DB_site2->fetch_array($r2);
    $rowspan=$DB_site2->num_rows($r2)+1;
    if($envoi['destinataire']!="0"){
      if($lf1['indate']!=0){
	if($envoi['enlevement']==0){
	  $nolivr=true;
	  $livrcmd="<input type=\"text\" id=\"datelivrcmd$envoi[id]\" size=\"10\" value=\"Date enl�v.\" onclick=\"efid=$envoi[id];this.value='';calendrier_show(this.id,this.value,false,updenlevcmd);\">";
	}else
	  $livrcmd="Enlev�e le ".date("d/m/Y",$envoi['enlevement'])."<br>";
	
      }
      if($lf1['outdate']!=0 && !$nolivr){
	if($envoi['livraison']==0)
	  $livrcmd.="<input type=\"text\" id=\"datelivrcmd$envoi[id]\" size=\"10\" value=\"Date livraison\" onclick=\"efid=$envoi[id];this.value='';calendrier_show(this.id,this.value,false,updlivrcmd);\">";
	else
	  $livrcmd.="Livr�e le ".date("d/m/Y",$envoi['livraison']);
      }
     
    }
    if($envoi['destinataire']=="0") $envoi['destinataire']="Pas de commande";
    $lieuenlev="";
    if($envoi['enlev']>0){
	 $fourn=$DB_site2->query_first("SELECT id,societe,ville FROM fournisseur WHERE id=".$envoi['enlev']);
	 if($fourn['id']!="") $lieuenlev="<br>Stock ".$fourn['societe']." ".$fourn['ville'];
    }
    print "<tr style=\"background-color:#00dd00;\">
      <td rowspan=\"".($rowspan)."\">".$envoi['destinataire'].$lieuenlev."<br>".date("d/m/Y H:i",$envoi['date'])."<br>".$lf1['expediteur']."<br>$livrcmd<!--<img src=\"images/supprimer.gif\" title=\"supprimer la commande\" style=\"float:right;cursor:pointer;\" onclick=\"deladvcmd($envoi[id])\">--></td>
      <td colspan=\"4\"></td>
      <td rowspan=\"$rowspan\" onmouseover=\"document.getElementById('trspt$envoi[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('trspt$envoi[id]').style.visibility='hidden';\"><div id=\"trspt$envoi[id]\" style=\"position:absolute;visibility:hidden;cursor:pointer;\"><img src=\"images/editer.gif\" onclick=\"document.getElementById('selecttrspt$envoi[id]').style.display='list-item';document.getElementById('tdtrspt$envoi[id]').style.display='none';document.getElementById('trspt$envoi[id]').style.display='none';\"></div>Transport<br><select id=\"selecttrspt$envoi[id]\" style=\"display:none;\" onchange=\"document.location='facture.php?action=editer&facturid=$facturid&newtrspt='+this.value+'&efid=$envoi[id]#adv'\">$opt</select><div id=\"tdtrspt$envoi[id]\">$envoi[trspt]</div></td>
      <td rowspan=\"$rowspan\">Tarif<br>".$envoi['tarif']."</td>
      <td rowspan=\"$rowspan\" align=\"center\">Info compta<br><textarea id=\"comment$envoi[id]\" style=\"background:none;\" cols=\"20\" onclick=\"this.style.background='white';document.getElementById('updcom$envoi[id]').style.display='block';\">$envoi[comment]</textarea><a href=\"javascript:updadvcomment($envoi[id]);\" id=\"updcom$envoi[id]\" style=\"display:none;color:black;\">Mettre � jour</a></td>
     </tr>";
    $total+=$envoi['tarif'];

    mysqli_data_seek($r2,0);
    while($lf=$DB_site2->fetch_array($r2)){
      $color1=($lf['indate']>=$today && $lf['indate']<$tomorrow?" style=\"color:red;\"":"");
      $color2=($lf['outdate']>=$today && $lf['outdate']<$tomorrow?" style=\"color:red;\"":"");
      $imperative=($lf['imperatif']==1?"imperative":"maxi");
      $img="";
      $path="../configurations/".$host."/images/produits/br/".$lf['artid'].".jpg";
      if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
      print "<tr style=\"background-color:#99dd99;\">
		  <td>$img</td>
		  <td title=\"$lf[libelle]\">".$lf['qte']." ".$lf['artcode']."</td>
		  <td$color1 onmouseover=\"document.getElementById('fournindate$lf[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('fournindate$lf[id]').style.visibility='hidden';\"><div id=\"fournindate$lf[id]\" style=\"position:absolute;visibility:hidden;cursor:pointer;\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);upddatetype='in';efl='$lf[id]';\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"upddatetype='in';efl='$lf[id]';cal_timestamp=0;updadvdate();\"></div>Date enl�v.<br>".($lf['indate']>0 && $lf['origin_in']!=$lf['indate']?date("d/m/Y",$lf['origin_in'])."<br>=>":"").($lf['indate']>0?date("d/m/Y",$lf['indate']):"-")."</td>
		  <td$color2 onmouseover=\"document.getElementById('fournoutdate$lf[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('fournoutdate$lf[id]').style.visibility='hidden';\"><div id=\"fournoutdate$lf[id]\" style=\"position:absolute;visibility:hidden;cursor:pointer;\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);upddatetype='out';efl='$lf[id]';\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"upddatetype='out';efl='$lf[id]';cal_timestamp=0;updadvdate();\"></div>Date livr. $imperative<br>".($lf['outdate']>0 && $lf['origin_out']!=$lf['outdate']?date("d/m/Y",$lf['origin_out'])."<br>=>":"").($lf['outdate']>0?date("d/m/Y",$lf['outdate']):"-")."</td>
	    </tr>";
    }
    $compat=false;
  }
  
}
     
$req=$DB_site2->query("SELECT * FROM lignefacture lf WHERE factureid=$facturid AND (SELECT COUNT(ligne_facture_id) FROM envoi_fournisseur_ligne WHERE ligne_facture_id=lf.lignefactureid AND type='fourn')=0");
if($DB_site2->num_rows($req)>0) print "<tr style=\"background-color:#FF2222;\"><td rowspan=\"".($DB_site2->num_rows($req)+1)."\" style=\"color:black;\">Articles non command�s<br><a href=\"javascript:tab(1);\">Commander</a></td><td colspan=\"6\"></td></tr>";
while($nocmd=$DB_site2->fetch_array($req)){
    $cmddate=$enlevdate=$livrdate="";
    $cmdf="fournisseur";
    $rc=$DB_site2->query("SELECT * FROM calendrier WHERE type='fourncmd' AND objid=".$nocmd['lignefactureid']);
    $cal=$DB_site2->fetch_array($rc);
    if($cal!=false){
      $cmdf=$cal['txt'];
      if($cal['date']>0) $cmddate=date("d/m/Y",$cal['date']);
      if($cal['enlevement']>0) $enlevdate=date("d/m/Y",$cal['enlevement']);
      if($cal['livraison']>0) $livrdate=date("d/m/Y",$cal['livraison']);
    }
    $img="";
    $path="../configurations/".$host."/images/produits/br/".$nocmd['artid'].".jpg";
    if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
    print "<tr style=\"background-color:#FF9999;\">
	<td>$img</td>
       <td title=\"$nocmd[libelle]\">".$nocmd['qte']." ".$nocmd['artcode']."</td>
	<td colspan=\"5\" style=\"white-space: nowrap;\">
	  <table>
	  <tr><td>Date cde</td><td>Date enl�v.</td><td>Date livr.</td><td>Info</td></tr>
	  <tr>
	    <td><input type=\"text\" name=\"cmddate$nocmd[lignefactureid]\" id=\"cmddate$nocmd[lignefactureid]\" value=\"$cmddate\" onclick=\"cmddate(this.id,'fourncmd',$nocmd[lignefactureid],'date')\" size=\"10\"></td>
	    <td><input type=\"text\" id=\"enl$nocmd[lignefactureid]\" size=\"10\" onclick=\"cmddate(this.id,'fourncmd',$nocmd[lignefactureid],'enlevement')\" value=\"$enlevdate\"></td>
	    <td><input type=\"text\" id=\"liv$nocmd[lignefactureid]\" size=\"10\" onclick=\"cmddate(this.id,'fourncmd',$nocmd[lignefactureid],'livraison')\" value=\"$livrdate\"></td>
	    <td><input type=\"text\" id=\"dcmdf$nocmd[lignefactureid]\" size=\"35\" value=\"$cmdf\" onclick=\"if(this.value=='fournisseur')this.value='';document.getElementById('okdcmdf$nocmd[lignefactureid]').style.display='inline';\"><a href=\"javascript:cmdtxt('fourncmd',$nocmd[lignefactureid],document.getElementById('dcmdf$nocmd[lignefactureid]').value)\" id=\"okdcmdf$nocmd[lignefactureid]\" value=\"OK\" style=\"display:none;\">Mettre � jour</a></td>
	  </tr>
	  </table>
	</td>
     </tr>";
    $compat=false;
}

//compatibilit� anciennes commandes
if($compat){
  $ef=$DB_site2->fetch_array($DB_site2->query("SELECT ROUND(SUM(tarif),2) FROM envoi_fournisseur WHERE type='fourn' AND facturid=$facturid"));
  $total+=$ef[0];
  $req=$DB_site2->query("SELECT * FROM lignefacture lf INNER JOIN envoi_fournisseur_ligne efl ON (lf.lignefactureid=efl.ligne_facture_id) WHERE lf.factureid=$facturid AND efl.type='fourn' AND efl.efid=0");
  if($DB_site2->num_rows($req)>0) print "<tr style=\"background-color:#AAAAAA;\"><td rowspan=\"".($DB_site2->num_rows($req)+1)."\">Articles command�s</td><td colspan=\"4\"></td><td rowspan=\"".($DB_site2->num_rows($req)+1)."\">$ef[0]</td></tr>";
  while($oldcmd=$DB_site2->fetch_array($req)){
    print "<tr><td title=\"$oldcmd[libelle]\">".$oldcmd['artcode']."</td><td>$oldcmd[expediteur] ".date("d/m/Y",$oldcmd['date'])."</td><td></td><td></td></tr>";
  }
}

  //transporteur
$requete=$DB_site2->query("SELECT artcode,lignefactureid FROM lignefacture WHERE factureid=$facturid");
print "<tr><td>&nbsp;</td></tr>
<tr><td colspan=\"8\" style=\"color:white;background-color:#AAA; font-weight:bold;\"><img src=\"images/livraison.png\" width=\"25\" align=\"absmiddle\"> Transporteur</td></tr>";

$compat=true;
$livrcmd="";
$r=$DB_site2->query("SELECT * FROM envoi_fournisseur WHERE type='trspt' AND facturid=$facturid");
while($envoi=$DB_site2->fetch_array($r)){
  $r2=$DB_site2->query("SELECT * FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) WHERE efid=".$envoi['id']." AND efl.type='trspt'");
  $nolivr=false;
  $livrcmd="";
  if($DB_site2->num_rows($r2)>0){
    $lf1=$DB_site2->fetch_array($r2);
    $rowspan=$DB_site2->num_rows($r2)+1;
    if($envoi['destinataire']!="0"){
      if($lf1['indate']!=0){
	if($envoi['enlevement']==0){
	  $nolivr=true;
	  $livrcmd="<input type=\"text\" id=\"datelivrcmd$envoi[id]\" size=\"10\" value=\"Date enl�v.\" onclick=\"efid=$envoi[id];this.value='';calendrier_show(this.id,this.value,false,updenlevcmd);\">";
	}else
	  $livrcmd="Enlev�e le ".date("d/m/Y",$envoi['enlevement'])."<br>";
	
      }
      if(!$nolivr){
	if($envoi['livraison']==0)
	  $livrcmd.="<input type=\"text\" id=\"datelivrcmd$envoi[id]\" size=\"10\" value=\"Date livraison\" onclick=\"efid=$envoi[id];this.value='';calendrier_show(this.id,this.value,false,updlivrcmd);\"><br><a href=\"javascript:efid=$envoi[id];cal_timestamp=-1;updlivrcmd();\" style=\"color:black;\">Livraison annul�e</a>";
	elseif($envoi['livraison']==-1)
	  $livrcmd.="<span style=\"color:black;white-space:nowrap;font-weight:bold;font-size:11px;\">Livraison annul�e</span>";
	else
	  $livrcmd.="Livr�e le ".date("d/m/Y",$envoi['livraison']);
      }
    }
    if($envoi['destinataire']=="0") $envoi['destinataire']="Pas de commande";
    if($envoi['destinataire']=="GLS"){
      if($envoi['trackid']!="")
	$tracking="<a href=\"http://www.gls-group.eu/276-I-PORTAL-WEB/content/GLS/FR01/FR/5004.htm?txtRefNo=".$envoi['trackid']."&txtAction=71000\" target=\"_blank\">".$envoi['trackid']."</a>";
      else
	$tracking="<input type=\"text\" id=\"trackid".$envoi['id']."\" value=\"Tracking\" size=\"10\" onclick=\"this.value=''\"><a href=\"javascript:void(0);\" onclick=\"document.location='facture.php?action=editer&facturid=$facturid&trackid='+document.getElementById('trackid".$envoi['id']."').value+'&efid=".$envoi['id']."';\">OK</a>";
    }
    print "<tr style=\"background-color:#00dd00;\">
      <td rowspan=\"$rowspan\">".$envoi['destinataire']."<br>".$tracking."<br>".date("d/m/Y H:i",$envoi['date'])."<br>".$lf1['expediteur']."<br>$livrcmd<!--<img src=\"images/supprimer.gif\" title=\"supprimer la commande\" style=\"float:right;cursor:pointer;\" onclick=\"deladvcmd($envoi[id])\">--></td>
      <td colspan=\"4\"></td>
      <td rowspan=\"$rowspan\">Transport<br>$envoi[trspt]</td>
      <td rowspan=\"$rowspan\">Tarif<br>".$envoi['tarif']."</td>
      <td rowspan=\"$rowspan\" align=\"center\">Info compta<br><textarea id=\"comment$envoi[id]\" style=\"background:none;\" cols=\"20\" onclick=\"this.style.background='white';document.getElementById('updcom$envoi[id]').style.display='block';\">$envoi[comment]</textarea><a href=\"javascript:updadvcomment($envoi[id]);\" id=\"updcom$envoi[id]\" style=\"display:none;color:black;\">Mettre � jour</a></td>
     </tr>";
    $total+=$envoi['tarif'];

    mysqli_data_seek($r2,0);
    while($lf=$DB_site2->fetch_array($r2)){
      $color1=($lf['indate']>=$today && $lf['indate']<$tomorrow?" style=\"color:red;\"":"");
      $color2=($lf['outdate']>=$today && $lf['outdate']<$tomorrow?" style=\"color:red;\"":"");
      $imperative=($lf['imperatif']==1?"imperative":"maxi");
      $img="";
      $path="../configurations/".$host."/images/produits/br/".$lf['artid'].".jpg";
      if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
      print "<tr style=\"background-color:#99dd99;\">
		  <td>$img</td>
		  <td title=\"$lf[libelle]\">".$lf['qte']." ".$lf['artcode']."</td>
		  <td$color1 onmouseover=\"document.getElementById('trsptindate$lf[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('trsptindate$lf[id]').style.visibility='hidden';\"><div id=\"trsptindate$lf[id]\" style=\"position:absolute;visibility:hidden;cursor:pointer;\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);upddatetype='in';efl='$lf[id]';\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"upddatetype='in';efl='$lf[id]';cal_timestamp=0;updadvdate();\"></div>Date enl�v.<br>".($lf['indate']>0 && $lf['origin_in']!=$lf['indate']?date("d/m/Y",$lf['origin_in'])."<br>=>":"").($lf['indate']>0?date("d/m/Y",$lf['indate']):"-")."</td>
		  <td$color2 onmouseover=\"document.getElementById('trsptoutdate$lf[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('trsptoutdate$lf[id]').style.visibility='hidden';\"><div id=\"trsptoutdate$lf[id]\" style=\"position:absolute;visibility:hidden;cursor:pointer;\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);upddatetype='out';efl='$lf[id]';\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"upddatetype='out';efl='$lf[id]';cal_timestamp=0;updadvdate();\"></div>Date livr. $imperative<br>".($lf['outdate']>0 && $lf['origin_out']!=$lf['outdate']?date("d/m/Y",$lf['origin_out'])."<br>=>":"").($lf['outdate']>0?date("d/m/Y",$lf['outdate']):"-")."</td>
	    </tr>";
    }
    $compat=false;
  }
}
     
$req=$DB_site2->query("SELECT * FROM lignefacture lf WHERE factureid=$facturid AND (SELECT COUNT(ligne_facture_id) FROM envoi_fournisseur_ligne WHERE ligne_facture_id=lf.lignefactureid AND type='trspt')=0");
if($DB_site2->num_rows($req)>0) print "<tr style=\"background-color:#FF2222;\"><td rowspan=\"".($DB_site2->num_rows($req)+1)."\" style=\"color:black;\">Articles non command�s<br><a href=\"javascript:tab(2);\">Commander</a></td><td colspan=\"6\"></td></tr>";
while($nocmd=$DB_site2->fetch_array($req)){
    $cmddate="";
    $cmdf="transporteur";
    $rc=$DB_site2->query("SELECT * FROM calendrier WHERE type='trsptcmd' AND objid=".$nocmd['lignefactureid']);
    $cal=$DB_site2->fetch_array($rc);
    if($cal!=false){
      if($cal['date']>0) $cmddate=$cal['date']!=0?date("d/m/Y",$cal['date']):"";
      $cmdf=$cal['txt'];
    }
    $img="";
    $path="../configurations/".$host."/images/produits/br/".$nocmd['artid'].".jpg";
    if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
    print "<tr style=\"background-color:#FF9999;\">
	<td>$img</td>
       <td title=\"$nocmd[libelle]\">".$nocmd['qte']." ".$nocmd['artcode']."</td>
	<td colspan=\"5\" nowrap>A commander le <input type=\"text\" name=\"cmddate2$nocmd[lignefactureid]\" id=\"cmddate2$nocmd[lignefactureid]\" value=\"$cmddate\" onclick=\"cmddate(this.id,'trsptcmd',$nocmd[lignefactureid],'date')\" size=\"10\"> � <input type=\"text\" id=\"dcmdf2$nocmd[lignefactureid]\" size=\"35\" value=\"$cmdf\" onclick=\"if(this.value=='transporteur')this.value='';document.getElementById('okdcmdf2$nocmd[lignefactureid]').style.display='inline'\"><input type=\"button\" id=\"okdcmdf2$nocmd[lignefactureid]\" value=\"OK\" style=\"display:none;\" onclick=\"cmdtxt('trsptcmd',$nocmd[lignefactureid],document.getElementById('dcmdf2$nocmd[lignefactureid]').value);\"></td>
     </tr>";
    $compat=false;
}

//compatibilit� anciennes commandes
if($compat){
  $ef=$DB_site2->fetch_array($DB_site2->query("SELECT ROUND(SUM(tarif),2) FROM envoi_fournisseur WHERE type='trspt' AND facturid=$facturid"));
  $total+=$ef[0];
  $req=$DB_site2->query("SELECT * FROM lignefacture lf INNER JOIN envoi_fournisseur_ligne efl ON (lf.lignefactureid=efl.ligne_facture_id) WHERE lf.factureid=$facturid AND efl.type='trspt' AND efl.efid=0");
  if($DB_site2->num_rows($req)>0) print "<tr style=\"background-color:#AAAAAA;\"><td rowspan=\"".($DB_site2->num_rows($req)+1)."\">Articles command�s</td><td colspan=\"4\"></td><td rowspan=\"".($DB_site2->num_rows($req)+1)."\">$ef[0]</td></tr>";
  while($oldcmd=$DB_site2->fetch_array($req)){
    print "<tr><td title=\"$oldcmd[libelle]\">".$oldcmd['artcode']."</td><td>$oldcmd[expediteur] ".date("d/m/Y",$oldcmd['date'])."</td><td></td><td></td></tr>";
  }
}


  //installateur
$requete=$DB_site2->query("SELECT artcode,lignefactureid FROM lignefacture WHERE factureid=$facturid");
print "<tr><td>&nbsp;</td></tr>
<tr><td colspan=\"8\" style=\"color:white;background-color:#AAAAAA;font-weight:bold;\"><img src=\"images/carton.png\" width=\"25\" align=\"absmiddle\"> Installateur</td></tr>";

$compat=true;
$livrcmd="";
$r=$DB_site2->query("SELECT * FROM envoi_fournisseur WHERE type='sls' AND facturid=$facturid");
while($envoi=$DB_site2->fetch_array($r)){
  $r2=$DB_site2->query("SELECT * FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) WHERE efid=".$envoi['id']." AND efl.type='sls'");
  $nolivr=false;
  if($DB_site2->num_rows($r2)>0){
    $lf1=$DB_site2->fetch_array($r2);
    $rowspan=$DB_site2->num_rows($r2)+1;
    $livrcmd="";
    if($envoi['destinataire']!="0"){
      /*if($lf1['indate']!=0){
	if($envoi['enlevement']==0){
	  $nolivr=true;
	  $livrcmd="<input type=\"text\" id=\"datelivrcmd$envoi[id]\" size=\"10\" value=\"Date enl�v.\" onclick=\"efid=$envoi[id];this.value='';calendrier_show(this.id,this.value,false,updenlevcmd);\">";
	}else
	  $livrcmd="Enlev�e le ".date("d/m/Y",$envoi['enlevement'])."<br>";
	
      } */
//       if(!$nolivr){
	if($envoi['livraison']==0)
	  $livrcmd.="<input type=\"text\" id=\"datelivrcmd$envoi[id]\" size=\"10\" value=\"Date livraison\" onclick=\"efid=$envoi[id];this.value='';calendrier_show(this.id,this.value,false,updlivrcmd);\"><br><a href=\"javascript:efid=$envoi[id];cal_timestamp=-1;updlivrcmd();\" style=\"color:black;\">Livraison annul�e</a>";
	elseif($envoi['livraison']==-1)
	  $livrcmd.="<span style=\"color:black;white-space:nowrap;font-weight:bold;font-size:11px;\">Livraison annul�e</span>";
	else
	  $livrcmd.="Livr�e le ".date("d/m/Y",$envoi['livraison']);
//       }
    }
    if($envoi['destinataire']=="0") $envoi['destinataire']="Pas de commande";
    print "<tr style=\"background-color:#00dd00;\">
      <td rowspan=\"$rowspan\">".$envoi['destinataire']."<br>".date("d/m/Y H:i",$envoi['date'])."<br>".$lf1['expediteur']."<br>$livrcmd<!--<img src=\"images/supprimer.gif\" title=\"supprimer la commande\" style=\"float:right;cursor:pointer;\" onclick=\"deladvcmd($envoi[id])\">--></td>
      <td colspan=\"4\"></td>
      <td rowspan=\"$rowspan\">Transport<br>$envoi[trspt]</td>
      <td rowspan=\"$rowspan\">Tarif<br>".$envoi['tarif']."</td>
      <td rowspan=\"$rowspan\" align=\"center\">Info compta<br><textarea id=\"comment$envoi[id]\" style=\"background:none;\" cols=\"20\" onclick=\"this.style.background='white';document.getElementById('updcom$envoi[id]').style.display='block';\">$envoi[comment]</textarea><a href=\"javascript:updadvcomment($envoi[id]);\" id=\"updcom$envoi[id]\" style=\"display:none;color:black;\">Mettre � jour</a></td>
     </tr>";
    $total+=$envoi['tarif'];

    mysqli_data_seek($r2,0);
    while($lf=$DB_site2->fetch_array($r2)){
      $color1=($lf['indate']>=$today && $lf['indate']<$tomorrow?" style=\"color:red;\"":"");
      $color2=($lf['outdate']>=$today && $lf['outdate']<$tomorrow?" style=\"color:red;\"":"");
      $imperative=($lf['imperatif']==1?"imperative":"maxi");
      $img="";
      $path="../configurations/".$host."/images/produits/br/".$lf['artid'].".jpg";
      if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
      print "<tr style=\"background-color:#99dd99;\">
		  <td>$img</td>
		  <td title=\"$lf[libelle]\">".$lf['qte']." ".$lf['artcode']."</td>
		  <td$color1 onmouseover=\"document.getElementById('slsindate$lf[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('slsindate$lf[id]').style.visibility='hidden';\"><div id=\"slsindate$lf[id]\" style=\"position:absolute;visibility:hidden;cursor:pointer;\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);upddatetype='in';efl='$lf[id]';\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"upddatetype='in';efl='$lf[id]';cal_timestamp=0;updadvdate();\"></div>Date enl�v.<br>".($lf['indate']>0 && $lf['origin_in']!=$lf['indate']?date("d/m/Y",$lf['origin_in'])."<br>=>":"").($lf['indate']>0?date("d/m/Y",$lf['indate']):"-")."</td>
		  <td$color2 onmouseover=\"document.getElementById('slsoutdate$lf[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('slsoutdate$lf[id]').style.visibility='hidden';\"><div id=\"slsoutdate$lf[id]\" style=\"position:absolute;visibility:hidden;cursor:pointer;\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);upddatetype='out';efl='$lf[id]';\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"upddatetype='out';efl='$lf[id]';cal_timestamp=0;updadvdate();\"></div>Date livr. $imperative<br>".($lf['outdate']>0 && $lf['origin_out']!=$lf['outdate']?date("d/m/Y",$lf['origin_out'])."<br>=>":"").($lf['outdate']>0?date("d/m/Y",$lf['outdate']):"-")."</td>
	    </tr>";
    }
    $compat=false;
  }
}
     
$req=$DB_site2->query("SELECT * FROM lignefacture lf WHERE factureid=$facturid AND (SELECT COUNT(ligne_facture_id) FROM envoi_fournisseur_ligne WHERE ligne_facture_id=lf.lignefactureid AND type='sls')=0");
if($DB_site2->num_rows($req)>0) print "<tr style=\"background-color:#FF2222;\"><td rowspan=\"".($DB_site2->num_rows($req)+1)."\" style=\"color:black;\">Articles non command�s<br><a href=\"javascript:tab(3);\">Commander</a></td><td colspan=\"6\"></td></tr>";
while($nocmd=$DB_site2->fetch_array($req)){
    $cmddate="";
    $cmdf="installateur";
    $rc=$DB_site2->query("SELECT * FROM calendrier WHERE type='slscmd' AND objid=".$nocmd['lignefactureid']);
    $cal=$DB_site2->fetch_array($rc);
    if($cal!=false){
      if($cal['date']>0) $cmddate=date("d/m/Y",$cal['date']);
      $cmdf=$cal['txt'];
    }
    $img="";
    $path="../configurations/".$host."/images/produits/br/".$nocmd['artid'].".jpg";
    if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
    print "<tr style=\"background-color:#FF9999;\">
	<td>$img</td>
       <td title=\"$nocmd[libelle]\">".$nocmd['qte']." ".$nocmd['artcode']."</td>
	<td colspan=\"5\" nowrap>A commander le <input type=\"text\" name=\"cmddate3$nocmd[lignefactureid]\" id=\"cmddate3$nocmd[lignefactureid]\" value=\"$cmddate\" onclick=\"cmddate(this.id,'slscmd',$nocmd[lignefactureid],'date')\" size=\"10\"> � <input type=\"text\" id=\"dcmdf3$nocmd[lignefactureid]\" value=\"$cmdf\" onclick=\"if(this.value=='installateur')this.value='';document.getElementById('okdcmdf3$nocmd[lignefactureid]').style.display='inline';\" size=\"35\"><input type=\"button\" id=\"okdcmdf3$nocmd[lignefactureid]\" style=\"display:none;\" value=\"OK\" onclick=\"cmdtxt('slscmd',$nocmd[lignefactureid],document.getElementById('dcmdf3$nocmd[lignefactureid]').value);\"></td>
     </tr>";
    $compat=false;
}

//compatibilit� anciennes commandes
if($compat){
  $ef=$DB_site2->fetch_array($DB_site2->query("SELECT ROUND(SUM(tarif),2) FROM envoi_fournisseur WHERE type='sls' AND facturid=$facturid"));
  $total+=$ef[0];
  $req=$DB_site2->query("SELECT * FROM lignefacture lf INNER JOIN envoi_fournisseur_ligne efl ON (lf.lignefactureid=efl.ligne_facture_id) WHERE lf.factureid=$facturid AND efl.type='sls' AND efl.efid=0");
  if($DB_site2->num_rows($req)>0) print "<tr style=\"background-color:#AAAAAA;\"><td rowspan=\"".($DB_site2->num_rows($req)+1)."\">Articles command�s</td><td colspan=\"4\"></td><td rowspan=\"".($DB_site2->num_rows($req)+1)."\">$ef[0]</td></tr>";
  while($oldcmd=$DB_site2->fetch_array($req)){
    print "<tr><td title=\"$oldcmd[libelle]\">".$oldcmd['artcode']."</td><td>$oldcmd[expediteur] ".date("d/m/Y",$oldcmd['date'])."</td><td></td><td></td></tr>";
  }
}

print "</table>";
/*
$total_facture_ht=floatval($float_total_facture)/(1+$tauxtva);

if($total_facture_ht>0){
  print "<p>&nbsp;</p><div class=\"advgroup\">
     <div style=\"color:white;background-color:#AAAAAA;font-weight:bold;\">Total</div>
     Total Achat HT : $total �
     <br>Total commande HT : ".round($total_facture_ht,2)." �
     <br>Marge brute HT : ".round($total_facture_ht-$total,2)." �
     <br>Marge : ".round(100*(1-$total/$total_facture_ht),2)." %  
     </div>";
  print "</div>";
}*/

//print "</body>";

function advmarque($advmids,$lignemids){
    foreach($advmids as $mid)
	if(in_array($mid,$lignemids)) return true;

    return false;
}

function fournkey($txt){
    $search=array("�","�","�","�","�"," ",",",";",":","'");
    $replace=array("e","e","e","a","c");
    return strtolower(str_replace($search,$replace,$txt));
}

?>
