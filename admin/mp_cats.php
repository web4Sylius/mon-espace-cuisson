<?php

include("admin_global.php");

include "./header.php";
include "./menu.php";

$keys=array("artcode"=>"Code article",
"reffab"=>"Ref. constructeur",
"libelle"=>"Libellé",
"description"=>"Description",
"marque"=>"Marque",
"gamme"=>"Gamme",
"modele"=>"Modèle",
"usage"=>"Usage",
"dimensions"=>"Dimensions",
"porte"=>"Version porte",
"couleur"=>"Couleur",
"couleurcadre"=>"Couleur cadre",
"capacite"=>"Capacité",
"capacitemini"=>"Capacité mini",
"capacitemaxi"=>"Capacité maxi",
"tempinstall"=>"Température d'installation",
"nbretemperatures"=>"Nombre températures",
"vibrations"=>"Absence de vibrations",
"filtre"=>"Filtre à charbon",
"rangements"=>"Rangements inclus",
"consoenergie"=>"Conso. énergie",
"consoeuro"=>"Conso. euros",
"poids"=>"Poids",
"prixht"=>"Prix HT",
"prixttc"=>"Prix TTC",
"prixconseille"=>"Prix conseillé HT",
"ecopartht"=>"Eco part. HT",
"ecopartttc"=>"Eco part. TTC",
"tauxtva"=>"Taux TVA",
"delailivraison"=>"Texte délai livraison",
"paysfabrication"=>"Pays fabrication",
"refsfab"=>"Références constructeur",
"garantie"=>"Garantie incluse",
"regulation"=>"Régulation",
"bouton"=>"Boutons réglages",
"reglage"=>"Réglage",
"affichage"=>"Affichage",
"temperature"=>"Température",
"thermometre"=>"Thermomètre",
"hygrometrie"=>"Hygrométrie",
"hygrometre"=>"Hygrométre",
"pieds"=>"Pieds vérins réglables",
"serrure"=>"Serrure",
"poignee"=>"Poignée",
"modularite"=>"Modularité intérieure",
"interrupteur"=>"Interrpteur",
"eclairage"=>"Eclairage",
"boutontest"=>"Bouton test",
"videpoche"=>"Vide poche",
"clayettefil"=>"Clayette fil",
"paniertransport"=>"Panier de transport",
"vinokit"=>"Vinokit",
"productionfroid"=>"Production de froid",
"productionchaud"=>"Production de chaud",
"declenchementchaud"=>"Déclenchement de chaud",
"fonctionhiver"=>"Fonction hiver",
"puissance"=>"Puissance électrique",
"intensite"=>"Intensité",
"conso24h"=>"Conso en kW/h 24h",
"conso1an"=>"Conso en kW/h par an",
"bruit"=>"Niveau de bruit (dBA)",
"hauteur"=>"Hauteur",
"largeur"=>"Largeur",
"profondeur"=>"Profondeur",
"volume"=>"Volume m<sup>3</sup>",
"classe_energie"=>"Classe énergétique",
"classe_clima"=>"Classe climatique",
"plage_temperature"=>"Plage de températures",
"gaz"=>"Gaz");

$rdc=array("denomination","description","fulldescription","installation","mcid","subjectivedenomination","wine_cellar_type","refrigerator_type","installation","total_net_capacity","freezer_pos","has_water_fountain","has_auto_defrost","color","installation_type","cooling_power","noise","is_reverse_cycle_air_conditioner");
$amazon=array("AwardWon","BulletPoint1","BulletPoint2","BulletPoint3","BulletPoint4","BulletPoint5","ConditionNote","Description","leadtime-to-ship","ProductName","ProductType","RecommendedBrowseNode1","SearchTerms","SearchTerms1","SearchTerms2","SearchTerms3","SearchTerms4","SearchTerms5");
$priceminister=array("Titre","Description","Type","typefichier","categorie","matiere","style","emplacement");
$google=array("title","description","product_type","google_product_category","genre");
$windil=array("Categorie","Titre","Description");
$pixmania=array("segment","title","description");
$fnac=array("meta-type","type","support","format","titre","description","caracteristiques","type_pose","type_cave");
$cdiscount=array("libelle_court","libelle_long","description","code_categorie","niveau1","niveau2","niveau3","niveau4");
$showroomprive=array("description","categorie");
$retif=array("categorie","titre","description","fulldescription");
$marketplaces=array("rdc"=>$rdc,"amazon"=>$amazon,"priceminister"=>$priceminister,"google"=>$google,"windil"=>$windil,"pixmania"=>$pixmania,"fnac"=>$fnac,"cdiscount"=>$cdiscount,"showroomprive"=>$showroomprive,"retif"=>$retif);

