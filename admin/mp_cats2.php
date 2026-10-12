<?php
require_once("admin_global.php");

require_once("./header.php");
require_once("./menu.php");
require_once("../class/DAOarticle.class.php");
require_once("../class/garantie.class.php");
require_once("../class/DAOgarantie.class.php");
require_once("../class/frais_port.php");

$site_code="base";
if(isset($_POST['site_code'])) $site_code=$_POST['site_code'];

function nice($var, $color = 'black'){
          echo "<div style=\"color:".$color.";\">";
          echo "<p style=\"color:red; font-weight:bold;\">Contenu de la variable :</p>";
          if(is_array($var) || is_object($var)){
                    echo "<pre>";
                    print_r($var);
                    echo "</pre>";   
          } else {
                    var_dump($var);
          }
          echo "</div>";
}

function nicex($var){
          nice($var);
          exit();
}

$keys = array("artcode"=>"Code article",
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
$marketplaces=array("rdc"=>$rdc,"amazon"=>$amazon,"priceminister"=>$priceminister,"google"=>$google,"windil"=>$windil,"pixmania"=>$pixmania,"fnac"=>$fnac,"cdiscount"=>$cdiscount);

$limites=array("google"=>array("title"=>70),
"windil"=>array("Titre"=>60),
"priceminister"=>array("Description"=>3000),
"amazon"=>array("BulletPoint1"=>50,"BulletPoint2"=>50,"BulletPoint3"=>50,"BulletPoint4"=>50,"BulletPoint5"=>50,"SearchTerms1"=>50,"SearchTerms2"=>50,"SearchTerms3"=>50,"SearchTerms4"=>50,"SearchTerms5"=>50,"Description"=>2000,"ProductName"=>500),
"fnac"=>array("titre"=>255,"description"=>4000,"caracteristiques"=>4000),
"cdiscount"=>array("libelle_court"=>30,"libelle_long"=>50,"description"=>5000));

$tags=array("fnac"=>array("description"=>"<br>","caracteristiques"=>"<br>"),
"pixmania"=>array("description"=>"<br>")
);

if(isset($_POST['mp'])){
    $DB_site2->query("DELETE FROM market_place_catid WHERE market_place='".$_POST['mp']."' AND catid=".$_POST['catid']);
    foreach($_POST as $name=>$value){
	if(substr($name,0,6)=="field_"){
	    $name=substr($name,6);
	    $DB_site2->query("INSERT INTO market_place_catid SET market_place='".$_POST['mp']."', champ='".$name."', valeur='".$value."', catid=".$_POST['catid']);
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

//Liste des catégories
if(!isset($action)){
  $cats=$DB_site2->query("SELECT catid,libelleFR FROM categorie WHERE parentid=0 ORDER BY position");
  while($cat=$DB_site2->fetch_array($cats)){
    $mpconfig="";
    $req=$DB_site2->query("SELECT DISTINCT market_place FROM market_place_catid WHERE catid=".$cat[catid]);
    while($mp=$DB_site2->fetch_array($req)) $mpconfig.=" ".$mp[0];
 
    print "<br>".displaysscat($cat['catid']);
  }
}else{
     
     //Modification des contenus pour une catégorie
     if($action=="editer" && isset($catid)){
	$cat=$DB_site2->query_first("SELECT * FROM categorie WHERE catid=".$catid);
	print "<p>Modifier ".$cat['libelleFR']." <a href=\"mp_cats2.php\">Retour</a> &nbsp; <a href=\"mp_cats2.php?action=articles&catid=$catid\">Voir les articles</a></p>";
	
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
    
    //Paramétrage des produits
     if($action == "articles" && isset($catid)){
          
          $change = $devises->getchange();
          $change['EUR']=1;
          
          $sql = "SELECT *";
          $sql .= " FROM categorie";
          $sql .= " WHERE catid = ".$catid;
          $cat = $DB_site2->query_first($sql);
          
          echo "<p>".$cat['libelleFR']." <a href=\"mp_cats2.php?action=editer&catid=$catid\">Configurer</a> &nbsp; <a href=\"mp_cats2.php\">Retour à la liste</a></p>";

          $artcodes = array();
          $lignes   = "";
          
          $sql = "SELECT a.*, af.trspt, af.ecopartht, af.devise";
          $sql .= " FROM article a";
          $sql .= " INNER JOIN article_fournisseur af ON (af.artid = a.artid)";
          $sql .= " WHERE a.catid = ".$catid;
          $sql .= " AND a.active <> '2'";
          $sql .= " GROUP BY a.artcode";
          $sql .= " ORDER BY a.artcode";
          $requete = $DB_site2->query($sql);
          while($row = $DB_site2->fetch_array($requete)){
               $tabArticles[] = $row;
          }
?>

          <table style="width: 100%;"cellspacing="0">
               <tr>
                    <td colspan="5">
                         <div style="width: 250px; background-color: #cccccc; padding: 5px 10px; text-align: left; border: 1px solid #363e42;">
                              <p style="font-weight: bold; margin: 0; margin: 7px 0;">Filtrer par :</p>
                              <select style="width: 250px; margin: 3px 0;">
                                   <option>Catégorie</option>
                              </select>
                              <select style="width: 250px; margin: 3px 0;">
                                   <option>Marque</option>
                              </select>
                              <select style="width: 250px; margin: 3px 0;">
                                   <option>Market Place</option>
                              </select>
                         </div>
                    </td>
                    <td style="border-left: 1px solid #ccc; text-align: center; vertical-align: middle; font-weight: bold;" colspan="2">RDC<br><img src="/admin/images/logos/logo-rdc.jpg" style="margin:10px 5px;"><br><input type="checkbox"></td>
                    <td style="border-left: 1px solid #ccc; text-align: center; vertical-align: middle; font-weight: bold;" colspan="2">Fnac<br><img src="/admin/images/logos/logo-fnac.jpg" style="margin:10px 5px;"><br><input type="checkbox"></td>
                    <td style="border-left: 1px solid #ccc; text-align: center; vertical-align: middle; font-weight: bold;" colspan="2">Amazon<br><img src="/admin/images/logos/logo-amazon.jpg" style="margin:10px 5px;"><br><input type="checkbox"></td>
                    <td style="border-left: 1px solid #ccc; text-align: center; vertical-align: middle; font-weight: bold;" colspan="2">PriceM<br><img src="/admin/images/logos/logo-priceminister.jpg" style="margin:10px 5px;"><br><input type="checkbox"></td>
                    <td style="border-left: 1px solid #ccc; text-align: center; vertical-align: middle; font-weight: bold;" colspan="2">Ebay<br><img src="/admin/images/logos/logo-ebay.jpg" style="margin:10px 5px;"><br><input type="checkbox"></td>
                    <td style="border-left: 1px solid #ccc; text-align: center; vertical-align: middle; font-weight: bold;" colspan="2">Redoute<br><img src="/admin/images/logos/logo-redoute.jpg" style="margin:10px 5px;"><br><input type="checkbox"></td>
                    <td style="border-left: 1px solid #ccc; text-align: center; vertical-align: middle; font-weight: bold;" colspan="2">Cdiscount<br><img src="/admin/images/logos/logo-cdiscount.jpg" style="margin:10px 5px;"><br><input type="checkbox"></td>
                    <td style="border-left: 1px solid #ccc; text-align: center; vertical-align: middle; font-weight: bold;" colspan="2">GaleriesL.<br><img src="/admin/images/logos/logo-galeries.jpg" style="margin:10px 5px;"><br><input type="checkbox"></td>
                    <td style="border-left: 1px solid #ccc; text-align: center; vertical-align: middle; font-weight: bold;" colspan="2">Pixmania<br><img src="/admin/images/logos/logo-pixmania.jpg" style="margin:10px 5px;"><br><input type="checkbox"></td>
                    <td style="border-left: 1px solid #ccc; text-align: center; vertical-align: middle; font-weight: bold;" colspan="2">Mister GD<br><img src="/admin/images/logos/logo-mistergd.jpg" style="margin:10px 5px;"><br><input type="checkbox"></td>
               </tr>
               <tr>
                    <td colspan="3" style="padding: 2px 5px; text-align: right;">PV TTC<br><strong>Marge %</strong></td>
                    <td style="padding: 2px 5px; text-align: center;"><strong>Stock</strong><br>Date réappro<br>Visible<br>Commandable</td>
                    <td style="padding: 2px 5px; text-align: right; width: 55px;"><strong>Com</strong><br><span style="color: #65b71a; font-weight: bold;">En ligne</span><br>Total</td>
                    <td colspan="2" style="border-left: 1px solid #ccc;text-align:center;"><span style="border: 1px solid; padding: 0 3px;">12%</span><br><span style="color: #65b71a; font-weight: bold;">(110)</span><br>(110)</td>
                    <td colspan="2" style="border-left: 1px solid #ccc;text-align:center;"><span style="border: 1px solid; padding: 0 3px;">12%</span><br><span style="color: #65b71a; font-weight: bold;">(110)</span><br>(110)</td>
                    <td colspan="2" style="border-left: 1px solid #ccc;text-align:center;"><span style="border: 1px solid; padding: 0 3px;">12%</span><br><span style="color: #65b71a; font-weight: bold;">(110)</span><br>(110)</td>
                    <td colspan="2" style="border-left: 1px solid #ccc;text-align:center;"><span style="border: 1px solid; padding: 0 3px;">12%</span><br><span style="color: #65b71a; font-weight: bold;">(110)</span><br>(110)</td>
                    <td colspan="2" style="border-left: 1px solid #ccc;text-align:center;"><span style="border: 1px solid; padding: 0 3px;">12%</span><br><span style="color: #65b71a; font-weight: bold;">(110)</span><br>(110)</td>
                    <td colspan="2" style="border-left: 1px solid #ccc;text-align:center;"><span style="border: 1px solid; padding: 0 3px;">12%</span><br><span style="color: #65b71a; font-weight: bold;">(110)</span><br>(110)</td>
                    <td colspan="2" style="border-left: 1px solid #ccc;text-align:center;"><span style="border: 1px solid; padding: 0 3px;">12%</span><br><span style="color: #65b71a; font-weight: bold;">(110)</span><br>(110)</td>
                    <td colspan="2" style="border-left: 1px solid #ccc;text-align:center;"><span style="border: 1px solid; padding: 0 3px;">12%</span><br><span style="color: #65b71a; font-weight: bold;">(110)</span><br>(110)</td>
                    <td colspan="2" style="border-left: 1px solid #ccc;text-align:center;"><span style="border: 1px solid; padding: 0 3px;">12%</span><br><span style="color: #65b71a; font-weight: bold;">(110)</span><br>(110)</td>
                    <td colspan="2" style="border-left: 1px solid #ccc;text-align:center;"><span style="border: 1px solid; padding: 0 3px;">12%</span><br><span style="color: #65b71a; font-weight: bold;">(110)</span><br>(110)</td>
               </tr>
               <tr style="background-color: #363e42;">
                    <td colspan="4" style="color: white; padding: 2px 3px; text-align: left;"><strong>Catégorie</strong> : Cave à vin Vieillissement</td>
                    <td style="color: white; padding: 2px 0; text-align: center;">Ventes</td>
                    <td colspan="2" style="color: white; padding: 2px 3px; text-align: center;">110</td>
                    <td colspan="2" style="color: white; padding: 2px 3px; text-align: center;">8</td>
                    <td colspan="2" style="color: white; padding: 2px 3px; text-align: center;">52</td>
                    <td colspan="2" style="color: white; padding: 2px 3px; text-align: center;">110</td>
                    <td colspan="2" style="color: white; padding: 2px 3px; text-align: center;">110</td>
                    <td colspan="2" style="color: white; padding: 2px 3px; text-align: center;">110</td>
                    <td colspan="2" style="color: white; padding: 2px 3px; text-align: center;">110</td>
                    <td colspan="2" style="color: white; padding: 2px 3px; text-align: center;">110</td>
                    <td colspan="2" style="color: white; padding: 2px 3px; text-align: center;">110</td>
                    <td colspan="2" style="color: white; padding: 2px 3px; text-align: center;">110</td>
               </tr>
          <?php
          $nb = 1;
          $cnt = 0;
          foreach($tabArticles as $l_article){
               //Récupération de la marque
               $sql = "SELECT libelle";
               $sql .= " FROM marque m, article_marque am";
               $sql .= " WHERE m.marqueid = am.marqueid";
               $sql .= " AND am.artid = ".$l_article['artid'];
               $result = $DB_site2->query_first($sql);
               $article_marque = $result['libelle'];
               
               //Récupération du status visible/invisible
               if($l_article['active'] == 1){
                    $article_visible = '<span style="color: #65b71a; font-weight: bold;">Visible</span>';
               } else {
                    $article_visible = '<span style="color: red;">Invisible</span>';
               }
               
               //Récupération du status commandable/non commandable
               if($l_article['commandable'] == 1){
                    $article_commandable = '<span style="color: #65b71a; font-weight: bold;">Commandable</span>';
               } else {
                    $article_commandable = '<span style="color: red;">Non commandable</span>';
               }
               
               //Récupération de la liste des garanties associés au produit, pour la France
               $garantie_euro = 0;
               $lesGaranties  = DAOgarantie::loadMaxGarantieArtByPays($DB_site2,$site_code,'FR',$sites[$site_code]['paysid_ref'],$l_article['artid']);

               //Calcul du prix d'achat total (garantie + extension)
               $i=0;
               foreach ($lesGaranties as $laGarantie) {
                    $i++;
                    if ($i==1) {
                         $garantie_euro = $laGarantie->paht/$change[$laGarantie->devise];
                    }
                    if ($laGarantie->cumul == 1) {
                         $garantie_euro += $laGarantie->paht/$change[$laGarantie->devise];
                    }
               }
               
               //Récupération des frais de port
               $paht_euro      = $l_article['paht'] / $change[$l_article['devise']];
               $frais_port     = new frais_port($DB_site2, array($l_article['artid'] => 1), $sites[$site_code]['paysid_ref'], $l_article['prix'], $paht_euro);
               $prix_ht_euro   = $l_article['prixht'] / $change[$sites[$site_code]['devise']];
               $prixtrspt_euro = $l_article['trspt'] / $change[$l_article['devise']];
               $ecopartht_euro = $l_article['ecopartht'] / $change[$l_article['devise']];
               $margeHT = ($prix_ht_euro + $frais_port->prixht) - ($paht_euro + $prixtrspt_euro + $ecopartht_euro + $garantie_euro + $frais_port->paht);
               $margePC = round(($margeHT * 100) / ($prix_ht_euro + $frais_port->prixht),2);
               
          ?>
               <tr <?php echo $cnt%2 == 0 ? 'style="background-color:#e5e5e5;"' : ''; ?>>
                    <td><img src="/configurations/www.ma-cave-a-vin.fr/images/produits/br/<?php echo $l_article['artid']; ?>.jpg" style="max-width: 25px;"></td>
                    <td style="padding:0 3px;"><?php echo $l_article['artcode']; ?><br>(<?php echo $l_article['artcode_eurocave']; ?>)<br><?php echo $l_article['gammeFR']; ?><br><strong><?php echo $article_marque; ?></strong></td>
                    <td style="padding:0 3px; text-align: center; width: 80px;"><?php echo number_format($l_article['prix'],2,',',' '); ?> ¤<br><strong><?php echo number_format($margePC,2,',',' '); ?>%</strong><br><strong><?php echo number_format($margeHT,2,',',' '); ?> ¤</strong></td>
                    <td style="padding:0 3px; text-align: center; width: 120px;"><strong>12</strong><br>22/10/14<br><?php echo $article_visible; ?><br/><?php echo $article_commandable; ?></td>
                    <td style="text-align: right; padding: 2px 3px;
                        ">N<br>N-1<br>Total</td>
                    <td style="text-align: center; border-left: 1px solid #ccc;">9<br>12<br>21</td>
                    <td style="text-align: center;"><input type="checkbox" name=""><br>999¤<br>25%<br>30¤</td>
                    <td style="text-align: center; border-left: 1px solid #ccc;">9<br>12<br>21</td>
                    <td style="text-align: center;"><input type="checkbox" name=""><br>999¤<br>25%<br>30¤</td>
                    <td style="text-align: center; border-left: 1px solid #ccc;">9<br>12<br>21</td>
                    <td style="text-align: center;"><input type="checkbox" name=""><br>999¤<br>25%<br>30¤</td>
                    <td style="text-align: center; border-left: 1px solid #ccc;">9<br>12<br>21</td>
                    <td style="text-align: center;"><input type="checkbox" name=""><br>999¤<br>25%<br>30¤</td>
                    <td style="text-align: center; border-left: 1px solid #ccc;">9<br>12<br>21</td>
                    <td style="text-align: center;"><input type="checkbox" name=""><br>999¤<br>25%<br>30¤</td>
                    <td style="text-align: center; border-left: 1px solid #ccc;">9<br>12<br>21</td>
                    <td style="text-align: center;"><input type="checkbox" name=""><br>999¤<br>25%<br>30¤</td>
                    <td style="text-align: center; border-left: 1px solid #ccc;">9<br>12<br>21</td>
                    <td style="text-align: center;"><input type="checkbox" name=""><br>999¤<br>25%<br>30¤</td>
                    <td style="text-align: center; border-left: 1px solid #ccc;">9<br>12<br>21</td>
                    <td style="text-align: center;"><input type="checkbox" name=""><br>999¤<br>25%<br>30¤</td>
                    <td style="text-align: center; border-left: 1px solid #ccc;">9<br>12<br>21</td>
                    <td style="text-align: center;"><input type="checkbox" name=""><br>999¤<br>25%<br>30¤</td>
                    <td style="text-align: center; border-left: 1px solid #ccc;">9<br>12<br>21</td>
                    <td style="text-align: center;"><input type="checkbox" name=""><br>999¤<br>25%<br>30¤</td>
                    
               </tr>
          <?php
               $cnt++;
               $nb++;
               if($nb == 10){
                    exit;
               }
          }
          
          ?>          
          </table>
          
          <form name="modifenabled" method="post">
               <input type="hidden" name="artcode">
               <input type="hidden" name="mpname">
               <input type="hidden" name="enabled">
               <input type="hidden" name="artcodes" value="<?php echo implode("','",$artcodes); ?>>
          </form>
          
          <table>
               <thead style="font-weight:bold; text-align:center;">
                    <tr>
                         <td>Article</td>
<?php
          
          foreach($marketplaces as $mp => $fields){
?>               
               <td><?php echo $mp; ?><br><input type="checkbox" name="checkall<?php echo $mp; ?>" onclick="checkall('<?php echo $mp; ?>')"></td>
<?php               
          }
          
?>
               </thead>
               <?php echo $lignes; ?>
          </table>
          <p>Même cochés, les articles inactifs n'apparaitront pas dans l'export</p>
<?php

     }
     
}

?>
                                                                                </td>
                                                                           </tr>   
                                                                      </table>    
                                                                      </font>
                                                                 </td>
                                                                 <td background="images2/cadre_r4_c10.gif" width="3" height="100%" border="0" alt=""></td>
                                                            </tr>
                                                       </table>
                                                  </td>
                                             </tr>
                                             <tr>
                                                  <td>
                                                       <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                            <tr>
                                                                 <td rowspan="2"><img name="cadre_r6_c2" src="images2/cadre_r6_c2.gif" width="10" height="26" border="0" alt=""></td>
                                                                 <td align="center" height="23" width="100%"></td>
                                                                 <td rowspan="2"><img name="cadre_r6_c9" src="images2/cadre_r6_c9.gif" width="8" height="26" border="0" alt=""></td>
                                                            </tr>
                                                            <tr>
                                                                 <td background="images2/cadre_r7_c4.gif" width="100%" height="3"></td>
                                                            </tr>
                                                       </table>
                                                  </td>
                                             </tr>
                                        </table>
                                   </td>
                              </tr>
                              <tr>
                              </tr>
                         </table>
                    </td>
               </tr>
          </table>
     </td>
</tr>
<?php
          
include_once("./footer.php");

function displaysscat($catid){
     
     global $DB_site2;
     $return   = "";
     $mpconfig = "";
     
     $sql = "SELECT *";
     $sql .= " FROM categorie";
     $sql .= " WHERE catid=".$catid;
     $cat = $DB_site2->query_first($sql);
     
     $sql = "SELECT DISTINCT market_place";
     $sql .= " FROM market_place_catid";
     $sql .= " WHERE catid = ".$catid;
     $req = $DB_site2->query($sql);
     
     while($mp = $DB_site2->fetch_array($req)){
          
          $mpconfig .= " <a href=\"mp_cats2.php?action=editer&catid=".$cat['catid']."#mp_".$mp[0]."\" style=\"color:green;\">".$mp[0]."</a>";
          
     }

     $return .= "<a href=\"mp_cats2.php?action=editer&catid=".$cat['catid']."\">".$cat['libelleFR']."</a>$mpconfig<br>";
    
     $sql = "SELECT catid, libelleFR";
     $sql .= " FROM categorie";
     $sql .= " WHERE catid='".$catid."'";
     $sql .= " ORDER BY position";
     $sscats = $DB_site2->query($sql);
     
     while($sscat = $DB_site2->fetch_array($sscats)){
          
          $sql = "SELECT catid";
          $sql .= " FROM categorie";
          $sql .= " WHERE parentid = ".$sscat['catid'];
          $sql .= " ORDER BY position";
          $ss2cats = $DB_site2->query($sql);
          
          if($DB_site2->num_rows($ss2cats) > 0){
               
               $return .= "<div style=\"margin-left:10px; border-left:solid thin black;\">";
               while($ss2cat = $DB_site2->fetch_array($ss2cats)){
                    
                    $return .= "__".displaysscat($ss2cat['catid']);
                    
               }
               
               $return .= "</div>";
          
          }
     
     }

     return $return;
}

?>