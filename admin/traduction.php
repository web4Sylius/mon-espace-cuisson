<?php
require "admin_global.php";

include "./header.php";
include "./menu.php";

$filter_langue=array_diff($langues,$langue_access)!=array();

$notranslate=array();
$notranslate=array("/site","/admin");

$nblangues=count($langues);
$jslangue="var langues=new Array(\"".implode("\",\"",$langues)."\");";
$sql="";

if(isset($_POST['path'])){

    foreach($langues as $langue)
    {
        if(in_array($langue,$langue_access))
        {
            $sqlInsert .= ",".$langue."='".mysqli_real_escape_string($DB_site2->link_id,$_POST[$langue])."'";
        } 
    } 
    
    $sql = "SELECT *";
    $sql .= " FROM traduction";
    $sql .= " WHERE path = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['path'])."'";
    $exist = $DB_site2->query_first($sql);

    if($exist['path'] == $_POST['path'])
    {
        $sql = "UPDATE traduction SET ";
        $sql .= substr($sqlInsert,1);
        $sql .= " WHERE path = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['path'])."'";
    }
    else
    {
        $sql = "INSERT INTO traduction SET ";
        $sql .= "path = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['path'])."'";
        $sql .= $sqlInsert;
    } 

    $DB_site2->query($sql);
}

