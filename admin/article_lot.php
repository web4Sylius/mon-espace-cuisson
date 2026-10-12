<?php

if(isset($_POST['addartid']) && $_POST['addartid']!=""){
    $DB_site2->query("INSERT INTO article_lot SET lotid=".$_POST['artid'].",artid=".$_POST['addartid'].", qte=".intval($_POST['qte']).", prix=".floatval($_POST['addprix']).", paht=".floatval($_POST['addpaht']).", caractvalids='".$_POST['caracts']."'");
}
if(isset($_GET['delartid'])) $DB_site2->query("DELETE FROM article_lot WHERE id=".intval($_GET['delartid']));

if(isset($_GET['facture_diss'])) $DB_site2->query("UPDATE article SET facture_diss=".intval($_GET['facture_diss'])." WHERE artid=".$artid);


$article=$DB_site2->query_first("SELECT prix,paht,ecopartht,prixconseilleht,tauxchoisi,artcode_eurocave,facture_diss FROM article WHERE artid=".$artid);

$js="var artcaract=new Array();\n";
$caracts=$DB_site2->query("SELECT * FROM article_caractval ac  WHERE ac.artid=".$artid);
while($caract=$DB_site2->fetch_array($caracts))
    $js.="artcaract[".$caract['caractvalid']."]=true;\n";

$contenu_onglet.="<script language=\"javascript\">
$js
function srcarticle(txt){
	      document.getElementById('resultlist').style.display='none';
	      document.getElementById('addprod').style.display='none';
	      document.getElementById('submitbtn').style.display='none';
	      if(txt.length<3) return;
	      okartcode=false;
	      ajaxrequest('marketplace/src.php?lot=1&txt='+txt,srccb);
}

function srccb(httprequest){
  var xml=httprequest.responseXML.documentElement;
    var html=\"\";
    if(xml.getElementsByTagName('prod')[0]){
	html+=\"<table width='100%' style='border:solid thin #808080;'>\";
	for(i=0;i<xml.getElementsByTagName('prod').length;i++){
	      var description=xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
	      var id=xml.getElementsByTagName('prod')[i].getAttribute('id');
	      var ref=xml.getElementsByTagName('prod')[i].getAttribute('ref');
	      var price=xml.getElementsByTagName('prod')[i].getAttribute('price');
	      var buyprice=xml.getElementsByTagName('prod')[i].getAttribute('paht');
	      var artid=xml.getElementsByTagName('prod')[i].getAttribute('artid');
	      var active=xml.getElementsByTagName('prod')[i].getAttribute('active');
	      color='black';
	      if(active=='0' || active=='2') color='red';
	      html+=\"<tr><td style='border-bottom:solid thin #cccccc;color:\"+color+\";'>\"+id+\"<br>\"+ref+\"</td><td style='border-bottom:solid thin #cccccc;'>\"+description+\"</span></td><td style='border-bottom:solid thin #cccccc;'><a href=javascript:selectprod('\"+artid+\"','\"+id+\"',\"+price+\",\"+buyprice+\")>Sélectionner</a></td></tr>\";
	}
	html+=\"</table>\";
    }else
	html=\"Pas de résultat\";

  document.getElementById('resultlist').innerHTML=html;
  document.getElementById('resultlist').style.display='block';
}

function selectprod(artid,artcode,prix,paht){
    document.getElementById('addopt').innerHTML='';
    ajaxrequest('marketplace/src.php?artid='+artid,optcb);

    document.getElementById('addartcode').innerHTML=artcode;
    document.getElementsByName('addartid')[0].value=artid;
    document.getElementsByName('addprix')[0].value=prix;
    document.getElementsByName('addpaht')[0].value=paht;
    
    document.getElementById('resultlist').style.display='none';
    document.getElementById('addprod').style.display='block';
    document.getElementById('submitbtn').style.display='block';
}

function optcb(httprequest){
    var xml=httprequest.responseXML.documentElement;
    var caractid=new Array();

    if(xml.getElementsByTagName('caract')[0]){
	
	for(i=0;i<xml.getElementsByTagName('caract').length;i++){
	     var id=xml.getElementsByTagName('caract')[i].getAttribute('id');
	     var name=xml.getElementsByTagName('caract')[i].getAttribute('name');
	     var value=xml.getElementsByTagName('caract')[i].getAttribute('value');
	     if(caractid[name]==undefined) caractid[name]=new Array();
	     caractid[name][id]=value;
	}
	
	var html=\"\";
	var cid=0;
	for(name in caractid){
	    if(caractid.hasOwnProperty(name)){
		cid++;
		noauto=false;
		html+=\"<p><b>\"+name+\"</b></p>\";
	      
		for(id in caractid[name]){
		    color=\"\";
		    if(artcaract[id]!=true){
			color='red';
			noauto=true;
		    }
		    if(caractid[name].hasOwnProperty(id)) html+=\"<input type='radio' name='caract_\"+cid+\"' value='\"+id+\"' onclick='listcaract()'> <span style='color:\"+color+\"'>\"+caractid[name][id]+\"</span><br>\";
		}
	    
		if(!noauto) 
		    html+=\"<input type='radio' name='caract_\"+cid+\"' value='' onclick='listcaract()' checked> Défini par l'article principal<br>\";
		else
		    html+=\"Cette option doit être définie manuellement<br>\";
	    }
	}

    }else
	html=\"\";

    document.getElementById('addopt').innerHTML=html;
}

