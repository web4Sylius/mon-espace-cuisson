<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

include $rootpath."class/DAOarticle.class.php";
include $rootpath."class/garantie.class.php";
include $rootpath."class/DAOgarantie.class.php";
include($rootpath."class/frais_port.php");
include "../class/fonctions.php";
include $rootpath."configurations/".$host."/class/cloudflare.class.php";

$tauxtva=1.2;

$filter_langue=array_diff($langues,$langue_access)!=array();
date_default_timezone_set ( "Europe/Paris" );
array_push($langues, 'AA');

$change=$devises->getchange();
$change['EUR']=1;
$extensions_valides = array('jpg');
$maxsize = 10485760;
$maxwidth= 1028;
$maxheight=600;

foreach($change as $devise_code=>$value) 
  $jschange.="change[\"".$devise_code."\"]=".$value.";\n";
  
// Ajout de données dans une vente flash
if(isset($_POST['action2'])){
    if($_POST['action2']=="edit0"){
        $path= "../ckeditor/kcfinder/upload/images/";
        $textid=substr($_GET['action'],4,1);
        $content = <<<HTML
            <h1>$_POST[h1]</h1>
			<p class="titreModif visible-xs visible-lg">$_POST[vfname]</p>
			<p class="titreModifResp visible-sm visible-md">$_POST[vfname]</p>
			<p class="soustitre">$_POST[desc]</p>
HTML;
        $DB_site2->query("UPDATE vente_flash SET text".$textid.$_GET['l']."='".mysqli_real_escape_string($DB_site2->link_id,$content)."' WHERE site_code='".$_POST['site_code']."' AND id='".$_POST['id']."'");
        $upload = move_uploaded_file($_FILES['upload_image']['tmp_name'], $path.$_FILES['upload_image']['name']);
        $_GET['action']="editer";
    }

    if($_POST['action2']=="edit1" || $_POST['action2']=="edit2"){
        $dossier = "vente_flash";
        if($_POST['action2'] == "edit2") $dossier = "vente_flash_off";
        $pathimg = "../configurations/".$host."/images/templates/base/".$_GET['l']."/".$dossier."/";
        if(isset($_FILES['upload_image']) && $_FILES['upload_image']['name'] != '' ){
            //on vérifie si c'est bien une photo
            $extension_upload = strtolower(pathinfo($_FILES['upload_image']['name'], PATHINFO_EXTENSION));
            if ( in_array($extension_upload,$extensions_valides) ){
                if ($_FILES['upload_image']['size'] < $maxsize){
                    $image_sizes = getimagesize($_FILES['upload_image']['tmp_name']);
                    $widthDest = $image_sizes[0];
                    $heightDest = $image_sizes[1];
                    if ($heightDest > $maxwidth) $widthDest = $maxwidth;
                    if ($heightDest > $maxheight) $heightDest = $maxheight;
                    $nom = $pathimg."vente_flash.".$extension_upload;
                    $upload = resizeImage($_FILES['upload_image']['tmp_name'],$widthDest,$heightDest,$nom);
                    //$upload = move_uploaded_file($_FILES['upload_image']['tmp_name'], $pathimg."vente_flash.".$extension_upload);
                    if($upload) $image = $extension_upload; 
                }else{
                    echo "<script>alert('attention le fichier est trop lourd');</script>";
                }
            }else{
                if($_FILES['upload_image']['size'] != '0')
                echo "<script>alert('attention le type de fichier n\'est pas autorisé');</script>";
            }
        }
        $_GET['action']="editer";
    }

    if($_POST['action2']=="dates"){
        $du=$au=0;
        if($_POST['du']!=""){
            $sp=preg_split('/[- :\/h]/',$_POST['du']);
            $du=mktime($sp[3],$sp[4],0,$sp[1],$sp[0],$sp[2]);
        }

        if($_POST['au']!=""){
            $sp=preg_split('/[- :\/h]/',$_POST['au']);
            $au=mktime($sp[3],$sp[4],0,$sp[1],$sp[0],$sp[2]);
        }

        if($du+$au>0) $DB_site2->query("UPDATE vente_flash SET du=$du, au=$au WHERE site_code='".$_POST['site_code']."' AND id='".$_POST['id']."'");
    }

    if($_POST['action2']=="addarticle"){
        //on vérifie si l'article n'est pas déjà présent dans la VF
        $exist = $DB_site2->query("SELECT * FROM vente_flash_article WHERE artcode ='".$_POST['artcode']."'");
        if($DB_site2->num_rows($exist) == 0){

          $DB_site2->query("INSERT INTO vente_flash_article SET vfid=".$_POST['vfid'].", artcode='".$_POST['artcode']."', prix=".floatval($_POST['prixflash']).", remise='".$_POST['remise_calc']."', marge='".$_POST['marge_calc']."', stock=".intval($_POST['stock']));
        }else{
          echo "<script>alert('L\'article éxiste déjà dans la Vente Flash')</script>";
        }
        
        unset($_GET['action2']);
        
        $_GET['action']="editer";
    }
	
	if($_POST['action2']=="modif_prix"){
		$urls=array();
		$urls[]="https://www.ma-cave-a-vin.fr/vente_flash.html";
		$articles = $DB_site2->query("SELECT * FROM vente_flash_article ");
		while($article = $DB_site2->fetch_array($articles)){   
			$temp="prixvf_".$article[artcode];
			if($_POST[$temp]!=$article[prix]){
				// echo "<script>alert('".$article[artcode]." : prix changé de ".$article[prixflash]." a ".$_POST[$temp]." ')</script>";
				$DB_site2->query("UPDATE vente_flash_article SET prix=".$_POST[$temp]." WHERE id=".$article['id']);
				$cloud_article=$DB_site2->query_first("SELECT artid, libelleFR, libelleEN, catid FROM article WHERE artcode='".$article[artcode]."'");
				$cloud_marque=$DB_site2->query_first("SELECT marque.libelle, marque.urlFR, marque.urlEN, marqueid FROM marque INNER JOIN article_marque USING(marqueid) WHERE article_marque.artid=".$cloud_article[artid]);
				$urls[]="https://www.ma-cave-a-vin.fr/".url_rewrite($cloud_article['libelleFR']."-".$cloud_marque['libelle'])."-art".$cloud_article[artid].".htm";
				$urls[]="https://pro.ma-cave-a-vin.fr/".url_rewrite($cloud_article['libelleFR']."-".$cloud_marque['libelle'])."-art".$cloud_article[artid].".htm";
				$urls[]="https://www.my-wine-cabinet.com/".url_rewrite($cloud_article['libelleEN']."-".$cloud_marque['libelle'])."-art".$cloud_article[artid].".htm";
				$urls[]="https://pro.my-wine-cabinet.com/".url_rewrite($cloud_article['libelleEN']."-".$cloud_marque['libelle'])."-art".$cloud_article[artid].".htm";
			}
		}
		
		$cloud = new cloudflare_api($DB_site2,$rootpath);
		//$cloud->zone_id=$zone_id;
		while (list($key, $val) = each($urls)) {
			if($val!=""){
				if(substr($val,0,28)=="https://www.ma-cave-a-vin.fr" || substr($val,0,28)=="https://pro.ma-cave-a-vin.fr"){
					$zoneid=$cloudflare_zone_id_mcav;
				} else {
					$zoneid=$cloudflare_zone_id_mwc;
				}
				$results = $cloud->purgeFiles($cloudflare_email, $cloudflare_api, $zoneid, array($val) );
				if($results->success === true) {
				//	echo "<script>alert('URL : ".$val." : Execution realisee avec succes.')</script>";
				}
				if($results->success === false) {
					echo "<script>alert('URL : ".$val." : ERREUR D EXECUTION')</script>";
				}
			}
		}
		echo "<script>alert('prix mis a jour.')</script>";
		unset($_GET['action2']);
        $_GET['action']="";
		//$_GET['site_code']="base";
		//$_GET['id']="1";
    }
}

