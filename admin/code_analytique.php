<?php

require "admin_global.php";


include "marketplace/order.php";

if(isset($_POST['value'])){
    if($_POST['value']==""){
	$DB_site2->query("DELETE FROM compte_analytique WHERE site='".$_POST['site']."' AND paysid='".$_POST['paysid']."'");
    }else{
	$code=$DB_site2->query_first("SELECT *,p.libelle FROM compte_analytique INNER JOIN pays p USING(paysid) WHERE code='".$_POST['value']."' AND (site!='".$_POST['site']."' OR paysid!='".$_POST['paysid']."')");
	if($code!=""){
	    print "alert('Attention : le code a été enregistré mais il existe aussi pour ".$code['libelle']."');";
	}
	$exist=$DB_site2->query_first("SELECT * FROM compte_analytique WHERE site='".$_POST['site']."' AND paysid='".$_POST['paysid']."'");
	if($exist!="")
	    $sql="UPDATE compte_analytique SET code='".$_POST['value']."' WHERE site='".$_POST['site']."' AND paysid='".$_POST['paysid']."'";
	else
	    $sql="INSERT INTO compte_analytique SET code='".$_POST['value']."', site='".$_POST['site']."', paysid='".$_POST['paysid']."'";
	$result=$DB_site2->query($sql);
	print "document.getElementsByName('c[".$_POST['paysid']."][".$_POST['site']."]')[0].style.color='".($result?"green":"red")."';";
    }
    exit;
}

include "./header.php";
include "./menu.php";


print <<<HTML
<style>
.tbl{border:solid thin black;width:800px;}
.tbl td {width:120px;height:20px; border-left:solid thin black;}
.tbl thead {background-color:yellow;color:black;text-align:center;}
.tbl input {background:none;border:none;width:100%;text-align:right;font-size:11px;margin-right:0px;}
</style>

<script language="javascript">
function save(site,paysid,value){
    var post=new Array();
    post['site']=site;
    post['paysid']=paysid;
    post['value']=value;
    ajaxrequest("code_analytique.php",save_cb,post);
}

function save_cb(httprequest){
    eval(httprequest.responseText);
}
</script>

<h1>Comptes analytiques</h1>
HTML;

$o=new MP_Order();
// $sites=array_merge(array("macaveavin"=>"ma cave à vin"),$o->marketplaces);
$sites=$o->marketplaces;
// var_dump($sites);
$compte=array();
$description=array();
$requete=$DB_site2->query("SELECT * FROM compte_analytique");
while($c=$DB_site2->fetch_array($requete)){
    $compte[$c['paysid']][$c['site']]=$c['code'];
    $description[$c['site']]=$c['description'];
}
ksort($compte);

$requete=$DB_site2->query("SELECT distinct p.libelle, p.paysid from fraisport as f inner join pays as p on (f.paysid=p.paysid) order by p.libelle");
while($p=$DB_site2->fetch_array($requete))
    $pays[$p['paysid']]=$p['libelle'];

asort($pays);

$bg=array(0=>"white",1=>"#CCCCCC");
$i=0;

print "
<table cellspacing=\"0\" class=\"tbl\">
<thead>
<tr><td></td><td>Mon Espace Cuisson</td>";
foreach($sites as $site=>$name) print "<td>".$name."</td>";
print "</tr>
</thead>";
foreach($pays as $paysid=>$paysname){
    print "<tr style=\"background-color:".$bg[$i].";\"><td>$paysname</td>";
    print "<td><input type=\"text\" name=\"c[$paysid][macaveavin]\" value=\"".$compte[$paysid]['macaveavin']."\" onclick=\"this.select();\" onfocus=\"this.style.border='solid thin black';\" onblur=\"this.style.border='none';save('macaveavin','".$paysid."',this.value);\"></td>";
    
    $i=abs($i-1);
    foreach($sites as $site=>$name){
	print "<td><input type=\"text\" name=\"c[$paysid][$site]\" value=\"".$compte[$paysid][$site]."\" onclick=\"this.select();\" onfocus=\"this.style.border='solid thin black';\" onblur=\"this.style.border='none';save('".$site."','".$paysid."',this.value);\"></td>";
    }
    print "</tr>";
}
print "</table>";


include "./footer.php";
?>