<?php

include("mpfields.php");

$mcid=array();
$mc0142=array("denomination","description","fulldescription","subjectivedenomination","refrigerator_type","installation","total_net_capacity","energy_label","freezer_pos","has_water_fountain","has_auto_defrost","color");
$mc2000=array("denomination","description","fulldescription","subjectivedenomination","wine_cellar_capacity","color","installation","wine_cellar_type");
$mc0446=array("denomination","description","fulldescription","subjectivedenomination");
$mc5695=array("denomination","description","fulldescription","subjectivedenomination");
$mc3499=array("denomination","description","fulldescription","subjectivedenomination","installation_type","cooling_power","noise","is_reverse_cycle_air_conditioner","energy_label");
$mc0355=array("denomination","description","fulldescription","subjectivedenomination");
$mc0372=array("denomination","description","fulldescription","subjectivedenomination");
$mc0626=array("denomination","description","fulldescription","subjectivedenomination");
$mc5425=array("denomination","description","fulldescription","subjectivedenomination");
$mc7652=array("denomination","description","fulldescription","subjectivedenomination");
$mc8406=array("denomination","description","fulldescription","subjectivedenomination");
$mc8407=array("denomination","description","fulldescription","subjectivedenomination");
$mc8408=array("denomination","description","fulldescription","subjectivedenomination");
$mc8409=array("denomination","description","fulldescription","subjectivedenomination");
$mc8410=array("denomination","description","fulldescription","subjectivedenomination");
$mcid['mc-0142']=$mc0142;
$mcid['mc-2000']=$mc2000;
$mcid['mc-0446']=$mc0446;
$mcid['mc-5695']=$mc5695;
$mcid['mc-3499']=$mc3499;
$mcid['mc-0355']=$mc0355;
$mcid['mc-0372']=$mc0372;
$mcid['mc-0626']=$mc0626;
$mcid['mc-5425']=$mc5425;
$mcid['mc-7652']=$mc7652;
$mcid['mc-8406']=$mc8406;
$mcid['mc-8407']=$mc8407;
$mcid['mc-8408']=$mc8408;
$mcid['mc-8409']=$mc8409;
$mcid['mc-8410']=$mc8410;

$article=$DB_site2->query_first("SELECT * FROM article WHERE artid=".$artid);


$contenu_onglet .= "
<h3>Market place</h3>
<font class=\"cost\">$erreur</font>
<script language=\"javascript\">
var backup=undefined;

function changemcid(id){
    if(backup==undefined){
      backup=new Array();
      for(i=0;i<document.forms['formu'].length;i++){
	var inp=document.forms['formu'][i];
	backup[inp.name]=inp.value;
      }
    }

    smcid=mcid[id];
    html='';
    for(i=0;i<smcid.length;i++){
	if(backup['field_'+smcid[i]]==undefined) backup['field_'+smcid[i]]='';
	html+='<tr><td>'+smcid[i]+'</td><td><input type=\"text\" name=\"field_'+smcid[i]+'\" value=\"'+backup['field_'+smcid[i]]+'\" size=\"50\"></td></tr>';

    }
    html+='<tr><td>MCID</td><td><select name=\"field_mcid\" onchange=\"changemcid(this.value)\">';
    for(i in mcid){
	selected='';
	if(i==id) selected=' selected';
	html+='<option value=\"'+i+'\"'+selected+'>'+i+'</option>';
    }
    html+='</option>';
    document.getElementById('field_list').innerHTML=html;
}

\n";

$js="var mcid=new Array();\n";
foreach($mcid as $id=>$fieldlist){
    $js.="mcid['".$id."']=new Array(";
    foreach($fieldlist as $field)
      $js.="'$field',";
    if(count($fieldlist)>0) $js=substr($js,0,strlen($js)-1);
    $js.=");\n";
}

/*$mpf=array();
$requete=$DB_site2->query("SELECT * FROM market_place_champs");
while($row=$DB_site2->fetch_array($requete)){
    $mpf[$row['market_place']][$row['artcode']]=$row['enabled'];
}
$DB_site2->query("TRUNCATE TABLE market_place_champs");
foreach($mpf as $name=>$artcodes){
    foreach($artcodes as $artcode=>$enabled)
	$DB_site2->query("INSERT INTO market_place_champs SET market_place='$name', artcode='$artcode', enabled=".intval($enabled));
}*/

$listmp=array("amazon","rdc","priceminister","google","windil","pixmania","showroomprive","retif");

$contenu_onglet.=$js."\n</script>
<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\" style=\"display:inline;\">
<input type=\"hidden\" name=\"old\" value=\"$mp\">
<input type=\"hidden\" name=\"quitter\" value=\"\">
<input type=\"hidden\" name=\"action2\" value=\"\">
<input type=\"hidden\" name=\"catid\" value=\"$catid\">
<input type=\"hidden\" name=\"artid\" value=\"$artid\">

<table id=\"field_list\">";

foreach($listmp as $mp) $contenu_onglet.=listfields($mp);

$contenu_onglet.="</table>
</form>
<p><a href=\"mp_cats.php?action=editer&catid=$article[catid]\">Modifier la catégorie</a></p>";


function listfields($mp){
    global $article;
    global $DB_site2;

    $enabled=$DB_site2->query_first("SELECT * FROM market_place_champs WHERE market_place='".$mp."' AND artcode='".$article['artcode']."'");
    $return="<tr><td colspan=\"2\"><h3>Exporter sur $mp</h3><input type=\"checkbox\" name=\"export[$mp]\" value=\"1\"".($enabled['enabled']=='1'?" checked":"")."> </td></tr>";

    $requete=$DB_site2->query("SELECT * FROM market_place_catid WHERE market_place='$mp' AND catid='$article[catid]'");
    while($mpfield=$DB_site2->fetch_array($requete)){
	$return.="<tr><td style=\"border-bottom:solid thin black;\">".$mpfield['champ']."</td><td style=\"border-bottom:solid thin black;\">".mpfields($article,$mpfield['valeur'])."</td></tr>";
    }
    if($DB_site2->num_rows($requete)==0) $return.="<tr><td colspan=\"2\">Catégorie non configurée <a href=\"mp_cats.php?action=editer&catid=$article[catid]\">configuration</a></td></tr>";

    return $return;
}

?>