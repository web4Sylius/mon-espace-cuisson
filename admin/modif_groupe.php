<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "./fonctions.php";







echo "<LINK href=\"cal.css\" type=\"text/css\" rel=\"stylesheet\">
<script language=\"javascript\" src=\"calendrier.js\"></script>
<script language=\"javascript\">
function switchmode(type){
    if(document.getElementById('edit_'+type).style.display=='none'){
	document.getElementById('edit_'+type).style.display='block';
	document.getElementById('list_'+type).style.display='none';
	document.getElementById('btn_'+type).src='images/moins.jpg';
    }else{
	document.getElementById('edit_'+type).style.display='none';
	document.getElementById('list_'+type).style.display='block';
	document.getElementById('btn_'+type).src='images/plus.jpg';
    }
}

function updcat(){
    html='';
    for(i=0;i<document.getElementsByName('cat[]').length;i++){
	chk=document.getElementsByName('cat[]')[i];
	if(chk.checked) html+=', '+document.getElementById('catname_'+chk.value).value;
    }
    html=html.substr(1);
    document.getElementById('list_cat').innerHTML=html;
    if(document.getElementById('articles')) document.getElementById('articles').style.display='none';
}

function updmarque(){
    html='';
    for(i=0;i<document.getElementsByName('marque[]').length;i++){
	chk=document.getElementsByName('marque[]')[i];
	if(chk.checked) html+=', '+document.getElementById('marquename_'+chk.value).value;
    }
    html=html.substr(1);
    document.getElementById('list_marque').innerHTML=html;
    if(document.getElementById('articles')) document.getElementById('articles').style.display='none';
}