$limites=array("google"=>array("title"=>70,"description"=>5000),
"windil"=>array("Titre"=>60),
"priceminister"=>array("Description"=>3000),
"amazon"=>array("BulletPoint1"=>50,"BulletPoint2"=>50,"BulletPoint3"=>50,"BulletPoint4"=>50,"BulletPoint5"=>50,"SearchTerms1"=>50,"SearchTerms2"=>50,"SearchTerms3"=>50,"SearchTerms4"=>50,"SearchTerms5"=>50,"Description"=>2000,"ProductName"=>500),
"fnac"=>array("titre"=>255,"description"=>4000,"caracteristiques"=>4000),
"cdiscount"=>array("libelle_court"=>30,"libelle_long"=>50,"description"=>5000),
"showroomprive"=>array("description"=>2000),
"retif"=>array("titre"=>80,"description"=>500,"fulldescription"=>2000));

$tags=array("fnac"=>array("description"=>"<br>","caracteristiques"=>"<br>"),
"pixmania"=>array("description"=>"<br>")
);

if(isset($_POST['mp'])){
    $DB_site2->query("DELETE FROM market_place_catid WHERE market_place='".$_POST['mp']."' AND catid=".$_POST['catid']);
    foreach($_POST as $name=>$value){
	if(substr($name,0,6)=="field_"){
	    $name=substr($name,6);
	    $DB_site2->query("INSERT INTO market_place_catid SET market_place='".$_POST['mp']."', champ='".$name."', valeur='".mysqli_real_escape_string($DB_site2->link_id,$value)."', catid=".$_POST['catid']);
	}
    }
    unset($action);
}

if(isset($_POST['enabled'])){
    $exist=array();
    $r=$DB_site2->query("SELECT * FROM market_place_champs WHERE artcode IN ('".stripslashes($_POST['artcode'])."') AND market_place='".$_POST['mpname']."'");
    while($oldvalue=$DB_site2->fetch_array($r)){
	$exist[]=$oldvalue['artcode'];
    }
    if(strpos($_POST['artcode'],",")>-1)
	$artcodes=explode("','",stripslashes($_POST['artcode']));
    else
	$artcodes=array($_POST['artcode']);

    foreach($artcodes as $a){
	  if(!in_array($a,$exist))
	    $DB_site2->query("INSERT INTO market_place_champs SET artcode='".$a."', market_place='".$_POST['mpname']."', enabled=".intval($_POST['enabled']));
	  else
	    $DB_site2->query("UPDATE market_place_champs SET enabled=".intval($_POST['enabled'])." WHERE artcode='".$a."' AND market_place='".$_POST['mpname']."'");
    }
}

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
    <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
        <tr>
            <td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Configuration de market places</font>
            </td>
        </tr>
        <tr>
            <td background=\"images2/cadre_r3_c2.gif\" width=\"100%\" height=\"2\">
            </td>
        </tr>
    </table>
   </td>
   <td><img name=\"cadre_r2_c5\" src=\"images2/cadre_r2_c5.gif\" width=\"11\" height=\"27\" border=\"0\" alt=\"\"></td>
   <td background=\"images2/cadre_r2_c6.gif\" width=\"100%\" height=\"27\" align=\"center\" valign=\"middle\">
   </td>
   <td><img name=\"cadre_r2_c8\" src=\"images2/cadre_r2_c8.gif\" width=\"13\" height=\"27\" border=\"0\" alt=\"\"></td>
  </tr>
</table>

</tr></td>
<tr><td>

