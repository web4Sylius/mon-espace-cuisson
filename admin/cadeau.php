<?php
require "admin_global.php";
include("../class/facture.class.php");

include "./header.php";
include "./menu.php";

if(!isset($_GET['site_code'])) $_GET['site_code']="base";
$optsite="";
foreach($sites as $site_code=>$site){
    $optsite.="<option value=\"".$site_code."\"".($site_code==$_GET['site_code']?" selected":"").">".$site_code."</option>";
}

function arborescence($parentid)
    {
    global $DB_site2,$select,$niveau,$libelle,$catid,$libellecat, $sel_catid;
    $niveau.="--->";
    $requete_arbo=" select $libelle,catid,color from categorie where parentid='$parentid' ORDER BY position ";
    $arbos=$DB_site2->query($requete_arbo);
    while ($arbo=$DB_site2->fetch_array($arbos)) 
        {
        if($arbo[catid]==$sel_catid) 
            {
            $selected="selected";
            $libellecat=$arbo[$libelle];
            $select.="<option value=\"$sel_catid\" $selected>$niveau $libellecat</option>";
            } 
        else 
            {
            $selected="";
            $select.="<option value=\"$arbo[catid]\" style=\"background:".$arbo['color'].";\">$niveau $arbo[$libelle]</option>";
            arborescence($arbo[catid]);
            }
        }
    $niveau=substr($niveau , 0 , strlen($niveau)-4 );
    return ($select);
    }
   
$txtva=$DB_site2->query_first("SELECT TVATauxnormal FROM pays WHERE paysid=57");
    
$jscadeau="<script language=\"javascript\">

	  function afficheMarques(){
	  	jQuery('#cadeaumarques').toggle();
	  } 		
	  function addcaract(caractvalid,name){
	    if(document.getElementById('cadeauoption').innerHTML=='Aucune') document.getElementById('cadeauoption').innerHTML='';
	    document.getElementById('cadeauoption').innerHTML+='<p id=\"caractvalid'+caractvalid+'\"><a href=\"javascript:removecaract('+caractvalid+');\" style=\"color:red;\" title=\"Supprimer\">X</a> '+name+'</p>';
	    formids=document.getElementsByName('option')[0].value.split(',');
	    for(i=0;i<=formids.length;i++){
		if(formids[i]==caractvalid){
		    alert('Cette option est déjà offerte avec ce code');
		    return;
		}
	    }
	    if(formids[0]=='')
	      document.getElementsByName('option')[0].value=caractvalid;
	    else{
	      formids.push(caractvalid);
	      document.getElementsByName('option')[0].value=formids.join(',');
	    }
	    
	    document.getElementById('caractlist').style.display='none';
	  }

	  function removecaract(caractvalid){
	    formids=document.getElementsByName('option')[0].value.split(',');
	    var newid=new Array();
	    document.getElementsByName('option')[0].value='';
	    for(i=0;i<formids.length;i++){
		if(formids[i]!=caractvalid){
		    newid.push(formids[i]);
		}
	    }
	    
	    if(newid.length>0) document.getElementsByName('option')[0].value=newid.join(',');
	    
	    var caractvalnames=document.getElementById('cadeauoption').getElementsByTagName('P');

	    for(i=0;i<caractvalnames.length;i++){
		if(caractvalnames[i].id=='caractvalid'+caractvalid) document.getElementById('cadeauoption').removeChild(caractvalnames[i]);
	    }
	    if(document.getElementById('cadeauoption').innerHTML=='') document.getElementById('cadeauoption').innerHTML='Aucune';
	  }

	  var okartcode=false;
	  function srccadeau(txt){
	      document.getElementById('resultlist').style.display='none';
	      if(txt.length<3) return;
	      okartcode=false;
	      ajaxrequest('marketplace/src.php?active=1&txt='+txt,srccb);
	  }
	  function srccb(httprequest){
	      var xml=httprequest.responseXML.documentElement;
               var html=\"\";
               if(xml.getElementsByTagName('prod')[0]){
                    html+=\"<table width='100%' style='border:solid thin #808080;'>\";
                    for(i=0;i<xml.getElementsByTagName('prod').length;i++){
                         var description=xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
                         var id=xml.getElementsByTagName('prod')[i].getAttribute('id');
                         var price=xml.getElementsByTagName('prod')[i].getAttribute('price');
                         var buyprice=xml.getElementsByTagName('prod')[i].getAttribute('buyprice');
                         var artid=xml.getElementsByTagName('prod')[i].getAttribute('artid');
			 var visible=xml.getElementsByTagName('prod')[i].getAttribute('visible');
			 color='black';
			 if(visible=='0') color='red';
                         html+=\"<tr><td><img src='../configurations/".$host."/images/produits/\"+artid+\".jpg' width='30'></td><td style='border-bottom:solid thin #cccccc;color:\"+color+\";'>\"+id+\"</td><td style='border-bottom:solid thin #cccccc;'>\"+description+\"</span></td><td style='border-bottom:solid thin #cccccc;'><a href=javascript:selectprod('\"+id+\"')>Sélectionner</a></td></tr>\";
                    }
                    html+=\"</table>\";
               }else
                   html=\"Pas de résultat\";

	      document.getElementById('resultlist').innerHTML=html;
	      document.getElementById('resultlist').style.display='block';
	  }
	  function selectprod(id){
	      document.getElementsByName('cadeau_artid')[0].value=id;
	      okartcode=true;
	      document.getElementById('resultlist').style.display='none';
	  }
	     
	  function srcuser(txt){
	      if(txt.length<3){
		  document.getElementById('user_results').style.display='none';
		  return;
	      }
	      ajaxrequest('mailbox_ajax.php?txt='+txt,user_cb);
	  }
	  function user_cb(httpRequest){
	      var xml=httpRequest.responseXML.documentElement;
	      var html='';
	      if(xml.getElementsByTagName('contact')[0]){
			  for(i=0;i<xml.getElementsByTagName('contact').length;i++){
				  var mail=xml.getElementsByTagName('contact')[i].getAttribute('mail');
				  var nom=xml.getElementsByTagName('contact')[i].getAttribute('nom');
				  var type=xml.getElementsByTagName('contact')[i].getAttribute('type');
				  var userid=xml.getElementsByTagName('contact')[i].getAttribute('userid');
				  var fonction=xml.getElementsByTagName('contact')[i].getAttribute('fonction');
				  var societe=xml.getElementsByTagName('contact')[i].getAttribute('societe');
				  var ville=xml.getElementsByTagName('contact')[i].getAttribute('ville');
				  var cp=xml.getElementsByTagName('contact')[i].getAttribute('codepostal');
				  if(userid!=undefined){
					  html+=\"<a href=\\\"javascript:cadeauuser(\"+userid+\",'\"+nom.replace(/\'/g,\"\\\'\")+\"')\\\"><i>n°\"+userid+\"</i>\"+societe+\" \"+nom+\" (\"+cp+\" \"+ville+\")</a><br>\";
				  
				  
				  
				  
				  }
			  }
	      }
	      document.getElementById('user_results').innerHTML=html;
	      document.getElementById('user_results').style.display='block';
	  }
	  function cadeauuser(userid,nom){
	      document.getElementById('user_results').style.display='none';
	      document.getElementsByName('userid')[0].value=userid;
	      if(nom=='')
		document.getElementById('cadeauuser').innerHTML='';
	      else
		document.getElementById('cadeauuser').innerHTML='<a href=\"javascript:cadeauuser(0,\'\')\" style=\"color:red;\">X</a> '+nom;
	      if(userid>0) document.getElementById('cadeauuser').innerHTML+=' ('+userid+')';
	  }

	  function srcart(txt){	        
	      if(txt.length<3) return;
	      ajaxrequest('marketplace/src.php?active=1&txt='+txt,srcartcb);
	  }
	  function srcartcb(httprequest){
	      var xml=httprequest.responseXML.documentElement;
               var html=\"\";
               if(xml.getElementsByTagName('prod')[0]){
                    html+=\"<table width='100%' style='border:solid thin #808080;'>\";
                    for(i=0;i<xml.getElementsByTagName('prod').length;i++){
                         var description=xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
                         var id=xml.getElementsByTagName('prod')[i].getAttribute('id');
                         var price=xml.getElementsByTagName('prod')[i].getAttribute('price');
                         var buyprice=xml.getElementsByTagName('prod')[i].getAttribute('buyprice');
                         var artid=xml.getElementsByTagName('prod')[i].getAttribute('artid');
			 var visible=xml.getElementsByTagName('prod')[i].getAttribute('visible');
			 color='black';
			 if(visible=='0') color='red';
                         html+=\"<tr><td><img src='../configurations/".$host."/images/produits/\"+artid+\".jpg' width='30'></td><td style='border-bottom:solid thin #cccccc;color:\"+color+\";'>\"+id+\"</td><td style='border-bottom:solid thin #cccccc;'>\"+description+\"</span></td><td style='border-bottom:solid thin #cccccc;'><a href=javascript:selectart('\"+artid+\"','\"+id+\"')>Sélectionner</a></td></tr>\";
                    }
                    html+=\"</table>\";
               }else
                   html=\"Pas de résultat\";

	      document.getElementById('art_result').innerHTML=html;
	      document.getElementById('art_result').style.display='block';
	  }
	  function selectart(artid,artcode){
	      document.getElementsByName('sel_art')[0].value=artid;
	      if(artcode=='')
		  document.getElementById('sel_artname').innerHTML='';
	      else
		  document.getElementById('sel_artname').innerHTML=artcode+\" <a href=\\\"javascript:selectart(0,'')\\\" style='color:red;'>X</a>\";
	      document.getElementById('art_result').style.display='none';
	  }

	  function confirmation(){
	      var error='';
	      if(document.getElementsByName('txt_code')[0].value.length<11) error +=\"Il faut donner un code à 12 caractères\\n\";
	      document.getElementById('confirm_code').innerHTML=document.getElementsByName('txt_code')[0].value;
	      var typecadeau=document.getElementById('typesign').value;
	      if(document.getElementsByName('txt_valeurcadeau')[0].value=='') error +=\"Il faut donner la valeur de la réduction\\n\";
	      document.getElementById('confirm_valeur').innerHTML=document.getElementsByName('txt_valeurcadeau')[0].value+\" \"+typecadeau;
	      var appliquer=\"Tout le site\";
	      var sel_catid=document.getElementsByName('sel_catid')[0];
	      if(sel_catid.selectedIndex>0) appliquer=sel_catid.options[sel_catid.selectedIndex].innerHTML;
	      /*var marque_select=document.getElementsByName('marque')[0];
	      if(marque_select.value!='') appliquer=marque_select.options[marque_select.selectedIndex].text;*/
	      if(document.getElementsByName('sel_art')[0].value!='') appliquer=document.getElementById('sel_artname').innerHTML;
	      document.getElementById('confirm_appliquer').innerHTML=appliquer;
	      if(document.getElementsByName('opt_nbrfois')[1].checked) nbrfois=\"illimité\";
	      if(document.getElementsByName('opt_nbrfois')[0].checked) nbrfois=document.getElementsByName('txt_nbrfois')[0].value+\" fois\";
	      document.getElementById('confirm_nbrfois').innerHTML=nbrfois;
	      if(document.getElementsByName('opt_nbrmaxi')[1].checked) nbrmaxi=\"illimité\";
	      if(document.getElementsByName('opt_nbrmaxi')[0].checked) nbrmaxi=document.getElementsByName('txt_nbrmaxi')[0].value+\" fois\";
	      document.getElementById('confirm_nbrmaxi').innerHTML=nbrmaxi;
	      if(document.getElementsByName('opt_datefin')[1].checked) datefin=\"aucune\";
	      if(document.getElementsByName('opt_datefin')[0].checked) datefin=document.getElementsByName('txt_datefin')[0].value;
	      document.getElementById('confirm_datefin').innerHTML=datefin;
	      if(document.getElementsByName('appliquer')[0].checked) appliquer=\"PPC\";
	      if(document.getElementsByName('appliquer')[1].checked) appliquer=\"PV\";
	      document.getElementById('confirm_appliquerprix').innerHTML=appliquer;
		  if(document.getElementsByName('appcommande')[0].checked) appliquer=\"Oui\";
		  else appliquer=\"Non\";
		  document.getElementById('confirm_appliquercomm').innerHTML=appliquer;
	      document.getElementById('confirm_cadeau').innerHTML=document.getElementsByName('cadeau_artid')[0].value;
	      document.getElementById('confirm_option').innerHTML=\"\";
	      var options=document.getElementById('cadeauoption').getElementsByTagName('p');
	      for(i=0;i<options.length;i++) document.getElementById('confirm_option').innerHTML+=options[i].innerHTML;
	      var portoffert=\"\";
	      if(document.getElementsByName('port0')[0].checked) portoffert=\"Livraison\";
	      if(document.getElementsByName('port1')[0].checked) portoffert=\"Livraison et installation\";
	      document.getElementById('confirm_port').innerHTML=portoffert;
	      document.getElementById('confirm_minimum').innerHTML=document.getElementsByName('minimum')[0].value;
	      document.getElementById('confirm_client').innerHTML=document.getElementById('cadeauuser').innerHTML;
	      var cptMarque = document.getElementById('cptMarque').value;
	      /*var i ;
	      var passemarque = false ;
	      var total = document.getElementById('sel_check').checked;
	      for(i=0;i<cptMarque;i++ ){
	      	if(document.getElementsByName('marques[]')[i].checked) passemarque = true;
	      }
	      if(!passemarque && !total) error += 'Veuillez cocher au moins une marque\\n';
	      */
	      if(error!=''){
		  alert(error);
		  return;
	      }

	      document.getElementById('formulaire').style.display='none';
	      document.getElementById('confirmation').style.display='block';
	  }

	  function annule_confirmation(){
	      document.getElementById('formulaire').style.display='block';
	      document.getElementById('confirmation').style.display='none';
	  }
	  
	  function convert_ht_ttc(value,type,to) {
	      var txtva=$txtva[0];
	      value=parseFloat(value);
	      if(type=='ht') value=value*(1+txtva/100);
	      if(type=='ttc') value=value/(1+txtva/100);
	      value=Math.round(value*100)/100;
	      if(document.getElementById(to)) document.getElementById(to).value=value;
	  }
	  </script>";