function listcaract(){
    list=new Array();
    for(i=0;i<document.forms.addform.getElementsByTagName('INPUT').length;i++){
	var elem=document.forms.addform.getElementsByTagName('INPUT')[i];
	if(elem.name.substr(0,7)=='caract_' && elem.checked && elem.value!='') list.push(elem.value);
    }
    document.getElementsByName('caracts')[0].value=list.join(',');
}


function saveprice(){
    document.getElementsByName('old')[0].value='lot';
    document.getElementsByName('action2')[0].value='lot';
    document.forms.formu.action='ajout_produit.php?updateprice=1';
    document.forms.formu.submit();
}
</script>";
$les_opt="";
$contenu_onglet.= "<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
<input type=\"hidden\" name=\"old\" value=\"\">
<input type=\"hidden\" name=\"quitter\" value=\"\">
<input type=\"hidden\" name=\"mq2\" value=\"\">
<input type=\"hidden\" name=\"modif\" value=\"0\">
<input type=\"hidden\" name=\"action2\" value=\"\">
<input type=\"hidden\" name=\"artid\" value=\"$artid\">
<input type=\"hidden\" name=\"catid\" value=\"$catid\">

<h3>Composition de l'article</h3>".$article['artcode_eurocave'];

$requete=$DB_site2->query("SELECT * FROM article_lot WHERE lotid=$artid");
if($DB_site2->num_rows($requete)>0){
  
  $contenu_onglet.="<br><input type=\"checkbox\" name=\"facture_diss\" value=\"1\" onclick=\"document.forms.addform.action='ajout_produit.php?facture_diss='+(this.checked?1:0);document.forms.addform.submit();\" ".($article['facture_diss']==1?"checked":"")."> Dissocier dans la facture<br><br><table>";
  while($lart=$DB_site2->fetch_array($requete)){
      $larticle=$DB_site2->query_first("SELECT artcode,artcode_eurocave,prix,paht,ecopartht,prixconseilleht FROM article WHERE artid=".$lart['artid']);
      $opt="";
      if($lart['caractvalids']!=""){
		$requete2=$DB_site2->query("SELECT cv.libelleFR AS caractval,c.libelleFR AS caract FROM caracteristiquevaleur cv INNER JOIN caracteristique c USING(caractid) WHERE cv.caractvalid IN (".$lart['caractvalids'].")");
		while($caract=$DB_site2->fetch_array($requete2)) $opt.="<td colspan=\"3\"><span style=\"font-weight:bold;\">".$caract['caract'].":".$caract['caractval']."</span></td>";
      }else{
      	$opt .= "<td colspan=\"3\"><span></span></td>";
      }
      $les_opt = "<tr>$opt</tr>";
      $contenu_onglet.= "<tr><td><img src=\"images/supprimer.gif\" style=\"cursor:pointer;\" onclick=\"document.forms.addform.action='ajout_produit.php?delartid=$lart[id]';document.forms.addform.submit();\"></td><td>".$lart['qte']."</td><td>".$larticle['artcode']." (".$larticle['artcode_eurocave'].")</td></tr>";
      $contenu_onglet .= $les_opt;
  }
 
  $contenu_onglet.="</table>";
}else
  $contenu_onglet.= "<p>Cet article n'est pas un lot</p>";

$contenu_onglet.="</form>";

$contenu_onglet.= "<p>Ajouter un article : <input type=\"text\" id=\"src\" onkeyup=\"srcarticle(this.value)\"></p>
<div id=\"resultlist\"></div>
<div id=\"addprod\" style=\"display:none;border:solid thin black;text-align:left;\">
<form name=\"addform\" action=\"ajout_produit.php\" method=\"post\">
<input type=\"hidden\" name=\"old\" value=\"lot\">
<input type=\"hidden\" name=\"action2\" value=\"lot\">
<input type=\"hidden\" name=\"delartid\">
<input type=\"hidden\" name=\"artid\" value=\"$artid\">
<input type=\"hidden\" name=\"addartid\">
<input type=\"hidden\" name=\"caracts\">

<p align=\"center\">Ajouter <span id=\"addartcode\"></span> au lot</p>
Quantité : <input type=\"text\" name=\"qte\" value=\"1\" size=\"2\">
<br>Prix de vente : <input type=\"text\" name=\"addprix\" size=\"5\">&euro;
<br>Prix d'achat : <input type=\"text\" name=\"addpaht\" size=\"5\">&euro;
<div id=\"addopt\" style=\"\"></div>
<input type=\"submit\" id=\"submitbtn\" value=\"Ajouter l'article\" style=\"display:none;\">
</form>
</div>";


?>