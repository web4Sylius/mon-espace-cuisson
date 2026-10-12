<?php

include("admin_global.php");

include "./header.php";
include "./menu.php";

$site_code=isset($_GET['site_code'])?$_GET['site_code']:"base";
$sitelangues=$sites[$site_code]['hosts'];

if(isset($_POST['encart'])){
    foreach($_POST['typeid'] as $typeid){
	foreach($_POST['langue'] as $l){
	    $enc=$DB_site2->query_first("SELECT * FROM encarts WHERE type='".$_POST['type']."' AND typeid=".$typeid." AND site_code='".$_GET['site_code']."' AND langue='".$l."' AND position=".$_POST['position']);
	    if($enc=="")
		$sql="INSERT INTO encarts SET type='".mysqli_real_escape_string($DB_site2->link_id,$_POST['type'])."', typeid=".$typeid.", site_code='".$_GET['site_code']."', langue='".$l."', position=".intval($_POST['position']).", encart='".mysqli_real_escape_string($DB_site2->link_id,$_POST['encart'])."'";
	    else
		$sql="UPDATE encarts SET encart='".mysqli_real_escape_string($DB_site2->link_id,$_POST['encart'])."' WHERE id=".$enc['id'];
	    $DB_site2->query($sql);
	}
    }
}

$encarts=array();
$enctemplate=new template("FR",$DB_site2);
foreach($sitelangues as $l){
    $enctemplate->changesite($site_code,$l);
    $listtemp=$enctemplate->searchtemplate("^access.+");
    foreach($listtemp as $name=>$html){
	if(preg_match("/<p class=\"intitule\"[^>]*>(.+)<\/p>/",$html,$m)){
	    $intitule=substr(ucfirst(strtolower(strip_tags(str_replace("<br>"," ",$m[1])))),0,40);
	    $encarts[$l][$intitule]=$name;
	}
    }
}



$nbppage=4;
$listpos="<option></option>";
for($i=0;$i<$nbppage;$i++) $listpos.="<option value=\"".$i."\">".($i+1)."</option>";

foreach($sitelangues as $l)
    $listlangues.=" <input type=\"checkbox\" id=\"ggrouplangue[".$l."]\" value=\"".$l."\" onclick=\"filtertemplate('group')\">".$l;


echo "<h1>Encarts</h1>";

print "<p>Site : <select onchange=\"document.location='encarts.php?site_code='+this.value;\">";
foreach($sites as $code=>$site)
    print "<option value=\"".$code."\"".($code==$site_code?" selected":"").">".$code."</option>";
print "</select></p>";


if(isset($_POST['scroll'])) 
  $jsscroll="window.addEventListener('load',function(){
    window.scrollTo(0,".$_POST['scroll'].");
  },false);";

$jslangues="var langues=new Array('".implode("','",$sitelangues)."');";
  
print <<<HTML
<ul>
    <li><a href="encarts.php?site_code=$site_code&typepage=categorie">Catégories</a></li>
    <li><a href="encarts.php?site_code=$site_code&typepage=marque">Marques</a></li>
    <li><a href="encarts.php?site_code=$site_code&typepage=instit">Institutionnelles</a></li>
    <li><a href="encarts.php?site_code=$site_code&typepage=conseil">Conseils</a></li>
    <li><a href="encarts.php?site_code=$site_code&typepage=promos">Promos</a></li>
    <li><a href="encarts.php?site_code=$site_code&typepage=idkdos">Idées cadeaux</a></li>
    <li><a href="encarts.php?site_code=$site_code&typepage=landing">Landing pages</a></li>
</ul>

<form name="upd" method="post">
    <input type="hidden" name="type" value="$_GET[typepage]">
    <input type="hidden" name="encart">
    <input type="hidden" name="position">
    <input type="hidden" name="scroll">
</form>

