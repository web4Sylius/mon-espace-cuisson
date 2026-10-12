<?php

require "admin_global.php";

$field="gamme";
if(isset($_GET['field'])) $field=$_GET['field'];
$flangue="EN";
if(array_diff($langues,$langue_access)!=array())
    $flangue=$langue_access[0];
if(isset($_GET['flangue'])) $flangue=$_GET['flangue'];

if(isset($_POST['id'])){
    $sql="UPDATE article SET `".$_POST['field'].$_POST['langue']."`='".utf8_decode($_POST['trad'])."' WHERE `".$_POST['field']."FR`='".htmlentities(utf8_decode($_POST['fr']))."'";
    $result=$DB_site2->query($sql);
    header("Cache-Control: no-cache");
    header("Content-type:text/xml");
    print "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>
    <xml>
     <id>".$_POST['id']."</id>
     <result>$result</result>
     <sql>$sql</sql>
    </xml>";
    exit;
}

include "./header.php";
include "./menu.php";

$sqlnt="";
if($_GET['ntrad']==1) $sqlnt=" AND TRIM(`".$field.$flangue."`)=''";
$sql="SELECT artid,artcode,COUNT(*) AS nb,".$field."FR,".$field.$flangue." FROM article WHERE active IN ('0','1') AND TRIM(libelleFR)!=''$sqlnt GROUP BY ".$field."FR";

print "<style>
.tdFR { background-color:#c0f1ff; width:200px; }
.tdEN { background-color:#ffc0cf; width:200px; }
.tdSP { background-color:#ffd9c0; width:200px; }
.tradok { color:green; font-weight:bold; }
.tradok img { width:20px; }
.traderr { color:red; }
</style>";

print <<<HTML
  <script language="javascript">
  function updfield(id,langue){
    if(!document.getElementsByName(langue+'_'+id)) return;

    var post=new Array();
    post['fr']=document.getElementsByName('FR_'+id)[0].value;
    post['trad']=document.getElementsByName(langue+'_'+id)[0].value;
    post['langue']=langue;
    post['id']=id;
    post['field']="$field";
    
    document.getElementById('result_'+id).innerHTML="<img src='../images/templates/wait.gif' width='20'>";
    
    ajaxrequest("gammes.php",updcb,post);
  }
  
  function updcb(httprequest){
    var xml=httprequest.responseXML.documentElement;
    var id=xml.getElementsByTagName('id')[0].firstChild.nodeValue;
    var result=xml.getElementsByTagName('result')[0].firstChild.nodeValue;
    document.getElementById('result_'+id).innerHTML=(result==1?"<span class=\"tradok\"><img src=\"images/coche_verte.svg\">OK</span>":"<span class=\"traderr\">Erreur</span>");
  }
  </script>
HTML;

print "<h3>Traduction $field</h3>";

$lopt="";
foreach($langues as $l) if($l!="FR" && in_array($l,$langue_access)) $lopt.="<option value=\"$l\"".($l==$flangue?" selected":"").">$l</option>";
print "<p>
  Langue à traduire : <select name=\"flangue\" onchange=\"document.location='gammes.php?field=$field&ntrad=".(intval(isset($_GET['ntrad'])))."&flangue='+this.value\">$lopt</select>
  <input type=\"checkbox\" name=\"ntrad\" value=\"1\"".($_GET['ntrad']==1?" checked":"")." onclick=\"document.location='gammes.php?field=$field&ntrad='+Number(this.checked)+'&flangue=$flangue'\"> Non traduits seulement
</p>";

print "
<table>
<tr><td>Nb</td><td width=\"250\">FR</td><td>$flangue</td></tr>";

$req=$DB_site2->query($sql);
while($gamme=$DB_site2->fetch_array($req)){
    $id=uniqid();
    $col1=$gamme['nb']>1?$gamme['nb']." références":"<a href=\"ajout_produit.php?artid=".$gamme['artid']."&prec=modif\" target=\"_blank\">".$gamme['artcode']."</a>";
    
    print "<tr>
      <td>".$col1."</td>
      <td class=\"tdFR\"><input type=\"hidden\" name=\"FR_$id\" value=\"".$gamme[$field.'FR']."\">".$gamme[$field.'FR']."</td>
      <td class=\"td".$flangue."\"><input type=\"text\" name=\"".$flangue."_".$id."\" value=\"".$gamme[$field.$flangue]."\" size=\"80\" onblur=\"updfield('$id','$flangue')\"></td>
      <td id=\"result_$id\"></td>";

    print "</tr>";
}

print "</table>";

include "./footer.php";

?>