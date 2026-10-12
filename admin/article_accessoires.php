<?php

$contenu_onglet.="<style>
#tblacc { border:solid thin black; border-collapse:collapse; }
#tblacc td { border:solid thin #888; }
#tblacc thead { font-weight:bold; }
</style>";

$contenu_onglet.="<script language=\"javascript\">
var results=new Array();
function srcarticle(txt){
	      if(txt.length<3) return;
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
	      results[artid]={'artcode':id,'ref':ref,'libelle':description};
	      color='black';
	      if(active=='0' || active=='2') color='red';
	      var link=\"<a href='javascript:addtblline(\"+artid+\")'>Sélectionner</a>\";
	      if(document.getElementById('acc_'+artid)) link=\"Dans la liste\";
	      html+=\"<tr><td style='border-bottom:solid thin #cccccc;color:\"+color+\";'>\"+id+\"<br>\"+ref+\"</td><td style='border-bottom:solid thin #cccccc;'>\"+description+\"</span></td><td style='border-bottom:solid thin #cccccc;'>\"+link+\"</td></tr>\";
	}
	html+=\"</table>\";
    }else
	html=\"Pas de résultat\";

  document.getElementById('resultlist').innerHTML=html;
  document.getElementById('resultlist').style.display='block';
}

function addtblline(artid){
  document.getElementById('resultlist').style.display='none';
  if(document.getElementById('noacc')) document.getElementById('noacc').style.display='none';
  var tr=document.createElement('tr');
  tr.id=\"tr\"+artid;
  var td0=document.createElement('td');
  td0.innerHTML=\"<img src='../configurations/".$host."/images/produits/br/\"+artid+\".jpg' width='30'>\";
  tr.appendChild(td0);
  var td1=document.createElement('td');
  td1.align='center';
  td1.innerHTML=\"<input type='text' name='qte[\"+artid+\"]' size='1' value='1' onkeyup='validqte(event)'><input type='hidden' name='acc_artid[]' value='\"+artid+\"'>\";
  tr.appendChild(td1);
  var td2=document.createElement('td');
  td2.innerHTML=results[artid].artcode+\"<br><i>\"+results[artid].ref+\"</i>\";
  tr.appendChild(td2);
  var td3=document.createElement('td');
  td3.innerHTML=results[artid].libelle;
  tr.appendChild(td3);
  var td4=document.createElement('td');
  td4.innerHTML=\"<a href='javascript:removetmpacc(\"+artid+\")'><img src='images/supprimer.gif' title='Supprimer'></a>\";
  tr.appendChild(td4);
  document.getElementById('tblacc').appendChild(tr);
}

function removetmpacc(artid){
  var tr=document.getElementById('tr'+artid);
  document.getElementById('tblacc').removeChild(tr);
  var nbline=document.getElementById('tblacc').getElementsByTagName('tr').length;
  if(nbline==2) document.getElementById('noacc').style.display='table-row';
}

function removeacc(id){
  var del=document.createElement('input');
  del.type='hidden';
  del.name='del_acc';
  del.value=id;
  document.getElementsByName('formu')[0].appendChild(del);
  document.getElementsByName('old')[0].value='accessoires';
  document.getElementsByName('action2')[0].value='accessoires';
  document.getElementsByName('formu')[0].submit();
}

function modifqte(id,qte){
//     if(document.getElementsByName('qte_id').length>0)
    document.getElementById('qte_'+id).innerHTML=\"<input type='hidden' name='qte_id' value='\"+id+\"'><input type='text' name='qte' value='\"+qte+\"' size='1' onkeyup='validqte(event)'>\";
}

function validqte(e){
  if(e.keyCode==13){
    document.getElementsByName('old')[0].value='accessoires';
    document.getElementsByName('action2')[0].value='accessoires';
    document.forms.formu.submit();
  }
}

function add_acc(){
  var acc=document.createElement('input');
  acc.type='hidden';
  acc.name='add_acc';
  acc.value=document.getElementsByName('libelle')[0].value;
  document.getElementsByName('formu')[0].appendChild(acc);
  document.getElementsByName('old')[0].value='accessoires';
  document.getElementsByName('action2')[0].value='accessoires';
  document.getElementsByName('formu')[0].submit();
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

<h3>Accessoires inclus avec le produit</h3>";

$contenu_onglet.="<table id=\"tblacc\">
<thead>
<tr><td>Quantité</td><td>Libellé</td></tr>
</thead>";

$requete=$DB_site2->query("SELECT aa.id,aa.qte,aa.libelle FROM article_accessoire aa WHERE aa.artid=".$artid);
if($DB_site2->num_rows($requete)>0){
  while($acc=$DB_site2->fetch_array($requete)){
     $contenu_onglet.="<tr><td id=\"qte_".$acc['id']."\" align=\"center\" style=\"cursor:pointer;\">".$acc['qte']."</td><td>".$template->translate($acc['libelle'])."</td><td><a href=\"javascript:removeacc(".$acc['id'].")\"><img src=\"images/supprimer.gif\" title=\"Supprimer\"></a></td></tr>";
  }
}else
  $contenu_onglet.="<tr id=\"noacc\"><td colspan=\"4\">Cet article n'a pas d'accessoire</td></tr>";

$contenu_onglet.="</table>
<br>
<table>
  <tr><td colspan=\"3\" align=\"center\">Ajouter un accessoire</td></tr>
  <tr><td><input type=\"text\" name=\"qte\" value=\"1\" size=\"1\"></td><td>".selecttranslate("libelle","/article/accessoires_inclus","")."</td><td><input type=\"button\" value=\"OK\" onclick=\"add_acc()\"></td></tr>
  <tr><td colspan=\"2\"><small>Mettre 0 pour les quantités non déterminées</small></td></tr>
</table>
</form>
</div>
";

?>