echo "
<style>
.keytable { display:none; border:solid thin black; border-collapse:collapse; width:800px; }
.keytable td { border-bottom:solid thin #CCCCCC; padding:2px; }
.keyfolder { background-color:#CCCCCC; }
#modifdiv { display:none; position:absolute; border:solid thin black; background-color:white; padding:5px; }
#keyinfo { display:none; position:absolute; border:solid thin black; background-color:white; padding:5px; }
.tdFR { background-color:#c0f1ff; width:200px; }
.tdEN { background-color:#ffc0cf; width:200px; }
.tdSP { background-color:#ffd9c0; width:200px; }
</style>

<script language=\"javascript\">
$jslangue
var modifline;
  function showkeys(key){
      if(document.getElementById(key).style.display=='none'){
	  document.getElementById(key).style.display='block';
	  document.getElementById('img'+key).src='images/moins.jpg';
      }else{
	  document.getElementById(key).style.display='none';
	  document.getElementById('img'+key).src='images/plus.jpg';
      }
  }

  function modif(path,line){
      modifline=line;
      if(line==undefined){
	for(i=0;i<langues.length;i++) document.getElementsByName(langues[i])[0].value='';
	document.getElementsByName('path')[0].removeAttribute('readonly');
	document.getElementById('action').innerHTML='Créer';
      }else{
	for(i=0;i<langues.length;i++) document.getElementsByName(langues[i])[0].value=document.getElementById(line+langues[i]).innerHTML;
	document.getElementsByName('path')[0].setAttribute('readonly', 'readonly');
	document.getElementById('action').innerHTML='Modifier';
      }
      document.getElementsByName('path')[0].value=path;
      document.getElementById('modifdiv').style.display='block';
      document.getElementById('modifdiv').style.top=document.body.scrollTop+'px';
  }

  function update(){
      var post={};
      for(i=0;i<langues.length;i++) post[langues[i]]=document.getElementsByName(langues[i])[0].value;
      post['path']=document.getElementsByName('path')[0].value;
      ajaxrequest('traduction.php',update_cb,post);
  }

  function update_cb(httprequest){
      document.getElementById('modifdiv').style.display='none';
      if(modifline!=undefined) for(i=0;i<langues.length;i++) document.getElementById(modifline+langues[i]).innerHTML=document.getElementsByName(langues[i])[0].value;
  }

  function keyinfo(key){
     ajaxrequest('traduction_ajax.php?where='+key,info_cb);
  }
  function info_cb(httprequest){
     document.getElementById('keyinfo').innerHTML='<img src=\"images/supprimer.gif\" onclick=\"document.getElementById(\'keyinfo\').style.display=\'none\';\">'+httprequest.responseText;
     document.getElementById('keyinfo').style.display='block';
     document.getElementById('keyinfo').style.left=MouseX+'px';
     document.getElementById('keyinfo').style.top=MouseY+'px';
  }

  var MouseX=0;
  var MouseY=0;
  document.onmousemove=function(e){
      MouseX=e.pageX;
      MouseY=e.pageY;
  }
</script>

<h1>Traduction</h1>";

$folders=$keys=array();

$req=$DB_site2->query("SELECT * FROM traduction ORDER BY path");
while($trad=$DB_site2->fetch_array($req)){
    $sps=explode("/",$trad['path']);
    $i=0;

    array_shift($sps);
    $c=count($sps);
    $p="";
    $lastf=$folders["/"];
    foreach($sps as $sp){
	if($i<$c-1){
	  $p.="/".$sp;
	  $folders[$p]=1;
	}else{
	  $folders[$p]++;
// 	  $keys[$p][$sp]="<td>".$trad['FR']."</td><td>".$trad['EN']."</td>";
	  foreach($langues as $langue) $keys[$p][$sp].="<td id=\"".md5($p."/".$sp).$langue."\" class=\"td".$langue."\">".$trad[$langue]."</td>";
	}
	$i++;
    }

//     print "<tr><td>$parentpath</td><td nowrap>".$trad['path']."</td><td>".$trad['FR']."</td><td>".$trad['EN']."</td></tr>".$endtable;
}

print "<div id=\"modifdiv\">
  <p><span id=\"action\"></span> <input type=\"text\" name=\"path\" size=\"60\"></p>";
  foreach($langues as $langue) print "<p>".$langue." <textarea name=\"$langue\" cols=\"80\" rows=\"4\"".(in_array($langue,$langue_access)?"":" readonly")."></textarea></p>";
print "<p><input type=\"button\" value=\"OK\" onclick=\"update()\"> <input type=\"button\" value=\"Annuler\" onclick=\"document.getElementById('modifdiv').style.display='none';\"></p>
</div>";

print "<div id=\"keyinfo\"></div>";

print "<table>";

ksort($folders);
foreach($folders as $path=>$qte){
    $elem=explode("/",$path);
    if(count($elem)==2) writekeys($path);
}

print "</table>";

function writekeys($path){
    global $keys;
    global $folders;
    global $langues;
    global $nblangues;
    global $filter_langue;
    global $notranslate;
    $len=strlen($path);
    $uid=uniqid();
    
    if(in_array($path,$notranslate)) return "";
    
    print "<tr><td><img src=\"images/plus.jpg\" id=\"img$uid\" onmousedown=\"showkeys('$uid')\"></td><td colspan=\"".($nblangues+1)."\" class=\"keyfolder\">$path ".($filter_langue?"":"<a href=\"javascript:modif('$path')\">Ajouter</a>")."</td></tr>
    <tr><td></td><td colspan=\"".($nblangues+1)."\"><table id=\"$uid\" class=\"keytable\"".(substr($_GET['open'],0,strlen($path))==$path?" style=\"display:block\"":"").">
    <tr><td colspan=\"2\"></td>";
    foreach($langues as $langue) print "<td class=\"td".$langue."\">".$langue."</td>";
    print "</tr>";

    if(isset($keys[$path])){
      foreach($keys[$path] as $kpath=>$value)
	print "<tr><td nowrap><img src=\"images/interrogation.gif\" onclick=\"keyinfo('$path/$kpath')\"> <img src=\"images/editer.gif\" onclick=\"modif('$path/$kpath','".md5("$path/$kpath")."')\"></td><td>$path/$kpath</td>$value</tr>";
    }

    foreach($folders as $fpath=>$qte){
	if(substr($fpath,0,$len)==$path && substr($fpath,$len,1)=="/" && $fpath!=$path){
	    writekeys($fpath);

	}
    }
    print "</table></td></tr>";
}


include "./footer.php";


?>