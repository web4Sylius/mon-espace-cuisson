<?php

ini_set("display_errors","on");

require "admin_global.php";

include("fonctions.php");

setcookie("hostname",$host,time()+3600,"/");
include_once("marketplace/article.php");
include_once("articles/onglets.php");
include_once("articles/fonctions.php");
include_once("articles/javascript.php");

include_once("../class/ral.class.php");
include "../class/garantie.class.php";
include "../class/categorie_mcav.class.php";
include "../class/DAOgarantie.class.php";
include "../class/DAOarticle.class.php";

include_once("articles/fonctions.php");//include $rootpath."configurations/".$host."/class/cloudflare.class.php";

$notranslate=false;

include "./header.php";
include "./menu.php";

// <script language=\"javascript\" src=\"../javascripts/prototype.js\"></script>

echo "<LINK href=\"/admin/cal.css\" type=\"text/css\" rel=\"stylesheet\">
<script language=\"javascript\" src=\"calendrier.js\"></script>
<script language=\"javascript\" src=\"../javascripts/prototype.js\"></script>
<script language=\"javascript\" src=\"filebox.js\"></script>
<LINK href=\"filebox.css\" type=\"text/css\" rel=\"stylesheet\">";
echo "<script language=\"javascript\"> 
<!-- 
	function resize()
		{
		window.open ('petit_marque.php', 'Marque', 'Width=400, Height=175, toolbar=no, directories=no, location=no, menubar=no, resizable=no, scrollbars=no, status=no') ;
		} 
	function resize2(caract)
		{
		window.open ('petit_caract.php?caractid='+caract+'', 'Caractéristique', 'Width=470, Height=250, toolbar=no, directories=no, location=no, menubar=no, resizable=no, scrollbars=no, status=no') ;
		} 
	function resize3(art)
		{
		window.open ('petit_champ.php?artid='+art+'', 'Champ', 'Width=450, Height=300, toolbar=no, directories=no, location=no, menubar=no, resizable=no, scrollbars=no, status=no') ;
		} 
	function suppr(art1,art2)
		{
		window.open ('supprimage.php?art='+art1+'&sup='+art2+'', 'Marque', 'Width=400, Height=175, toolbar=no, directories=no, location=yes, menubar=no, resizable=no, scrollbars=no, status=no') ;
		} 
	function passerbase(art1,art2)
		{
		window.open ('passerbase.php?art='+art1+'&sup='+art2+'', 'PasserBase', 'Width=1300, Height=500, toolbar=no, directories=no, location=yes, menubar=no, resizable=no, scrollbars=no, status=no') ;
		} 
	function changecat(action_envoyee,categ)
		{
		document.formu.action2.value=action_envoyee;
		document.formu.old.value=action_envoyee;
		document.formu.moncatid.value=categ;
		document.formu.submit() ;
		}
	function Deplacer(l1,l2) 
		{
		var selectedList = new Array();
		for (var i=0; i < l1.options.length; i++) 
			{ 
			if (l1.options[i].selected) 
				{ 
				//selectedList.push(l1.options[i].selectedindex); 
				o=new Option(l1.options[i].text,l1.options[l1.options.selectedIndex].text);
				l2.options[l2.options.length]=o;
				} 
			}
		effacer(l1);	
		}
	function effacer(l1)
		{
		var compteselect=0;
		for (yo = 0 ; yo < l1.length ; yo++)
			{
			if (l1.options[yo].selected == true)
				{
				compteselect++;
				}
			if (compteselect > 0)
				{
				for (yo = 0 ; yo < l1.length ; yo++)
					{
					if (l1.options[yo].selected == true)
						{
						l1.options[yo] = null;
						}
					}
				effacer(l1);
				}
			}
		}
	function supprimer_piece(piece)
		{
		window.open ('suppr_piece.php?pieceid='+piece+'', 'Pièce', 'Width=400, Height=175, toolbar=no, directories=no, location=yes, menubar=no, resizable=no, scrollbars=no, status=no') ;
		}
	function pascommandable()
		{
		
		if (document.getElementsByName('prix_aff')[0].checked == false)
			{
			document.getElementById('prix').style.display='none';
			}
		}
	function commandable()
		{
		
		document.getElementById('prix').style.display='block';
		}
	function affichepasprix()
		{
		document.formu.prix.value='0';
		document.getElementById('prix').style.display='none';
		}
	function afficheprix(prix)
		{
		document.formu.prix.value=prix;
		document.getElementById('prix').style.display='block';
		}
	function supprimg_caractval(artid,caractvalid)
		{
		window.open ('supprimg_caractval.php?artid='+artid+'&caractvalid='+caractvalid+'', 'Caractéristique', 'Width=400, Height=175, toolbar=no, directories=no, location=yes, menubar=no, resizable=no, scrollbars=no, status=no') ;
		}		
	function reappro(commandable){
		switch(commandable){
		    case '0':
		    case '1':
		    document.getElementById('divappro').style.display='none';
		    break;
		    case '2':
		    document.getElementById('divappro').style.display='block';
		    break;
		}
	}
	function nbonly(event,decimal,input) {
	  // Compatibilité IE / Firefox
	  if(!event && window.event) {
	    event=window.event;
	  }

	  if(decimal==undefined) decimal=false;

	  // IE
	  if(event.keyCode>0 && (event.keyCode < 48 || event.keyCode > 57) && event.keyCode!=8 && event.keyCode!=9) {
	      if(!decimal || event.keyCode!=46 || input.value.indexOf('.')>0) {
		  event.returnValue = false;
		  event.cancelBubble = true;
	      }
	  }

	  // DOM
	  if(event.which!=0 && (event.which < 48 || event.which > 57) && event.which!=8) {
	    if(!decimal || event.which!=46 || input.value.indexOf('.')>0){
		  event.preventDefault();
		  event.stopPropagation();
	    }
	  }
	}
	
//-->
</script>";

print <<<JS
<script language="JavaScript">


function setmodif(){
    document.getElementsByName('modif')[0].value='1';
}

function calcconso(value){
    value=parseFloat(value.replace(',','.'));
    if(isNaN(value)) value=0;
    document.getElementById('conso_kw').innerHTML=Math.round(value/36.5)/10;
    document.getElementById('conso_euro').innerHTML=Math.round(value*$prixkw);
}
function calcconsoold(value){
    value=parseFloat(value.replace(',','.'));
    if(isNaN(value)) value=0;
    document.getElementById('conso_kw_old').innerHTML=Math.round(value/36.5)/10;
    document.getElementById('conso_euro_old').innerHTML=Math.round(value*$prixkw);
}

function nolf(event){
    if(event.keyCode==13){
	event.preventDefault();
	event.stopPropagation();
    } 
}

function srcsimilaire(txt){
    if(txt.length<=3) return;
    document.getElementById('results').innerHTML='';
    ajaxrequest('marketplace/src.php?sim='+txt,srcsimilaire_cb);
}


function srcsimilaire_cb(httprequest){
    var xml=httprequest.responseXML.documentElement;
    var html='';
    var vistxt=new Array();
    vistxt[0]='<span style="color:red;">Invisible</span>';
    vistxt[1]='Visible';
    vistxt[2]='Supprimé';

    for(i=0;i<xml.childNodes.length;i++){
	var node=xml.childNodes[i];
	if(node.nodeName=='group'){
	    var content='';
	    var prods=node.getElementsByTagName('prod');
	    for(i2=0;i2<prods.length;i2++) content+='<br><img src="/configurations/$host/images/produits/br/'+prods[i2].getAttribute('artid')+'.jpg" height="50" align="absmiddle"> '+prods[i2].getAttribute('id')+' ('+prods[i2].getAttribute('ref');
	    html+='<p><a href="javascript:void(0)" onclick="document.getElementsByName(\'gartid\')[0].value='+prods[0].getAttribute('artid')+';clique(\'similaire\',\'similaire\');"><img src="images/plus.gif" align="absmiddle"> Ajouter au groupe</a>'+content+'</p><hr>';
	}
	if(node.nodeName=='prod'){
	     var visible=node.getAttribute('visible');
	     var artid=node.getAttribute('artid');
	     if(artid!=parseInt('$artid')) html+='<div><img src="/configurations/$host/images/produits/br/'+artid+'.jpg" height="70" align="left"> <a href="javascript:document.getElementsByName(\'gartid\')[0].value='+artid+';clique(\'similaire\',\'similaire\');"><img src="images/plus.gif" align="absmiddle"> Nouveau groupe avec '+node.getAttribute('id')+' ('+node.getAttribute('ref')+')</a><br>'+vistxt[visible]+'</div><br clear="all"><hr>';
	}
    }

    if(html=='') html='Aucun résultat';
    document.getElementById('results').innerHTML=html;
}

function showcaract(caractid){
    if(document.getElementById('caract'+caractid).style.display=='none'){
      document.getElementById('caract'+caractid).style.display='block';
      document.getElementById('icon'+caractid).src='images/moins.jpg';
    }else{
      document.getElementById('caract'+caractid).style.display='none';
      document.getElementById('icon'+caractid).src='images/plus.jpg';
    }
}

function srcartid(src){
      if(src.length<=3) return;
      var url="marketplace/src.php?txt="+src
      ajaxrequest(url,srcCallback);
      document.getElementById('results').innerHTML='<img src="/configurations/$host/images/templates/ajax_wait.gif">';
}

function srcCallback(httprequest){
      var xml=httprequest.responseXML.documentElement;
      var html='';
  
      var vistxt=new Array();
      vistxt[0]='<br><span style="color:red;">Invisible</span>';
      vistxt[1]='<br>Visible';
      if(xml.getElementsByTagName('prod')[0]){
	  html+='<table width="100%" style="border:solid thin #808080;"><tr>';
	  for(i=0;i<xml.getElementsByTagName('prod').length;i++){
		var description=xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
		var id=xml.getElementsByTagName('prod')[i].getAttribute('id');
		var artid=xml.getElementsByTagName('prod')[i].getAttribute('artid');
		var actif=xml.getElementsByTagName('prod')[i].getAttribute('active');
		var stock=xml.getElementsByTagName('prod')[i].getAttribute('stock');
		var marque=xml.getElementsByTagName('prod')[i].getAttribute('marque');
		var ref=xml.getElementsByTagName('prod')[i].getAttribute('ref');
		var lot=xml.getElementsByTagName('prod')[i].getAttribute('lot');
		var ventes=xml.getElementsByTagName('prod')[i].getAttribute('ventes');
		var ventes1an=xml.getElementsByTagName('prod')[i].getAttribute('ventes1an');
		var stock='Pas en stock';
		if(xml.getElementsByTagName('prod')[i].getAttribute('stock')=='1') stock='En stock : '+xml.getElementsByTagName('prod')[i].getAttribute('stockqte');
		if(actif<2) html+='<tr id="tr_'+artid+'"><td><img src="/configurations/$host/images/produits/br/'+artid+'.jpg" width="80"></td><td style="border-bottom:solid thin #cccccc;">'+marque+'<br>'+id+'<br>'+ref+'<br>'+vistxt[actif]+'</td><td style="border-bottom:solid thin #cccccc;"><b>'+description+'</b><br>Ventes : '+ventes+'<br>Ventes 1 an : '+ventes1an+'<br>'+stock+'</td><td style="border-bottom:solid thin #cccccc;"><a href="javascript:selectprod('+artid+')">Sélectionner</a></td></tr>';
	  }
	  html+='</table>';
      }else
	  html='Pas de résultat';

      document.getElementById('results').innerHTML=html;
}


function selectprod(artid){
    document.getElementsByName('remp_artid')[0].value=artid;
    clique('visible','visible');
}

function rmremp(id){
    document.getElementsByName('rempos')[0].value=id;
    document.getElementsByName('remp_artid')[0].value=0;
    clique('visible','visible');
}

function selectremp(id){
      for(i=1;i<=3;i++)
	  if(document.getElementById('remp'+i))
	      document.getElementById('remp'+i).style.borderColor='#888888';
      document.getElementById('remp'+id).style.borderColor='red';
      document.getElementsByName('rempos')[0].value=id;
      document.getElementById('modif').style.display='table-cell';
}


function srcredirect(src){
      if(src.length<=3) return;
      var url="marketplace/src.php?txt="+src
      ajaxrequest(url,srcredirectCallback);
      document.getElementById('results2').innerHTML='<img src="/configurations/$host/images/templates/ajax_wait.gif">';
}

function srcredirectCallback(httprequest){
      var xml=httprequest.responseXML.documentElement;
      var html='';
  
      var vistxt=new Array();
      vistxt[0]='<br><span style="color:red;">Invisible</span>';
      vistxt[1]='<br>Visible';
      if(xml.getElementsByTagName('prod')[0]){
	  html+='<table width="100%" style="border:solid thin #808080;"><tr>';
	  for(i=0;i<xml.getElementsByTagName('prod').length;i++){
		var description=xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
		var id=xml.getElementsByTagName('prod')[i].getAttribute('id');
		var artid=xml.getElementsByTagName('prod')[i].getAttribute('artid');
		var actif=xml.getElementsByTagName('prod')[i].getAttribute('active');
		var stock=xml.getElementsByTagName('prod')[i].getAttribute('stock');
		var marque=xml.getElementsByTagName('prod')[i].getAttribute('marque');
		var ref=xml.getElementsByTagName('prod')[i].getAttribute('ref');
		var lot=xml.getElementsByTagName('prod')[i].getAttribute('lot');
		var ventes=xml.getElementsByTagName('prod')[i].getAttribute('ventes');
		var ventes1an=xml.getElementsByTagName('prod')[i].getAttribute('ventes1an');
		var stock='Pas en stock';
		if(xml.getElementsByTagName('prod')[i].getAttribute('stock')=='1') stock='En stock : '+xml.getElementsByTagName('prod')[i].getAttribute('stockqte');
		if(actif<2) html+='<tr id="tr_'+artid+'"><td><img src="/configurations/$host/images/produits/br/'+artid+'.jpg" width="80"></td><td style="border-bottom:solid thin #cccccc;">'+marque+'<br>'+id+'<br>'+ref+'<br>'+vistxt[actif]+'</td><td style="border-bottom:solid thin #cccccc;"><b>'+description+'</b><br>Ventes : '+ventes+'<br>Ventes 1 an : '+ventes1an+'<br>'+stock+'</td><td style="border-bottom:solid thin #cccccc;"><a href="javascript:selectredirect('+artid+')">Sélectionner</a></td></tr>';
	  }
	  html+='</table>';
      }else
	  html='Pas de résultat';

      document.getElementById('results2').innerHTML=html;
}

function selectredirect(artid){
      document.getElementsByName('redirect')[0].value=artid;
      clique('visible','visible');
}

var fb=undefined;
function createfb(){
    fb=new filebox('fb');
    fb.dataurl='article_files.php?artid=$artid&action=files&parent=0';
}

function rechercher(){
	document.forms.rechform.submit();
}

 function tout_cocher() {
    	const inputs = rechform.querySelectorAll('input');
    	const nb_check = inputs.length;
    	const nb_check2 = rechform.querySelectorAll('input:checked').length;
    	if((nb_check - 2) ===  nb_check2) {
    		inputs.forEach((input) => {
        		input.checked = false;
    		});
    	}
    	else {
    		inputs.forEach((input) => {
        		input.checked = true;
    		});
    	}
    }

</script>
JS;

echo "<h1>Ajout / Modification de produit</h1>

<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td background=\"images2/cadre_r4_c2.gif\" width=\"3\" height=\"100%\"></td>
   <td align=\"center\" valign=\"middle\">" ;

echo "<form name=\"rechform\" method=\"post\" action=\"rechercher.php\">
      <table class=\"form_rech_article\">
		<tr>
		<td>Recherche : </td>
		<td></td>
		<td><img src=\"images/en_cours.png\" title=\"En cours de création sur site $site_code\" width=\"30\"></td>
		<td><img src=\"images/new.png\" title=\"Nouveau à contrôler\" width=\"30\"></td>
		<td><img src=\"images/visible.png\" title=\"Visible sur site $site_code\" width=\"30\"></td>
		<td><img src=\"images/invisible.png\" title=\"Invisible sur site $site_code\" width=\"30\"></td>
		<td><img src=\"images/supprime.png\" title=\"Supprimé sur site $site_code\" width=\"30\"></td>
		<td><img src=\"images/archive_temp.png\" title=\"Archivé temporairement sur site $site_code\" width=\"30\"></td>
		<td><img src=\"images/archive.png\" title=\"Archivé définitivement sur site $site_code\" width=\"30\"></td>
		</tr>
		<tr>
		<td><input class=\"bouton1\" type=\"text\" name=\"recherche\" size=\"10\"></td>
		<td><input type=\"checkbox\" onclick=\"tout_cocher()\"></td>
		<td><input type=\"checkbox\" name=\"active[]\" value=\"6\"></td>
		<td><input type=\"checkbox\" name=\"active[]\" value=\"4\"></td>
		<td><input type=\"checkbox\" name=\"active[]\" value=\"1\" checked></td>
		<td><input type=\"checkbox\" name=\"active[]\" value=\"0\" checked></td>
		<td><input type=\"checkbox\" name=\"active[]\" value=\"2\"></td>
		<td><input type=\"checkbox\" name=\"active[]\" value=\"5\"></td>
		<td><input type=\"checkbox\" name=\"active[]\" value=\"3\"></td>
		</tr>
		<tr>
		<td><img src=\"./images/rechercher.gif\" onclick=\"rechercher();\" style=\"cursor:hand\" align=\"absmiddle\"></td>
		<td>Tout<br>cocher </td>
		<td>En<br>création</td>
		<td>A<br>contrôler</td>
		<td>Actifs</td>
		<td>Actifs<br>invisibles</td>
		<td>Supprimés</td>
		<td>Archivés<br>temporairement</td>
		<td>Archivés<br>définitivement</td>
		</tr>
		</table></form>";

function navigation($catidfunc , $isarticle=0){
	$separateur_navigation=" > ";
	global $DB_site2,$libelle,$navigationbit,$parentid;
	$requete_categorie="select catid,libelleFR, parentid from categorie where catid='$catidfunc' ";
	$navigation_categorie=$DB_site2->query_first($requete_categorie);
	if ($parentid==$catidfunc and $isarticle==0) 
		$navigationbit=$navigation_categorie[$libelle];
	else 
		$navigationbit="<a href=\"catalogue.php?catid=$navigation_categorie[catid]\">$navigation_categorie[$libelle]</a>" . $separateur_navigation . $navigationbit;

	if ($navigation_categorie['parentid']!=0) 
		navigation($navigation_categorie['parentid']);
}

// 
$active=array(0=>"inactif", 1=>"actif", 2=>"supprimé", 3=>"archivé définitivement", 4=>"Nouveau - à contrôler", 5=>"archivé temporairement", 6=>"en cours de création");
echo "<script language=javascript>self.name=\"ajouter_produit\"</script>";

$imghost="../";
if(ESPACE=="prod") $imghost="https://dedie.mon-espace-cuisson.com/";

if(isset($artid) && $artid!=""){
  $artcodetitre=$DB_site2->query_first("SELECT artcode,artcode_eurocave,marqueid,catid FROM article INNER JOIN article_marque USING(artid) WHERE artid=".intval($artid));
  $imgm=" <img src=\"/configurations/$host/images/marques/br/".$artcodetitre[2].".jpg\" width=\"100\">";
  $img="";
  $path="configurations/$host/images/produits/br/".$artid.".jpg";
  if(file_exists($rootpath.$path)) $img=" <img src=\"".$imghost.$path."\" height=\"70\" align=\"absmiddle\">";

  $navigationdebut="<a href=\"catalogue.php\">".${"title$langue"}."</a> > ";
  navigation($artcodetitre['catid']);
}

$pj=$DB_site2->query_first("SELECT COUNT(*) FROM article_files WHERE (artid=".intval($artid)." OR artid IN (SELECT artid FROM article_lot WHERE lotid=".intval($artid).")) AND ext<>''");
echo ("<div style=\"float:left;width:60%;\">".$navigationdebut.$navigationbit."<br>Ajout / Modification de produit / $artcodetitre[0] ($artcodetitre[1])</div><div style=\"float:left;margin-left:12px;\">".$img.$imgm."</div><span class=\"fileboxbutton\" onclick=\"fb.show();\"><img src=\"images/icon/pdf.png\"> Fiches produits, notices, schemas (".$pj[0].")</span>");


/* ajout de benoit : bouton pour vider le cache dans cloudflare */


echo "<div style=\"float:left;margin-left:12px;margin-top:12px;\">
	<form target=\"_blank\" method=\"post\" action=\"cloudflare.php?action=deletearticle&mode=mini\">
		<input type=\"hidden\" name=\"artid\" value=\"".$artid."\">
		<input type=\"submit\" value=\"retirer du cache\">
	</form>
	</div>";
/* fin ajout de benoit : bouton pour vider le cache de cloudflare */


echo "<div style=\"float:left;margin-left:12px;margin-top:5px;\">
	<form method=\"post\" action=\"ajout_produit_history.php?artid=".$artid."\">
		<input type=\"submit\" value=\"Histo. des prix\">
	</form>
	</div>";
//Récupération des données spécifiques au type d'article
$sql = "SELECT bdd_table, typearticleid FROM typearticle";
$sql .= " INNER JOIN article a USING(typearticleid)";
$sql .=" WHERE a.artid= '$artid'";
$table_type = $DB_site2->query_first($sql);
$typearticleid = $table_type[1];
$typearticle = $typearticleid;

$onglets = array();
$onglets_textes = array();

//on recupère les onglets en fonction du type de produit
$onglet = $DB_site2->query("SELECT * FROM onglet WHERE typeonglet = 'base' ORDER BY position ASC");
while($row = $DB_site2->fetch_array($onglet)){
	array_push ($onglets, $row['libelle']) ;
	array_push ($onglets_textes, $row['textonglet']) ;
}
include $rootpath."configurations/".$host."/articles/onglets_spe.php";

$maxonglets = 8 ;
$onglet_en_cours = 0 ;


if (isset ($prec) and $prec == "modif") // si on édite un pdt
	{
	$action2 = "suivi" ;
	}