<script type="text/javascript">
    function update_encart(typeid,langue,encart,position){
	document.getElementsByName('encart')[0].value=encart;
	document.getElementsByName('position')[0].value=position;
	if(Array.isArray(typeid)){
	    for(i=0;i<typeid.length;i++)
		addinput("typeid",typeid[i]);
	}else
	    addinput("typeid",typeid);
	    
	if(Array.isArray(langue)){
	    for(i=0;i<langue.length;i++)
		addinput("langue",langue[i]);
	}else
	    addinput("langue",langue);
	
	document.getElementsByName("scroll")[0].value=document.body.scrollTop;
	document.forms.upd.submit();
    }
    
    function addinput(name,value){
	var inp=document.createElement("input");
	inp.type="hidden";
	inp.name=name+"[]";
	inp.value=value;
	document.forms.upd.appendChild(inp);
    }
    
    function checkall(group,state){
	var inputs=getInputsByRegEx("page"+group+"\[[0-9]+\]");
	for(i=0;i<inputs.length;i++){
	    inputs[i].checked=state;
	}
    }
    
    function group_upd(group){
	var typeid=new Array();
	var inputs=getInputsByRegEx("page"+group+"\[[0-9]+\]");
	for(i=0;i<inputs.length;i++){
	    if(inputs[i].checked){
		typeid.push(inputs[i].value);
	    }
	}
	if(typeid.length==0){
	    alert("Aucune page n'est sélectionnée");
	    return;
	}
	
	var langue=new Array();
	var inputs=getInputsByRegEx("g"+group+"langue\[[A-Z]+\]");
	
	for(i=0;i<inputs.length;i++){
	    if(inputs[i].checked){
		langue.push(inputs[i].value);
	    }
	}
	if(langue.length==0){
	    alert("Aucune langue n'est sélectionnée");
	    return;
	}
	
	var position=document.getElementById("g"+group+"pos").value;
	if(position==""){
	    alert("Aucune position n'est sélectionnée");
	    return;
	}
	
	var encart=document.getElementById("g"+group+"encart").value;
	/*if(encart==""){
	    alert("Aucun encart n'est sélectionné");
	    return;
	}*/
	
	update_encart(typeid,langue,encart,position);
    }
    
    function getInputsByRegEx(regex){
	var ret=new Array();
	var inputs=document.getElementsByTagName("input");
	var re=new RegExp(regex);
	for(i=0;i<inputs.length;i++){
	    if(re.test(inputs[i].id)) ret.push(inputs[i]);
	}
	return ret;
    }
    
    function filtertemplate(group){
	var selectencart=document.getElementById("g"+group+"encart");
	var options=selectencart.options;

	var sellang=new Array();
	for(i=0;i<langues.length;i++){
	    var chk=document.getElementById("g"+group+"langue["+langues[i]+"]");
	    if(chk.checked) sellang.push(langues[i]);
	}

	for(x=0;x<options.length;x++){
	    if(options[x].value!="" && options[x].dataset.lang){
		
		//options[x].style.display="none";
		var m=options[x].dataset.lang.match("("+sellang.join("|")+")","g");
		if(m && m.length==sellang.length) options[x].style.display="";
	    }
	    
	}
    }
    
    $jsscroll
    $jslangues
</script>
HTML;

