<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "spy.class.php";

$spy=new spy($user_info['username'],$user_info['password']);

if(!isset($action)){
$requete=$DB_site2->query("SELECT MAX(timestamp) FROM history");
$history=$DB_site2->fetch_array($requete);
if($history[0]<time()-86400 || isset($_GET['update'])){
    $servertime=$spy->request("lastdata");
    if($servertime!=false){
	if($servertime>$history[0]){
// 	      $data= implode('',gzfile($serverurl."&action=db&fromdate=".intval($history[0])));
	      $spy->request("db","&fromdate=".intval($history[0]));
	      $result=savedata($spy->data());
	      if($result)
		 $serverinfo="Données synchronisées";
	      else
		 $serverinfo="Erreur de synchronisation ".mysqli_error($DB_site2->link_id);
	    
	}else
	  $serverinfo="Données à jour";
    }else
      $serverinfo="Serveur non connecté";

}}

echo "<h1>SPY</h1>";


$optmarques="";
$requete=$DB_site2->query("SELECT marqueid, LCASE(libelle) AS name FROM marque ORDER BY libelle");
while($marque=$DB_site2->fetch_array($requete)){
    $optmarques.="<option value=\"".$marque['marqueid']."\">".$marque['name']."</option>";
}

$optsites="";
$requete=$DB_site2->query("SELECT site FROM history GROUP BY site ORDER BY site");
while($s=$DB_site2->fetch_array($requete)){
    $optsites.="<option value=\"".$s['site']."\">".$s['site']."</option>";
}

print "<p align=\"left\">".$serverinfo."</p>";
print "<fieldset>
<legend>Ajout - Modification</legend>
<div>Rechercher un produit
<form action=\"spy.php?action=rechercher\" method=\"post\">
Référence : <input type=\"text\" name=\"src\"> Site : <select name=\"site\"><option value=\"\">Tous</option>$optsites</select> <input type=\"submit\">
</form>
<p><a href=\"spy.php?action=errors\">Afficher les erreurs</a> &nbsp; <a href=\"spy.php?action=modif\">Ajouter une adresse</a></p>
</fieldset>

<fieldset>
<legend>Export</legend>
<p>Par marque : <select id=\"marqueid\">$optmarques</select> <input type=\"button\" value=\"OK\" onclick=\"document.location='export/export_spy_ooo.php?marqueid='+document.getElementById('marqueid').value;\"></p>
<p>Par site : <select id=\"exsite\">$optsites</select> <input type=\"button\" value=\"OK\" onclick=\"document.location='export/export_spy_ooo.php?site='+document.getElementById('exsite').value;\"></p>
<p><a href=\"export/export_spy_ooo.php\">Exporter le fichier complet</a></p>
</fieldset>";