//var_dump($_POST);
if (isset ($action2) and $action2 != "") // traitements des formulaires après (soummissiion)
	{
	if (!isset ($artid) or $artid == '') 
		{
		switch ($old)
			{
			case "infos": // ref, marque, lib, poids, frais g°, prix, tva, visible,stocks
				$erreur="";
				$mesparams=$DB_site2->query_first("select FRactif, DEactif, ENactif, ITactif, SPactif from parametresgeneraux");
				if ($catid == '')
					{
					$erreur .= "Vous devez sélectionner une catégorie pour créer votre article.<bR>" ;
					}
				if (in_array("FR" , $langues))
					{
					if ($mesparams[FRactif] == '1' and $libelleFR == '')
						{
						$erreur .= "Vous devez saisir un libellé en français pour votre article<bR>" ;
						}
					}
				if (in_array("DE" , $langues))
					{
					if ($mesparams[DEactif] == '1' and $libelleDE == '')
						{
						$erreur .= "Vous devez saisir un libellé en allemand pour votre article<bR>" ;
						}
					}
				if (in_array("EN" , $langues))
					{
					if ($mesparams[ENactif] == '1' and $libelleEN == '')
						{
						$erreur .= "Vous devez saisir un libellé en anglais pour votre article<bR>" ;
						}
					}
				if (in_array("IT" , $langues))
					{
					if ($mesparams[ITactif] == '1' and $libelleIT == '')
						{
						$erreur .= "Vous devez saisir un libellé en italien pour votre article<bR>" ;
						}
					}
				if (in_array("SP" , $langues))
					{
					if ($mesparams[SPactif] == '1' and $libelleSP == '')
						{
						$erreur .= "Vous devez saisir un libellé en espagnol pour votre article<bR>" ;
						}
					}
				if ($poid != '')
					{
					if (!is_numeric($poid))
						{
						$erreur .= "Le poids doit être numérique<bR>" ;
						}
					}
																																							
				if ($erreur == "")
					{
					// position
					$positions=$DB_site2->query_first("select page, pageposition from article where catid = '$catid' order by page desc, pageposition desc limit 1");
					if ($positions[pageposition] < $perpage)
						{
						$page = $positions[page] ;
						$pageposition = $positions[pageposition] + 1 ;
						}
					else
						{
						$page = $positions[page] + 1 ;
						$pageposition = 1 ;
						}	
					$date_reappro=intval($date_reappro);
					if($date_reappro!=0){
					    $sp=explode("/",$date_reappro);
					    $date_reappro=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
					}
					$opt_visible=6;
					$DB_site2->query("INSERT INTO article (catid,pro, artcode, prix, prixht, paht, prixconseilleht, ecopart, ecopartht, poid, tauxchoisi, fraisgestionid, libelleFR, libelleDE, 
							libelleEN, libelleIT, libelleSP, Titre2FR, Titre2DE, Titre2EN, Titre2IT, Titre2SP, active, commandable, date_appro,
							image, page, pageposition, delailivraison, delailivraison_txt, gammeFR, artcode_eurocave, dimensions, coloris, paysfabrication, garantie_incluse)
							  VALUES ($catid,'3', '$artcode', '$prix', '$prixht', '$paht', '$prixconseilleht', '$ecopart', '$ecopartht', '$poid', '$tauxtva', '$fraisgestion', '".mysqli_real_escape_string($DB_site2->link_id,$libelleFR)."', '".mysqli_real_escape_string($DB_site2->link_id,$libelleDE)."', 
							  '".mysqli_real_escape_string($DB_site2->link_id,$libelleEN)."', '".mysqli_real_escape_string($DB_site2->link_id,$libelleIT)."', '".mysqli_real_escape_string($DB_site2->link_id,$libelleSP)."', '".mysqli_real_escape_string($DB_site2->link_id,$titre2FR)."', '".mysqli_real_escape_string($DB_site2->link_id,$titre2DE)."', '".mysqli_real_escape_string($DB_site2->link_id,$titre2EN)."', '".mysqli_real_escape_string($DB_site2->link_id,$titre2IT)."', '".mysqli_real_escape_string($DB_site2->link_id,$titre2SP)."', 
							'$opt_visible', '$opt_commandable','$date_reappro', '', '$page', '$pageposition', '$delailivraison', '".mysqli_real_escape_string($DB_site2->link_id,$delailivraison_txt)."', '".mysqli_real_escape_string($DB_site2->link_id,$gammeFR)."', '".mysqli_real_escape_string($DB_site2->link_id,$artcode_eurocave)."', '".mysqli_real_escape_string($DB_site2->link_id,$dimensions)."', '".mysqli_real_escape_string($DB_site2->link_id,$coloris)."', '$paysfabrication', '$garantie_incluse')");
					$artid=$DB_site2->insert_id();
					
					// rue du commerce
					$DB_site2->query("INSERT INTO rueducommerce (artid, MCID) VALUES ('$artid', '$MCID')");
					
					if (in_array("4", $modules))
						{
						$DB_site2->query("INSERT INTO stocks (artid) VALUES ('$artid')");
						$DB_site2->query("INSERT INTO stock (artid) VALUES ('$artid')");
						}
					
					if (isset($mq) and $mq != 0)
						$DB_site2->query("INSERT INTO article_marque (artid, marqueid) VALUES ('$artid', '$mq')");
					else
						$DB_site2->query("DELETE FROM article_marque WHERE artid='$artid'");
					
					// mise à jour du treeview
					$DB_site2->query("update treeview_temporaire set dateline = dateline - 10000");
					$DB_site2->query("INSERT INTO suivi_article SET artid=$artid, date=".time().", commercial='".$user_info['username']."', suivi='Création article'");
				
					$erreur .= "Article créé";
					}
				else
					{
					$erreur .= "<br>" ;
					$action2 = $old ;
					}
				break ;
			case "img": // ERREUR !!!!
				echo "NOK" ;
				break ;
			case "desc": // ERREUR !!!!
				echo "NOK" ;
				break ;
			case "desc2": // ERREUR !!!!
				echo "NOK" ;
				break ;				
			case "caract": // ERREUR !!!!
				echo "NOK" ;
				break ;
			}
		} 
	if (isset ($artid) and $artid != "") 
		{
		switch ($old) // action d'où on part
			{
			case "suivi":
			if($_POST['suivi']!=""){
			    $DB_site2->query("INSERT INTO suivi_article SET artid=$artid, commercial='".$user_info['username']."', date=".time().", suivi='".nl2br(mysqli_real_escape_string($DB_site2->link_id,$_POST['suivi']))."'");
			}
			break;
			case "infos": // update
				$erreur="";
				$mesparams=$DB_site2->query_first("select FRactif, DEactif, ENactif, ITactif, SPactif from parametresgeneraux");
				if ($catid == '')
					{
				$erreur .= "Vous devez sélectionner une catégorie pour créer votre article.<bR>" ;
					}
				if (in_array("FR" , $langues))
					{
					if ($mesparams[FRactif] == '1' and $libelleFR == '')
						{
						$erreur .= "Vous devez saisir un libellé en français pour votre article<bR>" ;
						}
					}
				if (in_array("DE" , $langues))
					{
					if ($mesparams[DEactif] == '1' and $libelleDE == '')
						{
						$erreur .= "Vous devez saisir un libellé en allemand pour votre article<bR>" ;
						}
					}
				if (in_array("EN" , $langues))
					{
					if ($mesparams[ENactif] == '1' and $libelleEN == '')
						{
						$erreur .= "Vous devez saisir un libellé en anglais pour votre article<bR>" ;
						}
					}
				if (in_array("IT" , $langues))
					{
					if ($mesparams[ITactif] == '1' and $libelleIT == '')
						{
						$erreur .= "Vous devez saisir un libellé en italien pour votre article<bR>" ;
						}
					}
				if (in_array("SP" , $langues))
					{
					if ($mesparams[SPactif] == '1' and $libelleSP == '')
						{
						$erreur .= "Vous devez saisir un libellé en espagnol pour votre article<bR>" ;
						}
					}
				if ($poid != '')
					{
					if (!is_numeric($poid))
						{
						$erreur .= "Le poids doit être numérique<bR>" ;
						}
					}
																																										
				if ($erreur == "")
					{
					$libelleFR = $libelleFR;
					$libelleDE = $libelleDE;
					$libelleEN = $libelleEN;
					$libelleIT = $libelleIT;
					$libelleSP = $libelleSP;
					$titre2FR = $titre2FR;
					$titre2DE = $titre2DE;
					$titre2EN = $titre2EN;
					$titre2IT = $titre2IT;
					$titre2SP = $titre2SP;
					$gammeFR = $gammeFR;
					$paysidfab=intval($paysidfab);

					if($date_reappro!=""){
					    $sp=explode("/",$date_reappro);
					    $date_reappro=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
					}
					if($secondchoix=='on'){ 
						$secondchoix = 1;
						$exist = $DB_site2->query_first("SELECT * from secondchoix where artid=".$artid);
						if(is_null($exist[0])){
							$DB_site2->query("INSERT INTO secondchoix SET artid=".$artid);
						}
						
					}else{
						$secondchoix = 0;
						$DB_site2->query("DELETE FROM secondchoix WHERE artid=".$artid);
					}

					$DB_site2->query("update article set artcode = '$artcode', typearticleid = '".$_POST['typearticle']."',
					fraisgestionid = '$fraisgestion', libelleFR = '".mysqli_real_escape_string($DB_site2->link_id,$libelleFR)."', libelleDE = '".mysqli_real_escape_string($DB_site2->link_id,$libelleDE)."', libelleEN = '".mysqli_real_escape_string($DB_site2->link_id,$libelleEN)."',
					libelleIT = '".mysqli_real_escape_string($DB_site2->link_id,$libelleIT)."', libelleSP = '".mysqli_real_escape_string($DB_site2->link_id,$libelleSP)."', artcode_eurocave = '".mysqli_real_escape_string($DB_site2->link_id,$artcode_eurocave)."', 
					refs_fabricant='".mysqli_real_escape_string($DB_site2->link_id,$refscons)."', secondchoix='$secondchoix', gammeFR='".mysqli_real_escape_string($DB_site2->link_id,$gammeFR)."' where artid = '$artid'");
					
					modifhistory($_POST);
					
					// rue du commerce
					$existeRueducommerce = $DB_site2->query_first("SELECT COUNT(artid) FROM rueducommerce WHERE artid = '$artid'");
					if ($existeRueducommerce[0] == 0)
						$DB_site2->query("INSERT INTO rueducommerce (artid, MCID) VALUES ('$artid', '$MCID')");
					else
						$DB_site2->query("UPDATE rueducommerce SET MCID = '$MCID' WHERE artid = '$artid'");
					
					
					if (isset($mq) and $mq != 0)
						{
						$marque_existante=$DB_site2->query_first("select marqueid from article_marque where artid = '$artid'");
						if ($marque_existante[marqueid] != "")
							$DB_site2->query("update article_marque set marqueid = '$mq' where artid = '$artid'");
						else
							$DB_site2->query("INSERT INTO article_marque (artid, marqueid) VALUES ('$artid', '$mq')");
						}
					else
						$DB_site2->query("DELETE FROM article_marque WHERE artid='$artid'");
					// mise à jour du treeview
					$DB_site2->query("update treeview_temporaire set dateline = dateline - 10000");

					$erreur .= "Article modifié";
					}
				else
					{
					$erreur .= "<br>" ;
					$action2 = $old ;
					}
				$DB_site2->query("DELETE FROM articlerecherche_finition WHERE artid='$artid' ");
				$finitions=$DB_site2->query("SELECT * FROM recherche_finition");
				while ($finition=$DB_site2->fetch_array($finitions))
					if (${"finition".$finition[finitionid]} =="on")
						$DB_site2->query("INSERT INTO articlerecherche_finition (artid, finitionid) VALUES ('$artid','$finition[finitionid]') ");	
				break ;
			case "visible":
				if($_POST['delautovisible']!="0") $DB_site2->query("DELETE FROM article_visible WHERE id=".intval($_POST['delautovisible']));

				if($_POST['newdatevisible']!=""){
				    $sp=explode("/",$_POST['newdatevisible']);
				    $newdate=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
				    $DB_site2->query("INSERT INTO article_visible SET artid='$artid', date=".intval($newdate).", active=".intval($_POST['autovisible']));
				}

				if($_POST['delautocomm']!="0") $DB_site2->query("DELETE FROM article_commandable WHERE id=".intval($_POST['delautocomm']));

				if($_POST['newdatecomm']!=""){
				    $sp=explode("/",$_POST['newdatecomm']);
				    $newdate=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
				    $DB_site2->query("INSERT INTO article_commandable SET artid='$artid', date=".intval($newdate).", commandable=".intval($_POST['autocomm']));
				}

				if($date_reappro!=""){
					    $sp=explode("/",$date_reappro);
					    $date_reappro=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
				}
				if(!isset($_POST['pro'])) $_POST['pro']=array(0);
				foreach($_POST['opt_visible'] as $site_code=>$active)
				    $DB_site2->query("UPDATE article_site SET active=".intval($active)." WHERE artid=".$artid." AND site_code='".$site_code."'");
				//$DB_site2->query("update article set active = '".$_POST['opt_visible']['base']."', commandable = '$opt_commandable', delailivraison = '$delailivraison', date_appro='$date_reappro', redirect='$redirect',pro='".intval(array_sum($_POST['pro']))."' where artid = '$artid'");
				$DB_site2->query("update article set active = '".$_POST['opt_visible']['base']."', commandable = '$opt_commandable', delailivraison = '$delailivraison', date_appro='$date_reappro', redirect='$redirect',pro='3' where artid = '$artid'");

				modifhistory($_POST);

				if($_POST['remp_artid']!="" && $_POST['rempos']!=""){
				    $remp=$DB_site2->query_first("SELECT * FROM article_remplacement WHERE artid='".$artid."' AND position=".intval($_POST['rempos']));
				    if($remp['artid']==$artid){
				      if($_POST['remp_artid']==0)
					   $DB_site2->query("DELETE FROM article_remplacement WHERE id=".$remp['id']);
				      else
					   $DB_site2->query("UPDATE article_remplacement SET remp_artid='".$_POST['remp_artid']."' WHERE id=".$remp['id']);
				    }else
				      $DB_site2->query("INSERT INTO article_remplacement SET artid='".$artid."', remp_artid='".$_POST['remp_artid']."', position=".intval($_POST['rempos']));
                    }
                                
                    //$cloud = new cloudflare_api($DB_site2,$rootpath);
                    //$cloud->purgeAll($cloudflare_email, $cloudflare_api, $cloudflare_zone_id_mcav);
                    //$cloud->purgeAll($cloudflare_email, $cloudflare_api, $cloudflare_zone_id_mwc);
			break;
			case "montage":
				$DB_site2->query("UPDATE article SET montage='".intval($_POST['montage'])."', montage_heures='".floatval($_POST['montage_heures'])."' WHERE artid=".intval($_POST['artid']));
				$DB_site2->query("UPDATE article_files SET infos='' WHERE infos LIKE 'notice%' AND artid=".$_POST['artid']);
				foreach($langues as $langue) if(intval($_POST['notice'.$langue])>0) $DB_site2->query("UPDATE article_files SET infos='notice$langue' WHERE id=".intval($_POST['notice'.$langue]));
			break;
			case "prix":
				foreach($_POST['prix'] as $site_code=>$prix){
				    $article_site=$DB_site2->query_first("SELECT id FROM article_site WHERE artid=".$artid." AND site_code='".$site_code."'");
				    $prixconseilleht=round($_POST['prixconseille'][$site_code]/(1+$_POST['tvapc'][$site_code]/100),5);
				    if($article_site['id']!="")
					$DB_site2->query("UPDATE article_site SET prix=".floatval($prix).", prixconseilleht=".$prixconseilleht.", tauxchoisi='".$_POST['tauxtva'][$site_code]."' WHERE site_code='".$site_code."' AND artid=".$artid);
				    elseif($prix!=="")
					$DB_site2->query("INSERT INTO article_site SET artid=".$artid.", site_code='".$site_code."', prix=".floatval($prix).", prixconseilleht=".$prixconseilleht.", tauxchoisi='".$_POST['tauxtva'][$site_code]."', active=6");
					
				}
				$prixconseilleht=round($_POST['prixconseille']['base']/(1+$_POST['tvapc']['base']/100),5);
				$DB_site2->query("UPDATE article SET prix=".floatval($_POST['prix']['base']).", prixht='".round($_POST['prixht']['base'],3)."', paht=".floatval($_POST['paht']['base']).", ecopartht=".floatval($_POST['ecopartht']['base']).", ecopart=".floatval($_POST['ecopart']['base']).", prixtrspt=".floatval($_POST['prixtrspt']['base']).", prixconseilleht=".$prixconseilleht.",tauxchoisi='".$_POST['tauxtva']['base']."' WHERE artid=".$artid);

				if(isset($_POST['lotprix'])){
				    foreach($_POST['lotprix'] as $site_code=>$listlotprix){
					foreach($listlotprix as $lartid=>$pv){
					    if($site_code=="base") $DB_site2->query("UPDATE article_lot SET prix=".floatval($pv).", paht=".floatval($_POST['lotpaht'][$site_code][$lartid])." WHERE id=".$lartid);
					    $article_lot_site=$DB_site2->query_first("SELECT id FROM article_lot_site WHERE alid=".$lartid." AND site_code='".$site_code."'");
					    if($article_lot_site['id']=="")
						$DB_site2->query("INSERT INTO article_lot_site SET alid='".$lartid."', site_code='".$site_code."', prix='".floatval($pv)."', paht='".floatval($_POST['lotpaht'][$site_code][$lartid])."'");
					    else
						$DB_site2->query("UPDATE article_lot_site SET prix='".floatval($pv)."', paht='".floatval($_POST['lotpaht'][$site_code][$lartid])."' WHERE id=".$article_lot_site['id']);
					    }
				    }
				}
				include_once('article_fournisseurs.php');
				update_fournisseurs($DB_site2, $artid, $_POST);
				modifhistory($_POST);
			break;
			case "img":
				if (isset ($del) and $del == 1)
					{
					$action2 = $old ;
					}
				$erreur = "" ;
				$image1 = 0 ;
				$image11 = 0 ;
				$imageplus = 0 ; 
				$imageplus1 = 0 ; 
				
				if ($_FILES['articleimg']['name']!="") 
					{
					$image1 = 1 ;
					if ($_FILES['articleimg']['error']==1) 
						{
						$erreur.="Erreur !!! Le fichier téléchargé (image de base) excède la taille autorisée par le serveur (> 2Mb)";
						}
					/*elseif ($_FILES['articleimg']['size']>1048576) 
						{
						$erreur.="erreur !!! Le fichier téléchargé (image de base) excède la taille autorisée:<br>Vous ne pouvez télécharger qu'un fichier dont la taille est inférieur à 1 Mo";
						}*/	 
					elseif ($_FILES['articleimg']['error']==3) 
						{
						$erreur.="erreur !!! Le fichier (image de base) n'a été que partiellement téléchargé.";
						}
					elseif ($_FILES['articleimg']['error']==2) 
						{
						$erreur.="erreur !!! ereur inconnue (image de base) ...";
						}
					elseif ($_FILES['articleimg']['error']==4) 
						{
						$erreur.="erreur !!! Aucun fichier n'a été téléchargé (image de base).";
						}
					elseif ($_FILES['articleimg']['type']!="image/pjpeg" && $_FILES['articleimg']['type']!="image/gif" && $_FILES['articleimg']['type']!="image/png" && $_FILES['articleimg']['type']!="image/jpeg") 
						{
						$erreur.="erreur !!! Le fichier téléchargé (image de base) n'est pas du type autorisé:<br>Vous ne pouvez télécharger qu'un fichier du type JPEG , JPG , GIF ou PNG (type envoyé : ".$_FILES['articleimg']['type'].")";
						}
					}
				if ($articleimgurl != "") 
					{
					$image11 = 1 ;
					$reg = explode(".", $articleimgurl) ;
					$nb = count($reg) ;
					$extensionurl = $reg[$nb-1] ;
					$extensionurl = strtolower($extensionurl) ;
					if ($extensionurl == "jpeg")
						{
						$extensionurl = "jpg" ;
						}
					if ($extensionurl != "jpg" and $extensionurl != "gif" and $extensionurl != "png") 
						{
						$erreur .= "erreur !!! Le fichier téléchargé (image de base) n'est pas du type autorisé :
						<br>Vous ne pouvez télécharger qu'un fichier du type JPEG, JPG, GIF ou PNG (type envoyé : $extension)";
						}
					}
				for($i=0;$i<=7;$i++){
				    if ($_FILES['articleimgsup'.$i]['name']!="") 
					{
					$imageplus = 1 ;
					if ($_FILES['articleimgsup'.$i]['error']==1) 
						{
						$erreur.="Erreur !!! Le fichier téléchargé (image supplémentaire n°".($i+1).") excède la taille autorisée par le serveur (> 2Mb)";
						}
					/*elseif ($_FILES['articleimgsup']['size']>1048576) 
						{
						$erreur.="erreur !!! Le fichier téléchargé (image supplémentaire) excède la taille autorisée:<br>Vous ne pouvez télécharger qu'un fichier dont la taille est inférieur à 1 Mo";
						}	 */
					elseif ($_FILES['articleimgsup'.$i]['error']==3) 
						{
						$erreur.="erreur !!! Le fichier n°".($i+1)." n'a été que partiellement téléchargé (image supplémentaire).";
						}
					elseif ($_FILES['articleimgsup'.$i]['error']==2) 
						{
						$erreur.="erreur !!! ereur inconnue (image supplémentaire n°".($i+1).") ...";
						}
					elseif ($_FILES['articleimgsup'.$i]['error']==4) 
						{
						$erreur.="erreur !!! Aucun fichier n'a été téléchargé (image supplémentaire n°".($i+1).").";
						}
					elseif ($_FILES['articleimgsup'.$i]['type']!="image/pjpeg" && $_FILES['articleimgsup'.$i]['type']!="image/gif" && $_FILES['articleimgsup'.$i]['type']!="image/png" && $_FILES['articleimgsup'.$i]['type']!="image/jpeg") 
						{
						$erreur.="erreur !!! Le fichier téléchargé (image supplémentaire n°".($i+1).") n'est pas du type autorisé:<br>Vous ne pouvez télécharger qu'un fichier du type JPEG , JPG , GIF ou PNG (type envoyé : ".$_FILES['articleimgsup'.$i]['type'].")";
						}
					}
				}
				if ($articleimgsupurl != "") 
					{
					$imageplus1 = 1 ;
					$reg = explode(".", $articleimgsupurl) ;
					$nb = count($reg) ;
					$extensionurlsup = $reg[$nb-1] ;
					$extensionurlsup = strtolower($extensionurlsup) ;
					if ($extensionurlsup == "jpeg")
						{
						$extensionurlsup = "jpg" ;
						}
					if ($extensionurlsup != "jpg" and $extensionurlsup != "gif" and $extensionurlsup != "png") 
						{
						$erreur .= "erreur !!! Le fichier téléchargé (image supplémentaire) n'est pas du type autorisé :
						<br>Vous ne pouvez télécharger qu'un fichier du type JPEG, JPG, GIF ou PNG (type envoyé : $extension)";
						}
					}
				if ($erreur == "")
					{
					if (isset($legendebase) and $legendebase != "")
						{
						$art=$DB_site2->query_first("select image from article where artid = '$artid'");
						if ($art[0] != NULL)
							{
							$DB_site2->query("update article set legende = '".mysqli_real_escape_string($DB_site2->link_id,$legendebase)."' where artid = '$artid'");
							}
						}
					if (isset($legendesuplem) and count($legendesuplem) > 0)
						{
						foreach ($legendesuplem as $cle=>$valeur)
							{
							if ($cle != "")
								{
								$art=$DB_site2->query_first("select * from articlephoto where artid = '$artid' and articlephotoid = '$cle'");
								if ($art[0] != "")
									{
									$DB_site2->query("update articlephoto set legende = '$valeur' where artid = '$artid' and articlephotoid = '$cle'");
									}
								}
							}
						}
					if ($image1 == 1)
						{
						if ($_FILES['articleimg']['type']=="image/pjpeg") 
							{
							$type_fichier="jpg";
							}
						if ($_FILES['articleimg']['type']=="image/jpeg") 
							{
							$type_fichier="jpg";
							}
						if ($_FILES['articleimg']['type']=="image/gif") 
							{
							$type_fichier="gif";
							}
						if ($_FILES['articleimg']['type']=="image/png") 
							{
							$type_fichier="png";
							}
						$DB_site2->query("update article set image = '$type_fichier', legende = '$legende' where artid = '$artid'");
						/* copie de l'image sans la redimmensionner à la racine de images/produits */
						$nom_fic=$rootpath."configurations/$host/images/produits/$artid.$type_fichier";
						copier_image($nom_fic,"articleimg");
						/* redimmensionnement de cette image pour chaque dossier */
						$destination0=$rootpath."configurations/$host/images/produits/article/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination0,$article_largeur,$article_hauteur);
						$destination1=$rootpath."configurations/$host/images/produits/categorie/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination1,$categorie_largeur,$categorie_hauteur);
						$destination2=$rootpath."configurations/$host/images/produits/cdc_categ/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
						$destination3=$rootpath."configurations/$host/images/produits/cdc_index/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
						$destination4=$rootpath."configurations/$host/images/produits/nouveautes_categ/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
						$destination5=$rootpath."configurations/$host/images/produits/nouveautes_index/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
						$destination6=$rootpath."configurations/$host/images/produits/promos_categ/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
						$destination7=$rootpath."configurations/$host/images/produits/promos_index/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination7,$promos_index_largeur,$promos_index_hauteur);
						$destination8=$rootpath."configurations/$host/images/produits/topventes_categ/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
						$destination9=$rootpath."configurations/$host/images/produits/topventes_index/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
						$destination10=$rootpath."configurations/$host/images/produits/br/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination10,$largeurimages,$hauteurimages);
						if (isset($hauteurphotos) && isset($largeurphotos)) {
							$destination11=$rootpath."configurations/$host/images/produits/photos/$artid.$type_fichier";
							redimentionner_image($nom_fic,$destination11,$largeurphotos,$hauteurphotos);
							}
						$action2 = $old ;
						}
					if ($image11 == 1)
						{
						$type_fichier = $extensionurl ;
						$DB_site2->query("update article set image = '$type_fichier', legende = '$legende' where artid = '$artid'");
						$nom_fic=$rootpath."configurations/$host/images/produits/$artid.$type_fichier";
						if (copier_image_url($nom_fic,$articleimgurl,$type_fichier) != false)
							{
							$destination0=$rootpath."configurations/$host/images/produits/article/$artid.$type_fichier";
							redimentionner_image($nom_fic,$destination0,$article_largeur,$article_hauteur);
							$destination1=$rootpath."configurations/$host/images/produits/categorie/$artid.$type_fichier";
							redimentionner_image($nom_fic,$destination1,$categorie_largeur,$categorie_hauteur);
							$destination2=$rootpath."configurations/$host/images/produits/cdc_categ/$artid.$type_fichier";
							redimentionner_image($nom_fic,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
							$destination3=$rootpath."configurations/$host/images/produits/cdc_index/$artid.$type_fichier";
							redimentionner_image($nom_fic,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
							$destination4=$rootpath."configurations/$host/images/produits/nouveautes_categ/$artid.$type_fichier";
							redimentionner_image($nom_fic,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
							$destination5=$rootpath."configurations/$host/images/produits/nouveautes_index/$artid.$type_fichier";
							redimentionner_image($nom_fic,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
							$destination6=$rootpath."configurations/$host/images/produits/promos_categ/$artid.$type_fichier";
							redimentionner_image($nom_fic,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
							$destination7=$rootpath."configurations/$host/images/produits/promos_index/$artid.$type_fichier";
							redimentionner_image($nom_fic,$destination7,$promos_index_largeur,$promos_index_hauteur);
							$destination8=$rootpath."configurations/$host/images/produits/topventes_categ/$artid.$type_fichier";
							redimentionner_image($nom_fic,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
							$destination9=$rootpath."configurations/$host/images/produits/topventes_index/$artid.$type_fichier";
							redimentionner_image($nom_fic,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
							$destination10=$rootpath."configurations/$host/images/produits/br/$artid.$type_fichier";
							redimentionner_image($nom_fic,$destination10,$largeurimages,$hauteurimages);
							if (isset($hauteurphotos) && isset($largeurphotos)) {
								$destination11=$rootpath."configurations/$host/images/produits/photos/$artid.$type_fichier";
								redimentionner_image($nom_fic,$destination11,$largeurphotos,$hauteurphotos);
								}
							}
						else
							{
							$erreur .= "Erreur : l'image n'existe pas à cette adresse" ;
							$DB_site2->query("update article set image = '', legende = '' where artid = '$artid'");
							}
						$action2 = $old ;
						}
					if ($imageplus == 1){
					    for($i=0;$i<7;$i++){
						if($_FILES['articleimgsup'.$i]['name']!=""){
						  if ($_FILES['articleimgsup'.$i]['type']=="image/pjpeg") $type_fichier="jpg";
						  if ($_FILES['articleimgsup'.$i]['type']=="image/jpeg") $type_fichier="jpg";
						  if ($_FILES['articleimgsup'.$i]['type']=="image/gif") $type_fichier="gif";
						  if ($_FILES['articleimgsup'.$i]['type']=="image/png") $type_fichier="png";
							 
						  $DB_site2->query("insert into articlephoto (artid, image, legende) values ('$artid', '$type_fichier', '$legendesup')");
						  $num=$DB_site2->insert_id() ;
						
						  /* copie de l'image sans la redimmensionner à la racine de images/produits */
						  $nom_fic2=$rootpath."configurations/$host/images/produits/".$artid."_".$num.".".$type_fichier;
						  copier_image($nom_fic2,"articleimgsup".$i);
						  /* redimmensionnement de cette image pour chaque dossier */
						  $destination0=$rootpath."configurations/$host/images/produits/article/".$artid."_".$num.".".$type_fichier;
						  redimentionner_image($nom_fic2,$destination0,$article_largeur,$article_hauteur);
						  $destination1=$rootpath."configurations/$host/images/produits/categorie/".$artid."_".$num.".".$type_fichier;
						  redimentionner_image($nom_fic2,$destination1,$categorie_largeur,$categorie_hauteur);
						  $destination2=$rootpath."configurations/$host/images/produits/cdc_categ/".$artid."_".$num.".".$type_fichier;
						  redimentionner_image($nom_fic2,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
						  $destination3=$rootpath."configurations/$host/images/produits/cdc_index/".$artid."_".$num.".".$type_fichier;
						  redimentionner_image($nom_fic2,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
						  $destination4=$rootpath."configurations/$host/images/produits/nouveautes_categ/".$artid."_".$num.".".$type_fichier;
						  redimentionner_image($nom_fic2,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
						  $destination5=$rootpath."configurations/$host/images/produits/nouveautes_index/".$artid."_".$num.".".$type_fichier;
						  redimentionner_image($nom_fic2,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
						  $destination6=$rootpath."configurations/$host/images/produits/promos_categ/".$artid."_".$num.".".$type_fichier;
						  redimentionner_image($nom_fic2,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
						  $destination7=$rootpath."configurations/$host/images/produits/promos_index/".$artid."_".$num.".".$type_fichier;
						  redimentionner_image($nom_fic2,$destination7,$promos_index_largeur,$promos_index_hauteur);
						  $destination8=$rootpath."configurations/$host/images/produits/topventes_categ/".$artid."_".$num.".".$type_fichier;
						  redimentionner_image($nom_fic2,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
						  $destination9=$rootpath."configurations/$host/images/produits/topventes_index/".$artid."_".$num.".".$type_fichier;
						  redimentionner_image($nom_fic2,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
						  $destination10=$rootpath."configurations/$host/images/produits/br/".$artid."_".$num.".".$type_fichier;
						  redimentionner_image($nom_fic2,$destination10,$largeurimages,$hauteurimages);
						  if (isset($hauteurphotos) && isset($largeurphotos)) {
							$destination11=$rootpath."configurations/$host/images/produits/photos/".$artid."_".$num.".".$type_fichier;
							redimentionner_image($nom_fic2,$destination11,$largeurphotos,$hauteurphotos);
						  }
						}
					    }
					    $action2 = $old ;
					}
					if ($imageplus1 == 1)
						{
						$type_fichier = $extensionurlsup ;
						$DB_site2->query("insert into articlephoto (artid, image, legende) values ('$artid', '$type_fichier', '$legendesup')");
						$num=$DB_site2->insert_id() ;
						$nom_fic2=$rootpath."configurations/$host/images/produits/".$artid."_".$num.".".$type_fichier;
						if (copier_image_url($nom_fic2,$articleimgsupurl,$type_fichier) != false)
							{
							$destination0=$rootpath."configurations/$host/images/produits/article/".$artid."_".$num.".".$type_fichier;
							redimentionner_image($nom_fic2,$destination0,$article_largeur,$article_hauteur);
							$destination1=$rootpath."configurations/$host/images/produits/categorie/".$artid."_".$num.".".$type_fichier;
							redimentionner_image($nom_fic2,$destination1,$categorie_largeur,$categorie_hauteur);
							$destination2=$rootpath."configurations/$host/images/produits/cdc_categ/".$artid."_".$num.".".$type_fichier;
							redimentionner_image($nom_fic2,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
							$destination3=$rootpath."configurations/$host/images/produits/cdc_index/".$artid."_".$num.".".$type_fichier;
							redimentionner_image($nom_fic2,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
							$destination4=$rootpath."configurations/$host/images/produits/nouveautes_categ/".$artid."_".$num.".".$type_fichier;
							redimentionner_image($nom_fic2,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
							$destination5=$rootpath."configurations/$host/images/produits/nouveautes_index/".$artid."_".$num.".".$type_fichier;
							redimentionner_image($nom_fic2,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
							$destination6=$rootpath."configurations/$host/images/produits/promos_categ/".$artid."_".$num.".".$type_fichier;
							redimentionner_image($nom_fic2,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
							$destination7=$rootpath."configurations/$host/images/produits/promos_index/".$artid."_".$num.".".$type_fichier;
							redimentionner_image($nom_fic2,$destination7,$promos_index_largeur,$promos_index_hauteur);
							$destination8=$rootpath."configurations/$host/images/produits/topventes_categ/".$artid."_".$num.".".$type_fichier;
							redimentionner_image($nom_fic2,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
							$destination9=$rootpath."configurations/$host/images/produits/topventes_index/".$artid."_".$num.".".$type_fichier;
							redimentionner_image($nom_fic2,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
							$destination10=$rootpath."configurations/$host/images/produits/br/".$artid."_".$num.".".$type_fichier;
							redimentionner_image($nom_fic2,$destination10,$largeurimages,$hauteurimages);
							if (isset($hauteurphotos) && isset($largeurphotos)) {
								$destination11=$rootpath."configurations/$host/images/produits/photos/".$artid."_".$num.".".$type_fichier;
								redimentionner_image($nom_fic2,$destination11,$largeurphotos,$hauteurphotos);
								}
							}
						else
							{
							$erreur .= "Erreur : l'image n'existe pas à cette adresse" ;
							$DB_site2->query("delete from articlephoto where articlephotoid = '$num'");
							}
						$action2 = $old ;
						}
					if(isset($_GET['langue']) && isset($_GET['imgid'])) $DB_site2->query("UPDATE articlephoto SET langue='".$_GET['langue']."' WHERE articlephotoid=".$_GET['imgid']);
					if(isset($_GET['util']) && isset($_GET['imgid'])) $DB_site2->query("UPDATE articlephoto SET visible='".$_GET['util']."' WHERE articlephotoid=".$_GET['imgid']);
					if(isset($_GET['conseil']) && isset($_GET['imgid'])) $DB_site2->query("UPDATE articlephoto SET conseil='".$_GET['conseil']."' WHERE articlephotoid=".$_GET['imgid']);
					if(isset($_GET['site']) && isset($_GET['imgid'])) $DB_site2->query("UPDATE articlephoto SET pro='".intval($_GET['site'])."' WHERE articlephotoid=".$_GET['imgid']);
					if(isset($_POST['createposition'])){
					     $i=0;
					     $requete=$DB_site2->query("SELECT * FROM articlephoto WHERE artid=$artid ORDER BY position");
					     while($img=$DB_site2->fetch_array($requete)){
                            $DB_site2->query("UPDATE articlephoto SET position=$i WHERE articlephotoid=".$img['articlephotoid']);
                            $i++;
					     }
                    }
					if(isset($_GET['ambiance']) && isset($_GET['imgid'])) $DB_site2->query("UPDATE articlephoto SET ambiance=".intval($_GET['ambiance'])." WHERE articlephotoid=".intval($_GET['imgid']));
					if ($erreur == "")
						{
						$erreur .= "Article modifié";
						}
					}
				else
					{
					$erreur .= "<br>" ;
					$action2 = $old ;
					}
				break ;

			case "video":
				$virg=$sql="";
				foreach($langues as $langue){
				    $video = str_replace("watch?v=","embed/",$_POST['video'.$langue.'0']);
				    $sql.=$virg."`video_code".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$video)."'";
				    $virg=",";
				}
				$DB_site2->query("UPDATE article SET ".$sql." WHERE artid=".$artid);
				break;
				
			case "icon":
				foreach($_POST['du'] as $iconid=>$du){
				  $ai=$DB_site2->query_first("SELECT * FROM article_icon WHERE artid=$artid AND iconid=$iconid");
				  $du=strtots($_POST['du'][$iconid]);
				  $au=strtots($_POST['au'][$iconid]);    
			
				  if($ai['artid']=="")
				    $DB_site2->query("INSERT INTO article_icon SET artid=".$artid.", iconid=".$iconid.", active=".intval(isset($_POST['icons'][$iconid])).", du=".$du.", au=".$au);
				  else
				    $DB_site2->query("UPDATE article_icon SET active=".intval(isset($_POST['icons'][$iconid])).", du=".$du.", au=".$au." WHERE artid=".$artid." AND iconid=".$iconid);
				}
				break;
				
			case "desc":
				$virg="";
				$sql="UPDATE article SET";
				foreach($host_conf->langues as $lang){
				    $sql.=$virg." description".$lang."='".mysqli_real_escape_string($DB_site2->link_id,$_POST['content'.$lang])."'";
				    $virg=",";
				}
				$sql.=" WHERE artid=".$artid;
				$DB_site2->query($sql);
				$erreur = "Article modifié" ;
				break ;
			case "desc2":
				$description = "description2$lang" ;
				$DB_site2->query("update article set $description = '$content' where artid = '$artid'");
				$erreur = "Article modifié" ;
				break ;
			case "argument":
				$DB_site2->query("UPDATE article SET argument='".mysqli_real_escape_string($DB_site2->link_id,$_POST['argument'])."' WHERE artid='$artid'");
				$erreur = "Article modifié" ;
				modifhistory($_POST);
				break;
			case "ref": 
				$ref_titleFR = addslashes($ref_titleFR) ;
				$ref_titleDE = addslashes($ref_titleDE) ;
				$ref_titleEN = addslashes($ref_titleEN) ;
				$ref_titleIT = addslashes($ref_titleIT) ;
				$ref_titleSP = addslashes($ref_titleSP) ;
				$ref_descriptionFR = addslashes($ref_descriptionFR) ;
				$ref_descriptionDE = addslashes($ref_descriptionDE) ;
				$ref_descriptionEN = addslashes($ref_descriptionEN) ;
				$ref_descriptionIT = addslashes($ref_descriptionIT) ;
				$ref_descriptionSP = addslashes($ref_descriptionSP) ;
				$ref_keywordsFR = addslashes($ref_keywordsFR) ;
				$ref_keywordsDE = addslashes($ref_keywordsDE) ;
				$ref_keywordsEN = addslashes($ref_keywordsEN) ;
				$ref_keywordsIT = addslashes($ref_keywordsIT) ;
				$ref_keywordsSP = addslashes($ref_keywordsSP) ;
				$DB_site2->query("update article set ref_titleFR = '$ref_titleFR', ref_titleDE = '$ref_titleDE', ref_titleEN = '$ref_titleEN', 
				ref_titleIT = '$ref_titleIT', ref_titleSP = '$ref_titleSP',	ref_descriptionFR = '$ref_descriptionFR', 
				ref_descriptionDE = '$ref_descriptionDE', ref_descriptionEN = '$ref_descriptionEN', ref_descriptionIT = '$ref_descriptionIT', 
				ref_descriptionSP = '$ref_descriptionSP', ref_keywordsFR = '$ref_keywordsFR', ref_keywordsDE = '$ref_keywordsDE', 
				ref_keywordsEN = '$ref_keywordsEN', ref_keywordsIT = '$ref_keywordsIT', ref_keywordsSP = '$ref_keywordsSP', bandeau_defilant='$bandeaudefilantFR'
				where artid = '$artid'");
				$erreur = "Article modifié";
				break ;
			case "caract":
				$ral=new ral($DB_site2);
				$modification = 0 ;
				if (isset ($del) and $del == 1)
					{
					$action2 = $old ;
					}	
				else
					{
					if (isset($new) and $new == "1")
						{
						$action2 = $old ;
						}
					$erreur = "" ;
					$old_artvals = $DB_site2->query("SELECT * FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) WHERE artid = '$artid';");
					$DB_site2->query("delete from article_caractval where artid = '$artid';");
					
					//construire une array similaire a celle du POST avec les ancien valeur
					$old_array = array();
					while($old_artval = $DB_site2->fetch_array($old_artvals))
						$old_array[$old_artval['caractid']][$old_artval['caractvalid']] = 1;
					
					//array de modif
					$caractadd_array = array();
					$caractrem_array = array();
					$caractvalrem_array = array();
					
					$tauxfrance = $DB_site2->query_first("SELECT TVAtauxnormal FROM pays WHERE paysid=57");
					
					//construire les VALUES
					$values_array = array();
					if(isset($_POST['chk']) && sizeof($_POST['chk']))
						foreach($_POST['chk'] as $key => $value) {
							$count_old = isset($old_array[$key]) ? sizeof($old_array[$key]) : 0;
							$count_new = sizeof($value);
							
							//on a ajouter une caractéristique ?
							if(($count_old < 2) && ($count_new > 1))
								$caractadd_array[] = $key;
							
							foreach($value as $k => $v)
								{
								$erreurimg = "";
								$type_fichier = "";
								if (isset($_POST['imageok'][$key][$k]) and $_POST['imageok'][$key][$k] == "1")
									{
									$type_fichier = "jpg";
									}
								else
									{
									if ($_FILES["image-".$k]['name']!="") 
										{
										if ($_FILES["image-".$k]['error']==1) 
											$erreurimg.="Erreur !!! Le fichier téléchargé (image de base) excède la taille autorisée par le serveur (> 2Mb)";
										elseif ($_FILES["image-".$k]['error']==3) 
											$erreurimg.="erreur !!! Le fichier (image de base) n'a été que partiellement téléchargé.";
										elseif ($_FILES["image-".$k]['error']==2) 
											$erreurimg.="erreur !!! ereur inconnue (image de base) ...";
										elseif ($_FILES["image-".$k]['error']==4) 
											$erreurimg.="erreur !!! Aucun fichier n'a été téléchargé (image de base).";
										elseif ($_FILES["image-".$k]['type']!="image/pjpeg" && $_FILES["image-".$k]['type']!="image/jpeg") 
											$erreurimg.="erreur !!! Le fichier téléchargé (image de base) n'est pas du type autorisé:<br>Vous ne pouvez télécharger qu'un fichier du type JPEG , JPG  (type envoyé : ".$_FILES["image-".$k]['type'].")";
										}
									if ($erreurimg == "" and $_FILES["image-".$k]['name']!="")
										{
										if ($_FILES["image-".$k]['type']=="image/pjpeg") 
											$type_fichier="jpg";
										if ($_FILES["image-".$k]['type']=="image/jpeg") 
											$type_fichier="jpg";
										/* copie de l'image sans la redimmensionner à la racine de images/produits */
										$nomcomplet = $artid."_".$k.".".$type_fichier;
										$nom_fic=$rootpath."configurations/$host/images/produits/article_caractval/$nomcomplet";
										copier_image($nom_fic,"image-".$k);
										}
									}	
								
								// on calcule le prix TTC car on rentre du HT
								$modifprixht = $_POST['mtmodif'][$key][$k];
								//foreach($sites as $site_code=>$site) {
								    if ($modifprixht > 0)
									    $modifprixttc = number_format($modifprixht * ( 1 + ($tauxfrance[0]/100)), 2, '.', '');
								    else
									    $modifprixttc = 0;
								//}
								$values_array[] = "('$artid', '".$k."', '".$modifprixht."', '".$modifprixttc."',".floatval($_POST['achatmodif'][$key][$k]).", '$type_fichier')";
								if($ral->is_ral_option($k)) $ral->update_ral_price($artid,$modifprixttc,($_POST['mtmodif'][$key][$k]),floatval($_POST['achatmodif'][$key][$k]));
								}

						}
				
					//on verifie si on a effacer
					foreach($old_array as $key => $value) {
						$count_old = sizeof($value);
						$count_new = isset($_POST['chk'][$key]) ? sizeof($_POST['chk'][$key]) : 0;
						
						if(($count_old > 1) && ($count_new < 2)) {
							$caractrem_array[] = $key;
						}
						else
							foreach($value as $k => $v)
								if(!(isset($_POST['chk'][$key][$k]) && $_POST['chk'][$key][$k])) {
									$caractvalrem_array[] = $k;
								}
					}
					
					//executer la requete
					if(isset($values_array) && sizeof($values_array))
						{
						$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprixht, differenceprix,achat, image) VALUES".implode(",", $values_array).";");
						}
					}				
				$erreur .= "Article modifié";
				break ;
			
			case "garantie":
				// tu traites ici les données envoyées par le formulaire de configuration des garanties
				$idemGarantie=array();
				$verifSFGprix=array();
				$prix=$ppi=array();
				$prixPdt=$DB_site2->query_first("SELECT prix FROM article_site WHERE site_code='base' AND artid=".$artid.";");
				for ($i=0; $i < count($_POST['checkFournisseur']); $i++) {
					$garantieid=$_POST['checkFournisseur'][$i];
					foreach($sites as $site_code=>$site){
					    $prix[$garantieid][$site_code]=round($_POST['prix'][$garantieid][$site_code],2);
					    $ppi[$garantieid][$site_code]=round($_POST['ppi'][$garantieid][$site_code],2);
					}
					$garantie=new garantie($DB_site2,null,'FR',$garantieid);
					if ($garantie->fournisseur=="SFG" && $prixPdt[0]>4999.99) {
						$verifSFGprix[]=$garantie->garantieid;
					}
					for ($j=$i+1; $j < count($_POST['checkFournisseur']); $j++) {
						$garantie2=new garantie($DB_site2,null,'FR',$_POST['checkFournisseur'][$j]);
						$result = array_intersect($garantie->pays,$garantie2->pays);
						if ($garantie->duree==$garantie2->duree && $garantie->type_garantie==$garantie2->type_garantie && count($result)>0 && $garantie->cumul==2 && $garantie2->cumul==2 && $garantie->cumul==2 && $garantie2->cumul==2 && $garantie->destinataire_garantie == 1 && $garantie2->destinataire_garantie == 1) {
							$idemGarantie[]=$garantie->garantieid;
							$idemGarantie[]=$garantie2->garantieid;
						}
					}
				}
				
				if (count($idemGarantie)==0) {
					$DB_site2->query("DELETE FROM article_garantie_liste WHERE artid=".$artid.";");
					for ($i=0; $i < count($_POST['checkFournisseur']); $i++) {
						$garantieid=$_POST['checkFournisseur'][$i];
						$DB_site2->query("INSERT INTO article_garantie_liste SET artid=".$artid.", garantieid=".$garantieid.", paht=".number_format($_POST['paht'][$garantieid],2).", devise='".$_POST['devise'][$garantieid]."', prix='".serialize($prix[$garantieid])."', ppi='".serialize($ppi[$garantieid])."';");
					}
					//$erreur = "Article modifié" ;
				} else {
					for ($i=0; $i < count($_POST['checkFournisseur']); $i++) {
						$garantieid=$_POST['checkFournisseur'][$i];
						if (!in_array($garantieid, $idemGarantie) && !in_array($_POST['checkFournisseur'][$i], $verifSFGprix)) {
							$DB_site2->query("DELETE FROM article_garantie_liste WHERE artid=".$artid." AND garantieid=".$garantieid.";");
							$DB_site2->query("INSERT INTO article_garantie_liste SET artid=".$artid.", garantieid=".$garantieid.", paht=".number_format($_POST['paht'][$garantieid],2).", devise='".$_POST['devise'][$garantieid]."', prix='".serialize($prix[$garantieid])."', ppi='".serialize($ppi[$garantieid])."';");
						}
					}
					for ($i=0; $i < count($idemGarantie); $i++) {
						if ($i % 2 == 1) {
							$erreur .= "Les garanties ".$idemGarantie[$i-1]." et ".$idemGarantie[$i]." sont identiques (durée,type,pays)<br/>";
						}
					}
					
					//$action2 = "garantie";
				}
				if (count($verifSFGprix)>0) 
						$erreur .= "Il faut configurer les références de garantie car prix de vente > 4999¤<br/>";
					
				break;
			
			case "uploads":
				$DB_site2->query("update article set upload_images = '$nb_img', upload_images_oblig = '$oblig_img', 
						upload_comm = '$nb_comm', upload_comm_oblig = '$oblig_comm'	where artid = '$artid'") ;
				$erreur .= "Article modifié";
				break ;
			case "liens":
				if (isset($chk_categories))
					{
					// mise à jour du treeview
					$DB_site2->query("update treeview_temporaire set dateline = dateline - 10000");

					foreach ($chk_categories as $cle=>$valeur)
						{
						$liste_categs .= "$valeur," ;
						$categ=$DB_site2->query_first("SELECT a.artid FROM liens_articles la, article a WHERE a.catid = '$valeur'
						AND ((a.artid = la.article_base AND article_lie = '$artid') OR (a.artid = la.article_lie AND article_base = '$artid'))");
						if ($categ[0] == "")
							{
							/*article*/				
							$art=$DB_site2->query_first("select * from article where artid = '$artid'"); 
							$art[legende] = addslashes($art[legende]) ;
							$art[libelleFR] = addslashes($art[libelleFR]) ;
							$art[descriptionFR] = addslashes($art[descriptionFR]) ;
							$art[libelleDE] = addslashes($art[libelleDE]) ;
							$art[descriptionDE] = addslashes($art[descriptionDE]) ;
							$art[libelleEN] = addslashes($art[libelleEN]) ;
							$art[descriptionEN] = addslashes($art[descriptionEN]) ;
							$art[libelleIT] = addslashes($art[libelleIT]) ;
							$art[descriptionIT] = addslashes($art[descriptionIT]) ;
							$art[libelleSP] = addslashes($art[libelleSP]) ;
							$art[Titre2FR] = addslashes($art[Titre2FR]) ;
							$art[Titre2DE] = addslashes($art[Titre2DE]) ;
							$art[Titre2EN] = addslashes($art[Titre2EN]) ;
							$art[Titre2IT] = addslashes($art[Titre2IT]) ;
							$art[Titre2SP] = addslashes($art[Titre2SP]) ;
							$DB_site2->query("insert into article (artcode,pro, catid, active, prix, tva, tauxchoisi, poid, fraisgestionid, 
							image, legende, libelleFR, descriptionFR, libelleDE, descriptionDE, libelleEN, descriptionEN, libelleIT, 
							descriptionIT, libelleSP, descriptionSP, Titre2FR, Titre2DE, Titre2EN, Titre2IT, Titre2SP, upload_images, 
							upload_images_oblig, upload_comm, upload_comm_oblig, commandable) 
							values ('$art[artcode]','3', '$valeur', '$art[active]', '$art[prix]', '$art[tva]', '$art[tauxchoisi]', '$art[poid]',
							'$art[fraisgestionid]', '$art[image]', '$art[legende]', '$art[libelleFR]', '$art[descriptionFR]', 
							'$art[libelleDE]', '$art[descriptionDE]', '$art[libelleEN]', '$art[descriptionEN]', '$art[libelleIT]', 
							'$art[descriptionIT]', '$art[libelleSP]', '$art[descriptionSP]', '$art[Titre2FR]', '$art[Titre2DE]', 
							'$art[Titre2EN]', '$art[Titre2IT]', '$art[Titre2SP]', '$art[upload_images]', '$art[upload_images_oblig]', 
							'$art[upload_comm]', '$art[upload_comm_oblig]', '$art[commandable]')");
							$insertid=$DB_site2->insert_id() ;
							if ($art[image] != "")
								{
								$nom_fic=$rootpath."configurations/$host/images/produits/$artid.$art[image]";
								$new_nom_fic=$rootpath."configurations/$host/images/produits/$insertid.$art[image]";
								if (file_exists($nom_fic)) 
									{
									copy($nom_fic, $new_nom_fic) ;
									}
								$destination0=$rootpath."configurations/$host/images/produits/article/$insertid.$art[image]";
								redimentionner_image($new_nom_fic,$destination0,$article_largeur,$article_hauteur);
								$destination1=$rootpath."configurations/$host/images/produits/categorie/$insertid.$art[image]";
								redimentionner_image($new_nom_fic,$destination1,$categorie_largeur,$categorie_hauteur);
								$destination2=$rootpath."configurations/$host/images/produits/cdc_categ/$insertid.$art[image]";
								redimentionner_image($new_nom_fic,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
								$destination3=$rootpath."configurations/$host/images/produits/cdc_index/$insertid.$art[image]";
								redimentionner_image($new_nom_fic,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
								$destination4=$rootpath."configurations/$host/images/produits/nouveautes_categ/$insertid.$art[image]";
								redimentionner_image($new_nom_fic,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
								$destination5=$rootpath."configurations/$host/images/produits/nouveautes_index/$insertid.$art[image]";
								redimentionner_image($new_nom_fic,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
								$destination6=$rootpath."configurations/$host/images/produits/promos_categ/$insertid.$art[image]";
								redimentionner_image($new_nom_fic,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
								$destination7=$rootpath."configurations/$host/images/produits/promos_index/$insertid.$art[image]";
								redimentionner_image($new_nom_fic,$destination7,$promos_index_largeur,$promos_index_hauteur);
								$destination8=$rootpath."configurations/$host/images/produits/topventes_categ/$insertid.$art[image]";
								redimentionner_image($new_nom_fic,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
								$destination9=$rootpath."configurations/$host/images/produits/topventes_index/$insertid.$art[image]";
								redimentionner_image($new_nom_fic,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
								$destination10=$rootpath."configurations/$host/images/produits/br/$insertid.$art[image]";
								redimentionner_image($new_nom_fic,$destination10,$largeurimages,$hauteurimages);
								if (isset($hauteurphotos) && isset($largeurphotos)) {
									$destination11=$rootpath."configurations/$host/images/produits/photos/$insertid.$art[image]";
									redimentionner_image($new_nom_fic,$destination11,$largeurphotos,$hauteurphotos);
									}
								}
/* articlephoto */			$artphotos=$DB_site2->query("select * from articlephoto where artid = '$artid'");
							if ($DB_site2->num_rows($artphotos) > 0)
								{
								while ($artphoto=$DB_site2->fetch_array($artphotos))
									{
									$artphoto[legende] = addslashes ($artphoto[legende]) ;
									$DB_site2->query("insert into articlephoto (artid, image, legende) values ('$insertid', '$artphoto[image]', '$artphoto[legende]')");
									$insertid2=$DB_site2->insert_id() ;
									$nom_fic2=$rootpath."configurations/$host/images/produits/".$artid."_".$artphoto[articlephotoid].".".$artphoto[image];
									$new_nom_fic2=$rootpath."configurations/$host/images/produits/".$insertid."_".$insertid2.".".$artphoto[image];
									if (file_exists($nom_fic2)) 
										{
										copy($nom_fic2, $new_nom_fic2) ;
										}
									$destination0=$rootpath."configurations/$host/images/produits/article/".$insertid."_".$insertid2.".".$artphoto[image];
									redimentionner_image($new_nom_fic2,$destination0,$article_largeur,$article_hauteur);
									$destination1=$rootpath."configurations/$host/images/produits/categorie/".$insertid."_".$insertid2.".".$artphoto[image];
									redimentionner_image($new_nom_fic2,$destination1,$categorie_largeur,$categorie_hauteur);
									$destination2=$rootpath."configurations/$host/images/produits/cdc_categ/".$insertid."_".$insertid2.".".$artphoto[image];
									redimentionner_image($new_nom_fic2,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
									$destination3=$rootpath."configurations/$host/images/produits/cdc_index/".$insertid."_".$insertid2.".".$artphoto[image];
									redimentionner_image($new_nom_fic2,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
									$destination4=$rootpath."configurations/$host/images/produits/nouveautes_categ/".$insertid."_".$insertid2.".".$artphoto[image];
									redimentionner_image($new_nom_fic2,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
									$destination5=$rootpath."configurations/$host/images/produits/nouveautes_index/".$insertid."_".$insertid2.".".$artphoto[image];
									redimentionner_image($new_nom_fic2,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
									$destination6=$rootpath."configurations/$host/images/produits/promos_categ/".$insertid."_".$insertid2.".".$artphoto[image];
									redimentionner_image($new_nom_fic2,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
									$destination7=$rootpath."configurations/$host/images/produits/promos_index/".$insertid."_".$insertid2.".".$artphoto[image];
									redimentionner_image($new_nom_fic2,$destination7,$promos_index_largeur,$promos_index_hauteur);
									$destination8=$rootpath."configurations/$host/images/produits/topventes_categ/".$insertid."_".$insertid2.".".$artphoto[image];
									redimentionner_image($new_nom_fic2,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
									$destination9=$rootpath."configurations/$host/images/produits/topventes_index/".$insertid."_".$insertid2.".".$artphoto[image];
									redimentionner_image($new_nom_fic2,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
									$destination10=$rootpath."configurations/$host/images/produits/br/".$insertid."_".$insertid2.".".$artphoto[image];
									redimentionner_image($new_nom_fic2,$destination10,$largeurimages,$hauteurimages);
									if (isset($hauteurphotos) && isset($largeurphotos)) {
										$destination11=$rootpath."configurations/$host/images/produits/photos/".$insertid."_".$insertid2.".".$artphoto[image];
										redimentionner_image($new_nom_fic2,$destination11,$largeurphotos,$hauteurphotos);
										}
									}
								}
/* stock */					$articlestock=$DB_site2->query_first("select * from stock where artid = '$artid'") ;
							$DB_site2->query("insert into stock (artid, nombre, seuil, delaiappro) values ('$insertid', '$articlestock[nombre]', '$articlestock[seuil]', '$articlestock[delaiappro]')");
/* article_cadeau */		$artcadeaux=$DB_site2->query("select * from article_cadeau where artid = '$artid'");
							if ($DB_site2->num_rows($artcadeaux) > 0)
								{
								while ($artcadeau=$DB_site2->fetch_array($artcadeaux))
									{
									$DB_site2->query("insert into article_cadeau (cadeauid, artid) values ('$artcadeau[cadeauid]', '$insertid')");
									}
								}
/* article_caractval */		$artcaracts=$DB_site2->query("select * from article_caractval where artid = '$artid'");
							if ($DB_site2->num_rows($artcaracts) > 0)
								{
								while ($artcaract=$DB_site2->fetch_array($artcaracts))
									{
									$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$insertid', '$artcaract[caractvalid]', '$artcaract[differenceprix]')");
									}
								}
/* article_marque */		$artmarque=$DB_site2->query_first("select * from article_marque where artid = '$artid'"); 
							$DB_site2->query("insert into article_marque (artid, marqueid) values ('$insertid', '$artmarque[marqueid]')");
/* promotion */				$artcpromos=$DB_site2->query("select * from promotion where artid = '$artid'");
							if ($DB_site2->num_rows($artcpromos) > 0)
								{
								while ($artcpromo=$DB_site2->fetch_array($artcpromos))
									{
									$DB_site2->query("insert into promotion (artid, prix, datedebut, datefin) values ('$insertid', '$artcpromo[prix]', '$artcpromo[datedebut]', '$artcpromo[datefin]')");
									}
								}
/* remisearticle */			$artremises=$DB_site2->query("select * from remisearticle where artid = '$artid'");
							if ($DB_site2->num_rows($artremises) > 0)
								{
								while ($artremise=$DB_site2->fetch_array($artremises))
									{
									$DB_site2->query("insert into remisearticle (remiseid, artid) values ('$artremise[remiseid]', '$insertid')");
									}
								}
/* liens_articles */		$DB_site2->query("insert into liens_articles (article_base, article_lie) values ('$artid', '$insertid')");
							}
						}
					$liste_categs = substr($liste_categs, 0, -1) ;
					$arts_a_suppr = "" ;
					// supprimer les articles décochés
					$categs=$DB_site2->query("SELECT a.artid FROM liens_articles la, article a WHERE a.catid NOT IN ($liste_categs)
					AND ((a.artid = la.article_base AND article_lie = '$artid') OR (a.artid = la.article_lie AND article_base = '$artid'))");
					if ($DB_site2->num_rows($categs) > 0)
						{
						$arts_a_suppr = "0" ;
						while ($categ=$DB_site2->fetch_array($categs))
							{
							$arts_a_suppr .= ", $categ[artid] " ;
							}	
					if ($arts_a_suppr != "")
						{
	/* stock */			if (in_array("4",$modules))
							{
							$articlestock=$DB_site2->query_first("select * from stock where artid = '$artid'") ;
							if ($articlestock[nombre] == "") 
								{ // si la ligne n'existe pas on regarde si il y a des articles liés et on copie le stock
								$articlestock=$DB_site2->query_first("select * from stock where artid in ($arts_a_suppr)") ;
								if ($articlestock[nombre] != "")
									{
									$DB_site2->query("insert into stock (artid, nombre, seuil, delaiappro) values ('$artid', '$articlestock[nombre]', '$articlestock[seuil]', '$articlestock[delaiappro]')");
									$DB_site2->query("delete from stock where artid in ($arts_a_suppr)") ;
									}
								}
							}
	/* articlevote */	if (in_array("18", $modules))
							{
							$article_comms=$DB_site2->query("select * from articlevote where artid = '$artid'") ;
							if ($DB_site2->num_rows($article_comms) == 0)
								{
								$article_comms=$DB_site2->query("select * from articlevote where artid in ($arts_a_suppr)") ;
								if ($DB_site2->num_rows($article_comms) > 0)
									{
									while ($article_comms=$DB_site2->fetch_array($article_comms))
										{
										$DB_site2->query("insert into articlevote (artid, titre, note, contenu, datevote, actif) values ('$artid', '$article_comms[titre]', '$article_comms[note]', '$article_comms[contenu]', '$article_comms[datevote]', '$article_comms[actif]')");
										}
									$DB_site2->query("delete from articlevote where artid in ($arts_a_suppr)") ;
									}
								}
							}
	/* liens_articles */	$DB_site2->query("delete from liens_articles where article_base IN ($arts_a_suppr) OR article_lie IN ($arts_a_suppr)");
	/* remisearticle */		$DB_site2->query("delete from remisearticle where artid IN ($arts_a_suppr)");
	/* promotion */			$DB_site2->query("delete from promotion where artid IN ($arts_a_suppr)");
	/* article_marque */	$DB_site2->query("delete from article_marque where artid IN ($arts_a_suppr)");
	/* article_caractval */	$DB_site2->query("delete from article_caractval where artid IN ($arts_a_suppr)");
	/* article_cadeau */	$DB_site2->query("delete from article_cadeau where artid IN ($arts_a_suppr)");
	/* articlephoto */		$photos=$DB_site2->query("select * from articlephoto where artid IN ($arts_a_suppr)");
							if ($DB_site2->num_rows($photos))
								{
								while ($photo=$DB_site2->fetch_array($photos))
									{
									$file=$rootpath."configurations/$host/images/produits/".$photo[artid]."_".$photo[articlephotoid].".".$photo[image];
									if (file_exists($file)) 
										{
										unlink($file) ;
										}
									}
								$DB_site2->query("delete from articlephoto where artid IN ($arts_a_suppr)");
								}
	/* article */			$phos=$DB_site2->query("select artid, image from article where artid IN ($arts_a_suppr)") ;
							if ($DB_site2->num_rows($phos))
								{
								while ($pho=$DB_site2->fetch_array($phos))
									{
									$file=$rootpath."configurations/$host/images/produits/".$pho[artid].".".$pho[image];
									if (file_exists($file)) 
										{
										unlink($file) ;
										}
									}
								$DB_site2->query("delete from article where artid IN ($arts_a_suppr)");
								}
							}
						}
					}
				else
					{
					// supprimer les articles décochés
					$categs=$DB_site2->query("SELECT a.artid FROM liens_articles la, article a 
					WHERE ((a.artid = la.article_base AND article_lie = '$artid') OR (a.artid = la.article_lie AND article_base = '$artid'))");
					if ($DB_site2->num_rows($categs) > 0)
						{
						$arts_a_suppr = "0" ;
						while ($categ=$DB_site2->fetch_array($categs))
							{
							$arts_a_suppr .= ", $categ[artid] " ;
							}	
					if ($arts_a_suppr != "")
						{
	/* stock */			if (in_array("4",$modules))
							{
							$articlestock=$DB_site2->query_first("select * from stock where artid = '$artid'") ;
							if ($articlestock[nombre] == "") 
								{ // si la ligne n'existe pas on regarde si il y a des articles liés et on copie le stock
								$articlestock=$DB_site2->query_first("select * from stock where artid in ($arts_a_suppr)") ;
								if ($articlestock[nombre] != "")
									{
									$DB_site2->query("insert into stock (artid, nombre, seuil, delaiappro) values ('$artid', '$articlestock[nombre]', '$articlestock[seuil]', '$articlestock[delaiappro]')");
									$DB_site2->query("delete from stock where artid in ($arts_a_suppr)") ;
									}
								}
							}
	/* articlevote */	if (in_array("18", $modules))
							{
							$article_comms=$DB_site2->query("select * from articlevote where artid = '$artid'") ;
							if ($DB_site2->num_rows($article_comms) == 0)
								{
								$article_comms=$DB_site2->query("select * from articlevote where artid in ($arts_a_suppr)") ;
								if ($DB_site2->num_rows($article_comms) > 0)
									{
									while ($article_comms=$DB_site2->fetch_array($article_comms))
										{
										$DB_site2->query("insert into articlevote (artid, titre, note, contenu, datevote, actif) values ('$artid', '$article_comms[titre]', '$article_comms[note]', '$article_comms[contenu]', '$article_comms[datevote]', '$article_comms[actif]')");
										}
									$DB_site2->query("delete from articlevote where artid in ($arts_a_suppr)") ;
									}
								}
							}
	/* liens_articles */	$DB_site2->query("delete from liens_articles where article_base IN ($arts_a_suppr) OR article_lie IN ($arts_a_suppr)");
	/* remisearticle */		$DB_site2->query("delete from remisearticle where artid IN ($arts_a_suppr)");
	/* promotion */			$DB_site2->query("delete from promotion where artid IN ($arts_a_suppr)");
	/* article_marque */	$DB_site2->query("delete from article_marque where artid IN ($arts_a_suppr)");
	/* article_caractval */	$DB_site2->query("delete from article_caractval where artid IN ($arts_a_suppr)");
	/* article_cadeau */	$DB_site2->query("delete from article_cadeau where artid IN ($arts_a_suppr)");
	/* articlephoto */		$photos=$DB_site2->query("select * from articlephoto where artid IN ($arts_a_suppr)");
							if ($DB_site2->num_rows($photos))
								{
								while ($photo=$DB_site2->fetch_array($photos))
									{
									$file=$rootpath."configurations/$host/images/produits/".$photo[artid]."_".$photo[articlephotoid].".".$photo[image];
									if (file_exists($file)) 
										{
										unlink($file) ;
										}
									}
								$DB_site2->query("delete from articlephoto where artid IN ($arts_a_suppr)");
								}
	/* article */			$phos=$DB_site2->query("select artid, image from article where artid IN ($arts_a_suppr)") ;
							if ($DB_site2->num_rows($phos))
								{
								while ($pho=$DB_site2->fetch_array($phos))
									{
									$file=$rootpath."configurations/$host/images/produits/".$pho[artid].".".$pho[image];
									if (file_exists($file)) 
										{
										unlink($file) ;
										}
									}
								$DB_site2->query("delete from article where artid IN ($arts_a_suppr)");
								}
							}
						}
					}
				break ;
			case "stocks":
				$erreur = "" ;
				// vérifications de saisie
				if ($sa != "")
					{
					if (!is_numeric($sa))
						{
						$erreur .= "Le seuil d'alerte doit être numérique<bR>" ;
						}
					}
				if ($delai != "")
					{
					if (!is_numeric($delai))
						{
						$erreur .= "Le délai d'approvisionnement doit être numérique<bR>" ;
						}
					}
				if(is_array($stockid))
					foreach($stockid as $key => $value)
						if($value != "" && $value != 0)
							if(!is_numeric($value)) {
								$erreur = "La saisie des stocks doit être numérique<br>";
								break;
							}
				if(is_array($stocknew))
					foreach($stocknew as $key => $value) {
						//echo "$key => $value<br>" ;
						if($value != "" && $value != 0)
							if(!is_numeric($value)) {
								$erreur = "La saisie des stocks doit être numérique<br>";
								break;
								}
						}
				if ($erreur == "") 
					{
					if (is_array($stockid))
						{
						foreach ($stockid as $key => $value) 
							{
							if ($value != "" && $value != 0) 
								{
								$DB_site2->query("UPDATE stocks SET total = total + '$value' WHERE stockid = '$key';","logstock",$artid);
								$DB_site2->query("INSERT INTO stock_historique(stockid, dateline, delta) VALUES('$key', UNIX_TIMESTAMP(), '$value');","logstock",$artid);
								}
							}
						}			
					
					//mises à jour
					$lignes=$DB_site2->query_first("select count(*) from stocks WHERE artid = '$artid' and parentid != '0';");
					if ($lignes[0] > 0)
						{	
						$mytot=$DB_site2->query_first("select sum(total) from stocks where artid = '$artid' and parentid != '0'","logstock",$artid);
						$DB_site2->query("update stocks set total = '$mytot[0]' where artid = '$artid' and parentid = '0'","logstock",$artid);
						$DB_site2->query("update stock set nombre = '$mytot[0]', seuil = '$sa', delaiappro = '$delai' where artid = '$artid'","logstock",$artid);
						}
					else
						{
						$DB_site2->query("update stock set nombre = (select total from stocks where artid = '$artid' and parentid = '0'), seuil = '$sa', delaiappro = '$delai' where artid = '$artid'","logstock",$artid);
						}
					// mise à jour du treeview
					$DB_site2->query("update treeview_temporaire set dateline = dateline - 10000");
				
				}
				else {
					$erreur .= "(Vous pouvez saisir 0)" ;
				}
				//$action2 = $old ;
				break ;
			case "saisie":
				$erreur="";
				if (isset($chp2) and $chp2 != "")
					{
					$erreur .= "Article modifié";
					$action2 = $old ;
					}
				else
					{
					if (is_array($liste2) and count($liste2) > 0)
						{
						$champs_a_remplir = "0" ;
						foreach ($liste2 as $key => $value)
							{
							$infos_champ=$DB_site2->query_first("select * from article_champ where articlechampid = '$value'") ;
							if ($infos_champ[artid] != $artid)
								{
								$key2 = $key + 1 ;
								$DB_site2->query("insert into article_champ (artid, libelleFR, nom, type, obligatoire, position) values ('$artid', 
								'$infos_champ[libelle]', '$infos_champ[nom]', '$infos_champ[type]', '$infos_champ[obligatoire]', '$key2')") ;
								$insertid=$DB_site2->insert_id() ;
								$champs_a_remplir .= ", $insertid" ;
								$valeurs_champ=$DB_site2->query("select * from article_champ_valeur where articlechampid = '$value'") ;
								$DB_site2->query("delete from article_champ_valeur where articlechampid = '$value'") ;
								while ($valeur_champ=$DB_site2->fetch_array($valeurs_champ))
									{
									$DB_site2->query("insert into article_champ_valeur (articlechampid, libelle, description, valeur) values 
									('$insertid', '$valeur_champ[libelle]', '$valeur_champ[description]', '$valeur_champ[valeur]')") ;
									}
								}
							}
						//$DB_site2->query("delete from article_champ where artid = '$artid' and articlechampid NOT IN ($champs_a_remplir)") ;
						}
					else
						{
						$DB_site2->query("delete from article_champ where artid = '$artid'") ;
						}
					$erreur .= "Article modifié";
					}
				break ;			
			case "pieces":
				if (isset ($del) and $del == 1)
					{
					$action2 = $old ;
					}
				if ($type_piece != 0 and $description_piece != "")
					{
					$erreur = "" ;
					if ($_FILES['emplacement_piece']['name'] != "")
						{
						if ($_FILES['emplacement_piece']['error'] == 1) 
							{
							$erreur .= "Erreur !!! Le fichier téléchargé excède la taille autorisée par le serveur (> 2Mb)";
							}
						elseif ($_FILES['emplacement_piece']['error'] == 3) 
							{
							$erreur .= "Erreur !!! Le fichier n'a été que partiellement téléchargé.";
							}
						elseif ($_FILES['emplacement_piece']['error'] == 2) 
							{
							$erreur .= "Erreur !!! erreur inconnue ...";
							}
						elseif ($_FILES['emplacement_piece']['error'] == 4) 
							{
							$erreur .= "Erreur !!! Aucun fichier n'a été téléchargé.";
							}
						elseif ($_FILES['emplacement_piece']['type'] == "application/x-msdos-program" or $_FILES['emplacement_piece']['type'] == "application/octet-stream" or $_FILES['emplacement_piece']['type'] == "application/x-msdownload") 
							{
							$erreur .= "Le type du fichier téléchargé est d'un format non autorisé.";
							}
						} 
					else
						{
						$erreur .= "Le fichier à télécharger est introuvable.";
						}
					if ($erreur == "")
						{
						switch ($_FILES['emplacement_piece']['type'])  // définition de l'extension
							{
							case "audio/scpls":
								$file = "pls" ;
								break;
							case "audio/x-mpegurl":
								$file = "m3u" ;
								break;
							case "application/x-shockwave-flash":
								$file = "swf" ;
								break;
							case "application/postscript":
								$file = "eps" ;
								break;
							case "application/msword":
								$file = "doc" ;
								break;
							case "application/msword":
								$file = "rtf" ;
								break;
							case "application/excel":
								$file = "xls" ;
								break;
							case "application/vnd.ms-powerpoint":
								$file = "pps" ;
								break;
							case "application/pdf":
								$file = "pdf" ;
								break;
							case "application/x-gzip":
								$file = "gzip" ;
								break;
							case "application/acad":
								$file = "dwg" ;
								break;
							case "application/clariscad":
								$file = "ccad" ;
								break;
							case "application/drafting":
								$file = "drw" ;
							   break;
							case "application/dxf":
								$file = "dxf" ;
							   break;
							case "application/i-deas":
								$file = "unv" ;
							   break;
							case "application/iges":
								$file = "igs" ;
							   break;
							case "application/oda":
								$file = "oda" ;
							   break;
							case "application/pdf":
								$file = "dwg" ;
							   break;
							case "application/postscript":
								$file = "ai" ;
							   break;
							case "application/pro":
								$file = "prt" ;
							   break;
							case "application/rtf":
								$file = "rtf" ;
							   break;
							case "application/set":
								$file = "set" ;
							   break;
							case "application/sla":
								$file = "stl" ;
							   break;
							case "application/solids":
								$file = "dwg" ;
							   break;
							case "application/step":
								$file = "step" ;
							   break;
							case "application/vda":
								$file = "vda" ;
							   break;
							case "application/x-mif":
								$file = "mif" ;
							   break;
							case "application/x-csh":
								$file = "dwg" ;
							   break;
							case "application/x-dvi":
								$file = "dvi" ;
							   break;
							case "application/hdf":
								$file = "hdf" ;
							   break;
							case "application/x-latex":
								$file = "latex" ;
							   break;
							case "application/x-netcdf":
								$file = "nc" ;
							   break;
							case "application/x-sh":
								$file = "dwg" ;
							   break;
							case "application/x-tcl":
								$file = "tcl" ;
							   break;
							case "application/x-tex":
								$file = "tex" ;
							   break;
							case "application/x-texinfo":
								$file = "texi" ;
							   break;
							case "application/x-troff":
								$file = "troff" ;
							   break;
							case "application/x-troff-man":
								$file = "man" ;
							   break;
							case "application/x-troff-me":
								$file = "me" ;
							   break;
							case "application/x-troff-ms":
								$file = "ms" ;
							   break;
							case "application/x-wais-source":
								$file = "src" ;
							   break;
							case "application/x-bcpio":
								$file = "bcpio" ;
							   break;
							case "application/x-cpio":
								$file = "cpio" ;
							   break;
							case "application/x-gtar":
								$file = "gtar" ;
							   break;
							case "application/x-shar":
								$file = "shar" ;
							   break;
							case "application/x-sv4cpio":
								$file = "sv4cpio" ;
							   break;
							case "application/x-sv4crc":
								$file = "sc4crc" ;
							   break;
							case "application/x-tar":
								$file = "tar" ;
							   break;
							case "application/x-ustar":
								$file = "tar" ;
							   break;
							case "application/zip":
								$file = "ZIP" ;
							   break;
							case "application/x-bzip":
								$file = "bz2" ;
							   break;
							case "audio/vnd.rn-realaudio":
								$file = "ra" ;
							   break;
							case "audio/x-pn-realaudio":
								$file = "ram" ;
							   break;
							case "application/vnd.rn-realmedia":
								$file = "rm" ;
							   break;
							case "video/vnd.rn-realvideo":
								$file = "rv" ;
							   break;
							case "audio/wav":
								$file = "wav" ;
							   break;
							case "audio/ogg":
								$file = "ogg" ;
							   break;
							case "audio/mpeg":
								$file = "mp3" ;
							   break;
							case "audio/aiff":
								$file = "aif" ;
								break;
							case "audio/mid":
								$file = "midi" ;
								break;
							case "audio/basic":
								$file = "snd" ;
							   break;
							case "audio/x-aiff":
								$file = "aif" ;
							   break;
							case "audio/x-wav":
								$file = "wav" ;
							   break;
							case "image/psd":
								$file = "psd" ;
							   break;
							case "image/png":
								$file = "png" ;
							   break;
							case "image/bmp":
								$file = "bmp" ;
							   break;
							case "image/gif":
								$file = "gif" ;
							   break;
							case "image/ief":
								$file = "ief" ;
							   break;
							case "image/jpeg":
								$file = "jpg" ;
							   break;
							case "image/pjpeg":
								$file = "jpg" ;
							   break;
							case "image/tiff":
								$file = "tiff" ;
							   break;
							case "image/x-cmu-raster":
								$file = "cmu" ;
							   break;
							case "image/x-portable-anymap":
								$file = "pnm" ;
							   break;
							case "image/x-portable-bitmap":
								$file = "pbm" ;
							   break;
							case "image/x-portable-graymap":
								$file = "pgm" ;
							   break;
							case "image/x-portable-pixmap":
								$file = "ppm" ;
							   break;
							case "image/x-rgb":
								$file = "rgb" ;
							   break;
							case "image/x-xbitmap":
								$file = "xbm" ;
							   break;
							case "image/x-xpixmap":
								$file = "xpm" ;
							   break;
							case "image/x-xwindowdump":
								$file = "dump" ;
							   break;
							case "multipart/x-zip":
								$file = "zip" ;
							   break;
							case "multipart/x-gzip":
								$file = "gzip" ;
							   break;
							case "text/xml":
								$file = "xml" ;
							   break;
							case "text/xsl":
								$file = "xsl" ;
							   break;
							case "text/html":
								$file = "html" ;
							   break;
							case "text/plain":
								$file = "txt" ;
							   break;
							case "text/richtext":
								$file = "rtx" ;
							   break;
							case "text/tab-separated-value":
								$file = "tsv" ;
							   break;
							case "text/x-setext":
								$file = "etx" ;
							   break;
							case "text/css":
								$file = "css" ;
							   break;
							case "video/x-ms-wmv":
								$file = "wmv" ;
							   break;
							case "video/x-ms-wmx":
								$file = "wmx" ;
							   break;
							case "video/avi":
								$file = "avi" ;
							   break;
							case "video/x-ms-asf":
								$file = "asx" ;
							   break;
							case "video/mpeg":
								$file = "mpeg" ;
							   break;
							case "video/quicktime":
								$file = "mov" ;
							   break;
							case "video/msvideo":
								$file = "avi" ;
							   break;
							case "video/x-sgi-movie":
								$file = "movie" ;
							   break;
							}
						$DB_site2->query("insert into article_piece (artid, typepiece, contenu, description) values ('$artid', '$type_piece', '$file', '$description_piece')") ;
						$insertid=$DB_site2->insert_id() ;
						$nom_fic=$rootpath."configurations/$host/images/player/pieces_jointes/".$artid."_".$insertid.".".$file ;
						copier_image($nom_fic,'emplacement_piece') ;
						$erreur = "Article modifié";
						$action2 = $old ;
						}
					else
						{
						$action2 = $old ;
						}
					}
				$erreur .= "<br>" ;
				break ;			
			case "conseil":
				$DB_site2->query("delete from article_conseil where artid = '$artid'");
				if (is_array($chk_articles))
					{
					foreach ($chk_articles as $cle=>$valeur)
						{
						$DB_site2->query("insert into article_conseil (artid, artid_conseille) values ('$artid', '$valeur')");
						}
					}
				$erreur = "Article modifié" ;
				break ;
			case "rangements":
				if(isset($_POST['add_artid'])) $DB_site2->query("INSERT INTO article_rangement SET artid=".$artid.", artid_rangement=".$_POST['add_artid']);
				if(isset($_POST['rm_artid'])) $DB_site2->query("DELETE FROM article_rangement WHERE artid=".$artid." AND artid_rangement=".$_POST['rm_artid']);
				$erreur = "Article modifié" ;

				break ;
			case "cross2":	
				if(isset($_POST['add_artid']) && $_POST['add_artid']!="") $DB_site2->query("INSERT INTO cross_selling2 SET artid=".$artid.", artid_cross=".$_POST['add_artid']);
				if(isset($_POST['rm_artid'])) $DB_site2->query("DELETE FROM cross_selling2 WHERE artid=".$artid." AND artid_cross=".$_POST['rm_artid']);
				$erreur = "Article modifié" ;
				break ;
			case "cross":
				if(isset($_POST['add_artid']) && $_POST['add_artid']!="") $DB_site2->query("INSERT INTO cross_selling SET artid=".$artid.", artid_cross=".$_POST['add_artid']);
				if(isset($_POST['rm_artid'])) $DB_site2->query("DELETE FROM cross_selling WHERE artid=".$artid." AND artid_cross=".$_POST['rm_artid']);
				$erreur = "Article modifié" ;
				break ;
			case "rangements_nb":
				if (is_array($nb_rangements))
					{
					foreach ($nb_rangements as $cle=>$valeur)
						{
						$DB_site2->query("UPDATE article_rangement SET nombre = '$valeur' WHERE artid = '$artid' AND artid_rangement = '$cle'");
						}
					}
				$erreur = "Article modifié" ;
				break ;				
			case "equipements":
				$DB_site2->query("UPDATE ".$table_type['bdd_table']." SET interrupteur = '$interrupteur', eclairage = '$eclairage', bouton_test = '$bouton_test', hygrometrie = '$hygrometrie', 
				hygrometre = '$hygrometre', pieds_reglables = '$pieds_reglables', serrure = '$serrure', serrure_info = '$serrure_info', poignee = '$poignee', modularite_interieure = '$modularite_interieure', 
				roulettes = '$roulettes', vide_poche = '$vide_poche', clayette_fil = '$clayette_fil', panier_transport = '$panier_transport', vinokit = '$vinokit', 
				filtre_charbon = '".$filtre_charbon."', tablette = '$tablette', clayette_hetre = '$clayette_hetre', tapis_basse = '$tapis_basse', alarme='$alarme', sensporte='".intval($_POST['sensporte'])."' where artid = '$artid'");
				$erreur = "Article modifié" ;
				modifhistory($_POST);
				break;
			case "emballage":
			$DB_site2->query("DELETE FROM articlerecherche_typepose WHERE artid='$artid'");
				if(isset($_POST['typepose'])){
                    foreach($_POST['typepose'] as $typeposeid)
                            $DB_site2->query("INSERT INTO articlerecherche_typepose SET artid='$artid', typeposeid=".intval($typeposeid));
                }
				$DB_site2->query("update article set dimensions = '$dimensions', hauteur='$hauteur_txt', largeur='$largeur_txt', profondeur='$profondeur_txt', 
				poid = '$poid', volume='$volume', poidsvolume='$volumepoids', hauteur_encastrement='$hauteur_encastrement',largeur_encastrement='$largeur_encastrement',profondeur_encastrement='$profondeur_encastrement',distance_plafond='$distance_plafond',epaisseur_mur_min='$epaisseur_mur_min',epaisseur_mur_max='$epaisseur_mur_max',infos_encastrement='$infos_encastrement' where artid = '$artid'");
				foreach(range(1,3) as $num_emb)
				  $DB_site2->query("UPDATE `article` SET emballage_hauteur$num_emb = '".${'emballage_hauteur'.$num_emb}."', emballage_largeur$num_emb = '".${'emballage_largeur'.$num_emb}."', emballage_profondeur$num_emb = '".${'emballage_profondeur'.$num_emb}."', emballage_poids$num_emb = '".${'emballage_poids'.$num_emb}."' WHERE artid = '$artid'");
				
				$DB_site2->query("UPDATE article SET paysfabrication='".mysqli_real_escape_string($DB_site2->link_id,$paysfabrication)."',paysid=$paysidfab,douanier='$douanier',codebarre='$codebarre' WHERE artid=".$artid);
			
				$erreur = "Article modifié" ;	
				modifhistory($_POST);
				break;

			case "recherche":
				$DB_site2->query("DELETE FROM articlerecherche_usage WHERE artid='$artid' ");
				$usages=$DB_site2->query("SELECT * FROM recherche_usage");
				while ($usage=$DB_site2->fetch_array($usages))
					if (${"usage".$usage[rechercheusageid]} =="on")
						$DB_site2->query("INSERT INTO articlerecherche_usage (artid, rechercheusageid) VALUES ('$artid','$usage[rechercheusageid]') ");
				
				$erreur = "Article modifié" ;			

			
				$DB_site2->query("DELETE FROM articlerecherche_typeporte WHERE artid='$artid' ");
				$typesporte=$DB_site2->query("SELECT * FROM recherche_typeporte");
				while ($typeporte=$DB_site2->fetch_array($typesporte))
					if (${"typeporte".$typeporte[recherchetypeporteid]} =="on")
						$DB_site2->query("INSERT INTO articlerecherche_typeporte (artid, recherchetypeporteid) VALUES ('$artid','$typeporte[recherchetypeporteid]') ");
				
				$erreur = "Article modifié" ;			
			
			
				$DB_site2->query("DELETE FROM articlerecherche_coloris WHERE artid='$artid' ");
				$coloriss=$DB_site2->query("SELECT * FROM recherche_coloris");
				while ($coloris=$DB_site2->fetch_array($coloriss))
					if (${"coloris".$coloris[recherchecolorisid]} =="on")
						$DB_site2->query("INSERT INTO articlerecherche_coloris (artid, recherchecolorisid) VALUES ('$artid','$coloris[recherchecolorisid]') ");
				
				$erreur = "Article modifié" ;			
		
			
				$DB_site2->query("DELETE FROM articlerecherche_moderangement WHERE artid='$artid' ");
				$rangements=$DB_site2->query("SELECT * FROM recherche_moderangement");
				while ($rangement=$DB_site2->fetch_array($rangements))
					if (${"rangement".$rangement[recherchemoderangementid]} =="on")
						$DB_site2->query("INSERT INTO articlerecherche_moderangement (artid, recherchemoderangementid) VALUES ('$artid','$rangement[recherchemoderangementid]') ");
				
				$erreur = "Article modifié" ;			
			
			
				$DB_site2->query("DELETE FROM articlerecherche_emplacement WHERE artid='$artid' ");
				$emplacements=$DB_site2->query("SELECT * FROM recherche_emplacement");
				while ($emplacement=$DB_site2->fetch_array($emplacements))
					if (${"emplacement".$emplacement[rechercheemplacementid]} =="on")
						$DB_site2->query("INSERT INTO articlerecherche_emplacement (artid, rechercheemplacementid) VALUES ('$artid','$emplacement[rechercheemplacementid]') ");
				
				$erreur = "Article modifié" ;
				
				$DB_site2->query("UPDATE article_cave SET esthetique=".intval(isset($_POST['esthetique']))." WHERE artid='".$artid."'");
				modifhistory($_POST);
				break;
			case "coloris":
								
				$DB_site2->query("UPDATE article_cave SET coloris = '".mysqli_real_escape_string($DB_site2->link_id,$coloris)."', coloris_porte = '".mysqli_real_escape_string($DB_site2->link_id,$coloris_porte)."', version_porte = '".mysqli_real_escape_string($DB_site2->link_id,$version_porte)."', matiere='".mysqli_real_escape_string($DB_site2->link_id,$_POST['matiere'])."', hauteur_porte='".mysqli_real_escape_string($DB_site2->link_id,$_POST['hauteur_porte'])."' WHERE artid=".$artid);
				modifhistory($_POST);
				break;
			case "garantie_incluse":
				$DB_site2->query("update article set garantie_incluse = '$garantie_incluse' where artid = '$artid'");
				modifhistory($_POST);
				$erreur = "Article modifié" ;			
				break;
			case "similaire":
				if($_POST['gartid']>0){
				    $ga=$DB_site2->query_first("SELECT * FROM article_similaire WHERE artid=".intval($_POST['gartid']));
				    if($ga['gid']!="")
					$DB_site2->query("INSERT INTO article_similaire SET gid=".$ga['gid'].", artid=".$artid);
				    else{
					$gid=$DB_site2->query_first("SELECT MAX(gid) FROM article_similaire");
					$newgid=$gid[0]+1;
					$DB_site2->query("INSERT INTO article_similaire (gid,artid) VALUES ('".$newgid."','".$artid."'), ('".$newgid."','".$_POST['gartid']."')");
				    }
				}
				if($_POST['removegid']>0) $DB_site2->query("DELETE FROM article_similaire WHERE artid=".$artid." AND gid=".intval($_POST['removegid']));
			break;
			case "vcat":
				if(isset($_POST['dcat']) && is_array($_POST['dcat'])){
				  $DB_site2->query("DELETE FROM vcategorie WHERE artid=$artid AND NOT catid IN (".implode(",",$_POST['dcat']).")");
				  foreach($_POST['dcat'] as $key=>$catid){
				      $exist=$DB_site2->query_first("SELECT * FROM vcategorie WHERE artid=$artid AND catid=$catid");
				      if($exist['artid']!=$artid) $DB_site2->query("INSERT INTO vcategorie SET artid=$artid, catid=$catid");
				  }
				}else
				  $DB_site2->query("DELETE FROM vcategorie WHERE artid=$artid");

				  
				  
			break;
			case "performances":
				$extensions_valides=array('jpg','pdf','jpeg');
				if(isset($_FILES['energy_label_file'])){
					$extension_upload = strtolower(pathinfo($_FILES['energy_label_file']['name'], PATHINFO_EXTENSION));
					if ( in_array($extension_upload,$extensions_valides) ){
						$dest = $rootpath."configurations/".$host."/images/energy_label/".$artid.".".$extension_upload;
						$old = $DB_site2->query("SELECT id FROM article_fiche_energie WHERE artid = $artid ");
						while($del = $DB_site2->fetch_array($old)){
							$DB_site2->query("DELETE FROM article_fiche_energie WHERE id= ".$del['id']);
							if(file_exists($rootpath."configurations/".$host."/images/energy_label/".$artid.".".$del['ext'])){
								unlink($rootpath."configurations/".$host."/images/energy_label/".$artid.".".$del['ext']);
							}
						}
						$upload = move_uploaded_file($_FILES['energy_label_file']['tmp_name'],$dest);
						if($upload){
							$DB_site2->query("INSERT INTO article_fiche_energie SET artid= $artid, ext ='".$extension_upload."'");
						}
					}

				}
				if(isset($_POST['del_fiche']) && $_POST['del_fiche'] == '1'){
					$ext = $DB_site2->query_first("SELECT ext FROM article_fiche_energie WHERE artid=$artid");
					$DB_site2->query("DELETE FROM article_fiche_energie WHERE artid=$artid");
					unlink($rootpath."configurations/".$host."/images/energy_label/".$artid.".".$ext['ext']);
				}
				$conso_kw=str_replace(",",".",$conso_kw);
				$DB_site2->query("UPDATE ".$table_type['bdd_table']." set puissance_electrique = '".mysqli_real_escape_string($DB_site2->link_id,$puissance_electrique)."', intensite_electrique = '".mysqli_real_escape_string($DB_site2->link_id,$intensite_electrique)."', conso_kw='".($conso_an/365)."', niveau_bruit = '".$niveau_bruit."', niveau_bruit_abcd = '".$niveau_bruit_abcd."',niveau_bruit_old = '".$niveau_bruit_old."',conso_kw_old='".($conso_an_old/365)."', tension_electrique='".mysqli_real_escape_string($DB_site2->link_id,$_POST['tension_electrique'])."',temperature_cave = '".mysqli_real_escape_string($DB_site2->link_id,$temperature_cave)."', temp_instal_min=".floatval($_POST['temp_instal_min']).", temp_instal_max=".floatval($_POST['temp_instal_max']).", energy_label='".mysqli_real_escape_string($DB_site2->link_id,$energy_label)."', energy_label_old='".mysqli_real_escape_string($DB_site2->link_id,$energy_label_old)."',classe_clima='".mysqli_real_escape_string($DB_site2->link_id,$classe_clima)."',dispo_pieces=".intval($_POST['dispo_pieces'])." where artid = '$artid'");
				$erreur = "Article modifié" ;		
				modifhistory($_POST);
				break;
			case "technologies":
				$DB_site2->query("update article_cave set production_froid = '".mysqli_real_escape_string($DB_site2->link_id,$production_froid)."', production_chaud = '".mysqli_real_escape_string($DB_site2->link_id,$production_chaud)."', declenchement_chaud = '".mysqli_real_escape_string($DB_site2->link_id,$declenchement_chaud)."', fonction_hiver_techno = '".mysqli_real_escape_string($DB_site2->link_id,$fonction_hiver_techno)."', parois_laterales = '".mysqli_real_escape_string($DB_site2->link_id,$parois_laterales)."', parois_fond = '".mysqli_real_escape_string($DB_site2->link_id,$parois_fond)."', revetement_exterieur = '".mysqli_real_escape_string($DB_site2->link_id,$revetement_exterieur)."', isolation = '".mysqli_real_escape_string($DB_site2->link_id,$isolation)."', gaz='".mysqli_real_escape_string($DB_site2->link_id,$gaz)."',absence_vibration = '".mysqli_real_escape_string($DB_site2->link_id,$absence_vibration)."', type_ventilation='".mysqli_real_escape_string($DB_site2->link_id,$_POST['type_ventilation'])."' where artid = '$artid'");
				$erreur = "Article modifié" ;
				modifhistory($_POST);
				break;
			case "regulation":
				$DB_site2->query("update article set regulation = '".mysqli_real_escape_string($DB_site2->link_id,$regulation)."', boutons_reglage = '".mysqli_real_escape_string($DB_site2->link_id,$boutons_reglage)."', reglage = '".mysqli_real_escape_string($DB_site2->link_id,$reglage)."', affichage = '".mysqli_real_escape_string($DB_site2->link_id,$affichage)."', thermometre = '".mysqli_real_escape_string($DB_site2->link_id,$thermometre)."', temperature = '".mysqli_real_escape_string($DB_site2->link_id,$temperature)."', nbr_temperature = '".mysqli_real_escape_string($DB_site2->link_id,$nbr_temperature)."', plage_temperature='".mysqli_real_escape_string($DB_site2->link_id,$plage_temperature)."' where artid = '$artid'");
				$erreur = "Article modifié" ;
				modifhistory($_POST);
				break;
			case "nosconseils":
                if(isset($_POST['modif'])){
                    $DB_site2->query("UPDATE article SET instructions=".intval(isset($_POST['instructions']))." WHERE artid=".$artid);
                    $DB_site2->query("DELETE FROM article_nosconseils WHERE artid=".$artid);
                    if(isset($_POST['conseil'])){
                        foreach($_POST['conseil'] as $typeconseil=>$conseils){
                            foreach($conseils as $conseil){
                                $DB_site2->query("INSERT INTO article_nosconseils SET artid=".$artid.", type='".$typeconseil."', path='".$conseil."'");
                            }
                        }
                    }
                }
                if(isset($_POST['typeconseil'])){
                    if($_POST['FR']!=""){
                        $name=str_replace(" ","_",stripslashes($_POST['FR']));
                        $name=strtr($name, 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ', 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
                        $path="/article/".$_POST['typeconseil']."/".$_POST['marque']."/".substr(str_replace(array("'","\"","/",",",";","\\"),"",$name),0,100);
                        $req=$DB_site2->query_first("SELECT COUNT(*) FROM traduction WHERE path='".$path."'");
                        if($req[0]==0){
                            $sql="INSERT INTO traduction SET path='".mysqli_real_escape_string($DB_site2->link_id,$path)."'";
                            foreach($langues as $langue) $sql.=", `".$langue."`='".mysqli_real_escape_string($DB_site2->link_id,$_POST[$langue])."'";
                            $result=$DB_site2->query($sql);
                        }
                        $DB_site2->query("INSERT INTO article_nosconseils SET artid=".$artid.", type='".$_POST['typeconseil']."', path='".$path."'");
                    }
                }
				$erreur="Article modifié";
				modifhistory($_POST);
				break;
			case "mcid":
			
				$DB_site2->query("delete from article_attribut where artid = '$artid'");
				if (is_array($chk_attributs))		{
					foreach ($chk_attributs as $cle=>$valeur)		{
						$valeur = addslashes($valeur);
						$DB_site2->query("insert into article_attribut (artid, attributid, valeur) values ('$artid', '$cle', '$valeur')");
					}
				}
				$erreur = "Article modifié" ;			
				break;
			case "lot":
				
			break;

			case "accessoires":
			     if(isset($_POST['add_acc'])) $DB_site2->query("INSERT INTO article_accessoire SET artid=".$artid.", libelle='".mysqli_real_escape_string($DB_site2->link_id,$_POST['add_acc'])."', qte=".intval($_POST['qte']));
			     if(isset($_POST['del_acc'])) $DB_site2->query("DELETE FROM article_accessoire WHERE id=".$_POST['del_acc']);
			     if(isset($_POST['qte_id'])) $DB_site2->query("UPDATE article_accessoire SET qte=".intval($_POST['qte'])." WHERE id=".$_POST['qte_id']);
			break;

			case "origine":
			$DB_site2->query("UPDATE article SET paysfabrication='".mysqli_real_escape_string($DB_site2->link_id,$paysfabrication)."',paysid=$paysidfab,douanier='$douanier',codebarre='$codebarre' WHERE artid=".$artid);
			$erreur = "Article modifié" ;
			modifhistory($_POST);
			break;

			case "capacite":

			$exist = $DB_site2->query_first("SELECT artid FROM article_capacite WHERE artid =".$artid);
			$sql="";
			for($i=1;$i<6;$i++){
				$capacite_unite = mysqli_real_escape_string($DB_site2->link_id,$_POST['capacite_unite'.$i]);
				$capacite_valeur = mysqli_real_escape_string($DB_site2->link_id,$_POST['capacite_valeur'.$i]);
				$capacite_mini = mysqli_real_escape_string($DB_site2->link_id,$_POST['capacite_mini'.$i]);
				$capacite_maxi = mysqli_real_escape_string($DB_site2->link_id,$_POST['capacite_maxi'.$i]);
				$sql.="capacite_unite$i='".$capacite_unite."', capacite_valeur$i = '".$capacite_valeur."', capacite_mini$i = '".$capacite_mini."', capacite_maxi$i = '".$capacite_maxi."',";
				$erreur = "Article modifié";
			}

			if(is_null($exist[0])) 
				$sql =  "INSERT INTO article_capacite SET ".$sql." artid=".$artid;
			else{
				$sql = substr($sql, 0,-1);
				$sql = "UPDATE article_capacite SET ".$sql." WHERE artid=".$artid;
			}

			$DB_site2->query($sql);

			modifhistory($_POST);
			break;
		  
			case "amazon":
			case "rdc":
			case "priceminister":
			case "google":
			case "windil":
			case "pixmania":
			case "showroomprive":
			case "retif":
			    $listmp=array("amazon","rdc","priceminister","google","windil","pixmania","showroomprive","retif");
			    $article=$DB_site2->query_first("SELECT artcode FROM article WHERE artid=".$artid);
			    foreach($listmp as $mp){
				$oldvalue=$DB_site2->query_first("SELECT * FROM market_place_champs WHERE artcode='$article[0]' AND market_place='$mp'");
				if($oldvalue['enabled']=="")
				    $sql="INSERT INTO market_place_champs SET artcode='$article[0]', market_place='$mp', enabled=".intval(isset($_POST['export'][$mp]));
				else
				    $sql="UPDATE market_place_champs SET enabled=".intval(isset($_POST['export'][$mp]))." WHERE artcode='$article[0]' AND market_place='$mp'";

				$DB_site2->query($sql);
			    }

				
				break;
			}
		switch ($action2) // action où on arrive
			{
			case "infos": // ref, marque, lib, poids, frais g°, prix, tva, visible,stocks
				$sql = "SELECT bdd_table, typearticleid FROM typearticle";
				$sql .= " INNER JOIN article a USING(typearticleid)";
				$sql .=" WHERE a.artid= '$artid'";
				$table_type = $DB_site2->query_first($sql);
				$typearticleid = $table_type[1];
				$typearticle = $typearticleid;
				$article=$DB_site2->query_first("select a.*, spec.* from article AS a LEFT JOIN ".$table_type['bdd_table']." AS spec USING(artid)  where artid = '$artid'") ;
				$catid = $article[catid] ;
				$artcode = $article[artcode] ;
				$artcode_eurocave = $article[artcode_eurocave] ;
				$fraisgestion = $article[fraisgestionid] ;
				$libelleFR = $article[libelleFR] ;
				$libelleDE = $article[libelleDE] ;
				$libelleEN = $article[libelleEN] ;
				$libelleIT = $article[libelleIT] ;
				$libelleSP = $article[libelleSP] ;
				if ($article[delailivraison] != 0)
					$delailivraison = $article[delailivraison] ;
				else {
					$param=$DB_site2->query_first("select delailivraison from parametresgeneraux") ;
					$delailivraison = $param[delailivraison] ;
					}
				$delailivraison_txt = $article[delailivraison_txt] ;
				$opt_visible = $article[active] ;
				$msg_visible="";
				$dis_visible="";
				if($article['prix']==0){
				  $opt_visible=0;
				  $msg_visible="Il faut configurer les prix pour activer l'article<br>";
				  $dis_visible="disabled";
				}

				$version_porte = $article[version_porte] ;
				$coloris = $article[coloris] ;
				$coloris_porte = $article[coloris_porte] ;
				$absence_vibration = $article[absence_vibration] ;
				$filtre_charbon = $article[filtre_charbon] ;
				$rangements_txt = $article[rangements_txt] ;
				$conso_kw=$article[conso_kw];
				$conso_euro = $article[conso_euro];
				$refscons=$article['refs_fabricant'];
				
				switch ($opt_visible)
					{
					case '0':
						$checkedv0 = "checked" ;
						break ;
					case '1':
						$checkedv1 = "checked" ;
						break ;
					case '2':
						$checkedv2 = "checked" ;
						break ;
					case '4':
						$checkedv4 = "checked" ;
						break ;
					case '6':
						$checkedv6 = "checked" ;
						break ;
					}
				$opt_sowine = $article[sowine] ;
				switch ($opt_sowine)
					{
					case '0':
						$checkedsw0 = "checked" ;
						break ;
					case '1':
						$checkedsw1 = "checked" ;
						break ;
						case '4':
						$checkedsw1 = "checked" ;
						break ;
					}					
				
				$articlemarque=$DB_site2->query_first("select * from article_marque where artid = '$artid'") ;
				$mq = $articlemarque[marqueid] ;
				
				break ;
			case "visible":
				$article=$DB_site2->query_first("select * from article where artid = '$artid'") ;
				$delailivraison = $article['delailivraison'] ;
				$delailivraison_txt = $article['delailivraison_txt'] ;
				$opt_visible = $article['active'] ;
				$redirect=$article['redirect'];
				$msg_visible="";
				$dis_visible="";
				if($article['prix']==0){
				  $opt_visible=0;
				  $msg_visible="Il faut configurer les prix pour activer l'article<br>";
				  $dis_visible="disabled";
				}
				$checkedv0=$checkedv1=$checkedv2=$checkedv4=$checkedv6="";
				${"checkedv".$opt_visible}="checked";

				$opt_cdable = $article['commandable'] ;
				switch ($opt_cdable){
					case '0':
						$checkedcd0 = "checked" ;
						$appel_fonction="<SCRIPT LANGUAGE=\"JavaScript\">pascommandable();</SCRIPT>" ;
						if ($article[prix] == 0){
							$chkaffpx0 = "checked" ;
							$chkaffpx1 = "" ;
							$appel_fonction.="<SCRIPT LANGUAGE=\"JavaScript\">affichepasprix();</SCRIPT>" ;
						}else{
							$chkaffpx0 = "" ;
							$chkaffpx1 = "checked" ;
							$appel_fonction.="<SCRIPT LANGUAGE=\"JavaScript\">afficheprix($article[prix]);</SCRIPT>" ;
						}
						break ;
					case '1':
						$checkedcd1 = "checked" ;
						$appel_fonction="<SCRIPT LANGUAGE=\"JavaScript\">commandable();</SCRIPT>" ;
						break ;
					case '2':
						$checkedcd2 = "checked" ;
						$appel_fonction="<SCRIPT LANGUAGE=\"JavaScript\">commandable();</SCRIPT>" ;
						break ;
					default:
						$checkedcd1 = "checked" ;
						$appel_fonction="<SCRIPT LANGUAGE=\"JavaScript\">commandable();</SCRIPT>" ;
						break ;
					}
			break;
			case "img":
                
				break ;

			case "desc":
				break ;
			case "desc2":
				break ;
			case "caract":
				break ;
			case "saisie":
				break ;
			case "stocks":
				//si on arrive par les stocks
				$article=$DB_site2->query_first("select * from article where artid = '$artid'") ;
				$catid = $article[catid] ;
				break;
			case "equipements": 
				$article=$DB_site2->query_first("select a.*, spec.* from article AS a LEFT JOIN ".$table_type['bdd_table']." AS spec USING(artid)  where artid = '$artid'") ;
				$interrupteur = $article[interrupteur] ;
				$hygrometrie = $article[hygrometrie] ;
				$hygrometre = $article[hygrometre] ;
				$eclairage = $article[eclairage] ;
				$bouton_test = $article[bouton_test] ;
				$pieds_reglables = $article[pieds_reglables] ;
				$serrure = $article[serrure] ;
				$poignee = $article[poignee] ;
				$modularite_interieure = $article[modularite_interieure] ;
				$roulettes = $article[roulettes] ;
				$vide_poche = $article[vide_poche] ;
				$clayette_fil = $article[clayette_fil] ;
				$panier_transport = $article[panier_transport] ;
				$vinokit = $article[vinokit] ;
				$tablette = $article[tablette] ;
				$clayette_hetre = $article[clayette_hetre] ;
				$tapis_basse = $article[tapis_basse] ;
				
				$optsensporte="";
				$sensporte=array(0=>"Porte à droite",1=>"Porte à gauche",2=>"Porte réversible");
				foreach($sensporte as $sensporteid=>$txt)
				    $optsensporte.="<option value=\"".$sensporteid."\"".($article['sensporte']==$sensporteid?" selected":"").">".$txt."</option>";
				
				$opt_serrure="";
				$serrure_infos=array(0=>"Pas de serrure",1=>"Serrure",2=>"Serrure en option");
				foreach($serrure_infos as $key=>$txt)
				    $opt_serrure.="<option value=\"".$key."\"".($article['serrure_info']==$key?" selected":"").">".$txt."</option>";
				break ;					
			case "regulation": 
				$article=$DB_site2->query_first("select * from ".$table_type['bdd_table']." where artid = '$artid'") ;
				$regulation = $article[regulation] ;
				$boutons_reglage = $article[boutons_reglage] ;
				$reglage = $article[reglage] ;
				$affichage = $article[affichage] ;
				$temperature = $article[temperature] ;
				$thermometre = $article[thermometre] ;
				break ;	
			case "emballage": 
				$article=$DB_site2->query_first("select * from article where artid = '$artid'") ;
				$dimensions = $article[dimensions] ;
				$hauteur_txt = $article[hauteur] ;
				$largeur_txt = $article[largeur] ;
				$profondeur_txt = $article[profondeur] ;
				$poid = $article[poid] ;
				$emballage_hauteur = $article[emballage_hauteur1] ;
				$emballage_largeur = $article[emballage_largeur1] ;
				$emballage_profondeur = $article[emballage_profondeur1] ;
				$emballage_poids = $article[emballage_poids1] ;
				$emballage_hauteur2 = $article[emballage_hauteur2] ;
				$emballage_largeur2 = $article[emballage_largeur2] ;
				$emballage_profondeur2 = $article[emballage_profondeur2] ;
				$emballage_poids2 = $article[emballage_poids2] ;
				$emballage_hauteur3 = $article[emballage_hauteur3] ;
				$emballage_largeur3 = $article[emballage_largeur3] ;
				$emballage_profondeur3 = $article[emballage_profondeur3] ;
				$emballage_poids3 = $article[emballage_poids3] ;
				$emballage_volume=round(($article[emballage_hauteur1]*$article[emballage_largeur1]*$article[emballage_profondeur1])/pow(10,9),2);
				break ;

			case "recherche":
				$usages=$DB_site2->query("SELECT * FROM recherche_usage ");
				while ($usage=$DB_site2->fetch_array($usages)){
					$countusage=$DB_site2->query_first("SELECT count(*) as nb FROM articlerecherche_usage WHERE artid='$artid' AND rechercheusageid='$usage[rechercheusageid]' ");
					if ($countusage[nb]==1)
						${"usagechecked".$usage[rechercheusageid]}="checked";
				}

			
				$typesporte=$DB_site2->query("SELECT * FROM recherche_typeporte ");
				while ($typeporte=$DB_site2->fetch_array($typesporte)){
					$counttypeporte=$DB_site2->query_first("SELECT count(*) as nb FROM articlerecherche_typeporte WHERE artid='$artid' AND recherchetypeporteid='$typeporte[recherchetypeporteid]' ");
					if ($counttypeporte[nb]==1)
						${"portechecked".$typeporte[recherchetypeporteid]}="checked";
				}
			
			
				$coloriss=$DB_site2->query("SELECT * FROM recherche_coloris ");
				while ($coloris=$DB_site2->fetch_array($coloriss)){
					$countcoloris=$DB_site2->query_first("SELECT count(*) as nb FROM articlerecherche_coloris WHERE artid='$artid' AND recherchecolorisid='$coloris[recherchecolorisid]' ");
					if ($countcoloris[nb]==1)
						${"colorischecked".$coloris[recherchecolorisid]}="checked";
				}
			
			
				$rangements=$DB_site2->query("SELECT * FROM recherche_moderangement ");
				while ($rangement=$DB_site2->fetch_array($rangements)){
					$countrangement=$DB_site2->query_first("SELECT count(*) as nb FROM articlerecherche_moderangement WHERE artid='$artid' AND recherchemoderangementid='$rangement[recherchemoderangementid]' ");
					if ($countrangement[nb]==1)
						${"modechecked".$rangement[recherchemoderangementid]}="checked";
				}
			
			 
				$emplacements=$DB_site2->query("SELECT * FROM recherche_emplacement ");
				while ($emplacement=$DB_site2->fetch_array($emplacements)){
					$countemplacement=$DB_site2->query_first("SELECT count(*) as nb FROM articlerecherche_emplacement WHERE artid='$artid' AND rechercheemplacementid='$emplacement[rechercheemplacementid]' ");
					if ($countemplacement[nb]==1)
						${"emplacementchecked".$emplacement[rechercheemplacementid]}="checked";
				}
				
				break ;
			case "performances": 
				$article=$DB_site2->query_first("select a.*, spec.* from article AS a LEFT JOIN ".$table_type['bdd_table']." AS spec USING(artid)  where artid = '$artid'") ;
				$puissance_electrique = $article[puissance_electrique] ;
				$intensite_electrique = $article[intensite_electrique] ;
				$dispo_pieces = $article[dispo_pieces];
				$conso_kw=round($article['conso_kw'],1);
				$conso_kw_old=round($article['conso_kw_old'],1);
				$conso_an=round($article['conso_kw']*365);
				$conso_an_old=round($article['conso_kw_old']*365);
				$conso_euro=round($conso_an*$prixkw);
				$conso_euro_old=round($conso_an_old*$prixkw);
				$niveau_bruit = $article[niveau_bruit] ;
				$niveau_bruit_abcd = $article[niveau_bruit_abcd] ;
				$niveau_bruit_old = $article[niveau_bruit_old] ;
				for($i=-15;$i<=45;$i++){
				    $temp_instal_min_opt.="<option value=\"".$i."\"".($article['temp_instal_min']==$i?" selected":"").">".$i."</option>";
				    $temp_instal_max_opt.="<option value=\"".$i."\"".($article['temp_instal_max']==$i?" selected":"").">".$i."</option>";
				}
				break ;											
			case "technologies": 
				$article=$DB_site2->query_first("select a.*, spec.* from article AS a LEFT JOIN ".$table_type['bdd_table']." AS spec USING(artid)  where artid = '$artid'") ;
				$production_froid = $article[production_froid] ;
				$production_chaud = $article[production_chaud] ;
				$declenchement_chaud = $article[declenchement_chaud] ;
				$fonction_hiver_techno = $article[fonction_hiver_techno] ;
				$parois_laterales = $article[parois_laterales] ;
				$parois_fond = $article[parois_fond] ;
				$revetement_exterieur = $article[revetement_exterieur] ;
				$isolation = $article[isolation] ;
				$gaz=$article[gaz];
				break ;
			case "garantie_incluse": 
				$article=$DB_site2->query_first("select * from article where artid = '$artid'") ;
				$garantie_incluse = $article[garantie_incluse] ;
				break ;	
			}
			article_action($disp_tabs, $old, 'update');
		}
	}

if (isset ($quitter) and $quitter == "1") // terminer
	{
	if ($erreur == "Article modifié")
		{
		echo "<SCRIPT LANGUAGE=\"JavaScript\">document.location = 'catalogue.php?catid=$catid' ;</SCRIPT>" ;
		}
	else
		{
		//$action2 ;
		}
	}

if (!isset ($action2) or $action2 == "") // on arrive
	{
	$action2 = "infos" ;
	$msg_visible="";
	$dis_visible="";
	if($article['prix']==0){
	  $opt_visible=0;
	  $checkedv0="checked";
	  $checkedv1="";
	  $checkedv2="";
	  $msg_visible="Il faut configurer les prix pour activer l'article<br>";
	  $dis_visible="disabled";
	}
	}

// Modif TSI: !!!!! Gestion des onglets, ca tombe sous le sens non ? !!!!!
require "function3.php";

$contenu_onglet = "" ;
switch ($action2) // formulaires
	{
	case "suivi":
		include("article_history.php");
	break;

	case "simulateur":  //SIMULATEUR WINEBAR
		include("article_simulateur.php");
	break;
	case "devisdesc":  
		include("article_devisdesc.php");
	break;
	case "cmddesc":  
		include("article_cmddesc.php");
	break;

	case "infos":
		include("article_infos.php");
	break ;

	case "prix":
	case "tempprice":
	case "deltempprice":
        $article=$DB_site2->query_first("select * from article where artid = '$artid'") ;
		include("article_prix.php");
	
	break;
	case "four":
	include("article_four.php");
	break;
	case "coloris":
	      include("article_coloris.php");
	break;
	case "visible":
	$redirectart=null;
	$redirecturl="Accueil";
	if(intval($redirect)>0){
	    $redirectart=$DB_site2->query_first("SELECT * FROM article WHERE artid=".intval($redirect));
	    $redirecturl="<a href=\"https://$host/".url_rewrite2(strtolower($redirectart['libelleFR']))."-art".$redirectart['artid'].".htm\" target=\"_blank\">https://$host/".url_rewrite2(strtolower($redirectart['libelleFR']))."-art".$redirectart['artid'].".htm</a>";
	}
	$arturl="https://$host/".url_rewrite2(strtolower($article['libelleFR']))."-art".$artid.".htm";
	$contenu_onglet .= "
		<h3>Informations produit</h3>
		<font class=\"cost\">$erreur</font>
		<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"mq2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<input type=\"hidden\" name=\"rempos\" value=\"\">
		<input type=\"hidden\" name=\"remp_artid\" value=\"\">
		<input type=\"hidden\" name=\"redirect\" value=\"".$redirect."\">
		<table width=\"90%\" align=\"center\" class=\"table_infosproduit\">";
		
		$autovtxt="";
		$r=$DB_site2->query("SELECT * FROM article_visible WHERE artid='$artid'");
		while($autov=$DB_site2->fetch_array($r)) $autovtxt.=date("d/m/Y",$autov['date'])." ".($autov['active']==1?"Visible":"Invisible")." <img src=\"images/supprimer.gif\" align=\"absmiddle\" onclick=\"document.getElementsByName('delautovisible')[0].value=$autov[id];clique('visible','visible');\"><br>";

		$autoctxt="";
		$commstatus=array(0=>"Vendu en magasin",1=>"Commandable",2=>"Non disponible");
		$r=$DB_site2->query("SELECT * FROM article_commandable WHERE artid='$artid'");
		while($autoc=$DB_site2->fetch_array($r)) $autoctxt.=date("d/m/Y",$autoc['date'])." ".$commstatus[$autoc['commandable']]." <img src=\"images/supprimer.gif\" align=\"absmiddle\" onclick=\"document.getElementsByName('delautocomm')[0].value=$autoc[id];clique('visible','visible');\"><br>";


		$date_reappro="";
		if($article['date_appro']!=0) $date_reappro=date("d/m/Y",$article['date_appro']);
		foreach($sites as $site_code=>$site){
		    $msg_visible=$dis_visible="";
		    $checkedv0=$checkedv1=$checkedv2=$checkedv3=$checkedv4=$checkedv6=$checkedv7="";
		    $article_site=$DB_site2->query_first("SELECT * FROM article_site WHERE artid=".$artid." AND site_code='".$site_code."'");
		    if($article_site==""){
                            $msg_visible.="Prix à configurer avant d'activer le produit<br>";
                            $dis_visible="disabled";
		    }
                    if($article['typearticleid']==0){
                            $msg_visible.="type d'article à configurer avant d'activer le produit<br>";
                            $dis_visible="disabled";
		    }
                    if($article_site!="" && $article['typearticleid']!=0){
                            ${"checkedv".$article_site['active']}="checked";
                    }
                    
		    $contenu_onglet.="<tr>
		      <td align=\"left\">Etat produit :</td>
		      <td align=\"left\">
			<span style=\"color:red;\">$msg_visible</span>
			<img src=\"images/en_cours.png\" title=\"En cours de création sur site $site_code\" width=\"30\"> <input type=\"radio\" name=\"opt_visible[$site_code]\" value=\"6\" onchange=\"setmodif();\" $checkedv6 $dis_visible> En cours de création (fiche pas terminée)
			<br>
			<img src=\"images/new.png\" title=\"Nouveau à contrôler\" width=\"30\"> <input type=\"radio\" name=\"opt_visible[$site_code]\" value=\"4\" onchange=\"setmodif();\" $checkedv4 $dis_visible> Nouveau - à contrôler (nouvel article a vérifier avant mise en ligne)
			<br>
			<img src=\"images/visible.png\" title=\"Visible sur site $site_code\" width=\"30\"> <input type=\"radio\" name=\"opt_visible[$site_code]\" onchange=\"setmodif();\" value=\"1\" $checkedv1 $dis_visible> Actif (produit visible sur site) 
                        <br><img src=\"images/invisible.png\" title=\"Invisible sur site $site_code\" width=\"30\"> <input type=\"radio\" name=\"opt_visible[$site_code]\" value=\"0\" onchange=\"setmodif();\" $checkedv0 $dis_visible> Actif invisible (produit vendu mais apres dissociation, ou produit de base)
			<br><img src=\"images/supprime.png\" title=\"Supprimé sur site $site_code\" width=\"30\"> <input type=\"radio\" name=\"opt_visible[$site_code]\" value=\"2\" onchange=\"setmodif();\" $checkedv2 $dis_visible> Supprimé (erreur produit produit – ancien produit en lot - produit non visible nulle part)
			<br><img src=\"images/archive_temp.png\" title=\"Archivé temporairement sur site $site_code\" width=\"30\"> <input type=\"radio\" name=\"opt_visible[$site_code]\" value=\"5\" onchange=\"setmodif();\" $checkedv5 $dis_visible> 
			Archivé temporairement (produit non disponible temporairement mais visible dans \"anciens modèles\")
			<br><img src=\"images/archive.png\" title=\"Archivé définitivement sur site $site_code\" width=\"30\"> <input type=\"radio\" name=\"opt_visible[$site_code]\" value=\"3\" onchange=\"setmodif();\" $checkedv3 $dis_visible> 
			Archivé définitivement (produit plus fabriqué définitivement et visible dans \"anciens modeles\")
			<!--<br><img src=\"images/archive.png\" title=\"Archivé fin de stock $site_code\" width=\"30\"> <input type=\"radio\" name=\"opt_visible[$site_code]\" value=\"7\" onchange=\"setmodif();\" $checkedv7 $dis_visible> 
			Archivé à fin de stock (produit plus fabriqué définitivement )-->
		      </td>
		    </tr>";
		}
		
		$contenu_onglet.="
		<tr style=\"margin-top: 10px;\"><td align=\"left\" rowspan=\"3\">Changement automatique etat : </td><td><input type=\"hidden\" name=\"delautovisible\" value=\"0\">".$autovtxt."</td></tr>
		<tr><td id=\"newautoactive\" style=\"display:none;\">à partir du <input type=\"text\" id=\"newdatevisible\" name=\"newdatevisible\" size=\"10\" readonly onclick=\"calendrier_show(this.id,this.value);\"> le produit sera <input type=\"radio\" name=\"autovisible\" value=\"1\"> Visible &nbsp; <input type=\"radio\" name=\"autovisible\" value=\"0\"> Invisible <img src=\"images/supprimer.gif\" onclick=\"document.getElementById('newdatevisible').value='';document.getElementById('newautoactive').style.display='none';document.getElementById('addautovisible').style.display='table-row';\" title=\"Annuler\" align=\"absmiddle\"> <img src=\"images/coche_verte.svg\" onclick=\"clique('visible','visible');\" width=\"16\"></td></tr>
		<tr><td id=\"addautovisible\" onclick=\"document.getElementById('newautoactive').style.display='table-row';this.style.display='none';\" style=\"cursor:pointer;\"><img src=\"images/plus.gif\" align=\"absmiddle\"> Ajouter une date</td></tr>
		<tr><td>Redirection si invisible</td><td>
		    <div style=\"width:200px;\">".($redirect!=""?"<img src=\"images/supprimer.gif\" title=\"Supprimer\" onclick=\"selectredirect('');\">".vignette($redirectart):"")."</div>
		    <div><a href=\"$arturl\" target=\"_blank_\">$arturl</a><br>&gt;&gt; $redirecturl</div>
		    <br>Référence : <input type=\"text\" onkeyup=\"srcredirect(this.value)\">
		    <div id=\"results2\"></div>
		</td></tr>
		<tr><td colspan=\"2\"><hr></td></tr>
		<tr>
                    <td align=\"left\" valign=\"top\">Commandable<strong>*</strong> : &nbsp;</td>
                    <td align=\"left\">
                        <input type=\"radio\" name=\"opt_commandable\" value=\"1\" onchange=\"setmodif();\" $checkedcd1> Oui
                        <br><input type=\"radio\" name=\"opt_commandable\" value=\"0\" onchange=\"setmodif();\" $checkedcd0> Prix sur demande (prix non visible - nous contacter)
                        <br><input type=\"radio\" name=\"opt_commandable\" value=\"2\" onchange=\"setmodif();\" $checkedcd2> Non disponible<br>
                    </td>
		</tr>
		<tr><td align=\"left\" rowspan=\"3\">Changement automatique commandable :</td><td><input type=\"hidden\" name=\"delautocomm\" value=\"0\">".$autoctxt."</td></tr>
		<tr><td id=\"newautocomm\" style=\"display:none;\">à partir du <input type=\"text\" id=\"newdatecomm\" name=\"newdatecomm\" size=\"10\" readonly onclick=\"calendrier_show(this.id,this.value);\"><br>le produit sera <input type=\"radio\" name=\"autocomm\" value=\"1\"> Commandable &nbsp; <input type=\"radio\" name=\"autocomm\" value=\"0\"> Vendu en magasin &nbsp; <input type=\"radio\" name=\"autocomm\" value=\"2\"> Non disponible <img src=\"images/supprimer.gif\" onclick=\"document.getElementById('newdatecomm').value='';document.getElementById('newautocomm').style.display='none';document.getElementById('addautocomm').style.display='table-row';\" title=\"Annuler\" align=\"absmiddle\"> <img src=\"images/coche_verte.svg\" onclick=\"clique('visible','visible');\" width=\"16\"></td></tr>
		<tr><td id=\"addautocomm\" onclick=\"document.getElementById('newautocomm').style.display='table-row';this.style.display='none';\" style=\"cursor:pointer;\"><img src=\"images/plus.gif\" align=\"absmiddle\"> Ajouter une date</td></tr>
		<tr><td>Produits de remplacement :</td><td>
		<table><tr>";

		$art=$DB_site2->query_first("SELECT a.*,ar.position FROM article_remplacement ar INNER JOIN article a ON(a.artid=ar.remp_artid) WHERE ar.artid=".$article['artid']);
		$contenu_onglet.="<td id=\"remp1\" style=\"border:solid 1px #888; width:200px; height:150px;\" onclick=\"selectremp(1)\">".($art!=""?"<img src=\"images/supprimer.gif\" align=\"right\" onclick=\"rmremp(1)\">":"").vignette($art)."</td>";
		
		$contenu_onglet.="</tr></table></td></tr>
		<tr><td></td><td id=\"modif\" style=\"display:none;\"><p>Rechercher un article <input type=\"text\" onkeyup=\"srcartid(this.value)\"></p><div id=\"results\" style=\"text-align:left;\"></div></td></tr>
		<tr><td colspan=\"2\"><hr></td></tr>" ;
		$contenu_onglet .= "
		<tr>
			<td align=\"left\">Délai de livraison : </td>
			<td align=\"left\"><input type=\"text\" name=\"delailivraison\" value=\"$delailivraison\" size=\"4\" onchange=\"setmodif();\"> jours</td>
		</tr>" ;
		$contenu_onglet .= "
		<!--<tr>
			<td align=\"left\">Texte Délai de livraison : </td>
			<td align=\"left\"><input type=\"text\" name=\"delailivraison_txt\" value=\"$delailivraison_txt\" onchange=\"setmodif();\" size=\"50\"></td>
		</tr>-->
		<tr>
			<td>Date de réapprovisionnement : </td>
			<td><input type=\"text\" name=\"date_reappro\" id=\"date_reappro\" value=\"$date_reappro\" onclick=\"calendrier_show(this.id,this.value);document.getElementById('suppr_reappro').style.display='inline';\" onchange=\"setmodif();\" readonly><img src=\"images/supprimer.gif\" id=\"suppr_reappro\" style=\"display:".($date_reappro==""?"none":"")."\" onclick=\"document.getElementById('date_reappro').value='';this.style.display='none'\"></td>
		</tr>" ;

		list($pro,$part)=str_split(str_pad(decbin($article['pro']),2,0,STR_PAD_LEFT));
		$contenu_onglet.="<tr>
		      <td colspan=\"2\"><hr></td>
		      </tr>
		      <!--<tr><td>Afficher sur site :<br><small>L'article doit être au moins sur un site</small></td>
			<td><input type=\"hidden\" name=\"pro[]\" value=\"1\"><input type=\"checkbox\" name=\"pro[]\" value=\"2\" ".($pro==1?"checked":"")." onclick=\"return false;\"> Pro</td>
		      </tr>-->";

	$contenu_onglet.="</table></form>";
	
	include_once("../class/stock.class.php");
	$stock=new stock($DB_site2);
	$item=$stock->in_stock($artid);
	if($item!=false){
	    $lieux=$stock->lieux();
	    $stockitems0=$stock->search($artid,null,null,0);
	    foreach($stockitems0 as $item) $list[$item->lieu][0]++;
	    $stockitems1=$stock->search($artid,null,null,1);
	    foreach($stockitems1 as $item) $list[$item->lieu][1]++;
	    $stockitems2=$stock->search($artid,null,null,2);
	    foreach($stockitems2 as $item) $list[$item->lieu][2]++;
	    $noncommandenb=0;
	    $noncommande=$item->cmd_non_reserve();
	    foreach($noncommande as $noncmdid){
		      $noncmdqte=$DB_site2->query_first("SELECT SUM(qte) FROM lignefacture WHERE factureid=".$noncmdid." AND (artid=".$item->artid.($item->artids!=""?" OR artid IN (".$item->artids.")":"").")");
		      $noncommandenb+=$noncmdqte[0];
	    }	
	    $noncommande0nb=0;
	    $noncommande0=$item->cmd_non_reserve(array(0),array(2,4,5));
	    foreach($noncommande0 as $noncmdid){
		    $noncmdqte=$DB_site2->query_first("SELECT SUM(qte) FROM lignefacture WHERE factureid=".$noncmdid." AND (artid=".$item->artid.($item->artids!=""?" OR artid IN (".$item->artids.")":"").")");
		    $noncommande0nb+=$noncmdqte[0];
	    }
	    $total=array();

	    $contenu_onglet.="<h3>Stock</h3>
	    <table>
	    <thead style=\"background-color:#DDD;color:black;\">
	    <tr><td>Lieu</td><td>Qté stock physique</td><td>Qté non réservé</td><td>Qté réservé</td><td>Qté réel instantané</td><td>Qté en attente paiement</td><td>Qté en réappro</td><td>Qté stock virtuel</td></tr>
	    </thead>";
	    
	    if($list != null){
		    foreach($list as $lieuid=>$items){
			$total[0]+=$items[0];
			$total[1]+=$items[1];
			$total[2]+=$items[2];
			$contenu_onglet.="<tr><td>".$lieux[$lieuid]."</td><td align=\"center\">".intval($items[1]+$items[2])."</td><td align=\"center\">".$noncommandenb."</td><td align=\"center\">".intval($items[2])."</td><td align=\"center\">".intval($items[1]-$noncommandenb)."</td><td align=\"center\">".$noncommande0nb."</td><td align=\"center\">".intval($items[0])."</td><td align=\"center\">".intval($items[1]+$items[0]-$noncommande0nb-$noncommandenb)."</td></tr>";
		    }
		}

	    $contenu_onglet.="<tr><td>Total</td><td align=\"center\">".intval($total[1]+$total[2])."</td><td align=\"center\">".$noncommandenb."</td><td align=\"center\">".intval($total[2])."</td><td align=\"center\">".intval($total[1]-$noncommandenb)."</td><td align=\"center\">".$noncommande0nb."</td><td align=\"center\">".intval($total[0])."</td><td align=\"center\">".intval($total[1]+$total[0]-$noncommande0nb-$noncommandenb)."</td></tr>
	    </table>";
	}
	break;
	case "montage":
	     include("article_montage.php");
	 
	break;
	case "img":
		include("article_image.php");
		break ;
		
	case "video":
		include("article_video.php");
	break;
		
	case "icon":
		include("article_icons.php");
		break;
	case "desc":
		$contenu_onglet .= "
		<h3>Description</h3>
                <script language=\"javascript\">
                CKEDITOR.config.height='500px';
               CKEDITOR.config.filebrowserBrowseUrl = '/ckeditor/kcfinder/browse.php?type=files';
               CKEDITOR.config.filebrowserImageBrowseUrl = '/ckeditor/kcfinder/browse.php?type=images';
               CKEDITOR.config.filebrowserUploadUrl = '/ckeditor/kcfinder/upload.php?type=files';
               CKEDITOR.config.filebrowserImageUploadUrl = '/ckeditor/kcfinder/upload.php?type=images';
                </script>
		<font class=\"cost\">$erreur</font>
				<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\" style=\"display:inline;\">
				<input type=\"hidden\" name=\"old\" value=\"\">
				<input type=\"hidden\" name=\"quitter\" value=\"\">
				<input type=\"hidden\" name=\"action2\" value=\"\">
				<input type=\"hidden\" name=\"modif\" value=\"0\">
				<input type=\"hidden\" name=\"navig\" value=\"$browser\">
				<input type=\"hidden\" name=\"catid\" value=\"$catid\">
				<input type=\"hidden\" name=\"artid\" value=\"$artid\">
				<input type=\"hidden\" name=\"lang\" value=\"$lang\">
				<input type=\"hidden\" name=\"autrelang\" value=\"\">" ;
		

		foreach($host_conf->langues as $lang){
		    $contenu_onglet .= "
		    <br>Description <img src=\"images/drapeaux/$lang.gif\" width=\"20\">";
		    $description = "description$lang" ;
		    $actu=$DB_site2->query_first("select $description from article where artid = '$artid'");
		    $content = $actu[$description] ;
		    $contenu_onglet .= "<table align=\"center\" width=\"100%\"><tr><td>";
                    $contenu_onglet .= "<textarea name=\"content".$lang."\" class=\"ckeditor\">".$content."</textarea>";
		    $contenu_onglet .= "</td></tr></table>";
		}

		$contenu_onglet.="</form>";
		break ;
	case "desc2":
		if (isset($autrelang) and $autrelang != "")
			{
			$lang = $autrelang ;
			}
		elseif (!isset($lang) or $lang == "")
			{
			$lang = "FR" ;
			}
		$contenu_onglet .= "
		<h3>Description 2</h3>
		<font class=\"cost\">$erreur</font>
		<br><br>Choisir la langue à modifier : 
				<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\" style=\"display:inline;\">
				<input type=\"hidden\" name=\"old\" value=\"\">
				<input type=\"hidden\" name=\"quitter\" value=\"\">
				<input type=\"hidden\" name=\"action2\" value=\"\">
				<input type=\"hidden\" name=\"modif\" value=\"0\">
				<input type=\"hidden\" name=\"navig\" value=\"$browser\">
				<input type=\"hidden\" name=\"catid\" value=\"$catid\">
				<input type=\"hidden\" name=\"artid\" value=\"$artid\">
				<input type=\"hidden\" name=\"lang\" value=\"$lang\">
				<input type=\"hidden\" name=\"autrelang\" value=\"\">" ;
		$i = 0 ;
		while ($i < count($langues))
			{
			$contenu_onglet .= "<a href=\"Javascript: changelangue('$langues[$i]','$browser') ;\">$langues[$i]</a>&nbsp;&nbsp;" ;
			$i++ ;
			}

		$contenu_onglet .= "
		<br><br>Langue en cours : <strong>$lang</strong>" ;
		$wysiwyg_temp = $DB_site2->query_first("SELECT * FROM wysiwyg");
		$wysiwyg_suppr = $DB_site2->query( "DELETE FROM wysiwyg" );
		$description = "description2$lang" ;
		$actu=$DB_site2->query_first("select $description from article where artid = '$artid'");
		$content = $actu[$description] ;
		$contenu_onglet .= "<table align=\"center\" width=\"100%\"><tr><td><br>" ;

		$contenu_onglet .= "$wysiwyg</td></tr></table></form>";
		break ;
	case "argument":
		$article=$DB_site2->query_first("SELECT argument FROM article WHERE artid=".$artid);
		$contenu_onglet .= "
		<h3>Argument commercial interne</h3>
		<font class=\"cost\">$erreur</font><br>
		<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\" style=\"display:inline;\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"navig\" value=\"$browser\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<textarea name=\"argument\" style=\"width:600px;height:100px;\">".$article['argument']."</textarea>
		</form>" ;
		break;
	case "caract":
		include("article_options.php");
		
		break ;
	case "garantie":
		include("article_garantie.php");
		break;
		
	case "ref":
		$contenu_onglet .= "
		<h3>Mots clés</h3>
		<font class=\"cost\">$erreur</font>
		<br><form name=\"formu\" action=\"ajout_produit.php\" method=\"post\" style=\"display:inline;\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">" ;
		$article=$DB_site2->query_first("select ref_titleFR, ref_titleDE, ref_titleEN, ref_titleIT, ref_titleSP, ref_descriptionFR, 
		ref_descriptionDE, ref_descriptionEN, ref_descriptionIT, ref_descriptionSP, ref_keywordsFR, ref_keywordsDE, ref_keywordsEN, 
		ref_keywordsIT, ref_keywordsSP, bandeau_defilant from article where artid = '$artid'");
		$contenu_onglet .= "<table class=\"table_modules_boutiques\">" ;
		if(in_array("FR" , $langues))
			{
			$contenu_onglet .= "<tr><td colspan=\"2\"><strong>Français</strong></td></tr>";
			$contenu_onglet .= "<tr><td class=\"td_modules_boutiques\" valign=\"top\">Titre de la fenêtre</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_titleFR\" cols=\"50\" rows=\"2\">$article[ref_titleFR]</textarea></td></tr>";
			$contenu_onglet .= "<tr><td class=\"td_modules_boutiques\" valign=\"top\">META description</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_descriptionFR\" cols=\"50\" rows=\"6\">$article[ref_descriptionFR]</textarea></td></tr>";
			$contenu_onglet .= "<tr><td class=\"td_modules_boutiques\" valign=\"top\">META keywords</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_keywordsFR\" cols=\"50\" rows=\"6\">$article[ref_keywordsFR]</textarea></tr>";
			$contenu_onglet .= "<tr><td class=\"td_modules_boutiques\" valign=\"top\">Bandeau défilant</td><td class=\"td_modules_boutiques\"><textarea name=\"bandeaudefilantFR\" cols=\"50\" rows=\"6\">$article[bandeau_defilant]</textarea></tr>";
			}
		if(in_array("DE" , $langues))
			{
			$contenu_onglet .= "<tr><td colspan=\"2\"><strong>Allemand</strong></td></tr>";
			$contenu_onglet .= "<tr><td class=\"td_modules_boutiques\" valign=\"top\">Titre de la fenêtre</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_titleDE\" cols=\"50\" rows=\"2\">$article[ref_titleDE]</textarea></td></tr>";
			$contenu_onglet .= "<tr><td class=\"td_modules_boutiques\" valign=\"top\">META description</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_descriptionDE\" cols=\"50\" rows=\"6\">$article[ref_descriptionDE]</textarea></td></tr>";
			$contenu_onglet .= "<tr><td class=\"td_modules_boutiques\" valign=\"top\">META keywords</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_keywordsDE\" cols=\"50\" rows=\"6\">$article[ref_keywordsDE]</textarea>";
			}
		if(in_array("SP" , $langues))
			{
			$contenu_onglet .= "<tr><td colspan=\"2\"><strong>Espagnol</strong></td></tr>";
			$contenu_onglet .= "<tr><td class=\"td_modules_boutiques\" valign=\"top\">Titre de la fenêtre</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_titleSP\" cols=\"50\" rows=\"2\">$article[ref_titleSP]</textarea></td></tr>";
			$contenu_onglet .= "<tr><td class=\"td_modules_boutiques\" valign=\"top\">META description</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_descriptionSP\" cols=\"50\" rows=\"6\">$article[ref_descriptionSP]</textarea></td></tr>";
			$contenu_onglet .= "<tr><td class=\"td_modules_boutiques\" valign=\"top\">META keywords</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_keywordsSP\" cols=\"50\" rows=\"6\">$article[ref_keywordsSP]</textarea>";
			}
		if(in_array("IT" , $langues))
			{
			$contenu_onglet .= "<tr><td colspan=\"2\"><strong>Ilatien</strong></td></tr>";
			$contenu_onglet .= "<tr><td class=\"td_modules_boutiques\" valign=\"top\">Titre de la fenêtre</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_titleIT\" cols=\"50\" rows=\"2\">$article[ref_titleIT]</textarea></td></tr>";
			$contenu_onglet .= "<tr><td class=\"td_modules_boutiques\" valign=\"top\">META description</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_descriptionIT\" cols=\"50\" rows=\"6\">$article[ref_descriptionIT]</textarea></td></tr>";
			$contenu_onglet .= "<tr><td class=\"td_modules_boutiques\" valign=\"top\">META keywords</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_keywordsIT\" cols=\"50\" rows=\"6\">$article[ref_keywordsIT]</textarea>";
			}
		if(in_array("EN" , $langues))
			{
			$contenu_onglet .= "<tr><td colspan=\"2\"><strong>Anglais</strong></td></tr>";
			$contenu_onglet .= "<tr><td class=\"td_modules_boutiques\" valign=\"top\">Titre de la fenêtre</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_titleEN\" cols=\"50\" rows=\"2\">$article[ref_titleEN]</textarea></td></tr>";
			$contenu_onglet .= "<tr><td class=\"td_modules_boutiques\" valign=\"top\">META description</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_descriptionEN\" cols=\"50\" rows=\"6\">$article[ref_descriptionEN]</textarea></td></tr>";
			$contenu_onglet .= "<tr><td class=\"td_modules_boutiques\" valign=\"top\">META keywords</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_keywordsEN\" cols=\"50\" rows=\"6\">$article[ref_keywordsEN]</textarea>";
			}
		$contenu_onglet .= "</table>
		</form>";
		break ;
	case "votes":
		$contenu_onglet .= "<h3>Votes et commentaires</h3>
		<font class=\"cost\">$erreur</font>
		<form name=\"formu\" method=\"post\" action=\"ajout_produit.php\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<table width=\"100%\" align=\"center\">
			<tr>
				<td width=\"100%\" align=\"center\"><strong>Votes et commentaires postés sur cet article</strong></td>
			</tr>
			<tr>
				<td width=\"100%\" align=\"left\"><br><ul>" ;
		$article_comms=$DB_site2->query("select * from article a, articlevote av where a.artid = av.artid and a.artid = '$artid' order by datevote desc") ;
		if ($DB_site2->num_rows($article_comms) > 0)
			{
			while ($article_comm=$DB_site2->fetch_array($article_comms))
				{
				$contenu_onglet .= "<li>En date du $article_comm[datevote]
				<table>
				<TR><TD align=\"right\" valign=\"top\">Titre : </td><td align=\"left\"><input type=\"text\" value=\"$article_comm[titre]\"></td></tr>
				<tr><td align=\"right\" valign=\"top\">Contenu : </td><td align=\"left\"><textarea cols=\"40\" rows=\"3\">$article_comm[contenu]</textarea></td></tr></table>
				</li>" ;
				}
			}
		else
			{
			$arts_lies=$DB_site2->query("SELECT a.artid FROM liens_articles la, article a 
			WHERE ((a.artid = la.article_base AND article_lie = '$artid') OR (a.artid = la.article_lie AND article_base = '$artid'))");
			if ($DB_site2->num_rows($arts_lies) > 0)
				{
				$liste_arts = "0" ;
				while ($art_lie=$DB_site2->fetch_array($arts_lies))
					{
					$liste_arts .= ", $art_lie[0]" ;
					}
				$article_comms=$DB_site2->query("select * from article a, articlevote av where a.artid = av.artid and a.artid IN ($liste_arts) order by datevote desc") ;
				if ($DB_site2->num_rows($article_comms) > 0)
					{
					while ($article_comm=$DB_site2->fetch_array($article_comms))
						{
						$contenu_onglet .= "<li>En date du $article_comm[datevote]
						<table>
						<TR><TD align=\"right\" valign=\"top\">Titre : </td><td align=\"left\"><input type=\"text\" value=\"$article_comm[titre]\"></td></tr>
						<tr><td align=\"right\" valign=\"top\">Contenu : </td><td align=\"left\"><textarea cols=\"40\" rows=\"3\">$article_comm[contenu]</textarea></td></tr></table>
						</li>" ;
						}
					}
				else
					{
					$contenu_onglet .= "<li>Aucun vote ni commentaire sur cet article.</li>" ;
					}
				}
			else
				{
				$contenu_onglet .= "<li>Aucun vote ni commentaire sur cet article.</li>" ;
				}
			}
		$contenu_onglet .= 
				"</ul></td>
			</tr>
		</table></form>
		<a href=\"commentaires.php\" target=\"_blank\">Gérez les commentaires ici</a><br><bR>" ;
		break ;
	case "promos":
		$contenu_onglet .= "<h3>Promotions</h3>
		<font class=\"cost\">$erreur</font>
		<form name=\"formu\" method=\"post\" action=\"ajout_produit.php\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<table width=\"100%\" align=\"center\">
			<tr>
				<td width=\"100%\" align=\"center\"><strong>Promotions sur cet article</strong></td>
			</tr>
			<tr>
				<td width=\"100%\" align=\"left\"><br><ul>" ;
		$article_promos=$DB_site2->query("select datedebut, datefin, p.prix as prix, a.prix as prixbase from promotion p, article a where a.artid = p.artid 
		and p.artid = '$artid' order by datedebut desc") ;
		if ($DB_site2->num_rows($article_promos) > 0)
			{
			while ($article_promo=$DB_site2->fetch_array($article_promos))
				{
				$article_promo[datedebut] = substr ($article_promo[datedebut], 8, 2) . "/" . substr ($article_promo[datedebut], 5, 2) . "/" . substr($article_promo[datedebut], 0, 4) ;
				$article_promo[datefin] = substr ($article_promo[datefin], 8, 2) . "/" . substr ($article_promo[datefin], 5, 2) . "/" . substr($article_promo[datefin], 0, 4) ;
				$contenu_onglet .= "<li>Du $article_promo[datedebut] au $article_promo[datefin] : $article_promo[prix] € (au lieu de $article_promo[prixbase] €)</li>" ;
				}
			}
		else
			{
			$contenu_onglet .= "<li>Aucune promotion sur cet article.</li>" ;
			}
		$contenu_onglet .= 
				"</ul></td>
			</tr>
		</table></form>
		<a href=\"catalogue.php?catid=$catid\" target=\"_blank\">Ajoutez / Modifiez les promotions ici</a><br><bR>" ;
		break ;
	case "cadeaux":
		$contenu_onglet .= "<h3>Bons de réduction</h3>
		<font class=\"cost\">$erreur</font>
		<form name=\"formu\" method=\"post\" action=\"ajout_produit.php\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<table width=\"100%\" align=\"center\">
			<tr>
				<td width=\"100%\" align=\"center\"><strong>Bons de réduction sur cet article</strong></td>
			</tr>
			<tr>
				<td width=\"100%\" align=\"left\"><br><ul>" ;
		// article
		$article_cadeaux=$DB_site2->query("select * from article_cadeau ac, cadeau c where ac.cadeauid = c.cadeauid and artid = '$artid'") ;
		if ($DB_site2->num_rows($article_cadeaux) > 0)
			{
			while ($article_cadeau=$DB_site2->fetch_array($article_cadeaux))
				{
				if ($article_cadeau[datefin] != 0)
					{
					$article_cadeau[datefin] = substr ($article_cadeau[datefin], 8, 2) . "/" . substr ($article_cadeau[datefin], 5, 2) . "/" . substr($article_cadeau[datefin], 0, 4) ;
					$article_cadeau[datefin] = "(jusqu'au $article_cadeau[datefin])" ;
					}
				else
					{
					$article_cadeau[datefin] = "" ;
					}
				switch ($article_cadeau[typecadeau])
					{
					case 0:
						$article_cadeau[typecadeau] = "€" ;
						break ;
					case 1:
						$article_cadeau[typecadeau] = "%" ;
						break ;
					}
				$contenu_onglet .= "<li>$article_cadeau[code] : Réduction de $article_cadeau[valeurcadeau] $article_cadeau[typecadeau] $article_cadeau[datefin]</li>" ;
				}
			}
		// categorie
		$cats = $catid ;
		categories_parentes ($catid) ;
		$categorie_cadeaux=$DB_site2->query("select * from categorie_cadeau cc, cadeau c where cc.cadeauid = c.cadeauid and catid in ($cats)") ;
		if ($DB_site2->num_rows($categorie_cadeaux) > 0)
			{
			while ($categorie_cadeau=$DB_site2->fetch_array($categorie_cadeaux))
				{
				if ($categorie_cadeau[datefin] != 0)
					{
					$categorie_cadeau[datefin] = substr ($categorie_cadeau[datefin], 8, 2) . "/" . substr ($categorie_cadeau[datefin], 5, 2) . "/" . substr($categorie_cadeau[datefin], 0, 4) ;
					$categorie_cadeau[datefin] = "(jusqu'au $categorie_cadeau[datefin])" ;
					}
				else
					{
					$categorie_cadeau[datefin] = "" ;
					}
				switch ($categorie_cadeau[typecadeau])
					{
					case 0:
						$categorie_cadeau[typecadeau] = "€" ;
						break ;
					case 1:
						$categorie_cadeau[typecadeau] = "%" ;
						break ;
					}
				$contenu_onglet .= "<li>$categorie_cadeau[code] : Réduction de $categorie_cadeau[valeurcadeau] $categorie_cadeau[typecadeau] $categorie_cadeau[datefin]</li>" ;
				}
			}
		if ($DB_site2->num_rows($article_cadeaux) == 0 and $DB_site2->num_rows($categorie_cadeaux) == 0)
			{
			$contenu_onglet .= "Aucun bon de réduction ne concerne cet article ni son rayon." ;
			}
		$contenu_onglet .= 
				"</ul></td>
			</tr>
		</table></form>
		<a href=\"cadeau.php\" target=\"_blank\">Gérez vos bons de réduction ici</a><br><bR>" ;
		break ;
	case "uploads":
		$contenu_onglet .= "<h3>Uploads</h3>
		<font class=\"cost\">$erreur</font>
		<form name=\"formu\" method=\"post\" action=\"ajout_produit.php\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<table width=\"100%\" align=\"center\">
			<tr>
				<td width=\"100%\" align=\"center\"><strong>Uploads pour cet article</strong></td>
			</tr>
			<tr>
				<td width=\"100%\" align=\"center\"><br>" ;
		$contenu_onglet .= "<br><table class=\"table_coupscoeur\">";
		$uploads=$DB_site2->query_first("SELECT upload_images, upload_images_oblig, upload_comm, upload_comm_oblig
										FROM article where artid = '$artid'");
		if ($uploads[upload_images] > 0)
			{
			$nb_img = $uploads[upload_images] ;
			}
		if ($uploads[upload_comm] > 0)
			{
			$nb_comm = $uploads[upload_comm] ;
			}
		switch ($uploads[upload_images_oblig]) 
			{
			case '0':
				$checkedimg1 = "" ;
				$checkedimg2 = "checked" ;
			   break;
			case '1':
				$checkedimg1 = "checked" ;
				$checkedimg2 = "" ;
			   break;
			}		
		switch ($uploads[upload_comm_oblig]) 
			{
			case '0':
				$checkedcomm1 = "" ;
				$checkedcomm2 = "checked" ;
			   break;
			case '1':
				$checkedcomm1 = "checked" ;
				$checkedcomm2 = "" ;
			   break;
			}		
		$contenu_onglet .= "<tr>
				<td class=\"td_modules_boutiques\" align=\"left\"><em>Images</em></td>
				<td class=\"td_modules_boutiques\" align=\"right\">Nombre : </td>
				<td class=\"td_modules_boutiques\" align=\"left\"><input type=\"text\" name=\"nb_img\" value=\"$nb_img\" size=\"3\"></td>
				<td class=\"td_modules_boutiques\" align=\"left\"><input type=\"radio\" name=\"oblig_img\" value=\"1\" $checkedimg1> Obligatoire(s)</td>
				<td class=\"td_modules_boutiques\" align=\"left\"><input type=\"radio\" name=\"oblig_img\" value=\"0\" $checkedimg2> Facultative(s)</td>
			</tr>
			<tr>
				<td class=\"td_modules_boutiques\" align=\"left\"><em>Commentaires</em></td>
				<td class=\"td_modules_boutiques\" align=\"right\">Nombre : </td>
				<td class=\"td_modules_boutiques\" align=\"left\"><input type=\"text\" name=\"nb_comm\" value=\"$nb_comm\" size=\"3\"></td>
				<td class=\"td_modules_boutiques\" align=\"left\"><input type=\"radio\" name=\"oblig_comm\" value=\"1\" $checkedcomm1> Obligatoire(s)</td>
				<td class=\"td_modules_boutiques\" align=\"left\"><input type=\"radio\" name=\"oblig_comm\" value=\"0\" $checkedcomm2> Facultatif(s)</td>
			</tr>
			</table><br>";
		$contenu_onglet .= 
				"</td>
			</tr>
		</table></form>" ;
		break ;
	case "remises":
		$contenu_onglet .= "<h3>Remises de gros</h3>
		<font class=\"cost\">$erreur</font>
		<form name=\"formu\" method=\"post\" action=\"ajout_produit.php\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<table width=\"100%\" align=\"center\">
			<tr>
				<td width=\"100%\" align=\"center\"><strong>Remises concernant cet article</strong></td>
			</tr>
			<tr>
				<td width=\"100%\" align=\"left\"><br><ul>" ;
		// article
		$article_remises=$DB_site2->query("select * from remisearticle ra, remise r where ra.remiseid = r.remiseid and artid = '$artid'") ;
		if ($DB_site2->num_rows($article_remises) > 0)
			{
			while ($article_remise=$DB_site2->fetch_array($article_remises))
				{
				switch ($article_remise[typeremise])
					{
					case '0':
						$article_remise[typeremise] = "sur le prix" ;
						$article_remise[valeur] = "pour un montant supérieur à $article_remise[valeur] €" ;
						break ;
					case '1':
						$article_remise[typeremise] = "sur la quantité" ;
						$article_remise[valeur] = "pour une quantité supérieure à $article_remise[valeur] €" ;
						break ;
					case '2':
						$article_remise[typeremise] = "sur le poids" ;
						$article_remise[valeur] = "pour un poids supérieur à $article_remise[valeur] grammes" ;
						break ;
					}
				$contenu_onglet .= "<li>Remise $article_remise[typeremise] de $article_remise[pctremise] % $article_remise[valeur]</li>" ;
				}
			}
		// categorie
		$cats = $catid ;
		categories_parentes ($catid) ;
		$categorie_remises=$DB_site2->query("select * from remisecategorie rc, remise r where rc.remiseid = r.remiseid and catid in ($cats)") ;
		if ($DB_site2->num_rows($categorie_remises) > 0)
			{
			while ($categorie_remise=$DB_site2->fetch_array($categorie_remises))
				{
				switch ($categorie_remise[typeremise])
					{
					case '0':
						$categorie_remise[typeremise] = "sur le prix" ;
						$categorie_remise[valeur] = "pour un montant supérieur à $categorie_remise[valeur] €" ;
						break ;
					case '1':
						$categorie_remise[typeremise] = "sur la quantité" ;
						$categorie_remise[valeur] = "pour une quantité supérieure à $categorie_remise[valeur] €" ;
						break ;
					case '2':
						$categorie_remise[typeremise] = "sur le poids" ;
						$categorie_remise[valeur] = "pour un poids supérieur à $categorie_remise[valeur] grammes" ;
						break ;
					}
				$contenu_onglet .= "<li>Remise $categorie_remise[typeremise] de $categorie_remise[pctremise] % $categorie_remise[valeur]</li>" ;
				}
			}
		if ($DB_site2->num_rows($article_remises) == 0 and $DB_site2->num_rows($categorie_remises) == 0)
			{
			$contenu_onglet .= "Aucune remise ne concerne cet article ni son rayon." ;
			}
		$contenu_onglet .= 
				"</ul></td>
			</tr>
		</table></form>
		<a href=\"remise.php\" target=\"_blank\">Gérez vos remises ici</a><br><bR>" ;
		break ;
	case "liens":
		$lie=$DB_site2->query_first("select count(liensarticlesid) from liens_articles where article_lie = '$artid'");
		if (($lie[0] > 0 && $liensmultiples == 1) || $lie[0] == 0) {
			$contenu_onglet .= "<h3>Articles liés</h3>
			<font class=\"cost\">$erreur</font>
			<form name=\"formu\" method=\"post\" action=\"ajout_produit.php\">
			<input type=\"hidden\" name=\"old\" value=\"\">
			<input type=\"hidden\" name=\"action2\" value=\"\">
			<input type=\"hidden\" name=\"modif\" value=\"0\">
			<input type=\"hidden\" name=\"catid\" value=\"$catid\">
			<input type=\"hidden\" name=\"quitter\" value=\"\">
			<input type=\"hidden\" name=\"moncatid\" value=\"\">
			<input type=\"hidden\" name=\"artid\" value=\"$artid\">
			<table width=\"100%\" align=\"center\">
				<tr>
					<td width=\"100%\" align=\"center\">
			<strong>Cet article apparaît dans les rayons <font color=\"red\">en rouge</font></strong><br><bR><div align=\"left\">" ;
			
			$treeview.="<script><!--
				function toggle(id,p)
					{
					var myChild = document.getElementById(id);
					if(myChild.style.display!='block')
						{
						myChild.style.display='block';
						document.getElementById(p).className='folderOpen';
						}
					else
						{
						myChild.style.display='none';
						document.getElementById(p).className='folder';
						}
					}
					
				function changecolor(id)
					{
					var myChild = document.getElementById(id);
					if(myChild.style.color!='rgb(255, 0, 0)')
						{
						myChild.style.color='#FF0000';
						}
					else
						{
						myChild.style.color='#5F718A';
						}
					}
					
				function toggledemarrage(id,p)
					{
						var myChild = document.getElementById(id);
						myChild.style.display='block';
						document.getElementById(p).className='folderOpen';
					}
			--></script>";
	
			
			function construire_cat($parentid)
				{
				global $DB_site2,$treeview, $artid, $catid ;
				$cats=$DB_site2->query("select * from categorie where parentid = '$parentid' order by libelleFR asc");
				$treeview.="";
				while($cat=$DB_site2->fetch_array($cats)) // chaque categ de niveau 1
					{
					$txt = "" ;
					$case = "" ;
					$place = "" ;
					$arts_lies=$DB_site2->query("SELECT a.artid, a.libelleFR FROM liens_articles la, article a WHERE a.catid = '$cat[catid]'
					AND ((a.artid = la.article_base AND article_lie = '$artid') OR (a.artid = la.article_lie AND article_base = '$artid'))");
					while ($art_lie=$DB_site2->fetch_array($arts_lies))
						{
						$txt .= "<font color=\"#000044\"><strong>$art_lie[libelleFR] ($art_lie[artid])</strong></font>\n" ;
						$case = "<input type=\"checkbox\" name=\"chk_categories[$cat[catid]]\" value=\"$cat[catid]\" checked onchange=\"javascript:changecolor('C$cat[catid]');\">" ;
						}
					$sscats=$DB_site2->query("select * from categorie where parentid = '$cat[catid]' limit 1");
					if ($cat[catid] == $catid)
						{
						$place = "&nbsp;<font color=\"#000044\"><strong>Vous êtes ici</strong></font>\n" ;
						}
					elseif ($case == "")
						{
						$case = "<input type=\"checkbox\" name=\"chk_categories[$cat[catid]]\" value=\"$cat[catid]\" onchange=\"javascript:changecolor('C$cat[catid]');\">" ;
						}
					if($sscat=$DB_site2->fetch_array($sscats)) // chaque categ de niveau 2
						{
						if ($txt != "")
							{
							$treeview.="<li class=folder id=\"T$cat[catid]\"><a title=\"$cat[libelleFR]\" alt=\"$cat[libelleFR]\" href=\"Javascript: toggle('F$cat[catid]','T$cat[catid]');\" class=treeview><font style=\"color:#FF0000;\" id=\"C$cat[catid]\">$cat[libelleFR]</font></a>$case</li>\n<ul class=tree id=\"F$cat[catid]\">\n";
							construire_cat($cat[catid]);
							$treeview.="</ul>\n";
							}
						else
							{
							if ($place == "")
								{
								$treeview.="<li class=folder id=\"T$cat[catid]\"><a title=\"$cat[libelleFR]\" alt=\"$cat[libelleFR]\" href=\"Javascript: toggle('F$cat[catid]','T$cat[catid]');\" class=treeview><font style=\"color:#5F718A;\" id=\"C$cat[catid]\">$cat[libelleFR]</font></a>$case</li>\n<ul class=tree id=\"F$cat[catid]\">\n";
								construire_cat($cat[catid]);
								$treeview.="</ul>\n";
								}
							else
								{
								$treeview.="<li class=folder id=\"T$cat[catid]\"><a title=\"$cat[libelleFR]\" alt=\"$cat[libelleFR]\" href=\"Javascript: toggle('F$cat[catid]','T$cat[catid]');\" class=treeview><font color=\"red\">$cat[libelleFR]</font></a>$place</li>\n<ul class=tree id=\"F$cat[catid]\">\n";
								construire_cat($cat[catid]);
								$treeview.="</ul>\n";
								}
							}
						}
					else
						{
						if ($txt != "")
							{
							$treeview.="<li id=\"T$cat[catid]\"><font style=\"color:#FF0000;\" id=\"C$cat[catid]\">$cat[libelleFR]</font>$case</li>\n<ul class=tree id=\"F$cat[catid]\">\n</ul>\n";
							}
						else
							{
							if ($place == "")
								{
								$treeview.="<li id=\"T$cat[catid]\"><font style=\"color:#5F718A;\" id=\"C$cat[catid]\">$cat[libelleFR]</font>$case</li>\n";
								}
							else
								{
								$treeview.="<li id=\"T$cat[catid]\"><font color=\"red\">$cat[libelleFR]</font> $place</li>\n<ul class=tree id=\"F$cat[catid]\"></ul>\n";
								}
							}
						}
					}
				}
			
			function derouler($moncatid) 
				{
				global $DB_site2,$treeview ;
				$parentid=$DB_site2->query_first("select parentid from categorie where catid = '$moncatid'");
				$filleid=$DB_site2->query("select catid from categorie where parentid = '$moncatid'");
				$treeview .= "toggledemarrage('F$moncatid','T$moncatid') ;\n" ;
				if ($parentid[0] != "0")
					{
					derouler($parentid[0]);
					}
				}
			
			construire_cat(0);
			$treeview .= "<script language=\"javascript\">" ;
			$categs_qui_ont_des_articles_lies=$DB_site2->query("SELECT a.catid FROM liens_articles la, article a 
			WHERE ((a.artid = la.article_base AND article_lie = '$artid') OR (a.artid = la.article_lie AND article_base = '$artid'))");
			while ($categ_qui_a_des_articles_lies=$DB_site2->fetch_array($categs_qui_ont_des_articles_lies))
				{
				if ($categ_qui_a_des_articles_lies[0] != 0)
					{
					derouler($categ_qui_a_des_articles_lies[0]) ;
					}
				}
			derouler($catid) ;
			$treeview .= "</script>" ;
			
			$contenu_onglet .= $treeview ;
			$contenu_onglet .= "</div></td>
				</tr>
			</table>" ;
			}
		else {
			$contenu_onglet .= "
				<h3>Gestion des stocks</h3>
				<font class=\"cost\">$erreur</font>
				<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
				<input type=\"hidden\" name=\"old\" value=\"\">
				<input type=\"hidden\" name=\"quitter\" value=\"\">
				<input type=\"hidden\" name=\"action2\" value=\"\">
				<input type=\"hidden\" name=\"modif\" value=\"0\">
				<input type=\"hidden\" name=\"artid\" value=\"$artid\">
				<input type=\"hidden\" name=\"catid\" value=\"$catid\">
				<input type=\"hidden\" name=\"orderby\" value=\"\">
				<input type=\"hidden\" name=\"sens\" value=\"\">
				
				Cet article est déjà lié, vous ne pouvez le lier à nouveau.
				
				</form>
			" ;
			}
		break ;
	case "stocks":
		$lie=$DB_site2->query_first("select count(liensarticlesid) from liens_articles where article_lie = '$artid'");
		if (($lie[0] > 0 && $liensmultiples == 1) || $lie[0] == 0) {
			$lignes=$DB_site2->query_first("select count(*) from stocks WHERE artid = '$artid' and parentid != '0';");
			if ($lignes[0] > 0)
				$stock=$DB_site2->query_first("SELECT SUM(total) as totalst FROM stocks WHERE artid = '$artid' and parentid != '0'");
			else
				$stock=$DB_site2->query_first("SELECT total as totalst FROM stocks WHERE artid = '$artid' and parentid = '0'");
			$infos=$DB_site2->query_first("select seuil, delaiappro from stock WHERE artid = '$artid'");
	
			$contenu_onglet .= "
			<h3>Gestion des stocks</h3>
			<font class=\"cost\">$erreur</font>
			<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
			<input type=\"hidden\" name=\"old\" value=\"\">
			<input type=\"hidden\" name=\"quitter\" value=\"\">
			<input type=\"hidden\" name=\"action2\" value=\"\">
			<input type=\"hidden\" name=\"modif\" value=\"0\">
			<input type=\"hidden\" name=\"artid\" value=\"$artid\">
			<input type=\"hidden\" name=\"catid\" value=\"$catid\">
			<input type=\"hidden\" name=\"orderby\" value=\"\">
			<input type=\"hidden\" name=\"sens\" value=\"\">
			<table align=\"left\" width=\"100%\">
				<tr>
					<td>
						<table>
							<tr>
								<td align=\"left\">Stock total</td>
								<td align=\"left\">&nbsp;$stock[totalst]</td>
							</tr>
							<tr>
								<td align=\"left\">Seuil d'alerte</td>
								<td align=\"left\">&nbsp;<input type=\"text\" name=\"sa\" value=\"$infos[seuil]\" size=\"3\"></td>
								<td align=\"left\"></td>
							</tr>
							<tr>
								<td align=\"left\">Délai d'appro.</td>
								<td align=\"left\">&nbsp;<input type=\"text\" name=\"delai\" value=\"$infos[delaiappro]\" size=\"3\"></td>
								<td align=\"left\">(en nombre de jours)</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align=\"center\"><br>" ;
				
			//Afficher le filtre
			$filtre_query = $DB_site2->query("SELECT caracteristiquevaleur.$libelle as selval, caracteristique.$libelle as seltitle, caracteristiquevaleur.caractvalid, caracteristique.caractid FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) INNER JOIN caracteristique USING(caractid) INNER JOIN (SELECT caractid FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) INNER JOIN caracteristique USING(caractid) WHERE artid = '$artid' GROUP BY caractid HAVING(COUNT(*) > 1)) as cc USING(caractid) WHERE artid = '$artid' ORDER BY caracteristique.caractid, seltitle, selval");
						
			//construire le formulaire
			$caracteristique = "";
			$filtre_select = "";
			$where = "";
			while($filtre = $DB_site2->fetch_array($filtre_query)) {
					if($caracteristique != $filtre[seltitle]) {
						//debut de select
						$filtre_select .= ($filtre_select ? '</select>' : '')." $filtre[seltitle] <select name=\"filtrecaract[$filtre[caractid]]\" onchange=\"javascript:filtretable();\"><option value=\"\">Non filtré</option><option value=\"\">--------</option>";
						}
					$filtre_select .= "<option value=\"$filtre[caractvalid]\"".(isset($filtrecaract[$filtre[caractid]]) && $filtrecaract[$filtre[caractid]] == $filtre[caractvalid] ? ' selected' : '').">$filtre[selval]</option>";
					$caracteristique = $filtre[seltitle];
				}
			
			if($filtre_select)
				$contenu_onglet .=  " <a href=\"javascript:filtreempty();\"><img src=\"images/supprimer.gif\" align=\"absmiddle\" alt=\"Aucun Filtre\" title=\"Aucun Filtre\"></a> Filtre: ".$filtre_select."</select><br>";
	
			
			$contenu_onglet .=  "<br><br>";
			
			//effectuer la requete
			require_once("function_stock.php");
			if(isset($orderby)) {
				$stock_array = stock_retrieve($DB_site2, $artid, $libelle, $orderby, $sens);
				}
			else {
				$stock_array = stock_retrieve($DB_site2, $artid, $libelle);
				}

			$contenu_onglet .=  "<table class=\"table_stock_caract\">
					<tr>";
			
			$caract_array = current($stock_array);
			
			//Les caractéristiques et liens d'agencement
			foreach($caract_array as $key => $value) {
				if($key{0}.$key{1} == "cl") {
					$tri_key = "lb".substr($key, 2);
					$contenu_onglet .=  "<th><a href=\"javascript: tritable('$tri_key','".(isset($sens) && $sens == "ASC" && isset($orderby) && $orderby == $tri_key ? 'DESC' : 'ASC')."');\">$value</a></th>";
					}
				}
			$contenu_onglet .=  "<th class=\"table_stock_st\">Stock</th>";
			$contenu_onglet .=  "<th>MàJ</th>";
			$contenu_onglet .=  "<th class=\"table_stock_tt\">Total</th>";
			$contenu_onglet .=  "</tr>";
			
			//afficher le tableau
			$histo_labels = array();
			foreach($stock_array as $skey => $svalue) {
				$histo_label = array();
				$stock_label = "";
				foreach($svalue as $lkey => $lvalue) {
					if($lkey{0}.$lkey{1} == "lb") {
						$stock_label .= "<td>".$svalue[$lkey]."</td>";
						$histo_label[] = $svalue[$lkey];
						}
					}
				
				//construire de quoi afficher les historique
				if(isset($svalue['stockid']))
					$histo_labels[$svalue['stockid']] = implode(", ", $histo_label);
				
				//Filtre PHP
				$filtre_keys = explode('_', $skey);
				
				if(isset($filtrecaract) && sizeof($filtrecaract)) {
					//on a un filtre actif !
					$fdisp = false;
					foreach($filtrecaract as $fvalue)
						if(!in_array($fvalue, $filtre_keys)) {
							$fdisp = true;
							break;
							}
					}
				
				$contenu_onglet .= "\n<tr ".($fdisp ? "style=\"display:none\"" : '').">";
				$contenu_onglet .= $stock_label;
				$contenu_onglet .= "\n<td class=\"table_stock_st\"><input type=\"text\" value=\"".($svalue['value'] ? $svalue['value'] : 0)."\" readonly></td>";
				$contenu_onglet .= "\n<td><input type=\"text\" name=\"".(isset($svalue['stockid']) && $svalue['stockid'] ? "stockid[".$svalue['stockid']."]" : "stocknew[$skey]")."\" value=\"0\" onkeyup=\"javascript: updateStock('stockid$skey', ".($svalue['value'] ? $svalue['value'] : 0).", this);\"></td>";
				$contenu_onglet .= "\n<td class=\"table_stock_tt\"><input type=\"text\" id=\"stockid$skey\" value=\"".($svalue['value'] ? $svalue['value'] : 0)."\" readonly></td>";
				$contenu_onglet .= "\n</tr>";
				
				}
			
			$contenu_onglet .= "\n</table><br><input type=\"button\" value=\"Mettre à jour\" onclick=\"javascript:filtretable();\">";
			
			//Update Javascript
			$contenu_onglet .= '
			<script language="javascript" type="text/javascript">
				function updateStock(toup, val, inp) {
					if(isNaN(inp.value) && inp.value!="-")
						inp.value = inp.value.substr(0, inp.value.length-1);
					else
						{
						if (val+parseInt(inp.value) >= 0)
							document.getElementById(toup).value = val+parseInt(inp.value);
						else if (inp.value != "" && inp.value != "-")
							{
							inp.value = "0";
							alert(\'Le stock ne peut être négatif\');
							}
						}
				}
			</script>';
				
			//Afficher l'historique
			$contenu_onglet .= "<br><br><br>
			<strong>Historique des stocks</strong><br><br>
			<table class=\"table_remises_ajout\">
				<tr>
					<td align=\"center\"><strong>Date</strong></td>
					<td align=\"center\"><strong>Libelle</strong></td>
					<td align=\"center\"><strong>Stock</strong></td>
				</tr>" ;
			//requete de l'historique de stock
			$histos=$DB_site2->query("SELECT * FROM stock_historique INNER JOIN stocks USING(stockid) WHERE artid = '$artid' ORDER BY dateline DESC;");
			while ($histo=$DB_site2->fetch_array($histos))
				{
				$contenu_onglet .= "<tr>
						<td align=\"center\">".date("d/m/Y", $histo[dateline])."</td>
						<td align=\"left\">".$histo_labels[$histo['stockid']]."</td>
						<td align=\"right\">".($histo[delta] > 0 ? "<span style=\"color:green;\">+ ".$histo[delta]."</span>" : "<span style=\"color:red;\">- ".abs($histo[delta])."</span>")."</td>
					</tr>" ;
				}		
			$contenu_onglet .= "</table>
				</td>
				</tr>
			</table>" ;
			
			if (ereg("stocknew", $contenu_onglet))
				$contenu_onglet = "<br><br><br>
									<h3>Gestion des stocks</h3>
									<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
									<input type=\"hidden\" name=\"old\" value=\"\">
									<input type=\"hidden\" name=\"quitter\" value=\"\">
									<input type=\"hidden\" name=\"action2\" value=\"\">
									<input type=\"hidden\" name=\"artid\" value=\"$artid\">
									<input type=\"hidden\" name=\"catid\" value=\"$catid\">
									<input type=\"hidden\" name=\"orderby\" value=\"\">
									<input type=\"hidden\" name=\"sens\" value=\"\">
									Vous devez vérifier vos caracteristiques avant de saisir les stocks de cet article.
									</form><br><br><br>";
			}
		else {
			$art_base = article_base($artid);
			$contenu_onglet .= "<h3>Gestion des stocks</h3>
			<font class=\"cost\">$erreur</font>
			<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
			<input type=\"hidden\" name=\"old\" value=\"\">
			<input type=\"hidden\" name=\"quitter\" value=\"\">
			<input type=\"hidden\" name=\"action2\" value=\"\">
			<input type=\"hidden\" name=\"artid\" value=\"$artid\">
			<input type=\"hidden\" name=\"catid\" value=\"$catid\">
			<input type=\"hidden\" name=\"orderby\" value=\"\">
			<input type=\"hidden\" name=\"sens\" value=\"\">
			<table align=\"left\" width=\"100%\">
				<tr>
					<td align=\"center\">
						Il s'agit d'un article lié.<br><br>
						Se référer au sotck de l'article de base : <a href=\"ajout_produit.php?artid=$art_base&action2=stocks\" target=\"_blank\"><img src=\"images/voir.gif\" align=\"absmiddle\"></a>
					</td>
				</tr>
			</table>";							
			}
		break ;
	case "saisie":
		$contenu_onglet .= "<h3>Saisie d'informations et chargement de documents / images</h3>
		<font class=\"cost\">$erreur</font>
		<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"chp2\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"maliste\" value=\"\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<table align=\"left\" width=\"100%\">
			<tr>
				<td><br>Lorsqu'un visiteur commande cet article, il doit remplir les champs suivants :" ;

				$champs=$DB_site2->query("select articlechampid, position, libelleFR, ac.nom as nom, atc.nom as nomtype, obligatoire 
				from article_champ ac, article_type_champ atc where ac.type = atc.articletypechampid and artid != '$artid' order by position asc") ;
				$liste_champs = "" ;
				while ($champ=$DB_site2->fetch_array($champs))
					{
					switch ($champ[obligatoire])
						{
						case "0":
							$champ[obligatoire] = "" ;
							break ;
						case "1":
							$champ[obligatoire] = "Oblig." ;
							break ;
						}
					$liste_champs .= "<option value=\"$champ[articlechampid]\">$champ[articlechampid]:$champ[libelleFR] ($champ[nomtype] $champ[obligatoire])</option>" ;
					}
				$champs2=$DB_site2->query("select articlechampid, position, libelleFR, ac.nom as nom, atc.nom as nomtype, obligatoire 
				from article_champ ac, article_type_champ atc where ac.type = atc.articletypechampid and artid = '$artid' order by position asc") ;
				$liste_champs2 = "" ;
				while ($champ2=$DB_site2->fetch_array($champs2))
					{
					switch ($champ2[obligatoire])
						{
						case "0":
							$champ2[obligatoire] = "" ;
							break ;
						case "1":
							$champ2[obligatoire] = "Oblig." ;
							break ;
						}
					$liste_champs2 .= "<option value=\"$champ2[articlechampid]\">$champ2[articlechampid]:$champ2[libelleFR] ($champ2[nomtype] $champ2[obligatoire])</option>" ;
					}
				$contenu_onglet .= "
			<br>
			<br><br>
				<br><br>
				<table class=\"table_promos\" align=\"center\">
				<tr><td class=\"td_head_location\">Champs existants</td><td class=\"td_head_location\"> </td><td class=\"td_head_location\">Champs sélectionnés</td></tr>
				<tr>
					<td align=\"center\"><select name=\"liste1[]\" multiple ondblclick=\"Deplacer(this.form.elements['liste1[]'],this.form.elements['liste2[]'])\">$liste_champs</select></td>
					<td class=\"td_head_location\">
						<input type=\"button\" value=\"Enlever\" onClick=\"Deplacer(this.form.elements['liste2[]'],this.form.elements['liste1[]'])\"> <br><bR>
						<input type=\"button\" value=\"Ajouter\" onClick=\"Deplacer(this.form.elements['liste1[]'],this.form.elements['liste2[]'])\">
						<br><br><a href=\"Javascript: resize3($artid) ;\">Créer un champ</a>
					</td>
					<td align=\"center\" valign=\"top\">
						Position : <input name=\"Monter2\" type=\"button\" onClick=\"menuswitch(document.formu.liste2, -1);\" value=\"Monter\">&nbsp;&nbsp;
						<input name=\"Descendre2\" type=\"button\" onClick=\"menuswitch(document.formu.liste2, 1);\" value=\"Descendre\"><br><br>
						<select name=\"liste2[]\" id=\"liste2\" multiple ondblclick=\"Deplacer(this.form.elements['liste2[]'],this.form.elements['liste1[]'])\">$liste_champs2</select>
					</td>
				</tr>
			</table>
			</td>
			</tr>
		</table>
		" ;
		break ;
	case "pieces":
		$contenu_onglet .= "<h3>Ajouter des pièces jointes</h3>
		<font class=\"cost\">$erreur</font><br>
		<strong>Vous pouvez ici ajouter des documents qui apparaîtront dans la page de votre article.<br>
		Documents autorisés : Audio (mp3), Vidéo (wmv), PDF.</strong>
		<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\" enctype=\"multipart/form-data\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"del\" value=\"\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<table align=\"center\" width=\"100%\">
			<tr>
				<td colspan=\"2\">" ;
				$pieces=$DB_site2->query("SELECT articlepieceid, libelle, contenu, libelle, description FROM article_piece ap, 
										type_piece tp where ap.typepiece = tp.typepieceid and artid = '$artid'");
				if ($DB_site2->num_rows($pieces))
					{
					$contenu_onglet .= "<ul>" ;
					while ($piece=$DB_site2->fetch_array($pieces))
						{
						$piece[contenu] = "<a href=\"Javascript: supprimer_piece('$piece[articlepieceid]') ;\">Supprimer</a>" ;
						$contenu_onglet .= "<li>$piece[description] ($piece[libelle]) : $piece[contenu]</li>" ;
						}
					$contenu_onglet .= "</ul><br>" ;
					}
					$contenu_onglet .= 	"
				</td>
			</tr>
			<tr>
				<td align=\"left\">Type : </td>
				<td align=\"left\">
					&nbsp;<select name=\"type_piece\"><option value=\"0\"></option>" ;
					$types=$DB_site2->query("SELECT * FROM type_piece order by libelle asc");
					while ($type=$DB_site2->fetch_array($types))
						{
						$contenu_onglet .= "<option value=\"$type[typepieceid]\">$type[libelle]</option>" ;
						}
					$contenu_onglet .= "</select>
				</td>
			</tr>
			<tr>
				<td align=\"left\">Emplacement : </td>
				<td align=\"left\">&nbsp;<input type=\"file\" name=\"emplacement_piece\" value=\"\" size=\"36\"></td>
			</tr>
			<tr>
				<td align=\"left\">Texte de description : </td>
				<td align=\"left\">&nbsp;<input type=\"text\" name=\"description_piece\" value=\"\" size=\"50\" maxlength=\"50\"></td>
			</tr>
		</table>" ;
		break ;
	case "conseil":
		$contenu_onglet .= "
		<h3>Articles conseillés</h3>
		<font class=\"cost\">$erreur</font>
		<br><br>Articles complémentaires qui apparaîtront sous la description
		<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\" style=\"display:inline;\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">" ;
		$treeview.="<script><!--
			function toggle(id,p)
				{
				var myChild = document.getElementById(id);
				if(myChild.style.display!='block')
					{
					myChild.style.display='block';
					document.getElementById(p).className='folderOpen';
					}
				else
					{
					myChild.style.display='none';
					document.getElementById(p).className='folder';
					}
				}
				
			function changecolor(id)
				{
				var myChild = document.getElementById(id);
				if (myChild.style.color=='rgb(255, 0, 0)' || myChild.style.color=='#ff0000')
					{
					myChild.style.color='#5F718A';
					}
				else
					{
					myChild.style.color='#FF0000';
					}
				}
				
			function toggledemarrage(id,p)
				{
				var myChild = document.getElementById(id);
				myChild.style.display='block';
				document.getElementById(p).className='folderOpen';
				}
		--></script>";
		function construire_cat($parentid)
			{
			global $DB_site2,$treeview, $artid, $catid ;
			$cats=$DB_site2->query("select * from categorie where parentid = '$parentid' order by libelleFR asc");
			$treeview.="";
			while($cat=$DB_site2->fetch_array($cats)) // chaque categ de niveau 1
				{
				$txt = "" ;
				$tab_conseilles = array() ;
				$arts_conseilles=$DB_site2->query("SELECT * FROM article_conseil ac, article a WHERE a.catid = '$cat[catid]'
				AND a.artid = ac.artid_conseille AND ac.artid = '$artid'");
				while ($art_conseille=$DB_site2->fetch_array($arts_conseilles))
					{
					$txt = "ok" ;
					array_push($tab_conseilles, $art_conseille[artid_conseille]) ;
					}
				$sscats=$DB_site2->query("select * from categorie where parentid = '$cat[catid]' limit 1");
				if($sscat=$DB_site2->fetch_array($sscats)) // chaque categ de niveau 2
					{
					if ($txt != "")
						{
						$treeview.="<li class=folder id=\"T$cat[catid]\"><a title=\"$cat[libelleFR]\" alt=\"$cat[libelleFR]\" href=\"Javascript: toggle('F$cat[catid]','T$cat[catid]');\" class=treeview><font style=\"color:#FF0000;\" id=\"C$cat[catid]\">$cat[libelleFR]</font></a></li>\n<ul class=tree id=\"F$cat[catid]\">\n";
						$treeview .= "<ul class=\"tree2\">" ;
						$articles=$DB_site2->query("SELECT * FROM article WHERE catid = '$cat[catid]'");
						while ($article=$DB_site2->fetch_array($articles))
							{
							if ($article[artcode] != "")
								{
								$ref_article = "<i>Réf $article[artcode]</i> : ";
								}
								else
								{
								$ref_article = "";
								}
							$article[libelleFR] = substr($article[libelleFR], 0, 50) ;
							if ($article[artid] == $artid)
								{
								$treeview .= "<li class=\"art\">&nbsp;<font color=\"#000044\"><strong>$article[libelleFR]</strong></font></li>" ;
								}
							else 
								{
								if (in_array($article[artid], $tab_conseilles))
									{
									$treeview .= "<li class=\"art\"><input type=\"checkbox\" name=\"chk_articles[$article[artid]]\" value=\"$article[artid]\" onclick=\"javascript:changecolor('A$article[artid]');\" checked>$ref_article <font style=\"color:#FF0000;\" id=\"A$article[artid]\">$article[libelleFR]</font></li>" ;
									}
								else
									{
									$treeview .= "<li class=\"art\"><input type=\"checkbox\" name=\"chk_articles[$article[artid]]\" value=\"$article[artid]\" onclick=\"javascript:changecolor('A$article[artid]');\">$ref_article <font id=\"A$article[artid]\">$article[libelleFR]</font></li>" ;
									}
								}
							}
						$treeview.="</ul>" ;
						construire_cat($cat[catid]);
						$treeview.="</ul>\n";
						}
					else
						{
						$treeview.="<li class=folder id=\"T$cat[catid]\"><a title=\"$cat[libelleFR]\" alt=\"$cat[libelleFR]\" href=\"Javascript: toggle('F$cat[catid]','T$cat[catid]');\" class=treeview><font style=\"color:#5F718A;\" id=\"C$cat[catid]\">$cat[libelleFR]</font></a></li>\n<ul class=tree id=\"F$cat[catid]\">\n";
						$treeview .= "<ul class=\"tree2\">" ;
						$articles=$DB_site2->query("SELECT * FROM article WHERE catid = '$cat[catid]'");
						while ($article=$DB_site2->fetch_array($articles))
							{
							if ($article[artcode] != "")
								{
								$ref_article = "<i>Réf $article[artcode]</i> : ";
								}
								else
								{
								$ref_article = "";
								}							
							$article[libelleFR] = substr($article[libelleFR], 0, 50) ;
							if ($article[artid] == $artid)
								{
								$treeview .= "<li class=\"art\">&nbsp;<font color=\"#000044\"><strong>$article[libelleFR]</strong></font></li>" ;
								}
							else 
								{
								if (in_array($article[artid], $tab_conseilles))
									{
									$treeview .= "<li class=\"art\"><input type=\"checkbox\" name=\"chk_articles[$article[artid]]\" value=\"$article[artid]\" onclick=\"javascript:changecolor('A$article[artid]');\" checked>$ref_article <font style=\"color:#FF0000;\" id=\"A$article[artid]\">$article[libelleFR]</font></li>" ;
									}
								else
									{
									$treeview .= "<li class=\"art\"><input type=\"checkbox\" name=\"chk_articles[$article[artid]]\" value=\"$article[artid]\" onclick=\"javascript:changecolor('A$article[artid]');\">$ref_article <font id=\"A$article[artid]\">$article[libelleFR]</font></li>" ;
									}
								}
							}
						$treeview.="</ul>" ;
						construire_cat($cat[catid]);
						$treeview.="</ul>\n";
						}
					}
				else
					{
					if ($txt != "")
						{
						$treeview.="<li id=\"T$cat[catid]\"><font style=\"color:#000044;\" id=\"C$cat[catid]\">$cat[libelleFR]</font></li>\n<ul class=tree id=\"F$cat[catid]\">\n</ul>\n";
						}
					else
						{
						$treeview.="<li id=\"T$cat[catid]\"><font style=\"color:#5F718A;\" id=\"C$cat[catid]\">$cat[libelleFR]</font></li>\n";
						}
					$treeview .= "<ul class=\"tree2\">" ;
					$articles=$DB_site2->query("SELECT * FROM article WHERE catid = '$cat[catid]'");
					while ($article=$DB_site2->fetch_array($articles))
						{
						if ($article[artcode] != "")
							{
							$ref_article = "<i>Réf $article[artcode]</i> : ";
							}
							else
							{
							$ref_article = "";
							}													
						$article[libelleFR] = substr($article[libelleFR], 0, 50) ;
						if ($article[artid] == $artid)
							{
							$treeview .= "<li class=\"art\">&nbsp;<font color=\"#000044\"><strong>$article[libelleFR]</strong></font></li>" ;
							}
						else 
							{
							if (in_array($article[artid], $tab_conseilles))
								{
								$treeview .= "<li class=\"art\"><input type=\"checkbox\" name=\"chk_articles[$article[artid]]\" value=\"$article[artid]\" onclick=\"javascript:changecolor('A$article[artid]');\" checked>$ref_article <font style=\"color:#FF0000;\" id=\"A$article[artid]\">$article[libelleFR]</font>".($article[commandable]==0?" <font color=\"red\">(invisible)</font>":"")."</li>" ;
								}
							else
								{
								$treeview .= "<li class=\"art\"><input type=\"checkbox\" name=\"chk_articles[$article[artid]]\" value=\"$article[artid]\" onclick=\"javascript:changecolor('A$article[artid]');\">$ref_article <font id=\"A$article[artid]\">$article[libelleFR]</font>".($article[commandable]==0?" <font color=\"red\">(invisible)</font>":"")."</li>" ;
								}
							}
						}
					$treeview .= "</ul>" ;
					}
				}
			}
		
		function derouler($moncatid) 
			{
			global $DB_site2,$treeview ;
			$parentid=$DB_site2->query_first("select parentid from categorie where catid = '$moncatid'");
			$filleid=$DB_site2->query("select catid from categorie where parentid = '$moncatid'");
			$treeview .= "toggledemarrage('F$moncatid','T$moncatid') ;\n" ;
			if ($parentid[0] != "0" and $parentid[0] != "12")
				{
				derouler($parentid[0]);
				}
			}
		
		$contenu_onglet .= "<div align=\"left\" style=\"margin:25px;\"><ul>$treeview</ul></div>" ;
		break ;
	case "rangements":
		include("article_rangements.php");

		break ;
	case "rangements_nb":
		$contenu_onglet .= "
		<h3>Rangements associés</h3>
		<font class=\"cost\">$erreur</font>
		<br><br>Nombre de rangements complémentaires qui apparaîtront sous la description
		<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\" style=\"display:inline;\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<ul>";
		$rangements = $DB_site2->query("SELECT * FROM article_rangement ar INNER JOIN article a ON (a.artid = ar.artid_rangement) WHERE ar.artid = '$artid'");
		if ($DB_site2->num_rows($rangements) > 0)
			{
			while ($rangement=$DB_site2->fetch_array($rangements))
				$contenu_onglet .= "<li>$rangement[$libelle] : <input type=\"text\" name=\"nb_rangements[$rangement[artid_rangement]]\" value=\"$rangement[nombre]\" size=\"2\"><br></li>";
			}
			else
			{
			$contenu_onglet .= "<li>Aucun rangement complémentaire pour cet article</li>";
			}
		$contenu_onglet .= "
		</ul></form>" ;
		break ;	
	case "equipements":
		$contenu_onglet .= "
		<h3>Equipements</h3>
		<font class=\"cost\">$erreur</font>
		<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"mq2\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<table width=\"90%\" align=\"center\">
		<tr>
		<td align=\"left\">Système d'hygrométrie : </td>
		<td align=\"left\">&nbsp;".selecttranslate("hygrometrie","/article/hygrometrie",$hygrometrie)."</td>
		</tr>
		<tr>
		<td align=\"left\">Hygromètre : </td>
		<td align=\"left\">&nbsp;".selecttranslate("hygrometre","/article/hygrometre",$hygrometre)."</td>
		</tr>
		<tr>
		<td align=\"left\">Filtres à charbon : </td>
		<td align=\"left\">&nbsp;".selecttranslate("filtre_charbon","/article/filtre_charbon",$article[filtre_charbon])."</td>
		</tr>
		<tr>
		<td align=\"left\">Pieds vérin réglables : </td>
		<td align=\"left\">&nbsp;".selecttranslate("pieds_reglables","/article/pieds_reglables",$pieds_reglables)."</td>
		</tr>
		<tr>
		<td align=\"left\">Serrure :<br><small>Pour la recherche</small></td>
		<td align=\"left\">&nbsp;<select name=\"serrure_info\">".$opt_serrure."</select></td>
		</tr>
		<tr>
		<td align=\"left\">Serrure texte : </td>
		<td align=\"left\">&nbsp;".selecttranslate("serrure","/article/serrure",$serrure)."</td>
		</tr>
		<tr>
		<td align=\"left\">Poignée : </td>
		<td align=\"left\">&nbsp;".selecttranslate("poignee","/article/poignee",$poignee)."</td>
		</tr>
		<tr>
		<td align=\"left\">Modularité intérieure : </td>
		<td align=\"left\">&nbsp;".selecttranslate("modularite_interieure","/article/modularite_interieure",$modularite_interieure)."</td>
		</tr>
		<tr>
		<td align=\"left\">Interrupteur : Marche / Arret : </td>
		<td align=\"left\">&nbsp;".selecttranslate("interrupteur","/article/interrupteur",$interrupteur)."</td>
		</tr>		
		<tr>
		<td align=\"left\">Eclairage : </td>
		<td align=\"left\">&nbsp;".selecttranslate("eclairage","/article/eclairage",$eclairage)."</td>
		</tr>		
		<tr>
		<td align=\"left\">Bouton Test : </td>
		<td align=\"left\">&nbsp;".selecttranslate("bouton_test","/article/bouton_test",$bouton_test)."</td>
		</tr>		
		<tr>
		<td align=\"left\">Roulettes : </td>
		<td align=\"left\">&nbsp;".selecttranslate("roulettes","/article/roulettes",$roulettes)."</td>
		</tr>
		<tr>
		<td align=\"left\">Alarme : </td>
		<td align=\"left\">&nbsp;".selecttranslate("alarme","/article/alarme",$article['alarme'])."</td>
		</tr>
		<tr>
		<td align=\"left\">Sens de la porte : </td>
		<td align=\"left\">&nbsp;<select name=\"sensporte\" onchange=\"setmodif();\">".$optsensporte."</select></td>
		</tr>
		<!--<tr>
		<td align=\"left\">Vide poche : </td>
		<td align=\"left\">&nbsp;<input type=\"text\" name=\"vide_poche\" value=\"$vide_poche\" onchange=\"setmodif();\"></td>
		</tr>		
		<tr>
		<td align=\"left\">Clayette fil (partie basse) : </td>
		<td align=\"left\">&nbsp;<input type=\"text\" name=\"clayette_fil\" value=\"$clayette_fil\" onchange=\"setmodif();\"></td>
		</tr>		
		<tr>
		<td align=\"left\">Panier de transport : </td>
		<td align=\"left\">&nbsp;<input type=\"text\" name=\"panier_transport\" value=\"$panier_transport\" onchange=\"setmodif();\"></td>
		</tr>		
		<tr>
		<td align=\"left\">Vinokit : </td>
		<td align=\"left\">&nbsp;<input type=\"text\" name=\"vinokit\" value=\"$vinokit\" onchange=\"setmodif();\"></td>
		</tr>		
		<tr>
		<td align=\"left\">Tablette sous clayette centrale : </td>
		<td align=\"left\">&nbsp;<input type=\"text\" name=\"tablette\" value=\"$tablette\" onchange=\"setmodif();\"></td>
		</tr>		
		<tr>
		<td align=\"left\">Clayette en hêtre en demi profondeur : </td>
		<td align=\"left\">&nbsp;<input type=\"text\" name=\"clayette_hetre\" value=\"$clayette_hetre\" onchange=\"setmodif();\"></td>
		</tr>		
		<tr>
		<td align=\"left\">Tapis en partie basse : </td>
		<td align=\"left\">&nbsp;<input type=\"text\" name=\"tapis_basse\" value=\"$tapis_basse\" onchange=\"setmodif();\"></td>
		</tr>-->	
		</table>
		</form>" ;
		break ;	
	case "regulation":
		$contenu_onglet .= "
		<h3>Régulation</h3>
		<font class=\"cost\">$erreur</font>
		<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"mq2\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<table width=\"90%\" align=\"center\">
		<tr>
		<td align=\"left\">Nombre de températures : </td>
		<td align=\"left\">&nbsp;".selecttranslate("nbr_temperature","/article/nbr_temperature",$article[nbr_temperature])."</td>
		</tr>
		<tr>
		<td align=\"left\">Plage de réglage de la températures : </td>
		<td align=\"left\">&nbsp;".selecttranslate("plage_temperature","/article/plage_temperature",$article[plage_temperature])."</td>
		</tr>
		<tr>
		<td align=\"left\">Régulation : </td>
		<td align=\"left\">&nbsp;".selecttranslate("regulation","/article/regulation",$regulation)."</td>
		</tr>
		<tr>
		<td align=\"left\">Boutons de réglage : </td>
		<td align=\"left\">&nbsp;".selecttranslate("boutons_reglage","/article/boutons_reglage",$boutons_reglage)."</td>
		</tr>
		<tr>
		<td align=\"left\">Réglage : </td>
		<td align=\"left\">&nbsp;".selecttranslate("reglage","/article/reglage",$reglage)."</td>
		</tr>
		<tr>
		<td align=\"left\">Affichage : </td>
		<td align=\"left\">&nbsp;".selecttranslate("affichage","/article/affichage",$affichage)."</td>
		</tr>
		<tr>
		<td align=\"left\">Température : </td>
		<td align=\"left\">&nbsp;".selecttranslate("temperature","/article/temperature",$temperature)."</td>
		</tr>
		<tr>
		<td align=\"left\">Thermomètre : </td>
		<td align=\"left\">&nbsp;".selecttranslate("thermometre","/article/thermometre",$thermometre)."</td>
		</tr>		
		</table>
		</form>" ;
		break ;	
	case "emballage":
	      include("article_dimensions.php");
	      break ;

	case "recherche":
		$contenu_onglet .= "<font class=\"cost\">$erreur</font>
		<h3>Usage</h3>
		<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"mq2\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<table width=\"50%\" align=\"center\">";
		$listeusages = "" ;
		$rechercheusages=$DB_site2->query("SELECT * FROM recherche_usage ORDER BY rechercheusageid");
		while ($rechercheusage=$DB_site2->fetch_array($rechercheusages)){
			if($rechercheusage['rechercheusageid']==5)
			    $listeusages.="<tr><td colspan=\"2\" align=\"center\"><h3>Type de pose</h3></td></tr>";
			else
			    $listeusages .= "<tr> <td> <input type=\"checkbox\" name=\"usage$rechercheusage[rechercheusageid]\" ".${'usagechecked'.$rechercheusage[rechercheusageid]}." onclick=\"setmodif()\"></td> <td>$rechercheusage[nom]</td></tr>" ;
		}
		
		$contenu_onglet .= "$listeusages</table>" ;
	
		$contenu_onglet .= "
		<h3>Type porte</h3>
		<table width=\"50%\" align=\"center\">";
		$listetypeporte = "" ;
		$recherchetypesporte=$DB_site2->query("SELECT * FROM recherche_typeporte");
		while ($recherchetypeporte=$DB_site2->fetch_array($recherchetypesporte))
			$listetypeporte .= "<tr> <td> <input type=\"checkbox\" name=\"typeporte$recherchetypeporte[recherchetypeporteid]\" ".${'portechecked'.$recherchetypeporte[recherchetypeporteid]}." onclick=\"setmodif()\"></td> <td>$recherchetypeporte[nom]</td></tr>" ;
		
		$contenu_onglet .= "$listetypeporte</table>" ;
	
		$contenu_onglet .= "
		<h3>Coloris</h3>
		<table width=\"50%\" align=\"center\">";
		$listecoloris = "" ;
		$recherchecoloriss=$DB_site2->query("SELECT * FROM recherche_coloris");
		while ($recherchecoloris=$DB_site2->fetch_array($recherchecoloriss))
			$listecoloris .= "<tr> <td> <input type=\"checkbox\" name=\"coloris$recherchecoloris[recherchecolorisid]\" ".${'colorischecked'.$recherchecoloris[recherchecolorisid]}." onclick=\"setmodif()\"></td> <td>$recherchecoloris[nom]</td></tr>" ;
		
		$contenu_onglet .= "$listecoloris</table>" ;
	
		$article=$DB_site2->query_first("SELECT esthetique FROM article_cave WHERE artid=".$artid);
		$contenu_onglet.="
		<h3>Esthétique</h3>
		<div style=\"width:50%; margin:auto; text-align:left;\">
		  <input type=\"checkbox\" name=\"esthetique\" value=\"1\" onclick=\"setmodif()\"".($article['esthetique']==1?" checked":"")."> Produit esthétique
		  <br><small>Pour installer dans une pièce à vivre (cuisine, salon, salle à manger, salle de restaurant)</small>
		</div>";
	
		$contenu_onglet .= "
		<h3>Mode rangement</h3>
		<table width=\"50%\" align=\"center\">";
		$listerangement = "" ;
		$rechercherangements=$DB_site2->query("SELECT * FROM recherche_moderangement");
		while ($rechercherangement=$DB_site2->fetch_array($rechercherangements))
			$listerangement .= "<tr> <td> <input type=\"checkbox\" name=\"rangement$rechercherangement[recherchemoderangementid]\" ".${'modechecked'.$rechercherangement[recherchemoderangementid]}." onclick=\"setmodif()\"></td> <td>$rechercherangement[nom]</td></tr>" ;
		
		$contenu_onglet .= "$listerangement</table>" ;

	
		$contenu_onglet .= "
		<h3>Emplacement</h3>
		<table width=\"50%\" align=\"center\">";
		$listeemplacement = "" ;
		$rechercheemplacements=$DB_site2->query("SELECT * FROM recherche_emplacement");
		while ($rechercheemplacement=$DB_site2->fetch_array($rechercheemplacements))
			$listeemplacement .= "<tr> <td> <input type=\"checkbox\" name=\"emplacement$rechercheemplacement[rechercheemplacementid]\" ".${'emplacementchecked'.$rechercheemplacement[rechercheemplacementid]}." onclick=\"setmodif()\"></td> <td>$rechercheemplacement[nom]</td></tr>" ;
		
		$contenu_onglet .= "$listeemplacement</table>";

		$contenu_onglet.="</form>" ;
		break ;
	case "performances":
		include("article_performances.php");
		break ;
	case "technologies":
		$contenu_onglet .= "
		<h3>Technologies chaud - froid - isolation</h3>
		<font class=\"cost\">$erreur</font>
		<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"mq2\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<table width=\"90%\" align=\"center\">
		<tr>
		<td align=\"left\">Absence de vibration : </td>
		<td align=\"left\">&nbsp;".selecttranslate("absence_vibration","/article/absence_vibration",$article[absence_vibration])."</td>
		</tr>	
		<tr>
		<td align=\"left\">Production de froid : </td>
		<td align=\"left\">&nbsp;".selecttranslate("production_froid","/article/production_froid",$production_froid)."</td>
		</tr>
		<tr>
		<td align=\"left\">Production de chaud : </td>
		<td align=\"left\">&nbsp;".selecttranslate("production_chaud","/article/production_chaud",$production_chaud)."</td>
		</tr>
		<tr>
		<td align=\"left\">Déclenchement du chaud : </td>
		<td align=\"left\">&nbsp;".selecttranslate("declenchement_chaud","/article/declenchement_chaud",$declenchement_chaud)."</td>
		</tr>
		<tr>
		<td align=\"left\">Fonction hiver : </td>
		<td align=\"left\">&nbsp;".selecttranslate("fonction_hiver_techno","/article/fonction_hiver_techno",$fonction_hiver_techno)."</td>
		</tr>
		<tr>
		<td align=\"left\">Nature des parois latérales intérieures : </td>
		<td align=\"left\">&nbsp;".selecttranslate("parois_laterales","/article/parois_laterales",$parois_laterales)."</td>
		</tr>
		<tr>
		<td align=\"left\">Nature de la paroi du fond intérieur : </td>
		<td align=\"left\">&nbsp;".selecttranslate("parois_fond","/article/parois_fond",$parois_fond)."</td>
		</tr>		
		<tr>
		<td align=\"left\">Nature du revêtement extérieur : </td>
		<td align=\"left\">&nbsp;".selecttranslate("revetement_exterieur","/article/revetement_exterieur",$revetement_exterieur)."</td>
		</tr>		
		<tr>
		<td align=\"left\">Nature de l'isolation : </td>
		<td align=\"left\">&nbsp;".selecttranslate("isolation","/article/isolation",$isolation)."</td>
		</tr>	
		<tr>
		<td align=\"left\">Gaz : </td>
		<td align=\"left\">&nbsp;".selecttranslate("gaz","/article/gaz",$article['gaz'])."</td>
		</tr>
		<tr>
		<td align=\"left\">Type de ventilation : </td>
		<td align=\"left\">&nbsp;".selecttranslate("type_ventilation","/article/type_ventilation",$article['type_ventilation'])."</td>
		</tr>	
		</table></form>" ;
		break ;
	case "nosconseils":
	      include("article_nosconseils.php");
	break;
	case "garantie_incluse":
		$contenu_onglet .= "
		<h3>Garantie incluse</h3>
		<font class=\"cost\">$erreur</font>
		<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"mq2\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<table width=\"90%\" align=\"center\">
		<tr>
		<td align=\"left\">Garantie incluse : </td>
		<td align=\"left\">&nbsp;<textarea name=\"garantie_incluse\" onchange=\"setmodif();\" cols=\"60\">$garantie_incluse</textarea></td>
		</tr>
		</table>
		</form>" ;
		break ;
	case "similaire":	
		
		$contenu_onglet.="<h3>Articles même gamme</h3>
		<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"mq2\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<input type=\"hidden\" name=\"removegid\" value=\"0\">
		<input type=\"hidden\" name=\"gartid\" value=\"0\">";
		
		$a=new article($DB_site2);
		$a->load($artid);
		$sims=$a->similaire();
  		$contenu_onglet.="<p>Les articles de la même gamme sont des produits de dimensions différentes dans la même gamme</p>";
		if($sims==array()) $contenu_onglet.="<p>Cet article n'a pas d'articles associés</p>
		<p>Ajouter un article<input type=\"text\" onkeyup=\"srcsimilaire(this.value)\"></p>
		<div id=\"results\" style=\"text-align:left;\"></div>";

		$vistxt=array(0=>"invisible",1=>"visible",2=>"supprimé");
		foreach($sims as $gid=>$artids){
		    foreach($artids as $gartid){
			$ga=$DB_site2->query_first("SELECT * FROM article WHERE artid=".$gartid);
			$contenu_onglet.="<div style=\"float:left; border:solid 1px #888; margin-right:4px; width:200px; height:150px;\">".vignette($ga)."</div>";
		    }
		    $contenu_onglet.="<div style=\"clear:both;\"></div><a href=\"javascript:document.getElementsByName('removegid')[0].value=$gid;clique('similaire','similaire');\"><img src=\"images/supprimer.gif\" align=\"absmiddle\"> Désassocier de ce groupe</a>";
		}
		$contenu_onglet.="</form>";
	break;
	case "cross":
		include("article_cross.php");
	break;
	case "cross2":
		include("article_cross2.php");
	break;
	case "vcat":
		include("article_vcat.php");
	break;

	case "stat":
		$article=$DB_site2->query_first("SELECT artcode FROM article WHERE artid=".$artid);
		$contenu_onglet .= "
		<h3>Historique des modifications</h3>
		<font class=\"cost\">$erreur</font>
		<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
		<input type=\"hidden\" name=\"old\" value=\"\">
		<input type=\"hidden\" name=\"quitter\" value=\"\">
		<input type=\"hidden\" name=\"mq2\" value=\"\">
		<input type=\"hidden\" name=\"action2\" value=\"\">
		<input type=\"hidden\" name=\"modif\" value=\"0\">
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">";
		
		$count=$DB_site2->query_first("SELECT SUM(lf.qte) AS qte,SUM(lf.qte*lf.prix/(1+lf.tva/100)) AS total FROM lignefacture lf INNER JOIN facture f ON (f.facturid=lf.factureid) INNER JOIN article a ON (a.artcode=lf.artcode) WHERE f.valide=5 AND a.artid=$artid");
		$count0=$DB_site2->query_first("SELECT SUM(lf.qte) AS qte,SUM(lf.qte*lf.prix/(1+lf.tva/100)) AS total FROM lignefacture lf INNER JOIN facture f ON (f.facturid=lf.factureid) INNER JOIN article a ON (a.artcode=lf.artcode) WHERE f.valide=0 AND a.artid=$artid");
		$contenu_onglet.="Nombre de devis : ".$count0['qte']." <a href=\"facture.php?action=article&ref=$article[0]\">Voir les devis</a><br>Nombre de ventes : ".$count['qte']." <a href=\"facture.php?action=article&valid=1&ref=$article[0]\">Voir les commandes</a><br>Total HT : ".number_format($count['total'],2,',','')." &euro;";
		include("article_history.php");
	break;
      
	case "lot":
		include("article_lot.php");
	break;
	
	case "packs":
		include("article_packs.php");
	break;

	case "offres":
		include("article_offres.php");
	break;

	case "accessoires":
	$article=$DB_site2->query_first("SELECT * FROM article WHERE artid=".$artid);
	include("article_accessoires.php");
	break;

	case "origine":
		include("article_origine.php");
	break;

	case "capacite":
		include("article_capacite.php");
	break;

	case "mcid":
	$contenu_onglet .= "
	<h3>Attributs RDC </h3>
	<font class=\"cost\">$erreur</font>
	
	<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\" style=\"display:inline;\">
	<input type=\"hidden\" name=\"old\" value=\"\">
	<input type=\"hidden\" name=\"quitter\" value=\"\">
	<input type=\"hidden\" name=\"action2\" value=\"\">
	<input type=\"hidden\" name=\"catid\" value=\"$catid\">
	<input type=\"hidden\" name=\"artid\" value=\"$artid\">
	<ul>";
	
	
	
	$rueducommerce = $DB_site2->query_first("SELECT MCID FROM rueducommerce WHERE artid = '$artid'");
	
	
	$attributs = $DB_site2->query("SELECT * FROM rueducommerce_attribut WHERE MCID = '$rueducommerce[MCID]' ORDER BY attributid");
	if ($DB_site2->num_rows($attributs) > 0)
		{
		$contenu_onglet .= "<b>Liste des attributs associés au MCID <i>$rueducommerce[MCID]</i> </b><br><i>Veuillez vous référer aux documents RueDuCommerce pour savoir quels attributs sont obligatoires ou facultatifs.</i>";
		$contenu_onglet .= "<div align=\"left\">";
		$contenu_onglet .= "<ul class=\"arbo\">";
		while ($attribut=$DB_site2->fetch_array($attributs))
			{
			$attributArt = $DB_site2->query_first("SELECT valeur FROM article_attribut WHERE artid = '$artid' AND attributid = '$attribut[attributid]'");
			$attributArt[valeur] = stripslashes($attributArt[valeur]);
			$contenu_onglet .= "<li> $attribut[libelle] : <input type=\"text\" name=\"chk_attributs[$attribut[attributid]]\" value=\"$attributArt[valeur]\"></li>";
			}
		$contenu_onglet .= "</ul>";	
		$contenu_onglet .= "</div>";
		}
	else
		$contenu_onglet .= "Aucun attribut à saisir pour ce MCID ";	
	
	
	
	$contenu_onglet .= "
	</ul></form>" ;
	break ;		

	case "amazon":
	case "rdc":
	case "Priceminister":
	case "google":
	case "windil":
	case "pixmania":
	$mp=$action2;
	include("ajout_produit_mp.php");
	break;
	
	case "history":
		include("ajout_produit_history.php");
	break;
	
	}


echo (ongletcontenu($contenu_onglet) );
article_action($disp_tabs, $action2, 'show');	
// fonctions js	
?>
<SCRIPT LANGUAGE="JavaScript">
function filtretable() {
	document.formu.action2.value = 'stocks';
	document.formu.old.value = 'stocks';
	document.formu.submit();
}
function filtreempty() {
	document.formu.action2.value = 'stocks';
	document.formu.old.value = 'stocks';
	toempty = document.formu.elements;
	for(i = 0 ; i < toempty.length ; i++)
		if(toempty[i].type == "select-one")
			toempty[i].selectedIndex = 0;
	document.formu.submit();
}
function tritable(order,sens) {
	document.formu.action2.value = 'stocks';
	document.formu.old.value = 'stocks';
	document.formu.orderby.value = order;
	document.formu.sens.value = sens;
	document.formu.submit() ;
}

function clique(action_envoyee,ancienne_action)
	{
	if (ancienne_action == 'saisie')
		{
		elementSelect = document.getElementById('liste2');
		for (var i = 0 ; i < elementSelect.options.length ; i++) 
			{
			elementSelect.options[i].selected=true; 
			}		
			document.formu.elements.liste2.name = 'liste2[]';
		}
	document.formu.action2.value=action_envoyee;
	document.formu.old.value=ancienne_action;
	document.formu.submit() ;
	}

function changelangue(nouvelle,navig) 
	{
	document.formu.action2.value = 'desc' ;
	document.formu.old.value = 'desc' ;
	document.formu.autrelang.value = nouvelle ;
	document.formu.submit () ;
	}
function terminer (quitte,navig) 
	{
	if (quitte == 'saisie')
		{
		elementSelect = document.getElementById('liste2');
		for (var i = 0 ; i < elementSelect.options.length ; i++) 
			{
			elementSelect.options[i].selected=true; 
			}		
			document.formu.elements.liste2.name = 'liste2[]';
		}
	if (quitte == 'img')
		{ 
		/*alert("localhost.ma-cave-a-vin.fr/admin/ajout_produit.php?artid=<?echo $_POST[artid]; ?>&prec=modif");*/
		window.location.href="./articles.php";
		exit();
		}
	
	document.formu.action2.value = quitte ;
	document.formu.old.value = quitte ;
	document.formu.quitter.value = '1' ;
	document.formu.submit () ;
	}

// document.body.onclick=hidetranslate;

createfb();

<?php if(isset($article)) print "document.title=\"".$article['artcode']." (".addslashes($article['artcode_eurocave']).")\";"; ?>

</SCRIPT>
<?php	
// Modif TSI pour afficher les param envoyés ... et ceux possibles
// echo $action2." / ".$browser." : ".$_POST[artid]." / ".$_POST[prec]." ... ".$_POST[marqueid];

// Ajout du bouton sauvegarder, qui fait "reafficher" l'onglet courant et provoque la sauvegarde (idem que pour le changement d'onglet)
echo "
	<b>Naviguez entre les onglets ou cliquez sur sauvegarder pour enregistrer les modifications.<br>Cliquez sur 'Terminer' pour quitter la page.</b><br><br>
	<input type=\"button\" value=\"Terminer\" onclick=\"Javascript: terminer ('$action2','$browser') ;\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type=\"button\" value=\"Sauvegarder\" onclick=\"Javascript: clique ('$action2','$action2') ;\">
	<br><br>
</td>
   <td background=\"images2/cadre_r4_c10.gif\" width=\"3\" height=\"100%\" border=\"0\" alt=\"\"></td>
  </tr>
</table>
";
	  
include "./footer.php";

// fonction php images
function redimentionner_image ($nom_fichier, $destination, $largeur, $hauteur)
	{
	if (substr ($destination, -3) == "jpg") 
		{
		$imagetmp = imagecreatefromjpeg ($nom_fichier) ;
		}
	if (substr ($destination, -3) == "gif") 
		{
		$imagetmp = imagecreatefromgif ($nom_fichier) ;
		}
	if (substr ($destination, -3) == "png") 
		{
		$imagetmp = imagecreatefrompng ($nom_fichier) ;
		}
	$imagesize = getimagesize ($nom_fichier) ;
	if ($imagesize[0] > $largeur or $imagesize[1] > $hauteur)
		{
		$largeur_en_cours = $imagesize[0] ;
		$hauteur_en_cours = $imagesize[1] ;
		if ($largeur_en_cours > $largeur) // si largeur > 112
			{
			$width = $largeur ; // largeur = 112
			$height = floor ($largeur * $hauteur_en_cours / $largeur_en_cours) ; // hauteur = 112 * (768/1024)
			$largeur_en_cours = $width ;
			$hauteur_en_cours = $height ;
			} 
		if ($hauteur_en_cours > $hauteur) 
			{
			$width = floor($largeur_en_cours * $hauteur / $hauteur_en_cours) ;
			$height = $hauteur ;
			$largeur_en_cours = $width ;
			$hauteur_en_cours = $height ;
			}
		$image = imagecreatetruecolor ($largeur_en_cours, $hauteur_en_cours) ;
		imagecopyresampled ($image, $imagetmp, 0, 0, 0, 0, $largeur_en_cours, $hauteur_en_cours, $imagesize[0], $imagesize[1]) ;
		if (substr ($destination, -3) == "jpg") 
			{
			imagejpeg ($image, $destination , 80) ;
			}
		if (substr ($destination, -3) == "gif") 
			{
			imagegif ($image, $destination , 80) ;
			}
		if (substr ($destination, -3) == "png") {
			
			imagepng ($image, $destination , 0) ;
			}
// 		chmod ($destination, 0777) ;
		imagedestroy ($image) ;
		} 
	else 
		{
		$image = imagecreatetruecolor ($imagesize[0], $imagesize[1]) ;
		imagecopyresampled ($image, $imagetmp, 0, 0, 0, 0, $imagesize[0], $imagesize[1], $imagesize[0], $imagesize[1]) ;
		if (substr ($destination, -3) == "jpg") 
			{
			imagejpeg($image, $destination, 80) ;
			}
		if (substr ($destination, -3) == "gif") 
			{
			imagegif ($image, $destination, 80) ;
			}
		if (substr ($destination, -3) == "png") 
			{
			imagepng ($image, $destination, 80) ;
			}
// 		chmod ($destination, 0777) ;
		imagedestroy ($image) ;
		}
	}

function copier_image ($nom_fic, $imagename)
	{
	if ($OpenLocal = fopen ($_FILES[$imagename]['tmp_name'], "rb")) 
		{
		$LocalFileContent = fread ($OpenLocal, $_FILES[$imagename]['size']) ;
		} 
	else 
		{
		fclose ($OpenLocal) ;
		die ("<b>Erreur :</b> Impossible d'ouvrir le fichier local '" . $_POST[$imagename] . ".") ;
		}
	fclose ($OpenLocal) ;
	if ($OpenRemote = fopen ($nom_fic, "wb")) 
		{
		if (fwrite ($OpenRemote, $LocalFileContent)) 
			{
			fclose ($OpenRemote) ;
			}
		} 
	else 
		{
		fclose ($OpenRemote) ;
		die ("<b>Erreur :</b> Impossible d'ouvrir le fichier distant $nom_fic") ;
		}
// 	chmod ("$nom_fic", 0777) ;
	}

function copier_image_url ($nom_fic, $url, $extension)
	{
	$ok = "non" ;
	switch ($extension)
		{
		case "gif":
			if ($monimage = @imagecreatefromgif($url))
				{
				$ok = "oui" ;
				imagegif ($monimage, $nom_fic , 80) ;
				chmod ($nom_fic, 0777) ;
				imagedestroy ($monimage) ;
				}
			break ;
		case "jpg":
			if ($monimage = @imagecreatefromjpeg($url))
				{
				$ok = "oui" ;
				imagejpeg ($monimage, $nom_fic , 80) ;
				chmod ($nom_fic, 0777) ;
				imagedestroy ($monimage) ;
				}
			break ;
		case "png":
			if ($monimage = @imagecreatefrompng($url))
				{
				$ok = "oui" ;
				imagepng ($monimage, $nom_fic , 80) ;
				chmod ($nom_fic, 0777) ;
				imagedestroy ($monimage) ;
				}
			break ;
		}
	if ($ok == "oui")
		{
		return true ;
		}
	else
		{
		return false ;
		}
	}

function categories_parentes($catid)
	{
	global $DB_site2, $cats ;
	$parente=$DB_site2->query_first("select parentid from categorie where catid = '$catid'");
	if ($parente[0] != 0)
		{
		$cats .= "," . $parente[0] ;
		categories_parentes($parente[0]);
		}
	return ($cats) ;
	}
	
function article_base($artid_lie)
	{
	global $DB_site2, $artid_base;
	$artid_base = $artid_lie;
	$article=$DB_site2->query_first("SELECT * FROM liens_articles WHERE article_lie = '$artid_lie'");
	if ($article[liensarticlesid] != "")
		{
		$actif=$DB_site2->query_first("SELECT * FROM article WHERE artid = '$article[article_base]'");
		if ($actif[active] == '0' or $actif[artid] == "")
			$artid_base = $article[article_base];
		else
			article_base($article[article_base]);
		}
	return $artid_base;
	}
	
function modifhistory($data){
	global $DB_site2;
	global $user_info;
	$nohistory=array("old","infos","mq2","quitter","modif","action2","artid","catid");
	if(!array_key_exists("artid",$data)) return false;
	if($data['modif']!=1) return false;
	$artid=$data['artid'];
	if($artid=="") return false;
  
	foreach($data as $key=>$value){
	      if(is_array($value)) $value=mysqli_real_escape_string($DB_site2->link_id,trim(str_replace("array","",var_export($value,true))));
	      if(!in_array($key,$nohistory)) $DB_site2->query("INSERT INTO article_history SET artid=$artid, utilisateur='".$user_info['username']."', date=".time().", `key`='$key', value='".mysqli_real_escape_string($DB_site2->link_id,$value)."'");
	}
	if($data['prix']!="" && is_numeric($data['prix']['base'])){
		$DB_site2->query("INSERT INTO history_mcav_pdf SET artid=".$artid.", dateline=".time().", price=".$data['prix']['base']." ");
	}
}

?>