function checkall(type,state,values){
    var checknb=0;

    var display=(state?'table-row':'none');
    spvalue=new Array();
    if(values!=undefined) spvalues=values.split(',');
    for(i=0;i<document.getElementsByName(type+'[]').length;i++){
	artcode=document.getElementsByName(type+'[]')[i].value;
	if(values==undefined || in_array(artcode,spvalues)){
	  document.getElementsByName(type+'[]')[i].checked=state;
	}	  
    }
    if (eval(\"typeof upd\"+type+\" == 'function'\")) eval(\"upd\"+type+\"()\");
    if(type=='art') showartlines();
}

function showartlines(){
    var tr=document.getElementById('lstarticles').getElementsByTagName('tr');
    for(i=0;i<tr.length;i++) if(tr[i].id!=undefined){
      var chk=tr[i].getElementsByTagName('input')[0];
      if(chk!=undefined){
	var display=(chk.checked?'table-row':'none');
	tr[i].style.display=display;
      }
    }
}

function ordreresults(ordre){
    document.getElementsByName('ordre')[0].value=ordre;
    document.forms.src.submit();
}

function in_array(needle,haystack){
    for (key in haystack) 
           if (haystack[key] == needle) return true;
    return false;
}

function submitsrc(){
    document.getElementsByName('action')[0].value='list';
    document.forms.src.submit();
}

function submitupdate(){
	var \$jq = jQuery.noConflict();
	var nbart = \$jq('#lstarticles input:checked').length;
	// alert('nb art : '+nbart);
    if(nbart==0){
		alert('Aucun article est sélectionné');
		return false;
    }
	var nom_champ = \$jq('#sfieldname').val();
	txtconfirm=\"Confirmez-vous la modification du champ \"+nom_champ+\" sur les \"+nbart+\" articles ?\";
	if(confirm(txtconfirm)){
      document.getElementsByName('action')[0].value='update';
      document.forms.src.submit();
    }
}

</script>";

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
    <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
        <tr>
            <td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Modifications par groupe</font>
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

<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td background=\"images2/cadre_r4_c2.gif\" width=\"3\" height=\"100%\"></td>
   <td align=\"justify\" valign=\"middle\"><br>
    <font class=\"txt_2\">
        <table align=\"center\" width=\"100%\">
            <tr>
                <td align=\"center\" valign=\"middle\" width=\"100%\">
";

if(!isset($_POST['marque'])) $_POST['marque']=array();
if(!isset($_POST['cat'])) $_POST['cat']=array();

$fields=array("poid"=>"Poids net","libelleFR"=>"Libelle","commandable"=>"Commandable","delailivraison"=>"Délai livraison jours","reappro"=>"Date de réappro",
"delailivraison_txt"=>"Délai livraison texte","gammeFR"=>"Gamme FR","gammeEN"=>"Gamme EN","gammeSP"=>"Gamme SP","nbr_temperature"=>"Nombre de T°","capacite_bouteilles"=>"Capacité","avant"=>"Mise en avant",
"capacite_mini"=>"Capacité mini","capacite_maxi"=>"Capacité maxi","capacite_l"=>"Capacité en L","temperature_cave"=>"Température cave",
"fonction_hiver"=>"Fonction hiver","filtre_charbon"=>"Filtre à charbon","absence_vibration"=>"Absence vibration","version_porte"=>"Version porte",
"coloris"=>"Coloris","coloris_porte"=>"Coloris porte","garantie_incluse"=>"Garantie incluse","dimensions"=>"Dimensions","hauteur"=>"Hauteur","largeur"=>"Largeur","profondeur"=>"Profondeur",
"conso_kw"=>"Conso en kW","conso_euro"=>"Conso en euros","regulation"=>"Régulation","boutons_reglage"=>"Boutons réglage","reglage"=>"Réglage","temperature"=>"Température",
"thermometre"=>"Thermomètre","hygrometrie"=>"Hygrometrie","pieds_reglables"=>"Pieds réglables","serrure"=>"Serrure","poignee"=>"Poignée","modularite_interieure"=>"Modularité intéreur",
"interrupteur"=>"Interrupteur","eclairage"=>"Eclairage","bouton_test"=>"Bouton test","roulettes"=>"Roulettes","production_froid"=>"Production froid",
"production_chaud"=>"Production chaud","declenchement_chaud"=>"Déclenchement chaud","fonction_hiver_techno"=>"Fonction hiver techno","parois_laterales"=>"Parois laterales",
"parois_fond"=>"Parois fond","revetement_exterieur"=>"Revetement exterieur","isolation"=>"Isolation","puissance_electrique"=>"Puissance électrique","intensite_electrique"=>"Intensite électrique",
"consommation_h"=>"Consommation h","consommation_an"=>"Consommation an","niveau_bruit"=>"Niveau bruit","emballage_hauteur"=>"Emballage hauteur","emballage_largeur"=>"Emballage largeur",
"emballage_profondeur"=>"Emballage profondeur","emballage_poids"=>"Emballage poids","rangements_txt"=>"Rangements","energy_label"=>"Label énergétique","paysfabrication"=>"Origine fabrication",
"commandable"=>"Commandable","active"=>"Actif","plage_temperature"=>"Plage température","gaz"=>"Gaz","classe_clima"=>"Classe climatique","plus"=>"Les plus","affichage"=>"Affichage","hygrometre"=>"Hygromètre","alarme"=>"Alarme","energy_label"=>"Classe énergétique","niveau_bruit_abcd"=>"Niveau bruit abcd","sensporte"=>"Sens porte");
asort($fields);

$values=array();
$values["active"]=array(0=>"Temporairement inactif",1=>"Actif",2=>"Inactif");
$values["commandable"]=array(0=>"Vendu en magasin",1=>"Oui",2=>"Non disponible");
$values["paysfabrication"]=array(0=>"France",1=>"Europe",2=>"Asie",3=>"USA");
$values["avant"]=array(0=>"Non",1=>"Oui");

print "<form name=\"src\" method=\"post\">
<input type=\"hidden\" name=\"action\" value=\"list\">
<input type=\"hidden\" name=\"sfield\" value=\"".$_POST['sfield']."\">";

$requete=$DB_site2->query("SELECT * FROM marque ORDER BY libelle");
$nb=$DB_site2->num_rows($requete);
$c=0;
$list="";
print "<div class=\"table_factures\" style=\"text-align:left;\">
  <img src=\"images/".(isset($_POST['action'])?"plus":"moins").".jpg\" id=\"btn_marque\" align=\"absmiddle\" onclick=\"switchmode('marque');\"> Marque (".count($_POST['marque']).")
  <table id=\"edit_marque\" ".(isset($_POST['action'])?"style=\"display:none;\"":"").">
  <tr><td colspan=\"5\"><a href=\"javascript:checkall('marque',true);\">Tout cocher</a> <a href=\"javascript:checkall('marque',false);\">Tout décocher</a></td></tr>
  <tr>";
while($m=$DB_site2->fetch_array($requete)){
    print "<td><input type=\"hidden\" id=\"marquename_$m[marqueid]\" value=\"$m[libelle]\"><input type=\"checkbox\" name=\"marque[]\" onclick=\"updmarque()\" value=\"$m[marqueid]\"".(in_array($m[marqueid],$_POST['marque'])?" checked":"")."> $m[libelle]</td>";
    if(in_array($m[marqueid],$_POST['marque'])) $list.=", ".$m['libelle'];
    $c++;
    if($c==5){
	$c=0;
	print "</tr><tr>";
    }
}
$list=substr($list,1);
print "</table>
<div id=\"list_marque\" ".(!isset($_POST['action'])?"style=\"display:none;\"":"").">$list</div>
</div>";

print "<div class=\"table_factures\" style=\"text-align:left;margin-top:10px;\">
  <img src=\"images/".(isset($_POST['action'])?"plus":"moins").".jpg\" id=\"btn_cat\" align=\"absmiddle\" onclick=\"switchmode('cat');\"> Catégorie (".count($_POST['cat']).")
<div id=\"edit_cat\"".(isset($_POST['action'])?"style=\"display:none;\"":"")."><a href=\"javascript:checkall('cat',true);\">Tout cocher</a> <a href=\"javascript:checkall('cat',false);\">Tout décocher</a>";

$list="";
$cats=$DB_site2->query("SELECT catid,libelleFR FROM categorie WHERE parentid=0 ORDER BY position");
while($cat=$DB_site2->fetch_array($cats)){
  $sscattxt="";
  if(in_array($cat[catid],$_POST['cat'])) $list.=", ".$cat['libelleFR'];
  $clickall=array();
  $clickall[]=$cat['catid'];

  print "<br>".displaysscat($cat['catid']);

  /*$sscats=$DB_site2->query("SELECT catid,libelleFR FROM categorie WHERE parentid='$cat[catid]' ORDER BY position");
  while($sscat=$DB_site2->fetch_array($sscats)){
      $sscattxt.=$sscat['libelleFR']."<br>";
      if(in_array($sscat[catid],$_POST['cat'])) $list.=", ".$sscat['libelleFR'];
      $clickall[]=$sscat['catid'];
  }*/
  /*print "<br><input type=\"hidden\" id=\"catname_$cat[catid]\" value=\"$cat[libelleFR]\"><input type=\"checkbox\" name=\"cat[]\" onclick=\"updcat();\" value=\"$cat[catid]\"".(in_array($cat[catid],$_POST['cat'])?" checked":"").">".$cat['libelleFR']." <a href=\"javascript:checkall('cat',true,'".implode(',',$clickall)."')\">Cocher la catégorie</a> <a href=\"javascript:checkall('cat',false,'".implode(',',$clickall)."')\">Décocher la catégorie</a>
  <div style=\"padding-left:20px;\">
  $sscattxt
  </div>";*/
}
$list=substr($list,1);
print "</div>
<div id=\"list_cat\" ".(!isset($_POST['action'])?"style=\"display:none;\"":"").">$list</div>";

print "</div>";

print "<div class=\"table_factures\" style=\"text-align:left;margin-top:10px;\">
Autre condition
<br>
<select name=\"condfield\">";
foreach($fields as $field=>$txt) print "<option value=\"$field\"".($_POST['condfield']==$field?" selected":"")." onclick=\"document.getElementsByName('sfield')[0].value=this.value;\">$txt</option>";
print "</select>
<select name=\"condmode\" style=\"font-size:14px;text-align:center;\"><option value=\"=\"".($_POST['condmode']=="="?" selected":"").">=</option><option value=\"<>\"".($_POST['condmode']=="<>"?" selected":"").">&#8800;</option><option value=\"LIKE\"".($_POST['condmode']=="LIKE"?" selected":"").">contient</option><option value=\"IN\"".($_POST['condmode']=="IN"?" selected":"").">IN</option></select>
<input type=\"text\" name=\"condition\" value=\"".stripslashes($_POST['condition'])."\" size=\"60\">
</div>";

print "<p><input type=\"button\" value=\"Rechercher les articles\" onclick=\"submitsrc();\"></p>";

if($_POST['action']=="update"){
    $sfield=$_POST['sfield'];
    if($_POST['sfield']=="reappro"){
		$sfield="date_appro";
		$sp=preg_split('/[- :\/h]/',$_POST['newval']);
		$_POST['newval']=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
    }
	if($_POST['sfield']=="catid"){
		$sfield="catid";
    }
    $sql="UPDATE article SET ".$sfield."='".$_POST['newval']."' WHERE artcode IN ('".implode("','",$_POST['art'])."')";
	// echo $sql;
    $result=$DB_site2->query($sql);
    $req=$DB_site2->query("SELECT artid FROM article WHERE artcode IN ('".implode("','",$_POST['art'])."')");
    while($modifart=$DB_site2->fetch_array($req))
	$DB_site2->query("INSERT INTO article_history SET artid=".$modifart['artid'].", utilisateur='".$user_info['username']."', date=".time().", `key`='".$_POST['sfield']."', value='".$_POST['newval']."'");

    if($result)
	print "<p>".count($_POST['art'])." articles modifiés</p>";
    else
	print "<p>Erreur pendant la mise à jour</p>";

    $_POST['action']="list";
}

if(isset($_POST['action']) && $_POST['action']=="list"){
    (!isset($_POST['ordre'])?$ordre="artcode":$ordre=$_POST['ordre']);

    $fieldtxt="";
    $fselect="<select name=\"field\" onchange=\"document.getElementsByName('sfield')[0].value=this.value;document.forms.src.submit();\"><option value=\"\">Choisir ...</option>";
	$selected=false;
	if($_POST['sfield']=="catid"){
		$selected=true;
		$fieldtxt="ID catégorie";
	}
	$fselect.="<option value=\"catid\"".($selected?" selected":"").">--catégorie</option>";
    foreach($fields as $field=>$txt){
		$selected=false;
		if($_POST['sfield']==$field){
			$selected=true;
			$fieldtxt=$txt;
		}
		$fselect.="<option value=\"$field\"".($selected?" selected":"").">$txt</option>";
    }
    $fselect.="</select>";
    print "<div id=\"articles\">
    <input type=\"hidden\" name=\"ordre\" value=\"$ordre\">
    <p>Champ à modifier : $fselect</p>";
    
    $editfield="";
    $sql="SELECT a.*,FROM_UNIXTIME(date_appro) AS reappro FROM article a INNER JOIN article_marque am USING (artid) WHERE 1";
    if($_POST['cat']!=array()) $sql.=" AND catid IN (".implode(",",$_POST['cat']).")";
    if($_POST['marque']!=array()) $sql.=" AND am.marqueid IN (".implode(',',$_POST['marque']).")";
    if($_POST['condition']!=""){
	switch($_POST['condmode']){
	  case "=":
	  case "<>":
	  $_POST['condition']=" '".$_POST['condition']."'";
	  break;
	  case "LIKE":
	  $_POST['condition']=" '%".$_POST['condition']."%'";
	  break;
	  case "IN":
	  $_POST['condition']=" ".stripslashes($_POST['condition']);
	  break;
	}
	$sql.=" AND `".$_POST['condfield']."` ".$_POST['condmode'].$_POST['condition'];
    }

    $requete=$DB_site2->query($sql);
    $nbarticles=$DB_site2->num_rows($requete);
    
    $type=$DB_site2->query_first("SHOW FIELDS FROM article WHERE Field='".$_POST['sfield']."'");
    $par=strpos($type['Type'],"(");
    $par==-1?$otype=$type['Type']:$otype=substr($type['Type'],0,$par);

    if($_POST['sfield']=="reappro") $otype="cal";
	
	if($_POST['sfield']=="catid") $otype="categorie";

    switch($otype){
		case "int":
		case "smallint":
		case "tinyint":
		case "float":
		case "double":
			$input="<input type=\"text\" name=\"newval\" size=\"5\">";
			break;
		case "varchar":
		case "longtext":
		case "text":
			$input="<input type=\"text\" name=\"newval\">";
			break;
		case "enum":
			$prop=substr($type['Type'],$par);
			$prop=preg_replace("/\(|\)|'/","",$prop);
			$plist=explode(",",$prop);
			
			$input="<select name=\"newval\">";
			foreach($plist as $p){
			  $txt=$p;
			  if(isset($values[$_POST['sfield']])) $txt=$values[$_POST['sfield']][$p];
			  $input.="<option value=\"$p\">$txt</option>";
			}
			$input.="</select>";
			break;
		case "cal":
			$input="<input type=\"text\" id=\"newvval\" name=\"newval\" size=\"10\" onclick=\"calendrier_show(this.id,this.value);\" readonly>";
			break;
		case "categorie":
			$catid=0;
			arborescence(0);
			echo "<b><font color=\"red\">ATTENTION : CE QUE VOUS FAITES EST RISQUE !!!</font></b><br><br>";
			$input="<select name=\"newval\" size=25>
					$select
				</select>";
			break;
    }

    if($_POST['sfield']!="" && $nbarticles>0) print "<p>Remplacer par : $input <input type=\"hidden\" id=\"sfieldname\" value=\"".$fields[$_POST['sfield']]."\"><input type=\"button\" value=\"Modifier les articles sélectionnés\" onclick=\"submitupdate()\"></p>";

    print "<table id=\"lstarticles\" class=\"table_factures\" border=1>";
    print "<tr><td colspan=\"6\" align=\"center\">".$nbarticles." articles</td></tr>
	  <tr><td colspan=\"2\"><a href=\"#\" onclick=\"ordreresults('artcode')\"".($ordre=="artcode"?" style=\"font-weight:bold;\"":"").">Code</a></td><td><a href=\"#\" onclick=\"ordreresults('ref')\"".($ordre=="ref"?" style=\"font-weight:bold;\"":"").">Référence</a></td><td><a href=\"#\" onclick=\"ordreresults('margeht');\"".($ordre=="margeht"?" style=\"font-weight:bold;\"":"").">Marge HT</a></td><td><a href=\"#\" onclick=\"ordreresults('marge');\"".($ordre=="marge"?" style=\"font-weight:bold;\"":"").">Marge %</a></td><td><a href=\"#\" onclick=\"ordreresults('nbv');\"".($ordre=="nbv"?" style=\"font-weight:bold;\"":"").">Nb ventes</a></td><td><a href=\"#\" onclick=\"ordreresults('editfield')\"".($ordre=="editfield"?" style=\"font-weight:bold;\"":"").">$fieldtxt</a></td></tr>";
    $variantes=array();
    $tr=array();
    $colors=array(0=>"#888",1=>"green",2=>"red");
    while($article=$DB_site2->fetch_array($requete)){
	if($_POST['sfield']!="") $editfield=$article[$_POST['sfield']];
	$variantes[$editfield][$article[artcode]]++;
	$artcode=$article['artcode'];
	$ref=$article['artcode_eurocave'];
	$prixport=prixport($article['emballage_poids'],$article['prix']);
	$garantie=achatgarantie($article['artid']);
	$vente=$article['prixht']+$prixport['prix'];
	$achat=$article['paht']+$prixport['achat']+$garantie['achat']+$article['ecopartht']+$article['prixtrspt'];
	$margeht=round($vente-$achat,2);
	$marge=round(100*$margeht/$vente);
	$nb=$DB_site2->query_first("SELECT COUNT(*) FROM lignefacture lf INNER JOIN facture f ON (f.facturid=lf.factureid) WHERE lf.artid=$article[artid] AND f.valide IN (1,5,7,8)");
	$nbv=$nb[0];
	if(isset($values[$_POST['sfield']])) $editfield=$values[$_POST['sfield']][$editfield];
	$tr[${$ordre}][]="<tr id=\"tr_".substr($article['artcode'],4)."\"><td><!--<img src=\"/configurations/".$host."/images/produits/".$article[artid].".jpg\" width=\"50\">--></td><td nowrap><input type=\"checkbox\" name=\"art[]\" value=\"$article[artcode]\"><a href=\"ajout_produit.php?artid=".$article['artid']."&prec=modif\" target=\"_blank\" style=\"color:".$colors[$article['active']]."\">".$article['artcode']."</a></td><td>".$article['artcode_eurocave']."</td><td>$margeht</td><td>$marge</td><td>$nbv</td><td>$editfield</td></tr>";
    }
    ksort($tr);
    foreach($tr as $tr2)
      print implode("",$tr2);

    print "<tr><td colspan=\"2\"><a href=\"javascript:checkall('art',true);\">Tout cocher</a> <a href=\"javascript:checkall('art',false);\">Tout décocher</a></td></tr>
    </table>";

    $sql="";
    if($_POST['sfield']!="" && $nbarticles>0){
	if($_POST['sfield']=="reappro") $_POST['sfield']="FROM_UNIXTIME(date_appro)";
	ksort($variantes);
	print "<p>Valeurs différentes de ".$fields[$_POST['sfield']]."</p>
	<table border=\"1\" cellspacing=\"0\">
	<tr><td></td><td>Sélection</td><td>Tout</td></tr>";
	foreach($variantes as $value=>$artcodes){
	    $autres=$DB_site2->query_first("SELECT COUNT(*) FROM article WHERE ".$_POST['sfield']."='".addslashes($value)."'");
	    if($value!="") $sql.="INSERT INTO traduction SET path='/article/".$_POST['sfield']."/".str_replace(array("'","\"","/",","),"",str_replace(" ","_",$value))."', FR='".mysqli_real_escape_string($DB_site2->link_id,$value)."';\n";
	    if($value=="") $value="<i>(Vide)</i>";
	    print "<tr><td><input type=\"checkbox\" name=\"group[]\" onclick=\"checkall('art',this.checked,'".implode(",",array_keys($artcodes))."');\">$value</td><td align=\"right\">".count($artcodes)."</td><td align=\"right\">".$autres[0]."</td></tr>\n";
	}
	print "</table>";
    }
    

//     print "<textarea id=\"sql\" cols=\"150\" rows=\"10\">$sql</textarea>";
    print "</div>";
}

print "</form>";

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
        </tr>";


include "./footer.php";


function prixport($poids,$prix){
  global $DB_site2;
	$requete=$DB_site2->query("SELECT * FROM fraisport WHERE paysid=57 AND ".(1000*$poids)." BETWEEN poiddebut AND poidfin");
	$port=$DB_site2->fetch_array($requete);
	if($port==false) return array(0,0);
	if($prix>$port['gratuit']) $port['prix']=0;
	$port['prix']=round($port['prix']/1.196,2);
	
	return $port;
}

function achatgarantie($artid){
  global $DB_site2;
	$requete=$DB_site2->query("SELECT achat FROM article_caractval ac INNER JOIN caracteristiquevaleur cv USING (caractvalid) WHERE caractvalid IN (9,10,11,134,154,155) AND artid=".$artid." ORDER BY cv.position LIMIT 1");
	$return=$DB_site2->fetch_array($requete);
	return round($return['achat'],2);
}

function displaysscat($catid){
    global $DB_site2;
    global $list;
    $return="";

    $cat=$DB_site2->query_first("SELECT * FROM categorie WHERE catid=".$catid);
    
    $sscatlist="";
    $clickall=array($cat['catid']);

    $ss2cats=$DB_site2->query("SELECT catid,libelleFR FROM categorie WHERE parentid=$cat[catid] ORDER BY position");
    if($DB_site2->num_rows($ss2cats)>0){
      $sscatlist.="<div style=\"margin-left:20px;border-left:solid thin black;\">";
      while($ss2cat=$DB_site2->fetch_array($ss2cats)){
	  $sscatlist.="__".displaysscat($ss2cat['catid']);
	  $clickall[]=$ss2cat['catid'];
	  if(in_array($ss2cat[catid],$_POST['cat'])) $list.=", ".$ss2cat['libelleFR'];
      }
      $sscatlist.="</div>";
    }

    
    $return.="<input type=\"hidden\" id=\"catname_$cat[catid]\" value=\"$cat[libelleFR]\"><input type=\"checkbox\" name=\"cat[]\" onclick=\"updcat();\" value=\"$cat[catid]\"".(in_array($cat[catid],$_POST['cat'])?" checked":"").">".$cat['libelleFR'];
    if(count($clickall)>1) $return.=" <a href=\"javascript:checkall('cat',true,'".implode(',',$clickall)."')\">Cocher la catégorie</a> <a href=\"javascript:checkall('cat',false,'".implode(',',$clickall)."')\">Décocher la catégorie</a>";
    $return.="<br>".$sscatlist;

    return $return;
}

function arborescence($parentid){
	global $DB_site2,$select,$niveau,$libelle,$catid,$libellecat;
	$niveau.="--->";
	$requete_arbo=" select $libelle,catid from categorie where parentid='$parentid' ORDER BY $libelle ";
	$arbos=$DB_site2->query($requete_arbo);
	while ($arbo=$DB_site2->fetch_array($arbos)) {
		if($catid==$arbo[catid]) {
			$selected="selected";
			$libellecat=$arbo[$libelle];
		} else {
			$selected="";
		}
		$select.="<option value=\"$arbo[catid]\" $selected>$niveau $arbo[$libelle]</option>";
		arborescence($arbo[catid]);
	}
	$niveau=substr($niveau , 0 , strlen($niveau)-4 );
	return ($select);
}

?>