if( $action == "ajouter" || $action == "ajouter2" )
{
    $bodyonload = "onLoad=\"myInit();\"";
    
    echo "<script type=\"text/javascript\" src=\"scripts/calendar/Js/CLanguage.js\"></script>
            <script type=\"text/javascript\" src=\"scripts/calendar/Js/DateControl.js\"></script>
            <script type=\"text/javascript\" src=\"scripts/calendar/Js/DOMCalendar.js\"></script>
    
    <script language=\"javascript\">";

    /*-------------------------------------------------------------------------------------------*/
    /*------------------------------------DEBUT DU CALENDRIER------------------------------------*/
    /*-------------------------------------------------------------------------------------------*/
    echo "
    var DCAL = null;
    
    GetDomLeft = function ( oNode )
    {
      var oCurrentNode = oNode;
      var iLeft = 0;
      while (oCurrentNode.tagName != \"BODY\") {
        iLeft += oCurrentNode.offsetLeft;
        oCurrentNode = oCurrentNode.offsetParent;
      };
      return iLeft;
    };
    GetDomTop = function ( oNode )
    {
      var oCurrentNode = oNode;
      var iTop = 0;
      while (oCurrentNode.tagName != \"BODY\") {
        iTop += oCurrentNode.offsetTop;
        oCurrentNode = oCurrentNode.offsetParent;
      };
      return iTop;
    };
    
    function myInit() {
    
     // On recupere les objets images
     oImg1 = document.getElementById(\"iDC1\");
    
     // On instancie l'objet Calendar
     DCAL = new DOMCalendar();
    
     // On affecte le repertoire image
     DCAL.SetImgPath(\"scripts/calendar/Img\");
    
     // On ajoute des zones Calendar
     // Name, Form : Nom et formulaire a controler
     DCAL.AddCalendar(\"DC1\", document.formu.txt_datefin);
    
     // On affecte le premier jour de la semaine
     // 0 : Dimanche -> 6 : Samedi
     DCAL.SetFirstDayOfWeek(\"DC1\", 1);
     // On affecte la position par rapport a l'image
     DCAL.SetPosition(\"DC1\", GetDomLeft(oImg1) + oImg1.offsetWidth + 10, GetDomTop(oImg1) - 32);
     // On affecte les SelectList
     //DCAL.HideSelectList(\"DC1\", document.formu.tSelect_1);
     DCAL.Init();
    };";
    /*-----------------------------------------------------------------------------------------*/
    /*------------------------------------FIN DU CALENDRIER------------------------------------*/
    /*-----------------------------------------------------------------------------------------*/
    echo "</script>";
}



function convertirChaineEnDate( $la_date ) {
   @list( $jour, $mois, $annee ) = explode( '/', $la_date );
   return @date( 'Y-m-d',mktime(0,0,0,$mois,$jour,$annee) );
}

echo "<h1>Gestion des bons de réduction</h1>
<LINK href=\"cal.css\" type=\"text/css\" rel=\"stylesheet\">
<script language=\"javascript\" src=\"calendrier.js\"></script>
<script language=\"javascript\">document.title='Codes promo';</script>" ;

if (isset($action) and $action == "parametre")
    {
    $DB_site2->query("update parametresgeneraux set appliquer_reduction_sur_promo = '$appliquer'");
    echo "<font class=\"cost\">Paramêtre modifié</font>" ;
    $action = "" ;
    }


////////////////////////////////////////////////////////////////////////////////////////////////////////
// AFFICHER LES CADEAUX
////////////////////////////////////////////////////////////////////////////////////////////////////////
if ( !isset($action) or $action == "" )
{
    $perpage=50;
    $start=0;
    if(isset($_GET['start'])) $start=$_GET['start'];
    if(isset($_POST['artcode']) && $_POST['artcode']!=""){
      $_POST['artcode']=strtoupper($_POST['artcode']);
      if(substr($_POST['artcode'],0,4)!="ACI-") $_POST['artcode']="ACI-".$_POST['artcode'];
    }

    echo "<h3>Bons de réduction existants</h3>";
    echo "<form name=\"form_cadeau\" method=\"post\" action=\"cadeau.php?site_code=".$_GET['site_code']."\">";
    echo "<form method=\"post\">
    <div class=\"commande_detail\">
    <table>
    <tr>
	<td>Site</td>
	<td><select name=\"site_code\" onchange=\"document.location='cadeau.php?site_code='+this.value\">".$optsite."</select></td>
	<td><a href=\"cadeau.php?site_code=".$_GET['site_code']."&action=ajouter\" target=\"_self\"><img src=\"images/icon/edit_add.png\" align=\"absmiddle\"> Ajouter un bon de réduction</a></td>
    </tr>
    <tr>
	<td style=\"font-size:14px;width:50px;\">Rechercher</td>
	<td style=\"border-left:solid 1px #CCC;padding-left:4px;\">Par code : <input type=\"text\" name=\"code\" value=\"".$_POST['code']."\"><br>par article : <input type=\"text\" name=\"artcode\" value=\"".$_POST['artcode']."\"><br>par client : <input type=\"text\" name=\"nom\" value=\"".$_POST['nom']."\"></td>
	<td><input type=\"submit\" value=\"\" class=\"loupe\"> <input type=\"checkbox\" onclick=\"document.location='cadeau.php?site_code=".$_GET['site_code']."&hidden='+this.checked;\"".($_GET['hidden']=="true"?"checked":"")."> Afficher les codes expirés</td>
    </tr>
    </table>
    </div>
    <div class=\"clear\"></div>
    </form>";
		   
    echo "  <table class=\"table_modules_boutiques\">
              <tr height=\"35\">
		  <td class=\"td_head_factures\" align=\"center\"><b>Créé par</b></td>
		  <td class=\"td_head_factures\" align=\"center\"><b>Code</b></td>
                  <td class=\"td_head_factures\" align=\"center\"><b>Valeur<bR>(€ ou %)</b></td>
		  <td class=\"td_head_factures\" align=\"center\"><b>Article offert</b></td>
		  <td class=\"td_head_factures\" align=\"center\"><b>Option offerte</b></td>
		  <td class=\"td_head_factures\" align=\"center\"><b>Livr. offerte</b></td>
                  <td class=\"td_head_factures\" align=\"center\"><b>Nb fois<br>/client</b></td>
                  <td class=\"td_head_factures\" align=\"center\"><b>Nb fois<br>/max</b></td>
		  <td class=\"td_head_factures\" align=\"center\"><b>Min. achat</b></td>
		  <td class=\"td_head_factures\" align=\"center\"><b>Client</b></td>
                  <td class=\"td_head_factures\" align=\"center\"><b>Date de fin</b></td>
                  <td class=\"td_head_factures\" align=\"center\"><b>Rayon <br>Sous-rayon</b></td>
                  <td class=\"td_head_factures\" align=\"center\"><b>Article</b></td>
                  <td class=\"td_head_factures\" align=\"center\"><b>Marque</b></td>
              </tr>";
    $_GET['hidden']=="true"?$hidden="":$hidden=" AND (datefin=0 OR datefin>UNIX_TIMESTAMP(NOW()))";
    $sqlcode="";
    if(isset($_POST['code']) && $_POST['code']!=""){
	$sqlcode=" AND code LIKE '%".$_POST['code']."%'";
	$hidden="";
    }
    if(isset($_POST['artcode']) && $_POST['artcode']!=""){
	$art=$DB_site2->query_first("SELECT artid,artcode FROM article WHERE artcode='".$_POST['artcode']."'");
	if($art!="") $sqlart=" AND (SELECT COUNT(*) FROM article_cadeau WHERE artid=".$art['artid']." AND cadeauid=cadeau.cadeauid)>0";
	$hidden="";
    }
    if(isset($_POST['nom']) && $_POST['nom']!=""){
	$sqlclient=" AND cadeauid IN (SELECT cadeauid FROM facture f INNER JOIN utilisateur u USING(userid) WHERE f.nom LIKE '%".$_POST['nom']."%' OR f.raisonsociale LIKE '%".$_POST['nom']."%' OR u.nom LIKE '%".$_POST['nom']."%' OR u.raisonsociale LIKE '%".$_POST['nom']."%')";
    }
    $sqlsite="AND site_code='".$_GET['site_code']."'";
    
    $rq_cadeaux = $DB_site2->query( "SELECT * FROM cadeau 
                                     WHERE active='1'$hidden $sqlcode $sqlart $sqlclient $sqlsite
                                     ORDER BY cadeauid DESC LIMIT $start,$perpage" );
    if( $DB_site2->num_rows( $rq_cadeaux ) > 0 )
    {
        while ( $rs_cadeaux = $DB_site2->fetch_array( $rq_cadeaux ) )
        {
            $rowalt = "td_promos_".getrowbg();

            // Nombre de fois que ce code a été utilisé
            $nb_fois_utilise = $DB_site2->query_first( "SELECT count(*) FROM facture 
                                                    WHERE cadeauid=$rs_cadeaux[cadeauid]
                                                    AND valide='1'" );

            echo "<tr>";    
	    echo "<td class=\"$rowalt\">".$rs_cadeaux['utilisateur'].($rs_cadeaux['date']>0?"<br>".date("d/m/Y",$rs_cadeaux['date']):"")."</td>";
            echo "  <td class=\"$rowalt\" align=\"center\"><font color=".($rs_cadeaux['datefin']==0 || $rs_cadeaux['datefin']>time()?"green":"red").">$rs_cadeaux[code]</font></td>";
            if( $rs_cadeaux["typecadeauid"] == 0 ) {
                $unite_valeur = "€";
                $appliquer="";
            } elseif( $rs_cadeaux["typecadeauid"] == 1 ) {
                $unite_valeur = "%";
                $appliquer=$rs_cadeaux["appliquer"];
            }
            echo "  <td class=\"$rowalt\" align=\"right\">$rs_cadeaux[valeurcadeau] $unite_valeur $appliquer</td>";
	    if($rs_cadeaux['cadeau_artid']!=0){
	      $artoffert=$DB_site2->query_first("SELECT artcode FROM article WHERE artid=".$rs_cadeaux['cadeau_artid']);
	      echo "  <td class=\"$rowalt\">".substr($artoffert[artcode],4)."</td>";
	    }else{
	      echo "  <td class=\"$rowalt\">Aucun</td>";
	    }
	    
	    $opt=explode(',',$rs_cadeaux['option']);
	    $nbopt=count($opt);
	    if($opt==false) $nbopt=0;
	    echo "  <td class=\"$rowalt\" align=\"center\">".$nbopt."</td>";

	    switch($rs_cadeaux['port']){
		case 0:
		$txt="-";
		break;
		case 1:
		$txt="livr.";
		break;
		case 2:
		$txt="install.";
		break;
		case 3:
		$txt="livr.\ninstall.";
		break;
	    }
	    echo "  <td class=\"$rowalt\">$txt</td>";

            echo "  <td class=\"$rowalt\" align=\"right\">"
                     .($rs_cadeaux[nbrfois]==0?"<font color=red>Illimité</font>":$rs_cadeaux[nbrfois])."</td>";
            echo "  <td class=\"$rowalt\" align=\"right\">"
                     .$nb_fois_utilise[0]."/"
                     .($rs_cadeaux[nbrmaxi]==0?"Illimité":$rs_cadeaux[nbrmaxi])."</td>";

	    echo "<td class=\"$rowalt\" align=\"right\">".$rs_cadeaux['minimum']."</td>";

	    $nomutilisateur="";
	    if($rs_cadeaux['userid']>0){
		$utilisateur=$DB_site2->query_first("SELECT CONCAT(nom,' ',prenom) FROM utilisateur WHERE userid=".$rs_cadeaux['userid']);
		$nomutilisateur=$utilisateur[0];
	    }
	    echo "<td class=\"$rowalt\">$nomutilisateur</td>";

            echo "  <td class=\"$rowalt\" align=\"center\">"
                     .($rs_cadeaux[datefin]!=0?date("d/m/Y", $rs_cadeaux[datefin]):"Aucune")."</td>";

        $rayons = $DB_site2->query( "SELECT * 
                                    FROM categorie_cadeau cc, categorie c 
                                    WHERE cc.catid = c.catid and cc.cadeauid = '$rs_cadeaux[cadeauid]'
                                    ORDER BY c.libelleFR asc" );
        // test si le cadeau concerne un rayon et / ou un sous-rayon 
        if ($DB_site2->num_rows($rayons) > 0)
            {
            echo "  <td class=\"$rowalt\" align=\"center\">" ;
            while ($rayon=$DB_site2->fetch_array($rayons))
                {
                    echo "$rayon[libelleFR]<br>" ;
                }
            echo "</td>" ;
            }
        else
            {
            echo "  <td class=\"$rowalt\" align=\"center\">--</td>" ;
            }
        $articles = $DB_site2->query( "SELECT * 
                                    FROM article_cadeau ac, article a
                                    WHERE ac.artid = a.artid and ac.cadeauid = '$rs_cadeaux[cadeauid]'
                                    ORDER BY a.artcode asc" );
        // test si le cadeau concerne un article 
        if ($DB_site2->num_rows($articles) > 0)
            {
            echo "  <td class=\"$rowalt\" align=\"center\">" ;
            while ($article=$DB_site2->fetch_array($articles))
                {
                $artic = $article[artcode] ;
                if (strlen($artic) > 20)
                    {
                    $artic = substr($artic, 0, 20) ;
                    }
                    echo "$artic<br>" ;
                }
            echo "</td>" ;
            }
        else
            {
            echo "  <td class=\"$rowalt\" align=\"center\">--</td>" ;
            }
            
           
            if($rs_cadeaux['marqueid']!=0){

				$m=$DB_site2->query("SELECT libelle FROM marque WHERE marqueid IN(".$rs_cadeaux['marqueid'].")");
				$i=0;
				$libMarque ="";
				while($row = $DB_site2->fetch_array($m)){
					if($i == 0 ) $firstmarque = $row['libelle']; 
					$libMarque .= $row['libelle']." ";
					$i++;
				}
				if($i>1) $firstmarque = $firstmarque." et ".($i-1)." autres";
            }
            print "<td class=\"$rowalt\" title=\"".$libMarque."\">";
            print $firstmarque;
            print "</td>";
            $firstmarque = "";

            echo "  <td class=\"$rowalt\" align=\"center\" width=\"13%\">
                      <a href=\"cadeau.php?action=editer&site_code=".$_GET['site_code']."&cadeauid=$rs_cadeaux[cadeauid]\" target=\"_self\">Editer</a>
                    | <a href=\"cadeau.php?action=supprimer&site_code=".$_GET['site_code']."&cadeauid=$rs_cadeaux[cadeauid]\" target=\"_self\">Enlever</a>
                    <br><a href=\"../pubdevis.php?mkpdf=$rs_cadeaux[cadeauid]\" target=\"_blank\">pdf</a>
                    </td>";
            echo "</tr>";

	    echo "<tr><td colspan=\"10\">";
	    $req=$DB_site2->query("SELECT * FROM facture WHERE cadeauid='".$rs_cadeaux['cadeauid']."'");
	    while($f=$DB_site2->fetch_array($req)){
		$facture=new facture($DB_site2,$f);
		echo "<a href=\"facture.php?action=editer&facturid=".$facture->facturid."\" target=\"_blank\">".$facture->facturid."</a> ".$facture->validetxt." ".number_format($facture->totalttc,2,',','')."&euro;<br>";
	    }
	    if($DB_site2->num_rows($req)==0) echo "Pas de commande";
	    echo "</td></tr>";
        }
	$total=$DB_site2->query_first("SELECT COUNT(*) FROM cadeau WHERE active='1'$hidden $sqlcode $sqlart $sqlsite");
	$nbpages=ceil($total[0]/$perpage);
	print "<tr><td colspan=\"11\" align=\"center\">";
	if($start>0) print "<a href=\"cadeau.php?site_code=".$_GET['site_code']."&hidden=".$_GET['hidden']."&start=".($start-$perpage)."\">Précédent</a>";
	for($i=0;$i<$nbpages;$i++){
	  $pstart=($i*$perpage);
	  print "&nbsp;<a href=\"cadeau.php?site_code=".$_GET['site_code']."&hidden=".$_GET['hidden']."&start=".$pstart."\"".($start==$pstart?" style=\"font-weight:bold;color:black;\"":"").">".($i+1)."</a>";
	}
	if($start<$perpage*($nbpages-1)) print "&nbsp;<a href=\"cadeau.php?site_code=".$_GET['site_code']."&hidden=".$_GET['hidden']."&start=".($start+$perpage)."\">Suivant</a>";
    }
    else
        echo "<tr><td colspan=\"8\" align=\"center\"><b>Il n'y a aucun bon de réduction</b><br><br></td></tr>";
    echo "</table><br>";
    /*
    $applique=$DB_site2->query_first("SELECT appliquer_reduction_sur_promo FROM parametresgeneraux");
    switch ($applique[0])
        {
        case '0':
            $chk0 = "checked" ;
            $chk1 = "" ;
            break ;
        case '1':
            $chk0 = "" ;
            $chk1 = "checked" ;
            break ;
        }
    */
    echo "<div align=\"center\"><br><a href=\"cadeau.php?site_code=".$_GET['site_code']."&action=ajouter\" target=\"_self\"><img src=\"images/icon/edit_add.png\" align=\"absmiddle\"> Ajouter un bon de réduction</a></div><br>";
    /*
    echo "<form method=\"post\" action=\"cadeau.php\">
    <input type=\"hidden\" name=\"action\" value=\"parametre\">
    <table class=\"table_modules_boutiques\">
        <tr>
            <td align=\"left\" colspan=\"4\"><br></td>
        </tr>
        <tr>
            <td align=\"center\">Appliquer la réduction sur les prix en promotion ?<br>(seulement pour une réduction en %)</td>
            <td align=\"left\"><input type=\"radio\" name=\"appliquer\" value=\"1\" $chk1> Oui<font color=\"blue\">*</font></td>
            <td align=\"left\"><input type=\"radio\" name=\"appliquer\" value=\"0\" $chk0> Non<font color=\"blue\">*</font></td>
            <td align=\"center\"><input type=\"submit\" value=\"Valider\"> </td>
        </tr>
        <tr>
            <td align=\"left\" colspan=\"4\"><br><br>
            <font color=\"blue\">*</font><strong>Oui</strong> : On cumule la promotion et la réduction.<br>
            <font color=\"blue\">*</font><strong>Non</strong> : On prend celle qui offre la plus grande réduction au client (promo ou réduc).
            <br><br></td>red
        </tr>
        <tr>
            <td align=\"center\" colspan=\"4\"><br><strong>ATTENTION !</strong><br>Dans tous les cas vous devez le spécifier sur votre boutique.<br>
            (Soit dans les Conditions Générales de Vente, soit dans une page indépendante)</td>
        </tr>
    </table>
    </form>";
    */
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AJOUTER UN BON DE REDUCTION (action=ajouter2)
////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif ( $action == "ajouter2" )
{	
    $erreur_saisie = 0;
    if( isset($_POST['btn_code']) && $_POST['btn_code'] == "Générer" )
    {   // Générer un code
        do
        {
            $code = ""; 
            $chars = array("2","3","4","5","6","7","8","9",
                           "a","b","c","d","e","f","g","h","j","k","m","n","p","q","r","s","t","u","v","w","x","y","z",
                           "A","B","C","D","E","F","G","H","J","K","L","M","N","P","Q","R","S","T","U","V","W","X","Y","Z"
                           );
            $count = count($chars) - 1; 
            srand((float) microtime()*1000000);
            for ($i = 0; $i < 12; $i++) $code .= $chars[rand(0, $count)];
            
            // On teste que ce code n'existe pas déja
            $code_doublon = $DB_site2->query_first( "SELECT cadeauid FROM cadeau
                                                     WHERE code='$code' " );
        } while( $code_doublon[cadeauid] != "" );

        $txt_code = $code;  
    }
    else
    {
        echo "<table class=\"table_modules_boutiques\" align=\"center\">";

        // Code ///////////////////////////////////////////////////////////////////////////////////////////
        if( !isset( $txt_code ) || $txt_code == "" )
        {
            echo "<tr><td align=\"center\">Vous devez générer ou saisir un <b>code</b></td></tr>";
            $erreur_saisie = 1;
        } elseif( strlen( $txt_code ) < 8 )
        {
            echo "<tr><td align=\"center\">Le <b>code</b> doit être composé de 8 caractères minimum</td></tr>";
            $erreur_saisie = 1;
        } else
        {   // On teste que ce code n'existe pas déja
            $code_doublon = $DB_site2->query_first( "SELECT cadeauid FROM cadeau
                                                     WHERE code='$txt_code' AND site_code='".$_GET['site_code']."'" );
            if( $code_doublon[cadeauid] != "" )
            {
                echo "<tr><td align=\"center\">Ce <b>code</b> existe déja !</td></tr>";     
                $erreur_saisie = 1;
            }           
        }

        // Valeur /////////////////////////////////////////////////////////////////////////////////////////
        if( !isset( $txt_valeurcadeau ) || $txt_valeurcadeau == "" )
        {
            echo "<tr><td align=\"center\">Vous devez saisir la <b>valeur</b></td></tr>";
            $erreur_saisie = 1;
        } elseif( !is_numeric( $txt_valeurcadeau ) )
        {
            echo "<tr><td align=\"center\">La <b>valeur</b> doit être de type numérique</td></tr>";
            $erreur_saisie = 1;
        }elseif( $opt_typecadeau == 1 && $txt_valeurcadeau >= 100 ) // Réduction en pourcents
        {
            echo "<tr><td align=\"center\">La <b>valeur</b> doit être inférieure à 100%</td></tr>";
            $erreur_saisie = 1;     
        }

        // Nombre d'utilisations par utilisateur //////////////////////////////////////////////////////////
        if( $opt_nbrfois == 0 )
        {
            if( !isset( $txt_nbrfois ) || $txt_nbrfois == "" )
            {
                echo "<tr><td align=\"center\">Vous devez saisir le <b>nombre d'utilisations</b> par utilisateur</td></tr>";
                $erreur_saisie = 1;
            } elseif( !is_numeric( $txt_nbrfois ) )
            {
                echo "<tr><td align=\"center\">Le <b>nombre d'utilisations</b> par utilisateur doit être de type numérique</td></tr>";
                $erreur_saisie = 1;
            } elseif( $txt_nbrfois < 1 )
            {
                echo "<tr><td align=\"center\">Le <b>nombre d'utilisations</b> par utilisateur doit être supérieur à 1</td></tr>";
                $erreur_saisie = 1;
            }
        }

        // Nombre d'utilisations maximum pour l'ensemble des utilisateurs /////////////////////////////////
        if( $opt_nbrmaxi == 0 )
        {
            if( !isset( $txt_nbrmaxi ) || $txt_nbrmaxi == "" )
            {
                echo "<tr><td align=\"center\">Vous devez saisir le <b>nombre d'utilisations maximum</b></td></tr>";
                $erreur_saisie = 1;
            } elseif( !is_numeric( $txt_nbrmaxi ) )
            {
                echo "<tr><td align=\"center\">Le <b>nombre d'utilisations maximum</b> doit être de type numérique</td></tr>";
                $erreur_saisie = 1;
            } elseif( $txt_nbrmaxi < 1 )
            {
                echo "<tr><td align=\"center\">Le <b>nombre d'utilisations maximum</b> doit être supérieur à 1</td></tr>";
                $erreur_saisie = 1;
            }
        }

        //si cadeau sur totalite cmd alors on vérifie avec ou sans transport ///////////////////////////////
        if(isset($_POST['appcommande'])){
        	if(!isset($_POST['appcommandetrspt'])){
        		echo "<tr><td align=\"center\">Vous devez choisir l'application du bon <b>avec ou hors TRANSPORT</b></td></tr>";
                $erreur_saisie = 1;
        	}
        }


        // Date de fin ////////////////////////////////////////////////////////////////////////////////////
        if( $opt_datefin == 0 )
        {
            if( !isset( $txt_datefin ) || $txt_datefin == "" )
            {
                echo "<tr><td align=\"center\">Vous devez saisir la <b>date de fin</b></td></tr>";
                $erreur_saisie = 1;
            }
            elseif (isset ($txt_datefin) and $txt_datefin != "")
            {
            if (strlen ($txt_datefin) != 10)
                {
                echo "<tr><td align=\"center\">La <b>date de fin</b> doit être au format jj/mm/aaaa</td></tr>";
                $erreur_saisie = 1 ;
                }
            else
                {
                if ((substr ($txt_datefin, 2, 1) == '/') and (substr ($txt_datefin, 5, 1) == '/'))
                    {
                    $j = substr ($txt_datefin, 0, 2) ;
                    $m = substr ($txt_datefin, 3, 2) ;
                    $a = substr ($txt_datefin, 6, 4) ;
                    $ajd = date('Ymd') ;
                    $dt = $a.$m.$j ;
                    if ($dt <= $ajd)
                        {
                        echo "<tr><td align=\"center\">La <b>date de fin</b> doit être ultérieure à aujourd'hui</td></tr>";
                        $erreur_saisie = 1; 
                        }
                    }
                else
                    {
                    echo "<tr><td align=\"center\">La <b>date de fin</b> doit être au format jj/mm/aaaa</td></tr>";
                    $erreur_saisie = 1 ;
                    }
                }
            }   
            else
            {   
                $tmp_datefin = explode( '-', convertirChaineEnDate($txt_datefin) );
                $str_datefin = $tmp_datefin[0] . $tmp_datefin[1] . $tmp_datefin[2];
                if( date( "Ymd" ) >= $str_datefin )
                {
                    echo "<tr><td align=\"center\">La <b>date de fin</b> doit être après aujourd'hui</td></tr>";
                    $erreur_saisie = 1; 
                }
            }
        }

        echo "</table>";

        if( $erreur_saisie == 0 )
        {   

			// Si pas d'erreur on ajoute le bon de réduction

            if( $opt_nbrfois == 1 ) // Illimité
                $txt_nbrfois = 0;
            if( $opt_nbrmaxi == 1 ) // Illimité
                $txt_nbrmaxi = 0;
            if( $opt_datefin == 1 ) // Pas de date de fin
                $txt_datefin = "";
            else
            {
                list( $jour, $mois, $annee ) = explode( '/', $txt_datefin );
                $txt_datefin = mktime(23,59,59,$mois,$jour,$annee );
            }
            if(is_array($_POST['marques'])){// une ou plusieurs marques choisi
            	$saveMarques=implode(",", $_POST['marques']);
            }elseif (!isset($_POST['marques'])) {// aucune marques choisis
            	$saveMarques = "0";
            }

		    $port=0;
		    if(isset($_POST['port0'])) $port+=1;
		    if(isset($_POST['port1'])) $port+=2;
		    $cadeauartid=0;
		    if($_POST['cadeau_artid']!="" && substr($_POST['cadeau_artid'],0,3)=="ACI"){
				$cadeau_offert=$DB_site2->query_first("SELECT artid FROM article WHERE artcode='".$_POST['cadeau_artid']."' AND active IN ('0','1')");
				if($cadeau_offert['artid']!="") $cadeauartid=$cadeau_offert['artid'];
		    }
			$totalite=0;
			if(isset($_POST['appcommande'])){
				if(intval($_POST['appcommandetrspt']) == 1) $totalite = 1;
				if(intval($_POST['appcommandetrspt']) == 0) $totalite = 2;
			} 
		
             // si il existe un bon de réduction actif pour la meme période sur l'article ou la catégorie on peut pas le créer
					 $rq_ajouter_cadeau = "INSERT INTO cadeau (date, site_code, utilisateur, typecadeauid, valeurcadeau, code, nbrfois, nbrmaxi, datefin, appliquer, cadeau_artid, `option`, port, minimum, userid, marqueid, totalite) VALUES (".time().", '".$_GET['site_code']."', '".$user_info['username']."', '".htmlentities($opt_typecadeau,ENT_COMPAT,"ISO-8859-15")."', '".htmlentities($txt_valeurcadeau,ENT_COMPAT,"ISO-8859-15")."', '".htmlentities($txt_code,ENT_COMPAT,"ISO-8859-15")."', '".htmlentities($txt_nbrfois,ENT_COMPAT,"ISO-8859-15")."', '".htmlentities($txt_nbrmaxi,ENT_COMPAT,"ISO-8859-15")."', '".htmlentities($txt_datefin,ENT_COMPAT,"ISO-8859-15")."', '$appliquer', ".$cadeauartid.", '".$_POST['option']."', ".$port.", ".floatval($_POST['minimum']).", ".intval($_POST['userid']).", '".$saveMarques."', '".$totalite."')";
                    $DB_site2->query($rq_ajouter_cadeau);
                    $insertid_kado=$DB_site2->insert_id();
                    if ((isset($sel_catid) and $sel_catid != 0)){
			    $req_categ=$DB_site2->query("INSERT INTO categorie_cadeau (cadeauid, catid)
                                                         VALUES ('$insertid_kado', '$sel_catid')") ;
		    }
                    if (isset($sel_art) and $sel_art != 0){
                            $req_art=$DB_site2->query("INSERT INTO article_cadeau (cadeauid, artid)
                                                         VALUES ('$insertid_kado', '$sel_art')") ;
                    }
            echo "<script language=\"javascript\">location.href=\"cadeau.php?site_code=".$_GET['site_code']."\";</script>";
        }
    }
    //}
    //}
    $action = "ajouter";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AJOUTER UN BON DE REDUCTION (action=ajouter)
////////////////////////////////////////////////////////////////////////////////////////////////////////
if ( $action == "ajouter" )
{
    $site=$sites[$_GET['site_code']];
    $devisesign=$devises->symbole($site['devise']);

    echo "<h3>Ajouter un bon de réduction sur site ".$site['code']."</h3>";
    // Calendar
    echo <<<JS
    <script language="javascript">
    		\$ = jQuery ;
            function visualiser( type )
            {
                if( type == 0 ) {
                    document.getElementById('gauche').innerHTML = "Valeur ($site[devise]) :";
                    document.getElementById('droite').innerHTML = "$devisesign TTC &nbsp;<input type='text' id='valueht' size='5' onkeyup='convert_ht_ttc(this.value,\"ht\",\"valuettc\")'>$devisesign HT";
                    document.getElementById('typesign').value = '$devisesign';
					document.getElementById('lignetot').style.display = 'none';
					document.getElementById('lignetottrspt').style.display = 'none';
					document.getElementById('sel_catid').disabled = false;
					document.getElementById('sel_artid').disabled = false;
					document.getElementById('appcommande0').checked = false;
					document.getElementById('appcommande1').checked = false;
                } else {
                    if( type == 1 )
                    {
                        document.getElementById('gauche').innerHTML = "Valeur (Pourcents) :";
                        document.getElementById('droite').innerHTML = "%";
                        document.getElementById('typesign').value = '%';
						document.getElementById('lignetot').style.display = '';
                    }
                }           
            }
			
			function visuApp() {
				if(document.getElementById('sel_check').checked){
					document.getElementById('sel_catid').disabled = true;
					document.getElementById('sel_artid').disabled = true;
					document.getElementById('lignetottrspt').style.display = '';
					$('.typearticle > div').hide();
					$('#sel_catid').val(0);
					$('#sel_art').val(0);
					selectart(0,'');
					var elmt = document.getElementsByName('marques[]');
					var i;
					for (i = 0; i < elmt.length; i++) {
				  		if (elmt[i].type == "checkbox") {
				    		elmt[i].checked = false;
				  		}
					}

				}else{
					document.getElementById('sel_catid').disabled = false;
					document.getElementById('sel_artid').disabled = false;
					document.getElementById('lignetottrspt').style.display = 'none';
					$('.typearticle > div').show();
				}
			}

			function checkallmarque(){
				var elmt = document.getElementsByName('marques[]');
				var i;
				if(document.getElementById('cochemarque').checked == true){
					for (i = 0; i < elmt.length; i++) {
				  		if (elmt[i].type == "checkbox") {
				    		elmt[i].checked = true;
				  		}
					}	
				}else{
					for (i = 0; i < elmt.length; i++) {
				  		if (elmt[i].type == "checkbox") {
				    		elmt[i].checked = false;
				  		}
					}	
				}
			}
			
          </script>$jscadeau
JS;


    $checked_opt_nbrfois_0 = "checked";
    $checked_opt_nbrmaxi_0 = "checked";
    $checked_opt_datefin_0 = "checked";
    
    if( $opt_typecadeau != "" )
    {   // On a posté le formulaire mais il y a eu erreur de saisie -> on réaffiche
        if( $opt_nbrfois == 1 )
            $checked_opt_nbrfois_1 = "checked";
        if( $opt_nbrmaxi == 1 )
            $checked_opt_nbrmaxi_1 = "checked";
        if( $opt_datefin == 1 )
            $checked_opt_datefin_1 = "checked";
    }

    echo "<form method=\"post\" action=\"cadeau.php?site_code=".$site['code']."\" name=\"formu\">
	    <div id=\"formulaire\" class=\"col-xs-12\">
	    <div class=\"row\">
	    	<div class=\"col-xs-offset-3 col-xs-7\">
		    	<div class=\"col-xs-12 no-padding couleur-colonne\">
		    		<div class=\"col-xs-6\"><b>Code</b> (à 12 caractères)</div>
		    		<div class=\"col-xs-3\"><input type=\"text\" name=\"txt_code\" value=\"$txt_code\" maxlength=\"12\"></div>
		    		<div class=\"col-xs-3\"><input type=\"submit\" name=\"btn_code\" value=\"Générer\"></div>
		    	</div>
		    	<div class=\"col-xs-12 no-padding\">
		    		<div class=\"col-xs-6\">Type de bon de réduction</div>
		    		<div class=\"col-xs-6\">
		    		<input type=\"hidden\" id=\"typesign\" value=\"$devisesign\">
	                  <input type=\"radio\" name=\"opt_typecadeau\" id=\"opt_typecadeau0\" value=\"0\"";
	                  if( $opt_typecadeau == 0 )
	                  {
	                      echo " checked";
	                      $tmp_valeur = "Valeur (".$site['devise'].")";
	                      $tmp_unite_valeur = "$devisesign TTC &nbsp;<input type='text' id='valueht' size='5' onkeyup='convert_ht_ttc(this.value,\"ht\",\"valuettc\")'>$devisesign HT";
	                  }
	                  echo " onClick=\"javascript:visualiser(this.value);\"> en ".$site['devise']."&nbsp;&nbsp;&nbsp;";
	                  echo "<input type=\"radio\" name=\"opt_typecadeau\" id=\"opt_typecadeau1\" value=\"1\"";
	                  if( $opt_typecadeau == 1 )
	                  {
	                      echo " checked";
	                      $tmp_valeur = "Valeur (Pourcents)";
	                      $tmp_unite_valeur = "%";
	                  }
	                  echo " onClick=\"javascript:visualiser(this.value);\"> en Pourcents&nbsp;&nbsp;&nbsp;<br>";
		    echo 	"</div>
		    		<div id=\"lignetot\" class=\"col-xs-12 no-padding\" style=\"display:none;\">
		    			<div class=\"col-xs-6\">Code promo applicable : </div>
						<div class=\"col-xs-6\">
							<input type=\"checkbox\" name=\"appcommande\" id=\"sel_check\" onClick=\"javascript:visuApp();\"> sur la totalité de la commande
						</div>
		    		</div>
		    	</div>
		    	<div class=\"col-xs-12 no-padding couleur-colonne\">
		    		<div class=\"col-xs-6\"><div name=\"gauche\" ID=\"gauche\">$tmp_valeur&nbsp;&nbsp;&nbsp;</div></div>
		    		<div class=\"col-xs-6\">
		    		<input type=\"text\" size=\"5\" id=\"valuettc\" name=\"txt_valeurcadeau\" value=\"$txt_valeurcadeau\" onkeyup=\"convert_ht_ttc(this.value,'ttc','valueht')\">
	                      <div name=\"droite\" ID=\"droite\" style=\"display:inline\">$tmp_unite_valeur</div>
	                </div>
	    		</div>
		    	<div class=\"col-xs-12 no-padding\">
		    		<div class=\"col-xs-6\">Lier à un client :</div>
		    		<div class=\"col-xs-6\"><input type=\"hidden\" name=\"userid\" value=\"0\"><input type=\"text\" value=\"Rechercher\" onkeyup=\"srcuser(this.value)\" onclick=\"this.value=''\"><div id=\"user_results\" style=\"position:absolute;border:solid 1px black; background:white; display:none; padding:4px;z-index:999; width:500px;\"></div><br><span id=\"cadeauuser\"></span></div>
		    	</div>
	    	</div>

	    	<div class=\"col-xs-offset-3 col-xs-7\">
	    	<h2>Conditions</h2>
	    		<div id=\"lignetottrspt\" class=\"col-xs-12 no-padding couleur-colonne\" style=\"display:none;\">
		    		<div class=\"col-xs-6\">Code applicable :</div>
		    		<div class=\"col-xs-3\"><input type=\"radio\" name=\"appcommandetrspt\" id=\"appcommande1\" value=\"1\">Avec Transport</div>
		    		<div class=\"col-xs-3\"><input type=\"radio\" name=\"appcommandetrspt\" id=\"appcommande0\" value=\"0\">Hors Transport</div>
		    	</div>
		    	<div class=\"col-xs-12 no-padding\">
		    		<div class=\"col-xs-6\">Nbr utilisations par utilisateur :</div>
		    		<div class=\"col-xs-3\"><input type=\"radio\" name=\"opt_nbrfois\" value=\"0\" $checked_opt_nbrfois_0><input type=\"text\" size=\"5\" name=\"txt_nbrfois\" value=\"$txt_nbrfois\"></div>
		    		<div class=\"col-xs-3\"><input type=\"radio\" name=\"opt_nbrfois\" value=\"1\" $checked_opt_nbrfois_1>Illimité</div>
		    	</div>
		    	<div class=\"col-xs-12 no-padding couleur-colonne\">
		    		<div class=\"col-xs-6\">Nbr utilisations max au total</div>
		    		<div class=\"col-xs-3\"><input type=\"radio\" name=\"opt_nbrmaxi\" value=\"0\" $checked_opt_nbrmaxi_0>
                      <input type=\"text\" size=\"5\" name=\"txt_nbrmaxi\" value=\"$txt_nbrmaxi\"></div>
		    		<div class=\"col-xs-3\"><input type=\"radio\" name=\"opt_nbrmaxi\" value=\"1\" $checked_opt_nbrmaxi_1>Illimité</div>
		    	</div>
		    	<div class=\"col-xs-12 no-padding\">
		    		<div class=\"col-xs-6\">Date fin incluse (jj/mm/aaaa)</div>
		    		<div class=\"col-xs-4\"><input type=\"radio\" name=\"opt_datefin\" value=\"0\" $checked_opt_datefin_0>Oui: 
                  <input type = \"text\" name=\"txt_datefin\" value=\"$txt_datefin\" size=\"10\" maxlength=\"10\">
                  <!--<a href=\"javascript:DCAL.Show('DC1');\" border=\"0\"><img src=\"scripts/calendar/Img/c_b.gif\" border=\"0\" id=\"iDC1\"></a>--></div>
		    		<div class=\"col-xs-2\"><input type=\"radio\" name=\"opt_datefin\" value=\"1\" $checked_opt_datefin_1>Non</div>
		    	</div>
		    	<div class=\"col-xs-12 no-padding couleur-colonne\">
		    		<div class=\"col-xs-6\">Réduction à appliquer sur :</div>
		    		<div class=\"col-xs-3\"><input type=\"radio\" name=\"appliquer\" value=\"PPC\" checked> le PPC</div>
		    		<div class=\"col-xs-3\"><input type=\"radio\" name=\"appliquer\" value=\"PV\"> le PV</div>
		    	</div>
		    	<div class=\"col-xs-12 no-padding\">
		    		<div class=\"col-xs-6\">Minimum d'achat TTC:</div>
		    		<div class=\"col-xs-3\"><input type=\"text\" id=\"minimumttc\" name=\"minimum\" value=\"0\" size=\"5\" onkeyup=\"convert_ht_ttc(this.value,'ttc','minimumht')\">$devisesign TTC </div>
		    		<div class=\"col-xs-3\"><input type=\"text\" id=\"minimumht\" size=\"5\" onkeyup=\"convert_ht_ttc(this.value,'ht','minimumttc')\">$devisesign HT</div>
		    	</div>
	    	</div>
	    		<div class=\"col-xs-offset-3 col-xs-7 typearticle\">
	    		<h2>Type article</h2>
	    			<div class=\"col-xs-12 no-padding couleur-colonne\">
			    		<div class=\"col-xs-6\">Rayon / sous-rayon concerné</div>
			    		<div class=\"col-xs-6\">";
			    		arborescence(0);
			    		echo "<select name=\"sel_catid\" id=\"sel_catid\" style=\"width:100%;\">" ;
    					echo "<option value=\"0\"</option>";
    					echo "$select</select>" ;

			    		echo "</div>
		    		</div>
		    		<div class=\"col-xs-12 no-padding\">
		    			<div class=\"col-xs-6\">Article concerné :</div>
		    			<div class=\"col-xs-6\"><input type=\"hidden\" name=\"sel_art\"><input type=\"text\" value=\"Rechercher\" id=\"sel_artid\" onclick=\"this.value=''\" onkeyup=\"srcart(this.value)\" style=\"width:100%;\"><div id=\"art_result\" style=\"border:solid 1px black;background:white;position:absolute;display:none;z-index:999;\"></div><span id=\"sel_artname\"></span>
		    			</div>
		    		</div>
		    		<div class=\"col-xs-12 no-padding couleur-colonne\">";

    				$req=$DB_site2->query("SELECT libelle,marqueid FROM marque ORDER BY libelle");
   					while($m=$DB_site2->fetch_array($req)){
						$tabMarques[] = $m;
   					}

   					echo "<div class=\"col-xs-12 cadeaumarques\">Marque : <img onclick=\"afficheMarques();\" src=\"images/plus.png\" ></div>";
   					echo "<div id=\"cadeaumarques\" class=\"col-xs-12 no-padding\"><br>";
					$compteur = 0;
                    $cptMarque = round(count($tabMarques)/3);
                    $nbMax = $cptMarque;
                    echo "<input type=\"hidden\" id=\"cptMarque\" name=\"cptMarque\" value=\"".count($tabMarques)."\">";
                    echo "<input type=\"checkbox\" id=\"cochemarque\" name=\"cochemarque\" onclick=\"checkallmarque();\"><span>Tout cocher</span><br>";
                    echo "<div class=\"col-xs-4\">";
                    for($i=$compteur;$i<$nbMax;$i++){
                        $compteur++;
                        echo "<input type=\"checkbox\" name=\"marques[]\" id=\"marque_".$compteur."\" value=\"".$tabMarques[$i]['marqueid']."\" /><span >".$tabMarques[$i]['libelle']."</span><br/>";
                    }
   					echo "</div>";

   					echo "<div class=\"col-xs-4\">";
   					$nbMax = $cptMarque + $compteur;
	                for($i=$compteur;$i<$nbMax;$i++){
	                       $compteur++;
	                       echo "<input type=\"checkbox\" name=\"marques[]\" id=\"marque_".$compteur."\" value=\"".$tabMarques[$i]['marqueid']."\" /><span >".$tabMarques[$i]['libelle']."</span><br/>";
	                }
	                echo "</div>";
	                
	                echo "<div class=\"col-xs-4\">";
                 	$nbMax = $cptMarque + $compteur > count($tabMarques) ? count($tabMarques) : $cptMarque + $compteur;
                    for($i=$compteur;$i<$nbMax;$i++){
                        $compteur++;
                        echo "<input type=\"checkbox\" name=\"marques[]\" id=\"marque_".$compteur."\" value=\"".$tabMarques[$i]['marqueid']."\" /><span >".$tabMarques[$i]['libelle']."</span><br/>";
                      }
                    echo "</div>";

	    			echo "</div></div></div>";
	    		$divopt="<div id=\"caractlist\" style=\"position:absolute;background-color:#CCCCCC;text-align:left;display:none;z-index:999;\"><img src=\"images/moins.gif\" align=\"left\" style=\"cursor:pointer;\" onclick=\"document.getElementById('caractlist').style.display='none';\">";

			    $req=$DB_site2->query("SELECT cv.libelleFR AS caractvalname,c.libelleFR AS caractname,cv.caractvalid FROM caracteristiquevaleur cv INNER JOIN caracteristique c ON(c.caractid=cv.caractid) ORDER BY c.caractid, cv.caractvalid");
			    $lastcaract="";
			    while($opt=$DB_site2->fetch_array($req)){
					if($lastcaract!=$opt['caractname']) $divopt.="<p><b>".stripslashes($opt[caractname])."</b></p>";
					$divopt.="&nbsp; <a href=\"javascript:addcaract($opt[caractvalid],'$opt[caractname] : $opt[caractvalname]')\" title=\"Ajouter\">".stripslashes($opt[caractvalname])."</a><br>";
					$lastcaract=$opt['caractname'];
			    }

			    $divopt.="</div>";

	    	echo	"<div class=\"col-xs-offset-3 col-xs-7\">
	    			<h2>Cadeaux</h2>
	    			<div class=\"col-xs-12 no-padding couleur-colonne\">
	    				<div class=\"col-xs-4\">Offrir un cadeau :</div>
	    				<div class=\"col-xs-8\"><input type=\"text\" name=\"cadeau_artid\" value=\"\" onkeyup=\"srccadeau(this.value);\" onblur=\"if(!okartcode) this.value='';\"><div id=\"resultlist\" style=\"position:absolute;background-color:#CCCCCC;text-align:left;display:none;z-index:999;\"></div></div>
	    			</div>
	    			<div class=\"col-xs-12 no-padding\">
	    				<div class=\"col-xs-4\">Options offertes :</div>
	    				<div class=\"col-xs-4\">$divopt<img src=\"images/plus.gif\" style=\"cursor:pointer;\" onclick=\"document.getElementById('caractlist').style.display='block';\"><input type=\"hidden\" name=\"option\" value=\"\"></td><td colspan=\"2\" id=\"cadeauoption\"></div><div id=\"cadeauoption\" class=\"col-xs-4\">Aucune</div>
	    			</div>
	    			<div class=\"col-xs-12 no-padding couleur-colonne\">
	    				<div class=\"col-xs-4\">Offrir les frais de port :</div>
	    				<div class=\"col-xs-4\"><input type=\"checkbox\" name=\"port0\">Livraison seule</div>
	    				<div class=\"col-xs-4\"><input type=\"checkbox\" name=\"port1\">Livraison et installation</div>
	    			</div>
	    		</div>
	    </div>";
            /*echo "<!--<table class=\"table_modules_boutiques\" border=\"0\">
              <tr><td align=\"right\" width=\"50%\"><b>Code</b> (à 12 caractères)&nbsp;&nbsp;&nbsp;</td> 
                  <td align=\"left\"><input type=\"text\" name=\"txt_code\" value=\"$txt_code\" size=\"18\" maxlength=\"12\"></td>
                  <td align=\"left\"><input type=\"submit\" name=\"btn_code\" value=\"Générer\"></td>
              <tr><td align=\"right\">Type de bon de réduction&nbsp;&nbsp;&nbsp;</td> 
                  <td align=\"left\">
                  <input type=\"hidden\" id=\"typesign\" value=\"$devisesign\">
                  <input type=\"radio\" name=\"opt_typecadeau\" value=\"0\"";
                  if( $opt_typecadeau == 0 )
                  {
                      echo " checked";
                      $tmp_valeur = "Valeur (".$site['devise'].")";
                      $tmp_unite_valeur = "$devisesign TTC &nbsp;<input type='text' id='valueht' size='5' onkeyup='convert_ht_ttc(this.value,\"ht\",\"valuettc\")'>$devisesign HT";
                  }
                  echo " onClick=\"javascript:visualiser(this.value);\"> en ".$site['devise']."<br>";
                  echo "<input type=\"radio\" name=\"opt_typecadeau\" value=\"1\"";
                  if( $opt_typecadeau == 1 )
                  {
                      echo " checked";
                      $tmp_valeur = "Valeur (Pourcents)";
                      $tmp_unite_valeur = "%";
                  }
                  echo " onClick=\"javascript:visualiser(this.value);\"> en Pourcents&nbsp;&nbsp;&nbsp;<br>";
            echo "</td>
                  <td align=\"left\"> </td>
              </tr>
              <tr>" ;
	echo "    <tr id=\"lignetot\" style=\"display:none;\"><td align=\"right\">Code promo applicable : </td>
				  <td colspan=\"2\">
					<input type=\"checkbox\" name=\"appcommande\" id=\"sel_check\" onClick=\"javascript:visuApp();\"> sur la totalité de la commande
				  </td>
			  </tr>";
			  
	echo "<td align=\"right\">Rayon / ss-rayon concerné</td>
              <td colspan=\"2\" align=\"left\">" ;
            arborescence(0);
			
    echo "<select name=\"sel_catid\" id=\"sel_catid\">" ;
    echo "<option value=\"0\"</option>";
    echo "$select
    </select><br>" ;
    echo "</td><td align=\"right\"></td></tr>" ;

    echo "<tr><td align=\"right\">Article concerné : </td>
	    <td colspan=\"2\">
	      <input type=\"hidden\" name=\"sel_art\">
	      <input type=\"text\" value=\"Rechercher\" id=\"sel_artid\" onclick=\"this.value=''\" onkeyup=\"srcart(this.value)\">
	      <div id=\"art_result\" style=\"border:solid 1px black;background:white;position:absolute;display:none;\"></div>
	      <span id=\"sel_artname\"></span>
	    </td>
	  </tr>";
    
    $optmarque="<option value=\"\"></option>";
    $req=$DB_site2->query("SELECT libelle,marqueid FROM marque ORDER BY libelle");
    while($m=$DB_site2->fetch_array($req))
		$optmarque.="<option value=\"".$m['marqueid']."\">".$m['libelle']."</option>";
    
    echo "<tr><td align=\"right\">Marque : </td>
	  <td colspan=\"2\">
	    <select name=\"marque\" id=\"sel_marqid\">$optmarque</select>
	  </td>
	  </tr>";
	  
    echo "<tr><td align=\"right\"><div name=\"gauche\" ID=\"gauche\">$tmp_valeur&nbsp;&nbsp;&nbsp;</div></td>
                  <td align=\"left\"><input type=\"text\" size=\"5\" id=\"valuettc\" name=\"txt_valeurcadeau\" value=\"$txt_valeurcadeau\" onkeyup=\"convert_ht_ttc(this.value,'ttc','valueht')\">
                      <div name=\"droite\" ID=\"droite\" style=\"display:inline\">$tmp_unite_valeur</div>
                  </td>
                  <td align=\"left\"></td>
              </tr>";
	
    echo "    <tr><td align=\"right\">Nombre d'utilisations possibles par utilisateur&nbsp;&nbsp;&nbsp;</td>
                  <td align=\"left\"><input type=\"radio\" name=\"opt_nbrfois\" value=\"0\" $checked_opt_nbrfois_0>
                      <input type=\"text\" size=\"5\" name=\"txt_nbrfois\" value=\"$txt_nbrfois\"></td>
                  <td align=\"left\"><input type=\"radio\" name=\"opt_nbrfois\" value=\"1\" $checked_opt_nbrfois_1>Illimité</td>
              </tr>";
    echo "    <tr><td align=\"right\">Nombre d'utilisations maximum au total&nbsp;&nbsp;&nbsp;</td>
                  <td align=\"left\"><input type=\"radio\" name=\"opt_nbrmaxi\" value=\"0\" $checked_opt_nbrmaxi_0>
                      <input type=\"text\" size=\"5\" name=\"txt_nbrmaxi\" value=\"$txt_nbrmaxi\"></td>
                  <td align=\"left\"><input type=\"radio\" name=\"opt_nbrmaxi\" value=\"1\" $checked_opt_nbrmaxi_1>Illimité</td>
              </tr>";
    echo "    <tr><td align=\"right\">Date de fin incluse (jj/mm/aaaa)&nbsp;&nbsp;&nbsp;</td>
                  <td align=\"left\"><input type=\"radio\" name=\"opt_datefin\" value=\"0\" $checked_opt_datefin_0>Oui: 
                  <input type = \"text\" name=\"txt_datefin\" value=\"$txt_datefin\" size=\"10\" maxlength=\"10\">
                  <a href=\"javascript:DCAL.Show('DC1');\" border=\"0\"><img src=\"scripts/calendar/Img/c_b.gif\" border=\"0\" id=\"iDC1\"></a>
                  </td>
                  <td align=\"left\">       
                    <input type=\"radio\" name=\"opt_datefin\" value=\"1\" $checked_opt_datefin_1>Non
                  </td>
              </tr>";
    echo "    <tr><td align=\"right\">Réduction à appliquer sur : </td>
                  <td colspan=\"2\">
                    <input type=\"radio\" name=\"appliquer\" value=\"PPC\" checked> le PPC
                    <input type=\"radio\" name=\"appliquer\" value=\"PV\"> le PV
                  </td>
              </tr>";

    $divopt="<div id=\"caractlist\" style=\"position:absolute;background-color:#CCCCCC;text-align:left;display:none;\"><img src=\"images/moins.gif\" align=\"left\" style=\"cursor:pointer;\" onclick=\"document.getElementById('caractlist').style.display='none';\">";

    $req=$DB_site2->query("SELECT cv.libelleFR AS caractvalname,c.libelleFR AS caractname,cv.caractvalid FROM caracteristiquevaleur cv INNER JOIN caracteristique c ON(c.caractid=cv.caractid) ORDER BY c.caractid, cv.caractvalid");
    $lastcaract="";
    while($opt=$DB_site2->fetch_array($req)){
		if($lastcaract!=$opt['caractname']) $divopt.="<p><b>".stripslashes($opt[caractname])."</b></p>";
		$divopt.="&nbsp; <a href=\"javascript:addcaract($opt[caractvalid],'$opt[caractname] : $opt[caractvalname]')\" title=\"Ajouter\">".stripslashes($opt[caractvalname])."</a><br>";
		$lastcaract=$opt['caractname'];
    }

    $divopt.="</div>";

    echo "<tr><td align=\"right\">Offrir un cadeau : </td><td colspan=\"2\"><input type=\"text\" name=\"cadeau_artid\" value=\"\" onkeyup=\"srccadeau(this.value);\" onblur=\"if(!okartcode) this.value='';\"><div id=\"resultlist\" style=\"position:absolute;background-color:#CCCCCC;text-align:left;display:none;\"></div></td></tr>
    <tr><td align=\"right\">Options offertes : $divopt<img src=\"images/plus.gif\" style=\"cursor:pointer;\" onclick=\"document.getElementById('caractlist').style.display='block';\"><input type=\"hidden\" name=\"option\" value=\"\"></td><td colspan=\"2\" id=\"cadeauoption\">Aucune</td></tr>
    <tr><td align=\"right\">Offrir les frais de port : </td><td colspan=\"2\"><input type=\"checkbox\" name=\"port0\">Livraison seule<br><input type=\"checkbox\" name=\"port1\">Livraison et installation</td></tr>
    <tr><td align=\"right\">Minimum d'achat TTC : </td><td colspan=\"2\"><input type=\"text\" id=\"minimumttc\" name=\"minimum\" value=\"0\" size=\"5\" onkeyup=\"convert_ht_ttc(this.value,'ttc','minimumht')\">$devisesign TTC &nbsp;<input type=\"text\" id=\"minimumht\" size=\"5\" onkeyup=\"convert_ht_ttc(this.value,'ht','minimumttc')\">$devisesign HT</td></tr>";
    
    echo "<tr><td align=\"right\">Lier à un client : </td><td colspan=\"2\"><input type=\"hidden\" name=\"userid\" value=\"0\"><input type=\"text\" value=\"Rechercher\" onkeyup=\"srcuser(this.value)\" onclick=\"this.value=''\"><div id=\"user_results\" style=\"position:absolute;border:solid 1px black; background:white; display:none; padding:4px;\"></div><br><span id=\"cadeauuser\"></span></td></tr>";

    echo "  </table>-->*/

    echo        "<input type=\"hidden\" name=\"action\" value=\"ajouter2\">
            <br>
	    <div class=\"col-xs-offset-9 col-xs-2\"><input type=\"button\" value=\"Ajouter\" onclick=\"confirmation()\"></div>
	    </div>

	    <div id=\"confirmation\" style=\"display:none;\">
	    <table>
	    <tr><td>Code :</td><td id=\"confirm_code\"></td></tr>
	    <tr><td>Valeur :</td><td id=\"confirm_valeur\"></td></tr>
	    <tr><td>Appliquer sur :</td><td id=\"confirm_appliquer\"></td></tr>
	    <tr><td>Nombre d'utilisations par utilisateur :</td><td id=\"confirm_nbrfois\"></td></tr>
	    <tr><td>Nombre d'utilisations total :</td><td id=\"confirm_nbrmaxi\"></td></tr>
	    <tr><td>Date de fin</td><td id=\"confirm_datefin\"></td></tr>
	    <tr><td>Appliquer sur :</td><td id=\"confirm_appliquerprix\"></td></tr>
	    <tr><td>Appliquer sur la totalité de la commande :</td><td id=\"confirm_appliquercomm\"></td></tr>
	    <tr><td>Cadeau :</td><td id=\"confirm_cadeau\"></td></tr>
	    <tr><td>Option offerte :</td><td id=\"confirm_option\"></td></tr>
	    <tr><td>Frais de port offerts :</td><td id=\"confirm_port\"></td></tr>
	    <tr><td>Minimum d'achat</td><td id=\"confirm_minimum\"></td></tr>
	    <tr><td>Client :</td><td id=\"confirm_client\"></td></tr>
	    </table>
            <input type=\"button\" value=\"Annuler\" onclick=\"annule_confirmation()\"> <input type=\"submit\" name=\"btn_ajouter\" value=\"Ajouter le bon de réduction\">
	    </div>
          </form>";
    
    echo "<br><a href=\"cadeau.php\" target=\"_self\">Retour</a>";

}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// EDITER UN BON DE REDUCTION (action=editer2)
////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif ( $action == "editer2" )
{
    $erreur_saisie = 0;
    echo "<table align=\"center\">";
    // Nombre d'utilisations par utilisateur //////////////////////////////////////////////////////////
    if( $opt_nbrfois == 0 )
    {
        if( !isset( $txt_nbrfois ) || $txt_nbrfois == "" )
        {
            echo "<tr><td><b>Vous devez saisir le nombre d'utilisations par utilisateur</b></td></tr>";
            $erreur_saisie = 1;
        } elseif( !is_numeric( $txt_nbrfois ) )
        {
            echo "<tr><td><b>Le nombre d'utilisations par utilisateur doit être de type numérique</b></td></tr>";
            $erreur_saisie = 1;
        } elseif( $txt_nbrfois < 1 )
        {
            echo "<tr><td><b>Le nombre d'utilisations par utilisateur doit être supérieur à 1</b></td></tr>";
            $erreur_saisie = 1;
        }
    }
    // Nombre d'utilisations maximum pour l'ensemble des utilisateurs /////////////////////////////////
    if( $opt_nbrmaxi == 0 )
    {
        if( !isset( $txt_nbrmaxi ) || $txt_nbrmaxi == "" )
        {
            echo "<tr><td><b>Vous devez saisir le nombre d'utilisations maximum</b></td></tr>";
            $erreur_saisie = 1;
        } elseif( !is_numeric( $txt_nbrmaxi ) )
        {
            echo "<tr><td><b>Le nombre d'utilisations maximum doit être de type numérique</b></td></tr>";
            $erreur_saisie = 1;
        } elseif( $txt_nbrmaxi < 1 )
        {
            echo "<tr><td><b>Le nombre d'utilisations maximum doit être supérieur à 1</b></td></tr>";
            $erreur_saisie = 1;
        }
    }

    echo "</table>";
    
    if( $erreur_saisie == 0 )
    {   // Si pas d'erreur on edite le bon de réduction

        if( $opt_nbrfois == 1 ) // Illimité
            $txt_nbrfois = 0;
        if( $opt_nbrmaxi == 1 ) // Illimité
            $txt_nbrmaxi = 0;
	
	if($_POST['datefin']!=""){
	   $sp=explode("/",$_POST['datefin']);
	   $datefin=mktime(23,59,59,$sp[1],$sp[0],$sp[2]);
	}else
	   $datefin=0;

	$port=0;
	if(isset($_POST['port0'])) $port+=1;
	if(isset($_POST['port1'])) $port+=2;
	$cadeauartid=0;
	if($_POST['cadeau_artid']!="" && substr($_POST['cadeau_artid'],0,3)=="ACI"){
	    $cadeau_offert=$DB_site2->query_first("SELECT artid FROM article WHERE artcode='".$_POST['cadeau_artid']."' AND active IN ('0','1')");
	    if($cadeau_offert['artid']!="") $cadeauartid=$cadeau_offert['artid'];
	}
        $rq_editer_cadeau = "UPDATE cadeau SET nbrfois='$txt_nbrfois', nbrmaxi='$txt_nbrmaxi', appliquer='$appliquer', datefin=$datefin, `option`='".$_POST['option']."', cadeau_artid=".$cadeauartid.", port=".$port.", userid=".intval($_POST['userid']).", minimum=".floatval($_POST['minimum'])." WHERE cadeauid=$cadeauid";
        $DB_site2->query($rq_editer_cadeau);

        echo "<script language=\"javascript\">location.href=\"cadeau.php\";</script>";
    }
    $action = "editer";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// EDITER UN BON DE REDUCTION (action=editer)
////////////////////////////////////////////////////////////////////////////////////////////////////////
if ( $action == "editer" )
{
    $cadeau = $DB_site2->query_first( "SELECT * FROM cadeau WHERE cadeauid=$cadeauid" );

    $checked_opt_nbrfois_0 = "checked";
    $checked_opt_nbrmaxi_0 = "checked";
    if( $opt_nbrfois != "" )
    {   // On a posté le formulaire mais il y a eu erreur de saisie -> on réaffiche
        if( $opt_nbrfois == 1 )
            $checked_opt_nbrfois_1 = "checked";
        if( $opt_nbrmaxi == 1 )
            $checked_opt_nbrmaxi_1 = "checked";
    }
    else
    {   // Premier affichage du formulaire
    
        $txt_nbrfois = $cadeau[nbrfois];
        $txt_nbrmaxi = $cadeau[nbrmaxi];

        if( $cadeau[nbrfois] == 0 ) {
            $checked_opt_nbrfois_1 = "checked";
            $txt_nbrfois = "";
        }
        if( $cadeau[nbrmaxi] == 0 ) {
            $checked_opt_nbrmaxi_1 = "checked";
            $txt_nbrmaxi = "";
        }
    }

    echo "$jscadeau
	  <form method=\"post\" action=\"cadeau.php\" name=\"formu\">
            <table class=\"table_caracts\" border=\"0\">
            <tr><td></td></tr>
              <tr><td align=\"right\"><b>Code</b> (à 12 caractères) : </td> 
                  <td><font color=red>$cadeau[code]</font></td>
                  <td>".($cadeau['date']>0?"Créé par ".$cadeau['utilisateur']." le ".date("d/m/Y",$cadeau['date']):"")."</td>
	      </tr>
              <tr><td align=\"right\">Type de bon de réduction : </td> 
                  <td>";
                  if( $cadeau[typecadeauid] == 0 )
                  {
                      echo "en Euros";
                      $tmp_valeur = "Valeur (Euros) : ";
                      $tmp_unite_valeur = "&euro; TTC &nbsp;".round($cadeau[valeurcadeau]/(1+$txtva[0]/100),2)."&euro; HT";
                  }
                  if( $cadeau[typecadeauid] == 1 )
                  {
                      echo "en Pourcents";
                      $tmp_valeur = "Valeur (Pourcents) : ";
                      $tmp_unite_valeur = "%";
                  }
            echo "</td>
                  <td></td>
              </tr>" ;

             // si une categorie ou un article a été sélectionné, on l'affiche
            $req_categorie = $DB_site2->query_first( "SELECT libelleFR
                                                  FROM categorie_cadeau cc, categorie c 
                                                  WHERE cc.catid = c.catid and cc.cadeauid = '$cadeauid'" );
            if ($req_categorie[libelleFR] != '')
                {
                echo "
                  <tr>
                      <td align=\"right\">Categorie : </td>
                      <td colspan=\"2\">$req_categorie[libelleFR]</td>
                      <td></td>
                  </tr>" ;
                }
/* LA */                
            $req_article = $DB_site2->query_first("SELECT artcode
                                               FROM article_cadeau ac, article a 
                                               WHERE ac.artid = a.artid and ac.cadeauid = '$cadeauid'");
            if ($req_article[libelleFR] != '')
                {
                echo "
                  <tr>
                      <td align=\"right\">Article : </td>
                      <td colspan=\"2\">$req_article[artcode]</td>
                      <td></td>
                  </tr>" ;
                }
              
              echo "<tr><td align=\"right\">$tmp_valeur&nbsp;&nbsp;&nbsp;&nbsp;</td>
                  <td>$cadeau[valeurcadeau] $tmp_unite_valeur</td>
                  <td></td>
              </tr>";
    echo "    <tr><td align=\"right\">Nombre d'utilisations possibles par utilisateur : </td>
                  <td><input type=\"radio\" name=\"opt_nbrfois\" value=\"0\" $checked_opt_nbrfois_0>
                      <input type=\"text\" size=\"5\" name=\"txt_nbrfois\" value=\"$txt_nbrfois\"></td>
                  <td><input type=\"radio\" name=\"opt_nbrfois\" value=\"1\" $checked_opt_nbrfois_1>Illimité</td>
              </tr>";
    echo "    <tr><td align=\"right\">Nombre d'utilisations maximum au total : </td>
                  <td><input type=\"radio\" name=\"opt_nbrmaxi\" value=\"0\" $checked_opt_nbrmaxi_0>
                      <input type=\"text\" size=\"5\" name=\"txt_nbrmaxi\" value=\"$txt_nbrmaxi\"></td>
                  <td><input type=\"radio\" name=\"opt_nbrmaxi\" value=\"1\" $checked_opt_nbrmaxi_1>Illimité</td>
              </tr>";
    
    if( $cadeau[datefin] == 0 )
        $tmp_datefin = "";
    else
        $tmp_datefin = date( "d/m/Y", $cadeau[datefin] );
    echo "    <tr><td align=\"right\">Date de fin incluse : </td>
                  <td><input type=\"text\" id=\"datefin\" name=\"datefin\" value=\"$tmp_datefin\" size=\"10\" onclick=\"calendrier_show(this.id,this.value)\" readonly> </td>
                  <td><input type=\"button\" value=\"Illimité\" onclick=\"document.getElementsByName('datefin')[0].value='';\"></td>
              </tr>
              <tr><td></td></tr>";
    if($cadeau[appliquer]=="PPC"){
        $checkedappliqueroui="checked";
        $checkedappliquernon="";
    } else {
        $checkedappliqueroui="";
        $checkedappliquernon="checked";
    }
    echo "    <tr><td align=\"right\">Réduction à appliquer sur : </td>
                  <td colspan=\"2\">
                    <input type=\"radio\" name=\"appliquer\" value=\"PPC\" $checkedappliqueroui> le PPI
                    <input type=\"radio\" name=\"appliquer\" value=\"PV\" $checkedappliquernon> le PV
                  </td>
              </tr>";

    $offert=array();
    if($cadeau[cadeau_artid]!=0) $offert=$DB_site2->query_first("SELECT * FROM article WHERE artid=".$cadeau['cadeau_artid']);

    $divopt="<div id=\"caractlist\" style=\"position:absolute;background-color:#CCCCCC;text-align:left;display:none;z-index:999;\"><img src=\"images/moins.gif\" align=\"left\" style=\"cursor:pointer;\" onclick=\"document.getElementById('caractlist').style.display='none';\">";
    $req=$DB_site2->query("SELECT cv.libelleFR AS caractvalname,c.libelleFR AS caractname,cv.caractvalid FROM caracteristiquevaleur cv INNER JOIN caracteristique c ON(c.caractid=cv.caractid) ORDER BY c.caractid, cv.caractvalid");
    $lastcaract="";
    while($opt=$DB_site2->fetch_array($req)){
	if($lastcaract!=$opt['caractname']) $divopt.="<p><b>".stripslashes($opt[caractname])."</b></p>";
	$divopt.="&nbsp; <a href=\"javascript:addcaract($opt[caractvalid],'$opt[caractname] : $opt[caractvalname]')\" title=\"Ajouter\">".stripslashes($opt[caractvalname])."</a><br>";
	$lastcaract=$opt['caractname'];
    }
    $divopt.="</div>";

    $liboption="Aucune";
    if($cadeau['option']!=""){
	$liboption="";
	$req=$DB_site2->query("SELECT cv.libelleFR AS caractvalname,c.libelleFR AS caractname,cv.caractvalid FROM caracteristiquevaleur cv INNER JOIN caracteristique c ON(c.caractid=cv.caractid) WHERE cv.caractvalid IN (".$cadeau['option'].")");
	while($opt=$DB_site2->fetch_array($req)){
	    $liboption.="<p id=\"caractvalid$opt[caractvalid]\"><a href=\"javascript:removecaract($opt[caractvalid]);\" style=\"color:red;\" title=\"Supprimer\">X</a>".$opt['caractname']." : ".$opt['caractvalname']."</p>";
	}
    }

    $port0=$port1="";
    switch($cadeau['port']){
	case 1:
	$port0="checked";
	break;
	case 2:
	$port1="checked";
	case 3:
	$port0=$port1="checked";
	break;
    }

    echo "<tr><td align=\"right\">Offrir un cadeau : </td><td colspan=\"2\"><input type=\"text\" name=\"cadeau_artid\" value=\"$offert[artcode]\" onkeyup=\"srccadeau(this.value);\" onblur=\"if(!okartcode) this.value='';\"><div id=\"resultlist\" style=\"position:absolute;background-color:#CCCCCC;text-align:left;display:none;\"></div></td></tr>
    <tr><td align=\"right\">Options offertes : $divopt<img src=\"images/plus.gif\" style=\"cursor:pointer;\" onclick=\"document.getElementById('caractlist').style.display='block';\"><input type=\"hidden\" name=\"option\" value=\"".$cadeau['option']."\"></td><td colspan=\"2\" id=\"cadeauoption\">$liboption</td></tr>
    <tr><td align=\"right\">Offrir les frais de port : </td><td colspan=\"2\"><input type=\"checkbox\" name=\"port0\"$port0>Livraison seule<br><input type=\"checkbox\" name=\"port1\"$port1>Livraison et installation</td></tr>
    <tr><td align=\"right\">Minimum d'achat TTC : </td><td colspan=\"2\"><input type=\"text\" id=\"minimumttc\" name=\"minimum\" value=\"".$cadeau['minimum']."\" size=\"5\" onkeyup=\"convert_ht_ttc(this.value,'ttc','minimumht')\">&euro; TTC &nbsp;<input type=\"text\" id=\"minimumht\" size=\"5\" value=\"".round($cadeau['minimum']/(1+$txtva[0]/100),2)."\" onkeyup=\"convert_ht_ttc(this.value,'ht','minimumttc')\">&euro; HT</td></tr>";
    
    $utilisateur=array();
    $cadeauuser="";
    if($cadeau['userid']>0){
	$utilisateur=$DB_site2->query_first("SELECT userid,nom FROM utilisateur WHERE userid=".$cadeau['userid']);
	$cadeauuser="<a href=\"javascript:cadeauuser(0,'')\" style=\"color:red;\">X</a> ".$utilisateur['nom']." (".$utilisateur['userid'].")";
    }
    echo "<tr><td align=\"right\">Lier à un client : </td><td colspan=\"2\"><input type=\"hidden\" name=\"userid\" value=\"$cadeau[userid]\"><input type=\"text\" value=\"Rechercher\" onkeyup=\"srcuser(this.value)\" onclick=\"this.value=''\"><div id=\"user_results\" style=\"position:absolute;border:solid 1px black; background:white; display:none; padding:4px;z-index:999;\"></div><br><span id=\"cadeauuser\">$cadeauuser</span></td></tr>";

    echo "  </table><br><br>
            <input type=\"hidden\" name=\"action\" value=\"editer2\">
            <input type=\"hidden\" name=\"cadeauid\" value=\"$cadeauid\">
            <input type=\"submit\" name=\"btn_editer\" value=\"Editer le bon de réduction\">
          </form>";
    echo "<br><br><a href=\"cadeau.php\" target=\"_self\">Retour</a>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER UN BON DE REDUCTION (action=supprimer)
////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif ( $action == "supprimer" )
    {
    // Confirmation de suppression
    echo "Etes-vous sûr(e) de bien vouloir enlever ce bon de réduction ?<br><bR>
          <a href=\"cadeau.php?action=supprimer2&cadeauid=$cadeauid\">Oui</a> | <a href=\"cadeau.php\">Non</a>";
    }
    
////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER UNBON DE REDUCTION (action=supprimer2)
////////////////////////////////////////////////////////////////////////////////////////////////////////    
elseif ($action == "supprimer2")
    {
    $DB_site2->query("UPDATE cadeau SET active='0' WHERE cadeauid=$cadeauid");
    echo "<script language=\"javascript\">location.href=\"cadeau.php\";</script>";
    }


include "./footer.php";
?>