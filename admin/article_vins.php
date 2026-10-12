<?php


$contenu_onglet.="<script language=\"javascript\">
var results=new Array();
function srcarticle(txt){
	      if(txt.length<3) return;
	      ajaxrequest('marketplace/src.php?txt='+txt,srccb);
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
	      results[artid]={'artcode':id,'ref':ref,'libelle':description};
	      color='black';
	      if(active=='0' || active=='2') color='red';
	      var link=\"<a href='javascript:add_acc(\"+artid+\")'>Sélectionner</a>\";
	      if(document.getElementById('acc_'+artid)) link=\"Dans la liste\";
	      html+=\"<tr><td style='border-bottom:solid thin #cccccc;color:\"+color+\";'>\"+id+\"<br>\"+ref+\"</td><td style='border-bottom:solid thin #cccccc;'>\"+description+\"</span></td><td style='border-bottom:solid thin #cccccc;'>\"+link+\"</td></tr>\";
	}
	html+=\"</table>\";
    }else
	html=\"Pas de résultat\";

  document.getElementById('resultlist').innerHTML=html;
  document.getElementById('resultlist').style.display='block';
}

function add_acc(artid){
    var acc_artid=document.createElement('input');
    acc_artid.type='hidden';
    acc_artid.name='add_artid';
    acc_artid.value=artid;
    document.forms.formu.appendChild(acc_artid);
    clique('vins','vins');
}

function rm_acc(artid){
    var acc_artid=document.createElement('input');
    acc_artid.type='hidden';
    acc_artid.name='rm_artid';
    acc_artid.value=artid;
    document.forms.formu.appendChild(acc_artid);
    clique('vins','vins');
}

</script>";

$contenu_onglet.= "<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
<input type=\"hidden\" name=\"old\" value=\"\">
<input type=\"hidden\" name=\"quitter\" value=\"\">
<input type=\"hidden\" name=\"mq2\" value=\"\">
<input type=\"hidden\" name=\"modif\" value=\"0\">
<input type=\"hidden\" name=\"action2\" value=\"\">
<input type=\"hidden\" name=\"artid\" value=\"$artid\">
<input type=\"hidden\" name=\"catid\" value=\"$catid\">

<h3>Vins conseillés</h3>";



$requete=$DB_site2->query("SELECT a.* FROM vins_associes va INNER JOIN article a ON(a.artid=va.artid_vin) WHERE va.artid=".$artid);
if($DB_site2->num_rows($requete)>0){
  while($acc=$DB_site2->fetch_array($requete)){
     $contenu_onglet.="<div style=\"border:solid 1px #888; width:200px; height:150px; float:left; margin:5px;\"><img src=\"images/supprimer.gif\" title=\"Supprimer\" onclick=\"rm_acc(".$acc['artid'].")\">".vignette($acc)."</div>";
  }
}else
  $contenu_onglet.="Cet article n'a pas de vins associés";

$contenu_onglet.="<div class=\"clear\"></div>
<br>
Ajouter un vin :<input type=\"text\" onkeyup=\"srcarticle(this.value)\">
<div id=\"resultlist\"></div>
</form>
</div>
";

?>