<script language=\"javascript\">
    function checkall(list,state){
	for(i=0;i<list.length;i++){
	    document.getElementById('chk_'+list[i]).checked=state;
	}
    }

    function showhelp(where){
	var hdiv=document.getElementById('fieldshelp');
	hdiv.style.top=document.body.scrollTop+'px';
	hdiv.style.display='block';
	
    }

    function testfields(form){
	var post=new Array();
	post['catid']=form.catid.value;
	for(i=0;i<form.getElementsByTagName(\"TEXTAREA\").length;i++){
	      var oi=form.getElementsByTagName(\"TEXTAREA\")[i];
	      post[oi.name]=oi.value;     
	}
	for(i=0;i<form.getElementsByTagName(\"INPUT\").length;i++){
	      var oi=form.getElementsByTagName(\"INPUT\")[i];
	      post[oi.name]=oi.value;     
	}
	ajaxrequest('mp_cats_ajax.php',testcb,post);
    }

    function testcb(httprequest){
	document.getElementById('preview').innerHTML=httprequest.responseText;
	document.getElementById('previewdiv').style.display='block';
	document.getElementById('previewdiv').style.top=document.body.scrollTop+'px';
    }
    
    function fillkey(key){
	if(curfield==undefined) return;
	curfield.value+=key;
	document.getElementById('fieldshelp').style.display='none';
    }

    var curfield=undefined;
    function setcurfield(f){
	if(curfield!=undefined) curfield.style.backgroundColor='';
	curfield=f;
	curfield.style.backgroundColor='#AAFFFF';
    }

    function enableart(artcode,mp,enabled){
	document.getElementsByName('artcode')[0].value=artcode;
	document.getElementsByName('mpname')[0].value=mp;
	document.getElementsByName('enabled')[0].value=enabled?'1':'0';
	document.forms.modifenabled.submit();
    }
    function fieldskp(e){
	if(window.event){ //IE
	    keynum = e.keyCode;
	}else if(e.which){ // Netscape/Firefox/Opera
	    keynum = e.which;
	}
	if(keynum==13) return false;
	if(keynum==undefined) return false;
	keychar = String.fromCharCode(keynum)
	if(keychar=='['){ showhelp(undefined); return false;}
	return true;
    }
    function checkall(mp){
	for(i=0;i<document.getElementsByName('chk'+mp+'[]').length;i++)
	    document.getElementsByName('chk'+mp+'[]')[i].checked=document.getElementsByName('checkall'+mp)[0].checked;

	document.getElementsByName('artcode')[0].value=document.getElementsByName('artcodes')[0].value;
	document.getElementsByName('mpname')[0].value=mp;
	document.getElementsByName('enabled')[0].value=document.getElementsByName('checkall'+mp)[0].checked?'1':'0';
	document.forms.modifenabled.submit();
    }
    document.onkeydown = function(e){ 	
	if (e == null)
	  keycode = event.keyCode;
	else
	  keycode = e.which;

	if(keycode == 27){
	  document.getElementById('fieldshelp').style.display='none';
	  document.getElementById('previewdiv').style.display='none';
	}
    }
    document.body.onclick=function(){
	document.getElementById('fieldshelp').style.display='none';
	document.getElementById('previewdiv').style.display='none';
    }

</script>

<style>
.tbhelp{float:left;}
.tbhelp tr {cursor:pointer;}
.tbhelp tr:hover{background-color:white;}
</style>

<div id=\"previewdiv\" style=\"display:none;position:absolute;background-color:#EEE;border:solid thin black;\">
<div id=\"preview\" style=\"float:left;\"></div>
<div style=\"float:right;color:red;font-weight:bold;cursor:pointer;text-align:center;width:20px;\" onclick=\"document.getElementById('previewdiv').style.display='none';\">X</div>
</div>

<div id=\"fieldshelp\" style=\"display:none;position:absolute;background-color:#DDDDDD;border:solid thin black;\">
<table class=\"tbhelp\">";
ksort($keys);
foreach($keys as $key=>$help)
  echo "<tr onclick=\"fillkey('[$key]')\"><td>$help</td><td>[$key]</td></tr>";

echo "</table>
<div style=\"float:right;color:red;font-weight:bold;cursor:pointer;\" onclick=\"document.getElementById('fieldshelp').style.display='none';\">X</div>
</div>

<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td background=\"images2/cadre_r4_c2.gif\" width=\"3\" height=\"100%\"></td>
   <td align=\"justify\" valign=\"middle\">
    <font class=\"txt_2\">
        <table align=\"center\" width=\"100%\">
            <tr>
                <td valign=\"middle\" width=\"100%\">" ;

if(!isset($action)){
  $cats=$DB_site2->query("SELECT catid,libelleFR FROM categorie WHERE parentid=0 ORDER BY position");
  while($cat=$DB_site2->fetch_array($cats)){
    $mpconfig="";
    $req=$DB_site2->query("SELECT DISTINCT market_place FROM market_place_catid WHERE catid=".$cat[catid]);
    while($mp=$DB_site2->fetch_array($req)) $mpconfig.=" ".$mp[0];
 
    print "<br>".displaysscat($cat['catid']);
  }
}else{
    if($action=="editer" && isset($catid)){
	$cat=$DB_site2->query_first("SELECT * FROM categorie WHERE catid=".$catid);
	print "<p>Modifier ".$cat['libelleFR']." <a href=\"mp_cats.php\">Retour</a> &nbsp; <a href=\"mp_cats.php?action=articles&catid=$catid\">Voir les articles</a></p>";
	
	print "<p>";
	foreach($marketplaces as $name=>$fields) print "<a href=\"#mp_$name\">$name</a> - ";
	print "</p>";

	foreach($marketplaces as $name=>$fields){
	    print "<a name=\"mp_$name\"></a>
	    <form name=\"form$name\" method=\"post\">
	    <input type=\"hidden\" name=\"mp\" value=\"$name\"><input type=\"hidden\" name=\"catid\" value=\"$catid\">";
	   
	    print "<table class=\"table_factures\">
	    <tr><td colspan=\"2\" style=\"text-align:center;font-weight:bold;\">$name <img src=\"images/aide/aide.gif\" align=\"absmiddle\" title=\"Afficher les champs disponibles\" style=\"cursor:pointer;\" onclick=\"showhelp(this);\"></td</tr>";
	    foreach($fields as $field){
		$limite=$tag="";
		if(isset($limites[$name][$field])) $limite="<br>".$limites[$name][$field]." caract. maxi";
		if(isset($tags[$name][$field])) $tag="<br>Balises : ".htmlentities($tags[$name][$field],ENT_COMPAT,"ISO-8859-15");
		$f=$DB_site2->query_first("SELECT * FROM market_place_catid WHERE market_place='$name' AND catid=$catid AND champ='$field'");
		print "<tr><td>$field $limite $tag</td><td><input type=\"hidden\" name=\"limit_$field\" value=\"".intval($limites[$name][$field])."\"><input type=\"hidden\" name=\"tags_$field\" value=\"".$tags[$name][$field]."\"><textarea name=\"field_$field\" onfocus=\"setcurfield(this);\" cols=\"80\" rows=\"4\" onkeypress=\"return fieldskp(event);\">$f[valeur]</textarea></td></tr>";
	    }
	    print "<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"Enregistrer\"> <input type=\"button\" value=\"Tester\" onclick=\"testfields(document.form$name)\"></td></tr>
	    </table>";
	    print "</form>";
	}
    }
    if($action=="articles" && isset($catid)){
	    $cat=$DB_site2->query_first("SELECT * FROM categorie WHERE catid=".$catid);
	    print "<p>".$cat['libelleFR']." <a href=\"mp_cats.php?action=editer&catid=$catid\">Configurer</a> &nbsp; <a href=\"mp_cats.php\">Retour à la liste</a></p>";

	    $artcodes=array();
	    $lignes="";
	    $requete=$DB_site2->query("SELECT * FROM article WHERE catid=$catid AND active<>'2' GROUP BY artcode ORDER BY artcode");
	    while($article=$DB_site2->fetch_array($requete)){
		$enabled=array();
		$artcodes[]=$article['artcode'];
		$r2=$DB_site2->query("SELECT * FROM market_place_champs WHERE artcode='".$article['artcode']."'");
		while($e=$DB_site2->fetch_array($r2)) $enabled[strtolower($e['market_place'])]=$e['enabled'];
		$lignes.="<tr><td title=\"$article[libelleFR]\"><a href=\"ajout_produit.php?artid=$article[artid]&prec=modif\"".($article['active']!='1'?" style=\"color:red;\"":"").">$article[artcode] ($article[artcode_eurocave]) ".$article['gammeFR']."</a></td>";//<td align=\"center\"><input name=\"chkrdc[]\" type=\"checkbox\"".(intval($enabled['rdc'])==1?" checked":"")." onclick=\"enableart('$article[artcode]','rdc',this.checked);\"></td><td align=\"center\"><input name=\"chkamazon[]\" type=\"checkbox\"".(intval($enabled['amazon'])==1?" checked":"")." onclick=\"enableart('$article[artcode]','amazon',this.checked);\"></td><td align=\"center\"><input name=\"chkpriceminister[]\" type=\"checkbox\"".(intval($enabled['priceminister'])==1?" checked":"")." onclick=\"enableart('$article[artcode]','priceminister',this.checked);\"></td><td align=\"center\"><input name=\"chkgoogle[]\" type=\"checkbox\"".(intval($enabled['google'])==1?" checked":"")." onclick=\"enableart('$article[artcode]','google',this.checked);\"></td><td align=\"center\"><input name=\"chkwindil[]\" type=\"checkbox\"".(intval($enabled['windil'])==1?" checked":"")." onclick=\"enableart('$article[artcode]','windil',this.checked);\"></td><td align=\"center\"><input name=\"chkpixmania[]\" type=\"checkbox\"".(intval($enabled['pixmania'])==1?" checked":"")." onclick=\"enableart('$article[artcode]','pixmania',this.checked);\"></td></tr>";
		foreach($marketplaces as $mp=>$fields) $lignes.="<td align=\"center\"><input name=\"chk".$mp."[]\" type=\"checkbox\"".(intval($enabled[$mp])==1?" checked":"")." onclick=\"enableart('$article[artcode]','".$mp."',this.checked);\"></td>";
		$lignes.="</tr>";
	    }

	    print "<form name=\"modifenabled\" method=\"post\">
	    <input type=\"hidden\" name=\"artcode\">
	    <input type=\"hidden\" name=\"mpname\">
	    <input type=\"hidden\" name=\"enabled\">
	    <input type=\"hidden\" name=\"artcodes\" value=\"".implode("','",$artcodes)."\">
	    </form>";

	    print "<table>
	    <thead style=\"font-weight:bold;text-align:center;\">
	    <tr><td>Article</td>";//<td>RDC<br><input type=\"checkbox\" name=\"checkallrdc\" onclick=\"checkall('rdc')\"></td><td>Amazon<br><input type=\"checkbox\" name=\"checkallamazon\" onclick=\"checkall('amazon')\"></td><td>Priceminister<br><input type=\"checkbox\" name=\"checkallpriceminister\" onclick=\"checkall('priceminister')\"></td><td>Google<br><input type=\"checkbox\" name=\"checkallgoogle\" onclick=\"checkall('google')\"></td><td>Windil<br><input type=\"checkbox\" name=\"checkallwindill\" onclick=\"checkall('windill')\"></td><td>Pixmania<br><input type=\"checkbox\" name=\"checkallpixmania\" onclick=\"checkall('pixmania')\"></td></tr>
	    foreach($marketplaces as $mp=>$fields) print "<td>".$mp."<br><input type=\"checkbox\" name=\"checkall".$mp."\" onclick=\"checkall('".$mp."')\"></td>";
	    print "</thead>"
	    .$lignes.
	    "</table>
	    <p>Même cochés, les articles inactifs n'apparaitront pas dans l'export</p>";
    }
}


echo "</td>
            </tr>   
        </table>    
     </font>
    </td>
   <td background=\"images2/cadre_r4_c10.gif\" width=\"3\" height=\"100%\" border=\"0\" alt=\"\"></td>
  </tr>
</table>
</td>
</tr>
<tr><td>

<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td rowspan=\"2\"><img name=\"cadre_r6_c2\" src=\"images2/cadre_r6_c2.gif\" width=\"10\" height=\"26\" border=\"0\" alt=\"\"></td>
   <td align=\"center\" height=\"23\" width=\"100%\"></td>
   <td rowspan=\"2\"><img name=\"cadre_r6_c9\" src=\"images2/cadre_r6_c9.gif\" width=\"8\" height=\"26\" border=\"0\" alt=\"\"></td>
  </tr>
  <tr>
   <td background=\"images2/cadre_r7_c4.gif\" width=\"100%\" height=\"3\"></td>
  </tr>
</table>
</td></tr></table>
</td>
</tr><tr>
</tr></table>
                  </td>
              </tr>
            </table>
          </td>
        </tr>
";

include "./footer.php";

function displaysscat($catid){
    global $DB_site2;
    $return="";
    $mpconfig="";
    $cat=$DB_site2->query_first("SELECT * FROM categorie WHERE catid=".$catid);
    $req=$DB_site2->query("SELECT DISTINCT market_place FROM market_place_catid WHERE catid=".$catid);
    while($mp=$DB_site2->fetch_array($req)) $mpconfig.=" <a href=\"mp_cats.php?action=editer&catid=".$cat['catid']."#mp_".$mp[0]."\" style=\"color:green;\">".$mp[0]."</a>";

    $return.="<a href=\"mp_cats.php?action=editer&catid=".$cat['catid']."\">".$cat['libelleFR']."</a>$mpconfig<br>";
    
    $sscats=$DB_site2->query("SELECT catid,libelleFR FROM categorie WHERE catid='$catid' ORDER BY position");
    while($sscat=$DB_site2->fetch_array($sscats)){
	$ss2cats=$DB_site2->query("SELECT catid FROM categorie WHERE parentid=$sscat[catid] ORDER BY position");
	if($DB_site2->num_rows($ss2cats)>0){
	  $return.="<div style=\"margin-left:10px;border-left:solid thin black;\">";
	  while($ss2cat=$DB_site2->fetch_array($ss2cats)) $return.="__".displaysscat($ss2cat['catid']);
	  $return.="</div>";
	}
	
    }

    return $return;
}

?>