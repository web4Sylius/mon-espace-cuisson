<?php

require "admin_global.php";

include "./header.php";
include "./menu.php";

if(isset($_POST['cdcid'])){
    $cdc=$DB_site2->query_first("SELECT id FROM cdc_index WHERE id=".$_POST['cdcid']." AND site='".$_GET['version']."' AND site_code='".$_GET['site_code']."'");
    if($cdc['id']==$_POST['cdcid'])
	$DB_site2->query("UPDATE cdc_index SET artid=".$_POST['artid']." WHERE id=".$_POST['cdcid']." AND site='".$_GET['version']."' AND site_code='".$_GET['site_code']."'");
    else
	$DB_site2->query("INSERT INTO cdc_index SET artid=".$_POST['artid'].", id=".$_POST['cdcid'].", site='".$_GET['version']."', site_code='".$_GET['site_code']."'");
}

echo "
<style>
#indextable td { width:200px; height:150px; border:solid thin #888888; }
#modif { display:none; }
</style>
<script language=\"javascript\">
  var site_code=\"".$_GET['site_code']."\";
  function selectcdc(id){
      for(i=1;i<=4;i++) document.getElementById('cdc'+i).style.borderColor='#888888';
      document.getElementById('cdc'+id).style.borderColor='red';
      document.getElementsByName('cdcid')[0].value=id;
      document.getElementById('modif').style.display='block';
  }

  function srcartid(src){
               if(src.length<=3) return;
               var url=\"marketplace/src.php?txt=\"+src+\"&active=1&site_code=\"+document.getElementsByName('site_code')[0].value;
               ajaxrequest(url,srcCallback);
		document.getElementById('results').innerHTML='<img src=\"/configurations/".$host."/images/templates/ajax_wait.gif\">';
  }

  function srcCallback(httprequest){
               var xml=httprequest.responseXML.documentElement;
               var html='';
	   
	       var vistxt=new Array();
	       vistxt[0]='<br><span style=\"color:red;\">Invisible</span>';
	       vistxt[1]='<br>Visible';
               if(xml.getElementsByTagName('prod')[0]){
                    html+='<table width=\"100%\" style=\"border:solid thin #808080;\" class=\"txt\"><tr>';
                    for(i=0;i<xml.getElementsByTagName('prod').length;i++){
                         var description=xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
                         var id=xml.getElementsByTagName('prod')[i].getAttribute('id');
                         var artid=xml.getElementsByTagName('prod')[i].getAttribute('artid');
			 var actif=xml.getElementsByTagName('prod')[i].getAttribute('active_'+site_code);
			 var stock=xml.getElementsByTagName('prod')[i].getAttribute('stock');
			 var marque=xml.getElementsByTagName('prod')[i].getAttribute('marque');
			 var ref=xml.getElementsByTagName('prod')[i].getAttribute('ref');
			 var lot=xml.getElementsByTagName('prod')[i].getAttribute('lot');
                         if(actif<2) html+='<tr id=\"tr_'+artid+'\"><td><img src=\"/configurations/".$host."/images/produits/br/'+artid+'.jpg\" width=\"80\"></td><td style=\"border-bottom:solid thin #cccccc;\">'+marque+'<br>'+id+'<br>'+ref+'<br>'+vistxt[actif]+'</td><td style=\"border-bottom:solid thin #cccccc;\">'+description+'<td style=\"border-bottom:solid thin #cccccc;\"><a href=\"javascript:selectprod('+artid+')\">Sélectionner</a></td></tr>';
                    }
                    html+='</table>';
               }else
                   html='Pas de résultat';

               document.getElementById('results').innerHTML=html;
  }

  function selectprod(artid){
      document.getElementsByName('artid')[0].value=artid;
      document.forms.modifcdc.submit();
  }
</script>

<h1>Coup de coeur accueil</h1>";

print "<form name=\"filter\" method=\"get\">
Site <select name=\"site_code\" onchange=\"document.forms.filter.submit()\">
<option></option>";
foreach($sites as $site_code=>$site) print "<option value=\"".$site_code."\"".($_GET['site_code']==$site_code?" selected":"").">".$site_code."</option>";
print "</select> version : <select name=\"version\" onchange=\"document.forms.filter.submit()\">
  <option value=\"part\"".($_GET['version']=="part"?" selected":"").">Particulier</option>
  <option value=\"pro\"".($_GET['version']=="pro"?" selected":"").">Pro</option>
</select>
</form>";

if(isset($_GET['version']) && isset($_GET['site_code'])){
    $requete=$DB_site2->query("SELECT a.artcode,a.artid,a_s.prix,a.libelleFR,cdc_index.id,a_s.active FROM cdc_index INNER JOIN article a USING(artid) LEFT JOIN article_site a_s USING(artid) WHERE cdc_index.site='".$_GET['version']."' AND cdc_index.site_code='".$_GET['site_code']."' AND a_s.site_code='".$_GET['site_code']."' ORDER BY cdc_index.id");
    print "<table id=\"indextable\"><tr>";
    $art=array_fill(0,4,"");
    while($cdc=$DB_site2->fetch_array($requete)){
	$art[$cdc['id']]=$cdc;
    }

    $c=0;
    $devise_site=$devises->symbole($sites[$_GET['site_code']]['devise']);
    for($i=1;$i<=4;$i++){
	$cdc=$art[$i];
	print "<td id=\"cdc".$i."\" onclick=\"selectcdc(".$i.")\">".vignette($cdc,$devise_site)."</td>";
	$c++;
	if($c==4){ print "</tr><tr>"; $c=0; }
    }
    print "</tr>
    </table>";
}

print "<div id=\"modif\">
<form name=\"modifcdc\" method=\"post\">
<input type=\"hidden\" name=\"cdcid\">
<input type=\"hidden\" name=\"artid\">
Changer la référence : <input type=\"text\" onkeyup=\"srcartid(this.value)\">
<div id=\"results\"></div>
</form>
</div>";



include "./footer.php";

?>