if(isset($_GET['typepage'])){
    $enclist=array();
    $req=$DB_site2->query("SELECT * FROM encarts WHERE type='".$_GET['typepage']."' AND site_code='".$site_code."'");
    while($enc=$DB_site2->fetch_array($req)){
	$enclist[$enc['typeid']][$enc['langue']][$enc['position']]=$enc['encart'];
    }

    switch($_GET['typepage']){
	case "categorie":
	print "<h2>Catégories</h2>";
	
	$reqmenus=$DB_site2->query("SELECT idmenu,libelleFR FROM menus ORDER BY position");
	while($menu=$DB_site2->fetch_array($reqmenus)){
	    $idmenu=$menu['idmenu'];
	
	    print "<h3>".$menu['libelleFR']."</h3>
	    <table class=\"table_modules_boutiques\">
		    <tr>
		      <td></td>";
	
	    foreach($sitelangues as $l)
		    print "<td>$l</td>";
	    print "</tr>";
	    
	    $reqcat=$DB_site2->query("SELECT catid,libelleFR FROM categorie WHERE menu_appartenance=".$idmenu." ORDER BY position");
	    while($cat=$DB_site2->fetch_array($reqcat)){
		print "<tr>
		   <td><input type=\"checkbox\" id=\"page".$idmenu."[".$cat['catid']."]\" value=\"".$cat['catid']."\"> ".$cat['libelleFR']."</td>";
		   
		foreach($sitelangues as $l){
		    print "<td>";
		    for($i=0;$i<$nbppage;$i++){
			$opts=getopts($l,$enclist[$cat['catid']][$l][$i]);
			print (1+$i)."&nbsp;<select onchange=\"update_encart(".$cat['catid'].",'".$l."',this.value,".$i.");\">$opts</select><br>";
		    }
		    print "</td>";    
		}  
	    }
	    $groupopts=getallopts();

	    $chklangue=str_replace("group",$idmenu,$listlangues);
	    print <<<HTML
		<tr>
		    <td colspan="3"><a href="javascript:checkall('$idmenu',true)">Tout cocher</a> / <a href="javascript:checkall('$idmenu',false)">décocher</a> Langue :$chklangue Position : <select id="g${idmenu}pos">$listpos</select> Encart : <select id="g${idmenu}encart">$groupopts</select> <input type="button" onclick="group_upd('$idmenu')" value="OK"></td>
		</tr>
		</table>
HTML;
	}
	break;
	
	case "marque":
	print "<h2>Marques</h2>";
	
	print "<table class=\"table_modules_boutiques\">
	<tr>
	      <td></td>";
	foreach($sitelangues as $l)
	      print "<td>$l</td>";
	print "</tr>
	<tr>
	      <td>Marques</td>";
	foreach($sitelangues as $l){
	      print "<td>";
	      for($i=0;$i<$nbppage;$i++){
		  $opts=getopts($l,$enclist[0][$l][$i]);
		  print (1+$i)."&nbsp;<select onchange=\"update_encart(0,'".$l."',this.value,".$i.");\">$opts</select><br>";
	      }
	      print "</td>";    
	}
	print "</tr>
	</table><br>"; 
	
	$marques=$DB_site2->query("SELECT * from marque order by libelle");
	print "<table class=\"table_modules_boutiques\">
	<tr>
	      <td></td>";
	foreach($sitelangues as $l)
	      print "<td>$l</td>";
	print "</tr>";
	  
	while ($marque=$DB_site2->fetch_array($marques)){
	    print "<tr>
	      <td><input type=\"checkbox\" id=\"pagemarque[".$marque['marqueid']."]\" value=\"".$marque['marqueid']."\"> ".$marque['libelle']."</td>";
	      foreach($sitelangues as $l){
		    print "<td>";
		    for($i=0;$i<$nbppage;$i++){
			$opts=getopts($l,$enclist[$marque['marqueid']][$l][$i]);
			print (1+$i)."&nbsp;<select onchange=\"update_encart(".$marque['marqueid'].",'".$l."',this.value,".$i.");\">$opts</select><br>";
		    }
		    print "</td>";    
	      }
	    print "</tr>";
	}
	
	$groupopts=getallopts();
	$chklangue=str_replace("group","marque",$listlangues);
	print <<<HTML
	    <tr>
		<td colspan="3"><a href="javascript:checkall('marque',true)">Tout cocher</a> / <a href="javascript:checkall('marque',false)">décocher</a> Langue :$chklangue Position : <select id="gmarquepos">$listpos</select> Encart : <select id="gmarqueencart">$groupopts</select> <input type="button" onclick="group_upd('marque')" value="OK"></td>
	    </tr>
	    </table>
HTML;
	break;
	
	case "instit":
	print "<h2>Pages institutionnelles</h2>";
	$menus=array(1=>"Mon Espace Cuisson",2=>"Nos marques");
	
	$num_menu = 1;
	while ($num_menu<3){
	    //if($num_menu==2) $num_menu++;
	    $rq_institutionnel = $DB_site2->query( "SELECT * FROM institutionnel WHERE menu = '$num_menu' AND site_code='".$site_code."' ORDER BY position" );
	    echo "<h3>Menu $num_menu ".$menus[$num_menu]."</h3>
		    <table class=\"table_modules_boutiques\">
		    <tr>
		      <td></td>";
		    
	    foreach($sitelangues as $l)
		    print "<td>$l</td>";
	    print "</tr>";
	    
	    while  ($rs_institutionnel=$DB_site2->fetch_array($rq_institutionnel)) {
		echo "<tr>
		    <td style=\"".($rs_institutionnel[active] == "0"?"color:red;":"")."\"><input type=\"checkbox\" id=\"page".$num_menu."[".$rs_institutionnel['institutionnelid']."]\" value=\"".$rs_institutionnel['institutionnelid']."\"> $rs_institutionnel[$libelle]</td>";
		foreach($sitelangues as $l){
		    print "<td>";
		    for($i=0;$i<$nbppage;$i++){
			$opts=getopts($l,$enclist[$rs_institutionnel['institutionnelid']][$l][$i]);
			print (1+$i)."&nbsp;<select onchange=\"update_encart(".$rs_institutionnel['institutionnelid'].",'".$l."',this.value,".$i.");\">$opts</select><br>";
		    }
		    print "</td>";    
		}
		print "</tr>";
	    }
	    
	    $groupopts=getallopts();
	    $chklangue=str_replace("group",$num_menu,$listlangues);
	    print <<<HTML
	    <tr>
		<td colspan="3"><a href="javascript:checkall('$num_menu',true)">Tout cocher</a> / <a href="javascript:checkall('$num_menu',false)">décocher</a> Langue :$chklangue Position : <select id="g${num_menu}pos">$listpos</select> Encart : <select id="g${num_menu}encart">$groupopts</select> <input type="button" onclick="group_upd('$num_menu')" value="OK"></td>
	    </tr>
	    </table>
HTML;
	    $num_menu ++;
	}
	
	break;
	
	case "conseil":
	print "<h2>Pages conseils</h2>
	<table class=\"table_modules_boutiques\">
		    <tr>
		      <td></td>";
	foreach($sitelangues as $l)
		    print "<td>$l</td>";
	    print "</tr>";
		      
	$rq_conseil = $DB_site2->query( "SELECT * FROM conseil_part WHERE site_code='base' ORDER BY position" );
	while($conseil=$DB_site2->fetch_array($rq_conseil)){
	    echo "<tr>
		<td><input type=\"checkbox\" id=\"pageconseil[".$conseil['conseilid']."]\" value=\"".$conseil['conseilid']."\"> ".$conseil['libelleFR']."</td>";
	    foreach($sitelangues as $l){
		print "<td>";
		for($i=0;$i<$nbppage;$i++){
		    $opts=getopts($l,$enclist[$conseil['conseilid']][$l][$i]);
		    print (1+$i)."&nbsp;<select onchange=\"update_encart(".$conseil['conseilid'].",'".$l."',this.value,".$i.");\">$opts</select><br>";
		}
		print "</td>";    
	    }
	    print "</tr>";
	}
	$groupopts=getallopts();
	$chklangue=str_replace("group","conseil",$listlangues);
	print <<<HTML
	    <tr>
		<td colspan="3"><a href="javascript:checkall('conseil',true)">Tout cocher</a> / <a href="javascript:checkall('conseil',false)">décocher</a> Langue :$chklangue Position : <select id="gconseilpos">$listpos</select> Encart : <select id="gconseilencart">$groupopts</select> <input type="button" onclick="group_upd('conseil')" value="OK"></td>
	    </tr>
	    </table>
HTML;
	
	break;
	
	case "promos":
	print "<h2>Promos</h2>
	<table class=\"table_modules_boutiques\">
		    <tr>
		      <td></td>";
	foreach($sitelangues as $l)
		    print "<td>$l</td>";
	print "</tr>
	<tr>
	    <td></td>";
	   
	foreach($sitelangues as $l){
	    print "<td>";
	    for($i=0;$i<$nbppage;$i++){
		$opts=getopts($l,$enclist[0][$l][$i]);
		print (1+$i)."&nbsp;<select onchange=\"update_encart(0,'".$l."',this.value,".$i.");\">$opts</select><br>";
	    }
	    print "</td>";    
	}
	    
	print "</tr>
	</table>";
	break;
	
	case "idkdos":
	print "<h2>Idées cadeaux</h2>
	<table class=\"table_modules_boutiques\">
		    <tr>
		      <td></td>";
	foreach($sitelangues as $l)
		    print "<td>$l</td>";
	print "</tr>
	<tr>
	    <td></td>";
	   
	foreach($sitelangues as $l){
	    print "<td>";
	    for($i=0;$i<$nbppage;$i++){
		$opts=getopts($l,$enclist[0][$l][$i]);
		print (1+$i)."&nbsp;<select onchange=\"update_encart(0,'".$l."',this.value,".$i.");\">$opts</select><br>";
	    }
	    print "</td>";    
	}
	    
	print "</tr>
	</table>";
	break;
	
	case "landing":
	print "<h2>Landing pages</h2>";
	
	$landing=$DB_site2->query_first("SELECT id,libelleFR FROM landing WHERE type='index'");
	print "<h3>Index</h3>
	<table class=\"table_modules_boutiques\">
		    <tr>
		      <td></td>";
	foreach($sitelangues as $l)
		    print "<td>$l</td>";
	print "</tr>
	<tr>
	    <td>".$landing['libelleFR']."</td>";
	    
	foreach($sitelangues as $l){
	    print "<td>";
	    for($i=0;$i<$nbppage;$i++){
		$opts=getopts($l,$enclist[$landing['id']][$l][$i]);
		print (1+$i)."&nbsp;<select onchange=\"update_encart(".$landing['id'].",'".$l."',this.value,".$i.");\">$opts</select><br>";
	    }
	    print "</td>";    
	}
	    
	print "</tr>
	</table>";
	
	$req=$DB_site2->query("SELECT id,libelleFR FROM landing WHERE type='marque'");
	print "<h3>Marques</h3>
	<table class=\"table_modules_boutiques\">
		    <tr>
		      <td></td>";
	foreach($sitelangues as $l)
		    print "<td>$l</td>";
	print "</tr>";
	
	while($landing=$DB_site2->fetch_array($req)){
	    print "<tr>
	    <td><input type=\"checkbox\" id=\"pagemarque[".$landing['id']."]\" value=\"".$landing['id']."\"> ".$landing['libelleFR']."</td>";
	    
	    foreach($sitelangues as $l){
		print "<td>";
		for($i=0;$i<$nbppage;$i++){
		    $opts=getopts($l,$enclist[$landing['id']][$l][$i]);
		    print (1+$i)."&nbsp;<select onchange=\"update_encart(".$landing['id'].",'".$l."',this.value,".$i.");\">$opts</select><br>";
		}
		print "</td>";    
	    }
		
	    print "</tr>";
	}
	
	    
	$groupopts=getallopts();
	$chklangue=str_replace("group","marque",$listlangues);
	print <<<HTML
	    <tr>
		<td colspan="3"><a href="javascript:checkall('marque',true)">Tout cocher</a> / <a href="javascript:checkall('marque',false)">décocher</a> Langue :$chklangue Position : <select id="gmarquepos">$listpos</select> Encart : <select id="gmarqueencart">$groupopts</select> <input type="button" onclick="group_upd('marque')" value="OK"></td>
	    </tr>
	    </table>
HTML;

	$req=$DB_site2->query("SELECT id,libelleFR FROM landing WHERE type='categorie'");
	print "<h3>Catégories</h3>
	<table class=\"table_modules_boutiques\">
		    <tr>
		      <td></td>";
	foreach($sitelangues as $l)
		    print "<td>$l</td>";
	print "</tr>";
	
	while($landing=$DB_site2->fetch_array($req)){
	    print "<tr>
	    <td><input type=\"checkbox\" id=\"pagecategorie[".$landing['id']."]\" value=\"".$landing['id']."\"> ".$landing['libelleFR']."</td>";
	    
	    foreach($sitelangues as $l){
		print "<td>";
		for($i=0;$i<$nbppage;$i++){
		    $opts=getopts($l,$enclist[$landing['id']][$l][$i]);
		    print (1+$i)."&nbsp;<select onchange=\"update_encart(".$landing['id'].",'".$l."',this.value,".$i.");\">$opts</select><br>";
		}
		print "</td>";    
	    }
		
	    print "</tr>";
	}
	
	$groupopts=getallopts();
	$chklangue=str_replace("group","categorie",$listlangues);
	print <<<HTML
	    <tr>
		<td colspan="3"><a href="javascript:checkall('categorie',true)">Tout cocher</a> / <a href="javascript:checkall('categorie',false)">décocher</a> Langue :$chklangue Position : <select id="gcategoriepos">$listpos</select> Encart : <select id="gcategorieencart">$groupopts</select> <input type="button" onclick="group_upd('categorie')" value="OK"></td>
	    </tr>
	    </table>
HTML;
	
	break;
    }
}

include "./footer.php";

function getopts($langue,$value=null){
    global $encarts;
    
    $opts="<option></option>";
    if(!isset($encarts[$langue])) return "";
    foreach($encarts[$langue] as $intitule=>$name){
	$opts.="<option value=\"".$name."\"".($name==$value?" selected":"").">".$intitule."</option>";
    }
    return $opts;
}

function getallopts(){
    global $encarts;

    $opts=array();
    foreach($encarts as $langue=>$enclangue){
	foreach($enclangue as $intitule=>$name){
	    if(!isset($opts[$name]))
		$opts[$name]=$intitule;
	    $idl[$name][]=$langue;
	}
    }
    
    $return="<option></option>";
    foreach($opts as $name=>$intitule){
	$return.="<option data-lang=\"".implode("_",$idl[$name])."\" value=\"".$name."\">".$intitule."</option>";
    }
    return $return;
}

?>