// suppression de données dans une vente flash
if($_GET['action2']=="del"){
    $DB_site2->query("DELETE FROM vente_flash_article WHERE id=".$_GET['del']);
    unset($_GET['action2']);
    $_GET['action']="editer";
}

// ajout de commentaire/langue dans les  ventes flash
if(isset($_POST['meta_langue'])){
    $languemeta=$_POST['meta_langue'];
    $vfid=intval($_POST['vfid']);
    $DB_site2->query("UPDATE vente_flash SET ref_description$languemeta='".$_POST['description']."', ref_title$languemeta='".$_POST['title']."' WHERE id=".$vfid);
}

if(isset($_POST['txtcb'])){
    echo $_POST['txtcb'];
}

?>

<style>
.recap_vf { border-collapse: collapse; border: solid thin black; width: 60%; text-align: center; }
.recap_vf tr:first-child { font-weight: bold; background-color: #395870; line-height: 18px; }
.recap_vf tr:first-child td { color: white; }
.recap_vf img { vertical-align: middle; width: 20px; }

.hist_vf { border-collapse: collapse; border: solid thin black; width: 70%; text-align: center; }
.hist_vf tr:first-child { font-weight: bold; background-color: #395870; line-height: 25px; }
.hist_vf tr:first-child td { color: white; }
.hist_vf td:first-child { font-weight: bold; }

.vf_articles { width: 100%; border-collapse: collapse; border: solid thin black; }
.vf_articles tr:first-child { font-weight: bold; background-color: #395870; line-height: 25px; }
.vf_articles tr:first-child td { color: white; }
.vf_articles td:first-child { font-weight: bold; }
.vf_articles tr:nth-child(even) { background-color: #f5f5f5; }
.vf_articles td { padding: 5px 10px; }
.vf_articles td:nth-child(3), td:nth-child(4) { text-align: center; }

.tabProduit { border: solid thin black; border-collapse: collapse; }
.tabProduit tr:nth-child(2n) { background-color: #f5f5f5; }
.tabProduit td:nth-child(2) { font-weight: bold; }

.marge_ca { border-collapse: collapse; border: solid thin black; float: left; margin-left: 10px; text-align: center; }
.marge_ca tr:first-child { font-weight: bold; background-color: #395870; }
.marge_ca tr:first-child td { color: white; }
.marge_ca td { border: thin solid black; }

.divlangue { display:none; width:900px; margin:auto; }
.onglets { text-align:center; margin:auto; }
.onglet { display: inline-block; margin: 0 0 -1px; font-weight: 600; text-align: center; color: #bbb; border: 1px solid transparent; cursor: pointer; padding: 10px 15px; }
.onglet:hover { color: #888; }
.onglet_on { cursor: pointer; color: #555; border: 1px solid #ddd; border-top: 2px solid orange; border-bottom: 1px solid #fff; display: inline-block; margin: 0 0 -1px; padding: 10px 15px; font-weight: 600; text-align: center; }
.onglet img, .onglet_on img { vertical-align: sub; }

.inputvf { background-color: white; color: black; border: none; font-weight: bold; }

label { display: block; float: left; width: 110px; text-align: left; }
#artcode { font-weight: bold; text-align: center; }
</style>

<script type="text/javascript" src="../javascripts/host.js"></script>
<script language="javascript">
CKEDITOR.config.filebrowserBrowseUrl = '/ckeditor/kcfinder/browse.php?type=files';
CKEDITOR.config.filebrowserImageBrowseUrl = '/ckeditor/kcfinder/browse.php?type=images';
CKEDITOR.config.filebrowserFlashBrowseUrl = '/ckeditor/kcfinder/browse.php?type=flash';
CKEDITOR.config.filebrowserUploadUrl = '/ckeditor/kcfinder/upload.php?type=files';
CKEDITOR.config.filebrowserImageUploadUrl = '/ckeditor/kcfinder/upload.php?type=images';
CKEDITOR.config.filebrowserFlashUploadUrl = '/kcfinder/upload.php?type=flash';

var change = new Array();
<?php echo $jschange; ?>
var site_code = '<?php echo $_GET['site_code']; ?>';
var site_devise = '<?php echo $sites[$site_code]['devise']; ?>';
var prices = new Array();
var strike_out_prices = new Array();
var buyprices = new Array();
var pahts = new Array();
var ecoparthts = new Array();
var prixtrspt = new Array();
var descriptions = new Array();
var tvas = new Array();
var paopts = new Array();
var fraisport = new Array();
var portoffert = new Array();
var achatport = new Array();
var stocks = new Array();
var artids = new Array();
var activetxt = new Array('<span style="color:red;">Invisible</span>','Visible','<span style="color:red;">Supprimé</span>');

// Recherche d'un ou des produits pour les mettres dans la liste de la vf
function srcprod(){
    if (document.getElementById('src').value != "") {    
        ajaxrequest('marketplace/src.php?active=1&txt='+document.getElementById('src').value,srcprodcb);
        document.getElementById('resultsvf').innerHTML="Recherche en cours";
        document.getElementById('addarticle').style.display='none';
    } else {
        document.getElementById('resultsvf').innerHTML="Veuillez entrer un code";
        document.getElementById('addarticle').style.display='none';
    }
}

// creation du tableau de resultat contenant les articles retournes dans le XML
function srcprodcb(httprequest){
	// recuperation des données XML suite a la requete ajax sur src.php
    var xml=httprequest.responseXML.documentElement;

	// si au moins un article 
    if(xml.getElementsByTagName('prod')[0]){

    // tableau d'affichage des articles a afficher   
    html="<table width='100%' class='tabProduit'>";

    // pour chaque article de la liste, creation d'un tableau[] ou id=code de l'article
    for(i=0;i<xml.getElementsByTagName('prod').length;i++){
          // recuperation de l'element i dans variable prod
	      var prod=xml.getElementsByTagName('prod')[i];

          // recuperation des donnees XML pour l'article en cours
	      var id=prod.getAttribute('id');
	      var deviseachat=prod.getAttribute('devise_achat');
          var description=prod.firstChild.nodeValue;
          var price=Number(prod.getAttribute('price'));
          //var strike_out=Number(prod.getAttribute('strike_out_'+site_code));
          //var buyprice=Number(prod.getAttribute('paht_'+site_code))/change[deviseachat];
          var strike_out=Number(prod.getAttribute('strike_out'));
          var paht=Number(prod.getAttribute('paht'));
          var buyprice=Number(prod.getAttribute('buyprice'));
	      //var ecopartht=Number(prod.getAttribute('ecopartht_'+site_code))/change[deviseachat];
	      var ecopartht=Number(prod.getAttribute('ecopartht'));
	      var tva=Number(prod.getAttribute('tva'));
	      var stock=prod.getAttribute('stockqte');
	      var artid=prod.getAttribute('artid');
	      var visible=prod.getAttribute('visible');
	      var active=prod.getAttribute('active');
	      var marq=prod.getAttribute('marque');
	      var ref=prod.getAttribute('ref');

	      // Creation de la ligne d'affichage pour l'article
          html+="<tr>";
          html+="<td><img src='/configurations/"+myHost(window.location.host)+"/images/produits/"+artid+".jpg' width='30'></td>";
          html+="<td>"+id+"<br>"+ref+"</td>";
          html+="<td>"+marq+"</td>";
          html+="<td>"+description+"/"+strike_out+"/"+buyprice+"/"+paht+"/"+ecopartht+"/"+tva+"<br>En stock : <span style='color:red'>"+stock+"</span></td>";
          html+="<td>"+price+"€</td>";
          html+="<td><a href=javascript:selectprod('"+id+"')><img src='images/icon/edit_add_22.png' width='20'></a></td>";
          html+="</tr>";

          // stockage des infos pour l'article i, tableaux[] ou id=code de l'article
          prices[id]=price;
          strike_out_prices[id]=strike_out;
          buyprices[id]=buyprice;
          pahts[id]=paht;
	      ecoparthts[id]=ecopartht;
	      tvas[id]=tva;
          descriptions[id]=description;
          stocks[id]=stock;
          artids[id]=artid;
	      fraisport[id]=Number(prod.getAttribute('port'));
	      portoffert[id]=Number(prod.getAttribute('portgratuit'));
	      achatport[id]=Number(prod.getAttribute('achatport'));
	      paopts[id]=Number(prod.getAttribute('caract_paht'));
	      prixtrspt[id]=Number(prod.getAttribute('prixtrspt'));
      }
      html+="</table>";
  }else
    html="Pas de résultat";

  document.getElementById('resultsvf').innerHTML=html;
}

// Recuperation du detail d'un produit id = code de l'article
function selectprod(id){
	// Info entete article
    document.getElementsByName('artcode')[0].value=id;
    document.getElementById('prixnormal').innerHTML=prices[id];
    document.getElementById('prixnorm').value=prices[id];
    document.getElementById('stock').value=stocks[id];

    // champs caches detail 
    document.getElementById('arttva').value=(1+tvas[id]/100);
    document.getElementById('portoffert').value=portoffert[id];
    document.getElementById('port_origin').value=fraisport[id];

	// mise a zero du prix flash, en attente de saisie
    document.getElementsByName('prixflash')[0].value=0;

    // premiere ligne du tableau de calcul de marge
    document.getElementById('maarticle').innerHTML=round(pahts[id],2);
    document.getElementById('maeco').innerHTML=ecoparthts[id];
    document.getElementById('maembal').innerHTML=prixtrspt[id];
    document.getElementById('magar').innerHTML=paopts[id];
    document.getElementById('matrspt').innerHTML=achatport[id];

    // deuxieme ligne du tableau de calcul de marge
    //document.getElementById('mvarticle').innerHTML=round((prices[id]/document.getElementById('arttva').value)/change[site_devise],2);
    document.getElementById('mvarticle').innerHTML=round((prices[id]/document.getElementById('arttva').value)/change[site_devise],2);
    // pas d'ecopart defini dans cette ligne du tableau ! document.getElementById('mveco').innerHTML=ecoparthts[id];
	// pas de emballage / transporteur defini dans cette ligne du tableau !
	// pas de garantie defini dans cette ligne du tableau !    
    document.getElementById('mvtrspt').innerHTML=fraisport[id]; // Ajout TSI, n'y etait pas
    
	// troisieme ligne du tableau de calcul de marge
	// pas d'ecopart defini dans cette ligne du tableau ! document.getElementById('mvfeco').innerHTML=ecoparthts[id];
	// pas de emballage / transporteur defini dans cette ligne du tableau !
	// pas de garantie defini dans cette ligne du tableau !    
    document.getElementById('mvftrspt').innerHTML=fraisport[id]; // Ajout TSI, n'y etait pas

    // Modif TSI: On efface plus la liste, on la cache... pour pouvoir la réafficher si besoin
    //document.getElementById('resultsvf').innerHTML='';
    document.getElementById('resultsvf').style.display='none';
    document.getElementById('addarticle').style.display='block';

    // appel du calcul de marge
    calcmarge();
}

function backtolist(){
	document.getElementById('resultsvf').style.display='block';
    document.getElementById('addarticle').style.display='none';
}

function calcmarge(){

	/*var prixport=fraisport[id];*/
	/*var prixport=document.getElementById('port_origin').value;*/
	var garantie=0;
	/*document.getElementById('mvtrspt').innerHTML=prixport;
	document.getElementById('mvftrspt').innerHTML=prixport;*/
	document.getElementById('mvfarticle').innerHTML=round((Number(document.getElementsByName('prixflash')[0].value)/document.getElementById('arttva').value)/change[site_devise],2);
	
	var totalachat=Number(document.getElementById('magar').innerHTML)+Number(document.getElementById('maembal').innerHTML)+Number(document.getElementById('matrspt').innerHTML)+Number(document.getElementById('maarticle').innerHTML)+Number(document.getElementById('maeco').innerHTML);
	var totalvente=Number(document.getElementById('mvarticle').innerHTML)+Number(document.getElementById('mvtrspt').innerHTML)+Number(document.getElementById('mvgar').innerHTML);
	var totalventeflash=Number(document.getElementById('mvfarticle').innerHTML)+Number(document.getElementById('mvftrspt').innerHTML)+Number(document.getElementById('mvgar').innerHTML);
	var marge=round(totalvente-totalachat,2);
	var pcmarge=round(100*marge/totalvente,2);
	
	color='';
	if(pcmarge<25) color='red';
	
	document.getElementById('margeht').style.color=color;
	document.getElementById('matotal').innerHTML=round(totalachat,2);
	document.getElementById('mvtotal').innerHTML=round(totalvente,2);
	document.getElementById('margeht').innerHTML=marge+' ('+pcmarge+'%)';
	
	var margevf=round(totalventeflash-totalachat,2);
	var pcmargevf=round(100*margevf/totalventeflash,2); 
	var remisevf=round(((Number(document.getElementsByName('prixnorm')[0].value)-Number(document.getElementsByName('prixflash')[0].value))/Number(document.getElementById('prixnorm').value)*100),2);
	
	color='';
	if(pcmargevf<25) color='red';

	document.getElementById('marge_calc').innerHTML=margevf+' ('+pcmargevf+'%)';
	/*document.getElementById('remise_calc').innerHTML=remisevf+'%';*/

	document.getElementById('margevfht').style.color=color;
	document.getElementById('mvftotal').innerHTML=round(totalventeflash,2);
	document.getElementById('margevfht').innerHTML=margevf+' ('+pcmargevf+'%)';

	/* Ajout TSI pour remonter la marge et la remise dans le tableau de vente flash */
	document.getElementById('marge_calc').value=margevf+' ('+pcmargevf+'%)';
	document.getElementById('remise_calc').value=remisevf+'%';

	//alert(margevf+' ('+pcmargevf+'%) / '+ remisevf+'%' + document.getElementById('remise_calc').value);
}

var langues = new Array(<?php echo "'".implode("','",$langues)."'"; ?>);
function switchonglet(langue){
    for(i=0;i<langues.length;i++){
    if(document.getElementById('vf'+langues[i])){
      document.getElementById('vf'+langues[i]).style.display='none';
      document.getElementById('onglet'+langues[i]).className='onglet';
        }
    }

    if(langue!=undefined){
        document.getElementById('vf'+langue).style.display='block';
        document.getElementById('onglet'+langue).className='onglet_on';
    }
}

function notif_close(){
  document.getElementById('alert-box').style.display = "none";
}

// permet de sauvegarder la vente flash et de mettre les infos dans l'historique
function closeit(id){
  if(confirm("Sauvegarder la vente flash ?")){
    var name = prompt("Veuillez nommer la Vente Flash :");
    ajaxrequest('ajax/vf_maj.php?action=close&name='+name+'&id='+id,enditCallback);
  }
}
  
// efface les articles de la liste
function cleanArt(id){
  if(confirm("Effacer la liste de produits ?")){
    ajaxrequest('ajax/vf_maj.php?action=delete&id='+id,enditCallback);
  }
}

//http://localhost.ma-cave-a-vin.fr/admin/vente_flash.php?action2=deleteh&site=base&vfhid=8
// efface un historique vf de la liste
function deleteVfH(id,nom){
  if(confirm(nom+" : Effacer cet historique ?")){
    ajaxrequest('ajax/vf_maj.php?action=deleteh&site=base&vfhid='+id,enditCallback);
  }
}


// retour a la page animations/vente flash
function enditCallback(httpRequest){
  var html = httpRequest.responseText;
  var form = document.createElement('form');
  form.setAttribute('method',"POST");
  form.setAttribute('action',"vente_flash.php");

  var inpt = document.createElement('input');
  inpt.setAttribute('type',"text");
  inpt.setAttribute('name',"txtcb");
  inpt.setAttribute("value", html);

  form.appendChild(inpt);

  form.submit();

  window.location.href = "vente_flash.php";
}

function downloadCSV(filename, datacsv) {

  if(confirm("Export csv "+filename+" ?")){  
        var data, link;
        var csv = datacsv;
        if (csv == null) return;
        
        if (!csv.match(/^data:text\/csv/i)) {
            csv = 'data:text/csv;charset=utf-8,' + csv;
        }
        var encodedUri = encodeURI(csv);
        var link = document.createElement("a");
        
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", filename);
        document.body.appendChild(link); // Required for FF

        link.click();
       
    }
}


</script>

<?php

print "<h1><a href=\"vente_flash.php\">Ventes flash</a></h1><br>";

// Affichage vente flash actuelle et liste des historiques
if(!isset($_GET['action'])){
  
  // Vente flash programmée
  print "<h3>Créer une nouvelle Vente Flash</h3>";

  print "<table class=\"recap_vf\">
          <tr>
            <td with=\"10%\">Site</td>
            <td with=\"25%\">Du</td>
            <td with=\"25%\">Au</td>
            <td with=\"20%\">Etat</td>
            <td with=\"10%\">Editer</td>
            <td with=\"10%\">Web</td>    
          </tr>";

  $req = $DB_site2->query("SELECT * FROM vente_flash");
  while($vf = $DB_site2->fetch_array($req)){
      $du = date("d/m/Y H:i",$vf['du']);
      $au = date("d/m/Y H:i",$vf['au']);
      $nowD = date("d/m/Y H:i",time());
      $now = time();
      
     //print_r ($du." :   ".$nowD."   : ".$au);
  
      if( $now > $vf['du'] && $now < $vf['au']) {
        $statut ="En cours";
        $style ="<td style=\"color:green\">".$statut."</td>" ;
      }
      elseif($now > $vf['au']) {
        $statut = "Terminée";
        $style ="<td style=\"color:red\">".$statut."</td>";
      }
      else {
        $statut = "Prochainement";
        $style = "<td style=\"color:orange\">".$statut."</td>";
      }
  
      print "<tr>
                <td>".$vf['site_code']."</td>
                <td>".$du."</td>
                <td>".$au."</td>
                ".$style."
                <td><a href=\"vente_flash.php?action=editer&site_code=".$vf['site_code']."&id=".$vf['id']."\"><img src=\"images/icon/edit.png\" width=\"20\"></a>&nbsp;&nbsp;</td>
                <td><a href=\"".SITE_URL."/vente_flash.html\" target=\"_blank\"><img src=\"images/icon/viewmag.png\" width=\"20\"></a></td>
            </tr>";
  }

  print "</table>";

  // Historiques des ventes flash
  print "<br>
  <h3>Historiques précédentes Ventes Flash ou en cours</h3>

  <table class=\"vf_articles\">
  <tr>
      <td>Vente Flash</td>
      <td>Du</td>
      <td>Au</td>
      <td>Nb d'articles</td>
      <td>Nb de ventes</td>
      <td>CA HT</td>
      <td></td>";
  if ($admin_username=="truc")
  	print "<td></td>";
  print "</tr>";

  $reqVfH = $DB_site2->query("SELECT * FROM vente_flash_historique ORDER BY id DESC");
  while($vfh = $DB_site2->fetch_array($reqVfH)){
    $du_date = date("d/m/Y", $vfh['du']);
    $du_heure = date("H:i", $vfh['du']);
    $au_date = date("d/m/Y", $vfh['au']);
    $au_heure = date("H:i", $vfh['au']);
    
    // nombre d'articles de la vente flash
    $nbVfHA = $DB_site2->query_first("SELECT count(*) as nbArt FROM vente_flash_harticles WHERE vfhid=".$vfh['id']);
    
    // compte des ventes 
    $nbVentes=0;
    $caApproxtotal2=0;

    $ListeVfHA = $DB_site2->query("SELECT artcode, prixflash FROM vente_flash_harticles WHERE vfhid=".$vfh['id']);
    while($lgnArt = $DB_site2->fetch_array($ListeVfHA)){
          $nbVentes2=0;
          $requete = "SELECT sum(lf.qte) as nbVenteArt,prix FROM lignefacture lf, facture f  WHERE lf.factureid = f.facturid AND valide in (1,5,7,8) AND artcode='".$lgnArt['artcode']."'";
          $requete .=" AND f.heurefacture >= '".$vfh['du']."' AND f.heurefacture <= '".$vfh['au']."' AND provenance IN ('','mobile') ";
          $cptVenteVfHA = $DB_site2->query_first($requete);
          $nbVentes+=$cptVenteVfHA['nbVenteArt'];

          $nbVentes2+=$cptVenteVfHA['nbVenteArt'];
          $caApprox2=$lgnArt['prixflash']*$nbVentes2;
          $caApproxtotal2+=$caApprox2;
    }

    print "<tr>
            <td>".$vfh['nom']."</td>
            <td>".$du_date." ".$du_heure."</td>
            <td>".$au_date." ".$au_heure."</td>
            <td>".$nbVfHA['nbArt']."</td>
            <td align=\"center\">".$nbVentes."</td>
            <td>".number_format($caApproxtotal2, 0, ',', ' ')." €</td>
            <td><a href=\"vente_flash.php?action=voir&site=".$vfh['site']."&vfhid=".$vfh['id']."\"><img src=\"images/icon/viewmag.png\" width=\"20\"></a></td>
            <!--<td><a href=\"\"><img src=\"images/icon/xls.png\" width=\"20\"></a></td>-->";
    		if ($admin_username=="truc")
            	print "<td><a href=\"\"><img src=\"images/supprimer.gif\" onclick=\"deleteVfH(".$vfh['id'].",'".$vfh['nom']."')\" width=\"20\"></a></td>";
          print "</tr>";
  }

  print "</table>";
}

// Edition d'une vente flash 
if($_GET['action'] == "editer"){
  
    $vf = $DB_site2->query_first("SELECT * FROM vente_flash WHERE site_code='".$_GET['site_code']."' AND id='".$_GET['id']."'");
   
    if($vf == "") { print_r("oups !!".$_GET['id']); exit;}

    print "<h2>Site ".$vf['site_code']."</h2>";

    $du=date("d/m/Y H:i",$vf['du']);
    $au=date("d/m/Y H:i",$vf['au']);
    
    // affichage du status de la periode En cours, terminee...
    if( $now > $vf['du'] && $now < $vf['au']) {
        $statut ="En cours";
        $style ="<span style=\"color:green\">".$statut."</span>" ;
      }
      elseif($now > $vf['au']) {
        $statut = "Terminée";
        $style ="<span style=\"color:red\">".$statut."</span>";
      }
      else {
        $statut = "Prochainement";
        $style = "<span style=\"color:orange\">".$statut."</span>";
      }

    // Formulaire de saisie des dates
    if(!$filter_langue) {
        print "<P><form method=\"post\" >
          <input type=\"hidden\" name=\"action2\" value=\"dates\">
          <input type=\"hidden\" name=\"site_code\" value=\"".$vf['site_code']."\">
          <input type=\"hidden\" name=\"id\" value=\"".$vf['id']."\">
          Periode ".$style." : <input type=\"text\" name=\"du\" id=\"ventedu\" size=\"14\" value=\"$du\" readonly onclick=\"calendrier_show(this.id,this.value,true);\"> au <input type=\"text\" name=\"au\" id=\"venteau\" size=\"14\" value=\"$au\" readonly onclick=\"calendrier_show(this.id,this.value,true);\"> <input type=\"submit\" value=\"Valider\">
          <span style=\"float:right\"><a href=\"/admin/vente_flash.php\">Retour</a></span>
        </form>";
    }
    

    // Affichage des onglets
    print "<table class=\"onglets\">
      <tr>
        <td class=\"onglet_on\" id=\"ongletAA\" onclick=\"switchonglet('AA');\">Liste des articles <img src=\"images/apercu.png\" width=\"20\"></div>";

    foreach($sites[$site_code]['hosts'] as $langue){
      $on="";
      print "<td class=\"onglet$on\" id=\"onglet$langue\" onclick=\"switchonglet('$langue');\">Description $langue <img src=\"images/drapeaux/".$langue.".gif\" width=\"20\"></div>";
    }

    print "</tr>
    </table>";

    // Description vf en fonction des langues et gestion de l'affichage en fonction de l'onglet selectionné
    foreach($sites[$site_code]['hosts'] as $langue){
        $filemtime="";
        $readonly =! in_array($langue,$langue_access);
        $pathimg = "../configurations/".$host."/images/templates/base/".$langue."/";
        if(file_exists($pathimg."vente_flash/vente_flash.jpg")) $filemtimeOn = "?".filemtime($pathimg."vente_flash/vente_flash.jpg");
        if(file_exists($pathimg."vente_flash_off/vente_flash.jpg")) $filemtimeOff = "?".filemtime($pathimg."vente_flash_off/vente_flash.jpg");
        print "<div id=\"vf$langue\" class=\"divlangue\">
          Texte $langue Vente Flash active : ".($readonly?"":"<a href=\"vente_flash.php?action=edit0&site_code=".$vf['site_code']."&id=".$vf['id']."&l=$langue\">Modifier</a>")."
          <div style=\"border:solid thin black;color:black;\">".$vf['text0'.$langue]."</div>
          <br>
           Image $langue Vente Flash active : ".($readonly?"":"<a href=\"vente_flash.php?action=edit1&site_code=".$vf['site_code']."&id=".$vf['id']."&l=$langue\">Modifier</a>")."
          <div><img src=\"".$pathimg."vente_flash/vente_flash.jpg".$filemtimeOn."\" ></div>
          <br>
          Image $langue Vente Flash Inactive : ".($readonly?"":"<a href=\"vente_flash.php?action=edit2&site_code=".$vf['site_code']."&id=".$vf['id']."&l=$langue\">Modifier</a>")."
          <div><img src=\"".$pathimg."vente_flash_off/vente_flash.jpg".$filemtimeOff."\" ></div>
          <br>
          <form method=\"post\" style=\"display:block;border:solid thin black;color:black;\">
            <input type=\"hidden\" name=\"meta_langue\" value=\"$langue\">
            <input type=\"hidden\" name=\"vfid\" value=\"".$vf['id']."\">
            <br>Méta titre $langue : ".($readonly?$vf['ref_title'.$langue]:"<input type=\"text\" name=\"title\" value=\"".$vf['ref_title'.$langue]."\" size=\"60\">")."
            <br>Méta description $langue : ".($readonly?$vf['ref_description'.$langue]:"<textarea name=\"description\" cols=\"60\">".$vf['ref_description'.$langue]."</textarea>")."
            <br>".($readonly?"":"<input type=\"submit\" value=\"Mettre à jour\">")."
          </form>
        </div>";
    }

    // entete du tableau articles vf et gestion de son affichage
    print "<div id=\"vfAA\" class=\"divlangue\" style=\"display:block\">
    <p><b>Liste des articles :</b></p>
	<form action=\"vente_flash.php\" method=\"post\">
	  <input type=\"hidden\" name=\"action2\" value=\"modif_prix\">
	  <input type=\"hidden\" name=\"id\" value=\"1\">
    <table class=\"vf_articles\">
      <tr>
        <td>Code article</td>
        <td>Code fabricant</td>
		<td>PPI</td>
        <td>Prix actuel</td>
        <td>Prix v.flash</td>
        <td>Remise %/PPI</td>
        <td>Marge HT</td>
        <td>Stock</td>
		<td>Nb ventes</td>
		<td>CA HT estimé</td>
        <td>Supprimer</td>
      </tr>";

    $requete=$DB_site2->query("SELECT * FROM vente_flash_article WHERE vfid=".$vf['id']." ORDER BY artcode");
    
    // liste des articles
    while($vfart=$DB_site2->fetch_array($requete)){
          $article=$DB_site2->query_first("SELECT artcode, a_s.prix, libelleFR, artcode_eurocave FROM article INNER JOIN article_site a_s USING(artid) WHERE artcode='$vfart[artcode]' AND site_code='".$vf['site_code']."'");
		  
		  
		  // compte des ventes 
		  $nbVentes=0;
		  $requeteCpt ="SELECT sum(lf.qte) as nbVenteArt FROM lignefacture lf, facture f  WHERE lf.factureid = f.facturid AND valide in (1,5,7,8) AND artcode='".$article['artcode']."'";
		  $requeteCpt .=" AND f.heurefacture >= '".$vf['du']."' AND f.heurefacture <= '".$vf['au']."' AND provenance IN ('','mobile') ";
		  $cptVenteVfHA = $DB_site2->query_first($requeteCpt);
		  $nbVentes+=$cptVenteVfHA['nbVenteArt'];
		  $nbVentestotal+=$nbVentes;
		  $caApprox=$vfart[prix]*$nbVentes;
		  $caApproxtotal+=$caApprox;
		  
		  
			// debut calcul de marge de $article[artcode]
			$sql="SELECT artid, active, paht, artcode, artcode_eurocave, prix, prixtrspt, codebarre, prixconseilleht FROM article WHERE artcode='".$article[artcode]."' ";
			$res=$DB_site2->query_first($sql);
			$remise = round(100 - ($vfart[prix] * 100 / ($res[prixconseilleht]*$tauxtva)),2);
			
			$article_fournisseur=$DB_site2->query_first("SELECT ecopartht, trspt, prix FROM article_fournisseur WHERE artid=".$res[artid]." AND principal LIKE '%base%' AND devise='EUR' ");
			if($article_fournisseur[ecopartht]==""){
				$article_fournisseur[ecopartht]=0;
			}
			if($article_fournisseur[trspt]==""){
				$article_fournisseur[trspt]=0;
			}
			$sql="SELECT prix, paht FROM temp_price WHERE artid=".$res[artid]." AND du <= '".$vfh['du']."' AND au >= '".$vfh['au']."' LIMIT 1";
			$temp_price=$DB_site2->query_first($sql);
			if($temp_price[prix]!=""){
				$article_paht=$temp_price[paht];
			} else {
				if($article_fournisseur[prix] != ''){
					$article_paht = $article_fournisseur[prix];
				}else{
					$article_paht=$res[paht];
				}
			}
			$lesGaranties=DAOgarantie::loadMaxGarantieArtByPays($DB_site2,'base','FR','57',$res[artid],'',$vfart[prix]);
			$i=0;
			$garantie=0;
			if(!empty($lesGaranties)){
				foreach ($lesGaranties as $laGarantie) {
					$i++;
					if ($i==1)
						$garantie=round($laGarantie->paht);
					if ($laGarantie->cumul==1)
						$garantie+=round($laGarantie->paht);
				}
			}
			$frais_port=new frais_port($DB_site2,array($res[artid]=>1),57,$vfart[prix],$article_paht,'base');

			$article_paht_total=$article_paht+$res[prixtrspt]+$article_fournisseur[ecopartht]+$article_fournisseur[trspt]+$garantie+$frais_port->paht-$frais_port->prixht;
			$marge_ht=round(($vfart[prix]/$tauxtva)-$article_paht_total,2);
			$marge_pct=round($marge_ht*100/($vfart[prix]/$tauxtva),2);
			// fin calcul de marge
		  
		  
		  
          print "<tr>
		            <td>".$article[artcode]."</td>
		            <td>".$article[artcode_eurocave]."</td>
					<td>".round($res[prixconseilleht]*$tauxtva,2)."€</td>
		            <td>".$article[prix]."€</td>
					<td nowrap><input name=\"prixvf_".$article[artcode]."\" align=\"right\" type=\"number\" step=\"0.01\" min=\"0\" value=\"".number_format($vfart[prix], 2, '.', '')."\">€</td>
	      			<td align=\"center\">".number_format($remise, 2, '.', '')."%</td>
	      			<td align=\"center\">".number_format($marge_ht, 2, ',', ' ')."€<br>".$marge_pct."%</td>
	      			<td align=\"center\">".$vfart[stock]."</td>
					<td align=\"center\">".$nbVentes."</td>
					<td align=\"center\">".$caApprox."</td>
			      	<td><a href=\"vente_flash.php?action=suppr&site_code=".$site_code."&action2=del&del=".$vfart[id]."&id=".$_GET['id']."\"><img src=\"images/supprimer.gif\" title=\"Supprimer\"></a></td>
            	</tr>";
      }

    if($DB_site2->num_rows($requete)==0) 
        print "<tr><td colspan=\"10\" align=\"center\">Aucun article</td></tr>";

    print "<tr>
			<td colspan=\"4\" align=\"center\">&nbsp;</td>
			<td align=\"center\"><input type=\"submit\" value=\" /!\ mise à jour /!\ \"></td>
			<td colspan=\"4\" align=\"center\"></td>
			<td align=\"right\" nowrap>".number_format($caApproxtotal, 0, ',', ' ')." &euro;</td>
			<td>&nbsp;</td>
		  </tr>
		</table></form>";
    
    // Affichage et gestion des boutons de Ajouter, Cloture et Effacer
    Print "<br>
          <div class=\"vf_articles2\">";
    if ($statut!="En cours")      
      Print "<input type=\"button\" value=\"Effacer les articles\" onclick=\"cleanArt(".$_GET['id'].");\" style=\"float:right\">";
      
    Print "<input type=\"button\" value=\"Sauvegarder la VF\" onclick=\"closeit(".$_GET['id'].");\" style=\"float:right\">";
    
    Print "<br><br><br>Ajouter un article : <input type=\"text\" id=\"src\"  placeholder=\"Code MEC / Code Fabricant\" size=\"30px\">&nbsp;<input type=\"button\" value=\"Rechercher\" onclick=\"srcprod();\">";
    Print "</div>";
    
    // Affichage du produit
    // Modif TSI: Ajout d'un bouton retour a la liste (qui n'est plus effacée par le JS... voir modif ci-dessus
    print "<div id=\"resultsvf\"></div>
            <div id=\"addarticle\" style=\"display:none; margin-top:10px;\">
            <form method=\"post\" style=\"float:left; text-aligne:center;\">
                <input type=\"hidden\" name=\"action2\" value=\"addarticle\">
                <input type=\"hidden\" name=\"vfid\" value=\"".$vf['id']."\">
                <input type=\"hidden\" id=\"arttva\">
                <input type=\"hidden\" id=\"portoffert\">
                <input type=\"hidden\" id=\"port_origin\">
				<input type=\"hidden\" id=\"marge_calc\" name=\"marge_calc\" value=\"\">
				<input type=\"hidden\" id=\"remise_calc\" name=\"remise_calc\" value=\"\">
    			<input type=\"hidden\" id=\"stock\" name=\"stock\" value=\"\">
				<input type=\"hidden\" id=\"prixnorm\" name=\"prixnorm\">
                <input type=\"text\" class=\"inputvf\" name=\"artcode\" readonly>
                <br /><label for=\"prixnormal\">Prix original :</label> <span id=\"prixnormal\">".$devises->symbole($sites[$site_code]['devise'])." TTC </span> 
                <br /><label for=\"prixflash\">Prix vente flash :</label><input type=\"text\" id=\"prixflash\" name=\"prixflash\" size=\"4\" onkeyup=\"calcmarge();\"> ".$devises->symbole($sites[$site_code]['devise'])." TTC
                <p><input type=\"submit\" value=\"Ajouter\"> <input type=\"button\" value=\"Retour\" onclick=\"backtolist();\"></p>
            </form>";

    // Calcul des marges sur le produit
    print "<table class=\"marge_ca\" cellspacing=\"0\">
              <tr><td style=\"font-weight:bold;\">Marge en EUR</td><td>Article</td><td>Eco part</td><td>Emball/trspt</td><td>Garantie</td><td>Transport</td><td>Total</td><td>Marge</td></tr>
			
              <tr><td>Achat HT</td><td id=\"maarticle\"></td><td id=\"maeco\"></td><td id=\"maembal\"></td><td id=\"magar\"></td><td id=\"matrspt\"></td><td id=\"matotal\"></tr>
			
			  <tr><td>Vente HT normal</td><td id=\"mvarticle\"></td><td id=\"mveco\"></td><td></td><td id=\"mvgar\"></td><td id=\"mvtrspt\"></td><td id=\"mvtotal\"><td id=\"margeht\" style=\"font-weight:bold;\"></td></tr>
			
              <tr style=\"background:#FFAAAA\"><td>Vente HT flash</td><td id=\"mvfarticle\"></td><td id=\"mvfeco\"></td><td></td><td id=\"\"></td><td id=\"mvftrspt\"></td><td id=\"mvftotal\"><td id=\"margevfht\" style=\"font-weight:bold;\"></td></tr>
            </table>
            <div class=\"clear\"></div>
            </div>
          </div>";

      print "</div>";
         
  }
  
// Visualisation d'une vente flash de l'historique 
if($_GET['action'] == "voir")
  {
      // gestion de l'affichage d'un historique
      // cas action == voir
     
      print "<span style=\"float:right\"><a href=\"/admin/vente_flash.php\">Retour</a></span>";
      
      if (isset($_GET['vfhid']))
      {
          //print "vfhid :".$_GET['vfhid'];
          
          // Detail Vente flash historique affichée
          print "<h3>Detail ventes flash historique</h3>";
         
          $requete=$DB_site2->query("SELECT * FROM vente_flash_historique WHERE id=".$_GET['vfhid']);
          $vfh=$DB_site2->fetch_array($requete);
         
          print "<table class=\"recap_vf\">
                  <tr>
                    <td with=\"10%\">Nom</td>
                    <td with=\"25%\">Du</td>
                    <td with=\"25%\">Au</td>";
          print "</tr>";
          
          $du=date("d/m/Y H:i",$vfh['du']);
          $au=date("d/m/Y H:i",$vfh['au']);
          
          print "<tr>
                    <td>".$vfh[nom]."</td>
                    <td>".$du."</td>
                    <td>".$au."</td>";
          print "</tr>";
          
          print "</table><br><br>";
          // entete du tableau detail articles d'une vf historique et gestion de son affichage
          
          //print "<div id=\"vfAA\" class=\"divlangue\" style=\"display:block\">";
          print "<p><h3>Liste des articles :</h3></p>
          <table class=\"vf_articles\">
            <tr>
              <td>Code article</td>
              <td>Code fabricant</td>
              <td>Prix original</td>
              <td>Prix vente flash</td>
			  <td>Remise %</td>
			  <td>Marge HT estimé</td>
              <td>Nb vente</td>
			  <td>CA HT approx</td>
            </tr>";
         
          // liste des articles   id 	vfhid 	artcode 	artcode_eurocave 	prix 	prixflash
          $arraydata =$vfh[nom].";".$du.";".$au."\n\n";
          $arraydata .="code article;code fabricant;prix original;prix flash;nb vente\n";
          $nbVentestotal=0;
		  $caApproxtotal=0;
          $requete=$DB_site2->query("SELECT vfh.* FROM vente_flash_harticles vfh WHERE vfh.vfhid=".$vfh[id]." ORDER BY vfh.artcode"); 
          while($article=$DB_site2->fetch_array($requete)){
              // compte des ventes 
              $nbVentes=0;
              $requeteCpt ="SELECT sum(lf.qte) as nbVenteArt FROM lignefacture lf, facture f  WHERE lf.factureid = f.facturid AND valide in (1,5,7,8) AND artcode='".$article['artcode']."'";
              $requeteCpt .=" AND f.heurefacture >= '".$vfh['du']."' AND f.heurefacture <= '".$vfh['au']."' AND provenance IN ('','mobile') ";
              $cptVenteVfHA = $DB_site2->query_first($requeteCpt);
              $nbVentes+=$cptVenteVfHA['nbVenteArt'];
			  $nbVentestotal+=$nbVentes;
			  $caApprox=$article[prixflash]*$nbVentes;
              $caApproxtotal+=$caApprox;
			  $remise = round(100 - ($article[prixflash] * 100 / $article[prix]),2);
			  
				// debut calcul de marge de $article[artcode]
				$sql="SELECT artid, active, paht, artcode, artcode_eurocave, prix, prixtrspt, codebarre, prixconseilleht FROM article WHERE artcode='".$article[artcode]."' ";
				$res=$DB_site2->query_first($sql);
				$article_fournisseur=$DB_site2->query_first("SELECT ecopartht, trspt, prix FROM article_fournisseur WHERE artid=".$res[artid]." AND principal LIKE '%base%' AND devise='EUR' ");
				if($article_fournisseur[ecopartht]==""){
					$article_fournisseur[ecopartht]=0;
				}
				if($article_fournisseur[trspt]==""){
					$article_fournisseur[trspt]=0;
				}
				$sql="SELECT prix, paht FROM temp_price WHERE artid=".$res[artid]." AND du <= '".$vfh['du']."' AND au >= '".$vfh['au']."' LIMIT 1";
				$temp_price=$DB_site2->query_first($sql);
				if($temp_price[prix]!=""){
					$article_paht=$temp_price[paht];
				} else {
					if($article_fournisseur[prix] != ''){
						$article_paht = $article_fournisseur[prix];
					}else{
						$article_paht=$res[paht];
					}
				}
				$lesGaranties=DAOgarantie::loadMaxGarantieArtByPays($DB_site2,'base','FR','57',$res[artid],'',$article[prixflash]);
				$i=0;
				$garantie=0;
				if(!empty($lesGaranties)){
					foreach ($lesGaranties as $laGarantie) {
						$i++;
						if ($i==1)
							$garantie=round($laGarantie->paht);
						if ($laGarantie->cumul==1)
							$garantie+=round($laGarantie->paht);
					}
				}
				$frais_port=new frais_port($DB_site2,array($res[artid]=>1),57,$article[prixflash],$article_paht,'base');

				$article_paht_total=$article_paht+$res[prixtrspt]+$article_fournisseur[ecopartht]+$article_fournisseur[trspt]+$garantie+$frais_port->paht-$frais_port->prixht;
				$marge_ht=round(($article[prixflash]/$tauxtva)-$article_paht_total,2);
				$marge_pct=round($marge_ht*100/($article[prixflash]/$tauxtva),2);
				// fin calcul de marge
			  
              // affichage ligne du produit      
              print "<tr>
                        <td>".$article[artcode]."</td>
                        <td>".$article[artcode_eurocave]."</td>
                        <td>".number_format($article[prix], 2, ',', ' ')."€</td>
                        <td>".number_format($article[prixflash], 2, '.', '')."€</td>
						<td align=\"center\" nowrap>".$remise."%</td>
						<td align=\"center\" nowrap>".number_format($marge_ht, 2, ',', ' ')."€ | ".$marge_pct."%</td>
                        <td align=\"center\">".$nbVentes."</td>
						<td align=\"right\">".number_format($caApprox, 0, ',', ' ')." &euro;</td>
                    </tr>";
              
              // stockage des donnees du csv      
              $arraydata .= $article[artcode].";".$article[artcode_eurocave].";"
                            .$article[prix].$devises->symbole($sites[$site_code]['devise']).";".$article[prixflash].$devises->symbole($sites[$site_code]['devise']).";"
                            .$nbVentes."\n";
         }
         echo "<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td align=\"right\">Total :</td>
				<td align=\"center\">".$nbVentestotal."</td>
				<td align=\"right\" nowrap>".number_format($caApproxtotal, 0, ',', ' ')." &euro;</td>
				</tr>";
         print "</table><br><br><b>En cas de mise à jour de prix en cours de VF, les calculs de marge et de CA sont faussés.</b><br><br>";
         
         // appel de la fonction javascript export en csv
         // echo json_encode($day) permet de transformer la varible php en variable js
         $filename = $vfh[nom].".csv";
         print "<a href='#' onclick='downloadCSV(";
         print json_encode($filename).",";
         print json_encode($arraydata);
         print ");'>Download CSV</a>";
		 echo "<br>a noter : le CA approximatif ne comporte pas les frais de port.<br>
				";
          
      }
      
      
  }else{
      // Gestion de la modification des textes/images d'une vf
      //print_r("id : ".$_GET['id']);
      
      $l = $_GET['l'];
      switch($_GET['action']){
          case "edit0":
            $textid=substr($_GET['action'],4,1);
            print "<img src=\"images/drapeaux/".$_GET['l'].".gif\" width=\"20\">";
            print "<form method=\"post\">
            <input type=\"hidden\" name=\"action2\" value=\"".$_GET['action']."\">
            <input type=\"hidden\" name=\"l\" value=\"".$_GET['l']."\">
            <input type=\"hidden\" name=\"site_code\" value=\"".$_GET['site_code']."\">
            <input type=\"hidden\" name=\"id\" value=\"".$_GET['id']."\">";
            $vf=$DB_site2->query_first("SELECT * FROM vente_flash WHERE site_code='".$_GET['site_code']."' AND id='".$_GET['id']."'");
    
            $h1=$vfname=$desc="";
            $doc=new DOMDocument();
            $doc->loadHTML('<?xml encoding="utf-8" ?>'.$vf['text0'.$_GET['l']]);
            $elem=$doc->getElementsByTagName("h1");
            if($elem->length>0) $h1=$elem->item(0)->firstChild->nodeValue;
            $elem=$doc->getElementsByTagName("p");
            foreach($elem as $p){
                if(preg_match("/^titre/",$p->getAttribute("class"))) $vfname=$p->firstChild->nodeValue;
                if($p->getAttribute("class")=="soustitre") $desc=$p->ownerDocument->saveHTML($p);
            }
            print "<p>Titre : <input type=\"text\" name=\"h1\" value=\"".$h1."\"></p>";
            print "<p>Nom vente flash : <input type=\"text\" name=\"vfname\" value=\"".$vfname."\"></p>";
            print "<p>Texte : <textarea name=\"desc\" id=\"desc\">".$desc."</textarea></p>";
            
            print "<p><input type=\"submit\" value=\"Valider\"> <input type=\"button\" onclick=\"document.location='vente_flash.php?action=editer&id=".$_GET['id']."&site_code=".$_GET['site_code']."';\" value=\"Annuler\"></p>
            </form>";
            print "<script type=\"text/javascript\">CKEDITOR.config.enterMode=CKEDITOR.ENTER_BR;CKEDITOR.replace('desc');</script>";
          break;

        case "edit1":
        case "edit2":
          $filemtime = "";
          $dossier = "vente_flash";
          if($_GET['action'] == "edit2") $dossier = "vente_flash_off";
          $pathimg = "../configurations/".$host."/images/templates/base/".$_GET['l']."/".$dossier."/vente_flash.jpg";
          print "<p style=\"color:red;font-size:25px;\">ATTENTION, il faut que la PHOTO soit au format 1028x600</p><br><br>";
          print "<img src=\"images/drapeaux/".$_GET['l'].".gif\" width=\"20\"> ".$actions[$_GET['action']];
          if(file_exists($pathimg)) $filemtime = "?".filemtime($pathimg);
          print "<div><img src=\"".$pathimg.$filemtime."\"></div>";
          print "<form method=\"post\" enctype=\"multipart/form-data\">
            <input type=\"hidden\" name=\"action2\" value=\"".$_GET['action']."\">
            <input type=\"hidden\" name=\"l\" value=\"".$_GET['l']."\">
            <input type=\"hidden\" name=\"site_code\" value=\"".$_GET['site_code']."\">
            <input type=\"hidden\" name=\"id\" value=\"".$_GET['id']."\">
            <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"10485760\" />";
          $vf=$DB_site2->query_first("SELECT * FROM vente_flash WHERE site_code='".$_GET['site_code']."' AND id='".$_GET['id']."'");
          print "<input type=\"file\" name=\"upload_image\" /><br><br>";
          print "<p><input type=\"submit\" value=\"Valider\"> <input type=\"button\" onclick=\"document.location='vente_flash.php?action=editer&id=".$_GET['id']."&site_code=".$_GET['site_code']."';\" value=\"Annuler\"></p>";
          print "</form>";
          break;
      }
  }

include "./footer.php";


function sanitize($string, $force_lowercase = true, $anal = false) {
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
                   "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                   "â¤?", "â¤?", ",", "<", ">", "/", "?");
    $clean = trim(str_replace($strip, "", strip_tags($string)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
    return ($force_lowercase) ?
        (function_exists('mb_strtolower')) ?
            mb_strtolower($clean, 'UTF-8') :
            strtolower($clean) :
        $clean;
}

function resizeImage($srcFile, $dstWidth = 120, $dstHeight = 90,$dstFile = null, $jpegQuality = 100){
    global $msg2;
    if (!file_exists($srcFile)) {
        $msg2="$srcFile doesn't exist! ";
        return false;        
    }
    if (!$dstFile) {
        $dstFile = 'tn_' . basename($srcFile);
    }
    if (!is_writable($dstDir = dirname($dstFile))) {
        $msg2="$dstDir is not a writeable directory! ";
        return false;
    }

    list($srcWidth, $srcHeight, $type, ) = getimagesize($srcFile);

    switch ($type) {
    case 1 :
        $srcHandle = imagecreatefromgif($srcFile);
        break;
    case 2 :
        $srcHandle = imagecreatefromjpeg($srcFile);
        break;
    case 3 :
        $srcHandle = imagecreatefrompng($srcFile);
        break;
    default :
        $msg2='File Type Not Supported! ';
        return false;
    }
    if (!$srcHandle) {
        $msg2='Could not execute imagecreatefrom() function! ';
        return false;
    }

    // Source is wider than the Destination
    $width = $dstWidth;
    $xOffset = 0;

    $height = round(($width / $srcWidth) * $srcHeight);
    $yOffset = 0;
    /*if($height > $dstHeight){ pour redimensionner les photos en portrait
      $ratio = round($height/$dstHeight,2);
      $height = round($height/$ratio);
      $width = round($width/$ratio);
    }*/

    $dstHandle = imagecreatetruecolor($width, $height);

    // bool imagecopyresampled ( resource dst_image, resource src_image, int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h )
    if (!imagecopyresampled($dstHandle, $srcHandle, $xOffset, $yOffset, 0, 0, $width, $height, $srcWidth, $srcHeight)) {
        $msg2='Could not execute imagecopyresampled() function! ';
        return false;
    }
    imagedestroy($srcHandle);

    switch ($type) {
    case 1 :
        imagegif($dstHandle, $dstFile);
        break;
    case 2 :
        imagejpeg($dstHandle, $dstFile, $jpegQuality);
        break;
    case 3 :
        imagepng($dstHandle, $dstFile);
        break;
    default :
        $msg2='File Type Not Supported! ';
        return false;
    }

    imagedestroy($dstHandle);

    return true;
}

?>