if(isset($_GET['action'])){
    if($_GET['action']=="update"){
	  $reqaction="update";
	  if($_POST['id']=="") $reqaction="add";
	  $result=$spy->request($reqaction,$_POST);
	  print "<p>$result</p>";
	  $_GET['action']=$_POST['back'];
    }

    if($_GET['action']=="del"){
	  $result=$spy->request("del","&id=".$_GET['id']);
	  print "<p>".utf8_decode($result)."</p>";
	  $_GET['action']=$_GET['back'];
    }

    if($_GET['action']=="rechercher"){
	  $absmin=time()-(86400*365);                 
	  $absmax=mktime(0,0,0);
	  $article=array();
	  $requete=$DB_site2->query("SELECT artid,artcode,artcode_eurocave,prixht,prix FROM article WHERE artcode LIKE '%".$_POST['src']."' OR artcode_eurocave = '".$_POST['src']."'");
	  
	  $art=$DB_site2->fetch_array($requete);
	  $r2=$DB_site2->query("SELECT * FROM history WHERE artcode='".$art['artcode']."' AND timestamp>UNIX_TIMESTAMP(NOW()-INTERVAL 1 YEAR)");
	      
	  if($DB_site2->num_rows($requete)==0)
	    print "<tr><td>Aucun résultat</td></tr>";
	  else{
	    print "<p>Résultats pour <b>".$art['artcode']."</b> (".$art['artcode_eurocave'].") <img src=\"/configurations/".$host."/images/produits/br/".$art['artid'].".jpg\" width=\"50\" align=\"absmiddle\"></p>";

	    $urlsite="";
	    if($_POST['site']!="") $urlsite="&site=".$_POST['site'];
	    $data=$spy->request("src","&artcode=".$art['artcode'].$urlsite);
// 	    $data=implode('',@gzfile($serverurl."&action=src&artcode=".$art['artcode'].$urlsite));
	    $lines=explode("\r\n",$data);
	    
	    $tr=array();
	    foreach($lines as $line){
		$fields=explode("\",\"",$line);
		$fields[6]=str_replace("\"","",$fields[6]);
		if($fields[1]!="") $tr[strtolower($fields[1])]="<tr".($fields[5]!=""?" style=\"background:#FFAAAA;\"":"")."><td>".$fields[1]."</td><td>".$fields[2]."</td><td align=\"right\">".number_format($fields[3],2,',','')."</td><td".($fields[4]<time()-7*86400?" style=\"background:red;\"":"").">".date("d/m/Y H:i",$fields[4])."</td><td><a href=\"spy.php?action=modif&id=".$fields[6]."&artcode=".$_POST['src']."&site=".$fields[1]."&url=".rawurlencode($fields[2])."\"><img src=\"images/editer.gif\"></a></td></tr>";
	    }
	    ksort($tr);
	    print "<table>
	    <thead style=\"color:white; background-color:#ccc; font-weight:bold;\">
	    <tr><td>Site</td><td>URL</td><td>Dernier prix</td><td>Dernier relevé</td><td></td></tr>
	    </thead>";
	    print implode("",$tr);
	    print "</table>";

	    while($history=$DB_site2->fetch_array($r2))
		  $article[$history['site']][$history['timestamp']]=$history['price'];
	    print "
	    <table width=\"100%\">
	    <tr><td style=\"color:white; background-color:#691513; font-weight:bold;\">Ma Cave à Vin ".$art['artcode']."</td></tr>
	    <tr><td style=\"text-align:center; border:solid thin #808080;\">".$art['prix']."</td></tr>
	    </table>
	    <table width=\"100%\">";
	    ksort($article);
	    foreach($article as $site=>$timestamps){
	      $cols=count($timestamps);
	      $data="";
	      ksort($timestamps);
	      foreach($timestamps as $timestamp=>$price){
		    $data.=",".$timestamp.":".$price;
	      }
	      $data=substr($data,1);
	      print "<tr><td><img src=\"ccgraph.php?data=$data&height=150&width=700&ordtype=euro&abstype=ts&title=$site&limit=$art[prix]&absmin=$absmin&absmax=$absmax\"></td></tr>";
	      /*print "<tr><td colspan=\"$cols\" style=\"background-color:#aaffff; font-weight:bold;\">$site</td></tr>
	      <tr>";
	      ksort($timestamps);
	      foreach($timestamps as $timestamp=>$price){
		  print "<td style=\"text-align:center; border:solid thin #808080; color:".($price>$art['prix']?"green":"red")."\">$price<br>".date("d/m/Y",$timestamp)."</td>";
		  $last[$site]=$price;
	      }
	      print "</tr>";*/
	    }
	    print "</table>";
	    if($last!=null) print "<div>Moyenne actuelle : ".round(array_sum($last)/count($last),2)."<br>Min : ".min($last)." max : ".max($last)."</div>";
	  }
    }

    if($_GET['action']=="errors"){
// 	  $data= implode('',@gzfile($serverurl."&action=errors"));
	  $data=$spy->request("errors");
	  $lines=explode("\r\n",$data);
	  $count=0;
	  $i=0;
	  print "<table>
	  <tr><td>Article</td><td>Erreur</td><td>Référence fabricant</td><td>Site</td></tr>";
	  foreach($lines as $line){
		$fields=explode(",",$line);
		if(substr($fields[1],0,4)=="ACI-"){
			$i++;
		    $r=$DB_site2->query("SELECT artcode_eurocave FROM article WHERE artcode LIKE '$fields[1]'");
		    $article=$DB_site2->fetch_array($r);
			$alt=($i%2)+1;
		    echo "<tr class='td_factures_alt".$alt."'>
					<td>".$fields[1]."</td>
					<td nowrap>
						<a href=\"spy.php?action=modif&id=$fields[0]&artcode=$fields[1]&site=$fields[2]&url=".urlencode($fields[3])."&pattern=".urlencode($fields[5])."&back=errors\">Modifier</a>
						<a href=\"spy.php?action=del&id=".$fields[0]."&back=errors\" style=\"color:red;\">Supprimer</a>
					</td>
					<td>".$article[0]."</td>
					<td>".$fields[2]."</td>
					<td>".$fields[4]."</td>
				</tr>";
		    $count++;
		}
	  }
	  
	  print "<tr><td colspan=\"3\">".$count." erreurs</td></tr>
	  </table>";
    }

    if($_GET['action']=="modif"){
	  $submittxt="Ajouter";
	  if(isset($_GET['artcode'])){
	      $requete=$DB_site2->query("SELECT artcode_fabricant FROM article WHERE artcode='".$_GET['artcode']."'");
	      $article=$DB_site2->fetch_array($requete);
	      $supp="<a href=\"#\" onclick=\"if(confirm('Etes vous sûr de supprimer cette adresse ?')) document.location='spy.php?action=del&id=".$_GET['id']."&back=".$_GET['back']."';\" style=\"color:red;\">Supprimer</a>";
	      $submittxt="Modifier";
	  }
	  if(isset($_GET['site'])) $sur="sur ".$_GET['site'];
	  print "<p>&nbsp;</p><form action=\"spy.php?action=update\" method=\"post\">
	  <input type=\"hidden\" name=\"id\" value=\"".$_GET['id']."\">
	  <input type=\"hidden\" name=\"back\" value=\"".$_GET['back']."\">
	  <table>
	  <tr><td colspan=\"2\"><b>Modifier ou ajouter une adresse $sur</b></td></tr>
	  <tr><td>Référence Acilyon</td><td><input type=\"text\" name=\"artcode\" value=\"".$_GET['artcode']."\"> $article[0]</td></tr>
	  <tr><td>URL</td><td><input type=\"text\" name=\"url\" value=\"".$_GET['url']."\" size=\"100\"></td></tr>
	  <tr><td>Résultat SPY<br><a href=\"javascript:void(0);\" onclick=\"preview()\">Tester</a></td><td id=\"spyprice\"></td></tr>
	  <tr><td colspan=\"2\" align=\"right\"><a href=\"javascript:history.back();\">Annuler</a> $supp <input type=\"submit\" value=\"$submittxt\">
	  </table>
	  </form>";
    }

}

print <<<JS
<script language="javascript">
function preview(){
    var url=document.getElementsByName('url')[0].value;
    newwindow=window.open("redirect.php?url="+url,'name','height=900,width=900');
    ajaxrequest("spytest.php?url="+url,spyresult);
}

function spyresult(httprequest){
    document.getElementById('spyprice').innerHTML=httprequest.responseText;
}

</script>
JS;

include "./footer.php";


function savedata($data){
	global $DB_site2;
    $sql=explode(";\n",$data);
    foreach($sql as $line){
	if($line!="") $result=$DB_site2->query($line);
	if($result==false) return false;
    }
    return true;
}

?>